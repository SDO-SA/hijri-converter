<?php

declare(strict_types=1);

namespace SDOSA\Locales;

final class TurkishLocale extends Locale
{
    public function tag(): string
    {
        return 'tr';
    }

    protected function hijriMonths(): array
    {
        return [
            'Muharrem', 'Safer', 'Rebiülevvel', 'Rebiülahir',
            'Cemaziyelevvel', 'Cemaziyelahir', 'Recep', 'Şaban',
            'Ramazan', 'Şevval', 'Zilkade', 'Zilhicce',
        ];
    }

    protected function gregorianMonths(): array
    {
        return [
            'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
            'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık',
        ];
    }

    protected function weekdays(): array
    {
        return ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
    }

    public function hijriNotation(): string
    {
        return 'Hicri';
    }

    public function gregorianNotation(): string
    {
        return 'Miladi';
    }
}
