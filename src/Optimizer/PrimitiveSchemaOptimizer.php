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
    public function optimize(string $definition): string
    {
        $data = json_decode($definition, true, JSON_THROW_ON_ERROR);

        $data = $this->processSchema($data);

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array|mixed $data
     * @return array|mixed
     */
    private function processSchema($data)
    {
        if (true === $this->isPrimitive($data)) {
            $data = $data['type'];
        }

        return $data;
    }
}
