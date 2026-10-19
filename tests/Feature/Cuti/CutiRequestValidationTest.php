<?php

namespace Tests\Feature\Cuti;

use App\Http\Requests\Presensi\UpdateCutiAdminRequest;
use App\Http\Requests\Service\StoreCutiDraftRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Regresi untuk bug "The given value durasi_hari does not represent a valid number"
 * (Brick\Math\NumberFormatException -> HTTP 500).
 *
 * Rule "size:" HANYA menerima angka literal, bukan nama field. Rule lama
 * "size:durasi_hari" membuat validateSize() melempar NumberFormatException.
 */
class CutiRequestValidationTest extends TestCase
{
    private function validate(array $data, array $rules, array $messages = []): array
    {
        $errors = Validator::make($data, $rules, $messages)->errors();

        return $errors->all();
    }

    private function storeRules(array $data): array
    {
        return StoreCutiDraftRequest::create('/cuti/store', 'POST', $data)->rules();
    }

    private function storeMessages(): array
    {
        return StoreCutiDraftRequest::create('/cuti/store', 'POST')->messages();
    }

    private function adminRules(array $data): array
    {
        return UpdateCutiAdminRequest::create('/panel/cuti/{id}', 'PUT', $data)->rules();
    }

    private function adminMessages(): array
    {
        return UpdateCutiAdminRequest::create('/panel/cuti/{id}', 'PUT')->messages();
    }

    public function test_store_draft_rules_generate_numeric_size_from_input(): void
    {
        $rules = $this->storeRules([
            'durasi_hari' => '2',
            'tanggal_cuti' => ['2026-10-19', '2026-10-20'],
        ]);

        $this->assertSame('required|array|size:2', $rules['tanggal_cuti']);
    }

    public function test_store_draft_omits_size_rule_when_durasi_missing(): void
    {
        // Kasus pemicu bug lama: durasi_hari tidak ada, parameter size tidak boleh
        // jatuh ke string literal "durasi_hari" yang membuat Brick\Math crash.
        $rules = $this->storeRules([
            'tanggal_cuti' => ['2026-10-19'],
        ]);

        $this->assertSame('required|array', $rules['tanggal_cuti']);
    }

    public function test_store_draft_passes_when_count_matches_durasi(): void
    {
        $data = [
            'durasi_hari' => '2',
            'tanggal_cuti' => ['2026-10-19', '2026-10-20'],
            'keterangan' => 'Cuti keluarga',
        ];

        $errors = $this->validate($data, $this->storeRules($data), $this->storeMessages());

        $this->assertSame([], $errors);
    }

    public function test_store_draft_fails_when_count_does_not_match_durasi(): void
    {
        $data = [
            'durasi_hari' => '2',
            'tanggal_cuti' => ['2026-10-19'],
        ];

        $errors = $this->validate($data, $this->storeRules($data), $this->storeMessages());

        $this->assertContains('Jumlah tanggal harus sesuai dengan durasi cuti.', $errors);
    }

    public function test_store_draft_does_not_throw_when_durasi_missing(): void
    {
        // Regresi langsung: sebelum fix, kondisi ini melempar
        // NumberFormatException (HTTP 500) bukan ValidationException.
        $data = [
            'tanggal_cuti' => ['2026-10-19'],
        ];

        $errors = $this->validate($data, $this->storeRules($data), $this->storeMessages());

        $this->assertContains('Durasi cuti wajib dipilih.', $errors);
    }

    public function test_store_draft_fails_on_duplicate_dates(): void
    {
        $data = [
            'durasi_hari' => '2',
            'tanggal_cuti' => ['2026-10-19', '2026-10-19'],
        ];

        $errors = $this->validate($data, $this->storeRules($data), $this->storeMessages());

        $this->assertContains('Tanggal cuti tidak boleh sama.', $errors);
    }

    public function test_store_draft_fails_on_invalid_durasi(): void
    {
        $data = [
            'durasi_hari' => '5',
            'tanggal_cuti' => ['2026-10-19', '2026-10-20'],
        ];

        $errors = $this->validate($data, $this->storeRules($data), $this->storeMessages());

        $this->assertContains('Durasi cuti maksimal 3 hari dalam satu pengajuan.', $errors);
    }

    public function test_admin_update_passes_when_count_matches_durasi(): void
    {
        $data = [
            'durasi_hari' => '3',
            'tanggal_cuti' => ['2026-10-19', '2026-10-20', '2026-10-21'],
        ];

        $errors = $this->validate($data, $this->adminRules($data), $this->adminMessages());

        $this->assertSame([], $errors);
    }

    public function test_admin_update_does_not_throw_when_durasi_missing(): void
    {
        $data = [
            'tanggal_cuti' => ['2026-10-19'],
        ];

        $errors = $this->validate($data, $this->adminRules($data), $this->adminMessages());

        $this->assertContains('Durasi cuti wajib dipilih.', $errors);
    }

    public function test_admin_update_fails_when_count_does_not_match_durasi(): void
    {
        $data = [
            'durasi_hari' => '2',
            'tanggal_cuti' => ['2026-10-19', '2026-10-20', '2026-10-21'],
        ];

        $errors = $this->validate($data, $this->adminRules($data), $this->adminMessages());

        $this->assertContains('Jumlah tanggal harus sesuai dengan durasi cuti.', $errors);
    }
}
