<x-layouts.guestbook title="Selamat Datang">
    <div class="space-y-4">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Selamat datang</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">
                Sebelum masuk, mohon isi data terlebih dahulu. Pilih salah satu di bawah ini.
            </p>
        </div>

        <a href="{{ route('visitors.create') }}"
           class="flex items-center gap-4 rounded-xl bg-slate-900 p-5 text-white shadow-sm transition active:bg-slate-800">
            <span class="text-3xl" aria-hidden="true">&#128694;</span>
            <span>
                <span class="block text-base font-bold">Saya Tamu</span>
                <span class="block text-sm text-slate-300">Berkunjung ke penghuni komplek</span>
            </span>
        </a>

        <a href="{{ route('packages.create') }}"
           class="flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition active:bg-slate-50">
            <span class="text-3xl" aria-hidden="true">&#128230;</span>
            <span>
                <span class="block text-base font-bold text-slate-900">Saya Antar Paket</span>
                <span class="block text-sm text-slate-600">Menitipkan paket di kotak paket</span>
            </span>
        </a>
    </div>
</x-layouts.guestbook>
