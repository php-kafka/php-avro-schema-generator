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
        return true === $this->typeIsArray($data) && true === isset($data['type']['type']);
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsTypeArray($data): bool
    {
        return true === $this->typeIsArray($data) && false === isset($data['type']['type']);
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    private function typeIsArray($data): bool
    {
        return true === isset($data['type']) && true === is_array($data['type']);
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsRecordArray($data): bool
    {
        return true === isset($data['type']) && 'array' === $data['type']
            && true === is_array($data['items'])
            && true === isset($data['items']['type']);
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsMultiTypeArray($data): bool
    {
        return $this->typeIsAvroArray($data)
            && true === is_array($data['items'])
            && false === isset($data['items']['type']);
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsSingleypeArray($data): bool
    {
        return true === $this->typeIsAvroArray($data) && true === is_string($data['items']);
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    private function typeIsAvroArray($data): bool
    {
        return true === isset($data['type']) && 'array' === $data['type'];
    }

    /**
     * @param array|mixed $data
     * @return bool
     */
    protected function typeIsString($data): bool
    {
        return isset($data['type']) && true === is_string($data['type']);
    }
}