<?php

Kirby::plugin(
    'omz13/honehome',
    [
      'root'    => dirname( __FILE__, 2 ),

      'options' => [
        'disable'     => false,
        'homelanding' => '',
      ],

      'routes'  => [
        [
          'pattern' => '',
          'action'  => function () {
            return omz13\k3honehome\honehome();
          },
        ],
      ],

    ]
);

include_once __DIR__ . "/honehome.php";
