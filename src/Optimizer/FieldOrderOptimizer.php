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
        if (true === isset($data['type'])) {

        }
    }

    private function reorderFields(string $definition): string
    {
        $newDefinition = [];
        $data = json_decode($definition, true, JSON_THROW_ON_ERROR);

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
            $newDefinition = array_merge($newDefinition, $data);
            $definition = (string) json_encode($newDefinition);
        }

        return $definition;
    }
}