<?php
// app/Services/ActivityLogService.php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogService
{
    /**
     * Log an activity
     */
    public static function log(
        string $type,
        string $description,
        $subject = null,
        array $properties = [],
        ?User $user = null
    ): ActivityLog {
        return ActivityLog::log($type, $description, $subject, $properties, $user);
    }

    /**
     * Log user login
     */
    public static function userLogin(User $user): ActivityLog
    {
        return self::log(
            'user_login',
            'User logged in',
            $user,
            ['email' => $user->email],
            $user
        );
    }

    /**
     * Log user logout
     */
    public static function userLogout(User $user): ActivityLog
    {
        return self::log(
            'user_logout',
            'User logged out',
            $user,
            [],
            $user
        );
    }

    /**
     * Log profile update
     */
    public static function profileUpdated(User $user, array $changes): ActivityLog
    {
        return self::log(
            'profile_updated',
            'Profile information updated',
            $user,
            ['changes' => $changes],
            $user
        );
    }

    /**
     * Log password change
     */
    public static function passwordChanged(User $user): ActivityLog
    {
        return self::log(
            'password_changed',
            'Password was changed',
            $user,
            [],
            $user
        );
    }
}