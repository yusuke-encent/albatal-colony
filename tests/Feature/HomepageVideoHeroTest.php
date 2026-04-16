<?php

it('renders the homepage hero video without controls and with crt overlay styling', function () {
    $source = file_get_contents(resource_path('js/pages/Welcome.vue'));

    expect($source)
        ->toContain(':src="heroVideoUrl"')
        ->toContain('IntersectionObserver')
        ->toContain('shouldLoadHeroVideo && heroVideoUrl')
        ->toContain('autoplay')
        ->toContain('muted')
        ->toContain('loop')
        ->toContain('playsinline')
        ->toContain('preload="none"')
        ->toContain('rounded-[inherit]')
        ->toContain('crt-noise')
        ->toContain('crt-scanlines')
        ->not->toContain('controls');
});
