<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use GFFeedAddOn;
use League\OAuth2\Client\Token\AccessToken;

/**
 * This page appears in the Forms > Settings menu, as a tab on the left
 */
class PluginSettings
{
	public static function fields(GFFeedAddOn $addOn)
	{
		$fields = [
			self::app_settings_section(),
			self::auth_code_section($addOn),
		];

		if (!empty($addOn->api())) {
			$fields[] = [
				'title'       => 'Access Token',
				'fields' => [
					[
						'name' => 'auth_token',
						'type' => '',
						'callback' => fn() => self::auth_token($addOn->get_auth_token()),
					],
				],
			];
		}

		return $fields;
	}

	private static function app_settings_section() {
		return [
			'title'       => 'ControlShift Application Settings',
			'description' => 'Create a new Application in your ControlShift instance administration, in Settings > Integrations > REST API Apps, then fill these fields.',
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

	private static function auth_code_section($addOn) {
		return [
			'title'       => 'Authorization Code',
			'description' => 'Enter Authorization code to allow this plugin to interact with your ControlShift instance.',
			'fields' => [
				[
					'name' => 'authorization_code',
					'type' => '',
					'callback' => fn() => self::authorization_code($addOn),
				],
			],
		];
	}

	private static function authorization_code($addOn) {
		$addOn->settings_text([
			'label' => 'Authorization code',
			'name' => 'authorization_code',
			'type' => 'text',
			'tooltip' => 'You can obtain the authorization code by clicking on "Authorize" in your ControlShift application configuration.',
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

	private static function auth_token($token = null) {
		if (empty($token) || !($token instanceof AccessToken)) {
			echo '<p><em>No valid access token found.</em></p>';
			return;
		}

		echo '<p>';
        echo 'Access Token: ' . $token->getToken() . "<br/>";
        echo 'Refresh Token: ' . $token->getRefreshToken() . "<br/>";
        echo 'Created at: ' . date('d/m/Y H:i:s', $token->getValues()['created_at']) . "<br/>";
        echo 'Expires at: ' . date('d/m/Y H:i:s', $token->getExpires()) . "<br/>";
        echo 'Expired: ' . ($token->hasExpired() ? 'true' : 'false') . "<br/>";
		echo 'Additional infos: <pre>', print_r($token->getValues(), true), '</pre>';
		echo 'Token object: <pre>', print_r($token, true), '</pre>';
		echo '</p>';
	}
}
