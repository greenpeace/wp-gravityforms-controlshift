<?php

declare(strict_types=1);

namespace P4\ControlShift\GravityForms;

/**
 * This page appears in the Form edition page, in the Settings tab
 *
 * @phpstan-import-type GFSection from ControlShiftAddOn
 */
class FormSettings
{
    /**
     * @return array{GFSection}
     */
    public static function fields(ControlShiftAddOn $addOn): array
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

    private static function petitionsList(ControlShiftAddOn $addOn): void
    {
        if (!$addOn->api()) {
            return;
        }

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
                $petition['public_signature_count'],
                $petition['goal'],
            );
        }
        echo '</ul>';
    }
}
