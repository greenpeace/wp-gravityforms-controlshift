<?php

declare(strict_types=1);

namespace P4\ControlShift\API;

use League\OAuth2\Client\Token\AccessToken;
use P4\ControlShift\API\AuthenticatedEndpoint as Endpoint;

class AuthenticatedAPI
{
    //private static $instance = null;

    private OAuthProvider $oauth;
    private ?AccessToken $accessToken;

    public function __construct(
        OAuthProvider $oauth,
        ?AccessToken $accessToken = null
    ) {
        $this->oauth = $oauth;
        $this->accessToken = $accessToken;
    }

    //
    // API functionalities
    //

    public function events(): ?array
    {
        return $this->getFullPaginatedList('events');
    }

    public function partnerships(): ?array
    {
        return $this->getFullPaginatedList('partnerships');
    }

    public function petitions(): ?array
    {
        return $this->getFullPaginatedList('petitions');
    }

    public function emailOptInTypes(): ?array
    {
        $response = $this->request(Endpoint::emailOptInTypes());
        return $response['email_opt_in_types'] ?? [];
    }

    public function signatures(string $petition): ?array
    {
        return $this->getFullPaginatedList('signatures', Endpoint::signatures($petition));
    }

    public function postSignature(string $petition_slug, array $payload): ?array
    {
        return $this->request(
            Endpoint::signatures($petition_slug),
            'POST',
            ['body' => \http_build_query($payload)]
        );
    }

    public function getAccessToken(?string $authorizationCode = null): AccessToken
    {
        // No token given, negociate with authentication code
        if (!$this->accessToken || !($this->accessToken instanceof AccessToken)) {
            if (empty($authorizationCode)) {
                throw new \BadMethodCallException(
                    'No access token available or authorization code given. '
                    . 'Retrieve an authorization code at ' . $this->getAuthorizationUrl()
                );
            }

            $this->accessToken = $this->getAccessTokenFromAuthorizationCode($authorizationCode);
            if (!$this->accessToken) {
                throw new \UnexpectedValueException('No access token found.');
            }
        }

        // Token expired, negociate a new one
        if ($this->accessToken->hasExpired()) {
            $this->accessToken = $this->refreshAccessToken();
        }

        return $this->accessToken;
    }

    public function getAccessTokenFromAuthorizationCode(string $authorizationCode): AccessToken
    {
        $accessToken = $this->oauth->getAccessToken(
            'authorization_code',
            ['code' => $authorizationCode,]
        );
        return $accessToken;
    }

    public function refreshAccessToken(): AccessToken
    {
        $accessToken = $this->oauth->getAccessToken(
            'refresh_token',
            ['refresh_token' => $this->accessToken ? $this->accessToken->getToken() : null,]
        );
        return $accessToken;
    }

    public function getAuthorizationUrl(): string
    {
        return $this->oauth->getAuthorizationUrl();
    }

    /**
     * @return mixed Parsed response
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
    public function request(string $url, string $method = 'GET', array $args = [])
    {
        $request = $this->oauth->getAuthenticatedRequest(
            $method,
            $url,
            $this->getAccessToken(),
            $args
        );

        $response = $this->oauth->getParsedResponse($request);

        if (empty($response)) {
            throw new \Exception(
                'No valid response from API. '
                . sprintf(
                    '%d : %s -- %s',
                    $this->oauth->lastResponse()->getStatusCode(),
                    $this->oauth->lastResponse()->getReasonPhrase(),
                    $this->oauth->lastResponse()->getHeaderLine('www-authenticate'),
                )
            );
        }


        return $response;
    }

    private function getFullPaginatedList(string $resource, ?string $url = null): array
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
}
