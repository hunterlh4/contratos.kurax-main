<?php

$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
	die;
}

date_default_timezone_set("America/Lima");

$permiso_ver='false';
if(in_array("view", $usuario_permisos[$menu_id])){
	$permiso_ver='true';
}

$permiso_guardar='false';
if(in_array("save", $usuario_permisos[$menu_id])){
	$permiso_guardar='true';
}

$permiso_editar='false';
if(in_array("edit", $usuario_permisos[$menu_id])){
	$permiso_editar='true';
}

if($permiso_ver){

?>

<style type="text/css">
	.btn-default.active {
		color: #fff !important;
		background-color: #263238 !important;
		border-color: #263238 !important;
	}
	.alert-default{
		background-color: #f1f1f1;
		border-color: #898989;
		padding: 0px !important;
	}

	.tabla_primer_th th {
		color: white !important;
	}
	.tabla_primer_th_diario {
		background-color: #395168 !important;
	}
	.tabla_primer_th_mensual {
		background-color: #2971b1 !important;
	}
	.tabla_primer_th_anual {
		background-color: #6f9ac0 !important;
	}


	.tabla_segundo_th {
		background-color: #ffffdd !important;
	}
	.tabla_segundo_th th {
		color:  black !important;
	}

	.background_color_verde {
		background-color: #a9d18e !important;
	}

    .div_SecCronTls_tabla {
		font-size: 10px;
    }

    @media screen and (max-width: 1500px){
        .div_SecCronTls_tabla {
            overflow-x: auto;
        }
    }
</style>

<script>
	var permiso_ver=<?php echo $permiso_ver; ?>;
	var permiso_editar=<?php echo $permiso_editar; ?>;
	var permiso_guardar=<?php echo $permiso_guardar; ?>;
	var gen_fecha_actual=<?php echo "'".date('Y-m-d')."'"; ?>;
</script>

<div class="tbl_goldenRace_retail_jackpots" id="div_SecCronTls">


	<div id="loader_"></div>



	<div class="row">
		<div class="col-md-12">
			<div class="panel" style="border-color: transparent;margin-bottom: 0px;">

				<div class="panel-heading" style="border-color: #01579b;background: #fff;">
					<div class="panel-title" style="color: #000;text-align: center;font-size: 22px;">Reporte de Teleservicios</div>
				</div>

				<div class="panel-body">

					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label for="SecCronTls_fecha">Fecha:</label>
								<input id="SecCronTls_fecha" type="text" class="form-control">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<br>
								<button class="btn btn-primary" id="SecCronTls_btn_buscar" style="width: 100%;">
									<span class="glyphicon glyphicon-search"></span>
									Buscar
								</button>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<br>
								<button class="btn btn-primary" id="SecCronTls_btn_generar" style="width: 100%;">
									<span class="glyphicon glyphicon-save"></span>
									Generar
								</button>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<br>
								<button class="btn btn-primary" id="SecCronTls_btn_enviar" style="width: 100%;">
									<span class="fa fa-telegram"></span>
									Enviar
								</button>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>


	<hr style="margin-top: 0px;margin-bottom: 10px;">


	<div class="row" id="SecCronTls_div_resultado" hidden>
		<div class="col-md-12 div_SecCronTls_tabla">
			<h3 style="margin-top: 0px;margin-bottom: 0px;font-weight: bold;" id="titulo_diario"></h3>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_x_producto_diario" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_transaccionales_diario" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_x_caja_diario" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_jv_x_juego_diario" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_recargas_web_diario" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_terminales_diario" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_otros_pagos_diario" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>

		<!-- ************************************************************************************ -->
		<!-- MENSUAL **************************************************************************** -->
		<!-- ************************************************************************************ -->

		<div class="col-md-12 div_SecCronTls_tabla">
			<h3 style="margin-top: 0px;margin-bottom: 0px;font-weight: bold;" id="titulo_mensual"></h3>
		</div>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_x_producto_mensual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_transaccionales_mensual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_x_caja_mensual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_jv_x_juego_mensual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_recargas_web_mensual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_terminales_mensual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_otros_pagos_mensual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>

		<!-- ************************************************************************************ -->
		<!-- ANUAL ****************************************************************************** -->
		<!-- ************************************************************************************ -->

		<div class="col-md-12 div_SecCronTls_tabla">
			<h3 style="margin-top: 0px;margin-bottom: 0px;font-weight: bold;" id="titulo_anual"></h3>
		</div>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_x_producto_anual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_transaccionales_anual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_x_caja_anual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_jv_x_juego_anual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_recargas_web_anual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_terminales_anual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
		<br>
		<div class="col-md-12 div_SecCronTls_tabla">
			<table id="tabla_ventas_otros_pagos_anual" border="1" cellpadding="5" cellspacing="0" width="800px" style="font-size: 12px;color: black;">
			</table>
		</div>
	</div>







	<!--**************************************************************************************************-->
	<!-- MODAL CORREO -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_correo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">ENVIAR CORREO</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Asunto:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;" id="modal_correo_i_asunto_div">
							<input id="modal_correo_i_asunto" type="text" class="form-control" placeholder="Ingresar asunto">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Correos:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;" id="modal_correos_principales_txa_lista_div">
							<textarea class="form-control" id="modal_correos_principales_txa_lista" placeholder="Ingresar correos con ',' en la separación." 
							autocomplete="off" rows="3"></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Correos ocultos:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;" id="modal_correos_ocultos_txa_lista_div">
							<textarea class="form-control" id="modal_correos_ocultos_txa_lista" placeholder="Ingresar correos con ',' en la separación." 
							autocomplete="off" rows="3"></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<button type="button" class="btn btn-default pull-left" id="modal_correo_btn_limpiar">
							<b><i class="fa fa-edit"></i> Limpiar Campos</b>
						</button>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
				<button type="button" class="btn btn-primary pull-right" id="modal_correo_btn_guardar">
					<b><i class="fa fa-check"></i> ENVIAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>








</div>

<?php
}
?>