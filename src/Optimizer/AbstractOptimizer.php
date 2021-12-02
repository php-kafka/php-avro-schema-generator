<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

abstract class AbstractOptimizer
{
    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function isRecord($data): bool
    {
        return true === isset($data['type']) && 'record' === $data['type'];
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsRecord($data): bool
    {
        if (true === isset($data['type']) && true === is_array($data['type'])) {
            if (true === isset($data['type']['type'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsTypeArray($data): bool
    {
        if (true === isset($data['type']) && true === is_array($data['type'])) {
            if (false === isset($data['type']['type'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsRecordArray($data): bool
    {
        if (true === isset($data['type']) && 'array' === $data['type']) {
            if (true === is_array($data['items'])) {
                if (true === isset($data['items']['type'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsMultiTypeArray($data): bool
    {
        if (true=== isset($data['type']) && 'array' === $data['type']) {
            if (true === is_array($data['items'])) {
                if (false === isset($data['items']['type'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsSingleypeArray($data): bool
    {
        if (true=== isset($data['type']) && 'array' === $data['type']) {
            if (true === is_string($data['items'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsString($data): bool
    {
        if(isset($data['type']) && true === is_string($data['type'])) {
            return true;
        }

        return false;
    }
}