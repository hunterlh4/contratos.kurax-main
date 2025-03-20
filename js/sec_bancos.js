function sec_bancos() {
  if (sec_id == "bancos") {
    console.log("sec_bancos");
    sec_bancos_events();
    sec_bancos_settings();
  }
}
// tabla
function sec_bancos_events() {
  var table_clientes = $("#bancos_list").DataTable({
    sScrollY: false,
    sScrollX: false,
    sPaginationType: "full_numbers",
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "Todos"],
    ],
    bSort: false,
    sScrollY: true,
    sScrollX: false,
    sScrollXInner: false,
    bScrollCollapse: false,
    dom: "Blftip",
    buttons: [
      {
        extend: "copy",
        text: "Copiar",
        className: "sec_bancos_copiarButton",
      },
      {
        extend: "csv",
        text: "CSV",
        className: "sec_bancos_csvButton",
        filename: $(".export_bancos_filename").val(),
      },
      {
        extend: "excel",
        text: "Excel",
        className: "sec_bancos_excelButton",
        filename: $(".export_bancos_filename").val(),
      },
      {
        extend: "colvis",
        text: "Visibilidad",
        className: "sec_bancos_visibilidadButton",
        postfixButtons: ["colvisRestore"],
      },
    ],
    language: {
      decimal: ".",
      thousands: ",",
      emptyTable: "Tabla vacia",
      info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
      infoEmpty: "Mostrando 0 a 0 de 0 entradas",
      infoFiltered: "(filtered from _MAX_ total entradas)",
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
        copyTitle: "Contenido Copiado",
        copySuccess: {
          _: "%d filas copiadas",
          1: "1 fila copiada",
        },
      },
    },
    order: [[0, "desc"]],
  });
  $(".save_btn_bancos")
    .off()
    .on("click", function () {
      loading(true);
      sec_bancos_save_item($(this));
    });
  // BOTON EDITAR (tabla) (alguien lo hizo con errores)
  // $(".btn_editar_bancos")
  //   .off()
  //   .on("click", function (event) {
  //     event.preventDefault();

  //     var buton = $(this);
  //     var data = Object();
  //     data.filtro = Object();
  //     data.where = "validar_usuario_permiso_botones";
  //     $(".input_text_validacion").each(function (index, el) {
  //       data.filtro[$(el).attr("data-col")] = $(el).val();
  //     });
  //     data.filtro.text_btn = buton.data("button");

  //     console.log("Datos enviados en AJAX:", JSON.stringify(data, null, 2));
  //     console.log(data);
  //     auditoria_send({
  //       proceso: "validar_usuario_permiso_botones",
  //       data: data,
  //     });
  //     $.ajax({
  //       data: data,
  //       type: "POST",
  //       dataType: "json",
  //       url: "/api/?json",
  //     })
  //       .done(function (dataresponse) {
  //         try {
  //           console.log("respuesta del servidor", dataresponse);
  //           if (dataresponse.permisos == true) {
  //             window.location.href = buton.data("href");
  //             console.log("Tienes permisos 1");
  //           } else {
  //             console.log("No tienes permisos 1");
  //             swal(
  //               {
  //                 title: "No tienes permisos",
  //                 type: "info",
  //                 timer: 2000,
  //               },
  //               function () {
  //                 swal.close();
  //               }
  //             );
  //           }
  //         } catch (err) {
  //           swal(
  //             {
  //               title: "Error en la base de datos",
  //               type: "warning",
  //               timer: 2000,
  //             },
  //             function () {
  //               swal.close();
  //             }
  //           );
  //         }
  //       })
  //       .fail(function (jqXHR, textStatus, errorThrown) {
  //         if (console && console.log) {
  //           console.log(
  //             "La solicitud validar permisos bancos ver a fallado: " +
  //               textStatus
  //           );
  //         }

  //         // console.log("Respuesta del servidor:", jqXHR.responseText);
  //         // console.log("Error:", textStatus, errorThrown);
  //       });
  //   });
}
// colores

function sec_bancos_settings() {
  $("#input_text_color_hexadecimal").minicolors({
    control: $(this).attr("data-control") || "hue",
    defaultValue: $(this).attr("data-defaultValue") || "",
    format: $(this).attr("data-format") || "hex",
    keywords: $(this).attr("data-keywords") || "",
    inline: $(this).attr("data-inline") === "true",
    letterCase: $(this).attr("data-letterCase") || "lowercase",
    opacity: $(this).attr("data-opacity"),
    position: $(this).attr("data-position") || "bottom right",
    swatches: $(this).attr("data-swatches")
      ? $(this).attr("data-swatches").split("|")
      : [],
    change: function (value, opacity) {
      if (!value) return;
      if (opacity) value += ", " + opacity;
      if (typeof console === "object") {
      }
    },
    theme: "bootstrap",
  });
  if ($("#input_text_color_hexadecimal").val()) {
    var ipt = $("#input_text_color_hexadecimal").val().split("#")[1];
    $("#input_text_color_hexadecimal").val(ipt);
  }
}

// guardar
function sec_bancos_save_item(btn) {
  var save_data = Object();
  $(".save_data").each(function (index, el) {
    save_data[$(el).attr("data-col")] = $(el).val();
  });
  save_data.values = Object();
  $(".input_text").each(function (index, el) {
    save_data.values[$(el).attr("data-col")] = $(el).val();
  });
  save_data.validacion = Object();
  $(".input_text_validacion").each(function (index, el) {
    save_data.validacion[$(el).attr("data-col")] = $(el).val();
  });
  save_data.validacion.text_btn = btn.data("button");
  $(".switch").each(function (index, el) {
    if ($(el).prop("checked")) {
      save_data.values[$(el).attr("data-col")] = $(el).attr("data-on-value");
    } else {
      save_data.values[$(el).attr("data-col")] = $(el).attr("data-off-value");
    }
  });

  auditoria_send({ proceso: "sec_bancos_save_item", data: save_data });
  $.post(
    "sys/set_data.php",
    {
      opt: "save_item",
      data: save_data,
    },
    function (r, textStatus, xhr) {
      console.log("save_item_bancos:ready");
      loading();
      try {
        var response = jQuery.parseJSON(r);
        console.log(response);
        console.log(response.permisos);
        if (response.permisos == true) {
          swal(
            {
              title: "Guardado",
              text: "",
              type: "success",
              timer: 300,
              closeOnConfirm: false,
            },
            function () {
              console.log(btn.data("then"));
              if (btn.data("then") == "reload") {
                if (save_data["id"] == "new") {
                  save_data.id = response.item_id;
                  auditoria_send({
                    proceso: "add_item",
                    data: save_data,
                  });
                  window.location =
                    "./?sec_id=" +
                    sec_id +
                    "&sub_sec_id=" +
                    sub_sec_id +
                    "&item_id=" +
                    response.item_id;
                } else {
                  auditoria_send({
                    proceso: "save_item",
                    data: save_data,
                  });
                  swal.close();
                  m_reload();
                }
              } else if (btn.data("then") == "force_reload") {
                auditoria_send({
                  proceso: "save_item",
                  data: save_data,
                });
                m_reload();
              } else if (btn.data("then") == "exit") {
                auditoria_send({
                  proceso: "save_item",
                  data: save_data,
                });
                window.location =
                  "./?sec_id=" + sec_id + "&sub_sec_id=" + sub_sec_id;
              } else {
              }
              swal.close();
            }
          );
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
        console.log(r);
        swal(
          {
            title: "Error en la base de datos",
            type: "warning",
            timer: 2000,
          },
          function () {
            swal.close();
          }
        );
      }
    }
  );
}
