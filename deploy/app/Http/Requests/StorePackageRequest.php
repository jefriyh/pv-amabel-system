<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\RejectsHoneypot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StorePackageRequest extends FormRequest
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
            'courier_name' => ['required', 'string', 'min:3', 'max:120'],
            'courier_company' => ['required', 'string', Rule::in(config('guestbook.couriers'))],
            'recipient_note' => ['nullable', 'string', 'max:160'],
            'tracking_number' => ['nullable', 'string', 'max:80'],
            'photo' => ['required', $this->photoRule()],
            'selfie' => ['nullable', $this->photoRule()],
            ...$this->honeypotRules(),
        ];
    }

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
            'courier_name' => 'nama kurir',
            'courier_company' => 'ekspedisi',
            'recipient_note' => 'penerima / rumah tujuan',
            'tracking_number' => 'nomor resi',
            'photo' => 'foto paket',
            'selfie' => 'foto kurir',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'courier_company.in' => 'Pilih salah satu ekspedisi dari daftar.',
            'photo.required' => 'Foto paket di dalam kotak wajib diambil sebagai bukti.',
            'website.prohibited' => 'Pengisian terdeteksi tidak wajar. Silakan ulangi.',
        ];
    }
}
