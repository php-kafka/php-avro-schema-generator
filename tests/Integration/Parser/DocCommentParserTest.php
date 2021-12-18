<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Integration\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser
 */
class DocCommentParserTest extends TestCase
{
    public function testParseDoc(): void
    {
        $docParser = new DocCommentParser();
        $result = $docParser->parseDoc('/**
        * @var string
        asdf some text
        */');

        self::assertEquals(
            [
                'var' => 'string',
                'function-description' =>''
            ],
            $result
        );
    }
}