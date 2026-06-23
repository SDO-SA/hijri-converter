# hijri-converter

Accurate **Hijri (Umm al-Qura) ⇄ Gregorian** date conversion for PHP.

- Exact conversions backed by the official Umm al-Qura month-start table — not arithmetic approximations.
- One class to remember: `SDOSA\Hijri` with `::toHijri()` and `::toGregorian()`.
- Localized names in **Arabic, English, Bengali, and Turkish** out of the box.
- Zero required dependencies. Optional first-class Laravel support.
- Supported span: Gregorian **1924-08-01 → 2077-11-16**, Hijri **1343 → 1500**.

## Installation

```bash
composer require sdo-sa/hijri-converter
```

Requires PHP 8.2+.

## Usage

Everything goes through the single static class `SDOSA\Hijri` — same in plain PHP or Laravel:

```php
use SDOSA\Hijri;

$h = Hijri::toHijri('1982-12-02');     // accepts string, DateTime, or Carbon
echo $h->toIso();                      // 1403-02-17
echo $h->format();                     // 17/02/1403
echo $h->monthName();                  // صفر   (default locale is "ar")
echo $h->monthName('en');              // Safar
echo $h->dayName();                    // الخميس

$g = Hijri::toGregorian('1403-02-17'); // accepts string or HijriDate
echo $g->toIso();                      // 1982-12-02

Hijri::today();                        // HijriDate
```

Conversions return small immutable value objects — `SDOSA\HijriDate` and
`SDOSA\GregorianDate` — which carry the rich API (`format`, `monthName`,
`dayName`, `notation`, `weekday`, `toCarbon`, `compareTo`, …). You can also
construct them directly:

```php
use SDOSA\HijriDate;
use SDOSA\GregorianDate;

(new GregorianDate(1982, 12, 2))->toHijri();
(new HijriDate(1403, 2, 17))->toGregorian();
```

Out-of-range or impossible dates throw `SDOSA\Exceptions\DateOutOfRange`,
`InvalidHijriDate`, or `InvalidGregorianDate` (all extend `HijriException`).

## Locales

Four locales are bundled: **`ar`** (default), **`en`**, **`bn`** (Bengali), **`tr`** (Turkish).

Pass a tag explicitly to any name method:

```php
use SDOSA\Hijri;

Hijri::toHijri('2024-03-11')->monthName('tr');  // Ramazan
Hijri::toHijri('2024-03-11')->monthName('bn');  // রমজান
```

Or change the default used when no tag is passed. In plain PHP:

```php
use SDOSA\Locales\LocaleRegistry;

LocaleRegistry::setDefault('en');
```

In Laravel, set it in `config/hijri.php` (or the `HIJRI_LOCALE` env var) — the
service provider applies it on boot.

## Laravel

The service provider is auto-discovered. The core API is static, so there is no
facade to register — just call `SDOSA\Hijri` anywhere. The provider adds config,
the default-locale wiring, and Carbon macros.

```bash
php artisan vendor:publish --tag=hijri-config
```

```php
use SDOSA\Hijri;
use Carbon\Carbon;

Hijri::toHijri(Carbon::now());     // accepts Carbon / DateTime
Carbon::now()->toHijri();          // HijriDate (macro)
Carbon::now()->hijriFormat('-');   // e.g. 01-09-1445 (macro)
```

## Credits

The Umm al-Qura dataset and conversion algorithm derive from
[`dralshehri/hijridate`](https://github.com/dralshehri/hijridate) (MIT). See `LICENSE`.
