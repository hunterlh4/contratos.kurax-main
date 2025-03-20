// INICIO FUNCION DE INICIALIZACION
function sec_mepa_movilidad()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_movilidad_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mepa_solicitud_movilidad_inicializar_param_fechas();
}
// FIN FUNCION DE INICIALIZACION

function sec_mepa_solicitud_movilidad_inicializar_param_fechas()
{
	// INICIO INICIALIZACION DE DATEPICKER
	$('.mepa_solicitud_movilidad_datepicker')
		.datepicker({
			dateFormat:'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: '-15d',
			maxDate: '0d'
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
}

function mepa_solicitud_movilidad()
{
	$("#tabla_form_nueva_solicitud_movilidad_supervisor").hide();
	$("#tabla_form_nueva_solicitud_movilidad_cajero_volante").hide();

	var tipo_solicitud = $("#sec_mepa_solicitud_movilidad_select_tipo_solicitud").val();

	if(tipo_solicitud == 0)
	{
		alertify.error('Seleccione Tipo Solicitud',5);
		$("#sec_mepa_solicitud_movilidad_select_tipo_solicitud").focus();
		setTimeout(function() {
			$('#sec_mepa_solicitud_movilidad_select_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}
	else if(tipo_solicitud == 7)
	{
		// SOLICITUD MOVILIDAD SUPERVISOR
		mepa_solicitud_movilidad_listar_movilidad_supervisor();

	}
	else if(tipo_solicitud == 8)
	{
		// SOLICITUD MOVILIDAD CAJERO VOLANTE
		mepa_solicitud_movilidad_listar_movilidad_cajero_volante();
	}
	else
	{
		alertify.error('No existe el Tipo Solicitud',5);
	}

}

function mepa_solicitud_movilidad_listar_movilidad_supervisor()
{
	if(sec_id == "mepa" && sub_sec_id == "solicitud_movilidad")
	{
		var tipo_solicitud = $("#sec_mepa_solicitud_movilidad_select_tipo_solicitud").val();

		if(tipo_solicitud == 0)
		{
			alertify.error('Seleccione Tipo Solicitud',5);
			$("#sec_mepa_solicitud_movilidad_select_tipo_solicitud").focus();
			setTimeout(function() {
				$('#sec_mepa_solicitud_movilidad_select_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}

		var data = {
			"accion": "mepa_solicitud_movilidad_listar_movilidad_supervisor",
			"param_tipo_solicitud" : tipo_solicitud
		}

		$("#tabla_form_nueva_solicitud_movilidad_supervisor").show();

		tabla = $("#mepa_solicitud_movilidad_listar_movilidad_supervisor_datatable").dataTable(
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
					url : "/sys/get_mepa_movilidad.php",
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
				"order": [[1, 'desc']],
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();
	}
	else
	{
		alertify.error('No te encuentras en la vista correspondiente',5);
		return false;
	}
}

function mepa_solicitud_movilidad_listar_movilidad_cajero_volante()
{
	if(sec_id == "mepa" && sub_sec_id == "solicitud_movilidad")
	{
		var tipo_solicitud = $("#sec_mepa_solicitud_movilidad_select_tipo_solicitud").val();

		if(tipo_solicitud == 0)
		{
			alertify.error('Seleccione Tipo Solicitud',5);
			$("#sec_mepa_solicitud_movilidad_select_tipo_solicitud").focus();
			setTimeout(function() {
				$('#sec_mepa_solicitud_movilidad_select_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}

		var data = {
			"accion": "mepa_solicitud_movilidad_listar_movilidad_cajero_volante",
			"param_tipo_solicitud" : tipo_solicitud
		}

		$("#tabla_form_nueva_solicitud_movilidad_cajero_volante").show();

		tabla = $("#mepa_solicitud_movilidad_listar_movilidad_cajero_volante_datatable").dataTable(
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
					url : "/sys/get_mepa_movilidad.php",
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
				"order": [[1, 'desc']],
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();
	}
	else
	{
		alertify.error('No te encuentras en la vista correspondiente',5);
		return false;
	}
}

function sec_mepa_solicitud_movilidad_nueva_movilidad()
{
	$("#modal_form_nueva_solictud_movilidad").modal("show");
}

$('#mepa_solicitud_movilidad_modal_param_fecha_inicio,#mepa_solicitud_movilidad_modal_param_fecha_fin').change(function (e) {
	e.preventDefault();
	
	var fechaInicio = $("#mepa_solicitud_movilidad_modal_param_fecha_inicio").val();
	var fechaFin = $("#mepa_solicitud_movilidad_modal_param_fecha_fin").val();

	if(fechaInicio != '' & fechaFin != '')
	{
		if(new Date(fechaInicio) > new Date(fechaFin))
		{
			swal({
				type: "warning",
				title: "Alerta, rango de fechas",
				text: "La Fecha Inicio debe ser menor a la Fecha Fin ",
				timer: 8000
			});
			$('.btn_guardar').prop("disabled", true);
		}
		else
		{
			$('.btn_guardar').prop("disabled", false);
		}
	}
});

$("#modal_form_nueva_solictud_movilidad .btn_guardar").off("click").on("click",function(){
    
    
    var dataForm = new FormData($("#form_nueva_solictud_movilidad")[0]);

    var modal_form_id_usuario = $('#idUsuarioLogin').val();
    var modal_form_param_fecha_inicio = $('#mepa_solicitud_movilidad_modal_param_fecha_inicio').val();
    var modal_form_param_fecha_fin = $('#mepa_solicitud_movilidad_modal_param_fecha_fin').val();
    var modal_form_param_tipo_solicitud = $('#mepa_solicitud_movilidad_modal_param_tipo_solicitud').val();
    var modal_form_param_cajero_volante = $('#mepa_solicitud_movilidad_modal_param_cajero_volante').val();
    
    if(modal_form_param_fecha_inicio == "")
    {
    	alertify.error('Seleccione Fecha Inicio',5);
		$("#mepa_solicitud_movilidad_modal_param_fecha_inicio").focus();
		
		return false;
    }

    if(modal_form_param_fecha_fin == "")
    {
    	alertify.error('Seleccione Fecha Fin',5);
		$("#mepa_solicitud_movilidad_modal_param_fecha_fin").focus();
		
		return false;
    }

    if(modal_form_param_tipo_solicitud == "0")
	{
		alertify.error('Seleccione Tipo Solicitud',5);
		$("#mepa_solicitud_movilidad_modal_param_tipo_solicitud").focus();
		setTimeout(function() {
			$('#mepa_solicitud_movilidad_modal_param_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}

	if(modal_form_param_tipo_solicitud == "8")
	{
		if(modal_form_param_cajero_volante == "0")
		{
			alertify.error('Seleccione Cajero Volante',5);
			$("#mepa_solicitud_movilidad_modal_param_cajero_volante").focus();
			setTimeout(function() {
			$('#mepa_solicitud_movilidad_modal_param_cajero_volante').select2('open');
		}, 500);

			return false;
		}
	}

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
			dataForm.append("accion", "mepa_solicitud_movilidad_guardar_nueva_movilidad");
			$.ajax({
		        url: "sys/get_mepa_movilidad.php",
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
		        	if (respuesta.error == false)
		        	{
						$("#modal_form_nueva_solictud_movilidad").modal("hide");
						loading(false);
						swal({
							title: "Registro exitoso",
							text: "La solicitud fue registrada exitosamente",
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

					}
					if(respuesta.error == true)
					{
						swal("Error", respuesta.message, "error");
						loading(false);
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

$('#mepa_solicitud_movilidad_modal_param_tipo_solicitud').change(function () 
{
	$("#mepa_solicitud_movilidad_modal_param_tipo_solicitud option:selected").each(function ()
	{	
		var selectValor = $(this).val();

		if(selectValor == 0)
		{
			$("#sec_mepa_solictud_movilidad_div_usuario_volante").hide();

			alertify.error('Seleccione la Solicitud',5);
			$("#mepa_solicitud_movilidad_modal_param_tipo_solicitud").focus();
			setTimeout(function() {
				$('#mepa_solicitud_movilidad_modal_param_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}
		else if(selectValor == 7)
		{
			$("#sec_mepa_solictud_movilidad_div_usuario_volante").hide();
		}
		else if(selectValor == 8)
		{
			$("#sec_mepa_solictud_movilidad_div_usuario_volante").show();
		}
	});
});
