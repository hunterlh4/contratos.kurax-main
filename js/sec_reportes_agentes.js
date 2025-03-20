var at_liquidaciones_filtro_fecha_inicio = moment().subtract(1, 'days').format("YYYY-MM-DD");
var at_liquidaciones_filtro_fecha_fin = moment().subtract(1, 'days').format("YYYY-MM-DD");;

$('.fecha_inicio_enviar').val(at_liquidaciones_filtro_fecha_inicio);
$('.fecha_fin_enviar').val(at_liquidaciones_filtro_fecha_fin);

function sec_reportes_agentes(){
	if (sec_id == 'reportes' && sub_sec_id=='agentes') {
		eventos();
    } 
}

function formatonumeros2(x) {
  if (x) {
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }else{
    return 0;
  }
}

function eventos(){
	$(".filtro_datepicker")
	  .datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	  })
	  .on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
	  });
 
	$("#local_id").select2();
	$("#btn_buscar").on("click",function(){
		buscar_data();
	})		
}	


function buscar_data(){
	loading(true);
	get_liquidaciones_data.filtro={};
	get_liquidaciones_data.filtro.fecha_inicio=$(".fecha_inicio_enviar").val();
	get_liquidaciones_data.filtro.fecha_fin=$(".fecha_fin_enviar").val();
 
	local=[$("#local_id").val()];
	get_liquidaciones_data.filtro.locales = local;
	get_liquidaciones_data.where="liquidaciones";

	auditoria_send({"proceso":"sec_agente_liquidacion_get_liquidacion","data":get_liquidaciones_data});
	$.ajax({
		data: get_liquidaciones_data,
		type: "POST",
		url: "/api/?json",
	})
	.done(function(responsedata, textStatus, jqXHR ) {
		try
		{
			var obj = jQuery.parseJSON(responsedata);
			if(typeof obj.data.locales !="undefined"){
				datos_agente= obj.data.locales[0].liquidaciones.total_rango_fecha;
				//debugger;  

				
				if(datos_agente[16] != undefined){
					$('.apuestaDeportivaApostado').html(formatonumeros2(parseFloat(datos_agente[16].total_apostado).toFixed(2)));
					$('.apuestaDeportivaGanado').html(formatonumeros2(parseFloat(datos_agente[16].total_pagado).toFixed(2)));
					$('.apuestaDeportivatotal').html(formatonumeros2(parseFloat(datos_agente[16].total_produccion).toFixed(2)));
					 
					$('.participacionPbetMonto').html(formatonumeros2(parseFloat(datos_agente[16].total_cliente).toFixed(2)));
					$('.participacionOatPbetMonto').html(formatonumeros2(parseFloat(datos_agente[16].total_freegames).toFixed(2)));  

					$('.participacionOATWebMonto').html(formatonumeros2(parseFloat(datos_agente[16].total_caja_web).toFixed(2)));  

				}else{
					$('.apuestaDeportivaApostado').html('0.00');
					$('.apuestaDeportivaGanado').html('0.00');
					$('.apuestaDeportivatotal').html('0.00');
					$('.participacionPbetMonto').html('0.00'); 
					$('.participacionOatPbetMonto').html('0.00'); 
					$('.participacionOATWebMonto').html('0.00');

				}
				
				if(datos_agente[15] != undefined){
					$('.webApostado').html(formatonumeros2(parseFloat(datos_agente[15].total_apostado).toFixed(2)));
					$('.webGanado').html(formatonumeros2(parseFloat(datos_agente[15].total_pagado).toFixed(2))); 
					$('.webTotal').html(formatonumeros2(parseFloat(datos_agente[15].total_produccion).toFixed(2)));
					 
					$('.participacionWebMonto').html(formatonumeros2(parseFloat(datos_agente[15].total_cliente).toFixed(2))); 
					//$('.participacionOATWebMonto').html(parseFloat(datos_agente[15].total_freegames).toFixed(2));  
 
				}else{
					$('.webApostado').html('0.00');
					$('.webGanado').html('0.00');
					$('.webTotal').html('0.00');
					$('.participacionWebMonto').html('0.00');
					//$('.participacionOATWebMonto').html('0.00');
				}
				
				/*saldo web*/
				if (datos_agente[37] != undefined) {
					$('.webApostado').html(formatonumeros2(parseFloat(datos_agente[37].total_depositado_web).toFixed(2)));
					$('.webTotal').html(formatonumeros2(parseFloat(datos_agente[37].total_pagado).toFixed(2)));
					$('.participacionWebMonto').html(formatonumeros2(parseFloat(datos_agente[37].total_cliente).toFixed(2)));
					$('.participacionOATWebMonto').html(formatonumeros2(parseFloat(datos_agente[37].total_freegames).toFixed(2)));
				} else {
					$('.participacionWebMonto').html('0.00');
					$('.participacionOATWebMonto').html('0.00');
				}

				/** aterax caja*/
				if (datos_agente[42] != undefined) {
					$('.ateraxApostado').html(formatonumeros2(parseFloat(datos_agente[42].total_apostado).toFixed(2)));
					$('.ateraxGanado').html(formatonumeros2(parseFloat(datos_agente[42].total_pagado).toFixed(2)));
					$('.ateraxTotal').html(formatonumeros2(parseFloat(datos_agente[42].total_produccion).toFixed(2)));

					$('.participacionAteraxMonto').html(formatonumeros2(parseFloat(datos_agente[42].total_cliente).toFixed(2)));
					$('.participacionOatAteraxMonto').html(formatonumeros2(parseFloat(datos_agente[42].total_freegames).toFixed(2)));
				} else {
					$('.ateraxApostado').html('0.00');
					$('.ateraxGanado').html('0.00');
					$('.ateraxTotal').html('0.00');

					$('.participacionAteraxMonto').html('0.00');
					$('.participacionOatAteraxMonto').html('0.00');
				}

				/** aterax terminal*/
				if (datos_agente[43] != undefined) {
					$('.terminalAteraxApostado').html(formatonumeros2(parseFloat(datos_agente[43].total_apostado).toFixed(2)));
					$('.terminalAteraxPagado').html(formatonumeros2(parseFloat(datos_agente[43].total_pagado).toFixed(2)));
					$('.terminalAteraxTotal').html(formatonumeros2(parseFloat(datos_agente[43].total_produccion).toFixed(2)));

					$('.participacionAteraxsbtMonto').html(formatonumeros2(parseFloat(datos_agente[43].total_cliente).toFixed(2)));
					$('.participacionOATAteraxsbtMonto').html(formatonumeros2(parseFloat(datos_agente[43].total_freegames).toFixed(2)));
				} else {
					$('.terminalAteraxApostado').html('0.00');
					$('.terminalAteraxPagado').html('0.00');
					$('.terminalAteraxTotal').html('0.00');

					$('.participacionAteraxsbtMonto').html('0.00');
					$('.participacionOATAteraxsbtMonto').html('0.00');
				}
				
				if(datos_agente[21] != undefined){
					$('.goldenRaceApostado').html(formatonumeros2(parseFloat(datos_agente[21].total_apostado).toFixed(2)));
					$('.goldenRaceGanado').html(formatonumeros2(parseFloat(datos_agente[21].total_pagado).toFixed(2)));
					$('.goldenRaceTotal').html(formatonumeros2(parseFloat(datos_agente[21].total_produccion).toFixed(2)));
				 
					$('.participacionGrMonto').html(formatonumeros2(parseFloat(datos_agente[21].total_cliente).toFixed(2))); 
					$('.participacionOATGrMonto').html(formatonumeros2(parseFloat(datos_agente[21].total_freegames).toFixed(2)));  
				}else{
					$('.goldenRaceApostado').html('0.00');
					$('.goldenRaceGanado').html('0.00');
					$('.goldenRaceTotal').html('0.00');
					$('.participacionGrMonto').html('0.00');
					$('.participacionOATGrMonto').html('0.00');
				}

				if(datos_agente[17] != undefined){
					$('.terminalApostado').html(formatonumeros2(parseFloat(datos_agente[17].total_depositado - datos_agente[17].total_anulado_retirado).toFixed(2)));
					$('.terminalGanado').html(formatonumeros2(parseFloat(datos_agente[17].total_pagado).toFixed(2)));
					$('.terminalTotal').html(formatonumeros2(parseFloat(datos_agente[17].total_produccion).toFixed(2)));
					$('.participacionOATsbtMonto').html(formatonumeros2(parseFloat(datos_agente[17].total_freegames).toFixed(2)));
					$('.participacionsbtMonto').html(formatonumeros2(parseFloat(datos_agente[17].total_cliente).toFixed(2))); 
				}else{
					$('.terminalApostado').html('0.00');
					$('.terminalGanado').html('0.00');
					$('.terminalTotal').html('0.00');
				}

				if(datos_agente[30] != undefined){
					$('.bingoApostado').html(formatonumeros2(parseFloat(datos_agente[30].total_apostado).toFixed(2)));
					//$('.bingoPagado').html(formatonumeros2(parseFloat(datos_agente[30].total_ganado).toFixed(2)));
					$('.pago_de_bingos').html(formatonumeros2(parseFloat(datos_agente[30].pagados_en_su_punto_propios).toFixed(2)));

					$('.bingoTotal').html($(".bingoApostado").text());
					//$('.bingoTotal').html(formatonumeros2(parseFloat(datos_agente[30].total_produccion).toFixed(2)));
				 
					$('.participacionBingoMonto').html(formatonumeros2(parseFloat(datos_agente[30].total_cliente).toFixed(2)));
					$('.participacionOATBingoMonto').html(formatonumeros2(parseFloat(datos_agente[30].total_freegames).toFixed(2)));  
				}else{
					$('.bingoApostado').html('0.00');
					$('.bingoPagado').html('0.00'); 
					$('.bingoTotal').html('0.00');
					$('.participacionBingoMonto').html('0.00');
					$('.participacionOATBingoMonto').html('0.00');
				}
				/*carrera de caballos*/
				if(datos_agente[34] != undefined){
					$('.carreradecaballosApostado').html(formatonumeros2(parseFloat(datos_agente[34].total_apostado).toFixed(2)));
					$('.carreradecaballosPagado').html(formatonumeros2(parseFloat(datos_agente[34].total_pagado).toFixed(2)));
					$('.carreradecaballosTotal').html(formatonumeros2(parseFloat(datos_agente[34].total_produccion).toFixed(2)));
					$('.pago_carrera_de_caballos').html(formatonumeros2(parseFloat(datos_agente[34].total_freegames).toFixed(2)));
					$('.participacionHipica').html(formatonumeros2(parseFloat(datos_agente[34].total_cliente).toFixed(2))); 
				}else{
					$('.carreradecaballosApostado').html('0.00');
					$('.carreradecaballosPagado').html('0.00'); 
					$('.carreradecaballosTotal').html('0.00');
					$('.pago_carrera_de_caballos').html('0.00');
					$('.participacionHipica').html('0.00');

				}

				if(datos_agente.total != undefined){
					var total_depositado_web = parseFloat(datos_agente[37]!= undefined ? datos_agente[37].total_depositado_web : 0);
					//$('.total_ingreso').html(formatonumeros2(parseFloat(datos_agente.total.total_apostado).toFixed(2)));
					$('.total_ingreso').html(formatonumeros2(parseFloat(parseFloat(datos_agente.total.total_apostado) + total_depositado_web).toFixed(2)));
					//$('.total_salida').html(formatonumeros2(parseFloat(datos_agente.total.total_pagado).toFixed(2)));
					//var pago_de_bingos =  parseFloat($(".pago_de_bingos").text().replace(/,/g, ''));
					//var pago_de_bingos =  parseFloat(datos_agente[30]!="undefined"?datos_agente[30].total_pagado : 0)
					var pago_de_bingos =  parseFloat(datos_agente[30]!= undefined ?datos_agente[30].pagados_en_su_punto_propios : 0);
					var pago_carrera_de_caballos =  parseFloat(datos_agente[34]!= undefined ?datos_agente[34].total_freegames : 0);
										
					var pagado_de_bingos =  parseFloat(datos_agente[30]!= undefined ?datos_agente[30].total_pagado : 0)
					var total_salida = datos_agente.total.total_pagado - pagado_de_bingos;
					$('.total_salida').html(formatonumeros2(parseFloat(total_salida).toFixed(2)));

					var total_resultado =   parseFloat($(".apuestaDeportivatotal").text().replace(/,/g,'')) +
											parseFloat($(".webTotal").text().replace(/,/g,'')) +
											parseFloat($(".goldenRaceTotal").text().replace(/,/g,'')) +
											parseFloat($(".terminalTotal").text().replace(/,/g,'')) +
											parseFloat($(".cashTotal").text().replace(/,/g,'')) +
											/*parseFloat($(".pollaTotal").text().replace(/,/g,'')) +*/
											parseFloat($(".carreradecaballosTotal").text().replace(/,/g,'')) +
											parseFloat($(".bingoTotal").text().replace(/,/g,'')) ;
					$('.total_resultado').html(formatonumeros2(parseFloat(total_resultado).toFixed(2)));
					//$('.total_resultado').html(formatonumeros2(parseFloat(datos_agente.total.total_produccion).toFixed(2)));
					$('.resultadoNegocioMonto').html(formatonumeros2(parseFloat(datos_agente.total.total_produccion).toFixed(2)));
					//$('.participacionTotalMonto').html(formatonumeros2(parseFloat(datos_agente.total.total_cliente).toFixed(2)));
					$('.participacionTotalMonto').html(formatonumeros2(parseFloat(datos_agente.total.total_cliente).toFixed(2)));

					var part_web = parseFloat(datos_agente[16]!= undefined ?datos_agente[16].total_caja_web : 0);
					$('.participacionOATmonto').html(formatonumeros2(parseFloat(
						  datos_agente.total.total_freegames
						+ part_web
						- pago_de_bingos
						).toFixed(2)));

				} 	
			}
			//.total;
            loading();
		}
		catch(err)
		{
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            });
		}
	})
	.complete(function(){
		loading();
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		if ( console && console.log ) {
			console.log( "La solicitud liquidaciones a fallado: " +  textStatus);
		}
	});

}