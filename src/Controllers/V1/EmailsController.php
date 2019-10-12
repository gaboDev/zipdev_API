<?php

namespace Controllers\V1;

use Classes\Utils;
use Controllers\BaseController;
use Models\Email;
use Models\Person;
use Symfony\Component\HttpFoundation\Request;
use Models\QueryBuilder\QueryBuilder as DB;
use Database\DatabaseConnection as Transaction;

class EmailsController extends BaseController
{
	
	public function get(Request $request)
	{
		try{
			
			$email = $request->get('email');
			$emailsQuery = DB::table(Email::class);
			
			if (Utils::isEmail($email))
				$emailsQuery->where('phone', '=', $email);
			
			$emails = $emailsQuery->get();
			if (!$emails)
				return $this->response->errorNotFound("No emails found");
			
			return $this->response->ok("ok", $emails);
		}catch (\Exception $exception){
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	public function post(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$email = $request->get('email');
			
			if (!Utils::isValidIdentifier($personIdentifier))
					return $this->response->unprocessable("Invalid person identifier.");
			
			if (!Utils::isEmail($email))
					return $this->response->unprocessable("Invalid email.");
			
			$person = Person::getById($personIdentifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found for provided identifier");
			
			
			Transaction::beginTransaction();
			
				$emailModel = new Email();
				$emailModel->email = $email;
				$emailModel->person_id = $person->id;
				$emailModel->save();
			
			Transaction::commit();
			
			return $this->response->created("Email created.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	public function delete(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$email = $request->get('email');
			
			if (!Utils::isValidIdentifier($personIdentifier))
				return $this->response->unprocessable("Invalid person identifier.");
			
			if (!Utils::isEmail($email))
				return $this->response->unprocessable("Invalid email.");
			
			$person = Person::getById($personIdentifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found for provided identifier.");
			
			$emailModel = Email::getByEmailAndPersonId($email, $person->id);
			if (!$emailModel)
				return $this->response->errorNotFound("Email not found for provided data.");
			
			
			Transaction::beginTransaction();
				$deleteResult = $emailModel->delete();
			Transaction::commit();
			
			return $deleteResult ? $this->response->ok("Email record deleted.")
								 : $this->response->ok("Unable to delete email record.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	public function put(Request $request){
		try {
			
			$emailIdentifier = $request->get('email_identifier');
			$email = $request->get('email');
			
			if (!Utils::isValidIdentifier($emailIdentifier))
				return $this->response->unprocessable("Invalid email identifier.");
			
			if (!Utils::isEmail($email))
				return $this->response->unprocessable("Invalid email.");
			
			$emailModel = Email::getById($emailIdentifier);
			if (!$emailModel)
				return $this->response->errorNotFound("Email not found for provided data.");
			
			
			Transaction::beginTransaction();
			
				$emailModel->email = $email;
				$emailModel->save();
			
			Transaction::commit();
			
			return $this->response->ok("Email record edited.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}


}