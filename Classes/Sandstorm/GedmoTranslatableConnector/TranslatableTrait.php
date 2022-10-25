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

/**
 * This trait can be mixed into Models which have some properties being marked as Gedmo\Translatable.
 *
 * It adds a magic property `translations` which is an array of the following form (provided that 'name' and 'abstract'
 * are marked as translatable; and are translated in german and english):
 *
 * 'de' => [
 *   'name' => 'Name auf Deutsch',
 *   'abstract' => 'Der Abstract',
 * ],
 * 'en' => [
 *   'name' => 'Name in english',
 *   'abstract' => 'The abstract',
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
 * DEVELOPMENT HINT: In traits, make sure that *ALL ANNOTATIONS* are FULLY-QUALIFIED. Use-Statements are not properly
 * resolved when being in traits.
 */
trait TranslatableTrait
{
    /**
     * @\Neos\Flow\Annotations\Inject
     * @var \Sandstorm\GedmoTranslatableConnector\TranslatableManagement\TranslatableManagerInterface
     */
    protected $translatableManager;

    /**
     * Locale of this entity to override the translation listener`s locale.
     *
     * @\Neos\Flow\Annotations\Transient
     * @\Gedmo\Mapping\Annotation\Locale
     * @var string|null
     */
    protected ?string $locale = null;

    /**
     * The translations of this entity if loaded or set for update.
     *
     * @\Neos\Flow\Annotations\Transient
     * @var array
     * @phpstan-var array<string, array<string, string>>
     */
    protected array $translations = [];

    /**
     * Flag to indicate if the translations are loaded.
     *
     * @\Neos\Flow\Annotations\Transient
     * @var bool
     */
    private bool $translationsLoaded = false;

    /**
     * Fetch the translations properties or simply return them if previously loaded.
     *
     * @return array
     * @phpstan-return array<string, array<string, string>>
     */
    public function getTranslations(): array
    {
        if ($this->translationsLoaded) {
            return $this->translations;
        }

        $translations = $this->translatableManager->getTranslations($this);

        if (property_exists($this, 'translationAssociationMapping')) {
            foreach ($translations as $language => $_) {
                foreach ($this->translationAssociationMapping as $internalKey => $key) {
                    $possibleMethodName = ucfirst($key) . 'onLoad';
                    if (method_exists($this, $possibleMethodName)) {
                        if (isset($translations[$language][$internalKey])) {
                            $translations[$language][$key] = $this->$possibleMethodName(
                                $translations[$language][$internalKey]
                            );
                        }
                        unset($translations[$language][$internalKey]);
                    }
                }
            }
        }
        $this->translations = $translations;
        $this->translationsLoaded = true;

        return $this->translations;
    }

    /**
     * @deprecated use Utility::translationsByProperties()
     */
    public function getTranslationsByProperties(): array
    {
        return Utility::translationsByProperties($this);
    }

    /**
     * @deprecated use Utility::propertyTranslations()
     */
    public function getTranslationsOfProperty($propertyName): array
    {
        return Utility::propertyTranslations($this, $propertyName);
    }

    /**
     * @deprecated use Utility::propertyTranslationInLocale()
     */
    public function getPropertyInLocale(string $propertyName, string $locale): ?string
    {
        return Utility::propertyTranslationInLocale($this, $propertyName, $locale);
    }

    public function reloadInLocale(string $locale): void
    {
        $this->translatableManager->reloadInLocale($this, $locale);
    }

    /**
     * @deprecated use setTranslatableLocale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Set the translations property by recursively replacing exising entries.
     *
     * With enabled "instantTranslation" setting (default), this calls the translation repository's translate-method
     * which already persist this entity itself!
     *
     * @param array<string, array<string, string>> $translations
     */
    public function setTranslations(array $translations): void
    {
        if (property_exists($this, 'translationAssociationMapping')) {
            foreach ($translations as $language => $_) {
                foreach ($this->translationAssociationMapping as $internalKey => $key) {
                    $possibleMethodName = ucfirst($key) . 'onSave';
                    if (method_exists($this, $possibleMethodName)) {
                        if (isset($translations[$language][$key])) {
                            $translations[$language][$internalKey] = $this->$possibleMethodName(
                                $translations[$language][$key]
                            );
                        }
                        unset($translations[$language][$key]);
                    }
                }
            }
        }

        $this->translationsLoaded = true;
        $this->translations = array_replace_recursive($this->translations, $translations);
        $this->translatableManager->translate($this);
    }
}
