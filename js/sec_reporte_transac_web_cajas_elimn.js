$(".saldoweb_btn_corregir_caja").off("click").on("click", function () {
    console.log('click en saldoweb_btn_corregir_caja')
    let url = "/sys/get_reporte_diferencias_web.php";
    var dataSend = {
        accion: $(this).data("accion"),
        caja_eliminada_id: $(this).data("caja_eliminada_id"),
        nueva_caja_id: $(this).data("nueva_caja_id")
    }

    console.log(dataSend)

    $.ajax({
        url: url,
        type: 'POST',
        data: dataSend,
        cache: false,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            // loading("false");
        },
        success: function (resp) {
            console.log(resp.http_code)
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 200) {
                window.location.reload();
            } else {
                swal({
                    title: "Error al corregir transacciones.",
                    text: respuesta.error,
                    html: true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
        }
    });
})


$(".transacciones_por_caja").off("click").on("click", function () {
    console.log('click en saldoweb_btn_corregir_caja')
    let url = "/sys/get_reporte_diferencias_web.php";
    var dataSend = {
        accion: $(this).data("accion"),
        caja_eliminada_id: $(this).data("caja_eliminada_id"),
    }

    console.log(dataSend)

    $.ajax({
        url: url,
        type: 'POST',
        data: dataSend,
        cache: false,
        beforeSend: function () {
        },
        complete: function () {

        },
        success: function (resp) {
            // console.log(resp)

            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 200) {
                llenarTableTransacciones(respuesta.result);
                return true;
            }
            else {
                swal({
                    title: "Error al consultar caja",
                    text: respuesta.error,
                    html: true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
        }
    });

    function llenarTableTransacciones(transacciones) {
        console.log(transacciones)
        $("#table_body_transacciones").html("");

        transacciones.forEach(element => {
            $("#table_body_transacciones").append(`
            <tr>
            <td> `+element.swtcc_id+`</td>
            <td> `+element.local_id+`</td>
            <td> `+element.nombre+`</td>
            <td> `+element.turno_id+`</td>
            <td> `+element.monto+`</td>
            <td> `+element.caja_eliminada_id+`</td>
            <td> `+element.fecha_eliminacion+`</td>
        </tr>
            `);
            
        });

    }
})

