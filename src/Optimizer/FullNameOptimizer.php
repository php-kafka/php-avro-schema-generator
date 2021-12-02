<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

class FullNameOptimizer implements OptimizerInterface
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

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    private function processSchema(string $currentNamespace, $data, bool $isRoot = false)
    {
        if (true === isset($data['type']) && 'record' === $data['type'] && false === $isRoot) {
            $newNamespace = $data['namespace'] ?? '';
            $data = $this->optimizeNamespace($currentNamespace, $data);
            $currentNamespace = $newNamespace;
        }

        if (true === isset($data['type']) && true === is_string($data['type'])) {
            $data['type'] = $this->optimizeNamespace($currentNamespace, $data['type']);
        }

        if (true === isset($data['type']) && true === is_array($data['type'])) {
            if (true === isset($data['type']['type'])) {
                $data['type'] = $this->processSchema($currentNamespace, $data['type']);
            } else {
                foreach($data['type'] as $index => $type) {
                    $data['type'][$index] = $this->processSchema($currentNamespace, $type);
                }
            }
        }

        if (true === isset($data['type']) && 'array' === $data['type']) {
            if (true === is_array($data['items'])) {
                if(true === isset($data['items']['type'])) {
                    $data['items'] = $this->processSchema($currentNamespace, $data['items']);
                } else {
                    foreach($data['items'] as $index => $item) {
                        $data['items'][$index] = $this->processSchema($currentNamespace, $item);
                    }
                }
            } else {
                $data['items'] = $this->optimizeNamespace($currentNamespace, $data['items']);
            }
        }

        if (true === isset($data['fields'])) {
            foreach($data['fields'] as $index => $field) {
                $data['fields'][$index] = $this->processSchema($currentNamespace, $field);
            }
        }

        if (true === is_string($data)) {
            $data = $this->optimizeNamespace($currentNamespace, $data);
        }

        return $data;
    }

    private function optimizeNamespace(string $currentNamespace, $data)
    {
        if (true === is_array($data)) {
            $namespace = $data['namespace'] ?? '';

            if ($currentNamespace === $namespace) {
                unset($data['namespace']);
            }
        } elseif (true === is_string($data)) {
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
        }

        return $data;
    }
}