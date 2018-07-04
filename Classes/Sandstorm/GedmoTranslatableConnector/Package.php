<?php
namespace Sandstorm\GedmoTranslatableConnector;

/*                                                                            *
 * This script belongs to the Package "Sandstorm.GedmoTranslatableConnector". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU Lesser General Public License, either version 3       *
 * of the License, or (at your option) any later version.                     *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Neos\Flow\Reflection\ClassSchema;
use Neos\Utility\ObjectAccess;

/**
 * Class Package; reconfiguring TYPO3 Flow for use with Gedmo Translatable
 *
 * @package Sandstorm\GedmoTranslatableConnector
 */
class Package extends \Neos\Flow\Package\Package {
	public function boot(\Neos\Flow\Core\Bootstrap $bootstrap) {

		// 1. Make Gedmo\Translatable\Entity\Translation known to Doctrine, so that it can participate in Database Schema Generation
		//
		// Internally, we use a MappingDriverChain for that, which delegates almost all of its behavior to the already-existing
		// FlowAnnotationDriver. We additionally add the (default doctrine) Annotation Driver for the Gedmo namespace.
		//
		// Note: We replace FlowAnnotationDriver *on a very low level* with the *MappingDriverChain* object; because this class
		// is only used inside EntityManagerFactory -- so we know quite exactly what methods are called on that object.
		$bootstrap->getSignalSlotDispatcher()->connect('Neos\Flow\Core\Booting\Sequence', 'beforeInvokeStep', function($step) use($bootstrap) {
			if ($step->getIdentifier() === 'neos.flow:resources') {
				$flowAnnotationDriver = $bootstrap->getObjectManager()->get('Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver');

				$driverChain = new MappingDriverChainWithFlowAnnotationDriverAsDefault($flowAnnotationDriver);
				$driverChain->addDriver(new AnnotationDriver(ObjectAccess::getProperty($flowAnnotationDriver, 'reader', TRUE), FLOW_PATH_PACKAGES . 'Libraries/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity'), 'Gedmo');
				$bootstrap->getObjectManager()->setInstance('Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver', $driverChain);
			}
		});

		// 2. Work around a bug in Neos\Flow\Persistence\Doctrine\PersistenceManager::onFlush which expects that all objects in the
		//    Doctrine subsystem are entities known to Flow.
		//
		// The line $this->reflectionService->getClassSchema($entity)->getModelType() triggers a fatal error, for get_class($entity) == 'Gedmo\Translatable\Entity\Translation'
		// because this class is known only to Doctrine (see 1. above), but not to the Flow reflection service.
		//
		// As a workaround, we just add an empty placeholder class schema to the Class Schemata cache, right before the class schema is saved
		// inside the Neos\Flow\Core\Bootstrap::bootstrapShuttingDown signal (which is fired directly after "finishedCompiletimeRun").
		$bootstrap->getSignalSlotDispatcher()->connect('Neos\Flow\Core\Bootstrap', 'finishedCompiletimeRun', function() use($bootstrap) {
			$classSchemataCache = $bootstrap->getObjectManager()->get('Neos\Flow\Cache\CacheManager')->getCache('Flow_Reflection_RuntimeClassSchemata');
			if (!$classSchemataCache->has('Gedmo_Translatable_Entity_Translation')) {
				$classSchemataCache->set('Gedmo_Translatable_Entity_Translation', new ClassSchema('Gedmo\Translatable\Entity\Translation'));
			}
		});
	}
} 