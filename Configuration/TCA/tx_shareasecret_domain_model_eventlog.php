<?php

return [
    'ctrl' => [
        'title' => 'Eventlog',
        'label' => 'date',
        'crdate' => 'date',
    ],

    'columns' => [
        'secret' => [
            'exclude' => true,
            'label' => 'Secret ID',
            'config' => [
                'type' => 'input',
                'readOnly' => 'true',
            ],
        ],

        'date' => [
            'exclude' => true,
            'label' => 'Date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'readOnly' => 'true',
                'eval' => 'datetime',
            ],
        ],

        'event' => [
            'exclude' => true,
            'label' => 'Event',
            'config' => [
                'type' => 'input',
                'readOnly' => 'true',
            ]
        ],

        'message' => [
            'exclude' => true,
            'label' => 'Message',
            'config' => [
                'type' => 'input',
                'readOnly' => 'true',
            ]
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => 'secret, date, event, message',
        ]
    ],
];

