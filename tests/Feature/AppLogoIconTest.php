<?php

it('uses the public favicon for the app logo icon', function () {
    $source = file_get_contents(resource_path('js/components/AppLogoIcon.vue'));
    $logoUsageLines = collect([
        resource_path('js/components/AppLogo.vue'),
        resource_path('js/components/AppHeader.vue'),
        resource_path('js/layouts/auth/AuthCardLayout.vue'),
        resource_path('js/layouts/auth/AuthSimpleLayout.vue'),
        resource_path('js/layouts/auth/AuthSplitLayout.vue'),
    ])->flatMap(function (string $path): array {
        return preg_grep('/AppLogoIcon/', file($path));
    })->implode("\n");

    expect($source)
        ->toContain('src="/favicon.svg"')
        ->not->toContain('<svg');

    expect($logoUsageLines)
        ->not->toContain('fill-current')
        ->not->toContain('text-black');
});
