sec_usuarios_permisos_botones_x_menu_sub_menus_expand_collapse_rows();
sec_usuarios_permisos_asignar_botones_x_menu_sub_menu_botones();
sec_usuarios_permisos_locales_x_redes_expand_collapse_rows();

sec_manual_events();

function sec_usuarios_permisos_botones_x_menu_sub_menus_expand_collapse_rows() {
	$(".parent_tbl_sub_menu_botones_padres").off().on("click", function () {
		var id_row_children = $(this).data("id");
		if ($(".tbl_menu_sub_menu_botones_padres_detalles_" + id_row_children).hasClass("rows_hidden_usuarios_permisos")) {
			$(".tbl_menu_sub_menu_botones_padres_detalles_" + id_row_children).toggle().removeClass('rows_hidden_usuarios_permisos').addClass('rows_expanded_usuarios_permisos');
			$(this).find("span").removeClass("glyphicon-plus").addClass("glyphicon-minus");
			$(".tbl_menu_sub_menu_botones_detalles").hide();
		} else {
			$(".tbl_menu_sub_menu_botones_padres_detalles_" + id_row_children).toggle().removeClass('rows_expanded_usuarios_permisos').addClass('rows_hidden_usuarios_permisos');
			$(this).find("span").removeClass("glyphicon-minus").addClass("glyphicon-plus");
		}
	});
}

function sec_usuarios_permisos_asignar_botones_x_menu_sub_menu_botones() {
	$(".parent_tbl_sub_menu_botones").off().on("click", function () {
		var id_row_children = $(this).data("id");
		if ($(".tbl_menu_sub_menu_botones_detalles_" + id_row_children).hasClass("rows_hidden_usuarios_permisos")) {
			$(".tbl_menu_sub_menu_botones_detalles_" + id_row_children).toggle().removeClass('rows_hidden_usuarios_permisos').addClass('rows_expanded_usuarios_permisos');
			$(this).find("span").removeClass("glyphicon-plus").addClass("glyphicon-minus");
		} else {
			$(".tbl_menu_sub_menu_botones_detalles_" + id_row_children).toggle().removeClass('rows_expanded_usuarios_permisos').addClass('rows_hidden_usuarios_permisos');
			$(this).find("span").removeClass("glyphicon-minus").addClass("glyphicon-plus");
		}
	});
}

function sec_usuarios_permisos_locales_x_redes_expand_collapse_rows() {
	$(".all_parent_usuarios_permisos").off().on("click", function () {
		if ($(".children_usuarios_permisos").hasClass("rows_expanded_usuarios_permisos")) {
			$(".children_usuarios_permisos").hide();
			$(this).find("span").removeClass("glyphicon-minus").addClass("glyphicon-plus");
			$(".children_usuarios_permisos").removeClass('rows_expanded_usuarios_permisos').addClass('rows_hidden_usuarios_permisos');
			$(".parent_usuarios_permisos").find("span").removeClass("glyphicon-minus").addClass("glyphicon-plus");
		} else {
			$(".children_usuarios_permisos").show();
			$(".children_usuarios_permisos").removeClass('rows_hidden_usuarios_permisos').addClass('rows_expanded_usuarios_permisos');
			$(this).find("span").removeClass("glyphicon-plus").addClass("glyphicon-minus");
			$(".parent_usuarios_permisos").find("span").removeClass("glyphicon-plus").addClass("glyphicon-minus");
		}
	});

	$(".parent_usuarios_permisos").off().on("click", function () {
		var id_row_children = $(this).data("red");
		if ($(".children_row_collapse_expand_" + id_row_children).hasClass("rows_hidden_usuarios_permisos")) {
			$(".children_row_collapse_expand_" + id_row_children).toggle().removeClass('rows_hidden_usuarios_permisos').addClass('rows_expanded_usuarios_permisos');
			$(this).find("span").removeClass("glyphicon-plus").addClass("glyphicon-minus");
			$(".nombre_red_usuarios_permisos").removeClass('estilos_nombre_red_expand').addClass('estilos_nombre_red_collapse');
		} else {
			$(".children_row_collapse_expand_" + id_row_children).toggle().removeClass('rows_expanded_usuarios_permisos').addClass('rows_hidden_usuarios_permisos');
			$(this).find("span").removeClass("glyphicon-minus").addClass("glyphicon-plus");
			$(".nombre_red_usuarios_permisos").removeClass('estilos_nombre_red_collapse').addClass('estilos_nombre_red_expand');
		}
	});
}
var uploader = ""
$(document).ready(function () {

	loading(true)
	setTimeout(function () {
		var upload_btn = $(".upload-manual-btn");

		loading(false)
		$.each(upload_btn, function (key, value) {
			var data = JSON.parse(JSON.stringify(value.dataset));
			var senddata = Object.assign({ "accion": "cargar_manual" }, data)
			// console.log(senddata)

			var uploader = new ss.SimpleUpload({
				button: value,
				name: 'file',
				autoSubmit: true,
				data: senddata,
				debug: true,
				url: '/sys/get_manual_usuarios.php',
				onChange: function (filename, extension, uploadBtn, fileSize, file) {
					if (extension.toLowerCase() !== 'pdf') {
						// Mostrar aviso si el archivo no es un PDF
						swal({
							title: "Advertencia",
							text: "Por favor, selecciona un archivo PDF.",
							icon: "warning",
							type: 'warning',
							showCancelButton: true,
							closeOnConfirm: true
						});
						return false;
					}
	
					$('#progress').html("");
					$('#progressBar').width(0);
					$("#filename").html(filename + " " + (file.size) + "Kb");
				},
				onSubmit: function (filename, extension, uploadBtn, size) {
					loading(true);
				},
				onComplete: function (filename, response, uploadBtn, size) {
					//debugger
					//console.log(response);
					try {
						var result = JSON.parse(response)
					} catch (e) {
						loading();
						swal("¡Error!", "Error", "warning");
						return false;
					}

					if (result.estado != undefined) {
						loading();
						swal({
							title: "¡Éxito!",
							text: result.msg,
							type: result.estado,
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
						//console.log(JSON.parse(response))
						var error = JSON.parse(response)
						var msg = "No se pudo Importar el Archivo"
						if (error.error == true && error.msg != undefined) {
							msg = error.msg
						}
						swal("¡Error!", msg, "warning");
						return false;
					}
				},
				onProgress: function (progress) {
					$('#progress').html("Progreso: " + Math.round(progress) + "%");
					$('#progressBar').width(progress + "%");
				}
			});
		});
	}, 1000);

	$('.btn-download-manual').click(function () {
		console.log(this.dataset)
		var tipoManual = this.dataset.tipoManual
		var menuId = this.dataset.menuId
		var senddata = {
			"accion": "get_path_manuales",
			"tipoManual": tipoManual,
			"menuId": menuId,
		}

		loading(true);
		$.ajax({
			url: '/sys/get_manual_usuarios.php',
			type: 'post',
			data: senddata,
		}).done(function (dataresponse) {

			// console.log(dataresponse);
			var obj = JSON.parse(dataresponse);
			console.log(obj[tipoManual])
			if (obj[tipoManual] != 0) {
				// var link = document.createElement('a');
				// link.href = filePath;
				// link.download = filePath.substr(filePath.lastIndexOf('/') + 1);
				// link.click();
				window.download(obj[tipoManual])
				// loading();
			} else {

			}
		}).always(function (data) {
			loading();
		});
	})

	$('.btn-delete-manual').click(function () {
		var tipoManual = this.dataset.tipoManual
		var menuId = this.dataset.menuId
		var senddata = {
			"accion": "delete_manual",
			"tipoManual": tipoManual,
			"menuId": menuId,
		}

		swal({
			title: "Advertencia",
			text: "Si lo borra. Ya no tendrá acceso al manual.",
			type: 'warning',
			showCancelButton: true,
			closeOnConfirm: true
		},
			function () {

				$.ajax({
					url: '/sys/get_manual_usuarios.php',
					type: 'post',
					data: senddata,
				}).done(function (dataresponse) {
					//debugger
					//console.log(dataresponse);
					var obj = JSON.parse(dataresponse);
					if (obj.estado != undefined) {
						swal("¡Éxito!", obj.msg, "success");
						m_reload();

					} else {
					}

				}).always(function (data) {
					loading();
				});
			});
	})

	$('.btn-historico-cambios-manual').click(function () {

		var tipoManual = this.dataset.tipoManual
		var nombreManual = (tipoManual === '1') ? 'Manual de Usuario' : 'Guía de Acceso';
		var menuId = this.dataset.menuId
		var menu = this.dataset.menuTitulo
		
		$('#modalManualUsuarioHistoricoCambios').modal('show');
		$('#modal_title_manual_usuario_historico_cambios').html(('HISTORIAL DE CAMBIOS - '+ menu + ' - ' + nombreManual).toUpperCase());
		sec_mantenimiento_num_cuenta_listar_subdiarios_Datatable(tipoManual,menuId);
	
	})

	function sec_mantenimiento_num_cuenta_listar_subdiarios_Datatable(tipoManual, menuId) {
		if (sec_id == "manual_usuarios") {
	
			var data = {
				accion: "get_historico_cambios",
				tipoManual: tipoManual,
				menuId: menuId
			};
			$("#mantenimiento_num_cuenta_subdiario_div_tabla").show();
	
			tabla = $("#mmantenimiento_num_cuenta_subdiario_datatable").dataTable({
				language: {
					decimal: "",
					emptyTable: "No existen registros",
					info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
					infoEmpty: "Mostrando 0 a 0 de 0 entradas",
					infoFiltered: "",
					infoPostFix: "",
					thousands: ",",
					lengthMenu: "Mostrar _MENU_ entradas",
					loadingRecords: "Cargando...",
					processing: "Procesando...",
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
					url: "/sys/get_manual_usuarios.php",
					data: data,
					type: "POST",
					dataType: "json",
					error: function (e) {},
				},
				columnDefs: [{
					className: 'text-center',
					targets: [0, 1, 2, 3, 4]
				}],
				createdRow: function (row, data, dataIndex) {
					if (data[0] === 'error') {
						$('td:eq(0)', row).attr('colspan', 5);
						$('td:eq(0)', row).attr('align', 'center');
						$('td:eq(1)', row).css('text-align', 'center');
						$('td:eq(2)', row).css('text-align', 'center');
						$('td:eq(3)', row).css('text-align', 'center');
						$('td:eq(4)', row).css('text-align', 'center');
						$('td:eq(5)', row).css('text-align', 'center');
						this.api().cell($('td:eq(0)', row)).data(data[1]);
					}
				},
				bDestroy: true,
				aLengthMenu: [10, 20, 30, 40, 50, 100],
			}).DataTable();
	
			// Eliminar el campo de búsqueda
			tabla.on('init.dt', function () {
				$('.dataTables_filter').hide();
			});
		}
	}
	

})

function download(filePath){
    var link=document.createElement('a');
    link.href = filePath;
    link.download = filePath.substr(filePath.lastIndexOf('/') + 1);
    link.click();
}

function sec_manual_events(){
	sec_manual_usuarios_filtrar_tabla();
}

function sec_manual_usuarios_filtrar_tabla(){
	$("#filter_tbl_manuales_busqueda").off().on("keyup",function(){
		var term=$(this).val()
		if( term != ""){
			$("#tbl_menu_sub_menu_botones tbody>tr").hide();
			$("#tbl_menu_sub_menu_botones td").filter(function(){
				return $(this).text().toLowerCase().indexOf(term) >-1
			}).parent("tr").show();
		}else{
			$("#tbl_menu_sub_menu_botones tbody>tr").show();
		}
	});
}


