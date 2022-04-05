<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use P4\ControlShift\Signature;

/**
 * This page appears in the Form edition page, in the Settings tab
 *
 * @phpstan-import-type GFSection from ControlShiftAddOn
 */
class FeedSettings
{
    /**
     * @return array{GFSection}
     */
    public static function fields(ControlShiftAddOn $addOn): array
    {
        if (!$addOn->api()) {
            return [[
                'title' => 'Error using ControlShift API',
                'description' => 'ControlShift API is not available or not properly configured. '
                    . 'Please go to <em>Forms > Settings > ControlShift</em> to configure this plugin.',
                'fields' => [[
                    'name' => 'error_message',
                    'type' => '',
                    'callback' => function (): void {
                        return;
                    },
                ]],
            ]];
        }

        return [
            self::petition($addOn),
            self::mapFields($addOn),
        ];
    }

    /**
     * @return GFSection
     */
    private static function petition(ControlShiftAddOn $addOn): array
    {
        return [
            'title' => 'Petitions',
            'description' => 'Select the petition that will receive this form data:',
            'fields' => [
                [
                    'name' => 'petitions_list',
                    'type' => '',
                    'callback' => fn() => self::petitionsList($addOn),
                ],
            ],
        ];
    }

    /**
     * @return GFSection
     */
    private static function mapFields(ControlShiftAddOn $addOn): array
    {
        return [
            'title' => 'Map fields',
            'description' => 'Map ControlShift fields to this form fields.<br/>'
                . 'ControlShift has a fixed set of fields listed below, '
                . 'for each one you can select one of the current form fields or leave it empty.',
            'fields' => [
                [
                    'name' => 'map_fields',
                    'type' => '',
                    'callback' => fn() => self::signatureFields($addOn),
                ],
            ],
        ];
    }

    private static function signatureFields(ControlShiftAddOn $addOn): void
    {
        $controlshift_fields = Signature::$fields;
        $form = $addOn->get_current_form();
        if (!is_array($form)) {
            return;
        }

        $form_fields = $addOn->get_form_fields_as_choices($form);

        $choices = array_merge(
            [['value' => '', 'label' => '']],
            $form_fields
        );

        foreach ($controlshift_fields as $name => $cs_field) {
            echo '<p>' . ($cs_field['label'] ?? $name) . '</p>';
            $addOn->settings_select([
                'label' => $name,
                'name' => $name,
                'choices' => $choices,
            ]);
        }
    }

    private static function petitionsList(ControlShiftAddOn $addOn): void
    {
        if (!$addOn->api()) {
            return;
        }

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
