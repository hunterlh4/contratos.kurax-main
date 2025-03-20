function sec_reportes_solicitudes() {
	files=[];
	item_config = {};
	sec_reportes_solicitudes_events();
	sec_reportes_solicitudes_settings();	
}

function sec_reportes_solicitudes_settings(){
	$(".select2").select2({
		closeOnSelect: true,
		width:"100%"
	});
	$('.estado_solicitud').select2({closeOnSelect: false});
	$(".sec_reportes_solicitudes_fecha_datepicker")
	.datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	})
	.on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		
	});
}

function sec_reportes_solicitudes_events(){	

	$(document).off().on("click", ".reportes_ver_solicitud_modal_btn", function(event) {		
		var solicitud_id=$(this).attr('data-id');
		var bet_id=$(this).attr('data-bet_id');
		var estado=$(this).attr('data-estado');
		if(bet_id==""){bet_id=0;}		
		reportes_ver_solicitud_modal("show",solicitud_id,bet_id,estado);
	});	

	$(".guardar_abono_class").off().on("click",function(ev){	
		sec_reportes_solicitudes_guardar();
	});

	$(".solicitud_archivo").on("change",function(ev){				
		file_data = $(this).prop("files")[0];
		existe=files.filter(x=>x.name==file_data.name);
		if(existe.length==0){
			files.push(file_data);		
			strArchivos="";		
			strArchivos='<tr><td style="border-bottom:none !important;width: 10px !important;"><button data-nombre="'+$(this).prop("files")[0].name+'" class="btn btn-danger btn-xs btn_eliminar_archivo_solicitud">x</button></td><td style="border-bottom:none !important; cursor: pointer;">'+$(this).prop("files")[0].name+'</td></tr>';
			$("#tbodyArchivos").append(strArchivos);
		}else{
			swal({
				title: "Error!",
				text: "Este Archivo ya se encuentra cargado.",
				type: "warning",
				timer: 3000,
				closeOnConfirm: true
			});
		}
		$("#var_archivo_solicitud").val("");
	});	
	$(document).on("click", ".btn_eliminar_archivo_solicitud", function(event) {		
		files=files.filter(x=>x.name!=$(this).attr("data-nombre"));
		var tr=$(this).closest("tr");
		tr.remove();
	});	
	
	$(".consultar_sol_class")
	.off()
	.click(function(event) {
		sec_reportes_solicitudes_get_solicitudes();
	});

	$("[id='btnExportsolicitudes']").off().on('click', function(event) {		
		event.preventDefault();
		loading(true);

		$(".item_config").each(function(index, el) {		
			var config_index = $(el).attr("name");
			var config_val = $(el).val();
			var ls_index = "sec_"+sec_id+"_"+sub_sec_id+"_"+config_index;
			localStorage.setItem(ls_index,config_val);
			item_config[config_index]=config_val;
		});			
		var get_data = jQuery.extend({}, item_config);
		if($("#estado_solicitud").val()!=null){		
			var estados_array=$("#estado_solicitud").val();
			get_data.estados= estados_array.join();
		}else{
			get_data.estados= 'all';
		}		
		var get_data ={
			"opt": 'sec_locales_reporte_get_solicitudes_export',
			"data":get_data
		};

		$.ajax({
			url: '/export/local_solicitudes.php',
			type: 'POST',
			data: get_data,
		})
		.done(function(dataresponse) {			
			console.log(dataresponse);
			var obj = JSON.parse(dataresponse);
			window.open(obj.path);
		})
		.always(function(data){
			loading();
		});
	});

	$("[id='btn_reporte_imprimir_detalle_solicitud']").off().on('click', function(event) {
		event.preventDefault();
		//loading(true);
						
		solicitud_id=$("#btn_reporte_imprimir_detalle_solicitud").data('id');		
		monto_ticket=$("#btn_reporte_imprimir_detalle_solicitud").data('monto_ticket');				
		var get_data ={
			"opt": 'sec_locales_get_solicitud_detalle_export',
			"data":solicitud_id,
			"monto_ticket":monto_ticket,
		};

		$.ajax({
			url: '/export/local_solicitudes.php',
			type: 'POST',
			data: get_data,
		})
		.done(function(dataresponse) {			
			console.log(dataresponse);
			var obj = JSON.parse(dataresponse);
			window.open(obj.path);
		})
		.always(function(data){
			loading();
		});
	});	

}

function reportes_ver_solicitud_modal(opt,solicitud_id,bet_id,estado){	
	loading(true);	
	$("#reportes_ver_solicitud_modal").modal(opt);	
	if(opt=="show"){
		$("#div_abonado").hide();		
		var data =solicitud_id;
		var bet_id =bet_id;
		var estado =estado;		
		loading(true);			
		$.post('sys/get_local_solicitud.php', {"solicitud_id":data,"bet_id":bet_id,"estado":estado}, function(r, textStatus, xhr) {																					
			var response = jQuery.parseJSON(r);											

			$(".guardar_abono_class").attr('data-id_solicitud',response[0].id);								
			$("#btn_reporte_imprimir_detalle_solicitud").data('id',response[0].id);
			$("#desc_solicitud_motivo").text(response[0].motivo);
			$("#desc_solicitud_monto").text("S/ "+response[0].monto);
			$("#desc_solicitud_bet_id").text(response[0].bet_id);
			if(response[0].transaccion.length>0){
				$("#desc_solicitud_bet_id_monto").text("S/ "+response[0].transaccion[0].ganado);
				$("#btn_reporte_imprimir_detalle_solicitud").data('monto_ticket',"S/ "+response[0].transaccion[0].ganado);
			}				
			$("#desc_solicitud_usuario").text(response[0].usuario);
			$("#desc_solicitud_nombre").text(response[0].nombre);
			$("#desc_solicitud_ap_paterno").text(response[0].apellido_paterno);
			$("#desc_solicitud_area").text(response[0].area);
			$("#desc_solicitud_cargo").text(response[0].cargo);
			$("#desc_solicitud_tipo_sol").text(response[0].tipo_solicitud_desc);
			$("#desc_solicitud_subtipo_sol").text(response[0].subtipo_solicitud_desc);
			$("#desc_solicitud_tipo_subtipo_sol").text( response[0].tipo_solicitud_desc +" / "+response[0].subtipo_solicitud_desc);						
			$("#desc_solicitud_area_cargo").text( response[0].area +" / "+response[0].cargo);									
			$("#desc_solicitud_fecha_creacion").text(response[0].fecha_creacion);
			var estado="";
			if(response[0].estado==0){estado="Pendiente"};
			if(response[0].estado==1){estado="Aprobado"};
			if(response[0].estado==2){estado="Abonado"};
			if(response[0].estado==3){estado="Cancelado"};
			if(response[0].estado==4){estado="Expirado"};
			if(response[0].estado==5){estado="Recibido"};
			if(response[0].estado==6){estado="Abonado-Eliminacion-Turno"};
			$("#desc_solicitud_estado").text(estado);
			if(response[0].tipo_solicitud==1){
				$(".tr_monto").show();							
			}else{
				$(".tr_monto").hide();			
			};	
			if(response[0].subtipo_solicitud==2){
				$(".tr_bet_id").show();			
			}else{
				$(".tr_bet_id").hide();							
			}
			if(response[0].cobrado==true){
				$("#div_expirado").show();
				$("#desc_ticket").text(response[0].transaccion[0].ticket_id);
				$("#desc_fecha_pago").text(response[0].transaccion[0].paid_day);
				$("#desc_local_pago").text(response[0].transaccion[0].local_pago);
				$("#desc_monto_pago").text(response[0].transaccion[0].pagado);
				$(".abonar_sol_class").hide();
			}else{				
				$("#div_expirado").hide();
				$("#desc_ticket").text("");
				$("#desc_fecha_pago").text("");
				$("#desc_local_pago").text("");
				$("#desc_monto_pago").text("");					
				if(response[0].estado==1){					
					$(".abonar_sol_class").show();				
				}else{
					$(".abonar_sol_class").hide();			
				}
			}
			$("#tbodyArchivos").html("");
			if(response[0].archivos.length>0){
				strArchivos="";
				$.each(response[0].archivos, function( index, value ) {
					strArchivos+=
					'<tr><td style="border-bottom:none !important; cursor: pointer;"><a href="./files_bucket/solicitudes/'+value.archivo+'" target="_blank">'+value.archivo+'</a></td></tr>';
				});
				$("#tbodyArchivos").append(strArchivos);			
			}
			if(response[0].estado==2){										
				$("#varchar_transaccion_solicitud").val(response[0].numero_transaccion);
				$("#varchar_transaccion_solicitud").attr('readonly',true);
				$("#var_archivo_solicitud").attr('disabled',true);
				$(".guardar_abono_class").hide();
				$("#div_abonado").show(200);
			}
			else{
				$("#varchar_transaccion_solicitud").val("");
				$("#varchar_transaccion_solicitud").attr('readonly',false);
				$("#var_archivo_solicitud").attr('disabled',false);
				$(".guardar_abono_class").show();
			}
			auditoria_send({"proceso":"reportes_solicitud_ver_solicitud","data":{"solicitud_id":data,"bet_id":bet_id,"estado":estado} });
		});			
		loading(false);		
		$("#reportes_ver_solicitud_modal .ver_reporte_solicitud_cerrar_btn")
		.off()
		.click(function(event) {			
			$(".guardar_abono_class").attr('data-id_solicitud',0);
			$("#btn_reporte_imprimir_detalle_solicitud").data('id',0);	
			$("#btn_reporte_imprimir_detalle_solicitud").data('monto_ticket',0);
			reportes_ver_solicitud_modal("hide");
		});	
		$("#reportes_ver_solicitud_modal .abonar_sol_class")
		.off()
		.click(function(event) {
			$("#div_abonado").show(200);			
		});						
	}else{
		$("#desc_solicitud_tipo_subtipo_sol").text("");
		$("#desc_solicitud_fecha_creacion").text("");
		$("#desc_solicitud_estado").text("");
		$("#desc_solicitud_usuario").text("");
		$("#desc_solicitud_area_cargo").text("");
		$("#desc_solicitud_monto").text("");
		$("#desc_solicitud_motivo").text("");
		$("#desc_solicitud_bet_id").text("");
		$("#desc_solicitud_bet_id_monto").text("");
	}
	loading(false);
}

function sec_reportes_solicitudes_guardar(){
	loading(true);
	campos=[];						
	var form_data = new FormData();
	form_data.append("tabla",$("#var_archivo_solicitud").attr("data-tabla"));
	form_data.append("id_solicitud",  $(".guardar_abono_class").attr('data-id_solicitud'));
	form_data.append("transaccion",  $('#varchar_transaccion_solicitud').val());
	form_data.append("opt", "reporte_solicitudes_abono_guardar");	
	$.each(files, function( index, value ) {
		form_data.append("file"+index, value);
	});
	if(files.length!=0 && $('#varchar_transaccion_solicitud').val()!=""){
		$.ajax({
			url: '/sys/set_solicitud_prestamo.php',
			type: 'POST',
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
		})
		.done(function(r) {												
			var response = jQuery.parseJSON(r);							
			var object_auditoria = {};			
			for(let pair of form_data.entries()) {				
				if(pair[0].includes("file")){
					object_auditoria[pair[0]] = pair[1].name;
				}else{
					object_auditoria[pair[0]] = pair[1];
				} 
			}						
			if(response.response==true){				
				auditoria_send({"proceso":"locales_guardar_abonar_solicitud_done","data":object_auditoria});
				swal({
					title: "Guardado!",
					text: "Datos Guardados.",
					type: "success",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					m_reload();
					swal.close();
				});
			}
			else{
				auditoria_send({"proceso":"locales_guardar_abonar_solicitud_error","data":object_auditoria});
				swal({
					title: "Error!",
					text: "Error al Guardar.",
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				});
			}
			loading(false);
		});
	}
	else{		
		if(files.length==0){
			swal({
				title: "Error!",
				text: "Seleccione archivo.",
				type: "warning",
				timer: 3000,
				closeOnConfirm: true
			});
		}else{
			swal({
				title: "Error!",
				text: "Ingrese Nro de Transaccion.",
				type: "warning",
				timer: 3000,
				closeOnConfirm: true
			});
		}	
		loading(false);
	}
}

function sec_reportes_solicitudes_get_solicitudes(){
	loading(true);	
	$(".item_config").each(function(index, el) {		
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_"+sec_id+"_"+sub_sec_id+"_"+config_index;
		localStorage.setItem(ls_index,config_val);
		item_config[config_index]=config_val;
	});		
	var get_data = jQuery.extend({}, item_config);
	get_data.sub_sec_id= $("#sub_sec_id").val();
	if($("#estado_solicitud").val()!=null){		
		var estados_array=$("#estado_solicitud").val();
		get_data.estados= estados_array.join();
	}else{
		get_data.estados= 'all';
	}
	$.post('/sys/get_reportes_solicitudes.php', {
		"sec_reportes_solicitudes_get_solicitudes": get_data
	}, function(r) {			
		if(r.includes("table")){
			$("#btnExportsolicitudes").show();
		}else{
			$("#btnExportsolicitudes").hide();
		}
		$(".table_container").html(r);
		auditoria_send({"proceso":"reportes_solicitud_consultar_solicitudes","data":get_data});
		loading(false);		
	});
}
