<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Share Secret',
    'description' => 'An extension to share secrets.',
    'category' => 'plugin',
    'author' => 'Jens Pausewang',
    'author_company' => 'hauptsache.net',
    'author_email' => 'jens@hauptsache.net',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Hn\\HnShareSecret\\' => 'Classes'
        ],
    ],
];
