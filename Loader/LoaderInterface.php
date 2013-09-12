<?php

namespace Ulabox\Bundle\GearmanBundle\Loader;

use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;

/**
 * Interface for class loader.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Find resources classes in a given directory and load them.
     *
     * @param string $dir        Directory to find resource classes in.
     * @param string $bundleName The bundle name
     *
     * @return array $resources Array of loaded resource object instances
     */
    public function loadFromDirectory($dir, $bundleName);

    /**
     * Check if a given resource should be considered a valid resource class.
     *
     * @param string $className The class name
     *
     * @return boolean
     */
    public function implementInterface($className);

    /**
     * Check if the resource has a valid annotation
     *
     * @param ClassMetadata $metadata
     *
     * @return boolean
     */
    public function hasAnnotation(ClassMetadata $metadata);
}
