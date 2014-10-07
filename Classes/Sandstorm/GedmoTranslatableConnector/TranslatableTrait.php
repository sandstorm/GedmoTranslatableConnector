<?php
namespace Sandstorm\GedmoTranslatableConnector;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

/**
 * This trait can be mixed into Models which have some properties being marked as Gedmo\Translatable.
 *
 * It adds a magic property `translations` which is an array of the following form:
 *
 * <language>: {
 *   property1: value1
 * }
 *
 * This property can be read from and written to; so it can be used inside e.g. a Fluid form for the following:
 * <f:form.textfield property="name" id="name" />
 * <f:form.textfield property="translations.de.name" id="name" />
 * <f:form.textfield property="translations.en_US.name" id="name" />
 *
 *
 * DEVELOPMENT HINT: In this trait, make sure that *ALL ANNOTATIONS* are FULLY-QUALIFIED, as Use-Statements are not properly
 *                   resolved when being in traits as far as I see
 */
trait TranslatableTrait {

	/**
	 * Doctrine's Entity Manager. Note that "ObjectManager" is the name of the related interface.
	 *
	 * @TYPO3\Flow\Annotations\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;

	/**
	 * @return array
	 */
	public function getTranslations() {
		/* @var $repository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
		$repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
		return $repository->findTranslations($this);
	}

	/**
	 * @param array $translations
	 */
	public function setTranslations(array $translations) {
		/* @var $repository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
		$repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

		foreach ($translations as $language => $properties) {
			foreach ($properties as $propertyName => $translatedValue) {
				$repository->translate($this, $propertyName, $language, $translatedValue);
			}
		}
	}
}