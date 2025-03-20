<?php
global $mysqli;
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];
if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	include("403.php");
	return false;
}
?>
<div class="content container-fluid cuadro_de_ventas_form_contrato contenedor_general_sec_recaudacion_filtros"> <div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Recaudación - Liquidaciones Agentes
				</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 ">
				<?php
				if($item_id){
				}else{
					?>
					<button style="display:none;"
						type="button" 
						class="btn btn-success table_to_xls_btn" 
						data-table="table-liquidaciones"
						><span class="glyphicon glyphicon-export"></span> Exportar Lista</button>
					
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php
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
?>
<?php
$modelo = 4;
	?>
	<input type="hidden" class="export_filename" value="export_<?php echo date("c");?>">
	<div class="container-fluid">
		<div class="row">
			<div class="container_filtros_recaudacion mt-4">
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2 mt-2">
					<!-- <p class="text-center iniciocustom">Fecha Inicio </p> -->
					<label for="input_text-liq_filtro_inicio_fecha">Fecha Inicio</label>
					<div class="form-group">
						<div class="input-group">
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
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2 mt-2">
						<!-- <p class="text-center fincustom">Fecha Fin</p> -->
						<label for="input_text-liq_filtro_fin_fecha">Fecha Fin</label>
						<div class="form-group form-group-fin_fecha">
							<div class="input-group">
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
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 mt-2">
					<!-- <p class="text-center localcustom">Local</p> -->
					<label for="local_recaudacion">Local</label>
					<select id="local_recaudacion" class="local local_recaudacion" name="local_recaudacion" multiple="true" style="width:100%;">
					    <option value="all">Todos</option>
					</select>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-2 mt-2">
					<!-- <p class="text-center canalventascustom">Canal de venta</p> -->
					<label for="canalventarecaudacion">Canal de venta</label>
					<select id="canalventarecaudacion" class="canalventarecaudacion canal_venta_recaudacion" multiple="true" style="width:100%;">
					    <option value="all">Todos</option>
					</select>
				</div>
				<div class="col-lg-3 col-xs-12 text-right" style="margin-bottom: 10px">
					<button type="button" name="liquidaciones_filtro" value="1" class="filtarrecaudacion btnfiltarrecaudacion btn btn-primary" data-button="request" data-toggle="tooltip" data-placement="top" title="Filtrar recaudación"><span class="glyphicon glyphicon-search"></span> Consultar</button>								
				</div>																								
			</div>
		</div>
	</div>
<input type="hidden" value="<?php echo $modelo; ?>" class="sec_recaudaciones_tipo_de_modelo"/>
<?php
	?>
<div class="container-fluid container_datatable_recaudacion container_datatable_recaudacion_modelo_cuatro mt-3">
	<table id="tabla_sec_recaudacion" cellspacing="0" width="100%"   class="cell-border table-liquidaciones-modelo-4">
        <thead style="background-color:#fff !important; color:#fff !important; border-bottom:1px solid #ddd !important;">
            <tr>
                <th class="columna_local_id_head_th">
                    <div class="contenedornombrecolumna" data-name="local_id">LOCAL ID</div>
                </th>			            
                <th class="columna_nombre_local_head_th">
                    <div class="contenedornombrecolumna" data-name="local_nombre">NOMBRE LOCAL</div>
                </th>
                <th class="columna_dias_procesados_head_th">
                    <div class="contenedornombrecolumna" data-name="dias">DIAS</div>
                </th>
				<th class="columna_zona_nombre_procesados_head_th">
                    <div class="contenedornombrecolumna" data-name="zona_nombre">ZONA</div>
                </th>
                <th class="canal_de_venta_modelo_cuatro_head_th">
                    <div class="contenedornombrecolumna" data-name="canales_de_venta">CANAL DE VENTA</div>
                </th>
                <th class="columnasnumeros_head_th columna_apostado_modelo_cuatro_head_th">
                    <div class="contenedornombrecolumna" data-name="total_apostado" >TOTAL APOSTADO</div>
                </th>
                <th class="columnasnumeros_head_th columna_ganado_modelo_cuatro_head_th">
                    <div class="contenedornombrecolumna" data-name="total_ganado">TOTAL GANADO</div>
                </th>
                <th class="columnasnumeros_head_th columna_pagado_modelo_cuatro_head_th"><!-- TOTAL PAGADO -->
                    <div class="contenedornombrecolumna" data-name="total_pagado">TOTAL PREMIOS<br> PAGADOS</div>
                </th>
                <th class="columnasnumeros_head_th columna_produccion_modelo_cuatro_head_th"><!-- TOTAL PRODUCCION -->
                    <div class="contenedornombrecolumna" data-name="total_produccion">RESULTADO EFECTIVO</div>
                </th>
				<th class="columnasnumeros_head_th"><!-- SALDO ARRASTRADO DEL ANTERIOR MES -->
                    <div class="contenedornombrecolumna" data-name="saldo_arrastrar_anterior_mes">SALDO ARRASTRADO DEL<br> ANTERIOR MES</div>
                </th>
                <th class="columnasnumeros_head_th columna_produccion_modelo_cuatro_head_th"><!-- RESULTADO NEGOCIO -->
                    <div class="contenedornombrecolumna" data-name="resultado_negocio">RESULTADO DEL NEGOCIO</div>
                </th>
				<th class="columnasnumeros_head_th"><!-- BASE IMPONIBLE - MNTTO (2%) -->
                    <div class="contenedornombrecolumna" data-name="base_imponible_mntto">BASE IMPONIBLE - MNTTO (2%)</div>
                </th>
                <th class="columnasnumeros_head_th"><!-- IMPUESTO (12%) -->
                    <div class="contenedornombrecolumna" data-name="impuesto">IMPUESTO (12%)</div>
                </th>
				<th class="columnasnumeros_head_th"><!-- SALDO POR ARRASTRAR SIGUIENTE MES -->
                    <div class="contenedornombrecolumna" data-name="saldo_arrastrar_siguiente_mes">SALDO POR ARRASTRAR<br> SIGUIENTE MES</div>
                </th>
                <th class="columnasnumeros_head_th"><!-- BASE DE CALCULO -->
                    <div class="contenedornombrecolumna" data-name="base_calculo">BASE DE CALCULO</div>
                </th>
                <th class="columnasnumeros_head_th">
                    <div class="contenedornombrecolumna" data-name="total_depositado_web">DEPOSITADO<br> WEB</div>
                </th>
				<th class="columnasnumeros_head_th">
                    <div class="contenedornombrecolumna" data-name="porcentaje_cliente">PORCENTAJE CLIENTE (%)</div>
                </th>		
                <th class="columnasnumeros_head_th">
                    <div class="contenedornombrecolumna" data-name="participacion_cliente">PARTICIPACIÓN CLIENTE</div>
                </th>
				<th class="columnasnumeros_head_th">
                    <div class="contenedornombrecolumna" data-name="porcentaje_freegames">PORCENTAJE FREEGAMES (%)</div>
                </th>	
                <th class="columnasnumeros_head_th">
                    <div class="contenedornombrecolumna" data-name="participacion_freegames">PARTICIPACIÓN FREEGAMES</div>
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
							<th class="text-center ttcc_tipo_th" >Tipo</th>
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
?>