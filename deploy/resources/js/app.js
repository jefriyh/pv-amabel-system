/**
 * Kompresi foto di sisi klien.
 *
 * Foto kamera HP zaman sekarang bisa 4–8 MB. Mengunggahnya apa adanya lewat sinyal
 * seluler di gerbang bisa memakan puluhan detik, dan tamu keburu menyerah. Di sini
 * setiap foto digambar ulang ke canvas (maks 1600px, JPEG q=0.8) sebelum dikirim,
 * sehingga ukurannya turun drastis. Canvas juga menghilangkan metadata EXIF.
 *
 * Efek samping yang disengaja: foto HEIC dari iPhone ikut dikonversi ke JPEG, yang
 * memang satu-satunya format yang bisa diproses GD di server.
 */

const MAX_DIMENSION = 1600;
const QUALITY = 0.8;

async function compress(file) {
    const bitmap = await createImageBitmap(file);

    const scale = Math.min(1, MAX_DIMENSION / Math.max(bitmap.width, bitmap.height));
    const width = Math.round(bitmap.width * scale);
    const height = Math.round(bitmap.height * scale);

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(bitmap, 0, 0, width, height);
    bitmap.close?.();

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', QUALITY));

    if (!blob) {
        throw new Error('Gagal mengubah foto menjadi JPEG.');
    }

    const name = file.name.replace(/\.[^.]+$/, '') || 'foto';

    return new File([blob], `${name}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
}

function setupPhotoInput(wrapper) {
    const input = wrapper.querySelector('input[type="file"]');
    const preview = wrapper.querySelector('[data-preview]');
    const status = wrapper.querySelector('[data-status]');
    const placeholder = wrapper.querySelector('[data-placeholder]');
    const form = input.closest('form');

    let previousObjectUrl = null;

    input.addEventListener('change', async () => {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        status.textContent = 'Memproses foto…';
        input.disabled = true;
        form?.querySelectorAll('[type="submit"]').forEach((b) => (b.disabled = true));

        try {
            const compressed = await compress(file);

            // Ganti isi input dengan versi terkompresi supaya form biasa (tanpa
            // JavaScript upload manual) tetap mengirimkan file yang sudah kecil.
            const transfer = new DataTransfer();
            transfer.items.add(compressed);
            input.files = transfer.files;

            if (previousObjectUrl) {
                URL.revokeObjectURL(previousObjectUrl);
            }

            previousObjectUrl = URL.createObjectURL(compressed);
            preview.src = previousObjectUrl;
            preview.classList.remove('hidden');
            placeholder?.classList.add('hidden');

            status.textContent = `Foto siap (${Math.round(compressed.size / 1024)} KB). Ketuk untuk mengambil ulang.`;
        } catch (error) {
            // Biarkan file aslinya terkirim; server yang akan menolak kalau formatnya
            // memang tidak didukung, dengan pesan yang jelas.
            console.error(error);
            status.textContent = 'Foto tidak bisa diproses di HP ini. Coba ambil ulang atau pakai format JPG.';
        } finally {
            input.disabled = false;
            form?.querySelectorAll('[type="submit"]').forEach((b) => (b.disabled = false));
        }
    });
}

function setupSubmitGuard(form) {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[type="submit"]');

        if (!button) {
            return;
        }

        // Unggah foto bisa beberapa detik; tanpa ini tamu menekan tombol berkali-kali
        // dan membuat entri ganda.
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Mengirim…';
    });
}

document.querySelectorAll('[data-photo-input]').forEach(setupPhotoInput);
document.querySelectorAll('form[data-guestbook-form]').forEach(setupSubmitGuard);
