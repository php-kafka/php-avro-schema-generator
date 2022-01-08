<?php

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Exception\SkipPropertyException;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyType;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyTypeItem;

/**
 * We will skip transient and private properties like starting at 'o_', '_', 'omitMandatoryCheck', 'allLazyKeysMarkedAsLoaded'
 */
class AvroClassPropertyParser extends ClassPropertyParser {

    /**
     * @throws SkipPropertyException
     */
    public function parseProperty($property): PhpClassPropertyInterface {
        $prop = parent::parseProperty($property);
        if (str_starts_with($prop->getPropertyName(), 'o_') or str_starts_with($prop->getPropertyName(), '_')
            or in_array($prop->getPropertyName(), ['omitMandatoryCheck', 'allLazyKeysMarkedAsLoaded'])) {
            throw new SkipPropertyException();
        }
//        return $prop;
        $prop_ = new PhpClassProperty(
            $prop->getPropertyName(),
            // make type nullable. Can't now in array. See https://github.com/php-kafka/php-avro-schema-generator/issues/33#issuecomment-1007490595
            $prop->getPropertyType()->isNullable() ? $prop->getPropertyType() : new PhpClassPropertyType(new PhpClassPropertyTypeItem('null'), ...$prop->getPropertyType()->getTypeItems()),
//            'null|' . $prop->getPropertyType(), // make type nullable // See https://github.com/php-kafka/php-avro-schema-generator/issues/33#issuecomment-1007551821
                                                  // and only in string work. See https://github.com/php-kafka/php-avro-schema-generator/issues/33#issuecomment-1007490595
            ($prop->getPropertyDefault() != PhpClassPropertyInterface::NO_DEFAULT ?: null),
            $prop->getPropertyDoc(),
            $prop->getPropertyLogicalType()
        );
        return $prop_;
    }
}
