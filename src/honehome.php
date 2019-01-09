<?php

namespace omz13\k3honehome;

use Kirby\Cms\Page;

use const HONEHOME_CONFIGURATION_PREFIX;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_push;
use function array_reduce;
use function arsort;
use function assert;
use function count;
use function define;
use function explode;
use function header;
use function json_encode;
use function kirby;
use function stripos;
use function strlen;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;

define( 'HONEHOME_VERSION', '0.1.0' );
define( 'HONEHOME_CONFIGURATION_PREFIX', 'omz13.honehome' );

/*
 * Convert a PHP locale code to an HTML language code (and strip any pesky .utf8 or .utf-8 affix)
*/
function localeToLangCode( string $locale ) : string {
  // Normalize
  $locale = strtolower( $locale );
  // Rtrim .utf-8 from $locale
  $l = strlen( $locale ) - strlen( ".utf-8" );
  if ( $l > 0 && stripos( $locale, ".utf-8", $l ) !== false ) {
    $locale = substr( $locale, 0, -strlen( ".utf-8" ) );
  } else {
    // Rtrim .utf8 from $locale
    $l = strlen( $locale ) - strlen( ".utf8" );
    if ( $l > 0 && stripos( $locale, ".utf8", $l ) !== false ) {
      $locale = substr( $locale, 0, -strlen( ".utf8" ) );
    }
  }
  // Huzzah! $locale is now sanitized from that pesky .utf suffix

  $x = explode( '_', $locale );

  if ( count( $x ) == 1 ) {
    return $x[0];
  }
  return $x[0] . '-' . strtoupper( $x [1] );
}//end localeToLangCode()

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
//phpcs:disable Generic.Metrics.CyclomaticComplexity
function honehome() : Page {
  $debug = kirby()->option( 'debug' );

  $o = kirby()->option( HONEHOME_CONFIGURATION_PREFIX . '.disable' );
  if ( $o != null && $o == 'true' ) {
    if ( $debug == true ) {
      header( "X-omz13-hh: DISABLED" );
    }
    return kirby()->site()->homePage();
  }

  $home = ""; // reset

  $n = kirby()->option( HONEHOME_CONFIGURATION_PREFIX . '.homelanding' );
  if ( $n != null && $n != '' ) {
    if ( $debug == true ) {
      header( "X-omz13-hh-from-c: " . $n );
    }
    $home = kirby()->site()->find( $n );
    assert( $home != null ); // throw configuration error
  }

  if ( $home == "" ) {
    $n = kirby()->site()->content()->get( 'homelanding' )->toString();
    if ( $n != "" ) {
      if ( $debug == true ) {
        header( "X-omz13-hh-from-f: " . $n );
      }
      $home = kirby()->site()->find( $n );
      assert( $home != null );
    } else {
      $home = kirby()->site()->homePage();
    }
  }

  if ( $debug == true ) {
    header( "X-omz13-hh-landing:" . ( $n == "" ? 'homePage' : $n ) );
    header( "X-omz13-hh-multilang:" . ( kirby()->multilang() == false ? 's' : 'm' ) );
  }

  // Guard: if not multilanguage, all that's needed is the "homepage" now
  if ( kirby()->multilang() == false ) {
    return $home;
  }

  // Never say never
  assert( $_SERVER['REQUEST_URI'] == '/' );

  if ( $debug == true ) {
    header( "X-omz13-hh-Host:" . $_SERVER['HTTP_HOST'] );
  }

  // Guard: If request is to a langaue's configured url, already home, so just give it
  foreach ( kirby()->languages() as $lang ) {
    if ( $lang->url() ) {
      $want = $lang->url();

      $pos = strpos( $lang->url(), "//" );
      if ( $pos !== false ) {
        $want = substr( $lang->url(), $pos + 2 );
      }

      if ( $_SERVER['HTTP_HOST'] == $want ) {
        if ( $debug == true ) {
          header( "X-omz13-hh-Already:" . $lang->code() );
        }
        return $home;
      }
    }//end if
  }//end foreach

  if ( array_key_exists( 'HTTP_ACCEPT_LANGUAGE', $_SERVER ) ) {
  // en-US,en;q=0.8,en-GB;q=0.6,de;q=0.4,ja;q=0.2

    $prefLocales = array_reduce(
        explode( ',', strtolower( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ),
        function ( $res, $el ) {
          [$l, $q] = array_merge( explode( ';q=', $el ), [1] );
          $res[$l] = (float) $q;
          return $res;
        },
        []
    );
    arsort( $prefLocales );
    $wants = array_keys( $prefLocales );

    // A bit of OTT debug
    if ( $debug == true ) {
      header( "X-omz13-hh-Accept:" . json_encode( $wants ) );

      $a = [];
      foreach ( kirby()->languages() as $lang ) {
        array_push( $a, $lang->locale() );
      }
      header( "X-omz13-hh-HasLocales:" . json_encode( $a ) );
    }

    // Exact match
    foreach ( $wants as $want ) {
      foreach ( kirby()->languages() as $lang ) {
        $whatever = strtolower( localeToLangCode( $lang->locale() ) );
        // header( "X-omz13-hh-match-try-e-" . $i++ . ":" . $want . " with " . $whatever );
        if ( $want == $whatever ) {
          if ( $debug == true ) {
            header( 'X-omz13-hh-Match:EXACT ' . $want . ' to (' . $lang->code() . ') ' . $lang->locale() );
          }
          return kirby()->site()->visit( $home, $lang->code() );
//          kirby()->setCurrentLanguage( $lang->code() );
//          return $home;
//        go( $home->urlForLanguage( $lang->code() ), 302 );
//        return kirby()->site()->visit( $home, $lang->code() );
        }
      }
    }//end foreach

    // Best match if ignore any regional in request
    foreach ( $wants as $want ) {
      $wantwant = explode( '-', (string) $want );
      $want     = $wantwant[0];
      foreach ( kirby()->languages() as $lang ) {
        $whatever = strtolower( localeToLangCode( $lang->locale() ) );
        // header( "X-omz13-hh-match-try-b-" . $i++ . ":" . $want . " with " . $whatever );
        if ( $want == $whatever ) {
          if ( $debug == true ) {
            header( 'X-omz13-hh-Match:BEST ' . $want . ' to (' . $lang->code() . ') ' . $lang->locale() );
          }
          return kirby()->site()->visit( $home, $lang->code() );
//        go( $home->urlForLanguage( $lang->code() ), 302 );
        }
      }
    }//end foreach

    // Near match if ignore regional in both request and what's available
    foreach ( $wants as $want ) {
      $wantwant  = explode( '-', (string) $want );
      $shortwant = $wantwant[0];
      foreach ( kirby()->languages() as $lang ) {
        $whatever = explode( '-', localeToLangCode( $lang->locale() ) )[0];
        // header( "X-omz13-hh-match-try-n-" . $i++ . ":" . $shortwant . " with " . $whatever );
        if ( $shortwant == $whatever ) {
          if ( $debug == true ) {
            header( 'X-omz13-hh-Match:NEAR ' . $want . ' to (' . $lang->code() . ') ' . $lang->locale() );
          }
          return kirby()->site()->visit( $home, $lang->code() );
//        go( $home->urlForLanguage( $lang->code() ), 302 );
        }
      }
    }//end foreach

    // Fall through...
    if ( $debug == true ) {
      header( "X-omz13-hh-Match:FallThru" );
    }
  } else {
    if ( $debug == true ) {
      header( 'X-omz13-hh-Accept:MISSING' );
    }
  }//end if

  // If no preference requested, or nothing matched the preferene, just use the default language for the homepage
  if ( $debug == true ) {
    header( 'X-omz13-hh-DefaultTo:(' . kirby()->language()->code() . ') ' . kirby()->language()->locale() );
  }
  return kirby()->site()->visit( $home, kirby()->language()->code() );
//  kirby()->setCurrentLanguage( $lang->code() );
//  return $home;
//  go( $home->urlForLanguage( kirby()->language()->code() ), 302 );
}//end honehome()
