@props([
    'platformName' => $platformName ?? \App\Models\Setting::platformName(),
    'globalLogoUrl' => $globalLogoUrl ?? platform_logo_url(),
    'fallbackText' => 'LBC',
])

@if($globalLogoUrl)
    <img
        src="{{ $globalLogoUrl }}"
        alt="{{ $platformName ?: 'LBC Chile' }}"
        {{ $attributes->class('h-10 w-auto max-w-[140px] object-contain sm:max-w-[180px]') }}
    >
@else
    <div {{ $attributes->class('flex h-12 w-12 items-center justify-center rounded-xl bg-brand-700 text-lg font-extrabold text-white') }}>
        {{ $fallbackText }}
    </div>
@endif
