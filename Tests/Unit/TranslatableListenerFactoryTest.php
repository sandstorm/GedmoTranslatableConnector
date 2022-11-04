<?php

declare(strict_types=1);

namespace Sandstorm\GedmoTranslatableConnector\Tests\Unit;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Gedmo\Translatable\TranslatableListener;
use Neos\Flow\Tests\UnitTestCase;
use Sandstorm\GedmoTranslatableConnector\TranslatableListenerFactory;

class TranslatableListenerFactoryTest extends UnitTestCase
{
    /** @var TranslatableListenerFactory  */
    protected $obj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new TranslatableListenerFactory();

        $this->inject($this->obj, 'locale', 'de');
        $this->inject($this->obj, 'defaultLocale', 'mul_ZZ');
        $this->inject($this->obj, 'translationFallback', true);
        $this->inject($this->obj, 'persistDefaultLocaleTranslation', true);
    }

    /**
     * @test
     */
    public function createReturnsConfiguredListener(): void
    {
        $listener = $this->obj->create();

        self::assertInstanceOf(TranslatableListener::class, $listener);
        self::assertSame('de', $listener->getListenerLocale());
        self::assertSame('mul_ZZ', $listener->getDefaultLocale());
        self::assertSame(true, $listener->getTranslationFallback());
        self::assertSame(true, $listener->getPersistDefaultLocaleTranslation());
    }
}
