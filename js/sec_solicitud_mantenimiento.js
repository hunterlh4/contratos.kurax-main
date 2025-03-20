function sec_solicitud_mantenimiento(){
	if(sec_id=="solicitud_mantenimiento"  ||  sec_id=="solicitud_mantenimiento_form") {
		console.log("sec:solicitud_mantenimiento");
		sec_solicitud_mantenimiento_events();
	}
}


function sec_solicitud_mantenimiento_events(){
	$(".save_btn")
	.off()
	.click(function(event) {
		var btn = $(this);
		sec_solicitud_mantenimiento_save(btn);
	});

	$("#btn_ubicacion")
	.off()
	.click(function(event) {
		event.preventDefault();
		sec_solicitud_mantenimiento_ubicacion();
	});

	$(".select2")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width:"100%"
	});

    tablaserver = listar_solicitudes_mantenimiento();

    $("#btn_actualizar_tbl").off("click").on("click",function(){
    	tablaserver.ajax.reload(null, false);
    });

	$("#zona_id").on("change", function() {
		let zona_id = $(this).find(':selected').attr("value");
		sec_solicitud_mantenimiento_cargar_locales(zona_id);
	})


	// FECHA INICIO
	$(".filtro_datepicker_inicio")
	  .datepicker({
		dateFormat:'dd-mm-yy',
		// maxDate: 0,
		changeMonth: true,
		changeYear: true
	  })
	  .on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$(this).attr("data-fecha_formateada",$.format.date(newDate, "yyyy-MM-dd"));
		//tablaserver.ajax.reload();
	  });

	// FECHA FIN
	$(".filtro_datepicker_fin")
	  .datepicker({
		dateFormat:'dd-mm-yy',
		// minDate: 0,
		changeMonth: true,
		changeYear: true
	  })
	  .on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$(this).attr("data-fecha_formateada",$.format.date(newDate, "yyyy-MM-dd"));
		//tablaserver.ajax.reload();
	  });

  /* $("input[name='imagen[]']").on('change', function(event) {
        var files = event.target.files;
        for(i=0; i<files.length; i++){
            var image = files[i]
            var reader = new FileReader();
            reader.onload = function(file) {
              var img = new Image();
              img.width = 150;
              //img.height = 150;
              img.src = file.target.result;
              $('#previews').append(img);
              }
            reader.readAsDataURL(image);
         };
    });*/

	$("#zona_id").val($("#zona_id option:first").val()).change();
	$("#btn_ubicacion").click();
}

function sec_solicitud_mantenimiento_modal_detalle_derivar_a_servicio_tecnico()
{
	let id_servicio_mantenimiento = $("#id").val();

	if(id_servicio_mantenimiento == 0 || id_servicio_mantenimiento == "")
    {
        alertify.error('No se encontro el ID de la solicitud',5);
        alertify.error('Por favor refrescar la página',5);
        return false;
    }

    swal(
	{
		title: '¿Está seguro de Derivar?',
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
			var data = {
		        "accion": "sec_solicitud_mantenimiento_modal_detalle_derivar_a_servicio_tecnico",
		        "id_servicio_mantenimiento": id_servicio_mantenimiento
		    }

			$.ajax({
		        url: "/sys/set_solicitud_mantenimiento.php",
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
		            auditoria_send({ "respuesta": "sec_solicitud_mantenimiento_modal_detalle_derivar_a_servicio_tecnico", "data": respuesta });
		            
		            if (parseInt(respuesta.http_code) == 400)
		            {
		                swal({
							title: "¡Error!",
							text: respuesta.status,
							type: "warning",
							timer: 3000,
							closeOnConfirm: true
						},
						function(){
							swal.close();
						});
		            }
		            if (parseInt(respuesta.http_code) == 200)
		            {   
		            	swal({
							title: "Registro Exitoso",
							text: respuesta.status,
							type: "success",
							timer: 3000,
							closeOnConfirm: false,
							showCancelButton: false,
							showConfirmButton: true
						});
						tablaserver.ajax.reload();
						$("#modal_detalle").modal("hide");
		            }
		        },
		        error: function() {}
		    });
		}
	});
}

function sec_solicitud_mantenimiento_modal_detalle_events(){
	$("#modal_detalle #sec_solicitud_mantenimiento_guardar_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
		var tipo_b = $('#sec_sol_mant_tipo').val();
		if (tipo_b == 1){
			console.log("entro")
			sec_solicitud_mantenimiento_update(form);
		}else{
			console.log("entro2")
			sec_solicitud_mantenimiento_update_vt(form);
		}

		
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
///////guardar sec_solicitud_mantenimiento_cargar_locales
function sec_solicitud_mantenimiento_cargar_locales(zona_id){
	loading(true);
    var set_data = {};
    set_data.zona_id = zona_id ;
	set_data.sec_solicitud_mantenimiento_cargar_locales = "sec_solicitud_mantenimiento_cargar_locales";

	$.ajax({
		url: 'sys/set_solicitud_mantenimiento.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			$('#local_id_sm').empty();
			$(obj.locales).each(function(i,e){
				$('#local_id_sm').append($('<option>', { 
					value: e.id,
					text : e.nombre 
    			}));
			})
			$('#local_id_sm').select2();
			loading();

		}
	});
}
///////guardar sec_solicitud_mantenimiento_save
function sec_solicitud_mantenimiento_save(btn){
	loading(true);
    var dataForm = new FormData($(btn).closest("form")[0]);
	dataForm.append("sec_solicitud_mantenimiento_save","sec_solicitud_mantenimiento_save");

	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	result["imagen[]"]=result["imagen[]"].name;
	var set_data = {};
	set_data = result;

	$.ajax({
		url: 'sys/set_solicitud_mantenimiento.php',
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		success: function(r){
			console.log(r);
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_solicitud_mantenimiento_save_error","data":set_data});				
				loading(false);
				swal({
					title: "Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
					custom_highlight($("[name='"+obj.error_focus+"']"));
					setTimeout(function() {
						$("[name='"+obj.error_focus+"']").focus();
						},500)
				});
			}else{
				loading(false);
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_solicitud_mantenimiento_save_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					window.location.reload();
				});
			}
		}
	});
}


function sec_solicitud_mantenimiento_update_vt(form){
    var dataForm = new FormData(form);
	dataForm.append("set_solicitud_mantenimiento_update_vt","set_solicitud_mantenimiento_update_vt");

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
	loading(true);
	$.ajax({
		url: 'sys/set_solicitud_mantenimiento.php',
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		success: function(r){
			loading(false);
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_vt_error","data":set_data});
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
				auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_vt_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				tablaserver.ajax.reload();
				$("#modal_detalle").modal("hide");
			}
		}
	});
}


function sec_solicitud_mantenimiento_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_solicitud_mantenimiento_update","set_solicitud_mantenimiento_update");

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
		url: 'sys/set_solicitud_mantenimiento.php',
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
				auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_error","data":set_data});
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
				auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				tablaserver.ajax.reload();
				$("#modal_detalle").modal("hide");
			}
		}
	});
}

function limpiar_tabla_mantenimiento_vt() {
	$("#tbl_solicitud_derivado").html(
	  "<thead>" +
		"   <tr>" +

		'		<th class="text-center">Id</th>' +		                    
		'		<th class="text-center">Fecha Ingreso</th>' + 
		'		<th class="text-center">Zona</th>' +
		'		<th class="text-center">Tienda</th>' + 
		'		<th class="text-center">Descrip. Incidente</th>' +
		'		<th class="text-center">Equipo</th>' +	
		'		<th class="text-center">Recomendación</th>' +
		'		<th class="text-center">Nota para el técnico</th>' +		
		'		<th class="text-center">Estado</th>' +		                    
		'		<th class="text-center">Fecha Cierre</th>' +		
		'		<th class="text-center">Detalle</th>' + 
		"   </tr>" +
		"</thead>" +
		"<tbody>"
	);
	 
  }

function listar_solicitudes_mantenimiento_vt(){
 
	limpiar_tabla_mantenimiento_vt()
 
	var sec_sol_mt_estado = $.trim($("#estado_select").val());
	var sec_sol_mt_zona = $.trim($("#zona_select").val());
	var sec_sol_mt_tienda = $.trim($("#local_select").val());
	var sec_sol_mt_fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
	var sec_sol_mt_fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");
  
	var data = {
		"accion": "listar_derivaciones_vt",
		"estado": sec_sol_mt_estado,
		"zona": sec_sol_mt_zona ,
		"tienda": sec_sol_mt_tienda,
		"fecha_inicio": sec_sol_mt_fecha_inicio,
        "fecha_fin": sec_sol_mt_fecha_fin 
	  }
  
	auditoria_send({ proceso: "listar_derivaciones_vt", data: data });
	$.ajax({
	  url: "/sys/set_solicitud_mantenimiento.php",
	  type: "POST",
	  data: data,
	  beforeSend: function () {
		loading("true");
	  },
	  complete: function () {
		loading();
	  },
	  success: function (resp) {
		//  alert(datat)
		var respuesta = JSON.parse(resp);
		//console.log(respuesta);
		if (parseInt(respuesta.http_code) == 400) {
		  return false;
		}
		if (parseInt(respuesta.http_code) == 200) {
		  
		  if (respuesta.result.length > 0) {
			$.each(respuesta.result, function (index, item) {
			  
				if(!item.zona){
					item.zona="-";
				}

				if(!item.local){
					item.local="-";
				}
			  
				if(!item.incidencia_txt){
					item.incidencia_txt="-";
				}

				if(!item.estado_vt){
					item.estado_vt="Derivado";
				}
	  
				if(!item.fecha_cierre_vt){
					item.fecha_cierre_vt="-";
				   }
			 
			 
			  $("#tbl_solicitud_derivado").append(
				"<tr>" +
				  '<td class="text-center">' +
				  (index + 1) +
				  "</td>" +
				  '<td class="text-center">' +
				  item.created_at +
				  "</td>" +
				  '<td class="text-center">' +
				  item.zona +
				  "</td>" +
				  '<td class="text-center">' +
				  item.local +
				  "</td>" +				  
				  '<td class="text-center">' +
				  item.incidencia_txt +
				  "</td>" +
				  '<td class="text-center">' +
				  item.equipo +
				  "</td>" +
				  '<td class="text-center">' +
				  item.recomendacion +
				  "</td>" +	
				  '<td class="text-center">' +
				  item.nota_tecnico +
				  "</td>" +					  
				  '<td class="text-center">' +
				  item.estado_vt +
				  "</td>" +
				  '<td class="text-center">' +
				  item.fecha_cierre_vt +
				  "</td>" +				  
				  '<td class="text-center"><button type="button" class="btn btn-rounded btn-primary btn-sm ver_detalle" title="Ver detalle" onclick="sec_solicitud_mantenimiento_cargar_solicitud_vt(' +
				  item.id  +
				  ')"><i class="fa fa-eye"></i> Ver</button>' +
				  "</td>" +			   
				  "</tr>"
			  );
			});
			DATATABLE_FORMATO_tabla_derivados("#tbl_solicitud_derivado");
		  } else {
			$("#tbl_solicitud_derivado").append("<tr>" + '<td class="text-center" colspan="8">No hay transacciones.</td>' + "</tr>");
		  }
		  
		  //console.log(array_clientes);
		  return false;
		}
	  },
	  error: function () {},
	});
	 
}


function DATATABLE_FORMATO_tabla_derivados(id) {
	if ($.fn.dataTable.isDataTable(id)) {
	  $(id).DataTable().destroy();
	}
	$(id).DataTable({
	  paging: true,
	  lengthChange: true,
	  searching: true,
	  ordering: true,
	  order: [[0, "asc"]],
	  info: true,
	  autoWidth: false,
	  language: {
		processing: "Procesando...",
		lengthMenu: "Mostrar _MENU_ registros",
		zeroRecords: "No se encontraron resultados",
		emptyTable: "Ningún dato disponible en esta tabla",
		info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
		infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
		infoFiltered: "(filtrado de un total de _MAX_ registros)",
		infoPostFix: "",
		search: "Buscar: ",
		url: "",
		infoThousands: ",",
		loadingRecords: "Cargando...",
		paginate: {
		  first: "Primero",
		  last: "Último",
		  next: "Siguiente",
		  previous: "Anterior",
		},
		aria: {
		  sortAscending: ": Activar para ordenar la columna de manera ascendente",
		  sortDescending: ": Activar para ordenar la columna de manera descendente",
		},
	  },
	});
  }


function listar_solicitudes_mantenimiento(){
	tablaserver = $("#tbl_solicitud_mantenimiento")
						.on('order.dt', function () {
                            $('table').css('width', '100%');
                            $('.dataTables_scrollHeadInner').css('width', '100%');
                            $('.dataTables_scrollFootInner').css('width', '100%');
                        })
                        .on('search.dt', function () {
                                $('table').css('width', '100%');
                                $('.dataTables_scrollHeadInner').css('width', '100%');
                                $('.dataTables_scrollFootInner').css('width', '100%');
                            })
                        .on('page.dt', function () {
                                $('table').css('width', '100%');
                                $('.dataTables_scrollHeadInner').css('width', '100%');
                                $('.dataTables_scrollFootInner').css('width', '100%');
                            })
			.DataTable({
                "paging": true,
                "scrollX": true,
                "sScrollX": "100%",
                "bProcessing": true,
                'processing': false,
                "language": {
                    "search": "Buscar:",
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron registros",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "No hay registros",
                    "infoFiltered": "(filtrado de _MAX_ total records)",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    sProcessing: "Procesando..."
                },

                "bDeferRender": false,
                "autoWidth": true,
                 pageResize:true,
                "bAutoWidth": true,
                "pageLength": 10,
                serverSide: true,
                "bDestroy": true,
                colReorder: true,
                "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
                "order": [[ 1, "desc" ]],
			    buttons: [
			        {
			            text: '<span class="glyphicon glyphicon-refresh"></span>',
			            action: function ( e, dt, node, config ) {
			                tablaserver.ajax.reload(null,false);
			            }
			        }
			    ],
                ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
                	datat.action = typeof action=="undefined"?"sec_solicitud_mantenimiento_dias_list":action; //"sec_solicitud_mantenimiento_list";

                	datat.fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
                	datat.fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");

                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/set_solicitud_mantenimiento.php",
                        type: 'POST',
                        data: datat,
                        beforeSend: function () {
                        	tablaserver.columns.adjust();
                        },
                        complete: function () {
                        	tablaserver.columns.adjust();
                            //responsive_tabla_scroll(tablaserver);
                        },
                        success: function (datos) {//  alert(datat)
                            //aaaa = datos;
                            var respuesta = JSON.parse(datos);
                            if(datat.action == "sec_solicitud_mantenimiento_list_dias_excel"){
                            	$(".dataTables_processing").hide();
                            	window.open(respuesta.path);
                            	return;
                            }

							var cantidad = respuesta.aaData.length;
                            for(var i = 0; i < cantidad; i++){
                                if(respuesta.aaData[i].estado == "Terminado"){
                                    respuesta.aaData[i].total_dias = "-";
                                }else{
                                    function calcular_sabados_domingos(fechaInicio, fechaFinal) {
										if (fechaInicio > fechaFinal) {
											return fechaFinal;
										}
										
										const diaActual = fechaInicio.getDay();
										
										if (diaActual === 0 || diaActual === 6) { // Si es domingo o sábado
											fechaFinal.setDate(fechaFinal.getDate() + 1);
										}
										
										fechaInicio.setDate(fechaInicio.getDate() + 1);
										
										return calcular_sabados_domingos(fechaInicio, fechaFinal);
									} 

                                    function calcular_diferencia_dias(fecha1, fecha2) {
                                        const unDia = 1000 * 60 * 60 * 24; // 1 día en milisegundos
                                        const diferenciaEnMilisegundos = fecha2 - fecha1;
                                        const diferenciaEnDias = Math.floor(diferenciaEnMilisegundos / unDia);
                                        return diferenciaEnDias;
                                    }

                                    // FECHA INICIO
                                    var fecha_inicio = respuesta.aaData[i].created_at;
                                    var date_fecha_inicio = new Date(fecha_inicio);
                                    var anio_inicio = date_fecha_inicio.getFullYear();
                                    var mes_inicio = String(date_fecha_inicio.getMonth() + 1).padStart(2, '0'); // Sumamos 1 al mes, ya que los meses en JavaScript van de 0 a 11
                                    var dia_inicio = String(date_fecha_inicio.getDate()).padStart(2, '0');
                                    var solo_fecha_inicio = `${anio_inicio}-${mes_inicio}-${dia_inicio}`;
                                    var date_solo_fecha_inicio = new Date(solo_fecha_inicio + "T00:00:00")
                                    
                                    // VALORES PARA LA FORMULA
                                    var t_revision = respuesta.aaData[i].t_revision;
                                    var t_coordinacion = respuesta.aaData[i].t_coordinacion;
                                    var valor_v = respuesta.aaData[i].valor_v;
                                    var valor_e = respuesta.aaData[i].valor_e;

                                    // VERIFICAR QUE TODOS LOS VALORES NO ESTEN NULOS
                                    if(t_revision != null && t_coordinacion != null && valor_v != null && valor_e != null){
                                        respuesta.aaData[i].total_dias = parseFloat(t_revision) + parseFloat(t_coordinacion) + parseFloat(valor_v) + parseFloat(valor_e);
                                        var redondeo = parseFloat(respuesta.aaData[i].total_dias).toFixed(2);
                                        respuesta.aaData[i].total_dias = redondeo;
                                    }else{
                                        respuesta.aaData[i].total_dias = 0;
                                    }

                                    var fecha_fin = date_fecha_inicio.setDate(date_fecha_inicio.getDate() + Math.floor(respuesta.aaData[i].total_dias));
                                    // console.log(date_fecha_inicio.getDate())
                                    // console.log(Math.floor(respuesta.aaData[i].total_dias))
                                    var date_fecha_fin = new Date(fecha_fin);
                                    var anio_fin = date_fecha_fin.getFullYear();
                                    var mes_fin = String(date_fecha_fin.getMonth() + 1).padStart(2, "0"); // Sumamos 1 al mes ya que en JavaScript los meses comienzan en 0
                                    var dia_fin = String(date_fecha_fin.getDate()).padStart(2, "0");
                                    var solo_fecha_fin = `${anio_fin}-${mes_fin}-${dia_fin}`;
                                    var date_solo_fecha_fin = new Date(solo_fecha_fin + "T00:00:00")
                                    
                                    // FECHA ACTUAL
                                    var fecha_actual = new Date();
                                    var date_fecha_actual = new Date(fecha_actual);
									var anio_actual = date_fecha_actual.getFullYear();
									var mes_actual = String(date_fecha_actual.getMonth() + 1).padStart(2, '0'); // Sumamos 1 al mes, ya que los meses en JavaScript van de 0 a 11
									var dia_actual = String(date_fecha_actual.getDate()).padStart(2, '0');
									var solo_fecha_actual = `${anio_actual}-${mes_actual}-${dia_actual}`;

                                    var fecha_final = calcular_sabados_domingos(date_solo_fecha_inicio, date_solo_fecha_fin);

                                    var date_fecha_final = new Date(fecha_final)
                                    var anio_final = date_fecha_final.getFullYear();
                                    var mes_final = String(date_fecha_final.getMonth() + 1).padStart(2, '0');
                                    var dia_final = String(date_fecha_final.getDate()).padStart(2, '0');
                                    var solo_fecha_final = `${anio_final}-${mes_final}-${dia_final}`;

                                    var fecha1 = new Date(solo_fecha_actual);
                                    var fecha2 = new Date(solo_fecha_final);

                                    var diferencia_Dias = calcular_diferencia_dias(fecha1, fecha2);

                                    if(t_revision == null || t_coordinacion == null || valor_v == null || valor_e == null){
                                        respuesta.aaData[i].total_dias = "-";
										respuesta.aaData[i].total_formula = "-";
                                    }else{
                                        respuesta.aaData[i].total_dias = diferencia_Dias + " dias";
										respuesta.aaData[i].total_formula = (parseFloat(t_revision) + parseFloat(t_coordinacion) + parseFloat(valor_v) + parseFloat(valor_e)) + " dias";
                                    }
                                }
                            }

                            callback(respuesta);
                        },
                        error: function () {
                        }
                    });
                },
                rowId: function(row){
					return row.id;
				},
                columns: [
	                {data:"id",nombre:"id",title:"Id",visible:false},
	                {data:"created_at",nombre:"created_at",title:"Fecha Ingreso"},
	                {data:"zona",nombre:"zona",title:"Zona"},
					{data:"departamento",nombre:"departamento",title:"Departamento",
						defaultContent: "-"
					},
	                {data:"local",nombre:"local",title:"Tienda"},
	                {data:"sistema",nombre:"sistema",title:"Sistema"},
	                {data:"reporte",nombre:"reporte",title:"Reporte"
						,"render": function (data, type, row ) {
							// console.log(data)
							if(data != null){
								var length = data.length;
								var max_length = 25;
								if(length > max_length){
									return data.slice(0,max_length) +"..." ;
								}
							}
							return data;
						}
					},
	                {data:"tipo_mantenimiento",nombre:"tipo_mantenimiento",title:"Tipo Mantenimiento",
						defaultContent: "-"
	            	},
	                {data:"estado",nombre:"estado",title:"Estado"
						,"render": function (data, type, row ) {
							var inc_id=row["id"];
	                                var estado = data;
	                                html=estado;
	                                if (estado == "Solucionar") {
	                                	html="<button class='btn btn-sm text-success btn-default  btn-solucionar' data-id="+inc_id+"><span class='glyphicon glyphicon-ok-circle'></span> Solucionar</button>"; 
	                                } 
	                                return html;
	                        }
	             	},
					{data:"proveedor",nombre:"proveedor",title:"Proveedor Programado",
						defaultContent: "-"
					},
	                {data:"fecha_cierre",nombre:"fecha_cierre",title:"Fecha de Cierre",
						defaultContent: "-"
	            	},
					{data:"razon_social",nombre:"razon_social",title:"Razón Social",
						defaultContent: "-"
	            	},
                    {data:"total_dias",nombre:"total_dias",title:"Estim. Soli."
						,"render": function (data, type, row ) {
							if(row.estado == "Terminado"){
								var html = 'Terminado';
								return html;
							}
							else if(row.total_dias == '-') {
								var html = '<a data-toggle="tooltip" data-placement="bottom" title="No existe registro de Estimación">No registrado</a>';
								return html;
							}else{
								return row.total_dias;
							}
						}
                    },
	                {data:"id",nombre:"id",title:"Detalle"
						,"render": function (data, type, row ) {
							var html = '<a class="btn btn-rounded btn-primary btn-sm ver_detalle" title="Ver detalle">';
							html += '<i class="fa fa-eye"></i>';
							html += 'Ver';
							html += '</a>';
							return html;
	                	}
	            	}
                ],
				"createdRow": function (row, data){
                    var total_dias = parseFloat(data.total_dias)
                    var estado = data.estado;

                    var cantidad_dias = parseFloat(total_dias);
                    if(!isNaN(cantidad_dias)){
                        if(estado != "Terminado"){
                            if (cantidad_dias >= 0) {
                                $(row).addClass('mayor-cero');
                            } else if (cantidad_dias < 0) {
                                $(row).addClass('menor-cero');
                            } else {
                                $(row).addClass('menor-normal');
                            }
                        }
                    }
                },
                "drawCallback":function (){
					/*$("#tbl_solicitud_mantenimiento tbody tr td").off("click").on("click",function(){
						var solicitud_id = $(this).closest("tr").attr("id");
						sec_solicitud_mantenimiento_cargar_solicitud(solicitud_id);
					})*/
					$("#tbl_solicitud_mantenimiento tbody .ver_detalle").off("click").on("click",function(){
						var solicitud_id = $(this).closest("tr").attr("id");
						sec_solicitud_mantenimiento_cargar_solicitud(solicitud_id);
					})
                },

                "initComplete": function (settings, json) {
					setTimeout(function(){
						$("#solicitud_mantenimiento_recargar").off("click").on("click",function(){
							tablaserver.ajax.reload(null, false);
						})
						tablaserver.columns.adjust();
					},100)
					// 0 => Nuevo, 1 => Atendido, 2 => Asignado
					action = "sec_solicitud_mantenimiento_dias_list";
					filtrar_datatable_sec_solicitud_mantenimiento(settings,json);
                }
            });
	return tablaserver;
}

function sec_solicitud_mantenimiento_cargar_solicitud_vt(solicitud_id){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_solicitud_mantenimiento_cargar_solicitud_vt = "sec_solicitud_mantenimiento_cargar_solicitud_vt";

	$.ajax({
		url: 'sys/set_solicitud_mantenimiento.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			sec_solicitud_mantenimiento_modal_detalle_events();
			$("#check_comercial_div").hide();
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
				$("#modal_detalle #foto_terminado").attr("src","files_bucket/solicitud_mantenimiento/"+obj.local["foto_terminado"]);
			}
			//$("#modal_detalle #imagen_view").attr("src","files_bucket/solicitud_mantenimiento/"+obj.imagenes[0].archivo);
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

			
			$("#div-tipo-mant-modal").hide();
			$("#div-sistema-modal").hide();
			


			$("#modal_detalle #estado").off("change").on("change",function(){
				if($(this).val() == "Terminado"){
					$(".foto_terminado").show();
					$(".comentario").hide();					
				}
				if($(this).val() == "Programado"){
					$(".comentario").show();					
					$(".foto_terminado").hide();
				}
				if($(this).val() == "Solicitud"){
					$(".comentario").hide();					
					$(".foto_terminado").hide();
				}
			})
		//$("#imagen_view").imgViewer2("destroy");
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


function sec_solicitud_mantenimiento_cargar_solicitud(solicitud_id){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_solicitud_mantenimiento_cargar_solicitud = "sec_solicitud_mantenimiento_cargar_solicitud";

	$.ajax({
		url: 'sys/set_solicitud_mantenimiento.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			sec_solicitud_mantenimiento_modal_detalle_events();
			$("#check_comercial_div").show();
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

			var src_file_img = "files_bucket/solicitud_mantenimiento/";
			if (/*obj.local["id_garantia"] !== null || */ obj.local["id_garantia"] !== '') {
				src_file_img = "files_bucket/solicitud_garantia/";
			}

			if(obj.local["estado"]=="Terminado"){
				if(obj.local["foto_terminado"] != null)
				{
					$("#modal_detalle #foto_terminado").attr("src",src_file_img + obj.local["foto_terminado"]);
				}
				if (obj.local["comercial_visto"] != null)
				{
					if (obj.local["comercial_visto"] == 1){
						$("#check_comercial").prop("checked",true);
					}
					if (obj.local["comercial_visto"] == 0){
						$("#check_comercial").prop("checked",false);
					}
				}
			}
			//$("#modal_detalle #imagen_view").attr("src","files_bucket/solicitud_mantenimiento/"+obj.imagenes[0].archivo);
			$("#modal_detalle #imagenes_cargar").empty();
			$(obj.imagenes).each(function(i,e){
				$("#modal_detalle #imagenes_cargar").append(
					$("<img class='imagenes_modal'>").attr("src",src_file_img+e.archivo)
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
					$("#sec_solicitud_mantenimiento_btn_derivar_a_servicio_tecnico").show();
				}
				if($(this).val() == "Programado"){
					$(".comentario").show();
					$(".foto_terminado").hide();
					$("#div_derivacion_tecnico").show();
					$("#sec_solicitud_mantenimiento_btn_derivar_a_servicio_tecnico").show();
				}
				if($(this).val() == "Solicitud"){
					$(".comentario").hide();
					$(".foto_terminado").hide();
					$("#div_derivacion_tecnico").hide();
					$("#sec_solicitud_mantenimiento_btn_derivar_a_servicio_tecnico").show();
				}
				if($(this).val() == "Programado con Proveedor"){
					$(".comentario").show();
					$(".foto_terminado").hide();
					$("#div_derivacion_tecnico").hide();
					$("#sec_solicitud_mantenimiento_btn_derivar_a_servicio_tecnico").show();
				}
				if($(this).val() == "Derivado a Servicio Técnico")
				{
					$("#sec_solicitud_mantenimiento_btn_derivar_a_servicio_tecnico").hide();
				}
			})
		//$("#imagen_view").imgViewer2("destroy");
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
function filtrar_datatable_sec_solicitud_mantenimiento(settings,json){
	var boton_buscar = true;

	var localStorage_estado_var="sec_solicitud_mantenimento_estado_select";
	var datatable = settings.oInstance.api();

	$("#btn_solicitud_mantenimiento_search").off("click").on("click",function(){
		var tipo_b = $('#sec_sol_mant_tipo').val();
		console.log(tipo_b);
		if (tipo_b == 1){
			const div = document.querySelector("#div-fil-est");		 
			div.className;      
			div.getAttribute("class");  
			div.className = "col-lg-1 col-xs-12";
			div.setAttribute("class", "col-lg-1 col-xs-12"); 

			const div2 = document.querySelector("#div-fil-zona");		 
			div2.className;      
			div2.getAttribute("class");  
			div2.className = "col-lg-1 col-xs-12";
			div2.setAttribute("class", "col-lg-1 col-xs-12"); 


			$("#div_filtros_mant").show();
			$("#div_list_derivacion").hide();
			$("#div_list_mantenimiento").show();
			action = "sec_solicitud_mantenimiento_dias_list";
			datatable.ajax.reload(null, false);
		}else{
			const div = document.querySelector("#div-fil-est"); 
			div.className;   
			div.getAttribute("class");  
			div.className = "col-lg-1 col-xs-12";
			div.setAttribute("class", "col-lg-2 col-xs-12"); 

			const div2 = document.querySelector("#div-fil-zona");		 
			div2.className;      
			div2.getAttribute("class");  
			div2.className = "col-lg-1 col-xs-12";
			div2.setAttribute("class", "col-lg-2 col-xs-12"); 

			$("#div_filtros_mant").hide();
			$("#div_list_derivacion").show();
			$("#div_list_mantenimiento").hide();
			listar_solicitudes_mantenimiento_vt();
			 
		}
		
	})
	$("#btn_solicitud_mantenimiento_excel").off("click").on("click",function(){
		action = "sec_solicitud_mantenimiento_list_excel";
		datatable.ajax.reload(null, false);
	})

	$("#btn_solicitud_mantenimiento_dias_excel").off("click").on("click",function(){
		action = "sec_solicitud_mantenimiento_list_dias_excel";
		datatable.ajax.reload(null, false);
	})

	$("#estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(7).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#estado_select").select2();

	if(localStorage.getItem(localStorage_estado_var) && localStorage.getItem(localStorage_estado_var)!="null"){
		setTimeout(function(){
			var valor = localStorage.getItem(localStorage_estado_var).split(',');
			$("#estado_select").val(valor).change();
		},200);
	}
	else{
		setTimeout(function(){
			$("#estado_select").val([0,2]).change();//nuevos,asignados
		},200);
	}

	var localStorage_estado_var="sec_solicitud_mantenimento_local_select";
	$("#local_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(3).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#local_select").select2();


	var localStorage_estado_var="sec_solicitud_mantenimento_zona_select";
	$("#zona_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(2).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#zona_select").select2();

	var localStorage_estado_var="sec_solicitud_mantenimento_sistema_select";
	$("#sistema_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(4).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#sistema_select").select2();

	var localStorage_estado_var="sec_solicitud_mantenimiento_razon_social_select";
	$("#razon_social_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(10).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#razon_social_select").select2();

}
function sec_solicitud_mantenimiento_ubicacion(){
	if (navigator.geolocation) {
		data = {};
			var gps = navigator.geolocation.getCurrentPosition(
				function (position){
						$("input[name='latitude']").val(position.coords.latitude);
						$("input[name='longitude']").val(position.coords.longitude);
						/*swal({
								title: '¡Ubicación obtenida!',
								text: "",
								type: 'success',
								showCancelButton: false,
								closeOnConfirm: false
							})*/
						$("#btn_ubicacion").hide();
						$("#ubicacion_txt").val(position.coords.latitude+"  "+ position.coords.longitude);
						$("#ubicacion_txt").show();
				},function(err){
					switch(err.code) {
						case err.PERMISSION_DENIED:
							var txt = "";
								txt+= '<video width="400" controls><source src="files/howlocation2.mp4" type="video/mp4">Para continuar debes habilitar la ubicación.<br> Por favor haz click en el botón de abajo<br> para aprender cómo hacerlo.</video>';
							swal({
								title: '¡Ubicación no habilitada!',
								text: txt,
								type: 'warning',
								showCancelButton: false,
								closeOnConfirm: false,
								html:1,
								confirmButtonText : "Aprende cómo habilitar aquí",
								allowEscapeKey:0
							}, function(inputValue){
								console.log(inputValue);
								window.open(
									'https://support.google.com/chrome/answer/114662',
									'_blank' // <- This is what makes it open in a new window.
								);
							});
						break;
						case err.POSITION_UNAVAILABLE:
						break;
						case err.TIMEOUT:
						break;
						case err.UNKNOWN_ERROR:
						break;
					}
				});
	}
}