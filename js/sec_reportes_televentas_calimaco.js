var tabla;
$(function(){

    $("#SecRepTel_cajero_calimaco").select2();
    $("#select-cliente_id_calimaco").select2();
    $("#select-proveedor_id_calimaco").select2();

    $('#SecRepTel_fecha_inicio_calimaco').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('#SecRepTel_fecha_fin_calimaco').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    $('#SecRepTel_fecha_inicio_calimaco').val($('#g_fecha_actual_calimaco').val());
    $('#SecRepTel_fecha_fin_calimaco').val($('#g_fecha_actual_calimaco').val());

    $('#SecRepTel_fecha_inicio_calimaco').change(function () {
        var var_fecha_change = $('#SecRepTel_fecha_inicio_calimaco').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#SecRepTel_fecha_inicio_calimaco").val($("#g_fecha_actual_calimaco").val());
        }
    });
    $('#SecRepTel_fecha_fin_calimaco').change(function () {
        var var_fecha_change = $('#SecRepTel_fecha_fin_calimaco').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#SecRepTel_fecha_fin_calimaco").val($("#g_fecha_actual_calimaco").val());
        }
    });


    $('#SecRepTel_btn_buscar_calimaco').click(function() {
        //listar_SecRepTelBal_tabla_transacciones_balance();
        //listar_SecRepTelBal_tabla_transacciones_calimaco_v2();
        var table = $('#tabla_transacciones').DataTable();
        table.clear();
        table.destroy();
        listar();
    });

    $("#SecRepTel_btn_exportar_calimaco").on('click', function () {

        var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_calimaco").val());
        var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin_calimaco").val());
        //var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion_balance").val();
        var SecRepTel_cajero = $("#SecRepTel_cajero_calimaco").val();
        var SecRepTel_cliente_id = $("#select-cliente_id_calimaco").val();
        var SecRepTel_proveedor_id = $("#select-proveedor_id_calimaco").val();

        var data = {
            "accion": "listar_transacciones_calimaco_export_xls",
            "fecha_inicio": SecRepTel_fecha_inicio,
            "fecha_fin": SecRepTel_fecha_fin,
            //"tipo_transaccion": SecRepTel_tipo_transaccion,
            "cajero": SecRepTel_cajero,
            "cliente_id": SecRepTel_cliente_id,
            "proveedor_id": SecRepTel_proveedor_id
        }

        $.ajax({
            url: "/sys/get_reportes_televentas_calimaco.php",
            type: 'POST',
            data: data,
            beforeSend: function() {
                loading("true");
            },
            complete: function() {
                loading();
            },
            success: function(resp) {
                //console.log(respuesta);
                let obj = JSON.parse(resp);
                window.open(obj.path);
                loading(false);
            },
            error: function() {}
        });

    });

})

function get_SecRepTelBal_tabla_transacciones_calimaco_v2() {
    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_calimaco").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin_calimaco").val());
    //var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion_balance").val();
    var SecRepTel_cajero = $("#SecRepTel_cajero_calimaco").val();
    var SecRepTel_cliente = $("#select-cliente_id_calimaco").val();
    var SecRepTel_proveedor = $("#select-proveedor_id_calimaco").val();

    if (SecRepTel_fecha_inicio.length !== 10) {
        $("#SecRepTel_fecha_inicio_calimaco").focus();
        return false;
    }
    if (SecRepTel_fecha_fin.length !== 10) {
        $("#SecRepTel_fecha_fin_calimaco").focus();
        return false;
    }

    var data = {
        "accion": "listar_transacciones_calimaco_v2",
        "fecha_inicio": SecRepTel_fecha_inicio,
        "fecha_fin": SecRepTel_fecha_fin,
        //"tipo_transaccion": SecRepTel_tipo_transaccion,
        "cajero": SecRepTel_cajero,
        "cliente": SecRepTel_cliente,
        "proveedor": SecRepTel_proveedor
    }
    return data;
}

function listar_SecRepTelBal_tabla_transacciones_calimaco_v2() {
    var table = $('#SecRepTel_tabla_transacciones_calimaco_v2').DataTable();
    table.clear();
    table.destroy();
    var table = $('#SecRepTel_tabla_transacciones_calimaco_v2').DataTable({
        'destroy': true,
        'scrollX': true,
        "processing": true,
        "serverSide": true,
        'ordering': false,
        "ajax": {
            type: "POST",
            async : true,
            "url": "/sys/get_reportes_televentas_calimaco.php",
            "data": get_SecRepTelBal_tabla_transacciones_calimaco_v2()
        },
        // "dataSrc": function (json) {
        //     var result = JSON.parse(json);
        //     return result.result;
        // },
        "order": [],
        "language": {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "infoPostFix": "",
            "search": "Buscar: ",
            "url": "",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "columns": [
            { "data": null,
            render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
            },            
            {"data": "tipo_transaccion"},
            { "data": "telefono" },
            { "data": "tipo_doc" },
            { "data": "num_doc" },
            { "data": "web_id" },
            { "data": "cliente" },
            { "data": "cuenta" },
            { "data": "monto" },
            { "data": "bono_monto" },
            { "data": "total_recarga" },
            { "data": "cajero" },
            { "data": "supervisor" },
            { "data": "fecha_hora_registro" },
            { "data": "update_user_at" },
            { "data": "observacion_cajero" },
            { "data": "observacion_validador" },
            { "data": "observacion_supervisor" }
        ]
        
    });
    
    $(".transacciones_calimaco_v2").css("display", "");
    $('#SecRepTel_tabla_transacciones_calimaco_v2').on('init.dt', function () {
        var dtable = $("#SecRepTel_tabla_transacciones_calimaco_v2").dataTable().api();
        $(".dataTables_filter input")
            .unbind()
            .bind("input", function (e) {
                if (this.value.length >= 4 || e.keyCode == 13) {
                    dtable.search(this.value).draw();
                }
                if (this.value == "") {
                    dtable.search("").draw();
                }
                return;
            });
    });
    $('a.toggle-vis').on( 'click', function (e) {
        e.preventDefault();
    
        // Get the column API object
        var column = table.column( $(this).attr('data-column') );
    
        // Toggle the visibility
        column.visible( ! column.visible() );
    } );
   
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO - FUNCIONA
//*******************************************************************************************************************
//*******************************************************************************************************************

function listar(){
    
    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_calimaco").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin_calimaco").val());
    var SecRepTel_cajero = $("#SecRepTel_cajero_calimaco").val();
    var SecRepTel_cliente_id = $("#select-cliente_id_calimaco").val();
    var SecRepTel_proveedor_id = $("#select-proveedor_id_calimaco").val();

    var data = {
        "accion": "listar",
        "fecha_inicio": SecRepTel_fecha_inicio,
        "fecha_fin": SecRepTel_fecha_fin,
        "cajero": SecRepTel_cajero,
        "cliente_id": SecRepTel_cliente_id,
        "proveedor_id": SecRepTel_proveedor_id
    }

    $.ajax({
        url: "/sys/get_reportes_televentas_calimaco.php",
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
            console.log(respuesta);

            $("#transacciones_calimaco_v2").show();

            /* let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false); */

        if (respuesta.result.length > 0) {
            $.each(respuesta.result, function(index, item) {
                
                $('#tabla_transacciones').append(
                    '<tr>' +
                    '<td class="text-center">' + (index+1) + '</td>' +
                    '<td class="text-center">' + item.tipo_transaccion + '</td>' +
                    '<td class="text-center">' + item.cliente + '</td>'+
                    '<td class="text-center">' + item.proveedor + '</td>'+
                    '<td class="text-center">' + item.monto + '</td>'+
                    '<td class="text-center">' + item.fecha_hora_registro + '</td>'+
                    '</tr>'
                );
            });
            tabla_transacciones_datatable_formato('#tabla_transacciones');
        } else {
            $('#tabla_transacciones').append(
                '<tr>' +
                '<td colspan="9" class="text-center">NO HAY DATOS</td>' +
                '</tr>'
            );
        }

        return false;

        },
        /* error: function() {} */
    });

    //console.log(data);
}