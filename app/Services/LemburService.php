<?php

namespace App\Services;

use App\Enums\Jabatan;
use App\Enums\LemburStatus;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Services\Shared\PdfService;
use App\Services\Shared\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LemburService
{
    public function __construct(
        private PdfService $pdf,
        private WebPushService $push,
    ) {}

    public static function initialStatus(Karyawan $karyawan): array
    {
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($karyawan->atasan_nik)) {
            return [
                'status' => LemburStatus::PendingAdmin->value,
                'atasan_status' => 'pending',
                'admin_status' => 'pending',
            ];
        }
        return [
            'status' => LemburStatus::PendingAtasan->value,
            'atasan_status' => 'pending',
            'admin_status' => 'pending',
        ];
    }

    public static function initialLaporanStatus(Karyawan $karyawan): array
    {
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($karyawan->atasan_nik)) {
            return [
                'laporan_status' => LemburStatus::PendingAdmin->value,
                'laporan_atasan_status' => 'pending',
                'laporan_admin_status' => 'pending',
            ];
        }
        return [
            'laporan_status' => LemburStatus::PendingAtasan->value,
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

    public function canSubmit(Karyawan $karyawan): array
    {
        $hariIni = now('Asia/Jakarta')->format('Y-m-d');

        $exists = Lembur::where('nik', $karyawan->nik)
            ->where('tgl_lembur', $hariIni)
            ->exists();
        if ($exists) {
            return ['can' => false, 'message' => 'Anda sudah mengajukan lembur hari ini.'];
        }

        $jamSekarang = now('Asia/Jakarta');
        $jam = (int) $jamSekarang->format('H');
        $menit = (int) $jamSekarang->format('i');
        $totalMenit = $jam * 60 + $menit;

        // 00:01 - 16:50 = Day In (lembur hari ini, presensi pulang tidak wajib)
        // 18:00 - 23:59 = Day In + Next Day (lembur malam)
        // 16:51 - 17:59 = GAP, tidak boleh mengajukan
        // 00:00 = tidak ada lembur (reset harian)
        $dalamWindowPagi = ($totalMenit >= 1 && $totalMenit <= 1010);   // 00:01 - 16:50
        $dalamWindowMalam = ($totalMenit >= 1080 && $totalMenit <= 1439); // 18:00 - 23:59

        if (!$dalamWindowPagi && !$dalamWindowMalam) {
            return ['can' => false, 'message' => 'Pengajuan lembur hanya bisa dilakukan pada pukul 00:01-16:50 atau 18:00-23:59.'];
        }

        return ['can' => true];
    }

    public function getLemburHistory(string $nik)
    {
        return Lembur::with('atasan')
            ->where('nik', $nik)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', 'approved')
                        ->where('laporan_status', 'approved');
                })->orWhere('status', 'rejected')
                  ->orWhere('status', 'pending_atasan')
                  ->orWhere('status', 'pending_admin')
                  ->orWhere(function ($q3) {
                      $q3->where('status', 'approved')
                         ->where('laporan_status', '!=', 'approved');
                  });
            })
            ->orderBy('tgl_lembur', 'desc')
            ->get();
    }

    public function getDataLemburAdmin(Request $request): array
    {
        $query = Lembur::with(['karyawan.unitperusahaan', 'atasan']);

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
            $query->where('tgl_lembur', $request->tanggal);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        $datalembur = $query->orderBy('tgl_lembur', 'desc')->paginate(10)->withQueryString();
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();
        $pendingLemburAdmin = Lembur::where('status', LemburStatus::PendingAdmin->value)->count();
        $pendingLaporanAdmin = Lembur::where('laporan_status', LemburStatus::PendingAdmin->value)->count();

        return compact('datalembur', 'unitperusahaan', 'pendingLemburAdmin', 'pendingLaporanAdmin');
    }

    public function storePengajuan(Request $request, Karyawan $karyawan): array
    {
        $nik = $karyawan->nik;
        $karyawanFresh = Karyawan::with('unitperusahaan')->where('nik', $nik)->first();

        $atasanNik = self::determineAtasanNik($karyawanFresh);
        $jabatan = $karyawanFresh->jabatan instanceof Jabatan ? $karyawanFresh->jabatan->value : $karyawanFresh->jabatan;
        $posisi = $karyawanFresh->posisi;
        $perusahaan = $karyawanFresh->unitperusahaan?->perusahaan ?? '-';
        $atasan = $atasanNik ? Karyawan::where('nik', $atasanNik)->first() : null;

        $initial = self::initialStatus($karyawanFresh);

        $tglLembur = $request->tgl_lembur ?? now('Asia/Jakarta')->format('Y-m-d');
        $isProrate = $request->durasi_jam === 'prorate';
        $durasiJam = $isProrate ? 5.5 : (float) $request->durasi_jam;
        $jamMulai = $request->jam_mulai;

        // Validasi jam_mulai: menit harus :00 atau :30
        $jamMulaiParts = explode(':', $jamMulai);
        $jamMulaiMenit = (int) ($jamMulaiParts[1] ?? -1);
        if (!in_array($jamMulaiMenit, [0, 30], true)) {
            return ['success' => false, 'message' => 'Jam mulai lembur harus interval 30 menit (:00 atau :30).'];
        }

        $rencanaMulai = \Carbon\Carbon::parse($tglLembur . ' ' . $jamMulai);
        $rencanaSelesai = $isProrate
            ? null
            : $rencanaMulai->copy()->addMinutes((int) ($durasiJam * 60));

        $pdfData = [
            'headerSuratPath' => 'assets/img/header-surat.png',
            'nik' => $nik,
            'nama_lengkap' => $karyawanFresh->nama_lengkap,
            'jabatan' => $jabatan,
            'posisi' => $posisi ?? '-',
            'perusahaan' => $perusahaan,
            'keterangan' => $request->keterangan,
            'tgl_lembur' => $tglLembur,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $rencanaSelesai?->format('H:i') ?? 'Menyesuaikan',
            'durasi_jam' => $durasiJam,
            'is_prorate' => $isProrate,
            'nama_atasan' => $atasan?->nama_lengkap ?? '-',
            'jabatan_atasan' => $atasan?->jabatan instanceof Jabatan ? $atasan->jabatan->value : ($atasan?->jabatan ?? '-'),
        ];

        $stempelPath = $this->pdf->getStempelPath();

        DB::beginTransaction();
        try {
            $exists = Lembur::where('nik', $nik)
                ->where('tgl_lembur', $tglLembur)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Anda sudah mengajukan lembur pada hari ini!'];
            }

            $lembur = Lembur::create([
                'nik' => $nik,
                'tgl_lembur' => $tglLembur,
                'keterangan' => $request->keterangan,
                'durasi_jam' => $durasiJam,
                'rencana_mulai' => $rencanaMulai,
                'rencana_selesai' => $rencanaSelesai,
                'status' => $initial['status'],
                'atasan_nik' => $atasanNik,
                'atasan_status' => $initial['atasan_status'],
                'admin_status' => $initial['admin_status'],
                'dikirim_tanggal' => now('Asia/Jakarta'),
            ]);

            $pdfPath = $this->generatePdf($pdfData, $stempelPath);
            $lembur->update(['pdf_form_path' => $pdfPath]);

            if ($atasanNik) {
                try {
                    $atasanUser = Karyawan::where('nik', $atasanNik)->first();
                    if ($atasanUser) {
                        $atasanUser->notify(new \App\Notifications\LemburSubmitted($lembur, $karyawanFresh));
                        $this->push->send($atasanNik, 'Pengajuan Lembur Baru', $karyawanFresh->nama_lengkap . ' mengajukan lembur', '/presensi/datalembur', 'lembur-submitted-' . $lembur->id);
                    }
                } catch (\Exception $e) {
                    Log::warning('Lembur atasan notification failed: ' . $e->getMessage());
                }
            }

            DB::commit();
            cache()->forget('pending_lembur_count');
            cache()->forget('pending_lembur_admin_count');
            return ['success' => true, 'message' => 'Pengajuan lembur berhasil! Silahkan menunggu persetujuan.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storePengajuan lembur failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengajukan lembur. Silakan coba lagi.'];
        }
    }

    public function deleteLembur(int $id, string $nik): array
    {
        $lembur = Lembur::where('id', $id)->where('nik', $nik)->first();
        if (!$lembur) {
            return ['success' => false, 'message' => 'Data tidak ditemukan!'];
        }

        if (!in_array($lembur->status, [LemburStatus::PendingAtasan])) {
            return ['success' => false, 'message' => 'Lembur yang sudah disetujui atau masuk ke admin tidak bisa dihapus!'];
        }

        self::deleteLemburFiles($lembur);
        $lembur->delete();
        cache()->forget('pending_lembur_count');
        cache()->forget('pending_lembur_admin_count');
        cache()->forget('pending_laporan_lembur_admin_count');

        return ['success' => true, 'message' => 'Data lembur berhasil dihapus!'];
    }

    public function deleteLemburAdmin(int $id): array
    {
        $lembur = Lembur::find($id);
        if (!$lembur) return ['success' => false, 'message' => 'Data tidak ditemukan'];

        if (!in_array($lembur->status, [LemburStatus::PendingAdmin, LemburStatus::Rejected])) {
            return ['success' => false, 'message' => 'Hanya data dengan status pending atau ditolak yang bisa dihapus!'];
        }

        DB::beginTransaction();
        try {
            self::deleteLemburFiles($lembur);
            $lembur->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteLemburAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus data lembur'];
        }

        cache()->forget('pending_lembur_count');
        cache()->forget('pending_lembur_admin_count');
        cache()->forget('pending_laporan_lembur_admin_count');

        return ['success' => true, 'message' => 'Data lembur berhasil dihapus!'];
    }

    public function approveLemburAtasan(int $id, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($lembur->atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk pengajuan ini']; }
            if ($lembur->status !== LemburStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid']; }

            $lembur->update([
                'atasan_status' => 'approved',
                'status' => LemburStatus::PendingAdmin->value,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveLemburAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui lembur'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LemburApprovedByAtasan($lembur, $karyawan));
                $this->push->send($lembur->nik, 'Lembur Disetujui', 'Lembur disetujui, menunggu persetujuan selanjutnya', null, 'lembur-approved-atasan-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Lembur atasan approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_lembur_count');
        cache()->forget('pending_lembur_admin_count');

        return ['success' => true, 'message' => 'Lembur disetujui, diteruskan ke Admin'];
    }

    public function rejectLemburAtasan(int $id, string $rejectedReason, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($lembur->atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk pengajuan ini']; }
            if ($lembur->status !== LemburStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk penolakan']; }

            $lembur->update([
                'atasan_status' => 'rejected',
                'status' => LemburStatus::Rejected->value,
                'rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectLemburAtasan failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak lembur'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LemburRejected($lembur, $rejectedReason));
                $this->push->send($lembur->nik, 'Lembur Ditolak', 'Lembur ditolak: ' . $rejectedReason, null, 'lembur-rejected-atasan-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Lembur atasan rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_lembur_count');
        cache()->forget('pending_lembur_admin_count');

        return ['success' => true, 'message' => 'Lembur ditolak'];
    }

    public function approveLemburAdmin(int $id): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($lembur->status !== LemburStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk persetujuan']; }
            if ($lembur->admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Lembur ini sudah diproses']; }

            $lembur->update([
                'admin_status' => 'approved',
                'status' => LemburStatus::Approved->value,
                'approved_at' => now('Asia/Jakarta'),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveLemburAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui lembur'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LemburApproved($lembur));
                $this->push->send($lembur->nik, 'Lembur Disetujui', 'Lembur disetujui! Silakan ambil foto dan upload laporan.', '/presensi/lembur/' . $id . '/foto', 'lembur-approved-admin-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Lembur admin approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_lembur_count');
        cache()->forget('pending_lembur_admin_count');

        return ['success' => true, 'message' => 'Lembur disetujui. Karyawan bisa mengambil foto dan upload laporan.'];
    }

    public function rejectLemburAdmin(int $id, string $rejectedReason): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($lembur->status !== LemburStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status tidak valid untuk penolakan']; }
            if ($lembur->admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Lembur ini sudah diproses']; }

            $lembur->update([
                'admin_status' => 'rejected',
                'status' => LemburStatus::Rejected->value,
                'rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectLemburAdmin failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak lembur'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LemburRejected($lembur, $rejectedReason));
                $this->push->send($lembur->nik, 'Lembur Ditolak', 'Lembur ditolak Admin', null, 'lembur-rejected-admin-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Lembur admin rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_lembur_count');
        cache()->forget('pending_lembur_admin_count');

        return ['success' => true, 'message' => 'Lembur ditolak'];
    }

    public function getFotoData(int $id, string $nik): ?object
    {
        $lembur = Lembur::where('id', $id)->where('nik', $nik)->where('status', LemburStatus::Approved->value)->first();
        if (!$lembur) return null;

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) return null;

        return (object) [
            'lembur' => $lembur,
            'karyawan' => $karyawan,
            'has_mulai' => !empty($lembur->foto_mulai),
            'has_selesai' => !empty($lembur->foto_selesai),
        ];
    }

    public function storeFoto(Request $request, int $id, string $nik): array
    {
        $lembur = Lembur::where('id', $id)->where('nik', $nik)->where('status', LemburStatus::Approved->value)->first();
        if (!$lembur) return ['success' => false, 'message' => 'Akses ditolak'];

        $type = $request->type;
        $fieldFoto = $type === 'mulai' ? 'foto_mulai' : 'foto_selesai';
        $fieldWaktu = $type === 'mulai' ? 'waktu_mulai' : 'waktu_selesai';

        if (!empty($lembur->{$fieldFoto})) {
            return ['success' => false, 'message' => 'Foto ' . $type . ' lembur sudah ada!'];
        }

        if ($type === 'mulai') {
            // Cek apakah karyawan sudah presensi pulang hari ini
            $presensiHariIni = Presensi::where('nik', $nik)
                ->whereDate('tgl_presensi', $lembur->tgl_lembur)
                ->whereNotNull('jam_out')
                ->first();

            // Cek apakah lembur ini untuk jam SEBELUM jam buka presensi (pre-shift lembur)
            $jamBukaPresensi = '07:00:00';
            $rencanaMulaiPlan = $lembur->rencana_mulai instanceof \Carbon\Carbon
                ? $lembur->rencana_mulai->format('H:i:s')
                : null;

            $isPreShift = $rencanaMulaiPlan && $rencanaMulaiPlan < $jamBukaPresensi;

            // Foto mulai boleh diambil jika:
            // 1. Sudah ada presensi pulang hari ini, ATAU
            // 2. Lembur ini untuk jam sebelum jam buka presensi (pre-shift)
            if (!$presensiHariIni && !$isPreShift) {
                return ['success' => false, 'message' => 'Silakan presensi pulang terlebih dahulu sebelum mengambil foto mulai lembur.'];
            }
        }

        if ($type === 'selesai') {
            if (empty($lembur->waktu_mulai)) {
                return ['success' => false, 'message' => 'Foto mulai belum diambil. Silakan ambil foto mulai terlebih dahulu.'];
            }
            $selisih = \Carbon\Carbon::parse($lembur->waktu_mulai)->diffInMinutes(now('Asia/Jakarta'));
            if ($selisih < 60) {
                $sisa = 60 - $selisih;
                $sisaJam = floor($sisa / 60);
                $sisaMenit = $sisa % 60;
                $sisaStr = '';
                if ($sisaJam > 0) $sisaStr .= $sisaJam . ' jam ';
                if ($sisaMenit > 0) $sisaStr .= $sisaMenit . ' menit';
                return ['success' => false, 'message' => 'Foto selesai baru bisa diambil setelah 1 jam foto mulai. Sisa waktu: ' . trim($sisaStr) . '.'];
            }
        }

        $imageService = app(ImageService::class);
        $path = $imageService->processBase64($request->image, $nik, 'lembur_' . $type, 'lembur');
        if (!$path) {
            return ['success' => false, 'message' => 'Gagal menyimpan foto.'];
        }

        $fileName = basename($path);
        $updateData = [
            $fieldFoto => $fileName,
            $fieldWaktu => now('Asia/Jakarta'),
        ];

        if ($type === 'selesai' && !empty($lembur->waktu_mulai)) {
            $mulai = \Carbon\Carbon::parse($lembur->waktu_mulai);
            $selesai = now('Asia/Jakarta');
            $totalMenit = (int) $mulai->diffInMinutes($selesai);
            $jamTebulat = round($totalMenit / 30) * 30 / 60;
            $jamTebulat = max(0.5, $jamTebulat);
            $updateData['durasi_menit'] = $totalMenit;
            $updateData['durasi_jam'] = $jamTebulat;
        }

        $lembur->update($updateData);

        return ['success' => true, 'message' => 'Foto ' . $type . ' lembur berhasil disimpan!', 'type' => $type];
    }

    public function getLaporanData(int $id, string $nik, bool $isEdit = false): ?object
    {
        $lembur = Lembur::where('id', $id)->where('nik', $nik)
            ->where('status', LemburStatus::Approved->value)
            ->first();
        if (!$lembur) return null;

        if ($isEdit) {
            if ($lembur->laporan_status !== LemburStatus::Rejected) {
                return (object) ['error' => 'Laporan ini tidak dalam status ditolak.'];
            }
        } else {
            if (empty($lembur->foto_mulai) || empty($lembur->foto_selesai)) {
                return (object) ['error' => 'Anda harus mengambil foto mulai dan selesai lembur terlebih dahulu.'];
            }
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) return null;

        $durasiFormatted = $lembur->durasi_formatted;

        return (object) [
            'lembur' => $lembur,
            'karyawan' => $karyawan,
            'durasi_formatted' => $durasiFormatted,
        ];
    }

    public function storeLaporanLembur(Request $request, int $id, string $nik, bool $isEdit = false): array
    {
        $lembur = Lembur::where('id', $id)->where('nik', $nik)
            ->where('status', LemburStatus::Approved->value)
            ->first();
        if (!$lembur) return ['success' => false, 'message' => 'Akses ditolak'];

        if ($isEdit && $lembur->laporan_status !== LemburStatus::Rejected) {
            return ['success' => false, 'message' => 'Laporan ini tidak dalam status ditolak'];
        }

        if (!$isEdit && (empty($lembur->foto_mulai) || empty($lembur->foto_selesai))) {
            return ['success' => false, 'message' => 'Foto mulai dan selesai lembur harus diupload terlebih dahulu.'];
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];

        $initialLaporan = self::initialLaporanStatus($karyawan);
        $laporanAtasanNik = $karyawan->atasan_nik;
        if ($karyawan->role_approved === 'Direktur' || empty($karyawan->role_approved) || empty($laporanAtasanNik)) {
            $laporanAtasanNik = null;
        }

        $atasan = $laporanAtasanNik ? Karyawan::where('nik', $laporanAtasanNik)->first() : null;

        $jabatan = $karyawan->jabatan instanceof Jabatan ? $karyawan->jabatan->value : $karyawan->jabatan;
        $perusahaan = Unitperusahaan::where('unit', $karyawan->unit)->value('perusahaan') ?? '-';

        $jamMulai = $lembur->waktu_mulai instanceof \Carbon\Carbon
            ? $lembur->waktu_mulai->format('H:i')
            : '-';
        $jamSelesai = $lembur->waktu_selesai instanceof \Carbon\Carbon
            ? $lembur->waktu_selesai->format('H:i')
            : '-';
        $durasiFormatted = $lembur->durasi_formatted ?? '-';

        DB::beginTransaction();
        try {
            $imagePaths = [];
            if ($request->hasFile('laporan_images')) {
                if ($isEdit && !empty($lembur->laporan_images) && is_array($lembur->laporan_images)) {
                    foreach ($lembur->laporan_images as $oldPath) {
                        if (!empty($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
                $imageService = app(ImageService::class);
                foreach ($request->file('laporan_images') as $file) {
                    $path = $imageService->processUpload($file, 'lembur/laporan');
                    if ($path) {
                        $imagePaths[] = $path;
                    }
                }
            }

            $lembur->update([
                'laporan_deskripsi' => $request->deskripsi_pekerjaan,
                'laporan_images' => $imagePaths,
                'laporan_atasan_nik' => $laporanAtasanNik,
                'laporan_status' => $initialLaporan['laporan_status'],
                'laporan_atasan_status' => $initialLaporan['laporan_atasan_status'],
                'laporan_admin_status' => $initialLaporan['laporan_admin_status'],
                'laporan_rejected_reason' => null,
            ]);

            $pdfData = [
                'headerSuratPath' => 'assets/img/header-surat.png',
                'nik' => $nik,
                'nama_lengkap' => $karyawan->nama_lengkap,
                'jabatan' => $jabatan,
                'posisi' => $karyawan->posisi ?? '-',
                'perusahaan' => $perusahaan,
                'tgl_lembur' => $lembur->tgl_lembur,
                'keterangan' => $lembur->keterangan ?? '-',
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'durasi' => $durasiFormatted,
                'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
                'laporan_images' => $imagePaths,
                'foto_mulai' => $lembur->foto_mulai,
                'foto_selesai' => $lembur->foto_selesai,
                'nama_atasan' => $atasan?->nama_lengkap ?? '-',
                'jabatan_atasan' => $atasan?->jabatan instanceof Jabatan ? $atasan->jabatan->value : ($atasan?->jabatan ?? '-'),
            ];

            $stempelPath = $this->pdf->getStempelPath();

            if ($isEdit && !empty($lembur->laporan_file)) {
                Storage::disk('public')->delete($lembur->laporan_file);
            }

            $pdfPath = $this->generateLaporanPdf($pdfData, $stempelPath);
            $lembur->update(['laporan_file' => $pdfPath]);

            DB::commit();
            cache()->forget('pending_laporan_lembur_admin_count');
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($imagePaths)) {
                foreach ($imagePaths as $imgPath) {
                    Storage::disk('public')->delete($imgPath);
                }
            }
            Log::error('storeLaporanLembur failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengirim laporan lembur. Silakan coba lagi.'];
        }

        try {
            if ($laporanAtasanNik) {
                $atasanUser = Karyawan::where('nik', $laporanAtasanNik)->first();
                if ($atasanUser) {
                    $atasanUser->notify(new \App\Notifications\LaporanLemburSubmitted($lembur->fresh(), $karyawan));
                    $this->push->send($laporanAtasanNik, 'Laporan Lembur Diajukan', $karyawan->nama_lengkap . ' mengajukan laporan lembur', null, 'laporan-lembur-submitted-' . $id);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Laporan lembur submission notification failed: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Laporan lembur berhasil dikirim! Silahkan menunggu persetujuan.'];
    }

    public function approveLaporanAtasan(int $id, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($lembur->laporan_atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Anda bukan atasan untuk laporan ini']; }
            if ($lembur->laporan_status !== LemburStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid']; }

            $lembur->update([
                'laporan_atasan_status' => 'approved',
                'laporan_status' => LemburStatus::PendingAdmin->value,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveLaporanAtasan lembur failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui laporan'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanLemburApprovedByAtasan($lembur, $karyawan));
                $this->push->send($lembur->nik, 'Laporan Disetujui Atasan', 'Laporan lembur disetujui atasan, menunggu persetujuan HR', null, 'laporan-lembur-approved-atasan-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan lembur atasan approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_lembur_admin_count');

        return ['success' => true, 'message' => 'Laporan disetujui atasan, diteruskan ke Admin'];
    }

    public function rejectLaporanAtasan(int $id, string $rejectedReason, Karyawan $karyawan): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur || $lembur->laporan_atasan_nik !== $karyawan->nik) { DB::rollBack(); return ['success' => false, 'message' => 'Akses ditolak']; }
            if ($lembur->laporan_status !== LemburStatus::PendingAtasan) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid untuk penolakan']; }

            $lembur->update([
                'laporan_atasan_status' => 'rejected',
                'laporan_status' => LemburStatus::Rejected->value,
                'laporan_rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectLaporanAtasan lembur failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak laporan'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanLemburRejected($lembur, $rejectedReason));
                $this->push->send($lembur->nik, 'Laporan Ditolak Atasan', 'Laporan lembur ditolak atasan: ' . $rejectedReason, null, 'laporan-lembur-rejected-atasan-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan lembur atasan rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_lembur_admin_count');

        return ['success' => true, 'message' => 'Laporan ditolak atasan'];
    }

    public function approveLaporanAdmin(int $id): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($lembur->laporan_status !== LemburStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid untuk persetujuan']; }
            if ($lembur->laporan_admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Laporan ini sudah diproses']; }

            $lembur->update([
                'laporan_admin_status' => 'approved',
                'laporan_status' => LemburStatus::Approved->value,
                'laporan_approved_at' => now('Asia/Jakarta'),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveLaporanAdmin lembur failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyetujui laporan'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanLemburApproved($lembur));
                $this->push->send($lembur->nik, 'Laporan Disetujui HR', 'Laporan lembur telah disetujui.', null, 'laporan-lembur-approved-admin-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan lembur admin approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_lembur_admin_count');

        return ['success' => true, 'message' => 'Laporan disetujui HR'];
    }

    public function rejectLaporanAdmin(int $id, string $rejectedReason): array
    {
        DB::beginTransaction();
        try {
            $lembur = Lembur::where('id', $id)->lockForUpdate()->first();
            if (!$lembur) { DB::rollBack(); return ['success' => false, 'message' => 'Data tidak ditemukan']; }
            if ($lembur->laporan_status !== LemburStatus::PendingAdmin) { DB::rollBack(); return ['success' => false, 'message' => 'Status laporan tidak valid untuk penolakan']; }
            if ($lembur->laporan_admin_status !== 'pending') { DB::rollBack(); return ['success' => false, 'message' => 'Laporan ini sudah diproses']; }

            $lembur->update([
                'laporan_admin_status' => 'rejected',
                'laporan_status' => LemburStatus::Rejected->value,
                'laporan_rejected_reason' => $rejectedReason,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectLaporanAdmin lembur failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menolak laporan'];
        }

        $pengaju = Karyawan::where('nik', $lembur->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanLemburRejected($lembur, $rejectedReason));
                $this->push->send($lembur->nik, 'Laporan Ditolak Admin', 'Laporan lembur ditolak Admin', null, 'laporan-lembur-rejected-admin-' . $lembur->id);
            }
        } catch (\Exception $e) {
            Log::warning('Laporan lembur admin rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_lembur_admin_count');

        return ['success' => true, 'message' => 'Laporan ditolak Admin'];
    }

    public static function deleteLemburFiles(Lembur $lembur): void
    {
        try {
            if (!empty($lembur->pdf_form_path)) {
                Storage::disk('public')->delete($lembur->pdf_form_path);
            }
            if (!empty($lembur->foto_mulai)) {
                Storage::disk('public')->delete('uploads/lembur/' . $lembur->foto_mulai);
            }
            if (!empty($lembur->foto_selesai)) {
                Storage::disk('public')->delete('uploads/lembur/' . $lembur->foto_selesai);
            }
            if (!empty($lembur->laporan_file)) {
                Storage::disk('public')->delete($lembur->laporan_file);
            }
            if (!empty($lembur->laporan_images) && is_array($lembur->laporan_images)) {
                foreach ($lembur->laporan_images as $imagePath) {
                    if (!empty($imagePath)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete lembur files: ' . $e->getMessage());
        }
    }

    public function generatePdf(array $data, ?string $stempelPath = null): string
    {
        return $this->pdf->generateWithCustomPath(
            $data,
            'admin.presensi.pengajuan-lembur-pdf',
            'lembur',
            'lembur',
            $stempelPath
        );
    }

    public function generateLaporanPdf(array $data, ?string $stempelPath = null): string
    {
        return $this->pdf->generateWithCustomPath(
            $data,
            'admin.presensi.laporan-lembur-pdf',
            'lembur/laporan',
            'laporan-lembur',
            $stempelPath
        );
    }

    public static function showFileLembur(string $file, ?string $nik = null): ?string
    {
        $file = basename($file);
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
            return null;
        }

        $candidates = [
            'lembur/' . $file,
            'lembur/laporan/' . $file,
            'uploads/lembur/' . $file,
            'uploads/lembur/laporan/' . $file,
        ];
        foreach ($candidates as $rel) {
            if (Storage::disk('public')->exists($rel)) {
                if ($nik === null) return null;
                $exists = Lembur::where(function ($q) use ($nik) {
                        $q->where('nik', $nik)
                          ->orWhere('atasan_nik', $nik)
                          ->orWhere('laporan_atasan_nik', $nik);
                    })
                    ->where(function ($q) use ($rel) {
                        $q->where('pdf_form_path', $rel)
                          ->orWhere('laporan_file', $rel)
                          ->orWhere('foto_mulai', basename($rel))
                          ->orWhere('foto_selesai', basename($rel))
                          ->orWhere('laporan_images', 'like', '%' . $rel . '%');
                    })
                    ->exists();
                if (!$exists) return null;
                return $rel;
            }
        }

        $lembur = Lembur::where(function ($q) use ($file) {
                $q->where('pdf_form_path', 'like', '%/' . $file)
                  ->orWhere('laporan_file', 'like', '%/' . $file)
                  ->orWhere('foto_mulai', $file)
                  ->orWhere('foto_selesai', $file)
                  ->orWhere('laporan_images', 'like', '%"uploads/lembur/laporan/' . $file . '"%');
            })
            ->first();

        if ($lembur) {
            if ($nik === null || ($lembur->nik !== $nik && $lembur->atasan_nik !== $nik && $lembur->laporan_atasan_nik !== $nik)) {
                return null;
            }
            $try = [];
            if ($lembur->pdf_form_path) $try[] = $lembur->pdf_form_path;
            if ($lembur->laporan_file) $try[] = $lembur->laporan_file;
            if ($lembur->foto_mulai) $try[] = 'uploads/lembur/' . $lembur->foto_mulai;
            if ($lembur->foto_selesai) $try[] = 'uploads/lembur/' . $lembur->foto_selesai;
            if (!empty($lembur->laporan_images) && is_array($lembur->laporan_images)) {
                foreach ($lembur->laporan_images as $img) {
                    if ($img) $try[] = $img;
                }
            }
            foreach ($try as $rel) {
                if ($rel && Storage::disk('public')->exists($rel)) {
                    return $rel;
                }
            }
        }

        return null;
    }
}
