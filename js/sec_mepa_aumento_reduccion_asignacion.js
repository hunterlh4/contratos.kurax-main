function sec_mepa_aumento_reduccion_asignacion()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_aumento_reduccion_asignacion_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	// INICIO FORMATO CAMPO MONTO
	$(".sec_mepa_aumento_form_txt_monto").on({
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
	// FIN FORMATO CAMPO MONTO
}

function mepa_aumento_asignacion_buscar()
{
	$("#mepa_aumento_asignacion_div_tabla").hide();
	$("#mepa_aumento_asignacion_div_solicitar").hide();
	mepa_aumento_asignacion_listar_asignacion_datatable();
}

function mepa_aumento_asignacion_listar_asignacion_datatable()
{
	if(sec_id == "mepa" && sub_sec_id == "aumento_reduccion_asignacion")
	{
		var param_usuario = $("#mepa_aumento_asignacion_param_usuario").val();

		var data = {
			"accion": "mepa_aumento_asignacion_listar_asignacion",
			"param_usuario" : param_usuario
		}
		
		$("#mepa_aumento_asignacion_div_tabla").show();
		
		tabla = $("#mepa_aumento_asignacion_datatable").dataTable(
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
					url : "/sys/set_mepa_aumento_reduccion_asignacion.php",
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
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

function mepa_aumento_asignacion_nueva_solicitud(id_asignacion, usuario, empresa, zona, fondo)
{
	loading(true);
	
	$("#sec_mepa_aumento_form_txt_monto").val("");
	$("#sec_mepa_aumento_form_txt_motivo").val("");

	$('#mepa_aumento_asignacion_div_solicitar').hide();

	$("#mepa_aumento_solicitud_id_asignacion_nueva_solictud").val(id_asignacion);
	$("#mepa_aumento_solicitud_usuario").html(usuario);
	$("#mepa_aumento_solicitud_empresa").html(empresa);
	$("#mepa_aumento_solicitud_zona").html(zona);
	$("#mepa_aumento_solicitud_fondo").html("S/ "+fondo);

	setTimeout(function() {
		loading(false);
		$('#mepa_aumento_asignacion_div_solicitar').show();
	}, 1000);

}

function mepa_aumento_asignacion_btn_nueva_solicitud()
{
	var param_id_asignacion_nueva_solictud = $("#mepa_aumento_solicitud_id_asignacion_nueva_solictud").val();
	var param_txt_tipo_solicitud = $("#sec_mepa_aumento_form_txt_tipo_solicitud").val();
	var param_txt_monto = $("#sec_mepa_aumento_form_txt_monto").val();
	var param_txt_motivo = $("#sec_mepa_aumento_form_txt_motivo").val().trim();

	var txt_titulo_pregunta = "";

	if(param_txt_tipo_solicitud == 0)
	{
		alertify.error('Seleccione Tipo Solicitud',5);
		$("#sec_mepa_aumento_form_txt_tipo_solicitud").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_aumento_form_txt_tipo_solicitud').select2('open');
		}, 200);
		return false;
	}

	if(param_txt_monto == "")
	{
		alertify.error('Ingrese el Monto',5);
		$("#sec_mepa_aumento_form_txt_monto").focus();
		return false;
	}

	if(param_txt_monto == 0)
	{
		alertify.error('Monto no permitido',5);
		$("#sec_mepa_aumento_form_txt_monto").focus();
		return false;
	}

	if(param_txt_motivo == "")
	{
		alertify.error('Ingrese el Motivo',5);
		$("#sec_mepa_aumento_form_txt_motivo").focus();
		return false;
	}

	if(param_id_asignacion_nueva_solictud == 0)
	{
		alertify.error('No existe el ID de la asignación',5);
		return false;
	}

	if(param_txt_tipo_solicitud == 9)
	{
		txt_titulo_pregunta = "Aumentar";
	}
	else if(param_txt_tipo_solicitud == 10)
	{
		txt_titulo_pregunta = "Reducir";
	}

	swal(
	{
		title: '¿Está seguro de '+txt_titulo_pregunta+' el Fondo?',
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
			var dataForm = new FormData($("#form_sec_mepa_aumento_nueva_solicitud")[0]);
			dataForm.append("accion","mepa_aumento_asignacion_guardar_nueva_solicitud");
			dataForm.append("param_id_asignacion_nueva_solictud", param_id_asignacion_nueva_solictud);
			dataForm.append("param_txt_tipo_solicitud", param_txt_tipo_solicitud);
			dataForm.append("param_txt_monto", param_txt_monto);
			dataForm.append("param_txt_motivo", param_txt_motivo);

			$.ajax({
				url: "sys/set_mepa_aumento_reduccion_asignacion.php",
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
					auditoria_send({ "respuesta": "form_sec_mepa_aumento_nueva_solicitud", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: respuesta.status,
							text: respuesta.texto,
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=mepa&sub_sec_id=aumento_reduccion_asignacion&id";
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=aumento_reduccion_asignacion&id";
						}, 5000);

						return true;
					}
					if(parseInt(respuesta.http_code) == 300) 
					{
						swal({
							title: respuesta.status,
							text: respuesta.texto,
							html:true,
							type: "warning",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						});

						return true;
					} 
					else {
						swal({
							title: "Error al guardar Solicitud",
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
