var con_test = false;
function sec_contratos() {
	if(sec_id=="contratos"){
		console.log("sec_contratos");
		sec_contratos_events();
		var col_buscar=$('#tbl_contratos_datatable thead th:contains("Estado")').index();
		 table_contratos = $('#tbl_contratos_datatable').DataTable( {
			"scrollX": true,
			language:{
			    "decimal":        "",
			    "emptyTable":     "Tabla vacia",
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
			}
			,aLengthMenu:[15,30,60,120]
			,"order": [[ 0, 'desc' ]]
			,"columnDefs": [
	            {
	                "targets": [ col_buscar ],
	                "visible": false,
	                
	            }
	            
        	]

			,drawCallback:function(){
				 $("#estado_select").off("change").on("change",function(){
					$('#tbl_contratos_datatable').DataTable().columns(col_buscar).search($(this).val()).draw() ;
		 		})

				$('table').css('width', '100%');
				$('.dataTables_scrollHeadInner').css('width', '100%');
				$('.dataTables_scrollFootInner').css('width', '100%');
			},
			initComplete :function (){
				setTimeout(function(){
					$('table').css('width', '100%');
					$('.dataTables_scrollHeadInner').css('width', '100%');
					$('.dataTables_scrollFootInner').css('width', '100%');
				},100)
			}
	    });

		$('body').off("change").on("change",'.switch_estado',function(event){
			var btn=event.target;	
			console.log(event);
			var data = Object();
			data.id = $(btn).attr("data-id");
			data.table = "tbl_contratos";
			data.col = "estado";
			data.val = "2";
			auditoria_send({"proceso":"switch_contrato_terminado","data":data});
			$.ajax({
			  type: 'POST',
			  url: 'sys/set_contratos.php',
			  data: {
				"switch_contrato_terminado": 'switch_contrato_terminado',
				"data":data
			  },
			  beforeSend:function(){
			  	loading("true");
			  },
			  success:function(resp){
			  	var nombrecheckbox=$("#checkbox_"+$(btn).attr("data-id"));
				nombrecheckbox.bootstrapToggle("disable");
			    var colIndex = table_contratos.cell(nombrecheckbox.closest("td")).index().column;
			    var rowIndex = table_contratos.cell(nombrecheckbox.closest("td")).index().row;
			    table_contratos.cell(rowIndex, colIndex-1).data("Terminado");
			  },
			  complete:function(){
			  	loading();
			  }
			});

		});


		$("#select-cliente_id").off().change(function(event) {
			loading(true);
			var data = Object();
				data.cliente_id = $(this).val();
			$.get('sys/build_html.php', {
				"opt":"select_cliente_id",
				"data":data
				},
				function(r) {
					try{
						//console.log(r);
						var response = jQuery.parseJSON(r);
						//console.log(response);
						$("#select-local_id").html("");
						
						console.log(data.cliente_id);
						if(data.cliente_id){
							$("#select-local_id").append($("<option>").html("Seleccione un Local").val(""));						
							$.each(response.options, function(index, val) {
								 $("#select-local_id").append($("<option>").html(val).val(index));
							});
							$(".select_add_dialog_btn_local").removeClass('hidden').data('cliente_id', data.cliente_id);
							$("#select-local_id").removeAttr('disabled');


							$("#input_text-dni_o_ruc").val(response.cliente.dni_o_ruc);
							$(".form-group-dni_o_ruc").removeClass('hidden');
							$(".form-group-dni_o_ruc label.control-label").html(response.cliente.dni_o_ruc_label);
						}else{
							$("#select-local_id").append($("<option>").html("Seleccione un Cliente").val(""));
							$(".select_add_dialog_btn_local").addClass('hidden').data('cliente_id', '');
							$("#select-local_id").attr('disabled','disabled');
							$("#input_text-dni_o_ruc").val("").attr('placeHolder', 'Seleccione un Cliente');
						}
						loading();
				
					}catch(err){
			            swal({
			                title: 'Error en la base de datos',
			                type: "warning",
			                timer: 2000,
			            }, function(){
			                swal.close();
			                loading();
			            }); 
					}
			});
		});
		$('input[type=radio][name=como_se_entero]').change(function() {
			if (this.value == 'otros') {
				$(".hide_form_como_se_entero_des").show();
				$('.hide_form_como_se_entero_des textarea').attr('required','required');
				$(".hide_form_como_se_entero_des textarea").focus();
			}else{
				$(".hide_form_como_se_entero_des").hide();
				$('.hide_form_como_se_entero_des textarea').removeAttr('required');
			}
		});		
		$("#contratos_list_cols input[type=checkbox]")
			.click(function(event) {
				var limit = 10;
				event.stopPropagation();
				if($(this).attr("checked")){
					$(this).removeAttr("checked");
				}else{
					if($("#contratos_list_cols input[type=checkbox]:checked").length>limit){
						swal({title:"Maximo "+limit+" columnas.",type:"warning"});
						return false;
					}else{
						$(this).attr("checked","checked");
					}
				}
			});

		$(".add_file_modal_btn")
			.off()
			.click(function(event) {
				//.modal("show");
				console.log("add_file_modal_btn:click");
				var target = $(this).data("target");
				$(""+target)
				.on('shown.bs.modal', function (e) {
					$(target+" input[type=text]").first().focus();

					/* uploader /**/

					var error_msg = $('<div class="redtext"></div>');
					var btn = $(".upload-btn");
						btn.after(error_msg);

					var data = Object();
						data["tabla"] = $("#"+btn.data("form")+" input[name=new_file_tabla]").val();
						data["item_id"] = $("#"+btn.data("form")+" input[name=new_file_item_id]").val();
						data["nombre"]=$("#"+btn.data("form")+" input[name=new_file_nombre]").val();
						data["descripcion"]=$("#"+btn.data("form")+" input[name=new_file_descripcion]").val();
						data.url = document.location.href;
					var uploader = new ss.SimpleUpload({
							button: btn,
							name: 'uploadfile',
							autoSubmit:false,
							data: data,
							debug:true,
							onChange:function ( filename, extension, uploadBtn, fileSize, file) {
								//console.log(file);
								$("#"+btn.data("form")+" label.uploader_file_name").html(filename);
							},
							onSubmit: function(filename, extension, uploadBtn, size) {
								//save_item();
								loading(true);
								//console.log(data);
								//var progress_bar = $('<div class="progress_bar"><div class="progress"></div><div class="progress_text"></div></div>');
								//$(".loading_box").append(progress_bar);
							},         
							onComplete: function(filename, response, uploadBtn, size) {
								if (response) {
									//c(response);
									//mReload();
								}else{
									//alert(filename + 'upload failed');
									return false;            
								}
								m_reload();
							},
							onProgress:function(pro){
								//console.log(pro);
								//$(".progress").stop().animate({width: pro+"%"}, 200);
								//$(".progress_text").html(pro+"%");
							},
							onExtError: function(filename, type, status, statusText, response, uploadBtn, size) {
								//error_msg.html('Solo archivos '+data["data-type"]);
							}
						}); 						
						$("#"+btn.data("form"))
							.off()
							.submit(function(event) {
								event.preventDefault();
								data["nombre"]=$("#"+btn.data("form")+" input[name=new_file_nombre]").val();
								data["descripcion"]=$("#"+btn.data("form")+" input[name=new_file_descripcion]").val();
								uploader.submit();
							});

					/* /uploader /**/

				})
				.on('hidden.bs.modal', function (e) {
					//$("#select_add_dialog_modal").remove();
				})
				.modal("show");
			});
		//$(".add_file_modal_btn").click();
		$(".del_file_btn")
			.off()
			.click(function(event) {
				event.preventDefault();
				var btn = $(this);

				swal({
					title: '¿Seguro?',
					text: 'Esta accion eliminará el archivo.',
					type: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#DD6B55',
					confirmButtonText: 'Si, borrar!',
					cancelButtonText: 'No, cancelar!',
					closeOnConfirm: false,
					closeOnCancel: true

				}, function(isConfirm){
					if (isConfirm){ 
						var data = Object();
							data.table = "tbl_archivos";
							data.id = btn.data("id");
							data.col = "estado";
							data.val = "0";
						console.log(data);
						$.post('sys/set_data.php', {
							"opt": 'switch_data'
							,"data":data
						}, function(r, textStatus, xhr) {
							try{
								$("#file-item-"+data.id).hide();
								// auditoria_send({"consulta":"sec_reportes_web_total_get_reportes","data":get_reportes_data});
								auditoria_send({"proceso":"switch_data","data":data});
								swal('Eliminado!', 'El archivo ha sido eliminado.', 'success');
						
							}catch(err){
					            swal({
					                title: 'Error en la base de datos',
					                type: "warning",
					                timer: 2000,
					            }, function(){
					                swal.close();
					                loading();
					            }); 
							}
						});						
					}
				});				
			});
		$(".edit_file_btn")
			.off()
			.click(function(event) {
				var btn = $(this);
				var target = "#edit_file_modal";
				$(target+" input[name=edit_file_item_id]").val(btn.data("id"));
				$(target+" input[name=edit_file_nombre]").val(btn.data("nombre"));
				$(target+" input[name=edit_file_descripcion]").val(btn.data("descripcion"));
				$(""+target)
				.on('shown.bs.modal', function (e) {
					$(target+" input[type=text]").first().focus();
				})
				.on('hidden.bs.modal', function (e) {
					//$("#select_add_dialog_modal").remove();
				})
				.modal("show");
			});
		$("#edit_file_form")
			.off()
			.submit(function(event) {
				event.preventDefault();
				//loading(true);
				var save_data = Object();
					save_data.table = "tbl_archivos";
					save_data.id = $("input[name=edit_file_item_id]").val();
				
					save_data.values=Object();
					save_data.values.nombre = $("input[name=edit_file_nombre]").val();
					save_data.values.descripcion = $("input[name=edit_file_descripcion]").val();
				console.log(save_data);
				//console.log(save_data.values);
				$.post('sys/set_data.php', {
					"opt": 'save_item'
					,"data":save_data
				}, function(r, textStatus, xhr) {
					try{
						console.log("edit_file_form:ready");
						console.log(r);
						var response = jQuery.parseJSON(r);
						console.log(response);
						m_reload();
						auditoria_send({"proceso":"switch_data","data":data});	
					}catch(err){
			            swal({
			                title: 'Error en la base de datos',
			                type: "info",
			                timer: 2000,
			            }, function(){
			                swal.close();
			                loading();
			            }); 
					}
				});
			});		
		$(".contrato_change_formula_btn")
			.click(function(event) {
				var producto_id = $(this).data("pro-id");
				if($(".contrato_change_formula_holder[data-pro-id='"+producto_id+"']").hasClass('hidden')){
					$(".contrato_change_formula_holder[data-pro-id='"+producto_id+"']").removeClass('hidden');
					$(".formula_holder[data-pro-id='"+producto_id+"']").addClass('hidden');
					$(".contrato_change_formula_btn[data-pro-id='"+producto_id+"']").removeClass('btn-primary').addClass('btn-danger').html("Cancelar");
				}else{
					$(".contrato_change_formula_holder[data-pro-id='"+producto_id+"']").addClass('hidden');
					$(".formula_holder[data-pro-id='"+producto_id+"']").removeClass('hidden');
					$(".contrato_change_formula_btn[data-pro-id='"+producto_id+"']").addClass('btn-primary').removeClass('btn-danger').html("Cambiar Formula");
				}
			});
		$(".select2")
			.select2({ width: '100%' });
	}
}
function contrato_new() {
	$.post('sys/set_data.php', {
		"opt": 'add_contrato'
		,"data":save_data
	}, function(r, textStatus, xhr) {
		try{
			console.log("add_cliente_form:ready");
			console.log(r);
			var response = jQuery.parseJSON(r);
			console.log(response);	
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            }); 
		}
	});
}
function sec_contratos_events(){
	console.log("sec_contratos_events");

	// contratos_datepicker

	$(".contratos_datepicker")
		.datepicker("destroy")
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

	$(".btn_editar_contratos")
		.off()
		.on("click",function(event){
			event.preventDefault();
			
			var buton = $(this);
			var data = Object();
			data.filtro = Object();	
			data.where="validar_usuario_permiso_botones";			
			$(".input_text_validacion").each(function(index, el) {
				data.filtro[$(el).attr("data-col")]=$(el).val();
			});	
			data.filtro.text_btn = buton.data("button");
			console.log(data);
			auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
			$.ajax({
				data: data,
				type: "POST",
				dataType: "json",
				url: "/api/?json"
			})
			.done(function( dataresponse) {
				try{
					console.log(dataresponse);
					if (dataresponse.permisos==true) {
						window.location.href =	buton.data("href");
					}else{
						swal({
							title: 'No tienes permisos',
							type: "info",
							timer: 2000,
						}, function(){
							swal.close();
						});			
					}	
				}catch(err){
		            swal({
		                title: 'Error en la base de datos',
		                type: "warning",
		                timer: 2000,
		            }, function(){
		                swal.close();
		                loading();
		            }); 
				}
			})
			.fail(function( jqXHR, textStatus, errorThrown ) {
				if ( console && console.log ) {
					console.log( "La solicitud validar permisos contratos a fallado: " +  textStatus);
				}
			})
		});
	$(".contrato_save_btn")
		.off()
		.click(function(event) {
			var boton = $(this);	
			event.preventDefault();
			var d = {};
				d.form = $(this).data("form");
				d.then = $(this).data("then");
			contrato_save(d,boton);
		});
	$(".contrato_add_formula_modal_btn")
		.off()
		.click(function(event) {
			event.preventDefault();
			loading(true);
			var data = {};
				$(".save_data").each(function(index, el) {
					data[$(el).attr("data-col")]=$(el).val();
				});
			console.log(data);
			$.get('sys/build_html.php', {
				"opt":"contrato_add_formula_modal",
				"data":data
				},
				function(r) {
					try{
						//console.log(r);
						$("body").append(r);
						loading();
						$(".contrato_add_formula_btn")
							.off()
							.click(function(event) {
								event.preventDefault();
								//alert("OK");
								contrato_add_formula();
							});
						//var target = $(this).data("target");
						$("#contrato_add_formula_modal")
						.on('shown.bs.modal', function (e) {

						})
						.on('hidden.bs.modal', function (e) {
							$("#contrato_add_formula_modal").remove();
						})
						.modal("show");
					}catch(err){
			            swal({
			                title: 'Error en la base de datos',
			                type: "warning",
			                timer: 2000,
			            }, function(){
			                swal.close();
			                loading();
			            }); 
					}
			});
		});
	$(".select-formula_id")
		.off()
		.change(function(event) {
			console.log("select-formula_id:change");
			var data = {};
				data.id = $(this).val();
				data.new = 1;
				data.pro_id = $(this).data("pro-id");
			$.get('sys/build_html.php', {
				"opt":"get_formula_data",
				"data":data
				},
				function(r) {
					try{
						console.log("#select-formula_id:ready");
						$("#formula_data_holder-"+data.pro_id).html(r);
						loading();
						$("#formula_data_holder-"+data.pro_id+" input[type=text]").first().focus();
						sec_contratos_events();
					}catch(err){
			            swal({
			                title: 'Error en la base de datos',
			                type: "warning",
			                timer: 2000,
			            }, function(){
			                swal.close();
			                loading();
			            }); 
					}
			});
		});
	$(".contrato_formula_add_quiebre_btn")
		.off()
		.click(function(event) {
			var pro_id = $(this).data("pro-id");
			var num_ele = $(".detalle_item[data-pro-id='"+pro_id+"']").size();
			var cloned = $(".detalle_item[data-pro-id='"+pro_id+"']").last().clone();
			//console.log(cloned);
			$(cloned).attr("data-detalle-num",(num_ele));

			var prev_desde = parseInt($(cloned).find("input[name=desde]").val());
			var prev_hasta = parseInt($(cloned).find("input[name=hasta]").val());
			var prev_monto = parseInt($(cloned).find("input[name=monto]").val());

			$(cloned).find("input[name=desde]").val(prev_hasta+1);
			$(cloned).find("input[name=hasta]").val(prev_hasta+2);
			$(cloned).find("input[name=monto]").val(prev_monto+1);

			$(".detalle_item[data-pro-id='"+pro_id+"'] .rem_btn").addClass('hidden');
			$(cloned).appendTo(".detalle_holder table[data-pro-id='"+pro_id+"']");
			
			$(".detalle_item[data-pro-id='"+pro_id+"'] .rem_btn").last().removeClass('hidden');
			$(".detalle_item[data-pro-id='"+pro_id+"'] .rem_btn").last().off().click(function(event) {
				$(".detalle_item[data-pro-id='"+pro_id+"']").last().remove();
				if($(".detalle_item[data-pro-id='"+pro_id+"']").size()>1){
					$(".detalle_item[data-pro-id='"+pro_id+"'] .rem_btn").last().removeClass('hidden');
				}
			});
		});
	$(".contrato_add_producto_dialog_btn")
		.off()
		.click(function(event) {
			event.preventDefault();
			if(item_id=="new"){
				swal("Por favor primero guarde el contrato.");
			}else{
				$(".contrato_add_producto_dialog_btn").addClass('hidden');
				$(".form-add_producto").removeClass('hidden');
			}
		});
	$(".contrato_cancel_producto_dialog_btn")
		.off()
		.click(function(event) {
			event.preventDefault();
			$(".contrato_add_producto_dialog_btn").removeClass('hidden');
			$(".form-add_producto").addClass('hidden');
		});
	$(".contrato_add_producto_btn")
		.off()
		.click(function(event) {
			var btn = $(this).data("button");
			event.preventDefault();

			loading();
			var save_data = {};
				save_data.table="tbl_contrato_productos";
				save_data.id="new";
				save_data.values={};
				save_data.values.estado=1;
				save_data.values.contrato_id=item_id;
				save_data.values.producto_id=$("#select-add_producto").val();
				
					save_data.validacion = Object();	
				$(".input_text_validacion").each(function(index, el) {
					save_data.validacion[$(el).attr("data-col")]=$(el).val();
				});
					save_data.validacion.text_btn = btn;
				console.log(save_data);
			
			if(save_data.values.producto_id){
				console.log(save_data);
				$.post('sys/set_data.php', {
					"opt": 'save_item'
					,"data":save_data
				}, function(r, textStatus, xhr) {

						var response = jQuery.parseJSON(r);
						console.log(response);
						loading();
						try{
							if (response.permisos==true) {
								swal({
									title: "Agregado",
									text: "",
									type: "success",
									timer: 2000,
									closeOnConfirm: true
								},
								function(){
									m_reload();
									swal.close();
								});
										
							}else{
								swal({
									title: 'No tienes permisos',
									type: "info",
									timer: 2000,
								}, function(){
									swal.close();
									
								});
							}

						}catch(err){
				            swal({
				                title: 'Error en la base de datos',
				                type: "warning",
				                timer: 2000,
				            }, function(){
				                swal.close();
				                loading();
				            }); 							
							console.log(r);
						}
				});
			}else{
				swal({
					title: "Seleccione un producto",
					text: "",
					type: "warning",
					timer: 2000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
			}
			
		});

	$(".c_form_con_btn")
		.off()
		.click(function(event) {
			console.log("c_form_con_btn:click");
			var btn = $(this);
			c_form_con(btn);
		});
	if(!con_test){
		// $(".c_form_con_btn").click();
		con_test = 1;
	}

	$(".formula_tipo_3_save_btn")
		.off()
		.click(function(event) {
			var btn = $(this);	
			event.preventDefault();
			// swal({
			// 	title: '¿Seguro?',
			// 	text: '',
			// 	type: 'warning',
			// 	showCancelButton: true,
			// 	confirmButtonColor: '#55DD5D',
			// 	confirmButtonText: 'Si, guardar!',
			// 	cancelButtonText: 'No, cancelar!',
			// 	closeOnConfirm: false,
			// 	closeOnCancel: true

			// }, function(isConfirm){
			// 	if (isConfirm){
			// 		swal.close();

			// 	}else{
			// 	}
			// });

			formula_tipo_3_save(btn);
		});
	$(".formula_tipo_3_copy_btn")
		.off()
		.click(function(event) {
			var btn = $(this);	
			event.preventDefault();
			formula_tipo_3_copy(btn);
		});
}
function formula_tipo_3_copy(btn){
	var producto_id = btn.data("producto_id")
	var get_data = {};
		get_data.cf = $(".formula_tipo_3_copy_select_"+producto_id).val();
	$.post('sys/get_contratos.php', {
		"opt":"formula_tipo_3_copy",
		"data":get_data
	},
	function(r) {
		// console.log(r);
		if(r){
			$(".formula_tipo_3_holder_"+producto_id).html(r);
			sec_contratos_events();
		}
	});
}
function formula_tipo_3_save(btn){
	console.log("formula_tipo_3_save");

	var save_data = btn.data();
	var cons = {};

	$(".formula_tipo_3_holder_"+save_data.producto_id+" .f_condicional").each(function(index, el) {

		var con_data = $(el).data();
			con_data['ord']=index;
			con_data.is_true_id = $(el).children('.f_if_holder').children('.f_condicional').data('tmp_id');
			con_data.is_false_id = $(el).children('.f_else_holder').children('.f_condicional').data('tmp_id');
		console.log(con_data);
		cons[con_data.tmp_id] = con_data;
	});
	loading(true);
		save_data.cons = cons;
		console.log("save_data::::::::::::::");
		console.log(save_data);
		// save_data.producto_id = $()
	$.post('sys/set_contratos.php', {
		"opt": 'contratos_formula_tipo_3_save'
		,"data":save_data
	}, function(r, textStatus, xhr) {
		console.log(r);
		// loading();
		m_reload();
	});
	// console.log(cons);
}
function c_form_con_build_buttons(opt,data) {
	// console.log("c_form_con_build_buttons");
	// console.log(data);
	var span = $("<span>");
		span.addClass('input-group-btn');
	var buttons = {};

		buttons.add = $('<button>');
		buttons.add.addClass('btn').addClass('btn-primary').addClass('c_form_con_btn');
		buttons.add.html('<span class="glyphicon glyphicon-plus"></span>');
		buttons.add.data('what', 'add');
		if(data.holder_id){
			buttons.add.data('id',data.holder_id);
		}
		if(data.where){
		}else{
			data.where="new";
		}
			buttons.add.data('where',data.where);
		
		// data-id='f_<?php echo $f_id;?>'

		buttons.edit = $('<button>');
		buttons.edit.addClass('btn').addClass('btn-warning').addClass('c_form_con_btn');
		buttons.edit.html('<span class="glyphicon glyphicon-pencil"></span>');
		buttons.edit.data('what', 'edit');
		// buttons.edit.data('type', 'if');		

		buttons.remove = $('<button>');
		buttons.remove.addClass('btn').addClass('btn-danger').addClass('c_form_con_btn');
		buttons.remove.html('<span class="glyphicon glyphicon-minus"></span>');
		buttons.remove.data('what', 'remove');
		// buttons.remove.data('type', 'if');
		buttons.remove.data('id',data.holder_id);

		if(opt==1){
			span.append(buttons.add);
		}else if(opt==2){
			span.append(buttons.edit);
			span.append(buttons.remove);
		}
	return span;

	// var input_hidden = {};	

		// 	input_hidden.var1 = $('<input>');
		// 	input_hidden.var1.attr('type', 'hidden');
		// 	input_hidden.var1.attr('id', 'f_'+b.id+'_var1');
		// 	input_hidden.var1.attr('name', 'var1');
		// 	input_hidden.var1.val(b.current_data.var1);

		// 	input_hidden.var_operador = $('<input>');
		// 	input_hidden.var_operador.attr('type', 'hidden');
		// 	input_hidden.var_operador.attr('id', 'f_'+b.id+'_var_operador');
		// 	input_hidden.var_operador.attr('name', 'var_operador');
		// 	input_hidden.var_operador.val(b.current_data.var_operador);

		// 	input_hidden.var2 = $('<input>');
		// 	input_hidden.var2.attr('type', 'hidden');
		// 	input_hidden.var2.attr('id', 'f_'+b.id+'_var2');
		// 	input_hidden.var2.attr('name', 'var2');
		// 	input_hidden.var2.val(b.current_data.var2);
}
function c_form_con_build(b){
	console.log("c_form_con_build");
	console.log(b);
	// var html_insert = "";
	// if(b.btn_data.what="add"){
		var f_condicional_count = $(".f_condicional").length;
		var f_condicional_id = 'new_'+(f_condicional_count + 1);

		var f_condicional = $("<div>");
			f_condicional.addClass('f_condicional');
	// }else{
		// var f_condicional = $("#"+b.holder_id);
	// }

	// var f_condicional = $("<div>")
							// .addClass('f_condicional')
		f_condicional
			.attr('id','f_condicional_'+f_condicional_id)
			.data('tmp_id',f_condicional_id)
			.data('tipo', b.what);

							// if(b.what)

							
							// .data('true_id', 1)
							// .data('false_id', 2)
							;
	var f_line_holder = $("<div>")
							.addClass('input-group')
							.addClass('f_line_holder');

	var btn_data = {};
		btn_data.holder_id = f_condicional_id;
		// btn_data.holder_obj = $(f_condicional);

	if(b.what=="if"){
		f_condicional
			.data('var1', b.current_data.var1)
			.data('var_operador', b.current_data.var_operador)
			.data('var2', b.current_data.var2);
		var linea_text = $("<span>")
								.addClass('form-control')
								.html('SI('+b.current_data.var1+' '+b.current_data.var_operador+' '+b.current_data.var2+'){');
		f_line_holder.append(linea_text);
		f_line_holder.append(c_form_con_build_buttons(2,btn_data));


			btn_data.where = f_condicional_id+'_'+'f_if_holder';
		var f_if_holder = $("<div>")
							.addClass('f_if_holder')
							.attr('id',btn_data.where)
							.append(c_form_con_build_buttons(1,btn_data))
							;

			btn_data.where = f_condicional_id+'_'+'f_else_holder';
		var f_else_holder = $("<div>")
							.addClass('f_else_holder')
							.attr('id',btn_data.where)
							.append(c_form_con_build_buttons(1,btn_data))
							;

		f_condicional.append(f_line_holder);
		f_condicional.append(f_if_holder);
		f_condicional.append('<div class="form-control">}DE LO CONTRARIO{</div>');
		f_condicional.append(f_else_holder);
		f_condicional.append('<span class="form-control">}</span>');
	}else if(b.what=="action"){
		f_condicional
			.data('valor', b.current_data.valor)
			.data('valor_operador', b.current_data.valor_operador)
			.data('donde', b.current_data.donde);
		var linea_text = $("<span>")
								.addClass('form-control')
								.html(''+b.current_data.valor+''+b.current_data.valor_operador+' '+b.current_data.donde+'')
								;
		f_line_holder.append(linea_text);
		f_line_holder.append(c_form_con_build_buttons(2,btn_data));


		f_condicional.append(f_line_holder);		
	}else{		
	}
	// if(b.where){
	// 	$("#"+b.where).html(f_condicional);
	// }else{
	// 	$("#"+b.holder_id).html(f_condicional);
	// }
	// if(b.btn_data.what="add"){
	if(b.btn_data.what=="add"){
		if(b.where=='new'){
			$(".formula_tipo_3_holder").html(f_condicional);
		}else{
			$("#"+b.where).html(f_condicional);
		}
	}else{
		var parent = $("#"+b.holder_id).parent();
		parent.html(f_condicional);
		console.log(parent);
		// b.where = parent.attr('id');
	}
	
	// }else{
	// 	$("#"+b.holder_id).html(f_condicional);
	// }
	sec_contratos_events();
}
function c_form_con(btn){
	console.log("c_form_con");
	var btn_data = btn.data();
	console.log(btn_data);


	var current_data = {};
		current_data.var1 = "";
		current_data.var_operador = "";
		current_data.var2 = "";
		current_data.valor = "";
		current_data.valor_operador = "";
		current_data.donde = "";

	// alert(btn_data.what);
	if(btn_data.what=="add"){
		$("#edit_formula_modal").modal("show");
		$(".select_what")
			.off()
			.change(function(event) {
				var what = $(this).val();
				$("#edit_formula_modal .table").hide();
				$("#edit_formula_modal .table_"+what).stop().show();


				// current_data.var1 = "is_live";
				// current_data.var_operador = "==";
				// current_data.var2 = "1";

				// current_data.valor = "6";
				// current_data.valor_operador = "%";
				// current_data.donde = "apostado";

				// $.each(current_data, function(index, val) {
				// 	$("#edit_formula_modal .edit_input[name="+index+"]").val(val);
				// });
				// $("#edit_formula_modal .table input").first().focus();
				// alert(what);
			});
		// $(".select_what").val("if").change();
		$(".select_what").change().show();
		// $("#c_con_id_"+btn_data.id).addClass('bg-success');
		$("#edit_formula_modal .change_btn")
			.off()
			.click(function(event) {
				console.log("change_btn:click");

				var b = {};
					b.what = $(".select_what").val();
					b.holder_id = "f_condicional_"+btn_data.id;
					$.each(current_data, function(index, val) {
						current_data[index] = $("#edit_formula_modal .edit_input[name="+index+"]").val();
					});
					b.btn_data = btn_data;
					b.current_data = current_data;
					if(btn_data.where){
						b.where = btn_data.where;
					}
				c_form_con_build(b);

				$("#edit_formula_modal .close_btn").click();

			});
		// $(".change_btn").click();
	}else if(btn_data.what=="remove"){
		var parent = $("#f_condicional_"+btn_data.id).parent();
		btn_data.where = parent.attr("id");
		$("#f_condicional_"+btn_data.id).addClass('bg-danger');
		swal({
			title: '¿Seguro?',
			text: '',
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#DD6B55',
			confirmButtonText: 'Si, elmimar!',
			cancelButtonText: 'No, cancelar!',
			closeOnConfirm: false,
			closeOnCancel: true

		}, function(isConfirm){
			if (isConfirm){
				swal.close();
				// $("#c_con_id_"+btn_data.id).hide(500);
				$("#f_condicional_"+btn_data.id).hide("puff", {}, 100, function() {
					$("#f_condicional_"+btn_data.id).remove();
					parent.append(c_form_con_build_buttons(1,btn_data));
					sec_contratos_events();
				});
			}else{
				$("#f_condicional_"+btn_data.id).removeClass('bg-danger');
			}
		});

		if(btn_data.type=="if"){
			// $(this).closest('.f_condicional').addClass('bg-danger');

		}else if(btn_data.type=="action"){
			// $(this).closest('.f_doit').addClass('bg-danger');
		}				
	}else if(btn_data.what=="edit"){
		
		$("#edit_formula_modal").modal("show");
		$("#edit_formula_modal .table").hide();
		var f_condicional_data = $(".f_condicional#f_condicional_"+btn_data.id).data();
		console.log(f_condicional_data);
		// console.log(btn_data);
		// console.log(current_data);
		$.each(current_data, function(index, val) {
		// 	current_data[index] = $("#edit_formula_modal .edit_input[name="+index+"]").val();
			current_data[index] = f_condicional_data[index];
			$("#edit_formula_modal .edit_input[name="+index+"]").val(f_condicional_data[index]);
		});
		// console.log(current_data);
		// if(btn_data.tipo=="if"){
		// 	current_data.var1 = $("#"+btn_data.id+"_var1").val();
		// 	current_data.var_operador = $("#"+btn_data.id+"_var_operador").val();
		// 	current_data.var2 = $("#"+btn_data.id+"_var2").val();			
		// }else if(btn_data.tipo=="action"){
			// current_data.valor = $("#"+btn_data.id+"_valor").val();
			// current_data.valor_operador = $("#"+btn_data.id+"_valor_operador").val();
			// current_data.donde = $("#"+btn_data.id+"_donde").val();
			// current_data.valor = f_condicional_data.valor;
		// }
		$(".select_what").val(f_condicional_data.tipo).change().hide();
		// $.each(current_data, function(index, val) {
		// 	$("#edit_formula_modal .edit_input[name="+index+"]").val(val);
		// });
		$("#edit_formula_modal .table_"+f_condicional_data.tipo).stop().show(1,function(){
			$("#edit_formula_modal .table input").first().focus();
			console.log("focusNOW");
		});
		
		$("#edit_formula_modal .change_btn")
			.off()
			.click(function(event) {
				console.log("change_btn:click");

				var b = {};
					b.what = $(".select_what").val();
					b.holder_id = "f_condicional_"+btn_data.id;
					$.each(current_data, function(index, val) {
						current_data[index] = $("#edit_formula_modal .edit_input[name="+index+"]").val();
					});
					b.btn_data = btn_data;
					b.current_data = current_data;
					if(btn_data.where){
						b.where = btn_data.where;
					}

				// $("#"+b.holder_id+" .con_text").html("aquí");
				if(b.what=="action"){
					$("#con_text_"+btn_data.id).html(''+b.current_data.valor+''+b.current_data.valor_operador+' '+b.current_data.donde+'');
					$("#"+b.holder_id)
						.data('valor', b.current_data.valor)
						.data('valor_operador', b.current_data.valor_operador)
						.data('donde', b.current_data.donde);
				}else if(b.what=="if"){
					$("#con_text_"+btn_data.id).html('SI('+b.current_data.var1+' '+b.current_data.var_operador+' '+b.current_data.var2+'){');
					$("#"+b.holder_id)
						.data('var1', b.current_data.var1)
						.data('var_operador', b.current_data.var_operador)
						.data('var2', b.current_data.var2);
				}
				
				
				// c_form_con_build(b);

				$("#edit_formula_modal .close_btn").click();
			});
	}


	$("#edit_formula_modal .close_btn")
		.off()
		.click(function(event) {
			$("#edit_formula_modal").modal("hide");
		});
	// $("#edit_formula_modal .close_btn").click();



	$("#edit_formula_modal .table input").first().focus();
	// console.log(btn.data());
}
function contrato_add_formula(){
	console.log("contrato_add_formula");	

	var save_data = Object();
	$("#contrato_add_formula_modal .save_data").each(function(index, el) {
		save_data[$(el).data("col")]=$(el).val();
	});
		save_data.values=Object();
	$("#contrato_add_formula_modal .input_text").each(function(index, el) {
		save_data.values[$(el).attr("name")]=$(el).val();
	});
	$("#contrato_add_formula_modal input[type='radio']:checked").each(function(index, el) {
		save_data.values[$(el).attr("name")]=$(el).val();
	});
	console.log(save_data);

	loading(true);
	$("#contrato_add_formula_modal").modal("hide");

	$.post('sys/set_data.php', {
		"opt": 'save_item'
		,"data":save_data
	}, function(r, textStatus, xhr) {
		try{
			console.log("contrato_save:ready");
			console.log(r);
			var response = jQuery.parseJSON(r);
			console.log(response);
			loading();

			swal({
				title: "Guardado",
				text: "",
				type: "success",
				timer: 800,
				closeOnConfirm: false
			},
			function(){
				window.location="./?sec_id="+sec_id+"&sub_sec_id="+sub_sec_id+"&item_id="+save_data.values.contrato_id	+"#tab=tab_comercial";
			});
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            }); 
		}
	});
}
function contrato_save(bd,boton) {
	//alert(boton.data("button"));
	console.log("contrato_save");
	var save_data = Object();
	$(".save_data").each(function(index, el) {
		save_data[$(el).attr("data-col")]=$(el).val();
	});
		save_data.values=Object();
	$(".input_text").each(function(index, el) {
		save_data.values[$(el).attr("name")]=$(el).val();
	});
	$("input[type='radio']:checked").each(function(index, el) {
		save_data.values[$(el).attr("name")]=$(el).val();
	});
		save_data.validacion = Object();	
	$(".input_text_validacion").each(function(index, el) {
		save_data.validacion[$(el).attr("data-col")]=$(el).val();
	});

		save_data.validacion.text_btn = boton.data("button");

	save_data.extra = {};
	$(".save_extra").each(function(index, el) {
		var extra = {};
			extra.producto_id = $(el).data("pro-id");
			extra.formula_id = $(el).val();
		if(extra.formula_id){
			extra.detalles = {};
			$(".detalle_item[data-pro-id='"+extra.producto_id+"']").each(function(index, el) {
				var detalle = {};
					detalle.formula_id = extra.formula_id;
					detalle.producto_id = extra.producto_id;
					detalle.contrato_id = save_data.id;
					$(el).find("input").each(function(index, el) {
						detalle[$(el).attr("name")]=$(el).val();
					});
					//detalle.monto = $(el).find("input[name=monto]").val();
					detalle.num = $(el).data("detalle-num");

				extra.detalles[detalle.num]=detalle;
			});
			save_data.extra[index]=extra;
		}
	});
	console.log(save_data);				

	loading(true);
	$.post('sys/set_data.php', {
		"opt": 'save_item'
		,"data":save_data
	}, function(r, textStatus, xhr) {
		console.log("contrato_save:ready");
		console.log(r);
		var response = jQuery.parseJSON(r);
		console.log(response);

		// actualizamos solicitud contrato
		var cont_data = {};
		cont_data.porcentaje_participacion = $("#input_text-monto").val();
		cont_data.local_id = $("#select-local_id").val();
		$.post('sys/set_contratos.php', {
			"opt": 'contratos_actualiza_contrato_agente',
			"data":cont_data
		}, function(r) {
			console.log("contrato_agente_update:ready");
			console.log(r);
		});
		loading();
		try{
			if (response.permisos==true) {
				swal({
					title: "Guardado",
					text: "",
					type: "success",
					timer: 400,
					closeOnConfirm: false
				},
				function(){
					console.log(bd.then);
					if(bd.then=="reload"){
						if(save_data["id"]=="new"){
							save_data.id=response.item_id;
					 		auditoria_send({"proceso":"add_item","data":save_data});
					 		window.location="./?sec_id="+sec_id+"&sub_sec_id="+sub_sec_id+"&item_id="+response.item_id;
					 	}else{
					 		auditoria_send({"proceso":"save_item","data":save_data});
					 		swal.close();
					 		m_reload();
					 	}
					} else if(bd.then=="force_reload"){
						auditoria_send({"proceso":"save_item","data":save_data});
						m_reload();
					} else if(bd.then=="exit"){
					 	auditoria_send({"proceso":"save_item","data":save_data});
					 	window.location="./?sec_id="+sec_id+"&sub_sec_id="+sub_sec_id;
					}else{
					}
					swal.close();
				});
			}else{
				swal({
					title: 'No tienes permisos',
					type: "info",
					timer: 2000,
				}, function(){
					swal.close();
					
				});
			}
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            });
			console.log(r);
		}
	});
}