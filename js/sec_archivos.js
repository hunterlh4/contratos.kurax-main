function sec_archivos(){
	if(sec_id=="archivos" && sub_sec_id == ""){
		sec_archivos_events();
	}
}

function sec_archivos_events(){
	console.log('sec_archivos');
	if($('#tblArchivos').length){
		filter_archivos_table(0);
		
		setArchivoUploader($('#fileArchivosModal'));

		$(".select2").select2({
			closeOnSelect: true,
			width:"100%"
		});

		// $("#txtArchivosFilter").on("keyup", function() {
		// 	var value = $(this).val().toLowerCase();
		// 	$("#tblArchivos tbody tr").filter(function() {
		// 		$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		// 	});
		// });

		$('#btnArchivosSearch').on('click', function(event) {
			event.preventDefault();
			loading(true);
			if ($("#cbArchivosCategoria").val()==10){
				$("#digital_directory").show();
				$("#categoria_name").html("Directorio");
			} else {
				$("#digital_directory").hide();
				$("#cbDigitalDirectory").val("all");
				$("#categoria_name").html("Categoría");
			}
			filter_archivos_table(0);
		});

		$('#btnArchivoUpload').on('click', function(event) {
			event.preventDefault();
			$('#mdArchivos').modal("show");
		});

		$('#btnCrearCategoria').on('click', function(event) {
			event.preventDefault();
			$('#modal_crear_categoria').modal("show");
		});

		$('#btn_cerrar_modal_editar').on('click', function(event) {
			event.preventDefault();
			
			// $('#modal_editar_categoria').modal("show");
			$('#modal_crear_categoria').modal("show");
		});

		$("#btn_new_directory").on('click', function(event) {
			event.preventDefault();
			$("#Archivos_nombreCarpeta").val("");
			$('#modal_crear_carpeta').modal("show");
			$('#mdArchivos').modal("hide");
		});

		$("#btn_delete_directory").on('click', function(event) {
			event.preventDefault();
			elimina_carpeta_digital();
		});

		$('#modal_crear_carpeta').on('hidden.bs.modal', function (e) {
			$('#mdArchivos').modal("show");
		});

		$(document).on('change','.chk_file', function(){
			$('.chk_file').not(this).prop('checked', false);
		});

		$(document).on('change', '#cbArchivosModalCategoria', function(event) {
			var cat_selected = $("#cbArchivosModalCategoria").val();
			if (cat_selected == 10) {
				carga_carpetas();
				$("#form_SubirArchivoDigital").show();
			} else {
				$("#form_SubirArchivoDigital").hide();
			}
		});

		$(document).on('change', '#cbArchivosModalCategoria', function(event) {
			var cat_selected = $("#cbArchivosModalCategoria").val();
			if (cat_selected == 10) {
				carga_carpetas();
				$("#form_SubirArchivoDigital").show();
			} else {
				$("#form_SubirArchivoDigital").hide();
			}
		});

		$(document).on('change', '#cbDigitalDirectory', function(event) {
			filter_archivos_table(0);
		});
		

		$("#btn_Archivos_editar_categoria").on('click', function(event){
			event.preventDefault();
			var set_data = {};
			var idCategoriaEdit = $("#archivos_id_categoria_edit").val();
			var nombreCategoriaEdit = $("#archivos_nombre_categoria_edit").val();
			var estadoCategoriaEdit = $("#cbArchivosModalCategoriaEstado").val();
			
			set_data.id_categoria_edit = idCategoriaEdit;
			set_data.nombre_categoria_edit = nombreCategoriaEdit;
			set_data.estado_categoria_edit = estadoCategoriaEdit;
			console.log(set_data);
			if (nombreCategoriaEdit == "") {
				swal({
					type: "warning",
					title: "Atención",
					text: "No se ha escrito un nombre de categoría",
				});
			} else {

				$.ajax({
					url: "/sys/set_archivos_digital.php?action=editar_categoria",
					type: "POST",
					data: set_data,
					success: function(response) {
						result = JSON.parse(response);
						console.log(result);
						if(result.error){
							swal({
								type: result.error_type,
								title: result.error,
								text: result.error_msg,
							});
						} else {
							swal({
								type: "success",
								title: "Finalizado",
								text: "La categoría se ha editado correctamente",
							}, function(){
								swal.close();
								location.reload();
							}); 
							$('#modal_crear_categoria').modal("hide");
						}
					}
				});
			}
		});
		$("#btn_crear_categoria").on('click', function(event){
			event.preventDefault();
			var set_data = {};
			var nombreCategoria = $("#nombre_categoria").val();
			
			console.log(nombreCategoria);
			set_data.nombre_categoria = nombreCategoria;
			if (nombreCategoria == "") {
				swal({
					type: "warning",
					title: "Atención",
					text: "No se ha escrito un nombre de categoría",
				});
			} else {

				$.ajax({
					url: "/sys/set_archivos_digital.php?action=crea_categoria",
					type: "POST",
					data: set_data,
					success: function(response) {
						result = JSON.parse(response);
						console.log(result);
						if(result.error){
							swal({
								type: result.error_type,
								title: result.error,
								text: result.error_msg,
							});
						} else {
							swal({
								type: "success",
								title: "Finalizado",
								text: "La categoría se ha creado correctamente",
							}, function(){
								swal.close();
								location.reload();
							}); 
							$('#modal_crear_categoria').modal("hide");
						}
					}
				});
			}
		});
		$("#btn_Archivos_creaCarpeta").on('click', function(event){
			event.preventDefault();
			var set_data = {};
			var nombreCarpeta = $("#Archivos_nombreCarpeta").val();
			if (archivo_ruta_carperta_seleccionada=="" || archivo_ruta_carperta_seleccionada==null || archivo_ruta_carperta_seleccionada==undefined) {
				nombreCarpeta = nombreCarpeta;
			}
			else {
				nombreCarpeta = archivo_ruta_carperta_seleccionada + "/" + nombreCarpeta;
			}
			console.log(nombreCarpeta);
			set_data.nombre_carpeta = nombreCarpeta;
			if (nombreCarpeta == "") {
				swal({
					type: "warning",
					title: "Atención",
					text: "No se ha escrito un nombre de carpeta",
				});
			} else {
				$.ajax({
					url: "/sys/set_archivos_digital.php?action=crea_carpeta",
					type: "POST",
					data: set_data,
					success: function(response) {
						result = JSON.parse(response);
						console.log(result);
						if(result.error){
							swal({
								type: result.error_type,
								title: result.error,
								text: result.error_msg,
							});
						} else {
							swal({
								type: "success",
								title: "Finalizado",
								text: "La carpeta se ha creado correctamente",
							});
							archivo_ruta_carperta_seleccionada = "";
							carga_carpetas();
							$('#modal_crear_carpeta').modal("hide");
							$('#mdArchivos').modal("show");
						}
					}
				});
			}
		});

		$(document).on('submit', "#formArchivosModal", function(e) {
			e.preventDefault();
			var form_data = (new FormData(this));
			form_data.append("post_archivos", 1);
			if ($("#cbArchivosModalCategoria").val() == 10) {
				var cont = 0;
				var chk_file_digital = '';
				$('.chk_file:checkbox:checked').each(function(){
					cont++;
					chk_file_digital = $('.chk_file:checkbox:checked').data("name");
				});
				if($("#fileArchivosModal").val() == "") {
					swal({
						type: "warning",
						title: "Atención",
						text: "No se ha seleccionado ningún archivo para subir",
					});
					return;
				} else if (cont == 0 || cont >= 2) {
					swal({
						type: "warning",
						title: "Atención",
						text: "Asegúrese de marcar el check de sólo una carpeta",
					});
					return;
				} else {
					form_data.append("categoria", 10);
					form_data.append("chk_digital", chk_file_digital);
				}
			}
			loading(true);

			$.ajax({
				url: "/sys/get_archivos.php",
				type: "POST",
				data: form_data,
				cache: false,
				contentType: false,
				processData:false,
				success: function(response) {
					result = JSON.parse(response);
					loading();
					if(result.status){
						resetArchivosModal();
						const src_files = result.arr_src;
						let htmlStr = "";
						let linksStr = "";
						let htmlFileList = "";
						src_files.forEach((file) => {
							htmlFileList += `	<li class="list-group-item d-flex justify-content-between align-items-center">
													<small>${file.name}</small>
													<span class="badge badge-primary badge-pill" onclick="archivos_copiar_link('${file.src}')" style="cursor: pointer">Copiar Link</span>
												</li>`;
							linksStr += `${file.src}*`;
						});
						htmlStr = `
							<div class="alert alert-success" role="alert">
								<h4 class="alert-heading">Archivo(s) Agregado(s)</h4>
								<p>Se han agregado los siguientes archivos:</p>
							</div>
							<ul class="list-group">
								${htmlFileList}
								<li class="list-group-item d-flex justify-content-between align-items-center border-0">
									<span class="badge badge-warning badge-pill" onclick="archivos_copiar_links('${linksStr}')" style="cursor: pointer">Todos los links</span>
								</li>
							</ul>
						`;
						$("#modal_respuesta_subir_archivo_body").html(htmlStr);
						$("#modal_respuesta_subir_archivo").modal("show");
						$("#modal_respuesta_subir_archivo").on("hidden.bs.modal", function(){
							$("#modal_respuesta_subir_archivo_body").html("");
						});
						//swal("Archivo(s) Agregado(s)", "", "success");
					}
					else{
						swal({
							type: "warning",
							title: "Atención",
							text: result.message,
							html: true,
						});
					}
					filter_archivos_table(0);
				},
				always: function(data){
					loading();
					console.log(data);
				}
			});
		});

		$(document).on('click', '#btnArchivosDelete', function(event) {
			event.preventDefault();
			var id = $(this).data("id");
			swal({
				title: "¡Alerta!",
				text: "Estás seguro que deseas borrar este archivo?",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Si",
				cancelButtonText:"No",
				closeOnConfirm: true,
				closeOnCancel: true
			},
			function(opt){
				if(opt){
					loading(true);
					var get_data 	= {};
					get_data.id 	= id;

					auditoria_send({"proceso":"sec_archivos_drive_delete","data":get_data});
					$.post('/sys/get_archivos.php', {"delete_archivos": get_data}, function(response) {
						filter_archivos_table(0);
					});
				}
			}); 
		});
		
		$(document).on('click', '.btnCopyLink', function(event) {
			event.preventDefault();
			var url = $(this).data("url");
			
			var sampleTextarea = document.createElement("textarea");
			document.body.appendChild(sampleTextarea);
			sampleTextarea.value = url;
			sampleTextarea.select();
			document.execCommand("copy");
			document.body.removeChild(sampleTextarea);

			swal({
				title: "Listo",
				text: "El enlace se ha copiado al portapapeles",
				type: "success"
			}); 
		});
	}
}
function editarCategoria(categoria_id, categoria_nombre, categoria_estado){

	$("#archivos_id_categoria_edit").val(categoria_id);
	$("#archivos_nombre_categoria_edit").val(categoria_nombre);
	$("#cbArchivosModalCategoriaEstado").val(categoria_estado);
			
	$("#modal_editar_categoria").modal('show');
	$("#modal_crear_categoria").modal('hide');
}

function removeCategoria(categoria_id){
	var set_data = {};
	
	console.log(categoria_id);
	set_data.categoria_id = categoria_id;
	if (categoria_id == "") {
		swal({
			type: "warning",
			title: "Atención",
			text: "No se hay categoria",
		});
	} else {

		$.ajax({
			url: "/sys/set_archivos_digital.php?action=remove_categoria",
			type: "POST",
			data: set_data,
			success: function(response) {
				result = JSON.parse(response);
				console.log(result);
				if(result.error){
					swal({
						type: result.error_type,
						title: result.error,
						text: result.error_msg,
					});
				} else {
					swal({
						type: "success",
						title: "Finalizado",
						text: "La categoría se ha eliminado correctamente",
					});
					
					$('#cbArchivosModalCategoria>option[value="'+categoria_id+'"]').hide();
					$('#list_categoria_'+categoria_id).hide();
					// $('#modal_crear_categoria').modal("hide");
				}
			}
		});
	}
}

function archivos_copiar_link(url) {
	let inputHideToCopy = `<input type="text" id="inputHideToCopy" value="${url}" style="position: absolute; left: -1000px; top: -1000px;" hidden>`;
	$("body").append(inputHideToCopy);
	let copyText = document.getElementById("inputHideToCopy");
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	navigator.clipboard.writeText(copyText.value);
	$("#inputHideToCopy").remove();
	swal({
		title: "Listo",
		text: "El enlace se ha copiado al portapapeles",
		type: "success"
	});
}

function archivos_copiar_links(url) {
	let links = url.split("*");
	let str_links = "";
	links.forEach((link) => {
		str_links += link + "\n";
	});
	let textareaHideToCopy = `<textarea id="textareaHideToCopy" style="position: absolute; left: -1000px; top: -1000px;" hidden>${str_links}</textarea>`;
	$("body").append(textareaHideToCopy);
	let copyText = document.getElementById("textareaHideToCopy");
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	navigator.clipboard.writeText(copyText.value);
	$("#textareaHideToCopy").remove();
	swal({
		title: "Listo",
		text: "El enlace se ha copiado al portapapeles",
		type: "success"
	});
}

function filter_archivos_table(page) {
	var get_data 	= {};
	var limit 		= $("#cbArchivosLimit option:selected").val();
	get_data.page 	= page;
	get_data.limit 	= limit;
	get_data.category = $("#cbArchivosCategoria option:selected").val();
	get_data.filter = $("#txtArchivosFilter").val();
	get_data.filter_digital = $("#cbDigitalDirectory option:selected").val();

	$.post('/sys/get_archivos.php', {"get_archivos": get_data}, function(response) {
		try{
			result = JSON.parse(response);	
			$("#tblArchivos > tbody").html(result.body);

			$("#paginationArchivos").pagination({
				items: result.num_rows,
				currentPage: page+1,
				itemsOnPage: limit,
				cssStyle: 'light-theme',
				onPageClick: function(pageNumber, event){
					event.preventDefault();
					loading(true);
					filter_archivos_table(pageNumber-1);
				}
			});
		}
		catch(error){
			console.log(error);
		}
		loading();
	});
}

function setArchivoUploader(object){
	$(document).on('click', '.browse-btn', function(event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {
		let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
		if($(this)[0].files.length <= 1){
			const name = $(this).val().split(/\\|\//).pop();
			truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
		}

		$(".file-info").html(truncated);
	});
}

function resetArchivosModal(){
	$('#cbArchivosModalCategoria').prop('selectedIndex', 0);
	$('.file-info').html("Ningun Archivo Seleccionado...");
	$('#file_reset').click();
	$('#mdArchivos').modal('hide');
	$("#form_SubirArchivoDigital").hide();
}

function carga_carpetas() {
	loading(true);
	var set_data = {};
	$.ajax({
		url: "/sys/set_archivos_digital.php?action=carga_carpetas",
		type: "POST",
		data: set_data,
		success: function(response) {
			//$("#tbl_carpetas_digital>tbody").html(response);
			const carpetas = JSON.parse(response);
			$("#list_carpetas_digital").html("");
			const carpetasHtml = formart_carpetas(carpetas);
			$("#list_carpetas_digital").html(carpetasHtml);
			loading(false);
		}
	});
}
let archivo_ruta_carperta_seleccionada = "";
function formart_carpetas(carpetas, hijo = false, padre = "", nivel = 0) {
	console.log(carpetas);
	let html = "";
	let ruta = "";
	html = `<ul class="list-group" style="display: ${hijo ? 'none' : 'block'}" ${hijo ? 'id="archivo_ul_' + padre + '"' : ''}>`;
	carpetas.forEach((carpeta) => {
		ruta = carpeta.ruta.substring(1);
		let arr_ruta = ruta.split("/");
		arr_ruta.splice(0, 5);
		ruta = arr_ruta.join("/");
		///console.log(ruta);
		html += `
			<li class="list-group-item" id="carpeta_li_${carpeta.nombre}" style="padding: ${hijo ? '0px 0px 0px '+15*nivel+'px' : '3px'}">
				<input type="checkbox" class="chk_file" data-name="${ruta}" value="${ruta}" onclick="click_checked_carpeta('${ruta}')">
				${carpeta.nombre} <span class="text-info"> (Carpetas[${carpeta.subdirectorios.length}])</span> 
				<span type="button" class="badge badge-success badge-pill" onclick="ver_subcarpetas('${carpeta.nombre}')" style="cursor: pointer">Carpetas</span>
			</li>
			${formart_carpetas(carpeta.subdirectorios, true, carpeta.nombre, nivel+1)}
		`;
	});
	html += "</ul>";
	return html;
}

function click_checked_carpeta(ruta){
	archivo_ruta_carperta_seleccionada == ruta ? archivo_ruta_carperta_seleccionada = "" : archivo_ruta_carperta_seleccionada = ruta;
	//console.log(archivo_ruta_carperta_seleccionada);
}

function ver_subcarpetas (carpeta_padre) {
	$("#archivo_ul_" + carpeta_padre).toggle();
}

function elimina_carpeta_digital(){
	var set_data = {};
	var cont = 0;
	var chk_file_digital = '';
	$('.chk_file:checkbox:checked').each(function(){
		cont++;
		chk_file_digital = $('.chk_file:checkbox:checked').data("name");
	});
	if (cont == 0 || cont >= 2) {
		swal({
			type: "warning",
			title: "Atención",
			text: "Asegúrese de marcar el check de sólo una carpeta",
		});
	} else {
		swal({
			title: "Atención",
			text: "¿Está seguro(a) de borrar esta carpeta? Se eliminarán también los archivos guardados dentro",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Si",
			cancelButtonText:"No",
			closeOnConfirm: true
		},
		function(){
			loading(true);
			set_data.nombre_carpeta = chk_file_digital;
            $.ajax({
                url: "/sys/set_archivos_digital.php?action=elimina_carpeta",
				type: "POST",
				data: set_data,
            })
            .done(function() {
				swal({
					title: "Listo",
					text: "La carpeta y su contenido han sido eliminados correctamente",
					type: "success",
					closeOnConfirm: true
				});
            })
            .fail(function(error){
                console.log(error);
            });
			loading(false);
			carga_carpetas();
			auditoria_send({"proceso":"sec_archivos_drive_elimina_carpeta_digital","data":set_data});
		});
	}
}
