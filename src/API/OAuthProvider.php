<?php

declare(strict_types=1);

namespace P4\ControlShift\API;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OAuthProvider extends AbstractProvider
{
	use BearerAuthorizationTrait;

	private const DEFAULT_SCOPE = 'admin';

	protected $instance;
	protected $scope;
	private $lastResponse;

	/**
	 * Returns the base URL for authorizing a client.
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl()
	{
		return $this->instance . '/oauth/authorize';
	}

	/**
	 * Returns the base URL for requesting an access token.
	 *
	 * @param array $params
	 * @return string
	 */
	public function getBaseAccessTokenUrl(array $params)
	{
		return $this->instance . '/oauth/token';
	}

	/**
	 * Returns the URL for requesting the resource owner's details.
	 *
	 * @param AccessToken $token
	 * @return string
	 */
	public function getResourceOwnerDetailsUrl(AccessToken $token)
	{
		return $this->instance . '/api/v1/members';
	}

	/**
	 * Get the default scopes used by this provider
	 * @return array
	 */
	protected function getDefaultScopes()
	{
		return isset($this->scope) ? $this->scope : ['scope' => self::DEFAULT_SCOPE];
	}

	/**
	 * Checks a provider response for errors.
	 *
	 * @throws IdentityProviderException
	 * @param  ResponseInterface $response
	 * @param  array|string $data Parsed response data
	 * @return void
	 */
	protected function checkResponse(ResponseInterface $response, $data)
	{
		$this->lastResponse = $response;
	}

	/**
	 * Generates a resource owner object from a successful resource owner
	 * details request.
	 *
	 * @param  array $response
	 * @param  AccessToken $token
	 * @return ResourceOwnerInterface
	 */
	protected function createResourceOwner(array $response, AccessToken $token)
	{
		return null;
	}

	/**
	 * Returns the default headers used by this provider.
	 *
	 * Typically this is used to set 'Accept' or 'Content-Type' headers.
	 *
	 * @return array
	 */
	protected function getDefaultHeaders()
	{
		return [
			'Accept' => 'application/json',
		];
	}

	/**
	 * Creates a PSR-7 request instance.
	 *
	 * @param  string $method
	 * @param  string $url
	 * @param  AccessTokenInterface|string|null $token
	 * @param  array $options
	 * @return RequestInterface
	 */
	protected function createRequest($method, $url, $token, array $options)
	{
		if (strpos($url, '/') === 0) {
			$url = $this->instance . $url;
		}

		return parent::createRequest($method, $url, $token, $options);
	}

	public function lastResponse() {
		return $this->lastResponse;
	}
}
