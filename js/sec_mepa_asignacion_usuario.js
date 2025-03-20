var tabla;

function sec_mepa_asignacion_usuario() {
	sec_mepa_cargar_fechas();
	sec_mepa_obtener_usuario_creador();
	sec_mepa_obtener_usuario_aprobador();
	sec_mepa_obtener_areas();
	sec_mepa_asignacion_grupo_usuarios_listar_Datatable();

	$(".select_mepa").select2({
		width: "100%"
		});

	$('.limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
		});

	$('.limpiar_select_mepa').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val('').trigger("change");
		});

	$("#Frm_RegistroGrupo").submit(function (e) {
		e.preventDefault();
		sec_mepa_guardar_validar_grupo();
		});

	$("#Frm_RegistroUsuario").submit(function (e) {
		e.preventDefault();
		sec_mepa_guardar_validar_usuario();
		});

	$('#btn_mepa_limpiar_filtros_de_busqueda').click(function() {
		$('#area_id').select2().val('').trigger("change");
		$('#fecha_inicio').val('');
		$('#fecha_fin').val('');
	});
	$("#mepa_btn_export_grupo").on("click", function() {
		var area_id = $("#area_id").val();
		var fecha_fin = $("#fecha_fin").val();
		var fecha_inicio = $("#fecha_inicio").val();

		var data = {
			accion: "mepa_reporte_grupo",
			area_id: area_id,
			fecha_fin: fecha_fin,
			fecha_inicio: fecha_inicio
		};
		$.ajax({
			url: "/sys/set_mepa_asignacion_usuario.php",
			type: "POST",
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
					window.open(obj.ruta_archivo);
					loading(false);
				} else if (parseInt(obj.estado_archivo) == 0) {
					swal({
						title: "Error al generar el archivo excel",
						text: obj.ruta_archivo,
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
					return false;
				} else if (parseInt(obj.estado_archivo) == 2) {
					swal({
						title: "No hay data para generar el archivo excel",
					
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
					return false;
				}else {
					swal({
						title: "Error",
						text: "Ponerse en contacto con Soporte",
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
					return false;
				}
			},
			error: function(resp, status) {},
		});
	});
	}

// VISTA GRUPOS   /////////////////////////////////////////////////////////////////////////////////////

	//------------- Botones 
	function sec_mepa_obtener_usuarios_por_grupo_id(id_grupo) {

		let data = {
			id : id_grupo,
			accion:'mepa_asignacion_grupo_detalle'
			}

		$.ajax({
			url:  "/sys/get_mepa_asignacion_usuario.php",
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
				sec_mepa_obtener_usuario_integrante()
				$('#modalMantemientoUsuario').modal('show');
				$('#modal_title_mantenimiento_usuarios').html(('INTEGRANTES - '+respuesta.result.usuario_creador_nombre ).toUpperCase());
				$('#mepa_grupo_id').val(respuesta.result.id);
				sec_mepa_asignacion_listar_usuarios_Datatable(id_grupo);
				}
			},
			error: function (resp, status) {},
			});
		}
		
	function mepa_guardar_grupo(){
		$('#modal_title_mantenimiento_grupo').html(('NUEVO GRUPO').toUpperCase());
		$('#btn-form-mepa_registro-grupo-registrar').html('Registrar');
		$('#mepa_grupo_id').val(0);
		$('#mepa_grupo_titulo').val('');
		$('#mepa_grupo_descripcion').val('');
		$('#mepa_grupo_usuario_creador_id').val('');
		$('#mepa_grupo_usuario_aprobador_id').val('');
		$('#modalMantemientoGrupo').modal('show');
		sec_mepa_obtener_usuario_creador();
		sec_mepa_obtener_usuario_aprobador();
		}
	
	function sec_mepa_obtener_grupo(id_grupo){

		let data = {
			id : id_grupo,
			accion:'mepa_asignacion_grupo_detalle'
			}

		$.ajax({
			url:  "/sys/get_mepa_asignacion_usuario.php",
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
			console.log(respuesta);
			if (respuesta.status == 200) {
				$('#modalMantemientoGrupo').modal('show');
				$('#modal_title_mantenimiento_grupo').html(('GRUPO - '+respuesta.result.usuario_creador_nombre ).toUpperCase());
				$('#btn-form-mepa_registro-grupo-registrar').html('Modificar');
				$('#mepa_grupo_id').val(respuesta.result.id);
				$('#mepa_grupo_titulo').val(respuesta.result.titulo);
				$('#mepa_grupo_descripcion').val(respuesta.result.descripcion);
				if (respuesta.result.reportar_gerencia == 1) {
					$('#mepa_grupo_reportar_gerencia').prop('checked', true);
				} else {
					$('#mepa_grupo_reportar_gerencia').prop('checked', false);
				}
				$('#mepa_grupo_usuario_creador_id').val(respuesta.result.usuario_creador_id);
				$('#mepa_grupo_usuario_aprobador_id').val(respuesta.result.usuario_aprobador_id);
				sec_mepa_obtener_usuario_creador();
				sec_mepa_obtener_usuario_aprobador();
			}
			},
			error: function (resp, status) {},
		});
		}

	function sec_mepa_obtener_historico_usuarios_por_grupo_id(id_grupo) {

			let data = {
				id : id_grupo,
				accion:'mepa_asignacion_grupo_detalle'
				}
		
			$.ajax({
				url:  "/sys/get_mepa_asignacion_usuario.php",
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
					$('#modalMepaHistoricoUsuario').modal('show');
					$('#modal_title_mepa_historico_usuarios').html(('HISTORICO DE USUARIOS - '+respuesta.result.usuario_creador_nombre ).toUpperCase());
					$('#mepa_grupo_id').val(respuesta.result.id);
					sec_mepa_asignacion_listar_usuarios_creador_Datatable(id_grupo);
					sec_mepa_asignacion_listar_usuarios_aprobador_Datatable(id_grupo);
					sec_mepa_asignacion_listar_usuarios_integrante_Datatable(id_grupo);
		
					}
				},
				error: function (resp, status) {},
				});
		}

	//------------- Validación de formularios
	function sec_mepa_guardar_validar_grupo() {
		var id = $('#mepa_grupo_id').val();
		var usuario_creador_id = $('#mepa_grupo_usuario_creador_id').val();
		var usuario_aprobador_id = $('#mepa_grupo_usuario_aprobador_id').val();
		var titulo = $('#mepa_grupo_titulo').val();
		var descripcion = $('#mepa_grupo_descripcion').val();

		var validacionCorrecta = true;

		if (usuario_creador_id == 0) {
			alertify.error("Seleccione un usuario creador", 5);
			$('#mepa_grupo_usuario_creador_id').focus();
			validacionCorrecta = false;
		} else {
			$.ajax({
				url: "/sys/get_mepa_asignacion_usuario.php",
				type: "POST",
				data: {
					accion: "mepa_consultar_usuario_en_grupo",
					usuario_id: usuario_creador_id,
					grupo_id: id
				},
				success: function (resp) {
					var respuesta = JSON.parse(resp);
					if (parseInt(respuesta.http_code) == 400) {
						swal({
							title: "Escoge otro usuario creador",
							text: respuesta.result,
							html: true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						$('#mepa_grupo_usuario_creador_id').focus();
						validacionCorrecta = false;
					}
					validarAprobador();
				},
				error: function () {
					console.error("Error al verificar el ID.");
				}
			});
		}

		function validarAprobador() {
			if (usuario_aprobador_id == 0) {
				alertify.error("Seleccione un usuario aprobador", 5);
				$('#mepa_grupo_usuario_aprobador_id').focus();
				validacionCorrecta = false;
			} else {
				$.ajax({
					url: "/sys/get_mepa_asignacion_usuario.php",
					type: "POST",
					data: {
						accion: "mepa_consultar_usuario_en_grupo",
						usuario_id: usuario_aprobador_id,
						grupo_id: id
					},
					success: function (resp) {
						var respuesta = JSON.parse(resp);
						if (parseInt(respuesta.http_code) == 400) {
							swal({
								title: "Escoge otro usuario aprobador",
								text: respuesta.result,
								html: true,
								type: "warning",
								closeOnConfirm: false,
								showCancelButton: false
							});
							$('#mepa_grupo_usuario_aprobador_id').focus();
							validacionCorrecta = false;
						}
						validarTitulo();
					},
					error: function () {
						console.error("Error al verificar el ID.");
					}
				});
			}
		}

		function validarTitulo() {
			if (titulo.length == 0) {
				alertify.error("Ingrese un título", 5);
				$('#mepa_grupo_titulo').focus();
				validacionCorrecta = false;
			}
			validarDescripcion();
		}

		function validarDescripcion() {
			if (descripcion.length == 0) {
				alertify.error("Ingrese una descripción", 5);
				$('#mepa_grupo_descripcion').focus();
				validacionCorrecta = false;
			}
			mostrarSwal();
		}

		function mostrarSwal() {
			if (validacionCorrecta) {
				var title = '';
				if (id.length == 0 || id == 0) {
					title = "¿Está seguro de registrar el grupo?";
				} else {
					title = "¿Está seguro de modificar el grupo?";
				}
				swal({
					title: title,
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					confirmButtonText: "Sí, estoy de acuerdo",
					cancelButtonText: "No, cancelar",
					closeOnConfirm: true,
					closeOnCancel: true,
				}, function (isConfirm) {
					if (isConfirm) {
						sec_mepa_guardar_grupo();
					}
				});
			}
		}
		}

	function sec_mepa_guardar_validar_usuario() {
		var grupo_id = $('#mepa_grupo_id').val();
		var usuario_integrante_id = $('#mepa_grupo_usuario_integrante_id').val();
		
			if (usuario_integrante_id == 0 ) {
			alertify.error("Seleccione un usuario integrante", 5);
			$('#mepa_grupo_usuario_integrante_id').focus();
			return false;
			}else{
				$.ajax({
					url: "/sys/get_mepa_asignacion_usuario.php",
					type: "POST",
					data: {
						accion: "mepa_consultar_usuario_en_grupo",
						usuario_id: usuario_integrante_id,
						grupo_id: grupo_id
					},
					success: function (resp) {
						var respuesta = JSON.parse(resp);
						if (parseInt(respuesta.http_code) == 400) {
							swal({
								title: "Escoge otro usuario aprobador",
								text: respuesta.result,
								html: true,
								type: "warning",
								closeOnConfirm: false,
								showCancelButton: false
							});
							$('#mepa_grupo_usuario_aprobador_id').focus();
							validacionCorrecta = false;
						}
						else{
							swal({
								title: "¿Esta seguro de registrar el usuario?",
								type: "warning",
								showCancelButton: true,
								confirmButtonColor: '#3085d6',
								confirmButtonText: "Si, estoy de acuerdo!",
								cancelButtonText: "No, cancelar",
								closeOnConfirm: true,
								closeOnCancel: true,
							
							},function (isConfirm) {
								if (isConfirm) {
								sec_mepa_guardar_usuario();
								} 
							});
						}
					},
					error: function () {
						console.error("Error al verificar el ID.");
					}
				});
			}
			
		}

	//------------- Funciones
	function sec_mepa_obtener_areas() {
		let select = $("[name='search_id_mepa_area_id']");
		let valorSeleccionado = $("#area_id").val();
	
		$.ajax({
			url: "/sys/get_mepa_asignacion_usuario.php",
			type: "POST",
			data: {
				accion: "mepa_obtener_areas"
			},
			success: function (datos) {
				var respuesta = JSON.parse(datos);
				$(select).empty();
				if (!valorSeleccionado) {
					let opcionDefault = $('<option value="">-- TODOS --</option>');
					$(select).append(opcionDefault);
				}
	
				$(respuesta.result).each(function (i, e) {
					let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
				});
	
				if (valorSeleccionado != null) {
					$(select).val(valorSeleccionado);
				}
			},
			error: function () {
			}
		});
		}
	function sec_mepa_cargar_fechas() {
		$(".mepa_grupo_datepicker").datepicker({
			dateFormat: "dd-mm-yy",
			changeMonth: true,
			changeYear: true,
			});
		}

	function mepa_asignacion_grupo_usuarios_buscar_por_parametros() {
		sec_mepa_asignacion_grupo_usuarios_listar_Datatable();
		}

	function sec_mepa_asignacion_grupo_usuarios_listar_Datatable()
	{
		if (sec_id == "mepa" && sub_sec_id == "asignacion_usuario")
		{
			var area_id = $("#area_id").val();
			var param_usuario = $("#param_usuario").val();
			var fecha_inicio = $("#fecha_inicio").val();
			var fecha_fin = $("#fecha_fin").val();

			if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
				var fecha_inicio_date = new Date(fecha_inicio);
				var fecha_fin_date = new Date(fecha_fin);
				if (fecha_inicio_date > fecha_fin_date) {
					alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
					return false;
				}
			}
		
			var data = {
				accion: "mepa_asignacion_grupo_usuarios",
				area_id: area_id,
				param_usuario: param_usuario,
				fecha_inicio: fecha_inicio,
				fecha_fin: fecha_fin
			};
			$("#mepa_asignacion_grupo_usuario_div_tabla").show();

			$('#mepa_asignacion_grupo_usuario_datatable tfoot th').each(function () {
				var title = $(this).text();
				$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
			});

			tabla = $("#mepa_asignacion_grupo_usuario_datatable").dataTable({
				language: {
					decimal: "",
					emptyTable: "No existen registros",
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
					],
				aProcessing: true,
				aServerSide: true,
				ajax: {
					url: "/sys/get_mepa_asignacion_usuario.php",
					data: data,
					type: "POST",
					dataType: "json",
					error: function(e) {
						console.log(e.responseText);
					},
				},
				createdRow: function(row, data, dataIndex) {
					if (data[0] === 'error') {
						$('td:eq(0)', row).attr('colspan', 7);
						$('td:eq(0)', row).attr('align', 'center');
						$('td:eq(1)', row).css('display', 'none');
						$('td:eq(2)', row).css('display', 'none');
						$('td:eq(3)', row).css('display', 'none');
						$('td:eq(4)', row).css('display', 'none');
						$('td:eq(5)', row).css('display', 'none');
						$('td:eq(6)', row).css('display', 'none');
						this.api().cell($('td:eq(0)', row)).data(data[1]);
					}
				},
				bDestroy: true,
				aLengthMenu: [10, 20, 30, 40, 50, 100],
				initComplete: function () {
					this.api()
					.columns()
					.every(function () {
						var that = this;

						$('input', this.footer()).on('keyup change clear', function () {
							if (that.search() !== this.value) {
								that.search(this.value).draw();
							}
						});
					});
				},
			}).DataTable();
		}
		}

// VISTA MODAL "VER DETALLE"   ///////////////////////////////////////////////////////////////////////////

	//------------- Botones
	function sec_mepa_guardar_grupo() {

		var grupo_id = $('#mepa_grupo_id').val();
		var usuario_creador_id = $('#mepa_grupo_usuario_creador_id').val();
		var usuario_aprobador_id = $('#mepa_grupo_usuario_aprobador_id').val();
		var reportar_gerencia = ($('#mepa_grupo_reportar_gerencia').prop('checked')) ? 1 : 0;
		var titulo = $('#mepa_grupo_titulo').val();
		var descripcion = $('#mepa_grupo_descripcion').val();
		
		if (grupo_id.length > 0) {
			var dataForm = new FormData($("#form_contrato_firmado")[0]);
			dataForm.append("accion", "guardar_grupo_asignacion");
			dataForm.append("grupo_id", grupo_id);
			dataForm.append("usuario_creador_id", usuario_creador_id);
			dataForm.append("usuario_aprobador_id", usuario_aprobador_id);
			dataForm.append("reportar_gerencia", reportar_gerencia);
			dataForm.append("titulo", titulo);
			dataForm.append("descripcion", descripcion);
			auditoria_send({
			"proceso": "guardar_grupo_asignacion",
			"data": dataForm
			});
		$.ajax({
			url: "sys/set_mepa_asignacion_usuario.php",
			type: 'POST',
			data: dataForm,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(xhr) {
				loading(true);
			},
			success: function(resp) {
				var respuesta = JSON.parse(resp);
				auditoria_send({
					"proceso": "guardar_grupo_asignacion",
					"data": respuesta
				});
				if (parseInt(respuesta.http_code) == 200) {
					swal({
						title: "Registro exitoso",
						text: "El grupo se guardo correctamente",
						html: true,
						type: "success",
						timer: 3000,
						closeOnConfirm: false,
						showCancelButton: false
					});
					setTimeout(function() {
						$('#modalMantemientoGrupo').modal('hide');
						sec_mepa_asignacion_grupo_usuarios_listar_Datatable();
						return false;
					}, 2000);
				} else {
					swal({
						title: "Error al guardar el grupo",
						text: respuesta.error,
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
				}
			},
			complete: function() {
				loading(false);
			}
			});
		}
	
		}
	//------------- Funciones
	function sec_mepa_obtener_usuario_creador() {
			let grupo_id = $("[name='mepa_grupo_id']");
			let select = $("[name='search_id_mepa_usuario_creador']");
			let valorSeleccionado = $("#mepa_grupo_usuario_creador_id").val();
		
			// Primera llamada AJAX para obtener la lista de usuarios
			$.ajax({
				url: "/sys/get_mepa_asignacion_usuario.php",
				type: "POST",
				data: {
					accion: "mepa_obtener_usuarios_asignacion"
				},
				success: function (datos) {
					var respuesta = JSON.parse(datos);
		
					$(select).empty();
		
					if (!valorSeleccionado) {
						let opcionDefault = $("<option value='' selected>Seleccione un usuario</option>");
						$(select).append(opcionDefault);
					}
		
					$(respuesta.result).each(function (i, e) {
						let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
						$(select).append(opcion);
					});
		
					if (valorSeleccionado != null) {
						$(select).val(valorSeleccionado);
					}
				},
				error: function () {
					console.error("Error al obtener la lista de usuarios.");
				}
			});
		}
		
	function sec_mepa_obtener_usuario_aprobador() {
			let select = $("[name='search_id_mepa_usuario_aprobador']");
			let valorSeleccionado = $("#mepa_grupo_usuario_aprobador_id").val();
		
			$.ajax({
				url: "/sys/get_mepa_asignacion_usuario.php",
				type: "POST",
				data: {
					accion: "mepa_obtener_usuarios_asignacion"
				},
				success: function (datos) {
					var respuesta = JSON.parse(datos);
		
					$(select).empty(); 
					if (!valorSeleccionado) {
						let opcionDefault = $("<option value='' selected>Seleccione un usuario</option>");
						$(select).append(opcionDefault);
					}
		
					$(respuesta.result).each(function (i, e) {
						let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
						$(select).append(opcion);
					});
		
					// Seleccionar el valor si existe
					if (valorSeleccionado != null) {
						$(select).val(valorSeleccionado);
					}
				},
				error: function () {
					// Manejo de errores
				}
			});
		}

	function sec_mepa_obtener_usuario_integrante() {
			let select = $("[name='search_id_mepa_usuario_integrante']");
			let valorSeleccionado = $("#mepa_grupo_usuario_integrante_id").val();
			
				$.ajax({
					url: "/sys/get_mepa_asignacion_usuario.php",
					type: "POST",
					data: {
						accion: "mepa_obtener_usuarios_asignacion"
					},
					success: function (datos) {
						var respuesta = JSON.parse(datos);
			
						$(select).empty(); 
						if (!valorSeleccionado) {
							let opcionDefault = $("<option value='' selected>Seleccione un usuario</option>");
							$(select).append(opcionDefault);
						}
			
						$(respuesta.result).each(function (i, e) {
							let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
							$(select).append(opcion);
						});
			
						// Seleccionar el valor si existe
						if (valorSeleccionado != null) {
							$(select).val(valorSeleccionado);
						}
					},
					error: function () {
						// Manejo de errores
					}
				});
		}

// VISTA MODAL "VER INTEGRANTES"   ////////////////////////////////////////////////////////////////////////

	//------------- Botones
	function sec_mepa_guardar_usuario() {

		var grupo_id = $('#mepa_grupo_id').val();
		var usuario_integrante_id = $('#mepa_grupo_usuario_integrante_id').val();

		if (grupo_id.length > 0) {
			var dataForm = new FormData($("#Frm_RegistroUsuario")[0]);
			dataForm.append("accion", "guardar_usuario_integrante");
			dataForm.append("grupo_id", grupo_id);
			dataForm.append("usuario_integrante_id", usuario_integrante_id);
			auditoria_send({
				"proceso": "guardar_usuario_integrante",
				"data": dataForm
			});

		$.ajax({
			url: "sys/set_mepa_asignacion_usuario.php",
			type: 'POST',
			data: dataForm,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(xhr) {
				loading(true);
			},
			success: function(resp) {
				var respuesta = JSON.parse(resp);
				auditoria_send({
					"proceso": "guardar_usuario_integrante",
					"data": respuesta
				});
				if (parseInt(respuesta.http_code) == 200) {
					swal({
						title: "Registro exitoso",
						text: "El usuario se guardo correctamente",
						html: true,
						type: "success",
						timer: 3000,
						closeOnConfirm: false,
						showCancelButton: false
					});
					setTimeout(function() {
						sec_mepa_asignacion_listar_usuarios_Datatable(grupo_id);
						return false;
					}, 3000);
				} else {
					swal({
						title: "Error al guardar el usuario",
						text: respuesta.error,
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
				}
			},
			complete: function() {
				loading(false);
			}
			});
		}
	
		}

	function sec_mepa_eliminar_usuario(id_usuario){
		swal({
			title: 'Esta seguro de eliminar el usuario?',
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: "Si, estoy de acuerdo!",
			cancelButtonText: "No, cancelar",
			closeOnConfirm: true,
			closeOnCancel: true,
		
		},function (isConfirm) {
			if (isConfirm) {
				let data = {
					id : id_usuario,
					accion:'eliminar_usuario_integrante'
					}
		
				$.ajax({
					url: "sys/set_mepa_asignacion_usuario.php",
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
							"proceso": "eliminar_usuario_integrante",
							"data": respuesta
						});
						if (parseInt(respuesta.http_code) == 200) {
							swal({
								title: "Eliminación exitosa",
								text: "El usuario se eliminó correctamente",
								html: true,
								type: "success",
								timer: 3000,
								closeOnConfirm: false,
								showCancelButton: false
							});
							setTimeout(function() {
								sec_mepa_asignacion_listar_usuarios_Datatable(grupo_id);
								return false;
							}, 3000);
						} else {
							swal({
								title: "Error al eliminar el usuario",
								text: respuesta.error,
								html: true,
								type: "warning",
								closeOnConfirm: false,
								showCancelButton: false
							});
						}
					},
					complete: function() {
						loading(false);
					}
					});
					
			
			} 
		});
		}

	//------------- Funciones

	function sec_mepa_asignacion_listar_usuarios_Datatable(id_grupo) {
		if (sec_id == "mepa" && sub_sec_id == "asignacion_usuario") {
		
			var data = {
				accion: "mepa_asignacion_usuarios",
				grupo_id:id_grupo
				};
			$("#mepa_asignacion_usuario_div_tabla").show();
		
			$('#mepa_asignacion_usuario_div_tabla tfoot th').each(function () {
				var title = $(this).text();
				$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
				});
		
			tabla = $("#mepa_asignacion_usuario_datatable").dataTable({
				language: {
						decimal: "",
						emptyTable: "No existen registros",
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
						  },
						  
						},
					scrollY: true,
					scrollX: true,
					dom: 'Bfrtip',
					buttons: [
							'pageLength',
						],
					aProcessing: true,
					aServerSide: true,
					ajax: {
						url: "/sys/get_mepa_asignacion_usuario.php",
						data: data,
						type: "POST",
						dataType: "json",
						error: function(e) {
													},
					},
					createdRow: function(row, data, dataIndex) {
						if (data[0] === 'error') {
							$('td:eq(0)', row).attr('colspan', 7);
							$('td:eq(0)', row).attr('align', 'center');
							$('td:eq(1)', row).css('display', 'center');
							$('td:eq(2)', row).css('display', 'center');
							$('td:eq(3)', row).css('display', 'center');
							$('td:eq(4)', row).css('display', 'center');
							$('td:eq(5)', row).css('display', 'center');
							$('td:eq(6)', row).css('display', 'center');
							this.api().cell($('td:eq(0)', row)).data(data[1]);
						}
					},
					bDestroy: true,
					aLengthMenu: [10, 20, 30, 40, 50, 100],
					initComplete: function () {
						this.api()
						.columns()
						.every(function () {
							var that = this;
		
							$('input', this.footer()).on('keyup change clear', function () {
								if (that.search() !== this.value) {
									that.search(this.value).draw();
								}
							});
						});
					},
				}).DataTable();
			}
		}

// VISTA MODAL "HISTORICO DE INTEGRANTES"   //////////////////////////////////////////////////////////////////

	//------------- Funciones

	function sec_mepa_asignacion_listar_usuarios_creador_Datatable(id_grupo) {
		if (sec_id == "mepa" && sub_sec_id == "asignacion_usuario") {
			
			var data = {
				accion: "mepa_asignacion_usuarios_creador",
				grupo_id:id_grupo
				};
			$("#mepa_asignacion_usuario_creador_div_tabla").show();
			
			$('#mepa_asignacion_usuario_creador_div_tabla tfoot th').each(function () {
				var title = $(this).text();
				$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
				});
			
			tabla = $("#mepa_asignacion_usuario_creador_datatable").dataTable({
						language: {
						decimal: "",
						emptyTable: "No existen registros",
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
							},
							
							},
						scrollY: true,
						scrollX: true,
						dom: 'Bfrtip',
						buttons: [
								'pageLength',
							],
						aProcessing: true,
						aServerSide: true,
						ajax: {
							url: "/sys/get_mepa_asignacion_usuario.php",
							data: data,
							type: "POST",
							dataType: "json",
							error: function(e) {
							},
						},
						createdRow: function(row, data, dataIndex) {
							if (data[0] === 'error') {
								$('td:eq(0)', row).attr('colspan', 7);
								$('td:eq(0)', row).attr('align', 'center');
								$('td:eq(1)', row).css('display', 'center');
								$('td:eq(2)', row).css('display', 'center');
								$('td:eq(3)', row).css('display', 'center');
								$('td:eq(4)', row).css('display', 'center');
								$('td:eq(5)', row).css('display', 'center');
								$('td:eq(6)', row).css('display', 'center');
								$('td:eq(7)', row).css('display', 'center');
								$('td:eq(8)', row).css('display', 'center');
								this.api().cell($('td:eq(0)', row)).data(data[1]);
							}
						},
						bDestroy: true,
						aLengthMenu: [10, 20, 30, 40, 50, 100],
						initComplete: function () {
							// Ocultar la barra de búsqueda
							$('.dataTables_filter').css('display', 'none');
						},
					}).DataTable();
				}
		}
	function sec_mepa_asignacion_listar_usuarios_aprobador_Datatable(id_grupo) {
		if (sec_id == "mepa" && sub_sec_id == "asignacion_usuario") {
				
			var data = {
				accion: "mepa_asignacion_usuarios_aprobador",
				grupo_id:id_grupo
				};
			$("#mepa_asignacion_usuario_aprobador_div_tabla").show();
				
			$('#mepa_asignacion_usuario_aprobador_div_tabla tfoot th').each(function () {
				var title = $(this).text();
				$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
				});
				
			tabla = $("#mepa_asignacion_usuario_aprobador_datatable").dataTable({
						language: {
						decimal: "",
						emptyTable: "No existen registros",
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
								},
								
								},
							scrollY: true,
							scrollX: true,
							dom: 'Bfrtip',
							buttons: [
									'pageLength',
								],
							aProcessing: true,
							aServerSide: true,
							ajax: {
								url: "/sys/get_mepa_asignacion_usuario.php",
								data: data,
								type: "POST",
								dataType: "json",
								error: function(e) {
								},
							},
							createdRow: function(row, data, dataIndex) {
								if (data[0] === 'error') {
									$('td:eq(0)', row).attr('colspan', 7);
									$('td:eq(0)', row).attr('align', 'center');
									$('td:eq(1)', row).css('display', 'center');
									$('td:eq(2)', row).css('display', 'center');
									$('td:eq(3)', row).css('display', 'center');
									$('td:eq(4)', row).css('display', 'center');
									$('td:eq(5)', row).css('display', 'center');
									$('td:eq(6)', row).css('display', 'center');
									$('td:eq(7)', row).css('display', 'center');
									$('td:eq(8)', row).css('display', 'center');
									this.api().cell($('td:eq(0)', row)).data(data[1]);
								}
							},
							bDestroy: true,
							aLengthMenu: [10, 20, 30, 40, 50, 100],
							initComplete: function () {
								// Ocultar la barra de búsqueda
								$('.dataTables_filter').css('display', 'none');
							},
						}).DataTable();
					}
		}

	function sec_mepa_asignacion_listar_usuarios_integrante_Datatable(id_grupo) {
			if (sec_id == "mepa" && sub_sec_id == "asignacion_usuario") {
					
				var data = {
					accion: "mepa_asignacion_usuarios_integrante",
					grupo_id:id_grupo
					};
				$("#mepa_asignacion_usuario_integrante_div_tabla").show();
					
				$('#mepa_asignacion_usuario_integrante_div_tabla tfoot th').each(function () {
					var title = $(this).text();
					$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
					});
					
				tabla = $("#mepa_asignacion_usuario_integrante_datatable").dataTable({
							language: {
							decimal: "",
							emptyTable: "No existen registros",
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
									},
									
									},
								scrollY: true,
								scrollX: true,
								dom: 'Bfrtip',
								buttons: [
										'pageLength',
									],
								aProcessing: true,
								aServerSide: true,
								ajax: {
									url: "/sys/get_mepa_asignacion_usuario.php",
									data: data,
									type: "POST",
									dataType: "json",
									error: function(e) {
									},
								},
								createdRow: function(row, data, dataIndex) {
									if (data[0] === 'error') {
										$('td:eq(0)', row).attr('colspan', 7);
										$('td:eq(0)', row).attr('align', 'center');
										$('td:eq(1)', row).css('display', 'center');
										$('td:eq(2)', row).css('display', 'center');
										$('td:eq(3)', row).css('display', 'center');
										$('td:eq(4)', row).css('display', 'center');
										$('td:eq(5)', row).css('display', 'center');
										$('td:eq(6)', row).css('display', 'center');
										$('td:eq(7)', row).css('display', 'center');
										$('td:eq(8)', row).css('display', 'center');
										this.api().cell($('td:eq(0)', row)).data(data[1]);
									}
								},
								bDestroy: true,
								aLengthMenu: [10, 20, 30, 40, 50, 100],
								initComplete: function () {
									// Ocultar la barra de búsqueda
									$('.dataTables_filter').css('display', 'none');
								},
							}).DataTable();
						}
		}