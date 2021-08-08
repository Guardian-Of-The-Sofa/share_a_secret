<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Share a secret',
    'description' => 'An extension to share secrets.',
    'category' => 'plugin',
    'author' => 'Jens Pausewang',
    'author_company' => 'hauptsache.net',
    'author_email' => 'jens@hauptsache.net',
    'state' => 'alpha',
    'version' => '1.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.27-10.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Hn\\ShareASecret\\' => 'Classes'
        ],
    ],
];
