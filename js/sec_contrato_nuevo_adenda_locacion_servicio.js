// INICIO DECLARACION DE VARIABLES ARRAY
var array_proveedores_contrato = [];
var array_contraprestacion_contrato = [];
var array_nuevos_files_anexos = [];
var array_adendas_contrato = [];
// FIN DECLARACION DE VARIABLES ARRAY

function sec_contrato_nuevo_adenda_locacion_servicio() {
  $(".select2").select2({ width: "100%" });
  $(".sec_contrato_nuevo_datepicker")
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

  $(".sec_contrato_nuevo_adenda_datepicker")
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

  sec_con_nuevo_aden_locacion_servicio_obtener_opciones(
    "obtener_tiendas",
    "[name='sec_con_nuevo_tienda']"
  );
  // sec_con_nuevo_aden_locacion_servicio_obtener_opciones("obtener_abogados","[name='abogado_id']");
  // sec_con_nuevo_aden_locacion_servicio_obtener_opciones("obtener_empresa_at","[name='sec_con_nuevo_empresa_grupo_at_2']");
  // sec_con_nuevo_aden_locacion_servicio_obtener_opciones("obtener_bancos","[name='sec_con_nuevo_banco']");
  // sec_con_nuevo_aden_locacion_servicio_obtener_opciones("obtener_periodo","[name='periodo']");
  // sec_con_nuevo_aden_locacion_servicio_obtener_opciones("obtener_monedas","[name='modal_contr_ade_int_moneda_id']");
  // sec_con_nuevo_aden_locacion_servicio_obtener_opciones("obtener_forma_pago","[name='modal_contr_ade_int_forma_pago']");
  // sec_con_nuevo_aden_locacion_servicio_obtener_opciones("obtener_tipo_comprobante","[name='modal_contr_ade_int_tipo_comprobante']");

  //NIF16
  sec_contrato_nuevo_obtener_opciones(
    "obtener_tipo_periodicidad",
    $("[name='modal_if_tipo_periodicidad_id']")
  );
  sec_contrato_nuevo_obtener_opciones(
    "obtener_tipo_anio_mes",
    $("[name='modal_if_tipo_anio_mes']")
  );
  sec_contrato_nuevo_obtener_opciones(
    "obtener_meses",
    $("[name='modal_ce_mes']")
  );
  sec_contrato_nuevo_obtener_opciones(
    "obtener_directores",
    "[name='director_aprobacion_id']"
  );

  $(".moneda").on({
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

  $("#modal_propietario_tipo_persona_aa").change(function () {
    $("#modal_propietario_tipo_persona_aa option:selected").each(function () {
      tipo_persona = $(this).val();
      if (tipo_persona == 1) {
        $("#modal_propietario_tipo_docu_aa").val("1");
        $("#div_modal_propietario_representante_legal_aa").hide();
        $("#div_modal_propietario_num_partida_registral_aa").hide();
      } else if (tipo_persona == 2) {
        $("#modal_propietario_tipo_docu_aa").val("2");
        $("#div_modal_propietario_representante_legal_aa").show();
        $("#div_modal_propietario_num_partida_registral_aa").show();
      }
      $("#modal_propietario_tipo_docu_aa").change();
      setTimeout(function () {
        $("#modal_propietario_nombre").focus();
      }, 200);
    });
  });

  $("#modal_propietario_tipo_persona_contacto_aa").change(function () {
    $("#modal_propietario_tipo_persona_contacto_aa option:selected").each(
      function () {
        tipo_persona_contacto = $(this).val();
        if (tipo_persona_contacto == 1) {
          $("#div_modal_propietario_contacto_nombre_aa").hide();
          $("#modal_propietario_contacto_telefono_aa").focus();
        } else if (tipo_persona_contacto == 2) {
          $("#div_modal_propietario_contacto_nombre_aa").show();
          $("#modal_propietario_contacto_nombre_aa").focus();
        }
      }
    );
  });

  $("#modal_propietario_tipo_docu_aa").change(function () {
    $("#modal_propietario_tipo_docu_aa option:selected").each(function () {
      propietario_tipo_docu = $(this).val();
      if (
        propietario_tipo_docu == 1 ||
        propietario_tipo_docu == 3 ||
        propietario_tipo_docu == 4
      ) {
        $("#div_num_docu_propietario_aa").show();

        if (propietario_tipo_docu == 1) {
          $("#label_num_docu_propietario_aa").html(
            "Número de DNI del propietario:"
          );
          $(".mask_dni_agente").mask("00000000");
        } else if (propietario_tipo_docu == 3) {
          $("#label_num_docu_propietario_aa").html(
            "Número de Pasaporte del propietario:"
          );
          $(".mask_dni_agente").mask("000000000000");
        } else if (propietario_tipo_docu == 4) {
          $("#label_num_docu_propietario_aa").html(
            "Número de Carnet de Ext. del propietario:"
          );
          $(".mask_dni_agente").mask("000000000000");
        }

        setTimeout(function () {
          $("#modal_propietario_num_docu_aa").focus();
        }, 200);
      } else if (propietario_tipo_docu == 2) {
        $("#div_num_docu_propietario_aa").hide();

        setTimeout(function () {
          $("#modal_propietario_num_ruc_aa").focus();
        }, 200);
      }
    });
  });

  $("#adenda_inmueble_id_departamento").change(function () {
    $("#adenda_inmueble_id_departamento option:selected").each(function () {
      adenda_inmueble_id_departamento = $(this).val();
      var data = {
        accion: "obtener_provincias_segun_departamento",
        departamento_id: adenda_inmueble_id_departamento,
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
          console.log(respuesta);
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

            console.log(html);

            $("#adenda_inmueble_id_provincia").html(html).trigger("change");

            setTimeout(function () {
              $("#adenda_inmueble_id_provincia").select2("open");
            }, 500);

            return false;
          }
        },
        error: function () {},
      });
    });
  });

  $("#adenda_inmueble_id_provincia").change(function () {
    $("#adenda_inmueble_id_provincia option:selected").each(function () {
      adenda_inmueble_id_provincia = $(this).val();
      adenda_inmueble_id_departamento = $(
        "#adenda_inmueble_id_departamento"
      ).val();
      var data = {
        accion: "obtener_distritos_segun_provincia",
        provincia_id: adenda_inmueble_id_provincia,
        departamento_id: adenda_inmueble_id_departamento,
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
          console.log(respuesta);
          if (parseInt(respuesta.http_code) == 400) {
          }

          if (parseInt(respuesta.http_code) == 200) {
            array_distritos.push(respuesta.result);
            console.log("Cantidad de Registro: " + array_distritos.length);
            var html = '<option value="0">Seleccione el distrito</option>';

            for (var i = 0; i < array_distritos[0].length; i++) {
              html +=
                "<option value=" +
                array_distritos[0][i].id +
                ">" +
                array_distritos[0][i].nombre +
                "</option>";
            }

            console.log(html);

            $("#adenda_inmueble_id_distrito").html(html).trigger("change");

            setTimeout(function () {
              $("#adenda_inmueble_id_distrito").select2("open");
            }, 500);

            return false;
          }
        },
        error: function () {},
      });
    });
  });

  $("#adenda_inmueble_id_distrito").change(function () {
    var departamento_id = $("#adenda_inmueble_id_departamento")
      .val()
      .toString();
    var provincia_id = $("#adenda_inmueble_id_provincia").val().toString();
    var distrito_id = $("#adenda_inmueble_id_distrito").val().toString();

    var departamento_text = "";
    var data = $("#adenda_inmueble_id_departamento").select2("data");
    if (data) {
      departamento_text = data[0].text;
    }

    var provincia_text = "";
    var data = $("#adenda_inmueble_id_provincia").select2("data");
    if (data) {
      provincia_text = data[0].text;
    }

    var distrito_text = "";
    var data = $("#adenda_inmueble_id_distrito").select2("data");
    if (data) {
      distrito_text = data[0].text;
    }

    $("#ubigeo_id_nuevo").val(departamento_id + provincia_id + distrito_id);
    $("#ubigeo_text_nuevo").val(
      departamento_text + "/" + provincia_text + "/" + distrito_text
    );
  });

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

        $("#modal_beneficiario_num_docu").focus();
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
  // FIN CHANGE BENEFICIARIO

  // INICIO CHANGE INCREMENTOS
  $("#contrato_adenda_incrementos_monto_o_porcentaje").on({
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

  $("#contrato_adenda_incrementos_en").change(function () {
    $("#contrato_adenda_incrementos_en option:selected").each(function () {
      incrementos_en = $(this).val();
      if (incrementos_en != 0) {
        setTimeout(function () {
          $("#contrato_adenda_incrementos_continuidad").select2("open");
        }, 200);
      }
    });
  });

  $("#contrato_adenda_incrementos_continuidad").change(function () {
    $("#contrato_adenda_incrementos_continuidad option:selected").each(
      function () {
        continuidad_id = $(this).val();

        if (continuidad_id == 3) {
          $("#titulo_adenda_incremento_a_partir").html("");
          $("#titulo_adenda_incremento_a_partir").hide();
          $("#td_contrato_adenda_incrementos_a_partir_de_año").hide();
        } else {
          if (continuidad_id == 1) {
            $("#titulo_adenda_incremento_a_partir").html("El");
          } else if (continuidad_id == 2) {
            $("#titulo_adenda_incremento_a_partir").html("A partir del");
          }

          $("#titulo_adenda_incremento_a_partir").show();
          $("#td_contrato_adenda_incrementos_a_partir_de_año").show();

          setTimeout(function () {
            $("#contrato_adenda_incrementos_a_partir_de_año").select2("open");
          }, 200);
        }
      }
    );
  });
  // FIN CHANGE INCREMENTOS

  //INICIO - CONTRAPRESTACIONES
  $("#modal_contr_ade_int_moneda_id").change(function () {
    $("#modal_contr_ade_int_moneda_id option:selected").each(function () {
      modal_contr_ade_int_moneda_id = $(this).val();
      if (modal_contr_ade_int_moneda_id != 0) {
        setTimeout(function () {
          $("#modal_contr_ade_int_monto").focus();
        }, 200);
      }
    });
  });

  $("#modal_contr_ade_int_tipo_igv_id").change(function () {
    $("#modal_contr_ade_int_tipo_igv_id option:selected").each(function () {
      modal_contr_ade_int_tipo_igv_id = $(this).val();
      if (modal_contr_ade_int_tipo_igv_id != 0) {
        sec_con_nuevo_aden_arrend_calcular_subtotal_y_igv(
          modal_contr_ade_int_tipo_igv_id
        );
        setTimeout(function () {
          if ($("#modal_contr_ade_int_tipo_comprobante").val() == "0") {
            $("#modal_contr_ade_int_tipo_comprobante").focus();
            $("#modal_contr_ade_int_tipo_comprobante").select2("open");
          }
        }, 200);
      }
    });
  });

  $("#sec_con_nuevo_forma_pago").change(function () {
    $("#sec_con_nuevo_forma_pago option:selected").each(function () {
      sec_con_nuevo_forma_pago = $(this).val();
      if (sec_con_nuevo_forma_pago != 0) {
        setTimeout(function () {
          $("#modal_contr_ade_int_tipo_comprobante").focus();
          $("#modal_contr_ade_int_tipo_comprobante").select2("open");
        }, 200);
      }
    });
  });

  $("#modal_contr_ade_int_tipo_comprobante").change(function () {
    $("#modal_contr_ade_int_tipo_comprobante option:selected").each(
      function () {
        modal_contr_ade_int_tipo_comprobante = $(this).val();
        if (modal_contr_ade_int_tipo_comprobante != 0) {
          setTimeout(function () {
            $("#modal_contr_ade_int_plazo_pago").focus();
          }, 200);
        }
      }
    );
  });

  $("#modal_contr_ade_int_subtotal").on({
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

  $("#modal_contr_ade_int_igv").on({
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

  $("#modal_contr_ade_int_monto").on({
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
      $("#modal_contr_ade_int_tipo_igv_id").change();
    },
  });

  /// FIN CONTRAPRESTACIONES

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

  // INICIO ENMIENDA
  $(".datepicker_enmienda").datepicker({
    changeDay: true,
    changeMonth: true,
    changeYear: true,
    dateFormat: "MM yy",
    showButtonPanel: true,
    closeText: "Aceptar",
    currentText: "Este mes",
    onClose: function () {
      var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
      var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
      $(this).datepicker("setDate", new Date(iYear, iMonth, 1));

      setTimeout(function () {
        $(".ui-datepicker-calendar").css("display", "inline-table");
      }, 20);
    },
    beforeShow: function () {
      if ((selDate = $(this).val()).length > 0) {
        iYear = selDate.substring(selDate.length - 4, selDate.length);
        iMonth = jQuery.inArray(
          selDate.substring(0, selDate.length - 5),
          $(this).datepicker("option", "monthNames")
        );
        $(this).datepicker("option", "defaultDate", new Date(iYear, iMonth, 1));
        $(this).datepicker("setDate", new Date(iYear, iMonth, 1));
      }

      setTimeout(function () {
        $(".ui-datepicker-calendar").css("display", "none");
      }, 10);
    },
    onChangeMonthYear: function () {
      setTimeout(function () {
        $(".ui-datepicker-calendar").css("display", "none");
      }, 10);
    },
  });
  // FIN ENMIENDA
}

function sec_con_nuevo_aden_locacion_servicio_obtener_opciones(accion, select) {
  $.ajax({
    url: "/sys/get_contrato_nuevo_adenda_locacion_servicio.php",
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

      // setTimeout(function () {
      // 	$(select).select2("open");
      // }, 200);
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_obtener_contratos() {
  var proveedor = $("#sec_con_nuevo_tienda").val();

  let data = {
    accion: "obtener_contratos",
    proveedor: proveedor,
  };
  var select = "[name='sec_con_nuevo_contrato_id']";
  $.ajax({
    url: "/sys/get_contrato_nuevo_adenda_locacion_servicio.php",
    type: "POST",
    data: data,
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

function sec_con_nuevo_aden_locacion_obtener_contratos_interno_id() {
  var contrato_id = $("#sec_con_nuevo_tienda").val();
  let data = {
    accion: "obtener_contrato_locacion_servicio_por_id",
    contrato_id: contrato_id,
  };
  if (contrato_id == "" || contrato_id == 0) {
    $("#div_contrato_interno").html("");
    $("#div_detalle_solicitud_derecha").hide();
    return false;
  }

  $.ajax({
    url: "/sys/get_contrato_nuevo_adenda_locacion_servicio.php",
    type: "POST",
    data: data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      //  alert(datat)
      var respuesta = JSON.parse(datos);
      if (respuesta.status == 200) {
        $("#div_contrato_interno").html(respuesta.result);
        $("#div_detalle_solicitud_derecha").show();
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(
  nombre_menu_usuario,
  nombre_tabla,
  nombre_campo,
  nombre_campo_usuario,
  tipo_valor,
  valor_actual,
  metodo_select,
  id_del_registro
) {
  $("#div_modal_adenda_mensaje").hide();
  $("#form_adenda")[0].reset();
  $("#modal_adenda").modal({ backdrop: "static", keyboard: false });
  $("#adenda_valor_select_option").select2({
    dropdownParent: $("#modal_adenda"),
    width: "100%",
  });
  $("#adenda_nombre_menu_usuario").html(nombre_menu_usuario);
  $("#adenda_nombre_campo_usuario").html(nombre_campo_usuario);
  $("#adenda_valor_actual").html(valor_actual);

  $("#adenda_id_del_registro").val(id_del_registro);
  $("#adenda_nombre_tabla").val(nombre_tabla);
  $("#adenda_nombre_campo").val(nombre_campo);
  $("#adenda_tipo_valor").val(tipo_valor);

  if (tipo_valor == "varchar") {
    $("#div_adenda_valor_varchar").show();
    $("#div_adenda_valor_textarea").hide();
    $("#div_adenda_valor_int").hide();
    $("#div_adenda_valor_date").hide();
    $("#div_adenda_valor_decimal").hide();
    $("#div_adenda_valor_select_option").hide();
    $("#div_adenda_solicitud_departamento").hide();
    $("#div_adenda_solicitud_provincias").hide();
    $("#div_adenda_solicitud_distrito").hide();
    setTimeout(function () {
      $("#adenda_valor_varchar").focus();
    }, 500);
  }

  if (tipo_valor == "textarea") {
    $("#div_adenda_valor_varchar").hide();
    $("#div_adenda_valor_textarea").show();
    $("#div_adenda_valor_int").hide();
    $("#div_adenda_valor_date").hide();
    $("#div_adenda_valor_decimal").hide();
    $("#div_adenda_valor_select_option").hide();
    $("#div_adenda_solicitud_departamento").hide();
    $("#div_adenda_solicitud_provincias").hide();
    $("#div_adenda_solicitud_distrito").hide();
    setTimeout(function () {
      $("#div_adenda_valor_textarea").focus();
    }, 500);
  }

  if (tipo_valor == "int") {
    $("#div_adenda_valor_varchar").hide();
    $("#div_adenda_valor_textarea").hide();
    $("#div_adenda_valor_int").show();
    $("#div_adenda_valor_date").hide();
    $("#div_adenda_valor_decimal").hide();
    $("#div_adenda_valor_select_option").hide();
    $("#div_adenda_solicitud_departamento").hide();
    $("#div_adenda_solicitud_provincias").hide();
    $("#div_adenda_solicitud_distrito").hide();
    setTimeout(function () {
      $("#adenda_valor_int").focus();
    }, 500);
  }

  if (tipo_valor == "date") {
    $("#div_adenda_valor_varchar").hide();
    $("#div_adenda_valor_textarea").hide();
    $("#div_adenda_valor_int").hide();
    $("#div_adenda_valor_date").show();
    $("#div_adenda_valor_decimal").hide();
    $("#div_adenda_valor_select_option").hide();
    $("#div_adenda_solicitud_departamento").hide();
    $("#div_adenda_solicitud_provincias").hide();
    $("#div_adenda_solicitud_distrito").hide();
    setTimeout(function () {
      $("#adenda_valor_date").focus();
    }, 500);
  }

  if (tipo_valor == "decimal") {
    $("#div_adenda_valor_varchar").hide();
    $("#div_adenda_valor_textarea").hide();
    $("#div_adenda_valor_int").hide();
    $("#div_adenda_valor_date").hide();
    $("#div_adenda_valor_decimal").show();
    $("#div_adenda_valor_select_option").hide();
    $("#div_adenda_solicitud_departamento").hide();
    $("#div_adenda_solicitud_provincias").hide();
    $("#div_adenda_solicitud_distrito").hide();
    setTimeout(function () {
      $("#adenda_valor_decimal").focus();
    }, 500);
  }

  if (tipo_valor == "select_option") {
    if (nombre_campo == "ubigeo_id") {
      $("#div_adenda_valor_varchar").hide();
      $("#div_adenda_valor_textarea").hide();
      $("#div_adenda_valor_int").hide();
      $("#div_adenda_valor_date").hide();
      $("#div_adenda_valor_decimal").hide();
      $("#div_adenda_solicitud_departamento").show();
      $("#div_adenda_solicitud_provincias").show();
      $("#div_adenda_solicitud_distrito").show();

      $("#div_adenda_valor_select_option").hide();
      sec_con_nuevo_aden_locacion_servicio_obtener_opciones(
        metodo_select,
        $("[name='adenda_inmueble_id_departamento']")
      );
      $("#adenda_inmueble_id_departamento").select2({
        dropdownParent: $("#modal_adenda"),
        width: "100%",
      });
      $("#adenda_inmueble_id_provincia").select2({
        dropdownParent: $("#modal_adenda"),
        width: "100%",
      });
      $("#adenda_inmueble_id_distrito").select2({
        dropdownParent: $("#modal_adenda"),
        width: "100%",
      });
      setTimeout(function () {
        $("#adenda_inmueble_id_departamento").focus();
      }, 500);
    } else {
      $("#div_adenda_valor_varchar").hide();
      $("#div_adenda_valor_textarea").hide();
      $("#div_adenda_valor_int").hide();
      $("#div_adenda_valor_date").hide();
      $("#div_adenda_valor_decimal").hide();
      $("#div_adenda_solicitud_departamento").hide();
      $("#div_adenda_solicitud_provincias").hide();
      $("#div_adenda_solicitud_distrito").hide();

      $("#div_adenda_valor_select_option").show();
      sec_con_nuevo_aden_locacion_servicio_obtener_opciones(
        metodo_select,
        $("[name='adenda_valor_select_option']")
      );
      setTimeout(function () {
        $("#adenda_valor_select_option").focus();
      }, 500);
    }
  }
}

function sec_con_nuevo_aden_arrend_guardar_detalle_adenda(name_modal_close) {
  var nombre_tabla = $("#adenda_nombre_tabla").val();
  var nombre_campo = $("#adenda_nombre_campo").val();
  var nombre_menu_usuario = $("#adenda_nombre_menu_usuario").html();
  var nombre_campo_usuario = $("#adenda_nombre_campo_usuario").html();
  var valor_actual = $("#adenda_valor_actual").html();
  var tipo_valor = $("#adenda_tipo_valor").val();
  var valor_varchar = $("#adenda_valor_varchar").val();
  var valor_textarea = $("#adenda_valor_textarea").val();
  var valor_int = $("#adenda_valor_int").val();
  var valor_date = $("#adenda_valor_date").val();
  var valor_decimal = $("#adenda_valor_decimal").val();
  var valor_select_option = $(
    "#adenda_valor_select_option option:selected"
  ).text();
  var valor_select_option_id = $("#adenda_valor_select_option").val();
  var id_del_registro = $("#adenda_id_del_registro").val();

  var ubigeo_id_nuevo = $("#ubigeo_id_nuevo").val();
  var ubigeo_text_nuevo = $("#ubigeo_text_nuevo").val();

  $("#div_modal_adenda_mensaje").hide();

  if (tipo_valor == "varchar" && valor_varchar == "") {
    $("#div_modal_adenda_mensaje").show();
    $("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
    $("#adenda_valor_varchar").focus();
    return;
  }

  if (tipo_valor == "valor_textarea" && valor_valor_textarea == "") {
    $("#div_modal_adenda_mensaje").show();
    $("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
    $("#adenda_valor_valor_textarea").focus();
    return;
  }

  if (tipo_valor == "int" && valor_int == "") {
    $("#div_modal_adenda_mensaje").show();
    $("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
    $("#adenda_valor_int").focus();
    return;
  }

  if (tipo_valor == "date" && valor_date == "") {
    $("#div_modal_adenda_mensaje").show();
    $("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
    $("#adenda_valor_date").focus();
    return;
  }

  if (tipo_valor == "decimal" && valor_decimal == "") {
    $("#div_modal_adenda_mensaje").show();
    $("#modal_adenda_mensaje").html("Ingrese el nuevo valor");
    $("#adenda_valor_decimal").focus();
    return;
  }

  if (tipo_valor == "select_option" && nombre_campo == "ubigeo_id") {
    if (ubigeo_id_nuevo.length != 6) {
      $("#div_modal_adenda_mensaje").show();
      $("#modal_adenda_mensaje").html(
        "Seleccione una Departamento/Provincia/Distrito"
      );
      return;
    }
  } else {
    if (tipo_valor == "select_option" && valor_select_option_id == 0) {
      $("#div_modal_adenda_mensaje").show();
      $("#modal_adenda_mensaje").html("Seleccione una opcion");
      $("#adenda_valor_select_option").focus();
      return;
    }
  }

  if (tipo_valor == "select_option") {
    valor_int = valor_select_option_id;
  }

  var data = {
    accion: "guardar_adenda_detalle",
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: valor_actual,
    tipo_valor: tipo_valor,
    valor_varchar: valor_varchar,
    valor_textarea: valor_textarea,
    valor_int: valor_int,
    valor_date: valor_date,
    valor_decimal: valor_decimal,
    valor_select_option: valor_select_option,
    ubigeo_id_nuevo: ubigeo_id_nuevo,
    ubigeo_text_nuevo: ubigeo_text_nuevo,
    id_del_registro: id_del_registro,
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.error, "warning");
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          name_modal_close
        );
        $("#modal_adenda").modal("hide");
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
  id_adenda,
  modal
) {
  if (array_adendas_contrato.includes(id_adenda) === false) {
    array_adendas_contrato.push(id_adenda);
  }

  sec_con_nuevo_aden_arrend_actualizar_tabla_detalle_adenda();
}

function sec_con_nuevo_aden_arrend_eliminar_detalle_adenda(id_adenda) {
  const index = array_adendas_contrato.indexOf(id_adenda);
  if (index > -1) {
    array_adendas_contrato.splice(index, 1);
  }
  sec_con_nuevo_aden_arrend_actualizar_tabla_detalle_adenda();
}

function sec_con_nuevo_aden_arrend_actualizar_tabla_detalle_adenda() {
  if (array_adendas_contrato.length > 0) {
    var data = {
      accion: "obtener_adendas_detalle",
      id_adendas: JSON.stringify(array_adendas_contrato),
    };

    var array_adendas = [];

    auditoria_send({ proceso: "obtener_adendas_detalle", data: data });
    $.ajax({
      url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
        console.log(respuesta);
        if (parseInt(respuesta.http_code) == 400) {
          return false;
        }

        if (parseInt(respuesta.http_code) == 200) {
          $("#divTablaAdendas").html(respuesta.result);
          return false;
        }
      },
      error: function () {},
    });
  } else {
    $("#divTablaAdendas").html("");
  }
}

function sec_con_nuevo_aden_arrend_eliminar_detalle_adenda(id_adenda) {
  const index = array_adendas_contrato.indexOf(id_adenda);
  if (index > -1) {
    array_adendas_contrato.splice(index, 1);
  }
  sec_con_nuevo_aden_arrend_actualizar_tabla_detalle_adenda();
}

function sec_con_nuevo_aden_arrend_agregar_representante() {
  $("#modalNuevoProveedor").modal({ backdrop: "static", keyboard: false });
  // sec_con_detalle_int_limpiarInputsRL();
}

function sec_con_nuevo_aden_arrend_guardar_nuevo_representante_legal() {
  var contrato_id = $("#id_registro_contrato_id").val();

  var dniRepresentante = $("#modal_prov_ade_int_dni_representante").val();
  if (dniRepresentante.length != 8) {
    alertify.error("DNI debe tener 8 dígitos", 8);
    return false;
  }
  var nombreRepresentante = $("#modal_prov_ade_int_nombre_representante").val();
  var banco = $("#modal_prov_ade_int_prov_banco").val();
  var banco_nombre = $("#modal_prov_ade_int_prov_banco option:selected").text();
  var nro_cuenta = $("#modal_prov_ade_int_nro_cuenta").val();
  var nro_cci = $("#modal_prov_ade_int_nro_cci").val();
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

  var form_data = new FormData($("#frm_adenda_nuevo_proveedor")[0]);
  form_data.append("accion", "guardar_adenda_detalle_nuevos_registros");
  form_data.append("tabla", "representante_legal");
  form_data.append("contrato_id", contrato_id);
  form_data.append("dniRepresentante", dniRepresentante);
  form_data.append("nombreRepresentante", nombreRepresentante);
  form_data.append("banco", banco);
  form_data.append("nro_cuenta", nro_cuenta);
  form_data.append("nro_cci", nro_cci);
  loading(true);
  auditoria_send({
    proceso: "guardar_adenda_detalle_nuevos_registros",
    data: form_data,
  });
  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (response, status) {
      var respuesta = JSON.parse(response);
      auditoria_send({
        respuesta: "guardar_adenda_detalle_nuevos_registros",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        $("#frm_adenda_nuevo_proveedor")[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalNuevoProveedor"
        );
      }
    },
    always: function (data) {
      loading();
      console.log(data);
    },
  });
}

function sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
  id_adenda,
  modal
) {
  if (array_adendas_contrato.includes(id_adenda) === false) {
    array_adendas_contrato.push(id_adenda);
  }
  $("#".concat(modal)).modal("hide");

  sec_con_nuevo_aden_arrend_actualizar_tabla_detalle_adenda();
}

function sec_con_nuevo_aden_arrend_nuevo_contraprestacion_modal() {
  $("#modalNuevoContraprestacion").modal("show");
  $("#modal_contr_ade_int_moneda_id").focus();
}

function sec_con_nuevo_aden_arrend_calcular_subtotal_y_igv(tipo) {
  var monto = $("#modal_contr_ade_int_monto").val().trim().replace(",", "");
  var subtotal = 0;
  var igv = 0;

  if (monto != "") {
    monto = parseFloat(monto);
    subtotal = monto;

    if (tipo == "1") {
      subtotal = monto / 1.18;
      igv = monto - subtotal;
    }
  }

  $("#modal_contr_ade_int_subtotal").val(subtotal.toFixed(2));
  $("#modal_contr_ade_int_igv").val(igv.toFixed(2));

  $("#modal_contr_ade_int_subtotal").blur();
  $("#modal_contr_ade_int_igv").blur();
}

function sec_con_nuevo_aden_arrend_nuevo_contraprestacion() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var moneda_id = $("#modal_contr_ade_int_moneda_id").val();
  var monto = $("#modal_contr_ade_int_monto").val().trim();
  var tipo_igv_id = $("#modal_contr_ade_int_tipo_igv_id").val();
  var subtotal = $("#modal_contr_ade_int_subtotal").val().trim();
  var igv = $("#modal_contr_ade_int_igv").val().trim();
  var forma_pago = $("#modal_contr_ade_int_forma_pago").val();
  var tipo_comprobante = $("#modal_contr_ade_int_tipo_comprobante")
    .val()
    .trim();
  var plazo_pago = $("#modal_contr_ade_int_plazo_pago").val();
  var forma_pago_detallado = $(
    "#modal_contr_ade_int_forma_pago_detallado"
  ).val();

  if (parseInt(moneda_id) == 0) {
    alertify.error("Seleccione un tipo de moneda", 5);
    $("#modal_contr_ade_int_moneda_id").focus();
    $("#modal_contr_ade_int_moneda_id").select2("open");
    return false;
  }

  if (monto == "") {
    alertify.error("Ingrese un monto", 5);
    $("#modal_contr_ade_int_monto").focus();
    return false;
  }

  if (parseInt(tipo_igv_id) == 0) {
    alertify.error("Seleccione el IGV", 5);
    $("#modal_contr_ade_int_tipo_igv_id").focus();
    $("#modal_contr_ade_int_tipo_igv_id").select2("open");
    return false;
  }

  if (subtotal == "") {
    alertify.error("Ingrese un subtotal", 5);
    $("#modal_contr_ade_int_subtotal").focus();
    return false;
  }

  if (igv == "") {
    alertify.error("Ingrese un IGV", 5);
    $("#modal_contr_ade_int_igv").focus();
    return false;
  }

  if (parseInt(tipo_comprobante) == 0) {
    alertify.error("Seleccione el tipo de comprobante", 5);
    $("#modal_contr_ade_int_tipo_comprobante").focus();
    $("#modal_contr_ade_int_tipo_comprobante").select2("open");
    return false;
  }

  if (plazo_pago == "") {
    alertify.error("Ingrese un plazo de pago", 5);
    $("#modal_contr_ade_int_plazo_pago").focus();
    return false;
  }

  if (forma_pago_detallado == "") {
    alertify.error("Ingrese una forma de pago", 5);
    $("#modal_contr_ade_int_forma_pago_detallado").focus();
    return false;
  }

  var accion = "guardar_adenda_detalle_nuevos_registros";

  var data = {
    accion: accion,
    tabla: "contraprestacion",
    contrato_id: contrato_id,
    moneda_id: moneda_id,
    monto: monto,
    tipo_igv_id: tipo_igv_id,
    subtotal: subtotal,
    igv: igv,
    forma_pago: forma_pago,
    tipo_comprobante: tipo_comprobante,
    plazo_pago: plazo_pago,
    forma_pago_detallado: forma_pago_detallado,
  };

  auditoria_send({
    proceso: "guardar_adenda_detalle_nuevos_registros",
    data: data,
  });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (response) {
      //  alert(datat)
      var respuesta = JSON.parse(response);
      auditoria_send({
        respuesta: "guardar_adenda_detalle_nuevos_registros",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        $("#frm_adenda_nuevo_proveedor")[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalNuevoContraprestacion"
        );
      }
    },
    error: function () {},
  });
}

function obtenerTiposDeArchivo() {
  var tiposDeArchivo = [];
  // Supongamos que los tipos de archivo provienen de un input con el nombre "miarchivo[]"
  $('input[name="miarchivo[]"]').each(function () {
    tiposDeArchivo.push($(this).val());
  });
  return JSON.stringify(tiposDeArchivo);
}
function sec_con_nuevo_aden_arrend_guardar_adenda() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var tipo_contrato_id = $("#id_tipo_contrato").val();
  var aprobacion_obligatoria_id = $("#aprobacion_obligatoria_id").val().trim();
  var director_aprobacion_id = $("#director_aprobacion_id").val().trim();

  // Crea un objeto FormData
  var formData = new FormData();

  // Itera sobre todos los elementos de entrada de archivos
  var archivos = $('input[type="file"]');
  archivos.each(function (index, element) {
    var nombreCampo = $(element).attr("name");
    var archivos = $(element)[0].files;
    for (var i = 0; i < archivos.length; i++) {
      formData.append(nombreCampo + "_" + i, archivos[i]);
    }
  });

  $("#div_modal_adenda_mensaje").hide();

  if (contrato_id == "") {
    $("#div_modal_adenda_mensaje").show();
    $("#modal_adenda_mensaje").html("No se puede guardar la adenda");
  }

  if (array_adendas_contrato.length == 0) {
    alertify.error("No hay solicitud de cambio de adenda", 5);
    return false;
  }

  if (aprobacion_obligatoria_id == 1 && director_aprobacion_id == 0) {
    alertify.error("Seleccione el director que va aprobar la solicitud.", 5);
    setTimeout(function () {
      $("#director_aprobacion_id").focus();
      $("#director_aprobacion_id").select2("open");
    }, 200);
    return false;
  }

  var data = {
    accion: "guardar_adenda",
    contrato_id: contrato_id,
    tipo_contrato_id: tipo_contrato_id,
    id_adendas: JSON.stringify(array_adendas_contrato),
    aprobacion_obligatoria_id: aprobacion_obligatoria_id,
    director_aprobacion_id: director_aprobacion_id,
  };
  // Agrega los datos adicionales al FormData
  var arrayJSON = JSON.stringify(array_nuevos_files_anexos);

  formData.append("accion", "guardar_adenda");
  formData.append("contrato_id", contrato_id);
  formData.append("tipo_contrato_id", tipo_contrato_id);
  formData.append("id_adendas", JSON.stringify(array_adendas_contrato));
  formData.append("array_nuevos_files_anexos", arrayJSON);
  formData.append("aprobacion_obligatoria_id", aprobacion_obligatoria_id);
  formData.append("director_aprobacion_id", director_aprobacion_id);
  // console.log([...formData.entries()]);
  auditoria_send({ proceso: "guardar_adenda", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
    type: "POST",
    data: formData,
    processData: false, // No procesar los datos
    contentType: false, // No establecer el tipo de contenido
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      auditoria_send({ respuesta: "guardar_adenda", data: respuesta });
      if (parseInt(respuesta.http_code) == 400) {
        swal({
          title: respuesta.message,
          text: "",
          html: true,
          type: "warning",
          timer: 3000,
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
      }
      swal({
        title: respuesta.message,
        text: "",
        html: true,
        type: respuesta.status == 200 ? "success" : "warning",
        timer: 3000,
        closeOnConfirm: false,
        showCancelButton: false,
      });
      if (parseInt(respuesta.status) == 200) {
        setTimeout(function () {
          window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
          return false;
        }, 3000);
      }
    },
    error: function () {},
  });
}

////// new
function sec_con_nuevo_aden_arrend_buscar_propietario_modal(
  tipo,
  id_propiertario_old,
  id_persona_id
) {
  var titulo = "";
  if (tipo == "NuevoPropietario") {
    titulo = "Adenda - Buscar Nuevo Propietario";
  }
  if (tipo == "CambiarPropietario") {
    titulo = "Adenda - Buscar Propietario";
  }
  $("#modal_buscar_propietario_titulo").html(titulo);
  $("#modal_buscar_propietario_tipo_solicitud").val(tipo);
  $("#modal_id_propietario_old").val(id_propiertario_old);
  $("#modal_id_persona_old").val(id_persona_id);

  $("#modalBuscarPropietario_ca").modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#tlbPropietariosxBusqueda_ca").html("");
  $("#divNoSeEncontroPropietario_ca").hide();
  $("#divRegistrarNuevoPropietario_ca").hide();
  $("#modal_propietario_nombre_o_numdocu_ca").val("");
  $("#modal_propietario_nombre_o_numdocu_ca").focus();
}

function sec_con_nuevo_aden_arrend_buscar_propietario() {
  var array_propietarios = [];
  var nombre_o_numdocu = $.trim(
    $("#modal_propietario_nombre_o_numdocu_ca").val()
  );
  var tipo_busqueda = parseInt(
    $.trim($("#modal_propietario_tipo_busqueda_ca").val())
  );
  var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud").val();

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
    accion: "obtener_propietario",
    nombre_o_numdocu: nombre_o_numdocu,
    tipo_busqueda: tipo_busqueda,
    tipo_solicitud: tipo_solicitud,
  };

  auditoria_send({ proceso: "obtener_propietario", data: data });
  $.ajax({
    url: "/sys/get_contrato_nuevo_adenda_locacion_servicio.php",
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

function sec_con_nuevo_aden_arrend_agignar_propietario(id_persona) {
  var contrato_id = $("#id_registro_contrato_id").val();

  var data = {
    accion: "guardar_adenda_detalle_nuevos_registros",
    tabla: "propietario",
    contrato_id: contrato_id,
    id_persona: id_persona,
  };
  console.log(data);
  loading(true);
  auditoria_send({
    proceso: "guardar_adenda_detalle_nuevos_registros",
    data: data,
  });
  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
    type: "POST",
    data: data,
    success: function (response, status) {
      var respuesta = JSON.parse(response);
      auditoria_send({
        respuesta: "guardar_adenda_detalle_nuevos_registros",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 200) {
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalBuscarPropietario_ca"
        );
        $("#modalBuscarPropietario_ca").modal("hide");
      }
    },
    always: function (data) {
      loading();
    },
  });
}

function sec_con_nuevo_aden_arrend_reemplazar_propietario(id_persona) {
  var id_actual = $("#modal_id_persona_old").val();
  var registro_id = $("#modal_id_propietario_old").val();
  var id_del_registro = 0;

  var nombre_tabla = "cont_propietario";
  var nombre_campo = "persona_id";
  var nombre_menu_usuario = "Propietario";
  var nombre_campo_usuario = "Cambio de Propietario";
  var id_nuevo = id_persona;

  var data = {
    accion: "guardar_adenda_detalle",
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: id_actual,
    tipo_valor: "id_tabla",
    valor_varchar: "",
    valor_int: id_nuevo,
    valor_date: "",
    valor_decimal: "",
    valor_select_option: "",
    valor_id_tabla: registro_id,
    id_del_registro: id_del_registro,
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalBuscarPropietario_ca"
        );
        // $('#modal_adenda').modal('hide');
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_modal_eliminar_propietario(id_propietario) {
  var id_actual = id_propietario;

  var nombre_tabla = "cont_propietario";
  var nombre_campo = "status";
  var nombre_menu_usuario = "Propietario";
  var nombre_campo_usuario = "Eliminar Propietario";

  var data = {
    accion: "guardar_adenda_detalle",
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: id_actual,
    tipo_valor: "eliminar",
    valor_varchar: "",
    valor_int: id_actual,
    valor_date: "",
    valor_decimal: "",
    valor_select_option: "",
    valor_id_tabla: "",
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalBuscarPropietario_ca"
        );
        // $('#modal_adenda').modal('hide');
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_guardar_nuevo_representante_legalaaaaaa() {
  var contrato_id = $("#id_registro_contrato_id").val();

  var dniRepresentante = $("#modal_prov_ade_int_dni_representante").val();
  // if(dniRepresentante.length != 8){
  // 	alertify.error("DNI debe tener 8 dígitos", 8);
  // 	return false;
  // }
  var nombreRepresentante = $("#modal_prov_ade_int_nombre_representante").val();
  var banco = $("#modal_prov_ade_int_prov_banco").val();
  var banco_nombre = $("#modal_prov_ade_int_prov_banco option:selected").text();
  var nro_cuenta = $("#modal_prov_ade_int_nro_cuenta").val();
  var nro_cci = $("#modal_prov_ade_int_nro_cci").val();
  var input_vacios = "";
  // if($.trim(dniRepresentante) == "") { input_vacios += " - DNI del Representante"; }
  // if($.trim(nombreRepresentante) == "") { input_vacios += " - Nombre del Representante"; }
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

  var form_data = new FormData($("#frm_adenda_nuevo_proveedor")[0]);
  form_data.append("accion", "guardar_adenda_detalle_nuevos_registros");
  form_data.append("tabla", "representante_legal");
  form_data.append("contrato_id", contrato_id);
  form_data.append("dniRepresentante", dniRepresentante);
  form_data.append("nombreRepresentante", nombreRepresentante);
  form_data.append("banco", banco);
  form_data.append("nro_cuenta", nro_cuenta);
  form_data.append("nro_cci", nro_cci);
  loading(true);
  auditoria_send({
    proceso: "guardar_adenda_detalle_nuevos_registros",
    data: form_data,
  });
  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
    type: "POST",
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (response, status) {
      var respuesta = JSON.parse(response);
      auditoria_send({
        respuesta: "guardar_adenda_detalle_nuevos_registros",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        $("#frm_adenda_nuevo_proveedor")[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalNuevoProveedor"
        );
      }
    },
    always: function (data) {
      loading();
      console.log(data);
    },
  });
}

function sec_con_nuevo_aden_arrend_propietario_modal() {
  var tipo_solicitud = "adenda";

  $("#modal_nuevo_propietario_tipo_solicitud_aa").val(tipo_solicitud);
  $("#modal_nuevo_propietario_titulo_aa").val("Adenda - Nuevo Propietario");
  sec_con_nuevo_aden_arrend_resetear_formulario_nuevo_propietario_agente();

  $("#div_modal_propietario_mensaje_aa").hide();
  $("#modalBuscarPropietario_ca").modal("hide");
  $("#modalNuevoPropietario_aa").modal({ backdrop: "static", keyboard: false });

  var tipo_busqueda = $("#modal_propietario_tipo_busqueda_aa").val();
  var nombre_o_numdocu = $("#modal_propietario_nombre_o_numdocu_aa").val();
  if (tipo_busqueda == 1) {
    $("#modal_propietario_nombre_aa").val(nombre_o_numdocu);
  } else if (tipo_busqueda == 2) {
    $("#modal_propietario_num_docu_aa").val(nombre_o_numdocu);
  }
  setTimeout(function () {
    $("#modal_propietario_tipo_persona_aa").select2("open");
  }, 500);
}

function sec_con_nuevo_aden_arrend_resetear_formulario_nuevo_propietario_agente() {
  $("#frm_nuevo_propietario_aa")[0].reset();
  $("#div_modal_propietario_representante_legal_aa").hide();
  $("#div_modal_propietario_num_partida_registral_aa").hide();

  $("#modal_nuevo_propietario_titulo_aa").html("Registrar Propietario");
  $("#btn_agregar_propietario_aa").show();
  $("#btn_guardar_cambios_propietario_aa").hide();
  $("#btn_agregar_propietario_a_la_adenda_aa").hide();

  $("#div_modal_propietario_contacto_nombre_aa").hide();
  $("#div_modal_propietario_persona_contacto_aa").show();
}

function sec_con_nuevo_aden_arrend_nuevo_propietario() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud").val();
  var id_propietario_para_cambios = $("#modal_id_propietario_old").val();
  var id_persona_para_cambios = $("#modal_id_persona_old").val();

  var tipo_persona = $("#modal_propietario_tipo_persona_aa").val();
  var nombre = $("#modal_propietario_nombre_aa").val().trim();
  var tipo_docu = $("#modal_propietario_tipo_docu_aa").val();
  var num_docu = $("#modal_propietario_num_docu_aa").val().trim();
  var num_ruc = $("#modal_propietario_num_ruc_aa").val().trim();
  var direccion = $("#modal_propietario_direccion_aa").val().trim();
  var representante_legal = $("#modal_propietario_representante_legal_aa")
    .val()
    .trim();
  var num_partida_registral = $(
    "#modal_propietario_num_partida_registral_aa"
  ).val();
  var tipo_persona_contacto = $(
    "#modal_propietario_tipo_persona_contacto_aa"
  ).val();
  var contacto_nombre = $("#modal_propietario_contacto_nombre_aa").val().trim();
  var contacto_telefono = $("#modal_propietario_contacto_telefono_aa")
    .val()
    .trim();
  var contacto_email = $("#modal_propietario_contacto_email_aa").val().trim();

  if (parseInt(tipo_persona) == 0) {
    alertify.error("Seleccione el tipo de persona", 5);
    $("#modal_propietario_tipo_persona_ca").focus();
    $("#modal_propietario_tipo_persona_ca").select2("open");
    return false;
  }

  if (nombre.length < 6) {
    alertify.error("Ingrese el nombre completo del propietario", 5);
    $("#modal_propietario_nombre_ca").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 0) {
    alertify.error("Seleccione el tipo de documento de identidad", 5);
    $("#modal_propietario_tipo_docu_ca").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 1 && num_docu.length != 8) {
    alertify.error(
      "El número de DNI debe tener 8 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu_ca").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 3 && num_docu.length != 12) {
    alertify.error(
      "El número de Pasaporte debe tener 12 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu_ca").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 4 && num_docu.length != 12) {
    alertify.error(
      "El número de Carnet de Ext. debe tener 12 dígitos, no " +
        num_docu.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_docu_ca").focus();
    return false;
  }

  if (num_ruc.length != 11) {
    alertify.error(
      "El número de RUC debe tener 11 dígitos, no " +
        num_ruc.length +
        " dígitos",
      5
    );
    $("#modal_propietario_num_ruc_ca").focus();
    return false;
  }

  if (direccion.length < 10) {
    alertify.error("Ingrese el dirección completa del propietario", 5);
    $("#modal_propietario_direccion_ca").focus();
    return false;
  }

  if (parseInt(tipo_persona) == 2 && representante_legal.length == 0) {
    alertify.error("Ingrese el representante legal", 5);
    $("#modal_propietario_representante_legal_ca").focus();
    return false;
  }

  if (parseInt(tipo_persona) == 2 && num_partida_registral.length == 0) {
    alertify.error(
      "Ingrese el número de la Partida Registral de la empresa",
      5
    );
    $("#modal_propietario_num_partida_registral_ca").focus();
    return false;
  }

  if (parseInt(tipo_persona_contacto) == 0) {
    alertify.error("Seleccione el tipo de persona contacto", 5);
    $("#modal_propietario_tipo_persona_contacto_ca").focus();
    return false;
  }

  if (parseInt(tipo_persona_contacto) == 2 && contacto_nombre.length < 1) {
    alertify.error("Ingrese el nombre del contacto", 5);
    $("#modal_propietario_contacto_nombre_ca").focus();
    return false;
  }

  if (contacto_telefono.length < 8) {
    alertify.error("Ingrese el número telefónico del contacto", 5);
    $("#modal_propietario_contacto_telefono_ca").focus();
    return false;
  }

  if (
    contacto_email.length > 0 &&
    !sec_contrato_nuevo_es_email_valido(contacto_email)
  ) {
    alertify.error("El formato del correo electrónico es incorrecto", 5);
    $("#modal_propietario_contacto_email_ca").focus();
    return false;
  }

  var data = {
    accion: "guardar_propietario",
    contrato_id: contrato_id,
    id_propietario_para_cambios: id_propietario_para_cambios,
    id_persona_para_cambios: id_persona_para_cambios,
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
    tipo_solicitud: tipo_solicitud,
  };

  auditoria_send({ proceso: "guardar_propietario", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
        respuesta: "guardar_adenda_detalle_nuevos_registros",
        data: respuesta,
      });
      if (parseInt(respuesta.http_code) == 400) {
        alertify.error(respuesta.status, 5);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#frm_adenda_nuevo_proveedor")[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalNuevoPropietario_aa"
        );
      }
    },
    error: function () {},
  });
}

////BENEFICIARIOS
function sec_con_nuevo_aden_arrend_modal_nuevo_beneficiario(
  tipo,
  id_beneficiario,
  contrato_detalle_id
) {
  $("#modal_beneficiario_id_beneficiario_para_cambios").val(id_beneficiario);
  $("#modal_beneficiario_tipo_solicitud").val(tipo);
  if (tipo == "NuevoBeneficiario") {
    $("#modal_beneficiario_titulo").html("Nuevo Beneficiario");
  }
  if (tipo == "CambiarBeneficiario") {
    $("#modal_beneficiario_titulo").html("Cambiar Beneficiario");
  }
  $("#modalNuevoBeneficiario").modal({ backdrop: "static", keyboard: false });
  sec_con_nuevo_aden_arrend_modal_resetear_formulario_nuevo_beneficiario("new");
  $("#modal_beneficiario_contrato_detalle_id").val(contrato_detalle_id);
  setTimeout(function () {
    $("#modal_beneficiario_nombre").focus();
  }, 200);
}

function sec_con_nuevo_aden_arrend_modal_resetear_formulario_nuevo_beneficiario(
  evento
) {
  $("#frmNuevoBeneficiario")[0].reset();
  $("#div_modal_beneficiario_nombre_banco").hide();
  $("#div_modal_beneficiario_numero_cuenta_bancaria").hide();
  $("#div_modal_beneficiario_numero_CCI").hide();
  $("#div_modal_beneficiario_monto").hide();
  $("#div_modal_beneficiario_mensaje").hide();

  $("#modal_beneficiario_tipo_persona").trigger("change");
  $("#modal_beneficiario_tipo_docu").trigger("change");
  $("#modal_beneficiario_id_forma_pago").trigger("change");
  $("#modal_beneficiario_tipo_monto").trigger("change");
}

function sec_con_nuevo_aden_arrend_guardar_beneficiario() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var id_beneficiario_para_cambios = $(
    "#modal_beneficiario_id_beneficiario_para_cambios"
  ).val();
  var tipo_solicitud = $("#modal_beneficiario_tipo_solicitud").val();
  var contrato_detalle_id = $("#modal_beneficiario_contrato_detalle_id").val();
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
    accion: "guardar_beneficiario",
    contrato_id: contrato_id,
    id_beneficiario_para_cambios: id_beneficiario_para_cambios,
    contrato_detalle_id: contrato_detalle_id,
    tipo_solicitud: tipo_solicitud,
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

  auditoria_send({ proceso: "guardar_beneficiario", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
        respuesta: "guardar_adenda_detalle_nuevos_registros",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        $("#frmNuevoBeneficiario")[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalNuevoBeneficiario"
        );
        sec_con_nuevo_aden_arrend_modal_resetear_formulario_nuevo_beneficiario();
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_modal_eliminar_beneficiario(
  id_beneficiario,
  contrato_detalle_id
) {
  var id_actual = id_beneficiario;

  var nombre_tabla = "cont_beneficiarios";
  var nombre_campo = "status";
  var nombre_menu_usuario = "Beneficiario";
  var nombre_campo_usuario = "Eliminar Beneficiario";

  var data = {
    accion: "guardar_adenda_detalle",
    contrato_detalle_id: contrato_detalle_id,
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: id_actual,
    tipo_valor: "eliminar",
    valor_varchar: "",
    valor_int: id_actual,
    valor_date: "",
    valor_decimal: "",
    valor_select_option: "",
    valor_id_tabla: "",
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalBuscarPropietario_ca"
        );
        // $('#modal_adenda').modal('hide');
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

////BENEFICIARIOS
function sec_con_nuevo_aden_arrend_modal_nuevo_responsable_ir(
  tipo,
  id_responsable_ir,
  contrato_detalle_id
) {
  $("#modal_responsable_ir_id_responsable_ir_para_cambios").val(
    id_responsable_ir
  );
  $("#modal_responsable_ir_tipo_solicitud").val(tipo);
  if (tipo == "NuevoResponsableIR") {
    $("#modal_responsable_ir_titulo").html("Nuevo Responsable IR");
  }
  if (tipo == "CambiarResponsableIR") {
    $("#modal_responsable_ir_titulo").html("Cambiar Responsable IR");
  }
  $("#modalNuevoResponsableIR").modal({ backdrop: "static", keyboard: false });
  sec_con_nuevo_aden_arrend_modal_resetear_formulario_nuevo_responsable_ir(
    "new"
  );
  $("#modal_responsable_ir_contrato_detalle_id").val(contrato_detalle_id);
  setTimeout(function () {
    $("#modal_responsable_ir_nro_documento").focus();
  }, 200);
}

function sec_con_nuevo_aden_arrend_modal_resetear_formulario_nuevo_responsable_ir(
  evento
) {
  $("#frmNuevoResponsableIR")[0].reset();
  $("#modal_responsable_ir_tipo_docu").val("2").trigger("change");
  $("#modal_responsable_ir_nro_documento").val("");
  $("#modal_responsable_ir_nombre").val("");
  $("#modal_responsable_ir_num_porcentaje").val("");
}

function sec_con_nuevo_aden_arrend_guardar_responsable_ir() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var id_responsable_ir_para_cambios = $(
    "#modal_responsable_ir_id_responsable_ir_para_cambios"
  ).val();
  var tipo_solicitud = $("#modal_responsable_ir_tipo_solicitud").val();
  var contrato_detalle_id = $(
    "#modal_responsable_ir_contrato_detalle_id"
  ).val();
  var tipo_docu = $("#modal_responsable_ir_tipo_docu").val();
  var num_docu = $("#modal_responsable_ir_nro_documento").val().trim();
  var nombre = $("#modal_responsable_ir_nombre").val().trim();
  var num_porcentaje = $("#modal_responsable_ir_num_porcentaje").val().trim();

  if (parseInt(tipo_docu) == 0) {
    alertify.error("Seleccione el tipo de documento de identidad", 5);
    $("#modal_responsable_ir_tipo_docu").focus();
    return false;
  }

  if (num_docu.length == 0) {
    alertify.error("Ingrese el Número de Documento de Identidad", 5);
    $("#modal_responsable_ir_nro_documento").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 2 && num_docu.length != 11) {
    alertify.error(
      "El número de RUC posee 11 dígitos, no " + num_docu.length + " dígitos",
      5
    );
    $("#modal_responsable_ir_nro_documento").focus();
    return false;
  }

  if (nombre.length < 6) {
    alertify.error("Ingrese el nombre completo", 5);
    $("#modal_responsable_ir_nombre").focus();
    return false;
  }

  if (num_porcentaje.length == 0) {
    alertify.error("Ingrese un porcentaje", 5);
    $("#modal_responsable_ir_num_porcentaje").focus();
    return false;
  }

  var data = {
    accion: "guardar_responsable_ir",
    contrato_id: contrato_id,
    id_responsable_ir_para_cambios: id_responsable_ir_para_cambios,
    contrato_detalle_id: contrato_detalle_id,
    tipo_solicitud: tipo_solicitud,
    tipo_docu: tipo_docu,
    num_docu: num_docu,
    nombre: nombre,
    num_porcentaje: num_porcentaje,
  };

  auditoria_send({ proceso: "guardar_responsable_ir", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
        respuesta: "guardar_adenda_detalle_nuevos_registros",
        data: respuesta,
      });

      if (parseInt(respuesta.http_code) == 200) {
        $("#frmNuevoBeneficiario")[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalNuevoResponsableIR"
        );
        sec_con_nuevo_aden_arrend_modal_resetear_formulario_nuevo_responsable_ir(
          "new"
        );
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_modal_eliminar_responsable_ir(
  id_responsable_ir,
  contrato_detalle_id
) {
  var id_actual = id_responsable_ir;

  var nombre_tabla = "cont_responsable_ir";
  var nombre_campo = "status";
  var nombre_menu_usuario = "Responsable IR";
  var nombre_campo_usuario = "Eliminar Responsable IR";

  var data = {
    accion: "guardar_adenda_detalle",
    contrato_detalle_id: contrato_detalle_id,
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: id_actual,
    tipo_valor: "eliminar",
    valor_varchar: "",
    valor_int: id_actual,
    valor_date: "",
    valor_decimal: "",
    valor_select_option: "",
    valor_id_tabla: "",
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          ""
        );
        // $('#modal_adenda').modal('hide');
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

// INICIO INCREMENTOS
function sec_con_nuevo_aden_arrend_modal_agregar_incrementos(
  contrato_detalle_id
) {
  sec_con_nuevo_aden_arrend_resetear_formulario_nuevo_incremento("new");
  $("#modal_adenda_agregar_incrementos").modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_adenda_contrato_detalle_id").val(contrato_detalle_id);
  setTimeout(function () {
    $("#contrato_adenda_incrementos_monto_o_porcentaje").focus();
  }, 500);
}

function sec_con_nuevo_aden_arrend_resetear_formulario_nuevo_incremento(
  evento
) {
  $("#frm_adenda_incremento")[0].reset();
  $("#contrato_adenda_incrementos_en").val("0").trigger("change");
  $("#contrato_adenda_incrementos_continuidad").val("0").trigger("change");
  $("#contrato_adenda_incrementos_a_partir_de_año").val("0").trigger("change");

  if (evento == "new") {
    $("#modal_adenda_incremento_titulo").html("Registrar Incremento");
    $("#btn_adenda_agregar_incremento").show();
    $("#btn_adenda_guardar_cambios_incremento").hide();
  } else if (evento == "edit") {
    $("#modal_adenda_incremento_titulo").html("Editar Incremento");
    $("#btn_adenda_agregar_incremento").hide();
    $("#btn_adenda_guardar_cambios_incremento").show();
  }

  setTimeout(function () {
    $("#contrato_adenda_incrementos_en").select2("close");
    $("#contrato_adenda_incrementos_continuidad").select2("close");
    $("#contrato_adenda_incrementos_a_partir_de_año").select2("close");
  }, 200);
}

function sec_con_nuevo_aden_arrend_solicitud_guardar_incremento() {
  var accion = "guardar_incremento_adenda";
  var contrato_detalle_id = $("#modal_adenda_contrato_detalle_id").val();
  var contrato_id = $("#id_registro_contrato_id").val();
  var incremento_monto_o_porcentaje = $(
    "#contrato_adenda_incrementos_monto_o_porcentaje"
  ).val();
  var incrementos_en = $("#contrato_adenda_incrementos_en").val();
  var incrementos_continuidad = $("#contrato_adenda_incrementos_continuidad")
    .val()
    .trim();
  var incrementos_a_partir_de_año = $(
    "#contrato_adenda_incrementos_a_partir_de_año"
  ).val();

  var incrementos_continuidad_text = "";
  var data = $("#contrato_adenda_incrementos_continuidad").select2("data");
  if (data) {
    incrementos_continuidad_text = data[0].text;
  }

  var incrementos_a_partir_de_año_text = "";
  var data = $("#contrato_adenda_incrementos_a_partir_de_año").select2("data");
  if (data) {
    incrementos_a_partir_de_año_text = data[0].text;
  }

  if (incremento_monto_o_porcentaje.length < 1) {
    alertify.error("Ingrese el valor", 5);
    $("#contrato_adenda_incrementos_monto_o_porcentaje").focus();
    return false;
  }

  if (parseInt(incrementos_en) == 0) {
    alertify.error("Seleccione el tipo de valor", 5);
    $("#contrato_adenda_incrementos_en").focus();
    return false;
  }

  if (
    parseInt(incrementos_en) == 2 &&
    incremento_monto_o_porcentaje.length > 5
  ) {
    alertify.error("El incremento no puede ser mayor al 100%", 5);
    $("#contrato_adenda_incrementos_en").focus();
    return false;
  }

  if (parseInt(incrementos_continuidad) == 0) {
    alertify.error("Seleccione el tipo de continuidad", 5);
    $("#contrato_adenda_incrementos_continuidad").focus();
    return false;
  }

  if (
    parseInt(incrementos_a_partir_de_año) == 0 &&
    parseInt(incrementos_continuidad) != 3
  ) {
    alertify.error("Seleccione el año del inicio del incremento", 5);
    $("#contrato_adenda_incrementos_a_partir_de_año").focus();
    return false;
  }

  var data = {
    accion: accion,
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    incremento_monto_o_porcentaje: incremento_monto_o_porcentaje,
    incrementos_en: incrementos_en,
    incrementos_continuidad: incrementos_continuidad,
    incrementos_a_partir_de_año: incrementos_a_partir_de_año,
    incrementos_continuidad_text: incrementos_continuidad_text,
    incrementos_a_partir_de_año_text: incrementos_a_partir_de_año_text,
  };

  auditoria_send({ proceso: accion, data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ proceso: accion, data: respuesta });

      if (parseInt(respuesta.http_code) == 400) {
        swal("Aviso", respuesta.error, "warning");
        return false;
      }

      if (parseInt(respuesta.http_code) == 200) {
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modal_adenda_agregar_incrementos"
        );
      }
    },
    error: function () {},
  });
}

// FIN INCREMENTOS

// INICIO ENMIENDA
function sec_con_nuevo_aden_arrend_solicitud_guardar_enmienda() {}
// FIN ENMIENDA

// INICIO DE FUNCION DE IMPORTACION
function sec_con_nuevo_aden_arrend_importar_contratos() {
  var data = {
    accion: "contrato_proveedor",
  };
  $.ajax({
    url: "/sys/set_contrato_nuevo_importacion.php",
    type: "POST",
    data: data,
    beforeSend: function () {},
    complete: function () {},
    success: function (response) {
      //  alert(datat)
      console.log(response);
    },
    error: function () {},
  });
}
// FIN DE FUNCION DE IMPORTACION

// INICIO ENMIENDA
function sec_con_nuevo_aden_arrend_modal_agregar_enmienda() {
  $("#modal_adenda_agregar_enmienda").modal({
    backdrop: "static",
    keyboard: false,
  });
}

/// INICIO INFLACION
function sec_con_nuevo_aden_arrend_modal_agregar_inflacion(
  contrato_detalle_id
) {
  $("#modal_inflacion_titulo").html("Registrar Inflación");
  $("#btn_modal_if_agregar_agregar").show();
  $("#btn_modal_if_agregar_editar").hide();

  $("#modal_if_tipo_periodicidad_id").val("0").trigger("change");
  $("#modal_if_numero").val("");
  $("#modal_if_tipo_anio_mes").val("0").trigger("change");
  $("#modal_if_porcentaje_anadido").val("");
  $("#modal_if_tope_inflacion").val("");
  $("#modal_if_minimo_inflacion").val("");
  $("#modal_if_aplicacion_id").val("0").trigger("change");
  $("#modal_if_contrato_detalle_id").val(contrato_detalle_id);
  $("#modalAgregarInflacion").modal({ backdrop: "static", keyboard: false });
  setTimeout(function () {
    // $("#contrato_incrementos_monto_o_porcentaje").focus();
  }, 500);
}

function sec_con_nuevo_aden_arrend_agregar_inflacion() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var contrato_detalle_id = $("#modal_if_contrato_detalle_id").val();
  var tipo_periodicidad_id = $("#modal_if_tipo_periodicidad_id").val();
  var fecha = $("#modal_if_fecha").val();
  var numero = $("#modal_if_numero").val();
  var tipo_anio_mes = $("#modal_if_tipo_anio_mes").val();
  var porcentaje_anadido = $("#modal_if_porcentaje_anadido").val();
  var tope_inflacion = $("#modal_if_tope_inflacion").val();
  var minimo_inflacion = $("#modal_if_minimo_inflacion").val();
  var aplicacion_id = $("#modal_if_aplicacion_id").val();

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

  if (fecha == "") {
    alertify.error("Seleccione una fecha de ajuste", 5);
    $("#modal_if_fecha").focus();
    return false;
  }

  if (aplicacion_id == "" || aplicacion_id == "0") {
    alertify.error("Seleccione un tipo de aplicación", 5);
    $("#modal_if_aplicacion_id").select2("open");
    return false;
  }

  var accion = "guardar_inflacion";
  var data = {
    contrato_id: contrato_id,
    contrato_detalle_id: contrato_detalle_id,
    accion: accion,
    fecha: fecha,
    tipo_periodicidad_id: tipo_periodicidad_id,
    numero: numero,
    tipo_anio_mes: tipo_anio_mes,
    porcentaje_anadido: porcentaje_anadido,
    tope_inflacion: tope_inflacion,
    minimo_inflacion: minimo_inflacion,
    aplicacion_id: aplicacion_id,
  };
  auditoria_send({ proceso: "guardar_inflacion", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalAgregarInflacion"
        );
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_eliminar_inflacion(
  inflacion_id,
  contrato_detalle_id
) {
  var id_actual = inflacion_id;
  var nombre_tabla = "cont_inflaciones";
  var nombre_campo = "status";
  var nombre_menu_usuario = "Inflación";
  var nombre_campo_usuario = "Eliminar Inflación";

  var data = {
    accion: "guardar_adenda_detalle",
    contrato_detalle_id: contrato_detalle_id,
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: id_actual,
    tipo_valor: "eliminar",
    valor_varchar: "",
    valor_int: id_actual,
    valor_date: "",
    valor_decimal: "",
    valor_select_option: "",
    valor_id_tabla: "",
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalAgregarInflacion"
        );
        // $('#modal_adenda').modal('hide');
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}
// FINAL INFLACION

/// INICIO CUOTA EXTRAORDINARIA
function sec_con_nuevo_aden_arrend_modal_agregar_cuota_extraordinaria(
  contrato_detalle_id
) {
  $("#modal_cuota_extraordinaria_titulo").html(
    "Registrar Cuota Extraordinaria"
  );
  $("#btn_modal_ce_agregar_agregar").show();
  $("#btn_modal_ce_agregar_editar").hide();

  $("#modal_ce_mes").val("0").trigger("change");
  $("#modal_ce_multiplicador").val("");
  $("#modal_ce_contrato_detalle_id").val(contrato_detalle_id);
  $("#modalAgregarCuotaExtraordinaria").modal({
    backdrop: "static",
    keyboard: false,
  });
  setTimeout(function () {
    $("#modal_ce_mes").select2("open");
  }, 500);
}

function sec_con_nuevo_aden_arrend_agregar_cuota_extraordinaria() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var contrato_detalle_id = $("#modal_ce_contrato_detalle_id").val();
  var fecha = $("#modal_ce_fecha").val();
  var mes = $("#modal_ce_mes").val();
  var multiplicador = $("#modal_ce_multiplicador").val();
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

  if (fecha == "") {
    alertify.error("Seleccione una fecha de ajuste", 5);
    $("#modal_ce_fecha").focus();
    return false;
  }

  var accion = "guardar_cuota_extraordinaria";
  var data = {
    accion: accion,
    contrato_detalle_id: contrato_detalle_id,
    contrato_id: contrato_id,
    fecha: fecha,
    mes: mes,
    multiplicador: multiplicador,
  };
  auditoria_send({ proceso: "guardar_cuota_extraordinaria", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalAgregarCuotaExtraordinaria"
        );
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_eliminar_cuota_extraordinaria(
  cuota_extraordinaria_id,
  contrato_detalle_id
) {
  var id_actual = cuota_extraordinaria_id;

  var nombre_tabla = "cont_cuotas_extraordinarias";
  var nombre_campo = "status";
  var nombre_menu_usuario = "Cuota Extraordinaria";
  var nombre_campo_usuario = "Eliminar Cuota Extraordinaria";

  var data = {
    accion: "guardar_adenda_detalle",
    contrato_detalle_id: contrato_detalle_id,
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: id_actual,
    tipo_valor: "eliminar",
    valor_varchar: "",
    valor_int: id_actual,
    valor_date: "",
    valor_decimal: "",
    valor_select_option: "",
    valor_id_tabla: "",
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          "modalAgregarCuotaExtraordinaria"
        );
        // $('#modal_adenda').modal('hide');
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

// FINAL CUOTA EXTRAORDINARIA

/// INICIO SUMINSITROS
function sec_con_nuevo_aden_arrend_modal_agregar_suministro(
  tipo_servicio,
  inmueble_id,
  contrato_detalle_id
) {
  if (tipo_servicio == 1) {
    $("#modal_suministro_titulo").html("Registrar Suministro Luz");
  } else if (tipo_servicio == 2) {
    $("#modal_suministro_titulo").html("Registrar Suministro Agua");
  }

  $("#div_modal_suministro_monto_porcentaje").hide();

  $("#modal_suministro_compromiso_pago").val("0").trigger("change");
  $("#modal_suministro_nro_suministro").val("");
  $("#modal_suministro_numero").val("");
  $("#modal_suministro_tipo_servicio").val(tipo_servicio);
  $("#modal_suministro_inmueble_id").val(inmueble_id);
  $("#modal_suministro_contrato_detalle_id").val(contrato_detalle_id);

  $("#modalNuevoSuministro").modal({ backdrop: "static", keyboard: false });
  setTimeout(function () {
    $("#modal_suministro_nro_suministro").focus();
  }, 500);
}

function sec_con_nuevo_aden_arrend_modal_change_tipo_compromiso() {
  var tipo_compromiso = $("#modal_suministro_compromiso_pago").val();
  if (
    tipo_compromiso == 1 ||
    tipo_compromiso == 2 ||
    tipo_compromiso == 6 ||
    tipo_compromiso == 7
  ) {
    $("#div_modal_suministro_monto_porcentaje").show();

    $("#modal_suministro_numero").on({
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

    if (tipo_compromiso == 1) {
      $("#modal_suministro_numero").unmask();
      $("#modal_suministro_numero").mask("00");
    }
  } else {
    $("#div_modal_suministro_monto_porcentaje").hide();
    $("#modal_suministro_numero").val("");
  }
}

function sec_con_nuevo_aden_arrend_agregar_suministro() {
  var contrato_id = $("#id_registro_contrato_id").val();
  var tipo_servicio = $("#modal_suministro_tipo_servicio").val();
  var inmueble_id = $("#modal_suministro_inmueble_id").val();
  var contrato_detalle_id = $("#modal_suministro_contrato_detalle_id").val();
  var nro_suministro = $("#modal_suministro_nro_suministro").val();
  var compromiso_pago = $("#modal_suministro_compromiso_pago").val();
  var monto_porcentaje = $("#modal_suministro_monto_porcentaje").val();

  if (nro_suministro == "") {
    alertify.error("Ingrese un nro de suminsitro", 5);
    $("#modal_suministro_nro_suministro").focus();
    return false;
  }

  if (compromiso_pago == "" || compromiso_pago == "0") {
    alertify.error("Seleccione un compromiso de pago", 5);
    $("#modal_suministro_compromiso_pago").select2("open");
    return false;
  }

  if (parseInt(compromiso_pago) == 1 && monto_porcentaje.length == 0) {
    alertify.error("Ingrese el porcentaje del pago del servicio", 5);
    $("#modal_suministro_monto_porcentaje").focus();
    return false;
  }
  if (parseInt(compromiso_pago) == 2 && monto_porcentaje.length == 0) {
    alertify.error("Ingrese el monto fijo del pago del servicio", 5);
    $("#modal_suministro_monto_porcentaje").focus();
    return false;
  }

  var accion = "guardar_suministro";
  var data = {
    accion: accion,
    contrato_id: contrato_id,
    tipo_servicio: tipo_servicio,
    inmueble_id: inmueble_id,
    contrato_detalle_id: contrato_detalle_id,
    nro_suministro: nro_suministro,
    compromiso_pago: compromiso_pago,
    monto_porcentaje: monto_porcentaje,
  };
  auditoria_send({ proceso: "guardar_suministro", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ proceso: "guardar_suministro", data: respuesta });
      if (parseInt(respuesta.http_code) == 200) {
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          ""
        );
        $("#modalNuevoSuministro").modal("hide");
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_con_nuevo_aden_arrend_eliminar_suministro(
  suministro_id,
  contrato_detalle_id
) {
  var id_actual = suministro_id;

  var nombre_tabla = "cont_inmueble_suministros";
  var nombre_campo = "status";
  var nombre_menu_usuario = "Suministro";
  var nombre_campo_usuario = "Eliminar Suministro";

  var data = {
    accion: "guardar_adenda_detalle",
    contrato_detalle_id: contrato_detalle_id,
    nombre_tabla: nombre_tabla,
    nombre_campo: nombre_campo,
    nombre_menu_usuario: nombre_menu_usuario,
    nombre_campo_usuario: nombre_campo_usuario,
    valor_original: id_actual,
    tipo_valor: "eliminar",
    valor_varchar: "",
    valor_int: id_actual,
    valor_date: "",
    valor_decimal: "",
    valor_select_option: "",
    valor_id_tabla: "",
  };

  auditoria_send({ proceso: "guardar_adenda_detalle", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_adenda_locacion_servicio.php",
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
      auditoria_send({ respuesta: "guardar_adenda_detalle", data: respuesta });
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        // $('#frm_incremento')[0].reset();
        sec_con_nuevo_aden_arrend_asignar_detalle_a_la_adenda(
          respuesta.result,
          ""
        );
        document.getElementById("divTablaAdendas").focus();
        return false;
      }
    },
    error: function () {},
  });
}

// FINAL CUOTA EXTRAORDINARIA

function sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(id_adenda) {
  const index = array_adendas_contrato.indexOf(id_adenda);
  if (index > -1) {
    array_adendas_contrato.splice(index, 1);
  }
  sec_con_nuevo_aden_arrend_actualizar_tabla_detalle_adenda();
}

var array_nuevos_files_anexos = [];
function sec_con_nuevo_aden_anadir_archivo_arrendamiento() {
  var tipo_archivo_id = $("#modal_nuevo_select_archivo_anexo").val();
  var contrato_detalle_id = $("#modal_nuevo_adenda_detalle_contrato_id").val();

  if (tipo_archivo_id.length == 0 || parseFloat(tipo_archivo_id) == 0) {
    alertify.error("Seleccione un tipo de anexo", 5);
    $("#modal_nuevo_select_archivo_anexo").select2("open");
    return false;
  }
  var tipo_archivo_nombre = $("#modal_nuevo_select_archivo_anexo")
    .find("option:selected")
    .text();
  var tipo_contrato_id = 2; //$("#modal_nuevo_anexo_tipo_contrato_id").val();

  //Sumamos a la variable el número de archivos.
  contArchivos = contArchivos + 1;
  //Agregamos el componente de tipo input
  var div = document.createElement("div");
  var input = document.createElement("input");
  var a = document.createElement("a");

  //Añadimos los atributos de div
  div.id = "archivo_" + contArchivos;

  //Añadimos los atributos de input
  input.type = "file";
  input.name = "new_anexo_" + tipo_archivo_id;

  //Añadimos los atributos del enlace a eliminar
  a.href = "#";
  a.id = "archivo" + contArchivos;
  a.onclick = function () {
    borrarArchivo(a.id);
  };
  a.text = "X Eliminar archivo";

  //TIPO DE ARCHIVO SELECCIONADO

  var hoy = new Date();
  var fecha =
    hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
  var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
  var milisegundos = hoy.getMilliseconds();
  var Tiempo = fecha + "" + hora + "" + milisegundos;
  id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + +Tiempo;
  var name_file =
    tipo_archivo_id + "_" + contrato_detalle_id + "_anexo" + Tiempo;
  //var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

  var onclick =
    "borrarArchivo_proveedor('" + id_nuevo_objeto_nuevo_anexo + "')";

  var html = "";
  html +=
    '<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
    id_nuevo_objeto_nuevo_anexo +
    '">';
  html += '<div class="form-group">';
  html += '<div class="control-label">';
  html += tipo_archivo_nombre + ": ";
  html += "</div>";
  var onchange = "file_proveedor(event,'" + id_nuevo_objeto_nuevo_anexo + "')";
  html += '<div style="margin-top:10px;">';
  html +=
    '<input name="' +
    name_file +
    '" type="file" id="' +
    id_nuevo_objeto_nuevo_anexo +
    '" class="col-md-11" onchange="' +
    onchange +
    '" style="padding: 0px 0px;"/>';
  html +=
    '<button class="btn btn-xs btn-danger col-md-1" style="width: 22px;" onclick="' +
    onclick +
    '"><i class="fa fa-trash-o"></i></button>';
  html += "</div>";
  html += "</div>";
  html += "</div>";

  $("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item

  // $("#modaltiposanexos").modal("hide");
}

function borrarArchivo(id_anexo) {
  //Restamos el número de archivos
  contArchivos = contArchivos - 1;

  array_nuevos_files_anexos = array_nuevos_files_anexos.filter(
    (item) => item.id_objeto !== id_anexo
  );
  $("div[name=" + id_anexo + "]").remove();
}

function file_adenda(event, id) {
  var id_ = "#" + id;
  // var id_tip_documento = idtd;
  let file = $(id_)[0].files[0];
  var nombre_archivo = file.name;
  var tamano_archivo = file.size;
  var extension = $(id_).val().replace(/^.*\./, "");

  var objeto = {
    id_objeto: id,
    nombre_archivo: nombre_archivo,
    tamano_archivo: tamano_archivo,
    extension: extension,
    // id_tip_documento: id_tip_documento,
    // tip_doc_nombre: tdnombre,
  };

  array_nuevos_files_anexos.push(objeto);

  console.log(array_nuevos_files_anexos);
}

function sec_modal_adenda_arrendamiento_nuevo_archivo_anexo(
  tipo_contrato_id,
  contrato_detalle_id
) {
  $("#modalNuevoArchivoAnexo").modal("show");
  $("#modal_nuevo_adenda_detalle_contrato_id").val(contrato_detalle_id);
  var data = {
    accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
    tipo_contrato_id: tipo_contrato_id,
  };
  $("#modal_nuevo_select_archivo_anexo").html("");
  $("#modal_nuevo_select_archivo_anexo").append(
    '<option value="0">- Seleccione una opcion -</option>'
  );
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
          $("#modal_nuevo_select_archivo_anexo").append(
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
