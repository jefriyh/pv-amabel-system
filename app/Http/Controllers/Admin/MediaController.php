<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageDelivery;
use App\Models\Visitor;
use App\Support\GuestbookEntries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menyajikan foto KTP/selfie/paket dari disk privat ke admin yang sudah login.
 *
 * Whitelist dua lapis: hanya tipe model yang terdaftar di GuestbookEntries, dan hanya
 * kolom yang diakui model lewat photoFields(). Jadi parameter dari URL tidak pernah
 * dipakai langsung untuk menyusun path file.
 */
class MediaController extends Controller
{
    public function __invoke(Request $request, string $type, string $record, string $field): Response|StreamedResponse
    {
        $model = GuestbookEntries::modelFor($type);

        abort_if($model === null, 404);
        abort_unless(array_key_exists($field, $model::photoFields()), 404);

        $entry = $model::findOrFail($record);

        abort_unless($entry->hasPhoto($field), 404);

        return Storage::disk('local')->response(
            $entry->{$field},
            headers: [
                'Content-Type' => 'image/jpeg',
                // Foto tidak boleh nyangkut di cache proxy bersama
                'Cache-Control' => 'private, max-age=300, no-transform',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    /**
     * Bantu tempat lain menyusun URL foto tanpa menghafal urutan parameternya.
     */
    public static function urlFor(Visitor|PackageDelivery $entry, string $field): ?string
    {
        if (! $entry->hasPhoto($field)) {
            return null;
        }

        return route('admin.media', [
            'type' => GuestbookEntries::typeOf($entry),
            'record' => $entry->getKey(),
            'field' => $field,
        ]);
    }
}
