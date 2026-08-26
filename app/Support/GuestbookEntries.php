<?php

namespace App\Support;

use App\Models\PackageDelivery;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Model;

/**
 * Pemetaan antara nama tipe di URL dan kelas modelnya.
 *
 * Dipakai dua route foto yang berbeda (milik admin dan milik tamu sendiri). Daftarnya
 * ditaruh di satu tempat supaya keduanya tidak pernah menerima tipe yang tidak dikenal:
 * nilai dari URL tidak pernah dipakai langsung untuk menyusun nama kelas atau path file.
 */
class GuestbookEntries
{
    /** @var array<string, class-string<Visitor|PackageDelivery>> */
    public const TYPES = [
        'visitors' => Visitor::class,
        'packages' => PackageDelivery::class,
    ];

    /**
     * @return class-string<Visitor|PackageDelivery>|null
     */
    public static function modelFor(string $type): ?string
    {
        return self::TYPES[$type] ?? null;
    }

    public static function typeOf(Model $entry): string
    {
        return $entry instanceof Visitor ? 'visitors' : 'packages';
    }
}
