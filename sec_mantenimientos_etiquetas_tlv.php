<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimientos' AND sub_sec_id = 'etiquetas_tlv' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (array_key_exists($menu_id, $usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])) {

	$permiso_eliminar='false';
	if(in_array("delete", $usuario_permisos[$menu_id])){
		$permiso_eliminar='true';
	}
	$permiso_editar='false';
	if(in_array("edit", $usuario_permisos[$menu_id])){
		$permiso_editar='true';
	}
	$permiso_guardar='false';
	if(in_array("save", $usuario_permisos[$menu_id])){
		$permiso_guardar='true';
	}

	$permiso_gest_etq_tls='false';
	if(in_array("view_gest_etq_tls", $usuario_permisos[$menu_id])){
		$permiso_gest_etq_tls='true';
	}

	$permiso_etq_fraude='false';
	if(in_array("etq_tls_fraude", $usuario_permisos[$menu_id])){
		$permiso_etq_fraude='true';
	}

?>

<style type="text/css">
	body{
		background: none !important;
	}
	.colorpicker{
		z-index: 100000;
	}
	@media screen and (max-width: 1500px){
        #tabla_eventos_dinero_at_div {
            overflow-x: auto;
        }
    }

	.radio-buttons {
		display: inline-flex;
		align-items: center;
	}

	.radio-buttons input[type="radio"] {
		position: absolute;
		opacity: 0;
		z-index: -1;
	}

	.radio-buttons label {
		padding: 5px 10px;
		background-color: #e9ecef;
		border: 1px solid #ced4da;
		border-radius: 5px;
		margin-right: 5px;
		cursor: pointer;
	}

	.radio-buttons input[type="radio"]:checked + label {
		background-color: #007bff;
		color: #fff;
		border-color: #007bff;
	}
</style>

<script>
	var permiso_eliminar=<?php echo $permiso_eliminar; ?>;
	var permiso_editar=<?php echo $permiso_editar; ?>;
	var permiso_guardar=<?php echo $permiso_guardar; ?>;
</script>

<div class="sec_mantenimiento_etiquetas_tlv">


	<input id="g_fecha_actual" type="hidden" value="<?php echo date('Y-m-d'); ?>">
	<input id="g_fecha_hace_15_dias" type="hidden" value="<?php echo date('Y-m-d', strtotime(date('Y-m-d') . '- 15 days')); ?>">
	<input id="g_login_cod_cargo" type="hidden" value="<?php echo $login['cargo_id']; ?>">
	<input id="g_login_cod" type="hidden" value="<?php echo $login['id']; ?>">

	<input id="sectlv_manteetiquetas_fecha_actual" type="hidden" value="<?php echo date('Y-m-d'); ?>">

	<div id="loader_"></div>


	<div class="row">

		<div class="col-md-12">
			<div class="panel">

				<div class="panel-heading" style="border-color: #01579b;background: #fff;">
					<div class="panel-title" style="color: #000;text-align: center;font-size: 22px;">Mantenimiento de Etiquetas TLV</div>
				</div>

				<div class="panel-body no-pad">

					<?php
						if(in_array("save", $usuario_permisos[$menu_id])){
					?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<button class="btn btn-primary mt-2" id="SecManEtiTlv_btn_nuevo">
										<span class="glyphicon glyphicon-plus"></span>
										Nueva Etiqueta
									</button>

									<button class="btn btn-info mt-2" id="SecManEtiTlv_btn_nueva_comision">
										<span class="glyphicon glyphicon-plus"></span>
										Nueva Comisión
									</button>
									<button class="btn btn-success mt-2" id="SecManEtiTlv_btn_nueva_programacion">
										<span class="glyphicon glyphicon-plus"></span>
										Nueva Programación
									</button>
									<button class="btn btn-warning mt-2" id="SecManEtiTlv_btn_limite_clientes">
										<span class="glyphicon glyphicon-plus"></span>
										Modificar Limite Clientes
									</button>
									<?php 
										if (in_array("edit_limit_day", $usuario_permisos[$menu_id])) {
									?>
									<button class="btn btn-warning mt-2" id="SecManEtiTlv_btn_limite_dias_editar_depositos_retiros">
										<span class="glyphicon glyphicon-plus"></span>
										Edición días límite
									</button>
									<?php } ?>

									<!-- DINERO AT IMPORT -->
									<?php if(in_array("reg_evento_dinero_at", $usuario_permisos[$menu_id])){ ?>
										<button type="button" class="btn btn-info mt-2 btn_dinero_at" id="sec_tlv_btn_import_dinero_at">
											<span class="glyphicon glyphicon-plus"></span> Bono AT
										</button>
									<?php } ?>
									<!-- FIN DINERO AT IMPORT -->

									<!-- INICIO COMPROBANTES DE PAGO SIN NOTIFICAR -->
									<?php if(in_array("edit_voucher_sin_envio", $usuario_permisos[$menu_id])){ ?>
										<button class="btn btn-warning mt-2" id="SecManEtiTlv_btn_comprobante_de_pago_sin_notificar">
											<span class="glyphicon glyphicon-plus"></span>
											Comprobante pago sin notificar
										</button>
									<?php } ?>
									<!-- FIN COMPROBANTES DE PAGO SIN NOTIFICAR -->
									<?php if(in_array("crear_motivo", $usuario_permisos[$menu_id])){ ?>
										<button class="btn btn-primary mt-2" id="SecManEtiTlv_btn_nuevo_motivo">
											<span class="glyphicon glyphicon-plus"></span>
											Motivos
										</button>
									<?php } ?>

									<!-- BOTON ETIQUETAS MASIVAS -->
									<?php 
										if (in_array("ver_btn_etq_masivas", $usuario_permisos[$menu_id])) {
									?>
									<button class="btn btn-danger mt-2" id="SecManEtiTlv_btn_etiquetas_masivas">
										<span class="glyphicon glyphicon-plus"></span>
										Etiquetas Masivas
									</button>
									<?php } ?>

									<!-- FIN BOTON ETIQUETAS MASIVAS -->

								</div>
							</div>
						</div>
						<br>
					<?php
						}
					?>
				</div>

			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="table-responsive">
				<table class="table table-hover table-bordered" id="SecManEtiTlv_tabla_principal">		
				</table>
			</div>
		</div>
	</div>


	<!--**************************************************************************************************-->
	<!-- MODAL ETIQUETA -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_etiqueta" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">ETIQUETA</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Etiqueta:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
							<input type="text" id="modal_etiqueta_i_etiqueta" placeholder="Ingresar etiqueta" style="width: 100%;">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Descripción:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
							<textarea class="form-control" id="modal_etiqueta_txa_observacion" placeholder="Ingresar descripción" 
							autocomplete="off" rows="3"></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Color:</label>
						<div class="col-sm-10" id="modal_etiqueta_div_pintar" style="margin-bottom: 10px;height: 50px;padding-top: 8px;">
							<input type="text" id="modal_etiqueta_i_color" readonly="true">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Tipo:</label>
						<div class="col-sm-10" id="sec_eti_modal_etiqueta_agregar_div_tipo" style="margin-bottom: 10px;height: 50px;padding-top: 0px;">
							<select class="form-control" id="sec_eti_modal_etiqueta_tipo" style="width: 100%;">
								<?php
								$where_gest_etq_tls = "";
								if($permiso_gest_etq_tls == 'true' AND $permiso_etq_fraude == 'true') {
									$where_gest_etq_tls = "";
									
								}else if ($permiso_gest_etq_tls == 'true' AND $permiso_etq_fraude == 'false'){
									$where_gest_etq_tls = "AND id != 4";
								}else if ($permiso_gest_etq_tls == 'false' AND $permiso_etq_fraude == 'true'){
									$where_gest_etq_tls = "AND id = 4";
								}
								else if ($permiso_gest_etq_tls == 'false' AND $permiso_etq_fraude == 'false'){
									$where_gest_etq_tls = "AND id = 0";
								} 
 
									$query ="
										SELECT
										id, 
										IFNULL(descripcion, '') descripcion
										FROM tbl_televentas_etiqueta_tipo
										WHERE status = 1
										" . $where_gest_etq_tls . "
										";
									$resp_query=$mysqli->query($query);
									while ($li2=$resp_query->fetch_assoc()) {
								?>
								<option value="<?php echo $li2['id']; ?>"><?php echo $li2["descripcion"]; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
				<button type="button" class="btn btn-primary pull-right" id="modal_etiqueta_btn_guardar">
					<b><i class="fa fa-check"></i> GUARDAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>


	<!--**************************************************************************************************-->
	<!-- MODAL COMISIÓN -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_comision" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">COMISIONES</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="table-responsive">
					<div class="form-group" id="tbl_comisiones">
						</div>
					</div>

					<div class="mb-4">
		                <hr class="solid">
		            </div>

					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Nueva comisión:</label>
						<div class="col-sm-3" style="margin-bottom: 10px;">
							<input type="text" class="money" id="modal_comision_i_comision" placeholder="Ingresar la nueva comisión" style="width: 100%;" autocomplete="off">
						</div>
						<div class="col-sm-3" style="margin-bottom: 10px;">
							<button type="button" class="btn btn-primary btn-xs" id="modal_comision_btn_guardar">
								<b><i class="fa fa-plus"></i> Agregar comisión</b>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>


	<!--**************************************************************************************************-->
	<!-- MODAL EDITAR COMISIÓN -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_comision_editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#SecManEtiTlv_btn_nueva_comision').click();"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_comision_editar_titulo"></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Comisión:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
							<input type="text" class="money" id="modal_comision_i_comision_edit" placeholder="Ingresar la comisión" style="width: 100%;" autocomplete="off">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Estado:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
							<select class="form-control" id="modal_comision_i_estado_edit" style="width: 100%;">
								<option value="1">Activo</option>
								<option value="0">Inactivo</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal" onclick="$('#SecManEtiTlv_btn_nueva_comision').click();">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
				<button type="button" class="btn btn-primary pull-right" id="modal_comision_btn_editar_comision">
					<b><i class="fa fa-check"></i> GUARDAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>



		<!--**************************************************************************************************-->
	<!-- MODAL LIMITE CLIENTES -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="sec_mant_modal_limite_clientes" role="dialog" 
		aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="">Limite Clientes Teleservicios</h4>
			</div>
			<div class="modal-body">
				<div class="row">

					<form id="form_editar_limite_terceros" autocomplete="off" >	 
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<p style="font-size: 18px;">Limite Terceros Autorizados (Promotor):</p>
								<table class="table table-bordered"> 
									<tr>
										<td><b>Valor Actual:</b></td>
										<td id="editar_valor_actual_tercero"></td>
									</tr>
									<tr>
										<td><b>Nuevo Valor:</b></td>
										<td>
											<div id="div_editar_tercero">
												<input type="number" id="editar_limite_tercero"  style="width: 100%;">
											</div>
								 
										</td>
									</tr>
								</table>
							</div>
						</div>
					</form>
					<div class="modal-footer">
						 
							<button type="button" class="btn btn-success" onclick="sec_mant_modif_limite_tercero();">
								<i class="icon fa fa-edit"></i>
								<span id="demo-button-text">Editar</span>
							</button>
						</div>
				   
				</div>
			</div>
			<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
						<b><i class="fa fa-close"></i> CERRAR</b>
					</button>
				 
				</div>
			
		</div>
	  </div>
	</div>


	<!--**************************************************************************************************-->
	<!-- MODAL ETIQUETAS MASIVAS -->
	<!--**************************************************************************************************-->

	<div class="modal fade" id="sec_mant_modal_etiquetas_masivas" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">ETIQUETAS MASIVAS</h4>
				</div>


					<div class="modal-body">
						<div class="row">
							<div class="form-group">
								<label class="col-sm-2 control-label" style="text-align: right;color: black;">Etiqueta:</label>
								<div class="col-sm-10" style="margin-bottom: 10px;">

									<input type="text" class="form-control" id="SecManEtiTlv_modal_etiq" placeholder="Nombre de la etiqueta" autocomplete="off"
									style="border-radius: 5px;border: 1px solid #aaa; color: black; height:auto; cursor: text;">
									<ul id="SecManEtiTlv_modal_lista_etiq"></ul> 

								</div>
							</div>
						</div>	

						<div class="row">
							<div class="form-group">
								<label class="col-sm-2 control-label" style="text-align: right;color: black;">Clientes:</label>
								<div class="col-sm-10" style="margin-bottom: 10px;">
										<input type="text" class="form-control" id="SecManEtiTlv_modal_etiq_cli" placeholder="Nombre del cliente" autocomplete="off" style="border-radius: 5px;border: 1px solid #aaa; color: black; height:auto; cursor: text;">
										<ul id="SecManEtiTlv_modal_lista_etiq_cli"></ul>
										<button class="btn btn-primary" title="Agregar cliente al listado"id="SecManEtiTlv_modal_btn_etiq_cli"  type="button"><span class="fa fa-plus"></span></button>
								</div> 


							</div>
						</div>

						<div class="row" style="margin-top: 60px; margin-bottom: 30px; ">
							<div class="row mt-3 table-responsive" id="SecManEtiTlv_modal_list_etiq_div_tabla" >
								<table id="SecManEtiTlv_modal_list_etiq_cli" class="table table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>	
											<th class="text-center">#</th> 
											<th class="text-center">Cliente</th> 
											<th class="text-center">Etiqueta</th>
											<th class="text-center">Eliminar</th>
										</tr>
									</thead>
									<tbody id="SecManEtiTlv_modal_list_etiq_cli_tbody">
									</tbody>
								</table>
							</div>
						</div>

						<div class="row">
							<div class="form-group">

							</div>
						</div>
					</div>


				<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
						<b><i class="fa fa-close"></i> CERRAR</b>
					</button>
					<button type="button" class="btn btn-success pull-right" id="modal_etiqueta_masivas_btn_guardar">
						<b><i class="fa fa-check"></i> ASIGNAR ETIQUETAS</b>
					</button>
				</div>

			</div>
	  	</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL LIMITE DE DIAS PARA EDITAR LOS DEPOSITOS Y RETIROS APROBADOS -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="sec_modal_limite_dias_editar_depositos_retiros" role="dialog" 
		aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12" style="text-align: center;">
							<h3 style="color: black;">Límite de días para editar Depósitos y Retiros aprobados</h3>
							<br>
						</div> 
						<form id="form_editar_limite_dias_ret_dep_aprobados" autocomplete="off" >	 
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<table class="table table-bordered"> 
										<tr>
											<td><b>Valor Actual:</b></td>
											<td id="modal_edit_trans_aprob_valor_actual"></td>
										</tr>
										<tr>
											<td><b>Nuevo Valor:</b></td>
											<td>
												<div id="div_modal_editar_nuevo_limite_dias_ret_dep">
													<input type="number" id="modal_editar_nuevo_limite_dias_ret_dep" style="width: 100%;">
												</div>
									
											</td>
										</tr>
									</table>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-success" onclick="modificar_limite_dias_editar_depositos_retiros_aprobados();">
						<i class="icon fa fa-edit"></i>
						<span id="demo-button-text">Editar</span>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL PROGRAMACION DE HORARIOS -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="sec_mant_modal_programacion_pago" role="dialog" 
		aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12" style="text-align: center;">
						<h3 style="color: black;">PROGRAMACIÓN DE HORARIOS - SUPERVISOR</h3>
					</div>
					<div class="col-md-12" style="margin-top: 10px;">
						<label>Supervisor(a) de turno : <span id="sec_mant_supervisor_de_turno"></span></label><br>
						<hr class="solid" style="margin : 0px;" />
					</div>
					<!--FILTROS-->
					<div class="col-md-12" style="margin-bottom: 30px; margin-top: 10px;">
						<div class="form-group col-md-3">
							<label>Supervisor: </label>
							<select id="sec_mant_select_supervisores" class="form-control select2" style="width: 100%;">
							</select>
						</div>
						<div class="form-group col-md-3">
							<label>Desde: </label>
							<input type="datetime-local" class="form-control" id="sec_mant_desde_fecha" style="color: black;font-size: 15px;">
						</div>
						<div class="form-group col-md-3">
							<label>Hasta: </label>
							<input type="datetime-local" class="form-control" id="sec_mant_hasta_fecha" style="color: black;font-size: 15px;">
						</div>
						<div class="col-md-3" style="vertical-align: center;">
							<label></label>
							<div class="form-control" style="border: 0px;">
								<button type="button" class="btn btn-success btn-sm" style="border: none;" 
										onclick="sec_mant_agregar_programacion();">
									<i class="fa fa-plus"></i>
									Agregar
								</button>
								<button type="button" class="btn btn-info btn-sm" style="border: none;" 
										onclick="sec_mant_listarProgramaciones();">
									<i class="fa fa-search"></i>
									Buscar
								</button>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-12" id="sec_mant_div_tabla_programaciones">
							<table class="table" id="sec_mant_table_programaciones" style="width: 100%;">
								<thead>
									<th>#</th>
									<th>Supervisor</th>
									<th>Desde</th>
									<th>Hasta</th>
									<th>Fecha de Asignación</th>
									<th>Editar</th>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL EDITAR PROGRAMACION DE HORARIOS -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="sec_mant_modal_edit_programacion_pago" role="dialog" 
		aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12" style="text-align: center;">
						<h3 style="color: black;">Edición de Programación</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 form-group">
						<label>Supervisor</label>
						<input type="text" readonly id="sec_mant_input_supervisor_edit" class="form-control">
						<input type="hidden" readonly id="sec_mant_input_id_supervisor_edit" class="form-control">
						<input type="hidden" readonly id="sec_mant_input_id_programacion_edit" class="form-control">
					</div>
					<div class="col-md-4 form-group">
						<label>Desde: </label>
						<input type="datetime-local" class="form-control" id="sec_mant_input_desde_edit" style="color: black;font-size: 15px;">
					</div>
					<div class="col-md-4 form-group">
						<label>Hasta: </label>
						<input type="datetime-local" class="form-control" id="sec_mant_input_hasta_edit" style="color: black;font-size: 15px;">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" onclick="cancelar_edicion_programacion();">
					<b><i class="fa fa-close"></i> CANCELAR</b>
				</button>
				<button type="button" class="btn btn-success pull-right" onclick="validar_actualizacion_programacion();">
					<b><i class="fa fa-close"></i> ACTUALIZAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>


	<!--**************************************************************************************************-->
	<!-- MODAL DINERO AT -->
	<!--**************************************************************************************************-->
	<div class="modal fade bd-example-modal-xl" id="modal_tlv_mant_etiq_dinero_at" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	  	<div class="modal-dialog modal-xl" role="document" style="min-width: 95%;">
			<div class="modal-content" style="min-width: 95%;">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="">Registrar evento promocional - Bono AT</h4>
				</div>
				<div class="modal-body">
					
					<div class="row">
						<form id="modal_tlv_mant_etiq_dinero_at_form">
							<div class="row mb-5">
								<div class="form-row">
									<div class="form-group col-md-8" id="modal_tlv_mant_etiq_dinero_at_evento_nombre_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_nombre" class="control-label" style="color: black;">Nombre: (*)</label>
										<input type="text" id="modal_tlv_mant_etiq_dinero_at_evento_nombre" autocomplete="off" placeholder="Ingresar un nombre" style="width: 100%;">
									</div>
									<div class="form-group ml-0 col-md-4" id="modal_tlv_mant_etiq_dinero_at_evento_codigo_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_codigo" class="control-label" style="color: black;">Código: (*) </label><span style="font-weight:500; color:black;" class="text-body"> Máximo de caracteres: 11</span>
										<input type="text" maxlength="11"  id="modal_tlv_mant_etiq_dinero_at_evento_codigo" autocomplete="off" placeholder="Código alfanumérico" style="width: 100%;">
									</div>
								</div>
							</div>
							<div class="row mb-5">
								<div class="form-row">
									<div class="form-group col-md-8" id="modal_tlv_mant_etiq_dinero_at_evento_descripcion_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_descripcion" class="control-label" style="color: black;">Descripción:</label>
										<textarea class="form-control" id="modal_tlv_mant_etiq_dinero_at_evento_descripcion" autocomplete="off" placeholder="Ingresar una desripción" autocomplete="off" rows="2" style="border-radius: 5px;border: 1px solid #aaa;color: black;"></textarea>
									</div>
									<div class="form-group ml-0 col-md-2" id="modal_tlv_mant_etiq_dinero_at_evento_inicio_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_inicio" class="control-label" style="color: black;">Fecha Inicio: (*)</label>
											<input type="text" id="modal_tlv_mant_etiq_dinero_at_evento_inicio" placeholder="" style="width: 100%;">
									</div>
									<div class="form-group ml-0 col-md-2" id="modal_tlv_mant_etiq_dinero_at_evento_fin_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_fin" class="control-label" style="color: black;">Fecha Fin: (*)</label>
											<input type="text" id="modal_tlv_mant_etiq_dinero_at_evento_fin" placeholder="" style="width: 100%;">
									</div>
								</div>
							</div>
							<div class="row mb-5">
								<div class="form-row">
									<div class="form-group col-md-4" id="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente" class="control-label" style="color: black;">Bono por cliente: (*)</label>
										<div class="form-row">
											<div class="radio-buttons">
												<input type="radio" name="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio" value="1" id="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio_soles">
												<label for="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio_soles">S/</label>
												<input type="radio" name="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio" value="2" id="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio_porcentaje">
												<label for="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio_porcentaje">%</label>
												<input type="text" id="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente" placeholder="Bono/Porcentaje" autocomplete="off" disabled style="width: 100%;">
												<input type="text" id="modal_tlv_mant_etiq_dinero_at_evento_monto_minimo" placeholder="Monto mínimo Soles" autocomplete="off" hidden style="width: 100%; margin-left: 4px;">
											</div>
										</div>
									</div>
									<div class="form-group ml-0 col-md-4" id="modal_tlv_mant_etiq_dinero_at_evento_clientes_limite_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_clientes_limite" class="control-label" style="color: black;">Clientes límite: (*)</label>
										<input type="text" id="modal_tlv_mant_etiq_dinero_at_evento_clientes_limite" autocomplete="off" placeholder="Límite de clientes" style="width: 100%;">
									</div>
									<div class="form-group ml-0 col-md-4" id="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_conversion_max" class="control-label" style="color: black;">Conversión máxima: (*)</label>
										<div class="form-row">
											<div class="radio-buttons">
												<input type="radio" name="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio" value="1"  id="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio_soles">
												<label for="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio_soles">S/</label>
												<input type="radio" name="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio" value="2" id="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio_porcentaje">
												<label for="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio_porcentaje">%</label>
												<input type="text" id="modal_tlv_mant_etiq_dinero_at_evento_conversion_max" disabled autocomplete="off" placeholder="Monto/Porcentaje" style="width: 100%;">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="form-row">
									<div class="form-group col-md-6" id="modal_tlv_mant_etiq_dinero_at_juegos_div">
										<label class="control-label" style="color: black;">Productos permitidos: (*)</label>
										<div class="d-flex flex-row">
												<input class="form-check-input" type="checkbox" id="select_all">
												<label class="form-check-label" style="margin-right: 50px;" for="select_all">
													Todos
												</label>
												<input class="form-check-input check_trans" type="checkbox" id="modal_tlv_mant_etiq_dinero_at_juegos_virtuales" >
												<label class="form-check-label mr-2" for="modal_tlv_mant_etiq_dinero_at_juegos_virtuales">Juegos virtuales</label>

												<input class="form-check-input check_trans" type="checkbox" id="modal_tlv_mant_etiq_dinero_at_bingo" value="option2">
												<label class="form-check-label mr-2" for="modal_tlv_mant_etiq_dinero_at_bingo">Bingo</label>
												
												<input class="form-check-input check_trans" type="checkbox" id="modal_tlv_mant_etiq_dinero_at_sportbook" value="option4">
												<label class="form-check-label" for="modal_tlv_mant_etiq_dinero_at_sportbook">Apuestas deportivas</label>
										</div>
									</div>
									<div class="form-group col-md-2" id="modal_tlv_mant_etiq_dinero_at_evento_rollover_div">
										<label for="modal_tlv_mant_etiq_dinero_at_evento_rollover" class="control-label" style="color: black;">Rollover: (*)</label>
										<input type="number" id="modal_tlv_mant_etiq_dinero_at_evento_rollover" autocomplete="off" placeholder="Ingresar el Rollover" style="width: 100%;">
									</div>
								</div>

							</div>
							<div class="form-group col-md-12">
								<button type="button" class="btn btn-primary pull-right" id="modal_tlv_mant_etiq_dinero_at_btn_enviar">
								<i class="fa fa-save"></i> Registrar evento
								</button>
							</div>
						</form>
					</div>
					<div class="row tablaHeight mt-5" id="tabla_eventos_dinero_at_div">
						<table id="tabla_eventos_dinero_at" class="table table-hover table-condensed table-small table-bordered table-striped" style="table-layout: fixed">
							<thead>
								<tr>
									<th style="display:none;" colspan="1" rowspan="2">Id</th>
									<!-- <th style="width:100px; height: 58px;" colspan="1" rowspan="2" class="bg-primary text-center">Nombre</th> -->
									<th style="width:100px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Código</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Fecha<br/>Inicio</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Fecha<br/>Fin</th>
									<!-- <th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Total de<br/>Promo</th> -->
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Monto<br/>Mínimo</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Bono por<br/>Cliente</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Clientes<br/>Límite</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Clientes<br/>inscritos</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Usando<br/>Promo</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Conversión<br/>Máxima</th>
									<th style="width:80px; height: 58px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Rollover</th>
									<th style="width:300px; padding-right: 0;" colspan="3" rowspan="1" class="bg-primary text-center">Juegos habilitados</th>
									<th style="width:100px; padding-right: 0;" colspan="1" rowspan="2" class="bg-primary text-center">Acciones</th>
								</tr>
								<tr>
									<th class="text-center" style="padding-right: 0;">Virtuales</th>
									<th class="text-center" style="padding-right: 0;">Bingo</th>
									<!-- <th class="text-center">Recargas</th> -->
									<th class="text-center" style="padding-right: 0;">A. Deportivas</th>
								</tr>
							</thead>

							<tbody>
								
								
							</tbody>
							
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
						<b><i class="fa fa-close"></i> CERRAR</b>
					</button>
					<!-- <button type="button" class="btn btn-primary pull-right" id="modal_apuesta_registrar_btn_guardar">
						<b><i class="fa fa-save"></i> REGISTRAR</b>
					</button> -->
				</div>
			</div>
	  	</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL IMPORTAR CLIENTES DINERO AT -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_dinero_at_importar_clientes" role="dialog" 
		aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<div class="col-md-12" style="text-align: center;">
						<h4 class="modal-title" style="color: black;" id="dinero_at_titulo">Importar clientes para el Saldo Promocional</h4>
						<br>
					</div> 
				</div>
				<div class="modal-body">
					<div class="row">
						<input type="hidden" name="" id="modal_tlv_mant_etiq_dinero_at_evento_id">
						<form id="modal_tlv_mant_etiq_dinero_at_form" action="form" enctype="multipart/form-data">
							<div class="form-group col-md-12">
								<label for="modal_tlv_mant_etiq_dinero_at_archivo" class="col-md-3 control-label" 
								style="text-align: right;padding-left: 0px;padding-right: 0px;">Seleccionar archivo:</label>
								<div class="col-md-9" style="margin-bottom: 10px;">
									<input type="file" name="modal_tlv_mant_etiq_dinero_at_archivo" class="form-control" id="modal_tlv_mant_etiq_dinero_at_archivo" placeholder="Elegir archivo"
										maxlength="80" autocomplete="off">
								</div>
							</div>
							<br>
							<div class="col-md-12">
								<span style="color:red; margin-left:8px;"> * Si el archivo contiene más de 50 registros, el procesamiento puede demorar.</span>
							</div>
							<div class="col-md-12">
								<hr style="color: #0056b2;" />
							</div>
							<div style="padding-left: 10px;">
								<div class="row d-flex justify-content-between">
									<label class="col-md-6 control-label mb-3">Ejemplo de la plantilla:</label>
								</div>
								<div class="row">
									<!-- <div class="col-md-4"></div> -->
									<div class="col-md-4">
										<table id="tablaFormatoDineroAT" style="text-align:center;" class="table table-responsive table-bordered">
											<thead>
												<tr>
													<!-- <th>Tipo Documento</th> -->
													<th class="text-center">Número Documento</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<!-- <td>DNI</td> -->
													<td>0000432a</td>
												</tr>
												<tr>
													<!-- <td>CE</td> -->
													<td>abc999666</td>
												</tr>
												<tr>
													<!-- <td>PTP</td> -->
													<td>333444555</td>
												</tr>
											</tbody>
										</table>
									</div>							
								</div>
								<div class="col-md-12">
									<!-- <label style="color: red;">Seguir los siguientes pasos en la plantilla:</label>
									<p style="color: red; margin-bottom:0">1. Establecer el formato de la columna "A" como <span style="font-weight:700;"> "Texto"</span></p>
									<p style="color: red; margin-bottom:0">2. Registrar los número de documentos en la columna "A".</p>
									<p style="color: red; margin-bottom:0">3. Guardar y cargar la plantilla.</p> -->
									<button class="btn btn-success" id="btnExport_formatoClientesDineroAT" style="margin-top: 10px;"><i class="fa fa-download"></i> Descargar Plantilla</button>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-md-12">
						<button type="button" style="margin-left: 11px;" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL LISTAR CLIENTES DINERO AT -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_dinero_at_listar_clientes_evento" role="dialog" 
		aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<div class="col-md-12" style="text-align: center;">
						<h4 class="modal-title" style="color: black;" id="modal_dinero_at_listar_clientes_evento_titulo">Lista de clientes</h4>
						<br>
					</div> 
				</div>
				<div class="modal-body">
					<div class="row">
						<div id="div_modal_dinero_at_lista_clientes_evento">
							<table id="modal_dinero_at_lista_clientes_evento"  class="table table-responsive table-bordered">
								<thead>
									<tr>
										<th class="bg-primary text-center">Tipo<br/>Documento</th>
										<th class="bg-primary text-center">Número<br/>Documento</th>
										<th class="bg-primary text-center">Nombre<br/>Completo</th>
										<th class="bg-primary text-center">Usó<br/>Promoción</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row">
						<div class="col-md-12">
							<button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
							<button type="button" class="btn btn-success pull-right" id="modal_dinero_at_listar_clientes_evento_btn_exportar" style="display: none;"> <i class="fa fa-cloud-download"></i> Descargar</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL EDITAR EVENTO DINERO AT -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_dinero_at_editar" role="dialog" 
		aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<div class="col-md-12" style="text-align: center;">
						<h4 class="modal-title" style="color: black;" id="">Editar la promoción con Código:</h4>
						<h4 class="modal-title mt-3" style="color: black; font-weight: 700;" id="modal_dinero_at_editar_codigo"></h4>
						<br>
					</div> 
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="form-group col-md-12" id="modal_dinero_at_editar_nombre_div">
							<label for="modal_dinero_at_editar_nombre" class="control-label" style="color: black;">Nombre del evento:</label>
							<input type="text" class="form-control" id="modal_dinero_at_editar_nombre" readonly autocomplete="off" style="width: 100%;">
						</div>
						<div class="form-group col-md-12 mt-5 mb-5" id="modal_dinero_at_editar_descripcion_div">
							<label for="modal_dinero_at_editar_descripcion" class="control-label" style="color: black;">Descripción:</label>
							<br><label class="" style="color: black; font-weight: 300;">Puede añadir el motivo, por el cual está procediendo a editar el Evento</label>
							<textarea class="form-control" id="modal_dinero_at_editar_descripcion" autocomplete="off" rows="2" style="border-radius: 5px;border: 1px solid #aaa;color: black;"></textarea>
						</div>
						<div class="form-row">
							<div class="form-group col-md-6" id="modal_dinero_at_editar_fecha_inicio_div">
								<label for="modal_dinero_at_editar_fecha_inicio" class="control-label" style="color: black;">Fecha Inicio:</label>
								<input type="text" class="form-control"  id="modal_dinero_at_editar_fecha_inicio" autocomplete="off" style="width: 100%;">
							</div>
							<div class="form-group col-md-6" id="modal_dinero_at_editar_fecha_fin_div">
								<label for="modal_dinero_at_editar_fecha_fin" class="control-label" style="color: black;">Fecha Fin:</label>
								<input type="text" class="form-control"  id="modal_dinero_at_editar_fecha_fin" autocomplete="off" style="width: 100%;">
							</div>
						</div>
						<div class="form-group col-md-6" id="modal_dinero_at_editar_clientes_limite_div">
							<label for="modal_dinero_at_editar_clientes_limite" class="control-label" style="color: black;">Límite de clientes:</label>
							<input type="text" class="form-control"  id="modal_dinero_at_editar_clientes_limite" autocomplete="off" style="width: 100%;">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="form-group col-md-12">
						<button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
						<button type="button" class="btn btn-warning pull-right" id="modal_dinero_at_editar_btn">
							<i class="fa fa-edit"></i> Editar Evento
						</button>
					</div>	
				</div>
			</div>
		</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL COMPROBANTES DE PAGO SIN NOTIFICAR -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="sec_mant_modal_comprobante_de_pago_sin_notificar" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12" style="text-align: center;">
							<h3 style="color: black;">Comprobantes de Pago sin Notificar Teleservicios</h3>
							<br>
						</div>
						<form id="form_editar_comprobante_de_pago_sin_notificar" autocomplete="off">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<p style="font-size: 13px;">Cada cuantos minutos se consulta los comprobantes de pago sin envío en la base de datos:</p>
									<table class="table table-bordered">
										<tr>
											<td><b>Valor Actual (Minutos):</b></td>
											<td id="editar_valor_actual_num_minutos_consultar_voucher_sin_envio"></td>
										</tr>
										<tr>
											<td><b>Nuevo Valor (Minutos):</b></td>
											<td>
												<div id="div_editar_num_minutos_consultar_voucher_sin_envio">
													<input type="number" id="editar_num_minutos_consultar_voucher_sin_envio" style="width: 100%;">
												</div>

											</td>
										</tr>
									</table>
								</div>
							</div>
						</form>

						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
							<button type="button" class="btn btn-success" onclick="sec_mant_modif_num_minutos_consultar_voucher_sin_envio();">
								<i class="icon fa fa-edit"></i>
								<span id="demo-button-text">Editar</span>
							</button>
						</div>

						<form id="form_editar_comprobante_de_pago_sin_notificar" autocomplete="off">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<p style="font-size: 13px;">Rango de días para la consulta de los comprobantes de pago sin envío:</p>
									<table class="table table-bordered">
										<tr>
											<td><b>Valor Actual (Días):</b></td>
											<td id="editar_valor_actual_rango_dias_consultar_voucher_sin_envio"></td>
										</tr>
										<tr>
											<td><b>Nuevo Valor (Días):</b></td>
											<td>
												<div id="div_editar_rango_dias_consultar_voucher_sin_envio">
													<input type="number" id="editar_rango_dias_consultar_voucher_sin_envio" style="width: 100%;">
												</div>

											</td>
										</tr>
									</table>
								</div>
							</div>
						</form>

					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
					<button type="button" class="btn btn-success" onclick="sec_mant_modif_rango_dias_consultar_voucher_sin_envio();">
						<i class="icon fa fa-edit"></i>
						<span id="demo-button-text">Editar</span>
					</button>
				</div>
			</div>
		</div>
	</div>



	<!--**************************************************************************************************-->
	<!-- MODAL MOTIVOS -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_motivos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">MOTIVOS</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="table-responsive">
							<div class="form-group" id="tbl_motivos">
							</div>
						</div>

						<div class="mb-4">
							<hr class="solid">
						</div>
						<div class="row">
							<div class="form-group" id="modal_motivos_nuevo_div">
								<label class="col-sm-2 control-label" style="text-align: right;color: black;">
									Nuevo motivo:
								</label>
								<div class="col-sm-8" style="margin-bottom: 10px;">
									<input type="text" class="form-control" id="modal_motivos_nuevo" placeholder="Ingresar el nuevo motivo" style="width: 100%;" autocomplete="off">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group" id="modal_motivos_tipo_div">
								<label class="col-sm-2 control-label" style="text-align: right;color: black;">
									Tipo Motivo:
								</label>
								<div class="col-sm-4" style="margin-bottom: 10px;">
									<select class="form-control" id="modal_motivos_tipo" style="width: 100%;">
										<option value="0">:: Seleccione ::</option>
										<?php
											$query = "SELECT id, tipo FROM tbl_televentas_motivos_tipo WHERE estado=1; ";
											$resp_query = $mysqli->query($query);
											while ($li = $resp_query->fetch_assoc()) {
										?>
											<option value="<?php echo $li['id'] ?>"><?php echo $li["tipo"]; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-4" style="margin-bottom: 10px;">
									<button type="button" class="btn btn-primary" id="modal_motivos_btn_guardar">
										<b><i class="fa fa-plus"></i> Agregar motivo</b>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
						<b><i class="fa fa-close"></i> CERRAR</b>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL EDITAR COMISIÓN -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_motivo_editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#SecManEtiTlv_btn_nuevo_motivo').click();"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modal_motivo_editar_titulo"></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="form-group" id="modal_motivo_nuevo_editar_div">
							<label class="col-sm-2 control-label" style="text-align: right;color: black;">Motivo:</label>
							<div class="col-sm-10" style="margin-bottom: 10px;">
								<input type="text" class="" id="modal_motivo_nuevo_editar" placeholder="Ingresar el motivo" style="width: 100%;" autocomplete="off">
							</div>
						</div>
						<div class="form-group" id="modal_motivos_tipo_editar_div">
							<label class="col-sm-2 control-label" style="text-align: right;color: black;">
								Tipo Motivo:
							</label>
							<div class="col-sm-10" style="margin-bottom: 10px;">
								<select class="form-control" id="modal_motivos_tipo_editar" style="width: 100%;">
									<option value="0">:: Seleccione ::</option>
									<?php
										$query = "SELECT id, tipo FROM tbl_televentas_motivos_tipo WHERE estado=1; ";
										$resp_query = $mysqli->query($query);
										while ($li = $resp_query->fetch_assoc()) {
									?>
										<option value="<?php echo $li['id'] ?>"><?php echo $li["tipo"]; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" style="text-align: right;color: black;">Estado:</label>
							<div class="col-sm-10" style="margin-bottom: 10px;">
								<select class="form-control" id="modal_motivo_estado_editar" style="width: 100%;">
									<option value="1">Activo</option>
									<option value="0">Inactivo</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left" data-dismiss="modal" onclick="$('#SecManEtiTlv_btn_nuevo_motivo').click();">
						<b><i class="fa fa-close"></i> CERRAR</b>
					</button>
					<button type="button" class="btn btn-primary pull-right" id="modal_motivo_editar_btn">
						<b><i class="fa fa-check"></i> GUARDAR</b>
					</button>
				</div>
			</div>
		</div>
	</div>

</div>

<?php
} else {
	echo "No tienes permiso para acceder a este recurso.";
}
?>