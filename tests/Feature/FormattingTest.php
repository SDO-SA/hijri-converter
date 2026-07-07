<?php

declare(strict_types=1);

use SDOSA\GregorianDate;
use SDOSA\HijriDate;

it('formats ISO and DMY', function () {
    $h = HijriDate::make(1403, 2, 17);
    expect($h->toIso())->toBe('1403-02-17')
        ->and($h->format())->toBe('17/02/1403')
        ->and($h->format('-'))->toBe('17-02-1403')
        ->and($h->format('/', false))->toBe('17/2/1403')
        ->and((string) $h)->toBe('1403-02-17');
});

it('formats a full localized string with notation', function () {
    $h = HijriDate::make(1403, 2, 17);
    expect($h->longFormat('en'))->toBe('17 Safar 1403 AH')
        ->and($h->longFormat('ar'))->toBe('17 صفر 1403 هـ');
});

it('builds Gregorian from DateTime and back', function () {
    $dt = new DateTimeImmutable('1982-12-02');
    $g = GregorianDate::fromDateTime($dt);
    expect($g->toIso())->toBe('1982-12-02')
        ->and($g->toDateTime()->format('Y-m-d'))->toBe('1982-12-02');
});

it('compares and equates dates', function () {
    $a = HijriDate::make(1445, 1, 1);
    $b = HijriDate::make(1445, 1, 2);
    expect($a->compareTo($b))->toBe(-1)
        ->and($b->compareTo($a))->toBe(1)
        ->and($a->equals(HijriDate::make(1445, 1, 1)))->toBeTrue();
});
