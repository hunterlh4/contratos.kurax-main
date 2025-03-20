<?php
include_once("sys/global_config.php");
include_once("sys/sys_mantenimiento.php");
include_once("sys/sys_cookies.php");
include_once("sys/db_connect.php");
// include_once("sys/atsnacks_connect.php");
include_once("sys/sys_login.php");
include_once("sys/build_html.php");
include_once("sys/config_loader.php");
//se considera en caso de ser modulo de usuarios
if ($sec_id == "usuarios") ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<?php
include_once("page_header.php");
?>

<body class="sidebar-style" id="body_<?php echo $sec_id . '_' . $sub_sec_id . '_style'; ?>">
	<?php  ?>
	<input type="hidden" class="usuario_password_changed" value="<?php echo isset($login["password_changed"]) ? $login["password_changed"] : ''; ?>">
	<input type="hidden" id="usuario_local_id" value="<?php echo isset($login["local_id"]) ? $login["local_id"] : ''; ?>">
	<input type="hidden" class="input_text_validacion" data-col="sec_id" id="sec_id" value="<?php echo $sec_id; ?>">
	<input type="hidden" class="input_text_validacion" data-col="sub_sec_id" id="sub_sec_id" value="<?php echo $sub_sec_id; ?>">
	<input type="hidden" class="input_text_validacion" data-col="item_id" id="item_id" value="<?php echo $item_id; ?>">

	<?php
	if ($sec_id == "login") {
	} else {
		include("page_navbar.php");
	}
	?>


	<?php
	if ($sec_id == "login") {
	?>
		<main class="main-container no-margin no-padding">
		<?php
	} else {
		?>
			<main class="main-container">
			<?php
			include("page_sidebar.php");
		}

		$file = "sec_" . $sec_id . ".php";
		if (file_exists($file)) {
			if (isset($login['usuario'])) {
				$_SESSION['usuario'] = $login['usuario'];
				$_SESSION['id'] = $login["id"];
				$_SESSION['local_name'] = null;
				$_SESSION['local_id'] = $login['local_id'];
			}
			include("sec_" . $sec_id . ".php");
		} else {
			//echo "El archivo $file no existe";
			echo "  &nbsp;&nbsp;No cuenta con permisos suficientes.";
		}
			?>
			</main>

			<?php include("page_footer.php"); ?>

			<div class="loading_box" id="loading_box"></div>
			<div class="doc_size">
				<div class="visible-xs bg-danger">xs (<768px)< /div>
						<div class="visible-sm bg-warning">sm (≥768px)</div>
						<div class="visible-md bg-info">md (≥992px)</div>
						<div class="visible-lg bg-success">lg (≥1200px)</div>
				</div>
				<?php
				include("js_include.php");

				// ARRAY PARA AGREGAR MAS INCLUDES
				// $valores_sec_id = ['caja'];

				// foreach($valores_sec_id as $va_sec){
				// 	$file_js = "js_include/js_include_".$va_sec.".php";
				// 	if(isset($login['usuario'])){
				// 		if(file_exists($file_js)){
				// 			include($file_js);
				// 		}
				// 	}		
				// }
				$js_include = $sec_id;
				$js_include .= ($sub_sec_id) ? "_" . $sub_sec_id : "";

				$file_js = "js_include/js_include_" . $js_include . ".php";
				if (isset($login['usuario'])) {
					if (file_exists($file_js)) {
						include($file_js);
					}
				}
				?>
				<link rel="stylesheet" href="css/leaflet/leaflet.css" />
				<script type="text/javascript" src="js/leaflet/leaflet.js"></script>
				<?php if ($login) { ?>
					<script type="text/javascript" src="js/imgviewer2/imgViewer2.js"></script>
					<script type="text/javascript">
						var anuncios_area_id = <?php echo isset($login["area_id"]) ? $login["area_id"] : '0'; ?>;
						var anuncios_cargo_id = <?php echo isset($login["cargo_id"]) ? $login["cargo_id"] : '0'; ?>;
						var anuncios_grupo_id = <?php echo isset($login["grupo_id"]) ? $login["grupo_id"] : '0'; ?>;
						var gestion_ver_solo_una_pestana = <?php echo isset($usuario_permisos[160]) ? (in_array("view_only_one_tab", $usuario_permisos[160]) ? '1' : '0') : '0'; ?>;
					</script>
				<?php } ?>

</body>

</html>
<?php
include("sys/index_footer.php");
if ($sec_id == "usuarios") ob_end_flush();
?>