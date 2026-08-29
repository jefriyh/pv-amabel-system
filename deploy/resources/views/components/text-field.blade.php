@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
    'required' => false,
    'placeholder' => null,
    'rows' => null,
])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-semibold text-slate-800">
        {{ $label }}
        @if ($required)
            <span class="text-rose-600">*</span>
        @else
            <span class="font-normal text-slate-500">(opsional)</span>
        @endif
    </label>

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @php
        $classes = 'block w-full rounded-lg border px-3 py-2.5 text-base shadow-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 '
            .($errors->has($name) ? 'border-rose-400 bg-rose-50' : 'border-slate-300 bg-white');
    @endphp

    @if ($rows)
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
                  placeholder="{{ $placeholder }}" @required($required)
                  class="{{ $classes }}">{{ old($name) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
               value="{{ old($name) }}" placeholder="{{ $placeholder }}"
               @required($required) class="{{ $classes }}">
    @endif

    @error($name)
        <p class="text-sm font-medium text-rose-600">{{ $message }}</p>
    @enderror
</div>
