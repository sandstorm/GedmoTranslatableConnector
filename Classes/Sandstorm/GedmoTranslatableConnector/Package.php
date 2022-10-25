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

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Gedmo\Translatable\Entity\Translation;
use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver;
use Neos\Flow\Reflection\ClassSchema;
use Neos\Utility\ObjectAccess;

/**
 * Class Package; reconfiguring Neos Flow for use with Gedmo Translatable
 *
 * @package Sandstorm\GedmoTranslatableConnector
 */
class Package extends \Neos\Flow\Package\Package
{
    public function boot(Bootstrap $bootstrap): void
    {

        // 1. Make Gedmo\Translatable\Entity\Translation known to Doctrine, so that it can participate in Database
        // Schema Generation. Internally, we use a MappingDriverChain for that, which delegates almost all of its
        // behavior to the already-existing FlowAnnotationDriver.
        // We additionally add the (default doctrine) Annotation Driver for the Gedmo namespace.
        //
        // Note: We replace FlowAnnotationDriver *on a very low level* with the *MappingDriverChain* object;
        // because this class is only used inside EntityManagerFactory -- so we know quite exactly what methods are
        // called on that object.
        $bootstrap->getSignalSlotDispatcher()->connect(
            Sequence::class,
            'beforeInvokeStep',
            function ($step) use ($bootstrap) {
                if ($step->getIdentifier() === 'neos.flow:resources') {
                    /** @var FlowAnnotationDriver $flowAnnotationDriver */
                    $flowAnnotationDriver = $bootstrap->getObjectManager()->get(FlowAnnotationDriver::class);
                    $driverChain = new MappingDriverChainWithFlowAnnotationDriverAsDefault($flowAnnotationDriver);
                    /** @var Reader $reader */
                    $reader = ObjectAccess::getProperty($flowAnnotationDriver, 'reader', true);
                    $paths = FLOW_PATH_PACKAGES . 'Libraries/gedmo/doctrine-extensions/src/Translatable/Entity';
                    $driverChain->addDriver(new AnnotationDriver($reader, $paths), 'Gedmo');
                    $bootstrap->getObjectManager()->setInstance(FlowAnnotationDriver::class, $driverChain);
                }
            }
        );

        // 2. Work around a bug in Neos\Flow\Persistence\Doctrine\PersistenceManager::onFlush which expects that all
        // objects in the Doctrine subsystem are entities known to Flow.
        //
        // The line $this->reflectionService->getClassSchema($entity)->getModelType() triggers a fatal error, for
        // get_class($entity) == 'Gedmo\Translatable\Entity\Translation'
        // because this class is known only to Doctrine (see 1. above), but not to the Flow reflection service.
        //
        // As a workaround, we just add an empty placeholder class schema to the Class Schemata cache, right before the
        // class schema is saved inside the Neos\Flow\Core\Bootstrap::bootstrapShuttingDown signal (which is fired
        // directly after "finishedCompiletimeRun").
        $bootstrap->getSignalSlotDispatcher()->connect(
            Bootstrap::class,
            'finishedCompiletimeRun',
            function () use ($bootstrap) {
                /** @var CacheManager $cacheManager */
                $cacheManager = $bootstrap->getObjectManager()->get(CacheManager::class);
                $classSchemataCache = $cacheManager->getCache('Flow_Reflection_RuntimeClassSchemata');
                $cacheIdentifier = 'Gedmo_Translatable_Entity_Translation';
                if (!$classSchemataCache->has($cacheIdentifier)) {
                    $classSchemataCache->set($cacheIdentifier, new ClassSchema(Translation::class));
                }
            }
        );
    }
}
