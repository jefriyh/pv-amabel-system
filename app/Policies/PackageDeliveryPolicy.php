<?php

namespace App\Policies;

use App\Models\PackageDelivery;
use App\Models\User;

class PackageDeliveryPolicy
{
    public function viewAny(User $user): bool
    {
        // Superadmin, Pengurus, and Security can view package list
        return true;
    }

    public function view(User $user, PackageDelivery $package): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, PackageDelivery $package): bool
    {
        // Superadmin can edit full details; Security can update handover status
        return $user->isSuperAdmin() || $user->isSecurity() || $user->isPengurus();
    }

    public function delete(User $user, PackageDelivery $package): bool
    {
        // Only Super Admin can delete package records
        return $user->isSuperAdmin();
    }
}
