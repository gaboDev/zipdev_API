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
	
	/**
	 * @OA\Get(
	 *     path="/api/v1/phones",
	 *     tags={"phones"},
	 *     summary="Return a list of registered phones",
	 *     description="If a single phone number is provided return its related data.",
	 *     @OA\Parameter(
	 *         name="phone",
	 *         in="query",
	 *         description="An existing phone number",
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
	
	
	
	
	/**
	 * @OA\Post(
	 *     path="/api/v1/phones",
	 *     summary="Create a phone",
	 *     tags={"phones"},
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
	 *         name="phone",
	 *         in="query",
	 *         description="The phone number of the person.",
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
	 *         description="The phone number was registered successfully",
	 *     )
	 * )
	 */
	public function post(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$phone = $request->get('phone');
			
			if (!Utils::isValidIdentifier($personIdentifier))
				return $this->response->errorBadRequest("Invalid person identifier.");
			
			if (!Utils::isValidPhone($phone))
				return $this->response->errorBadRequest("Invalid phone number.");
			
			$person = Person::getById($personIdentifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found for provided identifier");
			
			
			Transaction::beginTransaction();
			
				$phoneModel = Phone::create($phone, $person->id);
				
			Transaction::commit();
			
			return $this->response->created("Phone created.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	/**
	 * @OA\Delete(
	 *     path="/api/v1/phones",
	 *     summary="Delete a phone",
	 *     tags={"phones"},
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
	 *         name="phone",
	 *         in="query",
	 *         description="The phone number to delete.",
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
	 *         description="It was not possible to delete the phone.",
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Phone deleted successfully",
	 *     )
	 * )
	 */
	public function delete(Request $request){
		try{
			
			$personIdentifier = $request->get('person_identifier');
			$phone = $request->get('phone');
			
			if (!Utils::isValidIdentifier($personIdentifier))
				return $this->response->errorBadRequest("Invalid person identifier.");
			
			if (!Utils::isValidPhone($phone))
				return $this->response->errorBadRequest("Invalid phone number.");
			
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
								 : $this->response->unprocessable("Unable to delete phone record.");
			
		}catch (\Exception $exception){
			Transaction::rollBack();
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	
	/**
	 * @OA\Put(
	 *     path="/api/v1/phones",
	 *     summary="Update a phone",
	 *     tags={"phones"},
	 *     @OA\Parameter(
	 *         name="phone_identifier",
	 *         in="query",
	 *         description="The identifier of the phone record.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="phone",
	 *         in="query",
	 *         description="The phone number to update.",
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
	 *         description="No phone found for provided data.",
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Phone updated successfully",
	 *     )
	 * )
	 */
	public function put(Request $request){
		try {
			
			$phoneIdentifier = $request->get('phone_identifier');
			$phone = $request->get('phone');
			
			if (!Utils::isValidIdentifier($phoneIdentifier))
				return $this->response->errorBadRequest("Invalid phone identifier.");
			
			if (!Utils::isValidPhone($phone))
				return $this->response->errorBadRequest("Invalid phone number.");
			
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