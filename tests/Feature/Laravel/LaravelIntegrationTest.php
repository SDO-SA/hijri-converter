<?php

declare(strict_types=1);

use Carbon\Carbon;
use SDOSA\GregorianDate;
use SDOSA\HijriDate;

it('registers Carbon macros', function () {
    expect(Carbon::parse('1982-12-02')->toHijri())->toBeInstanceOf(HijriDate::class)
        ->and(Carbon::parse('1982-12-02')->toHijri()->toIso())->toBe('1403-02-17')
        ->and(Carbon::parse('1982-12-02')->hijriFormat('-'))->toBe('17-02-1403');
});

it('converts Gregorian to Carbon', function () {
    expect((new GregorianDate(1982, 12, 2))->toCarbon()->format('Y-m-d'))->toBe('1982-12-02');
});

it('loads the package config default locale', function () {
    expect(config('hijri.default_locale'))->toBe('ar');
});
