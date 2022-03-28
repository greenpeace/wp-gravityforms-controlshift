<?php

declare(strict_types=1);

namespace P4\ControlShift\API;

use P4\ControlShift\API\AuthenticatedEndpoint as Endpoint;
use League\OAuth2\Client\Token\AccessToken;

use GFAPI;

class AuthenticatedAPI {

	//private static $instance = null;

	private $oauth;
	private $accessToken;

	/**
	 *
	 */
	public function __construct(
		OAuthProvider $oauth,
		?AccessToken $accessToken = null
	) {
		$this->oauth = $oauth;
		$this->accessToken = $accessToken;
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return object $_instance An instance of this class.
	 */
/*	public static function getInstance()
	{
		if ( self::$instance == null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}*/

	//
	// API functionalities
	//

	public function request(string $url, string $method = 'GET', $args = []): ?array
	{
		$request = $this->oauth->getAuthenticatedRequest(
			$method,
			$url,
			$this->getAccessToken(),
			$args
		);
/*		var_dump(
			(string) $request->getUri(),
			$request->getHeaders(),
			(string) $request->getBody(),
		);*/

        $response = $this->oauth->getParsedResponse($request);
		if (empty($response)) {
			throw new \Exception(
				'No valid response from API. '
				. sprintf('%d : %s -- %s',
					$this->oauth->lastResponse()->getStatusCode(),
					$this->oauth->lastResponse()->getReasonPhrase(),
					$this->oauth->lastResponse()->getHeaderLine('www-authenticate'),
				)
			);
		}


		return $response;
	}

	public function events(): ?array
	{
		return $this->get_full_paginated_list('events');
	}

	public function partnerships(): ?array
	{
		return $this->get_full_paginated_list('partnerships');
	}

	public function petitions(): ?array
	{
		return $this->get_full_paginated_list('petitions');
	}

	public function email_opt_in_types(): ?array
	{
		$response =  $this->request(Endpoint::email_opt_in_types());
		return $response['email_opt_in_types'] ?? [];
	}

	public function signatures(string $petition): ?array
	{
		return $this->get_full_paginated_list('signatures', Endpoint::signatures($petition));
	}

	public function post_signature($petition_slug, $payload)
	{
		return $this->request(
			Endpoint::signatures($petition_slug),
			'POST',
			['body' => \http_build_query($payload)]
		);
	}

	private function get_full_paginated_list(string $resource, ?string $url = null)
	{
		$items = [];
		$url = $url ?? Endpoint::$resource();

		$page = 1;
		while ($page) {
			$response = $this->request(
				$url . '?' . \http_build_query(['page' => $page])
			);
			if (!$response) {
				return $items;
			}

			$items = array_merge($items, $response[$resource]);

			$meta = $response['meta'] ?? [];
			$page = $meta['next_page'] ?? null;
		}

		return $items;
	}

	//
	// Token dance
	//

	public function getAccessToken($authorizationCode = null): ?AccessToken
	{
		// No token given, negociate with authentication code
		if (!$this->accessToken || !($this->accessToken instanceof AccessToken)) {
			if (empty($authorizationCode)) {
				throw new \Exception(
					'Missing access token and authorization code. '
					. 'Retrieve an authorization code on ' . $this->getAuthorizationUrl()
				);
			}

			try {
				$this->accessToken = $this->getAccessTokenFromAuthorizationCode(
					$authorizationCode
				);
			} catch (\Exception $e) {
				GFAPI::log_debug($e->getMessage());
				return null;
			}
		}

		// Token expired, negociate a new one
		if ($this->accessToken->hasExpired()) {
		    //$this->accessToken = $this->getAccessTokenFromOldToken();
			try {
		    	$this->accessToken = $this->refreshAccessToken();
		    } catch (\Exception $e) {
		    	GFAPI::log_debug($e->getMessage());
		    	return null;
		    }
		}

		return $this->accessToken;
	}

/*	public function getAuthorizationCode() {
		$request = $this->oauth->getRequest(
			'GET',
			$this->getAuthorizationUrl()
		);
        $response = $this->oauth->getResponse($request);
	}*/

	public function getAccessTokenFromAuthorizationCode($authorizationCode)
	{
		GFAPI::log_debug(__METHOD__);
		$accessToken = $this->oauth->getAccessToken(
			'authorization_code',
			['code' => $authorizationCode]
		);
		return $accessToken;
	}

	public function getAccessTokenFromOldToken()
	{
		GFAPI::log_debug(__METHOD__);
		$accessToken = $this->oauth->getAccessToken(
			'authorization_code',
			['code' => $this->accessToken->getToken()]
		);
		return $accessToken;
	}

	public function refreshAccessToken()
	{
		GFAPI::log_debug(__METHOD__);
	    $accessToken = $this->oauth->getAccessToken(
	    	'refresh_token',
	    	[
	    		'refresh_token' => $this->accessToken->getToken(),
	    	]
	    );
	    return $accessToken;
	}

	public function getAuthorizationUrl()
	{
		return $this->oauth->getAuthorizationUrl();
	}
}
