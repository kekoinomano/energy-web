<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
function find_wordpress_base_path() {
  $dir = dirname( __FILE__ );
  do {
    if ( file_exists( $dir . "/wp-load.php" ) ) {
      return $dir;
    }
    if ( file_exists( $dir . "/wp" ) ) {
      return $dir . "/wp";
    }
  } while ( $dir = realpath( "$dir/.." ) );

  return null;
}

define( 'BASE_PATH', find_wordpress_base_path() . "/" );
require_once __DIR__.'/simplexlsx/src/SimpleXLSX.php';
require_once( BASE_PATH . 'wp-load.php' );

if(isset($_POST['sku'])){
	$sku=$_POST['sku'];

  $skus_especiales = $wpdb->get_results( $wpdb->prepare( "SELECT sku FROM wp_actualizador WHERE sku = %s", $sku) );

  if (empty( $skus_especiales ) ) {
	$wpdb->query( $wpdb->prepare( "INSERT INTO wp_actualizador (sku) VALUES (%s)", $sku) );
  }
	echo "Se actualizó con éxito";
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
?>