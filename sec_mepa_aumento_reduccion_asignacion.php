<?php 

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'mesa_partes' LIMIT 1")->fetch_assoc();
$menu_asignacion_caja_chica = $menu_id_consultar["id"];

$usuario_id = $login?$login['id']:null;

?>

<style>
	textarea {
      resize: none;
    }

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

<div class="content container-fluid vista_sec_caja_chica">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Solictud de Aumento o Reducción de Fondo</h1>
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

    if(array_key_exists($menu_asignacion_caja_chica,$usuario_permisos) && in_array("AtenderLiquidacionJF", $usuario_permisos[$menu_asignacion_caja_chica]))
    {
    	?>
    	<div class="col-md-12" id="mepa_aumento_asignacion">
			<div class="page-header wide">
				<div class="row mt-4 mb-2">
					<fieldset class="dhhBorder">
						<legend class="dhhBorder">Búsqueda</legend>
						<form autocomplete="off">
							
							<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 mepa_rendicion_caja_chica_div_param_busqueda">
                                <label>
                                    Usuario:
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mepa_aumento_reduccion_asignacion_select_filtro"
                                        name="mepa_aumento_asignacion_param_usuario"
                                        id="mepa_aumento_asignacion_param_usuario"
                                        title="Seleccione el estado">
                                        <option value="0">-- Todos --</option>
                                        <?php  

                                            $query = 
                                            "
                                                SELECT
													a.usuario_asignado_id AS id,
												    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS nombre
												FROM mepa_asignacion_caja_chica a
													INNER JOIN tbl_usuarios tu
													ON a.usuario_asignado_id = tu.id
													INNER JOIN tbl_personal_apt tp
													ON tu.personal_id = tp.id
													INNER JOIN mepa_usuario_asignacion_detalle uad
							                        ON a.usuario_asignado_id = uad.usuario_id 
							                        AND uad.mepa_asignacion_rol_id = 3 AND uad.status = 1
							                        INNER JOIN mepa_usuario_asignacion ua
							                        ON uad.mepa_usuario_asignacion_id = ua.id
							                        INNER JOIN mepa_usuario_asignacion_detalle uada
							                        ON ua.id = uada.mepa_usuario_asignacion_id 
							                        AND uada.mepa_asignacion_rol_id = 2 AND uada.status = 1
												WHERE uada.usuario_id = '".$usuario_id."' 
													AND a.situacion_etapa_id = 6
												GROUP BY a.usuario_asignado_id
												ORDER BY concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) ASC
                                            ";
                                            
                                            $list_query = $mysqli->query($query);
                                            
                                            while ($li = $list_query->fetch_assoc()) 
                                            {
                                                ?>
                                                    <option value="<?php echo $li["id"]; ?>"><?php echo $li["nombre"]; ?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
								<button 
									type="button" 
									name="mepa_aumento_asignacion_btn_buscar"
									class="btn btn-success btn-block btn-sm"
									id="mepa_aumento_asignacion_btn_buscar"
									data-button="request"
									data-toggle="tooltip"
									data-placement="top"
									title="Buscar"
									style="position: relative; bottom: -19px; margin-bottom: 30px;"
									onclick="mepa_aumento_asignacion_buscar();">
									<i class="glyphicon glyphicon-search"></i>
									Buscar
								</button>
							</div>

						</form>
					</fieldset>
				</div>
			</div>
		</div>

		<div class="content container-fluid" style="margin-left: 0px; margin-right: 0px; padding-left: 0px; padding-right: 0px;">
			<div class="col-md-8 col-lg-8 mt-3" id="mepa_aumento_asignacion_div_tabla" style="display: none;">

				<div class="row mt-3" id="mepa_aumento_asignacion_listar_asignacion_table" style="">
					<table id="mepa_aumento_asignacion_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th class="text-center">Nº</th>
								<th class="text-center">Usuario</th>
								<th class="text-center">Empresa</th>
								<th class="text-center">Zona</th>
								<th class="text-center">Fondo</th>
								<th class="text-center">Solicitar</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			
			<div class="col-xs-12 col-md-4 col-lg-4 mt-3" id="mepa_aumento_asignacion_div_solicitar" style="display: none;">
				<div class="panel">
					<!-- Panel Heading -->
					<div class="panel-heading">
						<div class="panel-title">
							Nueva Solicitud
						</div>
					</div>
					<div class="panel-body">
						
						<form id="form_sec_mepa_aumento_nueva_solicitud" name="form_sec_mepa_aumento_nueva_solicitud" method="POST" enctype="multipart/form-data" autocomplete="off">
							
							<input type="hidden" value="0" id="mepa_aumento_solicitud_id_asignacion_nueva_solictud">
							<h5>
								Usuario Solicitante: <small id="mepa_aumento_solicitud_usuario" style="font-size: 12px;"></small>
							</h5>
							<h5>
								Empresa: <small id="mepa_aumento_solicitud_empresa" style="font-size: 12px;"></small>
							</h5>
							<h5>
								Zona: <small id="mepa_aumento_solicitud_zona" style="font-size: 12px;"></small>
							</h5>
							<h5>
								Fondo: <small id="mepa_aumento_solicitud_fondo" style="font-size: 12px;"></small>
							</h5>

							<div style="margin-right: 10px; margin-left: 5px;">
								<div class="form-group">
									<div class="control-label">Tipo Solicitud:</div>
									<select
			                            class="form-control input_text sec_mepa_aumento_reduccion_asignacion_select_filtro sec_mepa_aumento_form_txt_tipo_solicitud"
			                            data-live-search="true" 
			                            id="sec_mepa_aumento_form_txt_tipo_solicitud" 
			                            title="">
			                            <option value="0">-- Seleccione --</option>
			                            <?php
			                            $sel_query = $mysqli->query(
			                                "
												SELECT 
													id, nombre 
												FROM mepa_tipos_solicitud
												WHERE id iN (9, 10)
			                                ");

			                            while($sel=$sel_query->fetch_assoc())
			                            {
			                                
			                                ?>
			                                    <option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
			                                <?php
			                            }
			                            ?>
			                        </select>
								</div>
							</div>

							<div style="margin-top: 10px; margin-right: 10px; margin-left: 5px;">
								<div class="form-group">
									<div class="control-label">Monto S/:</div>
									<input type="txt" name="" class="form-control sec_mepa_aumento_form_txt_monto" id="sec_mepa_aumento_form_txt_monto">
								</div>
							</div>
							
							<div style="margin-top: 10px; margin-right: 10px; margin-left: 5px;">
								<div class="form-group">
									<div class="control-label">Motivo:</div>
									<textarea type="text" id="sec_mepa_aumento_form_txt_motivo" class="form-control" autocomplete="off" value="" placeholder="Ingrese el motivo" cols="4" maxlength="100"></textarea>
								</div>
							</div>

							<div style="margin-top: 10px; margin-right: 10px; margin-left: 5px;">
								<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_nueva_solicitud" onclick="mepa_aumento_asignacion_btn_nueva_solicitud()">
									<i class="icon fa fa-check"></i>
									<span id="demo-button-text">Solicitar</span>
								</button>
							</div>
						</form>
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
