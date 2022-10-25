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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\TranslatableListener;
use Neos\Flow\Tests\UnitTestCase;
use Neos\Utility\ObjectAccess;
use PHPUnit\Framework\MockObject\MockObject;
use Sandstorm\GedmoTranslatableConnector\Tests\Unit\Fixture\TranslationsFixture;
use Sandstorm\GedmoTranslatableConnector\Translatable;
use Sandstorm\GedmoTranslatableConnector\TranslatableManagement\TranslatableManager;

class TranslatableManagerTest extends UnitTestCase
{
    /** @var TranslatableManager  */
    protected $obj;

    /** @var Translatable&MockObject  */
    protected Translatable $fixture;

    /** @var EntityManagerInterface&MockObject */
    protected EntityManagerInterface $entityManager;

    /** @var TranslatableListener&MockObject */
    protected TranslatableListener $translatableListener;

    /** @var TranslationRepository&MockObject */
    protected TranslationRepository $translationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new TranslatableManager();

        $this->fixture = $this->createMock(Translatable::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->inject($this->obj, 'entityManager', $this->entityManager);
        $this->translatableListener = $this->createMock(TranslatableListener::class);
        $this->inject($this->obj, 'translatableListener', $this->translatableListener);

        $this->translationRepository = $this->createMock(TranslationRepository::class);
        $this->entityManager
            ->expects(self::any())
            ->method('getRepository')
            ->willReturn($this->translationRepository);
        $this->entityManager
            ->expects(self::any())
            ->method('getClassMetadata')
            ->willReturn($this->createMock(ClassMetadata::class));
    }

    /**
     * @test
     */
    public function getTranslationsCallsRepository(): void
    {
        $this->translationRepository
            ->expects(self::once())
            ->method('findTranslations')
            ->with($this->fixture)
            ->willReturn(TranslationsFixture::TRANSLATIONS);

        self::assertSame(TranslationsFixture::TRANSLATIONS, $this->obj->getTranslations($this->fixture));
    }

    /**
     * @test
     */
    public function translateWithInstantTranslationUpdatesTranslation(): void
    {
        $this->inject($this->obj, 'instantTranslation', true);

        $this->fixture->expects(self::once())
            ->method('getTranslations')
            ->willReturn(TranslationsFixture::TRANSLATIONS);

        $this->translationRepository->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive(
                [$this->fixture, 'name', 'de', 'Name auf Deutsch'],
                [$this->fixture, 'abstract', 'de', 'Der Abstract'],
                [$this->fixture, 'name', 'en', 'Name in english'],
                [$this->fixture, 'abstract', 'en', 'The abstract'],
            );

        $this->obj->translate($this->fixture);
    }

    /**
     * @test
     */
    public function translateWithoutInstantTranslationAddsChangedEntity(): void
    {
        $this->inject($this->obj, 'instantTranslation', false);

        $this->obj->translate($this->fixture);

        /** @var Translatable[] $changedEntities */
        $changedEntities = ObjectAccess::getProperty($this->obj, 'changedEntities', true);

        self::assertContains($this->fixture, $changedEntities);
    }

    /**
     * @test
     */
    public function reloadInLocaleRefreshesEntity(): void
    {
        $this->fixture->expects(self::once())
            ->method('setTranslatableLocale')
            ->with('en');

        $this->entityManager->expects(self::once())
            ->method('refresh')
            ->with($this->fixture);

        $this->obj->reloadInLocale($this->fixture, 'en');
    }

    /**
     * @test
     */
    public function flushTranslatesChangedEntities(): void
    {
        $this->fixture->expects(self::once())
            ->method('getTranslations')
            ->willReturn(TranslationsFixture::TRANSLATIONS);

        ObjectAccess::setProperty($this->obj, 'changedEntities', [$this->fixture], true);

        $this->translationRepository->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive(
                [$this->fixture, 'name', 'de', 'Name auf Deutsch'],
                [$this->fixture, 'abstract', 'de', 'Der Abstract'],
                [$this->fixture, 'name', 'en', 'Name in english'],
                [$this->fixture, 'abstract', 'en', 'The abstract'],
            );

        $this->obj->flush();

        /** @var Translatable[] $changedEntities */
        $changedEntities = ObjectAccess::getProperty($this->obj, 'changedEntities', true);

        self::assertEmpty($changedEntities);
    }
}
