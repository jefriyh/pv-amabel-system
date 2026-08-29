<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\RejectsHoneypot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreVisitorRequest extends FormRequest
{
    use RejectsHoneypot;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['nullable', 'string', 'min:8', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'host_name' => ['required', 'string', 'min:3', 'max:120'],
            'purpose' => ['required', 'string', 'min:5', 'max:500'],
            'ktp' => ['required', $this->photoRule()],
            'selfie' => ['required', $this->photoRule()],
            ...$this->honeypotRules(),
        ];
    }

    /**
     * Foto datang langsung dari kamera HP, jadi hanya format kamera yang diterima.
     * Batas 10 MB dipilih agar sedikit di bawah post_max_size nginx/PHP.
     */
    private function photoRule(): File
    {
        return File::image()
            ->types(['jpg', 'jpeg', 'png', 'webp'])
            ->max(10 * 1024);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'phone' => 'nomor HP',
            'host_name' => 'nama yang dituju',
            'purpose' => 'keperluan',
            'ktp' => 'foto KTP',
            'selfie' => 'foto selfie',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'ktp.required' => 'Foto KTP wajib diunggah.',
            'selfie.required' => 'Foto selfie wajib diambil.',
            'phone.required' => 'Nomor HP wajib diisi agar pengurus bisa menghubungi Anda.',
            'phone.regex' => 'Nomor HP hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            'phone.min' => 'Nomor HP sepertinya kurang lengkap.',
            'host_name.required' => 'Isi nama penghuni atau nomor rumah yang Anda tuju.',
            'purpose.min' => 'Tuliskan keperluan Anda sedikit lebih jelas.',
            'website.prohibited' => 'Pengisian terdeteksi tidak wajar. Silakan ulangi.',
        ];
    }
}
