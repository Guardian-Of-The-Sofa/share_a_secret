<?php

return [
    'ctrl' => [
        'title' => 'Secrets',
        'label' => 'message',
    ],

    'columns' => [
        'message' => [
            'label' => 'Message',
            'config' => [
                'type' => 'text',
                'eval' => 'trim,required',
            ],
        ],

        'index_hash' => [
            'label' => 'Index hash',
            'exclude' => true,
            'config' => [
                'type' => 'passthrough',
                'size' => 50,
            ],
        ],

        'attempt' => [
            'label' => 'Attempt',
            'config' => [
                'type' => 'passthrough',
                'default' => 0,
            ]
        ],

        'last_attempt' => [
            'label' => 'Last attempt',
            'config' => [
                'type' => 'passthrough',
                'default' => 0,
            ],
        ]
    ],

    'types' => [
        '0' => ['showitem' => 'message,password_hash,link_hash'],
    ],
];