
function sec_contrato_asiento_contable_servicio_publico() {
	sec_con_asi_cont_Cargar_Meses();
}

$('#sec_con_asi_cont_ser_pub_buscar_por').change(function(){
	var buscar_por = $('#sec_con_asi_cont_ser_pub_buscar_por').val();
	if (buscar_por == 1) {
		$('.block-periodo').show();
		$('.block-fecha').hide();
		$("#select2_example").empty();
	}
	if (buscar_por == 2 || buscar_por == 3) {
		$('.block-periodo').hide();
		$('.block-fecha').show();
	}
});

function sec_con_asi_cont_Cargar_Meses(){
	var date = new Date();
    var meses = [];

    date.setMonth(date.getMonth());
    var fecha_anio_ = date.toISOString().substring(0, 7);
    meses.push(fecha_anio_);

    //date.setMonth(date.getMonth() - 1);
    for (var c = 1; i <= 12; i++) { // Los ultimos 12 meses
        date.setMonth(date.getMonth() - c);
        var fecha_anio = date.toISOString().substring(0, 7);
        meses.push(fecha_anio)
    }

    $('#sec_con_asi_cont_ser_pub_select_mes').append(
  		'<option value="0">- Seleccione -</option>'
  	);

    $.each(meses, function (ind, elem) { 
		
	  $('#sec_con_asi_cont_ser_pub_select_mes').append(
	  		'<option '+(ind == 0 ? 'selected':'' )+' value="' + elem + '">' + obtenerAnioMesLetras(elem) + '</option>'
	  	);
	}); 
}


function sec_asi_cont_serv_pub_buscar_registros() {
	sec_asi_cont_serv_pub_mostrarReporteExcel();
}

function sec_asi_cont_serv_pub_mostrarReporteExcel() {
	let buscar_por = $('#sec_con_asi_cont_ser_pub_buscar_por').val();
	var periodo = $('#sec_con_asi_cont_ser_pub_select_mes').val();
	var fec_vcto_desde = $('#sec_con_asi_cont_ser_pub_txt_fec_vcto_desde').val();
	var fec_vcto_hasta = $('#sec_con_asi_cont_ser_pub_txt_fec_vcto_hasta').val();
	var tipo_servicio = $('#sec_con_asi_cont_ser_pub_select_tipo_servicio').val();
	var id_empresa = $('#sec_con_asi_cont_ser_pub_select_empresa').val();
	var fecha_comprobante = $('#sec_con_asi_cont_ser_pub_fecha_comprobante').val();
	var numero_comprobante = $('#sec_con_asi_cont_ser_pub_numero_comprobante').val();

	
	
	if(periodo == 0 && (fec_vcto_desde == "" || fec_vcto_hasta == "")){
		alertify.error('Información: Seleccione el Mes de Periodo o Seleccione el Rango de Fecha de Vencimiento',5);
		return false;
	}

	if(id_empresa == 0 ){
		alertify.error('Información: Seleccione una empresa',5);
		return false;
	}

	if(fecha_comprobante == "" ){
		alertify.error('Información: Ingrese una fecha de comprobante',5);
		return false;
	}

	if(numero_comprobante == "" ){
		alertify.error('Información: Ingrese un numero de comprobante',5);
		return false;
	}

	let url = 'contrato_export_servicio_publico_concar.php?export=reportes_servicio_publico_contabilidad&tipo_report_contable=1&buscar_por='+buscar_por+'&periodo='+periodo+'&fec_vcto_desde='+fec_vcto_desde+'&fec_vcto_hasta='+fec_vcto_hasta+'&tipo_servicio='+tipo_servicio+'&id_empresa='+id_empresa+'&fecha_comprobante='+fecha_comprobante+'&numero_comprobante='+numero_comprobante;

	window.open(url,'_blank');


	// document.getElementById('div_contrato_export_servicio_publico_concar').innerHTML = '<a href="contrato_export_servicio_publico_concar.php?export=reportes_servicio_publico_contabilidad&amp;tipo_report_contable=1&amp;buscar_por='+buscar_por+'&amp;periodo='+periodo+'&amp;fec_vcto_desde='+fec_vcto_desde+'&amp;fec_vcto_hasta='+fec_vcto_hasta+'&amp;tipo_servicio='+tipo_servicio+'&amp;id_empresa='+id_empresa+'&amp;fecha_comprobante='+fecha_comprobante+'&amp;numero_comprobante='+numero_comprobante+'" class="btn btn-success export_list_btn" target="_blank"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';

}