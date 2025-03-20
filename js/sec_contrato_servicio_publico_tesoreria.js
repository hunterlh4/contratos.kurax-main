// INICIO DECLARACION DE VARIABLES ARRAY

let sec_contrato_servicio_publico_tesoreria_meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
let sec_contrato_servicio_publico_tesoreria_meses_abrev = ["En", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

var array_recibos_programacion_de_pago = [];

// FIN DECLARACION DE VARIABLES ARRAY

function sec_contrato_servicio_publico_tesoreria()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_contrato_servicio_publico_tesoreria_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	// INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_contrato_servicio_publico_tesoreria_datepicker')
        .datepicker({
            dateFormat:'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        })
        .on("change", function(ev) {
            $(this).datepicker('hide');
            var newDate = $(this).datepicker("getDate");
            $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
            // localStorage.setItem($(this).atrr("id"),)
        });
    // FIN FORMATO Y BUSQUEDA DE FECHA

    contrato_servicio_publico_tesoreria_item_detalle_programacion_get_comprobante_pago($('#contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago'));
    contrato_servicio_publico_tesoreria_item_detalle_programacion_get_comprobante_pago_edit($('#contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit'));
}

$('#contrato_servicio_publico_tesoreria_tipo_solicitud').change(function () 
{
	$("#contrato_servicio_publico_tesoreria_listar_servicios_publicos_tabla").hide();
	$("#contrato_servicio_publico_tesoreria_reporte_btn_listar_servicios_publicos").hide();
	$("#contrato_servicio_publico_tesoreria_programacion_div_tabla").hide();

	$(".tipo_recibo").hide();
	$(".contrato_servicio_publico_tesoreria_div_fechas").hide();
	
	var selectValor = $(this).val();

	if(selectValor == 0)
	{
		alertify.error('Seleccione Tipo Solicitud:',5);
		$("#contrato_servicio_publico_tesoreria_tipo_solicitud").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_tesoreria_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}
	else if(selectValor == 3)
	{
		// RECIBOS SERVICIO PUBLICOS
		$("#contrato_servicio_publico_tesoreria_buscar_por").val("0").trigger("change.select2");
		sec_contrato_servicio_publico_tesoreria_obtener_supervisores(0, 0);
		$("#contrato_servicio_publico_tesoreria_label_fecha_inicio").html("Fecha Inicio Vencimiento");
		$("#contrato_servicio_publico_tesoreria_label_fecha_fin").html("Fecha Fin Vencimiento");
		$(".tipo_recibo").show();
		$(".contrato_servicio_publico_tesoreria_div_fechas").hide();
		$("#contrato_servicio_publico_tesoreria_div_periodo").hide();
	}
	else
	{
		// PLANTILLA SERVICIO PUBLICOS
		$("#contrato_servicio_publico_tesoreria_label_fecha_inicio").html("Fecha inicio de la programación:");
		$("#contrato_servicio_publico_tesoreria_label_fecha_fin").html("Fecha fin de la programación:");
		$(".contrato_servicio_publico_tesoreria_div_fechas").show();
	}
});

function sec_contrato_servicio_publico_tesoreria_obtener_supervisores(zona_id, mostrar) 
{	
	var data = {
		"accion": "sec_contrato_servicio_publico_tesoreria_obtener_supervisores",
		"zona_id": zona_id
	}
	
	var array_resultado = [];
	
	$.ajax({
		url: "/sys/set_contrato_servicio_publico_tesoreria.php",
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
			
			if (parseInt(respuesta.http_code) == 200) 
			{
				array_resultado.push(respuesta.result);
			
				var html = '<option value="0">Todos</option>';

				for (var i = 0; i < array_resultado[0].length; i++) 
				{
					html += '<option value=' + array_resultado[0][i].id  + '>' + array_resultado[0][i].nombre + '</option>';
				}

				$("#contrato_servicio_publico_tesoreria_supervisor").html(html).trigger("change");

				if(mostrar == 1)
				{
					setTimeout(function() {
						$('#contrato_servicio_publico_tesoreria_supervisor').select2('open');
					}, 500);	
				}

				return false;
			}
			else(parseInt(respuesta.http_code) == 400) 
			{
				swal({
					title: respuesta.status,
					text: respuesta.result,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
		},
		error: function() {}
	});
}

$('#contrato_servicio_publico_tesoreria_supervisor').change(function () 
{
	var selectValor = $(this).val();

	sec_contrato_servicio_publico_tesoreria_obtener_locales(selectValor, 1);
});

function sec_contrato_servicio_publico_tesoreria_obtener_locales(param_supervisor, mostrar) 
{
	param_zona = $("#contrato_servicio_publico_param_zona").val();

	var data = {
		"accion": "sec_contrato_servicio_publico_tesoreria_obtener_locales",
		"param_zona": param_zona,
		"param_supervisor": param_supervisor
	}
	
	var array_resultado = [];
	
	$.ajax({
		url: "/sys/set_contrato_servicio_publico_tesoreria.php",
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
			
			if (parseInt(respuesta.http_code) == 200) 
			{
				array_resultado.push(respuesta.result);
			
				var html = '<option value="0">Todos</option>';

				for (var i = 0; i < array_resultado[0].length; i++) 
				{
					html += '<option value=' + array_resultado[0][i].id  + '>' + array_resultado[0][i].nombre + '</option>';
				}

				$("#contrato_servicio_publico_tesoreria_local").html(html).trigger("change");

				if(mostrar == 1)
				{
					setTimeout(function() {
						$('#contrato_servicio_publico_tesoreria_local').select2('open');
					}, 500);	
				}

				return false;
			}
			else(parseInt(respuesta.http_code) == 400) 
			{
				swal({
					title: respuesta.status,
					text: respuesta.result,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
		},
		error: function() {}
	});
}

$('#contrato_servicio_publico_tesoreria_buscar_por').change(function () 
{
	$("#contrato_servicio_publico_tesoreria_div_periodo").hide();
	$(".contrato_servicio_publico_tesoreria_div_fechas").hide();

	var selectValor = $(this).val();

	if(selectValor == 1)
	{
		$("#contrato_servicio_publico_tesoreria_div_periodo").show();
		sec_contrato_servicio_publico_tesoreria_cargar_meses();
	}
	else if(selectValor == 2)
	{
		$(".contrato_servicio_publico_tesoreria_div_fechas").show();
	}
	else
	{
		alertify.error('Seleccione Buscar Por:',5);
		$("#contrato_servicio_publico_tesoreria_buscar_por").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_tesoreria_buscar_por').select2('open');
		}, 500);

		return false;
	}
});

function sec_contrato_servicio_publico_tesoreria_cargar_meses()
{
	var date = new Date();
    var meses = [];

    date.setMonth(date.getMonth());
    var fecha_anio_ = date.toISOString().substring(0, 7);
    meses.push(fecha_anio_);

    //date.setMonth(date.getMonth() - 1);
    // Los ultimos 12 meses
    for (var c = 1; i <= 12; i++)
    {
        date.setMonth(date.getMonth() - c);
        var fecha_anio = date.toISOString().substring(0, 7);
        meses.push(fecha_anio)
    }

    $('#contrato_servicio_publico_tesoreria_periodo').append(
  		'<option value="0">Todos</option>'
  	);

    $.each(meses, function (ind, elem)
    { 
		
		$('#contrato_servicio_publico_tesoreria_periodo').append(
	  		'<option '+(ind == 0 ? 'selected':'' )+' value="' + elem + '">' + sec_contrato_servicio_publico_tesoreria_obtener_anio_mes_letras(elem) + '</option>'
	  	);
	}); 
}

function sec_contrato_servicio_publico_tesoreria_obtener_anio_mes_letras(fecha)
{
	if (fecha.length > 0)
	{
		var anio = fecha.substring(0,4);
		var mes = fecha.substring(5,7);
		mes = meses[mes - 1];
		var fecha_mes_anio = anio + " - " + mes;

		return fecha_mes_anio;
	}

	return '';
}

function contrato_servicio_publico_tesoreria_item_detalle_programacion_get_comprobante_pago(object)
{
	
	$(document).on('click', '#contrato_servicio_publico_tesoreria_item_detalle_programacion_btn_buscar_comprobante', function(event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {
		let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
		
		if($(this)[0].files.length <= 1)
		{
			const name = $(this).val().split(/\\|\//).pop();
			truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
		}

		$("#contrato_servicio_publico_tesoreria_item_detalle_programacion_txt_comprobante_archivo").html(truncated);
	});
}

function contrato_servicio_publico_tesoreria_item_detalle_programacion_get_comprobante_pago_edit(object){

	$(document).on('click', '#contrato_servicio_publico_tesoreria_item_detalle_programacion_btn_buscar_comprobante_edit', function(event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {
		let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
		if($(this)[0].files.length <= 1)
		{
			const name = $(this).val().split(/\\|\//).pop();
			truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
		}
		
		$("#contrato_servicio_publico_tesoreria_item_detalle_programacion_txt_comprobante_archivo_edit").html(truncated);
	});
}

function contrato_servicio_publico_tesoreria_btn_buscar()
{
	var param_tipo_solicitud = $("#contrato_servicio_publico_tesoreria_tipo_solicitud").val();

	if(param_tipo_solicitud == 0)
	{
		alertify.error('Seleccione Tipo Solictud',5);
		$("#contrato_servicio_publico_tesoreria_tipo_solicitud").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_tesoreria_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}
	else if(param_tipo_solicitud == 3)
	{
		contrato_servicio_publico_tesoreria_buscar_servicio_publico();
	}
	else
	{
		sec_contrato_servicio_publico_tesoreria_listar_programacion_pago();
	}
}

function contrato_servicio_publico_tesoreria_buscar_servicio_publico()
{
	
	if(sec_id == "contrato" && sub_sec_id == "servicio_publico_tesoreria")
	{
		var param_buscar_por = $("#contrato_servicio_publico_tesoreria_buscar_por").val();
		var param_tipo_recibo = $("#contrato_servicio_publico_tesoreria_tipo_recibo").val();
		var param_tipo_servicio = $("#contrato_servicio_publico_tesoreria_tipo_servicio").val();
		var param_empresa_arrendataria = $("#contrato_servicio_publico_tesoreria_empresa_arrendataria").val();
		var param_zona = $("#contrato_servicio_publico_tesoreria_param_zona").val();
		var param_supervisor = $("#contrato_servicio_publico_tesoreria_supervisor").val();
		var param_local = $("#contrato_servicio_publico_tesoreria_local").val();
		var param_fecha_incio = $("#contrato_servicio_publico_tesoreria_fecha_inicio").val();
		var param_fecha_fin = $("#contrato_servicio_publico_tesoreria_fecha_fin").val();
		var param_periodo = $("#contrato_servicio_publico_tesoreria_periodo").val();
		var param_estado = $("#contrato_servicio_publico_tesoreria_estado").val();

		if(param_buscar_por == 0)
		{
			alertify.error('Seleccione Buscar Por:',5);
			$("#contrato_servicio_publico_tesoreria_buscar_por").focus();
			setTimeout(function() {
				$('#contrato_servicio_publico_tesoreria_buscar_por').select2('open');
			}, 500);
			
			return false;
		}
		else if(param_buscar_por == 2)
		{
			if(param_fecha_incio == "")
			{
				alertify.error('Seleccione Fecha Inicio:',5);
				$("#contrato_servicio_publico_tesoreria_fecha_inicio").focus();

				return false;
			}

			if(param_fecha_fin == "")
			{
				alertify.error('Seleccione Fecha Fin:',5);
				$("#contrato_servicio_publico_tesoreria_fecha_fin").focus();

				return false;
			}
		}

		var data = {
			"accion": "contrato_servicio_publico_tesoreria_buscar_servicio_publico",
			"param_buscar_por" : param_buscar_por,
			"param_tipo_recibo" : param_tipo_recibo,
			"param_tipo_servicio" : param_tipo_servicio,
			"param_empresa_arrendataria" : param_empresa_arrendataria,
			"param_zona" : param_zona,
			"param_supervisor" : param_supervisor,
			"param_local" : param_local,
			"param_fecha_incio" : param_fecha_incio,
			"param_fecha_fin" : param_fecha_fin,
			"param_periodo" : param_periodo,
			"param_estado" : param_estado
		}

		$("#contrato_servicio_publico_tesoreria_listar_servicios_publicos_tabla").show();
		$("#contrato_servicio_publico_tesoreria_reporte_btn_listar_servicios_publicos").show();
		
		tabla = $("#contrato_servicio_publico_tesoreria_listar_servicios_publicos_datatable").dataTable(
		{
			language:{
				"decimal":        "",
				"emptyTable":     "No existen registros",
				"info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
				"infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
				"infoFiltered":   "(filtered from _MAX_ total entradas)",
				"infoPostFix":    "",
				"thousands":      ",",
				"lengthMenu":     "Mostrar _MENU_ entradas",
				"loadingRecords": "Cargando...",
				"processing":     "Procesando...",
				"search":         "Filtrar:",
				"zeroRecords":    "Sin resultados",
				"paginate": {
					"first":      "Primero",
					"last":       "Ultimo",
					"next":       "Siguiente",
					"previous":   "Anterior"
				},
				"aria": {
					"sortAscending":  ": activate to sort column ascending",
					"sortDescending": ": activate to sort column descending"
				}
			},
			"aProcessing" : true,
			"aServerSide" : true,

			"ajax" :
			{
				url : "/sys/set_contrato_servicio_publico_tesoreria.php",
				data : data,
				type : "POST",
				dataType : "json",
				beforeSend: function() {
					loading("true");
				},
				complete: function() {
					loading();
				},
				error : function(e)
				{
					console.log(e.responseText);
				}
			},
			columnDefs: [
				{
					className: 'text-center',
					targets: [0, 4, 5]
				}
			],
			"columns" : [
				{"aaData" : "0"},
				{"aaData" : "1"},
				{"aaData" : "2"},
				{"aaData" : "3"},
				{"aaData" : "4"},
				{"aaData" : "5"},
				{"aaData" : "6"},
				{"aaData" : "7"},
				{"aaData" : "8"},
				{"aaData" : "9"},
				{"aaData" : "10"},
				{"aaData" : "11"},
				{"aaData" : "12"}
			],
			"bDestroy" : true,
			aLengthMenu:[10, 20, 30, 40, 50, 100],
			scrollX: true,
			scrollCollapse: true
		}).DataTable();

		setTimeout(function(){
			$('#contrato_servicio_publico_tesoreria_tipo_solicitud').select2('open');
		}, 500);
	}
	else
	{
		alertify.error('No se encuentra en la vista correspondientea la búsqueda.',5);
		return false;
	}
}

$("#contrato_servicio_publico_tesoreria_reporte_btn_listar_servicios_publicos").on('click', function () 
{
	var param_buscar_por = $("#contrato_servicio_publico_tesoreria_buscar_por").val();
	var param_tipo_recibo = $("#contrato_servicio_publico_tesoreria_tipo_recibo").val();
	var param_tipo_servicio = $("#contrato_servicio_publico_tesoreria_tipo_servicio").val();
	var param_empresa_arrendataria = $("#contrato_servicio_publico_tesoreria_empresa_arrendataria").val();
	var param_zona = $("#contrato_servicio_publico_tesoreria_param_zona").val();
	var param_supervisor = $("#contrato_servicio_publico_tesoreria_supervisor").val();
	var param_local = $("#contrato_servicio_publico_tesoreria_local").val();
	var param_fecha_incio = $("#contrato_servicio_publico_tesoreria_fecha_inicio").val();
	var param_fecha_fin = $("#contrato_servicio_publico_tesoreria_fecha_fin").val();
	var param_periodo = $("#contrato_servicio_publico_tesoreria_periodo").val();
	var param_estado = $("#contrato_servicio_publico_tesoreria_estado").val();

    if(param_buscar_por == 0)
	{
		alertify.error('Seleccione Buscar Por:',5);
		$("#contrato_servicio_publico_param_buscar_por").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_param_buscar_por').select2('open');
		}, 500);

		return false;
	}
	else if(param_buscar_por == 2)
	{
		if(param_fecha_incio == "")
		{
			alertify.error('Seleccione Fecha Inicio:',5);
			$("#contrato_servicio_publico_param_fecha_inicio").focus();

			return false;
		}

		if(param_fecha_fin == "")
		{
			alertify.error('Seleccione Fecha Fin:',5);
			$("#contrato_servicio_publico_param_fecha_fin").focus();

			return false;
		}
	}

	var data = {
		"accion": "contrato_servicio_publico_tesoreria_reporte_btn_listar_servicios_publicos",
		"param_buscar_por" : param_buscar_por,
		"param_tipo_recibo" : param_tipo_recibo,
		"param_tipo_servicio" : param_tipo_servicio,
		"param_empresa_arrendataria" : param_empresa_arrendataria,
		"param_zona" : param_zona,
		"param_supervisor" : param_supervisor,
		"param_local" : param_local,
		"param_fecha_incio" : param_fecha_incio,
		"param_fecha_fin" : param_fecha_fin,
		"param_periodo" : param_periodo,
		"param_estado" : param_estado
	}

    $.ajax({
        url: "/sys/set_contrato_servicio_publico_tesoreria.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            let obj = JSON.parse(resp);
            auditoria_send({ "respuesta": "contrato_servicio_publico_reporte_btn_listar_servicios_publicos", "data": obj });

            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function sec_contrato_servicio_publico_tesoreria_listar_programacion_pago()
{
    if(sec_id == "contrato" && sub_sec_id == "servicio_publico_tesoreria")
    {
        var param_tipo_solicitud = $("#contrato_servicio_publico_tesoreria_tipo_solicitud").val();
        var param_fecha_inicio = $("#contrato_servicio_publico_tesoreria_fecha_inicio").val();
        var param_fecha_fin = $("#contrato_servicio_publico_tesoreria_fecha_fin").val();

        if (parseInt(param_tipo_solicitud) == 0) 
		{
			alertify.error('Seleccione Tipo Solicitud',5);
			$('#contrato_servicio_publico_tesoreria_tipo_solicitud').focus();
			$('#contrato_servicio_publico_tesoreria_tipo_solicitud').select2('open');
			return false;
		}

        var data = {
            "accion": "sec_contrato_servicio_publico_tesoreria_listar_programacion_pago",
            "param_tipo_solicitud": param_tipo_solicitud,
            "param_fecha_inicio": param_fecha_inicio,
            "param_fecha_fin": param_fecha_fin
        }

        $("#contrato_servicio_publico_tesoreria_programacion_div_tabla").show();
        
        tabla = $("#contrato_servicio_publico_tesoreria_programacion_datatable").dataTable(
        {
            language:{
                "decimal":        "",
                "emptyTable":     "No existen registros",
                "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered":   "(filtered from _MAX_ total entradas)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing":     "Procesando...",
                "search":         "Filtrar:",
                "zeroRecords":    "Sin resultados",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Ultimo",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                	}
                },
                "aProcessing" : true,
                "aServerSide" : true,

                "ajax" :
                {
                    url : "/sys/set_contrato_servicio_publico_tesoreria.php",
                    data : data,
                    type : "POST",
                    dataType : "json",
                    beforeSend: function() {
                        loading("true");
                    },
                    complete: function() {
                        loading();
                    },
                    error : function(e)
                    {
                        console.log(e.responseText);
                    }
                },
                "bDestroy" : true,
                aLengthMenu:[10, 20, 30, 40, 50],
                "order" : 
                [
                    0, "desc"
                ]
            }
        ).DataTable();
    }
    else
    {
    	alertify.error('Error!!, vuelve a refrescar la página',5);
		return false;
    }
}

$("#contrato_servicio_publico_tesoreria_item_atencion_btn_buscar_recibos_pendiente_pago").click(function ()
{

	$("#servicio_publico_item_atencion_div_recibos_pendiente_pago_en_la_programacion").hide();
	
	var param_tipo_solicitud = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud").val();
	var param_tipo_empresa = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa").val();

	array_recibos_programacion_de_pago = [];
	
	if (parseInt(param_tipo_solicitud) == 0) 
	{
		alertify.error('Seleccione Tipo Solicitud',5);
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud').focus();
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud').select2('open');
		return false;
	}

	if (parseInt(param_tipo_empresa) == 0) 
	{
		alertify.error('Seleccione Empresa',5);
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa').focus();
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa').select2('open');
		return false;
	}
	
	contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago();

});

function contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago()
{
	
	var tipo_consulta = 1;
	var param_tipo_solicitud = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud").val();
	var param_tipo_solicitud_nombre = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud option:selected").text();
	var param_tipo_empresa = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa").val();
	var param_tipo_servicio = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_servicio").val();

	var data = {
		"accion": "contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago",
		"param_tipo_consulta": tipo_consulta,
		"param_tipo_solicitud": param_tipo_solicitud,
		"param_tipo_solicitud_nombre": param_tipo_solicitud_nombre,
		"param_tipo_empresa": param_tipo_empresa,
		"param_tipo_servicio":param_tipo_servicio,
		"ids_recibos": JSON.stringify(array_recibos_programacion_de_pago)
	}

	auditoria_send({ "proceso": "contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago", "data": data });

	$.ajax({
		url: "/sys/set_contrato_servicio_publico_tesoreria.php",
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
			auditoria_send({ "respuesta": "contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#servicio_publico_item_atencion_div_recibos_pendiente_pago').html(respuesta.result);
				$("#servicio_publico_item_atencion_div_recibos").show();
				return false;
			}
		},
		error: function() {}
	});

	$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud').select2('open');
	return false;
}

function contrato_servicio_publico_tesoreria_item_atencion_agregar_recibo_a_la_programacion_pagos(recibo_id) 
{
	$("#servicio_publico_item_atencion_div_recibos_pendiente_pago_en_la_programacion").show();
	
	if (array_recibos_programacion_de_pago.includes(recibo_id) === false)
	{
		array_recibos_programacion_de_pago.push(recibo_id)
	}
	
	contrato_servicio_publico_tesoreria_item_atencion_actualiza_recibo_pendiente_atencion_pago();
	contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago();
}

function contrato_servicio_publico_tesoreria_item_atencion_agregar_varios_recibo_a_la_programacion_pagos(...recibo_id) 
{
	$("#servicio_publico_item_atencion_div_recibos_pendiente_pago_en_la_programacion").show();

    var i;
    for(i = 0; i < recibo_id.length; i++)
    {
		if (array_recibos_programacion_de_pago.includes(recibo_id[i]) === false)
		{
			array_recibos_programacion_de_pago.push(recibo_id[i])
		}
    }

    contrato_servicio_publico_tesoreria_item_atencion_actualiza_recibo_pendiente_atencion_pago();
	contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago();
}

function contrato_servicio_publico_tesoreria_item_atencion_quitar_recibo_a_la_programacion_pagos(recibo_id)
{
	
	const index = array_recibos_programacion_de_pago.indexOf(recibo_id);
	if (index > -1) 
	{
		array_recibos_programacion_de_pago.splice(index, 1);
	}
	
	contrato_servicio_publico_tesoreria_item_atencion_actualiza_recibo_pendiente_atencion_pago();
	contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago();
}

function contrato_servicio_publico_tesoreria_item_atencion_quitar_varios_recibo_a_la_programacion_pagos(...recibo_id) 
{
    var i;
    for(i = 0; i < recibo_id.length; i++)
    {
		const index = array_recibos_programacion_de_pago.indexOf(recibo_id[i]);
		if (index > -1) 
		{
			array_recibos_programacion_de_pago.splice(index, 1);
		}
		
    }
    
    contrato_servicio_publico_tesoreria_item_atencion_actualiza_recibo_pendiente_atencion_pago();
	contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago();
}

function contrato_servicio_publico_tesoreria_item_atencion_actualiza_recibo_pendiente_atencion_pago()
{
	
	var tipo_consulta = 2;
	var param_tipo_solicitud = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud").val();
	var param_tipo_solicitud_nombre = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud option:selected").text();

	var data = {
		"accion": "contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago",
		"param_tipo_consulta": tipo_consulta,
		"param_tipo_solicitud": param_tipo_solicitud,
		"param_tipo_solicitud_nombre": param_tipo_solicitud_nombre,
		"ids_recibos": JSON.stringify(array_recibos_programacion_de_pago)
	}

	auditoria_send({ "proceso": "contrato_servicio_publico_tesoreria_item_atencion_recibo_pendiente_atencion_pago", "data": data });
	$.ajax({
		url: "/sys/set_contrato_servicio_publico_tesoreria.php",
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
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#servicio_publico_item_atencion_div_recibos_pendiente_pago_en_la_programacion').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});
}

function contrato_servicio_publico_tesoreria_item_atencion_guardar_programacion($num_tipo_grabacion)
{	
	
	var accion = '';
	var programacion_id_edit = $('#programacion_id_edit').val();
	var param_tipo_solicitud = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud").val();
	var param_tipo_empresa = $("#contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa").val();

	var txt_titulo_pregunta = "";

	if (parseInt(param_tipo_solicitud) == 0) 
	{
		alertify.error('Seleccione el banco',5);
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud').focus();
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_solicitud').select2('open');
		return false;
	}

	if (parseInt(param_tipo_empresa) == 0) 
	{
		alertify.error('Seleccione Empresa',5);
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa').focus();
		$('#contrato_servicio_publico_tesoreria_item_atencion_tipo_empresa').select2('open');
		return false;
	}

	if (array_recibos_programacion_de_pago.length == 0)
	{
		alertify.error('Tiene que agregar al menos un registro en la programación',5);
		return false;
	}

	if ($num_tipo_grabacion == '1') 
	{
		txt_titulo_pregunta = "Guardar";
		accion = 'contrato_servicio_publico_tesoreria_item_atencion_guardar_programacion_de_pago';
	} 
	else if ($num_tipo_grabacion == '2') 
	{
		txt_titulo_pregunta = "Editar";
		accion = 'contrato_servicio_publico_tesoreria_item_atencion_editar_programacion_de_pago';
	}
	else
	{
		alertify.error('¡Ups, ocurrio un error, vuelve a refrescar la pagina!',5);
		return false;
	}

	swal(
	{
		title: '¿Está seguro de '+txt_titulo_pregunta+' la Programación?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{
			var data = {
				"accion": accion,
				"param_tipo_solicitud": param_tipo_solicitud,
				"param_tipo_empresa": param_tipo_empresa,
				"ids_recibos": JSON.stringify(array_recibos_programacion_de_pago),
				"programacion_id_edit": programacion_id_edit
			}

			auditoria_send({ "proceso": "contrato_servicio_publico_tesoreria_item_atencion_guardar_programacion", "data": data });

			$.ajax({
				url: "/sys/set_contrato_servicio_publico_tesoreria.php",
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
					
					if(parseInt(respuesta.http_code) == 400)
					{
						swal({
							title: "Error al guardar programación de pagos.",
							text: respuesta.result,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
					else if(parseInt(respuesta.http_code) == 200)
					{
						swal({
							title: "Registro exitoso",
							text: "La programación de pago fue registrada exitosamente",
							html:true,
							type: "success",
							timer: 3000,
							closeOnConfirm: false,
							showCancelButton: false
						});
						setTimeout(function() {
							window.location.href = "?sec_id=contrato&sub_sec_id=servicio_publico_tesoreria";
							return false;
						}, 3000);
						return false;
					}
				},
				error: function() {}
			});
		}
	});
}

$(document).on('submit', "#form_contrato_servicio_publico_tesoreria_item_detalle_programacion_guardar_comprobante_pago", function(e) 
{
	e.preventDefault();
	
	var tesoreria_comprobante_pago = document.getElementById("contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago");
	var tesoreria_fecha_comprobante_pago = $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago").val();
	var servicio_publico_programacion_id = $("#servicio_publico_programacion_id").val();
	
	if(tesoreria_comprobante_pago.files.length > 0)
    {
        for(var i = 0; i < tesoreria_comprobante_pago.files.length; i ++)
        {
            if(tesoreria_comprobante_pago.files[i].size > 1000000)
            {
                alertify.error(`EL Archivo ${tesoreria_comprobante_pago.files[i].name} debe pesar menos de 1MB`,5);
                return false;
            }
        }
    }
    else
    {
        alertify.error('Seleccione el comprobante',5);
        $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago").focus();
        return false;
    }

    if(tesoreria_fecha_comprobante_pago == "")
    {
    	alertify.error('Seleccione la Fecha de Carga',5);
        $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago").focus();
        return false;
    }

	swal(
	{
		title: '¿Está seguro de registrar?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{

			var dataForm = new FormData($("#form_contrato_servicio_publico_tesoreria_item_detalle_programacion_guardar_comprobante_pago")[0]);
			dataForm.append("accion","contrato_servicio_publico_tesoreria_item_detalle_programacion_guardar_comprobante_pago");
			dataForm.append("tesoreria_fecha_comprobante_pago", tesoreria_fecha_comprobante_pago);
			dataForm.append("servicio_publico_programacion_id", servicio_publico_programacion_id);

			$.ajax({
				url: "sys/set_contrato_servicio_publico_tesoreria.php",
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

					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Registro exitoso",
							text: "Se registro exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        location.reload();
					    });

						setTimeout(function() {
							location.reload();
						}, 5000);

						return true;
					} 
					else {
						swal({
							title: "Error al guardar Solicitud",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function(){
					loading(false);
				}
			});
		}
	});
});

function contrato_servicio_publico_tesoreria_item_detalle_programacion_ver_comprobante_pago(tipo_documento, ruta_file) 
{
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="650"></iframe>';
		$('#contrato_servicio_publico_item_detalle_programacion_visor_pdf').html(htmlModal);

		$('#contrato_servicio_publico_item_detalle_programacion_div_visor_pdf_modal').modal('show');

	} 
	else if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') 
	{
		var image = new Image();
		image.src = ruta;
		var viewer = new Viewer(image, 
		{
			hidden: function () {
				viewer.destroy();
			},
		});
		// image.click();
		viewer.show();
	}
}

function contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante()
{
	$("#contrato_servicio_publico_tesoreria_item_detalle_programacion_mostrar_comprobante_div").hide();
	$("#contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_div").show();
}

function contrato_servicio_publico_tesoreria_item_detalle_programacion_mostrar_comprobante()
{
	$("#contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_div").hide();
	$("#contrato_servicio_publico_tesoreria_item_detalle_programacion_mostrar_comprobante_div").show();
}

$(document).on('submit', "#form_contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_pago", function(e) 
{
	e.preventDefault();
	
	var tesoreria_comprobante_pago_edit = document.getElementById("contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit");

	var tesoreria_fecha_comprobante_pago_edit = $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago_edit").val();
	var tesoreria_motivo_comprobante_pago_edit = $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_motivo_comprobante_pago_edit").val().trim();
	var servicio_publico_programacion_id = $("#servicio_publico_programacion_id").val();
	

	if(tesoreria_comprobante_pago_edit.files.length > 0)
    {
        for(var i = 0; i < tesoreria_comprobante_pago_edit.files.length; i ++)
        {
            if(tesoreria_comprobante_pago_edit.files[i].size > 1000000)
            {
                alertify.error(`EL Archivo ${tesoreria_comprobante_pago_edit.files[i].name} debe pesar menos de 1MB`,5);
                return false;
            }
        }
    }
    else
    {
        alertify.error('Seleccione el comprobante',5);
        $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_comprobante_pago_edit").focus();
        return false;
    }

    if(tesoreria_fecha_comprobante_pago_edit == "")
    {
    	alertify.error('Seleccione la Fecha de Carga',5);
        $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_fecha_comprobante_pago_edit").focus();
        return false;
    }

	if(tesoreria_motivo_comprobante_pago_edit.length == 0)
	{
		alertify.error('Ingrese Motivo',5);
		$("#tesoreria_motivo_comprobante_pago_edit").focus();
		return false;
	}

	swal(
	{
		title: '¿Está seguro de guardar?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{
			var dataForm = new FormData($("#form_contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_pago")[0]);
			dataForm.append("accion","contrato_servicio_publico_tesoreria_item_detalle_programacion_editar_comprobante_pago");
			dataForm.append("tesoreria_fecha_comprobante_pago_edit", tesoreria_fecha_comprobante_pago_edit);
			dataForm.append("tesoreria_motivo_comprobante_pago_edit", tesoreria_motivo_comprobante_pago_edit);
			dataForm.append("servicio_publico_programacion_id", servicio_publico_programacion_id);

			$.ajax({
				url: "sys/set_contrato_servicio_publico_tesoreria.php",
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

					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Registro exitoso",
							text: "Se registro exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        location.reload();
					    });

						setTimeout(function() {
							location.reload();
						}, 5000);

						return true;
					} 
					else {
						swal({
							title: "Error al guardar Solicitud",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);

});

function contrato_servicio_publico_tesoreria_item_detalle_plantilla_concar_excel(programacion_id)
{
	
    var programacion_tipo_solicitud_id = $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_tipo_solicitud_id").val();
    var programacion_num_comprobante = $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_num_comprobante").val();

	var programacion_num_movimiento = $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_num_movimiento").val();
	var programacion_servicio = $("#form_modal_sec_mantenimiento_razon_social_param_estado_vale").val();

	if(programacion_num_movimiento == "" && programacion_tipo_solicitud_id ==2)
	{
        alertify.error('Ingrese el numero de movimiento',5);
        $("#contrato_servicio_publico_tesoreria_item_detalle_programacion_num_movimiento").focus();
        return false;
    }

    if(programacion_num_comprobante == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_boveda_num_comprobante").focus();
        return false;
    }

    var accion = "";
    if(programacion_tipo_solicitud_id == 1)
    {
    	//RECIBOS TOTALES
    	accion = "contrato_servicio_publico_tesoreria_item_detalle_plantilla_totales_concar_excel";
    }
    else if(programacion_tipo_solicitud_id == 2)
    {
    	//RECIBOS COMPARTIDOS
    	accion = "contrato_servicio_publico_tesoreria_item_detalle_plantilla_compartidos_concar_excel";
    }

    var data = {
        "accion": accion,
        "programacion_id" : programacion_id,
        "programacion_num_comprobante" : programacion_num_comprobante,
		"programacion_num_movimiento" : programacion_num_movimiento,
		"programacion_servicio" : programacion_servicio
    }

    $.ajax({
        url: "/sys/set_contrato_servicio_publico_tesoreria.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
			console.log(resp)
            
            let obj = JSON.parse(resp);
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);    
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el Concar",
                    text: obj.ruta_archivo,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Ponerse en contacto con Soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            
        },
        error: function(resp, status) {

        }
    });
}

$("#contrato_servicio_publico_tesoreria_tabla_form_programacion_detalle").off("click").on("click", ".contrato_servicio_publico_tesoreria_programacion_detalle_btn_guardar_transferencia", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-programacion_detalle_id");
	var num_transferencia_anterior = $(this).closest("tr").attr("data-programacion_detalle_num_transferencia");
	var num_transferencia_banco = $("input[name='num_transferencia_banco']",tr).val();
	var url = "sys/set_contrato_servicio_publico_tesoreria.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "contrato_servicio_publico_tesoreria_item_detalle_guardar_programacion_detalle_num_transferencia",
			detalle_id : detalle_id,
			num_transferencia_banco : num_transferencia_banco
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
			
			if(typeof resp.error != "undefined")
			{
				swal({
					title: 'Editar Nº Transferencia',
					text: resp.mensaje,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(num_transferencia_anterior);
						},200);
					}
				);
	    		return false;
	    	}

        	swal({
				title: 'Nº Transferencia Actualizada',
				text: resp.mensaje,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})

function contrato_servicio_publico_tesoreria_item_detalle_programacion_anular_detalle(programacion_id)
{
	
	swal(
	{
		title: '¿Está seguro de quitar el recibo?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{
			var data = {
			"accion": "contrato_servicio_publico_tesoreria_item_detalle_programacion_anular_detalle",
			"programacion_detalle_id" : programacion_id
			}

			$.ajax({
				url : "/sys/set_contrato_servicio_publico_tesoreria.php",
				type : "POST",
				data : data,
				//contentType : false,
				//processData : false,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success : function(resp)
				{
					
					var respuesta = JSON.parse(resp);

					if (respuesta.status) 
					{
						swal({
							title: "Listo!",
							text: respuesta.message,
							type: "success",
							timer: 5000,
							closeOnConfirm: false
						},
						function (isConfirm) {
					        window.location.reload();
					    });
						
						setTimeout(function() {
							window.location.reload();
						}, 1000);

						return true;
					}
					else
					{
						swal({
							title: "Error!",
							text:  respuesta.message,
							type: "warning",
							timer: 5000,
							closeOnConfirm: false
						});
					}

					//tabla.ajax.reload();
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);
}

function contrato_servicio_publico_tesoreria_item_detalle_programacion_exportar_text_detalle(programacion_id) {
    var data = {
        "accion": "exportar_text_detalle",
        "programacion_id": programacion_id
    }

    $.ajax({
        url: "/sys/get_contrato_servicio_publico_tesoreria.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {    
            let obj = JSON.parse(resp);
            if (parseInt(obj.estado_archivo) == 1) {
                var link = document.createElement("a");
                link.href = obj.ruta_archivo;
                link.download = obj.filename;
                link.target = "_blank";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                loading(false);
            } else if (parseInt(obj.estado_archivo) == 0) {
                swal({
                    title: "Error al Generar el text del detalle",
                    text: obj.error,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            } else {
                swal({
                    title: "Error",
                    text: "Ponerse en contacto con Soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
        },
        error: function(resp, status) {
            // Manejar errores aquí si es necesario
        }
    });
}

function sec_contrato_servicio_publico_tesoreria_item_detalle_programacion_btn_export(programacion_id, tipo_solicitud_id)
{
	
	accion = '';
	
	if(tipo_solicitud_id == 1)
	{
		accion = 'sec_contrato_servicio_publico_tesoreria_item_detalle_programacion_totales';
	}
	else
	{
		accion = 'sec_contrato_servicio_publico_tesoreria_item_detalle_programacion_compartidos';
	}

	var data = {
        "accion": accion,
        "programacion_id": programacion_id
    }

    $.ajax({
        url: "/sys/set_contrato_servicio_publico_tesoreria.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            let obj = JSON.parse(resp);
            auditoria_send({ "respuesta": "sec_contrato_servicio_publico_tesoreria_item_detalle_programacion_btn_export", "data": obj });

            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function(resp, status) {

        }
    });
}

