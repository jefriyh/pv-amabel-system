@php
    $receipt = session('guestbook.receipt');
@endphp

<x-layouts.guestbook title="Selesai">
    @if (! $receipt)
        {{-- Halaman dibuka tanpa mengisi form, mis. dari riwayat browser. --}}
        <div class="rounded-2xl bg-white p-6 text-center shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Belum ada data</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Silakan isi formulir terlebih dahulu sebelum menuju halaman ini.
            </p>
            <a href="{{ route('guestbook.home') }}"
               class="mt-6 inline-block rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">
                Isi Formulir
            </a>
        </div>
    @else
        <div class="space-y-4">

            {{-- Instruksi utama. Ditaruh paling atas dan dibuat paling mencolok karena
                 inilah satu-satunya hal yang harus dibaca tamu di layar ini. --}}
            <div class="rounded-2xl bg-amber-50 p-5 text-center ring-2 ring-amber-400">
                <p class="text-base font-extrabold uppercase leading-snug tracking-wide text-amber-900">
                    Tunjukkan ini ke security
                </p>
                <p class="mt-2 text-sm leading-relaxed text-amber-800">
                    (jika security sedang berjaga)<br>
                    atau tunggu beberapa saat hingga dihampiri pemilik rumah.
                </p>
            </div>

            {{-- Kartu bukti --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">

                <div class="flex items-center gap-3 bg-emerald-600 px-5 py-4 text-white">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20 text-xl">
                        &#10003;
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold leading-tight">
                            {{ $receipt['type'] === 'paket' ? 'Paket Tercatat' : 'Tamu Terdaftar' }}
                        </p>
                        <p class="truncate text-xs text-emerald-50">{{ $receipt['at'] }}</p>
                    </div>
                </div>

                <dl class="divide-y divide-slate-100 px-5">
                    @foreach ($receipt['rows'] as $label => $value)
                        <div class="flex gap-3 py-3">
                            <dt class="w-24 shrink-0 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ $label }}
                            </dt>
                            <dd class="min-w-0 flex-1 text-sm font-medium break-words text-slate-900">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Garis putus-putus memberi kesan "sobekan tiket", memisahkan data
                     tertulis dari lampiran fotonya. --}}
                <div class="border-t-2 border-dashed border-slate-200"></div>

                <div class="grid grid-cols-2 gap-3 p-5">
                    @foreach ($receipt['photos'] as $field => $label)
                        @php $src = route('guestbook.receipt-photo', ['field' => $field]) @endphp

                        <figure class="space-y-1.5">
                            {{-- object-contain, bukan object-cover: KTP yang mendatar dan
                                 selfie yang menegak harus sama-sama terlihat utuh. Kalau
                                 dipangkas, justru wajah atau nomor NIK yang hilang.
                                 Sengaja tanpa loading="lazy" — hanya ada dua foto dan
                                 keduanya yang ingin dilihat satpam. --}}
                            <a href="{{ $src }}" target="_blank" rel="noopener"
                               class="block rounded-lg ring-1 ring-slate-200">
                                <img src="{{ $src }}" alt="{{ $label }}"
                                     class="h-40 w-full rounded-lg bg-slate-100 object-contain">
                            </a>
                            <figcaption class="text-center text-xs font-medium text-slate-500">
                                {{ $label }} <span class="text-slate-400">&middot; ketuk untuk perbesar</span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>

            <p class="text-center text-xs leading-relaxed text-slate-500">
                Pengurus komplek sudah menerima pemberitahuan atas kedatangan Anda.
            </p>

            <a href="{{ route('guestbook.home') }}"
               class="block rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-semibold text-white">
                Selesai
            </a>
        </div>
    @endif
</x-layouts.guestbook>
