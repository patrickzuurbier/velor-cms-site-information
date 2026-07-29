<?php

declare(strict_types=1);

return [
    'site-information-subjects' => [
        'singular' => 'Site information subject',
        'plural'   => 'Site information',
        'fields'   => [
            'collapsed' => 'Collapsed by default',
            'key'       => 'Key',
            'name'      => 'Name',
            'order'     => 'Order',
            'parent'    => 'Parent',
        ],
        'tabs' => [
            'fields' => 'Fields',
        ],
    ],
    'site-information' => [
        'singular' => 'Site information field',
        'plural'   => 'Site information fields',
        'fields'   => [
            'key'     => 'Key',
            'label'   => 'Label',
            'order'   => 'Order',
            'subject' => 'Subject',
            'type'    => 'Type',
            'value'   => 'Value',
        ],
        'tabs' => [
            'subject' => 'Subject',
        ],
        'help' => [
            'svg' => 'Paste SVG markup here. It is saved as :filename in the media bucket.',
        ],
    ],
];
