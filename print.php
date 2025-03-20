<?php
	require_once("sys/global_config.php");
	require_once("sys/sys_cookies.php");
	require_once("sys/db_connect.php");
	require_once("sys/sys_login.php");
	//require_once("sys/build_html.php");
	//print_r($_SESSION);
?>
<!DOCTYPE html>
<html lang="en">
<?php
	include("page_header.php");
?>
	<link rel="stylesheet" href="css/print.css?<?php echo $css_cache;?>">

<body>
<?php
if($sec_id=="contratos"){
	if($item_id){
		$item=$mysqli->query("SELECT 
									con.id, con.estado, con.descripcion, con.fecha_registro, con.fecha_inicio_contrato, con.fecha_fin_contrato, con.tiempo_de_contrato, con.juegos_virtuales, con.apuestas_deportivas, con.terminales, con.recargas_web, con.amortizacion_semanal, con.incluye_igv, con.pagador, con.limite_monto, con.corte_no_pago, con.observa_vendedor
									,con_tipo.nombre AS contrato_tipo
									,cli.nombre, cli.razon_social, cli.tipo_cliente_id, cli.dni, cli.ruc, cli.telefono, cli.celular, cli.email
									,cli_tipo.nombre AS cliente_tipo
									,loc.tipo_id, loc.nombre AS nombre_de_local, loc.direccion AS local_direccion, loc.area AS local_area
									,loc_estado_legal.nombre AS local_estado_legal
									,loctipo.nombre AS tipo_de_local
									,canal.nombre AS canal_de_venta
									,liq_mod.nombre AS periodo_liquidacion
									,con_tipo_red.nombre AS tipo_red
									,fac_doc_tipo.nombre AS documento_tipo
									,mon.sigla AS moneda
									,lim_pagos.nombre AS limite_pagos
									,neg_tip.nombre AS devo_negativa
									,ubi.nombre AS ubi_departamento
										FROM tbl_contratos con
										LEFT JOIN tbl_contrato_tipos con_tipo ON (con_tipo.id = con.tipo_contrato_id)
										LEFT JOIN tbl_clientes cli ON (cli.id = con.cliente_id)
										LEFT JOIN tbl_cliente_tipos cli_tipo ON (cli_tipo.id = cli.tipo_cliente_id)
										LEFT JOIN tbl_locales loc ON (loc.id = con.local_id)
										LEFT JOIN tbl_local_estado_legal loc_estado_legal ON (loc_estado_legal.id = loc.estado_legal_id)
										LEFT JOIN tbl_local_tipo loctipo ON (loctipo.id = loc.tipo_id)
										LEFT JOIN tbl_canales_venta canal ON (canal.id = con.canal_de_venta_id)
										LEFT JOIN tbl_liquidacion_modalidad liq_mod ON (liq_mod.id = con.periodo_liquidacion_id)
										LEFT JOIN tbl_contrato_tipos_de_red con_tipo_red ON (con_tipo_red.id = con.red_tipo_id)
										LEFT JOIN tbl_facturacion_documento_tipos fac_doc_tipo ON (fac_doc_tipo.id = con.documento_tipo_id)
										LEFT JOIN tbl_moneda mon ON (mon.id = con.moneda_id)
										LEFT JOIN tbl_contrato_pago_premios_limite lim_pagos ON (lim_pagos.id = con.limite_pago_id)
										LEFT JOIN tbl_contrato_negativo_devolucion_tipos neg_tip ON (neg_tip.id = con.negativo_devolucion_tipo_id)
										LEFT JOIN tbl_ubigeo ubi ON (ubi.cod_depa = LEFT(loc.ubigeo_id,2))
										WHERE con.id = '".$item_id."'
										")->fetch_assoc();
		//print_r($item);
		$item["juegos_virtuales"] = ($item["juegos_virtuales"] ? "SI" : "NO");
		$item["apuestas_deportivas"] = ($item["apuestas_deportivas"] ? "SI" : "NO");
		$item["terminales"] = ($item["terminales"] ? "SI" : "NO");
		$item["recargas_web"] = ($item["recargas_web"] ? "SI" : "NO");
		?>
		<div class="w-550px">
			<div class="col-xs-12">
				<img class="col-xs-4" src="images/logoApuestaTotalRegistro.png">
				<div class="col-xs-8"><h1>Contrato</h1></div>
			</div>
			<div class="col-xs-12">
				Datos Personales
			</div>
			<div class="col-xs-12">
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["nombre"]="Nombre y apellido";
						$tr_arr["razon_social"]="Razón Social";
						$tr_arr["telefono"]="Telefono";
						$tr_arr["celular"]="Celular";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["dni"]="DNI";
						$tr_arr["ruc"]="RUC";
						$tr_arr["telefono"]="Correo";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
			</div>
			<div class="col-xs-12">
				Acerca del Local
			</div>
			<div class="col-xs-12">
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["tipo_de_local"]="Tipo de Local";
						$tr_arr["nombre_de_local"]="Nombre";
						$tr_arr["local_estado_legal"]="Estado Legal";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["ubi_departamento"]="Departamento";
						$tr_arr["local_direccion"]="Direccion";
						$tr_arr["local_area"]="Area";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
			</div>
			<div class="col-xs-12">
				Datos del Contrato
			</div>
			<div class="col-xs-12">
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["id"]="ID del contrato";
						$tr_arr["fecha_registro"]="Fecha de Registro";
						$tr_arr["canal_de_venta"]="Canal de Venta";
						$tr_arr["contrato_tipo"]="Tipo de Contrato";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["fecha_inicio_contrato"]="Fecha Inicio";
						$tr_arr["fecha_fin_contrato"]="Fecha Fin";
						$tr_arr["tiempo_de_contrato"]="Tiempo de Contrato";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
			</div>
			<div class="col-xs-12">
				Productos y Servicios
			</div>
			<div class="col-xs-12">
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["juegos_virtuales"]="Juegos Virtuales";
						$tr_arr["apuestas_deportivas"]="Apuestas Deportivas";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<?php
						$tr_arr = array();
						$tr_arr["terminales"]="Terminales";
						$tr_arr["recargas_web"]="Recargas Web";
						foreach ($tr_arr as $key => $value) {
							?><tr>
								<td class="strong"><?php echo $value;?>: </td>
								<td><?php echo $item[$key];?></td>
							</tr><?php
						}
						?>
					</table>
				</div>
			</div>
			<div class="col-xs-12">
				Observaciones
			</div>
			<div class="col-xs-12">
				<div class="col-xs-12">
					<table class="table table-bordered table-condensed">
						<tr>
							<td>
							<?php echo $item["observa_vendedor"];?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
?>
<script type="text/javascript">
	setTimeout(function(){
		//window.location.reload(1);
	}, 1000);
</script>
</body>
</html>