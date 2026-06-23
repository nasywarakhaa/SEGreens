<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

final class AdminPanelAccess
{
    public static function currentUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    public static function isSuperuser(?User $user = null): bool
    {
        $user ??= self::currentUser();

        return $user?->role_code === UserRole::Superuser;
    }

    public static function isAdminOrSuperuser(?User $user = null): bool
    {
        $user ??= self::currentUser();

        return in_array($user?->role_code, [UserRole::Superuser, UserRole::Admin], true);
    }
}
