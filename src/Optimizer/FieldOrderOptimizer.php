<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

class FieldOrderOptimizer implements OptimizerInterface
{

    /**
     * @param string $definition
     * @return string
     * @throws \JsonException
     */
    public function optimize(string $definition): string
    {
        $data = json_decode($definition, true, JSON_THROW_ON_ERROR);

        $data = $this->processSchema($data);

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    private function processSchema(array $data): array
    {
        if (true === isset($data['type']) && 'record' === $data['type']) {
            $data = $this->reorderFields($data);
        }

        if (true === isset($data['type']) && true === is_array($data['type'])) {
            if (true === isset($data['type'])) {
                $data['type'] = $this->processSchema($data['type']);
            }
        }

        if (true === isset($data['type']) && 'array' === $data['type']) {
            if (true === is_array($data['items'])) {
                if(true === isset($data['items']['type'])) {
                    $data['items'] = $this->processSchema($data['items']);
                } else {
                    foreach($data['items'] as $index => $item) {
                        $data['items'][$index] = $this->processSchema($item);
                    }
                }
            }
        }

        if (true === isset($data['fields'])) {
            foreach($data['fields'] as $index => $field) {
                $data['fields'][$index] = $this->processSchema($field);
            }
        }

        return $data;
    }

    private function reorderFields(array $definition): array
    {
        $newDefinition = [];

        // Make sure, order of those fields is correct
        if (true === isset($data['type'])) {
            $newDefinition['type'] = $data['type'];
            unset($data['type']);
        }

        if (true === isset($data['name'])) {
            $newDefinition['name'] = $data['name'];
            unset($data['name']);
        }

        if (true === isset($data['namespace'])) {
            $newDefinition['namespace'] = $data['namespace'];
            unset($data['namespace']);
        }

        if ([] !== $newDefinition) {
            $definition = array_merge($newDefinition, $data);
        }

        return $definition;
    }
}