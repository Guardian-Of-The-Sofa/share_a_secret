<?php

return [
    'ctrl' => [
        'title' => 'Secrets',
        'label' => 'message',
        'crdate' => 'crdate',
    ],

    'columns' => [
        'crdate' => [
            'label' => 'Creation date',
            'config' => [
                'type' => 'input',
                'readOnly' => 'true',
            ],
        ],

        'message' => [
            'label' => 'Message',
            'config' => [
                'type' => 'text',
                'readOnly' => 'true',
            ],
        ],

        'index_hash' => [
            'label' => 'Index hash',
            'exclude' => true,
            'config' => [
                'type' => 'text',
                'readOnly' => 'true',
            ],
        ],
    ],

    'types' => [
        '0' => ['showitem' => 'message,index_hash'],
    ],
];