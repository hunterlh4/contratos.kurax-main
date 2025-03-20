function sec_usuarios() {
  console.log("js/usuario/usuario");
  if (sec_id == "usuarios") {
    console.log("sec:usuarios");
    sec_usuarios_events();
    sec_usuarios_permisos();
    ajax_get_permissions_user();
  }
  sec_usuarios_global_events();
}

function sec_usuarios_events() {
  $(document).on(
    "click",
    ".btn_permisos_ususarios_usuario_selecionado_table",
    function () {
      var id_perm = $(this).data("id");
      var user_perm = $(this).data("user");
      var nombre_perm = $(this).data("nombre") + " " + $(this).data("apellido");
      var sistema_perm = $(this).data("sistema");
      var grupo_perm = "";
      sec_usuarios_btn_permisos(
        id_perm,
        user_perm,
        nombre_perm,
        sistema_perm,
        grupo_perm
      );
    }
  );

  $(".btn_asignar_permisos_multiple_usuarios").on("click", function (event) {
    $("#modal_permisos_usuarios_multiples").modal("show");
    $(".sec_usuarios_permisos_filtros").show();
    $("#sec_usuarios_info").hide();
    sec_permisos_desmarcar_locales();
    sec_grupo_desmarcar_permisos_checkbox();
  });

  $(".btn_cerrar_usuarios_dni").on("click", function (event) {
    $("#search_dni_textarea").val("");
    $("#modal_search_dni").modal("show");
  });

  $(document).on("click", ".btn_dismiss_dni", function (event) {
    event.preventDefault();

    var search_dni_textarea = $("#search_dni_textarea").val();
    var search_dni = search_dni_textarea.replace(/ /g, "");

    if (search_dni == "") {
      swal(
        {
          title: "No hay datos",
          text: "Ingrese datos en la caja de texto para iniciar la búsqueda",
          type: "warning",
          timer: 4000,
        },
        function () {
          swal.close();
        }
      );
    } else {
      swal(
        {
          title: "¡ATENCIÓN!",
          text: "¿Está seguro(a) que desea desactivar estos DNI?",
          type: "warning",
          showCancelButton: true,
          confirmButtonText: "Si",
          cancelButtonText: "No",
          closeOnConfirm: true,
        },
        function () {
          console.log(
            "Iniciando: Desactivación de personal y usuarios por DNI"
          );
          var search_dni_textarea = $("#search_dni_textarea").val();
          var search_dni = search_dni_textarea.replace(/ /g, "");

          var data = { search_dni_textarea: search_dni };

          $.ajax({
            type: "POST",
            url: "sys/set_usuarios_cerrar_masivo.php",
            data: data,
          }).done(function (res) {
            var obj = jQuery.parseJSON(res);
            console.log(obj);

            var mensaje_error = "";
            if (obj.error_dni) {
              mensaje_error =
                mensaje_error +
                " -> Los siguientes DNI no se encontraron en la Base de Datos: \n" +
                obj.error_dni +
                "\n\n";
            }
            if (obj.error_personal) {
              mensaje_error =
                mensaje_error +
                "-> El siguiente PERSONAL ya fue desactivado anteriormente: \n" +
                obj.error_personal +
                "\n\n";
            }
            if (obj.error_usuario) {
              mensaje_error =
                mensaje_error +
                "-> Los siguientes USUARIOS ya fueron desactivados anteriormente: \n" +
                obj.error_usuario +
                "\n\n";
            }

            if (mensaje_error != "") {
              swal({
                title: "Atención",
                text:
                  mensaje_error +
                  "Los demás registros fueron desactivados correctamente.",
                type: "warning",
              });
            } else {
              swal(
                {
                  title: "Listo",
                  text: "Se desactivaron todos los usuarios indicados correctamente",
                  type: "success",
                  timer: 4000,
                },
                function () {
                  swal.close();
                }
              );
            }

            /*
					let today = new Date();
					let dd = String(today.getDate()).padStart(2, '0');
					let mm = String(today.getMonth() + 1).padStart(2, '0');
					let yyyy = today.getFullYear();
					today = `${mm}_${dd}_${yyyy}`;
					var blob = res;
					var downloadUrl = URL.createObjectURL(blob);
					var a = document.createElement("a");
					a.href = downloadUrl;
					a.download = `usuarios_cerrados_${today}.xlsx`;
					a.target = '_blank';
					a.click();
					$("#loader").html("");
					*/
          });
        }
      );
    }
  });

  $(document).on("click", ".btn_descarga_usuarios_activos", function (event) {
    event.preventDefault();

    swal(
      {
        title: "¡ATENCIÓN!",
        text: "¿Está seguro(a) de descargar un listado de todos los Usuarios Activos?",
        type: "info",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: true,
      },
      function () {
        console.log("Iniciando: Descarga de Usuarios Activos");

        set_data = { opt: "descarga_usuarios_activos" };
        loading(true);

        $.ajax({
          type: "POST",
          url: "sys/get_usuarios_descarga.php",
          data: set_data,
          xhrFields: {
            responseType: "blob",
          },
        }).done(function (res) {
          loading(false);

          swal({
            title: "¡Éxito!",
            text: "El archivo se ha descargado correctamente",
            type: "success",
          });

          let today = new Date();
          let dd = String(today.getDate()).padStart(2, "0");
          let mm = String(today.getMonth() + 1).padStart(2, "0");
          let yyyy = today.getFullYear();
          today = `${mm}_${dd}_${yyyy}`;
          var blob = res;
          var downloadUrl = URL.createObjectURL(blob);
          var a = document.createElement("a");
          a.href = downloadUrl;
          a.download = `usuarios_activos-${today}.xls`;
          a.target = "_blank";
          a.click();
        });
      }
    );
  });

  $(document).on("click", "#btn_search_dni", function (event) {
    event.preventDefault();
    $("#dt_users_to_close").DataTable().destroy();
    console.log("Iniciando: Buscando usuarios por DNI");
    var search_dni_textarea = $("#search_dni_textarea").val();
    var search_dni = search_dni_textarea.replace(/ /g, "");

    if (search_dni == "") {
      swal(
        {
          title: "No hay datos",
          text: "Ingrese datos en la caja de texto para iniciar la búsqueda",
          type: "warning",
          timer: 4000,
        },
        function () {
          swal.close();
        }
      );
    } else {
      var data = { search_dni_textarea: search_dni };

      $("#dt_users_to_close").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "sys/set_usuarios.php?action=busca_usuarios_dni",
          type: "POST",
          data: data,
        },
        columns: [
          { data: "0" },
          { data: "1" },
          { data: "2" },
          { data: "3" },
          { data: "4" },
          { data: "5" },
          { data: "6" },
          { data: "7" },
          { data: "8" },
          { data: "9" },
          { data: "10" },
        ],
      });

      $("#modal_users_to_close").modal("show");
    }
  });

  $("#btn_crear_grupo_usuarios").on("click", function (event) {
    //$("#btnCancelarGrupo").trigger("click");
    ajax_get_groups_user();
    $("#modal_grupo_user").modal("show");
  });

  $(".save_btn")
    .off()
    .on()
    .click(function (event) {
      let btn = $(this);
      sec_usuarios_save(btn);
    });

  $(".usuario_restaurar_pass_btn")
    .off()
    .click(function (event) {
      event.preventDefault();
      sec_usuarios_restore_password();
    });

  $("#button_change_password").on("click", function (event) {
    event.preventDefault();
    sec_usuarios_change_pass();
  });

  if ($("#tbl_usuarios").length > 0) sec_usuarios_filter_table(0);

  $("#txtSearchUser").on("keyup", function (event) {
    sec_usuarios_filter_table(0);
  });

  $("#cbLimit").on("change", function (event) {
    sec_usuarios_filter_table(0);
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
    sec_usuarios_filter_table(0);
  });

  $(".select2").select2({
    closeOnSelect: true,
    width: "100%",
  });

  $("#btn_new_user").on("click", function () {
    $(".save_data[name=type_user]").val("new");
    $(".save_data[name=usuario]").val("");
    $(".save_data[name=personal_id]").val("0");
    $("[name=personal_id]").select2().trigger("change");
    $(".save_data[name=sistema_id]").val("0");
    $("[name=sistema_id]").select2().trigger("change");
    $(".save_data[name=grupo_id]").val("0");
    $("[name=grupo_id]").select2().trigger("change");
    if ($("#switch_estado").val() == 0) {
      $("#switch_estado").bootstrapToggle("on");
    }
    $("#save_data_contador_caracteres").html("0");
    if ($("#switch_ip_restrict").val() == 1) {
      $("#switch_ip_restrict").bootstrapToggle("off");
    }
    if ($("#switch_validacion_2fa").val() == 1) {
      $("#switch_validacion_2fa").bootstrapToggle("off");
    }
    $("#switch_estado").attr("data-id", "");
    $("#switch_ip_restrict").attr("data-id", "");
    $("#switch_validacion_2fa").attr("data-id", "");
    //se llamaria a jquery ajax
    sec_usuarios_btns_user_new();
    ajax_get_all_personal();
  });

  $(".return_btn").on("click", function () {
    $("#title-text").html("Usuarios");
    sec_usuarios_btns_list_user();
  });

  $(".btn_permisos_usuario_seleccionado").on("click", function () {
    var id_perm = $(".save_data[name=id]").val();
    var user_perm = $(".save_data[name=usuario]").val();
    var nombre_perm = $(".save_data[name=personal_id]").select2("data");
    var sistema_perm = $(".save_data[name=sistema_id]").select2("data");
    var grupo_perm = $(".save_data[name=grupo_id]").select2("data");
    sec_usuarios_btn_permisos(
      id_perm,
      user_perm,
      nombre_perm[0].text,
      sistema_perm[0].text,
      grupo_perm[0].text
    );
  });

  $("#local_id").select2();

  $("#btnPersonal_Inactivo").on("click", function (event) {
    event.preventDefault();
    if ($(this).hasClass("btn-danger")) {
      $(this).removeClass("btn-danger");
      $(this).addClass("btn-info");
      $(this).text("Quitar filtro");
    } else {
      $(this).removeClass("btn-info");
      $(this).addClass("btn-danger");
      $(this).text("Filtrar por Personal inactivo");
    }
    sec_usuarios_filter_table(0);
  });

  $(".btn_importacion_validacion_2fa")
    .on()
    .click(function (event) {
      $("#importar_validacion_2fa_modal").modal("show");
    });

  $("#file_imp_2fa")
    .off()
    .on("change", function () {
      document.getElementById("label_name_File_2fa").innerHTML =
        document.getElementById("file_imp_2fa").files[0].name;
      $("#btn_imp_2fa").prop("disabled", false);
    });

  $("#btn_imp_2fa")
    .off()
    .on()
    .click(function (event) {
      let btn = $(this);
      sec_usuarios_import_2fa(btn);
    });

  $("#importar_validacion_2fa_modal").on("hidden.bs.modal", function (e) {
    document.getElementById("file_imp_2fa").value = "";
    document.getElementById("label_name_File_2fa").innerHTML = "";
    $("#btn_imp_2fa").prop("disabled", true);
  });
}

function sec_usuarios_btn_permisos(
  user_id,
  user_user,
  user_nombre,
  user_sistema,
  user_grupo
) {
  $("#modal_permisos_usuarios_multiples").modal("show");
  $("#select_permisos_selected").val(user_id);
  $(".valor_usuario_id").val(user_id);
  $(".sec_usuarios_permisos_filtros").hide();
  $("#sec_usuarios_info").show();
  var texto_info =
    "[" +
    user_id +
    "] - " +
    user_user +
    " - " +
    user_nombre +
    " - " +
    user_sistema +
    " - " +
    user_grupo;
  $("#sec_usuarios_info").text(texto_info);
  // Estas 2 funciones de modal_check se encuentran en sec_usuarios_permisos.js; éstos marcan los checkbox correspondientes
  sec_permisos_modal_check_locales(user_id);
  sec_permisos_modal_check_permisos(user_id);
  sec_permisos_modal_check_redes(user_id);
}

function sec_usuarios_global_events() {
  $(".user_change_pass_btn")
    .off()
    .click(function (event) {
      sec_usuarios_change_pass_modal(true);
    });
  let usuario_password_changed = $(".usuario_password_changed").val();
  if (usuario_password_changed != 1) {
    auditoria_send({ proceso: "sec_usuario_change_password_forced" });
    $(".user_change_pass_btn").click();
  }

  $("#btn_cashier_select_location")
    .off()
    .click(function (event) {
      sec_cashier_select_location_modal(true);
    });

  if ($("#btn_cashier_select_location").length === 0) {
    sec_cashier_select_location_modal(true);
  }

  let usuario_local_id = $("#usuario_local_id").val();
  if (usuario_local_id === "") {
    auditoria_send({ proceso: "sec_cashier_select_location" });
    $("#btn_cashier_select_location").click();
  }
}

function sec_usuarios_filter_table(page) {
  var get_data = {};
  var limit = $("#cbLimit option:selected").val();
  get_data.page = page;
  get_data.limit = limit;
  get_data.filter = $("#txtSearchUser").val();
  if ($("#btnActivos").hasClass("btn-success")) get_data.inactivo = true;
  if ($("#btnPersonal_Inactivo").hasClass("btn-info"))
    get_data.p_inactivo = true;
  $.post(
    "/sys/get_usuarios.php",
    { sec_usuarios_get_tabla_usuarios: get_data },
    function (response) {
      result = JSON.parse(response);

      $("#tbl_usuarios").html(result.body);
      $(".switch").bootstrapToggle({
        on: "Activo",
        off: "Inactivo",
        onstyle: "success",
        offstyle: "danger",
        size: "mini",
      });
      $(".toggle").on("click", function (event) {
        if (typeof $(this).find(".switch").data().ignore === "undefined") {
        }
      });

      $(".switch")
        .off()
        .on("change", function (event) {
          switch_data($(event.target));
        });

      $(".switch-table").change(function () {
        var set_data = {};
        id_switch_to_change = $(this).attr("id");
        set_data.id_switch = $(this).data("id");
        set_data.usuario_id = $(this).data("id");
        set_data.usuario = document.getElementById(
          "hid_usuario_" + set_data.usuario_id
        ).value;
        if (!$(this).prop("checked")) {
          swal(
            {
              title: "¡ATENCIÓN!",
              text: '<span style="color:black;" >Si desactivas este usuario, el personal y demás usuarios asociados serán dados de baja también</span>',
              html: true,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Dar de baja",
              cancelButtonText: "Cancelar",
              closeOnConfirm: false,
              closeOnCancel: false,
            },
            function (isConfirm) {
              if (isConfirm) {
                loading(true);
                $.ajax({
                  url: "sys/set_usuarios.php?action=dismiss_user",
                  type: "POST",
                  data: set_data,
                })
                  .done(function (data) {
                    var respuesta = JSON.parse(data);
                    auditoria_send({
                      proceso: "sec_usuarios_dismiss",
                      data: set_data,
                    });
                    loading(false);
                    swal(
                      "INACTIVO",
                      "El personal y usuarios asociados fueron dados de baja",
                      "success"
                    );
                  })
                  .fail(function (e) {
                    console.log(e);
                  });
              } else {
                $("#" + id_switch_to_change + "").bootstrapToggle("on");
                swal("No desactivado", "El usuario continuará activo", "info");
              }
            }
          );
        } else {
          swal(
            {
              title: "¡ATENCIÓN!",
              text: '<span style="color:black;" >Si activas este usuario, el personal asociado será activado también</span>',
              html: true,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Activar",
              cancelButtonText: "Cancelar",
              closeOnConfirm: false,
              closeOnCancel: false,
            },
            function (isConfirm) {
              if (isConfirm) {
                loading(true);
                $.ajax({
                  url: "sys/set_usuarios.php?action=activate_user",
                  type: "POST",
                  data: set_data,
                })
                  .done(function (data) {
                    var respuesta = JSON.parse(data);
                    auditoria_send({
                      proceso: "sec_usuarios_activate",
                      data: set_data,
                    });
                    loading(false);
                    swal(
                      "ACTIVO",
                      "El usuario y personal asociados fueron activados",
                      "success"
                    );
                  })
                  .fail(function (e) {
                    console.log(e);
                  });
              } else {
                $("#" + id_switch_to_change + "").bootstrapToggle("off");
                swal(
                  "No activado",
                  "El usuario continuará desactivado",
                  "info"
                );
              }
            }
          );
        }
      });

      $("#pagination").pagination({
        items: result.num_rows,
        currentPage: page + 1,
        itemsOnPage: limit,
        cssStyle: "light-theme",
        onPageClick: function (pageNumber, event) {
          event.preventDefault();
          sec_usuarios_filter_table(pageNumber - 1);
        },
      });
    }
  );
}

function sec_usuarios_btns_user_new() {
  $(".btn_permisos_usuario_seleccionado").hide();
  $("#btn_contrasena_user").hide();
  $("#btns_list_user").hide();
  $("#btns_opt_user").show();
  $("#opc_user").show();
  $("#opc_tabla_usuario").hide();
}
function sec_usuarios_btns_user_edit() {
  $(".btn_permisos_usuario_seleccionado").hide();
  $("#btn_contrasena_user").hide();
  $("#btns_list_user").hide();
  $("#btns_opt_user").show();
  $("#opc_user").show();
  $("#opc_tabla_usuario").hide();
}

function sec_usuarios_btns_list_user() {
  $("#btns_list_user").show();
  $("#btns_opt_user").hide();
  $("#opc_user").hide();
  $("#opc_tabla_usuario").show();
  $("#opc_tbl_permisos_auditoria").hide();
}

function sec_usuarios_save(btn) {
  loading(true);
  var set_data = {};
  $(".save_data").each(function (index, el) {
    set_data[$(el).attr("name")] = $(el).val();
  });
  // Obtenemos el valor del switch estado
  if ($("#switch_estado").is(":checked")) {
    set_data["estado"] = $("#switch_estado").attr("data-on-value");
  } else {
    set_data["estado"] = $("#switch_estado").attr("data-off-value");
  }
  // Obtenemos el valor del switch ip restrict
  if ($("#switch_ip_restrict").is(":checked")) {
    set_data["ip_restrict"] = $("#switch_ip_restrict").attr("data-on-value");
  } else {
    set_data["ip_restrict"] = $("#switch_ip_restrict").attr("data-off-value");
  }
  // Obtenemos el valor del switch validacion 2fa
  if ($("#switch_validacion_2fa").is(":checked")) {
    set_data["validacion_2fa"] = $("#switch_validacion_2fa").attr(
      "data-on-value"
    );
  } else {
    set_data["validacion_2fa"] = $("#switch_validacion_2fa").attr(
      "data-off-value"
    );
  }

  $.ajax({
    url: "sys/set_usuarios.php?action=new_user",
    type: "POST",
    data: set_data,
  })
    .done(function (data) {
      var respuesta = JSON.parse(data);
      console.log(respuesta);
      loading(false);
      if (respuesta.error == "exists") {
        set_data.error = respuesta.error;
        set_data.error_msg = respuesta.error_msg;
        auditoria_send({ proceso: "sec_usuarios_save_error", data: set_data });
        swal({
          title: "Nombre de usuario ya existe",
          text: "Escoja otro nombre de usuario",
          type: "info",
          closeOnConfirm: true,
        });
      } else if (respuesta.error == "invalid_user") {
        set_data.error = respuesta.error;
        set_data.error_msg = respuesta.error_msg;
        auditoria_send({ proceso: "sec_usuarios_save_error", data: set_data });
        swal({
          title: respuesta.error_msg,
          text: "Ingrese un usuario con el formato y caracteres permitidos. Ejemplo: nombre.apellido",
          type: "info",
          closeOnConfirm: true,
        });
      } else {
        set_data.curr_login = respuesta.curr_login;
        set_data.user_type_status = respuesta.user_type_status;
        auditoria_send({ proceso: "sec_usuarios_save_done", data: set_data });
        if (respuesta.user_type_status == "new_success") {
          swal({
            title: "Usuario creado correctamente",
            text: "El nuevo usuario ha sido creado satisfactoriamente",
            type: "success",
          });
          $(".save_data[name=type_user]").val("update");
          $(".save_data[name=id]").val(respuesta.id);
          $("#switch_estado").attr("data-id", respuesta.id);
          $("#switch_ip_restrict").attr("data-id", respuesta.id);
          $("#switch_validacion_2fa").attr("data-id", respuesta.id);
          $(".btn_permisos_usuario_seleccionado").show();
          $("#btn_contrasena_user").show();
        } else if (respuesta.user_type_status == "update_user") {
          swal({
            title: "Usuario actualizado correctamente",
            text: "La información del usuario ha sido actualizada correctamente",
            type: "success",
          });
        } else {
          swal({
            title: "¡Error!",
            text: "La información del usuario no se ha podido crear/guardar correctamente",
            type: "warning",
          });
        }
        if (btn.data("then") == "exit") {
          sec_usuarios_btns_list_user();
        }
      }
    })
    .fail(function (e) {
      console.log(e);
    });
}

function sec_usuarios_restore_password() {
  swal(
    {
      title: "¿Está seguro de crear/restaurar la contraseña?",
      text: '<input type="text" id="txtPersonalEmail" name="txtPersonalEmail" class="form-control" placeholder="Email para enviar password" style="display:block;">',
      html: true,
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: false,
    },
    function () {
      loading(true);
      var get_data = {};
      $(".save_data[name=id]").each(function (index, el) {
        get_data[$(el).attr("name")] = $(el).val();
      });
      get_data["correo"] = $('[id="txtPersonalEmail"]').val();

      $.post(
        "/sys/set_usuarios.php",
        {
          sec_usuarios_restore_password: get_data,
        },
        function (data) {
          loading(false);
          try {
            var respuesta = jQuery.parseJSON(data);
            get_data.new_password = respuesta.new_password;
            console.log(respuesta);
            swal(
              {
                title: "Contraseña generada correctamente",
                text: "La nueva contraseña es: " + respuesta.new_password,
                type: "success",
                timer: 360000,
                closeOnConfirm: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
              },
              function () {
                swal.close();
              }
            );
          } catch (error) {
            console.log(data);
            console.log(error);
            swal({
              title: "error",
              text: "No se pudo generar/restaurar contraseña",
              type: "danger",
            });
          }
        }
      );
      auditoria_send({
        proceso: "sec_usuarios_restore_password",
        data: get_data,
      });
    }
  );
}

function sec_usuarios_tbl_editar_usuario(id) {
  //se obtiene x ajax a todo el personal
  ajax_get_all_personal();

  var set_data = {};
  set_data["id"] = id;
  sec_usuarios_btns_user_edit();

  $.ajax({
    url: "sys/set_usuarios.php?action=info_user",
    type: "POST",
    data: set_data,
  })
    .done(function (data) {
      $(".btn_permisos_usuario_seleccionado").show();
      $("#btn_contrasena_user").show();

      var respuesta = JSON.parse(data);
      $(".save_data[name=id]").val(respuesta.id);
      $(".save_data[name=type_user]").val("update");
      $("input[name=usuario]").val(respuesta.usuario);
      $("#save_data_contador_caracteres").html(respuesta.usuario.length);
      $("#personal_id").val(respuesta.personal_id);
      $("#personal_id").select2().trigger("change");
      $("#personal_id").closest(".form-group").hide();
      $("[name=sistema_id]").val(respuesta.sistema_id);
      $("[name=sistema_id]").select2().trigger("change");

      $("[name=grupo_id]").val(respuesta.grupo_id);
      $("[name=grupo_id]").select2().trigger("change");

      $("#switch_estado").attr("data-id", respuesta.id);
      $("#switch_ip_restrict").attr("data-id", respuesta.id);
      $("#switch_validacion_2fa").attr("data-id", respuesta.id);

      if (respuesta.estado == 1) {
        $("#switch_estado").bootstrapToggle("on");
      } else {
        $("#switch_estado").bootstrapToggle("off");
      }

      if (respuesta.ip_restrict == 1) {
        $("#switch_ip_restrict").bootstrapToggle("on");
      } else {
        $("#switch_ip_restrict").bootstrapToggle("off");
      }

      if (respuesta.validacion_2fa == 1) {
        $("#switch_validacion_2fa").bootstrapToggle("on");
      } else {
        $("#switch_validacion_2fa").bootstrapToggle("off");
      }
    })
    .fail(function (e) {
      console.log(e);
    });
}

function sec_opc_tbl_permisos_auditoria() {
  $("#log_permisos_fecha_inicio").val("");
  $("#log_permisos_fecha_fin").val("");
  $(".save_btn").hide();
  $("#title-text").html("Historial de Usuario");

  $(".btn_permisos_usuario_seleccionado").hide();
  $("#btn_contrasena_user").hide();
  $("#btns_list_user").hide();
  $("#btns_opt_user").show();
  $("#opc_user").hide();
  $("#opc_tabla_usuario").hide();
  $("#opc_tbl_permisos_auditoria").show();
}

function sec_log_permisos_usuario(usuario_id) {
  var fecha_inicio = $("#log_permisos_fecha_inicio").val();
  var fecha_fin = $("#log_permisos_fecha_fin").val();
  $("#log_de_usuario").html("");

  sec_opc_tbl_permisos_auditoria();

  $("#log_permisos_fecha_inicio").val(fecha_inicio);
  $("#log_permisos_fecha_fin").val(fecha_fin);
  getPermisosAuditoria(usuario_id);

  $("#btn_consultar_log_permisos").on("click", function () {
    getPermisosAuditoria(usuario_id);
  });
}

function getPermisosAuditoria(usuario_id) {
  var set_data = {};
  set_data["usuario_id"] = usuario_id;
  set_data["fecha_inicio"] = $("#log_permisos_fecha_inicio").val();
  set_data["fecha_fin"] = $("#log_permisos_fecha_fin").val();

  $.ajax({
    url: "sys/set_usuarios.php?action=get_permisos_auditoria",
    type: "POST",
    data: set_data,
  })
    .done(function (data) {
      var respuesta = JSON.parse(data);

      printPermisosAuditoria(respuesta.auditoria);
      $("#log_de_usuario").html(
        "[" + respuesta.usuario.id + "]" + respuesta.usuario.usuario
      );
      //---------------------------------------------------------------------------------------------------------------------------//
      // Hidden agregados para consumo de logs usuarios auditoria
      if (document.getElementById("hid_usuario_id"))
        document.getElementById("hid_usuario_id").value = respuesta.usuario.id;
      if (document.getElementById("hid_usuario"))
        document.getElementById("hid_usuario").value =
          respuesta.usuario.usuario;
      if (document.getElementById("hid_personal_id"))
        document.getElementById("hid_personal_id").value =
          respuesta.usuario.personal_id;
      //---------------------------------------------------------------------------------------------------------------------------//
    })
    .fail(function (e) {
      console.log(e);
    });

  // $(function () {
  // 	$('#datetimepicker1').datetimepicker();
  // });
}

function printPermisosAuditoria(data) {
  $("#table_tbl_permisos_auditoria")
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
        { data: "menu_descripcion", className: "text-left" },
        { data: "boton_nombre", className: "text-left" },
        { data: "accion_nombre", className: "text-left" },
        { data: "ip", className: "text-left" },
      ],
      columnDefs: [
        {
          render: function (data, type, row, meta) {
            if (data == "Agregado") {
              return '<span style="color:' + "green" + '">' + data + "</span>";
            } else if (data == "Quitado") {
              return '<span style="color:' + "red" + '">' + data + "</span>";
            } else {
              return '<span style="color:' + "red" + '">' + data + "</span>";
            }
          },
          targets: 4,
        },
        {
          visible: true,
          targets: 4,
        },
        { width: "25%", targets: 2 },
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
          title: "Log de permisos de Usuario",
        },
      ],
    })
    .DataTable();
}

function sec_usuarios_change_pass_modal(opt) {
  console.log("sec_usuarios_change_pass_modal");
  $("#sec_usuarios_change_pass_modal").modal("hide");
  $("#sec_usuarios_change_pass_modal").remove();

  if (opt) {
    $.post(
      "/sys/get_usuarios.php",
      {
        sec_usuarios_change_pass_modal: 1,
      },
      function (r) {
        loading();
        try {
          $("body").append(r);
          $("#sec_usuarios_change_pass_modal").modal({
            backdrop: "static",
            show: true,
          });
          sec_usuarios_change_pass_modal_events();
        } catch (err) {}
      }
    );
  }
}

function sec_usuarios_change_pass_modal_events() {
  var new_password = document.getElementById("sec_usuarios_change_pass_form")
    .elements["new_password"];
  new_password.onkeyup = function (event) {
    var nuevo_password = event.target.value;
    validar_password(nuevo_password);
  };

  var new_repassword = document.getElementById("sec_usuarios_change_pass_form")
    .elements["new_repassword"];
  new_repassword.onkeyup = function (event) {
    var val_new_password = $("[name=new_password").val();
    var val_confirm_password = event.target.value;
    confirm_password(val_new_password, val_confirm_password);
  };

  $("#sec_usuarios_change_pass_form").submit(function (event) {
    event.preventDefault();
    sec_usuario_change_password();
  });

  $("#sec_usuarios_change_pass_modal .close_btn")
    .off()
    .click(function (event) {
      sec_usuarios_change_pass_modal();
    });
}

function sec_usuario_change_password() {
  //validar que el password sea segura
  var new_password = $("[name=new_password").val();
  var new_repassword = $("[name=new_repassword").val();
  const validator = new PasswordValidator(new_password);
  const val_password = validator.isValid();
  if (val_password.validate) {
    if (new_password != new_repassword) {
      alertify.error("Las contraseñas no coinciden.", 5);
      $("[name=new_repassword").focus();
      return false;
    }

    loading(true);
    var get_data = {};
    $("#sec_usuarios_change_pass_modal .save_data").each(function (index, el) {
      get_data[$(el).attr("name")] = $(el).val();
    });
    $.post(
      "/sys/set_usuarios.php",
      { sec_usuario_change_password: get_data },
      function (r) {
        loading();
        try {
          var obj = jQuery.parseJSON(r);
          if (obj.error) {
            get_data.error = obj.error;
            get_data.error_msg = obj.error_msg;
            auditoria_send({
              proceso: "sec_usuario_change_password_error",
              data: get_data,
            });
            swal(
              {
                title: "¡Error!",
                text: obj.error_msg,
                type: "warning",
                timer: 3000,
                closeOnConfirm: true,
              },
              function () {
                swal.close();
                custom_highlight(
                  $(
                    "#sec_usuarios_change_pass_modal .save_data[name='" +
                      obj.error_focus +
                      "']"
                  )
                );
                setTimeout(function () {
                  $(
                    "#sec_usuarios_change_pass_modal .save_data[name='" +
                      obj.error_focus +
                      "']"
                  )
                    .val("")
                    .focus();
                }, 10);
              }
            );
          } else {
            get_data.curr_login = obj.curr_login;
            auditoria_send({
              proceso: "sec_usuario_change_password_done",
              data: get_data,
            });
            swal(
              {
                title: "¡Contraseña cambiada!",
                text: "Por favor vuelve a iniciar sesión con tu nueva contraseña.",
                type: "success",
                timer: 5000,
                closeOnConfirm: true,
              },
              function () {
                m_reload();
              }
            );
          }
        } catch (err) {
          auditoria_send({
            proceso: "sec_usuario_change_password_error_general",
            data: r,
          });
        }
      }
    );
  } else {
    alertify.error("Ingrese una clave segura", 5);
    $("[name=new_password").focus();
  }
}

function sec_cashier_select_location_modal(opt) {
  $("#sec_cashier_select_location_modal").modal("hide");
  $("#sec_cashier_select_location_modal").remove();

  if (opt) {
    $.post(
      "/sys/get_usuarios.php",
      {
        sec_cashier_select_location_modal: 1,
      },
      function (r) {
        loading();
        try {
          $("body").append(r);
          $("#sec_cashier_select_location_modal").modal({
            backdrop: "static",
            show: true,
          });
          if (localStorage.getItem("ultimo_local_seleccionado") != null) {
            $("#sec_cashier_select_location_modal #local_id").val(
              localStorage.getItem("ultimo_local_seleccionado")
            );
          }
          $("#local_id").select2();
          sec_cashier_select_location_events();
        } catch (err) {}
      }
    );
  }
}

function sec_cashier_select_location_events() {
  $("#sec_cashier_select_location_form").submit(function (event) {
    event.preventDefault();
    sec_chashier_change_location();
  });

  $("#sec_cashier_select_location_modal .close_btn")
    .off()
    .click(function () {
      sec_cashier_select_location_modal();
    });
}

function sec_chashier_change_location() {
  loading(true);
  let get_data = {};

  $("#sec_cashier_select_location_modal .save_data").each(function (index, el) {
    get_data[$(el).attr("name")] = $(el).val();
  });

  console.log(get_data);
  $.post(
    "/sys/set_usuarios.php",
    { sec_cashier_change_location: get_data },
    function (r) {
      loading();
      auditoria_send({
        proceso: "sec_cashier_change_location_done",
        data: get_data,
      });
      localStorage.setItem(
        "ultimo_local_seleccionado",
        $("#sec_cashier_select_location_modal #local_id").val()
      );
      swal(
        {
          title: "Cambio de local",
          text: "Ha cambiado de local",
          type: "success",
          timer: 5000,
          closeOnConfirm: true,
        },
        function () {
          localStorage.setItem("anuncio_reload", "false");
          m_reload();
        }
      );
    }
  );
}
/**
 * Funcionalidad que carga el personal cuando se qiere agregar nuevo usuario
 */
function ajax_get_all_personal() {
  //se valida si ya contiene personas
  let icontador_personal = document.getElementById("personal_id").length;
  if (icontador_personal <= 1) {
    $.ajax({
      url: "sys/get_usuarios.php",
      type: "GET",
      data: {
        action: "get_all_personal",
        //id: objInput.value
      },
    })
      .done(function (data) {
        arr_result = JSON.parse(data);
        var i = 0;
        var sel = $("#personal_id");
        sel.empty();
        sel.append('<option value="0">Ninguno</option>');
        for (var i = 0; i < arr_result.length; i++) {
          sel.append(
            '<option value="' +
              arr_result[i].id +
              '">' +
              arr_result[i].nombre +
              "</option>"
          );
        }
        i = 8;
      })
      .fail(function () {})
      .always(function () {
        //r_loading();
        console.log("ajax_personal 1");
      });
  }
}
/**
 * funcionalidad para obtener la estructura arbol html de grupos usuario
 */
function ajax_get_groups_user() {
  let iindicador_grupos_usuario = parseInt(
    document.getElementById("hid_indicador_grupos_usuario").value
  );
  if (iindicador_grupos_usuario == 0) {
    //es 1era vez que se carga la estructura arbol

    loading(true);
    $.ajax({
      url: "sys/set_usuarios_grupo.php",
      type: "GET",
      data: {
        ajax_usuarios_grupo: "1",
        //id: objInput.value
      },
    })
      .done(function (data) {
        var el = document.getElementById("modal_grupo_user");
        el.innerHTML = data;
        iindicador_grupos_usuario++;
        document.getElementById("hid_indicador_grupos_usuario").value =
          iindicador_grupos_usuario;
        sec_usuarios_permisos_botones_x_menu_sub_menus_expand_collapse_rows();
        sec_usuarios_permisos_asignar_botones_x_menu_sub_menu_botones();
        sec_grupo_marcar_todos();
        sec_grupo_boton_cancelar();
        sec_grupo_boton_crear_grupo();
        sec_grupo_boton_editar_grupo();
        $("#btnCrearGrupo").show();
        $("#btnCrearGrupo").prop("disabled", false);
        $("#btnActualizarGrupo").hide();
        $("#btnActualizarGrupo").prop("disabled", true);
        $("#btnCancelarGrupo").show();
        $("#btnCancelarGrupo").prop("disabled", false);
        sec_permisos_locales_event();
        $("#btnCancelarGrupo").trigger("click");
        loading(false);
      })
      .fail(function () {
        loading(false);
      })
      .always(function () {
        loading(false);
        // roles
        console.log("ajax_personal 2");
      });
  } else {
    // roles ya cargados (modal)
    //no se necesita cargar la estructura arbol
    console.log("estructura ya generada");
  }
}
/**
 * funcionalidad para obtener la estructura arbol html de permisos usuario
 *
 */
function ajax_get_permissions_user() {
  $.ajax({
    url: "sys/set_usuarios_permisos.php",
    type: "GET",
    data: {
      ajax_usuarios_grupo: "1",
      //id: objInput.value
    },
  })
    .done(function (data) {
      var el = document.getElementById("div_usuarios_permisos");
      el.innerHTML = data;
      // console.log(data);
      //var el = document.getElementById("modal_grupo_user");
      //el.innerHTML= data;
      sec_permisos_select_permisos();
      sec_usuarios_permisos_locales_x_redes_expand_collapse_rows();
      sec_usuarios_permisos_asignar_botones_x_menu_sub_menu_botones();
      sec_permisos_btn_editar_local();
      sec_permisos_btn_editar_permisos();
      sec_usuarios_permisos_filtrar_tabla_locales_x_usuario();
      $("#btn_asignar_permisos_multiple_usuarios").html(
        '<i class="glyphicon glyphicon-equalizer"></i>Permisos'
      );
      sec_permisos_locales_event();
      sec_usuarios_permisos_botones_x_menu_sub_menus_expand_collapse_rows();
    })
    .fail(function () {
      loading(false);
    })
    .always(function () {
      loading(false);
      console.log("ajax_personal 3");
    });
}
//BUGFIX ENIE
function validarTextoUsuario(input) {
  var textoInput = input.value;
  var regex = /^[a-zñ]*\.?[a-zñ]*$/;

  if (!regex.test(textoInput)) {
    input.value = textoInput.slice(0, -1);
  } else {
    var primerCaracter = textoInput.charAt(0);
    if (primerCaracter == ".") {
      input.value = textoInput.slice(0, -1);
    }
  }
  var contador = input.value.length;
  $("#save_data_contador_caracteres").html(contador);
}

function sec_usuarios_import_2fa(btn) {
  loading(true);

  var archivo_import = $("#file_imp_2fa")[0].files[0];
  var formData = new FormData();
  formData.append("archivo_csv", archivo_import);

  $.ajax({
    url: "sys/set_usuarios.php?action=import_users_2fa",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
  })
    .done(function (data) {
      var respuesta = JSON.parse(data);
      loading(false);
      if (respuesta.error) {
        swal({
          title: "Error",
          text: respuesta.error,
          type: "info",
          closeOnConfirm: true,
        });
      } else {
        swal({
          title: "Completado",
          text: "La importación terminó correctamente",
          type: "success",
          closeOnConfirm: true,
        });
      }
    })
    .fail(function (e) {
      console.log(e);
    });
}

function validar_password(nuevo_password) {
  const validator = new PasswordValidator(nuevo_password);
  const result = validator.isValid();

  var alert = "";
  if (!result.validate) {
    alert =
      alert +
      '<div class="alert alert-danger" style="margin-bottom: -15px;" role="alert">';
    alert =
      alert + '<ul style=" list-style-type: none; margin:0; padding: 0;">';
    result.errors.forEach((element) => {
      alert =
        alert +
        '<li> <small><i class="glyphicon glyphicon-alert"></i>  ' +
        element +
        "</small></li>";
    });
    alert = alert + "</ul>";
    alert = alert + "</div>";
  }
  $("#container-alert-password").html(alert);
  $("#progress-bar-security").css({ width: result.point + "%" });

  $("#progress-bar-security").removeClass("progress-bar-danger");
  $("#progress-bar-security").removeClass("progress-bar-warning");
  $("#progress-bar-security").removeClass("progress-bar-info");
  $("#progress-bar-security").removeClass("progress-bar-success");

  var texto_progress_bar = "";
  if (result.point <= 10) {
    $("#progress-bar-security").addClass("progress-bar-danger");
  } else if (result.point == 15) {
    $("#progress-bar-security").addClass("progress-bar-danger");
  } else if (result.point == 25) {
    $("#progress-bar-security").addClass("progress-bar-warning");
  } else if (result.point == 60) {
    $("#progress-bar-security").addClass("progress-bar-warning");
  } else if (result.point == 85) {
    $("#progress-bar-security").addClass("progress-bar-info");
  } else if (result.point == 100) {
    $("#progress-bar-security").addClass("progress-bar-success");
  }
  texto_progress_bar = result.assessment + " (" + result.point + "/100)";
  $("#progress-bar-text").html(texto_progress_bar);
}

function confirm_password(nuevo_password, confirm_nuevo_password) {
  if (nuevo_password != confirm_nuevo_password) {
    $("[name=confirm_password").attr("data-toggle", "confirm_password");
    $("[name=confirm_password").attr("data-placement", "right");
    $("[name=confirm_password").attr("title", "Las contraseñas no coinciden.");
    $('[data-toggle="confirm_password"]').tooltip("toggle");
  } else {
    $('[data-toggle="confirm_password"]').tooltip("destroy");
  }
}

function sec_usuarios_modal_historial_claves(id) {
  $("#sec_usuarios_modal_historial_claves").modal("show");
  var set_data = {
    usuario_id: id,
    sec_usuarios_get_historial_claves: 1,
  };

  $.ajax({
    url: "sys/get_usuarios.php",
    type: "POST",
    data: set_data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      var data = JSON.parse(resp);

      if (data.status != 200) {
        $("#sec_usuarios_modal_historial_claves").modal("hide");
        swal("", "A ocurrido un error intentalo mas tarde", "error");
      }

      $("#title-modal-historial-cambio").html(data.result.personal.nombre);
      $("#tbl_historial_clave")
        .dataTable({
          bDestroy: true,
          data: data.result.password_changes,
          responsive: true,
          order: [[0, "desc"]],
          pageLength: 25,
          columns: [
            { data: "ip", className: "text-center" },
            { data: "created_at", className: "text-center" },
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
        })
        .DataTable();

      $("#tbl_reseteos_clave")
        .dataTable({
          bDestroy: true,
          data: data.result.password_reset,
          responsive: true,
          order: [[0, "desc"]],
          pageLength: 25,
          columns: [
            { data: "ip", className: "text-center" },
            { data: "created_at", className: "text-center" },
            { data: "personal", className: "text-left" },
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
        })
        .DataTable();
    },
    error: function () {},
  });
}
