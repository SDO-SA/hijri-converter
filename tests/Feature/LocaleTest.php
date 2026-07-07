<?php

declare(strict_types=1);

use SDOSA\HijriDate;
use SDOSA\Locales\LocaleRegistry;

// Default locale is "ar"; reset after each test that changes it.
afterEach(fn () => LocaleRegistry::setDefault('ar'));

it('returns Arabic names by default', function () {
    expect((HijriDate::make(1445, 9, 1))->monthName())->toBe('رمضان');
});

it('returns English names when requested', function () {
    expect((HijriDate::make(1445, 9, 1))->monthName('en'))->toBe('Ramadan');
});

it('honours an overridden default locale', function () {
    LocaleRegistry::setDefault('en');
    expect((HijriDate::make(1445, 9, 1))->monthName())->toBe('Ramadan');
});

it('falls back to default for an unknown locale', function () {
    expect((HijriDate::make(1445, 9, 1))->monthName('zz'))->toBe('رمضان');
});

it('normalises locale tags like en-US', function () {
    expect((HijriDate::make(1445, 9, 1))->monthName('en-US'))->toBe('Ramadan');
});

it('provides notations', function () {
    expect((HijriDate::make(1445, 9, 1))->notation())->toBe('هـ')
        ->and((HijriDate::make(1445, 9, 1))->notation('en'))->toBe('AH');
});

it('bundles Bengali and Turkish', function () {
    $h = HijriDate::make(1445, 9, 1); // Ramadan
    expect($h->monthName('bn'))->toBe('রমজান')
        ->and($h->monthName('tr'))->toBe('Ramazan')
        ->and($h->notation('tr'))->toBe('Hicri');
});

it('reports the four supported locales', function () {
    expect(LocaleRegistry::supported())->toEqualCanonicalizing(['ar', 'en', 'bn', 'tr']);
});
