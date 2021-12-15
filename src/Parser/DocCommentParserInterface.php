<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

interface DocCommentParserInterface
{
    public const DOC_DESCRIPTION = 'function-description';

    public function parseDoc(string $docComment): array;
}