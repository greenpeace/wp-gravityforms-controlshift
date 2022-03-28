<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use P4\ControlShift\Signature;
use GF_Field_Name;
use GFCommon;
use GFFeedAddOn;

/**
 * This page appears in the Form edition page, in the Settings tab
 */
class FeedSettings
{
	public static function fields(GFFeedAddOn $addOn)
	{
		return [
			self::petition($addOn),
			self::map_fields($addOn),
		];
	}

	private static function petition($addOn) {
		return [
			'title' => 'Petitions',
			'description' => 'Select the petition associated with this form.',
			'fields' => [
				[
					'name' => 'petitions_list',
					'type' => '',
					'callback' => fn() => self::petitions_list($addOn),
				],
			],
		];
	}

	private static function map_fields($addOn) {
		return [
			'title' => 'Map fields',
			'description' => 'Map ControlShift fields to this form fields.<br/>'
				. 'ControlShift has a fixed set of fields listed below, for each one you can select one of the current form fields or leave it empty.',
			'fields' => [
				[
					'name' => 'map_fields',
					'type' => '',
					'callback' => fn() => self::signature_fields($addOn),
				],
			],
		];
	}

	private static function signature_fields($addOn) {
		$controlshift_fields = Signature::$fields;
		$form_fields = $addOn->get_form_fields_as_choices($addOn->get_current_form()) ?? [];

		$choices = array_merge(
			[['value' => '', 'label' => '']],
			$form_fields
		);

		foreach($controlshift_fields as $name => $cs_field) {
			echo '<p>' . ($cs_field['label'] ?? $name) . '</p>';
			$addOn->settings_select([
				'label' => $name,
				'name' => $name,
				'choices' => $choices,
			]);
		}
	}

	private static function petitions_list($addOn)
	{
		$petitions = $addOn->api()->petitions();
		if (empty($petitions)) {
			echo '<p>No petition found.</p>';
			return;
		}

		usort($petitions, fn($a, $b) => $a['title'] <=> $b['title']);

		$addOn->settings_select([
			'label' => 'Petition',
			'name' => 'petition_slug',
			'choices' => array_map(
				fn($p) => ['value' => $p['slug'], 'label' => $p['title']],
				$petitions
			),
		]);
	}
}
