<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::getValue($key, $default);
    }
}

if (! function_exists('platform_logo_url')) {
    function platform_logo_url(): ?string
    {
        return Setting::platformLogoUrl();
    }
}
