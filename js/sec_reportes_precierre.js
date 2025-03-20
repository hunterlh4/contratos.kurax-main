const sec_reportes_precierre = () => {
    console.log('precierreeeee')
    sec_reportes_precierre_get_locales();


    $('#sec_reportes_precierre_buscar_precierres').click(function (e) {
        sec_reportes_precierre_buscar()
    })
}

function sec_reportes_precierre_buscar() {
    var fecha_inicio = $('#sec_reportes_precierre_fecha_inicio').val();
    var fecha_fin = $('#sec_reportes_precierre_fecha_fin').val();
    var local_id = $('#sec_reportes_precierre_local_id').val();

    var data = {
        buscar_precierre: {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            local_id: local_id
        }
    };

    var columnDefs = [{
        className: 'text-center',
        targets: [0, 1, 2, 3, 4, 5, 6, 7, 10]
    }];
    
    var tabla = crearDataTable(
        "#sec_reportes_precierre_table_data",
        "/sys/get_reportes_precierre.php",
        data,
        columnDefs,
        [
            { extend: 'pageLength' },
            { extend: 'excel' }
        ]
    );

    tabla.on('init.dt', function () {
        //  $('.dataTables_filter').hide();
    });

}

function sec_reportes_precierre_get_locales() {
    let redes_val = $('#').val()
    let redes = '_all_'
    if (redes_val != null && redes_val.length > 0) {
        redes = (redes_val)
    }

    var data = {
        reportes_precierre_get_locales: {
            redes: redes
        }
    };

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/sys/get_reportes_precierre.php",
        beforeSend: function () {
            loading("true");
        },
        complete: function (data) {
            loading();
        },
        success: function (response, status) {
            console.log(response)
        },
        always: function (data) {
            loading();
        }
    }).done(function (data, textStatus, jqXHR) {

        try {
            if (console && console.log) {
                $("#sec_reportes_precierre_local_id").html('');
                var new_option = $("<option>");
                $(new_option).val('');
                $(new_option).html(' - Todos - ');
                $("#sec_reportes_precierre_local_id").append(new_option);
                $.each(data.locales, function (index, val) {
                    var new_option = $("<option>");
                    $(new_option).val(val.id);
                    $(new_option).html('[' + val.id + ']' + val.nombre);
                    $("#sec_reportes_precierre_local_id").append(new_option);
                });
                $('#sec_reportes_precierre_local_id').select2({ closeOnSelect: true });
            }
        } catch (err) {
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function () {
                swal.close();
                loading();
            });
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (console && console.log) {
            console.log("La solicitud locales a fallado: " + textStatus + " Response: " + jqXHR.responseText);
        }
    })
}