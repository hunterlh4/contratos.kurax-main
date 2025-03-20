var fracc_manual_cuotas = [];
function sec_vale_fraccionamiento_manual() {

	$(".select2").select2({ width: "100%", placeholder: "- Seleccione una opcion -", });
	$(".sec_vale_fracc_manual_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	}).on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});

	sec_vale_fracc_manual_obtener_opciones("listar_empresas_por_usuario", $("#sec_vale_fracc_manual_empresa"));

	setTimeout(() => {
		sec_vale_fracc_manual_buscar_vale_descuento();
	}, 2000);

	
	$("#sec_vale_fracc_manual_empresa").change(function () {
		sec_vale_fracc_manual_obtener_zonas();
	});

	$("#frm-vale-descuento-btn-reset").click(function () {
		sec_vale_fracc_manual_resetear_form_vale_descuento();
	});
	
	$('#frm_fracc_manual_vale_descuento').submit(function (evt) {
		evt.preventDefault();

		var empresa = $('#sec_vale_fracc_manual_empresa').val();
		var zona = $('#sec_vale_fracc_manual_zona').val();
		var empleado = $('#sec_vale_fracc_manual_empleado').val();
		var dni = $('#sec_vale_fracc_manual_dni').val();
		var fecha_desde_vale = $('#sec_vale_fracc_manual_fecha_desde_vale').val();
		var fecha_hasta_vale = $('#sec_vale_fracc_manual_fecha_hasta_vale').val();
	

		if (empresa == null) {
			alertify.error("Seleccione al menos una empresa", 5);
			$("#sec_vale_fracc_manual_empresa").focus();
			$("#sec_vale_fracc_manual_empresa").select2("open");
			return false;
		}
		if (zona == null) {
			alertify.error("Seleccione al menos una zona", 5);
			$("#sec_vale_fracc_manual_zona").focus();
			$("#sec_vale_fracc_manual_zona").select2("open");
			return false;
		}
		if (fecha_desde_vale.length == 0) {
			alertify.error("Seleccione uan fecha", 5);
			$("#sec_vale_sinc_fecha_desde_vale").focus();
			$("#sec_vale_sinc_fecha_desde_vale").select2("open");
			return false;
		}
		if (fecha_hasta_vale.length == 0) {
			alertify.error("Seleccione uan fecha", 5);
			$("#sec_vale_sinc_fecha_hasta_vale").focus();
			$("#sec_vale_sinc_fecha_hasta_vale").select2("open");
			return false;
		}

		sec_vale_fracc_manual_buscar_vale_descuento();
	});

	
}

function sec_vale_fracc_manual_obtener_opciones(accion, select) {
	$.ajax({
		url: "/vales/controllers/DataController.php",
		type: "POST",
		data: { accion: accion }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			if (respuesta.status == 200) {
				var result = respuesta.result;
				var values = [];
				$(result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
					values.push(e.id);
				});
				$(select).val(values).trigger('change.select2');
				$(select).trigger('change');
				  
			}
			
		},
		error: function () {},
	});
}

function sec_vale_fracc_manual_obtener_zonas() {

	var empresa_id = $('#sec_vale_fracc_manual_empresa').val();
	if (empresa_id == null) {
		console.log("null")
		$("#sec_vale_fracc_manual_zona").find("option").remove().end();
		return false;
	}
	var data = {
		empresa_id : empresa_id,
		accion : 'listar_zonas_por_empresa_multiple'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("#sec_vale_fracc_manual_zona").find("option").remove().end();
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("#sec_vale_fracc_manual_zona").append(opcion);
				});
				$("#sec_vale_fracc_manual_zona").val(respuesta.value).trigger('change.select2');

			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_fracc_manual_obtener_estados() {

	var data = {
		accion : 'listar_estados_control_interno'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			console.log(respuesta)
			if (respuesta.status == 200) {
				$("#sec_vale_fracc_manual_estado").find("option").remove().end();
				$(respuesta.result.data).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("#sec_vale_fracc_manual_estado").append(opcion);
				});
				$("#sec_vale_fracc_manual_estado").val(respuesta.result.value).trigger('change.select2');

			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_fracc_manual_buscar_vale_descuento() {

	var empresa = $('#sec_vale_fracc_manual_empresa').val();
	var zona = $('#sec_vale_fracc_manual_zona').val();
	var empleado = $('#sec_vale_fracc_manual_empleado').val();
	var dni = $('#sec_vale_fracc_manual_dni').val();
	var fecha_desde_vale = $('#sec_vale_fracc_manual_fecha_desde_vale').val();
	var fecha_hasta_vale = $('#sec_vale_fracc_manual_fecha_hasta_vale').val();

	var data = {
		empresa : empresa,
		zona : zona,
		dni : dni,
		empleado : empleado,
		fecha_desde_vale : fecha_desde_vale,
		fecha_hasta_vale : fecha_hasta_vale,
		accion : 'listar_vales_fraccionamiento_manual'
	};

	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				fnc_render_table_fraccionamiento_manual(respuesta.result);
			}else{
				alertify.error(respuesta.message, 10);
			}
			return false;
		},
		error: function (error) {
			console.log(error);
		},
	});
}



function sec_vale_fracc_manual_resetear_form_vale_descuento() {

	$('#sec_vale_fracc_manual_empresa').val('0').trigger('change.select2');
	$('#sec_vale_fracc_manual_zona').val('0').trigger('change.select2');
	$('#sec_vale_fracc_manual_local').val('0').trigger('change.select2');
	$('#sec_vale_fracc_manual_solicitante').val('0').trigger('change.select2');
	$('#sec_vale_fracc_manual_empleado').val('0').trigger('change.select2');
	$('#sec_vale_fracc_manual_motivo').val('0').trigger('change.select2');
	//$('#sec_vale_fracc_manual_fecha_incidencia').val();
	$('#sec_vale_fracc_manual_monto').val('');
	$('#sec_vale_fracc_manual_observacion').val('');

}

function fnc_render_table_fraccionamiento_manual(data = []) {
	$("#tbl_vale_fraccionamiento_manual")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "nro_vale", className: "text-center" },
		  { data: "empresa", className: "text-left" }, 
		  { data: "zona", className: "text-left" },
		  { data: "local", className: "text-left" },
		  { data: "empleado", className: "text-left" },
		  { data: "dni_empleado", className: "text-left" },
		  { data: "motivo", className: "text-left" },
		  { data: "fecha_incidencia", className: "text-center" },
		  { data: "monto", className: "text-right" },
		  { data: "nro_cuotas", className: "text-center" },
		  { data: "observacion", className: "text-left" },
		  { data: "estado", className: "text-center" },
		  { data: "acciones", className: "text-center" },
		],
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
		  infoFiltered: "(filtered from _MAX_ total entradas)",
		  infoPostFix: "",
		  thousands: ",",
		  lengthMenu: "Mostrar _MENU_ entradas",
		  loadingRecords: "Cargando...",
		  processing: "Procesando...",
		  search: "Filtrar:",
		  zeroRecords: "Sin resultados",
		  paginate: {
			first: "Primero",
			last: "Ultimo",
			next: "Siguiente",
			previous: "Anterior",
		  },
		  aria: {
			sortAscending: ": activate to sort column ascending",
			sortDescending: ": activate to sort column descending",
		  },
		  buttons: {
			pageLength: {
				_: "Mostrar %d Resultados",
				'-1': "Tout afficher"
			}
		  }
		},
		scrollY: true,
		scrollX: true,
		dom: 'Bfrtip',
        buttons: [
            'pageLength',
			{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8,9,10,11]
                },
				title: 'Vales de Descuento - Fraccionamiento Manual'
            },
        ],
		columnDefs: [
            {
                targets: [10],
                visible: false
            }
        ]
	  })
	  .DataTable();
  

}



function sec_vale_fracc_manual_modal_fraccionamiento_manual(vale_id) {

	var data = {
		vale_id:vale_id,
		accion : 'listar_fraccionamientos_por_vale'
	};

	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
			fracc_manual_cuotas = [];
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#modal_fracc_manual_vale_id').val(respuesta.result.vale.id);
				$('#modal_fracc_manual_deuda_total').val(respuesta.result.vale.monto);
				$('#modal_fracc_manual_cuotas').val(respuesta.result.vale.nro_cuotas);
				$('#lbl_deuda_total').html(respuesta.result.vale.monto);
				fracc_manual_cuotas = respuesta.result.cuotas;
				sec_vale_fracc_manual_render_table_cuotas();
			}
		},
		error: function (error) {
			
		},
	});

	$('#sec_vale_fracc_manual_modal_fraccionamiento').modal('show');

}

function sec_vale_fracc_manual_render_table_cuotas() {
	
	var tbody = '';
	var monto = $('#modal_fracc_manual_deuda_total').val();
	var cuotas = $('#modal_fracc_manual_cuotas').val()
	var monto_cuota = parseFloat(monto) / parseFloat(cuotas);
	monto_cuota = parseFloat(monto_cuota).toFixed(2);
	tbody = tbody +'<tr>';
	tbody = tbody +'<td class="text-center"><input type="number" step="any" readonly class="form-control text-right" value="'+cuotas+'"></td>';
	tbody = tbody +'<td class="text-right">';
	tbody = tbody +'<input type="number" step="any" id="modal_fracc_monto_cuota" readonly class="form-control text-right" value="'+monto_cuota+'">';
	tbody = tbody +'</td>';
	tbody = tbody +'</tr>';
	$('#block_table_body_coutas').html(tbody);
}

function sec_vale_fracc_manual_agregar_cuota() {
	
	var nro_cuotas = $('#modal_fracc_manual_cuotas').val();
	if (nro_cuotas.length == 0) {
		$('#modal_fracc_manual_cuotas').val(1);
		return false;
	}
	nro_cuotas = parseFloat(nro_cuotas) + parseFloat(1);
	$('#modal_fracc_manual_cuotas').val(nro_cuotas);
  	sec_vale_fracc_manual_render_table_cuotas();
}

function sec_vale_fracc_manual_modificar_cuota() {
  	var monto = $('#block_monto_'+index).val();
	monto  = monto.length == 0 ? 0: monto;
  	fracc_manual_cuotas[index].monto = parseFloat(monto).toFixed(2);
	sec_vale_fracc_manual_render_table_cuotas();
}

function sec_vale_fracc_manual_eliminar_cuota() {
	
	var nro_cuotas = $('#modal_fracc_manual_cuotas').val();
	if (nro_cuotas.length == 0) {
		$('#modal_fracc_manual_cuotas').val(1);
		return false;
	}
	nro_cuotas = parseFloat(nro_cuotas) - parseFloat(1);
	if (nro_cuotas > 1) {
		$('#modal_fracc_manual_cuotas').val(nro_cuotas);
		sec_vale_fracc_manual_render_table_cuotas();
		return false;
	}
  	
}

function sec_vale_fracc_manual_validar_fraccionamiento() {

	var vale_id = $('#modal_fracc_manual_vale_id').val();
	var deuda_total = $('#modal_fracc_manual_deuda_total').val();
	var cuotas = $('#modal_fracc_manual_cuotas').val();
	var monto_cuota = $('#modal_fracc_monto_cuota').val();
	var val_monto_cuota = parseFloat(deuda_total) / parseFloat(cuotas);
	val_monto_cuota = parseFloat(val_monto_cuota).toFixed(2);

	if (monto_cuota != val_monto_cuota) {
		alertify.error("Por favor intente de nuevo, el monto de la cuota no coincide");
		sec_vale_fracc_manual_buscar_vale_descuento();
		$('#sec_vale_fracc_manual_modal_fraccionamiento').modal('hide');
		return false;
	}

	swal({
		title: "Esta seguro de guardar el fraccionamiento manual?",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		confirmButtonText: "Si, estoy de acuerdo!",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: true,
		closeOnCancel: true,

	},function (isConfirm) {
			
		if (isConfirm) {
			sec_vale_fracc_manual_guardar_fraccionamiento();
		} 
	});
}

function sec_vale_fracc_manual_guardar_fraccionamiento() {

	var vale_id = $('#modal_fracc_manual_vale_id').val();
	var deuda_total = $('#modal_fracc_manual_deuda_total').val();
	var cuotas = $('#modal_fracc_manual_cuotas').val();
	var monto_cuota = $('#modal_fracc_monto_cuota').val();
	var data = {
		vale_id:vale_id,
		deuda_total:deuda_total,
		cuotas:cuotas,
		monto_cuota:monto_cuota,
		accion : 'guardar_fraccionamiento_manual'
	};

	auditoria_send({ proceso: "guardar_fraccionamiento_manual_vales", data: data });

	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			auditoria_send({ proceso: "guardar_fraccionamiento_manual_vales", data: respuesta });
			if (respuesta.status == 200) {
				fracc_manual_cuotas = [];
				sec_vale_fracc_manual_buscar_vale_descuento();
				$('#sec_vale_fracc_manual_modal_fraccionamiento').modal('hide');
			}else{
				alertify.error(respuesta.message, 5);
				return false;
			}
		},
		error: function (error) {
			
		},
	});
}
