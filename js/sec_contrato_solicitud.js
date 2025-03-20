function sec_contrato_solicitud() {
  sec_contratos_solicitud_parametros_de_busqueda();
  sec_contratos_solicitud_obtener_departamentos();
  sec_contratos_solicitud_listar_solicitudes();

  sec_contrato_solicitud_obtener_opciones(
    "obtener_directores",
    $("[name='director_aprobacion_id']")
  );
  let contratos_tipo_contrato_id = localStorage.getItem(
    "contratos_tipo_contrato_id"
  );
  console.log("sec_contrato_solicitud", contratos_tipo_contrato_id);
  setTimeout(function () {
    sec_contratos_solicitud_listar_tipo_contrato();
    let contratos_tipo_contrato_id = localStorage.getItem(
      "contratos_tipo_contrato_id"
    );

    $(".select2").select2({ width: "100%" });
    if (contratos_tipo_contrato_id > 0) {
      $("#tipo_contrato_id").val(contratos_tipo_contrato_id).trigger("change");
    } else {
      $("#tipo_contrato_id").change();
    }
  }, 500);

  $("#contratos_form_solicitud_locales").hide();
  $("#contratos_form_solicitud_proveedores").hide();
  $("#contratos_form_solicitud_adenda_arrendamiento").hide();
  $("#contratos_form_solicitud_adenda_proveedores").hide();
  $("#contratos_form_solicitud_acuerdo_confidencialidad").hide();
  $("#contratos_form_solicitud_agentes").hide();

  $("#tipo_contrato_id").change(function () {
    $("#tipo_contrato_id option:selected").each(function () {
      $("#contratos_form_solicitud_proveedores").hide();
      $("#contratos_form_solicitud_locales").hide();
      $("#contratos_form_solicitud_adenda_arrendamiento").hide();
      $("#contratos_form_solicitud_adenda_proveedores").hide();
      $("#contratos_form_solicitud_acuerdo_confidencialidad").hide();
      $("#contratos_form_solicitud_agentes").hide();
      var selectValor = $(this).val();
      console.log("selectValor: ", selectValor);
      if (selectValor == "12") {
        $("#contratos_form_solicitud_locales").show();
      } else if (selectValor == "12") {
        $("#contratos_form_solicitud_proveedores").show();
      } else if (selectValor == "3") {
        $("#contratos_form_solicitud_adenda_arrendamiento").show();
      } else if (selectValor == "4") {
        $("#contratos_form_solicitud_adenda_proveedores").show();
      } else if (selectValor == "5") {
        $("#contratos_form_solicitud_acuerdo_confidencialidad").show();
      } else if (selectValor == "6") {
        $("#contratos_form_solicitud_agentes").show();
      }
      setTimeout(function () {
        $(".select2").select2({
          width: "100%",
        });
      }, 200);
      localStorage.setItem("contratos_tipo_contrato_id", selectValor);
    });
  });
  $(".sec_contrato_solicitud_datepicker").datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
  });
  $(".limpiar_input").click(function () {
    $("#" + $(this).attr("limpiar")).val("");
  });
  $(".limpiar_select2").click(function () {
    $("#" + $(this).attr("limpiar"))
      .select2()
      .val("")
      .trigger("change");
  });
  $("#btn_limpiar_filtros_de_busqueda").click(function () {
    $("#cont_proveedor_param_empresa").select2().val("").trigger("change");
    $("#cont_proveedor_param_area_solicitante")
      .select2()
      .val("")
      .trigger("change");
    $("#cont_proveedor_param_ruc").val("");
    $("#cont_proveedor_param_razon_social").val("");
    $("#cont_proveedor_param_moneda").select2().val("").trigger("change");
    $("#search_id_departamento").select2().val("").trigger("change");
    $("#search_id_provincia").select2().val("").trigger("change");
    $("#search_id_distrito").select2().val("").trigger("change");
    $("#search_fecha_inicio").val("");
    $("#search_fecha_fin").val("");
    $("#nombre_tienda").val("");
    $("#nombre_agente").val("");
    $("#nombre_nombre_agentetienda").val("");
    $("#sec_sol_estado_solicitud").select2().val("").trigger("change");
    $("#sec_sol_estado_solicitud_v2").select2().val("").trigger("change");
    $("#director_aprobacion_id").select2().val("").trigger("change");
    $("#sec_sol_estado_aprobacion").select2().val("").trigger("change");
    $("#search_fecha_inicio_aprobacion").val("");
    $("#search_fecha_fin_aprobacion").val("");
    $("#sec_sol_tipo_contrato").select2().val("").trigger("change");
    setTimeout(function () {
      $(".select2").select2({
        width: "100%",
      });
    }, 200);
  });
}

function sec_contratos_solicitud_listar_tipo_contrato() {
  var data = {
    accion: "obtener_tipo_contrato_v2",
  };

  $.ajax({
    url: "/sys/set_contrato_nuevo.php",
    type: "POST",
    data: data, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      //  alert(datat)
      var respuesta = JSON.parse(datos);
      // console.log(respuesta);
      if (respuesta.http_code == 200) {
        $("#contrato-dropdown-menu").find("li").remove().end();
        $("#contrato-dropdown-menu").append(respuesta.result);
      }
    },
    error: function () {},
  });
}

function sec_contrato_solicitud_obtener_opciones(accion, select) {
  $.ajax({
    url: "/sys/set_contrato_nuevo.php",
    type: "POST",
    data: { accion: accion },
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      var respuesta = JSON.parse(datos);
      $(select).find("option").remove().end();
      $(select).append('<option value="">-- TODOS --</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $(select).append(opcion);
      });
    },
    error: function () {},
  });
}

function sec_contratos_solicitud_parametros_de_busqueda() {
  let tipo_contrato_id = $("#tipo_contrato_id").val();
  $(".form-search-empresa").hide();
  $(".form-search-area").hide();
  $(".form-search-ruc-proveedor").hide();
  $(".form-search-razon-social").hide();
  $(".form-search-moneda").hide();
  $(".form-estado-solicitud").hide();
  $(".form-search-estado_aprobacion").hide();
  $(".form-tipo-solicitud").hide();
  $(".form-search-aprobante").hide();
  $(".form-search-fecha_aprobacion_desde").hide();
  $(".form-search-fecha_aprobacion_hasta").hide();
  $(".form-search-departamento").hide();
  $(".form-search-provincia").hide();
  $(".form-search-distrito").hide();
  $(".form-search-nombre-de-tienda").hide();
  $(".form-search-nombre-del-agente").hide();
  $("#cont_proveedor_param_empresa").select2().val("").trigger("change");
  $("#cont_proveedor_param_area_solicitante")
    .select2()
    .val("")
    .trigger("change");
  $("#cont_proveedor_param_moneda").select2().val("").trigger("change");
  $("#cont_proveedor_param_ruc").val("");
  $("#cont_proveedor_param_razon_social").val("");
  $("#search_id_departamento").select2().val("").trigger("change");
  $("#search_id_provincia").select2().val("").trigger("change");
  $("#search_id_distrito").select2().val("").trigger("change");
  $("#nombre_tienda").val("");
  if (tipo_contrato_id == 1) {
    $(".form-search-empresa").show();
    $(".form-search-moneda").show();
    $(".form-estado-solicitud").show();
    $(".form-search-departamento").show();
    $(".form-search-provincia").show();
    $(".form-search-distrito").show();
    $(".form-search-nombre-de-tienda").show();
  }
  if (tipo_contrato_id == 2) {
    $(".form-search-empresa").show();
    $(".form-search-area").show();
    $(".form-search-ruc-proveedor").show();
    $(".form-search-razon-social").show();
    $(".form-search-moneda").show();
    $(".form-estado-solicitud").show();
    $(".form-search-estado_aprobacion").show();
    $(".form-search-aprobante").show();
    $(".form-search-fecha_aprobacion_desde").show();
    $(".form-search-fecha_aprobacion_hasta").show();
  }
  if (tipo_contrato_id == 3) {
    // $(".form-search-empresa").show();
    // $(".form-search-moneda").show();
    $(".form-estado-solicitud").show();
    $(".form-search-departamento").show();
    $(".form-search-provincia").show();
    $(".form-search-distrito").show();
    // $(".form-search-nombre-de-tienda").show();
  }
  if (tipo_contrato_id == 4) {
    $(".form-search-empresa").show();
    $(".form-search-area").show();
    $(".form-search-ruc-proveedor").show();
    $(".form-search-razon-social").show();
    $(".form-search-moneda").hide();
    $(".form-estado-solicitud").show();
    $(".form-search-estado_aprobacion").show();
    $(".form-search-aprobante").show();
    $(".form-search-fecha_aprobacion_desde").show();
    $(".form-search-fecha_aprobacion_hasta").show();
  }
  if (tipo_contrato_id == 5) {
    $(".form-search-empresa").show();
    $(".form-search-area").show();
    $(".form-search-ruc-proveedor").show();
    $(".form-search-razon-social").show();
    $(".form-estado-solicitud").show();
    $(".form-search-aprobante").show();
    $(".form-search-fecha_aprobacion_desde").show();
    $(".form-search-fecha_aprobacion_hasta").show();
  }
  if (tipo_contrato_id == 6) {
    $(".form-search-empresa").show();
    $(".form-estado-solicitud").show();
    $(".form-search-nombre-del-agente").show();
  }
  if (tipo_contrato_id == 7) {
    $(".form-search-empresa").show();
    $(".form-search-area").show();
    $(".form-estado-solicitud").show();
  }
  if (tipo_contrato_id == 8) {
    $(".form-search-empresa").show();
    $(".form-search-area").show();
    $(".form-estado-solicitud").show();
  }
  if (tipo_contrato_id == 9) {
    $(".form-search-empresa").show();
    $(".form-search-area").show();
    $(".form-search-ruc-proveedor").show();
    $(".form-search-razon-social").show();
    $(".form-estado-solicitud").show();
    $(".form-search-estado_aprobacion").show();
    $(".form-search-aprobante").show();
    $(".form-search-fecha_aprobacion_desde").show();
    $(".form-search-fecha_aprobacion_hasta").show();
  }
  if (tipo_contrato_id == 10) {
    // $('.form-search-empresa').show();
    $(".form-estado-solicitud").show();
  }
  if (tipo_contrato_id == 11) {
    $(".form-estado-solicitud").show();
    $(".form-tipo-solicitud").show();
  }
  if (tipo_contrato_id == 12) {
    $(".form-search-empresa").show();
    // $(".form-estado-solicitud").show();
    $(".form-search-empresa label").text("Arrendatario:");
  }
  if (tipo_contrato_id == 13) {
    $(".form-search-empresa").show();
    //$(".form-estado-solicitud").show();
    $(".form-search-empresa label").text("Locador:");
  }
  if (tipo_contrato_id == 14) {
    $(".form-search-empresa").show();
    //$(".form-estado-solicitud").show();
    $(".form-search-empresa label").text("Mandatario:");
  }
  if (tipo_contrato_id == 15) {
    $(".form-search-empresa").show();
    //$(".form-estado-solicitud").show();
    $(".form-search-empresa label").text("Mutuatario:");
  }
}

function sec_contratos_solicitud_cambiar_de_pagina(pagina) {
  $("#currentPage").val(pagina);
  sec_contratos_solicitud_listar_solicitudes();
}

function sec_contratos_solicitud_listar_solicitudes() {
  let tipo_contrato_id = $("#tipo_contrato_id").val();
  console.log("tipo_contrato_id: ", tipo_contrato_id);

  let currentPage = $("#currentPage").val();
  let fecha_inicio = $("#search_fecha_inicio").val();
  let fecha_fin = $("#search_fecha_fin").val();
  let buscar_por = $("#buscar_por").val();
  let usuario_permisos = $("#usuario_permisos").val();
  let sec_id = $("#sec_id").val();
  let empresa = $("#cont_proveedor_param_empresa").val();
  let area = $("#cont_proveedor_param_area_solicitante").val();
  let moneda = $("#cont_proveedor_param_moneda").val();
  let ruc = $("#cont_proveedor_param_ruc").val();
  let razon_social = $("#cont_proveedor_param_razon_social").val();
  let id_departamento = $("#search_id_departamento").val();
  let id_provincia = $("#search_id_provincia").val();
  let id_distrito = $("#search_id_distrito").val();
  let nombre_tienda = $("#nombre_tienda").val();
  let nombre_agente = $("#nombre_agente").val();
  let estado_sol = $("#sec_sol_estado_solicitud").val();
  let estado_sol_v2 = $("#sec_sol_estado_solicitud_v2").val();
  let rc_tipo_contrato_id = $("#sec_sol_tipo_contrato").val();
  let estado_aprobacion = $("#sec_sol_estado_aprobacion").val();
  let fecha_inicio_aprobacion = $("#search_fecha_inicio_aprobacion").val();
  let fecha_fin_aprobacion = $("#search_fecha_fin_aprobacion").val();
  let director_aprobacion_id = $("#director_aprobacion_id").val();
  let menu_consultar = $("#menu_consultar").val();
  let area_id = $("#area_id").val();
  let cargo_id = $("#cargo_id").val();
  let menu_id = $("#menu_id").val();
  if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
    var fecha_inicio_date = new Date(fecha_inicio);
    var fecha_fin_date = new Date(fecha_fin);
    if (fecha_inicio_date > fecha_fin_date) {
      alertify.error(
        "La fecha inicio debe ser menor o igual a la fecha final",
        5
      );
      return false;
    }
  }
  currentPage = currentPage.length == 0 ? 1 : currentPage;
  let tipo_contrato = "listar_contrato_arredamiento_v2";
  switch (tipo_contrato_id) {
    case "1":
      tipo_contrato = "listar_contrato_arredamiento";
      break;
    case "2":
      tipo_contrato = "listar_contrato_proveedor";
      break;
    case "3":
      tipo_contrato = "listar_adenda_de_arrendamiento";
      break;
    case "4":
      tipo_contrato = "listar_adenda_de_proveedor";
      break;
    case "5":
      tipo_contrato = "listar_acuerdo_de_confidencialidad";
      break;
    case "6":
      tipo_contrato = "listar_contrato_de_agente";
      break;
    case "7":
      tipo_contrato = "listar_contrato_interno";
      break;
    case "8":
      tipo_contrato = "listar_contrato_adenda_interno";
      break;
    case "9":
      tipo_contrato = "listar_contrato_adenda_acuerdo_confidencialidad";
      break;
    case "10":
      tipo_contrato = "listar_contrato_adenda_agente";
      break;
    case "11":
      tipo_contrato = "listar_resolucion_contrato";
      break;
    case "12":
      tipo_contrato = "listar_contrato_arredamiento_v2";
      break;
    case "13":
      tipo_contrato = "listar_contrato_locacionservicio";
      break;
    case "14":
      tipo_contrato = "listar_contrato_mandato";
      break;
    case "15":
      tipo_contrato = "listar_contrato_mutuodinero";
      break;
    case "16":
      tipo_contrato = "listar_adenda_de_locacion_de_servicio";
      break;
    case "17":
      tipo_contrato = "listar_adenda_de_mandato";
      break;
    case "18":
      tipo_contrato = "listar_adenda_de_mutuo_dinero";
      break;
  }
  let data = {
    action: tipo_contrato,
    page: currentPage,
    fecha_inicio: fecha_inicio,
    fecha_fin: fecha_fin,
    buscar_por: buscar_por,
    usuario_permisos: usuario_permisos,
    sec_id: sec_id,
    empresa: empresa,
    area: area,
    moneda: moneda,
    ruc: ruc,
    razon_social: razon_social,
    nombre_tienda: nombre_tienda,
    nombre_agente: nombre_agente,
    estado_sol: estado_sol,
    estado_sol_v2: estado_sol_v2,
    rc_tipo_contrato_id: rc_tipo_contrato_id,
    estado_aprobacion: estado_aprobacion,
    fecha_inicio_aprobacion: fecha_inicio_aprobacion,
    fecha_fin_aprobacion: fecha_fin_aprobacion,
    director_aprobacion_id: director_aprobacion_id,
    id_departamento: id_departamento,
    id_provincia: id_provincia,
    id_distrito: id_distrito,
    menu_consultar: menu_consultar,
    area_id: area_id,
    cargo_id: cargo_id,
    menu_id: menu_id,
  };
  sec_contratos_solicitud_mostrar_excel();
  $.ajax({
    url: "sys/set_contrato_solicitud.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $("#block-resultado-tabla").html(resp);
      $(".tabla_contratos_para_filtro").dataTable({
        language: {
          decimal: "",
          emptyTable: "No existen registros",
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
        },
        ordering: false, // Deshabilita la ordenación
        drawCallback: function (settings) {
          $("#block-resultado-tabla").show();
        },
      });
    },
    error: function () {},
  });
}

function sec_contratos_solicitud_obtener_departamentos() {
  let select = $("[name='search_id_departamento']");
  $.ajax({
    url: "/sys/set_contrato_nuevo.php",
    type: "POST",
    data: {
      accion: "obtener_departamentos",
    },
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      var respuesta = JSON.parse(datos);
      $(select).find("option").remove().end();
      $(select).append('<option value="">- TODOS -</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $(select).append(opcion);
      });
    },
    error: function () {},
  });
}

function sec_contratos_solicitud_obtener_provincias() {
  $("#search_id_departamento option:selected").each(function () {
    let search_id_departamento = $("#search_id_departamento").val();
    if (search_id_departamento == "") {
      return false;
    }
    var data = {
      accion: "obtener_provincias_segun_departamento",
      departamento_id: search_id_departamento,
    };
    var array_provincias = [];
    $.ajax({
      url: "/sys/set_contrato_nuevo.php",
      type: "POST",
      data: data,
      beforeSend: function () {
        loading("true");
      },
      complete: function () {
        loading();
      },
      success: function (resp) {
        var respuesta = JSON.parse(resp);
        if (parseInt(respuesta.http_code) == 400) {
        }
        if (parseInt(respuesta.http_code) == 200) {
          array_provincias.push(respuesta.result);
          var html = '<option value="">- TODOS -</option>';
          for (var i = 0; i < array_provincias[0].length; i++) {
            html +=
              "<option value=" +
              array_provincias[0][i].id +
              ">" +
              array_provincias[0][i].nombre +
              "</option>";
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

function sec_contratos_solicitud_obtener_distritos() {
  $("#search_id_provincia option:selected").each(function () {
    let search_id_departamento = $("#search_id_departamento").val();
    let search_id_provincia = $("#search_id_provincia").val();
    if (search_id_provincia == "") {
      return false;
    }
    var data = {
      accion: "obtener_distritos_segun_provincia",
      provincia_id: search_id_provincia,
      departamento_id: search_id_departamento,
    };
    var array_distritos = [];
    $.ajax({
      url: "/sys/set_contrato_nuevo.php",
      type: "POST",
      data: data,
      beforeSend: function () {
        loading("true");
      },
      complete: function () {
        loading();
      },
      success: function (resp) {
        var respuesta = JSON.parse(resp);
        console.log(respuesta);
        if (parseInt(respuesta.http_code) == 400) {
        }

        if (parseInt(respuesta.http_code) == 200) {
          array_distritos.push(respuesta.result);

          var html = '<option value="">- TODOS -</option>';

          for (var i = 0; i < array_distritos[0].length; i++) {
            html +=
              "<option value=" +
              array_distritos[0][i].id +
              ">" +
              array_distritos[0][i].nombre +
              "</option>";
          }

          $("#search_id_distrito").html(html).trigger("change");

          setTimeout(function () {
            $("#search_id_distrito").select2("open");
          }, 500);

          return false;
        }
      },
      error: function () {},
    });
  });
}

function sec_contratos_solicitud_mostrar_excel() {
  let tipo_contrato_id = $("#tipo_contrato_id").val();
  let fecha_inicio = $("#search_fecha_inicio").val();
  let fecha_fin = $("#search_fecha_fin").val();
  let buscar_por = $("#buscar_por").val();
  let empresa = $("#cont_proveedor_param_empresa").val();
  let area = $("#cont_proveedor_param_area_solicitante").val();
  let moneda = $("#cont_proveedor_param_moneda").val();
  let ruc = $("#cont_proveedor_param_ruc").val();
  let razon_social = $("#cont_proveedor_param_razon_social").val();
  let id_departamento = $("#search_id_departamento").val();
  let id_provincia = $("#search_id_provincia").val();
  let id_distrito = $("#search_id_distrito").val();
  let nombre_tienda = $("#nombre_tienda").val();
  let estado_sol = $("#sec_sol_estado_solicitud").val();
  let estado_sol_v2 = $("#sec_sol_estado_solicitud_v2").val();
  let estado_aprobacion = $("#sec_sol_estado_aprobacion").val();
  let fecha_inicio_aprobacion = $("#search_fecha_inicio_aprobacion").val();
  let fecha_fin_aprobacion = $("#search_fecha_fin_aprobacion").val();
  let director_aprobacion_id = $("#director_aprobacion_id").val();
  let rc_tipo_contrato_id = $("#sec_sol_tipo_contrato").val();
  let area_id = $("#area_id").val();
  let cargo_id = $("#cargo_id").val();
  if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
    if (fecha_inicio > fecha_fin) {
      alertify.error(
        "La fecha inicio debe ser menor o igual a la fecha final",
        5
      );
      return false;
    }
  }
  $("#cont_contrato_excel").show();
  var url_parametros =
    "tipo_contrato_id=" +
    tipo_contrato_id +
    "&fecha_inicio=" +
    fecha_inicio +
    "&fecha_fin=" +
    fecha_fin +
    "&buscar_por=" +
    buscar_por +
    "&empresa=" +
    empresa +
    "&area=" +
    area +
    "&moneda=" +
    moneda +
    "&ruc=" +
    ruc +
    "&razon_social=" +
    razon_social +
    "&id_departamento=" +
    id_departamento +
    "&id_provincia=" +
    id_provincia +
    "&id_distrito=" +
    id_distrito +
    "&nombre_tienda=" +
    nombre_tienda +
    "&estado_sol=" +
    estado_sol +
    "&estado_sol_v2=" +
    estado_sol_v2 +
    "&estado_aprobacion=" +
    estado_aprobacion +
    "&fecha_inicio_aprobacion=" +
    fecha_inicio_aprobacion +
    "&fecha_fin_aprobacion=" +
    fecha_fin_aprobacion +
    "&director_aprobacion_id=" +
    director_aprobacion_id +
    "&rc_tipo_contrato_id=" +
    rc_tipo_contrato_id +
    "&area_id=" +
    area_id +
    "&cargo_id=" +
    cargo_id;
  document.getElementById("cont_contrato_excel").innerHTML =
    '<a  href="export.php?export=cont_contrato_solicitud&amp;type=lista&amp;' +
    url_parametros +
    '" class="btn btn-success export_list_btn" download="SOLICITUD DE CONTRATO.xls"><span class="fa fa-file-excel-o"></span> Exportar excel</a>';
}

function sec_contrato_solicitud_cancelar_solicitud_modal(
  tipo_de_solicitud,
  solicitud_id_temporal,
  nombre
) {
  $("#modal_cancelar_solicitud").modal("show");
  $("#form_cancelar_solicitud")[0].reset();
  setTimeout(function () {
    $("#tipo_de_solicitud").val(tipo_de_solicitud);
    $("#solicitud_id_temporal").val(solicitud_id_temporal);
    $("#modal_cancelar_solicitud_titulo").html("Cancelar Solicitud: " + nombre);
    $("#cancelado_motivo").focus();
  }, 200);
}

function sec_contrato_solicitud_cancelar_solicitud(contrato_id) {
  var tipo_de_solicitud = $("#tipo_de_solicitud").val().trim();
  var solicitud_id_temporal = $("#solicitud_id_temporal").val().trim();
  var cancelado_motivo = $("#cancelado_motivo").val().trim();

  if (cancelado_motivo == "") {
    alertify.error("Ingrese el motivo de la cancelación.", 5);
    $("#cancelado_motivo").focus();
    return false;
  }

  var contrato_id = $("#contrato_id_temporal").val();

  var data = {
    action: "cancelar_solicitud",
    tipo_de_solicitud: tipo_de_solicitud,
    solicitud_id_temporal: solicitud_id_temporal,
    cancelado_motivo: cancelado_motivo,
  };

  swal(
    {
      html: true,
      title: "Cancelar solicitud",
      text: "¿Está seguro de cancelar esta solicitud?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ed6b76",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SÍ, CANCELAR SOLICITUD",
      cancelButtonText: "DESISTIR",
      closeOnConfirm: false,
    },
    function () {
      auditoria_send({ proceso: "cancelar_solicitud", data: data });
      $.ajax({
        url: "/sys/set_contrato_solicitud.php",
        type: "POST",
        data: data,
        beforeSend: function () {
          loading("true");
        },
        complete: function () {
          loading();
        },
        success: function (resp) {
          $("#cont_proveedor_btn_buscar").click();
          var respuesta = JSON.parse(resp);
          auditoria_send({ proceso: "cancelar_solicitud", data: respuesta });
          console.log(respuesta);
          if (parseInt(respuesta.http_code) == 500) {
            swal({
              title: "Error al cancelar solicitud",
              text: respuesta.mensaje,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }

          if (parseInt(respuesta.http_code) == 200) {
            swal({
              title: "Cancelación exitosa",
              text: "La solicitud fue cancelada exitosamente",
              html: true,
              type: "success",
              timer: 6000,
              closeOnConfirm: false,
              showCancelButton: false,
            });

            $("#modal_cancelar_solicitud").modal("hide");
            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}
