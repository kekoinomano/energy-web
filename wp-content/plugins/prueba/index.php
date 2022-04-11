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

	echo "EE";
	echo '<br>';
	echo '<form action="../wp-content/plugins/prueba/funciones/subirexcel.php" method="post" enctype="multipart/form-data">
  Select image to upload:
  <input type="file" name="fileToUpload" id="fileToUpload">
  <input type="submit" value="Upload File" name="submit">
</form>';
}


?>


