<?php

namespace Tests\Feature\Scheduler;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel;
use Tests\TestCase;

/**
 * Regresi untuk kasus production (Hostinger shared): PHP CLI menonaktifkan
 * proc_open (ada di disable_functions), sehingga Schedule::command() yang
 * menjalankan command sebagai SUBPROCESS (Symfony Process) gagal diam-diam —
 * schedule:run "sukses" tapi tidak ada command yang benar-benar jalan
 * (laravel.log: "The Process class relies on proc_open, which is not
 * available"). Semua event harus callback in-process (Schedule::call).
 */
class SchedulerInProcessTest extends TestCase
{
    /** Ekspresi cron yang diharapkan per command (waktu Asia/Jakarta). */
    private const JADWAL = [
        'wfh:mark-unpaid' => '0 0 * * *',
        'lembur:mark-unpaid' => '0 0 * * *',
        'wfh:reminder-laporan' => '0 22 * * *',
        'lembur:reminder-laporan' => '0 22 * * *',
        'activitylog:cleanup' => '15 3 * * *',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Paksa console kernel memuat routes/console.php (command routes).
        $this->app->make(Kernel::class)->bootstrap();
    }

    public function test_semua_event_scheduler_terdaftar(): void
    {
        $names = collect($this->events())->map->description->all();

        foreach (array_keys(self::JADWAL) as $command) {
            $this->assertContains(
                $command,
                $names,
                "Command '{$command}' tidak terdaftar di scheduler (routes/console.php)."
            );
        }
    }

    public function test_event_scheduler_memakai_callback_in_process_bukan_subprocess(): void
    {
        foreach ($this->events() as $event) {
            if (! in_array($event->description, array_keys(self::JADWAL), true)) {
                continue;
            }

            $this->assertInstanceOf(
                CallbackEvent::class,
                $event,
                "Event '{$event->description}' harus CallbackEvent (Schedule::call, in-process). "
                .'Schedule::command() butuh proc_open yang disabled di hosting production.'
            );

            $this->assertNotSame(
                Event::class,
                get_class($event),
                "Event '{$event->description}' tidak boleh berupa event command (subprocess)."
            );
        }
    }

    public function test_jadwal_setiap_command_tidak_berubah(): void
    {
        foreach ($this->events() as $event) {
            if (! isset(self::JADWAL[$event->description])) {
                continue;
            }

            $this->assertSame(
                self::JADWAL[$event->description],
                $event->expression,
                "Ekspresi cron '{$event->description}' berubah."
            );
        }
    }

    /**
     * @return array<int, Event>
     */
    private function events(): array
    {
        return $this->app->make(Schedule::class)->events();
    }
}
