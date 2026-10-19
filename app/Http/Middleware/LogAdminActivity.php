<?php

namespace App\Http\Middleware;

use App\Enums\ActivityAction;
use App\Enums\ActivityModule;
use App\Models\AdminActivityLog;
use App\Models\Cuti;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Models\User;
use App\Models\Wfh;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    /**
     * Metode HTTP yang dianggap sebagai aksi perubahan data.
     */
    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Path dengan metode tulis yang hanya membaca data (bukan perubahan),
     * sehingga tidak dicatat sebagai aktivitas.
     */
    private const READ_ONLY_PATHS = [
        '#^/getpresensi$#',
        '#^/tampilkanpetamasuk$#',
        '#^/tampilkanpetapulang$#',
        '#^/getkaryawanbyunit$#',
        '#^/presensi/previewlaporan$#',
        '#^/presensi/cetaklaporan$#',
        '#/edit$#',
        '#^/api/realtime/#',
    ];

    /**
     * Peta path route -> [module, action, description].
     * Placeholder yang tersedia:
     *   {id}        -> id/nik target dari route parameter
     *   {nama}      -> nama target (karyawan/pengguna/unit) bila bisa dikenali
     *   {tanggal}   -> tanggal data target (izin/lembur/wfh/cuti/presensi)
     *   {permission}-> nama permission yang diubah
     * Path yang tidak terdaftar di sini tetap dicatat lewat fallback.
     *
     * @var array<int, array{0: string, 1: ActivityModule, 2: ActivityAction, 3: string}>
     */
    private const ROUTE_MAP = [
        // Pengguna administrator
        ['#^/users/store$#', ActivityModule::User, ActivityAction::Create, 'Membuat pengguna administrator baru {nama}'],
        ['#^/users/\d+/resetpassword$#', ActivityModule::User, ActivityAction::ResetPassword, 'Meriset password pengguna administrator #{id} ({nama})'],
        ['#^/users/\d+/update$#', ActivityModule::User, ActivityAction::Update, 'Memperbarui data pengguna administrator #{id} ({nama})'],
        ['#^/users/\d+/delete$#', ActivityModule::User, ActivityAction::Delete, 'Menghapus data pengguna administrator #{id} ({nama})'],
        ['#^/api/admin/permissions/toggle$#', ActivityModule::User, ActivityAction::TogglePermission, 'Mengubah pengaturan permission "{permission}" pengguna administrator'],

        // Unit perusahaan
        ['#^/unitperusahaan/store$#', ActivityModule::Unit, ActivityAction::Create, 'Membuat data unit perusahaan baru {nama}'],
        ['#^/unitperusahaan/\d+/update$#', ActivityModule::Unit, ActivityAction::Update, 'Memperbarui data unit perusahaan #{id} ({nama})'],
        ['#^/unitperusahaan/\d+/delete$#', ActivityModule::Unit, ActivityAction::Delete, 'Menghapus data unit perusahaan #{id} ({nama})'],

        // Karyawan
        ['#^/karyawan/store$#', ActivityModule::Karyawan, ActivityAction::Create, 'Membuat data karyawan baru {nama}'],
        ['#^/karyawan/[^/]+/update$#', ActivityModule::Karyawan, ActivityAction::Update, 'Memperbarui data karyawan {id} ({nama})'],
        ['#^/karyawan/[^/]+/delete$#', ActivityModule::Karyawan, ActivityAction::Delete, 'Menghapus data karyawan {id} ({nama})'],

        // Izin
        ['#^/presensi/dataizin/\d+/approve$#', ActivityModule::Izin, ActivityAction::Approve, 'Menyetujui pengajuan izin #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/dataizin/\d+/reject$#', ActivityModule::Izin, ActivityAction::Reject, 'Menolak pengajuan izin #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/dataizin/\d+/delete$#', ActivityModule::Izin, ActivityAction::Delete, 'Menghapus data izin #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/izin/\d+/update$#', ActivityModule::Izin, ActivityAction::Update, 'Memperbarui data izin #{id} — {nama} (tanggal {tanggal})'],

        // Lembur
        ['#^/presensi/datalembur/\d+/approve-laporan-admin$#', ActivityModule::Lembur, ActivityAction::Approve, 'Menyetujui laporan lembur #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datalembur/\d+/reject-laporan-admin$#', ActivityModule::Lembur, ActivityAction::Reject, 'Menolak laporan lembur #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datalembur/\d+/approve$#', ActivityModule::Lembur, ActivityAction::Approve, 'Menyetujui pengajuan lembur #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datalembur/\d+/reject$#', ActivityModule::Lembur, ActivityAction::Reject, 'Menolak pengajuan lembur #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datalembur/\d+/delete$#', ActivityModule::Lembur, ActivityAction::Delete, 'Menghapus data lembur #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/lembur/\d+/update$#', ActivityModule::Lembur, ActivityAction::Update, 'Memperbarui data lembur #{id} — {nama} (tanggal {tanggal})'],

        // WFH
        ['#^/presensi/datawfh/\d+/approve-laporan-admin$#', ActivityModule::Wfh, ActivityAction::Approve, 'Menyetujui laporan WFH #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datawfh/\d+/reject-laporan-admin$#', ActivityModule::Wfh, ActivityAction::Reject, 'Menolak laporan WFH #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datawfh/\d+/approve$#', ActivityModule::Wfh, ActivityAction::Approve, 'Menyetujui pengajuan WFH #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datawfh/\d+/reject$#', ActivityModule::Wfh, ActivityAction::Reject, 'Menolak pengajuan WFH #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/datawfh/\d+/delete$#', ActivityModule::Wfh, ActivityAction::Delete, 'Menghapus data WFH #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/wfh/\d+/update$#', ActivityModule::Wfh, ActivityAction::Update, 'Memperbarui data WFH #{id} — {nama} (tanggal {tanggal})'],

        // Cuti
        ['#^/cuti/\d+/update$#', ActivityModule::Cuti, ActivityAction::Update, 'Memperbarui data cuti #{id} — {nama} (tanggal {tanggal})'],
        ['#^/cuti/\d+/delete$#', ActivityModule::Cuti, ActivityAction::Delete, 'Menghapus data cuti #{id} — {nama} (tanggal {tanggal})'],

        // Presensi
        ['#^/presensi/\d+/update$#', ActivityModule::Presensi, ActivityAction::Update, 'Memperbarui data presensi #{id} — {nama} (tanggal {tanggal})'],
        ['#^/presensi/\d+/delete$#', ActivityModule::Presensi, ActivityAction::Delete, 'Menghapus data presensi #{id} — {nama} (tanggal {tanggal})'],
    ];

    /**
     * Kunci path -> modul untuk route yang tidak terdaftar di ROUTE_MAP.
     * Urutan menentukan prioritas (kata kunci spesifik dulu).
     *
     * @var array<string, ActivityModule>
     */
    private const MODULE_KEYWORDS = [
        'users' => ActivityModule::User,
        'permissions' => ActivityModule::User,
        'karyawan' => ActivityModule::Karyawan,
        'unitperusahaan' => ActivityModule::Unit,
        'izin' => ActivityModule::Izin,
        'lembur' => ActivityModule::Lembur,
        'wfh' => ActivityModule::Wfh,
        'cuti' => ActivityModule::Cuti,
        'presensi' => ActivityModule::Presensi,
    ];

    /**
     * Nama bulan Indonesia (locale aplikasi masih 'en', jadi dipetakan manual).
     */
    private const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $context = null;

        if ($this->shouldLog($request)) {
            // Konteks entity (nama & tanggal data) diambil SEBELUM request
            // dieksekusi, agar aksi hapus tetap bisa mencatat nama target
            // yang record-nya sudah terlanjur terhapus.
            $context = $this->resolveContext($request);
        }

        $response = $next($request);

        if ($context !== null) {
            $this->log($request, $response, $context);
        }

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if (! in_array($request->method(), self::WRITE_METHODS, true)) {
            return false;
        }

        if (! Auth::guard('user')->check()) {
            return false;
        }

        $path = '/'.$request->path();

        foreach (self::READ_ONLY_PATHS as $pattern) {
            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Siapkan konteks log (module, action, template, target) sebelum request dieksekusi.
     *
     * @return array{module: ActivityModule, action: ActivityAction, template: string, entity_id: ?string, nama: ?string, tanggal: ?string, permission: ?string}
     */
    private function resolveContext(Request $request): array
    {
        $path = '/'.$request->path();
        [$module, $action, $template] = $this->resolve($path);
        $entityId = $this->entityId($request);
        $detail = $this->resolveEntityDetail($module, $entityId, $request);

        return [
            'module' => $module,
            'action' => $action,
            'template' => $template,
            'entity_id' => $entityId,
            'nama' => $detail['nama'],
            'tanggal' => $detail['tanggal'],
            'permission' => $this->stringOrNull($request->input('permission')),
        ];
    }

    private function log(Request $request, Response $response, array $context): void
    {
        try {
            $user = Auth::guard('user')->user();
            $path = '/'.$request->path();

            AdminActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role' => $user->getRoleNames()->first(),
                'module' => $context['module'],
                'action' => $context['action'],
                'entity_id' => $context['entity_id'],
                'description' => Str::limit($this->buildDescription($context), 250),
                'status' => $this->resolveStatus($request, $response),
                'method' => $request->method(),
                'path' => $path,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit($request->userAgent() ?? '', 500, ''),
            ]);
        } catch (\Throwable $e) {
            // Kegagalan mencatat log tidak boleh menggagalkan request utama.
            report($e);
        }
    }

    /**
     * Susun deskripsi aktivitas dari template + konteks entity.
     */
    private function buildDescription(array $context): string
    {
        $description = str_replace(
            ['{id}', '{nama}', '{tanggal}', '{permission}'],
            [
                $context['entity_id'] ?? '-',
                $context['nama'] ?? '-',
                $context['tanggal'] ?? '-',
                $context['permission'] ?? '-',
            ],
            $context['template']
        );

        // Template fallback tidak punya placeholder; tetap sertakan konteks
        // target bila berhasil dikenali agar keterangan tetap detail.
        if (! str_contains($context['template'], '{nama}') && $context['nama'] !== null) {
            $description .= ' — '.$context['nama'];
        }

        if (! str_contains($context['template'], '{tanggal}') && $context['tanggal'] !== null) {
            $description .= ' ('.$context['tanggal'].')';
        }

        return $description;
    }

    /**
     * @return array{0: ActivityModule, 1: ActivityAction, 2: string}
     */
    private function resolve(string $path): array
    {
        foreach (self::ROUTE_MAP as [$pattern, $module, $action, $description]) {
            if (preg_match($pattern, $path)) {
                return [$module, $action, $description];
            }
        }

        return $this->fallback($path);
    }

    /**
     * @return array{0: ActivityModule, 1: ActivityAction, 2: string}
     */
    private function fallback(string $path): array
    {
        $module = ActivityModule::Lainnya;
        foreach (self::MODULE_KEYWORDS as $keyword => $candidate) {
            if (str_contains($path, $keyword)) {
                $module = $candidate;
                break;
            }
        }

        $action = match (true) {
            str_contains($path, '/delete') => ActivityAction::Delete,
            str_contains($path, '/store'), str_contains($path, '/create') => ActivityAction::Create,
            str_contains($path, '/approve') => ActivityAction::Approve,
            str_contains($path, '/reject') => ActivityAction::Reject,
            str_contains($path, '/update') => ActivityAction::Update,
            default => ActivityAction::Other,
        };

        return [$module, $action, 'Melakukan aksi "'.$action->label().'" pada '.$path];
    }

    private function entityId(Request $request): ?string
    {
        $route = $request->route();

        if (! $route instanceof Route) {
            return null;
        }

        foreach (['id', 'id_user', 'nik'] as $name) {
            $parameter = $route->parameter($name);

            if ($parameter === null) {
                continue;
            }

            if (is_object($parameter)) {
                if (method_exists($parameter, 'getKey')) {
                    return (string) $parameter->getKey();
                }

                continue;
            }

            return (string) $parameter;
        }

        return null;
    }

    /**
     * Ambil nama target + tanggal data target sesuai modul.
     *
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function resolveEntityDetail(ActivityModule $module, ?string $entityId, Request $request): array
    {
        $empty = ['nama' => null, 'tanggal' => null];

        try {
            // Aksi create/store: record belum ada, ambil nama dari input form.
            if ($entityId === null) {
                return [
                    'nama' => match ($module) {
                        ActivityModule::Karyawan => $this->stringOrNull($request->input('nama_lengkap')),
                        ActivityModule::User => $this->stringOrNull($request->input('nama_user')),
                        ActivityModule::Unit => $this->stringOrNull($request->input('unit')),
                        default => null,
                    },
                    'tanggal' => null,
                ];
            }

            return match ($module) {
                ActivityModule::Karyawan => $this->karyawanDetail($entityId),
                ActivityModule::User => $this->userDetail($entityId),
                ActivityModule::Unit => $this->unitDetail($entityId),
                ActivityModule::Izin => $this->izinDetail($entityId),
                ActivityModule::Lembur => $this->lemburDetail($entityId),
                ActivityModule::Wfh => $this->wfhDetail($entityId),
                ActivityModule::Cuti => $this->cutiDetail($entityId),
                ActivityModule::Presensi => $this->presensiDetail($entityId),
                default => $empty,
            };
        } catch (\Throwable $e) {
            // Konteks bersifat pelengkap; kegagalan lookup tidak boleh merusak log.
            return $empty;
        }
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function karyawanDetail(string $nik): array
    {
        $karyawan = Karyawan::query()->where('nik', $nik)->first(['nik', 'nama_lengkap']);

        return ['nama' => $karyawan?->nama_lengkap, 'tanggal' => null];
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function userDetail(string $id): array
    {
        $user = User::query()->find($id, ['id', 'name']);

        return ['nama' => $user?->name, 'tanggal' => null];
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function unitDetail(string $id): array
    {
        $unit = Unitperusahaan::query()->find($id, ['id', 'unit']);

        return ['nama' => $unit?->unit, 'tanggal' => null];
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function izinDetail(string $id): array
    {
        $izin = Izin::query()->with('karyawan:nik,nama_lengkap')->find($id, ['nik', 'tgl_izin']);

        return [
            'nama' => $izin?->karyawan?->nama_lengkap,
            'tanggal' => $this->formatTanggal($izin?->tgl_izin),
        ];
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function lemburDetail(string $id): array
    {
        $lembur = Lembur::query()->with('karyawan:nik,nama_lengkap')->find($id, ['nik', 'tgl_lembur']);

        return [
            'nama' => $lembur?->karyawan?->nama_lengkap,
            'tanggal' => $this->formatTanggal($lembur?->tgl_lembur),
        ];
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function wfhDetail(string $id): array
    {
        $wfh = Wfh::query()->with('karyawan:nik,nama_lengkap')->find($id, ['nik', 'tgl_wfh']);

        return [
            'nama' => $wfh?->karyawan?->nama_lengkap,
            'tanggal' => $this->formatTanggal($wfh?->tgl_wfh),
        ];
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function cutiDetail(string $id): array
    {
        $cuti = Cuti::query()->with('karyawan:nik,nama_lengkap')->find($id, ['nik', 'tanggal_cuti']);

        return [
            'nama' => $cuti?->karyawan?->nama_lengkap,
            'tanggal' => $this->formatTanggalList((array) ($cuti?->tanggal_cuti ?? [])),
        ];
    }

    /**
     * @return array{nama: ?string, tanggal: ?string}
     */
    private function presensiDetail(string $id): array
    {
        $presensi = Presensi::query()->with('karyawan:nik,nama_lengkap')->find($id, ['nik', 'tgl_presensi']);

        return [
            'nama' => $presensi?->karyawan?->nama_lengkap,
            'tanggal' => $this->formatTanggal($presensi?->tgl_presensi),
        ];
    }

    /**
     * Format tanggal tunggal menjadi bahasa Indonesia, contoh: "01 Oktober 2026".
     */
    private function formatTanggal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $date = $value instanceof \DateTimeInterface ? Carbon::instance($value) : Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }

        return sprintf('%02d %s %d', $date->day, self::BULAN[$date->month], $date->year);
    }

    /**
     * Format daftar tanggal (cuti bisa beberapa hari), contoh:
     * "01 Oktober 2026" atau "01 Oktober 2026 s.d. 03 Oktober 2026".
     */
    private function formatTanggalList(array $tanggal): ?string
    {
        $tanggal = array_values(array_filter($tanggal, fn ($t) => ! empty($t)));

        if ($tanggal === []) {
            return null;
        }

        if (count($tanggal) === 1) {
            return $this->formatTanggal($tanggal[0]);
        }

        $pertama = $this->formatTanggal($tanggal[0]);
        $terakhir = $this->formatTanggal(end($tanggal));

        if ($pertama === null || $terakhir === null) {
            return $pertama ?? $terakhir;
        }

        return $pertama === $terakhir ? $pertama : $pertama.' s.d. '.$terakhir;
    }

    private function stringOrNull($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function resolveStatus(Request $request, Response $response): string
    {
        if ($response->getStatusCode() >= 400) {
            return AdminActivityLog::STATUS_FAILED;
        }

        if ($request->hasSession()) {
            // Hanya flash yang dibuat pada request ini (bukan sisa request sebelumnya).
            $flashed = $request->session()->get('_flash.new', []);

            if (in_array('error', $flashed, true) || in_array('errors', $flashed, true)) {
                return AdminActivityLog::STATUS_FAILED;
            }
        }

        return AdminActivityLog::STATUS_SUCCESS;
    }
}
