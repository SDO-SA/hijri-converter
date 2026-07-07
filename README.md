# hijri-converter

Accurate **Hijri (Umm al-Qura) ⇄ Gregorian** date conversion for PHP.

- **Exact, not approximate.** Backed by the official Umm al-Qura calendar — no arithmetic guesswork.
- **One class to remember:** `SDOSA\Hijri`, with `::toHijri()` and `::toGregorian()`.
- **Localized** month and day names in **Arabic, English, Bengali, and Turkish** out of the box.
- **Zero required dependencies.** Optional first-class Laravel + Carbon support.
- **Supported span:** Gregorian `1924-08-01 → 2077-11-16`, Hijri `1343 → 1500`.

## Installation

```bash
composer require sdo-sa/hijri-converter
```

Requires PHP 8.2+.

## Quick start

Everything goes through the single static class `SDOSA\Hijri` — identical in plain PHP or Laravel:

```php
use SDOSA\Hijri;

$h = Hijri::toHijri('1982-12-02');   // accepts string, DateTime, or Carbon
echo $h->toIso();                    // 1403-02-17
echo $h->format();                   // 17/02/1403
echo $h->monthName();                // صفر   (default locale is "ar")
echo $h->monthName('en');            // Safar
echo $h->dayName();                  // الخميس

$g = Hijri::toGregorian('1403-02-17');
echo $g->toIso();                    // 1982-12-02

Hijri::today();                      // HijriDate for today
```

## Value objects

Conversions return small **immutable** value objects — `SDOSA\HijriDate` and
`SDOSA\GregorianDate` — that carry the rich API below. Build them yourself with
`::make()` (or a named constructor); the plain `new` constructor is intentionally
closed off, so there is exactly one obvious way to create a date.

```php
use SDOSA\GregorianDate;
use SDOSA\HijriDate;

GregorianDate::make(1982, 12, 2)->toHijri();       // HijriDate
HijriDate::make(1403, 2, 17)->toGregorian();       // GregorianDate

GregorianDate::fromString('1982-12-02');
HijriDate::fromString('1403-02-17');
GregorianDate::fromDateTime(new DateTimeImmutable); // any DateTime / Carbon
```

### API reference

Both value objects share the same shape:

| Method                       | Returns          | Example                       |
| ---------------------------- | ---------------- | ----------------------------- |
| `year()` `month()` `day()`   | `int`            | `1403`, `2`, `17`             |
| `toIso()`                    | `string`         | `1403-02-17`                  |
| `format($sep = '/', $pad)`   | `string`         | `17/02/1403`                  |
| `monthName($locale = null)`  | `string`         | `صفر` / `Safar`               |
| `dayName($locale = null)`    | `string`         | `الخميس` / `Thursday`         |
| `notation($locale = null)`   | `string`         | `هـ` / `AH`                   |
| `longFormat($locale = null)` | `string`         | `17 صفر 1403 هـ` / `17 Safar 1403 AH` |
| `isoWeekday()`               | `int` (1–7)      | `4` — ISO, Monday = 1         |
| `weekday()`                  | `int` (1–7)      | `5` — Saudi week, Sunday = 1  |
| `equals($other)`             | `bool`           |                               |
| `compareTo($other)`          | `int` (-1, 0, 1) |                               |
| `(string) $date`             | `string`         | same as `toIso()`            |

`HijriDate` adds `monthLength()`, `yearLength()`, and `toGregorian()`;
`GregorianDate` adds `toDateTime()`, `toCarbon()`, and `toHijri()`.

Out-of-range or impossible dates throw `SDOSA\Exceptions\DateOutOfRange`,
`InvalidHijriDate`, or `InvalidGregorianDate` — all extend `HijriException`.

## Locales

Four locales ship in the box: **`ar`** (default), **`en`**, **`bn`** (Bengali), **`tr`** (Turkish).

Pass a tag to any name method:

```php
Hijri::toHijri('2024-03-11')->monthName('tr');  // Ramazan
Hijri::toHijri('2024-03-11')->monthName('bn');  // রমজান
```

Or change the default used when no tag is passed:

```php
use SDOSA\Locales\LocaleRegistry;

LocaleRegistry::setDefault('en');
```

In Laravel, set it in `config/hijri.php` (or the `HIJRI_LOCALE` env var) instead —
the service provider applies it on boot. Unknown tags fall back to the default,
so a missing translation never breaks a call.

## Laravel

The service provider is auto-discovered. The core API is static, so there is no
facade to register — just call `SDOSA\Hijri` anywhere. The provider wires up
config, the default locale, and Carbon macros.

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

## Try it

- **CLI:** `php examples/demo.php`
- **Web:** a self-contained, interactive converter that runs the real library —

  ```bash
  php -S localhost:8000 -t examples/web
  ```

  then open <http://localhost:8000>.

## Credits

The Umm al-Qura dataset and conversion algorithm derive from
[`dralshehri/hijridate`](https://github.com/dralshehri/hijridate) (MIT). See `LICENSE`.
