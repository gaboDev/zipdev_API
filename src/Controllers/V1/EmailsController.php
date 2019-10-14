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
	
	/**
	 * @OA\Get(
	 *     path="/api/v1/emails",
	 *     tags={"emails"},
	 *     summary="Return a list of registered emails",
	 *     description="If a single email is provided return its related data.",
	 *     @OA\Parameter(
	 *         name="email",
	 *         in="query",
	 *         description="An existing email",
	 *         required=false,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Successful operation"
	 *     ),
	 *     @OA\Response(
	 *         response="404",
	 *         description="No data found",
	 *     )
	 * )
	 */
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
	
	
	
	/**
	 * @OA\Post(
	 *     path="/api/v1/emails",
	 *     summary="Create an emails",
	 *     tags={"emails"},
	 *     @OA\Parameter(
	 *         name="person_identifier",
	 *         in="query",
	 *         description="The identifier of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="email",
	 *         in="query",
	 *         description="The email of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Some of the required params are not present."
	 *     ),
	 *     @OA\Response(
	 *         response="404",
	 *         description="Person not found for provided identifier.",
	 *     ),
	 *     @OA\Response(
	 *         response="201",
	 *         description="The email was registered successfully",
	 *     )
	 * )
	 */
	public function post(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$email = $request->get('email');
			
			if (!Utils::isValidIdentifier($personIdentifier))
					return $this->response->errorBadRequest("Invalid person identifier.");
			
			if (!Utils::isEmail($email))
					return $this->response->errorBadRequest("Invalid email.");
			
			$person = Person::getById($personIdentifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found for provided identifier");
			
			
			Transaction::beginTransaction();
			
				$emailModel = Email::create($email, $person->id);
			
			Transaction::commit();
			
			return $this->response->created("Email created.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	
	/**
	 * @OA\Delete(
	 *     path="/api/v1/emails",
	 *     summary="Delete an email",
	 *     tags={"emails"},
	 *     @OA\Parameter(
	 *         name="person_identifier",
	 *         in="query",
	 *         description="The identifier of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="email",
	 *         in="query",
	 *         description="The email to delete.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Some of the required params are not present."
	 *     ),
	 *     @OA\Response(
	 *         response="404",
	 *         description="No person|phone found for provided data.",
	 *     ),
	 *     @OA\Response(
	 *         response="422",
	 *         description="It was not possible to delete the email.",
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Email deleted successfully",
	 *     )
	 * )
	 */
	public function delete(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$email = $request->get('email');
			
			if (!Utils::isValidIdentifier($personIdentifier))
				return $this->response->errorBadRequest("Invalid person identifier.");
			
			if (!Utils::isEmail($email))
				return $this->response->errorBadRequest("Invalid email.");
			
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
								 : $this->response->unprocessable("Unable to delete email record.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	
	/**
	 * @OA\Put(
	 *     path="/api/v1/emails",
	 *     summary="Update an email",
	 *     tags={"emails"},
	 *     @OA\Parameter(
	 *         name="email_identifier",
	 *         in="query",
	 *         description="The identifier of the email record.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="email",
	 *         in="query",
	 *         description="The email to update.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Some of the required params are not present."
	 *     ),
	 *     @OA\Response(
	 *         response="404",
	 *         description="No email found for provided data.",
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Email updated successfully",
	 *     )
	 * )
	 */
	public function put(Request $request){
		try {
			
			$emailIdentifier = $request->get('email_identifier');
			$email = $request->get('email');
			
			if (!Utils::isValidIdentifier($emailIdentifier))
				return $this->response->errorBadRequest("Invalid email identifier.");
			
			if (!Utils::isEmail($email))
				return $this->response->errorBadRequest("Invalid email.");
			
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