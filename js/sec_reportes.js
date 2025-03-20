function sec_reportes(){
	if(sec_id=="reportes"){
		if (sub_sec_id=="resultados_apuestas") {
			sec_reportes_resultados_apuestas();			
		}
		if (sub_sec_id=="tickets") {
			sec_reportes_tickets();			
		}		
		if (sub_sec_id=="cobranzas") {
			sec_reportes_cobranzas();			
		}
		if (sub_sec_id=="caja_sistema"){
			sec_reportes_caja_sistema();
		}
		if (sub_sec_id=="web_total"){
			sec_reportes_web_total();
		}
		if(sub_sec_id=="graficos_dinero_apostado"){
			sec_reportes_graficos_dinero_apostado();
		}
		if (sub_sec_id=="resumen_apostado") {
			sec_reportes_resumen_apostado();
		};
		if (sub_sec_id=="pagados_en_de_otras_tiendas") {
			sec_reportes_pagados_en_de_otras_tiendas();
		};
		if (sub_sec_id=="en_de") {
			sec_reportes_en_de();
		};
		if (sub_sec_id=="kurax_ende") {
			sec_reportes_kurax_ende();
		};

		if (sub_sec_id=="del_dep") {
			sec_reportes_del_dep();
		};

		if (sub_sec_id=="tercero_autorizado") {
			sec_reportes_tercero_autorizado();
		};

		if (sub_sec_id=="hist_fusion") {
            sec_reportes_hist_fusion();
        };

		if (sub_sec_id=="bet_bar") {
			sec_reportes_bet_bar();
		};
		if (sub_sec_id=="venta_general_tienda") {
			sec_reportes_venta_general_tienda();
		};
		if (sub_sec_id=="resumen_dia") {
			sec_reportes_resumen_dia();
		};
		if (sub_sec_id=="participaciones") {
			sec_reportes_participaciones();
		};
		if (sub_sec_id=="comisiones") {
			sec_reportes_comisiones();
		};
		if (sub_sec_id=="saldos") {
			sec_reportes_saldos();
		};

		if (sub_sec_id=="cajas_eliminadas"){
			sec_caja_eliminadas();
		};

		if (sub_sec_id=="solicitudes"){
			sec_reportes_solicitudes();
		};

		if (sub_sec_id=="kasnet"){
			sec_reportes_kasnet();
		};

		if (sub_sec_id=="servicio_publico"){
			sec_reportes_servicio_publico();
		};

		if (sub_sec_id=="centro_de_costos"){
			sec_reportes_centro_de_costos();
		};

		if (sub_sec_id=="concar"){
			fnc_sec_reportes_concar_inicializar();
		};

		if (sub_sec_id=="vigencia"){
			sec_reportes_vigencia();
		};

		if (sub_sec_id=="nif16_cambio_moneda"){
			sec_reportes_nif16_cam_moneda();
		};

		if (sub_sec_id=="nif16_terminacion_renovacion"){
			sec_reportes_nif16_terminacion_renovacion();
		};

		if (sub_sec_id=="nif16_bdt"){
			sec_reportes_nif16_bdt();
		};

		if (sub_sec_id=="contratos"){
			sec_reportes_contratos();
		};

		if (sub_sec_id=="listado_vales"){
			sec_reportes_listado_vales();
		};
		
		if (sub_sec_id=="vales_gdt"){
			sec_reportes_vales_gdt();
		};

		if (sub_sec_id=="vales_gdt_detallado"){
			sec_reportes_vales_gdt_detallado();
		};

		if (sub_sec_id=="apuestas_deportivas"){
			sec_reportes_apuestas_deportivas();
		}
	}
}

// $(function() {
	// 	$('.select_picker_periodo_de_tiempo').on('change', function(){
	// 		var selected = $(this).find("option:selected").val();
	// 		if (selected==6) {
	//     	//$('.form_date_desde > .form-control').prop('disabled', false);
	//     	//$('.form_date_hasta > .form-control').prop('disabled', false);	
	//     		$('.iconofecha').css("cursor","not-allowed","important");        						        			
	// 			$(".input_time_desde_ocultar_mostrar").css("width","73%");
	// 			$(".input_time_hasta_ocultar_mostrar").css("width","73%");        
	// 			$(".ocultar_mostrar_timepicker").css("display", "block");
	// 			$(".span_mostrar_timepicker").css("padding","8px").click();
	// 			$(".bootstrap-timepicker-widget").css("z-index","1");
	// 		}else{
	//     	/*
	//       $('.form_date_desde').datetimepicker('remove');
	//       $('.form_date_desde > .form-control').prop('disabled', true);	
	//     	$('.form_date_hasta').datetimepicker('remove');
	//       $('.form_date_hasta > .form-control').prop('disabled', true);	            		
				
	//       */
	//       $(".ocultar_mostrar_timepicker").css("display", "none");
	// 			$(".bootstrap-timepicker-widget").css("z-index","-1");
	// 		}
	// 	});
// });


