<div class="content container-fluid cuadro_de_ventas_form_contrato contenedor_general_sec_recaudacion_filtros">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Recaudacion<?php 
					if($sub_sec_id=="cdv"){ ?> - Importacion<?php }
					if($sub_sec_id=="liquidaciones"){ ?> - Liquidaciones<?php }
					if($sub_sec_id=="procesos"){ ?> - Procesos<?php }
					if($sub_sec_id=="pagos_manuales"){ ?> - Pagos Manuales<?php }
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
						?>
						<button
							class="btn btn-primary recaudacion_import_btn">
								<span class="glyphicon glyphicon-import"></span> 
								Importar CSV
						</button>
						<!-- <button
							class="btn btn-primary recaudacion_import_from_bc_btn ">
								<span class="glyphicon glyphicon-cloud-download"></span>
								Importar desde Bet Construct</button> -->
						<button class="btn btn-primary rec_reprocess_all_btn pull-right">RE-Procesar</button>
						<!-- <button class="btn btn-danger rec_hide_all_btn pull-right">Archivar</button> -->
						<?php
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
						?>
						<button
							class="btn btn-warning recaudacion_generar_liquidaciones_btn">Generar Liquidaciones</button>
						<?php
					}
					if($sub_sec_id=="pagos_manuales"){
						?>
						<button
							class="btn btn-warning recaudacion_add_pago_manual_btn">Agregar Pago</button>
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
		$srv_query = $mysqli->query("SELECT id,nombre FROM tbl_servicios WHERE  estado = '1' ORDER BY id ASC");
		while($srv=$srv_query->fetch_assoc()){
			$servicios[$srv["id"]]=$srv;
		}
	if($sub_sec_id=="liquidaciones"){
		$modelo = 4;
		if(isset($_GET["modelo"])){
			$modelo = $_GET["modelo"];
		}

		
			?>
			<input type="hidden" class="export_filename" value="export_<?php echo date("c");?>">
			<div class="container-fluid">	
				<div class="row">
					<?php
					if(isset($_GET["proceso_unique_id"])){
						$proceso = $mysqli->query("SELECT pro.at_unique_id, pro.fecha, pro.fecha_inicio, pro.fecha_fin, pro.servicio_id, pro.estado
														,u.usuario
													FROM tbl_transacciones_procesos pro
													LEFT JOIN tbl_usuarios u ON (u.id = pro.usuario_id)
													WHERE pro.at_unique_id = '".$_GET["proceso_unique_id"]."'")->fetch_assoc();

						?>
						<div class="container-fluid">
							<div class="col-xs-12">
							<br>
								<dl class="dl-horizontal">
									<dt>Proceso ID:</dt>
									<dd><?php echo $proceso["at_unique_id"];?></dd>
									<dt>Fecha de Proceso:</dt>
									<dd><?php echo $proceso["fecha"];?></dd>
									<dt>Periodo Inicio:</dt>
									<dd><?php echo strstr($proceso["fecha_inicio"]," ",true);?></dd>
									<input type="hidden" name="pro_liq_fecha_inicio" value="<?php echo $proceso["fecha_inicio"];?>">
									<dt>Periodo Fin:</dt>
									<dd><?php echo strstr($proceso["fecha_fin"]," ",true);?></dd>
									<input type="hidden" name="pro_liq_fecha_fin" value="<?php echo $proceso["fecha_fin"];?>">
									<dt>Proveedor:</dt>
									<dd><?php echo $servicios[$proceso["servicio_id"]]["nombre"];?></dd>
									<dt>Usuario:</dt>
									<dd><?php echo $proceso["usuario"];?></dd>
									<dt>Estado:</dt>
									<dd><?php 
										if($proceso["estado"]==0){
											?><span class="text-warning"><?php echo "Abierto";?></span><?php
										}elseif($proceso["estado"]==1){
											?><span class="text-success"><?php echo "Cerrado";?></span><?php
										}elseif($proceso["estado"]==2){
											?><span class="text-danger"><?php echo "Reabierto";?></span><?php
										}elseif($proceso["estado"]==5){
											?><span class="text-dark"><?php echo "Eliminado";?></span><?php
										}
									?></dd>
								</dl>
								
							</div>
						</div>
						<?php
					}else{
						?>
						<div class="col-xs-12 container_nav_tabs_recaudacion">
							<ul class="nav nav-tabs navtabcustom" role="tablist">
								<?php
								for ($im=1; $im <= 10; $im++) { 
									?>
									<li
										role="presentation"
										class="<?php if($modelo==$im){?> active<?php } ?>" >
										<a	class="hvr-underline-from-center <?php if($modelo==$im){?> btn-success<?php } ?> <?php if($im>5){?> not-active <?php }?>"
											href="?sec_id=recaudacion&amp;sub_sec_id=liquidaciones&amp;modelo=<?php echo $im;?>"
											data-toggle="tooltip"
											data-placement="top"
											title="Modelo <?php echo $im;?>"
											>Modelo <?php echo $im;?></a>
									</li>
									<?php
								}
								?>																																
							</ul>
						</div>
						<?php
					}
					?>
					<!--
					<div class="col-xs-12" >
						<div class="col-xs-12 col-sm-12	col-md-6 col-lg-6">
							<div class="col-xs-12 col-sm-6 containerfechainicial">
								<p class="text-left iniciocustom">Fecha Inicio </p>
								<div class="form-group form-group-inicio_fecha divfechainiciocustom">
									<label class="col-xs-4 control-label fechainiciocustom" for="input_text-inicio_fecha">Fecha</label>
									<div class="input-group col-xs-8 ">
										<input class="fecha_inicio_enviar"
							    			type="hidden" 
											name="liq_filtro_inicio_fecha " 
											value="<?php //echo $liquidaciones_filtro["liq_filtro_inicio_fecha"];?>" 
							    			data-real-date="input_text-liq_filtro_inicio_fecha">
										<input 
											type="text" 
											class="form-control filtro_datepicker fecha_inicio_enviar " 
											id="input_text-liq_filtro_inicio_fecha" 
											value="<?php //echo date("d-m-Y", strtotime($liquidaciones_filtro["liq_filtro_inicio_fecha"]));?>">
										<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-liq_filtro_inicio_fecha"></label>
									</div>
								</div>
								
								<div class="form-group form-group-inicio_hora containerhorainicio" style="display:none;">
									<label class="col-xs-4 control-label horainiciocustom" for="input_text-inicio_hora">Hora</label>
									<div class="input-group col-xs-8">
										<input 
											type="text" 
											class="form-control timepicker import_data hora_inicio_enviar " 
											id="input_text-inicio_hora" 
											value="<?php //echo $liquidaciones_filtro["liq_filtro_inicio_hora"];?>" 
											name="liq_filtro_inicio_hora" 										 
											>
										<label class="input-group-addon glyphicon glyphicon-time" for="input_text-inicio_hora"></label>
									</div>
								</div>
								
							</div>
							<div class="col-xs-12 col-sm-6">
								<p class="text-left fincustom">Fecha Fin</p>
								<div class="form-group form-group-fin_fecha">
									<label class="col-xs-4 control-label fechafincustom" for="input_text-fin_fecha">Fecha</label>
									<div class="input-group col-xs-8 ">
										<input class="fecha_fin_enviar"
							    			type="hidden" 
											name="liq_filtro_fin_fecha" 
											value="<?php //echo $liquidaciones_filtro["liq_filtro_fin_fecha"];?>" 
							    			data-real-date="input_text-liq_filtro_fin_fecha">
										<input 
											type="text" 
											class="form-control filtro_datepicker fecha_fin_enviar " 
											id="input_text-liq_filtro_fin_fecha" 
											value="<?php //echo date("d-m-Y", strtotime($liquidaciones_filtro["liq_filtro_fin_fecha"]));?>" >
										<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-liq_filtro_fin_fecha"></label>
									</div>
								</div>
								
								<div class="form-group form-group-fin_hora" style="display:none;">
									<label class="col-xs-4 control-label horafincustom" for="input_text-fin_hora">Hora</label>
									<div class="input-group col-xs-8">
										<input 
											type="text" 
											class="form-control timepicker import_data hora_fin_enviar " 
											id="input_text-liq_filtro_fin_hora" 
											value="<?php //echo $liquidaciones_filtro["liq_filtro_fin_hora"];?>" 
											name="liq_filtro_fin_hora">
										<label class="input-group-addon glyphicon glyphicon-time" for="input_text-liq_filtro_fin_hora"></label>
									</div>
								</div>
							
							</div>
						</div>							
						<div class="col-xs-12 col-sm-12	col-md-5 col-lg-5 contenedorgenerallocal">
							<div class="col-xs-12 col-sm-12	col-md-11 col-lg-11 contenedorlocal">

								<div class="col-xs-12 col-sm-12	col-md-6 col-lg-6 subcontenedorlocal">
									<p class="text-center localcustom">Local</p>
									<select class="local local_recaudacion" name="local_recaudacion" multiple="true">
									    <option value="all">Todos</option>
									</select>
								</div>

								<div class="col-xs-12 col-sm-12	col-md-6 col-lg-6">											
									<p class="text-center canalventascustom">Canal de venta</p>
									<select class="canalventarecaudacion canal_venta_recaudacion" multiple="true" >
									    <option value="all">Todos</option>
									</select>
								</div>	

							</div>

							<div class="col-xs-12 col-sm-12	col-md-1 col-lg-1 pull-right">
								<button type="button" name="liquidaciones_filtro" value="1" class="filtarrecaudacion hvr-float-shadow btnfiltarrecaudacion" data-toggle="tooltip" data-placement="top" title="Filtrar recaudación"></button>
							</div>
						</div>			
					</div>
					-->

					<div class="col-lg-12 col-md-12 container_filtros_recaudacion">

						<div class="col-lg-2 col-xs-12">
							<p class="text-center iniciocustom">Fecha Inicio </p>
							<div class="form-group">
								<div class="input-group col-xs-12 ">
									<input class="fecha_inicio_enviar"
						    			type="hidden" 
										name="liq_filtro_inicio_fecha " 
										value="<?php //echo $liquidaciones_filtro["liq_filtro_inicio_fecha"];?>" 
						    			data-real-date="input_text-liq_filtro_inicio_fecha">
									<input 
										type="text" 
										class="form-control filtro_datepicker fecha_inicio_enviar " 
										id="input_text-liq_filtro_inicio_fecha" 
										value="<?php //echo date("d-m-Y", strtotime($liquidaciones_filtro["liq_filtro_inicio_fecha"]));?>">
									<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-liq_filtro_inicio_fecha"></label>
								</div>
							</div>
						</div>
						<div class="col-lg-2 col-xs-12">
								<p class="text-center fincustom">Fecha Fin</p>
								<div class="form-group form-group-fin_fecha">
									<div class="input-group col-xs-12 ">
										<input class="fecha_fin_enviar"
							    			type="hidden" 
											name="liq_filtro_fin_fecha" 
											value="<?php //echo $liquidaciones_filtro["liq_filtro_fin_fecha"];?>" 
							    			data-real-date="input_text-liq_filtro_fin_fecha">
										<input 
											type="text" 
											class="form-control filtro_datepicker fecha_fin_enviar " 
											id="input_text-liq_filtro_fin_fecha" 
											value="<?php //echo date("d-m-Y", strtotime($liquidaciones_filtro["liq_filtro_fin_fecha"]));?>" >
										<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-liq_filtro_fin_fecha"></label>
									</div>
								</div>
						</div>
						<div class="col-lg-2 col-xs-12">
							<p class="text-center localcustom">Local</p>
							<select id="local_recaudacion" class="local local_recaudacion" name="local_recaudacion" multiple="true">
							    <option value="all">Todos</option>
							</select>
						</div>
						<div class="col-lg-2 col-xs-12">
							<p class="text-center canalventascustom">Canal de venta</p>
							<select id="canalventarecaudacion" class="canalventarecaudacion canal_venta_recaudacion" multiple="true" >
							    <option value="all">Todos</option>
							</select>
						</div>
						<div class="col-lg-2 col-xs-12">
							<p class="text-center redcustom">Red</p>
							<select id="red_recaudacion" class="red red_recaudacion" name="red_recaudacion" multiple="true">
							    <option value="0">Sin Red</option>
							    <option value="1">Bet Bar</option>
							    <option value="2">Dalu</option>					    					    
							</select>
						</div>
						<div class="col-lg-2 col-xs-12">
							<button type="button" name="liquidaciones_filtro" value="1" class="filtarrecaudacion btnfiltarrecaudacion btn btn-primary" data-toggle="tooltip" data-placement="top" title="Filtrar recaudación"><span class="glyphicon glyphicon-search"></span> Consultar</button>								
						</div>																								

					</div>
				</div>
			</div>
		<input type="hidden" value="<?php echo $modelo; ?>" class="sec_recaudaciones_tipo_de_modelo"/>
		<?php
		if($modelo==1){
			?>
			<div class="container-fluid container_datatable_recaudacion">
				<table id="tabla_sec_recaudacion" class="display table-liquidaciones-modelo-1" >
			        <thead>
			            <tr>
			                <th rowspan="2" class="cliente_local_cabecera_modelo_uno" >LOCAL</th>
			                <th rowspan="2" class="dias_procesados_cabecera_modelo_uno" >DIAS</th>
			                <th colspan="4" class="titulo_general_colspan_modelo_uno" >WEB</th> 
			                <th colspan="4" class="titulo_general_colspan_modelo_uno" >PBET</th>  
			                <th colspan="4" class="titulo_general_colspan_modelo_uno" >SBT-NEGOCIOS</th> 
			                <th colspan="4" class="titulo_general_colspan_modelo_uno">JV GLOBAL BET</th>
			                <th colspan="4" class="titulo_general_colspan_modelo_uno" >SBT-BC</th>
			                <th colspan="4" class="titulo_general_colspan_modelo_uno" >JV GOLDEN RACE</th> 
			                <th colspan="4" class="titulo_general_colspan_modelo_uno" >TOTAL</th>                                                                                       
			            </tr>        
			            <tr>
			                <th class="apostado_cabecera_modelo_uno">apostado</th>
			                <th class="ganado_cabecera_modelo_uno">ganado</th>
			                <th class="pagado_cabecera_modelo_uno">pagado</th>
			                <th class="produccion_cabecera_modelo_uno">produccion</th>

			                <th class="apostado_cabecera_modelo_uno">apostado</th>
			                <th class="ganado_cabecera_modelo_uno">ganado</th>
			                <th class="pagado_cabecera_modelo_uno">pagado</th>
			                <th class="produccion_cabecera_modelo_uno">produccion</th>

				            <th class="apostado_cabecera_modelo_uno">apostado</th>
			                <th class="ganado_cabecera_modelo_uno">ganado</th>
			                <th class="pagado_cabecera_modelo_uno">pagado</th>
			                <th class="produccion_cabecera_modelo_uno">produccion</th>
			                
			                <th class="apostado_cabecera_modelo_uno">apostado</th>
			                <th class="ganado_cabecera_modelo_uno">ganado</th>
			                <th class="pagado_cabecera_modelo_uno">pagado</th>
			                <th class="produccion_cabecera_modelo_uno">produccion</th>


					        <th class="apostado_cabecera_modelo_uno">apostado</th>
			                <th class="ganado_cabecera_modelo_uno">ganado</th>
			                <th class="pagado_cabecera_modelo_uno">pagado</th>
			                <th class="produccion_cabecera_modelo_uno">produccion</th>
			                
			                <th class="apostado_cabecera_modelo_uno">apostado</th>
			                <th class="ganado_cabecera_modelo_uno">ganado</th>
			                <th class="pagado_cabecera_modelo_uno">pagado</th>
			                <th class="produccion_cabecera_modelo_uno">produccion</th>


					        <th class="apostado_cabecera_modelo_uno">apostado</th>
			                <th class="ganado_cabecera_modelo_uno">ganado</th>
			                <th class="pagado_cabecera_modelo_uno">pagado</th>
			                <th class="produccion_cabecera_modelo_uno">produccion</th>

			            </tr>
			        </thead>
			    	<tfoot>
				        <tr>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>				            				            				            
 				        </tr>    	
					</tfoot>				        
				</table>
			</div>
			<?php
		}
		if($modelo==2){
			?>	
			<div class="container-fluid container_datatable_recaudacion">				
				<table id="tabla_sec_recaudacion" cellspacing="0" width="100%" class="table-liquidaciones-modelo-2">
			        <thead>
			            <tr>
			                <th class="colunmas_local_modelo_dos_head_th">LOCAL</th>
			                <th>DIAS</th>
			                <th>CANAL DE VENTA</th>
			                <th class="colunmas_apostado_modelo_dos_head_th">APOSTADO</th>
			                <th class="colunmas_ganado_modelo_dos_head_th">GANADO</th>
			                <th class="colunmas_pagado_modelo_dos_head_th">PAGADO</th>
			                <th class="colunmas_produccion_modelo_dos_head_th">PRODUCCIÓN</th>
			            </tr>
			        </thead>
				    <tfoot>
				        <tr>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
 				        </tr>    	
					</tfoot>			        
			        <tbody>
			        </tbody>
				</table>
			</div>
			<?php
		}
		if($modelo==3){
			?>
			<div class="container-fluid container_datatable_recaudacion">				
				<table id="tabla_sec_recaudacion" cellspacing="0" width="100%" class="table-liquidaciones-modelo-3">
			        <thead>
			            <tr>
			                <th class="colunmas_local_modelo_tres_head_th">LOCAL</th>
			                <th class='columna_dias_modelo_tres_head_th'>DIAS</th>
			                <th class='columna_canal_de_venta_modelo_tres_head_th'>CANAL DE VENTA</th>
			                <th class="colunmas_apostado_modelo_tres_head_th">APOSTADO</th>
			                <th class="colunmas_ganado_modelo_tres_head_th" >GANADO</th>
			                <th class="colunmas_pagado_modelo_tres_head_th">PAGADO</th>
			                <th class="colunmas_produccion_modelo_tres_head_th" >PRODUCCIÓN</th>
			                <th class='columnas_porcentaje_modelo_tres_head_th'>PORCENTAJE</th>
			                <th class='columnas_cliente_modelo_tres_head_th'>CLIENTE</th>
			                <th class='columnas_freegames_modelo_tres_head_th'>FREEGAMES</th>
			            </tr>
			        </thead>
				    <tfoot>
				        <tr>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				        </tr>    	
					</tfoot>				                		        
			        <tbody>
			        </tbody>
				</table> 
			</div>
			<?php
		}
		if($modelo==4){
			?>
			<div class="container-fluid container_datatable_recaudacion">
				<table id="tabla_sec_recaudacion" cellspacing="0" width="100%"   class="cell-border table-liquidaciones-modelo-4">
			        
			        <thead>
			            <tr>
			                <th class="columna_nombre_local_head_th">
			                    <div class="contenedornombrecolumna">NOMBRE LOCAL</div>
			                </th>
			                <th class="columna_dias_procesados_head_th">
			                    <div class="contenedornombrecolumna">DIAS</div>
			                </th>
			                <th class="canal_de_venta_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">CANAL DE VENTA</div>
			                </th>
			                <th class="columnasnumeros_head_th ">
			                    <div class="contenedornombrecolumna">TOTAL DEPOSITADO</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">TOTAL ANULADO /<br> RETIRADO</div>
			                </th>
			                <th class="columnasnumeros_head_th columna_apostado_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TOTAL APOSTADO</div>
			                </th>
			                <th class="columnasnumeros_head_th columna_ganado_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TOTAL GANADO</div>
			                </th>
			                <th class="columnasnumeros_head_th columna_pagado_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TOTAL PAGADO</div>
			                </th>
			                <th class="columnasnumeros_head_th columna_produccion_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TOTAL PRODUCCION</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">TOTAL DEPOSITADO<br> WEB</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">TOTAL RETITADO<br> WEB</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">TOTAL CAJA<br> WEB</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">PORCENTAJE CLIENTE (%)</div>
			                </th>			                
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">TOTAL CLIENTE</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">PORCENTAJE FREEGAMES (%)</div>
			                </th>			                
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">TOTAL FREEGAMES</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumnaextend">PAGADO EN <br>OTRA TIENDA</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumnaextend">PAGADO DE <br>OTRA TIENDA</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">TOTAL PAGADOS <br>FISICOS</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">CAJA FISICO</div>
			                </th>
			                <th class="columnasnumeros_head_th columna_test_diff">
			                    <div class="contenedornombrecolumna">TEST DIFF</div>
			                </th>			                
			                <th class="columnasnumeros_head_th columna_cd_balance">
			                    <div class="contenedornombrecolumna">CD BALANCE</div>
			                </th>			                
	            		</tr>
			        </thead> 
			        <tfoot>
			            <tr>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>
			                <th></th>			                
			                <th></th>
			                <th></th>			                
			                <th></th>			                			                				                				                				                
			            </tr>
			        </tfoot>       
					<tbody>					
					</tbody>
				</table>
			</div>
			<?php
		}
		if ($modelo==5) {
			?>
			<div class="container-fluid container_datatable_recaudacion">				
				<table id="tabla_sec_recaudacion" cellspacing="0" width="100%" class="table-liquidaciones-modelo-5">
			        <thead>
			            <tr>
			                <th class="colunmas_prueba_uno_modelo_cinco_head_th">PRUEBA 1</th>
			                <th class="columnas_prueba_dos_modelo_cinco_head_th">PRUEBA 2</th>
			                <th class="columnas_prueba_tres_modelo_cinco_head_th">PRUEBA 3</th>
			                <th class="colunmas_prueba_cuatro_modelo_cinco_head_th">PRUEBA 4</th>
			                <th class="colunmas_prueba_cinco_modelo_cinco_head_th" >PRUEBA 5</th>
			                <th class="colunmas_prueba_seis_modelo_cinco_head_th">PRUEBA 6</th>
			                <th class="colunmas_prueba_siete_modelo_cinco_head_th" >PRUEBA 7</th>
			                <th class="colunmas_prueba_ocho_modelo_cinco_head_th">PRUEBA 8</th>
			                <th class="colunmas_prueba_nueve_modelo_cinco_head_th">PRUEBA 9</th>
			                <th class="colunmas_prueba_diez_modelo_cinco_head_th">PRUEBA 10</th>
			            </tr>
			        </thead>
				    <tfoot>
				        <tr>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				            <th></th>
				        </tr>    	
					</tfoot>				                		        
			        <tbody>
			        </tbody>
				</table> 
			</div>	
			<?php		
		}
	}
	if($sub_sec_id=="cdv"){
		?>
		<div class="modal " id="recaudacion_import_modal">
			<div class="modal-dialog">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span>  Importar CSV</h4>
					</div>
					<div class="modal-body">
						<form method="post" action="sys/SimpleUpload.php" enctype="multipart/form-data" class="" id="recaudacion_import_form">
							<label class="h4 strong block">Servicio/Proveedor</label>
							<div class="btn-group" data-toggle="buttons">
								<?php
								foreach ($servicios as $srv_k => $srv_v) {
									if($srv_k!=1 && $srv_k!=4){
										?>
										<label class="btn btn-success">
											<input type="radio" class="import_data btn_servicio" name="servicio_id" value="<?php echo $srv_v["id"];?>" required> <?php echo $srv_v["nombre"];?>
										</label>
										<?php
									}
								}
								?>
							</div>
							<label class="h4 strong block">Tipo de reporte</label>
							<div class="btn-group tipo_servicio" data-toggle="buttons">									
								<label class="btn btn-primary active">
									<input type="radio" class="import_data" name="tipo" value="tickets" checked="checked"> Tickets
								</label>								
								<!-- <label class="btn btn-primary">
									<input type="radio" class="import_data" name="tipo" value="cashdesk"> Cajas
								</label>								
								<label class="btn btn-primary">
									<input type="radio" class="import_data" name="tipo" value="terminals"> Terminales
								</label> -->
							</div>
							<label class="h4 strong block">Archivos</label>
							<div class="files_list_holder col-xs-12">
								<div class="file col-xs-12 file_example hidden">
									<div class="progress_bg">
										<div class="progress-bar progress-bar-striped active progress-bar-success"></div>
									</div>
									<div class="filename col-xs-8">
										<span class="name"></span>
										<span class="size"></span>
									</div>
									<div class="por col-xs-2"><span class="num">0</span>%</div>
								</div>
							</div>
							<div class="form-group form-group-upload-btn">
								<input type="file" name="file" id="file" multiple accept=".csv">									
								<label class="uploader_file_name" for="file">
									<div class="btn btn-warning upload-btn" data-form="recaudacion_import_form">Seleccione</div>
								</label>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success" form="recaudacion_import_form" ><span class="glyphicon glyphicon-upload"></span> Subir</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal " id="recaudacion_import_from_bc_modal">
			<div class="modal-dialog">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span>  Importar desde Servidor</h4>
					</div>
					<div class="modal-body">
						<form method="post" action="sys/sys_transacciones.php" enctype="multipart/form-data" id="recaudacion_import_from_bc_form">
							<label class="h4 strong block">Servicio/Proveedor</label>
							<div class="btn-group" data-toggle="buttons">
								<?php
								foreach ($servicios as $srv_k => $srv_v) {
									if($srv_k==1){
										?>
										<label class="btn btn-success active">
											<input 
												type="radio" 
												class="import_bc_data btn_servicio " 
												name="servicio" 
												value="<?php echo $srv_v["id"];?>" 
												required 
												checked="checked"> <?php echo $srv_v["nombre"];?>
										</label>
										<?php
									}
								}
								?>
							</div>
							<label class="h4 strong block">Tipo de reporte</label>
							<div class="btn-group tipo_servicio" data-toggle="buttons">									
								<label class="btn btn-primary active">
									<input type="radio" class="import_bc_data" name="tipo" value="tickets" checked="checked"> Tickets
								</label>	
							</div>
							<label class="h4 strong block">Filtro</label>
							<div class="btn-group">
								<div class="checkbox">
									<label>
										<input type="checkbox" class="import_bc_data" name="filtro" value="All"> Todo
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" class="import_bc_data" name="filtro" value="Won" > Ganados
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" class="import_bc_data" name="filtro" value="Paid" > Pagados
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" class="import_bc_data" name="filtro" value="Pending"> Pendientes
									</label>
								</div>	
							</div>
							<div class="fecha_rango">
							<label class="h4 strong block">Rango de fecha</label>
								<div class="panel-body">
									<div class="col-xs-6">
										<p class="text-center"><strong>Inicio</strong></p>
										<div class="form-group form-group-inicio_fecha">
											<label class="col-xs-4 control-label" for="input_text-inicio_fecha">Fecha</label>
											<div class="input-group col-xs-8">
												<input 
									    			type="hidden" 
									    			class="import_bc_data generar_data"
									    			data-col="inicio_fecha"
													name="inicio_fecha" 
													value="2017-05-01<?php //echo date("Y-m-d", strtotime("-1 week"));?>" 
									    			data-real-date="input_text-inicio_fecha">
												<input 
													type="text" 
													class="form-control recaudacion_datepicker" 
													id="input_text-inicio_fecha" 
													value="01-05-2017<?php //echo date("d-m-Y", strtotime("-1 week"));?>" 
													readonly="readonly" 										 
													>
												<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-inicio_fecha"></label>
											</div>
										</div>
									</div>
									<div class="col-xs-6">
										<p class="text-center"><strong>Fin</strong></p>
										<div class="form-group form-group-fin_fecha">
											<label class="col-xs-4 control-label" for="input_text-fin_fecha">Fecha</label>
											<div class="input-group col-xs-8">
												<input 
									    			type="hidden" 
									    			class="import_bc_data generar_data"
									    			data-col="fin_fecha"
													name="fin_fecha" 
													value="2017-05-01<?php //echo date("Y-m-d",strtotime("today"));?>" 
									    			data-real-date="input_text-fin_fecha">
												<input 
													type="text" 
													class="form-control recaudacion_datepicker" 
													id="input_text-fin_fecha" 
													value="01-05-2017<?php //echo date("d-m-Y",strtotime("today"));?>" 
													readonly="readonly" 										 
													>
												<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fin_fecha"></label>
											</div>
										</div>
									</div>
								</div>
							</div>

						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success recaudacion_import_from_bc_submit_btn" ><span class="glyphicon glyphicon-upload"></span> Importar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<?php

		$procesos = array();
			$procesos_command = "SELECT tp.id
										,tp.time_init
										,tp.fecha_inicio
										,tp.fecha_fin
										,tp.file_registros
										,tp.repositorios_insertados
										,tp.repositorios_updateados
										,tp.repositorios_nothing
										,tp.cabeceras_insertadas
										,tp.cabeceras_updateadas
										,tp.cabeceras_nothing
										,tp.detalles_insertados
										,tp.detalles_updateados
										,tp.detalles_nothing
										,tp.ids_no_procesados
										,tp.tipo
										,s.nombre AS servicio
										,a.archivo
										,u.usuario
								FROM tbl_transacciones_procesos tp 
								LEFT JOIN tbl_servicios s ON (s.id = tp.servicio_id)
								LEFT JOIN tbl_archivos a ON (a.id = tp.archivo_id)
								LEFT JOIN tbl_usuarios u ON (u.id = tp.usuario_id)
								WHERE tp.estado = '1'
								AND tp.tipo = '4'
								ORDER BY tp.id DESC";
			$procesos_query = $mysqli->query($procesos_command);
			while($pro=$procesos_query->fetch_assoc()){
				$procesos[]=$pro;
			}
		?>
		<table class="table table-bordered table-condensed table-liquidaciones">
			<thead>
				<tr>
					<th>
						<label>
							<input type="checkbox" class="re_process_checkbox" data-id="all">
						</label>
					</th>
					<th>id</th>
					<th>Usuario</th>
					<th>Fecha Proceso</th>
					<th>Servicio</th>
					<th>Tipo</th>
					<th>Archivo</th>
					<th>File Registros</th>
					<th>Repositorio Insert</th>
					<th>Repositorio Update</th>
					<th>Repositorio Nothing</th>
					<th>Detalle Insert</th>
					<th>Detalle Update</th>
					<th>Detalle Nothing</th>
					<th>No procesados</th>
					<th>Opciones</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$totales=array();
				$totales["file_registros"]=0;
				$totales["repositorios_insertados"]=0;
				$totales["repositorios_updateados"]=0;
				$totales["repositorios_nothing"]=0;
				$totales["detalles_insertados"]=0;
				$totales["detalles_updateados"]=0;
				$totales["detalles_nothing"]=0;
				$totales["ids_no_procesados"]=array();
				foreach ($procesos as $pro_k => $pro_v) {
					$totales["file_registros"]+=$pro_v["file_registros"];
					$totales["repositorios_insertados"]+=$pro_v["repositorios_insertados"];
					$totales["repositorios_updateados"]+=$pro_v["repositorios_updateados"];
					$totales["repositorios_nothing"]+=$pro_v["repositorios_nothing"];
					$totales["detalles_insertados"]+=$pro_v["detalles_insertados"];
					$totales["detalles_updateados"]+=$pro_v["detalles_updateados"];
					$totales["detalles_nothing"]+=$pro_v["detalles_nothing"];
					?>
					<tr class="checkbox_me">
						<td>
							<label>
								<input type="checkbox" class="re_process_checkbox" data-id="<?php echo $pro_v["id"];?>">
							</label>
						</td>
						<td><?php echo $pro_v["id"];?></td>
						<td><?php echo $pro_v["usuario"];?></td>
						<td><?php echo $pro_v["time_init"];?></td>
						<td><?php echo $pro_v["servicio"];?></td>
						<td><?php 
							if($pro_v["tipo"]==4){
								echo "Reporte diario";
							}else{
								echo ucfirst($pro_v["tipo"]);
							}
							?></td>
						<td><?php echo $pro_v["archivo"];?> <a href="/files_bucket/<?php echo $pro_v["archivo"];?>" target="_blank"><span class="glyphicon glyphicon-download"></span></a></td>
						<td><?php echo $pro_v["file_registros"];?></td>
						<td><?php echo $pro_v["repositorios_insertados"];?></td>
						<td><?php echo $pro_v["repositorios_updateados"];?></td>
						<td><?php echo $pro_v["repositorios_nothing"];?></td>
						<td><?php echo $pro_v["detalles_insertados"];?></td>
						<td><?php echo $pro_v["detalles_updateados"];?></td>
						<td><?php echo $pro_v["detalles_nothing"];?></td>
						<td>
							<?php
							//echo $pro_v["ids_no_procesados"]; 
							$ids_no_procesados_arr = json_decode($pro_v["ids_no_procesados"]);
							if($ids_no_procesados_arr){
								foreach ($ids_no_procesados_arr as $key => $value) {
									if(!in_array($value, $totales["ids_no_procesados"])){
										$totales["ids_no_procesados"][]=$value;
									}
									echo $value; echo "<br>";
								}
							}else{
								?>0<?php
							}
							//print_r($ids_no_procesados_arr);
							//echo substr($pro_v["ids_no_procesados"],1,-1); 
							?>
							<span class="swal_alert" data-text="">								
							<?php //echo count(json_decode($pro_v["ids_no_procesados"]));?></span></td>
						<td>
							<button class="btn btn-primary rec_reprocess_btn" data-id="<?php echo $pro_v["id"];?>">RE-Procesar</button>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
			<tfoot>
				<tr class="success">
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?php echo $totales["file_registros"];?></td>
					<td><?php echo $totales["repositorios_insertados"];?></td>
					<td><?php echo $totales["repositorios_updateados"];?></td>
					<td><?php echo $totales["repositorios_nothing"];?></td>
					<td><?php echo $totales["detalles_insertados"];?></td>
					<td><?php echo $totales["detalles_updateados"];?></td>
					<td><?php echo $totales["detalles_nothing"];?></td>
					<td><?php foreach ($totales["ids_no_procesados"] as $key => $value) { 
						echo $value; echo "<br>";
						}?></td>
					<td></td>
				</tr>
			</tfoot>
		</table>
		<?php
	}
	if($sub_sec_id=="procesos"){
		?>	
		<div class="modal" id="recaudacion_generar_liquidacion_modal" >
			<div class="modal-dialog">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close generar_cerrar_btn"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Generar Liquidaciones</h4>
					</div>
					<div class="modal-body">
						<label class="h4 strong block">Servicio/Proveedor</label>
						<div class="btn-group" data-toggle="buttons">
							<?php
							foreach ($servicios as $srv_k => $srv_v) {
								?>
								<label class="btn btn-success <?php if($srv_k==1){ ?>active<?php } ?>">
									<input type="radio" class="generar_data" name="gen_servicio" value="<?php echo $srv_v["id"];?>" required <?php if($srv_k==1){ ?> checked="checked"<?php } ?>> <?php echo $srv_v["nombre"];?>
								</label>
								<?php
							}
							?>
						</div>
						<div class="fecha_rango">
							<label class="h4 strong block">Rango</label>
							<div class="panel-body">
								<div class="col-xs-6">
									<p class="text-center"><strong>Inicio</strong></p>
									<div class="form-group form-group-inicio_fecha">
										<label class="col-xs-4 control-label" for="input_text-inicio_fecha">Fecha</label>
										<div class="input-group col-xs-8">
											<input 
								    			type="hidden" 
								    			class="input_text generar_data"
								    			data-col="inicio_fecha"
												name="inicio_fecha" 
												value="<?php echo date("Y-m-d", strtotime("last week last tuesday "));?>" 
								    			data-real-date="input_text-inicio_fecha">
											<input 
												type="text" 
												class="form-control gen_liq_datepicker" 
												id="input_text-inicio_fecha" 
												value="<?php echo date("d-m-Y", strtotime("last week last tuesday "));?>" 
												readonly="readonly" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-inicio_fecha"></label>
										</div>
									</div>
								</div>
								<div class="col-xs-6">
									<p class="text-center"><strong>Fin</strong></p>
									<div class="form-group form-group-fin_fecha">
										<label class="col-xs-4 control-label" for="input_text-fin_fecha">Fecha</label>
										<div class="input-group col-xs-8">
											<input 
								    			type="hidden" 
								    			class="input_text generar_data"
								    			data-col="fin_fecha"
												name="fin_fecha" 
												value="<?php echo date("Y-m-d",strtotime("last monday"));?>" 
								    			data-real-date="input_text-fin_fecha">
											<input 
												type="text" 
												class="form-control gen_liq_datepicker" 
												id="input_text-fin_fecha" 
												value="<?php echo date("d-m-Y",strtotime("last monday"));?>" 
												readonly="readonly" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fin_fecha"></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success generar_btn" >Generar</button>
						<button class="btn btn-default generar_cerrar_btn">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<?php
		$procesos = array();
			$procesos_command = "SELECT tp.at_unique_id
										,tp.fecha
										,tp.fecha_inicio
										,tp.fecha_fin
										,tp.estado
										,tp.servicio_id
										,s.nombre AS servicio
										,u.usuario
								FROM tbl_transacciones_procesos tp 
								LEFT JOIN tbl_servicios s ON (s.id = tp.servicio_id)
								LEFT JOIN tbl_usuarios u ON (u.id = tp.usuario_id)
								WHERE tp.tipo = 'liquidacion'
								AND tp.estado != '5'
								ORDER BY tp.fecha DESC";
			$procesos_query = $mysqli->query($procesos_command);
			while($pro=$procesos_query->fetch_assoc()){
				$procesos[]=$pro;
			}
		?>
		<table class="table table-bordered table-condensed table-liquidaciones">
			<thead>
				<tr>
					<th class="">#</th>
					<th class="col-xs-2">Proceso ID</th>
					<th class="">Fecha Proceso</th>
					<th class="col-xs-1">Servicio</th>
					<th class="">Fecha Inicio</th>
					<th class="">Fecha Fin</th>
					<th class="col-xs-1">Usuario</th>
					<th class="col-xs-1">Estado</th>
					<th class="col-xs-2 text-right">Opciones</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($procesos as $pro_k => $pro_v) {
					?>
					<tr class="pro_bg_<?php echo $pro_v["servicio_id"];?>">
						<td><?php echo $pro_k+1;?></td>
						<td><?php echo $pro_v["at_unique_id"];?></td>
						<td><?php echo $pro_v["fecha"];?></td>
						<td><?php echo $pro_v["servicio"];?></td>
						<td><?php echo strstr($pro_v["fecha_inicio"]," ",true);?></td>
						<td><?php echo strstr($pro_v["fecha_fin"]," ",true);?></td>
						<td><?php echo $pro_v["usuario"];?></td>
						<td><?php
							if($pro_v["estado"]==0){
								?><span class="label label-warning"><?php echo "Abierto";?></span><?php
							}elseif($pro_v["estado"]==1){
								?><span class="label label-success"><?php echo "Cerrado";?></span><?php
							}elseif($pro_v["estado"]==2){
								?><span class="label label-danger"><?php echo "Reabierto";?></span><?php
							}elseif($pro_v["estado"]==5){
								?><span class="label label-dark"><?php echo "Eliminado";?></span><?php
							}							
							?></td>
						<td class="text-right">
							<?php
							if($pro_v["estado"]==0){
								?>
								<a class="btn btn-xs btn-rounded btn-default" target="_blank" href="/?sec_id=recaudacion&amp;sub_sec_id=liquidaciones&amp;proceso_unique_id=<?php echo $pro_v["at_unique_id"];?>">Ver</a>
								<button class="btn btn-xs btn-rounded btn-success liq_pro_btn" data-opt="cerrar" data-id="<?php echo $pro_v["at_unique_id"];?>">Cerrar</button>
								<button class="btn btn-xs btn-rounded btn-danger liq_pro_btn" data-opt="eliminar" data-id="<?php echo $pro_v["at_unique_id"];?>">Eliminar</button>
								<?php
							}elseif($pro_v["estado"]==1){
								?>
								<a class="btn btn-xs btn-rounded btn-default" target="_blank" href="/?sec_id=recaudacion&amp;sub_sec_id=liquidaciones&amp;proceso_unique_id=<?php echo $pro_v["at_unique_id"];?>">Ver</a>
								<button class="btn btn-xs btn-rounded btn-warning liq_pro_btn" data-opt="abrir" data-id="<?php echo $pro_v["at_unique_id"];?>">Abrir</button>
								<?php
							}elseif($pro_v["estado"]==2){
								?>
								<a class="btn btn-xs btn-rounded btn-default" target="_blank" href="/?sec_id=recaudacion&amp;sub_sec_id=liquidaciones&amp;proceso_unique_id=<?php echo $pro_v["at_unique_id"];?>">Ver</a>
								<button class="btn btn-xs btn-rounded btn-success liq_pro_btn" data-opt="cerrar" data-id="<?php echo $pro_v["at_unique_id"];?>">Cerrar</button>
								<button class="btn btn-xs btn-rounded btn-danger liq_pro_btn" data-opt="eliminar" data-id="<?php echo $pro_v["at_unique_id"];?>">Eliminar</button>
								<?php
							}elseif($pro_v["estado"]==5){
								?>
								<a class="btn btn-xs btn-rounded btn-default" target="_blank" href="/?sec_id=recaudacion&amp;sub_sec_id=liquidaciones&amp;proceso_unique_id=<?php echo $pro_v["at_unique_id"];?>">Ver</a>
								<button class="btn btn-xs btn-rounded btn-warning liq_pro_btn" data-opt="abrir" data-id="<?php echo $pro_v["at_unique_id"];?>">Abrir</button>
								<?php
							}							
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
	if($sub_sec_id=="pagos_manuales"){
		$ticket_info_arr = array();
			$ticket_info_arr["bet_id"]="Bet ID";
			$ticket_info_arr["bet_number"]="Bet Number";
			$ticket_info_arr["player_id"]="Player ID";
			$ticket_info_arr["currency"]="Moneda";
			$ticket_info_arr["created"]="Fecha de Creacion";
			$ticket_info_arr["local_id"]="ID Local de Origen";
			$ticket_info_arr["local"]="Local de Origen";
			$ticket_info_arr["apostado"]="Apostado";
			$ticket_info_arr["canal_de_venta"]="Canal de venta";	
			$ticket_info_arr["medio_nombre"]="Medio Nombre";			
			$ticket_info_arr["stake"]="Apostado";
			$ticket_info_arr["odds"]="Cuota";
			$ticket_info_arr["winnings"]="Ganado";
			$ticket_info_arr["type"]="Tipo";
			$ticket_info_arr["cashdesk"]="Cashdesk";
			$ticket_info_arr["state"]="Estado";
			$ticket_info_arr["_paiddate_"]="Fecha de Pago";
			$ticket_info_arr["paid_cash_desk_name"]="Lugar de Pago";
			$ticket_info_arr["betshop_id"]="Betshop ID";
			$ticket_info_arr["cashdesk_id"]="Cashdesk ID";
		$ticket_new_arr = array();
			//$ticket_new_arr["bet_id"]="Bet ID";
			//$ticket_new_arr["bet_number"]="Bet Number";
			//$ticket_new_arr["player_id"]="Player ID";
			//$ticket_new_arr["currency"]="Moneda";
			//$ticket_new_arr["created"]="Fecha de Creacion";
			//$ticket_new_arr["local"]="Tienda de Origen";
			//$ticket_new_arr["apostado"]="Apostado";
			//$ticket_new_arr["canal_de_venta"]="Canal de venta";			
			$ticket_new_arr["apostado"]="Apostado";
			//$ticket_new_arr["odds"]="Cuota";
			$ticket_new_arr["ganado"]="Ganado/Pagado";
			//$ticket_new_arr["type"]="Tipo";
			$ticket_new_arr["paid_local_id"]="ID Local de pago";
			//$ticket_new_arr["state"]="Estado";
			//$ticket_new_arr["paid_day"]="Fecha de Pago";
			//$ticket_new_arr["paid_cash_desk_name"]="Lugar de Pago";
			//$ticket_new_arr["paid_canal_de_venta_id"]="Canal de venta de Pago";
			//$ticket_new_arr["betshop_id"]="Betshop ID";
			//$ticket_new_arr["cashdesk_id"]="Cashdesk ID";
		?>
		<div class="modal" id="recaudacion_pago_manual_view_ticket_modal" data-backdrop="static" >
			<div class="modal-dialog modal-lg">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close pago_manual_view_ticket_cerrar_btn"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Pago Manual</h4>
					</div>
					<div class="modal-body">
						<div class="form-inline">
							<div class="form-group">
								<label for="input_bet_id">Bet ID</label>
								<input type="text" class="form-control get_ticket_filtro" id="input_bet_id" name="r.bet_id" placeholder="000000000" value="" autocomplete="autocomplete">
							</div>
							<div class="form-group">
								<label for="select_servicio_id">Proveedor</label>
								<select class="form-control get_ticket_filtro" name="r.servicio_id" id="select_servicio_id">
									<?php
									foreach ($servicios as $srv_k => $srv_v) {
										if($srv_k==1){
											?>
											<option value="<?php echo $srv_k;?>"><?php echo $srv_v["nombre"];?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<button class="btn btn-default get_ticket_btn"><span class="glyphicon glyphicon-search"></span> Buscar</button>
						</div>
						<hr>
						<div class="row ticket_data hidden">
							<div class="col-xs-4">
								<div class="panel panel_ticket_info">
									<div class="panel-heading">
										<div class="panel-title">Ticket Info</div>
									</div>
									<div class="panel-body">
										<div class="row">
											<table class="table table-condensed">
												<tbody>
													<?php
													foreach ($ticket_info_arr as $ti_k => $ti_v) {
														?>
														<tr>
															<td class="strong"><?php echo $ti_v;?>:</td>
															<td class="ti_val ti_<?php echo $ti_k;?>"><?php echo $ti_k;?></td>
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
							<div class="col-xs-8">
								<div class="panel panel_ticket_modificaciones">
									<div class="panel-heading">
										<div class="panel-title">Modificaciones</div>
										<div class="panel-controls">
											<ul class="panel-buttons">
												<li class="dropdown">
													<a href="#" class="btn-panel-control dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<span class="glyphicon glyphicon-option-vertical"></span>
														</a>
													<ul class="dropdown-menu dropdown-menu-right">
														<li class="add_modificacion_btn"><a href="#"><i class="icon fa fa-plus-square"></i> Agregar Modificacion</a></li>
													</ul>
												</li>
											</ul>
										</div>
									</div>
									<div class="panel-body">
										<div class="row">
											<div class="sin_modificaciones">Sin Modificaciones</div>
											<table class="table table-bordered tabla_modificaciones hidden">
												<thead>
													<tr>
														<th class="col-xs-2">Fecha</th>
														<th class="col-xs-2">Apostado</th>
														<th class="col-xs-2">Ganado</th>
														<th class="col-xs-4">Descripcion</th>
														<!-- <th class="col-xs-2">Opciones</th> -->
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
					<div class="modal-footer">
						<button class="btn btn-default pago_manual_view_ticket_cerrar_btn">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal" id="recaudacion_add_pago_manual_modal" data-backdrop="static" >
			<div class="modal-dialog modal-md">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close pago_manual_add_cerrar_btn"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Agregar Pago Manual</h4>
					</div>
					<div class="modal-body">
						<input type="hidden" class="copy_hoy">
						<input type="hidden" name="ref_bet_id" data-col="ref_bet_id" id="ref_bet_id" class="new_ticket_input">
						<input type="hidden" name="ref_servicio_id" data-col="ref_servicio_id" id="ref_servicio_id" class="new_ticket_input">
						<div class="form-horizontal">
							<div class="form-group">
								<label for="new_created" class="col-xs-4 control-label">Fecha de Creacion:</label>
								<div class="col-xs-8">
									<input 
										type="hidden"
										id="agregar_pago_manual_created" 
										class="input_text agregar_pago_manual_created new_ticket_input"
										data-col="created"
										name="created" 
										value="<?php echo date("Y-m-d");?>" 
										data-real-date="input_text-agregar_pago_manual_created">
									<input 
										type="text" 
										class="form-control agregar_pago_manual_datepicker" 
										id="input_text-agregar_pago_manual_created" 
										value="<?php echo date("d-m-Y");?>" 
										readonly="readonly" 										 
										>
									<!-- <input type="text" data-col="created" class="form-control new_ticket_input" id="new_created" name="new_created" value="<?php echo date("Y-m-d H:i:s");?>" placeHolder="YYYY/MM/DD HH:MM:SS"> -->
								</div>
							</div>
							<div class="form-group">
								<label for="new_paid_day" class="col-xs-4 control-label">Fecha de Pago:</label>
								<div class="col-xs-8">
									<input 
										type="hidden"
										id="agregar_pago_manual_paid_day" 
										class="input_text agregar_pago_manual_paid_day new_ticket_input"
										data-col="paid_day"
										name="paid_day" 
										value="<?php echo date("Y-m-d");?>" 
										data-real-date="input_text-agregar_pago_manual_paid_day">
									<input 
										type="text" 
										class="form-control agregar_pago_manual_datepicker" 
										id="input_text-agregar_pago_manual_paid_day" 
										value="<?php echo date("d-m-Y");?>" 
										readonly="readonly" 										 
										>
									<!-- <input type="text" data-col="paid_day" class="form-control new_ticket_input" id="new_paid_day" name="paid_day" value="<?php echo date("Y-m-d H:i:s");?>" placeHolder="YYYY/MM/DD HH:MM:SS"> -->
								</div>
							</div>
							
							<div class="form-group">
								<label for="new_apostado" class="col-xs-4 control-label">Apostado:</label>
								<div class="col-xs-8">
									<input type="text" class="form-control new_ticket_input" id="new_apostado" data-col="apostado" name="apostado" value="0">
								</div>
							</div>
							<div class="form-group">
								<label for="new_ganado" class="col-xs-4 control-label">Ganado/Pagado:</label>
								<div class="col-xs-8">
									<input type="text" class="form-control new_ticket_input" id="new_ganado" data-col="ganado" name="ganado" value="0">
								</div>
							</div>
							<div class="form-group">
								<label for="new_paid_local_id" class="col-xs-4 control-label">ID Local de pago:</label>
								<div class="col-xs-8">
									<select 
										class="selectpicker new_ticket_input selectpicker_new_paid_local_id" 
										id="new_paid_local_id" 
										data-live-search="true" 
										data-col="paid_local_id"
										name="paid_local_id" 
										data-width="100%">
										<?php
										$locales_query=$mysqli->query("SELECT id,nombre
																	FROM tbl_locales
																	WHERE estado = '1'
																	");
										while ($l=$locales_query->fetch_assoc()) {
											?>
											<option <?php if($l["id"] == "104"){ ?>selected="selected"<?php } ?> value="<?php echo $l["id"];?>"><?php echo $l["id"];?> - <?php echo $l["nombre"];?></option>
											<?php
										}
										?>
									</select>
									<!-- <input type="text" class="form-control new_ticket_input" id="new_paid_local_id" data-col="paid_local_id" value=""> -->
								</div>
							</div>
							<div class="form-group">
								<label for="new_paid_canal_de_venta_id" class="col-xs-4 control-label">Medio de pago:</label>
								<div class="col-xs-8">
									<select 
										class="selectpicker new_ticket_input selectpicker_new_paid_canal_de_venta_id" 
										id="new_paid_canal_de_venta_id" 
										data-live-search="true" 
										data-col="paid_canal_de_venta_id"
										name="paid_canal_de_venta_id" 
										data-width="100%">
										<?php
										$cdv_query = $mysqli->query("SELECT id, codigo FROM tbl_canales_venta WHERE estado = '1' ORDER BY id");
										while($cdv=$cdv_query->fetch_assoc()){
											?>
											<option <?php if($cdv["id"] == "16"){ ?>selected="selected"<?php } ?>  value="<?php echo $cdv["id"];?>"><?php echo $cdv["id"];?> - <?php echo $cdv["codigo"];?></option>
											<?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-xs-4 control-label">Descripcion</label>
								<div class="col-xs-8">
									<textarea class="form-control new_ticket_input" rows="3" data-col="descripcion" name="descripcion">321</textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success pago_manual_save_btn" data-loading-text="Agregando...">Agregar</button>
						<button class="btn btn-default pago_manual_add_cerrar_btn">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<?php
		$procesos = array();
			$procesos_command = "SELECT tp.at_unique_id
										,CAST(tp.fecha AS DATE) AS fecha_proceso
										,tp.fecha_inicio
										,tp.fecha_fin
										,tp.estado
										,tp.bet_id
										,tp.bet_id_m
										,s.nombre AS servicio_nombre
										,u.usuario
										,l.nombre AS local_pago
										,d.apostado
										,d.ganado
										,CAST(d.paid_day AS DATE) AS fecha_pago
										,CAST(do.created AS DATE) AS fecha_origen
										,l.nombre AS local_origen
										,co.codigo AS cdv_origen
										,c.codigo AS cdv_pago
								FROM tbl_transacciones_procesos tp 
								LEFT JOIN tbl_servicios s ON (s.id = tp.servicio_id)
								LEFT JOIN tbl_usuarios u ON (u.id = tp.usuario_id)
								LEFT JOIN tbl_transacciones_detalle d ON (d.ticket_id = tp.bet_id_m)
								LEFT JOIN tbl_transacciones_detalle do ON (do.ticket_id = tp.bet_id)
								LEFT JOIN tbl_locales l ON (l.id = d.local_id)
								LEFT JOIN tbl_locales lo ON (lo.id = do.local_id)
								LEFT JOIN tbl_canales_venta c ON (c.id = d.canal_de_venta_id)
								LEFT JOIN tbl_canales_venta co ON (co.id = do.canal_de_venta_id)
								WHERE tp.tipo = 'pago_manual'
								AND tp.estado = '1'
								ORDER BY tp.fecha DESC";
			$procesos_query = $mysqli->query($procesos_command);
			//print_r($mysqli->error);
			//exit();
			while($pro=$procesos_query->fetch_assoc()){
				$procesos[]=$pro;
			}
			//print_r($mysqli->error);
		?>	
		<table class="table table-bordered table-condensed table-liquidaciones">
			<thead>
				<tr>
					<th>#</th>
					<th>Fecha Proceso</th>
					<th>Proveedor</th>
					<th>Fecha Origen</th>
					<th>Local Origen</th>
					<th>Canal de venta</th>
					<th>Ticket ID</th>
					<th>Fecha de Pago</th>
					<th>Local de Pago</th>
					<th>Canal de venta de Pago</th>
					<th>Apostado</th>
					<th>Pagado</th>
					<th>Usuario</th>
					<th>Opciones</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($procesos as $pro_k => $pro_v) {
					?>
					<tr class="">
						<td><?php echo $pro_k+1;?></td>
						<td><?php echo $pro_v["fecha_proceso"];?></td>
						<td><?php echo $pro_v["servicio_nombre"];?></td>
						<td><?php echo $pro_v["fecha_origen"];?></td>
						<td><?php echo $pro_v["local_origen"];?></td>
						<td><?php echo $pro_v["cdv_origen"];?></td>
						<td><?php echo $pro_v["bet_id"];?></td>
						<td><?php echo $pro_v["fecha_pago"];?></td>
						<td><?php echo $pro_v["local_pago"];?></td>
						<td><?php echo $pro_v["cdv_pago"];?></td>
						<td><?php echo $pro_v["apostado"];?></td>
						<td><?php echo $pro_v["ganado"];?></td>
						<td><?php echo $pro_v["usuario"];?></td>
						<td>
							<button class="btn btn-xs btn-rounded btn-primary pago_manual_ver" data-id="<?php echo $pro_v["bet_id"];?>">Ver</button>
							<button class="btn btn-xs btn-rounded btn-danger pago_manual_del" data-id="<?php echo $pro_v["at_unique_id"];?>">Chau</button>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>	
		<?php
	}
}
?>