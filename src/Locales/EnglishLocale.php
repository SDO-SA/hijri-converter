<?php

declare(strict_types=1);

namespace SDOSA\Locales;

final class EnglishLocale extends Locale
{
    public function tag(): string
    {
        return 'en';
    }

    protected function hijriMonths(): array
    {
        return [
            'Muharram', 'Safar', 'Rabi al-Awwal', 'Rabi al-Thani',
            'Jumada al-Ula', 'Jumada al-Akhirah', 'Rajab', 'Shaban',
            'Ramadan', 'Shawwal', 'Dhu al-Qadah', 'Dhu al-Hijjah',
        ];
    }

    protected function gregorianMonths(): array
    {
        return [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
    }

    protected function weekdays(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    public function hijriNotation(): string
    {
        return 'AH';
    }

    public function gregorianNotation(): string
    {
        return 'CE';
    }
}
