<?php

namespace App\Services;

use App\Enums\Jabatan;
use App\Enums\WfhStatus;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Models\Wfh;
use App\Services\Shared\PdfService;
use App\Services\Shared\WebPushService;
use App\Services\Shared\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WfhService
{
    public function __construct(
        private PdfService $pdf,
        private WebPushService $push,
        private LocationService $location,
    ) {}

    public static function initialStatus(Karyawan $karyawan): array
    {
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($karyawan->atasan_nik)) {
            return [
                'status' => WfhStatus::PendingAdmin->value,
                'atasan_status' => 'pending',
                'admin_status' => 'pending',
            ];
        }
        return [
            'status' => WfhStatus::PendingAtasan->value,
            'atasan_status' => 'pending',
            'admin_status' => 'pending',
        ];
    }

    public static function initialLaporanStatus(Karyawan $karyawan): array
    {
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($karyawan->atasan_nik)) {
            return [
                'laporan_status' => WfhStatus::PendingAdmin->value,
                'laporan_atasan_status' => 'pending',
                'laporan_admin_status' => 'pending',
            ];
        }
        return [
            'laporan_status' => WfhStatus::PendingAtasan->value,
            'laporan_atasan_status' => 'pending',
            'laporan_admin_status' => 'pending',
        ];
    }

    public static function determineAtasanNik(Karyawan $karyawan): ?string
    {
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($karyawan->atasan_nik)) {
            return null;
        }
        return $karyawan->atasan_nik;
    }

    public static function canApproveAtasan(Wfh $wfh, Karyawan $karyawan): bool
    {
        return $wfh->atasan_nik === $karyawan->nik
            && $wfh->status === WfhStatus::PendingAtasan
            && $wfh->atasan_status === 'pending';
    }

    public static function canApproveAdmin(Wfh $wfh): bool
    {
        return $wfh->status === WfhStatus::PendingAdmin
            && $wfh->admin_status === 'pending';
    }

    public function getWfhHistory(string $nik)
    {
        return Wfh::with('atasan')
            ->where('nik', $nik)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', WfhStatus::Approved->value)
                        ->whereIn('laporan_status', [WfhStatus::Approved->value, WfhStatus::Rejected->value]);
                });
                $q->orWhere('status', WfhStatus::Rejected->value);
                $q->orWhere('status', WfhStatus::Unpaid->value);
            })
            ->orderBy('tgl_wfh', 'desc')
            ->get();
    }

    public function getDataWfhAdmin(Request $request): array
    {
        $query = Wfh::with(['karyawan.unitperusahaan', 'atasan']);

        if (!empty($request->nama_karyawan)) {
            $safeNama = addcslashes($request->nama_karyawan, '%_');
            $query->whereHas('karyawan', function ($q) use ($safeNama) {
                $q->where('nama_lengkap', 'like', '%' . $safeNama . '%');
            });
        }
        if (!empty($request->unit)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }
        if (!empty($request->tanggal)) {
            $query->where('tgl_wfh', $request->tanggal);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        $datawfh = $query->orderBy('tgl_wfh', 'desc')->paginate(10)->withQueryString();
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();
        $pendingWfhAdmin = Wfh::where('status', WfhStatus::PendingAdmin->value)->count();
        $pendingLaporanAdmin = Wfh::where('laporan_status', WfhStatus::PendingAdmin->value)->count();

        return compact('datawfh', 'unitperusahaan', 'pendingWfhAdmin', 'pendingLaporanAdmin');
    }

    public function storeWfh(Request $request, Karyawan $karyawan): array
    {
        $nik = $karyawan->nik;

        $karyawanFresh = Karyawan::with('unitperusahaan')->where('nik', $nik)->first();
        $unitKerja = $karyawanFresh?->unitperusahaan;
        $jamMasuk = $unitKerja?->jam_masuk instanceof \Carbon\Carbon
            ? $unitKerja->jam_masuk->format('H:i:s')
            : ($unitKerja?->jam_masuk ?? '08:00:00');

        if ($request->tgl_wfh === now('Asia/Jakarta')->format('Y-m-d')) {
            if (now('Asia/Jakarta')->format('H:i:s') >= $jamMasuk) {
                return ['success' => false, 'message' => 'Pengajuan WFH untuk hari ini sudah ditutup setelah jam masuk. Silakan pilih tanggal lain.'];
            }
        }

        $atasanNik = self::determineAtasanNik($karyawanFresh);
        $jabatan = $karyawanFresh->jabatan instanceof Jabatan ? $karyawanFresh->jabatan->value : $karyawanFresh->jabatan;
        $posisi = $karyawanFresh->posisi;

        $initial = self::initialStatus($karyawanFresh);
        $status = $initial['status'];
        $atasanStatus = $initial['atasan_status'];
        $adminStatus = $initial['admin_status'];

        $perusahaan = $karyawanFresh->unitperusahaan?->perusahaan ?? '-';
        $atasan = $atasanNik ? Karyawan::where('nik', $atasanNik)->first() : null;

        $pdfData = [
            'headerSuratPath' => 'assets/img/header-surat.png',
            'nama_lengkap' => $karyawanFresh->nama_lengkap,
            'jabatan' => $jabatan,
            'posisi' => $posisi ?? '-',
            'perusahaan' => $perusahaan,
            'tgl_wfh' => $request->tgl_wfh,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
            'nama_atasan' => $atasan?->nama_lengkap ?? '-',
            'jabatan_atasan' => $atasan?->jabatan instanceof Jabatan ? $atasan->jabatan->value : ($atasan?->jabatan ?? '-'),
            'nama_approver' => '-',
            'jabatan_approver' => '-',
        ];

        $stempelPath = $this->pdf->getStempelPath();

        DB::beginTransaction();
        try {
            $exists = Wfh::where('nik', $nik)
                ->where('tgl_wfh', $request->tgl_wfh)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Anda sudah mengajukan WFH pada tanggal tersebut!'];
            }

            $wfh = Wfh::create([
                'nik' => $nik,
                'jabatan' => $jabatan,
                'posisi' => $posisi,
                'tgl_wfh' => $request->tgl_wfh,
                'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
                'keterangan' => $request->keterangan,
                'atasan_nik' => $atasanNik,
                'status' => $status,
                'atasan_status' => $atasanStatus,
                'admin_status' => $adminStatus,
                'dikirim_tanggal' => now('Asia/Jakarta'),
            ]);

            $pdfPath = $this->generatePdf($pdfData, $stempelPath);
            $wfh->update(['pdf_form_path' => $pdfPath]);

            if ($atasanNik) {
                try {
                    $atasanUser = Karyawan::where('nik', $atasanNik)->first();
                    if ($atasanUser) {
                        $atasanUser->notify(new \App\Notifications\WfhSubmitted($wfh, $karyawanFresh));
                        $this->push->send($atasanNik, 'Pengajuan WFH Baru', $karyawanFresh->nama_lengkap . ' mengajukan WFH ' . $request->tgl_wfh, '/presensi/datawfh', 'wfh-submitted-' . $wfh->id);
                    }
                } catch (\Exception $e) {
                    Log::warning('WFH atasan notification failed: ' . $e->getMessage());
                }
            }

            DB::commit();
            cache()->forget('pending_wfh_count');
            cache()->forget('pending_wfh_admin_count');
            return ['success' => true, 'message' => 'Pengajuan WFH berhasil! Silahkan menunggu persetujuan pengajuan WFH.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storewfh failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengajukan WFH. Silakan coba lagi.'];
        }
    }

    public function deleteWfh(int $id, string $nik): array
    {
        $wfh = Wfh::where('id', $id)->where('nik', $nik)->first();
        if (!$wfh) {
            return ['success' => false, 'message' => 'Data tidak ditemukan!'];
        }

        if (!in_array($wfh->status, [WfhStatus::PendingAtasan])) {
            return ['success' => false, 'message' => 'WFH yang sudah disetujui atau masuk ke admin tidak bisa dihapus!'];
        }

        self::deleteWfhFiles($wfh);
        $wfh->delete();
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'Data WFH berhasil dihapus!'];
    }

    public function deleteWfhAdmin(int $id): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];

        if (!in_array($wfh->status, [WfhStatus::PendingAdmin, WfhStatus::Rejected])) {
            return ['success' => false, 'message' => 'Hanya data dengan status pending atau ditolak yang bisa dihapus!'];
        }

        self::deleteWfhFiles($wfh);
        $wfh->delete();
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Data WFH berhasil dihapus!'];
    }

    public function approveWfhAtasan(int $id, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($wfh->atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk pengajuan ini']; }
            if ($wfh->status !== WfhStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid']; }

            $wfh->update([
                'atasan_status' => 'approved',
                'status' => WfhStatus::PendingAdmin->value,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveWfhAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui WFH'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhApprovedByAtasan($wfh, $karyawan));
                $this->push->send($wfh->nik, 'WFH Disetujui', 'WFH ' . self::tglWfh($wfh) . ' disetujui, menunggu persetujuan selanjutnya', null, 'wfh-approved-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('WFH atasan approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'WFH disetujui, diteruskan ke Admin'];
    }

    public function rejectWfhAtasan(int $id, string $rejectedReason, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($wfh->atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk pengajuan ini']; }
            if ($wfh->status !== WfhStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk penolakan']; }

            $wfh->update([
                'atasan_status' => 'rejected',
                'status' => WfhStatus::Rejected->value,
                'rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectWfhAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak WFH'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhRejected($wfh, $rejectedReason));
                $this->push->send($wfh->nik, 'WFH Ditolak', 'WFH ' . self::tglWfh($wfh) . ' ditolak: ' . $rejectedReason, null, 'wfh-rejected-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('WFH atasan rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'WFH ditolak'];
    }

    public function approveWfhAdmin(int $id): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($wfh->status !== WfhStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk persetujuan']; }
            if ($wfh->admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'WFH ini sudah diproses']; }

            $wfh->update([
                'admin_status' => 'approved',
                'status' => WfhStatus::Approved->value,
                'approved_at' => now('Asia/Jakarta'),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveWfhAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui WFH'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhApproved($wfh));
                $this->push->send($wfh->nik, 'WFH Disetujui ', 'WFH ' . self::tglWfh($wfh) . ' disetujui! Silakan input Laporan.', '/presensi/wfh/' . $id . '/laporan', 'wfh-approved-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('WFH admin approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'WFH disetujui. Karyawan bisa input laporan.'];
    }

    public function rejectWfhAdmin(int $id, string $rejectedReason): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($wfh->status !== WfhStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk penolakan']; }
            if ($wfh->admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'WFH ini sudah diproses']; }

            $wfh->update([
                'admin_status' => 'rejected',
                'status' => WfhStatus::Rejected->value,
                'rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectWfhAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak WFH'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhRejected($wfh, $rejectedReason));
                $this->push->send($wfh->nik, 'WFH Ditolak', 'WFH ' . self::tglWfh($wfh) . ' ditolak Admin', null, 'wfh-rejected-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('WFH admin rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'WFH ditolak'];
    }

    public function getLaporanData(int $id, string $nik, bool $isEdit = false): ?object
    {
        $wfh = Wfh::where('id', $id)->where('nik', $nik)->where('status', WfhStatus::Approved->value)->first();
        if (!$wfh) return null;

        if ($isEdit) {
            if ($wfh->laporan_status !== WfhStatus::Rejected->value) {
                return (object) ['error' => 'Laporan ini tidak dalam status ditolak.'];
            }
            return $wfh;
        }

        $tglWfh = $wfh->tgl_wfh instanceof \Carbon\Carbon ? $wfh->tgl_wfh->format('Y-m-d') : (string) $wfh->tgl_wfh;
        $hariIni = now('Asia/Jakarta')->format('Y-m-d');
        if ($tglWfh !== $hariIni) {
            return (object) ['error' => 'Laporan hanya bisa diupload pada tanggal WFH (' . now('Asia/Jakarta')->parse($tglWfh)->format('d M Y') . '). Hari ini: ' . now('Asia/Jakarta')->format('d M Y') . '.'];
        }

        $presensiToday = Presensi::where('nik', $nik)->where('tgl_presensi', $hariIni)->first();
        if (!$presensiToday || !$presensiToday->jam_in) {
            return (object) ['error' => 'Anda belum melakukan absen masuk hari ini. Silakan absen masuk terlebih dahulu.'];
        }

        $jamMasuk = \Carbon\Carbon::parse($presensiToday->jam_in)->setTimezone('Asia/Jakarta');
        $selisihJam = $jamMasuk->diffInHours(now('Asia/Jakarta'));
        if ($selisihJam < 7) {
            $sisa = ceil(7 - $selisihJam);
            return (object) ['error' => 'Laporan hanya bisa diisi setelah 7 jam absen masuk. Sisa waktu: ' . $sisa . ' jam.'];
        }

        return $wfh;
    }

    public function storeLaporanWfh(Request $request, int $id, string $nik, bool $isEdit = false): array
    {
        $wfh = Wfh::where('id', $id)->where('nik', $nik)->where('status', WfhStatus::Approved->value)->first();
        if (!$wfh) return ['success' => false, 'message' => 'Akses ditolak'];

        if ($isEdit && $wfh->laporan_status !== WfhStatus::Rejected->value) {
            return ['success' => false, 'message' => 'Laporan ini tidak dalam status ditolak'];
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];

        if (!$isEdit) {
            $hariIni = now('Asia/Jakarta')->format('Y-m-d');
            $tglWfh = $wfh->tgl_wfh instanceof \Carbon\Carbon
                ? $wfh->tgl_wfh->format('Y-m-d')
                : (string) $wfh->tgl_wfh;
            if ($tglWfh !== $hariIni) {
                return ['success' => false, 'message' => 'Laporan hanya bisa diupload pada tanggal WFH (' . $tglWfh . ')'];
            }

            $presensiToday = Presensi::where('nik', $nik)->where('tgl_presensi', $hariIni)->first();
            if (!$presensiToday || !$presensiToday->jam_in) {
                return ['success' => false, 'message' => 'Data presensi hari ini tidak ditemukan. Silakan presensi terlebih dahulu.'];
            }

            $jamMasuk = \Carbon\Carbon::parse($presensiToday->jam_in)->setTimezone('Asia/Jakarta');
            $selisihJam = $jamMasuk->diffInHours(now('Asia/Jakarta'));
            if ($selisihJam < 7) {
                return ['success' => false, 'message' => 'Laporan WFH hanya bisa diajukan setelah 7 jam kerja dari jam masuk.'];
            }
        }

        DB::beginTransaction();
        try {
            $imagePaths = [];
            if ($request->hasFile('laporan_images')) {
                if ($isEdit && !empty($wfh->laporan_images) && is_array($wfh->laporan_images)) {
                    foreach ($wfh->laporan_images as $oldPath) {
                        if (!empty($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
                $imageService = app(ImageService::class);
                foreach ($request->file('laporan_images') as $idx => $file) {
                    $path = $imageService->processUpload($file, 'wfh/laporan');
                    if ($path) {
                        $imagePaths[] = $path;
                    }
                }
            }

            $initialLaporan = self::initialLaporanStatus($karyawan);

            $laporanAtasanNik = $karyawan->atasan_nik;
            if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($laporanAtasanNik)) {
                $laporanAtasanNik = null;
            }

            $atasan = $laporanAtasanNik ? Karyawan::where('nik', $laporanAtasanNik)->first() : null;

            $unit = $karyawan->unit;
            $jabatan = $karyawan->jabatan;
            $perusahaan = Unitperusahaan::where('unit', $unit)->value('perusahaan') ?? '-';
            $weekdayMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
            $hariTanggal = $weekdayMap[now('Asia/Jakarta')->format('l')] . ', ' . now('Asia/Jakarta')->format('d F Y');

            if ($isEdit) {
                $liveLocation = $wfh->live_location ?? '-';
            } else {
                $presensiToday = Presensi::where('nik', $nik)->where('tgl_presensi', now('Asia/Jakarta')->format('Y-m-d'))->first();
                $liveLocation = $this->location->reverseGeocode($presensiToday->lokasi_in ?? '-');
            }

            $pdfData = [
                'headerSuratPath' => 'assets/img/header-surat.png',
                'nik' => $nik,
                'nama_lengkap' => $karyawan->nama_lengkap,
                'jabatan' => $jabatan,
                'posisi' => $karyawan->posisi,
                'unit' => $unit,
                'perusahaan' => $perusahaan,
                'tgl_wfh' => $wfh->tgl_wfh,
                'live_location' => $liveLocation,
                'keterangan' => $wfh->keterangan ?? '-',
                'laporan_deskripsi' => $request->laporan_deskripsi,
                'laporan_images' => $imagePaths,
                'hariTanggal' => $hariTanggal,
                'nama_atasan' => $atasan?->nama_lengkap ?? '-',
                'jabatan_atasan' => $atasan?->jabatan instanceof Jabatan ? $atasan->jabatan->value : ($atasan?->jabatan ?? '-'),
            ];

            $stempelPath = $this->pdf->getStempelPath();

            $wfh->update([
                'laporan_deskripsi' => $request->laporan_deskripsi,
                'live_location' => $liveLocation,
                'laporan_images' => $imagePaths,
                'laporan_atasan_nik' => $laporanAtasanNik,
                'laporan_status' => $initialLaporan['laporan_status'],
                'laporan_atasan_status' => $initialLaporan['laporan_atasan_status'],
                'laporan_admin_status' => $initialLaporan['laporan_admin_status'],
                'laporan_rejected_reason' => null,
            ]);

            if ($isEdit && !empty($wfh->laporan_file)) {
                Storage::disk('public')->delete($wfh->laporan_file);
            }

            $pdfPath = $this->generateLaporanPdf($pdfData, $stempelPath);
            $wfh->update(['laporan_file' => $pdfPath]);

            DB::commit();
            cache()->forget('pending_laporan_admin_count');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeLaporanWfh failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengirim laporan WFH. Silakan coba lagi.'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($laporanAtasanNik && $pengaju) {
                $atasanUser = Karyawan::where('nik', $laporanAtasanNik)->first();
                if ($atasanUser) {
                    $atasanUser->notify(new \App\Notifications\LaporanSubmitted($wfh->fresh(), $karyawan));
                    $this->push->send($laporanAtasanNik, 'Laporan WFH Diajukan', $karyawan->nama_lengkap . ' mengajukan laporan WFH ' . self::tglWfh($wfh), null, 'laporan-submitted-' . $id);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Laporan submission notification failed: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Laporan WFH berhasil dikirim! Silahkan menunggu persetujuan Laporan.'];
    }

    public function approveLaporanAtasan(int $id, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($wfh->laporan_atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk laporan ini']; }
            if ($wfh->laporan_status !== WfhStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid']; }

            $wfh->update([
                'laporan_atasan_status' => 'approved',
                'laporan_status' => WfhStatus::PendingAdmin->value,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveLaporanAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui laporan'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanApprovedByAtasan($wfh, $karyawan));
                $this->push->send($wfh->nik, 'Laporan Disetujui Atasan', 'Laporan WFH ' . self::tglWfh($wfh) . ' disetujui atasan, menunggu persetujuan HR', null, 'laporan-approved-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan atasan approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan disetujui atasan, diteruskan ke Admin'];
    }

    public function rejectLaporanAtasan(int $id, string $rejectedReason, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh || $wfh->laporan_atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Akses ditolak']; }
            if ($wfh->laporan_status !== WfhStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid untuk penolakan']; }

            $wfh->update([
                'laporan_atasan_status' => 'rejected',
                'laporan_status' => WfhStatus::Rejected->value,
                'laporan_rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectLaporanAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak laporan'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanRejected($wfh, $rejectedReason));
                $this->push->send($wfh->nik, 'Laporan Ditolak Atasan', 'Laporan WFH ' . self::tglWfh($wfh) . ' ditolak atasan: ' . $rejectedReason, null, 'laporan-rejected-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan atasan rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan ditolak atasan'];
    }

    public function approveLaporanAdmin(int $id): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($wfh->laporan_status !== WfhStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid untuk persetujuan']; }
            if ($wfh->laporan_admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Laporan ini sudah diproses']; }

            $wfh->update([
                'laporan_admin_status' => 'approved',
                'laporan_status' => WfhStatus::Approved->value,
                'laporan_approved_at' => now('Asia/Jakarta'),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveLaporanAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui laporan'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanApproved($wfh));
                $this->push->send($wfh->nik, 'Laporan Disetujui HR', 'Laporan WFH ' . self::tglWfh($wfh) . ' telah disetujui.', null, 'laporan-approved-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan admin approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan disetujui HR'];
    }

    public function rejectLaporanAdmin(int $id, string $rejectedReason): array
    {
        DB::beginTransaction();
        try {
            $wfh = Wfh::where('id', $id)->lockForUpdate()->first();
            if (!$wfh) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($wfh->laporan_status !== WfhStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid untuk penolakan']; }
            if ($wfh->laporan_admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Laporan ini sudah diproses']; }

            $wfh->update([
                'laporan_admin_status' => 'rejected',
                'laporan_status' => WfhStatus::Rejected->value,
                'laporan_rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectLaporanAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak laporan'];
        }

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanRejected($wfh, $rejectedReason));
                $this->push->send($wfh->nik, 'Laporan Ditolak Admin', 'Laporan WFH ' . self::tglWfh($wfh) . ' ditolak Admin', null, 'laporan-rejected-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan admin rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan ditolak Admin'];
    }

    public static function deleteWfhFiles(Wfh $wfh): void
    {
        try {
            if (!empty($wfh->pdf_form_path)) {
                Storage::disk('public')->delete($wfh->pdf_form_path);
            }
            if (!empty($wfh->laporan_file)) {
                Storage::disk('public')->delete($wfh->laporan_file);
            }
            if (!empty($wfh->laporan_images) && is_array($wfh->laporan_images)) {
                foreach ($wfh->laporan_images as $imagePath) {
                    if (!empty($imagePath)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete WFH files: ' . $e->getMessage());
        }
    }

    public function generatePdf(array $data, ?string $stempelPath = null): string
    {
        return $this->pdf->generateWithCustomPath(
            $data,
            'admin.presensi.pengajuan-wfh-pdf',
            'wfh',
            'wfh',
            $stempelPath
        );
    }

    public function generateLaporanPdf(array $data, ?string $stempelPath = null): string
    {
        return $this->pdf->generateWithCustomPath(
            $data,
            'admin.presensi.laporan-pdf',
            'wfh/laporan',
            'laporan',
            $stempelPath
        );
    }

    public static function showFileWfh(string $file, ?string $nik = null): ?string
    {
        $file = basename($file);
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
            return null;
        }

        $candidates = [
            'wfh/' . $file,
            'uploads/wfh/' . $file,
        ];
        foreach ($candidates as $rel) {
            if (Storage::disk('public')->exists($rel)) {
                if ($nik === null) return null;
                $exists = Wfh::where('nik', $nik)
                    ->where(function ($q) use ($rel) {
                        $q->where('pdf_form_path', $rel)
                          ->orWhere('laporan_file', $rel)
                          ->orWhere('laporan_images', 'like', '%' . $rel . '%');
                    })
                    ->exists();
                if (!$exists) return null;
                return $rel;
            }
        }

        $wfh = Wfh::where('pdf_form_path', 'like', '%/' . $file)
            ->orWhere('laporan_file', 'like', '%/' . $file)
            ->orWhere('laporan_images', 'like', '%' . $file . '%')
            ->first();

        if ($wfh) {
            if ($nik === null || $wfh->nik !== $nik) {
                return null;
            }
            $try = [$wfh->pdf_form_path ?? '', $wfh->laporan_file ?? ''];
            foreach ($try as $rel) {
                if ($rel && Storage::disk('public')->exists($rel)) {
                    return $rel;
                }
                $abs = storage_path('app/public/' . $rel);
                if ($rel && file_exists($abs)) return $rel;
            }
        }

        return null;
    }

    private static function tglWfh(Wfh $wfh): string
    {
        return $wfh->tgl_wfh instanceof \Carbon\Carbon
            ? $wfh->tgl_wfh->format('Y-m-d')
            : (string) $wfh->tgl_wfh;
    }
}
