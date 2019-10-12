<?php

namespace Models\QueryBuilder;



class QueryBuilder
{
	protected static $query;
	
	static function table($classReference){
		self::$query = new Query($classReference);
		return self::$query;
	}
	
}