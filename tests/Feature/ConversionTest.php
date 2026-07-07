<?php

declare(strict_types=1);

use SDOSA\GregorianDate;
use SDOSA\HijriDate;

it('converts Gregorian to Hijri', function (string $greg, string $hijri) {
    expect(GregorianDate::fromString($greg)->toHijri()->toIso())->toBe($hijri);
})->with('conversion_pairs');

it('converts Hijri to Gregorian', function (string $greg, string $hijri) {
    expect(HijriDate::fromString($hijri)->toGregorian()->toIso())->toBe($greg);
})->with('conversion_pairs');

it('round-trips Hijri -> Gregorian -> Hijri across the range', function () {
    foreach ([1343, 1400, 1445, 1500] as $year) {
        foreach ([1, 6, 12] as $month) {
            $h = HijriDate::make($year, $month, 1);
            $back = $h->toGregorian()->toHijri();
            expect($back->equals($h))->toBeTrue("failed for {$h->toIso()}");
        }
    }
});

it('round-trips Gregorian -> Hijri -> Gregorian', function () {
    foreach (['1924-08-01', '1950-06-15', '2000-02-29', '2024-03-11', '2077-11-16'] as $iso) {
        $g = GregorianDate::fromString($iso);
        expect($g->toHijri()->toGregorian()->equals($g))->toBeTrue("failed for {$iso}");
    }
});

it('exposes month and year length', function () {
    $h = HijriDate::make(1445, 9, 1);
    expect($h->monthLength())->toBeIn([29, 30])
        ->and($h->yearLength())->toBeIn([354, 355]);
});

it('computes weekday and day name', function () {
    // 1982-12-02 was a Thursday.
    $g = GregorianDate::make(1982, 12, 2);
    expect($g->isoWeekday())->toBe(4)          // ISO: Monday = 1
        ->and($g->weekday())->toBe(5)          // Saudi week: Sunday = 1, so Thursday = 5
        ->and($g->dayName('en'))->toBe('Thursday')
        ->and($g->toHijri()->dayName('en'))->toBe('Thursday');
});
