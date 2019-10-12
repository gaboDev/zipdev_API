<?php


namespace App\Classes\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response
{
	private $payload;
	
	public function __construct()
	{
		$this->payload = new Payload("", []);
	}
	
	public function ok(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_OK);
		
	}
	
	public function created(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_CREATED);
		
	}
	
	public function noContent(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_NO_CONTENT);
		
	}
	
	public function unprocessable(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
		
	}
	
	public function errorNotFound(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_NOT_FOUND);
		
	}
	
	public function errorBadRequest(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_BAD_REQUEST);
	}
	
	public function errorForbidden(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_FORBIDDEN);
	}
	
	public function errorInternal(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
		
	}
	
	public function errorUnauthorized(string $message = "", array $data = []) : SymfonyResponse {
		return $this->createResponse($message, $data, SymfonyResponse::HTTP_UNAUTHORIZED);
	}
	
	private function createResponseBody(string $message = "", array $data = []) : string {
		$this->payload->setData($data);
		$this->payload->setMessage($message);
		return $this->payload->toJSON();
	}
	
	private function createResponse(string $message = "", array $data = [], int $statusCode) : SymfonyResponse {
		$payload = $this->createResponseBody($message, $data);
		return new SymfonyResponse($payload, $statusCode, ['Content-Type' => 'application/json']);
	}
	
}

