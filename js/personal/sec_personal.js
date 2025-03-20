var get_data_personal = {};

function sec_personal() {
  if (sec_id == "personal") {
    console.log("sec_personal");
    sec_personal_settings();
    sec_personal_events();

    $("#select-razon_social_id").on("change", function () {
      get_obtener_empresa();
    });
  }
  if (sub_sec_id == "log_personal") {
    console.log("sub_sec_log_personal");
    sub_sec_log_personal();
  }
}

function sub_sec_log_personal() {
  $(".save_btn").hide();
  $(".del_btn").hide();
  $("#btnActivos").hide();
  $("#title-text").html("Historial de Personal");
  getPersonalAuditoria(item_id);

  $("#btn_consultar_log_personal").on("click", function () {
    getPersonalAuditoria(item_id);
  });
}

function getPersonalAuditoria(personal_id) {
  var set_data = {};
  set_data["sec_personal_get_log_personal"] = "sec_personal_get_log_personal";
  set_data["personal_id"] = personal_id;
  set_data["fecha_inicio"] = $("#log_personal_fecha_inicio").val();
  set_data["fecha_fin"] = $("#log_personal_fecha_fin").val();

  $.ajax({
    url: "sys/get_personal.php",
    type: "POST",
    data: set_data,
  })
    .done(function (data) {
      var respuesta = JSON.parse(data);

      printPersonalAuditoria(respuesta.auditoria);
      $("#log_de_personal").html(respuesta.personal.nombre_completo);
      // $("#btn_contrasena_user").show();
    })
    .fail(function (e) {
      console.log(e);
    });

  // $(function () {
  // 	$('#datetimepicker1').datetimepicker();
  // });
}
// TABLA PERSONAL
function printPersonalAuditoria(data) {
  $("#table_tbl_personal_auditoria")
    .dataTable({
      bDestroy: true,
      data: data,
      responsive: true,
      order: [[0, "desc"]],
      pageLength: 15,
      lengthMenu: [
        [10, 15, 25, 50, 100, -1],
        [10, 15, 25, 50, 100, "Todos"],
      ],
      columns: [
        { data: "fecha", className: "text-center" },
        { data: "updated_by_nombre", className: "text-left" },
        { data: "campo", className: "text-left" },
        { data: "valor_anterior_relacionado", className: "text-left" },
        { data: "valor_relacionado", className: "text-left" },
        { data: "ip", className: "text-left" },
      ],
      columnDefs: [
        {
          render: function (data, type, row, meta) {
            if (data == "Activo") {
              return '<span style="color:' + "green" + '">' + data + "</span>";
            } else if (data == "Inactivo") {
              return '<span style="color:' + "red" + '">' + data + "</span>";
            } else {
              return data;
            }
          },
          targets: [3, 4],
        },
        {
          visible: true,
          targets: [3, 4],
        },
      ],
      language: {
        decimal: "",
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
          pageLength: {
            _: "Mostrar %d Resultados",
            "-1": "Tout afficher",
          },
        },
      },
      scrollY: true,
      scrollX: true,
      dom: "Bfrtip",
      buttons: [
        "pageLength",
        {
          extend: "excelHtml5",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          title: "Log de personal",
        },
      ],
    })
    .DataTable();
}
// FILTRAR TABLA
function filter_table_personal(page) {
  var get_data = {};
  var limit = $("#cbLimit option:selected").val();
  get_data.page = page;
  get_data.limit = limit;
  get_data.filter = $("#txtSearchUser").val();
  if ($("#btnActivos").hasClass("btn-success")) get_data.inactivo = true;
  $.post(
    "/sys/get_personal.php",
    { sec_personal_get_tabla_usuarios: get_data },
    function (response) {
      result = JSON.parse(response);
      $("#tbl_personal").html(result.body);
      $(".switch").bootstrapToggle({
        on: "activo",
        off: "inactivo",
        onstyle: "success",
        offstyle: "danger",
        size: "mini",
      });
      $(".toggle")
        .off()
        .on("click", function (event) {
          if (typeof $(this).find(".switch").data().ignore === "undefined")
            $(this).find(".switch").bootstrapToggle("toggle");
        });

      $(".switch")
        .off()
        .on("change", function (event) {
          switch_data($(event.target));
        });

      $("#pagination").pagination({
        items: result.num_rows,
        currentPage: page + 1,
        itemsOnPage: limit,
        cssStyle: "light-theme",
        onPageClick: function (pageNumber, event) {
          event.preventDefault();
          filter_table_personal(pageNumber - 1);
        },
      });
    }
  );
}

function sec_personal_settings() {}

function sec_personal_events() {
  $(".toggle")
    .off()
    .on("click", function (event) {
      event.preventDefault();
    });
  $(
    "#select-area_id,#select-cargo_id, #select-zona_id, #select-razon_social_id"
  )
    .attr("style", "width:100%")
    .select2();
  $(".btn_editar_personal")
    .off()
    .on("click", function (event) {
      event.preventDefault();

      var buton = $(this);
      var data = Object();
      data.filtro = Object();
      data.where = "validar_usuario_permiso_botones";

      console.log("valido permisos");
      $(".input_text_validacion").each(function (index, el) {
        data.filtro[$(el).attr("data-col")] = $(el).val();
      });
      data.filtro.text_btn = buton.data("button");
      console.log(data);
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
              window.location.href = buton.data("href");
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
              "La solicitud validar permisos personal ver a fallado: " +
                textStatus
            );
          }
        });
    });
  // $('#tbl_personal').DataTable({
  //     bSort : false,
  //     sScrollY: true,
  //     sScrollX: true,
  //     scrollCollapse:true,
  //     sPaginationType: "full_numbers",
  //     lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
  //     dom: 'Blftip',
  //     buttons: [
  //         {
  //             extend: 'copy',
  //             text:'Copiar',
  //             className: 'sec_personal_copiarButton'
  //         },
  //         {
  //             extend: 'csv',
  //             text:'CSV',
  //             className: 'sec_personal_csvButton'
  //             ,filename: $(".export_personal_filename").val()
  //         },
  //         {
  //             extend: 'excel',
  //             text:'Excel',
  //             className: 'sec_personal_excelButton'
  //             ,filename: $(".export_personal_filename").val()
  //         },
  //     ],
  //       columnDefs: [
  //             { className: "tbl_personal_columnas","targets": [0,1,2,3,4,8,9,10]},
  //             { className: "tbl_personal_columnas_numeros","targets": [5,6,7]}
  //       ],
  //     language:{
  //         "decimal":        ".",
  //         "thousands":      ",",
  //         "emptyTable":     "Tabla vacia",
  //         "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
  //         "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
  //         "infoFiltered":   "(filtered from _MAX_ total entradas)",
  //         "infoPostFix":    "",
  //         "thousands":      ",",
  //         "lengthMenu":     "Mostrar _MENU_ entradas",
  //         "loadingRecords": "Cargando...",
  //         "processing":     "Procesando...",
  //         "search":         "Filtrar:",
  //         "zeroRecords":    "Sin resultados",
  //         "paginate": {
  //             "first":      "Primero",
  //             "last":       "Ultimo",
  //             "next":       "Siguiente",
  //             "previous":   "Anterior"
  //         },
  //         "aria": {
  //             "sortAscending":  ": activate to sort column ascending",
  //             "sortDescending": ": activate to sort column descending"
  //         },
  //         "buttons": {
  //             "copyTitle": 'Contenido Copiado',
  //             "copySuccess": {
  //                 _: '%d filas copiadas',
  //                 1: '1 fila copiada'
  //             }
  //         }
  //     }
  //     ,"order": [[ 0, 'desc' ]]
  // });

  $("#varchar_correo")
    .off()
    .on("change", function () {
      validar_email_personal();
    });

  $("#varchar_dni")
    .off()
    .on("change", function () {
      validar_dni_personal();
    });

  $("#varchar_nombre")
    .off()
    .on("change", function () {
      validar_nombre_personal();
    });
  $("#varchar_apellido_paterno")
    .off()
    .on("change", function () {
      validar_ap_personal();
    });
  $("#varchar_apellido_materno")
    .off()
    .on("change", function () {
      validar_am_personal();
    });

  $("#varchar_telefono")
    .off()
    .on("change", function () {
      validar_telefono_personal();
    });
  $("#varchar_celular")
    .off()
    .on("change", function () {
      validar_celular_personal();
    });

  if ($("#tbl_personal").length > 0) filter_table_personal(0);

  $("#txtSearchUser").on("keyup", function (event) {
    filter_table_personal(0);
    /* Act on the event */
  });

  $("#cbLimit").on("change", function (event) {
    filter_table_personal(0);
  });

  $("#btnActivos").on("click", function (event) {
    event.preventDefault();
    if ($(this).hasClass("btn-default")) {
      $(this).removeClass("btn-default");
      $(this).addClass("btn-success");
      $(this).text("Mostrar Activos");
    } else {
      $(this).removeClass("btn-success");
      $(this).addClass("btn-default");
      $(this).text("Mostrar Inactivos");
    }
    filter_table_personal(0);
  });

  $(".personal_import_btn")
    .off()
    .click(function (event) {
      $("#personal_import_modal").modal("show");
    });

  $(".info_to_view_import")
    .off()
    .click(function (event) {
      $("#personal_formatos_modal").modal("show");
    });

  $(".massive_download_btn")
    .off()
    .click(function (event) {
      $("#massive_download_btn").modal("show");
    });

  $(".user_to_dni_btn")
    .off()
    .click(function (event) {
      $("#modal_user_to_dni").modal("show");
    });

  $("#dni_to_search_submit").on("click", function (event) {
    event.preventDefault();
    console.log("Iniciando: búsqueda de usuarios por dni");
    console.log($("#dni_to_search_textarea").val());
    swal({
      title: "Buscando...",
      text: "Espere hasta que se descargue el archivo Excel con la información",
      type: "info",
      closeOnConfirm: true,
    });
    document.getElementById("form_dni_to_search").submit();
  });

  $("#modal_user_to_dni").on("hidden.bs.modal", function (e) {
    $("#dni_to_search_textarea").val("");
  });

  $("#file_personal_import")
    .off()
    .on("change", function () {
      document.getElementById("label_name_File").innerHTML =
        document.getElementById("file_personal_import").files[0].name;
      $("#personal_import_btn_upload").prop("disabled", false);
    });

  $("#personal_import_btn_upload")
    .off()
    .click(function (event) {
      get_data_personal.nombre_archivo = document.getElementById(
        "file_personal_import"
      ).files[0].name;
      get_data_personal.usuario = $(".user-name").text();
      auditoria_send({
        proceso: "sec_personal_iniciando_importacion_masiva",
        data: get_data_personal,
      });
      console.log("Iniciando: importación");
      document.getElementById("personal_import_form").submit();
      swal({
        title: "Guardando...",
        text: "El archivo ya ha sido cargado, espere y verifique el archivo Excel que se está descargando para ver los resultados",
        type: "info",
        closeOnConfirm: true,
      });
      document.getElementById("file_personal_import").value = "";
      document.getElementById("label_name_File").innerHTML = "";
      $("#personal_import_btn_upload").prop("disabled", true);
    });

  $("#personal_import_modal").on("hidden.bs.modal", function (e) {
    document.getElementById("file_personal_import").value = "";
    document.getElementById("label_name_File").innerHTML = "";
    $("#personal_import_btn_upload").prop("disabled", true);
  });

  $(".download_personal_btn")
    .off()
    .click(function (event) {
      $("#personal_download_modal").modal("show");
    });

  $(".filter_donwload_data").select2();

  $("#personal_download_btn").on("click", function (event) {
    event.preventDefault();
    swal({
      title: "Descargando...",
      text: "Espere hasta que se descargue el archivo Excel con la información",
      type: "info",
      closeOnConfirm: true,
    });
    document.getElementById("personal_download_form").submit();
  });
}

function validar_nombre_personal() {
  var name = $("#varchar_nombre").val();
  if (name.search(/^[a-zA-Z\s]*$/) == -1) {
    $("#varchar_nombre")
      .focus()
      .closest(".form-group")
      .removeClass("has-success")
      .addClass("has-error");
  } else {
    $("#varchar_nombre")
      .closest(".form-group")
      .removeClass("has-error")
      .addClass("has-success");
  }
}

function validar_ap_personal() {
  var ap = $("#varchar_apellido_paterno").val();
  if (ap.search(/^[a-zA-Z\s]*$/) == -1) {
    $("#varchar_apellido_paterno")
      .focus()
      .closest(".form-group")
      .removeClass("has-success")
      .addClass("has-error");
  } else {
    $("#varchar_apellido_paterno")
      .closest(".form-group")
      .removeClass("has-error")
      .addClass("has-success");
  }
}

function validar_am_personal() {
  var am = $("#varchar_apellido_materno").val();
  if (am.search(/^[a-zA-Z\s]*$/) == -1) {
    $("#varchar_apellido_materno")
      .focus()
      .closest(".form-group")
      .removeClass("has-success")
      .addClass("has-error");
  } else {
    $("#varchar_apellido_materno")
      .closest(".form-group")
      .removeClass("has-error")
      .addClass("has-success");
  }
}

function validar_dni_personal() {
  var dni = $("#varchar_dni").val();
  var set_data = {};
  set_data["dni"] = dni;
  if (dni.search(/^\d{8}$/) != -1) {
    $("#varchar_dni")
      .closest(".form-group")
      .removeClass("has-error")
      .addClass("has-success");
    swal(
      {
        title: "Validando",
        text: "Estamos verificando que el DNI no esté duplicado.",
        type: "info",
        closeOnConfirm: true,
      },
      function (isConfirm) {
        loading(true);
        if (isConfirm) {
          $.post(
            "/sys/get_personal.php",
            {
              sec_personal_validar_dni: set_data,
            },
            function (data) {
              let result = JSON.parse(data);
              let existe = result.flag_dni;
              if (existe.trim() == "existe") {
                loading(false);
                $("#varchar_dni")
                  .focus()
                  .closest(".form-group")
                  .removeClass("has-success")
                  .addClass("has-error");
                let estado = "x";
                switch (result.personal.estado) {
                  case "1":
                    estado = "activo";
                    break;
                  case "0":
                    estado = "inactivo";
                    break;
                  default:
                    break;
                }
                swal({
                  title: "DNI duplicado",
                  text:
                    "DNI ya registrado, " +
                    result.personal.nombre_completo +
                    " Estado: " +
                    estado +
                    ".",
                  type: "error",
                  closeOnConfirm: true,
                });
              } else if (existe.trim() == "no_existe") {
                loading(false);
                $("#varchar_dni")
                  .closest(".form-group")
                  .removeClass("has-error")
                  .addClass("has-success");
                swal({
                  title: "DNI correcto",
                  text: "El DNI especificado puede ser utilizado",
                  type: "success",
                  closeOnConfirm: true,
                });
              }
            }
          );
        }
      }
    );
  } else {
    $("#varchar_dni")
      .focus()
      .closest(".form-group")
      .removeClass("has-success")
      .addClass("has-error");
  }
}

function validar_telefono_personal() {
  var t = $("#varchar_telefono").val();
  if (t.search(/^\d+$/) == -1) {
    $("#varchar_telefono")
      .focus()
      .closest(".form-group")
      .removeClass("has-success")
      .addClass("has-error");
  } else {
    $("#varchar_telefono")
      .closest(".form-group")
      .removeClass("has-error")
      .addClass("has-success");
  }
}

function validar_celular_personal() {
  var c = $("#varchar_celular").val();
  if (c.search(/^\d+$/) != -1) {
    $("#varchar_celular")
      .closest(".form-group")
      .removeClass("has-error")
      .addClass("has-success");
  } else {
    $("#varchar_celular")
      .focus()
      .closest(".form-group")
      .removeClass("has-success")
      .addClass("has-error");
  }
}

function validar_email_personal(mail) {
  var email = $("#varchar_correo").val();
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
    $("#varchar_correo")
      .closest(".form-group")
      .removeClass("has-error")
      .addClass("has-success");
  } else {
    $("#varchar_correo")
      .focus()
      .closest(".form-group")
      .removeClass("has-success")
      .addClass("has-error");
  }
}

function get_obtener_empresa() {
  var set_data = {};
  set_data["obtener_zonas_por_empresa"] = "obtener_zonas_por_empresa";
  set_data["razon_social_id"] = $("#select-razon_social_id").val();

  $.ajax({
    url: "sys/get_personal.php",
    type: "POST",
    data: set_data,
  })
    .done(function (data) {
      var respuesta = JSON.parse(data);
      $("#select-zona_id").find("option").remove().end();
      $("#select-zona_id").append('<option value="0">- Seleccione -</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $("#select-zona_id").append(opcion);
      });
    })
    .fail(function (e) {
      console.log(e);
    });

  // $(function () {
  // 	$('#datetimepicker1').datetimepicker();
  // });
}
