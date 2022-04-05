<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use GFFeedAddOn;
use League\OAuth2\Client\Token\AccessToken;
use P4\ControlShift\API\AuthenticatedAPI;
use P4\ControlShift\API\OAuthProvider;

/**
 * ControlShift add-on class
 * Extends GFFeedAddOn to offer settings and feed processing to ControlShift
 *
 * @phpstan-type GFField array{
 *  'name': string,
 *  'type': string,
 *  'label'?: string,
 *  'tooltip'?: string,
 *  'default_value'?: string,
 *  'callback'?: callable(): void}
 * @phpstan-type GFSection array{
 *  'title': string,
 *  'description'?: string,
 *  'fields': array{GFField}}
 * @phpstan-type GFFeed array{'meta': array<string, string>}
 * @phpstan-type GFEntry array<string, string>
 * @phpstan-type GFForm array<string, string>
 * @phpstan-type TokenData array<string, string>
 */
class ControlShiftAddOn extends GFFeedAddOn
{
    public const TEXT_DOMAIN = 'wp-gravityforms-controlshift';

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    // phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
    /** @var string */
    protected $_version = '1.0';
    /** @var string */
    protected $_min_gravityforms_version = '1.9';
    /** @var string */
    protected $_slug = 'controlshift';
    /** @var string */
    protected $_path = 'wp-gravityforms-controlshift/controlshift.php';
    /** @var string */
    protected $_full_path = 'wp-gravityforms-controlshift/controlshift.php';
    /** @var string */
    protected $_title = 'Gravity Forms ControlShift add-on';
    /** @var string */
    protected $_short_title = 'Controlshift';
    // phpcs:enable

    private static ?self $instance = null;
    private ?AuthenticatedAPI $api = null;

    public function __construct()
    {
        $settings = $this->get_plugin_settings();
        if (empty($settings)) {
            return;
        }

        $this->api = new AuthenticatedAPI(
            new OAuthProvider([
                'instance' => rtrim($settings['instance'], '/'),
                'clientId' => $settings['client_id'],
                'clientSecret' => $settings['client_secret'],
                'redirectUri' => $settings['callback_url'],
            ]),
            empty($settings['auth_token'])
                ? null
                : new AccessToken($settings['auth_token'])
        );
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    /**
     * @return array{GFSection}
     */
    public function plugin_settings_fields(): array
    {
        return PluginSettings::fields($this);
    }

    /**
     * @param array<string, string> $form
     *
     * @return array{GFSection}
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function form_settings_fields($form): array
    {
        return FormSettings::fields($this);
    }

    /**
     * @return array{GFSection}
     */
    public function feed_settings_fields(): array
    {
        return FeedSettings::fields($this);
    }

    /**
     * @return array<string, string>
     */
    public function feed_list_columns(): array
    {
        return array(
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
            'petition_slug' => __('Petition', self::TEXT_DOMAIN),
        );
    }

    /**
     * @param GFFeed $feed
     * @param GFEntry $entry
     * @param GFForm $form
     *
     * @return GFEntry
     */
    public function process_feed($feed, $entry, $form): array
    {
        if (!$this->api) {
            throw new \Exception('APi not initialized, missing settings ?');
        }

        $petition_slug = $feed['meta']['petition_slug'] ?? null;
        if (!$petition_slug) {
            return $entry;
        }

        $signature = [];

        unset($feed['meta']['petition_slug']);
        foreach ($feed['meta'] as $key => $field_id) {
            if (empty($field_id)) {
                continue;
            }

            $signature[$key] = $this->get_field_value($form, $entry, $field_id);
        }

        if (empty($signature)) {
            return $entry;
        }

        $petitions = $this->api->petitions();
        $signature['email_opt_in_type_external_id'] = 'web_form';

        $response = $this->api->postSignature($petition_slug, ['signature' => $signature]);

        if (empty($response['errors'])) {
            return $entry;
        }

        //echo '<pre>', print_r($response, true), '</pre>';
        return $entry;
    }

    public static function get_instance(): object
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    // phpcs:enable

    public function api(): ?AuthenticatedAPI
    {
        return $this->api;
    }

    /**
     * Auth token for authenticated API calls
     *
     * @return TokenData|null
     */
    public function getAuthToken(bool $forceNew = false): ?array
    {
        $settings = $this->get_plugin_settings();

        if (empty($settings) || !$this->api) {
            return null;
        }

        if (
            empty($settings['auth_token'])
            || $forceNew
        ) {
            $token = $this->api->getAccessToken(
                $settings['authorization_code'] ?? null
            );

            if ($token instanceof AccessToken) {
                $settings['auth_token'] = $token->jsonSerialize();
                $this->update_plugin_settings($settings);
            }
        }

        return $settings['auth_token'] ?? null;
    }

    /**
     * Add-on loader.
     */
    public static function load(): void
    {
        if (!method_exists('\\GFForms', 'include_feed_addon_framework')) {
            return;
        }

        GFFeedAddOn::register(static::class);
    }
}
