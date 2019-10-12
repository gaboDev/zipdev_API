<?php


namespace Models;


use Database\BaseModel;
use Models\QueryBuilder\QueryBuilder as DB;

class Person extends BaseModel
{
	protected $table = 'persons';
	public $id;
	public $first_name;
	public $surnames;
	
	static function create($first_name, $surnames) : Person {
		$person = new Person();
		$person->first_name = $first_name;
		$person->surnames= $surnames;
		$person->save();
		return $person;
	}
	
	
	static function getById($identifier){
		return DB::table(Person::class)
				 ->where('id', '=', $identifier)
				 ->first();
	}
	
}