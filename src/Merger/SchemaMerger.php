<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Merger;

use AvroSchemaParseException;
use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\Exception\SchemaMergerException;
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
     * @param bool $optimizeSubSchemaNamespaces
     * @return SchemaTemplateInterface
     * @throws AvroSchemaParseException
     * @throws SchemaMergerException
     */
    public function getResolvedSchemaTemplate(
        SchemaTemplateInterface $schemaTemplate,
        bool $optimizeSubSchemaNamespaces = false
    ): SchemaTemplateInterface {
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
                    $embeddedTemplate->getSchemaDefinition(),
                    $optimizeSubSchemaNamespaces
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
        string $embeddedDefinition,
        bool $optimizeSubSchemaNamespaces = false
    ): string {
        $idString = '"' . $schemaId . '"';

        if (true === $optimizeSubSchemaNamespaces) {
            $embeddedDefinition = $this->excludeNamespacesForEmbeddedSchema($definition, $embeddedDefinition);
        }

        $pos = strpos($definition, $idString);

        return substr_replace($definition, $embeddedDefinition, $pos, strlen($idString));
    }

    /**
     * @param bool $prefixWithNamespace
     * @param bool $useTemplateName
     * @param bool $optimizeSubSchemaNamespaces
     * @return integer
     * @throws AvroSchemaParseException
     * @throws SchemaMergerException
     */
    public function merge(
        bool $prefixWithNamespace = false,
        bool $useTemplateName = false,
        bool $optimizeSubSchemaNamespaces = false
    ): int {
        $mergedFiles = 0;
        $registry = $this->getSchemaRegistry();

        /** @var SchemaTemplateInterface $schemaTemplate */
        foreach ($registry->getRootSchemas() as $schemaTemplate) {
            try {
                $resolvedTemplate = $this->getResolvedSchemaTemplate($schemaTemplate, $optimizeSubSchemaNamespaces);
            } catch (SchemaMergerException $e) {
                throw $e;
            }
            $this->exportSchema(
                $resolvedTemplate,
                $prefixWithNamespace,
                $useTemplateName,
                $optimizeSubSchemaNamespaces
            );
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
        bool $useTemplateName = false,
        bool $optimizeSubSchemaNamespaces = false
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

        if (true === $optimizeSubSchemaNamespaces) {
            $embeddedSchemaNamespace = $rootSchemaDefinition['namespace'] . '.';
            $fileContents = str_replace($embeddedSchemaNamespace, '', $fileContents);
        }

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
     * @param string $definition
     * @param string $embeddedDefinition
     * @return string
     */
    private function excludeNamespacesForEmbeddedSchema(string $definition, string $embeddedDefinition): string
    {
        $decodedRootDefinition = json_decode($definition, true, JSON_THROW_ON_ERROR);
        $decodedEmbeddedDefinition = json_decode($embeddedDefinition, true, JSON_THROW_ON_ERROR);

        if (
            isset($decodedRootDefinition['namespace']) && isset($decodedEmbeddedDefinition['namespace']) &&
            $decodedRootDefinition['namespace'] === $decodedEmbeddedDefinition['namespace']
        ) {
            unset($decodedEmbeddedDefinition['namespace']);
            /** @var string $embeddedDefinition */
            $embeddedDefinition = json_encode($decodedEmbeddedDefinition);
        }

        return $embeddedDefinition;
    }
}
