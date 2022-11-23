<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;

class PrimitiveSchemaOptimizer extends AbstractOptimizer implements OptimizerInterface
{
    /**
     * @param SchemaTemplateInterface $schemaTemplate
     * @return SchemaTemplateInterface
     * @throws \JsonException
     */
    public function optimize(SchemaTemplateInterface $schemaTemplate): SchemaTemplateInterface
    {
        if (false === $schemaTemplate->isPrimitive()) {
            return $schemaTemplate;
        }

        $data = json_decode($schemaTemplate->getSchemaDefinition(), true, JSON_THROW_ON_ERROR);

        $data = $this->processSchema($data);

        return $schemaTemplate->withSchemaDefinition(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION)
        );
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
