<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\PackageDelivery;
use App\Models\SecurityAttendance;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Model;

class GuestbookEntries
{
    /** @var array<string, class-string<Visitor|PackageDelivery|SecurityAttendance|LeaveRequest>> */
    public const TYPES = [
        'visitors' => Visitor::class,
        'packages' => PackageDelivery::class,
        'attendances' => SecurityAttendance::class,
        'leaves' => LeaveRequest::class,
    ];

    /**
     * @return class-string<Visitor|PackageDelivery|SecurityAttendance|LeaveRequest>|null
     */
    public static function modelFor(string $type): ?string
    {
        return self::TYPES[$type] ?? null;
    }

    public static function typeOf(Model $entry): string
    {
        return match (true) {
            $entry instanceof Visitor => 'visitors',
            $entry instanceof PackageDelivery => 'packages',
            $entry instanceof SecurityAttendance => 'attendances',
            $entry instanceof LeaveRequest => 'leaves',
            default => 'visitors',
        };
    }
}
