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

use Gedmo\Translatable\TranslatableListener;
use Neos\Flow\Annotations as Flow;

/**
 * Builder for TranslatableListener; injecting locale and default locale from settings
 *
 * @Flow\Scope("singleton")
 */
class TranslatableListenerFactory
{
    /**
     * @Flow\InjectConfiguration("defaultLocale")
     * @var string
     */
    protected $defaultLocale;

    /**
     * @Flow\InjectConfiguration("locale")
     * @var string
     */
    protected $locale;

    /**
     * @Flow\InjectConfiguration(path="translationFallback")
     * @var boolean
     */
    protected $translationFallback;

    /**
     * @Flow\InjectConfiguration(path="persistDefaultLocaleTranslation")
     * @var boolean
     */
    protected $persistDefaultLocaleTranslation;

    public function create(): TranslatableListener
    {
        $listener = new TranslatableListener();
        $listener->setDefaultLocale($this->defaultLocale);
        $listener->setTranslatableLocale($this->locale);
        $listener->setTranslationFallback($this->translationFallback);
        $listener->setPersistDefaultLocaleTranslation($this->persistDefaultLocaleTranslation);

        return $listener;
    }
}
