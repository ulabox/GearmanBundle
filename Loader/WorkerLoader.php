<?php

namespace Ulabox\Bundle\GearmanBundle\Loader;

use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;

class WorkerLoader extends BaseLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function implementInterface($className)
    {
        $rc = new \ReflectionClass($className);
        if ($rc->isAbstract()) return true;

        $interfaces = $rc->getInterfaceNames();
        return in_array('Ulabox\Bundle\GearmanBundle\Model\WorkerInterface', $interfaces) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAnnotation(ClassMetadata $metadata)
    {
        return $metadata->isWorker();
    }
}
