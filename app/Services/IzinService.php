<?php

namespace App\Services;

use App\Enums\IzinStatus;
use App\Enums\JenisIzin;
use App\Enums\Jabatan;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Services\Shared\PdfService;
use App\Services\Shared\WebPushService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IzinService
{
    public function __construct(
        private PdfService $pdf,
        private WebPushService $push,
    ) {}

    public static function initialStatus(Karyawan $karyawan): array
    {
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($karyawan->atasan_nik)) {
            return [
                'status' => IzinStatus::PendingAdmin->value,
                'atasan_status' => 'pending',
                'admin_status' => 'pending',
            ];
        }
        return [
            'status' => IzinStatus::PendingAtasan->value,
            'atasan_status' => 'pending',
            'admin_status' => 'pending',
        ];
    }

    public static function determineAtasanNik(Karyawan $karyawan): ?string
    {
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($karyawan->atasan_nik)) {
            return null;
        }
        return $karyawan->atasan_nik;
    }

    public function storeIzin(Request $request, Karyawan $karyawan): array
    {
        $nik = $karyawan->nik;

        Log::info('=== storeIzin START ===', [
            'nik' => $nik,
            'tgl_izin' => $request->tgl_izin,
            'jenis_izin' => $request->jenis_izin,
            'keterangan' => $request->keterangan,
            'has_bukti_file' => $request->hasFile('bukti_file'),
        ]);

        $karyawanFresh = Karyawan::with('unitperusahaan')->where('nik', $nik)->first();
        if (!$karyawanFresh) {
            Log::error('storeIzin: Karyawan not found', ['nik' => $nik]);
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.'];
        }

        $unitKerja = $karyawanFresh->unitperusahaan;
        $jamMasuk = $unitKerja?->jam_masuk instanceof \Carbon\Carbon
            ? $unitKerja->jam_masuk->format('H:i:s')
            : ($unitKerja?->jam_masuk ?? '08:00:00');

        Log::info('storeIzin: Karyawan data loaded', [
            'nik' => $nik,
            'nama' => $karyawanFresh->nama_lengkap,
            'unit' => $karyawanFresh->unit,
            'has_unit_kerja' => $unitKerja !== null,
            'jam_masuk' => $jamMasuk,
            'role_approved' => $karyawanFresh->role_approved,
            'atasan_nik' => $karyawanFresh->atasan_nik,
        ]);

        $jenisIzin = $request->jenis_izin;

        // Deadline check: non-pulang_cepat must submit within 1 hour after jam masuk (HANYA untuk tanggal hari ini)
        if ($jenisIzin !== JenisIzin::PulangCepat->value) {
            $hariIni = now('Asia/Jakarta')->format('Y-m-d');
            if ($request->tgl_izin === $hariIni) {
                $batasSubmit = \Carbon\Carbon::parse($jamMasuk)->addHour()->format('H:i:s');
                $sekarang = now('Asia/Jakarta')->format('H:i:s');
                Log::info('storeIzin: Deadline check (tgl_izin = hari ini)', [
                    'jam_masuk' => $jamMasuk,
                    'batas_submit' => $batasSubmit,
                    'sekarang' => $sekarang,
                    'lewat_deadline' => $sekarang > $batasSubmit,
                ]);
                if ($sekarang > $batasSubmit) {
                    Log::warning('storeIzin: Rejected - deadline passed', ['nik' => $nik, 'sekarang' => $sekarang, 'batas' => $batasSubmit]);
                    return ['success' => false, 'message' => 'Batas pengajuan izin hari ini sudah lewat (maksimal 1 jam setelah jam masuk). Silakan pilih tanggal lain.'];
                }
            } else {
                Log::info('storeIzin: Deadline check skipped (tgl_izin bukan hari ini)', [
                    'tgl_izin' => $request->tgl_izin,
                    'hari_ini' => $hariIni,
                ]);
            }
        }

        // Pulang cepat must have presensi masuk today
        if ($jenisIzin === JenisIzin::PulangCepat->value) {
            $hariini = now('Asia/Jakarta')->format('Y-m-d');
            $hasPresensi = \App\Models\Presensi::where('nik', $nik)
                ->where('tgl_presensi', $hariini)
                ->whereNotNull('jam_in')
                ->exists();

            Log::info('storeIzin: Pulang cepat presensi check', [
                'nik' => $nik,
                'tanggal' => $hariini,
                'has_presensi_masuk' => $hasPresensi,
            ]);

            if (!$hasPresensi) {
                Log::warning('storeIzin: Rejected - no presensi masuk for pulang_cepat', ['nik' => $nik]);
                return ['success' => false, 'message' => 'Anda belum melakukan presensi masuk hari ini. Silakan presensi masuk terlebih dahulu.'];
            }
        }

        $initial = self::initialStatus($karyawanFresh);
        $status = $initial['status'];
        $atasanStatus = $initial['atasan_status'];
        $adminStatus = $initial['admin_status'];
        $atasanNik = self::determineAtasanNik($karyawanFresh);

        Log::info('storeIzin: Status determined', [
            'status' => $status,
            'atasan_status' => $atasanStatus,
            'admin_status' => $adminStatus,
            'atasan_nik' => $atasanNik,
        ]);

        $perusahaan = $karyawanFresh->unitperusahaan?->perusahaan ?? '-';
        $atasan = $atasanNik ? Karyawan::where('nik', $atasanNik)->first() : null;

        $stempelPath = $this->pdf->getStempelPath();

        DB::beginTransaction();
        Log::info('storeIzin: DB transaction started');
        try {
            $exists = Izin::where('nik', $nik)
                ->where('tgl_izin', $request->tgl_izin)
                ->lockForUpdate()
                ->exists();

            Log::info('storeIzin: Duplicate check', ['exists' => $exists]);

            if ($exists) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Anda sudah mengajukan izin pada tanggal tersebut!'];
            }

            // Handle bukti file upload
            $buktiFilePath = null;
            if ($request->hasFile('bukti_file')) {
                $file = $request->file('bukti_file');
                $namaFile = now('Asia/Jakarta')->format('YmdHis') . '-' . $nik . '.' . $file->getClientOriginalExtension();

                Log::info('storeIzin: Uploading bukti file', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'destination' => 'uploads/izin/' . $namaFile,
                ]);

                $file->storeAs('uploads/izin', $namaFile, 'public');
                $buktiFilePath = $namaFile;

                Log::info('storeIzin: Bukti file uploaded successfully', ['path' => $buktiFilePath]);
            }

            Log::info('storeIzin: Creating izin record');
            $izin = Izin::create([
                'nik' => $nik,
                'tgl_izin' => $request->tgl_izin,
                'jenis_izin' => $jenisIzin,
                'jam_datang' => $request->jam_datang,
                'keterangan' => $request->keterangan,
                'bukti_file' => $buktiFilePath,
                'atasan_nik' => $atasanNik,
                'status' => $status,
                'atasan_status' => $atasanStatus,
                'admin_status' => $adminStatus,
                'dikirim_tanggal' => now('Asia/Jakarta'),
            ]);

            Log::info('storeIzin: Izin record created', ['izin_id' => $izin->id]);

            // Generate PDF
            Log::info('storeIzin: Generating PDF');
            $pdfData = [
                'headerSuratPath' => 'assets/img/header-surat.png',
                'nama_lengkap' => $karyawanFresh->nama_lengkap,
                'jabatan' => $karyawanFresh->jabatan instanceof Jabatan ? $karyawanFresh->jabatan->value : ($karyawanFresh->jabatan ?? '-'),
                'posisi' => $karyawanFresh->posisi ?? '-',
                'perusahaan' => $perusahaan,
                'tgl_izin' => $request->tgl_izin,
                'jenis_izin' => $jenisIzin,
                'jenis_izin_label' => $this->getJenisIzinLabel($jenisIzin),
                'jam_datang' => $request->jam_datang,
                'keterangan' => $request->keterangan,
                'nama_atasan' => $atasan?->nama_lengkap ?? '-',
                'jabatan_atasan' => $atasan?->jabatan instanceof Jabatan ? $atasan->jabatan->value : ($atasan?->jabatan ?? '-'),
            ];

            $pdfPath = $this->generatePdf($pdfData, $stempelPath);
            $izin->update(['pdf_form_path' => $pdfPath]);

            Log::info('storeIzin: PDF generated', ['pdf_path' => $pdfPath]);

            // Notify atasan
            if ($atasanNik) {
                try {
                    $atasanUser = Karyawan::where('nik', $atasanNik)->first();
                    if ($atasanUser) {
                        $atasanUser->notify(new \App\Notifications\IzinSubmitted($izin, $karyawanFresh));
                        $jenisLabel = $this->getJenisIzinLabel($jenisIzin);
                        $this->push->send($atasanNik, 'Pengajuan Izin Baru', $karyawanFresh->nama_lengkap . ' mengajukan izin (' . $jenisLabel . ') ' . $request->tgl_izin, '/presensi/dataizin', 'izin-submitted-' . $izin->id);
                        Log::info('storeIzin: Atasan notified', ['atasan_nik' => $atasanNik]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Izin atasan notification failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            DB::commit();
            cache()->forget('pending_izin_admin_count');

            Log::info('=== storeIzin SUCCESS ===', ['izin_id' => $izin->id]);
            return ['success' => true, 'message' => 'Pengajuan izin berhasil! Silahkan menunggu persetujuan.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== storeIzin FAILED ===', [
                'nik' => $nik,
                'tgl_izin' => $request->tgl_izin,
                'jenis_izin' => $request->jenis_izin,
                'keterangan' => $request->keterangan,
                'has_bukti_file' => $request->hasFile('bukti_file'),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Gagal mengajukan izin. Silakan coba lagi.'];
        }
    }

    public function approveIzinAtasan(int $id, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $izin = Izin::where('id', $id)->lockForUpdate()->first();
            if (!$izin) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($izin->atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk pengajuan ini']; }
            if ($izin->status !== IzinStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid']; }

            $izin->update([
                'atasan_status' => 'approved',
                'status' => IzinStatus::PendingAdmin->value,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveIzinAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui izin'];
        }

        $pengaju = Karyawan::where('nik', $izin->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\IzinApprovedByAtasan($izin, $karyawan));
                $this->push->send($izin->nik, 'Izin Disetujui', 'Izin ' . $this->tglIzin($izin) . ' disetujui, menunggu persetujuan selanjutnya', null, 'izin-approved-atasan-' . $izin->id);
            }
        } catch (\Exception $e) {
            Log::warning('Izin atasan approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_izin_admin_count');

        return ['success' => true, 'message' => 'Izin disetujui, diteruskan ke Admin'];
    }

    public function rejectIzinAtasan(int $id, string $rejectedReason, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $izin = Izin::where('id', $id)->lockForUpdate()->first();
            if (!$izin) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($izin->atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk pengajuan ini']; }
            if ($izin->status !== IzinStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk penolakan']; }

            $izin->update([
                'atasan_status' => 'rejected',
                'status' => IzinStatus::Rejected->value,
                'rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectIzinAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak izin'];
        }

        $pengaju = Karyawan::where('nik', $izin->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\IzinRejected($izin, $rejectedReason));
                $this->push->send($izin->nik, 'Izin Ditolak', 'Izin ' . $this->tglIzin($izin) . ' ditolak: ' . $rejectedReason, null, 'izin-rejected-atasan-' . $izin->id);
            }
        } catch (\Exception $e) {
            Log::warning('Izin atasan rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_izin_admin_count');

        return ['success' => true, 'message' => 'Izin ditolak'];
    }

    public function approveIzinAdmin(int $id): array
    {
        DB::beginTransaction();
        try {
            $izin = Izin::where('id', $id)->lockForUpdate()->first();
            if (!$izin) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($izin->status !== IzinStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk persetujuan']; }
            if ($izin->admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Izin ini sudah diproses']; }

            $izin->update([
                'admin_status' => 'approved',
                'status' => IzinStatus::Approved->value,
                'approved_at' => now('Asia/Jakarta'),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveIzinAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui izin'];
        }

        $pengaju = Karyawan::where('nik', $izin->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\IzinApproved($izin));
                $pesan = 'Izin ' . $this->tglIzin($izin) . ' disetujui!';
                $url = null;
                if ($izin->jenis_izin === JenisIzin::PulangCepat->value || $izin->jenis_izin === 'pulang_cepat') {
                    $pesan .= ' Anda dapat melakukan presensi pulang kapan saja.';
                }
                $this->push->send($izin->nik, 'Izin Disetujui', $pesan, $url, 'izin-approved-admin-' . $izin->id);
            }
        } catch (\Exception $e) {
            Log::warning('Izin admin approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_izin_admin_count');

        return ['success' => true, 'message' => 'Izin disetujui.'];
    }

    public function rejectIzinAdmin(int $id, string $rejectedReason): array
    {
        DB::beginTransaction();
        try {
            $izin = Izin::where('id', $id)->lockForUpdate()->first();
            if (!$izin) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($izin->status !== IzinStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk penolakan']; }
            if ($izin->admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Izin ini sudah diproses']; }

            $izin->update([
                'admin_status' => 'rejected',
                'status' => IzinStatus::Rejected->value,
                'rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectIzinAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak izin'];
        }

        $pengaju = Karyawan::where('nik', $izin->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\IzinRejected($izin, $rejectedReason));
                $this->push->send($izin->nik, 'Izin Ditolak', 'Izin ' . $this->tglIzin($izin) . ' ditolak: ' . $rejectedReason, null, 'izin-rejected-admin-' . $izin->id);
            }
        } catch (\Exception $e) {
            Log::warning('Izin admin rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_izin_admin_count');

        return ['success' => true, 'message' => 'Izin ditolak'];
    }

    public function deleteIzin(int $id, string $nik): array
    {
        $izin = Izin::where('id', $id)->where('nik', $nik)->first();
        if (!$izin) {
            return ['success' => false, 'message' => 'Data tidak ditemukan!'];
        }

        if ($izin->status !== IzinStatus::PendingAtasan) {
            return ['success' => false, 'message' => 'Izin yang sudah disetujui atau masuk ke admin tidak bisa dihapus!'];
        }

        self::deleteIzinFiles($izin);
        $izin->delete();
        cache()->forget('pending_izin_admin_count');

        return ['success' => true, 'message' => 'Data izin berhasil dihapus!'];
    }

    public function getEditIzinData(int $id, string $nik): ?Izin
    {
        $izin = Izin::where('id', $id)->where('nik', $nik)->first();
        if (!$izin) return null;

        if ($izin->status !== IzinStatus::Rejected) {
            return null;
        }

        return $izin;
    }

    public function updateIzin(Request $request, int $id, string $nik): array
    {
        $izin = Izin::where('id', $id)->where('nik', $nik)->first();
        if (!$izin) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }

        if ($izin->status !== IzinStatus::Rejected) {
            return ['success' => false, 'message' => 'Hanya izin dengan status ditolak yang bisa diedit'];
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];
        }

        $initialStatus = self::initialStatus($karyawan);
        $atasanNik = self::determineAtasanNik($karyawan);

        DB::beginTransaction();
        try {
            $buktiFilePath = $izin->bukti_file;
            if ($request->hasFile('bukti_file')) {
                if (!empty($izin->bukti_file)) {
                    Storage::disk('public')->delete('uploads/izin/' . $izin->bukti_file);
                }
                $file = $request->file('bukti_file');
                $namaFile = time() . '_' . $nik . '.' . $file->getClientOriginalExtension();
                $file->storeAs('uploads/izin', $namaFile, 'public');
                $buktiFilePath = $namaFile;
            }

            $izin->update([
                'tgl_izin' => $request->tgl_izin,
                'jenis_izin' => $request->jenis_izin,
                'jam_datang' => $request->jenis_izin === 'terlambat' ? $request->jam_datang : null,
                'keterangan' => $request->keterangan,
                'bukti_file' => $buktiFilePath,
                'atasan_nik' => $atasanNik,
                'status' => $initialStatus['status'],
                'atasan_status' => $initialStatus['atasan_status'],
                'admin_status' => $initialStatus['admin_status'],
                'rejected_reason' => null,
            ]);

            $jabatan = $karyawan->jabatan instanceof Jabatan ? $karyawan->jabatan->value : $karyawan->jabatan;
            $perusahaan = Unitperusahaan::where('unit', $karyawan->unit)->value('perusahaan') ?? '-';
            $jenisIzin = $request->jenis_izin;
            $atasan = $atasanNik ? Karyawan::where('nik', $atasanNik)->first() : null;

            $pdfData = [
                'headerSuratPath' => 'assets/img/header-surat.png',
                'nama_lengkap' => $karyawan->nama_lengkap,
                'jabatan' => $jabatan,
                'posisi' => $karyawan->posisi ?? '-',
                'perusahaan' => $perusahaan,
                'tgl_izin' => $izin->tgl_izin,
                'jenis_izin' => $jenisIzin,
                'jenis_izin_label' => $this->getJenisIzinLabel($jenisIzin),
                'jam_datang' => $request->jenis_izin === 'terlambat' ? $request->jam_datang : null,
                'keterangan' => $izin->keterangan,
                'nama_atasan' => $atasan?->nama_lengkap ?? '-',
                'jabatan_atasan' => $atasan?->jabatan instanceof Jabatan ? $atasan->jabatan->value : ($atasan?->jabatan ?? '-'),
            ];

            if (!empty($izin->pdf_form_path)) {
                Storage::disk('public')->delete($izin->pdf_form_path);
            }

            $stempelPath = $this->pdf->getStempelPath();
            $pdfPath = $this->generatePdf($pdfData, $stempelPath);
            $izin->update(['pdf_form_path' => $pdfPath]);

            DB::commit();
            cache()->forget('pending_izin_admin_count');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateIzin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memperbarui izin. Silakan coba lagi.'];
        }

        return ['success' => true, 'message' => 'Izin berhasil diperbarui dan dikirim ulang untuk persetujuan.'];
    }

    public function deleteIzinAdmin(int $id): array
    {
        $izin = Izin::find($id);
        if (!$izin) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }

        if (!in_array($izin->status, [IzinStatus::PendingAdmin, IzinStatus::Rejected])) {
            return ['success' => false, 'message' => 'Hanya data dengan status pending atau ditolak yang bisa dihapus!'];
        }

        self::deleteIzinFiles($izin);
        $izin->delete();
        cache()->forget('pending_izin_admin_count');

        return ['success' => true, 'message' => 'Data izin berhasil dihapus!'];
    }

    public function updateIzinAdmin(int $id, Request $request): array
    {
        $izin = Izin::find($id);
        if (!$izin) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }

        $izin->update([
            'tgl_izin' => $request->tgl_izin,
            'jenis_izin' => $request->jenis_izin,
            'jam_datang' => $request->jam_datang,
        ]);

        return ['success' => true, 'message' => 'Data izin berhasil diperbarui'];
    }

    public function getIzinHistory(string $nik)
    {
        return Izin::with('atasan')
            ->where('nik', $nik)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('tgl_izin', 'desc')
            ->get();
    }

    public function getDataIzinAdmin(Request $request)
    {
        $query = Izin::with(['karyawan.unitperusahaan', 'atasan']);

        if (!empty($request->nama_karyawan)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->nama_karyawan . '%');
            });
        }

        if (!empty($request->unit)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        if (!empty($request->jenis_izin)) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        if (!empty($request->tanggal)) {
            $query->where('tgl_izin', $request->tanggal);
        }

        return $query->orderBy('tgl_izin', 'desc')->paginate(10)->withQueryString();
    }

    public function generatePdf(array $data, ?string $stempelPath = null): string
    {
        return $this->pdf->generateWithCustomPath(
            $data,
            'admin.presensi.pengajuan-izin-pdf',
            'izin',
            'izin',
            $stempelPath
        );
    }

    public static function showFileIzin(string $file, ?string $nik = null): ?string
    {
        $file = basename($file);
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
            return null;
        }

        $candidates = [
            'izin/' . $file,
            'uploads/izin/' . $file,
        ];
        foreach ($candidates as $rel) {
            if (Storage::disk('public')->exists($rel)) {
                if ($nik === null) return null;
                $exists = Izin::where('nik', $nik)
                    ->where(function ($q) use ($rel, $file) {
                        $q->where('pdf_form_path', $rel)
                          ->orWhere('pdf_form_path', $file)
                          ->orWhere('bukti_file', $file)
                          ->orWhere('bukti_file', $rel);
                    })
                    ->exists();
                if (!$exists) return null;
                return $rel;
            }
        }

        $izin = Izin::where('pdf_form_path', 'like', '%/' . $file)
            ->orWhere('pdf_form_path', $file)
            ->orWhere('bukti_file', $file)
            ->orWhere('bukti_file', 'like', '%/' . $file)
            ->first();

        if ($izin) {
            if ($nik !== null && $izin->nik !== $nik) {
                return null;
            }
            if (!empty($izin->bukti_file) && ($izin->bukti_file === $file || str_ends_with($izin->bukti_file, '/' . $file))) {
                return 'uploads/izin/' . $izin->bukti_file;
            }
            return $izin->pdf_form_path ?? ('uploads/izin/' . $izin->bukti_file);
        }

        return null;
    }

    public static function deleteIzinFiles(Izin $izin): void
    {
        try {
            if (!empty($izin->bukti_file)) {
                Storage::disk('public')->delete('uploads/izin/' . $izin->bukti_file);
            }
            if (!empty($izin->pdf_form_path)) {
                Storage::disk('public')->delete($izin->pdf_form_path);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete izin files: ' . $e->getMessage());
        }
    }

    private function tglIzin(Izin $izin): string
    {
        return $izin->tgl_izin instanceof \Carbon\Carbon
            ? $izin->tgl_izin->format('d-m-Y')
            : date('d-m-Y', strtotime($izin->tgl_izin));
    }

    private function getJenisIzinLabel(string $jenis): string
    {
        return match ($jenis) {
            'tidak_masuk' => 'Izin Tidak Masuk',
            'terlambat' => 'Izin Terlambat',
            'setengah_hari' => 'Izin Setengah Hari',
            'pulang_cepat' => 'Izin Pulang Cepat',
            'sakit' => 'Sakit',
            default => $jenis,
        };
    }
}
