<?php

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'mesa_partes' LIMIT 1")->fetch_assoc();
$menu_caja_chica = $menu_id_consultar["id"];

if(array_key_exists($menu_caja_chica,$usuario_permisos) && in_array("mepa_grupo_asignacion", $usuario_permisos[$menu_caja_chica]))
{
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

	#modalMepaHistoricoUsuario #modalCuerpoMepaHistoricoUsuario{
        margin: 0;
        margin-right: auto;
        margin-left: auto;
        width: 90%;
    }
	
</style>

<div class="content container-fluid">
	<div class="page-header wide" style="margin-bottom: 10px;">
		<div class="row">
			<div class="col-xs-12 text-center">
				<form id="form_solicitudes">
					<h1 class="page-title">
						<i class="fa fa-group"></i>
						Grupo de Asignación
					</h1>
				</form>
			</div>
		</div>
	</div>

	<div class="col-md-12" style="margin-bottom: 10px; display: flex; justify-content: space-between;">
		<a class="btn btn-primary" id="btnRegresar" href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=mesa_partes" style="text-align: left;">
			<i class="glyphicon glyphicon-arrow-left"></i>
			Regresar
		</a>
		<button type="button" class="btn btn-info" onclick="mepa_guardar_grupo();" style="text-align: right;">
			Nuevo Grupo
		</button>
	</div>
</div>

<div class="page-header wide">
	<div class="row mt-4 mb-2" style="margin-left: 25px; margin-right: 25px;">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Busqueda</legend>
				<form autocomplete="off">

					<div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12" style="height: 55px;">
						<label>Areas:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control input_text select_mepa"
									data-live-search="true"
									id="area_id"
									name="search_id_mepa_area_id"
									style="width: 100%">
									<option value="">-- TODOS --</option>
								</select>
								<a class="btn btn-xs btn-default limpiar_select_mepa" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="area_id" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12" style="height: 55px;">
						<label>Usuario:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select 
									class="form-control select_mepa"
									id="param_usuario"
									style="width: 100%">
									<option value="">-- TODOS --</option>
									<?php  

										$query = 
										"
											SELECT
											    u.id,
											    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS nombre,
											    tp.dni
											FROM tbl_personal_apt tp
											    INNER JOIN tbl_usuarios u
											    ON tp.id = u.personal_id
											WHERE tp.estado = 1 AND u.estado = 1
											ORDER BY concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, ''))
										";
										
										$list_query = $mysqli->query($query);
										
										while ($li = $list_query->fetch_assoc()) 
										{
											?>
												<option value="<?php echo $li["id"]; ?>">
													<?php echo $li["nombre"] .' - '. $li["dni"]; ?>
												</option>
											<?php
										}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select_mepa" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="param_usuario" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-2 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
						<label>
							Fecha desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control mepa_grupo_datepicker"
									id="fecha_inicio"
									value=""
									readonly="readonly"
									style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;"
									>
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_inicio').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_inicio" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-2 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
						<label>
							Fecha hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control mepa_grupo_datepicker"
									id="fecha_fin"
									value=""
									readonly="readonly"
									style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;"
									>
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_fin').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_fin" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right" style="margin-top: 15px; margin-bottom: 15px; float: right;">
						<button type="button" class="btn btn-warning float-left" id="btn_mepa_limpiar_filtros_de_busqueda">
							<i class="fa fa-eraser"></i>
							Limpiar filtros
						</button>
						<span id="cont_interno_excel" class="float-left"></span>
						<button type="button" class="btn btn-success" id="mepa_btn_export_grupo">
							<span class="fa fa-file-excel-o"></span>
							Exportar excel
						</button>
						
						<button type="button" class="btn btn-info float-left" onclick="mepa_asignacion_grupo_usuarios_buscar_por_parametros();">
							<i class="glyphicon glyphicon-search"></i>
							Buscar
						</button>
					</div>

					<div class="col-md-12" id="cont_locales_alerta_filtrar_por">
									
					</div>
				</form>
			</fieldset>
		</div>
	</div>

<div class="row mt-3" id="mepa_asignacion_grupo_usuario_div_tabla" style="display: none; margin-left: 25px; margin-right: 25px;">
	<table class="table display responsive" style="width:100%" id="mepa_asignacion_grupo_usuario_datatable">

		<thead>
			<tr>
			<th scope="col" style="width: 50px;">N°</th>
				<th scope="col">Titulo</th>
				<th scope="col">Área</th>
				<th scope="col">Usuario creador</th>
				<th scope="col">Usuario Aprobador</th>
				<th scope="col">Fecha Creación</th>
				<th scope="col">Opciones</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<th scope="col"></th>
				<th scope="col"></th>
				<th scope="col"></th>
				<th scope="col"></th>
				<th scope="col"></th> 
				<th scope="col"></th>
			</tr>
		</tfoot>
	</table>
</div>

<!-- INICIO MODAL DETALLE GRUPO -->
<div id="modalMantemientoGrupo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_grupo">GRUPO </h4>
			</div>
			<div class="modal-body">
                
                <form method="POST" id="Frm_RegistroGrupo" enctype="multipart/form-data" autocomplete="off">

					<div class="row">
						<input id="mepa_grupo_id" type="hidden" class="form-control">

						<div class="col-md-12 mt-3">
							<div class="form-group">
								<label for="mepa_grupo_titulo">Titulo</label>
								<input id="mepa_grupo_titulo" class="form-control" maxlength="100">
								<span id="errorMsg" style="color: red;"></span>
							</div>
						</div>

						<div class="col-md-12 mt-3">
							<div class="form-group">
								<label for="mepa_grupo_descripcion">Descripción</label>
								<textarea id="mepa_grupo_descripcion" rows="5" cols="30" class="form-control" maxlength="100"></textarea>
							</div>
						</div>


						<div class="col-md-12 mt-3">
							<div class="form-group">
								<label for="mepa_grupo_reportar_gerencia">Reportar a Gerencia</label>
								<input id="mepa_grupo_reportar_gerencia" name="mepa_grupo_reportar_gerencia"type="checkbox">
							</div>
						</div>


						<div class="col-md-12 mt-3">
							<div class="form-group">
								<label for="mepa_grupo_usuario_creador_id">Usuario Creador</label>
								<select id="mepa_grupo_usuario_creador_id" name="search_id_mepa_usuario_creador" class="form-control select_mepa"></select>
							</div>
						</div>

						<div class="col-md-12 mt-3">
							<div class="form-group">
								<label for="mepa_grupo_usuario_aprobador_id">Usuario Aprobador</label>
								<select id="mepa_grupo_usuario_aprobador_id" name="search_id_mepa_usuario_aprobador" class="form-control select_mepa"></select>
							</div>
						</div>
					</div>

						<label></label>
						<div class="modal-footer">
							<div class="col-md-2">
								<button type="submit" class="btn form-control btn-info" id="btn-form-mepa_registro-grupo-registrar">Guardar</button>
							</div>
						</div>
                    </form>
            
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL DETALLE GRUPO -->

<!-- INICIO MODAL USUARIOS POR GRUPO -->
<div id="modalMantemientoUsuario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_usuarios">Correos</h4>
			</div>
			<div class="modal-body">
                <fieldset class="dhhBorder">
                    <legend class="dhhBorder">Registro</legend>
                    <form method="POST" id="Frm_RegistroUsuario" enctype="multipart/form-data" autocomplete="off">
                        <div class="row">
							<input id="mepa_grupo_id" type="hidden" class="form-control">

                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <select id="mepa_grupo_usuario_integrante_id" name="search_id_mepa_usuario_integrante" class="form-control select_mepa"></select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label></label>
                                    <button type="submit" class="btn form-control btn-info">Registrar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </fieldset>
                <hr>
                <div class="col-md-12">
                    <div class="table-responsive" id="mepa_asignacion_usuario_div_tabla">
						<table class="table display responsive" style="width:100%" id="mepa_asignacion_usuario_datatable">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Nombres</th>
                                    <th class="text-center">Usuario</th>
                                    <th class="text-center">Correo</th>
									<th class="text-center">Rol</th>
									<th class="text-center">DNI</th>
									<th class="text-center">Fecha Registro</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
							<tfoot>
								<tr>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th> 
									<th scope="col"></th>
									<th scope="col"></th>
								</tr>
							</tfoot>
                        </table>
                    </div>
                </div>
			</div>
				<div class="modal-footer">
				
				</div>
			</div>
		</div>
</div>
<!-- FIN MODAL USUARIOS POR GRUPO -->

<!-- INICIO MODAL HISTORICO USUARIOS POR GRUPO -->
<div id="modalMepaHistoricoUsuario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" id="modalCuerpoMepaHistoricoUsuario" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mepa_historico_usuarios"></h4>
			</div>
			<div class="modal-body">
				<div class="panel-group" id="accordionMepaUsuarios" role="tablist" aria-multiselectable="true">
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingUsuarioCreador">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionMepaUsuarios" href="#collapseUsuarioCreador" aria-expanded="true" aria-controls="collapseUsuarioCreador">
									CREADOR
									</a>
								</h4>
								</div>
								<div id="collapseUsuarioCreador" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingUsuarioCreador">
									<div class="panel-body">
										<div class="col-md-12">
											<div class="table-responsive" id="mepa_asignacion_usuario_creador_div_tabla">
												<table class="table display responsive" style="width:100%" id="mepa_asignacion_usuario_creador_datatable">
													<thead>
														<tr>
															<th class="text-center">
																#
															</th>
															<th class="text-center">
																Nombres
															</th>
															<th class="text-center">
																Correo
															</th>
															<th class="text-center">
																Rol
															</th>
															<th class="text-center">
																Estado
															</th>
															<th class="text-center">
																Usuario Creación
															</th>
															<th class="text-center">
																Fecha Creación</th>
															<th class="text-center">
																Usuario Desactivación
															</th>
															<th class="text-center">
																Fecha Desactivación
															</th>
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingUsuarioAprobador">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionMepaUsuarios" href="#collapseUsuarioAprobador" aria-expanded="true" aria-controls="collapseUsuarioAprobador">
									APROBADOR
									</a>
								</h4>
								</div>
								<div id="collapseUsuarioAprobador" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingUsuarioAprobador">
									<div class="panel-body">
										<div class="col-md-12">
												<div class="table-responsive" id="mepa_asignacion_usuario_aprobador_div_tabla">
													<table class="table display responsive" style="width:100%" id="mepa_asignacion_usuario_aprobador_datatable">
														<thead>
															<tr>
																<th class="text-center">
																	#
																</th>
																<th class="text-center">
																	Nombres
																</th>
																<th class="text-center">
																	Correo
																</th>
																<th class="text-center">
																	Rol
																</th>
																<th class="text-center">
																	Estado
																</th>
																<th class="text-center">
																	Usuario Creación
																</th>
																<th class="text-center">
																	Fecha Creación</th>
																<th class="text-center">
																	Usuario Desactivación
																</th>
																<th class="text-center">
																	Fecha Desactivación
																</th>
															</tr>
														</thead>
														<tbody>
														</tbody>
													</table>
												</div>
											</div>
										</div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingUsuarioIntegrante">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionMepaUsuarios" href="#collapseUsuarioIntegrante" aria-expanded="true" aria-controls="collapseUsuarioIntegrante">
									INTEGRANTE
									</a>
								</h4>
								</div>
								<div id="collapseUsuarioIntegrante" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingUsuarioIntegrante">
									<div class="panel-body">
										<div class="col-md-12">
											<div class="table-responsive" id="mepa_asignacion_usuario_integrante_div_tabla">
												<table class="table display responsive" style="width:100%" id="mepa_asignacion_usuario_integrante_datatable">
													<thead>
														<tr>
															<th class="text-center">
																#
															</th>
															<th class="text-center">
																Nombres
															</th>
															<th class="text-center">
																Correo
															</th>
															<th class="text-center">
																Rol
															</th>
															<th class="text-center">
																Estado
															</th>
															<th class="text-center">
																Usuario Creación
															</th>
															<th class="text-center">
																Fecha Creación</th>
															<th class="text-center">
																Usuario Desactivación
															</th>
															<th class="text-center">
																Fecha Desactivación
															</th>
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
											</div>
										</div>
										</div>
									</div>
								</div>
							</div>
					</div>
				</div>
			</div>
	</div>
</div>
<!-- FIN MODAL HISTORICO USUARIOS POR GRUPO -->

<?php
}
else
{
	include("403.php");
	return false;
}
?>