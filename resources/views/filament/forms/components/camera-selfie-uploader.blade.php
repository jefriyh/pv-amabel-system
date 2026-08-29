<div
    x-data="{
        selfieValue: $wire.$entangle('data.selfie_path'),
        isCameraOpen: false,
        previewUrl: null,
        stream: null,
        facingMode: 'user',
        hasMultipleCameras: false,
        errorMessage: null,

        init() {
            if (this.selfieValue && typeof this.selfieValue === 'string' && (this.selfieValue.startsWith('data:image') || this.selfieValue.startsWith('http') || this.selfieValue.startsWith('/'))) {
                this.previewUrl = this.selfieValue;
            }
        },

        isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || (window.innerWidth <= 768);
        },

        async openSmartCamera() {
            this.errorMessage = null;

            // Jika browser tidak mendukung getUserMedia, langsung aktifkan kamera bawaan perangkat
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.$refs.nativeCameraInput.click();
                return;
            }

            try {
                this.isCameraOpen = true;

                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                this.hasMultipleCameras = videoDevices.length > 1;

                await this.startStream();
            } catch (err) {
                console.warn('WebRTC stream issue, switching to native device camera:', err);
                this.isCameraOpen = false;
                // Fallback otomatis ke aplikasi kamera HP
                this.$refs.nativeCameraInput.click();
            }
        },

        async startStream() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }

            const constraints = {
                video: {
                    facingMode: this.facingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 960 }
                },
                audio: false
            };

            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.$nextTick(() => {
                if (this.$refs.videoElement) {
                    this.$refs.videoElement.srcObject = this.stream;
                    this.$refs.videoElement.play();
                }
            });
        },

        async switchCamera() {
            this.facingMode = this.facingMode === 'user' ? 'environment' : 'user';
            await this.startStream();
        },

        takeSnapshot() {
            const video = this.$refs.videoElement;
            if (!video) return;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;

            const ctx = canvas.getContext('2d');
            
            if (this.facingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.90);
            this.previewUrl = dataUrl;
            this.selfieValue = dataUrl;

            this.closeCamera();
        },

        closeCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.isCameraOpen = false;
        },

        handleFileInput(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
                this.selfieValue = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        retakePhoto() {
            this.previewUrl = null;
            this.selfieValue = null;
            this.errorMessage = null;
            this.openSmartCamera();
        }
    }"
    style="width: 100%; display: flex; flex-direction: column; gap: 0.75rem;"
>
    {{-- Header Label --}}
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <label style="font-size: 0.875rem; font-weight: 700; color: #1e293b;">
            Foto Selfie Wajah Petugas <span style="color: #e11d48;">*</span>
        </label>
        <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
            <i class="fa-solid fa-camera" style="margin-right: 0.25rem; color: #436354;"></i> Kamera Selfie / Galeri
        </span>
    </div>

    {{-- Alert Error / Izin Kamera --}}
    <div
        x-show="errorMessage"
        x-cloak
        style="padding: 0.75rem 1rem; background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0.5rem; color: #92400e; font-size: 0.8rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;"
    >
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #d97706; font-size: 1rem;"></i>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="errorMessage = null" style="background: none; border: none; font-size: 1rem; color: #92400e; cursor: pointer; padding: 0.25rem;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- File Inputs Tersembunyi (Strictly Hidden) --}}
    <input
        x-ref="nativeCameraInput"
        type="file"
        accept="image/*"
        capture="user"
        @change="handleFileInput($event)"
        style="display: none !important;"
    />
    <input
        x-ref="galleryInput"
        type="file"
        accept="image/jpeg,image/png,image/webp,image/jpg"
        @change="handleFileInput($event)"
        style="display: none !important;"
    />

    {{-- TAMPILAN 1: KAMERA AKTIF (LIVE VIEWFINDER) --}}
    <div
        x-show="isCameraOpen"
        x-cloak
        style="position: relative; border-radius: 1rem; overflow: hidden; border: 2.5px solid #436354; background-color: #000000; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);"
    >
        <div style="position: relative; width: 100%; min-height: 280px; max-height: 400px; display: flex; align-items: center; justify-content: center; background: #000;">
            <video
                x-ref="videoElement"
                autoplay
                playsinline
                muted
                style="width: 100%; max-height: 380px; object-fit: cover; transform: scaleX(-1); display: block;"
            ></video>

            {{-- Floating Controls Atas --}}
            <div style="position: absolute; top: 0.75rem; right: 0.75rem; display: flex; gap: 0.5rem; z-index: 10;">
                <button
                    x-show="hasMultipleCameras"
                    type="button"
                    @click="switchCamera()"
                    style="background: rgba(0, 0, 0, 0.65); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); padding: 0.4rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.35rem; backdrop-filter: blur(4px);"
                >
                    <i class="fa-solid fa-arrows-rotate"></i> Putar
                </button>
                <button
                    type="button"
                    @click="closeCamera()"
                    style="background: rgba(0, 0, 0, 0.65); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); width: 2rem; height: 2rem; border-radius: 9999px; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        {{-- Shutter Bar Bawah --}}
        <div style="background-color: #0f172a; padding: 1rem; display: flex; align-items: center; justify-content: center; gap: 1rem;">
            <button
                type="button"
                @click="takeSnapshot()"
                style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; padding: 0.75rem 1.75rem; border-radius: 9999px; font-size: 0.9rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4); transition: transform 0.1s ease;"
                onmousedown="this.style.transform='scale(0.96)'"
                onmouseup="this.style.transform='scale(1)'"
            >
                <i class="fa-solid fa-camera" style="font-size: 1.1rem;"></i> Jepret Foto Selfie
            </button>
        </div>
    </div>

    {{-- TAMPILAN 2: FOTO TELAH DIAMBIL (PREVIEW STATE) --}}
    <div
        x-show="!isCameraOpen && previewUrl"
        x-cloak
        style="border-radius: 1rem; border: 1.5px solid #10b981; background-color: #f0fdf4; padding: 1rem 1.25rem; display: flex; flex-wrap: wrap; align-items: center; gap: 1.25rem; box-shadow: 0 2px 4px rgba(0,0,0,0.03);"
    >
        <div style="width: 100px; height: 100px; border-radius: 0.75rem; overflow: hidden; border: 2.5px solid #10b981; flex-shrink: 0; background: #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <img
                :src="previewUrl"
                alt="Foto Selfie Petugas"
                style="width: 100%; height: 100%; object-fit: cover; display: block;"
            />
        </div>

        <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 0.35rem;">
            <div style="display: inline-flex; align-items: center; gap: 0.35rem; width: fit-content; padding: 0.2rem 0.65rem; border-radius: 9999px; background-color: #dcfce7; color: #15803d; font-size: 0.75rem; font-weight: 700;">
                <i class="fa-solid fa-circle-check"></i> Foto Selfie Berhasil Direkam
            </div>
            <p style="margin: 0; font-size: 0.78rem; color: #475569; line-height: 1.35;">
                Foto wajah Anda siap disimpan bersama data presensi harian.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.35rem;">
                <button
                    type="button"
                    @click="retakePhoto()"
                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; border-radius: 0.5rem; background-color: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-size: 0.75rem; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#436354'; this.style.color='#1e293b';"
                    onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#334155';"
                >
                    <i class="fa-solid fa-arrows-rotate" style="color: #436354;"></i> Ambil Ulang
                </button>
                <button
                    type="button"
                    @click="$refs.galleryInput.click()"
                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; border-radius: 0.5rem; background-color: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-size: 0.75rem; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#436354'; this.style.color='#1e293b';"
                    onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#334155';"
                >
                    <i class="fa-solid fa-folder-open" style="color: #436354;"></i> Ganti Berkas
                </button>
            </div>
        </div>
    </div>

    {{-- TAMPILAN 3: KOTAK UTAMA (PILIHAN KAMERA OTOMATIS & GALERI) --}}
    <div
        x-show="!isCameraOpen && !previewUrl"
        style="border: 2px dashed #cbd5e1; border-radius: 1rem; padding: 1.75rem 1.25rem; background-color: #f8fafc; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; transition: border-color 0.2s;"
        onmouseover="this.style.borderColor='#436354'"
        onmouseout="this.style.borderColor='#cbd5e1'"
    >
        {{-- Bulatan Icon Kamera --}}
        <div style="width: 3.5rem; height: 3.5rem; border-radius: 9999px; background-color: #ecfdf5; color: #436354; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 2px 4px rgba(67, 99, 84, 0.1);">
            <i class="fa-solid fa-camera"></i>
        </div>

        <div>
            <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">
                Ambil Foto Selfie Wajah
            </h4>
            <p style="margin: 0; font-size: 0.8rem; color: #64748b; max-width: 420px; line-height: 1.4;">
                Gunakan kamera perangkat untuk mengambil foto selfie, atau pilih foto dari berkas galeri.
            </p>
        </div>

        {{-- Kelompok 2 Tombol Bersih: Buka Kamera & Pilih Berkas --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.75rem; margin-top: 0.5rem; width: 100%;">
            <!-- Tombol Pintar 1: Buka Kamera (Auto Detect Desktop / HP) -->
            <button
                type="button"
                @click="openSmartCamera()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background-color: #436354; color: #ffffff; padding: 0.7rem 1.5rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(67, 99, 84, 0.25); transition: all 0.15s ease;"
                onmouseover="this.style.backgroundColor='#344E42'; this.style.transform='translateY(-1px)';"
                onmouseout="this.style.backgroundColor='#436354'; this.style.transform='translateY(0)';"
            >
                <i class="fa-solid fa-camera" style="font-size: 1rem;"></i>
                <span>Buka Kamera</span>
            </button>

            <!-- Tombol 2: Pilih Berkas / Galeri -->
            <button
                type="button"
                @click="$refs.galleryInput.click()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background-color: #ffffff; color: #334155; padding: 0.7rem 1.35rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 600; border: 1.5px solid #cbd5e1; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: all 0.15s ease;"
                onmouseover="this.style.borderColor='#436354'; this.style.backgroundColor='#f0fdf4'; this.style.color='#1e293b'; this.style.transform='translateY(-1px)';"
                onmouseout="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#ffffff'; this.style.color='#334155'; this.style.transform='translateY(0)';"
            >
                <i class="fa-solid fa-folder-open" style="color: #436354;"></i>
                <span>Pilih dari Galeri</span>
            </button>
        </div>
    </div>
</div>
