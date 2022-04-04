<?php

declare(strict_types=1);

namespace P4\ControlShift\API;

class AuthenticatedEndpoint
{
    /**
     * Endpoints.
     */

    public static function emailOptInTypes(): string
    {
        return self::endpoint('/api/v1/organisation/email_opt_in_types');
    }

    public static function events(?array $args = null): string
    {
        return self::endpoint(
            '/api/v1/events',
            $args ? '?' . \http_build_query($args) : null
        );
    }

    public static function partnerships(?array $args = null): string
    {
        return self::endpoint(
            '/api/v1/partnerships',
            $args ? '?' . \http_build_query($args) : null
        );
    }

    public static function petitions(?array $args = null): string
    {
        return self::endpoint(
            '/api/v1/petitions',
            $args ? '?' . \http_build_query($args) : null
        );
    }

    public static function signatures(string $petition, ?string $slug = null): string
    {
        return self::endpoint('/api/v1/petitions/' . $petition . '/signatures/', $slug);
    }

    /**
     * Basics.
     */

    public static function endpoint(string $url, ?string $slug = null): string
    {
        return '/' . trim($url, '/')
            . ( empty($slug) ? '' : '/' . $slug );
    }
}
