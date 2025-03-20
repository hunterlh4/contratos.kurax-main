function sec_autollenado_tambo(){
	if(sec_id == "caja") {
		sec_autollenado_tambo_events();
	}
}

function sec_autollenado_tambo_events()
{
    $(".autollenado_tambo_modal_btn").off("click").on("click",function(){

		if (item_config.turnos_local_id) {
			$("#autollenado_tambo_local_id").val(item_config.turnos_local_id).change().select2({
				width:"100%",
				dropdownParent: $('#autollenado_tambo_modal'),
				placeholder: "-- Seleccione un local --"
			});
		}
        $("#autollenado_tambo_modal").modal("show");
    })
    $(".autollenado_tambo_date")
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

    $("#autollenado_tambo_local_id")
		.off()
		.change(function (event) {
			var local_id = $(this).val();
			$.post('/sys/get_caja.php', {
				"get_local_cajas": local_id
			}, function (r) {
				try {
					$("#autollenado_tambo_local_caja_id").html(r);
					$("#autollenado_tambo_local_caja_id").change();
					autollenado_tambo_monto_inicial_refresh();
				} catch (err) {
				}
			});
	});

    $(".autollenado_tambo_monto_inicial_refresh_btn")
    .off()
    .click(function (event) {
        autollenado_tambo_monto_inicial_refresh();
    });
    $("#autollenado_tambo_local_id").change();
    $("#autollenado_tambo_local_id").select2({
		width:"100%",
		dropdownParent: $('#autollenado_tambo_modal'),
		placeholder: "-- Seleccione un local --"
	});

    $(".autollenado_tambo_inicio").off("click").on("click",function(){
        $("#form_autollenado_tambo").submit();
    })
    $("#form_autollenado_tambo").submit(function (e) {
        e.preventDefault();
        autollenado_tambo($("#form_autollenado_tambo")[0]);
    })

}
function autollenado_tambo_monto_inicial_refresh() {
	loading(true);
	var get_data = {};
    get_data["local_id"] =  $("#autollenado_tambo_local_id").val();
    get_data["local_caja_id"] =  $("#autollenado_tambo_local_caja_id").val();
	$.post('/sys/get_caja.php', {
		"abrir_caja_monto_inicial_refresh": get_data
	}, function (r) {
		try {
			var obj = jQuery.parseJSON(r);
			loading();
			$("#autollenado_tambo_apertura").val(obj.valor).finish().effect("highlight", 1500);
			get_data.valor = obj.valor;
			auditoria_send({"proceso": "autollenado_tambo_monto_inicial_refresh", "data": get_data});
		} catch (err) {
		}
	});
}


function autollenado_tambo(form){
    save_data = [];
	$("input:visible, select" ,$(form)).each(function(index, el) {
		save_data[$(el).attr("name")]=$(el).val();
	});
    $.ajax({
        url: '/sys/set_autollenado_tambo.php',
        type: 'POST',
        data: new FormData(form),
        cache: false,
        contentType: false,
        processData: false,
		beforeSend: function () {
			loading(true);
		},
		complete: function () {
			loading();
		},
        success: function(r){
			try{
				var obj = jQuery.parseJSON(r);
				loading(false);
				if(obj.error){
					save_data.error = obj.error;
					auditoria_send({"proceso":"autollenado_tambo_save_error","data" : save_data});
					loading(false);
					swal({
						title: "Error!",
						text: obj.error,
						type: "warning",
						closeOnConfirm: true
					},
					function(){
						swal.close();
						custom_highlight($("#"+obj.error_focus, form));
						setTimeout(function(){
							$("#"+obj.error_focus,form).val("").focus();
						}, 10);
					});
				}else{
					save_data.curr_login = obj.curr_login;
					
					/*abrir turno mgs */
					if (obj.no_login) {
						auditoria_send({"proceso": "sec_caja_abrir_turno_no_login", "data": save_data});
						loading();
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
					else
					{
						/*abrir turno mgs */
						auditoria_send({"proceso":"autollenado_tambo_save_done","data":save_data});
						loading(false);
						swal({
							title: "Listo",
							text: '<div style="height: 10em;verflow-x: hidden;overflow-y: scroll;width: 100%;border: 1px solid #828282;text-align: left !important;">' + obj.mensaje + '</div>',
							type: "success",
							closeOnConfirm: true,
							html:true
						},
						function(){
							m_reload();
							auditoria_send({"proceso":"autollenado_tambo_save_done","data":save_data});
							window.location.reload();
						});
					}
				}
			}catch(err){

			}

        }
    });
}
