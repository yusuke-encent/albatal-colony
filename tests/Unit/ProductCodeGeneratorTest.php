<?php

use App\Services\Marketplace\GeneratesProductCodes;

it('matches the reference product code algorithm', function (int $merchantId, int $price, string $expected): void {
    $generator = new GeneratesProductCodes();

    expect($generator->forProviderPrice($merchantId, $price))->toBe($expected);
})->with([
    [1, 480, 'TT20003770'],
    [1, 930, 'TT10008567'],
    [2, 12800, 'TT20006671'],
]);
