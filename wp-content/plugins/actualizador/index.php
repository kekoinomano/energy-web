<?php

/**

 * Plugin Name: Actualizador

 * Description: Actualiza el stock de woocomerce.

 * Version: 1.0

 * Author: Gaspar Álvarez


 */
function actualizar_admin_menu(){
		add_menu_page('Forms', 'Actualizar Stock', 'manage_options', 'actualizar_admin_menu', 'actualizar_admin_menu_main', 'dashicons-update', 1);
}


add_action('admin_menu', 'actualizar_admin_menu');
//require_once "../wp-content/plugins/prueba/funciones/Classes/PHPExcel.php";



function actualizar_admin_menu_main(){


	echo "<div style='background: white; color:black;border: 2px solid gray;border-radius: 20px;padding: 20px;font-size: 17px;margin: 20px auto; width:90%;box-shadow: 0 4px 4px rgb(0 0 0 / 7%);'>";
	echo 'Sube el archivo excel (.xlsx) que tenga el siguiente formato:';
	echo '<table border=1 style="margin-top:30px;"><tr style="color:black;font-style: oblique;font-weight: bold;">';
	echo '<td>...</td><td>SKU</td><td>...</td><td>...</td><td>STOCK</td><td>...</td><td>...</td><td>...</td>';
	echo '</tr></table>';

	echo '<br>';
	echo '<form style="margin-top:30px;" action="../wp-content/plugins/actualizador/funciones/subirexcel.php" method="post" enctype="multipart/form-data">
  Selecciona el archivo:
  <input type="file" name="fileToUpload" id="fileToUpload">
  <input type="submit" value="Actualizar" name="submit">
</form></div>';



echo "<div style='text-align:center;font-size:15px;'><div>Casos Especiales</div>";

echo '<form style="margin-top:30px;" action="../wp-content/plugins/actualizador/funciones/anadircaso.php" method="post" enctype="multipart/form-data">
  SKU para añadir:
  <input type="text" name="sku" id="sku">
  <input type="submit" value="Añadir" name="submit">
</form>';
//$actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo '<form style="margin:30px auto;" action="../wp-content/plugins/actualizador/funciones/borrarcaso.php" method="post" enctype="multipart/form-data">
  SKU para borrar:
  <input type="text" name="sku" id="sku">
  <input type="submit" value="Borrar" name="submit">
</form>';
	global $wpdb;
	if ($result = $wpdb->query("SHOW TABLES LIKE 'wp_actualizador'")) {
		$casos = $wpdb->get_results( $wpdb->prepare( "SELECT sku FROM wp_actualizador") );

        if ( ! empty( $casos ) ) {
          foreach ( $casos as $caso ) {
            echo $caso->sku . "<br>";
          }
      }
	}
	else {
    	echo "Table does not exist: wp_actionscheduler_actions";
    	$sql = "CREATE TABLE wp_actualizador (
				id_especial INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				sku VARCHAR(30) NOT NULL)";

		if ($wpdb->query($sql) === TRUE) {
		  //echo "Table MyGuests created successfully";
		} else {
		  //echo "Error creating table: " . $wpdb->error;
		}
	}
	echo "</div>";

}


?>


