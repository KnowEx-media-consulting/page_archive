<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Page Archive',
    'description' => 'Archive for pages: hides old pages from the backend page tree without deleting them and restores them with one click.',
    'category' => 'be',
    'author' => 'Vitali Scharov',
    'author_email' => 'scharov@knowex.de',
    'author_company' => 'KnowEx media-consulting GmbH',
    'state' => 'beta',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.3.0-8.4.99',
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
