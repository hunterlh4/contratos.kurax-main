// INICIO FUNCION DE INICIALIZACION
function sec_mepa_mantenimiento_correo()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_mantenimiento_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	mepa_mantenimiento_correo_tipo_correo_listar_tipo_proceso();

	$("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario").select2({

        width: "100%",
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Por favor ingrese 1 o más caracteres';
            }
        },
        matcher: function(params, data){
            // Si no hay término de búsqueda, muestra todas las opciones
            if($.trim(params.term) === "")
            {
                return data;
            }

            // Comprueba si el término de búsqueda está incluido en el texto de la opción
            if(data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1)
            {
                return data;
            }

            return null;
        }    
    });
}
// FIN FUNCION DE INICIALIZACION

function mepa_mantenimiento_correo_buscar_correo_grupo()
{
	
	if(sec_id == "mepa" && sub_sec_id == "mantenimiento")
	{
		var param_tipo_correo = $("#sec_mepa_mantenimiento_correo_param_tipo_correo").val();

		var data = {
			"accion": "mepa_mantenimiento_correo_buscar_correo_grupo",
			"param_tipo_correo" : param_tipo_correo
		}

		$("#mepa_mantenimiento_correo_grupo_div_tabla").show();

		$.ajax({
		    url: "/sys/set_mepa_mantenimiento_correo.php",
		    data: data,
		    type: "POST",
		    dataType: "json",
		    beforeSend: function() {
		        loading("true");
		    },
		    success: function(response)
		    {
		    	if(response.aaData)
		        {
		        	if ($.fn.DataTable.isDataTable('#mepa_mantenimiento_correo_grupo_datatable'))
		        	{
						$('#mepa_mantenimiento_correo_grupo_datatable').DataTable().destroy();
		            }

		            var dataTable = $('#mepa_mantenimiento_correo_grupo_datatable').DataTable({
		                data: response.aaData,
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
						columnDefs: [
		                    {
		                        className: 'text-center',
		                        targets: [0, 1, 2, 3, 4, 5, 6, 7]
		                    }
		                ],
						aLengthMenu:[10, 20, 30, 40, 50, 100],
						"order" : 
						[
							1, "desc"
						]
		            });
		        }
		        else
		        {
		            console.error('Error al obtener los datos.');
		        }
		    },
		    error: function(e) {
		        console.log(e.responseText);
		    },
		    complete: function() {
		        loading();
		    }
		});
	}
}

function mepa_mantenimiento_correo_grupo_correo_detalle(param_grupo_id, param_metodo)
{
	$("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario").val(0).trigger("change.select2");
	$("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_grupo_id").val(param_grupo_id);
	$("#sec_mepa_mantenimiento_correo_modal_grupo_detalle_titulo").text("Detalle: " + param_metodo);
	$("#sec_mepa_mantenimiento_correo_modal_grupo_detalle").modal("show");

	mepa_mantenimiento_correo_modal_grupo_detalle_listar_usuario(param_grupo_id);

}

function mepa_mantenimiento_correo_modal_grupo_detalle_listar_usuario(param_grupo_id)
{
	if(sec_id == "mepa" && sub_sec_id == "mantenimiento")
	{
		var data = {
			"accion": "mepa_mantenimiento_correo_modal_grupo_detalle_listar_usuario",
			"param_grupo_id" : param_grupo_id
		}

		$("#mepa_mantenimiento_correo_modal_grupo_detalle_div_tabla").show();

		tabla = $("#mepa_mantenimiento_correo_modal_grupo_detalle_datatable").dataTable(
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
					url : "/sys/set_mepa_mantenimiento_correo.php",
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
                        targets: [0, 1, 2, 3, 4, 5]
                    }
                ],
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100],
				"order" : 
				[
					1, "desc"	
				]
			}
		).DataTable();
	}
}

function mepa_mantenimiento_correo_grupo_correo_cargar_datos_a_editar(param_grupo_id)
{
	
	swal(
	{
		title: '¿Está seguro de editar?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true,
		html: true,
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{

			var data = {
				"accion": "mepa_mantenimiento_correo_grupo_correo_cargar_datos_a_editar",
				"param_grupo_id": param_grupo_id
			}

			auditoria_send({ "proceso": "mepa_mantenimiento_correo_grupo_correo_cargar_datos_a_editar", "data": data });

			$.ajax({
				url: "sys/set_mepa_mantenimiento_correo.php",
				type: 'POST',
				data: data,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success: function(data){
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "mepa_mantenimiento_correo_grupo_correo_cargar_datos_a_editar", "data": respuesta });
					
					$.each(respuesta.data_correo_grupo,function(name,val)
					{
						if(name == "mepa_mantenimiento_correo_modal_grupo_correo_param_tipo")
						{
							$("#mepa_mantenimiento_correo_modal_grupo_correo_param_tipo").val(val).trigger("change.select2");

							return true;
						}
						else
						{
							$( "#"+name).val(val);
							return true;
						}
					});

					$("#sec_mepa_mantenimiento_correo_modal_grupo_correo .btn_guardar").text("Editar");
					$("#title_modal_sec_mepa_mantenimiento_correo_modal_grupo_correo").text("Editar Grupo Correo");
					$("#sec_mepa_mantenimiento_correo_modal_grupo_correo").modal("show");

				},
				complete: function(){
					swal.close();
					loading(false);
				}
			});
		}
	});
}

function mepa_mantenimiento_correo_modal_grupo_detalle_anular_usuario(param_grupo_detalle_id, param_grupo_id)
{
	swal(
	{
		title: '¿Está seguro de anular?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true,
		html: true,
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{

			var data = {
				"accion": "mepa_mantenimiento_correo_modal_grupo_detalle_anular_usuario",
				"param_grupo_detalle_id": param_grupo_detalle_id
			}

			auditoria_send({ "proceso": "mepa_mantenimiento_correo_modal_grupo_detalle_anular_usuario", "data": data });

			$.ajax({
				url: "sys/set_mepa_mantenimiento_correo.php",
				type: 'POST',
				data: data,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success: function(data){
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "mepa_mantenimiento_correo_modal_tipo_correo_activar_desactivar", "data": respuesta });
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
					        swal.close();
					        mepa_mantenimiento_correo_modal_grupo_detalle_listar_usuario(param_grupo_id);
					    });

						return true;
					} 
					else
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
				},
				complete: function(){
					loading(false);
				}
			});
		}
	});
}

function mepa_mantenimiento_correo_modal_grupo_detalle_agregar_usuario()
{
	
	var param_grupo_id = $("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_grupo_id").val().trim();
	var param_usuario = $("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario").val().trim();

	if(param_grupo_id == "")
	{
		alertify.error('No se encontro el ID del grupo, por favor vuelva a refrescar la página',5);
        $("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario").focus();
        setTimeout(function() 
        {
            $('#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario').select2('open');
        }, 200);

        return false;
	}

	if(param_usuario == "0")
	{
		alertify.error('Seleccione Usuario',5);
        $("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario").focus();
        setTimeout(function() 
        {
            $('#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario').select2('open');
        }, 200);

        return false;
	}

	swal(
	{
		title: '¿Está seguro de agregar?',
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
			var dataForm = new FormData($("#form_sec_mepa_mantenimiento_correo_modal_grupo_detalle")[0]);
			dataForm.append("accion","mepa_mantenimiento_correo_modal_grupo_detalle_agregar_usuario");
			dataForm.append("param_grupo_id", param_grupo_id);
			dataForm.append("param_usuario", param_usuario);

			$.ajax({
				url: "sys/set_mepa_mantenimiento_correo.php",
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
					auditoria_send({ "respuesta": "mepa_mantenimiento_correo_modal_guardar_o_editar_tipo_correo", "data": respuesta });
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
					    	swal.close();
					    	$("#mepa_mantenimiento_correo_modal_grupo_detalle_correo_param_usuario").val("0").trigger("change.select2");
					    	mepa_mantenimiento_correo_modal_grupo_detalle_listar_usuario(param_grupo_id);
					    });
						
						return true;
					}
					else
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
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);
}

$("#sec_mepa_mantenimiento_correo_btn_nuevo_grupo_correo").off("click").on("click",function(){
    
    mepa_mantenimiento_correo_modal_grupo_correo_limpiar_input();

    $("#sec_mepa_mantenimiento_correo_modal_grupo_correo .btn_guardar").text("Nuevo");
    $("#title_modal_sec_mepa_mantenimiento_correo_modal_grupo_correo").text("Nuevo Grupo Correo");
    $("#sec_mepa_mantenimiento_correo_modal_grupo_correo").modal("show");
})

function mepa_mantenimiento_correo_modal_grupo_correo_limpiar_input()
{
	$("#mepa_mantenimiento_correo_modal_grupo_correo_param_id").val("");
	$("#mepa_mantenimiento_correo_modal_grupo_correo_param_tipo").val("0").trigger("change.select2");
	$("#mepa_mantenimiento_correo_modal_grupo_correo_param_nombre").val("");
	$("#mepa_mantenimiento_correo_modal_grupo_correo_param_metodo").val("");
}

$("#sec_mepa_mantenimiento_correo_modal_grupo_correo .btn_guardar").off("click").on("click",function()
{
	
	var param_id = $('#mepa_mantenimiento_correo_modal_grupo_correo_param_id').val();
	var param_tipo = $('#mepa_mantenimiento_correo_modal_grupo_correo_param_tipo').val();
    var param_nombre = $('#mepa_mantenimiento_correo_modal_grupo_correo_param_nombre').val().trim();
    var param_metodo = $('#mepa_mantenimiento_correo_modal_grupo_correo_param_metodo').val().trim();

    let tipo_accion = 0;
	var title = "";

    if(param_id == "")
	{
		// TIPO INSERCION
		tipo_accion = 1;
		title = '¿Está seguro de guardar?';
	}
	else if(param_id != "")
	{
		// TIPO ACTUALIZACION
		tipo_accion = 2;
		title = '¿Está seguro de editar?';
	}

    if(param_tipo == "0")
    {
    	alertify.error('Seleccione Tipo Proceso',5);
        $("#mepa_mantenimiento_correo_modal_grupo_correo_param_tipo").focus();
        setTimeout(function() 
        {
            $('#mepa_mantenimiento_correo_modal_grupo_correo_param_tipo').select2('open');
        }, 200);

        return false;
    }

    if(param_nombre == "")
    {
    	alertify.error('Ingrese Nombre Grupo',5);
        return false;
    }

    if(param_metodo == "")
    {
    	alertify.error('Ingrese Metodo',5);
        return false;
    }

    swal(
	{
		title: title,
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
			var dataForm = new FormData($("#form_sec_mepa_mantenimiento_correo_modal_guardar_o_editar_grupo_correo")[0]);
			dataForm.append("accion","mepa_mantenimiento_correo_modal_guardar_o_editar_grupo_correo");
			dataForm.append("param_id", param_id);
			dataForm.append("param_tipo", param_tipo);
			dataForm.append("param_nombre", param_nombre);
			dataForm.append("param_metodo", param_metodo);
			dataForm.append("tipo_accion", tipo_accion);

			$.ajax({
				url: "sys/set_mepa_mantenimiento_correo.php",
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
					auditoria_send({ "respuesta": "mepa_mantenimiento_correo_modal_guardar_o_editar_grupo_correo", "data": respuesta });
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
					    	swal.close();
					    	$("#sec_mepa_mantenimiento_correo_modal_grupo_correo").modal("hide");
					    	mepa_mantenimiento_correo_buscar_correo_grupo();
					    });
						
						return true;
					}
					else
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
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);

})


$("#sec_mepa_mantenimiento_correo_btn_nuevo_tipo_correo").off("click").on("click",function(){
	
	mepa_mantenimiento_correo_tipo_correo_limpiar_input();
	mepa_mantenimiento_correo_tipo_correo_listar_tipo_proceso();
	
	$("#mepa_mantenimiento_correo_modal_tipo_correo_div_tabla").hide();
	$("#sec_mepa_mantenimiento_correo_modal_tipo_correo").modal("show");
})

function mepa_mantenimiento_correo_tipo_correo_limpiar_input()
{
	$("#mepa_mantenimiento_correo_modal_tipo_correo_param_id").val("");
	$("#mepa_mantenimiento_correo_modal_tipo_correo_param_nombre").val("");
	$("#mepa_mantenimiento_correo_modal_tipo_correo_param_descripcion").val("");
}

const mepa_mantenimiento_correo_tipo_correo_listar_tipo_proceso = () => {

    var data = {
        "accion": "mepa_mantenimiento_correo_tipo_correo_listar_tipo_proceso"
    }

    var array_servicios = [];
    
    $.ajax({
        url: "/sys/set_mepa_mantenimiento_correo.php",
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
            auditoria_send({ "respuesta": "mepa_mantenimiento_correo_tipo_correo_listar_tipo_proceso", "data": respuesta });
            
            if(parseInt(respuesta.http_code) == 400)
            {
                if(parseInt(respuesta.codigo) == 1)
                {
					var html = '<option value="0" selected>-- Todos --</option>';
                    html += '<option value="">' + respuesta.texto +'</option>';
                    $("#sec_mepa_mantenimiento_correo_modal_param_tipo_correo").html(html).trigger("change");
                    $("#sec_mepa_mantenimiento_correo_param_tipo_correo").html(html).trigger("change");
                    $("#mepa_mantenimiento_correo_modal_grupo_correo_param_tipo").html(html).trigger("change");

                    return false;
                }
                else if(parseInt(respuesta.codigo) == 2)
                {
                    swal({
                        title: respuesta.titulo,
                        text: respuesta.texto,
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
                array_servicios.push(respuesta.texto);
            
                var html = '<option value="0" selected>-- Todos --</option>';

                for (var i = 0; i < array_servicios[0].length; i++) 
                {
                    html += '<option value=' + array_servicios[0][i].id  + '>' + array_servicios[0][i].nombre +'</option>';
                }

                $("#sec_mepa_mantenimiento_correo_modal_param_tipo_correo").html(html).trigger("change");
                $("#sec_mepa_mantenimiento_correo_param_tipo_correo").html(html).trigger("change");
                $("#mepa_mantenimiento_correo_modal_grupo_correo_param_tipo").html(html).trigger("change");

                return false;
            }
        },
        error: function() {}
    });
}

function mepa_mantenimiento_correo_modal_guardar_o_editar_tipo_correo()
{
	
	var param_id = $("#mepa_mantenimiento_correo_modal_tipo_correo_param_id").val().trim();
	var param_nombre = $("#mepa_mantenimiento_correo_modal_tipo_correo_param_nombre").val().trim();
	var param_descripcion = $("#mepa_mantenimiento_correo_modal_tipo_correo_param_descripcion").val().trim();

	let tipo_accion = 0;
	var title = "";

	if(param_nombre == "")
	{
		alertify.error('Ingrese el Nombre',5);
		return false;
	}

	if(param_id == "")
	{
		// TIPO INSERCION
		tipo_accion = 1;
		title = '¿Está seguro de guardar?';
	}
	else if(param_id != "")
	{
		// TIPO ACTUALIZACION
		tipo_accion = 2;
		title = '¿Está seguro de editar?';
	}

	swal(
	{
		title: title,
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
			var dataForm = new FormData($("#form_sec_mepa_mantenimiento_correo_modal_guardar_o_editar_tipo_correo")[0]);
			dataForm.append("accion","mepa_mantenimiento_correo_modal_guardar_o_editar_tipo_correo");
			dataForm.append("param_id", param_id);
			dataForm.append("param_nombre", param_nombre);
			dataForm.append("param_descripcion", param_descripcion);
			dataForm.append("tipo_accion", tipo_accion);

			$.ajax({
				url: "sys/set_mepa_mantenimiento_correo.php",
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
					auditoria_send({ "respuesta": "mepa_mantenimiento_correo_modal_guardar_o_editar_tipo_correo", "data": respuesta });
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
					    	swal.close();
					    	mepa_mantenimiento_correo_tipo_correo_listar_tipo_proceso();
					    	mepa_mantenimiento_correo_modal_buscar_tipo_correo();
					    });
						
						return true;
					}
					else
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
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);
}

function mepa_mantenimiento_correo_modal_buscar_tipo_correo()
{
	
	if(sec_id == "mepa" && sub_sec_id == "mantenimiento")
	{
		mepa_mantenimiento_correo_tipo_correo_limpiar_input();

		var param_tipo_correo = $("#sec_mepa_mantenimiento_correo_modal_param_tipo_correo").val();

		var data = {
			"accion": "mepa_mantenimiento_correo_modal_buscar_tipo_correo",
			"param_tipo_correo" : param_tipo_correo
		}

		$("#mepa_mantenimiento_correo_modal_tipo_correo_div_tabla").show();

		tabla = $("#mepa_mantenimiento_correo_modal_tipo_correo_datatable").dataTable(
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
					url : "/sys/set_mepa_mantenimiento_correo.php",
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
                        targets: [0, 1, 2, 3, 4, 5, 6]
                    }
                ],
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100],
				"order" : 
				[
					1, "desc"	
				]
			}
		).DataTable();
	}
}

function mepa_mantenimiento_correo_modal_tipo_correo_cargar_datos_a_editar(id, nombre, descripcion)
{
	
	loading(true);
	$("#mepa_mantenimiento_correo_modal_tipo_correo_param_id").val(id);
	$("#mepa_mantenimiento_correo_modal_tipo_correo_param_nombre").val(nombre);
	$("#mepa_mantenimiento_correo_modal_tipo_correo_param_descripcion").val(descripcion);
	loading(false);

}

function mepa_mantenimiento_correo_modal_tipo_correo_activar_desactivar(param_id, param_tipo)
{
	title = "";

	if(param_tipo == 1)
	{
		title = "¿Está seguro de activar?";
	}
	else
	{
		title = "¿Está seguro de desactivar?";
	}

	swal(
	{
		title: title,
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true,
		html: true,
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{

			var data = {
				"accion": "mepa_mantenimiento_correo_modal_tipo_correo_activar_desactivar",
				"param_id": param_id,
				"param_tipo": param_tipo
			}

			auditoria_send({ "proceso": "mepa_mantenimiento_correo_modal_tipo_correo_activar_desactivar", "data": data });

			$.ajax({
				url: "sys/set_mepa_mantenimiento_correo.php",
				type: 'POST',
				data: data,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success: function(data){
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "mepa_mantenimiento_correo_modal_tipo_correo_activar_desactivar", "data": respuesta });
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
					        swal.close();
					        mepa_mantenimiento_correo_modal_buscar_tipo_correo();
					    });

						return true;
					} 
					else
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
				},
				complete: function(){
					loading(false);
				}
			});
		}
	});
}