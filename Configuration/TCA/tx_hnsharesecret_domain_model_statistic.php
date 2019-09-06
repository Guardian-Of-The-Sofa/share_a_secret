<?php

return [
    'ctrl' => [
        'title' => 'Statistic',
        'label' => 'created',
        'crdate' => 'created',
    ],

    'columns' => [
        'secret' => [
            'exclude' => true,
            'label' => 'Secret id',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_hnsharesecret_domain_model_secret',
            ],
        ],

        'read' => [
            'exclude' => true,
            'label' => 'Date read',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'readOnly' => 'true',
                'eval' => 'datetime',
            ]
        ],

        'deleted' => [
            'exclude' => true,
            'label' => 'Date deleted',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'readOnly' => 'true',
                'eval' => 'datetime',
            ]
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => 'created, read, deleted',
        ]
    ],
];