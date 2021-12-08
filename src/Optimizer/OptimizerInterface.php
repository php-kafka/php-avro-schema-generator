<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;

interface OptimizerInterface
{
    /**
     * @param SchemaTemplateInterface $schemaTemplate
     * @return SchemaTemplateInterface
     */
    public function optimize(SchemaTemplateInterface $schemaTemplate): SchemaTemplateInterface;
}
