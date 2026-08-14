<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets;

/**
 * v1 called these "locations" (public/private/template). v2 has no
 * VIEW_TYPE-style constant — the host derives the current surface from
 * the request's user: no user is a guest (Public), a staff user is
 * Staff, anyone else authenticated is a client in the Portal.
 */
enum AssetSurface: string
{
    case Public = 'public';
    case Portal = 'portal';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public pages',
            self::Portal => 'Client portal',
            self::Staff => 'Staff admin',
        };
    }

    /**
     * @param bool $isStaff Whether the host's own staff-detection (e.g.
     *   $user->isStaff()) says this authenticated user is staff. Ignored
     *   when $user is null.
     */
    public static function current(?object $user, bool $isStaff): self
    {
        if ($user === null) {
            return self::Public;
        }

        return $isStaff ? self::Staff : self::Portal;
    }
}
