<?php
namespace Sandstorm\GedmoTranslatableConnector;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

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
	 * @return array
	 */
	public function getTranslations() {
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

		return $translations;
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
			foreach ($properties as $propertyName => $translatedValue) {
				$repository->translate($this, $propertyName, $language, $translatedValue);
			}
		}
	}
}