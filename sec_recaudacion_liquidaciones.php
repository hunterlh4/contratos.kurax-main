 
<?php

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
						$proceso = $mysqli->query("
							SELECT pro.at_unique_id, pro.fecha, pro.fecha_inicio, pro.fecha_fin, pro.servicio_id, pro.estado, u.usuario
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


					<div class="row container_filtros_recaudacion">

						<div class="col-lg-2 col-sm-6 col-xs-12">
							<p class="text-center iniciocustom">Fecha Inicio</p>
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
						<div class="col-lg-2 col-sm-6 col-xs-12">
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
							<select id="local_recaudacion" class="local local_recaudacion" name="local_recaudacion" multiple="true" style="width:100%;">
							    <option value="all">Todos</option>
							</select>
						</div>
						<div class="col-lg-2 col-xs-12">
							<p class="text-center canalventascustom">Canal de venta</p>
							<select id="canalventarecaudacion" class="canalventarecaudacion canal_venta_recaudacion" multiple="true" style="width:100%;">
							    <option value="all">Todos</option>
							</select>
						</div>
						<div class="col-lg-2 col-xs-12">
							<p class="text-center redcustom">Red</p>
							<select id="red_recaudacion" class="red red_recaudacion" name="red_recaudacion" multiple="true" style="width:100%;">
							    <option value="0">Sin Red</option>
							</select>
						</div>
						<div class="col-lg-2 col-xs-12">
							<p class="text-center zonacustom">Zonas Comerciales</p>
							<select id="zona_recaudacion" class="zona zona_recaudacion" name="zona_recaudacion" multiple="true" style="width:100%;">
							</select>
						</div>
						<div class="col-lg-12 col-xs-12 text-right" style="margin-bottom: 10px">
							<button type="button" name="liquidaciones_filtro" value="1" class="filtarrecaudacion btnfiltarrecaudacion btn btn-primary" data-button="request" data-toggle="tooltip" data-placement="top" title="Filtrar recaudación"><span class="glyphicon glyphicon-search"></span> Consultar</button>								
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
			                <th class="colunmas_dias_modelo_dos_head_th">DIAS</th>
			                <th class="colunmas_canal_de_venta_modelo_dos_head_th">CANAL DE VENTA</th>
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

			                <th class="columnas_propietario_modelo_tres_head_th">
				                    <div class="contenedornombrecolumna" >PROPIETARIO</div>
			                </th>
			                <th class="columnas_departamento_modelo_tres_head_th">DEPARTAMENTO</th>
			                <th class="columnas_provincia_modelo_tres_head_th">PROVINCIA</th>
			                <th class="columnas_distrito_modelo_tres_head_th">DISTRITO</th>

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
				<div class="container-fluid container_datatable_recaudacion container_datatable_recaudacion_modelo_cuatro">
					<table id="tabla_sec_recaudacion" cellspacing="0" width="100%"   class="cell-border table-liquidaciones-modelo-4">
				        <thead style="background-color:#fff !important; color:#fff !important; border-bottom:1px solid #ddd !important;">
				            <tr>
								<th class="columna_local_id_head_th">
				                    <div class="contenedornombrecolumna" data-name="local_id">CECO</div>
				                </th>
				                <th class="columna_local_id_head_th">
				                    <div class="contenedornombrecolumna" data-name="local_id">LOCAL ID</div>
				                </th>			            
				                <th class="columna_nombre_local_head_th">
				                    <div class="contenedornombrecolumna" data-name="local_nombre">NOMBRE LOCAL</div>
				                </th>

				                <th class="columna_head_th columna_propietario">
				                    <div class="contenedornombrecolumna" data-name="propietario">PROPIETARIO</div>
				                </th>
				                <th class="columna_head_th columna_departamento">
				                    <div class="contenedornombrecolumna" data-name="departamento">DEPARTAMENTO</div>
				                </th>
				                <th class="columna_head_th columna_provincia">
				                    <div class="contenedornombrecolumna" data-name="provincia">PROVINCIA</div>
				                </th>
				                <th class="columna_head_th columna_distrito">
				                    <div class="contenedornombrecolumna" data-name="distrito">DISTRITO</div>
				                </th>

								<th class="columna_dias_procesados_head_th">
									<div class="contenedornombrecolumna" data-name="dias">DIAS</div>
								</th>
				                <th class="canal_de_venta_modelo_cuatro_head_th">
				                    <div class="contenedornombrecolumna" data-name="canales_de_venta">CANAL DE VENTA</div>
				                </th>
								<th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="num_tickets">&nbsp;N. TICKETS</div>
				                </th>
				                <th class="columnasnumeros_head_th ">
				                    <div class="contenedornombrecolumna" data-name="total_depositado">TOTAL DEPOSITADO</div>
				                </th>



				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_anulado_retirado" >TOTAL ANULADO /<br> RETIRADO</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_apostado_modelo_cuatro_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_apostado" >TOTAL APOSTADO</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_ganado_modelo_cuatro_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_ganado">TOTAL GANADO</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_pagado_modelo_cuatro_head_th"><!-- TOTAL PAGADO -->
				                    <div class="contenedornombrecolumna" data-name="total_pagado">TOTAL PREMIOS PAGADOS</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_produccion_modelo_cuatro_head_th"><!-- TOTAL PRODUCCION -->
				                    <div class="contenedornombrecolumna" data-name="total_produccion">RESULTADO EFECTIVO</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_produccion_modelo_cuatro_head_th"><!-- RESULTADO NEGOCIO -->
				                    <div class="contenedornombrecolumna" data-name="resultado_negocio">RESULTADO DE NEGOCIO</div>
				                </th>



				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_depositado_web">DEPOSITADO<br> WEB</div>
				                </th>
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_retirado_web">RETIRADO<br> WEB</div>
				                </th>
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_caja_web">TOTAL CAJA<br> WEB</div>
				                </th>
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="porcentaje_cliente">PORCENTAJE CLIENTE (%)</div>
				                </th>			                
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_cliente">PARTICIPACIÓN CLIENTE</div>
				                </th>



				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="porcentaje_freegames">PORCENTAJE FREEGAMES (%)</div>
				                </th>			                
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_freegames">PARTICIPACIÓN FREEGAMES</div>
				                </th>
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumnaextend" data-name="pagado_en_otra_tienda">TK PAGADO EN <br>OTRO PUNTO</div>
				                </th>
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_pagos_fisicos">TK PROPIOS<br>PAGADOS EN SU PUNTO</div>
				                </th>				                
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumnaextend" data-name="pagado_de_otra_tienda">TK PAGADO DE <br>OTRO PUNTO</div>
				                </th>



				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="total_pagos_fisicos">TK PAGADOS EN <br>SU PUNTO</div>
				                </th>
				                <th class="columnasnumeros_head_th">
				                    <div class="contenedornombrecolumna" data-name="caja_fisico">CAJA FISICO</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_cd_balance">
				                    <div class="contenedornombrecolumna" data-name="cashdesk_balance">CD BALANCE</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_test_balance">
				                    <div class="contenedornombrecolumna" data-name="test_balance">TEST BALANCE</div>
				                </th>
				                <th class="columnasnumeros_head_th columna_test_diff">
				                    <div class="contenedornombrecolumna" data-name="test_diff">TEST DIFF</div>
				                </th>	

				                

				                <th class="columnasnumeros_head_th columna_canal_de_venta_id">
				                    <div class="contenedornombrecolumna" data-name="canal_de_venta_id">CANAL ID</div>
				                </th>			                
		            		</tr>
				        </thead> 
				        <tfoot>
				            <tr>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>			            
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>

				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>

				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>

				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>

				                <th class="liquidaciones_footer_modelo_cuatro"></th>				                			                
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>			                
				                <th class="liquidaciones_footer_modelo_cuatro"></th>			                
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>

				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>
				                <th class="liquidaciones_footer_modelo_cuatro"></th>

				                
				            </tr>
				        </tfoot>       
						<tbody>					
						</tbody>
					</table>
				</div>
				<style>
						.container_datatable_recaudacion .DTFC_LeftHeadWrapper{
						background-color: #fff !important;
						}
						.container_datatable_recaudacion .dataTables_scrollHead{
							background-color: #fff !important;
						}
						#table_tickets_comision_cuota_filter{
							float:right !important;
							margin-bottom: 5px !important;
							font-size: 11px !important;   
						}
						#table_tickets_comision_cuota_length {
							margin-bottom: 5px !important;
							font-size: 11px !important;
							float: left;
							margin-right: 25px;
						}
						#table_tickets_comision_cuota_wrapper .btn-group{
							margin-bottom: -16px !important;
							font-size: 11px !important; 
						}
						#table_tickets_comision_cuota_paginate{
							float:right ;
							font-size: 11px !important; 
						}
						#table_tickets_comision_cuota_info{
							float:left ;
							font-size: 11px !important; 
						}
						.body_modal_tickets_comision_cuota{
							margin-bottom: 45px !important;
							font-size: 11px !important; 
						}
						.body_modal_tickets_comision_cuota .modal-body{
							margin-right: 0px !important;
							margin-left:0px !important;
						}
				</style>
				<!-- Modal detalles liquidaciones -->
				<div class="modal fade bs-example-modal-lg  modal-fullscreen" id="modal_detalle_liquidaciones" role="dialog" aria-labelledby="myLargeModalLabel" data-backdrop="static" tabindex='-1' >

							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title">CashDesk: <span class="cashdesk_nombre_tickets_comision_cuota"></span></h4>
							</div>
							<div class="modal-body body_modal_tickets_comision_cuota">
								<input type="hidden" class="export_tickets_comision_cuota" value="export_<?php echo date("c");?>">
								<table id='table_tickets_comision_cuota' class='table_tickets_comision_cuota' cellspacing="0" width="100%">
							        <thead>
							            <tr>
											<th class="text-center ttcc_id_apuestas_th" >ID Apuestas</th>
											<th class="text-center ttcc_bet_number_th" >Bet Number</th>
											<th class="text-center ttcc_moneda_th" >Moneda	</th>
											<th class="text-center ttcc_monto_th" >Monto</th>
											<!--<th class="text-center ttcc_monto_en_th" >Monto en</th>-->

											<th class="text-center ttcc_cuotas_th" >Cuotas</th>
											<th class="text-center ttcc_porcentaje_th" >%</th>
											<th class="text-center ttcc_importe_de_comision_th" >Importe de Comision</th>
											<th class="text-center ttcc_ganancias_th" >Ganancias</th>
											<!--<th class="text-center ttcc_monto_de_bono_th" >Monto de Bono</th>-->

											<!--<th class="text-center ttcc_monto_de_freebet_th" >Monto de Freebet</th>-->
											<!--<th class="text-center ttcc_ganancias_en_th" >Ganancias en</th>-->
											<th class="text-center ttcc_tipo_th" >Tipo</th>
											<!--<th class="text-center ttcc_cashdesk_th" >CashDesk</th>-->
											<!--<th class="text-center ttcc_cashdesk_info_th" >Cash Desk Info</th>-->

											<th class="text-center ttcc_estado_th" >Estado</th>
											<th class="text-center ttcc_creado_th" >Creado</th>
											<th class="text-center ttcc_fecha_de_apostado_th" >Fecha de Apostado</th>
											<th class="text-center ttcc_fecha_calc_th" >Fecha Calc</th>
											<th class="text-center ttcc_is_live_th" >Is Live</th>

											<th class="ttcc_paiddate_th" >PaidDate</th>
											<th class="ttcc_paidcashdeskname_th" >PaidCashDeskName</th>
										</tr>
							        <thead>
							        <tfoot>
							            <tr>
							            	<!--
							                <th class="ttcc_footer_th"></th>			            
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                -->

							                <!--<th class="ttcc_footer_th"></th>-->
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>

							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>

							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>
							                			                
							                <th class="ttcc_footer_th"></th>
							                <th class="ttcc_footer_th"></th>			                
							            </tr>
							        </tfoot>       
									<tbody>					
									</tbody>					        
								</table>

							</div>
							<div class="modal-footer">
								<!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
							</div>
				</div>
				<!-- Fin modal detalles liquidaciones -->

			<?php
		}
		if ($modelo==5) {
			?>
			<div class="container-fluid container_datatable_recaudacion">
				<table id="tabla_sec_recaudacion" cellspacing="0" width="90%"   class="cell-border table-liquidaciones-modelo-4">
			        
			        <thead>
			            <tr>
			                <th class="columna_nombre_local_head_th">
			                    <div class="contenedornombrecolumna">PTO VENTA</div>
			                </th>
			                <th class="canal_de_venta_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">CANAL DE VENTA</div>
			                </th>
			                <th class="tipo_punto_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TIPO DE PUNTO</div>
			                </th>
			                <th class="qty_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">QTY</div>
			                </th>	
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">%</div>
			                </th>			                		                			                
			                <th class="columnasnumeros_head_th ">
			                    <div class="contenedornombrecolumna">TOTAL DEPOSITADO</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">ANULADO / RETIRADO</div>
			                </th>
			                <th class="columnasnumeros_head_th columna_apostado_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TOTAL APOSTADO</div>
			                </th>
			                <th class="columnasnumeros_head_th columna_tk_pagados_en_su_punto_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TK PAGADOS EN<br> SU PUNTO</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumnaextend">TK PAGADOS EN<br>OTRO PUNTO</div>
			                </th>	
			                <th class="columnasnumeros_head_th columna_premios_pagado_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">TOTAL PREMIOS<br> PAGADOS</div>
			                </th>		
			                <th class="columnasnumeros_head_th columna_resultado_negocio_modelo_cuatro_head_th">
			                    <div class="contenedornombrecolumna">RESULTADO DEL NEGOCIO</div>
			                </th>	
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">CAJA</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">DEPOSITADO<br> WEB</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumna">RETITADO<br> WEB</div>
			                </th>
			                <th class="columnasnumeros_head_th">
			                    <div class="contenedornombrecolumnaextend">TK PAGADO DE <br>OTRO PUNTO</div>
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
			            </tr>
			        </tfoot>       
					<tbody>					
					</tbody>
				</table>
			</div>	
			<?php		
		}
	}
?>