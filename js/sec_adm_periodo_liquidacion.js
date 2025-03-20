function sec_adm_periodo_liquidacion(){
	if(sec_id=="adm_periodo_liquidacion"){
		console.log("sec:adm_periodo_liquidacion");
		sec_adm_periodo_liquidacion_events();
		cargar_tabla();
	}
}

function sec_adm_periodo_liquidacion_events(){

	$("#btn_actualizar").off("click").on("click",function(){
		cargar_tabla();
	})
	$('.fechas')
		.datepicker({
			dateFormat:'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		})
	$(".save_btn")
		.off()
		.click(function(event) {
			var btn = $(this);
			sec_adm_periodo_liquidacion_save(btn);
		});
	
	$("#tbl_periodo_liquidacion").DataTable();

	$(document).on("click",".periodo_liquidacion_estado",function(){
		estado=$(this).attr("data-estado");
		estado_nombre=$(this).attr("data-estado_nombre");
		periodo_liquidacion_id=$(this).attr("data-periodo_liquidacion_id");
		periodo = $(this).attr("data-periodo");
		sec_adm_periodo_liquidacion_estado(periodo_liquidacion_id,estado,estado_nombre,periodo);
	})

}


function sec_adm_periodo_liquidacion_estado(periodo_liquidacion_id,estado,estado_nombre,periodo){
	var set_data = {
		sec_adm_periodo_liquidacion_estado :'sec_adm_periodo_liquidacion_estado',
		periodo_liquidacion_id :periodo_liquidacion_id,
		estado : estado,
		estado_nombre : estado_nombre,
		periodo : periodo
	};
	loading(true);
	$.post('/sys/set_adm_periodo_liquidacion.php', {
		"sec_adm_periodo_liquidacion_estado": set_data
	}, function(r) {
		loading();
		try{
			loading();
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_adm_periodo_liquidacion_estado","data":set_data});
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
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_adm_periodo_liquidacion_estado","data":set_data});
				swal({
					title: "Estado Cambiado!",
					text: obj.mensaje,
					type: "success",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					auditoria_send({"proceso":"save_item","data":set_data});
					swal.close();
					//m_reload();
					cargar_tabla();
					
				});
			}
		}catch(err){
			loading();
			// console.log(r);
		}
	});
}




function sec_adm_periodo_liquidacion_save(btn){
	var set_data = {};
	$(".save_data").each(function(index, el) {
		set_data[$(el).attr("name")]=$(el).val();
	});
	// console.log(set_data);
	loading(true);

	$.post('/sys/set_adm_periodo_liquidacion.php', {
		"sec_adm_periodo_liquidacion_save": set_data
	}, function(r) {
		try{
			loading();
			var obj = jQuery.parseJSON(r);
			// console.log(obj);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_adm_periodo_liquidacion_save_error","data":set_data});
				swal({
					title: "Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
					custom_highlight($(".save_data[name='"+obj.error_focus+"']"));
					setTimeout(function(){
						$(".save_data[name='"+obj.error_focus+"']").val("").focus();	
					}, 10);
				});
			}else{
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_adm_periodo_liquidacion_save_done","data":set_data});
				swal({
					title: "Guardado!",
					text: obj.mensaje,
					type: "success",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					m_reload();
					if(btn.data("then")=="reload"){
						if(set_data.id=="new"){
							set_data.id=obj.id;
							auditoria_send({"proceso":"add_item","data":set_data});
							window.location="./?sec_id="+sec_id+"&sub_sec_id="+sub_sec_id+"&item_id="+obj.id;
						}else{
							auditoria_send({"proceso":"save_item","data":set_data});
							swal.close();
							m_reload();
						}
					}else if(btn.data("then")=="exit"){
						auditoria_send({"proceso":"save_item","data":set_data});
						window.location="./?sec_id="+sec_id+"&sub_sec_id="+sub_sec_id;
					}else{
					}
				});
			}
		}catch(err){
			loading();
			// console.log(r);
		}
	});
}


function cargar_tabla(){
	set_data={sec_periodo_liquidacion_list:1};
    $.ajax({
        url: "/sys/set_adm_periodo_liquidacion.php",
        data:set_data,
        type: 'POST',
         beforeSend: function() {
         	loading(true);
         },
         complete:function(){
         	loading();
         },
        success: function (response) {//  alert(datat)
            var resp=JSON.parse(response);
            var data=resp.lista;
			set_data.curr_login = resp.curr_login;
			auditoria_send({"proceso":"sec_periodo_liquidacion_list","data":set_data});

   			tabla_encuesta=$('#tabla_periodos').DataTable( {
                "bDestroy": true,
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
	      		data:data,
    		    "initComplete": function (settings, json) {
                },
                order:[1],

    			columns: [
    				{
						title: "Id",
						data: "id",
						class:"text-right"
					},
					{
						title: "Fecha Inicio",
						data: "fecha_inicio",

					},
					{
						title: "Fecha Fin",
						data: "fecha_fin",
					},
					
				
					{
						title: "Estado",
						data: "estado_nombre",
					},
					{
						title: "Opciones",
						width:"150px",
						//data: "id",
						"render":function(data,type,row){
							var id=row["id"];
							var estado=row["estado"];
							var estado_nombre=row["estado_nombre"];
							var periodo = row["fecha_inicio"]+ " - " +row["fecha_fin"];
							var html="<div class='text-right'>";
							var btn_class="btn btn-sm btn-success periodo_liquidacion_estado";
							switch (parseInt(estado)){
								case 0:
									html+="<button class='"+btn_class+"' data-estado_nombre='Procesado' data-estado=1 data-periodo_liquidacion_id='"+id+"' data-periodo='"+periodo+"'>";
									html+="Procesar</button>";
									break;
								case 1:
								case 2:
									html+="<button class='"+btn_class+"' data-estado_nombre='Reprocesado' data-estado=2 data-periodo_liquidacion_id='"+id+"' data-periodo='"+periodo+"'>";
									html+="Reprocesar</button>";
									break;
								/*case 2:
									html="Reprocesado";*/
								default:
									html+="<button class='"+btn_class+"' data-estado_nombre='Procesado' data-estado=1 data-periodo_liquidacion_id='"+id+"' data-periodo='"+periodo+"'>";
									html+="Procesar</button>";	
									break;
							}
							html+=' <a class="btn btn-rounded btn-default btn-sm btn-edit" title="Editar" href="./?sec_id='+sec_id+'&amp;sub_sec_id='+sub_sec_id+'&amp;item_id='+id+'">';
							html+='<i class="glyphicon glyphicon-edit"></i>';
							html+='</a>';
							html+='</div>';
							
							return html;
						}
					}
				],
    		} );
        },
        error: function () {
    		set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"sec_periodo_liquidacion_list","data":set_data});

        }
    });

}

