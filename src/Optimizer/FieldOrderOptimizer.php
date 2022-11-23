<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;

class FieldOrderOptimizer extends AbstractOptimizer implements OptimizerInterface
{
    /**
     * @param SchemaTemplateInterface $schemaTemplate
     * @return SchemaTemplateInterface
     * @throws \JsonException
     */
    public function optimize(SchemaTemplateInterface $schemaTemplate): SchemaTemplateInterface
    {
        $data = json_decode($schemaTemplate->getSchemaDefinition(), true, JSON_THROW_ON_ERROR);

        $data = $this->processSchema($data);

        return $schemaTemplate->withSchemaDefinition(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION)
        );
    }

    /**
     * @param array|mixed $data
     * @return array|mixed
     */
    private function processSchema($data)
    {
        if (true === $this->isRecord($data)) {
            $data = $this->reorderFields($data);
        }

        $data = $this->handleTypes($data);

        if (true === isset($data['fields'])) {
            foreach ($data['fields'] as $index => $field) {
                $data['fields'][$index] = $this->processSchema($field);
            }
        }

        return $data;
    }

    /**
     * @param array|mixed $data
     * @return array|mixed
     */
    private function handleTypes($data)
    {
        if (true === $this->typeIsRecord($data)) {
            $data['type'] = $this->processSchema($data['type']);
        } elseif (true === $this->typeIsTypeArray($data)) {
            foreach ($data['type'] as $index => $type) {
                $data['type'][$index] = $this->processSchema($type);
            }
        } elseif (true === $this->typeIsRecordArray($data)) {
            $data['items'] = $this->processSchema($data['items']);
        } elseif (true === $this->typeIsMultiTypeArray($data)) {
            foreach ($data['items'] as $index => $item) {
                $data['items'][$index] = $this->processSchema($item);
            }
        }

        return $data;
    }

    /**
     * @param array<mixed> $definition
     * @return array<mixed>
     */
    private function reorderFields(array $definition): array
    {
        $newDefinition = [];

        // Make sure, order of those fields is correct
        if (true === isset($definition['type'])) {
            $newDefinition['type'] = $definition['type'];
            unset($definition['type']);
        }

        if (true === isset($definition['name'])) {
            $newDefinition['name'] = $definition['name'];
            unset($definition['name']);
        }

        if (true === isset($definition['namespace'])) {
            $newDefinition['namespace'] = $definition['namespace'];
            unset($definition['namespace']);
        }

        if ([] !== $newDefinition) {
            $definition = array_merge($newDefinition, $definition);
        }

        return $definition;
    }
}
