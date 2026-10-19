<?php

namespace App\Services;

use App\Enums\CutiStatus;
use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\User;
use App\Services\Shared\PdfService;
use App\Services\Shared\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CutiService
{
    public const MAX_DURASI_PER_UPLOAD = 3;

    public const JATAH_CUTI_MAX = 12;

    public const FOLDER = 'uploads/cuti';

    public function __construct(
        private PdfService $pdf,
        private WebPushService $push,
    ) {}

    // ==================================================================
    // SISI KARYAWAN — STEP 1: input data -> draft + generate PDF
    // ==================================================================

    public function storeDraftCuti(Request $request, Karyawan $karyawan): array
    {
        $nik = $karyawan->nik;
        $durasi = (int) $request->durasi_hari;
        $tanggalCuti = array_values(array_filter((array) $request->tanggal_cuti, fn ($t) => ! empty($t)));

        if (count($tanggalCuti) !== $durasi) {
            return ['success' => false, 'message' => 'Jumlah tanggal harus sesuai dengan durasi cuti.'];
        }

        DB::beginTransaction();
        try {
            $karyawanLocked = Karyawan::where('nik', $nik)->lockForUpdate()->first();
            if (! $karyawanLocked) {
                DB::rollBack();

                return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.'];
            }

            // Kuota dihitung hanya dari cuti yang sudah disetujui.
            $terpakai = (int) $karyawanLocked->cuti()->approved()->sum('durasi_hari');
            $sisa = max(0, (int) $karyawanLocked->jatah_cuti - $terpakai);

            if ($durasi > $sisa) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => "Sisa cuti tidak mencukupi. Sisa cuti Anda: {$sisa} hari.",
                ];
            }

            $dupes = self::conflictingDates($nik, $tanggalCuti);
            if ($dupes !== []) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Tanggal '.implode(', ', $dupes).' sudah pernah dipilih cuti.',
                ];
            }

            $cuti = Cuti::create([
                'nik' => $nik,
                'status' => CutiStatus::Draft,
                'admin_status' => 'pending',
                'durasi_hari' => $durasi,
                'tanggal_cuti' => $tanggalCuti,
                'keterangan' => $request->keterangan,
                'dikirim_tanggal' => now('Asia/Jakarta'),
            ]);

            // Generate formulir PDF (kolom tanda tangan kosong) untuk dicetak.
            $pdfPath = $this->generateFormPdf($cuti, $karyawanLocked);
            $cuti->update(['pdf_form_path' => $pdfPath]);

            DB::commit();

            Log::info('storeDraftCuti SUCCESS', ['id' => $cuti->id, 'nik' => $nik, 'durasi' => $durasi, 'sisa' => $sisa]);

            return [
                'success' => true,
                'message' => 'Data pengajuan cuti tersimpan. Silakan cetak formulir PDF, tanda tangani, lalu upload file hasil scan di langkah berikutnya.',
                'cuti_id' => $cuti->id,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeDraftCuti FAILED', ['nik' => $nik, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Gagal menyimpan pengajuan cuti. Silakan coba lagi.'];
        }
    }

    public function updateDraftCuti(int $id, Request $request, Karyawan $karyawan): array
    {
        $cuti = Cuti::find($id);
        if (! $cuti || $cuti->nik !== $karyawan->nik) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }
        if ($cuti->status !== CutiStatus::Draft) {
            return ['success' => false, 'message' => 'Hanya pengajuan berstatus draft yang bisa diubah.'];
        }

        $durasi = (int) $request->durasi_hari;
        $tanggalCuti = array_values(array_filter((array) $request->tanggal_cuti, fn ($t) => ! empty($t)));

        if (count($tanggalCuti) !== $durasi) {
            return ['success' => false, 'message' => 'Jumlah tanggal harus sesuai dengan durasi cuti.'];
        }

        DB::beginTransaction();
        try {
            $karyawanLocked = Karyawan::where('nik', $cuti->nik)->lockForUpdate()->first();
            if (! $karyawanLocked) {
                DB::rollBack();

                return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.'];
            }

            $terpakai = (int) $karyawanLocked->cuti()->approved()->sum('durasi_hari');
            $sisa = max(0, (int) $karyawanLocked->jatah_cuti - $terpakai);

            if ($durasi > $sisa) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => "Sisa cuti tidak mencukupi. Sisa cuti Anda: {$sisa} hari.",
                ];
            }

            $dupes = self::conflictingDates($cuti->nik, $tanggalCuti, $cuti->id);
            if ($dupes !== []) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Tanggal '.implode(', ', $dupes).' sudah pernah dipilih cuti.',
                ];
            }

            $cuti->update([
                'durasi_hari' => $durasi,
                'tanggal_cuti' => $tanggalCuti,
                'keterangan' => $request->keterangan,
            ]);

            // Formulir PDF diregenerate agar data tercetak mengikuti perubahan.
            $pdfPath = $this->generateFormPdf($cuti, $karyawanLocked);
            $cuti->update(['pdf_form_path' => $pdfPath]);

            DB::commit();

            return ['success' => true, 'message' => 'Data pengajuan cuti diperbarui. Unduh kembali formulir PDF terbaru.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateDraftCuti failed: '.$e->getMessage());

            return ['success' => false, 'message' => 'Gagal memperbarui pengajuan cuti.'];
        }
    }

    // ==================================================================
    // SISI KARYAWAN — STEP 2: upload file hasil print & tanda tangan
    // ==================================================================

    public function uploadBuktiCuti(int $id, Request $request, Karyawan $karyawan): array
    {
        $cuti = Cuti::find($id);
        if (! $cuti || $cuti->nik !== $karyawan->nik) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }

        // Draft boleh diajukan; rejected boleh re-upload file (tanpa ulang step 1).
        if (! in_array($cuti->status, [CutiStatus::Draft, CutiStatus::Rejected], true)) {
            return [
                'success' => false,
                'message' => 'Pengajuan ini tidak menunggu upload file. Silakan tunggu proses persetujuan.',
            ];
        }

        $oldFile = $cuti->bukti_file;

        DB::beginTransaction();
        try {
            $buktiPath = null;
            if ($request->hasFile('bukti_file')) {
                $file = $request->file('bukti_file');
                $namaFile = now('Asia/Jakarta')->format('YmdHis').'-'.$cuti->nik.'.'.$file->getClientOriginalExtension();
                $file->storeAs(self::FOLDER, $namaFile, 'public');
                $buktiPath = $namaFile;
            }

            if (! $buktiPath) {
                DB::rollBack();

                return ['success' => false, 'message' => 'File cuti wajib diupload.'];
            }

            $cuti->update([
                'bukti_file' => $buktiPath,
                'status' => CutiStatus::PendingAdmin,
                'admin_status' => 'pending',
                'rejected_reason' => null,
                'dikirim_tanggal' => now('Asia/Jakarta'),
            ]);

            DB::commit();

            // File lama (milik pengajuan rejected) dibersihkan setelah commit.
            if ($oldFile && $oldFile !== $buktiPath) {
                try {
                    Storage::disk('public')->delete(self::FOLDER.'/'.$oldFile);
                } catch (\Exception $cleanupEx) {
                    Log::warning('uploadBuktiCuti: failed to cleanup old file: '.$cleanupEx->getMessage());
                }
            }

            Log::info('uploadBuktiCuti SUCCESS', ['id' => $cuti->id, 'nik' => $cuti->nik, 'status' => CutiStatus::PendingAdmin->value]);
            $this->notifySuperAdmins($cuti, $karyawan->fresh());

            return [
                'success' => true,
                'message' => 'File cuti berhasil diupload dan pengajuan sedang menunggu persetujuan superadmin.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($buktiPath) && $buktiPath) {
                try {
                    Storage::disk('public')->delete(self::FOLDER.'/'.$buktiPath);
                } catch (\Exception $cleanupEx) {
                    Log::warning('uploadBuktiCuti: failed to cleanup file: '.$cleanupEx->getMessage());
                }
            }
            Log::error('uploadBuktiCuti FAILED', ['id' => $id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Gagal mengupload file cuti. Silakan coba lagi.'];
        }
    }

    // ==================================================================
    // SISI SUPERADMIN — approve / reject
    // ==================================================================

    public function approveCuti(int $id, int $adminUserId): array
    {
        $cuti = Cuti::find($id);
        if (! $cuti) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }
        if ($cuti->status !== CutiStatus::PendingAdmin) {
            return ['success' => false, 'message' => 'Status tidak valid untuk persetujuan'];
        }
        if ($cuti->admin_status !== 'pending') {
            return ['success' => false, 'message' => 'Pengajuan cuti ini sudah diproses'];
        }

        DB::beginTransaction();
        try {
            $karyawanLocked = Karyawan::where('nik', $cuti->nik)->lockForUpdate()->first();
            if (! $karyawanLocked) {
                DB::rollBack();

                return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.'];
            }

            // Re-validasi kuota saat approve untuk mencegah over-book.
            $terpakaiLain = (int) $karyawanLocked->cuti()->approved()->where('id', '!=', $cuti->id)->sum('durasi_hari');
            $jatah = (int) $karyawanLocked->jatah_cuti;

            if ($terpakaiLain + $cuti->durasi_hari > $jatah) {
                DB::rollBack();
                $sisaTanpaIni = max(0, $jatah - $terpakaiLain);

                return [
                    'success' => false,
                    'message' => "Pengajuan melebihi sisa kuota cuti karyawan. Sisa kuota saat ini: {$sisaTanpaIni} hari.",
                ];
            }

            $cuti->update([
                'status' => CutiStatus::Approved,
                'admin_status' => 'approved',
                'approved_by' => $adminUserId,
                'approved_at' => now('Asia/Jakarta'),
                'rejected_reason' => null,
            ]);

            DB::commit();

            Log::info('approveCuti SUCCESS', ['id' => $cuti->id, 'nik' => $cuti->nik, 'durasi' => $cuti->durasi_hari]);
            $this->notifyKaryawan($cuti, 'approved');

            return ['success' => true, 'message' => 'Pengajuan cuti disetujui. Kuota cuti karyawan telah dikurangi.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('approveCuti FAILED', ['id' => $id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Gagal menyetujui pengajuan cuti.'];
        }
    }

    public function rejectCuti(int $id, string $rejectedReason): array
    {
        $cuti = Cuti::find($id);
        if (! $cuti) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }
        if ($cuti->status !== CutiStatus::PendingAdmin) {
            return ['success' => false, 'message' => 'Status tidak valid untuk penolakan'];
        }
        if ($cuti->admin_status !== 'pending') {
            return ['success' => false, 'message' => 'Pengajuan cuti ini sudah diproses'];
        }

        DB::beginTransaction();
        try {
            $cuti->update([
                'status' => CutiStatus::Rejected,
                'admin_status' => 'rejected',
                'rejected_reason' => $rejectedReason,
            ]);

            DB::commit();

            Log::info('rejectCuti SUCCESS', ['id' => $cuti->id, 'nik' => $cuti->nik, 'reason' => $rejectedReason]);
            $this->notifyKaryawan($cuti, 'rejected');

            return [
                'success' => true,
                'message' => 'Pengajuan cuti ditolak. Karyawan dapat mengupload ulang file yang sudah ditandatangani.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('rejectCuti FAILED', ['id' => $id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Gagal menolak pengajuan cuti.'];
        }
    }

    // ==================================================================
    // SISI ADMIN — kelola data (edit / hapus)
    // ==================================================================

    public function getHistory(string $nik)
    {
        return Cuti::where('nik', $nik)
            ->orderBy('dikirim_tanggal', 'desc')
            ->get();
    }

    public function getDataCutiAdmin(Request $request)
    {
        $query = Cuti::with(['karyawan.unitperusahaan']);

        if (! empty($request->nama_karyawan)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%'.$request->nama_karyawan.'%');
            });
        }

        if (! empty($request->nik)) {
            $query->where('nik', $request->nik);
        }

        if (! empty($request->unit)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        if (! empty($request->status)) {
            $query->where('status', $request->status);
        }

        if (! empty($request->tanggal)) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('dikirim_tanggal', $request->tanggal)
                    ->orWhereJsonContains('tanggal_cuti', $request->tanggal);
            });
        }

        return $query->orderBy('dikirim_tanggal', 'desc')->paginate(10)->withQueryString();
    }

    public function pendingCount(): int
    {
        return Cuti::where('status', CutiStatus::PendingAdmin)->count();
    }

    public function updateCuti(int $id, Request $request): array
    {
        $cuti = Cuti::find($id);
        if (! $cuti) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }

        $durasi = (int) $request->durasi_hari;
        $tanggalCuti = array_values(array_filter((array) $request->tanggal_cuti, fn ($t) => ! empty($t)));

        if (count($tanggalCuti) !== $durasi) {
            return ['success' => false, 'message' => 'Jumlah tanggal harus sesuai dengan durasi cuti.'];
        }

        DB::beginTransaction();
        try {
            $karyawanLocked = Karyawan::where('nik', $cuti->nik)->lockForUpdate()->first();
            if (! $karyawanLocked) {
                DB::rollBack();

                return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.'];
            }

            // Hanya record approved yang mengurangi kuota.
            $terpakaiLain = (int) $karyawanLocked->cuti()->approved()
                ->where('id', '!=', $cuti->id)
                ->sum('durasi_hari');
            $jatah = (int) $karyawanLocked->jatah_cuti;

            if ($terpakaiLain + $durasi > $jatah) {
                DB::rollBack();
                $sisaTanpaIni = max(0, $jatah - $terpakaiLain);

                return [
                    'success' => false,
                    'message' => "Durasi melebihi sisa kuota cuti. Maksimal durasi: {$sisaTanpaIni} hari.",
                ];
            }

            $dupes = self::conflictingDates($cuti->nik, $tanggalCuti, $cuti->id);
            if ($dupes !== []) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Tanggal '.implode(', ', $dupes).' sudah pernah dipilih cuti.',
                ];
            }

            $wasApproved = $cuti->status === CutiStatus::Approved;

            $cuti->update([
                'durasi_hari' => $durasi,
                'tanggal_cuti' => $tanggalCuti,
                'keterangan' => $request->keterangan,
            ]);

            // Formulir PDF selalu disinkronkan dengan data terbaru.
            $pdfPath = $this->generateFormPdf($cuti, $karyawanLocked);
            $cuti->update(['pdf_form_path' => $pdfPath]);

            DB::commit();

            Log::info('updateCuti SUCCESS', [
                'id' => $cuti->id,
                'nik' => $cuti->nik,
                'was_approved' => $wasApproved,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateCuti failed: '.$e->getMessage());

            return ['success' => false, 'message' => 'Gagal memperbarui data cuti.'];
        }

        return ['success' => true, 'message' => 'Data cuti berhasil diperbarui.'];
    }

    public function deleteCuti(int $id): array
    {
        $cuti = Cuti::find($id);
        if (! $cuti) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }

        DB::beginTransaction();
        try {
            self::deleteCutiFile($cuti);
            $this->deleteFormPdf($cuti);
            $cuti->delete();
            DB::commit();

            Log::info('deleteCuti SUCCESS', ['id' => $id, 'nik' => $cuti->nik]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteCuti failed: '.$e->getMessage());

            return ['success' => false, 'message' => 'Gagal menghapus data cuti.'];
        }

        return ['success' => true, 'message' => 'Data cuti berhasil dihapus. Kuota cuti (jika ada yang terpakai) otomatis kembali.'];
    }

    // ==================================================================
    // HELPERS — kuota, tanggal, file, PDF, notifikasi
    // ==================================================================

    /**
     * Sisa kuota karyawan (hanya cuti approved).
     */
    public static function sisaKuota(Karyawan $karyawan): int
    {
        return max(0, (int) $karyawan->jatah_cuti - (int) $karyawan->cuti()->approved()->sum('durasi_hari'));
    }

    /**
     * @return array<int, string> tanggal YYYY-MM-DD yang sudah dipakai cuti aktif
     *                             (draft / pending / approved — rejected tidak memblokir)
     */
    public static function getUsedDates(string $nik, ?int $excludeCutiId = null): array
    {
        $query = Cuti::where('nik', $nik)->active();
        if ($excludeCutiId !== null) {
            $query->where('id', '!=', $excludeCutiId);
        }

        $used = [];
        foreach ($query->pluck('tanggal_cuti') as $raw) {
            $list = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);
            foreach ($list as $t) {
                if (! empty($t)) {
                    $used[$t] = true;
                }
            }
        }

        return array_keys($used);
    }

    /**
     * @param  array<int, string>  $tanggalCuti
     * @return array<int, string> tanggal yang sudah dipakai cuti lain
     */
    public static function conflictingDates(string $nik, array $tanggalCuti, ?int $excludeCutiId = null): array
    {
        $used = self::getUsedDates($nik, $excludeCutiId);

        return array_values(array_intersect($tanggalCuti, $used));
    }

    /**
     * Generate formulir surat cuti (kolom tanda tangan kosong, tanpa stempel).
     */
    private function generateFormPdf(Cuti $cuti, Karyawan $karyawan): string
    {
        $jabatan = $karyawan->jabatan instanceof \App\Enums\Jabatan
            ? $karyawan->jabatan->value
            : ($karyawan->jabatan ?? '-');

        $data = [
            'headerSuratPath' => 'assets/img/header-surat.png',
            'nik' => $cuti->nik,
            'nama_lengkap' => $karyawan->nama_lengkap,
            'jabatan' => $jabatan,
            'posisi' => $karyawan->posisi ?? '-',
            'unit' => $karyawan->unit ?? '-',
            'perusahaan' => $karyawan->unitperusahaan?->perusahaan ?? '-',
            'durasi_hari' => $cuti->durasi_hari,
            'tanggalList' => array_values((array) $cuti->tanggal_cuti),
            'keterangan' => $cuti->keterangan,
        ];

        // Tanpa stempel: kolom tanda tangan dibuat kosong untuk tanda tangan manual.
        return $this->pdf->generateWithCustomPath(
            $data,
            'admin.presensi.pengajuan-cuti-pdf',
            self::FOLDER,
            'form-cuti'
        );
    }

    /**
     * Path PDF formulir milik karyawan tertentu (untuk download/print).
     */
    public function getPdfForKaryawan(int $id, string $nik): ?string
    {
        $cuti = Cuti::find($id);
        if (! $cuti || $cuti->nik !== $nik || empty($cuti->pdf_form_path)) {
            return null;
        }

        return Storage::disk('public')->exists($cuti->pdf_form_path) ? $cuti->pdf_form_path : null;
    }

    /**
     * Path PDF formulir untuk panel admin (permission cuti-view).
     */
    public function getPdfForAdmin(int $id): ?string
    {
        $cuti = Cuti::find($id);
        if (! $cuti || empty($cuti->pdf_form_path)) {
            return null;
        }

        return Storage::disk('public')->exists($cuti->pdf_form_path) ? $cuti->pdf_form_path : null;
    }

    private function deleteFormPdf(Cuti $cuti): void
    {
        try {
            if (! empty($cuti->pdf_form_path)) {
                Storage::disk('public')->delete($cuti->pdf_form_path);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete cuti form pdf: '.$e->getMessage());
        }
    }

    public static function showFileCuti(string $file, ?string $nik = null): ?string
    {
        $file = basename($file);
        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
            return null;
        }

        $rel = self::FOLDER.'/'.$file;
        if (! Storage::disk('public')->exists($rel)) {
            return null;
        }

        if ($nik === null) {
            return null;
        }

        $exists = Cuti::where('nik', $nik)->where('bukti_file', $file)->exists();
        if (! $exists) {
            return null;
        }

        return $rel;
    }

    public static function deleteCutiFile(Cuti $cuti): void
    {
        try {
            if (! empty($cuti->bukti_file)) {
                Storage::disk('public')->delete(self::FOLDER.'/'.$cuti->bukti_file);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete cuti file: '.$e->getMessage());
        }
    }

    /**
     * Notifikasi database ke semua user super_admin + webpush tidak tersedia
     * untuk guard user (tidak ada NIK), jadi cukup database notification.
     */
    private function notifySuperAdmins(Cuti $cuti, ?Karyawan $pengaju): void
    {
        try {
            if (! $pengaju) {
                return;
            }

            $admins = User::role('super_admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\CutiSubmitted($cuti, $pengaju));
            }
        } catch (\Exception $e) {
            Log::warning('CutiSubmitted notification failed: '.$e->getMessage());
        }
    }

    /**
     * Notifikasi ke karyawan pengaju (database + web push).
     */
    private function notifyKaryawan(Cuti $cuti, string $type): void
    {
        try {
            $karyawan = Karyawan::where('nik', $cuti->nik)->first();
            if (! $karyawan) {
                return;
            }

            $tanggal = collect((array) $cuti->tanggal_cuti)->implode(', ');

            if ($type === 'approved') {
                $karyawan->notify(new \App\Notifications\CutiApproved($cuti));
                $this->push->send(
                    $cuti->nik,
                    'Cuti Disetujui',
                    'Pengajuan cuti '.$cuti->durasi_hari.' hari ('.$tanggal.') telah disetujui.',
                    '/cuti',
                    'cuti-approved-'.$cuti->id
                );
            } elseif ($type === 'rejected') {
                $karyawan->notify(new \App\Notifications\CutiRejected($cuti));
                $this->push->send(
                    $cuti->nik,
                    'Cuti Ditolak',
                    'Pengajuan cuti ('.$tanggal.') ditolak: '.($cuti->rejected_reason ?: '-'),
                    '/cuti',
                    'cuti-rejected-'.$cuti->id
                );
            }
        } catch (\Exception $e) {
            Log::warning('Cuti notification failed: '.$e->getMessage());
        }
    }
}
