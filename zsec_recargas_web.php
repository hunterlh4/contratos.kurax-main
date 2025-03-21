<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

date_default_timezone_set("America/Lima");

$saldoweb_area_id = $login ? $login['area_id'] : 0;

$texto_tipo_venta = '';
$continuar = false;
$sw_permiso_depositar = 0;

if (array_key_exists($menu_id, $usuario_permisos)) {

	$local_id = $_COOKIE["usuario_local_id"];

	if (!empty($local_id)) {

		$query = " SELECT l.id, l.cc_id, l.nombre, lwc.agente_can_deposit FROM 
				tbl_locales  l
				inner join tbl_locales_web_config lwc ON lwc.local_id = l.id
				WHERE id = '" . $local_id . "'";

		$list_query = $mysqli->query($query);
		if ($mysqli->error) {
			echo $mysqli->error;
		} else {
			$list_locales = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_locales[] = $li;
			}
			if (count($list_locales) == 1) {
				$cc_id = strval($list_locales[0]["cc_id"]);
				$tienda_nombre = strval($list_locales[0]["nombre"]);
				$sw_permiso_depositar = $list_locales[0]["agente_can_deposit"];
				// echo $local_id.'-'.$cc_id;
				$continuar = true;
			}
		}
	}

	if (!$continuar) {
		echo 'No ha seleccionado un local.';
	}
} else {
	echo "No tienes permisos para este recurso.";
}

if ($continuar === true) {
?>

	<style>
		#mensaje_flotante {
			position: fixed;
			bottom: 0px;
			z-index: 5000;
		}

		.swal2-popup {
			font-size: 1.6rem !important;
		}
		.text-cred-dispon{
			text-align: right;
			color:#000;
			font-size:22px;
			font-weight:bold;
		}
		.cred-dispon{
			text-align: left;
			color:#000;
			font-size:22px;
		}
		@media (max-width: 767px) {
			.text-cred-dispon, .cred-dispon{
				text-align: center;
			}
		}
	</style>

	<script>
		var saldoweb_tienda_nombre = '<?php echo $tienda_nombre; ?>';
		var saldoweb_local_id = '<?php echo $local_id; ?>';
		var saldoweb_ccid = '<?php echo $cc_id; ?>';
		var saldoweb_area_id = '<?php echo $saldoweb_area_id; ?>';
	</script>
	<script src="js/sweetalert2@11.js"></script>
	<div class="row">
		<div class="col-md-12">
			<label style="font-size: 18px;color: black;" hidden>
				Local: <?php echo $local; ?>
			</label>

			<div class="panel" style="border-color: transparent;">

				<div class="panel-heading" style="border-color: #01579b;background: #fff;">
					<div class="row">
						<div class="col-md-6">
							<div class="panel-title" style="display: flex;color: #000;text-align: center;font-size: 18px;">
								Recargas Web&nbsp;<p style="color: red;"><?php echo $texto_tipo_venta; ?>
								<p>
							</div>
						</div>
						<div class="col-md-6 text-right">
							<button class="btn btn-primary" type="button" id="saldoweb_btn_regresar" style="font-weight: bold;font-size: 16px; display:none">
								<i class="fa fa-arrow-left"></i> Regresar
							</button>
						</div>
					</div>
				</div>
				<br>
				<!-- <div class="row">
					<div class="col-md-4"></div>
					<div class="col-xs-12 col-sm-8 col-md-8" style="padding: 0px;">
						<div class="alert alert-success alert-dismissible fade in" role="alert" style="margin: 0px;">
							<?php
							// $command_lcls = "
							// 		SELECT
							// 		m.updated_at
							// 		from tbl_modificaciones m
							// 		where modulo = 'Saldo Web'
							// 		ORDER BY m.id DESC
							// 		LIMIT 1
							// 		";
							// $list_query = $mysqli->query($command_lcls);
							// $saldoweb_ultima_actualizacion = '11/10/2022 12:30 p.m.';
							// while ($li = $list_query->fetch_assoc()) {
							// 	$saldoweb_ultima_actualizacion = $li["updated_at"];
							// }
							?>
							<p>
								El sistema ha sido actualizado el <b><?php echo $saldoweb_ultima_actualizacion; ?>.</b> En caso de identificar un mal funcionamiento presionar: <br>
								<b>Ctrl+F5</b> (Si estás en PC) o <b>Ctrl+Tecla Función +F5</b> (Si estás en laptop) o contactar con el área de soporte.
							</p>
						</div>
					</div>
				</div> -->
				<div class="col-md-12 text-center p-4">
					<span style="font-size:18px;">


					</span>
				</div>
				<div class="panel-body no-pad">
					<div id="saldoweb_cliente_buscador_div">
						<div class="row">
							<div class="col-md-4 text-cred-dispon">
								Crédito disponible:
							</div>
							<div class="col-md-3 cred-dispon">
								<span class="credito_disponible"></span>
							</div>
							<div class="col-md-3">
							</div>
						</div>
						<div class="row">
							<div class="col-md-4" style="color:#000;text-align:right;font-size:22px;font-weight:bold;">
								ID-WEB
							</div>
							<div class="col-md-3 mt-2">
								<input type="text" class="form-control" id="saldoweb_idweb" placeholder="000" autocomplete="off">
							</div>
							<div class="col-md-3 mt-2">
								<button class="btn btn-primary btn-block" type="button" id="saldoweb_btn_consultar">
									<span class="glyphicon glyphicon-search"></span> CONSULTAR
								</button>
							</div>
						</div>
					</div>
					<div id="saldoweb_cliente_div" hidden>
						<div class="row">
							<div class="col-md-2" style="color:#000;text-align:left;font-size:18px;">Crédito disponible:</div>
							<div class="col-md-10" style="color:black;font-weight:bold;font-size:20px;">
								<b class="credito_disponible"></b>
							</div>
						</div>
						<div class="row">
							<div class="col-md-2" style="color:#000;text-align:left;font-size:18px;">ID-WEB:</div>
							<div class="col-md-10" style="color:black;font-weight:bold;font-size:20px;" id="saldoweb_cliente_idweb"></div>
						</div>
						<div class="row">
							<div class="col-md-2" style="color:#000;text-align:left;font-size:18px;">Cliente:</div>
							<div class="col-md-10" style="color:black;font-weight:bold;font-size:20px;" id="saldoweb_cliente_name"></div>
						</div>
						<br>
						<div class="row">
							<div class="col-md-3">
								<?php if ((int)$sw_permiso_depositar > 0) { ?>
									<button class="btn btn-info btn-block" type="button" id="saldoweb_btn_deposito" style="font-weight: bold;font-size: 16px;">
										<span class="fa fa-arrow-circle-up"></span>&nbsp;&nbsp;&nbsp;DEPÓSITO
									</button>
								<?php } ?>
							</div>
							<div class="col-md-2"></div>
							<div class="col-md-2"></div>
							<div class="col-md-3">
								<!-- aqui iba el boton retiro -->
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-md-12">
								<table class="table table-hover table-striped table-bordered" id="saldoweb_tbl_transacciones"></table>
							</div>
							<br>
							<div class="col-md-12" style="font-size: 15px;color: black;">
								Últimas 10 transacciones.
								<br>
								*En caso de estados <b style="color: red;">FALLIDO</b> hacer clic en el boton de reenvio
								<button type="button" class="btn btn-success" style="padding: 2px 5px;" title="Reenviar Solicitud">
									<span class="fa fa-cloud-upload"></span>
								</button>,
								si el problema persiste se debe comunicar con soporte.
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>



	<!--**************************************************************************************************-->
	<!-- MODAL DEPOSITO -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="saldoweb_modal_deposito" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="saldoweb_modal_deposito_titulo">Depósito</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="form-group">
							<label for="saldoweb_modal_deposito_monto" class="col-sm-4 control-label" style="text-align: right;">Monto S/:</label>
							<div class="col-sm-8" style="margin-bottom: 10px;">
								<input type="text" class="form-control" id="saldoweb_modal_deposito_monto" placeholder="0.00" autocomplete="off" style="color: black;">
							</div>
						</div>
					</div>
				</div>
				<!-- PP -->
				<input type="hidden" id="deposito_txn">
				<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
						<b><i class="fa fa-close"></i> CERRAR</b>
					</button>
					<button type="button" class="btn btn-primary pull-right" id="saldoweb_modal_deposito_btn_guardar">
						<b><i class="fa fa-save"></i> DEPOSITAR</b>
					</button>
				</div>
			</div>
		</div>
	</div>



	<!--**************************************************************************************************-->
	<!-- MODAL VOUCHER APUESTA CALIMACO               DEPOSITO  -->
	<!--**************************************************************************************************-->

	<style type="text/css">
		.font_1 {
			font-size: 15px;
		}

		.font_2 {
			font-size: 20px;
		}
	</style>

	<div class="modal fade" id="modal_saldoWeb_deposito_voucher" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-md" style="width: 450px;" role="document">
			<div class="modal-content">
				<div class="modal-body" style="font-size: 15px;">
					<div id="sec_tlv_content_modal_voucher_apuesta_altenar">
						<div class="row">
							<div class="form-group">
								<label class="col-sm-12 control-label" style="text-align: center;color: black" id="modal_saldoWeb_deposito_voucher_tienda"></label>
								<br>
								<label class="col-sm-12 control-label" style="text-align: center;color: black;font-weight: 100;" id="modal_saldoWeb_deposito_voucher_direccion"></label>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="form-group">
								<label class="col-sm-12 control-label" style="text-align: center;color: black" id="modal_voucher_apuesta_altenar_titulo">
									Depósito Web Apuesta Total
								</label>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align: left;color: black;">N° de recibo: </label>
								<label id="modal_saldoWeb_deposito_voucher_nrecibo" class="col-sm-3 control-label" style="text-align: left; padding-left: 0px;color: black;font-weight: 100;"></label>
								<label id="modal_saldoWeb_deposito_voucher_fechahora" class="col-sm-5 control-label" style="text-align: left;color: black;font-weight: 100;"></label>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="form-group">
								<label class="col-sm-12 control-label" style="text-align: left;color: black;" id="modal_voucher_apuesta_idweb">
									Datos del cliente:
								</label>
								<label id="modal_saldoWeb_deposito_voucher_datosCliente" class="col-sm-12 control-label" style="text-align: center;color: black;font-weight: 100; "></label>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="form-group">
								<label class="col-sm-12 control-label" style="text-align: left;color: black;" id="modal_voucher_apuesta_altenar_footer">
									Fundamento y finalidad del recibo:
									<br>
									<center style="color: black;font-weight: 100; ">Depósito Web en Apuesta Total</center>
								</label>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="form-group">
								<label class="col-sm-2 control-label" style="text-align: left;color: black;">Monto: </label>
								<label class="col-sm-10 control-label" style="text-align: left;color: black;font-weight: 100;" id="modal_saldoWeb_deposito_voucher_monto"></label>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="form-group">
								<label class="col-sm-12 control-label" style="text-align: center;color: black;" id="modal_voucher_apuesta_altenar_footer">
									PARA GANAR HAY QUE CREER
								</label>
							</div>
						</div>
						<hr>
					</div>
					<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
						<div class="col-md-4">
							<button type="button" class="btn btn-info btn-sm pull-left" data-dismiss="modal">
								<b><i class="fa fa-close"></i> Cerrar</b>
							</button>
						</div>
						<div class="col-md-4"></div>
						<div class="col-md-4" id="div_voucher_saldo_web">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>



<?php
}
?>