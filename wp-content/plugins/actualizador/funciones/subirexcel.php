<?php

//$especiales=['1008010','1008011'];





//---------------Requisitos previos--------------------
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


//--------------Comprueba si eres un usuario válido--------------
if (!current_user_can('administrator')){
  echo "No eres administrador";
}
else{

//--------------Recogiendo el archivo y creando la tabla------------
$target_dir = "../archivo/";
$nombre = basename($_FILES["fileToUpload"]["name"]);
$target_file = $target_dir . $nombre;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if(!isset($_POST["submit"]) || empty($_POST["submit"])) {
  echo "Archivo vacío";
}

// Check if file already exists
if (file_exists($target_file)) {
  unlink($target_file);
  echo "Se actualizó el anterior<br>";
}



// Allow certain file formats
if($imageFileType != "xlsx") {
  echo "Sorry, only xlsx files are allowed.<br>";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {



  //----------------Si todo ha salido bien, crea la tabla----------------------

  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    $path="../archivo/" . $nombre;

    echo "El archivo ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " se subió con éxtio.";
    echo "<br><br><br>";
    $verproductos="/wp-admin/edit.php?post_type=product";
    echo "<a href='".$verproductos."' style='font-size:20px;margin-left:15px;'>Ver productos actualizados</a>";
    echo "<br><br><br>";
    echo '<h1>'. $nombre.'</h1>';
    if ( $xlsx = SimpleXLSX::parse($path) ) {

      $dim = $xlsx->dimension();
      $num_cols = $dim[0];
      $num_rows = $dim[1];
      echo '<h2>'.$xlsx->sheetName( 0 ).'</h2>';
  echo '<table border=1>';

  foreach ( $xlsx->rows( 0 ) as $r ) {
    echo '<tr>';
    
    for ( $i = 0; $i < $num_cols; $i ++ ) {
      echo '<td>' . ( ! empty( $r[ $i ] ) ? $r[ $i ] : '&nbsp;' ) . '</td>';

    }
    echo '</tr>';
  }


/*--------------------------------------------------------------------------
----------------------------IMPORTANTE---------------------------------------
En el excel, la segunda columna corresponde con el sku y la quinta con el stock
Es importante mantener el formato.
---------------------------------------------------------------------------------

1. En cada fila selecciona la id del producto que coincida con la sku del excel
2. Si la id existe, actualiza el stock y el status con la cantidad que ponga en el excel
3. Comprueba si la sku del excel coincide con algún caso especial
    3.1. Si coincide, encuentra la id del sku + "-a" y la actualiza con stock y status del excel 
    3.2. Encuentra la id del sku + "-b" y la actualiza con stock*4


---------------------------------------------------------------------------*/
$especiales=array();
$getespeciales = $wpdb->get_results( $wpdb->prepare( "SELECT sku FROM wp_actualizador") );

if ( ! empty( $getespeciales ) ) {
  foreach ( $getespeciales as $especial ) {
    $especiales[]=$especial->sku;
  }
}
//echo "Especiales: " . $especiales[0];

  foreach ( $xlsx->rows( 0 ) as $r ) {
    $cantidad=floatval($r[4])-floatval($r[5]);
    if($cantidad<0){
      $cantidad=0;
    }
    $sku=$r[1];
    $status="instock";
    $id_producto=0;
    if($cantidad==0){
      $status="outofstock";
    }

        $productos = $wpdb->get_results( $wpdb->prepare( "SELECT product_id FROM wp_wc_product_meta_lookup WHERE sku = %s", $sku) );

        if ( ! empty( $productos ) ) {
          foreach ( $productos as $producto ) {
            $id_producto=$producto->product_id;
          }
          $wpdb->query( $wpdb->prepare( "UPDATE wp_wc_product_meta_lookup SET stock_quantity = %s, stock_status = %s WHERE sku = %s AND product_id = %s", $cantidad, $status, $sku, $id_producto) );
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %s AND meta_key = '_stock_status'", $status, $id_producto) );
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %s AND meta_key = '_stock'", $cantidad, $id_producto) );

        for($j=0;$j<count($especiales);$j++){
          if($sku==$especiales[$j]){
            $skua=$sku . "-a";
        $nuevosproductos = $wpdb->get_results( $wpdb->prepare( "SELECT product_id FROM wp_wc_product_meta_lookup WHERE sku = %s", $skua) );

        if ( ! empty( $nuevosproductos ) ) {
          foreach ( $nuevosproductos as $nuevosproducto ) {
            $id_producto=$nuevosproducto->product_id;
          }

        //Status
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %s AND meta_key = '_stock_status'", $status, $id_producto) );
        //Cantidad
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %s AND meta_key = '_stock'", $cantidad, $id_producto) );
        //Manage stock
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = 'yes' WHERE post_id = %s AND meta_key = '_manage_stock'", $id_producto) );

        $wpdb->query( $wpdb->prepare( "UPDATE wp_wc_product_meta_lookup SET stock_quantity = %s, stock_status = %s WHERE sku = %s AND product_id = %s", $cantidad, $status, $skua, $id_producto) );
        }
        $skub=$sku . "-b";
        $cantidad=floatval($cantidad)*4;
        $nuevosproductos = $wpdb->get_results( $wpdb->prepare( "SELECT product_id FROM wp_wc_product_meta_lookup WHERE sku = %s", $skub) );

        if ( ! empty( $nuevosproductos ) ) {
          foreach ( $nuevosproductos as $nuevosproducto ) {
            $id_producto=$nuevosproducto->product_id;
          }
        //Status
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %s AND meta_key = '_stock_status'", $status, $id_producto) );
        //Cantidad
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = %s WHERE post_id = %s AND meta_key = '_stock'", $cantidad, $id_producto) );
        //Manage stock
        $wpdb->query( $wpdb->prepare( "UPDATE wp_postmeta SET meta_value = 'yes' WHERE post_id = %s AND meta_key = '_manage_stock'", $id_producto) );

        $wpdb->query( $wpdb->prepare( "UPDATE wp_wc_product_meta_lookup SET stock_quantity = %s, stock_status = %s WHERE sku = %s AND product_id = %s", $cantidad, $status, $skub, $id_producto) );
        }
          }
        }

        }
  }

  
    } else {
      echo SimpleXLSX::parseError();
    }



  } else {
    echo "Sorry, there was an error uploading your file.";
  }
}
}
?>