// INICIO: FUNCIONES INICIALIZADOS
function sec_conciliacion_venta()
{
	$("#conci_venta_div_listar").hide();

    //  Filtros de Busqueda

    sec_conci_venta_search_proveedor_listar();
    sec_conci_venta_search_calimaco_listar();
    document.getElementById('search_conci_venta_proveedor_estado').style.display = 'none';
    //  Formulario
    //sec_conci_venta_proveedor_listar();
    conci_venta_historial_importacion_proveedor_listar();
    conci_venta_formPeriodo_proveedor_listar();
    conci_venta_formHistorialPeriodo_proveedor_listar();
    conci_venta_importar_archivo_calimaco_btn_subir($('#form_modal_sec_conci_venta_importar_calimaco'));
    conci_venta_importar_archivo_proveedor_btn_subir($('#form_modal_sec_conci_venta_importar_proveedor'));
    conci_venta_periodo_importar_archivo_proveedor_btn_subir($('#form_modal_sec_conci_venta_periodo_importar_proveedor'));
    conci_venta_periodo_importar_archivo_calimaco_btn_subir($('#form_modal_sec_conci_venta_periodo_importar_calimaco'));


    ///////////////////////////////

	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".conci_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

    $('#btn_conci_venta_limpiar_filtro').click(function() {
		$('#search_conci_venta_proveedor_id').select2().val('').trigger("change");
		$('#search_conci_venta_proveedor_estado_id').select2().val(0).trigger("change");
		$('#search_conci_venta_fecha_inicio').val('');
		$('#search_conci_venta_fecha_fin').val('');
        $('#search_conci_venta_estado_conciliacion').select2().val('').trigger("change");
        conci_venta_listar_datatable();

	});

    const today = new Date().toISOString().split('T')[0];

    //document.getElementById('search_conci_venta_fecha_inicio').value = today;
    //document.getElementById('search_conci_venta_fecha_fin').value = today;
    //conci_venta_listar_datatable();

}


function conci_venta_importar_archivo_calimaco_btn_subir(object){

    $(document).on('click', '#conci_venta_importar_archivo_calimaco_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
            truncated = name;
        }
        else
        {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
            $("#form_modal_sec_conci_venta_importar_calimaco").val("");
        }

        $("#sec_conci_form_calimaco_txt_mensaje").html(truncated);

    });
}

function conci_venta_importar_archivo_proveedor_btn_subir(object){

    $(document).on('click', '#conci_venta_importar_archivo_proveedor_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
            truncated = name;
        }
        else
        {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
            $("#form_modal_sec_conci_venta_importar_proveedor").val("");
        }

        $("#sec_conci_form_proveedor_txt_mensaje").html(truncated);

    });
}

function conci_venta_periodo_importar_archivo_proveedor_btn_subir(object){

    $(document).on('click', '#conci_venta_periodo_importar_archivo_proveedor_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
            truncated = name;
        }
        else
        {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
            $("#form_modal_sec_conci_venta_periodo_importar_proveedor").val("");
        }

        $("#form_modal_sec_conci_venta_periodo_importar_proveedor_txt_mensaje").html(truncated);

    });
}

function conci_venta_periodo_importar_archivo_calimaco_btn_subir(object){

    $(document).on('click', '#conci_venta_periodo_importar_archivo_calimaco_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
            truncated = name;
        }
        else
        {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
            $("#form_modal_sec_conci_venta_periodo_importar_calimaco").val("");
        }

        $("#form_modal_sec_conci_venta_periodo_importar_calimaco_txt_mensaje").html(truncated);

    });
}
/// FILTROS DE BUSQUEDA

function sec_conci_venta_search_proveedor_listar() {
    let select = $("[name='search_conci_venta_proveedor_id']");

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: "POST",
        data: {
            accion: "conci_venta_proveedor_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
            
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de proveedores.");
        }
    });
    }
function sec_conci_venta_search_calimaco_listar() {
    let select = $("[name='search_conci_venta_calimaco_estado_id']");

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: "POST",
        data: {
            accion: "conci_venta_calimaco_estado_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
            
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de proveedores.");
        }
    });
    }

function conci_venta_search_fn_listarEstados() {

    var proveedor_id = $('#search_conci_venta_proveedor_id').val();
    let select = $("[name='search_conci_venta_proveedor_estado_id']");

    if(proveedor_id != 0){
        var data = {
            'accion': 'conci_venta_proveedor_estado_listar',
            'proveedor_id': proveedor_id
        };
    
        $.ajax({
            url: "/sys/get_conciliacion_venta.php",
            type: "POST",
            data: data,
            success: function(datos) {
                var respuesta = JSON.parse(datos);
                if (parseInt(respuesta.http_code) == 200) {
                    $(select).empty();
        
                    let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                
                    $(respuesta.result).each(function (i, e) {
                        let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                        $(select).append(opcion);
                        });
                    document.getElementById('search_conci_venta_proveedor_estado').style.display = 'block';

                }else if (parseInt(respuesta.http_code) == 400) {
                    $('#search_conci_venta_proveedor_estado').val(0).trigger("change.select2");
                    document.getElementById('search_conci_venta_proveedor_estado').style.display = 'none';
                }

            },
            error: function() {
                $('#search_conci_venta_proveedor_estado').val(0).trigger("change.select2");
                document.getElementById('search_conci_venta_proveedor_estado').style.display = 'none';
                console.error("Error al obtener la lista de tipos de proveedores.");
            }
        });
    }else{

        $('#search_conci_venta_proveedor_estado').val(0).trigger("change.select2");
        document.getElementById('search_conci_venta_proveedor_estado').style.display = 'none';
    }
    }

function conci_venta_listar_datatable() {
	$("#conci_venta_div_listar").hide();

    if(sec_id == "conciliacion" && sub_sec_id == "venta"){

	$("#conci_venta_div_listar").show();
	var proveedor_id = $("#search_conci_venta_proveedor_id").val();
	var proveedor_estado_id = $("#search_conci_venta_proveedor_estado_id").val();
	var calimaco_estado_id = $("#search_conci_venta_calimaco_estado_id").val();
	var fecha_inicio = $("#search_conci_venta_fecha_inicio").val();
	var fecha_fin = $("#search_conci_venta_fecha_fin").val();
	var estado_conciliacion = $("#search_conci_venta_estado_conciliacion").val();

    if(proveedor_id == ''){
        alertify.error('Seleccionar el proveedor',5);
        $("#search_conci_venta_proveedor_id").focus();
        return false;
    }
	
	if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    var data = {
        "accion": "conci_venta_listar",
		proveedor_estado_id: proveedor_estado_id,
        calimaco_estado_id: calimaco_estado_id,
		estado_conciliacion: estado_conciliacion,
        proveedor_id: proveedor_id,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin    
    }

    tabla = $("#conci_venta_div_listar_datatable").dataTable(
        {
            language:{
                "decimal":        "",
                "emptyTable":     "No existen registros",
                "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered":   "(filtrado de _MAX_ entradas)",
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
                },
                "buttons": {
                    pageLength: {
                            _: "Mostrar %d Resultados",
                            '-1': "Tout afficher"
                        }
                    }
            },
            "aProcessing" : true,
            "aServerSide" : true,
            /*
            buttons: [
                'pageLength',
            ],
            scrollY: true,
            scrollX: true,
            dom: 'Bfrtip',
            */
            "ajax" :
            {
                url : "/sys/get_conciliacion_venta.php",
                data : data,
                type : "POST",
                dataType : "json",
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                error : function(e){
                    console.log(e.responseText);
                }
            },
            columnDefs: [
                {
                    className: 'text-center',
                    targets: [0, 1, 2, 3, 4, 5, 6, 7,8]
                },
                {
                    render: $.fn.dataTable.render.number(',', '.', 2),
                    targets: 5
                }
            ],
            "createdRow": function(row, data, dataIndex) {
                // Aplicar estilo basado en data[8]
                if (data[9] === 'red') {
                    $(row).css('background-color', '#FFCDD2');  // Color rojo claro
                } else if (data[8] === 'yellow') {
                    $(row).css('background-color', '#FFF9C4');  // Color amarillo claro
                }
            },
            "bDestroy" : true,
            aLengthMenu:[10, 20, 30, 40, 50, 100]
        }).DataTable();
        
    }
}


function conci_venta_btn_exportar(formato){

	var proveedor_id = $("#search_conci_venta_proveedor_id").val();
	var proveedor_estado_id = $("#search_conci_venta_proveedor_estado_id").val();
	var calimaco_estado_id = $("#search_conci_venta_calimaco_estado_id").val();
	var fecha_inicio = $("#search_conci_venta_fecha_inicio").val();
	var fecha_fin = $("#search_conci_venta_fecha_fin").val();
	var estado_conciliacion = $("#search_conci_venta_estado_conciliacion").val();

	
	if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    var data = {
        "accion": "conci_venta_exportar",
        formato: formato,
		proveedor_estado_id: proveedor_estado_id,
        calimaco_estado_id: calimaco_estado_id,
		estado_conciliacion: estado_conciliacion,
        proveedor_id: proveedor_id,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin
    }

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            let respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                    swal({
                        title: respuesta.titulo,
                        text: respuesta.error,
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                        });
                    return false;
                    }
        
            if (parseInt(respuesta.http_code) == 200) {
                window.open(respuesta.ruta_archivo);
                loading(false);
            }      
        },
        error: function(resp, status) {

        }
    });
}
///

function conci_venta_importar_archivo_venta_btn_subir(object){

    $(document).on('click', '#conci_venta_importar_archivo_venta_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
            truncated = name;
        }
        else
        {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
            $("#form_modal_sec_conci_venta_importar_venta").val("");
        }

        $("#sec_comp_form_da_ac_txt_mensaje").html(truncated);

    });
}

$("#btn_conci_venta_registrar_periodo").off("click").on("click",function(){
    conci_venta_formPeriodo_limpiar_input();

    /*
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    */

    $("#modal_conci_venta_registrar_periodo_titulo").text("Registrar Período");
    $("#modal_conci_venta_registrar_periodo").modal("show");
    })

function conci_venta_formPeriodo_proveedor_listar() {
    let select = $("[name='form_modal_sec_conci_venta_periodo_param_proveedor_id']");
    let valorSeleccionado = $("#form_modal_sec_conci_venta_periodo_param_proveedor_id").val();

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: "POST",
        data: {
            accion: "conci_venta_proveedor_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
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
        error: function() {
            console.error("Error al obtener la lista de tipos de proveedores.");
        }
    });
}

function sec_conci_proveedor_obtener_metodo()
    {
        var proveedor_id = $('#form_modal_sec_conci_venta_importar_proveedor_id').val();
        var data = {
            'accion': 'conci_venta_proveedor_obtener_metodo',
            'proveedor_id': proveedor_id
        };
    
        $.ajax({
                url: "sys/get_conciliacion_venta.php",
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
                        $('#form_modal_sec_conci_venta_importar_proveedor_metodo').val(respuesta.metodo);
                    }
    
                    if (parseInt(respuesta.http_code) == 200) {
                        $('#form_modal_sec_conci_venta_importar_proveedor_metodo').val(respuesta.metodo);
                    }   		
                },
                error: function(){
                    alert('failure');
                  }
            });
    }
$("#modal_conci_venta_importar_proveedor .btn_guardar").off("click").on("click",function(){

    var param_proveedor_id = $("#form_modal_sec_conci_venta_importar_proveedor_id").val();

    var param_fecha_inicio = $("#form_modal_sec_conci_venta_importar_proveedor_fecha_inicio").val();
    var param_fecha_fin = $("#form_modal_sec_conci_venta_importar_proveedor_fecha_fin").val();

    if(param_proveedor_id == 0){
        alertify.error('Seleccionar el proveedor',5);
        $("#form_modal_sec_conci_venta_importar_proveedor_id").focus();
        return false;
    }
    if (param_fecha_inicio.length > 0 && param_fecha_fin.length > 0) {
        var fecha_inicio_date = new Date(param_fecha_inicio);
        var fecha_fin_date = new Date(param_fecha_fin);
        if (fecha_inicio_date > fecha_fin_date) {
            alertify.error('La fecha de inicio debe ser menor o igual a la fecha fin.', 5);
            return false;
        }
    }
    sec_conci_venta_proveedor_importar();     

    })

$("#modal_conci_venta_importar_calimaco .btn_guardar").off("click").on("click",function(){

    var param_fecha_inicio = $("#form_modal_sec_conci_venta_importar_calimaco_fecha_inicio").val();
    var param_fecha_fin = $("#form_modal_sec_conci_venta_importar_calimaco_fecha_fin").val();
    var param_archivo_venta = $("#sec_conci_form_calimaco_txt_mensaje").html();

    if (param_fecha_inicio.length > 0 && param_fecha_fin.length > 0) {
        var fecha_inicio_date = new Date(param_fecha_inicio);
        var fecha_fin_date = new Date(param_fecha_fin);
        if (fecha_inicio_date > fecha_fin_date) {
            alertify.error('La fecha de inicio debe ser menor o igual a la fecha fin.', 5);
            return false;
        }
    }

    if (param_archivo_venta.length == 0) {
		alertify.error('Ingrese el archivo de ventas', 5);
		//$("#form_comp_da_param_cpdf_archivo").focus();
		return false;
	}
        

    sec_conci_venta_calimaco_importar();     

    })

//  PERIODOS

function conci_venta_formHistorialPeriodo_proveedor_listar() {
	let select = $("[name='search_conci_venta_historial_periodo_param_proveedor_id']");
	let valorSeleccionado = $("#search_conci_venta_historial_periodo_param_proveedor_id").val();
	
	$.ajax({
		url: "/sys/get_conciliacion_venta.php",
		type: "POST",
		data: {
			accion: "conci_venta_historial_importacion_proveedor_listar"
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

function conci_venta_periodo_importar_archivo_proveedor_btn_conciliar(importacion_id,periodo_id){
    swal({
        title: 'Conciliar',
        text: '¿Está seguro de conciliar el archivo importado del proveedor?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
        },
        function(isConfirm){
            if(isConfirm){

                var dataForm = new FormData($("#Frm_HistorialImportarVentaProveedor")[0]);
                dataForm.append("accion","conci_venta_periodo_conciliar_proveedor");
                dataForm.append("importacion_id",importacion_id);
                dataForm.append("periodo_id",periodo_id);
                
                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
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
                    success: function (response) {
                        var obj = JSON.parse(response);
                        console.log(response);
                        loading();
                        swal({
                            title: obj.swal_title + "<br>",
                            html: true,
                            text: "<div>" + obj.msg + "</div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                            type: "success",
                            customClass: 'large-swal',
                            closeOnConfirm: true
                            }, function () {
                                //m_reload();
                                swal.close();
                            });
                            //conci_venta_listar_datatable();
                            sec_conci_venta_historial_periodo_Datatable();
                            sec_conci_venta_periodo_historial_importacion_proveedor_Datatable();
                        },
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            swal({
                                title: errorThrown,
                                html: true,
                                text: jqXHR.responseText,
                                type: textStatus,
                                //customClass: 'large-swal',
                                closeOnConfirm: true
                            }, function () {
                                sec_conci_venta_historial_periodo_Datatable();
                                //$("input[name='form_modal_sec_conci_venta_importar_proveedor']").val('');
                                swal.close();
                            })
                        }
                    });
                

                    
                    // Agrega estilos CSS personalizados
                    const style = document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = `
                        .large-swal {
                            width: 500px !important;
                        }
                        .large-swal .swal2-modal {
                            max-width: 600px !important;
                        }
                        .large-swal .swal2-content {
                            font-size: 18px !important;
                        }
                    `;
                    document.head.appendChild(style);
                    
                }
        });
    }
function conci_venta_formPeriodo_limpiar_input(){
	$('#form_modal_sec_conci_venta_periodo_param_id').val(0);
	$('#form_modal_sec_conci_venta_periodo_param_periodo').val(0);
    $('#form_modal_sec_conci_venta_periodo_param_proveedor_id').val(0).trigger("change.select2");
    
}

function sec_conci_venta_periodo_historial_importacion_proveedor_Datatable() {
    if (sec_id == "conciliacion" && sub_sec_id == "venta") {
        var proveedor_id = $("#form_modal_sec_conci_venta_periodo_editar_param_proveedor_id").val();
        var periodo_id = $("#form_modal_sec_conci_venta_periodo_editar_param_id").val();

        var data = {
            accion: "conci_venta_periodo_historial_importacion_proveedor",
            proveedor_id: proveedor_id,
            periodo_id: periodo_id
            };
        $("#conci_venta_periodo_historial_importacion_proveedor_div_tabla").show();
        
        tabla = $("#conci_venta_periodo_historial_importacion_proveedor_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_venta.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 9);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            $('td:eq(6)', row).addClass('text-center');
                            $('td:eq(7)', row).addClass('text-center');
                            $('td:eq(8)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }
function conci_venta_importacion_btn_eliminar(importacion_id){

    swal({
            title: '¿Está seguro de eliminar el archivo del proveedor?',
            type: "warning",
            showCancelButton: true,
            html: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: "SI",
            cancelButtonText: "NO",
            closeOnConfirm: false,
            closeOnCancel: true,
        }, function (isConfirm) {
            if (isConfirm) {
    
                let data = {
                    importacion_id: importacion_id,
                    accion: 'conci_venta_importacion_proveedor_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
                    type: 'POST',
                    data: data,
                    beforeSend: function () {
                        loading(true);
                    },
                    complete: function () {
                        loading(false);
                    },
                    success: function (resp) {
                        var respuesta = JSON.parse(resp);
                        auditoria_send({
                            "proceso": "conci_venta_importacion_proveedor_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El archivo se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_conci_venta_periodo_historial_importacion_proveedor_Datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el archivo",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                sec_comprobante_listar_datatable();
                return false;
            }
        });
    }

function conci_venta_importacion_calimaco_btn_eliminar(importacion_id){

    swal({
            title: '¿Está seguro de eliminar el archivo de calimaco?',
            type: "warning",
            showCancelButton: true,
            html: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: "SI",
            cancelButtonText: "NO",
            closeOnConfirm: false,
            closeOnCancel: true,
        }, function (isConfirm) {
            if (isConfirm) {
    
                let data = {
                    importacion_id: importacion_id,
                    accion: 'conci_venta_importacion_calimaco_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
                    type: 'POST',
                    data: data,
                    beforeSend: function () {
                        loading(true);
                    },
                    complete: function () {
                        loading(false);
                    },
                    success: function (resp) {
                        var respuesta = JSON.parse(resp);
                        auditoria_send({
                            "proceso": "conci_venta_importacion_calimaco_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El archivo se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_conci_venta_periodo_historial_importacion_calimaco_Datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el archivo",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                sec_comprobante_listar_datatable();
                return false;
            }
        });
    }
function sec_conci_venta_periodo_historial_importacion_no_conciliado_proveedor_Datatable() {
    var periodo_id = $("#form_modal_sec_conci_venta_periodo_no_conciliado_param_id").val();

    if (sec_id == "conciliacion" && sub_sec_id == "venta") {

        var data = {
            accion: "conci_venta_periodo_historial_importacion_noconciliado_proveedor",
            periodo_id: periodo_id
            };
        $("#conci_venta_periodo_historial_importacion_noconciliado_proveedor_div_tabla").show();
        
        tabla = $("#conci_venta_periodo_historial_importacion_noconciliado_proveedor_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_venta.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 9);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            $('td:eq(6)', row).addClass('text-center');
                            $('td:eq(7)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }

function sec_conci_venta_periodo_historial_importacion_no_conciliado_calimaco_Datatable() {
    var periodo_id = $("#form_modal_sec_conci_venta_periodo_no_conciliado_param_id").val();

    if (sec_id == "conciliacion" && sub_sec_id == "venta") {

        var data = {
            accion: "conci_venta_periodo_historial_importacion_noconciliado_calimaco",
            periodo_id: periodo_id
            };
        $("#conci_venta_periodo_historial_importacion_noconciliado_calimaco_div_tabla").show();
        
        tabla = $("#conci_venta_periodo_historial_importacion_noconciliado_calimaco_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_venta.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 9);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            $('td:eq(6)', row).addClass('text-center');
                            $('td:eq(7)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }

function sec_conci_venta_periodo_historial_importacion_calimaco_Datatable() {
    if (sec_id == "conciliacion" && sub_sec_id == "venta") {
        var periodo_id = $("#form_modal_sec_conci_venta_periodo_editar_param_id").val();

        var data = {
            accion: "conci_venta_periodo_historial_importacion_calimaco",
            periodo_id: periodo_id
            };
        $("#conci_venta_periodo_historial_importacion_calimaco_div_tabla").show();
        
        tabla = $("#conci_venta_periodo_historial_importacion_calimaco_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_venta.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 9);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            $('td:eq(6)', row).addClass('text-center');
                            $('td:eq(7)', row).addClass('text-center');
                            $('td:eq(8)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }

$("#btn_conci_venta_historial_periodo").off("click").on("click",function(){

    $('#modal_conci_venta_historial_periodo').modal('show');
    $('#modal_title_conci_venta_historial_periodo').html(('HISTORIAL DE PERÍODOS').toUpperCase());
    sec_conci_venta_historial_periodo_Datatable();
    })

function conci_venta_periodo_historial_btn_ver(periodo_id,proveedor_id){
    conci_venta_periodo_historial_btn_editar(periodo_id, proveedor_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });

    document.getElementById('accordionConciVentaAuditoria').style.display = 'block';
    document.getElementById('conciVentaPeriodoImportarCalimaco').style.display = 'none';
    document.getElementById('conciVentaPeriodoImportarProveedor').style.display = 'none';
    document.getElementById('conciVentaPeriodoEditar').style.display = 'none';

}

function conci_venta_periodo_historial_btn_editar(periodo_id, proveedor_id){
    $('#form_modal_sec_conci_venta_periodo_editar_param_id').val(periodo_id);
    $('#form_modal_sec_conci_venta_periodo_editar_param_proveedor_id').val(proveedor_id);
    document.getElementById('conciVentaPeriodoImportarCalimaco').style.display = 'block';
    document.getElementById('conciVentaPeriodoImportarProveedor').style.display = 'block';
    document.getElementById('conciVentaPeriodoEditar').style.display = 'block';

    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });

    let data = {
        id : periodo_id,
        accion:'conci_venta_periodo_obtener'
    }

    $.ajax({
        url:  "/sys/get_conciliacion_venta.php",
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
            $('#form_modal_sec_conci_venta_periodo_editar_param_id').val(respuesta.result.id);
            $('#form_modal_sec_conci_venta_periodo_editar_param_proveedor').val(respuesta.result.proveedor);

            var periodo = respuesta.result.periodo;

            var year = periodo.substring(0, 4);
            var month = periodo.substring(5, 7);

            var periodoFormatted = year + '-' + month;

            $('#form_modal_sec_conci_venta_periodo_editar_param_periodo').val(periodoFormatted);
            
            if(respuesta.result.updated_at==""){
                document.getElementById('conciVentaPeriodoActualizacion').style.display = 'none';

            }else{
                document.getElementById('conciVentaPeriodoActualizacion').style.display = 'block';
                $('#form_modal_sec_conci_venta_periodo_editar_param_fecha_update').val(respuesta.result.updated_at);
                $('#form_modal_sec_conci_venta_periodo_editar_param_usuario_update').val(respuesta.result.usuario_update);    
            }
            if(respuesta.result.created_at==""){
                document.getElementById('conciVentaPeriodoCreacion').style.display = 'none';

            }else{
                document.getElementById('conciVentaPeriodoCreacion').style.display = 'block';
                $('#form_modal_sec_conci_venta_periodo_editar_param_fecha_create').val(respuesta.result.created_at);
                $('#form_modal_sec_conci_venta_periodo_editar_param_usuario_create').val(respuesta.result.usuario_create);    
            }

            
            var periodo_formato = respuesta.result.periodo_formato;
            var proveedor = respuesta.result.proveedor;
            document.getElementById('accordionConciVentaAuditoria').style.display = 'none';

            $('#modal_conci_venta_detalle_periodo').modal('show');
            $('#modal_title_conci_venta_detalle_periodo').html((proveedor + ' - ' + periodo_formato).toUpperCase());
            sec_conci_venta_periodo_historial_importacion_proveedor_Datatable();
            sec_conci_venta_periodo_historial_importacion_calimaco_Datatable();
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

function conci_venta_periodo_historial_btn_comision(periodo_id){
    swal({
        title: "Cerrar",
        text: "¿Esta seguro de cerrar la conciliación?",
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
            "accion" : "conci_venta_periodo_cerrar_conciliacion",
            "periodo_id" : periodo_id
        }

        auditoria_send({ "respuesta": "conci_venta_periodo_editar", "data": data });
        $.ajax({
            url: "sys/set_conciliacion_venta.php",
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
                        title: "Error al cerrar la conciliación.",
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
                        text: "La conciliación se cerró correctamente.",
                        html:true,
                        type: "success",
                        closeOnConfirm: false,
                        showCancelButton: false
                        });
                        }      
                    },
                    error: function() {}
                });
            }else{
                return false;
            }
    });
}

$("#sec_conci_venta_periodo_btn_editar").off("click").on("click", function(event) {
    event.preventDefault();

    var id = $("#form_modal_sec_conci_venta_periodo_editar_param_id").val();
    var proveedor_id = $("#form_modal_sec_conci_venta_periodo_editar_param_proveedor_id").val();
    var periodo = $("#form_modal_sec_conci_venta_periodo_editar_param_periodo").val();

    if (periodo.length == 0){
        alertify.error('Ingrese el periodo',5);
        $("#form_modal_sec_conci_venta_periodo_editar_param_periodo").focus();
        return false;
    }

    swal({
        title: "Editar",
        text: "¿Esta seguro de editar el periodo?",
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
            "accion" : "conci_venta_periodo_editar",
            "id" : id,
            "periodo": periodo,
            "proveedor_id": proveedor_id       
   
        }

        auditoria_send({ "respuesta": "conci_venta_periodo_editar", "data": data });
        $.ajax({
            url: "sys/set_conciliacion_venta.php",
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
                        title: "Error al editar el periodo.",
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
                        text: "El periodo se guardó correctamente.",
                        html:true,
                        type: "success",
                        closeOnConfirm: false,
                        showCancelButton: false
                        });
                        }      
                    },
                    error: function() {}
                });
            }else{
                return false;
            }
    });
})


$("#sec_conci_venta_periodo_btn_importar_proveedor").off("click").on("click", function(event) {
    event.preventDefault();

    var param_fecha_inicio = $("#form_modal_sec_conci_periodo_venta_importar_proveedor_fecha_inicio").val();
    var param_fecha_fin = $("#form_modal_sec_conci_venta_periodo_importar_proveedor_fecha_fin").val();

    if (param_fecha_inicio.length == 0){
        alertify.error('Ingrese la fecha de inicio',5);
        $("#form_modal_sec_conci_periodo_venta_importar_proveedor_fecha_inicio").focus();
        return false;
    }

    if (param_fecha_fin.length == 0){
        alertify.error('Ingrese la fecha fin',5);
        $("#form_modal_sec_conci_venta_periodo_importar_proveedor_fecha_fin").focus();
        return false;
    }

    if (param_fecha_inicio.length > 0 && param_fecha_fin.length > 0) {
        var fecha_inicio_date = new Date(param_fecha_inicio);
        var fecha_fin_date = new Date(param_fecha_fin);
        if (fecha_inicio_date > fecha_fin_date) {
            alertify.error('La fecha de inicio debe ser menor o igual a la fecha fin.', 5);
            return false;
        }
    }

    sec_conci_venta_historial_proveedor_importar();
})

$("#sec_conci_venta_periodo_btn_importar_calimaco").off("click").on("click", function(event) {
    event.preventDefault();

    var param_fecha_inicio = $("#form_modal_sec_conci_periodo_venta_importar_calimaco_fecha_inicio").val();
    var param_fecha_fin = $("#form_modal_sec_conci_venta_periodo_importar_calimaco_fecha_fin").val();

    if (param_fecha_inicio.length == 0){
        alertify.error('Ingrese la fecha de inicio',5);
        $("#form_modal_sec_conci_periodo_venta_importar_calimaco_fecha_inicio").focus();
        return false;
    }

    if (param_fecha_fin.length == 0){
        alertify.error('Ingrese la fecha fin',5);
        $("#form_modal_sec_conci_venta_periodo_importar_calimaco_fecha_fin").focus();
        return false;
    }

    if (param_fecha_inicio.length > 0 && param_fecha_fin.length > 0) {
        var fecha_inicio_date = new Date(param_fecha_inicio);
        var fecha_fin_date = new Date(param_fecha_fin);
        if (fecha_inicio_date > fecha_fin_date) {
            alertify.error('La fecha de inicio debe ser menor o igual a la fecha fin.', 5);
            return false;
        }
    }

    sec_conci_venta_historial_calimaco_importar();
})

function sec_conci_venta_historial_proveedor_importar(){  
    
    var periodo_id = $("#form_modal_sec_conci_venta_periodo_editar_param_id").val();
    var proveedor_id = $("#form_modal_sec_conci_venta_periodo_editar_param_proveedor_id").val();
                
    swal({
        title: 'Importar',
        text: '¿Está seguro de importar el archivo del venta del proveedor?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
        },
        function(isConfirm){
            if(isConfirm){

                var dataForm = new FormData($("#Frm_DetallePeriodo")[0]);

                dataForm.append("accion","conci_venta_periodo_importar_proveedor");
                dataForm.append("periodo_id",periodo_id);
                dataForm.append("proveedor_id",proveedor_id);

                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
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
                    success: function (response) {
                        var obj = JSON.parse(response);
                        loading();
                        swal({
                            title: obj.swal_title + "<br>",
                            html: true,
                            text: "<div>" + obj.msg + "</div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                            type: "success",
                            customClass: 'large-swal',
                            closeOnConfirm: true
                            }, function () {
                                $("input[name='form_modal_sec_conci_venta_periodo_importar_proveedor']").val('');
                                $('#form_modal_sec_conci_periodo_venta_importar_proveedor_fecha_inicio').val('');
                                $('#form_modal_sec_conci_venta_periodo_importar_proveedor_fecha_fin').val('');
                                sec_conci_venta_periodo_historial_importacion_proveedor_Datatable();
                                swal.close();
                            });
                        },
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            swal({
                                title: errorThrown,
                                html: true,
                                text: jqXHR.responseText,
                                type: textStatus,
                                customClass: 'large-swal',
                                closeOnConfirm: true
                            }, function () {
                                $("input[name='form_modal_sec_conci_venta_periodo_importar_proveedor']").val('');
                                $('#form_modal_sec_conci_periodo_venta_importar_proveedor_fecha_inicio').val('');
                                $('#form_modal_sec_conci_venta_periodo_importar_proveedor_fecha_fin').val('');
                                sec_conci_venta_periodo_historial_importacion_proveedor_Datatable();
                                swal.close();
                            })
                        }
                    });

                    // Agrega estilos CSS personalizados
                    const style = document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = `
                        .large-swal {
                            width: 500px !important;
                        }
                        .large-swal .swal2-modal {
                            max-width: 600px !important;
                        }
                        .large-swal .swal2-content {
                            font-size: 18px !important;
                        }
                    `;
                    document.head.appendChild(style);
                }
        });
    }

function sec_conci_venta_historial_calimaco_importar(){  
    
    var periodo_id = $("#form_modal_sec_conci_venta_periodo_editar_param_id").val();
    var calimaco_id = $("#form_modal_sec_conci_venta_periodo_editar_param_calimaco_id").val();
                
    swal({
        title: 'Importar',
        text: '¿Está seguro de importar el archivo del venta de calimaco?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
        },
        function(isConfirm){
            if(isConfirm){

                var dataForm = new FormData($("#Frm_DetallePeriodo")[0]);

                dataForm.append("accion","conci_venta_periodo_importar_calimaco");
                dataForm.append("periodo_id",periodo_id);
                dataForm.append("calimaco_id",calimaco_id);

                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
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
                    success: function (response) {
                        var obj = JSON.parse(response);
                        loading();
                        swal({
                            title: obj.swal_title + "<br>",
                            html: true,
                            text: "<div>" + obj.msg + "</div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                            type: "success",
                            customClass: 'large-swal',
                            closeOnConfirm: true
                            }, function () {
                                $("input[name='form_modal_sec_conci_venta_periodo_importar_calimaco']").val('');
                                $('#form_modal_sec_conci_periodo_venta_importar_calimaco_fecha_inicio').val('');
                                $('#form_modal_sec_conci_venta_periodo_importar_calimaco_fecha_fin').val('');
                                sec_conci_venta_periodo_historial_importacion_calimaco_Datatable();
                                sec_conci_venta_historial_periodo_Datatable();
                                swal.close();
                            });
                        },
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            swal({
                                title: errorThrown,
                                html: true,
                                text: jqXHR.responseText,
                                type: textStatus,
                                customClass: 'large-swal',
                                closeOnConfirm: true
                            }, function () {
                                $("input[name='form_modal_sec_conci_venta_periodo_importar_calimaco']").val('');
                                $('#form_modal_sec_conci_periodo_venta_importar_calimaco_fecha_inicio').val('');
                                $('#form_modal_sec_conci_venta_periodo_importar_calimaco_fecha_fin').val('');
                                sec_conci_venta_periodo_historial_importacion_calimaco_Datatable();
                                swal.close();
                            })
                        }
                    });

                    // Agrega estilos CSS personalizados
                    const style = document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = `
                        .large-swal {
                            width: 500px !important;
                        }
                        .large-swal .swal2-modal {
                            max-width: 600px !important;
                        }
                        .large-swal .swal2-content {
                            font-size: 18px !important;
                        }
                    `;
                    document.head.appendChild(style);
                }
        });
    }

function sec_conci_venta_historial_periodo_Datatable() {
    if (sec_id == "conciliacion" && sub_sec_id == "venta") {
        var proveedor_id = $("#search_conci_venta_historial_periodo_param_proveedor_id").val();

        var data = {
            accion: "conci_venta_historial_periodo",
            proveedor_id: proveedor_id
            };
        $("#conci_venta_historial_periodo_div_tabla").show();
        
        tabla = $("#conci_venta_historial_periodo_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_venta.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 9);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            $('td:eq(6)', row).addClass('text-center');
                            $('td:eq(7)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }

$("#modal_conci_venta_registrar_periodo .btn_guardar").off("click").on("click",function(){

    var param_proveedor_id = $("#form_modal_sec_conci_venta_periodo_param_proveedor_id").val();

    var param_periodo = $("#form_modal_sec_conci_venta_periodo_param_periodo").val();

    if(param_proveedor_id == 0){
        alertify.error('Seleccionar el proveedor',5);
        $("#form_modal_sec_conci_venta_periodo_param_proveedor_id").focus();
        return false;
    }
    if (param_periodo.length == 0) {
            alertify.error('Ingrese el periodo.', 5);
            $("#form_modal_sec_conci_venta_periodo_param_proveedor_id").focus();
            return false;
    }
    sec_conci_venta_registrar_periodo();     

    })

function sec_conci_venta_registrar_periodo(){
    var id = $('#form_modal_sec_conci_venta_periodo_param_id').val();
    var proveedor_id = $('#form_modal_sec_conci_venta_periodo_param_proveedor_id').val();
    var periodo = $('#form_modal_sec_conci_venta_periodo_param_periodo').val();

    if(id == 0)
    {
        // CREAR
        aviso = "¿Está seguro de registrar el período?";
        titulo = "Registrar";
    }
    else
    {
        // EDITAR
        aviso = "¿Está seguro de editar el período?";
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
                  "accion" : "conci_venta_periodo_guardar",
                  "id" : id,
                  "proveedor_id" : proveedor_id,
                  "periodo": periodo       
              }
      
          auditoria_send({ "respuesta": "conci_venta_periodo_guardar", "data": data });
          $.ajax({
              url: "sys/set_conciliacion_venta.php",
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
                          title: "Error al guardar el periodo.",
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
                          text: "El período se guardó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              $('#Frm_conciVentaRegistrarPeriodo')[0].reset();
                              $("#form_modal_sec_conci_venta_periodo_param_id").val(0);
                              $("#modal_conci_venta_registrar_periodo").modal("hide");
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
}

function sec_conci_venta_calimaco_importar(){    
                
        swal({
                title: 'Importar',
                text: '¿Está seguro de importar el archivo de ventas de calimaco?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No',
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm){
                if(isConfirm){

                    var dataForm = new FormData($("#Frm_ImportarVentaCalimaco")[0]);

                    dataForm.append("accion","conci_venta_importar_calimaco");
    
                    $.ajax({
                        url: "sys/set_conciliacion_venta.php",
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
                        success: function (response) {
                            var obj = JSON.parse(response);
                            loading();
                            swal({
                                title: obj.swal_title + "<br>",
                                html: true,
                                text: "<div>" + obj.msg + "</div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                                type: "success",
                                customClass: 'large-swal',
                                closeOnConfirm: true
                            }, function () {
                                //m_reload();
                                swal.close();
                            });
                        },
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            swal({
                                title: errorThrown,
                                html: true,
                                text: jqXHR.responseText,
                                type: textStatus,
                                customClass: 'large-swal',
                                closeOnConfirm: true
                            }, function () {
                                $("input[name='form_modal_sec_conci_venta_importar_calimaco']").val('');
                                swal.close();
                            })
                        }
                    });

                    // Agrega estilos CSS personalizados
                    const style = document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = `
                        .large-swal {
                            width: 500px !important;
                        }
                        .large-swal .swal2-modal {
                            max-width: 600px !important;
                        }
                        .large-swal .swal2-content {
                            font-size: 18px !important;
                        }
                    `;
                    document.head.appendChild(style);
                }
        });
    }

function sec_conci_venta_proveedor_importar(){    
                
    swal({
        title: 'Importar',
        text: '¿Está seguro de importar el archivo del venta del proveedor?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
        },
        function(isConfirm){
            if(isConfirm){

                var dataForm = new FormData($("#Frm_ImportarVentaProveedor")[0]);

                dataForm.append("accion","conci_venta_importar_proveedor");
    
                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
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
                    success: function (response) {
                        var obj = JSON.parse(response);
                        loading();
                        swal({
                            title: obj.swal_title + "<br>",
                            html: true,
                            text: "<div>" + obj.msg + "</div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                            type: "success",
                            customClass: 'large-swal',
                            closeOnConfirm: true
                            }, function () {
                                //m_reload();
                                swal.close();
                            });
                        },
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            swal({
                                title: errorThrown,
                                html: true,
                                text: jqXHR.responseText,
                                type: textStatus,
                                customClass: 'large-swal',
                                closeOnConfirm: true
                            }, function () {
                                $("input[name='form_modal_sec_conci_venta_importar_proveedor']").val('');
                                swal.close();
                            })
                        }
                    });

                    // Agrega estilos CSS personalizados
                    const style = document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = `
                        .large-swal {
                            width: 500px !important;
                        }
                        .large-swal .swal2-modal {
                            max-width: 600px !important;
                        }
                        .large-swal .swal2-content {
                            font-size: 18px !important;
                        }
                    `;
                    document.head.appendChild(style);
                }
        });
    }


function conci_venta_periodo_historial_btn_no_conciliado(periodo_id){

    $('#form_modal_sec_conci_venta_periodo_no_conciliado_param_id').val(periodo_id);

    let data = {
        id : periodo_id,
        accion:'conci_venta_periodo_obtener'
    }

    $.ajax({
        url:  "/sys/get_conciliacion_venta.php",
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
               
                var periodo_formato = respuesta.result.periodo_formato;
                var proveedor = respuesta.result.proveedor;

                $('#modal_conci_venta_detalle_periodo_no_conciliado').on('show.bs.modal', function () {
                    // Ocultar otros modales
                    $('#modal_conci_venta_historial_periodo').modal('hide');
                    $('#modal_conci_venta_detalle_periodo').modal('hide');
                    $('#modal_conci_venta_periodo').modal('hide');
                });

                $('#modal_conci_venta_detalle_periodo_no_conciliado').modal('show');
                $('#modal_title_conci_venta_detalle_periodo_no_conciliado').html(("NO CONCILIADOS - "+proveedor + ' - ' + periodo_formato).toUpperCase());
                sec_conci_venta_periodo_historial_importacion_no_conciliado_proveedor_Datatable();
                sec_conci_venta_periodo_historial_importacion_no_conciliado_calimaco_Datatable();
            }
            else {
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

//  HISTORIAL DE CAMBIOS    /////////////////////////////////////////////////////////////////////////////////////////////////////

function conci_venta_historial_importacion_proveedor_listar() {
	let select = $("[name='search_conci_venta_historial_importacion_proveedor_param_id']");
	let valorSeleccionado = $("#search_conci_venta_historial_importacion_proveedor_param_id").val();
	
	$.ajax({
		url: "/sys/get_conciliacion_venta.php",
		type: "POST",
		data: {
			accion: "conci_venta_historial_importacion_proveedor_listar"
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
$("#btn_conci_venta_historial_proveedor").off("click").on("click",function(){

    $('#modal_conci_venta_historial_importacion_proveedor').modal('show');
    $('#modal_title_conci_venta_historial_importacion_proveedor').html(('HISTORIAL DE IMPORTACIÓN DE PROVEEDOR').toUpperCase());
    sec_conci_venta_historial_importacion_proveedor_Datatable();
    })

function sec_conci_venta_historial_importacion_proveedor_Datatable() {
    if (sec_id == "conciliacion" && sub_sec_id == "venta") {
        var proveedor_id = $("#search_conci_venta_historial_importacion_proveedor_param_id").val();
        
        var data = {
            accion: "conci_venta_historial_importacion_proveedor",
            proveedor_id: proveedor_id
            };
        $("#conci_venta_historial_importacion_proveedor_div_tabla").show();
        
        tabla = $("#conci_venta_historial_importacion_proveedor_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_venta.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 9);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            $('td:eq(6)', row).addClass('text-center');
                            $('td:eq(7)', row).addClass('text-center');
                            $('td:eq(8)', row).addClass('text-center');
                            $('td:eq(9)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }
$("#btn_conci_venta_historial_calimaco").off("click").on("click",function(){

    $('#modal_conci_venta_historial_importacion_calimaco').modal('show');
    $('#modal_title_conci_venta_historial_importacion_calimaco').html(('HISTORIAL DE IMPORTACIÓN DE CALIMACO').toUpperCase());
    sec_conci_venta_historial_importacion_calimaco_Datatable();
    })

function sec_conci_venta_historial_importacion_calimaco_Datatable() {
    if (sec_id == "conciliacion" && sub_sec_id == "venta") {
        
        var data = {
            accion: "conci_venta_historial_importacion_calimaco"
            };
        $("#conci_venta_historial_importacion_calimaco_div_tabla").show();
        
        $('#conci_venta_historial_importacion_calimaco_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#conci_venta_historial_importacion_calimaco_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_venta.php",
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
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            $('td:eq(6)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }

function conci_venta_importar_archivo_proveedor_btn_conciliar(importacion_id){
    swal({
        title: 'Conciliar',
        text: '¿Está seguro de conciliar el archivo importado del proveedor?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
        },
        function(isConfirm){
            if(isConfirm){

                var dataForm = new FormData($("#Frm_HistorialImportarVentaProveedor")[0]);
                dataForm.append("accion","conci_venta_conciliar_proveedor");
                dataForm.append("importacion_id",importacion_id);
                
                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
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
                    success: function (response) {
                        var obj = JSON.parse(response);
                        console.log(response);
                        loading();
                        swal({
                            title: obj.swal_title + "<br>",
                            html: true,
                            text: "<div>" + obj.msg + "</div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                            type: "success",
                            customClass: 'large-swal',
                            closeOnConfirm: true
                            }, function () {
                                //m_reload();
                                swal.close();
                            });
                            conci_venta_listar_datatable();
                        },
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            swal({
                                title: errorThrown,
                                html: true,
                                text: jqXHR.responseText,
                                type: textStatus,
                                //customClass: 'large-swal',
                                closeOnConfirm: true
                            }, function () {
                                //$("input[name='form_modal_sec_conci_venta_importar_proveedor']").val('');
                                swal.close();
                            })
                        }
                    });
                

                    
                    // Agrega estilos CSS personalizados
                    const style = document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = `
                        .large-swal {
                            width: 500px !important;
                        }
                        .large-swal .swal2-modal {
                            max-width: 600px !important;
                        }
                        .large-swal .swal2-content {
                            font-size: 18px !important;
                        }
                    `;
                    document.head.appendChild(style);
                    
                }
        });
    }

//  Detalle de conciliación

$("#btn_conci_venta_historial_calimaco").off("click").on("click",function(){

    $('#modal_conci_venta_historial_importacion_calimaco').modal('show');
    $('#modal_title_conci_venta_historial_importacion_calimaco').html(('HISTORIAL DE IMPORTACIÓN DE CALIMACO').toUpperCase());
    sec_conci_venta_historial_importacion_calimaco_Datatable();
    })

function sec_conci_venta_detalle_conciliacion_proveedor(transaccion_id,periodo_id) {
    $('#modal_conci_venta_detalle_proveedor').modal('show');
    $('#modal_title_conci_venta_detalle_proveedor').html(('DETALLE DE VENTA DE PROVEEDOR').toUpperCase());

    if (sec_id == "conciliacion" && sub_sec_id == "venta") {
        
        var data = {
            accion: "conci_venta_detalle_proveedor",
            id: transaccion_id,
            periodo_id: periodo_id

        };
        $("#conci_venta_detalle_proveedor_div_tabla").show();

        $("#conci_venta_detalle_proveedor_div_tabla").empty();

        $.ajax({
            url: "/sys/get_conciliacion_venta.php",
            type: "POST",
            data: data,
            dataType: "json",
            success: function(response) {
                if (response.aaData && response.aaData.length > 0) {
                    var data = response.aaData;
                    
                    data.forEach((tableData, index) => {
                        if (tableData) {
                            var tableId = "conci_venta_detalle_proveedor_datatable_" + index;
                            var tableHtml = `
                                <div class="table-responsive">
                                    <table id="${tableId}" class="display" style="width:100%">
                                        <thead></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            `;
                            $("#conci_venta_detalle_proveedor_div_tabla").append(tableHtml);

                            var columns = [];
                            var thead = "<tr>";

                            // Usar las claves del primer objeto como nombres de columnas
                            for (var key in tableData) {
                                if (tableData.hasOwnProperty(key)) {
                                    columns.push({ title: key, data: key });
                                    thead += "<th class='text-center'>" + key + "</th>";
                                }
                            }
                            thead += "</tr>";
                            $("#" + tableId + " thead").html(thead);

                            // Inicializar DataTable con las columnas dinámicas
                            $("#" + tableId).DataTable({
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
                                        last: "Último",
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
                                scrollX: true, // Permitir desplazamiento horizontal
                                scrollCollapse: true,
                                dom: 'Bfrtip',
                                buttons: [
                                    'pageLength',
                                ],
                                data: [tableData],
                                columns: columns,
                                createdRow: function(row, data, dataIndex) {
                                    if (data[0] === 'error') {
                                        $('td:eq(0)', row).attr('colspan', 6);
                                        $('td:eq(0)', row).attr('align', 'center');
                                        $('td:eq(0)', row).addClass('text-center');
                                        $('td:eq(1)', row).addClass('text-center');
                                        $('td:eq(2)', row).addClass('text-center');
                                        $('td:eq(3)', row).addClass('text-center');
                                        $('td:eq(4)', row).addClass('text-center');
                                        $('td:eq(5)', row).addClass('text-center');
                                        $('td:eq(6)', row).addClass('text-center');
                                        this.api().cell($('td:eq(0)', row)).data(data[1]);
                                    }
                                },
                                columnDefs: [{
                                    className: "text-center",
                                    targets: "_all"
                                }],
                                bDestroy: true,
                                aLengthMenu: [10, 20, 30, 40, 50, 100],
                                initComplete: function () {
                                    // Ocultar la barra de búsqueda
                                    $('.dataTables_filter').css('display', 'none');
                                },
                            });

                            $("#" + tableId).css({
                                'border-spacing': '10px',
                                //'border-collapse': 'separate'
                            });
                        } else {
                            console.log("No hay datos disponibles para la tabla " + index);
                        }
                    });

                } else {
                    console.log("No hay datos disponibles en la respuesta");
                }
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
}


//  ANULACIÓN


function sec_conci_venta_anular(transaccion_calimaco_id) {
    $('#modal_conci_anulacion_solicitud').modal('show');
    $('#modal_title_conci_anulacion_solicitud').html(('SOLICITUD DE ANULACIÓN').toUpperCase());
    sec_conci_venta_anulacion_tipo_listar();

    $('#form_modal_conci_solicitud_anulacion_param_transaccion_calimaco_id').val(transaccion_calimaco_id);

}

function sec_conci_venta_devolucion(transaccion_calimaco_id,cantidad,periodo_id) {
    $('#modal_conci_devolucion_solicitud').modal('show');
    $('#modal_title_conci_devolucion_solicitud').html(('SOLICITUD DE DEVOLUCIÓN').toUpperCase());
    $('#form_modal_conci_solicitud_devolucion_param_periodo_id').val(periodo_id);
    $('#form_modal_conci_solicitud_devolucion_param_transaccion_calimaco_id').val(transaccion_calimaco_id);
    $('#form_modal_conci_solicitud_devolucion_param_monto').val(cantidad);
}

$("#modal_conci_devolucion_solicitud .btn_guardar").off("click").on("click",function(){

    var transaccion_calimaco_id = $("#form_modal_conci_solicitud_devolucion_param_transaccion_calimaco_id").val();
    var periodo_id = $("#form_modal_conci_solicitud_devolucion_param_periodo_id").val();

    console.log(periodo_id);
    swal({
                title: "Devolución",
                text: "¿Está seguro de registrar la solicitud de devolución?",
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
                    "accion" : "conci_devolucion_guardar",
                    "transaccion_calimaco_id": transaccion_calimaco_id, 
                    "periodo_id": periodo_id
                }
        
            auditoria_send({ "respuesta": "conci_devolucion_guardar", "data": data });
            $.ajax({
                url: "sys/set_conciliacion_devolucion.php",
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
                            title: "Error al guardar la devolución.",
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
                            text: "La devolución se guardó correctamente.",
                            html:true,
                            type: "success",
                            closeOnConfirm: false,
                            showCancelButton: false
                            });
                                $('#Frm_SolicitudDevolucion')[0].reset();
                                $("#modal_conci_devolucion_solicitud").modal("hide");
                                conci_venta_listar_datatable();
                            }      
                        },
                        error: function() {}
                    });
                }else{
                    return false;
                }
            });  
})

function sec_conci_venta_anulacion_tipo_listar() {
    let select = $("[name='form_modal_conci_solicitud_anulacion_param_tipo']");

    $.ajax({
        url: "/sys/get_conciliacion_anulacion.php",
        type: "POST",
        data: {
            accion: "conci_anulacion_tipo_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

            let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de anulaciones.");
        }
    });
    }

$("#modal_conci_anulacion_solicitud .btn_guardar").off("click").on("click",function(){

	
    var motivo = $('#form_modal_conci_solicitud_anulacion_param_motivo').val();
    var tipo = $("#form_modal_conci_solicitud_anulacion_param_tipo").val();

    if(tipo == 0){
        alertify.error('Seleccione el tipo de anulación',5);
        $("#form_modal_conci_solicitud_anulacion_param_tipo").focus();
        return false;
    }

    if(motivo.length == 0)
        {
            alertify.error('Ingrese la descripción del motivo de anulación',5);
            $("#form_modal_conci_solicitud_anulacion_param_motivo").focus();
            return false;
        }

    conci_venta_anulacion_solicitud_guardar();     
})

function conci_venta_anulacion_solicitud_guardar(){
    var motivo = $('#form_modal_conci_solicitud_anulacion_param_motivo').val();
    var tipo_id = $("#form_modal_conci_solicitud_anulacion_param_tipo").val();
    var transaccion_calimaco_id = $("#form_modal_conci_solicitud_anulacion_param_transaccion_calimaco_id").val();
    swal({
                title: "Anular",
                text: "¿Está seguro de registrar la solicitud de anulación?",
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
                    "accion" : "conci_anulacion_guardar",
                    "motivo" : motivo,
                    "tipo_id": tipo_id ,
                    "transaccion_calimaco_id": transaccion_calimaco_id,     
                }
        
            auditoria_send({ "respuesta": "conci_anulacion_guardar", "data": data });
            $.ajax({
                url: "sys/set_conciliacion_anulacion.php",
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
                            title: "Error al guardar la solicitud de anulación.",
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
                            text: "La solicitud se guardó correctamente.",
                            html:true,
                            type: "success",
                            closeOnConfirm: false,
                            showCancelButton: false
                            });
                                $('#Frm_SolicitudAnulacion')[0].reset();
                                $("#modal_conci_anulacion_solicitud").modal("hide");
                                conci_venta_listar_datatable();
                            }      
                        },
                        error: function() {}
                    });
                }else{
                    return false;
                }
            });
  }

function conci_venta_periodo_btn_registrar_observacion_proveedor(id) {
    $('#modal_conci_venta_detalle_periodo_no_conciliado_observacion').modal('show');
    $('#modal_title_conci_venta_detalle_periodo_no_conciliado_observacion').html(('REGISTRAR OBSERVACIÓN DEL PROVEEDOR').toUpperCase());
    sec_conci_venta_anulacion_tipo_listar();

    $('#form_modal_conci_venta_no_conciliado_observacion_param_id').val(id);
}

function conci_venta_periodo_btn_registrar_observacion_calimaco(id) {
    $('#modal_conci_venta_detalle_periodo_no_conciliado_calimaco_observacion').modal('show');
    $('#modal_title_conci_venta_detalle_periodo_no_conciliado_calimaco_observacion').html(('REGISTRAR OBSERVACIÓN DEL CALIMACO').toUpperCase());
    sec_conci_venta_anulacion_tipo_listar();

    $('#form_modal_conci_venta_no_conciliado_observacion_calimaco_param_id').val(id);
}

$("#modal_conci_venta_detalle_periodo_no_conciliado_observacion .btn_guardar").off("click").on("click",function(){
    var observacion = $('#form_modal_conci_venta_no_conciliado_observacion_param_observacion').val();
    var id = $("#form_modal_conci_venta_no_conciliado_observacion_param_id").val();

    swal({
                title: "Observar",
                text: "¿Está seguro de registrar la observación?",
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
                    "accion" : "conci_venta_periodo_observar_proveedor",
                    "observacion" : observacion,
                    "id": id,     
                }
        
            auditoria_send({ "respuesta": "conci_venta_periodo_observar_proveedor", "data": data });
            $.ajax({
                url: "sys/set_conciliacion_venta.php",
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
                            title: "Error al guardar la observación.",
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
                            text: "La observación se guardó correctamente.",
                            html:true,
                            type: "success",
                            closeOnConfirm: false,
                            showCancelButton: false
                            });
                                $('#Frm_conciVentaObservacionProveedor')[0].reset();
                                $("#modal_conci_venta_detalle_periodo_no_conciliado_observacion").modal("hide");
                                sec_conci_venta_periodo_historial_importacion_no_conciliado_proveedor_Datatable();
                                sec_conci_venta_periodo_historial_importacion_no_conciliado_calimaco_Datatable();
                            }      
                        },
                        error: function() {}
                    });
                }else{
                    return false;
                }
            });
})
$("#modal_conci_venta_detalle_periodo_no_conciliado_calimaco_observacion .btn_guardar").off("click").on("click",function(){
    var observacion = $('#form_modal_conci_venta_no_conciliado_observacion_calimaco_param_observacion').val();
    var id = $("#form_modal_conci_venta_no_conciliado_observacion_calimaco_param_id").val();

    swal({
                title: "Observar",
                text: "¿Está seguro de registrar la observación?",
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
                    "accion" : "conci_venta_periodo_observar_calimaco",
                    "observacion" : observacion,
                    "id": id,     
                }
        
            auditoria_send({ "respuesta": "conci_venta_periodo_observar_calimaco", "data": data });
            $.ajax({
                url: "sys/set_conciliacion_venta.php",
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
                            title: "Error al guardar la observación.",
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
                            text: "La observación se guardó correctamente.",
                            html:true,
                            type: "success",
                            closeOnConfirm: false,
                            showCancelButton: false
                            });
                                $('#Frm_conciVentaObservacionCalimaco')[0].reset();
                                $("#modal_conci_venta_detalle_periodo_no_conciliado_calimaco_observacion").modal("hide");
                                sec_conci_venta_periodo_historial_importacion_no_conciliado_proveedor_Datatable();
                                sec_conci_venta_periodo_historial_importacion_no_conciliado_calimaco_Datatable();
                            }      
                        },
                        error: function() {}
                    });
                }else{
                    return false;
                }
            });
})

function sec_conci_venta_importar_proveedor_btn_editar(importacion_id) {
    $('#form_modal_conci_venta_importar_proveedor_editar_param_importacion_id').val(importacion_id);

    let data = {
        id: importacion_id,
        accion: 'conci_venta_importacion_obtener'
    };

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: "POST",
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status == 200) {
                console.log(respuesta.result.fecha_fin);
                console.log(respuesta.result.fecha_inicio);

                // Convertir fechas al formato YYYY-MM-DD
                var fechaInicio = convertToDateInputFormat(respuesta.result.fecha_inicio);
                var fechaFin = convertToDateInputFormat(respuesta.result.fecha_fin);

                $('#form_modal_conci_venta_importar_proveedor_editar_param_fecha_inicio').val(fechaInicio);
                $('#form_modal_conci_venta_importar_proveedor_editar_param_fecha_fin').val(fechaFin);

                $('#modal_conci_venta_importar_proveedor_editar').modal('show');
                $('#modal_title_conci_venta_importar_proveedor_editar').html(('EDITAR IMPORTACIÓN PROVEEDOR').toUpperCase());
            } else {
                swal({
                    title: 'Error',
                    text: respuesta.message,
                    html: true,
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

function convertToDateInputFormat(dateStr) {
    var parts = dateStr.split("-");
    return parts[2] + "-" + parts[1] + "-" + parts[0];
}

$("#modal_conci_venta_importar_proveedor_editar .btn_guardar").off("click").on("click",function(){

    var id = $('#form_modal_conci_venta_importar_proveedor_editar_param_importacion_id').val();
    var fecha_inicio = $('#form_modal_conci_venta_importar_proveedor_editar_param_fecha_inicio').val();
    var fecha_fin = $("#form_modal_conci_venta_importar_proveedor_editar_param_fecha_fin").val();

    if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}else{
        alertify.error('Ingrese la fecha inicio y la fecha fin.', 5);
        return false;
    }

    swal({
                title: "Editar",
                text: "¿Esta seguro de editar la importación?",
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
                  "accion" : "conci_venta_importacion_proveedor_editar",
                  "id" : id,
                  "fecha_inicio" : fecha_inicio,
                  "fecha_fin": fecha_fin       
              }
      
          auditoria_send({ "respuesta": "conci_venta_importacion_proveedor_editar", "data": data });
          $.ajax({
              url: "sys/set_conciliacion_venta.php",
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
                          title: "Error al editar la importación.",
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
                          text: "La importación se editó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              $('#Frm_ConciVentaImportarProveedorEditar')[0].reset();
                              $("#form_modal_conci_venta_importar_proveedor_editar_param_importacion_id").val(0);
                              $("#modal_conci_venta_importar_proveedor_editar").modal("hide");
                              sec_conci_venta_periodo_historial_importacion_proveedor_Datatable();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
})

function sec_conci_venta_importar_calimaco_btn_editar(importacion_id) {
    $('#form_modal_conci_venta_importar_calimaco_editar_param_importacion_id').val(importacion_id);

    let data = {
        id: importacion_id,
        accion: 'conci_venta_importacion_calimaco_obtener'
    };

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: "POST",
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status == 200) {
                console.log(respuesta.result.fecha_fin);
                console.log(respuesta.result.fecha_inicio);

                var fechaInicio = convertToDateInputFormat(respuesta.result.fecha_inicio);
                var fechaFin = convertToDateInputFormat(respuesta.result.fecha_fin);

                $('#form_modal_conci_venta_importar_calimaco_editar_param_fecha_inicio').val(fechaInicio);
                $('#form_modal_conci_venta_importar_calimaco_editar_param_fecha_fin').val(fechaFin);

                /*
                if(respuesta.result.created_at==""){
                    document.getElementById('conciVentaImportacionEditarCreate').style.display = 'none';
  
                }else{
                    document.getElementById('conciVentaImportacionEditarCreate').style.display = 'block';
                    $('#form_modal_conci_venta_importar_calimaco_editar_param_fecha_create').val(respuesta.result.created_at);
                    $('#form_modal_conci_venta_importar_calimaco_editar_param_usuario_create').val(respuesta.result.usuario_create);    
                }

                if(respuesta.result.updated_at==""){
                    document.getElementById('conciVentaImportacionEditarUpdate').style.display = 'none';
  
                }else{
                    document.getElementById('conciVentaImportacionEditarUpdate').style.display = 'block';
                    $('#form_modal_conci_venta_importar_calimaco_editar_param_fecha_update').val(respuesta.result.updated_at);
                    $('#form_modal_conci_venta_importar_calimaco_editar_param_usuario_update').val(respuesta.result.usuario_update);    
                }
                    */

                $('#modal_conci_venta_importar_calimaco_editar').modal('show');
                $('#modal_title_conci_venta_importar_calimaco_editar').html(('EDITAR IMPORTACIÓN CALIMACO').toUpperCase());
            } else {
                swal({
                    title: 'Error',
                    text: respuesta.message,
                    html: true,
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

function convertToDateInputFormat(dateStr) {
    var parts = dateStr.split("-");
    return parts[2] + "-" + parts[1] + "-" + parts[0];
}

$("#modal_conci_venta_importar_calimaco_editar .btn_guardar").off("click").on("click",function(){

    var id = $('#form_modal_conci_venta_importar_calimaco_editar_param_importacion_id').val();
    var fecha_inicio = $('#form_modal_conci_venta_importar_calimaco_editar_param_fecha_inicio').val();
    var fecha_fin = $("#form_modal_conci_venta_importar_calimaco_editar_param_fecha_fin").val();

    if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}else{
        alertify.error('Ingrese la fecha inicio y la fecha fin.', 5);
        return false;
    }

    swal({
                title: "Editar",
                text: "¿Esta seguro de editar la importación?",
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
                  "accion" : "conci_venta_importacion_calimaco_editar",
                  "id" : id,
                  "fecha_inicio" : fecha_inicio,
                  "fecha_fin": fecha_fin       
              }
      
          auditoria_send({ "respuesta": "conci_venta_importacion_calimaco_editar", "data": data });
          $.ajax({
              url: "sys/set_conciliacion_venta.php",
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
                          title: "Error al editar la importación.",
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
                          text: "La importación se editó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              $('#Frm_ConciVentaImportarProveedorEditar')[0].reset();
                              $("#form_modal_conci_venta_importar_calimaco_editar_param_importacion_id").val(0);
                              $("#modal_conci_venta_importar_calimaco_editar").modal("hide");
                              sec_conci_venta_periodo_historial_importacion_calimaco_Datatable();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
})

function conci_venta_periodo_btn_eliminar(periodo_id){

    swal({
            title: '¿Está seguro de eliminar el período?',
            type: "warning",
            showCancelButton: true,
            html: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: "SI",
            cancelButtonText: "NO",
            closeOnConfirm: false,
            closeOnCancel: true,
        }, function (isConfirm) {
            if (isConfirm) {
    
                let data = {
                    periodo_id: periodo_id,
                    accion: 'conci_venta_periodo_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_venta.php",
                    type: 'POST',
                    data: data,
                    beforeSend: function () {
                        loading(true);
                    },
                    complete: function () {
                        loading(false);
                    },
                    success: function (resp) {
                        var respuesta = JSON.parse(resp);
                        auditoria_send({
                            "proceso": "conci_venta_periodo_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El período se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_conci_venta_periodo_historial_importacion_proveedor_Datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el período",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                sec_comprobante_listar_datatable();
                return false;
            }
        });
    }
