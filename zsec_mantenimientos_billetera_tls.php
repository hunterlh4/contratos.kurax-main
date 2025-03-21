<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];
$area_id  = $login ? $login['area_id'] : 0;
 
date_default_timezone_set("America/Lima");

$tls_continuar=false;

if (in_array("view", $usuario_permisos[$menu_id])) {
	$tls_continuar = true;
} else {
	echo "&nbsp; No tienes permisos para ver este recurso.";
}

if($tls_continuar == true){
	if($area_id != 6){
		echo "&nbsp; Lo sentimos, este modulo es de uso restringido para T.I.";
		$tls_continuar=false;
	}else{
		$tls_continuar = true;
	}
}


if($tls_continuar===true){
?>

<style type="text/css">
    .fullscreen-modal .modal-dialog {
      margin: 0;
      margin-right: auto;
      margin-left: auto;
      margin-top: 10px;
      width: 100%;
    }
    @media (min-width: 768px) {
      .fullscreen-modal .modal-dialog {
        width: 750px;
      }
    }
    @media (min-width: 992px) {
      .fullscreen-modal .modal-dialog {
        width: 970px;
      }
    }
    @media (min-width: 1200px) {
      .fullscreen-modal .modal-dialog {
         width: 95%;
      }
    }



	.btn-default.active {
		color: #fff !important;
		background-color: #263238 !important;
		border-color: #263238 !important;
	}

	/* DIV - TAB */
	.alert-default{
		background-color: #f1f1f1;
		border-color: #898989;
		padding: 0px !important;
	}
	.div_tab{
		margin: 0px !important;
		padding: 0px !important;
	}
	.mant_bil_div_tab_cliente {
		/*fondo azul oscuro*/
		background-color: #395168;
		min-height: 40px;
		color: white;
		margin: 0px !important;
		padding: 0px !important;
		margin: 8px !important;
	}
	.mant_bil_div_tab_cliente.active {
		background-color: #659ce0;
	}
	.mant_bil_div_tab_cliente.naranja {
		background-color: #f0ad4e;
	}
	.mant_bil_div_tab_cliente_texto {
		font-weight: bold;
		font-size: 15px;
		width: 90%;
		margin: 0px !important;
		margin-top: 10px !important;
	}
	.mant_bil_div_tab_cliente_close {
		width: 10%;
		margin: 0px !important;
		padding: 0px !important;
	}
	.mant_bil_div_sec_televentas_padding {
		padding: 5px 0px 0px 0px !important;
		font-size: 10px;
	}
	body{
		background: none !important;
	}
	.colorpicker{
		z-index: 100000;
	}
	.campo_obligatorio{
		font-size: 13px;
		color: red;
		padding-left: 15px;
	}
</style>
<div class="tbl_goldenRace_retail_jackpots" id="div_sec_billetera_tls">

	<input id="mant_bil_g_fecha_actual" type="hidden" value="<?php echo date('Y-m-d'); ?>">
	<input id="mant_bil_g_fecha_hace_7_dias" type="hidden" value="<?php echo date('Y-m-d', strtotime(date('Y-m-d') . '- 7 days')); ?>">

	<div id="loader_"></div>


	<!-- *****************************************
	******************************************
	BUSCADOR
	******************************************
	****************************************** -->
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div class="row">
			<label style="font-size: 18px;color: darkblue;">
			 	Mantenimiento Billetera TLS				 
			</label>
			<?php if(in_array("add_reason_edit_balance", $usuario_permisos[$menu_id])){ ?>
				<button id="mv_btn_add_reason_edit_balance" type="button" class="btn btn-info pull-right" style="margin-left: 10px;">
					<span class="fa fa-edit"></span> Motivos Editar Balance
				</button>
			<?php } ?>
			<button class="btn btn-warning pull-right" style="color:black;" type="button" id="mant_bil_btn_consultar_dif_cliente">
				<span class="glyphicon glyphicon-search"></span> VER DIFERENCIAS EN BALANCES
			</button>
			</div>
			<br>
			<div class="panel panel-primary" style="margin-bottom: 5px;">
				<div class="panel-heading">
					<div class="panel-title">Buscador</div>
				</div>
				<div class="panel-body" style="padding: 5px 10px 0px 10px;">
					<form id="mant_bil_form_consultar">
						<input id="id_cajero_tlv" type="hidden" value="<?php echo($login["id"] )?>" autocomplete="off">
						<div class="row" style="padding-bottom: 5px;">
							<div class="col-md-4">
								<div class="btn-group btn-group-justified" data-toggle="buttons" id="buscador_tipo_div">
									<label class="btn btn-default active">
										<input type="radio" name="buscador_tipo" value="0"> DNI
									</label>
									<label class="btn btn-default">
										<input type="radio" name="buscador_tipo" value="1"> CE/PTP
									</label>
									<label class="btn btn-default">
										<input type="radio" name="buscador_tipo" value="8"> ID-WEB
									</label>
								</div>
							</div>
							
							<div class="col-md-8">							
								<div class="col-md-9 no-pad">
									<div class="focoPadre">
										<input id="mant_bil_buscador_texto" oninput="this.value=this.value.replace(/[^0-9]/g,'');" class="auto-focus" type="text" placeholder="Ingrese Nro." autocomplete="off">
										<span class="triki"></span>
									</div>
									<label style="color: red;" id="mant_bil_buscador_lbl_mensaje"></label>
								</div>
								<div class="col-md-3 no-pad">
								 
										<button class="btn btn-primary btn-block" type="submit" id="mant_bil_btn_consultar_cliente">
											<span class="glyphicon glyphicon-search"></span> CONSULTAR
										</button>
								 
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="row" id="div_tabs" hidden>
		<div class="col-xs-12 col-sm-12">
			<div class="alert alert-default" style="margin-bottom: 5px;">
				<div class="row" id="mant_bil_div_tabs_contenedor">
				</div>
			</div>
		</div>
	</div>

	<!-- *************************************************************************************************************************
	******************************************************************************************************************************
	RESULTADO
	******************************************************************************************************************************
	************************************************************************************************************************** -->
	<div class="row" id="mant_bil_div_resultado" hidden>
		<div class="col-xs-12 col-sm-12">
			<div class="panel panel-primary" style="margin-bottom: 5px;">
				<div class="panel-body" style="padding: 0px !important; padding-top: 4px !important;">

					<div class="row">
						<!--DIV ETIQUETAS-->
						<div class="col-md-12 mant_bil_div_etiquetas" style="padding-left: 4px;padding-right: 4px;">
							<div class="btn-group" id="div_labels">
							</div>
						</div>
						<!--DIV CLIENTE-->
						<div class="col-md-3 mant_bil_div_cliente" style="padding-left: 0px;" hidden>
							<div class="panel" style="border-color: transparent;margin-bottom: 10px;">
								<div class="panel-heading" style="border-color: #01579b;background: #fff;">
									<div class="panel-title" style="color: #000;">Cliente</div>
								</div>
								<div class="panel-body mant_bil_div_sec_televentas_padding">
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label>Tipo Documento</label>
												<select class="form-control" id="cliente_tipo_doc">
													<option value="">:: Seleccione ::</option>
													<option value="0">DNI</option>
													<option value="1">CE/PTP</option>
													<option value="2">PASAPORTE</option>
												</select>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Núm. Documento</label>
												<div style="display: flex;">
													<input type="text" class="form-control" id="cliente_num_doc" placeholder="Núm. doc." style="width: 100%;">
											 
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Fecha Creación Web</label>
												<input type="text" class="form-control" id="sec_tlv_hist_fecha_creacion_web" disabled>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Celular</label>
												<input type="text" class="form-control" id="cliente_celular" placeholder="Celular">
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Fecha Nacimiento</label>
												<input
													type="text"
													name="cliente_fec_nac"
													id="cliente_fec_nac"
													class="form-control fecha_nac_datepicker_tlv"	disabled
													>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group" id="cliente_idweb_div">
												<label>ID-WEB</label>
												<input type="text" class="form-control" id="cliente_idweb" placeholder="ID-WEB">
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group" id="cliente_webfullname_div">
												<label hidden>WEB-NOMBRES</label>
												<input type="text" class="form-control" id="cliente_webfullname" placeholder="" disabled>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group" id="cliente_idweb_div">
												<label>ID-JUGADOR</label>
												<input type="text" class="form-control" id="cliente_idjugador" placeholder="ID-JUGADOR">
											</div>
										</div>
										<div class="col-md-12" style="display: none;">
											<div class="form-group" id="cliente_idweb_calimaco_div">
												<label>ID-WEB CALIMACO</label>
												<input type="text" class="form-control" id="cliente_idwebc" placeholder="ID-WEB CALIMACO">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label>Nombres</label>
												<input type="text" class="form-control" id="cliente_nombre" placeholder="Nombres">
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Apellido Paterno</label>
												<input type="text" class="form-control" id="cliente_apepaterno" placeholder="Apellido Paterno">
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Apellido Materno</label>
												<input type="text" class="form-control" id="cliente_apematerno" placeholder="Apellido Materno">
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Bono Limite</label> 
												<input type="text" class="form-control" id="bono_limite" placeholder="00">
											</div>
										</div>
										<div class="col-md-12" disabled>
											<div class="form-group" id="cliente_local_div">
												<label>Local</label>
												<?php
													$command_lcls ="
														SELECT
															tbl_locales.cc_id,
															tbl_locales.nombre
														FROM
															tbl_locales
														WHERE
															tbl_locales.estado = 1 
															AND tbl_locales.operativo = 1 
															AND tbl_locales.red_id IN ( 1, 9, 5 ) -- 8=TELEVENTAS
															AND tbl_locales.zona_id IS NOT NULL 
															AND tbl_locales.cc_id IS NOT NULL 
														ORDER BY
															tbl_locales.nombre;
														";
													$list_query=$mysqli->query($command_lcls);
													$list_tipo=array();
													while ($li=$list_query->fetch_assoc()) {
														$list_lcls[]=$li;
													}
												?>
												<select class="form-control" id="cliente_local" style="width: 100%;" disabled>
													<option value="3900">TELEVENTAS</option>
													<?php foreach ($list_lcls as $key => $value) { ?>
														<option value="<?php echo $value['cc_id']?>"><?php echo $value["nombre"];?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group" id="cliente_idweb_div">
												<label>TERCERO AUTORIZADO</label>
												<select class="form-control" id="sec_tlv_hist_cliente_tercero_titular" ></select>
											</div>
										</div>
									</div>
									<hr style="margin: 10px !important;">
									<div class="row">
						
									
									</div>
								</div>
							</div>
						</div>
						
						<!--DIV TRANSACCIONES-->
						<div class="col-md-9 mant_bil_div_transacciones" style="padding-right: 3px;" hidden>
							<div class="panel" style="border-color: transparent;">

								<div class="panel-heading" style="border-color: #01579b;background: #fff;">
									<div class="row" style="color: #000;">
										<div class="col-md-5" style="font-size: 14px;">TRANSACCIONES</div>
										<div class="col-md-2" style="padding-right: 0px;">
											<div class="form-group">
												<button type="button" id="mant_bil_btn_estado_cuenta" class="btn btn-info btn-sm"
													onclick="mant_bil_estado_cuenta()">
													<i class="fa fa-clipboard"></i>
													Estado de Cuenta		
												</button>
											</div>
										</div>
										<div class="col-md-2" style="padding-right: 0px;">
											<div class="form-group">
												<input id="mant_bil_cliente_fecha_inicio" type="text" class="form-control" 
													style="height: 20px;color: black;border-radius: 5px;border: 1px solid #aaa;">
											</div>
										</div>
										<div class="col-md-2" style="padding-left: 0px;">
											<div class="form-group">
												<input id="mant_bil_cliente_fecha_fin" type="text" class="form-control" 
													style="height: 20px;color: black;border-radius: 5px;border: 1px solid #aaa;">
											</div>
										</div>
										<div class="col-md-1" style="padding: 0px;">
											<div class="form-group">
												<button type="button" class="btn btn-primary" 
														id="mant_bil_btn_actualizar_tabla_transacciones" 
														style="height: 20px;width: 100%;padding: 0px;">
														<span class="glyphicon glyphicon-refresh"></span>
													</button>
											</div>
										</div>
									</div>
								</div>
								<div class="panel-body mant_bil_div_sec_televentas_padding">
									<div class="row mant_bil_div_sec_televentas_padding">
										<div class="col-md-3 mant_bil_div_sec_televentas_padding">
											<h3 style="margin-top: 0px;padding: 0px !important;margin-bottom: 0px;font-weight: 700;font-size: 16px;">
												Balance: S/ <span id="span_balance">0.00</span></h3>
										</div>
										<div class="col-md-3 mant_bil_div_sec_televentas_padding" hidden>
											<h3 style="margin-top: 0px;padding: 0px !important;margin-bottom: 0px;font-weight: 700;font-size: 16px;">
												Bono Disponible: S/ <span id="span_balance_bono_disponible">0.00</span></h3>
										</div>
										<div class="col-md-3 mant_bil_div_sec_televentas_padding">
												<h3 style="margin-top: 0px;padding: 0px !important;margin-bottom: 0px;font-weight: 700;font-size: 16px;">
													Retirable: S/ <span id="span_balance_retiro_disponible">0.00</span></h3>
										</div>
										<div class="col-md-3 mant_bil_div_sec_televentas_padding" hidden>
											<h3 style="margin-top: 0px;text-align: right;padding: 0px !important;margin-bottom: 0px;font-weight: 700;font-size: 16px;">
												Bonos del Mes: S/ <span id="span_bonos">0.00</span></h3>
										</div>
										<div class="col-md-3 mant_bil_div_sec_televentas_padding">
                                        	<h3 style="margin-top: 0px;padding: 0px !important;margin-bottom: 0px;font-weight: 700;font-size: 16px;"
                                        	>
											Bono AT: S/ <span id="mant_bil_span_dinero_at">0.00</span></h3> 
                                            
	                                    </div>
	                                    <div class="form-inline col-md-3" style="font-size: 14px;">
											<label for="mant_bil_cbx_tipo_balance" class="control-label" style="font-weight: 700">Tipo de Saldo: </label>
                                        	<select class="form-control" id="mant_bil_cbx_tipo_balance" 
                                        		style="border-radius: 5px;border: 1px solid #aaa;color: black;" >
												<option value="1">Real</option>
												<option value="6">Promocional</option>
												<option value="999">Todos</option>
											</select>
                                        </div>
									</div>

									<div class="row mant_bil_div_sec_televentas_padding">
										<div class="sec_tlv_botones_funciones">
											<div class="col-md-9 mant_bil_div_sec_televentas_padding">
												<?php if(in_array("edit_balance", $usuario_permisos[$menu_id])){ ?>
													<button id="mb_btn_edit_balance" type="button" class="btn btn-success" 
														style="font-size: 12px;">
														<span class="fa fa-edit"></span> Editar Balance
													</button>
												<?php } ?>
												<?php if(in_array("mb_subir_balance", $usuario_permisos[$menu_id])){ ?> 
													<button id="mb_btn_subir_balance" type="button" class="btn btn-primary" 
														style="font-size: 12px;">
														<span class="glyphicon glyphicon-plus"></span> SUBIR BALANCE
													</button>
												<?php } ?>
												<?php if(in_array("mb_bajar_balance", $usuario_permisos[$menu_id])){ ?>
												 <button id="mb_btn_bajar_balance" type="button" class="btn btn-danger" 
														style="font-size: 12px;">
														<span class="glyphicon glyphicon-minus"></span> BAJAR BALANCE
													</button>
												<?php } ?> 
											</div>
										</div>
									</div>
								 
									<div class="row mant_bil_div_sec_televentas_padding">
										<div class="col-md-12 mant_bil_div_sec_televentas_padding">
											<table class="table table-hover" id="mant_bil_tabla_transacciones">

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
 
 

	<!--**************************************************************************************************-->
	<!-- MODAL ESTADO DE CUENTA -->
	<!--**************************************************************************************************-->
	<style type="text/css">
		.font_1{
			font-size: 15px;
		}
		.font_2{
			font-size: 20px;
		}
	</style>
	<div class="modal fade" id="mant_bil_modal_estado_cuenta" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-md" style="width: 450px;" role="document">
		<div class="modal-content" style="margin: 10px;">
			<div class="modal-body">
				<div class="row" >
					<div class="form-group" id="sec_tlv_modal_solicitud_de_retiro_div" style="text-align: center;">
						<img src="img/logo_at_voucher.jpeg" height="100"><br>
						<label class="font_1" style="cursor: text;">Teleservicios</label><br>
						<label class="font_1" style="cursor: text;">Estado de Cuenta</label><br>
						<label class="font_1" style="cursor: text;"><?php echo date('Y-m-d h:i:s a', time()); ?></label><br>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="col-12" style="text-align: center;">
							<label class="control-label col-sm-2" style="text-align: right;color: black;cursor: text; color: black;">Cliente:</label>
							<label id="sec_tlv_ec_cliente_name" class="control-label col-sm-10" style="text-align: left;cursor: text; "></label>	
						</div>	
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="col-12" style="text-align: center;">
							<label class="control-label col-sm-6" style="text-align: right;color: black;cursor: text;">Balance:</label>
							<label id="sec_tlv_ec_balance_total" class="control-label col-sm-6" style="text-align: left;cursor: text;"></label>	
						</div>	
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="col-12" style="text-align: center;">
							<label class="col-sm-6 control-label" style="text-align: right;color: black; cursor: text;">Retiro Disponible:</label>
							<label id="sec_tlv_ec_retiro_disponible" class="col-sm-6 control-label" style="text-align: left; cursor: text;"></label>
						</div>	
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<br>
						<label class="col-sm-12 control-label" style="text-align: center; color: black; cursor: text;">Últimas Transacciones:</label>
						<br>
						<div style="text-align: center; display: flex; justify-content: center;">
							
							<div id="sec_tlv_ec_div_ultimas_transacciones" style="text-align: justify; cursor: text;">
								
							</div>
						</div>
					</div>
				</div>
				<hr>
				<div class="row" style="text-align: center;">
					<button type="button" class="btn btn-info align-middle" data-dismiss="modal">
						<b><i class="fa fa-close"></i> OK</b>
					</button>
				</div>
			</div>
		</div>
	  </div>
	</div>


	<!--**************************************************************************************************-->
	<!-- MODAL VOUCHER APUESTA -->
	<!--**************************************************************************************************-->

	<style type="text/css">
		.font_1{
			font-size: 15px;
		}
		.font_2{
			font-size: 20px;
		}
	</style>

	<div class="modal fade" id="HisCli_modal_voucher_apuesta" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <!-- <div class="modal-dialog" role="document"> -->
	  <div class="modal-dialog modal-md" style="width: 450px;" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row" >
					<div class="form-group" style="text-align: center;">
						<img id="HisCli_modal_voucher_apuesta_logo" src="" height="150"><br>
						<label class="font_1" style="font-size: 24px;color: black;font-family: system-ui;">Teleservicios</label><br><br>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_apuesta_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Caja:</label>
						<label id="HisCli_modal_voucher_apuesta_caja" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_apuesta_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Proveedor:</label>
						<label id="HisCli_modal_voucher_apuesta_proveedor" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row" id="HisCli_modal_voucher_apuesta_tipo_torito_div">
					<div class="form-group" id="modal_voucher_apuesta_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Tipo:</label>
						<label id="HisCli_modal_voucher_apuesta_tipo_torito" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_apuesta_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">ID-Apuesta:</label>
						<label id="HisCli_modal_voucher_apuesta_idtransaccion" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_apuesta_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Fecha Hora:</label>
						<label id="HisCli_modal_voucher_apuesta_fechahora" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row" id="HisCli_modal_voucher_apuesta_estado_div">
					<div class="form-group" id="modal_voucher_apuesta_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Estado:</label>
						<label id="HisCli_modal_voucher_apuesta_estado" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_apuesta_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto:</label>
						<label id="HisCli_modal_voucher_apuesta_monto" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<hr>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="col-md-5"></div>
					<div class="col-md-3">
						<button type="button" class="btn btn-info btn-sm" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> OK</b>
						</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL VOUCHER APUESTA ANULADA -->
	<!--**************************************************************************************************-->

	<style type="text/css">
		.font_1{
			font-size: 15px;
		}
		.font_2{
			font-size: 20px;
		}
	</style>

	<div class="modal fade" id="HisCli_modal_voucher_ap_anulada" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <!-- <div class="modal-dialog" role="document"> -->
	  <div class="modal-dialog modal-md" style="width: 450px;" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row" >
					<div class="form-group" style="text-align: center;">
						<img src="img/logo_at_voucher.jpeg" height="150"><br>
						<label class="font_1" style="font-size: 24px;color: black;font-family: system-ui;">Teleservicios</label><br><br>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_ap_anulada_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Fecha Hora:</label>
						<label id="HisCli_modal_voucher_ap_anulada_fechahora" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_ap_anulada_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Caja:</label>
						<label id="HisCli_modal_voucher_ap_anulada_caja" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_ap_anulada_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Promotor:</label>
						<label id="HisCli_modal_voucher_ap_anulada_promotor" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_ap_anulada_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Proveedor:</label>
						<label id="HisCli_modal_voucher_ap_anulada_proveedor" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_ap_anulada_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">ID-Apuesta:</label>
						<label id="HisCli_modal_voucher_ap_anulada_idtransaccion" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_ap_anulada_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Estado:</label>
						<label class="col-sm-8 control-label" style="text-align: left;">Apuesta Anulada</label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_ap_anulada_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto:</label>
						<label id="HisCli_modal_voucher_ap_anulada_monto" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<hr>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="col-md-5"></div>
					<div class="col-md-3">
						<button type="button" class="btn btn-info btn-sm" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> OK</b>
						</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>




	<!--**************************************************************************************************-->
	<!-- MODAL OBSERVACION VALIDADOR-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="HisCli_modal_observacion_validador" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel" style="color: black;">Observaciones:</h4>
			</div>
			<div class="modal-body">
				<div id="HisCli_modal_ov_deposito_aprobado">
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_fechahora_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Caja:</label>
							<label id="HisCli_modal_ov_caja" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_fecha_hora_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Registro Depósito:</label>
							<label id="HisCli_modal_ov_fecha_hora" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_num_operacion_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Núm. Operación:</label>
							<label id="HisCli_modal_ov_num_operacion" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_id_web_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">ID-WEB:</label>
							<label id="HisCli_modal_ov_id_web" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_cliente_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Cliente:</label>
							<label id="HisCli_modal_ov_cliente" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_monto_deposito_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto Depósito:</label>
							<label id="HisCli_modal_ov_monto_deposito" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_monto_comision_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto Comisión:</label>
							<label id="HisCli_modal_ov_monto_comision" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_monto_real_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto Real:</label>
							<label id="HisCli_modal_ov_monto_real" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_nombre_bono_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Bono:</label>
							<label id="HisCli_modal_ov_nombre_bono" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_monto_bono_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto Bono Max.:</label>
							<label id="HisCli_modal_ov_monto_bono" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_ov_tipo_jugada_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Tipo Jugada:</label>
							<label id="HisCli_modal_ov_tipo_jugada" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row" hidden>
						<div class="form-group" id="HisCli_modal_ov_monto_recarga_div">
							<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto Recarga:</label>
							<label id="HisCli_modal_ov_monto_recarga" class="col-sm-8 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row" id="HisCli_sec_tlv_comision_comprobante_div">
						<div class="form-group">
							<label class="col-sm-5 control-label" style="text-align: right;color: black;">Comisión - Comprobante:</label>
							<img id="HisCli_sec_tlv_img_comprobante_comision" style="height: auto; margin: 0 auto;" src="" class="col-sm-12"> <br>
						</div>
					</div>
					<hr>
				</div>
				<div class="row" id="HisCli_modal_ov_obs_cajero_div">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Cajero:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
								<textarea class="form-control" id="HisCli_modal_ov_obs_cajero" rows="5" style="color: black;" disabled></textarea>
						</div>
					</div>
				</div>
				<div class="row" id="HisCli_modal_ov_rechazo_div">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Motivo Rechazo:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
								<textarea class="form-control" id="HisCli_modal_ov_rechazo" rows="2" style="color: black;" disabled></textarea>
						</div>
					</div>
				</div>
				<div class="row" id="HisCli_modal_ov_obs_validador_div">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Validador:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
								<textarea class="form-control" id="HisCli_modal_ov_obs_validador" rows="5" style="color: black;" disabled></textarea>
						</div>
					</div>
				</div>
				<div class="row" id="HisCli_modal_ov_obs_supervisor_div">
					<div class="form-group">
						<label class="col-sm-2 control-label" style="text-align: right;color: black;">Supervisor:</label>
						<div class="col-sm-10" style="margin-bottom: 10px;">
								<textarea class="form-control" id="HisCli_modal_ov_obs_supervisor" rows="5" style="color: black;" disabled></textarea>
						</div>
					</div>
				</div>
				<div class="row" style="margin-top: 20px;">
					<div class="col-md-5"></div>
					<div class="col-md-2">
						<button type="button" class="btn btn-info pull-left" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> OK</b>
						</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>



	<!--**************************************************************************************************-->
	<!-- MODAL OBSERVACION SUPERVISOR-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="HisCli_modal_observacion_supervisor"  role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Observación</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group">
						<label class="col-sm-12 control-label" style="text-align: center;color: black;">
							LA TRANSACCIÓN SE ELIMINARÁ.
						</label>
						<br>
						<label class="col-sm-12 control-label" style="text-align: center;color: gray;">
							Esta acción, afectará el Balance y/o Bono del cliente.
						</label>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="form-group">
					<label for="motivo_del_dep" class="col-sm-12 control-label" style="text-align: left;color: black;"><spam style="color: red; font-weight: bold;">(*)</spam> Motivos:</label>
					 
					<div class="col-sm-12" style="margin-bottom: 10px;">
							<select class="form-control" id="HisCli_motivo_del_dep" style="width: 100%;font-size: 15px;">
									<option value="0">:: Seleccione ::</option>
									<?php
									$query = "SELECT id, tipo_rechazo FROM tbl_televentas_tipo_rechazo WHERE status='2'; ";
									$resp_query = $mysqli->query($query);
									while ($li2 = $resp_query->fetch_assoc()) {
									?>
										<option value="<?php echo $li2['id'] ?>"><?php echo $li2["tipo_rechazo"]; ?></option>
									<?php } ?>
							</select>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="form-group">
						<label class="col-sm-12 control-label" style="text-align: left;color: black;">
							<spam style="color: red; font-weight: bold;">(*)</spam>
							Observación:
							
						</label>
						<br>
						<div class="col-sm-12" style="margin-bottom: 10px;">
								<textarea required class="form-control" id="HisCli_modal_os_observacion" placeholder="Escribir una observación ..." 
									rows="5"></textarea>
						</div>
					</div>
				</div>

				<span class="campo_obligatorio">Los Campos con (*) son obligatorios</span>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
				<button type="button" class="btn btn-danger pull-right" id="HisCli_modal_os_btn_guardar">
					<b><i class="fa fa-trash"></i> ELIMINAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL VOUCHER RECARGA Y RETORNO-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="HisCli_modal_voucher" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row" style="margin-top: 20px;margin-bottom: 16px;">
					<div class="form-group" id="HisCli_modal_voucher_fechahora_div">
						<label class="col-sm-12 control-label" style="text-align: center;font-size: 24px;color: black;">Recarga Web</label>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_caja_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Caja:</label>
						<label id="HisCli_modal_voucher_caja" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_idtransaccion_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Transacción:</label>
						<label id="HisCli_modal_voucher_idtransaccion" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_fechahora_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Fecha Hora:</label>
						<label id="HisCli_modal_voucher_fechahora" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_idweb_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">ID-WEB:</label>
						<label id="HisCli_modal_voucher_idweb" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_cliente_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Cliente:</label>
						<label id="HisCli_modal_voucher_cliente" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_monto_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;font-size: 16px;">Recarga S/:</label>
						<label id="HisCli_modal_voucher_monto" class="col-sm-8 control-label" style="text-align: left;font-size: 16px;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_bono_nombre_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;font-size: 16px;">Bono:</label>
						<label id="HisCli_modal_voucher_bono_nombre" class="col-sm-8 control-label" style="text-align: left;font-size: 16px;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_bono_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;font-size: 16px;">Bono S/:</label>
						<label id="HisCli_modal_voucher_bono" class="col-sm-8 control-label" style="text-align: left;font-size: 16px;"></label>
					</div>
				</div>
				<div class="row" hidden>
					<div class="form-group" id="HisCli_modal_voucher_total_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Total:</label>
						<label id="HisCli_modal_voucher_total" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<hr>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="col-md-5"></div>
					<div class="col-md-2">
						<button type="button" class="btn btn-info pull-left" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> OK</b>
						</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL VOUCHER APUESTA ALTENAR -->
	<!--**************************************************************************************************-->

	<style type="text/css">
		.font_1{
			font-size: 15px;
		}
		.font_2{
			font-size: 20px;
		}
		.modal_voucher_apuesta_altenar_tabla_tr{
			border: solid 2px;
		}
		.modal_voucher_apuesta_altenar_tabla_hr{
			margin: 0px;
			color: black;
			background-color: black;
			height: 2px;
		}
	</style>

	<div class="modal fade" id="HisCli_modal_voucher_apuesta_altenar" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-md" style="width: 450px;" role="document">
		<div class="modal-content">
			<div class="modal-body" style="font-size: 15px;">
				<div id="HisCli_sec_tlv_content_modal_voucher_apuesta_altenar">
					<div class="row" >
						<div class="form-group" style="text-align: center;">
							<img src="img/logo_at_voucher.jpeg" height="150"><br>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-6 control-label" style="text-align: left;color: black;font-weight: 100;" id="HisCli_modal_voucher_apuesta_altenar_fecha">Fecha:</label>
							<label class="col-sm-6 control-label" style="text-align: left;color: black;font-weight: 100;" id="HisCli_modal_voucher_apuesta_altenar_hora">>Hora</label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-12 control-label" style="text-align: left;color: black;font-weight: 100;" id="HisCli_modal_voucher_apuesta_altenar_id_bet"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-12 control-label" style="text-align: left;color: black;font-weight: 100;" id="HisCli_modal_voucher_apuesta_altenar_cliente_name"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-12 control-label" style="text-align: left;color: black;font-weight: 100;" id="HisCli_modal_voucher_apuesta_altenar_caja"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-12 control-label" style="text-align: center;color: black;font-size: 30px;font-weight: bold;" id="HisCli_modal_voucher_apuesta_altenar_monto"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group" id="HisCli_modal_voucher_apuesta_altenar_qr" style="text-align: center;">
							<img height="100" />
						</div>
					</div>
					<div class="row" hidden>
						<div class="form-group">
							<label class="col-sm-12 control-label" style="text-align: center;color: black;" id="HisCli_modal_voucher_apuesta_altenar_qr_id_bet"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<table class="table" id="HisCli_modal_voucher_apuesta_altenar_tabla" style="color: black;border-color: black;">
								<tbody>
									<tr class="modal_voucher_apuesta_altenar_tabla_tr">
										<td>SIMPLE</td>
									</tr>
									<tr class="modal_voucher_apuesta_altenar_tabla_tr">
										<td>
											PAIS, DDDDDD
											<br>
											nddnidd
											<hr class="modal_voucher_apuesta_altenar_tabla_hr">
											ddjddkjdd
										</td>
									</tr>
									<tr class="modal_voucher_apuesta_altenar_tabla_tr">
										<td>
											PAIS, DDDDDD
											<br>
											nddnidd
											<hr class="modal_voucher_apuesta_altenar_tabla_hr">
											ddjddkjdd
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-12 control-label" style="text-align: center;color: black;font-weight: 100;" id="HisCli_modal_voucher_apuesta_altenar_footer">
								El pago de la apuesta ganada será hasta 30
								días después de validado el ticket.
							</label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-12 control-label" style="text-align: center;color: black;" id="HisCli_modal_voucher_apuesta_altenar_footer">
								¡Cuida tu ticket, no compartas tu código!
							</label>
						</div>
					</div>
					<hr>
				</div>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="row">
						<label class="col-sm-2">Estado: </label>
						<label class="col-sm-10 control-label" id="HisCli_modal_voucher_apuesta_altenar_estado"
							style="text-align: left;color: black;font-weight: bold;"></label>
					</div>
					<div class="row">
						<div class="col-md-4" style="text-align: center;">
							<button type="button" class="btn btn-info btn-sm" data-dismiss="modal" style="margin-left: 2px">
								<b><i class="fa fa-close"></i> OK</b>
							</button>	
						</div>
						<div class="col-md-4" style="text-align: center;">
							<button id="HisCli_sec_tlv_copiar_voucher_apuesta_pagada" type="button" class="btn btn-success btn-sm"  style="margin-left: 2px">
								<b><i class="fa fa-copy"></i> Copiar</b>
							</button>
						</div>
						<div class="col-md-4" style="text-align: center;">
							<button id="HisCli_sec_tlv_pdf_voucher_apuesta_pagada" type="button" class="btn btn-danger btn-sm"  style="margin-left: 2px">
								<b><i class="fa fa-copy"></i> PDF</b>
							</button>
						</div>	
					</div>
					
				</div>
			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL SOLICITUD DE RETIRO -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="HisCli_sec_tlv_modal_solicitud_de_retiro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row" style="margin-top: 20px;margin-bottom: 16px;">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div">
						<label class="col-sm-12 control-label" style="text-align: center;font-size: 24px;color: black;" 
							id="HisCli_sec_tlv_title_solicitud_retiro"></label>
						<input type="hidden" id="HisCli_sec_tlv_id_trans_retiro">
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Caja:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_caja" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Fecha Hora:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_fechahora" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Estado:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_estado" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_monto" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Banco de Pago:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_tipo_operacion_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Tipo Operación:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_tipo_operacion" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_dev_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Motivo Devolución:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_devolucion" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Motivo Rechazo:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_rechazo" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Motivo Cancelación:</label>
						<label id="HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Observación Supervisor:</label>
						<textarea readonly id="HisCli_sec_tlv_modal_solicitud_de_retiro_obs_supervisor" class="col-sm-8 control-label" style="text-align: left;"></textarea>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_pagador">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Observación Pagador:</label>
						<textarea readonly id="HisCli_sec_tlv_modal_solicitud_de_retiro_obs_pagador" class="col-sm-8 control-label" style="text-align: left;" ></textarea>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_sec_tlv_modal_solicitud_de_retiro_voucher">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Comprobante de Pago:</label>
						<div class="row" style="margin-bottom: 20px;" id="HisCli_sec_tlv_div_dp_voucher_retiro">
						
						</div>
					</div>
				</div>
				<hr>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="col-md-5"></div>
					<div class="col-md-2">
						<button type="button" class="btn btn-info pull-left" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> OK</b>
						</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL PARA EL VOUCHER DE PAGO DE SORTEO DEL MUNDIAL -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="HisCli_modal_voucher_sorteo_mundial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-md" style="width: 450px;" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row" >
					<div class="form-group" id="HisCli_modal_voucher_sorteo_mundial_titulo_div" style="text-align: center;">
						<br>
						<label class="col-sm-12 control-label" id="modal_voucher_sorteo_mundial_titulo" 
							style="font-weight: bold;font-size: 20px;color: #246fbe;"></label>
						<br>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_sorteo_mundial_cliente_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Cliente:</label>
						<label id="HisCli_modal_voucher_sorteo_mundial_cliente" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_sorteo_mundial_caja_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Caja:</label>
						<label id="HisCli_modal_voucher_sorteo_mundial_caja" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_sorteo_mundial_proveedor_div" hidden>
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Proveedor:</label>
						<label id="HisCli_modal_voucher_sorteo_mundial_proveedor" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_sorteo_mundial_fechahora_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Fecha Hora:</label>
						<label id="HisCli_modal_voucher_sorteo_mundial_fechahora" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<div class="row">
					<div class="form-group" id="HisCli_modal_voucher_sorteo_mundial_monto_div">
						<label class="col-sm-4 control-label" style="text-align: right;color: black;">Monto:</label>
						<label id="HisCli_modal_voucher_sorteo_mundial_monto" class="col-sm-8 control-label" style="text-align: left;"></label>
					</div>
				</div>
				<hr>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="col-md-5"></div>
					<div class="col-md-3">
						<button type="button" class="btn btn-info btn-sm" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> OK</b>
						</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>

	
	<!--**************************************************************************************************-->
	<!-- MODAL VOUCHER TERMINAL DEPOSIT TAMBO-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="HisCli_sec_tlv_modal_voucher_terminal_tambo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-md" style="width: 450px;" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div id="HisCli_sec_tlv_modal_terminal_tambo_body">
					<div class="row" >
						<div class="form-group" style="text-align: center;">
							<img src="img/logo_at_voucher.jpeg" height="150"><br>
						</div>
						<label class="col-sm-12 control-label" style="font-weight: bold;font-size: 20px;color: #246fbe; text-align: center;">
							DEPÓSITO TERMINAL
						</label>
					</div><br>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Fecha:</label>
							<label id="HisCli_sec_tlv_modal_tambo_fecha" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Hora:</label>
							<label id="HisCli_sec_tlv_modal_tambo_hora" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Cliente:</label>
							<label id="HisCli_sec_tlv_modal_tambo_cliente" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label id="HisCli_sec_tlv_modal_tambo_monto" class="col-sm-12 control-label" 
								style="font-weight: bold; font-size: 22px; text-align: center; color: black;"></label>
						</div>
					</div>
					<input type="hidden" id="HisCli_sec_tlv_modal_tambo_id_barcode">
					<div class="row" style="text-align: center;">
						<img src="" id="HisCli_sec_tlv_img_barcode_tambo_voucher">
					</div>
					<div class="row" style="text-align: center;">
						<div class="col-sm-12" style="text-align: center;">
							<label>¡Gracias por adquirir tu crédito para terminal</label><br>
							<label>Recuerda Sobrin@, que por teleservicios también</label><br>
							<label>puedes hacer tus jugadas y recargas!</label><br><br>
							<label>Atendemos las 24 horas.</label><br>
						</div>
					</div>
				</div>

				<br>
				<div class="row" style="text-align: center;">	
					<div class="col-md-6" style="text-align: center;">
						<button id="HisCli_sec_tlv_copiar_voucher_terminal_tambo" type="button" class="btn btn-success btn-sm"  style="margin-left: 2px">
							<b><i class="fa fa-copy"></i> Copiar</b>
						</button>
					</div>
					<div class="col-md-6" style="text-align: center;">
						<button id="HisCli_sec_tlv_pdf_voucher_terminal_tambo" type="button" class="btn btn-danger btn-sm"  style="margin-left: 2px">
							<b><i class="fa fa-copy"></i> PDF</b>
						</button>
					</div>
				</div>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="col-md-3">
						<button type="button" class="btn btn-light btn-sm" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> Cerrar </b>
						</button>
					</div>
				</div>

			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<!-- MODAL EDITAR BALANCE -->
	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="mb_modal_edit_balance" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">EDITAR BALANCE</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group">
						<label for="mb_modal_select_tipo_balance" class="col-sm-4 control-label" style="text-align: right;">
							Tipo de Balance:
						</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<select class="form-control select2" id="mb_modal_select_tipo_balance" 
								style="border-radius: 5px;border: 1px solid #aaa;color: black;height: 40px;font-size: 16px;">
								<option value="0">-- Seleccione --</option>
								<option value="1">BALANCE GENERAL</option>
								<option value="4">BALANCE NO RETIRABLE</option>
								<option value="5">BALANCE RETIRABLE</option>
								<option value="6">BALANCE BONO AT</option>
							</select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="form-group">
						<label for="mb_modal_balance_actual" class="col-sm-4 control-label" style="text-align: right;">Balance Actual S/</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<input type="text" class="form-control" id="mb_modal_balance_actual" placeholder="0.00" disabled 
							style="font-weight: bold;">
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group">
						<label for="mb_modal_nuevo_balance" class="col-sm-4 control-label" style="text-align: right;" id="mb_modal_monto_texto">Balance nuevo S/</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<input type="text" class="form-control" oninput="this.value=this.value.replace(/[^0-9.]/g,'');" id="mb_modal_nuevo_balance" placeholder="0.00" autocomplete="off" 
							style="color: black;">
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group">
						<label for="mb_modal_observacion_edit" class="col-sm-4 control-label" style="text-align: right;">
							Observación: 
						</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<textarea class="form-control" id="mb_modal_observacion_edit" rows="2" style="color: black;"></textarea>
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
				<button type="button" class="btn btn-primary pull-right" id="mb_modal_btn_guardar_edit_balance">
					<i class="fa fa-save"></i> <b>ACTUALIZAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>


	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<!-- MODAL VER DIFERENCIAS DE BALANCE -->
	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="mb_modal_ver_dif_balance" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-xl"  role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">DIFERENCIAS DE BALANCE</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="row mant_bil_div_sec_televentas_padding">
						<div class="col-md-12">
							<table class="table table-hover table-responsive" id="mant_bil_tabla_diferencias_balance">

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
	<!-- MODAL VOUCHER CORRECCIÓN BALANCE-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="mant_bil_modal_voucher_correccion_balance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-md" style="width: 450px;" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div id="mant_bil_modal_voucher_correccion_balance_body">
					<div class="row">
						<label class="col-sm-12 control-label" style="font-weight: bold;font-size: 20px;color: #246fbe; text-align: center;">
							CORRECCIÓN BALANCE
						</label>
					</div><br>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Transacción:</label>
							<label id="mb_modal_correc_transaccion" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Tipo Balance:</label>
							<label id="mb_modal_correc_tipo_balance" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Monto:</label>
							<label id="mb_modal_correc_monto" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Nuevo Balance:</label>
							<label id="mb_modal_correc_nuevo_balance" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Obervación:</label>
							<label id="mb_modal_correc_observacion" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-sm-1"></div>
							<label class="col-sm-4 control-label" style="text-align: left;color: black;">Usuario:</label>
							<label id="mb_modal_correc_usuario" class="col-sm-7 control-label" style="text-align: left;"></label>
						</div>
					</div>
				</div>
				<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
					<div class="col-md-3">
						<button type="button" class="btn btn-light btn-sm" data-dismiss="modal" style="width: 100%;">
							<b><i class="fa fa-close"></i> Cerrar </b>
						</button>
					</div>
				</div>

			</div>
		</div>
	  </div>
	</div>

	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<!-- MODAL EDITAR BALANCE -->
	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="mb_modal_add_reason_edit_balance" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">MOTIVOS EDITAR BALANCE</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group">
						<label for="mb_modal_select_tipo_edit_balance" class="col-sm-3 control-label" style="text-align: right;">
							Tipo
						</label>
						<div class="col-sm-9" style="margin-bottom: 10px;">
							<select class="form-control select2" id="mb_modal_select_tipo_edit_balance" 
								style="border-radius: 5px;border: 1px solid #aaa;color: black;height: 40px;font-size: 16px;">
								<option value="0">-- Seleccione --</option>
								<option value="1">SUBIR BALANCE</option>
								<option value="2">BAJAR BALANCE</option>
							</select>
						</div>
					</div>
				</div>
				<div class="row" id="mb_modal_add_reason_new">
					<label for="mb_modal_nombre_reason" class="col-sm-3 control-label" style="text-align: right;">
						Nombre
					</label>
					<div class="col-sm-9" style="margin-bottom: 10px;">
						<input type="text" class="form-control"
							id="mb_modal_nombre_reason"
							style="color: black;">
					</div>
				</div>
				<div class="row" style="display: none;">
					<div class="form-group">
						<div class="col-sm-12" style="margin-bottom: 10px;">
							<table id="mb_modal_table_reasons_edit_balance" class="table table-hover">
								<thead>
									<th>Id</th>
									<th>Nombre</th>
									<th>Estado</th>
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
				<button type="button" class=" col-sm-3 btn btn-primary pull-right" id="mb_modal_add_reason">
					<i class="fa fa-save"></i> <b>Guardar</b>
				</button>
			</div>
		</div>
	  </div>
	</div>


	
	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<!-- MODAL SUBIR Y BAJAR BALANCE -->
	<!--**************************************************************************************************-->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="modal_sbBal"   role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel" id="modal_sbBal_titulo">BALANCE</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="form-group">
						<label for="modal_recargaweb_monto" class="col-sm-4 control-label" style="text-align: right;"><spam style="color: red; font-weight: bold;">(*)</spam> Tipo de Balance:</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<select class="form-control" id="modal_sbBal_balance_tipo" 
								style="border-radius: 5px;border: 1px solid #aaa;color: black;height: 40px;font-size: 16px;">
								<option value="0">-- Seleccione --</option>
								<option value="4">Balance NO RETIRABLE</option>
								<option value="5">Balance RETIRABLE</option>
								<!-- <?php if(in_array("switch_dinero_at", $usuario_permisos[$menu_id])) { ?>
								<option value="6">Dinero AT</option>
								<?php } ?> -->
							</select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label for="modal_recargaweb_monto" class="col-sm-4 control-label" style="text-align: right;"><spam style="color: red; font-weight: bold;">(*)</spam> Motivo:</label>
						<div class="col-sm-8" style="margin-bottom: 10px; ">
							<select
							class="form-control" style="width: 100%; font-size: 15px;"
									data-live-search="true" 
									data-col="modal_sbBal_motivo_balance_id" 
									data-table="tbl_televentas_motivo_balances"
									name="modal_sbBal_motivo_balance_id" 
									id="modal_sbBal_motivo_balance_id" 
									title="Seleccione una opción">
								</select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="form-group">
						<label for="modal_sbBal_juego_motivo_balance_lb" class="col-sm-4 control-label" style="text-align: right;"><spam style="color: red; font-weight: bold;">(*)</spam> Juego:</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
						<select
							class="form-control" style="width: 100%;font-size: 15px;"
									data-live-search="true" 
									data-col="modal_sbBal_juego_motivo_balance" 
									data-table="tbl_televentas_tipo_juego"
									name="modal_sbBal_juego_motivo_balance" 
									id="modal_sbBal_juego_motivo_balance" 
									title="Seleccione una opción">
								</select>
						</div>
					</div>
				</div>


				<div class="row">
					<div class="form-group">
						<label for="modal_sbBal_idtrans_motivo_balance_lb" class="col-sm-4 control-label" style="text-align: right;"><spam style="color: red; font-weight: bold;">(*)</spam> ID Transacción:</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
						<input type="text" class="form-control" id="modal_sbBal_idtrans" placeholder="---" autocomplete="off" maxlength="20"
							style="color: black;">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="form-group">
						<label for="modal_cajero_motivo_balance_lb" class="col-sm-4 control-label" style="text-align: right;"><spam style="color: red; font-weight: bold;">(*)</spam> Cajero:</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
						<select
							class="form-control" style="width: 100%;font-size: 15px;"
									data-live-search="true" 
									data-col="modal_sbBal_cajero_motivo_balance" 
									data-table="tbl_personal_apt"
									name="modal_sbBal_cajero_motivo_balance" 
									id="modal_sbBal_cajero_motivo_balance" 
									title="Seleccione una opción">
								</select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="form-group">
						<label for="modal_supervisor_motivo_balance_lb" class="col-sm-4 control-label" style="text-align: right;"><spam style="color: red; font-weight: bold;">(*)</spam> Supervisor:</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
						<select
							class="form-control" style="width: 100%;font-size: 15px;"
									data-live-search="true" 
									data-col="modal_sbBal_supervisor_motivo_balance" 
									data-table="tbl_personal_apt"
									name="modal_sbBal_supervisor_motivo_balance" 
									id="modal_sbBal_supervisor_motivo_balance" 
									title="Seleccione una opción">
								</select>
						</div>
					</div>
				</div>


				<div class="row">
					<div class="form-group">
						<label for="modal_recargaweb_monto" class="col-sm-4 control-label" style="text-align: right;">Balance Actual S/</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<input type="text" class="form-control" id="modal_sbBal_balance_actual" placeholder="0.00" disabled 
							style="color: black;background-color: #659de0;">
						</div>
					</div>
				</div>
				<div class="row" id="modal_sbBal_balance_tipo_actual_div" hidden>
					<div class="form-group">
						<label for="modal_recargaweb_monto" class="col-sm-4 control-label" id="modal_sbBal_balance_tipo_actual_texto"
						 style="text-align: right;">Balance No Retirable Actual S/</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<input type="text" class="form-control" id="modal_sbBal_balance_tipo_actual" placeholder="0.00" disabled 
							style="color: black;background-color: #ffff7f;">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label for="modal_recargaweb_monto" class="col-sm-4 control-label" style="text-align: right;" id="modal_sbBal_monto_texto"><spam style="color: red; font-weight: bold;">(*)</spam> Balance a restar S/</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<input type="text" class="form-control" id="modal_sbBal_monto" placeholder="0.00" autocomplete="off" 
							style="color: black;">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label for="modal_recargaweb_monto" class="col-sm-4 control-label" style="text-align: right;">Nuevo Balance S/</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<input type="text" class="form-control" id="modal_sbBal_balance_nuevo" placeholder="0.00" disabled 
							style="color: black;background-color: #659de0;">
						</div>
					</div>
				</div>
				<div class="row" id="modal_sbBal_balance_tipo_nuevo_div" hidden>
					<div class="form-group">
						<label for="modal_recargaweb_monto" class="col-sm-4 control-label" style="text-align: right;" id="modal_sbBal_balance_tipo_nuevo_texto">Nuevo Balance No Retirable S/</label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<input type="text" class="form-control" id="modal_sbBal_balance_tipo_nuevo" placeholder="0.00" disabled 
							style="color: black;background-color: #ffff7f;">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						<label for="modal_sbBal_observacion" class="col-sm-4 control-label" style="text-align: right;"><spam style="color: red; font-weight: bold;">(*)</spam> Observación: </label>
						<div class="col-sm-8" style="margin-bottom: 10px;">
							<textarea class="form-control" id="modal_sbBal_observacion" rows="2" style="color: black;"></textarea>
						</div>
					</div>
				</div>
			</div>
			<span class="campo_obligatorio">Los Campos con (*) son obligatorios</span>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
				<button type="button" class="btn btn-primary pull-right" id="modal_sbBal_btn_guardar" onclick="sb_balance()">
					<i class="fa fa-save"></i> <b id="modal_sbBal_btn_guardar_texto">RECARGAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>

  
 
</div>

<?php
}
?>