<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector\Tests\Functional;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Tests\FunctionalTestCase;
use Sandstorm\GedmoTranslatableConnector\Tests\Functional\Fixture\TranslatableFixture;
use Sandstorm\GedmoTranslatableConnector\Tests\Functional\Fixture\TranslatableFixtureWithTranslationEntity;
use Sandstorm\GedmoTranslatableConnector\Tests\Functional\Fixture\TranslatableFixtureWithTranslationEntityTranslation;
use Sandstorm\GedmoTranslatableConnector\Tests\Unit\Fixture\TranslationsFixture;
use Sandstorm\GedmoTranslatableConnector\TranslatableManagement\TranslatableManager;

/**
 * TranslatableManager t
 */
class TranslatableManagerTest extends FunctionalTestCase
{
    protected static $testablePersistenceEnabled = true;

    protected TranslatableManager $obj;
    protected TranslatableFixture $fixture;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->obj = $this->objectManager->get(TranslatableManager::class);
    }

    /**
     * @test
     */
    public function setTranslationsWithInstantTranslationAndReloadInLocale(): void
    {
        $this->fixture = new TranslatableFixture();
        $this->inject($this->fixture, 'translatableManager', $this->obj);

        $this->inject($this->obj, 'instantTranslation', true);

        $this->fixture->setTranslations(TranslationsFixture::TRANSLATIONS);

        $this->persistenceManager->add($this->fixture);
        $this->persistenceManager->persistAll();

        $this->obj->reloadInLocale($this->fixture, 'en');

        $this->assertSame('Name in english', $this->fixture->getName());
        $this->assertSame('The abstract', $this->fixture->getAbstract());
    }

    /**
     * @test
     */
    public function setTranslationsWithDisabledInstantTranslationAndReloadInLocale(): void
    {
        $this->fixture = new TranslatableFixture();
        $this->inject($this->fixture, 'translatableManager', $this->obj);

        $this->inject($this->obj, 'instantTranslation', false);

        $this->fixture->setTranslations(TranslationsFixture::TRANSLATIONS);
        $this->obj->flush();

        $this->persistenceManager->add($this->fixture);
        $this->persistenceManager->persistAll();

        $this->obj->reloadInLocale($this->fixture, 'en');

        $this->assertSame('Name in english', $this->fixture->getName());
        $this->assertSame('The abstract', $this->fixture->getAbstract());
    }

    /**
     * @test
     */
    public function setTranslationsWithTranslationEntityReturnsTranslationWithConcreteRepository(): void
    {
        $entityManager = $this->objectManager->get(EntityManagerInterface::class);

        $this->fixture = new TranslatableFixtureWithTranslationEntity();
        $this->inject($this->fixture, 'translatableManager', $this->obj);

        $this->inject($this->obj, 'instantTranslation', true);

        $this->fixture->setTranslations(TranslationsFixture::TRANSLATIONS);

        $this->persistenceManager->add($this->fixture);
        $this->persistenceManager->persistAll();

        // Get the concrete repository to test that the translations got written to the specific entity.
        $repository = $entityManager->getRepository(TranslatableFixtureWithTranslationEntityTranslation::class);

        $translation = $repository->findOneBy([
            'locale' => 'en',
            'objectClass' => TranslatableFixtureWithTranslationEntity::class,
            'field' => 'name',
        ]);

        $this->assertSame('Name in english', $translation->getContent());
    }
}
