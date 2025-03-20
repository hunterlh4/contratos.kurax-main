$(document).ready(function () {
    if ($('#sec_soporte_alert_caja_web_deposit').length == 0) {
    } else {

        fncRenderizarDataTableListadoLocalesCajaWebDeposit();
    }
});
function fncRenderizarDataTableListadoLocalesCajaWebDeposit() {

    var table = $('#tbl_alert_caja_web_deposit_switch').DataTable();
    table.clear();
    table.destroy();
    var table = $('#tbl_alert_caja_web_deposit_switch').DataTable({
        'destroy': true,
        "autoWidth": false,
        "ajax": {
            type: "POST",
            async: false,
            "url": "sys/get_soporte_alert_caja_web_deposit.php",
            "data": { accion: 'sec_soporte_alert_caja_web_deposit_listar_locales' }

        },
        "fnDrawCallback": function () {
            // $(".switch").bootstrapToggle({
            //     on: "Encendido ",
            //     off: "Apagado",
            //     onstyle: "info",
            //     offstyle: "danger",
            // });
        },
        "dataSrc": function (json) {
            console.log(json);
            var result = JSON.parse(json);
            return result.data;
        },
        "columnDefs": [{
            "searchable": false,
            "orderable": false,
            "targets": 0
        }],
        "order": [
            [3, 'asc']
        ],
        "language": {
            url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
        },
        columnDefs: [{
            className: 'text-center',
            targets: [0,2,3]
        },],
        "columns": [

            {
                "data": "cc_id",
                render: function (data, type, row) {
                    var codigo = '[' + data + ']';
                    return codigo;
                }
            },
            {
                "data": "nombre"
            },
            {
                "data": "updated_at"
            },
            {
                "data": "config_param",
                render: function (data, type, row) {
                    if (data == 1) {
                        return '<span id="idSpan'+row.id+'">Encendida</span>';
                    }else{
                         return '<span id="idSpan'+row.id+'">Apagada</span>';
                    }
                    
                }
            },
            {
                "data": "config_param",
                render: function (data, type, row) {
                    var input = '';
                    if (data == 1) {
                        input = '<button class="btn btn-info" type="button" id="'+row.id+'">Apagar</button>';
                    }
                    return input;
                }
            }
        ]


    });
    $('#tbl_alert_caja_web_deposit_switch tbody').off('click');

    $('#tbl_alert_caja_web_deposit_switch tbody').on('click', '.btn', function () {

        var data = table.row($(this).parents('tr')).data();        
        var rowColor = $(this).parents('tr');
        var idRow = table.row($(this).parents('tr'));
       
        var rowData = null;
        if (data == undefined) {
            var selected_row = $(this).parents('tr');
            if (selected_row.hasClass('child')) {
                selected_row = selected_row.prev();
            }
            rowData = $('#tbl_alert_caja_web_deposit_switch').DataTable().row(selected_row).data();
        } else {
            rowData = data;
        }
        var data = new FormData();		
		data.append('accion', 'sec_soporte_alert_caja_web_deposit_cambiar_estado');
        data.append('id', rowData.id);
        var checked_estado = '';
        // $('.switchAp').change(function() {
        //     checked_estado = $(this).prop('checked');
        // })
        $.ajax({
            type: "POST",
            data: data,
            url: 'sys/get_soporte_alert_caja_web_deposit.php',
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
                
                var jsonData = JSON.parse(response);
                //console.log(jsonData.error);
                if (jsonData.error == false) {
                    swal("Apagado", jsonData.message, "success");                    
                    // if (checked_estado == true) {
                    //     checked_estado = true;
                    //     rowColor.css('background-color', '');
                    // }else{
                    //     checked_estado = false;
                    //     rowColor.css('background-color', '#FFE3C9');
                    // }
                    var idSpan_elemente = '#idSpan'+rowData.id;
                    $(idSpan_elemente).text('Apagado');
                } else {
                    swal("Error", jsonData.message, "error");
                    
                    // if (checked_estado == true) {
                    //     checked_estado = false;
                    //     rowColor.css('background-color', '#FFE3C9');
                    // }else{
                    //     checked_estado = true;
                    //     rowColor.css('background-color', '');
                    // }
                }                              
                var switch_elemente = '#'+rowData.id    
                     
                            
                $(switch_elemente).css('display','none');
            }
        });


    });

}