<?php

namespace App\Http\Controllers;

use App\Support\GuestbookEntries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menampilkan foto pada kartu bukti di halaman /selesai.
 *
 * Halaman itu terbuka tanpa login, jadi izinnya diambil sepenuhnya dari session: yang
 * boleh dilihat hanya entri yang baru saja dikirim dari browser ini. Id entrinya pun
 * tidak diambil dari URL, melainkan dari session, sehingga tamu tidak bisa mengintip
 * foto KTP tamu lain dengan menebak-nebak alamat.
 */
class ReceiptPhotoController extends Controller
{
    public function __invoke(Request $request, string $field): Response|StreamedResponse
    {
        $receipt = $request->session()->get('guestbook.receipt');

        abort_if(blank($receipt), 404);

        $model = GuestbookEntries::modelFor($receipt['entity']);

        abort_if($model === null, 404);
        abort_unless(array_key_exists($field, $model::photoFields()), 404);

        $entry = $model::find($receipt['id']);

        abort_if($entry === null, 404);
        abort_unless($entry->hasPhoto($field), 404);

        return Storage::disk('local')->response(
            $entry->{$field},
            headers: [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
