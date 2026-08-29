<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'phone',
    'annual_leave_quota',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_PENGURUS = 'pengurus';
    public const ROLE_SECURITY = 'security';

    public static function getRoleLabels(): array
    {
        return [
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_PENGURUS => 'Pengurus',
            self::ROLE_SECURITY => 'Security / Satpam',
        ];
    }

    public function getRoleLabelAttribute(): string
    {
        return self::getRoleLabels()[$this->role] ?? ucfirst($this->role);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isPengurus(): bool
    {
        return $this->role === self::ROLE_PENGURUS;
    }

    public function isSecurity(): bool
    {
        return $this->role === self::ROLE_SECURITY;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active;
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(SecurityAttendance::class, 'user_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function approvedVisitors(): HasMany
    {
        return $this->hasMany(Visitor::class, 'approved_by');
    }

    /**
     * Hitung sisa kuota cuti tahunan di tahun berjalan
     */
    public function getRemainingLeaveQuotaAttribute(): int
    {
        $usedDays = $this->leaveRequests()
            ->where('type', 'cuti')
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');

        return max(0, (int) $this->annual_leave_quota - (int) $usedDays);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'annual_leave_quota' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
