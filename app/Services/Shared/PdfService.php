<?php

namespace App\Services\Shared;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfService
{
    public function generate(array $data, string $view, ?string $stempelPath = null): string
    {
        $pdf = Pdf::loadView($view, array_merge($data, ['stempelPath' => $stempelPath]));
        $pdf->setPaper('A4', 'portrait');
        $dir = Str::slug($data['nama_lengkap']);
        $filename = Str::uuid() . '-' . Str::slug($data['nama_lengkap']) . '.pdf';
        $path = $dir . '/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());
        return $path;
    }

    public function generateWithCustomPath(array $data, string $view, string $dir, string $suffix, ?string $stempelPath = null): string
    {
        $pdf = Pdf::loadView($view, array_merge($data, ['stempelPath' => $stempelPath]));
        $pdf->setPaper('A4', 'portrait');
        $filename = Str::uuid() . '-' . $suffix . '-' . Str::slug($data['nama_lengkap']) . '.pdf';
        $path = $dir . '/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());
        return $path;
    }

    public function getStempelPath(): ?string
    {
        if (file_exists(public_path('assets/img/stempel-approved.png'))) {
            return 'assets/img/stempel-approved.png';
        }
        return null;
    }
}
