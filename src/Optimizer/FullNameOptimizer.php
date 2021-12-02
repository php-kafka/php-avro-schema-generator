<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

class FullNameOptimizer implements OptimizerInterface
{

    public function optimize(string $definition): string
    {


        return $definition;
    }
}