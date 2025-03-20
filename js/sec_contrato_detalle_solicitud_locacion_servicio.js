var claseTipoAlertas = {
  alertaSuccess: 1,
  alertaInfo: 2,
  alertaWarning: 3,
  alertaDanger: 4,
};

$("#estado_solicitud").change(function () {
  $("#estado_solicitud option:selected").each(function () {
    estado_solicitud = $(this).val();
    if (estado_solicitud == 4) {
      $("#divNoAplica").show();
    } else {
      $("#divNoAplica").hide();
    }
  });
});

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
    .delay(8000)
    .fadeOut("slow");
};
var contrato_detalle_id_archivo = 0;

function moda_subir_archivo_req_solicitud_arrendamiento(
  nombre_documento,
  id_archivo,
  id_tipo_archivo,
  contrato_detalle_id = 0
) {
  $("#moda_subir_archivo_req_solicitud_arrendamiento h4").html(
    "Agregar archivo - " + nombre_documento
  );

  $("#moda_subir_archivo_req_solicitud_arrendamiento #id_archivo").val(
    +id_archivo
  );
  $("#moda_subir_archivo_req_solicitud_arrendamiento #id_tipo_archivo").val(
    +id_tipo_archivo
  );
  $(
    "#moda_subir_archivo_req_solicitud_arrendamiento #id_representante_legal"
  ).val("");
  contrato_detalle_id_archivo = contrato_detalle_id;

  $("#modal_subir_arch_contrato_detalle_id").val(contrato_detalle_id);
  $("#moda_subir_archivo_req_solicitud_arrendamiento").modal("show");
}
function moda_reemplazar_archivo_req_solicitud_arrendamiento(
  nombre_documento,
  id_archivo,
  id_tipo_archivo,
  contrato_detalle_id = 0
) {
  $("#moda_subir_archivo_req_solicitud_arrendamiento h4").html(
    "Reemplazar archivo - " + nombre_documento
  );
  $("#moda_subir_archivo_req_solicitud_arrendamiento #id_archivo").val(
    +id_archivo
  );
  $("#moda_subir_archivo_req_solicitud_arrendamiento #id_tipo_archivo").val(
    +id_tipo_archivo
  );
  $(
    "#moda_subir_archivo_req_solicitud_arrendamiento #id_representante_legal"
  ).val("");
  contrato_detalle_id_archivo = contrato_detalle_id;
  $("#modal_subir_arch_contrato_detalle_id").val(contrato_detalle_id);
  $("#moda_subir_archivo_req_solicitud_arrendamiento").modal("show");
}

function moda_subir_archivo_req_solicitud_proveedor_representante_legal(
  nombre_documento,
  id_archivo,
  id_tipo_archivo,
  id_representante_legal
) {
  $("#moda_subir_archivo_req_solicitud_arrendamiento h4").html(
    "Agregar archivo - " + nombre_documento
  );

  $("#moda_subir_archivo_req_solicitud_arrendamiento #id_archivo").val(
    +id_archivo
  );
  $("#moda_subir_archivo_req_solicitud_arrendamiento #id_tipo_archivo").val(
    +id_tipo_archivo
  );
  $(
    "#moda_subir_archivo_req_solicitud_arrendamiento #id_representante_legal"
  ).val(+id_representante_legal);

  $("#moda_subir_archivo_req_solicitud_arrendamiento").modal("show");
}

$("#contrato_observaciones").keyup(function () {
  $("#txtCantidadCaracteresComentario").text(500 - $(this).val().length);
});

$("#cont_detalle_solicitudv2_param_texto_motivo_giro").keyup(function () {
  $("#cont_detalle_solicitudv2_param_text_giro_cantidad_caracteres").text(
    200 - $(this).val().length
  );
});

function sec_contrato_detalle_solicitud_locacionservicio() {
  var midocu = "";
  sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio();
  sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_gerencia();
  $(".select2").select2({ width: "100%" });
  setTimeout(function () {
    sec_contrato_Detalle_solicitud_collapse_contrato("show");
  }, 500);
  $(".sec_contrato_detalle_solicitudv2_datepicker")
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
      // localStorage.setItem($(this).atrr("id"),)
    });

  $(".fecha_detalle_proveedor_datepicker")
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
      // localStorage.setItem($(this).atrr("id"),)
    });

  $(".fecha_detalle_arrendemiento_datepicker")
    .datepicker({
      dateFormat: "dd/mm/yy",
      changeMonth: true,
      changeYear: true,
    })
    .on("change", function (ev) {
      $(this).datepicker("hide");
      var newDate = $(this).datepicker("getDate");
      $("input[data-real-date=" + $(this).attr("id") + "]").val(
        $.format.date(newDate, "dd/mm/yy")
      );
    });

  // INICIO DECLARACION DE MASK
  $(".area_cuadrada").mask("000");
  $(".num_suministro").mask("000000000", {
    translation: { 0: { pattern: /[0-9-]/ } },
  });

  $(".money").mask("00,000.00", { reverse: true });
  $(".vigencia_meses").mask("00");

  $(".num_ruc").mask("00000000000");
  // FIN DECLARACION DE MASK

  // INICIO OTROS EVENTOS
  $("#editar_solicitud_valor_decimal").on({
    focus: function (event) {
      $(event.target).select();
    },
    blur: function (event) {
      if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
        $(event.target).val(
          parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
        );
        $(event.target).val(function (index, value) {
          return value
            .replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      } else {
        $(event.target).val("0.00");
      }
    },
  });
  // FIN OTROS EVENTOS

  // INICIO OBTENER LAS FORMAS DE PAGO
  setTimeout(function () {
    var tipo_contrato_id_temporal = $("#tipo_contrato_id_temporal").val();
    if (tipo_contrato_id_temporal == "1") {
      sec_contrato_detalle_solicitudv2_emails_enviados_formato_de_pago();
      sec_contrato_detalle_solicitudv2_obtener_opciones(
        "obtener_tipo_mes_adelanto",
        $("[name='num_periodo_id']")
      );
      // NIF16
      sec_contrato_nuevo_obtener_opciones(
        "obtener_tipo_periodicidad",
        $("[name='modal_if_tipo_periodicidad_id']")
      );
      sec_contrato_nuevo_obtener_opciones(
        "obtener_tipo_anio_mes",
        $("[name='modal_if_tipo_anio_mes']")
      );
      sec_contrato_nuevo_obtener_opciones(
        "obtener_tipo_aplicacion",
        $("[name='modal_if_tipo_aplicacion_id']")
      );
      sec_contrato_nuevo_obtener_opciones(
        "obtener_meses",
        $("[name='modal_ce_mes']")
      );
      sec_contrato_nuevo_obtener_opciones(
        "obtener_meses",
        $("[name='modal_ce_mes']")
      );

      sec_contrato_nuevo_obtener_opciones(
        "obtener_empresa_at",
        $("[name='modal_aden_esc_empresa_id']")
      );
    } else if (tipo_contrato_id_temporal == "2") {
      sec_contrato_detalle_solicitudv2_obtener_opciones(
        "obtener_monedas",
        $("[name='moneda_id']")
      );
      sec_contrato_detalle_solicitudv2_obtener_opciones(
        "obtener_forma_pago",
        $("[name='forma_pago']")
      );
      sec_contrato_detalle_solicitudv2_obtener_opciones(
        "obtener_tipo_comprobante",
        $("[name='tipo_comprobante']")
      );
    }
    if (sub_sec_id == "detalle_agente") {
      sec_contrato_detalle_agente_vigencia();
    }
  }, 100);
  // FIN OBTENER LAS FORMAS DE PAGO

  // INICIO CONTRAPRESTACION
  $("#moneda_id").change(function () {
    $("#moneda_id option:selected").each(function () {
      moneda_id = $(this).val();
      if (moneda_id != 0) {
        setTimeout(function () {
          $("#subtotal").focus();
        }, 200);
      }
    });
  });

  $("#check_collapse").change(function (event) {
    if (event.currentTarget.checked) {
      sec_contrato_Detalle_solicitud_collapse_contrato("hide");
    } else {
      sec_contrato_Detalle_solicitud_collapse_contrato("show");
    }
  });

  $("#forma_pago").change(function () {
    $("#forma_pago option:selected").each(function () {
      forma_pago = $(this).val();
      if (forma_pago != 0) {
        setTimeout(function () {
          $("#tipo_comprobante").focus();
          $("#tipo_comprobante").select2("open");
        }, 200);
      }
    });
  });

  $("#tipo_comprobante").change(function () {
    $("#tipo_comprobante option:selected").each(function () {
      tipo_comprobante = $(this).val();
      if (tipo_comprobante != 0) {
        setTimeout(function () {
          $("#plazo_pago").focus();
        }, 200);
      }
    });
  });
  // FIN CONTRAPRESTACION

  // INICIO EVENTOS CONTRATO DE PROVEEDOR - CONTRAPRESTACION
  $("#subtotal").on({
    focus: function (event) {
      $(event.target).select();
    },
    blur: function (event) {
      if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
        $(event.target).val(
          parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
        );
        $(event.target).val(function (index, value) {
          return value
            .replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      } else {
        $(event.target).val("0.00");
      }
    },
  });

  $("#igv").on({
    focus: function (event) {
      $(event.target).select();
    },
    blur: function (event) {
      if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
        $(event.target).val(
          parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
        );
        $(event.target).val(function (index, value) {
          return value
            .replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      } else {
        $(event.target).val("0.00");
      }
    },
  });

  $("#monto").on({
    focus: function (event) {
      $(event.target).select();
    },
    change: function (event) {
      if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
        $(event.target).val(
          parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
        );
        $(event.target).val(function (index, value) {
          return value
            .replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      } else {
        $(event.target).val("0.00");
      }
    },
  });
  // FIN EVENTOS CONTRATO DE PROVEEDOR - CONTRAPRESTACION

  $("#fecha_vencimiento_indefinida_id").change(function () {
    $("#fecha_vencimiento_indefinida_id option:selected").each(function () {
      fecha_vencimiento_indefinida_id = $(this).val();
      if (fecha_vencimiento_indefinida_id == 1) {
        $("#div_fecha_de_vencimiento").hide();
      } else {
        $("#div_fecha_de_vencimiento").show();
        $(
          "#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"
        ).focus();
      }
    });
  });

  $("#btn_agregar_propietario_agente").click(function () {
    sec_contrato_detalle_agente_solicitud_guardar_propietario(
      "guardar_propietario_agente"
    );
  });

  $("#btn_agregar_propietario").click(function () {
    sec_contrato_detalle_solicitudv2_guardar_propietario("guardar_propietario");
  });

  // INICIO CHANGE INCREMENTOS
  $("#contrato_incrementos_monto_o_porcentaje").on({
    focus: function (event) {
      $(event.target).select();
    },
    change: function (event) {
      if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
        $(event.target).val(
          parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
        );
        $(event.target).val(function (index, value) {
          return value
            .replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      } else {
        $(event.target).val("0.00");
      }
    },
  });

  $("#contrato_incrementos_en").change(function () {
    $("#contrato_incrementos_en option:selected").each(function () {
      incrementos_en = $(this).val();
      if (incrementos_en != 0) {
        setTimeout(function () {
          $("#contrato_incrementos_continuidad").select2("open");
        }, 200);
      }
    });
  });

  $("#contrato_incrementos_continuidad").change(function () {
    $("#contrato_incrementos_continuidad option:selected").each(function () {
      continuidad_id = $(this).val();

      if (continuidad_id == 3) {
        $("#titulo_incremento_a_partir").html("");
        $("#titulo_incremento_a_partir").hide();
        $("#td_contrato_incrementos_a_partir_de_año").hide();
      } else {
        if (continuidad_id == 1) {
          $("#titulo_incremento_a_partir").html("El");
        } else if (continuidad_id == 2) {
          $("#titulo_incremento_a_partir").html("A partir del");
        }

        $("#titulo_incremento_a_partir").show();
        $("#td_contrato_incrementos_a_partir_de_año").show();

        setTimeout(function () {
          $("#contrato_incrementos_a_partir_de_año").select2("open");
        }, 200);
      }
    });
  });
  // FIN CHANGE INCREMENTOS

  // INICIO CHANGE BENEFICIARIO
  $("#modal_beneficiario_tipo_persona").change(function () {
    $("#modal_beneficiario_tipo_persona option:selected").each(function () {
      tipo_persona = $(this).val();
      if (tipo_persona == 1 || tipo_persona == 2) {
        setTimeout(function () {
          $("#modal_beneficiario_nombre").focus();
        }, 100);
      } else if (tipo_persona == 0) {
        $("#modal_beneficiario_tipo_persona").focus();
      }
    });
  });

  $("#modal_beneficiario_tipo_docu").change(function () {
    $("#modal_beneficiario_tipo_docu option:selected").each(function () {
      tipo_docu = $(this).val();
      if (tipo_docu == 1 || tipo_docu == 2 || tipo_docu == 3) {
        if (tipo_docu == 1) {
          $("#modal_beneficiario_num_docu").mask("00000000");
        } else if (tipo_docu == 2) {
          $("#modal_beneficiario_num_docu").mask("00000000000");
        }

        setTimeout(function () {
          $("#modal_beneficiario_num_docu").focus();
        }, 100);
      } else if (tipo_docu == 0) {
        $("#modal_beneficiario_tipo_docu").focus();
      }
    });
  });

  $("#modal_beneficiario_id_forma_pago").change(function () {
    $("#modal_beneficiario_id_forma_pago option:selected").each(function () {
      id_forma_pago = $(this).val();
      if (id_forma_pago == 1 || id_forma_pago == 2) {
        $("#div_modal_beneficiario_nombre_banco").show();
        $("#div_modal_beneficiario_numero_cuenta_bancaria").show();
        $("#div_modal_beneficiario_numero_CCI").show();
        setTimeout(function () {
          $("#modal_beneficiario_id_banco").select2("open");
        }, 200);
      } else if (id_forma_pago == 3) {
        $("#div_modal_beneficiario_nombre_banco").hide();
        $("#div_modal_beneficiario_numero_cuenta_bancaria").hide();
        $("#div_modal_beneficiario_numero_CCI").hide();
        setTimeout(function () {
          $("#modal_beneficiario_tipo_monto").select2("open");
        }, 200);
      }
    });
  });

  $("#modal_beneficiario_id_banco").change(function () {
    $("#modal_beneficiario_id_banco option:selected").each(function () {
      id_banco = $(this).val();
      if (id_banco == 0) {
        setTimeout(function () {
          $("#modal_beneficiario_id_banco").select2("open");
        }, 500);
      } else {
        setTimeout(function () {
          $("#modal_beneficiario_num_cuenta_bancaria").focus();
        }, 200);
      }
    });
  });

  $("#modal_beneficiario_tipo_monto").change(function () {
    $("#modal_beneficiario_tipo_monto option:selected").each(function () {
      tipo_monto = $(this).val();
      if (tipo_monto == 1 || tipo_monto == 2) {
        $("#div_modal_beneficiario_monto").show();
        if (tipo_monto == 1) {
          $("#label_beneficiario_tipo_pago").text(
            "Monto (Según la moneda del contrato)"
          );
        } else if (tipo_monto == 2) {
          $("#label_beneficiario_tipo_pago").text("Porcentaje (%)");
        }
        setTimeout(function () {
          $("#modal_beneficiario_monto").focus();
        }, 200);
      } else if (tipo_monto == 3) {
        $("#div_modal_beneficiario_monto").hide();
      }
    });
  });

  $("#modal_beneficiario_monto").on({
    focus: function (event) {
      $(event.target).select();
    },
    change: function (event) {
      if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
        $(event.target).val(
          parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
        );
        $(event.target).val(function (index, value) {
          return value
            .replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      } else {
        $(event.target).val("0.00");
      }
    },
  });

  $("#btn_agregar_beneficiario").click(function () {
    sec_contrato_detalle_solicitudv2_guardar_beneficiario(
      "guardar_beneficiario"
    );
  });

  $("#btn_agregar_responsable_ir").click(function () {
    sec_contrato_detalle_solicitudv2_guardar_responsable_ir(
      "guardar_responsable_ir"
    );
  });
  // FIN CHANGE BENEFICIARIO

  // INICIO CHANGE PROPIETARIOS
  $("#modal_propietario_tipo_persona").change(function () {
    $("#modal_propietario_tipo_persona option:selected").each(function () {
      tipo_persona = $(this).val();
      if (tipo_persona == 1) {
        $("#modal_propietario_tipo_docu").val("1");
        $("#div_modal_propietario_representante_legal").hide();
        $("#div_modal_propietario_num_partida_registral").hide();
      } else if (tipo_persona == 2) {
        $("#modal_propietario_tipo_docu").val("2");
        $("#div_modal_propietario_representante_legal").show();
        $("#div_modal_propietario_num_partida_registral").show();
      }
      $("#modal_propietario_tipo_docu").change();
      setTimeout(function () {
        $("#modal_propietario_nombre").focus();
      }, 200);
    });
  });

  $("#modal_propietario_tipo_docu").change(function () {
    $("#modal_propietario_tipo_docu option:selected").each(function () {
      propietario_tipo_docu = $(this).val();
      if (
        propietario_tipo_docu == 1 ||
        propietario_tipo_docu == 3 ||
        propietario_tipo_docu == 4
      ) {
        $("#div_num_docu_propietario").show();

        if (propietario_tipo_docu == 1) {
          $("#label_num_docu_propietario").html(
            "Número de DNI del propietario:"
          );
          $("#modal_propietario_num_docu").mask("00000000");
        } else if (propietario_tipo_docu == 3) {
          $("#label_num_docu_propietario").html(
            "Número de Pasaporte del propietario:"
          );
          $("#modal_propietario_num_docu").mask("000000000000", {
            translation: { 0: { pattern: /[A-Za-z0-9]/ } },
          });
        } else if (propietario_tipo_docu == 4) {
          $("#label_num_docu_propietario").html(
            "Número de Carnet de Ext. del propietario:"
          );
          $("#modal_propietario_num_docu").mask("000000000000", {
            translation: { 0: { pattern: /[A-Za-z0-9]/ } },
          });
        }

        setTimeout(function () {
          $("#modal_propietario_num_docu").focus();
        }, 200);
      } else if (propietario_tipo_docu == 2) {
        $("#div_num_docu_propietario").hide();

        setTimeout(function () {
          $("#modal_propietario_num_ruc").focus();
        }, 200);
      }
    });
  });

  $("#modal_propietario_tipo_persona_contacto").change(function () {
    $("#modal_propietario_tipo_persona_contacto option:selected").each(
      function () {
        tipo_persona_contacto = $(this).val();
        if (tipo_persona_contacto == 1) {
          $("#div_modal_propietario_contacto_nombre").hide();
          $("#modal_propietario_contacto_telefono").focus();
        } else if (tipo_persona_contacto == 2) {
          $("#div_modal_propietario_contacto_nombre").show();
          $("#modal_propietario_contacto_nombre").focus();
        }
      }
    );
  });
  // FIN CHANGE PROPIETARIOS

  $("#moneda_id").change(function () {
    $("#moneda_id option:selected").each(function () {
      moneda_id = $(this).val();
      if (moneda_id != 0) {
        setTimeout(function () {
          $("#monto").focus();
        }, 200);
      }
    });
  });

  $("#tipo_igv_id").change(function () {
    $("#tipo_igv_id option:selected").each(function () {
      tipo_igv_id = $(this).val();
      if (tipo_igv_id != 0) {
        sec_contrato_nuevo_calcular_subtotal_y_igv(tipo_igv_id);
        setTimeout(function () {
          if ($("#tipo_comprobante").val() == "0") {
            $("#tipo_comprobante").focus();
            $("#tipo_comprobante").select2("open");
          }
        }, 200);
      }
    });
  });

  $("#forma_pago").change(function () {
    $("#forma_pago option:selected").each(function () {
      forma_pago = $(this).val();
      if (forma_pago != 0) {
        setTimeout(function () {
          $("#tipo_comprobante").focus();
          $("#tipo_comprobante").select2("open");
        }, 200);
      }
    });
  });

  $("#tipo_comprobante").change(function () {
    $("#tipo_comprobante option:selected").each(function () {
      tipo_comprobante = $(this).val();
      if (tipo_comprobante != 0) {
        setTimeout(function () {
          $("#plazo_pago").focus();
        }, 200);
      }
    });
  });

  // INICIO AUTORIZACIONES MUNICIPALES
  $("#estado_id_am").on("change", function () {
    var select_valor = $(this).val();

    if (select_valor == "CONCLUIDO") {
      $("#div_condicion_id_am").show();
      $("#div_archivo_autorizacion_municipal").show();

      setTimeout(function () {
        $("#condicion_id_am").focus();
        $("#condicion_id_am").select2("open");
      }, 200);
    } else {
      $("#div_condicion_id_am").hide();
      $("#div_fecha_vencimiento_am").hide();
      $("#div_fecha_renovacion_am").hide();
      $("#div_archivo_autorizacion_municipal").hide();
    }
  });

  $("#condicion_id_am").on("change", function () {
    var selectValor = $(this).val();

    if (selectValor == "TEMPORAL") {
      $("#div_fecha_vencimiento_am").show();
      $("#div_fecha_renovacion_am").show();
      $("#fecha_vencimiento_am").focus();
    } else {
      $("#div_fecha_vencimiento_am").hide();
      $("#div_fecha_renovacion_am").hide();
    }
  });

  $("#fecha_vencimiento_am").on("change", function () {
    setTimeout(function () {
      $("#fecha_renovacion_am").focus();
    }, 200);
  });
  // FIN AUTORIZACIONES MUNICIPALES

  // NIF16
  $("#modal_if_tipo_periodicidad_id").change(function () {
    $("#modal_if_tipo_periodicidad_id option:selected").each(function () {
      tipo_periosidad = $(this).val();
      if (tipo_periosidad == 1) {
        $(".block-periosidad").show();
      } else if (tipo_periosidad == 2) {
        $(".block-periosidad").hide();
      }
    });
  });

  // INICIO VER ADENDA
  if (typeof $("#adenta_id_temporal").val() !== "undefined") {
    var adenta_id_temporal = $("#adenta_id_temporal").val();
    if (parseInt(adenta_id_temporal) > 0) {
      setTimeout(function () {
        $("#btn_adendas").click();
      }, 500);
      setTimeout(function () {
        $("div.resplandor").focus();
      }, 800);
    }
  }
  // FIN VER ADENDA

  ///SUMINISTROS
  $("#modal_suministo_compromiso_pago_id").change(function () {
    $("#modal_suministo_compromiso_pago_id option:selected").each(function () {
      tipo_compromiso_pago_id = $(this).val();
      if (tipo_compromiso_pago_id == 0) {
        $("#div_modal_monto_o_porcentaje").hide();
        setTimeout(function () {
          $("#modal_tipo_compromiso_pago_id").focus();
        }, 100);
      } else if (
        tipo_compromiso_pago_id == 3 ||
        tipo_compromiso_pago_id == 4 ||
        tipo_compromiso_pago_id == 5 ||
        tipo_compromiso_pago_id == 8
      ) {
        $("#div_modal_monto_o_porcentaje").hide();
        setTimeout(function () {
          $("#modal_inmueble_num_suministro_luz").focus();
        }, 100);
      } else {
        $("#modal_suministo_monto_o_porcentaje").unmask();
        if (tipo_compromiso_pago_id == 1) {
          $("#modal_suministo_monto_o_porcentaje").mask("00");
        }
        $("#div_modal_monto_o_porcentaje").show();
        $("#modal_suministo_monto_o_porcentaje").val("");
        setTimeout(function () {
          $("#modal_suministo_monto_o_porcentaje").focus();
        }, 100);
      }
    });
  });

  $(".toggleButtonVerHistorialSeguimiento").click(function () {
    var div_historial_seguimiento = $(this).attr("div-toggle");
    $("#" + div_historial_seguimiento).toggle();
    // Cambiar el texto del botón
    if ($("#" + div_historial_seguimiento).is(":visible")) {
      $(this).text("Ocultar Historial");
    } else {
      $(this).text("Ver Historial");
    }
  });

  $(".btn-atender-seguimiento-proceso").click(function () {
    var seguimiento_id = $(this).attr("seguimiento-id");
    var nueva_etapa_id = $(this).attr("nueva-etapa-id");

    var title = "";
    switch (nueva_etapa_id) {
      case "3":
        title = "¿Estas seguro de pasar a la etapa de revisión área usuaria?";
        break;
      case "4":
        title = "¿Estas seguro de pasar a la etapa de revisión del proveedor?";
        break;
      case "5":
        title = "¿Estas seguro de pasar a la etapa de revisión área legal?";
        break;
      case "6":
        title = "¿Estas seguro de pasar a la etapa de paso a firmas?";
        break;
      case "7":
        title = "¿Estas seguro de dar la conformidad área usuaria?";
        break;
      case "8":
        title = "¿Estas seguro de pasar a la etapa de no hay seguimiento?";
        break;
      default:
        break;
    }

    swal(
      {
        title: title,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false,
        closeOnCancel: true,
      },
      function (isConfirm) {
        if (isConfirm) {
          var data_seguimiento = {
            seguimiento_id: seguimiento_id,
            nueva_etapa_id: nueva_etapa_id,
          };
          sec_contrato_detalle_solicitudv2_atender_seguimiento_proceso(
            data_seguimiento
          );
        }
      }
    );
  });

  $(".btn-reinicio-proceso-legal").click(function () {
    var tipo_documento_id = $(this).attr("tipo-documento-id");
    var proceso_id = $(this).attr("proceso-id");
    var proceso_detalle_id = $(this).attr("proceso-detalle-id");
    var nueva_etapa_id = $(this).attr("nueva-etapa-id");

    var title = "";
    switch (nueva_etapa_id) {
      case "3":
        title = "¿Estas seguro de pasar a la etapa de revisión área usuaria?";
        break;
      default:
        break;
    }

    swal(
      {
        title: title,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false,
        closeOnCancel: true,
      },
      function (isConfirm) {
        if (isConfirm) {
          var data_seguimiento = {
            tipo_documento_id: tipo_documento_id,
            proceso_id: proceso_id,
            proceso_detalle_id: proceso_detalle_id,
            nueva_etapa_id: nueva_etapa_id,
          };
          sec_contrato_detalle_solicitudv2_reiniciar_seguimiento_proceso(
            data_seguimiento
          );
        }
      }
    );
  });
}

function sec_contrato_detalle_solicitudv2_obtener_opciones(accion, select) {
  $.ajax({
    url: "/sys/set_contrato_nuevo.php",
    type: "POST",
    data: { accion: accion }, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      //  alert(datat)
      var respuesta = JSON.parse(datos);
      $(select).find("option").remove().end();
      $(select).append('<option value="0">- Seleccione -</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $(select).append(opcion);
      });
    },
    error: function () {},
  });
}

setArchivo_requisitos_arrendamiento($("#fileArchivo_requisitos_arrendamiento"));

function setArchivo_requisitos_arrendamiento(object) {
  $(document).on(
    "click",
    "#btnBuscarFile_req_solicitud_arrendamiento",
    function (event) {
      event.preventDefault();
      object.click();
    }
  );

  object.on("change", function (event) {
    //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
    if ($(this)[0].files.length <= 1) {
      const name = $(this).val().split(/\\|\//).pop();
      //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
      truncated = name;
    } else {
      truncated = "";
      mensajeAlerta(
        "Advertencia:",
        "Solo esta permitido adjuntar un archivo.",
        claseTipoAlertas.alertaWarning,
        $("#divMensajeAlertaLicFuncionamiento")
      );
      $("#fileArchivoLicFuncionamiento").val("");
    }

    $("#txtFile_req_solicitud_arrendamiento").html(truncated);
  });
}

function cerrar_moda_subir_archivo_req_solicitud_arrendamiento() {
  truncated = "";
  $("#txtFile_req_solicitud_arrendamiento").html(truncated);

  $("#moda_subir_archivo_req_solicitud_arrendamiento").modal("hide");
}

$(document).on(
  "submit",
  "#formArchivosModal_req_solicitud_arrendamiento",
  function (e) {
    var id_archivo = $("#id_archivo").val();
    var id_archivo = $("#id_archivo").val();
    var contrato_id = $("#id_contrato_req_file_arrendamiento").val();
    var id_representante_legal = $("#id_representante_legal").val();
    var contrato_detalle_id = contrato_detalle_id_archivo; //$("#modal_subir_arch_contrato_detalle_id").val();

    //var contrato_id =document.getElementById("id_contrato_req_file_arrendamiento").value;

    var id_tipo_archivo = $("#id_tipo_archivo").val();
    e.preventDefault();
    var form_data = new FormData(this);
    form_data.append("post_archivo_req_solicitud_arrendamiento", 1);
    form_data.append("id_archivo", id_archivo);
    form_data.append("contrato_detalle_id", contrato_detalle_id);
    form_data.append("contrato_id", contrato_id);
    form_data.append("id_tipo_archivo", id_tipo_archivo);
    form_data.append("id_representante_legal", id_representante_legal);

    loading(true);

    auditoria_send({
      proceso: "post_archivo_req_solicitud_arrendamiento",
      data: form_data,
    });

    $.ajax({
      url: "/sys/set_contrato_detalle_solicitudv2.php",
      type: "POST",
      data: form_data,
      cache: false,
      contentType: false,
      processData: false,
      success: function (response, status) {
        result = JSON.parse(response);
        loading();
        if (result.status) {
          m_reload();
          swal(result.message, "", "success");
        } else {
          swal({
            type: "warning",
            title: "Alerta!",
            text: result.message,
            html: true,
          });
        }
        //filter_archivos_table(0);
      },
      always: function (data) {
        loading();
      },
    });
  }
);

// INICIO FUNCIONES DETALLE SOLICITUD
function sec_contrato_detalle_solicitud_verificar_documentos_locacion(
  contrato_detalle_id
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var archivo_contrato = document.getElementById(
    "archivo_contrato_" + contrato_detalle_id
  );
  var fecha_suscripcion = $(
    "#cont_detalle_contrato_firmado_fecha_suscripcion_param_" +
      contrato_detalle_id
  ).val();
  var fecha_fin = $(
    "#cont_detalle_contrato_firmado_fecha_vencimiento_param_" +
      contrato_detalle_id
  ).val();
  var fecha_inicio = $(
    "#cont_detalle_contrato_firmado_fecha_incio_param_" + contrato_detalle_id
  ).val();

  // if (fecha_inicio.length == 0) {
  //   alertify.error("Ingrese una fecha inicio", 5);
  //   $(
  //     "#cont_detalle_contrato_firmado_fecha_incio_param_" + contrato_detalle_id
  //   ).focus();
  //   return false;
  // }

  if (fecha_suscripcion.length == 0) {
    alertify.error("Ingrese una fecha suscripción", 5);
    $(
      "#cont_detalle_contrato_firmado_fecha_suscripcion_param_" +
        contrato_detalle_id
    ).focus();
    return false;
  }

  if (archivo_contrato.files.length == 0) {
    alertify.error("Ingrese el contrato firmado", 5);
    $("#archivo_contrato_" + contrato_detalle_id).focus();
    return false;
  }
  var data = {
    accion: "obtener_documentos_incompletos",
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
  };

  auditoria_send({ proceso: "obtener_documentos_incompletos", data: data });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: data,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      //  alert(datat)
      var respuesta = JSON.parse(resp);
      auditoria_send({
        respuesta: "obtener_documentos_incompletos",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.result == "") {
          sec_contrato_detalle_solicitudv2_guardar_contrato_firmado2_locacion(
            contrato_detalle_id
          );
        } else {
          $("#modal_documentos_pendientes_por_subir").modal({
            backdrop: "static",
            keyboard: false,
          });
          $("#div_documentos_pendientes_por_subir").html(respuesta.result);
        }
      }
    },
    complete: function () {
      loading(false);
    },
  });
}

function sec_contrato_detalle_solicitudv2_guardar_contrato_firmado2_locacion(
  contrato_detalle_id = 0
) {
  $("#modal_documentos_pendientes_por_subir").modal("hide");

  var contrato_id = $("#contrato_id_temporal").val();

  var archivo_contrato = document.getElementById(
    "archivo_contrato_" + contrato_detalle_id
  );

  var fecha_suscripcion = $(
    "#cont_detalle_contrato_firmado_fecha_suscripcion_param_" +
      contrato_detalle_id
  ).val();
  var fecha_fin = $(
    "#cont_detalle_contrato_firmado_fecha_vencimiento_param_" +
      contrato_detalle_id
  ).val();
  var fecha_inicio = $(
    "#cont_detalle_contrato_firmado_fecha_incio_param_" + contrato_detalle_id
  ).val();

  // if (fecha_inicio.length == 0) {
  //   alertify.error("Ingrese una fecha inicio", 5);
  //   $(
  //     "#cont_detalle_contrato_firmado_fecha_incio_param_" + contrato_detalle_id
  //   ).focus();
  //   return false;
  // }

  if (fecha_suscripcion.length == 0) {
    alertify.error("Ingrese una fecha suscripción", 5);
    $(
      "#cont_detalle_contrato_firmado_fecha_suscripcion_param_" +
        contrato_detalle_id
    ).focus();
    return false;
  }

  if (archivo_contrato.files.length == 0) {
    alertify.error("Ingrese el contrato firmado", 5);
    $("#archivo_partida_registral_" + contrato_detalle_id).focus();
    return false;
  }

  var dataForm = new FormData(
    $("#form_contrato_firmado_" + contrato_detalle_id)[0]
  );

  dataForm.append("accion", "guardar_contrato_firmado");
  dataForm.append("contrato_id", contrato_id);
  dataForm.append("contrato_detalle_id", contrato_detalle_id);
  dataForm.append("plazo_id", 1);
  dataForm.append("fecha_inicio", fecha_inicio);
  dataForm.append("fecha_fin", fecha_fin);
  dataForm.append("fecha_suscripcion", fecha_suscripcion);

  auditoria_send({ proceso: "guardar_contrato_firmado", data: dataForm });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      //  alert(datat)

      var respuesta = JSON.parse(resp);
      auditoria_send({
        respuesta: "guardar_contrato_firmado",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        swal({
          title: "Registro exitoso",
          text: "El contrato firmado se guardo correctamente",
          html: true,
          type: "success",
          timer: 3000,
          closeOnConfirm: false,
          showCancelButton: false,
        });

        var data_correo = {
          contrato_id: contrato_id,
          action: "contrato_locacionservicio/enviar_email_confirmacion_firmar_contrato_locacion",
        };
  
        $.ajax({
          url: "sys/router/contratos/index.php",
          type: "POST",
          data: data_correo,
          success: function (resp_correo) {
              console.log("Correo enviado correctamente:", resp_correo);
          },
          error: function (xhr, status, error) {
              console.error("Error al enviar el correo:", error);
          }
      });

        setTimeout(function () {
          window.location.href = "?sec_id=contrato&sub_sec_id=locales";
          return false;
        }, 3000);
      } else {
        if (parseInt(respuesta.http_code) == 400) {
          if (respuesta.error == "sin_asignar") {
            if (respuesta.campo_incompleto == "nombre_de_la_tienda") {
              var msg_error_titulo =
                "No se pudo agregar el contrato firmado ya que falta ingresar el Nombre de la Tienda.";
              var msg_error_text = "¿Desea ingresar el nombre de la tienda?";
              var msg_error_confirmButtonText =
                "SI, AGREGAR EL NOMBRE DE LA TIENDA";
            } else if (respuesta.campo_incompleto == "centro_de_costos") {
              var msg_error_titulo =
                "No se pudo agregar el contrato firmado ya que falta ingresar el Centro de Costos.";
              var msg_error_text =
                "El área Contable es responsable de ingresar el Centro de Costos";
              var msg_error_confirmButtonText = "ENTENDIDO";
              // } else if (respuesta.campo_incompleto == 'abogado') {
              // 	var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
              // 	var msg_error_text = '¿Desea ingresar el abogado?';
              // 	var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
            } else if (respuesta.campo_incompleto == "latitud") {
              var msg_error_titulo =
                "No se pudo agregar el contrato firmado ya que falta ingresar la latitud.";
              var msg_error_text = "¿Desea ingresar la latitud?";
              var msg_error_confirmButtonText = "SI, AGREGAR LA LATITUD";
            } else if (respuesta.campo_incompleto == "longitud") {
              var msg_error_titulo =
                "No se pudo agregar el contrato firmado ya que falta ingresar la longitud.";
              var msg_error_text = "¿Desea ingresar la longitud?";
              var msg_error_confirmButtonText = "SI, AGREGAR LA LONGITUD";
            } else if (respuesta.campo_incompleto == "supervisor") {
              var msg_error_titulo =
                "No se pudo agregar el contrato firmado ya que falta ingresar el supervisor de la tienda.";
              var msg_error_text = "¿Desea ingresar el supervisor?";
              var msg_error_confirmButtonText = "SI, AGREGAR EL SUPERVISOR";
            } else if (respuesta.campo_incompleto == "jefe_comercial") {
              var msg_error_titulo =
                "No se pudo agregar el contrato firmado ya que falta ingresar el jefe comercial.";
              var msg_error_text = "¿Desea ingresar el jefe comercial?";
              var msg_error_confirmButtonText = "SI, AGREGAR EL JEFE COMERCIAL";
            }

            swal(
              {
                title: msg_error_titulo,
                text: msg_error_text,
                html: true,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#1cb787",
                cancelButtonColor: "#d56d6d",
                confirmButtonText: msg_error_confirmButtonText,
                cancelButtonText: "CANCELAR",
              },
              function (isConfirm) {
                if (isConfirm) {
                  if (respuesta.campo_incompleto == "nombre_de_la_tienda") {
                    $("#btn_editar_nombre_de_la_tienda").click();
                  } else if (respuesta.campo_incompleto == "latitud") {
                    $("#btn_editar_latitud").click();
                  } else if (respuesta.campo_incompleto == "longitud") {
                    $("#btn_editar_longitud").click();
                  } else if (respuesta.campo_incompleto == "supervisor") {
                    $("#btn_editar_supervisor").click();
                  } else if (respuesta.campo_incompleto == "jefe_comercial") {
                    $("#btn_editar_jefe_comercial").click();
                  } else if (respuesta.campo_incompleto == "abogado") {
                    $("#btn_editar_abogado").click();
                  }
                }
              }
            );
          } else {
            swal({
              title: "Error al guardar el contrato firmado",
              text: respuesta.error,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }
        }
      }
    },
    complete: function () {
      loading(false);
    },
  });
}

function guardar_contrato_firmado_proveedor() {
  var contrato_id = $("#contrato_id_temporal").val();
  var archivo_contrato_proveedor = document.getElementById(
    "archivo_contrato_proveedor"
  );
  var cont_detalle_proveedor_contrato_firmado_categoria_param = $(
    "#cont_detalle_proveedor_contrato_firmado_categoria_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_tipo_contrato_param = $(
    "#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_tipo_firma_param = $(
    "#cont_detalle_proveedor_contrato_firmado_tipo_firma_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_fecha_incio_param = $(
    "#cont_detalle_proveedor_contrato_firmado_fecha_incio_param"
  )
    .val()
    .trim();
  var cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = $(
    "#cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"
  )
    .val()
    .trim();
  var cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = $(
    "#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"
  )
    .val()
    .trim();
  var fecha_vencimiento_indefinida_id = $(
    "#fecha_vencimiento_indefinida_id"
  ).val();
  var cont_detalle_proveedor_renovacion_automatica = $(
    "#cont_detalle_proveedor_renovacion_automatica"
  )
    .val()
    .trim();

  if (parseInt(cont_detalle_proveedor_contrato_firmado_categoria_param) == 0) {
    alertify.error("Seleccione la categoría", 5);
    $("#cont_detalle_proveedor_contrato_firmado_categoria_param").focus();
    $("#cont_detalle_proveedor_contrato_firmado_categoria_param").select2(
      "open"
    );
    return false;
  }

  if (
    parseInt(cont_detalle_proveedor_contrato_firmado_tipo_contrato_param) ==
      0 ||
    cont_detalle_proveedor_contrato_firmado_tipo_contrato_param == undefined
  ) {
    alertify.error("Seleccione el tipo contrato", 5);
    $("#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param").focus();
    $("#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param").select2(
      "open"
    );
    return false;
  }

  if (parseInt(cont_detalle_proveedor_contrato_firmado_tipo_firma_param) == 0) {
    alertify.error("Seleccione el tipo de firma", 5);
    $("#cont_detalle_proveedor_contrato_firmado_tipo_firma_param").focus();
    $("#cont_detalle_proveedor_contrato_firmado_tipo_firma_param").select2(
      "open"
    );
    return false;
  }

  if (
    fecha_vencimiento_indefinida_id == 2 &&
    cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param == ""
  ) {
    alertify.error("Seleccione la fecha de vencimiento", 5);
    $(
      "#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"
    ).focus();
    return false;
  }

  if (archivo_contrato_proveedor.files.length == 0) {
    alertify.error("Ingrese el contrato firmado", 5);
    $("#archivo_partida_registral").focus();
    return false;
  }

  if (parseInt(cont_detalle_proveedor_renovacion_automatica) == 0) {
    alertify.error("Completar el campo de Renovación Automática", 5);
    $("#cont_detalle_proveedor_renovacion_automatica").focus();
    $("#cont_detalle_proveedor_renovacion_automatica").select2("open");
    return false;
  }

  var dataForm = new FormData($("#form_contrato_proveedor_firmado")[0]);

  dataForm.append("accion", "guardar_contrato_proveedor_firmado");
  dataForm.append("contrato_id", contrato_id);
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_categoria_param",
    cont_detalle_proveedor_contrato_firmado_categoria_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_tipo_contrato_param",
    cont_detalle_proveedor_contrato_firmado_tipo_contrato_param
  );

  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_tipo_firma_param",
    cont_detalle_proveedor_contrato_firmado_tipo_firma_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_fecha_incio_param",
    cont_detalle_proveedor_contrato_firmado_fecha_incio_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param",
    cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param",
    cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param
  );
  dataForm.append(
    "fecha_vencimiento_indefinida_id",
    fecha_vencimiento_indefinida_id
  );
  dataForm.append(
    "cont_detalle_proveedor_renovacion_automatica",
    cont_detalle_proveedor_renovacion_automatica
  );

  auditoria_send({
    proceso: "guardar_contrato_proveedor_firmado",
    data: dataForm,
  });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      auditoria_send({
        proceso: "guardar_contrato_proveedor_firmado",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
        // }else if(parseInt(respuesta.http_code) == 400){
        // 	if (respuesta.campo_incompleto == "abogado") {
        // 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
        // 		var msg_error_text = '¿Desea ingresar el abogado?';
        // 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
        // 	}

        // 	swal({
        // 		title: msg_error_titulo,
        // 		text: msg_error_text,
        // 		html: true,
        // 		type: "warning",
        // 		showCancelButton: true,
        // 		confirmButtonColor: '#1cb787',
        // 		cancelButtonColor: '#d56d6d',
        // 		confirmButtonText: msg_error_confirmButtonText,
        // 		cancelButtonText: 'CANCELAR'
        // 	}, function (isConfirm) {
        // 		if(isConfirm){
        // 			if (respuesta.campo_incompleto == 'abogado') {
        // 				$('#btn_editar_abogado').click();
        // 			}
        // 		}
        // 	});
      } else if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
        return false;
      }
    },
    complete: function () {
      loading(false);
    },
  });
}

function guardar_contrato_firmado_acuerdo_confidencialidad() {
  var contrato_id = $("#contrato_id_temporal").val();
  var archivo_contrato_proveedor = document.getElementById(
    "archivo_contrato_proveedor"
  );
  var cont_detalle_proveedor_contrato_firmado_categoria_param = $(
    "#cont_detalle_proveedor_contrato_firmado_categoria_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_tipo_contrato_param = $(
    "#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_tipo_firma_param = $(
    "#cont_detalle_proveedor_contrato_firmado_tipo_firma_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_fecha_incio_param = $(
    "#cont_detalle_proveedor_contrato_firmado_fecha_incio_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = $(
    "#cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"
  ).val();
  var cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = $(
    "#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"
  ).val();
  var fecha_vencimiento_indefinida_id = $(
    "#fecha_vencimiento_indefinida_id"
  ).val();
  var cont_detalle_renovacion_automatica = $(
    "#cont_detalle_renovacion_automatica"
  ).val();

  if (parseInt(cont_detalle_proveedor_contrato_firmado_categoria_param) == 0) {
    alertify.error("Seleccione la categoría", 5);
    $("#cont_detalle_proveedor_contrato_firmado_categoria_param").focus();
    $("#cont_detalle_proveedor_contrato_firmado_categoria_param").select2(
      "open"
    );
    return false;
  }

  if (
    parseInt(cont_detalle_proveedor_contrato_firmado_tipo_contrato_param) == 0
  ) {
    alertify.error("Seleccione el tipo contrato", 5);
    $("#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param").focus();
    $("#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param").select2(
      "open"
    );
    return false;
  }

  if (parseInt(cont_detalle_proveedor_contrato_firmado_tipo_firma_param) == 0) {
    alertify.error("Seleccione el tipo de firma", 5);
    $("#cont_detalle_proveedor_contrato_firmado_tipo_firma_param").focus();
    $("#cont_detalle_proveedor_contrato_firmado_tipo_firma_param").select2(
      "open"
    );
    return false;
  }

  if (
    fecha_vencimiento_indefinida_id == 2 &&
    cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param == ""
  ) {
    alertify.error("Seleccione la fecha de vencimiento", 5);
    $(
      "#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"
    ).focus();
    return false;
  }

  if (archivo_contrato_proveedor.files.length == 0) {
    alertify.error("Ingrese el contrato firmado", 5);
    $("#archivo_partida_registral").focus();
    return false;
  }

  if (parseInt(cont_detalle_renovacion_automatica) == 0) {
    alertify.error("Completar el campo de Renovación Automática", 5);
    $("#cont_detalle_renovacion_automatica").focus();
    $("#cont_detalle_renovacion_automatica").select2("open");
    return false;
  }

  var dataForm = new FormData($("#form_contrato_proveedor_firmado")[0]);

  dataForm.append(
    "accion",
    "guardar_contrato_acuerdo_confidencialidad_firmado"
  );
  dataForm.append("contrato_id", contrato_id);
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_categoria_param",
    cont_detalle_proveedor_contrato_firmado_categoria_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_tipo_contrato_param",
    cont_detalle_proveedor_contrato_firmado_tipo_contrato_param
  );

  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_tipo_firma_param",
    cont_detalle_proveedor_contrato_firmado_tipo_firma_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_fecha_incio_param",
    cont_detalle_proveedor_contrato_firmado_fecha_incio_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param",
    cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param
  );
  dataForm.append(
    "cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param",
    cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param
  );
  dataForm.append(
    "fecha_vencimiento_indefinida_id",
    fecha_vencimiento_indefinida_id
  );
  dataForm.append(
    "cont_detalle_renovacion_automatica",
    cont_detalle_renovacion_automatica
  );

  auditoria_send({
    proceso: "guardar_contrato_acuerdo_confidencialidad_firmado",
    data: dataForm,
  });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      //  alert(datat)

      var respuesta = JSON.parse(resp);
      auditoria_send({
        proceso: "guardar_contrato_acuerdo_confidencialidad_firmado",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
        // }else if(parseInt(respuesta.http_code) == 400){
        // 	if (respuesta.campo_incompleto == "abogado") {
        // 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
        // 		var msg_error_text = '¿Desea ingresar el abogado?';
        // 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
        // 	}

        // 	swal({
        // 		title: msg_error_titulo,
        // 		text: msg_error_text,
        // 		html: true,
        // 		type: "warning",
        // 		showCancelButton: true,
        // 		confirmButtonColor: '#1cb787',
        // 		cancelButtonColor: '#d56d6d',
        // 		confirmButtonText: msg_error_confirmButtonText,
        // 		cancelButtonText: 'CANCELAR'
        // 	}, function (isConfirm) {
        // 		if(isConfirm){
        // 			if (respuesta.campo_incompleto == 'abogado') {
        // 				$('#btn_editar_abogado').click();
        // 			}
        // 		}
        // 	});
      } else if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
        return false;
      }
    },
    complete: function () {
      loading(false);
    },
  });
}

//AGREGAR DIRECCION MUNICIPAL
function sec_contrato_detalle_solicitudv2_guardar_direccion_municipal() {
  var contrato_id = $("#contrato_id_temporal").val();
  var direccion_municipal = $("#direccion_municipal").val().trim();

  if (direccion_municipal.length == 0) {
    alertify.error("Ingrese dirección municipal", 5);
    $("#direccion_municipal").focus();
    return false;
  }
  if (direccion_municipal.length >= 1000) {
    alertify.error("Tamaño maximo de caracteres permitidos (1000)", 5);
    $("#direccion_municipal").focus();
    return false;
  }
  var data = {
    accion: "guardar_direccion_municipal",
    contrato_id: contrato_id,
    direccion_municipal: direccion_municipal,
  };
  swal(
    {
      title: "¿Desea guardar esta dirección municipal?",
      type: "warning",
      timer: 10000,
      showCancelButton: true,
      closeOnConfirm: true,
      confirmButtonColor: "#3085d6",
      confirmButtonText: "Aceptar",
      cancelButtonText: "Cancelar",
    },
    function (result) {
      if (result) {
        auditoria_send({ proceso: "guardar_direccion_municipal", data: data });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
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

            auditoria_send({
              proceso: "guardar_autorizacion_municipal",
              data: respuesta,
            });

            if (parseInt(respuesta.http_code) == 200) {
              swal({
                title: "Registro exitoso",
                text: "La dirección municipal se actualizo correctamente",
                html: true,
                type: "success",
                timer: 6000,
                closeOnConfirm: false,
                showCancelButton: false,
              });
            } else {
              swal({
                title: "Error al registrar la dirección municipal ",
                text: respuesta.error,
                html: true,
                type: "warning",
                closeOnConfirm: false,
                showCancelButton: false,
              });
            }
          },
          error: function () {},
        });
      }
    }
  );
}

$(".cont_detalleSolicitudProveedor_btn_guardar_aprobar_gerencia").click(
  function () {
    var contrato_id = $("#contrato_id_temporal").val();
    var cont_detalle_proveedor_aprobacion_gerencia_param = $(this).val();

    var texto_mensaje_pregunta = "";

    if (cont_detalle_proveedor_aprobacion_gerencia_param == 1) {
      texto_mensaje_pregunta = "¿Está seguro de aprobar la solicitud?";
    } else {
      texto_mensaje_pregunta = "¿Está seguro de rechazar la solicitud?";
    }

    swal(
      {
        title: texto_mensaje_pregunta,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false,
        closeOnCancel: true,
      },
      function (isConfirm) {
        if (isConfirm) {
          var data = {
            accion:
              "sec_contrato_detalleSolicitudProveedor_aprobar_solicitud_gerencia",
            contrato_id: contrato_id,
            cont_detalle_proveedor_aprobacion_gerencia_param:
              cont_detalle_proveedor_aprobacion_gerencia_param,
          };

          auditoria_send({
            proceso:
              "sec_contrato_detalleSolicitudProveedor_aprobar_solicitud_gerencia",
            data: data,
          });

          $.ajax({
            url: "/sys/set_contrato_detalle_solicitudv2.php",
            type: "POST",
            data: data,
            //contentType : false,
            //processData : false,
            beforeSend: function (xhr) {
              loading(true);
            },
            success: function (resp) {
              var respuesta = JSON.parse(resp);

              if (respuesta.status) {
                swal(
                  {
                    title: "¡Listo!",
                    text: respuesta.message,
                    type: "success",
                    timer: 5000,
                    closeOnConfirm: false,
                  },
                  function () {
                    location.reload();
                  }
                );

                var data = {
                  accion: "send_email_solicitud_contrato_proveedores",
                  contrato_id: contrato_id,
                };

                $.ajax({
                  url: "/sys/set_contrato_nuevo.php",
                  type: "POST",
                  data: data,
                  success: function (resp) {},
                });
              } else {
                swal(
                  {
                    title: "¡Error!",
                    text:
                      "Ocurrio un error: " +
                      respuesta.message +
                      ", pongase en contacto con el personal de SOPORTE",
                    type: "warning",
                    timer: 5000,
                    closeOnConfirm: false,
                  },
                  function () {
                    location.reload();
                  }
                );
              }
              //tabla.ajax.reload();
            },
            complete: function () {
              loading(false);
            },
          });
        }
      }
    );
  }
);

$(".cont_btn_guardar_aprobar_agente").click(function () {
  var contrato_id = $("#contrato_id_temporal").val();
  var cont_detalle_aprobacion_param = $(this).val();
  var tipo_contrato_id = $("#tipo_contrato_id_temporal").val();
  var texto_mensaje_pregunta = "";

  if (cont_detalle_aprobacion_param == 1) {
    texto_mensaje_pregunta = "¿Está seguro de aprobar la solicitud?";
  } else {
    texto_mensaje_pregunta = "¿Está seguro de rechazar la solicitud?";
  }

  swal(
    {
      title: texto_mensaje_pregunta,
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: false,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "sec_contrato_detalle_aprobar_solicitud",
          contrato_id: contrato_id,
          cont_detalle_aprobacion_param: cont_detalle_aprobacion_param,
        };

        auditoria_send({
          proceso: "sec_contrato_detalle_aprobar_solicitud",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
          type: "POST",
          data: data,
          //contentType : false,
          //processData : false,
          beforeSend: function (xhr) {
            loading(true);
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);

            if (respuesta.status) {
              swal(
                {
                  title: "¡Listo!",
                  text: respuesta.message,
                  type: "success",
                  timer: 5000,
                  closeOnConfirm: false,
                },
                function () {
                  location.reload();
                }
              );

              var data = {
                accion: "send_email_solicitud_contrato_agente",
                contrato_id: contrato_id,
                tipo_contrato_id: tipo_contrato_id,
              };

              $.ajax({
                url: "/sys/set_contrato_nuevo_agente.php",
                type: "POST",
                data: data,
                success: function (resp) {},
              });
            } else {
              swal(
                {
                  title: "¡Error!",
                  text:
                    "Ocurrio un error: " +
                    respuesta.message +
                    ", pongase en contacto con el personal de SOPORTE",
                  type: "warning",
                  timer: 5000,
                  closeOnConfirm: false,
                },
                function () {
                  location.reload();
                }
              );
            }
            //tabla.ajax.reload();
          },
          complete: function () {
            loading(false);
          },
        });
      }
    }
  );
});

$(".cont_btn_guardar_aprobar_locacion").click(function () {
  var contrato_id = $("#contrato_id_temporal").val();
  var cont_detalle_aprobacion_param = $(this).val();
  var tipo_contrato_id = $("#tipo_contrato_id_temporal").val();
  var texto_mensaje_pregunta = "";

  if (cont_detalle_aprobacion_param == 1) {
    texto_mensaje_pregunta = "¿Está seguro de aprobar la solicitud?";
  } else {
    texto_mensaje_pregunta = "¿Está seguro de rechazar la solicitud?";
  }

  swal(
    {
      title: texto_mensaje_pregunta,
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: false,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "sec_contrato_detalle_aprobar_solicitud",
          contrato_id: contrato_id,
          cont_detalle_aprobacion_param: cont_detalle_aprobacion_param,
        };

        auditoria_send({
          proceso: "sec_contrato_detalle_aprobar_solicitud",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
          type: "POST",
          data: data,
          //contentType : false,
          //processData : false,
          beforeSend: function (xhr) {
            loading(true);
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);

            if (respuesta.status) {
              var data_correo = {
                contrato_id: contrato_id,
                action:
                  "contrato_locacionservicio/enviar_email_confirmacion_aprobar_contrato_locacion"
              };

              $.ajax({
                url: "sys/router/contratos/index.php",
                type: "POST",
                data: data_correo,
                success: function (resp) {},
              });

              swal(
                {
                  title: "¡Listo!",
                  text: respuesta.message,
                  type: "success",
                  timer: 5000,
                  closeOnConfirm: false,
                },
                function () {
                  location.reload();
                }
              );
            } else {
              swal(
                {
                  title: "¡Error!",
                  text:
                    "Ocurrio un error: " +
                    respuesta.message +
                    ", pongase en contacto con el personal de SOPORTE",
                  type: "warning",
                  timer: 5000,
                  closeOnConfirm: false,
                },
                function () {
                  location.reload();
                }
              );
            }
            //tabla.ajax.reload();
          },
          complete: function () {
            loading(false);
          },
        });
      }
    }
  );
});

$(".cont_detalle_acuerdo_confidencialidad_btn_guardar_aprobar_gerencia").click(
  function () {
    var contrato_id = $("#contrato_id_temporal").val();
    var cont_detalle_proveedor_aprobacion_gerencia_param = $(this).val();

    var texto_mensaje_pregunta = "";

    if (cont_detalle_proveedor_aprobacion_gerencia_param == 1) {
      texto_mensaje_pregunta = "¿Está seguro de aprobar la solicitud?";
    } else {
      texto_mensaje_pregunta = "¿Está seguro de rechazar la solicitud?";
    }

    swal(
      {
        title: texto_mensaje_pregunta,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false,
        closeOnCancel: true,
      },
      function (isConfirm) {
        if (isConfirm) {
          var data = {
            accion:
              "sec_contrato_detalleSolicitudProveedor_aprobar_solicitud_gerencia",
            contrato_id: contrato_id,
            cont_detalle_proveedor_aprobacion_gerencia_param:
              cont_detalle_proveedor_aprobacion_gerencia_param,
          };

          auditoria_send({
            proceso:
              "sec_contrato_detalleSolicitudProveedor_aprobar_solicitud_gerencia",
            data: data,
          });

          $.ajax({
            url: "/sys/set_contrato_detalle_solicitudv2.php",
            type: "POST",
            data: data,
            //contentType : false,
            //processData : false,
            beforeSend: function (xhr) {
              loading(true);
            },
            success: function (resp) {
              var respuesta = JSON.parse(resp);

              if (respuesta.status) {
                swal({
                  title: "¡Listo!",
                  text: respuesta.message,
                  type: "success",
                  timer: 5000,
                  closeOnConfirm: false,
                });

                var data = {
                  accion:
                    "send_email_solicitud_contrato_acuerdo_confidencialidad",
                  contrato_id: contrato_id,
                };

                $.ajax({
                  url: "/sys/set_contrato_nuevo.php",
                  type: "POST",
                  data: data,
                  success: function (resp) {},
                });
              } else {
                swal({
                  title: "¡Error!",
                  text:
                    "Ocurrio un error: " +
                    respuesta.message +
                    ", pongase en contacto con el personal de SOPORTE",
                  type: "warning",
                  timer: 5000,
                  closeOnConfirm: false,
                });
              }
              //tabla.ajax.reload();
            },
            complete: function () {
              loading(false);
              location.reload(true);
            },
          });
        }
      }
    );
  }
);

$(".cont_detalle_solicitudv2_giro_boton").click(function () {
  var contrato_id = $("#contrato_id_temporal").val();
  var cont_detalle_solicitudv2_giro_boton_param = $(this).val();
  var cont_detalle_solicitudv2_param_texto_motivo_giro = $(
    "#cont_detalle_solicitudv2_param_texto_motivo_giro"
  ).val();

  if (cont_detalle_solicitudv2_giro_boton_param == 2) {
    $("#cont_detalle_solicitudv2_param_texto_motivo_giro").val("");
    document.getElementById(
      "cont_detalle_solicitudv2_param_text_giro_cantidad_caracteres"
    ).innerHTML = "200";

    $("#cont_detalle_solicitudv2_div_giro_botones_aprobaryrechazar").hide();
    $("#cont_detalle_solicitudv2_div_giro_ingrese_motivo").show();
    return;
  }

  if (cont_detalle_solicitudv2_giro_boton_param == 3) {
    if (cont_detalle_solicitudv2_param_texto_motivo_giro == "") {
      alertify.error("Debe ingresar motivo del rechazo", 5);
      $("#cont_detalle_solicitudv2_param_texto_motivo_giro").focus();
      return false;
    }

    sec_contrato_detalle_solicitudv2_verificar_giro(
      contrato_id,
      cont_detalle_solicitudv2_giro_boton_param,
      cont_detalle_solicitudv2_param_texto_motivo_giro
    );
    return;
  }

  if (cont_detalle_solicitudv2_giro_boton_param == 4) {
    $("#cont_detalle_solicitudv2_div_giro_ingrese_motivo").hide();
    $("#cont_detalle_solicitudv2_div_giro_botones_aprobaryrechazar").show();
    return;
  }

  if (cont_detalle_solicitudv2_giro_boton_param == 1) {
    sec_contrato_detalle_solicitudv2_verificar_giro(
      contrato_id,
      cont_detalle_solicitudv2_giro_boton_param,
      cont_detalle_solicitudv2_param_texto_motivo_giro
    );
    return;
  }
});

function sec_contrato_detalle_solicitudv2_verificar_giro(
  contrato_id,
  boton_valor,
  txt_motivo
) {
  swal(
    {
      title: "¿Está seguro de confirmar el giro?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: false,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "sec_contrato_detalle_solicitudv2_verificar_giro",
          contrato_id: contrato_id,
          cont_detalle_solicitudv2_giro_boton_param: boton_valor,
          cont_detalle_solicitudv2_param_texto_motivo_giro:
            txt_motivo.toUpperCase(),
        };

        auditoria_send({
          proceso: "sec_contrato_detalle_solicitudv2_verificar_giro",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
          type: "POST",
          data: data,
          //contentType : false,
          //processData : false,
          beforeSend: function (xhr) {
            loading(true);
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);

            if (respuesta.status) {
              swal({
                title: "¡Listo!",
                text: respuesta.message,
                type: "success",
                timer: 5000,
                closeOnConfirm: false,
              });
            } else {
              swal({
                title: "¡Error!",
                text:
                  "Ocurrio un error: " +
                  respuesta.message +
                  ", pongase en contacto con el personal de SOPORTE",
                type: "warning",
                timer: 5000,
                closeOnConfirm: false,
              });
            }
            //tabla.ajax.reload();
          },
          complete: function () {
            loading(false);
            location.reload(true);
          },
        });
      }
    }
  );
}

var mi_ruta_temporal = "";

function sec_contrato_detalle_solicitudv2_ver_documento_en_visor(
  ruta,
  documento,
  tipodocumento,
  titulo
) {
  mi_ruta_temporal = ruta;
  midocu = documento;
  var tipodocumento = tipodocumento.toLowerCase();
  var html = "";
  var destino = "";

  $("#divDetalleSolicitud").hide();

  if ($("#divFormatoDePago").length) {
    $("#divFormatoDePago").hide();
  }

  $("#divAnexos").hide();

  if (tipodocumento == "html") {
    if (titulo == "") {
      $("#divDetalleSolicitud").show();
    } else if (titulo == "formato_de_pago") {
      if ($("#divFormatoDePago").length) {
        $("#divFormatoDePago").show();
      }
    }
  } else if (tipodocumento == "pdf") {
    $("#divAnexoHeadingValue").html(titulo);
    $("#divDetalleSolicitud").hide();
    $("#divAnexos").show();
    $("#divVerPdfFullPantalla").show();
    $("#divVisorPdfPrincipal").show();
    $("#divVerImagenFullPantalla").hide();
    $("#divDescargarImagen").hide();
    $("#divVisorImagen").hide();

    html =
      '<iframe src="' +
      mi_ruta_temporal +
      midocu +
      '" class="col-xs-12 col-md-12 col-sm-12" height="580"></iframe>';
    var htmlModal =
      '<iframe src="' +
      mi_ruta_temporal +
      midocu +
      '" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
    $("#divVisorPdfPrincipal").html(html);
    $("#divVisorPdfModal").html(htmlModal);
  } else {
    $("#divAnexoHeadingValue").html(titulo);
    $("#divDetalleSolicitud").hide();
    $("#divAnexos").show();
    $("#divVerPdfFullPantalla").hide();
    $("#divVisorPdfPrincipal").hide();
    $("#divVerImagenFullPantalla").show();
    $("#divDescargarImagen").show();
    $("#divVisorImagen").show();

    destino =
      "sec_contrato_detalle_solicitudv2_btn_descargar('" +
      mi_ruta_temporal.replace("/var/www/html", "") +
      midocu +
      "');";

    $("#sec_contrato_detalle_solicitudv2_descargar_imagen").attr(
      "onClick",
      destino
    );

    if (
      tipodocumento == "jpg" ||
      tipodocumento == "png" ||
      tipodocumento == "jpeg"
    ) {
      html =
        '<img src="' +
        mi_ruta_temporal +
        midocu +
        '" class="img-responsive" style="border: 1px solid;">';
      document
        .getElementById(
          "sec_contrato_detalle_solicitudv2_ver_imagen_full_pantalla"
        )
        .removeEventListener(
          "click",
          sec_contrato_detalle_solicitudv2_ver_imagen_full_pantalla
        );
      document
        .getElementById(
          "sec_contrato_detalle_solicitudv2_ver_imagen_full_pantalla"
        )
        .addEventListener(
          "click",
          sec_contrato_detalle_solicitudv2_ver_imagen_full_pantalla
        );
      $("#divVerImagenFullPantalla").show();
      $("#divDescargarImagen").removeClass("col-xs-12 col-md-12 col-sm-12");
      $("#divDescargarImagen").addClass("col-xs-3 col-md-3 col-sm-3");
    } else {
      $("#divVerImagenFullPantalla").hide();
      $("#divDescargarImagen").removeClass("col-xs-3 col-md-3 col-sm-3");
      $("#divDescargarImagen").addClass("col-xs-12 col-md-12 col-sm-12");
      html =
        '<a title="El documento no se puede visualizar en el sistema, clic en descargar para visualizalo en su equipo" onClick="' +
        destino +
        '"><img src="/img/document_cant_display.jpg" class="img-responsive" style="border: 1px solid;"></a>';
    }

    $("#divVisorImagen").html(html);
  }
}

function sec_contrato_detalle_solicitudv2_btn_descargar(ruta_archivo) {
  var extension = "";

  // Obtener el nombre del archivo
  var ultimoPunto = ruta_archivo.lastIndexOf("/");

  if (ultimoPunto !== -1) {
    var extension = ruta_archivo.substring(ultimoPunto + 1);
  }

  // Crear un enlace temporal
  var enlace = document.createElement("a");
  enlace.href = ruta_archivo;

  // Darle un nombre al archivo que se descargará
  enlace.download = extension;

  // Simular un clic en el enlace
  document.body.appendChild(enlace);
  enlace.click();

  // Limpiar el enlace temporal
  document.body.removeChild(enlace);
}

function sec_contrato_detalle_solicitudv2_ver_imagen_full_pantalla() {
  var image = new Image();
  image.src = mi_ruta_temporal + midocu;
  var viewer = new Viewer(image, {
    hidden: function () {
      viewer.destroy();
    },
  });
  // image.click();
  viewer.show();
}

function sec_contrato_detalle_solicitudv2_guardar_observaciones_agente() {
  var contrato_id = $("#contrato_id_temporal").val();
  var observaciones = $("#contrato_observaciones").val().trim();
  var correos_adjuntos = $("#correos_adjuntos").val().trim();

  if (observaciones == "") {
    alertify.error("Ingrese la observación", 5);
    $("#contrato_observaciones").focus();
    return false;
  }

  swal(
    {
      title: "¿Está seguro de agregar y notificar la observación?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "guardar_observaciones_contrato_agente",
          contrato_id: contrato_id,
          observaciones: observaciones,
          correos_adjuntos: correos_adjuntos,
        };

        auditoria_send({
          proceso: "guardar_observaciones_contrato_agente",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
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
            auditoria_send({
              respuesta: "guardar_observaciones_contrato_agente",
              data: respuesta,
            });

            if (parseInt(respuesta.http_code) == 500) {
              swal({
                title: respuesta.mensaje,
                text: "",
                html: true,
                type: respuesta.status,
                closeOnConfirm: false,
                showCancelButton: false,
              });
              return false;
            }
            if (parseInt(respuesta.http_code) == 400) {
              // swal('Aviso', respuesta.status, 'warning');
              // listar_transacciones(gen_cliente_id);
              return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
              // window.location.href = "http://localhost/?sec_id=contrato&sub_sec_id=locales";
              sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio();
              // sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_gerencia();
              $("#contrato_observaciones").val("");
              $("#contrato_observaciones").focus();
              return false;
            }
          },
          error: function () {},
        });
      }
    }
  );
}

function sec_contrato_detalle_solicitudv2_guardar_observaciones() {
  var contrato_id = $("#contrato_id_temporal").val();
  var observaciones = $("#contrato_observaciones").val().trim();
  var correos_adjuntos = $("#correos_adjuntos").val().trim();

  if (observaciones == "") {
    alertify.error("Ingrese la observación", 5);
    $("#contrato_observaciones").focus();
    return false;
  }

  swal(
    {
      title: "¿Está seguro de agregar y notificar la observación?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "guardar_observaciones_contrato",
          contrato_id: contrato_id,
          tipo_observacion: "local",
          observaciones: observaciones,
          correos_adjuntos: correos_adjuntos,
        };

        auditoria_send({
          proceso: "guardar_observaciones_contrato_arrendamiento",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
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
            auditoria_send({
              respuesta: "guardar_observaciones_contrato_arrendamiento",
              data: respuesta,
            });

            if (parseInt(respuesta.http_code) == 500) {
              swal({
                title: respuesta.mensaje,
                text: "",
                html: true,
                type: respuesta.status,
                closeOnConfirm: false,
                showCancelButton: false,
              });
              return false;
            }
            if (parseInt(respuesta.http_code) == 400) {
              // swal('Aviso', respuesta.status, 'warning');
              // listar_transacciones(gen_cliente_id);
              return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
              // window.location.href = "http://localhost/?sec_id=contrato&sub_sec_id=locales";
              sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio();
              sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_gerencia();
              $("#contrato_observaciones").val("");
              $("#contrato_observaciones").focus();
              return false;
            }
          },
          error: function () {},
        });
      }
    }
  );
}

function sec_contrato_detalle_solicitudv2_guardar_observaciones_proveedores_gerencia() {
  var contrato_id = $("#contrato_id_temporal").val();
  var observaciones = $("#contrato_observaciones_proveedor_gerencia")
    .val()
    .trim();
  if (observaciones == "") {
    alertify.error("Ingrese la observación", 5);
    $("#contrato_observaciones_proveedor_gerencia").focus();
    return false;
  }

  var data = {
    accion: "guardar_observaciones_contrato_gerencia",
    contrato_id: contrato_id,
    tipo_observacion: "proveedor",
    observaciones: observaciones,
  };

  auditoria_send({
    proceso: "guardar_observaciones_contrato_proveedor_gerencia",
    data: data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        respuesta: "guardar_observaciones_contrato_proveedor_gerencia",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
      }
      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        swal(
          {
            title: "¡Observación ingresada correctamente!",
            text: respuesta.message,
            type: "success",
            timer: 5000,
            closeOnConfirm: false,
          },
          function () {
            location.reload(); // Recarga la página
          }
        );
        // window.location.href = "http://localhost/?sec_id=contrato&sub_sec_id=locales";
        sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio();
        sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_gerencia();
        $("#contrato_observaciones_proveedor_gerencia").val("");
        $("#contrato_observaciones_proveedor_gerencia").focus();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_guardar_observaciones_proveedores() {
  var contrato_id = $("#contrato_id_temporal").val();
  var observaciones = $("#contrato_observaciones_proveedor").val().trim();
  var correos_adjuntos = $("#correos_adjuntos").val().trim();

  if (observaciones == "") {
    alertify.error("Ingrese la observación", 5);
    $("#contrato_observaciones_proveedor").focus();
    return false;
  }

  swal(
    {
      title: "¿Está seguro de agregar y notificar la observación?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "guardar_observaciones_contrato",
          contrato_id: contrato_id,
          tipo_observacion: "proveedor",
          observaciones: observaciones,
          correos_adjuntos: correos_adjuntos,
        };

        auditoria_send({
          proceso: "guardar_observaciones_contrato_proveedor",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
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
            auditoria_send({
              respuesta: "guardar_observaciones_contrato_proveedor",
              data: respuesta,
            });

            if (parseInt(respuesta.http_code) == 500) {
              swal({
                title: respuesta.mensaje,
                text: "",
                html: true,
                type: respuesta.status,
                closeOnConfirm: false,
                showCancelButton: false,
              });
              return false;
            }
            if (parseInt(respuesta.http_code) == 400) {
              // swal('Aviso', respuesta.status, 'warning');
              // listar_transacciones(gen_cliente_id);
              return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
              // window.location.href = "http://localhost/?sec_id=contrato&sub_sec_id=locales";
              sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio();
              $("#contrato_observaciones_proveedor").val("");
              $("#contrato_observaciones_proveedor").focus();
              return false;
            }
          },
          error: function () {},
        });
      }
    }
  );
}
function sec_contrato_detalle_solicitudv2_insert_nuevo_locales() {
  var data = {
    accion: "insert_nuevo_local",
  };

  auditoria_send({ proceso: "insert_nuevo_local", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: data,
    // beforeSend: function() {
    // 	loading("true");
    // },
    // complete: function() {
    // 	loading();
    // },
    success: function (resp) {
      //  alert(datat)
      var respuesta = JSON.parse(resp);
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio() {
  var contrato_id = $("#contrato_id_temporal").val();

  if (contrato_id > 0) {
    var data = {
      accion: "obtener_observaciones",
      contrato_id: contrato_id,
    };

    auditoria_send({ proceso: "obtener_observaciones", data: data });

    $.ajax({
      url: "/sys/set_contrato_detalle_solicitudv2.php",
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
          return false;
        }

        if (parseInt(respuesta.http_code) == 200) {
          $("#div_observaciones").html(respuesta.result);

          if (respuesta.cant_mensaje > 4) {
            document.getElementById("div_observaciones").style.height = "40em";
            document.getElementById("div_observaciones").style.overflow =
              "scroll";
          }

          return false;
        }
      },
      error: function () {},
    });
  }
}

function sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_gerencia() {
  var contrato_id = $("#contrato_id_temporal").val();

  if (contrato_id > 0) {
    var data = {
      accion: "obtener_observaciones_gerencia",
      contrato_id: contrato_id,
    };

    auditoria_send({ proceso: "obtener_observaciones_gerencia", data: data });

    $.ajax({
      url: "/sys/set_contrato_detalle_solicitudv2.php",
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
          return false;
        }

        if (parseInt(respuesta.http_code) == 200) {
          $("#div_observaciones_gerencia").html(respuesta.result);

          if (respuesta.cant_mensaje > 4) {
            document.getElementById("div_observaciones_gerencia").style.height =
              "40em";
            document.getElementById(
              "div_observaciones_gerencia"
            ).style.overflow = "scroll";
          }

          return false;
        }
      },
      error: function () {},
    });
  }
}
// FIN FUNCIONES DETALLE SOLICITUD

// INICIO FUNCIONES ADENDA
function sec_contrato_detalle_solicitud_locacion_servicio_guardar_adenda_firmada(
  id_adenda
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var adenda_id = $("#adenda_id_" + id_adenda)
    .val()
    .trim();
  var fecha_aplicacion = $("#adenda_fecha_aplicacion_" + id_adenda).val();
  var adenda_firmada = document.getElementById("adenda_firmada_" + id_adenda);

  if (fecha_aplicacion == "") {
    alertify.error("Ingrese la fecha de aplicación", 5);
    $("#adenda_fecha_aplicacion_" + id_adenda).focus();
    return false;
  }
  if (adenda_firmada.files.length == 0) {
    alertify.error("Ingrese la adenda firmada", 5);
    $("#adenda_firmada_" + id_adenda).focus();
    return false;
  }

  var dataForm = new FormData($("#form_adenda_firmada_" + id_adenda)[0]);

  dataForm.append("accion", "guardar_adenda_firmada");
  dataForm.append("contrato_id", contrato_id);
  dataForm.append("fecha_aplicacion", fecha_aplicacion);
  dataForm.append("adenda_id", adenda_id);
  auditoria_send({ proceso: "guardar_adenda_firmada", data: dataForm });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitud_locacion_servicio.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      //  alert(datat)
      var respuesta = JSON.parse(resp);
      auditoria_send({ respuesta: "guardar_adenda_firmada", data: respuesta });
      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
        // }else if(parseInt(respuesta.http_code) == 400){
        // 	if (respuesta.campo_incompleto == "abogado") {
        // 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
        // 		var msg_error_text = '¿Desea ingresar el abogado?';
        // 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
        // 	}

        // 	swal({
        // 		title: msg_error_titulo,
        // 		text: msg_error_text,
        // 		html: true,
        // 		type: "warning",
        // 		showCancelButton: true,
        // 		confirmButtonColor: '#1cb787',
        // 		cancelButtonColor: '#d56d6d',
        // 		confirmButtonText: msg_error_confirmButtonText,
        // 		cancelButtonText: 'CANCELAR'
        // 	}, function (isConfirm) {
        // 		if(isConfirm){
        // 			if (respuesta.campo_incompleto == 'abogado') {
        // 				$('#btn_editar_adenda_abogado_'+id_adenda).click();
        // 			}
        // 		}
        // 	});
      } else if (parseInt(respuesta.http_code) == 200) {
        window.location.href = window.location.href;
        sec_contrato_detalle_solicitudv2_actualizar_provision(contrato_id);
        return false;
      }
    },
    complete: function () {
      loading(false);
    },
  });
}
function sec_contrato_detalle_solicitudv2_actualizar_provision(contrato_id) {
  var settings = {
    url: "/sys/router/provisiones/index.php",
    method: "POST",
    timeout: 0,
    headers: {
      "Content-Type": "application/json",
    },
    data: JSON.stringify({
      action: "generacion_de_provisiones_por_contrato",
      contrato_id: contrato_id,
    }),
  };

  $.ajax(settings).done(function (response) {});
}
function sec_contrato_detalle_solicitudv2_guardar_adenda_proveedor_firmada(
  id_adenda
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var adenda_id = $("#adenda_id_" + id_adenda)
    .val()
    .trim();
  var adenda_firmada = document.getElementById("adenda_firmada_" + id_adenda);

  if (adenda_firmada.files.length == 0) {
    alertify.error("Ingrese la adenda firmada", 5);
    $("#adenda_firmada").focus();
    return false;
  }

  var dataForm = new FormData($("#form_adenda_firmada_" + id_adenda)[0]);

  dataForm.append("accion", "guardar_adenda_proveedor_firmada");
  dataForm.append("contrato_id", contrato_id);
  dataForm.append("adenda_id", adenda_id);

  auditoria_send({
    proceso: "guardar_adenda_proveedor_firmada",
    data: dataForm,
  });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      //  alert(datat)
      var respuesta = JSON.parse(resp);
      auditoria_send({
        respuesta: "guardar_adenda_proveedor_firmada",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
        // }else if(parseInt(respuesta.http_code) == 400){
        // 	if (respuesta.campo_incompleto == "abogado") {
        // 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
        // 		var msg_error_text = '¿Desea ingresar el abogado?';
        // 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
        // 	}

        // 	swal({
        // 		title: msg_error_titulo,
        // 		text: msg_error_text,
        // 		html: true,
        // 		type: "warning",
        // 		showCancelButton: true,
        // 		confirmButtonColor: '#1cb787',
        // 		cancelButtonColor: '#d56d6d',
        // 		confirmButtonText: msg_error_confirmButtonText,
        // 		cancelButtonText: 'CANCELAR'
        // 	}, function (isConfirm) {
        // 		if(isConfirm){
        // 			if (respuesta.campo_incompleto == 'abogado') {
        // 				$('#btn_editar_adenda_abogado_'+id_adenda).click();
        // 			}
        // 		}
        // 	});
      } else if (parseInt(respuesta.http_code) == 200) {
        window.location.href = window.location.href;
        return false;
      }
    },
    complete: function () {
      loading(false);
    },
  });
}
// FIN FUNCIONES ADENDA

// INICIO EDITAR SOLICITUD DE ARRENDAMIENTO

function sec_contrato_detalle_solicitudv2_editar_solicitud(
  nombre_menu_usuario,
  nombre_tabla,
  nombre_campo,
  nombre_campo_usuario,
  tipo_valor,
  valor_actual,
  metodo_select,
  id_tabla
) {
  $("#form_editar_solicitud")[0].reset();
  $("#modal_editar_solicitud").modal({ backdrop: "static", keyboard: false });
  $("#editar_solicitud_nombre_menu_usuario").html(nombre_menu_usuario);
  $("#editar_solicitud_nombre_campo_usuario").html(nombre_campo_usuario);
  $("#editar_solicitud_valor_actual").html(valor_actual);

  $("#editar_solicitud_nombre_tabla").val(nombre_tabla);
  $("#editar_solicitud_nombre_campo").val(nombre_campo);
  $("#editar_solicitud_tipo_valor").val(tipo_valor);
  $("#editar_solicitud_id_tabla").val(id_tabla);

  $("#div_editar_solicitud_departamento").hide();
  $("#div_editar_solicitud_provincias").hide();
  $("#div_editar_solicitud_distrito").hide();

  if (tipo_valor == "varchar") {
    $("#div_editar_solicitud_valor_varchar").show();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_varchar").focus();
    }, 500);
  }

  if (tipo_valor == "textarea") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").show();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_textarea").focus();
    }, 500);
  }

  if (tipo_valor == "int") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").show();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_int").focus();
    }, 500);
  }

  if (tipo_valor == "date") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").show();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_date").focus();
    }, 500);
  }

  if (tipo_valor == "decimal") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").show();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_decimal").focus();
    }, 500);
  }

  if (tipo_valor == "select_option") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    if (nombre_campo == "ubigeo_id") {
      $("#div_editar_solicitud_departamento").show();
      $("#div_editar_solicitud_provincias").show();
      $("#div_editar_solicitud_distrito").show();
      $("#div_editar_solicitud_valor_select_option").hide();
      sec_contrato_detalle_solicitudv2_obtener_opciones(
        metodo_select,
        $("[name='inmueble_id_departamento']")
      );
      setTimeout(function () {
        $("#editar_solicitud_valor_select_option").focus();
      }, 500);
    } else {
      $("#div_editar_solicitud_departamento").hide();
      $("#div_editar_solicitud_provincias").hide();
      $("#div_editar_solicitud_distrito").hide();
      $("#div_editar_solicitud_valor_select_option").show();
      sec_contrato_detalle_solicitudv2_obtener_opciones(
        metodo_select,
        $("[name='editar_solicitud_valor_select_option']")
      );
      setTimeout(function () {
        $("#editar_solicitud_valor_select_option").select2("open");
        $("#editar_solicitud_valor_select_option").focus();
      }, 400);
    }
  }
}

function sec_contrato_detalle_agente_solicitud_editar_solicitud(
  nombre_menu_usuario,
  nombre_tabla,
  nombre_campo,
  nombre_campo_usuario,
  tipo_valor,
  valor_actual,
  metodo_select,
  id_tabla
) {
  $("#form_editar_solicitud")[0].reset();
  $("#modal_editar_solicitud").modal({ backdrop: "static", keyboard: false });
  $("#editar_solicitud_nombre_menu_usuario").html(nombre_menu_usuario);
  $("#editar_solicitud_nombre_campo_usuario").html(nombre_campo_usuario);
  $("#editar_solicitud_valor_actual").html(valor_actual);

  $("#editar_solicitud_nombre_tabla").val(nombre_tabla);
  $("#editar_solicitud_nombre_campo").val(nombre_campo);
  $("#editar_solicitud_tipo_valor").val(tipo_valor);
  $("#editar_solicitud_id_tabla").val(id_tabla);

  $("#div_editar_solicitud_departamento").hide();
  $("#div_editar_solicitud_provincias").hide();
  $("#div_editar_solicitud_distrito").hide();

  if (tipo_valor == "varchar") {
    $("#div_editar_solicitud_valor_varchar").show();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_varchar").focus();
    }, 500);
  }

  if (tipo_valor == "textarea") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").show();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_textarea").focus();
    }, 500);
  }

  if (tipo_valor == "int") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").show();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_int").focus();
    }, 500);
  }

  if (tipo_valor == "date") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").show();
    $("#div_editar_solicitud_valor_decimal").hide();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_date").focus();
    }, 500);
  }

  if (tipo_valor == "decimal") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").show();
    $("#div_editar_solicitud_valor_select_option").hide();
    setTimeout(function () {
      $("#editar_solicitud_valor_decimal").focus();
    }, 500);
  }

  if (tipo_valor == "select_option") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();
    if (nombre_campo == "ubigeo_id") {
      $("#div_editar_solicitud_departamento").show();
      $("#div_editar_solicitud_provincias").show();
      $("#div_editar_solicitud_distrito").show();
      $("#div_editar_solicitud_valor_select_option").hide();
      sec_contrato_detalle_agente_solicitud_obtener_opciones(
        metodo_select,
        $("[name='inmueble_id_departamento']")
      );
      setTimeout(function () {
        $("#editar_solicitud_valor_select_option").focus();
      }, 500);
    } else {
      $("#div_editar_solicitud_departamento").hide();
      $("#div_editar_solicitud_provincias").hide();
      $("#div_editar_solicitud_distrito").hide();
      $("#div_editar_solicitud_valor_select_option").show();
      sec_contrato_detalle_agente_solicitud_obtener_opciones(
        metodo_select,
        $("[name='editar_solicitud_valor_select_option']")
      );
      setTimeout(function () {
        $("#editar_solicitud_valor_select_option").select2("open");
        $("#editar_solicitud_valor_select_option").focus();
      }, 400);
    }
  }
}

function sec_contrato_detalle_agente_solicitud_editar_campo_solicitud(
  name_modal_close
) {
  var nombre_tabla = $("#editar_solicitud_nombre_tabla").val();
  var nombre_campo = $("#editar_solicitud_nombre_campo").val();
  var nombre_menu_usuario = $("#editar_solicitud_nombre_menu_usuario").html();
  var nombre_campo_usuario = $("#editar_solicitud_nombre_campo_usuario").html();
  var valor_actual = $("#editar_solicitud_valor_actual").html();
  var tipo_valor = $("#editar_solicitud_tipo_valor").val();
  var id_tabla = $("#editar_solicitud_id_tabla").val();
  var valor_varchar = $("#editar_solicitud_valor_varchar").val();
  var valor_textarea = $("#editar_solicitud_valor_textarea").val();
  var valor_int = $("#editar_solicitud_valor_int").val();
  var valor_date = $("#editar_solicitud_valor_date").val();
  var valor_decimal = $("#editar_solicitud_valor_decimal").val();
  var valor_select_option = $(
    "#editar_solicitud_valor_select_option option:selected"
  ).text();
  var valor_select_option_id = $("#editar_solicitud_valor_select_option").val();
  var ubigeo_id_nuevo = $("#ubigeo_id_nuevo").val();
  var ubigeo_text_nuevo = $("#ubigeo_text_nuevo").val();
  var contrato_id = $("#contrato_id_temporal").val();

  if ($("#editar_solicitud_valor_varchar").is(":visible")) {
    if (valor_varchar.length < 1) {
      alertify.error("Ingrese un valor", 5);
      $("#editar_solicitud_valor_varchar").focus();
      return false;
    }
  } else {
  }

  if ($("#editar_solicitud_valor_int").is(":visible")) {
    if (valor_int.length < 1) {
      alertify.error("Ingrese un valor", 5);
      $("#editar_solicitud_valor_int").focus();
      return false;
    }
  } else {
  }

  if ($("#editar_solicitud_valor_decimal").is(":visible")) {
    if (valor_decimal.length < 1) {
      alertify.error("Ingrese un valor", 5);
      $("#editar_solicitud_valor_decimal").focus();
      return false;
    }
  } else {
  }

  var data = {
    accion: "editar_solicitud",
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: valor_actual,
    tipo_valor: tipo_valor,
    id_tabla: id_tabla,
    valor_varchar: valor_varchar,
    valor_textarea: valor_textarea,
    valor_int: valor_int,
    valor_date: valor_date,
    valor_decimal: valor_decimal,
    valor_select_option: valor_select_option,
    valor_select_option_id: valor_select_option_id,
    ubigeo_id_nuevo: ubigeo_id_nuevo,
    ubigeo_text_nuevo: ubigeo_text_nuevo,
    contrato_id: contrato_id,
  };

  auditoria_send({ proceso: "editar_solicitud", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: "editar_solicitud", data: respuesta });

      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.error, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (typeof respuesta.insert_error !== "undefined") {
          swal("Aviso", respuesta.insert_error, "warning");
        } else if (typeof respuesta.update_error !== "undefined") {
          swal("Aviso", respuesta.update_error, "warning");
        } else if (respuesta.result == "ok") {
          swal({
            title: "Se actualizó el campo con éxito.",
            text: "",
            type: "success",
            timer: 5000,
            closeOnConfirm: false,
          });

          setTimeout(() => {
            location.reload();
          }, 1000);
        }
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_editar_campo_solicitud_mandato(
  name_modal_close
) {
  var nombre_tabla = $("#editar_solicitud_nombre_tabla").val();
  var nombre_campo = $("#editar_solicitud_nombre_campo").val();
  var nombre_menu_usuario = $("#editar_solicitud_nombre_menu_usuario").html();
  var nombre_campo_usuario = $("#editar_solicitud_nombre_campo_usuario").html();
  var valor_actual = $("#editar_solicitud_valor_actual").html();
  var tipo_valor = $("#editar_solicitud_tipo_valor").val();
  var id_tabla = $("#editar_solicitud_id_tabla").val();
  var valor_varchar = $("#editar_solicitud_valor_varchar").val();
  var valor_textarea = $("#editar_solicitud_valor_textarea").val();
  var valor_int = $("#editar_solicitud_valor_int").val();
  var valor_date = $("#editar_solicitud_valor_date").val();
  var valor_decimal = $("#editar_solicitud_valor_decimal").val();
  var valor_select_option = $(
    "#editar_solicitud_valor_select_option option:selected"
  ).text();
  var valor_select_option_id = $("#editar_solicitud_valor_select_option").val();
  var ubigeo_id_nuevo = $("#ubigeo_id_nuevo").val();
  var ubigeo_text_nuevo = $("#ubigeo_text_nuevo").val();
  var contrato_id = $("#contrato_id_temporal").val();

  var data = {
    accion: "editar_solicitud",
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: valor_actual,
    tipo_valor: tipo_valor,
    id_tabla: id_tabla,
    valor_varchar: valor_varchar,
    valor_textarea: valor_textarea,
    valor_int: valor_int,
    valor_date: valor_date,
    valor_decimal: valor_decimal,
    valor_select_option: valor_select_option,
    valor_select_option_id: valor_select_option_id,
    ubigeo_id_nuevo: ubigeo_id_nuevo,
    ubigeo_text_nuevo: ubigeo_text_nuevo,
    contrato_id: contrato_id,
  };

  auditoria_send({ proceso: "editar_solicitud", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitud_mandato.php",
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
      auditoria_send({ respuesta: "editar_solicitud", data: respuesta });

      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.error, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (typeof respuesta.insert_error !== "undefined") {
          swal("Aviso", respuesta.insert_error, "warning");
        } else if (typeof respuesta.update_error !== "undefined") {
          swal("Aviso", respuesta.update_error, "warning");
        } else if (respuesta.result == "ok") {
          swal({
            title:
              respuesta.requiere_aprobacion == 1
                ? "Se ha registrado la solicitud de cambio que requiere la aprobación del director."
                : "Se actualizó el campo con éxito.",
            text: "",
            type: "success",
            timer: 5000,
            closeOnConfirm: false,
          });

          setTimeout(() => {
            location.reload();
          }, 2000);
        }
        sec_contrato_detalle_solicitudv2_actualizar_provision(contrato_id);

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_agente_solicitud_obtener_opciones(
  accion,
  select
) {
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
    type: "POST",
    data: { accion: accion }, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      //  alert(datat)
      var respuesta = JSON.parse(datos);
      $(select).find("option").remove().end();
      $(select).append('<option value="0">- Seleccione -</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $(select).append(opcion);
      });
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_obtener_opciones(accion, select) {
  $.ajax({
    url: "/sys/set_contrato_nuevo.php",
    type: "POST",
    data: { accion: accion }, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      //  alert(datat)
      var respuesta = JSON.parse(datos);
      $(select).find("option").remove().end();
      $(select).append('<option value="0">- Seleccione -</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $(select).append(opcion);
      });
    },
    error: function () {},
  });
}

$("#inmueble_id_departamento").change(function () {
  $("#inmueble_id_departamento option:selected").each(function () {
    inmueble_id_departamento = $(this).val();
    var data = {
      accion: "obtener_provincias_segun_departamento",
      departamento_id: inmueble_id_departamento,
    };
    var array_provincias = [];
    auditoria_send({
      proceso: "obtener_provincias_segun_departamento",
      data: data,
    });
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
        //  alert(datat)
        var respuesta = JSON.parse(resp);

        if (parseInt(respuesta.http_code) == 400) {
        }

        if (parseInt(respuesta.http_code) == 200) {
          array_provincias.push(respuesta.result);
          var html = '<option value="0">Seleccione la provincia</option>';

          for (var i = 0; i < array_provincias[0].length; i++) {
            html +=
              "<option value=" +
              array_provincias[0][i].id +
              ">" +
              array_provincias[0][i].nombre +
              "</option>";
          }

          $("#inmueble_id_provincia").html(html).trigger("change");

          setTimeout(function () {
            $("#inmueble_id_provincia").select2("open");
          }, 500);

          return false;
        }
      },
      error: function () {},
    });
  });
});

$("#inmueble_id_provincia").change(function () {
  $("#inmueble_id_provincia option:selected").each(function () {
    inmueble_id_provincia = $(this).val();
    inmueble_id_departamento = $("#inmueble_id_departamento").val();
    var data = {
      accion: "obtener_distritos_segun_provincia",
      provincia_id: inmueble_id_provincia,
      departamento_id: inmueble_id_departamento,
    };
    var array_distritos = [];
    auditoria_send({
      proceso: "obtener_distritos_segun_provincia",
      data: data,
    });
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
        //  alert(datat)
        var respuesta = JSON.parse(resp);

        if (parseInt(respuesta.http_code) == 400) {
        }

        if (parseInt(respuesta.http_code) == 200) {
          array_distritos.push(respuesta.result);

          var html = '<option value="0">Seleccione el distrito</option>';

          for (var i = 0; i < array_distritos[0].length; i++) {
            html +=
              "<option value=" +
              array_distritos[0][i].id +
              ">" +
              array_distritos[0][i].nombre +
              "</option>";
          }

          $("#inmueble_id_distrito").html(html).trigger("change");

          setTimeout(function () {
            $("#inmueble_id_distrito").select2("open");
          }, 500);

          return false;
        }
      },
      error: function () {},
    });
  });
});

$("#inmueble_id_distrito").change(function () {
  var departamento_id = $("#inmueble_id_departamento").val().toString();
  var provincia_id = $("#inmueble_id_provincia").val().toString();
  var distrito_id = $("#inmueble_id_distrito").val().toString();

  var departamento_text = "";
  var data = $("#inmueble_id_departamento").select2("data");
  if (data) {
    departamento_text = data[0].text;
  }

  var provincia_text = "";
  var data = $("#inmueble_id_provincia").select2("data");
  if (data) {
    provincia_text = data[0].text;
  }

  var distrito_text = "";
  var data = $("#inmueble_id_distrito").select2("data");
  if (data) {
    distrito_text = data[0].text;
  }

  $("#ubigeo_id_nuevo").val(departamento_id + provincia_id + distrito_id);
  $("#ubigeo_text_nuevo").val(
    departamento_text + "/" + provincia_text + "/" + distrito_text
  );
});

$("#cont_detalle_proveedor_contrato_firmado_categoria_param").change(
  function () {
    $(
      "#cont_detalle_proveedor_contrato_firmado_categoria_param option:selected"
    ).each(function () {
      cont_detalle_proveedor_contrato_firmado_categoria_param = $(this).val();
      var data = {
        accion: "cont_detalle_solicitudv2_proveedor_obtener_tipo_contrato",
        cont_detalle_proveedor_contrato_firmado_categoria_param:
          cont_detalle_proveedor_contrato_firmado_categoria_param,
      };

      var array_cont_detalle_proveedor_contrato_firmado_tipo_contrato_param =
        [];
      auditoria_send({
        proceso: "cont_detalle_solicitudv2_proveedor_obtener_tipo_contrato",
        data: data,
      });
      $.ajax({
        url: "/sys/set_contrato_detalle_solicitudv2.php",
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
          $(
            "#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param"
          ).empty();
          if (parseInt(respuesta.http_code) == 400) {
          }

          if (parseInt(respuesta.http_code) == 200) {
            array_cont_detalle_proveedor_contrato_firmado_tipo_contrato_param.push(
              respuesta.result
            );
            var html = '<option value="0">-- Seleccione --</option>';

            for (
              var i = 0;
              i <
              array_cont_detalle_proveedor_contrato_firmado_tipo_contrato_param[0]
                .length;
              i++
            ) {
              html +=
                "<option value=" +
                array_cont_detalle_proveedor_contrato_firmado_tipo_contrato_param[0][
                  i
                ].id +
                ">" +
                array_cont_detalle_proveedor_contrato_firmado_tipo_contrato_param[0][
                  i
                ].nombre +
                "</option>";
            }

            $("#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param")
              .html(html)
              .trigger("change");

            setTimeout(function () {
              $(
                "#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param"
              ).select2("open");
            }, 500);

            return false;
          }
        },
        error: function () {},
      });
    });
  }
);

function enviar_por_email_solicitud_al_jefe_de_arrendamiento(contrato_id) {
  var data = {
    accion: "llamar_funcion_send_email_solicitud_contrato_locales_detallado",
    contrato_id: contrato_id,
  };

  swal(
    {
      html: true,
      title: "Reenviar Email",
      text: "¿Desea reenviar el email?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#1cb787",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SÍ, REENVIAR EMAIL",
      cancelButtonText: "CANCELAR",
      closeOnConfirm: false,
      //,showLoaderOnConfirm: true
    },
    function () {
      auditoria_send({
        proceso:
          "llamar_funcion_send_email_solicitud_contrato_locales_detallado",
        data: data,
      });
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
          //  alert(datat)
          var respuesta = JSON.parse(resp);

          if (parseInt(respuesta.http_code) == 400) {
            swal({
              title: "Error al enviar Solicitud de Arrendamiento",
              text: respuesta.error,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }

          if (parseInt(respuesta.http_code) == 200) {
            swal({
              title: "Reenvío exitoso",
              text: "La solicitud de arrendamiento fue enviada exitosamente",
              html: true,
              type: "success",
              timer: 6000,
              closeOnConfirm: false,
              showCancelButton: false,
            });

            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}

function sec_contrato_detalle_solicitudv2_reenviar_por_email_solicitud_de_arrendamiento(
  contrato_id
) {
  var data = {
    accion: "llamar_funcion_send_email_solicitud_de_arrendamiento",
    contrato_id: contrato_id,
  };

  swal(
    {
      html: true,
      title: "Reenviar Email",
      text: "¿Desea reenviar el email?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#1cb787",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SÍ, REENVIAR EMAIL",
      cancelButtonText: "CANCELAR",
      closeOnConfirm: false,
    },
    function () {
      auditoria_send({
        proceso: "llamar_funcion_send_email_solicitud_de_arrendamiento",
        data: data,
      });

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

          auditoria_send({
            proceso: "llamar_funcion_send_email_solicitud_de_arrendamiento",
            data: respuesta,
          });

          if (parseInt(respuesta.http_code) == 400) {
            swal({
              title: "Error al enviar Solicitud de Arrendamiento",
              text: respuesta.error,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }

          if (parseInt(respuesta.http_code) == 200) {
            swal({
              title: "Reenvío exitoso",
              text: "La solicitud de arrendamiento fue enviada exitosamente",
              html: true,
              type: "success",
              timer: 6000,
              closeOnConfirm: false,
              showCancelButton: false,
            });

            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}

function sec_contrato_detalle_solicitudv2_enviar_formato_de_pago(contrato_id) {
  var data = {
    accion: "consultar_asignacion_del_nombre_de_tienda",
    contrato_id: contrato_id,
  };

  auditoria_send({
    proceso: "consultar_asignacion_del_nombre_de_tienda",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
        if (respuesta.error == "sin_asignar") {
          swal(
            {
              title:
                "No se pudo enviar el email, el Nombre de la Tienda es un requisito para el envio.",
              text: "¿Desea ingresar el nombre de la tienda?",
              html: true,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#1cb787",
              cancelButtonColor: "#d56d6d",
              confirmButtonText: "SI, AGREGAR EL NOMBRE DE LA TIENDA",
              cancelButtonText: "CANCELAR",
            },
            function (isConfirm) {
              if (isConfirm) {
                $("#btn_editar_nombre_de_la_tienda").click();
              }
            }
          );
        } else {
          swal({
            title: "Error al consultar el nombre de la tienda",
            text: respuesta.error,
            html: true,
            type: "warning",
            closeOnConfirm: false,
            showCancelButton: false,
          });
        }
      }

      if (parseInt(respuesta.http_code) == 200) {
        var data = {
          accion: "llamar_funcion_send_email_formato_de_pago",
          contrato_id: contrato_id,
        };

        swal(
          {
            html: true,
            title: "Enviar Formato de Pago",
            text: "¿Desea enviar el email?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#1cb787",
            cancelButtonColor: "#d56d6d",
            confirmButtonText: "SI, ENVIAR EMAIL",
            cancelButtonText: "CANCELAR",
            closeOnConfirm: false,
            //,showLoaderOnConfirm: true
          },
          function () {
            auditoria_send({
              proceso: "llamar_funcion_send_email_formato_de_pago",
              data: data,
            });
            $.ajax({
              url: "/sys/set_contrato_detalle_solicitudv2.php",
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
                  swal({
                    title: "Error al enviar Formato de Pago",
                    text: respuesta.error,
                    html: true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false,
                  });
                }

                if (parseInt(respuesta.http_code) == 200) {
                  sec_contrato_detalle_solicitudv2_emails_enviados_formato_de_pago();

                  swal({
                    title: "Envio exitoso",
                    text: "El Formato de Pago fue enviado exitosamente",
                    html: true,
                    type: "success",
                    timer: 6000,
                    closeOnConfirm: false,
                    showCancelButton: false,
                  });

                  return false;
                }
              },
              error: function () {},
            });
          }
        );

        return false;
      }
    },
    error: function () {},
  });
}

function enviar_por_email_solicitud_al_lourdes_britto(contrato_id) {
  var data = {
    accion:
      "llamar_funcion_send_email_solicitud_proveedor_detalle_lourdes_britto",
    contrato_id: contrato_id,
  };

  swal(
    {
      html: true,
      title: "Reenviar Email",
      text: "¿Desea reenviar el email?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#1cb787",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SÍ, REENVIAR EMAIL",
      cancelButtonText: "CANCELAR",
      closeOnConfirm: false,
      //,showLoaderOnConfirm: true
    },
    function () {
      auditoria_send({
        proceso:
          "llamar_funcion_send_email_solicitud_proveedor_detalle_lourdes_britto",
        data: data,
      });
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
          //  alert(datat)
          var respuesta = JSON.parse(resp);

          if (parseInt(respuesta.http_code) == 400) {
            swal({
              title: "Error al enviar Solicitud de Proveedor",
              text: respuesta.error,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }

          if (parseInt(respuesta.http_code) == 200) {
            swal({
              title: "Reenvío exitoso",
              text: "La solicitud de proveedor fue enviada exitosamente",
              html: true,
              type: "success",
              timer: 6000,
              closeOnConfirm: false,
              showCancelButton: false,
            });

            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}

function enviar_por_email_solicitud_al_lourdes_britto_gerencia(contrato_id) {
  var data = {
    accion:
      "llamar_funcion_send_email_solicitud_proveedor_gerencia_detalle_lourdes_britto",
    contrato_id: contrato_id,
  };

  swal(
    {
      html: true,
      title: "Notificar Observación Corregida",
      text: "¿Desea notificar a Director(a)?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#1cb787",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SI, NOTIFICAR",
      cancelButtonText: "CANCELAR",
      closeOnConfirm: false,
      //,showLoaderOnConfirm: true
    },
    function () {
      auditoria_send({
        proceso:
          "llamar_funcion_send_email_solicitud_proveedor_detalle_lourdes_britto",
        data: data,
      });
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
          //  alert(datat)
          var respuesta = JSON.parse(resp);

          if (parseInt(respuesta.http_code) == 400) {
            swal({
              title: "Error al enviar Solicitud de Proveedor",
              text: respuesta.error,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }

          if (parseInt(respuesta.http_code) == 200) {
            swal({
              title: "Reenvío exitoso",
              text: "La solicitud de proveedor fue enviada exitosamente",
              html: true,
              type: "success",
              timer: 6000,
              closeOnConfirm: false,
              showCancelButton: false,
            });

            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}

function sec_contrato_detalle_solicitudv2_enviar_por_email_solicitud_de_proveedor(
  contrato_id
) {
  var data = {
    accion: "llamar_funcion_send_email_solicitud_proveedor",
    contrato_id: contrato_id,
  };

  swal(
    {
      html: true,
      title: "Reenviar Email",
      text: "¿Desea reenviar el email?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#1cb787",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SÍ, REENVIAR EMAIL",
      cancelButtonText: "CANCELAR",
      closeOnConfirm: false,
      //,showLoaderOnConfirm: true
    },
    function () {
      auditoria_send({
        proceso: "llamar_funcion_send_email_solicitud_proveedor",
        data: data,
      });
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
          //  alert(datat)
          var respuesta = JSON.parse(resp);

          auditoria_send({
            proceso: "llamar_funcion_send_email_solicitud_proveedor",
            data: respuesta,
          });

          if (parseInt(respuesta.http_code) == 400) {
            swal({
              title: "Error al enviar Solicitud de Proveedor",
              text: respuesta.error,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }

          if (parseInt(respuesta.http_code) == 200) {
            swal({
              title: "Reenvío exitoso",
              text: "La solicitud de proveedor fue enviada exitosamente",
              html: true,
              type: "success",
              timer: 6000,
              closeOnConfirm: false,
              showCancelButton: false,
            });

            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}

function enviar_por_email_acuerdo_de_confidencialidad_a_lourdes_britto(
  contrato_id
) {
  var data = {
    accion:
      "llamar_funcion_send_email_solicitud_acuerdo_confidencialidad_detalle_lourdes_britto",
    contrato_id: contrato_id,
  };

  swal(
    {
      html: true,
      title: "Reenviar Email",
      text: "¿Desea reenviar el email?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#1cb787",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SÍ, REENVIAR EMAIL",
      cancelButtonText: "CANCELAR",
      closeOnConfirm: false,
      //,showLoaderOnConfirm: true
    },
    function () {
      auditoria_send({
        proceso:
          "llamar_funcion_send_email_solicitud_acuerdo_confidencialidad_detalle_lourdes_britto",
        data: data,
      });
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
          //  alert(datat)
          var respuesta = JSON.parse(resp);

          if (parseInt(respuesta.http_code) == 400) {
            swal({
              title:
                "Error al enviar Solicitud de Acuerdo de Confidencialidad de Proveedor",
              text: respuesta.error,
              html: true,
              type: "warning",
              closeOnConfirm: false,
              showCancelButton: false,
            });
          }

          if (parseInt(respuesta.http_code) == 200) {
            swal({
              title: "Reenvío exitoso",
              text: "La solicitud de Acuerdo de Confidencialidad fue enviada exitosamente",
              html: true,
              type: "success",
              timer: 6000,
              closeOnConfirm: false,
              showCancelButton: false,
            });

            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}

function agregarNuevoAnexoConProv() {
  $("#modalNuevosAnexosConProv").modal({ backdrop: "static", keyboard: false });
  sec_nuevo_cargar_tipos_anexos_con_prov();
}

function sec_nuevo_cargar_tipos_anexos_con_prov() {
  $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").html("");
  $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").append(
    '<option value="0"> - Seleccione - </option>'
  );

  var tipo_contrato_id = 2;
  var data = {
    accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
    tipo_contrato_id: tipo_contrato_id,
  };
  auditoria_send({
    proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos",
    data: data,
  });
  $.ajax({
    url: "sys/get_contrato_nuevo.php",
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
        $.each(respuesta.result, function (index, item) {
          $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").append(
            '<option value="' +
              item.tipo_archivo_id +
              '">' +
              item.nombre_tipo_archivo +
              "</option>"
          );
        });
        return false;
      }
    },
    error: function () {},
  });
}

function agregarNuevoAnexoConAgente() {
  $("#modalNuevosAnexosConProv").modal({ backdrop: "static", keyboard: false });
  sec_nuevo_cargar_tipos_anexos_con_agente();
}

function sec_nuevo_cargar_tipos_anexos_con_agente() {
  $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").html("");
  $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").append(
    '<option value="0"> - Seleccione - </option>'
  );

  var tipo_contrato_id = 6;
  var data = {
    accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
    tipo_contrato_id: tipo_contrato_id,
  };
  auditoria_send({
    proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos",
    data: data,
  });
  $.ajax({
    url: "sys/get_contrato_nuevo.php",
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
        $.each(respuesta.result, function (index, item) {
          $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").append(
            '<option value="' +
              item.tipo_archivo_id +
              '">' +
              item.nombre_tipo_archivo +
              "</option>"
          );
        });
        return false;
      }
    },
    error: function () {},
  });
}

function agregarNuevoAnexoConAcuerdoConfidencilidad() {
  $("#modalNuevosAnexosConProv").modal({ backdrop: "static", keyboard: false });
  sec_nuevo_cargar_tipos_anexos_con_acuerdo_confidencialidad();
}

function agregarNuevoAnexoConAcuerdoConfidencilidad() {
  $("#modalNuevosAnexosConProv").modal({ backdrop: "static", keyboard: false });
  sec_nuevo_cargar_tipos_anexos_con_acuerdo_confidencialidad();
}

function sec_nuevo_cargar_tipos_anexos_con_acuerdo_confidencialidad() {
  $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").html("");
  $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").append(
    '<option value="0"> - Seleccione - </option>'
  );

  var tipo_contrato_id = 5;
  var data = {
    accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
    tipo_contrato_id: tipo_contrato_id,
  };
  auditoria_send({
    proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos",
    data: data,
  });
  $.ajax({
    url: "sys/get_contrato_nuevo.php",
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
        $.each(respuesta.result, function (index, item) {
          $("#modal_nuevo_anexo_select_tipos_anexos_con_prov").append(
            '<option value="' +
              item.tipo_archivo_id +
              '">' +
              item.nombre_tipo_archivo +
              "</option>"
          );
        });
        return false;
      }
    },
    error: function () {},
  });
}

function agregarNuevoAnexoDetalleProveedor() {
  $("#sec_nuevo_con_agregar_nuevo_tipo_archivo_contrato_proveedor").modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#sec_nuevo_tipo_anexo_nombre_con_prov").val("");
  setTimeout(function () {
    $("#sec_nuevo_tipo_anexo_nombre_con_prov").focus();
  }, 500);
}

function guardarNuevoTipoAnexoConProv(tipo_contrato) {
  if ($("#sec_nuevo_tipo_anexo_nombre_con_prov").val() == "") {
    swal({
      title: "Ingrese el nombre del tipo de anexo nuevo",
      text: respuesta.error,
      html: true,
      type: "warning",
      closeOnConfirm: false,
      showCancelButton: false,
    });
    return false;
  }

  var anexo = $("#sec_nuevo_tipo_anexo_nombre_con_prov").val();
  var data = {
    accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
    anexo: anexo,
    tipo_contrato_id: tipo_contrato,
  };
  auditoria_send({
    proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
    data: data,
  });
  $.ajax({
    url: "sys/set_contrato_nuevo.php",
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
        $("#sec_nuevo_con_agregar_nuevo_tipo_archivo_contrato_proveedor").modal(
          "hide"
        );
        setTimeout(function () {
          $("#modal_nuevo_anexo_select_tipos_anexos_con_prov")
            .val(respuesta.result)
            .trigger("change");
        }, 1500);

        sec_nuevo_cargar_tipos_anexos_con_prov();
        if (tipo_contrato == 5) {
          sec_nuevo_cargar_tipos_anexos_con_acuerdo_confidencialidad();
        } else if (tipo_contrato == 6) {
          sec_nuevo_cargar_tipos_anexos_con_agente();
        }

        return false;
      }
    },
    error: function () {},
  });
}

function guardarNuevoTipoAnexoConAcuerdoConfidencialidad(tipo_contrato) {
  if ($("#sec_nuevo_tipo_anexo_nombre_con_prov").val() == "") {
    swal({
      title: "Ingrese el nombre del tipo de anexo nuevo",
      text: respuesta.error,
      html: true,
      type: "warning",
      closeOnConfirm: false,
      showCancelButton: false,
    });
    return false;
  }

  var anexo = $("#sec_nuevo_tipo_anexo_nombre_con_prov").val();
  var data = {
    accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
    anexo: anexo,
    tipo_contrato_id: tipo_contrato,
  };
  auditoria_send({
    proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
    data: data,
  });
  $.ajax({
    url: "sys/set_contrato_nuevo.php",
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
        $("#sec_nuevo_con_agregar_nuevo_tipo_archivo_contrato_proveedor").modal(
          "hide"
        );
        sec_nuevo_cargar_tipos_anexos_con_acuerdo_confidencialidad();
        setTimeout(function () {
          $("#modal_nuevo_anexo_select_tipos_anexos_con_prov")
            .val(respuesta.result)
            .trigger("change");
        }, 1500);
        swal(result.message, "", "success");
        return false;
      }
    },
    error: function () {},
  });
}

function sec_det_modal_guardar_nuevo_anexo_con_prov() {
  var contrato_id = $("#sec_det_con_prov_id_contrato").val();
  var id_tipo_archivo = $(
    "#modal_nuevo_anexo_select_tipos_anexos_con_prov"
  ).val();

  if (id_tipo_archivo == 0) {
    alertify.error("Seleccione el tipo de anexo", 5);
  } else {
    var form_data = new FormData($("#sec_nuevo_form_modal_nuevo_anexo")[0]);
    form_data.append("post_archivo_req_solicitud_arrendamiento", 1);
    form_data.append("id_archivo", "0");
    form_data.append("contrato_id", contrato_id);
    form_data.append("id_tipo_archivo", id_tipo_archivo);
    form_data.append("id_representante_legal", "");

    loading(true);

    auditoria_send({
      proceso: "post_archivo_req_solicitud_arrendamiento",
      data: form_data,
    });

    $.ajax({
      url: "/sys/set_contrato_detalle_solicitudv2.php",
      type: "POST",
      data: form_data,
      cache: false,
      contentType: false,
      processData: false,
      success: function (response, status) {
        result = JSON.parse(response);
        loading();
        if (result.status) {
          m_reload();
          swal(result.message, "", "success");
        } else {
          swal({
            type: "warning",
            title: "Alerta!",
            text: result.message,
            html: true,
          });
        }
      },
      always: function (data) {
        loading();
      },
    });
  }
}

function limpiarInputsRL() {
  $("#sec_con_det_dni_representante").val("");
  $("#sec_con_det_nombre_representante").val("");
  $("#sec_con_det_sec_con_nuev_prov_nro_cuenta").val("");
  $("#sec_con_det_sec_con_nuev_prov_nro_cci").val("");
  $("#sec_con_det_sec_con_nuevo_prov_banco").val("0").trigger("change.select2");
  $("#sec_con_det_prov_file_vigencia_nuevo_rl").val("");
  $("#sec_con_det_prov_file_dni_nuevo_rl").val("");
}

function sec_con_det_prov_agregar_representante() {
  $("#modalSecConDetProvAgregarRepresentante").modal({
    backdrop: "static",
    keyboard: false,
  });
  limpiarInputsRL();
}

function sec_con_det_prov_guardar_nuevo_representante_legal() {
  var contrato_id = $(
    "#sec_con_det_prov_id_contrato_modal_nuevo_representante"
  ).val();
  var dniRepresentante = $("#sec_con_det_dni_representante").val();
  if (dniRepresentante.length != 8) {
    alertify.error("DNI debe tener 8 dígitos", 8);
    return false;
  }
  var nombreRepresentante = $("#sec_con_det_nombre_representante").val();
  var banco = $("#sec_con_det_sec_con_nuevo_prov_banco").val();
  var banco_nombre = $(
    "#sec_con_det_sec_con_nuevo_prov_banco option:selected"
  ).text();
  var nro_cuenta = $("#sec_con_det_sec_con_nuev_prov_nro_cuenta").val();
  var nro_cci = $("#sec_con_det_sec_con_nuev_prov_nro_cci").val();
  var input_vacios = "";
  if ($.trim(dniRepresentante) == "") {
    input_vacios += " - DNI del Representante";
  }
  if ($.trim(nombreRepresentante) == "") {
    input_vacios += " - Nombre del Representante";
  }
  if ($.trim(banco) == 0) {
    input_vacios += " - Banco";
  }
  if ($.trim(nro_cuenta) == "" && $.trim(nro_cci) == "") {
    input_vacios += " - Nro Cuenta o CCI";
  }

  if ($.trim(input_vacios) != "") {
    alertify.error("Datos Vacios: " + input_vacios, 8);
    return;
  }

  var form_data = new FormData(
    $("#sec_con_nuevo_agregar_nuevo_representante_legal_form")[0]
  );
  form_data.append("accion", "sec_con_det_prov_agregar_representante_legal");
  form_data.append("contrato_id", contrato_id);
  form_data.append("dniRepresentante", dniRepresentante);
  form_data.append("nombreRepresentante", nombreRepresentante);
  form_data.append("banco", banco);
  form_data.append("nro_cuenta", nro_cuenta);
  form_data.append("nro_cci", nro_cci);
  loading(true);

  auditoria_send({
    proceso: "sec_con_det_prov_agregar_representante_legal",
    data: form_data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status) {
        m_reload();
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_con_det_prov_guardar_nuevo_representante_legal_acuerdo_confidencialidad() {
  var contrato_id = $(
    "#sec_con_det_prov_id_contrato_modal_nuevo_representante"
  ).val();
  var dniRepresentante = $("#sec_con_det_dni_representante").val();
  if (dniRepresentante.length != 8) {
    alertify.error("DNI debe tener 8 dígitos", 8);
    return false;
  }
  var nombreRepresentante = $("#sec_con_det_nombre_representante").val();
  var banco = 0;
  var banco_nombre = "";
  var nro_cuenta = "";
  var nro_cci = "";
  var input_vacios = "";
  if ($.trim(dniRepresentante) == "") {
    input_vacios += " - DNI del Representante";
  }
  if ($.trim(nombreRepresentante) == "") {
    input_vacios += " - Nombre del Representante";
  }

  if ($.trim(input_vacios) != "") {
    alertify.error("Datos Vacios: " + input_vacios, 8);
    return;
  }

  var form_data = new FormData(
    $("#sec_con_nuevo_agregar_nuevo_representante_legal_form")[0]
  );
  form_data.append(
    "accion",
    "sec_con_det_prov_agregar_representante_legal_acuerdo_confidencialidad"
  );
  form_data.append("contrato_id", contrato_id);
  form_data.append("dniRepresentante", dniRepresentante);
  form_data.append("nombreRepresentante", nombreRepresentante);
  form_data.append("banco", banco);
  form_data.append("nro_cuenta", nro_cuenta);
  form_data.append("nro_cci", nro_cci);
  loading(true);

  auditoria_send({
    proceso:
      "sec_con_det_prov_agregar_representante_legal_acuerdo_confidencialidad",
    data: form_data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status) {
        m_reload();
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_contrato_detalle_solicitudv2_emails_enviados_formato_de_pago() {
  var contrato_id = $("#contrato_id_temporal").val();

  var data = {
    accion: "obtener_emails_enviados_formato_de_pago",
    contrato_id: contrato_id,
  };

  auditoria_send({
    proceso: "obtener_emails_enviados_formato_de_pago",
    data: data,
  });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      // loading("true");
    },
    complete: function () {
      // loading();
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }

      if (parseInt(respuesta.http_code) == 200) {
        $("#div_emails_enviados_formato_de_pago").html(respuesta.result);
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_limpiar_inputs_contraprestacion() {
  $("#moneda_id").val("0").trigger("change.select2");
  $("#subtotal").val("");
  $("#igv").val("");
  $("#monto").val("");
  $("#forma_pago").val("0").trigger("change.select2");
  $("#tipo_comprobante").val("0").trigger("change.select2");
  $("#plazo_pago").val("");
}

function sec_contrato_detalle_solicitudv2_agregar_contraprestacion() {
  $("#modal_agregar_contraprestacion").modal({
    backdrop: "static",
    keyboard: false,
  });
  sec_contrato_detalle_solicitudv2_limpiar_inputs_contraprestacion();
  setTimeout(function () {
    $("#moneda_id").focus();
    $("#moneda_id").select2("open");
  }, 500);
}

// INICIO SUMINISTRO
function sec_contrato_detalle_solicitudv2_llamar_modal_agregar_suministro(
  inmueble_id = 0,
  contrato_detalle_id = 0
) {
  $("#modal_suministro").modal({ backdrop: "static", keyboard: false });
  $("#modal_suministro_inmueble_id").val(inmueble_id);
  $("#modal_suministro_contrato_detalle_id").val(contrato_detalle_id);
  setTimeout(function () {
    $("#modal_suministo_tipo_servicio_id").focus();
    $("#modal_suministo_tipo_servicio_id").select2("open");
  }, 500);
}

function sec_contrato_detalle_solicitudv2_guardar_suministro() {
  var contrato_id = $("#contrato_id_temporal").val();
  var contrato_detalle_id = $("#modal_suministro_contrato_detalle_id").val();
  var inmueble_id = $("#modal_suministro_inmueble_id").val();
  var tipo_servicio_id = $("#modal_suministo_tipo_servicio_id").val();
  var nro_suministro = $("#modal_suministo_nro_suministro").val();
  var compromiso_pago_id = $("#modal_suministo_compromiso_pago_id").val();
  var monto_o_porcentaje = $("#modal_suministo_monto_o_porcentaje").val();

  if (tipo_servicio_id.length == 0 || tipo_servicio_id == 0) {
    alertify.error("Seleccione un tipo de servicio");
    $("#modal_suministo_tipo_servicio_id").focus();
    $("#modal_suministo_tipo_servicio_id").select2("open");
    return false;
  }

  if (nro_suministro.length < 7) {
    alertify.error("El número de suministro debe ser mayor a 6 dígitos", 5);
    $("#modal_suministo_nro_suministro").focus();
    return false;
  }

  if (compromiso_pago_id.length == 0 || compromiso_pago_id == 0) {
    alertify.error("Seleccione un tipo de compromiso de pago");
    $("#modal_suministo_compromiso_pago_id").focus();
    $("#modal_suministo_compromiso_pago_id").select2("open");
    return false;
  }

  if (parseInt(compromiso_pago_id) == 1 && monto_o_porcentaje.length == 0) {
    alertify.error(
      "Ingrese el porcentaje del pago del servicio del inmuble",
      5
    );
    $("#modal_suministo_monto_o_porcentaje").focus();
    return false;
  }

  if (parseInt(compromiso_pago_id) == 2 && monto_o_porcentaje.length == 0) {
    alertify.error(
      "Ingrese el monto fijo del pago del servicio del inmuble",
      5
    );
    $("#modal_suministo_monto_o_porcentaje").focus();
    return false;
  }

  var data = {
    accion: "guardar_nuevo_suministro",
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    inmueble_id: inmueble_id,
    tipo_servicio_id: tipo_servicio_id,
    nro_suministro: nro_suministro,
    compromiso_pago_id: compromiso_pago_id,
    monto_o_porcentaje: monto_o_porcentaje,
  };

  auditoria_send({ proceso: "guardar_nuevo_suministro", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        respuesta: "guardar_nuevo_suministro",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }

        return false;
      }
    },
    error: function () {},
  });
}
// FIN SUMINISTRO

// INICIO ADELANTOS
function sec_contrato_detalle_solicitudv2_llamar_modal_agregar_adelanto(
  contrato_detalle_id = 0
) {
  $("#modal_adelantos").modal({ backdrop: "static", keyboard: false });
  $("#modad_adel_contrato_detalle_id").val(contrato_detalle_id);
  setTimeout(function () {
    $("#num_periodo_id").focus();
    $("#num_periodo_id").select2("open");
  }, 500);
}

function sec_contrato_detalle_solicitudv2_guardar_adelanto() {
  var contrato_id = $("#contrato_id_temporal").val();
  var num_periodo_id = $("#num_periodo_id").val();
  var contrato_detalle_id = $("#modad_adel_contrato_detalle_id").val();
  var num_periodo = "";
  var data = $("#num_periodo_id").select2("data");
  if (data) {
    num_periodo = data[0].text;
  }

  var data = {
    accion: "guardar_nuevo_adelanto",
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    num_periodo_id: num_periodo_id,
    num_periodo: num_periodo,
  };

  auditoria_send({ proceso: "guardar_nuevo_adelanto", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: "guardar_nuevo_adelanto", data: respuesta });

      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }

        return false;
      }
    },
    error: function () {},
  });
}
// FIN ADELANTOS

// INICIO INCREMENTOS
function sec_contrato_detalle_solicitudv2_llamar_modal_agregar_incrementos(
  contrato_detalle_id = ""
) {
  sec_contrato_nuevo_resetear_formulario_nuevo_incremento("new");
  $("#modal_agregar_incrementos").modal({
    backdrop: "static",
    keyboard: false,
  });
  setTimeout(function () {
    $("#contrato_incrementos_monto_o_porcentaje").focus();
  }, 500);
  $("#modal_inc_contrato_detalle_id").val(contrato_detalle_id);
}

function sec_contrato_nuevo_resetear_formulario_nuevo_incremento(evento) {
  $("#frm_incremento")[0].reset();
  $("#contrato_incrementos_en").val("0").trigger("change");
  $("#contrato_incrementos_continuidad").val("0").trigger("change");
  $("#contrato_incrementos_a_partir_de_año").val("0").trigger("change");

  if (evento == "new") {
    $("#modal_incremento_titulo").html("Registrar Incremento");
    $("#btn_agregar_incremento").show();
    $("#btn_guardar_cambios_incremento").hide();
  } else if (evento == "edit") {
    $("#modal_incremento_titulo").html("Editar Incremento");
    $("#btn_agregar_incremento").hide();
    $("#btn_guardar_cambios_incremento").show();
  }

  setTimeout(function () {
    $("#contrato_incrementos_en").select2("close");
    $("#contrato_incrementos_continuidad").select2("close");
    $("#contrato_incrementos_a_partir_de_año").select2("close");
  }, 200);
}

function sec_contrato_detalle_solicitudv2_guardar_incremento() {
  var proceso = "guardar_incremento";
  var data = sec_contrato_nuevo_validar_campos_formulario_incremento(proceso);

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: proceso, data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: proceso, data: respuesta });

      if (parseInt(respuesta.http_code) == 500) {
        swal({
          title: respuesta.mensaje,
          text: "",
          html: true,
          type: respuesta.status,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
      }

      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.error, "warning");
        return false;
      }

      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          $("#modal_agregar_incrementos").modal("hide");
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_validar_campos_formulario_incremento(accion) {
  var contrato_id = $("#contrato_id_temporal").val();
  var contrato_detalle_id = $("#modal_inc_contrato_detalle_id").val();
  var id_incremento_para_cambios = $(
    "#contrato_incrementos_id_incremento_para_cambios"
  ).val();
  var incremento_monto_o_porcentaje = $(
    "#contrato_incrementos_monto_o_porcentaje"
  ).val();
  var incrementos_en = $("#contrato_incrementos_en").val();
  var incrementos_continuidad = $("#contrato_incrementos_continuidad")
    .val()
    .trim();
  var incrementos_a_partir_de_año = $(
    "#contrato_incrementos_a_partir_de_año"
  ).val();

  var incrementos_continuidad_text = "";
  var data = $("#contrato_incrementos_continuidad").select2("data");
  if (data) {
    incrementos_continuidad_text = data[0].text;
  }

  var incrementos_a_partir_de_año_text = "";
  var data = $("#contrato_incrementos_a_partir_de_año").select2("data");
  if (data) {
    incrementos_a_partir_de_año_text = data[0].text;
  }

  if (incremento_monto_o_porcentaje.length < 1) {
    alertify.error("Ingrese el valor", 5);
    $("#contrato_incrementos_monto_o_porcentaje").focus();
    return false;
  }

  if (parseInt(incrementos_en) == 0) {
    alertify.error("Seleccione el tipo de valor", 5);
    $("#contrato_incrementos_en").focus();
    return false;
  }

  if (
    parseInt(incrementos_en) == 2 &&
    incremento_monto_o_porcentaje.length > 5
  ) {
    alertify.error("El incremento no puede ser mayor al 100%", 5);
    $("#contrato_incrementos_en").focus();
    return false;
  }

  if (parseInt(incrementos_continuidad) == 0) {
    alertify.error("Seleccione el tipo de continuidad", 5);
    $("#contrato_incrementos_continuidad").focus();
    return false;
  }

  if (
    parseInt(incrementos_a_partir_de_año) == 0 &&
    parseInt(incrementos_continuidad) != 3
  ) {
    alertify.error("Seleccione el año del inicio del incremento", 5);
    $("#contrato_incrementos_a_partir_de_año").focus();
    return false;
  }

  var data = {
    accion: accion,
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    id_incremento_para_cambios: id_incremento_para_cambios,
    incremento_monto_o_porcentaje: incremento_monto_o_porcentaje,
    incrementos_en: incrementos_en,
    incrementos_continuidad: incrementos_continuidad,
    incrementos_a_partir_de_año: incrementos_a_partir_de_año,
    incrementos_continuidad_text: incrementos_continuidad_text,
    incrementos_a_partir_de_año_text: incrementos_a_partir_de_año_text,
  };

  return data;
}

function sec_contrato_detalle_solicitudv2_obtener_incremento_para_editar(
  incremento_id
) {
  $("#modal_agregar_incrementos").modal("show");

  sec_contrato_nuevo_resetear_formulario_nuevo_incremento("edit");

  var data = {
    accion: "obtener_incrementos",
    incremento_id: incremento_id,
  };

  var array_incrementos = [];

  auditoria_send({ proceso: "obtener_incrementos", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
        return false;
      }

      if (parseInt(respuesta.http_code) == 200) {
        array_incrementos.push(respuesta.result);
        $("#contrato_incrementos_id_incremento_para_cambios").val(
          array_incrementos[0][0].id
        );
        $("#contrato_incrementos_monto_o_porcentaje").val(
          array_incrementos[0][0].valor
        );
        $("#contrato_incrementos_en")
          .val(array_incrementos[0][0].tipo_valor_id)
          .trigger("change");
        $("#contrato_incrementos_continuidad")
          .val(array_incrementos[0][0].tipo_continuidad_id)
          .trigger("change");
        $("#contrato_incrementos_a_partir_de_año")
          .val(array_incrementos[0][0].a_partir_del_año)
          .trigger("change");

        setTimeout(function () {
          $("#contrato_incrementos_en").select2("close");
          $("#contrato_incrementos_continuidad").select2("close");
          $("#contrato_incrementos_a_partir_de_año").select2("close");
          $("#contrato_incrementos_monto_o_porcentaje").focus();
        }, 200);

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_guardar_cambios_incremento() {
  var data = sec_contrato_nuevo_validar_campos_formulario_incremento(
    "guardar_cambios_incremento"
  );
  var contrato_id = $("#contrato_id_temporal").val();

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: "guardar_cambios_incremento", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        respuesta: "guardar_cambios_incremento",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          $("#modal_agregar_incrementos").modal("hide");
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }
        sec_contrato_detalle_solicitudv2_actualizar_provision(contrato_id);
        return false;
      }
    },
    error: function () {},
  });
}
// FIN INCREMENTOS

// INICIO BENEFICIARIOS
function sec_contrato_detalle_solicitudv2_llamar_modal_agregar_beneficiario(
  contrato_detalle_id = ""
) {
  $("#modal_beneficiarios").modal({ backdrop: "static", keyboard: false });
  setTimeout(function () {
    $("#modal_beneficiario_tipo_persona").focus();
    $("#modal_beneficiario_tipo_persona").select2("open");
  }, 500);
  $("#modal_ben_contrato_detalle_id").val(contrato_detalle_id);
}

function sec_contrato_detalle_solicitudv2_validar_campos_formulario_beneficiario(
  accion
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var contrato_detalle_id = $("#modal_ben_contrato_detalle_id").val();
  var tipo_persona = $("#modal_beneficiario_tipo_persona").val();
  var nombre = $("#modal_beneficiario_nombre").val().trim();
  var tipo_docu = $("#modal_beneficiario_tipo_docu").val();
  var num_docu = $("#modal_beneficiario_num_docu").val().trim();
  var id_forma_pago = $("#modal_beneficiario_id_forma_pago").val().trim();
  var id_banco = $("#modal_beneficiario_id_banco").val();
  var num_cuenta_bancaria = $("#modal_beneficiario_num_cuenta_bancaria").val();
  var num_cuenta_cci = $("#modal_beneficiario_num_cuenta_cci").val();
  var tipo_monto = $("#modal_beneficiario_tipo_monto").val();
  var monto = $("#modal_beneficiario_monto").val();

  if (parseInt(tipo_persona) == 0) {
    alertify.error("Seleccione el tipo de persona", 5);
    $("#modal_beneficiario_tipo_persona").focus();
    return false;
  }

  if (contrato_detalle_id.length == 0) {
    alertify.error("Se requiere un codigo de contrato", 5);
    return false;
  }

  if (nombre.length < 6) {
    alertify.error("Ingrese el nombre completo del beneficiario", 5);
    $("#modal_beneficiario_nombre").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 0) {
    alertify.error("Seleccione el tipo de documento de identidad", 5);
    $("#modal_beneficiario_tipo_docu").focus();
    return false;
  }

  if (num_docu.length == 0) {
    alertify.error("Ingrese el Número de Documento de Identidad", 5);
    $("#modal_beneficiario_num_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 1 && num_docu.length != 8) {
    alertify.error(
      "El número de DNI posee 8 dígitos, no " + num_docu.length + " dígitos",
      5
    );
    $("#modal_beneficiario_num_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 2 && num_docu.length != 11) {
    alertify.error(
      "El número de RUC posee 11 dígitos, no " + num_docu.length + " dígitos",
      5
    );
    $("#modal_beneficiario_num_docu").focus();
    return false;
  }

  if (parseInt(id_forma_pago) == 0) {
    alertify.error("Seleccione el tipo de forma de pago", 5);
    $("#modal_beneficiario_id_forma_pago").focus();
    return false;
  }

  if (parseInt(id_banco) == 0 && parseInt(id_forma_pago) != 3) {
    alertify.error("Seleccione el banco", 5);
    $("#modal_beneficiario_id_banco").focus();
    return false;
  }

  if (num_cuenta_bancaria.length == 0 && parseInt(id_forma_pago) != 3) {
    alertify.error("Ingrese el número de cuenta bancaria", 5);
    $("#modal_beneficiario_num_cuenta_bancaria").focus();
    return false;
  }

  if (num_cuenta_bancaria.length < 5 && parseInt(id_forma_pago) != 3) {
    alertify.error(
      "El número de cuenta bancaria debe ser mayor a 5 dígitos",
      5
    );
    $("#modal_beneficiario_num_cuenta_bancaria").focus();
    return false;
  }

  if (num_cuenta_cci.length < 8 && parseInt(id_forma_pago) != 3) {
    alertify.error(
      "El código de cuenta Interbancaria debe ser mayor a 8 dígitos",
      5
    );
    $("#modal_beneficiario_num_cuenta_cci").focus();
    return false;
  }

  if (parseInt(tipo_monto) == 0) {
    alertify.error("Seleccione el tipo de monto a pagar", 5);
    $("#modal_beneficiario_tipo_monto").focus();
    return false;
  }

  if (parseInt(tipo_monto) != 3 && monto.length < 1) {
    alertify.error("Ingrese el monto a pagar", 5);
    $("#modal_beneficiario_monto").focus();
    return false;
  }

  var data = {
    accion: accion,
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    tipo_persona: tipo_persona,
    nombre: nombre,
    tipo_docu: tipo_docu,
    num_docu: num_docu,
    id_forma_pago: id_forma_pago,
    id_banco: id_banco,
    num_cuenta_bancaria: num_cuenta_bancaria,
    num_cuenta_cci: num_cuenta_cci,
    tipo_monto: tipo_monto,
    monto: monto,
  };

  return data;
}

function sec_contrato_detalle_solicitudv2_guardar_beneficiario(metodo) {
  var data =
    sec_contrato_detalle_solicitudv2_validar_campos_formulario_beneficiario(
      metodo
    );

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: metodo, data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: metodo, data: respuesta });

      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          $("#modal_beneficiarios").modal("hide");
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }

        return false;
      }
    },
    error: function () {},
  });
}
// FIN BENEFICIARIOS

// INICIO BENEFICIARIOS
function sec_contrato_detalle_solicitudv2_llamar_modal_agregar_responsable_ir(
  contrato_detalle_id = ""
) {
  $("#modal_responsables_ir").modal({ backdrop: "static", keyboard: false });
  setTimeout(function () {
    $("#modal_res_num_docu").focus();
  }, 500);
  $("#modal_res_contrato_detalle_id").val(contrato_detalle_id);
}

function sec_contrato_detalle_solicitudv2_validar_campos_formulario_responsable_ir(
  accion
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var contrato_detalle_id = $("#modal_res_contrato_detalle_id").val();
  var tipo_docu = $("#modal_res_tipo_docu").val();
  var num_docu = $("#modal_res_num_docu").val().trim();
  var nombres = $("#modal_res_nombres").val().trim();
  var porcentaje = $("#modal_res_porcentaje").val();

  if (parseInt(tipo_docu) == 0) {
    alertify.error("Seleccione el tipo de persona", 5);
    $("#modal_res_tipo_docu").focus();
    return false;
  }

  if (num_docu.length != 11) {
    alertify.error("Ingrese un numero de documento", 5);
    $("#modal_res_num_docu").focus();
    return false;
  }

  if (nombres.length < 6) {
    alertify.error("Ingrese un nombre", 5);
    $("#modal_res_nombres").focus();
    return false;
  }

  if (porcentaje.length == 0) {
    alertify.error("Ingrese el porcentaje", 5);
    $("#modal_res_porcentaje").focus();
    return false;
  }

  var data = {
    accion: accion,
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    tipo_docu: tipo_docu,
    num_docu: num_docu,
    nombres: nombres,
    porcentaje: porcentaje,
  };

  return data;
}

function sec_contrato_detalle_solicitudv2_guardar_responsable_ir(metodo) {
  var data =
    sec_contrato_detalle_solicitudv2_validar_campos_formulario_responsable_ir(
      metodo
    );

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: metodo, data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: metodo, data: respuesta });
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          $("#modal_responsables_ir").modal("hide");
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }

        return false;
      }
    },
    error: function () {},
  });
}
// FIN BENEFICIARIOS

// INICIO PROPIETARIOS

function sec_contrato_detalle_solicitudv2_llamar_modal_agregar_propietario() {
  $("#modal_propietario").modal({ backdrop: "static", keyboard: false });
  setTimeout(function () {
    $("#modalBuscarPropietario_ca").modal("hide");
    $("#modal_propietario_tipo_persona").focus();
    $("#modal_propietario_tipo_persona").select2("open");
  }, 500);
}

function sec_contrato_detalle_agente_buscar_propietario() {
  //var array_propietarios = [];
  var nombre_o_numdocu = $.trim(
    $("#modal_propietario_nombre_o_numdocu_ca").val()
  );
  var tipo_busqueda = parseInt(
    $.trim($("#modal_propietario_tipo_busqueda_ca").val())
  );
  var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud_ca").val();

  if (nombre_o_numdocu.length < 3) {
    var busqueda_por = "";
    if (tipo_busqueda == 1) {
      busqueda_por = "Nombre del Propietario";
    } else if (tipo_busqueda == 2) {
      busqueda_por = "Número de Documento de Identidad";
    }
    alertify.error(
      "El " + busqueda_por + " debe de tener más de dos dígitos",
      5
    );
    $("#modal_propietario_nombre_o_numdocu_ca").focus();
    return;
  }

  var data = {
    accion: "obtener_propietario_detalle_agente",
    nombre_o_numdocu: nombre_o_numdocu,
    tipo_busqueda: tipo_busqueda,
    tipo_solicitud: tipo_solicitud,
  };

  auditoria_send({ proceso: "obtener_propietario_detalle_agente", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
        $("#tlbPropietariosxBusqueda_ca").html("");
        $("#divNoSeEncontroPropietario_ca").show();
        $("#divRegistrarNuevoPropietario_ca").show();
        var msg = "";
        if (tipo_busqueda == "1") {
          msg = "nombre";
        } else {
          msg = "número de documento";
        }
        $("#valoresDeBusqueda_ca").text(msg + " " + nombre_o_numdocu);
        return false;
      }

      if (parseInt(respuesta.http_code) == 200) {
        $("#tlbPropietariosxBusqueda_ca").html(respuesta.result);
        $("#divNoSeEncontroPropietario_ca").hide();
        $("#divRegistrarNuevoPropietario_ca").show();

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_agente_asignar_propietario_al_contrato(
  idpersona,
  idcontrato
) {
  var data = {
    accion: "registrar_cambio_propietario_agente",
    idpersona: idpersona,
    idcontrato: idcontrato,
  };

  auditoria_send({
    proceso: "registrar_cambio_propietario_agente",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
        return false;
      }

      if (parseInt(respuesta.http_code) == 200) {
      }
    },
    error: function () {},
  });
  $("#modalBuscarPropietario_ca").modal("hide");
  window.location.reload();
}

function sec_contrato_detalle_solicitudv2_llamar_modal_buscar_propietario() {
  $("#modalBuscarPropietario_ca").modal({
    backdrop: "static",
    keyboard: false,
  });
  setTimeout(function () {
    $("#modal_propietario_nombre_o_numdocu_ca").focus();
    //	$('#modal_propietario_tipo_persona').select2('open');
  }, 500);
}

function sec_contrato_detalle_agente_ruc_registrado(ruc) {
  var nro_ruc = ruc;
  var data = {
    accion: "obtener_propietario_ruc_existe_detalle_ag",
    nombre_o_numdocu: nro_ruc,
  };

  auditoria_send({
    proceso: "obtener_propietario_ruc_existe_detalle_ag",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: respuesta, data: respuesta });

      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);

        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        alertify.error("El número de RUC " + nro_ruc + " ya existe,", 5);
        $("#modal_propietario_num_ruc").focus();

        return true;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_agente_dni_registrado(dni) {
  var nro_dni = dni;
  var data = {
    accion: "obtener_propietario_existe_detalle_ag",
    nombre_o_numdocu: nro_dni,
  };

  auditoria_send({
    proceso: "obtener_propietario_existe_detalle_ag",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: respuesta, data: respuesta });

      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);

        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        alertify.error("El número de DNI " + nro_dni + " ya existe,", 5);
        $("#modal_propietario_num_docu").focus();

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_agente_solicitud_validar_campos_formulario_propietario(
  accion
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var tipo_persona = $("#modal_propietario_tipo_persona").val();
  var nombre = $("#modal_propietario_nombre").val().trim();
  var tipo_docu = $("#modal_propietario_tipo_docu").val();
  var num_docu = $("#modal_propietario_num_docu").val().trim();
  var num_ruc = $("#modal_propietario_num_ruc").val().trim();
  var direccion = $("#modal_propietario_direccion").val().trim();
  var representante_legal = $("#modal_propietario_representante_legal")
    .val()
    .trim();
  var num_partida_registral = $(
    "#modal_propietario_num_partida_registral"
  ).val();
  var tipo_persona_contacto = $(
    "#modal_propietario_tipo_persona_contacto"
  ).val();
  var contacto_nombre = $("#modal_propietario_contacto_nombre").val().trim();
  var contacto_telefono = $("#modal_propietario_contacto_telefono").val();
  var contacto_email = $("#modal_propietario_contacto_email").val().trim();

  if (parseInt(tipo_persona) == 0) {
    alertify.error("Seleccione el tipo de persona", 5);
    $("#modal_propietario_tipo_persona").focus();
    $("#modal_propietario_tipo_persona").select2("open");
    return false;
  }

  if (nombre.length < 6) {
    alertify.error("Ingrese el nombre completo del propietario", 5);
    $("#modal_propietario_nombre").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 0) {
    alertify.error("Seleccione el tipo de documento de identidad", 5);
    $("#modal_propietario_tipo_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 1 && num_docu.length != 8) {
    alertify.error(
      "El número de DNI debe tener 8 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 3 && num_docu.length != 12) {
    alertify.error(
      "El número de Pasaporte debe tener 12 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 4 && num_docu.length != 12) {
    alertify.error(
      "El número de Carnet de Ext debe tener 12 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu").focus();
    return false;
  }

  // if (num_docu.length == 8)
  // {
  //   sec_contrato_detalle_agente_dni_registrado(num_docu);

  // }

  if (num_ruc.length != 11) {
    alertify.error(
      "El número de RUC debe tener 11 dígitos, no " +
        num_ruc.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_ruc").focus();
    return false;
  }

  // if (num_ruc.length == 11) {
  // 	sec_contrato_detalle_agente_ruc_registrado(num_ruc);
  // }

  if (direccion.length < 10) {
    alertify.error("Ingrese el dirección completa del propietario", 5);
    $("#modal_propietario_direccion").focus();
    return false;
  }

  if (parseInt(tipo_persona) == 2 && representante_legal.length == 0) {
    alertify.error("Ingrese el representante legal", 5);
    $("#modal_propietario_representante_legal").focus();
    return false;
  }

  if (parseInt(tipo_persona) == 2 && num_partida_registral.length == 0) {
    alertify.error(
      "Ingrese el número de la Partida Registral de la empresa",
      5
    );
    $("#modal_propietario_num_partida_registral").focus();
    return false;
  }

  if (accion == "guardar_propietario") {
    if (parseInt(tipo_persona_contacto) == 0) {
      alertify.error("Seleccione el tipo de persona contacto", 5);
      $("#modal_propietario_tipo_persona_contacto").focus();
      return false;
    }

    if (parseInt(tipo_persona_contacto) == 2 && contacto_nombre.length < 1) {
      alertify.error("Ingrese el nombre del contacto", 5);
      $("#modal_propietario_contacto_nombre").focus();
      return false;
    }
  }

  if (contacto_telefono.length < 9) {
    alertify.error(
      "El número telefónico del contacto debe de ser de 9 dígitos, no de " +
        contacto_telefono.length +
        " dígitos",
      5
    );
    $("#modal_propietario_contacto_telefono").focus();
    return false;
  }

  if (
    contacto_email.length > 0 &&
    !sec_contrato_detalle_solicitudv2_es_email_valido(contacto_email)
  ) {
    alertify.error("El formato del correo electrónico es incorrecto", 5);
    $("#modal_propietario_contacto_email").focus();
    return false;
  }

  var data = {
    accion: accion,
    contrato_id: contrato_id,
    tipo_persona: tipo_persona,
    nombre: nombre,
    tipo_docu: tipo_docu,
    num_docu: num_docu,
    num_ruc: num_ruc,
    direccion: direccion,
    representante_legal: representante_legal,
    num_partida_registral: num_partida_registral,
    tipo_persona_contacto: tipo_persona_contacto,
    contacto_nombre: contacto_nombre,
    contacto_telefono: contacto_telefono,
    contacto_email: contacto_email,
  };

  return data;
}

function sec_contrato_detalle_agente_resetear_formulario_nuevo_propietario_agente(
  evento
) {
  $("#frm_nuevo_propietario")[0].reset();
  $("#div_modal_propietario_representante_legal").hide();
  $("#div_modal_propietario_num_partida_registral").hide();
}

function sec_contrato_detalle_solicitudv2_validar_campos_formulario_propietario(
  accion
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var tipo_persona = $("#modal_propietario_tipo_persona").val();
  var nombre = $("#modal_propietario_nombre").val().trim();
  var tipo_docu = $("#modal_propietario_tipo_docu").val();
  var num_docu = $("#modal_propietario_num_docu").val().trim();
  var num_ruc = $("#modal_propietario_num_ruc").val().trim();
  var direccion = $("#modal_propietario_direccion").val().trim();
  var representante_legal = $("#modal_propietario_representante_legal")
    .val()
    .trim();
  var num_partida_registral = $(
    "#modal_propietario_num_partida_registral"
  ).val();
  var tipo_persona_contacto = $(
    "#modal_propietario_tipo_persona_contacto"
  ).val();
  var contacto_nombre = $("#modal_propietario_contacto_nombre").val().trim();
  var contacto_telefono = $("#modal_propietario_contacto_telefono").val();
  var contacto_email = $("#modal_propietario_contacto_email").val().trim();

  if (parseInt(tipo_persona) == 0) {
    alertify.error("Seleccione el tipo de persona", 5);
    $("#modal_propietario_tipo_persona").focus();
    $("#modal_propietario_tipo_persona").select2("open");
    return false;
  }

  if (nombre.length < 6) {
    alertify.error("Ingrese el nombre completo del propietario", 5);
    $("#modal_propietario_nombre").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 0) {
    alertify.error("Seleccione el tipo de documento de identidad", 5);
    $("#modal_propietario_tipo_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 1 && num_docu.length != 8) {
    alertify.error(
      "El número de DNI debe tener 8 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 3 && num_docu.length != 12) {
    alertify.error(
      "El número de Pasaporte debe tener 12 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 4 && num_docu.length != 12) {
    alertify.error(
      "El número de Carnet de Ext. debe tener 12 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu").focus();
    return false;
  }

  if (num_ruc.length != 11) {
    alertify.error(
      "El número de RUC debe tener 11 dígitos, no " +
        num_ruc.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_ruc").focus();
    return false;
  }

  if (direccion.length < 10) {
    alertify.error("Ingrese el dirección completa del propietario", 5);
    $("#modal_propietario_direccion").focus();
    return false;
  }

  if (parseInt(tipo_persona) == 2 && representante_legal.length == 0) {
    alertify.error("Ingrese el representante legal", 5);
    $("#modal_propietario_representante_legal").focus();
    return false;
  }

  if (parseInt(tipo_persona) == 2 && num_partida_registral.length == 0) {
    alertify.error(
      "Ingrese el número de la Partida Registral de la empresa",
      5
    );
    $("#modal_propietario_num_partida_registral").focus();
    return false;
  }

  if (accion == "guardar_propietario") {
    if (parseInt(tipo_persona_contacto) == 0) {
      alertify.error("Seleccione el tipo de persona contacto", 5);
      $("#modal_propietario_tipo_persona_contacto").focus();
      return false;
    }

    if (parseInt(tipo_persona_contacto) == 2 && contacto_nombre.length < 1) {
      alertify.error("Ingrese el nombre del contacto", 5);
      $("#modal_propietario_contacto_nombre").focus();
      return false;
    }
  }

  if (contacto_telefono.length < 9) {
    alertify.error(
      "El número telefónico del contacto debe de ser de 9 dígitos, no de " +
        contacto_telefono.length +
        " dígitos",
      5
    );
    $("#modal_propietario_contacto_telefono").focus();
    return false;
  }

  if (
    contacto_email.length > 0 &&
    !sec_contrato_detalle_solicitudv2_es_email_valido(contacto_email)
  ) {
    alertify.error("El formato del correo electrónico es incorrecto", 5);
    $("#modal_propietario_contacto_email").focus();
    return false;
  }

  var data = {
    accion: accion,
    contrato_id: contrato_id,
    tipo_persona: tipo_persona,
    nombre: nombre,
    tipo_docu: tipo_docu,
    num_docu: num_docu,
    num_ruc: num_ruc,
    direccion: direccion,
    representante_legal: representante_legal,
    num_partida_registral: num_partida_registral,
    tipo_persona_contacto: tipo_persona_contacto,
    contacto_nombre: contacto_nombre,
    contacto_telefono: contacto_telefono,
    contacto_email: contacto_email,
  };

  return data;
}

function sec_contrato_detalle_agente_solicitud_guardar_propietario(proceso) {
  var data =
    sec_contrato_detalle_agente_solicitud_validar_campos_formulario_propietario(
      proceso
    );

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: proceso, data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: proceso, data: respuesta });
      if (parseInt(respuesta.http_code) == 400) {
        // $('#modal_recargaweb').modal('hide');
        alertify.error(respuesta.status, 5);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          $("#modal_propietario").modal("hide");
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_guardar_propietario(proceso) {
  var data =
    sec_contrato_detalle_solicitudv2_validar_campos_formulario_propietario(
      proceso
    );

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: proceso, data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: proceso, data: respuesta });
      if (parseInt(respuesta.http_code) == 400) {
        // $('#modal_recargaweb').modal('hide');
        alertify.error(respuesta.status, 5);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          $("#modal_propietario").modal("hide");
          location.reload(true);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_guardar_estado_solicitud() {
  var contrato_id = $("#contrato_id_temporal").val();
  var estado_solicitud = $("#estado_solicitud").val().trim();
  var motivo_estado_na = $("#motivo_estado_na").val().trim();
  if (parseInt(estado_solicitud) == 0 || estado_solicitud == "") {
    alertify.error("Seleccione una estado de solicitud", 5);
    $("#estado_solicitud").focus();
    return false;
  }

  if (parseInt(estado_solicitud) == 4 && motivo_estado_na.length == 0) {
    alertify.error("Ingrese un motivo", 5);
    $("#motivo_estado_na").focus();
    return false;
  }
  if (motivo_estado_na.length >= 1000) {
    alertify.error("Tamaño maximo de caracteres permitidos (1000)", 5);
    $("#estado_solicitud").focus();
    return false;
  }

  var data = {
    accion: "guardar_estado_solicitud",
    contrato_id: contrato_id,
    estado_solicitud: estado_solicitud,
    motivo_estado_na: motivo_estado_na,
  };

  auditoria_send({
    proceso: "guardar_estado_solicitud_de_contrato",
    data: data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        proceso: "guardar_estado_solicitud_de_contrato",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.message, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_guardar_estado_solicitud_adenda(
  adenda_id
) {
  var estado_solicitud = $("#adenda_estado_solicitud_" + adenda_id).val();

  if (estado_solicitud.length == 0) {
    alertify.error("Seleccione una estado de solicitud", 5);
    $("#estado_solicitud").focus();
    return false;
  }

  var data = {
    accion: "guardar_estado_solicitud_adenda",
    adenda_id: adenda_id,
    estado_solicitud: estado_solicitud,
  };

  auditoria_send({ proceso: "guardar_estado_solicitud_adenda", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        proceso: "guardar_estado_solicitud_adenda",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.error, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (respuesta.error == "") {
          swal({
            title: "Se actualizo el estado",
            text: "",
            html: true,
            type: "success",
            timer: 4000,
            closeOnConfirm: false,
            showCancelButton: false,
          });
          setTimeout(() => {
            location.reload(true);
          }, 2000);
        } else {
          swal("Aviso", respuesta.error, "warning");
        }

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_corregir_dias_habiles() {
  var contrato_id = $("#contrato_id_temporal").val();
  var data = {
    accion: "corregir_dias_habiles",
    contrato_id: contrato_id,
  };
  auditoria_send({
    proceso: "corregir_dias_habiles",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        proceso: "corregir_dias_habiles",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.message, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        swal("Aviso", "Se guardo correctamente", "success");
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_es_email_valido(email) {
  var regex =
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return regex.test(email);
}
// FIN PROPIETARIOS

function sec_contrato_Detalle_solicitud_collapse_contrato(tipo) {
  $(".panel-collapse-all").collapse(tipo);
}

function sec_contrato_detalle_solicitudv2_editar_solicitud_param(
  nombre_menu_usuario,
  nombre_tabla,
  nombre_campo,
  nombre_campo_usuario,
  tipo_valor,
  valor_actual,
  metodo_select,
  id_tabla,
  valor_select
) {
  $("#form_editar_solicitud")[0].reset();
  $("#modal_editar_solicitud").modal({ backdrop: "static", keyboard: false });
  $("#editar_solicitud_nombre_menu_usuario").html(nombre_menu_usuario);
  $("#editar_solicitud_nombre_campo_usuario").html(nombre_campo_usuario);
  $("#editar_solicitud_valor_actual").html(valor_actual);

  $("#editar_solicitud_nombre_tabla").val(nombre_tabla);
  $("#editar_solicitud_nombre_campo").val(nombre_campo);
  $("#editar_solicitud_tipo_valor").val(tipo_valor);
  $("#editar_solicitud_id_tabla").val(id_tabla);

  $("#div_editar_solicitud_departamento").hide();
  $("#div_editar_solicitud_provincias").hide();
  $("#div_editar_solicitud_distrito").hide();

  if (tipo_valor == "select_option") {
    $("#div_editar_solicitud_valor_varchar").hide();
    $("#div_editar_solicitud_valor_textarea").hide();
    $("#div_editar_solicitud_valor_int").hide();
    $("#div_editar_solicitud_valor_date").hide();
    $("#div_editar_solicitud_valor_decimal").hide();

    $("#div_editar_solicitud_departamento").hide();
    $("#div_editar_solicitud_provincias").hide();
    $("#div_editar_solicitud_distrito").hide();
    $("#div_editar_solicitud_valor_select_option").show();
    sec_contrato_detalle_solicitudv2_obtener_opciones_param(
      metodo_select,
      $("[name='editar_solicitud_valor_select_option']"),
      valor_select
    );
    setTimeout(function () {
      $("#editar_solicitud_valor_select_option").select2("open");
      $("#editar_solicitud_valor_select_option").focus();
    }, 400);
  }
}

function sec_contrato_detalle_solicitudv2_obtener_opciones_param(
  accion,
  select,
  valor_select
) {
  $.ajax({
    url: "/sys/set_contrato_nuevo.php",
    type: "POST",
    data: { accion: accion, valor_select: valor_select }, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      //  alert(datat)
      var respuesta = JSON.parse(datos);
      $(select).find("option").remove().end();
      $(select).append('<option value="0">- Seleccione -</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $(select).append(opcion);
      });
    },
    error: function () {},
  });
}

// INICIO MODAL CATEGORIA CONTRATO
function sec_contrato_detalle_solicitudv2_modal_categoria_contrato() {
  $("#modal_categoria_contrato").modal({ backdrop: "static", keyboard: false });
  $("#md_id_categoria_servicio").val("");
  $("#md_categoria_servicio_nombre").val("");

  $("#md_id_tipo_categoria_servicio").val("");
  $("#md_categoria_servicio_id").val("0").trigger("change");
  $("#md_tipo_categoria_servicio_nombre").val("");

  sec_contrato_detalle_solicitudv2_modal_listar_categoria_servicio();
  sec_contrato_detalle_solicitudv2_modal_listar_tipo_categoria_servicio();
}

function sec_contrato_detalle_solicitudv2_guardar_modal_categoria_servicio() {
  var id_categoria_servicio = $("#md_id_categoria_servicio").val();
  var nombre = $("#md_categoria_servicio_nombre").val();
  if (nombre.length == 0) {
    alertify.error("Ingrese un nombre", 5);
    $("#md_categoria_servicio_nombre").focus();
    return false;
  }
  var data = {
    accion: "guardar_categoria_servicio",
    id_categoria_servicio: id_categoria_servicio,
    nombre: nombre,
  };

  auditoria_send({ proceso: "guardar_categoria_servicio", data: data });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        proceso: "guardar_categoria_servicio",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.message, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#md_id_categoria_servicio").val("");
        $("#md_categoria_servicio_nombre").val("");
        sec_contrato_detalle_solicitudv2_modal_listar_categoria_servicio();
        sec_contrato_detalle_solicitudv2_modal_obtener_select_categoria_servicio();
        swal("Aviso", respuesta.message, "success");
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_listar_categoria_servicio() {
  var data = {
    accion: "listar_categoria_servicio",
  };

  auditoria_send({ proceso: "listar_categoria_servicio", data: data });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        respuesta: "listar_categoria_servicio",
        data: respuesta,
      });
      $("#div_listar_servicio_categoria").html(respuesta.result);
      $(".data-table").DataTable();
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_cambiar_estado_categoria_servicio(
  id_categoria_servicio,
  estado
) {
  var data = {
    accion: "modificar_estado_categoria_servicio",
    id_categoria_servicio: id_categoria_servicio,
    estado: estado,
  };

  auditoria_send({
    proceso: "modificar_estado_categoria_servicio",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        respuesta: "modificar_estado_categoria_servicio",
        data: respuesta,
      });
      sec_contrato_detalle_solicitudv2_modal_listar_categoria_servicio();
      sec_contrato_detalle_solicitudv2_modal_obtener_select_categoria_servicio();
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_obtener_datos_categoria_servicio(
  id_categoria_servicio
) {
  var data = {
    accion: "obtener_categoria_servicio",
    id_categoria_servicio: id_categoria_servicio,
  };

  auditoria_send({ proceso: "obtener_categoria_servicio", data: data });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        respuesta: "obtener_categoria_servicio",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.message, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#md_id_categoria_servicio").val(respuesta.result.id);
        $("#md_categoria_servicio_nombre").val(respuesta.result.nombre);
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_guardar_modal_tipo_categoria_servicio() {
  var id_tipo_categoria_servicio = $("#md_id_tipo_categoria_servicio").val();
  var categoria_servicio_id = $("#md_categoria_servicio_id").val();
  var nombre = $("#md_tipo_categoria_servicio_nombre").val();
  if (categoria_servicio_id == "0") {
    alertify.error("Seleccione una categoria", 5);
    $("#md_categoria_servicio_id").focus();
    return false;
  }
  if (nombre.length == 0) {
    alertify.error("Ingrese un nombre", 5);
    $("#md_tipo_categoria_servicio_nombre").focus();
    return false;
  }
  var data = {
    accion: "guardar_tipo_categoria_servicio",
    id_tipo_categoria_servicio: id_tipo_categoria_servicio,
    categoria_servicio_id: categoria_servicio_id,
    nombre: nombre,
  };

  auditoria_send({ proceso: "guardar_tipo_categoria_servicio", data: data });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        proceso: "guardar_tipo_categoria_servicio",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.message, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#md_id_tipo_categoria_servicio").val("");
        $("#md_categoria_servicio_id").val("0").trigger("change");
        $("#md_tipo_categoria_servicio_nombre").val("");
        sec_contrato_detalle_solicitudv2_modal_listar_tipo_categoria_servicio();
        swal("Aviso", respuesta.message, "success");
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_listar_tipo_categoria_servicio() {
  var data = {
    accion: "listar_tipo_categoria_servicio",
  };

  auditoria_send({ proceso: "listar_tipo_categoria_servicio", data: data });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        respuesta: "listar_tipo_categoria_servicio",
        data: respuesta,
      });
      $("#div_listar_tipo_servicio_categoria").html(respuesta.result);
      $(".data-table").DataTable();
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_cambiar_estado_tipo_categoria_servicio(
  id_tipo_categoria_servicio,
  estado
) {
  var data = {
    accion: "modificar_estado_tipo_categoria_servicio",
    id_tipo_categoria_servicio: id_tipo_categoria_servicio,
    estado: estado,
  };

  auditoria_send({
    proceso: "modificar_estado_tipo_categoria_servicio",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        respuesta: "modificar_estado_tipo_categoria_servicio",
        data: respuesta,
      });
      sec_contrato_detalle_solicitudv2_modal_listar_tipo_categoria_servicio();
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_obtener_datos_tipo_categoria_servicio(
  id_tipo_categoria_servicio
) {
  var data = {
    accion: "obtener_tipo_categoria_servicio",
    id_tipo_categoria_servicio: id_tipo_categoria_servicio,
  };

  auditoria_send({ proceso: "obtener_tipo_categoria_servicio", data: data });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        respuesta: "obtener_tipo_categoria_servicio",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.message, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#md_id_tipo_categoria_servicio").val(respuesta.result.id);
        $("#md_categoria_servicio_id")
          .val(respuesta.result.categoria_servicio_id)
          .trigger("change");
        $("#md_tipo_categoria_servicio_nombre").val(respuesta.result.nombre);
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_obtener_select_categoria_servicio() {
  var data = {
    accion: "obtener_listar_categoria_servicio",
  };
  auditoria_send({ proceso: "obtener_listar_categoria_servicio", data: data });
  $.ajax({
    url: "/sys/set_contrato_servicio.php",
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
      auditoria_send({
        respuesta: "obtener_listar_categoria_servicio",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 200) {
        $("#cont_detalle_proveedor_contrato_firmado_categoria_param").html(
          respuesta.result
        );
        $("#cont_detalle_proveedor_contrato_firmado_categoria_param")
          .val("0")
          .trigger("change");

        $("#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param").html(
          '<option value="0">- Seleccione -</option>'
        );
        $("#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param")
          .val("0")
          .trigger("change");
        return false;
      }
    },
    error: function () {},
  });
}
// FIN MODAL CATEGORIA CONTRATO

function sec_contrato_detalle_solicitudv2_modal_agregar_autorizacion_municipal(
  autorizacion_municipal_id,
  titulo
) {
  $("#modal_autorizacion_municipal").modal("show");

  $("#form_autorizacion_municipal")[0].reset();
  $("#estado_id_am").val("").trigger("change");
  $("#condicion_id").val("").trigger("change");
  $("#giro_am").val("").trigger("change");
  $("#modal_autorizacion_municipal_titulo").html(titulo);

  $("#autorizacion_municipal_id").val(autorizacion_municipal_id);

  $("#div_estado_id_am").hide();
  $("#div_condicion_id_am").hide();
  $("#div_fecha_vencimiento_am").hide();
  $("#div_fecha_renovacion_am").hide();
  $("#div_giro_am").hide();
  $("#div_archivo_autorizacion_municipal").hide();

  if (autorizacion_municipal_id == 7) {
    $("#div_giro_am").show();
    $("#div_archivo_autorizacion_municipal").show();
    setTimeout(function () {
      $("#giro_am").focus();
      $("#giro_am").select2("open");
    }, 500);
  } else {
    $("#div_estado_id_am").show();
    setTimeout(function () {
      $("#estado_id_am").focus();
      $("#estado_id_am").select2("open");
    }, 500);
  }
}

function sec_contrato_detalle_solicitudv2_agregar_autorizacion_municipal() {
  var contrato_id = $("#contrato_id_temporal").val();
  var autorizacion_municipal_id = $("#autorizacion_municipal_id").val();
  var estado_id = $("#estado_id_am").val();
  var condicion_id = $("#condicion_id_am").val();
  var fecha_vencimiento = $("#fecha_vencimiento_am").val();
  var fecha_renovacion = $("#fecha_renovacion_am").val();
  var giro_id = $("#giro_am").val();
  var archivo_autorizacion_municipal = document.getElementById(
    "archivo_autorizacion_municipal_am"
  );

  if (autorizacion_municipal_id == "7") {
    if (giro_id == "") {
      alertify.error("Seleccione el giro", 5);
      $("#giro_am").focus();
      $("#giro_am").select2("open");
      return false;
    } else if (archivo_autorizacion_municipal.files.length == 0) {
      alertify.error("Seleccione el archivo", 5);
      $("#archivo_autorizacion_municipal_am").focus();
      return false;
    }
  } else {
    if (estado_id == "") {
      alertify.error("Seleccione estado de la licencia", 5);
      $("#estado_id_am").focus();
      $("#estado_id_am").select2("open");
      return false;
    } else if (estado_id == "CONCLUIDO") {
      if (condicion_id == "") {
        alertify.error("Seleccione la condición", 5);
        $("#condicion_id_am").focus();
        $("#condicion_id_am").select2("open");
        return false;
      }
      if (archivo_autorizacion_municipal.files.length == 0) {
        alertify.error("Seleccione el archivo", 5);
        $("#archivo_autorizacion_municipal_am").focus();
        return false;
      }
    }
  }

  var form_data = new FormData($("#form_autorizacion_municipal")[0]);
  form_data.append("accion", "guardar_autorizacion_municipal");
  form_data.append("contrato_id", contrato_id);
  form_data.append("autorizacion_municipal_id", autorizacion_municipal_id);
  form_data.append("estado_id", estado_id);
  form_data.append("condicion_id", condicion_id);
  form_data.append("fecha_vencimiento", fecha_vencimiento);
  form_data.append("fecha_renovacion", fecha_renovacion);
  form_data.append("giro_id", giro_id);

  auditoria_send({
    proceso: "guardar_autorizacion_municipal",
    data: form_data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (data) {
      var respuesta = JSON.parse(data);

      auditoria_send({
        proceso: "guardar_autorizacion_municipal",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        swal({
          title: "Registro exitoso",
          text: "La autorización municipal fue registrada exitosamente",
          html: true,
          type: "success",
          timer: 6000,
          closeOnConfirm: false,
          showCancelButton: false,
        });

        $("#modal_autorizacion_municipal").modal("hide");

        sec_contrato_detalle_solicitudv2_actualizar_tabla_autorizaciones_municipales(
          autorizacion_municipal_id
        );
      } else {
        swal({
          title: "Error al registrar la autorización municipal",
          text: respuesta.error,
          html: true,
          type: "warning",
          closeOnConfirm: false,
          showCancelButton: false,
        });
      }
    },
    complete: function () {
      loading(false);
    },
  });
}

function sec_contrato_detalle_solicitudv2_actualizar_tabla_autorizaciones_municipales(
  autorizacion_municipal_id
) {
  var contrato_id = $("#contrato_id_temporal").val();

  var data = {
    accion: "actualizar_tabla_autorizaciones_municipales",
    contrato_id: contrato_id,
    autorizacion_municipal_id: autorizacion_municipal_id,
  };

  auditoria_send({
    proceso: "actualizar_tabla_autorizaciones_municipales",
    data: data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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

      auditoria_send({
        proceso: "actualizar_tabla_autorizaciones_municipales",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 400) {
        alertify.error(
          "No se pudo actualizar la sección de Autorizaciones Municipales",
          5
        );
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (autorizacion_municipal_id == 4) {
          $("#body_funcionamiento").html(respuesta.body);
        } else if (autorizacion_municipal_id == 5) {
          $("#body_indeci").html(respuesta.body);
        } else if (autorizacion_municipal_id == 6) {
          $("#body_publicidad").html(respuesta.body);
        } else if (autorizacion_municipal_id == 7) {
          $("#body_dj").html(respuesta.body);
        }

        return false;
      }
    },
    error: function () {},
  });
}

///INICIO RESOLICION DE CONTRATO FIRMADO
function sec_contrato_detalle_guardar_resolucion_contrato() {
  var contrato_id = $("#contrato_id_temporal").val();
  var resolucion_contrato_id = $("#resolucion_contrato_id").val();
  var resolucion_tipo_contrato_id = $("#resolucion_tipo_contrato_id").val();
  var fecha_resolucion = $("#cont_detalle_resolucion_contrato_fecha")
    .val()
    .trim();
  var archivo_resolucion_contrato = document.getElementById(
    "archivo_resolucion_contrato"
  );

  if (fecha_resolucion.length == 0) {
    alertify.error("Seleccione una fecha de resolución", 5);
    $("#cont_detalle_resolucion_contrato_fecha").focus();
    return false;
  }
  if (archivo_resolucion_contrato.files.length == 0) {
    alertify.error("Ingrese la resolución de contrato firmado", 5);
    $("#archivo_resolucion_contrato").focus();
    return false;
  }

  var dataForm = new FormData($("#form_resolucion_contrato_firmado")[0]);

  dataForm.append("accion", "guardar_resolucion_contrato_firmado");
  dataForm.append("contrato_id", contrato_id);
  dataForm.append("resolucion_contrato_id", resolucion_contrato_id);
  dataForm.append("resolucion_tipo_contrato_id", resolucion_tipo_contrato_id);
  dataForm.append("fecha_resolucion", fecha_resolucion);

  auditoria_send({
    proceso: "guardar_resolucion_contrato_firmado",
    data: dataForm,
  });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      //  alert(datat)

      var respuesta = JSON.parse(resp);
      auditoria_send({
        respuesta: "guardar_resolucion_contrato_firmado",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        swal({
          title: "Registro exitoso",
          text: "La resolución de contrato firmado se guardo correctamente",
          html: true,
          type: "success",
          timer: 3000,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        setTimeout(function () {
          location.reload(true);
          return false;
        }, 3000);
        // } else if (respuesta.http_code == 400){
        // 	if (respuesta.campo_incompleto == "abogado") {
        // 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
        // 		var msg_error_text = '¿Desea ingresar el abogado?';
        // 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
        // 	}

        // 	swal({
        // 		title: msg_error_titulo,
        // 		text: msg_error_text,
        // 		html: true,
        // 		type: "warning",
        // 		showCancelButton: true,
        // 		confirmButtonColor: '#1cb787',
        // 		cancelButtonColor: '#d56d6d',
        // 		confirmButtonText: msg_error_confirmButtonText,
        // 		cancelButtonText: 'CANCELAR'
        // 	}, function (isConfirm) {
        // 		if(isConfirm){
        // 			if (respuesta.campo_incompleto == 'abogado') {
        // 				$('#btn_editar_resolucion_abogado_'+resolucion_contrato_id).click();
        // 			}
        // 		}
        // 	});
      } else {
        swal({
          title: "A ocurrido un error",
          text: respuesta.message,
          html: true,
          type: "success",
          timer: 3000,
          closeOnConfirm: false,
          showCancelButton: false,
        });
      }
    },
    complete: function () {
      loading(false);
    },
  });
}

function sec_contrato_detalle_guardar_resolucion_contrato_arrendamiento(
  resolucion_id
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var resolucion_contrato_id = resolucion_id;
  var resolucion_tipo_contrato_id = $(
    "#resolucion_tipo_contrato_id_" + resolucion_id
  ).val();
  var fecha_resolucion = $(
    "#cont_detalle_resolucion_contrato_fecha_" + resolucion_id
  )
    .val()
    .trim();
  var archivo_resolucion_contrato = document.getElementById(
    "archivo_resolucion_contrato_" + resolucion_id
  );

  if (fecha_resolucion.length == 0) {
    alertify.error("Seleccione una fecha de resolución", 5);
    $("#cont_detalle_resolucion_contrato_fecha").focus();
    return false;
  }
  if (archivo_resolucion_contrato.files.length == 0) {
    alertify.error("Ingrese la resolución de contrato firmado", 5);
    $("#archivo_resolucion_contrato").focus();
    return false;
  }

  var dataForm = new FormData(
    $("#form_resolucion_contrato_firmado_" + resolucion_id)[0]
  );

  dataForm.append("accion", "guardar_resolucion_contrato_firmado");
  dataForm.append("contrato_id", contrato_id);
  dataForm.append("resolucion_contrato_id", resolucion_contrato_id);
  dataForm.append("resolucion_tipo_contrato_id", resolucion_tipo_contrato_id);
  dataForm.append("fecha_resolucion", fecha_resolucion);

  auditoria_send({
    proceso: "guardar_resolucion_contrato_firmado",
    data: dataForm,
  });

  $.ajax({
    url: "sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (resp) {
      //  alert(datat)

      var respuesta = JSON.parse(resp);
      auditoria_send({
        respuesta: "guardar_resolucion_contrato_firmado",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        swal({
          title: "Registro exitoso",
          text: "La resolución de contrato firmado se guardo correctamente",
          html: true,
          type: "success",
          timer: 3000,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        setTimeout(function () {
          location.reload(true);
          return false;
        }, 3000);
        // } else if (respuesta.http_code == 400){
        // 	if (respuesta.campo_incompleto == "abogado") {
        // 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
        // 		var msg_error_text = '¿Desea ingresar el abogado?';
        // 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
        // 	}

        // 	swal({
        // 		title: msg_error_titulo,
        // 		text: msg_error_text,
        // 		html: true,
        // 		type: "warning",
        // 		showCancelButton: true,
        // 		confirmButtonColor: '#1cb787',
        // 		cancelButtonColor: '#d56d6d',
        // 		confirmButtonText: msg_error_confirmButtonText,
        // 		cancelButtonText: 'CANCELAR'
        // 	}, function (isConfirm) {
        // 		if(isConfirm){
        // 			if (respuesta.campo_incompleto == 'abogado') {
        // 				$('#btn_editar_resolucion_abogado_'+resolucion_id).click();
        // 			}
        // 		}
        // 	});
      } else {
        swal({
          title: "A ocurrido un error",
          text: respuesta.message,
          html: true,
          type: "success",
          timer: 3000,
          closeOnConfirm: false,
          showCancelButton: false,
        });
      }
    },
    complete: function () {
      loading(false);
    },
  });
}
///FIN RESOLICION DE CONTRATO FIRMADO

///AGREGAR CONTRAPRESTACION EN DETALLE SOLICITUD
function sec_con_det_prov_guardar_contraprestacion() {
  var contrato_id = $("#contrato_id_temporal").val();
  var moneda_id = $("#moneda_id").val();
  var subtotal_sin_parse = $("#subtotal").val().trim().replace(",", "");
  var igv_sin_parse = $("#igv").val().trim().replace(",", "");
  var monto_sin_parse = $("#monto").val().trim().replace(",", "");
  var subtotal = parseFloat(subtotal_sin_parse);
  var igv = parseFloat(igv_sin_parse);
  var monto = parseFloat(monto_sin_parse);
  var forma_pago_detallado = $("#forma_pago_detallado").val();
  var tipo_comprobante = $("#tipo_comprobante").val().trim();
  var plazo_pago = $("#plazo_pago").val().trim();

  if (parseInt(moneda_id) == 0) {
    alertify.error("Seleccione el tipo de moneda", 5);
    $("#moneda_id").focus();
    $("#moneda_id").select2("open");
    return false;
  }

  if (monto_sin_parse.length == 0) {
    alertify.error("Ingrese el monto bruto", 5);
    $("#monto").focus();
    return false;
  }

  if (subtotal_sin_parse.length == 0) {
    alertify.error("Ingrese el subtotal", 5);
    $("#subtotal").focus();
    return false;
  }

  if (igv_sin_parse.length == 0) {
    alertify.error("Ingrese el monto del IGV", 5);
    $("#igv").focus();
    return false;
  }

  if (parseInt(tipo_comprobante) == 0) {
    alertify.error("Seleccione tipo de comprobante a emitir", 5);
    $("#tipo_comprobante").focus();
    $("#tipo_comprobante").select2("open");
    return false;
  }

  if (plazo_pago.length == 0) {
    alertify.error("Ingrese el Plazo de Pago", 5);
    $("#plazo_pago").focus();
    return false;
  }

  if (forma_pago_detallado.length == 0) {
    alertify.error("Ingrese la forma de pago", 5);
    $("#forma_pago_detallado").focus();
    return false;
  }

  if (forma_pago_detallado.length < 5) {
    alertify.error("La forma de pago debe de ser detallada", 5);
    $("#forma_pago_detallado").focus();
    return false;
  }

  if (monto.toFixed(2) != (subtotal + igv).toFixed(2)) {
    alertify.error(
      "La suma del Subtotal + el IGV no coincide con el Monto Bruto",
      5
    );
    return false;
  }

  var data = {
    accion: "guardar_contraprestacion",
    contrato_id: contrato_id,
    moneda_id: moneda_id,
    subtotal: subtotal,
    igv: igv,
    monto: monto,
    forma_pago_detallado: forma_pago_detallado,
    tipo_comprobante: tipo_comprobante,
    plazo_pago: plazo_pago,
  };

  auditoria_send({ proceso: "guardar_contraprestacion", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        respuesta: "guardar_contraprestacion",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        // $('#modal_recargaweb').modal('hide');
        // swal('Aviso', respuesta.status, 'warning');
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        swal("Contratos", "Se ha guardado la contraprestación", "success");
        setTimeout(function () {
          location.reload(true);
        }, 1500);
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_cancelar_solicitud_modal() {
  $("#modal_cancelar_solicitud").modal("show");
  $("#form_cancelar_solicitud")[0].reset();
  setTimeout(function () {
    $("#cancelado_motivo").focus();
  }, 200);
}

function sec_contrato_detalle_solicitudv2_cancelar_solicitud(contrato_id) {
  var cancelado_motivo = $("#cancelado_motivo").val().trim();

  if (cancelado_motivo == "") {
    alertify.error("Ingrese el motivo de la cancelación.", 5);
    $("#cancelado_motivo").focus();
    return false;
  }

  var contrato_id = $("#contrato_id_temporal").val();

  var data = {
    accion: "cancelar_solicitud",
    contrato_id: contrato_id,
    cancelado_motivo: cancelado_motivo,
  };

  swal(
    {
      html: true,
      title: "Cancelar solicitud",
      text: "¿Esta seguro de cancelar esta solicitud?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ed6b76",
      cancelButtonColor: "#d56d6d",
      confirmButtonText: "SI, CANCELAR SOLICITUD",
      cancelButtonText: "DESISTIR",
      closeOnConfirm: false,
    },
    function () {
      auditoria_send({ proceso: "cancelar_solicitud", data: data });
      $.ajax({
        url: "/sys/set_contrato_detalle_solicitudv2.php",
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
          auditoria_send({ proceso: "cancelar_solicitud", data: respuesta });

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

            setTimeout(function () {
              location.reload(true);
            }, 4000);

            return false;
          }
        },
        error: function () {},
      });
    }
  );

  return false;
}

function sec_contrato_detalle_agente_vigencia() {
  $("#plazo_id_arr option:selected").each(function () {
    plazo_id = $(this).val();
    if (plazo_id == 1) {
      $(".div_vig_def").show();
      setTimeout(function () {
        $("#cont_detalle_contrato_firmado_fecha_vencimiento_param").focus();
      }, 200);
    } else if (plazo_id == 2) {
      $(".div_vig_def").hide();
      setTimeout(function () {
        $("#cont_detalle_contrato_firmado_fecha_incio_param").focus();
      }, 200);
    }
  });
}

function sec_contrato_detalle_solicitudv2_aprobar_adenda(
  aprobar_id,
  adenda_id
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var tipo_contrato_id = $("#tipo_contrato_id_temporal").val();
  var texto_mensaje_pregunta = "";
  if (aprobar_id == 1) {
    texto_mensaje_pregunta = "¿Está seguro de aprobar la solicitud?";
  } else {
    texto_mensaje_pregunta = "¿Está seguro de rechazar la solicitud?";
  }
  swal(
    {
      title: texto_mensaje_pregunta,
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: false,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "aprobar_adenda",
          contrato_id: contrato_id,
          adenda_id: adenda_id,
          aprobar_id: aprobar_id,
        };
        auditoria_send({
          proceso: "aprobar_adenda",
          data: data,
        });
        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
          type: "POST",
          data: data,
          beforeSend: function (xhr) {
            loading(true);
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status) {
              swal({
                title: "¡Listo!",
                text: respuesta.message,
                type: "success",
                timer: 5000,
                closeOnConfirm: false,
              });

              var email_accion = "";
              var email_url = "";

              if (tipo_contrato_id == 2) {
                email_accion = "send_email_solicitud_adenda_proveedor";
                email_url = "/sys/set_contrato_nuevo_adenda_proveedor.php";
              } else if (tipo_contrato_id == 5) {
                email_accion =
                  "send_email_solicitud_adenda_acuerdo_confidencialidad";
                email_url =
                  "/sys/set_contrato_nuevo_adenda_acuerdo_confidencialidad.php";
              } else if (tipo_contrato_id == 6) {
                email_accion = "send_email_solicitud_adenda_agente";
                email_url = "/sys/set_contrato_nuevo_adenda_agente.php";
              } else if (tipo_contrato_id == 1) {
                email_accion = "send_email_solicitud_adenda_arrendamiento";
                email_url = "/sys/set_contrato_nuevo_adenda_arrendamiento.php";
              }

              var data = {
                accion: email_accion,
                adenda_id: adenda_id,
              };

              auditoria_send({
                proceso: "enviar_email_adenda",
                data: data,
              });

              $.ajax({
                url: email_url,
                type: "POST",
                data: data,
                success: function (resp) {},
              });
            } else {
              swal({
                title: "¡Error!",
                text:
                  "Ocurrio un error: " +
                  respuesta.message +
                  ", pongase en contacto con el personal de SOPORTE",
                type: "warning",
                timer: 5000,
                closeOnConfirm: false,
              });
            }
          },
          complete: function () {
            loading(false);

            setTimeout(function () {
              location.reload(true);
            }, 6000);
          },
        });
      }
    }
  );
}

function sec_contrato_detalle_solicitudv2_guardar_observaciones_proveedores_adenda_gerencia(
  adenda_id
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var tipo_contrato_id = $("#tipo_contrato_id_temporal").val();
  var observaciones = $("#observaciones_adenda_gerencia").val().trim();
  var tipo_observacion = "";

  if (observaciones == "") {
    alertify.error("Ingrese la observación", 5);
    $("#observaciones_adenda_gerencia").focus();
    return false;
  }

  swal(
    {
      title: "¿Está seguro de agregar y notificar la observación?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        if (tipo_contrato_id == 2) {
          tipo_observacion = "proveedor";
        } else if (tipo_contrato_id == 5) {
          tipo_observacion = "acuerdo_confidencialidad";
        }

        var data = {
          accion: "guardar_observaciones_contrato_adenda_gerencia",
          contrato_id: contrato_id,
          adenda_id: adenda_id,
          tipo_observacion: tipo_observacion,
          observaciones: observaciones,
        };

        auditoria_send({
          proceso: "guardar_observaciones_contrato_adenda_gerencia",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
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
            auditoria_send({
              proceso: "guardar_observaciones_contrato_adenda_gerencia",
              data: respuesta,
            });

            if (parseInt(respuesta.http_code) == 500) {
              swal({
                title: respuesta.mensaje,
                text: "",
                html: true,
                type: respuesta.status,
                closeOnConfirm: false,
                showCancelButton: false,
              });
              return false;
            }
            if (parseInt(respuesta.http_code) == 400) {
              swal("Aviso", respuesta.status, "warning");
              return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
              swal(
                {
                  title: "¡Observación ingresada correctamente!",
                  text: respuesta.message,
                  type: "success",
                  timer: 5000,
                  closeOnConfirm: false,
                },
                function () {
                  location.reload(); // Recarga la página
                }
              );
              sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_adenda_gerencia(
                adenda_id
              );
              $("#observaciones_adenda_gerencia").val("");
              $("#observaciones_adenda_gerencia").focus();
              return false;
            }
          },
          error: function () {},
        });
      }
    }
  );
}

function sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_adenda_gerencia(
  adenda_id
) {
  var contrato_id = $("#contrato_id_temporal").val();

  if (contrato_id > 0) {
    var data = {
      accion: "obtener_observaciones_adenda_gerencia",
      contrato_id: contrato_id,
      adenda_id: adenda_id,
    };

    auditoria_send({
      proceso: "obtener_observaciones_adenda_gerencia",
      data: data,
    });

    $.ajax({
      url: "/sys/set_contrato_detalle_solicitudv2.php",
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
          return false;
        }

        if (parseInt(respuesta.http_code) == 200) {
          $("#div_observaciones_adenda_gerencia_" + adenda_id).html(
            respuesta.result
          );

          if (respuesta.cant_mensaje > 4) {
            document.getElementById(
              "div_observaciones_adenda_gerencia_" + adenda_id
            ).style.height = "40em";
            document.getElementById(
              "div_observaciones_adenda_gerencia_" + adenda_id
            ).style.overflow = "scroll";
          }

          return false;
        }
      },
      error: function () {},
    });
  }
}

function sec_contrato_detalle_solicitudv2_agregar_formato_nombre_tienda(
  nombre_de_tienda
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var data = {
    accion: "agregar_formato_al_nombre_tienda",
    contrato_id: contrato_id,
    nombre_tienda: nombre_de_tienda,
  };
  auditoria_send({
    proceso: "agregar_formato_al_nombre_tienda",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        proceso: "agregar_formato_al_nombre_tienda",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        swal(
          "Ocurrió un error al actualizar el formato del nombre de tienda",
          respuesta.error,
          "warning"
        );
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        swal({
          title: "Actualización exitosa",
          text: "Se agregó el formato al nombre de tienda",
          html: true,
          type: "success",
          timer: 4000,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        setTimeout(function () {
          location.reload(true);
        }, 3000);
      }
    },
    error: function () {},
  });
}
// FIN FUNCIONES DETALLE SOLICITUD

/// INICIO INFLACION
function sec_contrato_detalle_modal_agregar_inflacion(contrato_detalle_id) {
  $("#modal_inflacion_titulo").html("Registrar Inflación");
  $("#btn_modal_if_agregar_agregar").show();
  $("#btn_modal_if_agregar_editar").hide();

  $("#modal_if_fecha").val("");
  $("#modal_if_contrato_detalle_id").val("");
  $("#modal_if_tipo_periodicidad_id").val("0").trigger("change");
  $("#modal_if_numero").val("");
  $("#modal_if_tipo_anio_mes").val("0").trigger("change");
  $("#modal_if_moneda_id").val("0").trigger("change");
  $("#modal_if_porcentaje_anadido").val("");
  $("#modal_if_tipo_inflacion").val("");
  $("#modal_if_minimo_inflacion").val("");
  $("#modalAgregarInflacion").modal({ backdrop: "static", keyboard: false });
  setTimeout(function () {
    // $("#contrato_incrementos_monto_o_porcentaje").focus();
  }, 500);

  $("#modal_if_contrato_detalle_id").val(contrato_detalle_id);
}

function name(params) {}

function sec_contrato_detalle_agregar_inflacion() {
  var contrato_id = $("#contrato_id_temporal").val();
  var contrato_detalle_id = $("#modal_if_contrato_detalle_id").val();
  var tipo_periodicidad_id = $("#modal_if_tipo_periodicidad_id").val();
  var numero = $("#modal_if_numero").val();
  var tipo_anio_mes = $("#modal_if_tipo_anio_mes").val();
  var porcentaje_anadido = $("#modal_if_porcentaje_anadido").val();
  var tope_inflacion = $("#modal_if_tope_inflacion").val();
  var minimo_inflacion = $("#modal_if_minimo_inflacion").val();
  var tipo_aplicacion_id = $("#modal_if_tipo_aplicacion_id").val();

  if (tipo_periodicidad_id == "" || tipo_periodicidad_id == "0") {
    alertify.error("Seleccione un tipo de valor", 5);
    $("#modal_if_tipo_periodicidad_id").select2("open");
    return false;
  }

  if (tipo_periodicidad_id == 1) {
    if (numero == "") {
      alertify.error("Ingrese un numero", 5);
      $("#modal_if_numero").focus();
      return false;
    }

    if (tipo_anio_mes == "" || tipo_anio_mes == "0") {
      alertify.error("seleccione una mes/año", 5);
      $("#modal_if_tipo_anio_mes").select2("open");
      return false;
    }
  }

  if (tipo_aplicacion_id == "" || tipo_aplicacion_id == "0") {
    alertify.error("Seleccione un tipo de aplicación", 5);
    $("#modal_if_tipo_aplicacion_id").select2("open");
    return false;
  }

  var accion = "guardar_inflacion";
  var data = {
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    accion: accion,
    tipo_periodicidad_id: tipo_periodicidad_id,
    numero: numero,
    tipo_anio_mes: tipo_anio_mes,
    porcentaje_anadido: porcentaje_anadido,
    tope_inflacion: tope_inflacion,
    minimo_inflacion: minimo_inflacion,
    tipo_aplicacion_id: tipo_aplicacion_id,
  };
  auditoria_send({ proceso: "guardar_inflacion", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ proceso: "guardar_inflacion", data: respuesta });
      if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_eliminar_inflacion(inflacion_id) {
  var contrato_id = $("#contrato_id_temporal").val();
  var accion = "eliminar_inflacion";
  var data = {
    contrato_id: contrato_id,
    accion: accion,
    inflacion_id: inflacion_id,
  };
  auditoria_send({ proceso: "eliminar_inflacion", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: "eliminar_inflacion", data: respuesta });
      if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
      }
    },
    error: function () {},
  });
}

// FINAL INFLACION

/// INICIO CUOTA EXTRAORDINARIA
function sec_contrato_detalle_modal_agregar_cuota_extraordinaria(
  contrato_detalle_id
) {
  $("#modal_cuota_extraordinaria_titulo").html(
    "Registrar Cuota Extraordinaria"
  );
  $("#btn_modal_ce_agregar_agregar").show();
  $("#btn_modal_ce_agregar_editar").hide();

  $("#modal_ce_mes").val("0").trigger("change");
  $("#modal_ce_multiplicador").val("");
  $("#modal_ce_meses_prox_pago").val("");
  $("#modalAgregarCuotaExtraordinaria").modal({
    backdrop: "static",
    keyboard: false,
  });
  setTimeout(function () {
    $("#modal_ce_mes").select2("open");
  }, 500);

  $("#modal_ce_contrato_detalle_id").val(contrato_detalle_id);
}

function sec_contrato_detalle_agregar_cuota_extraordinaria() {
  var contrato_id = $("#contrato_id_temporal").val();
  var mes = $("#modal_ce_mes").val();
  var multiplicador = $("#modal_ce_multiplicador").val();
  var contrato_detalle_id = $("#modal_ce_contrato_detalle_id").val();
  if (mes == "" || mes == "0") {
    alertify.error("Ingrese un mes", 5);
    $("#modal_ce_mes").select2("open");
    return false;
  }
  if (multiplicador == "") {
    alertify.error("Ingrese un multiplicador", 5);
    $("#modal_ce_multiplicador").focus();
    return false;
  }

  var accion = "guardar_cuota_extraordinaria";
  var data = {
    accion: accion,
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    mes: mes,
    multiplicador: multiplicador,
  };
  auditoria_send({ proceso: "guardar_cuota_extraordinaria", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        proceso: "guardar_cuota_extraordinaria",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_eliminar_cuota_extraordinaria(
  cuota_extraordinaria_id
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var accion = "eliminar_cuota_extraordinaria";
  var data = {
    contrato_id: contrato_id,
    accion: accion,
    cuota_extraordinaria_id: cuota_extraordinaria_id,
  };
  auditoria_send({ proceso: "eliminar_cuota_extraordinaria", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({
        respuesta: "eliminar_cuota_extraordinaria",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
      }
    },
    error: function () {},
  });
}
// FINAL CUOTA EXTRAORDINARIA

///TIPO DE PLAZO DE ARRENDAMIENTO
function change_plazo_arrendamiento(contrato_detalle_id) {
  var plazo_id = $("#plazo_id_arr_" + contrato_detalle_id).val();
  if (plazo_id == 1) {
    $(".div_vig_def_" + contrato_detalle_id).show();
    setTimeout(function () {
      $(
        "#cont_detalle_contrato_firmado_fecha_vencimiento_param_" +
          contrato_detalle_id
      ).focus();
    }, 200);
  } else if (plazo_id == 2) {
    $(".div_vig_def_" + contrato_detalle_id).hide();
    setTimeout(function () {
      $(
        "#cont_detalle_contrato_firmado_fecha_incio_param_" +
          contrato_detalle_id
      ).focus();
    }, 200);
  }
}

function sec_contrato_detalle_solicitudv2_btn_regresar() {
  let tipo_contrato_id = 0;
  let adenda_id = 0;
  let resolucion_id = 0;
  tipo_contrato_id = parseInt($("#tipo_contrato_id_temporal").val());
  adenda_id = $("#adenta_id_temporal").val().trim();
  resolucion_id = $("#resolucion_id_temporal").val().trim();
  if (resolucion_id !== "") {
    resolucion_id = parseInt(resolucion_id);
    if (resolucion_id > 0) {
      tipo_contrato_id = 11;
    }
  }
  if (adenda_id !== "") {
    adenda_id = parseInt(adenda_id);
    if (adenda_id > 0) {
      switch (tipo_contrato_id) {
        case 1:
          tipo_contrato_id = 3;
          break;
        case 2:
          tipo_contrato_id = 4;
          break;
        case 5:
          tipo_contrato_id = 9;
          break;
        case 6:
          tipo_contrato_id = 10;
          break;
        case 7:
          tipo_contrato_id = 8;
          break;
      }
    }
  }
  localStorage.setItem("contratos_tipo_contrato_id", 12);
  $(location).attr("href", "?sec_id=contrato&sub_sec_id=solicitud");
}

function fnc_modal_nuevo_documento_adenda() {
  $("#modalAgregarDocumentoAdenda").modal("show");
  $("#formArchivosModal_documento_adenda")[0].reset();
  $("#txtFile_req_solicitud_adenda").html("");
}

setArchivo_requisitos_arrendamiento_adenda($("#fileArchivo_adenda"));

function setArchivo_requisitos_arrendamiento_adenda(object) {
  $(document).on(
    "click",
    "#btnBuscarFile_req_solicitud_adenda",
    function (event) {
      event.preventDefault();
      object.click();
    }
  );

  object.on("change", function (event) {
    //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
    if ($(this)[0].files.length <= 1) {
      const name = $(this).val().split(/\\|\//).pop();
      //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
      truncated = name;
    } else {
      truncated = "";
      mensajeAlerta(
        "Advertencia:",
        "Solo esta permitido adjuntar un archivo.",
        claseTipoAlertas.alertaWarning,
        $("#divMensaje_modal_documento_adenda")
      );
      $("#fileArchivo_adenda").val("");
    }

    $("#txtFile_req_solicitud_adenda").html(truncated);
  });
}

function modal_documento_subir_adenda() {
  var archivo = $("#fileArchivo_adenda").val();
  var contrato_id = $("#contrato_id_temporal").val();
  if (archivo.length == 0) {
    alertify.error("Seleccione un archivo", 5);
    $("#fileArchivo_adenda").focus();
    return false;
  }
  var form_data = new FormData($("#formArchivosModal_documento_adenda")[0]);
  form_data.append("accion", "subir_documento_adenda");
  form_data.append("contrato_id", contrato_id);

  auditoria_send({ proceso: "subir_documento_adenda", data: form_data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status == 200) {
        m_reload();
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
      //filter_archivos_table(0);
    },
    always: function (data) {
      loading();
    },
  });
}

function fnc_modal_nuevo_documento_adenda_escision() {
  $("#modalAgregarDocumentoAdendaEscision").modal("show");
  $("#formArchivosModal_documento_adenda_escision")[0].reset();
  $("#txtFile_req_solicitud_arrendamiento_adenda_escision").html("");
}

setArchivo_requisitos_arrendamiento_adenda_escision(
  $("#fileArchivo_arrendamiento_adenda_escision")
);

function setArchivo_requisitos_arrendamiento_adenda_escision(object) {
  $(document).on(
    "click",
    "#btnBuscarFile_req_solicitud_arrendamiento_adenda_escision",
    function (event) {
      event.preventDefault();
      object.click();
    }
  );

  object.on("change", function (event) {
    //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
    if ($(this)[0].files.length <= 1) {
      const name = $(this).val().split(/\\|\//).pop();
      //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
      truncated = name;
    } else {
      truncated = "";
      mensajeAlerta(
        "Advertencia:",
        "Solo esta permitido adjuntar un archivo.",
        claseTipoAlertas.alertaWarning,
        $("#divMensaje_modal_documento_adenda_escision")
      );
      $("#fileArchivo_arrendamiento_adenda_escision").val("");
    }

    $("#txtFile_req_solicitud_arrendamiento_adenda_escision").html(truncated);
  });
}

function modal_documento_subir_adenda_escision() {
  var archivo = $("#fileArchivo_arrendamiento_adenda_escision").val();
  var contrato_id = $("#contrato_id_temporal").val();
  var empresa_id = $("#modal_aden_esc_empresa_id").val();
  var fecha_escision = $("#modal_aden_esc_fecha_escision").val();

  if (empresa_id.length == 0 || empresa_id == "0") {
    alertify.error("Seleccione una empresa", 5);
    $("#modal_aden_esc_empresa_id").select2("open");
    return false;
  }

  if (fecha_escision.length == 0) {
    alertify.error("Ingrese una fecha de escisión", 5);
    $("#modal_aden_esc_fecha_escision").focus();
    return false;
  }

  if (archivo.length == 0) {
    alertify.error("Seleccione un archivo", 5);
    $("#fileArchivo_arrendamiento_adenda_escision").focus();
    return false;
  }
  var form_data = new FormData(
    $("#formArchivosModal_documento_adenda_escision")[0]
  );
  form_data.append("accion", "subir_documento_adenda_escision_arrendamiento");
  form_data.append("contrato_id", contrato_id);
  form_data.append("empresa_id", empresa_id);
  form_data.append("fecha_escision", fecha_escision);
  auditoria_send({
    proceso: "subir_documento_adenda_escision_arrendamiento",
    data: form_data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status == 200) {
        m_reload();
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
      //filter_archivos_table(0);
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_contrato_detalle_cambiar_estado() {
  var contrato_id = $("#contrato_id_temporal").val();
  var estado = $("#contrato_estado").val();
  var motivo = $("#contrato_motivo").val();
  var accion = "guardar_estado_contrato";

  if (estado.length == 0) {
    alertify.error("Seleccione un estado", 5);
    $("#contrato_estado").focus();
    return false;
  }
  if (motivo.trim().length == 0) {
    alertify.error("Ingrese un motivo", 5);
    $("#contrato_motivo").focus();
    return false;
  }
  var data = {
    contrato_id: contrato_id,
    accion: accion,
    estado: estado,
    motivo: motivo,
  };
  auditoria_send({ proceso: "guardar_estado_contrato", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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
      auditoria_send({ respuesta: "guardar_estado_contrato", data: respuesta });
      if (parseInt(respuesta.http_code) == 200) {
        location.reload(true);
      } else {
        swal({
          type: "warning",
          title: "¡Alerta!",
          text: respuesta.message,
          html: true,
        });
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_reenviar_adenda(adenda_id, tipo_contrato) {
  var data = {
    adenda_id: adenda_id,
    tipo_contrato: tipo_contrato,
    accion: "reenviar_adenda_firmada",
  };
  let url_api = "";
  if (tipo_contrato == 1 || tipo_contrato == 2 || tipo_contrato == 5) {
    url_api = "/sys/set_contrato_detalle_solicitudv2.php";
  }
  if (tipo_contrato == 6) {
    url_api = "/sys/set_contrato_agente.php";
  }
  if (tipo_contrato == 7) {
    url_api = "/sys/set_contrato_detalle_solicitudv2_interno.php";
  }

  auditoria_send({ proceso: "reenviar_adenda_firmada", data: data });
  $.ajax({
    url: url_api,
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
      auditoria_send({ respuesta: "reenviar_adenda_firmada", data: respuesta });
      if (parseInt(respuesta.status) == 200) {
        swal({
          type: "success",
          title: "",
          text: respuesta.message,
          html: true,
        });
      } else {
        swal({
          type: "error",
          title: "",
          text: respuesta.message,
          html: true,
        });
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_reenviar_resolucion(
  resolucion_id,
  tipo_contrato
) {
  var data = {
    resolucion_id: resolucion_id,
    tipo_contrato: tipo_contrato,
    accion: "reenviar_resolucion_firmada",
  };
  let url_api = "";
  if (
    tipo_contrato == 1 ||
    tipo_contrato == 2 ||
    tipo_contrato == 5 ||
    tipo_contrato == 6 ||
    tipo_contrato == 7
  ) {
    url_api = "/sys/set_contrato_detalle_solicitudv2.php";
  }

  auditoria_send({ proceso: "reenviar_resolucion_firmada", data: data });
  $.ajax({
    url: url_api,
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
      auditoria_send({
        respuesta: "reenviar_resolucion_firmada",
        data: respuesta,
      });
      if (parseInt(respuesta.status) == 200) {
        swal({
          type: "success",
          title: "",
          text: respuesta.message,
          html: true,
        });
      } else {
        swal({
          type: "error",
          title: "",
          text: respuesta.message,
          html: true,
        });
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_resolucion_cambiar_estado_legal(
  resolucion_id,
  tipo_contrato
) {
  var estado_legal = $(
    "#resolucion_estado_solicitud_legal_" + resolucion_id
  ).val();
  if (estado_legal == "") {
    alertify.error("Seleccione un estado", 5);
    $("#resolucion_estado_solicitud_legal_" + resolucion_id).focus();
    return false;
  }
  var data = {
    resolucion_id: resolucion_id,
    tipo_contrato: tipo_contrato,
    estado_solicitud: estado_legal,
    accion: "cambiar_estado_legal_resolucion_contrato",
  };
  let url_api = "";
  if (
    tipo_contrato == 1 ||
    tipo_contrato == 2 ||
    tipo_contrato == 5 ||
    tipo_contrato == 6 ||
    tipo_contrato == 7
  ) {
    url_api = "/sys/set_contrato_detalle_solicitudv2.php";
  }

  auditoria_send({
    proceso: "cambiar_estado_legal_resolucion_contrato",
    data: data,
  });
  $.ajax({
    url: url_api,
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
      auditoria_send({
        respuesta: "cambiar_estado_legal_resolucion_contrato",
        data: respuesta,
      });
      if (parseInt(respuesta.status) == 200) {
        swal({
          type: "success",
          title: "",
          text: respuesta.message,
          html: true,
        });

        setTimeout(() => {
          location.reload();
        }, 3000);
      } else {
        swal({
          type: "error",
          title: "",
          text: respuesta.message,
          html: true,
        });
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_ver_eliminar_anexo(archivo_id) {
  var data = {
    archivo_id: archivo_id,
    accion: "eliminar_anexo",
  };
  let url_api = "/sys/set_contrato_detalle_solicitudv2.php";

  auditoria_send({ proceso: "eliminar_anexo", data: data });
  $.ajax({
    url: url_api,
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
      auditoria_send({ respuesta: "eliminar_anexo", data: respuesta });
      if (parseInt(respuesta.status) == 200) {
        swal({
          type: "success",
          title: "",
          text: respuesta.message,
          html: true,
        });

        setTimeout(() => {
          location.reload();
        }, 3000);
      } else {
        swal({
          type: "error",
          title: "",
          text: respuesta.message,
          html: true,
        });
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_eliminar_adenda_escision(
  adenda_escision_id
) {
  var data = {
    adenda_escision_id: adenda_escision_id,
    accion: "eliminar_adenda_escision",
  };
  let url_api = "/sys/set_contrato_detalle_solicitudv2.php";

  auditoria_send({ proceso: "eliminar_adenda_escision", data: data });
  $.ajax({
    url: url_api,
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
      auditoria_send({
        respuesta: "eliminar_adenda_escision",
        data: respuesta,
      });
      if (parseInt(respuesta.status) == 200) {
        swal({
          type: "success",
          title: "",
          text: respuesta.message,
          html: true,
        });

        setTimeout(function () {
          location.reload(true);
        }, 1000);
      } else {
        swal({
          type: "error",
          title: "",
          text: respuesta.message,
          html: true,
        });
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_modal_objeto_adenta(
  contrato_detalle_id = 0
) {
  $("#modal_objeto_contrato_detalle_id").val(0);
  $("#modal_objeto_adenda").modal("show");
}

function sec_contrato_detalle_solicitudv2_modal_guardar_objeto_adenda() {
  var contrato_id = $("#contrato_id_temporal").val();
  var contrato_detalle_id = $("#modal_objeto_contrato_detalle_id").val();
  var objeto_adenda = $("#modal_objeto_de_adenda").val();
  var archivo = $("#modal_archivo_objeto_adenda").val();

  if (objeto_adenda.trim().length == 0) {
    alertify.error("Ingrese un objeto", 5);
    $("#modal_objeto_de_adenda").focus();
    return false;
  }

  var form_data = new FormData($("#form_modal_objeto_adenda")[0]);
  form_data.append("accion", "guardar_objeto_adenda");
  form_data.append("contrato_id", contrato_id);
  form_data.append("contrato_detalle_id", contrato_detalle_id);
  form_data.append("objeto_adenda", objeto_adenda);

  auditoria_send({ proceso: "guardar_objeto_adenda", data: form_data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status == 200) {
        m_reload();
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
      //filter_archivos_table(0);
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_cont_detalle_agregarNuevoContratoFirmado() {
  $("#modalNuevosContratoFirmado").modal({
    backdrop: "static",
    keyboard: false,
  });
}

function sec_cont_detalle_modal_guardar_nuevo_contrato_firmado() {
  var contrato_id = $("#sec_modal_cont_firmado_contrato_id").val();
  var contrato_detalle_id = $(
    "#sec_modal_cont_firmado_contrato_detalle_id"
  ).val();
  var tipo_contrato_id = $("#sec_modal_cont_firmado_tipo_contrato_id").val();
  var tipo_archivo_id = $("#sec_modal_cont_firmado_tipo_archivo_id").val();

  var form_data = new FormData(
    $("#sec_nuevo_form_modal_nuevo_contrato_firmado")[0]
  );
  form_data.append("accion", "subir_nuevo_contrato_firmado");
  form_data.append("contrato_id", contrato_id);
  form_data.append("contrato_detalle_id", contrato_detalle_id);
  form_data.append("tipo_contrato_id", tipo_contrato_id);
  form_data.append("tipo_archivo_id", tipo_archivo_id);

  loading(true);

  auditoria_send({ proceso: "subir_nuevo_contrato_firmado", data: form_data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status == 200) {
        m_reload();
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_contrato_detalle_solicitudv2_aprobar_resolucion(
  estado_resolucion,
  resolucion_contrato_id
) {
  var contrato_id = $("#contrato_id_temporal").val();
  var tipo_contrato_id = $("#tipo_contrato_id_temporal").val();
  var texto_mensaje_pregunta = "";
  if (estado_resolucion == 1) {
    texto_mensaje_pregunta = "¿Está seguro de aprobar la solicitud?";
  } else {
    texto_mensaje_pregunta = "¿Está seguro de rechazar la solicitud?";
  }
  swal(
    {
      title: texto_mensaje_pregunta,
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: false,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "aprobar_rechazar_resolucion_contrato",
          contrato_id: contrato_id,
          resolucion_contrato_id: resolucion_contrato_id,
          estado_resolucion: estado_resolucion,
        };
        auditoria_send({
          proceso: "aprobar_rechazar_resolucion_contrato",
          data: data,
        });
        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
          type: "POST",
          data: data,
          beforeSend: function (xhr) {
            loading(true);
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status == 200) {
              swal({
                title: "¡Listo!",
                text: respuesta.message,
                type: "success",
                timer: 5000,
                closeOnConfirm: false,
              });
            } else {
              swal({
                title: "¡Error!",
                text: "Ocurrio un error, intentelo mas tarde.",
                type: "warning",
                timer: 5000,
                closeOnConfirm: false,
              });
            }
          },
          complete: function () {
            loading(false);

            setTimeout(function () {
              location.reload(true);
            }, 6000);
          },
        });
      }
    }
  );
}

function fnc_modal_nuevo_documento_resolucion_contrato() {
  $("#modalAgregarDocumentoResolucionContrato").modal("show");
  $("#formArchivosModal_documento_resolucion_contrato")[0].reset();
  $("#txtFile_req_solicitud_resolucion_contrato").html("");
}

setArchivo_requisitos_resolucion_contrato(
  $("#fileArchivo_resolucion_contrato")
);

function setArchivo_requisitos_resolucion_contrato(object) {
  $(document).on(
    "click",
    "#btnBuscarFile_req_solicitud_resolucion_contrato",
    function (event) {
      event.preventDefault();
      object.click();
    }
  );

  object.on("change", function (event) {
    //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
    if ($(this)[0].files.length <= 1) {
      const name = $(this).val().split(/\\|\//).pop();
      //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
      truncated = name;
    } else {
      truncated = "";
      mensajeAlerta(
        "Advertencia:",
        "Solo esta permitido adjuntar un archivo.",
        claseTipoAlertas.alertaWarning,
        $("#divMensaje_modal_documento_resolucion_contrato")
      );
      $("#fileArchivo_resolucion_contrato").val("");
    }

    $("#txtFile_req_solicitud_resolucion_contrato").html(truncated);
  });
}

function modal_documento_subir_resolucion_contrato() {
  var archivo = $("#fileArchivo_resolucion_contrato").val();
  var contrato_id = $("#contrato_id_temporal").val();
  if (archivo.length == 0) {
    alertify.error("Seleccione un archivo", 5);
    $("#fileArchivo_resolucion_contrato").focus();
    return false;
  }
  var form_data = new FormData(
    $("#formArchivosModal_documento_resolucion_contrato")[0]
  );
  form_data.append("accion", "subir_documento_resolucion_contrato");
  form_data.append("contrato_id", contrato_id);

  auditoria_send({
    proceso: "subir_documento_resolucion_contrato",
    data: form_data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status == 200) {
        setTimeout(function () {
          location.reload(true);
        }, 2000);
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
      //filter_archivos_table(0);
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_contrato_detalle_solicitudv2_atender_seguimiento_proceso(
  data_seguimiento
) {
  var data = {
    accion: "atender_seguimiento_proceso",
    seguimiento_id: data_seguimiento.seguimiento_id,
    nueva_etapa_id: data_seguimiento.nueva_etapa_id,
  };

  auditoria_send({ proceso: "atender_seguimiento_proceso", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response, status) {
      var respuesta = JSON.parse(response);
      auditoria_send({
        respuesta: "atender_seguimiento_proceso",
        data: respuesta,
      });
      if (respuesta.status == 200) {
        setTimeout(function () {
          location.reload(true);
        }, 2000);
        swal(respuesta.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: respuesta.message,
          html: true,
        });
      }
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_contrato_detalle_solicitudv2_reiniciar_seguimiento_proceso(
  data_seguimiento
) {
  var data = {
    accion: "reiniciar_seguimiento_proceso",
    tipo_documento_id: data_seguimiento.tipo_documento_id,
    proceso_id: data_seguimiento.proceso_id,
    proceso_detalle_id: data_seguimiento.proceso_detalle_id,
    nueva_etapa_id: data_seguimiento.nueva_etapa_id,
  };

  auditoria_send({ proceso: "reiniciar_seguimiento_proceso", data: data });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response, status) {
      var respuesta = JSON.parse(response);
      auditoria_send({
        respuesta: "reiniciar_seguimiento_proceso",
        data: respuesta,
      });
      if (respuesta.status == 200) {
        setTimeout(function () {
          location.reload(true);
        }, 2000);
        swal(respuesta.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: respuesta.message,
          html: true,
        });
      }
    },
    always: function (data) {
      loading();
    },
  });
}

$("#formArchivosModal_req_autorizacion_mincetur").on("submit", function (e) {
  e.preventDefault();
  var archivo = $("#fileArchivo_requisitos_autorizacion_mincetur").val();
  var contrato_id = $("#contrato_id_temporal").val();
  if (archivo.length == 0) {
    alertify.error("Seleccione un archivo", 5);
    $("#fileArchivo_requisitos_autorizacion_mincetur").focus();
    return false;
  }
  var form_data = new FormData(
    $("#formArchivosModal_req_autorizacion_mincetur")[0]
  );
  form_data.append("accion", "subir_documento_autorizacion_mincetur");
  form_data.append("contrato_id", contrato_id);

  auditoria_send({
    proceso: "subir_documento_autorizacion_mincetur",
    data: form_data,
  });

  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response, status) {
      result = JSON.parse(response);
      loading();
      if (result.status == 200) {
        m_reload();
        swal(result.message, "", "success");
      } else {
        swal({
          type: "warning",
          title: "Alerta!",
          text: result.message,
          html: true,
        });
      }
    },
    always: function (data) {
      loading();
    },
  });
});

setArchivo_requisitos_autorizacion_mincetur(
  $("#fileArchivo_requisitos_autorizacion_mincetur")
);

function setArchivo_requisitos_autorizacion_mincetur(object) {
  $(document).on(
    "click",
    "#btnBuscarFile_req_autorizacion_mincetur",
    function (event) {
      event.preventDefault();
      object.click();
    }
  );
  object.on("change", function (event) {
    if ($(this)[0].files.length <= 1) {
      const name = $(this).val().split(/\\|\//).pop();
      truncated = name;
    } else {
      truncated = "";
      mensajeAlerta(
        "Advertencia:",
        "Solo esta permitido adjuntar un archivo.",
        claseTipoAlertas.alertaWarning,
        $("#divMensajeAlertaLicFuncionamiento")
      );
      $("#fileArchivo_requisitos_autorizacion_mincetur").val("");
    }

    $("#txtFile_req_autorizacion_mincetur").html(truncated);
  });
}

$(document).on("click", ".btn_eliminar_autorizacion_mincetur", function () {
  var archivoId = $(this).data("archivo_id");

  swal(
    {
      title: "¿Está seguro de eliminar el archivo?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: false,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "sec_eliminar_autorizacion_mincetur",
          archivo_id: archivoId,
        };

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
          type: "POST",
          data: data,
          beforeSend: function (xhr) {
            loading(true);
          },
          success: function (resp) {
            var respuesta = JSON.parse(resp);

            if (respuesta.status === 200) {
              swal({
                title: "¡Listo!",
                text: respuesta.message,
                type: "success",
                timer: 5000,
                closeOnConfirm: false,
              });
            } else {
              swal({
                title: "¡Error!",
                text:
                  "Ocurrió un error: " +
                  respuesta.message +
                  ", póngase en contacto con el personal de SOPORTE",
                type: "warning",
                timer: 5000,
                closeOnConfirm: false,
              });
            }
          },
          error: function (jqXHR, textStatus, errorThrown) {
            swal({
              title: "Error!",
              text: "Ocurrió un error: " + textStatus + ", " + errorThrown,
              type: "warning",
              timer: 5000,
              closeOnConfirm: false,
            });
          },
          complete: function () {
            loading(false);
            location.reload(true);
          },
        });
      }
    }
  );
});

$(document).on(
  "click",
  ".btn_ver_historial_autorizacion_mincetur",
  function () {
    var archivo_id = $(this).data("archivo_id");

    $("#modalAutorizacionMinceturHistoricoCambios").modal("show");

    sec_autorizacion_mincetur_historico(archivo_id);
  }
);

function sec_autorizacion_mincetur_historico(archivo_id) {
  var data = {
    accion: "get_autorizacion_mincetur_historico",
    archivo_id: archivo_id,
  };
  $("#autorizacion_mincetur_historico_div_tabla").show();

  var columnDefs = [
    {
      className: "text-center",
      targets: [0, 1, 2, 3, 4],
    },
  ];

  var tabla = crearDataTable(
    "#autorizacion_mincetur_historico_datatable",
    "/sys/set_contrato_detalle_solicitudv2.php",
    data,
    columnDefs
  );

  // Eliminar el campo de búsqueda
  tabla.on("init.dt", function () {
    $(".dataTables_filter").hide();
  });
}

$(document).on("click", ".btn_reemplazar_autorizacion_mincetur", function (e) {
  var archivoId = $(this).data("archivo_id");
  $("#moda_reemplazar_archivo_req_autorizacion_mincetur")
    .find("#archivoIdField")
    .val(archivoId);
});

$("#formArchivosModal_reemplazar_autorizacion_mincetur").on(
  "submit",
  function (e) {
    e.preventDefault();
    var archivoId = $("#archivoIdField").val();
    var archivo = $("#fileArchivo_reemplazar_autorizacion_mincetur").val();
    if (archivo.length == 0) {
      alertify.error("Seleccione un archivo", 5);
      $("#fileArchivo_reemplazar_autorizacion_mincetur").focus();
      return false;
    }
    var form_data = new FormData(
      $("#formArchivosModal_reemplazar_autorizacion_mincetur")[0]
    );
    form_data.append("accion", "reemplazar_documento_autorizacion_mincetur");
    form_data.append("archivo_id", archivoId);
    auditoria_send({
      proceso: "reemplazar_documento_autorizacion_mincetur",
      data: form_data,
    });

    $.ajax({
      url: "/sys/set_contrato_detalle_solicitudv2.php",
      type: "POST",
      data: form_data,
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function () {
        loading("true");
      },
      complete: function () {
        loading();
      },
      success: function (response, status) {
        result = JSON.parse(response);
        loading();
        if (result.status == 200) {
          m_reload();
          swal(result.message, "", "success");
        } else {
          swal({
            type: "warning",
            title: "Alerta!",
            text: result.message,
            html: true,
          });
        }
      },
      always: function (data) {
        loading();
      },
    });
  }
);

setArchivo_reemplazar_autorizacion_mincetur(
  $("#fileArchivo_reemplazar_autorizacion_mincetur")
);

function setArchivo_reemplazar_autorizacion_mincetur(object) {
  $(document).on(
    "click",
    "#btnBuscarFile_reemplazar_autorizacion_mincetur",
    function (event) {
      event.preventDefault();
      object.click();
    }
  );
  object.on("change", function (event) {
    if ($(this)[0].files.length <= 1) {
      const name = $(this).val().split(/\\|\//).pop();
      truncated = name;
    } else {
      truncated = "";
      mensajeAlerta(
        "Advertencia:",
        "Solo esta permitido adjuntar un archivo.",
        claseTipoAlertas.alertaWarning,
        $("#divMensajeAlertaLicFuncionamiento")
      );
      $("#fileArchivo_reemplazar_autorizacion_mincetur").val("");
    }

    $("#txtFile_reemplazar_autorizacion_mincetur").html(truncated);
  });
}

function sec_contrato_detalle_aprobar_rechazar_cambio(cambio_id, estado) {
  var data = {
    cambio_id: cambio_id,
    accion: "aprobar_rechazar_cambio",
    estado: estado,
  };
  auditoria_send({ proceso: "aprobar_rechazar_cambio", data: data });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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

      auditoria_send({ respuesta: "aprobar_rechazar_cambio", data: respuesta });
      if (parseInt(respuesta.status) == 200) {
        swal(respuesta.message, "", "success");
        setTimeout(() => {
          location.reload(true);
        }, 3000);
      } else {
        swal("Aviso", respuesta.message, "warning");
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_reenviar_notificacion_seguimiento_proceso(
  seguimiento_id,
  tipo_contrato_id
) {
  var data = {
    seguimiento_id: seguimiento_id,
    tipo_contrato_id: tipo_contrato_id,
    accion: "reenviar_notificacion_seguimiento_proceso",
  };
  auditoria_send({
    proceso: "reenviar_notificacion_seguimiento_proceso",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_detalle_solicitudv2.php",
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

      auditoria_send({
        respuesta: "reenviar_notificacion_seguimiento_proceso",
        data: respuesta,
      });
      if (parseInt(respuesta.status) == 200) {
        swal(respuesta.message, "", "success");
        setTimeout(() => {
          location.reload(true);
        }, 3000);
      } else {
        swal("Aviso", respuesta.message, "warning");
      }
    },
    error: function () {},
  });
}

function sec_contrato_detalle_solicitudv2_eliminar_observacion(id_observacion) {
  swal(
    {
      title: "¿Está seguro de eliminar la observación?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        var data = {
          accion: "eliminar_observacion_contrato",
          id_observacion: id_observacion,
        };
        auditoria_send({
          proceso: "eliminar_observacion_contrato",
          data: data,
        });

        $.ajax({
          url: "/sys/set_contrato_detalle_solicitudv2.php",
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
            auditoria_send({
              respuesta: "eliminar_observacion_contrato",
              data: respuesta,
            });
            if (parseInt(respuesta.status) == 200) {
              swal(respuesta.message, "", "success");
              sec_contrato_detalle_solicitudv2_actualizar_div_observaciones_locacionservicio();
              return false;
            } else {
              swal(respuesta.message, "", "warning");
            }
          },
          error: function () {},
        });
      }
    }
  );
}
