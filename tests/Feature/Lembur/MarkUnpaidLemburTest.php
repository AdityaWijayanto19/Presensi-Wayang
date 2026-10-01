<?php

namespace Tests\Feature\Lembur;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Notifications\LemburMarkedUnpaid;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MarkUnpaidLemburTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-09-28';

    private const KEMARIN = '2026-09-27';

    private Unitperusahaan $unit;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        Karyawan::create([
            'nik' => 'MUNATS001',
            'nama_lengkap' => 'Atasan Mark Unpaid',
            'jabatan' => 'Manager',
            'posisi' => 'Manager IT',
            'role_approved' => 'Manager',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567891',
            'password' => bcrypt('password'),
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'MUN001',
            'nama_lengkap' => 'Karyawan Mark Unpaid',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => 'MUNATS001',
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567890',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function setTime(string $jam): void
    {
        Carbon::setTestNow(Carbon::parse(self::HARI_INI.' '.$jam, 'Asia/Jakarta'));
    }

    private function insertLembur(array $atribut = []): int
    {
        return DB::table('lemburs')->insertGetId(array_merge([
            'nik' => $this->karyawan->nik,
            'tgl_lembur' => self::KEMARIN,
            'keterangan' => 'Lembur test',
            'status' => 'approved',
            'atasan_nik' => 'MUNATS001',
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'laporan_deskripsi' => null,
            'laporan_status' => null,
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ], $atribut));
    }

    private function statusLembur(int $id): ?string
    {
        return DB::table('lemburs')->where('id', $id)->value('status');
    }

    public function test_menandai_laporan_belum_diupload_sebagai_unpaid(): void
    {
        $this->setTime('00:00:05');
        Notification::fake();
        $id = $this->insertLembur();

        $this->artisan('lembur:mark-unpaid')->assertExitCode(0);

        $this->assertSame('unpaid', $this->statusLembur($id));
        Notification::assertSentTo(
            $this->karyawan,
            LemburMarkedUnpaid::class,
            fn (LemburMarkedUnpaid $n) => $n->reason === 'belum upload laporan'
        );
    }

    public function test_menandai_laporan_ditolak_belum_diperbaiki_sebagai_unpaid(): void
    {
        $this->setTime('00:00:05');
        Notification::fake();
        $id = $this->insertLembur([
            'laporan_deskripsi' => 'Hasil kerja lembur',
            'laporan_status' => 'rejected',
        ]);

        $this->artisan('lembur:mark-unpaid')->assertExitCode(0);

        $this->assertSame('unpaid', $this->statusLembur($id));
        Notification::assertSentTo(
            $this->karyawan,
            LemburMarkedUnpaid::class,
            fn (LemburMarkedUnpaid $n) => $n->reason === 'laporan ditolak dan belum diperbaiki'
        );
    }

    public function test_tidak_menandai_laporan_sudah_dikirim_pending(): void
    {
        $this->setTime('00:00:05');
        Notification::fake();
        $id = $this->insertLembur([
            'laporan_deskripsi' => 'Hasil kerja lembur',
            'laporan_status' => 'pending_atasan',
        ]);

        $this->artisan('lembur:mark-unpaid')->assertExitCode(0);

        $this->assertSame('approved', $this->statusLembur($id));
        Notification::assertNotSentTo($this->karyawan, LemburMarkedUnpaid::class);
    }

    public function test_tidak_menandai_laporan_disetujui(): void
    {
        $this->setTime('00:00:05');
        Notification::fake();
        $id = $this->insertLembur([
            'laporan_deskripsi' => 'Hasil kerja lembur',
            'laporan_status' => 'approved',
        ]);

        $this->artisan('lembur:mark-unpaid')->assertExitCode(0);

        $this->assertSame('approved', $this->statusLembur($id));
        Notification::assertNotSentTo($this->karyawan, LemburMarkedUnpaid::class);
    }

    public function test_tidak_menandai_lembur_belum_tanggal_lewat(): void
    {
        $this->setTime('23:59:00');
        Notification::fake();
        $id = $this->insertLembur(['tgl_lembur' => self::HARI_INI]);

        $this->artisan('lembur:mark-unpaid')->assertExitCode(0);

        $this->assertSame('approved', $this->statusLembur($id));
        Notification::assertNotSentTo($this->karyawan, LemburMarkedUnpaid::class);
    }

    public function test_tidak_menandai_lembur_ditolak(): void
    {
        $this->setTime('00:00:05');
        Notification::fake();
        $id = $this->insertLembur([
            'status' => 'rejected',
            'rejected_reason' => 'Jadwal bentrok',
        ]);

        $this->artisan('lembur:mark-unpaid')->assertExitCode(0);

        $this->assertSame('rejected', $this->statusLembur($id));
        Notification::assertNotSentTo($this->karyawan, LemburMarkedUnpaid::class);
    }
}
