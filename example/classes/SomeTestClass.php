<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Example;

use PhpKafka\PhpAvroSchemaGenerator\Example\SomeOtherTestClass;
use PhpKafka\PhpAvroSchemaGenerator\Example\SomeBaseClass;

class SomeTestClass extends SomeBaseClass
{

    /**
     * @var string
     */
    private $string;

    /**
     * @var array|int[]
     */
    protected $array;

    /**
     * @var int
     */
    public $int;

    /**
     * @var integer
     */
    public $integer;

    /**
     * @var float
     */
    public $float;

    /**
     * @var bool
     */
    public $bool;

    /**
     * @var boolean
     */
    public $boolean;

    /**
     * @var mixed
     */
    public $mixed;

    /**
     * @var double
     */
    public $double;

    /**
     * @var object
     */
    public $object;

    /**
     * @var callable
     */
    public $callable;

    /**
     * @var resource
     */
    public $resource;

    /**
     * @var SomeOtherTestClass
     */
    public $someOtherTestClass;

    /**
     * @var array|SomeOtherTestClass[]
     */
    public $someOtherTestClasses;

    public int|string $blaaaaaaaa;
}
