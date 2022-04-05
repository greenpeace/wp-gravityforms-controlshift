<?php

declare(strict_types=1);

namespace P4\ControlShift\API;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use P4\ControlShift\API\AuthenticatedEndpoint as Endpoint;

/**
 * @phpstan-type EmailOptInType array{
 *  'context': string,
 *  'kind': string,
 *  'mailable': bool,
 *  'active': bool,
 *  'external_id': string
 * }
 * @phpstan-type Event array{
 *  'id': int,
 *  'slug': string,
 *  'title': string,
 *  'url': string,
 *  'public_signature_count': int,
 *  'goal': int,
 *  'created_at': string,
 *  'updated_at': string
 * }
 * @phpstan-type Partnership array{
 *  'id': int,
 *  'slug': string,
 *  'title': string,
 *  'url': string,
 *  'public_signature_count': int,
 *  'goal': int,
 *  'created_at': string,
 *  'updated_at': string
 * }
 * @phpstan-type Petition array{
 *  'id': int,
 *  'slug': string,
 *  'title': string,
 *  'url': string,
 *  'public_signature_count': int,
 *  'goal': int,
 *  'created_at': string,
 *  'updated_at': string
 * }
 * @phpstan-type Signature array{
 *  'id': int,
 *  'first_name': string,
 *  'last_name': string,
 *  'email_opt_in_type': EmailOptInType
 * }
 */
class AuthenticatedAPI
{
    //private static $instance = null;

    private OAuthProvider $oauth;
    private ?AccessTokenInterface $accessToken;

    public function __construct(
        OAuthProvider $oauth,
        ?AccessTokenInterface $accessToken = null
    ) {
        $this->oauth = $oauth;
        $this->accessToken = $accessToken;
    }

    //
    // API functionalities
    //

    /**
     * @return array<Event>
     */
    public function events(): array
    {
        return $this->getFullPaginatedList('events');
    }

    /**
     * @return array<Partnership>
     */
    public function partnerships(): array
    {
        return $this->getFullPaginatedList('partnerships');
    }

    /**
     * @return array<Petition>
     */
    public function petitions(): array
    {
        return $this->getFullPaginatedList('petitions');
    }

    /**
     * @return array<EmailOptInType>
     */
    public function emailOptInTypes(): array
    {
        $response = $this->request(Endpoint::emailOptInTypes());
        return $response['email_opt_in_types'] ?? [];
    }

    /**
     * @return array<Signature>
     */
    public function signatures(string $petition): array
    {
        return $this->getFullPaginatedList('signatures', Endpoint::signatures($petition));
    }

    /**
     * @param array<string, array<string, string>> $payload
     * @return array<string, array<string, string>>
     */
    public function postSignature(string $petition_slug, array $payload): array
    {
        return $this->request(
            Endpoint::signatures($petition_slug),
            'POST',
            ['body' => \http_build_query($payload)]
        );
    }

    public function getAccessToken(?string $authorizationCode = null): AccessTokenInterface
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
        }

        // Token expired, negociate a new one
        if ($this->accessToken->hasExpired()) {
            $this->accessToken = $this->refreshAccessToken();
        }

        return $this->accessToken;
    }

    public function getAccessTokenFromAuthorizationCode(
        string $authorizationCode
    ): AccessTokenInterface {
        $accessToken = $this->oauth->getAccessToken(
            'authorization_code',
            ['code' => $authorizationCode,]
        );
        return $accessToken;
    }

    public function refreshAccessToken(): AccessTokenInterface
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
     * @param array<string, int|string> $args
     * @return array<string, array<string, mixed>> Parsed response
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
            $lastResponse = $this->oauth->lastResponse();
            throw new \Exception(
                'No valid response from API. '
                . ($lastResponse ? sprintf(
                    '%d : %s -- %s',
                    $lastResponse->getStatusCode(),
                    $lastResponse->getReasonPhrase(),
                    $lastResponse->getHeaderLine('www-authenticate'),
                ) : '')
            );
        }


        return $response;
    }

    /**
     * @return array<mixed>
     */
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
