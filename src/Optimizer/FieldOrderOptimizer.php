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

    private function processSchema($data)
    {
        if (true === isset($data['type']) && 'record' === $data['type']) {
            $data = $this->reorderFields($data);
        }

        if (true === isset($data['type']) && true === is_array($data['type'])) {
            if (true === isset($data['type']['type'])) {
                $data['type'] = $this->processSchema($data['type']);
            } else {
                foreach($data['type'] as $index => $type) {
                    $data['type'][$index] = $this->processSchema($type);
                }
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