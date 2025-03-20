function sec_contrato_procesar_programacion(){
	sec_contrato_procesar_programacion_obtener_datos();
	sec_contrato_procesar_programacion_listar_subdiarios();
}

function sec_contrato_procesar_programacion_modal_confirmar_proceso(){	
	var data = sec_contrato_procesar_programacion_validar_variables();

	if (!data) {
		return false;
	}

	$('#sec_proc_modal_confirmar_proceso').modal({backdrop: 'static', keyboard: false});
}

function sec_contrato_procesar_programacion_validar_variables(){
	//debugger;
	var subdiario = $('#sec_proc_select_subdiario').val();
	if (parseInt(subdiario) == 0) {
		alertify.error('Seleccione el subdiario',5);
		$('#subdiario').select2('open');
		return false;
	}
	return true;
}

function sec_contrato_procesar_programacion_obtener_datos(){
	//debugger;
	var programacion_id = $('#sec_proc_id_programacion_seleccionada').val();
	if(programacion_id > 0){
		array_tabla_detalle_programacion = [];
		var data = {
	        "accion": "sec_contrato_procesar_programacion_obtener_datos",
	        "programacion_id":programacion_id
	    }
	    //debugger;
	    auditoria_send({ "proceso": "sec_contrato_procesar_programacion_obtener_datos", "data": data });
	    $.ajax({
	        url: "sys/get_contrato_procesar_programacion.php",
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
	                    $('#sec_proc_num_programacion').val(item.programacion_numero);
	                    $('#sec_proc_concepto_programacion').val(item.concepto);
	                    $('#sec_proc_tipo_pago_programacion').val(item.tipo_pago);
	                    $('#sec_proc_banco_programacion').val(item.banco);
	                    $('#sec_proc_fecha_programacion').val(nuevoFormatofecha_programacion);
	                    $('#sec_proc_tipo_cambio_programacion').val(item.valor_cambio);
	                    $('#sec_proc_moneda_programacion').val(item.moneda);
	                    $('#sec_proc_situacion_programacion').val(item.etapa);
	                    
	                    $('#sec_proc_tabla_auditoria').append(
	                        '<tr>' +
	                        '<td>' + '<b>Elaborado por: </b>' + item.elaborado_por + '</td>' +
	                        '<td>' + '<b>Editado por: </b>' + item.editado_por + '</td>' +
	                        '</tr>' +
	                        '<tr>' +
	                        '<td>' + '<b>Fecha elaboración: </b>' + item.fecha_elaboracion + '</td>' +
	                        '<td>' + '<b>Fecha edición: </b>' + item.fecha_edicion + '</td>' +
	                        '</tr>'
	                    );
	                    nombre_archivo = "'" + item.nombre_archivo + "'";
	                    extension_archivo = "'" + item.extension_archivo + "'";
	                });
	                return false;
	            }      
	        },
	        error: function() {}
	    });
	}
}

function sec_contrato_procesar_programacion_listar_subdiarios(){
	array_tabla_subdiarios = [];
	var data = {
        "accion": "sec_contrato_procesar_programacion_listar_subdiarios"
    }
    //debugger;
    auditoria_send({ "proceso": "sec_contrato_procesar_programacion_listar_subdiarios", "data": data });
	$.ajax({
        url: "sys/get_contrato_procesar_programacion.php",
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
                    array_tabla_subdiarios.push(item);
                    $('#sec_proc_select_subdiario').append(
                    	'<option value="0">- Seleccione -</option>' +
                        '<option value="' + item.subdiario_id + '">' + item.descripcion + '</option>'
                    );
                });
                return false;
            }      
        },
        error: function() {}
    });
}

function sec_contrato_procesar_programacion_procesar(){
	//debugger;
	var programacion_id = $('#sec_proc_id_programacion_seleccionada').val();
	var subdiario_id = $('#sec_proc_id_programacion_seleccionada').val();
    
    var dataForm = new FormData($("#form_eliminar_programacion")[0]);
	dataForm.append("accion","sec_contrato_procesar_programacion_procesar");
	dataForm.append("programacion_id", programacion_id);
	dataForm.append("subdiario_id", subdiario_id);
	console.log(programacion_id);
	console.log(subdiario_id);

    auditoria_send({ "proceso": "sec_contrato_procesar_programacion_procesar", "data": dataForm });
	$.ajax({
		url: "sys/set_contrato_procesar_programacion.php",
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(data){
			var respuesta = JSON.parse(data);
			console.log(respuesta.query);
			auditoria_send({ "respuesta": "sec_contrato_procesar_programacion_procesar", "data": respuesta });
			if(parseInt(respuesta.http_code) == 200) {
				swal({
					title: "Proceso de programación exitosa",
					text: "La programación se procesó correctamente",
					html:true,
					type: "success",
					timer: 1000,
					closeOnConfirm: false,
					showCancelButton: false
				});
				setTimeout(function() {
					window.location.href = "?sec_id=contrato&sub_sec_id=tesoreria";
					return false;
				}, 1000);
			} else {
				swal({
					title: "Error al procesar la programación",
					text: respuesta.error,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
			}
		},
		complete: function(){
			loading(false);
		}
	});
}