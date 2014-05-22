<?php

namespace Ulabox\Bundle\GearmanBundle\Loader;

use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;

/**
 * Worker class loader.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class WorkerLoader extends BaseLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function implementInterface($className)
    {
        return is_subclass_of($className, 'Ulabox\Bundle\GearmanBundle\Model\WorkerInterface');
    }

    /**
     * {@inheritdoc}
     */
    public function hasAnnotation(ClassMetadata $metadata)
    {
        return $metadata->isWorker();
    }
}
