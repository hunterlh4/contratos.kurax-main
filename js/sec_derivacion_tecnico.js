function sec_derivacion_tecnico(){
	if(sec_id=="derivacion_tecnico"  ||  sec_id=="derivacion_tecnico_form") {
		console.log("sec:derivacion_tecnico");
		sec_derivacion_tecnico_events();
	}
}
function sec_derivacion_tecnico_events(){
	$(".select2")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width:"100%"
	});
    
    $("#btn_actualizar_tbl").off("click").on("click",function(){
    	tablaserver.ajax.reload(null, false);
    });

	$("#btn_ubicacion").click();

    sec_derivacion_tecnico_modal_detalle_events();
    $(".sec_derivacion_tecnico_solicitud_detalle").off("click").on("click",function(){
        var solicitud_id = $(this).attr("data-solicitud_id");
        sec_derivacion_tecnico_cargar_solicitud(solicitud_id);
    })
}

function sec_derivacion_tecnico_modal_detalle_events(){
	$("#modal_detalle #sec_derivacion_tecnico_guardar_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
        sec_derivacion_tecnico_update(form);
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

function sec_derivacion_tecnico_cargar_solicitud(solicitud_id){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_derivacion_tecnico_cargar_solicitud = "sec_derivacion_tecnico_cargar_solicitud";
	$.ajax({
		url: 'sys/set_derivacion_tecnico.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			modal_detalle_events();
			$.each(obj.local,function(i,e){
				if($("#modal_detalle #"+i).length > 0 ){
					if($("#modal_detalle #"+i)[0].nodeName == "P" || $("#modal_detalle #"+i)[0].nodeName == "DIV")
					{
						$("#modal_detalle #"+i).text(e);
					}
					else{
						$("#modal_detalle #"+i).val(e);
					}
				}
			})
			if(obj.local["estado"]=="Terminado"){
				if(obj.local["foto_terminado"] != null)
				{
					$("#modal_detalle #foto_terminado").attr("src","files_bucket/solicitud_mantenimiento/"+obj.local["foto_terminado"]);
				}
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

			$("#div-tipo-mant-modal").show();
			$("#div-sistema-modal").show();

			$("#modal_detalle #estado").off("change").on("change",function(){
				if($(this).val() == "Terminado"){
					$(".foto_terminado").show();
					$(".comentario").hide();
					$("#div_derivacion_tecnico").hide();
                    $(".div_comentario_terminado").show();
				}
				if($(this).val() == "Programado"){
					$(".comentario").show();
					$(".foto_terminado").hide();
                    $(".div_comentario_terminado").hide();
					$("#div_derivacion_tecnico").show();

				}
				if($(this).val() == "Solicitud"){
					$(".comentario").hide();
					$(".foto_terminado").hide();
                    $(".div_comentario_terminado").hide();
					$("#div_derivacion_tecnico").hide();
				}
			})
			$("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal",function(){
				$("#vista_previa_modal #img01").imgViewer2();
			})
			$("#vista_previa_modal").off("hidden.bs.modal").on("hidden.bs.modal",function(){
				$("#vista_previa_modal #img01").imgViewer2("destroy");
			})
			$("#modal_detalle #estado").val(obj.local["estado"]).change();
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

function sec_derivacion_tecnico_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_derivacion_tecnico_update","set_derivacion_tecnico_update");

	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	var set_data = {};
	if(result.foto_terminado_update.name != ""){
		result.foto_terminado_update = result.foto_terminado_update.name; 
	}
	else{
		result.foto_terminado_update = "";
	}
	set_data = result;

	$.ajax({
		url: 'sys/set_derivacion_tecnico.php',
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
				auditoria_send({"proceso":"sec_derivacion_tecnico_update_error","data":set_data});
				loading(false);
				swal({
					title: "Error!",
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
				auditoria_send({"proceso":"sec_derivacion_tecnico_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				},function(){
					swal.close();
					window.location.reload();
					$("#modal_detalle").modal("hide");
				});
			}
		}
	});
}
