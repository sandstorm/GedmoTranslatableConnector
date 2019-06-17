<?php
namespace Sandstorm\GedmoTranslatableConnector;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            */

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver;

/**
 * Helper class which just delegates the "setEntityManager()" method to the Flow Annotation Driver passed in the constructor.
 */
class MappingDriverChainWithFlowAnnotationDriverAsDefault extends MappingDriverChain {

	public function __construct(FlowAnnotationDriver $flowAnnotationDriver) {
		$this->setDefaultDriver($flowAnnotationDriver);
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $entityManager
	 * @return void
	 */
	public function setEntityManager(\Doctrine\Common\Persistence\ObjectManager $entityManager) {
		$this->getDefaultDriver()->setEntityManager($entityManager);
	}
} 