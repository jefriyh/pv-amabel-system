@props([
    'name',
    'label',
    'hint' => null,
    'facing' => 'environment',
    'required' => false,
])

@php
    $id = 'photo-'.$name;
@endphp

<div data-photo-input class="space-y-2">
    {{-- Judul field. "for" sengaja tidak dipasang di sini: yang menjadi tombolnya
         adalah kotak dropzone di bawah, dan dua label untuk satu input membuat
         pembaca layar mengumumkannya dua kali. --}}
    <span class="block text-sm font-semibold text-slate-800">
        {{ $label }}
        @if ($required)
            <span class="text-rose-600">*</span>
        @else
            <span class="font-normal text-slate-500">(opsional)</span>
        @endif
    </span>

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    <label for="{{ $id }}"
           class="flex min-h-40 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-white p-3 transition active:border-slate-400">
        <div data-placeholder class="text-center">
            <svg class="mx-auto h-9 w-9 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
            </svg>
            <span class="mt-2 block text-sm font-medium text-slate-600">Ketuk untuk membuka kamera</span>
        </div>

        <img data-preview alt="" class="hidden max-h-64 w-full rounded-lg object-contain">
    </label>

    <input
        id="{{ $id }}"
        type="file"
        name="{{ $name }}"
        accept="image/*"
        capture="{{ $facing }}"
        aria-label="{{ $label }}"
        @required($required)
        class="sr-only"
    >

    <p data-status class="text-xs text-slate-500" aria-live="polite"></p>

    @error($name)
        <p class="text-sm font-medium text-rose-600">{{ $message }}</p>
    @enderror
</div>
