<?php

namespace App\Services\Marketplace;

class GeneratesProductCodes
{
    public function forProviderPrice(int|string $merchantId, int $price, int $variantIndex = 1): string
    {
        $hash = md5(sprintf('%s:%d:%d', (string) $merchantId, $price, $variantIndex));
        $firstSegmentValue = hexdec(substr($hash, 0, 8));
        $secondSegmentValue = hexdec(substr($hash, 8, 8));
        $leadingDigit = $firstSegmentValue % 2 === 0 ? '1' : '2';
        $suffix = $secondSegmentValue % 10000;

        return sprintf('TT%s000%04d', $leadingDigit, $suffix);
    }
}
