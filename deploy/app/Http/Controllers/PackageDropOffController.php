<?php

namespace App\Http\Controllers;

use App\Filament\Resources\PackageDeliveries\PackageDeliveryResource;
use App\Http\Requests\StorePackageRequest;
use App\Models\PackageDelivery;
use App\Services\PhotoStorageService;
use App\Services\TelegramNotifier;
use App\Support\TelegramMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PackageDropOffController extends Controller
{
    public function create(): View
    {
        return view('guestbook.package', [
            'couriers' => config('guestbook.couriers'),
        ]);
    }

    public function store(
        StorePackageRequest $request,
        PhotoStorageService $photos,
        TelegramNotifier $telegram,
    ): RedirectResponse {
        $id = (string) Str::ulid();

        try {
            $package = new PackageDelivery($request->safe()->only([
                'courier_name', 'courier_company', 'recipient_note', 'tracking_number',
            ]));

            $package->id = $id;
            $package->status = PackageDelivery::STATUS_DITITIPKAN;
            $package->photo_path = $photos->store($request->file('photo'), 'packages', $id, 'paket');

            if ($request->hasFile('selfie')) {
                $package->selfie_path = $photos->store($request->file('selfie'), 'packages', $id, 'kurir');
            }

            $package->ip_address = $request->ip();
            $package->user_agent = Str::limit((string) $request->userAgent(), 500, '');
            $package->save();
        } catch (Throwable $e) {
            $photos->forget('packages', $id);

            throw $e;
        }

        // Dikirim langsung, bukan lewat antrean. Kegagalan Telegram tidak menggagalkan
        // pencatatan paket yang sudah selesai di atas; errornya masuk ke log.
        $telegram->sendQuietly(
            TelegramMessage::forPackage($package, PackageDeliveryResource::getUrl('view', ['record' => $package])),
        );

        $request->session()->put('guestbook.receipt', [
            'type' => 'paket',
            'entity' => 'packages',
            'id' => $package->id,
            'name' => $package->courier_name,
            'rows' => array_filter([
                'Kurir' => $package->courier_name,
                'Ekspedisi' => $package->courier_company,
                'Untuk' => $package->recipient_note,
                'No. Resi' => $package->tracking_number,
            ]),
            // Selfie kurir opsional, jadi hanya foto yang benar-benar ada yang
            // ditampilkan — kalau tidak, kartu bukti memuat gambar rusak.
            'photos' => array_filter(
                PackageDelivery::photoFields(),
                fn (string $field) => $package->hasPhoto($field),
                ARRAY_FILTER_USE_KEY,
            ),
            'at' => $package->created_at->translatedFormat('d F Y, H:i').' WIB',
        ]);

        return redirect()->route('guestbook.done');
    }
}
