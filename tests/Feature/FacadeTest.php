<?php

declare(strict_types=1);

use SDOSA\GregorianDate;
use SDOSA\Hijri;
use SDOSA\HijriDate;

it('converts via the single static class', function () {
    expect(Hijri::toHijri('1982-12-02'))->toBeInstanceOf(HijriDate::class)
        ->and(Hijri::toHijri('1982-12-02')->toIso())->toBe('1403-02-17')
        ->and(Hijri::toGregorian('1403-02-17'))->toBeInstanceOf(GregorianDate::class)
        ->and(Hijri::toGregorian('1403-02-17')->toIso())->toBe('1982-12-02');
});

it('accepts DateTime input', function () {
    expect(Hijri::toHijri(new DateTimeImmutable('1982-12-02'))->toIso())->toBe('1403-02-17');
});

it('accepts value objects (idempotent)', function () {
    $g = new GregorianDate(1982, 12, 2);
    $h = new HijriDate(1403, 2, 17);
    expect(Hijri::toHijri($g)->toIso())->toBe('1403-02-17')
        ->and(Hijri::toGregorian($h)->toIso())->toBe('1982-12-02');
});

it('returns today', function () {
    expect(Hijri::today())->toBeInstanceOf(HijriDate::class);
});
