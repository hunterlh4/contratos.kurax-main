function sec_conciliacion_mantenimiento_proveedor() {

    // INICIO FORMATO COMBO CON BUSQUEDA
	$(".conci_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

    sec_conci_mant_proveedor_events()



    //  INICIO SELECTS
    conci_mant_proveedor_importacion_tipo();
    conci_mant_proveedor_archivo_combinado_formato_tipo();
    conci_mant_proveedor_archivo_venta_formato_tipo();
    conci_mant_proveedor_archivo_liquidacion_formato_tipo();
    conci_mant_proveedor_liquidacion_formula_tipo();
    conci_mant_proveedor_liquidacion_calculo_tipo();
    conci_mant_proveedor_banco_listar();
    conci_mant_proveedor_moneda_listar();

    //conci_mant_proveedor_columna_combinado_tipo();
    conci_mant_proveedor_columna_venta_tipo();
    conci_mant_proveedor_columna_liquidacion_tipo();
    conci_mant_proveedor_liquidacion_moneda_listar();

    // FORMATO
    conci_mant_proveedor_conciliacion_calimaco_monto_separador();
    conci_mant_proveedor_conciliacion_calimaco_comision_total_separador();
    conci_mant_proveedor_conciliacion_calimaco_id_separador();
    conci_mant_proveedor_conciliacion_calimaco_id();
    conci_mant_proveedor_conciliacion_calimaco_venta_id();
    conci_mant_proveedor_conciliacion_calimaco_liquidacion_id();
    conci_mant_proveedor_conciliacion_calimaco_id_tipo();
    conci_mant_proveedor_conciliacion_calimaco_venta_id_tipo();
    conci_mant_proveedor_conciliacion_calimaco_liquidacion_id_tipo();
    conci_mant_proveedor_conciliacion_calimaco_venta_monto_separador();
    conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador();
    conci_mant_proveedor_conciliacion_calimaco_venta_id_separador();
    conci_mant_proveedor_conciliacion_calimaco_liquidacion_id_separador();
    //  FORMULAS

    conci_mant_proveedor_formula_fija_operador_listar();
    conci_mant_proveedor_formula_mixta_operador_listar();

    conci_mant_proveedor_formula_fija_opcion_listar();
    conci_mant_proveedor_formula_mixta_opcion_listar();

    conci_mant_proveedor_columna_venta_conciliacion();
    conci_mant_proveedor_columna_liquidacion_conciliacion();
    //conci_mant_proveedor_columna_combinado_conciliacion();
    
    conci_mant_proveedor_archivo_liquidacion_separador_tipo();
    conci_mant_proveedor_archivo_venta_separador_tipo();
    conci_mant_proveedor_archivo_combinado_separador_tipo();

    //  FIN SELECTS

    //  INICIO Upload archivos
    conci_mant_proveedor_archivo_combinado_btn_subir($('#form_conci_mant_proveedor_param_formato_archivo_combinado'));
    conci_mant_proveedor_archivo_venta_btn_subir($('#form_conci_mant_proveedor_param_formato_archivo_venta'));
    conci_mant_proveedor_archivo_liquidacion_btn_subir($('#form_conci_mant_proveedor_param_formato_archivo_liquidacion'));

    //

	sec_conci_mant_proveedor_listar();
    //sec_conciliacion_mantenimiento_cuenta_contable_listar_param_empresa();
    //sec_conciliacion_mantenimiento_cuenta_contable_listar_empresa();
    $('#btn_conciliacion_mant_cuenta_limpiar_filtros_de_busqueda').click(function() {
		$('#search_conciliacion_mant_cuenta_contable_param_empresa').select2().val(0).trigger("change");
		$('#search_conciliacion_mant_cuenta_contable_param_estado').select2().val('').trigger("change");
        //sec_conciliacion_mantenimiento_cuenta_contable_listar();

	});
}

function conci_mant_proveedor_archivo_combinado_btn_subir(object){

    $(document).on('click', '#conci_mant_proveedor_archivo_combinado_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        if($(this)[0].files.length <= 1)
        {
            event.preventDefault();
            
            //  Verificación de campos

            var nombreCorto = $('#form_modal_conci_mant_proveedor_param_nombre_corto').val();
            var tipoExtension = $('#form_modal_conci_mant_proveedor_archivo_combinado_extension_id').val();
            var lineaInicio = $('#form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio').val();
            var columnaInicio = $('#form_modal_conci_mant_proveedor_archivo_combinado_columna_inicio').val();
            var separador = $('#form_modal_conci_mant_proveedor_archivo_combinado_separador_id').val();
            var selectValueExtension = $('#form_modal_conci_mant_proveedor_archivo_combinado_extension_id option:selected').text();

            if(nombreCorto.length == 0)
            {
                    alertify.error('Ingrese el nombre corto del proveedor',5);
                    $("#form_modal_conci_mant_proveedor_param_nombre_corto").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                    return false;
            }

            if(tipoExtension == "0"){
                alertify.error('Seleccionar el tipo de extensión del archivo',5);
                $("#form_modal_conci_mant_proveedor_archivo_combinado_extension_id").focus();
                $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                return false;
            }

            if(lineaInicio.length == 0)
                {
                    alertify.error('Ingrese el número de la linea de inicio',5);
                    $("#form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                    return false;
                }

            if(columnaInicio.length == 0)
                {
                    alertify.error('Ingrese el número de la columna de inicio',5);
                    $("#form_modal_conci_mant_proveedor_archivo_combinado_columna_inicio").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                    return false;
                }

            if (selectValueExtension == "csv") { //    CSV
                if(separador == "0"){
                    alertify.error('Seleccionar el separador csv del archivo combinado',5);
                    $("#form_modal_conci_mant_proveedor_archivo_combinado_separador_id").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                    return false;
                }
            }
            //  Verificación de la tabla del archivo combinado

            var filasColumnaCombinado = document.querySelectorAll('.detalle_columnas_combinado');
            var nombresCombinado = [];
            var ColumnaCombinado = [];

            for (var i = 0; i < filasColumnaCombinado.length; i++) {
                var fila = filasColumnaCombinado[i];

                var nombre = fila.querySelector('input[name="nombre"]').value.trim();
                var formato = fila.querySelector('select[name="conci_proveedor_formato-combinado"]').value;
                var columna = fila.querySelector('select[name="conci_proveedor_columna-combinado"]').value;
                var selectElement = fila.querySelector('select[name="conci_proveedor_columna-combinado"]');
                var nombreColumna = selectElement.options[selectElement.selectedIndex].text;

                if (nombre === '' || formato === '0') {
                    alertify.error('Por favor, llene completamente la tabla de columnas del archivo combinado',5);
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                    return false;
                }

                if (columna != '0' && ColumnaCombinado.includes(columna)) {
                    alertify.error('No deben existir 2 columnas con la misma columna calimaco: '+ nombreColumna, 5);
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                    return false;
                }else{
                    ColumnaCombinado.push(columna);
                }

            }

            var ColumnasCalimacoJSON = JSON.stringify(ColumnaCombinado);            

            var datos = [];

            var filas = document.querySelectorAll('.detalle_columnas_combinado');

            filas.forEach(function(fila) {
                var nombre = fila.querySelector('input[name="nombre"]').value;
                var formatoSelect = fila.querySelector('select[name="conci_proveedor_formato-combinado"]');           
                var formato = formatoSelect.options[formatoSelect.selectedIndex].value; // Declaración de formato
            
                var dato = {};
                    dato[nombre] = formato;
                    datos.push(dato);
            });
            var datosJSON = JSON.stringify(datos);
            
            var formato_name = $('#form_modal_conci_mant_proveedor_archivo_combinado_extension_id option:selected').text();

            var dataForm = new FormData($("#Frm_RegistroConciProveedor")[0]);
            dataForm.append("accion", "conci_mant_proveedor_test_importar");
            dataForm.append("datos", datosJSON);
            dataForm.append("archivo_name", "form_conci_mant_proveedor_param_formato_archivo_combinado");
            dataForm.append("formato_name", formato_name);
            dataForm.append("archivo_tipo", "combinado");
            dataForm.append("columnaSincronia", ColumnasCalimacoJSON);
            dataForm.append("lineaInicio", lineaInicio);
            dataForm.append("columnaInicio", columnaInicio);
            dataForm.append("separador", separador);

            $.ajax({
                url: '/sys/set_conciliacion_mantenimiento_proveedor.php',
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    var obj = JSON.parse(response);
                    loading();
                    swal({
                        title: obj.swal_title + "<br>",
                        html: true,
                        text: "<div><strong>" + obj.msg + "</strong></div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                        type: "success",
                        closeOnConfirm: true
                    }, function () {
                        //m_reload();
                        object.val('');
                        swal.close();
                    });
                },
                beforeSend: function () {
                    loading(true);
                },
                complete: function() {
                    loading(false);
                    object.val('');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    swal({
                        title: errorThrown,
                        html: true,
                        text: jqXHR.responseText,
                        type: textStatus,
                        closeOnConfirm: true
                    }, function () {
                        object.val('');
                        $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                        swal.close();
                    })
                }
            })
        }
        else
        {
            truncated = "";
            
            alertify.error('Solo puede subir un archivo',5);
            $("#form_conci_mant_proveedor_param_formato_archivo_combinado").val("");
        }

    });
}

function conci_mant_proveedor_archivo_venta_btn_subir(object){

    $(document).on('click', '#conci_mant_proveedor_archivo_venta_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        if($(this)[0].files.length <= 1)
        {
            event.preventDefault();
            
            //  Verificación de campos

            var nombreCorto = $('#form_modal_conci_mant_proveedor_param_nombre_corto').val();
            var tipoExtension = $('#form_modal_conci_mant_proveedor_archivo_venta_extension_id').val();
            var lineaInicio = $('#form_modal_conci_mant_proveedor_archivo_venta_linea_inicio').val();
            var columnaInicio = $('#form_modal_conci_mant_proveedor_archivo_venta_columna_inicio').val();
            var separador = $('#form_modal_conci_mant_proveedor_archivo_venta_separador_id').val();
            var selectValueExtension = $('#form_modal_conci_mant_proveedor_archivo_venta_extension_id option:selected').text();

            if(nombreCorto.length == 0)
            {
                    alertify.error('Ingrese el nombre corto del proveedor',5);
                    $("#form_modal_conci_mant_proveedor_param_nombre_corto").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                    return false;
            }

            if(tipoExtension == "0"){
                alertify.error('Seleccionar el tipo de extensión del archivo',5);
                $("#form_modal_conci_mant_proveedor_archivo_venta_extension_id").focus();
                $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                return false;
            }

            if(lineaInicio.length == 0)
                {
                    alertify.error('Ingrese el número de la linea de inicio del archivo de venta',5);
                    $("#form_modal_conci_mant_proveedor_archivo_venta_linea_inicio").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                    return false;
                }

            if(columnaInicio.length == 0)
                {
                    alertify.error('Ingrese el número de la columna de inicio del archivo de venta',5);
                    $("#form_modal_conci_mant_proveedor_archivo_venta_columna_inicio").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                    return false;
                }

            if (selectValueExtension == "csv") { //    CSV
                if(separador == "0"){
                    alertify.error('Seleccionar el separador csv del archivo de ventas',5);
                    $("#form_modal_conci_mant_proveedor_archivo_venta_separador_id").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                    return false;
                }
            }
            //Verificación de la tabla de venta

            var filasColumnaVenta = document.querySelectorAll('.detalle_columnas_venta');
            var nombresVenta = [];
            var ColumnaVenta = [];


            for (var i = 0; i < filasColumnaVenta.length; i++) {
                var fila = filasColumnaVenta[i];

                var nombre = fila.querySelector('input[name="nombre"]').value.trim();
                var formato = fila.querySelector('select[name="conci_proveedor_formato-venta"]').value;
                var columna = fila.querySelector('select[name="conci_proveedor_columna-venta"]').value;
                var selectElement = fila.querySelector('select[name="conci_proveedor_columna-venta"]');
                var nombreColumna = selectElement.options[selectElement.selectedIndex].text;

                if (nombre === '' || formato === '0') {
                    alertify.error('Por favor, llene completamente la tabla de columnas del archivo de ventas',5);
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                    return false;
                }

                if (columna != '0' && ColumnaVenta.includes(columna)) {
                    alertify.error('No deben existir 2 columnas con la misma columna calimaco: '+ nombreColumna, 5);
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                    return false;
                }else{
                    ColumnaVenta.push(columna);
                }
            }

            var ColumnasCalimacoJSON = JSON.stringify(ColumnaVenta);            
            var datos = [];

            var filas = document.querySelectorAll('.detalle_columnas_venta');

            filas.forEach(function(fila) {
                var nombre = fila.querySelector('input[name="nombre"]').value;
                var formatoSelect = fila.querySelector('select[name="conci_proveedor_formato-venta"]');           
                var formato = formatoSelect.options[formatoSelect.selectedIndex].value; // Declaración de formato
            
                var dato = {};
                    dato[nombre] = formato;
                    datos.push(dato);
            });
            var datosJSON = JSON.stringify(datos);

            var formato_name =  $('#form_modal_conci_mant_proveedor_archivo_venta_extension_id option:selected').text();
            var dataForm = new FormData($("#Frm_RegistroConciProveedor")[0]);
            dataForm.append("accion", "conci_mant_proveedor_test_importar");
            dataForm.append("datos", datosJSON);
            dataForm.append("archivo_name", "form_conci_mant_proveedor_param_formato_archivo_venta");
            dataForm.append("formato_name", formato_name);
            dataForm.append("archivo_tipo", "venta");
            dataForm.append("columnaSincronia", ColumnasCalimacoJSON);
            dataForm.append("lineaInicio", lineaInicio);
            dataForm.append("columnaInicio", columnaInicio);
            dataForm.append("separador", separador);

            $.ajax({
                url: '/sys/set_conciliacion_mantenimiento_proveedor.php',
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    var obj = JSON.parse(response);
                    loading();
                    swal({
                        title: obj.swal_title + "<br>",
                        html: true,
                        text: "<div><strong>" + obj.msg + "</strong></div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                        type: "success",
                        closeOnConfirm: true
                    }, function () {
                        //m_reload();
                        object.val('');
                        swal.close();
                    });
                },
                beforeSend: function () {
                    loading(true);
                },
                complete: function() {
                    loading(false);
                    object.val('');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    swal({
                        title: errorThrown,
                        html: true,
                        text: jqXHR.responseText,
                        type: textStatus,
                        closeOnConfirm: true
                    }, function () {
                        object.val('');
                        $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                        swal.close();
                    })
                }
            })
        }
        else
        {
            truncated = "";
            
            alertify.error('Solo puede subir un archivo',5);
            $("#form_conci_mant_proveedor_param_formato_archivo_venta").val("");
        }

    });
}

function conci_mant_proveedor_archivo_liquidacion_btn_subir(object){

    $(document).on('click', '#conci_mant_proveedor_archivo_liquidacion_btn_subir', function(event) {

        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {

        if($(this)[0].files.length <= 1)
        {
            event.preventDefault();
            
            //  Verificación de campos

            var nombreCorto = $('#form_modal_conci_mant_proveedor_param_nombre_corto').val();
            var tipoExtension = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id').val();
            var lineaInicio = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio').val();
            var columnaInicio = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_columna_inicio').val();
            var separador = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id').val();
            var selectValueExtension = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id option:selected').text();

            if(nombreCorto.length == 0){
                    alertify.error('Ingrese el nombre corto del proveedor',5);
                    $("#form_modal_conci_mant_proveedor_param_nombre_corto").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                    return false;
            }

            if(tipoExtension == "0"){
                alertify.error('Seleccionar el tipo de extensión del archivo',5);
                $("#form_modal_conci_mant_proveedor_archivo_venta_extension_id").focus();
                $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                return false;
            }

            if(lineaInicio.length == 0){
                alertify.error('Ingrese el número de la linea de inicio del archivo de liquidación',5);
                $("#form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio").focus();
                $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                return false;
            }

            if(columnaInicio.length == 0){
                alertify.error('Ingrese el número de la columna de inicio del archivo de liquidación',5);
                $("#form_modal_conci_mant_proveedor_archivo_liquidacion_columna_inicio").focus();
                $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                return false;
            }


            if (selectValueExtension == "csv") { //    CSV
                if(separador == "0"){
                    alertify.error('Seleccionar el separador csv del archivo de liquidación',5);
                    $("#form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id").focus();
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                    return false;
                }
            }
            //Verificación de la tabla de liquidacion

            var filasColumnaVenta = document.querySelectorAll('.detalle_columnas_liquidacion');
            var nombresLiquidacion = [];
            var ColumnaLiquidacion = [];

            for (var i = 0; i < filasColumnaVenta.length; i++) {
                var fila = filasColumnaVenta[i];

                var nombre = fila.querySelector('input[name="nombre"]').value.trim();
                var formato = fila.querySelector('select[name="conci_proveedor_formato-liquidacion"]').value;
                var columna = fila.querySelector('select[name="conci_proveedor_columna-liquidacion"]').value;

                var selectElement = fila.querySelector('select[name="conci_proveedor_columna-liquidacion"]');
                var nombreColumna = "";

                if (selectElement && selectElement.selectedIndex >= 0) {
                    nombreColumna = selectElement.options[selectElement.selectedIndex].text;
                } else {
                    console.error('No se encontró una opción seleccionada en el select de columna.');
                }

                if (nombre === '' || formato === '0') {
                    alertify.error('Por favor, llene completamente la tabla de columnas del archivo de liquidacion',5);
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                    return false;
                }

                if (columna != '0' && ColumnaLiquidacion.includes(columna)) {
                    alertify.error('No deben existir 2 columnas con la misma columna calimaco: '+ nombreColumna, 5);
                    $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                    return false;
                }else{
                    ColumnaLiquidacion.push(columna);
                }
            }

            var ColumnasCalimacoJSON = JSON.stringify(ColumnaLiquidacion);   

            var datos = [];

            var filas = document.querySelectorAll('.detalle_columnas_liquidacion');

            filas.forEach(function(fila) {
                var nombre = fila.querySelector('input[name="nombre"]').value;
                var formatoSelect = fila.querySelector('select[name="conci_proveedor_formato-liquidacion"]');           
                var formato = formatoSelect.options[formatoSelect.selectedIndex].value; // Declaración de formato
            
                var dato = {};
                dato[nombre] = formato;
                datos.push(dato);
            });
            var datosJSON = JSON.stringify(datos);
            var formato_name =  $('#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id option:selected').text();


            var dataForm = new FormData($("#Frm_RegistroConciProveedor")[0]);
            dataForm.append("accion", "conci_mant_proveedor_test_importar");
            dataForm.append("datos", datosJSON);
            dataForm.append("archivo_name", "form_conci_mant_proveedor_param_formato_archivo_liquidacion");
            dataForm.append("formato_name", formato_name);
            dataForm.append("archivo_tipo", "liquidacion");
            dataForm.append("columnaSincronia", ColumnasCalimacoJSON);
            dataForm.append("lineaInicio", lineaInicio);
            dataForm.append("columnaInicio", columnaInicio);
            dataForm.append("separador", separador);

            $.ajax({
                url: '/sys/set_conciliacion_mantenimiento_proveedor.php',
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    var obj = JSON.parse(response);
                    loading();
                    swal({
                        title: obj.swal_title + "<br>",
                        html: true,
                        text: "<div><strong>" + obj.msg + "</strong></div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                        type: "success",
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
                        closeOnConfirm: true
                    }, function () {
                        $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                        swal.close();
                    })
                }
            })
        }
        else
        {
            truncated = "";
            
            alertify.error('Solo puede subir un archivo',5);
            $("#form_conci_mant_proveedor_param_formato_archivo_liquidacion").val("");
        }

    });
}

function sec_conci_mant_proveedor_events(){

    $(document).ready(function() {

        $(document).on('click', '.conci_proveedor_add_estado_btn', function(event) {
            event.preventDefault();
            var num_ele = $(".conci_proveedor_estado_detalle_item").length;
            var cloned = $(".conci_proveedor_estado_detalle_item").last().clone();
    
            $(cloned).attr("data-detalle-estado-num", num_ele);
            $(cloned).find("input[name='nombre']").val("");
            $(cloned).find("input[name='id']").val(0);
            $(cloned).find("select[id='estado']").val(1);            
    
            $(cloned).find('.conci_proveedor_estado_rem_btn').removeClass('hidden');
    
            $(this).closest(".conci_proveedor_estado_detalle_item").after(cloned);
    
            $(".conci_proveedor_estado_rem_btn").off().on('click', function(event) {
                event.preventDefault();
                $(this).closest(".conci_proveedor_estado_detalle_item").remove();
                if ($(".conci_proveedor_estado_detalle_item").length === 1) {
                    $(".conci_proveedor_estado_detalle_item .conci_proveedor_estado_rem_btn").addClass('hidden');
                }
            });
        });
    });
    

    $(document).ready(function() {

        $(document).on('click', '.conci_proveedor_add_columna_combinado_btn', function(event) {
            event.preventDefault();
            var num_ele = $(".detalle_columnas_combinado").length;
            var cloned = $(".detalle_columnas_combinado").last().clone();
    
            $(cloned).attr("data-detalle-columna-combinado-num", num_ele);
            $(cloned).find("input[name='nombre']").val("");
            $(cloned).find("input[name='id']").val(0);
            $(cloned).find("select[id='input_text-formato-combinado']").val(1);            
            $(cloned).find("select[id='input_text-columna-combinado']").val(0);
    
            $(cloned).find('.rem_columna_combinado_btn').removeClass('hidden');
    
            $(this).closest(".detalle_columnas_combinado").after(cloned);
    
            $(".rem_columna_combinado_btn").off().on('click', function(event) {
                event.preventDefault();
                $(this).closest(".detalle_columnas_combinado").remove();
                if ($(".detalle_columnas_combinado").length === 1) {
                    $(".detalle_columnas_combinado .rem_columna_combinado_btn").addClass('hidden');
                }
            });
        });

    });

    $(document).ready(function() {

        $(document).on('click', '.conci_proveedor_add_columna_ventas_btn', function(event) {
            event.preventDefault();
            var num_ele = $(".detalle_columnas_venta").length;
            var cloned = $(".detalle_columnas_venta").last().clone();
    
            $(cloned).attr("data-detalle-columna-venta-num", num_ele);
            $(cloned).find("input[name='nombre']").val("");
            $(cloned).find("input[name='id']").val(0);
            $(cloned).find("select[id='input_text-formato-venta']").val(1);            
            $(cloned).find("select[id='input_text-columna-venta']").val(0);
    
            $(cloned).find('.rem_columna_venta_btn').removeClass('hidden');
    
            $(this).closest(".detalle_columnas_venta").after(cloned);
    
            $(".rem_columna_venta_btn").off().on('click', function(event) {
                event.preventDefault();
                $(this).closest(".detalle_columnas_venta").remove();
                if ($(".detalle_columnas_venta").length === 1) {
                    $(".detalle_columnas_venta .rem_columna_venta_btn").addClass('hidden');
                }
            });
        });
    });
        
    $(document).ready(function() {

        $(document).on('click', '.conci_proveedor_add_columna_liquidacion_btn', function(event) {
            event.preventDefault();
            var num_ele = $(".detalle_columnas_liquidacion").length;
            var cloned = $(".detalle_columnas_liquidacion").last().clone();
    
            $(cloned).attr("data-detalle-columna-liquidacion-num", num_ele);
            $(cloned).find("input[name='nombre']").val("");
            $(cloned).find("input[name='id']").val(0);
            $(cloned).find("select[id='input_text-formato-liquidacion']").val(1);            
            $(cloned).find("select[id='input_text-columna-liquidacion']").val(0);
    
            $(cloned).find('.rem_columna_liquidacion_btn').removeClass('hidden');
    
            $(this).closest(".detalle_columnas_liquidacion").after(cloned);
    
            $(".rem_columna_liquidacion_btn").off().on('click', function(event) {
                event.preventDefault();
                $(this).closest(".detalle_columnas_liquidacion").remove();
                if ($(".detalle_columnas_liquidacion").length === 1) {
                    $(".detalle_columnas_liquidacion .rem_columna_liquidacion_btn").addClass('hidden');
                }
            });
        });
    });        

    $(document).ready(function() {
        $(document).on('click', '.conci_proveedor_add_formula_mixta_btn', function(event) {

            event.preventDefault();
            var num_ele = $(".detalle_formula_mixta").length;
            var cloned = $(".detalle_formula_mixta").last().clone();
    
            var prev_desde = parseInt($(cloned).find("input[name=desde_mixta]").val());
            var prev_hasta = parseInt($(cloned).find("input[name=hasta_mixta]").val());
            $(cloned).find("select[id='input_text-operador-mixta']").val("0");
            $(cloned).find("input[name='id']").val(0);
            $(cloned).find("input[name=desde_mixta]").val(prev_desde + 20);
            $(cloned).find("input[name=hasta_mixta]").val(prev_hasta + 20);
    
            $(cloned).find('.rem_formula_mixta_btn').removeClass('hidden');
    
            $(this).closest(".detalle_formula_mixta").after(cloned);
    
            $(".rem_formula_mixta_btn").off().on('click', function(event) {
                event.preventDefault();
                $(this).closest(".detalle_formula_mixta").remove();
                if ($(".detalle_formula_mixta").length === 1) {
                    $(".detalle_formula_mixta .rem_formula_mixta_btn").addClass('hidden');
                }
            });
        });

        
        $(document).on('click', '.conci_proveedor_add_formula_escalonada_btn', function(event) {
            event.preventDefault();
            var num_ele = $(".detalle_formula_escalonada").length;
            var cloned = $(".detalle_formula_escalonada").last().clone();

            var prev_desde = parseInt($(cloned).find("input[name=desde_escalonada]").val());
            var prev_hasta = parseInt($(cloned).find("input[name=hasta_escalonada]").val());
            var prev_monto = parseInt($(cloned).find("input[name=constante_escalonada]").val());

            $(cloned).find("input[name=id]").val(0);
            $(cloned).find("input[name=desde_escalonada]").val(prev_desde + 20);
            $(cloned).find("input[name=hasta_escalonada]").val(prev_hasta + 20);
            $(cloned).find("input[name=porcentaje_escalonada]").val(0);
            $(cloned).find("input[name=constante_escalonada]").val(0);
        
            $(cloned).find('.rem_formula_escalonada_btn').removeClass('hidden');
    
            $(this).closest(".detalle_formula_escalonada").after(cloned);
    
            $(".rem_formula_escalonada_btn").off().on('click', function(event) {
                event.preventDefault();
                $(this).closest(".detalle_formula_escalonada").remove();
                if ($(".detalle_formula_escalonada").length === 1) {
                    $(".detalle_formula_escalonada .rem_formula_escalonada_btn").addClass('hidden');
                }
            });
    
        });

    $(document).on('click', '.conci_proveedor_add_formula_fija_btn', function(event) {

        event.preventDefault();
        var num_ele = $(".detalle_formula_fija").length;
        var cloned = $(".detalle_formula_fija").last().clone();

        $(cloned).find("select[id='input_text-operador-fija']").val("0");
        $(cloned).find("input[name='id']").val(0);

        $(cloned).find('.rem_formula_fija_btn').removeClass('hidden');

        $(this).closest(".detalle_formula_fija").after(cloned);

        $(".rem_formula_fija_btn").off().on('click', function(event) {
            event.preventDefault();
            $(this).closest(".detalle_formula_fija").remove();
            if ($(".detalle_formula_fija").length === 1) {
                $(".detalle_formula_fija .rem_formula_fija_btn").addClass('hidden');
            }
        });

    });
    });

    $(document).ready(function() {

        $(document).on('click', '.conci_proveedor_add_cuenta_bancaria_btn', function(event) {
            event.preventDefault();
            event.preventDefault();
            var num_ele = $(".detalle_cuenta_bancaria").length;
            var cloned = $(".detalle_cuenta_bancaria").last().clone();
    
            $(cloned).attr("data-detalle-cuenta-num", num_ele);
            $(cloned).find("input[name='cuentainterbancaria']").val("");
            $(cloned).find("input[name='cuentacorriente']").val("");
            $(cloned).find("input[name='id']").val(0);
            $(cloned).find("select[id='input_text-banco']").val(1);            
            $(cloned).find("select[id='input_text-moneda']").val(1);
    
            $(cloned).find('.rem_cuenta_bancaria_btn').removeClass('hidden');
    
            $(this).closest(".detalle_cuenta_bancaria").after(cloned);
    
            $(".rem_cuenta_bancaria_btn").off().on('click', function(event) {
                event.preventDefault();
                $(this).closest(".detalle_cuenta_bancaria").remove();
                if ($(".detalle_cuenta_bancaria").length === 1) {
                    $(".detalle_cuenta_bancaria .rem_cuenta_bancaria_btn").addClass('hidden');
                }
            });
        });
    });
    }
    
function sec_conci_mant_proveedor_listar()
{
    if(sec_id == "conciliacion" && sub_sec_id == "mantenimiento"){

        //$("#sec_comprobante_pago_div_listar").show();

        //var empresa_id = $("#search_conciliacion_mant_cuenta_contable_param_empresa").val();
        var estado_id = $("#search_conci_mant_proveedor_param_param_estado").val();
    

        var data = {
            "accion": "conci_mant_proveedor_listar",
            //empresa_id: empresa_id,
            estado_id: estado_id
        }

        tabla = $("#table_conci_mant_proveedor").dataTable(
            {
                language:{
                    "decimal":        "",
                    "emptyTable":     "No existen registros",
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
                },
                "aProcessing" : true,
                "aServerSide" : true,

                "ajax" :
                {
                    url : "/sys/get_conciliacion_mantenimiento.php",
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
						targets: [0, 1, 2, 3, 4, 5]
					}
				],
                "bDestroy" : true,
                "order": [[0, 'desc']],
                aLengthMenu:[10, 20, 30, 40, 50, 100]
            }
        ).DataTable();
    }
    }

function sec_conciliacion_mantenimiento_cuenta_contable_listar_empresa(){
        let select = $("[name='search_conciliacion_mant_cuenta_contable_param_empresa']");
        
        $.ajax({
            url: "/sys/get_mepa_mantenimiento_cuenta_contable.php",
            type: "POST",
            data: {
                accion: "mepa_mantenimiento_cuenta_contable_listar_empresa"
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
                console.error("Error al obtener la lista de empresas.");
                }
            });
    }

function sec_conci_mant_proveedor_eliminar(proveedor_id){

    swal({
            title: '¿Está seguro de eliminar el proveedor?',
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
                    proveedor_id: proveedor_id,
                    accion: 'conci_mant_proveedor_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_mantenimiento_proveedor.php",
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
                            "proceso": "conci_mant_proveedor_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El proveedor se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_conci_mant_proveedor_listar();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el proveedor",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                sec_conci_mant_proveedor_listar();
                return false;
            }
        });
    }
$("#sec_conci_mantenimiento_proveedor_btn_nuevo").off("click").on("click",function(){
    conci_mant_proveedor_limpiar();
    conci_mant_proveedor_limpiar_tablas();
    
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#accordionConciProveedorCamposAuditoria").hide();
    $("#conci_mant_proveedor_modal_guardar_titulo").text("Nuevo Proveedor");
    $("#conci_mant_proveedor_modal_nuevo").modal("show");
  })
function sec_conci_mant_proveedor_ver(param_id) {
    sec_conci_mant_proveedor_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
    $("#conci_mant_proveedor_modal_guardar_titulo").text("Ver Proveedor");

}

function sec_conci_mant_proveedor_obtener(param_id) {
    conci_mant_proveedor_limpiar_tablas();
    $("#conci_mant_proveedor_modal_guardar_titulo").text("Editar Proveedor");

    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    let data = {
          id : param_id,
          accion:'conci_mant_proveedor_obtener'
      }

    $.ajax({
        url:  "/sys/get_conciliacion_mantenimiento.php",
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
                $("#accordionConciProveedorCamposAuditoria").show();
                var estados = respuesta.result.estados;

                //  0.  FUNCIONES

                    function obtenerFormatosYAsignar(selectElement, formato_id) {
                        $.ajax({
                            url: "/sys/get_conciliacion_mantenimiento.php",
                            type: "POST",
                            data: {
                                accion: "conci_mant_proveedor_columna_tipo"
                            },
                            success: function(datos) {
                                var respuesta = JSON.parse(datos);
                                $(selectElement).empty();
                                $(respuesta.result).each(function(i, e) {
                                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                                    $(selectElement).append(opcion);
                                });
                                // Selecciona la opción correcta
                                $(selectElement).val(formato_id);
                            },
                            error: function() {
                                console.error("Error al obtener la lista de formatos.");
                            }
                        });
                    }

                    function obtenerColumnasYAsignar(selectElement, columna_id) {
                        $.ajax({
                            url: "/sys/get_conciliacion_mantenimiento.php",
                            type: "POST",
                            data: {
                                accion: "conci_mant_proveedor_columna_combinado"
                            },
                            success: function(datos) {
                                var respuesta = JSON.parse(datos);
                                $(selectElement).empty();
                                $(respuesta.result).each(function(i, e) {
                                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                                    $(selectElement).append(opcion);
                                });
                                // Selecciona la opción correcta
                                $(selectElement).val(columna_id);
                            },
                            error: function() {
                                console.error("Error al obtener la lista de columnas.");
                            }
                        });
                    }

                    function obtenerbanco(selectElement, formato_id) {
                        $.ajax({
                            url: "/sys/get_conciliacion_mantenimiento.php",
                            type: "POST",
                            data: {
                                accion: "conci_mant_proveedor_banco_listar"
                            },
                            success: function(datos) {
                                var respuesta = JSON.parse(datos);
                                $(selectElement).empty();
                                $(respuesta.result).each(function(i, e) {
                                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                                    $(selectElement).append(opcion);
                                });
                                // Selecciona la opción correcta
                                $(selectElement).val(formato_id);
                            },
                            error: function() {
                                console.error("Error al obtener la lista de bancos.");
                            }
                        });
                    }

                    function obtenerMoneda(selectElement, formato_id) {
                        $.ajax({
                            url: "/sys/get_conciliacion_mantenimiento.php",
                            type: "POST",
                            data: {
                                accion: "conci_mant_proveedor_moneda_listar"
                            },
                            success: function(datos) {
                                var respuesta = JSON.parse(datos);
                                $(selectElement).empty();
                                $(respuesta.result).each(function(i, e) {
                                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                                    $(selectElement).append(opcion);
                                });
                                // Selecciona la opción correcta
                                $(selectElement).val(formato_id);
                            },
                            error: function() {
                                console.error("Error al obtener la lista de monedas.");
                            }
                        });
                    }

                    function obtenerOperador(selectElement, formato_id) {
                        $.ajax({
                            url: "/sys/get_conciliacion_mantenimiento.php",
                            type: "POST",
                            data: {
                                accion: "conci_mant_proveedor_formula_fija_operador_listar"
                            },
                            success: function(datos) {
                                var respuesta = JSON.parse(datos);
                                $(selectElement).empty();
                                $(respuesta.result).each(function(i, e) {
                                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                                    $(selectElement).append(opcion);
                                });
                                // Selecciona la opción correcta
                                $(selectElement).val(formato_id);
                            },
                            error: function() {
                                console.error("Error al obtener la lista de bancos.");
                            }
                        });
                    }

                    function obtenerOpcion(selectElement, formato_id) {
                        $.ajax({
                            url: "/sys/get_conciliacion_mantenimiento.php",
                            type: "POST",
                            data: {
                                accion: "conci_mant_proveedor_formula_fija_opcion_listar"
                            },
                            success: function(datos) {
                                var respuesta = JSON.parse(datos);
                                $(selectElement).empty();
                                $(respuesta.result).each(function(i, e) {
                                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                                    $(selectElement).append(opcion);
                                });
                                // Selecciona la opción correcta
                                $(selectElement).val(formato_id);
                            },
                            error: function() {
                                console.error("Error al obtener la lista de monedas.");
                            }
                        });
                    }

                    function obtenerColumna(selectElement, tipo_importacion, columna_id) {
                        var filas = document.querySelectorAll('.conci_proveedor_detalle_columna_' + tipo_importacion + ' tbody tr');
                        var nombres = [];
                    
                        filas.forEach(function(fila) {
                            var nombre = fila.querySelector('input[name="nombre"]').value;
                            var id = fila.querySelector('input[name="id"]').value;
                    
                            if (nombre && !nombres.includes(nombre) && id != 0) {
                                nombres.push({ nombre: nombre, id: id });
                            }
                        });
                    
                        $(selectElement).empty();
                    
                        nombres.forEach(function(item) {
                            let opcion = $("<option value='" + item.id + "'>" + item.nombre + "</option>");
                            $(selectElement).append(opcion);
                        });
                    
                        $(selectElement).val(columna_id);
                    }

                    function checkRemoveButtonVisibilityEstados() {
                        if ($(".conci_proveedor_estado_detalle_item").length > 1) {
                            $(".conci_proveedor_estado_rem_btn").removeClass('hidden');
                        } else {
                            $(".conci_proveedor_estado_rem_btn").addClass('hidden');
                        }
                    }

                    function checkRemoveButtonVisibilityCuentasBancarias() {
                        if ($(".detalle_cuenta_bancaria").length > 1) {
                            $(".rem_cuenta_bancaria_btn").removeClass('hidden');
                        } else {
                            $(".rem_cuenta_bancaria_btn").addClass('hidden');
                        }
                    }

                    function checkRemoveButtonVisibilityColumnasCombinado() {
                        if ($(".detalle_columnas_combinado").length > 1) {
                            $(".rem_columna_combinado_btn").removeClass('hidden');
                        } else {
                            $(".rem_columna_combinado_btn").addClass('hidden');
                        }
                    }

                    function checkRemoveButtonVisibilityColumnasVenta() {
                        if ($(".detalle_columnas_venta").length > 1) {
                            $(".rem_columna_venta_btn").removeClass('hidden');
                        } else {
                            $(".rem_columna_venta_btn").addClass('hidden');
                        }
                    }

                    function checkRemoveButtonVisibilityColumnasLiquidacion() {
                        if ($(".detalle_columnas_liquidacion").length > 1) {
                            $(".rem_columna_liquidacion_btn").removeClass('hidden');
                        } else {
                            $(".rem_columna_liquidacion_btn").addClass('hidden');
                        }
                    }

                    function checkRemoveButtonVisibilityFormulaMixta() {
                        if ($(".detalle_formula_mixta").length > 1) {
                            $(".rem_formula_mixta_btn").removeClass('hidden');
                        } else {
                            $(".rem_formula_mixta_btn").addClass('hidden');
                        }
                    }
                
                    function checkRemoveButtonVisibilityFormulaEscalonada() {
                        if ($(".detalle_formula_escalonada").length > 1) {
                            $(".rem_formula_escalonada_btn").removeClass('hidden');
                        } else {
                            $(".rem_formula_escalonada_btn").addClass('hidden');
                        }
                    }

                    function checkRemoveButtonVisibilityFormulaFija() {
                        if ($(".detalle_formula_fija").length > 1) {
                            $(".rem_formula_fija_btn").removeClass('hidden');
                        } else {
                            $(".rem_formula_fija_btn").addClass('hidden');
                        }
                    }
                
                    function attachRemoveButtonEvent() {

                        
                        $(document).on('click', '.conci_proveedor_estado_rem_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".conci_proveedor_estado_detalle_item").remove();
                            checkRemoveButtonVisibilityEstados();
                        });

                        $(document).on('click', '.rem_cuenta_bancaria_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_cuenta_bancaria").remove();
                            checkRemoveButtonVisibilityCuentasBancarias();
                        });

                        $(document).on('click', '.rem_columna_combinado_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_columnas_combinado").remove();
                            checkRemoveButtonVisibilityColumnasCombinado();
                        });

                        $(document).on('click', '.rem_columna_venta_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_columnas_venta").remove();
                            checkRemoveButtonVisibilityColumnasVenta();
                        });

                        $(document).on('click', '.rem_columna_liquidacion_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_columnas_liquidacion").remove();
                            checkRemoveButtonVisibilityColumnasLiquidacion();
                        });

                        $(document).on('click', '.rem_formula_mixta_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_formula_mixta").remove();
                            checkRemoveButtonVisibilityFormulaMixta();
                        });

                        $(document).on('click', '.rem_formula_escalonada_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_formula_escalonada").remove();
                            checkRemoveButtonVisibilityFormulaEscalonada();
                        });

                        $(document).on('click', '.rem_formula_fija_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_formula_fija").remove();
                            checkRemoveButtonVisibilityFormulaFija();
                        });

                        /*
                        $(document).on('click', '.rem_formula_fija_btn', function(event) {
                            event.preventDefault();
                            $(this).closest(".detalle_formula_fija").remove();
                            if ($(".detalle_formula_fija").length > 1) {
                                $(".detalle_formula_fija .rem_formula_fija_btn").addClass('hidden');
                            }else{
                                $(cloned).find('.rem_formula_fija_btn').removeClass('hidden');
                            }
                        });
                        */
                    }                    

                    attachRemoveButtonEvent();

                //    1. Datos del proveedor

                    $('#form_modal_conci_mant_proveedor_id').val(param_id);
                    $('#form_modal_conci_mant_proveedor_param_nombre').val(respuesta.result.nombre);
                    $('#form_modal_conci_mant_proveedor_param_nombre_corto').val(respuesta.result.nombre_corto);
                    $('#form_modal_conci_mant_proveedor_param_tipo_importacion_id').val(respuesta.result.metodo_importacion).trigger("change.select2");

                //      2.  Estados

                    var tabla = $('.conci_proveedor_estado_detalle_holder table');
                    tabla.find('tbody').empty();

                    estados.forEach(function(estado, index) {
                        var fila = '<tr class="form-group conci_proveedor_estado_detalle_item" data-detalle-estado-num="' + index + '" data-pro-id="' + estado.id + '">' +
                            '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + estado.id + '"></td>' +
                            '<td><input type="text" class="form-control" name="nombre" value="' + estado.nombre + '" placeholder="Nombre" maxlength="100"></td>' +
                            '<td><select class="form-control" name="estado">' +
                                '<option value="0"' + (estado.status == 0 ? ' selected' : '') + '>No válido</option>' +
                                '<option value="1"' + (estado.status == 1 ? ' selected' : '') + '>Válido</option>' +
                            '</select></td>' +
                            '<td style="text-align: center;">' +
                            '<a class="btn btn-sm btn-danger conci_proveedor_estado_rem_btn hidden" data-pro-id="' + estado.id + '"><i class="icon fa fa-trash"></i></a> ' +
                            '<a class="btn btn-sm btn-success conci_proveedor_add_estado_btn" data-pro-id="' + estado.id + '"><i class="icon fa fa-plus"></i></a>' +
                            '</td>' +
                            '</tr>';

                        tabla.find('tbody').append(fila);
                    });

                    checkRemoveButtonVisibilityEstados();
             
                //    4.    Formato calimaco

                    switch(respuesta.result.metodo_importacion){
                        case 'ColumnasArchivoCombinado':

                            var tipo_importacion = 'combinado'; // o 'combinado'

                            //  3.  COLUMNAS

                            var columnas = respuesta.resultArchivoFormato.columnas;
                            var tabla = $('.conci_proveedor_detalle_columna_combinado table');
                            tabla.find('tbody').empty(); // Vaciar la tabla antes de agregar nuevas filas

                            columnas.forEach(function(columna, index) {
                                var fila = '<tr class="form-group detalle_columnas_combinado" data-detalle-columna-combinado-num="' + index + '" data-pro-id="' + columna.id + '">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + columna.id + '" ></td>' +
                                    '<td><input type="text" class="form-control" name="nombre" value="' + columna.nombre + '" placeholder="Nombre" maxlength="100"></td>' +
                                    '<td><select class="form-control" id="input_text-formato-combinado-' + index + '" name="conci_proveedor_formato-combinado"></select></td>' +
                                    '<td><select class="form-control" id="input_text-columna-combinado-' + index + '" name="conci_proveedor_columna-combinado"></select></td>' +              
                                    '<td style="text-align: center;">' +
                                    '<a class="btn btn-sm btn-danger rem_columna_combinado_btn hidden" data-pro-id="' + columna.id + '"><i class="icon fa fa-trash"></i></a>  ' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_columna_combinado_btn" data-pro-id="' + columna.id + '"><i class="icon fa fa-plus"></i></a>' +
                                    '</td>' +
                                    '</tr>';
                                tabla.find('tbody').append(fila);
                            
                                var formatoSelect = $('#input_text-formato-combinado-' + index);
                                var columnaSelect = $('#input_text-columna-combinado-' + index);
                            
                                obtenerFormatosYAsignar(formatoSelect, columna.formato_id);
                                obtenerColumnasYAsignar(columnaSelect, columna.columna_id);
                            });

                            checkRemoveButtonVisibilityColumnasCombinado();

                            //  4.  FORMATO CALIMACO

                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_id').val(respuesta.resultColumnaConciliacion.combinado_calimaco_id).trigger("change.select2");
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo').val(respuesta.resultColumnaConciliacion.combinado_calimaco_tipo).trigger("change.select2");
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_nombre_json').val(respuesta.resultColumnaConciliacion.combinado_nombre_json);
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_prefijo').val(respuesta.resultColumnaConciliacion.combinado_prefijo);
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_sufijo').val(respuesta.resultColumnaConciliacion.combinado_sufijo);
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id').val(respuesta.resultColumnaConciliacion.combinado_separador_id).trigger("change.select2");

                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_prefijo').val(respuesta.resultColumnaConciliacion.combinado_monto_prefijo);
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_sufijo').val(respuesta.resultColumnaConciliacion.combinado_monto_sufijo);
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id').val(respuesta.resultColumnaConciliacion.combinado_monto_separador_id).trigger("change.select2");

                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_prefijo').val(respuesta.resultColumnaConciliacion.combinado_comision_prefijo);
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_sufijo').val(respuesta.resultColumnaConciliacion.combinado_comision_sufijo);
                            $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_separador_id').val(respuesta.resultColumnaConciliacion.combinado_comision_separador_id).trigger("change.select2");

                             //  5   Formato de archivos

                             $('#form_modal_conci_mant_proveedor_archivo_combinado_extension_id').val(respuesta.resultArchivoFormato.extension_id).trigger("change.select2");
                             $('#form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio').val(respuesta.resultArchivoFormato.linea_inicio);
                             $('#form_modal_conci_mant_proveedor_archivo_combinado_columna_inicio').val(respuesta.resultArchivoFormato.columna_inicio);
                             $('#form_modal_conci_mant_proveedor_archivo_combinado_separador_id').val(respuesta.resultArchivoFormato.separador_id).trigger("change.select2");

                            break;

                        case 'ColumnasArchivosIndependientes':

                            var tipo_importacion = 'venta'; // o 'combinado'

                            //  3.  COLUMNAS
                            
                                var columnasVenta = respuesta.resultColumnaVenta.columnasVenta;
                                var tablaVenta = $('.conci_proveedor_detalle_columna_venta table');
                                tablaVenta.find('tbody').empty();

                                columnasVenta.forEach(function(columna, index) {
                                    var fila = '<tr class="form-group detalle_columnas_venta" data-detalle-columna-venta-num="' + index + '" data-pro-id="' + columna.id + '">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + columna.id + '"></td>' +
                                    '<td><input type="text" class="form-control" name="nombre" value="' + columna.nombre + '" placeholder="Nombre" maxlength="100"></td>' +
                                    '<td><select class="form-control" id="input_text-formato-venta-' + index + '" name="conci_proveedor_formato-venta"></select></td>' +
                                    '<td><select class="form-control" id="input_text-columna-venta-' + index + '" name="conci_proveedor_columna-venta"></select></td>' +              
                                    '<td style="text-align: center;">' +
                                    '<a class="btn btn-sm btn-danger rem_columna_venta_btn hidden" data-pro-id="' + columna.id + '"><i class="icon fa fa-trash"></i></a>  ' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_columna_ventas_btn" data-pro-id="' + columna.id + '"><i class="icon fa fa-plus"></i></a>' +
                                    '</td>' +
                                    '</tr>';
                                    tablaVenta.find('tbody').append(fila);

                                    var formatoSelect = $('#input_text-formato-venta-' + index);
                                    var columnaSelect = $('#input_text-columna-venta-' + index);

                                    obtenerFormatosYAsignar(formatoSelect, columna.formato_id);
                                    obtenerColumnasYAsignar(columnaSelect, columna.columna_id);
            
                                });

                                checkRemoveButtonVisibilityColumnasVenta();

                                var columnasLiquidacion = respuesta.resultColumnaLiquidacion.columnasLiquidacion;
                                var tablaLiquidacion = $('.conci_proveedor_detalle_columna_liquidacion table');
                                tablaLiquidacion.find('tbody').empty();

                                columnasLiquidacion.forEach(function(columna, index) {
                                    var fila = '<tr class="form-group detalle_columnas_liquidacion" data-detalle-columna-liquidacion-num="' + index + '" data-pro-id="' + columna.id + '">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + columna.id + '"></td>' +
                                    '<td><input type="text" class="form-control" name="nombre" value="' + columna.nombre + '" placeholder="Nombre" maxlength="100"></td>' +
                                    '<td><select class="form-control" id="input_text-formato-liquidacion-' + index + '" name="conci_proveedor_formato-liquidacion"></select></td>' +
                                    '<td><select class="form-control" id="input_text-columna-liquidacion-' + index + '" name="conci_proveedor_columna-liquidacion"></select></td>' +              
                                    '<td style="text-align: center;">' +
                                    '<a class="btn btn-sm btn-danger rem_columna_liquidacion_btn hidden" data-pro-id="' + columna.id + '"><i class="icon fa fa-trash"></i></a>  ' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_columna_liquidacion_btn" data-pro-id="' + columna.id + '"><i class="icon fa fa-plus"></i></a>' +
                                    '</td>' +
                                    '</tr>';
                                    tablaLiquidacion.find('tbody').append(fila);
                                    var formatoSelect = $('#input_text-formato-liquidacion-' + index);
                                    var columnaSelect = $('#input_text-columna-liquidacion-' + index);

                                    obtenerFormatosYAsignar(formatoSelect, columna.formato_id);
                                    obtenerColumnasYAsignar(columnaSelect, columna.columna_id);
            
                                });

                                checkRemoveButtonVisibilityColumnasLiquidacion();

                            //  4.  FORMATO CALIMACO

                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_id').val(respuesta.resultColumnaVenta.calimaco_id).trigger("change.select2");
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_tipo').val(respuesta.resultColumnaVenta.calimaco_tipo).trigger("change.select2");
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_nombre_json').val(respuesta.resultColumnaVenta.nombre_json);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_prefijo').val(respuesta.resultColumnaVenta.prefijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_sufijo').val(respuesta.resultColumnaVenta.sufijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_separador_id').val(respuesta.resultColumnaVenta.separador_id).trigger("change.select2");

                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_prefijo').val(respuesta.resultColumnaVenta.monto_prefijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_sufijo').val(respuesta.resultColumnaVenta.monto_sufijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_separador_id').val(respuesta.resultColumnaVenta.monto_separador_id).trigger("change.select2");

                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_tipo').val(respuesta.resultColumnaLiquidacion.calimaco_tipo).trigger("change.select2");
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_nombre_json').val(respuesta.resultColumnaLiquidacion.nombre_json);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_prefijo').val(respuesta.resultColumnaLiquidacion.prefijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_sufijo').val(respuesta.resultColumnaLiquidacion.sufijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_separador_id').val(respuesta.resultColumnaLiquidacion.separador_id).trigger("change.select2");

                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_prefijo').val(respuesta.resultColumnaLiquidacion.comision_prefijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_sufijo').val(respuesta.resultColumnaLiquidacion.comision_sufijo);
                                $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador_id').val(respuesta.resultColumnaLiquidacion.comision_separador_id).trigger("change.select2");

                                //  5   Formato de archivos

                                $('#form_modal_conci_mant_proveedor_archivo_venta_extension_id').val(respuesta.resultArchivoFormatoVenta.extension_id).trigger("change.select2");
                                $('#form_modal_conci_mant_proveedor_archivo_venta_linea_inicio').val(respuesta.resultArchivoFormatoVenta.linea_inicio);
                                $('#form_modal_conci_mant_proveedor_archivo_venta_columna_inicio').val(respuesta.resultArchivoFormatoVenta.columna_inicio);
                                $('#form_modal_conci_mant_proveedor_archivo_venta_separador_id').val(respuesta.resultArchivoFormatoVenta.separador_id).trigger("change.select2");

                                $('#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id').val(respuesta.resultArchivoFormatoLiquidacion.extension_id).trigger("change.select2");
                                $('#form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio').val(respuesta.resultArchivoFormatoLiquidacion.linea_inicio);
                                $('#form_modal_conci_mant_proveedor_archivo_liquidacion_columna_inicio').val(respuesta.resultArchivoFormatoLiquidacion.columna_inicio);
                                $('#form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id').val(respuesta.resultArchivoFormatoLiquidacion.separador_id).trigger("change.select2");

                            break;

                        default:
                            console.log('Metodo no reconocido');
                    }

                //  6. Liquidación

                    $('#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id').val(respuesta.result.tipo_calculo_id).trigger("change.select2");
                    $('#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id').val(respuesta.result.metodo_formula).trigger("change.select2");
                    $('#form_modal_conci_mant_proveedor_liquidacion_moneda_id').val(respuesta.result.comision_moneda_id).trigger("change.select2");

                //  7.  FORMULAS

                //    4.    Formato calimaco

                    switch(respuesta.result.metodo_formula){
                        case 'FormulaFija':

                            var formulas = respuesta.formulas.formulas;
                            var tabla = $('.FormulaFija table');
                            tabla.find('tbody').empty();

                            formulas.forEach(function(formula, index) {
                                var fila = '<tr class="form-group detalle_formula_fija" data-detalle-num="' + index + '" data-pro-id="' + formula.id + '">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + formula.id + '"></td>' +
                                    '<td><select class="form-control" id="input_text-columna-fija-' + index + '" name="columna_fija"></select></td>' +
                                    '<td><select class="form-control" id="input_text-operador-fija-' + index + '" name="conci_proveedor_formula_operador_fija"></select></td>' + 
                                    '<td><select class="form-control" id="input_text-valor-fija-' + index + '" name="conci_proveedor_formula_valor_fija"></select></td>' +
                                    '<td><input type="text" class="form-control" name="porcentaje_fija" value="' + formula.comision_porcentual + '" maxlength="5"></td>' +
                                    '<td><input type="text" class="form-control" name="constante_fija" value="' + formula.comision_fija + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="igv_fija" value="' + formula.igv + '" maxlength="3"></td>' +        
                                    '<td style="text-align: center;">' +
                                    '<a class="btn btn-sm btn-danger rem_formula_fija_btn hidden" data-pro-id="' + formula.id + '"><i class="icon fa fa-trash"></i></a>  ' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_formula_fija_btn" data-pro-id="' + formula.id + '"><i class="icon fa fa-plus"></i></a>' +
                                    '</td>' +
                                    '</tr>';
                                tabla.find('tbody').append(fila);
                            
                                var columnaSelect = $('#input_text-columna-fija-' + index);
                                var operadorSelect = $('#input_text-operador-fija-' + index);
                                var valorSelect = $('#input_text-valor-fija-' + index);
                            
                                obtenerColumna(columnaSelect, tipo_importacion, formula.columna_id);
                                obtenerOperador(operadorSelect, formula.operador_id);
                                obtenerOpcion(valorSelect, formula.opcion_id);
                            });

                            checkRemoveButtonVisibilityFormulaFija();

                            break;

                            /*
                        case 'FormulaEscalonada':

                            var formulas = respuesta.formulas.formulas;
                            var tabla = $('.FormulaEscalonada table');
                            tabla.find('tbody').empty();

                            formulas.forEach(function(formula, index) {
                                var fila = '<tr class="form-group detalle_formula_escalonada" data-detalle-num="' + index + '" data-pro-id="' + formula.id + '">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + formula.id + '"></td>' +
                                    '<td><input type="text" class="form-control" name="desde_escalonada" value="' + formula.desde + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="hasta_escalonada" value="' + formula.hasta + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="porcentaje_escalonada" value="' + formula.comision_porcentual + '" maxlength="5"></td>' +
                                    '<td><input type="text" class="form-control" name="constante_escalonada" value="' + formula.comision_fija + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="igv_escalonada" value="' + formula.igv + '" maxlength="3"></td>' +        
                                    '<td style="text-align: center;">' +
                                    '<a class="btn btn-sm btn-danger rem_formula_escalonada_btn" data-pro-id="' + formula.id + '"><i class="icon fa fa-trash"></i></a>  ' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_formula_escalonada_btn" data-pro-id="' + formula.id + '"><i class="icon fa fa-plus"></i></a>' +
                                    '</td>' +
                                    '</tr>';
                                tabla.find('tbody').append(fila);
                            });
                            checkRemoveButtonVisibilityFormulaEscalonada();
                            break;
*/
                    case 'FormulaEscalonada':
                        var formulas = respuesta.formulas.formulas;
                        var tabla = $('.FormulaEscalonada table');
                        tabla.find('tbody').empty();

                        if (formulas.length === 0) {
                            var filaVacia = '<tr class="form-group detalle_formula_escalonada" data-detalle-num="0">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input class="form-control" name="id"></td>' +
                                    '<td><input type="text" class="form-control" name="desde_escalonada" id="input_text-desde-escalonada" value="0" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="hasta_escalonada" id="input_text-hasta-escalonada" value="20" oninput="his.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="porcentaje_escalonada" id="input_text-porcentaje-escalonada" value="" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="5"></td>' +
                                    '<td><input type="text" class="form-control" name="constante_escalonada" id="input_text-constante-escalonada" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="igv_escalonada" id="input_text-igv-escalonada" oninput="this.value=this.value.replace(/[^0-9]/g,\'\')" maxlength="3" value="18"></td>' +
                                    '<td><button class="btn btn-sm btn-danger rem_formula_escalonada_btn hidden"><i class="icon fa fa-trash"></i></button>' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_formula_escalonada_btn"><i class="icon fa fa-plus"></i></a></td>' +
                                    '</tr>';
                            tabla.find('tbody').append(filaVacia);
                        } else {
                            formulas.forEach(function(formula, index) {
                                var fila = '<tr class="form-group detalle_formula_escalonada" data-detalle-num="' + index + '" data-pro-id="' + formula.id + '">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + formula.id + '"></td>' +
                                    '<td><input type="text" class="form-control" name="desde_escalonada" value="' + formula.desde + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="hasta_escalonada" value="' + formula.hasta + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="porcentaje_escalonada" value="' + formula.comision_porcentual + '" maxlength="5"></td>' +
                                    '<td><input type="text" class="form-control" name="constante_escalonada" value="' + formula.comision_fija + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="igv_escalonada" value="' + formula.igv + '" maxlength="3"></td>' +        
                                    '<td style="text-align: center;">' +
                                    '<a class="btn btn-sm btn-danger rem_formula_escalonada_btn" data-pro-id="' + formula.id + '"><i class="icon fa fa-trash"></i></a>  ' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_formula_escalonada_btn" data-pro-id="' + formula.id + '"><i class="icon fa fa-plus"></i></a>' +
                                    '</td>' +
                                    '</tr>';
                                tabla.find('tbody').append(fila);
                            });
                        }

                        checkRemoveButtonVisibilityFormulaEscalonada();
                        break;

                        case 'FormulaMixta':
                            var formulas = respuesta.formulas.formulas;
                            var tabla = $('.FormulaMixta table');
                            tabla.find('tbody').empty();

                            formulas.forEach(function(formula, index) {
                                var fila = '<tr class="form-group detalle_formula_mixta" data-detalle-num="' + index + '" data-pro-id="' + formula.id + '">' +
                                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + formula.id + '"></td>' +
                                    '<td><select class="form-control" id="input_text-columna-mixta-' + index + '" name="columna_mixta"></select></td>' +
                                    '<td><select class="form-control" id="input_text-operador-mixta-' + index + '" name="conci_proveedor_formula_operador_mixta"></select></td>' + 
                                    '<td><select class="form-control" id="input_text-valor-mixta-' + index + '" name="conci_proveedor_formula_valor_mixta"></select></td>' +
                                    '<td><input type="text" class="form-control" name="desde_mixta" value="' + formula.desde + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="hasta_mixta" value="' + formula.hasta + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="porcentaje_mixta" value="' + formula.comision_porcentual + '" maxlength="5"></td>' +
                                    '<td><input type="text" class="form-control" name="constante_mixta" value="' + formula.comision_fija + '" maxlength="10"></td>' +
                                    '<td><input type="text" class="form-control" name="igv_mixta" value="' + formula.igv + '" maxlength="3"></td>' +        
                                    '<td style="text-align: center;">' +
                                    '<a class="btn btn-sm btn-danger rem_formula_mixta_btn" data-pro-id="' + formula.id + '"><i class="icon fa fa-trash"></i></a>  ' +
                                    '<a class="btn btn-sm btn-success conci_proveedor_add_formula_mixta_btn" data-pro-id="' + formula.id + '"><i class="icon fa fa-plus"></i></a>' +
                                    '</td>' +
                                    '</tr>';
                                tabla.find('tbody').append(fila);
                                var columnaSelect = $('#input_text-columna-mixta-' + index);
                                var operadorSelect = $('#input_text-operador-mixta-' + index);
                                var valorSelect = $('#input_text-valor-mixta-' + index);

                                obtenerColumna(columnaSelect, tipo_importacion, formula.columna_id);
                                obtenerOperador(operadorSelect, formula.operador_id);
                                obtenerOpcion(valorSelect, formula.opcion_id);
                            });
                            checkRemoveButtonVisibilityFormulaMixta();
                            break;

                        default:
                            console.log('Metodo no reconocido');
                    }

                //  8. CUENTAS BANCARIAS
                    var cuentas = respuesta.cuentasBancarias.cuentas;

                    var tablaCuenta = $('.conci_proveedor_detalle_cuenta_bancaria table');
                    tablaCuenta.find('tbody').empty();

                    cuentas.forEach(function(cuenta, index) {
                        var fila = '<tr class="form-group detalle_cuenta_bancaria" data-detalle-cuenta-num="' + index + '" data-pro-id="' + cuenta.id + '">' +
                            '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id" value="' + cuenta.id + '"></td>' +
                            '<td><select class="form-control" id="banco-' + index + '" name="conci_proveedor_cuenta_banco"></select></td>' +
                            '<td><select class="form-control" id="moneda-' + index + '" name="conci_proveedor_cuenta_moneda"></select></td>' + 
                            '<td><input type="text" class="form-control" name="cuentacorriente" value="' + cuenta.cuenta_corriente + '" placeholder="000000000000000000" maxlength="100"></td>' +
                            '<td><input type="text" class="form-control" name="cuentainterbancaria" value="' + cuenta.cuenta_interbancaria + '" placeholder="00000000000000000000" maxlength="100"></td>' +
                            '<td><select class="form-control" name="estado">' +
                                '<option value="0"' + (cuenta.status == 0 ? ' selected' : '') + '>Inactivo</option>' +
                                '<option value="1"' + (cuenta.status == 1 ? ' selected' : '') + '>Activo</option>' +
                            '</select></td>' +
                            '<td style="text-align: center;">' +
                            '<a class="btn btn-sm btn-danger rem_cuenta_bancaria_btn hidden" data-pro-id="' + cuenta.id + '"><i class="icon fa fa-trash"></i></a> ' +
                            '<a class="btn btn-sm btn-success conci_proveedor_add_cuenta_bancaria_btn" data-pro-id="' + cuenta.id + '"><i class="icon fa fa-plus"></i></a>' +
                            '</td>' +
                            '</tr>';
                        tablaCuenta.find('tbody').append(fila);

                        
                        var bancoSelect = $('#banco-' + index);
                        var monedaSelect = $('#moneda-' + index);

                        obtenerbanco(bancoSelect, cuenta.banco_id);
                        obtenerMoneda(monedaSelect, cuenta.moneda_id);
                        
                    });

                    checkRemoveButtonVisibilityCuentasBancarias();

             
                //     9.  Campos de auditoria
                
                if(respuesta.result.created_at==""){
                    document.getElementById('conciAuditoriaFechaCreacionProveedor').style.display = 'none';

                }else{
                    document.getElementById('conciAuditoriaFechaCreacionProveedor').style.display = 'block';
                    $('#form_modal_conci_mant_proveedor_auditoria_fecha_create').val(respuesta.result.created_at);
                    $('#form_modal_conci_mant_proveedor_auditoria_usuario_creador').val(respuesta.result.usuario_create);
                }
                if(respuesta.result.updated_at==""){
                    document.getElementById('conciAuditoriaFechaActualizacionProveedor').style.display = 'none';

                }else{
                    document.getElementById('conciAuditoriaFechaActualizacionProveedor').style.display = 'block';
                    $('#form_modal_conci_mant_proveedor_auditoria_fecha_update').val(respuesta.result.updated_at);
                    $('#form_modal_conci_mant_proveedor_auditoria_usuario_actualizacion').val(respuesta.result.usuario_update);    
                }
                
                if(respuesta.result.created_at=="" && respuesta.result.updated_at==""){
                    $("#collapseConciProveedorCamposAuditoria").hide();
                }

            $("#conci_mant_proveedor_modal_nuevo").modal("show");
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

$("#conci_mant_proveedor_modal_nuevo .btn_guardar").off("click").on("click",function(){

        var id = $('#form_modal_conci_mant_proveedor_id').val();


    //  1.  Verificación de datos del Proveedor   ///////////////////////////////////////////////////////////////////

    
        var nombre = $('#form_modal_conci_mant_proveedor_param_nombre').val();
        var nombreCorto = $('#form_modal_conci_mant_proveedor_param_nombre_corto').val();
        var tipoImportacion = $('#form_modal_conci_mant_proveedor_param_tipo_importacion_id').val();

        if(nombre.length == 0)
        {
            alertify.error('Ingrese el nombre del proveedor',5);
            $("#form_modal_conci_mant_proveedor_param_nombre").focus();
            return false;
        }

        if(nombreCorto.length == 0)
        {
                alertify.error('Ingrese el nombre corto del proveedor',5);
                $("#form_modal_conci_mant_proveedor_param_nombre_corto").focus();
                return false;
        }

        if(tipoImportacion == "0"){
            alertify.error('Seleccionar el tipo de importación',5);
            $("#form_modal_conci_mant_proveedor_param_tipo_importacion_id").focus();
            return false;
        }

    //  2.  Verificación de estados  ///////////////////////////////////////////////////////////////////
    
        var filasEstados = document.querySelectorAll('.conci_proveedor_estado_detalle_item');
        var nombresEstado = [];

        for (var i = 0; i < filasEstados.length; i++) {
            var fila = filasEstados[i];

            var nombre = fila.querySelector('input[name="nombre"]').value.trim();
            var estado = fila.querySelector('select[name="estado"]').value;

            if (nombre === '') {
                alertify.error('Por favor, llene completamente la tabla de columnas de estados',5);
                //$("#form_modal_conci_mant_proveedor_param_nombre_corto").focus();
                return false;
            }

            if (nombresEstado.includes(nombre)) {
                alertify.error('Hay nombres duplicados en la tabla de estados', 5);
                return false;

            }else{
                nombresEstado.push(nombre);
                }
        }
    
    //  3.  Verificación de archivo a importar  ///////////////////////////////////////////////////////////////////

        var tipoImportacion = document.getElementById("form_modal_conci_mant_proveedor_param_tipo_importacion_id").value;

        switch (tipoImportacion) {
            case 'ColumnasArchivosIndependientes':

                //  Verficiacion de columnas
                
                var filasColumnaVenta = document.querySelectorAll('.detalle_columnas_venta');
                var nombresVenta = [];
                var ColumnaVenta = [];
    
    
                for (var i = 0; i < filasColumnaVenta.length; i++) {
                    var fila = filasColumnaVenta[i];
    
                    var nombre = fila.querySelector('input[name="nombre"]').value.trim();
                    var formato = fila.querySelector('select[name="conci_proveedor_formato-venta"]').value;
                    var columna = fila.querySelector('select[name="conci_proveedor_columna-venta"]').value;
                    var selectElement = fila.querySelector('select[name="conci_proveedor_columna-venta"]');
                    var nombreColumna = selectElement.options[selectElement.selectedIndex].text;
    
                    if (nombre === '' || formato === '0') {
                        alertify.error('Por favor, llene completamente la tabla de columnas del archivo de ventas',5);
                        $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                        return false;
                    }
    
                    if (columna != '0' && ColumnaVenta.includes(columna)) {
                        alertify.error('No deben existir 2 columnas con la misma columna calimaco: '+ nombreColumna, 5);
                        $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');
                        return false;
                    }else{
                        ColumnaVenta.push(columna);
                    }
                }

                var filasColumnaVenta = document.querySelectorAll('.detalle_columnas_liquidacion');
                var nombresLiquidacion = [];
                var ColumnaLiquidacion = [];

                for (var i = 0; i < filasColumnaVenta.length; i++) {
                    var fila = filasColumnaVenta[i];

                    var nombre = fila.querySelector('input[name="nombre"]').value.trim();
                    var formato = fila.querySelector('select[name="conci_proveedor_formato-liquidacion"]').value;
                    var columna = fila.querySelector('select[name="conci_proveedor_columna-liquidacion"]').value;

                    var selectElement = fila.querySelector('select[name="conci_proveedor_columna-liquidacion"]');
                    var nombreColumna = "";

                    if (selectElement && selectElement.selectedIndex >= 0) {
                        nombreColumna = selectElement.options[selectElement.selectedIndex].text;
                    } else {
                        console.error('No se encontró una opción seleccionada en el select de columna.');
                    }

                    if (nombre === '' || formato === '0') {
                        alertify.error('Por favor, llene completamente la tabla de columnas del archivo de liquidacion',5);
                        $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                        return false;
                    }

                    if (columna != '0' && ColumnaLiquidacion.includes(columna)) {
                        alertify.error('No deben existir 2 columnas con la misma columna calimaco: '+ nombreColumna, 5);
                        $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');
                        return false;
                    }else{
                        ColumnaLiquidacion.push(columna);
                    }
                }

                //    Verificación de Archivos   ///////////////////////////////////////////////////////////////////

                var formatoArchivoVenta = $('#form_modal_conci_mant_proveedor_archivo_venta_extension_id').val();
                var formatoArchivoLiquidacion = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id').val();
                var lineaInicioVenta = $('#form_modal_conci_mant_proveedor_archivo_venta_linea_inicio').val();
                var lineaInicioLiquidacion = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio').val();
                var columnaInicioVenta = $('#form_modal_conci_mant_proveedor_archivo_venta_columna_inicio').val();
                var columnaInicioLiquidacion = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_columna_inicio').val();
                var separadorVenta = $('#form_modal_conci_mant_proveedor_archivo_venta_separador').val();
                var separadorLiquidacion = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_separador').val();
                var selectValueExtensionVenta = $('#form_modal_conci_mant_proveedor_archivo_venta_extension_id option:selected').text();
                var selectValueExtensionLiquidacion = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id option:selected').text();

                if(formatoArchivoVenta == "0"){
                    alertify.error('Seleccionar la extensión de archivo de Venta',5);
                    $("#form_modal_conci_mant_proveedor_archivo_venta_extension_id").focus();
                    return false;
                    }

                if(lineaInicioVenta.length == 0  || lineaInicioVenta == 0){
                    alertify.error('Ingrese el número de la linea de inicio del archivo de ventas',5);
                    $("#form_modal_conci_mant_proveedor_archivo_venta_linea_inicio").focus();
                    return false;
                    }

                if(columnaInicioVenta.length == 0  || columnaInicioVenta == 0){
                    alertify.error('Ingrese el número de la columna de inicio del archivo de ventas',5);
                    $("#form_modal_conci_mant_proveedor_archivo_venta_columna_inicio").focus();
                    return false;
                    }

                if (selectValueExtensionVenta == "csv"){
                    if(separadorVenta == "0"){
                        alertify.error('Seleccionar el separador csv del archivo de ventas',5);
                        $("#form_modal_conci_mant_proveedor_archivo_venta_separador").focus();
                        return false;
                        }
                    }
                if(formatoArchivoLiquidacion == "0"){
                    alertify.error('Seleccionar la extensión de archivo de Liquidación',5);
                    $("#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id").focus();
                    return false;
                    }

                if(lineaInicioLiquidacion.length == 0 || lineaInicioLiquidacion == 0){
                    alertify.error('Ingrese el número de la linea de inicio del archivo de liquidación',5);
                    $("#form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio").focus();
                    return false;
                    }

                if(columnaInicioLiquidacion.length == 0 || columnaInicioLiquidacion == 0){
                    alertify.error('Ingrese el número de la columna de inicio del archivo de liquidación',5);
                    $("#form_modal_conci_mant_proveedor_archivo_liquidacion_columna_inicio").focus();
                    return false;
                    }

                if (selectValueExtensionLiquidacion == "csv") {
                    if(separadorLiquidacion == "0"){
                        alertify.error('Seleccionar el separador csv del archivo de liquidación',5);
                        $("#form_modal_conci_mant_proveedor_archivo_liquidacion_separador").focus();
                        return false;
                        }
                    }
                break;
            
            case 'ColumnasArchivoCombinado':

                //  Verificación de columnas

                    var filasColumnaCombinado = document.querySelectorAll('.detalle_columnas_combinado');
                    var nombresCombinado = [];
                    var ColumnaCombinado = [];
        
                    for (var i = 0; i < filasColumnaCombinado.length; i++) {
                        var fila = filasColumnaCombinado[i];
        
                        var nombre = fila.querySelector('input[name="nombre"]').value.trim();
                        var formato = fila.querySelector('select[name="conci_proveedor_formato-combinado"]').value;
                        var columna = fila.querySelector('select[name="conci_proveedor_columna-combinado"]').value;
                        var selectElement = fila.querySelector('select[name="conci_proveedor_columna-combinado"]');
                        var nombreColumna = selectElement.options[selectElement.selectedIndex].text;
        
                        if (nombre === '' || formato === '0') {
                            alertify.error('Por favor, llene completamente la tabla de columnas del archivo combinado',5);
                            $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                            return false;
                        }
        
                        if (columna != '0' && ColumnaCombinado.includes(columna)) {
                            alertify.error('No deben existir 2 columnas con la misma columna calimaco: '+ nombreColumna, 5);
                            $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');
                            return false;
                        }else{
                            ColumnaCombinado.push(columna);
                        }
        
                    }

                //  Verificación de formato de ID calimaco  /////////////////////////////////////////////////////////////////////

                    var selectValue = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo option:selected').text();
                    var formatoIdTipo = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo').val();
                    var formatoIdNombreColumnaJson = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_nombre_json').val();
                    var formatoIdSeparador = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id').val();

                    if(formatoIdTipo=="0"){
                        alertify.error('Seleccione el tipo de dato del ID calimaco',5);
                        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo").focus();
                        return false;
                    }else if(selectValue!='0'){
                        if (selectValue == "JSON") { //    JSON
                            if(formatoIdNombreColumnaJson.length == 0){
                                alertify.error('Ingrese el nombre de la columna json del id calimaco',5);
                                $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_nombre_json").focus();
                                return false;
                                }
                        }
                    }

                    if(formatoIdSeparador==""){
                        alertify.error('Seleccione el separador del ID calimaco',5);
                        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id").focus();
                        return false;
                    }

                //  Verificación de formato de MONTO calimaco  /////////////////////////////////////////////////////////////////////

                    var formatoMontoSeparador = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id').val();

                    if(formatoMontoSeparador==""){
                        alertify.error('Seleccione el separador del monto calimaco',5);
                        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id").focus();
                        return false;
                    }

                //    Verificación de Archivos   ///////////////////////////////////////////////////////////////////

                    var formatoArchivoCombinado = $('#form_modal_conci_mant_proveedor_archivo_combinado_extension_id').val();
                    var lineaInicioCombinado = $('#form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio').val();
                    var columnaInicioCombinado = $('#form_modal_conci_mant_proveedor_archivo_combinado_columna_inicio').val();
                    var separadorCombinado = $('#form_modal_conci_mant_proveedor_archivo_combinado_separador').val();
                    var selectValueExtensionCombinado = $('#form_modal_conci_mant_proveedor_archivo_combinado_extension_id option:selected').text();

                    if(formatoArchivoCombinado == "0"){
                        alertify.error('Seleccionar la extensión del archivo combinado',5);
                        $("#form_modal_conci_mant_proveedor_archivo_combinado_extension_id").focus();
                        return false;
                    }

                    if(lineaInicioCombinado.length == 0 || lineaInicioCombinado == 0){
                        alertify.error('Ingrese el número de la linea de inicio del archivo combinado',5);
                        $("#form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio").focus();
                        return false;
                        }

                    if(columnaInicioCombinado.length == 0 || columnaInicioCombinado == 0){
                        alertify.error('Ingrese el número de la columna de inicio del archivo combinado',5);
                        $("#form_modal_conci_mant_proveedor_archivo_combinado_columna_inicio").focus();
                        return false;
                        }

                    if (selectValueExtensionCombinado == "csv"){
                        if(separadorCombinado == "0"){
                            alertify.error('Seleccionar el separador csv del archivo combinado',5);
                            $("#form_modal_conci_mant_proveedor_archivo_combinado_separador").focus();
                            return false;
                        }
                    }
                break;

            default:
                swal({
                    title: "No se encontro el tipo de formula seleccionado.",
                    text: "Comunicarse con soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                    });
                return false;  
                break;
            }

    //  Verificación de liquidación

        var tipoCalculo = $('#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id').val();
        var tipoFormula = $('#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id').val();
        var comision_moneda_id = $('#form_modal_conci_mant_proveedor_liquidacion_moneda_id').val();
        
        if(tipoCalculo == "0"){
            alertify.error('Seleccionar el tipo de cálculo',5);
            $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").focus();
            return false;
        }

        if(tipoFormula == "0"){
            alertify.error('Seleccionar el tipo de fórmula',5);
            $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").focus();
            return false;
        }

        if(comision_moneda_id == "0"){
            alertify.error('Seleccionar la moneda de comisión',5);
            $("#form_modal_conci_mant_proveedor_liquidacion_moneda_id").focus();
            return false;
        }

    //  Verificación de tablas de formulas

    if(id != 0){

        var filasFormula = document.querySelectorAll('.'+tipoFormula);

        for (var i = 0; i < filasFormula.length; i++) {
            var fila = filasFormula[i];

            switch (tipoFormula) {
                case "FormulaEscalonada":
                    var desde = fila.querySelector('input[name="desde_escalonada"]').value.trim();
                    var hasta = fila.querySelector('input[name="hasta_escalonada"]').value.trim();
                    var porcentaje = fila.querySelector('input[name="porcentaje_escalonada"]').value.trim();
                    var constante = fila.querySelector('input[name="constante_escalonada"]').value.trim();
                    var igv = fila.querySelector('input[name="igv_escalonada"]').value.trim();
            
                    if (desde === '' || hasta === '' || porcentaje === '' || constante === '' || igv === '' ) {
                        alertify.error('Por favor, llene completamente la tabla de formulas por rangos',5);
                        return false;
                    }              
                    break;
                case "FormulaFija":
                    var columna = fila.querySelector('select[name="columna_fija"]').value.trim();
                    var operador = fila.querySelector('select[name="conci_proveedor_formula_operador_fija"]').value.trim();
                    var valor = fila.querySelector('select[name="conci_proveedor_formula_valor_fija"]').value.trim();
                    var porcentaje = fila.querySelector('input[name="porcentaje_fija"]').value.trim();
                    var constante = fila.querySelector('input[name="constante_fija"]').value.trim();
                    var igv = fila.querySelector('input[name="igv_fija"]').value.trim();
            
                    if (columna === '0' || operador === '0' || valor === '0'|| porcentaje.length == 0 || constante.length == 0 || igv.length == 0 ) {
                        alertify.error('Por favor, llene completamente la tabla de formulas fijas',5);
                        return false;
                    }
                    break;
                case "FormulaMixta":
                    var columna = fila.querySelector('select[name="columna_mixta"]').value.trim();
                    var operador = fila.querySelector('select[name="conci_proveedor_formula_operador_mixta"]').value.trim();
                    var valor = fila.querySelector('select[name="conci_proveedor_formula_valor_mixta"]').value.trim();
                    var desde = fila.querySelector('input[name="desde_mixta"]').value.trim();
                    var hasta = fila.querySelector('input[name="hasta_mixta"]').value.trim();
                    var porcentaje = fila.querySelector('input[name="porcentaje_mixta"]').value.trim();
                    var constante = fila.querySelector('input[name="constante_mixta"]').value.trim();
                    var igv = fila.querySelector('input[name="igv_mixta"]').value.trim();
                
                    if (columna === '0' || operador === '0' || valor === '0' || desde === '' || hasta === '' || porcentaje === '' || constante === '' || igv === '' ) {
                        alertify.error('Por favor, llene completamente la tabla de formulas mixtas',5);
                        return false;
                    }
                    break;            
                default:
                    swal({
                        title: "No se encontro el tipo de formula seleccionado.",
                        text: "Comunicarse con soporte",
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                        });
                    return false;          
                }

        }
    }

    //    Verificación de cuentas bancarias   ///////////////////////////////////////////////////////////////////
    
    
    var filasCuentaBancaria = document.querySelectorAll('.detalle_cuenta_bancaria');

    for (var i = 0; i < filasCuentaBancaria.length; i++) {
        var fila = filasCuentaBancaria[i];

        var moneda = fila.querySelector('select[name="conci_proveedor_cuenta_moneda"]').value;
        var banco = fila.querySelector('select[name="conci_proveedor_cuenta_banco"]').value;
        var cuentacorriente = fila.querySelector('input[name="cuentacorriente"]').value.trim();
        var cuentainterbancaria = fila.querySelector('input[name="cuentainterbancaria"]').value.trim();

        if (cuentacorriente === '' || cuentainterbancaria === '' || moneda === '0' || banco === '0') {
            alertify.error('Por favor, llene completamente la tabla de columnas de cuentas bancarias',5);
            //$("#form_modal_conci_mant_proveedor_param_nombre_corto").focus();
            return false;
        }
    }
    
    sec_conci_mant_proveedor_guardar();     

  })


function sec_conci_mant_proveedor_guardar(){

    //  Declaración de variables

        var columnasArchivoCombinado = [];
        var columnasArchivoVenta = [];
        var columnasArchivoLiquidacion = [];

        var id = $('#form_modal_conci_mant_proveedor_id').val();
        var selectValueTipoImportacion = $('#form_modal_conci_mant_proveedor_param_tipo_importacion_id').val();
        var formula = $('#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id').val();

    //  Tabla de estados
    
        var columnasEstados = [];

        var filasEstados = document.querySelectorAll('.conci_proveedor_estado_detalle_item');

        filasEstados.forEach(function(filasEstados) {
            var id = filasEstados.querySelector('input[name="id"]').value;
            var nombre = filasEstados.querySelector('input[name="nombre"]').value;
            var estadoSelect = filasEstados.querySelector('select[name="estado"]');           
            var estado = estadoSelect.options[estadoSelect.selectedIndex].value; // Declaración de formato
                
            var columnaEstado = {
                        id: id,
                        nombre: nombre,
                        estado: estado
                    };
            columnasEstados.push(columnaEstado);
            });
        
        var columnasEstadosJSON = JSON.stringify(columnasEstados);
        
    //  Tabla de columnas de importación

        switch(selectValueTipoImportacion){

            case 'ColumnasArchivosIndependientes':

                var filasColumnaArchivoVenta = document.querySelectorAll('.detalle_columnas_venta');
                filasColumnaArchivoVenta.forEach(function(filasColumnaArchivoVenta, index) {
                    var idInput = filasColumnaArchivoVenta.querySelector('input[name="id"]');
                    var nombreInput = filasColumnaArchivoVenta.querySelector('input[name="nombre"]');
                    var formatoSelect = filasColumnaArchivoVenta.querySelector('select[name="conci_proveedor_formato-venta"]');
                    var columnaSelect = filasColumnaArchivoVenta.querySelector('select[name="conci_proveedor_columna-venta"]');

                    if (idInput && nombreInput && formatoSelect && columnaSelect) {

                        var id = idInput.value;
                        var nombre = nombreInput.value;

                        if (formatoSelect.options.length > 0 && formatoSelect.selectedIndex >= 0) {
                            var formato = formatoSelect.options[formatoSelect.selectedIndex].value;
                        } else {
                            console.error(`Error en la fila ${index + 1}: formatoSelect no tiene opciones válidas.`);
                            return; 
                        }

                        if (columnaSelect.options.length > 0 && columnaSelect.selectedIndex >= 0) {
                            var columna = columnaSelect.options[columnaSelect.selectedIndex].value;
                        } else {
                            console.error(`Error en la fila ${index + 1}: columnaSelect no tiene opciones válidas.`);
                            return; 
                        }

                        var columnaArchivoVenta = {
                            id: id,
                            nombre: nombre,
                            formato: formato,
                            columna: columna
                        };
                        columnasArchivoVenta.push(columnaArchivoVenta);
                    } else {
                        // Imprimir mensajes de depuración detallados
                        console.error(`Error en la fila ${index + 1}:`);
                        if (!idInput) {
                            console.error('Elemento input[name="id"] no encontrado.');
                        }
                        if (!nombreInput) {
                            console.error('Elemento input[name="nombre"] no encontrado.');
                        }
                        if (!formatoSelect) {
                            console.error('Elemento select[name="conci_proveedor_formato-venta"] no encontrado.');
                        }
                        if (!columnaSelect) {
                            console.error('Elemento select[name="conci_proveedor_columna-venta"] no encontrado.');
                        }
                    }
                });

                    var filasColumnaArchivoLiquidacion = document.querySelectorAll('.detalle_columnas_liquidacion');
                    filasColumnaArchivoLiquidacion.forEach(function(filasColumnaArchivoLiquidacion, index) {
                        var idInput = filasColumnaArchivoLiquidacion.querySelector('input[name="id"]');
                        var nombreInput = filasColumnaArchivoLiquidacion.querySelector('input[name="nombre"]');
                        var formatoSelect = filasColumnaArchivoLiquidacion.querySelector('select[name="conci_proveedor_formato-liquidacion"]');
                        var columnaSelect = filasColumnaArchivoLiquidacion.querySelector('select[name="conci_proveedor_columna-liquidacion"]');

                        if (idInput && nombreInput && formatoSelect && columnaSelect) {

                            var id = idInput.value;
                            var nombre = nombreInput.value;

                            if (formatoSelect.options.length > 0 && formatoSelect.selectedIndex >= 0) {
                                var formato = formatoSelect.options[formatoSelect.selectedIndex].value;
                            } else {
                                console.error(`Error en la fila ${index + 1}: formatoSelect no tiene opciones válidas.`);
                                return; 
                            }

                            if (columnaSelect.options.length > 0 && columnaSelect.selectedIndex >= 0) {
                                var columna = columnaSelect.options[columnaSelect.selectedIndex].value;
                            } else {
                                console.error(`Error en la fila ${index + 1}: columnaSelect no tiene opciones válidas.`);
                                return;
                            }

                            var columnaArchivoLiquidacion = {
                                id: id,
                                nombre: nombre,
                                formato: formato,
                                columna: columna
                            };
                            columnasArchivoLiquidacion.push(columnaArchivoLiquidacion);
                        } else {

                            if (!idInput) {
                                console.error('Elemento input[name="id"] no encontrado.');
                            }
                            if (!nombreInput) {
                                console.error('Elemento input[name="nombre"] no encontrado.');
                            }
                            if (!formatoSelect) {
                                console.error('Elemento select[name="conci_proveedor_formato-liquidacion"] no encontrado.');
                            }
                            if (!columnaSelect) {
                                console.error('Elemento select[name="conci_proveedor_columna-liquidacion"] no encontrado.');
                            }
                        }
                    });

                break;

            case 'ColumnasArchivoCombinado':

                var filasColumnaArchivoCombinado = document.querySelectorAll('.detalle_columnas_combinado');
                filasColumnaArchivoCombinado.forEach(function(filasColumnaArchivoCombinado) {
                    var id = filasColumnaArchivoCombinado.querySelector('input[name="id"]').value;
                    var nombre = filasColumnaArchivoCombinado.querySelector('input[name="nombre"]').value;
                    var formatoSelect = filasColumnaArchivoCombinado.querySelector('select[name="conci_proveedor_formato-combinado"]');           
                    var formato = formatoSelect.options[formatoSelect.selectedIndex].value; 
                    var columnaSelect = filasColumnaArchivoCombinado.querySelector('select[name="conci_proveedor_columna-combinado"]');           
                    var columna = columnaSelect.options[columnaSelect.selectedIndex].value; 
            
                    var columnaArchivoCombinado = {
                                id:id,
                                nombre: nombre,
                                formato: formato,
                                columna: columna
                                };
                    columnasArchivoCombinado.push(columnaArchivoCombinado);
                    });
                break;

            default:
                console.log('Metodo no reconocido');
            }
            
    var columnasArchivoVentaJSON = JSON.stringify(columnasArchivoVenta);
    var columnasArchivoLiquidacionJSON = JSON.stringify(columnasArchivoLiquidacion);
    var columnasArchivoCombinadoJSON = JSON.stringify(columnasArchivoCombinado);

    //  Tabla de formulas de Comisión

    var tipoFormula = $('#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id').val();
    var formulasComision = [];

    if(id != 0){
        switch (tipoFormula) {
            case 'FormulaFija':
                var filasFormulasComision = document.querySelectorAll('.detalle_formula_fija');
                filasFormulasComision.forEach(function(filasFormulasComision){
                    var id = filasFormulasComision.querySelector('input[name="id"]').value;
                    var columnaSelect = filasFormulasComision.querySelector('select[name="columna_fija"]');           
                    var columna = columnaSelect.options[columnaSelect.selectedIndex].value; 
                    var operadorSelect = filasFormulasComision.querySelector('select[name="conci_proveedor_formula_operador_fija"]');           
                    var operador = operadorSelect.options[operadorSelect.selectedIndex].value; 
                    var valorSelect = filasFormulasComision.querySelector('select[name="conci_proveedor_formula_valor_fija"]');           
                    var valor = valorSelect.options[valorSelect.selectedIndex].value; 
                    var porcentaje = filasFormulasComision.querySelector('input[name="porcentaje_fija"]').value;
                    var constante = filasFormulasComision.querySelector('input[name="constante_fija"]').value;
                    var igv = filasFormulasComision.querySelector('input[name="igv_fija"]').value;
    
    
                    var filaFormulaComision = {
                            id: id,
                            nombre: columna,
                            operador: operador,
                            valor: valor,
                            porcentaje:porcentaje,
                            constante:constante,
                            igv:igv
                            };
                    formulasComision.push(filaFormulaComision);
                    });
            break;
            case 'FormulaEscalonada':
                var filasFormulasComision = document.querySelectorAll('.detalle_formula_escalonada');
                filasFormulasComision.forEach(function(filasFormulasComision){
                    var id = filasFormulasComision.querySelector('input[name="id"]').value;
                    var desde_escalonada = filasFormulasComision.querySelector('input[name="desde_escalonada"]').value;
                    var hasta_escalonada = filasFormulasComision.querySelector('input[name="hasta_escalonada"]').value;
                    var porcentaje_escalonada = filasFormulasComision.querySelector('input[name="porcentaje_escalonada"]').value;
                    var constante_escalonada = filasFormulasComision.querySelector('input[name="constante_escalonada"]').value;
                    var igv_escalonada = filasFormulasComision.querySelector('input[name="igv_escalonada"]').value;
    
                    var filaFormulaComision = {
                            id: id,
                            desde_escalonada: desde_escalonada,
                            hasta_escalonada: hasta_escalonada,
                            porcentaje_escalonada: porcentaje_escalonada,
                            constante_escalonada:constante_escalonada,
                            igv_escalonada:igv_escalonada
                            };
                    formulasComision.push(filaFormulaComision);
                    });
            break;
            case 'FormulaMixta':
                var filasFormulasComision = document.querySelectorAll('.detalle_formula_mixta');
                filasFormulasComision.forEach(function(filasFormulasComision){
                    var id = filasFormulasComision.querySelector('input[name="id"]').value;
                    var columnaSelect = filasFormulasComision.querySelector('select[name="columna_mixta"]');           
                    var columna = columnaSelect.options[columnaSelect.selectedIndex].value; 
                    var operadorSelect = filasFormulasComision.querySelector('select[name="conci_proveedor_formula_operador_mixta"]');           
                    var operador = operadorSelect.options[operadorSelect.selectedIndex].value; 
                    var valorSelect = filasFormulasComision.querySelector('select[name="conci_proveedor_formula_valor_mixta"]');           
                    var valor = valorSelect.options[valorSelect.selectedIndex].value; 
    
                    var desde_mixta = filasFormulasComision.querySelector('input[name="desde_mixta"]').value;
                    var hasta_mixta = filasFormulasComision.querySelector('input[name="hasta_mixta"]').value;
    
                    var porcentaje_mixta = filasFormulasComision.querySelector('input[name="porcentaje_mixta"]').value;
                    var constante_mixta = filasFormulasComision.querySelector('input[name="constante_mixta"]').value;
                    var igv_mixta = filasFormulasComision.querySelector('input[name="igv_mixta"]').value;
    
                    var filaFormulaComision = {
                        id: id,
                        nombre:columna,
                        operador:operador,
                        valor:valor,
                        desde_mixta:desde_mixta,
                        hasta_mixta:hasta_mixta,
                        porcentaje_mixta: porcentaje_mixta,
                        constante_mixta:constante_mixta,
                        igv_mixta:igv_mixta
                        };
                    formulasComision.push(filaFormulaComision);
                    });
    
            break;
            default:
                alertify.error('El metodo de formula no existe en el codigo. Contactar con soporte',5);
                $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").focus();
                return false;
        }
        var formulasComisionJSON = JSON.stringify(formulasComision);
    
    }

    //  CUENTAS BANCARIAS

        var cuentasBancaria = [];

        var filasCuentaBancaria = document.querySelectorAll('.detalle_cuenta_bancaria');
        filasCuentaBancaria.forEach(function(filasCuentaBancaria, index) {
            var idInput = filasCuentaBancaria.querySelector('input[name="id"]');
            var bancoSelect = filasCuentaBancaria.querySelector('select[name="conci_proveedor_cuenta_banco"]');
            var monedaSelect = filasCuentaBancaria.querySelector('select[name="conci_proveedor_cuenta_moneda"]');
            var cuentacorrienteInput = filasCuentaBancaria.querySelector('input[name="cuentacorriente"]');
            var cuentainterbancariaInput = filasCuentaBancaria.querySelector('input[name="cuentainterbancaria"]');

            // Verificar que los elementos existen antes de acceder a sus propiedades
            if (idInput && bancoSelect && monedaSelect && cuentacorrienteInput && cuentainterbancariaInput) {

                var id = idInput.value;

                // Verificar que bancoSelect tenga opciones y selectedIndex válido
                if (bancoSelect.options.length > 0 && bancoSelect.selectedIndex >= 0) {
                    var banco = bancoSelect.options[bancoSelect.selectedIndex].value;
                } else {
                    console.error(`Error en la fila ${index + 1}: bancoSelect no tiene opciones válidas.`);
                    return; // Salir del bucle actual si hay un problema con bancoSelect
                }

                // Verificar que monedaSelect tenga opciones y selectedIndex válido
                if (monedaSelect.options.length > 0 && monedaSelect.selectedIndex >= 0) {
                    var moneda = monedaSelect.options[monedaSelect.selectedIndex].value;
                } else {
                    console.error(`Error en la fila ${index + 1}: monedaSelect no tiene opciones válidas.`);
                    return; // Salir del bucle actual si hay un problema con monedaSelect
                }

                var cuentacorriente = cuentacorrienteInput.value;
                var cuentainterbancaria = cuentainterbancariaInput.value;

                var filaCuentaBancaria = {
                    id: id,
                    banco: banco,
                    moneda: moneda,
                    cuentacorriente: cuentacorriente,
                    cuentainterbancaria: cuentainterbancaria
                };
                cuentasBancaria.push(filaCuentaBancaria);
            } else {
                // Imprimir mensajes de depuración detallados
                console.error(`Error en la fila ${index + 1}:`);
                if (!idInput) {
                    console.error('Elemento input[name="id"] no encontrado.');
                }
                if (!bancoSelect) {
                    console.error('Elemento select[name="conci_proveedor_cuenta_banco"] no encontrado.');
                }
                if (!monedaSelect) {
                    console.error('Elemento select[name="conci_proveedor_cuenta_moneda"] no encontrado.');
                }
                if (!cuentacorrienteInput) {
                    console.error('Elemento input[name="cuentacorriente"] no encontrado.');
                }
                if (!cuentainterbancariaInput) {
                    console.error('Elemento input[name="cuentainterbancaria"] no encontrado.');
                }
            }
        });

        var cuentasBancariaJSON = JSON.stringify(cuentasBancaria);

    if(id == 0)
    {
        // CREAR
        aviso = "¿Está seguro de registrar el proveedor?";
        titulo = "Registrar";
        accion = "conci_mant_proveedor_guardar";

    }
    else
    {
        // EDITAR
        aviso = "¿Está seguro de editar el proveedor?";
        titulo = "Editar";
        accion = "conci_mant_proveedor_guardar";
    }
  
    swal(
        {
            title: titulo,
            text: aviso,
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
                var dataForm = new FormData($("#Frm_RegistroConciProveedor")[0]);
    
                dataForm.append("columnasArchivoVentaJSON",columnasArchivoVentaJSON);
                dataForm.append("columnasArchivoLiquidacionJSON",columnasArchivoLiquidacionJSON);
                dataForm.append("columnasEstadosJSON",columnasEstadosJSON);
                dataForm.append("columnasArchivoCombinadoJSON",columnasArchivoCombinadoJSON);
                dataForm.append("formulasComisionJSON",formulasComisionJSON);
                dataForm.append("cuentasBancariaJSON",cuentasBancariaJSON);
                dataForm.append("accion",accion);

                $.ajax({
                    url: "sys/set_conciliacion_mantenimiento_proveedor.php",
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
                        if (parseInt(respuesta.http_code) == 400) {
                            swal({
                                title: "Error al guardar el proveedor.",
                                text: respuesta.error,
                                html:true,
                                type: "warning",
                                closeOnConfirm: false,
                                showCancelButton: false
                                });
                            return false;
                        }else if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Guardar",
                                text: "El proveedor se guardó correctamente.",
                                html:true,
                                type: "success",
                                closeOnConfirm: false,
                                showCancelButton: false
                                });
                                
                            $('#Frm_RegistroConciProveedor')[0].reset();
                            $("#form_modal_conci_mant_proveedor_id").val(0);
                            $("#conci_mant_proveedor_modal_nuevo").modal("hide");
                            sec_conci_mant_proveedor_listar();
                        }
                    }
                });
            }
    });
}
//  PRINCIPAL   //////////////////////////////////////////////////////////////////////////////////
function conci_mant_proveedor_limpiar()
  {
    
    document.getElementById('conci_proveedor_archivo_combinado_separador_csv').style.display = 'none';
    //document.getElementById('conci_mant_proveedor_linea_excel').style.display = 'none';

    //  1. Datos de proveedor

        $('#form_modal_conci_mant_proveedor_id').val(0);

        $('#form_modal_conci_mant_proveedor_param_id').val(0);
        $('#form_modal_conci_mant_proveedor_param_nombre').val("");
        $('#form_modal_conci_mant_proveedor_param_nombre_corto').val("");
        $("#form_modal_conci_mant_proveedor_param_tipo_importacion_id").val(0).trigger("change.select2");

    //   6. Formato 

        //  Archivo combinado

        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_id").val(0).trigger("change.select2");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo").val(0).trigger("change.select2");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_nombre_json').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_prefijo').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_sufijo').val("");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id").val(0).trigger("change.select2");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_prefijo').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_sufijo').val("");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id").val(0).trigger("change.select2");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_prefijo').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_sufijo').val("");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_separador_id").val(0).trigger("change.select2");

        //  Archivo Independiente

        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_id").val(0).trigger("change.select2");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_tipo").val(0).trigger("change.select2");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_nombre_json').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_prefijo').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_sufijo').val("");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_separador_id").val(0).trigger("change.select2");

        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_prefijo').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_sufijo').val("");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_separador_id").val(0).trigger("change.select2");

        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_tipo").val(0).trigger("change.select2");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_nombre_json').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_prefijo').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_sufijo').val("");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_separador_id").val(0).trigger("change.select2");

        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_prefijo').val("");
        $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_sufijo').val("");
        $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador_id").val(0).trigger("change.select2");


        $("#form_modal_conci_mant_proveedor_archivo_combinado_extension_id").val(1).trigger("change.select2");
        $("#form_modal_conci_mant_proveedor_archivo_combinado_separador_id").val(0).trigger("change.select2");
        $('#form_modal_conci_mant_proveedor_archivo_combinado_linea_inicio').val("1");

        $("#form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id").val(0).trigger("change.select2");
        $("#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id").val(1).trigger("change.select2");
        $('#form_modal_conci_mant_proveedor_archivo_liquidacion_linea_inicio').val("1");

        $('#form_modal_conci_mant_proveedor_archivo_venta_linea_inicio').val("1");
        $("#form_modal_conci_mant_proveedor_archivo_venta_extension_id").val(1).trigger("change.select2");
        $("#form_modal_conci_mant_proveedor_archivo_venta_separador_id").val(0).trigger("change.select2");
        

        $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val(0).trigger("change.select2");
        $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val(0).trigger("change.select2");
        $("#form_modal_conci_mant_proveedor_liquidacion_moneda_id").val(0).trigger("change.select2");

    //  Ocultar div

        $("#FormulaEscalonada").hide();
        $("#FormulaFija").hide();
        $("#FormulaMixta").hide();

        $("#ColumnasArchivosIndependientes").hide();
        $("#ColumnasArchivoCombinado").hide();
        $("#ColumnasArchivosIndependientes").hide();


        $("#FormatoColumnasArchivosIndependientes").hide();
        $("#FormatoColumnasArchivoCombinado").hide();

        $("#ConciFormatoColumnasArchivoCombinado").hide();
        $("#ConciFormatoColumnasArchivosIndependientes").hide();
    
    }

function conci_mant_proveedor_limpiar_tablas(){
        

    //  Limpiar collumnas de estados

        var tabla = document.querySelector('.conci_proveedor_estado_detalle_holder tbody');
            var filasActuales = tabla.querySelectorAll('.conci_proveedor_estado_detalle_item');

            filasActuales.forEach(function(fila) {
                fila.remove();
            });

            var filaInicial = '<tr class="form-group conci_proveedor_estado_detalle_item" data-detalle-estado-num="0">' +
                    '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id"></td>' +
                    '<td><input type="text" class="form-control" name="nombre" placeholder="Nombre" maxlength="100"></td>' +
                    '<td><select class="form-control" name="estado"><option value="0">No valido</option><option value="1">Valido</option></select></td>' +
                    '<td style="text-align: center;">' +
                    '<a class="btn btn-sm btn-danger conci_proveedor_estado_rem_btn hidden"><i class="icon fa fa-trash"></i></a> ' +
                    '<a class="btn btn-sm btn-success conci_proveedor_add_estado_btn"><i class="icon fa fa-plus"></i></a>' +
                    '</td>' +
                    '</tr>';
                tabla.innerHTML = filaInicial;
                conci_mant_proveedor_banco_listar();
                conci_mant_proveedor_moneda_listar();

            if ($(".conci_proveedor_estado_detalle_item").length === 0) {
                $(".conci_proveedor_add_estado_btn").click();
            }
        

    //  Limpiar columnas de combinado

        var tabla = document.querySelector('.conci_proveedor_detalle_columna_combinado tbody');
        var filasActuales = tabla.querySelectorAll('.detalle_columnas_combinado');

        filasActuales.forEach(function(fila) {
            fila.remove();
        });

        var filaInicial = '<tr class="form-group detalle_columnas_combinado" data-detalle-columna-combinado-num="0" data-fila-defecto="true">' +
                '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id"></td>' +
                '<td><input type="text" class="form-control" name="nombre" placeholder="Nombre" maxlength="100"></td>' +
                '<td><select class="form-control" id="input_text-formato-combinado" name="conci_proveedor_formato-combinado"></select></td>' +
                '<td><select class="form-control" id="input_text-formato-combinado" name="conci_proveedor_columna-combinado"></select></td>' +
                '<td style="text-align: center;">' +
                '<a class="btn btn-sm btn-danger rem_columna_combinado_btn hidden"><i class="icon fa fa-trash"></i></a> ' +
                '<a class="btn btn-sm btn-success conci_proveedor_add_columna_combinado_btn"><i class="icon fa fa-plus"></i></a>' +
                '</td>' +
                '</tr>';
        tabla.innerHTML = filaInicial;
        conci_mant_proveedor_columna_combinado_tipo();
        conci_mant_proveedor_columna_combinado_conciliacion();

        if ($(".detalle_columnas_combinado").length === 0) {
            $(".conci_proveedor_add_columna_combinado_btn").click();
        }

    //  Limpiar columnas venta

        var tabla = document.querySelector('.conci_proveedor_detalle_columna_venta tbody');
        var filasActuales = tabla.querySelectorAll('.detalle_columnas_venta');

        filasActuales.forEach(function(fila) {
            fila.remove();
        });

        var filaInicial = '<tr class="form-group detalle_columnas_venta" data-detalle-columna-venta-num="0" data-fila-defecto="true">' +
                '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id"></td>' +
                '<td><input type="text" class="form-control" name="nombre" placeholder="Nombre" maxlength="100"></td>' +
                '<td><select class="form-control" id="input_text-formato-venta" name="conci_proveedor_formato-venta"></select></td>' +
                '<td><select class="form-control" id="input_text-formato-venta" name="conci_proveedor_columna-venta"></select></td>' +
                '<td style="text-align: center;">' +
                '<a class="btn btn-sm btn-danger rem_columna_venta_btn hidden"><i class="icon fa fa-trash"></i></a> ' +
                '<a class="btn btn-sm btn-success conci_proveedor_add_columna_ventas_btn"><i class="icon fa fa-plus"></i></a>' +
                '</td>' +
                '</tr>';
        tabla.innerHTML = filaInicial;
        conci_mant_proveedor_columna_venta_tipo();
        conci_mant_proveedor_columna_venta_conciliacion();

        if ($(".detalle_columnas_venta").length === 0) {
            $(".conci_proveedor_add_columna_ventas_btn").click();
        }

    //  Limpiar columnas de liquidación

        var tabla = document.querySelector('.conci_proveedor_detalle_columna_liquidacion tbody');
        var filasActuales = tabla.querySelectorAll('.detalle_columnas_liquidacion');

        filasActuales.forEach(function(fila) {
            fila.remove();
        });

        var filaInicial = '<tr class="form-group detalle_columnas_liquidacion" data-detalle-columna-liquidacion-num="0" data-fila-defecto="true">' +
                '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id"></td>' +
                '<td><input type="text" class="form-control" name="nombre" placeholder="Nombre" maxlength="100"></td>' +
                '<td><select class="form-control" id="input_text-formato-liquidacion" name="conci_proveedor_formato-liquidacion"></select></td>' +
                '<td><select class="form-control" id="input_text-columna-liquidacion" name="conci_proveedor_columna-liquidacion"></select></td>' +
                '<td style="text-align: center;">' +
                '<a class="btn btn-sm btn-danger rem_columna_liquidacion_btn hidden"><i class="icon fa fa-trash"></i></a> ' +
                '<a class="btn btn-sm btn-success conci_proveedor_add_columna_liquidacion_btn"><i class="icon fa fa-plus"></i></a>' +
                '</td>' +
                '</tr>';
        tabla.innerHTML = filaInicial;
        conci_mant_proveedor_columna_liquidacion_tipo();
        conci_mant_proveedor_columna_liquidacion_conciliacion();

        if ($(".detalle_columnas_liquidacion").length === 0) {
            $(".conci_proveedor_add_columna_liquidacion_btn").click();
        }

    //  Limpiar cuentas Bancarias

        var tabla = document.querySelector('.conci_proveedor_detalle_cuenta_bancaria tbody');
        var filasActuales = tabla.querySelectorAll('.detalle_cuenta_bancaria');

        filasActuales.forEach(function(fila) {
            fila.remove();
        });

        var filaInicial = '<tr class="form-group detalle_cuenta_bancaria" data-detalle-cuenta-num="0" data-fila-defecto="true">' +
                '<td type="hidden" style="text-align: center; display: none;"><input type="text" class="form-control" name="id"></td>' +
                '<td><select class="form-control" name="conci_proveedor_cuenta_banco"></select></td>' +
                '<td><select class="form-control" name="conci_proveedor_cuenta_moneda"></select></td>' +
                '<td><input type="text" class="form-control" name="cuentacorriente" placeholder="Nombre" maxlength="100"></td>' +
                '<td><input type="text" class="form-control" name="cuentainterbancaria" placeholder="Nombre" maxlength="100"></td>' +
                '<td><select class="form-control"  id="input_text-estado" name="estado"><option value="0">Inactivo</option><option value="1">Activo</option></select></td>' +
                '<td style="text-align: center;">' +
                '<a class="btn btn-sm btn-danger rem_cuenta_bancaria_btn hidden"><i class="icon fa fa-trash"></i></a> ' +
                '<a class="btn btn-sm btn-success conci_proveedor_add_cuenta_bancaria_btn"><i class="icon fa fa-plus"></i></a>' +
                '</td>' +
                '</tr>';
            tabla.innerHTML = filaInicial;
            conci_mant_proveedor_banco_listar();
            conci_mant_proveedor_moneda_listar();

        if ($(".detalle_cuenta_bancaria").length === 0) {
            $(".conci_proveedor_add_cuenta_bancaria_btn").click();
        }

    //  Limpiar formula mixtas

        var tabla = document.querySelector('.FormulaMixta tbody');
        var filasActuales = tabla.querySelectorAll('.detalle_formula_mixta');

        filasActuales.forEach(function(fila) {
            fila.remove();
        });

        var filaInicial = '<tr class="form-group detalle_formula_mixta" data-detalle-num="0">' +
            '<td type="hidden" style="text-align: center; display: none;"><input class="form-control" name="id"></td>' +
            '<td><select class="form-control" name="columna_mixta" id="input_text-columna-mixta"></select></td>' +
            '<td><select class="form-control" name="conci_proveedor_formula_operador_mixta" id="input_text-operador-mixta"></select></td>' +
            '<td><select class="form-control" name="conci_proveedor_formula_valor_mixta" id="input_text-valor-mixta"></select></td>' +
            '<td><input type="text" class="form-control" name="desde_mixta" id="input_text-desde_mixta" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10" value="0"></td>' +
            '<td><input type="text" class="form-control" name="hasta_mixta" id="input_text-hasta_mixta" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10" value="20"></td>' +
            '<td><input type="text" class="form-control" style="width: 80px;" name="porcentaje_mixta" id="input_text-porcentaje-mixta" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="5" value=""></td>' +
            '<td><input type="text" class="form-control" name="constante_mixta" style="width: 80px;" id="input_text-constante-mixta" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10" value=""></td>' +
            '<td><input type="text" class="form-control" name="igv_mixta" style="width: 80px;" id="input_text-igv-mixta" oninput="this.value=this.value.replace(/[^0-9]/g,\'\')" maxlength="3" value="18"></td>' +
            '<td><button class="btn btn-sm btn-danger rem_formula_mixta_btn hidden"><i class="icon fa fa-trash"></i></button>' +
            '<a class="btn btn-sm btn-success conci_proveedor_add_formula_mixta_btn"><i class="icon fa fa-plus"></i></a></td>' +
            '</tr>';

        tabla.innerHTML = filaInicial;

        conci_mant_proveedor_formula_mixta_opcion_listar();
        conci_mant_proveedor_formula_mixta_operador_listar();
        conci_mant_proveedor_fn_mostrarFormulasComision();

        if ($(".detalle_formula_mixta").length === 0) {
            $(".conci_proveedor_add_formula_mixta_btn").click();
        }

    //  Limpiar formulas Escalonada

        var tabla = document.querySelector('.FormulaEscalonada tbody');
        var filasActuales = tabla.querySelectorAll('.detalle_formula_escalonada');

        filasActuales.forEach(function(fila) {
            fila.remove();
        });

        var filaInicial = '<tr class="form-group detalle_formula_escalonada" data-detalle-num="0">' +
            '<td type="hidden" style="text-align: center; display: none;"><input class="form-control" name="id"></td>' +
            '<td><input type="text" class="form-control" name="desde_escalonada" id="input_text-desde-escalonada" value="0" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10"></td>' +
            '<td><input type="text" class="form-control" name="hasta_escalonada" id="input_text-hasta-escalonada" value="20" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10"></td>' +
            '<td><input type="text" class="form-control" name="porcentaje_escalonada" id="input_text-porcentaje-escalonada" value="" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="5"></td>' +
            '<td><input type="text" class="form-control" name="constante_escalonada" id="input_text-constante-escalonada" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10"></td>' +
            '<td><input type="text" class="form-control" name="igv_escalonada" id="input_text-igv-escalonada" oninput="this.value=this.value.replace(/[^0-9]/g,\'\')" maxlength="3" value="18"></td>' +
            '<td><button class="btn btn-sm btn-danger rem_formula_escalonada_btn hidden"><i class="icon fa fa-trash"></i></button>' +
            '<a class="btn btn-sm btn-success conci_proveedor_add_formula_escalonada_btn"><i class="icon fa fa-plus"></i></a></td>' +
            '</tr>';

        tabla.innerHTML = filaInicial;

        if ($(".detalle_formula_escalonada").length === 0) {
            $(".conci_proveedor_add_formula_escalonada_btn").click();
        }

    //  Limpiar formula fija

        var tabla = document.querySelector('.FormulaFija tbody');
        var filasActuales = tabla.querySelectorAll('.detalle_formula_fija');

        filasActuales.forEach(function(fila) {
            fila.remove();
        });

        var filaInicial = '<tr class="form-group detalle_formula_fija" data-detalle-num="0">' +
            '<td type="hidden" style="text-align: center; display: none;"><input class="form-control" name="id"></td>' +
            '<td><select class="form-control" style="width: 150px;" name="columna_fija" id="input_text-columna-fija"></select></td>' +
            '<td><select class="form-control" name="conci_proveedor_formula_operador_fija" id="input_text-operador-fija">' +
            '<option value="0">Seleccionar</option>' +
            '<option value="1">==</option>' +
            '</select></td>' +
            '<td><select class="form-control" style="width: 110px;" name="conci_proveedor_formula_valor_fija" id="input_text-valor-fija"></select></td>' +
            '<td><input type="text" class="form-control" name="porcentaje_fija" id="input_text-porcentaje-fija" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="5" value=""></td>' +
            '<td><input type="text" class="form-control" name="constante_fija" id="input_text-constante-fija" oninput="this.value=this.value.replace(/[^\\d.]/g,\'\')" maxlength="10" value=""></td>' +
            '<td><input type="text" class="form-control" name="igv_fija" id="input_text-igv-fija" oninput="this.value=this.value.replace(/[^0-9]/g,\'\')" maxlength="3" value="18"></td>' +
            '<td><button class="btn btn-sm btn-danger rem_formula_fija_btn hidden"><i class="icon fa fa-trash"></i></button>' +
            '<a class="btn btn-sm btn-success conci_proveedor_add_formula_fija_btn"><i class="icon fa fa-plus"></i></a></td>' +
            '</tr>';

        tabla.innerHTML = filaInicial;
        conci_mant_proveedor_formula_fija_opcion_listar();
        conci_mant_proveedor_formula_fija_operador_listar();
        conci_mant_proveedor_fn_mostrarFormulasComision();

        if ($(".detalle_formula_fija").length === 0) {
            $(".conci_proveedor_add_formula_fija_btn").click();
        }


}

//  DATOS GENERALES ///////////////////////////////////////////////////////////////////////////////

function conci_mant_proveedor_importacion_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_param_tipo_importacion_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_param_tipo_importacion_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_importacion_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.metodo + "'>" + e.nombre + "</option>");
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

function conci_mant_proveedor_fn_mostrarColumnasArchivo() {
    var selectValue = document.getElementById("form_modal_conci_mant_proveedor_param_tipo_importacion_id").value;

    $("#ColumnasArchivoCombinado").hide();
    $("#ColumnasArchivosIndependientes").hide();
    $("#FormatoColumnasArchivoCombinado").hide();
    $("#FormatoColumnasArchivosIndependientes").hide();
    $("#ConciFormatoColumnasArchivoCombinado").hide();
    $("#ConciFormatoColumnasArchivosIndependientes").hide();

    if(selectValue!='0'){
        $("#" + selectValue).show();
        $("#Formato" + selectValue).show();
        $("#ConciFormato" + selectValue).show();
        $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val(0).trigger("change.select2");
        }
}
//  FORMATO DE ID

function conci_mant_proveedor_conciliacion_calimaco_id(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_id"
            },
        success: function (datos) {
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
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_venta_id(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_id"
            },
        success: function (datos) {
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
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}
function conci_mant_proveedor_conciliacion_calimaco_liquidacion_id(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_id"
            },
        success: function (datos) {
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
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}
function conci_mant_proveedor_conciliacion_calimaco_id_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_tipo"
            },
        success: function (datos) {
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
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_venta_id_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_tipo']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_tipo").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_tipo"
            },
        success: function (datos) {
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
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_liquidacion_id_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_tipo']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_tipo").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_tipo"
            },
        success: function (datos) {
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
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_fn_mostrarNombreColumnaJsonId() {
    var selectValue = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_tipo option:selected').text();

    document.getElementById('conci_proveedor_conciliacion_calimaco_nombre_columna_tipo').style.display = 'none';

    if(selectValue!=0){

        if (selectValue == "JSON") { //    JSON
            document.getElementById('conci_proveedor_conciliacion_calimaco_nombre_columna_tipo').style.display = 'block';
            $("input[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_nombre_json']").val('');
        }
    }
}

function conci_mant_proveedor_fn_mostrarNombreColumnaVentaJsonId() {
    var selectValue = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_tipo option:selected').text();

    document.getElementById('conci_proveedor_conciliacion_calimaco_nombre_columna_venta_tipo').style.display = 'none';

    if(selectValue!=0){

        if (selectValue == "JSON") { //    JSON
            document.getElementById('conci_proveedor_conciliacion_calimaco_nombre_columna_venta_tipo').style.display = 'block';
            $("input[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_nombre_json']").val('');
        }
    }
}

function conci_mant_proveedor_fn_mostrarNombreColumnaLiquidacionJsonId() {
    var selectValue = $('#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_tipo option:selected').text();

    document.getElementById('conci_proveedor_conciliacion_calimaco_nombre_columna_liquidacion_tipo').style.display = 'none';

    if(selectValue!=0){

        if (selectValue == "JSON") { //    JSON
            document.getElementById('conci_proveedor_conciliacion_calimaco_nombre_columna_liquidacion_tipo').style.display = 'block';
            $("input[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_nombre_json']").val('');
        }
    }
}


function conci_mant_proveedor_conciliacion_calimaco_monto_separador(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_monto_separador"
            },
        success: function (datos) {
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

                    
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_comision_total_separador(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_comision_total_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_monto_separador"
            },
        success: function (datos) {
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

                    
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_venta_monto_separador(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_monto_venta_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_monto_separador"
            },
        success: function (datos) {
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

                    
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_comision_total_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_monto_separador"
            },
        success: function (datos) {
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

                    
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}
function conci_mant_proveedor_conciliacion_calimaco_id_separador(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_separador"
            },
        success: function (datos) {
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

                    
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_venta_id_separador(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_venta_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_separador"
            },
        success: function (datos) {
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

                    
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}

function conci_mant_proveedor_conciliacion_calimaco_liquidacion_id_separador(){
    let select = $("[name='form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_conciliacion_calimaco_liquidacion_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_calimaco_separador"
            },
        success: function (datos) {
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

                    
        
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}


//  ARCHIVO ///////////////////////////////////////////////////////////////////////////////////////
function conci_mant_proveedor_archivo_combinado_formato_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_archivo_combinado_extension_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_archivo_combinado_extension_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_formato_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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


function conci_mant_proveedor_archivo_venta_formato_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_archivo_venta_extension_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_archivo_venta_extension_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_formato_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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

function conci_mant_proveedor_archivo_liquidacion_formato_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_formato_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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

//  Separador
function conci_mant_proveedor_archivo_liquidacion_separador_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_separador_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}
function conci_mant_proveedor_archivo_liquidacion_separador_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_archivo_liquidacion_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_separador_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}
function conci_mant_proveedor_archivo_venta_separador_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_archivo_venta_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_archivo_venta_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_separador_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}
function conci_mant_proveedor_archivo_combinado_separador_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_archivo_combinado_separador_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_archivo_combinado_separador_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_separador_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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
                console.error("Error al obtener la lista de separadores de csv.");
                }
        });
}

function conci_mant_proveedor_columna_combinado_tipo(){
    let select = $("[name='conci_proveedor_formato-combinado']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_columna_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
        
        //    if (!valorSeleccionado) {
        //        let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
        //            $(select).append(opcionDefault);
        //        }
        
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                
                    //if (valorSeleccionado != 0 || valorSeleccionado != null) {
                    //    $(select).val(valorSeleccionado);
                    //}
                    
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_columna_venta_tipo(){
    let select = $("[name='conci_proveedor_formato-venta']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_columna_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
        /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        */
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_columna_liquidacion_tipo(){
    let select = $("[name='conci_proveedor_formato-liquidacion']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_columna_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
        /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        */
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_columna_combinado_conciliacion(){
    let select = $("[name='conci_proveedor_columna-combinado']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_columna_combinado"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
        /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        */
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_columna_venta_conciliacion(){
    let select = $("[name='conci_proveedor_columna-venta']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_columna_venta"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
        /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        */
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_columna_liquidacion_conciliacion(){
    let select = $("[name='conci_proveedor_columna-liquidacion']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_columna_liquidacion"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
        /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        */
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_fn_mostrarSeparadorCsvArchivoVenta() {
    //var selectValue = document.getElementById("form_modal_conci_mant_proveedor_archivo_venta_extension_id").value;
    var selectValue = $('#form_modal_conci_mant_proveedor_archivo_venta_extension_id option:selected').text();

    document.getElementById('conci_proveedor_archivo_venta_separador_csv').style.display = 'none';

    if(selectValue!='0'){
        $("input[name='form_conci_mant_proveedor_param_formato_archivo_venta']").val('');

        if (selectValue == "csv") { //    CSV
            document.getElementById('conci_proveedor_archivo_venta_separador_csv').style.display = 'block';
            $("#form_modal_conci_mant_proveedor_archivo_venta_separador_id").val(0).trigger("change.select2");
        }
    }
}
function conci_mant_proveedor_fn_mostrarSeparadorCsvArchivoLiquidacion() {
    //var selectValue = document.getElementById("form_modal_conci_mant_proveedor_archivo_venta_extension_id").value;
    var selectValue = $('#form_modal_conci_mant_proveedor_archivo_liquidacion_extension_id option:selected').text();

    document.getElementById('conci_proveedor_archivo_liquidacion_separador_csv').style.display = 'none';


    if(selectValue!='0'){
        $("input[name='form_conci_mant_proveedor_param_formato_archivo_liquidacion']").val('');

        if (selectValue == "csv") { //    CSV
            document.getElementById('conci_proveedor_archivo_liquidacion_separador_csv').style.display = 'block';
            $("#form_modal_conci_mant_proveedor_archivo_liquidacion_separador").val(0).trigger("change.select2");
        }
    }
}
function conci_mant_proveedor_fn_mostrarSeparadorCsvArchivoCombinado() {
    //var selectValue = document.getElementById("form_modal_conci_mant_proveedor_archivo_venta_extension_id").value;
    var selectValue = $('#form_modal_conci_mant_proveedor_archivo_combinado_extension_id option:selected').text();

    document.getElementById('conci_proveedor_archivo_combinado_separador_csv').style.display = 'none';


    if(selectValue!='0'){
        $("input[name='form_conci_mant_proveedor_param_formato_archivo_combinado']").val('');

        if (selectValue == "csv") { //    CSV
            document.getElementById('conci_proveedor_archivo_combinado_separador_csv').style.display = 'block';
            $("#form_modal_conci_mant_proveedor_archivo_combinado_separador").val(0).trigger("change.select2");
        }
    }
}


//  LIQUIDACIÓN ///////////////////////////////////////////////////////////////////////////////////////

function conci_mant_proveedor_liquidacion_calculo_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_calculo_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_liquidacion_calculo_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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

function conci_mant_proveedor_liquidacion_moneda_listar(){
    let select = $("[name='form_modal_conci_mant_proveedor_liquidacion_moneda_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_moneda_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_moneda_listar"
            },
        success: function (datos) {
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
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}
function conci_mant_proveedor_liquidacion_formula_tipo(){
    let select = $("[name='form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_liquidacion_formula_tipo"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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

function conci_mant_proveedor_fn_mostrarFormulasComision() {
    var selectValue = document.getElementById("form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").value;
    var selectValueTipoImportacion = document.getElementById("form_modal_conci_mant_proveedor_param_tipo_importacion_id").value;
    var proveedor_id = $('#form_modal_conci_mant_proveedor_id').val();

    $("#FormulaEscalonada").hide();
    $("#FormulaFija").hide();
    $("#FormulaMixta").hide();

    if (selectValue != '0' && proveedor_id != 0) {
        if (selectValue == "FormulaFija" || selectValue == "FormulaMixta") {
            var tipo_importacion = (selectValueTipoImportacion == "ColumnasArchivosIndependientes") ? "venta" : "combinado";
            
            var filas = document.querySelectorAll('.conci_proveedor_detalle_columna_' + tipo_importacion + ' tbody tr');
            if (filas.length > 0 && [...filas].some(fila => fila.querySelector('input[name="nombre"]').value.trim() !== '')) {
                cargarNombres(tipo_importacion);
            } else {
                alertify.error('Complete la tabla de columnas del archivo ' + tipo_importacion, 5);
                $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val(0).trigger("change.select2");
                return false;
            }

            document.querySelectorAll('.conci_proveedor_detalle_columna_' + tipo_importacion + ' tbody tr input[name="nombre"]').forEach(function(input) {
                input.addEventListener('change', function() {
                    cargarNombres(tipo_importacion);
                });
            });
        }

        $("#" + selectValue).show();
    }
}

function cargarNombres(tipo_importacion) {
    var selectFija = document.getElementById('input_text-columna-fija');
    var selectMixta = document.getElementById('input_text-columna-mixta');

    if (!selectFija || !selectMixta) {
        setTimeout(function() {
            cargarNombres(tipo_importacion);
        }, 100);
        return;
    }

    var filas = document.querySelectorAll('.conci_proveedor_detalle_columna_' + tipo_importacion + ' tbody tr');

    var nombres = [];

    filas.forEach(function(fila) {
        var nombre = fila.querySelector('input[name="nombre"]').value;
        var id = fila.querySelector('input[name="id"]').value;

        if (nombre && !nombres.some(item => item.nombre === nombre && item.id === id) && id != 0) {
            nombres.push({ nombre: nombre, id: id });
        }
    });

    selectFija.innerHTML = '';
    selectMixta.innerHTML = '';

    nombres.forEach(function(item) {
        var optionFija = document.createElement('option');
        optionFija.text = item.nombre;
        optionFija.value = item.id;
        selectFija.appendChild(optionFija);

        var optionMixta = document.createElement('option');
        optionMixta.text = item.nombre;
        optionMixta.value = item.id;
        selectMixta.appendChild(optionMixta);
    });
}




//  FOMRULAS    /////////////////////////////////////////////////////////////////////////////////////////////////

function conci_mant_proveedor_formula_fija_operador_listar(){
    let select = $("[name='conci_proveedor_formula_operador_fija']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_formula_fija_operador_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
            /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
            */
           //
           let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                //
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                    /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_formula_mixta_operador_listar(){
    let select = $("[name='conci_proveedor_formula_operador_mixta']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_formula_mixta_operador_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
            /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
            */
           //
           let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                //
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                    /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_formula_fija_opcion_listar(){
    let select = $("[name='conci_proveedor_formula_valor_fija']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_formula_fija_opcion_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
            /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
            */
           //
           let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                //
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                    /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_formula_mixta_opcion_listar(){
    let select = $("[name='conci_proveedor_formula_valor_mixta']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_formula_mixta_opcion_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
            /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
            */
           //
           let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                //
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                    /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

//  CUENTAS BANCARIAS

function conci_mant_proveedor_banco_listar(){
    let select = $("[name='conci_proveedor_cuenta_banco']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_banco_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
            /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
            */
           //
           let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                //
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                    /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_proveedor_moneda_listar(){
    let select = $("[name='conci_proveedor_cuenta_moneda']");
    //let valorSeleccionado = $("#form_modal_conci_mant_proveedor_liquidacion_tipo_formula_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento.php",
        type: "POST",
        data: {
            accion: "conci_mant_proveedor_moneda_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
            /*
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
            */
           //
           let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                //
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
                /*
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    */
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}