// INICIO: FUNCIONES INICIALIZADOS
function sec_comprobante_pago()
{

    /*
    $(document).ready(function() {
        sec_comprobante_obtener_registrador_ruc();
    });
    */
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_comp_select_filtro").select2({ width: '100%' });

    
	// FIN FORMATO COMBO CON BUSQUEDA


     // FILTRO DE BUSQUEDA

    sec_comprobante_filtro_proveedor_listar();
    sec_comprobante_filtro_razon_social_listar();
    sec_comprobante_filtro_etapa_listar();
    sec_comprobante_historico_listar_campo();
    // FORMULARIO DE REGISTRO DE COMPROBANTES

    sec_comprobante_proveedor_listar();
    sec_comprobante_empresa_at_listar();
    sec_comprobante_moneda_listar();
    sec_comprobante_area_listar();
    sec_comprobante_ceco_listar();
    sec_comprobante_fp_moneda_listar();
    sec_comprobante_fp_banco_listar();
    sec_comprobante_tipos_listar();
    sec_comprobante_listar_datatable();

    sec_comprobante_motivo_listar();


    //  FORMULARIO PARA PAGAR COMPROBANTE
    sec_comprobante_pagar_cpp_moneda_listar();
    sec_comprobante_pagar_cd_moneda_listar();
    sec_comp_form_pagar_cd_btn_subir($('#form_comp_pagar_cd_param_archivo'));
    sec_comp_form_pagar_cpp_btn_subir($('#form_comp_pagar_cpp_param_archivo'));
    sec_comp_form_da_btn_ac_subir($('#form_comp_da_param_ac_archivo'));
    sec_comp_form_da_btn_oc_subir($('#form_comp_da_param_oc_archivo'));
    sec_comp_form_da_btn_cpdf_subir($('#form_comp_da_param_cpdf_archivo'));
    sec_comp_form_da_btn_cxml_subir($('#form_comp_da_param_cxml_archivo'));
    sec_comp_form_da_btn_cs_subir($('#form_comp_da_param_cs_archivo'));
    sec_comp_form_da_btn_gr_subir($('#form_comp_da_param_gr_archivo'));

    $('.comp_limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});

	$('.comp_limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val(0).trigger("change");
	});

    $('.comp_limpiar_vacio_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
	});

    $('#btn_comprobante_historico_limpiar_filtros_de_busqueda').click(function() {
        $('#search_comp_campo_id').select2().val("").trigger("change");
    });

    $('#btn_limpiar_filtros_de_busqueda').click(function() {
		$('#search_comp_proveedor_id').select2().val(0).trigger("change");
		$('#search_comp_razon_social_id').select2().val(0).trigger("change");
		$('#search_comp_etapa_id').select2().val(0).trigger("change");
		$('#search_comp_fecha_inicio_registro').val('');
		$('#search_comp_fecha_fin_registro').val('');
		$('#search_comp_fecha_inicio_emision').val('');
		$('#search_comp_fecha_fin_emision').val('');
        $('#search_comp_estado_id').select2().val('').trigger("change");
        sec_comprobante_listar_datatable();

	});

    $('#btn_exportar_filtros_de_busqueda').click(function() {
		
        var proveedor_id = $("#search_comp_proveedor_id").val();
        var razon_social_id = $("#search_comp_razon_social_id").val();
        var etapa_id = $("#search_comp_etapa_id").val();
        var estado_id = $("#search_comp_estado_id").val();
    
        var fecha_inicio_registro = $("#search_comp_fecha_inicio_registro").val();
        var fecha_fin_registro = $("#search_comp_fecha_fin_registro").val();
        var fecha_inicio_emision = $("#search_comp_fecha_inicio_emision").val();
        var fecha_fin_emision = $("#search_comp_fecha_fin_emision").val();
    
    
        
        if (fecha_inicio_registro.length > 0 && fecha_fin_registro.length > 0) {
            var fecha_inicio_date = new Date(fecha_inicio_registro);
            var fecha_fin_date = new Date(fecha_fin_registro);
            if (fecha_inicio_date > fecha_fin_date) {
                alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
                return false;
            }
        }
        if (fecha_inicio_emision.length > 0 && fecha_fin_emision.length > 0) {
            var fecha_inicio_date = new Date(fecha_inicio_emision);
            var fecha_fin_date = new Date(fecha_fin_emision);
            if (fecha_inicio_date > fecha_fin_date) {
                alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta', 5);
                return false;
            }
        }
    
        var data = {
            "accion": "comp_exportar_listado",
            "proveedor_id": proveedor_id,
            "razon_social_id": razon_social_id,
            "etapa_id": etapa_id,
            "estado_id": estado_id,
            "fecha_inicio_registro": fecha_inicio_registro,
            "fecha_fin_registro": fecha_fin_registro,
            "fecha_inicio_emision": fecha_inicio_emision,
            "fecha_fin_emision": fecha_fin_emision
        }
        $.ajax({
            url: "/sys/get_comprobante_pago.php",
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

	});

}
/*
function sec_comprobante_obtener_registrador_ruc(){
    const data = { accion: 'comprobante_obtener_usuario_registrador_ruc' };

        $.ajax({
            url: "/sys/get_comprobante_pago.php",
            type: "POST",
            data: data,
            beforeSend: function () {
                loading(true);
            },
            complete: function () {
                loading(false);
            },
            success: function (resp) {
                try {
                    const respuesta = JSON.parse(resp);
    
                    if (respuesta.http_code === 200) {
                        const selectOptions = {
                            width: '100%',
                            language: {
                                noResults: function() {
                                    return "No está registrado el número de RUC, contactarse con contadora de cuentas por pagar - " + respuesta.descripcion;
                                }
                            }
                        };
    
                        $(".sec_comp_ruc_proveedor_select_filtro, .sec_comp_ruc_at_select_filtro").select2(selectOptions);

                        
                        $(".sec_comp_ruc_proveedor_select_filtro, .sec_comp_ruc_at_select_filtro").select2(selectOptions).on('select2:open', function() {
                            var $input = $(this).data('select2').$dropdown.find('input');
                            $input.on('input', function() {
                                var value = $(this).val();
                                var isNumeric = /^\d*$/.test(value);
                                if (!isNumeric) {
                                    $(this).val(value.replace(/\D/g, ''));
                                    $(this).trigger('change');
                                }
                            });
                        });
                    
                    } else {
                        swal({
                            title: respuesta.titulo || 'Error',
                            text: respuesta.descripcion,
                            html: true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                    }
                } catch (error) {
                    console.error("Error parsing response:", error);
                    swal({
                        title: 'Error',
                        text: 'Hubo un problema al procesar la respuesta.',
                        type: "error",
                        closeOnConfirm: false,
                        showCancelButton: false
                    });
                }
            },
            error: function (resp, status) {
                console.error("AJAX error:", status, resp);
                swal({
                    title: 'Error',
                    text: 'Hubo un problema con la solicitud.',
                    type: "error",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
            }
        });
    }
*/
function eliminarTexto_ac() {
    document.getElementById('sec_comp_form_da_ac_txt_mensaje').innerText = '';
}
function eliminarTexto_cpdf() {
    document.getElementById('sec_comp_form_da_cpdf_txt_mensaje').innerText = '';
}
function eliminarTexto_cxml() {
    document.getElementById('sec_comp_form_da_cxml_txt_mensaje').innerText = '';
}
function eliminarTexto_cs() {
    document.getElementById('sec_comp_form_da_cs_txt_mensaje').innerText = '';
}

function eliminarTexto_oc() {
    document.getElementById('sec_comp_form_da_oc_txt_mensaje').innerText = '';
}
function eliminarTexto_gr() {
    document.getElementById('sec_comp_form_da_gr_txt_mensaje').innerText = '';
}

function formatAmount(input) {
    // Obtener el valor del campo de monto
    var value = input.value;

    // Eliminar cualquier caracter que no sea un dígito o un punto
    value = value.replace(/[^\d.]/g, '');

    // Si no hay punto decimal, agregar dos decimales
    if (value.indexOf('.') === -1) {
        value = parseFloat(value).toFixed(2);
    }

    // Formatear el valor con separador de miles por comas y dos decimales
    var parts = value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    input.value = parts.join('.');

    // Asegurar que se mantenga el punto decimal si está presente
    if (parts.length > 1) {
        input.value = input.value.replace(/\.$/, '');
    }
}


// FIN: FUNCIONES INICIALIZADOS

//////////   FUNCIONES PARA RAZON SOCIAL

$("#sec_comprobante_pago_btn_nuevo").off("click").on("click",function(){
    sec_comprobante_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#accordionCompCamposAuditoria").hide();
    var botonesDescarga = document.getElementsByClassName('btn-descargar-da');
    for (var i = 0; i < botonesDescarga.length; i++) {
        botonesDescarga[i].style.display = 'none';
    }

    var botonesSubir = document.getElementsByClassName('btn-subir-da');
    for (var i = 0; i < botonesSubir.length; i++) {
        botonesSubir[i].style.display = 'inline-block';
    }
    $("#sec_comprobante_modal_guardar_titulo").text("Nuevo Comprobante");
    $("#sec_comprobante_modal_nuevo").modal("show");
    })

function sec_comprobante_limpiar_input()
{
    //  DATOS DEL COMPROBANTE DE PAGO

	$('#form_modal_sec_comp_param_id').val(0);
    $('#form_modal_sec_comp_param_tipo_comprobante_id').val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_num_documento_prefijo').val("");
    $('#form_modal_sec_comp_param_num_documento_sufijo').val("");
    $('#form_modal_sec_comp_param_fecha_emision').val("");
    $('#form_modal_sec_comp_param_fecha_vencimiento').val("");
	$("#form_modal_sec_comp_param_proveedor_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_proveedor_nombre').val("");
    $("#form_modal_sec_comp_param_razon_social_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_razon_social_nombre').val("");
    $("#form_modal_sec_comp_param_moneda_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_monto').val("");
    $("#form_modal_sec_comp_param_area_id").val(0).trigger("change.select2");

    //  DATOS DE ORDEN DE COMPRA

	$('#form_modal_sec_comp_op_param_num_orden_pago').val("");
    $("#form_modal_sec_comp_op_param_ceco_id").val('').trigger("change.select2");
    $('#form_modal_sec_comp_op_param_ceco_descripcion').val("");

    //  ARCHIVOS ADJUNTOS

    $('#sec_comp_form_da_ac_txt_mensaje').html('');
    $('#sec_comp_form_da_cpdf_txt_mensaje').html('');
    $('#sec_comp_form_da_cxml_txt_mensaje').html('');
    $('#sec_comp_form_da_cs_txt_mensaje').html('');
    $('#sec_comp_form_da_oc_txt_mensaje').html('');
    $('#sec_comp_form_da_gr_txt_mensaje').html('');

    var botonesVer = document.getElementsByClassName('btn-ver-da');
    for (var i = 0; i < botonesVer.length; i++) {
        botonesVer[i].style.display = 'none';
        }


    }

function comp_pago_fn_cambiarRequisitoArchivoXml() {
    var selectValue = document.getElementById("form_modal_sec_comp_param_tipo_comprobante_id").value;
    var mensajeSpanSi = document.getElementById('comp_pago_opcion_documento_archivo_cxml_si');
    var mensajeSpanNo = document.getElementById('comp_pago_opcion_documento_archivo_cxml_no');

    mensajeSpanSi.style.display = "none";
    mensajeSpanNo.style.display = "none";

    if(selectValue!='0'){
        if(selectValue == 1  || selectValue == 3){
            mensajeSpanSi.style.display = "inline";
            mensajeSpanNo.style.display = "none";
        } else {
            mensajeSpanSi.style.display = "none";
            mensajeSpanNo.style.display = "inline";
        }
    }
    }

$("#sec_comprobante_modal_nuevo .btn_guardar").off("click").on("click",function(){

    //  DATOS DEL COMPROBANTE DE PAGO /////////////////////////////////////////////////////////////////
    
        var param_tipo_comprobante_id = $("#form_modal_sec_comp_param_tipo_comprobante_id").val();
        var param_num_documento_prefijo = $("#form_modal_sec_comp_param_num_documento_prefijo").val();
        var param_num_documento_sufijo = $("#form_modal_sec_comp_param_num_documento_sufijo").val();

        var param_fecha_emision = $("#form_modal_sec_comp_param_fecha_emision").val();
        var param_fecha_vencimiento = $("#form_modal_sec_comp_param_fecha_vencimiento").val();
        var param_proveedor_id = $("#form_modal_sec_comp_param_proveedor_id").val();
        var param_razon_social_id = $("#form_modal_sec_comp_param_razon_social_id").val();
        var param_monto = $("#form_modal_sec_comp_param_monto").val();
        var param_moneda_id = $("#form_modal_sec_comp_param_moneda_id").val();
        var param_area_id = $("#form_modal_sec_comp_param_area_id").val();

        if(param_tipo_comprobante_id == 0){
            alertify.error('Seleccione el tipo de comprobante',5);
            $("#form_modal_sec_comp_param_tipo_comprobante_id").focus();
            return false;
        }

        if (param_num_documento_prefijo.length < 4) {
            alertify.error('Ingrese 4 digitos para la serie del número de documento', 5);
            $("#form_modal_sec_comp_param_num_documento_prefijo").focus();
            return false;
        }
        
        if (param_num_documento_sufijo.length > 8 || param_num_documento_sufijo.length <=1) {
            alertify.error('Ingrese mas de 1 digito para el correlativo del número de documento', 5);
            $("#form_modal_sec_comp_param_num_documento_sufijo").focus();
            return false;
        }
        
        if (param_fecha_emision.length == 0) {
            alertify.error('Ingrese una fecha de emisión', 5);
            $("#form_modal_sec_comp_param_fecha_emision").focus();
            return false;
        }

        if (param_fecha_vencimiento.length == 0) {
            alertify.error('Ingrese una fecha de vencimiento', 5);
            $("#form_modal_sec_comp_param_fecha_vencimiento").focus();
            return false;
        }
        
        if (param_fecha_emision.length > 0 && param_fecha_vencimiento.length > 0) {
            var fecha_inicio_date = new Date(param_fecha_emision);
            var fecha_fin_date = new Date(param_fecha_vencimiento);
            if (fecha_inicio_date > fecha_fin_date) {
                alertify.error('La fecha de emisión debe ser menor o igual a la fecha de vencimiento.', 5);
                return false;
            }
        }

        if(param_proveedor_id == 0){
            alertify.error('Seleccione el proveedor',5);
            $("#form_modal_sec_comp_param_proveedor_id").focus();
            return false;
        }

        if(param_razon_social_id == 0){
            alertify.error('Seleccione la Empresa AT',5);
            $("#form_modal_sec_comp_param_razon_social_id").focus();
            return false;
        }

        if(param_moneda_id == 0){
            alertify.error('Seleccione la moneda',5);
            $("#form_modal_sec_comp_param_moneda_id").focus();
            return false;
        }

        if(param_area_id == 0){
            alertify.error('Seleccione el área',5);
            $("#form_modal_sec_comp_param_area_id").focus();
            return false;
        }

        if(param_monto.length == 0){
            alertify.error('Ingrese el monto',5);
            $("#form_modal_sec_comp_param_monto").focus();
            return false;
        }

    ////DATOS DE FORMA DE PAGO  //////////////////////////////////////////////////////////////////

        var param_num_orden_pago = $("#form_modal_sec_comp_op_param_num_orden_pago").val();
        var param_ceco_id = $("#form_modal_sec_comp_op_param_ceco_id").val();

        if (param_num_orden_pago.length == 0) {
            alertify.error('Ingrese el número de Orden de Pago', 5);
            $("#form_modal_sec_comp_op_param_num_orden_pago").focus();
            return false;
        }

        if(param_ceco_id == ''){
            alertify.error('Seleccione el ceco',5);
            $("#form_modal_sec_comp_op_param_ceco_id").focus();
            return false;
        }


    ////DATOS DE DOCUMENTOS ADJUNTOS  //////////////////////////////////////////////////////////////////
    var param_ac_archivo_nombre = $("#sec_comp_form_da_ac_txt_mensaje").html();
    var param_cpdf_archivo_nombre = $("#sec_comp_form_da_cpdf_txt_mensaje").html();
    var param_cxml_archivo_nombre = $("#sec_comp_form_da_cxml_txt_mensaje").html();
    var param_cs_archivo_nombre = $("#sec_comp_form_da_cs_txt_mensaje").html();
    var param_gr_archivo_nombre = $("#sec_comp_form_da_gr_txt_mensaje").html();
    var param_oc_archivo_nombre = $("#sec_comp_form_da_oc_txt_mensaje").html();


    var regexPDF = /\.pdf$/i;
    var regexXML = /\.xml$/i;

    if (param_ac_archivo_nombre.length != 0) {
        if (!regexPDF.test(param_ac_archivo_nombre)) {

                alertify.error('El archivo de la Acta de Conformidad debe estar en formato PDF', 5);
                //$("#form_comp_da_param_ac_archivo").focus();
                return false;
        }
    }

    if (param_cpdf_archivo_nombre.length == 0) {
		alertify.error('Ingrese el Comprobante(PDF)', 5);
		//$("#form_comp_da_param_cpdf_archivo").focus();
		return false;
	}else{
        if (!regexPDF.test(param_cpdf_archivo_nombre)) {

            alertify.error('El archivo del Comprobante (PDF) debe estar en formato PDF', 5);
            //$("#form_comp_da_param_ac_archivo").focus();
            return false;
        }
    }

    if(param_tipo_comprobante_id == 1  || param_tipo_comprobante_id == 3){
        if (param_cxml_archivo_nombre.length == 0) {
            alertify.error('Ingrese el Comprobante(XML)', 5);
            //$("#sec_comp_form_da_cxml_txt_mensaje").focus();
            return false;
        }else{
            if (!regexXML.test(param_cxml_archivo_nombre)) {
                alertify.error('El archivo del Comprobante(XML) debe estar en formato XML', 5);
               // $("#sec_comp_form_da_cxml_txt_mensaje").focus();
                return false;
            }
        }

        if (param_cs_archivo_nombre.length == 0 && param_oc_archivo_nombre.length == 0) {
            alertify.error('Ingrese el Contrato de servicio o licencia firmado(PDF) o la Orden de Compra(PDF)', 5);
            //$("#sec_comp_form_da_cs_txt_mensaje").focus();
            return false;
        }else{
            
            if (param_cs_archivo_nombre.length != 0 && !regexPDF.test(param_cs_archivo_nombre)) {
    
                alertify.error('El archivo del Contrato de Servicio o Licencia debe estar en formato PDF', 5);
                $("#sec_comp_form_da_cs_txt_mensaje").focus();
                return false;
            }
    
            if (param_oc_archivo_nombre.length != 0 && !regexPDF.test(param_oc_archivo_nombre)) {
    
                alertify.error('El archivo de Orden de Compra debe estar en formato PDF', 5);
                $("#sec_comp_form_da_cs_txt_mensaje").focus();
                return false;
            }
            
        }
    }

    if (param_gr_archivo_nombre.length != 0) {

        if (!regexPDF.test(param_gr_archivo_nombre)) {

                alertify.error('El archivo de la Guia de Remisión debe estar en formato PDF', 5);
                $("#sec_comp_form_da_gr_txt_mensaje").focus();
                return false;
        }
    }

    sec_comprobante_guardar();     

    })

function sec_comprobante_ver(param_id) {
    sec_comprobante_obtener(param_id);

    $("#accordionCompCamposAuditoria").show();

    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });

    var botonesDescarga = document.getElementsByClassName('btn-subir-da');
    for (var i = 0; i < botonesDescarga.length; i++) {
        botonesDescarga[i].style.display = 'none';
        }

    var botonesEliminar = document.getElementsByClassName('btn-eliminar-texto');
        for (var i = 0; i < botonesEliminar.length; i++) {
            botonesEliminar[i].style.display = 'none';
            }
    $("#sec_comprobante_modal_guardar_titulo").text("Ver Comprobante");
    $("#sec_comprobante_modal_nuevo").modal("show");

}

function sec_comprobante_obtener(param_id) {

    $("#accordionCompCamposAuditoria").hide();

    $("#sec_comprobante_modal_guardar_titulo").text("Editar Comprobante");

    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });

    var botonesEliminar = document.getElementsByClassName('btn-eliminar-texto');
    for (var i = 0; i < botonesEliminar.length; i++) {
        botonesEliminar[i].style.display = 'inline-block';
        }

    var botonesDescarga = document.getElementsByClassName('btn-subir-da');
    for (var i = 0; i < botonesDescarga.length; i++) {
        botonesDescarga[i].style.display = 'inline-block';
        }

    let data = {
            id : param_id,
            accion:'comprobante_obtener'
        }

    $.ajax({
            url:  "/sys/get_comprobante_pago.php",
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

                var botonesDescarga = document.getElementsByClassName('btn-descargar-da');
                for (var i = 0; i < botonesDescarga.length; i++) {
                    botonesDescarga[i].style.display = 'inline-block';
                }

                var botonesVer = document.getElementsByClassName('btn-ver-da');
                for (var i = 0; i < botonesVer.length; i++) {
                    botonesVer[i].style.display = 'inline-block';
                    }
            
                //  DATOS DEL COMPROBANTE DE PAGO

                $('#form_modal_sec_comp_param_id').val(param_id);
                $('#form_modal_sec_comp_param_tipo_comprobante_id').val(respuesta.result.tipo_comprobante_id).trigger("change.select2");
                console.log(respuesta.result.tipo_comprobante_id);

                var num_documento = respuesta.result.num_documento;
                var num_documento_prefijo = num_documento.substr(0,4);
                var num_documento_sufijo =num_documento.substr(5);

                $('#form_modal_sec_comp_param_num_documento_prefijo').val(num_documento_prefijo);
                $('#form_modal_sec_comp_param_num_documento_sufijo').val(num_documento_sufijo);

                $('#form_modal_sec_comp_param_fecha_emision').val(respuesta.result.fecha_emision);
                $('#form_modal_sec_comp_param_fecha_vencimiento').val(respuesta.result.fecha_vencimiento);
                $('#form_modal_sec_comp_param_proveedor_id').val(respuesta.result.proveedor_id).trigger("change.select2");
                sec_comprobante_proveedor_obtener_nombre();
                $('#form_modal_sec_comp_param_razon_social_id').val(respuesta.result.razon_social_id).trigger("change.select2");
                sec_comprobante_empresa_at_obtener_nombre();
                
                var montoOriginal = respuesta.result.monto;
                var montoNumero = parseFloat(montoOriginal);
                if (montoOriginal.indexOf('.') !== -1) {
                    var montoFormateado = montoNumero.toLocaleString('en-US', {minimumFractionDigits: 2});
                } else {
                    var montoFormateado = montoNumero.toLocaleString('en-US', {minimumFractionDigits: 0});
                }

                // Establecer el valor formateado en el campo de texto
                $('#form_modal_sec_comp_param_monto').val(montoFormateado);//$('#form_modal_sec_comp_param_monto').val(parseFloat(respuesta.result.monto).toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                //$('#form_modal_sec_comp_param_monto').val(respuesta.result.monto);
                $('#form_modal_sec_comp_param_moneda_id').val(respuesta.result.moneda_id).trigger("change.select2");
                $('#form_modal_sec_comp_param_area_id').val(respuesta.result.area_id).trigger("change.select2");

                //  DATOS DE ORDEN DE COMPRA
                $('#form_modal_sec_comp_op_param_num_orden_pago').val(respuesta.result.oc_num_orden_pago);
                $('#form_modal_sec_comp_op_param_ceco_id').val(respuesta.result.oc_ceco_id).trigger("change.select2");
                sec_comprobante_ceco_obtener_descripcion();

                //  DATOS DE ORDEN DE COMPRA
                $('#form_modal_sec_comp_fp_param_banco_id').val(respuesta.result.fp_banco_id).trigger("change.select2");
                $('#form_modal_sec_comp_fp_param_moneda_id').val(respuesta.result.fp_moneda_id).trigger("change.select2");
                $('#form_modal_sec_comp_fp_param_num_cuenta_corriente').val(respuesta.result.fp_num_cuenta_corriente);
                $('#form_modal_sec_comp_fp_param_num_cuenta_interbancaria').val(respuesta.result.fp_num_cuenta_interbancaria);

                //  DOCUMENTOS ADJUNTOS

                $("#sec_comp_form_da_cpdf_txt_mensaje").html(respuesta.result.ad_comprobante_pdf);
                var boton_cpdf = document.getElementById("sec_comp_form_da_btn_cpdf_ver");
                if(respuesta.result.ad_comprobante_pdf == ""){
                    boton_cpdf.disabled = true;
                }else{
                    boton_cpdf.disabled = false;
                }
                $("#sec_comp_form_da_cxml_txt_mensaje").html(respuesta.result.ad_comprobante_xml);
                var boton_cxml = document.getElementById("sec_comp_form_da_btn_cxml_descargar");
                if(respuesta.result.ad_comprobante_xml == ""){
                    boton_cxml.disabled = true;
                }else{
                    boton_cxml.disabled = false;
                }
                $("#sec_comp_form_da_cs_txt_mensaje").html(respuesta.result.ad_contrato_servicio);

                var boton_cs = document.getElementById("sec_comp_form_da_btn_cs_descargar");
                if(respuesta.result.ad_contrato_servicio == ""){
                    boton_cs.disabled = true;
                }else{
                    boton_cs.disabled = false;
                }
                $("#sec_comp_form_da_gr_txt_mensaje").html(respuesta.result.ad_guia_remision);
                var boton_gr = document.getElementById("sec_comp_form_da_btn_gr_descargar");
                if(respuesta.result.ad_guia_remision == ""){
                    boton_gr.disabled = true;
                }else{
                    boton_gr.disabled = false;
                }
                $("#sec_comp_form_da_ac_txt_mensaje").html(respuesta.result.ad_acta_conformidad);

                var boton_ac = document.getElementById("sec_comp_form_da_btn_ac_descargar");
                if(respuesta.result.ad_acta_conformidad == ""){
                    boton_ac.disabled = true;
                }else{
                    boton_ac.disabled = false;
                }
                
                             
                $("#sec_comp_form_da_oc_txt_mensaje").html(respuesta.result.ad_orden_compra);
                
                var boton_oc = document.getElementById("sec_comp_form_da_btn_oc_descargar");
                if(respuesta.result.ad_orden_compra == ""){
                    boton_oc.disabled = true;
                }else{
                    boton_oc.disabled = false;
                }
                
                
                if(respuesta.result.created_at==""){
                    document.getElementById('compCampoFechaCreacion').style.display = 'none';

                }else{
                    document.getElementById('compCampoFechaCreacion').style.display = 'block';
                    $('#form_modal_sec_comp_ca_param_fecha_create').val(respuesta.result.created_at);
                    $('#form_modal_sec_comp_ca_param_usuario_create').val(respuesta.result.usuario_create);
                }
                if(respuesta.result.updated_at==""){
                    document.getElementById('compCampoFechaActualiacion').style.display = 'none';

                }else{
                    document.getElementById('compCampoFechaActualiacion').style.display = 'block';
                    $('#form_modal_sec_comp_ca_param_fecha_update').val(respuesta.result.updated_at);
                    $('#form_modal_sec_comp_ca_param_usuario_update').val(respuesta.result.usuario_update);    
                }

            	$("#sec_comprobante_modal_nuevo").modal("show");
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

function  sec_comprobante_obtener_historico_cambios(comprobante_id) {
	
   // var num_documento = this.dataset.numDocumento;

    $('#modalComprobantesHistoricoCambios').modal('show');
    $('#modal_title_comprobante_historico').html((comprobante_id + ' - HISTORIAL DE CAMBIOS').toUpperCase());
    $('#search_comp_id').val(comprobante_id);
    sec_comprobante_historico_listar_Datatable();
    sec_comprobante_historico_etapas_listar_Datatable(comprobante_id);

}

function sec_comprobante_historico_listar_Datatable() {
    if (sec_id == "comprobante" && sub_sec_id == "pago") {
        var id_campo = $("#search_comp_campo_id").val();
        var comprobante_id = $("#search_comp_id").val();
        
        console.log(id_campo);
        var data = {
            accion: "comp_obtener_historico",
            comprobante_id:comprobante_id,
            campo_id: id_campo
            };
        $("#comprobante_historico_cambios_div_tabla").show();
        
        $('#comprobante_historico_cambios_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#comprobante_historico_cambios_datatable").dataTable({
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
                        url: "/sys/get_comprobante_pago.php",
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

function sec_comprobante_historico_etapas_listar_Datatable(comprobante_id) {
    if (sec_id == "comprobante" && sub_sec_id == "pago") {

        var data = {
            accion: "comp_obtener_historico_etapas",
            comprobante_id:comprobante_id
            };
        $("#comprobante_historico_etapas_div_tabla").show();
        
        $('#comprobante_historico_etapas_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#comprobante_historico_etapas_datatable").dataTable({
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
                        url: "/sys/get_comprobante_pago.php",
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

function sec_comprobante_historico_listar_campo() {
	let select = $("[name='search_comp_campo_id']");
	let valorSeleccionado = $("#search_comp_campo_id").val();
	
	$.ajax({
		url: "/sys/get_comprobante_pago.php",
		type: "POST",
		data: {
			accion: "comp_obtener_campos"
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
function sec_comprobante_guardar(){
        var id = $('#form_modal_sec_comp_param_id').val();

        //  DATOS DEL COMPROBANTE DE PAGO /////////////////////////////////////////////////////////////////

        var param_tipo_comprobante_id = $("#form_modal_sec_comp_param_tipo_comprobante_id").val();
        var param_num_documento_prefijo = $("#form_modal_sec_comp_param_num_documento_prefijo").val();
        var param_num_documento_sufijo = $("#form_modal_sec_comp_param_num_documento_sufijo").val();
        var param_num_documento = param_num_documento_prefijo+"-"+param_num_documento_sufijo;
        var param_fecha_emision = $("#form_modal_sec_comp_param_fecha_emision").val();
        var param_fecha_vencimiento = $("#form_modal_sec_comp_param_fecha_vencimiento").val();
        var param_proveedor_id = $("#form_modal_sec_comp_param_proveedor_id").val();
        var param_razon_social_id = $("#form_modal_sec_comp_param_razon_social_id").val();
        var param_monto = $("#form_modal_sec_comp_param_monto").val();
        param_monto = param_monto.replace(/,/g, '');
        var param_moneda_id = $("#form_modal_sec_comp_param_moneda_id").val();
        var param_area_id = $("#form_modal_sec_comp_param_area_id").val();

        ////DATOS DE FORMA DE PAGO  //////////////////////////////////////////////////////////////////

        var param_num_orden_pago = $("#form_modal_sec_comp_op_param_num_orden_pago").val();
        var param_ceco_id = $('#form_modal_sec_comp_op_param_ceco_id option:selected').text();

        //// HISTORICO DE CAMBIOS - DATOS DE FORMA DE PAGO  //////////////////////////////////////////////////////////////////
        var tipo_comprobante_id = $('#form_modal_sec_comp_param_tipo_comprobante_id option:selected').text();
        var num_documento_prefijo = $("#form_modal_sec_comp_param_num_documento_prefijo").val().trim();
        var num_documento_sufijo = $("#form_modal_sec_comp_param_num_documento_sufijo").val().trim();
        var num_documento = num_documento_prefijo+"-"+num_documento_sufijo;
        var fecha_emision = $('#form_modal_sec_comp_param_fecha_emision').val().trim();
        var fecha_vencimiento = $('#form_modal_sec_comp_param_fecha_vencimiento').val().trim();
        var proveedor_id = $('#form_modal_sec_comp_param_proveedor_id option:selected').text();
        var razon_social_id = $('#form_modal_sec_comp_param_razon_social_id option:selected').text();
        var monto = $('#form_modal_sec_comp_param_monto').val().trim();
        monto = monto.replace(/,/g, '');
        var moneda_id = $('#form_modal_sec_comp_param_moneda_id option:selected').text();
        var area_id = $('#form_modal_sec_comp_param_area_id option:selected').text();

        var oc_num_orden_pago = $('#form_modal_sec_comp_op_param_num_orden_pago').val().trim();
        var oc_ceco_id = $('#form_modal_sec_comp_op_param_ceco_id option:selected').text();

        var ad_acta_conformidad = $("#sec_comp_form_da_ac_txt_mensaje").html();
        var ad_comprobante_pdf = $("#sec_comp_form_da_cpdf_txt_mensaje").html();
        var ad_comprobante_xml = $("#sec_comp_form_da_cxml_txt_mensaje").html();
        var ad_contrato_servicio = $("#sec_comp_form_da_cs_txt_mensaje").html();
        var ad_guia_remision = $("#sec_comp_form_da_gr_txt_mensaje").html();
        var ad_orden_compra = $("#sec_comp_form_da_oc_txt_mensaje").html();

        if(id == 0)
        {
            // CREAR
            aviso = "¿Está seguro de registrar el comprobante?";
            titulo = "Registrar";
        }
        else
        {
            // EDITAR
            aviso = "¿Está seguro de editar el comprobante?";
            titulo = "Editar";
        }
        
        swal({
                    title: titulo,
                    text: aviso,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#529D73",
                    confirmButtonText: "SI",
                    cancelButtonText: "NO",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
            function (isConfirm) {
                if(isConfirm){
                    var dataForm = new FormData($("#Frm_RegistroComprobante")[0]);
                    dataForm.append("accion", "comprobante_guardar");
                    dataForm.append("id", id);
                    dataForm.append("param_tipo_comprobante_id", param_tipo_comprobante_id);
                    dataForm.append("param_num_documento", param_num_documento);
                    dataForm.append("param_fecha_emision", param_fecha_emision);
                    dataForm.append("param_fecha_vencimiento", param_fecha_vencimiento);
                    dataForm.append("param_proveedor_id", param_proveedor_id);
                    dataForm.append("param_razon_social_id", param_razon_social_id);
                    dataForm.append("param_monto", param_monto);
                    dataForm.append("param_moneda_id", param_moneda_id);
                    dataForm.append("param_area_id", param_area_id);
                    dataForm.append("param_num_orden_pago", param_num_orden_pago);
                    dataForm.append("param_ceco_id", param_ceco_id);

                    dataForm.append("tipo_comprobante_id", tipo_comprobante_id);
                    dataForm.append("num_documento", num_documento);
                    dataForm.append("fecha_emision", fecha_emision);
                    dataForm.append("fecha_vencimiento", fecha_vencimiento);
                    dataForm.append("proveedor_id", proveedor_id);
                    dataForm.append("razon_social_id", razon_social_id);
                    dataForm.append("monto", monto);
                    dataForm.append("moneda_id", moneda_id);
                    dataForm.append("area_id", area_id);
                    dataForm.append("oc_ceco_id", oc_ceco_id);
                    dataForm.append("oc_num_orden_pago", oc_num_orden_pago);

                    dataForm.append("ad_comprobante_pdf", ad_comprobante_pdf);
                    dataForm.append("ad_comprobante_xml", ad_comprobante_xml);
                    dataForm.append("ad_contrato_servicio", ad_contrato_servicio);
                    dataForm.append("ad_guia_remision", ad_guia_remision);
                    dataForm.append("ad_acta_conformidad", ad_acta_conformidad);
                    dataForm.append("ad_orden_compra", ad_orden_compra);

       
                    //auditoria_send({ "respuesta": "comprobante_guardar", "data": dataForm });
                    loading("true");

                    $.ajax({
                    url: "sys/set_comprobante_pago.php",
                    type: 'POST',
                    data: dataForm,
                    cache: false,
                    contentType: false,
                    processData:false,
                    beforeSend: function() {
                        loading("true");
                        },
                    complete: function() {
                        loading();
                           },
                    success: function(resp) {
                        loading("false");
                        var respuesta = JSON.parse(resp);
                        if (parseInt(respuesta.http_code) == 400) {
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
            
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: respuesta.titulo,
                                text:  respuesta.descripcion,
                                html:true,
                                type: "success",
                                closeOnConfirm: false,
                                showCancelButton: false
                                });
                                $('#Frm_RegistroComprobante')[0].reset();
                                $("#form_modal_sec_comp_param_id").val(0);
                                $("#sec_comprobante_modal_nuevo").modal("hide");
                                sec_comprobante_listar_datatable();
                                return false;
                            }      
                        },
                    error: function() {}
                        });
                }else{
                        alertify.error('No se guardaron los cambios',5);
                        return false;
                }
            });
    }

function sec_comprobante_anular(id_comprobante){

    swal({
            title: '¿Está seguro de anular el comprobante?',
            text: '<input type="text" id="txtMotivo" name="txtMotivo" class="form-control" placeholder="Ingresar motivo" style="display:block;">',
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
                let motivo = $('#txtMotivo').val().trim();
    
                if (motivo === "") {
                    alertify.error('Ingrese un motivo para continuar', 5);
                    $("#txtMotivo").focus();
                    return false;
                }
    
                let data = {
                    comprobante_id: id_comprobante,
                    accion: 'comprobante_eliminar',
                    motivo: motivo 
                };
    
                $.ajax({
                    url: "sys/set_comprobante_pago.php",
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
                            "proceso": "comprobante_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El comprobante se eliminó correctamente.",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_comprobante_listar_datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el comprobante",
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
    function toggleMotivo() {
        if ($('#selectMotivo').val() == 4 ) {
            $('#txtMotivoObservar').show();
        } else {
            $('#txtMotivoObservar').hide();
        }
    }
   

function btn_comp_cambiar_etapa(etapa_id,id_comprobante,  etapa_nombre){
    if(etapa_id == 5){

        //  DATOS DE FORMA DE PAGO

        $("#form_modal_sec_comp_pagar_fp_param_banco_id").val(0).trigger("change.select2");
        $("#form_modal_sec_comp_pagar_fp_param_moneda_id").val(0).trigger("change.select2");
        $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente').val("");
        $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente_interbancaria').val("");
        $('#form_modal_sec_comp_pagar_cd_param_monto').val("");
        $("#form_modal_sec_comp_pagar_cd_param_moneda_id").val('').trigger("change.select2");
		$('#sec_comp_form_pagar_cd_btn_txt_mensaje').html('');

        $('#form_modal_sec_comp_pagar_cpp_param_monto').val("");
        $("#form_modal_sec_comp_pagar_cpp_param_moneda_id").val('').trigger("change.select2");
		$('#sec_comp_form_pagar_cpp_btn_txt_mensaje').html('');

        $("#modalPagarComprobante").modal("show");
        $("#modal_title_comprobante_pagar").text("PAGO DE COMPROBANTE");
        $('#form_modal_sec_comp_pagar_id').val(id_comprobante);

        $('#modalPagarComprobante').on('hidden.bs.modal', function (e) {
            sec_comprobante_listar_datatable();
            //$('#sec_comprobante_pago_div_listar_datatable').DataTable().ajax.reload();
        });

        //  OBTENER DATA DE PAGO EN CASO EXISTAN
        let data = {
            id : id_comprobante,
            accion:'comprobante_obtener_pago'
        }

        $.ajax({
            url:  "/sys/get_comprobante_pago.php",
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
                
                //  DATOS DEL COMPROBANTE DE PAGO

                $('#form_modal_sec_comp_pagar_id').val(respuesta.result.id);

                //  DATOS DE ORDEN DE COMPRA
                $('#form_modal_sec_comp_pagar_fp_param_banco_id').val(respuesta.result.fp_banco_id).trigger("change.select2");
                $('#form_modal_sec_comp_pagar_fp_param_moneda_id').val(respuesta.result.fp_moneda_id).trigger("change.select2");
                $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente').val(respuesta.result.fp_num_cuenta_corriente);
                $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente_interbancaria').val(respuesta.result.fp_num_cuenta_interbancaria);

                var pd_montoOriginal = respuesta.result.cd_pd_monto;
                var pd_montoNumero = parseFloat(pd_montoOriginal);
                if (pd_montoOriginal.indexOf('.') !== -1) {
                    var pd_montoFormateado = pd_montoNumero.toLocaleString('en-US', {minimumFractionDigits: 2});
                } else {
                    var pd_montoFormateado = pd_montoNumero.toLocaleString('en-US', {minimumFractionDigits: 0});
                }
                $('#form_modal_sec_comp_pagar_cd_param_monto').val(pd_montoFormateado);
                $('#form_modal_sec_comp_pagar_cd_param_moneda_id').val(respuesta.result.cd_pd_moneda_id).trigger("change.select2");

                var pp_montoOriginal = respuesta.result.cd_pp_monto;
                var pp_montoNumero = parseFloat(pp_montoOriginal);
                if (pp_montoOriginal.indexOf('.') !== -1) {
                    var pp_montoFormateado = pp_montoNumero.toLocaleString('en-US', {minimumFractionDigits: 2});
                } else {
                    var pp_montoFormateado = pp_montoNumero.toLocaleString('en-US', {minimumFractionDigits: 0});
                }
                $('#form_modal_sec_comp_pagar_cpp_param_monto').val(pp_montoFormateado);
                $('#form_modal_sec_comp_pagar_cpp_param_moneda_id').val(respuesta.result.cd_pp_moneda_id).trigger("change.select2");
                //  CONSTANCIAS
                
                $("#sec_comp_form_pagar_cd_btn_txt_mensaje").html(respuesta.result.cd_pago_detraccion);
                $("#sec_comp_form_pagar_cpp_btn_txt_mensaje").html(respuesta.result.cd_pago_proveedor);
       
                $('#form_modal_sec_comp_pagado').val(1);

                $("#modal_title_comprobante_pagar").text("Editar Datos de Pago");
                }
            else
                {
                $('#form_modal_sec_comp_pagado').val(0);

                $("#form_modal_sec_comp_pagar_fp_param_banco_id").val(0).trigger("change.select2");
                $("#form_modal_sec_comp_pagar_fp_param_moneda_id").val(0).trigger("change.select2");
                $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente').val("");
                $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente_interbancaria').val("");
                
                }    
            },
            error: function (resp, status) {},
            });

    }else if(etapa_id == 4 || etapa_id == 6 || etapa_id == 7){
        
        if(etapa_id == 7){
        //  MOTIVO PARA REVERSAR

            campo_motivo = `
            <div id="campo_motivo">
                <!-- El select se generará aquí -->
            </div>
            <div style="margin-top: 10px;">
                <textarea id="txtMotivoObservar" name="txtMotivoObservar" class="form-control" placeholder="Ingresar motivo" style="display:none; height: 60px; overflow-y: scroll;" rows="2"></textarea>
            </div>
            `;
        }else{
            //  MOTIVO PARA OBSERVAR Y OBSERVAR POR TESORERIA
            campo_motivo ='<textarea id="txtMotivoObservar" name="txtMotivoObservar" class="form-control" placeholder="Ingresar motivo" maxlength="200" style="display:block; height: 60px; overflow-y: scroll;" rows="2"></textarea>';

        }
        sec_comprobante_motivo_listar();

        swal({
            title: '¿Está seguro cambiar de etapa a ' + etapa_nombre + '?',
            text: campo_motivo,
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
                if(etapa_id == 7){
                    var seleccion = $('#selectMotivo option:selected').text();
                    if (seleccion == 4) {
                        motivo = $('#txtMotivoObservar').val();
                    } else {
                        motivo = seleccion;
                    }
                    if (motivo == "") {
                        alertify.error('Ingrese un motivo para continuar', 5);
                        $("#txtMotivoObservar").focus();
                        return false;
                    }                  
                }else{
                    var motivo = $('#txtMotivoObservar').val();
    
                    if (motivo == "") {
                        alertify.error('Ingrese un motivo para continuar', 5);
                        $("#txtMotivoObservar").focus();
                        return false;
                    }
                }
    
                let data = {
                    comprobante_id: id_comprobante,
                    accion: 'comprobante_observar',
                    motivo: motivo,
                    etapa_id: etapa_id
                };
    
                $.ajax({
                    url: "sys/set_comprobante_pago.php",
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
                        var titulo = etapa_nombre + " exitosamente";
                        
                        auditoria_send({
                            "proceso": "comprobante_observar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: titulo,
                                text: "El comprobante se cambió de etapa correctamente.",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_comprobante_listar_datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al cambiar de etapa",
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
    else{
        swal({
            title: '¿Está seguro cambiar de etapa a ' + etapa_nombre + '?',
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
                        comprobante_id: id_comprobante,
                        accion: 'comp_etapa_cambiar',
                        etapa_id: etapa_id
                    };
        
                    $.ajax({
                        url: "sys/set_comprobante_pago.php",
                        type: 'POST',
                        data: data,
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                        },
                        success: function (resp) {
                            var titulo = "Comprobante " + etapa_nombre + " exitosamente";
                            var respuesta = JSON.parse(resp);
                            auditoria_send({
                                "proceso": "comp_etapa_cambiar",
                                "data": respuesta
                            });
                            if (parseInt(respuesta.http_code) == 200) {
                                swal({
                                    title: titulo,
                                    text: "El comprobante se cambió de etapa correctamente.",
                                    type: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                setTimeout(function () {
                                    sec_comprobante_listar_datatable();
                                }, 2000);
                            } else {
                                swal({
                                    title: "Error al cambiar de etapa",
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
    }

//////////   FUNCIONES DEL FORMULARIO DE REGISTRO


function sec_comprobante_tipos_listar() {
    let select = $("[name='form_modal_sec_comp_param_tipo_comprobante_id']");
    let valorSeleccionado = $("#form_modal_sec_comp_param_tipo_comprobante_id").val();

    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_tipo_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccione</option>");
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
            console.error("Error al obtener la lista de tipos de comprobantes.");
        }
    });
}


function sec_comprobante_proveedor_listar(){
        let select = $("[name='form_modal_sec_comp_param_proveedor_id']");
        let valorSeleccionado = $("#form_modal_sec_comp_param_proveedor_id").val();
        
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_proveedor_listar_ruc"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccione</option>");
                    $(select).append(opcionDefault);
                }
        
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.ruc + "</option>");
                    $(select).append(opcion);
                    });
        
                    if (valorSeleccionado != 0) {
                        $(select).val(valorSeleccionado);
                    }
                },
            error: function () {
                console.error("Error al obtener la lista de tipos de comprobantes.");
                }
        });
    }

function sec_comprobante_empresa_at_listar(){
        let select = $("[name='form_modal_sec_comp_param_razon_social_id']");
        let valorSeleccionado = $("#form_modal_sec_comp_param_razon_social_id").val();
        
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_empresa_at_listar_ruc"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccione</option>");
                    $(select).append(opcionDefault);
                }
        
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.ruc + "</option>");
                    $(select).append(opcion);
                    });
        
                    if (valorSeleccionado != 0) {
                        $(select).val(valorSeleccionado);
                    }
                },
            error: function () {
                console.error("Error al obtener la lista de empresas AT.");
                }
        });
    }

function sec_comprobante_moneda_listar(){
        let select = $("[name='form_modal_sec_comp_param_moneda_id']");
        let valorSeleccionado = $("#form_modal_sec_comp_param_moneda_id").val();
        
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_moneda_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccione</option>");
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
                console.error("Error al obtener la lista de empresas AT.");
                }
        });
    }

function sec_comprobante_area_listar(){
        let select = $("[name='form_modal_sec_comp_param_area_id']");
        let valorSeleccionado = $("#form_modal_sec_comp_param_area_id").val();
        
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_area_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccione</option>");
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
                console.error("Error al obtener la lista de áreas.");
                }
        });
    }
function sec_comprobante_ceco_listar(){
        let select = $("[name='form_modal_sec_comp_op_param_ceco_id']");
        let valorSeleccionado = $("#form_modal_sec_comp_op_param_ceco_id").val();
        
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_ceco_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccione</option>");
                    $(select).append(opcionDefault);
                }
        
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.cc_id + "</option>");
                    $(select).append(opcion);
                    });
        
                    if (valorSeleccionado != 0) {
                        $(select).val(valorSeleccionado);
                    }
                },
            error: function () {
                console.error("Error al obtener la lista de áreas.");
                }
        });
    }

function sec_comprobante_fp_banco_listar(){
    let select = $("[name='form_modal_sec_comp_pagar_fp_param_banco_id']");
    let valorSeleccionado = $("#form_modal_sec_comp_pagar_fp_param_banco_id").val();
        
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_banco_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccione</option>");
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
                console.error("Error al obtener la lista de áreas.");
                }
        });
    }

function sec_comprobante_fp_moneda_listar(){
    let select = $("[name='form_modal_sec_comp_pagar_fp_param_moneda_id']");
    let valorSeleccionado = $("#form_modal_sec_comp_pagar_fp_param_moneda_id").val();
        
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_moneda_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value=0 selected>Seleccione</option>");
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
                console.error("Error al obtener la lista de áreas.");
                }
        });
    }

//////////   FUNCIONES DEL FILTROS DE BUSQUEDA

function sec_comprobante_filtro_proveedor_listar(){
    let select = $("[name='search_comp_proveedor_id']");
    
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_proveedor_listar_ruc_nombre"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
                  
                },
        error: function () {
            console.error("Error al obtener la lista de tipos de comprobantes.");
            }
        });
}

function sec_comprobante_filtro_razon_social_listar(){
    let select = $("[name='search_comp_razon_social_id']");
    
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_empresa_at_listar_nombre"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
                
            },
        error: function () {
            console.error("Error al obtener la lista de tipos de comprobantes.");
            }
        });
}

function sec_comprobante_filtro_etapa_listar(){
    let select = $("[name='search_comp_etapa_id']");
    
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_etapa_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
                
            },
        error: function () {
            console.error("Error al obtener la lista de tipos de etapas.");
            }
        });
}


function sec_comprobante_motivo_listar() {
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_motivo_listar"
        },
        success: function (datos) {
            var respuesta = JSON.parse(datos);

            let selectOptions = '<option value="">Seleccione</option>';
            $(respuesta.result).each(function (i, e) {
                selectOptions += '<option value="' + e.id + '">' + e.nombre + '</option>';
            });

            // Crear el select con las opciones
            let select = `
                <label>
                    Seleccione el motivo:
                </label>
                <select id="selectMotivo" class="form-control" onchange="toggleMotivo()">
                    ${selectOptions}
                </select>
            `;

            // Insertar el select en el DOM
            $('#campo_motivo').html(select);

            // Si ya había una opción seleccionada antes de actualizar la lista, restaurarla
            let seleccionAnterior = $('#selectMotivo').data('selected');
            if (seleccionAnterior) {
                $('#selectMotivo').val(seleccionAnterior);
            }
        },
        error: function () {
            console.error("Error al obtener la lista de tipos de etapas.");
        }
    });
}
//  DOCUMENTOS ADJUNTOS

function sec_comprobante_tipos_documento_listar() {
    let container = $(".campos-adjuntar-archivos");

    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: { accion: "comp_tipo_documento_listar" },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(container).empty();

            $(respuesta.result).each(function(i, e) {
                let campo = '<div class="form-group row" style="margin-bottom: 10px;">';
                campo += '<label class="col-lg-6 col-md-6 col-sm-6 col-xs-12">' + e.nombre + ' (' + e.extension.toUpperCase() + '): ';

                // Agregar span de campo obligatorio si es necesario
                if (e.obligatorio == 1) {
                    campo += '<span class="campo-obligatorio">(*)</span>';
                }

                campo += '</label>';
                campo += '<div class="input-comp-doc-adjunto col-lg-6 col-md-6 col-sm-6 col-xs-12">';
                campo += '<input type="file" class="campo-editable" name="comp_documento_' + e.id + '" id="comp_documento_' + e.id + '">';
                campo += '<div class="button-container" style="text-align: center;">'; // Nuevo div para centrar botones
                campo += '<button class="btn btn-info" id="sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_servicio_publico"><span class="glyphicon glyphicon-cloud-upload"></span></button>';
                campo += ' <button class="btn btn-success" id="sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_servicio_publico"><span class="glyphicon glyphicon-download-alt"></span></button>';
                campo += ' <button class="btn btn-warning" id="sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_servicio_publico"><span class="glyphicon glyphicon-eye-open"></span></button>';
                campo += '</div>'; // Cierre del div de centrado
                campo += '<span class="file-info" id="sec_locales_tab_servicio_publico_form_modal_btn_buscar_file_txt_mensaje"></span>';
                campo += '</div></div>';

                $(container).append(campo);
            });
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de comprobantes.");
        }
    });
}

// OBTENER NOMBRE DEL PROVEEDOR POR RUC

function sec_comprobante_proveedor_obtener_nombre()
    {
        var proveedor_id = $('#form_modal_sec_comp_param_proveedor_id').val();
        var data = {
            'accion': 'comp_proveedor_obtener_nombre',
            'proveedor_id': proveedor_id
        };
    
        $.ajax({
                url: "sys/get_comprobante_pago.php",
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
                        $('#form_modal_sec_comp_param_proveedor_nombre').val(respuesta.descripcion);
                    }
    
                    if (parseInt(respuesta.http_code) == 200) {
                        $('#form_modal_sec_comp_param_proveedor_nombre').val(respuesta.descripcion);
                    }   		
                },
                error: function(){
                    alert('failure');
                  }
            });
        }

function sec_comprobante_empresa_at_obtener_nombre()
    {
        var razon_social_id = $('#form_modal_sec_comp_param_razon_social_id').val();
        var data = {
            'accion': 'comp_empresa_at_obtener_nombre',
            'razon_social_id': razon_social_id
        };
    
        $.ajax({
                url: "sys/get_comprobante_pago.php",
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
                        $('#form_modal_sec_comp_param_razon_social_nombre').val(respuesta.descripcion);
                    }
    
                    if (parseInt(respuesta.http_code) == 200) {
                        $('#form_modal_sec_comp_param_razon_social_nombre').val(respuesta.descripcion);
                    }   		
                },
                error: function(){
                    alert('failure');
                  }
            });
        }

function sec_comprobante_ceco_obtener_descripcion()
    {
        var ceco_id = $('#form_modal_sec_comp_op_param_ceco_id').val();
        var data = {
            'accion': 'comp_ceco_obtener_descripcion',
            'cc_id': ceco_id
        };
    
        $.ajax({
                url: "sys/get_comprobante_pago.php",
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
                        $('#form_modal_sec_comp_op_param_ceco_descripcion').val(respuesta.descripcion);
                    }
    
                    if (parseInt(respuesta.http_code) == 200) {
                        $('#form_modal_sec_comp_op_param_ceco_descripcion').val(respuesta.descripcion);
                    }   		
                },
                error: function(){
                    alert('failure');
                  }
            });
        }
//////////   FUNCIONES PARA FILTRO DEz BUSQUEDA


function buscarComprobantePorParametros() {
	$("#sec_comprobante_pago_div_listar").hide();
    sec_comprobante_listar_datatable();
}

function sec_comprobante_listar_datatable() {

    if(sec_id == "comprobante" && sub_sec_id == "pago"){

	$("#sec_comprobante_pago_div_listar").show();
	var proveedor_id = $("#search_comp_proveedor_id").val();
	var razon_social_id = $("#search_comp_razon_social_id").val();
	var etapa_id = $("#search_comp_etapa_id").val();
	var estado_id = $("#search_comp_estado_id").val();

	var fecha_inicio_registro = $("#search_comp_fecha_inicio_registro").val();
	var fecha_fin_registro = $("#search_comp_fecha_fin_registro").val();
	var fecha_inicio_emision = $("#search_comp_fecha_inicio_emision").val();
	var fecha_fin_emision = $("#search_comp_fecha_fin_emision").val();


	
	if (fecha_inicio_registro.length > 0 && fecha_fin_registro.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_registro);
		var fecha_fin_date = new Date(fecha_fin_registro);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}
	if (fecha_inicio_emision.length > 0 && fecha_fin_emision.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_emision);
		var fecha_fin_date = new Date(fecha_fin_emision);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta', 5);
			return false;
		}
	}

    var data = {
        "accion": "comprobante_pago_listar",
        proveedor_id: proveedor_id,
		razon_social_id: razon_social_id,
		etapa_id: etapa_id,
		estado_id: estado_id,
		fecha_inicio_registro: fecha_inicio_registro,
		fecha_fin_registro: fecha_fin_registro,
		fecha_inicio_emision: fecha_inicio_emision,
		fecha_fin_emision: fecha_fin_emision
    }

    tabla = $("#sec_comprobante_pago_div_listar_datatable").dataTable(
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
                }
            },
            "aProcessing" : true,
            "aServerSide" : true,

            "ajax" :
            {
                url : "/sys/get_comprobante_pago.php",
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
                    targets: [0, 1, 2, 3, 4, 5, 6,7,8,9]
                },
                {
                    render: $.fn.dataTable.render.number(',', '.', 2),
                    targets: 7
                }
            ],
            "bDestroy" : true,
            "order": [[0, 'desc']],
            aLengthMenu:[10, 20, 30, 40, 50, 100]
        }
    ).DataTable();
    }
}

//  INICIO PAGAR COMPROBANTE

function sec_comp_form_pagar_btn_guardar(){
	var comprobante_id = $('#form_modal_sec_comp_pagar_id').val();
    var pagado = $('#form_modal_sec_comp_pagado').val();
	var param_cd_monto = $('#form_modal_sec_comp_pagar_cd_param_monto').val();
    param_cd_monto = param_cd_monto.replace(/,/g, '');

    var param_cd_moneda_id= $('#form_modal_sec_comp_pagar_cd_param_moneda_id').val();
	var param_cd_archivo = document.getElementById("form_comp_pagar_cd_param_archivo");

    var param_cpp_monto = $('#form_modal_sec_comp_pagar_cpp_param_monto').val();
    param_cpp_monto = param_cpp_monto.replace(/,/g, '');
    var param_cpp_moneda_id= $('#form_modal_sec_comp_pagar_cpp_param_moneda_id').val();
	var param_cpp_archivo = document.getElementById("form_comp_pagar_cpp_param_archivo");

	if (param_cd_monto.length == 0) {
		alertify.error('Ingrese el monto de la Constancia de Detracción', 5);
		$("#form_modal_sec_comp_pagar_cd_param_monto").focus();
		return false;
	}
    
	if (param_cpp_monto.length == 0) {
		alertify.error('Ingrese el monto de la Constancia de Pago al Proveedor', 5);
		$("#form_modal_sec_comp_pagar_cpp_param_monto").focus();
		return false;
	}
    

    if (parseInt(param_cd_moneda_id) == '') {
		alertify.error('Seleccione la moneda', 5);
		$("#form_modal_sec_comp_pagar_cd_param_moneda_id").focus();
		$('#form_modal_sec_comp_pagar_cd_param_moneda_id').select2('open');
		return false;
	}

    
    if (parseInt(param_cpp_moneda_id) == '') {
		alertify.error('Seleccione la moneda', 5);
		$("#form_modal_sec_comp_pagar_cpp_param_moneda_id").focus();
		$('#form_modal_sec_comp_pagar_cpp_param_moneda_id').select2('open');
		return false;
	}

    ////DATOS DE FORMA DE PAGO  //////////////////////////////////////////////////////////////////

    var param_fp_banco_id = $("#form_modal_sec_comp_pagar_fp_param_banco_id").val();
    var param_fp_moneda_id = $("#form_modal_sec_comp_pagar_fp_param_moneda_id").val();
    var param_fp_num_cuenta_corriente = $("#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente").val();
    var param_fp_num_cuenta_interbancaria = $("#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente_interbancaria").val();

    if(param_fp_banco_id == 0){
        alertify.error('Seleccione el banco de la forma de pago',5);
        $("#form_modal_sec_comp_pagar_fp_param_banco_id").focus();
        return false;
        }

    if(param_fp_moneda_id == 0){
        alertify.error('Seleccione la moneda de la forma de pago',5);
        $("#form_modal_sec_comp_pagar_fp_param_moneda_id").focus();
        return false;
        }

    if (param_fp_num_cuenta_corriente.length == 0) {
        alertify.error('Ingrese el número de Cuenta Corriente', 5);
        $("#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente").focus();
        return false;
    }else if(param_fp_banco_id == 12 && (param_fp_num_cuenta_corriente.length < 18 || param_fp_num_cuenta_corriente.length > 18)){
        alertify.error('Ingrese 18 digitos para cuentas bancarias del banco BBVA', 5);
        $("#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente").focus();
        return false;
        }

    if (param_fp_num_cuenta_interbancaria.length == 0) {
        alertify.error('Ingrese el número de Cuenta Interbancaria CCI', 5);
        $("#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente_interbancaria").focus();
        return false;
    }else if(param_fp_num_cuenta_interbancaria.length < 20){
        alertify.error('Ingrese 20 digitos para Cuentas Interbancarias CCI', 5);
        $("#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente_interbancaria").focus();
        return false;
        }

        //// HISTORICO DE CAMBIOS - DATOS DE FORMA DE PAGO  //////////////////////////////////////////////////////////////////
        var fp_banco_id = $('#form_modal_sec_comp_pagar_fp_param_banco_id option:selected').text();
        var fp_moneda_id = $('#form_modal_sec_comp_pagar_fp_param_moneda_id option:selected').text();
        var fp_num_cuenta_corriente = $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente').val().trim();
        var fp_num_cuenta_interbancaria = $('#form_modal_sec_comp_pagar_fp_param_num_cuenta_corriente_interbancaria').val().trim();

        var cd_pd_monto = $('#form_modal_sec_comp_pagar_cd_param_monto').val().trim();
        cd_pd_monto = cd_pd_monto.replace(/,/g, '');
        var cd_pd_moneda_id = $('#form_modal_sec_comp_pagar_cd_param_moneda_id option:selected').text();
        var cd_pago_detraccion = $("#sec_comp_form_pagar_cd_btn_txt_mensaje").html();

        var cd_pp_monto = $('#form_modal_sec_comp_pagar_cpp_param_monto').val().trim();
        cd_pp_monto = cd_pp_monto.replace(/,/g, '');
        var cd_pp_moneda_id = $('#form_modal_sec_comp_pagar_cpp_param_moneda_id option:selected').text();
        var cd_pago_proveedor = $("#sec_comp_form_pagar_cpp_btn_txt_mensaje").html();

        var regexPDF = /\.pdf$/i;

        if (cd_pago_detraccion.length == 0) {
            alertify.error('Ingrese la Constancia de Detracción', 5);
            //$("#form_comp_da_param_ac_archivo").focus();
            return false;
        }else{
            if (!regexPDF.test(cd_pago_detraccion)) {
    
                alertify.error('El archivo de la Constancia de Detracción debe estar en formato PDF', 5);
                //$("#form_comp_da_param_ac_archivo").focus();
                return false;
            }
        }
    
        if (cd_pago_proveedor.length == 0) {
            alertify.error('Ingrese la Constancia de Pago al Proveedor', 5);
            //$("#form_comp_da_param_cpdf_archivo").focus();
            return false;
        }else{
            if (!regexPDF.test(cd_pago_proveedor)) {
    
                alertify.error('El archivo de la Constancia de Detracción debe estar en formato PDF', 5);
                //$("#form_comp_da_param_ac_archivo").focus();
                return false;
            }
        }

    if(pagado == 0)
        {
            aviso = "¿Está seguro de pagar el comprobante?";
            titulo = "Pagar";
        }
    else
        {
            aviso = "¿Está seguro de editar el Pago?";
            titulo = "Editar";
        }
    swal({
        title: titulo,
        text: aviso,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#529D73",
        confirmButtonText: "SI",
        cancelButtonText: "NO",
        closeOnConfirm: false,
        closeOnCancel: false
    },
    function (isConfirm) {
        if(isConfirm){
            var dataForm = new FormData($("#form_pagarComprobante")[0]);
            dataForm.append("accion", "comp_pagar");
            dataForm.append("comprobante_id", comprobante_id);
            dataForm.append("param_cd_monto", param_cd_monto);
            dataForm.append("param_cd_moneda_id", param_cd_moneda_id);
            dataForm.append("param_cpp_monto", param_cpp_monto);
            dataForm.append("param_cpp_moneda_id", param_cpp_moneda_id);

            dataForm.append("param_fp_banco_id", param_fp_banco_id);
            dataForm.append("param_fp_moneda_id", param_fp_moneda_id);
            dataForm.append("param_fp_num_cuenta_corriente", param_fp_num_cuenta_corriente);
            dataForm.append("param_fp_num_cuenta_interbancaria", param_fp_num_cuenta_interbancaria);

            dataForm.append("fp_banco_id", fp_banco_id);
            dataForm.append("fp_moneda_id", fp_moneda_id);
            dataForm.append("fp_num_cuenta_corriente", fp_num_cuenta_corriente);
            dataForm.append("fp_num_cuenta_interbancaria", fp_num_cuenta_interbancaria);
            dataForm.append("cd_pd_monto", cd_pd_monto);
            dataForm.append("cd_pd_moneda_id", cd_pd_moneda_id);
            dataForm.append("cd_pago_detraccion", cd_pago_detraccion);
            dataForm.append("cd_pp_monto", cd_pp_monto);
            dataForm.append("cd_pp_moneda_id", cd_pp_moneda_id);
            dataForm.append("cd_pago_proveedor", cd_pago_proveedor);

            dataForm.append("pagado", pagado);

            auditoria_send({
                "proceso": "comp_pagar",
                "data": dataForm
            });
            $.ajax({
                url: "sys/set_comprobante_pago.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function(xhr) {
                    loading(true);
                },
                complete: function () {
                    loading(false);
                },
                success: function(resp) {
                    var respuesta = JSON.parse(resp);
                    auditoria_send({
                        "proceso": "comprobante_pagar",
                        "data": respuesta
                    });
                    if (parseInt(respuesta.http_code) == 200) {
                        swal({
                            title: "Operación Exitosa",
                            text: "Se guardó el pago exitosamente.",
                            html: true,
                            type: "success",
                            timer: 3000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                        setTimeout(function() {
                            $("#modalPagarComprobante").modal("hide");
                            sec_comprobante_listar_datatable();
                            $('#form_pagarComprobante')[0].reset();
                            return false;
                        }, 3000);
                    } else {
                        if (parseInt(respuesta.http_code) == 400) {
                            swal({
                                    title: "Error al guardar el pago",
                                    text: respuesta.error,
                                    html: true,
                                    type: "warning",
                                    closeOnConfirm: false,
                                    showCancelButton: false
                                });
                        }
                    }
                }
            });
        }else{
            alertify.error('No se guardaron los cambios',5);
            //$('#form_pagarComprobante')[0].reset();
            sec_comprobante_listar_datatable();
            return false;
    }
    });
}

function sec_comprobante_pagar_cd_moneda_listar(){
    let select = $("[name='form_modal_sec_comp_pagar_cd_param_moneda_id']");
    let valorSeleccionado = $("#form_modal_sec_comp_pagar_cd_param_moneda_id").val();
    
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_moneda_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccione</option>");
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

function sec_comprobante_pagar_cpp_moneda_listar(){
    let select = $("[name='form_modal_sec_comp_pagar_cpp_param_moneda_id']");
    let valorSeleccionado = $("#form_modal_sec_comp_pagar_cpp_param_moneda_id").val();
    
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_moneda_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccione</option>");
                    $(select).append(opcionDefault);
                }
        
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function sec_comp_form_pagar_cd_btn_subir(object){

    $(document).on('click', '#sec_comp_form_pagar_cd_btn_subir', function(event) {

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
            $("#form_comp_pagar_cd_param_archivo").val("");
        }

        $("#sec_comp_form_pagar_cd_btn_txt_mensaje").html(truncated);

    });
}

function sec_comp_form_pagar_cpp_btn_subir(object){

    $(document).on('click', '#sec_comp_form_pagar_cpp_btn_subir', function(event) {

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
            $("#form_comp_pagar_cpp_param_archivo").val("");
        }

        $("#sec_comp_form_pagar_cpp_btn_txt_mensaje").html(truncated);

    });
}

function sec_comprobante_exportar_zip(comprobante_id) {

    var data = {
        "accion": "comp_exportar_zip",
        "comprobante_id": comprobante_id
    };

    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            let respuesta = JSON.parse(resp);
                if (parseInt(respuesta.http_code) == 400) {
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
            
                if (parseInt(respuesta.http_code) == 200) {
                    window.open(respuesta.ruta_archivo);
                    loading(false);
                }      
        },
        error: function (resp, status) {
        }
    });
}

function sec_comprobante_da_descargar(tipo_documento_id) {
    var comprobante_id = $('#form_modal_sec_comp_param_id').val();

    var data = {
        "accion": "comp_da_descargar",
        "comprobante_id": comprobante_id,
        "tipo_documento_id":tipo_documento_id
    };

    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {

            let obj = JSON.parse(resp);
            var link = document.createElement('a');
            link.href = obj.ruta_archivo;
            link.download = obj.nombre;

            document.body.appendChild(link);
            link.click();

            document.body.removeChild(link);
        },
        error: function (resp, status) {
        }
    });
}

function sec_comprobante_da_ver(tipo_documento_id) {
    $("#modal_title_comprobante_ver").text("VER COMPROBANTE");
    
    var comprobante_id = $('#form_modal_sec_comp_param_id').val();

    var data = {
        "accion": "comp_da_descargar",
        "comprobante_id": comprobante_id,
        "tipo_documento_id":tipo_documento_id
    };

    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {

            let obj = JSON.parse(resp);
            $("#modalVerComprobante").modal("show");
            $('#sec_comp_mant_archivo_div').html('');
            if (obj.extension == "pdf") {
                    $('#sec_comp_mant_archivo_div').append(
                        '<iframe src="' + obj.ruta_archivo + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="600"></iframe>'
                    );      
            }
        },
        error: function (resp, status) {
        }
    });
}
function sec_comp_form_da_btn_ac_subir(object){

    $(document).on('click', '#sec_comp_form_da_btn_ac_subir', function(event) {

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
            $("#form_comp_da_param_ac_archivo").val("");
        }

        $("#sec_comp_form_da_ac_txt_mensaje").html(truncated);

    });
}

function sec_comp_form_da_btn_oc_subir(object){

    $(document).on('click', '#sec_comp_form_da_btn_oc_subir', function(event) {

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
            $("#form_comp_da_param_oc_archivo").val("");
        }

        $("#sec_comp_form_da_oc_txt_mensaje").html(truncated);

    });
}
function sec_comp_form_da_btn_cpdf_subir(object){

    $(document).on('click', '#sec_comp_form_da_btn_cpdf_subir', function(event) {

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
            $("#form_comp_da_param_cpdf_archivo").val("");
        }

        $("#sec_comp_form_da_cpdf_txt_mensaje").html(truncated);

    });
}

function sec_comp_form_da_btn_cxml_subir(object){

    $(document).on('click', '#sec_comp_form_da_btn_cxml_subir', function(event) {

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
            $("#form_comp_da_param_cxml_archivo").val("");
        }

        $("#sec_comp_form_da_cxml_txt_mensaje").html(truncated);

    });


}

function sec_comp_form_da_btn_cs_subir(object){

    $(document).on('click', '#sec_comp_form_da_btn_cs_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            truncated = name;
        }
        else
        {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
            $("#form_comp_da_param_cs_archivo").val("");
        }

        $("#sec_comp_form_da_cs_txt_mensaje").html(truncated);

    });
}

function sec_comp_form_da_btn_gr_subir(object){

    $(document).on('click', '#sec_comp_form_da_btn_gr_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            truncated = name;
        }
        else
        {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLocalesServicioPublico'));
            $("#form_comp_da_param_gr_archivo").val("");
        }

        $("#sec_comp_form_da_gr_txt_mensaje").html(truncated);

    });
}

