function sec_adm_tipo_cambio(){
	if(sec_id=="adm_tipo_cambio"){
		console.log("sec:adm_tipo_cambio");
		sec_adm_tipo_cambio_events();
		cargar_tabla_tipo_cambio();
	}
}

function sec_adm_tipo_cambio_events(){
	$("#btn_actualizar").off("click").on("click",function(){
		cargar_tabla_tipo_cambio();
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
			sec_adm_tipo_cambio_save(btn);
		});

	$(document).on("click",".tipo_cambio_historial",function(){
		id = $(this).attr("data-tipo_cambio_id");		
		cargar_tabla_tipo_cambio_historial(id);
	})
	$(".validar_numerico").validar_numerico_decimales({decimales : 3});
	$("#panel-datos_2 input:visible:not('.fechas'):first").focus();
}

function sec_adm_tipo_cambio_historial(id){
	var set_data = {
			sec_adm_tipo_cambio_historial :'sec_adm_tipo_cambio_historial',
			id : id
	};
	loading(true);
	$.post('/sys/set_adm_tipo_cambio.php', {
		"sec_adm_tipo_cambio_historial": set_data
	}, function(r) {
		loading();
		try{
			loading();
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_adm_tipo_cambio_historial_error","data":set_data});
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
				auditoria_send({"proceso":"sec_adm_tipo_cambio_historial","data":set_data});
				$("#modal_historial_tipo_cambio").modal("show");
			}
		}catch(err){
			loading();
		}
	});
}

function sec_adm_tipo_cambio_save(btn){
	var set_data = {};
	$(".save_data").each(function(index, el) {
		set_data[$(el).attr("name")]=$(el).val();
	});
	loading(true);

	$.post('/sys/set_adm_tipo_cambio.php', {
		"sec_adm_tipo_cambio_save": set_data
	}, function(r) {
		try{
			loading();
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_adm_tipo_cambio_save_error","data":set_data});
				swal_msg({	type : "warning" , 
							text : obj.error_msg ,
							title : "Error!",
							callback:function(){
										swal.close();
										custom_highlight($(".save_data[name='"+obj.error_focus+"']"));
										setTimeout(function(){
											$(".save_data[name='"+obj.error_focus+"']").val("").focus();	
										}, 10);
									}
						});
			}else{
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_adm_tipo_cambio_save_done","data":set_data});
				swal({type : "success" , text : obj.mensaje , title : "Guardado!"},
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
				})

			}
		}catch(err){
			loading();
			// console.log(r);
		}
	});
}

function cargar_tabla_tipo_cambio(){
	set_data = { sec_tipo_cambio_list : 1 };
    $.ajax({
        url: "/sys/set_adm_tipo_cambio.php",
        data: set_data,
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
			var data_meses = resp.lista_meses;

			set_data.curr_login = resp.curr_login;
			auditoria_send({"proceso":"sec_tipo_cambio_list","data":set_data});

   			tabla_tipo_cambio = $('#tbl_datos').DataTable( {
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
				 sDom:"<'row'<'col-sm-3'l><'col-sm-3 div_select_mes'><'col-sm-2 div_select_year'><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
    		    "initComplete": function (settings, json) {
					var sele = $('<select name="mes_select" id="mes_select" class="form-control input-sm" style="width:80%"></select>');
					sele.append($('<option value="">Seleccione Mes</option>'))
					for (var i = 0; i < data_meses.length; i++) {
						sele.append($('<option value="' + data_meses[i].nombre + '">' + data_meses[i].nombre + '</option>'));
					}
					$(".div_select_mes").append("Mes  ");
					$(".div_select_mes").append(sele);

					$("#mes_select").off("change").on("change",function(){
						var val=$(this).val();
						tabla_tipo_cambio.column(2).search(val).draw();
						tabla_tipo_cambio.columns.adjust();
					})

					var sele=$('<select name="year_select" id="year_select" class="form-control input-sm" style="width:80%"></select>')
							.append($('<option value="">Seleccione</option>'))
							.append($('<option value="2022">2022</option>'))
					$(".div_select_year").append("Año  ");
					$(".div_select_year").append(sele);

					$("#year_select").off("change").on("change",function(){
						var val=$(this).val();
						tabla_tipo_cambio.column(3).search(val).draw();
						tabla_tipo_cambio.columns.adjust();
					})
                },
                order:[],
    			columns: [
    				{
						title: "Id",
						data: "id"
					},
					{
						title: "Moneda",
						data: "moneda_nombre",
						class: "text-left"


					},
					{
						title: "mes",
						"render": function (data, type, row) {
							var mes=  parseInt(row["mes"]);
							mes=data_meses[mes-1].nombre ;
							return mes;
						},
						visible : false
					},
					{
						title: "year",
						data: "year",
						visible : false
					},
					{
						title: "Fecha",
						data: "fecha",
						class: "text-left"

					},
					{
						title: "Venta",
						data: "monto_venta",
						class: "text-left"

					},
					{
						title: "Compra",
						data: "monto_compra",
						class: "text-left"

					},
					{
						title: "Usuario",
						data: "usuario_updated",
						class: "text-left"

					},
					{
						title: "Hora",
						data: "updated_at",
						class: "text-left"

					},
					{
						title: "Opciones",
						width:"150px",
						"render":function(data,type,row){
							var id=row["id"];
							var estado_nombre=row["estado_nombre"];
							var html="<div class='text-center'>";
							var btn_class="btn btn-sm btn-success tipo_cambio_historial";
							
							html+="<button class='"+btn_class+"' data-tipo_cambio_id='"+id+"' >";
							html+="Historial</button>";	
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
			auditoria_send({"proceso":"sec_tipo_cambio_list","data":set_data});
        }
    });
}

function cargar_tabla_tipo_cambio_historial(id){
	set_data = { sec_tipo_cambio_historial_list : 1 
		,id : id};
    $.ajax({
        url: "/sys/set_adm_tipo_cambio.php",
        data: set_data,
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
			auditoria_send({"proceso":"sec_tipo_cambio_historial_list","data":set_data});

   			tabla_tipo_cambio_historial = $('#tbl_tipo_cambio_historial').DataTable( {
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
    		    	$("#modal_historial_tipo_cambio").modal("show");
                },
                order:[],
    			columns: [
    				{
						title: "Moneda",
						data: "moneda_nombre",
						/*class:"text-right"*/
					},
					{
						title: "Venta",
						data: "monto_venta",
					},
					{
						title: "Compra",
						data: "monto_compra",
					},
					{
						title: "Fecha",
						data: "created_at",
					},
					{
						title: "Usuario",
						data: "usuario",
					},
				],
    		} );
        },
        error: function () {
    		set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"sec_tipo_cambio_historial_list","data":set_data});
        }
    });
}
/*swal_msg({type : "error" , text : obj.mensaje , title : "Error"});*/
/*swal_msg({mensaje:"default"})*/
function swal_msg(opc){
	defaults = {
	 title : "Registro"
	,text : ""
	,type : "success"
	,timer : 8000
	,callback :  function (){
					swal.close();
				}
	};  
	opciones = $.extend(defaults,opc);
	swal({
		title : opciones.title,
		text : opciones.text,
		type : opciones.type,
		timer : opciones.timer,
		closeOnConfirm : true
	},
	function(){
		defaults.callback();
	}
	);
}


function filtro_numerico_dec(__val__,decimales = 2) {
	var punto = "\.";
	if(decimales == 0){
		punto = "";
	}
	var preg = new RegExp("^-?([0-9]+"+punto+"?[0-9]{0,"+decimales+"})$","i") ;
	if (preg.test(__val__) === true) {
		return true;
	} else {
		return false;
	}
}
function validar_input_float_dec(evt, input,decimales) {
    var key = window.Event ? evt.which : evt.keyCode;
	var chark = String.fromCharCode(key);
    var posicion = input.selectionStart;
    var tempValue = [(input.value).slice(0, posicion), chark, (input.value).slice(posicion)].join('');

    if (key >= 48 && key <= 57 || key ==45 ) {
		if(key == 45){
			if(posicion==0){
				return true;
			}
			else{
				return false;
			}
		}
		if (filtro_numerico_dec(tempValue,decimales) === false) {
			return false;
		}
		else {
			return true;
		}
    } else {
        if (key == 8 || key == 13 || key == 0) {
            return true;
        } else if (key == 46) {
            if (filtro_numerico_dec(tempValue,decimales) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}

(function( $ ) {
    $.fn.validar_numerico_decimales = function(opc) {
		defaults = {
			decimales : 2
		};
		opciones = $.extend(defaults,opc);

        return this.each(function() {
			var $this = $(this);
			$this.on('keypress', function () {
				return validar_input_float_dec(event, this,opciones.decimales);
			});
		    $this.on('paste', function (e) {
				var data = e.originalEvent.clipboardData.getData('Text');
				var input = $(this)[0];
				var posicion = input.selectionStart;

				var tempValue = [(input.value).slice(0, posicion), data, (input.value).slice(posicion)].join('');
				if(input.selectionStart == 0 && input.selectionEnd == input.value.length){//all selected
					tempValue = data;
				}
				if (filtro_numerico_dec(tempValue,opciones.decimales) === false) {
					e.preventDefault();//no paste
				}
			})
		});
    };
}( jQuery ));