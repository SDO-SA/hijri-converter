<?php

declare(strict_types=1);

namespace SDOSA\Locales;

final class ArabicLocale extends Locale
{
    public function tag(): string
    {
        return 'ar';
    }

    protected function hijriMonths(): array
    {
        return [
            'محرم', 'صفر', 'ربيع الأول', 'ربيع الآخر',
            'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان',
            'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة',
        ];
    }

    protected function gregorianMonths(): array
    {
        return [
            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
        ];
    }

    protected function weekdays(): array
    {
        return ['الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'];
    }

    public function hijriNotation(): string
    {
        return 'هـ';
    }

    public function gregorianNotation(): string
    {
        return 'م';
    }
}
