<?php

namespace CBSE\Helper;

final class UserHelper
{
    public const USER_ROLES_FOR_COACH = array('administrator', 'editor', 'author', 'contributor');

    public static function isUserCoach(int $userId): bool
    {
        return !empty(array_intersect(self::USER_ROLES_FOR_COACH, self::getUserRolesByUserId($userId)));
    }

    private static function getUserRolesByUserId(int $user_id): array
    {
        $user = get_userdata($user_id);
        return empty($user) ? array() : $user->roles;
    }

}