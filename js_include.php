<?php
$jsArray = [];

$jsArray[] = "js/jquery-2.2.4.min.js";
if ($login) {
	$jsArray[] = "js/jquery.cookie.js";
	$jsArray[] = "components/jquery-ui/jquery-ui.min.js";
	$jsArray[] = "js/datepicker-es.js";
	$jsArray[] = "js/bootstrap.min.js";
	$jsArray[] = "js/moment.js";
	$jsArray[] = "js/jquery.mCustomScrollbar.concat.min.js";
	// $jsArray[]="js/jquery.stellar.min.js";
	// $jsArray[]="js/jquery.magnific-popup.min.js";
	// $jsArray[]="js/pnotify.custom.min.js";
	$jsArray[] = "js/owl.carousel.min.js";
	$jsArray[] = "js/jquery.validate.min.js";
	$jsArray[] = "js/messages_es.js";
	//$jsArray[]="js/jquery.animateNumber.min.js";
	if ($sec_id != 'mepa' && $sub_sec_id != 'reporte_contabilidad') {
		$jsArray[] = "js/Chart.min.js";
	}
	$jsArray[] = "js/accent_map.js";
	$jsArray[] = "js/sweetalert.min.js";
	$jsArray[] = "js/bootstrap-multiselect.js";
	$jsArray[] = "js/canvas/canvas2image.js";
	//$jsArray[]="js/circle-progress.min.js";
	$jsArray[] = "components/jstree/jstree.min.js";

	$jsArray[] = "js/json2.js";
	$jsArray[] = "js/moment-with-locales.js";
	$jsArray[] = "js/jquery-dateFormat.js";
	// $jsArray[]="js/jquery.number.js"
	//$jsArray[]="js/fullcalendar.min.js";
	$jsArray[] = "js/jquery.dataTables.min.js";
	$jsArray[] = "js/datetime.js";
	$jsArray[] = "js/SimpleAjaxUploader.js";
	$jsArray[] = "js/simpleUpload.js";
	$jsArray[] = "js/PasswordValidator.js";
	//$jsArray[]="js/bootstrap-datepicker.js";
	//$jsArray[]="js/bootstrap-timepicker.js";
	$jsArray[] = "js/bootstrap-toggle.min.js";
	$jsArray[] = "js/jquery.treegrid.js";
	$jsArray[] = "js/php.js";
	$jsArray[] = "js/general.js";
	$jsArray[] = "js/demo.js";
	$jsArray[] = "js/jquery.json-viewer.js";
	$jsArray[] = "js/sec_afiliarse.js";
	$jsArray[] = "js/sec_clientes.js";
	$jsArray[] = "js/sec_contratos.js";

	$jsArray[] = "js/sec_home.js";
	$jsArray[] = "js/sec_recaudacion_pagos_manuales.js";
	$jsArray[] = "js/sec_recaudacion_liquidaciones.js";
	$jsArray[] = "js/sec_recaudaciones_liquidacion_productos.js";
	$jsArray[] = "js/sec_recaudacion_transacciones_bancarias.js";
	$jsArray[] = "js/sec_recaudacion_fraccionamiento.js";
	$jsArray[] = "js/sec_recaudacion_procesos.js";
	$jsArray[] = "js/sec_recaudacion.js";

	$jsArray[] = "js/sec_reportes.js";
	$jsArray[] = "js/sec_reportes_caja_sistema.js";
	$jsArray[] = "js/sec_reportes_cajas_eliminadas.js";
	$jsArray[] = "js/sec_reportes_solicitudes.js";
	$jsArray[] = "js/sec_reportes_kasnet.js";
	$jsArray[] = "js/sec_reportes_disashop.js";
	$jsArray[] = "js/sec_reportes_locales.js";
	$jsArray[] = "js/sec_reportes_historial_monto.js";
	$jsArray[] = "js/sec_reportes_web_total.js";
	$jsArray[] = "js/sec_reportes_graficos_dinero_apostado.js";
	$jsArray[] = "js/sec_reportes_resultados_apuestas.js";
	$jsArray[] = "js/sec_reportes_tickets.js";
	$jsArray[] = "js/sec_reportes_tickets_por_pagar.js";
	$jsArray[] = "js/sec_reportes_cobranzas.js";
	$jsArray[] = "js/sec_reportes_resumen_apostado.js";
	$jsArray[] = "js/sec_reportes_pagados_en_de_otras_tiendas.js";
	$jsArray[] = "js/sec_reportes_en_de.js";
	$jsArray[] = "js/sec_reportes_en_de_torito.js";
	$jsArray[] = "js/sec_reportes_bet_bar.js";
	$jsArray[] = "js/sec_reportes_venta_general_tienda.js";
	$jsArray[] = "js/sec_reportes_resumen_dia.js";
	$jsArray[] = "js/sec_reportes_participaciones.js";
	$jsArray[] = "js/sec_reportes_comisiones.js";
	$jsArray[] = "js/sec_reportes_saldos.js";
	$jsArray[] = "js/sec_reportes_emailage.js";
	$jsArray[] = "js/sec_reportes_prevencion_fraude.js";
	$jsArray[] = "js/sec_reportes_conf_reportes.js";
	$jsArray[] = "js/sec_reportes_balance.js";
	$jsArray[] = "js/sec_reportes_tickets_pagados.js";
	$jsArray[] = "js/sec_reportes_recargas_web.js";
	//$jsArray[]="js/sec_reportes_pbet.js";
	$jsArray[] = "js/sec_reportes_cuadre_balance.js";
	$jsArray[] = "js/sec_reportes_centro_de_costos.js";
	$jsArray[] = "js/sec_reportes_vigencia.js";
	$jsArray[] = "js/sec_reportes_nif16_cambio_moneda.js";
	$jsArray[] = "js/sec_reportes_nif16_terminacion_renovacion.js";
	$jsArray[] = "js/sec_reportes_nif16_bdt.js";
	$jsArray[] = "js/sec_reportes_correcciones.js";
	$jsArray[] = "js/sec_reportes_contratos.js";
	$jsArray[] = "js/sec_reportes_apuestas_deportivas.js";
	$jsArray[] = "js/sec_reportes_ventas_retail.js";

	$jsArray[] = "js/sec_cobranzas.js";
	$jsArray[] = "js/sec_cobranzas_estados_de_cuenta.js";
	$jsArray[] = "js/sec_cobranzas_detalle_estado_cuenta.js";

	// $jsArray[]="js/sec_cobranzas_estados_de_cuenta_diferencia.js";

	$jsArray[] = "js/sec_comercial.js";
	$jsArray[] = "js/sec_comercial_reporte_total_pais.js";
	$jsArray[] = "js/sec_comercial_reporte_acumulado_mensual.js";
	$jsArray[] = "js/sec_comercial_reporte_total_productos.js";
	$jsArray[] = "js/sec_comercial_reporte_total_por_zona.js";
	$jsArray[] = "js/sec_comercial_reporte_admin_productos.js";

	$jsArray[] = "js/sec_consultas.js";
	$jsArray[] = "js/sec_consultas_dni.js";
	$jsArray[] = "js/sec_consultas_kasnet_directorio_recaudos.js";

	$jsArray[] = "js/sec_soporte.js";
	$jsArray[] = "js/sec_soporte_retiros.js";
	$jsArray[] = "js/sec_soporte_alerta_goldenrace.js";
	$jsArray[] = "js/sec_soporte_alerta_terminal.js";
	$jsArray[] = "js/sec_soporte_alerta_apuestas_deportivas.js";
	$jsArray[] = "js/sec_soporte_alerta_deposit_web.js";
	$jsArray[] = "js/sec_soporte_alerta_betshop.js";
	$jsArray[] = "js/sec_soporte_alerta_betshop_retail.js";
	$jsArray[] = "js/sec_soporte_alerta_simulcast_retail.js";
	$jsArray[] = "js/sec_soporte_alerta_simulcast_agente.js";
	$jsArray[] = "js/sec_soporte_alerta_terminal_cash_in.js";
	$jsArray[] = "js/sec_soporte_alerta_caja_cash_in.js";

	$jsArray[] = "js/sec_soporte_proveedor.js";

	$jsArray[] = "js/sec_garantia_locales.js";

	$jsArray[] = "js/sec_dni_2_factores.js";

	$jsArray[] = "js/sec_caja.js";

	$jsArray[] = "js/sec_bingotorito.js";
	$jsArray[] = "js/sec_archivos.js";

	$jsArray[] = "js/sec_destinatario.js";

	$jsArray[] = "js/sec_locales.js";
	$jsArray[] = "js/sec_horarios.js";
	$jsArray[] = "js/sec_sorteos.js";

	// usuarios
	$jsArray[] = "js/usuario/sec_usuarios.js";
	$jsArray[] = "js/usuario/sec_usuarios_permisos.js";


	// personal
	$jsArray[] = "js/personal/sec_personal.js";
	// bancos
	$jsArray[] = "js/banco/sec_bancos.js";
	// general
	$jsArray[] = "js/sec_mantenimientos.js";



	$jsArray[] = "js/sec_caja_hermeticase.js";
	$jsArray[] = "js/sec_incidencias_ca.js";
	$jsArray[] = "js/sec_solicitud_mantenimiento.js";
	$jsArray[] = "js/sec_reportes_solicitud_mantenimiento.js";
	$jsArray[] = "js/sec_aprobacion_registros.js";
	$jsArray[] = "js/sec_adm_tipo_cambio.js";
	$jsArray[] = "js/sec_adm_indice_inflacion.js";
	$jsArray[] = "js/sec_contrato_mantenimiento.js";
	$jsArray[] = "js/sec_adm_feriados.js";
	$jsArray[] = "js/sec_recaudacion_liquidaciones_agente.js";
	$jsArray[] = "js/sec_recaudacion_liquidaciones_cont_agentes.js";
	$jsArray[] = "js/sec_autollenado_tambo.js";
	$jsArray[] = "js/sec_versiones.js";
	$jsArray[] = "js/sec_mantenimientos_billetera_tls.js";
	$jsArray[] = "js/sec_adm_parametros_generales.js";
	$jsArray[] = "js/sec_adm_modificaciones.js";

	$jsArray[] = "js/custom.js";
	$jsArray[] = "js/select2.full.min.js";
	$jsArray[] = "js/dataTables.fixedHeader.min.js";
	$jsArray[] = "js/dataTables.bootstrap.min.js";
	$jsArray[] = "js/dataTables.responsive.min.js";
	$jsArray[] = "js/dataTables.rowsGroup.js";
	$jsArray[] = "js/dataTables.fixedColumns.min.js";
	$jsArray[] = "js/dataTables.keyTable.min.js";
	$jsArray[] = "js/dataTables.buttons.min.js";
	$jsArray[] = "js/dataTables.select.min.js";
	// $jsArray[]="js/dataTables.checkboxes.min.js";
	$jsArray[] = "js/buttons.bootstrap.min.js";
	$jsArray[] = "js/buttons.colVis.min.js";
	$jsArray[] = "js/buttons.flash.min.js";
	$jsArray[] = "js/jszip.min.js";
	$jsArray[] = "js/buttons.html5.min.js";
	$jsArray[] = "js/buttons.print.min.js";
	$jsArray[] = "js/sum.js";
	$jsArray[] = "js/bootstrap-timepicker.js";
	$jsArray[] = "js/jquery.timepicker.min.js";
	$jsArray[] = "js/bootstrap-select.js";
	$jsArray[] = "js/bootstrap-datetimepicker.js";
	$jsArray[] = "js/bootstrap-datetimepicker.min.js";
	$jsArray[] = "js/xlsx.full.min.js";
	$jsArray[] = "js/Blob.js";
	$jsArray[] = "js/FileSaver.js";
	$jsArray[] = "js/jquery.base64.js";
	$jsArray[] = "js/jquery.bootstrap-duallistbox.js";
	//$jsArray[]="js/d3.min.js";
	$jsArray[] = "js/jquery.floatThead.js";
	$jsArray[] = "js/bootstrap-treeview.min.js";
	//$jsArray[]="js/jquery.super-table.js";
	$jsArray[] = "js/jquery.minicolors.js";
	$jsArray[] = "js/fusioncharts.js";
	$jsArray[] = "js/fusioncharts.charts.js";
	$jsArray[] = "js/jquery.simplePagination.js";
	$jsArray[] = "js/newFixHeader.js";
	$jsArray[] = "js/sidebar.js";

	$jsArray[] = "js/sec_registro_foto_premios.js";
	$jsArray[] = "js/sec_reporte_premios.js";
	$jsArray[] = "js/sec_caja_clientes_depositos.js";
	$jsArray[] = "js/sec_televentas.js";
	$jsArray[] = "js/sec_televentas_historial_cliente.js";
	$jsArray[] = "js/sec_televentas_depositos.js";
	$jsArray[] = "js/sec_televentas_pagador.js";
	$jsArray[] = "js/sec_televentas_abonos.js";
	$jsArray[] = "js/sec_televentas_abonos_pendientes.js";
	$jsArray[] = "js/sec_televentas_devoluciones.js";
	$jsArray[] = "js/sec_televentas_cuentas_at.js";
	$jsArray[] = "js/sec_reportes_televentas_pagos.js";
	$jsArray[] = "js/sec_mantenimientos_programacion_pagador.js";
	//		$jsArray[]="js/sec_caja_clientes_depositos_val.js";
	$jsArray[] = "js/sec_reportes_concar.js";
	$jsArray[] = "js/sec_reportes_del_dep.js";
	$jsArray[] = "js/sec_reportes_tercero_autorizado.js";
	$jsArray[] = "js/sec_reportes_televentas.js";
	$jsArray[] = "js/sec_reportes_hist_fusion.js";
	$jsArray[] = "js/sec_reportes_televentas_balance.js";
	$jsArray[] = "js/sec_reportes_televentas_calimaco.js";	//Reporte Calimaco
	$jsArray[] = "js/sec_reportes_televentas_clientes.js";
	$jsArray[] = "js/sec_reportes_televentas_apuestas_altenar.js";
	$jsArray[] = "js/sec_reportes_torito.js";
	$jsArray[] = "js/sec_reportes_info_agentes.js";
	$jsArray[] = "js/sec_reportes_agentes.js";
	$jsArray[] = "js/sec_reportes_servicio_publico.js"; // CONTRATOS
	$jsArray[] = "js/sec_torito.js";
	$jsArray[] = "js/sec_adm_periodo_liquidacion.js";
	$jsArray[] = "js/sec_comercial_zona_meta.js";
	$jsArray[] = "js/sec_cron_report_tls.js";
	$jsArray[] = "js/sec_resumen_apuestas_aterax.js";

	$jsArray[] = "js/sec_reportes_listado_vales.js"; // VALES DE DESCUENTO
	$jsArray[] = "js/sec_reportes_vales_gdt.js"; // VALES DE DESCUENTO
	$jsArray[] = "js/sec_reportes_vales_gdt_detallado.js"; // VALES DE DESCUENTO
	$jsArray[] = "js/sec_reportes_clientes_online_jv.js";
	$jsArray[] = "js/sec_reportes_billetera_juegos_virtuales.js";
	$jsArray[] = "js/sec_reportes_billetera_det_juegos_virtuales.js";
	$jsArray[] = "js/sec_reportes_etiquetas.js";
	$jsArray[] = "js/sec_reportes_sorteo_nuevos_registros.js";

	$jsArray[] = "js/sec_dev.js";  // VIEW LOG APIS
	$jsArray[] = "js/sec_dev_view_log_apis.js"; // VIEW LOG APIS

	$jsArray[] = "js/sec_kasnet.js";
	$jsArray[] = "js/sec_kasnet_mantenimiento.js";
	$jsArray[] = "js/sec_kasnet_mantenimiento_operacion.js";


	//registo_foto_jackpots()
	//$jsArray[]="js/printThis.js";
	$jsArray[] = "js/jspdf.min.js";
	$jsArray[] = "js/html2pdf.bundle.min.js";
	$jsArray[] = "js/qrcode.min.js";
	// $jsArray[]="js/zendesk.js";
	// $jsArray[]="js/freshchat.js";
	$jsArray[] = "js/anuncios.js";
	$jsArray[] = "js/dataTables.checkboxes.min.js";
	$jsArray[] = "js/dataTables.colReorder.min.js";
	$jsArray[] = "js/sec_mantenimientos_etiquetas_tlv.js";
	$jsArray[] = "js/sec_soporte_cliente_web.js";
	$jsArray[] = "js/sec_tesoreria_retiros_web.js";
	$jsArray[] = "js/sec_tesoreria.js";

	$jsArray[] = "js/sec_reportes_contables.js";

	$jsArray[] = "js/sec_saldo_web.js";
	$jsArray[] = "js/sec_saldo_tls.js";
	$jsArray[] = "js/sec_reportes_saldo_web.js";
	$jsArray[] = "js/sec_reportes_retiros_web.js";
	$jsArray[] = "js/sec_reportes_saldo_tls.js";
	$jsArray[] = "js/sec_reportes_retiros_tls.js";
	if ($sec_id === 'recargas_web') {
		$jsArray[] = "js/sec_recargas_web.js";
	}
	if ($sec_id === 'reporte_transac_web_cajas_elimn') {
		$jsArray[] = "js/sec_reporte_transac_web_cajas_elimn.js";
	}
	if ($sec_id === 'consolidado_free_games') {
		$jsArray[] = "js/sec_consolidado_free_games.js";
	}
	if ($sec_id === 'consolidado_gastos') {
		$jsArray[] = "js/sec_consolidado_gastos.js";
	}
	if ($sec_id === 'reportes' && $sub_sec_id === 'api_torito') {
		$jsArray[] = "js/sec_reportes_api_torito.js";
	}
	if ($sec_id === 'reportes' && $sub_sec_id === 'torito') {
		$jsArray[] = "js/global_functions.js";
	}
	if ($sec_id === 'manual_usuarios') {
		$jsArray[] = "js/sec_manual_usuarios.js";
	}
	if ($sec_id === 'logs') {
		$jsArray[] = "js/sec_logs.js";
	}
	if ($sec_id === 'locales') {
		$jsArray[] = "js/sec_locales_supplier.js";
		$jsArray[] = "js/global_functions.js";
	}
	if ($sec_id === 'contrato') {
		$jsArray[] = "js/global_functions.js";
	}
	if ($sec_id === 'adm_depositos_web') {
		$jsArray[] = "js/sec_adm_depositos_web.js";
	}

	if ($sec_id === 'adm_saldo_tls') {
		$jsArray[] = "js/sec_adm_saldo_tls.js";
	}

	if ($sec_id === 'reportes' && $sub_sec_id === 'ende') {
		//$jsArray[]="https://code.jquery.com/jquery-1.7.0.js";
		//$jsArray[]="https://cdn.datatables.net/2.0.2/js/dataTables.js";
		$jsArray[] = "js/dataTables.rowGroup.js";
		$jsArray[] = "js/exceljs.min.js";

		//$jsArray[]="js/dataTables.rowsGroup.js";
		$jsArray[] = "js/sec_reportes_ende.js";
	}
	if ($sec_id === 'soporte' && $sub_sec_id === 'alert_caja_web_deposit') {
		$jsArray[] = "js/sec_soporte_alert_caja_web_deposit.js";
	}
	if (
		($sec_id === 'adm_parametros_generales' && $sub_sec_id === 'parametros_generales')
		|| ($sec_id === 'reportes' && $sub_sec_id === 'kurax_ende')
	) {
		$jsArray[] = "js/global_functions.js";
	}

	if ($sec_id === 'reportes' && $sub_sec_id === 'login_log') {
		$jsArray[] = "js/sec_reportes_login_log.js";
	}
	if ($sec_id === 'reportes' && $sub_sec_id === 'kurax_ende') {
		$jsArray[] = "js/sec_reportes_kurax_ende.js";
	}
	if ($sec_id === 'usuarios_por_acceso') {
		$jsArray[] = "js/sec_usuarios_por_acceso.js";
	}
	// INICIO MESA DE PARTES - CAJA CHICA
	$jsArray[] = "js/global_functions.js";
	$jsArray[] = "js/sec_locales_terminal.js";
	$jsArray[] = "js/sec_mepa.js";
	$jsArray[] = "js/sec_mepa_asignacion.js";
	$jsArray[] = "js/sec_mepa_caja_chica.js";
	$jsArray[] = "js/sec_mepa_detalle_solicitud_asignacion.js";
	$jsArray[] = "js/sec_mepa_solicitud_asignacion.js";
	$jsArray[] = "js/sec_mepa_solicitud_liquidacion.js";
	$jsArray[] = "js/sec_mepa_solicitud_rendicion_caja_chica.js";
	$jsArray[] = "js/sec_mepa_tesoreria.js";
	$jsArray[] = "js/sec_mepa_tesoreria_atencion.js";
	$jsArray[] = "js/sec_mepa_movilidad.js";
	$jsArray[] = "js/sec_mepa_movilidad_detalle.js";
	$jsArray[] = "js/sec_mepa_atencion_liquidacion.js";
	$jsArray[] = "js/sec_mepa_detalle_atencion_liquidacion.js";
	$jsArray[] = "js/sec_mepa_contabilidad.js";
	$jsArray[] = "js/sec_mepa_reporte_contabilidad.js";
	$jsArray[] = "js/sec_mepa_detalle_tesoreria_programacion.js";
	$jsArray[] = "js/sec_mepa_reporte_tesoreria.js";
	$jsArray[] = "js/sec_mepa_zona_asignacion.js";
	$jsArray[] = "js/sec_mepa_cajas_chicas_rechazadas.js";
	$jsArray[] = "js/sec_mepa_movilidad_volante_detalle.js";
	$jsArray[] = "js/sec_mepa_aumento_reduccion_asignacion.js";
	$jsArray[] = "js/sec_mepa_detalle_solicitud_aumento.js";
	$jsArray[] = "js/sec_mepa_devolucion_asignacion.js";
	$jsArray[] = "js/sec_mepa_cuenta_bancaria.js";
	$jsArray[] = "js/sec_mepa_migrar_dato.js";
	$jsArray[] = "js/sec_mepa_seguimiento.js";
	$jsArray[] = "js/sec_mepa_asignacion_usuario.js";
	$jsArray[] = "js/sec_mepa_mantenimiento.js";
	$jsArray[] = "js/sec_mepa_mantenimiento_correlativo.js";
	$jsArray[] = "js/sec_mepa_mantenimiento_zona.js";
	$jsArray[] = "js/sec_mepa_mantenimiento_cuenta_contable.js";
	$jsArray[] = "js/sec_conciliacion.js";
	$jsArray[] = "js/sec_conciliacion_venta.js";
	$jsArray[] = "js/sec_conciliacion_anulacion.js";
	$jsArray[] = "js/sec_conciliacion_devolucion.js";
	$jsArray[] = "js/sec_conciliacion_liquidacion.js";
	$jsArray[] = "js/sec_conciliacion_recaudacion.js";
	$jsArray[] = "js/sec_conciliacion_mantenimiento.js";
	$jsArray[] = "js/sec_conciliacion_mantenimiento_proveedor.js";
	$jsArray[] = "js/sec_conciliacion_mantenimiento_metodo.js";
	$jsArray[] = "js/sec_conciliacion_mantenimiento_opcion.js";
	$jsArray[] = "js/sec_conciliacion_mantenimiento_tipo_cambio.js";
	$jsArray[] = "js/sec_conciliacion_mantenimiento_correo.js";
	$jsArray[] = "js/sec_conciliacion_reporte.js";
	$jsArray[] = "js/sec_conciliacion_reporte_conciliacion.js";
	$jsArray[] = "js/sec_conciliacion_reporte_liquidacion.js";
	$jsArray[] = "js/sec_conciliacion_reporte_anulacion.js";
	$jsArray[] = "js/sec_conciliacion_reporte_devolucion.js";
	$jsArray[] = "js/sec_conciliacion_reporte_comision.js";
	$jsArray[] = "js/sec_conciliacion_reporte_recaudacion.js";

	$jsArray[] = "js/sec_mepa_mantenimiento_correo.js";


	// FIN MESA DE PARTES - CAJA CHICA

	// INICIO PRESTAMO SLOT - TIENDAS, BOVEDAD
	$jsArray[] = "js/sec_prestamo.js";
	$jsArray[] = "js/sec_prestamo_slot.js";
	$jsArray[] = "js/sec_prestamo_slot_detalle_solicitud.js";
	$jsArray[] = "js/sec_prestamo_boveda.js";
	$jsArray[] = "js/sec_prestamo_boveda_detalle_solicitud.js";
	$jsArray[] = "js/sec_prestamo_tesoreria.js";
	$jsArray[] = "js/sec_prestamo_tesoreria_atencion.js";
	$jsArray[] = "js/sec_prestamo_detalle_tesoreria_programacion.js";
	$jsArray[] = "js/sec_prestamo_configuracion.js";
	// FIN PRESTAMO SLOT - TIENDAS, BOVEDAD

	//INICIO WALLET AUTOSERVICIO
	$jsArray[] = "js/sec_terminal_auto_servicio.js";
	$jsArray[] = "js/sec_terminal_auto_servicio_reporte.js";
	$jsArray[] = "js/sec_terminal_auto_servicio_reporte_transacciones.js";
	//FIN WALLET AUTOSERVICIO


	//INICIO VALES DE DESCUENTO
	$jsArray[] = "js/sec_vale.js";
	$jsArray[] = "js/sec_vale_nuevo.js";
	$jsArray[] = "js/sec_vale_solicitud.js";
	$jsArray[] = "js/sec_vale_control_interno.js";
	$jsArray[] = "js/sec_vale_mantenimiento.js";
	$jsArray[] = "js/sec_vale_motivo.js";
	$jsArray[] = "js/sec_vale_parametros_fraccionamiento.js";
	$jsArray[] = "js/sec_vale_sincronizacion.js";
	$jsArray[] = "js/sec_vale_fraccionamiento_manual.js";
	$jsArray[] = "js/sec_vale_parametro_general.js";
	//FIN VALES DE DESCUENTO

	//INICIO DE HERRAMIENTAS TI
	$jsArray[] = "js/sec_herramientas_ti.js";
	$jsArray[] = "js/sec_herramientas_ti_mantenimiento.js";
	$jsArray[] = "js/sec_herramientas_ti_proceso.js";
	//FIN DE HERRAMIENTAS TI

	$jsArray[] = "js/sec_adm_login_ip_whitelist.js";
	$jsArray[] = "js/sec_consolidado_agentes.js";
	$jsArray[] = "js/sec_caja_resumen_agentes.js";
	$jsArray[] = "js/colorpicker.js";
	$jsArray[] = "js/sec_derivacion_tecnico.js";

	$jsArray[] = "js/sec_servicio_tecnico_atencion.js";
	$jsArray[] = "js/sec_servicio_tecnico_derivacion.js";
	$jsArray[] = "js/sec_servicio_tecnico_observado.js";
	$jsArray[] = "js/sec_servicio_tecnico_reporte.js";
	$jsArray[] = "js/sec_servicio_tecnico.js";

	$jsArray[] = "js/sec_extorno.js";

	$jsArray[] = "js/sec_locales_aplicativos.js";

	// INICIO: MANTENIMIENTO

	$jsArray[] = "js/sec_mantenimiento.js";
	$jsArray[] = "js/sec_mantenimiento_num_cuenta.js";
	$jsArray[] = "js/sec_mantenimiento_razon_social.js";
	$jsArray[] = "js/sec_mantenimiento_producto.js";
	$jsArray[] = "js/sec_mantenimiento_canal_venta.js";
	$jsArray[] = "js/sec_mantenimiento_canal_caja.js";

	// FIN: MANTENIMIENTO

	// INICIO: SOLICITUD ESTIMACION

	$jsArray[] = "js/sec_solicitud_estimacion.js";

	// FIN: SOLICITUD ESTIMACION

	//INICIO KURAX
	$jsArray[] = "js/sec_kurax.js";
	$jsArray[] = "js/sec_kurax_transacciones_consolidado.js";
	//FIN KURAX

	//INICIO BILLETERA DIGITAL
	if ($sec_id === 'billetera') {
		$jsArray[] = "js/sec_billetera.js";
		$jsArray[] = "js/sec_billetera_validar.js";
		$jsArray[] = "js/sec_billetera_reporte.js";
		$jsArray[] = "js/sec_billetera_mantenimiento.js";
		if ($sub_sec_id === 'transaccion') {
			$jsArray[] = "js/sec_billetera_transaccion.js";
		}
	}
	//FIN BILLETERA DIGITAL

	/*
		if($sec_id === 'bingotorito'){
			$jsArray[]="js/sec_bingotorito.js";
		}
		*/
	//INICIO REPORTES
	if ($sec_id === 'reportes') {
		if ($sub_sec_id === 'precierre') {
			$jsArray[] = "js/sec_reportes_precierre.js";
		}
	}
	//FIN REPORTES

} else {
	$jsArray[] = "js/login.js";
}
if ($login) {
	if (false) {
?><script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=08f58c2f-b8ce-499f-a7f3-486c835e7773"> </script><?php
																															}
																														}
																														foreach ($jsArray as $key => $value) {
																																?><script type="text/javascript" src="<?php echo $value . "?" . $js_cache; ?>"></script><?php
																																																					}

																																																					// INICIO CONTRATOS
																																																					$js_cache_contratos = '20220101080101';

																																																					$ultimo_cambio_contratos_command = "SELECT updated_at FROM tbl_modificaciones WHERE modulo = 'Contratos' AND status = 1 ORDER BY updated_at DESC LIMIT 1";
																																																					$ultimo_cambio_contratos_query = $mysqli->query($ultimo_cambio_contratos_command);

																																																					if ($mysqli->error) {
																																																						print_r($mysqli->error);
																																																						echo "\n";
																																																						echo $ultimo_cambio_contratos_command;
																																																					} else {
																																																						$ultimo_cambio_contratos_row = $ultimo_cambio_contratos_query->fetch_assoc();
																																																						$js_cache_contratos = preg_replace('/\W/', '', $ultimo_cambio_contratos_row["updated_at"]);
																																																					}

																																																					$jsArray_contratos = [];

																																																					$jsArray_contratos[] = "js/jquery.mask.min.js";
																																																					$jsArray_contratos[] = "js/viewer.min.js";
																																																					// $jsArray_contratos[]="js/jquery.dataTables.min.js";
																																																					// $jsArray_contratos[]="js/dataTables.select.min.js";
																																																					$jsArray_contratos[] = "js/alertify.min.js";
																																																					$jsArray_contratos[] = "js/sec_contrato.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_agente.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_solicitud.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_solicitud.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_solicitudv2.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_solicitud_mandato.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_reportes_contabilidad.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_locales.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_locaciones.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_mandatos.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_mutuodinero.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_licencias.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_servicio.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_licenciafile.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_proveedor.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_arrendamiento.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_agente.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_acuerdo_confidencialidad.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_tesoreria.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nueva_programacion.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_procesar_programacion.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_programacion.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_servicio_publico.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_servicio_publico_tesoreria.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_servicio_publico.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_asiento_contable_servicio_publico.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_interno.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_solicitud_interno.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_solicitud_locacion_servicio.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_solicitud_mutuo_dinero.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_interno.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_interno.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_acuerdo_confidencialidad.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_acuerdo_confidencialidad.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_agente.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_proveedor.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_proveedor.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_contrato_agente.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_contrato_interno.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_acuerdo_confidencialidad.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_provision.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_arrendamiento.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_locacion_servicio.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_mandato.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_adenda_mutuodinero.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_nuevo_resolucion_contrato.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_responsables_de_area.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_directores_de_area.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_contabilidadProvisiones.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_mantenimiento_correo_metodo.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_mantenimiento_notificacion_contrato.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_mantenimiento_correo_cargo.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_mantenimiento_correlativo.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_mantenimiento_servicio_publico.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_arrendamiento.js";
																																																					$jsArray_contratos[] = "js/NumeroALetras.js";
																																																					$jsArray_contratos[] = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js";
																																																					$jsArray_contratos[] = "https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.4.0/purify.min.js";
																																																					// <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
																																																					// <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.4.0/purify.min.js"></script>
																																																					$jsArray_contratos[] = "js/sec_configuracion_detalle_formato.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_mutuo_dinero.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_mandato.js";
																																																					$jsArray_contratos[] = "js/sec_contrato_detalle_adenda_locacion.js";

																																																					foreach ($jsArray_contratos as $key => $value) {
																																																						?><script type="text/javascript" src="<?php echo $value . "?version=" . $js_cache_contratos; ?>"></script><?php
																																																																																}
																																																																																// FIN CONTRATOS


																																																																																// INICIO: COMPROBANTES DE PAGO

																																																																																$js_cache_comprobantes = '20220101080101';

																																																																																$ultimo_cambio_comprobantes_command = "SELECT updated_at FROM tbl_modificaciones WHERE modulo = 'Comprobantes' AND status = 1 ORDER BY updated_at DESC LIMIT 1";
																																																																																$ultimo_cambio_comprobantes_query = $mysqli->query($ultimo_cambio_comprobantes_command);

																																																																																if ($mysqli->error) {
																																																																																	print_r($mysqli->error);
																																																																																	echo "\n";
																																																																																	echo $ultimo_cambio_comprobantes_command;
																																																																																} else {
																																																																																	$ultimo_cambio_comprobantes_row = $ultimo_cambio_comprobantes_query->fetch_assoc();

																																																																																	if ($ultimo_cambio_comprobantes_row && isset($ultimo_cambio_comprobantes_row["updated_at"])) {
																																																																																		$js_cache_comprobantes = preg_replace('/\W/', '', $ultimo_cambio_comprobantes_row["updated_at"]);
																																																																																	}
																																																																																	// $ultimo_cambio_comprobantes_row = $ultimo_cambio_comprobantes_query->fetch_assoc();
																																																																																	// $js_cache_comprobantes = preg_replace('/\W/', '', $ultimo_cambio_comprobantes_row["updated_at"] );
																																																																																}




																																																																																$jsArrayComprobantes = [];

																																																																																$jsArrayComprobantes[] = "js/sec_comprobante.js";
																																																																																$jsArrayComprobantes[] = "js/sec_comprobante_pago.js";
																																																																																$jsArrayComprobantes[] = "js/sec_comprobante_reporte.js";
																																																																																$jsArrayComprobantes[] = "js/sec_comprobante_mantenimiento.js";
																																																																																$jsArrayComprobantes[] = "js/sec_comprobante_mantenimiento_proveedor.js";
																																																																																$jsArrayComprobantes[] = "js/sec_comprobante_mantenimiento_motivo.js";

																																																																																foreach ($jsArrayComprobantes as $key => $value) {
																																																																																	?><script type="text/javascript" src="<?php echo $value . "?version=" . $js_cache_comprobantes; ?>"></script><?php
																																																																																																												}

																																																																																																												// FIN: COMPROBANTES DE PAGO

																																																																																																												// INICIO MARKETING
																																																																																																												$js_cache_marketing = '20230101080101';

																																																																																																												$ultimo_cambio_marketing_command = "SELECT updated_at FROM tbl_modificaciones WHERE modulo = 'Marketing' AND status = 1 ORDER BY updated_at DESC LIMIT 1";
																																																																																																												$ultimo_cambio_marketing_query = $mysqli->query($ultimo_cambio_marketing_command);

																																																																																																												if ($mysqli->error) {
																																																																																																													print_r($mysqli->error);
																																																																																																													echo "\n";
																																																																																																													echo $ultimo_cambio_marketing_command;
																																																																																																												} else {
																																																																																																													$ultimo_cambio_marketing_row = $ultimo_cambio_marketing_query->fetch_assoc();

																																																																																																													if ($ultimo_cambio_marketing_row && isset($ultimo_cambio_marketing_row["updated_at"])) {
																																																																																																														$js_cache_marketing = preg_replace('/\W/', '', $ultimo_cambio_marketing_row["updated_at"]);
																																																																																																													}
																																																																																																												}

																																																																																																												$jsArray_marketing = [];
																																																																																																												$jsArray_marketing[] = "js/sec_marketing.js";
																																																																																																												$jsArray_marketing[] = "js/sec_marketing_pizarra.js";
																																																																																																												$jsArray_marketing[] = "js/sec_marketing_solicitud.js";
																																																																																																												$jsArray_marketing[] = "js/sec_marketing_nuevo.js";
																																																																																																												$jsArray_marketing[] = "js/sec_marketing_detalle_solicitud.js";
																																																																																																												$jsArray_marketing[] = "js/sec_marketing_promocion_marketing.js";

																																																																																																												foreach ($jsArray_marketing as $key => $value) {
																																																																																																													?><script type="text/javascript" src="<?php echo $value . "?version=" . $js_cache_marketing; ?>"></script><?php
																																																																																																																																							}
																																																																																																																																							// FIN MARKETING

																																																																																																																																							$js_cache_registro_premios = '1002';
																																																																																																																																							//$jsArray[]="js/sec_registro_premios.js";
																																																																																																																																							echo '<script type="text/javascript" src="js/sec_registro_premios.js?version=' . $js_cache_registro_premios . '"></script>';
																																																																																																																																								?>