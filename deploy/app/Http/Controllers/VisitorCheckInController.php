<?php

namespace App\Http\Controllers;

use App\Filament\Resources\Visitors\VisitorResource;
use App\Http\Requests\StoreVisitorRequest;
use App\Models\Visitor;
use App\Services\PhotoStorageService;
use App\Services\TelegramNotifier;
use App\Support\TelegramMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class VisitorCheckInController extends Controller
{
    public function create(): View
    {
        return view('guestbook.visitor');
    }

    public function store(
        StoreVisitorRequest $request,
        PhotoStorageService $photos,
        TelegramNotifier $telegram,
    ): RedirectResponse {
        // ULID dibuat lebih dulu supaya foto bisa langsung disimpan ke folder milik
        // entri ini, tanpa perlu menyimpan baris dua kali.
        $id = (string) Str::ulid();

        try {
            $visitor = new Visitor($request->safe()->only([
                'name', 'phone', 'host_name', 'purpose',
            ]));

            $visitor->id = $id;
            $visitor->status = Visitor::STATUS_PENDING;
            $visitor->ktp_path = $photos->store($request->file('ktp'), 'visitors', $id, 'ktp');
            $visitor->selfie_path = $photos->store($request->file('selfie'), 'visitors', $id, 'selfie');
            $visitor->ip_address = $request->ip();
            $visitor->user_agent = Str::limit((string) $request->userAgent(), 500, '');
            $visitor->save();
        } catch (Throwable $e) {
            // Jangan tinggalkan foto yatim kalau penyimpanan barisnya gagal.
            $photos->forget('visitors', $id);

            throw $e;
        }

        // Dikirim langsung, bukan lewat antrean, supaya satpam menerima pemberitahuan
        // pada detik yang sama tamu menekan kirim. sendQuietly menjamin gangguan di
        // sisi Telegram tidak sampai menggagalkan pencatatan yang sudah selesai di atas.
        $telegram->sendQuietly(
            TelegramMessage::forVisitor($visitor, VisitorResource::getUrl('view', ['record' => $visitor])),
        );

        // Disimpan sebagai session biasa, bukan flash: halaman kartu bukti memuat foto
        // lewat request terpisah, dan flash sudah hangus sebelum gambar sempat diminta.
        $request->session()->put('guestbook.receipt', [
            'type' => 'tamu',
            'entity' => 'visitors',
            'id' => $visitor->id,
            'name' => $visitor->name,
            'rows' => array_filter([
                'Nama' => $visitor->name,
                'Menemui' => $visitor->host_name,
                'Keperluan' => $visitor->purpose,
            ]),
            'photos' => array_filter(
                Visitor::photoFields(),
                fn (string $field) => $visitor->hasPhoto($field),
                ARRAY_FILTER_USE_KEY,
            ),
            'at' => $visitor->created_at->translatedFormat('d F Y, H:i').' WIB',
        ]);

        return redirect()->route('guestbook.done');
    }
}
