<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector\Tests\Unit\Fixture;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

interface TranslationsFixture
{
    /** @var string[][] */
    public const TRANSLATIONS = [
        'de' => [
            'name' => 'Name auf Deutsch',
            'abstract' => 'Der Abstract',
        ],
        'en' => [
            'name' => 'Name in english',
            'abstract' => 'The abstract',
        ],
    ];
}
