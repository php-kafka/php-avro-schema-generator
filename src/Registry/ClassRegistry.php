<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Registry;

use FilesystemIterator;
use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverterInterface;
use PhpKafka\PhpAvroSchemaGenerator\Exception\ClassRegistryException;
use PhpKafka\PhpAvroSchemaGenerator\Parser\TokenParser;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ClassRegistry implements ClassRegistryInterface
{
    /**
     * @var array<string,int>
     */
    protected $classDirectories = [];

    /**
     * @var PhpClass[]
     */
    protected $classes = [];

    private PhpClassConverterInterface $classConverter;

    public function __construct(PhpClassConverterInterface $classConverter)
    {
        $this->classConverter = $classConverter;
    }

    /**
     * @param string $classDirectory
     * @return ClassRegistryInterface
     */
    public function addClassDirectory(string $classDirectory): ClassRegistryInterface
    {
        $this->classDirectories[$classDirectory] = 1;

        return $this;
    }

    /**
     * @return array<string,int>
     */
    public function getClassDirectories(): array
    {
        return $this->classDirectories;
    }

    /**
     * @return ClassRegistryInterface
     * @throws ClassRegistryException
     */
    public function load(): ClassRegistryInterface
    {
        foreach ($this->getClassDirectories() as $directory => $loneliestNumber) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $directory,
                    FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ('php' === $file->getExtension()) {
                    $this->registerClassFile($file);
                }
            }
        }

        return $this;
    }

    /**
     * @return PhpClass[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param SplFileInfo $fileInfo
     * @throws ClassRegistryException
     * @return void
     */
    private function registerClassFile(SplFileInfo $fileInfo): void
    {
        if (false === $fileName = $fileInfo->getRealPath()) {
            throw new ClassRegistryException(ClassRegistryException::FILE_PATH_EXCEPTION_MESSAGE);
        }

        if (false === $fileContent = @file_get_contents($fileName)) {
            throw new ClassRegistryException(
                sprintf(
                    ClassRegistryException::FILE_NOT_READABLE_EXCEPTION_MESSAGE,
                    $fileName
                )
            );
        }
        $convertedClass = $this->classConverter->convert($fileContent);

        if (null !== $convertedClass) {
            $this->classes[] = $convertedClass;
        }
    }
}
