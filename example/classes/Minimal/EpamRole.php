<?php

/**
* Inheritance: no
* Variants: no


Fields Summary:
- code [input]
- name [input]
- fullName [input]
- masterSystem [multiselect]
- roleSubject [select]
- roleObject [multiselect]
- topic [multiselect]
- description [textarea]
*/

namespace PhpKafka\PhpAvroSchemaGenerator\Example\Minimal;

/**
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing getList()
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByCode($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByName($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByFullName($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByMasterSystem($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByRoleSubject($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByRoleObject($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByTopic($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole\Listing|\PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole|null getByDescription($value, $limit = 0, $offset = 0, $objectTypes = null)
*/

class EpamRole {
protected $o_classId = "6";
protected $o_className = "EpamRole";
protected $code;
protected $name;
protected $fullName;
/**
* @var string[]
**/
protected $masterSystem;
protected $roleSubject;
protected $roleObject;
protected $topic;
protected $description;


/**
* @param array $values
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public static function create($values = array()) {
	$object = new static();
	return $object;
}

/**
* Get code - code
* @return string|null
*/
public function getCode(): ?string
{
	return $this->code;
}

/**
* Set code - code
* @param string|null $code
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setCode(?string $code)
{
	$this->code = $code;

	return $this;
}

/**
* Get name - name
* @return string|null
*/
public function getName(): ?string
{
	return $this->name;
}

/**
* Set name - name
* @param string|null $name
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setName(?string $name)
{
	$this->name = $name;

	return $this;
}

/**
* Get fullName - fullName
* @return string|null
*/
public function getFullName(): ?string
{
	return $this->fullName;
}

/**
* Set fullName - fullName
* @param string|null $fullName
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setFullName(?string $fullName)
{
	$this->fullName = $fullName;

	return $this;
}

/**
* Get masterSystem - masterSystem
* @return string[]|null
*/
public function getMasterSystem(): ?array
{
	$data = $this->masterSystem;
	return $data;
}

/**
* Set masterSystem - masterSystem
* @param string[]|null $masterSystem
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setMasterSystem(?array $masterSystem)
{
	$this->masterSystem = $masterSystem;
	return $this;
}

/**
* Get roleSubject - subject
* @return string|null
*/
public function getRoleSubject(): ?string
{
	$data = $this->roleSubject;
	return $data;
}

/**
* Set roleSubject - subject
* @param string|null $roleSubject
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setRoleSubject(?string $roleSubject)
{
	$this->roleSubject = $roleSubject;
	return $this;
}

/**
* Get roleObject - roleObject
* @return string[]|null
*/
public function getRoleObject(): ?array
{
	$data = $this->roleObject;
	return $data;
}

/**
* Set roleObject - roleObject
* @param string[]|null $roleObject
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setRoleObject(?array $roleObject)
{
	$this->roleObject = $roleObject;
	return $this;
}

/**
* Get topic - topic
* @return string[]|null
*/
public function getTopic(): ?array
{
	$data = $this->topic;
	return $data;
}

/**
* Set topic - topic
* @param string[]|null $topic
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setTopic(?array $topic)
{
	$this->topic = $topic;
	return $this;
}

/**
* Get description - description
* @return string|null
*/
public function getDescription(): ?string
{
	$data = $this->description;
	return $data;
}

/**
* Set description - description
* @param string|null $description
* @return \PhpKafka\PhpAvroSchemaGenerator\Example\Minimal\EpamRole
*/
public function setDescription(?string $description)
{
	$this->description = $description;
	return $this;
}

}
