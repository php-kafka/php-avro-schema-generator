<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Merger;

use AvroSchemaParseException;
use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\Exception\SchemaMergerException;
use PhpKafka\PhpAvroSchemaGenerator\Optimizer\OptimizerInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;

final class SchemaMerger implements SchemaMergerInterface
{
    /**
     * @var string
     */
    private $outputDirectory;

    /**
     * @var SchemaRegistryInterface
     */
    private $schemaRegistry;

    /**
     * @var OptimizerInterface[]
     */
    private $optimizers = [];

    public function __construct(SchemaRegistryInterface $schemaRegistry, string $outputDirectory = '/tmp')
    {
        $this->schemaRegistry = $schemaRegistry;
        $this->outputDirectory = $outputDirectory;
    }

    /**
     * @return SchemaRegistryInterface
     */
    public function getSchemaRegistry(): SchemaRegistryInterface
    {
        return $this->schemaRegistry;
    }

    /**
     * @return string
     */
    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }

    /**
     * @param SchemaTemplateInterface $rootSchemaTemplate
     * @return SchemaTemplateInterface
     * @throws AvroSchemaParseException
     * @throws SchemaMergerException
     */
    public function getResolvedSchemaTemplate(SchemaTemplateInterface $rootSchemaTemplate): SchemaTemplateInterface
    {
        $rootDefinition = $rootSchemaTemplate->getSchemaDefinition();

        do {
            $exceptionThrown = false;

            try {
                \AvroSchema::parse($rootDefinition);
            } catch (AvroSchemaParseException $e) {
                if (false === strpos($e->getMessage(), ' is not a schema we know about.')) {
                    throw $e;
                }
                $exceptionThrown = true;
                $schemaId = $this->getSchemaIdFromExceptionMessage($e->getMessage());
                $embeddedTemplate = $this->schemaRegistry->getSchemaById($schemaId);
                if (null === $embeddedTemplate) {
                    throw new SchemaMergerException(
                        sprintf(SchemaMergerException::UNKNOWN_SCHEMA_TYPE_EXCEPTION_MESSAGE, $schemaId)
                    );
                }

                $rootDefinition =  $this->replaceSchemaIdWithDefinition(
                    $rootDefinition,
                    $schemaId,
                    $embeddedTemplate->getSchemaDefinition()
                );
            }
        } while (true === $exceptionThrown);

        return $rootSchemaTemplate->withSchemaDefinition($rootDefinition);
    }

    private function getSchemaIdFromExceptionMessage(string $exceptionMessage): string
    {
        return str_replace(' is not a schema we know about.', '', $exceptionMessage);
    }

    private function replaceSchemaIdWithDefinition(
        string $rootDefinition,
        string $schemaId,
        string $embeddedDefinition
    ): string {
        $idString = '"' . $schemaId . '"';
        $pos = (int) strpos($rootDefinition, $idString);

        return substr_replace($rootDefinition, $embeddedDefinition, $pos, strlen($idString));
    }

    /**
     * @param bool $prefixWithNamespace
     * @param bool $useTemplateName
     * @return integer
     * @throws AvroSchemaParseException
     * @throws SchemaMergerException
     */
    public function merge(
        bool $prefixWithNamespace = false,
        bool $useTemplateName = false
    ): int {
        $mergedFiles = 0;
        $registry = $this->getSchemaRegistry();

        /** @var SchemaTemplateInterface $rootSchemaTemplate */
        foreach ($registry->getRootSchemas() as $rootSchemaTemplate) {
            try {
                $resolvedTemplate = $this->getResolvedSchemaTemplate($rootSchemaTemplate);
                foreach ($this->optimizers as $optimizer) {
                    $resolvedTemplate = $resolvedTemplate->withSchemaDefinition(
                        $optimizer->optimize($resolvedTemplate->getSchemaDefinition())
                    );
                }
            } catch (SchemaMergerException $e) {
                throw $e;
            }
            $this->exportSchema($resolvedTemplate, $prefixWithNamespace, $useTemplateName);

            ++$mergedFiles;
        }

        return $mergedFiles;
    }

    /**
     * @param SchemaTemplateInterface $rootSchemaTemplate
     * @param boolean                 $prefixWithNamespace
     * @param boolean                 $useTemplateName
     * @return void
     */
    public function exportSchema(
        SchemaTemplateInterface $rootSchemaTemplate,
        bool $prefixWithNamespace = false,
        bool $useTemplateName = false
    ): void {
        $rootSchemaDefinition = $this->transformExportSchemaDefinition(
            json_decode($rootSchemaTemplate->getSchemaDefinition(), true, JSON_THROW_ON_ERROR)
        );

        $prefix = '';

        if (true === $prefixWithNamespace && false === $rootSchemaTemplate->isPrimitive()) {
            $prefix = $rootSchemaDefinition['namespace'] . '.';
        }

        $schemaFilename = $rootSchemaTemplate->getFilename();

        if (false === $useTemplateName && false === $rootSchemaTemplate->isPrimitive()) {
            $schemaFilename = $prefix . $rootSchemaDefinition['name'] . '.' . Avro::FILE_EXTENSION;
        }

        if (false === file_exists($this->getOutputDirectory())) {
            mkdir($this->getOutputDirectory());
        }

        /** @var string $fileContents */
        $fileContents = json_encode($rootSchemaDefinition);

        file_put_contents($this->getOutputDirectory() . '/' . $schemaFilename, $fileContents);
    }

    /**
     * @param  array<string,mixed> $schemaDefinition
     * @return array<string,mixed>
     */
    public function transformExportSchemaDefinition(array $schemaDefinition): array
    {
        unset($schemaDefinition['schema_level']);

        return $schemaDefinition;
    }

    /**
     * @param OptimizerInterface $optimizer
     */
    public function addOptimizer(OptimizerInterface $optimizer): void
    {
        $this->optimizers[] = $optimizer;
    }
}
