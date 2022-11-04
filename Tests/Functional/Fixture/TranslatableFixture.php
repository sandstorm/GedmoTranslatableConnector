<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector\Tests\Functional\Fixture;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Neos\Flow\Annotations as Flow;
use Sandstorm\GedmoTranslatableConnector\Translatable;
use Sandstorm\GedmoTranslatableConnector\TranslatableTrait;

/**
 * @Flow\Entity
 */
class TranslatableFixture implements Translatable
{
    use TranslatableTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(length=20)
     */
    protected string $id = '';

    /**
     * @var string
     * @Gedmo\Translatable
     */
    protected string $name = '';

    /**
     * @var string
     * @Gedmo\Translatable
     */
    protected string $abstract = '';

    public function getName(): string
    {
        return $this->name;
    }
    public function getAbstract(): string
    {
        return $this->abstract;
    }

    public function __wakeup(): void
    {
    }
}
