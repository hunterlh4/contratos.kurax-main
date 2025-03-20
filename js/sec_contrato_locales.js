var array_locales_orden_detalle_id = [];
$.fn.dataTable.ext.type.order["custom-numeric-asc"] = function (a, b) {
  return extraerNumero(a) - extraerNumero(b);
};

$.fn.dataTable.ext.type.order["custom-numeric-desc"] = function (a, b) {
  return extraerNumero(b) - extraerNumero(a);
};

function extraerNumero(texto) {
  let num = texto.replace(/\D/g, "");
  return parseInt(num, 10) || 0;
}

function agregar_local_a_lista(orden_detalle_id) {
  if (array_locales_orden_detalle_id.includes(orden_detalle_id) === false) {
    array_locales_orden_detalle_id.push(orden_detalle_id);
  }
  console.log(array_locales_orden_detalle_id);
  actualizar_tabla_lista_locales();
}

function actualizar_tabla_lista_locales() {
  if (array_locales_orden_detalle_id.length > 0) {
    var data = {
      accion: "obtener_locales_por_ids",
      orden_detalle_ids: JSON.stringify(array_locales_orden_detalle_id),
    };
    auditoria_send({
      proceso: "obtener_locales_por_ids",
      data: data,
    });
    $.ajax({
      url: "/sys/set_contrato_locales.php",
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
          console.log("Error");
        }
        if (parseInt(respuesta.http_code) == 200) {
          $("#tbl_ordenes_lista").html(respuesta.result);
        }
      },
      error: function () {},
    });
  } else {
    $("#tbl_ordenes_lista").html(
      '<table class="table table-bordered"><tbody><tr><td align="center">Agregar locales en el buscador ubicado al lado derecho <span class="fa fa-arrow-right"></span></td></tr></tbody></table>'
    );
  }
}

function remover_local_de_la_lista(orden_detalle_id) {
  console.log(array_locales_orden_detalle_id);
  const index = array_locales_orden_detalle_id.indexOf(orden_detalle_id);
  if (index > -1) {
    array_locales_orden_detalle_id.splice(index, 1);
  }
  console.log(array_locales_orden_detalle_id);
  actualizar_tabla_lista_locales();
}

function buscar_ordenes_varios_locales() {
  var nombre_local = $("#varios_locales_nombre_local").val();
  var periodo = $("#varios_locales_periodo").val();
  var tipo_renta = $("#varios_locales_tipo_renta").val();
  var numero_registros_a_mostrar = $(
    "#varios_locales_numero_registros_a_mostrar"
  ).val();
  if (periodo == 0) {
    return false;
  }
  var data = {
    accion: "obtener_ordenes_de_pago_varios_locales",
    nombre_local: nombre_local,
    periodo: periodo,
    tipo_renta: tipo_renta,
    numero_registros_a_mostrar: numero_registros_a_mostrar,
  };
  auditoria_send({
    proceso: "obtener_ordenes_de_pago_varios_locales",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_locales.php",
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
        console.log("Error");
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#tbl_ordenes_busqueda").html(respuesta.result);
      }
    },
    error: function () {},
  });
}

function modal_agregar_pago(modal_agregar_pago) {
  $("#modal_agregar_pago").modal("show");
  $("#orden_detalle_id").val(modal_agregar_pago);
  $("#modal_ordenes_de_pago").modal("hide");
  $("#cont_locales_fecha_pago").focus();
}

var tabla;

function sec_contrato_locales() {
  $(".select2").select2({
    width: "100%",
  });
  sec_contrato_locales_obtener_departamentos();
  $(".limpiar_input").click(function () {
    $("#" + $(this).attr("limpiar")).val("");
  });
  $(".limpiar_select2").click(function () {
    $("#" + $(this).attr("limpiar"))
      .select2()
      .val("")
      .trigger("change");
    buscarContratoPorParametros();
  });
  $(".cont_locales_datepicker").datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
  });
  $(".change-contrato-locales-firmado").on("select2:select", function (e) {
    buscarContratoPorParametros();
  });
  // INICIO BLOQUER LOS BOTONES AL CARGAR
  $("#btn_ordenes_de_pago").prop("disabled", true);
  // FIN BLOQUEAR BOTONES AL CARGAR
  // INICIO ACTIVAR Y DESACTIVAR BOTONES DE ACUERDO A LA SELECCION DE UN REGISTRO EN LA TABLA
  $("#cont_locales_datatable").on("click", "tr", function () {
    setTimeout(function () {
      if ($(".selected").attr("id")) {
        $("#btn_ordenes_de_pago").prop("disabled", false);
      } else {
        $("#btn_ordenes_de_pago").prop("disabled", true);
      }
    }, 10);
  });
  // FIN ACTIVAR Y DESACTIVAR BOTONES DE ACUERDO A LA SELECCION DE UN REGISTRO EN LA TABLA
  $("#btn_generar_ordenes_de_pago").click(function () {
    if ($(".selected").attr("id")) {
      var contrato_id = $(".selected").attr("id");
      var data = {
        accion: "generar_provision_contable_manual",
        contrato_id: contrato_id,
      };
      auditoria_send({
        proceso: "generar_provision_contable_manual",
        data: data,
      });
      $.ajax({
        url: "/sys/set_contrato_locales.php",
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
            console.log("Error");
          }
          if (parseInt(respuesta.http_code) == 200) {
            console.log("Finalizo el proceso OK");
          }
        },
        error: function () {},
      });
    } else {
      alertify.error("Debe de seleccionar un registro de la tabla");
    }
  });
  $("#btn_ordenes_de_pago").click(function () {
    if ($(".selected").attr("id")) {
      $("#div_tabla_ordenes").html("");
      var contrato_id = $(".selected").attr("id");
      $("#contrato_id_temp").val(contrato_id);
      $("#modal_ordenes_de_pago").modal("show");
      var data = {
        accion: "obtener_ordenes_de_pago",
        contrato_id: contrato_id,
      };
      auditoria_send({
        proceso: "obtener_ordenes_de_pago",
        data: data,
      });
      $.ajax({
        url: "/sys/set_contrato_locales.php",
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
            console.log("Error");
          }
          if (parseInt(respuesta.http_code) == 200) {
            $("#div_tabla_ordenes").html(respuesta.result);
          }
        },
        error: function () {},
      });
      var data = {
        accion: "obtener_nombre_del_local",
        contrato_id: contrato_id,
      };
      auditoria_send({
        proceso: "obtener_nombre_del_local",
        data: data,
      });
      $.ajax({
        url: "/sys/set_contrato_locales.php",
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
            console.log("Error");
          }
          if (parseInt(respuesta.http_code) == 200) {
            $("#nombre_del_local").html(respuesta.result);
          }
        },
        error: function () {},
      });
    } else {
      alertify.error("Debe de seleccionar un registro de la tabla");
    }
  });
  $("#btn_comprobante_varios_locales").click(function () {
    $("#modal_comprobante_varios_locales").modal({
      backdrop: "static",
      keyboard: false,
    });
    array_locales_orden_detalle_id = [];
    $("#form_comprobante_de_pago_varios_locales")[0].reset();
    $("#form_buscador_locales_orden_detalle")[0].reset();
    $("#tbl_ordenes_lista").html(
      '<table class="table table-bordered"><tbody><tr><td align="center">Agregar locales en el buscador ubicado al lado derecho <span class="fa fa-arrow-right"></span></td></tr></tbody></table>'
    );
    $("#tbl_ordenes_busqueda").html(
      '<table class="table table-bordered"><tbody><tr><td align="center"><span class="fa fa-arrow-up"></span> Ingrese el nombre del local y el periodo</td></tr></tbody></table>'
    );
  });
  $("#varios_locales_periodo").change(function () {
    buscar_ordenes_varios_locales();
  });
  $("#varios_locales_tipo_renta").change(function () {
    buscar_ordenes_varios_locales();
  });
  $("#varios_locales_numero_registros_a_mostrar").change(function () {
    buscar_ordenes_varios_locales();
  });
  $("#varios_locales_nombre_local").keyup(function (e) {
    var nombre_local = $("#varios_locales_nombre_local").val();
    var num_digitos_nombre_local = nombre_local.length;
    buscar_ordenes_varios_locales();
  });
  mostrar_switch_contrato_locales();
  sec_contrato_locales_listar_contratos_datatable();
  $("#cont_locales_datatable")
    .DataTable()
    .on("draw", function () {
      mostrar_switch_contrato_locales();
    });

  $("#btn_limpiar_filtros_de_busqueda").click(function () {
    $("#search_id_empresa").select2().val("").trigger("change");
    $("#search_nombre_tienda").val("");
    $("#search_centro_costos").val("");
    $("#search_moneda").select2().val("").trigger("change");
    $("#search_id_departamento").select2().val("").trigger("change");
    $("#search_id_provincia").select2().val("").trigger("change");
    $("#search_id_distrito").select2().val("").trigger("change");
    $("#fecha_inicio_solicitud").val("");
    $("#fecha_fin_solicitud").val("");
    $("#fecha_inicio_inicio").val("");
    $("#fecha_fin_inicio").val("");
    $("#fecha_inicio_suscripcion").val("");
    $("#fecha_fin_suscripcion").val("");
  });
}
var claseTipoAlertas = {
  alertaSuccess: 1,
  alertaInfo: 2,
  alertaWarning: 3,
  alertaDanger: 4,
};

function RecuperarClaseAlerta(valor) {
  var clase = "";
  switch (valor) {
    case 1:
      clase = "alert alert-success alerta-dismissible";
      break;
    case 2:
      clase = "alert alert-info alerta-dismissible";
      break;
    case 3:
      clase = "alert alert-warning alerta-dismissible";
      break;
    case 4:
      clase = "alert alert-danger alerta-dismissible";
      break;
  }
  return clase;
}

function tipoFont(valor) {
  var clase = "";
  switch (valor) {
    case 1:
    case 2:
      clase = "<i class='fa fa-info-circle fa-2x'></i>";
      break;
    case 3:
    case 4:
      clase = "<i class='fa fa-exclamation-triangle fa-2x'></i>";
      break;
  }
  return clase;
}
//ESTE ES PARA LAS ALERTAS
var mensajeAlerta = function (titulo, mensaje, tipoClase, controlDiv) {
  var clase = RecuperarClaseAlerta(tipoClase);
  var font = tipoFont(tipoClase);
  var control = $(controlDiv);
  var divMensaje = "<div class = '" + clase + "' role = 'alert'>";
  divMensaje +=
    "<button type = 'button' class = 'close' data-dismiss = 'alert' aria-label = 'close'>";
  divMensaje += "<span aria-hidden = 'true'>&times;</span>";
  divMensaje += "</button>";
  divMensaje += font + "<strong>" + titulo + "</strong><br/>" + mensaje;
  divMensaje += "</div>";
  control.empty();
  control
    .hide()
    .html(divMensaje.toString())
    .fadeIn(2000)
    .delay(2000)
    .fadeOut("slow");
};

function buscarContratoPorParametros() {
  $("#cont_locales_alerta_filtrar_por").hide();
  $("#cont_contrato_div_tabla").hide();
  if ($("#select_campo_busqueda").val() == "0") {
    sec_contrato_locales_listar_contratos_datatable();
  } else {
    if ($("#cont_locales_fecha_inicio").val() == "") {
      mensajeAlerta(
        "Advertencia:",
        "Tiene que ingresar la fecha de inicio para la busqueda correspondiente.",
        claseTipoAlertas.alertaWarning,
        $("#cont_locales_alerta_filtrar_por")
      );
      return;
    } else if ($("#cont_locales_fecha_fin").val() == "") {
      mensajeAlerta(
        "Advertencia:",
        "Tiene que ingresar la fecha de fin para la busqueda correspondiente.",
        claseTipoAlertas.alertaWarning,
        $("#cont_locales_alerta_filtrar_por")
      );
      return;
    } else {
      sec_contrato_locales_listar_contratos_datatable();
    }
  }
}

function sec_contrato_locales_listar_contratos_datatable() {
  $("#cont_contrato_div_tabla").show();
  var id_empresa = $("#search_id_empresa").val();
  var nombre_tienda = $("#search_nombre_tienda").val();
  var centro_costos = $("#search_centro_costos").val();
  var moneda = $("#search_moneda").val();
  var id_departamento = $("#search_id_departamento").val();
  var id_provincia = $("#search_id_provincia").val();
  var id_distrito = $("#search_id_distrito").val();
  var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
  var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
  var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
  var fecha_fin_inicio = $("#fecha_fin_inicio").val();
  var fecha_inicio_suscripcion = $("#fecha_inicio_suscripcion").val();
  var fecha_fin_suscripcion = $("#fecha_fin_suscripcion").val();
  var etapa = $("#search_etapa").val();
  if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
    var fecha_inicio_date = new Date(fecha_inicio_solicitud);
    var fecha_fin_date = new Date(fecha_fin_solicitud);
    if (fecha_inicio_date > fecha_fin_date) {
      alertify.error(
        "La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ",
        5
      );
      return false;
    }
  }
  if (fecha_inicio_inicio.length > 0 && fecha_fin_inicio.length > 0) {
    var fecha_inicio_date = new Date(fecha_inicio_inicio);
    var fecha_fin_date = new Date(fecha_fin_inicio);
    if (fecha_inicio_date > fecha_fin_date) {
      alertify.error(
        "La fecha inicio desde debe ser menor o igual a la fecha inicio hasta",
        5
      );
      return false;
    }
  }
  if (fecha_inicio_suscripcion.length > 0 && fecha_fin_suscripcion.length > 0) {
    var fecha_inicio_date = new Date(fecha_inicio_suscripcion);
    var fecha_fin_date = new Date(fecha_fin_suscripcion);
    if (fecha_inicio_date > fecha_fin_date) {
      alertify.error(
        "La fecha de suscripción desde debe ser menor o igual a la fecha de suscripción hasta",
        5
      );
      return false;
    }
  }
  var data = {
    accion: "cont_listar_locales",
    id_empresa: id_empresa,
    nombre_tienda: nombre_tienda,
    centro_costos: centro_costos,
    moneda: moneda,
    id_departamento: id_departamento,
    id_provincia: id_provincia,
    id_distrito: id_distrito,
    fecha_inicio_solicitud: fecha_inicio_solicitud,
    fecha_fin_solicitud: fecha_fin_solicitud,
    fecha_inicio_inicio: fecha_inicio_inicio,
    fecha_fin_inicio: fecha_fin_inicio,
    fecha_inicio_suscripcion: fecha_inicio_suscripcion,
    fecha_fin_suscripcion: fecha_fin_suscripcion,
    etapa: etapa,
  };
  tabla = $("#cont_locales_datatable")
    .dataTable({
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
      aProcessing: true,
      aServerSide: true,
      createdRow: function (row, data) {
        var id = data[0];
        $(row).prop("id", id).data("id", id);
      },
      ajax: {
        url: "/sys/set_contrato_locales.php",
        data: data,
        type: "POST",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      bDestroy: true,
      aLengthMenu: [10, 20, 30, 40, 50, 100],
      //   order: [1, "desc"],
      columnDefs: [
        {
          targets: 0, // Aplica a la columna 1 (índice 0)
          type: "custom-numeric", // Usa el tipo de orden personalizado
        },
      ],
      order: [[0, "desc"]],
      select: {
        style: "single",
      },
      rowCallback: function (row, data) {
        if (data[15] == "2") {
          //estado
          $(row).css("background-color", "red");
          $(row).css("color", "white");
        }
        // Puedes agregar más condiciones aquí para otros valores de status
      },
    })
    .DataTable();
  mostrar_switch_contrato_locales();
  sec_contrato_locales_mostrarReporteExcel_arrendamiento();
  $("#cont_locales_datatable tfoot th:lt(10)").each(function () {
    var title = $(this).text();
    $(this).html(
      '<input type="text" style="width:100%;" placeholder="Buscar ' +
        title +
        '" />'
    );
  });
  var table = $("#cont_locales_datatable").DataTable();
  table.columns().every(function () {
    var that = this;
    $("input", this.footer()).on("keyup change", function () {
      if (that.search() !== this.value) {
        that.search(this.value).draw();
      }
    });
  });
}

function mostrar_switch_contrato_locales() {
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
}

function sec_contrato_locales_mostrarReporteExcel_arrendamiento() {
  var id_empresa = $("#search_id_empresa").val();
  var nombre_tienda = $("#search_nombre_tienda").val();
  var centro_costos = $("#search_centro_costos").val();
  var moneda = $("#search_moneda").val();
  var id_departamento = $("#search_id_departamento").val();
  var id_provincia = $("#search_id_provincia").val();
  var id_distrito = $("#search_id_distrito").val();
  var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
  var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
  var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
  var fecha_fin_inicio = $("#fecha_fin_inicio").val();
  var fecha_inicio_suscripcion = $("#fecha_inicio_suscripcion").val();
  var fecha_fin_suscripcion = $("#fecha_fin_suscripcion").val();
  var etapa = $("#search_etapa").val();
  document.getElementById("cont_locales_excel_arrendamiento").innerHTML =
    '<a href="export.php?export=cont_contrato_arrendamiento&amp;type=lista&amp;' +
    "&amp;fecha_inicio_solicitud=" +
    fecha_inicio_solicitud +
    "&amp;fecha_fin_solicitud=" +
    fecha_fin_solicitud +
    "&amp;fecha_inicio_inicio=" +
    fecha_inicio_inicio +
    "&amp;fecha_fin_inicio=" +
    fecha_fin_inicio +
    "&amp;fecha_inicio_suscripcion=" +
    fecha_inicio_suscripcion +
    "&amp;fecha_fin_suscripcion=" +
    fecha_fin_suscripcion +
    "&amp;id_empresa=" +
    id_empresa +
    "&amp;nombre_tienda=" +
    nombre_tienda +
    "&amp;centro_costos=" +
    centro_costos +
    "&amp;moneda=" +
    moneda +
    "&amp;id_departamento=" +
    id_departamento +
    "&amp;id_provincia=" +
    id_provincia +
    "&amp;id_distrito=" +
    id_distrito +
    "&amp;etapa=" +
    etapa +
    '" class="btn btn-success" download="contrato_locales.xls"><span class="fa fa-file-excel-o"></span> Exportar excel</a>';
}

function guardar_comprobante_pago(orden_detalle_id) {
  var fecha_pago = $("#cont_locales_fecha_pago").val();
  var orden_detalle_id = $("#orden_detalle_id").val();
  var contrato_id = $("#contrato_id_temp").val();
  var dataForm = new FormData($("#form_comprobante_de_pago")[0]);
  dataForm.append("accion", "guardar_comprobante_pago");
  dataForm.append("fecha_pago", fecha_pago);
  dataForm.append("orden_detalle_id", orden_detalle_id);
  dataForm.append("contrato_id", contrato_id);
  auditoria_send({
    proceso: "guardar_comprobante_pago",
    data: dataForm,
  });
  $.ajax({
    url: "sys/set_contrato_locales.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (data) {
      $("#modal_agregar_pago").modal("hide");
      $("#btn_ordenes_de_pago").click();
      return false;
    },
    complete: function () {
      loading(false);
    },
  });
}

function guardar_comprobante_pago_varios_locales() {
  var fecha_pago = $("#cont_locales_fecha_pago_varios_locales").val();
  var dataForm = new FormData($("#form_comprobante_de_pago_varios_locales")[0]);
  dataForm.append("accion", "guardar_comprobante_pago_varios_locales");
  dataForm.append("fecha_pago", fecha_pago);
  dataForm.append(
    "locales_orden_detalle_id",
    JSON.stringify(array_locales_orden_detalle_id)
  );
  auditoria_send({
    proceso: "guardar_comprobante_pago_varios_locales",
    data: dataForm,
  });
  $.ajax({
    url: "sys/set_contrato_locales.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (data) {
      $("#modal_comprobante_varios_locales").modal("hide");
      return false;
    },
    complete: function () {
      loading(false);
    },
  });
}

function contrato_alerta(id) {
  limpiar_tr_modal_alerta();
  $("#configurarAlerta").modal("show");
  var data = {
    accion: "obtener_dato_contrato",
    parametro: id,
  };
  loading(true);
  $.ajax({
    url: "/sys/set_contrato_locales.php",
    type: "POST",
    data: data,
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      $("#condicion_economica_id").val(respuesta.condicion_economica_id);
      var num_dias_alerta = respuesta.num_alerta_vencimiento;
      var txt_num_dias_alerta = "";
      if (num_dias_alerta == 0) {
        txt_num_dias_alerta = "No existe numero de dias para la alerta";
      } else {
        txt_num_dias_alerta =
          num_dias_alerta + " dias antes de la fecha de vencimiento";
      }
      contenido_modal_alerta.innerHTML =
        '<tr><td class="text-center">' +
        respuesta.contrato_id +
        "</td><td>" +
        respuesta.ubicacion +
        '</td><td class="text-center">' +
        respuesta.fecha_inicio +
        '</td><td class="text-center">' +
        respuesta.fecha_fin +
        '</td><td class="text-center">' +
        txt_num_dias_alerta +
        "</td></tr>";
      loading();
      return true;
    },
    error: function (textStatus) {
      console.log(
        "La solicitud obtener datos para alerta a fallado: " + textStatus
      );
    },
  });
}

function limpiar_tr_modal_alerta() {
  $("#condicion_economica_id").val("");
  contenido_modal_alerta.innerHTML =
    '<tr><td class="text-center" id="modal_id_contrato"></td><td id="modal_nombre_tienda"></td><td id="modal_ubicaion_inmueble"></td><td class="text-center" id="modal_fecha_inicio"></td><td class="text-center" id="modal_fecha_fin"></td></tr>';
}

function registrar_alerta() {
  var id_condicion = $("#condicion_economica_id").val();
  var numero_alerta = $("#numAlerta").val();
  if (numero_alerta == 0 || numero_alerta == "") {
    mensajeAlerta(
      "Advertencia:",
      "Tiene que ingresar la cantidad de días para la alerta a este contrato.",
      claseTipoAlertas.alertaWarning,
      $("#divMensajeAlerta")
    );
    return;
  }
  var data = {
    accion: "actualizar_alerta_contrato",
    condicion_economica_id: id_condicion,
    numAlerta: numero_alerta,
  };
  loading(true);
  $.ajax({
    url: "/sys/set_contrato_locales.php",
    type: "POST",
    data: data,
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      if (respuesta == "ok") {
        $("#configurarAlerta").modal("hide");
        limpiar_tr_modal_alerta();
        m_reload();
      } else {
        mensajeAlerta(
          "Advertencia:",
          "Ocurrio un error, vuelva a registrar, si el problema persiste consulte con SOPORTE TI.",
          claseTipoAlertas.alertaWarning,
          $("#divMensajeAlerta")
        );
        return;
      }
      loading();
      return true;
    },
    error: function (textStatus) {
      console.log(
        "La solicitud para actualizar alerta contrato a fallado: " + textStatus
      );
    },
  });
}

function m_reload() {
  console.log("m_reload:reload");
  window.location.reload();
}

function soloNumeros(e) {
  var val = document.all;
  var key = val ? e.keyCode : e.which;
  if (key > 31 && (key < 48 || key > 57)) {
    if (val) {
      window.event.keyCode = 0;
    } else {
      e.stopPropagation();
      e.preventDefault();
    }
  }
}
var ruta_archivo;
var nombre_archivo;

function ver_comprobante(nombre, extension, ruta) {
  ruta_archivo = ruta;
  nombre_archivo = nombre;
  var altura_pagina = window.screen.height - window.screen.height * 0.23;
  if (extension == "pdf") {
    var htmlModal =
      '<iframe src="' +
      ruta_archivo +
      nombre_archivo +
      '" class="col-xs-12 col-md-12 col-sm-12" height="' +
      altura_pagina +
      '"></iframe>';
    $("#div_modal_visor_pdf").html(htmlModal);
    $("#modal_visor_pdf").modal({
      backdrop: "static",
      keyboard: false,
    });
  } else {
    view_image_full_screen();
  }
}

function view_image_full_screen() {
  var image = new Image();
  image.src = ruta_archivo + nombre_archivo;
  var viewer = new Viewer(image, {
    hidden: function () {
      viewer.destroy();
    },
  });
  viewer.show();
}

function sec_contrato_locales_obtener_departamentos() {
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

function sec_contrato_locales_obtener_provincias() {
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

function sec_contrato_locales_obtener_distritos() {
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

function sec_rep_vig_cambioestado(value, id) {
  var data = {
    accion: "cambio_estado_vigencia",
    valor_id: value,
    contrato_id: id,
  };
  $.ajax({
    url: "/sys/set_contrato_locales.php",
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
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        swal("Estado Cambiado", "Registrado", "success");
        return false;
      }
    },
    error: function () {},
  });
}

function generar_provision_manual(contrato_id) {
  var data = {
    accion: "generar_provision_contable_manual",
    contrato_id: contrato_id,
  };
  auditoria_send({
    proceso: "generar_provision_contable_manual",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_locales.php",
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
        console.log("Error");
      }
      if (parseInt(respuesta.http_code) == 200) {
        console.log("Finalizo el proceso OK");
      }
    },
    error: function () {},
  });
}
