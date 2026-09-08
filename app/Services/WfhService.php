<?php

namespace App\Services;

use App\Enums\Jabatan;
use App\Enums\WfhStatus;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Models\Wfh;
use App\Models\PushSubscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Minishlink\WebPush\WebPush;

class WfhService
{
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
                'laporan_status' => 'pending_admin',
                'laporan_atasan_status' => 'pending',
                'laporan_admin_status' => 'pending',
            ];
        }
        return [
            'laporan_status' => 'pending_atasan',
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
            && $wfh->status === WfhStatus::PendingAtasan->value
            && $wfh->atasan_status === 'pending';
    }

    public static function canApproveAdmin(Wfh $wfh): bool
    {
        return $wfh->status === WfhStatus::PendingAdmin->value
            && $wfh->admin_status === 'pending';
    }

    public static function canLapor($presensi): bool
    {
        if (!$presensi || !$presensi->jam_in) return false;
        $jamMasuk = \Carbon\Carbon::parse($presensi->jam_in);
        $selisihJam = $jamMasuk->diffInHours(now());
        return $selisihJam >= 7;
    }

    public static function getWfhHistory(string $nik)
    {
        return Wfh::with('atasan')
            ->where('nik', $nik)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', WfhStatus::Approved->value)
                        ->whereIn('laporan_status', ['approved', 'rejected']);
                });
                $q->orWhere('status', WfhStatus::Rejected->value);
                $q->orWhere('status', WfhStatus::Unpaid->value);
            })
            ->orderBy('tgl_wfh', 'desc')
            ->get();
    }

    public static function getDataWfhAdmin(Request $request): array
    {
        $query = Wfh::with(['karyawan.unitperusahaan', 'atasan']);

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
        if (!empty($request->tanggal)) {
            $query->where('tgl_wfh', $request->tanggal);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        $datawfh = $query->orderBy('tgl_wfh', 'desc')->paginate(5)->withQueryString();
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();
        $pendingWfhAdmin = Wfh::where('status', 'pending_admin')->count();
        $pendingLaporanAdmin = Wfh::where('laporan_status', 'pending_admin')->count();

        return compact('datawfh', 'unitperusahaan', 'pendingWfhAdmin', 'pendingLaporanAdmin');
    }

    public static function storeWfh(Request $request, Karyawan $karyawan): array
    {
        $nik = $karyawan->nik;

        $request->validate([
            'tgl_wfh' => 'required|date|after_or_equal:today',
            'keterangan' => 'required|string|min:5|max:1000',
            'deskripsi_pekerjaan' => 'required|string|min:10|max:2000',
        ]);

        $cek = Wfh::where('nik', $nik)->where('tgl_wfh', $request->tgl_wfh)->exists();
        if ($cek) {
            return ['success' => false, 'message' => 'Anda sudah mengajukan WFH pada tanggal tersebut!'];
        }

        $karyawanFresh = Karyawan::with('unitperusahaan')->where('nik', $nik)->first();
        $atasanNik = self::determineAtasanNik($karyawanFresh);
        $jabatan = $karyawanFresh->jabatan instanceof Jabatan ? $karyawanFresh->jabatan->value : $karyawanFresh->jabatan;
        $posisi = $karyawanFresh->posisi;

        $initial = self::initialStatus($karyawanFresh);
        $status = $initial['status'];
        $atasanStatus = $initial['atasan_status'];
        $adminStatus = $initial['admin_status'];

        $perusahaan = $karyawanFresh->unitperusahaan->perusahaan ?? '-';
        $atasan = $atasanNik ? Karyawan::where('nik', $atasanNik)->first() : null;

        $pdfData = [
            'headerSuratPath' => 'assets/img/header-surat.png',
            'nama_lengkap' => $karyawanFresh->nama_lengkap,
            'jabatan' => $jabatan instanceof Jabatan ? $jabatan->value : $jabatan,
            'posisi' => $posisi ?? '-',
            'perusahaan' => $perusahaan,
            'tgl_wfh' => $request->tgl_wfh,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
            'nama_atasan' => $atasan?->nama_lengkap ?? '-',
            'jabatan_atasan' => $atasan?->jabatan instanceof Jabatan ? $atasan->jabatan->value : ($atasan?->jabatan ?? '-'),
            'nama_approver' => '-',
            'jabatan_approver' => '-',
        ];

        $stempelPath = self::getStempelPath();

        DB::beginTransaction();
        try {
            $pdfPath = self::generatePdf($pdfData, $stempelPath);

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
                'pdf_form_path' => $pdfPath,
                'dikirim_tanggal' => now(),
            ]);

            if ($atasanNik) {
                try {
                    $atasanUser = Karyawan::where('nik', $atasanNik)->first();
                    if ($atasanUser) {
                        $atasanUser->notify(new \App\Notifications\WfhSubmitted($wfh, $karyawanFresh));
                        self::sendWebPush($atasanNik, 'Pengajuan WFH Baru', $karyawanFresh->nama_lengkap . ' mengajukan WFH ' . $request->tgl_wfh, '/presensi/datawfh', 'wfh-submitted-' . $wfh->id);
                    }
                } catch (\Exception $e) {
                    \Log::warning('WFH atasan notification failed: ' . $e->getMessage());
                }
            }

            DB::commit();
            cache()->forget('pending_wfh_count');
            cache()->forget('pending_wfh_admin_count');
            return ['success' => true, 'message' => 'Pengajuan WFH berhasil! Menunggu persetujuan.'];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('storewfh failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengajukan WFH. Silakan coba lagi.'];
        }
    }

    public static function deleteWfh(int $id, string $nik): array
    {
        $wfh = Wfh::where('id', $id)->where('nik', $nik)->first();
        if (!$wfh) {
            return ['success' => false, 'message' => 'Data tidak ditemukan!'];
        }

        if (!in_array($wfh->status, [WfhStatus::PendingAtasan->value, WfhStatus::PendingAdmin->value])) {
            return ['success' => false, 'message' => 'WFH yang sudah disetujui/ditolak tidak bisa dihapus!'];
        }

        self::deleteWfhFiles($wfh);
        $wfh->delete();

        return ['success' => true, 'message' => 'Data WFH berhasil dihapus!'];
    }

    public static function deleteWfhAdmin(int $id): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];

        self::deleteWfhFiles($wfh);
        $wfh->delete();
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Data WFH berhasil dihapus!'];
    }

    public static function approveWfhAtasan(int $id, Karyawan $karyawan): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];
        if ($wfh->atasan_nik !== $karyawan->nik) return ['success' => false, 'message' => 'Anda bukan atasan untuk pengajuan ini'];
        if ($wfh->status !== WfhStatus::PendingAtasan->value) return ['success' => false, 'message' => 'Status tidak valid'];

        $wfh->update([
            'atasan_status' => 'approved',
            'status' => WfhStatus::PendingAdmin->value,
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhApprovedByAtasan($wfh, $karyawan));
                self::sendWebPush($wfh->nik, 'WFH Disetujui', 'WFH ' . $wfh->tgl_wfh . ' disetujui, menunggu persetujuan selanjutnya', null, 'wfh-approved-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('WFH atasan approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'WFH disetujui, diteruskan ke Admin'];
    }

    public static function rejectWfhAtasan(int $id, string $rejectedReason, Karyawan $karyawan): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh || $wfh->atasan_nik !== $karyawan->nik) return ['success' => false, 'message' => 'Akses ditolak'];
        if ($wfh->status !== WfhStatus::PendingAtasan->value) return ['success' => false, 'message' => 'Status tidak valid untuk penolakan'];

        $wfh->update([
            'atasan_status' => 'rejected',
            'status' => WfhStatus::Rejected->value,
            'rejected_reason' => $rejectedReason,
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhRejected($wfh, $rejectedReason));
                self::sendWebPush($wfh->nik, 'WFH Ditolak', 'WFH ' . $wfh->tgl_wfh . ' ditolak: ' . $rejectedReason, null, 'wfh-rejected-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('WFH atasan rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');

        return ['success' => true, 'message' => 'WFH ditolak'];
    }

    public static function approveWfhAdmin(int $id): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];
        if ($wfh->status !== WfhStatus::PendingAdmin->value) return ['success' => false, 'message' => 'Status tidak valid untuk persetujuan'];
        if ($wfh->admin_status !== 'pending') return ['success' => false, 'message' => 'WFH ini sudah diproses'];

        $wfh->update([
            'admin_status' => 'approved',
            'status' => WfhStatus::Approved->value,
            'approved_at' => now(),
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhApproved($wfh));
                self::sendWebPush($wfh->nik, 'WFH Disetujui ', 'WFH ' . $wfh->tgl_wfh . ' disetujui! Silakan input Laporan.', '/presensi/wfh/' . $id . '/laporan', 'wfh-approved-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('WFH admin approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'WFH disetujui. Karyawan bisa input laporan.'];
    }

    public static function rejectWfhAdmin(int $id, string $rejectedReason): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];
        if ($wfh->status !== WfhStatus::PendingAdmin->value) return ['success' => false, 'message' => 'Status tidak valid untuk penolakan'];
        if ($wfh->admin_status !== 'pending') return ['success' => false, 'message' => 'WFH ini sudah diproses'];

        $wfh->update([
            'admin_status' => 'rejected',
            'status' => WfhStatus::Rejected->value,
            'rejected_reason' => $rejectedReason,
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\WfhRejected($wfh, $rejectedReason));
                self::sendWebPush($wfh->nik, 'WFH Ditolak', 'WFH ' . $wfh->tgl_wfh . ' ditolak Admin', null, 'wfh-rejected-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('WFH admin rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return ['success' => true, 'message' => 'WFH ditolak'];
    }

    public static function getLaporanData(int $id, string $nik): ?object
    {
        $wfh = Wfh::where('id', $id)->where('nik', $nik)->where('status', WfhStatus::Approved->value)->first();
        if (!$wfh) return null;

        $tglWfh = $wfh->tgl_wfh instanceof \Carbon\Carbon ? $wfh->tgl_wfh->format('Y-m-d') : date('Y-m-d', strtotime($wfh->tgl_wfh));
        $hariIni = date('Y-m-d');
        if ($tglWfh !== $hariIni) {
            return (object) ['error' => 'Laporan hanya bisa diupload pada tanggal WFH (' . date('d M Y', strtotime($tglWfh)) . '). Hari ini: ' . date('d M Y') . '.'];
        }

        $presensiToday = Presensi::where('nik', $nik)->where('tgl_presensi', $hariIni)->first();
        if (!$presensiToday || !$presensiToday->jam_in) {
            return (object) ['error' => 'Anda belum melakukan absen masuk hari ini. Silakan absen masuk terlebih dahulu.'];
        }

        $jamMasuk = \Carbon\Carbon::parse($presensiToday->jam_in);
        $selisihJam = $jamMasuk->diffInHours(now());
        if ($selisihJam < 7) {
            $sisa = ceil(7 - $selisihJam);
            return (object) ['error' => 'Laporan hanya bisa diisi setelah 7 jam absen masuk. Sisa waktu: ' . $sisa . ' jam.'];
        }

        return $wfh;
    }

    public static function storeLaporanWfh(Request $request, int $id, string $nik): array
    {
        $request->validate([
            'laporan_deskripsi' => 'required|string|min:10|max:3000',
            'laporan_images' => 'required|array|min:2|max:5',
            'laporan_images.*' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $wfh = Wfh::where('id', $id)->where('nik', $nik)->where('status', WfhStatus::Approved->value)->first();
        if (!$wfh) return ['success' => false, 'message' => 'Akses ditolak'];

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];

        $presensiToday = Presensi::where('nik', $nik)->where('tgl_presensi', date('Y-m-d'))->first();

        DB::beginTransaction();
        try {
            $imagePaths = [];
            if ($request->hasFile('laporan_images')) {
                foreach ($request->file('laporan_images') as $idx => $file) {
                    $nama = Str::uuid() . '-laporan-' . ($idx + 1) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('wfh/laporan', $nama, 'public');
                    $imagePaths[] = $path;
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
            $hariTanggal = $weekdayMap[now()->format('l')] . ', ' . now()->format('d F Y');

            $presensiService = new PresensiService();
            $liveLocation = $presensiService->reverseGeocode($presensiToday->lokasi_in ?? '-');

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

            $stempelPath = self::getStempelPath();

            $pdf = Pdf::loadView('presensi.laporan-pdf', array_merge($pdfData, ['stempelPath' => $stempelPath]));
            $pdf->setPaper('A4', 'portrait');
            $pdfFilename = Str::uuid() . '-laporan-' . Str::slug($karyawan->nama_lengkap) . '.pdf';
            $pdfPath = 'wfh/laporan/' . $pdfFilename;
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $wfh->update([
                'laporan_deskripsi' => $request->laporan_deskripsi,
                'laporan_images' => json_encode($imagePaths),
                'laporan_file' => $pdfPath,
                'laporan_atasan_nik' => $laporanAtasanNik,
                'laporan_status' => $initialLaporan['laporan_status'],
                'laporan_atasan_status' => $initialLaporan['laporan_atasan_status'],
                'laporan_admin_status' => $initialLaporan['laporan_admin_status'],
            ]);

            DB::commit();

            try {
                if ($laporanAtasanNik) {
                    $atasanUser = Karyawan::where('nik', $laporanAtasanNik)->first();
                    if ($atasanUser) {
                        $atasanUser->notify(new \App\Notifications\LaporanSubmitted($wfh->fresh(), $karyawan));
                        self::sendWebPush($laporanAtasanNik, 'Laporan WFH Diajukan', $karyawan->nama_lengkap . ' mengajukan laporan WFH ' . $wfh->tgl_wfh, null, 'laporan-submitted-' . $id);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Laporan submission notification failed: ' . $e->getMessage());
            }

            return ['success' => true, 'message' => 'Laporan WFH berhasil dikirim! Menunggu persetujuan atasan dan administrator.'];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('storeLaporanWfh failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengirim laporan WFH. Silakan coba lagi.'];
        }
    }

    public static function approveLaporanAtasan(int $id, Karyawan $karyawan): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];
        if ($wfh->laporan_atasan_nik !== $karyawan->nik) return ['success' => false, 'message' => 'Anda bukan atasan untuk laporan ini'];
        if ($wfh->laporan_status !== 'pending_atasan') return ['success' => false, 'message' => 'Status laporan tidak valid'];

        $wfh->update([
            'laporan_atasan_status' => 'approved',
            'laporan_status' => 'pending_admin',
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanApprovedByAtasan($wfh, $karyawan));
                self::sendWebPush($wfh->nik, 'Laporan Disetujui Atasan', 'Laporan WFH ' . $wfh->tgl_wfh . ' disetujui atasan, menunggu persetujuan HR', null, 'laporan-approved-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('Laporan atasan approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan disetujui atasan, diteruskan ke Admin'];
    }

    public static function rejectLaporanAtasan(int $id, string $rejectedReason, Karyawan $karyawan): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh || $wfh->laporan_atasan_nik !== $karyawan->nik) return ['success' => false, 'message' => 'Akses ditolak'];
        if ($wfh->laporan_status !== 'pending_atasan') return ['success' => false, 'message' => 'Status laporan tidak valid untuk penolakan'];

        $wfh->update([
            'laporan_atasan_status' => 'rejected',
            'laporan_status' => 'rejected',
            'laporan_rejected_reason' => $rejectedReason,
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanRejected($wfh, $rejectedReason));
                self::sendWebPush($wfh->nik, 'Laporan Ditolak Atasan', 'Laporan WFH ' . $wfh->tgl_wfh . ' ditolak atasan: ' . $rejectedReason, null, 'laporan-rejected-atasan-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('Laporan atasan rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan ditolak atasan'];
    }

    public static function approveLaporanAdmin(int $id): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];
        if ($wfh->laporan_status !== 'pending_admin') return ['success' => false, 'message' => 'Status laporan tidak valid untuk persetujuan'];
        if ($wfh->laporan_admin_status !== 'pending') return ['success' => false, 'message' => 'Laporan ini sudah diproses'];

        $wfh->update([
            'laporan_admin_status' => 'approved',
            'laporan_status' => 'approved',
            'laporan_approved_at' => now(),
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanApproved($wfh));
                self::sendWebPush($wfh->nik, 'Laporan Disetujui HR', 'Laporan WFH ' . $wfh->tgl_wfh . ' telah disetujui.', null, 'laporan-approved-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('Laporan admin approval notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan disetujui HR'];
    }

    public static function rejectLaporanAdmin(int $id, string $rejectedReason): array
    {
        $wfh = Wfh::find($id);
        if (!$wfh) return ['success' => false, 'message' => 'Data tidak ditemukan'];
        if ($wfh->laporan_status !== 'pending_admin') return ['success' => false, 'message' => 'Status laporan tidak valid untuk penolakan'];
        if ($wfh->laporan_admin_status !== 'pending') return ['success' => false, 'message' => 'Laporan ini sudah diproses'];

        $wfh->update([
            'laporan_admin_status' => 'rejected',
            'laporan_status' => 'rejected',
            'laporan_rejected_reason' => $rejectedReason,
        ]);

        $pengaju = Karyawan::where('nik', $wfh->nik)->first();
        try {
            if ($pengaju) {
                $pengaju->notify(new \App\Notifications\LaporanRejected($wfh, $rejectedReason));
                self::sendWebPush($wfh->nik, 'Laporan Ditolak Admin', 'Laporan WFH ' . $wfh->tgl_wfh . ' ditolak Admin', null, 'laporan-rejected-admin-' . $wfh->id);
            }
        } catch (\Exception $e) {
            \Log::warning('Laporan admin rejection notification failed: ' . $e->getMessage());
        }
        cache()->forget('pending_laporan_admin_count');

        return ['success' => true, 'message' => 'Laporan ditolak Admin'];
    }

    public static function getStempelPath(): ?string
    {
        if (file_exists(public_path('storage/uploads/stempel/stempel.png'))) {
            return 'storage/uploads/stempel/stempel.png';
        }
        if (file_exists(storage_path('app/template/stempel.png'))) {
            if (!is_dir(public_path('storage/uploads/stempel'))) {
                @mkdir(public_path('storage/uploads/stempel'), 0755, true);
            }
            @copy(storage_path('app/template/stempel.png'), public_path('storage/uploads/stempel/stempel.png'));
            return 'storage/uploads/stempel/stempel.png';
        }
        return null;
    }

    public static function deleteWfhFiles(Wfh $wfh): void
    {
        if (!empty($wfh->pdf_form_path)) {
            Storage::disk('public')->delete($wfh->pdf_form_path);
        }
        if (!empty($wfh->laporan_file)) {
            Storage::disk('public')->delete($wfh->laporan_file);
        }
    }

    public static function generatePdf(array $data, ?string $stempelPath = null): string
    {
        $pdf = Pdf::loadView('presensi.pengajuan-wfh-pdf', array_merge($data, ['stempelPath' => $stempelPath]));
        $pdf->setPaper('A4', 'portrait');
        $dir = 'wfh';
        $filename = Str::uuid() . '-' . Str::slug($data['nama_lengkap']) . '-wfh.pdf';
        $path = $dir . '/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());
        return $path;
    }

    public static function showFileWfh(string $file): ?string
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
                return $rel;
            }
        }

        $wfh = Wfh::where('pdf_form_path', 'like', '%/' . $file)
            ->orWhere('laporan_file', 'like', '%/' . $file)
            ->first();

        if ($wfh) {
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

    public static function sendWebPush(string $nik, string $title, string $body, ?string $url = null, ?string $tag = null): void
    {
        if (!config('webpush.enabled')) return;

        try {
            $subscriptions = PushSubscription::where('nik', $nik)->get();
            if ($subscriptions->isEmpty()) return;

            $auth = [
                'VAPID' => [
                    'subject' => config('webpush.vapid.subject'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ],
            ];

            $webPush = new WebPush($auth);

            foreach ($subscriptions as $sub) {
                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url ?? '/dashboard',
                    'tag' => $tag,
                ]);

                $subscription = new \Minishlink\WebPush\Subscription(
                    $sub->endpoint,
                    $sub->public_key,
                    $sub->auth_token
                );

                $webPush->sendOneNotification($subscription, $payload, ['TTL' => 3600]);
            }
        } catch (\Exception $e) {
            \Log::warning('Web push failed: ' . $e->getMessage());
        }
    }
}
