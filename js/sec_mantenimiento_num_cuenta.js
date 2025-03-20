var global_param_num_cuenta_id = 0;

//INICIO: FUNCIONES INICIALIZADOS
function sec_mantenimiento_num_cuenta()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mantenimiento_num_cuenta_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mantenimiento_num_cuenta_listar_cuenta_bancaria();
    sec_mantenimiento_num_cuenta_listar_proceso();
    sec_mantenimiento_num_cuenta_listar_tipo_pago();
    sec_mantenimiento_num_cuenta_listar_campo();
    sec_mantenimiento_num_cuenta_listar_moneda();
    sec_mantenimiento_num_cuenta_listar_banco();
    sec_mantenimiento_num_cuenta_listar_empresa();
    sec_mantenimiento_num_cuenta_listar_canal();


    $(document).ready(function() {
        $("#form_modal_sec_mantenimiento_num_cuenta_param_banco_id").change(function() {
            var selectedBanco = $(this).val();
            var $campoObligatorio = $(".sec_mantenimiento_form_num_cuenta_campo_obligatorio");
            
            if (selectedBanco !== "0") {
                $campoObligatorio.html('(*)').addClass("campo-obligatorio");
            } else {
                $campoObligatorio.text('').removeClass("campo-obligatorio");
            }
        });
    });
}
//FIN: FUNCIONES INICIALIZADOS

function sec_mantenimiento_num_cuenta_listar_cuenta_bancaria()
{
	if(sec_id == "mantenimiento" && sub_sec_id == "num_cuenta")
    {
        var data = {
            "accion": "mantenimiento_num_cuenta_cuenta_bancaria_listar"
        }

        tabla = $("#sec_mantenimiento_num_cuenta_div_listar_cuenta_bancaria_datatable").dataTable(
            {
                language:{
                    "decimal":        "",
                    "emptyTable":     "No existen registros",
                    "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered":   "",
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
                },
                "aProcessing" : true,
                "aServerSide" : true,

                "ajax" :
                {
                    url : "/sys/get_mantenimiento_num_cuenta.php",
                    data : data,
                    type : "POST",
                    dataType : "json",
                    beforeSend: function() {
                        loading("true");
                    },
                    complete: function() {
                        loading();
                    },
                    error : function(e)
                    {
                        console.log(e.responseText);
                    }
                },
                columnDefs: [
					{
						className: 'text-center',
						targets: [0, 1, 2, 3, 4, 5, 7, 8, 10]
					}
				],
                "bDestroy" : true,
                aLengthMenu:[10, 20, 30, 40, 50, 100],
                "order" : 
                [
                    0, "desc"   
                ]
            }
        ).DataTable();
    }
}

$("#sec_mantenimiento_num_cuenta_btn_nuevo").off("click").on("click",function(){
    sec_mantenimiento_num_cuenta_limpiar_input();
    $("#sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria_titulo").text("Registro de Cuenta Bancaria y Contable");
    $("#sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria").modal("show");
})

function sec_mantenimiento_num_cuenta_limpiar_input()
{
	$('#form_modal_sec_mantenimiento_num_cuenta_param_id').val(0);
	$("#form_modal_sec_mantenimiento_num_cuenta_param_canal_id").val(0).trigger("change.select2");
	$("#form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id").val(0).trigger("change.select2");
	$("#form_modal_sec_mantenimiento_num_cuenta_param_banco_id").val(0).trigger("change.select2");
	$('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente').val("");
	$('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario').val("");
	$("#form_modal_sec_mantenimiento_num_cuenta_param_moneda_id").val(0).trigger("change.select2");
	$('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable').val("");
    $('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable_haber').val("");
	$('#form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo').val("");
	$("#form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id").val(0).trigger("change.select2");
    $("#form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id").val(0).trigger("change.select2");
	$('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario_descripcion').val("");
}

$("#sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria .btn_guardar").off("click").on("click",function(){

	
	var param_num_cuenta_id = $('#form_modal_sec_mantenimiento_num_cuenta_param_id').val();
	var param_canal = $('#form_modal_sec_mantenimiento_num_cuenta_param_canal_id').val();
	var param_empresa = $('#form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id').val();
	var param_banco = $('#form_modal_sec_mantenimiento_num_cuenta_param_banco_id').val();
	var param_num_cuenta_bancaria = $('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente').val().trim();
	var param_subdiario = $('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario').val().trim();
	var param_moneda = $('#form_modal_sec_mantenimiento_num_cuenta_param_moneda_id').val();
	var param_num_cuenta_contable = $('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable').val().trim();
	var param_num_codigo_anexo = $('#form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo').val().trim();
	var param_tipo_pago = $('#form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id').val();
    var param_proceso = $('#form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id').val();

    //---- Campos historico
    var canal_id = $('#form_modal_sec_mantenimiento_num_cuenta_param_canal_id option:selected').text();
    var razon_social_id = $('#form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id option:selected').text();
    var banco_id = $('#form_modal_sec_mantenimiento_num_cuenta_param_banco_id option:selected').text();
    var num_cuenta_corriente = $('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente').val().trim();
    var subdiario = $('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario').val().trim();
    var moneda_id = $('#form_modal_sec_mantenimiento_num_cuenta_param_moneda_id option:selected').text();
    var num_cuenta_contable = $('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable').val().trim();
    var num_cuenta_contable_haber = $('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable_haber').val().trim();
    var cod_anexo = $('#form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo').val().trim();
    var tipo_pago_id = $('#form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id option:selected').text();
    var cont_num_cuenta_proceso_id = $('#form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id option:selected').text();
    
    //------------------
	var accion = "";
	var titulo = "";

	if(param_num_cuenta_id == "")
    {
        alertify.error('Ocurrió un error, vuelve a refrescar la página',5);
        return false;
    }
    else if(param_num_cuenta_id == "0")
    {
    	// CREAR
    	accion = "mantenimiento_num_cuenta_cuenta_bancaria_nuevo";
    	titulo = "registrar";
    }
    else if(param_num_cuenta_id != "0")
    {
    	// EDITAR
    	if(param_num_cuenta_id == global_param_num_cuenta_id)
    	{
    		accion = "mantenimiento_num_cuenta_cuenta_bancaria_editar";
    		titulo = "editar";
    	}
    	else
    	{
    		alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
        	return false;
    	}
    }
    
    if(param_canal == "0")
    {
        alertify.error('Seleccione Canal',5);
        $("#form_modal_sec_mantenimiento_num_cuenta_param_canal_id").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_num_cuenta_param_canal_id').select2('open');
        }, 200);

        return false;
    }
    
    if(param_empresa == "0")
    {
        alertify.error('Seleccione Empresa',5);
        $("#form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id').select2('open');
        }, 200);

        return false;
    }
    // Condición selección de banco
    if(param_banco != "0")
    {
        if(param_num_cuenta_bancaria.length == 0)
        {
            alertify.error('Ingrese Número Cuenta Bancaria',5);
            $("#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente").focus();
            return false;
        }
     
        if(param_subdiario.length == 0)
        {
            alertify.error('Ingrese Sub Diario',5);
            $("#form_modal_sec_mantenimiento_num_cuenta_param_subdiario").focus();
               return false;
        }
        
        if(param_moneda == "0")
        {
            alertify.error('Seleccione Moneda',5);
            $("#form_modal_sec_mantenimiento_num_cuenta_param_moneda_id").focus();
            setTimeout(function() 
            {
                $('#form_modal_sec_mantenimiento_num_cuenta_param_moneda_id').select2('open');
            }, 200);
    
            return false;
        }
        if(param_num_codigo_anexo.length == 0)
        {
            alertify.error('Ingrese Código Anexo',5);
            $("#form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo").focus();
            return false;
        }
        
        if(param_tipo_pago == "0")
        {
            alertify.error('Seleccione Tipo Pago',5);
            $("#form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id").focus();
            setTimeout(function() 
            {
                $('#form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id').select2('open');
            }, 200);

            return false;
        }
    }

    if(param_num_cuenta_contable.length == 0)
    {
        alertify.error('Ingrese Número Cuenta Contable',5);
        $("#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable").focus();
       	return false;
    }
    
    swal(
    {
        title: '¿Está seguro de '+titulo+'?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
    	if(isConfirm)
    	{
    		var dataForm = new FormData($("#form_modal_sec_mantenimiento_num_cuenta_cuenta_bancaria")[0]);
    		dataForm.append("canal_id",canal_id);
            dataForm.append("razon_social_id",razon_social_id);
    		dataForm.append("banco_id",banco_id);
    		dataForm.append("num_cuenta_corriente",num_cuenta_corriente);
    		dataForm.append("subdiario",subdiario);
    		dataForm.append("moneda_id",moneda_id);
    		dataForm.append("num_cuenta_contable",num_cuenta_contable);
            dataForm.append("cod_anexo",cod_anexo);
    		dataForm.append("tipo_pago_id",tipo_pago_id);
    		dataForm.append("cont_num_cuenta_proceso_id",cont_num_cuenta_proceso_id);
    		dataForm.append("num_cuenta_contable_haber",num_cuenta_contable_haber);


            dataForm.append("accion",accion);


    		$.ajax({
                url: "sys/set_mantenimiento_num_cuenta.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                	loading();
                },
                success: function(data){
                	
                	var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm) {
                        	if (isConfirm)
                        	{
                        		$("#sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria").modal("hide");
	                        	swal.close();
	                        	sec_mantenimiento_num_cuenta_listar_cuenta_bancaria();

	                        	return true;
                        	}
                        	else
                        	{
                        		setTimeout(function() {
		                        	$("#sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria").modal("hide");
		                            swal.close();
		                        	sec_mantenimiento_num_cuenta_listar_cuenta_bancaria();

		                        	return true
		                        }, 5000);
                        	}
                        	
                        });

                        return true;
                    }
                    else
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });

                        return false;
                    }

                }
			});
    	}
    });

})

function sec_mantenimiento_num_cuenta_obtener_cuenta_bancaria(param_cuenta_bancaria_id)
{
	
	var data = {
        "accion": "mantenimiento_num_cuenta_cuenta_bancaria_obtener",
        "param_cuenta_bancaria_id": param_cuenta_bancaria_id
    }

    $.ajax({
        url: "sys/get_mantenimiento_num_cuenta.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data) {
        	
            var respuesta = JSON.parse(data);
            auditoria_send({ "respuesta": "mantenimiento_num_cuenta_cuenta_bancaria_obtener", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
            	var data_back = respuesta.descripcion;

            	$('#form_modal_sec_mantenimiento_num_cuenta_param_id').val(data_back[0].id);
            	global_param_num_cuenta_id = data_back[0].id;
            	$("#form_modal_sec_mantenimiento_num_cuenta_param_canal_id").val(data_back[0].canal_id).trigger("change.select2");
            	$("#form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id").val(data_back[0].empresa_id).trigger("change.select2");
            	$("#form_modal_sec_mantenimiento_num_cuenta_param_banco_id").val(data_back[0].banco_id).trigger("change.select2");
            	$('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente').val(data_back[0].num_cuenta_corriente);
            	$('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario').val(data_back[0].subdiario);
                sec_mantenimiento_num_cuenta_obtener_subdiario_descripcion();
            	$("#form_modal_sec_mantenimiento_num_cuenta_param_moneda_id").val(data_back[0].moneda_id).trigger("change.select2");
            	$('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable').val(data_back[0].num_cuenta_contable);
                $('#form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable_haber').val(data_back[0].num_cuenta_contable_haber);
            	$('#form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo').val(data_back[0].cod_anexo);
            	$("#form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id").val(data_back[0].tipo_pago_id).trigger("change.select2");
            	$("#form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id").val(data_back[0].proceso_id).trigger("change.select2");

            	$("#sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria_titulo").text("Editar Cuenta Bancaria y Contable");
            	$("#sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria").modal("show");
            }
            else
            {
            	swal({
                    title: respuesta.titulo,
                    text: respuesta.descripcion,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });

                return false;
            }    
        }
    });
}

$('#btn_mantenimiento_historico_limpiar_filtros_de_busqueda').click(function() {
    $('#search_id_mantenimiento_num_cuenta_campo_id').select2().val("").trigger("change");
});

function  sec_mantenimiento_num_cuenta_obtener_historico_cambios(id_cuenta) {
	
            $('#modalCuentasContablesHistoricoCambios').modal('show');
            $('#modal_title_cuenta_contable_historico').html((id_cuenta + ' - HISTORIAL DE CAMBIOS').toUpperCase());
            $('#num_cuenta_id').val(id_cuenta);
            sec_mantenimiento_num_cuenta_historico_listar_Datatable(id_cuenta);
}

function sec_mantenimiento_num_cuenta_historico_listar_Datatable() {
    if (sec_id == "mantenimiento" && sub_sec_id == "num_cuenta") {
        var id_campo = $("#search_id_mantenimiento_num_cuenta_campo_id").val();
        var id_cuenta = $("#num_cuenta_id").val();
        console.log(id_cuenta);
        console.log(id_campo);

        var data = {
            accion: "mantenimiento_num_cuenta_cuenta_bancaria_obtener_historico",
            cuenta_id:id_cuenta,
            campo_id: id_campo
            };
        $("#cuenta_contable_historico_cambios_div_tabla").show();
        
        $('#cuenta_contable_historico_cambios_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#cuenta_contable_historico_cambios_datatable").dataTable({
                    language: {
                    decimal: "",
                    emptyTable: "No existen registros",
                    info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    infoEmpty: "Mostrando 0 a 0 de 0 entradas",
                    infoFiltered: "",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ entradas",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Filtrar:",
                    zeroRecords: "Sin resultados",
                    paginate: {
                        first: "Primero",
                        last: "Ultimo",
                        next: "Siguiente",
                        previous: "Anterior",
                        },
                    aria: {
                    sortAscending: ": activate to sort column ascending",
                    sortDescending: ": activate to sort column descending",
                        },
                    buttons: {
                        pageLength: {
                                _: "Mostrar %d Resultados",
                                '-1': "Tout afficher"
                            }
                        },
                        
                        },
                    scrollY: true,
                    scrollX: true,
                    dom: 'Bfrtip',
                    buttons: [
                            'pageLength',
                        ],
                    aProcessing: true,
                    aServerSide: true,
                    ajax: {
                        url: "/sys/get_mantenimiento_num_cuenta.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 6);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(1)', row).css('display', 'center');
                            $('td:eq(2)', row).css('display', 'center');
                            $('td:eq(3)', row).css('display', 'center');
                            $('td:eq(4)', row).css('display', 'center');
                            $('td:eq(5)', row).css('display', 'center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }

function sec_mantenimiento_num_cuenta_listar_campo() {
		let select = $("[name='search_id_mantenimiento_num_cuenta_campo_id']");
		let valorSeleccionado = $("#search_id_mantenimiento_num_cuenta_campo_id").val();
	
		$.ajax({
			url: "/sys/get_mantenimiento_num_cuenta.php",
			type: "POST",
			data: {
				accion: "mantenimiento_num_cuenta_cuenta_bancaria_campo_listar"
			},
			success: function (datos) {
				var respuesta = JSON.parse(datos);
				$(select).empty();
				if (!valorSeleccionado) {
					let opcionDefault = $('<option value=""> Todos</option>');
					$(select).append(opcionDefault);
				}
	
				$(respuesta.result).each(function (i, e) {
					let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
				});
	
				if (valorSeleccionado != null) {
					$(select).val(valorSeleccionado);
				}
			},
			error: function () {
			}
		});
		}

////////// FUNCIONES PARA SUBDIARIOS

function sec_mantenimiento_num_cuenta_obtener_subdiario_descripcion()
{
	var subdiario = $('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario').val();
    var primerosDosDigitosSubdiario = subdiario.toString().substring(0, 2);
    console.log(primerosDosDigitosSubdiario);
	var data = {
		'accion': 'mantenimiento_num_cuenta_cuenta_bancaria_obtener_subdiario_descripcion',
		'subdiario': primerosDosDigitosSubdiario
	};

	$.ajax({
			url: "sys/get_mantenimiento_num_cuenta.php",
			data : data,
			type : "POST",
			beforeSend: function() {
				loading("true");
			},
			complete: function() {
				loading();
			},
			success: function(resp){
				var respuesta = JSON.parse(resp);
		        if (parseInt(respuesta.http_code) == 400) {
                    $('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario_descripcion').val(respuesta.descripcion);
                }

		        if (parseInt(respuesta.http_code) == 200) {
                    $('#form_modal_sec_mantenimiento_num_cuenta_param_subdiario_descripcion').val(respuesta.descripcion);
                }   		
			},
			error: function(){
				alert('failure');
			  }
	    });
}

function sec_mantenimiento_num_cuenta_subdiarios() {

    $('#btn-form-registro-subdiario').html('Registrar');
    $('#modalMantenimientoSubdiario').modal('show');
    $('#modal_title_mantenimiento_subdiario').html(('SUBDIARIOS').toUpperCase());
    sec_mantenimiento_num_cuenta_listar_subdiarios_Datatable();
    sec_mantenimiento_num_cuenta_reset_form_subdiario();
    }

function sec_mantenimiento_num_cuenta_listar_subdiarios_Datatable() {
		if (sec_id == "mantenimiento" && sub_sec_id == "num_cuenta") {
		
			var data = {
				accion: "mantenimiento_num_cuenta_subdiarios_datatable"				
            };
			$("#mantenimiento_num_cuenta_subdiario_div_tabla").show();
		
			$('#mantenimiento_num_cuenta_subdiario_div_tabla tfoot th').each(function () {
				var title = $(this).text();
				$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
				});
		
			tabla = $("#mmantenimiento_num_cuenta_subdiario_datatable").dataTable({
				language: {
						decimal: "",
						emptyTable: "No existen registros",
						info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
						infoEmpty: "Mostrando 0 a 0 de 0 entradas",
						infoFiltered: "",
						infoPostFix: "",
						thousands: ",",
						lengthMenu: "Mostrar _MENU_ entradas",
						loadingRecords: "Cargando...",
						processing: "Procesando...",
						search: "Filtrar:",
						zeroRecords: "Sin resultados",
						paginate: {
							first: "Primero",
							last: "Ultimo",
							next: "Siguiente",
							previous: "Anterior",
						},
						aria: {
							sortAscending: ": activate to sort column ascending",
							sortDescending: ": activate to sort column descending",
						},
						buttons: {
							pageLength: {
								_: "Mostrar %d Resultados",
								'-1': "Tout afficher"
							}
						  },
						  
						},
					scrollY: true,
					scrollX: true,
					dom: 'Bfrtip',
					buttons: [
							'pageLength',
						],
					aProcessing: true,
					aServerSide: true,
					ajax: {
						url: "/sys/get_mantenimiento_num_cuenta.php",
						data: data,
						type: "POST",
						dataType: "json",
						error: function(e) {
													},
					},
                    columnDefs: [
                        {
                            className: 'text-center',
                            targets: [0, 1, 2, 3, 4, 5,6,7,8]
                        }
                    ],
					createdRow: function(row, data, dataIndex) {
						if (data[0] === 'error') {
							$('td:eq(0)', row).attr('colspan', 5);
							$('td:eq(0)', row).attr('align', 'center');
							$('td:eq(1)', row).css('text-align', 'center');
							$('td:eq(2)', row).css('text-align', 'center');
							$('td:eq(3)', row).css('text-align', 'center');
							$('td:eq(4)', row).css('text-align', 'center');
							$('td:eq(5)', row).css('text-align', 'center');
							$('td:eq(6)', row).css('text-align', 'center');
							$('td:eq(7)', row).css('text-align', 'center');
							$('td:eq(8)', row).css('text-align', 'center');
							this.api().cell($('td:eq(0)', row)).data(data[1]);
						}
					},
					bDestroy: true,
					aLengthMenu: [10, 20, 30, 40, 50, 100],
					initComplete: function () {
						this.api()
						.columns()
						.every(function () {
							var that = this;
		
							$('input', this.footer()).on('keyup change clear', function () {
								if (that.search() !== this.value) {
									that.search(this.value).draw();
								}
							});
						});
					},
				}).DataTable();
			}
	}

function sec_mantenimiento_num_cuenta_obtener_subdiario(id)
{
	
	var data = {
        "accion": "mantenimiento_num_cuenta_subdiarios_obtener",
        "id": id
    }

    $.ajax({
        url: "sys/get_mantenimiento_num_cuenta.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data) {
        	
            var respuesta = JSON.parse(data);
            if(parseInt(respuesta.http_code) == 200)
            {
            	var data_back = respuesta.descripcion;

            	$('#modal_mant_subdiario_id').val(data_back[0].id);
            	$('#modal_mant_subdiario_cod_operacion').val(data_back[0].cod_operacion);
            	$('#modal_mant_subdiario_descripcion').val(data_back[0].descripcion);

                $('#btn-form-registro-subdiario').html('Modificar');

            }
            else
            {
            	swal({
                    title: respuesta.titulo,
                    text: respuesta.descripcion,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });

                return false;
            }    
        }
    });
}

function sec_mantenimiento_num_cuenta_obtener_subdiario(subdiario_id) {
    let data = {
            id : subdiario_id,
            accion:'mantenimiento_num_cuenta_subdiarios_obtener'
        }

    $.ajax({
            url:  "/sys/get_mantenimiento_num_cuenta.php",
            type: "POST",
            data:  data,
            beforeSend: function () {
            loading("true");
            },
            complete: function () {
            loading();
            },
            success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status == 200) {	
                
                $('#modal_mant_subdiario_id').val(respuesta.result.id);
                $('#modal_mant_subdiario_cod_operacion').val(respuesta.result.cod_operacion);
                $('#modal_mant_subdiario_descripcion').val(respuesta.result.descripcion);
                $('#btn-form-registro-subdiario').html('Modificar');
                }
            else
                {
                swal({
                        title: 'Error',
                        text: respuesta.message,
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                    });
    
                    return false;
                }    
            },
            error: function (resp, status) {},
            });
    }
$("#Frm_RegistroSubdiario").submit(function (e) {
    e.preventDefault();
    sec_mantenimiento_num_cuenta_validar_subdiario();
    });
function sec_mantenimiento_num_cuenta_validar_subdiario()
{
	var cod_operacion = $('#modal_mant_subdiario_cod_operacion').val();
	var id_subdiario = $('#modal_mant_subdiario_id').val();
    console.log(cod_operacion);
    console.log(id_subdiario);

	if(cod_operacion == "" || cod_operacion == 0){
		alertify.error('Debe ingresar el codigo de operación',5);
		return false;
	}else {

		var data = {
			'accion': 'mantenimiento_num_cuenta_subdiario_verificar',
			'cod_operacion': cod_operacion,
			'id_subdiario': id_subdiario
		};

		$.ajax({
			url: "sys/get_mantenimiento_num_cuenta.php",
			data : data,
			type : "POST",
			beforeSend: function() {
				loading("true");
			},
			complete: function() {
				loading();
			},
			success: function(resp){
				var respuesta = JSON.parse(resp);
                console.log(respuesta.titulo);

		        if (parseInt(respuesta.http_code) == 400) {
					sec_mantenimiento_num_cuenta_guardar_subdiario();
		            }

		         if (parseInt(respuesta.http_code) == 200) {
					alertify.error(respuesta.titulo,5);
					return false;
		            }   		
			},
			error: function(){
				alert('failure');
			  }
		});
	
	}
	
}

function sec_mantenimiento_num_cuenta_guardar_subdiario(){
    var paramSubdiario_id = $('#modal_mant_subdiario_id').val();
	var paramSubdiario_cod_operacion = $('#modal_mant_subdiario_cod_operacion').val();
	var paramSubdiario_descripcion = $('#modal_mant_subdiario_descripcion').val();

	var titulo = "";

    if(paramSubdiario_id == 0)
    {
    	// CREAR
        aviso = "¿Está seguro de crear el nuevo subdiario?";
    	titulo = "Registrar";
    }
    else
    {
    	// EDITAR
        aviso = "¿Está seguro de editar el subdiario?";
    	titulo = "Editar";
    }
    
    if(paramSubdiario_descripcion.length == 0)
    {
        alertify.error('Ingrese descripción de Sub Diario',5);
        $("#modal_mant_subdiario_descripcion").focus();
           return false;
    }
    
    swal({
                title: titulo,
                text: aviso,
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "NO",
                confirmButtonColor: "#529D73",
                confirmButtonText: "SI",
                closeOnConfirm: false
            },
    function (isConfirm) {
                if(isConfirm){
                    
                    var data = {
                        "accion" : "mantenimiento_num_cuenta_subdiario_guardar",
                        "descripcion" : paramSubdiario_descripcion,
                        "cod_operacion" : paramSubdiario_cod_operacion,
                        "subdiario_id" : paramSubdiario_id
                    }
        
                    auditoria_send({ "respuesta": "mantenimiento_num_cuenta_subdiario_guardar", "data": data });
                    $.ajax({
                        url: "sys/set_mantenimiento_num_cuenta.php",
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
        
                            if (parseInt(respuesta.http_code) == 400) {
                                swal({
                                    title: "Error al guardar el subdiario.",
                                    text: respuesta.error,
                                    html:true,
                                    type: "warning",
                                    closeOnConfirm: false,
                                    showCancelButton: false
                                });
                                return false;
                            }
        
                            if (parseInt(respuesta.http_code) == 200) {
                                swal({
                                    title: "Guardar",
                                    text: "El subdiario se guardó correctamente.",
                                    html:true,
                                    type: "success",
                                    closeOnConfirm: false,
                                    showCancelButton: false
                                });
                                $('#Frm_RegistroSubdiario')[0].reset();
                                $("#modal_mant_subdiario_id").val(0);
                                $('#btn-form-registro-subdiario').html('Registrar');
                                sec_mantenimiento_num_cuenta_subdiarios();
                            }      
                        },
                        error: function() {}
                    });
        
        
                }else{
                    //alertify.error('No se guardó el monto',5);
                    return false;
                }
            });
}

function sec_mantenimiento_num_cuenta_reset_form_subdiario() {
    $("#modal_mant_subdiario_id").val(0);
    $('#modal_mant_subdiario_cod_operacion').val("");
    $('#modal_mant_subdiario_descripcion').val("");
    $('#btn-form-registro-subdiario').html('Registrar');
}

function sec_mantenimiento_num_cuenta_eliminar_subdiario(id_subdiario){
    swal({
        title: '¿Esta seguro de eliminar el subdiario?',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        confirmButtonText: "Si, estoy de acuerdo!",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: true,
        closeOnCancel: true,
    
    },function (isConfirm) {
        if (isConfirm) {
            let data = {
                id_subdiario : id_subdiario,
                accion:'mantenimiento_num_cuenta_subdiario_eliminar'
                }
    
            $.ajax({
                url: "sys/set_mantenimiento_num_cuenta.php",
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
                    auditoria_send({
                        "proceso": "mantenimiento_num_cuenta_subdiario_eliminar",
                        "data": respuesta
                    });
                    if (parseInt(respuesta.http_code) == 200) {
                        swal({
                            title: "Eliminación exitosa",
                            text: "El subdiario se eliminó correctamente",
                            html: true,
                            type: "success",
                            timer: 3000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                        setTimeout(function() {
                            $("#modal_mant_subdiario_id").val(0);
                            sec_mantenimiento_num_cuenta_listar_subdiarios_Datatable();
                            return false;
                        }, 3000);
                    } else {
                        swal({
                            title: "Error al eliminar el subdiario",
                            text: respuesta.error,
                            html: true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                    }
                },
                complete: function() {
                    loading(false);
                }
                });
                
        
        } 
    });
    }

////////// FUNCIONES PARA PROCESOS DE CUENTAS

    function sec_mantenimiento_num_cuenta_listar_proceso() {
        let select = $("[name='form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id']");
        let valorSeleccionado = $("#form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id").val();
        
        $.ajax({
            url: "/sys/get_mantenimiento_num_cuenta.php",
            type: "POST",
            data: {
                accion: "mantenimiento_num_cuenta_proceso_listar"
                },
            success: function (datos) {
                var respuesta = JSON.parse(datos);
        
                $(select).empty();
        
                if (!valorSeleccionado) {
                        let opcionDefault = $('<option value="0" selected>Seleccione</option>');
                        $(select).append(opcionDefault);
                    }
        
                    $(respuesta.result).each(function (i, e) {
                        let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                        $(select).append(opcion);
                    });
        
                    if (valorSeleccionado != 0) {
                        $(select).val(valorSeleccionado);
                    }
                },
            error: function () {
                console.error("Error al obtener la lista de usuarios.");
                }
            });
        }
    function sec_mantenimiento_num_cuenta_procesos() {

            $('#btn-form-registro-proceso').html('Registrar');
            $('#modalMantenimientoProceso').modal('show');
            $('#modal_title_mantenimiento_proceso').html(('PROCESOS').toUpperCase());
            sec_mantenimiento_num_cuenta_listar_procesos_Datatable();
            sec_mantenimiento_num_cuenta_reset_form_proceso();
        }

    function sec_mantenimiento_num_cuenta_listar_procesos_Datatable() {
		if (sec_id == "mantenimiento" && sub_sec_id == "num_cuenta") {
		
			var data = {
				accion: "mantenimiento_num_cuenta_proceso_datatable"				
            };
			$("#mantenimiento_num_cuenta_proceso_div_tabla").show();
		
			$('#mantenimiento_num_cuenta_proceso_div_tabla tfoot th').each(function () {
				var title = $(this).text();
				$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
				});
		
			tabla = $("#mantenimiento_num_cuenta_proceso_datatable").dataTable({
				language: {
						decimal: "",
						emptyTable: "No existen registros",
						info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
						infoEmpty: "Mostrando 0 a 0 de 0 entradas",
						infoFiltered: "",
						infoPostFix: "",
						thousands: ",",
						lengthMenu: "Mostrar _MENU_ entradas",
						loadingRecords: "Cargando...",
						processing: "Procesando...",
						search: "Filtrar:",
						zeroRecords: "Sin resultados",
						paginate: {
							first: "Primero",
							last: "Ultimo",
							next: "Siguiente",
							previous: "Anterior",
						},
						aria: {
							sortAscending: ": activate to sort column ascending",
							sortDescending: ": activate to sort column descending",
						},
						buttons: {
							pageLength: {
								_: "Mostrar %d Resultados",
								'-1': "Tout afficher"
							}
						  },
						  
						},
					scrollY: true,
					scrollX: true,
					dom: 'Bfrtip',
					buttons: [
							'pageLength',
						],
					aProcessing: true,
					aServerSide: true,
					ajax: {
						url: "/sys/get_mantenimiento_num_cuenta.php",
						data: data,
						type: "POST",
						dataType: "json",
						error: function(e) {
													},
					},
                    columnDefs: [
                        {
                            className: 'text-center',
                            targets: [0, 1, 2, 3, 4, 5,6,7,8]
                        }
                    ],
					createdRow: function(row, data, dataIndex) {
						if (data[0] === 'error') {
							$('td:eq(0)', row).attr('colspan', 5);
							$('td:eq(0)', row).attr('align', 'center');
							$('td:eq(1)', row).css('text-align', 'center');
							$('td:eq(2)', row).css('text-align', 'center');
							$('td:eq(3)', row).css('text-align', 'center');
							$('td:eq(4)', row).css('text-align', 'center');
							$('td:eq(5)', row).css('text-align', 'center');
							this.api().cell($('td:eq(0)', row)).data(data[1]);
						}
					},
					bDestroy: true,
					aLengthMenu: [10, 20, 30, 40, 50, 100],
					initComplete: function () {
						this.api()
						.columns()
						.every(function () {
							var that = this;
		
							$('input', this.footer()).on('keyup change clear', function () {
								if (that.search() !== this.value) {
									that.search(this.value).draw();
								}
							});
						});
					},
				}).DataTable();
			}
	    }

    function sec_mantenimiento_num_cuenta_obtener_proceso(proceso_id) {
        let data = {
                id : proceso_id,
                accion:'mantenimiento_num_cuenta_proceso_obtener'
            }
    
        $.ajax({
                url:  "/sys/get_mantenimiento_num_cuenta.php",
                type: "POST",
                data:  data,
                beforeSend: function () {
                loading("true");
                },
                complete: function () {
                loading();
                },
                success: function (resp) {
                var respuesta = JSON.parse(resp);
                if (respuesta.status == 200) {	
                    
                    $('#modal_mant_proceso_id').val(proceso_id);
                    $('#modal_mant_proceso_nombre').val(respuesta.result.nombre);
                    $('#modal_mant_proceso_descripcion').val(respuesta.result.descripcion);
                    $('#btn-form-registro-proceso').html('Modificar');
                    }
                },
                error: function (resp, status) {},
                });
        }
    $("#Frm_RegistroProceso").submit(function (e) {
        e.preventDefault();
        sec_mantenimiento_num_cuenta_validar_proceso();
        });
    function sec_mantenimiento_num_cuenta_validar_proceso()
    {
        var nombre = $('#modal_mant_proceso_nombre').val();
        var id_proceso = $('#modal_mant_proceso_id').val();
    
        if(nombre == "" || nombre == 0){
            alertify.error('Debe ingresar el nombre del proceso',5);
            return false;
        }else {
    
            var data = {
                'accion': 'mantenimiento_num_cuenta_proceso_validar',
                'nombre': nombre,
                'id_proceso': id_proceso
            };
    
            $.ajax({
                url: "sys/get_mantenimiento_num_cuenta.php",
                data : data,
                type : "POST",
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                success: function(resp){
                    var respuesta = JSON.parse(resp);
                    console.log(respuesta.titulo);
    
                    if (parseInt(respuesta.http_code) == 400) {
                        sec_mantenimiento_num_cuenta_guardar_proceso();
                        }
    
                     if (parseInt(respuesta.http_code) == 200) {
                        alertify.error(respuesta.titulo,5);
                        return false;
                        }   		
                },
                error: function(){
                    alert('failure');
                  }
            });
        
        }
        
        }
    
    function sec_mantenimiento_num_cuenta_guardar_proceso(){
        var paramProceso_id = $('#modal_mant_proceso_id').val();
        var paramProceso_nombre = $('#modal_mant_proceso_nombre').val();
        var paramProceso_descripcion = $('#modal_mant_proceso_descripcion').val();
    
        var titulo = "";
    
        if(paramProceso_id == 0)
        {
            // CREAR
            aviso = "¿Está seguro de registrar el nuevo proceso?";
            titulo = "Registrar";
        }
        else
        {
            // EDITAR
            aviso = "¿Está seguro de editar el proceso?";
            titulo = "Editar";
        }
        
        if(paramProceso_descripcion.length == 0)
        {
            alertify.error('Ingrese descripción del proceso',5);
            $("#modal_mant_proceso_descripcion").focus();
               return false;
        }
        
        swal({
                    title: titulo,
                    text: aviso,
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: "NO",
                    confirmButtonColor: "#529D73",
                    confirmButtonText: "SI",
                    closeOnConfirm: false
                },
        function (isConfirm) {
                    if(isConfirm){
                        
                        var data = {
                            "accion" : "mantenimiento_num_cuenta_proceso_guardar",
                            "descripcion" : paramProceso_descripcion,
                            "nombre" : paramProceso_nombre,
                            "proceso_id" : paramProceso_id
                        }
            
                        auditoria_send({ "proceso": "mantenimiento_num_cuenta_proceso_guardar", "data": data });
                        $.ajax({
                            url: "sys/set_mantenimiento_num_cuenta.php",
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
            
                                if (parseInt(respuesta.http_code) == 400) {
                                    swal({
                                        title: "Error al guardar el proceso.",
                                        text: respuesta.error,
                                        html:true,
                                        type: "warning",
                                        closeOnConfirm: false,
                                        showCancelButton: false
                                    });
                                    return false;
                                }
            
                                if (parseInt(respuesta.http_code) == 200) {
                                    swal({
                                        title: "Guardar",
                                        text: "El proceso se guardó correctamente.",
                                        html:true,
                                        type: "success",
                                        closeOnConfirm: false,
                                        showCancelButton: false
                                    });
                                    $('#Frm_RegistroProceso')[0].reset();
                                    $("#modal_mant_proceso_id").val(0);
                                    sec_mantenimiento_num_cuenta_procesos();
                                    sec_mantenimiento_num_cuenta_listar_cuenta_bancaria();
                                    sec_mantenimiento_num_cuenta_listar_proceso();
                                    $('#btn-form-registro-proceso').html('Registrar');
                                }      
                            },
                            error: function() {}
                        });
            
            
                    }else{
                        alertify.error('No se guardó los cambios',5);
                        return false;
                    }
                });
        }
    
    function sec_mantenimiento_num_cuenta_reset_form_proceso() {
        $("#modal_mant_proceso_id").val(0);
        $('#modal_mant_proceso_nombre').val("");
        $('#modal_mant_proceso_descripcion').val("");
        $('#btn-form-registro-proceso').html('Registrar');
        }
    
    function sec_mantenimiento_num_cuenta_eliminar_proceso(id_proceso){
        swal({
            title: '¿Esta seguro de eliminar el proceso?',
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: "Si, estoy de acuerdo!",
            cancelButtonText: "No, cancelar",
            closeOnConfirm: true,
            closeOnCancel: true,
        
        },function (isConfirm) {
            if (isConfirm) {
                let data = {
                    id_proceso : id_proceso,
                    accion:'mantenimiento_num_cuenta_proceso_eliminar'
                    }
        
                $.ajax({
                    url: "sys/set_mantenimiento_num_cuenta.php",
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
                        auditoria_send({
                            "proceso": "mantenimiento_num_cuenta_proceso_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El proceso se eliminó correctamente",
                                html: true,
                                type: "success",
                                timer: 3000,
                                closeOnConfirm: false,
                                showCancelButton: false
                            });
                            setTimeout(function() {
                                $("#modal_mant_proceso_id").val(0);
                                sec_mantenimiento_num_cuenta_listar_procesos_Datatable();
                                return false;
                            }, 3000);
                        } else {
                            swal({
                                title: "Error al eliminar el proceso",
                                text: respuesta.error,
                                html: true,
                                type: "warning",
                                closeOnConfirm: false,
                                showCancelButton: false
                            });
                        }
                    },
                    complete: function() {
                        loading(false);
                    }
                    });
                    
            
            } 
        });
        }

////////// FUNCIONES PARA TIPO PAGO

function sec_mantenimiento_num_cuenta_listar_tipo_pago() {
    let select = $("[name='form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id']");
    let valorSeleccionado = $("#form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id").val();
    
    $.ajax({
        url: "/sys/get_mantenimiento_num_cuenta.php",
        type: "POST",
        data: {
            accion: "mantenimiento_num_cuenta_tipo_pago_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
    
            $(select).empty();
    
            if (!valorSeleccionado) {
                    let opcionDefault = $('<option value="0" selected>Seleccione</option>');
                    $(select).append(opcionDefault);
                }
    
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                });
    
                if (valorSeleccionado != 0) {
                    $(select).val(valorSeleccionado);
                }
            },
        error: function () {
            console.error("Error al obtener la lista de usuarios.");
            }
        });
    }
function sec_mantenimiento_num_cuenta_tipo_pagos() {

        $('#btn-form-registro-tipo_pago').html('Registrar');
        $('#modalMantenimientoTipoPago').modal('show');
        $('#modal_title_mantenimiento_tipo_pago').html(('TIPO DE PAGO').toUpperCase());
        sec_mantenimiento_num_cuenta_listar_tipo_pagos_Datatable();
        sec_mantenimiento_num_cuenta_reset_form_tipo_pago();
    }

function sec_mantenimiento_num_cuenta_listar_tipo_pagos_Datatable() {
    if (sec_id == "mantenimiento" && sub_sec_id == "num_cuenta") {
    
        var data = {
            accion: "mantenimiento_num_cuenta_tipo_pago_datatable"				
        };
        $("#mantenimiento_num_cuenta_tipo_pago_div_tabla").show();
    
        $('#mantenimiento_num_cuenta_tipo_pago_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
    
        tabla = $("#mantenimiento_num_cuenta_tipo_pago_datatable").dataTable({
            language: {
                    decimal: "",
                    emptyTable: "No existen registros",
                    info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    infoEmpty: "Mostrando 0 a 0 de 0 entradas",
                    infoFiltered: "",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ entradas",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Filtrar:",
                    zeroRecords: "Sin resultados",
                    paginate: {
                        first: "Primero",
                        last: "Ultimo",
                        next: "Siguiente",
                        previous: "Anterior",
                    },
                    aria: {
                        sortAscending: ": activate to sort column ascending",
                        sortDescending: ": activate to sort column descending",
                    },
                    buttons: {
                        pageLength: {
                            _: "Mostrar %d Resultados",
                            '-1': "Tout afficher"
                        }
                      },
                      
                    },
                scrollY: true,
                scrollX: true,
                dom: 'Bfrtip',
                buttons: [
                        'pageLength',
                    ],
                aProcessing: true,
                aServerSide: true,
                ajax: {
                    url: "/sys/get_mantenimiento_num_cuenta.php",
                    data: data,
                    type: "POST",
                    dataType: "json",
                    error: function(e) {
                                                },
                },
                columnDefs: [
                    {
                        className: 'text-center',
                        targets: [0, 1, 2, 3, 4, 5, 6 ,7]
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    if (data[0] === 'error') {
                        $('td:eq(0)', row).attr('colspan', 5);
                        $('td:eq(0)', row).attr('align', 'center');
                        $('td:eq(1)', row).css('text-align', 'center');
                        $('td:eq(2)', row).css('text-align', 'center');
                        $('td:eq(3)', row).css('text-align', 'center');
                        $('td:eq(4)', row).css('text-align', 'center');
                        $('td:eq(5)', row).css('text-align', 'center');
                        $('td:eq(6)', row).css('text-align', 'center');
                        $('td:eq(7)', row).css('text-align', 'center');
                        this.api().cell($('td:eq(0)', row)).data(data[1]);
                    }
                },
                bDestroy: true,
                aLengthMenu: [10, 20, 30, 40, 50, 100],
                initComplete: function () {
                    this.api()
                    .columns()
                    .every(function () {
                        var that = this;
    
                        $('input', this.footer()).on('keyup change clear', function () {
                            if (that.search() !== this.value) {
                                that.search(this.value).draw();
                            }
                        });
                    });
                },
            }).DataTable();
        }
    }

function sec_mantenimiento_num_cuenta_obtener_tipo_pago(tipo_pago_id) {
    let data = {
            id : tipo_pago_id,
            accion:'mantenimiento_num_cuenta_tipo_pago_obtener'
        }

    $.ajax({
            url:  "/sys/get_mantenimiento_num_cuenta.php",
            type: "POST",
            data:  data,
            beforeSend: function () {
            loading("true");
            },
            complete: function () {
            loading();
            },
            success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status == 200) {	
                
                $('#modal_mant_tipo_pago_id').val(tipo_pago_id);
                $('#modal_mant_tipo_pago_nombre').val(respuesta.result.nombre);
                $('#modal_mant_tipo_pago_descripcion').val(respuesta.result.descripcion);
                $('#btn-form-registro-tipo_pago').html('Modificar');
                }
            },
            error: function (resp, status) {},
            });
    }
$("#Frm_RegistroTipoPago").submit(function (e) {
    e.preventDefault();
    sec_mantenimiento_num_cuenta_validar_tipo_pago();
    });
function sec_mantenimiento_num_cuenta_validar_tipo_pago()
{
    var nombre = $('#modal_mant_tipo_pago_nombre').val();
    var id_tipo_pago = $('#modal_mant_tipo_pago_id').val();

    if(nombre == "" || nombre == 0){
        alertify.error('Debe ingresar el nombre del tipo de pago',5);
        return false;
    }else {

        var data = {
            'accion': 'mantenimiento_num_cuenta_tipo_pago_validar',
            'nombre': nombre,
            'id_tipo_pago': id_tipo_pago
        };

        $.ajax({
            url: "sys/get_mantenimiento_num_cuenta.php",
            data : data,
            type : "POST",
            beforeSend: function() {
                loading("true");
            },
            complete: function() {
                loading();
            },
            success: function(resp){
                var respuesta = JSON.parse(resp);
                console.log(respuesta.titulo);

                if (parseInt(respuesta.http_code) == 400) {
                    sec_mantenimiento_num_cuenta_guardar_tipo_pago();
                    }

                 if (parseInt(respuesta.http_code) == 200) {
                    alertify.error(respuesta.titulo,5);
                    return false;
                    }   		
            },
            error: function(){
                alert('failure');
              }
        });
    
    }
    
    }

function sec_mantenimiento_num_cuenta_guardar_tipo_pago(){
    var paramTipoPago_id = $('#modal_mant_tipo_pago_id').val();
    var paramTipoPago_nombre = $('#modal_mant_tipo_pago_nombre').val();

    var titulo = "";

    if(paramTipoPago_id == 0)
    {
        // CREAR
        aviso = "¿Está seguro de registrar el nuevo tipo de pago?";
        titulo = "Registrar";
    }
    else
    {
        // EDITAR
        aviso = "¿Está seguro de editar el tipo de pago?";
        titulo = "Editar";
    }
    
    swal({
                title: titulo,
                text: aviso,
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "NO",
                confirmButtonColor: "#529D73",
                confirmButtonText: "SI",
                closeOnConfirm: false
            },
    function (isConfirm) {
                if(isConfirm){
                    
                    var data = {
                        "accion" : "mantenimiento_num_cuenta_tipo_pago_guardar",
                        "nombre" : paramTipoPago_nombre,
                        "tipo_pago_id" : paramTipoPago_id
                    }
        
                    auditoria_send({ "proceso": "mantenimiento_num_cuenta_tipo_pago_guardar", "data": data });
                    $.ajax({
                        url: "sys/set_mantenimiento_num_cuenta.php",
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
        
                            if (parseInt(respuesta.http_code) == 400) {
                                swal({
                                    title: "Error al guardar el tipo de pago.",
                                    text: respuesta.error,
                                    html:true,
                                    type: "warning",
                                    closeOnConfirm: false,
                                    showCancelButton: false
                                });
                                return false;
                            }
        
                            if (parseInt(respuesta.http_code) == 200) {
                                swal({
                                    title: "Guardar",
                                    text: "El tipo de pago se guardó correctamente.",
                                    html:true,
                                    type: "success",
                                    closeOnConfirm: false,
                                    showCancelButton: false
                                });
                                $('#Frm_RegistroTipoPago')[0].reset();
                                $("#modal_mant_tipo_pago_id").val(0);
                                $('#btn-form-registro-tipo_pago').html('Registrar');
                                sec_mantenimiento_num_cuenta_tipo_pagos();
                                sec_mantenimiento_num_cuenta_listar_cuenta_bancaria();
                                sec_mantenimiento_num_cuenta_listar_tipo_pago();
                            }      
                        },
                        error: function() {}
                    });
        
        
                }else{
                    alertify.error('No se guardó los cambios',5);
                    return false;
                }
            });
    }

function sec_mantenimiento_num_cuenta_reset_form_tipo_pago(){
    $("#modal_mant_tipo_pago_id").val(0);
    $('#modal_mant_tipo_pago_nombre').val("");
    $('#btn-form-registro-tipo_pago').html('Registrar');
    }

function sec_mantenimiento_num_cuenta_eliminar_tipo_pago(id_tipo_pago){
    swal({
        title: '¿Esta seguro de eliminar el tipo de pago?',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        confirmButtonText: "Si, estoy de acuerdo!",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: true,
        closeOnCancel: true,
    
    },function (isConfirm) {
        if (isConfirm) {
            let data = {
                id_tipo_pago : id_tipo_pago,
                accion:'mantenimiento_num_cuenta_tipo_pago_eliminar'
                }
    
            $.ajax({
                url: "sys/set_mantenimiento_num_cuenta.php",
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
                    auditoria_send({
                        "tipo_pago": "mantenimiento_num_cuenta_tipo_pago_eliminar",
                        "data": respuesta
                    });
                    if (parseInt(respuesta.http_code) == 200) {
                        swal({
                            title: "Eliminación exitosa",
                            text: "El tipo de pago se eliminó correctamente",
                            html: true,
                            type: "success",
                            timer: 3000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                        setTimeout(function() {
                            $("#modal_mant_tipo_pago_id").val(0);
                            sec_mantenimiento_num_cuenta_listar_tipo_pagos_Datatable();
                            return false;
                        }, 3000);
                    } else {
                        swal({
                            title: "Error al eliminar el tipo de pago",
                            text: respuesta.error,
                            html: true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                    }
                },
                complete: function() {
                    loading(false);
                }
                });
                
        
        } 
    });
    }



/////////////  DATA


function sec_mantenimiento_num_cuenta_listar_moneda() {
    let select = $("[name='form_modal_sec_mantenimiento_num_cuenta_param_moneda_id']");
    let valorSeleccionado = $("#form_modal_sec_mantenimiento_num_cuenta_param_moneda_id").val();
    
    $.ajax({
        url: "/sys/get_mantenimiento_num_cuenta.php",
        type: "POST",
        data: {
            accion: "mantenimiento_num_cuenta_moneda_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
    
            $(select).empty();
    
            if (!valorSeleccionado) {
                    let opcionDefault = $('<option value="0" selected>Seleccione</option>');
                    $(select).append(opcionDefault);
                }
    
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                });
    
                if (valorSeleccionado != 0) {
                    $(select).val(valorSeleccionado);
                }
            },
        error: function () {
            console.error("Error al obtener la lista de monedas.");
            }
        });
    }

function sec_mantenimiento_num_cuenta_listar_banco() {
    let select = $("[name='form_modal_sec_mantenimiento_num_cuenta_param_banco_id']");
    let valorSeleccionado = $("#form_modal_sec_mantenimiento_num_cuenta_param_banco_id").val();
    
    $.ajax({
        url: "/sys/get_mantenimiento_num_cuenta.php",
        type: "POST",
        data: {
            accion: "mantenimiento_num_cuenta_banco_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
    
            $(select).empty();
    
            if (!valorSeleccionado) {
                    let opcionDefault = $('<option value="0" selected>Seleccione</option>');
                    $(select).append(opcionDefault);
                }
    
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                });
    
                if (valorSeleccionado != 0) {
                    $(select).val(valorSeleccionado);
                }
            },
        error: function () {
            console.error("Error al obtener la lista de monedas.");
            }
        });
    }

function sec_mantenimiento_num_cuenta_listar_empresa() {
    let select = $("[name='form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id']");
    let valorSeleccionado = $("#form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id").val();
    
    $.ajax({
        url: "/sys/get_mantenimiento_num_cuenta.php",
        type: "POST",
        data: {
            accion: "mantenimiento_num_cuenta_empresa_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
    
            $(select).empty();
    
            if (!valorSeleccionado) {
                    let opcionDefault = $('<option value="0" selected>Seleccione</option>');
                    $(select).append(opcionDefault);
                }
    
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                });
    
                if (valorSeleccionado != 0) {
                    $(select).val(valorSeleccionado);
                }
            },
        error: function () {
            console.error("Error al obtener la lista de monedas.");
            }
        });
    }

function sec_mantenimiento_num_cuenta_listar_canal() {
    let select = $("[name='form_modal_sec_mantenimiento_num_cuenta_param_canal_id']");
    let valorSeleccionado = $("#form_modal_sec_mantenimiento_num_cuenta_param_canal_id").val();
    
    $.ajax({
        url: "/sys/get_mantenimiento_num_cuenta.php",
        type: "POST",
        data: {
            accion: "mantenimiento_num_cuenta_canal_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
    
            $(select).empty();
    
            if (!valorSeleccionado) {
                    let opcionDefault = $('<option value="0" selected>Seleccione</option>');
                    $(select).append(opcionDefault);
                }
    
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                });
    
                if (valorSeleccionado != 0) {
                    $(select).val(valorSeleccionado);
                }
            },
        error: function () {
            console.error("Error al obtener la lista de monedas.");
            }
        });
    }


function sec_mantenimiento_num_cuenta_inactivar(id_cuenta){
    swal({
            title: '¿Está seguro de eliminar la cuenta contable?',
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: "SI",
            cancelButtonText: "NO",
            closeOnConfirm: true,
            closeOnCancel: true,
        
        },function (isConfirm) {
            if (isConfirm) {
                let data = {
                    id_cuenta : id_cuenta,
                    accion:'mantenimiento_num_cuenta_inactivar'
                    }
        
                $.ajax({
                    url: "sys/set_mantenimiento_num_cuenta.php",
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
                        auditoria_send({
                            "proceso": "mantenimiento_num_cuenta_inactivar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "La cuenta contable se eliminó correctamente",
                                html: true,
                                type: "success",
                                timer: 3000,
                                closeOnConfirm: false,
                                showCancelButton: false
                            });
                            setTimeout(function() {
                                sec_mantenimiento_num_cuenta_listar_cuenta_bancaria();
                                return false;
                            }, 3000);
                        } else {
                            swal({
                                title: "Error al eliminar la cuenta contable",
                                text: respuesta.error,
                                html: true,
                                type: "warning",
                                closeOnConfirm: false,
                                showCancelButton: false
                            });
                        }
                    },
                    complete: function() {
                        loading(false);
                    }
                    });
                    
            
            } 
        });
    }
