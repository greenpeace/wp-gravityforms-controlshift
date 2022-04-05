<?php

declare(strict_types=1);

namespace P4\ControlShift\API;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OAuthProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private const DEFAULT_SCOPE = 'admin';

    /** @var array{'scope': string} */
    protected ?array $scope;
    private ?ResponseInterface $lastResponse;
    protected string $instance;

    /**
     * Returns the base URL for authorizing a client.
     *
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->instance . '/oauth/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array<string, string> $params
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->instance . '/oauth/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->instance . '/api/v1/members';
    }

    public function lastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    /**
     * Get the default scopes used by this provider
     * @return array{'scope': string}
     */
    protected function getDefaultScopes(): array
    {
        return isset($this->scope) ? $this->scope : ['scope' => self::DEFAULT_SCOPE];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @param  array<string, string>|string $data Parsed response data
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $this->lastResponse = $response;
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array<string, string> $response
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new GenericResourceOwner($response, '');
    }

    /**
     * Returns the default headers used by this provider.
     *
     * Typically this is used to set 'Accept' or 'Content-Type' headers.
     *
     * @return array{'Accept': string}
     */
    protected function getDefaultHeaders(): array
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
     * @param  \League\OAuth2\Client\Token\AccessTokenInterface|string|null $token
     * @param  array<string, string> $options
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    protected function createRequest($method, $url, $token, array $options): RequestInterface
    {
        if (strpos($url, '/') === 0) {
            $url = $this->instance . $url;
        }

        return parent::createRequest($method, $url, $token, $options);
    }
}
