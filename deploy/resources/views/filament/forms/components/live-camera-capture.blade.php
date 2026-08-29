<div
    x-data="{
        selfieState: $wire.$entangle('data.selfie_path'),
        cameraActive: false,
        previewUrl: null,
        loading: false,
        errorMsg: null,
        stream: null,
        facingMode: 'user',

        init() {
            // Jika sudah ada selfie tersimpan
            if (this.selfieState && typeof this.selfieState === 'string' && !this.selfieState.startsWith('livewire-file:')) {
                this.previewUrl = '/media/attendances/' + this.selfieState;
            }
        },

        async startCamera() {
            this.errorMsg = null;
            this.loading = true;
            try {
                if (this.stream) {
                    this.stopCamera();
                }
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: this.facingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 960 }
                    },
                    audio: false
                });

                this.$nextTick(() => {
                    if (this.$refs.videoEl) {
                        this.$refs.videoEl.srcObject = this.stream;
                        this.$refs.videoEl.play();
                    }
                });
                this.cameraActive = true;
            } catch (err) {
                console.warn('Gagal akses kamera langsung, gunakan tombol file kamera:', err);
                this.errorMsg = 'Kamera tidak dapat diakses langsung. Silakan gunakan tombol kamera sistem di bawah.';
                // Fallback langsung buka input file capture
                this.$refs.fallbackInput.click();
            } finally {
                this.loading = false;
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.cameraActive = false;
        },

        snapPhoto() {
            if (!this.$refs.videoEl || !this.$refs.canvasEl) return;
            const video = this.$refs.videoEl;
            const canvas = this.$refs.canvasEl;
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            this.loading = true;
            canvas.toBlob((blob) => {
                if (!blob) {
                    this.loading = false;
                    return;
                }
                const file = new File([blob], 'selfie_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                this.previewUrl = URL.createObjectURL(blob);
                this.stopCamera();

                // Upload ke Livewire
                $wire.upload('data.selfie_path', file, 
                    () => {
                        this.loading = false;
                    }, 
                    (error) => {
                        this.loading = false;
                        this.errorMsg = 'Gagal mengunggah foto selfie. Silakan coba lagi.';
                    }
                );
            }, 'image/jpeg', 0.88);
        },

        switchCamera() {
            this.facingMode = (this.facingMode === 'user') ? 'environment' : 'user';
            this.startCamera();
        },

        handleFallbackFile(e) {
            const files = e.target.files;
            if (files && files[0]) {
                const file = files[0];
                this.previewUrl = URL.createObjectURL(file);
                this.loading = true;
                $wire.upload('data.selfie_path', file,
                    () => { this.loading = false; },
                    () => { this.loading = false; this.errorMsg = 'Gagal mengunggah foto.'; }
                );
            }
        },

        resetPhoto() {
            this.previewUrl = null;
            this.selfieState = null;
            this.stopCamera();
            if (this.$refs.fallbackInput) {
                this.$refs.fallbackInput.value = '';
            }
        }
    }"
    class="flex flex-col gap-3 w-full"
>
    <!-- Label -->
    <div class="flex items-center justify-between">
        <label class="text-sm font-semibold text-slate-800 dark:text-slate-200">
            Foto Selfie Wajah (Kamera) <span class="text-rose-500">*</span>
        </label>
        <span class="text-xs text-slate-500">Kamera Depan / HP</span>
    </div>

    <!-- Hidden native file input with camera capture support -->
    <input
        x-ref="fallbackInput"
        type="file"
        accept="image/*"
        capture="user"
        class="hidden"
        x-on:change="handleFallbackFile($event)"
    />

    <!-- Hidden canvas for snapping photos -->
    <canvas x-ref="canvasEl" class="hidden"></canvas>

    <!-- AREA TAMPILAN: 1. Live Camera Stream -->
    <div
        x-show="cameraActive"
        class="relative overflow-hidden rounded-2xl border-2 border-emerald-500 bg-slate-900 shadow-md"
        style="display: none;"
    >
        <video
            x-ref="videoEl"
            autoplay
            playsinline
            muted
            class="w-full max-h-[360px] object-cover mx-auto"
            style="transform: scaleX(-1);"
        ></video>

        <!-- Overlay Control Buttons on Camera -->
        <div class="absolute bottom-3 left-0 right-0 flex items-center justify-center gap-3 px-4">
            <button
                type="button"
                x-on:click="stopCamera()"
                class="inline-flex items-center gap-1 rounded-full bg-slate-800/80 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur hover:bg-slate-700"
            >
                ✕ Batal
            </button>

            <button
                type="button"
                x-on:click="snapPhoto()"
                class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-emerald-500 active:scale-95 transition"
            >
                <span class="text-base">📸</span> Jepret Foto
            </button>

            <button
                type="button"
                x-on:click="switchCamera()"
                class="inline-flex items-center gap-1 rounded-full bg-slate-800/80 p-2.5 text-xs font-semibold text-white backdrop-blur hover:bg-slate-700"
                title="Ganti Kamera Depan/Belakang"
            >
                🔄
            </button>
        </div>
    </div>

    <!-- AREA TAMPILAN: 2. Preview Foto yang Berhasil Diambil -->
    <div
        x-show="!cameraActive && previewUrl"
        class="relative overflow-hidden rounded-2xl border-2 border-emerald-400 bg-slate-50 p-2 text-center shadow-sm dark:bg-slate-900"
        style="display: none;"
    >
        <img
            :src="previewUrl"
            alt="Preview Selfie"
            class="max-h-[300px] w-auto max-w-full rounded-xl mx-auto object-contain shadow-inner"
        />

        <div class="mt-2.5 flex items-center justify-center gap-2 pb-1">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                ✓ Foto Siap Digunakan
            </span>
            <button
                type="button"
                x-on:click="resetPhoto(); startCamera();"
                class="inline-flex items-center gap-1 rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            >
                🔄 Ambil Ulang Foto
            </button>
        </div>
    </div>

    <!-- AREA TAMPILAN: 3. Tombol Utama Buka Kamera (Sebelum Foto Diambil) -->
    <div
        x-show="!cameraActive && !previewUrl"
        class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-6 text-center transition hover:border-emerald-500 hover:bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/20"
    >
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300 shadow-sm">
            📷
        </div>

        <div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">Ambil Foto Selfie Wajah</h4>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                Gunakan kamera langsung untuk bukti kehadiran di pos gerbang.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2.5 pt-1">
            <button
                type="button"
                x-on:click="startCamera()"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-emerald-500 active:scale-95 transition"
            >
                <span>📸</span> Buka Kamera Langsung
            </button>

            <button
                type="button"
                x-on:click="$refs.fallbackInput.click()"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
            >
                <span>📱</span> Buka Kamera HP / Berkas
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div x-show="loading" class="text-center text-xs font-semibold text-emerald-600 animate-pulse" style="display: none;">
        ⏳ Memproses foto selfie...
    </div>

    <!-- Error Message -->
    <template x-if="errorMsg">
        <p class="text-xs font-medium text-rose-600" x-text="errorMsg"></p>
    </template>
</div>
