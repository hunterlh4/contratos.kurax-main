function sec_servicio_tecnico_derivacion(){
	if(sec_id=="servicio_tecnico_derivacion"  ||  sec_id=="servicio_tecnico_derivacion_form") {
		console.log("sec:servicio_tecnico_derivacion");
		sec_servicio_tecnico_derivacion_events();
	}
}
function sec_servicio_tecnico_derivacion_events(){
	$(".select2")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width:"100%"
	});        

	$("#btn_ubicacion").click();

    sec_servicio_tecnico_derivacion_modal_detalle_events();
    $(".sec_servicio_tecnico_derivacion_detalle").off("click").on("click",function(){
        sec_servicio_tecnico_derivacion_cargar_solicitud(this);
    })
}

function sec_servicio_tecnico_derivacion_modal_detalle_events(){
	$("#modal_detalle #sec_servicio_tecnico_derivacion_guardar_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
        sec_servicio_tecnico_derivacion_update(form);
	})
	$("#modal_detalle").off("shown.bs.modal").on("shown.bs.modal",function(){
		$('.modal').css('overflow-y', 'auto');
		loading();
	});
	$("#modal_detalle").off("hidden.bs.modal").on("hidden.bs.modal",function(){
		$(".comentario, .foto_terminado").hide();
		$("#modal_detalle select, textarea ,input:file").val("");
		$("#modal_detalle #foto_terminado").attr("src","data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==");
	});
}

function sec_servicio_tecnico_derivacion_cargar_solicitud(elem){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = $(elem).attr("data-solicitud_id") ;
    set_data.incidencia_id = $(elem).attr("data-incidencia_id") ;
	set_data.sec_servicio_tecnico_derivacion_cargar_solicitud = "sec_servicio_tecnico_derivacion_cargar_solicitud";
	$('#equipo_id').select2({
		placeholder: "Seleccione"
	});

	$.ajax({
		url: 'sys/set_servicio_tecnico_derivacion.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			modal_detalle_events();
			$.each(obj.local,function(i,e){
				if($("#modal_detalle #" + i).length > 0 ){
					if(e != "")
					{
						if( i == "equipo_id" )
						{
							let estado = $("#modal_detalle #equipo_id [data-nombre = '" + e +"']").val();
							$("#modal_detalle #equipo_id").val( estado ).change();
						}else{
							if($("#modal_detalle #"+i)[0].nodeName == "P" || $("#modal_detalle #"+i)[0].nodeName == "DIV")
							{
								$("#modal_detalle #"+i).text(e);
							}
							else{
								$("#modal_detalle #"+i).val(e);
							}
						}
						
					}
				}
			})
			if( obj.local["estado"] == "Terminado" ){
				if(obj.local["foto_terminado"] != null)
				{
					$("#modal_detalle #foto_terminado").attr("src","files_bucket/solicitud_mantenimiento/"+obj.local["foto_terminado"]);
				}
			}

			if( !obj.imagenes ||  (obj.imagenes).length == 0 )
			{
				$("#modal_detalle div.imagenes").hide();
			}

			$("#modal_detalle #imagenes_cargar").empty();
			$(obj.imagenes).each(function(i,e){
				$("#modal_detalle #imagenes_cargar").append(
					$("<img class='imagenes_modal'>").attr("src","files_bucket/solicitud_mantenimiento/"+e.archivo)
				)
			})
			$(".imagenes_modal").off("click").on("click",function(){
				var src = $(this).attr("src");
				$("#vista_previa_modal #img01").attr("src",src);
				$("#vista_previa_modal").modal("show");
			})
			$("#modal_detalle #estado").off("change").on("change",function(){
				if($(this).val() == 4 ){
					$(".foto_terminado").show();
					$(".comentario_observado").hide();
					$("#div_servicio_tecnico_derivacion").hide();
                    $(".div_comentario_terminado").show();
					$(".div_comentario_observado").hide();
					$(".der_comentario_para_tecnico").hide();
				}
				if($(this).val() == 3 ){
					$(".comentario_observado").show();
					$(".foto_terminado").hide();
                    $(".div_comentario_terminado").hide();
					$(".div_comentario_observado").show();
					$(".der_comentario_para_tecnico").hide();
				}
				if($(this).val() == 2 ){
					$(".comentario_observado").hide();
					$(".foto_terminado").hide();
                    $(".div_comentario_terminado").hide();
					$("#div_servicio_tecnico_derivacion").show();
					$(".der_comentario_para_tecnico").show();
					$(".div_comentario_observado").hide();

				}
				if($(this).val() == 1 ){
					$(".comentario_observado").hide();
					$(".foto_terminado").hide();
                    $(".div_comentario_terminado").hide();
					$("#div_servicio_tecnico_derivacion").hide();
				}
			})
			$("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal",function(){
				$("#vista_previa_modal #img01").imgViewer2();
			})
			$("#vista_previa_modal").off("hidden.bs.modal").on("hidden.bs.modal",function(){
				$("#vista_previa_modal #img01").imgViewer2("destroy");
			})
			$("#modal_detalle #estado").val(obj.local["estado"]).change();
			$("#der_nota_para_tecnico").val(obj.local["nota_tecnico"]);
			$("#der_comentario_para_tecnico").val(obj.local["comentario"]);
			$("#modal_detalle").modal("show");

		},
		complete : function (){
			loading();
		},
        error: function () {
			loading();
       	}
	});
}

function sec_servicio_tecnico_derivacion_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_servicio_tecnico_derivacion_update","set_servicio_tecnico_derivacion_update");
	dataForm.append("estado_vt", $("#estado option:selected" ,$(form)).text());	

	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	result.estado_vt = $("#estado option:selected" ,$(form)).text(); 
	var set_data = {};
	if(result.foto_terminado_update.name != ""){
		result.foto_terminado_update = result.foto_terminado_update.name; 
	}
	else{
		result.foto_terminado_update = "";
	}
	set_data = result;

	$.ajax({
		url: 'sys/set_servicio_tecnico_derivacion.php',
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_servicio_tecnico_derivacion_update_error","data":set_data});
				loading(false);
				swal({
					title: "¡Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
			}else{
				loading(false);
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_servicio_tecnico_derivacion_update_done","data":set_data});	
				console.log(set_data);
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				$("#modal_detalle").modal("hide");
				window.location.reload();
			}
		}
	});
}
