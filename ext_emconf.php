<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Share a secret',
    'description' => 'An extension to share secrets.',
    'category' => 'plugin',
    'author' => 'Jens Pausewang',
    'author_company' => 'hauptsache.net',
    'author_email' => 'jens@hauptsache.net',
    'state' => 'alpha',
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.27-9.5.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Hn\\ShareASecret\\' => 'Classes'
        ],
    ],
];
