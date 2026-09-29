<?php

namespace App\Http\Middleware;

use App\Enums\ActivityAction;
use App\Enums\ActivityModule;
use App\Models\AdminActivityLog;
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
     * "{id}" akan diganti dengan id/nik target dari route parameter.
     * Path yang tidak terdaftar di sini tetap dicatat lewat fallback.
     *
     * @var array<int, array{0: string, 1: ActivityModule, 2: ActivityAction, 3: string}>
     */
    private const ROUTE_MAP = [
        // Pengguna administrator
        ['#^/users/store$#', ActivityModule::User, ActivityAction::Create, 'Membuat pengguna administrator baru'],
        ['#^/users/\d+/resetpassword$#', ActivityModule::User, ActivityAction::ResetPassword, 'Meriset password pengguna administrator #{id}'],
        ['#^/users/\d+/update$#', ActivityModule::User, ActivityAction::Update, 'Memperbarui data pengguna administrator #{id}'],
        ['#^/users/\d+/delete$#', ActivityModule::User, ActivityAction::Delete, 'Menghapus data pengguna administrator #{id}'],
        ['#^/api/admin/permissions/toggle$#', ActivityModule::User, ActivityAction::TogglePermission, 'Mengubah pengaturan permission pengguna administrator'],

        // Unit perusahaan
        ['#^/unitperusahaan/store$#', ActivityModule::Unit, ActivityAction::Create, 'Membuat data unit perusahaan baru'],
        ['#^/unitperusahaan/\d+/update$#', ActivityModule::Unit, ActivityAction::Update, 'Memperbarui data unit perusahaan #{id}'],
        ['#^/unitperusahaan/\d+/delete$#', ActivityModule::Unit, ActivityAction::Delete, 'Menghapus data unit perusahaan #{id}'],

        // Karyawan
        ['#^/karyawan/store$#', ActivityModule::Karyawan, ActivityAction::Create, 'Membuat data karyawan baru'],
        ['#^/karyawan/[^/]+/update$#', ActivityModule::Karyawan, ActivityAction::Update, 'Memperbarui data karyawan {id}'],
        ['#^/karyawan/[^/]+/delete$#', ActivityModule::Karyawan, ActivityAction::Delete, 'Menghapus data karyawan {id}'],

        // Izin
        ['#^/presensi/dataizin/\d+/approve$#', ActivityModule::Izin, ActivityAction::Approve, 'Menyetujui pengajuan izin #{id}'],
        ['#^/presensi/dataizin/\d+/reject$#', ActivityModule::Izin, ActivityAction::Reject, 'Menolak pengajuan izin #{id}'],
        ['#^/presensi/dataizin/\d+/delete$#', ActivityModule::Izin, ActivityAction::Delete, 'Menghapus data izin #{id}'],
        ['#^/presensi/izin/\d+/update$#', ActivityModule::Izin, ActivityAction::Update, 'Memperbarui data izin #{id}'],

        // Lembur
        ['#^/presensi/datalembur/\d+/approve-laporan-admin$#', ActivityModule::Lembur, ActivityAction::Approve, 'Menyetujui laporan lembur #{id}'],
        ['#^/presensi/datalembur/\d+/reject-laporan-admin$#', ActivityModule::Lembur, ActivityAction::Reject, 'Menolak laporan lembur #{id}'],
        ['#^/presensi/datalembur/\d+/approve$#', ActivityModule::Lembur, ActivityAction::Approve, 'Menyetujui pengajuan lembur #{id}'],
        ['#^/presensi/datalembur/\d+/reject$#', ActivityModule::Lembur, ActivityAction::Reject, 'Menolak pengajuan lembur #{id}'],
        ['#^/presensi/datalembur/\d+/delete$#', ActivityModule::Lembur, ActivityAction::Delete, 'Menghapus data lembur #{id}'],
        ['#^/presensi/lembur/\d+/update$#', ActivityModule::Lembur, ActivityAction::Update, 'Memperbarui data lembur #{id}'],

        // WFH
        ['#^/presensi/datawfh/\d+/approve-laporan-admin$#', ActivityModule::Wfh, ActivityAction::Approve, 'Menyetujui laporan WFH #{id}'],
        ['#^/presensi/datawfh/\d+/reject-laporan-admin$#', ActivityModule::Wfh, ActivityAction::Reject, 'Menolak laporan WFH #{id}'],
        ['#^/presensi/datawfh/\d+/approve$#', ActivityModule::Wfh, ActivityAction::Approve, 'Menyetujui pengajuan WFH #{id}'],
        ['#^/presensi/datawfh/\d+/reject$#', ActivityModule::Wfh, ActivityAction::Reject, 'Menolak pengajuan WFH #{id}'],
        ['#^/presensi/datawfh/\d+/delete$#', ActivityModule::Wfh, ActivityAction::Delete, 'Menghapus data WFH #{id}'],
        ['#^/presensi/wfh/\d+/update$#', ActivityModule::Wfh, ActivityAction::Update, 'Memperbarui data WFH #{id}'],

        // Cuti
        ['#^/cuti/\d+/update$#', ActivityModule::Cuti, ActivityAction::Update, 'Memperbarui data cuti #{id}'],
        ['#^/cuti/\d+/delete$#', ActivityModule::Cuti, ActivityAction::Delete, 'Menghapus data cuti #{id}'],

        // Presensi
        ['#^/presensi/\d+/update$#', ActivityModule::Presensi, ActivityAction::Update, 'Memperbarui data presensi #{id}'],
        ['#^/presensi/\d+/delete$#', ActivityModule::Presensi, ActivityAction::Delete, 'Menghapus data presensi #{id}'],
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

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request)) {
            $this->log($request, $response);
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

    private function log(Request $request, Response $response): void
    {
        try {
            $user = Auth::guard('user')->user();
            $path = '/'.$request->path();
            [$module, $action, $template] = $this->resolve($path);
            $entityId = $this->entityId($request);

            AdminActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role' => $user->getRoleNames()->first(),
                'module' => $module,
                'action' => $action,
                'entity_id' => $entityId,
                'description' => str_replace('{id}', $entityId ?? '-', $template),
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
