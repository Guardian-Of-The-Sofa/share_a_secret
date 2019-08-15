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

        'password_hash' => [
            'label' => 'Password',
            'exclude' => true,
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'password,saltedPassword,required',
            ],
        ],

        'link_hash' => [
            'label' => 'Link hash',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'required',
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