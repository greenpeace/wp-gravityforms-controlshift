<?php
/*
Plugin Name: Gravity Forms ControlShift Add-On
Plugin URI: https://gravityforms.com
Description: Integrates Gravity Forms with ControlShift, allowing form submissions to be automatically sent to ControlShift.
Version: 1.0
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-3.0+
Text Domain: planet4_controlshift
*/

declare(strict_types=1);

use P4\ControlShift\GravityForms\ControlShiftAddOn;
use P4\ControlShift\ControlShiftAPI;

require __DIR__
	. DIRECTORY_SEPARATOR . 'vendor'
	. DIRECTORY_SEPARATOR . 'autoload.php';

define( 'GF_CONTROLSHIFT_VERSION', '1.0' );
define( 'GF_CONTROLSHIFT_MIN_GF_VERSION', '2.2' );

add_action(
	'gform_loaded',
	function () {
		GFForms::include_feed_addon_framework();
		require_once('src/GravityForms/ControlShiftAddOn.php');
		ControlShiftAddOn::load();
	},
	5
);

function gf_controlshift(): ControlShiftAddOn {
	return ControlShiftAddOn::get_instance();
}

function controlshift_api(): ControlShiftAPI {
	return ControlShiftAPI::getInstance();
}
