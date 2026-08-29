<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;

class VisitorPolicy
{
    public function viewAny(User $user): bool
    {
        // Superadmin, Pengurus, and Security can view the visitor list
        return true;
    }

    public function view(User $user, Visitor $visitor): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Visitors register from public gate form, or superadmin
        return $user->isSuperAdmin();
    }

    public function update(User $user, Visitor $visitor): bool
    {
        // Superadmin can edit full details; Pengurus can approve/update status
        return $user->isSuperAdmin() || $user->isPengurus();
    }

    public function delete(User $user, Visitor $visitor): bool
    {
        // Only Super Admin can delete visitor records
        return $user->isSuperAdmin();
    }

    public function approve(User $user, Visitor $visitor): bool
    {
        return $user->isSuperAdmin() || $user->isPengurus();
    }
}
