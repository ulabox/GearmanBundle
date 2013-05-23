<?php

namespace Ulabox\Bundle\GearmanBundle\Loader;

use Metadata\MetadataFactory;

abstract class BaseLoader implements LoaderInterface
{
    /**
     * The file extension of resource files.
     *
     * @var string
     */
    protected $fileExtension = '.php';

    /**
     * The metadata factory
     *
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * The configured servers.
     *
     * @var array
     */
    protected $servers = array();

    /**
     * Constructor
     *
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(MetadataFactory $metadataFactory, $servers)
    {
        $this->metadataFactory = $metadataFactory;
        $this->servers = $servers;
    }

    /**
     * Find resources classes in a given directory and load them.
     *
     * @param string $dir Directory to find resource classes in.
     * @return array $resources Array of loaded resource object instances
     */
    public function loadFromDirectory($dir, $bundleName)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist', $dir));
        }

        $resources = array();
        $includedFiles = array();

        $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (($fileName = $file->getBasename($this->fileExtension)) == $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }
        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();
            $metadata = $this->metadataFactory->getMetadataForClass($className)->getOutsideClassMetadata();

            if (in_array($sourceFile, $includedFiles) && $this->implementInterface($className)) {
                if ($this->hasAnnotation($metadata)) {
                    $metadata->setBundleName($bundleName);

                    if ($metadata->isClient()) {
                        $resource = new $className($this->metadataFactory, $this->servers);
                    } else {
                        $resource = new $className;
                    }

                    $resources[] = $resource;
                }
            }
        }

        return $resources;
    }
}
