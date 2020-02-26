<?php
namespace Sandstorm\GedmoTranslatableConnector;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Neos\Utility\ObjectAccess;

/**
 * This trait can be mixed into Models which have some properties being marked as Gedmo\Translatable.
 *
 * It adds a magic property `translations` which is an array of the following form (provided that 'name' and 'abstract'
 * are marked as translatable; and are translated in german and english):
 *
 * 'de' => [
 *   'name' => 'Name auf Deutsch',
 *   'abstract' => 'Der Abstract,
 * ],
 * 'en' => [
 *   'name' => 'Name in english',
 *   'abstract' => 'The abstract'
 * ]
 *
 *
 * This property can be read from and written to; so it can be used inside e.g. a Fluid form for the following:
 *
 * <f:form.textfield property="name" id="name" />
 * <f:form.textfield property="translations.de.name" id="name" />
 * <f:form.textfield property="translations.en.name" id="name" />
 *
 * Furthermore, it adds a method "reloadInLocale" which can be used to reload this object in a specific language.
 *
 * DEVELOPMENT HINT: In this trait, make sure that *ALL ANNOTATIONS* are FULLY-QUALIFIED, as Use-Statements are not properly
 *                   resolved when being in traits as far as I see
 */
trait TranslatableTrait {

	/**
	 * Doctrine's Entity Manager. Note that "ObjectManager" is the name of the related interface.
	 *
	 * @Neos\Flow\Annotations\Inject
	 * @var \Doctrine\ORM\EntityManagerInterface
	 */
	protected $entityManager;

	/**
	 * @Neos\Flow\Annotations\Transient
	 * @\Gedmo\Mapping\Annotation\Locale
	 * @var string
	 */
	protected $locale;

	/**
	 * @Neos\Flow\Annotations\Transient
	 * @var array
	 */
	protected $firstLevelTranslationsCache = [];

	/**
	 * @Neos\Flow\Annotations\Inject
	 * @var \Gedmo\Translatable\TranslatableListener
	 */
	protected $translatableListener;

	/**
	 * @return array
	 */
	public function getTranslations() {
		if (count($this->firstLevelTranslationsCache) > 0) {
			return $this->firstLevelTranslationsCache;
		}

		/* @var $repository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
		$repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
		$translations = $repository->findTranslations($this);

		if (property_exists($this, 'translationAssociationMapping')) {
			foreach ($translations as $language => $tmp) {
				foreach ($this->translationAssociationMapping as $internalKey => $key) {
					$possibleMethodName = ucfirst($key) . 'onLoad';
					if (method_exists($this, $possibleMethodName)) {
						if (isset($translations[$language][$internalKey])) {
							$translations[$language][$key] = $this->$possibleMethodName($translations[$language][$internalKey]);
						}
						unset($translations[$language][$internalKey]);
					}
				}
			}
		}
		$this->firstLevelTranslationsCache = $translations;

		return $this->firstLevelTranslationsCache;
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
	 * @return array
	 */
	public function getTranslationsByProperties(): array
	{
		$translations = [];
		foreach ($this->getTranslations() as $language => $values) {
			foreach ($values as $propertyName => $value) {
				$translations[$propertyName][$language] = $value;
			}
		}
		return $translations;
	}

	/**
	 * Return the translations of a property
	 *
	 * @param $propertyName
	 * @return array
	 */
	public function getTranslationsOfProperty($propertyName): array
	{
		$translations = array();
		foreach ($this->getTranslations() as $key => $values) {
			if (array_key_exists($propertyName, $values)) {
				$translations[$key] = $values[$propertyName];
			}
		}
		return $translations;
	}

	/**
	 * Return the property in a specific locale
	 *
	 * @param string $propertyName
	 * @param string $locale
	 * @return null|string
	 */
	public function getPropertyInLocale(string $propertyName, string $locale): ?string
	{
		$propertyTranslations = $this->getTranslationsOfProperty($propertyName);
		return $propertyTranslations[$locale] ?? null;
	}

	/**
	 * Reload this object in $locale
	 *ja
	 * @param string $locale
	 */
	public function reloadInLocale($locale) {
		$this->locale = $locale;
		$this->entityManager->refresh($this);
	}

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

	/**
	 * @param array $translations
	 */
	public function setTranslations(array $translations) {
		if (property_exists($this, 'translationAssociationMapping')) {
			foreach ($translations as $language => $tmp) {
				foreach ($this->translationAssociationMapping as $internalKey => $key) {
					$possibleMethodName = ucfirst($key) . 'onSave';
					if (method_exists($this, $possibleMethodName)) {
						if (isset($translations[$language][$key])) {
							$translations[$language][$internalKey] = $this->$possibleMethodName($translations[$language][$key]);
						}
						unset($translations[$language][$key]);
					}
				}
			}
		}

		/* @var $repository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
		$repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

		foreach ($translations as $language => $properties) {
            $meta = $this->entityManager->getClassMetadata(get_class($this));
            // $locale class property overwrites the translatable listener (for some reasons, this is not always the same)
			$locale = $this->locale ?? $this->translatableListener->getTranslatableLocale($this, $meta, $this->entityManager);
            foreach ($properties as $propertyName => $translatedValue) {
				/* Do not store empty translations since gedmo extension's behaviour has changed in
				https://github.com/Atlantic18/DoctrineExtensions/commit/6cc9fb3864a2562806d8a66276196825e3181c49 */
				if ($translatedValue) {
					if ($language === $locale) {
						/* Do not translate the default language by the repository. The repository->translate() does the
						same, but also persists the object ($this). However, persisting the object should not be handled
						withing this setter.
						TODO: Rethink the concept of calling the translation repository in here. Move it to a doctrine persistence listener? */
						ObjectAccess::setProperty($this, $propertyName, $translatedValue);
					} else {
						$repository->translate($this, $propertyName, $language, $translatedValue);
					}
				} else {
					$this->removeTranslation($repository, $language, $propertyName);
				}
			}
		}

		// Keep the cache up to date. This also supports getTranslations() calls without persisting the object.
		$this->firstLevelTranslationsCache = $translations;
	}

    /**
     * @param $repository
     * @param $language
     * @param $propertyName
     */
    private function removeTranslation($repository, $language, $propertyName)
    {
        $meta = $this->entityManager->getClassMetadata(get_class($this));
        $foreignKey = $meta->getReflectionProperty($meta->getSingleIdentifierFieldName())->getValue($this);
        $trans = $repository->findOneBy([
            'locale' => $language,
            'objectClass' => get_class($this),
            'field' => $propertyName,
            'foreignKey' => $foreignKey
        ]);
        if ($trans) {
            $this->entityManager->remove($trans);
        }
    }
}