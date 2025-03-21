<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'marketing' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
	die;
}

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'marketing' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();

$menu_consultar = $menu_id_consultar["id"];

$area_id = $login ? $login['area_id'] : 0;
$cargo_id = $login ? $login['cargo_id'] : 0;


?>

<style>

	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 15px 0;
		box-shadow: 0px 0px 0px 0px #000;
		border-radius: 5px;
	}

	legend.dhhBorder{
		font-size: 14px;
		text-align: left;
		width: auto;
		padding: 0 10px;
		border-bottom: none;
		margin-bottom: 10px;
		text-transform: capitalize;
	}


	.cont_proveedor_datepicker {
    	min-height: 28px !important;
	}
</style>

<div class="content container-fluid">

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12">
		<div class="alert alert-success alert-dismissible fade in" role="alert" style="margin: 0px;">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">×</span>
			</button>
			<p class="text-center">
				<?php 
				$ultima_modificacion = "";
				$query_updated = $mysqli->query("SELECT updated_at FROM tbl_modificaciones WHERE status = 1 AND modulo = 'Marketing' ORDER BY updated_at DESC LIMIT 1");
				while($sel2 = $query_updated->fetch_assoc())
				{
					$ultima_modificacion = $sel2['updated_at'];
				}
				if (!Empty($ultima_modificacion)) {
					$ultima_modificacion = !Empty($ultima_modificacion) ? date("d/m/Y H:i A", strtotime($ultima_modificacion)):'';
				}

				?>
				El sistema ha sido actualizado el <b> <?=$ultima_modificacion ?> </b> En caso de identificar un mal funcionamiento presionar:
				<b>Ctrl+F5</b> (Si estás en PC) o <b>Ctrl+Tecla Función +F5</b> (Si estás en laptop) o contactar con el área de sistemas.
			</p>
		</div>

		<br>
	</div>
</div>

<div class="page-header wide">

		<div class="row">
			<div class="col-md-4 col-sm-12 col-xs-12">
				<a class="btn btn-info" href="<?='./?sec_id=marketing&sub_sec_id=nuevo'?>"><i class="fa fa-plus"></i>  Nueva Solicitud</a>
			</div>
		</div>

		<div class="row mt-4 mb-2">
			<fieldset class="dhhBorder no-pad">
				
				<legend class="dhhBorder">Búsqueda</legend>
				<form autocomplete="off">
				<input type="hidden" id="menu_consultar" value="<?=$menu_consultar?>">
					<input type="hidden" id="area_id" value="<?=$area_id?>">
					<input type="hidden" id="cargo_id" value="<?=$cargo_id?>">
					<input type="hidden" id="menu_id" value="<?=$menu_id?>">
					<input type="hidden" id="sec_id" value="<?=$sec_id?>">
					<input type="hidden" id="currentPage" value="1">

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 mt-3">
						<div class="form-group">
							<label for="sec_mkt_req_area_id">Área <span class="campo_obligatorio_v2">(*)</span>:</label>
							<select class="form-control select2" name="sec_mkt_req_area_id" id="sec_mkt_req_area_id" 
								title="Seleccione la empresa grupo AT 1">
							</select>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 mt-3">
						<div class="form-group">
							<label for="sec_mkt_req_producto_id">Producto <span class="campo_obligatorio_v2">(*)</span>:</label>
							<select class="form-control select2" name="sec_mkt_req_producto_id" id="sec_mkt_req_producto_id" 
								title="Seleccione la empresa grupo AT 1">
							</select>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 mt-3">
						<div class="form-group">
							<label for="sec_mkt_req_tipo_solicitud_id">Solicitud <span class="campo_obligatorio_v2">(*)</span>:</label>
							<select class="form-control select2" name="sec_mkt_req_tipo_solicitud_id" id="sec_mkt_req_tipo_solicitud_id" 
								title="Seleccione la empresa grupo AT 1">
							</select>
						</div>
					</div>

					<div class="form-group">
						<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 mt-3">
							<label>Estados:</label>
							<div class="form-group">
								<!-- <div class="input-group"> -->
								<select name="sec_mkt_req_estado" id="sec_mkt_req_estado" class="form-control select2" style="width: 100%">
								</select>
								<!-- </div> -->
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 mt-3">
							<label>F. Solicitud desde:</label>
							<div class="form-group">
								<div class="input-group">
									<input type="text"class="form-control form-control-rounded mkt_req_datepicker" id="sec_mkt_req_fecha_inicio" readonly="readonly">
									<label class="input-group-addon input-group-addon-rounded glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_mkt_req_fecha_inicio"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 mt-3">
							<label>F. Solicitud hasta:</label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<input type="text" class="form-control form-control-rounded mkt_req_datepicker" id="sec_mkt_req_fecha_fin" value="" readonly="readonly">
									<label class="input-group-addon input-group-addon-rounded glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_mkt_req_fecha_fin"></label>
								</div>
							</div>
						</div>	
					</div>
				
					<div class="row form-horizontal">
						<div class="col-xs-12 col-sm-12 col-md-3 col-md-offset-7 text-right" style="margin-top: 10px; margin-bottom: 20px;">
							<button type="button" class="btn btn-block btn-primary btn-rounded float-left btn-block" id="cont_proveedor_btn_buscar" onclick="sec_mkt_req_restablecer_parametros();">
								Restablecer Parametros
							</button>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 text-right" style="margin-top: 10px; margin-bottom: 20px;">
							<button type="button" class="btn btn-rounded btn-success float-left btn-block" id="cont_proveedor_btn_buscar" onclick="sec_mkt_req_listar_solicitudes();">
								<i class="glyphicon glyphicon-search"></i>
								Buscar
							</button>
						</div>
					</div>
				</form>
			</fieldset>
		</div>
	</div>


	
</div>



<div class="col-md-12" id="block-resultado-tabla">

</div>

<textarea style="display:none" id="usuario_permisos" cols="30" rows="10"><?php echo json_encode($usuario_permisos); ?></textarea>