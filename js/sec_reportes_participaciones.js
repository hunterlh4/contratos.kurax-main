var reportes_participaciones_inicio_fecha_localstorage = false;
var reportes_participaciones_fin_fecha_localstorage = false;
var $table_p = false;
function sec_reportes_participaciones() {
  console.log("sec_reportes_participaciones");
  loading(true);
  sec_reportes_participaciones_settings();
  sec_reportes_participaciones_events();
  sec_reportes_participaciones_get_canales_venta();
  sec_reportes_participaciones_locales();
}
function sec_reportes_participaciones_get_canales_venta() {
  var data = {};
  data.what = {};
  data.what[0] = "id";
  data.what[1] = "codigo";
  data.where = "canales_de_venta";
  data.filtro = {};
  auditoria_send({
    proceso: "sec_reportes_participaciones_get_canales_venta",
    data: data,
  });
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
            $(".canalventareporte_participaciones").append(new_option);
          });
          $(".canalventareporte_participaciones").select2({
            closeOnSelect: false,
          });
        }
      } catch (err) {
        swal(
          {
            title: "Error en la base de datos",
            type: "warning",
            timer: 2000,
          },
          function () {
            swal.close();
            loading();
          }
        );
      }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      if (console && console.log) {
        console.log("La solicitud canales de ventas a fallado: " + textStatus);
      }
    });
}
function sec_reportes_participaciones_locales() {
  var data = {};
  data.what = {};
  data.what[0] = "id";
  data.what[1] = "nombre";
  data.where = "locales";
  data.filtro = {};
  auditoria_send({
    proceso: "sec_reportes_participaciones_locales",
    data: data,
  });
  var local_call = $.ajax({
    data: data,
    type: "POST",
    dataType: "json",
    url: "/api/?json",
  });
  $.when(local_call)
    .done(function (data, textStatus, jqXHR) {
      try {
        if (console && console.log) {
          $.each(data.data, function (index, val) {
            array_local_nombres[val.id] = val.nombre;
            var new_option = $("<option>");
            $(new_option).val(val.id);
            $(new_option).html(val.nombre);
            $(".localreporte_participaciones").append(new_option);
          });
          $(".localreporte_participaciones").select2({ closeOnSelect: false });
        }
        sec_reportes_participaciones_get_data();
      } catch (err) {
        swal(
          {
            title: "Error en la base de datos",
            type: "warning",
            timer: 2000,
          },
          function () {
            swal.close();
            loading();
          }
        );
      }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      if (console && console.log) {
        console.log("La solicitud locales a fallado: " + textStatus);
      }
    });
}
function sec_reportes_participaciones_events() {
  $(".btn_filtrar_reporte_participaciones")
    .off()
    .on("click", function () {
      var btn = $(this).data("button");
      sec_reportes_participaciones_validacion_permisos_usuarios(btn);
    });
  $table_p = $("#tabla_reportes_participaciones");
  $table_p.floatThead({
    top: 50,
  });
  $("td").each(function () {
    var cellvalue = $(this).html();
    if (cellvalue < 0) {
      $(this).wrapInner(
        '<strong class="negative_number_reportes_participaciones"></strong>'
      );
    }
  });
  sec_reportes_participaciones_export_excel_table();
  sec_reportes_participaciones_expand_collapse_rows();
}
function sec_reportes_participaciones_settings() {
  $(".localreporte_participaciones").select2({
    closeOnSelect: false,
    allowClear: true,
  });
  $(".canalventareporte_participaciones").select2({
    closeOnSelect: false,
    allowClear: true,
  });
  $(".red_reporte_participaciones").select2({
    closeOnSelect: false,
    allowClear: true,
  });
  $(".reportes_participaciones_datepicker")
    .datepicker({
      dateFormat: "dd-mm-yy",
      changeMonth: true,
      changeYear: true,
    })
    .on("change", function (ev) {
      $(this).datepicker("hide");
      var newDate = $(this).datepicker("getDate");
      $("input[data-real-date=" + $(this).attr("id") + "]").val(
        $.format.date(newDate, "yyyy-MM-dd")
      );
    });

  reportes_participaciones_inicio_fecha_localstorage = localStorage.getItem(
    "reportes_participaciones_inicio_fecha_localstorage"
  );
  if (reportes_participaciones_inicio_fecha_localstorage) {
    var reportes_participaciones_inicio_fecha_localstorage_new = moment(
      reportes_participaciones_inicio_fecha_localstorage
    ).format("DD-MM-YYYY");
    $("#input_text-reportes_participaciones_inicio_fecha")
      .datepicker(
        "setDate",
        reportes_participaciones_inicio_fecha_localstorage_new
      )
      .trigger("change");
  }

  reportes_participaciones_fin_fecha_localstorage = localStorage.getItem(
    "reportes_participaciones_fin_fecha_localstorage"
  );
  if (reportes_participaciones_fin_fecha_localstorage) {
    var reportes_participaciones_fin_fecha_localstorage_new = moment(
      reportes_participaciones_fin_fecha_localstorage
    ).format("DD-MM-YYYY");
    $("#input_text-reportes_participaciones_fin_fecha")
      .datepicker(
        "setDate",
        reportes_participaciones_fin_fecha_localstorage_new
      )
      .trigger("change");
  }
}
function sec_reportes_participaciones_get_data() {
  var get_participaciones_data = {};
  get_participaciones_data.where = "reporte_participaciones";
  get_participaciones_data.filtro = {};
  get_participaciones_data.filtro.fecha_inicio = $(
    ".reportes_participaciones_inicio_fecha"
  ).val();
  get_participaciones_data.filtro.fecha_fin = $(
    ".reportes_participaciones_fin_fecha"
  ).val();
  get_participaciones_data.filtro.locales = $(
    ".localreporte_participaciones"
  ).val();
  get_participaciones_data.filtro.canales_de_venta = $(
    ".canalventareporte_participaciones"
  ).val();
  get_participaciones_data.filtro.red_id = $(
    ".red_reporte_participaciones"
  ).val();
  localStorage.setItem(
    "reportes_participaciones_inicio_fecha_localstorage",
    get_participaciones_data.filtro.fecha_inicio
  );
  localStorage.setItem(
    "reportes_participaciones_fin_fecha_localstorage",
    get_participaciones_data.filtro.fecha_fin
  );
  auditoria_send({
    proceso: "sec_reportes_participaciones_get_data",
    data: get_participaciones_data,
  });
  $.ajax({
    url: "/api/?json",
    type: "POST",
    data: get_participaciones_data,
  })
    .done(function (dataresponse) {
      try {
        var obj = JSON.parse(dataresponse);
        console.log(obj);
        sec_reportes_participaciones_create_table(obj);
      } catch (err) {
        swal(
          {
            title: "Error en la base de datos",
            type: "warning",
            timer: 2000,
          },
          function () {
            swal.close();
            loading();
          }
        );
      }
    })
    .fail(function () {
      console.log("error");
    });
}
function sec_reportes_participaciones_create_table(obj) {
  var nombre_mes = [
    "",
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Setiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ];
  var cols = {};
  cols["anio_part"] = "&nbsp;&nbsp;&nbsp;Año&nbsp;&nbsp;&nbsp;";
  cols["mes_part"] = "&nbsp;&nbsp;&nbsp;Mes&nbsp;&nbsp;&nbsp;";
  cols["rango_fecha_part"] = "&nbsp;&nbsp;&nbsp;Fecha&nbsp;&nbsp;&nbsp;";
  cols["fecha_emision_part"] = "Fecha Emision&nbsp;&nbsp;";
  cols["fecha_vencimiento_part"] = "&nbsp;Fecha Vencimiento&nbsp;";
  cols["dias_de_atraso_part"] = "Dias de Atraso&nbsp;";
  cols["ejecutivo_de_ventas_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ejecutivo de Ventas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["antiguedad_part"] = "&nbsp;&nbsp;&nbsp;Antiguedad&nbsp;&nbsp;&nbsp;";
  cols["cliente_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cliente&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["punto_venta_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Punto de Venta&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["canal_de_venta_part"] =
    "&nbsp;&nbsp;Canal&nbsp;de&nbsp;Venta&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["tipo_de_punto_part"] =
    "&nbsp;&nbsp;Tipo&nbsp;de&nbsp;Punto&nbsp;&nbsp;&nbsp;";
  cols["qty_part"] = "QTY";
  cols["porcentaje_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["total_depositado_part"] = "&nbsp;&nbsp;Total &nbsp;Depositado&nbsp;";
  cols["anulado_retirado_part"] =
    "&nbsp;&nbsp;&nbsp;Anulado/Retirado&nbsp;&nbsp;&nbsp;";
  cols["total_apostado_part"] =
    "&nbsp;&nbsp;&nbsp;Total&nbsp;Apostado&nbsp;&nbsp;&nbsp;";
  cols["tk_pagados_en_su_punto_part"] =
    "&nbsp;&nbsp;&nbsp;Tk &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pagado&nbsp;&nbsp;&nbsp; en su &nbsp;&nbsp;&nbsp;&nbsp;Punto&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["tk_pagado_en_otro_punto_part"] =
    "&nbsp;&nbsp;&nbsp;Tk &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pagado &nbsp;&nbsp;&nbsp;&nbsp;en otro &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Punto&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["total_premiados_pagados_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;Total&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Premios&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Pagados&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["resultado_del_negocio_part"] =
    "&nbsp;&nbsp;Resultado&nbsp;&nbsp; del&nbsp;&nbsp; Negocio&nbsp;&nbsp;";
  cols["caja_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Caja&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["depositado_web_part"] =
    "&nbsp;&nbsp;Depositado&nbsp;&nbsp; Web&nbsp;&nbsp;";
  cols["retirado_web_part"] =
    "&nbsp;&nbsp;Retirado&nbsp;&nbsp; Web&nbsp;&nbsp;";
  cols["tk_pagado_de_otro_punto_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tk&nbsp;&nbsp;&nbsp; Pagado de&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Otro&nbsp;punto&nbsp;&nbsp;&nbsp;";
  cols["participacion_cliente_part"] = "Participación&nbsp;&nbsp; Cliente";
  cols["participacion_free_games_part"] =
    "Participacion&nbsp;&nbsp; Free Games";
  cols["difer_web_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;Differ.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Web&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["difer_tickets_part"] =
    "&nbsp;&nbsp;&nbsp;Difer.&nbsp;Tickets&nbsp;&nbsp;&nbsp;";
  cols["abonado_participacion_part"] =
    "&nbsp;&nbsp;Abonado Participación&nbsp;&nbsp;";
  cols["abonado_web_part"] = "&nbsp;&nbsp;Abonado&nbsp; Web&nbsp;&nbsp;";
  cols["factor_de_redondeo_part"] =
    "&nbsp;&nbsp;Factor de &nbsp;&nbsp;Redondeo&nbsp;&nbsp;";
  cols["total_a_pagar_part"] =
    "&nbsp;&nbsp;&nbsp;&nbsp;Total&nbsp;&nbsp; a &nbsp;&nbsp;&nbsp;Pagar&nbsp;&nbsp;&nbsp;&nbsp;";
  cols["comision_apostado_part"] =
    "&nbsp;&nbsp;Comision&nbsp;&nbsp;&nbsp;&nbsp; Apostado&nbsp;&nbsp;";
  cols["comision_web_part"] =
    "&nbsp;&nbsp;Comision&nbsp;&nbsp;&nbsp; Web&nbsp;&nbsp;";

  var html =
    "<table class='tabla_reportes_participaciones' id='tabla_reportes_participaciones' width='100%' cellspacing='0'>";
  html +=
    '<thead style="background-color: #C0C0C0 !important; color: #333 !important;">';
  html += "<tr>";
  html +=
    '<th id="th_boton"><button class="all_parent_participaciones btn_expand_collapse_rows_participaciones"><span class="glyphicon glyphicon-pushpin"></span></button></th>';
  $.each(cols, function (index_cols, val_cols) {
    html +=
      '<th id="th_' +
      index_cols +
      '" class="' +
      index_cols +
      '">' +
      val_cols +
      "</th>";
  });
  html += "<tr>";
  html += "</thead><tbody>";
  var ejecutivo_de_ventas_participaciones = "";
  $.each(obj.data, function (anio, val_anio) {
    $.each(val_anio, function (mes, val_mes) {
      $.each(val_mes, function (rango, val_rango) {
        $.each(val_rango, function (local, val_local) {
          if (val_local.ejecutivo_de_ventas == null) {
            ejecutivo_de_ventas_participaciones =
              "<span style='color:red;'>No asignado</span>";
          } else {
            ejecutivo_de_ventas_participaciones = val_local.ejecutivo_de_ventas;
          }

          html +=
            '<tr class="rows_hidden_participaciones children_row_collapse_expand_participaciones_' +
            mes +
            ' children_participaciones" >';
          html +=
            '<td class="sec_reportes_participaciones_button"><button class="parent_participaciones btn_expand_collapse_rows_participaciones" data-id="' +
            mes +
            '" ><span class="glyphicon glyphicon-pushpin"></span></button></td>';
          html +=
            '<td class="sec_reportes_participaciones_anio">' +
            validate_null_number(anio) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_mes">' +
            nombre_mes[validate_null_number(parseInt(mes))] +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_rango">' +
            validate_null_number(val_local.fecha) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_fecha_emision">' +
            validate_null_number(val_local.fecha_emision) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_fecha_vencimiento">' +
            validate_null_number(val_local.fecha_vencimiento) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_dias_de_atraso">' +
            validate_null_number(val_local.dias_de_atraso) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_ejecutivo_de_ventas">' +
            ejecutivo_de_ventas_participaciones +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_antiguedad">' +
            validate_null_number(val_local.antiguedad) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_cliente">' +
            validate_null_string(val_local.cliente) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_punto_de_venta">' +
            validate_null_string(val_local.punto_de_venta) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_canal_de_venta">' +
            validate_null_string(val_local.canal_de_venta) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_tipo_de_punto">' +
            validate_null_string(val_local.tipo_de_punto) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_qty">' +
            validate_null_number(val_local.qty) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_porcentaje">' +
            validate_null_number(val_local.porcentaje) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_depositado">' +
            validate_null_number(val_local.total_depositado) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_anulado_retirado">' +
            validate_null_number(val_local.total_anulado_retirado) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_apostado">' +
            validate_null_number(val_local.total_apostado) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_pagados_en_su_punto">' +
            validate_null_number(val_local.total_pagados_en_su_punto) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_pagado_en_otro_punto">' +
            validate_null_number(val_local.total_pagado_en_otro_punto) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_premios_pagados">' +
            validate_null_number(val_local.total_premios_pagados) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_resultado_del_negocio">' +
            validate_null_number(val_local.resultado_del_negocio) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_caja">' +
            validate_null_number(val_local.caja) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_depositado_web">' +
            validate_null_number(val_local.total_depositado_web) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_retirado_web">' +
            validate_null_number(val_local.total_retirado_web) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_pagado_de_otro_punto">' +
            validate_null_number(val_local.total_pagado_de_otro_punto) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_participacion_cliente">' +
            validate_null_number(val_local.participacion_cliente) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_participacion_freegames">' +
            validate_null_number(val_local.participacion_freegames) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_difer_web">' +
            validate_null_number(val_local.difer_web) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_difer_tickets">' +
            validate_null_number(val_local.difer_tickets) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_abonado_participacion">' +
            validate_null_number(val_local.abonado_participacion) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_abonado_web">' +
            validate_null_number(val_local.abonado_web) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_factor_de_redondeo">' +
            validate_null_number(val_local.factor_de_redondeo) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_total_a_pagar">' +
            validate_null_number(val_local.total_a_pagar) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_comision_apostado">' +
            validate_null_number(val_local.comision_apostado) +
            "</td>";
          html +=
            '<td class="sec_reportes_participaciones_comision_web">' +
            validate_null_number(val_local.comision_web) +
            "</td>";

          html += "</tr>";
        });
      });
      html += '<tr class="row_mes_collapse_expand">';
      html +=
        '<td class="sec_reportes_participaciones_button"><button class="parent_participaciones btn_expand_collapse_rows_participaciones" data-id="' +
        mes +
        '"><span class="glyphicon glyphicon-pushpin"></span></button></td>';
      html +=
        '<td class="sec_reportes_participaciones_anio">' +
        validate_null_number(anio) +
        "</td>";
      html +=
        '<td class="sec_reportes_participaciones_mes" >' +
        nombre_mes[validate_null_number(parseInt(mes))] +
        "</td>";
      html += '<td colspan="35"></td>';
      html += "</tr>";
    });
  });

  html += '<tr class="row_mes_collapse_expand_all">';
  html +=
    '<th id="th_boton_all_bottom"><button class="all_parent_participaciones btn_expand_collapse_rows_participaciones_all"><span class="glyphicon glyphicon-pushpin"></span></button></th>';
  html += '<td class="sec_reportes_participaciones_anio"></td>';
  html += '<td class="sec_reportes_participaciones_mes" ></td>';
  html += '<td colspan="35"></td>';
  html += "</tr>";

  html += "</tbody><tfoot><tr>";
  html += "</tr></tfoot>";
  html += "</table>";
  $(".tabla_contenedor_reportes_participaciones").html(html);
  sec_reportes_participaciones_events();
  loading();
}
function sec_reportes_participaciones_expand_collapse_rows() {
  $(".all_parent_participaciones")
    .off()
    .on("click", function () {
      if (
        $(".children_participaciones").hasClass("rows_expanded_participaciones")
      ) {
        $(".children_participaciones").hide();
        $(".children_participaciones")
          .removeClass("rows_expanded_participaciones")
          .addClass("rows_hidden_participaciones");
      } else {
        $(".children_participaciones").show();
        $(".children_participaciones")
          .removeClass("rows_hidden_participaciones")
          .addClass("rows_expanded_participaciones");
      }
    });
  $(".parent_participaciones")
    .off()
    .on("click", function () {
      var id_row_children = $(this).data("id");
      if (
        $(
          ".children_row_collapse_expand_participaciones_" + id_row_children
        ).hasClass("rows_hidden_participaciones")
      ) {
        $(".children_row_collapse_expand_participaciones_" + id_row_children)
          .toggle()
          .removeClass("rows_hidden_participaciones")
          .addClass("rows_expanded_participaciones");
      } else {
        $(".children_row_collapse_expand_participaciones_" + id_row_children)
          .toggle()
          .removeClass("rows_expanded_participaciones")
          .addClass("rows_hidden_participaciones");
      }
    });
}
function sec_reportes_participaciones_validacion_permisos_usuarios(btn) {
  console.log("btn_filtrar_resumen_dia:click");
  $(document).on("evento_validar_permiso_usuario", function (event) {
    $(document).off("evento_validar_permiso_usuario");
    console.log("EVENT: evento_validar_permiso_usuario");
    if (event.event_data == true) {
      loading(true);
      sec_reportes_participaciones_get_data();
    } else {
      console.log(event.event_data);
      event.preventDefault();
      swal(
        {
          title: "No tienes permisos",
          type: "info",
          timer: 2000,
        },
        function () {
          swal.close();
        }
      );
    }
  });
  validar_permiso_usuario(btn, sec_id, sub_sec_id);
}
function sec_reportes_ejecutar_participaciones(type, fn) {
  return sec_reportes_export_table_to_excel_participaciones(
    "tabla_reportes_participaciones",
    type || "xlsx",
    fn
  );
}
function sec_reportes_validar_exportacion_participaciones(s) {
  if (typeof ArrayBuffer !== "undefined") {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xff;
    return buf;
  } else {
    var buf = new Array(s.length);
    for (var i = 0; i != s.length; ++i) buf[i] = s.charCodeAt(i) & 0xff;
    return buf;
  }
}
function sec_reportes_export_table_to_excel_participaciones(id, type, fn) {
  var wb = XLSX.utils.table_to_book(
    document.getElementById(id),
    { raw: true },
    { sheet: "Sheet JS" }
  );
  var wbout = XLSX.write(wb, { bookType: type, bookSST: true, type: "binary" });
  var fname = fn || "tabla_reportes_participaciones." + type;
  try {
    saveAs(
      new Blob([sec_reportes_validar_exportacion_participaciones(wbout)], {
        type: "application/octet-stream",
      }),
      fname
    );
  } catch (e) {
    if (typeof console != "undefined") console.log(e, wbout);
  }
  return wbout;
}
function sec_reportes_get_table_to_export_participaciones(
  pid,
  iid,
  fmt,
  ofile
) {
  if (fallback) {
    if (document.getElementById(iid))
      document.getElementById(iid).hidden = true;
    Downloadify.create(pid, {
      swf: "media/downloadify.swf",
      downloadImage: "download.png",
      width: 100,
      height: 30,
      filename: ofile,
      data: function () {
        var o = sec_reportes_ejecutar_reporte_participaciones(fmt, ofile);
        return window.btoa(o);
      },
      transparent: false,
      append: false,
      dataType: "base64",
      onComplete: function () {
        alert("Your File Has Been Saved!");
      },
      onCancel: function () {
        alert("You have cancelled the saving of this file.");
      },
      onError: function () {
        alert(
          "You must put something in the File Contents or there will be nothing to save!"
        );
      },
    });
  }
}

function sec_reportes_participaciones_export_excel_table() {
  $(".btn_export_participaciones_xlsx")
    .off()
    .on("click", function () {
      event.preventDefault();
      var buton = $(this);
      var data = Object();
      data.filtro = Object();
      data.where = "validar_usuario_permiso_botones";
      $(".input_text_validacion").each(function (index, el) {
        data.filtro[$(el).attr("data-col")] = $(el).val();
      });
      data.filtro.text_btn = buton.data("button");
      auditoria_send({
        proceso: "validar_usuario_permiso_botones reporte participaciones",
        data: data,
      });
      $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/api/?json",
      })
        .done(function (dataresponse) {
          try {
            console.log(dataresponse);
            if (dataresponse.permisos == true) {
              var reinit = $table_p.floatThead("destroy");
              sec_reportes_ejecutar_participaciones("xlsx");
              sec_reportes_get_table_to_export_participaciones(
                "xlsxbtn",
                "xportxlsx",
                "xlsx",
                "reporte_participaciones.xlsx"
              );
              reinit();
            } else {
              swal(
                {
                  title: "No tienes permisos",
                  type: "info",
                  timer: 2000,
                },
                function () {
                  swal.close();
                }
              );
            }
          } catch (err) {
            swal(
              {
                title: "Error en la base de datos",
                type: "warning",
                timer: 2000,
              },
              function () {
                swal.close();
                loading();
              }
            );
          }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          if (console && console.log) {
            console.log(
              "La solicitud validar permisos exportar xlsx a fallado: " +
                textStatus
            );
          }
        });
    });
  $(".btn_export_participaciones_xls")
    .off()
    .on("click", function () {
      event.preventDefault();
      var buton = $(this);
      var data = Object();
      data.filtro = Object();
      data.where = "validar_usuario_permiso_botones";
      $(".input_text_validacion").each(function (index, el) {
        data.filtro[$(el).attr("data-col")] = $(el).val();
      });
      data.filtro.text_btn = buton.data("button");
      auditoria_send({
        proceso: "validar_usuario_permiso_botones",
        data: data,
      });
      $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/api/?json",
      })
        .done(function (dataresponse) {
          try {
            console.log(dataresponse);
            if (dataresponse.permisos == true) {
              var reinit = $table_p.floatThead("destroy");
              sec_reportes_ejecutar_participaciones(
                "biff2",
                "reporte_participaciones.xls"
              );
              sec_reportes_get_table_to_export_participaciones(
                "biff2btn",
                "xportbiff2",
                "biff2",
                "reporte_participaciones.xls"
              );
              reinit();
            } else {
              swal(
                {
                  title: "No tienes permisos",
                  type: "info",
                  timer: 2000,
                },
                function () {
                  swal.close();
                }
              );
            }
          } catch (err) {
            swal(
              {
                title: "Error en la base de datos",
                type: "warning",
                timer: 2000,
              },
              function () {
                swal.close();
                loading();
              }
            );
          }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          if (console && console.log) {
            console.log(
              "La solicitud validar permisos exportar xls a fallado: " +
                textStatus
            );
          }
        });
    });
}
