<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector\Tests\Unit;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Neos\Flow\Tests\UnitTestCase;
use Sandstorm\GedmoTranslatableConnector\Tests\Unit\Fixture\TranslationsFixture;
use Sandstorm\GedmoTranslatableConnector\Translatable;
use Sandstorm\GedmoTranslatableConnector\Utility;

class UtilityTest extends UnitTestCase
{
    protected Translatable $fixture;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = $this->createMock(Translatable::class);
        $this->fixture->expects(self::any())->method('getTranslations')->willReturn(TranslationsFixture::TRANSLATIONS);
    }

    /**
     * @test
     */
    public function translationsByLocalesReturnsArrayWithLocaleKeys(): void
    {
        $expected = TranslationsFixture::TRANSLATIONS;

        self::assertSame($expected, Utility::translationsByLocales($this->fixture));
    }

    /**
     * @test
     */
    public function translationsByPropertiesReturnsArrayWithPropertyKeys(): void
    {
        $expected = [
            'name' => [
                'de' => 'Name auf Deutsch',
                'en' => 'Name in english',
            ],
            'abstract' => [
                'de' => 'Der Abstract',
                'en' => 'The abstract',
            ],
        ];

        self::assertSame($expected, Utility::translationsByProperties($this->fixture));
    }

    protected function propertyTranslationsReturnsPropertyArrayWithLocaleKeysForSpecificPropertyDataProvider(): array
    {
        return [
            ['name', [
                'de' => 'Name auf Deutsch',
                'en' => 'Name in english',
            ]],
            ['abstract', [
                'de' => 'Der Abstract',
                'en' => 'The abstract',
            ]],
        ];
    }

    /**
     * @test
     * @dataProvider propertyTranslationsReturnsPropertyArrayWithLocaleKeysForSpecificPropertyDataProvider
     */
    public function propertyTranslationsReturnsPropertyArrayWithLocaleKeysForSpecificProperty(
        string $propertyName,
        array $expected,
    ): void {
        self::assertSame($expected, Utility::propertyTranslations($this->fixture, $propertyName));
    }

    protected function propertyTranslationInLocaleReturnsSingleTranslationDataProvider(): array
    {
        return [
            ['name', 'de', 'Name auf Deutsch'],
            ['name', 'en', 'Name in english'],
            ['abstract', 'de', 'Der Abstract'],
            ['abstract', 'en', 'The abstract'],
        ];
    }

    /**
     * @test
     * @dataProvider propertyTranslationInLocaleReturnsSingleTranslationDataProvider
     */
    public function propertyTranslationInLocaleReturnsSingleTranslation(
        string $propertyName,
        string $locale,
        string $expected,
    ): void {
        self::assertSame($expected, Utility::propertyTranslationInLocale($this->fixture, $propertyName, $locale));
    }
}
