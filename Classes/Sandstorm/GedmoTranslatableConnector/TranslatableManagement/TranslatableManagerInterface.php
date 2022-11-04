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

use Sandstorm\GedmoTranslatableConnector\Translatable;

interface TranslatableManagerInterface
{
    /**
     * @param Translatable $entity
     * @return array<string, array<string, string>>
     */
    public function getTranslations(Translatable $entity): array;

    public function translate(Translatable $entity): void;

    public function reloadInLocale(Translatable $entity, string $locale): void;

    public function flush(): void;
}
