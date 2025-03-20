function sec_contrato_tesoreria_detalle_programacion(){
	
    sec_contrato_tesoreria_detalle_listar();
    sec_contrato_tesoreria_listar_acreedores();
}

function sec_contrato_tesoreria_detalle_listar(){
	sec_contrato_tesoreria_limpiar_tabla_auditoria();
	var programacion_id = $('#id_detalle_programacion_seleccionada').val();
	if(programacion_id > 0){
		array_tabla_detalle_programacion = [];
		var data = {
	        "accion": "sec_contrato_detalle_programacion_obtener_detalle",
	        "programacion_id":programacion_id
	    }
	    auditoria_send({ "proceso": "sec_contrato_detalle_programacion_obtener_detalle", "data": data });
	    $.ajax({
	        url: "sys/get_contrato_detalle_programacion.php",
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
	                /*swal('Aviso', respuesta.status, 'warning');
	                $('#tabla_programaciones').append(
	                    '<tr>' +
	                    '<td class="text-center" colspan="10">No hay programaciones</td>' +
	                    '</tr>'
	                );*/
	                return false;
	            }

	            if (parseInt(respuesta.http_code) == 200) {
					
	                $.each(respuesta.result, function(index, item) {
	                    array_tabla_detalle_programacion.push(item);
						var fechaObj = new Date(item.fecha_programacion+'T00:00:00-05:00');

						// Obtener los componentes de la fecha
						var year = fechaObj.getFullYear().toString();
						var month = (fechaObj.getMonth() + 1).toString(); // Los meses son devueltos de 0 a 11, así que sumamos 1
						var day = fechaObj.getDate().toString();

						// Asegurarnos de que el mes y el día tengan dos dígitos
						month = month.length === 1 ? '0' + month : month;
						day = day.length === 1 ? '0' + day : day;

						// Crear el nuevo formato de fecha "dd-mm-yyyy"
						var nuevoFormatofecha_programacion = day + '-' + month + '-' + year;
	                    $('#sec_det_num_programacion').val(item.programacion_numero);
	                    $('#sec_det_concepto_programacion').val(item.concepto);
	                    $('#sec_det_tipo_pago_programacion').val(item.tipo_pago);
	                    $('#sec_det_banco_programacion').val(item.banco);
	                    $('#sec_det_fecha_programacion').val(nuevoFormatofecha_programacion);
	                    $('#sec_det_tipo_cambio_programacion').val(item.valor_cambio);
	                    $('#sec_det_moneda_programacion').val(item.moneda);
	                    $('#sec_det_situacion_programacion').val(item.etapa);
	                    
	                    $('#sec_det_tabla_auditoria').append(
	                        '<tr>' +
	                        '<td>' + '<b>Elaborado por: </b>' + item.elaborado_por + '</td>' +
	                        '<td>' + '<b>Editado por: </b>' + item.editado_por + '</td>' +
	                        '<td>' + '<b>Procesado por: </b>' + item.procesado_por + '</td>' +
	                        '<td>' + '<b>Eliminado por: </b>' + item.eliminado_por + '</td>' +
	                        '</tr>' +
	                        '<tr>' +
	                        '<td>' + '<b>Fecha elaboración: </b>' + item.fecha_elaboracion + '</td>' +
	                        '<td>' + '<b>Fecha edición: </b>' + item.fecha_edicion + '</td>' +
	                        '<td>' + '<b>Fecha procesado: </b>' + item.fecha_proceso + '</td>' +
	                        '<td>' + '<b>Fecha eliminación: </b>' + item.fecha_eliminacion + '</td>' +
	                        '</tr>'
	                    );
	                    nombre_archivo = "'" + item.nombre_archivo + "'";
	                    extension_archivo = "'" + item.extension_archivo + "'";
	                    $('#sec_det_div_boton_ver_comprobante').append(
	                    	'<button type="button" class="btn btn-info btn-xs"title="Ver comprobante de pago" '+
	                    	'onclick="sec_det_contrato_verComprobanteVista(' + nombre_archivo + ', ' + extension_archivo + ')">' +
	                    	'<i class="fa fa-eye" ' +
	                    	'style="margin-right:5px;"></i>Visualizar comprobante</button>'
                    	);

                    	$('#sec_det_midocu').val(item.nombre_archivo);
	                });
	                return false;
	            }      
	        },
	        error: function() {}
	    });
	}
}

function sec_contrato_tesoreria_limpiar_tabla_auditoria() {
    $('#sec_det_tabla_auditoria').html(
        '<thead> ' +
		'<tr>' +
		'<th colspan="4" style="background-color: #E5E5E5; text-align: center;">' +
		'<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px">' +
		'Auditoría' +
		'</div>' +
		'</th>' +
		'</tr>' +
		'</thead>'
    );
}

function sec_contrato_tesoreria_listar_acreedores(){
	sec_contrato_tesoreria_limpiar_tabla_acreedores();
	var programacion_id = $('#id_detalle_programacion_seleccionada').val();
	if(programacion_id > 0){
		array_tabla_acreedores_programacion = [];
		var data = {
	        "accion": "sec_contrato_detalle_programacion_obtener_acreedores",
	        "programacion_id":programacion_id
	    }
	    auditoria_send({ "proceso": "sec_contrato_detalle_programacion_obtener_acreedores", "data": data });
	    $.ajax({
	        url: "sys/get_contrato_detalle_programacion.php",
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
	            	var c = 0;
	            	var monto_total = 0;
					
	                $.each(respuesta.result, function(index, item) {
	                	
	                    array_tabla_acreedores_programacion.push(item);
	                    c += 1;
	                    var fechaObj = new Date(item.fecha_vencimiento+'T00:00:00-05:00');

						// Obtener los componentes de la fecha
						var year = fechaObj.getFullYear().toString();
						var month = (fechaObj.getMonth() + 1).toString(); // Los meses son devueltos de 0 a 11, así que sumamos 1
						var day = fechaObj.getDate().toString();

						// Asegurarnos de que el mes y el día tengan dos dígitos
						month = month.length === 1 ? '0' + month : month;
						day = day.length === 1 ? '0' + day : day;

						// Crear el nuevo formato de fecha "dd-mm-yyyy"
						var nuevoFormatofecha_programacion = day + '-' + month + '-' + year;
	                    $('#sec_det_tabla_acreedores').append(
	                        '<tr>' +
	                        '<td>' + c + '</td>' +
	                        '<td>' + item.num_documento + '</td>' +
	                        '<td>' + item.acreedor + '</td>' +
	                        '<td>' + item.num_doc + '</td>' +
	                        '<td>' + item.dia_pago + '</td>' +
	                        '<td>' + nuevoFormatofecha_programacion + '</td>' +
	                        '<td>' + item.moneda + '</td>' +
	                        '<td>' + item.programado + '</td>' +
	                        '<td>' + item.centro_costo + '</td>' +
	                        '</tr>'
	                    );
	                    
	                    monto_total += parseFloat(item.programado);
	                });
	                console.log(monto_total);

	                $('#sec_det_tabla_acreedores').append(
                    	'<tr> ' +
                    	'<th colspan="9" style="text-align: right; background-color: #E5E5E5;"></th>' +
                        '</tr>' +
                        '<tr style="font-size: 13px;">' +
                        '<th colspan="8" style="text-align: right;">Total acreedores:</th>' +
                        '<th style="text-align: right;">' + c + '</th>' +
                        '</tr>' +
                        '<tr style="font-size: 13px;">' +
                        '<th colspan="8" style="text-align: right;">Total monto:</th>' +
                        '<th style="text-align: right;">' + parseFloat(monto_total) + '</th>' +
                        '</tr>'
                	);

	                return false;
	            }      
	        },
	        error: function() {}
	    });
	}
}

function sec_contrato_tesoreria_limpiar_tabla_acreedores() {
    $('#sec_det_tabla_acreedores').html(
        '<thead>' +
		'<tr>' +
		'<th colspan="9" style="background-color: #E5E5E5;">' +
		'<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px" style="text-align: left;">' +
		'Acreedores que integran la programación de pago:' +
		'</div>' +
		'<div id="sec_det_div_boton_ver_comprobante" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">' +
		'</div>' +
		'</th>' +
		'</tr>' +
		'<tr>' +
		'<th>N.°</th>' +
		'<th>Código</th>' +
		'<th>Acreedor</th>' +
		'<th>N.° doc</th>' +
		'<th>Día de pago</th>' +
		'<th>F. Vencimiento</th>' +
		'<th>Moneda</th>' +
		'<th>Programado</th>' +
		'<th>Centro de costos</th>' +
		'</tr>' +
		'</thead>'
    );
    //console.log("limpiar tabla comprobantes");
}

function sec_det_contrato_verComprobanteVista(nombre_archivo, extension){
	$('#sec_det_div_modal_archivo').modal({backdrop: 'static', keyboard: false});
	var midocu = nombre_archivo;
	var micarpeta = 'comprobantes_de_pago';
	var tipodocumento = extension;
	var html = '';
	var titulo = '';
	if (tipodocumento == 'html') 
	{
		$('#sec_det_div_contenido_archivo').hide();
	} 
	else if (tipodocumento == 'pdf') 
	{

		$('#sec_det_div_heading_value_archivo').html(titulo);
		$('#sec_det_div_contenido_archivo').show();
		$('#sec_det_div_Ver_Pdf').show();
		$('#sec_det_divVisorPdfPrincipal').show();
		$('#sec_det_divVerImagenFullPantallaModal').hide();
		$('#sec_det_divVisorImagen').hide();

		html = '<iframe src="files_bucket/contratos/' + micarpeta + '/' + midocu + '" class="col-xs-12 col-md-12 col-sm-12" height="580"></iframe>';
		var htmlModal = '<iframe src="files_bucket/contratos/' + micarpeta + '/' + midocu + '" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';

		$('#sec_det_divVisorPdfPrincipal').html(html);
		$('#divVisorPdfModal').html(htmlModal);

	} 
	else if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') 
	{

		$('#sec_det_div_heading_value_archivo').html(titulo);
		$('#sec_det_div_contenido_archivo').show();
		$('#sec_det_div_Ver_Pdf').hide();
		$('#sec_det_divVisorPdfPrincipal').hide();
		$('#sec_det_divVerImagenFullPantallaModal').show();
		$('#sec_det_divVisorImagen').show();

		html = '<img src="files_bucket/contratos/' + micarpeta + '/' + midocu + '" class="img-responsive" style="border: 1px solid;">';
		$('#sec_det_divVisorImagen').html(html);
	}else{
		$('#sec_det_titulo_comprobante_pago').html('LA PROGRAMACION NO TIENE COMPROBANTE DE PAGO REGISTRADO');
	}
}

function sec_det_contrato_tesoreria_verFullImagenComprobante() {
	var archivo = $('#sec_det_midocu').val();
	console.log(archivo);
	if(archivo == undefined){
		return;
	}
	var image = new Image();
	image.src = 'files_bucket/contratos/comprobantes_de_pago/' + archivo;
	var viewer = new Viewer(image, 
	{
		hidden: function () {
			viewer.destroy();
		},
	});
	viewer.show()
	//console.log(viewer);
}
