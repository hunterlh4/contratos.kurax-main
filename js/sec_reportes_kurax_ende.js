
function sec_reportes_kurax_ende() {
    console.log("sec_reportes_pagados_en_de_otras_tiendas");
    loading(true);
    sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta();
    sec_reportes_kurax_ende_get_locales();
    $('.red_kurax_ende').select2({ closeOnSelect: false });

    $('.datep')
        .datepicker({
          //  dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        });
    loading();

    $("#btn_consultar_data_kurax_ende").off("click").on("click", function () {
        get_data_kurax_ende();
    })

    $('#red_kurax_ende').change( function (e) {
        sec_reportes_kurax_ende_get_locales();
    })
  //  setTimeout(function () { $("#btn_consultar_data_kurax_ende").click() }, 800);

}

function get_data_kurax_ende(){

    let fecha_inicio = $.format.date($('#fecha_inicio').datepicker("getDate"), "yyyy-MM-dd") 
    let fecha_fin = $.format.date($('#fecha_fin').datepicker("getDate"), "yyyy-MM-dd") 
    let locales = $('#locales_kurax_ende').val()
    let redes = $('#red_kurax_ende').val()
    let canales_venta = $('#canal_venta_kurax_ende').val()

    var data = {
        get_data_kurax_ende: {
            fecha_inicio : fecha_inicio ,
            fecha_fin : fecha_fin,
            locales: locales,
            redes: redes,
            canales_venta: canales_venta
        }
    };

    var columnDefs = [{
        className: 'text-center',
        targets: [0, 1, 2, 3, 4, 5, 6]
    }];

    var tabla = crearDataTable(
        "#tbl_data_kurax_ende",
        "/sys/get_reportes_kurax_ende.php",
        data,
        columnDefs,
        [
            'pageLength', 'excel'
        ]
    );

    tabla.on('init.dt', function () {
      //  $('.dataTables_filter').hide();

    });

}


function sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta() {
    var data = {};
    data.what = {};
    data.what[0] = "id";
    data.what[1] = "codigo";
    data.where = "canales_de_venta";
    data.filtro = {}
    data.filtro.servicio_id = "17";

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/api/?json",
    })
        .done(function (data, textStatus, jqXHR) {
            try {
                if (console && console.log) {
                    $.each(data.data, function (index, val) {
                        canales_de_venta[val.id] = val.codigo;
                        var new_option = $("<option>");
                        $(new_option).val(val.id);
                        $(new_option).html(val.codigo);
                        $(".canal_venta_kurax_ende").append(new_option);

                    });
                    $('.canal_venta_kurax_ende').select2({ closeOnSelect: false });
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
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            if (console && console.log) {
                console.log("La solicitud canales de ventas a fallado: " + textStatus);
            }
        })
}
function sec_reportes_kurax_ende_get_locales() {

    // let redes = [1,9,16,8]
    let redes_val = $('#red_kurax_ende').val()
    if(redes_val != null && redes_val.length > 0){
        redes = ( redes_val)
    }

    var data = {
        get_locales_kurax_ende: {
            redes: redes
        }
    };

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/sys/get_reportes_kurax_ende.php",
    }).done(function (data, textStatus, jqXHR) {
        try {
            if (console && console.log) {
                $(".locales_kurax_ende").html('');
                $.each(data.locales, function (index, val) {
                    var new_option = $("<option>");
                    $(new_option).val(val.id);
                    $(new_option).html(val.nombre);
                    $(".locales_kurax_ende").append(new_option);
                });
                $('.locales_kurax_ende').select2({ closeOnSelect: false });
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
            console.log("La solicitud locales a fallado: " + textStatus);
        }
    })
}


