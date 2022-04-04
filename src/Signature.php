<?php

declare(strict_types=1);

namespace P4\ControlShift;

class Signature
{
    public static array $fields = [
        'first_name' => [
            'required' => true,
            'type' => 'name',
            'label' => 'First name',
        ],
        'last_name' => [
            'required' => true,
            'type' => 'name',
            'label' => 'Last name',
        ],
        'email' => [
            'required' => true,
            'type' => 'email',
            'label' => 'Email',
        ],
        'postcode' => [
            'required' => false,
            'type' => 'postcode',
            'label' => 'Postal or Zip code',
        ],
        'phone_number' => [
            'required' => false,
            'type' => 'phone',
            'label' => 'Phone number',
        ],
        'locale' => [ // ISO-639-1 ISO-3166
            'required' => false,
            'default' => 'en',
            'label' => 'Locale',
            'type' => 'locale',
        ],
        'country' => [ // ISO-3166
            'required' => false,
            'label' => 'Country',
            'type' => 'country',
        ],
        'email_opt_in_type_external_id' => [
            'required' => false,
            'label' => 'Email opt-in',
            'type' => 'email_opt_in_type',
        ],
        'join_organisation' => [
            'required' => false,
            'type' => 'bool',
        ],
        'join_partnership' => [
            'required' => false,
            'type' => 'bool',
        ],
        'eu_data_processing_consent' => [
            'required' => false,
            'type' => 'bool',
        ],
        'consent_content_version_external_id' => [
            'required' => false,
            'type' => 'consent_content_version',
        ],
        'utm_source' => [
            'required' => false,
            'type' => 'text',
        ],
        'utm_campaign' => [
            'required' => false,
            'type' => 'text',
        ],
        'utm_content' => [
            'required' => false,
            'type' => 'text',
        ],
        'utm_medium' => [
            'required' => false,
            'type' => 'text',
        ],
        'utm_term' => [
            'required' => false,
            'type' => 'text',
        ],
    ];
}
