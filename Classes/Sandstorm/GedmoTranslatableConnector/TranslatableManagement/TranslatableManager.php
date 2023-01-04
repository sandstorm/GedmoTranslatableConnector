<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector\TranslatableManagement;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\TranslatableListener;
use Neos\Flow\Annotations as Flow;
use Sandstorm\GedmoTranslatableConnector\Translatable;

/**
 * @Flow\Scope("singleton")
 */
class TranslatableManager implements TranslatableManagerInterface
{
    /**
     * Array to track the changed entities that should get updated.
     *
     * @var Translatable[]
     */
    protected array $changedEntities = [];

    /**
     * Doctrine's Entity Manager. Note that "ObjectManager" is the name of the related interface.
     *
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var TranslatableListener
     */
    protected $translatableListener;

    /**
     * @Flow\InjectConfiguration(package="Sandstorm.GedmoTranslatableConnector", path="instantTranslation")
     * @var bool
     */
    protected $instantTranslation;

    /**
     * Fetch the translations from the repository.
     *
     * @param Translatable $entity
     * @return array<string, array<string, string>>
     */
    public function getTranslations(Translatable $entity): array
    {
        /** @var TranslationRepository $repository */
        $repository = $this->entityManager->getRepository(Translation::class);

        return $repository->findTranslations($entity);
    }

    /**
     * With enabled "instantTranslation" setting (default), this translates and persists the entity's translations.
     * With disabled "instantTranslation" this tracks the changed entity to update and persist the translations later
     * on through the flush method.
     */
    public function translate(Translatable $entity): void
    {
        if ($this->instantTranslation) {
            $this->updateTranslation($entity);
        } else {
            $this->addChangedEntity($entity);
        }
    }

    /**
     * Reload the entity in the specific locale.
     */
    public function reloadInLocale(Translatable $entity, string $locale): void
    {
        $entity->setTranslatableLocale($locale);
        $this->entityManager->refresh($entity);
    }

    /**
     * Update and persist all changed translatable entities which are tracked through the translate method.
     */
    public function flush(): void
    {
        foreach ($this->changedEntities as $changedEntity) {
            $this->updateTranslation($changedEntity);
        }
    }

    protected function updateTranslation(Translatable $entity): void
    {
        /* Get the repository of the actual translationClass if specified. Unlike other functions inside the
        TranslationRepository, the findOneBy() method inside the translate() function does not recognize the object's
        translationClass configuration. */
        $meta = $this->entityManager->getClassMetadata($entity::class);
        $config = $this->translatableListener->getConfiguration($this->entityManager, $meta->getName());
        $class = $config['translationClass'] ?? Translation::class;

        /** @var TranslationRepository $repository */
        $repository = $this->entityManager->getRepository($class);

        foreach ($entity->getTranslations() as $locale => $fields) {
            foreach ($fields as $field => $translatedValue) {
                $repository->translate($entity, $field, $locale, $translatedValue);
            }
        }

        $this->removeChangedEntity($entity);
    }

    public function addChangedEntity(Translatable $entity): void
    {
        if (!in_array($entity, $this->changedEntities, true)) {
            $this->changedEntities[] = $entity;
        }
    }

    public function removeChangedEntity(Translatable $entity): void
    {
        $key = array_search($entity, $this->changedEntities, true);
        if ($key !== false) {
            unset($this->changedEntities[$key]);
        }
    }
}
