// INICIO FUNCION DE INICIALIZACION
function sec_mepa_zona_asignacion()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_zona_asignacion_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}
// FIN FUNCION DE INICIALIZACION

function mepa_zona_asignacion_buscar_por_parametros()
{
	sec_mepa_zona_asignacion_listar__datatable();
}

function sec_mepa_zona_asignacion_listar__datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "zona_asignacion")
	{
		var sec_mepa_zona_asignacion_param_usuario = $("#sec_mepa_zona_asignacion_param_usuario").val();
		var sec_mepa_zona_asignacion_param_zona = $("#sec_mepa_zona_asignacion_param_zona").val();

		var data = {
			"accion": "mepa_zona_asignacion_listar_usuario_zona_asignacion",
			"sec_mepa_zona_asignacion_param_usuario" : sec_mepa_zona_asignacion_param_usuario,
			"sec_mepa_zona_asignacion_param_zona" : sec_mepa_zona_asignacion_param_zona
		}

		auditoria_send({"proceso":"mepa_zona_asignacion_listar_usuario_zona_asignacion","data":data});
				
		$("#sec_mepa_zona_asignacion_div_tabla").show();

		tabla = $("#sec_mepa_zona_asignacion_datatable").dataTable(
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
					url : "/sys/set_mepa_zona_asignacion.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100],
				"order": [
					[0, 'asc']
				],
				columnDefs: [{
					targets: [0, 1, 2]
				},
					{ targets: 0, orderable: false },
					{ targets: 1, orderable: false },
					{ targets: 2, orderable: false }
				],
				"drawCallback": function (settings) {
			
					var api = this.api();
					var rows = api.rows({ page: 'all' }).nodes();
					var last = null;
					
					var usuario_id = 0;

					api.column(0, { page: 'all' }).data().each(function (group, i) {
						group_assoc = group.replace(' ', "_");
						
						let dividir_select = group_assoc.split('-');
						usuario =  dividir_select[0];
						usuario_id =  dividir_select[1];
						
						if (last !== group)
						{
							$(rows).eq(i).before(
								'<tr style="background-color: #F0F0F0 !important;"><td class="">' + '<h4><span class="badge badge-dark">' + usuario + '</span> <button class="btn btn-warning btn-xs btn-rounded" onclick="zona_asignacion_editar_usuario_zona('+usuario_id+');" title="Clic para agregar o quitar una zona al usuario"><span class="fa fa-pencil"></span> Editar</button></h4></td><td></td><td></td></tr>'
							);

							last = group;
						}
					});
				}

			}
		).DataTable();

	}
	else
	{
		alertify.error('No estas en la vista correspondiente, por favor contactarse con Sistemas',5);
		return false;
	}
}

$("#mepa_zona_asignacion_btn_nuevo_usuario").off("click").on("click",function(){
	
	$("#mepa_zona_asignacion_modal_nuevo_usuario #div_zona_asignacion_usuario").hide();
	$("#zona_asignacion_usuario_id",$("#mepa_zona_asignacion_modal_nuevo_usuario form")).val('');

	$("#mepa_zona_asignacion_modal_nuevo_usuario .btn_editar").hide();
	$("#mepa_zona_asignacion_modal_nuevo_usuario .btn_guardar").show();
	$("#mepa_zona_asignacion_modal_nuevo_usuario #div_sec_mepa_form_param_usuario").show();
	$("#title_mepa_zona_asignacion_modal_nuevo_usuario").text("Agregar usuario - Zona");
	

	var data = {
        "accion": "sec_mepa_zona_asignacion_listar_zonas",
        "param_usuario_id" : 0
    }

    $.ajax({
		url: "sys/set_mepa_zona_asignacion.php",
		type: 'POST',
		data: data,
		//cache: false,
		//contentType: false,
		//processData: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(data){
			
			var resp = JSON.parse(data);
			$("#sec_mepa_form_param_grupo_permisos_usuario_zona").html(resp.lista_zonas);
			$("#zona_asignacion_usuario").val(resp.usuario);
		},
		complete: function(){
			loading(false);
		}
	});

	$("#mepa_zona_asignacion_modal_nuevo_usuario").modal("show");

})

$("#mepa_zona_asignacion_modal_nuevo_usuario .btn_guardar").off("click").on("click",function(){
    
    var dataForm = new FormData($("#form_zona_asignacion_usuario_zona")[0]);

    var sec_mepa_form_param_usuario = $('#sec_mepa_form_param_usuario').val();

    if(sec_mepa_form_param_usuario == "0")
	{
		alertify.error('Seleccione el Usuario',5);
		$("#sec_mepa_form_param_usuario").focus();
		return false;
	}

    dataForm.append("accion","mepa_zona_asignacion_nuevo_usuario_zona");
	
	$.ajax({
        url: "sys/set_mepa_zona_asignacion.php",
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
					title: "Registro exitoso",
					text: "El usuario fue registrado correctamente",
					html:true,
					type: "success",
					timer: 6000,
					closeOnConfirm: false,
					showCancelButton: false
				},
			    function (isConfirm) {
			        window.location.href = "?sec_id=mepa&sub_sec_id=zona_asignacion";
			    });

				setTimeout(function() {
					window.location.href = "?sec_id=mepa&sub_sec_id=zona_asignacion";
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
					title: "Error al guardar Solicitud",
					text: respuesta.status,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
        }
    });
})

function zona_asignacion_editar_usuario_zona(usuario_id)
{
	
	$("#mepa_zona_asignacion_modal_nuevo_usuario #div_sec_mepa_form_param_usuario").hide();

	$("#zona_asignacion_usuario_id",$("#mepa_zona_asignacion_modal_nuevo_usuario form")).val(usuario_id);
	
	var data = {
        "accion": "sec_mepa_zona_asignacion_listar_zonas",
        "param_usuario_id" : usuario_id
    }

    $.ajax({
		url: "sys/set_mepa_zona_asignacion.php",
		type: 'POST',
		data: data,
		//cache: false,
		//contentType: false,
		//processData: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(data){
			
			var resp = JSON.parse(data);
			$("#sec_mepa_form_param_grupo_permisos_usuario_zona").html(resp.lista_zonas);
			$("#zona_asignacion_usuario").val(resp.usuario);
		},
		complete: function(){
			loading(false);
		}
	});

	$("#mepa_zona_asignacion_modal_nuevo_usuario .btn_guardar").hide();
	$("#mepa_zona_asignacion_modal_nuevo_usuario .btn_editar").show();
	$("#mepa_zona_asignacion_modal_nuevo_usuario #div_zona_asignacion_usuario").show();
	$("#title_mepa_zona_asignacion_modal_nuevo_usuario").text("Editar detalle liquidación");
	$("#mepa_zona_asignacion_modal_nuevo_usuario").modal("show");
}

$("#mepa_zona_asignacion_modal_nuevo_usuario .btn_editar").off("click").on("click",function(){
	
	var zona_asignacion_usuario_id = $('#zona_asignacion_usuario_id').val();
	

    if(zona_asignacion_usuario_id == "")
	{
		alertify.error('No pudimos encontrar el Id del Usuario',5);
		$("#zona_asignacion_usuario_id").focus();
		return false;
	}

	var dataForm = new FormData($("#form_zona_asignacion_usuario_zona")[0]);
    dataForm.append("accion","mepa_zona_asignacion_editar_usuario_zona");
	
	$.ajax({
	    url: "sys/set_mepa_zona_asignacion.php",
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
					title: "Registro exitoso",
					text: "El usuario fue registrado correctamente",
					html:true,
					type: "success",
					timer: 6000,
					closeOnConfirm: false,
					showCancelButton: false
				},
			    function (isConfirm) {
			        window.location.href = "?sec_id=mepa&sub_sec_id=zona_asignacion";
			    });

				setTimeout(function() {
					window.location.href = "?sec_id=mepa&sub_sec_id=zona_asignacion";
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
					title: "Error al guardar Solicitud",
					text: respuesta.status,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
	    }
	});
})
