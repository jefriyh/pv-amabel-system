<?php

namespace App\Policies;

use App\Models\SecurityAttendance;
use App\Models\User;

class SecurityAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SecurityAttendance $attendance): bool
    {
        return $user->isSuperAdmin() || $user->isPengurus() || $user->id === $attendance->user_id;
    }

    public function create(User $user): bool
    {
        // Security can log attendance; Super Admin can manual log
        return $user->isSecurity() || $user->isSuperAdmin();
    }

    public function update(User $user, SecurityAttendance $attendance): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, SecurityAttendance $attendance): bool
    {
        return $user->isSuperAdmin();
    }
}
