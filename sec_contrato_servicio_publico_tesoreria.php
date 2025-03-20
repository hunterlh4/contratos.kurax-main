<?php

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'servicio_publico_tesoreria' LIMIT 1")->fetch_assoc();

$menu_id = $menu_id_consultar["id"];

$mi_permiso = false;

	$codigo_parametro_igh="razon_social_igh";
	$selectQuery = "SELECT valor FROM tbl_parametros_generales WHERE codigo = ?";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("s", $codigo_parametro_igh);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
	$selectStmt->bind_result($razon_social_igh);
	$selectStmt->fetch();
	}

if(array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id]))
{
	$mi_permiso = true;
}


$item_atencion = false;
$item_detalle_programacion = false;
$editar = false;

if(isset($_GET["item_atencion"]))
{
	$item_atencion = true;
	$item_detalle_programacion = false;
	
	$programacion_id = $_GET["item_atencion"];

	if($programacion_id != "")
	{
		$editar = true;
	}
	else
	{
		$editar = false;
	}
}
else if(isset($_GET["item_detalle_programacion"]))
{
	$item_atencion = false;
	$item_detalle_programacion = true;
	$editar = false;

	$servicio_publico_programacion_id = $_GET["item_detalle_programacion"];
}

$row_count = 0;

$tipo_solicitud_id = 0;
$se_cargo_comprobante = 0;

if($editar)
{
	// SELECT A LA PROGRAMACION A EDITAR
	$query_programacion = 
	"
		SELECT
			p.id, p.tipo_solicitud_id, p.tipo_empresa_id, p.se_cargo_comprobante
		FROM cont_ser_pub_programacion p
		WHERE p.id = '".$programacion_id."'
	";

	$list_query_programacion = $mysqli->query($query_programacion);

	$row_count = $list_query_programacion->num_rows;

	if ($row_count > 0) 
	{
		$row = $list_query_programacion->fetch_assoc();
		$id = $row["id"];
		$tipo_solicitud_id = $row["tipo_solicitud_id"];
		$tipo_empresa_id = $row["tipo_empresa_id"];
		$se_cargo_comprobante = $row["se_cargo_comprobante"];
	}
	

	// SITUACION: PAGO REALIZADO
	

	// OPCIONES DE CONCAR PARA LA EMPRESA IGH Y RECIBOS COMPARTIDOS
		
	// SELECT AL DETALLE DE LA PROGRAMACION A EDITAR

	$query_detalle_programacion = 
	"
		SELECT
			pd.id, pd.cont_ser_pub_programacion_id, pd.cont_local_servicio_publico_id
		FROM cont_ser_pub_programacion_detalle pd
		WHERE pd.status = 1 AND pd.cont_ser_pub_programacion_id = '".$programacion_id."'
	";

	$list_query_detalle = $mysqli->query($query_detalle_programacion);

	$row_count_detalle = $list_query_detalle->num_rows;

	$ids_programacion_registrado = '';
	$contador_ids = 0;
	
	if ($row_count_detalle > 0) 
	{
		while ($row = $list_query_detalle->fetch_assoc()) 
		{
			if ($contador_ids > 0) 
			{
				$ids_programacion_registrado .= ',';
			}

			$ids_programacion_registrado .= $row["cont_local_servicio_publico_id"];			
			$contador_ids++;
		}
	}
}
	// verificar si el usuario tiene todos los permisos locales 
	$usuario_id = $login?$login['id']:null;

	$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;

	$list_query_permisos = $mysqli->query($query);
	// var_dump($list_query_permisos);exit();
?>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<style>
	.highcharts-figure,
	.highcharts-data-table table {
	    min-width: 310px;
	    max-width: 800px;
	    margin: 1em auto;
	}

	#container {
	    height: 400px;
	}

	.highcharts-data-table table {
	    font-family: Verdana, sans-serif;
	    border-collapse: collapse;
	    border: 1px solid #ebebeb;
	    margin: 10px auto;
	    text-align: center;
	    width: 100%;
	    max-width: 500px;
	}

	.highcharts-data-table caption {
	    padding: 1em 0;
	    font-size: 1.2em;
	    color: #555;
	}

	.highcharts-data-table th {
	    font-weight: 600;
	    padding: 0.5em;
	}

	.highcharts-data-table td,
	.highcharts-data-table th,
	.highcharts-data-table caption {
	    padding: 0.5em;
	}

	.highcharts-data-table thead tr,
	.highcharts-data-table tr:nth-child(even) {
	    background: #f8f8f8;
	}

	.highcharts-data-table tr:hover {
	    background: #f1f7ff;
	}
</style>

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
	
	textarea {
      resize: none;
    }

</style>

<?php

if($item_atencion)
{
	// verificar si la razón social del usuario es igual a la de IGH
	$usuario_id = $login?$login['id']:null;
	
	$query_razon_social = "SELECT p.razon_social_id  FROM tbl_usuarios u LEFT JOIN tbl_personal_apt p ON u.personal_id =p.id WHERE u.id=".$usuario_id. " LIMIT 1";
	$list_query_razon_social = $mysqli->query($query_razon_social)->fetch_assoc();;
	$razon_social_idtest = $list_query_razon_social["razon_social_id"];
	if($razon_social_idtest == $razon_social_igh){
		$condicion_escision = true;
	}else{
		
		$condicion_escision = false;
	}

	?>
	<div class="content container-fluid">
		<div class="page-header wide">
			<div class="row">
				<div class="col-xs-12 text-center">
					<h1 class="page-title titulosec_contrato">
						<i class="icon icon-inline fa fa-fw fa-money"></i>
						<?php
							if($editar)
							{
								echo "Editar Programación de Pago - Servicios Públicos ID: ".$programacion_id;
							}
							else
							{
								echo "Nueva Programación de Pago - Servicios Públicos";
							}
						?>
					</h1>
				</div>
			</div>
		</div>

		<div class="col-md-12" style="margin-bottom: 10px;">
	        <a class="btn btn-primary btn-sm" id="btnRegresar" onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=servicio_publico_tesoreria')" >
	            <i class="glyphicon glyphicon-arrow-left"></i>
	            Regresar
	        </a>
	    </div>

	    <div class="row col-md-12 mt-3 mb-2">
			<div class="page-header wide">
				<fieldset class="dhhBorder">
					<legend class="dhhBorder">Búsqueda</legend>
					<form 
						id="contrato_servicio_publico_tesoreria_item_atencion_form_parametro_busqueda">
						
						<input type="hidden" id="programacion_id_edit" name="programacion_id_edit" value="<?php echo $editar ? $programacion_id : 0; ?>">

						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
							<label>Tipo Solicitud:</label>
							<select
	                            class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                            id="contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud"
	                            title="Seleccione">
	                            <?php
		                            if($row_count > 0)
		                            {
		                            	$sel_query = $mysqli->query(
		                                "
		                                    SELECT
                                                id, nombre, status
                                            FROM cont_ser_pub_tipo_solicitud
                                            WHERE id = '".$tipo_solicitud_id."'
		                                ");

			                            while($sel=$sel_query->fetch_assoc())
			                            {
			                                
			                                ?>
			                                    <option value="<?php echo $sel["id"];?>">
			                                    	<?php echo $sel["nombre"];?>
			                                    </option>
			                                <?php
			                            }
		                            }
		                            else
		                            {
		                            	?>
		                            	<option value="0" selected>-- Seleccione --</option>
		                            	<?php
		                            	$sel_query = $mysqli->query(
		                                "
		                                    SELECT 
		                                        id, nombre, status 
		                                    FROM cont_ser_pub_tipo_solicitud
		                                    WHERE status = 1 AND id IN(1,2)
		                                ");

			                            while($sel=$sel_query->fetch_assoc())
			                            {
			                                
			                                ?>
			                                    <option value="<?php echo $sel["id"];?>">
			                                    	<?php echo $sel["nombre"];?>
			                                    </option>
			                                <?php
			                            }
		                            }
	                            
	                            ?>
	                        </select>
						</div>

						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
							<label>Razón Social:</label>
							<select
	                            class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                            id="contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa"
	                            title="Seleccione">
	                            <?php
		                            if($row_count > 0)
		                            {
		                            	$sel_query = $mysqli->query(
		                                "
		                                    SELECT
											    id, nombre
											FROM tbl_razon_social
											WHERE status = 1 AND id = '".$tipo_empresa_id."'
		                                ");

			                            while($sel=$sel_query->fetch_assoc())
			                            {
			                                
			                                ?>
			                                    <option value="<?php echo $sel["id"];?>">
			                                    	<?php echo $sel["nombre"];?>
			                                    </option>
			                                <?php
			                            }
		                            }
		                            else
		                            {
		                            	?>
		                            	<option value="0" selected>-- Seleccione --</option>
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
										AND rs.permiso_servicios_publicos=1'; 
									
										if ($list_query_permisos_empresas->num_rows > 0) {
											$empresas_query.= ' AND  tul.usuario_id ='.$usuario_id.'   GROUP BY  rs.id';
										}else{
											$empresas_query.= '   GROUP BY  rs.id';
									
										} 
		                            	$sel_query = $mysqli->query($empresas_query);

			                            while($sel=$sel_query->fetch_assoc())
			                            {
			                                
			                                ?>
			                                    <option value="<?php echo $sel["id"];?>">
			                                    	<?php echo $sel["nombre"];?>
			                                    </option>
			                                <?php
			                            }
		                            }
	                            
	                            ?>
	                        </select>
						</div>

						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-3" style="<?php echo ($condicion_escision == true) ? 'display: block; padding-top: 0px; margin-top: 0px; margin-bottom: 10px;' : 'display: none;'; ?>">
							<label>Servicio:</label>
							<select
	                            class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                            id="contrato_servicio_publico_tesoreria_item_atencion_tipo_servicio"
	                            title="Seleccione">
									<option value="0" selected>Todos</option>
									<option value="2">Agua</option>
									<option value="1">Luz</option>            
									</select>	
						</div>

						<?php 
						if($row_count == 0)
						{
							?>
                        	<div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
								<button
									type="button"
									name="contrato_servicio_publico_tesoreria_item_atencion_btn_buscar_recibos_pendiente_pago"
									id="contrato_servicio_publico_tesoreria_item_atencion_btn_buscar_recibos_pendiente_pago"
									value="1"
									class="btn btn-success btn-block btn-sm"
									data-button="request"
									data-toggle="tooltip"
									data-placement="top"
									title="Buscar"
									style="position: relative; bottom: -19px; margin-bottom: 30px;">
									<i class="glyphicon glyphicon-search"></i>
									Buscar
								</button>
							</div>
                        	<?php
						}
						?>

					</form>

				</fieldset>
			</div>
		</div>

		<div id="servicio_publico_item_atencion_div_recibos" style="display: none;">
			
			<div class="row mt-3 mb-2 col-xs-12 col-sm-12 col-md-6 col-lg-6">
				<div class="table-responsive" 
					id="servicio_publico_item_atencion_div_recibos_pendiente_pago">
					<table class="table table-bordered" style="font-size: 12px;">
						<thead>
							<tr>
								<th colspan="10" style="background-color: #E5E5E5">
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">
										Recibos:
									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="text-align: center;"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class="row mt-2 mb-2 col-xs-12 col-sm-12 col-md-6 col-lg-6">
				<div class="table-responsive" 
					id="servicio_publico_item_atencion_div_recibos_pendiente_pago_en_la_programacion">
					<table class="table table-bordered" style="font-size: 12px;">
						<thead>
							<tr>
								<th colspan="10" style="background-color: #E5E5E5;">
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">
										Recibos que integran la programación de pago:
									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="10" style="text-align: center;"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			
			<?php 
				if($se_cargo_comprobante == 0)
				{
					?>
					<div class="row mt-2 mb-2" style="text-align: right;">

						<?php
						if ($editar) 
						{
							$titulo = 'Guardar cambios';
							$funcion = 'contrato_servicio_publico_tesoreria_item_atencion_guardar_programacion(2);';
						} 
						else
						{
							$titulo = 'Grabar';
							$funcion = 'contrato_servicio_publico_tesoreria_item_atencion_guardar_programacion(1);';
						}
						?>

						<button 
							type="button"
							class="btn btn-success" 
							title="<?php echo $titulo; ?>" 
							onclick="<?php echo $funcion; ?>">
							<i class="fa fa-save"></i>
							<?php echo $titulo; ?>
						</button>
					</div>
					<?php
				}
			?>
		</div>
	</div>
	<?php
}
else if($item_detalle_programacion)
{
	$sel_query = $mysqli->query("
		SELECT
			p.id,
		    p.tipo_solicitud_id,
			spts.nombre AS tipo_solicitud_nombre,
			rs.nombre AS tipo_empresa_nombre,
			rs.id AS razon_social_empresa_id,
			p.situacion_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_creacion,
		    p.created_at AS fecha_programacion,
		    tesp.nombre AS situacion_programacion,
		    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, '')) AS usuario_carga_comprobante,
		    p.fecha_comprobante,
		    p.fecha_carga_comprobante,
		    p.numero_comprobante_concar
		FROM cont_ser_pub_programacion p
			INNER JOIN cont_ser_pub_tipo_solicitud spts
			ON p.tipo_solicitud_id = spts.id
			INNER JOIN tbl_razon_social rs
			ON p.tipo_empresa_id = rs.id
			INNER JOIN tbl_usuarios tu
			ON p.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_tipo_estado_servicio_publico tesp
			ON p.situacion_id = tesp.id
			LEFT JOIN tbl_usuarios tuc
			ON p.user_id_carga_comprobante = tuc.id
			LEFT JOIN tbl_personal_apt tpc
			ON tuc.personal_id = tpc.id
		WHERE p.id = '".$servicio_publico_programacion_id."'
	");

	
	while($sel = $sel_query->fetch_assoc())
	{
		$tipo_solicitud_id = $sel["tipo_solicitud_id"];
		$tipo_solicitud_nombre = $sel["tipo_solicitud_nombre"];
		$razon_social_id = $sel["razon_social_empresa_id"];
		$tipo_empresa_nombre = $sel["tipo_empresa_nombre"];
		$programacion_situacion_id = $sel["situacion_id"];
		$usuario_creacion = $sel["usuario_creacion"];
		$fecha_programacion = $sel["fecha_programacion"];
		$situacion_programacion = $sel["situacion_programacion"];
		$usuario_carga_comprobante = $sel["usuario_carga_comprobante"];
		$fecha_comprobante = $sel["fecha_comprobante"];
		$fecha_carga_comprobante = $sel["fecha_carga_comprobante"];
		$numero_comprobante_concar = $sel["numero_comprobante_concar"];
	}


	?>
	<div class="content container-fluid">
		<input type="hidden" id="servicio_publico_programacion_id" value="<?php echo $servicio_publico_programacion_id ?>">
	    <div class="page-header wide" style="margin-bottom: 10px;">
	        <div class="row">
	            <div class="col-xs-12 text-center">
	                <h1 class="page-title titulosec_contrato">
	                	<i class="icon icon-inline glyphicon glyphicon-briefcase"></i>
	                	Detalle Programación de Servicios Públicos - ID: <?php echo $servicio_publico_programacion_id ?>
	            	</h1>
	            </div>
	        </div>
	    </div>

	    <div class="col-md-12" style="margin-bottom: 10px;">
	        <a class="btn btn-primary btn-sm" id="btnRegresar" onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=servicio_publico_tesoreria')" >
	            <i class="glyphicon glyphicon-arrow-left"></i>
	            Regresar
	        </a>
	    </div>

	    <div class="col-xs-12 col-md-12 col-lg-8">
			<div class="panel" id="">
				<div class="panel-heading">
					<div class="panel-title" style="width: 300px; display: inline-block;">DETALLE DE LA PROGRAMACIÓN</div>
				</div>

				<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
					<div class="col-xs-12 col-md-12 col-lg-12">
						<div class="h5"><b>DATOS GENERALES</b></div>
					</div>

					<div class="col-xs-12 col-md-12 col-lg-12">
						<div id="divTablaGenerales" class="form-group">

							<table class="table table-bordered table-hover">
								<tr>
									<td style="width: 250px;"><b>Tipo Solicitud:</b></td>
									<td><?php echo $tipo_solicitud_nombre;?></td>
								</tr>
								<tr>
									<td style="width: 250px;"><b>Razón Social:</b></td>
									<td><?php echo $tipo_empresa_nombre;?></td>
								</tr>
								<tr>
									<td style="width: 250px;"><b>Creado Por:</b></td>
									<td><?php echo $usuario_creacion;?></td>
								</tr>
								<tr>
									<td style="width: 250px;"><b>Fecha Programación:</b></td>
									<td><?php echo $fecha_programacion;?></td>
								</tr>
								<tr>
									<td style="width: 250px;"><b>Situación:</b></td>
									<td><?php echo $situacion_programacion;?></td>
								</tr>
							</table>
						</div>
					</div>
				</div>

				<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
				
					<div class="col-xs-12 col-md-12 col-lg-12">
						<div class="h5">
							<b>DATOS DE LA PROGRAMACIÓN - RECIBOS QUE INTEGRAN LA PROGRAMACIÓN</b>
						</div>
					</div>

					<div class="col-xs-12 col-md-12 col-lg-12">
						<div id="" class="form-group">

							<?php
								// verificar si el usuario tiene todos los permisos locales 
								$query_edit = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;
								$list_query_permisos_edit = $mysqli->query($query_edit);

					            
								$query_programacines = "
								SELECT
									pd.id,
									CONCAT('[' ,l.cc_id, '] ', l.nombre) AS local,
								    sp.inmueble_suministros_id,
								    cis.tipo_servicio_id AS tipo_servicio_publico,
									cis.nro_suministro AS suministro,
									cis.tipo_compromiso_pago_id,
									cis.nombre_beneficiario,
									cis.nro_cuenta_soles,
									cis.nro_documento_beneficiario,
									td.nombre AS tipo_documento_beneficiario,
									tps.nombre AS compromiso_pago,
								    sp.id_tipo_servicio_publico,
									tsp.nombre AS tipo_servicio_nombre,
									ea.nombre_comercial AS empresa_agua_nombre_comercial,
									el.nombre_comercial AS empresa_luz_nombre_comercial,
								    sp.periodo_consumo,
									sp.total_pagar AS total_pagado,
									pd.num_transferencia_banco,
									sp.estado,
									esp.nombre as estado_nombre
								FROM cont_ser_pub_programacion_detalle pd
									INNER JOIN cont_local_servicio_publico sp
									ON pd.cont_local_servicio_publico_id = sp.id
									INNER JOIN tbl_locales l
									ON sp.id_local = l.id
									INNER JOIN cont_inmueble_suministros cis
									ON sp.inmueble_suministros_id = cis.id
									INNER JOIN cont_inmueble i
									ON cis.inmueble_id = i.id
									INNER JOIN cont_tipo_pago_servicio tps
									ON cis.tipo_compromiso_pago_id = tps.id
									INNER JOIN cont_tipo_servicio_publico tsp
									ON sp.id_tipo_servicio_publico = tsp.id
									INNER JOIN cont_tipo_estado_servicio_publico AS esp 
									ON sp.estado = esp.id
									LEFT JOIN cont_local_servicio_publico_empresas ea
									ON i.id_empresa_servicio_agua = ea.id
									LEFT JOIN tbl_tipo_documento td
									ON cis.tipo_documento_beneficiario = td.id
									LEFT JOIN cont_local_servicio_publico_empresas el
									ON i.id_empresa_servicio_luz = el.id
									LEFT JOIN tbl_usuarios_locales tul 
									on tul.local_id = l.id
								WHERE pd.status = 1 AND pd.cont_ser_pub_programacion_id = '".$servicio_publico_programacion_id."'
					            ";

								if ($list_query_permisos_edit->num_rows > 0) {
									$query_programacines.= ' AND  tul.usuario_id ='.$usuario_id.' GROUP BY  pd.id';
								}else{
									$query_programacines.= ' GROUP BY   pd.id';
							
								} 
								$sel_query_programacion_detalle = $mysqli->query($query_programacines);
					            $cant_programacion_detalle = $sel_query_programacion_detalle->num_rows;
					        ?>

					        <div class="col-md-12" style="margin-bottom: 10px; padding-right: 0px; text-align: right;">
					        	<button 
						        	class="btn btn-success btn-sm" 
						        	onclick="sec_contrato_servicio_publico_tesoreria_item_detalle_programacion_btn_export(<?php echo $servicio_publico_programacion_id .",". $tipo_solicitud_id; ?>);" 
						        	>
	                                <span class="glyphicon glyphicon-download-alt"></span>
	                                Descargar
	                            </button>
	                        </div>

							<table class="table table-bordered table-hover" 
								id="contrato_servicio_publico_tesoreria_tabla_form_programacion_detalle">
								 <thead>
					                <tr>
					                    <th class="text-center">Nº</th>
					                    <th class="text-center">ID</th>
					                    <th class="text-center">Local</th>
					                    <th class="text-center"># Suministro</th>
					                    <th class="text-center">Servicio</th>
					                    <th class="text-center">Empresa</th>
					                    <th class="text-center">Periodo</th>
					                    <th class="text-center">Monto</th>
					                    <?php
					                    if($tipo_solicitud_id == 1)
					                    {
					                    	// tipo_solicitud_id: Recibos Totales

					                    	if((array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])) AND array_key_exists($menu_id,$usuario_permisos) && in_array("add_pago", $usuario_permisos[$menu_id]))
						                    {
						                    	?>
			                            		<th class="text-center">Nº Transferencia</th>
			                            		<?php
						                    }
					                    }
										elseif($tipo_solicitud_id == 2)
					                    {
											?>
			                            		<th class="text-center">Nombre Beneficiario</th>
												<th class="text-center">Tipo Documento</th>
												<th class="text-center">Número Documento Beneficiario</th>
			                            		<th class="text-center">Número Cuenta Soles</th>

			                            		<?php
										}
					                    if($programacion_situacion_id == 7 || $razon_social_id == $razon_social_igh)
					                    {
					                    	// SITUACION: PENDIDNTE DE PAGO
					                    	?>
					                    		<th class="text-center">Quitar</th>
					                    	<?php
					                    }
					                    ?>
					                </tr>
					            </thead>
					            <tbody>
					            	<?php
					            	if($cant_programacion_detalle > 0)
					            	{
					            		$programacion_num_cant = 1;

				                        while($row=$sel_query_programacion_detalle->fetch_assoc())
				                        {
				                        	$programacion_detalle_id = $row["id"];
											$local = $row["local"];
											$tipo_servicio_publico = $row["tipo_servicio_publico"];
											$suministro = $row["suministro"];
											$tipo_servicio_nombre = $row["tipo_servicio_nombre"];
											$periodo_consumo = contrato_servicio_publico_tesoreria_item_detalle_programacion_nombre_mes($row["periodo_consumo"]);
											$total_pagado = $row["total_pagado"];
											$num_transferencia_banco = $row["num_transferencia_banco"];
											$nombre_beneficiario = $row["nombre_beneficiario"];
											$nro_cuenta_soles = $row["nro_cuenta_soles"];
											$tipo_documento_beneficiario = $row["tipo_documento_beneficiario"];
											$nro_documento_beneficiario = $row["nro_documento_beneficiario"];


											$empresa_servicio_publico = "El servicio publico no es Agua ni Luz.";

											if($tipo_servicio_publico == 1)
											{
												// LUZ
												$empresa_servicio_publico = $row["empresa_luz_nombre_comercial"];
											}
											else if($tipo_servicio_publico == 2)
											{
												// AGUA
												$empresa_servicio_publico = $row["empresa_agua_nombre_comercial"];
											}

				                        	?>
					                        <tr 
					                        	data-programacion_detalle_id = "<?php echo $programacion_detalle_id;?>"

					                        	data-programacion_detalle_num_transferencia = "<?php echo $num_transferencia_banco;?>"
					                        	>
					                        	<td class="text-center">
					                            	<?php echo $programacion_num_cant;?>
					                            </td>
					                            <td class="text-center">
					                            	<?php echo $programacion_detalle_id;?>
					                            </td>
					                            <td class="text-center">
					                            	<?php echo $local;?>
					                            </td>
					                            <td class="text-center">
					                            	<?php echo $suministro;?>
					                            </td>
					                            <td class="text-center">
					                            	<?php echo $tipo_servicio_nombre;?>
					                            </td>

					                            <td class="text-center">
					                            	<?php echo $empresa_servicio_publico;?>
					                            </td>

					                            <td class="text-center">
					                            	<?php echo $periodo_consumo;?>
					                            </td>
					                            <td class="text-center">
					                            	<?php echo $total_pagado;?>
					                            </td>

					                            <?php
					                            	if($tipo_solicitud_id == 1)
					                            	{
					                            		// tipo_solicitud_id: Recibos Totales
					                            		if((array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])) AND array_key_exists($menu_id,$usuario_permisos) && in_array("add_pago", $usuario_permisos[$menu_id]))
						                            	{
						                            		?>
						                            		<td class="text-center">
																<div style="display: flex;gap:1px">
																	<input 
																		type="text" 
																		name="num_transferencia_banco" 
																		class="form-control num_transferencia_banco" 
																		autocomplete="off" 
																		value="<?php echo $num_transferencia_banco;?>"
																		maxlength=10
																		style="width:100px;">
																	<button type="button" class="btn btn-sm btn-success contrato_servicio_publico_tesoreria_programacion_detalle_btn_guardar_transferencia"><i class="fa fa-save"></i></button>
																</div>
									                        </td>
						                            		<?php
						                            	}	
					                            	}
													elseif($tipo_solicitud_id == 2)
													{
														?>
															<td class="text-center">
																<?php echo $nombre_beneficiario;?>
															</td>
															<td class="text-center">
																<?php echo $tipo_documento_beneficiario;?>
															</td>
															<td class="text-center">
																<?php echo $nro_documento_beneficiario;?>
															</td>
															<td class="text-center">
																<?php echo $nro_cuenta_soles;?>
															</td>
			
															<?php
													}
													if($programacion_situacion_id == 7 || $razon_social_id == $razon_social_igh)
						                            {
						                            	// SITUACION: PENDIDNTE DE PAGO
						                            	?>
						                            	<td class="text-center">
							                            	<button type="button" class="btn text-center btn-danger btn-sm" title="Quitar" 
																onclick="contrato_servicio_publico_tesoreria_item_detalle_programacion_anular_detalle(<?php echo $programacion_detalle_id ?>);">
														  		<i class="glyphicon glyphicon-trash"></i>
															</button>
														
							                            </td>
						                            	<?php
						                            }
					                            ?>
					                        </tr>
				                        	<?php

				                        $programacion_num_cant ++;
				                        
				                        }
					            	}
					            	else
					            	{
					            		?>
				                        <tr>
				                            <td colspan="6" class="text-center">NO EXISTEN REGISTROS</td>
				                        </tr>
				                        <?php
					            	}
					            	?>
					            </tbody>
							</table>
						</div>
					</div>

				</div>

			</div>
		</div>

		<div class="col-xs-12 col-md-12 col-lg-4">
			
			<div class="panel" id="">
				<div class="panel-body" style="padding: 5px 10px 5px 10px;">
					<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">
						<div class="panel">
							<div class="panel-heading" role="tab" id="browsers-this-day-heading">
								<div class="panel-title">
									<a href="#browsers-this-day" class="collapsed" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-day">
										INFORMACIÓN DE PAGO
									</a>
								</div>
							</div>
							<?php
							if($programacion_situacion_id == 7)
							{
								// SITUACION: PENDIDNTE DE PAGO

								?>

								<div id="browsers-this-day" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-this-day-heading">
									<div class="panel-body" style="margin-top: -35px;">
										<?php
										if((array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])) AND array_key_exists($menu_id,$usuario_permisos) && in_array("add_pago", $usuario_permisos[$menu_id]))
										{
											?>
											<form id="form_contrato_servicio_publico_tesoreria_item_detalle_programacion_guardar_comprobante_pago" name="form_contrato_servicio_publico_tesoreria_item_detalle_programacion_guardar_comprobante_pago" method="POST" enctype="multipart/form-data" autocomplete="off">
												<div>
													<div class="alert alert-warning small mt--4 col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 13px;">
														<i class="fa fa-info"></i> 
														Se puede subir multiples archivos presionando la tecla (ctrl) y posteriormente ir seleccionando.
													</div>

													<div class="form-group col-xs-12 col-md-12 col-lg-12" style="padding-top: 0px; margin-top: 0px; margin-bottom: 10px;">
														<label for="contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago">Seleccione Archivo - Comprobante de Pago:</label>
														<div class="input-container" style="margin-bottom: 0px;">
															<input type="file" id="contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago" name ="contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago[]" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

															<button class="browse-btn" id="contrato_servicio_publico_tesoreria_item_detalle_programacion_btn_buscar_comprobante">
																Seleccionar
															</button>
															<span class="file_info" id="contrato_servicio_publico_tesoreria_item_detalle_programacion_txt_comprobante_archivo"></span>
														</div>
													</div>
													
													<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 0px; margin-top: 10px; margin-bottom: 10px;">
														<label for="start">Seleccione Fecha de Carga:</label>
														<div class="input-group">
															<input
																	type="text"
																	name="contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago"
																	id="contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago"
																	class="form-control sec_contrato_servicio_publico_tesoreria_datepicker"
																	value=""
																	style="height: 30px;"
																	>
															<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago"></label>
														</div>
													</div>
												</div>

												<div style="margin-right: 10px; margin-left: 5px; margin-top: 30px; padding-top: 50px;">
													<button type="submit" class="btn btn-success btn-xs btn-block" id="btn_guardar_comprobante_pago">
														<i class="icon fa fa-save"></i>
														<span id="demo-button-text">Guardar Comprobante de Pago</span>
													</button>
												</div>
											</form>
											<?php
										}
										else
										{
											?>
											<table class="table table-responsive table-hover no-mb" style="font-size: 10px; margin-top: 20px;">
											<tbody>
												<tr style="text-transform: none; font-size: 13px;">
													<td><b>Aún no se realiza el pago</b></td>
												</tr>
											</tbody>
										</table>
											<?php
										}
										?>
									</div>
								</div>

								<?php 

							}
							else if($programacion_situacion_id == 8)
							{
								// SITUACION: PAGO REALIZADO
								?>
								<div id="browsers-this-day" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-this-day-heading">
									<div class="panel-body">
										
										<div id="contrato_servicio_publico_tesoreria_item_detalle_programacion_mostrar_comprobante_div">
											<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
												<tbody>
													<?php

													if((array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])) AND array_key_exists($menu_id,$usuario_permisos) && in_array("add_pago", $usuario_permisos[$menu_id]))
													{
														?>
														<div style="margin-right: 20px; margin-left: 20px;">
															<button type="button" class="btn btn-info btn-xs btn-block" onclick="contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante();">
																<i class="icon fa fa-pencil"></i>
																<span id="demo-button-text">Editar Comprobante</span>
															</button>
														</div>
														<?php
													}

													?>
													
													<tr style="text-transform: none; font-size: 13px;">
														<td><b>Atendido por:</b></td>
														<td>
															<?php echo $usuario_carga_comprobante; ?>
														</td>
													</tr>
													<tr style="text-transform: none; font-size: 13px;">
														<td><b>Fecha comprobante:</b></td>
														<td>
															<?php echo $fecha_comprobante; ?>
														</td>
													</tr>
													<tr style="text-transform: none; font-size: 13px;">
														<td><b>Fecha carga comprobante:</b></td>
														<td>
															<?php echo $fecha_carga_comprobante; ?>
														</td>
													</tr>

													<?php 
													
													$query_programacion_coprobante_pago = "
															SELECT
																id, extension, download
															FROM cont_ser_pub_programacion_files
															WHERE cont_ser_pub_programacion_id = '".$servicio_publico_programacion_id."' AND status = 1
															";

													$query_files = $mysqli->query($query_programacion_coprobante_pago);
													$files_count = $query_files->num_rows;
													
													if($files_count > 0)
													{
														?>
														<tr style="text-transform: none; font-size: 13px;">
															<td colspan="2" style="text-align: center;"><b>Comprobantes de Pagos:</b></td>
														</tr>

														<?php
														$cant = 1;
														while ($row = $query_files->fetch_assoc())
														{
															$file_id = $row["id"];
															$file_extension = $row["extension"];
															$file_download = $row["download"];

															?>

															<tr style="text-transform: none; font-size: 13px;">
																<td><b>File Nº <?php echo $cant; ?>:</b></td>
																<td>
																	<button type="button" class="btn btn-success btn-xs btn-block"
																		onclick="contrato_servicio_publico_tesoreria_item_detalle_programacion_ver_comprobante_pago(<?php echo "'$file_extension'" ;?>, <?php echo "'$file_download'" ;?>);" title="Ver documento">
																		<i class="icon fa fa-eye"></i>
																		<span id="demo-button-text">Ver Archivo - Comprobante</span>
																	</button>
																</td>
															</tr>

															<?php

															$cant++;
														}
													}
													else
													{
														?>
														<tr style="text-transform: none; font-size: 13px;">
															<td colspan="2" style="text-align: center;"><b>No existen Comprobantes de Pagos:</b></td>
														</tr>

														<?php
													}
													?>

												</tbody>
											</table>
										</div>
										
										<div id="contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_div" style="display: none;">
											<div style="margin-right: 20px; margin-left: 20px;">
												<button type="button" class="btn btn-warning btn-xs btn-block" onclick="contrato_servicio_publico_tesoreria_item_detalle_programacion_mostrar_comprobante();">
													<i class="icon fa fa-eye"></i>
													<span id="demo-button-text">Omitir Editar Comprobante</span>
												</button>
											</div>

											<form id="form_contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_pago" name="form_prestamo_tesoreria_editar_comprobante_pago" method="POST" enctype="multipart/form-data" autocomplete="off">
												<div>
													<div class="alert alert-warning small mt--4 col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 13px;">
														<i class="fa fa-info"></i> 
														Se puede subir multiples archivos presionando la tecla (ctrl) y posteriormente ir seleccionando.
													</div>

													<div class="form-group col-xs-12 col-md-12 col-lg-12" style="padding-top: 0px; margin-top: 10px; margin-bottom: 10px;">
														<label for="contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit">Seleccione Archivo - Comprobante de Pago:</label>
														<div class="input-container" style="margin-bottom: 0px;">
															<input type="file" id="contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit" name ="contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit[]" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

															<button class="browse-btn" id="contrato_servicio_publico_tesoreria_item_detalle_programacion_btn_buscar_comprobante_edit">
																Seleccionar
															</button>
															<span class="file-info" id="contrato_servicio_publico_tesoreria_item_detalle_programacion_txt_comprobante_archivo_edit"></span>
														</div>
													</div>

													<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 0px; margin-top: 10px; margin-bottom: 10px;">
														<label for="start">Seleccione Fecha de Carga:</label>
														<div class="input-group">
															<input
																	type="text"
																	name="contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago_edit"
																	id="contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago_edit"
																	class="form-control sec_contrato_servicio_publico_tesoreria_datepicker"
																	value=""
																	style="height: 30px;"
																	>
															<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago_edit"></label>
														</div>
													</div>

													<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 0px; margin-top: 10px; margin-bottom: 10px;">
														<label for="start">Motivo:</label>
														<textarea type="text" id="contrato_servicio_publico_tesoreria_item_detalle_programacion_motivo_comprobante_pago_edit" class="form-control" autocomplete="off" maxlength="100" placeholder="Ingrese el motivo" cols="4"></textarea>
													</div>

												</div>

												<div style="margin-right: 10px; margin-left: 5px; margin-top: 30px; padding-top: 50px;">
													<button type="submit" class="btn btn-success btn-xs btn-block" id="btn_guardar_comprobante_pago_edit">
														<i class="icon fa fa-save"></i>
														<span id="demo-button-text">Editar Comprobante de Pago</span>
													</button>
												</div>
											</form>
										</div>

									</div>
								</div>
								<?php
							}
							?>
						</div>

						<?php 
						if($programacion_situacion_id == 8)
						{
							if($razon_social_id == $razon_social_igh){
								$condicion_escision = true;

							}else{
								$condicion_escision = false;

							}
		
							?>

							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-concar-heading">
									<div class="panel-title">
										<a href="#browsers-this-concar" role="button" data-toggle="collapse"
										   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-concar">
											Concar
										</a>
									</div>
								</div>

								<div id="browsers-this-concar" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-this-concar-heading">
									<div class="panel-body" style="margin-bottom: 0px !important; margin-top: 0px !important;">
										<div style="margin-top: 0px; margin-right: 10px; margin-left: 5px; padding-top: 1px;">
											<input 
												type="hidden" 
												id="contrato_servicio_publico_tesoreria_item_detalle_programacion_tipo_solicitud_id"
												value="<?php echo $tipo_solicitud_id; ?>">

											<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 0px; margin-top: 0px; margin-bottom: 10px;">
												<label for="start">Número de comprobante (correlativo): 4 digitos</label>
												<input type="text" 
													class="form-control contrato_servicio_publico_tesoreria_item_detalle_programacion_num_comprobante"
													id="contrato_servicio_publico_tesoreria_item_detalle_programacion_num_comprobante" 
													autocomplete="off" 
		     									value="<?php echo $numero_comprobante_concar; ?>">
											</div>

											<div 
												class="col-xs-12 col-sm-12 col-md-12 col-lg-12" 
												style="<?php echo ($condicion_escision == true && $tipo_solicitud_id ==2) ? 'display: block; padding-top: 0px; margin-top: 0px; margin-bottom: 10px;' : 'display: none;'; ?>">
												<label for="start">Número de movimiento de banco:</label>
													<input type="text" 
														class="form-control contrato_servicio_publico_tesoreria_item_detalle_programacion_num_movimiento"
														id="contrato_servicio_publico_tesoreria_item_detalle_programacion_num_movimiento" 
														autocomplete="off"
														value="<?php echo ($condicion_escision == true && $tipo_solicitud_id ==2) ? '' : 'no aplica'; ?>">
												</div>
												<div 
													class="col-xs-12 col-sm-12 col-md-12 col-lg-12" 
													style="<?php echo ($condicion_escision == true && ($tipo_solicitud_id ==2 || $tipo_solicitud_id ==1)) ? 'display: block; padding-top: 0px; margin-top: 0px; margin-bottom: 10px;' : 'display: none;'; ?>">
													<label for="start">Servicio:</label>
													<select
														class="form-control "
														name="form_modal_sec_mantenimiento_razon_social_param_estado_vale"
														id="form_modal_sec_mantenimiento_razon_social_param_estado_vale"
														style="width:100%;"
														title="Seleccione">     
														<?php 
															if(($condicion_escision == true && ($tipo_solicitud_id ==2 || $tipo_solicitud_id ==1))){
														?>	
															<option value="2" selected>Agua</option>
															
														<?php 
															}else{
														?>	
															<option value="0" selected>Todos</option>
															<option value="2">Agua</option>
														<?php 
															}
																 
														?>	
														<option value="1">Luz</option>            
													</select>
												</div>						
											<div style="margin-right: 10px; margin-left: 5px; margin-top: 30px; padding-top: 50px;">
												<button type="button" 
													class="btn btn-success btn-sm btn-block" 
													title="Concar Servicio Publico"
													onclick="contrato_servicio_publico_tesoreria_item_detalle_plantilla_concar_excel(<?php echo $servicio_publico_programacion_id; ?>);">
						                    		Generar
							                    	<i class="fa fa-file-excel-o"></i>
							                    </button>
											</div>

										</div>
									</div>
								</div>

							</div>

							<?php
						}
						?>
					</div>
				</div>
			</div>

		</div>
    </div>

	<?php
}
else
{
	?>
	<div class="content container-fluid">
		
		<div class="page-header wide" style="margin-bottom: 10px;">
			<div class="row">
				<div class="col-xs-12 text-center">
					<h1 class="page-title titulosec_contrato">
						<i class="icon icon-inline fa fa-fw fa-money"></i>
						Programación de pagos - Servicios Públicos
					</h1>
				</div>
			</div>
		</div>

		<div class="col-md-12" style="margin-bottom: 10px;">
			
			<?php
			if($mi_permiso === true)
			{
				?>
				<button 
					type="button"
					class="btn btn-success btn-sm" 
					data-button="request" 
					data-toggle="tooltip" 
					data-placement="top" 
					title="Crear programación" 
					onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=servicio_publico_tesoreria&item_atencion');"
				>
					<i class="fa fa-plus"></i>
					Crear programación
				</button>
				<?php
			}
			?>
	    </div>

	    <?php 

	    if($mi_permiso === true)
	    {
	    	?>
	    	<div class="content col-md-12">
	        	<div class="page-header wide">
	                <div class="row mt-4 mb-2">
						<fieldset class="dhhBorder">
							<legend class="dhhBorder">Parámetros de búsqueda</legend>
							<form>
								
								<div class="col-xs-12 col-sm-6 col-md-2 col-lg-3">
									<label>Tipo Solictud</label>
									<select
										class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
										data-live-search="true"
										name="contrato_servicio_publico_tesoreria_tipo_solicitud" 
										id="contrato_servicio_publico_tesoreria_tipo_solicitud" 
										title="Seleccione">
										<option value="0">-- Seleccione --</option>
										<?php
										$sel_query = $mysqli->query(
											"
												SELECT 
			                                        id, nombre, status 
			                                    FROM cont_ser_pub_tipo_solicitud
			                                    WHERE status = 1 AND id IN(1, 2, 3)
											");
										while($sel=$sel_query->fetch_assoc())
										{
										?>
											<option value="<?php echo $sel["id"];?>">
												<?php echo $sel["nombre"];?>
											</option>
										<?php
										}
										?>
									</select>
								</div>

								<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
	                            	style="display: none;">
	                                <label>
	                                    Buscar Por:
	                                </label>
	                                <div class="form-group">
		                                <select 
		                                	class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro" 
		                                	name="contrato_servicio_publico_tesoreria_buscar_por"
		                                	id="contrato_servicio_publico_tesoreria_buscar_por" 
		                                	title="Seleccione">
											<option value="0">-- Seleccione --</option>
											<option value="1">Periodo</option>
											<option value="2">Fecha Vencimiento</option>
										</select>
	                                </div>
	                            </div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
	                            	style="display: none;">
	                                <label>
	                                    Tipo Recibo:
	                                </label>
	                                <div class="form-group">
		                                <select 
		                                	class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro" 
		                                	name="contrato_servicio_publico_tesoreria_tipo_recibo"
		                                	id="contrato_servicio_publico_tesoreria_tipo_recibo" 
		                                	title="Todos">
											<option value="0">-- Todos --</option>
											<?php
											$sel_query = $mysqli->query(
												"
													SELECT 
				                                        id, nombre, status 
				                                    FROM cont_ser_pub_tipo_solicitud
				                                    WHERE status = 1 AND id IN(1, 2)
												");
											while($sel=$sel_query->fetch_assoc())
											{
											?>
												<option value="<?php echo $sel["id"];?>">
													<?php echo $sel["nombre"];?>
												</option>
											<?php
											}
											?>
										</select>
	                                </div>
	                            </div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
	                            	style="display: none;">
	                                <label>
	                                    Tipo de Servicio:
	                                </label>
	                                <div class="form-group">
	                                	<select 
	                                		class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro" 
	                                		name="contrato_servicio_publico_tesoreria_tipo_servicio"  
	                                		id="contrato_servicio_publico_tesoreria_tipo_servicio" 
	                                		title="Seleccione el Servicio">
										<option value="0">Todos</option>
										<?php
											$locales_command = 
											"
												SELECT 
													id, nombre 
												FROM cont_tipo_servicio_publico 
												where id IN (1, 2) AND status = 1";
										
										$locales_query = $mysqli->query($locales_command);

										while($ct=$locales_query->fetch_assoc())
										{
											?>
												<option value="<?php echo $ct["id"];?>">
													<?php echo $ct["nombre"];?>
												</option>
											<?php
										}
										?>
									</select>
	                                </div>
	                            </div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
	                            	style="display: none;">
	                                <label>
	                                    Razón Social:
	                                </label>
	                                <div class="form-group">
	                                    <select
	                                        class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                                        name="contrato_servicio_publico_tesoreria_empresa_arrendataria"
	                                        id="contrato_servicio_publico_tesoreria_empresa_arrendataria"
	                                        title="Todos la Empresa Arrendataria">
	                                        <option value="0">Todos</option>
	                                        <?php
													$usuario_id = $login?$login['id']:null;
														// verificar si el usuario tiene todos los permisos locales 
													$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;

													$list_query_permisos = $mysqli->query($query);
													 

												$query = 
												"
													SELECT 
													rs.id, rs.nombre  
													FROM tbl_razon_social rs
												
													LEFT JOIN tbl_locales_redes tlr 
													ON tlr.id = rs.red_id 
													LEFT JOIN tbl_locales tl 
													ON tl.red_id = tlr.id
													LEFT JOIN tbl_usuarios_locales tul 
													on tul.local_id = tl.id
													WHERE rs.status = 1
													
												";
												if ($list_query_permisos->num_rows > 0) {
													$query.= ' AND  tul.usuario_id ='.$usuario_id.' GROUP BY  rs.id ORDER BY rs.nombre ASC';
												}else{
													$query.= ' GROUP BY  rs.id ORDER BY rs.nombre ASC';
											
												} 
													$list_query = $mysqli->query($query);
													
													while ($li = $list_query->fetch_assoc())
													{
													?>
														<option value="<?php echo $li["id"];?>">
															<?php echo $li["nombre"];?>
														</option>
													<?php
													}
											?>
	                                    </select>
	                                </div>
	                            </div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo"
	                            	style="display: none;">
	                                <label>
	                                    Zona:
	                                </label>
	                                <div class="form-group">
	                                    <select
	                                        class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                                        name="contrato_servicio_publico_tesoreria_param_zona"
	                                        id="contrato_servicio_publico_tesoreria_param_zona"
	                                        title="Todos">
	                                        <option value="0">Todos</option>
	                                        <?php

	                                            $query = "SELECT 
														tz.id ,tz.nombre 
														FROM 
														tbl_zonas tz 
														LEFT JOIN tbl_razon_social rs
														ON rs.id = tz.razon_social_id 
														LEFT JOIN tbl_locales_redes tlr 
														ON tlr.id = rs.red_id 
														LEFT JOIN tbl_locales tl 
														ON tl.red_id = tlr.id
														LEFT JOIN tbl_usuarios_locales tul 
														on tul.local_id = tl.id
														WHERE rs.status = 1
	                                                    ";
												if ($list_query_permisos->num_rows > 0) {
													$query.= ' AND  tul.usuario_id ='.$usuario_id.' GROUP BY  tz.id ORDER BY tz.nombre ASC';
												}else{
													$query.= ' GROUP BY  tz.id ORDER BY tz.nombre ASC';
											
												} 
	                                            
	                                            $list_query = $mysqli->query($query);
	                                            
	                                            while ($li = $list_query->fetch_assoc()) 
	                                            {
	                                                ?>
	                                                    <option value="<?php echo $li["id"]; ?>">
	                                                    	<?php echo $li["nombre"]; ?>
	                                                    </option>
	                                                <?php
	                                            }
	                                        ?>
	                                    </select>
	                                </div>
	                            </div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
	                            	style="display: none;">
	                                <label>
	                                    Supervisor:
	                                </label>
	                                <div class="form-group">
	                                    <select
	                                        class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                                        name="contrato_servicio_publico_tesoreria_supervisor"
	                                        id="contrato_servicio_publico_tesoreria_supervisor"
	                                        title="Todos">
	                                        <option value="0">Todos</option>
	                                    </select>
	                                </div>
	                            </div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
	                            	style="display: none;">
	                                <label>
	                                    Local:
	                                </label>
	                                <div class="form-group">
	                                    <select
	                                        class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                                        name="contrato_servicio_publico_tesoreria_local"
	                                        id="contrato_servicio_publico_tesoreria_local"
	                                        title="Todos">
	                                        <option value="0">Todos</option>
	                                    </select>
	                                </div>
	                            </div>

	                            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-3 tipo_recibo contrato_servicio_publico_tesoreria_div_fechas" style="display: none;">
									<label id="contrato_servicio_publico_tesoreria_label_fecha_inicio">
										Fecha inicio de la programación:
									</label>
									<div class="input-group">
										<input
												type="text"
												name="contrato_servicio_publico_tesoreria_fecha_inicio"
												id="contrato_servicio_publico_tesoreria_fecha_inicio"
												class="form-control sec_contrato_servicio_publico_tesoreria_datepicker"
												value="<?php echo date("d-m-Y", strtotime("-1 days"));?>"
												style="height: 30px;"
												>
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="contrato_servicio_publico_tesoreria_fecha_inicio"></label>
									</div>
								</div>

								<div class="col-xs-12 col-sm-6 col-md-2 col-lg-3 tipo_recibo contrato_servicio_publico_tesoreria_div_fechas" style="display: none;">
									<label id="contrato_servicio_publico_tesoreria_label_fecha_fin">
										Fecha fin de la programación:
									</label>
									<div class="input-group">
										<input
												type="text"
												name="contrato_servicio_publico_tesoreria_fecha_fin"
												id="contrato_servicio_publico_tesoreria_fecha_fin"
												class="form-control sec_contrato_servicio_publico_tesoreria_datepicker"
												value="<?php echo date("d-m-Y");?>"
												style="height: 30px;"
												>
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="contrato_servicio_publico_tesoreria_fecha_fin"></label>
									</div>
								</div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
	                            	id="contrato_servicio_publico_tesoreria_div_periodo" 
	                            	style="display: none;">
	                                <label>
	                                    Periodo:
	                                </label>
	                                <div class="form-group">
	                                	<select 
	                                		class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro" 
	                                		name="contrato_servicio_publico_tesoreria_periodo"  
	                                		id="contrato_servicio_publico_tesoreria_periodo" 
	                                		title="Seleccione el Periodo">
										</select>
	                                </div>
	                            </div>

	                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 	style="display: none;">
	                                <label>
	                                    Estado:
	                                </label>
	                                <div class="form-group">
	                                    <select
	                                        class="form-control sec_contrato_servicio_publico_tesoreria_select_filtro"
	                                        name="contrato_servicio_publico_tesoreria_estado"
	                                        id="contrato_servicio_publico_tesoreria_estado"
	                                        title="Todos los Estados">
	                                        <option value="0">Todos</option>
											<?php 
											$query = 
											"
												SELECT
													id, nombre 
												FROM cont_tipo_estado_servicio_publico 
												WHERE status = 1 AND id IN(1, 2, 3, 4, 5, 6)
											";
											
											$list_query = $mysqli->query($query);
											while ($li = $list_query->fetch_assoc())
											{
												?>
													<option value="<?=$li['id']?>">
														<?=$li['nombre']?>
													</option>
												<?php
											}
											?>
	                                    </select>
	                                </div>
	                            </div>

								<div class="col-xs-12 col-sm-6 col-md-2 col-lg-3">
									<button 
										type="button" 
										name="" 
										value="1" 
										class="btn btn-success btn-block btn-sm" 
										data-button="request" 
										data-toggle="tooltip" 
										data-placement="top" 
										title="Buscar" 
										onclick="contrato_servicio_publico_tesoreria_btn_buscar();"
										style="position: relative; bottom: -19px; margin-bottom: 30px;">
										<i class="glyphicon glyphicon-search"></i>
										Buscar
									</button>
								</div>
							</form>
						</fieldset>
					</div>
				</div>

				<div class="page-header wide" id="">
	                <div class="row mt-3 mb-2">
	                    <div class="row form-horizontal">
	                        <div class="col-md-12">                   
	                            <button 
	                                class="btn btn-success btn-sm" 
	                                id="contrato_servicio_publico_tesoreria_reporte_btn_listar_servicios_publicos"
	                                style="display: none;">
	                                <span class="icon fa fa-file-excel-o" style="font-size: 14px;"></span>
	                                Exportar xls
	                            </button>
	                        </div>
	                    </div>
	                </div>
	            </div>			

				<div class="row mt-3" id="contrato_servicio_publico_tesoreria_listar_servicios_publicos_tabla" 
	            	style="display: none; width:100%;overflow: auto;">
	                <table id="contrato_servicio_publico_tesoreria_listar_servicios_publicos_datatable" class="table table-striped table-bordered table-hover table-condensed dt-responsive display" cellspacing="0" width="100%">
	                    <thead>
	                        <tr>
	                            <th class="text-center">ID</th>
	                            <th class="text-center">Jefe Comercial</th>
	                            <th class="text-center">Creado Por</th>
								<th class="text-center">Local</th>
	                            <th class="text-center">Servicio</th>
	                            <th class="text-center">Empresa</th>
	                            <th class="text-center">Periodo</th>
	                            <th class="text-center">Suministro</th>
	                            <th class="text-center">Monto</th>
	                            <th class="text-center">F. Vencimiento</th>
	                            <th class="text-center">Estado</th>
	                            <th class="text-center">Monto Total</th>
	                            <th class="text-center">Detalle</th>
	                        </tr>
	                    </thead>
	                    <tbody>
	                    </tbody>
	                </table>
	            </div>

				<div class="row mt-3" id="contrato_servicio_publico_tesoreria_programacion_div_tabla" 
					style="display: none;">
			        <table id="contrato_servicio_publico_tesoreria_programacion_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%">
			            <thead>
			                <tr>
			                    <th>ID</th>
								<th>Tipo Solicitud</th>
								<th>Razón Social</th>
								<th>F. Programación</th>
								<th>Importe</th>
								<th>Usuario Creación</th>
								<th>Situación</th>
								<th>Opciones</th>
			                </tr>
			            </thead>
			            <tbody>
			            </tbody>
			        </table>
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
	<?php
}

if($editar)
{
	?>
	<script>

		setTimeout(function(){
			contrato_servicio_publico_tesoreria_item_atencion_agregar_varios_recibo_a_la_programacion_pagos(<?php echo $ids_programacion_registrado; ?>)
		}, 2000);
		
	</script>
	<?php 
}

function contrato_servicio_publico_tesoreria_item_detalle_programacion_nombre_mes($fecha)
{
	if (Empty($fecha))
	{
		return '';
	}

	$anio = date("Y", strtotime($fecha));
	$mes = date("m", strtotime($fecha));
	$nombre_mes = "";
	switch ($mes)
	{
		case '01': $nombre_mes = "Enero"; break;
		case '02': $nombre_mes = "Febrero"; break;
		case '03': $nombre_mes = "Marzo"; break;
		case '04': $nombre_mes = "Abril"; break;
		case '05': $nombre_mes = "Mayo"; break;
		case '06': $nombre_mes = "Junio"; break;
		case '07': $nombre_mes = "Julio"; break;
		case '08': $nombre_mes = "Agosto"; break;
		case '09': $nombre_mes = "Septiembre"; break;
		case '10': $nombre_mes = "Octubre"; break;
		case '11': $nombre_mes = "Noviembre"; break;
		case '12': $nombre_mes = "Diciembre"; break;
	}

	return $nombre_mes." del ".$anio;
}

?>

<!-- INICIO MODAL AGREGAR MONTO -->
<div id="sec_con_serv_pub_modal_agregar_monto" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 90%;">
		<div class="modal-content">
			<div class="modal-header">
				<input type="hidden" name="sec_con_serv_pub_id_archivo" id="sec_con_serv_pub_id_archivo">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" id="sec_con_serv_pub_title_modal_agregar_monto" style="text-align: center;"></h3>
				<h5 class="modal-title" id="sec_con_serv_pub_title_modal_agregar_monto_local" style="text-align: center; font-weight: bold;"></h5>
			</div>
			<div class="modal-body">
				<form id="sec_con_ser_pub_agregar_monto_recibo" name="sec_con_ser_pub_agregar_monto_recibo" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-12">
					    	<!--RECIBO-->
					        <div class="col-md-4">
					        	<div class="panel">
									<div class="panel-body" style="padding: 0px !important; padding-top: 15px !important;">	
										<h4 id="sec_con_serv_pub_mensaje_imagen" style="text-align: center;"></h4>
										<div class="row" style="margin-bottom: 20px;" id="sec_con_serv_pub_div_imagen_recibo">
											
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_div_VerImagenFullPantalla">
											<button type="button" class="btn btn-block btn-block btn-info" id="sec_con_serv_pub_ver_full_pantalla">
												<i class="fa fa-arrows-alt"></i>  Pantalla Completa
											</button>
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_btn_descargar_imagen_recibo">											
											<a id="sec_con_serv_pub_descargar_imagen_a" class="btn btn-block btn-block btn-success"><i class="fa fa-arrow-circle-down"></i> Descargar</a>
										</div>
									</div>
								</div>
					        </div>

					        <!--FILE CONTOMETRO-->
					        <div class="col-md-4" 
					        	id="sec_contrato_servicio_publico_modal_agregar_monto_div_file_contometro"
					        	style="display: none;" 
					        	>
					        	<div class="panel">
									<div class="panel-body" style="padding: 0px !important; padding-top: 15px !important;">	
										<h4 id="sec_con_serv_pub_mensaje_imagen_contometro" style="text-align: center;"></h4>
										<div class="row" style="margin-bottom: 20px;" id="sec_con_serv_pub_div_imagen_recibo_contometro">
											
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_div_VerImagenFullPantalla_contometro">
											<button type="button" class="btn btn-block btn-block btn-info" id="sec_con_serv_pub_ver_full_pantalla_contometro">
												<i class="fa fa-arrows-alt"></i>  Pantalla Completa
											</button>
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_btn_descargar_imagen_recibo_contometro">											
											<a id="sec_con_serv_pub_descargar_imagen_a_contometro" class="btn btn-block btn-block btn-success"><i class="fa fa-arrow-circle-down"></i> Descargar</a>
										</div>
									</div>
								</div>
					        </div>

					        <!--DATOS-->
					        <div class="col-md-3">
					        	<div class="col-md-12" style="text-align: right;">
					        		<label id="sec_con_serv_pub_periodo_busqueda" style="text-align: center;"></label>
					        		<input type="hidden" id="sec_con_serv_pub_periodo_busqueda_data" style="text-align: center;"></label>
					        		<button type="button" class="btn btn-info btn-sm" id="sec_con_serv_pub_btn_navegar_periodo" onclick="sec_con_serv_pub_navegar_periodo(-1)"><i class="fa fa-chevron-left"></i></button>
					        		<button type="button" class="btn btn-info btn-sm" id="sec_con_serv_pub_btn_navegar_periodo" onclick="sec_con_serv_pub_navegar_periodo(1)"><i class="fa fa-chevron-right"></i></button>
					        	</div>
					        	<div class="col" id="sec_con_serv_pub_div_datos_recibo">
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Número de suministro: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" disabled name="sec_con_serv_pub_num_suministro" id="sec_con_serv_pub_num_suministro" placeholder="123456789" style="text-align: right;" />
										</div>
									</div>
									<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Serie: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" name="sec_con_serv_pub_serie" id="sec_con_serv_pub_serie" style="text-align: right; font-weight: bold;" />
										</div>
									</div>
									<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Número de recibo: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" name="sec_con_serv_pub_num_recibo" id="sec_con_serv_pub_num_recibo" placeholder="000000010" style="text-align: right; font-weight: bold;" />
										</div>
									</div>
									<div class="form-group col-md-12" id="div_con_serv_pub_comentario" style="width:  100%; display:none">
										<label style="text-align: left;">Comentario: </label>
										<div class="input-group" style="width: 100%;">
											<textarea disabled class="form-control" name="sec_con_serv_pub_comentario" id="sec_con_serv_pub_comentario"  rows="3"></textarea>
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Periodo de consumo: </label>
										<div class="input-group" style="width: 100%;">
											<input type="month" class="form-control" name="sec_con_serv_pub_periodo_consumo" id="sec_con_serv_pub_periodo_consumo" placeholder="123456789" style="text-align: right;" />
										</div>
										
										
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Fecha de emisión: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control servicio_publico_datepicker" name="sec_con_serv_pub_fecha_emision" id="sec_con_serv_pub_fecha_emision" style="text-align: right;" />
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Fecha de vencimiento: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control servicio_publico_datepicker" name="sec_con_serv_pub_fecha_vencimiento" id="sec_con_serv_pub_fecha_vencimiento" style="text-align: right;" />
										</div>
									</div>
									<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Compromiso de pago: </label>
										<input type="hidden" name="sec_con_serv_pub_id_tipo_compromiso" id="sec_con_serv_pub_id_tipo_compromiso">
										<input type="hidden" name="sec_con_serv_pub_id_tipo_recibo" id="sec_con_serv_pub_id_tipo_recibo">
										<input type="hidden" name="sec_con_serv_pub_id_local" id="sec_con_serv_pub_id_local">
										<input type="hidden" name="sec_con_serv_pub_id_recibo" id="sec_con_serv_pub_id_recibo">
										<input type="hidden" name="sec_con_serv_pub_id_monto_pct" id="sec_con_serv_pub_id_monto_pct">
										<div class="input-group" style="width: 100%;">
											<select style="display:none;" class="form-control input_text col-5" name="sec_con_ser_pub_select_compromiso_pago"  id="sec_con_ser_pub_select_compromiso_pago" title="Seleccione el Jefe Comercial">
											</select>
											<input type="text" disabled class="form-control" name="sec_con_serv_pub_id_tipo_compromiso_nombre" id="sec_con_serv_pub_id_tipo_compromiso_nombre" placeholder="" style="text-align: right;" />
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Total mes actual: <span style="color: red;">(*)</span></label>
										<div class="input-group" style="width: 100%;">
											<input type="number" class="form-control" name="sec_con_serv_pub_monto_mes_actual" id="sec_con_serv_pub_monto_mes_actual" placeholder="Ejm: 125.60" style="text-align: right; font-weight: bold;" />
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Total a pagar: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control"  name="sec_con_serv_pub_total_pagar" id="sec_con_serv_pub_total_pagar" placeholder="0.00" style="text-align: right;" />
										</div>
									</div>
									<div class="form-group col-md-12" id="sec_con_serv_pub_check_caja_chica_div" style="width: 100%; margin-top: 10px;">
										<input type="checkbox" value="" id="sec_con_serv_pub_check_cajachica_id" />
										<label for="sec_con_serv_pub_check_cajachica_id">Caja Chica</label>
									</div>
									<div class="form-group col-md-12 ocultar_div" style="width: 100%;" id="sec_con_serv_pub_div_nombre_pagar_hide">
										<label style="text-align: left;">Ingrese el email de la persona que se notificara: <span style="color: red;">(*)</span></label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" name="sec_con_serv_pub_nombre_pagar" id="sec_con_serv_pub_nombre_pagar" placeholder="Ejm: maria.casas@testtest.apuestatotal.com" style="text-align: right; font-weight: bold;" maxlength="100" />
										</div>
									</div>
									<!--<div class="form-group col-md-12" style="width: 100%;">
										<label style="text-align: left;">Observación: </label>
										<div class="input-group" style="width: 100%;">
											 <textarea class="form-control" id="sec_con_serv_pub_observacion" rows="3"></textarea>
										</div>
									</div>-->
					        	</div>
					        	
					        </div>
					        <div class="col-md-5">
								<figure class="highcharts-figure">
								    <div id="container"></div>
								    <!--<p class="highcharts-description"></p>-->
								</figure>
							</div>
					    </div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR MONTO -->

<!-- INICIO MODAL OBSERVAR SERVICIO -->
<div id="sec_con_serv_pub_modal_observar_servicio" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" style="text-align: center;">Agregar Observación</h3>
			</div>
			<div class="modal-body">
				<div class="form-group" style="width: 100%;">
					<label style="text-align: left;">Observación: </label>
					<div class="input-group" style="width: 100%;">
						 <textarea class="form-control" id="sec_con_serv_pub_observacion" rows="6"></textarea>
					</div>
				</div>

				<div class="form-group" style="width: 100%;">
					<label style="text-align: left;">Correos: </label>
					<div class="input-group" style="width: 100%;">
						 <textarea placeholder="Ingrese los correos separados con ','" class="form-control" id="sec_con_serv_pub_observacion_correos" rows="4"></textarea>
						 <b>Nota: Para más de un correo se debe separar por comas (,)</b>
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_observar_archivo" onclick="observarServicioPublicoTesoreria()">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL OBSERVAR SERVICIO -->

<!-- INICIO MODAL AGREGAR RECIBO -->
<div id="sec_con_serv_pub_modal_agregar_recibo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 55%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" style="text-align: center;" id="sec_serv_pub_modal_title_agregar_recibo">Agregar Recibo</h3>
			</div>
			<div class="modal-body">
				<form id="sec_con_serv_pub_form_modal_agregar_recibo" method="POST" enctype="multipart/form-data">
					<div class="form-group col-md-4">
						<label>Fecha emision:</label>
						<div class="input-group">
							<input type="hidden" id="sec_con_serv_pub_fecha_emision_recibo" class="filtro"
                                data-col="sec_con_serv_pub_fecha_emision_recibo" name="sec_con_serv_pub_fecha_emision_recibo" value="<?php echo date("Y-m-d"); ?>"
                                data-real-date="sec_con_serv_pub_txt_fecha_emision">
                            <input type="text" class="form-control servicio_publico_fecha_emision_datepicker"
                                id="sec_con_serv_pub_txt_fecha_emision" value="<?php echo date("d-m-Y");?>"
                                readonly="readonly" style="height: 34px;" >
                            <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_serv_pub_txt_fecha_emision"></label>

						</div>
					</div>

					<div class="form-group col-md-4">
						<label>Fecha vencimiento:</label>
						<div class="input-group">
						  <input type="hidden" id="sec_con_serv_pub_fecha_vencimiento_recibo" class="filtro" 
						  		data-col="sec_con_serv_pub_fecha_vencimiento_recibo" 
						  		name="sec_con_serv_pub_fecha_vencimiento_recibo" value="<?php echo date("Y-m-d"); ?>"
                                data-real-date="sec_con_serv_pub_txt_fecha_vencimiento">
                            <input type="text" class="form-control servicio_publico_fecha_emision_datepicker"
                                id="sec_con_serv_pub_txt_fecha_vencimiento" value="<?php echo date("d-m-Y");?>"
                                readonly="readonly" style="height: 34px;">
                            <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_serv_pub_txt_fecha_vencimiento"></label>
						</div>
					</div>

					<div class="form-group col-md-4">
						<label>Monto total:</label>
						<input type="text" class="form-control monto sec_con_serv_pub_monto_total_recibo" 
						id="sec_con_serv_pub_monto_total_recibo" style="height: 34px;" placeholder="0.00">
					</div>

					<div class="form-group col-md-6">
						<label>Número de Recibo:</label>
						<input type="text" class="form-control" 
						id="sec_con_serv_pub_num_recibo_nuevo" style="height: 34px;" placeholder="0123456" maxlength="10">

						<label for="fileLocalServicioPublico">Nombre file:</label>
						<div class="input-container">
							<input type="file" id="sec_serv_pub_file_archivo_recibo" name="sec_serv_pub_file_archivo_recibo" required multiple="multiple" accept=".jpeg, .jpg, .png, .pdf">

							<button class="browse-btn btn btn-info" id="sec_serv_pub_btn_buscar_archivo">
								Seleccionar
							</button>
							<span class="file-info" id="sec_serv_pub_file_info"></span>
						</div>
					</div>

					<!--<div class="form-group col-md-3">
						<label>Periodo consumo:</label>
						<div class="input-group" style="width: 100%">
						  <input type="month" class="form-control txt_locales_servicio_publico_periodo_consumo" id="txt_locales_servicio_publico_periodo_consumo" style="height: 34px;">
						</div>
					</div>-->

					<div class="form-group col-md-6">
						<label>¿Desea ingresar algun comentario?</label>
						<textarea class="form-control" name="sec_con_serv_pub_comentario_recibo" id="sec_con_serv_pub_comentario_recibo" maxlength="255" style = "text-transform: uppercase;" rows="4"></textarea>
						<small>Quedan <span id="sec_con_serv_pub_caracteres_comentario">255</span> caracteres</small>
					</div>

					
					<!--<div class="col-xs-6" style="margin-top: 20px;">
						<button type="submit" class="btn btn-block btn-success" id="btnGuardarLocalesServicioPublico">
							<i class="glyphicon glyphicon-saved" ></i>
							Agregar recibo
						</button>	
					</div>-->
				</form>
			</div>
			<br>
			<br>
			<br>
			<br>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_recibo">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR RECIBO -->

<!-- INICIO LECTOR PANTALLA COMPLETA -->
<div class="modal fade right" id="contrato_servicio_publico_item_detalle_programacion_div_visor_pdf_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalPreviewLabel" aria-hidden="true" style="width: 100%; height: 100%; padding-left: 0px;">
    <div class="modal-dialog-full-width modal-dialog momodel modal-fluid" role="document" style="width: 100%; margin: 10px auto;">
        <div class="modal-content-full-width modal-content " style="background-color: rgb(0 0 0 / 0%) !important;">
            <div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 0px; margin-bottom:10px;">
              <button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>
            </div>
            <div class="modal-body" style="padding: 0px;" id="contrato_servicio_publico_item_detalle_programacion_visor_pdf">
            </div>
            <div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 10px; margin-bottom:10px;">
              <button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>   
            </div>
            <div class="col-xs-12 col-md-12 col-sm-12"></div>
        </div>
    </div>
</div>
<!-- FIN LECTOR PANTALLA COMPLETA -->