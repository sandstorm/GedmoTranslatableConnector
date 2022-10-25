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

use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver;
use Neos\Flow\Tests\UnitTestCase;
use Sandstorm\GedmoTranslatableConnector\MappingDriverChainWithFlowAnnotationDriverAsDefault;

class MappingDriverChainWithFlowAnnotationDriverAsDefaultTest extends UnitTestCase
{
    /**
     * @test
     */
    public function mappingDriverChainSetsEntityManagerToFlowAnnotationDriver(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $driver = $this->createMock(FlowAnnotationDriver::class);
        $driver->expects(self::once())->method('setEntityManager')->with($entityManager);

        $obj = new MappingDriverChainWithFlowAnnotationDriverAsDefault($driver);

        $obj->setEntityManager($entityManager);
    }
}
