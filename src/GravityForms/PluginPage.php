<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use GFFeedAddOn;

/**
 * This page appears in the Forms menu
 */
class PluginPage
{
	public static function render(GFFeedAddOn $addOn)
	{
		$token = $addOn->get_auth_token();
		$api = $addOn->api();
		if (!$api || !$token) {
			echo '<p>Go to plugin settings.</p>';
			return;
		}

		echo '<p>This page appears in the Forms menu</p>';
		echo '<pre>', print_r($addOn->get_plugin_settings(), true), '</pre>';

		echo '<pre>', print_r($token, true), '</pre>';

		if ($token) {
			var_dump($api->email_opt_in_types());

			$petitions = $api->petitions();
			echo '<ul>';
			foreach ($petitions as $petition) {
				echo sprintf('<li><a href="%s">%s</a> (%d/%d)</li>',
					$petition['url'],
					$petition['title'],
					$petition['public_signature_count'] ?? 0,
					$petition['goal'] ?? 0,
				);
			}
			echo '</ul>';

			$events = $api->events();
			echo '<ul>';
			foreach ($events as $event) {
				echo sprintf('<li><a href="%s">%s</a></li>',
					$event['url'],
					$event['title'],
				);
			}
			echo '</ul>';
		}
	}
}
