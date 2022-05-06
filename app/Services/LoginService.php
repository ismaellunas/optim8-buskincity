<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use JoelButcher\Socialstream\Socialstream;

class LoginService
{
    public static function isAdminLoginAttemptRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_login_attempt');
    }

    public static function isAdminTwoFactorLoginAttemptRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_two_factor_login_attempt');
    }

    public static function isAdminLoginRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_login');
    }

    public static function isAdminTwoFactorLoginRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_two_factor_login');
    }

    public static function setUserHomeUrl()
    {
        session(['home_url' => config('fortify.home')]);
    }

    public static function setAdminHomeUrl()
    {
        session(['home_url' => config('fortify.admin_home')]);
    }

    public static function isUserHomeUrl(): bool
    {
        return session('home_url') === config('fortify.home');
    }

    public static function isAdminHomeUrl(): bool
    {
        return session('home_url') === config('fortify.admin_home');
    }

    public static function hasHomeUrl(): bool
    {
        return session()->has('home_url');
    }

    public static function getHomeUrl(): ?string
    {
        return session('home_url');
    }

    public static function getAvailableSocialiteDrivers(): array
    {
        $drivers = app(SettingService::class)->getSocialiteDrivers();

        if (app()->environment('testing') || is_null($drivers)) {
            return config('socialstream.providers', []);
        }

        return $drivers;
    }

    public static function isConnectedAccountEnabled(): bool
    {
        return Socialstream::show() && self::isUserHomeUrl();
    }

    public static function setHomeUrl(Request $request): void
    {
        if ($request->routeIs('admin.*')) {
            self::setAdminHomeUrl();
        } else {
            self::setUserHomeUrl();
        }
    }
}
