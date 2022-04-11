<?php
//require_once "../../wp-content/plugins/prueba/funciones/Classes/PHPExcel.php";
//require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
//require_once('Classes/PHPExcel.php');
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

$target_dir = "../archivo/";
$nombre = basename($_FILES["fileToUpload"]["name"]);
$target_file = $target_dir . $nombre;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if(!isset($_POST["submit"])) {
  echo "Archivo vacío";
}

// Check if file already exists
if (file_exists($target_file)) {
  unlink($target_file);
  echo "Se actualizó el anterior<br>";
}



// Allow certain file formats
if($imageFileType != "xlsx" && $imageFileType != "csv") {
  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    $path="../archivo/" . $nombre;
    //subir_memes($path);
    echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
    echo "<br>";
    echo '<h1>'. $nombre.'</h1><pre>';
    if ( $xlsx = SimpleXLSX::parse($path) ) {

      $dim = $xlsx->dimension();
      $num_cols = $dim[0];
      $num_rows = $dim[1];
      echo '<h2>'.$xlsx->sheetName( 0 ).'</h2>';
  echo '<table border=1>';
  foreach ( $xlsx->rows( 0 ) as $r ) {
    echo '<tr>';
    $cantidad=$r[4];
    $sku=$r[2];
    if($sku!="Mediterranean"){
      $wpdb->query( $wpdb->prepare( "UPDATE wp_wc_product_meta_lookup SET stock_quantity = %s WHERE sku = %s", $cantidad, $sku) );
    }else{
      $wpdb->query( $wpdb->prepare( "UPDATE wp_wc_product_meta_lookup SET stock_quantity = %s WHERE sku = 'Mediterranean'", $cantidad) );
      $wpdb->query( $wpdb->prepare( "UPDATE wp_wc_product_meta_lookup SET stock_quantity = %s WHERE sku = 'Mediterranean-b'", $cantidad*0.25) );
    }
    
    for ( $i = 0; $i < $num_cols; $i ++ ) {
      echo '<td>' . ( ! empty( $r[ $i ] ) ? $r[ $i ] : '&nbsp;' ) . '</td>';

    }
    echo '</tr>';
  }
  echo '</table>';

  
    } else {
      echo SimpleXLSX::parseError();
    }
    //$excel= json_decode($xlsx);
    echo '<pre>';
    global $wpdb;

    //select a slider and then duplicate it

    //$wpdb->query("UPDATE wp_wc_product_meta_lookup SET stock_quantity = 4 WHERE sku = 'identificador'");

    //header("Location: https://meme.wakeapp.org/meter");

  } else {
    echo "Sorry, there was an error uploading your file.";
  }
}
?>