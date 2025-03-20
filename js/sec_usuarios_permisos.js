var sec_permisos_check_change = [];
var sec_locales_check_change = [];

function sec_usuarios_permisos() {
  console.log("sec_usuarios_permisos");
  sec_permisos_events();
}

function sec_permisos_events() {
  $(document).on("change", ".select_permisos_checkbox", function () {
    sec_permisos_check_save_change($(this), "permisos");
  });

  $(document).on("change", ".checkbox_locales_to_usuarios", function () {
    sec_permisos_check_save_change($(this), "locales");
  });

  //GENERAL TABLA USUARIOS
  $("#modal_grupo_user").on("hidden.bs.modal", function (e) {
    $("#btnCancelarGrupo").trigger("click");
  });

  //Para el Modal GRUPO
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

  //Para el modal PERMISOS
  sec_permisos_locales_event();
  sec_permisos_select_permisos();
  sec_usuarios_permisos_locales_x_redes_expand_collapse_rows();
  sec_permisos_btn_editar_local();
  sec_permisos_btn_editar_permisos();
  sec_usuarios_permisos_filtrar_tabla_locales_x_usuario();
}

function sec_permisos_check_save_change(check_data, state) {
  if (state == "permisos") {
    let btnId = check_data.val();
    let menuId = check_data.attr("data-menu-id");
    let activo = check_data.prop("checked") ? 1 : 0;
    let nombre_permiso = check_data.attr("data-menu-nombre");
    sec_permisos_check_change.push({
      menuId: menuId,
      btnId: btnId,
      nombre_permiso: nombre_permiso,
      active: activo,
    });
    sec_permisos_check_change = select_permisos_checkbox_elimina_duplicados2(
      sec_permisos_check_change,
      ["btnId", "menuId"],
      false
    );
  } else if (state == "locales") {
    let local_btnId = check_data.val();
    let local_menuId = check_data.attr("data-red");
    let local_activo = check_data.prop("checked") ? 1 : 0;
    sec_locales_check_change.push({
      local_menuId: local_menuId,
      local_btnId: local_btnId,
      local_active: local_activo,
    });
    sec_locales_check_change = select_permisos_checkbox_elimina_duplicados2(
      sec_locales_check_change,
      ["local_btnId", "local_menuId"],
      false
    );
  }
}

const select_permisos_checkbox_elimina_duplicados2 = (
  arr,
  indexedKeys,
  isPrioritizeFormer = true
) => {
  const lookup = new Map();
  const makeIndex = (el) =>
    indexedKeys.reduce((index, key) => `${index};;${el[key]}`, "");
  arr.forEach((el) => {
    const index = makeIndex(el);
    if (lookup.has(index) && isPrioritizeFormer) {
      return;
    }
    lookup.set(index, el);
  });

  return Array.from(lookup.values());
};

function sec_usuarios_permisos_botones_x_menu_sub_menus_expand_collapse_rows() {
  $(".parent_tbl_sub_menu_botones_padres")
    .off()
    .on("click", function () {
      var id_row_children = $(this).data("id");
      if (
        $(
          ".tbl_menu_sub_menu_botones_padres_detalles_" + id_row_children
        ).hasClass("rows_hidden_usuarios_permisos")
      ) {
        $(".tbl_menu_sub_menu_botones_padres_detalles_" + id_row_children)
          .toggle()
          .removeClass("rows_hidden_usuarios_permisos")
          .addClass("rows_expanded_usuarios_permisos");
        $(this)
          .find("span")
          .removeClass("glyphicon-plus")
          .addClass("glyphicon-minus");
        $(".tbl_menu_sub_menu_botones_detalles").hide();
      } else {
        $(".tbl_menu_sub_menu_botones_padres_detalles_" + id_row_children)
          .toggle()
          .removeClass("rows_expanded_usuarios_permisos")
          .addClass("rows_hidden_usuarios_permisos");
        $(this)
          .find("span")
          .removeClass("glyphicon-minus")
          .addClass("glyphicon-plus");
      }
    });
}

function sec_usuarios_permisos_asignar_botones_x_menu_sub_menu_botones() {
  $(".parent_tbl_sub_menu_botones")
    .off()
    .on("click", function () {
      var id_row_children = $(this).data("id");
      if (
        $(".tbl_menu_sub_menu_botones_detalles_" + id_row_children).hasClass(
          "rows_hidden_usuarios_permisos"
        )
      ) {
        $(".tbl_menu_sub_menu_botones_detalles_" + id_row_children)
          .toggle()
          .removeClass("rows_hidden_usuarios_permisos")
          .addClass("rows_expanded_usuarios_permisos");
        $(this)
          .find("span")
          .removeClass("glyphicon-plus")
          .addClass("glyphicon-minus");
      } else {
        $(".tbl_menu_sub_menu_botones_detalles_" + id_row_children)
          .toggle()
          .removeClass("rows_expanded_usuarios_permisos")
          .addClass("rows_hidden_usuarios_permisos");
        $(this)
          .find("span")
          .removeClass("glyphicon-minus")
          .addClass("glyphicon-plus");
      }
    });
}

function sec_grupo_boton_crear_grupo() {
  $("#btnCrearGrupo")
    .off()
    .on("click", function () {
      $("#formGuardarGrupo").each(function () {
        if ($("#txtGroupName").val() == "") {
          swal({
            title: "No se pudo crear rol",
            text: "Ingrese un nombre de rol para poder crearlo",
            type: "error",
            closeOnConfirm: true,
          });
          $("#txtGroupName").focus();
          console.log("error nombre grupo");
        } else {
          loading(true);
          var data = {
            name: $("#txtGroupName").val(),
            desc: $("#txtGroupDesc").val(),
            perms: [],
          };
          $(this)
            .find(":input")
            .each(function () {
              var atributo = $(this).attr("data-menu-id");
              var nombre_permiso = $(this).attr("data-menu-nombre");
              if (typeof atributo !== "undefined" && atributo !== false) {
                if ($(this).prop("checked")) {
                  data.perms.push({
                    menuId: atributo,
                    nombre_permiso: nombre_permiso,
                    btnId: $(this).val(),
                    active: 1,
                  });
                }
              }
            });
          $.ajax({
            url: "sys/set_usuarios_grupo.php?action=guardar_grupo",
            type: "POST",
            data: data,
          })
            .done(function (respuesta) {
              loading(false);
              $("#txtGroupName").val("");
              $("#txtGroupDesc").val("");
              sec_grupo_desmarcar_permisos_checkbox();
              $("#listado_grupo tbody").append(respuesta);
              swal({
                title: "Grupo creado correctamente",
                text: "",
                type: "success",
                closeOnConfirm: true,
              });
              console.log("rol creado");
            })
            .fail(function (e) {
              console.log(e);
            });
        }
      });
    });
}

function sec_grupo_boton_editar_grupo() {
  $("#btnActualizarGrupo").on("click", function (event) {
    event.preventDefault();
    swal(
      {
        title: "¿Estás seguro que deseas editar los permisos del grupo?",
        text: "Este cambio será replicado a los usuarios contenidos con permisos no customizados.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: true,
      },
      function () {
        loading(true);
        $("#formGuardarGrupo").each(function () {
          var data = {
            id: $("#txtGroupId").val(),
            name: $("#txtGroupName").val(),
            desc: $("#txtGroupDesc").val(),
            perms: [],
          };
          data.perms.push(sec_permisos_check_change);
          console.log(data);

          $.ajax({
            url: "sys/set_usuarios_grupo.php?action=actualizar_grupo",
            type: "POST",
            data: data,
          })
            .done(function (respuesta) {
              swal({
                title: "Grupo: " + $("#txtGroupName").val() + " actualizado",
                text: "Este grupo fue actualizado correctamente",
                type: "success",
                closeOnConfirm: true,
              });
              sec_permisos_check_change = [];
              $(
                "span[id='txtGrupoNombreHidden_" + $("#txtGroupId").val() + "']"
              ).text($("#txtGroupName").val());
              $(
                "span[id='txtGrupoDescHidden_" + $("#txtGroupId").val() + "']"
              ).text($("#txtGroupDesc").val());
              $("#boxMessage").html("Grupo editado exitosamente.");
              $("#boxMessage").fadeIn().delay(3000).fadeOut("slow");
              loading(false);
            })
            .fail(function (hxr, error) {
              console.log(error);
            });
        });
      }
    );
  });
}

function sec_grupo_eliminar_grupo(id_grupo) {
  swal(
    {
      title: "¿Estás seguro que deseas borrar este grupo?",
      text: "Esta acción es irreversible. No se afecta los permisos de los usuarios contenidos.",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
    },
    function () {
      var data = { groupId: id_grupo };
      $.ajax({
        url: "sys/set_usuarios_grupo.php?action=eliminar_grupo",
        type: "POST",
        data: data,
      })
        .done(function (data) {
          $("#tr_grupoid_" + id_grupo).remove();
          $("#boxMessage").html("Grupo ELIMINADO exitosamente.");
          $("#boxMessage").fadeIn().delay(3000).fadeOut("slow");
          swal({
            title: "Grupo Eliminado",
            text: "El grupo fue eliminado correctamente",
            type: "success",
            closeOnConfirm: true,
          });
          $("#btnCancelarGrupo").trigger("click");
        })
        .fail(function (e) {
          console.log(e);
        });
    }
  );
}

function sec_grupo_modal_check_grupo(id_grupo) {
  loading(true);
  $("#btnCrearGrupo").hide();
  $("#btnCrearGrupo").prop("disabled", true);
  $("#btnActualizarGrupo").show();
  $("#btnActualizarGrupo").prop("disabled", false);
  $("#btnCancelarGrupo").show();
  $("#btnCancelarGrupo").prop("disabled", false);

  var nombre_grupo = $(
    "span[id='txtGrupoNombreHidden_" + id_grupo + "']"
  ).text();
  var descripcion_grupo = $(
    "span[id='txtGrupoDescHidden_" + id_grupo + "']"
  ).text();

  //Se desmarcan todos los checkbox
  sec_grupo_desmarcar_permisos_checkbox();

  var datos_locales = {};
  datos_locales.id_grupo = id_grupo;
  $.ajax({
    url: "sys/set_usuarios_grupo.php",
    type: "POST",
    data: datos_locales,
  })
    .done(function (respuesta) {
      //nombre, descripción e id del grupo
      $("#txtGroupName").val(nombre_grupo);
      $("#txtGroupDesc").val(descripcion_grupo);
      $("#txtGroupId").val(id_grupo);

      //Se marcan los checkbox del grupo
      let data_respuesta = JSON.parse(respuesta);
      var chck_temp;
      for (let i in data_respuesta) {
        $(
          "input:checkbox[name='data_menu_" +
            data_respuesta[i].menu_id +
            "_" +
            data_respuesta[i].boton_id +
            "']"
        ).prop("checked", true);
        chck_temp = $(
          "input:checkbox[name='data_menu_" +
            data_respuesta[i].menu_id +
            "_" +
            data_respuesta[i].boton_id +
            "']"
        ).attr("data-menu-f");
        if (
          !$(".tbl_menu_first_checkbox[data-menu=" + chck_temp + "]").prop(
            "checked"
          )
        ) {
          $(".tbl_menu_first_checkbox[data-menu=" + chck_temp + "]").prop(
            "checked",
            true
          );
        }
        if (
          !$(
            ".tbl_menu_second_checkbox[data-menu-sub-id=" +
              data_respuesta[i].menu_id +
              "]"
          ).prop("checked")
        ) {
          $(
            ".tbl_menu_second_checkbox[data-menu-sub-id=" +
              data_respuesta[i].menu_id +
              "]"
          ).prop("checked", true);
        }
      }
      loading(false);
    })
    .fail(function (e) {
      console.log(e);
    });
  sec_permisos_check_change = [];
}

function sec_grupo_marcar_todos() {
  $("#btnCheckAll").on("click", function (e) {
    e.preventDefault();
    var chkboxes = $(
      '.tbl_menu_sub_menu_botones_crear_grupo input[type="checkbox"]'
    );
    chkboxes.prop("checked", !chkboxes.prop("checked"));
    if (chkboxes.prop("checked")) {
      $("#iconCheck").removeClass("glyphicon-unchecked");
      $("#iconCheck").addClass("glyphicon-check");
    } else {
      $("#iconCheck").removeClass("glyphicon-check");
      $("#iconCheck").addClass("glyphicon-unchecked");
    }
  });
}

function sec_usuarios_permisos_filtrar_tabla_locales_x_usuario() {
  $("#filter_tbl_locales_usuarios_seleccionados")
    .off()
    .on("keyup", function () {
      var term = $(this).val();
      if (term != "") {
        $("#tbl_locales_usuarios_seleccionados tbody>tr").hide();
        $("#tbl_locales_usuarios_seleccionados td")
          .filter(function () {
            return $(this).text().toLowerCase().indexOf(term) > -1;
          })
          .parent("tr")
          .show();
      } else {
        $("#tbl_locales_usuarios_seleccionados tbody>tr").show();
      }
    });
}

function sec_grupo_desmarcar_permisos_checkbox() {
  $(".select_permisos_checkbox").prop("checked", false);
  $(".tbl_menu_first_checkbox").prop("checked", false);
  $(".tbl_menu_second_checkbox").prop("checked", false);
}

function sec_permisos_desmarcar_locales() {
  $(".checkbox_locales_red").prop("checked", false);
  $(".checkbox_locales_to_usuarios").prop("checked", false);
}

function sec_grupo_boton_cancelar() {
  $("#btnCancelarGrupo")
    .off()
    .on("click", function () {
      $(".select_permisos_checkbox").prop("checked", false);
      $(".tbl_menu_first_checkbox").prop("checked", false);
      $(".tbl_menu_second_checkbox").prop("checked", false);
      $("#txtGroupName").val("");
      $("#txtGroupDesc").val("");
      $("#txtGroupId").val("");

      $("#btnCrearGrupo").show();
      $("#btnCrearGrupo").prop("disabled", false);
      $("#btnActualizarGrupo").hide();
      $("#btnActualizarGrupo").prop("disabled", true);
      $("#btnCancelarGrupo").show();
      $("#btnCancelarGrupo").prop("disabled", false);
    });
}

function sec_grupo_cambiar_estado_grupo(id_grupo) {
  swal(
    {
      title:
        "Al cambiar el estado de un Grupo, todos los usuarios contenidos serán afectados. Estás seguro que deseas continuar?",
      text: "",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
    },
    function () {
      var data = { groupId: id_grupo };
      $.ajax({
        url: "sys/set_usuarios_grupo.php?action=toggleActive",
        type: "POST",
        data: data,
      })
        .done(function (data) {
          if (
            $("input:hidden[id='txtEstadoGrupo_" + id_grupo + "']").val() > 0
          ) {
            $("button[id='btnToogleGrupoEstado_" + id_grupo + "']").removeClass(
              "btn-success"
            );
            $("button[id='btnToogleGrupoEstado_" + id_grupo + "']").addClass(
              "btn-danger"
            );

            $("i[id='icoToogleGrupeEstado_" + id_grupo + "']").removeClass(
              "glyphicon-ok"
            );
            $("i[id='icoToogleGrupeEstado_" + id_grupo + "']").addClass(
              "glyphicon-remove"
            );

            $("input:hidden[id='txtEstadoGrupo_" + id_grupo + "']").val(0);
          } else {
            $("button[id='btnToogleGrupoEstado_" + id_grupo + "']").removeClass(
              "btn-danger"
            );
            $("button[id='btnToogleGrupoEstado_" + id_grupo + "']").addClass(
              "btn-success"
            );

            $("i[id='icoToogleGrupeEstado_" + id_grupo + "']").removeClass(
              "glyphicon-remove"
            );
            $("i[id='icoToogleGrupeEstado_" + id_grupo + "']").addClass(
              "glyphicon-ok"
            );

            $("input:hidden[id='txtEstadoGrupo_" + id_grupo + "']").val(1);
          }
        })
        .fail(function (e) {
          loading();
          console.log(e);
        });
    }
  );
}

function sec_permisos_locales_event() {
  $("#container_locales_menus_sub_menus").css({
    display: "block",
    "margin-top": "5px",
  });
  $(".select_sistemas").select2();
  $(".select_permisos").select2();
  $("#usuario_a_copiar_permisos").select2();
  $("#select_permisos").on("select2:select", function (event) {
    var usuario_id = $(this).val();
    $(".valor_usuario_id").val(usuario_id);
    sec_permisos_modal_check_locales($(".valor_usuario_id").val());
    sec_permisos_modal_check_permisos($(".valor_usuario_id").val());
  });

  $(".select_permisos_usuario_a_copiar").on("change", function (e) {
    var usuario_copiar = $(this).val();
    $(".usuario_objectivo_seleccionado_a_copiar").val(usuario_copiar);
    sec_usuarios_permisos_verificar_asignacion_permisos_usuario_objetivo(
      usuario_copiar
    );
  });

  // Marcar todos los checkbox de locales de cada red
  $(".checkbox_locales_red").change(function () {
    if (this.checked) {
      $(
        ".checkbox_locales_to_usuarios[data-red=" +
          $(this).attr("data-red") +
          "]"
      ).prop("checked", true);
    } else {
      $(
        ".checkbox_locales_to_usuarios[data-red=" +
          $(this).attr("data-red") +
          "]"
      ).prop("checked", false);
    }

    // Guardamos los checkbox marcados o desmarcados anteriormente
    $(
      ".checkbox_locales_to_usuarios[data-red=" + $(this).attr("data-red") + "]"
    ).each(function () {
      sec_permisos_check_save_change($(this), "locales");
    });
  });

  // Para marcar todos los checkbox de los locales
  $(".checkAll_locales").change(function () {
    sec_locales_check_change = [];
    if (this.checked) {
      $(".checkbox_locales_red").prop("checked", true);
      $(".checkbox_locales_to_usuarios").prop("checked", true);
    } else {
      $(".checkbox_locales_red").prop("checked", false);
      $(".checkbox_locales_to_usuarios").prop("checked", false);
    }
    $(".checkbox_locales_to_usuarios").each(function () {
      sec_permisos_check_save_change($(this), "locales");
    });
  });

  // Para marcar todos los checkbox de los permisos menu padre
  $(".tbl_menu_first_checkbox").change(function () {
    if (this.checked) {
      $(
        ".select_permisos_checkbox[data-menu-id=" +
          $(this).attr("data-menu") +
          "]"
      ).prop("checked", true);
      $(
        ".tbl_menu_second_checkbox[data-menu-first-id=" +
          $(this).attr("data-menu") +
          "]"
      ).prop("checked", true);
      $(
        ".select_permisos_checkbox[data-menu-f=" +
          $(this).attr("data-menu") +
          "]"
      ).prop("checked", true);
    } else {
      $(
        ".select_permisos_checkbox[data-menu-id=" +
          $(this).attr("data-menu") +
          "]"
      ).prop("checked", false);
      $(
        ".tbl_menu_second_checkbox[data-menu-first-id=" +
          $(this).attr("data-menu") +
          "]"
      ).prop("checked", false);
      $(
        ".select_permisos_checkbox[data-menu-f=" +
          $(this).attr("data-menu") +
          "]"
      ).prop("checked", false);
    }

    // Guardamos los checkbox marcados o desmarcados anteriormente
    $(
      ".select_permisos_checkbox[data-menu-id=" +
        $(this).attr("data-menu") +
        "]"
    ).each(function () {
      sec_permisos_check_save_change($(this), "permisos");
    });
    $(
      ".select_permisos_checkbox[data-menu-f=" + $(this).attr("data-menu") + "]"
    ).each(function () {
      sec_permisos_check_save_change($(this), "permisos");
    });
  });

  // Para marcar todos los checkbox de los permisos sub menu
  $(".tbl_menu_second_checkbox").change(function () {
    if (this.checked) {
      $(
        ".select_permisos_checkbox[data-menu-id=" +
          $(this).attr("data-menu-sub-id") +
          "]"
      ).prop("checked", true);
    } else {
      $(
        ".select_permisos_checkbox[data-menu-id=" +
          $(this).attr("data-menu-sub-id") +
          "]"
      ).prop("checked", false);
    }

    // Guardamos los checkbox marcados o desmarcados anteriormente
    $(
      ".select_permisos_checkbox[data-menu-id=" +
        $(this).attr("data-menu-sub-id") +
        "]"
    ).each(function () {
      sec_permisos_check_save_change($(this), "permisos");
    });
  });

  // Se obtiene la información a mostrar en el modal de copiar locales
  $(".btn_copiar_user_settings_locales")
    .off()
    .on("click", function () {
      $("#modal_copiar_locales").modal("show");
      var info_user_1 = $(
        'select[id="select_permisos"] option:selected'
      ).text();
      var info_user_2 = $(
        'select[id="select_sistemas"] option:selected'
      ).text();
      var info_user = info_user_1 + " - " + info_user_2;
      $("#current_user").text(info_user);
      var tipo_permisos = $(this).data("tipo-permisos");

      $(".tipo_de_permisos_to_copy").val("1");
      $(".contenedor_tabla_permisos_locales_copiar").show();
      $(".contenedor_tabla_permisos_menus_copiar").hide();
      $(".contenedor_tabla_permisos_menus_copiar").empty();
      sec_usuarios_permisos_validacion_copiar_permisos_locales(
        $(this).data("button"),
        tipo_permisos,
        $(".valor_usuario_id").val()
      );
    });

  // Se obtiene la información a mostrar en el modal de copiar permisos
  $(".btn_copiar_user_settings_menus")
    .off()
    .on("click", function () {
      $("#modal_copiar_locales").modal("show");
      var info_user_1 = $(
        'select[id="select_permisos"] option:selected'
      ).text();
      var info_user_2 = $(
        'select[id="select_sistemas"] option:selected'
      ).text();
      var info_user = info_user_1 + " - " + info_user_2;
      $("#current_user").text(info_user);
      var tipo_permisos = $(this).data("tipo-permisos");

      $(".tipo_de_permisos_to_copy").val("2");
      $(".contenedor_tabla_permisos_locales_copiar").hide();
      $(".contenedor_tabla_permisos_locales_copiar").empty();
      $(".contenedor_tabla_permisos_menus_copiar").show();
      sec_usuarios_permisos_validacion_copiar_permisos_menus(
        $(this).data("button"),
        tipo_permisos,
        $(".valor_usuario_id").val()
      );
    });

  // Botón de copiar permisos o locales
  $(".btn_copy_settings_users_menu_permisos")
    .off()
    .on("click", function () {
      if ($(".select_permisos_usuario_a_copiar").val() == "") {
        swal(
          {
            title: "Por favor Seleccione Usuario destino",
            animation: "slide-from-top",
            type: "warning",
          },
          function (inputValue) {
            $("#modal_copiar_permisos").modal("show");
          }
        );
      } else {
        swal(
          {
            title: "Seguro desea reescribir los permisos?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "COPIAR",
            cancelButtonText: "CANCELAR",
            closeOnConfirm: false,
          },
          function () {
            loading(true);
            $("#modal_copiar_permisos").modal("hide");
            sec_usuarios_permisos_copy_settings_permisos(
              $(".tipo_de_permisos_to_copy").val()
            );
          }
        );
      }
    });
}

function fxAsignarRedUsuario(cb, _red_id) {
  console.log(cb.checked);
  //console.log(red_id.substring(13, red_id.len));
  let usuario_id = $(".valor_usuario_id").val();
  let red_id = _red_id.substring(7, _red_id.len);
  //console.log(red_id + ":" + usuario_id);
  Swal.fire({
    title: "¿Estás seguro que deseas editar la red para este usuario?",
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: "Si",
    denyButtonText: "No",
    cancelButtonText: "Cancelar",
    customClass: {
      actions: "my-actions",
      cancelButton: "order-1 right-gap",
      confirmButton: "order-2",
      denyButton: "order-3",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      loading(true);
      //Swal.fire('Saved!', '', 'success');
      //$("#local_checkbox_2").click();
      //$(".checkbox_locales_red").prop("checked", false);
      $.ajax({
        url: "sys/set_usuarios_permisos.php",
        type: "GET",
        data: {
          action: "asignar_usuarios_redes",
          usuario_id: usuario_id,
          red_id: red_id,
          operacion: cb.checked == true ? "asignar" : "quitar",
        },
      })
        .done(function (respuesta) {
          //Se marcan los checkbox del grupo
          let data_respuesta = JSON.parse(respuesta);
          Swal.fire(data_respuesta.msg, "", "success");
          console.log(data_respuesta);
          loading(false);
        })
        .fail(function (e) {
          console.log(false);
          console.log(e);
          loading(false);
        });
    } else {
      Swal.fire("Red no asignada/cancelada", "", "info");
      cb.checked = !cb.checked;
    }
  });
}

function sec_permisos_select_permisos() {
  $("#select_sistemas").change(function () {
    var data = {};
    data.id = $("#select_sistemas").val();
    data.opt = "select_permisos_change";
    $.ajax({
      data: data,
      url: "sys/get_usuarios_permisos.php",
      type: "POST",
      dataType: "json",
    })
      .done(function (response) {
        $("#select_permisos").html(response);
      })
      .fail(function (e) {
        console.log(e);
      });
  });

  $("#select_permisos").change(function () {
    $("#select_permisos_selected").val($("#select_permisos").val());
  });
}

function sec_usuarios_permisos_locales_x_redes_expand_collapse_rows() {
  $(".all_parent_usuarios_permisos")
    .off()
    .on("click", function () {
      if (
        $(".children_usuarios_permisos").hasClass(
          "rows_expanded_usuarios_permisos"
        )
      ) {
        $(".children_usuarios_permisos").hide();
        $(this)
          .find("span")
          .removeClass("glyphicon-minus")
          .addClass("glyphicon-plus");
        $(".children_usuarios_permisos")
          .removeClass("rows_expanded_usuarios_permisos")
          .addClass("rows_hidden_usuarios_permisos");
        $(".parent_usuarios_permisos")
          .find("span")
          .removeClass("glyphicon-minus")
          .addClass("glyphicon-plus");
      } else {
        $(".children_usuarios_permisos").show();
        $(".children_usuarios_permisos")
          .removeClass("rows_hidden_usuarios_permisos")
          .addClass("rows_expanded_usuarios_permisos");
        $(this)
          .find("span")
          .removeClass("glyphicon-plus")
          .addClass("glyphicon-minus");
        $(".parent_usuarios_permisos")
          .find("span")
          .removeClass("glyphicon-plus")
          .addClass("glyphicon-minus");
      }
    });

  $(".parent_usuarios_permisos")
    .off()
    .on("click", function () {
      var id_row_children = $(this).data("red");
      if (
        $(".children_row_collapse_expand_" + id_row_children).hasClass(
          "rows_hidden_usuarios_permisos"
        )
      ) {
        $(".children_row_collapse_expand_" + id_row_children)
          .toggle()
          .removeClass("rows_hidden_usuarios_permisos")
          .addClass("rows_expanded_usuarios_permisos");
        $(this)
          .find("span")
          .removeClass("glyphicon-plus")
          .addClass("glyphicon-minus");
        $(".nombre_red_usuarios_permisos")
          .removeClass("estilos_nombre_red_expand")
          .addClass("estilos_nombre_red_collapse");
      } else {
        $(".children_row_collapse_expand_" + id_row_children)
          .toggle()
          .removeClass("rows_expanded_usuarios_permisos")
          .addClass("rows_hidden_usuarios_permisos");
        $(this)
          .find("span")
          .removeClass("glyphicon-minus")
          .addClass("glyphicon-plus");
        $(".nombre_red_usuarios_permisos")
          .removeClass("estilos_nombre_red_collapse")
          .addClass("estilos_nombre_red_expand");
      }
    });
}

function sec_permisos_modal_check_locales(id_usuario) {
  loading(true);
  //Se desmarcan todos los checkbox
  sec_permisos_desmarcar_locales();
  var datos_locales = {};
  datos_locales.id_usuario = id_usuario;
  $.ajax({
    url: "sys/set_usuarios_permisos.php",
    type: "POST",
    data: datos_locales,
  })
    .done(function (respuesta) {
      //Se marcan los checkbox del grupo
      let data_respuesta = JSON.parse(respuesta);
      for (let i in data_respuesta) {
        $(
          "input:checkbox[id='local_checkbox_hijo_" +
            data_respuesta[i].local_id +
            "']"
        ).prop("checked", true);
        if (
          !$(
            "input:checkbox[id='local_checkbox_" +
              data_respuesta[i].red_id +
              "']"
          ).prop("checked")
        ) {
          $(
            "input:checkbox[id='local_checkbox_" +
              data_respuesta[i].red_id +
              "']"
          ).prop("checked", true);
        }
      }
      loading(false);
    })
    .fail(function (e) {
      console.log(false);
      console.log(e);
    });
  sec_locales_check_change = [];
}
function sec_permisos_modal_check_redes(id_usuario) {
  //loading(true);
  //Se desmarcan todos los checkbox
  $(".switch_redes").prop("checked", false);
  var datos_locales = {};
  datos_locales.id_usuario = id_usuario;
  $.ajax({
    url: "sys/set_usuarios_permisos.php",
    type: "GET",
    data: {
      action: "obtener_usuarios_redes",
      usuario_id: id_usuario,
    },
  })
    .done(function (respuesta) {
      //Se marcan los checkbox del grupo
      let data_respuesta = JSON.parse(respuesta);
      for (let i in data_respuesta) {
        $("input:checkbox[id='switch_" + data_respuesta[i].red_id + "']").prop(
          "checked",
          true
        );
      }
      loading(false);
    })
    .fail(function (e) {
      console.log(false);
      console.log(e);
    });
  sec_locales_check_change = [];
}

function sec_permisos_btn_editar_local() {
  $("#btnActualizarLocales").on("click", function (event) {
    event.preventDefault();
    swal(
      {
        title: "¿Estás seguro que deseas editar los locales de este usuario?",
        text: "",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: true,
      },
      function () {
        loading(true);
        var data = { id_user: $("#select_permisos_selected").val(), perms: [] };
        const locales_change = JSON.stringify(sec_locales_check_change);
        data.perms.push(locales_change);

        $.ajax({
          url: "sys/set_usuarios_permisos.php?action=actualizar_locales",
          type: "POST",
          data: data,
        })
          .done(function () {
            swal({
              title: "¡Locales actualizados!",
              text: "Los locales del usuario han sido actualizados",
              type: "success",
              closeOnConfirm: true,
            });
            loading(false);
          })
          .fail(function (error) {
            console.log(error);
            loading(false);
          });
        sec_locales_check_change = [];
      }
    );
  });
}

function sec_permisos_btn_editar_permisos() {
  $("#btnActualizarPermisos").on("click", function (event) {
    event.preventDefault();
    swal(
      {
        title: "¿Estás seguro que deseas editar los permisos de este usuario?",
        text: "",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: true,
      },
      function () {
        loading(true);
        var data = { id_user: $("#select_permisos_selected").val(), perms: [] };
        data.perms.push(sec_permisos_check_change);
        console.log(data);
        $.ajax({
          url: "sys/set_usuarios_permisos.php?action=actualizar_permisos",
          type: "POST",
          data: data,
        })
          .done(function (result) {
            loading(false);
            console.log("Permisos guardados");
            console.log(result);
            swal({
              title: "Permisos de usuario guardados",
              text: "",
              type: "success",
              closeOnConfirm: true,
            });
          })
          .fail(function (hxr, error) {
            loading(false);
            swal({
              title: "Los permisos no se pudieron guardar",
              text: error,
              type: "error",
              closeOnConfirm: true,
            });
            console.log(error);
          });
        sec_permisos_check_change = [];
      }
    );
  });
}

function sec_usuarios_permisos_validacion_copiar_permisos_locales(
  btn,
  tipo_permisos,
  usuario_id
) {
  auditoria_send({
    proceso: "sec_usuarios_permisos_validacion_copiar_permisos",
    data: btn,
  });
  $(document).on("evento_validar_permiso_usuario", function (event) {
    $(document).off("evento_validar_permiso_usuario");
    console.log("EVENT: evento_validar_permiso_usuario copiar permisos");
    console.log(tipo_permisos);
    if (event.event_data == true) {
      if ($("#select_permisos").val() == "") {
        swal(
          {
            title: "Por favor Seleccione Usuario",
            animation: "slide-from-top",
            type: "warning",
          },
          function (inputValue) {
            $("#modal_copiar_permisos").modal("hide");
          }
        );
      } else {
        if (tipo_permisos == "locales") {
          //funcion para cargar permisos por tipo- locales - menus
          sec_usuarios_permisos_get_permisos_locales_copiar(
            tipo_permisos,
            usuario_id
          );
        }
      }
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

function sec_usuarios_permisos_get_permisos_locales_copiar(
  tipo_permisos,
  usuario_id
) {
  var data_tipo_permisos = {};
  data_tipo_permisos.opt = "sec_permisos_get_data_tipo_permisos_locales_copiar";
  data_tipo_permisos.filtro = {};
  data_tipo_permisos.filtro.tipo = tipo_permisos;
  data_tipo_permisos.filtro.usuario = usuario_id;
  auditoria_send({
    proceso: "sec_usuarios_permisos_get_permisos_locales_copiar",
    data: data_tipo_permisos,
  });
  loading(true);
  $.ajax({
    url: "sys/get_usuario_permisos_locales_copiar.php",
    type: "POST",
    data: data_tipo_permisos,
  })
    .done(function (data_response, textStatus, jqXHR) {
      loading(false);
      try {
        $(".contenedor_tabla_permisos_locales_copiar").html(data_response);
      } catch (err) {
        swal(
          {
            title:
              "Error en la base de datos sec_usuarios_permisos_get_permisos_locales_copiar",
            type: "warning",
            timer: 2000,
          },
          function () {
            swal.close();
          }
        );
      }
    })
    .fail(function () {
      console.log("error");
    });
}

function sec_usuarios_permisos_copy_settings_permisos(tipo_de_permiso) {
  let user_since_copy = $(".valor_usuario_id").val();
  var user_to_copy = $(".usuario_objectivo_seleccionado_a_copiar").val();
  var data = {};
  data.user_from = user_since_copy;
  data.user_to = user_to_copy;

  if (tipo_de_permiso == 1) {
    data.type = 1;
    data.opt = "sec_permisos_copiar_permisos_usuarios_locales";
  } else {
    data.type = 2;
    data.opt = "sec_permisos_copiar_permisos_usuarios_menus";
  }
  auditoria_send({
    proceso: "sec_usuarios_permisos_copy_settings_permisos",
    data: data,
  });
  $.ajax({
    url: "sys/set_data.php",
    type: "POST",
    data: data,
  }).done(function (dataresponse) {
    loading();
    var obj = $.parseJSON(dataresponse);
    try {
      swal(
        {
          title: "Se copio los permisos satisfactoriamente!!!",
          animation: "slide-from-top",
          type: "success",
          timer: 100,
          showConfirmButton: true,
        },
        function (inputValue) {
          $("#modal_copiar_permisos").modal("hide");
        }
      );
    } catch (err) {
      swal(
        {
          title:
            "Error en la base de datos sec_usuarios_permisos_copy_settings_permisos",
          type: "warning",
          timer: 2000,
        },
        function () {
          swal.close();
        }
      );
    }
  });
}

function sec_usuarios_permisos_verificar_asignacion_permisos_usuario_objetivo(
  user_id_to_copy
) {
  var data_send = {};
  data_send.id_user_to_copy = user_id_to_copy;
  data_send.opt = "sec_permisos_copiar_permisos";
  auditoria_send({
    proceso:
      "sec_usuarios_permisos_verificar_asignacion_permisos_usuario_objetivo",
    data: data_send,
  });
  $.ajax({
    url: "sys/get_data.php",
    type: "POST",
    data: data_send,
  })
    .done(function (dataresponse) {
      try {
        var obj = JSON.parse(dataresponse);
      } catch (err) {
        swal(
          {
            title:
              "Error en la base de datos sec_usuarios_permisos_verificar_asignacion_permisos_usuario_objetivo",
            type: "warning",
            timer: 2000,
          },
          function () {
            swal.close();
          }
        );
      }
    })
    .fail(function () {
      console.log("error en asignacion permisos usuarios");
    });
}

function sec_permisos_modal_check_permisos(id_usuario_permisos) {
  loading(true);
  sec_grupo_desmarcar_permisos_checkbox();
  var datos = {};
  datos.id_usuario_permisos = id_usuario_permisos;
  $.ajax({
    url: "sys/set_usuarios_permisos.php?action=cargar_permisos",
    type: "POST",
    data: datos,
  })
    .done(function (respuesta) {
      //Se marcan los checkbox del MENU y SUB MENUS (sin terminar)
      let data_respuesta = JSON.parse(respuesta);
      var chck_temp;
      for (let i in data_respuesta) {
        $(
          "input:checkbox[name='data_menu_p_" +
            data_respuesta[i].menu_id +
            "_" +
            data_respuesta[i].boton_id +
            "']"
        ).prop("checked", true);
        chck_temp = $(
          "input:checkbox[name='data_menu_p_" +
            data_respuesta[i].menu_id +
            "_" +
            data_respuesta[i].boton_id +
            "']"
        ).attr("data-menu-f");
        if (
          !$(".tbl_menu_first_checkbox[data-menu=" + chck_temp + "]").prop(
            "checked"
          )
        ) {
          $(".tbl_menu_first_checkbox[data-menu=" + chck_temp + "]").prop(
            "checked",
            true
          );
        }
        if (
          !$(
            ".tbl_menu_second_checkbox[data-menu-sub-id=" +
              data_respuesta[i].menu_id +
              "]"
          ).prop("checked")
        ) {
          $(
            ".tbl_menu_second_checkbox[data-menu-sub-id=" +
              data_respuesta[i].menu_id +
              "]"
          ).prop("checked", true);
        }
      }
      loading(false);
    })
    .fail(function (e) {
      console.log(e);
      loading(false);
    });
  sec_permisos_check_change = [];
}

function sec_usuarios_permisos_validacion_copiar_permisos_menus(
  btn,
  tipo_permisos,
  usuario_id
) {
  auditoria_send({
    proceso: "sec_usuarios_permisos_validacion_copiar_permisos",
    data: btn,
  });
  $(document).on("evento_validar_permiso_usuario", function (event) {
    $(document).off("evento_validar_permiso_usuario");
    console.log("EVENT: evento_validar_permiso_usuario copiar permisos");
    console.log(tipo_permisos);
    if (event.event_data == true) {
      if ($(".select_permisos").val() == "") {
        swal(
          {
            title: "Por favor Seleccione Usuario",
            animation: "slide-from-top",
            type: "warning",
          },
          function (inputValue) {
            $("#modal_copiar_permisos").modal("hide");
          }
        );
      } else {
        if (tipo_permisos == "menus") {
          //funcion para cargar permisos por tipo- locales - menus
          sec_usuarios_permisos_get_permisos_menus_copiar(
            tipo_permisos,
            usuario_id
          );
        }
      }
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

function sec_usuarios_permisos_get_permisos_menus_copiar(
  tipo_permisos,
  usuario_id
) {
  var data_tipo_permisos = {};
  data_tipo_permisos.opt = "sec_permisos_get_data_tipo_permisos_menus_copiar";
  data_tipo_permisos.filtro = {};
  data_tipo_permisos.filtro.tipo = tipo_permisos;
  data_tipo_permisos.filtro.usuario = usuario_id;
  auditoria_send({
    proceso: "sec_usuarios_permisos_get_permisos_menus_copiar",
    data: data_tipo_permisos,
  });
  loading(true);
  $.ajax({
    url: "sys/get_usuario_permisos_menus_copiar.php",
    type: "POST",
    data: data_tipo_permisos,
  })
    .done(function (data_response, textStatus, jqXHR) {
      loading(false);
      try {
        $(".contenedor_tabla_permisos_menus_copiar").html(data_response);
      } catch (err) {
        swal(
          {
            title:
              "Error en la base de datos sec_usuarios_permisos_get_permisos_menus_copiar",
            type: "warning",
            timer: 2000,
          },
          function () {
            swal.close();
          }
        );
      }
    })
    .fail(function () {
      console.log("error");
    });
}
