<?php

namespace App\Services;

use App\Models\Cuti;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CutiService
{
    public const MAX_DURASI_PER_UPLOAD = 3;

    public const JATAH_CUTI_MAX = 12;

    public const FOLDER = 'uploads/cuti';

    public function storeCuti(Request $request, Karyawan $karyawan): array
    {
        $nik = $karyawan->nik;
        $durasi = (int) $request->durasi_hari;
        $tanggalCuti = array_values(array_filter((array) $request->tanggal_cuti, fn ($t) => ! empty($t)));

        if (count($tanggalCuti) !== $durasi) {
            return ['success' => false, 'message' => 'Jumlah tanggal harus sesuai dengan durasi cuti.'];
        }

        $buktiPath = null;

        DB::beginTransaction();
        try {
            $karyawanLocked = Karyawan::where('nik', $nik)->lockForUpdate()->first();
            if (! $karyawanLocked) {
                DB::rollBack();

                return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.'];
            }

            $terpakai = (int) Cuti::where('nik', $nik)->sum('durasi_hari');
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

            if ($request->hasFile('bukti_file')) {
                $file = $request->file('bukti_file');
                $namaFile = now('Asia/Jakarta')->format('YmdHis').'-'.$nik.'.'.$file->getClientOriginalExtension();
                $file->storeAs(self::FOLDER, $namaFile, 'public');
                $buktiPath = $namaFile;
            }

            $cuti = Cuti::create([
                'nik' => $nik,
                'durasi_hari' => $durasi,
                'tanggal_cuti' => $tanggalCuti,
                'keterangan' => $request->keterangan,
                'bukti_file' => $buktiPath,
                'dikirim_tanggal' => now('Asia/Jakarta'),
            ]);

            DB::commit();

            $sisaBaru = max(0, $sisa - $durasi);
            Log::info('storeCuti SUCCESS', ['id' => $cuti->id, 'nik' => $nik, 'durasi' => $durasi, 'sisa' => $sisaBaru]);

            return [
                'success' => true,
                'message' => "Cuti berhasil diupload. Sisa cuti Anda: {$sisaBaru} hari.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if ($buktiPath) {
                try {
                    Storage::disk('public')->delete(self::FOLDER.'/'.$buktiPath);
                } catch (\Exception $cleanupEx) {
                    Log::warning('storeCuti: failed to cleanup file: '.$cleanupEx->getMessage());
                }
            }
            Log::error('storeCuti FAILED', ['nik' => $nik, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Gagal mengupload cuti. Silakan coba lagi.'];
        }
    }

    public function getHistory(string $nik)
    {
        return Cuti::where('nik', $nik)
            ->orderBy('dikirim_tanggal', 'desc')
            ->get();
    }

    /**
     * @return array<int, string> tanggal YYYY-MM-DD yang sudah dipakai cuti karyawan
     */
    public static function getUsedDates(string $nik, ?int $excludeCutiId = null): array
    {
        $query = Cuti::where('nik', $nik);
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

        if (! empty($request->tanggal)) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('dikirim_tanggal', $request->tanggal)
                    ->orWhereJsonContains('tanggal_cuti', $request->tanggal);
            });
        }

        return $query->orderBy('dikirim_tanggal', 'desc')->paginate(10)->withQueryString();
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

            $terpakaiLain = (int) Cuti::where('nik', $cuti->nik)
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

            $cuti->update([
                'durasi_hari' => $durasi,
                'tanggal_cuti' => $tanggalCuti,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
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
            $cuti->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteCuti failed: '.$e->getMessage());

            return ['success' => false, 'message' => 'Gagal menghapus data cuti.'];
        }

        return ['success' => true, 'message' => 'Data cuti berhasil dihapus dan kuota cuti dikembalikan.'];
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
}
