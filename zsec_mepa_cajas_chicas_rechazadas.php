<?php

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'mesa_partes' LIMIT 1")->fetch_assoc();
$menu_caja_chica = $menu_id_consultar["id"];

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
	
</style>

<div class="content container-fluid">
	<div class="page-header wide" style="margin-bottom: 10px;">
		<div class="row">
			<div class="col-xs-12 text-center">
				<form id="form_solicitudes">
					<h1 class="page-title titulosec">
						<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Historial Cajas Chicas Rechazadas
					</h1> 
				</form>
			</div>
		</div>
	</div>

	<div class="col-md-12" style="margin-bottom: 10px;">
        <a class="btn btn-primary btn-sm" id="btnRegresar" href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=mesa_partes">
            <i class="glyphicon glyphicon-arrow-left"></i>
            Regresar
        </a>
    </div>

    <?php
    if(array_key_exists($menu_caja_chica,$usuario_permisos) && in_array("MepaCajasRechazadas", $usuario_permisos[$menu_caja_chica]))
    {
    	?>
    	<div class="col-md-12" id="mepa_cajas_chicas_rechazadas">
			<div class="page-header wide">
				<div class="row mt-4 mb-2">
					<fieldset class="dhhBorder">
						<legend class="dhhBorder">Búsqueda</legend>
						<form autocomplete="off">
							
							<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
								<label>
									F. Solicitud Desde:
								</label>
								<div class="form-group">
									<div class="input-group col-xs-12">
										<input
											type="hidden"
											id="mepa_cajas_chicas_rechazadas_fecha_desde_hidden"
											class="input_text filtro"
											data-col="fecha_inicio"
											name="fecha_inicio"
											value="<?php echo date("Y-m-d", strtotime("+1 days")); ?>"
											data-real-date="mepa_cajas_chicas_rechazadas_param_fecha_desde">
										<input
											type="text"
											class="form-control mepa_cajas_chicas_rechazadas_datepicker"
											id="mepa_cajas_chicas_rechazadas_param_fecha_desde"
											value="<?php echo date("d-m-Y");?>"
											readonly="readonly"
											style="height: 34px;"
											>
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="mepa_cajas_chicas_rechazadas_param_fecha_desde"></label>
									</div>
								</div>
							</div>

							<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
								<label>
									F. Solicitud Hasta:
								</label>
								<div class="form-group">
									<div class="input-group col-xs-12">
										<input
											type="hidden"
											id="mepa_cajas_chicas_rechazadas_fecha_hasta_hidden"
											class="input_text filtro"
											data-col="fecha_inicio"
											name="fecha_inicio"
											value="<?php echo date("Y-m-d", strtotime("+7 days")); ?>"
											data-real-date="mepa_cajas_chicas_rechazadas_param_fecha_hasta">
										<input
											type="text"
											class="form-control mepa_cajas_chicas_rechazadas_datepicker"
											id="mepa_cajas_chicas_rechazadas_param_fecha_hasta"
											value="<?php echo date("d-m-Y");?>"
											readonly="readonly"
											style="height: 34px;"
											>
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="mepa_cajas_chicas_rechazadas_param_fecha_hasta"></label>
									</div>
								</div>
							</div>

							<div class="row form-horizontal">
								<div class="col-md-4 col-md-offset-8 text-right">
									<button type="button" class="btn btn-success float-left" id="mepa_cajas_chicas_rechazadas_btn_buscar" onclick="mepa_cajas_chicas_rechazadas_buscar();">
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

		<div class="content container-fluid" style="margin-left: 0px; margin-right: 0px; padding-left: 0px; padding-right: 0px;">
			<div class="col-md-8 col-lg-8 mt-3" id="mepa_cajas_chicas_rechazadas_div_tabla" style="display: none;">

				<div class="row mt-3" id="mepa_cajas_chicas_rechazadas_listar_liquidacion_table" style="">
					<table id="mepa_cajas_chicas_rechazadas_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th scope="col">Nº</th>
								<th scope="col">Usuario Solicitante</th>
								<th scope="col">Empresa</th>
								<th scope="col">Zona</th>
								<th scope="col">Nº Caja</th>
								<th scope="col">Monto</th>
								<th scope="col">Fecha Solicitud</th>
								<th scope="col">Ver</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			
			<div class="col-xs-12 col-md-4 col-lg-4 mt-3" id="mepa_cajas_chicas_rechazadas_div_detalle" style="display: none;">
				<div class="panel">
					<!-- Panel Heading -->
					<div class="panel-heading">
						<div class="panel-title">
							Detalle Informativo
						</div>
					</div>
					<div class="panel-body" style="margin-left: 0px; padding-left: 8px;">
						<h5>
							<strong>Usuario Solicitante:</strong> <small id="detalle_usuario" style="font-size: 13px;"></small>
						</h5>
						<h5>
							<strong>Nº Caja:</strong> <small id="detalle_num_caja" style="font-size: 13px;"></small>
						</h5>
						<h5>
							<strong>Empresa:</strong> <small id="detalle_empresa" style="font-size: 13px;"></small>
						</h5>
						<h5>
							<strong>Zona:</strong> <small id="detalle_zona" style="font-size: 13px;"></small>
						</h5>
					</div>
					
					<div class="panel-body" style="padding: 10px 0px 10px 0px;">
						
						<div id="mepa_cajas_chicas_rechazadas_cuerpo_detalle">
						</div>
					</div>

				</div>
			</div>

		</div>
    	<?php
    }
    else
    {
    	include("403.php");
        return false;
    }
    ?>

</div>
