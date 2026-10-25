<?php

namespace App\Support;

use Native\Desktop\Facades\Settings;
use Native\Desktop\Facades\System;

class RememberedLogin
{
    protected static function inDesktopApp(): bool
    {
        return str_contains(request()->userAgent() ?? '', 'Electron');
    }

   public static function save(string $email, string $password): void
{
    if (! self::inDesktopApp()) return;

    try {
        $encrypted = System::encrypt($password);

        if (! $encrypted) {
            self::forget(); // encryption not available: don't keep anything
            return;
        }

        Settings::set('remembered_email', $email);
        Settings::set('remembered_password', $encrypted);
    } catch (\Throwable $e) {
        report($e);
    }
}

    public static function get(): ?array
    {
        if (! self::inDesktopApp()) return null;

        try {
            $email = Settings::get('remembered_email');
            $enc   = Settings::get('remembered_password');
            if (! $email || ! $enc) return null;

            return ['email' => $email, 'password' => System::decrypt($enc)];
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function forget(): void
    {
        if (! self::inDesktopApp()) return;

        try {
            Settings::forget('remembered_email');
            Settings::forget('remembered_password');
        } catch (\Throwable $e) {
            report($e);
        }
    }
}