<?php

namespace Controllers\V1;

use Classes\Utils;
use Controllers\BaseController;
use Models\Person;
use Models\Phone;
use Symfony\Component\HttpFoundation\Request;
use Models\QueryBuilder\QueryBuilder as DB;
use Database\DatabaseConnection as Transaction;

class PhonesController extends BaseController
{
	
	public function get(Request $request)
	{
		try{
			
			$phone = $request->get('phone');
			$phonesQuery = DB::table(Phone::class);
			
			if (Utils::isValidPhone($phone))
				$phonesQuery->where('phone', '=', $phone);
			
			$phones = $phonesQuery->get();
			if (!$phones)
				return $this->response->errorNotFound("No phones found");
			
			return $this->response->ok("ok", $phones);
		}catch (\Exception $exception){
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	public function post(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$phone = $request->get('phone');
			
			if (!Utils::isValidIdentifier($personIdentifier))
				return $this->response->unprocessable("Invalid person identifier.");
			
			if (!Utils::isValidPhone($phone))
				return $this->response->unprocessable("Invalid phone number.");
			
			$person = Person::getById($personIdentifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found for provided identifier");
			
			
			Transaction::beginTransaction();
			
				$phoneModel = new Phone();
				$phoneModel->phone = $phone;
				$phoneModel->person_id = $person->id;
				$phoneModel->save();
				
			Transaction::commit();
			
			return $this->response->created("Phone created.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	public function delete(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$phone = $request->get('phone');
			
			if (!Utils::isValidIdentifier($personIdentifier))
				return $this->response->unprocessable("Invalid person identifier.");
			
			if (!Utils::isValidPhone($phone))
				return $this->response->unprocessable("Invalid phone number.");
			
			$person = Person::getById($personIdentifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found for provided identifier.");
			
			$phoneModel = Phone::getByPhoneAndPersonId($phone, $person->id);
			if (!$phoneModel)
				return $this->response->errorNotFound("Phone not found for provided data.");
			
			
			Transaction::beginTransaction();
				$deleteResult = $phoneModel->delete();
			Transaction::commit();
			
			return $deleteResult ? $this->response->ok("Phone record deleted.")
								 : $this->response->ok("Unable to delete phone record.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	public function put(Request $request){
		try {
			
			$phoneIdentifier = $request->get('phone_identifier');
			$phone = $request->get('phone');
			
			if (!Utils::isValidIdentifier($phoneIdentifier))
				return $this->response->unprocessable("Invalid phone identifier.");
			
			if (!Utils::isValidPhone($phone))
				return $this->response->unprocessable("Invalid phone number.");
			
			$phoneModel = Phone::getById($phoneIdentifier);
			if (!$phoneModel)
				return $this->response->errorNotFound("Phone not found for provided data.");
			
			
			Transaction::beginTransaction();
			
				$phoneModel->phone = $phone;
				$phoneModel->save();
			
			Transaction::commit();
			
			return $this->response->ok("Phone record edited.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
		
	}
	
}