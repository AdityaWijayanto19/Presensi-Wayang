@props([
    'name' => 'foto',
    'id' => 'foto',
    'label' => 'Upload Foto',
    'currentImage' => null,
    'aspectRatio' => 1,
    'size' => 80,
])

@php
    $modalId = 'crop-modal-' . $id;
    $previewId = 'crop-preview-' . $id;
    $inputId = 'crop-input-' . $id;
    $imgId = 'crop-img-' . $id;
@endphp

<div class="mb-2">
    <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>

    {{-- Preview --}}
    <div id="{{ $previewId }}" class="mb-2" @if(!$currentImage) style="display:none;" @endif>
        <div class="relative inline-block">
            <img id="{{ $previewId }}-img"
                src="{{ $currentImage ? asset('storage/uploads/karyawan/' . $currentImage) : '' }}"
                alt="Preview"
                class="rounded-md object-cover border border-slate-200"
                style="width:{{ $size }}px;height:{{ $size }}px;">
            <button type="button" data-crop-reset="{{ $id }}"
                class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                tabindex="-1">
                <i data-lucide="x" style="width:12px;height:12px;"></i>
            </button>
        </div>
    </div>

    {{-- Tombol Pilih --}}
    <button type="button" data-crop-trigger="{{ $id }}"
        class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-3 py-1.5 rounded-md hover:bg-slate-200 transition-colors text-sm font-medium border border-slate-300">
        <i data-lucide="image-plus" style="width:16px;height:16px;"></i>
        <span data-crop-btn-text="{{ $id }}">{{ $currentImage ? 'Ganti Foto' : 'Pilih Foto' }}</span>
    </button>

    {{-- Hidden File Input --}}
    <input type="file" name="{{ $name }}" id="{{ $inputId }}" accept="image/*"
        class="hidden" data-crop-input="{{ $id }}">
</div>

{{-- Crop Modal --}}
<div id="{{ $modalId }}" style="display:none;" class="fixed inset-0 z-[60]">
    <div class="fixed inset-0 bg-black/60" data-crop-cancel="{{ $id }}"></div>
    <div class="fixed inset-0 flex items-center justify-center p-3">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full" @click.stop>
            <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-800">Potong Foto</h3>
                <button type="button" data-crop-cancel="{{ $id }}"
                    class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" style="width:16px;height:16px;"></i>
                </button>
            </div>
            <div class="p-3">
                <div class="bg-slate-50 rounded-md overflow-hidden" style="max-height:400px;">
                    <img id="{{ $imgId }}" src="" alt="Crop"
                        style="max-width:100%;display:block;">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 px-4 py-2.5 border-t border-slate-200">
                <x-admin.button variant="secondary" data-crop-cancel="{{ $id }}">Batal</x-admin.button>
                <x-admin.button variant="primary" icon="save" data-crop-save="{{ $id }}">Simpan</x-admin.button>
            </div>
        </div>
    </div>
</div>
