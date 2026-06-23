<?php

declare(strict_types=1);

use SDOSA\Support\GregorianMath;
use SDOSA\Support\Julian;
use SDOSA\Support\Math;

it('bisects to the right insertion point', function () {
    $a = [10, 20, 20, 30];
    expect(Math::bisectRight($a, 5))->toBe(0)
        ->and(Math::bisectRight($a, 20))->toBe(3)
        ->and(Math::bisectRight($a, 35))->toBe(4);
});

it('divmods with floor semantics', function () {
    expect(Math::divmod(7, 3))->toBe([2, 1])
        ->and(Math::divmod(-7, 3))->toBe([-3, 2]);
});

it('round-trips ordinal <-> ymd', function () {
    foreach (['1924-08-01', '1982-12-02', '2024-02-29', '2077-11-16'] as $iso) {
        [$y, $m, $d] = array_map('intval', explode('-', $iso));
        $ord = GregorianMath::ymdToOrdinal($y, $m, $d);
        expect(GregorianMath::ordinalToYmd($ord))->toBe([$y, $m, $d]);
    }
});

it('knows leap years', function () {
    expect(GregorianMath::isLeap(2000))->toBeTrue()
        ->and(GregorianMath::isLeap(1900))->toBeFalse()
        ->and(GregorianMath::isLeap(2024))->toBeTrue();
});

it('round-trips julian helpers', function () {
    expect(Julian::jdnToOrdinal(Julian::ordinalToJdn(123456)))->toBe(123456)
        ->and(Julian::rjdToJdn(Julian::jdnToRjd(999999)))->toBe(999999);
});
