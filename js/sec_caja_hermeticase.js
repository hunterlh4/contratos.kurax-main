function sec_caja_hermeticase(){
	if( sec_id == "caja_hermeticase" ) {
		item_config = {};
		console.log("sec:caja_hermeticase");
		sec_caja_hermeticase_config();
		sec_caja_hermeticase_events();
		sec_caja_hermeticase_get_locales();

		function sec_caja_hermeticase_config() {
			$(".sec_caja_reporte_fecha_datepicker")
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
		}

		function sec_caja_hermeticase_events() {
			console.log("sec_caja_hermeticase_events");		   
			$(".btn_concar_hermeticase").off().on("click", function () {
				$("#sec_caja_hermeticase_concar_local_id").val($("#sec_caja_hermeticase_local_id").children("option:selected").val()).change();
				$("#sec_caja_hermeticase_tipo_cambio").val("");
				$('#sec_caja_hermeticase_concar_local_id').select2({closeOnSelect: false});
				$("#sec_caja_hermeticase_modal_concar").modal("show");
				sec_caja_hermeticase_get_locales_concar();
			});

			$(".btn-concar-boveda_hermeticase").off().on("click", function () {
				$("#sec_caja_hermeticase_concar_boveda_local_id").val($("#sec_caja_hermeticase_local_id").children("option:selected").val()).change();
				$("#sec_caja_hermeticase_concar_boveda_tipo_cambio").val("");
				$('#sec_caja_hermeticase_concar_boveda_local_id').select2({closeOnSelect: false});
				$("#sec_caja_hermeticase_modal_concar_boveda").modal("show");
				sec_caja_hermeticase_get_locales_concar_boveda();
			});

			$(document).on('click', '#deleteConcarHistory', function (event) {
				event.preventDefault();
				loading(true);

				var get_data = {};
				get_data.id = $(this).data("id");

				$.post('/sys/get_caja_hermeticase.php', {"delete_concar_hermeticase_history": get_data}, function () {
					//filter_concar_hermeticase_table(0);
					if(table_dt_concar)
					{
						table_dt_concar.ajax.reload();
					}
					loading(false);
				});
			});

			$(document).on('click', '#deleteConcarBovedaHistory', function (event) {
				event.preventDefault();
				loading(true);

				var get_data = {};
				get_data.id = $(this).data("id");

				$.post('/sys/get_caja_hermeticase.php', {"delete_concar_hermeticase_boveda_history": get_data}, function () {
					if(table_dt_concar_boveda)
					{
						table_dt_concar_boveda.ajax.reload();
					}
					loading(false);
				});
			});

			$(".sec_caja_hermeticase_btn_descargar_excel").off().on("click", function (event) {
				sec_descargar_concar_hermeticase();
				//$("#modal_concar").modal("hide");
			});

			$(".sec_caja_hermeticase_concar_boveda_btn_descargar_excel").off().on("click", function (event) {
				sec_descargar_concar_hermeticase_boveda();
				//$("#sec_caja_hermeticase_modal_concar_boveda").modal("hide");
			});

			$('#sec_caja_hermeticase_modal_concar').off().on('shown.bs.modal', function () {
				sec_caja_hermeticase_concar_datatable();
				$('#sec_caja_hermeticase_tipo_cambio').focus();
			});
			$('#sec_caja_hermeticase_modal_concar_boveda').off().on('shown.bs.modal', function () {
				sec_caja_hermeticase_concar_boveda_datatable();
				$('#sec_caja_hermeticase_concar_boveda_tipo_cambio').focus();
			});

			setTimeout(function () {
				var upload_btn = $(".upload-btn");
				var data = {};
				data["sec_caja_hermeticase_archivo"] = "tbl_transacciones_hermeticase";
				var uploader = new ss.SimpleUpload({
					button: upload_btn,
					name: 'file',
					autoSubmit: true,
					data: data,
					debug: true,
					url: '/sys/get_caja_hermeticase.php',
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

			setTimeout(function () {
				var upload_btn = $(".upload-concar-btn");
				var data = {};
				data["sec_caja_hermeticase_boveda_archivo"] = "sec_caja_hermeticase_boveda_archivo";
				var uploader = new ss.SimpleUpload({
					button: upload_btn,
					name: 'concar_hermeticase_file',
					autoSubmit: true,
					data: data,
					debug: true,
					url: '/sys/get_caja_hermeticase.php',
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
				                text:  "<div><strong>" + resp.msg + "</strong></div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>"+(resp.msg_error ? resp.msg_error : '' )+ "<div>",
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

			/*$("#concar_hermeticase_file").on('change',(function(e) {
				let fileInput = document.getElementById('concar_hermeticase_file');
				let file = fileInput.files[0];
				let formData = new FormData();
				formData.append('concar_hermeticase_file', file);

				loading(true)
				$.ajax({
					url: '/sys/get_caja_hermeticase.php',
					type: "POST",
					data: formData,
					contentType: false,
					cache: false,
					processData:false,
					success: function(r) {
						loading(false)
						if (r){
							swal({
									title: "Éxito",
									text: "Las transacciones fueron importadas exitosamente.",
									type: "success",
									timer: 3000,
									closeOnConfirm: true
								},
								function () {
									swal.close();
								});
						} else {
							swal("Error!", "No se pudo Importar el Archivo", "warning");
						}
					},
					error: function(e) {
						loading();
						swal("Error!", "No se pudo Importar el Archivo", "warning");
					}
				});
			}));*/

			$(".depositos_hermeticase_btn")
				.off()
				.click(function (event) {
					sec_caja_get_depositos_hermeticase();
				});

			$(".select2").select2({
				closeOnSelect: true,
				width: "100%",
			});
		}

		function sec_descargar_concar_hermeticase() {
			if ($("#sec_caja_hermeticase_tipo_cambio").val() == "") {
				swal({
						  title: 'Ingrese tipo de cambio',
						  type: "warning",
						  timer: 5000,
					  }, function(){
						  swal.close();
						  $("#sec_caja_hermeticase_tipo_cambio").focus();
					  }); 
				return false;
			}
			item_config = [];
			$(".sec_caja_resumen_hermeticase_concar_filtros_div input:visible , .sec_caja_resumen_hermeticase_concar_filtros_div select")
			.each(function(i,e)
			{
				var id = $(e).attr("id");
				if( $(e).hasClass("sec_caja_resumen_hermeticase_date") ){
					item_config[id] = $(e).attr("data-date");
				}
				else{
					item_config[id] = $(e).val();
				}
				localStorage.setItem( id , item_config[id] );
			})

			var get_data = jQuery.extend({}, item_config);
			loading(true);
			auditoria_send({"proceso": "caja_depositos_hermeticase_concar", "data": get_data});
			$.ajax({
				url: '/export/caja_depositos_concar_hermeticase.php',
				type: 'POST',
				data: { "sec_caja_hermeticase_concar_excel" : get_data },
			})
				.done(function (dataresponse) {
					var obj = JSON.parse(dataresponse);
					if(obj.error ){
						loading();
						swal({
							title: obj.mensaje ,
							html: true,
							type: "info",
							closeOnConfirm: true
						}, function () {
							swal.close();
						})
						return false;
					}
					window.open(obj.path);
					table_dt_concar.ajax.reload();
					loading();
				})
		}
		function sec_descargar_concar_hermeticase_boveda() {
			if ($("#sec_caja_hermeticase_concar_boveda_tipo_cambio").val() == "") {
				swal({
						  title: 'Ingrese tipo de cambio',
						  type: "warning",
						  timer: 5000,
					  }, function(){
						  swal.close();
						  $("#sec_caja_hermeticase_concar_boveda_tipo_cambio").focus();
					  }); 
				return false;
			}
			item_config = [];
			$(".sec_caja_resumen_hermeticase_concar_boveda_filtros_div input:visible , .sec_caja_resumen_hermeticase_concar_boveda_filtros_div select")
			.each(function(i,e)
			{
				var id = $(e).attr("id");
				if( $(e).hasClass("sec_caja_resumen_hermeticase_date") ){
					item_config[id] = $(e).attr("data-date");
				}
				else{
					item_config[id] = $(e).val();
				}
				localStorage.setItem( id , item_config[id] );
			})

			var get_data = jQuery.extend({}, item_config);
			loading(true);
			auditoria_send({"proceso": "caja_depositos_hermeticase_concar_boveda", "data": get_data});
			$.ajax({
				url: '/export/caja_depositos_concar_boveda_hermeticase.php',
				type: 'POST',
				data: { "sec_caja_hermeticase_concar_boveda_excel" : get_data },
			})
				.done(function (dataresponse) {
					var obj = JSON.parse(dataresponse);
					if(obj.error ){
						loading();
						swal({
							title: obj.mensaje ,
							html: true,
							type: "info",
							closeOnConfirm: true
						}, function () {
							swal.close();
						})
						return false;
					}
					window.open(obj.path);					
					table_dt_concar_boveda.ajax.reload();
					loading();
				})
		}
		function sec_caja_get_depositos_hermeticase() {
			loading(true);
			$(".item_config").each(function (index, el) {
				var config_index = $(el).attr("name");
				var config_val = $(el).val();
				var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
				localStorage.setItem(ls_index, config_val);
				item_config[config_index] = config_val;
			});
			var get_data = jQuery.extend({}, item_config);
			$.post('/sys/get_caja_hermeticase.php', {
				"sec_caja_depositos_hermeticase": get_data
			}, function (r) {
				loading();
				try {
					$(".table_container").html(r);
					sec_caja_depositos_hermeticase_events();
					filter_caja_depositos_hermeticase_table()
				} catch (err) {
				}
			});
		}

		function sec_caja_depositos_hermeticase_events() {
			console.log("sec_caja_depositos_hermeticase_events");
			$(".btn-conciliar_hermeticase").off().on("click", function () {
				sec_caja_hermeticase_get_depositosPreValidacion();
			});
			$("#btnSaveConciliaciones_hermeticase").off().on('click', function (event) {
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
						"caja_id": $(this).closest('tr').find("#caja_id").html(),
						"id_transaccion": $(this).closest('tr').find("#id_transaccion").html(),
						"tipo": 0
					});
				});

				auditoria_send({"proceso":"sec_caja_hermeticase_conciliar_automatico","data":data});
				$.post('/sys/get_caja_hermeticase.php', {
					"sec_caja_hermeticase_conciliar_automatico": data
				}, function (response) {
					console.log(response)
					auditoria_send({"proceso": "hermeticase_auto_conciliar", "data": data});
					$("#modal_prevalidacion").modal("hide");
					setTimeout(function () {
						loading();
						sec_caja_get_depositos_hermeticase();
					}, 1000);
				});

			});

			$("#btnRemoveConciliacion").off().on('click', function (event) {
				event.preventDefault();
				var data = {};
				data.where = "sec_quitarDeposito";
				data.idexcel = $("#txtId").text();
				data.caja_id = $("#txtCajaId").val();
				data.monto = $("#txtMonto").val();
				data.mvto = $("#txtMvto").val();
				swal({
						title: "Está Seguro de quitar Depósito?",
						text: "Id Transacción : <strong>" + data.mvto + "</strong><br> Monto : <strong>" + data.monto + "</strong>",
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
								url: "sys/sys_exceldepositoshermeticase_sugerencia.php",
							})
								.done(function (dato, textStatus, jqXHR) {
									auditoria_send({"proceso": "caja_hermeticase_remover_deposito", "data": data});
									swal.close();
									sec_caja_get_depositos_hermeticase();
									$("#mdDetalleConciliacion").modal("hide");
								});
						}
					});
			});

			$('#btnSelectConciliacion').off().on('click', function (event) {
				event.preventDefault();
				if ($('[id="chkTransacciones"]:checkbox:checked').length > 0) {
					loading(true);
					$('[id="chkTransacciones"]:checkbox:checked').each(function (i) {
						var data = {};

						data.where = "sec_relacionar";
						data.mvto = $(this).closest('tr').find('#numero_movimiento').html();
						data.idcaja = $("#txtDefId").html();
						data.tipo = $("#txtDefTipo").text();
						data.idexcel = $(this).closest('tr').find('#id').html();
						auditoria_send({"proceso": "sec_depositos_relacionar", "data": data});
						$.ajax({
							data: data,
							type: "POST",
							url: "sys/sys_exceldepositoshermeticase_sugerencia.php",
						})
							.done(function (dato, textStatus, jqXHR) {
								var obj = dato;
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
						sec_caja_get_depositos_hermeticase();
					}, 1000);
				}
			});
			$('[id=btnCheckDeposito]').off().on("click", function (event) {
				event.preventDefault();
				loading(true);
				var get_data = jQuery.extend({}, item_config);
				get_data.dataset = $(this)[0].dataset;

				rowInfo = {
					"fecha": $(this).closest('tr').find('#tblFecha').html(),
					"cc": $(this).closest('tr').find('#tblCC').html(),
					"local": $(this).closest('tr').find('#tblLocal').html(),
					"turno": $(this).closest('tr').find('#tblTurno').html(),
					"venta": $(this).closest('tr').find('#tblMonto').html(),
					"boveda": $(this).closest('tr').find('#tblBoveda').html()
				}

				if ($(this)[0].dataset.cajaid == null) {
					var data = {};

					data.where = "sec_excel_depositohermeticasesugerencias";
					data.local_id = $(this).closest('tr').attr("data-local_id");
					data.fecha = $(this).closest('tr').find("#tblFecha").html();
					if (get_data.dataset.tipo == 0)
						data.monto = $(this).closest('tr').find("#tblMonto").html().replace(',', '');
					else
						data.monto = $(this).closest('tr').find("#tblBoveda").html().replace(',', '');
					data.total = $(this).closest('tr').find("#tblTotal").html().replace(',', '');
					data.cct = $(this).closest('tr').find("#tblCC").html();

					auditoria_send({"proceso": "sec_depositos_hermeticase_sugerencia", "data": data});
					$.ajax({
						data: data,
						type: "POST",
						url: "sys/sys_exceldepositoshermeticase_sugerencia.php",
					})
						.done(function (response, textStatus, jqXHR) {
							response = JSON.parse(response);
							var tbody = $('#tbConciliacionFilter tbody');
							tbody.html("");
							$.each(response, function (index, sugerencias) {
								var tr = $('<tr onclick="selectRow(this)">');
								$.each(sugerencias, function (i, field) {
									var clase_td = "text-left";
									if( i == "monto"){
										clase_td = "text-right";
									}
									if( i == "id_transaccion"){
										clase_td = "text-center";
									}
									$('<td id="' + i + '" class = "' + clase_td+ '">').html(field).appendTo(tr);
								});
								$('<td>').html('<div class="checkbox"><label><input type="checkbox" id="chkTransacciones" name="chkTransacciones" class="form-check-input"><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span><label</div>').appendTo(tr);
								tbody.append(tr);
							});
							$("#txtDefId").html(get_data.dataset.id);
							$("#txtDefTipo").html(get_data.dataset.tipo);
							$("#txtDefConcFecha").val(rowInfo.fecha);
							$("#txtDefConcCC").val(rowInfo.cc);
							$("#txtDefConcLocal").val(rowInfo.local);
							$("#txtDefConcTurno").val(rowInfo.turno);
							$("#txtDefConcVenta").val(rowInfo.venta);
							$("#txtDefConcBoveda").val(rowInfo.boveda);
							$("#txtConciliacionFilter").val("");
							loading();
							$("#mdDefinirConciliacion").modal("show");
						})
						.fail(function (jqXHR, textStatus, errorThrown) {
							loading();
							console.log("La solicitud usuarios a fallado: " + textStatus);
						})
				} else {
					loading();
					$("#txtCajaId").val(get_data.dataset.cajaid);
					$("#txtId").text(get_data.dataset.id);
					$("#txtFecha").val(get_data.dataset.fecha);
					$("#txtMonto").val(get_data.dataset.monto);
					$("#txtMvto").val(get_data.dataset.mvto);
					$("#nro_op").val(get_data.dataset.nro_op);
					$("#txtReferencia").val(get_data.dataset.referencia);
					if (get_data.dataset.tipo == 0) $("#txtTipo").val("Venta");
					else $("#txtTipo").val("Boveda");
					$("#mdDetalleConciliacion").modal("show");
				}
			});
			$('#modal_prevalidacion').off().on('hidden.bs.modal', function () {
			});

			$('#mdDefinirConciliacion').off().on('shown.bs.modal', function () {
				$('#txtConciliacionFilter').focus();
			});

			$("#btnFilterConciliadosHermeticase").off().on('click', function (event) {
				event.preventDefault();
				if ($(this).text().indexOf("Filtrar") == 1) {
					$(this).html('<i id="icoFilterConciliadosHermeticase" class="glyphicon glyphicon-remove-sign"></i> Remover Filtro');
					$(this).removeClass('btn-default');
					$(this).addClass('btn-danger');
					$("#tbl_caja_depositos_hermeticase tbody tr").filter(function () {
						$(this).toggle(parseFloat($(this).find(".td_diferencia").html()) > 0 );
					});
				} else {
					$(this).html('<i id="icoFilterConciliadosHermeticase" class="glyphicon glyphicon-filter"></i> Filtrar Conciliados');
					$(this).removeClass('btn-danger');
					$(this).addClass('btn-default');
					$("#tbl_caja_depositos_hermeticase tbody tr").filter(function () {
						$(this).toggle(true);
					});
				}
			});


			$("#chkConciliacionesAll").on('change', function(){
				$(".chkConciliaciones").prop('checked', $(this).prop('checked'));
			})
		}

		let filter_caja_depositos_hermeticase_table = () => {
			if (!$("#tbl_caja_depositos_hermeticase").length) return
			let cantidad = {
				venta_inicio : 0,
				venta_fin : 0,
				boveda_inicio : 0,
				boveda_fin : 0
			}

			$("#tbl_caja_depositos_hermeticase tbody tr").filter(function() {
				let ventaShow = (cantidad.venta_inicio || cantidad.venta_fin) ? is_filter_amount_valid($(this).children("td"), cantidad, "venta") : true
				let bovedaShow = (cantidad.boveda_inicio || cantidad.boveda_fin) ? is_filter_amount_valid($(this).children("td"), cantidad, "boveda") : true
				$(this).toggle(ventaShow && bovedaShow)
			})

			let switch_sate = $("#terminales_switch").prop('checked')
			if (switch_sate) $('.terminales-hide').hide()
			else $('.terminales-hide').show()
		}

		function sec_caja_hermeticase_concar_datatable(){
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
					html += ' <button id="deleteConcarHistory" data-id="' + row["id"] + '" class="btn btn-xs btn-danger"><i class="fa fa-close"></i></button>';
					return html;
				}
			});

			table_dt_concar = $('#sec_caja_hermeticase_tblConcarHistorico').DataTable
			(
				{
					"bDestroy": true,
					scrollX : true,
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
					"bDeferRender": 0,
					"pageLength": 10,
					serverSide: true,
					"lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
					"order": [[ 7, "desc" ]],
					//paging:false,
					columns : columnas,
					ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
						datat.opt = "sec_caja_hermeticase_get_concar";
						ajaxrepitiendo = $.ajax({
							type: "POST",
							url: "/sys/get_caja_hermeticase.php",
							data: datat,
							beforeSend: function () {
							},
							complete: function () {
								setTimeout(function(){
									table_dt_concar.columns.adjust();
								},200);
							},
							success: function (datos) {
								var respuesta = JSON.parse(datos);
								callback(respuesta);
							},
						});
					},
					"initComplete": function (settings, json) {
					}			
				}
			);
			loading();
		}

		function sec_caja_hermeticase_concar_boveda_datatable(){
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
					html += ' <button id="deleteConcarBovedaHistory" data-id="' + row["id"] + '" class="btn btn-xs btn-danger"><i class="fa fa-close"></i></button>';
					return html;
				}
			});

			table_dt_concar_boveda = $('#sec_caja_hermeticase_concar_boveda_tblConcarHistorico').DataTable
			(
				{
					"bDestroy": true,
					scrollX : true,
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
					"bDeferRender": 0,
					"pageLength": 10,
					serverSide: true,
					"lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
					"order": [[ 7, "desc" ]],
					//paging:false,
					columns : columnas,
					ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
						datat.opt = "sec_caja_hermeticase_get_concar_boveda";
						ajaxrepitiendo = $.ajax({
							type: "POST",
							url: "/sys/get_caja_hermeticase.php",
							data: datat,
							beforeSend: function () {
							},
							complete: function () {
								setTimeout(function(){
									table_dt_concar_boveda.columns.adjust();
								},200);
							},
							success: function (datos) {
								var respuesta = JSON.parse(datos);
								callback(respuesta);
							},
						});
					},
					"initComplete": function (settings, json) {
					}			
				}
			);
			loading();
		}

		function sec_caja_hermeticase_get_depositosPreValidacion() {
			console.log("sec_caja_hermeticase_get_depositosPrevalidacion");
			loading(true);
			$(".item_config").each(function (index, el) {
				var config_index = $(el).attr("name");
				var config_val = $(el).val();
				var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
				localStorage.setItem(ls_index, config_val);
				item_config[config_index] = config_val;
			});
			var get_data = jQuery.extend({}, item_config);
			get_data.is_terminal = $("#terminales_switch").prop('checked');

			auditoria_send({"proceso":"sec_caja_hermeticase_Prevalidacion","data":get_data});
			$.post('/sys/get_caja_hermeticase.php', {
				"sec_caja_hermeticase_Prevalidacion": get_data
			}, function (response) {
				response = JSON.parse(response);
				var tbodyVenta = $('#tbConciliarMatchesVenta tbody');
				tbodyVenta.html("");

				var props = []
				$.each(response, function (index, conciliaciones) {
					if (conciliaciones.transacciones_hermeticase != undefined ) {
						if(conciliaciones.transacciones_hermeticase.length > 0 ) {
							for (let i = 0; i < conciliaciones.transacciones_hermeticase.length; i++) {
								var tr = $('<tr onclick="selectRow(this)" style="background-color: #e2ffe7">');
								$('<td id="fecha_operacion" class="text-center">').html(conciliaciones.fecha_operacion).appendTo(tr);
								$('<td id="cc_id">').html(conciliaciones.cc_id).appendTo(tr);
								$('<td id="local_nombre">').html(conciliaciones.local_nombre).appendTo(tr);
								$('<td id="turno_id">').html(conciliaciones.turno_id).appendTo(tr);
								$('<td id="importe" class="text-right">').html(conciliaciones.total).appendTo(tr);
								$('<td id="nro_doc" class="text-right">').html(conciliaciones.mov_nro_doc).appendTo(tr);
								$('<td id="depo_fecha" class="text-center">').html(conciliaciones.transacciones_hermeticase[i].fecha_inicio).appendTo(tr);
								$('<td id="depo_nro_operacion" class="text-right">').html(conciliaciones.transacciones_hermeticase[i].nro_operacion).appendTo(tr);
								$('<td id="depo_importe" class="text-right">').html(conciliaciones.transacciones_hermeticase[i].monto).appendTo(tr);
								$('<td id="depo_movimiento" class="text-center">').html(conciliaciones.transacciones_hermeticase[i].id_transaccion).appendTo(tr);
								$('<td class="text-center">').html('<div class="checkbox"><label><input type="checkbox" id="chkConciliaciones" checked="checked" name="chkConciliaciones" class="form-check-input chkConciliaciones"><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span><label</div>').appendTo(tr);

								$('<td style="display:none;" id="caja_id">').html(conciliaciones.id).appendTo(tr);
								$('<td style="display:none;" id="id_transaccion">').html(conciliaciones.transacciones_hermeticase[i].id).appendTo(tr);
								tbodyVenta.append(tr);
							}
						}
					}
				});
				loading();
				$("#modal_prevalidacion").modal("show");
			});
		}

		function sec_caja_hermeticase_get_locales() {
			let select = $("[name='sec_caja_hermeticase_local_id']");
			let valorSeleccionado = $("#sec_caja_hermeticase_local_id").val();
		
			$.ajax({
				url: "/sys/get_caja_reporte.php",
				type: "POST",
				data: {
					accion: "sec_caja_reporte_obtener_locales"
				},
				success: function (datos) {
					var respuesta = JSON.parse(datos);
					$(select).empty();
					if (!valorSeleccionado) {
						let opcionDefault = $('<option value="_all_">Todos (Puede demorar)</option>');
						$(select).append(opcionDefault);
					}
					$(respuesta.result).each(function (i, e) {
						let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
						$(select).append(opcion);
					});
		
					// Seleccionar el primer local por defecto
					$(select).prop('selectedIndex', 0);
		
					if (valorSeleccionado != null) {
						$(select).val(valorSeleccionado);
					}
				},
				error: function () {
					// Manejar el error si es necesario
				}
			});
		}

		function sec_caja_hermeticase_get_locales_concar() {
			let select = $("[name='sec_caja_hermeticase_concar_local_id']");
			let valorSeleccionado = $("#sec_caja_hermeticase_concar_local_id").val();
		
			$.ajax({
				url: "/sys/get_caja_reporte.php",
				type: "POST",
				data: {
					accion: "sec_caja_reporte_obtener_locales"
				},
				success: function (datos) {
					var respuesta = JSON.parse(datos);
					$(select).empty();
					if (!valorSeleccionado) {
						let opcionDefault = $('<option value="_all_">Todos (Puede demorar)</option>');
						$(select).append(opcionDefault);
					}
					$(respuesta.result).each(function (i, e) {
						let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
						$(select).append(opcion);
					});
		
					// Seleccionar el primer local por defecto
					$(select).prop('selectedIndex', 0);
		
					if (valorSeleccionado != null) {
						$(select).val(valorSeleccionado);
					}
				},
				error: function () {
					// Manejar el error si es necesario
				}
			});
		}

		function sec_caja_hermeticase_get_locales_concar_boveda() {
			let select = $("[name='sec_caja_hermeticase_concar_boveda_local_id']");
			let valorSeleccionado = $("#sec_caja_hermeticase_concar_boveda_local_id").val();
		
			$.ajax({
				url: "/sys/get_caja_reporte.php",
				type: "POST",
				data: {
					accion: "sec_caja_reporte_obtener_locales"
				},
				success: function (datos) {
					var respuesta = JSON.parse(datos);
					$(select).empty();
					if (!valorSeleccionado) {
						let opcionDefault = $('<option value="_all_">Todos (Puede demorar)</option>');
						$(select).append(opcionDefault);
					}
					$(respuesta.result).each(function (i, e) {
						let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
						$(select).append(opcion);
					});
		
					// Seleccionar el primer local por defecto
					$(select).prop('selectedIndex', 0);
		
					if (valorSeleccionado != null) {
						$(select).val(valorSeleccionado);
					}
				},
				error: function () {
					// Manejar el error si es necesario
				}
			});
		}
		
		
	}
}
