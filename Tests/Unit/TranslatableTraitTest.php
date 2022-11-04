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
use Neos\Utility\ObjectAccess;
use PHPUnit\Framework\MockObject\MockObject;
use Sandstorm\GedmoTranslatableConnector\Tests\Unit\Fixture\TranslatableFixture;
use Sandstorm\GedmoTranslatableConnector\Tests\Unit\Fixture\TranslationsFixture;
use Sandstorm\GedmoTranslatableConnector\TranslatableManagement\TranslatableManagerInterface;


class TranslatableTraitTest extends UnitTestCase
{
    /** @var TranslatableFixture  */
    protected $obj;

    /** @var TranslatableManagerInterface&MockObject */
    protected TranslatableManagerInterface $translatableManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new TranslatableFixture();

        $this->translatableManager = $this->createMock(TranslatableManagerInterface::class);
        $this->inject($this->obj, 'translatableManager', $this->translatableManager);
    }

    /**
     * @test
     */
    public function getTranslationsCallsTranslationManagerExactlyOnce(): void
    {
        $this->translatableManager
            ->expects(self::once())
            ->method('getTranslations')
            ->with($this->obj)
            ->willReturn(TranslationsFixture::TRANSLATIONS);

        self::assertSame(TranslationsFixture::TRANSLATIONS, $this->obj->getTranslations());

        // Call it again will return the same without calling the translation manager
        self::assertSame(TranslationsFixture::TRANSLATIONS, $this->obj->getTranslations());
    }

    /**
     * @test
     */
    public function reloadInLocalePropagatesCallToTranslatableManager(): void
    {
        $this->translatableManager->expects(self::once())
            ->method('reloadInLocale')
            ->with($this->obj, 'en');

        $this->obj->reloadInLocale('en');
    }

    /**
     * @test
     */
    public function setTranslatableLocaleSetsLocaleProperty(): void
    {
        $this->obj->setTranslatableLocale('en');

        self::assertSame('en', ObjectAccess::getProperty($this->obj, 'locale', true));
    }

    /**
     * @test
     */
    public function setTranslationsReplacesTranslationsRecursively(): void
    {
        $this->translatableManager
            ->expects(self::once())
            ->method('translate')
            ->with($this->obj);

        // Getter should not reload the translation after setter call
        $this->translatableManager
            ->expects(self::never())
            ->method('getTranslations');

        ObjectAccess::setProperty($this->obj, 'translations', TranslationsFixture::TRANSLATIONS, true);

        $this->obj->setTranslations([
            'en' => [
                'name' => 'New name in english',      // Changed property
                'license' => 'GNU'                    // New property
            ],
            'fr' => [                                 // New translation
                'name' => 'Nom en allemand',
            ]
        ]);

        self::assertSame([
            'de' => [                                 // Unchanged translation
                'name' => 'Name auf Deutsch',
                'abstract' => 'Der Abstract',
            ],
            'en' => [
                'name' => 'New name in english',      // Changed property
                'abstract' => 'The abstract',
                'license' => 'GNU'                    // New property
            ],
            'fr' => [                                 // New translation
                'name' => 'Nom en allemand',
            ]
        ], $this->obj->getTranslations());
    }
}
