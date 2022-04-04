<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

use GFFeedAddOn;

/**
 * This page appears in the Form edition page, in the Settings tab
 */
class FormSettings
{
    public static function fields(GFFeedAddOn $addOn): array
    {
        return [
            [
                'title' => 'Petitions',
                'description' => 'All petitions.',
                'fields' => [
                    [
                        'name' => 'petitions_list',
                        'type' => '',
                        'callback' => fn() => self::petitionsList($addOn),
                    ],
                ],
            ],
        ];
    }

    private static function petitionsList(GFFeedAddOn $addOn): void
    {
        $petitions = $addOn->api()->petitions();
        echo '<ul>';
        $tpl = '<li>
			<a href="%s">%s</a> (%d/%d)
		</li>';
        foreach ($petitions as $petition) {
            echo sprintf(
                $tpl,
                $petition['url'],
                $petition['title'],
                $petition['public_signature_count'] ?? 0,
                $petition['goal'] ?? 0,
            );
        }
        echo '</ul>';
    }
}
