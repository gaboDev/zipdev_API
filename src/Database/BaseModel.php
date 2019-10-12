<?php

namespace Database;

use ReflectionClass;

abstract class BaseModel
{
	
	protected $id;
	protected $autoincrements;
	protected $primaryKey;
	protected $table;
	protected $class;
	protected $dbConnection;
	
	
	public function __construct()
	{
		$this->autoincrements = true;
		$this->primaryKey = 'id';
		$this->class = new \ReflectionClass($this);
		$this->dbConnection = DatabaseConnection::getConnection();
	}
	
	public function save() : void {
		$executeSaveStatement = !$this->hasId();
		$executeResult        = $executeSaveStatement ? $this->executeInsertStatement() : $this->executeUpdateStatement();
		if ($executeResult && $executeSaveStatement)
				$this->id = $this->dbConnection->lastInsertId();
	}
	
	public function delete() : bool {
		if ($this->hasId()){
			return $this->executeDeleteStatement();
		}
	}
	
	public function getTableName(){
		if ($this->table != '')
			return $this->table;
		return strtolower($this->class->getShortName());
	}
	
	public function getPK(){
		return $this->primaryKey;
	}
	
	public function toArray() : array {
		$modelArray = array();
		$props = $this->transformClassProperties(function (\ReflectionProperty $property){
			$propertyName = $property->getName();
			return [$propertyName => $this->{$propertyName}];
		});
		
		foreach ($props as $prop)
			foreach ($prop as $attr => $value)
				$modelArray[$attr] = $value;
		
		return $modelArray;
	}
	
	protected function hasId() : bool {
		return !is_null($this->id);
	}
	
	protected function executeInsertStatement() : bool {
		$tableName = $this->getTableName();
		$attrToInsert = $this->createAttrToInsert();
		$propertiesValues = $this->getArrayOfPropertiesValues();
		$valueClause = $this->createValuesClause();
		$insertQuery = "INSERT INTO $tableName $attrToInsert $valueClause";
		$statement = $this->dbConnection->prepare($insertQuery);
		return $statement->execute($propertiesValues);
	}
	protected function executeDeleteStatement() : bool {
		$tableName = $this->getTableName(); // DELETE FROM `table_name` [WHERE condition];
		$deleteQuery = "DELETE FROM $tableName WHERE $this->primaryKey = ?";
		$statement = $this->dbConnection->prepare($deleteQuery);
		return $statement->execute([$this->id]);
	}
	
	protected function executeUpdateStatement() : bool {
		$tableName = $this->getTableName();
		$setClause = $this->createSetClause();
		$updateQuery = "UPDATE `$tableName` $setClause WHERE id = ?";
		$statement = $this->dbConnection->prepare($updateQuery);
		$propertiesValues = $this->getArrayOfPropertiesValues();
		$propertiesValues[] = $this->id;
		return $statement->execute($propertiesValues);
	}
	
	protected function getClassProperties(){
		return $this->class->getProperties(\ReflectionProperty::IS_PUBLIC);
	}
	
	protected function isAutoIncrement() :  bool {
		return $this->autoincrements;
	}
	
	protected function isPK(\ReflectionProperty $property) : bool {
		$propertyName = $property->getName();
		return $propertyName == $this->primaryKey && $this->isAutoIncrement();
	}
	
	protected function transformClassProperties($callback) : array {
		$props = array();
		$classProperties = $this->getClassProperties();
		foreach ($classProperties as $property){
			if (!$this->isPK($property)){
				$props[] = $callback($property);
			}
		}
		return $props;
	}
	
	protected function createSetClause(): string {
		$props = $this->transformClassProperties(function (\ReflectionProperty $property){
					$propertyName = $property->getName();
					return "$propertyName = ?";
				 });
		$setClause = implode(',', $props);
		return "SET $setClause";
	}
	
	protected function createAttrToInsert(): string {
		$props = $this->transformClassProperties(function (\ReflectionProperty $property){
			return $property->getName();
		});
		$properties = implode(',', $props);
		return "($properties)";
	}
	
	protected function createValuesClause(){
		$valuesArray = $this->transformClassProperties(function (\ReflectionProperty $property){
			return '?';
		});
		$values = implode(',', $valuesArray);
		return "VALUES ($values)";
	}
	
	protected function getArrayOfPropertiesValues() : array {
		return $this->transformClassProperties(function (\ReflectionProperty $property){
						return $this->{$property->getName()};
				});
	}

	protected static function getReflectionClass(): ReflectionClass {
		return new \ReflectionClass(get_called_class());
	}
	
}