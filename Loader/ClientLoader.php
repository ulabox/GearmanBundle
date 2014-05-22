<?php

namespace Ulabox\Bundle\GearmanBundle\Loader;

use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;

/**
 * Client class loader.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class ClientLoader extends BaseLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function implementInterface($className)
    {
        return is_subclass_of($className, 'Ulabox\Bundle\GearmanBundle\Model\ClientInterface');
    }


    /**
     * {@inheritdoc}
     */
    public function hasAnnotation(ClassMetadata $metadata)
    {
        return $metadata->isClient();
    }
}
