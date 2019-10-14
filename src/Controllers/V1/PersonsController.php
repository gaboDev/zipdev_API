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
	/**
	 * @OA\Get(
	 *     path="/api/v1/persons",
	 *     summary="Return a list of registered persons",
	 *     tags={"persons"},
	 *     description="If a single indentifier is provided return its related data.",
	 *     @OA\Parameter(
	 *         name="identifier",
	 *         in="query",
	 *         description="The id of a person",
	 *         required=false,
	 *         @OA\Schema(
	 *           type="integer"
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
			
			$id = $request->get('identifier');
			$personsQuery = DB::table(Person::class);
			
			if (Utils::isValidIdentifier($id))
				$personsQuery->where('id', '=', $id);
			
			$persons = $personsQuery->get();
			if (!$persons)
				return $this->response->errorNotFound("No persons found");
			
			return $this->response->ok("ok", $persons);
		}catch (\Exception $exception){
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	
	/**
	 * @OA\Post(
	 *     path="/api/v1/persons",
	 *     summary="Create a person",
	 *     tags={"persons"},
	 *     @OA\Parameter(
	 *         name="first_name",
	 *         in="query",
	 *         description="The name of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="surnames",
	 *         in="query",
	 *         description="The surnames of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="phones",
	 *         in="query",
	 *         description="comma-separated list of phone numbers, if present create an entry for each provided phone, Ex. of a valid phone list: 5518524571,5514851275",
	 *         required=false,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="emails",
	 *         in="query",
	 *         description="comma-separated list of emails, if present create an entry for each provided email, Ex. of a valid email list: gabs@gmail.com,alx@hotmail.com",
	 *         required=false,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Some of the required params are not present."
	 *     ),
	 *     @OA\Response(
	 *         response="500",
	 *         description="Some of the comma-separated list items are not valid.",
	 *     ),
	 *     @OA\Response(
	 *         response="201",
	 *         description="The information was registered successfully",
	 *     )
	 * )
	 */
	public function post(Request $request){
		try{
			
			Transaction::beginTransaction();
			
			$firstName = $request->get('first_name');
			$surnames  = $request->get('surnames');
			$commaSeparatedPhones = $request->get('phones');
			$commaSeparatedEmails = $request->get('emails');
			
			if (!$firstName || !$surnames)
				return $this->response->errorBadRequest("First name and surnames required.");
			
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
	
	
	
	/**
	 * @OA\Delete(
	 *     path="/api/v1/persons",
	 *     summary="Delete a person",
	 *     tags={"persons"},
	 *     @OA\Parameter(
	 *         name="identifier",
	 *         in="query",
	 *         description="The identifier of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Some of the required params are not present."
	 *     ),
	 *     @OA\Response(
	 *         response="404",
	 *         description="No person found for provided identifier.",
	 *     ),
	 *     @OA\Response(
	 *         response="422",
	 *         description="It was not possible to delete the person.",
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Person deleted successfully",
	 *     )
	 * )
	 */
	public function delete(Request $request){
		try{
			
			$identifier = $request->get('identifier');
			if (!Utils::isValidIdentifier($identifier))
				return $this->response->errorBadRequest("Invalid identifier.");
			
			$person = Person::getById($identifier);
			if (!$person)
				return $this->response->errorNotFound("Person not found.");
			
			if (!$person->delete())
				return $this->response->unprocessable("Unable to delete person record.");
			
			return $this->response->ok("Person and attached data deleted.");
		}catch (\Exception $exception){
			return $this->response->errorInternal($exception->getMessage());
		}
	}
	
	
	
	
	
	/**
	 * @OA\Put(
	 *     path="/api/v1/persons",
	 *     summary="Update a person",
	 *     tags={"persons"},
	 *     @OA\Parameter(
	 *         name="identifier",
	 *         in="query",
	 *         description="The identifier of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="first_name",
	 *         in="query",
	 *         description="The new first name of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="string"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         name="surnames",
	 *         in="query",
	 *         description="The new surnames of the person.",
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
	 *         description="No person found for provided identifier.",
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Person updated successfully",
	 *     )
	 * )
	 */
	public function put(Request $request){
		try{
			$identifier = $request->get('identifier');
			$firstName = $request->get('first_name');
			$surnames  = $request->get('surnames');
			
			if (!Utils::isValidIdentifier($identifier))
				return $this->response->errorBadRequest("Invalid identifier.");
			
			if (!$firstName && !$surnames)
				return $this->response->errorBadRequest("Params: first_name, surnames at least one required.");
			
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
	
	
	/**
	 * @OA\Put(
	 *     path="/api/v1/getAllRelatedData",
	 *     summary="Retrieve all info about a person",
	 *     tags={"persons"},
	 *     @OA\Parameter(
	 *         name="identifier",
	 *         in="query",
	 *         description="The identifier of the person.",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     )
	 *     @OA\Response(
	 *         response=400,
	 *         description="Some of the required params are not present."
	 *     ),
	 *     @OA\Response(
	 *         response="404",
	 *         description="No person found for provided identifier.",
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Information retrieved successfully",
	 *     )
	 * )
	 */
	public function getAllRelatedData(Request $request){
		
		$identifier = $request->get('identifier');
		if (!Utils::isValidIdentifier($identifier))
			return $this->response->errorBadRequest("Invalid identifier.");
		
		$person = Person::getById($identifier);
		if (!$person)
			return $this->response->errorNotFound("Person not found.");
		
		try{
			
			$relatedData = DB::table(Person::class)
				->select('phone', 'email')
				->join(Phone::class)
				->join(Email::class)
				->where("persons.id", '=', $person->id)
				->get();
			
			$filteredData = Utils::filterData($relatedData, 'phone', 'email');
			$personData = $person->toArray();
			$personData["info"] = $filteredData;
			
			return $this->response->ok("ok", $personData);
			
		}catch (\Exception $exception){
			return $this->response->errorInternal($exception->getMessage());
		}
		
	}


}