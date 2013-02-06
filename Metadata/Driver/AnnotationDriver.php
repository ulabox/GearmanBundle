<?php

namespace Ulabox\Bundle\GearmanBundle\Metadata\Driver;

use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;
use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;
use Metadata\MethodMetadata;

class AnnotationDriver implements DriverInterface
{
    /**
     * Annotation reader
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Constructor
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($class->getName());

        // worker annotation
        $classAnnotation = $this->reader->getClassAnnotation(
            $class,
            'Ulabox\\Bundle\\GearmanBundle\\Annotation\\Worker'
        );

        if ($classAnnotation !== null) { // is a worker?
            // register servers and mark as worker
            $classMetadata->setServers($classAnnotation->getServers());
            $classMetadata->setWorker(true);
        }

        // client annotation
        $classAnnotation = $this->reader->getClassAnnotation(
            $class,
            'Ulabox\\Bundle\\GearmanBundle\\Annotation\\Client'
        );

        if ($classAnnotation !== null) { // is a client?
            // register servers, tasks and mark as client
            $classMetadata->setServers($classAnnotation->getServers());
            $classMetadata->setTasks($classAnnotation->getTasks());
            $classMetadata->setClient(true);

            // register worker name
            if ($classAnnotation->getWorkerName() === null) { // no has a worker name?
                // suggest a name
                $suggestName = str_replace(
                    'Client',
                    'Worker',
                    substr($classMetadata->name, strrpos($classMetadata->name, '\\') + 1)
                );
                $classMetadata->setWorkerName($suggestName);
            } else {
                $classMetadata->setWorkerName($classAnnotation->getWorkerName());
            }
        }

        // find jobs
        foreach ($class->getMethods() as $reflectionMethod) {
            $methodMetadata = new MethodMetadata($class->getName(), $reflectionMethod->getName());

            $methodAnnotation = $this->reader->getMethodAnnotation(
                $reflectionMethod,
                'Ulabox\\Bundle\\GearmanBundle\\Annotation\\Job'
            );

            if ($methodAnnotation !== null) { // is a job?
                if ($name = $methodAnnotation->getName()) // has a job name?
                    $methodMetadata->name = $name; // then replace default name

                // add this method as a job
                $classMetadata->addJob($methodMetadata);
            }

            // register all methods
            $classMetadata->addMethod($methodMetadata);
        }

        return $classMetadata;
    }
}
