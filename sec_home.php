<div class="content container-fluid">
	<?php
	// $filename="dashboard/dashboard_".$login['area_id'].".php";
	// 	if (!file_exists($filename)) {
	// 		include($filename);
	// 	}else{
	// print "<pre>";
	// print_r($usuario_permisos);
	// print "</pre>";
	if(array_key_exists(18, $usuario_permisos)){
		if(in_array("view", $usuario_permisos[18])){
			// include("dashboard/dashboard.php");			
		}
	}


		// }
	?>
</div>