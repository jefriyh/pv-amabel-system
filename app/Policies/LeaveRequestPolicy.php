<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // Superadmin and pengurus can view any; security can only view own request
        return $user->isSuperAdmin() || $user->isPengurus() || $user->id === $leaveRequest->user_id;
    }

    public function create(User $user): bool
    {
        // Security and Superadmin can create leave requests
        return $user->isSecurity() || $user->isSuperAdmin();
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // Superadmin can edit; Pengurus can approve/reject; Security can edit only while pending
        if ($user->isSuperAdmin() || $user->isPengurus()) {
            return true;
        }

        return $user->isSecurity() && $user->id === $leaveRequest->user_id && $leaveRequest->status === LeaveRequest::STATUS_PENDING;
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        // Superadmin can delete; Security can delete own pending request
        return $user->isSuperAdmin() || ($user->isSecurity() && $user->id === $leaveRequest->user_id && $leaveRequest->status === LeaveRequest::STATUS_PENDING);
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->isSuperAdmin() || $user->isPengurus();
    }
}
