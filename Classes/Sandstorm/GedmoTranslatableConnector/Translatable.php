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

interface Translatable extends \Gedmo\Translatable\Translatable
{
    /**
     * @return  array
     * @phpstan-return  array<string, array<string, string>>
     */
    public function getTranslations(): array;

    public function setTranslatableLocale(string $locale): void;
}
