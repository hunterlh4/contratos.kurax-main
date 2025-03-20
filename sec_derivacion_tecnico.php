<?php
global $mysqli;
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '".$sec_id."' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];
if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	include("403.php");
	return false;
	die();
}

if(isset($_GET["buscador"]) && !empty($_GET["buscador"])){
	$where_tecnicos = $_GET["buscador"];
	$ids_tecnicos = implode(',', unserialize(urldecode(stripslashes($where_tecnicos))));
}

$query_tecnicos = "SELECT u.id ,
			CONCAT(p.nombre ,' ', IFNULL(p.apellido_paterno, '')) AS usuario
			FROM tbl_usuarios u
			LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
			WHERE p.area_id = 37
			AND p.cargo_id = 20
			AND u.estado  = 1";
$result_tecnicos = $mysqli->query($query_tecnicos);
while ($row = $result_tecnicos->fetch_assoc()){
    $tecnicos1[] = $row;
}

$query = "SELECT u.id ,
			CONCAT(p.nombre ,' ', IFNULL(p.apellido_paterno, '')) AS usuario
			FROM tbl_usuarios u
			LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
			WHERE p.area_id = 37
			AND p.cargo_id = 20
			AND u.estado  = 1
			-- AND u.id IN (7174,7175)
			";
if(isset($_GET["buscador"]) && !empty($_GET["buscador"])){
	$query.= " AND u.id in ($ids_tecnicos)";
}
$result = $mysqli->query($query);
$tecnicos = [];
while ($row = $result->fetch_assoc()){
    $tecnicos[] = $row;
}
$query = "SELECT l.id  ,l.nombre as 'local',sm.id as 'solicitud_id', sm.tecnico_id
	FROM wwwapuestatotal_gestion.tbl_solicitud_mantenimiento sm
	LEFT JOIN tbl_locales l on l.id = sm.local_id 
	WHERE  sm.tecnico_id IS NOT null
	AND sm.estado = 'Programado'
	ORDER BY sm.id DESC";
$result = $mysqli->query($query);
$solicitudes_derivadas_tecnico = [];
while ($row = $result->fetch_assoc()){
    $solicitudes_derivadas_tecnico[$row["tecnico_id"]][] = $row;
}
$estados_solicitud  = ["Programado","Terminado"];
?>
 
<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row mb-4">
			<div class="col-xs-12 text-center">
                <div class="page-title"><i role="button" title="Recargar Derivaciòn Tècnico" class="icon icon-inline fa fa-fw fa-envelope" id="sec_derivacion_tecnico_recargar"></i> Derivación Mantenimiento</div>
			</div>
		</div>
		<div class="row mb-4">
			<div class="col-xs-12">
				<div class="col-xs-12">
	                <div class="form-inline">
	                </div>
	            </div>
			</div>
		</div>
	</div>

	<!-- FILTRO TECNICO -->
	<form action="sys/set_mantenimiento_tecnico_derivacion_buscador.php" method="POST">
		<div class="row container_filtros_recaudacion">
			<div id="div-fil-tecnicos" class="col-lg-2 col-xs-12 mt-3">
				<p class="text-center">Técnicos</p>
				<select name="tecnico_select[]" 
					id="tecnico_select" 
					multiple="true" 
					class="form-control input-sm select2" 
					style="width:100%">
					<?php foreach ($tecnicos1 as $key => $value)
					{
						?>
						<option value="<?php echo $value["id"];?>">
							<?php echo $value["usuario"]?>
						</option>
					<?php
					}?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-xs-12 mt-3 mb-4">
				<!--<p class="text-center">&nbsp;</p>-->
				<button class="btn btn-success" id="btn_solicitud_estimacion_search">
					<span class="glyphicon glyphicon-search"></span> Consultar
				</button>
			</div>
		</div>
	</form>

	<div class="st_derivacion_contenedor">
		<?php foreach($tecnicos as $tecnico)
		{ 
			$solicitudes_cant = 0;
			$grid_row = 1;
			if(isset($solicitudes_derivadas_tecnico[$tecnico["id"]]))
			{
				$solicitudes_cant = count($solicitudes_derivadas_tecnico[$tecnico["id"]]);
				
				$grid_row = $solicitudes_cant > 2 ? "3":"1";
			}
			?>

				<div class="st_card" >
					<div class="st_card_cabecera">
						<div class="st_card_cabecera_usu"><?php echo $tecnico["usuario"];?></div>
						<span class="st_card_cabecera_qty"><?php echo $solicitudes_cant;?></span>
					</div>
					<div class="st_card_list_contenedor">
						<?php
						if(isset($solicitudes_derivadas_tecnico[$tecnico["id"]]))
						{
							foreach($solicitudes_derivadas_tecnico[$tecnico["id"]] as $sol)
							{?>
							<div class="st_card_list_fila sec_derivacion_tecnico_solicitud_detalle"
							data-solicitud_id = "<?php echo $sol["solicitud_id"];?>">
								<div class="st_card_list_state">
									<div class="st_card_list_state_bar"></div>
								</div>
								<div class="st_card_list_local"
									
								>
									<?php echo $sol["local"];?>
								</div>
								<div class="st_card_list_buttons">
									<span class="fa fa-align-justify"></span>
								</div>
							</div>
							<?php 
							}
							?>
						<?php
						}
						/*else
						{?>
							No hay Solicitudes de Mantenimiento
						<?php 
						}*/?>
					</div>
			</div>
		<?php }?>
	</div>
</div>

<div class="modal fade" id="modal_detalle" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>      
				<h5 class="modal-title" id="mdCrearGrupoTitle">Solicitud de Mantenimiento</h5>
			</div>
			<div class="modal-body">
				<div class="col-xs-12">
					<div id="boxMessage" class="alert alert-success text-center" style="display: none"></div>
				</div>
				<div class="row">
				
					<div class="col-sm-8 col-sm-offset-2" >
						<h5 id="txtGrupoTitle"></h5>
						<form method="POST" class="form-horizontal">
							<div class="form-group" style="display:none;">
								<label class="col-sm-3 control-label" for="id">Id</label>
								<div class="col-xs-12 col-sm-9">
									<input type="text" class="form-control-plaintext" id="id" name="id">	
								</div>
							</div>
							<div class="form-group">
								<label  class="col-sm-3 control-label" for="created_at">Fecha Ingreso</label>
								<div class="col-xs-12 col-sm-9"><p class="form-control-static" id="created_at">-</p>
								</div> 
							</div>
							<div class="form-group">
								<label  class="col-sm-3 control-label" for="zona">Zona</label>
								<div class="col-xs-12 col-sm-9"><p class="form-control-static" id="zona">-</p>
								</div>
							</div>
							<div class="form-group">
								<label  class="col-sm-3 control-label" for="local">Tienda</label>
								<div class="col-sm-9"><p class="form-control-static" id="local">-</p>
								</div>
							</div>
							<div id="div-sistema-modal" class="form-group">
								<label  class="col-sm-3 control-label" for="sistema">Sistema</label>
								<div class="col-sm-9"><p class="form-control-static" id="sistema">-</p>
								</div>
							</div>
							<div id="div-tipo-mant-modal" class="form-group">
								<label  class="col-sm-3 control-label" for="tipo_mantenimiento">Tipo Mantenimiento</label>
								<div class="col-xs-12 col-sm-9">
									<select id="tipo_mantenimiento" name="tipo_mantenimiento" class="form-control" style="width:100%">
										<option value = "Emergencia" selected>Emergencia</option>
										<option value = "Preventivo">Preventivo</option>
										<option value = "Correctivo">Correctivo</option>
									</select>
								</div>
							</div>
						     
							<div style="" class="form-group">
								<label class="col-sm-3 control-label" for="txtGroupDesc">Reporte</label>
								<div class="col-xs-12 col-sm-6"><div class="form-control-static" id="comentario">-</div>
								</div>
							</div>
							<div class="form-group">
								<label  class="col-sm-3 control-label" for="estado">Estado</label>
								<div class="col-xs-12 col-sm-9">
									<select id="estado" name="estado" class="form-control" style="width:100%">
										<?php foreach ($estados_solicitud as $value) {?>
											<option value="<?php echo $value;?>"><?php echo $value;?></option>
										<?php }?>
									</select>
								</div>
							</div>
							<br>		
							<div class="form-group foto_terminado">
								<label  class="col-sm-3 control-label" for="estado">Foto</label>
								<div class="col-sx-12 col-sm-9">
									<input type="file" name="foto_terminado_update" id="foto_terminado_update">
									<div class="col-xs-2 imagenes_views" id="imagen_terminado">
										<img id="foto_terminado" name="foto_terminado" class="imagenes_modal">
									</div>
								</div>
							</div>
							<div class="form-group div_comentario_terminado">
								<label  class="col-sm-3 control-label" for="estado">Comentario</label>
								<div class="col-xs-12 col-sm-9">
									<textarea id = "comentario_terminado" name = "comentario_terminado" style="width:50%" rows="5"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label  class="col-sm-3 control-label" for="estado">Imagen</label>
								<div class="col-xs-12 col-sm-5">
									<div class="col-xs-12 imagenes_views" id="imagenes_cargar">
									</div>
								</div>
							</div>
					   
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-success open_btn" title="Abrir" id="sec_derivacion_tecnico_guardar_btn"><span class='glyphicon glyphicon-floppy-save'></span> Guardar</button>
					<button class="btn btn-default close_btn" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="vista_previa_modal" data-backdrop="static" tabindex="-1" >
	<div class="modal-dialog modal-xs">
		<div class="modal-content modal-rounded modal_imagen">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<img id="img01" style="width:100%">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default " data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>