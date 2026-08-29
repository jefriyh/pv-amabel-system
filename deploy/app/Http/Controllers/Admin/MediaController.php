<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\PackageDelivery;
use App\Models\SecurityAttendance;
use App\Models\Visitor;
use App\Support\GuestbookEntries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                'Cache-Control' => 'private, max-age=300, no-transform',
                'X-Content-Type-Options' => 'nosniff',
                'Access-Control-Allow-Origin' => '*',
            ],
        );
    }

    /**
     * Bantu tempat lain menyusun URL foto tanpa menghafal urutan parameternya.
     */
    public static function urlFor(Model $entry, string $field): ?string
    {
        if (method_exists($entry, 'hasPhoto') && ! $entry->hasPhoto($field)) {
            return null;
        }

        return route('admin.media', [
            'type' => GuestbookEntries::typeOf($entry),
            'record' => $entry->getKey(),
            'field' => $field,
        ]);
    }
}
