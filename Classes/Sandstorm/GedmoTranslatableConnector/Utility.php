<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class Utility
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function translationsByLocales(Translatable $object): array
    {
        return $object->getTranslations();
    }

    /**
     * Return the translations in the format of
     * 'name' => [
     *   'de' => 'Name auf Deutsch',
     *   'en' => 'Name in english',
     * ],
     * 'abstract' => [
     *   'de' => 'Der Abstract,
     *   'en' => 'The abstract'
     * ]
     * @return array<string, array<string, string>>
     */
    public static function translationsByProperties(Translatable $object): array
    {
        $translations = [];
        foreach ($object->getTranslations() as $language => $values) {
            foreach ($values as $propertyName => $value) {
                $translations[$propertyName][$language] = $value;
            }
        }

        return $translations;
    }


    /**
     * Return the translations in the format of
     * [
     *   'de' => 'Der Abstract,
     *   'en' => 'The abstract'
     * ]
     * @return array<string, string>
     */
    public static function propertyTranslations(Translatable $object, string $propertyName): array
    {
        $translations = self::translationsByProperties($object);
        if (array_key_exists($propertyName, $translations)) {
            return $translations[$propertyName];
        }

        return [];
    }


    /**
     * Return the property in a specific locale
     */
    public static function propertyTranslationInLocale(
        Translatable $object,
        string $propertyName,
        string $locale
    ): ?string {
        $translations = self::propertyTranslations($object, $propertyName);
        if (array_key_exists($locale, $translations)) {
            return $translations[$locale];
        }

        return null;
    }
}
