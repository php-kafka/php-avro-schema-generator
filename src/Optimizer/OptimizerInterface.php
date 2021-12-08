<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

interface OptimizerInterface
{
    /**
     * @param string $definition
     * @return string
     */
    public function optimize(string $definition): string;
}
