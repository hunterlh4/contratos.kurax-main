function sec_mepa_cuenta_bancaria()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_cuenta_bancaria_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}

function mepa_cuenta_bancaria_buscar()
{
	$("#mepa_cuenta_bancaria_div_tabla").hide();
	mepa_cuenta_bancaria_listar_datatable();
}

function mepa_cuenta_bancaria_listar_datatable()
{
	if(sec_id == "mepa" && sub_sec_id == "cuenta_bancaria")
	{
		var param_usuario = $("#mepa_cuenta_bancaria_param_usuario").val();
		var param_situacion = $("#mepa_cuenta_bancaria_param_situacion").val();

		var data = {
			"accion": "mepa_cuenta_bancaria_listar",
			"param_usuario" : param_usuario,
			"param_situacion" : param_situacion
		}
		
		$("#mepa_cuenta_bancaria_div_tabla").show();
		
		tabla = $("#mepa_cuenta_bancaria_datatable").dataTable(
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
					url : "/sys/set_mepa_cuenta_bancaria.php",
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
				"order": [[2, 'asc']],
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();
	}
}

$('#mepa_cuenta_bancaria_modal_form_param_situacion').change(function () 
{
	$("#mepa_cuenta_bancaria_modal_form_param_situacion option:selected").each(function ()
	{	
		
		var selectValor = $(this).val();

		if(selectValor == 7)
		{
			$("#div_mepa_cuenta_bancaria_modal_form_param_situacion_motivo").show();
		}
		else
		{
			$("#div_mepa_cuenta_bancaria_modal_form_param_situacion_motivo").hide();
		}
	});
});

function sec_mepa_cuenta_bancaria_atender_solicitud(cuenta_bancaria_id, asignacion_id)
{
	$("#div_mepa_cuenta_bancaria_modal_form_param_situacion_motivo").hide();
	$("#mepa_cuenta_bancaria_modal_form_param_situacion_motivo").val("");
	$("#mepa_cuenta_bancaria_modal_form_param_situacion").val('0').trigger('change');

	$("#modal_form_cuenta_bancaria_atender_solicitud .btn_guardar").show();
	$("#title_modal_form_cuenta_bancaria_atender_solicitud").text("Atender Solicitud");
	$("#modal_form_cuenta_bancaria_atender_solicitud").modal("show");
	$( "#modal_form_id_asignacion",$("#modal_form_cuenta_bancaria_atender_solicitud form")).val(asignacion_id);
	$( "#modal_form_id_cuenta_bancaria",$("#modal_form_cuenta_bancaria_atender_solicitud form")).val(cuenta_bancaria_id);
}

$("#modal_form_cuenta_bancaria_atender_solicitud .btn_guardar").off("click").on("click",function(){
    
    var modal_form_id_asignacion = $('#modal_form_id_asignacion').val();
    var modal_form_id_cuenta_bancaria = $('#modal_form_id_cuenta_bancaria').val();
    var param_situacion = $('#mepa_cuenta_bancaria_modal_form_param_situacion').val();
    var param_situacion_motivo = $('#mepa_cuenta_bancaria_modal_form_param_situacion_motivo').val().toUpperCase();

    if(modal_form_id_asignacion == ""  || modal_form_id_asignacion == 0)
	{
		alertify.error('No existe el ID Asignación, por favor contactarse con Sistemas',5);
		return false;
	}

	if(modal_form_id_cuenta_bancaria == ""  || modal_form_id_cuenta_bancaria == 0)
	{
		alertify.error('No existe el ID Cuenta Bancaria, por favor contactarse con Sistemas',5);
		return false;
	}

    if(param_situacion == "0")
	{
		alertify.error('Seleccione Situación',5);
		$("#mepa_cuenta_bancaria_modal_form_param_situacion").focus();
		setTimeout(function() 
		{
			$('#mepa_cuenta_bancaria_modal_form_param_situacion').select2('open');
		}, 200);
		return false;
	}

	if(param_situacion == 7)
	{
		if(param_situacion_motivo.length == 0)
		{
			alertify.error('Ingrese Motivo Rechazo',5);
			$("#mepa_cuenta_bancaria_modal_form_param_situacion_motivo").focus();
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
			var dataForm = new FormData($("#mepa_cuenta_bancaria_form_atencion_cuenta_bancaria")[0]);
			dataForm.append("accion", "mepa_cuenta_bancaria_atender_solicitud");
			$.ajax({
		        url: "sys/set_mepa_cuenta_bancaria.php",
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
							title: "Atención exitosa",
							text: "La atención fue registrada exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.reload();
					    });

						setTimeout(function() {
							window.location.reload();
						}, 5000);

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
})
