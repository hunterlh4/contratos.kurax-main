




function sec_reportes_servicio_publico() {
	sec_rep_servicio_publico_cargar_meses();
	sec_rep_ser_pub_cargar_jefe_comercial('');
	sec_rep_ser_pub_cargar_supervisor('');
	
}


$('#sec_rep_ser_pub_buscar_por').change(function(){
	var buscar_por = $('#sec_rep_ser_pub_buscar_por').val();
	if (buscar_por == 1) {
		$('.block-periodo').show();
		$('.block-fecha').hide();
	}
	if (buscar_por == 2) {
		$('.block-periodo').hide();
		$('.block-fecha').show();
	}
	if (buscar_por == 3) {
		$('.block-periodo').hide();
		$('.block-fecha').show();
	}
	// ObtenerTipoServicio();
});

$('#sec_rep_ser_pub_select_locales').change(function(){
	var local_id = $('#sec_rep_ser_pub_select_locales').val();
	sec_rep_limpiar_selects();
	sec_rep_ser_pub_cargar_jefe_comercial(local_id);
	sec_rep_ser_pub_cargar_supervisor(local_id);
});

function sec_rep_limpiar_selects(){
	$('#sec_rep_ser_pub_select_jefe_comercial').html('');
	$('#sec_rep_ser_pub_select_jefe_comercial').append('<option value="0">TODOS</option>');
	$('#sec_rep_ser_pub_select_supervisor').html('');
	$('#sec_rep_ser_pub_select_supervisor').append('<option value="0">TODOS</option>');
}
function sec_rep_servicio_publico_cargar_meses(){
	console.log("meses");
    var data = {
		"accion": "obtener_meses",
	}
	// auditoria_send({ "proceso": "obtener_meses", "data": data });
    $.ajax({
        url: "sys/get_reportes_servicio_publico.php",
        type: 'POST',
        data: data,
        success: function(resp) {
			let response = JSON.parse(resp);
			if (response.status == 200) {
				$('#sec_rep_ser_pub_select_mes').append(response.result);
			}
        },
    });
  
}
function sec_rep_ser_pub_cargar_jefe_comercial(id_local){
	var local_id = id_local;
	var data = {
		"accion": "obtener_jefes_comerciales",
		"local_id": local_id
	}
	$('#sec_rep_ser_pub_select_jefe_comercial').html('');
	$('#sec_rep_ser_pub_select_jefe_comercial').append('<option value="0">TODOS</option>');
	auditoria_send({ "proceso": "obtener_jefes_comerciales", "data": data });
    $.ajax({
        url: "sys/get_reportes_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
        	//debugger;
            var respuesta = JSON.parse(resp);

            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }

            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    $('#sec_rep_ser_pub_select_jefe_comercial').append(
                        '<option value = "' + item.id + '">' + item.jefe_comercial + '</option>'
                    );
                });
                return false;
            }      
        },
        error: function() {}
    });
}
function sec_rep_ser_pub_cargar_supervisor(id_local){
	var local_id = id_local;
	var data = {
		"accion": "obtener_supervisores",
		"local_id": local_id
	}
	$('#sec_rep_ser_pub_select_supervisor').html('');
	$('#sec_rep_ser_pub_select_supervisor').append('<option value="0">TODOS</option>');

	auditoria_send({ "proceso": "obtener_supervisores", "data": data });
    $.ajax({
        url: "sys/get_reportes_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    $('#sec_rep_ser_pub_select_supervisor').append(
                        '<option value = "' + item.id + '">' + item.supervisor + '</option>'
                    );
                });
                return false;
            }
        },
        error: function() {}
    });
}


function sec_rep_serv_pub_buscar_registros(){

	let buscar_por = $('#sec_rep_ser_pub_buscar_por').val();
	let tipo_servicio = $('#sec_rep_ser_pub_select_tipo_servicio').val();
	let estado = $('#sec_rep_ser_pub_select_estado').val();
	var id_local = $('#sec_rep_ser_pub_select_locales').val();
	var id_empresa = $('#sec_rep_ser_pub_select_empresa').val();
	var id_jefe_comercial = $('#sec_rep_ser_pub_select_jefe_comercial').val();
	var id_supervisor = $('#sec_rep_ser_pub_select_supervisor').val();
	var periodo = $('#sec_rep_ser_pub_select_mes').val();
	var fec_vcto_desde = $('#sec_rep_serv_pub_inicio_vcto').val();
	var fec_vcto_hasta = $('#sec_rep_serv_pub_fin_vcto').val();

	if(periodo == 0 && (fec_vcto_desde == "" || fec_vcto_hasta == "")){
		alertify.error('Información: Seleccione el Mes de Periodo o Seleccione el Rango de Fecha de Vencimiento',10);
		return false;
	}

	
	if(buscar_por == 1){
		if (periodo == "" || periodo == '0') {
			alertify.error('Información: Seleccione el Mes de Periodo',10);
			return false;
		}
	}
	if(buscar_por == 2 ){
		if(fec_vcto_desde == "" || fec_vcto_hasta == ""){
			alertify.error('Información: Seleccione el Rango de Fecha de Vencimiento',10);
			return false;
		}
	}

	var data = {
		"accion": "obtener_reporte_servicio_publico",
		"buscar_por" : buscar_por,
		"local_id": id_local,
		"id_empresa": id_empresa,
		"id_jefe_comercial" : id_jefe_comercial,
		"id_supervisor" : id_supervisor,
		"periodo" : periodo,
		"fec_vcto_desde" : fec_vcto_desde,
		"fec_vcto_hasta" : fec_vcto_hasta,
		"estado" : estado,
		"tipo_servicio" : tipo_servicio,
	}
	reporte_mostrarReporteExcel();
	auditoria_send({ "proceso": "obtener_reporte_servicio_publico", "data": data });
    $.ajax({
        url: "sys/get_reportes_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				$('#resportes_servicio_publico_locales_div_tabla').html(respuesta.table);
			}
			initialize_table('sec_rep_servicio_publico');
			return false;
        },
        error: function() {}
    });
}
function reporte_mostrarReporteExcel()
{

	
	let buscar_por = $('#sec_rep_ser_pub_buscar_por').val();
	let tipo_servicio = $('#sec_rep_ser_pub_select_tipo_servicio').val();
	let estado = $('#sec_rep_ser_pub_select_estado').val();
	var id_local = $('#sec_rep_ser_pub_select_locales').val();
	var id_empresa = $('#sec_rep_ser_pub_select_empresa').val();
	var id_jefe_comercial = $('#sec_rep_ser_pub_select_jefe_comercial').val();
	var id_supervisor = $('#sec_rep_ser_pub_select_supervisor').val();
	var periodo = $('#sec_rep_ser_pub_select_mes').val();
	var fec_vcto_desde = $('#sec_rep_serv_pub_inicio_vcto').val();
	var fec_vcto_hasta = $('#sec_rep_serv_pub_fin_vcto').val();
	if(periodo == 0 && (fec_vcto_desde == "" || fec_vcto_hasta == "")){
		alertify.error('Información: Seleccione el Mes de Periodo o Seleccione el Rango de Fecha de Vencimiento',10);
		return false;
	}

	document.getElementById('cont_reporte_servicio_publico_excel').innerHTML = '<a href="export.php?export=reporte_servicio_publico&amp;type=lista&amp;buscar_por='+buscar_por+'&amp;tipo_servicio='+tipo_servicio+'&amp;estado='+estado+'&amp;id_local='+id_local+'&amp;id_empresa='+id_empresa+'&amp;id_jefe_comercial='+id_jefe_comercial+'&amp;id_supervisor='+id_supervisor+'&amp;periodo='+periodo+'&amp;fec_vcto_desde='+fec_vcto_desde+'&amp;fec_vcto_hasta='+fec_vcto_hasta+'" class="btn btn-success export_list_btn" download="reporte_servicio_publico.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';

}

function ModalVerRecibo(tipo, id_recibo){
	$("#sec_rep_serv_pub_modal_recibo").modal("show");
	
	
	let data = {
		tipo : tipo,
		id_recibo : id_recibo,
		accion: "obtener_recibo_servicio_publico"
	};

	$.ajax({
        url: "sys/get_reportes_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				if (tipo == "Recibo") {
					$('#sec_rep_serv_titulo_recibo').html('Recibo - '+ respuesta.result.centro_costo+' '+respuesta.result.local_nombre);
				}else{
					$('#sec_rep_serv_titulo_recibo').html('Constacia de Pago - '+ respuesta.result.centro_costo+' '+respuesta.result.local_nombre);
				}

				var path_img = "files_bucket/contratos/servicios_publicos/";
				if(respuesta.result.id_tipo_servicio_publico == 1){ //Luz
					path_img += "luz/";
				}else{
					path_img += "agua/";
				}
				$('#sec_rep_serv_pub_div_recibo').html('');

				if (respuesta.result.nombre_file != null) {
					path_img += respuesta.result.nombre_file;
				
					var nuevo_id = "sec_serv_pub_img_servicio_publico"+respuesta.result.id+"_"+respuesta.result.numero_suministro;
					if (respuesta.result.extension == "pdf") {
						$('#sec_rep_serv_pub_div_recibo').append(
							'<iframe src="' + path_img + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="500"></iframe>'
						);

						$('#sec_rep_serv_pub_btn_descargar_imagen_recibo').hide();
						$('#sec_rep_serv_pub_div_VerImagenFullPantalla').hide();
						
					}else if (respuesta.result.extension == 'jpg' || respuesta.result.extension == 'png' || respuesta.result.extension == 'jpeg') {
						$('#sec_rep_serv_pub_div_recibo').append(
							'<div class="col-md-12">' +
							'   <div align="center" style="height: 100%; width: 100%;">' +
							'       <img  id="' + nuevo_id + '" src="' + path_img + '" width="300px" height="350px" />' +
							'   </div>' +
							'</div>'
						);
						$('#sec_rep_serv_pub_ver_full_pantalla').attr('onClick', 'sec_contrato_detalle_solicitud_ver_imagen_full_pantalla("' + path_img + '");');
						$('#sec_rep_serv_pub_btn_descargar_imagen_recibo').show();
						$('#sec_rep_serv_pub_div_VerImagenFullPantalla').show();
						var ruta = "sec_reportes_servicio_publico_btn_descargar('"+ respuesta.result.ruta_download_file +"');";
						$('#sec_rep_serv_pub_descargar_imagen_a').attr('onClick', ruta);
						$("#" + nuevo_id).error(function(){
						$(this).hide();
						$('#sec_rep_serv_pub_mensaje_imagen').html('La imagen no existe en la carpeta');
						$('#sec_rep_serv_pub_ver_full_pantalla').prop('disabled', true);
						$('#sec_rep_serv_pub_descargar_imagen_a').prop('disabled', true);
						
						});
					}
				}else{
					if (tipo == "Recibo") {
						$('#sec_rep_serv_pub_div_recibo').html('<div class="alert alert-danger" role="alert">No cuenta con un recibo</div>');
					}else if(tipo == "Pago") {
						$('#sec_rep_serv_pub_div_recibo').html('<div class="alert alert-danger" role="alert">No cuenta con una constacia de pago</div>');
					}
					
				}
				
			}
			return false;
        },
        error: function() {}
    });
}

function sec_reportes_servicio_publico_btn_descargar(ruta_archivo)
{
	var extension = "";

	// Obtener el nombre del archivo
	var ultimoPunto = ruta_archivo.lastIndexOf("/");

	if(ultimoPunto !== -1)
	{
	    var extension = ruta_archivo.substring(ultimoPunto + 1);
	}
	
	// Crear un enlace temporal
    var enlace = document.createElement('a');
    enlace.href = ruta_archivo;

    // Darle un nombre al archivo que se descargará
    enlace.download = extension;

    // Simular un clic en el enlace
    document.body.appendChild(enlace);
    enlace.click();

    // Limpiar el enlace temporal
    document.body.removeChild(enlace);
}