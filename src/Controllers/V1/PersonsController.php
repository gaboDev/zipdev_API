<?php

namespace Controllers\V1;

use Classes\Utils;
use Controllers\BaseController;
use Models\Email;
use Models\Person;
use Models\Phone;
use Symfony\Component\HttpFoundation\Request;
use Models\QueryBuilder\QueryBuilder as DB;
use Database\DatabaseConnection as Transaction;

class PersonsController extends BaseController
{
	
	public function get(Request $request)
	{
		try{
			
			$id = $request->get('identifier');
			$personsQuery = DB::table(Person::class);
			
			if (Utils::isValidIdentifier($id))
				$personsQuery->where('id', '=', $id);
			
			$persons = $personsQuery->get();
			if (empty($persons))
				return $this->response->ok("No persons found");
			
			return $this->response->ok("ok", $persons);
		}catch (\Exception $exception){
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	public function post(Request $request){
		try{
			
			Transaction::beginTransaction();
			
			$firstName = $request->get('first_name');
			$surnames  = $request->get('surnames');
			$commaSeparatedPhones = $request->get('phones');
			$commaSeparatedEmails = $request->get('emails');
			
			if (!$firstName || !$surnames)
				return $this->response->unprocessable("First name and surnames required.");
			
			$person = Person::create($firstName, $surnames);
			
			if ($commaSeparatedEmails){
				$validEmails = Utils::processCommaSeparatedItems($commaSeparatedEmails, 'isEmail', 'emails');
				foreach ($validEmails as $validEmail)
					Email::create($validEmail, $person->id);
			}
			
			if ($commaSeparatedPhones){
				$validPhones = Utils::processCommaSeparatedItems($commaSeparatedPhones, 'isValidPhone', 'phones');
				foreach ($validPhones as $validPhone)
					Phone::create($validPhone, $person->id);
			}
			
			Transaction::commit();
			
			return $this->response->created("Person created.");
		
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	public function delete(Request $request){
		try{
			
			$identifier = $request->get('identifier');
			if (!Utils::isValidIdentifier($identifier))
				return $this->response->unprocessable("Invalid identifier.");
			
			$person = Person::getById($identifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found.");
			
			if (!$person->delete())
				return $this->response->ok("Unable to delete person record.");
			
			return $this->response->ok("Person and attached data deleted.");
		}catch (\Exception $exception){
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	public function put(Request $request){
		try{
			$identifier = $request->get('identifier');
			$firstName = $request->get('first_name');
			$surnames  = $request->get('surnames');
			
			if (!Utils::isValidIdentifier($identifier))
				return $this->response->unprocessable("Invalid identifier.");
			
			if (!$firstName && !$surnames)
				return $this->response->unprocessable("Params: first_name, surnames at least one required.");
			
			$person = Person::getById($identifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found.");
			
			Transaction::beginTransaction();
			
				if ($firstName)
					$person->first_name = $firstName;
				
				if ($surnames)
					$person->surnames = $surnames;
				
				$person->save();
			
			Transaction::commit();
			
			return $this->response->ok("Person edited.");
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}


}