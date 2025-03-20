function ObtenerEmails() {
  $("#nombre_reporte option:selected").each(function () {
    let nombre_reporte = $("#nombre_reporte").val();
    if (nombre_reporte == "") {
      return false;
    }
    var data = {
      accion: "obtener_emails_segun_reporte",
      departamento_id: nombre_reporte,
    };
    var array_provincias = [];
    // auditoria_send({ "proceso": "obtener_provincias_segun_departamento", "data": data });
    $.ajax({
      url: "/sys/get_reportes_conf_reportes.php",
      type: "POST",
      data: data,
      beforeSend: function () {
        loading("true");
      },
      complete: function () {
        loading();
      },
      success: function (resp) {
        //  alert(datat)
        var respuesta = JSON.parse(resp);
        if (parseInt(respuesta.http_code) == 400) {
        }

        if (parseInt(respuesta.http_code) == 200) {
          array_provincias.push(respuesta.result);

          var html = '<option value="">- TODOS -</option>';

          for (var i = 0; i < array_provincias[0].length; i++) {
            html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
          }

          $("#search_id_provincia").html(html).trigger("change");

          setTimeout(function () {
            $("#search_id_provincia").select2("open");
          }, 500);

          return false;
        }
      },
      error: function () {},
    });
  });
}
