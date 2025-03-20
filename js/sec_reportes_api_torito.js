$(function () {
    console.log(sec_id)
    $('#SecRepApiTor_fecha_inicio').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('#SecRepApiTor_fecha_fin').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    $('#SecRepApiTor_local').select2();
    $.each(list_locales, function (index, item) {
        $('#SecRepApiTor_local').append('<option value="' + item.id + '">[' + item.id + '] ' + item.local + '</option>');
    });

    var cant_transacciones_existentes = 0;
    var cant_transacciones_faltantes = 0;
    var transacciones_faltantes = {};

    $('#btn_insertar_transacciones_faltantes').click(() => {
        Swal.fire({
            title: "Se necesita confimarción",
            text: 'Se insertará ' + transacciones_faltantes.length + " transacciones.",
            icon: "info",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            denyButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                loading(true);
                insertTransaccionesFaltantes(result.isConfirmed)

            } else if (result.isDenied) {
                Swal.fire('No se insertó nada', '', 'info')
            }
        });
    })


    $('#SecRepApiTor_btn_buscar').click(function () {
        loading(true);

        console.log($('#SecRepApiTor_fecha_fin').val())

        var fecha_inicio = $('#SecRepApiTor_fecha_inicio').val()
        var fecha_fin = '2024-01-01'; // $('#SecRepApiTor_fecha_fin').val()
        var trx_estado_bd = $('#trx_estado_bd').val()
        var local_id = $('#SecRepApiTor_local').val()
        var transaction_id = $('#SecRepApiTor_num_transaccion').val()

        var data = {
            'accion': 'get_api_transacciones',
            'fecha_inicio': fecha_inicio,
            'fecha_fin': fecha_fin,
            'trx_estado_bd': trx_estado_bd,
            'local_id': local_id,
            'transaction_id': transaction_id,
        }

        getReporteApiTorito(data);

    })

    function insertTransaccionesFaltantes(confirmed){
        if(!confirmed){
            return;
        }

        var data = {
            'accion': 'insertar_transacciones_faltantes',
            'transacciones_faltantes': JSON.stringify( transacciones_faltantes ),
        }
        console.log("se eva insertar: " + transacciones_faltantes.length)

        $.ajax({
            url: "/sys/get_reportes_api_torito.php",
            data: data,
            type: "POST",
            dataType: "json",
		}).done(function (dataresponse) {
            loading(false);
			var result = (dataresponse);
            console.log(result);

            if (result.status == 500) {
                swal({
                    title: "Ocurrió un error",
                    text: result.msg,
                    type: "error",
                    closeOnConfirm: true
                });

            } else if (result.status == 200) {
                //mostrar data
                console.log('200');
                Swal.fire({
                    title: result.title,
                    html: result.msg,
                    icon: result.icon,
                });
            }



		}).always(function (data) {
			loading();
		});

        
    }

    function getReporteApiTorito(data) {

        tabla = $("#table_reporte_api_torito").dataTable(
            {
                language: {
                    "decimal": "",
                    "emptyTable": "No existen registros",
                    "info": "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered": "(filtered from _MAX_ total entradas)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Filtrar:",
                    "zeroRecords": "Sin resultados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Ultimo",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    }
                },
                "aProcessing": true,
                "aServerSide": true,
                "ajax":
                {
                    url: "/sys/get_reportes_api_torito.php",
                    data: data,
                    type: "POST",
                    dataType: "json",
                    beforeSend: function (xhr) {
                        if (data.fecha_fin && data.fecha_inicio) {
                            // loading(true);

                        } else {
                            loading(false);
                            xhr.abort();
                            swal({
                                title: "Ocurrió un error",
                                text: 'Debe selecionar fechas',
                                type: "error",
                                closeOnConfirm: true
                            });
                        }
                    },
                    complete: function (r) {
                        debugger
                        loading(false);
                        var result = r.responseJSON
                        console.log(result)
                        if (result.status == 500) {
                            swal({
                                title: "Ocurrió un error",
                                text: result.msg,
                                type: "error",
                                closeOnConfirm: true
                            });

                        } else if (result.status == 200) {
                            $('#btn_insertar_transacciones_faltantes').hide()
                            //mostrar data
                            console.log(result.dataFaltante)
                            transacciones_faltantes = result.dataFaltante
                            $('#b_trx_existentes').html(result.aaData.length - result.dataFaltante.length)
                            $('#b_trx_faltantes').html(result.dataFaltante.length)
                            if(result.dataFaltante.length > 0){
                                $('#btn_insertar_transacciones_faltantes').show()
                            }
                        }
                    },
                    error: function (e) {
                        console.log(e.responseText);
                    }
                },
                columnDefs:
                    [
                        {
                            className: 'text-center',
                            targets: [1, 2, 3, 4, 5, 6, 7, 8]
                        },
                        {
                            width: "300px",
                            className: "text-left",
                            targets: 0
                        },
                        {
                            width: "150px", targets: 1
                        },
                        {
                            width: "200px", targets: 2
                        },
                        {
                            width: "200px", targets: 3
                        },
                        {
                            width: "10px", targets: 4
                        },
                        {
                            width: "10px", targets: 5
                        },
                        {
                            width: "10px", targets: 6
                        },
                        {
                            width: "200px", targets: 7
                        },
                        {
                            width: "100px", targets: 8
                        },
                        { "defaultContent": "-", "targets": "_all" }
                    ],
                "columns": [
                    { "aaData": "0" },
                    { "aaData": "1" },
                    { "aaData": "2" },
                    { "aaData": "3" },
                    { "aaData": "4" },
                    { "aaData": "5" },
                    { "aaData": "6" },
                    { "aaData": "7" },
                    { "aaData": "8" },
                ],
                "bDestroy": true,
                aLengthMenu: [10, 20, 30, 40, 50, 100]
            }
        ).DataTable();
    }
})