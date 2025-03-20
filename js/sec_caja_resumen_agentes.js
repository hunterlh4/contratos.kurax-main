function sec_caja_resumen_agentes(){
	if( sec_id == "caja_resumen_agentes") {
		loading(true);
		sec_caja_resumen_agentes_events();
		sec_caja_resumen_agentes_settings();
		sec_caja_resumen_agentes_get_locales();
		loading();
		item_config = [];
		table_dt_concar = null;
		table_dt = null;
		function sec_caja_resumen_agentes_get_locales(){
		  var data = {};
		  data.what = {};
		  data.what[0] = "id";
		  data.what[1] = "nombre";
		  data.where = "locales";
		  data.filtro = {red_id: [5]}
			$.ajax({
			  data: data,
			  type: "POST",
			  dataType: "json",
			  url: "/api/?json",
			  async: "false"
			})
			.done(function( data, textStatus, jqXHR ) {
			  try{
				  $.each(data.data,function(index,val){
					var new_option = $("<option>");
					$(new_option).val(val.nombre);
					$(new_option).html(val.nombre);
					$("#sec_caja_resumen_agentes_local").append(new_option);
				  });				  
				  $('#sec_caja_resumen_agentes_local').select2({closeOnSelect: false});
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
				console.log( "La solicitud locales a fallado: " +  textStatus);
			})
		}
		function sec_caja_resumen_agentes_settings(){
		    $(".sec_caja_resumen_agentes_date")
				.datepicker({
					dateFormat:'dd-mm-yy',
					changeMonth: true,
					changeYear: true
				})
				.on("change", function(ev) {
					$(this).datepicker('hide');
					var newDate = $(this).datepicker("getDate");
					$(this).attr("data-date" , $.format.date(newDate, "yyyy-MM-dd") );
				});
			$(".sec_caja_resumen_agentes_filtros_div input ,.sec_caja_resumen_agentes_filtros_div select ").
			each(function(i,e){
				var id = $(e).attr("id");
				if(localStorage.getItem( id )){
					if($(e).hasClass("sec_caja_resumen_agentes_date"))
					{
						var d = moment(localStorage.getItem(id),"YYYY-MM-DD").format('DD-MM-YYYY');
						$(e).datepicker("setDate", d ).change();
					}
					else
					{
			   			$(e).val(localStorage.getItem(id));
					}
				}	
			})
			
		    
		   
		}
		function sec_caja_resumen_agentes_events(){
			$(".btn_filtrar_sec_caja_resumen_agentes")
				.off("click")
				.on("click",function(){
					loading(true);
					sec_caja_resumen_agentes_mostrar_datatable(null);
					//sec_caja_resumen_agentes_get_pagos();
				});
			$(".btn_filtrar_sec_caja_resumen_agentes_periodo")
				.off("click")
				.on("click",function(){
					loading(true);
					sec_caja_resumen_agentes_mostrar_datatable(null, true);
					//sec_caja_resumen_agentes_get_pagos();
				});
			$(".btn_descargar_excel").off().on("click", function (event) {
				sec_descargar_concar_agentes();
				//$("#modal_concar").modal("hide");
			});
			$(".btn_concar_agentes").off().on("click", function () {
				$("#concar_local_id").val($("#sec_caja_resumen_agentes_local").children("option:selected").val()).change();
				$("#tipo_cambio").val("");
				sec_caja_resumen_agentes_concar_datatable();
				$('#concar_local_id').select2({closeOnSelect: false});
				$("#modal_concar").modal("show");
			});

			$("#btnSaveConciliaciones_agentes").off().on('click', function (event) {
				event.preventDefault();
				if($('[id="chkConciliaciones"]:checked').length ==  0)
				{
					swal({
							title: "Seleccionar Registros" ,
							html: true,
							type: "info",
							closeOnConfirm: true
						}, function () {
							swal.close();
						})
					return false;
				}
				loading(true);
				var data = [];
				$('[id="chkConciliaciones"]:checked').each(function (index, el) {
					data.push({
						"nro_operacion": $(this).closest('tr').find("#nro_operacion").html(),
						"id_pago_detalle": $(this).closest('tr').find("#id_pago_detalle").html(),
						"id_transaccion": $(this).closest('tr').find("#id_transaccion").html(),
					});
				});

				auditoria_send({"proceso":"sec_caja_resumen_agentes_conciliar_automatico","data":data});
				$.post('/sys/get_caja_resumen_agentes.php', {
					"opt" : "sec_caja_resumen_agentes_conciliar_automatico",
					"sec_caja_resumen_agentes_conciliar_automatico": data
				}, function (response) {
					console.log(response)
					auditoria_send({"proceso": "caja_resumen_agentes_auto_conciliar", "data": data});
					$("#modal_prevalidacion").modal("hide");
					setTimeout(function () {
						loading();
						table_dt.ajax.reload();
					}, 1000);
				});

			});

			$('#tabla_sec_caja_resumen_agentes').on("click",'[id=btnCheckDeposito]' , function (event) {
				event.preventDefault();
				loading(true);

				rowInfo = {
					"id_pago_detalle": $(this).attr('data-id'),
					"nro_operacion": $(this).attr('data-nro_operacion'),
				}

				get_data = $(this)[0].dataset;
				if (typeof get_data.id_transaccion == "undefined") {
					var data = {};

					data.opt = "sec_excel_depositosugerencias";
					data.fecha = $(this).attr("data-fecha");
					data.nro_doc = $(this).attr("data-nro_doc");
					data.monto = $(this).attr("data-monto");

					auditoria_send({"proceso": "sec_depositos_agentes_sugerencia", "data": data});
					$.ajax({
						data: data,
						type: "POST",
						url: "sys/get_caja_resumen_agentes.php",
					})
						.done(function (response, textStatus, jqXHR) {
							response = JSON.parse(response);
							var tbody = $('#tbConciliacionFilter tbody');
							tbody.html("");
							$.each(response, function (index, sugerencias) {
								var tr = $('<tr onclick="selectRow(this)">');
								$.each(sugerencias, function (i, field) {
									$('<td id="' + i + '">').html(field).appendTo(tr);
								});
								$('<td>').html('<div class="checkbox"><label><input type="checkbox" id="chkTransacciones" name="chkTransacciones" class="form-check-input"><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span><label</div>').appendTo(tr);
								tbody.append(tr);
							});
							$("#local",$("#mdDefinirConciliacion")).val(get_data.local);
							$("#txtId",$("#mdDefinirConciliacion")).text(get_data.id);
							$("#txtFecha",$("#mdDefinirConciliacion")).val(get_data.fecha);
							$("#nro_operacion",$("#mdDefinirConciliacion")).val(get_data.nro_doc);
							$("#txtMonto",$("#mdDefinirConciliacion")).val(get_data.monto);
							$("#txtConciliacionFilter",$("#mdDefinirConciliacion")).val("");
							$("#id_pago_detalle").val(get_data.id);
							loading();
							$("#mdDefinirConciliacion").modal("show");
						})
						.fail(function (jqXHR, textStatus, errorThrown) {
							loading();
							console.log("La solicitud usuarios a fallado: " + textStatus);
						})
				} else {
					$("#local").val(get_data.local);
					$("#txtId").text(get_data.id);
					$("#txtFecha").val(get_data.fecha);
					$("#nro_operacion").val(get_data.nro_op);
					$("#id_transaccion").val(get_data.id_transaccion);
					$("#txtMonto").val(get_data.monto);
					$("#concepto").val(get_data.concepto);
					$("#importe").val(get_data.importe);
					$("#nro_doc").val(get_data.nro_doc);
					$("#txtFecha_tra").val(get_data.fecha_tra);
					loading();
					$("#mdDetalleConciliacion").modal("show");
				}
			});
			$('#btnSelectConciliacion').off().on('click', function (event) {
				event.preventDefault();
				if ($('[id="chkTransacciones"]:checkbox:checked').length > 0) {
					loading(true);
					$('[id="chkTransacciones"]:checkbox:checked').each(function (i) {
						var data = {};

						data.opt = "sec_relacionar";
						data.id_transaccion = $(this).closest('tr').find('#id').html();
						data.id_pago_detalle = $('#id_pago_detalle').val();
						auditoria_send({"proceso": "sec_depositos_agentes_relacionar", "data": data});
						$.ajax({
							data: data,
							type: "POST",
							url: "sys/get_caja_resumen_agentes.php",
						})
							.done(function (dato, textStatus, jqXHR) {
								var obj = dato;
								table_dt.ajax.reload();
							})
							.fail(function (jqXHR, textStatus, errorThrown) {
								if (console && console.log) {
									console.log("La solicitud  a fallado: " + textStatus);
								}
							})

					});
					$("#mdDefinirConciliacion").modal("hide");

					setTimeout(function () {
						loading();
					}, 1000);
				}
			});

			$("#btnRemoveConciliacion").off().on('click', function (event) {
				event.preventDefault();
				var data = {};
				data.opt = "sec_quitarDeposito";
				data.id_transaccion = $("#id_transaccion").val();
				data.importe = $("#importe").val();
				data.concepto = $("#concepto").val();
				data.nro_doc = $("#nro_doc").val();
				swal({
						title: "Está Seguro de quitar Depósito?",
						text: "Id Transacción : <strong>" + data.id_transaccion + "</strong><br>Nro Op. : <strong>" + data.nro_doc + "</strong><br>Concepto : <strong>" + data.concepto + "</strong><br> Monto : <strong>" + data.importe + "</strong>",
						type: "warning",
						timer: 800,
						html: true,
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "Si!",
						cancelButtonText: "No!",
						closeOnConfirm: true,
						closeOnCancel: true
					},
					function (isConfirm) {
						if (isConfirm) {
							$.ajax({
								data: data,
								type: "POST",
								url: "sys/get_caja_resumen_agentes.php",
							})
								.done(function (dato, textStatus, jqXHR) {
									auditoria_send({"proceso": "deposito_agente_remover_deposito", "data": data});
									swal.close();
									table_dt.ajax.reload();
									$("#mdDetalleConciliacion").modal("hide");
								});
						}
					});
			});


			setTimeout(function () {
				var upload_btn = $(".upload_agentes_btn");
				if(upload_btn.length == 0 )
				{
					return;
				}
				var data = {};
				data["opt"] = "sec_caja_resumen_agentes_archivo";
				var uploader = new ss.SimpleUpload({
					button: upload_btn,
					name: 'file',
					autoSubmit: true,
					data: data,
					debug: true,
					url: '/sys/get_caja_resumen_agentes.php',
					error: function (jqXHR, textStatus, errorThrown) {
						swal({
							title: errorThrown ,
							html: true,
							text: jqXHR.responseText ,
							type: textStatus,
							closeOnConfirm: true
						}, function () {
							swal.close();
						})
		            },
					onChange: function (filename, extension, uploadBtn, fileSize, file) {
						console.log("uploader:onChange");
						console.log(file);
						$('#progress').html("");
						$('#progressBar').width(0);
						$("#filename").html(filename + " " + (file.size) + "Kb");
					},
					onSubmit: function (filename, extension, uploadBtn, size) {
						loading(true);
					},
					onComplete: function (filename, response, uploadBtn, size) {
						console.log(response);
						var resp = JSON.parse(response); 
						if (resp.error == false) {
							loading();
							swal({
									title: "Éxito!",
									text: "Las transacciones fueron importadas exitosamente.",
									type: "success",
									timer: 3000,
									closeOnConfirm: true
								},
								function () {
									swal.close();
									//m_reload();
								});
						} else {
							loading();
							swal({
								title: "Error!" ,
								html: true,
				                text:  "<div><strong>" + resp.msg+ "</strong></div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>"+resp.msg_error+ "<div>",
								type: "warning",
								closeOnConfirm: true
							}, function () {
								swal.close();
							})
							return false;
						}
					},
					onProgress: function (progress) {
						$('#progress').html("Progreso: " + Math.round(progress) + "%");
						$('#progressBar').width(progress + "%");
					}
				});
			}, 1000);


			$(".btn_conciliar_agentes").off().on("click", function () {
				sec_caja_resumen_agentes_get_depositosPreValidacion();
			});
			$("#chkConciliacionesAll").on('change', function(){
				$(".chkConciliaciones").prop('checked', $(this).prop('checked'));
			})
			$("#tipo_cambio").validar_numerico_decimales({decimales : 3});


			$(document).on('click', '#deleteConcarHistoryAgentes', function (event) {
				event.preventDefault();
				loading(true);

				var get_data = {};
				get_data.id = $(this).data("id");

				$.post('/sys/get_caja_resumen_agentes.php', {"delete_concar_agentes_history": get_data}, function () {
					if(table_dt_concar)
					{
						table_dt_concar.ajax.reload();
					}
					loading(false);
				});
			});
		}

			function sec_caja_resumen_agentes_concar_datatable(){

				var columnas = [];
				columnas.push({title : "Local Creación" , data : "nombre" 
                	,render : function(data,type,row){
                		if(data == null){
                			return "Todos"; 
                		}
                		return data;
					}	
				});
				columnas.push({title : "Tipo Cambio" , data : "cambio" 		,className : "",defaultContent: "---" 	});
				columnas.push({title : "Correlativo Inicial" , data : "correlativo" ,className : "",defaultContent: "---" 	});
				columnas.push({title : "Usuario" , data : "usuario" 			,className : "",defaultContent: "---" 	});
				columnas.push({title : "Fecha Inicio" , data : "fecha_inicio" 	,className : "",defaultContent: "---" 	});
				columnas.push({title : "Fecha Fin" , data : "fecha_fin" 		,className : "",defaultContent: "---" 	});
				columnas.push({title : "Fecha Op." , data : "fecha_operacion" 		,className : "",defaultContent: "---" 	});
				columnas.push({title : "Ver" , data : "id"   		,className : "",defaultContent: "---" 	
                	,render : function(data,type,row){
						var html = '<a href="/export/files_exported/' + row['url'] + '" class="btn btn-xs btn-success"><i class="fa fa-file-o"></i></a>';
						html += ' <button id="deleteConcarHistoryAgentes" data-id="' + row["id"] + '" class="btn btn-xs btn-danger"><i class="fa fa-close"></i></button>';
						return html;
					}
				});

				table_dt_concar = $('#tblConcarHistorico').DataTable
				(
					{
		              	"bDestroy": true,
						scrollX : true,
		                "sScrollX": "100%",
		                "bProcessing": true,
		                'processing': true,
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
		                "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
		                "order": [[ 7, "desc" ]],
		                "columnDefs":[
						],
						columns : columnas,
						ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
							datat.opt = "sec_caja_resumen_agentes_get_concar";
		                    ajaxrepitiendo = $.ajax({
		                        global: false,
								type: "POST",
								url: "/sys/get_caja_resumen_agentes.php",
		                        data: datat,//+data,
		                        beforeSend: function () {
		                        },
		                        complete: function () {
									setTimeout(function(){
										table_dt_concar.columns.adjust()
									},200);
		                        },
		                        success: function (datos) {//  alert(datat)
		                            var respuesta = JSON.parse(datos);
		                            callback(respuesta);
		                        },
		                    });
		                },

		                "initComplete": function (settings, json) {
							$(".dataTables_filter:visible").hide()
							filtrar_local_datatable(settings,json);
		                }			
					}
				);
				loading();
			}

		function sec_caja_resumen_agentes_get_pagos(){
			loading(true);
			var payload = {};
			payload.opt = "sec_caja_resumen_agentes_get_pagos";
			payload.filtro = {};
			$(".sec_caja_resumen_agentes_filtros_div input:visible , .sec_caja_resumen_agentes_filtros_div select ")
			.each(function(i,e)
			{
				var id = $(e).attr("id");
				if( $(e).hasClass("sec_caja_resumen_agentes_date") ){
					payload[id] = $(e).attr("data-date");
				}
				else{
					payload[id] = $(e).val();
				}
				localStorage.setItem( id , payload[id] );
			})
			auditoria_send({"proceso":"sec_caja_resumen_agentes_get_pagos","data": payload});

			$.ajax({
				data: payload,
				type: "POST",
				url: "/sys/get_caja_resumen_agentes.php",
			})
			.done(function(responsedata, textStatus, jqXHR ) {
				try{
					var response = jQuery.parseJSON(responsedata);
					var obj = response.data;
					sec_caja_resumen_agentes_mostrar_datatable(obj);
				}catch(err){
					console.log(err);
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
				console.log( "La solicitud liquidaciones a fallado: " +  textStatus);
			});
		}

		function sec_caja_resumen_agentes_mostrar_datatable(obj, search_type = false){

			var columnas = [];
			columnas.push({title : "LOCAL" , data : "LOCAL" 		});
			columnas.push({title : "PAGO TIPO" , data : "PAGO TIPO" 		,className : "",defaultContent: "---" 	});
			columnas.push({title : "FECHA" , data : "FECHA"   				,className : "",defaultContent: "---" 	});
			columnas.push({title : "PERIODO INICIO" , data : "PERIODO INICIO" ,className : "",defaultContent: "---" 	});
			columnas.push({title : "PERIODO FIN" , data : "PERIODO FIN"   	,className : "",defaultContent: "---" 	});
			columnas.push({title : "NRO OPERACIÓN" , data : "NRO OPERACIÓN" ,className : "",defaultContent: "---" 	});
			columnas.push({title : "DESCRIPCIÒN" , data : "DESCRIPCIÓN" 	,className : "",defaultContent: "---" 	});
			columnas.push({title : "MONTO" , data : "MONTO"   				,className : "text-right",defaultContent: "---" 	});
			columnas.push({title : "AGENTEDEP" , data : "id_transaccion"  , className : "text-right",
				render : function(data,type,row){					
					var id_transaccion = row["id_transaccion"];
					var html = "";
					var conciliacion_de_cajas_permiso = $("#conciliacion_de_cajas_permiso").length;
					if(conciliacion_de_cajas_permiso == 0 )
					{
						html = row["importe"] ? row["importe"] : "--";
						return html;
					}
					var tra_green = '" data-importe="' + row["importe"] + '" data-concepto="' + row["concepto"] + '" data-id_transaccion="' + row["id_transaccion"] + '" data-fecha_tra="' + row["fecha_tra"] + '"';
					if(id_transaccion)
					{
						html =   '<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-success" data-local="' + row["LOCAL"] + '" data-fecha="' + row["FECHA"] + '" data-monto="' + row["MONTO"] + '"  data-id="' + row["id"] + '" data-nro_op ="' + row["NRO OPERACIÓN"]+ '" data-nro_doc="' + (row["NRO OPERACIÓN"]? row["NRO OPERACIÓN"] :"") + '"';
						html += tra_green;
						html += '>';
						html += row["importe"];
					}
					else
					{
						html = '<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-warning" data-local="' + row["LOCAL"] + '" data-fecha="' + row["FECHA"] + '" data-monto="' + row["MONTO"] + '"  data-id="' + row["id"] + '" data-nro_op ="' + row["NRO OPERACIÓN"]+ '" data-nro_doc="' + (row["NRO OPERACIÓN"]? row["NRO OPERACIÓN"] :"") + '">';
						html += '<i class="glyphicon glyphicon-search"></i>';
					}
					html += '</button>';
					return html;
				}
			});
			columnas.push({title:"id_transaccion",data : "id_transaccion"   	, visible:false});

			table_dt = $('#tabla_sec_caja_resumen_agentes').DataTable
			(
				{
	              	"bDestroy": true,
					scrollX : true,
	                "sScrollX": "100%",
	                "bProcessing": true,
	                'processing': true,
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
	 				//"deferLoading": 0, // here
	                "bDeferRender": false,
					"autoWidth": true,
					pageResize:true,
	                "bAutoWidth": true,
					paging:false,
	                serverSide: true,
	                "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
	                "order": [[ 1, "desc" ]],
	                "columnDefs":[
					],
					columns : columnas,
					ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
						datat.periodo = search_type;
						datat.opt = "sec_caja_resumen_agentes_get_pagos";
						$(".sec_caja_resumen_agentes_filtros_div input:visible , .sec_caja_resumen_agentes_filtros_div select ")
						.each(function(i,e)
						{
							var id = $(e).attr("id");
							if( $(e).hasClass("sec_caja_resumen_agentes_date") ){
								datat[id] = $(e).attr("data-date");
							}
							else{
								datat[id] = $(e).val();
							}
							localStorage.setItem( id , datat[id] );
						})
						
						console.log(datat);
	                    ajaxrepitiendo = $.ajax({
	                        global: false,
							type: "POST",
							url: "/sys/get_caja_resumen_agentes.php",
	                        data: datat,//+data,
	                        beforeSend: function () {
	                        },
	                        complete: function () {
								table_dt.columns.adjust();
	                        },
	                        success: function (datos) {//  alert(datat)
	                            var respuesta = JSON.parse(datos);
								if(respuesta.error){
									swal(respuesta.error_msg, '', "error");
									callback({ data: [] });
									return false;
								}
								callback(respuesta);
	                        },
	                    });
	                },

	                "initComplete": function (settings, json) {
						filtrar_local_datatable(settings,json);
						$("#btnFilterConciliadosAgentes").off().on('click', function (event) {
							event.preventDefault();
							var val = "";
							if ($(this).text().indexOf("Filtrar") == 1) {
								$(this).html('<i id="icoFilterConciliadosAgente" class="glyphicon glyphicon-remove-sign"></i> Remover Filtro');
								$(this).removeClass('btn-default');
								$(this).addClass('btn-danger');
								val = 1;
							} else {
								$(this).html('<i id="icoFilterConciliadosAgente" class="glyphicon glyphicon-filter"></i> Filtrar No Conciliados');
								$(this).removeClass('btn-danger');
								$(this).addClass('btn-default');
								val = "";
							}
							var datatable = settings.oInstance.api();
							datatable.column(7).search(val).draw();
							datatable.columns.adjust();
						});
	                }			
						
					
				}
			);
			loading();
		}


		function sec_caja_resumen_agentes_get_depositosPreValidacion() {
			console.log("sec_caja_resumen_agentes_get_depositosPrevalidacion");
			loading(true);
			item_config = [];
			/*$(".item_config").each(function (index, el) {
				var config_index = $(el).attr("name");
				var config_val = $(el).val();
				var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
				localStorage.setItem(ls_index, config_val);
				item_config[config_index] = config_val;
			});*/
			item_config["local_id"] = $("#sec_caja_resumen_agentes_local").val();
			item_config["fecha_inicio"] = $("#sec_caja_resumen_agentes_fecha_inicio").attr("data-date");
			item_config["fecha_fin"] = $("#sec_caja_resumen_agentes_fecha_fin").attr("data-date");
			var get_data = jQuery.extend({}, item_config);

			auditoria_send({"proceso":"sec_caja_resumen_agentes_Prevalidacion","data":get_data});
			$.post('/sys/get_caja_resumen_agentes.php', {
				"opt" : "sec_caja_resumen_agentes_Prevalidacion" ,
				"sec_caja_resumen_agentes_Prevalidacion": get_data
			}, function (response) {
				response = JSON.parse(response);
				var tbodyVenta = $('#tbConciliarMatchesVenta tbody');
				tbodyVenta.html("");

				var props = []
				$.each(response, function (index, conciliaciones) {
					if (conciliaciones.transacciones_agente != undefined ) {
						if(conciliaciones.transacciones_agente.length > 0 ) {
							for (let i = 0; i < conciliaciones.transacciones_agente.length; i++) {
								var tr = $('<tr onclick="selectRow(this)" style="background-color: #e2ffe7">');
								$('<td id="fecha_operacion" class="text-center">').html(conciliaciones.FECHA).appendTo(tr);
								$('<td id="local_nombre">').html(conciliaciones.LOCAL).appendTo(tr);
								$('<td id="nro_operacion">').html(conciliaciones["NRO OPERACIÓN"]).appendTo(tr);
								$('<td id="importe" class="text-right">').html(conciliaciones.MONTO).appendTo(tr);
								$('<td id="depo_id_transaccion" class="text-center">').html(conciliaciones.transacciones_agente[i].id).appendTo(tr);
								$('<td id="depo_fecha" class="text-center">').html(conciliaciones.transacciones_agente[i].fecha_operacion).appendTo(tr);
								$('<td id="depo_nro_cod" class="text-center">').html(conciliaciones.transacciones_agente[i].nro_doc).appendTo(tr);
								$('<td id="depo_importe" class="text-right">').html(conciliaciones.transacciones_agente[i].importe).appendTo(tr);
								$('<td class="text-center">').html('<div class="checkbox"><label><input type="checkbox" id="chkConciliaciones" checked="checked" name="chkConciliaciones" class="form-check-input chkConciliaciones"><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span><label</div>').appendTo(tr);
								$('<td style="display:none;" id="id_pago_detalle">').html(conciliaciones.id).appendTo(tr);
								$('<td style="display:none;" id="id_transaccion">').html(conciliaciones.transacciones_agente[i].id).appendTo(tr);
								tbodyVenta.append(tr);
							}
						}
					}
				});
				loading();
				$("#modal_prevalidacion").modal("show");
			});
		}

		function sec_descargar_concar_agentes() {
			if ($("#tipo_cambio").val() == "") {
				console.log("tipo de cambio vacio...");
				swal({
						  title: 'Ingrese tipo de cambio',
						  type: "warning",
						  timer: 2000,
					  }, function(){
						  swal.close();
					  }); 
				return false;
			}
			item_config = [];
			$(".sec_caja_resumen_agentes_concar_filtros_div input:visible , .sec_caja_resumen_agentes_concar_filtros_div select ")
			.each(function(i,e)
			{
				var id = $(e).attr("id");
				if( $(e).hasClass("sec_caja_resumen_agentes_date") ){
					item_config[id] = $(e).attr("data-date");
				}
				else{
					item_config[id] = $(e).val();
				}
				localStorage.setItem( id , item_config[id] );
			})

			var get_data = jQuery.extend({}, item_config);
			loading(true);
			auditoria_send({"proceso": "caja_depositos_agentes_concar", "data": get_data});
			$.ajax({
				url: '/export/caja_depositos_agentes_concar.php',
				type: 'post',
				data: {"sec_caja_agentes_concar_excel": get_data},
			})
				.done(function (dataresponse) {
					var obj = JSON.parse(dataresponse);
					window.open(obj.path);
					table_dt_concar.ajax.reload();
					loading();
				})
		}

	}
};


function filtrar_local_datatable(settings,json){
	var localStorage_var = sec_id + "_local";
	var datatable = settings.oInstance.api();
	var $elem = $("#" + sec_id + "_local");
	$elem.off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(0).search(val).draw();
		datatable.columns.adjust();
		localStorage.setItem(localStorage_var,val);
	})
	$elem.select2();

	if(localStorage.getItem(localStorage_var) && localStorage.getItem(localStorage_var)!="null"){
		setTimeout(function(){
			var valor = localStorage.getItem(localStorage_var).split(',');
			$elem.val(valor).change();
		},200);
	}
	else{
		setTimeout(function(){
			$elem.val("").change();//nuevos,asignados
		},200);
	}
}