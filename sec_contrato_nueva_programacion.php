<?php

if (isset($_GET["id"])) {
	$editar = true;
	$programacion_id = $_GET["id"];
} else {
	$editar = false;
}

$valor_cambio = "3.7530";

if ($editar) {
	// FIN OBTERNER PARAMETROS DE LA PROGRAMACIÓN
	$query_parametros = "
	SELECT 
		p.id AS programacion_id,
		p.numero programacion_numero,
		tpp.nombre AS tipo_programacion,
		p.fecha_programacion,
		p.tipo_concepto_id,
		tc.nombre concepto,
		p.tipo_pago_id,
		tp.nombre tipo_pago,
		rs.nombre AS arrendatario,
		p.num_cuenta_id,
		CONCAT(b.nombre , ' ', nc.num_cuenta_corriente) AS banco,
		p.moneda_id,
		(
			CASE
				WHEN p.moneda_id = 1 THEN 'MN'
				WHEN p.moneda_id = 2 THEN 'ME'
			END
		) AS moneda,
		p.importe AS importe,
		p.etapa_id,
		ep.nombre etapa,
		tpp.id as programacion_tipo_id,
		b.id as banco_id,
		rs.id empresa_id,
		p.valor_cambio
	FROM 
		cont_programacion p
		INNER JOIN cont_tipo_concepto tc on p.tipo_concepto_id = tc.id
		INNER JOIN cont_tipo_pago_programacion tp on p.tipo_pago_id = tp.id
		INNER JOIN cont_num_cuenta nc on p.num_cuenta_id = nc.id
		INNER JOIN tbl_razon_social rs ON p.razon_social_id = rs.id
		INNER JOIN tbl_bancos b ON nc.banco_id = b.id
		INNER JOIN cont_etapa_programacion ep on p.etapa_id = ep.id
		INNER JOIN cont_tipo_programacion tpp ON p.tipo_programacion_id = tpp.id
	WHERE 
		p.status = 1 AND 
		p.id =" . $programacion_id . "
	";

	$list_query = $mysqli->query($query_parametros);

	if($mysqli->error){
		enviar_error($mysqli->error . $query_parametros);
	}

	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$row = $list_query->fetch_assoc();
		$numero = $row["programacion_numero"];
		$fecha_programacion = $row["fecha_programacion"];
		$tipo_concepto_id = $row["tipo_concepto_id"];
		$tipo_pago_id = $row["tipo_pago_id"];
		$num_cuenta_id = $row["num_cuenta_id"];
		$valor_cambio = $row["valor_cambio"];
		$moneda_id = $row["moneda_id"];
		$moneda = $row["moneda"];
		$banco_id = $row["banco_id"];
		$empresa_id = $row["empresa_id"];
		$banco_y_cuenta = $row["banco"];
		$programacion_tipo_id = $row["programacion_tipo_id"];
	}
	// FIN OBTERNER PARAMETROS DE LA PROGRAMACIÓN
	// INICIO OBTERNER DETALLE DE LA PROGRAMACIÓN
	$query_detalle_programacion = "
	SELECT 
	    cp2.fecha_actual,cd.provision_id ,cd.programacion_id ,cp2.id ,cp2.importe as programado ,cp2.periodo_fin 
	FROM 
		cont_programacion_detalle cd
		left join cont_programacion cp 
		on cp.id =cd.programacion_id 
		left join cont_provision cp2 
		on cp2 .id = cd.provision_id 
	WHERE 
		cd.status = 1
		AND cd.programacion_id =" . $programacion_id . "
	";

	$list_query = $mysqli->query($query_detalle_programacion);

	if($mysqli->error){
		enviar_error($mysqli->error . $query_detalle_programacion);
	}

	$row_count = $list_query->num_rows;

	$provision_ids = '';
	$contador_ids = 0;
	if ($row_count > 0) {
		while ($row = $list_query->fetch_assoc()) {
			if ($contador_ids > 0) {
				$provision_ids .= ',';
			}
			$provision_ids .= $row["provision_id"];			
			$contador_ids++;
		}
	}
	// FIN OBTERNER DETALLE DE LA PROGRAMACIÓN

	// OBTENER LA FEHCA MAXIMA Y MINIMA 
	$list_query2 = $mysqli->query($query_detalle_programacion);

	$row2 = $list_query2->fetch_assoc();
	$fecha_minima = $row2['periodo_fin'];
	$fecha_maxima = $row2['periodo_fin'];
	if ($row_count > 0) {
	$fecha_maxima = $row2['periodo_fin'];
		$periodo_provisiones = $row2['fecha_actual'];

		while($row2 = $list_query2->fetch_assoc()) {
			if($row2['periodo_fin'] < $fecha_minima) {
				$fecha_minima = $row2['periodo_fin'];
			}
			if($row2['periodo_fin'] > $fecha_maxima) {
				$fecha_maxima = $row2['periodo_fin'];
			}
		}
	}
	

}
?>

<style>
	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 10px 0;
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

	.table-comprimido>tbody>tr>th, 
	.table-comprimido>tfoot>tr>th, 
	.table-comprimido>thead>tr>th {
		padding: 4px;
		padding-left: 8px;
	}

	.table-comprimido>tbody>tr>td, 
	.table-comprimido>tfoot>tr>td, 
	.table-comprimido>thead>tr>td {
		padding: 0px;
		padding-left: 8px;
	}
</style>

<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i>
					<?php 
					if ($editar) {
						echo "Editar programación N°. " . $numero;
					} else {
						echo "Creación de programación de pagos";
					}
					?>
				</h1>
			</div>
		</div>
	</div>

	<div class="row mt-3 mb-2">
		<div class="page-header wide">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Parámetros de la programación</legend>
				<form>
					<input type="hidden" id="programacion_id_edit" name="programacion_id_edit" value="<?php echo $editar ? $programacion_id : 0; ?>">

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>N.° de programación</label>
						<input 
							type="text" 
							class="form-control" 
							name="num_programacion" 
							readonly="readonly"
							<?php
							if ($editar) {
								$num_programacion = $numero;
							} else {
								$select_correlativo = "
								SELECT 
									(CAST(MAX(numero) AS UNSIGNED) + 1) AS correlativo 
								FROM 
									cont_programacion
								";

								$sel_query = $mysqli->query($select_correlativo);
								$row = $sel_query->fetch_assoc();
								$num_programacion = str_pad($row['correlativo'], 10, '0', STR_PAD_LEFT);
							}
							?>
							value="<?php echo $num_programacion;?>"
						>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Tipo de Programación</label>
						<select
							class="form-control input_text select2"
							data-live-search="true"
							name="tipo_programacion_id" 
							id="tipo_programacion_id" 
							title="Seleccione el concepto">
							<option value="0">- Seleccione -</option>
							<?php
							$sel_query = $mysqli->query("
								SELECT id, nombre
								FROM cont_tipo_programacion
								WHERE status = 1
								ORDER BY id ASC;");
							while($sel=$sel_query->fetch_assoc()){

								if ($editar) {

									if ($programacion_tipo_id === $sel["id"]) {

										$programacion_tipo_idD = ' selected';
									} else {
										$programacion_tipo_idD = '';
									}
								} else {
									$programacion_tipo_idD = '';
								}
							?>
								<option value="<?php echo $sel["id"];?>" <?php echo $programacion_tipo_idD; ?>><?php echo $sel["nombre"] ?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" id="div_tipo_anticipo" style="display: none;">
						<label>Tipo de Anticipo</label>
						<select
							class="form-control select2"
							name="tipo_anticipo_id" 
							id="tipo_anticipo_id" 
							title="Seleccione el tipo de anticipo">
							<option value="T">Adelantos y garantía</option>
							<?php
							$sel_query = $mysqli->query("
								SELECT id, nombre
								FROM cont_tipo_anticipo
								WHERE status = 1
								ORDER BY nombre ASC;");
							while($sel=$sel_query->fetch_assoc()){
								if ($editar) {
									if ($tipo_concepto_id == $sel["id"]) {
										$selected_concepto = ' selected';
									} else {
										$selected_concepto = '';
									}
								} else {
									$selected_concepto = '';
								}
							?>
								<option value="<?php echo $sel["id"];?>" <?php echo $selected_concepto; ?>><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Concepto</label>
						<select
							class="form-control input_text select2"
							data-live-search="true"
							name="tipo_concepto_id" 
							id="tipo_concepto_id" 
							title="Seleccione el concepto">
							<option value="0">- Seleccione -</option>
							<?php
							$sel_query = $mysqli->query("SELECT id, nombre
								FROM cont_tipo_concepto
								WHERE status = 1
								ORDER BY nombre ASC;");
							while($sel=$sel_query->fetch_assoc()){
								if ($editar) {
									if ($tipo_concepto_id == $sel["id"]) {
										$selected_concepto = ' selected';
									} else {
										$selected_concepto = '';
									}
								} else {
									$selected_concepto = '';
								}
							?>
								<option value="<?php echo $sel["id"];?>" <?php echo $selected_concepto; ?>><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Empresa:</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="empresa_id" 
							id="empresa_id" 
							title="Seleccione el tipo de pago">
							<option value="0">- Seleccione -</option>
							<?php
							$usuario_id = $login?$login['id']:null;

							$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;
						
							$list_query_permisos_empresas = $mysqli->query($query);
							$empresas_query = 'SELECT rs.id ,rs.nombre  from  tbl_razon_social rs  

							LEFT JOIN tbl_locales_redes tlr 
							ON tlr.id = rs.red_id 
							LEFT JOIN tbl_locales tl 
							ON tl.red_id = tlr.id
							LEFT JOIN tbl_usuarios_locales tul 
							on tul.local_id = tl.id
							WHERE rs.status = 1
							AND subdiario IS NOT NULL'; 
						
							if ($list_query_permisos_empresas->num_rows > 0) {
								$empresas_query.= ' AND  tul.usuario_id ='.$usuario_id.'   GROUP BY  rs.id';
							}else{
								$empresas_query.= '   GROUP BY  rs.id';
						
							} 

							$sel_query = $mysqli->query($empresas_query);
						
							while($sel=$sel_query->fetch_assoc()){
								if ($editar) {
									if ($empresa_id == $sel["id"]) {
										$empresa_idD = ' selected';
									} else {
										$empresa_idD = '';
									}
								} else {
									$empresa_idD = '';
								}
							?>
								<option value="<?php echo $sel["id"];?>" <?php echo $empresa_idD; ?>><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>	
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Banco (Número de cuenta)</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="banco_id" 
							id="banco_id" 
							title="Seleccione el banco">
							<option value="0">- Seleccione -</option>
							<?php
							$sel_query = $mysqli->query("SELECT c.id, CONCAT(b.nombre, ' ', c.num_cuenta_corriente) AS nombre
								FROM cont_num_cuenta c
									INNER JOIN tbl_bancos b ON c.banco_id = b.id
								WHERE c.status = 1
								ORDER BY nombre ASC");
							while($sel=$sel_query->fetch_assoc()){
								if ($editar) {
									if ($banco_y_cuenta == $sel["nombre"]) {
										$banco_y_cuentaD = ' selected';
									} else {
										$banco_y_cuentaD = '';
									}
								} else {
									$banco_y_cuentaD = '';
								}
							?>
								<option value="<?php echo $sel["id"];?>" <?php echo $banco_y_cuentaD; ?>><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label for="start">Fecha de la programación:</label>
						<div class="input-group">
							<?php
							if ($editar) {
								$fecha_de_la_programacion = date('d/m/Y', strtotime($fecha_programacion));
							} else {
								$fecha_de_la_programacion = date("d/m/Y", strtotime("0 days"));
							}
							?>
							<input
									type="text"
									name="fecha_programacion"
									id="fecha_programacion"
									class="form-control"
									value="<?php echo $fecha_de_la_programacion;?>"
									readonly="readonly"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_programacion"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" style="margin-bottom: 10px;">
						<label>Tipo de cambio (Venta)</label>
						<?php
						if ($editar) {
							$valor_de_cambio = $valor_cambio;
						} else {
							$fecha = date("Y-m-d", strtotime("0 days"));
							$select_correlativo = "
							SELECT 
								monto_venta
							FROM 
								tbl_tipo_cambio
							WHERE
								fecha = '$fecha'
								AND moneda_id = 2
							";

							$sel_query = $mysqli->query($select_correlativo);
							$row_count = $sel_query->num_rows;
							if ($row_count > 0) {
								$row = $sel_query->fetch_assoc();
								$valor_cambio = $row['monto_venta'];
							} else {
								$valor_cambio = 'No existe tipo de cambio el ' . $fecha;
							}
						}
						?>
						<input 
							type="text" 
							class="form-control" 
							name="tipo_de_cambio" 
							id="tipo_de_cambio" 
							readonly="readonly"
							value="<?php echo $valor_cambio;?>"
						>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" style="display: none;">
						<label>Tipo de pago</label>
						<select
							class="form-control input_text select2"
							name="tipo_pago_id" 
							id="tipo_pago_id" 
							title="Seleccione el tipo de pago">
							<?php
							$sel_query = $mysqli->query("SELECT id, nombre
								FROM cont_tipo_pago_programacion
								WHERE status = 1
								ORDER BY nombre ASC;");
							while($sel=$sel_query->fetch_assoc()){
								if ($editar) {
									if ($tipo_pago_id == $sel["id"]) {
										$selected_tipo_de_pago = ' selected';
									} else {
										$selected_tipo_de_pago = '';
									}
								} else {
									$selected_tipo_de_pago = '';
								}
							?>
								<option value="<?php echo $sel["id"];?>" <?php echo $selected_tipo_de_pago; ?>><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>	
						</select>
					</div>

				</form>
			</fieldset>
		</div>
	</div>

	<div class="row mt-3 mb-2">
		<div class="page-header wide">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Parámetros de búsqueda</legend>
				<form id="form_busqueda_acreedores">

					<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
						<label>Busqueda por:</label>
						<select
							class="form-control input_text select2"
							name="busqueda_por" 
							id="busqueda_por" 
							title="Seleccione el tipo de busqueda">
							<option value="0">- Seleccione -</option>
							<option value="2" <?php echo $editar?'selected':'' ?> >Periodo</option>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2" id="div_mes" style="display: none;">
						<label>Mes:</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="mes_id" 
							id="mes_id" 
							title="Seleccione el tipo de pago">
							<option value="0">- Seleccione -</option>
							<?php 
							$inicio = new DateTime(date('Y-m-d'));
							$meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
							for ($i = 1; $i <= 6; $i++) {
							    $mes = $meses[$inicio->format('n') - 1] . ' del ' . $inicio->format('Y');
							    $value_mes = $inicio->format('Y') . $inicio->format('m');
							    echo '<option value="' . $value_mes . '">' . $mes . '</option>';
							    $inicio->modify('- 1 month');
							}

							?>
						</select>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2" id="div_periodo" <?php echo $editar?'':'' ?> >
						<label>Periodo:</label>
						<div class="input-group">
								<select class="form-control input_text select2" name="" id="fecha_vencimiento_inicio">
                                    <option value="2023-03-30">Periodo   2023-03</option>
                                    <option value="2023-04-30">Periodo   2023-04</option>
                                    <option value="2023-05-30">Periodo   2023-05</option>
                                    <option value="2023-06-30">Periodo   2023-06</option>
                                    <option value="2023-07-30">Periodo   2023-07</option>
                                    <option value="2023-08-30">Periodo   2023-08</option>
                                    <option value="2023-09-30">Periodo   2023-09</option>
                                    <option value="2023-10-30">Periodo   2023-10</option>
                                    <option value="2023-11-30">Periodo   2023-11</option>
                                    <option value="2023-12-30" selected>Periodo   2023-12</option>
                                </select>
							<!-- <input
									type="text"
									name="fecha_vencimiento_inicio"
									id="fecha_vencimiento_inicio"
									class="form-control sec_contrato_nueva_programacion_datepicker"
									value="<?php echo isset($fecha_minima)?$fecha_minima:date("01/m/Y");?>"
									readonly="readonly"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_vencimiento_inicio" id="label_fecha_vencimiento_inicio"></label> -->
						</div>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2" id="div_dia_de_pago">
						<label>Día de pago:</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="dia_de_pago" 
							id="dia_de_pago" 
							title="Seleccione el tipo de pago">
							<option value="0">- Todos -</option>
							<?php 
							for ($dia = 1; $dia <= 31; $dia++) { 
								echo '<option value="' . $dia . '">' . $dia . '</option>';
							}
							?>
						</select>
					</div>

					

					
<!-- 
					<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2" id="div_fecha_de_vencimiento_hasta" <?php echo $editar?'':'style="display: none;"' ?>>
						<div class="form-group">
						<label for="start">Fecha vcto. Hasta:</label>
						<div class="input-group">
							<input
									type="text"
									name="fecha_vencimiento_fin"
									id="fecha_vencimiento_fin"
									class="form-control sec_contrato_nueva_programacion_datepicker"
									value="<?php echo isset($fecha_maxima)?$fecha_maxima: date("t/m/Y");?>"
									readonly="readonly"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_vencimiento_fin" id="label_fecha_vencimiento_fin"></label>
						</div>
						</div>
					</div> -->

					<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
						<label>Banco:</label>
						<select
							class="form-control input_text select2"
							name="banco_de_acreedores" 
							id="banco_de_acreedores" 
							title="Seleccione el banco">
							<option value="0">TODOS</option>
							<option value="1">BBVA</option>
							<option value="2">Interbancario</option>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
						<button 
							type="button" 
							name="btn_buscar_acreedores_pendiente_de_pago" 
							id="btn_buscar_acreedores_pendiente_de_pago" 
							value="1" 
							class="btn btn-success btn-block btn-sm" 
							data-button="request" 
							data-toggle="tooltip" 
							data-placement="top" 
							title="Buscar acreedores" 
							style="position: relative; bottom: -19px; margin-bottom: 30px;">
							<i class="glyphicon glyphicon-search"></i>
							Buscar
						</button>
					</div>
				</form>
			</fieldset>
		</div>
	</div>

	<div class="row mt-3 mb-2">
		<div class="table-responsive" id="div_acreedores_pendiente_pago">
			<table class="table table-bordered" style="font-size: 12px">
				<thead>
					<tr>
						<th colspan="10" style="background-color: #E5E5E5;">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">
								Acreedores pendiente de pago:
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="text-align: center;">Seleccione los parámetros de búsqueda y haga click en el boton Buscar.</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<br>						
	<div class="row mt-2 mb-2">
		<div class="table-responsive" id="div_acreedores_en_la_programacion_de_pagos">
			<table class="table table-bordered" style="font-size: 12px">
				<thead>
					<tr>
						<th colspan="10" style="background-color: #E5E5E5;">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">
								Acreedores que integran la programación de pago:
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="10" style="text-align: center;">No se han agregado acreedores pendientes de pago a la programación de pago.</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table-responsive" id="div_acreedores_en_la_programacion_de_pagos_montos">

		</div>
	</div>

	<div class="row mt-2 mb-2" style="text-align: right;">
		<button 
			type="button"
			class="btn btn-danger" 
			title="Cancelar" 
			onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=tesoreria')" 
		>
			<i class="fa fa-times"></i>
			Cancelar
		</button>

		<?php 
		if ($editar) {
			$titulo = 'Guardar cambios';
			$funcion = 'sec_contrato_nueva_programacion_guardar_cambios_programacion();';
		} else {
			$titulo = 'Grabar y aprobar programación';
			$funcion = 'sec_contrato_nueva_programacion_guardar_y_aprobar_programacion();';
		}
		?>

		<button 
			type="button"
			class="btn btn-success" 
			title="<?php echo $titulo; ?>" 
			onclick="<?php echo $funcion; ?>" 
		>
			<i class="fa fa-save"></i>
			<?php echo $titulo; ?>
		</button>
	</div>
</div>


<!-- INICIO MODAL DETALLE DE PAGO -->
<div id="modal_detalle_de_pagos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" style="font-weight: bold;">Detalle de Pagos</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div id="div_detalle_de_pagos"></div>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL DETALLE DE PAGO -->
