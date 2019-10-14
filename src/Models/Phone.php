<?php


namespace Models;


use Database\BaseModel;
use Models\QueryBuilder\QueryBuilder as DB;

class Phone extends BaseModel
{
	protected $table = 'phones';
	public $id;
	public $phone;
	public $persons_id;
	
	static function create(string $phone, int $person_id){
		$phoneModel = new Phone();
		$phoneModel->phone = $phone;
		$phoneModel->persons_id = $person_id;
		$phoneModel->save();
		return $phoneModel;
	}
	
	static function getById($identifier){
		return DB::table(Phone::class)
			->where('id', '=', $identifier)
			->first();
	}
	
	static function getByPhoneAndPersonId(string $phone, int $personId){
		return DB::table(Phone::class)
				 ->where('phone', '=', $phone)
				 ->where('persons_id', '=', $personId)
				 ->first();
	}
}