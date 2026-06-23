<?php

declare(strict_types=1);

namespace SDOSA\Locales;

final class BengaliLocale extends Locale
{
    public function tag(): string
    {
        return 'bn';
    }

    protected function hijriMonths(): array
    {
        return [
            'মুহাররম', 'সফর', 'রবিউল আউয়াল', 'রবিউস সানী',
            'জুমাদাল উলা', 'জুমাদাস সানী', 'রজব', 'শাবান',
            'রমজান', 'শাওয়াল', 'জিলক্বদ', 'জিলহজ',
        ];
    }

    protected function gregorianMonths(): array
    {
        return [
            'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
            'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর',
        ];
    }

    protected function weekdays(): array
    {
        return ['সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার', 'রবিবার'];
    }

    public function hijriNotation(): string
    {
        return 'হিজরি';
    }

    public function gregorianNotation(): string
    {
        return 'খ্রিস্টাব্দ';
    }
}
