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

use Sandstorm\GedmoTranslatableConnector\Translatable;
use Sandstorm\GedmoTranslatableConnector\TranslatableTrait;

class TranslatableFixture implements Translatable
{
    use TranslatableTrait;
}
