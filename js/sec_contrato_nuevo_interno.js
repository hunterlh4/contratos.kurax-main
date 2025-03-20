
// INICIO DECLARACION DE VARIABLES ARRAY
var array_proveedores_contrato = [];
var array_contraprestacion_contrato = [];
var array_nuevos_files_anexos = [];
// FIN DECLARACION DE VARIABLES ARRAY

function sec_contrato_nuevo_interno() {

	setTimeout(function() {
		$(".select2").select2({ width: "100%" });
	}, 500);

	$(".fecha_datepicker_ci")
	.datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
	})
	.on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});

	sec_con_nuevo_int_obtener_opciones("obtener_cargos", $("[name='cargo_id_persona_contacto']"));
	sec_con_nuevo_int_obtener_opciones("obtener_cargos", $("[name='cargo_id_responsable']"));
	sec_con_nuevo_int_obtener_opciones("obtener_cargos", $("[name='cargo_id_aprobante']"));
	// sec_con_nuevo_int_obtener_opciones("obtener_abogados", $("[name='abogado_id']"));


	sec_con_nuevo_int_obtener_opciones("obtener_empresa_at","[name='sec_con_nuevo_empresa_grupo_at_1']");
	sec_con_nuevo_int_obtener_opciones("obtener_empresa_at","[name='sec_con_nuevo_empresa_grupo_at_2']");
	sec_con_nuevo_int_obtener_opciones("obtener_bancos","[name='sec_con_nuevo_banco']");
	sec_con_nuevo_int_obtener_opciones("obtener_periodo","[name='periodo']");
	sec_con_nuevo_int_obtener_opciones("obtener_monedas","[name='sec_con_nuevo_moneda_id']");
	sec_con_nuevo_int_obtener_opciones("obtener_forma_pago","[name='sec_con_nuevo_forma_pago']");
	sec_con_nuevo_int_obtener_opciones("obtener_tipo_comprobante","[name='sec_con_nuevo_tipo_comprobante']");
	sec_contrato_nuevo_obtener_opciones("obtener_gerentes", "[name='gerente_area_id']");

	setTimeout(function () {
		$("#sec_con_nuevo_empresa_grupo_at_1").select2("open");
		sec_con_nuevo_int_select_cargo('persona_contacto');
	}, 1000);

	$("#sec_con_nuevo_empresa_grupo_at_1").change(function () {
		$("#sec_con_nuevo_empresa_grupo_at_1 option:selected").each(function () {
			empresa_id = $(this).val();
			if (empresa_id != 0) {
				setTimeout(function () {
					$("#sec_con_nuevo_empresa_grupo_at_2").select2("open");
				}, 200);
			}
		});
	});

	$("#sec_con_nuevo_empresa_grupo_at_2").change(function () {
		$("#sec_con_nuevo_empresa_grupo_at_2 option:selected").each(function () {
			empresa_id = $(this).val();
			if (empresa_id != 0) {
				setTimeout(function () {
					$("#gerente_area_id").select2("open");
				}, 200);
			}
		});
	});

	$("#gerente_area_id").change(function () {
		$("#gerente_area_id option:selected").each(function () {
			gerente_id = $(this).val();

			if (gerente_id == 'A') {
				$("#div_gerencia_area_nombre_gerente").show();
				$("#div_gerencia_area_email_gerente").show();

				setTimeout(function () {
					$("#nombre_del_gerente_del_area").focus();
				}, 200);
			} else {
				$("#div_gerencia_area_nombre_gerente").hide();
				$("#div_gerencia_area_email_gerente").hide();
			}

		});
	});

	$("#sec_con_nuevo_banco").change(function () {
		$("#sec_con_nuevo_banco option:selected").each(function () {
			banco = $(this).val();
			if (banco != 0) {
				setTimeout(function () {
					$("#sec_con_nuevo_nro_cuenta").focus();
				}, 200);
			}
		});
	});

	$("#sec_con_nuevo_periodo").change(function () {
		$("#sec_con_nuevo_periodo option:selected").each(function () {
			periodo_id = $(this).val();
			if (periodo_id != 0) {
				setTimeout(function () {
					$("#sec_con_nuevo_fecha_inicio").focus();
				}, 200);
			}
		});
	});

	$("#sec_con_nuevo_fecha_inicio").change(function () {
		var plazo_id = $("#plazo_id").val();

		if (plazo_id == "1") {
			setTimeout(function () {
				$("#num_dias_para_alertar_vencimiento").focus();
			}, 200);
		} else if (plazo_id == "2") {
			setTimeout(function () {
				$("#alerta_vencimiento_por_fecha_id").focus();
				$("#alerta_vencimiento_por_fecha_id").select2("open");
			}, 200);
		}
		
	});

	$("#plazo_id").change(function () {
		$("#plazo_id option:selected").each(function () {
			plazo_id = $(this).val();
			if (plazo_id == 1) {
				$("#div_periodo").show();
				$("#div_num_dias_para_alertar_vencimiento").show();
				$("#div_alerta").hide();
				$("#div_fecha_de_la_alerta").hide();
				setTimeout(function () {
					$("#sec_con_nuevo_periodo_numero").focus();
				}, 200);
			} else if (plazo_id == 2) {
				$("#div_periodo").hide();
				$("#div_num_dias_para_alertar_vencimiento").hide();
				$("#div_alerta").show();
				$('#alerta_vencimiento_por_fecha_id').val('0').trigger('change.select2');
				$("#div_fecha_de_la_alerta").hide();
				setTimeout(function () {
					$("#sec_con_nuevo_fecha_inicio").focus();
				}, 200);
			}
		});
	});

	$("#alerta_vencimiento_por_fecha_id").change(function () {
		$("#alerta_vencimiento_por_fecha_id option:selected").each(function () {
			alerta_vencimiento_por_fecha_id = $(this).val();
			if (alerta_vencimiento_por_fecha_id == 0) {
				$("#div_fecha_de_la_alerta").hide();
				setTimeout(function () {
					$("#sec_con_nuevo_moneda_id").focus();
					$("#sec_con_nuevo_moneda_id").select2("open");
				}, 200);
			} else if (alerta_vencimiento_por_fecha_id == 1) {
				$("#div_fecha_de_la_alerta").show();
				setTimeout(function () {
					$("#fecha_de_la_alerta").focus();
				}, 200);
			}
		});
	});

	$("#fecha_de_la_alerta").change(function () {
		setTimeout(function () {
			$("#sec_con_nuevo_moneda_id").focus();
			$("#sec_con_nuevo_moneda_id").select2("open");
		}, 200);
	});
}


function sec_con_nuevo_int_obtener_opciones(accion,select){
	$.ajax({
	   url: "/sys/get_contrato_nuevo_interno.php",
	   type: 'POST',
	   data:{accion:accion} ,//+data,
	   beforeSend: function () {
	   },
	   complete: function () {
	   },
	   success: function (datos) {//  alert(datat)
		   var respuesta = JSON.parse(datos);
		   $(select).find('option').remove().end();
		   $(select).append('<option value="0">- Seleccione -</option>');
		   $(respuesta.result).each(function(i,e){
			   opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
			   $(select).append(opcion);	
		   })
	   },
	   error: function () {
	   }
   });
}

$("#form_contrato_interno input:file").each(function(i,e){
	var nombre=$(e).attr("name");
	$(e).on("change", function () {
		if(this.files[0].size > 52428800) {
			swal({
				title: "Archivo debe ser menor a 50 MB",
				text: "",
				type: "warning",
				timer: 3000,
				closeOnConfirm: true
			},
			function(){
				swal.close();
			});
			$(this).val('');
		}
	});
});

// INICIO PROVEEDOR -REPRESENTANTES LEGALES
function sec_con_nuevo_int_agregar_proveedor(){
	var input_vacios = "";
	var dni_representante = $('#sec_con_nuevo_dni_representante').val();
	var nombre_representante = $('#sec_con_nuevo_nombre_representante').val();
	var nro_cuenta_detraccion = $('#sec_con_nuevo_nro_cuenta_detraccion').val();
	var banco = $('#sec_con_nuevo_banco').val();
	var banco_nombre = $('#sec_con_nuevo_banco option:selected').text();
	var nro_cuenta = $('#sec_con_nuevo_nro_cuenta').val();
	var nro_cci = $('#sec_con_nuevo_nro_cci').val();

	// if(dni_representante.length != 8){
	// 	swal({ title: "DNI debe tener 8 dígitos", text: "", type: "warning", timer: 3000, closeOnConfirm: true },
	// 	function(){ swal.close();});
	// 	return false;
	// }
	// if($.trim(dni_representante) == "") { input_vacios += " - DNI del Representante"; }
	// if($.trim(nombre_representante) == "") { input_vacios += " - Nombre del Representante"; }
	if($.trim(banco) == 0) { input_vacios += " - Banco"; }
	if($.trim(nro_cuenta) == "" && $.trim(nro_cci) == "") { input_vacios += " - Nro Cuenta o CCI"; }

	if($.trim(input_vacios) != ""){
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}

	var id_registro = array_proveedores_contrato.length + '_' + dni_representante;
	var data_representantes = {
		"id_registro" : id_registro,
		"dni_representante" : dni_representante,
		"nombre_representante" : nombre_representante,
		"nro_cuenta_detraccion" : nro_cuenta_detraccion,
		"banco" : banco,
		"banco_nombre" : banco_nombre,
		"nro_cuenta" : nro_cuenta,
		"nro_cci" : nro_cci
	};
	array_proveedores_contrato.push(data_representantes);

	var onclick_editar_repr = "sec_con_nuevo_int_editar_proveedor('" + id_registro + "')";
	$('#sec_con_nuevo_tabla_proveedores').append(
		'<tr>'
		+ '<td style="display: none;" class="id_registro">' + id_registro + '</td>'
		// + '<td class="dni_representante">' + dni_representante + '</td>'
		// + '<td class="nombre_representante">' + nombre_representante + '</td>'
		// + '<td class="nro_cuenta_detraccion">' + nro_cuenta_detraccion + '</td>'
		+ '<td class="banco_nombre">' + banco_nombre + '</td>'
		+ '<td class="nro_cuenta">' + nro_cuenta + '</td>'
		+ '<td class="nro_cci">' + nro_cci + '</td>'
		// + '<td><div class="file-select" id="src">'
		// 	+'<input type="file" name="vigencia_nuevo_representante_' + id_registro + '" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></div></td>'
		// + '<td>' 
		// 	+ '<input type="file" name="dni_nuevo_representante_' + id_registro + '" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>'
		+ '<td class="onclick_editar_repr"><button type="button" class="btn btn-sm btn-success" onclick="' + onclick_editar_repr + '"><i class="fa fa-edit"></i></button></td>'
		+ '<td><button type="button" class="btn btn-sm btn-danger borrar_representante_legal_int"><i class="fa fa-close"></i></button></td>'
		+ '</tr>'
	);

	sec_con_nuevo_int_limpiar_proveedor();

}

function sec_con_nuevo_int_limpiar_proveedor(){
	$('#id_registro_proveedor_temporal').val('');
	$('#sec_con_nuevo_dni_representante').val('');
	$('#sec_con_nuevo_nombre_representante').val('');
	$('#sec_con_nuevo_nro_cuenta_detraccion').val('');
	$('#sec_con_nuevo_banco').val('');
	$('#sec_con_nuevo_banco option:selected').text('');
	$('#sec_con_nuevo_nro_cuenta').val('');
	$('#sec_con_nuevo_nro_cci').val('');
	$('#sec_con_nuevo_banco').val('0').trigger('change.select2');
}

function sec_con_nuevo_int_editar_proveedor(id_registro){
	const index = array_proveedores_contrato.map(item => item.id_registro).indexOf(id_registro);
	$('#id_registro_proveedor_temporal').val(index);
	let data_representantes = array_proveedores_contrato[index];
	$('#sec_con_nuevo_dni_representante').val(data_representantes.dni_representante);
	$('#sec_con_nuevo_nombre_representante').val(data_representantes.nombre_representante);
	$('#sec_con_nuevo_nro_cuenta_detraccion').val(data_representantes.nro_cuenta_detraccion);
	$('#sec_con_nuevo_banco').val(data_representantes.banco);
	$('#sec_con_nuevo_banco option:selected').text(data_representantes.banco_nombre);
	$('#sec_con_nuevo_nro_cuenta').val(data_representantes.nro_cuenta);
	$('#sec_con_nuevo_nro_cci').val(data_representantes.nro_cci);
	$('#sec_con_nuevo_banco').val(data_representantes.banco).trigger('change.select2');
	$('#div_sec_con_nuevo_prov_editar_proveedor').show();
	$('#sec_con_nuevo_btn_nuevo_proveedor').hide();

	$('#div_sec_con_nuevo_editar_proveedor').show();
	$('#div_sec_con_nuevo_nuevo_proveedor').hide();
}

function sec_con_nuevo_int_modificar_proveedor(){
	var input_vacios = "";
	var index = $('#id_registro_proveedor_temporal').val();
	var dni_representante = $('#sec_con_nuevo_dni_representante').val();
	var nombre_representante = $('#sec_con_nuevo_nombre_representante').val();
	var nro_cuenta_detraccion = $('#sec_con_nuevo_nro_cuenta_detraccion').val();
	var banco = $('#sec_con_nuevo_banco').val();
	var banco_nombre = $('#sec_con_nuevo_banco option:selected').text();
	var nro_cuenta = $('#sec_con_nuevo_nro_cuenta').val();
	var nro_cci = $('#sec_con_nuevo_nro_cci').val();

	// if(dni_representante.length != 8){
	// 	swal({ title: "DNI debe tener 8 dígitos", text: "", type: "warning", timer: 3000, closeOnConfirm: true },
	// 	function(){ swal.close();});
	// 	return false;
	// }
	// if($.trim(dni_representante) == "") { input_vacios += " - DNI del Representante"; }
	// if($.trim(nombre_representante) == "") { input_vacios += " - Nombre del Representante"; }
	if($.trim(banco) == 0) { input_vacios += " - Banco"; }
	if($.trim(nro_cuenta) == "" && $.trim(nro_cci) == "") { input_vacios += " - Nro Cuenta o CCI"; }

	if($.trim(input_vacios) != ""){
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}
	var nuevo_id_registro = index +"_"+dni_representante;

	var data_representantes = {
		"id_registro" : nuevo_id_registro,
		"dni_representante" : dni_representante,
		"nombre_representante" : nombre_representante,
		"nro_cuenta_detraccion" : nro_cuenta_detraccion,
		"banco" : banco,
		"banco_nombre" : banco_nombre,
		"nro_cuenta" : nro_cuenta,
		"nro_cci" : nro_cci
	};
	var id_registro_old = array_proveedores_contrato[index].id_registro;
	array_proveedores_contrato[index] = data_representantes;

	$('#sec_con_nuevo_tabla_proveedores tr').each(function(i){
		if(i != 0){
			var row = $(this);
			var id = row.find(".id_registro").text();
			if(id == id_registro_old){
				var onclick_editar_repr = "sec_con_nuevo_int_editar_proveedor('" + nuevo_id_registro + "')";
				row.find(".id_registro").html(nuevo_id_registro);
				// row.find(".dni_representante").html(dni_representante);
				// row.find(".nombre_representante").html(nombre_representante);
				// row.find(".nro_cuenta_detraccion").html(nro_cuenta_detraccion);
				row.find(".banco").html(banco);
				row.find(".banco_nombre").html(banco_nombre);
				row.find(".nro_cuenta").html(nro_cuenta);
				row.find(".nro_cci").html(nro_cci);
				row.find(".onclick_editar_repr").html('<button class="btn btn-sm btn-success" onclick="' + onclick_editar_repr + '"><i class="fa fa-edit"></i></button>');
			}
		}
   });
	sec_con_nuevo_int_limpiar_proveedor();
	$('#div_sec_con_nuevo_nuevo_proveedor').show();
	$('#div_sec_con_nuevo_editar_proveedor').hide();
}

function sec_con_nuevo_int_cancelar_proveedor() {
	sec_con_nuevo_int_limpiar_proveedor();
	
	$('#div_sec_con_nuevo_nuevo_proveedor').show();
	$('#div_sec_con_nuevo_editar_proveedor').hide();
}

$(document).on('click', '.borrar_representante_legal_int', function(event) {
	var this_tr = $(this)
	swal({
    title: "Quitar la cuenta bancaria",
    text: "¿Desea quitar la cuenta bancaria seleccionada?",
    type: "warning",
    showCancelButton: true,
    cancelButtonText: "No",
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Si",
    closeOnConfirm: false
    },
    function (isConfirm) {
        if(isConfirm){
        	var id_registro_seleccionado = $(this).closest("tr").find(".id_registro").text();  
            rr_representantes = rr_representantes.filter(w => w.id_registro != id_registro_seleccionado);
            this_tr.parents("tr").remove();

			$('#div_sec_con_nuevo_nuevo_proveedor').show();
			$('#div_sec_con_nuevo_editar_proveedor').hide();
			sec_con_nuevo_int_limpiar_proveedor();

            swal({
                title: "Listo!",
                text: "",
                type: "success"
            });
        } else {
        	return false;
        }
    });
  
});

// FIN PROVEEDOR - REPRESENTANTES LEGALES




//INICIO - CONTRAPRESTACIONES

$("#sec_con_nuevo_moneda_id").change(function () {
	$("#sec_con_nuevo_moneda_id option:selected").each(function () {
		sec_con_nuevo_moneda_id = $(this).val();
		if (sec_con_nuevo_moneda_id != 0) {
			setTimeout(function() {
				$('#sec_con_nuevo_monto').focus();
			}, 200);
		}
	});
});

$("#sec_con_nuevo_tipo_igv_id").change(function () {
	$("#sec_con_nuevo_tipo_igv_id option:selected").each(function () {
		sec_con_nuevo_tipo_igv_id = $(this).val();
		if (sec_con_nuevo_tipo_igv_id != 0) {
			sec_con_nuevo_int_calcular_subtotal_y_igv(sec_con_nuevo_tipo_igv_id);
			setTimeout(function() {
				if($('#sec_con_nuevo_tipo_comprobante').val() == "0"){
					$('#sec_con_nuevo_tipo_comprobante').focus();
					$('#sec_con_nuevo_tipo_comprobante').select2('open');
				}
			}, 200);
		}
	});
});

$("#sec_con_nuevo_forma_pago").change(function () {
	$("#sec_con_nuevo_forma_pago option:selected").each(function () {
		sec_con_nuevo_forma_pago = $(this).val();
		if (sec_con_nuevo_forma_pago != 0) {
			setTimeout(function() {
				$('#sec_con_nuevo_tipo_comprobante').focus();
				$('#sec_con_nuevo_tipo_comprobante').select2('open');
			}, 200);
		}
	});
});

$("#sec_con_nuevo_tipo_comprobante").change(function () {
	$("#sec_con_nuevo_tipo_comprobante option:selected").each(function () {
		sec_con_nuevo_tipo_comprobante = $(this).val();
		if (sec_con_nuevo_tipo_comprobante != 0) {
			setTimeout(function() {
				$('#sec_con_nuevo_plazo_pago').focus();
			}, 200);
		}
	});
});

$("#sec_con_nuevo_subtotal").on({
	"focus": function (event) {
		$(event.target).select();
	},
	"blur": function (event) {
		if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
			$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
			$(event.target).val(function (index, value ) {
				return value.replace(/\D/g, "")
							.replace(/([0-9])([0-9]{2})$/, '$1.$2')
							.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
			});
		} else {
			$(event.target).val("0.00");
		}
	}
});

$("#sec_con_nuevo_igv").on({
	"focus": function (event) {
		$(event.target).select();
	},
	"blur": function (event) {
		if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
			$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
			$(event.target).val(function (index, value ) {
				return value.replace(/\D/g, "")
							.replace(/([0-9])([0-9]{2})$/, '$1.$2')
							.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
			});
		} else {
			$(event.target).val("0.00");
		}
	}
});

$("#sec_con_nuevo_monto").on({
	"focus": function (event) {
		$(event.target).select();
	},
	"change": function (event) {
		if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
			$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
			$(event.target).val(function (index, value ) {
				return value.replace(/\D/g, "")
							.replace(/([0-9])([0-9]{2})$/, '$1.$2')
							.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
			});
		} else {
			$(event.target).val("0.00");
		}
		$("#sec_con_nuevo_tipo_igv_id").change();
	}
});

function sec_con_nuevo_int_calcular_subtotal_y_igv(tipo)
{
	var monto = $('#sec_con_nuevo_monto').val().trim().replace(',', '');
	var subtotal = 0;
	var igv = 0;

	if (monto != '') {
		monto = parseFloat(monto);
		subtotal = monto;

		if (tipo == "1") {
			subtotal = monto / 1.18;
			igv = monto - subtotal;
		}
	}

	$('#sec_con_nuevo_subtotal').val(subtotal.toFixed(2));
	$('#sec_con_nuevo_igv').val(igv.toFixed(2));

	$('#sec_con_nuevo_subtotal').blur();
	$('#sec_con_nuevo_igv').blur();
}


function sec_con_nuevo_int_proveedor_guardar_contraprestacion() {
	var contraprestacion_id = $('#id_registro_contraprestacion_temporal').val();
	var moneda_id = $('#sec_con_nuevo_moneda_id').val();
	var moneda_nombre = $('#sec_con_nuevo_moneda_id option:selected').text();
	var subtotal = parseFloat($('#sec_con_nuevo_subtotal').val().trim().replace(',', ''));
	var tipo_igv = $('#sec_con_nuevo_tipo_igv_id').val();
	var tipo_igv_nombre = $('#sec_con_nuevo_tipo_igv_id option:selected').text();
	var igv = parseFloat($('#sec_con_nuevo_igv').val().trim().replace(',', ''));
	var monto = parseFloat($('#sec_con_nuevo_monto').val().trim().replace(',', ''));
	var forma_pago_detallado = $('#sec_con_nuevo_forma_pago_detallado').val();
	var tipo_comprobante = $('#sec_con_nuevo_tipo_comprobante').val().trim();
	var tipo_comprobante_nombre = $('#sec_con_nuevo_tipo_comprobante option:selected').text();
	var plazo_pago = $('#sec_con_nuevo_plazo_pago').val().trim();

	if (parseInt(moneda_id) == 0) {
		alertify.error('Seleccione el tipo de moneda',5);
		$("#sec_con_nuevo_moneda_id").focus();
		$('#sec_con_nuevo_moneda_id').select2('open');
		return false;
	}

	if (forma_pago_detallado.length < 5) {
		alertify.error('Seleccione la forma de pago',5);
		$("#sec_con_nuevo_forma_pago_detallado").focus();
		return false;
	}

	if (parseInt(tipo_comprobante) == 0) {
		alertify.error('Seleccione tipo de comprobante a emitir',5);
		$("#sec_con_nuevo_tipo_comprobante").focus();
		$('#sec_con_nuevo_tipo_comprobante').select2('open');
		return false;
	}

	if (plazo_pago.length < 2) {
		alertify.error('Ingrese el Plazo de Pago',5);
		$("#sec_con_nuevo_plazo_pago").focus();
		return false;
	}

	if (monto.toFixed(2) != (subtotal + igv).toFixed(2)) { 
		alertify.error('La suma del Subtotal + el IGV no coincide con el Monto Bruto',5);
		return false;
	}

	var data = {
		"moneda_id": moneda_id,
		"moneda_nombre": moneda_nombre,
		"tipo_igv": tipo_igv,
		"tipo_igv_nombre": tipo_igv_nombre,
		"subtotal": subtotal,
		"igv": igv,
		"monto": monto,
		"forma_pago_detallado": forma_pago_detallado,
		"tipo_comprobante": tipo_comprobante,
		"tipo_comprobante_nombre": tipo_comprobante_nombre,
		"plazo_pago": plazo_pago
	}
	array_contraprestacion_contrato.push(data);
	sec_con_nuevo_int_limpiar_contraprestacion();
	sec_con_nuevo_int_listar_contraprestaciones(array_contraprestacion_contrato)
}

function sec_con_nuevo_int_listar_contraprestaciones(array_contraprestacion_contrato){

	let data = {
		accion : 'listar_contraprestaciones',
		contraprestaciones : array_contraprestacion_contrato,
	}
	$.ajax({
		url: "/sys/get_contrato_nuevo_interno.php",
		type: 'POST',
		data:data ,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (datos) {//  alert(datat)
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#sec_con_nuevo_tabla_contraprestaciones').html(respuesta.result)
			}
		}
	});
}

function sec_con_nuevo_int_limpiar_contraprestacion(){
	$('#id_registro_contraprestacion_temporal').val('');
	$('#sec_con_nuevo_moneda_id').val('');
	$('#sec_con_nuevo_moneda_id').val('0').trigger('change.select2');
	$('#sec_con_nuevo_subtotal').val('');
	$('#sec_con_nuevo_tipo_igv_id').val('');
	$('#sec_con_nuevo_tipo_igv_id').val('0').trigger('change.select2');
	$('#sec_con_nuevo_igv').val('');
	$('#sec_con_nuevo_monto').val('');
	$('#sec_con_nuevo_forma_pago_detallado').val('');
	$('#sec_con_nuevo_tipo_comprobante').val();
	$('#sec_con_nuevo_tipo_comprobante').val('0').trigger('change.select2');
	$('#sec_con_nuevo_plazo_pago').val('');
}

function sec_con_nuevo_int_editar_contraprestacion(index){
	$('#id_registro_contraprestacion_temporal').val(index);
	let data_contraprestacion = array_contraprestacion_contrato[index];
	$('#id_registro_contraprestacion_temporal').val();
	$('#sec_con_nuevo_moneda_id').val(data_contraprestacion.moneda_id);
	$('#sec_con_nuevo_moneda_id').val(data_contraprestacion.moneda_id).trigger('change.select2');
	$('#sec_con_nuevo_subtotal').val(data_contraprestacion.subtotal);
	$('#sec_con_nuevo_tipo_igv_id').val(data_contraprestacion.tipo_igv);
	$('#sec_con_nuevo_tipo_igv_id').val(data_contraprestacion.tipo_igv).trigger('change.select2');
	$('#sec_con_nuevo_igv').val(data_contraprestacion.igv);
	$('#sec_con_nuevo_monto').val(data_contraprestacion.monto);
	$('#sec_con_nuevo_forma_pago_detallado').val(data_contraprestacion.forma_pago_detallado);
	$('#sec_con_nuevo_tipo_comprobante').val(data_contraprestacion.tipo_comprobante);
	$('#sec_con_nuevo_tipo_comprobante').val(data_contraprestacion.tipo_comprobante).trigger('change.select2');
	$('#sec_con_nuevo_plazo_pago').val(data_contraprestacion.plazo_pago);

	$('#sec_con_nuevo_monto').change();

	$('#btn_agregar_contraprestacion').hide();
	$('#btn_modificar_contraprestacion').show();
	$('#btn_cancelar_contraprestacion').show();
}

function sec_con_nuevo_int_proveedor_modificar_contraprestacion() {
	var index = $('#id_registro_contraprestacion_temporal').val();
	var moneda_id = $('#sec_con_nuevo_moneda_id').val();
	var moneda_nombre = $('#sec_con_nuevo_moneda_id option:selected').text();
	var subtotal = parseFloat($('#sec_con_nuevo_subtotal').val().trim().replace(',', ''));
	var tipo_igv = $('#sec_con_nuevo_tipo_igv_id').val();
	var tipo_igv_nombre = $('#sec_con_nuevo_tipo_igv_id option:selected').text();
	var igv = parseFloat($('#sec_con_nuevo_igv').val().trim().replace(',', ''));
	var monto = parseFloat($('#sec_con_nuevo_monto').val().trim().replace(',', ''));
	var forma_pago_detallado = $('#sec_con_nuevo_forma_pago_detallado').val();
	var tipo_comprobante = $('#sec_con_nuevo_tipo_comprobante').val().trim();
	var tipo_comprobante_nombre = $('#sec_con_nuevo_tipo_comprobante option:selected').text();
	var plazo_pago = $('#sec_con_nuevo_plazo_pago').val().trim();

	if (parseInt(moneda_id) == 0) {
		alertify.error('Seleccione el tipo de moneda',5);
		$("#sec_con_nuevo_moneda_id").focus();
		$('#sec_con_nuevo_moneda_id').select2('open');
		return false;
	}

	if (forma_pago_detallado.length < 5) {
		alertify.error('Seleccione la forma de pago',5);
		$("#sec_con_nuevo_forma_pago_detallado").focus();
		return false;
	}

	if (parseInt(tipo_comprobante) == 0) {
		alertify.error('Seleccione tipo de comprobante a emitir',5);
		$("#sec_con_nuevo_tipo_comprobante").focus();
		$('#sec_con_nuevo_tipo_comprobante').select2('open');
		return false;
	}

	if (plazo_pago.length < 2) {
		alertify.error('Ingrese el Plazo de Pago',5);
		$("#sec_con_nuevo_plazo_pago").focus();
		return false;
	}

	if (monto.toFixed(2) != (subtotal + igv).toFixed(2)) { 
		alertify.error('La suma del Subtotal + el IGV no coincide con el Monto Bruto',5);
		return false;
	}

	var data = {
		"moneda_id": moneda_id,
		"moneda_nombre": moneda_nombre,
		"tipo_igv": tipo_igv,
		"tipo_igv_nombre": tipo_igv_nombre,
		"subtotal": subtotal,
		"igv": igv,
		"monto": monto,
		"forma_pago_detallado": forma_pago_detallado,
		"tipo_comprobante": tipo_comprobante,
		"tipo_comprobante_nombre": tipo_comprobante_nombre,
		"plazo_pago": plazo_pago
	}
	array_contraprestacion_contrato[index] = data;
	sec_con_nuevo_int_limpiar_contraprestacion();
	sec_con_nuevo_int_listar_contraprestaciones(array_contraprestacion_contrato);

	$('#btn_agregar_contraprestacion').show();
	$('#btn_modificar_contraprestacion').hide();
	$('#btn_cancelar_contraprestacion').hide();
}

function sec_con_nuevo_int_eliminar_contraprestacion(index) {
	array_contraprestacion_contrato.splice(index,1);
	sec_con_nuevo_int_listar_contraprestaciones(array_contraprestacion_contrato);
}

function sec_con_nuevo_int_proveedor_cancelar_modificar_cambios_contraprestacion(index) {
	sec_con_nuevo_limpiar_contraprestacion();
	$('#btn_agregar_contraprestacion').show();
	$('#btn_modificar_contraprestacion').hide();
	$('#btn_cancelar_contraprestacion').hide();
}
//FIN - CONTRAPRESTACIONES



// INICIO OTROS ANEXOS 
function sec_con_nuevo_int_abrir_modal_tipos_anexos(){
	$('#modaltiposanexos').modal({backdrop: 'static', keyboard: false});
	$('#modal_nuevo_anexo_tipo_contrato_id').val("2");
	 sec_con_nuevo_int_cargar_tipos_anexos();
}
function sec_con_nuevo_int_cargar_tipos_anexos(){
	sec_con_nuevo_int_limpiar_select_tipos_anexos();
	var tipo_contrato_id = $('#modal_nuevo_anexo_tipo_contrato_id').val();
	array_tabla_subdiarios = [];
	var data = {
		"accion": "obtener_tipos_de_archivos",
		"tipo_contrato_id": tipo_contrato_id
	}
	auditoria_send({ "proceso": "obtener_tipos_de_archivos", "data": data });
	$.ajax({
		url: "sys/get_contrato_nuevo_interno.php",
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
					array_tabla_subdiarios.push(item);
					$('#modal_nuevo_anexo_select_tipos').append(
						'<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + '</option>'
					);
				});
				return false;
			}      
		},
		error: function() {}
	});
}


function sec_con_nuevo_int_anadirArchivo() {
	tipo_documento_seleccionado_nombre = $('#modal_nuevo_anexo_select_tipos option:selected').text();
	id_tipo_documento_seleccionado = $('#modal_nuevo_anexo_select_tipos option:selected').val();

	if (id_tipo_documento_seleccionado != "0") {
		//Sumamos a la variable el número de archivos.
		contArchivos=contArchivos+1;
		//Agregamos el componente de tipo input
		var div = document.createElement("div");
		var input = document.createElement("input");
		var a = document.createElement("a");

		//Añadimos los atributos de div
		div.id ='archivo'+contArchivos;

		//Añadimos los atributos de input
		input.type = 'file';
		input.name = 'newAnexoPrueba[]';

		//Añadimos los atributos del enlace a eliminar
		a.href = "#";
		a.id = 'archivo'+contArchivos;
		a.onclick = function() {
		sec_con_nuevo_int_borrarArchivo(a.id);
		}
		a.text ="X Eliminar archivo";

		//TIPO DE ARCHIVO SELECCIONADO
		tipo_documento_seleccionado_nombre = $('#modal_nuevo_anexo_select_tipos option:selected').text();
		id_tipo_documento_seleccionado = $('#modal_nuevo_anexo_select_tipos option:selected').val();
		html2 = '';
		html2 += '<div class="control-label">';
		html2 +=  tipo_documento_seleccionado_nombre + ': ';
		html2 += '</div>';

		var hoy = new Date();
		var fecha = hoy.getDate() + '' + ( hoy.getMonth() + 1 ) + '' + hoy.getFullYear();
		var hora = hoy.getHours() + '' + hoy.getMinutes() + '' + hoy.getSeconds();
		var Tiempo = fecha + '' + hora;
		id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
		//var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

		var onclick = "sec_con_nuevo_int_borrarArchivo('" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "')";

		var html = "";
		html += '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + '">';
		html += '<div class="form-group">';
		html += '<div class="control-label">';
		html +=  tipo_documento_seleccionado_nombre + ': ';
		html += '</div>';
		var onchange = "file(event,'" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "', " + id_tipo_documento_seleccionado + ", '" + tipo_documento_seleccionado_nombre + "')";
		html += '<div style="margin-top:10px;">';
		html += '<input name="miarchivo[]" type="file" id="'+ id_tipo_documento_seleccionado + '_' + id_nuevo_objeto_nuevo_anexo + '" class="col-md-11" onchange="' + onchange + '" style="padding: 0px 0px;"/>';
		html += '<button class="btn btn-xs btn-danger col-md-1" style="width: 22px;" onclick="' + onclick + '"><i class="fa fa-trash-o"></i></button>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		
		$('#sec_con_nuevo_nuevos_anexos_listado').append(html); // cargar el nuevo item
		
		$('#modaltiposanexos').modal('hide');
	} else {
		alertify.error('Seleccione el tipo de anexo',5);
	}
	
}

function sec_con_nuevo_int_borrarArchivo(id_anexo){
	//Restamos el número de archivos
	contArchivos=contArchivos-1;
	
	array_nuevos_files_anexos = array_nuevos_files_anexos.filter((item) => item.id_objeto !== id_anexo);
	$('div[name=' + id_anexo + ']').remove();
}

function sec_con_nuevo_int_agregar_nuevo_tipo_archivo(){
	$('#sec_con_nuevo_agregar_nuevo_tipo_archivo').modal({backdrop: 'static', keyboard: false});
	$('#sec_con_nuevo_tipo_anexo_nombre').val('');
	setTimeout(function() {
		$('#sec_con_nuevo_tipo_anexo_nombre').focus();
	}, 500);
}

function sec_con_nuevo_int_guardarNuevoTipoAnexo(){
	if($('#sec_con_nuevo_tipo_anexo_nombre').val() == ''){
		swal({
			title: "Ingrese el nombre del tipo de anexo nuevo",
			text: respuesta.error,
			html:true,
			type: "warning",
			closeOnConfirm: false,
			showCancelButton: false
		});
		return false;
	}
	var tipo_contrato_id = $('#modal_nuevo_anexo_tipo_contrato_id').val();
	var anexo = $('#sec_con_nuevo_tipo_anexo_nombre').val();
	var data = {
		"accion": "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
		"anexo" : anexo,
		"tipo_contrato_id": tipo_contrato_id
	}

	auditoria_send({ "proceso": "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo", "data": data });
	$.ajax({
		url: "sys/set_contrato_nuevo.php",
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
				$('#sec_con_nuevo_agregar_nuevo_tipo_archivo').modal('hide');
				sec_con_nuevo_int_cargar_tipos_anexos();
				setTimeout(function () {
                    $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").val(respuesta.result).trigger('change');
               
                  }, 1500); 

				  setTimeout(function () {
                    $("#modal_nuevo_anexo_select_tipos").val(respuesta.result).trigger('change');
               
                  }, 1500); 

				return false;
			}      
		},
		error: function() {}
	});
}

function sec_con_nuevo_int_limpiar_select_tipos_anexos(){
	$('#modal_nuevo_anexo_select_tipos').html('');
	$('#modal_nuevo_anexo_select_tipos').append(
		'<option value="0"> - Seleccione - </option>'
	);
}
// FIN OTROS ANEXOS 


function sec_con_nuevo_validate_email(email) {
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}

// INICIO GUARDAR CONTRATO DE PROVEEDOR
$("#form_contrato_interno").submit(function (e) {
	e.preventDefault();

	var empresa_grupo_at_1_id = $("#sec_con_nuevo_empresa_grupo_at_1").val().trim();
	var empresa_grupo_at_2_id = $("#sec_con_nuevo_empresa_grupo_at_2").val().trim();
	var gerente_area_id = $("#gerente_area_id").val().trim();
	var nombre_del_gerente_del_area = $("#nombre_del_gerente_del_area").val().trim();
	var email_del_gerente_del_area = $("#email_del_gerente_del_area").val().trim();
	var detalle_del_servicio = $("#sec_con_nuevo_detalle_servicio").val().trim();
	var plazo_id = $("#plazo_id").val().trim();
	var periodo_numero = $("#sec_con_nuevo_periodo_numero").val().trim();
	var periodo_id = $("#sec_con_nuevo_periodo").val().trim();
	var num_dias_para_alertar_vencimiento = $("#num_dias_para_alertar_vencimiento").val().trim();
	var alerta_vencimiento_por_fecha_id = $("#alerta_vencimiento_por_fecha_id").val().trim();
	var fecha_de_la_alerta = $("#fecha_de_la_alerta").val().trim();
	var fecha_inicio = $("#sec_con_nuevo_fecha_inicio").val().trim();
	var fecha_actual = new Date();
	var fecha_de_la_alerta_date = new Date(fecha_de_la_alerta);

	var cargo_id_persona_contacto = $("#cargo_id_persona_contacto").val().trim();
	var cargo_id_aprobante = $("#cargo_id_aprobante").val().trim();
	var cargo_id_responsable = $("#cargo_id_responsable").val().trim();

	if(empresa_grupo_at_1_id == 0) {
		alertify.error('Seleccionar la empresa Grupo AT 1',5);
		$('#sec_con_nuevo_empresa_grupo_at_1').focus();
		$('#sec_con_nuevo_empresa_grupo_at_1').select2('open');
		return false;
	}

	if(empresa_grupo_at_2_id == 0) {
		alertify.error('Seleccionar la empresa Grupo AT 2',5);
		$('#sec_con_nuevo_empresa_grupo_at_2').focus();
		$('#sec_con_nuevo_empresa_grupo_at_2').select2('open');
		return false;
	}

	var correos_adjuntos = $("#correos_adjuntos").val();
	var new_correos = [];

	if(correos_adjuntos.length > 0){
		correos_adjuntos = correos_adjuntos.split(',');
		new_correos = correos_adjuntos.map( item => item.trim());
		for (let index = 0; index < new_correos.length; index++) {
			const element = new_correos[index];
			if (element.length == 0) {
				alertify.error('Ingrese un correo',5);
				$("#correos_adjuntos").focus();
				return false;
			}
			if (!sec_con_nuevo_validate_email(element)) {
				$("#correos_adjuntos").focus();
				alertify.error(element + ' no es correo valido',5);
				return false;
			}
		}
	}

	if (document.getElementById('check_gerencia_interno').checked && cargo_id_aprobante == 0){
		alertify.error("Seleccione el cargo del Aprobador.", 5);
		$("#cargo_id_aprobante").focus();
		$("#cargo_id_aprobante").select2("open");
		return false;
	}

	if (gerente_area_id == 0) {
		alertify.error("Seleccione el Responsable de Área.", 5);
		$("#gerente_area_id").focus();
		$("#gerente_area_id").select2("open");
		return false;
	}

	if (gerente_area_id == 'A' && nombre_del_gerente_del_area.length == 0) {
		alertify.error("Ingrese el nombre del Responsable de Área.", 5);
		$("#nombre_del_gerente_del_area").focus();
		return false;
	}

	if (gerente_area_id == 'A' && email_del_gerente_del_area.length == 0) {
		alertify.error("Ingrese el email del Responsable de Área.", 5);
		$("#email_del_gerente_del_area").focus();
		return false;
	}

	if (gerente_area_id == 'A' && !(sec_contrato_nuevo_es_email_valido(email_del_gerente_del_area))) {
		alertify.error("Ingrese un email válido del Responsable de Área.", 5);
		$("#email_del_gerente_del_area").focus();
		return false;
	}

	if (cargo_id_responsable == 0) {
		alertify.error("Seleccione el cargo del responsable.", 5);
		$("#cargo_id_responsable").focus();
		$("#cargo_id_responsable").select2("open");
		return false;
	}

	if (cargo_id_persona_contacto == 0) {
		alertify.error("Seleccione el cargo de la persona de contacto.", 5);
		$("#cargo_id_persona_contacto").focus();
		$("#cargo_id_persona_contacto").select2("open");
		return false;
	}
	
	if (array_proveedores_contrato.length == 0) {
		alertify.error("No ha proporcionado datos de la cuenta bancaria.", 5);
		$("#sec_con_nuevo_banco").select2("open");
		return false;
	}

	if (detalle_del_servicio.length == 0) {
		alertify.error("Ingrese el detalle del servicio a contratar.", 5);
		$("#sec_con_nuevo_detalle_servicio").focus();
		return false;
	}

	if (plazo_id == 1) {
		if (periodo_numero.length == 0) {
			alertify.error("Ingrese el periodo del plazo del contrato.", 5);
			$("#periodo_numero").focus();
			return false;
		}

		if (periodo_id == 0) {
			alertify.error("Seleccione el periodo del plazo del contrato.", 5);
			$("#periodo").focus();
			$("#periodo").select2("open");
			return false;
		}

		if (num_dias_para_alertar_vencimiento.length == 0) {
			alertify.error("Ingrese los días de anticipación para alertar vencimiento.", 5);
			$("#num_dias_para_alertar_vencimiento").focus();
			return false;
		}
	} else {
		if (alerta_vencimiento_por_fecha_id == 1 && ( fecha_de_la_alerta_date < fecha_actual ) ) {
			alertify.error("Seleccione una fecha mayor a la fecha de hoy.", 5);
			$("#fecha_de_la_alerta").focus();
			return false;
		}
	}

	if (fecha_inicio.length == 0) {
		alertify.error("Ingrese la fecha inicio.", 5);
		$("#sec_con_nuevo_fecha_inicio").focus();
		return false;
	}

	if(array_contraprestacion_contrato.length == 0) {
		alertify.error('Agregar la contraprestación a la solicitud',5);
		$('#sec_con_nuevo_moneda_id').focus();
		$('#sec_con_nuevo_moneda_id').select2('open');
		return false;
	}


	var check_gerencia_interno = 0;

	if (document.getElementById('check_gerencia_interno').checked)
	{
		check_gerencia_interno = 1;
	}
	else
	{
		check_gerencia_interno = 0;
	}

	var dataForm = new FormData($("#form_contrato_interno")[0]);
	dataForm.append("accion","guardar_contrato_interno");
	dataForm.append("array_nuevos_files_anexos", JSON.stringify(array_nuevos_files_anexos));
	dataForm.append("rr_representantes",JSON.stringify(array_proveedores_contrato));
	dataForm.append("check_gerencia_interno", check_gerencia_interno);
	dataForm.append("array_contraprestaciones", JSON.stringify(array_contraprestacion_contrato));
	dataForm.append("array_correos_adjuntos",JSON.stringify(new_correos));

	auditoria_send({ proceso: "guardar_contrato_interno", data: dataForm });
	
	$.ajax({
		url: "sys/set_contrato_nuevo_interno.php",
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
			
			auditoria_send({ proceso: "guardar_contrato_interno", data: respuesta });

			if (parseInt(respuesta.status) == 200) {
				swal({
					title: respuesta.message,
					text: "",
					html: true,
					type: "success",
					timer: 10000,
					closeOnConfirm: false,
					showCancelButton: false,
				},
					function (isConfirm) {
						window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
					}
				);

				if (parseInt(respuesta.http_code) == 200) {
					setTimeout(function () {
						window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
						return false;
					}, 10000);
				}


				return true;
			} else {

				swal({
					title: "Error al guardar Solicitud de Contrato Interno",
					text: respuesta.message,
					html: true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false,
				});

				return false;
			}
		},
		complete: function(){
			loading(false);
		}
	});
})
// FIN GUARDAR CONTRATO DE PROVEEDOR


function sec_con_nuevo_int_select_cargo(type) {

	var usuario_id = '';
	if (type == "persona_contacto") {
		usuario_id = '';
	}else if(type == "responsable"){
		usuario_id = $('#gerente_area_id').val();
	}else if(type == "aprobador"){
		usuario_id = $('#director_aprobacion_id').val();
	}
	

	var data = {
		"accion": 'obtener_cargo_usuario',
		"type" : type,
		"usuario_id" : usuario_id,
	};
	auditoria_send({ "proceso": "obtener_cargo_usuario", "data": data });
	$.ajax({
		url: "/sys/get_contrato_nuevo_interno.php",
		type: 'POST',
		data: data,
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ "respuesta": "obtener_cargo_usuario", "data": respuesta });
			if (parseInt(respuesta.status) == 200) {
				if (type == "persona_contacto") {
					$('#cargo_id_persona_contacto').val(respuesta.result).trigger('change');
				}else if(type == "responsable"){
					$('#cargo_id_responsable').val(respuesta.result).trigger('change');
				}else if(type == "aprobador"){
					$('#cargo_id_aprobante').val(respuesta.result).trigger('change');
				}			
			}

		},
		error: function() {}
	});
}
