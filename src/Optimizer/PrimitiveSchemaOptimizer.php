<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

class PrimitiveSchemaOptimizer extends AbstractOptimizer implements OptimizerInterface
{
    /**
     * @param string $definition
     * @return string
     * @throws \JsonException
     */
    public function optimize(string $definition, bool $isPrimitive = false): string
    {
        if (false === $isPrimitive) {
            return $definition;
        }

        $data = json_decode($definition, true, JSON_THROW_ON_ERROR);

        $data = $this->processSchema($data);

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function processSchema($data)
    {
        if (true === isset($data['type'])) {
            $data = $data['type'];
        }

        return $data;
    }
}
