<x-layouts.guestbook title="Form Antar Paket">
    <form method="POST" action="{{ route('packages.store') }}" enctype="multipart/form-data"
          data-guestbook-form class="space-y-5">
        @csrf

        <div class="hidden" aria-hidden="true">
            <label for="website">Website</label>
            <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                Ada isian yang perlu diperbaiki. Silakan periksa tanda merah di bawah.
            </div>
        @endif

        <div class="space-y-5 rounded-xl bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Data Kurir</h2>

            <x-text-field name="courier_name" label="Nama kurir" required
                          placeholder="Nama Anda" />

            <div class="space-y-1.5">
                <label for="courier_company" class="block text-sm font-semibold text-slate-800">
                    Ekspedisi <span class="text-rose-600">*</span>
                </label>
                <select id="courier_company" name="courier_company" required
                        class="block w-full rounded-lg border px-3 py-2.5 text-base shadow-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 {{ $errors->has('courier_company') ? 'border-rose-400 bg-rose-50' : 'border-slate-300 bg-white' }}">
                    <option value="">-- Pilih ekspedisi --</option>
                    @foreach ($couriers as $courier)
                        <option value="{{ $courier }}" @selected(old('courier_company') === $courier)>{{ $courier }}</option>
                    @endforeach
                </select>
                @error('courier_company')
                    <p class="text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <x-text-field name="recipient_note" label="Paket untuk siapa / rumah nomor berapa"
                          placeholder="Contoh: Ibu Sari, Blok B1 No. 12" />

            <x-text-field name="tracking_number" label="Nomor resi"
                          placeholder="Contoh: JT1234567890" />
        </div>

        <div class="space-y-5 rounded-xl bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Foto</h2>

            <x-photo-input name="photo" label="Foto paket di dalam kotak" required facing="environment"
                           hint="Foto ini menjadi bukti bahwa paket sudah dititipkan." />

            <x-photo-input name="selfie" label="Foto kurir" facing="user" />
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-slate-900 px-4 py-3.5 text-base font-bold text-white shadow-sm transition active:bg-slate-800 disabled:opacity-60">
            Kirim Laporan Paket
        </button>

        <a href="{{ route('guestbook.home') }}"
           class="block text-center text-sm font-medium text-slate-500">Kembali</a>
    </form>
</x-layouts.guestbook>
