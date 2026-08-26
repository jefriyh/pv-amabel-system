<x-layouts.guestbook title="Form Tamu">
    <form method="POST" action="{{ route('visitors.store') }}" enctype="multipart/form-data"
          data-guestbook-form class="space-y-5">
        @csrf

        {{-- Umpan bot: disembunyikan dari mata dan dari pembaca layar. --}}
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
            <h2 class="text-base font-bold text-slate-900">Data Tamu</h2>

            <x-text-field name="name" label="Nama lengkap" required
                          placeholder="Sesuai KTP" />

            <x-text-field name="phone" label="Nomor HP" type="tel"
                          placeholder="08xxxxxxxxxx" />

            <x-text-field name="host_name" label="Menemui siapa / rumah nomor berapa"
                          placeholder="Contoh: Pak Andi, Blok C2 No. 5" />

            <x-text-field name="purpose" label="Keperluan" required rows="3"
                          placeholder="Contoh: silaturahmi keluarga, servis AC, antar barang" />
        </div>

        <div class="space-y-5 rounded-xl bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Foto</h2>

            <x-photo-input name="ktp" label="Foto KTP" required facing="environment"
                           hint="Pastikan nama dan foto pada KTP terbaca jelas." />

            <x-photo-input name="selfie" label="Foto selfie" required facing="user"
                           hint="Ambil foto wajah Anda saat ini juga." />
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-slate-900 px-4 py-3.5 text-base font-bold text-white shadow-sm transition active:bg-slate-800 disabled:opacity-60">
            Kirim &amp; Masuk
        </button>

        <a href="{{ route('guestbook.home') }}"
           class="block text-center text-sm font-medium text-slate-500">Kembali</a>
    </form>
</x-layouts.guestbook>
