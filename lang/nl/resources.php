<?php

declare(strict_types=1);

return [
    'site-information-subjects' => [
        'singular' => 'Site-informatie onderwerp',
        'plural'   => 'Site-informatie',
        'fields'   => [
            'collapsed' => 'Standaard ingeklapt',
            'key'       => 'Sleutel',
            'name'      => 'Naam',
            'order'     => 'Volgorde',
            'parent'    => 'Bovenliggend onderwerp',
        ],
        'tabs' => [
            'children' => 'Onderwerpen',
            'fields'   => 'Velden',
        ],
    ],
    'site-information' => [
        'singular' => 'Site-informatie veld',
        'plural'   => 'Site-informatie velden',
        'fields'   => [
            'key'     => 'Sleutel',
            'label'   => 'Label',
            'order'   => 'Volgorde',
            'subject' => 'Onderwerp',
            'type'    => 'Type',
            'value'   => 'Waarde',
        ],
        'tabs' => [
            'subject' => 'Onderwerp',
        ],
        'help' => [
            'svg' => 'Plak hier SVG-code. Deze wordt opgeslagen als :filename in de media bucket.',
        ],
    ],
];
