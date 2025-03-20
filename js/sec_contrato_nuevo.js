// INICIO DECLARACION DE VARIABLES ARRAY
var array_propietarios_contrato = [];
var array_inmuebles_contrato = [];
var array_incrementos_contrato = [];
var array_beneficiarios_contrato = [];
var array_adelantos_contrato = [];
var array_adendas_contrato = [];
var array_inflacion_contrato = [];
var array_cuota_extraordinaria_contrato = [];
var array_cambio_cuota_moneda_contrato = [];
var array_terminacion_renovacion_contrato = [];
// FIN DECLARACION DE VARIABLES ARRAY

// INICIO FUNCIONES CONTRATO DE PROVEEDOR
function sec_contrato_nuevo() {
	// INICIO INICIALIZACION DE DATEPICKER
	$(".sec_contrato_nuevo_datepicker")
		.datepicker({
			dateFormat: "dd-mm-yy",
			changeMonth: true,
			changeYear: true,
		})
		.on("change", function (ev) {
			$(this).datepicker("hide");
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});

	$(".fecha_datepicker")
		.datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
		})
		.on("change", function (ev) {
			$(this).datepicker("hide");
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});

	setTimeout(function () {
		sec_contrato_nuevo_select_cargo('persona_contacto');
	}, 1000);

	// FIN INICIALIZACION DE DATEPICKER

	// INICIO ENFOCAR SELECT OPTION TIPO DE CONTRATO
	// setTimeout(function () {
	//   $("#tipo_contrato_id").focus();
	// }, 200);

	// setTimeout(function () {
	//   $("#tipo_contrato_id").select2("open");
	// }, 800);
	// FIN ENFOCAR SELECT OPTION TIPO DE CONTRATO

	// INICIO DECLARACION DE MASK
	$(".num_periodo_gracia").mask("000");
	$(".num_suministro").mask("00000000000000000000", { translation: { 0: { pattern: /[0-9-]/ } } });
	$(".money").mask("00,000.00", { reverse: true });
	$(".vigencia_meses").mask("000");
	$(".formato_porcentaje").mask("00");

	$('.formato_texto').mask('A', {
		translation: {
			'A': { pattern: /[A-Za-z\s]/, recursive: true }
		}
	});

	$(".num_ruc").mask("00000000000");
	// FIN DECLARACION DE MASK

///SELECCIONAR TIPO DE CONTRATO
	sec_cont_nuevo_listar_tipo_contrato();

	// INICIO CARGAR SELECT OPTION
	// sec_contrato_nuevo_obtener_opciones("obtener_tipo_contrato", $("[name='tipo_contrato_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_empresa_at", $("[name='empresa_suscribe_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_personal_responsable", $("[name='personal_responsable_id']"));
	// sec_contrato_nuevo_obtener_opciones("obtener_abogados", $("[name='abogado_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_departamentos", $("[name='modal_inmueble_id_departamento']"));
	sec_contrato_nuevo_obtener_opciones("obtener_departamentos", $("[name='modal_inmueble_id_departamento_ca']"));
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_impuesto_a_la_renta", $("[name='contrato_impuesto_a_la_renta']"));
	sec_contrato_nuevo_obtener_opciones("obtener_periodo", $("[name='periodo']"));
	sec_contrato_nuevo_obtener_opciones("obtener_monedas", $("[name='moneda_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_forma_pago", $("[name='forma_pago']"));
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_comprobante", $("[name='tipo_comprobante']"));
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_terminacion_anticipada", $("[name='tipo_terminacion_anticipada_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_adelantos", $("[name='tipo_terminacion_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_directores", $("[name='director_aprobacion_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_gerentes", $("[name='gerente_area_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_docu_identidad", $("[name='repr_tipo_documento_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_cargos", $("[name='cargo_id_persona_contacto']"));
	sec_contrato_nuevo_obtener_opciones("obtener_cargos", $("[name='cargo_id_responsable']"));
	sec_contrato_nuevo_obtener_opciones("obtener_cargos", $("[name='cargo_id_aprobante']"));

	// NIF16
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_periodicidad", $("[name='modal_if_tipo_periodicidad_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_anio_mes", $("[name='modal_if_tipo_anio_mes']"));
	sec_contrato_nuevo_obtener_opciones("obtener_meses", $("[name='modal_ce_mes']"));

	// FIN CARGAR SELECT OPTION

	// INICIO TAMAÑO MAXIMO ARCHIVO
	$("#form_contrato_proveedor input:file").each(function (i, e) {
		var nombre = $(e).attr("name");
		$(e).on("change", function () {
			if (this.files[0].size > 52428800) {
				swal(
					{
						title: "Archivo debe ser menor a 50 MB",
						text: "",
						type: "warning",
						timer: 3000,
						closeOnConfirm: true,
					},
					function () {
						swal.close();
					}
				);
				$(this).val("");
			}
		});
	});

	$("#director_aprobacion_id").change(function () {
		setTimeout(function () {
			$("#gerente_area_id").focus();
			$("#gerente_area_id").select2("open");
		}, 200);
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

				setTimeout(function () {
					$("#ruc").focus();
				}, 200);
			}

		});
	});

	$("#sec_con_nuevo_repr_legal_posee_banco").change(function () {
		$("#sec_con_nuevo_repr_legal_posee_banco option:selected").each(function () {
			pose_banco_id = $(this).val();

			$("#sec_con_nuevo_prov_banco").val(0).trigger('change');
			$("#sec_con_nuev_prov_nro_cuenta").val('');
			$("#sec_con_nuev_prov_nro_cci").val('');

			if (pose_banco_id == 1) {
				$("#div_repr_legal_banco").show();
				$("#div_repr_legal_num_cuenta").show();
				$("#div_repr_legal_num_cci").show();
				$("#div_repr_legal_nota").show();

				setTimeout(function () {
					$("#sec_con_nuevo_prov_banco").focus();
					$("#sec_con_nuevo_prov_banco").select2("open");
				}, 200);
			} else {
				$("#div_repr_legal_banco").hide();
				$("#div_repr_legal_num_cuenta").hide();
				$("#div_repr_legal_num_cci").hide();
				$("#div_repr_legal_nota").hide();
			}
		});
	});

	$("#sec_con_nuevo_prov_banco").change(function () {
		$("#sec_con_nuevo_prov_banco option:selected").each(function () {
			banco = $(this).val();
			if (banco != 0) {
				setTimeout(function () {
					$("#sec_con_nuev_prov_nro_cuenta").focus();
				}, 200);
			}
		});
	});

	$("#periodo").change(function () {
		$("#periodo option:selected").each(function () {
			periodo = $(this).val();
			if (periodo != 0) {
				setTimeout(function () {
					$("#fecha_inicio").focus();
				}, 200);
			}
		});
	});

	$("#fecha_inicio").change(function () {
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

	$("#moneda_id").change(function () {
		$("#moneda_id option:selected").each(function () {
			moneda_id = $(this).val();
			if (moneda_id != 0) {
				setTimeout(function () {
					$("#monto").focus();
				}, 200);
			}
		});
	});

	$("#tipo_igv_id").change(function () {
		$("#tipo_igv_id option:selected").each(function () {
			tipo_igv_id = $(this).val();
			if (tipo_igv_id != 0) {
				sec_contrato_nuevo_calcular_subtotal_y_igv(tipo_igv_id);
				setTimeout(function () {
					if ($("#tipo_comprobante").val() == "0") {
						$("#tipo_comprobante").focus();
						$("#tipo_comprobante").select2("open");
					}
				}, 200);
			}
		});
	});

	$("#forma_pago").change(function () {
		$("#forma_pago option:selected").each(function () {
			forma_pago = $(this).val();
			if (forma_pago != 0) {
				setTimeout(function () {
					$("#tipo_comprobante").focus();
					$("#tipo_comprobante").select2("open");
				}, 200);
			}
		});
	});

	$("#tipo_comprobante").change(function () {
		$("#tipo_comprobante option:selected").each(function () {
			tipo_comprobante = $(this).val();
			if (tipo_comprobante != 0) {
				setTimeout(function () {
					$("#plazo_pago").focus();
				}, 200);
			}
		});
	});

	$("#tipo_terminacion_anticipada_id").change(function () {
		$("#tipo_terminacion_anticipada_id option:selected").each(function () {
			tipo_terminacion_anticipada_id = $(this).val();
			if (tipo_terminacion_anticipada_id == 0) {
				$("#div_terminacion_anticipada").hide();
				setTimeout(function () {
					$("#tipo_terminacion_anticipada_id").focus();
					$("#tipo_terminacion_anticipada_id").select2("open");
				}, 200);
			} else if (tipo_terminacion_anticipada_id == 1) {
				$("#div_terminacion_anticipada").show();
				setTimeout(function () {
					$("#terminacion_anticipada").focus();
				}, 200);
			} else if (tipo_terminacion_anticipada_id == 2) {
				$("#div_terminacion_anticipada").hide();
				setTimeout(function () {
					$("#observaciones_legal").focus();
				}, 200);
			}
		});
	});
	// FIN TAMAÑO MAXIMO ARCHIVO

	// INICIO CONTRATO DE AGENTE
	/*
	$("#bien_entregado").change(function () {
		var bien_entregado = $("#bien_entregado").val();
		if (bien_entregado == "SI") {
			$("#div_detalle_bien").show();
			$("#detalle_bien_entradado").focus();
		} else {
			$("#div_detalle_bien").hide();
		}
	});
	*/
	// FIN CONTRATO DE AGENTE

	// INICIO EVENTOS CONTRATO DE PROVEEDOR
	$("#repr_tipo_documento_id").change(function () {
		$("#repr_tipo_documento_id option:selected").each(function () {
			tipo_documento_id = $(this).val();
			$('#dni_representante').off('input');
			if (tipo_documento_id == 1) {
                $('#dni_representante').attr('maxlength','8');
                $('#dni_representante').mask("00000000");
                $('#dni_representante').val($('#dni_representante').val().substr(0,8));
            }else if(tipo_documento_id == 2){
				$('#dni_representante').attr('maxlength','11');
                $('#dni_representante').mask("00000000000");
				$('#dni_representante').val($('#dni_representante').val().substr(0,11));
            }else if(tipo_documento_id == 3){
				$('#dni_representante').unmask();
                $('#dni_representante').attr('maxlength', '12');
				$('#dni_representante').val($('#dni_representante').val().substr(0, 12));
				$('#dni_representante').on('input', function() {
					this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
				});
            }else if(tipo_documento_id == 4){
                $('#dni_representante').attr('maxlength','12');
                $('#dni_representante').mask("000000000000");
				$('#dni_representante').val($('#dni_representante').val().substr(0,12));
            }
			setTimeout(function () {
				$("#dni_representante").focus();
			}, 200);

		});
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
					$("#periodo_numero").focus();
				}, 200);
			} else if (plazo_id == 2) {
				$("#div_periodo").hide();
				$("#div_num_dias_para_alertar_vencimiento").hide();
				$("#div_alerta").show();
				$('#alerta_vencimiento_por_fecha_id').val('0').trigger('change.select2');
				$("#div_fecha_de_la_alerta").hide();
				setTimeout(function () {
					$("#fecha_inicio").focus();
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
					$("#moneda_id").focus();
					$("#moneda_id").select2("open");
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
			$("#moneda_id").focus();
			$("#moneda_id").select2("open");
		}, 200);
	});

	///TIPO DE PLAZO DE ARRENDAMIENTO	
	$("#plazo_id_arr").change(function () {
		$("#plazo_id_arr option:selected").each(function () {
			plazo_id = $(this).val();
			if (plazo_id == 1) {
				$(".div_vig_def").show();
				setTimeout(function () {
					$("#contrato_vigencia_del_contrato_en_meses").focus();
				}, 200);
			} else if (plazo_id == 2) {
				$(".div_vig_def").hide();
				setTimeout(function () {
					$("#input_text_contrato_inicio_fecha").focus();
				}, 200);
			}
		});
	});

	$("#subtotal").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		},
	});

	$("#igv").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		},
	});

	$("#monto").on({
		focus: function (event) {
			$(event.target).select();
		},
		change: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
			$("#tipo_igv_id").change();
		},
	});
	// FIN EVENTOS CONTRATO DE PROVEEDOR

	// INICIO GUARDAR CONTRATO DE PROVEEDOR
	$("#form_contrato_proveedor").submit(function (e) {
		e.preventDefault();

		var empresa_suscribe_contrato_id = $("#empresa_suscribe_id").val();
		var persona_contacto = $("#persona_contacto_proveedor").val().trim();
		var aprobacion_obligatoria_id = $("#aprobacion_obligatoria_id").val().trim();
		var director_aprobacion_id = $("#director_aprobacion_id").val().trim();
		var gerente_area_id = $("#gerente_area_id").val().trim();
		var nombre_del_gerente_del_area = $("#nombre_del_gerente_del_area").val().trim();
		var email_del_gerente_del_area = $("#email_del_gerente_del_area").val().trim();
		var num_ruc = $("#ruc").val().trim();
		var razon_social = $("#razon_social").val().trim();
		var nombre_comercial = $("#nombre_comercial").val().trim();
		var detalle_servicio = $("#detalle_servicio").val().trim();
		var plazo_id = $("#plazo_id").val().trim();
		var periodo_numero = $("#periodo_numero").val().trim();
		var periodo_id = $("#periodo").val().trim();
		var num_dias_para_alertar_vencimiento = $("#num_dias_para_alertar_vencimiento").val().trim();
		var alerta_vencimiento_por_fecha_id = $("#alerta_vencimiento_por_fecha_id").val().trim();
		var fecha_de_la_alerta = $("#fecha_de_la_alerta").val().trim();
		var alcance_servicio = $("#alcance_servicio").val().trim();
		var tipo_terminacion_anticipada_id = $("#tipo_terminacion_anticipada_id").val().trim();
		var terminacion_anticipada = $("#terminacion_anticipada").val().trim();
		var fecha_actual = new Date();
		var fecha_de_la_alerta_date = new Date(fecha_de_la_alerta);
		var area_id = $("#area_id").val().trim();

		var cargo_id_persona_contacto = $("#cargo_id_persona_contacto").val().trim();
		var cargo_id_aprobante = $("#cargo_id_aprobante").val().trim();
		var cargo_id_responsable = $("#cargo_id_responsable").val().trim();

		if (empresa_suscribe_contrato_id == 0) {
			alertify.error("Seleccione la empresa que suscribe el contrato.", 5);
			$("#empresa_suscribe_id").focus();
			$("#empresa_suscribe_id").select2("open");
			return false;
		}
		if (area_id == 0) {
			alertify.error("Seleccione el área.", 5);
			$("#area_id").focus();
			$("#area_id").select2("open");
			return false;
		}
		if (persona_contacto.length == 0) {
			alertify.error("Ingrese la Persona contacto (AT).", 5);
			$("#persona_contacto_proveedor").focus();
			return false;
		}
		if (cargo_id_persona_contacto == 0) {
			alertify.error("Seleccione el cargo de la persona de contacto.", 5);
			$("#cargo_id_persona_contacto").focus();
			$("#cargo_id_persona_contacto").select2("open");
			return false;
		}
		if (aprobacion_obligatoria_id == 1 && director_aprobacion_id == 0) {
			alertify.error("Seleccione el director que va aprobar la solicitud.", 5);

			setTimeout(function () {
				$("#director_aprobacion_id").focus();
				$("#director_aprobacion_id").select2("open");
			}, 200);
			
			return false;
		}

		if (director_aprobacion_id != 0 && cargo_id_aprobante == 0) {
			alertify.error("Seleccione el cargo del Aprobador.", 5);
			setTimeout(function () {
				$("#cargo_id_aprobante").focus();
				$("#cargo_id_aprobante").select2("open");
			}, 200);

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
		if (num_ruc.length == 0) {
			alertify.error("Ingrese el número de RUC.", 5);
			$("#ruc").focus();
			return false;
		}

		if (num_ruc.length != 11) {
			alertify.error("El número de RUC es de 11 dígitos, no de " + num_ruc.length + " dígitos.", 5);
			$("#ruc").focus();
			return false;
		}

		if (razon_social.length == 0) {
			alertify.error("Ingrese la Razón Social del Proveedor.", 5);
			$("#razon_social").focus();
			return false;
		}

		if (nombre_comercial.length == 0) {
			alertify.error("Ingrese el Nombre Comercial del Proveedor.", 5);
			$("#nombre_comercial").focus();
			return false;
		}

		if (rr_representantes.length == 0) {
			alertify.error("Agrege al representante legal del proveedor.", 5);
			$("#dni_representante").focus();
			return false;
		}

		if (detalle_servicio.length == 0) {
			alertify.error("Ingrese el objeto del contrato.", 5);
			$("#detalle_servicio").focus();
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

		if (array_contraprestaciones_contrato_proveedor.length == 0) {
			alertify.error("Agregar la contraprestación a la solicitud", 5);
			$("#moneda_id").focus();
			$("#moneda_id").select2("open");
			return false;
		}

		if (alcance_servicio.length == 0) {
			alertify.error("Ingrese el alcance del servicio del contrato.", 5);
			$("#alcance_servicio").focus();
			return false;
		}

		if (tipo_terminacion_anticipada_id == 0) {
			alertify.error("Seleccione el periodo del plazo del contrato.", 5);
			$("#tipo_terminacion_anticipada_id").focus();
			$("#tipo_terminacion_anticipada_id").select2("open");
			return false;
		}

		if (tipo_terminacion_anticipada_id == 1 && terminacion_anticipada.length == 0) {
			alertify.error("Ingrese el detalle de la terminación anticipada.", 5);
			$("#terminacion_anticipada").focus();
			return false;
		}

		var dataForm = new FormData($("#form_contrato_proveedor")[0]);

		dataForm.append("accion", "guardar_contrato_proveedor");
		dataForm.append("tipo_contrato_id", $("#tipo_contrato_id").val());
		dataForm.append("empresa_id", $("#empresa_suscribe_id").val());
		// dataForm.append("area_id", $("#area_responsable_id").val());
		dataForm.append("area_id", $("#area_id").val());
		dataForm.append("personal_id", $("#personal_responsable_id").val());
		dataForm.append("array_nuevos_files_anexos", JSON.stringify(array_nuevos_files_anexos));
		dataForm.append("rr_representantes", JSON.stringify(rr_representantes));
		dataForm.append("contraprestacion_ids", JSON.stringify(array_contraprestaciones_contrato_proveedor));

		auditoria_send({ proceso: "guardar_contrato_proveedor", data: dataForm });

		$.ajax({
			url: "sys/set_contrato_nuevo.php",
			type: "POST",
			data: dataForm,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function (xhr) {
				loading(true);
			},
			success: function (data) {
				var respuesta = JSON.parse(data);

				auditoria_send({ proceso: "guardar_contrato_proveedor", data: respuesta });

				if (parseInt(respuesta.http_code) == 200) {
					swal({
						title: respuesta.mensaje,
						text: "",
						html: true,
						type: respuesta.status,
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
						title: "Error al guardar Solicitud de Proveedor",
						text: respuesta.mensaje,
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false,
					});

					return false;
				}
			},
			complete: function () {
				loading(false);
			},
		});
	});
	// FIN GUARDAR CONTRATO DE PROVEEDOR

	// ADENDA DE ARRENDAMIENTO UBIGEOS
	$("#adenda_inmueble_id_departamento").change(function () {
		console.log("222");
		$("#adenda_inmueble_id_departamento option:selected").each(function () {
			adenda_inmueble_id_departamento = $(this).val();
			var data = {
				accion: "obtener_provincias_segun_departamento",
				departamento_id: adenda_inmueble_id_departamento,
			};
			var array_provincias = [];
			auditoria_send({ proceso: "obtener_provincias_segun_departamento", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}

					if (parseInt(respuesta.http_code) == 200) {
						array_provincias.push(respuesta.result);

						var html = '<option value="0">Seleccione la provincia</option>';
						for (var i = 0; i < array_provincias[0].length; i++) {
							html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
						}

						console.log(html);

						$("#adenda_inmueble_id_provincia").html(html).trigger("change");

						setTimeout(function () {
							$("#adenda_inmueble_id_provincia").select2("open");
						}, 500);

						return false;
					}
				},
				error: function () {},
			});
		});
	});

	$("#adenda_inmueble_id_provincia").change(function () {
		$("#adenda_inmueble_id_provincia option:selected").each(function () {
			adenda_inmueble_id_provincia = $(this).val();
			adenda_inmueble_id_departamento = $("#adenda_inmueble_id_departamento").val();
			var data = {
				accion: "obtener_distritos_segun_provincia",
				provincia_id: adenda_inmueble_id_provincia,
				departamento_id: adenda_inmueble_id_departamento,
			};
			var array_distritos = [];
			auditoria_send({ proceso: "obtener_distritos_segun_provincia", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}

					if (parseInt(respuesta.http_code) == 200) {
						array_distritos.push(respuesta.result);
						console.log("Cantidad de Registro: " + array_distritos.length);
						var html = '<option value="0">Seleccione el distrito</option>';

						for (var i = 0; i < array_distritos[0].length; i++) {
							html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
						}

						console.log(html);

						$("#adenda_inmueble_id_distrito").html(html).trigger("change");

						setTimeout(function () {
							$("#adenda_inmueble_id_distrito").select2("open");
						}, 500);

						return false;
					}
				},
				error: function () {},
			});
		});
	});

	$("#adenda_inmueble_id_distrito").change(function () {
		var departamento_id = $("#adenda_inmueble_id_departamento").val().toString();
		var provincia_id = $("#adenda_inmueble_id_provincia").val().toString();
		var distrito_id = $("#adenda_inmueble_id_distrito").val().toString();

		var departamento_text = "";
		var data = $("#adenda_inmueble_id_departamento").select2("data");
		if (data) {
			departamento_text = data[0].text;
		}

		var provincia_text = "";
		var data = $("#adenda_inmueble_id_provincia").select2("data");
		if (data) {
			provincia_text = data[0].text;
		}

		var distrito_text = "";
		var data = $("#adenda_inmueble_id_distrito").select2("data");
		if (data) {
			distrito_text = data[0].text;
		}

		$("#ubigeo_id_nuevo").val(departamento_id + provincia_id + distrito_id);
		$("#ubigeo_text_nuevo").val(departamento_text + "/" + provincia_text + "/" + distrito_text);
	});
	// FIN ADENDA DE ARRENDAMIENTO UBIGEOS

	// INICIO GUARDAR ACUERDO DE CONFIDENCIALIDAD
	$("#form_acuerdo_confidencialidad").submit(function (e) {
		e.preventDefault();

		if ($("#empresa_suscribe_id").val() == "0") {
			swal(
				{
					title: "Escoger empresa que suscribe el contrato",
					text: "",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true,
				},
				function () {
					swal.close();
					$("#empresa_suscribe_id").focus();
				}
			);
			return false;
		}

		if ($("[name='persona_contacto_proveedor_ac']").val().length < 0) {
			swal(
				{
					title: "Ingrese Persona contacto",
					text: "",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true,
				},
				function () {
					swal.close();
					$("[name='persona_contacto_proveedor_ac']").focus();
				}
			);
			return false;
		}
		if ($("[name='ruc_ac']").val().length != 11) {
			swal(
				{
					title: "RUC debe tener 11 dígitos",
					text: "",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true,
				},
				function () {
					swal.close();
					$("[name='ruc_ac']").focus();
				}
			);
			return false;
		}

		if (rr_representantes.length == 0) {
			swal(
				{
					title: "No ha proporcionado datos del representante legal",
					text: "",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true,
				},
				function () {
					swal.close();
					$("[name='dni_representante_ac']").focus();
				}
			);
			return false;
		}

		var check_gerencia_proveedor_ac = 0;

		if (document.getElementById("check_gerencia_proveedor_ac").checked) {
			check_gerencia_proveedor_ac = 1;
		} else {
			check_gerencia_proveedor_ac = 0;
		}

		var dataForm = new FormData($("#form_acuerdo_confidencialidad")[0]);

		dataForm.append("accion", "guardar_acuerdo_confidencialidad");
		dataForm.append("tipo_contrato_id", $("#tipo_contrato_id").val());
		dataForm.append("empresa_id", $("#empresa_suscribe_id").val());
		dataForm.append("area_id", $("#area_responsable_id").val());
		dataForm.append("personal_id", $("#personal_responsable_id").val());
		dataForm.append("array_nuevos_files_anexos", JSON.stringify(array_nuevos_files_anexos));
		dataForm.append("rr_representantes", JSON.stringify(rr_representantes));
		dataForm.append("check_gerencia_proveedor_ac", check_gerencia_proveedor_ac);
		dataForm.append("contraprestacion_ids", JSON.stringify(array_contraprestaciones_contrato_proveedor));

		$.ajax({
			url: "sys/set_contrato_nuevo.php",
			type: "POST",
			data: dataForm,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function (xhr) {
				loading(true);
			},
			success: function (data) {
				var respuesta = JSON.parse(data);
				swal({
					title: respuesta.mensaje,
					text: "",
					html: true,
					type: respuesta.status,
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
				});
				if (parseInt(respuesta.http_code) == 200) {
					setTimeout(function () {
						window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
						return false;
					}, 3000);
				}
			},
			complete: function () {
				loading(false);
			},
		});
	});
	// FIN GUARDAR ACUERDO DE CONFIDENCIALIDAD

	// INICIO CHANGE DATOS GENERALES DEL CONTRATO
	$("#tipo_contrato_id").change(function () {
		$("#tipo_contrato_id option:selected").each(function () {
			tipo_contrato_id = $(this).val();
			if (tipo_contrato_id == 1) {
				// Contrato de arrendamiento
				$("#div_detalle_solicitud_proveedor_izquierda").hide();
				$("#div_detalle_solicitud_izquierda").hide();
				$("#div_detalle_solicitud_derecha").hide();
				$("#divContratoArrendamiento").show();
				$("#divContratoProveedor").hide();
				$("#divAcuerdoDeConfidencialidad").hide();
				$("#divContratoAgente").hide();
				$("#div_personal_responsable").show();
				$("#div_empresa_suscribe").show();
				$("#div_nombre_tienda").hide();
				$("#div_nombre_proveedor").hide();
				$("#div_fecha_contrato_proveedor").hide();
				$("#div_correos_adicionales").hide();

				sec_contrato_nuevo_actualizar_tabla_propietarios();
				sec_contrato_nuevo_actualizar_tabla_beneficiario();

				setTimeout(function () {
					$("#empresa_suscribe_id").select2("open");
				}, 200);
			} else if (tipo_contrato_id == 2) {
				$("#div_detalle_solicitud_proveedor_izquierda").hide();
				$("#div_detalle_solicitud_izquierda").hide();
				$("#div_detalle_solicitud_derecha").hide();
				$("#divContratoArrendamiento").hide();
				$("#divContratoProveedor").show();
				$("#divAcuerdoDeConfidencialidad").hide();
				$("#divContratoAgente").hide();
				$("#div_personal_responsable").hide();
				$("#div_empresa_suscribe").show();
				$("#div_nombre_tienda").hide();
				$("#div_nombre_proveedor").hide();
				$("#div_fecha_contrato_proveedor").hide();
				$("#div_correos_adicionales").hide();
				$("#div_area_id").show();
				setTimeout(function () {
					$("#empresa_suscribe_id").select2("open");
				}, 200);
			} else if (tipo_contrato_id == 3) {
				$("#div_detalle_solicitud_proveedor_izquierda").hide();
				$("#div_detalle_solicitud_izquierda").hide();
				$("#div_detalle_solicitud_derecha").hide();
				$("#divContratoArrendamiento").hide();
				$("#divContratoProveedor").hide();
				$("#divAcuerdoDeConfidencialidad").hide();
				$("#divContratoAgente").hide();
				$("#div_personal_responsable").hide();
				$("#div_empresa_suscribe").hide();
				$("#div_nombre_tienda").show();
				$("#div_nombre_proveedor").hide();
				$("#div_fecha_contrato_proveedor").hide();
				$("#div_correos_adicionales").hide();
				setTimeout(function () {
					$("#contrato_id_segun_nombre_tienda").select2("open");
				}, 200);
			} else if (tipo_contrato_id == 4) {
				// $('#div_detalle_solicitud_proveedor_izquierda').show();
				$("#div_detalle_solicitud_izquierda").hide();
				// $('#div_detalle_solicitud_derecha').show();
				$("#divContratoArrendamiento").hide();
				$("#divContratoProveedor").hide();
				$("#divAcuerdoDeConfidencialidad").hide();
				$("#divContratoAgente").hide();
				$("#div_personal_responsable").hide();
				$("#div_empresa_suscribe").hide();
				$("#div_nombre_tienda").hide();
				$("#div_nombre_proveedor").show();
				$("#div_fecha_contrato_proveedor").show();
				$("#div_correos_adicionales").hide();
				setTimeout(function () {
					$("#contrato_num_ruc_proveedor").select2("open");
				}, 200);
			} else if (tipo_contrato_id == 5) {
				// Acuerdo de Confidencialidad
				$("#div_detalle_solicitud_proveedor_izquierda").hide();
				$("#div_detalle_solicitud_izquierda").hide();
				$("#div_detalle_solicitud_derecha").hide();
				$("#divContratoArrendamiento").hide();
				$("#divContratoProveedor").hide();
				$("#divAcuerdoDeConfidencialidad").show();
				$("#divContratoAgente").hide();
				$("#div_personal_responsable").hide();
				$("#div_empresa_suscribe").show();
				$("#div_nombre_tienda").hide();
				$("#div_nombre_proveedor").hide();
				$("#div_fecha_contrato_proveedor").hide();
				$("#div_correos_adicionales").hide();
				setTimeout(function () {
					$("#empresa_suscribe_id").select2("open");
				}, 200);
			} else if (tipo_contrato_id == 6) {
				// Contrato de agente
				$("#div_detalle_solicitud_proveedor_izquierda").hide();
				$("#div_detalle_solicitud_izquierda").hide();
				$("#div_detalle_solicitud_derecha").hide();
				$("#divContratoArrendamiento").hide();
				$("#divContratoProveedor").hide();
				$("#divAcuerdoDeConfidencialidad").hide();
				$("#divContratoAgente").show();
				$("#div_personal_responsable").show();
				$("#div_empresa_suscribe").hide();
				$("#div_nombre_tienda").hide();
				$("#div_nombre_proveedor").hide();
				$("#div_fecha_contrato_proveedor").hide();
				$("#div_correos_adicionales").show();
				$("#div_nombre_local").show();
				$("#div_centro_costos").show();
				$("#porcentaje_participacion_bet").val("65");
				$("#porcentaje_participacion_j").val("50");
				$("#porcentaje_participacion_ter").val("50");
				$("#porcentaje_participacion_bin").val("5");
				$("#porcentaje_participacion_dep").val("2");
				$("#periodo_numero_ca").val("2");
			 

				sec_contrato_nuevo_actualizar_tabla_propietarios_ca();
				sec_contrato_nuevo_actualizar_tabla_beneficiario_ca();

				setTimeout(function () {
					$("#personal_responsable_id").val("1137").trigger('change');
		 
				}, 200);
				
			}
		});
	});

	$("#empresa_suscribe_id").change(function () {
		$("#empresa_suscribe_id option:selected").each(function () {
			empresa_suscribe_id = $(this).val();
			tipo_contrato_id = $("#tipo_contrato_id").val();
			if (tipo_contrato_id == 1) {
				if (empresa_suscribe_id != 0) {
					setTimeout(function () {
						$("#personal_responsable_id").select2("open");
					}, 200);
				}
			} else if (tipo_contrato_id == 2) {
				setTimeout(function () {
					$("#persona_contacto_proveedor").focus();
				}, 100);
			}
		});
	});

	$("#area_responsable_id").change(function () {
		$("#area_responsable_id option:selected").each(function () {
			area_responsable_id = $(this).val();
			var data = {
				accion: "obtener_personal_segun_area",
				area_id: area_responsable_id,
			};
			var array_personal = [];
			auditoria_send({ proceso: "obtener_personal_segun_area", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}

					if (parseInt(respuesta.http_code) == 200) {
						array_personal.push(respuesta.result);
						console.log("Cantidad de Registro: " + array_personal.length);
						var html = '<option value="">Seleccione al personal responsable</option>';

						for (var i = 0; i < array_personal[0].length; i++) {
							html += "<option value=" + array_personal[0][i].id + ">" + array_personal[0][i].nombre_completo + "</option>";
						}

						console.log(html);

						$("#personal_responsable_id").html(html).trigger("change");

						return false;
					}
				},
				error: function () {},
			});

			if (area_responsable_id != 0) {
				setTimeout(function () {
					$("#personal_responsable_id").select2("open");
				}, 200);
			}
		});
	});

	$("#personal_responsable_id").change(function () {
		$("#personal_responsable_id option:selected").each(function () {
			personal_responsable_id = $(this).val();
			tipo_contrato_id = $("#tipo_contrato_id").val();
			if (tipo_contrato_id == 1) {
				if (personal_responsable_id != 0 && array_propietarios_contrato.length == 0) {
					setTimeout(function () {
						sec_contrato_nuevo_buscar_propietario_modal("arrendamiento");
					}, 500);
					setTimeout(function () {
						$("#modal_propietario_nombre_o_numdocu").focus();
					}, 700);
				}
			}
		});
	});

	$("#contrato_id_segun_nombre_tienda").change(function () {
		$("#contrato_id_segun_nombre_tienda option:selected").each(function () {
			id_contrato_arrendamiento = $(this).val();
			if (id_contrato_arrendamiento != 0) {
				$("#div_detalle_solicitud_izquierda").show();
				$("#div_detalle_solicitud_derecha").show();

				var data = {
					accion: "obtener_contrato_arrendamiento_por_id",
					id_contrato_arrendamiento: id_contrato_arrendamiento,
				};

				auditoria_send({ proceso: "obtener_contrato_arrendamiento_por_id", data: data });
				$.ajax({
					url: "/sys/set_contrato_nuevo.php",
					type: "POST",
					data: data,
					beforeSend: function () {
						loading("true");
					},
					complete: function () {
						loading();
					},
					success: function (resp) {
						//  alert(datat)
						var respuesta = JSON.parse(resp);
						console.log(respuesta);
						if (parseInt(respuesta.http_code) == 400) {
						}

						if (parseInt(respuesta.http_code) == 200) {
							var html = respuesta.result;
							$("#contrato_id").val(id_contrato_arrendamiento);
							console.log(html);
							$('#tipo_contrato_id').select2('open');
							$("#form_adenda_arrendamiento").html(html);
							$('#tipo_contrato_id').select2('close');
							return false;
						}
					},
					error: function () {},
				});
			}
		});
	});

	$("#contrato_num_ruc_proveedor").change(function () {
		$("#contrato_num_ruc_proveedor option:selected").each(function () {
			num_ruc_proveedor = $(this).val();

			var array_contratos_proveedor = [];

			var data = {
				accion: "obtener_contratos_proveedor",
				num_ruc_proveedor: num_ruc_proveedor,
			};

			if (contrato_id_segun_nombre_tienda != 0) {
				auditoria_send({ proceso: "obtener_contratos_proveedor", data: data });
				$.ajax({
					url: "/sys/set_contrato_nuevo.php",
					type: "POST",
					data: data,
					beforeSend: function () {
						loading("true");
					},
					complete: function () {
						loading();
					},
					success: function (resp) {
						//  alert(datat)
						var respuesta = JSON.parse(resp);
						console.log(respuesta);
						if (parseInt(respuesta.http_code) == 400) {
						}

						if (parseInt(respuesta.http_code) == 200) {
							array_contratos_proveedor.push(respuesta.result);
							console.log("Cantidad de Registro: " + array_contratos_proveedor.length);
							var html = '<option value="0">- Seleccione -</option>';

							for (var i = 0; i < array_contratos_proveedor[0].length; i++) {
								html +=
									"<option value=" +
									array_contratos_proveedor[0][i].contrato_id +
									">" +
									array_contratos_proveedor[0][i].fecha_inicio +
									" - " +
									array_contratos_proveedor[0][i].detalle_servicio +
									"</option>";
							}

							console.log(html);

							$("#contrato_id_contrato_proveedor").html(html).trigger("change");

							return false;
						}
					},
					error: function () {},
				});
			}
		});
	});

	$("#contrato_id_contrato_proveedor").change(function () {
		$("#contrato_id_contrato_proveedor option:selected").each(function () {
			id_contrato_proveedor = $(this).val();
			if (id_contrato_proveedor != 0) {
				$("#div_detalle_solicitud_proveedor_izquierda").show();
				$("#div_detalle_solicitud_derecha").show();

				var data = {
					accion: "obtener_contrato_proveedor_por_id",
					id_contrato_proveedor: id_contrato_proveedor,
				};

				auditoria_send({ proceso: "obtener_contrato_proveedor_por_id", data: data });
				$.ajax({
					url: "/sys/set_contrato_nuevo.php",
					type: "POST",
					data: data,
					beforeSend: function () {
						loading("true");
					},
					complete: function () {
						loading();
					},
					success: function (resp) {
						//  alert(datat)
						var respuesta = JSON.parse(resp);
						console.log(respuesta);
						if (parseInt(respuesta.http_code) == 400) {
						}

						if (parseInt(respuesta.http_code) == 200) {
							var html = respuesta.result;
							$("#contrato_id").val(id_contrato_proveedor);
							console.log(html);
							$('#tipo_contrato_id').select2('open');
							$("#form_adenda_proveedor").html(html);
							$('#tipo_contrato_id').select2('close');
							return false;
						}
					},
					error: function () {},
				});
			}
		});
	});
	// FIN CHANGE DATOS GENERALES DEL CONTRATO

	// INICIO CHANGE PROPIETARIOS
	$("#modal_propietario_tipo_busqueda").change(function () {
		$("#modal_propietario_tipo_busqueda option:selected").each(function () {
			tipo_busqueda = $(this).val();
			if (tipo_busqueda == 1) {
				$("#modal_propietario_nombre_o_numdocu").attr("placeholder", "Ingrese el nombre, despues los apellidos");
			} else if (tipo_busqueda == 2) {
				$("#modal_propietario_nombre_o_numdocu").attr("placeholder", "Ingrese el número de documento");
			}

			setTimeout(function () {
				$("#modal_propietario_nombre_o_numdocu").focus();
			}, 100);
		});
	});

	$("#modal_propietario_tipo_persona").change(function () {
		$("#modal_propietario_tipo_persona option:selected").each(function () {
			tipo_persona = $(this).val();
			if (tipo_persona == 1) {
				$("#modal_propietario_tipo_docu").val("1");
				$("#div_modal_propietario_representante_legal").hide();
				$("#div_modal_propietario_num_partida_registral").hide();
			} else if (tipo_persona == 2) {
				$("#modal_propietario_tipo_docu").val("2");
				$("#div_modal_propietario_representante_legal").show();
				$("#div_modal_propietario_num_partida_registral").show();
			}
			$("#modal_propietario_tipo_docu").change();
			setTimeout(function () {
				$("#modal_propietario_nombre").focus();
			}, 200);
		});
	});

	$("#modal_propietario_tipo_persona_ca").change(function () {
		$("#modal_propietario_tipo_persona_ca option:selected").each(function () {
			tipo_persona = $(this).val();
			if (tipo_persona == 1) {
				$("#modal_propietario_tipo_docu_ca").val("1");
				$("#div_modal_propietario_representante_legal_ca").hide();
				$("#div_modal_propietario_num_partida_registral_ca").hide();
			} else if (tipo_persona == 2) {
				$("#modal_propietario_tipo_docu_ca").val("2");
				$("#div_modal_propietario_representante_legal_ca").show();
				$("#div_modal_propietario_num_partida_registral_ca").show();
			}
			$("#modal_propietario_tipo_docu_ca").change();
			setTimeout(function () {
				$("#modal_propietario_nombre").focus();
			}, 200);
		});
	});

	$("#modal_propietario_tipo_docu").change(function () {
		$("#modal_propietario_tipo_docu option:selected").each(function () {
			propietario_tipo_docu = $(this).val();
			if (propietario_tipo_docu == 1 || propietario_tipo_docu == 3 || propietario_tipo_docu == 4) {
				$("#div_num_docu_propietario").show();

				if (propietario_tipo_docu == 1) {
					$("#label_num_docu_propietario").html("Número de DNI del propietario:");
					$(".mask_dni_agente").mask("00000000");
				} else if (propietario_tipo_docu == 3) {
					$("#label_num_docu_propietario").html("Número de Pasaporte del propietario:");
					$(".mask_dni_agente").mask("000000000000");
				} else if (propietario_tipo_docu == 4) {
					$("#label_num_docu_propietario").html("Número de Carnet Ext. propietario:");
					$(".mask_dni_agente").mask("000000000000");
				}
			
				setTimeout(function () {
					$("#modal_propietario_num_docu").focus();
				}, 200);
			} else if (propietario_tipo_docu == 2) {
				$("#div_num_docu_propietario").hide();

				setTimeout(function () {
					$("#modal_propietario_num_ruc").focus();
				}, 200);
			}
		});
	});

	$("#modal_propietario_tipo_docu_ca").change(function () {
		$("#modal_propietario_tipo_docu_ca option:selected").each(function () {
			propietario_tipo_docu = $(this).val();
			if (propietario_tipo_docu == 1 || propietario_tipo_docu == 3) {
				$("#div_num_docu_propietario_ca").show();

				if (propietario_tipo_docu == 1) {
					$("#label_num_docu_propietario_ca").html("Número de DNI del propietario:");
				} else if (propietario_tipo_docu == 3) {
					$("#label_num_docu_propietario_ca").html("Número de Pasaporte del propietario:");
				}

				setTimeout(function () {
					$("#modal_propietario_num_docu_ca").focus();
				}, 200);
			} else if (propietario_tipo_docu == 2) {
				$("#div_num_docu_propietario_ca").hide();

				setTimeout(function () {
					$("#modal_propietario_num_ruc_ca").focus();
				}, 200);
			}
		});
	});

	$("#modal_propietario_tipo_persona_contacto").change(function () {
		$("#modal_propietario_tipo_persona_contacto option:selected").each(function () {
			tipo_persona_contacto = $(this).val();
			if (tipo_persona_contacto == 1) {
				$("#div_modal_propietario_contacto_nombre").hide();
				$("#modal_propietario_contacto_telefono").focus();
			} else if (tipo_persona_contacto == 2) {
				$("#div_modal_propietario_contacto_nombre").show();
				$("#modal_propietario_contacto_nombre").focus();
			}
		});
	});

	$("#modal_propietario_tipo_persona_contacto_ca").change(function () {
		$("#modal_propietario_tipo_persona_contacto_ca option:selected").each(function () {
			tipo_persona_contacto = $(this).val();
			if (tipo_persona_contacto == 1) {
				$("#div_modal_propietario_contacto_nombre_ca").hide();
				$("#modal_propietario_contacto_telefono_ca").focus();
			} else if (tipo_persona_contacto == 2) {
				$("#div_modal_propietario_contacto_nombre_ca").show();
				$("#modal_propietario_contacto_nombre_ca").focus();
			}
		});
	});
	// FIN CHANGE PROPIETARIOS

	// INICIO CHANGE INMUEBLES
	$("#modal_inmueble_area_arrendada").on({
		focus: function (event) {
			$(event.target).select();
		},
		change: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		},
	});

	$("#modal_inmueble_id_departamento").change(function () {
		$("#modal_inmueble_id_departamento option:selected").each(function () {
			modal_inmueble_id_departamento = $(this).val();
			var data = {
				accion: "obtener_provincias_segun_departamento",
				departamento_id: modal_inmueble_id_departamento,
			};
			var array_provincias = [];
			auditoria_send({ proceso: "obtener_provincias_segun_departamento", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}

					if (parseInt(respuesta.http_code) == 200) {
						array_provincias.push(respuesta.result);
						console.log("Cantidad de Registro: " + array_provincias.length);
						var html = '<option value="0">Seleccione la provincia</option>';

						for (var i = 0; i < array_provincias[0].length; i++) {
							html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
						}

						console.log(html);

						$("#modal_inmueble_id_provincia").html(html).trigger("change");

						setTimeout(function () {
							$("#modal_inmueble_id_provincia").select2("open");
						}, 500);

						return false;
					}
				},
				error: function () {},
			});
		});
	});

	$("#modal_inmueble_id_departamento_ca").change(function () {
		$("#modal_inmueble_id_departamento_ca option:selected").each(function () {
			modal_inmueble_id_departamento = $(this).val();
			var data = {
				accion: "obtener_provincias_segun_departamento",
				departamento_id: modal_inmueble_id_departamento,
			};
			var array_provincias = [];
			auditoria_send({ proceso: "obtener_provincias_segun_departamento", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}

					if (parseInt(respuesta.http_code) == 200) {
						array_provincias.push(respuesta.result);
						console.log("Cantidad de Registro: " + array_provincias.length);
						var html = '<option value="0">Seleccione la provincia</option>';

						for (var i = 0; i < array_provincias[0].length; i++) {
							html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
						}

						console.log(html);

						$("#modal_inmueble_id_provincia_ca").html(html).trigger("change");

						setTimeout(function () {
							$("#modal_inmueble_id_provincia_ca").select2("open");
						}, 500);

						return false;
					}
				},
				error: function () {},
			});
		});
	});

	$("#modal_inmueble_id_provincia").change(function () {
		$("#modal_inmueble_id_provincia option:selected").each(function () {
			modal_inmueble_id_provincia = $(this).val();
			modal_inmueble_id_departamento = $("#modal_inmueble_id_departamento").val();
			var data = {
				accion: "obtener_distritos_segun_provincia",
				provincia_id: modal_inmueble_id_provincia,
				departamento_id: modal_inmueble_id_departamento,
			};
			var array_distritos = [];
			auditoria_send({ proceso: "obtener_distritos_segun_provincia", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}

					if (parseInt(respuesta.http_code) == 200) {
						array_distritos.push(respuesta.result);
						console.log("Cantidad de Registro: " + array_distritos.length);
						var html = '<option value="0">Seleccione el distrito</option>';

						for (var i = 0; i < array_distritos[0].length; i++) {
							html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
						}

						console.log(html);

						$("#modal_inmueble_id_distrito").html(html).trigger("change");

						setTimeout(function () {
							$("#modal_inmueble_id_distrito").select2("open");
						}, 500);

						return false;
					}
				},
				error: function () {},
			});
		});
	});

	$("#modal_inmueble_id_provincia_ca").change(function () {
		$("#modal_inmueble_id_provincia_ca option:selected").each(function () {
			modal_inmueble_id_provincia = $(this).val();
			modal_inmueble_id_departamento = $("#modal_inmueble_id_departamento_ca").val();
			var data = {
				accion: "obtener_distritos_segun_provincia",
				provincia_id: modal_inmueble_id_provincia,
				departamento_id: modal_inmueble_id_departamento,
			};
			var array_distritos = [];
			auditoria_send({ proceso: "obtener_distritos_segun_provincia", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}

					if (parseInt(respuesta.http_code) == 200) {
						array_distritos.push(respuesta.result);
						console.log("Cantidad de Registro: " + array_distritos.length);
						var html = '<option value="0">Seleccione el distrito</option>';

						for (var i = 0; i < array_distritos[0].length; i++) {
							html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
						}

						console.log(html);

						$("#modal_inmueble_id_distrito_ca").html(html).trigger("change");

						setTimeout(function () {
							$("#modal_inmueble_id_distrito_ca").select2("open");
						}, 500);

						return false;
					}
				},
				error: function () {},
			});
		});
	});

	$("#modal_inmueble_id_distrito").change(function () {
		$("#modal_inmueble_id_distrito option:selected").each(function () {
			inmueble_id_distrito = $(this).val();
			if (inmueble_id_distrito != 0) {
				setTimeout(function () {
					$("#modal_inmueble_ubicacion").focus();
				}, 100);
			}
		});
	});

	$("#modal_inmueble_id_distrito_ca").change(function () {
		$("#modal_inmueble_id_distrito_ca option:selected").each(function () {
			inmueble_id_distrito = $(this).val();
			if (inmueble_id_distrito != 0) {
				setTimeout(function () {
					$("#modal_inmueble_ubicacion_ca").focus();
				}, 100);
			}
		});
	});

	$("#modal_inmueble_tipo_compromiso_pago_agua").change(function () {
		$("#modal_inmueble_tipo_compromiso_pago_agua option:selected").each(function () {
			inmueble_tipo_compromiso_pago_agua = $(this).val();
			if (inmueble_tipo_compromiso_pago_agua == 0) {
				$("#div_modal_inmueble_monto_o_porcentaje_recibo_agua").hide();
				setTimeout(function () {
					$("#modal_inmueble_tipo_compromiso_pago_agua").focus();
				}, 100);
			} else if (
				inmueble_tipo_compromiso_pago_agua == 3 ||
				inmueble_tipo_compromiso_pago_agua == 4 ||
				inmueble_tipo_compromiso_pago_agua == 5 ||
				inmueble_tipo_compromiso_pago_agua == 8
			) {
				$("#div_modal_inmueble_monto_o_porcentaje_recibo_agua").hide();
				setTimeout(function () {
					$("#modal_inmueble_num_suministro_luz").focus();
				}, 100);
			} else {
				$("#modal_inmueble_monto_o_porcentaje_agua").unmask();
				if (inmueble_tipo_compromiso_pago_agua == 1) {
					$("#modal_inmueble_monto_o_porcentaje_agua").mask("00");
					$("#div_inmueble_label_monto_o_porcentaje_agua").html("(%) del recibo de agua");
				} else if (inmueble_tipo_compromiso_pago_agua == 2) {
					$("#div_inmueble_label_monto_o_porcentaje_agua").html("Monto fijo del servicio de agua");
				} else if (inmueble_tipo_compromiso_pago_agua == 6) {
					$("#div_inmueble_label_monto_o_porcentaje_agua").html("Monto base del servicio de agua");
				} else if (inmueble_tipo_compromiso_pago_agua == 7) {
					$("#div_inmueble_label_monto_o_porcentaje_agua").html("Monto a facturar del servicio de agua");
				}
				$("#div_modal_inmueble_monto_o_porcentaje_recibo_agua").show();
				$("#modal_inmueble_monto_o_porcentaje_agua").val("");
				setTimeout(function () {
					$("#modal_inmueble_monto_o_porcentaje_agua").focus();
				}, 100);
			}
		});
	});

	$("#modal_inmueble_tipo_compromiso_pago_luz").change(function () {
		$("#modal_inmueble_tipo_compromiso_pago_luz option:selected").each(function () {
			inmueble_tipo_compromiso_pago_luz = $(this).val();
			if (inmueble_tipo_compromiso_pago_luz == 0) {
				$("#div_modal_inmueble_monto_o_porcentaje_recibo_luz").hide();
				setTimeout(function () {
					$("#modal_inmueble_tipo_compromiso_pago_luz").focus();
				}, 100);
			} else if (
				inmueble_tipo_compromiso_pago_luz == 3 ||
				inmueble_tipo_compromiso_pago_luz == 4 ||
				inmueble_tipo_compromiso_pago_luz == 5 ||
				inmueble_tipo_compromiso_pago_luz == 8
			) {
				$("#div_modal_inmueble_monto_o_porcentaje_recibo_luz").hide();
				setTimeout(function () {
					$("#modal_inmueble_tipo_compromiso_pago_arbitrios").select2("open");
					$("#modal_inmueble_tipo_compromiso_pago_arbitrios").focus();
				}, 100);
			} else {
				$("#modal_inmueble_monto_o_porcentaje_luz").unmask();
				if (inmueble_tipo_compromiso_pago_luz == 1) {
					$("#modal_inmueble_monto_o_porcentaje_luz").mask("00");
					$("#div_inmueble_label_monto_o_porcentaje_luz").html("(%) del recibo de luz");
				} else if (inmueble_tipo_compromiso_pago_luz == 2) {
					$("#div_inmueble_label_monto_o_porcentaje_luz").html("Monto fijo del servicio de luz");
				} else if (inmueble_tipo_compromiso_pago_luz == 6) {
					$("#div_inmueble_label_monto_o_porcentaje_luz").html("Monto base del servicio de luz");
				} else if (inmueble_tipo_compromiso_pago_luz == 7) {
					$("#div_inmueble_label_monto_o_porcentaje_luz").html("Monto a facturar del servicio de luz");
				}
				$("#div_modal_inmueble_monto_o_porcentaje_recibo_luz").show();
				$("#modal_inmueble_monto_o_porcentaje_luz").val("");
				setTimeout(function () {
					$("#modal_inmueble_monto_o_porcentaje_luz").focus();
				}, 100);
			}
		});
	});

	$("#modal_inmueble_tipo_compromiso_pago_arbitrios").change(function () {
		$("#modal_inmueble_tipo_compromiso_pago_arbitrios option:selected").each(function () {
			inmueble_tipo_compromiso_pago_arbitrios = $(this).val();
			if (inmueble_tipo_compromiso_pago_arbitrios == 0) {
				$("#div_modal_inmueble_porcentaje_pago_arbitrios").hide();
				setTimeout(function () {
					$("#modal_inmueble_tipo_compromiso_pago_arbitrios").focus();
				}, 100);
			} else if (inmueble_tipo_compromiso_pago_arbitrios == 2) {
				$("#div_modal_inmueble_porcentaje_pago_arbitrios").hide();
				setTimeout(function () {
					$("#contrato_tipo_moneda_renta_pactada").select2("open");
				}, 200);
			} else if (inmueble_tipo_compromiso_pago_arbitrios == 1) {
				$("#div_modal_inmueble_porcentaje_pago_arbitrios").show();
				setTimeout(function () {
					$("#modal_inmueble_porcentaje_pago_arbitrios").focus();
				}, 100);
			}
		});
	});
	// FIN CHANGE INMUEBLES

	// INICIO CHANGE CONDICIONES ECONOMICAS Y COMERCIALES
	$("#contrato_tipo_moneda_renta_pactada").change(function () {
		$("#contrato_tipo_moneda_renta_pactada option:selected").each(function () {
			tipo_moneda_renta_pactada = $(this).val();
			if (tipo_moneda_renta_pactada != 0) {
				sec_contrato_nuevo_calcular_monto_a_pagar();
				setTimeout(function () {
					$("#contrato_monto_renta").focus();
				}, 100);
			}
		});
	});

	$("#contrato_tipo_moneda_renta_pactada").change(function () {
		$("#contrato_tipo_moneda_renta_pactada option:selected").each(function () {
			tipo_moneda_renta_pactada = $(this).val();
			if (tipo_moneda_renta_pactada != 0) {
				sec_contrato_nuevo_calcular_monto_a_pagar();
				setTimeout(function () {
					$("#contrato_monto_renta").focus();
				}, 100);
			}
		});
	});

	$("#tipo_pago_de_renta_id").change(function () {
		$("#tipo_pago_de_renta_id option:selected").each(function () {
			tipo_pago_de_renta_id = $(this).val();
			$("#div_porcentaje_venta").hide();
			$("#div_tipo_venta").hide();
			
			if (parseInt(tipo_pago_de_renta_id) == 2) {
				$("#div_porcentaje_venta").show();
				$("#div_tipo_venta").show();
			}

			setTimeout(function () {
				$("#contrato_monto_renta").focus();
			}, 200);
		});
	});

	$("#tipo_venta_id").change(function () {
		$("#tipo_venta_id option:selected").each(function () {
			tipo_venta_id = $(this).val();
			if (tipo_venta_id != 0) {
				setTimeout(function () {
					$("#tipo_igv_renta_id").select2("open");
				}, 100);
			}
		});
	});

	$("#tipo_igv_renta_id").change(function () {
		$("#tipo_igv_renta_id option:selected").each(function () {
			tipo_igv_renta_id = $(this).val();
			if (tipo_igv_renta_id != 0) {
				setTimeout(function () {
					$("#contrato_monto_garantia").focus();
				}, 100);
			}
		});
	});

	$("#contrato_impuesto_a_la_renta_carta_de_instruccion_id").change(function () {
		$("#contrato_impuesto_a_la_renta_carta_de_instruccion_id option:selected").each(function () {
			sec_contrato_nuevo_calcular_monto_a_pagar();
		});
	});

	$("#contrato_adelanto").change(function () {
		$("#contrato_adelanto option:selected").each(function () {
			adelanto = $(this).val();

			$("#div_tabla_adelantos").html("");

			if (adelanto == 1) {
				$("#modalAdelantos").modal({ backdrop: "static", keyboard: false });
				$("#form_adelantos")[0].reset();
			} else if (adelanto != 0) {
				setTimeout(function () {
					$("#contrato_impuesto_a_la_renta").select2("open");
				}, 100);
			}
		});
	});
	// FIN CHANGE CONDICIONES ECONOMICAS Y COMERCIALES

	// INICIO CHANGE PERIODO GRACIA

	$("#contrato_periodo_gracia_id").change(function () {
		$("#contrato_periodo_gracia_id option:selected").each(function () {
			periodo_gracia = $(this).val();
			if (periodo_gracia == 1) {
				$("#div_periodo_gracia_numero").show();
				setTimeout(function () {
					$("#contrato_periodo_gracia_numero").focus();
				}, 100);
			} else {
				$("#div_periodo_gracia_numero").hide();
			}
		});
	});

	// FIN CHANGE PERIODO GRACIA

	// INICIO CHANGE VIGENCIA
	$("#input_text_contrato_inicio_fecha").change(function () {
		calcularFechaFinVigencia();
	});

	$("#input_text_contrato_inicio_fecha").change(function () {
		fecha_inicio = $("#input_text_contrato_inicio_fecha").val();
		fecha_fin = $("#input_text_contrato_fin_fecha").val();
		if (fecha_fin != "") {
			var num_meses = sec_contrato_nuevo_calcular_meses(fecha_inicio, fecha_fin);
			$("#contrato_vigencia_del_contrato_en_meses").val(num_meses);
			sec_contrato_nuevo_calcular_anios_y_meses(num_meses);
		}
	});

	$("#input_text_contrato_fin_fecha").change(function () {
		fecha_inicio = $("#input_text_contrato_inicio_fecha").val();
		if (fecha_inicio != "") {
			fecha_fin = $("#input_text_contrato_fin_fecha").val();
			var num_meses = sec_contrato_nuevo_calcular_meses(fecha_inicio, fecha_fin);
			$("#contrato_vigencia_del_contrato_en_meses").val(num_meses);
			sec_contrato_nuevo_calcular_anios_y_meses(num_meses);
		}
	});
	// FIN CHANGE VIGENCIA

	// INICIO CHANGE INCREMENTOS
	$("#contrato_incrementos_monto_o_porcentaje").on({
		focus: function (event) {
			$(event.target).select();
		},
		change: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		},
	});

	$("#contrato_tipo_incremento_id").change(function () {
		$("#contrato_tipo_incremento_id option:selected").each(function () {
			tipo_incremento_id = $(this).val();
			if (tipo_incremento_id == 1) {
				sec_contrato_nuevo_modal_agregar_incremento();
			} else if (tipo_incremento_id != 0) {
				sec_contrato_nuevo_buscar_beneficiario_modal();
			}
		});
	});

	$("#contrato_incrementos_en").change(function () {
		$("#contrato_incrementos_en option:selected").each(function () {
			incrementos_en = $(this).val();
			if (incrementos_en != 0) {
				setTimeout(function () {
					$("#contrato_incrementos_continuidad").select2("open");
				}, 200);
			}
		});
	});

	$("#contrato_incrementos_continuidad").change(function () {
		$("#contrato_incrementos_continuidad option:selected").each(function () {
			continuidad_id = $(this).val();

			if (continuidad_id == 3) {
				$("#titulo_incremento_a_partir").html("");
				$("#titulo_incremento_a_partir").hide();
				$("#td_contrato_incrementos_a_partir_de_año").hide();
			} else {
				if (continuidad_id == 1) {
					$("#titulo_incremento_a_partir").html("El");
				} else if (continuidad_id == 2) {
					$("#titulo_incremento_a_partir").html("A partir del");
				}

				$("#titulo_incremento_a_partir").show();
				$("#td_contrato_incrementos_a_partir_de_año").show();

				setTimeout(function () {
					$("#contrato_incrementos_a_partir_de_año").select2("open");
				}, 200);
			}
		});
	});
	// FIN CHANGE INCREMENTOS

	// INICIO CHANGE BENEFICIARIO
	$("#modal_beneficiario_tipo_persona").change(function () {
		$("#modal_beneficiario_tipo_persona option:selected").each(function () {
			tipo_persona = $(this).val();
			if (tipo_persona == 1 || tipo_persona == 2) {
				setTimeout(function () {
					$("#modal_beneficiario_nombre").focus();
				}, 100);
			} else if (tipo_persona == 0) {
				$("#modal_beneficiario_tipo_persona").focus();
			}
		});
	});

	$("#modal_beneficiario_tipo_docu").change(function () {
		$("#modal_beneficiario_tipo_docu option:selected").each(function () {
			tipo_docu = $(this).val();
			if (tipo_docu == 1 || tipo_docu == 2 || tipo_docu == 3) {
				if (tipo_docu == 1) {
					$("#modal_beneficiario_num_docu").mask("00000000");
				} else if (tipo_docu == 2) {
					$("#modal_beneficiario_num_docu").mask("00000000000");
				}

				$("#modal_beneficiario_num_docu").focus();
			} else if (tipo_docu == 0) {
				$("#modal_beneficiario_tipo_docu").focus();
			}
		});
	});

	$("#modal_beneficiario_id_forma_pago").change(function () {
		$("#modal_beneficiario_id_forma_pago option:selected").each(function () {
			id_forma_pago = $(this).val();
			if (id_forma_pago == 1 || id_forma_pago == 2) {
				$("#div_modal_beneficiario_nombre_banco").show();
				$("#div_modal_beneficiario_numero_cuenta_bancaria").show();
				$("#div_modal_beneficiario_numero_CCI").show();
				setTimeout(function () {
					$("#modal_beneficiario_id_banco").select2("open");
				}, 200);
			} else if (id_forma_pago == 3) {
				$("#div_modal_beneficiario_nombre_banco").hide();
				$("#div_modal_beneficiario_numero_cuenta_bancaria").hide();
				$("#div_modal_beneficiario_numero_CCI").hide();
				setTimeout(function () {
					$("#modal_beneficiario_tipo_monto").select2("open");
				}, 200);
			}
		});
	});

	$("#modal_beneficiario_id_banco").change(function () {
		$("#modal_beneficiario_id_banco option:selected").each(function () {
			id_banco = $(this).val();
			if (id_banco == 0) {
				setTimeout(function () {
					$("#modal_beneficiario_id_banco").select2("open");
				}, 500);
			} else {
				setTimeout(function () {
					$("#modal_beneficiario_num_cuenta_bancaria").focus();
				}, 200);
			}
		});
	});

	$("#modal_beneficiario_tipo_monto").change(function () {
		$("#modal_beneficiario_tipo_monto option:selected").each(function () {
			tipo_monto = $(this).val();
			if (tipo_monto == 1 || tipo_monto == 2) {
				$("#div_modal_beneficiario_monto").show();
				if (tipo_monto == 1) {
					$("#label_beneficiario_tipo_pago").text("Monto (Según la moneda del contrato)");
				} else if (tipo_monto == 2) {
					$("#label_beneficiario_tipo_pago").text("Porcentaje (%)");
				}
				setTimeout(function () {
					$("#modal_beneficiario_monto").focus();
				}, 200);
			} else if (tipo_monto == 3) {
				$("#div_modal_beneficiario_monto").hide();
			}
		});
	});
	// FIN CHANGE BENEFICIARIO

	// INICIO CLICK PROPIETARIO
	$("#btnModalNuevoPropietario").click(function () {
		var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud").val();
		if (tipo_solicitud == "adenda") {
			sec_contrato_nuevo_nuevo_propietario_modal("adenda");
		} else {
			sec_contrato_nuevo_nuevo_propietario_modal("arrendamiento");
		}
	});

	$("#btnModalNuevoPropietario_ca").click(function () {
		var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud_ca").val();
		if (tipo_solicitud == "adenda") {
			sec_contrato_nuevo_nuevo_propietario_modal_ca("adenda");
		} else {
			sec_contrato_nuevo_nuevo_propietario_modal_ca("agente");
		}
	});

	$("#btn_agregar_propietario").click(function () {
		sec_contrato_nuevo_guardar_propietario("guardar_propietario");
	});

	$("#btn_agregar_propietario_a_la_adenda").click(function () {
		sec_contrato_nuevo_guardar_propietario("guardar_propietario_adenda");
	});

	// $("#btn_agregar_propietario_a_la_adenda_ca").click(function () {
	// 	sec_contrato_nuevo_guardar_propietario_ca("guardar_propietario_agente");
	// });

	$("#btn_guardar_cambios_propietario").click(function () {
		sec_contrato_nuevo_guardar_cambios_propietario();
	});
	// $("#btn_guardar_cambios_propietario_ca").click(function () {
	// 	sec_contrato_nuevo_guardar_cambios_propietario_ca();
	// });
	// FIN CLICK PROPIETARIO

	// INICIO CLICK INMUEBLE
	$("#btnModalAgregarInmueble").click(function () {
		$("#modalAgregarInmueble").modal({ backdrop: "static", keyboard: false });
		setTimeout(function () {
			$("#modal_inmueble_id_departamento").select2("open");
		}, 500);
	});

	$("#btnAgregarInmueble").click(function () {
		sec_contrato_nuevo_guardar_inmueble();
	});
	// INICIO CLICK INMUEBLE

	// INICIO CLICK INCREMENTO
	$("#btnAgregarSoloIncremento").click(function () {
		sec_contrato_nuevo_guardar_incremento("modalAgregar");
	});

	$("#btnAgregarIncrementoPlus").click(function () {
		sec_contrato_nuevo_guardar_incremento("");
	});

	$("#btn_guardar_cambios_incremento").click(function () {
		sec_contrato_nuevo_guardar_cambios_incremento();
	});
	// FIN CLICK INCREMENTO

	// INICIO CLICK BENEFICIARIO
	$("#btn_agregar_beneficiario").click(function () {
		sec_contrato_nuevo_guardar_beneficiario("guardar_beneficiario");
	});

	$("#btn_guardar_cambios_beneficiario").click(function () {
		sec_contrato_nuevo_guardar_cambios_beneficiario();
	});

	$("#btnModalRegistrarBeneficiario").click(function () {
		$("#modalCandidatosBeneficiario").modal("hide");
		// $('#modalNuevoBeneficiario').modal('show');
		$("#modalNuevoBeneficiario").modal({ backdrop: "static", keyboard: false });
		sec_contrato_nuevo_resetear_formulario_nuevo_beneficiario("new");
		setTimeout(function () {
			$("#modal_beneficiario_nombre").focus();
		}, 200);
	});

	$("#btn_guardar_beneficiario_adenda").click(function () {
		sec_contrato_nuevo_guardar_beneficiario("guardar_beneficiario_adenda");
	});
	// FIN CLICK BENEFICIARIO

	$("#boton_guardar_contratos").click(function () {
		$("#boton_guardar_contratos").hide();
		if (!sec_contrato_nuevo_guardar_contrato()) {
			$("#boton_guardar_contratos").show();
		}
	});

	$("#boton_guardar_contrato_agente").click(function () {
		$("#boton_guardar_contrato_agente").hide();
		if (!sec_contrato_nuevo_guardar_contrato_agente()) {
			console.log("guardar");
			$("#boton_guardar_contrato_agente").show();
		}
	});

	$("#modal_propietario_nombre_o_numdocu").keypress(function (e) {
		if (e.which == 13) {
			event.preventDefault();
			$("#btnBuscarPropietario").click();
		}
	});

	$("#contrato_vigencia_del_contrato_en_meses").keyup(function (e) {
		var meses = $("#contrato_vigencia_del_contrato_en_meses").val();
		sec_contrato_nuevo_calcular_anios_y_meses(meses);
		calcularFechaFinVigencia();
	});

	$(".select2").select2({ width: "100%" });

	// INICIO OTROS EVENTOS CONTRATO DE ARRENDAMIENTO
	$(".formato_moneda_clase").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		},
	});

	$("#modal_inmueble_monto_o_porcentaje_agua").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			var tipo_compromiso_pago_agua = $("#modal_inmueble_tipo_compromiso_pago_agua").val();
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				if (parseInt(tipo_compromiso_pago_agua) != 1) {
					$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
					$(event.target).val(function (index, value) {
						return value
							.replace(/\D/g, "")
							.replace(/([0-9])([0-9]{2})$/, "$1.$2")
							.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					});
				}
			} else {
				if (parseInt(tipo_compromiso_pago_agua) != 1) {
					$(event.target).val("0.00");
				} else {
					$(event.target).val("0");
				}
			}
		},
	});

	$("#modal_inmueble_monto_o_porcentaje_luz").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			var tipo_compromiso_pago_luz = $("#modal_inmueble_tipo_compromiso_pago_luz").val();
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				if (parseInt(tipo_compromiso_pago_luz) != 1) {
					$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
					$(event.target).val(function (index, value) {
						return value
							.replace(/\D/g, "")
							.replace(/([0-9])([0-9]{2})$/, "$1.$2")
							.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					});
				}
			} else {
				if (parseInt(tipo_compromiso_pago_luz) != 1) {
					$(event.target).val("0.00");
				} else {
					$(event.target).val("0");
				}
			}
		},
	});

	$("#contrato_monto_renta").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
			sec_contrato_nuevo_calcular_monto_a_pagar();
		},
	});

	$("#contrato_monto_garantia").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		},
	});

	$("#modal_beneficiario_monto").on({
		focus: function (event) {
			$(event.target).select();
		},
		blur: function (event) {
			if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
				$(event.target).val(function (index, value) {
					return value
						.replace(/\D/g, "")
						.replace(/([0-9])([0-9]{2})$/, "$1.$2")
						.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		},
	});
	// FIN OTROS EVENTOS CONTRATO DE ARRENDAMIENTO
	cargarBancos();



	// NIF16
	$("#contrato_tipo_inflacion_id").change(function () {
		$("#contrato_tipo_inflacion_id option:selected").each(function () {
			tope_inflacion_id = $(this).val();
			
			if (tope_inflacion_id == 1) {
				sec_contrato_nuevo_modal_agregar_inflacion('new');
			}else if( tope_inflacion_id == 2){
				array_inflacion_contrato = [];
				$('#divTablaInflacion').html('');
			}
			
		});
	});

	$("#modal_if_tipo_periodicidad_id").change(function () {
		$("#modal_if_tipo_periodicidad_id option:selected").each(function () {
			tipo_periosidad = $(this).val();
			if (tipo_periosidad == 1) {
				$('.block-periosidad').show();
			}else if( tipo_periosidad == 2){
				$('.block-periosidad').hide();
			}
		});
	});


	$("#contrato_tipo_cuota_extraordinaria_id").change(function () {
		$("#contrato_tipo_cuota_extraordinaria_id option:selected").each(function () {
			tipo_cuota_extraordinaria_id = $(this).val();
			if (tipo_cuota_extraordinaria_id == 1) {
				sec_contrato_nuevo_modal_agregar_cuota_extraordinaria('new');
			}else if( tipo_cuota_extraordinaria_id == 2){
				array_cuota_extraordinaria_contrato = [];
				$('#divTablaCuotaExtraordinaria').html('');
			}
		});
	});

	
}

//Seleccionar tipo de contrato
function sec_cont_nuevo_listar_tipo_contrato() {
	const valores = window.location.search;
	const urlParams = new URLSearchParams(valores);
	var opt_tipo_contrato = urlParams.get('option');
	var select = $("[name='tipo_contrato_id']");
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data:{accion:'obtener_tipo_contrato'} ,//+data,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (datos) {//  alert(datat)
			var respuesta = JSON.parse(datos);
			$(select).find('option').remove().end();
			$(select).append('<option value="0">- Seleccione -</option>');
			$(respuesta.result).each(function(i,e){
				opcion = $("<option "+ (opt_tipo_contrato == e.id ? "selected":"")  +" value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);	
			})

			$('#tipo_contrato_id').trigger('change.select2');
			$('#tipo_contrato_id').trigger('change');
		},
		error: function () {
		}
	});
}

// INICIO FUNCIONES UX
function calcularFechaFinVigencia() {
	var meses = parseInt($("#contrato_vigencia_del_contrato_en_meses").val());
	var inicio_fecha = $("#input_text_contrato_inicio_fecha").val();
	console.log(meses);
	if (meses != 0 && meses != "" && inicio_fecha != "") {
		var date2 = $("#input_text_contrato_inicio_fecha").datepicker("getDate", "+1d");
		date2.setMonth(date2.getMonth() + meses);
		date2.setDate(date2.getDate() - 1);
		$("#input_text_contrato_fin_fecha").datepicker("setDate", date2);
	}
}

function sec_contrato_nuevo_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: { accion: accion }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			//  alert(datat)
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			$(select).append('<option value="0">- Seleccione -</option>');
			$(respuesta.result).each(function (i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			});
		},
		error: function () {},
	});
}

function filterFloat_2(evt, input) {
	// Backspace = 8, Enter = 13, '0′ = 48, '9′ = 57, '.' = 46, '-' = 43
	var key = window.Event ? evt.which : evt.keyCode;
	var chark = String.fromCharCode(key);
	var tempValue = input.value + chark;
	if (key >= 48 && key <= 57) {
		if (filter_2(tempValue) === false) {
			return false;
		} else {
			return true;
		}
	} else {
		if (key == 8 || key == 13 || key == 0) {
			return true;
		} else if (key == 46) {
			if (filter_2(tempValue) === false) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}
// FIN FUNCIONES UX

// INICIO FUNCIONES PROPIETARIO
function sec_contrato_nuevo_buscar_propietario() {
	var array_propietarios = [];
	var nombre_o_numdocu = $.trim($("#modal_propietario_nombre_o_numdocu").val());
	var tipo_busqueda = parseInt($.trim($("#modal_propietario_tipo_busqueda").val()));
	var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud").val();

	if (nombre_o_numdocu.length < 3) {
		var busqueda_por = "";
		if (tipo_busqueda == 1) {
			busqueda_por = "Nombre del Propietario";
		} else if (tipo_busqueda == 2) {
			busqueda_por = "Número de Documento de Identidad";
		}
		alertify.error("El " + busqueda_por + " debe de tener más de dos dígitos", 5);
		$("#modal_propietario_nombre_o_numdocu").focus();
		return;
	}

	var data = {
		accion: "obtener_propietario",
		nombre_o_numdocu: nombre_o_numdocu,
		tipo_busqueda: tipo_busqueda,
		tipo_solicitud: tipo_solicitud,
	};

	auditoria_send({ proceso: "obtener_propietario", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$("#tlbPropietariosxBusqueda").html("");
				$("#divNoSeEncontroPropietario").show();
				$("#divRegistrarNuevoPropietario").show();
				var msg = "";
				if (tipo_busqueda == "1") {
					msg = "nombre";
				} else {
					msg = "número de documento";
				}
				$("#valoresDeBusqueda").text(msg + " " + nombre_o_numdocu);
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#tlbPropietariosxBusqueda").html(respuesta.result);
				$("#divNoSeEncontroPropietario").hide();
				$("#divRegistrarNuevoPropietario").show();

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_buscar_propietario_ca() {
	var array_propietarios = [];
	var nombre_o_numdocu = $.trim($("#modal_propietario_nombre_o_numdocu_ca").val());
	var tipo_busqueda = parseInt($.trim($("#modal_propietario_tipo_busqueda_ca").val()));
	var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud_ca").val();

	if (nombre_o_numdocu.length < 3) {
		var busqueda_por = "";
		if (tipo_busqueda == 1) {
			busqueda_por = "Nombre del Propietario";
		} else if (tipo_busqueda == 2) {
			busqueda_por = "Número de Documento de Identidad";
		}
		alertify.error("El " + busqueda_por + " debe de tener más de dos dígitos", 5);
		$("#modal_propietario_nombre_o_numdocu_ca").focus();
		return;
	}

	var data = {
		accion: "obtener_propietario_ca",
		nombre_o_numdocu: nombre_o_numdocu,
		tipo_busqueda: tipo_busqueda,
		tipo_solicitud: tipo_solicitud,
	};

	auditoria_send({ proceso: "obtener_propietario_ca", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$("#tlbPropietariosxBusqueda_ca").html("");
				$("#divNoSeEncontroPropietario_ca").show();
				$("#divRegistrarNuevoPropietario_ca").show();
				var msg = "";
				if (tipo_busqueda == "1") {
					msg = "nombre";
				} else {
					msg = "número de documento";
				}
				$("#valoresDeBusqueda_ca").text(msg + " " + nombre_o_numdocu);
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#tlbPropietariosxBusqueda_ca").html(respuesta.result);
				$("#divNoSeEncontroPropietario_ca").hide();
				$("#divRegistrarNuevoPropietario_ca").show();

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_asignar_propietario_al_contrato(idpersona, modal) {
	if (array_propietarios_contrato.includes(idpersona) === false) {
		array_propietarios_contrato.push(idpersona);
	}
	console.log(array_propietarios_contrato);
	if (modal == "modalBuscar") {
		$("#modalBuscarPropietario").modal("hide");
	} else if (modal == "modalNuevo") {
		$("#modalNuevoPropietario").modal("hide");
	}
	sec_contrato_nuevo_actualizar_tabla_propietarios();
}

function sec_contrato_nuevo_asignar_propietario_al_contrato_ca(idpersona, modal) {
	if (array_propietarios_contrato.includes(idpersona) === false) {
		array_propietarios_contrato.push(idpersona);
	}
	console.log(array_propietarios_contrato);
	if (modal == "modalBuscar") {
		$("#modalBuscarPropietario_ca").modal("hide");
	} else if (modal == "modalNuevo") {
		$("#modalNuevoPropietario_ca").modal("hide");
	}
	sec_contrato_nuevo_actualizar_tabla_propietarios_ca();
}

function sec_contrato_nuevo_actualizar_tabla_propietarios() {
	var data = {
		accion: "obtener_propietario",
		nombre_o_numdocu: JSON.stringify(array_propietarios_contrato),
		tipo_busqueda: "3",
		tipo_solicitud: "",
	};

	auditoria_send({ proceso: "obtener_propietario", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#divTablaPropietarios").html(respuesta.result);
				$("#divNoSeEncontroPropietario").hide();
				$("#divRegistrarNuevoPropietario").show();
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_actualizar_tabla_propietarios_ca() {
	var data = {
		accion: "obtener_propietario_ca",
		nombre_o_numdocu: JSON.stringify(array_propietarios_contrato),
		tipo_busqueda: "3",
		tipo_solicitud: "",
	};

	auditoria_send({ proceso: "obtener_propietario_ca", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#divTablaPropietarios_ca").html(respuesta.result);
				$("#divNoSeEncontroPropietario_ca").hide();
				$("#divRegistrarNuevoPropietario_ca").show();
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_guardar_propietario(proceso) {
	var data = sec_contrato_nuevo_validar_campos_formulario_propietario(proceso);

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: proceso, data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: proceso, data: respuesta });
			if (parseInt(respuesta.http_code) == 400) {
				alertify.error(respuesta.status, 5);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				if (proceso == "guardar_propietario_adenda") {
					sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda("propietario", respuesta.result, "modalNuevoPropietario");
				} else {
					sec_contrato_nuevo_asignar_propietario_al_contrato(respuesta.result, "modalNuevo");
				}
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_guardar_propietario_ca(proceso) {
	var data = sec_contrato_nuevo_validar_campos_formulario_propietario_ca(proceso);

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: proceso, data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: proceso, data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// $('#modal_recargaweb').modal('hide');
				// swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_asignar_propietario_al_contrato_ca(respuesta.result, "modalNuevo");

				// if (proceso == 'guardar_propietario_agente') {
				// 	sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda_ca('propietario', respuesta.result, 'modalNuevoPropietario_ca');
				// } else {
				// 	sec_contrato_nuevo_asignar_propietario_al_contrato_ca(respuesta.result,'modalNuevo');
				// }
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_eliminar_propietario(id_propietario) {
	console.log(array_propietarios_contrato);
	const index = array_propietarios_contrato.indexOf(id_propietario);
	if (index > -1) {
		array_propietarios_contrato.splice(index, 1);
	}
	console.log(array_propietarios_contrato);
	sec_contrato_nuevo_actualizar_tabla_propietarios();
}

function sec_contrato_nuevo_eliminar_propietario_ca(id_propietario) {
	console.log(array_propietarios_contrato);
	const index = array_propietarios_contrato.indexOf(id_propietario);
	if (index > -1) {
		array_propietarios_contrato.splice(index, 1);
	}
	console.log(array_propietarios_contrato);
	sec_contrato_nuevo_actualizar_tabla_propietarios_ca();
}

function sec_contrato_nuevo_es_email_valido(email) {
	var regex =
		/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return regex.test(email);
}
// FIN FUNCIONES PROPIETARIO

// INICIO FUNCIONES INMUEBLE
function sec_contrato_nuevo_guardar_inmueble() {
	var id_departamento = $("#modal_inmueble_id_departamento").val();
	var id_provincia = $("#modal_inmueble_id_provincia").val();
	var id_distrito = $("#modal_inmueble_id_distrito").val();
	var ubicacion = $("#modal_inmueble_ubicacion").val().trim();
	var area_arrendada = $("#modal_inmueble_area_arrendada").val().trim();
	var num_partida_registral = $("#modal_inmueble_num_partida_registral").val();
	var oficina_registral = $("#modal_inmueble_oficina_registral").val();
	var num_suministro_agua = $("#modal_inmueble_num_suministro_agua").val();
	var tipo_compromiso_pago_agua = $("#modal_inmueble_tipo_compromiso_pago_agua").val();
	var monto_o_porcentaje_agua = $("#modal_inmueble_monto_o_porcentaje_agua").val();
	var num_suministro_luz = $("#modal_inmueble_num_suministro_luz").val();
	var tipo_compromiso_pago_luz = $("#modal_inmueble_tipo_compromiso_pago_luz").val();
	var monto_o_porcentaje_luz = $("#modal_inmueble_monto_o_porcentaje_luz").val();
	var tipo_compromiso_pago_arbitrios = $("#modal_inmueble_tipo_compromiso_pago_arbitrios").val();
	var porcentaje_pago_arbitrios = $("#modal_inmueble_porcentaje_pago_arbitrios").val().trim();

	if (parseInt(id_departamento) == 0) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Seleccione el departamento");
		$("#modal_inmueble_id_departamento").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_departamento").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(id_provincia) == 0) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Seleccione la provincia");
		$("#modal_inmueble_id_provincia").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_provincia").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(id_distrito) == 0) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Seleccione el distrito");
		$("#modal_inmueble_id_distrito").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_distrito").select2("open");
		}, 200);
		return false;
	}

	if (ubicacion.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese la ubicación");
		$("#modal_inmueble_ubicacion").focus();
		return false;
	}

	if (ubicacion.length < 6) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("La ubicación del inmueble debe de ser mayor de 5 letras");
		$("#modal_inmueble_ubicacion").focus();
		return false;
	}

	if (area_arrendada.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el area arrendada");
		$("#modal_inmueble_area_arrendada").focus();
		return false;
	}

	if (num_partida_registral.length < 5) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el número de partida registral");
		$("#modal_inmueble_num_partida_registral").focus();
		return false;
	}

	if (oficina_registral.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el nombre de la oficina registral");
		$("#modal_inmueble_oficina_registral").focus();
		return false;
	}

	if (num_suministro_agua.length < 0) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el número de suministro del agua");
		$("#modal_inmueble_num_suministro_agua").focus();
		return false;
	}

	if (num_suministro_agua.length < 7) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("El número de suministro de agua debe ser mayor a 6 dígitos");
		$("#modal_inmueble_num_suministro_agua").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 0) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Seleccione el tipo de compromiso de pago del servicio del agua");
		$("#modal_inmueble_tipo_compromiso_pago_agua").focus();
		setTimeout(function () {
			$("#modal_inmueble_tipo_compromiso_pago_agua").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 1 && monto_o_porcentaje_agua.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el porcentaje del pago del servicio de agua");
		$("#modal_inmueble_monto_o_porcentaje_agua").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 2 && monto_o_porcentaje_agua.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el monto fijo del pago del servicio de agua");
		$("#modal_inmueble_monto_o_porcentaje_agua").focus();
		return false;
	}

	if (num_suministro_luz.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el número de suministro del servicio de luz");
		$("#modal_inmueble_num_suministro_luz").focus();
		return false;
	}

	if (num_suministro_luz.length < 7) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("El número de suministro de luz debe ser mayor a 6 dígitos");
		$("#modal_inmueble_num_suministro_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 0) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Seleccione el tipo de compromiso de pago del servicio de luz");
		$("#modal_inmueble_tipo_compromiso_pago_luz").focus();
		setTimeout(function () {
			$("#modal_inmueble_tipo_compromiso_pago_luz").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 1 && monto_o_porcentaje_luz.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el porcentaje del servicio de luz");
		$("#modal_inmueble_monto_o_porcentaje_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 2 && monto_o_porcentaje_luz.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el monto del servicio de luz");
		$("#modal_inmueble_monto_o_porcentaje_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_arbitrios) == 0) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Seleccione el tipo de compromiso de pago de arbitrios");
		$("#modal_inmueble_tipo_compromiso_pago_arbitrios").focus();
		setTimeout(function () {
			$("#modal_inmueble_tipo_compromiso_pago_arbitrios").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(tipo_compromiso_pago_arbitrios) == 1 && porcentaje_pago_arbitrios.length < 1) {
		$("#div_modal_inmueble_mensaje").show();
		$("#modal_inmueble_mensaje").html("Ingrese el porcentaje de pago de arbitrios");
		$("#modal_inmueble_porcentaje_pago_arbitrios").focus();
		return false;
	}

	$("#div_modal_inmueble_mensaje").hide();

	var data = {
		accion: "guardar_inmueble",
		id_departamento: id_departamento,
		id_provincia: id_provincia,
		id_distrito: id_distrito,
		ubicacion: ubicacion,
		area_arrendada: area_arrendada,
		num_partida_registral: num_partida_registral,
		oficina_registral: oficina_registral,
		num_suministro_agua: num_suministro_agua,
		tipo_compromiso_pago_agua: tipo_compromiso_pago_agua,
		monto_o_porcentaje_agua: monto_o_porcentaje_agua,
		num_suministro_luz: num_suministro_luz,
		tipo_compromiso_pago_luz: tipo_compromiso_pago_luz,
		monto_o_porcentaje_luz: monto_o_porcentaje_luz,
		tipo_compromiso_pago_arbitrios: tipo_compromiso_pago_arbitrios,
		porcentaje_pago_arbitrios: porcentaje_pago_arbitrios,
	};
	auditoria_send({ proceso: "guardar_inmueble", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_inmueble", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_asignar_inmueble_al_contrato(respuesta.result, "modalAgregar");
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_asignar_inmueble_al_contrato(id_inmueble, modal) {
	if (array_inmuebles_contrato.includes(id_inmueble) === false) {
		array_inmuebles_contrato.push(id_inmueble);
	}
	console.log(array_inmuebles_contrato);
	if (modal == "modalAgregar") {
		$("#modalAgregarInmueble").modal("hide");
	}
	sec_contrato_nuevo_actualizar_tabla_inmuebles();
}

function sec_contrato_nuevo_actualizar_tabla_inmuebles() {
	if (array_inmuebles_contrato.length > 0) {
		var data = {
			accion: "obtener_inmuebles",
			id_inmuebles: JSON.stringify(array_inmuebles_contrato),
		};

		var array_inmuebles = [];

		auditoria_send({ proceso: "obtener_inmuebles", data: data });
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
			data: data,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (resp) {
				//  alert(datat)
				var respuesta = JSON.parse(resp);
				console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					return false;
				}

				if (parseInt(respuesta.http_code) == 200) {
					array_inmuebles.push(respuesta.result);
					console.log("Cantidad de Registro: " + array_inmuebles_contrato[0].length);
					var table = '<table class="table table-bordered table-striped no-mb" style="font-size:10px;margin-top: 10px;">';
					table += "<thead><tr><th>#</th>";
					table += "<th>Ubicación</th>";
					table += "<th>Área arrendada</th>";
					table += "<th>No. Partida Registral</th>";
					table += "<th>Oficina Registral</th>";
					table += "<th>N° Suministro - Agua</th>";
					table += "<th>N° Suministro - Luz</th>";
					table += "<th>Opciones</th></tr></thead><tbody>";

					for (var i = 0; i < array_inmuebles[0].length; i++) {
						var num = i + 1;
						table += "<tr><td>" + num + "</td>";
						table += "<td>" + array_inmuebles[0][i].ubicacion + "</td>";
						table += "<td>" + array_inmuebles[0][i].area_arrendada + "</td>";
						table += "<td>" + array_inmuebles[0][i].num_partida_registral + "</td>";
						table += "<td>" + array_inmuebles[0][i].oficina_registral + "</td>";
						table += "<td>" + array_inmuebles[0][i].num_suministro_agua + "</td>";
						table += "<td>" + array_inmuebles[0][i].num_suministro_luz + "</td>";
						table +=
							'<td><a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar"><i class="fa fa-edit"></i></a>';
						table +=
							'<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_inmueble(' +
							array_inmuebles[0][i].id +
							')"><i class="fa fa-trash"></i></a></td></tr>';
					}
					table += "</tbody></table>";
					$("#divTablaInmuebles").html(table);

					return false;
				}
			},
			error: function () {},
		});
	} else {
		$("#divTablaInmuebles").html("");
	}
}

function sec_contrato_nuevo_eliminar_inmueble(id_inmueble) {
	console.log(array_inmuebles_contrato);
	const index = array_inmuebles_contrato.indexOf(id_inmueble);
	if (index > -1) {
		array_inmuebles_contrato.splice(index, 1);
	}
	console.log(array_inmuebles_contrato);
	sec_contrato_nuevo_actualizar_tabla_inmuebles();
}
// FIN FUNCIONES INMUEBLE

// INICIO FUNCIONES CONDICIONES ECONOMICAS Y COMERCIALES
function sec_contrato_nuevo_guardar_adelantos(name_modal_close) {
	var array_mes_adelanto = [];
	$(".contrato_adelanto").each(function () {
		if ($(this).is(":checked")) {
			array_mes_adelanto.push($(this).val());
		}
	});

	if (array_mes_adelanto.length == 0) {
		alertify.error("Debe de seleccionar el mes del adelanto", 5);
		return;
	}

	var data = {
		accion: "guardar_adelantos",
		mes_adelanto: JSON.stringify(array_mes_adelanto),
	};

	auditoria_send({ proceso: "guardar_adelantos", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_adelantos", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				var temp_meses = "";
				array_adelantos_contrato = respuesta.result;
				array_adelantos_contrato.forEach(function (valor, indice, array) {
					console.log("En el índice " + indice + " hay este valor: " + valor);
					temp_meses = temp_meses + valor + ",";
				});

				$("#modalAdelantos").modal("hide");
				sec_contrato_nuevo_actualizar_tabla_adelantos();
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_actualizar_tabla_adelantos() {
	if (array_adelantos_contrato.length > 0) {
		var data = {
			accion: "obtener_adelantos",
			id_adelantos: JSON.stringify(array_adelantos_contrato),
		};

		auditoria_send({ proceso: "obtener_adelantos", data: data });
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
			data: data,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (resp) {
				//  alert(datat)
				var respuesta = JSON.parse(resp);
				console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					return false;
				}

				if (parseInt(respuesta.http_code) == 200) {
					$("#div_tabla_adelantos").html(respuesta.result);
					return false;
				}
			},
			error: function () {},
		});
	} else {
		$("#div_tabla_adelantos").html("");
	}
}

function sec_contrato_nuevo_editar_adelantos() {
	$("#modalAdelantos").modal("show");
	$("#form_adelantos")[0].reset();

	var data = {
		accion: "obtener_num_mes_de_adelantos",
		id_adelantos: JSON.stringify(array_adelantos_contrato),
	};

	var array_beneficiarios = [];

	auditoria_send({ proceso: "obtener_num_mes_de_adelantos", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				array_adelantos_tmṕ = respuesta.result;
				array_adelantos_tmṕ.forEach(function (valor, indice, array) {
					console.log("En el índice " + indice + " hay este valor: " + valor);
					if (valor > 0 && valor < 7) {
						checked_en_modal = valor - 1;
					} else if (valor == "x") {
						checked_en_modal = 6;
					} else if (valor == "y") {
						checked_en_modal = 7;
					} else if (valor == "z") {
						checked_en_modal = 8;
					}
					$(".contrato_adelanto")[checked_en_modal].checked = true;
				});

				return false;
			}
		},
		error: function () {},
	});
}
// FIN FUNCIONES CONDICIONES ECONOMICAS Y COMERCIALES

// INICIO FUNCIONES INCREMENTOS
function sec_contrato_nuevo_guardar_incremento(name_modal_close) {
	var data = sec_contrato_nuevo_validar_campos_formulario_incremento("guardar_incremento");

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: "guardar_incremento", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_incremento", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#frm_incremento")[0].reset();
				sec_contrato_nuevo_asignar_incremento_al_contrato(respuesta.result, name_modal_close);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_asignar_incremento_al_contrato(id_incremento, modal) {
	if (array_incrementos_contrato.includes(id_incremento) === false) {
		array_incrementos_contrato.push(id_incremento);
	}
	console.log(array_incrementos_contrato);
	sec_contrato_nuevo_actualizar_tabla_incremento();
	if (modal == "modalAgregar") {
		$("#modalAgregarIncrementos").modal("hide");
	} else {
		setTimeout(function () {
			$("#contrato_incrementos_monto_o_porcentaje").focus();
		}, 500);
	}
}

function sec_contrato_nuevo_actualizar_tabla_incremento() {
	if (array_incrementos_contrato.length > 0) {
		var data = {
			accion: "obtener_incrementos",
			id_incrementos: JSON.stringify(array_incrementos_contrato),
		};

		var array_incrementos = [];

		auditoria_send({ proceso: "obtener_incrementos", data: data });
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
			data: data,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (resp) {
				//  alert(datat)
				var respuesta = JSON.parse(resp);
				console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					return false;
				}

				if (parseInt(respuesta.http_code) == 200) {
					$("#divTablaIncrementos").html(respuesta.result);
					return false;
				}
			},
			error: function () {},
		});
	} else {
		$("#divTablaIncrementos").html("");
	}
}

function sec_contrato_nuevo_resetear_formulario_nuevo_incremento(evento) {
	$("#frm_incremento")[0].reset();

	if (evento == "new") {
		$("#modal_incremento_titulo").html("Registrar Incremento");
		$("#btnAgregarSoloIncremento").show();
		$("#btnAgregarIncrementoPlus").show();
		$("#div_btn_guardar_cambios_incremento").hide();
	} else if (evento == "edit") {
		$("#modal_incremento_titulo").html("Editar Incremento");
		$("#btnAgregarSoloIncremento").hide();
		$("#btnAgregarIncrementoPlus").hide();
		$("#div_btn_guardar_cambios_incremento").show();
	}
}

function sec_contrato_nuevo_editar_incremento(id_incremento) {
	$("#modalAgregarIncrementos").modal("show");

	sec_contrato_nuevo_resetear_formulario_nuevo_incremento("edit");

	var data = {
		accion: "obtener_incrementos",
		id_incremento: id_incremento,
	};

	var array_incrementos = [];

	auditoria_send({ proceso: "obtener_incrementos", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				array_incrementos.push(respuesta.result);
				$("#contrato_incrementos_id_incremento_para_cambios").val(array_incrementos[0][0].id);
				$("#contrato_incrementos_monto_o_porcentaje").val(array_incrementos[0][0].valor);
				$("#contrato_incrementos_en").val(array_incrementos[0][0].tipo_valor_id).trigger("change");
				$("#contrato_incrementos_continuidad").val(array_incrementos[0][0].tipo_continuidad_id).trigger("change");
				$("#contrato_incrementos_a_partir_de_año").val(array_incrementos[0][0].a_partir_del_año).trigger("change");

				setTimeout(function () {
					$("#contrato_incrementos_en").select2("close");
					$("#contrato_incrementos_continuidad").select2("close");
					$("#contrato_incrementos_a_partir_de_año").select2("close");
					$("#contrato_incrementos_monto_o_porcentaje").focus();
				}, 200);

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_validar_campos_formulario_incremento(accion) {
	var id_incremento_para_cambios = $("#contrato_incrementos_id_incremento_para_cambios").val();
	var incremento_monto_o_porcentaje = $("#contrato_incrementos_monto_o_porcentaje").val();
	var incrementos_en = $("#contrato_incrementos_en").val();
	var incrementos_continuidad = $("#contrato_incrementos_continuidad").val().trim();
	var incrementos_a_partir_de_año = $("#contrato_incrementos_a_partir_de_año").val();

	if (incremento_monto_o_porcentaje.length < 1) {
		alertify.error("Ingrese el valor", 5);
		$("#contrato_incrementos_monto_o_porcentaje").focus();
		return false;
	}

	if (parseInt(incrementos_en) == 0) {
		alertify.error("Seleccione el tipo de valor", 5);
		$("#contrato_incrementos_en").focus();
		return false;
	}

	if (parseInt(incrementos_en) == 2 && incremento_monto_o_porcentaje.length > 5) {
		alertify.error("El incremento no puede ser mayor al 100%", 5);
		$("#contrato_incrementos_en").focus();
		return false;
	}

	if (parseInt(incrementos_continuidad) == 0) {
		alertify.error("Seleccione el tipo de continuidad", 5);
		$("#contrato_incrementos_continuidad").focus();
		return false;
	}

	if (parseInt(incrementos_a_partir_de_año) == 0 && parseInt(incrementos_continuidad) != 3) {
		alertify.error("Seleccione el año del inicio del incremento", 5);
		$("#contrato_incrementos_a_partir_de_año").focus();
		return false;
	}

	var data = {
		accion: accion,
		id_incremento_para_cambios: id_incremento_para_cambios,
		incremento_monto_o_porcentaje: incremento_monto_o_porcentaje,
		incrementos_en: incrementos_en,
		incrementos_continuidad: incrementos_continuidad,
		incrementos_a_partir_de_año: incrementos_a_partir_de_año,
	};

	return data;
}

function sec_contrato_nuevo_guardar_cambios_incremento() {
	var data = sec_contrato_nuevo_validar_campos_formulario_incremento("guardar_cambios_incremento");

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: "guardar_cambios_incremento", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_cambios_incremento", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#modalAgregarIncrementos").modal("hide");
				sec_contrato_nuevo_actualizar_tabla_incremento();
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_eliminar_incremento(id_incremento) {
	console.log(array_incrementos_contrato);
	const index = array_incrementos_contrato.indexOf(id_incremento);
	if (index > -1) {
		array_incrementos_contrato.splice(index, 1);
	}
	console.log(array_incrementos_contrato);
	sec_contrato_nuevo_actualizar_tabla_incremento();
}
// FIN FUNCIONES INCREMENTOS

// INICIO FUNCIONES BENEFICIARIOS
function sec_contrato_nuevo_buscar_candidatos_beneficiarios() {
	if (array_propietarios_contrato.length > 0) {
		var data = {
			accion: "obtener_propietario",
			nombre_o_numdocu: JSON.stringify(array_propietarios_contrato),
			tipo_busqueda: "5",
			tipo_solicitud: "",
		};

		var array_propietarios = [];

		auditoria_send({ proceso: "obtener_propietario", data: data });
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
			data: data,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (resp) {
				//  alert(datat)
				var respuesta = JSON.parse(resp);
				console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					return false;
				}

				if (parseInt(respuesta.http_code) == 200) {
					/*
					array_propietarios.push();
					console.log('Cantidad de Registro: ' + array_propietarios[0].length);
					var table = '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
					table += '<thead><tr><th>#</th><th>Nombre / Razón Social</th><th>DNI / RUC</th><th>Opciones</th></tr></thead><tbody>';

					for (var i = 0; i < array_propietarios[0].length; i++) {
						var num = i + 1;
						table += '<tr><td>' + num + '</td>';
						table += '<td>' + array_propietarios[0][i].nombre + '</td>';
						table += '<td>' + array_propietarios[0][i].num_docu + '</td>';			    
						table += '<td><a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_asignar_pre_beneficiario_al_contrato(' + array_propietarios[0][i].id + ', \'modalBuscar\')"><i class="fa fa-plus"></i> Agregar al propietario como beneficiario</a></td></tr>';
						
					}
					table += '</tbody></table>';
					*/
					$("#divTablaCandidatosBeneficiarios").html(respuesta.result);
					return false;
				}
			},
			error: function () {},
		});
	} else {
		$("#divTablaCandidatosBeneficiarios").html("");
	}
}

function sec_contrato_nuevo_resetear_formulario_nuevo_beneficiario(evento) {
	$("#frmNuevoBeneficiario")[0].reset();
	$("#div_modal_beneficiario_nombre_banco").hide();
	$("#div_modal_beneficiario_numero_cuenta_bancaria").hide();
	$("#div_modal_beneficiario_numero_CCI").hide();
	$("#div_modal_beneficiario_monto").hide();
	$("#div_modal_beneficiario_mensaje").hide();

	if (evento == "new") {
		$("#modal_beneficiario_titulo").html("Registrar Beneficiario");
		$("#btn_agregar_beneficiario").show();
		$("#btn_guardar_cambios_beneficiario").hide();
		$("#btn_guardar_beneficiario_adenda").hide();
	} else if (evento == "edit") {
		$("#modal_beneficiario_titulo").html("Editar Beneficiario");
		$("#btn_agregar_beneficiario").hide();
		$("#btn_guardar_cambios_beneficiario").show();
		$("#btn_guardar_beneficiario_adenda").hide();
	} else if (evento == "adenda") {
		$("#modal_beneficiario_titulo").html("Adenda - Nuevo Beneficiario");
		$("#btn_agregar_beneficiario").hide();
		$("#btn_guardar_cambios_beneficiario").hide();
		$("#btn_guardar_beneficiario_adenda").show();
	}
}

function sec_contrato_nuevo_resetear_formulario_nuevo_propietario(evento) {
	$("#frm_nuevo_propietario")[0].reset();
	$("#div_modal_propietario_representante_legal").hide();
	$("#div_modal_propietario_num_partida_registral").hide();

	if (evento == "new") {
		$("#modal_nuevo_propietario_titulo").html("Registrar Propietario");
		$("#btn_agregar_propietario").show();
		$("#btn_guardar_cambios_propietario").hide();
		$("#btn_agregar_propietario_a_la_adenda").hide();

		$("#div_modal_propietario_contacto_nombre").hide();
		$("#div_modal_propietario_persona_contacto").show();
	} else if (evento == "edit") {
		$("#modal_nuevo_propietario_titulo").html("Editar Propietario");
		$("#btn_agregar_propietario").hide();
		$("#btn_guardar_cambios_propietario").show();
		$("#btn_agregar_propietario_a_la_adenda").hide();

		$("#div_modal_propietario_contacto_nombre").show();
		$("#div_modal_propietario_persona_contacto").hide();
	} else if (evento == "adenda") {
		$("#modal_nuevo_propietario_titulo").html("Adenda - Registrar Propietario");
		$("#btn_agregar_propietario").hide();
		$("#btn_guardar_cambios_propietario").hide();
		$("#btn_agregar_propietario_a_la_adenda").show();

		$("#div_modal_propietario_contacto_nombre").hide();
		$("#div_modal_propietario_persona_contacto").show();
	}
}

function sec_contrato_nuevo_resetear_formulario_nuevo_propietario_ca(evento) {
	$("#frm_nuevo_propietario_ca")[0].reset();
	$("#div_modal_propietario_representante_legal_ca").hide();
	$("#div_modal_propietario_num_partida_registral_ca").hide();

	if (evento == "new") {
		$("#modal_nuevo_propietario_titulo_ca").html("Registrar Propietario");
		$("#btn_agregar_propietario_ca").show();
		$("#btn_guardar_cambios_propietario_ca").hide();
		$("#btn_agregar_propietario_a_la_adenda_ca").hide();

		$("#div_modal_propietario_contacto_nombre_ca").hide();
		$("#div_modal_propietario_persona_contacto_ca").show();
	} else if (evento == "edit") {
		$("#modal_nuevo_propietario_titulo_ca").html("Editar Propietario");
		$("#btn_agregar_propietario_ca").hide();
		$("#btn_guardar_cambios_propietario_ca").show();
		$("#btn_agregar_propietario_a_la_adenda_ca").hide();

		$("#div_modal_propietario_contacto_nombre_ca").show();
		$("#div_modal_propietario_persona_contacto_ca").hide();
	} else if (evento == "adenda") {
		$("#modal_nuevo_propietario_titulo_ca").html("Adenda - Registrar Propietario");
		$("#btn_agregar_propietario_ca").hide();
		$("#btn_guardar_cambios_propietario_ca").hide();
		$("#btn_agregar_propietario_a_la_adenda_ca").show();

		$("#div_modal_propietario_contacto_nombre_ca").hide();
		$("#div_modal_propietario_persona_contacto_ca").show();
	} else if (evento == "agente") {
		$("#modal_nuevo_propietario_titulo_ca").html("Agente - Registrar Propietario");
		$("#btn_agregar_propietario_ca").hide();
		$("#btn_guardar_cambios_propietario_ca").hide();
		$("#btn_agregar_propietario_a_la_adenda_ca").show();

		$("#div_modal_propietario_contacto_nombre_ca").hide();
		$("#div_modal_propietario_persona_contacto_ca").show();
	}
}

function sec_contrato_nuevo_asignar_pre_beneficiario_al_contrato(id_persona) {
	$("#modalCandidatosBeneficiario").modal("hide");
	$("#modalNuevoBeneficiario").modal("show");

	sec_contrato_nuevo_resetear_formulario_nuevo_beneficiario("new");

	var array_propietarios = [];
	var data = {
		accion: "obtener_propietario",
		nombre_o_numdocu: id_persona,
		tipo_busqueda: "4",
		tipo_solicitud: "",
	};

	auditoria_send({ proceso: "obtener_propietario", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$("#tlbPropietariosxBusqueda").html("");
				$("#divNoSeEncontroPropietario").show();
				$("#divRegistrarNuevoPropietario").show();
				var msg = "";
				if (tipo_busqueda == "1") {
					msg = "nombre";
				} else {
					msg = "número de documento";
				}
				$("#valoresDeBusqueda").text(msg + " " + nombre_o_numdocu);
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				array_propietarios.push(respuesta.result);
				console.log("Cantidad de Registro: " + array_propietarios[0].length);

				for (var i = 0; i < array_propietarios[0].length; i++) {
					var num = i + 1;
					$("#modal_beneficiario_tipo_persona").val(array_propietarios[0][i].tipo_persona_id).trigger("change");
					$("#modal_beneficiario_nombre").val(array_propietarios[0][i].nombre);
					$("#modal_beneficiario_tipo_docu").val(2).trigger("change");
					$("#modal_beneficiario_num_docu").val(array_propietarios[0][i].num_ruc);
				}

				setTimeout(function () {
					$("#modal_beneficiario_tipo_persona").select2("close");
					$("#modal_beneficiario_tipo_docu").select2("close");
					$("#modal_beneficiario_id_forma_pago").focus();
					$("#modal_beneficiario_id_forma_pago").select2("open");
				}, 500);

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_validar_campos_formulario_beneficiario(accion) {
	var id_beneficiario_para_cambios = $("#modal_beneficiario_id_beneficiario_para_cambios").val();
	var tipo_persona = $("#modal_beneficiario_tipo_persona").val();
	var nombre = $("#modal_beneficiario_nombre").val().trim();
	var tipo_docu = $("#modal_beneficiario_tipo_docu").val();
	var num_docu = $("#modal_beneficiario_num_docu").val().trim();
	var id_forma_pago = $("#modal_beneficiario_id_forma_pago").val().trim();
	var id_banco = $("#modal_beneficiario_id_banco").val();
	var num_cuenta_bancaria = $("#modal_beneficiario_num_cuenta_bancaria").val();
	var num_cuenta_cci = $("#modal_beneficiario_num_cuenta_cci").val();
	var tipo_monto = $("#modal_beneficiario_tipo_monto").val();
	var monto = $("#modal_beneficiario_monto").val();

	if (parseInt(tipo_persona) == 0) {
		alertify.error("Seleccione el tipo de persona", 5);
		$("#modal_beneficiario_tipo_persona").focus();
		return false;
	}

	if (nombre.length < 6) {
		alertify.error("Ingrese el nombre completo del beneficiario", 5);
		$("#modal_beneficiario_nombre").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 0) {
		alertify.error("Seleccione el tipo de documento de identidad", 5);
		$("#modal_beneficiario_tipo_docu").focus();
		return false;
	}

	if (num_docu.length == 0) {
		alertify.error("Ingrese el Número de Documento de Identidad", 5);
		$("#modal_beneficiario_num_docu").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 1 && num_docu.length != 8) {
		alertify.error("El número de DNI posee 8 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_beneficiario_num_docu").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 2 && num_docu.length != 11) {
		alertify.error("El número de RUC posee 11 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_beneficiario_num_docu").focus();
		return false;
	}

	if (parseInt(id_forma_pago) == 0) {
		alertify.error("Seleccione el tipo de forma de pago", 5);
		$("#modal_beneficiario_id_forma_pago").focus();
		return false;
	}

	if (parseInt(id_banco) == 0 && parseInt(id_forma_pago) != 3) {
		alertify.error("Seleccione el banco", 5);
		$("#modal_beneficiario_id_banco").focus();
		return false;
	}

	if (num_cuenta_bancaria.length == 0 && parseInt(id_forma_pago) != 3) {
		alertify.error("Ingrese el número de cuenta bancaria", 5);
		$("#modal_beneficiario_num_cuenta_bancaria").focus();
		return false;
	}

	if (num_cuenta_bancaria.length < 5 && parseInt(id_forma_pago) != 3) {
		alertify.error("El número de cuenta bancaria debe ser mayor a 5 dígitos", 5);
		$("#modal_beneficiario_num_cuenta_bancaria").focus();
		return false;
	}

	if (num_cuenta_cci.length < 8 && parseInt(id_forma_pago) != 3) {
		alertify.error("El código de cuenta Interbancaria debe ser mayor a 8 dígitos", 5);
		$("#modal_beneficiario_num_cuenta_cci").focus();
		return false;
	}

	if (parseInt(tipo_monto) == 0) {
		alertify.error("Seleccione el tipo de monto a pagar", 5);
		$("#modal_beneficiario_tipo_monto").focus();
		return false;
	}

	if (parseInt(tipo_monto) != 3 && monto.length < 1) {
		alertify.error("Ingrese el monto a pagar", 5);
		$("#modal_beneficiario_monto").focus();
		return false;
	}

	if (accion == "guardar_beneficiario_adenda") {
		accion = "guardar_beneficiario";
	}

	/*
	if (!sec_contrato_nuevo_monto_beneficiario_valido()) {
		return false;
	}
	*/

	var data = {
		accion: accion,
		id_beneficiario_para_cambios: id_beneficiario_para_cambios,
		tipo_persona: tipo_persona,
		nombre: nombre,
		tipo_docu: tipo_docu,
		num_docu: num_docu,
		id_forma_pago: id_forma_pago,
		id_banco: id_banco,
		num_cuenta_bancaria: num_cuenta_bancaria,
		num_cuenta_cci: num_cuenta_cci,
		tipo_monto: tipo_monto,
		monto: monto,
	};

	return data;
}

function sec_contrato_nuevo_validar_campos_formulario_propietario(accion) {
	var id_propietario_para_cambios = $("#modal_propietaria_id_persona_para_cambios").val();
	var tipo_persona = $("#modal_propietario_tipo_persona").val();
	var nombre = $("#modal_propietario_nombre").val().trim();
	var tipo_docu = $("#modal_propietario_tipo_docu").val();
	var num_docu = $("#modal_propietario_num_docu").val().trim();
	var num_ruc = $("#modal_propietario_num_ruc").val().trim();
	var direccion = $("#modal_propietario_direccion").val().trim();
	var representante_legal = $("#modal_propietario_representante_legal").val().trim();
	var num_partida_registral = $("#modal_propietario_num_partida_registral").val();
	var tipo_persona_contacto = $("#modal_propietario_tipo_persona_contacto").val();
	var contacto_nombre = $("#modal_propietario_contacto_nombre").val().trim();
	var contacto_telefono = $("#modal_propietario_contacto_telefono").val();
	var contacto_email = $("#modal_propietario_contacto_email").val().trim();

	if (parseInt(tipo_persona) == 0) {
		alertify.error("Seleccione el tipo de persona", 5);
		$("#modal_propietario_tipo_persona").focus();
		$("#modal_propietario_tipo_persona").select2("open");
		return false;
	}

	if (nombre.length < 6) {
		alertify.error("Ingrese el nombre completo del propietario", 5);
		$("#modal_propietario_nombre").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 0) {
		alertify.error("Seleccione el tipo de documento de identidad", 5);
		$("#modal_propietario_tipo_docu").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 1 && num_docu.length != 8) {
		alertify.error("El número de DNI debe tener 8 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_propietario_num_docu").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 3 && num_docu.length != 12) {
		alertify.error("El número de Pasaporte debe tener 12 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_propietario_num_docu").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 4 && num_docu.length != 12) {
		alertify.error("El número de Carnet de Ext. debe tener 12 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_propietario_num_docu").focus();
		return false;
	}

	if (num_ruc.length != 11) {
		alertify.error("El número de RUC debe tener 11 dígitos, no " + num_ruc.length + " dígitos", 5);
		$("#modal_propietario_num_ruc").focus();
		return false;
	}

	if (direccion.length < 10) {
		alertify.error("Ingrese el dirección completa del propietario", 5);
		$("#modal_propietario_direccion").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 2 && representante_legal.length == 0) {
		alertify.error("Ingrese el representante legal", 5);
		$("#modal_propietario_representante_legal").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 2 && num_partida_registral.length == 0) {
		alertify.error("Ingrese el número de la Partida Registral de la empresa", 5);
		$("#modal_propietario_num_partida_registral").focus();
		return false;
	}

	if (accion == "guardar_propietario") {
		if (parseInt(tipo_persona_contacto) == 0) {
			alertify.error("Seleccione el tipo de persona contacto", 5);
			$("#modal_propietario_tipo_persona_contacto").focus();
			return false;
		}

		if (parseInt(tipo_persona_contacto) == 2 && contacto_nombre.length < 1) {
			alertify.error("Ingrese el nombre del contacto", 5);
			$("#modal_propietario_contacto_nombre").focus();
			return false;
		}
	}

	if (accion == "guardar_cambios_propietario") {
		if (contacto_nombre.length < 1) {
			alertify.error("Ingrese el nombre del contacto", 5);
			$("#modal_propietario_contacto_nombre").focus();
			return false;
		}
	}

	if (contacto_telefono.length < 8) {
		alertify.error("Ingrese el número telefónico del contaco", 5);
		$("#modal_propietario_contacto_telefono").focus();
		return false;
	}

	if (contacto_email.length > 0 && !sec_contrato_nuevo_es_email_valido(contacto_email)) {
		alertify.error("El formato del correo electrónico es incorrecto", 5);
		$("#modal_propietario_contacto_email").focus();
		return false;
	}

	if (accion == "guardar_propietario_adenda") {
		accion = "guardar_propietario";
	}

	var data = {
		accion: accion,
		id_propietario_para_cambios: id_propietario_para_cambios,
		tipo_persona: tipo_persona,
		nombre: nombre,
		tipo_docu: tipo_docu,
		num_docu: num_docu,
		num_ruc: num_ruc,
		direccion: direccion,
		representante_legal: representante_legal,
		num_partida_registral: num_partida_registral,
		tipo_persona_contacto: tipo_persona_contacto,
		contacto_nombre: contacto_nombre,
		contacto_telefono: contacto_telefono,
		contacto_email: contacto_email,
	};

	return data;
}

function sec_contrato_nuevo_validar_campos_formulario_propietario_ca(accion) {
	var id_propietario_para_cambios = $("#modal_propietaria_id_persona_para_cambios_ca").val();
	var tipo_persona = $("#modal_propietario_tipo_persona_ca").val();
	var nombre = $("#modal_propietario_nombre_ca").val().trim();
	var tipo_docu = $("#modal_propietario_tipo_docu_ca").val();
	var num_docu = $("#modal_propietario_num_docu_ca").val().trim();
	var num_ruc = $("#modal_propietario_num_ruc_ca").val().trim();
	var direccion = $("#modal_propietario_direccion_ca").val().trim();
	var representante_legal = $("#modal_propietario_representante_legal_ca").val().trim();
	var num_partida_registral = $("#modal_propietario_num_partida_registral_ca").val();
	var tipo_persona_contacto = $("#modal_propietario_tipo_persona_contacto_ca").val();
	var contacto_nombre = $("#modal_propietario_contacto_nombre_ca").val().trim();
	var contacto_telefono = $("#modal_propietario_contacto_telefono_ca").val().trim();
	var contacto_email = $("#modal_propietario_contacto_email_ca").val().trim();

	if (parseInt(tipo_persona) == 0) {
		alertify.error("Seleccione el tipo de persona", 5);
		$("#modal_propietario_tipo_persona_ca").focus();
		$("#modal_propietario_tipo_persona_ca").select2("open");
		return false;
	}

	if (nombre.length < 6) {
		alertify.error("Ingrese el nombre completo del propietario", 5);
		$("#modal_propietario_nombre_ca").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 0) {
		alertify.error("Seleccione el tipo de documento de identidad", 5);
		$("#modal_propietario_tipo_docu_ca").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 1 && num_docu.length != 8) {
		alertify.error("El número de DNI debe tener 8 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_propietario_num_docu_ca").focus();
		return false;
	}

	if (num_ruc.length != 11) {
		alertify.error("El número de RUC debe tener 11 dígitos, no " + num_ruc.length + " dígitos", 5);
		$("#modal_propietario_num_ruc_ca").focus();
		return false;
	}

	if (direccion.length < 10) {
		alertify.error("Ingrese el dirección completa del propietario", 5);
		$("#modal_propietario_direccion_ca").focus();
		return false;
	}




	if (parseInt(tipo_persona) == 2 && representante_legal.length == 0) {
		alertify.error("Ingrese el representante legal", 5);
		$("#modal_propietario_representante_legal_ca").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 2 && num_partida_registral.length == 0) {
		alertify.error("Ingrese el número de la Partida Registral de la empresa", 5);
		$("#modal_propietario_num_partida_registral_ca").focus();
		return false;
	}

	if (accion == "guardar_propietario") {
		if (parseInt(tipo_persona_contacto) == 0) {
			alertify.error("Seleccione el tipo de persona contacto", 5);
			$("#modal_propietario_tipo_persona_contacto_ca").focus();
			return false;
		}

		if (parseInt(tipo_persona_contacto) == 2 && contacto_nombre.length < 1) {
			alertify.error("Ingrese el nombre del contacto", 5);
			$("#modal_propietario_contacto_nombre_ca").focus();
			return false;
		}
	}

	if (accion == "guardar_cambios_propietario") {
		if (contacto_nombre.length < 1) {
			alertify.error("Ingrese el nombre del contacto", 5);
			$("#modal_propietario_contacto_nombre_ca").focus();
			return false;
		}

	
	}

	if (contacto_telefono.length < 8) {
		alertify.error("Ingrese el número telefónico del contacto", 5);
		$("#modal_propietario_contacto_telefono_ca").focus();
		return false;
	}

	if (contacto_email.length > 0 && !sec_contrato_nuevo_es_email_valido(contacto_email)) {
		alertify.error("El formato del correo electrónico es incorrecto", 5);
		$("#modal_propietario_contacto_email_ca").focus();
		return false;
	}

	if (accion == "guardar_propietario_agente") {
		accion = "guardar_propietario";
	}

	var data = {
		accion: accion,
		id_propietario_para_cambios: id_propietario_para_cambios,
		tipo_persona: tipo_persona,
		nombre: nombre,
		tipo_docu: tipo_docu,
		num_docu: num_docu,
		num_ruc: num_ruc,
		direccion: direccion,
		representante_legal: representante_legal,
		num_partida_registral: num_partida_registral,
		tipo_persona_contacto: tipo_persona_contacto,
		contacto_nombre: contacto_nombre,
		contacto_telefono: contacto_telefono,
		contacto_email: contacto_email,
	};

	return data;
}

function sec_contrato_nuevo_guardar_beneficiario(metodo) {
	var data = sec_contrato_nuevo_validar_campos_formulario_beneficiario(metodo);

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: metodo, data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: metodo, data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				if (metodo == "guardar_beneficiario") {
					sec_contrato_nuevo_asignar_beneficiario_al_contrato(respuesta.result, "modalAgregar");
				} else if (metodo == "guardar_beneficiario_adenda") {
					sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda("beneficiario", respuesta.result, "modalNuevoBeneficiario");
				}

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_guardar_cambios_beneficiario() {
	var data = sec_contrato_nuevo_validar_campos_formulario_beneficiario("guardar_cambios_beneficiario");

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: "guardar_cambios_beneficiario", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_cambios_beneficiario", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#modalNuevoBeneficiario").modal("hide");
				sec_contrato_nuevo_actualizar_tabla_beneficiario();
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_guardar_cambios_propietario() {
	var data = sec_contrato_nuevo_validar_campos_formulario_propietario("guardar_cambios_propietario");

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: "guardar_cambios_propietario", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_cambios_propietario", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#modalNuevoPropietario").modal("hide");
				sec_contrato_nuevo_actualizar_tabla_propietarios();
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_guardar_cambios_propietario_ca() {
	var data = sec_contrato_nuevo_validar_campos_formulario_propietario_ca("guardar_cambios_propietario");

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: "guardar_cambios_propietario", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_cambios_propietario", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#modalNuevoPropietario_ca").modal("hide");
				sec_contrato_nuevo_actualizar_tabla_propietarios_ca();
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_asignar_beneficiario_al_contrato(id_beneficiario, modal) {
	if (array_beneficiarios_contrato.includes(id_beneficiario) === false) {
		array_beneficiarios_contrato.push(id_beneficiario);
	}
	console.log(array_beneficiarios_contrato);
	if (modal == "modalAgregar") {
		$("#modalNuevoBeneficiario").modal("hide");
	}
	sec_contrato_nuevo_actualizar_tabla_beneficiario();
}

function sec_contrato_nuevo_actualizar_tabla_beneficiario() {
	var data = {
		accion: "obtener_beneficiarios",
		id_beneficiarios: JSON.stringify(array_beneficiarios_contrato),
	};

	var array_beneficiarios = [];

	auditoria_send({ proceso: "obtener_beneficiarios", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#divTablaBeneficiarios").html(respuesta.result);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_actualizar_tabla_beneficiario_ca() {
	var data = {
		accion: "obtener_beneficiarios_ca",
		id_beneficiarios: JSON.stringify(array_beneficiarios_contrato),
	};

	var array_beneficiarios = [];

	auditoria_send({ proceso: "obtener_beneficiarios_ca", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#divTablaBeneficiarios_ca").html(respuesta.result);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_editar_beneficiario(id_beneficiario) {
	$("#modalNuevoBeneficiario").modal("show");

	sec_contrato_nuevo_resetear_formulario_nuevo_beneficiario("edit");

	var data = {
		accion: "obtener_beneficiario",
		id_beneficiario: id_beneficiario,
	};

	var array_beneficiarios = [];

	auditoria_send({ proceso: "obtener_beneficiario", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				array_beneficiarios.push(respuesta.result);
				$("#modal_beneficiario_id_beneficiario_para_cambios").val(array_beneficiarios[0][0].id);
				$("#modal_beneficiario_tipo_persona").val(array_beneficiarios[0][0].tipo_persona_id);
				$("#modal_beneficiario_tipo_docu").val(array_beneficiarios[0][0].tipo_docu_identidad_id);
				$("#modal_beneficiario_num_docu").val(array_beneficiarios[0][0].num_docu);
				$("#modal_beneficiario_nombre").val(array_beneficiarios[0][0].nombre);
				$("#modal_beneficiario_id_forma_pago").val(array_beneficiarios[0][0].forma_pago_id);
				$("#modal_beneficiario_id_forma_pago").change();
				$("#modal_beneficiario_id_banco").val(array_beneficiarios[0][0].banco_id).trigger("change");
				$("#modal_beneficiario_num_cuenta_bancaria").val(array_beneficiarios[0][0].num_cuenta_bancaria);
				$("#modal_beneficiario_num_cuenta_cci").val(array_beneficiarios[0][0].num_cuenta_cci);
				$("#modal_beneficiario_tipo_monto").val(array_beneficiarios[0][0].tipo_monto_id);
				$("#modal_beneficiario_tipo_monto").change();
				$("#modal_beneficiario_monto").val(array_beneficiarios[0][0].monto);

				setTimeout(function () {
					$("#modal_beneficiario_id_banco").select2("close");
					$("#modal_beneficiario_tipo_persona").focus();
				}, 250);

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_editar_propietario(id_propietario) {

	var data = {
		accion: "obtener_propietario_por_id",
		nombre_o_numdocu: id_propietario,
		tipo_busqueda: "4",
		tipo_solicitud: "",
	};

	var array_propietarios = [];

	auditoria_send({ proceso: "obtener_propietario", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
						
			if (parseInt(respuesta.http_code) == 400) {
				alertify.error(respuesta.status, 5);
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#modalNuevoPropietario").modal("show");
				sec_contrato_nuevo_resetear_formulario_nuevo_propietario("edit");
				array_propietarios.push(respuesta.result);
				$("#modal_propietaria_id_persona_para_cambios").val(array_propietarios[0][0].id);
				$("#modal_propietario_tipo_persona").val(array_propietarios[0][0].tipo_persona_id).trigger("change");
				$("#modal_propietario_tipo_docu").val(array_propietarios[0][0].tipo_docu_identidad_id);
				$("#modal_propietario_num_docu").val(array_propietarios[0][0].num_docu);
				$("#modal_propietario_num_ruc").val(array_propietarios[0][0].num_ruc);
				$("#modal_propietario_nombre").val(array_propietarios[0][0].nombre);
				$("#modal_propietario_direccion").val(array_propietarios[0][0].direccion);
				$("#modal_propietario_representante_legal").val(array_propietarios[0][0].representante_legal);
				$("#modal_propietario_num_partida_registral").val(array_propietarios[0][0].num_partida_registral);
				$("#modal_propietario_tipo_persona_contacto").val(array_propietarios[0][0].xyz);
				$("#modal_propietario_contacto_nombre").val(array_propietarios[0][0].contacto_nombre);
				$("#modal_propietario_contacto_telefono").val(array_propietarios[0][0].contacto_telefono);
				$("#modal_propietario_contacto_email").val(array_propietarios[0][0].contacto_email);

				$("#modal_propietario_tipo_docu").change();

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_editar_propietario_ca(id_propietario) {
	$("#modalNuevoPropietario_ca").modal("show");

	sec_contrato_nuevo_resetear_formulario_nuevo_propietario_ca("edit");

	var data = {
		accion: "obtener_propietario",
		nombre_o_numdocu: id_propietario,
		tipo_busqueda: "4",
		tipo_solicitud: "",
	};

	var array_propietarios = [];

	auditoria_send({ proceso: "obtener_propietario", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				array_propietarios.push(respuesta.result);
				$("#modal_propietaria_id_persona_para_cambios_ca").val(array_propietarios[0][0].id);
				$("#modal_propietario_tipo_persona_ca").val(array_propietarios[0][0].tipo_persona_id).trigger("change");
				$("#modal_propietario_tipo_docu_ca").val(array_propietarios[0][0].tipo_docu_identidad_id);
				$("#modal_propietario_num_docu_ca").val(array_propietarios[0][0].num_docu);
				$("#modal_propietario_num_ruc_ca").val(array_propietarios[0][0].num_ruc);
				$("#modal_propietario_nombre_ca").val(array_propietarios[0][0].nombre);
				$("#modal_propietario_direccion_ca").val(array_propietarios[0][0].direccion);
				$("#modal_propietario_representante_legal_ca").val(array_propietarios[0][0].representante_legal);
				$("#modal_propietario_num_partida_registral_ca").val(array_propietarios[0][0].num_partida_registral);
				$("#modal_propietario_tipo_persona_contacto_ca").val(array_propietarios[0][0].xyz);
				$("#modal_propietario_contacto_nombre_ca").val(array_propietarios[0][0].contacto_nombre);
				$("#modal_propietario_contacto_telefono_ca").val(array_propietarios[0][0].contacto_telefono);
				$("#modal_propietario_contacto_email_ca").val(array_propietarios[0][0].contacto_email);

				$("#modal_propietario_tipo_docu_ca").change();

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_eliminar_beneficiario(id_beneficiario) {
	console.log(array_beneficiarios_contrato);
	const index = array_beneficiarios_contrato.indexOf(id_beneficiario);
	if (index > -1) {
		array_beneficiarios_contrato.splice(index, 1);
	}
	console.log(array_beneficiarios_contrato);
	sec_contrato_nuevo_actualizar_tabla_beneficiario();
}
// INICIO FUNCIONES BENEFICIARIOS

// INICO FUNCIO GUARDAR CONTRATO DE ARRENDAMIENTO
function sec_contrato_nuevo_guardar_contrato() {
	var usuario_logueado_id = $("#usuario_id_temporal").val();
	var tipo_contrato_id = $("#tipo_contrato_id").val();
	var empresa_suscribe_id = $("#empresa_suscribe_id").val();
	var area_responsable_id = $("#area_responsable_id").val();
	var personal_responsable_id = $("#personal_responsable_id").val();
	var observaciones = $("#contrato_observaciones").val();

	var id_departamento = $("#modal_inmueble_id_departamento").val();
	var id_provincia = $("#modal_inmueble_id_provincia").val();
	var id_distrito = $("#modal_inmueble_id_distrito").val();
	var ubicacion = $("#modal_inmueble_ubicacion").val().trim();
	var area_arrendada = $("#modal_inmueble_area_arrendada").val().trim();
	var num_partida_registral = $("#modal_inmueble_num_partida_registral").val();
	var oficina_registral = $("#modal_inmueble_oficina_registral").val();
	var num_suministro_agua = $("#modal_inmueble_num_suministro_agua").val();
	var tipo_compromiso_pago_agua = $("#modal_inmueble_tipo_compromiso_pago_agua").val();
	var monto_o_porcentaje_agua = $("#modal_inmueble_monto_o_porcentaje_agua").val().trim();
	var num_suministro_luz = $("#modal_inmueble_num_suministro_luz").val();
	var tipo_compromiso_pago_luz = $("#modal_inmueble_tipo_compromiso_pago_luz").val();
	var monto_o_porcentaje_luz = $("#modal_inmueble_monto_o_porcentaje_luz").val().trim();
	var tipo_compromiso_pago_arbitrios = $("#modal_inmueble_tipo_compromiso_pago_arbitrios").val();
	var porcentaje_pago_arbitrios = $("#modal_inmueble_porcentaje_pago_arbitrios").val().trim();
	var latitud = $("#modal_inmueble_latitud").val().trim();
	var longitud = $("#modal_inmueble_longitud").val().trim();

	var tipo_moneda_renta_pactada = $("#contrato_tipo_moneda_renta_pactada").val().trim();
	var tipo_pago_de_renta_id = $("#tipo_pago_de_renta_id").val().trim();
	var monto_renta = $("#contrato_monto_renta").val().trim();
	var porcentaje_venta = $("#porcentaje_venta").val().trim();
	var tipo_venta_id = $("#tipo_venta_id").val().trim();
	var tipo_igv_renta_id = $("#tipo_igv_renta_id").val().trim();
	var impuesto_a_la_renta_id = $("#contrato_impuesto_a_la_renta").val();
	var impuesto_a_la_renta_carta_de_instruccion_id = $("#contrato_impuesto_a_la_renta_carta_de_instruccion_id").val();
	var numero_cuenta_detraccion = $("#contrato_numero_cuenta_detraccion").val();
	var monto_garantia = $("#contrato_monto_garantia").val();
	var tipo_adelanto_id = $("#contrato_adelanto").val();

	var plazo_id = $("#plazo_id_arr").val();
	var vigencia_del_contrato_en_meses = $("#contrato_vigencia_del_contrato_en_meses").val();
	var contrato_inicio_fecha = $("#input_text_contrato_inicio_fecha").val();
	var contrato_fin_fecha = $("#input_text_contrato_fin_fecha").val();

	var periodo_gracia_id = $("#contrato_periodo_gracia_id").val();
	var periodo_gracia_numero = $("#contrato_periodo_gracia_numero").val();

	var tipo_incremento_id = $("#contrato_tipo_incremento_id").val();

	var contrato_fecha_suscripcion = $("#input_text_contrato_fecha_suscripcion").val();

	var tipo_inflacion_id = $("#contrato_tipo_inflacion_id").val();
	var tipo_cuota_extraordinaria_id = $("#contrato_tipo_cuota_extraordinaria_id").val();
if (parseInt(tipo_pago_de_renta_id) == 2 && porcentaje_venta.length == 0) {
		alertify.error("Ingrese el porcentaje de venta", 5);
		$("#porcentaje_venta").focus();
		return false;
	}

	if (parseInt(tipo_pago_de_renta_id) == 2 && parseFloat(porcentaje_venta) == 0) {
		alertify.error("El porcentaje de venta no puede ser 0", 5);
		$("#porcentaje_venta").focus();
		return false;
	}

	if (parseInt(tipo_pago_de_renta_id) == 2 && parseInt(tipo_venta_id) == 0) {
		alertify.error("Seleccione el tipo de venta", 5);
		$("#tipo_venta_id").focus();
		$("#tipo_venta_id").select2("open");
		return false;
	}

	if (parseInt(tipo_igv_renta_id) == 0) {
		alertify.error("Seleccione el IGV en la renta", 5);
		$("#tipo_igv_renta_id").focus();
		$("#tipo_igv_renta_id").select2("open");
		return false;
	}
	// INICIO VALIDAR DATOS PRINCIPALES
	if (parseInt(tipo_contrato_id) == 0) {
		alertify.error("Seleccione el tipo de solicitud", 5);
		$("#tipo_contrato_id").focus();
		$("#tipo_contrato_id").select2("open");
		return false;
	}

	if (parseInt(empresa_suscribe_id) == 0) {
		alertify.error("Seleccione la empresa que suscribe el contrato", 5);
		$("#empresa_suscribe_id").focus();
		$("#empresa_suscribe_id").select2("open");
		return false;
	}
	// FIN VALIDAR DATOS PRINCIPALES

	// INICIO VALIDAR PROPIETARIOS
	if (array_propietarios_contrato.length == 0) {
		alertify.error("Debe de agregar un propietario al contrato", 5);
		$("#modalBuscarPropietario").modal({ backdrop: "static", keyboard: false });
		$("#modal_propietario_nombre_o_numdocu").focus();
		return false;
	}
	// FIN VALIDAR PROPIETARIOS

	// INICIO VALIDAR INMUEBLES
	if (parseInt(id_departamento) == 0) {
		alertify.error("Seleccione el departamento del inmueble", 5);
		$("#modal_inmueble_id_departamento").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_departamento").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(id_provincia) == 0) {
		alertify.error("Seleccione la provincia del inmueble", 5);
		$("#modal_inmueble_id_provincia").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_provincia").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(id_distrito) == 0) {
		alertify.error("Seleccione el distrito del inmueble", 5);
		$("#modal_inmueble_id_distrito").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_distrito").select2("open");
		}, 200);
		return false;
	}

	if (ubicacion.length < 1) {
		alertify.error("Ingrese la ubicación del inmueble", 5);
		$("#modal_inmueble_ubicacion").focus();
		return false;
	}

	if (ubicacion.length < 6) {
		alertify.error("La ubicación del inmueble debe de ser mayor de 5 letras", 5);
		$("#modal_inmueble_ubicacion").focus();
		return false;
	}

	if (area_arrendada.length < 1) {
		alertify.error("Ingrese el area arrendada del inmueble", 5);
		$("#modal_inmueble_area_arrendada").focus();
		return false;
	}

	if (num_partida_registral.length < 5) {
		alertify.error("Ingrese el número de partida registral del inmueble", 5);
		$("#modal_inmueble_num_partida_registral").focus();
		return false;
	}

	if (oficina_registral.length < 1) {
		alertify.error("Ingrese el nombre de la oficina registral del inmueble", 5);
		$("#modal_inmueble_oficina_registral").focus();
		return false;
	}

	if (latitud.length < 4 && parseInt(usuario_logueado_id) != 4474) {
		alertify.error("Ingrese la latitud", 5);
		$("#modal_inmueble_latitud").focus();
		return false;
	}

	if (longitud.length < 4 && parseInt(usuario_logueado_id) != 4474) {
		alertify.error("Ingrese la longitud", 5);
		$("#modal_inmueble_longitud").focus();
		return false;
	}

	if (num_suministro_agua.length < 0) {
		alertify.error("Ingrese el número de suministro del agua del inmueble", 5);
		$("#modal_inmueble_num_suministro_agua").focus();
		return false;
	}

	if (num_suministro_agua.length < 3) {
		alertify.error("El número de suministro de agua debe ser mayor a 2 dígitos", 5);
		$("#modal_inmueble_num_suministro_agua").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 0) {
		alertify.error("Seleccione el tipo de compromiso de pago del servicio del agua", 5);
		$("#modal_inmueble_tipo_compromiso_pago_agua").focus();
		setTimeout(function () {
			$("#modal_inmueble_tipo_compromiso_pago_agua").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 1 && monto_o_porcentaje_agua.length < 1) {
		alertify.error("Ingrese el porcentaje del pago del servicio de agua", 5);
		$("#modal_inmueble_monto_o_porcentaje_agua").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 1 && monto_o_porcentaje_agua.length > 5) {
		alertify.error("Ingrese un porcentaje válido de pago del servicio de agua", 5);
		$("#modal_inmueble_monto_o_porcentaje_agua").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 2 && monto_o_porcentaje_agua.length < 1) {
		alertify.error("Ingrese el monto fijo del pago del servicio de agua", 5);
		$("#modal_inmueble_monto_o_porcentaje_agua").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 6 && monto_o_porcentaje_agua.length < 1) {
		alertify.error("Ingrese el monto base del servicio de agua", 5);
		$("#modal_inmueble_monto_o_porcentaje_agua").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_agua) == 7 && monto_o_porcentaje_agua.length < 1) {
		alertify.error("Ingrese el monto de la factura del servicio de agua", 5);
		$("#modal_inmueble_monto_o_porcentaje_agua").focus();
		return false;
	}

	if (num_suministro_luz.length < 1) {
		alertify.error("Ingrese el número de suministro del servicio de luz del inmueble", 5);
		$("#modal_inmueble_num_suministro_luz").focus();
		return false;
	}

	if (num_suministro_luz.length < 3) {
		alertify.error("El número de suministro de luz debe ser mayor a 2 dígitos", 5);
		$("#modal_inmueble_num_suministro_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 0) {
		alertify.error("Seleccione el tipo de compromiso de pago del servicio de luz", 5);
		$("#modal_inmueble_tipo_compromiso_pago_luz").focus();
		setTimeout(function () {
			$("#modal_inmueble_tipo_compromiso_pago_luz").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 1 && monto_o_porcentaje_luz.length < 1) {
		alertify.error("Ingrese el porcentaje del servicio de luz", 5);
		$("#modal_inmueble_monto_o_porcentaje_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 1 && monto_o_porcentaje_luz.length > 5) {
		alertify.error("Ingrese un porcentaje válido de pago del servicio de luz", 5);
		$("#modal_inmueble_monto_o_porcentaje_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 2 && monto_o_porcentaje_luz.length < 1) {
		alertify.error("Ingrese el monto del servicio de luz", 5);
		$("#modal_inmueble_monto_o_porcentaje_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 6 && monto_o_porcentaje_luz.length < 1) {
		alertify.error("Ingrese el monto base del servicio de luz", 5);
		$("#modal_inmueble_monto_o_porcentaje_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_luz) == 7 && monto_o_porcentaje_luz.length < 1) {
		alertify.error("Ingrese el monto de la factura del servicio de luz", 5);
		$("#modal_inmueble_monto_o_porcentaje_luz").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_arbitrios) == 0) {
		alertify.error("Seleccione el tipo de compromiso de pago de arbitrios", 5);
		$("#modal_inmueble_tipo_compromiso_pago_arbitrios").focus();
		setTimeout(function () {
			$("#modal_inmueble_tipo_compromiso_pago_arbitrios").select2("open", 5);
		}, 200);
		return false;
	}

	if (parseInt(tipo_compromiso_pago_arbitrios) == 1 && porcentaje_pago_arbitrios.length < 1) {
		alertify.error("Ingrese el porcentaje de pago de arbitrios", 5);
		$("#modal_inmueble_porcentaje_pago_arbitrios").focus();
		return false;
	}

	if (parseInt(tipo_compromiso_pago_arbitrios) == 1 && porcentaje_pago_arbitrios.length > 5) {
		alertify.error("Ingrese un porcentaje válido de pago de arbitrios", 5);
		$("#modal_inmueble_porcentaje_pago_arbitrios").focus();
		return false;
	}
	// FIN VALIDAR INMUEBLES

	// INICIO VALIDAR CONDICIONES ECONOMICAS
	if (parseInt(tipo_moneda_renta_pactada) == 0) {
		alertify.error("Seleccione la moneda del contrato", 5);
		$("#contrato_tipo_moneda_renta_pactada").focus();
		$("#contrato_tipo_moneda_renta_pactada").select2("open");
		return false;
	}

	

	if (monto_renta.trim().length < 3) {
		alertify.error("Ingrese el monto de renta pactada", 5);
		$("#contrato_monto_renta").focus();
		return false;
	}

	if (parseInt(impuesto_a_la_renta_id) == 0) {
		alertify.error("Seleccione el impuesto a la renta", 5);
		$("#contrato_impuesto_a_la_renta").focus();
		$("#contrato_impuesto_a_la_renta").select2("open");
		return false;
	}

	if (parseInt(impuesto_a_la_renta_carta_de_instruccion_id) == 0 && parseInt(impuesto_a_la_renta_id) != 4) {
		alertify.error("Seleccione si AT deposita impuesto a la renta a SUNAT, o no.", 5);
		$("#contrato_impuesto_a_la_renta_carta_de_instruccion_id").focus();
		$("#contrato_impuesto_a_la_renta_carta_de_instruccion_id").select2("open");
		return false;
	}

	if (parseInt(impuesto_a_la_renta_id) == 4 && numero_cuenta_detraccion.trim().length < 2) {
		alertify.error("Ingrese el Número de Cuenta de Detracción (Banco de la Nación)", 5);
		$("#contrato_numero_cuenta_detraccion").focus();
		return false;
	}

	if (monto_garantia.trim().length < 3) {
		alertify.error("Ingrese el monto de garantía", 5);
		$("#contrato_monto_garantia").focus();
		return false;
	}

	if (parseInt(tipo_adelanto_id) == 0) {
		alertify.error("Seleccione si el contrato posee adelantos", 5);
		$("#contrato_adelanto").focus();
		$("#contrato_adelanto").select2("open");
		return false;
	}

	if (parseInt(tipo_adelanto_id) == 1 && array_adelantos_contrato.length == 0) {
		alertify.error("Seleccione los meses de adelanto");
		$("#modalAdelantos").modal({ backdrop: "static", keyboard: false });
		return false;
	}
	// FIN VALIDAR CONDICIONES ECONOMICAS

	// INICIO VALIDAR PERIODO DE GRACIA
	if (parseInt(periodo_gracia_id) == 1 && periodo_gracia_numero.length == 0) {
		alertify.error("Ingrese el numero de periodo de gracias", 5);
		$("#contrato_periodo_gracia_numero").focus();
		return false;
	}
	// FIN VALIDAR PERIODO DE GRACIA

	// INICIO VALIDAR INCREMENTOS
	if (parseInt(tipo_incremento_id) == 0) {
		alertify.error("Seleccione si el contrato posee incrementos", 5);
		$("#contrato_tipo_incremento_id").focus();
		$("#contrato_tipo_incremento_id").select2("open");
		return false;
	}

	if (parseInt(tipo_incremento_id) == 1 && array_incrementos_contrato.length == 0) {
		alertify.error("Ingrese el incremento del contrato");
		$("#modalAgregarIncrementos").modal({ backdrop: "static", keyboard: false });
		return false;
	}
	// FIN VALIDAR INCREMENTOS

	//INICIO INFLACION
	if (parseInt(tipo_inflacion_id) == 0) {
		alertify.error("Seleccione el tipo de inflacion", 5);
		$("#contrato_tipo_inflacion_id").focus();
		$("#contrato_tipo_inflacion_id").select2("open");
		return false;
	}
	//FIN INFLACION

	//INICIO INFLACION
	if (parseInt(tipo_cuota_extraordinaria_id) == 0) {
		alertify.error("Seleccione el tipo de cuota extraordinaria", 5);
		$("#contrato_tipo_cuota_extraordinaria_id").focus();
		$("#contrato_tipo_cuota_extraordinaria_id").select2("open");
		return false;
	}
	//FIN INFLACION
	


	// INICIO VALIDAR BENEFICIARIO
	if (array_beneficiarios_contrato.length == 0) {
		alertify.error("Agregar el beneficiario a la solicitud de contrato", 5);
		$("#modalCandidatosBeneficiario").modal({ backdrop: "static", keyboard: false });
		return false;
	}
	// FIN VALIDAR BENEFICIARIO

	var dataForm = new FormData($("#form_contrato_arrendatario")[0]);

	dataForm.append("accion", "guardar_contrato");
	dataForm.append("tipo_contrato_id", tipo_contrato_id);
	dataForm.append("empresa_suscribe_id", empresa_suscribe_id);
	dataForm.append("area_responsable_id", area_responsable_id);
	dataForm.append("personal_responsable_id", personal_responsable_id);
	dataForm.append("observaciones", observaciones);

	dataForm.append("id_departamento", id_departamento);
	dataForm.append("id_provincia", id_provincia);
	dataForm.append("id_distrito", id_distrito);
	dataForm.append("ubicacion", ubicacion);
	dataForm.append("area_arrendada", area_arrendada);
	dataForm.append("num_partida_registral", num_partida_registral);
	dataForm.append("oficina_registral", oficina_registral);
	dataForm.append("num_suministro_agua", num_suministro_agua);
	dataForm.append("tipo_compromiso_pago_agua", tipo_compromiso_pago_agua);
	dataForm.append("monto_o_porcentaje_agua", monto_o_porcentaje_agua);
	dataForm.append("num_suministro_luz", num_suministro_luz);
	dataForm.append("tipo_compromiso_pago_luz", tipo_compromiso_pago_luz);
	dataForm.append("monto_o_porcentaje_luz", monto_o_porcentaje_luz);
	dataForm.append("tipo_compromiso_pago_arbitrios", tipo_compromiso_pago_arbitrios);
	dataForm.append("porcentaje_pago_arbitrios", porcentaje_pago_arbitrios);
	dataForm.append("latitud", latitud);
	dataForm.append("longitud", longitud);

	dataForm.append("tipo_moneda_renta_pactada", tipo_moneda_renta_pactada);
	dataForm.append("tipo_pago_de_renta_id", tipo_pago_de_renta_id);
	dataForm.append("monto_renta", monto_renta);
	dataForm.append("porcentaje_venta", porcentaje_venta);
	dataForm.append("tipo_venta_id", tipo_venta_id);
	dataForm.append("tipo_igv_renta_id", tipo_igv_renta_id);
	dataForm.append("impuesto_a_la_renta_id", impuesto_a_la_renta_id);
	dataForm.append("impuesto_a_la_renta_carta_de_instruccion_id", impuesto_a_la_renta_carta_de_instruccion_id);
	dataForm.append("numero_cuenta_detraccion", numero_cuenta_detraccion);
	dataForm.append("monto_garantia", monto_garantia);
	dataForm.append("tipo_adelanto_id", tipo_adelanto_id);
	dataForm.append("plazo_id", plazo_id);
	dataForm.append("vigencia_del_contrato_en_meses", vigencia_del_contrato_en_meses);
	dataForm.append("contrato_inicio_fecha", contrato_inicio_fecha);
	dataForm.append("contrato_fin_fecha", contrato_fin_fecha);
	dataForm.append("periodo_gracia_id", periodo_gracia_id);
	dataForm.append("periodo_gracia_numero", periodo_gracia_numero);
	dataForm.append("tipo_incremento_id", tipo_incremento_id);
	dataForm.append("tipo_inflacion_id", tipo_inflacion_id);
	dataForm.append("tipo_cuota_extraordinaria_id", tipo_cuota_extraordinaria_id);
	
	dataForm.append("contrato_fecha_suscripcion", contrato_fecha_suscripcion);
	dataForm.append("id_propietarios", JSON.stringify(array_propietarios_contrato));
	dataForm.append("id_inmuebles", JSON.stringify(array_inmuebles_contrato));
	dataForm.append("id_incrementos", JSON.stringify(array_incrementos_contrato));
	dataForm.append("id_beneficiarios", JSON.stringify(array_beneficiarios_contrato));
	dataForm.append("id_adelantos", JSON.stringify(array_adelantos_contrato));
	dataForm.append("id_inflaciones", JSON.stringify(array_inflacion_contrato));
	dataForm.append("id_cuenta_extraordinaria", JSON.stringify(array_cuota_extraordinaria_contrato));
	dataForm.append("array_nuevos_files_anexos", JSON.stringify(array_nuevos_files_anexos));

	auditoria_send({ proceso: "guardar_contrato", data: dataForm });

	$.ajax({
		url: "sys/set_contrato_nuevo.php",
		type: "POST",
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function (xhr) {
			loading(true);
		},
		success: function (data) {
			var respuesta = JSON.parse(data);

			auditoria_send({ proceso: "guardar_contrato", data: respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				swal(
					{
						title: "Registro exitoso",
						text: "La solicitud de arrendamiento fue registrada exitosamente",
						html: true,
						type: "success",
						timer: 6000,
						closeOnConfirm: false,
						showCancelButton: false,
					},
					function (isConfirm) {
						window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
					}
				);

				setTimeout(function () {
					window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
				}, 5000);

				return true;
			} else {

				if(!(typeof respuesta.error_title === 'undefined')) {
					swal(
						{
							title: respuesta.error_title,
							text: respuesta.error,
							html: true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false,
						},
						function (isConfirm) {
							window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
						}
					);
				} else {
					swal({
						title: "Error al guardar Solicitud de Arrendamiento",
						text: respuesta.error,
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false,
					});
				}
				
				
				return false;
			}
		},
		complete: function () {
			loading(false);
		},
	});
}
// FIN FUNCIO GUARDAR CONTRATO DE ARRENDAMIENTO
function ValidateEmailContrato_Agente(email) {
	var re =
		/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}
// INICO FUNCIO GUARDAR CONTRATO DE ARRENDAMIENTO
function sec_contrato_nuevo_guardar_contrato_agente() {
	var usuario_logueado_id = $("#usuario_id_temporal").val();
	var tipo_contrato_id = $("#tipo_contrato_id").val();
	var empresa_suscribe_id = $("#empresa_suscribe_id").val("1");
	var area_responsable_id = $("#area_responsable_id").val();
	var personal_responsable_id = $("#personal_responsable_id").val();
	var observaciones = $("#contrato_observaciones").val();
	var correos_adjuntos_ad = $("#correos_adjuntos_ad").val();

	var id_departamento = $("#modal_inmueble_id_departamento_ca").val();
	var id_provincia = $("#modal_inmueble_id_provincia_ca").val();
	var id_distrito = $("#modal_inmueble_id_distrito_ca").val();
	var ubicacion = $("#modal_inmueble_ubicacion_ca").val().trim();

	var participacion_id_bet = $("#participacion_id_bet").val("BETSHOP");
	var participacion_id_jv = $("#participacion_id_jv").val("JUEGOS VIRTUALES");
	var participacion_id_t = $("#participacion_id_t").val("TERMINALES");
	var participacion_id_b = $("#participacion_id_b").val("BINGO");
	var participacion_id_dw = $("#participacion_id_dw").val("DEPOSITO WEB");
	var porcentaje_participacion_bet = $("#porcentaje_participacion_bet").val().trim();
	var porcentaje_participacion_j = $("#porcentaje_participacion_j").val().trim();
	var porcentaje_participacion_ter = $("#porcentaje_participacion_ter").val().trim();
	var porcentaje_participacion_bin = $("#porcentaje_participacion_bin").val().trim();
	var porcentaje_participacion_dep = $("#porcentaje_participacion_dep").val().trim();
	var condicion_comercial_id_bet = $("#condicion_comercial_id_bet").val().trim();
	var condicion_comercial_id_jv = $("#condicion_comercial_id_jv").val().trim();
	var condicion_comercial_id_t = $("#condicion_comercial_id_t").val().trim();
	var condicion_comercial_id_b = $("#condicion_comercial_id_b").val().trim();
	var condicion_comercial_id_dw = $("#condicion_comercial_id_dw").val().trim();
	//var bien_entregado = $("#bien_entregado").val().trim();
	// var detalle_bien_entradado = $("#detalle_bien_entradado").val().trim();

	var nombre_agente = $("#nombre_agente").val().trim();

	 
	var contrato_ag_observaciones = $("#contrato_ag_observaciones").val().trim();
	var periodo_numero = $("#periodo_numero_ca").val().trim();
	var periodo = $("#periodo_ca").val().trim();

	var area_arrendada = "";
	var num_partida_registral = "";
	var oficina_registral = "";
	var num_suministro_agua = "";
	var tipo_compromiso_pago_agua = "";
	var monto_o_porcentaje_agua = "";
	var num_suministro_luz = "";
	var tipo_compromiso_pago_luz = "";
	var monto_o_porcentaje_luz = "";
	var tipo_compromiso_pago_arbitrios = "";
	var porcentaje_pago_arbitrios = "";
	var latitud = "";
	var longitud = "";

	var tipo_moneda_renta_pactada = "";
	var monto_renta = "";
	var impuesto_a_la_renta_id = "";
	var impuesto_a_la_renta_carta_de_instruccion_id = "";
	var numero_cuenta_detraccion = "";
	var monto_garantia = "";
	var tipo_adelanto_id = "";

	var vigencia_del_contrato_en_meses = "";
	var contrato_inicio_fecha = "";
	var contrato_fin_fecha = "";

	var periodo_gracia_id = "";
	var periodo_gracia_numero = "";

	var tipo_incremento_id = "";

	var contrato_fecha_suscripcion = "";

	// INICIO VALIDAR DATOS PRINCIPALES
	if (parseInt(tipo_contrato_id) == 0) {
		alertify.error("Seleccione el tipo de solicitud", 5);
		$("#tipo_contrato_id").focus();
		$("#tipo_contrato_id").select2("open");
		return false;
	}

	let new_correos = [];
	if (correos_adjuntos_ad != "") {
		correos_adjuntos_ad = correos_adjuntos_ad.split(",");
		new_correos = correos_adjuntos_ad.map((item) => item.trim());
		for (let index = 0; index < new_correos.length; index++) {
			const element = new_correos[index];
			if (element.length == 0) {
				alertify.error(" Ingrese un correo", 5);
				return false;
			}
			if (!ValidateEmailContrato_Agente(element)) {
				alertify.error(element + " no es correo valido", 5);
				return false;
			}
		}
	}

	if (parseInt(empresa_suscribe_id) == 0) {
		alertify.error("Seleccione la empresa que suscribe el contrato", 5);
		$("#empresa_suscribe_id").focus();
		$("#empresa_suscribe_id").select2("open");
		return false;
	}
	// FIN VALIDAR DATOS PRINCIPALES

	// INICIO VALIDAR PROPIETARIOS
	if (array_propietarios_contrato.length == 0) {
		alertify.error("Debe de agregar un propietario al contrato", 5);
		$("#modalBuscarPropietario_ca").modal({ backdrop: "static", keyboard: false });
		$("#modal_propietario_nombre_o_numdocu_ca").focus();
		return false;
	}
	// FIN VALIDAR PROPIETARIOS

	// INICIO VALIDAR INMUEBLES
	if (parseInt(id_departamento) == 0) {
		alertify.error("Seleccione el departamento del inmueble", 5);
		$("#modal_inmueble_id_departamento_ca").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_departamento_ca").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(id_provincia) == 0) {
		alertify.error("Seleccione la provincia del inmueble", 5);
		$("#modal_inmueble_id_provincia_ca").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_provincia_ca").select2("open");
		}, 200);
		return false;
	}

	if (parseInt(id_distrito) == 0) {
		alertify.error("Seleccione el distrito del inmueble", 5);
		$("#modal_inmueble_id_distrito_ca").focus();
		setTimeout(function () {
			$("#modal_inmueble_id_distrito_ca").select2("open");
		}, 200);
		return false;
	}

	if (ubicacion.length < 1) {
		alertify.error("Ingrese la ubicación del inmueble", 5);
		$("#modal_inmueble_ubicacion_ca").focus();
		return false;
	}

	if (ubicacion.length < 6) {
		alertify.error("La ubicación del inmueble debe de ser mayor de 5 letras", 5);
		$("#modal_inmueble_ubicacion_ca").focus();
		return false;
	}

	// INICIO VALIDAR CONDICION COMERCIAL
	/*if (participacion_id.length == 0) {
		alertify.error("Seleccione un tipo ", 5);
		$("#participacion_id").focus();
		return false;
	}
	if (porcentaje_participacion.length == 0) {
		alertify.error("Ingrese un porcentaje de participación ", 5);
		$("#porcentaje_participacion").focus();
		return false;
	}
	if (condicion_comercial_id.length == 0) {
		alertify.error("Seleccione una condición comercial", 5);
		$("#condicion_comercial_id").focus();
		return false;
	}*/
	// FIN VALIDAR CONDICION COMERCIAL

	// INICIO VALIDAR BIEN ENTRAGADO
	/*
 if (bien_entregado.length == 0) {
		alertify.error("Seleccione si cuenta con un bien entregado", 5);
		$("#bien_entregado").focus();
		return false;
	}
	if (bien_entregado == "SI") {
		if (bien_entregado == "SI" && detalle_bien_entradado.length == 0) {
			alertify.error("Ingrese un porcentaje de participación ", 5);
			$("#detalle_bien_entradado").focus();
			return false;
		}
	}
	*/
	// FIN VALIDAR BIEN ENTRAGADO

	// INICIO VALIDAR PERIODO
	if (periodo_numero.length == 0) {
		alertify.error("Ingrese un periodo", 5);
		$("#periodo_numero").focus();
		return false;
	}
	if (periodo.length == 0) {
		alertify.error("Seleccione un periodo", 5);
		$("#periodo").focus();
		return false;
	}
	// FIN VALIDAR PERIODO
	//  debugger;
	var dataForm = new FormData($("#form_contrato_agente")[0]);

	dataForm.append("accion", "guardar_contrato_agente");
	dataForm.append("tipo_contrato_id", tipo_contrato_id);
	dataForm.append("empresa_suscribe_id", empresa_suscribe_id);
	dataForm.append("area_responsable_id", area_responsable_id);
	dataForm.append("personal_responsable_id", personal_responsable_id);
	dataForm.append("observaciones", observaciones);
	dataForm.append("correos_adjuntos_ad", JSON.stringify(new_correos));

	dataForm.append("id_departamento", id_departamento);
	dataForm.append("id_provincia", id_provincia);
	dataForm.append("id_distrito", id_distrito);
	dataForm.append("ubicacion", ubicacion);
	dataForm.append("area_arrendada", area_arrendada);
	dataForm.append("num_partida_registral", num_partida_registral);
	dataForm.append("oficina_registral", oficina_registral);
	dataForm.append("num_suministro_agua", num_suministro_agua);
	dataForm.append("tipo_compromiso_pago_agua", tipo_compromiso_pago_agua);
	dataForm.append("monto_o_porcentaje_agua", monto_o_porcentaje_agua);
	dataForm.append("num_suministro_luz", num_suministro_luz);
	dataForm.append("tipo_compromiso_pago_luz", tipo_compromiso_pago_luz);
	dataForm.append("monto_o_porcentaje_luz", monto_o_porcentaje_luz);
	dataForm.append("tipo_compromiso_pago_arbitrios", tipo_compromiso_pago_arbitrios);
	dataForm.append("porcentaje_pago_arbitrios", porcentaje_pago_arbitrios);
	dataForm.append("latitud", latitud);
	dataForm.append("longitud", longitud);

	dataForm.append("participacion_id_bet", participacion_id_bet);
	dataForm.append("participacion_id_jv", participacion_id_jv);
	dataForm.append("participacion_id_t", participacion_id_t);
	dataForm.append("participacion_id_b", participacion_id_b);
	dataForm.append("participacion_id_dw", participacion_id_dw);
	dataForm.append("porcentaje_participacion_bet", porcentaje_participacion_bet);
	dataForm.append("porcentaje_participacion_j", porcentaje_participacion_j);
	dataForm.append("porcentaje_participacion_ter", porcentaje_participacion_ter);
	dataForm.append("porcentaje_participacion_bin", porcentaje_participacion_bin);
	dataForm.append("porcentaje_participacion_dep", porcentaje_participacion_dep);
	dataForm.append("condicion_comercial_id_bet", condicion_comercial_id_bet);
	dataForm.append("condicion_comercial_id_jv", condicion_comercial_id_jv);
	dataForm.append("condicion_comercial_id_t", condicion_comercial_id_t);
	dataForm.append("condicion_comercial_id_b", condicion_comercial_id_b);
	dataForm.append("condicion_comercial_id_dw", condicion_comercial_id_dw);

	dataForm.append("nombre_agente", nombre_agente);

	// dataForm.append("bien_entregado", bien_entregado);
	// dataForm.append("detalle_bien_entradado", detalle_bien_entradado);
	dataForm.append("periodo_numero", periodo_numero);
	dataForm.append("periodo", periodo);
	 
	dataForm.append("contrato_ag_observaciones", contrato_ag_observaciones);

	dataForm.append("tipo_moneda_renta_pactada", tipo_moneda_renta_pactada);
	dataForm.append("monto_renta", monto_renta);
	dataForm.append("impuesto_a_la_renta_id", impuesto_a_la_renta_id);
	dataForm.append("impuesto_a_la_renta_carta_de_instruccion_id", impuesto_a_la_renta_carta_de_instruccion_id);
	dataForm.append("numero_cuenta_detraccion", numero_cuenta_detraccion);
	dataForm.append("monto_garantia", monto_garantia);
	dataForm.append("tipo_adelanto_id", tipo_adelanto_id);
	dataForm.append("vigencia_del_contrato_en_meses", vigencia_del_contrato_en_meses);
	dataForm.append("contrato_inicio_fecha", contrato_inicio_fecha);
	dataForm.append("contrato_fin_fecha", contrato_fin_fecha);
	dataForm.append("periodo_gracia_id", periodo_gracia_id);
	dataForm.append("periodo_gracia_numero", periodo_gracia_numero);
	dataForm.append("tipo_incremento_id", tipo_incremento_id);
	dataForm.append("contrato_fecha_suscripcion", contrato_fecha_suscripcion);
	dataForm.append("id_propietarios", JSON.stringify(array_propietarios_contrato));
	dataForm.append("id_inmuebles", JSON.stringify(array_inmuebles_contrato));
	// dataForm.append("id_incrementos", JSON.stringify(array_incrementos_contrato));
	dataForm.append("id_beneficiarios", JSON.stringify(array_beneficiarios_contrato));
	// dataForm.append("id_adelantos", JSON.stringify(array_adelantos_contrato));
	dataForm.append("array_nuevos_files_anexos", JSON.stringify(array_nuevos_files_anexos));

	auditoria_send({ proceso: "guardar_contrato_agente", data: dataForm });

	$.ajax({
		url: "sys/set_contrato_nuevo.php",
		type: "POST",
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function (xhr) {
			loading(true);
		},
		success: function (data) {
			var respuesta = JSON.parse(data);
			auditoria_send({ proceso: "guardar_contrato_agente", data: respuesta });
			if (parseInt(respuesta.http_code) == 200) {
				swal(
					{
						title: "Registro exitoso",
						text: "La solicitud de contrato de agente fue registrada exitosamente",
						html: true,
						type: "success",
						timer: 6000,
						closeOnConfirm: false,
						showCancelButton: false,
					},
					function (isConfirm) {
						window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
					}
				);

				setTimeout(function () {
					window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
				}, 5000);

				return true;
			} else {
				if (typeof respuesta.error === "undefined") {
					texto_del_mensaje = respuesta.mensaje;
				} else {
					texto_del_mensaje = respuesta.error;
				}
				swal({
					title: "Error al guardar Solicitud de Contrato de Agente",
					text: texto_del_mensaje,
					html: true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false,
				});
				return false;
			}
		},
		complete: function () {
			loading(false);
		},
	});
}
// FIN FUNCIO GUARDAR CONTRATO DE ARRENDAMIENTO

// INICIO FUNCIONES ADENDAS //
function sec_contrato_nuevo_solicitud_editar_campo_adenda(
	nombre_menu_usuario,
	nombre_tabla,
	nombre_campo,
	nombre_campo_usuario,
	tipo_valor,
	valor_actual,
	metodo_select,
	id_del_registro
) {
	$("#div_modal_adenda_mensaje").hide();
	$("#form_adenda")[0].reset();
	$("#modal_adenda").modal({ backdrop: "static", keyboard: false });
	$("#adenda_valor_select_option").select2({
		dropdownParent: $("#modal_adenda"),
		width: "100%",
	});
	$("#adenda_nombre_menu_usuario").html(nombre_menu_usuario);
	$("#adenda_nombre_campo_usuario").html(nombre_campo_usuario);
	$("#adenda_valor_actual").html(valor_actual);

	$("#adenda_id_del_registro").val(id_del_registro);
	$("#adenda_nombre_tabla").val(nombre_tabla);
	$("#adenda_nombre_campo").val(nombre_campo);
	$("#adenda_tipo_valor").val(tipo_valor);

	if (tipo_valor == "varchar") {
		$("#div_adenda_valor_varchar").show();
		$("#div_adenda_valor_int").hide();
		$("#div_adenda_valor_date").hide();
		$("#div_adenda_valor_decimal").hide();
		$("#div_adenda_valor_select_option").hide();
		$("#div_adenda_solicitud_departamento").hide();
		$("#div_adenda_solicitud_provincias").hide();
		$("#div_adenda_solicitud_distrito").hide();
		setTimeout(function () {
			$("#adenda_valor_varchar").focus();
		}, 500);
	}

	if (tipo_valor == "int") {
		$("#div_adenda_valor_varchar").hide();
		$("#div_adenda_valor_int").show();
		$("#div_adenda_valor_date").hide();
		$("#div_adenda_valor_decimal").hide();
		$("#div_adenda_valor_select_option").hide();
		$("#div_adenda_solicitud_departamento").hide();
		$("#div_adenda_solicitud_provincias").hide();
		$("#div_adenda_solicitud_distrito").hide();
		setTimeout(function () {
			$("#adenda_valor_int").focus();
		}, 500);
	}

	if (tipo_valor == "date") {
		$("#div_adenda_valor_varchar").hide();
		$("#div_adenda_valor_int").hide();
		$("#div_adenda_valor_date").show();
		$("#div_adenda_valor_decimal").hide();
		$("#div_adenda_valor_select_option").hide();
		setTimeout(function () {
			$("#adenda_valor_date").focus();
		}, 500);
	}

	if (tipo_valor == "decimal") {
		$("#div_adenda_valor_varchar").hide();
		$("#div_adenda_valor_int").hide();
		$("#div_adenda_valor_date").hide();
		$("#div_adenda_valor_decimal").show();
		$("#div_adenda_valor_select_option").hide();
		$("#div_adenda_solicitud_departamento").hide();
		$("#div_adenda_solicitud_provincias").hide();
		$("#div_adenda_solicitud_distrito").hide();
		setTimeout(function () {
			$("#adenda_valor_decimal").focus();
		}, 500);
	}

	if (tipo_valor == "select_option") {
		if (nombre_campo == "ubigeo_id") {
			$("#div_adenda_valor_varchar").hide();
			$("#div_adenda_valor_int").hide();
			$("#div_adenda_valor_date").hide();
			$("#div_adenda_valor_decimal").hide();
			$("#div_adenda_solicitud_departamento").show();
			$("#div_adenda_solicitud_provincias").show();
			$("#div_adenda_solicitud_distrito").show();

			$("#div_adenda_valor_select_option").hide();
			sec_contrato_nuevo_obtener_opciones(metodo_select, $("[name='adenda_inmueble_id_departamento']"));
			$("#adenda_inmueble_id_departamento").select2({
				dropdownParent: $("#modal_adenda"),
				width: "100%",
			});
			$("#adenda_inmueble_id_provincia").select2({
				dropdownParent: $("#modal_adenda"),
				width: "100%",
			});
			$("#adenda_inmueble_id_distrito").select2({
				dropdownParent: $("#modal_adenda"),
				width: "100%",
			});
			setTimeout(function () {
				$("#adenda_inmueble_id_departamento").focus();
			}, 500);
		} else {
			$("#div_adenda_valor_varchar").hide();
			$("#div_adenda_valor_int").hide();
			$("#div_adenda_valor_date").hide();
			$("#div_adenda_valor_decimal").hide();
			$("#div_adenda_solicitud_departamento").hide();
			$("#div_adenda_solicitud_provincias").hide();
			$("#div_adenda_solicitud_distrito").hide();

			$("#div_adenda_valor_select_option").show();
			sec_contrato_nuevo_obtener_opciones(metodo_select, $("[name='adenda_valor_select_option']"));
			setTimeout(function () {
				$("#adenda_valor_select_option").focus();
			}, 500);
		}
	}
}

function sec_contrato_nuevo_guardar_detalle_adenda(name_modal_close) {
	var nombre_tabla = $("#adenda_nombre_tabla").val();
	var nombre_campo = $("#adenda_nombre_campo").val();
	var nombre_menu_usuario = $("#adenda_nombre_menu_usuario").html();
	var nombre_campo_usuario = $("#adenda_nombre_campo_usuario").html();
	var valor_actual = $("#adenda_valor_actual").html();
	var tipo_valor = $("#adenda_tipo_valor").val();
	var valor_varchar = $("#adenda_valor_varchar").val();
	var valor_int = $("#adenda_valor_int").val();
	var valor_date = $("#adenda_valor_date").val();
	var valor_decimal = $("#adenda_valor_decimal").val();
	var valor_select_option = $("#adenda_valor_select_option option:selected").text();
	var valor_select_option_id = $("#adenda_valor_select_option").val();
	var id_del_registro = $("#adenda_id_del_registro").val();

	var ubigeo_id_nuevo = $("#ubigeo_id_nuevo").val();
	var ubigeo_text_nuevo = $("#ubigeo_text_nuevo").val();

	$("#div_modal_adenda_mensaje").hide();

	if (tipo_valor == "varchar" && valor_varchar == "") {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
		$("#adenda_valor_varchar").focus();
		return;
	}

	if (tipo_valor == "int" && valor_int == "") {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
		$("#adenda_valor_int").focus();
		return;
	}

	if (tipo_valor == "date" && valor_date == "") {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
		$("#adenda_valor_date").focus();
		return;
	}

	if (tipo_valor == "decimal" && valor_decimal == "") {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
		$("#adenda_valor_decimal").focus();
		return;
	}

	if (tipo_valor == "select_option" && nombre_campo == "ubigeo_id") {
		if (ubigeo_id_nuevo.length != 6) {
			$("#div_modal_adenda_mensaje").show();
			$("#modal_adenda_mensaje").html("Seleccione una Departamento/Provincia/Distrito");
			return;
		}
	} else {
		if (tipo_valor == "select_option" && valor_select_option_id == 0) {
			$("#div_modal_adenda_mensaje").show();
			$("#modal_adenda_mensaje").html("Seleccione una opcion");
			$("#adenda_valor_select_option").focus();
			return;
		}
	}

	if (tipo_valor == "select_option") {
		valor_int = valor_select_option_id;
	}

	var data = {
		accion: "guardar_adenda_detalle",
		nombre_tabla: nombre_tabla,
		nombre_campo: nombre_campo,
		nombre_menu_usuario: nombre_menu_usuario,
		nombre_campo_usuario: nombre_campo_usuario,
		valor_original: valor_actual,
		tipo_valor: tipo_valor,
		valor_varchar: valor_varchar,
		valor_int: valor_int,
		valor_date: valor_date,
		valor_decimal: valor_decimal,
		valor_select_option: valor_select_option,
		ubigeo_id_nuevo: ubigeo_id_nuevo,
		ubigeo_text_nuevo: ubigeo_text_nuevo,
		id_del_registro: id_del_registro,
	};

	auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_adenda_detalle", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#frm_incremento")[0].reset();
				sec_contrato_nuevo_asignar_detalle_a_la_adenda(respuesta.result, name_modal_close);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_guardar_adenda() {
	var contrato_id = $("#contrato_id").val();
	var tipo_contrato_id = $("#tipo_contrato_id").val();

	$("#div_modal_adenda_mensaje").hide();

	if (contrato_id == "") {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html("No se puede guardar la adenda");
	}

	if (array_adendas_contrato.length == 0) {
		alertify.error("No hay solicitud de cambio de adenda", 5);
		//$('#modalBuscarPropietario').modal({backdrop: 'static', keyboard: false});
		//$("#modal_propietario_nombre_o_numdocu").focus();
		return false;
	}

	var data = {
		accion: "guardar_adenda",
		contrato_id: contrato_id,
		tipo_contrato_id: tipo_contrato_id,
		id_adendas: JSON.stringify(array_adendas_contrato),
	};

	auditoria_send({ proceso: "guardar_adenda", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)

			var respuesta = JSON.parse(resp);

			auditoria_send({ proceso: "guardar_adenda", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
				return false;
				// $('#frm_incremento')[0].reset();
				//    sec_contrato_nuevo_asignar_detalle_a_la_adenda(respuesta.result, name_modal_close)
				//    return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_asignar_detalle_a_la_adenda(id_adenda, modal) {
	if (array_adendas_contrato.includes(id_adenda) === false) {
		array_adendas_contrato.push(id_adenda);
	}
	console.log(array_adendas_contrato);
	if (modal == "modalAgregar") {
		$("#modal_adenda").modal("hide");
	} else {
		$("#".concat(modal)).modal("hide");
	}
	sec_contrato_nuevo_actualizar_tabla_detalle_adenda();
}

function sec_contrato_nuevo_eliminar_detalle_adenda(id_adenda) {
	console.log(array_adendas_contrato);
	const index = array_adendas_contrato.indexOf(id_adenda);
	if (index > -1) {
		array_adendas_contrato.splice(index, 1);
	}
	console.log(array_adendas_contrato);
	sec_contrato_nuevo_actualizar_tabla_detalle_adenda();
}

function sec_contrato_nuevo_actualizar_tabla_detalle_adenda() {
	if (array_adendas_contrato.length > 0) {
		var data = {
			accion: "obtener_adendas_detalle",
			id_adendas: JSON.stringify(array_adendas_contrato),
		};

		var array_adendas = [];

		auditoria_send({ proceso: "obtener_adendas_detalle", data: data });
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
			data: data,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (resp) {
				//  alert(datat)
				var respuesta = JSON.parse(resp);
				console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					return false;
				}

				if (parseInt(respuesta.http_code) == 200) {
					$("#divTablaAdendas").html(respuesta.result);
					return false;
				}
			},
			error: function () {},
		});
	} else {
		$("#divTablaAdendas").html("");
	}
}

function sec_contrato_nuevo_adenda_cambiar_beneficiario(beneficiario_id, contrato_id) {
	$("#modalNuevoBeneficiario").modal("show");
	$("#beneficiario_id_actual_adenda").val(beneficiario_id);
	$("#beneficiario_id_adenda").val(contrato_id);
	sec_contrato_nuevo_resetear_formulario_nuevo_beneficiario("adenda");
}

function sec_contrato_nuevo_buscar_propietario_adenda(propietario_id, persona_id) {
	$("#persona_id_actual_adenda").val(persona_id);
	$("#propietario_id_adenda").val(propietario_id);
	sec_contrato_nuevo_buscar_propietario_modal("adenda");
}

function sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda(tabla, id_nuevo, name_modal_close) {
	if (tabla == "propietario") {
		var id_actual = $("#persona_id_actual_adenda").val();
		var registro_id = $("#propietario_id_adenda").val();

		var nombre_tabla = "cont_propietario";
		var nombre_campo = "persona_id";
		var nombre_menu_usuario = "Propietario";
		var nombre_campo_usuario = "Cambio de propietario";
	} else if (tabla == "beneficiario") {
		var id_actual = $("#beneficiario_id_actual_adenda").val();
		var registro_id = $("#beneficiario_id_adenda").val();

		var nombre_tabla = "cont_beneficiarios";
		var nombre_campo = "contrato_id";
		var nombre_menu_usuario = "Beneficiario";
		var nombre_campo_usuario = "Cambio de beneficiario";
	} else if (tabla == "incrementos") {
		var id_actual = 0;
		var registro_id = id_nuevo.id;

		var nombre_tabla = "cont_incrementos";
		var nombre_campo = "contrato_id";
		var nombre_menu_usuario = "Nuevo Incremento";
		var nombre_campo_usuario = id_nuevo.nuevo_valor;
		id_nuevo = id_nuevo.id;
	}

	var data = {
		accion: "guardar_adenda_detalle",
		nombre_tabla: nombre_tabla,
		nombre_campo: nombre_campo,
		nombre_menu_usuario: nombre_menu_usuario,
		nombre_campo_usuario: nombre_campo_usuario,
		valor_original: id_actual,
		tipo_valor: "id_tabla",
		valor_varchar: "",
		valor_int: id_nuevo,
		valor_date: "",
		valor_decimal: "",
		valor_select_option: "",
		valor_id_tabla: registro_id,
	};

	auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_adenda_detalle", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#frm_incremento")[0].reset();
				sec_contrato_nuevo_asignar_detalle_a_la_adenda(respuesta.result, name_modal_close);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda_ca(tabla, id_nuevo, name_modal_close) {
	if (tabla == "propietario") {
		var id_actual = $("#persona_id_actual_adenda").val();
		var registro_id = $("#propietario_id_adenda").val();

		var nombre_tabla = "cont_propietario";
		var nombre_campo = "persona_id";
		var nombre_menu_usuario = "Propietario";
		var nombre_campo_usuario = "Cambio de propietario";
	} else if (tabla == "beneficiario") {
		var id_actual = $("#beneficiario_id_actual_adenda").val();
		var registro_id = $("#beneficiario_id_adenda").val();

		var nombre_tabla = "cont_beneficiarios";
		var nombre_campo = "contrato_id";
		var nombre_menu_usuario = "Beneficiario";
		var nombre_campo_usuario = "Cambio de beneficiario";
	}

	var data = {
		accion: "guardar_adenda_detalle",
		nombre_tabla: nombre_tabla,
		nombre_campo: nombre_campo,
		nombre_menu_usuario: nombre_menu_usuario,
		nombre_campo_usuario: nombre_campo_usuario,
		valor_original: id_actual,
		tipo_valor: "id_tabla",
		valor_varchar: "",
		valor_int: id_nuevo,
		valor_date: "",
		valor_decimal: "",
		valor_select_option: "",
		valor_id_tabla: registro_id,
	};

	auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_agente_detalle", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$("#frm_incremento_ca")[0].reset();
				sec_contrato_nuevo_asignar_detalle_a_la_adenda(respuesta.result, name_modal_close);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_buscar_propietario_modal(tipo_solicitud) {
	var tipo_solicitud = tipo_solicitud;

	if (tipo_solicitud == "adenda") {
		$("#modal_buscar_propietario_titulo").html("Adenda - Buscar Nuevo Propietario");
		$("#modal_buscar_propietario_tipo_solicitud").val("adenda");
	} else if (tipo_solicitud == "arrendamiento") {
		$("#modal_buscar_propietario_titulo").html("Buscar Propietario");
		$("#modal_buscar_propietario_tipo_solicitud").val("arrendamiento");
	}

	$("#modalBuscarPropietario").modal({ backdrop: "static", keyboard: false });
	$("#tlbPropietariosxBusqueda").html("");
	$("#divNoSeEncontroPropietario").hide();
	$("#divRegistrarNuevoPropietario").hide();
	$("#modal_propietario_nombre_o_numdocu").val("");
	$("#modal_propietario_nombre_o_numdocu").focus();
}

function sec_contrato_nuevo_buscar_propietario_modal_ca(tipo_solicitud) {
	var tipo_solicitud = tipo_solicitud;

	if (tipo_solicitud == "adenda") {
		$("#modal_buscar_propietario_titulo").html("Adenda - Buscar Nuevo Propietario");
		$("#modal_buscar_propietario_tipo_solicitud").val("adenda");
	} else if (tipo_solicitud == "arrendamiento") {
		$("#modal_buscar_propietario_titulo").html("Buscar Propietario");
		$("#modal_buscar_propietario_tipo_solicitud").val("arrendamiento");
	} else if (tipo_solicitud == "agente") {
		$("#modal_buscar_propietario_titulo_ca").html("Buscar Propietario");
		$("#modal_buscar_propietario_tipo_solicitud_ca").val("agente");
	}

	$("#modalBuscarPropietario_ca").modal({ backdrop: "static", keyboard: false });
	$("#tlbPropietariosxBusqueda_ca").html("");
	$("#divNoSeEncontroPropietario_ca").hide();
	$("#divRegistrarNuevoPropietario_ca").hide();
	$("#modal_propietario_nombre_o_numdocu_ca").val("");
	$("#modal_propietario_nombre_o_numdocu_ca").focus();
}

function sec_contrato_nuevo_nuevo_propietario_modal(tipo_solicitud) {
	var tipo_solicitud = tipo_solicitud;

	$("#modal_nuevo_propietario_tipo_solicitud").val(tipo_solicitud);

	if (tipo_solicitud == "arrendamiento") {
		$("#modal_nuevo_propietario_titulo").val("Nuevo Propietario");
		sec_contrato_nuevo_resetear_formulario_nuevo_propietario("new");
	} else if (tipo_solicitud == "adenda") {
		$("#modal_nuevo_propietario_titulo").val("Adenda - Nuevo Propietario");
		sec_contrato_nuevo_resetear_formulario_nuevo_propietario("adenda");
	}

	$("#div_modal_propietario_mensaje").hide();
	$("#modalBuscarPropietario").modal("hide");
	$("#modalNuevoPropietario").modal({ backdrop: "static", keyboard: false });

	var tipo_busqueda = $("#modal_propietario_tipo_busqueda").val();
	var nombre_o_numdocu = $("#modal_propietario_nombre_o_numdocu").val();
	if (tipo_busqueda == 1) {
		$("#modal_propietario_nombre").val(nombre_o_numdocu);
	} else if (tipo_busqueda == 2) {
		$("#modal_propietario_num_docu").val(nombre_o_numdocu);
	}

	setTimeout(function () {
		$("#modal_propietario_tipo_persona").select2("open");
	}, 500);
}

function sec_contrato_nuevo_nuevo_propietario_modal_ca(tipo_solicitud) {
	var tipo_solicitud = tipo_solicitud;

	$("#modal_nuevo_propietario_tipo_solicitud_ca").val(tipo_solicitud);

	if (tipo_solicitud == "arrendamiento") {
		$("#modal_nuevo_propietario_titulo_ca").val("Nuevo Propietario");
		sec_contrato_nuevo_resetear_formulario_nuevo_propietario_ca("new");
	} else if (tipo_solicitud == "adenda") {
		$("#modal_nuevo_propietario_titulo_ca").val("Adenda - Nuevo Propietario");
		sec_contrato_nuevo_resetear_formulario_nuevo_propietario_ca("adenda");
	} else if (tipo_solicitud == "agente") {
		$("#modal_nuevo_propietario_titulo_ca").val("Agente - Nuevo Propietario");
		sec_contrato_nuevo_resetear_formulario_nuevo_propietario_ca("agente");
	}

	$("#div_modal_propietario_mensaje_ca").hide();
	$("#modalBuscarPropietario_ca").modal("hide");
	$("#modalNuevoPropietario_ca").modal({ backdrop: "static", keyboard: false });

	var tipo_busqueda = $("#modal_propietario_tipo_busqueda_ca").val();
	var nombre_o_numdocu = $("#modal_propietario_nombre_o_numdocu_ca").val();
	if (tipo_busqueda == 1) {
		$("#modal_propietario_nombre_ca").val(nombre_o_numdocu);
	} else if (tipo_busqueda == 2) {
		$("#modal_propietario_num_docu_ca").val(nombre_o_numdocu);
	}

	setTimeout(function () {
		$("#modal_propietario_tipo_persona_ca").select2("open");
	}, 500);
}

function sec_contrato_nuevo_modal_agregar_incremento() {
	// $("#btnModalAgregarIncremento").click(function () {
	$("#modalAgregarIncrementos").modal({ backdrop: "static", keyboard: false });
	setTimeout(function () {
		$("#contrato_incrementos_monto_o_porcentaje").focus();
	}, 500);
	//});
}

function sec_contrato_nuevo_buscar_beneficiario_modal() {
	$("#modalCandidatosBeneficiario").modal({ backdrop: "static", keyboard: false });
	sec_contrato_nuevo_buscar_candidatos_beneficiarios();
}

function sec_contrato_nuevo_calcular_meses(fecha_inicio, fecha_fin) {
	var fecha_inicio = fecha_inicio;
	var fecha_fin = fecha_fin;
	fecha_inicio = fecha_inicio.substring(3, 5) + "/" + fecha_inicio.substring(0, 2) + "/" + fecha_inicio.substring(7, 11);
	fecha_fin = fecha_fin.substring(3, 5) + "/" + fecha_fin.substring(0, 2) + "/" + fecha_fin.substring(7, 11);
	var $startdate = new Date(fecha_inicio);
	var $enddate = new Date(fecha_fin);
	$enddate.setDate($enddate.getDate() + 1);
	var $months = $enddate.getMonth() - $startdate.getMonth() + 12 * ($enddate.getFullYear() - $startdate.getFullYear());
	return $months;
}

function sec_contrato_nuevo_calcular_anios_y_meses(meses) {
	if (meses == 0 || meses == "") {
		$("#contrato_vigencia_en_anios").val("0 años y 0 meses");
	} else if (meses < 12) {
		$("#contrato_vigencia_en_anios").val(meses + " meses");
	} else {
		var anio = parseInt(meses / 12);
		var meses_restantes = meses % 12;

		console.log(anio);

		if (anio == 0) {
			anio = "";
		} else if (anio == 1) {
			anio = anio + " año";
		} else if (anio > 1) {
			anio = anio + " años";
		}

		if (meses_restantes == 0) {
			meses_restantes = "";
		} else if (meses_restantes == 1) {
			meses_restantes = " y " + meses_restantes + " mes";
		} else if (meses_restantes > 1) {
			meses_restantes = " y " + meses_restantes + " meses";
		}

		$("#contrato_vigencia_en_anios").val(anio + meses_restantes);
	}
}

function sec_contrato_nuevo_monto_beneficiario_valido() {
	var tipo_monto = $("#modal_beneficiario_tipo_monto").val();
	var monto = $("#modal_beneficiario_monto").val();

	var data = {
		accion: "verificar_beneficiarios_monto",
		id_beneficiarios: JSON.stringify(array_beneficiarios_contrato),
		tipo_monto: tipo_monto,
		monto: monto,
	};

	var array_beneficiarios = [];

	auditoria_send({ proceso: "verificar_beneficiarios_monto", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				if (typeof respuesta.mensaje !== "undefined") {
					swal("Aviso", respuesta.msg, "warning");
					return false;
				} else {
					return true;
				}
			}
		},
		error: function () {},
	});
}

/*function sec_nuevo_abrir_modal_nuevos_anexos(){
	$('#modalNuevosAnexos').modal({backdrop: 'static', keyboard: false});
	var objeto = '';
	objeto = '<input type="file" id="sec_nuevo_file_nuevo_anexo" name="sec_nuevo_file_nuevo_anexo" required accept=".jpg, .jpeg, .png, .pdf">'
	$('#sec_contrato_nuevo_div_input_file_nuevo_anexo').html('');
	$('#sec_contrato_nuevo_div_input_file_nuevo_anexo').append(objeto);
	sec_nuevo_cargar_tipos_anexos();
}*/

/*function sec_contrato_nuevo_close_modal_nuevos_anexos(){
	$('#modalNuevosAnexos').modal('hide');
}*/

function sec_nuevo_cargar_tipos_anexos() {
	limpiar_select_tipos_anexos();
	array_tabla_subdiarios = [];
	var data = {
		accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
	};
	auditoria_send({ proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos", data: data });
	$.ajax({
		url: "sys/get_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);

			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function (index, item) {
					array_tabla_subdiarios.push(item);
					$("#modal_nuevo_anexo_select_tipos").append(
						'<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
					);
				});
				return false;
			}
		},
		error: function () {},
	});
}

function limpiar_select_tipos_anexos() {
	$("#modal_nuevo_anexo_select_tipos2").html("");
	$("#modal_nuevo_anexo_select_tipos2").append('<option value="0"> - Seleccione - </option>');

	$("#modal_nuevo_anexo_select_tipos2_ac").html("");
	$("#modal_nuevo_anexo_select_tipos2_ac").append('<option value="0"> - Seleccione - </option>');

	$("#modal_nuevo_anexo_select_tipos2_ca").html("");
	$("#modal_nuevo_anexo_select_tipos2_ca").append('<option value="0"> - Seleccione - </option>');
}

var nuevos_anexos_rr = [];
function sec_nuevo_modal_guardar_nuevo_anexo() {
	var nombre_archivo = "";
	var tamano_archivo = 0;
	var extension_archivo = "";
	var tipo_documento_seleccionado_nombre = "";
	var id_tipo_documento_seleccionado = 0;
	var id_nuevo_objeto_nuevo_anexo = 0;
	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();
	//ARCHIVO SUBIDO TYPE FILE
	var fileInput = $("#sec_nuevo_file_nuevo_anexo")[0];
	nombre_archivo = $("#sec_nuevo_file_nuevo_anexo")
		.val()
		.replace(/.*(\/|\\)/, "");
	if (nombre_archivo == "") {
		swal("Aviso", "Seleccione el archivo que desea registrar", "warning");
		return;
	}
	tamano_archivo = fileInput.files[0].size;
	extension_archivo = nombre_archivo.split(".");
	extension_archivo = extension_archivo[extension_archivo.length - 1];

	//TIPO DE ARCHIVO SELECCIONADO
	tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos option:selected").text();
	id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos option:selected").val();

	if (id_tipo_documento_seleccionado == 0) {
		swal("Aviso", "Seleccione tipo de anexo", "warning");
		return;
	}

	var hoy = new Date();
	var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
	var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();

	var Tiempo = fecha + "" + hora;
	id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
	var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

	var html = "";
	html += '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px;">';
	html += '<div class="form-group">';
	html += '<div class="control-label">';
	html += tipo_documento_seleccionado_nombre + ": ";
	html += "</div>";
	html +=
		'<input id="' +
		id_nuevo_objeto_nuevo_anexo +
		'" name="' +
		id_nuevo_objeto_nuevo_anexo +
		'" type="text" readonly="true" value="' +
		nombre_archivo +
		'"/>';
	html += '<input hidden type="file" name = "file_new_anexo" id = "file_' + id_nuevo_objeto_nuevo_anexo + '" />';
	//html += '<input name="' + id_nuevo_objeto_nuevo_anexo + '" type="file" readonly="true" value="' + nombre_archivo + '"/>';
	html +=
		'<button style="margin-left:10px;" class="btn btn-sm btn-danger" onclick="' + onclick + '"><i class="fa fa-trash-o"></i></button>';
	html += "</div>";
	html += "</div>";

	$("#file_" + id_nuevo_objeto_nuevo_anexo).val($("#sec_nuevo_file_nuevo_anexo").val());

	var objeto = {
		id_objeto: id_nuevo_objeto_nuevo_anexo,
		nombre_archivo: nombre_archivo,
		tamano_archivo: tamano_archivo,
		extension_archivo: extension_archivo,
		tipo_documento_seleccionado_nombre: tipo_documento_seleccionado_nombre,
		id_tipo_documento_seleccionado: id_tipo_documento_seleccionado,
	};
	nuevos_anexos_rr.push(objeto);
	//console.log(nuevos_anexos_rr);

	if (tipo_contrato_id == "1") {
		$("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item
	} else if (tipo_contrato_id == "2") {
		$("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // cargar el nuevo item
	}

	$("#modalNuevosAnexos").modal("hide");

	let inputFile = $("#sec_nuevo_file_nuevo_anexo");
	let filesContainer = $("#divListaAnexosCargados");
	var filesAnexos = [];
	divListaAnexos.style.display = "none";

	divListaAnexos.style.display = "";
	let newFiles = [];
	for (let index = 0; index < inputFile[0].files.length; index++) {
		let file = inputFile[0].files[index];
		newFiles.push(file);
		filesAnexos.push(file);
	}

	newFiles.forEach((file) => {
		let fileElement = $(`<p>${file.name}</p>`);
		fileElement.data("fileData", file);
		filesContainer.append(fileElement);

		fileElement.click(function (event) {
			let fileElement = $(event.target);
			let indexToRemove = filesAnexos.indexOf(fileElement.data("fileData"));
			fileElement.remove();
			filesAnexos.splice(indexToRemove, 1);
		});
	});
	console.log(filesAnexos);
	console.log(newFiles);

	$("#file_" + id_nuevo_objeto_nuevo_anexo).append(newFiles);
}

var nuevos_anexos_rr = [];
function sec_nuevo_modal_guardar_nuevo_anexo() {
	var nombre_archivo = "";
	var tamano_archivo = 0;
	var extension_archivo = "";
	var tipo_documento_seleccionado_nombre = "";
	var id_tipo_documento_seleccionado = 0;
	var id_nuevo_objeto_nuevo_anexo = 0;
	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();
	//ARCHIVO SUBIDO TYPE FILE
	var fileInput = $("#sec_nuevo_file_nuevo_anexo")[0];
	nombre_archivo = $("#sec_nuevo_file_nuevo_anexo")
		.val()
		.replace(/.*(\/|\\)/, "");
	if (nombre_archivo == "") {
		swal("Aviso", "Seleccione el archivo que desea registrar", "warning");
		return;
	}
	tamano_archivo = fileInput.files[0].size;
	extension_archivo = nombre_archivo.split(".");
	extension_archivo = extension_archivo[extension_archivo.length - 1];

	//TIPO DE ARCHIVO SELECCIONADO
	tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos option:selected").text();
	id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos option:selected").val();

	if (id_tipo_documento_seleccionado == 0) {
		swal("Aviso", "Seleccione tipo de anexo", "warning");
		return;
	}

	var hoy = new Date();
	var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
	var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();

	var Tiempo = fecha + "" + hora;
	id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
	var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

	var html = "";
	html += '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px;">';
	html += '<div class="form-group">';
	html += '<div class="control-label">';
	html += tipo_documento_seleccionado_nombre + ": ";
	html += "</div>";
	html +=
		'<input id="' +
		id_nuevo_objeto_nuevo_anexo +
		'" name="' +
		id_nuevo_objeto_nuevo_anexo +
		'" type="text" readonly="true" value="' +
		nombre_archivo +
		'"/>';
	html += '<input hidden type="file" name = "file_new_anexo" id = "file_' + id_nuevo_objeto_nuevo_anexo + '" />';
	//html += '<input name="' + id_nuevo_objeto_nuevo_anexo + '" type="file" readonly="true" value="' + nombre_archivo + '"/>';
	html +=
		'<button style="margin-left:10px;" class="btn btn-sm btn-danger" onclick="' + onclick + '"><i class="fa fa-trash-o"></i></button>';
	html += "</div>";
	html += "</div>";

	$("#file_" + id_nuevo_objeto_nuevo_anexo).val($("#sec_nuevo_file_nuevo_anexo").val());

	var objeto = {
		id_objeto: id_nuevo_objeto_nuevo_anexo,
		nombre_archivo: nombre_archivo,
		tamano_archivo: tamano_archivo,
		extension_archivo: extension_archivo,
		tipo_documento_seleccionado_nombre: tipo_documento_seleccionado_nombre,
		id_tipo_documento_seleccionado: id_tipo_documento_seleccionado,
	};
	nuevos_anexos_rr.push(objeto);
	//console.log(nuevos_anexos_rr);

	if (tipo_contrato_id == "1") {
		$("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item
	} else if (tipo_contrato_id == "2") {
		$("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // cargar el nuevo item
	}

	$("#modalNuevosAnexos").modal("hide");

	let inputFile = $("#sec_nuevo_file_nuevo_anexo");
	let filesContainer = $("#divListaAnexosCargados");
	var filesAnexos = [];
	divListaAnexos.style.display = "none";

	divListaAnexos.style.display = "";
	let newFiles = [];
	for (let index = 0; index < inputFile[0].files.length; index++) {
		let file = inputFile[0].files[index];
		newFiles.push(file);
		filesAnexos.push(file);
	}

	newFiles.forEach((file) => {
		let fileElement = $(`<p>${file.name}</p>`);
		fileElement.data("fileData", file);
		filesContainer.append(fileElement);

		fileElement.click(function (event) {
			let fileElement = $(event.target);
			let indexToRemove = filesAnexos.indexOf(fileElement.data("fileData"));
			fileElement.remove();
			filesAnexos.splice(indexToRemove, 1);
		});
	});
	console.log(filesAnexos);
	console.log(newFiles);

	$("#file_" + id_nuevo_objeto_nuevo_anexo).append(newFiles);
}

function sec_nuevo_modal_eliminar_nuevo_anexo(id_anexo) {
	nuevos_anexos_rr = nuevos_anexos_rr.filter((item) => item.id_objeto !== id_anexo);
	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();

	var html = "";
	$.each(nuevos_anexos_rr, function (ind, elem) {
		var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + elem["id_objeto"] + "')";

		html += '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px;">';
		html += '<div class="form-group">';
		html += '<div class="control-label">';
		html += elem["tipo_documento_seleccionado_nombre"] + ": ";
		html += "</div>";
		html +=
			'<input id="' + elem["id_objeto"] + '" name="' + elem["id_objeto"] + '" type="text" readonly="true" value="' + nombre_archivo + '"/>';
		html +=
			'<button style="margin-left:10px;" class="btn btn-sm btn-danger" onclick="' + onclick + '"><i class="fa fa-trash-o"></i></button>';
		html += "</div>";
		html += "</div>";
	});
	if (tipo_contrato_id == "1") {
		$("#sec_nuevo_nuevos_anexos_listado").html(""); // limpiar el div
		$("#sec_nuevo_nuevos_anexos_listado").append(html); // y cargarlo nuevamente sin el item eliminado
	} else if (tipo_contrato_id == "2") {
		$("#sec_nuevo_nuevos_anexos_listado_proveedor").html(""); // limpiar el div
		$("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // y cargarlo nuevamente sin el item eliminado
	}
}

// INICIO OTROS ANEXOS PROVEEDOR
function sec_contrato_nuevo_abrir_modal_tipos_anexos_proveedor() {
	$("#modaltiposanexos").modal({ backdrop: "static", keyboard: false });
	$("#modal_nuevo_anexo_tipo_contrato_id").val("2");
	sec_nuevo_cargar_tipos_anexos2();
}
// FIN OTROS ANEXOS PROVEEDOR

// INICIO OTROS ANEXOS ACUERDO DE CONFIDENCIALIDAD
function sec_contrato_nuevo_abrir_modal_tipos_anexos_acuerdo_confidencialidad() {
	$("#modaltiposanexos_ac").modal({ backdrop: "static", keyboard: false });
	$("#modal_nuevo_anexo_tipo_contrato_id_ac").val("5");

	sec_nuevo_cargar_tipos_anexos2_acuerdo_confidencialidad();
}
// FIN OTROS ANEXOS ACUERDO DE CONFIDENCIALIDAD

// INICIO OTROS ANEXOS ARRENDAMIENTO
var contArchivos = 1;

function sec_con_nuevo_abrir_modal_tipos_anexos() {
	$("#modaltiposanexos").modal({ backdrop: "static", keyboard: false });
	$("#modal_nuevo_anexo_tipo_contrato_id").val("1");
	sec_nuevo_cargar_tipos_anexos2();
}
// FIN OTROS ANEXOS ARRENDAMIENTO

function sec_con_nuevo_abrir_modal_tipos_anexos_ca() {
	$("#modaltiposanexos_ca").modal({ backdrop: "static", keyboard: false });
	$("#modal_nuevo_anexo_tipo_contrato_id_ca").val("6");
	sec_nuevo_cargar_tipos_anexos2_contrato_agente();
}
// FIN OTROS ANEXOS ARRENDAMIENTO

var array_nuevos_files_anexos = [];
function anadirArchivo() {
	tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2 option:selected").text();
	id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2 option:selected").val();

	if (id_tipo_documento_seleccionado != "0") {
		var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();

		//Sumamos a la variable el número de archivos.
		contArchivos = contArchivos + 1;
		//Agregamos el componente de tipo input
		var div = document.createElement("div");
		var input = document.createElement("input");
		var a = document.createElement("a");

		//Añadimos los atributos de div
		div.id = "archivo" + contArchivos;

		//Añadimos los atributos de input
		input.type = "file";
		input.name = "newAnexoPrueba[]";

		//Añadimos los atributos del enlace a eliminar
		a.href = "#";
		a.id = "archivo" + contArchivos;
		a.onclick = function () {
			borrarArchivo(a.id);
		};
		a.text = "X Eliminar archivo";

		//TIPO DE ARCHIVO SELECCIONADO
		tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2 option:selected").text();
		id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2 option:selected").val();
		html2 = "";
		html2 += '<div class="control-label">';
		html2 += tipo_documento_seleccionado_nombre + ": ";
		html2 += "</div>";

		var hoy = new Date();
		var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
		var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
		var Tiempo = fecha + "" + hora;
		id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
		//var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

		var onclick = "borrarArchivo('" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "')";

		var html = "";
		html +=
			'<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			'">';
		html += '<div class="form-group">';
		html += '<div class="control-label">';
		html += tipo_documento_seleccionado_nombre + ": ";
		html += "</div>";
		var onchange =
			"file(event,'" +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			"', " +
			id_tipo_documento_seleccionado +
			", '" +
			tipo_documento_seleccionado_nombre +
			"')";
		html += '<div style="margin-top:10px;">';
		html +=
			'<input name="miarchivo[]" type="file" id="' +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			'" class="col-md-11" onchange="' +
			onchange +
			'" style="padding: 0px 0px;"/>';
		html +=
			'<button class="btn btn-xs btn-danger col-md-1" style="width: 22px;" onclick="' +
			onclick +
			'"><i class="fa fa-trash-o"></i></button>';
		html += "</div>";
		html += "</div>";
		html += "</div>";
		if (tipo_contrato_id == "1") {
			$("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item
		} else if (tipo_contrato_id == "2") {
			$("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // cargar el nuevo item
		}

		$("#modaltiposanexos").modal("hide");
	} else {
		alertify.error("Seleccione el tipo de anexo", 5);
	}
}

function borrarArchivo(id_anexo) {
	//Restamos el número de archivos
	contArchivos = contArchivos - 1;

	array_nuevos_files_anexos = array_nuevos_files_anexos.filter((item) => item.id_objeto !== id_anexo);
	$("div[name=" + id_anexo + "]").remove();
}

///AÑADIR ARCHIVOS ACUERDO DE CONFIDENCILIDAD
// var array_nuevos_files_anexos_acuerdos_confidencilidad = [];
function anadirArchivo_ac() {
	tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").text();
	id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").val();

	if (id_tipo_documento_seleccionado != "0") {
		var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ac").val();

		//Sumamos a la variable el número de archivos.
		contArchivos = contArchivos + 1;
		//Agregamos el componente de tipo input
		var div = document.createElement("div");
		var input = document.createElement("input");
		var a = document.createElement("a");

		//Añadimos los atributos de div
		div.id = "archivo" + contArchivos;

		//Añadimos los atributos de input
		input.type = "file";
		input.name = "newAnexoPrueba[]";

		//Añadimos los atributos del enlace a eliminar
		a.href = "#";
		a.id = "archivo" + contArchivos;
		a.onclick = function () {
			borrarArchivo(a.id);
		};
		a.text = "X Eliminar archivo";

		//TIPO DE ARCHIVO SELECCIONADO
		tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").text();
		id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").val();
		html2 = "";
		html2 += '<div class="control-label">';
		html2 += tipo_documento_seleccionado_nombre + ": ";
		html2 += "</div>";

		var hoy = new Date();
		var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
		var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
		var Tiempo = fecha + "" + hora;
		id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
		//var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

		var onclick = "borrarArchivo('" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "')";

		var html = "";
		html +=
			'<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			'">';
		html += '<div class="form-group">';
		html += '<div class="control-label">';
		html += tipo_documento_seleccionado_nombre + ": ";
		html += "</div>";
		var onchange =
			"file(event,'" +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			"', " +
			id_tipo_documento_seleccionado +
			", '" +
			tipo_documento_seleccionado_nombre +
			"')";
		html += '<div style="margin-top:10px;">';
		html +=
			'<input name="miarchivo[]" type="file" id="' +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			'" class="col-md-11" onchange="' +
			onchange +
			'" style="padding: 0px 0px;"/>';
		html +=
			'<button class="btn btn-xs btn-danger col-md-1" style="width: 22px;" onclick="' +
			onclick +
			'"><i class="fa fa-trash-o"></i></button>';
		html += "</div>";
		html += "</div>";
		html += "</div>";
		if (tipo_contrato_id == "1") {
			$("#sec_nuevo_nuevos_anexos_listado_ac").append(html); // cargar el nuevo item
		} else if (tipo_contrato_id == "5") {
			$("#sec_nuevo_nuevos_anexos_listado_proveedor_ac").append(html); // cargar el nuevo item
		}

		$("#modaltiposanexos_ac").modal("hide");
	} else {
		alertify.error("Seleccione el tipo de anexo", 5);
	}
}

//CONTRATO DE AGENTES
function anadirArchivo_ca() {
	tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").text();
	id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").val();

	if (id_tipo_documento_seleccionado != "0") {
		var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ca").val();

		//Sumamos a la variable el número de archivos.
		contArchivos = contArchivos + 1;
		//Agregamos el componente de tipo input
		var div = document.createElement("div");
		var input = document.createElement("input");
		var a = document.createElement("a");

		//Añadimos los atributos de div
		div.id = "archivo" + contArchivos;

		//Añadimos los atributos de input
		input.type = "file";
		input.name = "newAnexoPrueba[]";

		//Añadimos los atributos del enlace a eliminar
		a.href = "#";
		a.id = "archivo" + contArchivos;
		a.onclick = function () {
			borrarArchivo(a.id);
		};
		a.text = "X Eliminar archivo";

		//TIPO DE ARCHIVO SELECCIONADO
		tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").text();
		id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").val();
		html2 = "";
		html2 += '<div class="control-label">';
		html2 += tipo_documento_seleccionado_nombre + ": ";
		html2 += "</div>";

		var hoy = new Date();
		var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
		var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
		var Tiempo = fecha + "" + hora;
		id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
		//var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

		var onclick = "borrarArchivo('" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "')";

		var html = "";
		html +=
			'<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			'">';
		html += '<div class="form-group">';
		html += '<div class="control-label">';
		html += tipo_documento_seleccionado_nombre + ": ";
		html += "</div>";
		var onchange =
			"file(event,'" +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			"', " +
			id_tipo_documento_seleccionado +
			", '" +
			tipo_documento_seleccionado_nombre +
			"')";
		html += '<div style="margin-top:10px;">';
		html +=
			'<input name="miarchivo[]" type="file" id="' +
			id_tipo_documento_seleccionado +
			"_" +
			id_nuevo_objeto_nuevo_anexo +
			'" class="col-md-11" onchange="' +
			onchange +
			'" style="padding: 0px 0px;"/>';
		html +=
			'<button class="btn btn-xs btn-danger col-md-1" style="width: 22px;" onclick="' +
			onclick +
			'"><i class="fa fa-trash-o"></i></button>';
		html += "</div>";
		html += "</div>";
		html += "</div>";
		if (tipo_contrato_id == "6") {
			$("#sec_nuevo_nuevos_anexos_listado_ca").append(html); // cargar el nuevo item
		}

		$("#modaltiposanexos_ca").modal("hide");
	} else {
		alertify.error("Seleccione el tipo de anexo", 5);
	}
}

function borrarArchivoAcuerdoConfidencialidad(id_anexo) {
	//Restamos el número de archivos
	contArchivos = contArchivos - 1;

	array_nuevos_files_anexos = array_nuevos_files_anexos.filter((item) => item.id_objeto !== id_anexo);
	$("div[name=" + id_anexo + "]").remove();
}

function sec_nuevo_cargar_tipos_anexos2() {
	limpiar_select_tipos_anexos();

	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();

	array_tabla_subdiarios = [];
	var data = {
		accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
		tipo_contrato_id: tipo_contrato_id,
	};
	auditoria_send({ proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos", data: data });
	$.ajax({
		url: "sys/get_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);

			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function (index, item) {
					array_tabla_subdiarios.push(item);
					$("#modal_nuevo_anexo_select_tipos2").append(
						'<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
					);
				});
				return false;
			}
		},
		error: function () {},
	});
}

function sec_nuevo_cargar_tipos_anexos2_acuerdo_confidencialidad() {
	limpiar_select_tipos_anexos();

	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ac").val();

	array_tabla_subdiarios = [];
	var data = {
		accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
		tipo_contrato_id: tipo_contrato_id,
	};
	auditoria_send({ proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos", data: data });
	$.ajax({
		url: "sys/get_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);

			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function (index, item) {
					array_tabla_subdiarios.push(item);
					$("#modal_nuevo_anexo_select_tipos2_ac").append(
						'<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
					);
				});
				return false;
			}
		},
		error: function () {},
	});
}

function sec_nuevo_cargar_tipos_anexos2_contrato_agente() {
	limpiar_select_tipos_anexos();

	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ca").val();

	array_tabla_subdiarios = [];
	var data = {
		accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
		tipo_contrato_id: tipo_contrato_id,
	};
	auditoria_send({ proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos", data: data });
	$.ajax({
		url: "sys/get_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);

			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function (index, item) {
					array_tabla_subdiarios.push(item);
					$("#modal_nuevo_anexo_select_tipos2_ca").append(
						'<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
					);
				});
				return false;
			}
		},
		error: function () {},
	});
}

function sec_nuevo_con_agregar_nuevo_tipo_archivo() {
	$("#sec_nuevo_con_agregar_nuevo_tipo_archivo").modal({ backdrop: "static", keyboard: false });
	$("#sec_nuevo_tipo_anexo_nombre").val("");
	setTimeout(function () {
		$("#sec_nuevo_tipo_anexo_nombre").focus();
	}, 500);
}

function sec_nuevo_con_agregar_nuevo_tipo_archivo_ac() {
	$("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ac").modal({ backdrop: "static", keyboard: false });
	$("#sec_nuevo_tipo_anexo_nombre_ac").val("");
	setTimeout(function () {
		$("#sec_nuevo_tipo_anexo_nombre_ac").focus();
	}, 500);
}

function sec_nuevo_con_agregar_nuevo_tipo_archivo_ca() {
	$("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ca").modal({ backdrop: "static", keyboard: false });
	$("#sec_nuevo_tipo_anexo_nombre_ca").val("");
	setTimeout(function () {
		$("#sec_nuevo_tipo_anexo_nombre_ca").focus();
	}, 500);
}

function guardarNuevoTipoAnexo() {
	if ($("#sec_nuevo_tipo_anexo_nombre").val() == "") {
		swal({
			title: "Ingrese el nombre del tipo de anexo nuevo",
			text: respuesta.error,
			html: true,
			type: "warning",
			closeOnConfirm: false,
			showCancelButton: false,
		});
		return false;
	}
	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();
	var anexo = $("#sec_nuevo_tipo_anexo_nombre").val();
	var data = {
		accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
		anexo: anexo,
		tipo_contrato_id: tipo_contrato_id,
	};

	auditoria_send({ proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo", data: data });
	$.ajax({
		url: "sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			console.log(resp);
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#sec_nuevo_con_agregar_nuevo_tipo_archivo").modal("hide");
				sec_nuevo_cargar_tipos_anexos2();
				setTimeout(function () {
                    $("#modal_nuevo_anexo_select_tipos2").val(respuesta.result).trigger('change');
               
                  }, 1500); 
				return false;
			}
		},
		error: function () {},
	});
}

function guardarNuevoTipoAnexo_ac() {
	if ($("#sec_nuevo_tipo_anexo_nombre_ac").val() == "") {
		swal({
			title: "Ingrese el nombre del tipo de anexo nuevo",
			text: respuesta.error,
			html: true,
			type: "warning",
			closeOnConfirm: false,
			showCancelButton: false,
		});
		return false;
	}
	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ac").val();
	var anexo = $("#sec_nuevo_tipo_anexo_nombre_ac").val();
	var data = {
		accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
		anexo: anexo,
		tipo_contrato_id: tipo_contrato_id,
	};

	auditoria_send({ proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo", data: data });
	$.ajax({
		url: "sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			console.log(resp);
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ac").modal("hide");
				sec_nuevo_cargar_tipos_anexos2_acuerdo_confidencialidad();
				return false;
			}
		},
		error: function () {},
	});
}

function guardarNuevoTipoAnexo_ca() {
	if ($("#sec_nuevo_tipo_anexo_nombre_ca").val() == "") {
		swal({
			title: "Ingrese el nombre del tipo de anexo nuevo",
			text: respuesta.error,
			html: true,
			type: "warning",
			closeOnConfirm: false,
			showCancelButton: false,
		});
		return false;
	}
	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ca").val();
	var anexo = $("#sec_nuevo_tipo_anexo_nombre_ca").val();
	var data = {
		accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
		anexo: anexo,
		tipo_contrato_id: tipo_contrato_id,
	};

	auditoria_send({ proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo", data: data });
	$.ajax({
		url: "sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			console.log(resp);
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ca").modal("hide");
				sec_nuevo_cargar_tipos_anexos2_contrato_agente();

				setTimeout(function () {
                    $("#modal_nuevo_anexo_select_tipos2_ca").val(respuesta.result).trigger('change');
               
                  }, 1500); 
				return false;
			}
		},
		error: function () {},
	});
}

function file(event, id, idtd, tdnombre) {
	var id_ = "#" + id;
	var id_tip_documento = idtd;
	let file = $(id_)[0].files[0];
	var nombre_archivo = file.name;
	var tamano_archivo = file.size;
	var extension = $(id_).val().replace(/^.*\./, "");

	var objeto = {
		id_objeto: id,
		nombre_archivo: nombre_archivo,
		tamano_archivo: tamano_archivo,
		extension: extension,
		id_tip_documento: id_tip_documento,
		tip_doc_nombre: tdnombre,
	};

	array_nuevos_files_anexos.push(objeto);
}

function limpiarSelectBanco() {
	$("#sec_con_nuevo_prov_banco").html("");
	$("#sec_con_nuevo_prov_banco").append('<option value="0"> - Seleccione - </option>');
}

function cargarBancos() {
	var data = {
		accion: "sec_con_nuevo_prov_cargar_bancos",
	};
	auditoria_send({ proceso: "sec_con_nuevo_prov_cargar_bancos", data: data });
	$.ajax({
		url: "sys/get_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			limpiarSelectBanco();
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function (ind, elem) {
					$("#sec_con_nuevo_prov_banco").append('<option value="' + elem.id + '"> ' + elem.nombre + " </option>");
				});

				return false;
			}
		},
		error: function () {},
	});
}

var rr_representantes = [];

function limpiar_input_nuevo_representante() {
	$("#repr_tipo_documento_id").val("0").trigger("change.select2");
	$("#dni_representante").val("");
	$("#nombre_representante").val("");
	$("#sec_con_nuev_prov_nro_cuenta_detraccion").val("");
	$("#sec_con_nuevo_prov_banco").val("");
	$("#sec_con_nuev_prov_nro_cuenta").val("");
	$("#sec_con_nuev_prov_nro_cci").val("");
	$("#sec_con_nuevo_prov_id_prov_hidden").val("");
	$("#sec_con_nuevo_prov_banco").val("0").trigger("change.select2");
}

function sec_con_nuevo_prov_agregar_prov() {
	var input_vacios = "";

	var tipo_documento_id = $("#repr_tipo_documento_id").val().trim();
	var dniRepresentante = $("#dni_representante").val().trim();
	var nombreRepresentante = $("#nombre_representante").val().trim();
	var nro_cuenta_detraccion = $("#sec_con_nuev_prov_nro_cuenta_detraccion").val().trim();
	var posee_banco = $("#sec_con_nuevo_repr_legal_posee_banco").val();
	var banco = $("#sec_con_nuevo_prov_banco").val();
	var banco_nombre = $("#sec_con_nuevo_prov_banco option:selected").text().trim();
	var nro_cuenta = $("#sec_con_nuev_prov_nro_cuenta").val().trim();
	var nro_cci = $("#sec_con_nuev_prov_nro_cci").val().trim();

	
	if (tipo_documento_id == 0) {
		alertify.error("Seleccione un tipo de documento", 8);
		$("#repr_tipo_documento_id").focus();
		$("#repr_tipo_documento_id").select2("open");
		return false;
	}else{
		var nombre_tipo_doc = '';
		switch (parseInt(tipo_documento_id)) {
			case 1: nombre_tipo_doc = 'DNI'; break;
			case 2: nombre_tipo_doc = 'RUC'; break;
			case 3: nombre_tipo_doc = 'Pasaporte'; break;
			case 4: nombre_tipo_doc = 'Carnet de Extranjeria'; break;
			default:break;
		}
	}

	if (tipo_documento_id == 1 && dniRepresentante.length != 8) {
		alertify.error("El DNI del representante legal debe de tener 8 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}else if(tipo_documento_id == 2 && dniRepresentante.length != 11){
		alertify.error("El RUC del representante legal debe de tener 11 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}else if(tipo_documento_id == 3 && dniRepresentante.length != 12){
		alertify.error("El Pasaporte del representante legal debe de tener 12 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}else if(tipo_documento_id == 4 && dniRepresentante.length != 12){
		alertify.error("El Carnet de Extranjeria del representante legal debe de tener 12 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}

	if (nombreRepresentante.length == 0) {
		alertify.error("Ingrese el Nombre Completo del Representante legal.", 8);
		$("#nombre_representante").focus();
		return false;
	}

	if (posee_banco == 0) {
		alertify.error("Seleccione si el representante legal posee cuenta bancaria.", 8);
		$("#sec_con_nuevo_repr_legal_posee_banco").focus();
		$("#sec_con_nuevo_repr_legal_posee_banco").select2("open");
		return false;
	}

	if (posee_banco == 1 && banco == 0) {
		alertify.error("Seleccione el banco del representante legal.", 8);
		$("#sec_con_nuevo_prov_banco").focus();
		$("#sec_con_nuevo_prov_banco").select2("open");
		return false;
	}

	if (posee_banco == 1 && nro_cuenta.length == 0 && nro_cci.length == 0) {
		alertify.error("Ingrese el número de cuenta o el número de CCI del representante legal.", 8);
		$("#sec_con_nuev_prov_nro_cuenta").focus();
		return false;
	}

	if (banco == 0) {
		banco_nombre = '';
	}

	var nro_filas = $("#sec_con_nuevo_prov_tabla_proveedores tr").length;
	var id_registro = nro_filas + "_" + dniRepresentante;

	var data_representantes = {
		id_registro: id_registro,
		tipo_documento_id: tipo_documento_id,
		nombre_tipo_doc: nombre_tipo_doc,
		dniRepresentante: dniRepresentante,
		nombreRepresentante: nombreRepresentante,
		nro_cuenta_detraccion: nro_cuenta_detraccion,
		banco: banco,
		banco_nombre: banco_nombre,
		nro_cuenta: nro_cuenta,
		nro_cci: nro_cci,
	};
	rr_representantes.push(data_representantes);
	limpiar_input_nuevo_representante();

	$("#sec_con_nuevo_prov_tabla_proveedores_sin_representantes").hide();
	$("#sec_con_nuevo_prov_tabla_proveedores").show();

	var onclick_editar_repr = "editar_nuev_repr('" + id_registro + "')";
	var onclick_eliminar_repr = "eliminar_nuev_repr('" + id_registro + "')";

	$("#sec_con_nuevo_prov_tabla_proveedores").append(
		"<tr>" +
			'<td style="display: none;" class="id_registro">' +
			id_registro +
			"</td>" +
			'<td class="tipoDocumentoRepresentante">' +
			nombre_tipo_doc +
			"</td>" +
			'<td class="dniRepresentante">' +
			dniRepresentante +
			"</td>" +
			'<td class="nombreRepresentante">' +
			nombreRepresentante +
			"</td>" +
			'<td class="nro_cuenta_detraccion">' +
			nro_cuenta_detraccion +
			"</td>" +
			'<td class="banco_nombre">' +
			banco_nombre +
			"</td>" +
			'<td class="nro_cuenta">' +
			nro_cuenta +
			"</td>" +
			'<td class="nro_cci">' +
			nro_cci +
			"</td>" +
			'<td><div class="file-select" id="src">' +
			'<input type="file" name="vigencia_nuevo_representante_' +
			id_registro +
			'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></div></td>' +
			"<td>" +
			'<input type="file" name="dni_nuevo_representante_' +
			id_registro +
			'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>' +
			'<td class="onclick_editar_repr"><button type="button" class="btn btn-sm btn-success" onclick="' +
			onclick_editar_repr +
			'"><i class="fa fa-edit"></i></button></td>' +
			'<td><button class="btn btn-sm btn-danger borrar_representante_legal"><i class="fa fa-close"></i></button></td>' +
			"</tr>"
	);

	$("#sec_con_nuevo_prov_banco").val(0).trigger('change');
	$("#sec_con_nuevo_repr_legal_posee_banco").val(0).trigger('change');
}

function editar_nuev_repr(id_nuev_prov) {
	$("#div_repr_legal_banco").show();
	$("#div_repr_legal_num_cuenta").show();
	$("#div_repr_legal_num_cci").show();
	$("#div_repr_legal_nota").show();

	var rr = rr_representantes.filter((w) => w.id_registro == id_nuev_prov);
	$.each(rr, function (ind, elem) {
		$("#repr_tipo_documento_id").val(elem.tipo_documento_id).trigger("change.select2");
		$("#dni_representante").val(elem.dniRepresentante);
		$("#nombre_representante").val(elem.nombreRepresentante);
		$("#sec_con_nuev_prov_nro_cuenta_detraccion").val(elem.nro_cuenta_detraccion);
		$("#sec_con_nuevo_prov_banco").val(elem.banco);
		$("#sec_con_nuev_prov_nro_cuenta").val(elem.nro_cuenta);
		$("#sec_con_nuev_prov_nro_cci").val(elem.nro_cci);

		$("#sec_con_nuevo_prov_id_prov_hidden").val(elem.id_registro);
		$("#sec_con_nuevo_prov_banco").val(elem.banco).trigger("change.select2");
	});

	$("#div_sec_con_nuevo_prov_editar_proveedor").show();
	$("#sec_con_nuevo_btn_nuevo_proveedor").hide();
}

function guardarActualizacionRepresentante() {
	var id_registro = $("#sec_con_nuevo_prov_id_prov_hidden").val();
	var tipo_documento_id = $("#repr_tipo_documento_id").val();
	var dniRepresentante = $("#dni_representante").val();
	var nombreRepresentante = $("#nombre_representante").val();
	var nro_cuenta_detraccion = $("#sec_con_nuev_prov_nro_cuenta_detraccion").val();
	var banco = $("#sec_con_nuevo_prov_banco").val();
	var banco_nombre = $("#sec_con_nuevo_prov_banco option:selected").text();
	var nro_cuenta = $("#sec_con_nuev_prov_nro_cuenta").val();
	var nro_cci = $("#sec_con_nuev_prov_nro_cci").val();
	var nuevo_id_registro = "";

	if (tipo_documento_id == 0) {
		alertify.error("Seleccione un tipo de documento", 8);
		$("#repr_tipo_documento_id").focus();
		$("#repr_tipo_documento_id").select2("open");
		return false;
	}else{
		var nombre_tipo_doc = '';
		switch (parseInt(tipo_documento_id)) {
			case 1: nombre_tipo_doc = 'DNI'; break;
			case 2: nombre_tipo_doc = 'RUC'; break;
			case 3: nombre_tipo_doc = 'Pasaporte'; break;
			case 4: nombre_tipo_doc = 'Carnet de Extranjeria'; break;
			default:break;
		}
	}

	if (tipo_documento_id == 1 && dniRepresentante.length != 8) {
		alertify.error("El DNI del representante legal debe de tener 8 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}else if(tipo_documento_id == 2 && dniRepresentante.length != 11){
		alertify.error("El RUC del representante legal debe de tener 11 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}else if(tipo_documento_id == 3 && dniRepresentante.length != 12){
		alertify.error("El Pasaporte del representante legal debe de tener 12 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}else if(tipo_documento_id == 4 && dniRepresentante.length != 12){
		alertify.error("El Carnet de Extranjeria del representante legal debe de tener 12 dígitos, no " + dniRepresentante.length + " dígitos.", 8);
		$("#dni_representante").focus();
		return false;
	}
	if (nombreRepresentante.length == 0) {
		alertify.error("Ingrese el Nombre Completo del Representante legal.", 8);
		$("#nombre_representante").focus();
		return false;
	}

	if (banco == 0) {
		banco_nombre = '';
	}

	$.each(rr_representantes, function (ind, elem) {
		nuevo_id_registro = ind + "_" + dniRepresentante;
		if (elem.id_registro == id_registro) {
			elem.id_registro = nuevo_id_registro;
			elem.tipo_documento_id = tipo_documento_id;
			elem.nombre_tipo_doc = nombre_tipo_doc;
			elem.dniRepresentante = dniRepresentante;
			elem.nombreRepresentante = nombreRepresentante;
			elem.nro_cuenta_detraccion = nro_cuenta_detraccion;
			elem.banco = banco;
			elem.banco_nombre = banco_nombre;
			elem.nro_cuenta = nro_cuenta;
			elem.nro_cci = nro_cci;
		}
	});
	limpiar_input_nuevo_representante();
	$("#div_sec_con_nuevo_prov_editar_proveedor").hide();
	$("#sec_con_nuevo_btn_nuevo_proveedor").show();

	$("#sec_con_nuevo_prov_tabla_proveedores tr").each(function (i) {
		if (i != 0) {
			var row = $(this);
			var id = row.find(".id_registro").text();
			if (id == id_registro) {
				var onclick_editar_repr = "editar_nuev_repr('" + nuevo_id_registro + "')";
				row.find(".id_registro").html(nuevo_id_registro);
				row.find(".tipoDocumentoRepresentante").html(nombre_tipo_doc);
				row.find(".dniRepresentante").html(dniRepresentante);
				row.find(".nombreRepresentante").html(nombreRepresentante);
				row.find(".nro_cuenta_detraccion").html(nro_cuenta_detraccion);
				row.find(".banco").html(banco);
				row.find(".banco_nombre").html(banco_nombre);
				row.find(".nro_cuenta").html(nro_cuenta);
				row.find(".nro_cci").html(nro_cci);
				row
					.find(".onclick_editar_repr")
					.html('<button class="btn btn-sm btn-success" onclick="' + onclick_editar_repr + '"><i class="fa fa-edit"></i></button>');
			}
		}
	});
}

function cancelarEdicionProveedor() {
	$("#div_sec_con_nuevo_prov_editar_proveedor").hide();
	$("#sec_con_nuevo_btn_nuevo_proveedor").show();
	limpiar_input_nuevo_representante();
}

$(document).on("click", ".borrar_representante_legal", function (event) {
	var this_tr = $(this);
	swal(
		{
			title: "Quitar Representante Legal",
			text: "¿Desea quitar el representante legal seleccionado?",
			type: "warning",
			showCancelButton: true,
			cancelButtonText: "No",
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Si",
			closeOnConfirm: false,
		},
		function (isConfirm) {
			if (isConfirm) {
				var id_registro_seleccionado = $(this).closest("tr").find(".id_registro").text();
				rr_representantes = rr_representantes.filter((w) => w.id_registro != id_registro_seleccionado);
				this_tr.parents("tr").remove();

				$("#div_sec_con_nuevo_prov_editar_proveedor").hide();
				$("#sec_con_nuevo_btn_nuevo_proveedor").show();
				limpiar_input_nuevo_representante();

				swal({
					title: "Listo!",
					text: "",
					type: "success",
				});
			} else {
				return false;
			}
		}
	);
});

// INICIO PROVEEDORES CONTRAPRESTACIÓN
var array_contraprestaciones_contrato_proveedor = [];

function sec_contrato_nuevo_proveedor_guardar_contraprestacion() {
	var proceso = "guardar_contraprestacion";
	var data = sec_contrato_nuevo_validar_campos_formulario_contraprestacion(proceso);

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: proceso, data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: proceso, data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// $('#modal_recargaweb').modal('hide');
				// swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_asignar_contraprestacion_al_contrato_proveedor(respuesta.result);
				sec_contrato_nuevo_resetear_formulario_nuevo_contraprestacion("new");
				setTimeout(function () {
					$("#alcance_servicio").focus();
				}, 250);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_eliminar_contraprestacion(contraprestacion_id) {
	const index = array_contraprestaciones_contrato_proveedor.indexOf(contraprestacion_id);
	if (index > -1) {
		array_contraprestaciones_contrato_proveedor.splice(index, 1);
	}
	sec_contrato_nuevo_actualizar_tabla_contraprestacion();
	sec_contrato_nuevo_resetear_formulario_nuevo_contraprestacion("new");
}

function sec_contrato_nuevo_validar_campos_formulario_contraprestacion(accion) {
	var contraprestacion_id = $("#contraprestacion_id_para_cambios").val();
	var moneda_id = $("#moneda_id").val();
	var subtotal_sin_parse = $("#subtotal").val().trim().replace(",", "");
	var igv_sin_parse = $("#igv").val().trim().replace(",", "");
	var monto_sin_parse = $("#monto").val().trim().replace(",", "");
	var subtotal = parseFloat(subtotal_sin_parse);
	var igv = parseFloat(igv_sin_parse);
	var monto = parseFloat(monto_sin_parse);
	var forma_pago_detallado = $("#forma_pago_detallado").val();
	var tipo_comprobante = $("#tipo_comprobante").val().trim();
	var plazo_pago = $("#plazo_pago").val().trim();

	if (parseInt(moneda_id) == 0) {
		alertify.error("Seleccione el tipo de moneda", 5);
		$("#moneda_id").focus();
		$("#moneda_id").select2("open");
		return false;
	}

	if (monto_sin_parse.length == 0) {
		alertify.error("Ingrese el monto bruto", 5);
		$("#monto").focus();
		return false;
	}

	if (subtotal_sin_parse.length == 0) {
		alertify.error("Ingrese el subtotal", 5);
		$("#subtotal").focus();
		return false;
	}

	if (igv_sin_parse.length == 0) {
		alertify.error("Ingrese el monto del IGV", 5);
		$("#igv").focus();
		return false;
	}

	if (parseInt(tipo_comprobante) == 0) {
		alertify.error("Seleccione tipo de comprobante a emitir", 5);
		$("#tipo_comprobante").focus();
		$("#tipo_comprobante").select2("open");
		return false;
	}

	if (plazo_pago.length == 0) {
		alertify.error("Ingrese el Plazo de Pago", 5);
		$("#plazo_pago").focus();
		return false;
	}

	if (forma_pago_detallado.length == 0) {
		alertify.error("Ingrese la forma de pago", 5);
		$("#forma_pago_detallado").focus();
		return false;
	}

	if (forma_pago_detallado.length < 5) {
		alertify.error("La forma de pago debe de ser detallada", 5);
		$("#forma_pago_detallado").focus();
		return false;
	}

	if (monto.toFixed(2) != (subtotal + igv).toFixed(2)) {
		alertify.error("La suma del Subtotal + el IGV no coincide con el Monto Bruto", 5);
		return false;
	}

	var data = {
		accion: accion,
		contraprestacion_id_para_cambios: contraprestacion_id,
		moneda_id: moneda_id,
		subtotal: subtotal,
		igv: igv,
		monto: monto,
		forma_pago_detallado: forma_pago_detallado,
		tipo_comprobante: tipo_comprobante,
		plazo_pago: plazo_pago,
	};

	return data;
}

function sec_contrato_nuevo_asignar_contraprestacion_al_contrato_proveedor(contraprestacion_id) {
	if (array_contraprestaciones_contrato_proveedor.includes(contraprestacion_id) === false) {
		array_contraprestaciones_contrato_proveedor.push(contraprestacion_id);
	}
	sec_contrato_nuevo_actualizar_tabla_contraprestacion();
}

function sec_contrato_nuevo_actualizar_tabla_contraprestacion() {
	var data = {
		accion: "obtener_contraprestaciones",
		contraprestacion_id: JSON.stringify(array_contraprestaciones_contrato_proveedor),
	};

	auditoria_send({ proceso: "obtener_contraprestaciones", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$("#div_tabla_contraprestaciones").html(respuesta.result);
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_editar_contraprestacion(contraprestacion_id) {
	sec_contrato_nuevo_resetear_formulario_nuevo_contraprestacion("edit");

	var data = {
		accion: "obtener_contraprestacion",
		contraprestacion_id: contraprestacion_id,
	};

	var array_contraprestacion = [];

	auditoria_send({ proceso: "obtener_contraprestacion", data: data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				array_contraprestacion.push(respuesta.result);
				$("#contraprestacion_id_para_cambios").val(array_contraprestacion[0][0].id);
				$("#moneda_id").val(array_contraprestacion[0][0].moneda_id).trigger("change");
				$("#subtotal").val(array_contraprestacion[0][0].subtotal);
				$("#igv").val(array_contraprestacion[0][0].igv);
				$("#monto").val(array_contraprestacion[0][0].monto);
				// $('#forma_pago').val(array_contraprestacion[0][0].forma_pago_id).trigger('change');
				$("#tipo_comprobante").val(array_contraprestacion[0][0].tipo_comprobante_id).trigger("change");
				$("#plazo_pago").val(array_contraprestacion[0][0].plazo_pago);
				$("#forma_pago_detallado").val(array_contraprestacion[0][0].forma_pago_detallado);

				setTimeout(function () {
					$("#tipo_comprobante").select2("close");
					$("#subtotal").focus();
				}, 250);

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_resetear_formulario_nuevo_contraprestacion(evento) {
	$("#subtotal").val("");
	$("#tipo_igv_id").val("0").trigger("change");
	$("#igv").val("");
	$("#monto").val("");
	$("#plazo_pago").val("");

	if (evento == "new") {
		$("#btn_agregar_contraprestacion").show();
		$("#btn_guardar_cambios_contraprestacion").hide();
		$("#btn_cancelar_cambios_contraprestacion").hide();
	} else if (evento == "edit") {
		$("#btn_agregar_contraprestacion").hide();
		$("#btn_guardar_cambios_contraprestacion").show();
		$("#btn_cancelar_cambios_contraprestacion").show();
	}
}

function sec_contrato_nuevo_proveedor_guardar_cambios_contraprestacion() {
	var proceso = "guardar_cambios_contraprestacion";

	var data = sec_contrato_nuevo_validar_campos_formulario_contraprestacion(proceso);

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: proceso, data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: proceso, data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_actualizar_tabla_contraprestacion();
				sec_contrato_nuevo_resetear_formulario_nuevo_contraprestacion("new");
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_proveedor_cancelar_guardar_cambios_contraprestacion() {
	sec_contrato_nuevo_resetear_formulario_nuevo_contraprestacion("new");
}

function sec_contrato_nuevo_calcular_subtotal_y_igv(tipo) {
	var monto = $("#monto").val().trim().replace(",", "");
	var subtotal = 0;
	var igv = 0;

	if (monto != "") {
		monto = parseFloat(monto);
		subtotal = monto;

		if (tipo == "1") {
			subtotal = monto / 1.18;
			igv = monto - subtotal;
		}
	}

	$("#subtotal").val(subtotal.toFixed(2));
	$("#igv").val(igv.toFixed(2));

	$("#subtotal").blur();
	$("#igv").blur();
}

// FIN PROVEEDORES CONTRAPRESTACIÓN

function sec_contrato_nuevo_calcular_monto_a_pagar() {
	var tipo_moneda_renta_pactada = $("#contrato_tipo_moneda_renta_pactada").val().trim();
	var monto_renta = $("#contrato_monto_renta").val().trim();
	var impuesto_a_la_renta_id = $("#contrato_impuesto_a_la_renta").val();
	var impuesto_a_la_renta_carta_de_instruccion_id = $("#contrato_impuesto_a_la_renta_carta_de_instruccion_id").val();

	if (
		tipo_moneda_renta_pactada == 0 ||
		monto_renta == "" ||
		impuesto_a_la_renta_id == 0 ||
		impuesto_a_la_renta_id == 4 ||
		impuesto_a_la_renta_carta_de_instruccion_id == 0
	) {
		$("#div_detalle_del_pago").html("");
		return false;
	}

	var proceso = "calcular_monto_a_pagar_segun_impuesto_a_la_renta";

	var data = {
		accion: proceso,
		tipo_moneda_renta_pactada: tipo_moneda_renta_pactada,
		monto_renta: monto_renta,
		impuesto_a_la_renta_id: impuesto_a_la_renta_id,
		impuesto_a_la_renta_carta_de_instruccion_id: impuesto_a_la_renta_carta_de_instruccion_id,
	};

	auditoria_send({ proceso: proceso, data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: data,
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: proceso, data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				//swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				var ir_detalle = JSON.parse(respuesta.result);

				var table = '<table class="table table-bordered table-hover" style="font-size:10px; margin-top: 10px; margin-bottom: 0px;">';
				table += "<tbody>";
				table += "<tr>";
				table += '<td style="width:120px;font-weight: bold;">Impuesto a la renta</td>';
				table += '<td style="text-align: right;">' + ir_detalle.impuesto_a_la_renta + "</td>";
				table += "</tr>";
				table += "<tr>";
				table += '<td style="width:120px;font-weight: bold;">Renta Bruta</td>';
				table += '<td style="text-align: right;">' + ir_detalle.renta_bruta + "</td>";
				table += "</tr>";
				table += "<tr>";
				table += '<td style="width:120px;font-weight: bold;">Renta a pagar</td>';
				table += '<td style="text-align: right;">' + ir_detalle.renta_neta + "</td>";
				table += "</tr>";
				table += "<tr>";
				table += '<td style="width:120px;font-weight: bold;">Detalle</td>';
				table += '<td style="text-align: right;">' + ir_detalle.detalle + "</td>";
				table += "</tr>";
				table += "</tbody></table>";
				$("#div_detalle_del_pago").html(table);
				return false;
			}
		},
		error: function () {},
	});
}

///ACUERDO DE CONFIDENCIALIDAD
function sec_con_nuevo_prov_agregar_prov_ac() {
	var input_vacios = "";

	var dniRepresentante = $("#dni_representante_ac").val();
	if (dniRepresentante.length != 8) {
		swal({ title: "DNI debe tener 8 dígitos", text: "", type: "warning", timer: 3000, closeOnConfirm: true }, function () {
			swal.close();
		});
		return false;
	}
	var nombreRepresentante = $("#nombre_representante_ac").val();

	if ($.trim(dniRepresentante) == "") {
		input_vacios += " - DNI del Representante";
	}
	if ($.trim(nombreRepresentante) == "") {
		input_vacios += " - Nombre del Representante";
	}

	if ($.trim(input_vacios) != "") {
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}

	var nro_filas = $("#sec_con_nuevo_prov_tabla_proveedores_ac tr").length;
	var id_registro = nro_filas + "_" + dniRepresentante;

	var data_representantes = {
		id_registro: id_registro,
		dniRepresentante: dniRepresentante,
		nombreRepresentante: nombreRepresentante,
		nro_cuenta_detraccion: "",
		banco: 0,
		banco_nombre: "",
		nro_cuenta: "",
		nro_cci: "",
	};
	rr_representantes.push(data_representantes);
	limpiar_input_nuevo_representante_ac();

	var onclick_editar_repr = "editar_nuev_repr_ac('" + id_registro + "')";

	$("#sec_con_nuevo_prov_tabla_proveedores_ac").append(
		"<tr>" +
			'<td style="display: none;" class="id_registro">' +
			id_registro +
			"</td>" +
			'<td class="dniRepresentante">' +
			dniRepresentante +
			"</td>" +
			'<td class="nombreRepresentante">' +
			nombreRepresentante +
			"</td>" +
			'<td><div class="file-select" id="src">' +
			'<input type="file" name="vigencia_nuevo_representante_ac_' +
			id_registro +
			'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></div></td>' +
			"<td>" +
			'<input type="file" name="dni_nuevo_representante_ac_' +
			id_registro +
			'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>' +
			'<td class="onclick_editar_repr"><button type="button" class="btn btn-sm btn-success" onclick="' +
			onclick_editar_repr +
			'"><i class="fa fa-edit"></i></button></td>' +
			'<td><button type="button" class="btn btn-sm btn-danger borrar_representante_legal_ac"><i class="fa fa-close"></i></button></td>' +
			"</tr>"
	);
}

function editar_nuev_repr_ac(id_nuev_prov) {
	var rr = rr_representantes.filter((w) => w.id_registro == id_nuev_prov);
	$.each(rr, function (ind, elem) {
		$("#dni_representante_ac").val(elem.dniRepresentante);
		$("#nombre_representante_ac").val(elem.nombreRepresentante);
		// $('#sec_con_nuev_prov_nro_cuenta_detraccion').val(elem.nro_cuenta_detraccion);
		// $('#sec_con_nuevo_prov_banco').val(elem.banco);
		// $('#sec_con_nuev_prov_nro_cuenta').val(elem.nro_cuenta);
		// $('#sec_con_nuev_prov_nro_cci').val(elem.nro_cci);

		$("#sec_con_nuevo_prov_id_prov_hidden_ac").val(elem.id_registro);
		// $('#sec_con_nuevo_prov_banco').val(elem.banco).trigger('change.select2');
	});

	$("#div_sec_con_nuevo_prov_editar_proveedor_ac").show();
	$("#sec_con_nuevo_btn_nuevo_proveedor_ac").hide();
}

function guardarActualizacionRepresentante_ac() {
	var id_registro = $("#sec_con_nuevo_prov_id_prov_hidden_ac").val();
	var dniRepresentante = $("#dni_representante_ac").val();
	var nombreRepresentante = $("#nombre_representante_ac").val();
	var nuevo_id_registro = "";
	$.each(rr_representantes, function (ind, elem) {
		nuevo_id_registro = ind + "_" + dniRepresentante;
		if (elem.id_registro == id_registro) {
			elem.id_registro = nuevo_id_registro;
			elem.dniRepresentante = dniRepresentante;
			elem.nombreRepresentante = nombreRepresentante;
			elem.nro_cuenta_detraccion = "";
			elem.banco = 0;
			elem.banco_nombre = "";
			elem.nro_cuenta = "";
			elem.nro_cci = "";
		}
	});
	limpiar_input_nuevo_representante_ac();
	$("#div_sec_con_nuevo_prov_editar_proveedor_ac").hide();
	$("#sec_con_nuevo_btn_nuevo_proveedor_ac").show();

	$("#sec_con_nuevo_prov_tabla_proveedores_ac tr").each(function (i) {
		if (i != 0) {
			var row = $(this);
			var id = row.find(".id_registro").text();
			if (id == id_registro) {
				var onclick_editar_repr = "editar_nuev_repr_ac('" + nuevo_id_registro + "')";
				row.find(".id_registro").html(nuevo_id_registro);
				row.find(".dniRepresentante").html(dniRepresentante);
				row.find(".nombreRepresentante").html(nombreRepresentante);
				// row.find(".nro_cuenta_detraccion").html('');
				// row.find(".banco").html('');
				// row.find(".banco_nombre").html('');
				// row.find(".nro_cuenta").html('');
				// row.find(".nro_cci").html('');
				row
					.find(".onclick_editar_repr")
					.html(
						'<button type="button" class="btn btn-sm btn-success" onclick="' + onclick_editar_repr + '"><i class="fa fa-edit"></i></button>'
					);
			}
		}
	});
}

function cancelarEdicionProveedor_ac() {
	$("#div_sec_con_nuevo_prov_editar_proveedor_ac").hide();
	$("#sec_con_nuevo_btn_nuevo_proveedor_ac").show();
	limpiar_input_nuevo_representante_ac();
}

$(document).on("click", ".borrar_representante_legal_ac", function (event) {
	var this_tr = $(this);
	swal(
		{
			title: "Quitar Representante Legal",
			text: "¿Desea quitar el representante legal seleccionado?",
			type: "warning",
			showCancelButton: true,
			cancelButtonText: "No",
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Si",
			closeOnConfirm: false,
		},
		function (isConfirm) {
			if (isConfirm) {
				var id_registro_seleccionado = $(this).closest("tr").find(".id_registro").text();
				rr_representantes = rr_representantes.filter((w) => w.id_registro != id_registro_seleccionado);
				this_tr.parents("tr").remove();

				$("#div_sec_con_nuevo_prov_editar_proveedor_ac").hide();
				$("#sec_con_nuevo_btn_nuevo_proveedor_ac").show();
				limpiar_input_nuevo_representante_ac();

				swal({
					title: "Listo!",
					text: "",
					type: "success",
				});
			} else {
				return false;
			}
		}
	);
});

function limpiar_input_nuevo_representante_ac() {
	$("#dni_representante_ac").val("");
	$("#nombre_representante_ac").val("");
	$("#sec_con_nuevo_prov_id_prov_hidden_ac").val("");
	// $('#sec_con_nuev_prov_nro_cuenta_detraccion').val('');
	// $('#sec_con_nuevo_prov_banco').val('');
	// $('#sec_con_nuev_prov_nro_cuenta').val('');
	// $('#sec_con_nuev_prov_nro_cci').val('');

	// $('#sec_con_nuevo_prov_banco').val('0').trigger('change.select2');
}

// INICIO INCREMENTOS
function sec_contrato_nuevo_arrendamiento_modal_agregar_incrementos(id_contrato) {
	$("#contrato_id_temporal").val(id_contrato);
	sec_contrato_nuevo_arrendamiento_resetear_formulario_nuevo_incremento("new");
	$("#modal_adenda_agregar_incrementos").modal({ backdrop: "static", keyboard: false });
	setTimeout(function () {
		$("#contrato_adenda_incrementos_monto_o_porcentaje").focus();
	}, 500);
}

function sec_contrato_nuevo_arrendamiento_resetear_formulario_nuevo_incremento(evento) {
	$("#frm_adenda_incremento")[0].reset();
	$("#contrato_adenda_incrementos_en").val("0").trigger("change");
	$("#contrato_adenda_incrementos_continuidad").val("0").trigger("change");
	$("#contrato_adenda_incrementos_a_partir_de_año").val("0").trigger("change");

	if (evento == "new") {
		$("#modal_adenda_incremento_titulo").html("Registrar Incremento");
		$("#btn_adenda_agregar_incremento").show();
		$("#btn_adenda_guardar_cambios_incremento").hide();
	} else if (evento == "edit") {
		$("#modal_adenda_incremento_titulo").html("Editar Incremento");
		$("#btn_adenda_agregar_incremento").hide();
		$("#btn_adenda_guardar_cambios_incremento").show();
	}

	setTimeout(function () {
		$("#contrato_adenda_incrementos_en").select2("close");
		$("#contrato_adenda_incrementos_continuidad").select2("close");
		$("#contrato_adenda_incrementos_a_partir_de_año").select2("close");
	}, 200);
}

function sec_contrato_nuevo_arrendamiento_solicitud_guardar_incremento() {
	var proceso = "guardar_incremento_adenda";
	var data = sec_contrato_nuevo_arrendamiento_validar_campos_formulario_incremento(proceso);
	if (!data) {
		return false;
	}
	// auditoria_send({ "proceso": proceso, "data": data });

	$.ajax({
		url: "/sys/set_contrato_detalle_solicitud.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: proceso, data: respuesta });

			if (parseInt(respuesta.http_code) == 400) {
				swal("Aviso", respuesta.error, "warning");
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				if (respuesta.error == "") {
					$("#modal_adenda_agregar_incrementos").modal("hide");
					sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda(
						"incrementos",
						{ id: respuesta.id, nuevo_valor: respuesta.nuevo_valor },
						"modal_adenda_agregar_incrementos"
					);
					// location.reload(true);
				} else {
					swal("Aviso", respuesta.error, "warning");
				}

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_arrendamiento_validar_campos_formulario_incremento(accion) {
	var contrato_id = $("#contrato_id_temporal").val();
	var id_incremento_para_cambios = $("#contrato_adenda_incrementos_id_incremento_para_cambios").val();
	var incremento_monto_o_porcentaje = $("#contrato_adenda_incrementos_monto_o_porcentaje").val();
	var incrementos_en = $("#contrato_adenda_incrementos_en").val();
	var incrementos_continuidad = $("#contrato_adenda_incrementos_continuidad").val().trim();
	var incrementos_a_partir_de_año = $("#contrato_adenda_incrementos_a_partir_de_año").val();

	var incrementos_continuidad_text = "";
	var data = $("#contrato_adenda_incrementos_continuidad").select2("data");
	if (data) {
		incrementos_continuidad_text = data[0].text;
	}

	var incrementos_a_partir_de_año_text = "";
	var data = $("#contrato_adenda_incrementos_a_partir_de_año").select2("data");
	if (data) {
		incrementos_a_partir_de_año_text = data[0].text;
	}

	if (incremento_monto_o_porcentaje.length < 1) {
		alertify.error("Ingrese el valor", 5);
		$("#contrato_adenda_incrementos_monto_o_porcentaje").focus();
		return false;
	}

	if (parseInt(incrementos_en) == 0) {
		alertify.error("Seleccione el tipo de valor", 5);
		$("#contrato_adenda_incrementos_en").focus();
		return false;
	}

	if (parseInt(incrementos_en) == 2 && incremento_monto_o_porcentaje.length > 5) {
		alertify.error("El incremento no puede ser mayor al 100%", 5);
		$("#contrato_adenda_incrementos_en").focus();
		return false;
	}

	if (parseInt(incrementos_continuidad) == 0) {
		alertify.error("Seleccione el tipo de continuidad", 5);
		$("#contrato_adenda_incrementos_continuidad").focus();
		return false;
	}

	if (parseInt(incrementos_a_partir_de_año) == 0 && parseInt(incrementos_continuidad) != 3) {
		alertify.error("Seleccione el año del inicio del incremento", 5);
		$("#contrato_adenda_incrementos_a_partir_de_año").focus();
		return false;
	}

	var data = {
		accion: accion,
		contrato_id: contrato_id,
		id_incremento_para_cambios: id_incremento_para_cambios,
		incremento_monto_o_porcentaje: incremento_monto_o_porcentaje,
		incrementos_en: incrementos_en,
		incrementos_continuidad: incrementos_continuidad,
		incrementos_a_partir_de_año: incrementos_a_partir_de_año,
		incrementos_continuidad_text: incrementos_continuidad_text,
		incrementos_a_partir_de_año_text: incrementos_a_partir_de_año_text,
	};

	return data;
}

function sec_contrato_detalle_solicitud_obtener_incremento_para_editar(incremento_id) {
	$("#modal_adenda_agregar_incrementos").modal("show");

	sec_contrato_nuevo_arrendamiento_resetear_formulario_nuevo_incremento("edit");

	var data = {
		accion: "obtener_incrementos",
		incremento_id: incremento_id,
	};

	var array_incrementos = [];

	auditoria_send({ proceso: "obtener_incrementos", data: data });
	$.ajax({
		url: "/sys/set_contrato_detalle_solicitud.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				array_incrementos.push(respuesta.result);
				$("#contrato_incrementos_id_incremento_para_cambios").val(array_incrementos[0][0].id);
				$("#contrato_incrementos_monto_o_porcentaje").val(array_incrementos[0][0].valor);
				$("#contrato_incrementos_en").val(array_incrementos[0][0].tipo_valor_id).trigger("change");
				$("#contrato_incrementos_continuidad").val(array_incrementos[0][0].tipo_continuidad_id).trigger("change");
				$("#contrato_incrementos_a_partir_de_año").val(array_incrementos[0][0].a_partir_del_año).trigger("change");

				setTimeout(function () {
					$("#contrato_incrementos_en").select2("close");
					$("#contrato_incrementos_continuidad").select2("close");
					$("#contrato_incrementos_a_partir_de_año").select2("close");
					$("#contrato_incrementos_monto_o_porcentaje").focus();
				}, 200);

				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_detalle_solicitud_guardar_cambios_incremento() {
	var data = sec_contrato_nuevo_arrendamiento_validar_campos_formulario_incremento("guardar_cambios_incremento");

	if (!data) {
		return false;
	}

	auditoria_send({ proceso: "guardar_cambios_incremento", data: data });

	$.ajax({
		url: "/sys/set_contrato_detalle_solicitud.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			//  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ proceso: "guardar_cambios_incremento", data: respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				// swal('Aviso', respuesta.status, 'warning');
				// listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				if (respuesta.error == "") {
					$("#modal_agregar_incrementos").modal("hide");
					location.reload(true);
				} else {
					swal("Aviso", respuesta.error, "warning");
				}

				return false;
			}
		},
		error: function () {},
	});
}
// FIN INCREMENTOS

// INICIO CHANGE INCREMENTOS
$("#contrato_adenda_incrementos_monto_o_porcentaje").on({
	focus: function (event) {
		$(event.target).select();
	},
	change: function (event) {
		if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
			$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
			$(event.target).val(function (index, value) {
				return value
					.replace(/\D/g, "")
					.replace(/([0-9])([0-9]{2})$/, "$1.$2")
					.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
			});
		} else {
			$(event.target).val("0.00");
		}
	},
});

$("#contrato_adenda_incrementos_en").change(function () {
	$("#contrato_adenda_incrementos_en option:selected").each(function () {
		incrementos_en = $(this).val();
		if (incrementos_en != 0) {
			setTimeout(function () {
				$("#contrato_adenda_incrementos_continuidad").select2("open");
			}, 200);
		}
	});
});

$("#contrato_adenda_incrementos_continuidad").change(function () {
	$("#contrato_adenda_incrementos_continuidad option:selected").each(function () {
		continuidad_id = $(this).val();

		if (continuidad_id == 3){
			$("#titulo_adenda_incremento_a_partir").html('');
			$("#titulo_adenda_incremento_a_partir").hide();
			$("#td_contrato_adenda_incrementos_a_partir_de_año").hide();
		} else {
			if (continuidad_id == 1) {
				$("#titulo_adenda_incremento_a_partir").html('El');
			} else if (continuidad_id == 2){
				$("#titulo_adenda_incremento_a_partir").html('A partir del');
			} 

			$("#titulo_adenda_incremento_a_partir").show();
			$("#td_contrato_adenda_incrementos_a_partir_de_año").show();

			setTimeout(function() {
				$('#contrato_adenda_incrementos_a_partir_de_año').select2('open');
			}, 200);
		}

	});
});	
// FIN CHANGE INCREMENTOS






///INICIO NUEVOS CAMBIOS ADENDA DE PROVEEDOR
function sec_contrato_nuevo_proveedor_modal_ap(id_contrato,accion) {
	
	$('#modalNuevoProveedor_ap').modal('show');
	$('#modal_nuevo_proveedor_ap_contrato_id').val(id_contrato);
	$('#modal_nuevo_proveedor_ap_accion').val(accion);
	
	sec_contrato_nuevo_resetear_formulario_nuevo_proveedor_ap('adenda');
}

function sec_contrato_nuevo_resetear_formulario_nuevo_proveedor_ap(evento){
	$('#frm_nuevo_proveedor_ap')[0].reset();
	$('#div_modal_propietario_representante_legal_ap').hide();
	$('#div_modal_propietario_num_partida_registral_ap').hide();
	if (evento == 'adenda') {
		$('#modal_nuevo_proveedor_titulo_ap').html('Adenda - Registrar Propietario');
		$('#btn_agregar_propietario_ap').show();
		$('#btn_guardar_cambios_propietario_ap').hide();
	
		$('#div_modal_propietario_contacto_nombre_ap').hide();
		$('#div_modal_propietario_persona_contacto_ap').show();
	}
}

function sec_contrato_nuevo_guardar_nuevo_representante_legal_ap(){
	var contrato_id = $('#modal_nuevo_proveedor_ap_contrato_id').val();

	var dniRepresentante = $('#sec_con_ap_dni_representante').val();
	if(dniRepresentante.length != 8){
		alertify.error("DNI debe tener 8 dígitos", 8);
		return false;
	}
	var nombreRepresentante = $('#sec_con_ap_nombre_representante').val();
	var banco = $('#sec_con_ap_sec_con_nuevo_prov_banco').val();
	var banco_nombre = $('#sec_con_ap_sec_con_nuevo_prov_banco option:selected').text();
	var nro_cuenta = $('#sec_con_ap_sec_con_nuev_prov_nro_cuenta').val();
	var nro_cci = $('#sec_con_ap_sec_con_nuev_prov_nro_cci').val();
	var input_vacios = "";
	if($.trim(dniRepresentante) == "") { input_vacios += " - DNI del Representante"; }
	if($.trim(nombreRepresentante) == "") { input_vacios += " - Nombre del Representante"; }
	if($.trim(banco) == 0) { input_vacios += " - Banco"; }
	if($.trim(nro_cuenta) == "" && $.trim(nro_cci) == "") { input_vacios += " - Nro Cuenta o CCI"; }

	if($.trim(input_vacios) != ""){
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}

	var form_data = new FormData($("#frm_nuevo_proveedor_ap")[0]);
	form_data.append("accion","guardar_adenda_detalle_nuevos_registros");
	form_data.append("tabla","representante_legal");
	form_data.append("contrato_id" , contrato_id);
	form_data.append("dniRepresentante", dniRepresentante);
	form_data.append("nombreRepresentante", nombreRepresentante);
	form_data.append("banco", banco);
	form_data.append("nro_cuenta", nro_cuenta);
	form_data.append("nro_cci", nro_cci);
	loading(true);
	auditoria_send({ "proceso": "guardar_adenda_detalle_nuevos_registros", "data": form_data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			var respuesta = JSON.parse(response);
			auditoria_send({ "proceso": "guardar_adenda_detalle_nuevos_registros", "data": respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_asignar_detalle_a_la_adenda(respuesta.result, 'modalNuevoProveedor_ap');
			}
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
}

function sec_contrato_nuevo_propiertario_ap() {
	var contrato_id = $('#modal_nuevo_proveedor_ap_contrato_id').val();
	var tipo_persona = $('#modal_propietario_tipo_persona_ap').val();
	var nombre = $('#modal_propietario_nombre_ap').val().trim();
	var tipo_docu = $('#modal_propietario_tipo_docu_ap').val();
	var num_docu = $('#modal_propietario_num_docu_ap').val().trim();
	var num_ruc = $('#modal_propietario_num_ruc_ap').val().trim();
	var direccion = $('#modal_propietario_direccion_ap').val().trim();
	var representante_legal = $('#modal_propietario_representante_legal_ap').val().trim();
	var num_partida_registral = $('#modal_propietario_num_partida_registral_ap').val();
	var tipo_persona_contacto = $('#modal_propietario_tipo_persona_contacto_ap').val();
	var contacto_nombre = $('#modal_propietario_contacto_nombre_ap').val().trim();
	var contacto_telefono = $('#modal_propietario_contacto_telefono_ap').val();
	var contacto_email = $('#modal_propietario_contacto_email_ap').val().trim();

	if (parseInt(tipo_persona) == 0) {
		alertify.error('Seleccione el tipo de persona',5);
		$("#modal_propietario_tipo_persona_ap").focus();
		$('#modal_propietario_tipo_persona_ap').select2('open');
		return false;
	}

	if (nombre.length < 6) {
		alertify.error('Ingrese el nombre completo del propietario',5);
		$("#modal_propietario_nombre").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 0) {
		alertify.error('Seleccione el tipo de documento de identidad',5);
		$("#modal_propietario_tipo_docu_ap").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 1 && num_docu.length != 8) {
		alertify.error('El número de DNI debe tener 8 dígitos, no ' + num_docu.length  + ' dígitos',5);
		$("#modal_propietario_num_docu_ap").focus();
		return false;
	}

	if (num_ruc.length != 11) {
		alertify.error('El número de RUC debe tener 11 dígitos, no ' + num_ruc.length  + ' dígitos',5);
		$("#modal_propietario_num_ruc_ap").focus();
		return false;
	}

	if (direccion.length < 10) {
		alertify.error('Ingrese el dirección completa del propietario',5);
		$("#modal_propietario_direccion_ap").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 2 && representante_legal.length == 0) {
		alertify.error('Ingrese el representante legal',5);
		$("#modal_propietario_representante_legal_ap").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 2 && num_partida_registral.length == 0) {
		alertify.error('Ingrese el número de la Partida Registral de la empresa',5);
		$("#modal_propietario_num_partida_registral_ap").focus();
		return false;
	}

	if (parseInt(tipo_persona_contacto) == 0) {
		alertify.error('Seleccione el tipo de persona contacto',5);
		$("#modal_propietario_tipo_persona_contacto_ap").focus();
		return false;
	}

	if (parseInt(tipo_persona_contacto) == 2 && contacto_nombre.length < 1) {
		alertify.error('Ingrese el nombre del contacto',5);
		$("#modal_propietario_contacto_nombre_ap").focus();
		return false;
	}
	
	// if (contacto_nombre.length < 1) {
	// 	alertify.error('Ingrese el nombre del contacto',5);
	// 	$("#modal_propietario_contacto_nombre_ap").focus();
	// 	return false;
	// }

	// if (contacto_telefono.length < 8) {
	// 	alertify.error('Ingrese el número telefónico del contaco',5);
	// 	$("#modal_propietario_contacto_telefono_ap").focus();
	// 	return false;
	// }

	if (contacto_email.length > 0 && !sec_contrato_nuevo_es_email_valido(contacto_email)) {
		alertify.error('El formato del correo electrónico es incorrecto',5);
		$("#modal_propietario_contacto_email_ap").focus();
		return false;
	}	

	var accion = 'guardar_adenda_detalle_nuevos_registros';
	
	var data = {
		"accion": accion,
		"tabla": "propietario",
		"contrato_id": contrato_id,
		"tipo_persona": tipo_persona,
		"nombre": nombre,
		"tipo_docu": tipo_docu,
		"num_docu": num_docu,
		"num_ruc": num_ruc,
		"direccion": direccion,
		"representante_legal": representante_legal,
		"num_partida_registral": num_partida_registral,
		"tipo_persona_contacto": tipo_persona_contacto,
		"contacto_nombre": contacto_nombre,
		"contacto_telefono": contacto_telefono,
		"contacto_email": contacto_email
	}

	auditoria_send({ "proceso": "guardar_detalle_adenda_nuevos_registros", "data": data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)

			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "guardar_detalle_adenda_nuevos_registros", "data": respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_asignar_detalle_a_la_adenda(respuesta.result, 'modalNuevoProveedor_ap');
			}
			
		},
		error: function() {}
	});
}

function sec_contrato_nuevo_contraprestacion_modal_ap(id_contrato,accion) {
	
	$('#modalNuevoContraprestacion_ap').modal('show');
	$('#modal_nuevo_contraprestacion_ap_contrato_id').val(id_contrato);
	$('#modal_nuevo_contraprestacion_ap_accion').val(accion);
	
	sec_contrato_nuevo_obtener_opciones("obtener_monedas",$("[name='modal_cp_moneda_id']"));
	sec_contrato_nuevo_obtener_opciones("obtener_tipo_comprobante",$("[name='modal_cp_tipo_comprobante']"));
}

function sec_contrato_nuevo_calcular_subtotal_y_igv_modal(tipo)
{
	var monto = $('#modal_cp_monto').val().trim().replace(',', '');
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

	$('#modal_cp_subtotal').val(subtotal.toFixed(2));
	$('#modal_cp_igv').val(igv.toFixed(2));

	$('#modal_cp_subtotal').blur();
	$('#modal_cp_igv').blur();
}

function sec_contrato_nuevo_contraprestacion_ap() {
	var contrato_id = $('#modal_nuevo_contraprestacion_ap_contrato_id').val();
	var moneda_id = $('#modal_cp_moneda_id').val();
	var monto = $('#modal_cp_monto').val().trim();
	var tipo_igv_id = $('#modal_cp_tipo_igv_id').val();
	var subtotal = $('#modal_cp_subtotal').val().trim();
	var igv = $('#modal_cp_igv').val().trim();
	var forma_pago = $('#modal_cp_forma_pago').val();
	var tipo_comprobante = $('#modal_cp_tipo_comprobante').val().trim();
	var plazo_pago = $('#modal_cp_plazo_pago').val();
	var forma_pago_detallado = $('#modal_cp_forma_pago_detallado').val();

	if (parseInt(moneda_id) == 0) {
		alertify.error('Seleccione un tipo de moneda',5);
		$("#modal_cp_moneda_id").focus();
		$('#modal_cp_moneda_id').select2('open');
		return false;
	}

	if (monto == "") {
		alertify.error('Ingrese un monto',5);
		$("#modal_cp_monto").focus();
		return false;
	}

	if (parseInt(tipo_igv_id) == 0) {
		alertify.error('Seleccione el IGV',5);
		$("#modal_cp_tipo_igv_id").focus();
		$('#modal_cp_tipo_igv_id').select2('open');
		return false;
	}

	if (subtotal == "") {
		alertify.error('Ingrese un subtotal',5);
		$("#modal_cp_subtotal").focus();
		return false;
	}

	if (igv == "") {
		alertify.error('Ingrese un IGV',5);
		$("#modal_cp_igv").focus();
		return false;
	}

	if (parseInt(tipo_comprobante) == 0) {
		alertify.error('Seleccione el tipo de comprobante',5);
		$("#modal_cp_tipo_comprobante").focus();
		$('#modal_cp_tipo_comprobante').select2('open');
		return false;
	}
	
	if (plazo_pago == "") {
		alertify.error('Ingrese un plazo de pago',5);
		$("#modal_cp_plazo_pago").focus();
		return false;
	}

	if (forma_pago_detallado == "") {
		alertify.error('Ingrese una forma de pago',5);
		$("#modal_cp_forma_pago_detallado").focus();
		return false;
	}


	var accion = 'guardar_adenda_detalle_nuevos_registros';
	
	var data = {
		"accion": accion,
		"tabla": "contraprestacion",
		"contrato_id": contrato_id,
		"moneda_id" : moneda_id,
		"monto" : monto,
		"tipo_igv_id" : tipo_igv_id,
		"subtotal" : subtotal,
		"igv" : igv,
		"forma_pago" : forma_pago,
		"tipo_comprobante" : tipo_comprobante,
		"plazo_pago" : plazo_pago,
		"forma_pago_detallado" : forma_pago_detallado,
	}

	auditoria_send({ "proceso": "guardar_detalle_adenda_nuevos_registros", "data": data });

	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)

			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "guardar_detalle_adenda_nuevos_registros", "data": respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_asignar_detalle_a_la_adenda(respuesta.result, 'modalNuevoContraprestacion_ap');
			}
			
		},
		error: function() {}
	});
}
///FIN NUEVOS CAMBIOS ADENDA DE PROVEEDOR


// INICIO CAMBIOS ADENDA PROVEEDOR
$("#modal_propietario_tipo_persona_ap").change(function () {
	$("#modal_propietario_tipo_persona_ap option:selected").each(function () {
		tipo_persona = $(this).val();
		if (tipo_persona == 1) {
			$('#modal_propietario_tipo_docu_ap').val('1');
			$('#div_modal_propietario_representante_legal_ap').hide();
			$('#div_modal_propietario_num_partida_registral_ap').hide();
		} else if (tipo_persona == 2) {
			$('#modal_propietario_tipo_docu_ap').val('2');
			$('#div_modal_propietario_representante_legal_ap').show();
			$('#div_modal_propietario_num_partida_registral_ap').show();
		}
		$('#modal_propietario_tipo_docu_ap').change();
		setTimeout(function() {
			$('#modal_propietario_nombre').focus();
		}, 200);		
	});
});

$("#modal_propietario_tipo_docu_ap").change(function () {
	$("#modal_propietario_tipo_docu_ap option:selected").each(function () {
		propietario_tipo_docu = $(this).val();
		if (propietario_tipo_docu == 1 || propietario_tipo_docu == 3) {
			$('#div_num_docu_propietario_ap').show();

			if(propietario_tipo_docu == 1){
				$('#label_num_docu_propietario_ap').html('Número de DNI del propietario:');
			} else if(propietario_tipo_docu == 3){
				$('#label_num_docu_propietario_ap').html('Número de Pasaporte del propietario:');
			}

			setTimeout(function() {
				$('#modal_propietario_num_docu_ap').focus();
			}, 200);
		} else if (propietario_tipo_docu == 2) {
			$('#div_num_docu_propietario_ap').hide();

			setTimeout(function() {
				$('#modal_propietario_num_ruc_ap').focus();
			}, 200);
		}
	});
});

$("#modal_propietario_tipo_persona_contacto_ap").change(function () {
	$("#modal_propietario_tipo_persona_contacto_ap option:selected").each(function () {
		tipo_persona_contacto = $(this).val();
		if (tipo_persona_contacto == 1) {
			$('#div_modal_propietario_contacto_nombre_ap').hide();
			$('#modal_propietario_contacto_telefono_ap').focus();
		} else if (tipo_persona_contacto == 2) {
			$('#div_modal_propietario_contacto_nombre_ap').show();
			$('#modal_propietario_contacto_nombre_ap').focus();
		}
	});
});



$("#modal_cp_subtotal").on({
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

$("#modal_cp_igv").on({
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

$("#modal_cp_monto").on({
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
		$("#modal_cp_tipo_igv_id").change();
	}
});

$("#modal_cp_moneda_id").change(function () {
	$("#modal_cp_moneda_id option:selected").each(function () {
		moneda_id = $(this).val();
		if (moneda_id != 0) {
			setTimeout(function() {
				$('#modal_cp_monto').focus();
			}, 200);
		}
	});
});

$("#modal_cp_tipo_igv_id").change(function () {
	$("#modal_cp_tipo_igv_id option:selected").each(function () {
		tipo_igv_id = $(this).val();
		if (tipo_igv_id != 0) {
			sec_contrato_nuevo_calcular_subtotal_y_igv_modal(tipo_igv_id);
			setTimeout(function() {
				if($('#modal_cp_tipo_comprobante').val() == "0"){
					$('#modal_cp_tipo_comprobante').focus();
					$('#modal_cp_tipo_comprobante').select2('open');
				}
			}, 200);
		}
	});
});

$("#modal_cp_forma_pago").change(function () {
	$("#modal_cp_forma_pago option:selected").each(function () {
		forma_pago = $(this).val();
		if (forma_pago != 0) {
			setTimeout(function() {
				$('#modal_cp_tipo_comprobante').focus();
				$('#modal_cp_tipo_comprobante').select2('open');
			}, 200);
		}
	});
});

$("#modal_cp_tipo_comprobante").change(function () {
	$("#modal_cp_tipo_comprobante option:selected").each(function () {
		tipo_comprobante = $(this).val();
		if (tipo_comprobante != 0) {
			setTimeout(function() {
				$('#modal_cp_plazo_pago').focus();
			}, 200);
		}
	});
});

// FIN CAMBIOS ADENDA PROVEEDOR



// INICIO contrato NIF16

function sec_contrato_nuevo_select_cargo(type) {

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
		url: "/sys/set_contrato_nuevo.php",
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

/// INICIO INFLACION
function sec_contrato_nuevo_modal_agregar_inflacion(type) {
	console.log(type)
	if (type == 'new') {
		$('#modal_inflacion_titulo').html('Registrar Inflación');
		$('#btn_modal_if_agregar_agregar').show();
		$('#btn_modal_if_agregar_editar').hide();
	}
	if (type == 'edit') {
		$('#modal_inflacion_titulo').html('Editar Inflación');
		$('#btn_modal_if_agregar_agregar').hide();
		$('#btn_modal_if_agregar_editar').show();
	}

	$('#modal_if_fecha').val('');
	$('#modal_if_tipo_periodicidad_id').val("0").trigger("change");
	$('#modal_if_numero').val('');
	$('#modal_if_tipo_anio_mes').val("0").trigger("change");
	$('#modal_if_porcentaje_anadido').val('');
	$('#modal_if_tope_inflacion').val('');
	$('#modal_if_minimo_inflacion').val('');
	$("#modalAgregarInflacion").modal({ backdrop: "static", keyboard: false });
	setTimeout(function () {
		// $("#contrato_incrementos_monto_o_porcentaje").focus();
	}, 500);


}

function sec_contrato_nuevo_agregar_inflacion() {

	var tipo_periodicidad_id = $('#modal_if_tipo_periodicidad_id').val();
	var numero = $('#modal_if_numero').val();
	var tipo_anio_mes = $('#modal_if_tipo_anio_mes').val();
	var porcentaje_anadido = $('#modal_if_porcentaje_anadido').val();
	var tope_inflacion = $('#modal_if_tope_inflacion').val();
	var minimo_inflacion = $('#modal_if_minimo_inflacion').val();

	if (tipo_periodicidad_id == "" || tipo_periodicidad_id == "0") {
		alertify.error('Seleccione un tipo de valor',5);
		$("#modal_if_tipo_periodicidad_id").select2('open');
		return false;
	}

	if (tipo_periodicidad_id == 1) {
		if (numero == "") {
			alertify.error('Ingrese un numero',5);
			$("#modal_if_numero").focus();
			return false;
		}
	
		if (tipo_anio_mes == "" || tipo_anio_mes == "0") {
			alertify.error('seleccione una mes/año',5);
			$("#modal_if_tipo_anio_mes").select2('open');
			return false;
		}
	}

	// if (porcentaje_anadido == "") {
	// 	alertify.error('Ingrese un porcentaje',5);
	// 	$("#modal_if_porcentaje_anadido").focus();
	// 	return false;
	// }

	var accion = 'guardar_inflacion';
	var data = {
		"accion": accion,
		"tipo_periodicidad_id" : tipo_periodicidad_id,
		"numero" : numero,
		"tipo_anio_mes" : tipo_anio_mes,
		"porcentaje_anadido" : porcentaje_anadido,
		"tope_inflacion" : tope_inflacion,
		"minimo_inflacion" : minimo_inflacion,
	};
	auditoria_send({ "proceso": "guardar_inflacion", "data": data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "guardar_inflacion", "data": respuesta });
			if (parseInt(respuesta.http_code) == 200) {
				array_inflacion_contrato.push(respuesta.result);
				sec_contrato_nuevo_listar_inflacion();
			}

		},
		error: function() {}
	});
}

function sec_contrato_nuevo_listar_inflacion() {

	var accion = 'obtener_nuevo_lista_inflacion';
	var data = {
		"accion": accion,
		"inflaciones": array_inflacion_contrato,
	}
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				$('#divTablaInflacion').html(respuesta.result);
				$('#modalAgregarInflacion').modal('hide');

				if (array_inflacion_contrato.length >= 1) {
					$('.block-new-inflacion').hide();
				}else{
					$('.block-new-inflacion').show();
				}
				
			}
		},
		error: function() {}
	});
}

function sec_contrato_nuevo_modal_editar_inflacion(inflacion_id) {

	sec_contrato_nuevo_modal_agregar_inflacion('edit');
	var accion = 'obtener_inflacion_por_id';
	var data = {
		"accion": accion,
		"inflacion_id": inflacion_id,
	}
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_if_inflacion_id').val(respuesta.result.id);
				$('#modal_if_fecha').val(respuesta.result.fecha);
				$('#modal_if_tipo_periodicidad_id').val(respuesta.result.tipo_periodicidad_id).trigger("change");
				$('#modal_if_numero').val(respuesta.result.numero);
				$('#modal_if_tipo_anio_mes').val(respuesta.result.tipo_anio_mes).trigger("change");
				$('#modal_if_moneda_id').val(respuesta.result.moneda_id).trigger("change");
				$('#modal_if_porcentaje_anadido').val(respuesta.result.porcentaje_anadido);
				$('#modal_if_tope_inflacion').val(respuesta.result.tope_inflacion);
				$('#modal_if_minimo_inflacion').val(respuesta.result.minimo_inflacion);
			}
		},
		error: function() {}
	});
}

function sec_contrato_nuevo_editar_inflacion() {

	var inflacion_id = $('#modal_if_inflacion_id').val();
	var tipo_periodicidad_id = $('#modal_if_tipo_periodicidad_id').val();
	var numero = $('#modal_if_numero').val();
	var tipo_anio_mes = $('#modal_if_tipo_anio_mes').val();
	var porcentaje_anadido = $('#modal_if_porcentaje_anadido').val();
	var tope_inflacion = $('#modal_if_tope_inflacion').val();
	var minimo_inflacion = $('#modal_if_minimo_inflacion').val();

	if (tipo_periodicidad_id == "" || tipo_periodicidad_id == "0") {
		alertify.error('Seleccione un tipo de valor',5);
		$("#modal_if_tipo_periodicidad_id").select2('open');
		return false;
	}

	if (tipo_periodicidad_id == 1) {
		if (numero == "") {
			alertify.error('Ingrese un numero',5);
			$("#modal_if_numero").focus();
			return false;
		}
	
		if (tipo_anio_mes == "" || tipo_anio_mes == "0") {
			alertify.error('seleccione una mes/año',5);
			$("#modal_if_tipo_anio_mes").select2('open');
			return false;
		}
	}

	if (porcentaje_anadido == "") {
		alertify.error('Ingrese un porcentaje',5);
		$("#modal_if_porcentaje_anadido").focus();
		return false;
	}

	var accion = 'editar_inflacion';
	var data = {
		"accion": accion,
		"inflacion_id" : inflacion_id,
		"tipo_periodicidad_id" : tipo_periodicidad_id,
		"numero" : numero,
		"tipo_anio_mes" : tipo_anio_mes,
		"porcentaje_anadido" : porcentaje_anadido,
		"tope_inflacion" : tope_inflacion,
		"minimo_inflacion" : minimo_inflacion,
	};
	auditoria_send({ "proceso": "editar_inflacion", "data": data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "editar_inflacion", "data": respuesta });
			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_listar_inflacion();
			}

		},
		error: function() {}
	});
}

function sec_contrato_nuevo_eliminar_inflacion(inflacion_id) {
	var index = array_inflacion_contrato.indexOf(inflacion_id);
	if (index != "-1") {
		array_inflacion_contrato.splice(index,1);
		sec_contrato_nuevo_listar_inflacion();
	}
}

// FINAL INFLACION


/// INICIO CUOTA EXTRAORDINARIA
function sec_contrato_nuevo_modal_agregar_cuota_extraordinaria(type) {
	console.log(type)
	if (type == 'new') {
		$('#modal_cuota_extraordinaria_titulo').html('Registrar Cuota Extraordinaria');
		$('#btn_modal_ce_agregar_agregar').show();
		$('#btn_modal_ce_agregar_editar').hide();
	}
	if (type == 'edit') {
		$('#modal_cuota_extraordinaria_titulo').html('Editar Cuota Extraordinaria');
		$('#btn_modal_ce_agregar_agregar').hide();
		$('#btn_modal_ce_agregar_editar').show();
	}

	$('#modal_ce_mes').val("0").trigger("change");
	$('#modal_ce_multiplicador').val('');
	$('#modal_ce_meses_prox_pago').val('');
	$("#modalAgregarCuotaExtraordinaria").modal({ backdrop: "static", keyboard: false });
	setTimeout(function () {
		$("#modal_ce_mes").select2('open');
	}, 500);


}

function sec_contrato_nuevo_agregar_cuota_extraordinaria() {

	var mes = $('#modal_ce_mes').val();
	var multiplicador = $('#modal_ce_multiplicador').val();
	if (mes == "" || mes == "0" ) {
		alertify.error('Ingrese un mes',5);
		$("#modal_ce_mes").select2('open');
		return false;
	}
	if (multiplicador == "") {
		alertify.error('Ingrese un multiplicador',5);
		$("#modal_ce_multiplicador").focus();
		return false;
	}
	
	var accion = 'guardar_cuota_extraordinaria';
	var data = {
		"accion": accion,
		"mes" : mes,
		"multiplicador" : multiplicador,
	};
	auditoria_send({ "proceso": "guardar_cuota_extraordinaria", "data": data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "guardar_cuota_extraordinaria", "data": respuesta });
			if (parseInt(respuesta.http_code) == 200) {
				array_cuota_extraordinaria_contrato.push(respuesta.result);
				sec_contrato_nuevo_listar_cuota_extraordinaria();
			}

		},
		error: function() {}
	});
}

function sec_contrato_nuevo_listar_cuota_extraordinaria() {

	var accion = 'obtener_nuevo_lista_cuota_extraordinaria';
	var data = {
		"accion": accion,
		"cuota_extraordinaria": array_cuota_extraordinaria_contrato,
	}
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				$('#divTablaCuotaExtraordinaria').html(respuesta.result);
				$('#modalAgregarCuotaExtraordinaria').modal('hide');
			}
		},
		error: function() {}
	});
}

function sec_contrato_nuevo_modal_editar_cuota_extraordinaria(cuota_extraordinaria_id) {

	sec_contrato_nuevo_modal_agregar_cuota_extraordinaria('edit');
	var accion = 'obtener_cuota_extraordinaria_por_id';
	var data = {
		"accion": accion,
		"cuota_extraordinaria_id": cuota_extraordinaria_id,
	}
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_ce_cuota_extraordinaria_id').val(respuesta.result.id);
				$('#modal_ce_mes').val(respuesta.result.mes).trigger("change");
				$('#modal_ce_multiplicador').val(respuesta.result.multiplicador);
				$('#modal_ce_meses_prox_pago').val(respuesta.result.meses_despues);
			}
		},
		error: function() {}
	});
}

function sec_contrato_nuevo_editar_cuota_extraordinaria() {

	var cuota_extraordinaria_id = $('#modal_ce_cuota_extraordinaria_id').val();
	var mes = $('#modal_ce_mes').val();
	var multiplicador = $('#modal_ce_multiplicador').val();
	var meses_prox_pago = $('#modal_ce_meses_prox_pago').val();
	if (mes == "" || mes == "0" ) {
		alertify.error('Ingrese un mes',5);
		$("#modal_ce_mes").select2('open');
		return false;
	}
	if (multiplicador == "") {
		alertify.error('Ingrese un multiplicador',5);
		$("#modal_ce_multiplicador").focus();
		return false;
	}
	if (meses_prox_pago == "") {
		alertify.error('Seleccione los meses del proximo pago',5);
		$("#modal_ce_meses_prox_pago").focus();
		return false;
	}

	var accion = 'editar_cuota_extraordinaria';
	var data = {
		"accion": accion,
		"cuota_extraordinaria_id" : cuota_extraordinaria_id,
		"mes" : mes,
		"multiplicador" : multiplicador,
		"meses_prox_pago" : meses_prox_pago,
	};
	auditoria_send({ "proceso": "editar_cuota_extraordinaria", "data": data });
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "editar_cuota_extraordinaria", "data": respuesta });
			if (parseInt(respuesta.http_code) == 200) {
				sec_contrato_nuevo_listar_cuota_extraordinaria();
			}

		},
		error: function() {}
	});
}

function sec_contrato_nuevo_eliminar_cuota_extraordinaria(inflacion_id) {
	var index = array_cuota_extraordinaria_contrato.indexOf(inflacion_id);
	if (index != "-1") {
		array_cuota_extraordinaria_contrato.splice(index,1);
		sec_contrato_nuevo_listar_cuota_extraordinaria();
	}
}

// FINAL CUOTA EXTRAORDINARIA

/// INICIO CONTRATO NIF16