<div
    x-data="{
        latitude: $wire.$entangle('data.latitude'),
        longitude: $wire.$entangle('data.longitude'),
        locationAddress: $wire.$entangle('data.location_address'),
        accuracy: null,
        loading: false,
        statusText: 'Mendeteksi lokasi GPS...',
        hasLocation: false,

        init() {
            this.detectLocation();
        },

        detectLocation() {
            if (!navigator.geolocation) {
                this.statusText = 'Perangkat tidak mendukung geolokasi GPS.';
                return;
            }

            this.loading = true;
            this.statusText = 'Meminta izin GPS perangkat...';

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.accuracy = Math.round(position.coords.accuracy);
                    this.hasLocation = true;
                    this.statusText = 'Lokasi GPS berhasil didapatkan.';
                    this.loading = false;

                    // Reverse geocode jika belum ada catatan lokasi
                    if (!this.locationAddress || this.locationAddress === 'Pos Gerbang Utama Villa Amabel') {
                        try {
                            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${this.latitude}&lon=${this.longitude}&zoom=18&addressdetails=1`, {
                                headers: {
                                    'Accept-Language': 'id-ID,id;q=0.9,en;q=0.8'
                                }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                if (data && data.display_name) {
                                    this.locationAddress = data.display_name;
                                }
                            }
                        } catch (e) {
                            console.warn('Geocoding offline or blocked, using default pos address:', e);
                        }
                    }
                },
                (error) => {
                    this.loading = false;
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            this.statusText = '⚠️ Izin GPS ditolak. Silakan aktifkan izin lokasi di browser.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            this.statusText = '⚠️ Informasi lokasi GPS tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            this.statusText = '⚠️ Deteksi lokasi GPS timeout.';
                            break;
                        default:
                            this.statusText = '⚠️ Gagal mendapatkan koordinat GPS.';
                            break;
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
    }"
    class="flex flex-col gap-2"
>
    <!-- Label Field -->
    <div class="flex items-center justify-between">
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
            Catatan Lokasi & Alamat Pos
        </label>
        <button
            type="button"
            x-on:click="detectLocation()"
            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
            :disabled="loading"
        >
            <span x-show="loading" class="animate-spin text-xs">🌀</span>
            <span x-show="!loading">🔄</span>
            <span x-text="loading ? 'Mendeteksi...' : 'Perbarui Lokasi GPS'"></span>
        </button>
    </div>

    <!-- Input Catatan Lokasi -->
    <div class="relative">
        <input
            type="text"
            x-model="locationAddress"
            placeholder="Pos Gerbang Utama Villa Amabel"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm transition focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
        />
    </div>

    <!-- Tampilan Status & Koordinat GPS di bawah Catatan Lokasi -->
    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
        <template x-if="hasLocation && latitude && longitude">
            <div class="inline-flex items-center gap-2 rounded-md bg-emerald-50 px-2.5 py-1 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                <span>📍 <strong>GPS:</strong> <span x-text="Number(latitude).toFixed(6)"></span>, <span x-text="Number(longitude).toFixed(6)"></span></span>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400" x-text="'(Akurasi ±' + accuracy + 'm)'"></span>
                <a
                    :href="'https://www.google.com/maps?q=' + latitude + ',' + longitude"
                    target="_blank"
                    class="ml-1 inline-flex items-center gap-0.5 font-bold text-emerald-700 underline hover:text-emerald-900 dark:text-emerald-300"
                >
                    Lihat di Google Maps ↗
                </a>
            </div>
        </template>

        <template x-if="!hasLocation">
            <span class="inline-flex items-center gap-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400" x-text="statusText"></span>
        </template>
    </div>
</div>
