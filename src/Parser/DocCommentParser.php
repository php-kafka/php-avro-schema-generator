<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

class DocCommentParser implements DocCommentParserInterface
{
    public function parseDoc(string $docComment): array
    {
        $doc = [];
        $docLines = explode(PHP_EOL, $docComment);
        $cleanLines = $this->cleanDocLines($docLines);

        foreach ($cleanLines as $idx => $line) {
            if (true === str_starts_with($line, '@')) {
                $nextSpace = strpos($line, ' ');
                $doc[substr($line, 1, $nextSpace)] = substr($line, $nextSpace + 1);
                unset($cleanLines[$idx]);
            }
        }

        $doc[self::DOC_DESCRIPTION] =  implode(' ', $cleanLines);

        return $doc;
    }

    private function cleanDocLines(array $docLines): array
    {
        foreach ($docLines as $idx => $docLine) {
            $docLines[$idx] = $this->cleanDocLine($docLine);
        }

        return $docLines;
    }

    private function cleanDocLine(string $docLine): string
    {
        $trimmedString = ltrim(rtrim($docLine));

        if (true === str_starts_with($docLine, '/**')) {
            $trimmedString = substr($trimmedString, 3);
        }

        if (true === str_ends_with($docLine, '*/')) {
            $trimmedString = substr($trimmedString, 0, strlen($trimmedString) - 2);
        }

        if (true === str_starts_with($docLine, '*')) {
            $trimmedString = substr($trimmedString, 1);
        }

        return ltrim(rtrim($trimmedString));
    }
}