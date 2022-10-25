<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector\Tests\Functional\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="translatable_fixture_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 */
class TranslatableFixtureWithTranslationEntityTranslation extends AbstractTranslation
{
}
