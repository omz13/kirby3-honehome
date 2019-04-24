<?php

Kirby::plugin(
    'omz13/honehome',
    [
      'root'        => dirname( __FILE__, 2 ),

      'options'     => [
        'disable'     => false,
        'homelanding' => '',
      ],

      'blueprints'  => [
        'honehome/homeoverride' => dirname( __FILE__, 2 ) . '/blueprints/homeoverride.yml',
      ],


      'routes'      => [
        [
          'pattern' => '',
          'action'  => function () {
            return omz13\k3honehome\honehome();
          },
        ],
      ],

      'pageMethods' => [
        'honehomeLang' => function ( string $d = 'en' ) {
          if ( kirby()->multilang() ) {
            return omz13\k3honehome\localeToLangCode( omz13\k3honehome\localeFromLang() );
          } else {
            return $d;
          }
        },
      ],

    ]
);

include_once __DIR__ . "/honehome.php";
