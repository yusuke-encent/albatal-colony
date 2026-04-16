<?php

it('removes Japanese-support messaging from non-admin Vue pages', function () {
    $paths = [
        resource_path('js/pages/Welcome.vue'),
        resource_path('js/pages/Dashboard.vue'),
        resource_path('js/pages/Library/Index.vue'),
    ];

    $combinedSource = collect($paths)
        ->map(fn (string $path): string => file_get_contents($path))
        ->implode("\n");

    expect($combinedSource)
        ->not->toContain('Japanese')
        ->not->toContain('Japan to Global')
        ->toContain('Creator Marketplace')
        ->toContain('Digital content built for audiences worldwide.')
        ->toContain('Track key metrics by role and monitor the latest global sales across your catalog.')
        ->toContain('Re-download content you have already purchased.');
});
