<?php

namespace CBSE\Helper;

final class UserHelper
{
    public const USER_ROLES_FOR_COACH = array('administrator', 'editor', 'author', 'contributor');
    public const USER_ROLES_FOR_MANGER = array('administrator');

    public static function isUserCoach(int $userId): bool
    {
        return !empty(array_intersect(self::USER_ROLES_FOR_COACH, self::getUserRolesByUserId($userId)));
    }

    private static function getUserRolesByUserId(int $userId): array
    {
        $user = get_userdata($userId);
        return empty($user) ? array() : $user->roles;
    }

    public static function isUserManager(int $userId): bool
    {
        return !empty(array_intersect(self::USER_ROLES_FOR_MANGER, self::getUserRolesByUserId($userId)));
    }

}
