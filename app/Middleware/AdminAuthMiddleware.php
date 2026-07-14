<?php
class AdminAuthMiddleware
{
    public static function handle(): void
    {
        if (!isAdminLoggedIn()) {
            flash('error', 'يجب تسجيل الدخول أولاً');
            header('Location: ' . adminUrl('login'));
            exit;
        }
    }
}
