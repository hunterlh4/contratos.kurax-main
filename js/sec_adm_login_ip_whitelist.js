function sec_adm_login_ip_whitelist(){
	if(sec_id == "adm_login_ip_whitelist"){
		console.log("sec:adm_login_ip_whitelist");
		sec_adm_login_ip_whitelist_events();
		cargar_tabla_whitelist();
	}
}

function sec_adm_login_ip_whitelist_events(){
	$("#btn_desactivar_listablanca_grupo").off("click").on("click",function(){
		var set_data = {
			grupo_id : $("#modal_grupos_listablanca [name='grupo_id']").val(),
			grupo_nombre : $("#modal_grupos_listablanca [name='grupo_id'] option:selected").text()
		};
		$.post('/sys/set_adm_login_ip_whitelist.php', {
			"desactivar_listablanca_grupo": set_data
		}, function(r) {
			loading();
			try{
				loading();
				var obj = jQuery.parseJSON(r);
				if(obj.error){
					set_data.error = obj.error;
					set_data.error_msg = obj.error_msg;
					auditoria_send({"proceso":"sec_adm_login_ip_desactivar_listablanca_grupo","data":set_data});
					swal({
						title: "¡Error!",
						text: obj.error_msg,
						type: "warning",
						timer: 6000,
						closeOnConfirm: true
					},
					function(){
						swal.close();
					});
					return false;
				}

				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_adm_login_ip_desactivar_listablanca_grupo","data":set_data});
				swal({
					title: "Lista Blanca Desactivada!",
					text: obj.mensaje,
					type: "success",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
			}catch(err){
				loading();
			}
		});
	})
	$("#btn_grupos_listablanca").off("click").on("click",function()
	{
		$("#modal_grupos_listablanca").on("shown.bs.modal",function(){
			$(".select_listablanca_desactivar").select2();
		});
		$("#modal_grupos_listablanca").modal("show");
	});
	$(".select2").select2();
	$("#btn_actualizar").off("click").on("click",function(){
		cargar_tabla_whitelist();
	})
	$(".save_btn")
		.off()
		.click(function(event) {
			var btn = $(this);
			sec_adm_login_ip_whitelist_save(btn);
		});
	
	$("#tbl_login_ip_whitelist").DataTable();

	$("#tabla_whitelist").off("change").on("change",".switch_estado",function(event){
		sec_adm_login_ip_whitelist_estado($(event.target));
	})
	$("#panel-datos_2 .switch_estado").off("change").on("change",function(event){
		if($("input[name='id']").val() != "new")
		{
			sec_adm_login_ip_whitelist_estado($(event.target));
		}
	})

	$("#panel-datos_2 .switch_estado")
		.bootstrapToggle({
			on:"activo",
			off:"inactivo",
			onstyle:"success",
			offstyle:"danger",
			size:"mini"
	});
}

function sec_adm_login_ip_whitelist_estado(btn){
	estado = "";
	if(btn.prop('checked')){
		estado = btn.attr("data-on-value");
		btn.val(1);
	}else{
		estado = btn.attr("data-off-value");
		btn.val(0);
	}

	var set_data = {
		sec_adm_login_ip_whitelist_estado :'sec_adm_login_ip_whitelist_estado',
		id : btn.attr("data-id"),
		estado : estado
	};
	loading(true);
	$.post('/sys/set_adm_login_ip_whitelist.php', {
		"sec_adm_login_ip_whitelist_estado": set_data
	}, function(r) {
		loading();
		try{
			loading();
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_adm_login_ip_whitelist_estado","data":set_data});
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
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_adm_login_ip_whitelist_estado","data":set_data});
				swal({
					title: "¡Estado Cambiado!",
					text: obj.mensaje,
					type: "success",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					auditoria_send({"proceso":"save_item","data":set_data});
					swal.close();
				});
			}
		}catch(err){
			loading();
		}
	});
}

function sec_adm_login_ip_whitelist_save(btn){
	var set_data = {};
	$(".save_data").each(function(index, el) {
		set_data[$(el).attr("name")] = $(el).val();
	});
	var estado_check = $("input[name='estado']"); 
	set_data["estado"] = estado_check.prop("checked") ? estado_check.attr("data-on-value") : estado_check.attr("data-off-value");
	
	loading(true);
	$.post('/sys/set_adm_login_ip_whitelist.php', {
		"sec_adm_login_ip_whitelist_save": set_data
	}, function(r) {
		try{
			loading();
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_adm_login_ip_whitelist_save_error","data":set_data});
				swal({
					title: "¡Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
					custom_highlight($(".save_data[name='"+obj.error_focus+"']"));
					setTimeout(function(){
						$(".save_data[name='"+obj.error_focus+"']").focus();	
					}, 10);
				});
			}else{
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_adm_login_ip_whitelist_save_done","data":set_data});
				swal({
					title: "¡Guardado!",
					text: obj.mensaje,
					type: "success",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					if(btn.data("then")=="reload"){
						if(set_data.id=="new"){
							set_data.id=obj.id;
							auditoria_send({"proceso":"add_item","data":set_data});
							window.location="./?sec_id=" +sec_id+ "&item_id="+obj.id;
						}else{
							auditoria_send({"proceso":"save_item","data":set_data});
							swal.close();
						}
					}else if(btn.data("then") == "exit"){
						auditoria_send({"proceso":"save_item","data":set_data});
						window.location="./?sec_id="+sec_id;
					}
				});
			}
		}catch(err){
			loading();
		}
	});
}

function cargar_tabla_whitelist(){
	set_data = { sec_adm_login_ip_whitelist_list : 1 };
    $.ajax({
        url: "/sys/set_adm_login_ip_whitelist.php",
        data:set_data,
        type: 'POST',
         beforeSend: function() {
         	loading(true);
         },
         complete:function(){
         	loading();
         },
        success: function (response) {//  alert(datat)
            var resp = JSON.parse(response);
            var data = resp.lista;
			set_data.curr_login = resp.curr_login;
			auditoria_send({"proceso":"sec_login_ip_whitelist_list","data":set_data});

   			tabla_encuesta = $('#tabla_whitelist').DataTable( {
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
	      		data : data,
    		    "drawCallback": function (settings, json) {
		    		$(".switch_estado")
					.bootstrapToggle({
						on:"activo",
						off:"inactivo",
						onstyle:"success",
						offstyle:"danger",
						size:"mini"
					});
                },
                order : [],
    			columns: [
    				{
						title: "Id",
						data: "id",
						class:"text-right"
					},
					{
						title: "Ip",
						data: "ip",

					},
					{
						title: "Descripción",
						data: "descripcion",
					},
					{
						title: "Grupo",
						data: "grupo_nombre",
						defaultContent : "---"
					},
				
					{
						title: "Estado",
						data: "estado",
						"render" : function (data,type,row){
							return '<input class="switch_estado" id="checkbox_' + row["id"]+'" type="checkbox" ' +(row["estado"] == 1 ? 'checked="checked"' : "") + ' data-id="' + row["id"] + '" data-on-value="1" data-off-value="0">';
						}
					},
					{
						title: "Opciones",
						width:"150px",
						"render":function(data,type,row){
							var id = row["id"];
							var estado = row["estado"];
							var estado_nombre = row["estado_nombre"];
							var html = "<div class='text-right'>";
							html += ' <a class="btn btn-rounded btn-default btn-sm btn-edit" title="Editar" href="./?sec_id='+sec_id+'&amp;item_id='+id+'">';
							html += '<i class="glyphicon glyphicon-edit"></i>';
							html += '</a>';
							html += '</div>';
							
							return html;
						}
					}
				],
    		} );
        },
        error: function () {
    		set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"sec_login_ip_whitelist_list","data":set_data});
        }
    });
}