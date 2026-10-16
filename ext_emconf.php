<?php

$EM_CONF['oap_instance_example'] = [
    'title' => 'OAP Instance: Example',
    'description' => 'Blueprint instance extension for open-oap. Copy, rename and adapt to configure your own OAP installation.',
    'category' => 'misc',
    'author' => 'OpenOAP community',
    'author_email' => '',
    'state' => 'beta',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'open_oap' => '',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
