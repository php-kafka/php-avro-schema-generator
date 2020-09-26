<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Merger;

use AvroSchemaParseException;
use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\Exception\SchemaMergerException;
use PhpKafka\PhpAvroSchemaGenerator\Exception\SchemaGenerationException;
use PhpKafka\PhpAvroSchemaGenerator\Exception\UnknownSchemaTypeException;
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
     * @param SchemaTemplateInterface $schemaTemplate
     * @return SchemaTemplateInterface
     * @throws AvroSchemaParseException
     * @throws SchemaMergerException
     */
    public function getResolvedSchemaTemplate(SchemaTemplateInterface $schemaTemplate): SchemaTemplateInterface
    {
        $definition = $schemaTemplate->getSchemaDefinition();

        do {
            $exceptionThrown = false;

            try {
                \AvroSchema::parse($definition);
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

                $definition =  $this->replaceSchemaIdWithDefinition(
                    $definition,
                    $schemaId,
                    $embeddedTemplate->getSchemaDefinition()
                );
            }
        } while (true === $exceptionThrown);

        return $schemaTemplate->withSchemaDefinition($definition);
    }

    private function getSchemaIdFromExceptionMessage(string $exceptionMessage): string
    {
        return str_replace(' is not a schema we know about.', '', $exceptionMessage);
    }

    private function replaceSchemaIdWithDefinition(
        string $definition,
        string $schemaId,
        string $embeddedDefinition
    ): string {
        $idString = '"' . $schemaId . '"';

        $pos = strpos($definition, $idString);

        return substr_replace($definition, $embeddedDefinition, $pos, strlen($idString));
    }


    /**
     * @param boolean $prefixWithNamespace
     * @param boolean $useTemplateName
     * @return integer
     * @throws AvroSchemaParseException
     * @throws SchemaMergerException
     */
    public function merge(bool $prefixWithNamespace = false, bool $useTemplateName = false): int
    {
        $mergedFiles = 0;
        $registry = $this->getSchemaRegistry();

        /** @var SchemaTemplateInterface $schemaTemplate */
        foreach ($registry->getRootSchemas() as $schemaTemplate) {
            try {
                $resolvedTemplate = $this->getResolvedSchemaTemplate($schemaTemplate);
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
            json_decode($rootSchemaTemplate->getSchemaDefinition(), true)
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
}
