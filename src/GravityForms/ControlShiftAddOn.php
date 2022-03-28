<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use GFForms;
use GFAddOn;
use GFFeedAddOn;

use P4\ControlShift\API\AuthenticatedAPI;
use P4\ControlShift\API\OAuthProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Remove GDPR Comments plugin options
 */
class ControlShiftAddOn extends GFFeedAddOn {
	protected $_version = '1.0';
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'controlshift';
	protected $_path = 'ControlShiftAddOn.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms ControlShift add-on';
	protected $_short_title = 'Controlshift';

	private $api;
	private $errors = [];

	/**
	 * @var object|null $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Add-on loader.
	 */
	public static function load(): void {
		if (!method_exists('GFForms', 'include_addon_framework')) {
			return;
		}

		\GFForms::include_addon_framework();
		\GFFeedAddOn::register(static::class);
	}

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();

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
			empty($settings['auth_token']) ? null : $settings['auth_token']
		);
	}

	//
	// Add-on pages
	//

	public function plugin_page() {
		PluginPage::render($this);
	}

	public function plugin_settings_fields() {
		return PluginSettings::fields($this);
	}

	public function form_settings_fields($form) {
		return FormSettings::fields($this, $form);
	}

	public function feed_settings_fields() {
		return FeedSettings::fields($this);
	}

	public function feed_list_columns() {
	    return array(
	        'petition_slug' => __( 'Petition', 'ControlShift add-on' ),
	    );
	}

	//
	// Process signatures
	//

	public function process_feed( $feed, $entry, $form ) {
		//var_dump($feed,$entry,$form,);
		//die;

		$petition_slug = $feed['meta']['petition_slug'] ?? null;
		if (!$petition_slug) {
			return;
		}

		$signature = [];

		unset($feed['meta']['petition_slug']);
		foreach ( $feed['meta'] as $key => $field_id ) {
			if (empty($field_id)) {
				continue;
			}

			$signature[$key] = $this->get_field_value($form, $entry, $field_id);
		}

		if (empty($signature)) {
			return;
		}

		$petitions = $this->api->petitions();
		$signature['email_opt_in_type_external_id'] = 'web_form';

		//var_dump('posting', $signature);
		$response = $this->api->post_signature($petition_slug, ['signature' => $signature]);

		if (!empty($response['errors'])) {
			echo '<pre>', print_r($response, true), '</pre>';
		}
	}

	//
	// Utils
	//

	public function api() {
		return $this->api;
	}

	/**
	 * Auth token for Authenticated API calls
	 */
	public function get_auth_token($force = false) {
		$settings = $this->get_plugin_settings();

		if (empty($settings)
			|| empty($settings['authorization_code'])
			|| !$this->api
		) {
			return null;
		}

		if (empty($settings['auth_token'])
			|| $settings['auth_token']->hasExpired()
			|| $force
		) {
			//try {
				$settings['auth_token'] = $this->api->getAccessToken(
					$settings['authorization_code'] ?? null
				);
				$this->update_plugin_settings($settings);
			//} catch (\Exception $e) {
			//	$this->errors[] = (string) $e;
			//}
		}

		return $settings['auth_token'] ?? null;
	}
}
