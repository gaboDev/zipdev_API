<?php

namespace Database;

use ReflectionClass;

class Utils{
	
	public static function morph(array $records, ReflectionClass $class) {
		$morphedObjects = [];
		foreach ($records as $record)
			$morphedObjects[] = self::morphRecord($record, $class);
		
		return $morphedObjects;
	}
	
	public static function morphRecord(array $record, ReflectionClass $class){
		$model = $class->newInstance();
		$properties = $class->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach($properties as $prop) {
			$propertyName = $prop->getName();
			if (isset($record[$propertyName]))
				$prop->setValue($model, $record[$propertyName]);
		}
		return $model;
	}
}