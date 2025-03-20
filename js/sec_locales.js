function sec_locales() {
	if(sec_id=="locales"){
		sec_locales_events();
		var id_local = $('#sec_locales_setting_id_local').html();
		if(parseInt(id_local)>0){
			// listarAddressMacxLocal();
		}

		$("#select-red_id").change(function () {
			$("#select-red_id option:selected").each(function () {
				var red_id = $(this).val();
				var area_id = $('#area_id_temporal').val();
				var cargo_id = $('#cargo_id_temporal').val();
				var item_id = $('#item_id_temporal').val();
				var permiso_para_crear_locales_red_at = $('#permiso_para_crear_locales_red_at_temporal').val();
				if (item_id == "new" && red_id == "1" && area_id == "21" && permiso_para_crear_locales_red_at == "0" && (cargo_id == "4" || cargo_id == "16")) {
					swal({
						title: 'Los locales de la Red AT son generados desde el Módulo de Contratos al cargar el Contrato Firmado en Gestión',
						type: "warning",
						timer: 10000
					});
					$('#select-red_id').val("0").trigger('change');
					return false;
				}
				return false;
			});
		});

		$("#select_option_permiso_para_crear_locales_red_at").change(function () {
			$("#select_option_permiso_para_crear_locales_red_at option:selected").each(function () {
				var permiso_para_crear_locales_red_at = $(this).val();

				var data = {
					"accion": "actualizar_permiso_para_crear_locales_red_at",
					"permiso_para_crear_locales_red_at": permiso_para_crear_locales_red_at
				}

				auditoria_send({ "proceso": "actualizar_permiso_para_crear_locales_red_at", "data": data });

				$.ajax({
					url: "/sys/set_locales.php",
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
						auditoria_send({"respuesta": "actualizar_permiso_para_crear_locales_red_at", "data": respuesta});
						if (parseInt(respuesta.http_code) == 400) {
							swal({
								title: "Error",
								text: respuesta.error,
								type: 'warning'
							});
							return false;
						}
						
						if (parseInt(respuesta.http_code) == 200) {
							swal({
								title: respuesta.msg,
								text: false,
								type: 'success'
							});
							
							return false;
						}
					},
					error: function() {}
				});

			});
		});


	}
}

function list_detalle_locales_excel()
{
	
    var data = {
        "accion": "list_detalle_locales_excel"
	}

    $.ajax({
        url: "/sys/set_locales.php",
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
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el detalle de locales",
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



// INICIO DE FUNCION DE IMPORTACION
function import_detalle_locales_excel() {
	var data = {
		'accion' : 'import_detalle_locales_excel'
	};
	$.ajax({
		url: "/sys/set_locales.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (response) {//  alert(datat)
			console.log(response)
		},
		error: function () {
		}
	});
}
// FIN DE FUNCION DE IMPORTACION
function sec_locales_events(){
	$(".switch_is_open").off().change(function(event) {
		event.preventDefault();
		if(!($(this).prop('checked'))){
			swal({
				title: "¡ATENCIÓN!",
				text: '<span style="color:black;" >Si deshabilita este local se notificará a <br><b>Dirección y Gerencia</b><br>¿Está seguro de deshabilitar el local?</span>',
				html: true,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Deshabilitar",
				cancelButtonText: "Dejar habilitado"
			},
			function(isConfirm){
				if (isConfirm) {
					switch_data($(event.target));
				} else {
					$("#checkbox_web").bootstrapToggle('on');
				}
			});
		} else {
			switch_data($(event.target));
		}
	});
	$(".switch_caja_locales").off().change(function(event) {
		event.preventDefault();
		switch_estado_caja($(event.target));
	});
	
	$(".switch_caja_locales").bootstrapToggle({
		on: "activo",
		off: "inactivo",
		onstyle: "success",
		offstyle: "danger",
		size: "mini",
	  });
	  $(".toggle")
		//.off()
		.on("click", function (event) {
		  if (typeof $(this).find(".switch_caja_locales").data().ignore === "undefined") {
		  }
		});

	$(".local_guardar_zona_covid_btn").off().click(function(event) {
		loading(1);
		var zona_covid = $("#select-zona_covid").val();
		console.log(zona_covid);
		var data = Object();
			data.table = "tbl_locales_web_config";
			data.id = item_id;
			data.col = "zona_covid";
			data.val = zona_covid;
		console.log(data);
		auditoria_send({"proceso":"switch_data","data":data});
		$.post('sys/set_data.php', {
			"opt": 'switch_data'
			,"data":data
		}, function(r, textStatus, xhr) {
			loading();
			console.log("switch_data:ready");
			console.log(r);
		});
	});
	$(".local_guardar_sorteo_local_nuevo_btn").off().click(function(event) {
		loading(1);
		$(".local_guardar_sorteo_local_nuevo_btn").hide();
		var fecha_inicio = $("#fecha_sorteo_tienda_nueva").val();
		console.log(fecha_inicio);
		var data = Object();
			data.id = item_id;
			data.val = fecha_inicio;
		console.log(data);
		//return false;
		auditoria_send({"proceso":"sorteo_tienda_nueva_inicio","data":data});
		$.post('sys/set_data.php', {
			"opt": 'sorteo_tienda_nueva_inicio'
			,"data":data
		}, function(r, textStatus, xhr) {
			loading();
			location.reload();
			console.log("sorteo_inicio:ready");
			console.log(r);
		});
	});
	searchTable();
	/* HORARIOS */
		$('.calendar-grid').css("height", ($(window).height()-290)+"px");

		$('#txtLocalHorarioInicio').datepicker({ dateFormat: 'yy-mm-dd' });
		$('#txtLocalHorarioFin').datepicker({ dateFormat: 'yy-mm-dd' });

		$('#btnLocalHorarioSearch').on('click', function(event) {
			event.preventDefault();
			get_local_horarios();
		});

		$('#cbHorarioPerfil').on('change', function(event) {
			event.preventDefault();
			$("#btnHorarioPerfilGuardar").data("horario-id", $(this).val());
			get_horarios_modal($(this).val());
		});

		$(document).on('click', '#cellCalendar', function(event) {
			event.preventDefault();

			$('#cbHorarioPerfil').val($(this).data("horario-id"));
			$('#txtHorarioPerfilDate').html($(this).data("date"));

			$("#btnHorarioPerfilGuardar").data("local-id", $('#txtHorarioLocalId').val());
			$("#btnHorarioPerfilGuardar").data("horario-id", $(this).data("horario-id"));
			$("#btnHorarioPerfilGuardar").data("date", $(this).data("date"));
			$("#btnHorarioPerfilGuardar").data("daily", 0);

			get_horarios_modal($(this).data("horario-id"));

			$('#mdHorarioPerfil').modal("show");

		});

		$('#btnHorarioPerfilGuardarDia').on('click', function(event) {
			event.preventDefault();
			$("#btnHorarioPerfilGuardar").data("daily", 1);
			$("#btnHorarioPerfilGuardar").click();
		});

		$('#btnHorarioPerfilGuardar').on('click', function(event) {
			event.preventDefault();

			var data = {};
			data.local_id = $(this).data("local-id");
			data.horario_id = $(this).data("horario-id");
			data.started_at = $(this).data("date");
			data.daily = $(this).data("daily");

			if($("#btnHorarioPerfilGuardar").data("horario-id") != 0){
				$.post('/sys/get_horarios.php', {"save_local_horario": data}, function(response) {
					get_local_horarios();
					$('#mdHorarioPerfil').modal("hide");
				});
			}
			else swal("Error!", "Es necesario elegir el perfil antes de guardar", "warning");


			console.log(data);
		});

		// if($("#tab_horarios").length()){
		// 	if(item_id !== ""){ get_local_horarios();}
		// }
	/* END HORARIOS */

	$("#btnSaldoKasnet").on('click', function(event) {
		event.preventDefault();
		$("#mdSaldoKasnet").modal("show");
	});

	$('#startDateTime').datetimepicker({maxDate: moment()});
	$('#endDateTime').datetimepicker({
		useCurrent: false,
		maxDate: moment()
	});
	$("#startDateTime").on("dp.change", function (e) {
		$('#endDateTime').data("DateTimePicker").minDate(e.date);
	});
	$("#endDateTime").on("dp.change", function (e) {
		$('#startDateTime').data("DateTimePicker").maxDate(e.date);
	});

	$("#select-ubigeo_departamento")
	.off()
	.change(function(event) {
		loading(true);
			//$("#select-ubigeo_provincia").append($("<option>").html("Seleccione una Provincia").val(0));
			$("#select-ubigeo_distrito").html("");
			$("#select-ubigeo_distrito").append($("<option>").html("- Seleccione una Provincia -").val(""));
			$("#select-ubigeo_distrito").attr('disabled',"disabled");
			// $("#select-ubigeo_distrito").attr('disabled',"disabled");
			//Seleccione Departamento
			var data = Object();
			data.departamento_id = $(this).val();
			//console.log(data);
			$.get('sys/build_html.php', {
				"opt":"select_ubigeo_departamento",
				"data":data
			},
			function(r) {
					//console.log(r);
					var response = jQuery.parseJSON(r);
					//console.log(response);
					$("#select-ubigeo_provincia").html("");
					$("#select-ubigeo_provincia").append($("<option>").html("Seleccione una Provincia").val(""));
					$.each(response, function(index, val) {
						$("#select-ubigeo_provincia").append($("<option>").html(val.nombre).val(val.cod));
					});
					$("#select-ubigeo_provincia").removeAttr('disabled');
					loading();
					$("#select-ubigeo_provincia")
					.off()
					.change(function(event) {
						loading(true);
							//var data = Object();
							data.provincia_id = $(this).val();
							//console.log(data);
							$.get('sys/build_html.php', {
								"opt":"select_ubigeo_provincia",
								"data":data
							},
							function(r) {
								try{
										//console.log(r);
										var response = jQuery.parseJSON(r);
										//console.log(response);
										$("#select-ubigeo_distrito").html("");
										$("#select-ubigeo_distrito").append($("<option>").html("- Seleccione un Distrito -").val(""));
										$.each(response, function(index, val) {
											$("#select-ubigeo_distrito").append($("<option>").html(val.nombre).val(val.cod));
										});
										$("#select-ubigeo_distrito").removeAttr('disabled');
										$("#select-ubigeo_distrito")
										.select2({
											width:"100%"
										});
										loading();

									}catch(err){
										swal({
											title: 'Error en la base de datos',
											type: "warning",
											timer: 2000,
										}, function(){
											swal.close();
										});
									}
								});
						});
					$("#select-ubigeo_provincia")
					.select2({
						width:"100%"
					});
				});
		});
	$("#select-ubigeo_departamento")
	.select2({
		width:"100%"
	});

	// $("#select-ubigeo_provincia")
	// 	.off()
	// 	.change(function(event) {
	// 		loading(true);
	// 		var data = Object();
	// 			data.departamento_id = $("#select-ubigeo_departamento").val();
	// 			data.provincia_id = $(this).val();
	// 		//console.log(data);
	// 		$.get('sys/build_html.php', {
	// 			"opt":"select_ubigeo_provincia",
	// 			"data":data
	// 			},
	// 			function(r) {
	// 				//console.log(r);
	// 				var response = jQuery.parseJSON(r);
	// 				//console.log(response);
	// 				$("#select-ubigeo_distrito").html("");
	// 				$("#select-ubigeo_distrito").append($("<option>").html("- Seleccione un Distrito -").val(""));
	// 				$.each(response, function(index, val) {
	// 					 $("#select-ubigeo_distrito").append($("<option>").html(val.nombre).val(val.cod));
	// 				});
	// 				$("#select-ubigeo_distrito").removeAttr('disabled');
	// 				loading();

	// 		});
	// 	});
	$(".select2")
	.select2({
		width:"100%"
	});
	$('#fecha_sorteo_tienda_nueva').datepicker({ dateFormat: 'yy-mm-dd' });
	$(".local_datepicker")
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
	$(".lp_id_add_btn")
	.off()
	.click(function(event) {
		lp_id_add($(this).data());
	});
	$(".lp_id_del_btn")
	.off()
	.click(function(event) {
		lp_id_del($(this).data());
	});
	$(document).on('click', '.btn_change_estado_proveedor', function(e) {
        const id = e.currentTarget.dataset.id
        const status = e.currentTarget.dataset.status;
		const servicio_id = e.currentTarget.dataset.servicio;
        let new_status = 0;
        let msg_confirm = '';
        let btn_msg_confirm = '';
        
        switch (status) {
            case "0":
                new_status = 1;
                msg_confirm = 'Se habilitará el terminal del Proveedor.';
                btn_msg_confirm = 'Sí, habilitar';
                break;
            case "1":
                new_status = 0;
                msg_confirm = 'Se deshabilitará el terminal del Proveedor.';
                btn_msg_confirm = 'Sí, deshabilitar';
                break;
            default:
                new_status = 0;
                break;
        }
        
        swal({
            title: "¿Estás seguro?",
            text: msg_confirm,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: btn_msg_confirm,
            cancelButtonText: "No, cancelar",
            closeOnConfirm: false,
            closeOnCancel: true,
        }, function (isConfirm) {
            if (isConfirm) {
                changeEstadoProveedor(id, new_status, status, servicio_id);
            } else {
                
            }
        });
    });


	$(document).on('click', '.btn_historico_cambios_estado_proveedor', function(e) {

        const id = e.currentTarget.dataset.id;
		const servicio_id = e.currentTarget.dataset.servicio;
		const proveedor_id = e.currentTarget.dataset.proveedor_id;
        $('#modalLocalProveedorHistoricoCambios').modal('show');
		document.getElementById('span_local_proveedor').textContent = proveedor_id;



        sec_local_proveedor_historico(id,servicio_id);
    })

	function sec_local_proveedor_historico(id, servicio_id){

        var data = {
            accion: "get_historico_local_proveedor",
            local_proveedor_id: id,
			servicio_id: servicio_id,
        };
        $("#modal_local_historico_div_tabla").show();

        var columnDefs = [{
            className: 'text-center',
            targets: [0, 1, 2, 3, 4,5]
        }];

        var tabla = crearDataTable(
            "#modal_local_historico_datatable",
            "/sys/set_locales.php",
            data,
            columnDefs
        );

         // Eliminar el campo de búsqueda
         tabla.on('init.dt', function () {
            $('.dataTables_filter').hide();
        });
        
    }

	$(".local_add_promo_dialog_btn")
	.off()
	.click(function(event) {
		event.preventDefault();
		if(item_id=="new"){
			swal("Por favor primero guarde el local.");
		}else{
			$(".local_add_promo_dialog_btn").addClass('hidden');
			$(".form-add_promo").removeClass('hidden');
		}
	});
	$(".local_cancel_promo_dialog_btn")
	.off()
	.click(function(event) {
		event.preventDefault();
		$(".local_add_promo_dialog_btn").removeClass('hidden');
		$(".form-add_promo").addClass('hidden');
	});
	$(".local_add_promo_btn")
	.off()
	.click(function(event) {
		event.preventDefault();

		loading();
		var save_data = {};
		save_data.table="tbl_local_promociones";
		save_data.id="new";
		save_data.values={};
		save_data.values.estado=1;
		save_data.values.local_id=item_id;
		save_data.values.promo_unique_id=$("#select-add_promo").val();
		save_data.values.fecha_inicio=$("#input-fecha_inicio").val();
		save_data.values.fecha_fin=$("#input-fecha_fin").val();

		if(save_data.values.promo_unique_id){
			console.log(save_data);
			$.post('sys/set_data.php', {
				"opt": 'save_item'
				,"data":save_data
			}, function(r, textStatus, xhr) {
				try{
					swal({
						title: "Agregado",
						text: "",
						type: "success",
						timer: 200,
						closeOnConfirm: true
					},
					function(){
						m_reload();
						swal.close();
					});
				}catch(err){
					swal({
						title: 'Error en la base de datos',
						type: "warning",
						timer: 2000,
					}, function(){
						swal.close();
						loading();
					});
				}
			});
		}else{
			swal({
				title: "Seleccione un producto",
				text: "",
				type: "warning",
					// timer: 1000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
		}
	});

	$(".local_promo_modificar_btn")
	.off()
	.click(function(event) {
		var data = $(this).data();

		var save_data = {};
		save_data.table="tbl_local_promociones";
		save_data.id=data.id;
		save_data.values={};
		save_data.values.estado=1;
				// save_data.values.local_id=item_id;
				// save_data.values.promo_unique_id=$("#select-add_promo").val();
				save_data.values.fecha_inicio=$("#input-lp_"+data.id+"_fecha_inicio").val();
				save_data.values.fecha_fin=$("#input-lp_"+data.id+"_fecha_fin").val();

				console.log(save_data);

				swal({
					title: "¿Seguro?",
				// text: "Una vez eliminado no se podrá recuperar!",
				type: "info",
				showCancelButton: true,
				// confirmButtonColor: "#DD6B55",
				confirmButtonText: "Si",
				cancelButtonText:"No",
				closeOnConfirm: false
			},
			function(){
				$.post('sys/set_data.php', {
					"opt": 'save_item'
					,"data":save_data
				}, function(response) {
					try{
						console.log(response);
						swal({
							title: "Guardado",
							text: "",
							type: "success",
							timer: 200,
							closeOnConfirm: true
						},
						function(){
							m_reload();
							swal.close();
						});
					}catch(err){
						swal({
							title: 'Error en la base de datos',
							type: "warning",
							timer: 2000,
						}, function(){
							swal.close();
							loading();
						});
					}
				});
			});
			});
	$(".local_promo_eliminar_btn")
	.off()
	.click(function(event) {
		var data = $(this).data();
		swal({
			title: "¿Seguro?",
			text: "¡Una vez eliminado no se podrá recuperar!",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Si, borrar!",
			cancelButtonText:"No",
			closeOnConfirm: false
		},
		function(){
			var html_parent = $(".local_promo_panel_"+data.id+"");
				// console.log(html_parent);
				html_parent.hide();
				data.table = "tbl_local_promociones";
				data.col = "estado";
				data.val = 0;
				auditoria_send({"proceso":"switch_data","data":data});
				$.post('sys/set_data.php', {
					"opt": 'switch_data'
					,"data":data
				}, function(r, textStatus, xhr) {
					try{
						swal({
							title: "Eliminado",
							text: "El archivo ha sido eliminado.",
							type: "success",
							timer: 400,
							closeOnConfirm: false
						},
						function(){
							html_parent.hide();
							swal.close();
						});
					}catch(err){
						swal({
							title: 'Error en la base de datos',
							type: "warning",
							timer: 2000,
						}, function(){
							swal.close();
							loading();
						});
					}
				});
			});
	});

	$(".local_archivo").on("change",function(ev){
		console.log("imagen");
		campos=[];
		var element=$(this);
		var panel =element.closest(".panel_credenciales");
		var inputs=panel.find(".credencial");
		inputs.each(function(index,input){
			var cred_id = {};
			cred_id.id = input.getAttribute('data-id');
			cred_id.local_id = input.getAttribute('data-id_local');
			cred_id.campo_tipo_credencial_id = input.getAttribute('data-id_campo');
			cred_id.valor = input.value;
			campos[index]=cred_id;
			//console.log(input);
		});

		var form_data = new FormData();
		form_data.append("tabla",element.attr("data-tabla"));
		form_data.append("id_campo", element.attr("data-id_campo"));
		form_data.append("campos", JSON.stringify(campos));
		form_data.append("sec_local_credencial_archivo_guardar", "sec_local_credencial_archivo_guardar");
		var file_data = "";
		if(element.val!=""){
			file_data = element.prop("files")[0];
		};
		form_data.append("file", file_data);
		if(file_data!=""){
			$.ajax({
				url: '/sys/set_locales.php',
				type: 'POST',
				data: form_data,
				cache: false,
				contentType: false,
				processData: false,
			})
			.done(function(r) {
				var archivo = r.split('@');
				var estado = archivo[0];

				if(estado=="0"){

					swal({
						title: "Error!",
						text: "Error al Insertar el archivo.",
						type: "warning",
						timer: 3000,
						closeOnConfirm: true
					});
					element.val("");
					return false;
				};
				m_reload();
				// var id_archivo = archivo[1];
				// var nombre = archivo[2];
				// $("#div_archivos_caja").append('<tr>'+
				// 							'<td style="border-bottom:none !important;width: 10px !important;">'+
				// 								'<button data-item="'+id_archivo+'" data-nombre="'+nombre+'" class="btn btn-danger btn-xs btn_eliminar_archivo_credencial">x</button>'+
				// 							'</td>'+
				// 							'<td style="border-bottom:none !important; cursor: pointer;">'+
				// 								'<a href="./files_bucket/credenciales/'+nombre+'" target="_blank">'+nombre+'</a>'+
				// 							'</td>'+
				// 						'</tr>');

				$("#"+element.attr('id')).val("");
			});
		}
	});

	$(".btn_eliminar_archivo_credencial")
	.on("click",function(ev){
		console.log("btn_eliminar_archivo_credencial");
		var archivo_id = $(this).attr("data-id");
		var archivo_nombre = $(this).attr("data-nombre");
		var parent = $(this).parent().parent();

		swal({
			title: "¿Seguro?",
			text: "¿Que desea borrar el Archivo?",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Si",
			cancelButtonText:"No",
		},
		function(evt){
			swal.close();
			if(evt){
				eliminar_archivo_credencial(parent,archivo_id,archivo_nombre);
			}
		});
	});

	function eliminar_archivo_credencial(tr,id,nombre){
		var archivo_id = id;
		var archivo_nombre = nombre;
		var parent = tr;
		var caja_id = caja_id;
		$.post('/sys/set_locales.php', {
			"sec_local_credencial_archivo_eliminar": archivo_id,"nombre_archivo":archivo_nombre
		}, function(r) {
			var respuesta = r;
			setTimeout(function(){
				if(respuesta=="ok"){
					parent.remove();
				};
				if(respuesta=="Error"){
					swal("Error!", "Error al tratar de eliminar el archivo.", "warning");
				};
			}, 500);

		});
	}

	/*$('#area_id').on('change', function(event) {
		event.preventDefault();
		$('#cargo_id').children('option').remove();
		if($(this).find(":selected").val() == 21){
			$("#cargo_id").append('<option value="4">Supervisor</option>');
			$("#cargo_id").append('<option value="5">Cajero</option>');
		}
		else if($(this).find(":selected").val() == 22){
			$("#cargo_id").append('<option value="13">Auditor</option>');
			$("#cargo_id").append('<option value="17">Analista</option>');
			$("#cargo_id").append('<option value="19">Coordinador</option>');
		}
	});*/
	if($('#tblLocales').length){
		loading(true);
		filter_locales_table(0);
	}
	$('#txtLocalesFilter').focus();

	// $("#txtLocalesFilter").on("keyup", function() {
	// 	$('#icoLocalesSpinner').show();
	// 	filter_locales_table(0);
	// });

	$('#cbLocalesLimit').on('change', function(event) {
		loading(true);
		$('#txtLocalesFilter').val('');
		filter_locales_table(0);
	});

	$(".locales_add_caja_modal_btn")
	.off()
	.click(function(event) {
		locales_add_caja_modal("show");
	});
	// $(".locales_add_caja_modal_btn").first().click();

	$(".locales_add_caja_cdv_modal_btn")
	.off()
	.click(function(event) {
		locales_add_caja_cdv_modal({opt: "show"});
	});
	// $(".locales_add_caja_cdv_modal_btn").first().click();

	$(".locales_guardar_caja_config_btn")
	.off()
	.click(function(event) {
		locales_guardar_caja_config();
	});

	$(".locales_incrementar_saldo_kasnet_btn")
	.off()
	.click(function(event) {
		locales_incrementar_saldo_kasnet();
	});

	$(".locales_add_usuario_modal_btn")
	.off()
	.click(function(event) {
		locales_add_usuario_modal("show");
	});
	// $(".locales_add_usuario_modal_btn").click();
	$(".lu_restaurar_pass_btn")
	.off()
	.click(function(event) {
		var usuario_id = $(this).data("id");
		locales_restore_usuario_password(usuario_id);
	});
	$(".lu_remove_btn")
	.off()
	.click(function(event) {
		var usuario_id = $(this).data("id");
		locales_remove_usuario(usuario_id);
	});

	$("#txtFilter").on("keyup", function() {
		var value = $(this).val().toLowerCase();
		$("#tbProductos tbody tr").filter(function() {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		});
	});

	if($('#tbProductos').length) populate_configv2_table();

	$("#btnConfigV2").on('click', function(event) {
		event.preventDefault();
		$("#alertBox").hide();

		var data = {};
		data.local_id = $("#local_id").val();
		data.producto_id = $("#cbProductos option:selected").val();
		data.servicio_id = $("#cbProveedores option:selected").val();
		data.canal_id = $("#cbCanales option:selected").val();
		data.proveedor_id = $("#txtProveedorId").val();
		data.nombre = $("#txtNombre").val();

		let display = true;
		$.each(data, function(index, val) {
			if(val === null || val === '') display = false;
		});

		if(!display) $("#alertBox").show();
		else{
			auditoria_send({"proceso":"add_configuracion","data":data});
			$.post('sys/get_locales.php', {"add_configuracion":data}, function(r, textStatus, xhr) {
				console.log(r);
				populate_configv2_table();
			});
		}
	});

	$(document).off("click", ".btnRemoveConfigV2");
	$(document).on("click", ".btnRemoveConfigV2", function(event) {
		var data = {};
		data.id = $(this).closest('tr').find("#config_id").val();
		swal({
			title: "¿Seguro?",
			text: "¡Una vez eliminado no se podrá recuperar!",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Si, borrar!",
			cancelButtonText:"No",
			closeOnConfirm: true
		},
		function(){
			auditoria_send({"proceso":"remove_configuracion","data":data});
			$.post('sys/get_locales.php',{"remove_configuracion":data}, function(r, textStatus, xhr) {
				console.log(r);
				populate_configv2_table();
			});
		});
	});

	$("#addStreaming").on('click', function(event) {
		event.preventDefault();
		document.getElementById("cctvStream").src="https://thumbs.gfycat.com/VibrantHeavyFrogmouth-size_restricted.gif";
		var data = {};

		data.startDateTime = $("#startDateTime").val();
		data.endDateTime = $("#endDateTime").val();
		data.trackId = $("#trackId").val();
		data.url = $("#url").val();
		data.id = $("#cameraId").val();
		console.log(data.id);

		if(data.id != null)	$.post('sys/get_locales.php', {"remove_streaming":data}, function(r, textStatus, xhr){
			console.log(r);
		});

			$.post('sys/get_locales.php', {"add_streaming":data}, function(r, textStatus, xhr) {
				var monitor = jQuery.parseJSON(r);
				console.log(monitor);

				document.getElementById("cctvStream").src="http://35.185.3.100/zm/cgi-bin/nph-zms?scale=100&width=640px&height=480px&mode=jpeg&maxfps=30&monitor="+monitor.Id+"&"+$("#auth").val();
				$("#cameraId").val(monitor.Id);
			});
		});

	$("[id='mdCCTV']").on('hidden.bs.modal', function () {
		$("[id='cctv_monitor']").show();
		var data = {};
		data.id = $("#cameraId").val();
		console.log(data)
		if(data.id != null)	$.post('sys/get_locales.php', {"remove_streaming":data}, function(r, textStatus, xhr){
			console.log(r);
		});
	});

	$("[id='cctvModal']").on('click', function(event){
		event.preventDefault();
		$("#mdTitle").text($(this).data('title'));
		$("#trackId").val($(this).data('track'));
		$("#url").val($(this).data('url'));
		$("#auth").val($(this).data('auth'));
		$("#cameraId").val("");
		$("[id='cctv_monitor']").hide();
		$("#cctvStream").attr("src", "http://35.185.3.100/zm/cgi-bin/nph-zms?scale=100&width=640px&height=480px&mode=jpeg&maxfps=30&monitor="+$(this).data('monitor')+"&"+$(this).data('auth'));
		$("[id='mdCCTV']").modal();
	});


	$(".locales_add_solicitud_modal_btn")
	.off()
	.click(function(event) {
		locales_add_solicitud_modal("show");
	});

	$("#select-tipo_solicitud_id")
	.change(function(event) {
		loading(true);
		$("#select-subtipo_solicitud_id").html("");
		$("#select-subtipo_solicitud_id").append($("<option>").html("- Seleccione un SubTipo -").val(""));
		$("#select-subtipo_solicitud_id").attr('disabled',"disabled");
		var data = $(this).val();
		if(data==1){
			$(".div_monto").show('200');
		}else{
			$("#varchar_monto_solicitud").val("");
			$(".div_monto").hide('200');
			$("#varchar_ticket_solicitud").val("");
			$(".div_ticket").hide('200');
			$(".div_pendiente").hide('200');
		};
		$.post('sys/get_subtipo_solicitud.php', {"tipo_solicitud":data}, function(r, textStatus, xhr) {
			var response = jQuery.parseJSON(r);
			$.each(response, function(index, val) {
				$("#select-subtipo_solicitud_id").append($("<option>").html(val.descripcion).val(val.id));
			});
			$("#select-subtipo_solicitud_id").attr('disabled',false);
			loading(false);
		});
	});



	$("#select-razon_social_id")
	.change(function(event) {
		var razon_social_id = $('#select-razon_social_id').val();
		if (razon_social_id == "0") {
			$('#select_zona_id_local').find("option").remove().end();
			$('#select_zona_id_local').append('<option value="0">- Seleccione -</option>');
			return false;
		}
		$.ajax({
			url: "/sys/get_locales.php",
			type: "POST",
			data: { 
				accion: 'obtener_zonas_por_empresa',
				razon_social_id: razon_social_id,
			}, //+data,
			beforeSend: function () {},
			complete: function () {},
			success: function (datos) {
				var respuesta = JSON.parse(datos);
				$('#select_zona_id_local').find("option").remove().end();
				$('#select_zona_id_local').append('<option value="0">- Seleccione -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$('#select_zona_id_local').append(opcion);
				});
			},
			error: function () {},
		});
	});



	$("#select-subtipo_solicitud_id")
	.change(function(event) {
		loading(true);
		if($("#select-tipo_solicitud_id").val()==1){
			if( $(this).val()==1 ){
				$(".div_pendiente").show('200');
				$("#varchar_ticket_solicitud").val("");
				$(".div_ticket").hide('200');
				$.post('sys/get_locales.php', {"subtipo_solicitud":$("#varchar_local_solicitud").val()}, function(r, textStatus, xhr) {
					var response = jQuery.parseJSON(r);
					$("#varchar_pendiente_solicitud").val((response[0].suma==null)?0:response[0].suma);
					loading(false);
				});
			}
			else if($(this).val()==2){
				$(".div_pendiente").hide('200');
				$(".div_ticket").show('200');
				loading(false);
			}
			else{
				$(".div_pendiente").hide('200');
				$("#varchar_ticket_solicitud").val("");
				$(".div_ticket").hide('200');
				loading(false);
			}
		}
		else{
			loading(false);
		}
	});


	$(".locales_ver_solicitud_modal_btn")
	.off()
	.click(function(event) {
		var solicitud_id=$(this).data('id');
		var estado=$(this).data('estado');
		var bet_id=$(this).data('bet_id');
		if(bet_id==""){bet_id=0;}
		locales_ver_solicitud_modal("show",solicitud_id,bet_id,estado);
	});

	$("[id='btnExportsolicitudes']").off().on('click', function(event) {
		event.preventDefault();
		loading(true);

		local_id=$("#item_id").val();
		var get_data ={
			"opt": 'sec_locales_get_solicitudes_export',
			"data":local_id
		};

		$.ajax({
			url: '/export/local_solicitudes.php',
			type: 'POST',
			data: get_data,
		})
		.done(function(dataresponse) {
			console.log(dataresponse);
			var obj = JSON.parse(dataresponse);
			window.open(obj.path);
		})
		.always(function(data){
			loading();
		});
	});

	$("[id='btn_imprimir_detalle_solicitud']").off().on('click', function(event) {
		event.preventDefault();
		//loading(true);

		solicitud_id=$("#btn_imprimir_detalle_solicitud").data('id');
		monto_ticket=$("#btn_imprimir_detalle_solicitud").data('monto_ticket');
		var get_data ={
			"opt": 'sec_locales_get_solicitud_detalle_export',
			"data":solicitud_id,
			"monto_ticket":monto_ticket,
		};

		$.ajax({
			url: '/export/local_solicitudes.php',
			type: 'POST',
			data: get_data,
		})
		.done(function(dataresponse) {
			console.log(dataresponse);
			var obj = JSON.parse(dataresponse);
			window.open(obj.path);
		})
		.always(function(data){
			loading();
		});
	});

	/* filtro para listado de promociones marketing */
	$('#idInputFechaFiltroCreacion').change(function() {
		
		fncMarketingPromocionListarPromociones();
	});
	
	// INICIO: SERVICIO PUBLICO

	$("#sec_locales_tab_servicio_publico_btn_nuevo").off("click").on("click",function(){
		
		$('#sec_locales_tab_servicio_publico_form_modal_param_id').val(0);
    	$("#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio").val(0).trigger("change.select2");
		$("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").val(0).trigger("change.select2");
		$("#sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago").val(0).trigger("change.select2");
        $('#sec_locales_tab_servicio_publico_form_modal_param_monto_o_porcentaje').val(0).trigger("change.select2");
		$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_txt_mensaje").html("");
		$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_contometro_txt_mensaje").html("");
		$('#sec_locales_tab_servicio_publico_form_modal_param_mes_facturado').val("");
		$("#sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro").val("");
		$('#sec_locales_tab_servicio_publico_form_modal_param_tipo_documento').val(0).trigger("change.select2");
        $('#sec_locales_tab_servicio_publico_form_modal_param_importe').val("");
		$('#sec_locales_tab_servicio_publico_form_modal_param_comentario').val("");
		
		$("#sec_locales_tab_servicio_publico_modal_nuevo_servicio").modal("show");
		$("#container-vista-recibo").hide();
		$("#container-nuevo-archivo").show();
		$("#sec_locales_tab_servicio_publico_modal_form_titulo").text("Nuevo de servicio público");
	})

	$('#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio').change(function () 
	{
	    $("#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio option:selected").each(function ()
	    {   
	        var selectValor = $(this).val();

	        if(selectValor != 0)
	        {
	        	var local_id = $("#sec_locales_tab_servicio_publico_form_modal_param_local_id").val();
	            sec_locales_tab_servicio_publico_listar_codigo_suministro(local_id, selectValor,null);
				$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_txt_mensaje").html("");
				$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_contometro_txt_mensaje").html("");
	        }
	        else
	        {
	        	alertify.error('Seleccione Tipo Servicio',5);
		        $("#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio").focus();
		        setTimeout(function() 
		        {
		            $('#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio').select2('open');
		        }, 200);

		        return false;
	        }
	    });
	});

	$('#sec_locales_tab_servicio_publico_form_modal_param_num_suministro').change(function () 
	{
		
	    $("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro option:selected").each(function ()
	    {   
	    	
	        var selectValor = $(this).val();

	        if(selectValor != 0)
	        {
	        	sec_locales_tab_servicio_publico_mostrar_compromiso_pago(selectValor);
	        }
	        else
	        {
	        	alertify.error('Seleccione Nº Suministro',5);
		        $("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").focus();
		        setTimeout(function() 
		        {
		            $('#sec_locales_tab_servicio_publico_form_modal_param_num_suministro').select2('open');
		        }, 200);

		        return false;
	        }
	    });
	});

	$("#sec_locales_tab_servicio_publico_form_modal_param_comentario").keyup(function ()
	{
		$("#sec_locales_tab_servicio_publico_form_modal_param_comentario_txt_caracteres").text(255 - $(this).val().length)
	});

	function sec_locales_tab_servicio_publico_mostrar_compromiso_pago(inmueble_suministro_id) 
	{   
	    var data = {
	        "accion": "sec_locales_tab_servicio_publico_mostrar_compromiso_pago",
	        "inmueble_suministro_id": inmueble_suministro_id
	    }
	    
	    var array_compromiso_pago = [];
	    
	    $.ajax({
	        url: "/sys/set_locales.php",
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
	            auditoria_send({ "respuesta": "sec_locales_tab_servicio_publico_mostrar_compromiso_pago", "data": respuesta });
	            
				var html = '';
				var html_tipo_compromiso_pago_id = '';
				var html_option_monto_o_porcentaje = '';
				var html_texto_mensaje = '';

	            if(parseInt(respuesta.http_code) == 400) 
	            {
					$("#sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago").html(html).trigger("change");
					$("#sec_locales_tab_servicio_publico_form_modal_param_monto_o_porcentaje").html(html_option_monto_o_porcentaje).trigger("change");
					$("#sec_locales_tab_servicio_publico_form_modal_label_texto_monto_o_porcentaje").html(html_texto_mensaje).trigger("change");	

					$("#sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago_id").val("0");

					return false;

	            }
	            else if(parseInt(respuesta.http_code) == 200) 
	            {
	            	array_compromiso_pago.push(respuesta.result);
	            	
	            	for (var i = 0; i < array_compromiso_pago[0].length; i++) 
	                {
						html += '<option value=' + array_compromiso_pago[0][i].id  + '>' + array_compromiso_pago[0][i].nombre + '</option>';
	                    html_texto_mensaje += '<option value=' + array_compromiso_pago[0][i].id  + '>' + array_compromiso_pago[0][i].texto_mensaje + ':' + '</option>';
	                    html_option_monto_o_porcentaje += '<option value=' + array_compromiso_pago[0][i].id  + '>' + array_compromiso_pago[0][i].monto_o_porcentaje + '</option>';

	                    $("#sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago_id").val(array_compromiso_pago[0][i].tipo_compromiso_pago_id);

	                    // COMPROMISO DE PAGOS
	                    // SELECT * FROM cont_tipo_pago_servicio

	                    // 5: Compartido
						// 4: Contometro
						// 6: Excedente monto base
						// 7: Factura
						// 3: Medidor propio (totalidad del servicio)
						// 2: Monto Fijo
						// 8: NO se paga
						// 1: Porcentaje (%)

						$("#sec_locales_tab_servicio_publico_form_modal_div_archivo_contometro").hide();

	                    if(array_compromiso_pago[0][i].tipo_compromiso_pago_id == 1 
	                		|| array_compromiso_pago[0][i].tipo_compromiso_pago_id == 2
	                		|| array_compromiso_pago[0][i].tipo_compromiso_pago_id == 6
	                		|| array_compromiso_pago[0][i].tipo_compromiso_pago_id == 7)
	                	{
	                		$("#sec_locales_tab_servicio_publico_form_modal_div_monto_o_porcentaje").show();

							if(array_compromiso_pago[0][i].tipo_compromiso_pago_id == 2)
	                		{
								$("#sec_locales_tab_servicio_publico_form_modal_div_archivo_contometro").show();
								
	                		}
	                	}
	                	else
	                	{
	                		$("#sec_locales_tab_servicio_publico_form_modal_div_monto_o_porcentaje").hide();

	                		if(array_compromiso_pago[0][i].tipo_compromiso_pago_id == 4 || array_compromiso_pago[0][i].tipo_compromiso_pago_id == 2)
	                		{
								$("#sec_locales_tab_servicio_publico_form_modal_div_archivo_contometro").show();
								
	                		}
	                		else
	                		{
	                			$("#sec_locales_tab_servicio_publico_form_modal_div_archivo_contometro").hide();
	                		}
	                	}
	                }

					$("#sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago").html(html).trigger("change");
					$("#sec_locales_tab_servicio_publico_form_modal_param_monto_o_porcentaje").html(html_option_monto_o_porcentaje).trigger("change");
					$("#sec_locales_tab_servicio_publico_form_modal_label_texto_monto_o_porcentaje").html(html_texto_mensaje).trigger("change");
					
					
					return false;
	            }
	        },
	        error: function() {}
	    });
	}


	//ESTE ES PARA LAS ALERTAS EN DIV AUTOMATICAMENTE - INICIO

	var claseTipoAlertas = 
	{
		alertaSuccess: 1,
		alertaInfo: 2,
		alertaWarning: 3,
		alertaDanger: 4
	};

	function RecuperarClaseAlerta(valor)
	{
		var clase = "";
		switch(valor)
		{
			case 1 : clase = 'alert alert-success alerta-dismissible';
			break;

			case 2 : clase = 'alert alert-info alerta-dismissible';
			break;

			case 3 : clase = 'alert alert-warning alerta-dismissible';
			break;

			case 4 : clase = 'alert alert-danger alerta-dismissible';
			break; 
		}

		return clase;
	}

	function tipoFont(valor)
	{
		var clase = "";
		switch(valor)
		{
			case 1:
			case 2: clase = "<i class='fa fa-info-circle fa-2x'></i>";
			break;

			case 3:
			case 4: clase = "<i class='fa fa-exclamation-triangle fa-2x'></i>";
			break;

		}

		return clase;
	}

	var mensajeAlerta = function (titulo, mensaje, tipoClase, controlDiv)
	{
		var clase = RecuperarClaseAlerta(tipoClase);
		var font = tipoFont(tipoClase);
		var control = $(controlDiv);
		var divMensaje = "<div class = '"+ clase +"' role = 'alert'>";
		divMensaje += "<button type = 'button' class = 'close' data-dismiss = 'alert' aria-label = 'close'>";
		divMensaje += "<span aria-hidden = 'true'>&times;</span>";
		divMensaje += "</button>";
		divMensaje += font + "<strong>" + titulo + "</strong><br/>" + mensaje;
		divMensaje += "</div>";
		control.empty();
		control.hide().html(divMensaje.toString()).fadeIn(2000).delay(8000).fadeOut("slow");
	}

	//ESTE ES PARA LAS ALERTAS EN DIV AUTOMATICAMENTE - FIN

	sec_locales_tab_servicio_publico();

	// ESTE ES PARA LA SELECCION DEL FILE RECIBO - INICIO
	sec_locales_tab_servicio_publico_seleccionar_archivo($('#sec_locales_tab_servicio_publico_form_modal_param_archivo'));

	function sec_locales_tab_servicio_publico_seleccionar_archivo(object){

		$(document).on('click', '#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_servicio_publico', function(event) {

			event.preventDefault();
			object.click();
		});

		object.on('change', function(event) {

			//let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
			if($(this)[0].files.length <= 1)
			{
				const name = $(this).val().split(/\\|\//).pop();
				//truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
				truncated = name;
			}
			else
			{
				truncated = "";
				mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
				$("#sec_locales_tab_servicio_publico_form_modal_param_archivo").val("");
			}

			$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_txt_mensaje").html(truncated);

		});
	}
	// ESTE ES PARA LA SELECCION DEL FILE RECIBO- FIN

	// ESTE ES PARA LA SELECCION DEL FILE CONTOMETRO - INICIO
	sec_locales_tab_servicio_publico_seleccionar_archivo_contometro($('#sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro'));

	function sec_locales_tab_servicio_publico_seleccionar_archivo_contometro(object){

		$(document).on('click', '#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_contometro_servicio_publico', function(event) {

			event.preventDefault();
			object.click();
		});

		object.on('change', function(event) {

			//let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
			if($(this)[0].files.length <= 1)
			{
				const name = $(this).val().split(/\\|\//).pop();
				//truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
				truncated = name;
			}
			else
			{
				truncated = "";
				mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
				$("#sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro").val("");
			}

			$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_contometro_txt_mensaje").html(truncated);

		});
	}
	// ESTE ES PARA LA SELECCION DEL FILE CONTOMETRO- FIN


	// ESTE ES PARA INSERTAR DATOS DE SERVICIO PUBLICOS - INICIO

	function validateRecibo(rec){
		let recibo = rec
		let res = [];
		var data = {
			'accion': 'verificar_num_recibo',
			'num_recibo': rec
		};
		$.ajax({
			async: false,
			url: "sys/set_locales.php",
			data : data,
			type : "POST",
			dataType : "json",
			beforeSend: function() {
				loading("true");
			},
			complete: function() {
				loading();
			},
			success: function(data){
				console.log(data);
				var respuesta = JSON.parse(data);
				res = respuesta;
				
			}
		});
		return res;
	}

	$("#sec_locales_tab_servicio_publico_modal_nuevo_servicio .btn_guardar").off("click").on("click",function()
	{
		var param_id = $('#sec_locales_tab_servicio_publico_form_modal_param_id').val();
		var param_nombre_recibo = $("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_txt_mensaje").html();
		var param_local_id = $('#sec_locales_tab_servicio_publico_form_modal_param_local_id').val();
		var param_tipo_servicio = $('#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio').val();
		var param_num_suministro = $('#sec_locales_tab_servicio_publico_form_modal_param_num_suministro').val();
		var param_compromiso_pago_id = $('#sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago_id').val();
		var param_archivo_contometro = document.getElementById("sec_locales_tab_servicio_publico_form_modal_param_archivo_contometro");
		var param_nombre_archivo_contometro = $("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_contometro_txt_mensaje").html();
		var param_fecha_mes_facturado = $('#sec_locales_tab_servicio_publico_form_modal_param_mes_facturado').val();
		var param_archivo = document.getElementById("sec_locales_tab_servicio_publico_form_modal_param_archivo");
		var param_tipo_documento = $('#sec_locales_tab_servicio_publico_form_modal_param_tipo_documento').val();
		var param_importe = $('#sec_locales_tab_servicio_publico_form_modal_param_importe').val();
		var monto_coma_length = param_importe.replace(/,/g, '');
    	var monto_length = monto_coma_length.replace('.', '');
		var param_comentario = $('#sec_locales_tab_servicio_publico_form_modal_param_comentario').val().trim();

		if(param_local_id == "" || param_local_id == "0")
		{
			alertify.error('No se encontro el ID de la tienda, porfavor refrescar la página',5);
			return false;
		}
		if(param_tipo_servicio == "0")
		{
			alertify.error('Seleccione Tipo Servicio',5);
	        $("#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio").focus();
	        setTimeout(function() 
	        {
	            $('#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio').select2('open');
	        }, 200);

	        return false;
		}

		if(param_num_suministro == "0")
		{
			alertify.error('Seleccione Nº Suministro',5);
	        $("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").focus();
	        setTimeout(function() 
	        {
	            $('#sec_locales_tab_servicio_publico_form_modal_param_num_suministro').select2('open');
	        }, 200);

	        return false;
		}

		if(param_compromiso_pago_id == 4)
		{
			if(param_archivo_contometro.files.length == 0)
			{
				alertify.error('Seleccione el Archivo -  Contometro',5);
				return false;
			}

			if(param_archivo_contometro.files[0].size > 1000000)
			{
				alertify.error('EL Archivo - Contometro debe pesar menos de 1MB',5);
				return false;
			}	
		}
		else if(param_compromiso_pago_id == 0)
		{
			alertify.error('No se encontro el ID del compromiso de pago, porfavor refrescar la página',5);
			return false;
		}

		if(param_fecha_mes_facturado == "")
		{
			alertify.error('Seleccione Mes Facturado',5);
	        $("#sec_locales_tab_servicio_publico_form_modal_param_mes_facturado").focus();
	        
	        return false;
		}

		if(param_nombre_recibo.length == 0)
		{
			alertify.error('Seleccione el Archivo - Recibo',5);
			return false;
		}

		if(param_id == 0)
		{
			if(param_archivo.files.length == 0)
			{
				alertify.error('Seleccione el Archivo - Recibo',5);
				return false;
			}

			if(param_archivo.files[0].size > 1000000)
			{
				alertify.error('EL Archivo - Recibo debe pesar menos de 1MB',5);
				return false;
			}
		}
		if(param_tipo_documento == "0")
		{
			alertify.error('Seleccione Tipo Documento',5);
	        $("#sec_locales_tab_servicio_publico_form_modal_param_tipo_documento").focus();
	        setTimeout(function() 
	        {
	            $('#sec_locales_tab_servicio_publico_form_modal_param_tipo_documento').select2('open');
	        }, 200);

	        return false;
		}

		if(param_compromiso_pago_id == 4)
		{
			// param_compromiso_pago_id = 4 => CONTOMETRO
			if(param_importe == "")
			{
				alertify.error('Ingrese Importe S/',5);
		        $("#sec_locales_tab_servicio_publico_form_modal_param_importe").focus();
		        
		        return false;
			}
		}
		else
		{
			if(param_importe == "" || param_importe == 0)
			{
				alertify.error('Ingrese Importe S/',5);
		        $("#sec_locales_tab_servicio_publico_form_modal_param_importe").focus();
		        
		        return false;
			}
		}

		if(monto_length.length > 9)
	    {
	        alertify.error('El Importe se permite 9 digitos, incluye 2 decimales',5);
	        $("#sec_locales_tab_servicio_publico_form_modal_param_importe").focus();
	        return false;
	    }
		var test = new FormData($("#sec_locales_tab_servicio_publico_form_modal_guardar_servicio_publico")[0]);
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
					var dataForm = new FormData($("#sec_locales_tab_servicio_publico_form_modal_guardar_servicio_publico")[0]);
					dataForm.append("accion","sec_locales_tab_servicio_publico_guardar_nuevo_servicio");
					dataForm.append("param_nombre_recibo", param_nombre_recibo);
					dataForm.append("param_nombre_archivo_contometro", param_nombre_archivo_contometro);
					
					$.ajax({
						url: "sys/set_locales.php",
						type: 'POST',
						data: dataForm,
						cache: false,
						contentType: false,
						processData: false,
						beforeSend: function() {
							loading("true");
						},
						complete: function() {
							loading();
						},
						success: function(data){
							
							var respuesta = JSON.parse(data);
							auditoria_send({ "respuesta": "sec_locales_tab_servicio_publico_guardar_nuevo_servicio", "data": respuesta });
							if(parseInt(respuesta.http_code) == 200)
							{
								swal({
									title: "Registro exitoso",
									text: "Se registró satisfactoriamente",
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
							else if(parseInt(respuesta.http_code) == 400) 
							{
								swal({
									title: respuesta.status,
									text: respuesta.error,
									html:true,
									type: "warning",
									closeOnConfirm: false,
									showCancelButton: false
								});
								return false;
							}
							else {
								swal({
									title: respuesta.status,
									text: respuesta.error,
									html:true,
									type: "warning",
									closeOnConfirm: false,
									showCancelButton: false
								});
								return false;
							}
						}
					});
				}
			});		
	})
		
		/*$(document).on('submit', "#formularioLocalesServicioPublico", function(e) 
		{
			debugger;
			var txt_id_local = $("#txt_id_local").val();
			var txt_nombre_tienda = $("#txt_nombre_tienda").val();
			
			var txt_locales_tipo_servicio_publico = $("#txt_locales_tipo_servicio_publico").val();
			var txt_locales_servicio_publico_fecha_emision = $("#txt_locales_servicio_publico_fecha_emision").val();
			var txt_locales_servicio_publico_fecha_vencimiento = $("#txt_locales_servicio_publico_fecha_vencimiento").val();
			
		var txt_locales_servicio_publico_monto_total = $("#txt_locales_servicio_publico_monto_total").val();
		var sec_locales_tab_servicio_publico_form_modal_param_archivo = $('#sec_locales_tab_servicio_publico_form_modal_param_archivo').val();
		
		if(txt_locales_servicio_publico_monto_total == "")
		{
			txt_locales_servicio_publico_monto_total = 0;
		}

		var txt_locales_servicio_publico_periodo_consumo = $("#txt_locales_servicio_publico_periodo_consumo").val();
		var sec_locales_tab_servicio_publico_form_modal_param_comentario = $("#sec_locales_tab_servicio_publico_form_modal_param_comentario").val();
		
		if(txt_locales_servicio_publico_periodo_consumo.length == 0)
		{	
			e.preventDefault();
			mensajeAlerta("Advertencia:", "Tiene que seleccionar el periodo de consumo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
			return;
		}

		if(txt_locales_servicio_publico_fecha_emision.length == 0)
		{	
			e.preventDefault();
			mensajeAlerta("Advertencia:", "Tiene que seleccionar una fecha de emision.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
			return;
		}
		if(txt_locales_servicio_publico_fecha_vencimiento.length == 0)
		{	
			e.preventDefault();
			mensajeAlerta("Advertencia:", "Tiene que seleccionar una fecha de vencimiento.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
			return;
		}


		if(sec_locales_tab_servicio_publico_form_modal_param_archivo == "")
		{	
			e.preventDefault();
			mensajeAlerta("Advertencia:", "Tiene que seleccionar un recibo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
			return;
		}

		e.preventDefault();
		var form_data = (new FormData(this));
		form_data.append("post_locales_servicio_publico", 1);
		form_data.append("txt_id_local", txt_id_local);
		form_data.append("txt_nombre_tienda", txt_nombre_tienda);
		form_data.append("txt_locales_tipo_servicio_publico", txt_locales_tipo_servicio_publico);
		form_data.append("txt_locales_servicio_publico_fecha_emision", txt_locales_servicio_publico_fecha_emision);
		form_data.append("txt_locales_servicio_publico_fecha_vencimiento", txt_locales_servicio_publico_fecha_vencimiento);
		form_data.append("txt_locales_servicio_publico_monto_total", txt_locales_servicio_publico_monto_total);
		form_data.append("txt_locales_servicio_publico_periodo_consumo", txt_locales_servicio_publico_periodo_consumo);
		form_data.append("sec_locales_tab_servicio_publico_form_modal_param_comentario", sec_locales_tab_servicio_publico_form_modal_param_comentario);


		

		loading(true);

		$.ajax({
			url: "/sys/set_locales.php",
			type: "POST",
			data: form_data,
			cache: false,
			contentType: false,
			processData:false,
			success: function(response) {
				

				result = JSON.parse(response);
				console.log(response);
				loading(false);

				if(result.status)
				{

					$("#txt_locales_servicio_publico_monto_total").val('');
					$("#sec_locales_tab_servicio_publico_form_modal_param_comentario").val('');
					$("#txt_locales_servicio_publico_fecha_emision").val('');
					$("#txt_locales_servicio_publico_fecha_vencimiento").val('');
					$("#sec_locales_tab_servicio_publico_form_modal_param_archivo").val('');
					
					
					swal(result.message, "", "success");

				}
				else
				{
					swal(
					{
						type: "warning",
						title: "Alerta!",
						text: result.message,
						html: true,
					});
				}
				//filter_archivos_table(0);
			},
			always: function(data){
				loading(false);
				console.log(data);
			}
		});
	});*/
	// ESTE ES PARA INSERTAR DATOS DE SERVICIO PUBLICOS - FIN



	// FIN: SERVICIO PUBLICO

	$('div[data-servicio_id="17"] input[name="proveedor_id"]').keyup(function(event){
		$('div[data-servicio_id="17"] input[name="proveedor_id"]').val(this.value)
	});

	$('.btn_edit_local_caja_detalle_tipos').click(function(event){
		
		var local_caja_detalle_id = event.currentTarget.dataset.id
		console.log(local_caja_detalle_id)
		locales_add_caja_cdv_modal({ opt: 'show', accion: 'edit', local_caja_detalle_id: local_caja_detalle_id });

	})
	
	$('.btn_view_local_caja_detalle_historial').click(function(event){
		
        const id = event.currentTarget.dataset.id;
        $('#modalLocalCajaDetalleHistoricoCambios').modal('show');
        sec_local_caja_detalle_historico(id);	
	})

	function sec_local_caja_detalle_historico(id){

        var data = {
            accion: "get_historico_local_caja_detalle",
            local_caja_detalle_tipos_id: id
        };
        $("#modal_local_caja_detalle_historico_div_tabla").show();

        var columnDefs = [{
            className: 'text-center',
            targets: [0, 1, 2, 3, 4, 5, 6]
        }];

        var tabla = crearDataTable(
            "#modal_local_caja_detalle_historico_datatable",
            "/sys/set_locales.php",
            data,
            columnDefs
        );

         tabla.on('init.dt', function () {
            $('.dataTables_filter').hide();
        });
        
    }
}
var  ev;

function sec_locales_tab_servicio_publico()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_locales_tab_servicio_publico_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	// INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_locales_tab_servicio_publico_datepicker')
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

	$("#sec_locales_tab_servicio_publico_form_modal_param_importe").on({
        "focus": function (event) {
            $(event.target).select();
        },
        "change": function (event) {
            if(parseFloat($(event.target).val().replace(/\,/g, ''))>0)
            {
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
}

function sec_locales_tab_servicio_publico_listar_servicios()
{
	$("#divPanelComentarioRecibo").hide();

	var param_tipo_servicio = $("#sec_locales_tab_servicio_publico_param_tipo_servicio").val();
	var param_local_id = $("#sec_locales_tab_servicio_publico_form_modal_param_local_id").val();

	if(param_tipo_servicio == 0)
	{
		alertify.error('Seleccione Tipo Servicio',5);
        $("#sec_locales_tab_servicio_publico_param_tipo_servicio").focus();
        setTimeout(function() 
        {
            $('#sec_locales_tab_servicio_publico_param_tipo_servicio').select2('open');
        }, 200);

        return false;
	}

	var data = {
		"accion": "cont_listar_locales_contabilidad_reporte",
		"param_tipo_servicio" : param_tipo_servicio,
		"param_local_id" : param_local_id
	}

	$.ajax({
		url : "/sys/set_locales.php",
		data : data,
		type : "POST",
		dataType : "json",
		beforeSend: function( xhr ) {
			loading(true);
		},
		success : function(response)
		{
			
			console.log(response.resultado);

			tabla = $("#locales_servicio_publico_datatable").dataTable({
				language : {
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
				"bDestroy" : true,
				aLengthMenu:[10, 15, 20],
				"order": [[ 0, 'desc' ]],
				"data" : response.resultado.aaData,
				"columns" : [

					{
						"data" : "0"
					},
					{
						"data" : "1"
					},
					{
						"data" : "2"
					},
					{
						"data" : "3"
					},
					{
						"data" : "4"
					},
					{
						"data" : "5"
					},
					{
						"data" : "6"
					},
					{
						"data" : "7"
					},
					{
						"data" : "8"
					}

				]

			}).DataTable();
		},
		complete: function(){
			loading(false);
		}
	});
}

function sec_locales_servicio_publico_modal_voucher_pago(recibo_id)
{
	var data = {
		"accion": "sec_locales_servicio_publico_modal_voucher_pago",
		"recibo_id": recibo_id
	}

	auditoria_send({ "proceso": "sec_locales_servicio_publico_modal_voucher_pago", "data": data });

	$.ajax({
		url: "/sys/set_locales.php",
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
			auditoria_send({ "respuesta": "sec_locales_servicio_publico_modal_voucher_pago", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#sec_locales_servicio_publico_div_modal_body').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});

	$("#sec_locales_servicio_publico_modal_voucher_pago").modal("show");
}


function verComentarioReciboServicioPublico(nombre_file, comentario)
{

	$('#divTextTitulo').html('Recibo: '+nombre_file);
	$('#divComentarioReciboServicioPublicoLocal').show();

	$('#divComentarioReciboServicioPublicoLocal').html(comentario);
	$('#divPanelComentarioRecibo').show();

}

function verFileServicioPublicoEnVisor(tipo_documento, ruta_file) 
{

	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#divVisorPdfModal').html(htmlModal);

		$('#exampleModalPreviewServicio').modal('show');

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


function EliminarReciboServicioPublicoLocales(id_recibo)
{


	swal(
	{
		title: '¿Está seguro de eliminiar el recibo?',
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
			"accion": "cont_eliminar_recibo_servicio_publico",
			"txt_id_recibo_local_servicio_publico" : id_recibo
			}

			$.ajax({
				url : "/sys/set_locales.php",
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

function EditarReciboServicioPublicoLocales(id_recibo)
{

	var data = {
        "accion": "sec_locales_servicio_publico_obtener_por_id",
        "param_id": id_recibo
    }

    $.ajax({
        url: "sys/get_locales.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data) {
        	
            var respuesta = JSON.parse(data);
            auditoria_send({ "respuesta": "sec_locales_servicio_publico_obtener_por_id", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
            	var data_back = respuesta.descripcion;

            	$('#sec_locales_tab_servicio_publico_form_modal_param_id').val(data_back[0].id);
				$('#sec_locales_tab_servicio_publico_form_modal_param_local_id').val(data_back[0].local_id);
            	$("#sec_locales_tab_servicio_publico_form_modal_param_tipo_servicio").val(data_back[0].id_tipo_servicio_publico).trigger("change.select2");
				$("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").val(data_back[0].inmueble_suministro_id).trigger("change.select2");
				sec_locales_tab_servicio_publico_listar_codigo_suministro(data_back[0].local_id, data_back[0].id_tipo_servicio_publico,data_back[0].inmueble_suministro_id);
				$("#sec_locales_tab_servicio_publico_form_modal_param_compromiso_pago").val(data_back[0].tipo_compromiso).trigger("change.select2");
            	$('#sec_locales_tab_servicio_publico_form_modal_param_monto_o_porcentaje').val(data_back[0].monto_pct).trigger("change.select2");
				var mesFacturado = data_back[0].mes_facturado;
				var partes = mesFacturado.split('-');
				var year = partes[0];
				var month = partes[1];
				var formattedValue = year + '-' + (month < 10 ? '0' + month : month);
				$('#sec_locales_tab_servicio_publico_form_modal_param_mes_facturado').val(formattedValue);
				
				$('#sec_locales_tab_servicio_publico_form_modal_param_tipo_documento').val(data_back[0].tipo_documento).trigger("change.select2");
            	$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_txt_mensaje").html(data_back[0].nombre_file);
				$("#sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_contometro_txt_mensaje").html(data_back[0].nombre_file_contometro);
				$('#sec_locales_tab_servicio_publico_form_modal_param_importe').val(data_back[0].monto_total);
				$('#sec_locales_tab_servicio_publico_form_modal_param_comentario').val(data_back[0].comentario);
            	$("#sec_locales_tab_servicio_publico_modal_form_titulo").text("Editar recibo de servicio público");
            	$("#sec_locales_tab_servicio_publico_modal_nuevo_servicio").modal("show");
				$("#container-vista-recibo").show();
				$("#container-nuevo-archivo").show();
				var path_img = "files_bucket/contratos/servicios_publicos/";
				if(data_back[0].id_tipo_servicio_publico == 1){ //Luz
						path_img += "luz/";
					}else{
						path_img += "agua/";
					}
				path_img += data_back[0].nombre_file;
				$('#sec_local_serv_pub_div_imagen_recibo').html('');
				//Validar si la imagen de la BD existe en la carpeta
				var nuevo_id = "sec_serv_pub_img_servicio_publico"+data_back[0].id+"_"+data_back[0].numero_suministro;
				if (data_back[0].extension == "pdf") {
						$('#sec_local_serv_pub_div_imagen_recibo').append(
							'<iframe src="' + path_img + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>'
						);

						$('#sec_local_serv_pub_btn_descargar_imagen_recibo').hide();
						$('#sec_local_serv_pub_div_VerImagenFullPantalla').hide();
						
				}else if (data_back[0].extension == 'jpg' || data_back[0].extension == 'png' || data_back[0].extension == 'jpeg') {
						$('#sec_local_serv_pub_div_imagen_recibo').append(
							'<div class="col-md-12">' +
							'   <div align="center" style="height: 100%; width: 100%;">' +
							'       <img  id="' + nuevo_id + '" src="' + path_img + '" width="300px" height="350px" />' +
							'   </div>' +
							'</div>'
						);
						$('#sec_local_serv_pub_ver_full_pantalla').attr('onClick', 'sec_local_servicio_publico_ver_imagen_full_pantalla("' + path_img + '");');
						$('#sec_local_serv_pub_btn_descargar_imagen_recibo').show();
						$('#sec_local_serv_pub_div_VerImagenFullPantalla').show();
						var ruta = "sec_locales_btn_descargar('"+ data_back[0].ruta_download_file +"');";
						$('#sec_local_serv_pub_descargar_imagen_a').attr('onClick', ruta);
						$("#" + nuevo_id).error(function(){
						  $(this).hide();
						  $('#sec_local_serv_pub_mensaje_imagen').html('La imagen no existe en la carpeta');
						  $('#sec_local_serv_pub_ver_full_pantalla').prop('disabled', true);
						  $('#sec_local_serv_pub_descargar_imagen_a').prop('disabled', true);
						  
						});
						//Fin de Validar si la imagen de la BD existe en la carpeta
				}
            }
            else
            {
            	swal({
                    title: respuesta.titulo,
                    text: respuesta.descripcion,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });

                return false;
            }    
        }
    });
}

function sec_locales_tab_servicio_publico_listar_codigo_suministro(local_id, tipo_servicio, selectedOption) {
    var data = {
        "accion": "sec_locales_tab_servicio_publico_listar_codigo_suministro",
        "local_id": local_id,
        "tipo_servicio": tipo_servicio
    }

    var array_codigo_suministro = [];

    $.ajax({
        url: "/sys/set_locales.php",
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
            auditoria_send({ "respuesta": "sec_locales_tab_servicio_publico_listar_codigo_suministro", "data": respuesta });

            if (parseInt(respuesta.http_code) == 400) {
                var html = '<option value="0">-- Seleccione --</option>';
                $("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").html(html).trigger("change");

                return false;
            } else if (parseInt(respuesta.http_code) == 200) {
                array_codigo_suministro.push(respuesta.result);

                var html = '<option value="0">-- Seleccione --</option>';

                for (var i = 0; i < array_codigo_suministro[0].length; i++) {
                    var optionValue = array_codigo_suministro[0][i].inmueble_suministro_id;
                    var optionText = 'Código: ' + array_codigo_suministro[0][i].codigo_suministro;
                    if (selectedOption !== null && selectedOption === optionValue) {
                        html += '<option value="' + optionValue + '" selected>' + optionText + '</option>';
                    } else {
                        html += '<option value="' + optionValue + '">' + optionText + '</option>';
                    }
                }

                $("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").html(html).trigger("change");

                return false;
            }
        },
        error: function() {}
    });
}


/*
function sec_locales_tab_servicio_publico_listar_codigo_suministro(local_id, tipo_servicio) 
	{   
	    var data = {
	        "accion": "sec_locales_tab_servicio_publico_listar_codigo_suministro",
	        "local_id": local_id,
	        "tipo_servicio": tipo_servicio
	    }
	    
	    var array_codigo_suministro = [];
	    
	    $.ajax({
	        url: "/sys/set_locales.php",
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
	            auditoria_send({ "respuesta": "sec_locales_tab_servicio_publico_listar_codigo_suministro", "data": respuesta });
	            
	            if(parseInt(respuesta.http_code) == 400) 
	            {
	                var html = '<option value="0">-- Seleccione --</option>';
	                $("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").html(html).trigger("change");

	                return false;

	            }
	            else if(parseInt(respuesta.http_code) == 200) 
	            {
	                array_codigo_suministro.push(respuesta.result);
	            
	                var html = '<option value="0">-- Seleccione --</option>';

	                for (var i = 0; i < array_codigo_suministro[0].length; i++) 
	                {
	                    html += '<option value=' + array_codigo_suministro[0][i].inmueble_suministro_id  + '>' + 'Código: ' + array_codigo_suministro[0][i].codigo_suministro + '</option>';
	                }

	                $("#sec_locales_tab_servicio_publico_form_modal_param_num_suministro").html(html).trigger("change");

	                return false;
	            }
	        },
	        error: function() {}
	    });
	}
*/
function get_local_horarios(){

	var data = {}
	data.local_id = $("#item_id").val();
	data.startdate = $('#txtLocalHorarioInicio').val();
	data.enddate = $('#txtLocalHorarioFin').val();

	console.log(data);
	$.post('/sys/get_horarios.php', {"get_local_horarios": data}, function(response) {
		result = JSON.parse(response);
		$('#tblGridHorarioLocales').html(result.body);
		loading();
	});
}

function get_horarios_modal(id){
	var data = {};
	data.id = id;
	data.show = 1; //to not show edit button
	data.date = $('#txtHorarioPerfilDate').html();

	loading(true);
	$.post('/sys/get_horarios.php', {"get_horario_dias_modal": data}, function(response) {
		result = JSON.parse(response);
		$('#tblLocalesHorario').html(result.body);
		loading();
	});
}

function populate_configv2_table() {
	let data = {};
	data.local_id = $("#local_id").val();


	$.post('sys/get_locales.php', {"populate_configv2_table":data}, function(r, textStatus, xhr) {
		$("#tbProductos tbody").html(r);
	});

}

function lp_id_add(data){
	console.log(data);
	var new_id = "new_"+$(".lp_id_item").size();
	var html_holder = $(".ids_holder_"+data.canal_de_venta_id);
	var html_item = $('<div>');
	$(html_item).html('<div class="form-group col-xs-5"><input type="text" class="form-control" name="nombre" value=""></div><div class="form-group col-xs-5"><input type="text" class="form-control" name="proveedor_id"></div><div class="form-group col-xs-2"><button class="btn btn-sm btn-danger pull-right lp_id_del_btn"><span class="glyphicon glyphicon-remove"></span></button></div>');	$(html_holder).append(html_item);
	html_item.addClass('row');
	html_item.addClass('lp_id');
	html_item.addClass('lp_id_item');
	html_item.attr('data-id',new_id);
	html_item.attr('data-servicio_id',data.servicio_id);
	html_item.attr('data-canal_de_venta_id',data.canal_de_venta_id);
	html_item.attr('data-local_id',data.local_id);
	html_item.find('.lp_id_del_btn').attr('data-id', new_id);
	sec_locales_events();
}
function lp_id_del(data) {
	var html_parent = $(".lp_id_item[data-id='"+data.id+"']");
	swal({
		title: "¿Seguro?",
		text: "¡Una vez eliminado no se podrá recuperar!",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Si, borrar!",
		cancelButtonText:"No",
		closeOnConfirm: false
	},
	function(){
		data.table = "tbl_local_proveedor_id";
		data.col = "estado";
		data.val = 0;
		auditoria_send({"proceso":"switch_data","data":data});
		$.post('sys/set_data.php', {
			"opt": 'switch_data'
			,"data":data
		}, function(r, textStatus, xhr) {
			try{
				swal({
					title: "Eliminado",
					text: "El archivo ha sido eliminado.",
					type: "success",
					timer: 400,
					closeOnConfirm: false
				},
				function(){
					html_parent.remove();
					swal.close();
					if(data.update == 1){
						if(data.status == 1){
							changeEstadoProveedor(data.id, 0, 1, data.servicio_id);
						}	
					}
				});
			}catch(err){
				swal({
					title: 'Error en la base de datos',
					type: "warning",
					timer: 2000,
				}, function(){
					swal.close();
					loading();
				});
			}
		});
	});
}

function changeEstadoProveedor(id, new_status, old_status, servicio_id) {
	const data = {
		set_status_proveedor: 'set_status_proveedor',
		local_proveedor_id: id,
		new_status: new_status,
		old_status: old_status,
		servicio_id: servicio_id
	};

	$.ajax({
		url: "/sys/set_locales.php",
		type: "POST",
		data: data,
		success: function (response) {
			const respuesta = JSON.parse(response);
			console.log(respuesta);
			if (respuesta.status === 200) {
				swal("Actualización Exitosa", respuesta.message, "success");
				setTimeout(() => {
					window.location.reload();
				}, 2000);
			} else if (respuesta.status === 500) {
				swal("Error", respuesta.message, "warning");
			}
		},
		error: function () {
			swal("Error", "Ocurrió un problema con la solicitud. Inténtelo de nuevo.", "error");
		},
		complete: function () {
			loading();
		}
	});
}

$("#sec_locales_numero_caja_nombre").change(function ()
{
	$("#sec_locales_numero_caja_nombre").each(function()
	{
		var selectValor = $(this).val();

		if(selectValor == 5)
		{
			$("#sec_locales_numero_caja_nombre_txt").show();
		}
		else
		{
			$("#sec_locales_numero_caja_nombre_txt").hide();
		}

	});
});

function locales_add_caja_modal(opt){
	console.log("locales_add_caja_modal");
	console.log(opt);
	$("#locales_add_caja_modal").modal(opt);
	if(opt=="show"){
		$("#locales_add_caja_modal .add_caja_btn")
		.off()
		.click(function(event) {
			// OBTENER EL TEXTO DE LA OPCION SELECCIONADA
			var combo = document.getElementById("sec_locales_numero_caja_nombre");
  			let param_nombre_texto = combo.options[combo.selectedIndex].text;
			locales_add_caja(param_nombre_texto);
		});
		$("#locales_add_caja_modal .add_caja_cerrar_btn")
		.off()
		.click(function(event) {
			locales_add_caja_modal("hide");
		});
	}
}
function locales_add_caja(param_nombre_texto){
	var save_data = {};
	save_data["param_nombre_texto"] = param_nombre_texto;
	$("#locales_add_caja_modal .add_caja_form .save_col")
	.each(function(index, el) {
		save_data[$(el).attr("name")]=$(el).val();
	});
	$.post('sys/set_locales.php', {
		"opt": 'add_caja'
		,"data":save_data
	}, function(r, textStatus, xhr) {
		try{
			resp = JSON.parse(r);
			if(resp.error)
			{
				swal({
					title: resp.msg,
					text: "",
					type: "warning",
					// timer: 3000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
				return false;
			}
			swal({
				title: "Agregado",
				text: "",
				type: "success",
				// timer: 2000,
				closeOnConfirm: true
			},
			function(){
				m_reload();
				swal.close();
			});
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
	});
	console.log(save_data);
}

function editar_local_caja_detalle_tipo(detalle_tipo){
	var data = {
		"accion": "editar_local_caja_detalle_tipo",
		"id": detalle_tipo.id,
		"nombre": detalle_tipo.nombre,
		"descripcion": detalle_tipo.descripcion,
	}

	$.ajax({
		url : "/sys/set_locales.php",
		data : data,
		type : "POST",
		dataType : "json",
		beforeSend: function( xhr ) {
			loading(true);
		},
		success : function(response)
		{
			
			console.log(response.resultado);

		},
		complete: function(){
			loading(false);
		}
	});
}

function locales_add_caja_cdv_modal(opt){
	console.log("locales_add_caja_cdv_modal");
	console.log(opt);
	$("#locales_add_caja_cdv_modal").modal(opt.opt);
	if(opt.opt=="show"){
		if(opt.local_caja_detalle_id != undefined){
			$('#title-modal-cdv').html('Editar Canal de Venta')
			$('#modal-sec-agregar-cdv').hide();
			$("#locales_add_caja_cdv_modal select[name='detalle_tipos_id']").hide()

			var nombre = $('#tipo_nombre_' + opt.local_caja_detalle_id ).html()
			var descripcion = $('#tipo_descripcion_' + opt.local_caja_detalle_id ).html()

			$("#locales_add_caja_cdv_modal input[name='id']").val(opt.local_caja_detalle_id);
			$("#locales_add_caja_cdv_modal input[name='nombre']").val(nombre);
			$("#locales_add_caja_cdv_modal textarea[name='descripcion']").val(descripcion);

			$("#locales_add_caja_cdv_modal .add_caja_cdv_btn").html('Guardar')

		} else {
			$('#title-modal-cdv').html('Agregar Canal de Venta')
			$('#modal-sec-agregar-cdv').show();
			$("#locales_add_caja_cdv_modal input[name='id']").val('');
			$("#locales_add_caja_cdv_modal select[name='detalle_tipos_id']").show()
			$("#locales_add_caja_cdv_modal .add_caja_cdv_btn").html('Agregar')

			$("#locales_add_caja_cdv_modal select[name='detalle_tipos_id']")
			.off()
			.change(function(event) {
				var val = $(this).val();
				var option_data = $(this).find("option[value='"+val+"']").data();
				console.log(option_data);
					// var custom_name = $(this).data("nombre");
					$("#locales_add_caja_cdv_modal input[name='nombre']").val(option_data.nombre);
					// var custom_name = $(this).data("descripcion");
					$("#locales_add_caja_cdv_modal textarea[name='descripcion']").val(option_data.descripcion);
			})

			$("#locales_add_caja_cdv_modal select[name='detalle_tipos_id']").change();

		}

		$("#locales_add_caja_cdv_modal .add_caja_cdv_btn")
		.off()
		.click(function(event) {
			locales_add_caja_cdv();
		});
		$("#locales_add_caja_cdv_modal .add_caja_cdv_cerrar_btn")
		.off()
		.click(function(event) {
			locales_add_caja_cdv_modal({opt: "hide"});
		});

	}
}
function locales_add_caja_cdv(){
	console.log("locales_add_caja_cdv");
	loading(true);
	var save_data = {};
	$("#locales_add_caja_cdv_modal .add_caja_cdv_form .save_col")
	.each(function(index, el) {
		save_data[$(el).attr("name")]=$(el).val();
	});

	var opt = "add_caja_cdv"
	if(save_data['id'] > 0){
		opt = "editar_local_caja_detalle_tipo";
	}

	$.post('sys/set_locales.php', {
		"opt": opt
		,"data":save_data
	}, function(r, textStatus, xhr) {
		try{
			console.log(r);
			response=JSON.parse(r);
			if(response.error){
				loading();
				swal({
					title: "Error",
					text: response.mensaje,
					type: "error",
					// timer: 1800,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
				return false;
			} else
			if(response.updated){
				auditoria_send({"proceso":"locales_editar_canal_de_venta","data":save_data});
				loading(false);
				swal({
					title: "OK",
					text: response.mensaje,
					type: "success",
					closeOnConfirm: true
				}, function(isConfirmed) {
					if (isConfirmed) {
						m_reload();
					}
				})
				return false;
			}
			auditoria_send({"proceso":"locales_agregar_canal_de_venta","data":save_data});
			loading(false);
			swal({
				title: "Agregado",
				text: "",
				type: "success",
				// timer: 2000,
				closeOnConfirm: true
			}, function(isConfirmed) {
				if (isConfirmed) {
					setTimeout(function() {
						m_reload();
					}, 0); // Se ejecuta después de la finalización de la pila de llamadas actuales
				}
			});
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
	});
	console.log(save_data);
}
function locales_incrementar_saldo_kasnet(){
	loading(true);
	var save_data = {};
	save_data.item_id = item_id;
	save_data.saldo = $("#txtSaldoKasnet").val();
	$.post('sys/set_locales.php', {
		"opt": 'locales_incrementar_saldo_kasnet'
		,"data":save_data
	}, function(r, textStatus, xhr) {
		try{
			loading();
			console.log(r);
			swal({
				title: "Guardado",
				text: "",
				type: "success",
				timer: 200,
				closeOnConfirm: true
			},
			function(){
				auditoria_send({"proceso":"locales_incrementar_saldo_kasnet","data":save_data});
				swal.close();
				m_reload();
			});
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
	});
}

function locales_guardar_caja_config(){
	// console.log("locales_guardar_caja_config");
	loading(true);
	var save_data = {};
	save_data.item_id = item_id;
	save_data.config = {};
	$("#local_cajas_config .local_config_item").each(function(index, el) {
		var config = {};
		config.campo = $(el).attr("name");
		config.valor = $(el).val();
		save_data.config[config.campo]=config.valor;
	});
	// console.log(save_data);
	$.post('sys/set_locales.php', {
		"opt": 'locales_guardar_caja_config'
		,"data":save_data
	}, function(r, textStatus, xhr) {
		var obj = jQuery.parseJSON(r);
		if(obj.error){
			loading();
			// console.log(r);
			swal({
				title: "Error",
				text: obj.msg,
				type: "error",
				timer: 2000,
				closeOnConfirm: true
			});
		}else{
			try{
				loading();
				// console.log(r);
				swal({
					title: "Guardado",
					text: "",
					type: "success",
					timer: 2000,
					closeOnConfirm: true
				},
				function(){
					// m_reload();
					auditoria_send({"proceso":"locales_guardar_monto_inicial","data":save_data});
					swal.close();
				});
			}catch(err){
				ajax_error(true,r,err);//opt,response,catch-error
			}
		}
	});
}

// Inicio funciones agregar personal a locales 
function locales_add_usuario_modal(opt){
	document.getElementById('container-form-locales-usuario_botones').style.display = 'block';
	console.log(opt);
	$("#locales_add_usuario_modal").modal(opt);
	if(opt=="show"){
		$("#locales_add_usuario_modal .add_local_usuario_btn")
		.off()
		.click(function(event) {
			locales_add_usuario();
		});
		$("#locales_add_usuario_modal .add_local_usuario_cerrar_btn")
		.off()
		.click(function(event) {
			locales_add_usuario_modal("hide");
		});
		// $("#locales_add_usuario_modal select[name='detalle_tipos_id']")
		// 	.off()
		// 	.change(function(event) {
		// 		var val = $(this).val();
		// 		var option_data = $(this).find("option[value='"+val+"']").data();
		// 		console.log(option_data);
		// 		// var custom_name = $(this).data("nombre");
		// 		$("#locales_add_usuario_modal input[name='nombre']").val(option_data.nombre);
		// 		// var custom_name = $(this).data("descripcion");
		// 		$("#locales_add_usuario_modal textarea[name='descripcion']").val(option_data.descripcion);
		// 	});
		// $("#locales_add_usuario_modal select[name='detalle_tipos_id']").change();
		// $("#locales_add_usuario_modal .new_usuario_id").val("new");
		$("#locales_add_usuario_modal .new_usuario_id")
		.off()
		.change(function(event) {
			var val = $(this).val();
			$(".new_user_form").addClass('hidden');
			$(".user_locales").addClass('hidden');
			$(".user_locales tbody").html("");
			if(val=="new"){
				$("#locales_add_usuario_modal .new_user_form").removeClass('hidden');
				document.getElementById('container-form-locales-usuario').style.display = 'none';
				document.getElementById('container-form-locales-usuario_botones').style.display = 'none';

				setTimeout(function(){
					$("#locales_add_usuario_modal .new_user_form .set_data").first().focus();
				}, 100);
			}else{

				$.post('/sys/get_locales.php', {
					"usuario_locales": val
				}, function(r) {
					loading();
					try{
						$(".user_locales").removeClass('hidden');
						$(".user_locales tbody").html(r);
						console.log(r);
							// console.log(obj);
						}catch(err){
							// console.log(err);
							// auditoria_send({"proceso":"locales_add_usuario_error_general","data":r});
							// console.log(r);
						}
					});
			}
			console.log(val);
		});
		// $("#locales_add_usuario_modal .new_usuario_id").change();

		$(".select2")
		.select2({
			width:"100%"
		});
	}
}
function locales_add_usuario(){
	// console.log("locales_add_usuario");

	var locales_usuarios_id = $('#locales_usuarios_id').val();
	var locales_personal_id = $('#locales_personal_id').val();

	var set_data = {};
	$(".save_data")
	.each(function(index, el) {
		set_data[$(el).attr("data-col")]=$(el).val();
	});
	$("#locales_add_usuario_modal .set_data")
	.each(function(index, el) {
		set_data[$(el).attr("name")]=$(el).val();
	});
	// console.log(set_data);
	$.post('/sys/set_locales.php', {
		"locales_add_usuario": set_data,
		"locales_personal_id": locales_personal_id,
		"locales_usuarios_id": locales_usuarios_id,
	}, function(r) {
		loading();
		try{
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"locales_add_usuario_error","data":set_data});
				swal({
					title: "¡Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
					custom_highlight($("#locales_add_usuario_modal .set_data[name='"+obj.error_focus+"']"));
					setTimeout(function(){
						$("#locales_add_usuario_modal .set_data[name='"+obj.error_focus+"']").val("").focus();
					}, 10);
				});
			}else{
				// set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"locales_add_usuario_done","data":set_data});
				console.log(obj);
				if(locales_personal_id != 0){
					title =  "¡Usuario asignado!";
				}else{
					title = "¡Usuario asignado!";
				}
				swal({
					title: title,
					text: "",
					type: "success",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					m_reload();
				});
			}
		}catch(err){
			// console.log(err);
			auditoria_send({"proceso":"locales_add_usuario_error_general","data":r});
			// console.log(r);
		}
	});
}


function locales_usuarios_limpiar_form_crear()
{
	$('#locales_usuarios_id').val(0);
	$('#locales_personal_id').val(0);

	$('#locales_usuarios_nombre').val("");
	$('#locales_usuarios_usuario').val("");
	$('#locales_usuarios_apellido').val("");

}

function locales_usuarios_obtener_por_dni(dni) {
	locales_usuarios_limpiar_form_crear();
	var param_dni = $('#locales_varchar_dni').val();

	if(param_dni.length < 8){
		{

			
			swal({
					title: 'Error',
					text: "Ingrese un DNI válido",
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
			
            $("#locales_varchar_dni").focus();

			return false;
		}    
	}else{
		let data = {
            dni : param_dni,
            accion:'locales_usuarios_obtener_por_dni'
        }

   		$.ajax({
            url:  "/sys/get_locales.php",
            type: "POST",
            data:  data,
            beforeSend: function () {
            loading("true");
            },
            complete: function () {
            loading();
            },
            success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status == 200) {
                $('#locales_personal_id').val(respuesta.result.id);
				$('#locales_usuarios_id').val(respuesta.result.usuario_id);
                $('#locales_usuarios_nombre').val(respuesta.result.nombre);
                $('#locales_usuarios_usuario').val(respuesta.result.usuario);
				$('#locales_usuarios_apellido').val(respuesta.result.apellido_paterno);
				var estado = respuesta.result.estado;
				var locales_usuarios_id = $('#locales_usuarios_id').val();

				document.getElementById('container-form-locales-usuario').style.display = 'block';
				document.getElementById('container-form-locales-usuario_botones').style.display = 'block';
				var msg_estado_personal = '';
				if(estado == 0){
					msg_estado_personal = 'El personal está inactivo.';
				} else if(estado == 1){
					msg_estado_personal = 'El personal está activo.';
				}  

				if(locales_usuarios_id != 0){
					aviso= "Usuario y Personal encontrado. Se modificarán sus permisos de acuerdo al área y cargo. <b>" + msg_estado_personal + "</b>";
				}else{
					aviso= "Usuario no encontrado. Se creara el usuario al agregarlo. <b>" + msg_estado_personal + "</b>";

				}
				swal({
					type: 'warning',
					title: 'Aviso',
					text: aviso,
					html:true,
					closeOnConfirm: false,
					showCancelButton: false
				});

                }
            else
                {
				document.getElementById('container-form-locales-usuario').style.display = 'block';
				document.getElementById('container-form-locales-usuario_botones').style.display = 'block';

                swal({
                        title: 'Aviso',
                        text: "Personal no encontrado. Se registrara como nuevo personal y usuario",
                        html:true,
						type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                    });
    
                    return false;
					
                }    
            },
            error: function (resp, status) {},
            });
    	}
	}

// Fin funciones agregar personal a locales 

function locales_add_solicitud(){
	loading(true);
	var save_data = {};
	save_data.values={};
	save_data.values.tipo_solicitud=$("#select-tipo_solicitud_id").val();
	save_data.values.subtipo_solicitud=$("#select-subtipo_solicitud_id").val();
	save_data.values.motivo=$("#varchar_motivo_solicitud").val();
	save_data.values.ticket=$("#varchar_ticket_solicitud").val();
	if(save_data.values.tipo_solicitud==1){
		save_data.values.monto=$("#varchar_monto_solicitud").val();
	}else{
		save_data.values.monto=0;
	};
	if($("#select-subtipo_solicitud_id").val()==1 && parseFloat($("#varchar_pendiente_solicitud").val())==0){
		swal({
			title: "¡Error!",
			text:"No tiene tickets pendientes de pago.",
			html:true,
			type: "warning",
			closeOnConfirm: true
		},
		function(){
			swal.close();
		});
		loading(false);
		return false;
	}
	save_data.values.local=$("#local_solicitud_id").val();
	$.post('/sys/set_solicitud_prestamo.php', {
		"opt": 'locales_guardar_solicitud',
		"data":save_data
	}, function(r) {
		try{
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				save_data.error = obj.error;
				save_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"locales_add_solicitud_error","data":save_data});
				swal({
					title: "Error!",
					text:obj.error_msg,
					html:true,
					type: "warning",
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
			}else{
				auditoria_send({"proceso":"locales_add_solicitud_done","data":save_data});
				swal({
					title: "Solicitud Guardada!",
					text: "",
					type: "success",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					m_reload();
				});
			}
		}catch(err){
			auditoria_send({"proceso":"locales_add_solicitud_error_general","data":r});
		}
		loading(false);
	});
}

function filter_locales_table(page) {
	var get_data 	= {};
	var limit 		= $("#cbLocalesLimit option:selected").val();
	get_data.page 	= page;
	get_data.limit 	= limit;
	get_data.filter = $("#txtLocalesFilter").val();
	console.log(get_data.filter);
	$.post('/sys/get_locales.php', {"get_locales": get_data}, function(response) {
		try{
			result = JSON.parse(response);
			$("#tblLocales > tbody").html(result.body);
				let pag = page+1;
				console.log(result.num_rows);
			$("#paginationLocalesJS").pagination({
				items: result.num_rows,
				pages: parseInt(result.num_rows/limit),
				currentPage: pag,
				itemsOnPage: limit,
				cssStyle: 'light-theme',
				onInit: function(pageNumber, event){
					$('#txtLocalesFilter').val('');
					$('#paginationLocalesJS').attr('data-page',pag);
					$('#paginationLocalesJS').attr('data-ini',page*limit);
                    $("#tblLocales > tbody > tr").each(function(){
						if($(this).index() < limit ){
							$(this).show();
						}else{
							$(this).hide();
						}
					});

				},
				onPageClick: function(pageNumber, event){
						console.log(pageNumber);
						var limit = $("#cbLocalesLimit option:selected").val();
						var newIni = pageNumber*limit;
						var fin = (pageNumber+1)*limit;

						$('#paginationLocalesJS').attr('data-page',pageNumber);
						$('#paginationLocalesJS').attr('data-ini',newIni);
						$('#txtLocalesFilter').val('');
						$("#tblLocales > tbody > tr").each(function(){
							if($(this).index() >= newIni && $(this).index() < fin){
								$(this).show();
							}else{
								$(this).hide();
							}

						});
					}
			});

			// $("#paginationLocales").pagination({
			// 	items: result.num_rows,
			// 	currentPage: page+1,
			// 	itemsOnPage: limit,
			// 	cssStyle: 'light-theme',
			// 	onPageClick: function(pageNumber, event){
			// 		event.preventDefault();
			// 		loading(true);
			// 		filter_locales_table(pageNumber-1);
			// 	}
			// });
		}
		catch(error){
			console.log(error);
		}
		$('#icoLocalesSpinner').hide();
		loading();
	});
}


function searchTable(){

	$('#txtLocalesFilter').on('keyup', function(){
		var value = $(this).val().toLowerCase();
			value = sinAcentos(value);
			console.log(value);
			if(value == "" || value == null){
				try{
					//loading(true);
					console.log('entro');
					filter_locales_table(0);
				}catch(err){
					console.log(err.message);
				};
				loading(true);

			}else{
				$("#tblLocales > tbody > tr").filter(function(){
				 let texto = $(this).text().toLowerCase();
				 console.log(texto);
				 texto = sinAcentos(texto);
				 $(this).toggle(texto.indexOf(value) > -1);
				});
			}

	});
};

function sinAcentos(cadena){
	var chars={
		"á":"a", "é":"e", "í":"i", "ó":"o", "ú":"u",
		"à":"a", "è":"e", "ì":"i", "ò":"o", "ù":"u", "ñ":"n",
		"Á":"A", "É":"E", "Í":"I", "Ó":"O", "Ú":"U",
		"À":"A", "È":"E", "Ì":"I", "Ò":"O", "Ù":"U", "Ñ":"N"}
	var expr=/[áàéèíìóòúùñ]/ig;
	var res=cadena.replace(expr,function(e){return chars[e]});
	return res;
}

function locales_cambiar_estado_solicitud(estado,solicitud_id){
	loading(true);
	var save_data = {};
	save_data.values={};
	save_data.values.estado_solicitud=estado;
	save_data.values.solicitud_id=solicitud_id;
	if(estado==1){
		save_data.values.abonar_a=$("#abonar_a").val();
	}
	$.post('/sys/set_solicitud_prestamo.php', {
		"opt": 'locales_cambiar_estado_solicitud',
		"data":save_data
	}, function(r) {
		try{
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				save_data.error = obj.error;
				save_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"locales_cambiar_estado_solicitud_error","data":save_data});
				swal({
					title: "Error!",
					text:obj.error_msg,
					html:true,
					type: "warning",
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
			}else{
				auditoria_send({"proceso":"locales_cambiar_estado_solicitud_done","data":save_data});
				swal({
					title: "Cambios Guardados!",
					text: "",
					type: "success",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					m_reload();
				});
			}
		}catch(err){
			auditoria_send({"proceso":"locales_add_solicitud_error_general","data":r});
		}
		loading(false);
	});
}
function locales_restore_usuario_password(usuario_id){
	swal({
		title: "¿Seguro?",
		text: "",
		type: "warning",
		showCancelButton: true,
		confirmButtonText: "Si",
		cancelButtonText:"No",
		closeOnConfirm: false
	},
	function(){
		// swal.close();
		var get_data = {};
		get_data.id = usuario_id;
		get_data.local_id = $('#item_id_temporal').val();
		// $(".save_data[name=id]").each(function(index, el) {
		// 	get_data[$(el).attr("name")]=$(el).val();
		// });
		// console.log(get_data);
		$.post('/sys/set_usuarios.php', {
			"sec_usuarios_restore_password": get_data
		}, function(r) {
			loading();
			try{
				var obj = jQuery.parseJSON(r);
				// console.log(obj);
				swal({
					title: "Contraseña cambiada!",
					text: "La nueva contraseña es: "+obj.new_password,
					type: "success",
					closeOnConfirm: true
				},
				function(){
					m_reload();
					swal.close();
				});
				get_data.new_password = obj.new_password;
				auditoria_send({"proceso":"locales_restore_usuario_password","data":get_data});
			}catch(err){
				// console.log(r);
				// console.log(err);
			}
			// console.log(r);
		});
	});
}
function locales_remove_usuario(usuario_id){
	swal({
		title: "¿Seguro?",
		text: "",
		type: "warning",
		showCancelButton: true,
		confirmButtonText: "Si",
		cancelButtonText:"No",
		closeOnConfirm: false
	},
	function(){
		// swal.close();
		var get_data = {};
		$(".save_data")
		.each(function(index, el) {
			get_data[$(el).attr("data-col")]=$(el).val();
		});
		get_data.usuario_id = usuario_id;
		// $(".save_data[name=id]").each(function(index, el) {
		// 	get_data[$(el).attr("name")]=$(el).val();
		// });
		console.log(get_data);
		$.post('/sys/set_locales.php', {
			"locales_remove_usuario": get_data
		}, function(r) {
			loading();
			try{
				// var obj = jQuery.parseJSON(r);
				auditoria_send({"proceso":"locales_remove_usuario","data":get_data});
				// console.log(r);
				swal({
					title: "¡Usuario eliminado de este local!",
					text: "",
					type: "success",
					closeOnConfirm: true
				},
				function(){
					m_reload();
					swal.close();
				});
			}catch(err){
				// console.log(r);
				// console.log(err);
			}
			// console.log(r);
		});
	});
}
function locales_add_solicitud_modal(opt){
	$('#select-tipo_solicitud_id').val('1').trigger('change');
	$("#locales_add_solicitud_modal").modal(opt);
	if(opt=="show"){
		$("#locales_add_solicitud_modal .add_local_solicitud_btn")
		.off()
		.click(function(event) {
			locales_add_solicitud();
		});
		$("#locales_add_solicitud_modal .add_local_solicitud_cerrar_btn")
		.off()
		.click(function(event) {
			locales_add_solicitud_modal("hide");
		});
		$(".select2")
		.select2({
			width:"100%"
		});
	}
}
function locales_ver_solicitud_modal(opt,solicitud_id,bet_id,estado){
	loading(true);
	if(opt=="show"){
		$("#abonar_a").val("");
		var data =solicitud_id;
		var bet_id =bet_id;
		var estado =estado;
		$.post('sys/get_local_solicitud.php', {"solicitud_id":data,"bet_id":bet_id,"estado":estado}, function(r, textStatus, xhr) {
			var response = jQuery.parseJSON(r);
			$(".aprobar_sol_class").data('id_solicitud',response[0].id);
			$(".cancelar_sol_class").data('id_solicitud',response[0].id);
			$("#btn_imprimir_detalle_solicitud").data('id',response[0].id);
			$("#desc_solicitud_motivo").text(response[0].motivo);
			$("#desc_solicitud_monto").text("S/ "+response[0].monto);
			$("#desc_solicitud_bet_id").text(response[0].bet_id);
			if(response[0].transaccion.length>0){
				$("#desc_solicitud_bet_id_monto_ganado").text("S/ "+response[0].transaccion[0].ganado);
				$("#btn_imprimir_detalle_solicitud").data('monto_ticket',"S/ "+response[0].transaccion[0].ganado);
			}
			$("#desc_solicitud_usuario").text(response[0].usuario);
			$("#desc_solicitud_nombre").text(response[0].nombre);
			$("#desc_solicitud_ap_paterno").text(response[0].apellido_paterno);
			$("#desc_solicitud_area").text(response[0].area);
			$("#desc_solicitud_cargo").text(response[0].cargo);
			$("#desc_solicitud_area_cargo").text(response[0].area+" / "+response[0].cargo);
			$("#desc_solicitud_tipo_sol").text(response[0].tipo_solicitud_desc);
			$("#desc_solicitud_subtipo_sol").text(response[0].subtipo_solicitud_desc);
			$("#desc_solicitud_tipo_subtipo_sol").text(response[0].tipo_solicitud_desc+" / "+ response[0].subtipo_solicitud_desc);
			$("#desc_solicitud_fecha_creacion").text(response[0].fecha_creacion);
			var estado="";
			if(response[0].estado==0){estado="Pendiente"};
			if(response[0].estado==1){estado="Aprobado"};
			if(response[0].estado==2){estado="Abonado"};
			if(response[0].estado==3){estado="Cancelado"};
			if(response[0].estado==4){estado="Expirado"};
			if(response[0].estado==5){estado="Recibido"};
			if(response[0].estado==6){estado="Abonado-Eliminacion-Turno"};
			$("#desc_solicitud_estado").text(estado);
			if(response[0].tipo_solicitud==1){
				$(".tr_monto").show();
			}else{
				$(".tr_monto").hide();
			};
			if(response[0].subtipo_solicitud==2){
				$(".tr_bet_id").show();
			}else{
				$(".tr_bet_id").hide();
			}
			if(response[0].cobrado==true){
				$("#div_expirado").show();
				$("#desc_ticket").text(response[0].transaccion[0].ticket_id);
				$("#desc_fecha_pago").text(response[0].transaccion[0].paid_day);
				$("#desc_local_pago").text(response[0].transaccion[0].local_pago);
				$("#desc_monto_pago").text(response[0].transaccion[0].pagado);
				$(".aprobar_sol_class").hide();
				$(".tr_abonar_a").hide();
				$(".cancelar_sol_class").hide();
			}else{
				$("#div_expirado").hide();
				$("#desc_ticket").text("");
				$("#desc_fecha_pago").text("");
				$("#desc_local_pago").text("");
				$("#desc_monto_pago").text("");
				if(response[0].estado==0){
					$(".aprobar_sol_class").show();
					$(".tr_abonar_a").show();
					$(".cancelar_sol_class").show();
				}else{
					$(".aprobar_sol_class").hide();
					$(".tr_abonar_a").hide();
					$(".cancelar_sol_class").hide();
				}
			}
			$("#locales_ver_solicitud_modal").modal(opt);
			loading(false);
		});
		$("#locales_ver_solicitud_modal .ver_local_solicitud_cerrar_btn")
		.off()
		.click(function(event) {
			$(".aprobar_sol_class").data('id_solicitud',0);
			$(".cancelar_sol_class").data('id_solicitud',0);
			$("#btn_imprimir_detalle_solicitud").data('id',0);
			$("#btn_imprimir_detalle_solicitud").data('monto_ticket',0);
			locales_ver_solicitud_modal("hide");
		});
		$("#locales_ver_solicitud_modal .aprobar_sol_class")
		.off()
		.click(function(event) {
			var solicitud_id=$(this).data('id_solicitud');
			locales_cambiar_estado_solicitud(1,solicitud_id);
		});
		$("#locales_ver_solicitud_modal .cancelar_sol_class")
		.off()
		.click(function(event) {
			var solicitud_id=$(this).data('id_solicitud');
			locales_cambiar_estado_solicitud(3,solicitud_id);
		});
	}
	if(opt=="hide"){
		$("#locales_ver_solicitud_modal").modal(opt);
		loading(false);
		m_reload();
	}
}

/*************************************************************************/
/*************************************************************************/
/************** DIRECCIONES MAC ******************************************/
/******************************ASOCIADAS AL*******************************/
/******************************************LOCAL SELECCIONADO*************/
/*************************************************************************/
/*************************************************************************/
/*
function listarAddressMacxLocal(){
	var id_local = $('#sec_locales_setting_id_local').html();
	$('#sec_locales_tabla_mac_address').html('');
	try {
		if(id_local != ""){ // Validar si el local se está editando o es uno nuevo
			var data = {
				"accion": "obtener_address_mac_devices_x_local",
				"id_local" : id_local
			}
			auditoria_send({ "proceso": "obtener_address_mac_devices_x_local", "data": data });
			$.ajax({
		        url: "sys/get_locales.php",
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
		            	//alertify.error('Error: ' + respuesta.status,5);
						return false;
		            }
		            var id_mac = new Array();
		            if (parseInt(respuesta.http_code) == 200) {
		            	$('#sec_locales_mac_address').val('');
		                $.each(respuesta.result, function(index, item) {
							$('#sec_locales_tabla_mac_address').append(
								'<tr>'
								+ '<td>' + item.macAddress + '</td>' 
								+ '<td><button type="button" class="btn btn-sm btn-danger" onclick="eliminarMac(' + item.id + ');">'
								+ '<i class="fa fa-close"></i>'
								+'</button></td>' 
								+ '</tr>'
							);
		                });
		                
		                return false;
		            }      
		        },
		        error: function() {}
		    });
		}
    } catch (e) {
        console.log("Error de TRY-CATCH -- Error: " + e);
    }
}

function eliminarMac(id_mac){
	var id_local = $('#sec_locales_setting_id_local').html();
	try {
		swal({
        title: "Eliminar Dirección MAC",
        text: "¿Está seguro de eliminar la dirección MAC de este local?",
        type: "warning",
        showCancelButton: true,
        cancelButtonText: "NO",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si",
        closeOnConfirm: false
        },
        function (isConfirm) {
            if(isConfirm){
                var data = {
					"accion": "eliminar_device_local",
					"id_mac" : id_mac,
					"id_local" : id_local
				}
				auditoria_send({ "proceso": "eliminar_device_local", "data": data });
				$.ajax({
			        url: "sys/get_locales.php",
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
			            	alertify.error('Error: ' + respuesta.error, 5);
							return false;
			            }

			            if (parseInt(respuesta.http_code) == 200) {
			            	listarAddressMacxLocal();
			                swal({
			                    title: "Eliminado",
			                    text: "Eliminaste el registro.",
			                    type: "success",
			                    timer: 1000
			                });
			                delay(2000);
			            }   
			            
			        },
			        error: function() {}
			    });
            }else{
            	return false;
            }
        });
    	
    } catch (e) {
        console.log("Error de TRY-CATCH -- Error: " + e);
    }
}

$('#sec_locales_mac_address').bind('keypress', function (event) { 
	var regex = new RegExp("^[a-zA-Z0-9]+$"); 
	var key = String.fromCharCode(!event.charCode ? event.which : event.charCode); 
	if (!regex.test(key)) { event.preventDefault(); return false; } 
});

function validarAddressMac(){
	var address = $('#sec_locales_mac_address').val();
	var regex = new RegExp("^[a-zA-Z0-9]+$"); 
	if (address.match(regex)){
		
	}else{
		alertify.error("No se permiten caracteres especiales.",5);
		return false;
	}
	var id_local = $('#sec_locales_setting_id_local').html();
	try {
    	var data = {
			"accion": "validar_address_mac_existe",
			"address" : address
		}
		auditoria_send({ "proceso": "validar_address_mac_existe", "data": data });
		$.ajax({
	        url: "sys/get_locales.php",
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
	            	mac_address_d = "";
	            	local_d ="";
	            	if(parseInt(respuesta.code) == 2){
	            		$.each(respuesta.list_use_local, function(index, item) {
		                	mac_address_d = item.macAddress;
		            		local_d = item.local;
		                });
	            		alertify.error('La dirección MAC está siendo utilizada por el local ' + local_d, 5);
	            		$('#sec_locales_mac_address').val('');
	            	}else if(parseInt(respuesta.code) == 3){
	            		swal({
					        title: "La dirección MAC no existe",
					        text: "¿Desea agregar esta dirección MAC?",
					        type: "warning",
					        showCancelButton: true,
					        cancelButtonText: "NO",
					        confirmButtonColor: "#DD6B55",
					        confirmButtonText: "Si",
					        closeOnConfirm: false
					        },
					        function (isConfirm) {
					            if(isConfirm){
					            	agregarMAC();
					            }else{
					            	return false;
					            }
				        	});
	            	}
					return false;
	            }

	            if (parseInt(respuesta.http_code) == 200) {
	            	$.each(respuesta.list_existe_mac, function(index, item) {
	            		if(id_local != ""){
	                		guardarAddressMAC(item.id, id_local);
	            		}
	                });
	                return false;
	            }      
	        },
	        error: function() {}
	    });
    } catch (e) {
        console.log("Error de TRY-CATCH -- Error: " + e);
    }
}

function guardarAddressMAC(id_mac, id_local){
	try {
    	var data = {
			"accion": "guardar_address_mac_device_local",
			"id_mac" : id_mac,
			"id_local" : id_local
		}
		auditoria_send({ "proceso": "guardar_address_mac_device_local", "data": data });
		$.ajax({
	        url: "sys/get_locales.php",
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
	            	$('#sec_locales_mac_address').val('');
	            	alertify.error('Error: ' + respuesta.error, 5);
	            }

	            if (parseInt(respuesta.http_code) == 200) {
	            	swal({
						title: "Agregado",
						text: "",
						type: "success",
						timer: 1000,
						closeOnConfirm: true
					},
					function(){
						listarAddressMacxLocal();
						swal.close();
					});
	            }   
	            
	        },
	        error: function() {}
	    });
    } catch (e) {
        console.log("Error de TRY-CATCH -- Error: " + e);
    }
}

function agregarMAC(){
	var address = $('#sec_locales_mac_address').val();
	var id_local = $('#sec_locales_setting_id_local').html();

	try {
    	var data = {
			"accion": "agregar_address_mac_new",
			"address" : address,
			"id_local" : id_local
		}

		auditoria_send({ "proceso": "agregar_address_mac_new", "data": data });
		$.ajax({
	        url: "sys/get_locales.php",
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
	            $('#sec_locales_mac_address').val('');
	            	alertify.error('Error: ' + respuesta.error, 5);
	            }

	            if (parseInt(respuesta.http_code) == 200) {
	            	swal({
						title: "Agregado",
						text: "",
						type: "success",
						timer: 1000,
						closeOnConfirm: true
					},
					function(){
						listarAddressMacxLocal();
						swal.close();
					});
	            }   
	            
	        },
	        error: function() {}
	    });
    } catch (e) {
        console.log("Error de TRY-CATCH -- Error: " + e);
    }
}
*/

function fncMarketingPromocionListarPromociones() {
	var numMes = $('#idInputFechaFiltroCreacion').val();
	var data ={
			"accion": 'sec_locales_get_listado_promociones',
			'fechaPromocion':numMes
	}
	$.ajax({
		url: "sys/get_locales_promocion_marketing.php",
		type: 'POST',
        data: data,
        success: function (response) {

            var jsonData = JSON.parse(response);
			$('#idSelectNombrePromociones').empty();
            for (let index = 0; index < jsonData.data.length; index++) {
                // $('#idSelectNombrePromociones').append($('<option>', {
                //     value: jsonData.data[index].id,
                //     text: '[ ' + jsonData.data[index].id + ' ] ' + jsonData.data[index].nombrePromocion.toUpperCase()
                // }));
				$('#idSelectNombrePromociones').append('<option value="'+jsonData.data[index].id+'" data-fecha="'+jsonData.data[index].fechaPromocion+'"> ' + jsonData.data[index].nombrePromocion.toUpperCase()+'</option>');
            }
			if (jsonData.data.length>0) {
				$("#idInputFechaCreacion").val(jsonData.data[0].fechaPromocion);
			}
        }
    });
	if (window.domIsReady) {
		$("#idSelectNombrePromociones").select2({
			placeholder: "Select a state",
			allowClear: true
		});
	}
	
}


$(document).ready(function () {
	
	if ($('#idTablePromocionesMarketingLocal').length==0) {
	} else {
	  fncMarketingPromocionListarPromociones();
	  fncRenderizarDataTableListadoPromocionMarketing();

	  $("#idSelectNombrePromociones").change(function (e) { 
		e.preventDefault();
		$("#idInputFechaCreacion").val($(this).children('option:selected').data('fecha'));
	});
	
	}
	$('#idBtnGuardarPromocionMarketing').on('click', (e) => {
		//debugger;
		e.preventDefault();
		$('#idFormMarketingPromocion').submit();
	    //alert("Check");
		return false;
	});

	$('#idFormMarketingPromocion').validate({
		rules: {
			idInputFechaFiltroCreacion: {
				required: true,
			},
			idSelectNombrePromociones: {
				required: true,
			},
			idInputFilePromocion: {
				required: true,
			}
			
		},
		messages: {
			idInputFechaFiltroCreacion: {
				required: "Por favor, introduce una fecha de Promocion válida",
			},
			idSelectNombrePromociones: {
				required: "Por favor, una promoción es requerida",
			},
			idInputFilePromocion: {
				required: "Por favor, un archivo es requerido",
			}
		},
		submitHandler: function(form) {
			 var formData = fncPromocionMarketingGetDataInsertUpdate();
			// console.log(formData);
			 fncGuardarPromocionMarketing(formData);
			return false;
		}
	});

	function fncPromocionMarketingGetDataInsertUpdate() {
        var formData = new FormData();
		
		formData.append('accion', 'sec_locales_guardar_promocion');
        formData.append('localId', $("#idHiddenLocalId").val());
		formData.append('user_id', $("#idHiddenLoginUserId").val());		
        formData.append('idPromocion', $("#idSelectNombrePromociones").val());
        formData.append('fechaPromocionCreacion', $("#idInputFechaCreacion").val());
		var filesMarketing = document.getElementById('idInputFilePromocion').files.length;
		for (var x = 0; x < filesMarketing; x++) {
			formData.append("filesMarketing[]", document.getElementById('idInputFilePromocion').files[x]);
		}
        return formData;
    }
	function fncGuardarPromocionMarketing(data) {
        $.ajax({
            type: "POST",
            data: data,
            url: 'sys/get_locales_promocion_marketing.php',
            contentType: false,
            processData: false,
            cache: false,
            success: function(response) {
                var jsonData = JSON.parse(response);
                if (jsonData.error == false) {
                    swal("Registrado", jsonData.message, "success");
					fncRenderizarDataTableListadoPromocionMarketing();
					$("#idInputFilePromocion").val(null);
					$("#idImgArchivoPromocionMarketing").attr("src","");
					$('#idTextTituloPromocionMarketing').text("Ninguna Promoción Seleccionada");
					$('#idListArchivosPromocionMarketing').empty();
                } else {
                    swal("Error", jsonData.message, "error");
                }

                // $("#idBtnGuardarPromocionMarketin").val("Agregar Nueva Promoción");
                // $('#idBtnEditarCancelar').hide();
                // $("#idInputNombrePromocion").val("");
                // $("#idPromocion").val("0");
                // $("#idformPromcionesMarketing").validate().resetForm();

                // $("#idInputFechaPromocion").val('');
                // fncRenderizarDataTable();
            }
        });
    }
	function fncRenderizarDataTableListadoPromocionMarketing() {

        var table = $('#idTablePromocionesMarketingLocal').DataTable();
        table.clear();
        table.destroy();
        var table = $('#idTablePromocionesMarketingLocal').DataTable({
            'destroy': true,
            "ajax": {
                type: "POST",
                "url": "sys/get_locales_promocion_marketing.php",
                "data": { idLocal: $("#idHiddenLocalId").val() , accion:'sec_locales_listar_promocion_local' }

            },
            "dataSrc": function(json) {
                console.log(json);
                var result = JSON.parse(json);
                return result.data;
            },
            "columnDefs": [{
                "searchable": false,
                "orderable": false,
                "targets": 0
            }],
            "order": [
                [3, 'desc']
            ],
            "language": {
                url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
            },
            columnDefs: [{
                className: 'text-center',
                targets: [0, 1, 2, 3, 4, 5, 6]
            }, ],
            "columns": [
                
                {
                    "data": "id",
                    render: function(data, type, row) {
                        var codigo = '[' + data + ']';
                        return codigo;
                    }
                },
                {
                    "data": "nombre_promocion"
                },
                {
                    "data": "fecha"
                },
                {
                    "data": "fecha",
                    render: function(data, type, row) {
                        var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                        var fecha = data;
                        var objDate = new Date(Date.parse(fecha));
                        //console.log(objDate.getMonth());
                        return meses[objDate.getMonth()];
                    }
                },
                {
                    "data": "cantidad_archivos",
					render:function name(data, type, row) {
						if(data>0){
							return ('<button id="idBtnVerArchivosPromocionMarketing" class="btn-primary"><i class="fa fa-eye"></i>Ver</button>');
						}else{
							return '--';
						}
					}
                },
                {
                    "data": "created_at"
                },
                {
                    "defaultContent": '',
                    render: function(data, type, row) {
                        var btn = '<button id="idBtnEliminarPromocionLocal" class="btn-danger btn-round"><i class="fa fa-close"></i>Eliminar</button>';
                        return btn;
                    }
                }
            ]


        });
        $('#idTablePromocionesMarketingLocal tbody').off('click');

        $('#idTablePromocionesMarketingLocal tbody').on('click', '#idBtnEliminarPromocionLocal', function() {

            var data = table.row($(this).parents('tr')).data();
            var rowData = null;
            if (data == undefined) {
                var selected_row = $(this).parents('tr');
                if (selected_row.hasClass('child')) {
                    selected_row = selected_row.prev();
                }
                rowData = $('#idTablePromocionesMarketingLocal').DataTable().row(selected_row).data();
            } else {
                rowData = data;
            }
            fncEliminarMarketingPromocion(rowData.id);


        });
		 $('#idTablePromocionesMarketingLocal tbody').on('click', '#idBtnVerArchivosPromocionMarketing', function() {

            var data = table.row($(this).parents('tr')).data();
			var idRow = table.row($(this).parents('tr'));
            var rowData = null;
            if (data == undefined) {
                var selected_row = $(this).parents('tr');
                if (selected_row.hasClass('child')) {
                    selected_row = selected_row.prev();
                }
                rowData = $('#idTablePromocionesMarketingLocal').DataTable().row(selected_row).data();
            } else {
                rowData = data;
            }
			$('#idTextTituloPromocionMarketing').text(rowData.nombre_promocion);
			fncListarArchivosMarketingPromocion(rowData.id,idRow.index());
			
        });

    }
	
	function fncListarArchivosMarketingPromocion(idPromocion,rowData) {
		var formData = new FormData();		
		formData.append('accion', 'sec_locales_listar_archivos_promocion_local');
        formData.append('idPromocionLocal', idPromocion);
		$.ajax({
            type: "POST",
            data: formData,
            url: 'sys/get_locales_promocion_marketing.php',
            contentType: false,
            processData: false,
            cache: false,
            success: function(response) {
                var jsonData = JSON.parse(response);
                if (jsonData.error == false) {                    
					//console.log(rowData);
					$('#idListArchivosPromocionMarketing').empty();
					var nombreImg ='';
					jsonData.data.forEach( function(archivo, indice, array) {
						$('#idListArchivosPromocionMarketing').append('<li><i class="fa fa-file"></i>&nbsp;'+archivo.nombre.substring(0, 50)+
						'&nbsp;<a class="verArchivoPromocion" onclick="fncVerArchivoPromocionMarketing(this)" data-nombrearchivo="'+archivo.nombre+'"><span class="badge badge-primary">ver</span> </a>&nbsp;'+ '');
						// '<a class="eliminarArchivoPromocion" data-itemid="'+rowData+'"  onclick="fncEliminarArchivoPromocionMarketing(this)" data-idarchivo="'+archivo.id+'" ><span class="badge badge-danger">Eliminar</span> </a> </li>');
					nombreImg =archivo.nombre;				
					});
					$('#idLottieAnimationImg').css("display", "none");
					$("#idImgArchivoPromocionMarketing").attr("src",'/files_bucket/promociones/marketing/'+nombreImg);
                } else {
                    swal("Error", jsonData.message, "error");
                }
            }
        });
	}

	function fncEliminarMarketingPromocion(idPromocion) {
		var formData = new FormData();		
		formData.append('accion', 'sec_locales_eliminar_promocion_local');
        formData.append('idPromocionLocal', idPromocion);	

		swal({
            title: "¿Estas seguro?",
            text: "¡Los datos seran eliminados de Gestión!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Si, eliminar",
            cancelButtonText: "No, cancelar",
            closeOnConfirm: false,
            closeOnCancel: false,

        },
            function (isConfirm) {
                if (isConfirm) {
					$.ajax({
						type: "POST",
						data: formData,
						url: 'sys/get_locales_promocion_marketing.php',
						contentType: false,
						processData: false,
						cache: false,
						success: function(response) {
							var jsonData = JSON.parse(response);
							if (jsonData.error === false) {
								swal("Eliminado", jsonData.message, "success");
								fncRenderizarDataTableListadoPromocionMarketing();
								$("#idImgArchivoPromocionMarketing").attr("src",'');
								$('#idListArchivosPromocionMarketing').empty();
								$('#idTextTituloPromocionMarketing').text("Ninguna Promoción Seleccionada");
							} else {
								swal("Error", jsonData.message, "error");
							}
						}
					});
                }
            });
	}
	
	if ($('#idTableLocaleUserOperations').length==0) {
	} else {
		fncDataToRenderTableLocaleUserOperations();
		$('#idSearchUserOperatinsLog').on('click', (e) => {
			
			fncDataToRenderTableLocaleUserOperations();
			//alert("Check");
			return false;
		});
	}
	function fncDataToRenderTableLocaleUserOperations() {
		var formData = new FormData();
		formData.append('fechaInicio',$("#idFromUserOperatinsLog").val());
		formData.append('fechaFin', $("#idToUserOperatinsLog").val());
		formData.append('local_id', $('#item_id_temporal').val());
		formData.append('action', 'list_logs');

			$.ajax({
				type: "POST",
				data: formData,
				url: 'app/routes/UsuarioLog/',
				contentType: false,
				processData: false,
				cache: false,
				success: function (response) {
					var jsonData = JSON.parse(response);
					if (jsonData.error == true) {
						
						
					}else{
						fncRenderTableLocaleUserOperations(jsonData.data);
					} 
					

				}, beforeSend: function () {
					//loading(true);
				}
			});
		
	}
	function fncRenderTableLocaleUserOperations(data = {}) {
		var table = $('#idTableLocaleUserOperations').DataTable();
		table.clear();
		table.destroy();
		//loading(true);
		var table = $('#idTableLocaleUserOperations').DataTable({
			'destroy': true,
			"autoWidth": false,
			scrollX: true,
			"lengthChange": false,
			"dom": 'Bfrtip',
			"fnDrawCallback": function (oSettings) {
				$(function () {
					//$('[data-toggle="popover"]').popover()
				})
			},
			buttons: {
				buttons: [
	
				]
			},
			"language": {
				processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> '
			},
			"data": data,
			"ordering": true,
			"language": {
				url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
			},
			"columns": [
	
				{
					"data": "id",
					// render: function (data, type, row) {
					// 	var codigo = '[' + data + ']';
					// 	return codigo;
					// }
				},
	
				{
					"data": "usuario",
				},
				{
					"data": "to_user"
				},
				{
					"data": "action"
				}
				,
				{
					"data": "created_at"
				}
	
			],
			"createdRow": function (row, data, dataIndex, cells) {
				var code_color = '';
				if (data.action == "Agregar" ) {
					code_color = '#93ebd0';
				} else if(data.action == "Eliminar" ){
					code_color = '#eaa1a7';
				}
				$(row).css("background-color", code_color);
			}
		});
		$('#idTableLocaleUserOperations tbody').off('click');
	
	
	}

	//LOG USUARIOS PROMOCION FIN






});



function fncVerArchivoPromocionMarketing(elemento) {
	var aDom = jQuery(elemento);
	$('#idLottieAnimationImg').css("display", "none");
	var recursoImg = '/files_bucket/promociones/marketing/'+$(aDom).data().nombrearchivo;
	$("#idImgArchivoPromocionMarketing").attr("src",recursoImg);	
}

function fncEliminarArchivoPromocionMarketing(elemento) {
	var aDom = jQuery(elemento);
	var idArchivo = $(aDom).data().idarchivo;
	var idItem = $(aDom).data().itemid;
	var formData = new FormData();		
	formData.append('accion', 'sec_locales_eliminar_archivos_promocion_local');
	formData.append('idArchivo', idArchivo);
	formData.append('idItem', idItem);
	
	$.ajax({
		type: "POST",
		data: formData,
		url: 'sys/get_locales_promocion_marketing.php',
		contentType: false,
		processData: false,
		cache: false,
		success: function(response) {
			var jsonData = JSON.parse(response);
			if (jsonData.error == false) { 
				$(aDom).parent().remove();
				swal("Archivo Eliminado", jsonData.message, "success");
				$("#idImgArchivoPromocionMarketing").attr("src",'');				
				var contarArchivosPromocion = $("#idListArchivosPromocionMarketing").children().length;
					if (contarArchivosPromocion <= 0) {
						//console.log(contarArchivosPromocion);
						$('#idTablePromocionesMarketingLocal').DataTable().row(idItem).remove().draw();
						$('#idTextTituloPromocionMarketing').text("Ninguna Promoción Seleccionada");
					}
			} else {
				swal("Error", jsonData.message, "error");
			}
		}
	});	
}

function sec_local_servicio_publico_ver_imagen_full_pantalla(ruta) {
	
	var image = new Image();
	image.src = ruta;
	var viewer = new Viewer(image, {
		hidden: function () {
			viewer.destroy();
		},
	});
	// image.click();
	viewer.show();
}

function sec_locales_btn_descargar(ruta_archivo)
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


function sec_locales_caja_cambiar_estado(caja_id,estado){
    let data = {
        caja_id : caja_id,
		estado: estado,
    	accion:'locales_caja_cambiar_estado'
        }
        
    $.ajax({
        url: "sys/set_locales.php",
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
            auditoria_send({
                "proceso": "locales_caja_cambiar_estado",
                "data": respuesta
                        });
            if (parseInt(respuesta.http_code) == 200) {
                swal({
                    title: respuesta.title,
                    text: respuesta.text,
                    html: true,
                    type: "success",
                    // timer: 3000,
                    closeOnConfirm: true,
                    showCancelButton: false
                    }, function(isConfirmed) {
						if (isConfirmed) {
							setTimeout(function() {
								m_reload();
							}, 0); // Se ejecuta después de la finalización de la pila de llamadas actuales
						}
					});
            } else {
                swal({
                    title: "Error al cambiar de estado la caja",
                    text: respuesta.error,
                    html: true,
                    type: "warning",
                    closeOnConfirm: true,
                    showCancelButton: false
                    });
            }
        	},
        complete: function() {
            loading(false);
            }
        });
                    
            
           
    }

	function switch_estado_caja(btn) {
		var data = Object();
		data.id = btn.attr("data-id");
		data.col = btn.attr("data-col");
		
		// Guarda el estado actual del botón de alternancia
		var originalState = btn.prop("checked");
	
		if (btn.prop("checked")) {
			data.val = btn.attr("data-on-value");
			btn.val(1);
		} else {
			data.val = btn.attr("data-off-value");
			btn.val(0);
		}
	
		if (!btn.prop("checked")) {
			let data_set = {
				caja_id: data.id,
				accion: 'obtener_ultimo_turno_caja'
			}
	
			$.ajax({
				url: "sys/get_locales.php",
				type: 'POST',
				data: data_set,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					var respuesta = JSON.parse(resp);
					auditoria_send({
						"proceso": "obtener_ultimo_turno_caja",
						"data": respuesta
					});
					if (parseInt(respuesta.http_code) == 200) {
						var datosTurnos = [
							{ id: respuesta.caja, turno: respuesta.turno, fecha: respuesta.fecha_operacion, cierre_efectivo: respuesta.cierre_efectivo }
						];
	
						var tablaHTML = 'Existe un último turno registrado: <table style="margin: 0 auto; border-collapse: collapse; border-spacing: 0; text-align: center;"><thead><tr><th style="border: 1px solid #dddddd; padding: 8px;">ID</th><th style="border: 1px solid #dddddd; padding: 8px;">Turno</th><th style="border: 1px solid #dddddd; padding: 8px;">Fecha de operación</th><th style="border: 1px solid #dddddd; padding: 8px;">Cierre efectivo</th></tr></thead><tbody>';
						datosTurnos.forEach(function (turno) {
							tablaHTML += '<tr><td style="border: 1px solid #dddddd; padding: 8px;">' + turno.id + '</td><td style="border: 1px solid #dddddd; padding: 8px;">' + turno.turno + '</td><td style="border: 1px solid #dddddd; padding: 8px;">' + turno.fecha + '</td><td style="border: 1px solid #dddddd; padding: 8px;">' + turno.cierre_efectivo + '</td></tr>';
						});
						tablaHTML += '</tbody></table>';
						swal({
							title: '¿Está seguro de desactivar la caja?',
							html: true,
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: '#3085d6',
							confirmButtonText: "SI",
							cancelButtonText: "NO",
							closeOnConfirm: true,
							closeOnCancel: true,
							text: tablaHTML
						}, function (isConfirm) {
							if (isConfirm) {
								sec_locales_caja_cambiar_estado(data.id, data.val);

							}else {
								$("#checkbox_" +data.id+"").bootstrapToggle('on');

							}
						});
					} else if (parseInt(respuesta.http_code) == 400) {
						sec_locales_caja_cambiar_estado(data.id, data.val);
					}
				},
				complete: function () {
					loading(false);
				}
			});
		} else {
			swal({
				title: '¿Está seguro de activar la caja?',
				text: 'Esta caja está desactivada, ¿Seguro que desea reactivarla?',
				html: true,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				confirmButtonText: "SI",
				cancelButtonText: "NO",
				closeOnConfirm: true,
				closeOnCancel: true,
			}, function (isConfirm) {
				if (isConfirm) {
					sec_locales_caja_cambiar_estado(data.id, data.val);

				} else {
					
					location.reload();

				}
			});
		}
	}
	