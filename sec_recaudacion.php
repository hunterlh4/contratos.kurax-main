<div class="content container-fluid cuadro_de_ventas_form_contrato contenedor_general_sec_recaudacion_filtros"> <div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Recaudacion<?php 
					if($sub_sec_id=="cdv"){ ?> - Importacion<?php }
					if($sub_sec_id=="liquidaciones"){ ?> - Liquidaciones<?php }
					if($sub_sec_id=="liquidacion_productos"){ ?> - Liquidación Productos<?php }
					if($sub_sec_id=="procesos"){ ?> - Procesos<?php }
					if($sub_sec_id=="pagos_manuales"){ ?> - Pagos Manuales<?php }
					if($sub_sec_id=="transacciones_bancarias"){ ?> - Transacciones Bancarias<?php }
					if($sub_sec_id=="fraccionamiento"){ ?> - Fraccionamiento<?php }
					?>
				</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 ">
				<?php
				if($item_id){
				}else{
					if($sub_sec_id=="cdv"){
						// print_r($usuario_permisos);
						if(array_key_exists(30,$usuario_permisos)){
							if(in_array("import", $usuario_permisos[30])){
								?>
								<button
									class="btn btn-primary recaudacion_import_btn" data-button="import">
										<span class="glyphicon glyphicon-import"></span> 
										Importar CSV
								</button>
								<?php
							}
						}
						if(array_key_exists(30,$usuario_permisos)){
							if(in_array("reprocess_all", $usuario_permisos[30])){
								?>
								<button class="btn btn-primary rec_reprocess_all_btn pull-right" data-button="reprocess_all">RE-Procesar</button>
								<?php
							}
						}
					}
					if($sub_sec_id=="liquidaciones"){
						?>
						<button style="display:none;"
							type="button" 
							class="btn btn-success table_to_xls_btn" 
							data-table="table-liquidaciones"
							><span class="glyphicon glyphicon-export"></span> Exportar Lista</button>
						
						<?php
					}
					if($sub_sec_id=="procesos"){
						if(array_key_exists(35,$usuario_permisos) && in_array("generate", $usuario_permisos[35])){
							?><button class="btn btn-warning recaudacion_generar_liquidaciones_btn">Generar Liquidaciones</button><?php
						}
					}
					if($sub_sec_id=="pagos_manuales"){
						if(array_key_exists(36,$usuario_permisos) && in_array("new", $usuario_permisos[36])){
							?>
							<button class="btn btn-warning recaudacion_add_pago_manual_btn">Agregar Pago Manual</button>
							<button id="btnExportPagoManual" class="btn btn-success recaudacion_add_pago_manual_btn pull-right">Exportar</button>
						<?php } ?>
						<?php if(array_key_exists(36,$usuario_permisos) && in_array("importar_xls", $usuario_permisos[36])) { ?>
							<button id="btnImportPagoManual" class="btn btn-success"><span class="glyphicon glyphicon-import"></span>Importar</button>
						<?php }?>
						<!-- <button class="btn btn-primary recaudacion_modificar_pago_manual_btn" data-button="new" data-opt="modificar_apuesta">Modificar Apuesta</button>
						<button class="btn btn-primary recaudacion_add_transaccion_manual_btn" data-button="new" data-opt="agregar_transaccion_de_terminal">Agregar Transaccion de Terminal</button> -->
						<?php
					}
					if($sub_sec_id=="transacciones_bancarias"){
						$view_estado = 1;
						if(isset($_GET["estado"])){
							$view_estado = $_GET["estado"];
						}
						?>
						<!-- <button class="btn btn-primary transacciones_bancarias_btn" data-button="new" data-opt="new"><span class="glyphicon glyphicon-plus"></span> Agregar</button> -->
						<button class="btn btn-primary transacciones_bancarias_btn" data-button="import" data-opt="import"><span class="glyphicon glyphicon-import"></span> Importar</button>
						<!-- <button class="btn btn-success transacciones_bancarias_btn" data-button="assig" data-opt="assig"><span class="glyphicon glyphicon-random"></span> Asignar Seleccionados</button> -->
						<button class="btn btn-success trans_ass_batch_btn"><span class="glyphicon glyphicon-random"></span> Asignar Seleccionados</button>
						<?php
						if($view_estado){
							?><a 
							href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=<?php echo $sub_sec_id;?>&amp;estado=0"
							class="btn btn-default pull-right"><i class="glyphicon glyphicon-eye-close"></i> Ver Ocultos</a><?php
						}else{
							?><a 
							href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=<?php echo $sub_sec_id;?>"
							class="btn btn-success pull-right"><i class="glyphicon glyphicon-eye-open"></i> Ver Activos</a><?php
						}
					}
					if($sub_sec_id=="fraccionamiento"){
						?>
						<button class="btn btn-primary fraccionamiento_add_btn" data-button="new" data-opt="new"><span class="glyphicon glyphicon-plus"></span> Agregar</button>
						<?php
					}
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php
if($item_id){
}else{
	$servicios=array();	//	SERVICIOS
		$srv_query = $mysqli->query("SELECT 
				s.id,s.nombre 
			FROM tbl_servicios s
			INNER JOIN tbl_canales_venta c ON c.servicio_id = s.id
			WHERE  s.estado = '1'
			AND c.en_liquidacion = 1
			group by s.id
			ORDER BY id ASC");
		while($srv=$srv_query->fetch_assoc()){
			$servicios[$srv["id"]]=$srv;
		}
		
	include("sec_recaudacion_liquidaciones.php");
	include("sec_recaudacion_liquidacion_productos.php");
	include("sec_recaudacion_cdv.php");
	include("sec_recaudacion_procesos.php");
	include("sec_recaudacion_pagos_manuales.php");
	include("sec_recaudacion_modulo_pagos.php");
	include("sec_recaudacion_transacciones_bancarias.php");
	include("sec_recaudacion_fraccionamiento.php");
}
?>