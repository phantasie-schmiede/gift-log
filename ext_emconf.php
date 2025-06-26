<?php

$EM_CONF[$EXTKEY] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'plugin',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'conflicts' => [],
        'depends'   => [
            'contact_book' => '0.1',
            'foundation'   => '3.0',
            'php'          => '8.3',
            'typo3'        => '13.4.0-13.4.99',
        ],
        'suggests'  => [],
    ],
    'description'      => 'Keep a record of all the gifts you have received or given.',
    'state'            => 'alpha',
    'title'            => 'PSBits | Gift Log',
    'version'          => '0.1.0',
];
