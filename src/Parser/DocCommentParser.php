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
        $foundFirstAt = false;

        foreach ($cleanLines as $idx => $line) {
            if (true === str_starts_with($line, '@')) {
                $foundFirstAt = true;
                $nextSpace = strpos($line, ' ');
                $doc[substr($line, 1, $nextSpace -1 )] = substr($line, $nextSpace + 1);
                unset($cleanLines[$idx]);
            } elseif (true === $foundFirstAt) {
                //ignore other stuff for now
                //TODO: Improve multiline @ doc comment
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

            if ('' === $docLines[$idx]) {
                unset($docLines[$idx]);
            }
        }

        return $docLines;
    }

    private function cleanDocLine(string $docLine): string
    {
        $trimmedString = ltrim(rtrim($docLine));

        if (true === str_starts_with($trimmedString, '/**')) {
            $trimmedString = substr($trimmedString, 3);
            $trimmedString = ltrim($trimmedString);
        }

        if (true === str_ends_with($trimmedString, '*/')) {
            $trimmedString = substr($trimmedString, 0, strlen($trimmedString) - 2);
            $trimmedString = rtrim($trimmedString);
        }

        if (true === str_starts_with($trimmedString, '*')) {
            $trimmedString = substr($trimmedString, 1);
            $trimmedString = ltrim($trimmedString);
        }

        return ltrim(rtrim($trimmedString));
    }
}