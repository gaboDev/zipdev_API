<?php


namespace Models;


use Database\BaseModel;
use Models\QueryBuilder\QueryBuilder as DB;

class Email extends BaseModel
{
	protected $table = 'emails';
	public $id;
	public $email;
	public $persons_id;
	
	static function create(string $email, int $person_id) : Email {
		$emailModel = new Email();
		$emailModel->email = $email;
		$emailModel->persons_id = $person_id;
		$emailModel->save();
		return $emailModel;
	}
	
	
	static function getById($identifier){
		return DB::table(Email::class)
			->where('id', '=', $identifier)
			->first();
	}
	
	static function getByEmailAndPersonId(string $phone, int $personId){
		return DB::table(Email::class)
			->where('email', '=', $phone)
			->where('persons_id', '=', $personId)
			->first();
	}
}