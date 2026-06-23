<?php

declare(strict_types=1);

namespace SDOSA\Locales;

/**
 * Resolves the four bundled locales by tag and holds the package default.
 *
 * Supported tags: ar (default), en, bn, tr.
 */
final class LocaleRegistry
{
    /** @var array<string, Locale> */
    private static array $locales = [];

    private static string $default = 'ar';

    public static function setDefault(string $tag): void
    {
        self::$default = self::normalize($tag);
    }

    public static function default(): Locale
    {
        return self::get(self::$default);
    }

    /**
     * Resolve a locale. A null tag returns the default. Unknown tags also fall
     * back to the default rather than throwing, so callers never break on a
     * missing translation.
     */
    public static function get(?string $tag = null): Locale
    {
        self::bootstrap();

        $tag = self::normalize($tag ?? self::$default);

        return self::$locales[$tag]
            ?? self::$locales[self::$default]
            ?? self::$locales['ar'];
    }

    /** @return list<string> The supported language tags. */
    public static function supported(): array
    {
        self::bootstrap();

        return array_keys(self::$locales);
    }

    private static function normalize(string $tag): string
    {
        return strtolower(substr($tag, 0, 2));
    }

    private static function bootstrap(): void
    {
        if (self::$locales !== []) {
            return;
        }

        foreach ([new ArabicLocale(), new EnglishLocale(), new BengaliLocale(), new TurkishLocale()] as $locale) {
            self::$locales[$locale->tag()] = $locale;
        }
    }
}
