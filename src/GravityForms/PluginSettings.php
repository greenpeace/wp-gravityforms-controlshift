<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use League\OAuth2\Client\Token\AccessToken;

/**
 * This page appears in the Forms > Settings menu, as a tab on the left
 *
 * @phpstan-import-type GFSection from ControlShiftAddOn
 */
class PluginSettings
{
    /**
     * @return array{GFSection}
     */
    public static function fields(ControlShiftAddOn $addOn): array
    {
        $fields = [
            self::appSettingsSection(),
            self::authCodeSection($addOn),
        ];

        if (!empty($addOn->api())) {
            $fields[] = self::authTokenSection($addOn);
        }

        return $fields;
    }

    /**
     * @return GFSection
     */
    private static function appSettingsSection(): array
    {
        return [
            'title' => 'ControlShift Application Settings',
            'description' => 'Create a new Application in your ControlShift instance administration, '
              . 'in <em>Settings > Integrations > REST API Apps</em>, then fill these fields.',
            'fields' => [
                [
                    'name' => 'instance',
                    'type' => 'text',
                    'label' => 'ControlShift instance URL',
                    'tooltip' => 'Example: https://gpfood.controlshiftlabs.com/',
                ],
                [
                    'name' => 'client_id',
                    'type' => 'text',
                    'label' => 'Application Id',
                ],
                [
                    'name' => 'client_secret',
                    'type' => 'text',
                    'label' => 'Secret',
                ],
                [
                    'name' => 'callback_url',
                    'type' => 'text',
                    'label' => 'Callback URL',
                    'default_value' => 'urn:ietf:wg:oauth:2.0:oob',
                ],
            ],
        ];
    }

    /**
     * @return GFSection
     */
    private static function authCodeSection(ControlShiftAddOn $addOn): array
    {
        return [
            'title' => 'Authorization Code',
            'description' => 'Enter Authorization code to allow this plugin '
              . 'to interact with your ControlShift instance.',
            'fields' => [
                [
                    'name' => 'authorization_code',
                    'type' => '',
                    'callback' => fn() => self::authorizationCode($addOn),
                ],
            ],
        ];
    }

    /**
     * @return GFSection
     */
    private static function authTokenSection(ControlShiftAddOn $addOn): array
    {
        return [
            'title' => 'Access Token',
            'fields' => [
                [
                    'name' => 'auth_token',
                    'type' => '',
                    'callback' => function () use ($addOn): void {
                        try {
                            self::authToken($addOn->getAuthToken());
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    },
                ],
            ],
        ];
    }

    private static function authorizationCode(ControlShiftAddOn $addOn): void
    {
        $addOn->settings_text([
            'label' => 'Authorization code',
            'name' => 'authorization_code',
            'type' => 'text',
            'tooltip' => 'You can obtain the authorization code by clicking '
              . 'on "Authorize" in your ControlShift application configuration.',
        ]);

        if (empty($addOn->api())) {
            echo '<p><strong>
				Fill configuration settings and reload this page to get an authorization link.
			</strong></p>';
            return;
        }

        echo '<p>Open this >>> <a href="' . $addOn->api()->getAuthorizationUrl() . '" target="_blank">
			Authorize link
		</a> <<< and copy the given code here.</p>';
    }

    /**
     * @param array<string, string>|null $tokenData
     */
    private static function authToken(?array $tokenData = null): void
    {
        if (empty($tokenData)) {
            echo '<p><em>No valid access token found.</em></p>';
            return;
        }

        $token = new AccessToken($tokenData);
        $expires = $token->getExpires();

        echo '<p>';
        echo 'Access Token: ' . $token->getToken() . "<br/>";
        echo 'Refresh Token: ' . $token->getRefreshToken() . "<br/>";
        echo 'Created at: ' . date('d/m/Y H:i:s', $token->getValues()['created_at']) . "<br/>";
        echo 'Expires at: ' . (
            $expires ? date('d/m/Y H:i:s', $expires) : 'never'
        ) . "<br/>";
        echo 'Expired: ' . ($token->hasExpired() ? 'true' : 'false') . "<br/>";
        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        echo 'Additional infos: <pre>', print_r($token->getValues(), true), '</pre>';
        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        echo 'Token object: <pre>', print_r($token, true), '</pre>';
        echo '</p>';
    }
}
