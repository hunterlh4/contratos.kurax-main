
function sec_aprobacion_registros(){	
	if(sec_id=="aprobacion_registros") {
		console.log("sec:aprobacion_registros");
		sec_aprobacion_registro_events();
	}
}

function sec_aprobacion_registros_estado(objeto){
		set_data=objeto;
		loading(true);

		$.post('/sys/set_aprobacion_registros.php', {
		"set_registro_aprobacion": set_data
		}, function(r) {
			set_data.curr_login = r.curr_login;
			auditoria_send({"proceso":"set_aprobacion_registros","data":set_data});
			try{
				obj = jQuery.parseJSON(r);
				console.log(obj);
				if(obj.error){
					if(obj.recargar){window.location.reload();}
					swal_modal({title:"Error" ,text:mensaje, type:"error" });
					return false;
				}
				id=obj.login_id;
				mensaje= obj.mensaje;
				tipo= obj.swaltipo;
				loading();
				swal_modal({ title:mensaje,text:"", type:tipo })
			
				tablaserver.ajax.reload(null,false);
			} catch (e) {
				console.log(r);
				loading();
				swal_modal({ title:"ERROR",text:r, type:"error" });
			 
				return false;
			}

		})
		.fail(function(xhr, status, error) {
			loading();
			swal.close();
		});

}


function sec_aprobacion_registro_events(){

	var modal_foto=$("#fotoModal");
	$(".imgFoto",modal_foto).css("display","flex").css("align-items","center").css("justify-content","center").css("margin-bottom","10px").css("height","517px");//.css("width","570px")
	$(document).on("click", "#tbl_aprobacion_registros .btn-adjuntos", function()
	{
		id=$(this).attr("data-id");
		estado=$(this).attr("data-estado");
		cliente=$(this).attr("data-cliente");
		tipo_doc=$(this).attr("data-tipo_doc");
		nro_doc=$(this).attr("data-nro_doc");
		cantidad=$(this).attr("data-cantidad");
		if(cantidad==0){
			swal_modal({ text:"No hay Adjuntos", type:"warning" })
			return false;
		}
		var texto_titulo_modal=cliente+" - "+tipo_doc+" - "+nro_doc+" - "+estado;
		$("#fotoModal .modal-title").text(texto_titulo_modal);
		cargaAdjuntos_abrirmodal(id,modal_foto);
	})

	modal_foto.on("hidden.bs.modal",function(){
		$('#miniatura',modal_foto).html(" ");
		$("#imgInp").val('');
		deleteImg();
		$('.formUpload').css('display', 'block');
	})

	$('#miniatura',modal_foto).on('click', '.mini', function () {
		if($(this).prop('nodeName')=="A"){
			$(".imgFoto").hide();
		}else{
			$(".imgFoto").css('display','flex');
			var src = $(this).attr('src');
			src = src.replace('min_', '');
			$('#previewImg').attr('src', src);
		}
		
	});

	$(document).on("click", "#tbl_aprobacion_registros .btn-vercliente", function()
	{
		cliente=$(this).attr("data-cliente");
		ClientId=$(this).attr("data-clienteid");
		estado=$(this).attr("data-estado");
		register_id=$(this).attr("data-register_id");

		var texto=$(this).attr("data-texto");
		var id=$(this).attr("data-id");
		/*$("#modal_ver .modal-title").text(cliente+" - "+estado);
		$("#modal_ver #vercontenido").text(texto);
		$("#modal_ver").modal("show");*/
		swal_modal({ title:ClientId,text:cliente+" - "+estado, type:"info" ,timer:12000})


	});

	   $(document).on("click", "#tbl_aprobacion_registros .btn-vermotivo", function()
	{
		cliente=$(this).attr("data-cliente");
		estado=$(this).attr("data-estado");
		register_id=$(this).attr("data-register_id");
		var texto=$(this).attr("data-texto");
		var id=$(this).attr("data-id");
		/*$("#modal_ver .modal-title").text(cliente+" - "+estado);
		$("#modal_ver #vercontenido").text(texto);
		$("#modal_ver").modal("show");*/
		swal_modal({ title:texto,text:cliente+" - "+estado, type:"info" ,timer:12000})


	});

	$(document).on("click", "#tbl_aprobacion_registros .btn-aprobar", function()
	{
		id=$(this).attr("data-id");
		register_id=$(this).attr("data-register_id");

		estado=$(this).attr("data-estado");
		cliente=$(this).attr("data-cliente");
		var correo=$(this).attr("data-correo");
		var objeto={
			id:id
			,register_id:register_id
			,estado:estado
			,cliente:cliente
			,correo:correo

		};
		//    swal({
		//     title: '<span style="font-size:16px">'+cliente+'</span> ',
		//     text: "Está seguro que desea Aprobar? ",
		//     html: true,
		//     type: "warning",
		//     showCancelButton: true,
		//     confirmButtonColor: "#DD6B55",
		//     confirmButtonText: "Si",
		//     cancelButtonText:"No",
		//     closeOnConfirm: false,
		//     closeOnCancel: false
		// },
		// function(opt){
		//     if(opt){
				sec_aprobacion_registros_estado(objeto);
		//     }else{
		//       swal.close();
		//     }

		// });

	});
	$(document).on("click", "#tbl_aprobacion_registros .btn-desaprobar", function()
	{
		id=$(this).attr("data-id");
		estado=$(this).attr("data-estado");
		cliente=$(this).attr("data-cliente");
		register_id=$(this).attr("data-register_id");
		var correo=$(this).attr("data-correo");
		var objeto={
			id:id
			,register_id:register_id
			,estado:estado
			,cliente:cliente
			,correo:correo
		};
		swal({
			title: '<span style="font-size:16px">'+cliente+'</span>'+'<br><span style="font-size:14px">Motivo :</span> <textarea autofocus id="txtMotivo" name="txtMotivo" class="form-control" style="display:block;font-size:14px;margin-top: -10px;"></textarea>',
			text: "Está seguro que desea rechazar? ",
			html: true,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Si",
			cancelButtonText:"No",
			closeOnConfirm: false,
			closeOnCancel: false
		},
		function(opt){
			if(opt){
				var motivo=$("#txtMotivo").val();
				objeto.motivo=motivo;
				if(motivo==""){
					$("#txtMotivo").on("keyup",function(){$(this).css("border","1px solid")});
					$("#txtMotivo").css("border","1px solid red");
					$("#txtMotivo").focus();
					//swal_modal({ text:"Ingresar Motivo", type:"warning" })
					return false;
				}
				sec_aprobacion_registros_estado(objeto);
			}else{
			  swal.close();
			}

		});
		setTimeout(function(){
			$("#txtMotivo").focus();
		},600) 

	})
	tablaserver=listar_registros_clientes();
}


function swal_modal(opc){
	defaults={
	 title:"Registro"
	,text:""
	,type:"info"
	,timer:8000
	};  
	opciones=$.extend(defaults,opc);
	swal({
		title: opciones.title,
		text: opciones.text,
		type: opciones.type,
		timer: opciones.timer,
		closeOnConfirm: true
	},
	function(){
		swal.close();
	});

}


 function deleteImg() {
		let src = 'images/default_avatar.png';
		$('#miniatura').find('img').remove();
		$('#previewImg').attr('src', src);
		let html = "<i class='fa fa-picture-o' aria-hidden='true'></i> <span id='leyenda'>Elegir imágenes</span>";
		$('#labelbtn').html('');
		$('.labelbtn').html(html);
	}
function cargaAdjuntos_abrirmodal(id,modal_foto) {
		var data = {};
		data.id = id;
		var ubicacion="../files_bucket/atregistro/";
		//var ubicacion="../images/";
		set_data=data;

		$.post("sys/set_aprobacion_registros.php", {"get_archivos": data}, function (data) {
			loading(true);
			objeto = JSON.parse(data);

			set_data.curr_login = objeto.curr_login;
			auditoria_send({"proceso":"set_aprobacion_registros_get_archivos","data":set_data});
			 //result=result.lista;
			result=objeto.archivos;
			let i = result.length;
			var modal_foto=$("#fotoModal");
			for (let x = 0; x < i; x++) {
				if (typeof (result[x]['archivo']) != 'undefined') {
					nombre_archivo=result[x]['archivo'];
					ext=nombre_archivo.substring(nombre_archivo.indexOf(".")+1);
					if(ext=="pdf"){
						$('#miniatura',modal_foto).append("<a class='mini' title="+nombre_archivo+" target='_blank' href='"+ubicacion + nombre_archivo + "'><img style='width:50px;height:50px' class='' src='images/pdf_file.png'" + nombre_archivo + "' /></a>");
					}else{
						$('#miniatura',modal_foto).append("<img  style='width:50px;height:50px' class='mini' src='"+ubicacion+"" + nombre_archivo + "' />");
					}
				}
			}

			if (typeof (result[0]) != 'undefined') {
				nombre_archivo=result[0]['archivo'];
				ext=nombre_archivo.substring(nombre_archivo.indexOf(".")+1);

				if(ext=="pdf"){                    
					$('.imgFoto',modal_foto).hide();
				}
				else{
					$('.imgFoto',modal_foto).show();
					$('#previewImg',modal_foto).attr('src', ubicacion + result[0]['archivo']);

				}
			}

			var fadein = $(this).hasClass('in');
			if (fadein == false) {
				modal_foto.modal('show');

			} else {
				modal_foto.modal('hide');
				$('#miniatura',modal_foto).html(" ");
			}
			loading(false);


		}).always(function(){
			loading(false);
		}).fail(function(){
			alert("error");
		});;
	};



function listar_registros_clientes(){
	tablaserver=$("#tbl_aprobacion_registros")
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
				//"scrollY": "450px",
			 //   "scrollCollapse": false,
				"bProcessing": true,
				'processing': true,
			   // "sScrollXInner":'100%',
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
				//processing: true,
				"columnDefs":[],
				 sDom:"<'row'<'col-sm-4'l><'col-sm-4 div_select_estado'><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
				//sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
				 //sDom: 'lfrtip',
			   buttons: [
					{
						text: '<span class="glyphicon glyphicon-refresh"></span>',
						action: function ( e, dt, node, config ) {
							tablaserver.ajax.reload(null,false);
						}
					}
				],
				ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
					datat.sec_aprobacion_registros_list=true;
					 datat.estado_select=$("#estado_select").val()||"Todos";
					ajaxrepitiendo = $.ajax({
						global: false,
						url: "/sys/set_aprobacion_registros.php",
						type: 'POST',
						data: datat,//+data,
						beforeSend: function () {
						},
						complete: function () {
							//responsive_tabla_scroll(tablaserver);
						},
						success: function (datos) {//  alert(datat)
							var respuesta = JSON.parse(datos);
							callback(respuesta);
						},
						error: function () {
						}
					});
				},

				columns: [
					{data:"register_id",nombre:"register_id",title:"Id" },
					{data:"created_at",nombre:"created_at",title:"Fecha Registro"},
					{data:"nombre",nombre:"nombre",title:"Nombre"},
					{data:"apellido",nombre:"apellido",title:"Apellido"},
					{data:"DocType",nombre:"DocType",title:"Tipo Doc."},
					{data:"DocNumber",nombre:"DocNumber",title:"Doc."},
					{data:"id",nombre:"id",title:"Adjunto"
						,"render": function (data, type, row ) {
							var estado=row["estado"];
							var cliente=row["Cliente"];
							var nombre=row["nombre"];
							var apellido=row["apellido"];var correo=row["Email"];
							var cantidad=row["cantidad"];
							var tipo_doc=row["DocType"];
							var nro_doc=row["DocNumber"];
							var id = data;
							var clase_btn=cantidad>0?'primary':'danger';       
							html="<button class='btn btn-sm btn-rounded btn-"+clase_btn+"  btn-adjuntos' data-estado="+estado+" data-cliente='"+nombre+" "+apellido+"' data-id="+id+" data-cantidad="+cantidad+" data-tipo_doc='"+tipo_doc+"' data-nro_doc='"+nro_doc+"'><i class='fa fa-camera' aria-hidden='true'></i>(" +cantidad+ ")</button>";
							
							return html;
						}
					},                    
					{data:"Email",nombre:"email",title:"Correo"},
					{data:"estado",nombre:"estado",title:"Estado"               },
					{data:"id",nombre:"id",title:" Acciones ", width:"150px"
						,"render": function (data, type, row ) {
							var register_id=row["register_id"];
							var estado=row["estado"];
							var cliente=row["Cliente"];
							var clienteid=row["ClientId"];
							var nombre=row["nombre"];
							var apellido=row["apellido"];
							var correo=row["Email"];
							var motivo=row["motivo_txt"];
							var id = data;
							if(estado=="Pendiente"){
								html="<button class='btn btn-sm btn-danger btn-rounded btn-desaprobar' data-register_id="+register_id+" data-id="+id+" data-estado=2 data-cliente='"+nombre+" "+apellido+"' data-correo="+correo+">Rechazar </button> ";
								html=html+"<button class='btn btn-sm btn-success btn-rounded btn-aprobar' data-id="+id+" data-register_id="+register_id+" data-estado=1 data-cliente='"+nombre+" "+apellido+"' data-correo="+correo+"> Aprobar</button>"; 
							}
							if(estado=="Rechazado"){
								html="<button class='btn btn-sm btn-primary btn-rounded btn-vermotivo' data-register_id="+register_id+" data-id="+id+" data-texto='"+motivo+"' data-estado="+estado+" data-cliente='"+nombre+" "+apellido+"'>Ver Motivo</button>";
							}
							if(estado=="Aprobado"){
								html="<button class='btn btn-sm btn-primary btn-rounded btn-vercliente' data-clienteid="+clienteid+ " data-id="+id+" data-register_id="+register_id+" data-texto='"+register_id+"' data-estado="+estado+" data-cliente='"+nombre+" "+apellido+"'>Ver Id</button>";
							}

							/*else{
								html="<button class='btn btn-sm text-success btn-default  btn-aprobar' data-id="+id+" data-estado=1><span class='glyphicon glyphicon-ok-circle'></span> Aprobar</button>"; 
							}*/
							return html;
						}
					},


					{data:"usuario",nombre:"usuario",title:"Agente"},
					{data:"update_user_at",nombre:"update_user_at",title:"Fecha Atención."}
				],
				"drawCallback":function(){
					$("#tbl_aprobacion_registros tbody tr td:nth-of-type(3)").css("cursor","pointer");
					$("#tbl_aprobacion_registros tbody tr td:nth-of-type(4)").css("cursor","pointer");
					$("#tbl_aprobacion_registros tbody tr td:nth-of-type(8)").css("cursor","pointer");
				},

				"initComplete": function (settings, json) {
					var sele=$('<select name="estado_select" id="estado_select" class="form-control input-sm" style="width:50%"></select>')
							.append($('<option value="Todos">Todos</option>'))
							.append($('<option value="Pendiente">Pendientes</option>'))
							.append($('<option value="Aprobado">Aprobados</option>'))
							.append($('<option value="Rechazado">Rechazados</option>'))
							.append($('<option value="Registrado">Registrado</option>'))
					$(".div_select_estado").append(sele);

					$("#estado_select").off("change").on("change",function(){
						var val=$(this).val();
						tablaserver.column(6).search(val).draw();
						tablaserver.columns.adjust();
					})



					///////actualizar datos cliente
	
	          		$('#tbl_aprobacion_registros tbody')
		          		.on( 'click', 'tr td:nth-of-type(3),tr td:nth-of-type(4),tr td:nth-of-type(8)', function (){
			   				var tr=$(this).closest("tr");

   							swal({
								title: 'Editar Cliente?',
								html: true,
								type: "warning",
								showCancelButton: true,
								confirmButtonColor: "#DD6B55",
								confirmButtonText: "Si",
								cancelButtonText:"No",
								closeOnConfirm: false,
								closeOnCancel: false
							},
							function(opti){
								if(opti){
				   					var at_web_registers_id=$("td:eq(0)",tr).text();
				   					var nombre=$("td:eq(2)",tr).text();
				   					var apellido=$("td:eq(3)",tr).text();
				   					var correo=$("td:eq(7)",tr).text();

									var html='<table class="table" id="tbl_datos_cliente_editar">';
									html+="<tbody>";
									html+="<tr>";
									html+='<td style="text-align:right;width:40%;border-bottom:0px !important"><strong>Nombre :</strong></td>';
									html+='<td style="width:60%;text-align:left;border-bottom:0px !important"><input autofocus style="display:block;margin:0px" type="text" id="nombre_txt" name="nombre_txt" value="'+nombre+'" class="form-control"></td>';
									html+="</tr>";
									html+="<tr>";
									html+='<td style="text-align:right;width:40%;border-bottom:0px !important "><strong>Apellido :</strong></td>';
									html+='<td style="width:60%;text-align:left;border-bottom:0px !important"><input style="display:block;margin:0px" type="text" id="apellido_txt" name="apellido_txt" value="'+apellido+'" class="form-control"></td>';
									html+="</tr>";
									html+="<tr>";
									html+='<td style="text-align:right;width:40%;border-bottom:0px !important"><strong>Correo :</strong></td>';
									html+='<td style="width:60%;text-align:left;border-bottom:0px !important"><input style="display:block;margin:0px" type="text" id="correo_txt" name="correo_txt" value="'+correo+'" class="form-control"></td>';
									html+="</tr>";
									html+="</tbody>";
									html+="</table>";
									swal({
										title: 'Editar  '+at_web_registers_id,
										text: html,
										html: true,
										type: "warning",
										showCancelButton: true,
										confirmButtonColor: "#DD6B55",
										confirmButtonText: "Guardar",
										cancelButtonText:"Cancelar",
										closeOnConfirm: false,
										closeOnCancel: false
									},
									function(opt){
										if(opt){
											var id=at_web_registers_id;
											var nombre=$("#nombre_txt").val();
											var apellido=$("#apellido_txt").val();
											var correo=$("#correo_txt").val();
											var objeto={}; 
											objeto.id=at_web_registers_id;
											objeto.nombre=nombre;
											objeto.apellido=apellido;
											objeto.correo=correo;
											actualizar_datos_cliente(objeto);
										}else{
											swal.close();
										}
									});
									setTimeout(function(){
										$("#nombre_txt",$("#tbl_datos_cliente_editar")).focus();
									},600) 

								}else{
									swal.close();
								}
							});
					});
					///////fin actualizar datos cliente

					//recarga_tabla();
					setTimeout(function(){
							$("#registros_recargar").off("click").on("click",function(){
								tablaserver.ajax.reload(null, false);
							})
							tablaserver.columns.adjust();
							//$('.dataTables_scrollHeadInner').css('width', '100%');
							//$('.dataTables_scrollHeadInner table').css('width', '100%');
							// agregar_scrolltop(tablaserver);
							//responsive_tabla_scroll(tablaserver);
					},200)
				}
			});
	return tablaserver;
}

function actualizar_datos_cliente(objeto){
		set_data=objeto;
		loading(true);
		$.post('/sys/set_aprobacion_registros.php', {
		"set_actualizar_datos_cliente": set_data
		}, function(r) {
			set_data.curr_login = r.curr_login;
			auditoria_send({"proceso":"set_actualizar_datos_cliente","data":set_data});
			try{
				obj = jQuery.parseJSON(r);
				console.log(obj);
				if(obj.error){
					if(obj.recargar){window.location.reload();}
					swal_modal({title:"Error" ,text:mensaje, type:"error" });
					return false;
				}
				id=obj.login_id;
				mensaje= obj.mensaje;
				tipo= obj.swaltipo;
				loading();
				swal_modal({ title:mensaje,text:"", type:tipo })
			
				tablaserver.ajax.reload(null,false);
			} catch (e) {
				console.log(r);
				loading();
				swal_modal({ title:"ERROR",text:r, type:"error" });
				return false;
			}

		})
		.fail(function(xhr, status, error) {
			loading();
			swal.close();
		});

}
