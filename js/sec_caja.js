function sec_caja() {
	if (sec_id == "caja") {
		// console.log("sec_caja");

		if (!sub_sec_id) {
			sub_sec_id = "turnos";
		}
		item_config = {};
		$(".item_config").each(function (index, el) {
			item_config[$(el).attr("name")] = $(el).val();
		});
		// var ls_obj = {};
		$.each(item_config, function (index, val) {
			var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + index;
			// console.log(ls_index);
			// var input_name = ls_index.replace(ls_index_pref,"");
			if (ls = localStorage.getItem(ls_index)) {
				item_config[index] = ls;
			}
		});
		// console.log(sub_sec_id);

		if (sub_sec_id == "reporte") {

			sec_caja_reporte_get_locales();
		}
		else if (sub_sec_id == "faltantes" || sub_sec_id == "comparacion") {
			//console.log("etc");
		} else {
			if (item_id) {
				if (item_config.estado == 1) {

				} else {
					sec_caja_update_data();
				}
			} else {
				// sec_caja_get_turnos();
			}
		}
		// console.log(item_config);
		sec_caja_config();
		sec_caja_events();

		if (sub_sec_id == "turnos") {
			$(".load_turnos_btn").click();
		}
		if (sub_sec_id == "reporte") {
			// $(".search_btn").click();
		}
		if (sub_sec_id == "comparacion") {
			// $(".compare_btn").click();
			var ctrl_active = false;
			$(document).keydown(function (e) {
				// console.log('key code is: '+e.which);
				if (e.which == 17) {
					ctrl_active = true;
				} else if (e.which == 13) {
					if (ctrl_active) {
						// console.log("OOOOOOOK");
						$(".compare_btn").click();
						ctrl_active = false;
					}
				} else {
					ctrl_active = false;
				}
			});
			$(document).keyup(function (e) {
				if (e.which == 17) {
					ctrl_active = false;
				}
			});

		}
		// auditoria_send({"proceso":"visita","data":item_config});
		sec_eliminar_caja_turno();
	}
	;

}

let sec_get_reporte_eliminados = (id) => {
	return new Promise((resolve) => {
		$.post('/sys/get_caja.php', {
			"get_caja_reporte_eliminados": id
		}, function (r) {
			let response = JSON.parse(r);
			resolve(response);
		});
	})
}

function sec_eliminar_caja_turno() {
	$(document).off('click', '.caja_eliminar_turno_btn');
	$(document).on("click", ".caja_eliminar_turno_btn", function () {
		console.log("caja_eliminar_turno_btn");

		var item_id = $(this).data("item_id");
		var local = $(this).data('local');
		var fecha_operacion = moment($(this).data("fecha_operacion"), "YYYY-MM-DD");
		var fecha_actual = moment().format("YYYY-MM-DD");
		var dias = moment.duration(fecha_operacion.diff(fecha_actual)).asDays();
		var mensaje = "¡Esta acción es irreversible!";
		var titulo = "¿Estás completamente seguro que desea eliminar?";

		if (dias <= -2) {
			mensaje = "Han pasado 2 o más días desde que se Aperturo";
		}
		;
		console.log(dias, "han pasado");

		var get_data = {id: item_id};
		$.post('/sys/get_caja.php', {
			"sec_caja_validado": get_data
		}, function (r) {
			try {
				var estado = r;
				if (estado == '1') {
					console.log(r, "estado");
					swal("Error!", "Registro Validado, no se puede Eliminar", "warning");
					auditoria_send({"proceso": "sec_caja_eliminar_stop_registro_validado", "data": get_data});
					return false;
				} else {
					var get_data_ = {id: item_id, local: local};
					$.post('/sys/get_caja.php', {
						"sec_caja_existe_posterior": get_data_
					}, function (r) {
						try {
							var cantidad = r;
							if (cantidad > 0) {
								console.log(r, "cantidad");
								swal("Error!", "Existen Registros Posteriores, no se puede Eliminar", "warning");
								auditoria_send({
									"proceso": "sec_caja_eliminar_stop_existen_registros_posteriores",
									"data": get_data_
								});
								return false;
							} else {
								loading(true);
								sec_get_reporte_eliminados(local).then(data => {
									loading(false);
									var save_data = {};
									save_data.item_id = item_id;
									let table = `
												<br> 
												<div style="height: 5em;display: block;overflow: scroll;">
													<table class="table table-sm table-condensed" style="font-size: 0.5em;">
														<thead>
															<tr>
																<th> FECHA ELIMINACIÓN </th>
																<th> USUARIO ELIMINACIÓN </th>
																<th> TURNO </th>
																<th> FECHA OPERACIÓN </th>
															</tr>
														</thead>
														<tbody>
															${data.map((item, i) =>
										`
																<tr>
																	<td>${item.fecha_eliminacion}</td>
																	<td>${item.usuario_eliminacion}</td>
																	<td>${item.turno}</td>
																	<td>${item.fecha_operacion}</td>
																</tr>
															`.trim()).join('')}														
														</tbody>
													</table>		
												</div>																						
												`
									swal({
											title: `<h3>${titulo}</h3>` + '<span style="font-size:12px">Comentario :</span> <textarea id="txtComentario" autofocus name="txtComentario" class="form-control" style="display:block;font-size:11px;margin-top: -10px;"></textarea>' + table,
											text: mensaje,
											type: "warning",
											showCancelButton: true,
											confirmButtonColor: "#DD6B55",
											confirmButtonText: "Si",
											cancelButtonText: "No",
											closeOnConfirm: false,
											closeOnCancel: false,
											html: true,
											customClass: "sweet_alert_wide",
										},
										function (opt) {
											if (opt) {
												//console.log("SISISI");
												loading(true);
												$.post('/sys/set_caja.php', {
													"sec_caja_eliminar": save_data
												}, function (r) {
													// console.log(r);
													save_data.response = r;
													save_data.mensaje = $("#txtComentario").val();
													auditoria_send_promise({
														"proceso": "sec_caja_eliminar",
														"data": save_data
													}).then(() => {
														loading(false);
														swal({
																title: "¡Eliminado!",
																text: "Ya no hay macha atrás.",
																type: "success",
																timer: 1000,
																closeOnConfirm: true
															},
															function () {
																// auditoria_send({"proceso":"sec_caja_eliminar_stop","data":save_data});
																// m_reload();
																swal.close();
																loading(true);
																window.location = "/?&sec_id=caja";
															});
													});
													//auditoria_send({"proceso": "sec_caja_eliminar", "data": save_data});
												});
											} else {

												console.log("NONONONO");
												swal({
														title: "Estuvo cerca!",
														text: "La próxima piénsalo mejor!",
														type: "success",
														timer: 1000,
														closeOnConfirm: true
													},
													function (opt) {
														if (opt) {
															auditoria_send({
																"proceso": "sec_caja_eliminar_stop",
																"data": save_data
															});
														}
														// m_reload();
														// swal.close();
													});
											}
										});
								});


							}
						} catch (err) {
							console.log(err);
						}
						// console.log(r);
					});
				}
			} catch (err) {
				console.log(err);
			}
			// console.log(r);
		});
	});
}

function sec_caja_get_turnos() {
	// console.log(item_config);
	loading(true);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_get_turnos": get_data
	}, function (r) {
		loading();
		// console.log(r);
		try {
			$(".container_tabla_detalles_apertura_caja").html(r);
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
}

function sec_caja_config() {
	// console.log("sec_caja_config");

	$.each(localStorage, function (ls_index, val) {
		var ls_index_pref = "sec_" + sec_id + "_" + sub_sec_id + "_";
		if (ls_index.indexOf(ls_index_pref) >= 0) {
			var input_name = ls_index.replace(ls_index_pref, "");
			$(".item_config[name=" + input_name + "]")
				.filter(function(){
					return !$(this).hasClass('input_ignore');
				})
				.val(val).change();
			var real_date = $(".item_config[name=" + input_name + "]").data('real-date');
			if (real_date) {
				var new_date = moment(val).format("DD-MM-YYYY");
				$("#" + real_date).val(new_date);
			}
		}

		var ls_index_single_searcher = "sec_caja_turno_searcher_";
		if (ls_index.indexOf(ls_index_single_searcher) >= 0) {
			$("." + ls_index).val(val);
		}
	});

	$('#sec_caja_depositos_cantidad_inicio').keyup(function (e) {
		if (/[^,.\d]/g.test(this.value)) {
			this.value = this.value.replace(/[^,.\d]/g, '');
		}
		filter_caja_depositos_table();
	});

	$('#sec_caja_depositos_cantidad_fin').keyup(function (e) {
		if (/[^,.\d]/g.test(this.value)) {
			this.value = this.value.replace(/[^,.\d]/g, '');
		}
		filter_caja_depositos_table();
	});

	$('#sec_caja_depositos_boveda_cantidad_inicio').keyup(function (e) {
		if (/[^,.\d]/g.test(this.value)) {
			this.value = this.value.replace(/[^,.\d]/g, '');
		}
		filter_caja_depositos_table();
	});

	$('#sec_caja_depositos_boveda_cantidad_fin').keyup(function (e) {
		if (/[^,.\d]/g.test(this.value)) {
			this.value = this.value.replace(/[^,.\d]/g, '');
		}
		filter_caja_depositos_table();
	});

	$(".sec_caja_reporte_fecha_datepicker")
		.datepicker({
			dateFormat: 'dd-mm-yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function (ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));

		});

	$(".sec_caja_faltantes_fecha_datepicker")
		.datepicker({
			dateFormat: 'dd-mm-yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function (ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));

		});

	$(document).on('change keyup paste click', '#kasnet_ingreso', function (event) {
		event.preventDefault();
		change_kasnet_saldo();

	});
	$(document).on('change keyup paste click', '#kasnet_salida', function (event) {
		event.preventDefault();
		change_kasnet_saldo();
	});

	/*disashop_ingreso input event*/
	$(document).on('change keyup paste click', '#disashop_ingreso', function (event) {
		event.preventDefault();
		change_disashop_saldo();
	});
	/*fin disashop_ingreso input event*/
}

function change_disashop_saldo() {
	var v_in = $("#disashop_ingreso").val();
	var v_now = $("#fixed_saldo_disashop").val();
	var result = (Number((v_now) ? v_now : 0) - Number((v_in) ? v_in : 0));
	var result_detalle = - Number((v_in) ? v_in : 0);

	$(".saldo_disashop").removeClass('bg-danger text-white text-bold');
	if (result < 0) $(".saldo_disashop").addClass('bg-danger text-white text-bold');

	$("#saldo_disashop").val(result_detalle);
	$(".saldo_disashop").val(result_detalle);
	$("#saldo_disashop").data('db_val', result_detalle);
}

function change_kasnet_saldo() {
	var v_in = $("#kasnet_ingreso").val();
	var v_out = $("#kasnet_salida").val();
	var v_now = $("#fixed_saldo_kasnet").val();
	var result = (Number((v_now) ? v_now : 0) + Number((v_out) ? v_out : 0) - Number((v_in) ? v_in : 0));
	var result_detalle = (Number((v_out) ? v_out : 0) - Number((v_in) ? v_in : 0));

	$(".saldo_kasnet").removeClass('bg-danger text-white text-bold');
	if (result < 0) $(".saldo_kasnet").addClass('bg-danger text-white text-bold');


	$("#saldo_kasnet").val(result_detalle);
	$(".saldo_kasnet").val(result_detalle);
	$("#saldo_kasnet").data('db_val', result_detalle);

}

function sec_caja_auditoria() {
	// console.log("sec_caja_get_reporte");
	loading(true);
	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_auditoria": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);
			sec_caja_auditoria_events(get_data);
			//fixHead();
			var wd = $(window).outerWidth();
			if (wd < 769) {
				$('[id=tbl_auditoria]').fixMe({
					"columns": 0,
					"footer": false,
					"marginTop": 50,
					"zIndex": 1,
					"bgColor": "white",
					"bgHeaderColor": "white"
				});
			} else {
				$('[id=tbl_auditoria]').fixMe({
					"columns": 5,
					"footer": false,
					"marginTop": 50,
					"zIndex": 1,
					"bgHeaderColor": "white"
				});
			}

		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
}

function sec_caja_auditoria_events(data_export) {
	// console.log("sec_caja_auditoria_events");
	$(".detalle_btn")
		.off()
		.click(function (event) {
			loading(true);
			var btn_data = $(this).data();
			$.each(btn_data, function (config_index, config_val) {
				var ls_index = "sec_caja_reporte_" + config_index;
				localStorage.setItem(ls_index, config_val);
			});
			localStorage.setItem("sec_caja_auditoria_detalle", "true");
			window.open("/?sec_id=caja&sub_sec_id=reporte#ste=.table_container");
			setTimeout(function () {
				loading();
			}, 1000);
			console.log(btn_data);
		});

	$(".btn_export_caja_auditoria")
		.off()
		.on("click", function (e) {
			loading(true);
			$.ajax({
				url: '/export/caja_auditoriav2.php',
				type: 'post',
				data: data_export,
			})
				.done(function (dataresponse) {
					var obj = JSON.parse(dataresponse);
					window.open(obj.path);
					loading();
				});
		});
}

function sec_caja_compare() {
	// console.log("sec_caja_get_reporte");
	loading(true);
	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_compare": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);
			sec_caja_compare_events(get_data);
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
}

function sec_caja_compare_events(data_export) {
	// console.log("sec_caja_compare_events");
	$(".detalle_btn")
		.off()
		.click(function (event) {
			loading(true);
			var btn_data = $(this).data();
			$.each(btn_data, function (config_index, config_val) {
				var ls_index = "sec_caja_reporte_" + config_index;
				localStorage.setItem(ls_index, config_val);
			});
			window.open("/?sec_id=caja&sub_sec_id=reporte#ste=.table_container");
			setTimeout(function () {
				loading();
			}, 1000);
			console.log(btn_data);
		});

	$(".btn_export_caja_compare")
		.off()
		.on("click", function (e) {
			loading(true);
			$.ajax({
				url: '/export/caja_auditoria.php',
				type: 'post',
				data: data_export,
			})
				.done(function (dataresponse) {
					var obj = JSON.parse(dataresponse);
					window.open(obj.path);
					loading();
				});
		});
}

function sec_caja_get_reporte() {
	console.log("sec_caja_get_reporte_1");
	loading(true);

	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_get_reporte": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);
			$(".switch")
				.bootstrapToggle({
					on: "Validado",
					off: "No Validado",
					onstyle: "success",
					offstyle: "danger",
					size: "mini"
				});

			$(".switch")
				.off()
				.change(function (event) {
					var element = $(this);
					var valor = $(this).val();
					var area_id = $(this).data('area');
					var switch_trigger = $(this).is(':checked');
					var id = $(this).data('id');
					var fecha = $(this).data('fecha_operacion');
					var local = $(this).data('local');
					var get_data = {fecha: fecha, local: local, id: id};
					/*
				if(area_id==22){
					if(valor==0)switch_data($(event.target));
					else $(this).bootstrapToggle('on');
				}
				if(area_id==3){
					if(valor==0) $(this).bootstrapToggle('off');
					else switch_data($(event.target));
				}
				*/
					if (area_id == 6 || area_id == 3 || area_id == 22) {

						if (switch_trigger) {
							console.log("validar");

							// console.log(get_data);
							$.post('/sys/get_caja.php', {
								"sec_caja_validado_anterior": get_data
							}, function (r) {
								try {
									var estado = r;
									console.log(r, "estado1");
									if (estado == '0') {
										swal("Error!", "Registro Anterior No esta Validado", "warning");
										$(element).bootstrapToggle('destroy');
										$(element).val(0);
										$(element).prop('checked', false);
										$(element).bootstrapToggle({
											on: "Validado",
											off: "No Validado",
											onstyle: "success",
											offstyle: "danger",
											size: "mini"
										});
										//$(element).bootstrapToggle('off');
										return false;
									} else {
										switch_data($(event.target));
										return false;
									}
								} catch (err) {
									console.log(err);
								}
								// console.log(r);
							});

						} else {
							console.log("desvalidar");
							$.post('/sys/get_caja.php', {
								"sec_caja_validado_posterior": get_data
							}, function (r) {
								try {
									var estado = r;
									console.log(r, "estado2");
									if (estado == '1') {
										swal("Error!", "Registro Posterior esta Validado", "warning");
										//$(element).bootstrapToggle('on');
										$(element).bootstrapToggle('destroy');
										$(element).val(1);
										$(element).prop('checked', true);
										$(element).bootstrapToggle({
											on: "Validado",
											off: "No Validado",
											onstyle: "success",
											offstyle: "danger",
											size: "mini"
										});
										return false;
									} else {
										$.post('/sys/get_caja.php', {
											"sec_caja_check_conciliations": get_data
										}, function (response) {
											if (response > 0) {
												swal({
														title: "¡Alerta!",
														text: "El turno tiene un depósito vinculado en la transacción bancaria. Si desvalidas el vínculo se romperá. ¿Estas Seguro?",
														type: "warning",
														showCancelButton: true,
														confirmButtonColor: "#DD6B55",
														confirmButtonText: "Si",
														cancelButtonText: "No",
														closeOnConfirm: true,
														closeOnCancel: true
													},
													function (opt) {
														if (opt) {
															switch_data($(event.target));
															$.post('/sys/get_caja.php', {"sec_caja_remove_conciliations": get_data}, function (response) {
																console.log(response);
															});
														} else element.bootstrapToggle('on');
													});
											} else switch_data($(event.target));
										});
										return false;
									}
								} catch (err) {
									console.log(err);
								}
								// console.log(r);
							});

						}
					}
				});
			$(".btn_export_caja_reporte").off().on("click", function (e) {
				loading(true);
				$.ajax({
					url: '/export/caja_reporte.php',
					type: 'post',
					data: get_data,
				})
					.done(function (dataresponse) {
						console.log(dataresponse, "valor");
						var obj = JSON.parse(dataresponse);
						window.open(obj.path);
						loading();
					})
					.always(function (data) {
						loading();
					});
			});

			$(".registro_caja_archivos").off().on("click", function (e) {
				e.preventDefault();
				var item_id = $(this).data("id");
				var archivos = ($(this).data("archivos")).split(',');
				var tbody = "";
				$.each(archivos, function (index, val) {
					var file = val.split("@");
					var ext = file[0];
					var size = file[1];
					var nombre = file[2];
					tbody += "<tr style='cursor:pointer'>";
					tbody += "<td class='tr_archivo'><i class='glyphicon glyphicon-save-file'></i></td>";
					tbody += "<td style='text-align:left;'><a href='./files_bucket/cajas/" + nombre + "' download>" + nombre + "</a></td><td class='tr_archivo'>" + size + " kb</td><td class='tr_archivo'>" + ext + "</td>";
					tbody += "</tr>";
				});

				swal({
					title: 'Archivo',
					text: '<table class="table table-condensed" style="font-size: 12px;"><thead><tr><th></th><th>Nombre</th><th>size</th><th>ext</th></tr></thead><tbody>' + tbody + '</tbody></table>',
					html: true,
					type: "",
					showCancelButton: true,
					showConfirmButton: false,
					cancelButtonColor: "#DD6B55",
					cancelButtonText: "Cerrar",
					closeOnConfirm: false,
					closeOnCancel: true
				});
			});

			$(".registro_premios_archivos").off().on("click", function (e) {
				e.preventDefault();
				var item_id = $(this).data("id");
				var archivos = ($(this).data("archivos")).split(',');
				var tbody = "";
				$.each(archivos, function (index, val) {
					var file = val.split("@");
					var ext = file[0];
					var size = file[1];
					var nombre = file[2];
					tbody += "<tr style='cursor:pointer'>";
					tbody += "<td class='tr_archivo'><i class='glyphicon glyphicon-save-file'></i></td>";
					tbody += "<td style='text-align:left;'><a href='./files_bucket/registros/premios/" + nombre + "' download>" + nombre + "</a></td><td class='tr_archivo'>" + size + " kb</td><td class='tr_archivo'>" + ext + "</td>";
					tbody += "</tr>";
				});

				swal({
					title: 'Archivo',
					text: '<table class="table table-condensed" style="font-size: 12px;"><thead><tr><th></th><th>Nombre</th><th>size</th><th>ext</th></tr></thead><tbody>' + tbody + '</tbody></table>',
					html: true,
					type: "",
					showCancelButton: true,
					showConfirmButton: false,
					cancelButtonColor: "#DD6B55",
					cancelButtonText: "Cerrar",
					closeOnConfirm: false,
					closeOnCancel: true
				});
			});

			// AGREGAR OCI
			$(".btn_add_ociCAMBIAR").off().on("click", function (e) {
				e.preventDefault();
				var id = $(this).data("id");
				var jsonOci = JSON.parse($(this).attr("jsonOci"));
				var contenido = '';

				console.log(jsonOci);

				contenido += '<form>';
				contenido += '<div class="row">';
				contenido += '<div class="col-6">';
				contenido += '<select>';
				// for(i=0;i<jsonOci.length;i++){
				// 	contenido += '<option>'+jsonOci[i]['titulo']+'</option>';
				// }
				contenido += '</select>';
				contenido += '</div>';
				contenido += '<div class="col-6" style="background:red">';
				contenido += 'Hola';
				contenido += '</div>';
				contenido += '</div>';
				contenido += '</form>';
			});

			// console.log("sec_caja_get_reporte:READY");
			$(".table_container .view_more_btn").tooltip({
				placement: 'left'
			});
			// console.log(url_object);
			if (url_object.fragment) {
				if (url_object.fragment.ste) {
					// console.log(url_object.fragment.ste);
					// var obj_top = $(url_object.fragment.ste).position();
					// setTimeout(function(){
					var obj_offset = $(".table_container").offset();
					// console.log(obj_offset);
					$(document).scrollTop(obj_offset.top - 52);
					// }, 10);
				}
			}
			let resol = $(window).outerWidth();
			if (resol < 769) {
				console.log('minimal');
				$("#tbl_reporte_resumen_turno").fixMe({
					"columns": 1,
					"footer": true,
					"marginTop": 50,
					"zIndex": 1,
					"bgColor": "white",
					"bgHeaderColor": "white"
				});
				$("#tbl_reporte_resumen_dia").fixMe({
					"columns": 1,
					"footer": true,
					"marginTop": 50,
					"zIndex": 1,
					"bgColor": "white",
					"bgHeaderColor": "white"
				});
			} else {
				console.log('maximal');
				$("#tbl_reporte_resumen_turno").fixMe({
					"columns": 5,
					"footer": true,
					"marginTop": 50,
					"zIndex": 1,
					"bgColor": "white",
					"bgHeaderColor": "white"
				});
				$("#tbl_reporte_resumen_dia").fixMe({
					"columns": 5,
					"footer": true,
					"marginTop": 50,
					"zIndex": 1,
					"bgColor": "white",
					"bgHeaderColor": "white"
				});
			}


		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
	// console.log(item_config);

}

function sec_caja_get_validados() {
	console.log("sec_caja_get_validados");
	loading(true);

	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_get_validados": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);

			// console.log("sec_caja_get_reporte:READY");
			$(".table_container .view_more_btn").tooltip({
				placement: 'left'
			});
			// console.log(url_object);
			if (url_object.fragment) {
				if (url_object.fragment.ste) {
					// console.log(url_object.fragment.ste);
					// var obj_top = $(url_object.fragment.ste).position();
					// setTimeout(function(){
					var obj_offset = $(".table_container").offset();
					// console.log(obj_offset);
					$(document).scrollTop(obj_offset.top - 52);
					// }, 10);
				}
			}

			$("[id='btnShowAnalistas']").off().on('click', function (event) {
				event.preventDefault();
				$("#divAnalistas").html($(this).closest('tr').find('#txtAnalistas').html());
				$("[id='mdAnalistas']").modal();

			});
			$("#tbl_reporte_resumen_turno").scroll(function (event) {
				console.log('scroll');
				$("#tbl_reporte_resumen_turno").fixMe({
					"marginTop": 50,
					"zIndex": 1,
					"bgColor": "white",
					"bgHeaderColor": "white"
				});
			});

		} catch (err) {
			// console.log(r);
		}

		$("[id='btnExportValidados']").off().on('click', function (event) {
			event.preventDefault();
			loading(true);

			$(".item_config").each(function (index, el) {
				var config_index = $(el).attr("name");
				var config_val = $(el).val();
				var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
				localStorage.setItem(ls_index, config_val);
				item_config[config_index] = config_val;
			});
			var get_data = jQuery.extend({}, item_config);

			$.ajax({
				url: '/export/caja_validados.php',
				type: 'POST',
				data: get_data,
			})
				.done(function (dataresponse) {
					console.log(dataresponse);
					var obj = JSON.parse(dataresponse);
					window.open(obj.path);
				})
				.always(function (data) {
					loading();
				});
		});
		// console.log(r);
	});
	// console.log(item_config);
}

function sec_caja_get_jackpot() {
	console.log("sec_caja_get_jackpot");
	loading(true);

	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_get_jackpot": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);

			$(".btn_export_caja_jackpot").off().on("click", function (e) {
				loading(true);
				$.ajax({
					url: '/export/caja_jackpot.php',
					type: 'post',
					data: get_data,
				})
					.done(function (dataresponse) {
						var obj = JSON.parse(dataresponse);
						window.open(obj.path);
						loading();
					})
					.always(function (data) {
						loading();
					});
			});


			// console.log("sec_caja_get_reporte:READY");
			$(".table_container .view_more_btn").tooltip({
				placement: 'left'
			});
			// console.log(url_object);
			if (url_object.fragment) {
				if (url_object.fragment.ste) {
					// console.log(url_object.fragment.ste);
					// var obj_top = $(url_object.fragment.ste).position();
					// setTimeout(function(){
					var obj_offset = $(".table_container").offset();
					// console.log(obj_offset);
					$(document).scrollTop(obj_offset.top - 52);
					// }, 10);
				}
			}

			$("[id='btnShowAnalistas']").off().on('click', function (event) {
				event.preventDefault();
				$("#divAnalistas").html($(this).closest('tr').find('#txtAnalistas').html());
				$("[id='mdAnalistas']").modal();

			});

			$("#tbl_reporte_resumen_turno").fixMe({
				"marginTop": 50,
				"zIndex": 1,
				"bgColor": "white",
				"bgHeaderColor": "white"
			});
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
	// console.log(item_config);
}

function sec_caja_get_reporte_faltantes() {
	loading(true);

	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_get_faltantes": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);
			$(".switch")
				.bootstrapToggle({
					on: "Validado",
					off: "No Validado",
					onstyle: "success",
					offstyle: "danger",
					size: "mini"
				});

			$(".switch")
				.off()
				.change(function (event) {
					console.log(event);
					switch_data($(event.target));
				});
			$(".btn_export_caja_faltantes").off().on("click", function (e) {
				loading(true);
				$.ajax({
					url: '/export/caja_faltantes.php',
					type: 'post',
					data: get_data,
				})
					.done(function (dataresponse) {
						var obj = JSON.parse(dataresponse);
						window.open(obj.path);
						loading();
					})
			});


			// console.log("sec_caja_get_reporte:READY");
			$(".table_container .view_more_btn").tooltip({
				placement: 'left'
			});
			// console.log(url_object);
			if (url_object.fragment) {
				if (url_object.fragment.ste) {
					// console.log(url_object.fragment.ste);
					// var obj_top = $(url_object.fragment.ste).position();
					// setTimeout(function(){
					var obj_offset = $(".table_container").offset();
					// console.log(obj_offset);
					$(document).scrollTop(obj_offset.top - 52);
					// }, 10);
				}
			}
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
	// console.log(item_config);
}

function filter_concar_table(page) {
	var get_data = {};
	get_data.page = page;
	get_data.is_terminal = $("#terminales_switch").prop('checked')

	auditoria_send({"proceso":"get_concar_history","data":get_data});
	$.post('/sys/get_caja.php', {"get_concar_history": get_data}, function (response) {
		result = JSON.parse(response);
		$("#tblConcarHistorico").html(result.body);

		$("#concarPagination").pagination({
			items: result.num_rows,
			currentPage: page + 1,
			itemsOnPage: 5,
			cssStyle: 'light-theme',
			onPageClick: function (pageNumber, event) {
				event.preventDefault();
				filter_concar_table(pageNumber - 1);
			}
		});
	});
}

function filter_concar_boveda_table(page) {
	var get_data = {};
	get_data.page = page;

	auditoria_send({"proceso":"get_concar_boveda_history","data":get_data});
	$.post('/sys/get_caja.php', {"get_concar_boveda_history": get_data}, function (response) {
		result = JSON.parse(response);
		$("#tblConcarBovedaHistorico").html(result.body);

		$("#concarBovedaPagination").pagination({
			items: result.num_rows,
			currentPage: page + 1,
			itemsOnPage: 5,
			cssStyle: 'light-theme',
			onPageClick: function (pageNumber, event) {
				event.preventDefault();
				filter_concar_boveda_table(pageNumber - 1);
			}
		});
	});
}

function sec_caja_events() {

	set_caja_depositos_file_transcribir_concar_boveda($('#archivo_transcribir_concar_boveda'));

	if ($(".depositos_btn").length) {

		$(".btn-concar").off().on("click", function () {
			$("#concar_local_id").val($("#local_id").children("option:selected").val()).change();
			$("#tipo_cambio").val("");
			$("#modal_concar").modal("show");
			filter_concar_table(0);
		});

		$(".btn-concar-boveda").off().on("click", function () {
			$("#concar_boveda_local_id").val($("#local_id").children("option:selected").val()).change();
			$("#tipo_cambio").val("");
			$("#modal_concar_boveda").modal("show");
			filter_concar_boveda_table(0);
		});

		$('#terminales_switch').on('change', function() {
			let switch_sate = $("#terminales_switch").prop('checked')
			let local_select = $("#local_id");
			let concar_local_select = $("#concar_local_id");
			localStorage.setItem("sec_caja_depositos_is_terminales", switch_sate);

			local_select.empty().trigger('change')
			concar_local_select.empty().trigger('change')
			$(".table_container").html("");

			loading(true)
			$.post('/sys/get_caja_depositos.php', {"get_locales": switch_sate}, function(r) {
				let response = "";

				console.log(response)
				try { response = JSON.parse(r) }
				catch { console.log(r) }
				//let properties = Object.keys(response).reverse()
				response.forEach(e => {
					let option = new Option(e.nombre, e.id, true, true)
					let option2 = new Option(e.nombre, e.id, true, true)
					local_select.append(option)
					concar_local_select.append(option2)
				})
				if (switch_sate) {
					local_select.val('_all_terminales_').trigger('change')
					concar_local_select.val('_all_terminales_').trigger('change')
				}
				else {
					local_select.val('_all_').trigger('change')
					concar_local_select.val('_all_').trigger('change')
				}

				loading()
			})

			if (switch_sate) $('.terminales-hide').hide()
			else $('.terminales-hide').show()
		})

		let switch_state = localStorage.getItem("sec_caja_depositos_is_terminales") === "true";
		$("#terminales_switch").prop('checked', switch_state).trigger('change')

		$(document).on('click', '#deleteConcarHistory', function (event) {
			event.preventDefault();
			loading(true);

			var get_data = {};
			get_data.id = $(this).data("id");

			$.post('/sys/get_caja.php', {"delete_concar_history": get_data}, function () {
				filter_concar_table(0);
				loading(false);
			});
		});

		$(document).on('click', '#deleteConcarBovedaHistory', function (event) {
			event.preventDefault();
			loading(true);

			var get_data = {};
			get_data.id = $(this).data("id");

			$.post('/sys/get_caja.php', {"delete_concar_boveda_history": get_data}, function () {
				filter_concar_boveda_table(0);
				loading(false);
			});
		});

		$(".btn_descargar_excel").off().on("click", function (event) {
			sec_descargar_concar();

			$("#modal_concar").modal("hide");
		});

		$(".btn_descargar_excel_boveda").off().on("click", function (event) {
			sec_descargar_concar_boveda();

			$("#modal_concar_boveda").modal("hide");
		});

		$('#modal_concar').off().on('shown.bs.modal', function () {
			$('#tipo_cambio').focus();
		});

		setTimeout(function () {
			var upload_btn = $(".upload-btn");
			var data = {};
			data["sec_caja_archivo"] = "tbl_transacciones_repositorio";
			var uploader = new ss.SimpleUpload({
				button: upload_btn,
				name: 'file',
				autoSubmit: true,
				data: data,
				debug: true,
				url: '/sys/get_caja_depositos.php',
				onChange: function (filename, extension, uploadBtn, fileSize, file) {
					console.log("uploader:onChange");
					console.log(file);
					$('#progress').html("");
					$('#progressBar').width(0);
					$("#filename").html(filename + " " + (file.size) + "Kb");
				},
				onSubmit: function (filename, extension, uploadBtn, size) {
					loading(true);
				},
				onComplete: function (filename, response, uploadBtn, size) {
					console.log(response);
					if (response.trim() == "true") {
						loading();
						swal({
								title: "Éxito!",
								text: "Las transacciones bancarias fueron importadas exitosamente.",
								type: "success",
								timer: 3000,
								closeOnConfirm: true
							},
							function () {
								swal.close();
								m_reload();
							});
					} else {
						loading();
						//debugger
						console.log(JSON.parse(response))
						var error = JSON.parse(response)
						var msg = "No se pudo Importar el Archivo"
						if(error.error == true && error.msg != undefined){
							msg = error.msg
						}
						swal("Error!", msg, "warning");
						return false;
					}
				},
				onProgress: function (progress) {
					$('#progress').html("Progreso: " + Math.round(progress) + "%");
					$('#progressBar').width(progress + "%");
				}
			});
		}, 1000);
	}

	$("#concar_file").on('change',(function(e) {
		let fileInput = document.getElementById('concar_file');
		let file = fileInput.files[0];

		let formData = new FormData();
		formData.append('concar_file', file);

		loading(true)
		$.ajax({
			url: '/sys/get_caja_depositos.php',
			type: "POST",
			data: formData,
			contentType: false,
			cache: false,
			processData:false,
			success: function(r) {
				loading(false)
				if (r){
					swal({
							title: "Éxito",
							text: "Las transacciones fueron importadas exitosamente.",
							type: "success",
							timer: 3000,
							closeOnConfirm: true
						},
						function () {
							swal.close();
						});
				} else {
					swal("Error!", "No se pudo Importar el Archivo", "warning");
				}
			},
			error: function(e) {
				loading();
				swal("Error!", "No se pudo Importar el Archivo", "warning");
			}
		});

	}));

	$(".depositos_btn")
		.off()
		.click(function (event) {
			sec_caja_get_depositos();
		});
	$(".search_btn")
		.off()
		.click(function (event) {
			sec_caja_get_reporte();
		});
	$(".validados_btn")
		.off()
		.click(function (event) {
			sec_caja_get_validados();
		});
	$(".jackpot_btn")
		.off()
		.click(function (event) {
			sec_caja_get_jackpot();
		});
	$(".faltantes_btn")
		.off()
		.click(function (event) {
			sec_caja_get_reporte_faltantes();
		});
	$(".auditoria_btn")
		.off()
		.click(function (event) {
			sec_caja_auditoria();
		});
	$(".compare_btn")
		.off()
		.click(function (event) {
			sec_caja_compare();
		});
	$(".load_turnos_btn")
		.off()
		.click(function (event) {
			sec_caja_get_turnos();
		});
	$(".abrir_turno_modal_btn")
		.off()
		.click(function (event) {
			/**
			 * Marcha Blanca para : id = 1433, TEST Sistemas
			 * id=993, Red At Listo La Marina
			 * id =706, Red AT San Roque Surco
			 * id =1578, Red AT Capacitación
			 */
			if(document.getElementById('hid_indicador_checklistusuario').value==0 && 
				(document.getElementById('usuario_local_id').value==1433 
				|| document.getElementById('usuario_local_id').value==993
				|| document.getElementById('usuario_local_id').value==706
				||document.getElementById('usuario_local_id').value==1578)
			){
				//en caso de marcha blanca sobre local test sistemas
				protoApp.interno();
			}else{
				sec_caja_abrir_turno_modal("show");
			}
		});

	$(document)
		.on("click", "#btn_show_images", function () {
			let caja_id = $(this).data("id");
			if (caja_id) $("#caja_aux_id").val(caja_id);
			$("#modal_premios_images").modal("show")
		});

	$("#btn_vincular_registros")
		.off()
		.click(function () {
			$("#modal_vinculacion").modal("show")
		});

	$(".caja_guardar_btn")
		.off()
		.click(function (event) {

			var param_prestamos_pendientes = $("#sec_caja_cant_prestamos_pendientes").val();

			if(param_prestamos_pendientes != 0)
			{
				swal({
					title: "",
					text: "Tiene un préstamo pendiente por aprobar del supervisor tienda receptora.",
					html:true,
					type: "info",
					closeOnConfirm: false,
					showCancelButton: false
				});

			    return false;
			}

			var param_salida_sin_confirmar = $("#sec_caja_cant_salida_sin_confirmar").val();

			if(param_salida_sin_confirmar != 0)
			{
				swal({
					title: "",
					text: "Tiene pendiente confirmar dinero entregado.",
					html:true,
					type: "info",
					closeOnConfirm: false,
					showCancelButton: false
				});

			    return false;
			}

			var param_ingreso_sin_confirmar = $("#sec_caja_cant_ingreso_sin_confirmar").val();

			if(param_ingreso_sin_confirmar != 0)
			{
				swal({
					title: "",
					text: "Tiene pendiente confirmar dinero recibido.",
					html:true,
					type: "info",
					closeOnConfirm: false,
					showCancelButton: false
				});

			    return false;
			}

			var param_cant_vincular_slot = $("#sec_caja_cant_vincular_slot").val();

			if(param_cant_vincular_slot != 0)
			{
				swal({
					title: "",
					text: "Tiene pendiente vincular préstamos Slot.",
					html:true,
					type: "info",
					closeOnConfirm: false,
					showCancelButton: false
				});

			    return false;
			}

			var param_cant_vincular_boveda_ingreso = $("#sec_caja_cant_vincular_boveda_ingreso").val();

			if(param_cant_vincular_boveda_ingreso != 0)
			{
				swal({
					title: "",
					text: "Tiene pendiente vincular préstamo bóveda.",
					html:true,
					type: "info",
					closeOnConfirm: false,
					showCancelButton: false
				});

			    return false;
			}

			var btn_data = $(this).data();
			var diferencia = $(".table_datos_fisicos .diferencia").html();
			//console.log(diferencia);
			if (parseFloat(diferencia) != 0.00) {
				proceed = false;
				swal({
						title: "¿Seguro?",
						text: "¿Existe diferencia, desea guardar?",
						type: "info",
						showCancelButton: true,
						// confirmButtonColor: "#DD6B55",
						confirmButtonText: "Si",
						cancelButtonText: "No",
						closeOnConfirm: false
					},
					function () {
						// proceed=true;
						sec_caja_guardar(btn_data);
						//m_reload();
					});

			} else {
				sec_caja_guardar(btn_data);
			}
			;
		});

	$("#caja_btn_vincular_slot_caja_eliminada").click(function()
	{
		
		var param_id_caja_actual = $(this).attr('data_id_caja_actual');
		
		swal(
		{
			title: '¿Está seguro de vincular Slot?',
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
					"accion": "caja_btn_vincular_slot_caja_eliminada",
					"id_caja_actual": param_id_caja_actual
				}

				auditoria_send({ "proceso": "caja_btn_vincular_slot_caja_eliminada", "data": data });

				$.ajax({
					url: "sys/set_caja.php",
					type: 'POST',
					data: data,
					beforeSend: function( xhr ) {
						loading(true);
					},
					success: function(data){
						
						var respuesta = JSON.parse(data);
						auditoria_send({ "respuesta": "caja_btn_vincular_slot_caja_eliminada", "data": respuesta });
						if(parseInt(respuesta.http_code) == 200)
						{
							swal({
								title: "Vinculación exitoso",
								text: "La vinculación fue exitoso.",
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
								title: "Error al guardar",
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
	})

	$(".caja_btn_slot_salida").click(function()
	{
		
		var param_id_prestamo = $(this).attr('data_id_prestamo');
		
		swal(
		{
			title: '¿Está seguro de confirmar entrega?',
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
					"accion": "caja_btn_slot_salida_confirmar_entrega",
					"id_prestamo": param_id_prestamo
				}

				auditoria_send({ "proceso": "caja_btn_slot_salida_confirmar_entrega", "data": data });

				$.ajax({
					url: "sys/set_caja.php",
					type: 'POST',
					data: data,
					beforeSend: function( xhr ) {
						loading(true);
					},
					success: function(data){
						
						var respuesta = JSON.parse(data);
						auditoria_send({ "respuesta": "caja_btn_slot_salida_confirmar_entrega", "data": respuesta });
						if(parseInt(respuesta.http_code) == 200)
						{
							swal({
								title: "Confirmación exitosa",
								text: "La confirmación fue exitosa.",
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
								title: "Error al guardar",
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
	})

	$(".caja_btn_slot_ingreso").click(function()
	{
		
		var param_id_prestamo = $(this).attr('data_id_prestamo');
		
		swal(
		{
			title: '¿Está seguro de confirmar recibo?',
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
					"accion": "caja_btn_slot_entrada_confirmar_recibo",
					"id_prestamo": param_id_prestamo
				}

				auditoria_send({ "proceso": "caja_btn_slot_entrada_confirmar_recibo", "data": data });

				$.ajax({
					url: "sys/set_caja.php",
					type: 'POST',
					data: data,
					beforeSend: function( xhr ) {
						loading(true);
					},
					success: function(data){
						
						var respuesta = JSON.parse(data);
						auditoria_send({ "respuesta": "caja_btn_slot_entrada_confirmar_recibo", "data": respuesta });
						if(parseInt(respuesta.http_code) == 200)
						{
							swal({
								title: "Confirmación exitoso",
								text: "La confirmación fue exitoso.",
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
								title: "Error al guardar",
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
	})

	$(".caja_btn_boveda_confirmar_ingreso").click(function()
	{
		
		var param_id_prestamo = $(this).attr('data_id_prestamo_boveda');
		var caja_id = $(this).attr('data_caja_id');
		
		swal(
		{
			title: '¿Está seguro de confirmar recibo de dinero bóveda?',
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
					"accion": "caja_btn_boveda_confirmar_ingreso",
					"id_prestamo": param_id_prestamo,
					"caja_id": caja_id
				}

				auditoria_send({ "proceso": "caja_btn_boveda_confirmar_ingreso", "data": data });

				$.ajax({
					url: "sys/set_caja.php",
					type: 'POST',
					data: data,
					beforeSend: function( xhr ) {
						loading(true);
					},
					success: function(data){
						
						var respuesta = JSON.parse(data);
						auditoria_send({ "respuesta": "caja_btn_boveda_confirmar_ingreso", "data": respuesta });
						if(parseInt(respuesta.http_code) == 200)
						{
							swal({
								title: "Confirmación exitoso",
								text: "La confirmación fue exitoso.",
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
								title: "Error al guardar",
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
	})

	$("#caja_btn_vincular_boveda_caja_eliminada").click(function()
	{
		
		var param_id_caja_actual = $(this).attr('data_id_caja_actual');
		
		swal(
		{
			title: '¿Está seguro de vincular Préstamo Bóveda?',
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
					"accion": "caja_btn_vincular_boveda_caja_eliminada",
					"id_caja_actual": param_id_caja_actual
				}

				auditoria_send({ "proceso": "caja_btn_vincular_boveda_caja_eliminada", "data": data });

				$.ajax({
					url: "sys/set_caja.php",
					type: 'POST',
					data: data,
					beforeSend: function( xhr ) {
						loading(true);
					},
					success: function(data){
						
						var respuesta = JSON.parse(data);
						auditoria_send({ "respuesta": "caja_btn_vincular_boveda_caja_eliminada", "data": respuesta });
						if(parseInt(respuesta.http_code) == 200)
						{
							swal({
								title: "Vinculación exitosa",
								text: "La vinculación fue exitosa.",
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
								title: "Error al guardar",
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
	})

	$(".caja_aceptar_abono_btn")
		.off()
		.click(function (event) {
			var id_caja = $(this).attr('data-id_caja');
			var id_solicitud = $(this).attr('data-id_solicitud');
			var monto = $(this).attr('data-monto');

			if ($.isNumeric(id_caja) == false || $.isNumeric(id_solicitud) == false) {
				swal({
					title: "Error!",
					text: "Error Consulte al Administrador.",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				});
				return false;
			}
			sec_caja_aceptar_abono(id_caja, id_solicitud, monto);
		});
		$('.negative-box-validator').keypress(function (e) {
			var txt = String.fromCharCode(e.which);
			if (!txt.match(/[0-9.]/g)) {
				return false;
			}
		});
	function sec_caja_eliminar() {
		console.log("sec_caja_eliminar");

		var fecha_operacion = moment($("#th_fecha_operacion").text(), "YYYY-MM-DD");
		var fecha_actual = moment().format("YYYY-MM-DD");
		var dias = moment.duration(fecha_operacion.diff(fecha_actual)).asDays();
		var mensaje = "¡Esta acción es irreversible!";
		var titulo = "¿Estás completamente seguro que desea eliminar?";

		if (dias <= -2) {
			mensaje = "Han pasado 2 o más días desde que se aperturo.";
		}
		;
		console.log(dias, "han pasado");

		var save_data = {};
		save_data.item_id = item_id;

		swal({
				title: titulo + '<br><span style="font-size:12px">Comentario :</span> <textarea autofocus id="txtComentario" name="txtComentario" class="form-control" style="display:block;font-size:11px;margin-top: -10px;"></textarea>',
				text: mensaje,
				html: true,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Si",
				cancelButtonText: "No",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function (opt) {
				if (opt) {
					loading(true);
					$.post('/sys/set_caja.php', {
						"sec_caja_eliminar": save_data
					}, function (r) {
						// console.log(r);
						save_data.response = r;
						save_data.mensaje = $("#txtComentario").val();
						auditoria_send_promise({
							"proceso": "sec_caja_eliminar",
							"data": save_data
						}).then(() => {
							loading(false);
							swal({
									title: "Eliminado!!!",
									text: "Ya no hay macha atras...",
									type: "success",
									timer: 1000,
									closeOnConfirm: true
								},
								function () {
									swal.close();
									loading(true);
									window.location = "/?&sec_id=caja";
								});
						})
						//auditoria_send({"proceso": "sec_caja_eliminar", "data": save_data});
					});
				} else {
					console.log("NONONONO");
					swal({
							title: "Estuvo cerca!",
							text: "La próxima piénsalo mejor!",
							type: "success",
							timer: 1000,
							closeOnConfirm: true
						},
						function (opt) {
							if (opt) {
								auditoria_send({"proceso": "sec_caja_eliminar_stop", "data": save_data});
							}
							// m_reload();
							// swal.close();
						});
				}
				// console.log(opt);
				// proceed=true;
				// sec_caja_guardar(btn_data);
				// sec_caja_eliminar();
			});
		// if(confirm("sure sure?")){
		// 	var save_data = {};
		// 		save_data.item_id = item_id;
		// 	$.post('/sys/set_caja.php', {
		// 		"sec_caja_eliminar": save_data
		// 	}, function(r) {
		// 		// console.log(r);
		// 		save_data.response = r;
		// 		auditoria_send({"proceso":"sec_caja_abrir_turno_remove","data":save_data});

		// 		window.location="/?&sec_id=caja";
		// 	});
		// }else{

		// }
	}

	$(".caja_eliminar_btn")
		.off()
		.click(function (event) {
			sec_caja_eliminar();
		});

	$('#disableClick').on('click', function (event) {
		event.preventDefault();
		$('#txt_archivo').click();
	});

	$("#archivoForm").submit(function (e) {
		e.preventDefault();
		var itemValidar = $("#txt_archivo").attr("itemValidar");
		var loginAreaID = $(".btn_eliminar_archivo_caja").attr("loginAreaID");
		var formData = new FormData(this);

		$.ajax({
			url: $(this).attr("action"),
			type: 'POST',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) {
				response = jQuery.parseJSON(data);
				if (response.status == 400) {
					swal({
						title: "¡Error!",
						text: response.message,
						type: "warning",
						timer: 3000,
						closeOnConfirm: true
					});
					return false;
				}
				$.each(response, function (index, file) {
					if (itemValidar == 1 && loginAreaID != 6) {
						$("#div_archivos_caja").append(
							'<tr>' +
							'<td style="border-bottom:none !important; cursor: pointer;">' +
							'<a href="./files_bucket/cajas/' + file.filename + '" download>' + file.filename + '</a>' +
							'</td>' +
							'</tr>');
						$("#txt_archivo").val("");
					} else {
						$("#div_archivos_caja").append(
							'<tr>' +
							'<td style="border-bottom:none !important;width: 10px !important;">' +
							'<button data-item="' + file.id + '" data-nombre="' + file.filename + '" class="btn btn-danger btn-xs btn_eliminar_archivo_caja">x</button>' +
							'</td>' +
							'<td style="border-bottom:none !important; cursor: pointer;">' +
							'<a href="./files_bucket/cajas/' + file.filename + '" download>' + file.filename + '</a>' +
							'</td>' +
							'</tr>');
						$("#txt_archivo").val("");
					}
				});
			},

		});
	});

	$("#txt_archivo").on("change", function (ev) {
		$("#archivoForm").submit();
		// var itemValidar = $("#txt_archivo").attr("itemValidar");
		// var loginAreaID = $(".btn_eliminar_archivo_caja").attr("loginAreaID");

		// var form_data = new FormData();

		//    form_data.append("item_id", item_id);
		//    form_data.append("sec_caja_archivo_guardar", "sec_caja_archivo_guardar");

		//    var file_data = "";

		//    if($("#txt_archivo").val!=""){
		//       file_data = $("#txt_archivo").prop("files")[0];
		//    }

		//    form_data.append("file", file_data);

		//    if(file_data!=""){
		//    	$.ajax({
		// 		url: '/sys/set_caja.php',
		// 		type: 'POST',
		// 		data: form_data,
		// 		cache: false,
		//            contentType: false,
		//            processData: false,
		// 	})
		// 	.done(function(r) {

		// 	})
		// 	.always(function(r){
		// 		console.log(r);
		// 	});
		//    }
	});

	// INICIO EDITADO 15/05/19 => auditoria
	$(document)
		.on("click", ".btn_eliminar_archivo_caja", function (ev) {
			auditoria_send({"proceso": "btn_eliminar_archivo_caja"});
			console.log("btn_eliminar_archivo_caja");
			var caja_id = item_id;
			var archivo_id = $(this).data("item");
			var archivo_nombre = $(this).data("nombre");
			var parent = $(this).parent().parent();
			swal({
					title: "Seguro?",
					text: "¿Que desea borrar la Imagen",
					type: "warning",
					showCancelButton: true,
					confirmButtonText: "Si",
					cancelButtonText: "No",
				},
				function (evt) {
					swal.close();
					if (evt) {
						caja_eliminar_archivo(parent, archivo_id, archivo_nombre, caja_id);
					}
				});
		});

	$(document)
		.on("click", ".btn_descargar_archivo", function (ev) {
			auditoria_send({"proceso": "btn_descargar_archivo"});
		});

	$(document)
		.on("click", ".btn_seleccionar_archivo", function (ev) {
			auditoria_send({"proceso": "btn_seleccionar_archivo"});
		});

	if (localStorage.getItem("sec_caja_auditoria_detalle") === "true"){
		setTimeout(() => {
			console.log("search_btn_autoclick");
			if (localStorage.sec_caja_reporte_local_id) {
				$("[name='local_id']").val(localStorage.sec_caja_reporte_local_id);
				$("[name='local_id']").trigger('change');
			}
			$(".search_btn_autoclick").click();
			
		}, "1000");
		localStorage.setItem("sec_caja_auditoria_detalle", "false");
	}

	function caja_eliminar_archivo(tr, id, nombre, caja_id) {

		var loginAreaID = $(".btn_eliminar_archivo_caja").attr("loginAreaID");

		var archivo_id = id;
		var archivo_nombre = nombre;
		var parent = tr;
		var caja_id = caja_id;
		$.post('/sys/set_caja.php', {
			"sec_caja_archivo_eliminar": archivo_id,
			"nombre_archivo": archivo_nombre,
			"caja_id": caja_id,
			"loginAreaID": loginAreaID
		}, function (r) {
			var respuesta = r;
			setTimeout(function () {
				if (respuesta == "ok") {
					parent.remove();
				}
				;
				if (respuesta == "validado") {
					console.log(respuesta, "respues");
					swal("Error!", "La Caja se Encuentra Validada ,no se puede Eliminar el archivo.", "warning");
				}
				;
				if (respuesta == "Error") {
					swal("Error!", "Error al tratar de eliminar el archivo.", "warning");
				}
				;
			}, 500);

		});
	}

	// $(".caja_guardar_btn").click();
	// sec_caja_guardar();
	// $(".caja_abrir_btn")
	// 	.off()
	// 	.click(function(event) {
	// 		sec_caja_guardar
	// 	});

	$(document).on("wheel", "input[type=number]", function (e) {
		$(this).blur();
	});

	$(".table_datos_del_sistema .updater")
		.on("change keyup paste click", function (ev) {
			// console.log(ev.keyCode);
			sec_caja_update_data("datos_del_sistema");
		});

	$(".table_datos_fisicos .updater")
		.on("change keyup paste click", function () {
			sec_caja_update_data("datos_fisicos");
			//console.log("updated")
		});

	$(".caja_apertura_fecha_proceso_datepicker")
		.datepicker({
			dateFormat: 'dd-mm-yy',
			changeMonth: true,
			maxDate:moment().format('DD-MM-YYYY'),
			// changeYear: true
		})
		.on("change", function (ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
		});

	$(".select2").select2({
		closeOnSelect: true,
		width: "100%",
	});

	$(".turnos_local_id_select")
		.off()
		.change(function (event) {
			sec_caja_get_turnos();
		});

	$(".turnos_local_id_select")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width: "300px"
	});

	$(".single_searcher")
		.each(function (index, el) {
			var search_input = $(this);
			var holder_id = $(this).data("holder_id");
			var item_class = $(this).data("item_class");
			var item_where = $(this).data("where");
			var search_clear_btn = $(this).parent().find('.search_clear_btn');
			search_clear_btn
				.off()
				.click(function (event) {
					search_input.val("").change().focus();
				});
			search_input
				.off()
				.on('change keyup paste click', function () {
					var searchTerm = force_plain_text(search_input.val());
					if (searchTerm) {
						localStorage.setItem("sec_caja_turno_searcher_" + item_where, searchTerm);
					} else {
						localStorage.removeItem("sec_caja_turno_searcher_" + item_where);
					}
					// console.log(searchTerm);
					$("#" + holder_id + " ." + item_class).each(function (index, itm) {
						$(itm).stop().hide();
						var item_text = force_plain_text($(itm).find("." + item_where).html());
						var n = item_text.indexOf(searchTerm);
						if (n >= 0) {
							$(itm).stop().show();
						}
					});
				})
				.click()
			;
		});


	////modal dep vincular
	var modal_dep_vincular = $("#depositos_libre_modal");
	$("#btn_abrir_modal_vincular").on("click", function () {
		modal_dep_vincular.modal("show");
	})
	$(".close_btn", modal_dep_vincular).on("click", function () {
		modal_dep_vincular.modal("hide");
	})
	$(".vincular_btn").on("click", function () {
		modal_dep_vincular.modal("hide");
	})
	modal_dep_vincular.on("hidden.bs.modal", function () {
		array_dep = [];
		$("input:checkbox[name=dep_vincular]:checked").each(function () {
			array_dep.push($(this).val());
		});
	})
	////

}

function sec_caja_update_data(w) {
	// console.log("sec_caja_update_data");
	// if(w=="datos_del_sistema"){
	var total_ingreso = 0;
	var total_salida = 0;
	var total_resultado = 0;
	$(".table_datos_del_sistema input.updater")
		.each(function (index, el) {
			var tipo = $(el).attr("name");
			var val = Number($(el).val());

			if (val < 0) {
				swal({
					title: "Error!",
					text: "El sistema no acepta valores negativos.",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				});
				$(this).val("");
				val = 0;
			}

			if (tipo == "ingreso") {
				total_ingreso += val;
			}
			if (tipo == "salida") {
				total_salida += val;
			}
		});

	$(".table_datos_del_sistema input.updater").on('wheel', function (event) {
		event.preventDefault();
	});
	$(".table_datos_del_sistema .lcdt").each(function (index, el) {
		var prev_v_total = Number($(el).find(".line_total").html().replace(",", ""));
		var v_in = Number($(el).find("input.updater[name='ingreso']").val());
		var v_out = Number($(el).find("input.updater[name='salida']").val());
		var v_total = (((v_in) ? v_in : 0) - ((v_out) ? v_out : 0));
		$(el).find(".line_total").html(number_format(v_total, 2));
		if (prev_v_total != v_total) {
			$(el).find(".line_total").finish().effect("highlight", 1500);
		}
	});

	total_resultado = total_ingreso - total_salida;

	var prev_total_ingreso = Number($(".table_datos_del_sistema .total_ingreso").html().replace(",", ""));
	$(".table_datos_del_sistema .total_ingreso").html(number_format(total_ingreso, 2));
	if (prev_total_ingreso != total_ingreso) {
		$(".table_datos_del_sistema .total_ingreso").finish().effect("highlight", 1500);
	}

	var prev_total_salida = Number($(".table_datos_del_sistema .total_salida").html().replace(",", ""));
	$(".table_datos_del_sistema .total_salida").html(number_format(total_salida, 2));
	if (prev_total_salida != total_salida) {
		$(".table_datos_del_sistema .total_salida").finish().effect("highlight", 1500);
	}

	var prev_total_resultado = Number($(".table_datos_del_sistema .total_resultado").html().replace(",", ""));
	$(".table_datos_del_sistema .total_resultado").html(number_format(total_resultado, 2));
	if (prev_total_resultado != total_resultado) {
		$(".table_datos_del_sistema .total_resultado").finish().effect("highlight", 1500);
	}

	var prev_df_5 = Number($(".table_datos_fisicos .df[data-tipo_id='5'] input").val());
	$(".table_datos_fisicos .df[data-tipo_id='5'] input").val(total_resultado.toFixed(2)).finish();
	if (prev_df_5 != total_resultado) {
		$(".table_datos_fisicos .df[data-tipo_id='5'] input").effect("highlight", 1500);
	}
	// }
	// if(w=="datos_fisicos"){
	var cierre = 0;
	$(".table_datos_fisicos .df").each(function (index, el) {
		var input = $(el).find("input.updater");
		var df_data = $(el).data();
		if (input.length) {
			var val = Number(input.val());
			if (df_data.operador == "+") {
				cierre += val;
			}
			if (df_data.operador == "-") {
				cierre -= val;
			}
		}
	});
	var prev_cierre = Number($(".table_datos_fisicos .df .cierre").val());
	$(".table_datos_fisicos .df .cierre").val(cierre.toFixed(2));
	if (prev_cierre != cierre) {
		$(".table_datos_fisicos .df .cierre").finish().effect("highlight", 1500);
	}


	var db_deuda_slot = Number($(".df_hidden .deuda_slot").data("db_val"));
	var deuda_slot = Number($(".df_hidden .deuda_slot").val());
	var prestamo_slot = Number($(".table_datos_fisicos .df .prestamo_slot").val());
	var devolucion_slot = Number($(".table_datos_fisicos .df .devolucion_slot").val());
	var new_deuda_slot = Number(db_deuda_slot + prestamo_slot - devolucion_slot).toFixed(2);

	$(".df_hidden .deuda_slot").val(new_deuda_slot);
	$(".table_info_turno .deuda_slot").html(number_format(new_deuda_slot, 2));
	if (new_deuda_slot > 0) {
		$(".table_info_turno .deuda_slot").addClass('bg-danger text-white text-bold');
	} else {
		$(".table_info_turno .deuda_slot").removeClass('bg-danger text-white text-bold');
	}


	var db_deuda_boveda = Number($(".df_hidden .deuda_boveda").data("db_val"));
	var deuda_boveda = Number($(".df_hidden .deuda_boveda").val());
	var prestamo_boveda = Number($(".table_datos_fisicos .df .prestamo_boveda").val());
	var devolucion_boveda = Number($(".table_datos_fisicos .df .devolucion_boveda").val());
	
	var devolucion_hermeticase_boveda = Number($(".table_datos_fisicos .df .devolucion_hermeticase").val());
	var new_deuda_boveda = Number(db_deuda_boveda + prestamo_boveda - devolucion_boveda - devolucion_hermeticase_boveda ).toFixed(2);
	//var new_deuda_boveda = Number(db_deuda_boveda + prestamo_boveda - devolucion_boveda).toFixed(2);
	$(".df_hidden .deuda_boveda").val(new_deuda_boveda);
	$(".table_info_turno .deuda_boveda").html(number_format(new_deuda_boveda, 2));
	if (new_deuda_boveda > 0) {
		$(".table_info_turno .deuda_boveda").addClass('bg-danger text-white text-bold');
	} else {
		$(".table_info_turno .deuda_boveda").removeClass('bg-danger text-white text-bold');
	}

	var v_in = $("#kasnet_ingreso").val();
	var v_out = $("#kasnet_salida").val();
	var result = (Number((v_out) ? v_out : 0) - Number((v_in) ? v_in : 0));
	var saldo_kasnet = Number($("#fixed_saldo_kasnet").val()) + Number(result);
	$(".table_info_turno .saldo_kasnet").html(number_format(saldo_kasnet, 2));

	change_kasnet_saldo();

	/*disashop*/
	var v_in = $("#disashop_ingreso").val();
    var result = - Number((v_in) ? v_in : 0);
    var saldo_disashop = Number($("#fixed_saldo_disashop").val()) + Number(result);
    $(".table_info_turno .saldo_disashop").html(number_format(saldo_disashop, 2));

	change_disashop_saldo();
	/*fin disashop*/
	
	// console.log(db_deuda_slot);
	// console.log(deuda_slot);
	// console.log(prestamo_slot);
	// console.log(devolucion_slot);
	// console.log(new_deuda_slot);
	// var new_deuda_slot =


	var diferencia = 0;
	var dinero_encontrado = Number($(".table_datos_fisicos .df .dinero_encontrado").val());
	diferencia = dinero_encontrado - cierre;
	$(".table_datos_fisicos .diferencia").removeClass('bg-danger bg-warning text-white text-bold');
	// $(".table_info_turno .diferencia").removeClass('bg-warning');
	if (diferencia > 0) {
		$(".table_datos_fisicos .diferencia").addClass('bg-warning text-white text-bold');
	} else if (diferencia < 0) {
		$(".table_datos_fisicos .diferencia").addClass('bg-danger text-white text-bold');
	} else {
		$(".table_datos_fisicos .diferencia").removeClass('bg-danger bg-warning text-white text-bold');
	}
	$(".table_datos_fisicos .diferencia").html(number_format(diferencia, 2)).finish().effect("highlight", 1500);
	// }
}

function sec_caja_abrir_turno_modal(opt) {
	// console.log("abrir_turno_modal:"+opt);
	// console.log(item_config);
	check_login();

	$("#abrir_turno_modal").modal(opt);
	$("#abrir_turno_modal .close_btn")
		.off()
		.click(function (event) {
			sec_caja_abrir_turno_modal("hide");
		});

	if (item_config.turnos_local_id) {
		$("#abrir_caja_local_id").val(item_config.turnos_local_id);
	}

	$("#abrir_caja_local_id")
		.off()
		.change(function (event) {
			var local_id = $(this).val();
			$.post('/sys/get_caja.php', {
				"get_local_cajas": local_id
			}, function (r) {
				try {
					$("#abrir_caja_local_caja_id").html(r);
					$("#abrir_caja_local_caja_id").change();
				} catch (err) {
					// console.log(r);
				}
			});
		});

	$("#abrir_caja_local_caja_id")
		.off()
		.change(function (event) {
			abrir_caja_monto_inicial_refresh();
		});
	$("#abrir_caja_local_id")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width: "100%"
	});
	$("#abrir_caja_local_id")
		.change();

	$("#abrir_turno_modal .open_btn")
		.off()
		.click(function (event) {
			sec_caja_abrir_turno();
		});
	$(".abrir_caja_monto_inicial_refresh_btn")
		.off()
		.click(function (event) {
			abrir_caja_monto_inicial_refresh();
		});
}

function abrir_caja_monto_inicial_refresh() {
	// console.log("abrir_caja_monto_inicial_refresh");
	loading(true);
	// $("#abrir_caja_apertura").val("1500.00");
	var get_data = {};
	$("#abrir_turno_modal .input_text_refresh").each(function (index, el) {
		get_data[$(el).attr("name")] = $(el).val();
	});
	// console.log(get_data);
	$.post('/sys/get_caja.php', {
		"abrir_caja_monto_inicial_refresh": get_data
	}, function (r) {
		try {
			var obj = jQuery.parseJSON(r);
			// console.log(obj);
			loading();
			$("#abrir_caja_apertura").val(obj.valor).finish().effect("highlight", 1500);
			get_data.valor = obj.valor;
			auditoria_send({"proceso": "abrir_caja_monto_inicial_refresh", "data": get_data});
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
}

function sec_caja_abrir_turno() {
	// console.log("sec_caja_abrir_turno");
	loading(true);
	var post_send = true;
	var save_data = {};
	save_data.turno_data = {};
	$("#abrir_turno_modal .input_text").each(function (index, el) {
		var name = $(el).attr("name");
		var val = $(el).val();
		if (val === null) {
			swal({
					title: "Error!",
					text: "Debe llenar todos los campos",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function () {
					// $("#abrir_turno_modal .input_text[name='"+name+"']").addClass("bg-danger",500, function(){ $("#abrir_turno_modal .input_text[name='"+name+"']").removeClass("bg-danger",1500,false); } );
					custom_highlight($("#abrir_turno_modal .input_text[name='" + name + "']"));
					swal.close();
				});
			post_send = false;
			return false;
		} else {
			save_data.turno_data[name] = val;
		}
	});
	save_data.datos_fisicos = {};
	$("#abrir_turno_modal .dato_fisico").each(function (index, el) {
		var df = {};
		df.tipo_id = $(el).data("tipo_id");
		df.valor = $(el).val();
		if (df.valor === null) {
			swal({
					title: "Error!",
					text: "Debe llenar todos los campos",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function () {
					custom_highlight($("#abrir_turno_modal .dato_fisico[data-tipo_id='" + tipo_id + "']"));
					swal.close();
				});
			post_send = false;
			return false;
		} else {
			save_data.datos_fisicos[df.tipo_id] = df;
		}
	});
	

	array_dep_libres = [];
	$("input:checkbox[name=dep_vincular]:checked").each(function () {
		array_dep_libres.push($(this).val());
	});
	save_data.depositos_libres = array_dep_libres;

	if (post_send) {
		// console.log(save_data);

		$.post('/sys/set_caja.php', {
			"sec_caja_abrir_turno": save_data
		}, function (r) {

			try {
				var obj = jQuery.parseJSON(r);
				console.log(obj.has_turnos);

				if (obj.caja_id) {
					save_data.caja_id = obj.caja_id;
					auditoria_send({"proceso": "sec_caja_abrir_turno", "data": save_data});
					// jQuery.extend({}, btn.data())
					window.location = "/?&sec_id=caja&item_id=" + obj.caja_id;
				} else if (obj.no_login) {
					auditoria_send({"proceso": "sec_caja_abrir_turno_no_login", "data": save_data});
					loading();
					// console.log(obj.exists);
					swal({
							title: "Por favor inicia sesión!",
							text: "",
							type: "warning",
							timer: 2000,
							closeOnConfirm: true
						},
						function () {
							m_reload();
							// swal.close();
						});
				} else if (obj.exists) {
					auditoria_send({"proceso": "sec_caja_abrir_turno_exists", "data": save_data});
					loading();
					// console.log(obj.exists);
					swal({
							title: "Ya existe!",
							text: "",
							type: "warning",
							timer: 3000,
							closeOnConfirm: true
						},
						function () {
							swal.close();
						});
				} else if (obj.open) {
					auditoria_send({"proceso": "sec_caja_abrir_turno_open", "data": save_data});
					loading();
					// console.log(obj.exists);
					swal({
							title: "Turno pendiente!",
							text: "Debe cerrar el turno anterior para poder abrir uno nuevo.",
							type: "warning",
							timer: 3000,
							closeOnConfirm: true
						},
						function () {
							swal.close();
						});
				} else if (obj.exists_turno_anterior) {
					auditoria_send({"proceso": "sec_caja_abrir_turno_sin_anterior", "data": save_data});
					loading();
					// console.log(obj.exists);
					swal({
							title: "Turno Anterior!",
							text: "Debe crear el turno anterior para poder abrir uno nuevo.",
							type: "warning",
							timer: 3000,
							closeOnConfirm: true
						},
						function () {
							swal.close();
						});
				} else if (obj.exists_fecha_superior) {
					auditoria_send({"proceso": "sec_caja_abrir_turno_fecha_superior", "data": save_data});
					loading();
					// console.log(obj.exists);
					swal({
							title: "Fecha Superior!",
							text: "No es permitido crear cajas caso ya existan en fechas futuras.",
							type: "warning",
							timer: 3000,
							closeOnConfirm: true
						},
						function () {
							swal.close();
						});
				} else if (obj.exists_fecha_anterior) {
					console.log("im here");
					auditoria_send({"proceso": "sec_caja_abrir_turno_sin_turno_fecha_anterior", "data": save_data});
					loading();
					// console.log(obj.exists);
					swal({
							title: "Turno Fecha Anterior!",
							text: "Debe crear el turno en la fecha anterior para poder abrir uno nuevo.",
							type: "warning",
							timer: 3000,
							closeOnConfirm: true
						},
						function () {
							swal.close();
						});
				}

			} catch (err) {
				save_data.res = r;
				auditoria_send({"proceso": "sec_caja_abrir_turno_error", "data": save_data});
				loading();
				swal({
						title: "Error!",
						text: "Reportar al administrador: " + r,
						type: "warning"
					},
					function () {
						m_reload();
						// swal.close();
					});
			}
			// console.log(r);
		});
	}
}

function sec_caja_guardar(btn_data) {
	// console.log("sec_caja_guardar");
	loading(true);
	var proceed = true;

	// console.log(diferencia);

	var save_data = {};
	save_data.item_id = item_id;
	save_data.estado = btn_data.estado;

	save_data.observaciones = $(".panel_observaciones_turno textarea[name='observaciones']").val();
	save_data.detalles = {};

	$(".lcdt").each(function (index, el) {
		var detalle = {};
		detalle.tipo_id = $(el).data("tipo_id");
		detalle.ingreso = $(el).find("input[name='ingreso']").val();
		detalle.salida = $(el).find("input[name='salida']").val();
		// console.log(detalle);
		save_data.detalles[detalle.tipo_id] = detalle;
		// console.log(el);
	});
	save_data.datos_fisicos = {};
	$(".df").each(function (index, el) {
		var df = {};
		df.tipo_id = $(el).data("tipo_id");
		// df.columna = $(el).data("columna");
		df.valor = $(el).find("input[name='valor']").val();
		// console.log(df);
		save_data.datos_fisicos[df.tipo_id] = df;
	});

	//proceed=false;
	if (proceed) {
		if (btn_data.estado == 1) {
			// var diferencia = $('.table_datos_fisicos .diferencia').text();
			// if (parseFloat($('.diferencia').html()) >= 1 || parseFloat($('.diferencia').html()) <= -1) {
			// 	enviar_correo($(".table_datos_fisicos .diferencia").html(), save_data);
			// } else {
				$.post('/sys/set_caja.php', {
					"sec_caja_guardar": save_data
				}, function (r) {
					//console.log(r);
					swal({
							title: "Guardado",
							text: "",
							type: "success",
							timer: 800,
							closeOnConfirm: true
						},
						function () {

							auditoria_send({"proceso": "save_item", "data": save_data});
							swal.close();
							m_reload();
						});
					// console.log(r);
				});
			// }
		} else {
			$.post('/sys/set_caja.php', {
				"sec_caja_guardar": save_data
			}, function (r) {
				swal({
						title: "Guardado",
						text: "",
						type: "success",
						timer: 800,
						closeOnConfirm: true
					},
					function () {
						auditoria_send({"proceso": "save_item", "data": save_data});
						swal.close();
						m_reload();
					});
				//console.log(r);
			});
		}
		;
		// console.log(save_data);
	} else {
		auditoria_send({"proceso": "save_item_error", "data": save_data});
		swal({
				title: "Error",
				text: "No se guardó!",
				type: "warning",
				timer: 3000,
				closeOnConfirm: true
			},
			function () {

				loading();
				swal.close();

			});
	}
}


// GUIA AUDITORIA
function sec_caja_aceptar_abono(id_caja, id_solicitud, monto) {
	loading(true);
	var save_data = {};
	save_data.id_caja = id_caja;
	save_data.id_solicitud = id_solicitud;
	save_data.monto = monto;
	$.post('/sys/set_solicitud_prestamo.php', {
		"opt": 'caja_aceptar_abono_guardar',
		"data": save_data
	}, function (r) {
		try {
			var obj = jQuery.parseJSON(r);
			if (obj.error) {
				save_data.error = obj.error;
				save_data.error_msg = obj.error_msg;
				auditoria_send({"proceso": "caja_aceptar_solicitud_error", "data": save_data});
				swal({
						title: "Error!",
						text: obj.error_msg,
						html: true,
						type: "warning",
						closeOnConfirm: true
					},
					function () {
						swal.close();
					});
			} else {
				auditoria_send({"proceso": "caja_aceptar_solicitud_done", "data": save_data});
				swal({
						title: "Abono Aceptado!",
						text: "",
						type: "success",
						timer: 3000,
						closeOnConfirm: true
					},
					function () {
						m_reload();
					});
			}
		} catch (err) {
			auditoria_send({"proceso": "caja_apcetar_solicitud_error_general", "data": r});
		}
		loading(false);
	});
}

function sec_caja_get_depositos() {
	loading(true);
	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja.php', {
		"sec_caja_depositos": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);
			sec_caja_depositos_events();
			filter_caja_depositos_table()
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
}

function sec_caja_get_depositosPreValidacion() {
	console.log("sec_caja_get_depositosPrevalidacion");
	loading(true);
	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	get_data.is_terminal = $("#terminales_switch").prop('checked')

	auditoria_send({"proceso":"daja_depositos_prevalidacion","data":get_data});
	$.post('/sys/get_caja.php', {
		"sec_caja_depositos_Prevalidacion": get_data
	}, function (response) {
		response = JSON.parse(response);

		var tbodyVenta = $('#tbConciliarMatchesVenta tbody');
		var tbodyBoveda = $('#tbConciliarMatchesBoveda tbody');
		tbodyVenta.html("");
		tbodyBoveda.html("");

		var props = []
		$.each(response, function (index, conciliaciones) {
			if (conciliaciones.venta != undefined) {
				if(conciliaciones.venta.length > 0){
					for (let i = 0; i < conciliaciones.venta.length; i++) {
						var tr = $('<tr onclick="selectRow(this)" style="background-color: #e2ffe7">');
						$('<td id="fecha_operacion">').html(conciliaciones.fecha_operacion).appendTo(tr);
						$('<td id="cc_id">').html(conciliaciones.cc_id).appendTo(tr);
						$('<td id="local_nombre">').html(conciliaciones.local_nombre).appendTo(tr);
						$('<td id="turno_id">').html(conciliaciones.turno_id).appendTo(tr);
						$('<td id="importe">').html(conciliaciones.depo_venta).appendTo(tr);
						$('<td id="depo_fecha">').html(conciliaciones.venta[i].fecha_operacion).appendTo(tr);
						$('<td id="depo_referencia">').html(conciliaciones.venta[i].referencia).appendTo(tr);
						$('<td id="depo_importe">').html(conciliaciones.venta[i].importe).appendTo(tr);
						$('<td id="depo_codigo">').html(conciliaciones.venta[i].codigo).appendTo(tr);
						$('<td id="depo_movimiento">').html(conciliaciones.venta[i].numero_movimiento).appendTo(tr);
						$('<td class="text-center">').html('<div class="checkbox"><label><input type="checkbox" id="chkConciliacionesVenta" checked="checked" name="chkConciliacionesVenta" class="form-check-input chkConciliacionesVenta"><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span><label</div>').appendTo(tr);

						$('<td style="display:none;" id="caja_id">').html(conciliaciones.id).appendTo(tr);
						$('<td style="display:none;" id="id_transaccion">').html(conciliaciones.venta[i].id).appendTo(tr);
						tbodyVenta.append(tr);
					}
				}
			}
			if (conciliaciones.boveda != undefined) {
				if(conciliaciones.boveda.length > 0){
					for (let i = 0; i < conciliaciones.boveda.length; i++) {
						var tr = $('<tr onclick="selectRow(this)" style="background-color: #e2ffe7">');
						$('<td id="fecha_operacion">').html(conciliaciones.fecha_operacion).appendTo(tr);
						$('<td id="cc_id">').html(conciliaciones.cc_id).appendTo(tr);
						$('<td id="local_nombre">').html(conciliaciones.local_nombre).appendTo(tr);
						$('<td id="turno_id">').html(conciliaciones.turno_id).appendTo(tr);
						$('<td id="importe">').html(conciliaciones.depo_boveda).appendTo(tr);
						$('<td id="depo_fecha">').html(conciliaciones.boveda[i].fecha_operacion).appendTo(tr);
						$('<td id="depo_referencia">').html(conciliaciones.boveda[i].referencia).appendTo(tr);
						$('<td id="depo_importe">').html(conciliaciones.boveda[i].importe).appendTo(tr);
						$('<td id="depo_codigo">').html(conciliaciones.boveda[i].codigo).appendTo(tr);
						$('<td id="depo_movimiento">').html(conciliaciones.boveda[i].numero_movimiento).appendTo(tr);
						$('<td>').html('<div class="checkbox"><label><input type="checkbox" id="chkConciliacionesBoveda" checked="checked" name="chkConciliacionesBoveda" class="form-check-input chkConciliacionesBoveda"><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span><label</div>').appendTo(tr);

						$('<td style="display:none;" id="caja_id">').html(conciliaciones.id).appendTo(tr);
						$('<td style="display:none;" id="id_transaccion">').html(conciliaciones.boveda[i].id).appendTo(tr);
						tbodyBoveda.append(tr);
					}
				}
			}
		});

		loading();
		$("#modal_prevalidacion").modal("show");
	});
}

function selectRow(row) {
	var firstInput = row.getElementsByTagName('input')[0];
	firstInput.checked = !firstInput.checked;
	if (firstInput.checked) row.setAttribute("style", "background-color: #e2ffe7");
	else row.setAttribute("style", "background-color: #fff");


}

function sec_caja_depositos_events() {
	console.log("sec_caja_depositos_events");

	$(".btn-conciliar").off().on("click", function () {
		sec_caja_get_depositosPreValidacion();
	});

	$("#btnSaveConciliaciones").off().on('click', function (event) {
		loading(true);
		event.preventDefault();
		var data = [];
		$('[id="chkConciliacionesVenta"]:checked').each(function (index, el) {
			data.push({
				"caja_id": $(this).closest('tr').find("#caja_id").html(),
				"id_transaccion": $(this).closest('tr').find("#id_transaccion").html(),
				"tipo": 0
			});
		});
		$('[id="chkConciliacionesBoveda"]:checked').each(function (index, el) {
			data.push({
				"caja_id": $(this).closest('tr').find("#caja_id").html(),
				"id_transaccion": $(this).closest('tr').find("#id_transaccion").html(),
				"tipo": 1
			});
		});

		auditoria_send({"proceso":"sec_caja_conciliar_automatico","data":data});
		$.post('/sys/get_caja.php', {
			"sec_caja_conciliar_automatico": data
		}, function (response) {
			console.log(response)
			auditoria_send({"proceso": "auto_conciliar", "data": data});
			$("#modal_prevalidacion").modal("hide");
			setTimeout(function () {
				loading();
				sec_caja_get_depositos();
			}, 1000);
		});

	});

	$("#btnRemoveConciliacion").off().on('click', function (event) {
		event.preventDefault();
		var data = {};
		data.where = "sec_quitarDeposito";
		data.idexcel = $("#txtId").text();
		data.caja_id = $("#txtCajaId").val();
		data.monto = $("#txtMonto").val();
		data.mvto = $("#txtMvto").val();
		data.referencia = $("#txtReferencia").val();
		swal({
				title: "Esta Seguro de quitar Deposito?",
				text: "mvto : <strong>" + data.mvto + "</strong> Monto : <strong>" + data.monto + "</strong> <br>Referencia : <strong>" + data.referencia + "</strong>",
				type: "warning",
				timer: 800,
				html: true,
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Si!",
				cancelButtonText: "No!",
				closeOnConfirm: true,
				closeOnCancel: true
			},
			function (isConfirm) {
				if (isConfirm) {
					$.ajax({
						data: data,
						type: "POST",
						url: "sys/sys_exceldepositos_sugerencia.php",
					})
						.done(function (dato, textStatus, jqXHR) {
							auditoria_send({"proceso": "edit_item", "data": data});
							swal.close();
							sec_caja_get_depositos();
							$("#mdDetalleConciliacion").modal("hide");
						});
				}
			});
	});

	$('#btnSelectConciliacion').off().on('click', function (event) {
		event.preventDefault();
		if ($('[id="chkTransacciones"]:checkbox:checked').length > 0) {
			loading(true);
			$('[id="chkTransacciones"]:checkbox:checked').each(function (i) {
				var data = {};

				data.where = "sec_relacionar";
				data.mvto = $(this).closest('tr').find('#numero_movimiento').html();
				data.idcaja = $("#txtDefId").html();
				data.tipo = $("#txtDefTipo").text();
				data.idexcel = $(this).closest('tr').find('#id').html();
				auditoria_send({"proceso": "sec_depositos_relacionar", "data": data});
				$.ajax({
					data: data,
					type: "POST",
					url: "sys/sys_exceldepositos_sugerencia.php",
				})
					.done(function (dato, textStatus, jqXHR) {
						var obj = dato;
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						if (console && console.log) {
							console.log("La solicitud  a fallado: " + textStatus);
						}
					})

			});
			$("#mdDefinirConciliacion").modal("hide");

			setTimeout(function () {
				loading();
				sec_caja_get_depositos();
			}, 1000);
		}


	});

	$('[id=btnCheckDeposito]').off().on("click", function (event) {
		event.preventDefault();
		loading(true);
		var get_data = jQuery.extend({}, item_config);
		get_data.dataset = $(this)[0].dataset;

		rowInfo = {
			"fecha": $(this).closest('tr').find('#tblFecha').html(),
			"cc": $(this).closest('tr').find('#tblCC').html(),
			"local": $(this).closest('tr').find('#tblLocal').html(),
			"turno": $(this).closest('tr').find('#tblTurno').html(),
			"venta": $(this).closest('tr').find('#tblMonto').html(),
			"boveda": $(this).closest('tr').find('#tblBoveda').html()
		}

		if ($(this)[0].dataset.cajaid == null) {
			var data = {};

			data.where = "sec_excel_depositosugerencias";
			data.fecha = $(this).closest('tr').find("#tblFecha").html();
			if (get_data.dataset.tipo == 0)
				data.monto = $(this).closest('tr').find("#tblMonto").html().replace(',', '');
			else
				data.monto = $(this).closest('tr').find("#tblBoveda").html().replace(',', '');
			data.cct = $(this).closest('tr').find("#tblCC").html();

			auditoria_send({"proceso": "sec_depositos_sugerencia", "data": data});
			$.ajax({
				data: data,
				type: "POST",
				url: "sys/sys_exceldepositos_sugerencia.php",
			})
				.done(function (response, textStatus, jqXHR) {
					response = JSON.parse(response);
					var tbody = $('#tbConciliacionFilter tbody');
					tbody.html("");
					$.each(response, function (index, sugerencias) {
						var tr = $('<tr onclick="selectRow(this)">');
						$.each(sugerencias, function (i, field) {
							$('<td id="' + i + '">').html(field).appendTo(tr);
						});
						$('<td>').html('<div class="checkbox"><label><input type="checkbox" id="chkTransacciones" name="chkTransacciones" class="form-check-input"><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span><label</div>').appendTo(tr);
						tbody.append(tr);
					});
					$("#txtDefId").html(get_data.dataset.id);
					$("#txtDefTipo").html(get_data.dataset.tipo);
					$("#txtDefConcFecha").val(rowInfo.fecha);
					$("#txtDefConcCC").val(rowInfo.cc);
					$("#txtDefConcLocal").val(rowInfo.local);
					$("#txtDefConcTurno").val(rowInfo.turno);
					$("#txtDefConcVenta").val(rowInfo.venta);
					$("#txtDefConcBoveda").val(rowInfo.boveda);
					$("#txtConciliacionFilter").val("");
					loading();
					$("#mdDefinirConciliacion").modal("show");
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					loading();
					console.log("La solicitud usuarios a fallado: " + textStatus);
				})
		} else {
			loading();
			$("#txtCajaId").val(get_data.dataset.cajaid);
			$("#txtId").text(get_data.dataset.id);
			$("#txtFecha").val(get_data.dataset.fecha);
			$("#txtMonto").val(get_data.dataset.monto);
			$("#txtMvto").val(get_data.dataset.mvto);
			$("#txtReferencia").val(get_data.dataset.referencia);
			if (get_data.dataset.tipo == 0) $("#txtTipo").val("Venta");
			else $("#txtTipo").val("Boveda");
			$("#mdDetalleConciliacion").modal("show");
		}
	});

	$('#modal_prevalidacion').off().on('hidden.bs.modal', function () {
		// sec_caja_get_depositos();
	});

	$('#mdDefinirConciliacion').off().on('shown.bs.modal', function () {
		$('#txtConciliacionFilter').focus();
	});

	$("#btnFilterConciliados").off().on('click', function (event) {
		event.preventDefault();
		if ($(this).text().indexOf("Filtrar") == 1) {
			$(this).html('<i id="icoFilterConciliados" class="glyphicon glyphicon-remove-sign"></i> Remover Filtro');
			$(this).removeClass('btn-default');
			$(this).addClass('btn-danger');
			$("#tbl_caja_depositos tbody tr").filter(function () {
				$(this).toggle(parseFloat($(this).find(".td_diferencia").html()) > 0 || parseFloat($(this).find(".td_diferencia_boveda").html()) > 0);
			});
		} else {
			$(this).html('<i id="icoFilterConciliados" class="glyphicon glyphicon-filter"></i> Filtrar Conciliados');
			$(this).removeClass('btn-danger');
			$(this).addClass('btn-default');
			$("#tbl_caja_depositos tbody tr").filter(function () {
				$(this).toggle(true);
			});
		}
	});

	$("#chkConciliacionesBovedaAll").on('change', function(){
		$(".chkConciliacionesBoveda").prop('checked', $(this).prop('checked'));
	})

	$("#chkConciliacionesVentaAll").on('change', function(){
		$(".chkConciliacionesVenta").prop('checked', $(this).prop('checked'));
	})
}

function enviar_correo(diferencia, data__) {

	loading(true);
	$.post('/sys/set_caja.php', {
		"sec_caja_guardar": data__
	}, function (r) {
		swal({
				title: "Guardado",
				text: "",
				type: "success",
				timer: 800,
				closeOnConfirm: true
			},
			function () {

				auditoria_send({"proceso": "save_item", "data": data__});
				swal.close();
				//m_reload();
			});
		// console.log(r);
	}).done(function () {
		var save_data = {};
		save_data.idcc = $("#idcc").attr("data-idcc");
		save_data.local = $('.table_info_turno').find('tr').eq(2).find("td").eq(0).html();
		save_data.apertura = $('.table_info_turno').find('tr').eq(6).find("td").eq(0).html();
		save_data.usuElimina = $(".user-name").text();
		var tablita = "";
		tablita += "<tr>";
		tablita += $(".table_info_turno").find('tr').eq(2).html();
		tablita += "</tr>";
		tablita += "<tr>";
		tablita += $(".table_info_turno").find('tr').eq(3).html();
		tablita += "</tr>";
		tablita += "<tr>";
		tablita += $(".table_info_turno").find('tr').eq(4).html();
		tablita += "</tr>";
		tablita += "<tr>";
		tablita += $(".table_info_turno").find('tr').eq(5).html();
		tablita += "</tr>";
		tablita += "<tr>";
		tablita += "<td><strong>Fecha Cierre</strong></td><td>" + moment().format("YYYY-MM-DD hh:mm:ss") + "</td>";
		tablita += "</tr>";
		tablita += "<tr>";
		tablita += $(".table_info_turno").find('tr').eq(6).html();
		tablita += "</tr>";
		tablita += "<tr>";
		tablita += $(".table_info_turno").find('tr').eq(8).html();
		tablita += "</tr>";
		save_data.turno = tablita;
		save_data.url = window.location.href;
		save_data.diferencia = diferencia;

		// console.log(save_data);
		$.post('/sys/set_caja.php', {
			"sec_caja_correo": save_data
		}, function (r) {
			auditoria_send({"proceso": "send_mail", "data": save_data});
			// console.log(r);
		}).done(function () {
			loading();
			m_reload();
		});

	});
};

function limpiar(text) {
	var text = text.toLowerCase(); // a minusculas
	text = text.replace(/[áàäâå]/, 'a');
	text = text.replace(/[éèëê]/, 'e');
	text = text.replace(/[íìïî]/, 'i');
	text = text.replace(/[óòöô]/, 'o');
	text = text.replace(/[úùüû]/, 'u');
	text = text.replace(/[ýÿ]/, 'y');
	text = text.replace(/[ñ]/, 'n');
	text = text.replace(/[ç]/, 'c');
	text = text.replace(/['"]/, '');
	text = text.replace(/[^a-zA-Z0-9-]/, '');
	text = text.replace(/\s+/, '-');
	text = text.replace(/' '/, '-');
	text = text.replace(/(_)$/, '');
	text = text.replace(/^(_)/, '');
	return text;
}

function sec_descargar_concar() {
	// console.log(item_config);
	if ($("#tipo_cambio").val() == "") {
		console.log("tipo de cambio vacio...");
		return false;
	}

	$(".item_config_concar").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	get_data.is_terminal = $("#terminales_switch").prop('checked')

	loading(true);
	auditoria_send({"proceso": "caja_depositos_concar", "data": get_data});
	$.ajax({
		url: '/export/caja_depositos_concar.php',
		type: 'post',
		data: {"sec_caja_concar_excel": get_data},
	})
		.done(function (dataresponse) {
			var obj = JSON.parse(dataresponse);
			window.open(obj.path);
			loading();
		})
}

function sec_descargar_concar_boveda() {
	// console.log(item_config);

	$(".item_config_concar_boveda").each(function (index, el) {
		let config_index = $(el).attr("name");
		let config_val = $(el).val();
		let ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});
	let get_data = jQuery.extend({}, item_config);

	loading(true);
	auditoria_send({"proceso": "caja_depositos_concar_boveda", "data": get_data});
	$.ajax({
		url: '/export/caja_depositos_concar_boveda.php',
		type: 'post',
		data: {"sec_caja_concar_excel_boveda": get_data},
	})
		.done(function (dataresponse) {
			loading();
			let obj;
			try{
				obj = JSON.parse(dataresponse)
			} catch(e){
				console.log(e);
				swal({
					title: "Mensaje",
					text: "No se han hallado registros",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				});
				return
			}
			window.open(obj.path);
		})
}

$(document).ready(function () {
	$("#txtConciliacionFilter").on("keyup", function () {
		var value = $(this).val().toLowerCase();
		$("#tbConciliacionFilter tbody tr").filter(function () {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		});
	});

	$(document).on('shown.bs.modal','#modal_vinculacion', function () {
		let data = {};
		data.caja_id = item_config["item_id"];

		loading(true);
		auditoria_send({"proceso":"get_tabla_reporte_premios_caja","data":data});
		$.post('/sys/get_reporte_premios.php', {"get_tabla_reporte_premios_caja": data}, function (response) {
			let result = response !== "" ? JSON.parse(response) : "";
			$("#linking-table-body").html('');
			if(result.rows === null){
				$("#linking-table-body").append(`<td style="text-align: center;font-size: 1.4em;padding: 1em 0;" colspan="11"> Sin registros </td>`)
			}
			else{
				[...result.rows].forEach(e => {
					$("#linking-table-body").append(`
					<tr>
						<td>${e.ticket_id}</td>
						<td>${e.tipo_premio}</td>
						<td>${e.nombre}</td>
						<td>${e.created_at}</td>
						<td>${e.monto_apostado}</td>
						<td>${e.monto_entregado}</td>
						<td>${e.tipo_doc}</td>
						<td>${e.num_doc}</td>
						<td>${e.usuario}</td>
						<td data-id="${e.id}"><input class="vincular_checkbox" type="checkbox"></td>                        
					</tr>
				`);
				});
			}
			loading(false);
		});
	})

	$(document).on('click', '#btn_save_links', function() {
		let data = {}
		data.ids = [];
		data.caja_id = item_config["item_id"];
		[...$(".vincular_checkbox:checked")].forEach(e =>{
			data.ids.push(e.parentElement.attributes["data-id"].value);
		})

		loading(true);
		auditoria_send({"proceso":"update_turno","data":data});
		$.post('/sys/get_registro_premios.php', {"update_turno": data}, function (response) {
			loading(false);
			swal({
				title: "Mensaje",
				text: "Registros vinculados",
				type: "success",
				timer: 3000,
				closeOnConfirm: true
			});
			$("#modal_vinculacion").modal("hide")
		});
	});

	$(document).on('shown.bs.modal','#modal_premios_images', function () {
		let data = {};
		data.caja_id = item_config["item_id"] === undefined ? $("#caja_aux_id").val() : item_config["item_id"];

		loading(true);
		auditoria_send({"proceso":"get_reporte_premios_images","data":data});
		$.post('/sys/get_reporte_premios.php', {"get_reporte_premios_images": data}, function (response) {
			let result = JSON.parse(response);
			$("#images_table_body").html('');
			if (response !== ""){
				if(result.rows === null){
					$("#images_table_body").append(`<td style="text-align: center;font-size: 1.4em;padding: 1em 0;" colspan="11"> Sin registros </td>`)
				}
				else if(result.rows.length < 1){
					$("#images_table_body").append(`<td style="text-align: center;font-size: 1.4em;padding: 1em 0;" colspan="11"> Sin registros </td>`)
				}
				else{
					Object.values(result.rows).forEach(e => {
						let marketingCount = e.foto_markt !== undefined ? e.foto_markt.length : 0;
						let idCount = e.foto_id !== undefined ? e.foto_id.length : 0;
						let vouchCount = e.foto_vouch !== undefined ? e.foto_vouch.length : 0;

						let marketingDisabled = marketingCount > 0 ? "" : "disabled='true'";
						let idDisabled = idCount > 0 ? "" : "disabled='true'";
						let vouchDisabled = vouchCount > 0 ? "" : "disabled='true'";

						$("#images_table_body").append(`
					<tr>
						<td>${e.ticket_id}</td>
						<td>${e.tipo_premio}</td>                        
						<td>${e.created_at}</td>
						<td>${e.monto_entregado}</td>
						<td>
							<button class='btn btn-rounded btn-primary btn-xs showImgs' ${marketingDisabled} type='button' name='button' data-id="${e.id}" data-type='markt'>
								<i class='fa fa-picture-o' aria-hidden='true'></i> (${marketingCount}) 
							</button>
						</td>
						<td>
							<button class='btn btn-rounded btn-primary btn-xs showImgs' ${idDisabled} type='button' name='button' data-id="${e.id}" data-type='id'>
								<i class='fa fa-picture-o' aria-hidden='true'></i> (${idCount}) 
							</button>
						</td>
						<td>
							<button class='btn btn-rounded btn-primary btn-xs showImgs' ${vouchDisabled} type='button' name='button' data-id="${e.id}" data-type='vouch'>
								<i class='fa fa-picture-o' aria-hidden='true'></i> (${vouchCount}) 
							</button>
						</td>
					</tr>
				`);
					});
				}
			}
			loading(false);
		});
	})

	$('#sec_caja_despositos_red_id').change(function()
	{
    	
        var param_caja_despositos_red_id = $('#sec_caja_despositos_red_id').val();
        
        var data = {
	        "accion": "sec_caja_depositos_listar_zonas",
	        "param_caja_despositos_red_id": param_caja_despositos_red_id
	    }
	    
	    var array_zonas = [];
	    
	    $.ajax({
	        url: "/sys/get_caja_depositos.php",
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
	            auditoria_send({ "respuesta": "sec_caja_depositos_listar_zonas", "data": respuesta });
	            
	            if(parseInt(respuesta.http_code) == 400)
	            {
	                if(parseInt(respuesta.codigo) == 1)
	                {

	                    var html = '<option value="_all_">Todos <i>(Puede demorar)</i></option>';
	                    $("#zona_id").html(html).trigger("change");

	                    setTimeout(function() {
	                        $('#zona_id').select2('open');
	                    }, 500);

	                    return false;
	                }
	                else if(parseInt(respuesta.codigo) == 2)
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
	            }
	            else if(parseInt(respuesta.http_code) == 200) 
	            {
	                array_zonas.push(respuesta.result);
	            
	                var html = '<option value="_all_">Todos <i>(Puede demorar)</i></option>';

	                for (var i = 0; i < array_zonas[0].length; i++) 
	                {
	                    html += '<option value=' + array_zonas[0][i].zona_id  + '>' + array_zonas[0][i].zona_nombre + ' - '+ array_zonas[0][i].empresa_nombre + '</option>';
	                }

	                $("#zona_id").html(html).trigger("change");

	                setTimeout(function() {
	                    $('#zona_id').select2('open');
	                }, 500);

	                return false;
	            }
	        },
	        error: function() {}
	    });
    });

    $('#zona_id').change(function()
	{
    	
        var param_red_id = $('#sec_caja_despositos_red_id').val();
		var param_zona_id = $('#zona_id').val();
        
        if(param_zona_id == null)
        {
        	return;
        }

        var data = {
	        "accion": "sec_caja_depositos_listar_locales_por_zona",
	        "param_red_id": param_red_id,
	        "param_zona_id": param_zona_id
	    }
	    
	    var array_locales = [];
	    
	    $.ajax({
	        url: "/sys/get_caja_depositos.php",
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
	            auditoria_send({ "respuesta": "sec_caja_depositos_listar_zonas", "data": respuesta });
	            
	            if(parseInt(respuesta.http_code) == 400)
	            {
	            	
	                if(parseInt(respuesta.codigo) == 1)
	                {

	                    var html = '<option value="_all_">Todos <i>(Puede demorar)</i></option>';
	                    $("#local_id").html(html).trigger("change");

	                    setTimeout(function() {
	                        $('#local_id').select2('open');
	                    }, 500);

	                    return false;
	                }
	                else if(parseInt(respuesta.codigo) == 2)
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
	            }
	            else if(parseInt(respuesta.http_code) == 200) 
	            {
	            	
	                array_locales.push(respuesta.result);
	            
	                var html = '<option value="_all_">Todos <i>(Puede demorar)</i></option>';

	                for (var i = 0; i < array_locales[0].length; i++) 
	                {
	                    html += '<option value=' + array_locales[0][i].id  + '>' + array_locales[0][i].nombre + '</option>';
	                }

	                $("#local_id").html(html).trigger("local_id");

	                setTimeout(function() {
	                    $('#local_id').select2('open');
	                }, 500);

	                return false;
	            }
	        },
	        error: function() {}
	    });
    })

	/*$('#zona_id').change(function(){
		
		$("#local_id").html("");
		var param_empresa_id = $('#sec_caja_despositos_empresa_id').val();
		var param_zona_id = $('#zona_id').val();

		var locales = "";

		if(param_zona_id != null)
		{
			locales = getLocalesCajaDepositos($(this).val(), param_empresa_id);
			//console.log(locales,'zona_id');
			
			if(locales.length != undefined)
			{
				
				$("#local_id").html("<option value='_all_' selected>Todos (puede demorar)</option>")
				locales.forEach(e => {
					let option = new Option(e.nombre, e.id, false, false)
					$("#local_id").append(option)
				})
			}	
		}
	})*/
	
	$('#concar_zona_id').change(function(){
		var locales = getLocalesCajaDepositos($(this).val());
		//console.log(locales,'concar_zona_id');
		$("#concar_local_id").html("<option value='_all_' selected>Todos (puede demorar)</option>")
		locales.forEach(e => {
			let option = new Option(e.nombre, e.id, false, false)
			$("#concar_local_id").append(option)
		})
	})

	$('#concar_boveda_zona_id').change(function(){
		var locales = getLocalesCajaDepositos($(this).val());
		//console.log(locales,'concar_boveda_zona_id');
		$("#concar_boveda_local_id").html("<option value='_all_' selected>Todos (puede demorar)</option>")
		locales.forEach(e => {
			let option = new Option(e.nombre, e.id, false, false)
			$("#concar_boveda_local_id").append(option)
		})
	})

	$('#concar_boveda_zona_id').change();


});

function getLocalesCajaDepositos(obj) {
	console.log("id: " + obj)
	var get_data = {
		zona_id: obj
	};
	var locales = {};


	$.ajax({
		type: "POST",
		url: '/sys/get_caja.php',
		async: false,
		data: {
			"get_locales_caja_depositos": get_data
		},
		success: function (response) {
			response = (response);
			if (response.error == undefined) {
				locales = response

			} else {
				console.log(response)
			}
		},
		dataType: "json"
	});

	return locales;

}

$(document).on('click', '.showImgs', function () {
	//$('#imgsModal').modal('show');
	var id = $(this).attr('data-id');
	var data = {};
	data.id = id;
	data.type = $(this).attr('data-type');
	auditoria_send({"proceso":"open_modal_premios","data":data});
	$.post('/sys/get_reporte_premios.php', {"open_modal_premios": data}, function (response) {
		let result = JSON.parse(response);
		console.log(result);
		customDataSuccess(result);
	});
});

function customDataSuccess(data) {
	let content = "";
	for (let i in data['items']) {
		let img = data['items'][i]['img'];
		content += "<div class='img-galery'><img src='../files_bucket/registros/premios/min_" + img + "' alt=" + img + " ></div>";
	}
	$(".gallery").html('');
	$(".gallery").append(content);
	$('#imgsModal').modal('show');
}

$('.close_fixed').on('click', function () {
	$(this).parent().parent().removeClass('active');
});

$('body').on('click', '.img-galery', function () {
	let imgs = $(this).find('img');
	let urls = imgs.attr('alt');
	console.log(urls);
	$('.fondo_fixed').find('img').attr('src', '../files_bucket/registros/premios/' + urls);
	$('.fondo_fixed').addClass('active');
});

// ******************************************************************************
// ******************************************************************************
$(document).on('click', '#btn_toggle_observaciones', function () {
	$("#btn_toggle_observaciones #_collapse").toggle();
	$("#body_observaciones_ci").toggle();
});

$(document).on('click', '#btnAgregarObservacion', function () {
	let id = $('#modalObservacionesCi #_id').val();
	let titulo = $('#modalObservacionesCi #_titulo').val();
	let descripcion = $('#modalObservacionesCi #_descripcion').val();

	if (titulo.length >= 3) {
		if (descripcion.length >= 5) {
			auditoria_send({"proceso":"sec_caja_observacion_control_interno","data":{
					"sec_caja_observacion_control_interno": true,
					"id": id,
					"titulo": titulo,
					"descripcion": descripcion
				}});
			$.post('/sys/set_caja.php', {
				"sec_caja_observacion_control_interno": true,
				"id": id,
				"titulo": titulo,
				"descripcion": descripcion
			}, function (response) {
				$("#modalObservacionesCi #_modal-error").html("");
				$("#modalObservacionesCi #_body").css({"display": "none"});
				$('#btnRemoveObservacion').css({"display": "none"});
				$('#btnAgregarObservacion').css({"display": "none"});
				if (response == "error") {
					$("#modalObservacionesCi #_response").html('<div class="alert alert-danger"><strong>Error</strong> :: al agregar observacin.</div>');
				} else {
					$("#modalObservacionesCi #_response").html('<div class="alert alert-success"><strong>Observación </strong> :: agregada correctamente.</div>');
					$('#body_observaciones_ci ._lista').html(response);
				}
			});
		} else {
			$("#modalObservacionesCi #_modal-error").html('<div class="alert alert-danger"><strong>Descripción</strong> :: muy corta.</div>');
		}
	} else {
		$("#modalObservacionesCi #_modal-error").html('<div class="alert alert-danger"><strong>Titulo</strong> :: muy corto.</div>');
	}
});

$(document).on('click', '#btnRemoveObservacion', function () {
	let id = $('#modalObservacionesCi #_id').val();

	auditoria_send({"proceso":"sec_caja_observacion_control_interno","data":{
			"sec_caja_observacion_control_interno": true,
			"tipo": "remove",
			"id": id
		}});
	$.post('/sys/set_caja.php', {
		"sec_caja_observacion_control_interno": true,
		"tipo": "remove",
		"id": id
	}, function (response) {
		$("#modalObservacionesCi #_modal-error").html("");
		$("#modalObservacionesCi #_body").css({"display": "none"});
		$('#btnRemoveObservacion').css({"display": "none"});
		$('#btnAgregarObservacion').css({"display": "none"});
		if (response == "error") {
			$("#modalObservacionesCi #_response-error").html('<div class="alert alert-danger"><strong>Error</strong> :: al eliminar observacin.</div>' + response);
		} else {
			$("#modalObservacionesCi #_response").html('<div class="alert alert-success"><strong>Observación </strong> :: eliminada correctamente.</div>');
			$('#body_observaciones_ci ._lista').html(response);
		}
	});
});

function modalObservacionesCi(tipo, id, titulo, descripcion) {
	let modal = "";
	var remove = "";
	var agregar = "Agregar";

	modal = tipo == "add" ? "Agregar" : "Editar";
	remove = tipo == "add" ? "none" : "block";
	agregar = tipo == "add" ? "Agregar" : "Editar";
	id = tipo == "add" ? 0 : id;
	titulo = tipo == "add" ? "" : titulo;
	descripcion = tipo == "add" ? "" : descripcion;

	$("#modalObservacionesCi #_body").css({"display": "block"});
	$('#modalObservacionesCi ._modal-titulo').html(modal + " observación");
	$('#modalObservacionesCi #_id').val(id);
	$('#modalObservacionesCi #_titulo').val(titulo);
	$('#modalObservacionesCi #_descripcion').val(descripcion);
	$('#btnRemoveObservacion').css({"display": remove});
	$('#btnAgregarObservacion').html(agregar);
	$('#btnAgregarObservacion').css({"display": "block"});
	$("#modalObservacionesCi #_modal-error").html("");
	$("#modalObservacionesCi #_response").html("");
	$('#modalObservacionesCi').modal('show');
}

// ***********************************
$(document).on('change', '#modalSelectListOci', function () {
	var valores = (this.value).split("@limit@");

	var idOci = valores[0];
	var titleOci = valores[1];
	var descOci = valores[2];
	var idCaja = valores[3];

	if (idOci > 0) {
		$('#modalDesOci').html(descOci);
		$("#btnAddOci").css({"display": "block"});
	} else {
		$('#modalDesOci').html("");
		$("#btnAddOci").css({"display": "none"});
	}
});

// AGREGAR OBSERVACION CONTROL INTERNO
$(document).on('click', '#btnAddOci', function () {
	var valores = ($("#modalSelectListOci").val()).split("@limit@");
	var idOci = valores[0];
	var tituloOci = valores[1];
	var descOci = valores[2];
	var idCaja = valores[3];

	if (idOci && idCaja) {
		$.post('/sys/set_caja.php', {"sec_caja_add_oci": true, "idOci": idOci, "idCaja": idCaja}, function (response) {
			if (response.trim() == "ok") {
				$("#caja_" + idCaja + "_oci").html('<div onclick="modalAddOci(' + idCaja + ',' + idOci + ',\'' + tituloOci + '\')"><div class="_description_oci view_more_btn" title="' + tituloOci + '">' + tituloOci.substr(0, 16) + '...</div></div>');
				$("#btnAddOci").css({"display": "none"});
				$("#btnRemoveOci").css({"display": "none"});
				$("#modalOciBody").css({"display": "none"});
				$("#modalOciBodyMsg").html('<div class="alert alert-success"><strong>Perfecto!</strong> Agregado correctamente.</div>');
			} else {
				$("#modalOciBodyMsg").html('<div class="alert alert-danger"><strong>Error!</strong> Ocurrio un error al agregar.</div>');
			}
		});
	}
});

// ELIMINAR OBSERVACION CONTROL INTERNO
$(document).on('click', '#btnRemoveOci', function () {
	var valores = ($("#modalSelectListOci").val()).split("@limit@");
	var idOci = 0;
	var tituloOci = "";
	var idCaja = $("#modalIdCajaSeleccionada").val();

	$.post('/sys/set_caja.php', {"sec_caja_remove_oci": true, "idCaja": idCaja}, function (response) {
		if (response.trim() == "ok") {
			$("#caja_" + idCaja + "_oci").html('<div class="_add_oci" onclick="modalAddOci(' + idCaja + ',' + idOci + ',\'' + tituloOci + '\')">+ </div>');

			$("#btnAddOci").css({"display": "none"});
			$("#btnRemoveOci").css({"display": "none"});
			$("#modalOciBody").css({"display": "none"});
			$("#modalOciBodyMsg").html('<div class="alert alert-success"><strong>Perfecto!</strong> Eliminado correctamente.</div>');
		} else {
			$("#modalOciBodyMsg").html('<div class="alert alert-danger"><strong>Error!</strong> Ocurrio un error al eliminar.</div>');
		}
	});
});

function modalAddOci(idCaja, editOci, tituloOci) {
	clearModalOci();

	var listaOci = JSON.parse($("#sec_caja_valores_globales").attr("listOciJson"));
	var nombreLocal = $("#sec_caja_valores_globales").attr("nombreLocal");

	var contenido = '';
	var descripcion = '';
	contenido += '<option value="0@limit@SELECCIONAR OBSERVACION">SELECCIONAR OBSERVACION</option>';
	for (i = 0; i < listaOci.length; i++) {
		let idOci = listaOci[i]['id'];
		let titleOci = listaOci[i]['titulo'];
		let descOci = listaOci[i]['descripcion'];
		let values = idOci + "@limit@" + titleOci + "@limit@" + descOci + "@limit@" + idCaja;

		if (idOci == editOci) {
			contenido += '<option selected value="' + values + '">' + titleOci + '</option>';
			descripcion = descOci;
		} else {
			contenido += '<option value="' + values + '">' + titleOci + '</option>';
		}
	}

	if (editOci > 0) {
		$("#btnAddOci").css({"display": "none"});
		$("#btnRemoveOci").css({"display": "block"});
	} else {
		$("#btnAddOci").css({"display": "none"});
		$("#btnRemoveOci").css({"display": "none"});
	}

	$('#modalIdCajaSeleccionada').val(idCaja);
	$('#modalSelectListOci').html(contenido);
	$('#modalDesOci').html(descripcion);
	$('#modalAddOci').modal('show');
}

function clearModalOci() {
	$('#modalIdCajaSeleccionada').val("");
	$("#modalOciBody").css({"display": "block"});
	$('#modalOciBodyMsg').html("");
	$('#modalDesOci').html("");
	$('#modalSelectListOci').html("");
}

let filter_caja_depositos_table = () => {
	if (!$("#tbl_caja_depositos").length) return

	let cantidad = {
		venta_inicio : $("#sec_caja_depositos_cantidad_inicio").val(),
		venta_fin : $("#sec_caja_depositos_cantidad_fin").val(),
		boveda_inicio : $("#sec_caja_depositos_boveda_cantidad_inicio").val(),
		boveda_fin : $("#sec_caja_depositos_boveda_cantidad_fin").val()
	}

	$("#tbl_caja_depositos tbody tr").filter(function() {
		let ventaShow = (cantidad.venta_inicio || cantidad.venta_fin) ? is_filter_amount_valid($(this).children("td"), cantidad, "venta") : true
		let bovedaShow = (cantidad.boveda_inicio || cantidad.boveda_fin) ? is_filter_amount_valid($(this).children("td"), cantidad, "boveda") : true
		$(this).toggle(ventaShow && bovedaShow)
	})

	let switch_sate = $("#terminales_switch").prop('checked')
	if (switch_sate) $('.terminales-hide').hide()
	else $('.terminales-hide').show()
}

let is_filter_amount_valid = (row, cantidad, type) => {
	let monto = get_float_value(row.eq(4).html())
	let deposito_venta = get_float_value(row.eq(5).children("button").eq(0).html())
	let devolucion_boveda = get_float_value(row.eq(7).html())
	let devolucion_banco = get_float_value(row.eq(8).children("button").eq(0).html())

	let cantidad_inicio, cantidad_fin

	let showRow = false
	let amounts

	if (type === "venta") {
		cantidad_inicio = cantidad.venta_inicio
		cantidad_fin = cantidad.venta_fin
		amounts = [monto, deposito_venta]
	}
	else if (type === "boveda") {
		cantidad_inicio = cantidad.boveda_inicio
		cantidad_fin = cantidad.boveda_fin
		amounts = [devolucion_boveda, devolucion_banco]
	}

	amounts.forEach( element => {
		showRow = showRow || is_range_valid( element, cantidad_inicio, cantidad_fin )
	})

	return showRow
}

let get_float_value = (value) => {
	return value == null ? 0.0 : parseFloat(value.replace(/,/g,''))
}

function is_range_valid(monto,cantidad_inicio,cantidad_fin){
	let isStartValid = false
	let isEndValid = false

	if(cantidad_inicio) {
		isStartValid = cantidad_inicio <= monto
	}
	if (cantidad_fin) {
		isEndValid = monto < cantidad_fin
	}
	if (cantidad_inicio && cantidad_fin) {
		return isStartValid && isEndValid
	}
	return isStartValid || isEndValid
}



let auditoria_send_post_promise = (d) => {
	return new Promise((resolve) => {
		$.post('sys/sys_auditoria.php', {
			"opt": 'auditoria_send'
			, "data": d
		}, function (r) {
			resolve();
		});
	});
}

let auditoria_send_promise = (data) => {
	return new Promise((resolve) => {
		if (!data) {
			data = {};
		}
		if (!data.proceso) {
			data.proceso = "visita";
		}
		if (!data.sec_id) {
			data.sec_id = sec_id;
		}
		if (!data.sub_sec_id) {
			data.sub_sec_id = sub_sec_id;
		}
		if (!data.item_id) {
			data.item_id = item_id;
		}
		if (!data.url) {
			data.url = window.location.href;
		}

		if (navigator.geolocation) {
			// console.log("geo OK");
			var gps = navigator.geolocation.getCurrentPosition(
				function (position) {
					var gl = {};
					gl.latitude = position.coords.latitude;
					gl.longitude = position.coords.longitude;
					gl.altitude = position.coords.altitude;
					gl.accuracy = position.coords.accuracy;
					gl.altitudeAccuracy = position.coords.altitudeAccuracy;
					gl.heading = position.coords.heading;
					gl.speed = position.coords.speed;
					var gl_json = JSON.stringify(gl);
					data.geolocation = gl_json;
					auditoria_send_post_promise(data).then(() => {
						resolve()
					});
				}, function (err) {
					// console.log(err);
					switch (err.code) {
						case err.PERMISSION_DENIED:
							var txt = "";
							txt += '<video width="400" controls><source src="files/howlocation2.mp4" type="video/mp4">Para continuar debes habilitar la ubicación.<br> Por favor haz click en el botón de abajo<br> para aprender cómo hacerlo.</video>';
							swal({
								title: '¡Ubicación no habilitada!',
								text: txt,
								type: 'warning',
								showCancelButton: false,
								closeOnConfirm: false,
								html: 1,
								confirmButtonText: "Aprende cómo habilitar aquí",
								allowEscapeKey: 0
							}, function (inputValue) {
								console.log(inputValue);
								window.open(
									'https://support.google.com/chrome/answer/114662',
									'_blank' // <- This is what makes it open in a new window.
								);
							});
							break;
						case err.POSITION_UNAVAILABLE:
							break;
						case err.TIMEOUT:
							break;
						case err.UNKNOWN_ERROR:
							break;
					}
					var gl = {};
					gl.error = err.message;
					var gl_json = JSON.stringify(gl);
					data.geolocation = gl_json;
					auditoria_send_post_promise(data).then(() => {
						resolve()
					});
				});
		} else {
			auditoria_send_post_promise(data).then(() => {
				resolve()
			});
		}




	});
}



///Denomicacion de Billetes y monedas
function sec_caja_abrir_modal_denominacion_billetes(readonly = false) {
	$('#modal_cierre_efectivo').modal({backdrop: 'static', keyboard: false});

	if (readonly) {
		$('#btn-guardar-cierre-efectivo').hide();
	}else{
		$('#btn-guardar-cierre-efectivo').show();
	}
	
	sec_caja_modal_readonly_denominacion_billetes(readonly); 

	var caja_id = $('#modal_ce_caja_id').val();
	var data = {
		action: "obtener_cierre_efectivo_por_caja_id",
		caja_id: caja_id,
	};


	auditoria_send({ proceso: "obtener_cierre_efectivo_por_caja_id", data: data });
	$.ajax({
		url: "sys/router/caja_cierre_efectivo/index.php",
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
			if (respuesta.status == 200) {
				$('#modal_ce_billete_10').val(respuesta.result.billete_10_cant);
				$('#modal_ce_billete_20').val(respuesta.result.billete_20_cant);
				$('#modal_ce_billete_50').val(respuesta.result.billete_50_cant);
				$('#modal_ce_billete_100').val(respuesta.result.billete_100_cant);
				$('#modal_ce_billete_200').val(respuesta.result.billete_200_cant);
				$('#modal_ce_moneda_001').val(respuesta.result.moneda_001_cant);
				$('#modal_ce_moneda_002').val(respuesta.result.moneda_002_cant);
				$('#modal_ce_moneda_005').val(respuesta.result.moneda_005_cant);
				$('#modal_ce_moneda_010').val(respuesta.result.moneda_010_cant);
				$('#modal_ce_moneda_020').val(respuesta.result.moneda_020_cant);
				$('#modal_ce_moneda_050').val(respuesta.result.moneda_050_cant);

				$('#modal_ce_importe_boveda').val(respuesta.result.monto_boveda);

				$('#lbl_ce_billete_total_10').val(respuesta.result.billete_10_total);
				$('#lbl_ce_billete_total_20').val(respuesta.result.billete_20_total);
				$('#lbl_ce_billete_total_50').val(respuesta.result.billete_50_total);
				$('#lbl_ce_billete_total_100').val(respuesta.result.billete_100_total);
				$('#lbl_ce_billete_total_200').val(respuesta.result.billete_200_total);
				$('#lbl_ce_moneda_total_001').val(respuesta.result.moneda_001_total);
				$('#lbl_ce_moneda_total_002').val(respuesta.result.moneda_002_total);
				$('#lbl_ce_moneda_total_005').val(respuesta.result.moneda_005_total);
				$('#lbl_ce_moneda_total_010').val(respuesta.result.moneda_010_total);
				$('#lbl_ce_moneda_total_020').val(respuesta.result.moneda_020_total);
				$('#lbl_ce_moneda_total_050').val(respuesta.result.moneda_050_total);
				if (respuesta.result.red_id == 9) {
					$('#div-container-boveda').show();
					// $('#modal_ce_importe_boveda').attr('readonly',false);
				}else{
					$('#div-container-boveda').hide();
					// $('#modal_ce_importe_boveda').attr('readonly',true);
				}

				sec_caja_modal_ce_calcular_monto_total();
			}else{
				alertify.error(respuesta.message, 5);
				$("#modal_cierre_efectivo").modal('hide');
				return false;
			}
			
		},
		error: function () {},
	});
}


function sec_caja_modal_readonly_denominacion_billetes(readonly = true) {

	$('#modal_ce_billete_10').attr('readonly',readonly);
	$('#modal_ce_billete_20').attr('readonly',readonly);
	$('#modal_ce_billete_50').attr('readonly',readonly);
	$('#modal_ce_billete_100').attr('readonly',readonly);
	$('#modal_ce_billete_200').attr('readonly',readonly);
	$('#modal_ce_moneda_001').attr('readonly',readonly);
	$('#modal_ce_moneda_002').attr('readonly',readonly);
	$('#modal_ce_moneda_005').attr('readonly',readonly);
	$('#modal_ce_moneda_010').attr('readonly',readonly);
	$('#modal_ce_moneda_020').attr('readonly',readonly);
	$('#modal_ce_moneda_050').attr('readonly',readonly);

	$('#modal_ce_importe_boveda').attr('readonly',readonly);

	$('#lbl_ce_billete_total_10').attr('readonly',readonly);
	$('#lbl_ce_billete_total_20').attr('readonly',readonly);
	$('#lbl_ce_billete_total_50').attr('readonly',readonly);
	$('#lbl_ce_billete_total_100').attr('readonly',readonly);
	$('#lbl_ce_billete_total_200').attr('readonly',readonly);
	$('#lbl_ce_moneda_total_001').attr('readonly',readonly);
	$('#lbl_ce_moneda_total_002').attr('readonly',readonly);
	$('#lbl_ce_moneda_total_005').attr('readonly',readonly);
	$('#lbl_ce_moneda_total_010').attr('readonly',readonly);
	$('#lbl_ce_moneda_total_020').attr('readonly',readonly);
	$('#lbl_ce_moneda_total_050').attr('readonly',readonly);
	
}

function sec_caja_modal_ce_validar_guardar_cierre_efectivo() {

	sec_caja_modal_ce_calcular_monto_total();

	var total = $('#modal_ce_total').val();

	swal({
		title: "¿Esta seguro de guardar el cierre de efectivo con "+total+"?",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		confirmButtonText: "Si, estoy de acuerdo!",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: true,
		closeOnCancel: true,

	},function (isConfirm) {
		if (isConfirm) {
			sec_caja_modal_ce_guardar_cierre_efectivo();
		} 
	});

}

function sec_caja_modal_ce_guardar_cierre_efectivo() {

	var caja_id = $('#modal_ce_caja_id').val();
	var billete_10 = $('#modal_ce_billete_10').val();
	var billete_20 = $('#modal_ce_billete_20').val();
	var billete_50 = $('#modal_ce_billete_50').val();
	var billete_100 = $('#modal_ce_billete_100').val();
	var billete_200 = $('#modal_ce_billete_200').val();
	var billete_total = $('#modal_ce_billete_total').val();

	var moneda_001 = $('#modal_ce_moneda_001').val();
	var moneda_002 = $('#modal_ce_moneda_002').val();
	var moneda_005 = $('#modal_ce_moneda_005').val();
	var moneda_010 = $('#modal_ce_moneda_010').val();
	var moneda_020 = $('#modal_ce_moneda_020').val();
	var moneda_050 = $('#modal_ce_moneda_050').val();
	var moneda_total = $('#modal_ce_moneda_total').val();

	var importe_boveda = $('#modal_ce_importe_boveda').val();
	var total = $('#modal_ce_total').val();

	var data = {
		action: "guardar_cierre_efectivo",
		caja_id: caja_id,
		billete_10: billete_10,
		billete_20: billete_20,
		billete_50: billete_50,
		billete_100: billete_100,
		billete_200: billete_200,
		billete_total: billete_total,
		moneda_001: moneda_001,
		moneda_002: moneda_002,
		moneda_005: moneda_005,
		moneda_010: moneda_010,
		moneda_020: moneda_020,
		moneda_050: moneda_050,
		moneda_total: moneda_total,
		importe_boveda: importe_boveda,
		total: total,
	};


	auditoria_send({ proceso: "guardar_cierre_efectivo", data: data });
	$.ajax({
		url: "sys/router/caja_cierre_efectivo/index.php",
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
			if (respuesta.status == 200) {
				$('#modal_cierre_efectivo').modal('hide');
				$('#cierre_efectivo_dinero_real').val(total);

				sec_caja_update_data("datos_del_sistema");
				var btn = { estado : $('.caja_guardar_btn').attr('data-estado')};
				setTimeout(() => {
					sec_caja_guardar(btn);
				}, 1000);

				alertify.success(respuesta.message, 5);
			}else{
				alertify.error(respuesta.message, 5);
				$("#modal_cierre_efectivo").modal('hide');
				return false;
			}
			
		},
		error: function () {},
	});


}

function sec_caja_modal_ce_calcular_monto_total() {

	//******** Billetes ***//
	var billete_10 = $('#modal_ce_billete_10').val();
	var billete_20 = $('#modal_ce_billete_20').val();
	var billete_50 = $('#modal_ce_billete_50').val();
	var billete_100 = $('#modal_ce_billete_100').val();
	var billete_200 = $('#modal_ce_billete_200').val();

	billete_10 = billete_10.length == 0 ? 0 : billete_10;
	billete_20 = billete_20.length == 0 ? 0 : billete_20;
	billete_50 = billete_50.length == 0 ? 0 : billete_50;
	billete_100 = billete_100.length == 0 ? 0 : billete_100;
	billete_200 = billete_200.length == 0 ? 0 : billete_200;

	$('#modal_ce_billete_10').val(billete_10);
	$('#modal_ce_billete_20').val(billete_20);
	$('#modal_ce_billete_50').val(billete_50);
	$('#modal_ce_billete_100').val(billete_100);
	$('#modal_ce_billete_200').val(billete_200);

	var total_billete_10 = (parseInt(billete_10) * parseFloat(10));
	var total_billete_20 = (parseInt(billete_20) * parseFloat(20));
	var total_billete_50 = (parseInt(billete_50) * parseFloat(50));
	var total_billete_100 = (parseInt(billete_100) * parseFloat(100));
	var total_billete_200 = (parseInt(billete_200) * parseFloat(200));

	var total_billetes = total_billete_10 + total_billete_20 + total_billete_50 + total_billete_100 + total_billete_200;
	$('#modal_ce_billete_total').val(parseFloat(total_billetes).toFixed(2));

	//******** Monedas ***//
	var moneda_001 = $('#modal_ce_moneda_001').val();
	var moneda_002 = $('#modal_ce_moneda_002').val();
	var moneda_005 = $('#modal_ce_moneda_005').val();
	var moneda_010 = $('#modal_ce_moneda_010').val();
	var moneda_020 = $('#modal_ce_moneda_020').val();
	var moneda_050 = $('#modal_ce_moneda_050').val();

	moneda_001 = moneda_001.length == 0 ? 0 : moneda_001;
	moneda_002 = moneda_002.length == 0 ? 0 : moneda_002;
	moneda_005 = moneda_005.length == 0 ? 0 : moneda_005;
	moneda_010 = moneda_010.length == 0 ? 0 : moneda_010;
	moneda_020 = moneda_020.length == 0 ? 0 : moneda_020;
	moneda_050 = moneda_050.length == 0 ? 0 : moneda_050;

	$('#modal_ce_moneda_001').val(moneda_001);
	$('#modal_ce_moneda_002').val(moneda_002);
	$('#modal_ce_moneda_005').val(moneda_005);
	$('#modal_ce_moneda_010').val(moneda_010);
	$('#modal_ce_moneda_020').val(moneda_020);
	$('#modal_ce_moneda_050').val(moneda_050);

	var total_moneda_001 = (parseInt(moneda_001) * parseFloat(0.1));
	var total_moneda_002 = (parseInt(moneda_002) * parseFloat(0.2));
	var total_moneda_005 = (parseInt(moneda_005) * parseFloat(0.5));
	var total_moneda_010 = (parseInt(moneda_010) * parseFloat(1));
	var total_moneda_020 = (parseInt(moneda_020) * parseFloat(2));
	var total_moneda_050 = (parseInt(moneda_050) * parseFloat(5));

	var total_monedas = total_moneda_001 + total_moneda_002 + total_moneda_005 + total_moneda_010 + total_moneda_020 + total_moneda_050;
	$('#modal_ce_moneda_total').val(parseFloat(total_monedas).toFixed(2));

	//******** Importe  Noveda ***//
	var importe_boveda = $('#modal_ce_importe_boveda').val();
	importe_boveda = importe_boveda.length == 0 ? 0 : importe_boveda;

	var total_cierre_efectivo = parseFloat(total_billetes) + parseFloat(total_monedas) + parseFloat(importe_boveda);

	// importe_boveda = parseFloat(importe_boveda).toFixed(2);
	// total_cierre_efectivo = parseFloat(total_cierre_efectivo).toFixed(2);

	$('#modal_ce_importe_boveda').val(parseFloat(importe_boveda).toFixed(2));
	$('#modal_ce_total').val(parseFloat(total_cierre_efectivo).toFixed(2));
	
	//******** Imprimir en formato en-US ***//
	total_billete_10 = formatearNumeroConDelimitador(total_billete_10);
	total_billete_20 = formatearNumeroConDelimitador(total_billete_20);
	total_billete_50 = formatearNumeroConDelimitador(total_billete_50);
	total_billete_100 = formatearNumeroConDelimitador(total_billete_100);
	total_billete_200 = formatearNumeroConDelimitador(total_billete_200);
	total_billetes = formatearNumeroConDelimitador(total_billetes);
	
	$('#lbl_ce_billete_total_10').html(total_billete_10);
	$('#lbl_ce_billete_total_20').html(total_billete_20);
	$('#lbl_ce_billete_total_50').html(total_billete_50);
	$('#lbl_ce_billete_total_100').html(total_billete_100);
	$('#lbl_ce_billete_total_200').html(total_billete_200);
	$('#lbl_ce_billete_total').html(total_billetes);
	
	total_moneda_001 = formatearNumeroConDelimitador(total_moneda_001);
	total_moneda_002 = formatearNumeroConDelimitador(total_moneda_002);
	total_moneda_005 = formatearNumeroConDelimitador(total_moneda_005);
	total_moneda_010 = formatearNumeroConDelimitador(total_moneda_010);
	total_moneda_020 = formatearNumeroConDelimitador(total_moneda_020);
	total_moneda_050 = formatearNumeroConDelimitador(total_moneda_050);
	total_monedas = formatearNumeroConDelimitador(total_monedas);

	$('#lbl_ce_moneda_total_001').html(total_moneda_001);
	$('#lbl_ce_moneda_total_002').html(total_moneda_002);
	$('#lbl_ce_moneda_total_005').html(total_moneda_005);
	$('#lbl_ce_moneda_total_010').html(total_moneda_010);
	$('#lbl_ce_moneda_total_020').html(total_moneda_020);
	$('#lbl_ce_moneda_total_050').html(total_moneda_050);
	$('#lbl_ce_moneda_total').html(total_monedas);
	
	
	total_cierre_efectivo = formatearNumeroConDelimitador(total_cierre_efectivo);
	$('#lbl_ce_total').html(total_cierre_efectivo);
}

function sec_caja_cerrar_modal_denominacion_billetes() {
	$('#modal_cierre_efectivo').modal('hide');
}


//Vincular Cierre efectivo

function sec_caja_abrir_modal_vinculacion_ce() {
	$('#modal_cierre_efectivo').modal({backdrop: 'static', keyboard: false});
	var caja_id = $('#modal_ce_caja_id').val();

	var data = {
		action: "vincular_caja_eliminada_detalle_efectivo",
		caja_id: caja_id,
	};


	auditoria_send({ proceso: "vincular_caja_eliminada_detalle_efectivo", data: data });
	$.ajax({
		url: "sys/router/caja_cierre_efectivo/index.php",
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
			if (respuesta.status == 200) {
				$('#modal_ce_billete_10').val(respuesta.result.billete_10_cant);
				$('#modal_ce_billete_20').val(respuesta.result.billete_20_cant);
				$('#modal_ce_billete_50').val(respuesta.result.billete_50_cant);
				$('#modal_ce_billete_100').val(respuesta.result.billete_100_cant);
				$('#modal_ce_billete_200').val(respuesta.result.billete_200_cant);
				$('#modal_ce_moneda_001').val(respuesta.result.moneda_001_cant);
				$('#modal_ce_moneda_002').val(respuesta.result.moneda_002_cant);
				$('#modal_ce_moneda_005').val(respuesta.result.moneda_005_cant);
				$('#modal_ce_moneda_010').val(respuesta.result.moneda_010_cant);
				$('#modal_ce_moneda_020').val(respuesta.result.moneda_020_cant);
				$('#modal_ce_moneda_050').val(respuesta.result.moneda_050_cant);

				$('#modal_ce_importe_boveda').val(respuesta.result.monto_boveda);

				$('#lbl_ce_billete_total_10').val(respuesta.result.billete_10_total);
				$('#lbl_ce_billete_total_20').val(respuesta.result.billete_20_total);
				$('#lbl_ce_billete_total_50').val(respuesta.result.billete_50_total);
				$('#lbl_ce_billete_total_100').val(respuesta.result.billete_100_total);
				$('#lbl_ce_billete_total_200').val(respuesta.result.billete_200_total);
				$('#lbl_ce_moneda_total_001').val(respuesta.result.moneda_001_total);
				$('#lbl_ce_moneda_total_002').val(respuesta.result.moneda_002_total);
				$('#lbl_ce_moneda_total_005').val(respuesta.result.moneda_005_total);
				$('#lbl_ce_moneda_total_010').val(respuesta.result.moneda_010_total);
				$('#lbl_ce_moneda_total_020').val(respuesta.result.moneda_020_total);
				$('#lbl_ce_moneda_total_050').val(respuesta.result.moneda_050_total);
				if (respuesta.result.red_id == 9) {
					$('#div-container-boveda').show();
					$('#modal_ce_importe_boveda').attr('readonly',false);
				}else{
					$('#div-container-boveda').hide();
					$('#modal_ce_importe_boveda').attr('readonly',true);
				}

				$('#cierre_efectivo_dinero_real').val(respuesta.result.monto_final);
				sec_caja_modal_ce_calcular_monto_total();
				sec_caja_update_data("datos_del_sistema");
				$('#btn-vinculacion-ce').hide();
				alertify.success(respuesta.message, 5);
			}else{
				alertify.error(respuesta.message, 5);
				$("#modal_cierre_efectivo").modal('hide');
				return false;
			}
			
		},
		error: function () {},
	});
}


function formatearNumeroConDelimitador(numero, locale = 'en-US', minDecimales = 2, maxDecimales = 2) {
	return numero.toLocaleString(locale, {
		style: 'decimal',
		minimumFractionDigits: minDecimales,
		maximumFractionDigits: maxDecimales,
		useGrouping: true,
	});
}

function sec_caja_reporte_get_locales() {
    let select = $("[name='local_id']");
    let valorSeleccionado = $("#local_id").val();

    $.ajax({
        url: "/sys/get_caja_reporte.php",
        type: "POST",
        data: {
            accion: "sec_caja_reporte_obtener_locales"
        },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
            $(select).empty();

            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
            });

            // Seleccionar el primer local por defecto
            $(select).prop('selectedIndex', 0);

            if (valorSeleccionado != null) {
                $(select).val(valorSeleccionado);
            }
        },
        error: function () {
            // Manejar el error si es necesario
        }
    });
}



function sec_caja_obtener_data_caja_detalle(caja_dato_fisico_id) {

	var data = {
		accion: 'obtener_caja_dato_fisico',
		caja_dato_fisico_id: caja_dato_fisico_id,
	};

	$.ajax({
        url: "/sys/set_caja.php",
        type: "POST",
        data: data,
        success: function (datos) {
            var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#modal_caja_dato_fisico').modal('show');
				$('#modal_dato_fisico_id').val(respuesta.result.id);
				$('#modal_dato_fisico_monto').val(respuesta.result.valor);
				$('#modal_dato_fisico_tipo').val(respuesta.result.nombre);
			}
        },
        error: function () {
            // Manejar el error si es necesario
        }
    });
}

function sec_caja_guardar_caja_datos_fisicos() {

	var id = $('#modal_dato_fisico_id').val();
	var valor = $('#modal_dato_fisico_monto').val();
	var caja_id = $('#modal_dato_caja_id').val();
	
	if (valor.length == 0) {
		alertify.error("Ingrese un valor", 5);
		$('#modal_dato_fisico_monto').focus();
		return;
	}

	if (parseFloat(valor) < 0) {
		alertify.error("Ingrese un valor", 5);
		$('#modal_dato_fisico_monto').focus();
		return;
	}

	var data = {
		accion: 'guardar_caja_dato_fisico',
		caja_id: caja_id,
		id: id,
		valor: valor,
	};

	$.ajax({
        url: "/sys/set_caja.php",
        type: "POST",
        data: data,
        success: function (datos) {
            var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#modal_caja_dato_fisico').modal('hide');
				m_reload();
			}
        },
        error: function () {
            // Manejar el error si es necesario
        }
    });

}


function sec_caja_modal_historial_cambios_datos_fisicos(caja_dato_fisico_id=null) {

	var data = {
		accion: 'historial_cambios_caja_dato_fisico',
		caja_dato_fisico_id: caja_dato_fisico_id,
	};

	$.ajax({
        url: "/sys/set_caja.php",
        type: "POST",
        data: data,
        success: function (datos) {
            var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#modal_caja_historial_dato_fisico').modal('show');
				$('#sec_table_historial_cambio_dato_fisico').html(respuesta.result);
			}
        },
        error: function () {
            // Manejar el error si es necesario
        }
    });
}

function sec_caja_depositos_transcribir_concar_boveda()
{
	$("#sec_caja_depositos_modal_transcribir_concar_boveda").modal("show");
}

function set_caja_depositos_file_transcribir_concar_boveda(object){
	
	$(document).on('click', '#btn_buscar_archivo', function(event) {
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
		
		$("#txt_concar_boveda_archivo").html(truncated);
	});
}

$("#sec_caja_depositos_modal_transcribir_concar_boveda .btn_guardar").off("click").on("click",function(){
    
	var param_archivo = document.getElementById("archivo_transcribir_concar_boveda");

	var tesoreria_fecha_comprobante_pago = $("#tesoreria_fecha_comprobante_pago").val();
	var mepa_programacion_id = $("#mepa_programacion_id").val();
	
	
	if(param_archivo.files.length <= 0)
    {
    	alertify.error('Seleccione el archivo',5);
        $("#archivo_transcribir_concar_boveda").focus();
        return false;
    }

    swal(
    {
        title: '¿Está seguro de transcribir?',
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
            var dataForm = new FormData($("#sec_caja_deposito_form_modal_transcribir_concar_boveda")[0]);
            dataForm.append("accion","sec_caja_depositos_modal_transcribir_concar_boveda");
            
            $.ajax({
                url: "sys/get_caja_depositos.php",
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

					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: respuesta.status,
							text: respuesta.error,
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        location.reload();
					    });

						return true;
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

function sec_caja_modal_datos_sistema(caja_detalle_id = null, tipo_id = null) {

	$('#modal_caja_dato_sistema').modal({backdrop: 'static', keyboard: false});
	var data = {
		accion: "obtener_dato_sistema",
		caja_detalle_id: caja_detalle_id,
		tipo_id: tipo_id,
	};

	auditoria_send({ proceso: "obtener_dato_sistema", data: data });
	$.ajax({
		url: "sys/set_caja.php",
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
			if (respuesta.status == 200) {
				$('#modal_dato_sistema_tipo').val(respuesta.result.nombre);
				$('#modal_dato_sistema_caja_detalle_id').val(respuesta.result.caja_detalle_id);
				$('#modal_dato_sistema_tipo_id').val(tipo_id);
				$('#modal_dato_sistema_ingreso').val(respuesta.result.ingreso);
				$('#modal_dato_sistema_salida').val(respuesta.result.salida);
				var resultado = parseFloat(respuesta.result.ingreso) - parseFloat(respuesta.result.salida);
				resultado = parseFloat(resultado).toFixed(2);
				$('#modal_dato_sistema_resultado').val(resultado);

				if (respuesta.result.out == "0") {
					$('#div_salida_modal_dato_sistema').hide();
				}else{
					$('#div_salida_modal_dato_sistema').show();
				}
			}else{
				alertify.error(respuesta.message, 5);
			}
			
		},
		error: function () {},
	});
}


function sec_caja_modal_dato_sistema_update_resultado() {
	var ingreso = $('#modal_dato_sistema_ingreso').val();
	var salida = $('#modal_dato_sistema_salida').val();

	ingreso = ingreso.length == 0 ? 0 : ingreso;
	salida = salida.length == 0 ? 0 : salida;

	var resultado = parseFloat(ingreso) - parseFloat(salida);

	ingreso = parseFloat(ingreso).toFixed(2);
	salida = parseFloat(salida).toFixed(2);
	resultado = parseFloat(resultado).toFixed(2);

	$('#modal_dato_sistema_ingreso').val(ingreso);
	$('#modal_dato_sistema_salida').val(salida);
	$('#modal_dato_sistema_resultado').val(resultado);
}

function sec_caja_modal_modificar_datos_sistema() {

	var caja_detalle_id = $('#modal_dato_sistema_caja_detalle_id').val();
	var caja_id = $('#modal_dato_sistema_caja_id').val();
	var tipo_id = $('#modal_dato_sistema_tipo_id').val();
	var local_id = $('#modal_dato_sistema_local_id').val();
	var ingreso = $('#modal_dato_sistema_ingreso').val();
	var salida = $('#modal_dato_sistema_salida').val();

	if (ingreso.length == 0) {
		$('#modal_dato_sistema_ingreso').focus();
		alertify.success('Inngrese un monto', 5);
		return false;
	}

	if (salida.length == 0) {
		$('#modal_dato_sistema_salida').focus();
		alertify.success('Inngrese un monto', 5);
		return false;
	}

	var data = {
		accion: "modificar_dato_sistema",
		caja_id: caja_id,
		tipo_id: tipo_id,
		caja_detalle_id: caja_detalle_id,
		local_id: local_id,
		ingreso: ingreso,
		salida: salida,
	};


	auditoria_send({ proceso: "modificar_dato_sistema", data: data });
	$.ajax({
		url: "sys/set_caja.php",
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
			if (respuesta.status == 200) {
				$('#modal_caja_dato_sistema').modal('hide');

				alertify.success(respuesta.message, 5);
				location.reload(true);
			}else{
				alertify.error(respuesta.message, 5);
			}
			
		},
		error: function () {},
	});
}


function sec_caja_modal_historial_cambios_datos_sistema(caja_detalle_id) {

	var data = {
		accion: 'historial_cambios_caja_dato_sistema',
		caja_detalle_id: caja_detalle_id,
	};

	$.ajax({
        url: "/sys/set_caja.php",
        type: "POST",
        data: data,
        success: function (datos) {
            var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#modal_caja_historial_dato_sistema').modal('show');
				$('#sec_table_historial_cambio_dato_sistema').html(respuesta.result);
			}
        },
        error: function () {
            // Manejar el error si es necesario
        }
    });
}
