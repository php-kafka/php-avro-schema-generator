<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

class FullNameOptimizer extends AbstractOptimizer implements OptimizerInterface
{
    /**
     * @param string $definition
     * @return string
     * @throws \JsonException
     */
    public function optimize(string $definition): string
    {
        $data = json_decode($definition, true, JSON_THROW_ON_ERROR);

        $currentNamespace = $data['namespace'] ?? '';
        $data = $this->processSchema($currentNamespace, $data, true);

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * @param string $currentNamespace
     * @param array|mixed $data
     * @param bool $isRoot
     * @return array|mixed|string|null
     */
    private function processSchema(string $currentNamespace, $data, bool $isRoot = false)
    {
        if (true === $this->isRecord($data) && false === $isRoot) {
            $newNamespace = $data['namespace'] ?? '';
            $data = $this->optimizeNamespace($currentNamespace, $data);
            $currentNamespace = $newNamespace;
        }

        $data = $this->handleTypes($currentNamespace, $data);

        if (true === isset($data['fields'])) {
            foreach ($data['fields'] as $index => $field) {
                $data['fields'][$index] = $this->processSchema($currentNamespace, $field);
            }
        }

        if (true === is_string($data)) {
            $data = $this->optimizeNamespace($currentNamespace, $data);
        }

        return $data;
    }

    /**
     * @param string $currentNamespace
     * @param array|mixed $data
     * @return array|mixed|string|null
     */
    private function handleTypes(string $currentNamespace, $data)
    {
        if (true === $this->typeIsRecord($data)) {
            $data['type'] = $this->processSchema($currentNamespace, $data['type']);
        } elseif (true === $this->typeIsTypeArray($data)) {
            foreach ($data['type'] as $index => $type) {
                $data['type'][$index] = $this->processSchema($currentNamespace, $type);
            }
        } elseif (true === $this->typeIsRecordArray($data)) {
            $data['items'] = $this->processSchema($currentNamespace, $data['items']);
        } elseif (true === $this->typeIsMultiTypeArray($data)) {
            foreach ($data['items'] as $index => $item) {
                $data['items'][$index] = $this->processSchema($currentNamespace, $item);
            }
        } elseif (true === $this->typeIsSingleypeArray($data)) {
            $data['items'] = $this->optimizeNamespace($currentNamespace, $data['items']);
        } elseif (true === $this->typeIsString($data)) {
            $data['type'] = $this->optimizeNamespace($currentNamespace, $data['type']);
        }

        return $data;
    }

    /**
     * @param string $currentNamespace
     * @param array|mixed $data
     * @return array|mixed|string|null
     */
    private function optimizeNamespace(string $currentNamespace, $data)
    {
        $data = $this->removeNamespaceFromArray($currentNamespace, $data);
        return $this->removeNamespaceFromString($currentNamespace, $data);
    }

    /**
     * @param string $currentNamespace
     * @param array|mixed $data
     * @return array|mixed|string|null
     */
    private function removeNamespaceFromArray(string $currentNamespace, $data)
    {
        if (false === is_array($data)) {
            return $data;
        }

        $namespace = $data['namespace'] ?? '';

        if ($currentNamespace === $namespace) {
            unset($data['namespace']);
        }

        return $data;
    }

    /**
     * @param string $currentNamespace
     * @param array|mixed $data
     * @return array|mixed|string|null
     */
    private function removeNamespaceFromString(string $currentNamespace, $data)
    {
        if (false === is_string($data)) {
            return $data;
        }

        $currentNameSpacePaths = explode('.', $currentNamespace);
        $dataNameSpacePaths = explode('.', $data);

        foreach ($dataNameSpacePaths as $idx => $dataNameSpacePath) {
            if ($currentNameSpacePaths[$idx] === $dataNameSpacePath) {
                unset($dataNameSpacePaths[$idx]);
            } else {
                break;
            }
        }

        if (1 === sizeof($dataNameSpacePaths)) {
            $data = array_pop($dataNameSpacePaths);
        }

        return $data;
    }
}
