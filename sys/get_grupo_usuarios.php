<?php
include("db_connect.php");
include("sys_login.php");

if(isset($_POST["data_check"]) and $_POST["data_check"] =="check_users"){
	$list_totales = [];
	$result = $mysqli->query("SELECT id FROM tbl_usuarios WHERE grupo_id = ".$_POST["id_grupo"]."");
	while($r = $result->fetch_assoc()) $list_totales[] = $r["id"];
	$result =[];
	if(count($list_totales)===0){
		$result["http_code"] = 204;
		$result["data"] =$list_totales;
	} elseif(count($list_totales)>0){
		$result["http_code"] = 200;
		$result["data"] =$list_totales;
		$result["error"] =false;
	} else {
		$result["http_code"] = 400;
		$result["status_totales"] ="Ocurrió un error al consultar transacciones.";
	}
	echo json_encode($result);
}

if(isset($_POST["sec_usuarios_change_pass_modal"])){
	?>
	<div class="modal" id="sec_usuarios_change_pass_modal">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form class="form" id="sec_usuarios_change_pass_form">
					<div class="modal-header">
						<?php if($login["password_changed"]){ ?><button type="button" class="close close_btn"><span aria-hidden="true">&times;</span></button><?php } ?>
						<h4 class="modal-title">Cambiar Contraseña</h4>
					</div>
					<div class="modal-body">						
						<div class="form-group">
							<label>Contraseña actual:</label>
							<input class="form-control save_data" type="password" name="current_password" required="required">
						</div>
						<div class="form-group">
							<label>Nueva contraseña:</label>
							<input class="form-control save_data" type="password" name="new_password" required="required">
						</div>
						<div class="form-group">
							<label>Confirmar Nueva contraseña:</label>
							<input class="form-control save_data" type="password" name="new_repassword" required="required">
						</div>						
					</div>	
					<div class="modal-footer">
						<div class="form-group ">
							<button type="submit" class="btn btn-success save_btn" title="Abrir"><span class='glyphicon glyphicon-floppy-save'></span> Guardar</button>
							<?php if($login["password_changed"]){ ?><button class="btn btn-default close_btn">Cancelar</button><?php } ?>
						</div>				
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
}
?>