<?php

$EM_CONF[$_EXTKEY] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'plugin',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'conflicts' => [],
        'depends'   => [
            'contact_book' => '0.1',
            'foundation'   => '4.0',
            'php'          => '8.3.0-8.5.99',
            'typo3'        => '13.4.0-14.99.99',
        ],
        'suggests'  => [],
    ],
    'description'      => 'Keep a record of all the gifts you have received or given.',
    'state'            => 'alpha',
    'title'            => 'PSBits | Gift Log',
    'version'          => '0.1.0',
];
