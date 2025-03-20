// INICIO DECLARACION DE VARIABLES ARRAY
var array_propietarios_contrato = [];
var array_inmuebles_contrato = [];
var array_incrementos_contrato = [];
var array_beneficiarios_contrato = [];
var array_adelantos_contrato = [];
var array_adendas_contrato = [];
// FIN DECLARACION DE VARIABLES ARRAY

// INICIO FUNCIONES CONTRATO DE PROVEEDOR
function sec_contrato_nuevo_agente() {
  // INICIO INICIALIZACION DE D Contratos - Nueva Solicitud xATEPICKER
 

  setTimeout(function () {
    $("#tipo_contrato_id").val("6").trigger('change');

  }, 200);

 $(".fecha_datepicker_ag")
  .datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
  })
  .on("change", function (ev) {
    $(this).datepicker("hide");
    var newDate = $(this).datepicker("getDate");
    $("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
    // localStorage.setItem($(this).atrr("id"),)
  });


  $(".mask_centro_costos").mask("0000");
  $(".mask_dni_agente").mask("000000000000");
  $(".mask_ruc_agente").mask("00000000000");
  $(".mask_telefono_agente").mask("000000000");
  

  $(".formato_porcentaje").mask("00");
  // INICIO CARGAR SELECT OPTION
  sec_contrato_nuevo_agente_obtener_opciones("obtener_tipo_contrato_agente", $("[name='tipo_contrato_id']"));
  sec_contrato_nuevo_agente_obtener_opciones("obtener_empresa_at", $("[name='empresa_suscribe_id']"));
  sec_contrato_nuevo_agente_obtener_opciones("obtener_personal_responsable_agente", $("[name='personal_responsable_id']"));
  sec_contrato_nuevo_agente_obtener_opciones("obtener_departamentos", $("[name='modal_inmueble_id_departamento']"));
  sec_contrato_nuevo_agente_obtener_opciones("obtener_departamentos", $("[name='modal_inmueble_id_departamento_ca']"));
  sec_contrato_nuevo_agente_obtener_opciones("obtener_agente_cc_lc", $("[name='nombre_ag_lc']"));
  sec_contrato_nuevo_agente_obtener_opciones("obtener_empresa_agente", $("[name='empresa_suscribe_ag']"));
  sec_contrato_nuevo_obtener_opciones("obtener_directores", $("[name='aprobador_id']"));
  sec_contrato_nuevo_obtener_opciones("obtener_cargos", $("[name='cargo_aprobador_id']"));
  // FIN CARGAR SELECT OPTION
  
 

  // INICIO CHANGE DATOS GENERALES DEL CONTRATO
 
    
   
        // Contrato de agente
        $("#div_detalle_solicitud_proveedor_izquierda").hide();
        $("#div_detalle_solicitud_izquierda").hide();
        $("#div_detalle_solicitud_derecha").hide();
        $("#divContratoArrendamiento").hide();
        $("#divContratoProveedor").hide();
        $("#divAcuerdoDeConfidencialidad").hide();
        $("#divContratoAgente").show();
        $("#div_personal_responsable").show();
        $("#div_empresa_suscribe").hide();
        $("#div_nombre_tienda").hide();
        $("#div_nombre_proveedor").hide();
        $("#div_fecha_contrato_proveedor").hide();
        $("#div_correos_adicionales").show();
        $("#div_nombre_local").show();
        $("#div_centro_costos").show(); 
        $("#porcentaje_participacion_bet").val("65");
        $("#porcentaje_participacion_j").val("50");
        $("#porcentaje_participacion_ter").val("50");
        $("#porcentaje_participacion_bin").val("5");
        $("#porcentaje_participacion_dep").val("0");
        $("#porcentaje_participacion_ccv").val("55");
        $("#periodo_numero_ca").val("2");
       

        sec_contrato_nuevo_agente_actualizar_tabla_propietarios_agente();
       // sec_contrato_nuevo_agente_actualizar_tabla_beneficiario_ca();

        setTimeout(function () {
          $("#personal_responsable_id").val("1137").trigger('change');
     
        }, 1500);
        
        $("#area_responsable_id").change(function () {
          $("#area_responsable_id option:selected").each(function () {
            area_responsable_id = $(this).val();
            var data = {
              accion: "obtener_personal_segun_area",
              area_id: area_responsable_id,
            };
            var array_personal = [];
            auditoria_send({ proceso: "obtener_personal_segun_area", data: data });
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
                  array_personal.push(respuesta.result);
                  
      
                  for (var i = 0; i < array_personal[0].length; i++) {
                    html += "<option value=" + array_personal[0][i].id + ">" + array_personal[0][i].nombre_completo + "</option>";
                  }
       
      
                  $("#personal_responsable_id").html(html).trigger("change");
      
                  return false;
                }
              },
              error: function () {},
            });
      
            if (area_responsable_id != 0) {
              setTimeout(function () {
                $("#personal_responsable_id").select2("open");
              }, 200);
            }
          });
        });
        $("#personal_responsable_id").change(function () {
          $("#personal_responsable_id option:selected").each(function () {
            personal_responsable_id = $(this).val();
            tipo_contrato_id = $("#tipo_contrato_id").val();
            if (tipo_contrato_id == 1) {
              if (personal_responsable_id != 0 && array_propietarios_contrato.length == 0) {
                setTimeout(function () {
                  sec_contrato_nuevo_agente_buscar_propietario_modal("arrendamiento");
                }, 500);
                setTimeout(function () {
                  $("#modal_propietario_nombre_o_numdocu").focus();
                }, 700);
              }
            }
          });
        });
 
 
  // FIN CHANGE DATOS GENERALES DEL CONTRATOmodal_propietario_tipo_busqueda

  // INICIO CHANGE PROPIETARIOS
  $("#modal_propietario_tipo_busqueda").change(function () {
    $("#modal_propietario_tipo_busqueda option:selected").each(function () {
      tipo_busqueda = $(this).val();
      if (tipo_busqueda == 1) {
        $("#modal_propietario_nombre_o_numdocu").attr("placeholder", "Ingrese el nombre, despues los apellidos");
      } else if (tipo_busqueda == 2) {
        $("#modal_propietario_nombre_o_numdocu").attr("placeholder", "Ingrese el número de documento");
      }

      setTimeout(function () {
        $("#modal_propietario_nombre_o_numdocu").focus();
      }, 100);
    });
  });

  

  $("#modal_propietario_tipo_persona_ca").change(function () {
    $("#modal_propietario_tipo_persona_ca option:selected").each(function () {
      tipo_persona = $(this).val();
      if (tipo_persona == 1) {
        $("#modal_propietario_tipo_docu_ca").val("1");
        $("#div_modal_propietario_representante_legal_ca").hide();
        $("#div_modal_propietario_num_partida_registral_ca").hide();
      } else if (tipo_persona == 2) {
        $("#modal_propietario_tipo_docu_ca").val("2");
        $("#div_modal_propietario_representante_legal_ca").show();
        $("#div_modal_propietario_num_partida_registral_ca").show();
      }
      $("#modal_propietario_tipo_docu_ca").change();
      setTimeout(function () {
        $("#modal_propietario_nombre").focus();
      }, 200);
    });
  });

  

  $("#modal_propietario_tipo_docu_ca").change(function () {
    $("#modal_propietario_tipo_docu_ca option:selected").each(function () {
      propietario_tipo_docu = $(this).val();
      if (propietario_tipo_docu == 1 || propietario_tipo_docu == 3 || propietario_tipo_docu == 4) {
        $("#div_num_docu_propietario_ca").show();

        if (propietario_tipo_docu == 1) {
          $("#label_num_docu_propietario_ca").html("Número de DNI del propietario<span class='campo_obligatorio_v2'>(*)</span>:");
          $(".mask_dni_agente").mask("00000000");
        } else if (propietario_tipo_docu == 3) {
          $("#label_num_docu_propietario_ca").html("Número de Pasaporte del propietario:");
          $(".mask_dni_agente").mask("000000000000");
        } else if (propietario_tipo_docu == 4) {
          $("#label_num_docu_propietario_ca").html("Número de Carnet de Ext. del propietario:");
          $(".mask_dni_agente").mask("000000000000");
        }

        setTimeout(function () {
          $("#modal_propietario_num_docu_ca").focus();
        }, 200);
      } else if (propietario_tipo_docu == 2) {
        $("#div_num_docu_propietario_ca").hide();

        setTimeout(function () {
          $("#modal_propietario_num_ruc_ca").focus();
        }, 200);
      }
    });
  });

 

  $("#modal_propietario_tipo_persona_contacto_ca").change(function () {
    $("#modal_propietario_tipo_persona_contacto_ca option:selected").each(function () {
      tipo_persona_contacto = $(this).val();
      if (tipo_persona_contacto == 1) {
        $("#div_modal_propietario_contacto_nombre_ca").hide();
        $("#modal_propietario_contacto_telefono_ca").focus();
      } else if (tipo_persona_contacto == 2) {
        $("#div_modal_propietario_contacto_nombre_ca").show();
        $("#modal_propietario_contacto_nombre_ca").focus();
      }
    });
  });
  // FIN CHANGE PROPIETARIOS

  // INICIO CHANGE INMUEBLES
  $("#modal_inmueble_area_arrendada").on({
    focus: function (event) {
      $(event.target).select();
    },
    change: function (event) {
      if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
        $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
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

  $("#modal_inmueble_id_departamento").change(function () {
    $("#modal_inmueble_id_departamento option:selected").each(function () {
      modal_inmueble_id_departamento = $(this).val();
      var data = {
        accion: "obtener_provincias_segun_departamento",
        departamento_id: modal_inmueble_id_departamento,
      };
      var array_provincias = [];
      auditoria_send({ proceso: "obtener_provincias_segun_departamento", data: data });
      $.ajax({
        url: "/sys/set_contrato_nuevo_agente.php",
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
              html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
            }

       

            $("#modal_inmueble_id_provincia").html(html).trigger("change");

            setTimeout(function () {
              $("#modal_inmueble_id_provincia").select2("open");
            }, 500);

            return false;
          }
        },
        error: function () {},
      });
    });
  });

  $("#modal_inmueble_id_departamento_ca").change(function () {
    $("#modal_inmueble_id_departamento_ca option:selected").each(function () {
      modal_inmueble_id_departamento = $(this).val();
      var data = {
        accion: "obtener_provincias_segun_departamento",
        departamento_id: modal_inmueble_id_departamento,
      };
      var array_provincias = [];
      auditoria_send({ proceso: "obtener_provincias_segun_departamento", data: data });
      $.ajax({
        url: "/sys/set_contrato_nuevo_agente.php",
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
              html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
            }

           

            $("#modal_inmueble_id_provincia_ca").html(html).trigger("change");

            setTimeout(function () {
              $("#modal_inmueble_id_provincia_ca").select2("open");
            }, 500);

            return false;
          }
        },
        error: function () {},
      });
    });
  });

  $("#modal_inmueble_id_provincia").change(function () {
    $("#modal_inmueble_id_provincia option:selected").each(function () {
      modal_inmueble_id_provincia = $(this).val();
      modal_inmueble_id_departamento = $("#modal_inmueble_id_departamento").val();
      var data = {
        accion: "obtener_distritos_segun_provincia",
        provincia_id: modal_inmueble_id_provincia,
        departamento_id: modal_inmueble_id_departamento,
      };
      var array_distritos = [];
      auditoria_send({ proceso: "obtener_distritos_segun_provincia", data: data });
      $.ajax({
        url: "/sys/set_contrato_nuevo_agente.php",
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
              html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
            }

          

            $("#modal_inmueble_id_distrito").html(html).trigger("change");

            setTimeout(function () {
              $("#modal_inmueble_id_distrito").select2("open");
            }, 500);

            return false;
          }
        },
        error: function () {},
      });
    });
  });

  $("#modal_inmueble_id_provincia_ca").change(function () {
    $("#modal_inmueble_id_provincia_ca option:selected").each(function () {
      modal_inmueble_id_provincia = $(this).val();
      modal_inmueble_id_departamento = $("#modal_inmueble_id_departamento_ca").val();
      var data = {
        accion: "obtener_distritos_segun_provincia",
        provincia_id: modal_inmueble_id_provincia,
        departamento_id: modal_inmueble_id_departamento,
      };
      var array_distritos = [];
      auditoria_send({ proceso: "obtener_distritos_segun_provincia", data: data });
      $.ajax({
        url: "/sys/set_contrato_nuevo_agente.php",
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
              html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
            }

           

            $("#modal_inmueble_id_distrito_ca").html(html).trigger("change");

            setTimeout(function () {
              $("#modal_inmueble_id_distrito_ca").select2("open");
            }, 500);

            return false;
          }
        },
        error: function () {},
      });
    });
  });

  $("#modal_inmueble_id_distrito").change(function () {
    $("#modal_inmueble_id_distrito option:selected").each(function () {
      inmueble_id_distrito = $(this).val();
      if (inmueble_id_distrito != 0) {
        setTimeout(function () {
          $("#modal_inmueble_ubicacion").focus();
        }, 100);
      }
    });
  });

  $("#modal_inmueble_id_distrito_ca").change(function () {
    $("#modal_inmueble_id_distrito_ca option:selected").each(function () {
      inmueble_id_distrito = $(this).val();
      if (inmueble_id_distrito != 0) {
        setTimeout(function () {
          $("#modal_inmueble_ubicacion_ca").focus();
        }, 100);
      }
    });
  });
     
  // FIN CHANGE INMUEBLES

  
   
  // INICIO CLICK PROPIETARIO
  $("#btnModalNuevoPropietario").click(function () {
    var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud").val();
    if (tipo_solicitud == "adenda") {
      sec_contrato_nuevo_agente_nuevo_propietario_modal("adenda");
    } else {
      sec_contrato_nuevo_agente_nuevo_propietario_modal("arrendamiento");
    }
  });

  $("#btnModalNuevoPropietario_ca").click(function () {
    var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud_ca").val();
    if (tipo_solicitud == "adenda") {
      sec_contrato_nuevo_agente_nuevo_propietario_modal_ca("adenda");
    } else {
      sec_contrato_nuevo_agente_nuevo_propietario_modal_ca("agente");
    }
  });

  

  $("#btn_agregar_propietario_a_la_adenda_ca").click(function () {
    sec_contrato_nuevo_agente_guardar_propietario_agente("guardar_propietario_agente");
  });

  $("#btn_guardar_cambios_propietario").click(function () {
    sec_contrato_nuevo_agente_guardar_cambios_propietario();
  });
  $("#btn_guardar_cambios_propietario_ca").click(function () {
    sec_contrato_nuevo_agente_guardar_cambios_propietario_ca();
  });
  // FIN CLICK PROPIETARIO

  // INICIO CLICK INMUEBLE
  $("#btnModalAgregarInmueble").click(function () {
    $("#modalAgregarInmueble").modal({ backdrop: "static", keyboard: false });
    setTimeout(function () {
      $("#modal_inmueble_id_departamento").select2("open");
    }, 500);
  });

  $("#btnAgregarInmueble").click(function () {
    sec_contrato_nuevo_agente_guardar_inmueble();
  });
  // INICIO CLICK INMUEBLE

  $("#boton_guardar_contrato_agente").click(function () {
    $("#boton_guardar_contrato_agente").hide();
    if (!sec_contrato_nuevo_agente_guardar_contrato_agente()) {
      
      $("#boton_guardar_contrato_agente").show();
    }
  });

  $("#modal_propietario_nombre_o_numdocu").keypress(function (e) {
    if (e.which == 13) {
      event.preventDefault();
      $("#btnBuscarPropietario").click();
    }
  });

 

  $(".select2").select2({ width: "100%" });

   
  cargarBancos();
}

// INICIO FUNCIONES UX


function sec_contrato_nuevo_agente_obtener_opciones(accion, select) {
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
      $(select).append('<option value="0" selected>- Seleccione -</option>');
      $(respuesta.result).each(function (i, e) {
        opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
        $(select).append(opcion);
      });
    },
    error: function () {},
  });
}

function set_contrato_nuevo_agente_filterFloat(evt, input) {
  // Backspace = 8, Enter = 13, '0′ = 48, '9′ = 57, '.' = 46, '-' = 43
  var key = window.Event ? evt.which : evt.keyCode;
  var chark = String.fromCharCode(key);
  var tempValue = input.value + chark;
  if (key >= 48 && key <= 57) {
    if (filter_2(tempValue) === false) {
      return false;
    } else {
      return true;
    }
  } else {
    if (key == 8 || key == 13 || key == 0) {
      return true;
    } else if (key == 46) {
      if (filter_2(tempValue) === false) {
        return false;
      } else {
        return true;
      }
    } else {
      return false;
    }
  }
}
// FIN FUNCIONES UX

 

function sec_contrato_nuevo_agente_buscar_propietario() {
  var array_propietarios = [];
  var nombre_o_numdocu = $.trim($("#modal_propietario_nombre_o_numdocu_ca").val());
  var tipo_busqueda = parseInt($.trim($("#modal_propietario_tipo_busqueda_ca").val()));
  var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud_ca").val();

  if (nombre_o_numdocu.length < 3) {
    var busqueda_por = "";
    if (tipo_busqueda == 1) {
      busqueda_por = "Nombre del Propietario";
    } else if (tipo_busqueda == 2) {
      busqueda_por = "Número de Documento de Identidad";
    }
    alertify.error("El " + busqueda_por + " debe de tener más de dos dígitos", 5);
    $("#modal_propietario_nombre_o_numdocu_ca").focus();
    return;
  }

  var data = {
    accion: "obtener_propietario_ca",
    nombre_o_numdocu: nombre_o_numdocu,
    tipo_busqueda: tipo_busqueda,
    tipo_solicitud: tipo_solicitud,
  };

  auditoria_send({ proceso: "obtener_propietario_ca", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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

function sec_contrato_nuevo_agente_asignar_propietario_al_contrato_agente(idpersona, modal) {
  if (array_propietarios_contrato.includes(idpersona) === false) {
    array_propietarios_contrato.push(idpersona);
  }
 
  if (modal == "modalBuscar") {
    $("#modalBuscarPropietario").modal("hide");
  } else if (modal == "modalNuevo") {
    $("#modalNuevoPropietario").modal("hide");
  }
  sec_contrato_nuevo_agente_actualizar_tabla_propietarios();
}

function sec_contrato_nuevo_agente_asignar_propietario_al_contrato(idpersona, modal) {
  if (array_propietarios_contrato.includes(idpersona) === false) {
    array_propietarios_contrato.push(idpersona);
  }
  
  if (modal == "modalBuscar") {
    $("#modalBuscarPropietario_ca").modal("hide");
  } else if (modal == "modalNuevo") {
    $("#modalNuevoPropietario_ca").modal("hide");
  }
  sec_contrato_nuevo_agente_actualizar_tabla_propietarios_agente();
}

function sec_contrato_nuevo_agente_actualizar_tabla_propietarios() {
  var data = {
    accion: "obtener_propietario",
    nombre_o_numdocu: JSON.stringify(array_propietarios_contrato),
    tipo_busqueda: "3",
    tipo_solicitud: "",
  };

  auditoria_send({ proceso: "obtener_propietario", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
        $("#divTablaPropietarios").html(respuesta.result);
        $("#divNoSeEncontroPropietario").hide();
        $("#divRegistrarNuevoPropietario").show();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_actualizar_tabla_propietarios_agente() {
  var data = {
    accion: "obtener_propietario_ca",
    nombre_o_numdocu: JSON.stringify(array_propietarios_contrato),
    tipo_busqueda: "3",
    tipo_solicitud: "",
  };

  auditoria_send({ proceso: "obtener_propietario_ca", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
        $("#divTablaPropietarios_ca").html(respuesta.result);
        $("#divNoSeEncontroPropietario_ca").hide();
        $("#divRegistrarNuevoPropietario_ca").show();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_guardar_propietario(proceso) {
  var data = sec_contrato_nuevo_agente_validar_campos_formulario_propietario(proceso);

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: proceso, data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: proceso, data: respuesta });
      
      if (parseInt(respuesta.http_code) == 400) {
        // $('#modal_recargaweb').modal('hide');
        // swal('Aviso', respuesta.status, 'warning');
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (proceso == "guardar_propietario_adenda") {
          sec_contrato_nuevo_agente_asignar_otros_detalles_a_la_adenda("propietario", respuesta.result, "modalNuevoPropietario");
        } else {
          sec_contrato_nuevo_agente_asignar_propietario_al_contrato_agente(respuesta.result, "modalNuevo");
        }
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_guardar_propietario_agente(proceso) {
  var data = sec_contrato_nuevo_agente_validar_campos_formulario_propietario_agente(proceso);

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: proceso, data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: proceso, data: respuesta });
      
      if (parseInt(respuesta.http_code) == 400) {
        // $('#modal_recargaweb').modal('hide');
        alertify.error(respuesta.status, 5);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        sec_contrato_nuevo_agente_asignar_propietario_al_contrato(respuesta.result, "modalNuevo");

      
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_eliminar_propietario(id_propietario) {
  
  const index = array_propietarios_contrato.indexOf(id_propietario);
  if (index > -1) {
    array_propietarios_contrato.splice(index, 1);
  }
   
  sec_contrato_nuevo_agente_actualizar_tabla_propietarios();
}

function sec_contrato_nuevo_agente_eliminar_propietario_agente(id_propietario) {
  
  const index = array_propietarios_contrato.indexOf(id_propietario);
  if (index > -1) {
    array_propietarios_contrato.splice(index, 1);
  }
 
  sec_contrato_nuevo_agente_actualizar_tabla_propietarios_agente();
}

function sec_contrato_nuevo_agente_es_email_valido(email) {
  var regex =
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return regex.test(email);
}



function sec_contrato_nuevo_agente_dni_registrado(dni) {
  var nro_dni = dni;  
  var data = {
    accion: "obtener_propietario_existe",
    nombre_o_numdocu: nro_dni,
   
  };

  auditoria_send({ proceso: "obtener_propietario_existe", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: respuesta, data: respuesta });
   
      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
         
        console.log('false');
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {       
        console.log('true');
        
        alertify.error("El número de DNI " + nro_dni + " ya existe,", 5);
        $("#modal_propietario_num_docu_ca").focus();

        return false;
      }
    },
    error: function () {},
  });

   
}
 

function sec_contrato_nuevo_agente_ruc_registrado(ruc) {
  var nro_ruc = ruc;  
  var data = {
    accion: "obtener_propietario_ruc_existe",
    nombre_o_numdocu: nro_ruc,
   
  };

  auditoria_send({ proceso: "obtener_propietario_ruc_existe", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: respuesta, data: respuesta });
   
      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        console.log('false');
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {       
        console.log('true');

        alertify.error("El número de RUC " + nro_ruc + " ya existe,", 5);
        $("#modal_propietario_num_ruc_ca").focus();

        return true;
      }
    },
    error: function () {},
  });

   
}
 

function sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario_agente(evento) {
  $("#frm_nuevo_propietario_ca")[0].reset();
  $("#div_modal_propietario_representante_legal_ca").hide();
  $("#div_modal_propietario_num_partida_registral_ca").hide();
  $("#modal_propietario_tipo_docu_ca").change();

  if (evento == "new") {
    $("#modal_nuevo_propietario_titulo_ca").html("Registrar Propietario");
    $("#btn_agregar_propietario_ca").show();
    $("#btn_guardar_cambios_propietario_ca").hide();
    $("#btn_agregar_propietario_a_la_adenda_ca").hide();

    $("#div_modal_propietario_contacto_nombre_ca").hide();
    $("#div_modal_propietario_persona_contacto_ca").show();
  } else if (evento == "edit") {
    $("#modal_nuevo_propietario_titulo_ca").html("Editar Propietario");
    $("#btn_agregar_propietario_ca").hide();
    $("#btn_guardar_cambios_propietario_ca").show();
    $("#btn_agregar_propietario_a_la_adenda_ca").hide();

    $("#div_modal_propietario_contacto_nombre_ca").show();
    $("#div_modal_propietario_persona_contacto_ca").hide();
  }   else if (evento == "agente") {
    $("#modal_nuevo_propietario_titulo_ca").html("Agente - Registrar Propietario");
    $("#btn_agregar_propietario_ca").hide();
    $("#btn_guardar_cambios_propietario_ca").hide();
    $("#btn_agregar_propietario_a_la_adenda_ca").show();

    $("#div_modal_propietario_contacto_nombre_ca").hide();
    $("#div_modal_propietario_persona_contacto_ca").show();
  }

  
  
}

 

function sec_contrato_nuevo_agente_validar_campos_formulario_propietario(accion) {
  var id_propietario_para_cambios = $("#modal_propietaria_id_persona_para_cambios").val();
  var tipo_persona = $("#modal_propietario_tipo_persona").val();
  var nombre = $("#modal_propietario_nombre").val().trim();
  var tipo_docu = $("#modal_propietario_tipo_docu").val();
  var num_docu = $("#modal_propietario_num_docu").val().trim();
  var num_ruc = $("#modal_propietario_num_ruc").val().trim();
  var direccion = $("#modal_propietario_direccion").val().trim();
  var representante_legal = $("#modal_propietario_representante_legal").val().trim();
  var num_partida_registral = $("#modal_propietario_num_partida_registral").val();
  var tipo_persona_contacto = $("#modal_propietario_tipo_persona_contacto").val();
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

  if (parseInt(tipo_persona) == 1 && num_docu.length != 8) {
    alertify.error("El número de DNI debe tener 8 dígitos, no " + num_docu.length + " dígitos", 5);
    $("#modal_propietario_num_docu").focus();
    return false;
  }

  if (num_ruc.length != 11) {
    alertify.error("El número de RUC debe tener 11 dígitos, no " + num_ruc.length + " dígitos", 5);
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
    alertify.error("Ingrese el número de la Partida Registral de la empresa", 5);
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

  if (accion == "guardar_cambios_propietario") {
    if (contacto_nombre.length < 1) {
      alertify.error("Ingrese el nombre del contacto", 5);
      $("#modal_propietario_contacto_nombre").focus();
      return false;
    }
  }

  if (contacto_telefono.length < 8) {
    alertify.error("Ingrese el número telefónico del contaco", 5);
    $("#modal_propietario_contacto_telefono").focus();
    return false;
  }

  if (contacto_email.length > 0 && !sec_contrato_nuevo_agente_es_email_valido(contacto_email)) {
    alertify.error("El formato del correo electrónico es incorrecto", 5);
    $("#modal_propietario_contacto_email").focus();
    return false;
  }

  if (accion == "guardar_propietario_adenda") {
    accion = "guardar_propietario";
  }

  var data = {
    accion: accion,
    id_propietario_para_cambios: id_propietario_para_cambios,
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

function sec_contrato_nuevo_agente_validar_campos_formulario_propietario_agente(accion) {
  var id_propietario_para_cambios = $("#modal_propietaria_id_persona_para_cambios_ca").val();
  var tipo_persona = $("#modal_propietario_tipo_persona_ca").val();
  var nombre = $("#modal_propietario_nombre_ca").val().trim();
  var tipo_docu = $("#modal_propietario_tipo_docu_ca").val();
  var num_docu = $("#modal_propietario_num_docu_ca").val().trim();
  var num_ruc = $("#modal_propietario_num_ruc_ca").val().trim();
  var direccion = $("#modal_propietario_direccion_ca").val().trim();
  var representante_legal = $("#modal_propietario_representante_legal_ca").val().trim();
  var num_partida_registral = $("#modal_propietario_num_partida_registral_ca").val();
  var tipo_persona_contacto = $("#modal_propietario_tipo_persona_contacto_ca").val();
  var contacto_nombre = $("#modal_propietario_contacto_nombre_ca").val().trim();
  var contacto_telefono = $("#modal_propietario_contacto_telefono_ca").val().trim();
  var contacto_email = $("#modal_propietario_contacto_email_ca").val().trim();

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
    alertify.error("El número de DNI debe tener 8 dígitos, no " + num_docu.length + " dígitos", 5);
    $("#modal_propietario_num_docu_ca").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 3 && num_docu.length != 12) {
    alertify.error("El número de Pasaporte debe tener 12 dígitos, no " + num_docu.length + " dígitos", 5);
    $("#modal_propietario_num_docu_ca").focus();
    return false;
  }

  if (parseInt(tipo_docu) == 4 && num_docu.length != 12) {
    alertify.error("El número de Carnet de Ext. debe tener 12 dígitos, no " + num_docu.length + " dígitos", 5);
    $("#modal_propietario_num_docu_ca").focus();
    return false;
  }

  if (num_ruc.length != 11) {
    alertify.error("El número de RUC debe tener 11 dígitos, no " + num_ruc.length + " dígitos", 5);
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
    alertify.error("Ingrese el número de la Partida Registral de la empresa", 5);
    $("#modal_propietario_num_partida_registral_ca").focus();
    return false;
  }

  if (accion == "guardar_propietario") {
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
  }

  if (accion == "guardar_cambios_propietario") {
    if (contacto_nombre.length < 1) {
      alertify.error("Ingrese el nombre del contacto", 5);
      $("#modal_propietario_contacto_nombre_ca").focus();
      return false;
    }

  
  }

  if (contacto_telefono.length < 8) {
    alertify.error("Ingrese el número telefónico del contacto", 5);
    $("#modal_propietario_contacto_telefono_ca").focus();
    return false;
  }

  if (contacto_email.length > 0 && !sec_contrato_nuevo_agente_es_email_valido(contacto_email)) {
    alertify.error("El formato del correo electrónico es incorrecto", 5);
    $("#modal_propietario_contacto_email_ca").focus();
    return false;
  }

  if (accion == "guardar_propietario_agente") {
    accion = "guardar_propietario";
  }

  var data = {
    accion: accion,
    id_propietario_para_cambios: id_propietario_para_cambios,
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

function sec_contrato_nuevo_agente_guardar_beneficiario(metodo) {
  var data = sec_contrato_nuevo_agente_validar_campos_formulario_beneficiario(metodo);

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: metodo, data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: metodo, data: respuesta });
   
      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        if (metodo == "guardar_beneficiario") {
          sec_contrato_nuevo_agente_asignar_beneficiario_al_contrato(respuesta.result, "modalAgregar");
        } else if (metodo == "guardar_beneficiario_adenda") {
          sec_contrato_nuevo_agente_asignar_otros_detalles_a_la_adenda("beneficiario", respuesta.result, "modalNuevoBeneficiario");
        }

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_guardar_cambios_beneficiario() {
  var data = sec_contrato_nuevo_agente_validar_campos_formulario_beneficiario("guardar_cambios_beneficiario");

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: "guardar_cambios_beneficiario", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: "guardar_cambios_beneficiario", data: respuesta });
    
      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#modalNuevoBeneficiario").modal("hide");
        sec_contrato_nuevo_agente_actualizar_tabla_beneficiario();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_guardar_cambios_propietario() {
  var data = sec_contrato_nuevo_agente_validar_campos_formulario_propietario("guardar_cambios_propietario");

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: "guardar_cambios_propietario", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: "guardar_cambios_propietario", data: respuesta });
   
      if (parseInt(respuesta.http_code) == 400) {
        // swal('Aviso', respuesta.status, 'warning');
        // listar_transacciones(gen_cliente_id);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#modalNuevoPropietario").modal("hide");
        sec_contrato_nuevo_agente_actualizar_tabla_propietarios();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_guardar_cambios_propietario_ca() {
  var data = sec_contrato_nuevo_agente_validar_campos_formulario_propietario_agente("guardar_cambios_propietario");

  if (!data) {
    return false;
  }

  auditoria_send({ proceso: "guardar_cambios_propietario", data: data });

  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
      auditoria_send({ proceso: "guardar_cambios_propietario", data: respuesta });
      
      // console.log(respuesta.status)
      if (parseFloat(respuesta.http_code) == 400) {;
        alertify.error(respuesta.status, 5);
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        $("#modalNuevoPropietario_ca").modal("hide");
        sec_contrato_nuevo_agente_actualizar_tabla_propietarios_agente();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_asignar_beneficiario_al_contrato(id_beneficiario, modal) {
  if (array_beneficiarios_contrato.includes(id_beneficiario) === false) {
    array_beneficiarios_contrato.push(id_beneficiario);
  }
 
  if (modal == "modalAgregar") {
    $("#modalNuevoBeneficiario").modal("hide");
  }
  sec_contrato_nuevo_agente_actualizar_tabla_beneficiario();
}

function sec_contrato_nuevo_agente_actualizar_tabla_beneficiario() {
  var data = {
    accion: "obtener_beneficiarios",
    id_beneficiarios: JSON.stringify(array_beneficiarios_contrato),
  };

  var array_beneficiarios = [];

  auditoria_send({ proceso: "obtener_beneficiarios", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
        $("#divTablaBeneficiarios").html(respuesta.result);
        return false;
      }
    },
    error: function () {},
  });
}

 
 
function sec_contrato_nuevo_agente_editar_propietario_ca(id_propietario) {

  var data = {
    accion: "obtener_propietario_por_id",
    nombre_o_numdocu: id_propietario,
    tipo_busqueda: "4",
    tipo_solicitud: "",
  };

  var array_propietarios = [];

  auditoria_send({ proceso: "obtener_propietario_por_id", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
        alertify.error(respuesta.status, 5);
        return false;
      }

      if (parseInt(respuesta.http_code) == 200) {
        $("#modalNuevoPropietario_ca").modal("show");
        sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario_agente("edit");
        array_propietarios.push(respuesta.result);
        $("#modal_propietaria_id_persona_para_cambios_ca").val(array_propietarios[0][0].id);
        $("#modal_propietario_tipo_persona_ca").val(array_propietarios[0][0].tipo_persona_id).trigger("change");
        $("#modal_propietario_tipo_docu_ca").val(array_propietarios[0][0].tipo_docu_identidad_id);
        $("#modal_propietario_num_docu_ca").val(array_propietarios[0][0].num_docu);
        $("#modal_propietario_num_ruc_ca").val(array_propietarios[0][0].num_ruc);
        $("#modal_propietario_nombre_ca").val(array_propietarios[0][0].nombre);
        $("#modal_propietario_direccion_ca").val(array_propietarios[0][0].direccion);
        $("#modal_propietario_representante_legal_ca").val(array_propietarios[0][0].representante_legal);
        $("#modal_propietario_num_partida_registral_ca").val(array_propietarios[0][0].num_partida_registral);
        $("#modal_propietario_tipo_persona_contacto_ca").val(array_propietarios[0][0].xyz);
        $("#modal_propietario_contacto_nombre_ca").val(array_propietarios[0][0].contacto_nombre);
        $("#modal_propietario_contacto_telefono_ca").val(array_propietarios[0][0].contacto_telefono);
        $("#modal_propietario_contacto_email_ca").val(array_propietarios[0][0].contacto_email);

        $("#modal_propietario_tipo_docu_ca").change();
    

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_editar_propietario(id_propietario) {
  $("#modalNuevoPropietario_ca").modal("show");

  sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario_agente("edit");

  var data = {
    accion: "obtener_propietario",
    nombre_o_numdocu: id_propietario,
    tipo_busqueda: "4",
    tipo_solicitud: "",
  };

  var array_propietarios = [];

  auditoria_send({ proceso: "obtener_propietario", data: data });
  $.ajax({
    url: "/sys/set_contrato_nuevo_agente.php",
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
        array_propietarios.push(respuesta.result);
        $("#modal_propietaria_id_persona_para_cambios_ca").val(array_propietarios[0][0].id);
        $("#modal_propietario_tipo_persona_ca").val(array_propietarios[0][0].tipo_persona_id).trigger("change");
        $("#modal_propietario_tipo_docu_ca").val(array_propietarios[0][0].tipo_docu_identidad_id);
        $("#modal_propietario_num_docu_ca").val(array_propietarios[0][0].num_docu);
        $("#modal_propietario_num_ruc_ca").val(array_propietarios[0][0].num_ruc);
        $("#modal_propietario_nombre_ca").val(array_propietarios[0][0].nombre);
        $("#modal_propietario_direccion_ca").val(array_propietarios[0][0].direccion);
        $("#modal_propietario_representante_legal_ca").val(array_propietarios[0][0].representante_legal);
        $("#modal_propietario_num_partida_registral_ca").val(array_propietarios[0][0].num_partida_registral);
        $("#modal_propietario_tipo_persona_contacto_ca").val(array_propietarios[0][0].xyz);
        $("#modal_propietario_contacto_nombre_ca").val(array_propietarios[0][0].contacto_nombre);
        $("#modal_propietario_contacto_telefono_ca").val(array_propietarios[0][0].contacto_telefono);
        $("#modal_propietario_contacto_email_ca").val(array_propietarios[0][0].contacto_email);

        $("#modal_propietario_tipo_docu_ca").change();

        return false;
      }
    },
    error: function () {},
  });
}

 
function sec_contrato_nuevo_agente_ValidateEmailContrato(email) {
  var re =
    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}
 
 
function sec_contrato_nuevo_agente_guardar_contrato_agente() {
  var usuario_logueado_id = $("#usuario_id_temporal").val();
  var tipo_contrato_id = $("#tipo_contrato_id").val();
  var empresa_suscribe_id = $("#empresa_suscribe_ag").val();
  var area_responsable_id = $("#area_responsable_id").val();
  var personal_responsable_id = $("#personal_responsable_id").val();
  var nombre_ag_lc = $("#nombre_ag_lc").val();

  var aprobador_id = $("#aprobador_id").val();
  var cargo_aprobador_id = $("#cargo_aprobador_id").val();
  
  var observaciones = $("#contrato_observaciones").val();
  var correos_adjuntos_ad = $("#correos_adjuntos_ad").val();

  var id_departamento = $("#modal_inmueble_id_departamento_ca").val();
  var id_provincia = $("#modal_inmueble_id_provincia_ca").val();
  var id_distrito = $("#modal_inmueble_id_distrito_ca").val();
  var ubicacion = $("#modal_inmueble_ubicacion_ca").val().trim();

  var participacion_id_bet = $("#participacion_id_bet").val("BETSHOP");
  var participacion_id_jv = $("#participacion_id_jv").val("JUEGOS VIRTUALES");
  var participacion_id_t = $("#participacion_id_t").val("TERMINALES");
  var participacion_id_b = $("#participacion_id_b").val("BINGO");
  var participacion_id_dw = $("#participacion_id_dw").val("DEPOSITO WEB");
  var participacion_id_ccv = $("#participacion_id_ccv").val("CARRERAS DE CABALLO EN VIVO");
  var porcentaje_participacion_bet = $("#porcentaje_participacion_bet").val().trim();
  var porcentaje_participacion_j = $("#porcentaje_participacion_j").val().trim();
  var porcentaje_participacion_ter = $("#porcentaje_participacion_ter").val().trim();
  var porcentaje_participacion_bin = $("#porcentaje_participacion_bin").val().trim();
  var porcentaje_participacion_dep = $("#porcentaje_participacion_dep").val().trim();
  var porcentaje_participacion_ccv = $("#porcentaje_participacion_ccv").val().trim();
  var condicion_comercial_id_bet = $("#condicion_comercial_id_bet").val().trim();
  var condicion_comercial_id_jv = $("#condicion_comercial_id_jv").val().trim();
  var condicion_comercial_id_t = $("#condicion_comercial_id_t").val().trim();
  var condicion_comercial_id_b = $("#condicion_comercial_id_b").val().trim();
  var condicion_comercial_id_dw = $("#condicion_comercial_id_dw").val().trim();
  var condicion_comercial_id_ccv = $("#condicion_comercial_id_ccv").val().trim();
  //var bien_entregado = $("#bien_entregado").val().trim();
  // var detalle_bien_entradado = $("#detalle_bien_entradado").val().trim();

  //var nombre_agente = $("#nombre_agente").val().trim();
  //var centro_costos_n_ag = $("#centro_costos_n_ag").val().trim();
 

  var fecha_contrato_ag = $("#fecha_contrato_ag").val().trim();
  var contrato_ag_observaciones = $("#contrato_ag_observaciones").val().trim();
  var periodo_numero = $("#periodo_numero_ca").val().trim();
  var periodo = $("#periodo_ca").val().trim();

  var area_arrendada = "";
  var num_partida_registral = "";
  var oficina_registral = "";
  var num_suministro_agua = "";
  var tipo_compromiso_pago_agua = "";
  var monto_o_porcentaje_agua = "";
  var num_suministro_luz = "";
  var tipo_compromiso_pago_luz = "";
  var monto_o_porcentaje_luz = "";
  var tipo_compromiso_pago_arbitrios = "";
  var porcentaje_pago_arbitrios = "";
  var latitud = "";
  var longitud = "";

  var tipo_moneda_renta_pactada = "";
  var monto_renta = "";
  var impuesto_a_la_renta_id = "";
  var impuesto_a_la_renta_carta_de_instruccion_id = "";
  var numero_cuenta_detraccion = "";
  var monto_garantia = "";
  var tipo_adelanto_id = "";

  var vigencia_del_contrato_en_meses = "";
  var contrato_inicio_fecha = "";
  var contrato_fin_fecha = "";

  var periodo_gracia_id = "";
  var periodo_gracia_numero = "";

  var tipo_incremento_id = "";

  var contrato_fecha_suscripcion = "";

  // INICIO VALIDAR DATOS PRINCIPALES
  if (parseInt(tipo_contrato_id) == 0) {
    alertify.error("Seleccione el tipo de solicitud", 5);
    $("#tipo_contrato_id").focus();
    $("#tipo_contrato_id").select2("open");
    return false;
  }

  let new_correos = [];
  if (correos_adjuntos_ad != "") {
    correos_adjuntos_ad = correos_adjuntos_ad.split(",");
    new_correos = correos_adjuntos_ad.map((item) => item.trim());
    for (let index = 0; index < new_correos.length; index++) {
      const element = new_correos[index];
      if (element.length == 0) {
        alertify.error(" Ingrese un correo", 5);
        return false;
      }
      if (!ValidateEmailContrato_Agente(element)) {
        alertify.error(element + " no es correo valido", 5);
        return false;
      }
    }
  }

  if (parseInt(empresa_suscribe_id) == 0) {
    alertify.error("Seleccione la empresa que suscribe el contrato", 5);
    $("#empresa_suscribe_id").focus();
    $("#empresa_suscribe_id").select2("open");
    return false;
  }


  if (parseInt(nombre_ag_lc) == 0) {
    alertify.error("Seleccione un agente y cc", 5);
    $("#nombre_ag_lc").focus();
    $("#nombre_ag_lc").select2("open");
    return false;
  }



  if (parseInt(personal_responsable_id) == 0) {
    alertify.error("Seleccione un supervisor", 5);
    $("#personal_responsable_id").focus();
    $("#personal_responsable_id").select2("open");
    return false;
  }

  if (parseInt(aprobador_id) == 0) {
    alertify.error("Seleccione el aprobador que suscribe el contrato", 5);
    $("#aprobador_id").focus();
    $("#aprobador_id").select2("open");
    return false;
  }

  
  // FIN VALIDAR DATOS PRINCIPALES

  // INICIO VALIDAR PROPIETARIOS
  if (array_propietarios_contrato.length == 0) {
    alertify.error("Debe de agregar un propietario al contrato", 5);
    $("#modalBuscarPropietario_ca").modal({ backdrop: "static", keyboard: false });
    $("#modal_propietario_nombre_o_numdocu_ca").focus();
    return false;
  }
  // FIN VALIDAR PROPIETARIOS

  // INICIO VALIDAR INMUEBLES
  if (parseInt(id_departamento) == 0) {
    alertify.error("Seleccione el departamento del inmueble", 5);
    $("#modal_inmueble_id_departamento_ca").focus();
    setTimeout(function () {
      $("#modal_inmueble_id_departamento_ca").select2("open");
    }, 200);
    return false;
  }


  if (parseInt(id_provincia) == 0) {
    alertify.error("Seleccione la provincia del inmueble", 5);
    $("#modal_inmueble_id_provincia_ca").focus();
    setTimeout(function () {
      $("#modal_inmueble_id_provincia_ca").select2("open");
    }, 200);
    return false;
  }

  if (parseInt(id_distrito) == 0) {
    alertify.error("Seleccione el distrito del inmueble", 5);
    $("#modal_inmueble_id_distrito_ca").focus();
    setTimeout(function () {
      $("#modal_inmueble_id_distrito_ca").select2("open");
    }, 200);
    return false;
  }

  if (ubicacion.length < 1) {
    alertify.error("Ingrese la ubicación del inmueble", 5);
    $("#modal_inmueble_ubicacion_ca").focus();
    return false;
  }

  if (ubicacion.length < 6) {
    alertify.error("La ubicación del inmueble debe de ser mayor de 5 letras", 5);
    $("#modal_inmueble_ubicacion_ca").focus();
    return false;
  }


  if (porcentaje_participacion_bet.length == 0) {
    alertify.error("Ingrese un valor de porcentaje", 5);
    $("#porcentaje_participacion_bet").focus();
    return false;
  }

  if (porcentaje_participacion_j.length == 0) {
    alertify.error("Ingrese un valor de porcentaje", 5);
    $("#porcentaje_participacion_j").focus();
    return false;
  }

  if (porcentaje_participacion_ter.length == 0) {
    alertify.error("Ingrese un valor de porcentaje", 5);
    $("#porcentaje_participacion_ter").focus();
    return false;
  }

  if (porcentaje_participacion_bin.length == 0) {
    alertify.error("Ingrese un valor de porcentaje", 5);
    $("#porcentaje_participacion_bin").focus();
    return false;
  }

  if (porcentaje_participacion_dep.length == 0) {
    alertify.error("Ingrese un valor de porcentaje", 5);
    $("#porcentaje_participacion_dep").focus();
    return false;
  }


  if (parseInt(condicion_comercial_id_bet) == 0) {
    alertify.error("Seleccione una opción", 5);
    $("#condicion_comercial_id_bet").focus();
    $("#condicion_comercial_id_bet").select2("open");
    return false;
  }


  if (parseInt(condicion_comercial_id_jv) == 0) {
    alertify.error("Seleccione una opción", 5);
    $("#condicion_comercial_id_jv").focus();
    $("#condicion_comercial_id_jv").select2("open");
    return false;
  }

  if (parseInt(condicion_comercial_id_t) == 0) {
    alertify.error("Seleccione una opción", 5);
    $("#condicion_comercial_id_t").focus();
    $("#condicion_comercial_id_t").select2("open");
    return false;
  }

  if (parseInt(condicion_comercial_id_b) == 0) {
    alertify.error("Seleccione una opción", 5);
    $("#condicion_comercial_id_b").focus();
    $("#condicion_comercial_id_b").select2("open");
    return false;
  }

  if (parseInt(condicion_comercial_id_dw) == 0) {
    alertify.error("Seleccione una opción", 5);
    $("#condicion_comercial_id_dw").focus();
    $("#condicion_comercial_id_dw").select2("open");
    return false;
  }

 

  // INICIO VALIDAR PERIODO
  if (periodo_numero.length == 0) {
    alertify.error("Ingrese un periodo", 5);
    $("#periodo_numero_ca").focus();
    return false;
  }

  if (parseInt(periodo) == 0) {
    alertify.error("Seleccione un periodo", 5);
    $("#periodo_ca").focus(); 
    return false;
  }
 
 
 

 

  if (contrato_ag_observaciones.length > 1000) {
    alertify.error("La observacion puede contener maximo 1000 caracteres", 5);
    $("#contrato_ag_observaciones").focus();
    return false;
  }
  2

  // FIN VALIDAR PERIODO
  //  debugger;
  var dataForm = new FormData($("#form_contrato_agente")[0]);

  dataForm.append("accion", "guardar_contrato_agente");
  dataForm.append("tipo_contrato_id", tipo_contrato_id);
  dataForm.append("empresa_suscribe_id", empresa_suscribe_id);
  dataForm.append("area_responsable_id", area_responsable_id);
  dataForm.append("personal_responsable_id", personal_responsable_id);
  dataForm.append("aprobador_id", aprobador_id);
  dataForm.append("cargo_aprobador_id", cargo_aprobador_id);
  
  dataForm.append("observaciones", observaciones);
  dataForm.append("correos_adjuntos_ad", JSON.stringify(new_correos));

  dataForm.append("id_departamento", id_departamento);
  dataForm.append("id_provincia", id_provincia);
  dataForm.append("id_distrito", id_distrito);
  dataForm.append("ubicacion", ubicacion);
  dataForm.append("area_arrendada", area_arrendada);
  dataForm.append("num_partida_registral", num_partida_registral);
  dataForm.append("oficina_registral", oficina_registral);
  dataForm.append("num_suministro_agua", num_suministro_agua);
  dataForm.append("tipo_compromiso_pago_agua", tipo_compromiso_pago_agua);
  dataForm.append("monto_o_porcentaje_agua", monto_o_porcentaje_agua);
  dataForm.append("num_suministro_luz", num_suministro_luz);
  dataForm.append("tipo_compromiso_pago_luz", tipo_compromiso_pago_luz);
  dataForm.append("monto_o_porcentaje_luz", monto_o_porcentaje_luz);
  dataForm.append("tipo_compromiso_pago_arbitrios", tipo_compromiso_pago_arbitrios);
  dataForm.append("porcentaje_pago_arbitrios", porcentaje_pago_arbitrios);
  dataForm.append("latitud", latitud);
  dataForm.append("longitud", longitud);

  dataForm.append("participacion_id_bet", participacion_id_bet);
  dataForm.append("participacion_id_jv", participacion_id_jv);
  dataForm.append("participacion_id_t", participacion_id_t);
  dataForm.append("participacion_id_b", participacion_id_b);
  dataForm.append("participacion_id_dw", participacion_id_dw);
  dataForm.append("participacion_id_ccv", participacion_id_ccv);
  dataForm.append("porcentaje_participacion_bet", porcentaje_participacion_bet);
  dataForm.append("porcentaje_participacion_j", porcentaje_participacion_j);
  dataForm.append("porcentaje_participacion_ter", porcentaje_participacion_ter);
  dataForm.append("porcentaje_participacion_bin", porcentaje_participacion_bin);
  dataForm.append("porcentaje_participacion_dep", porcentaje_participacion_dep);
  dataForm.append("porcentaje_participacion_ccv", porcentaje_participacion_ccv);
  dataForm.append("condicion_comercial_id_bet", condicion_comercial_id_bet);
  dataForm.append("condicion_comercial_id_jv", condicion_comercial_id_jv);
  dataForm.append("condicion_comercial_id_t", condicion_comercial_id_t);
  dataForm.append("condicion_comercial_id_b", condicion_comercial_id_b);
  dataForm.append("condicion_comercial_id_dw", condicion_comercial_id_dw);
  dataForm.append("condicion_comercial_id_ccv", condicion_comercial_id_ccv);

 // dataForm.append("nombre_agente", nombre_agente);
 // dataForm.append("centro_costos_n_ag", centro_costos_n_ag);
  dataForm.append("nombre_ag_lc", nombre_ag_lc);

  // dataForm.append("bien_entregado", bien_entregado);
  // dataForm.append("detalle_bien_entradado", detalle_bien_entradado);
  dataForm.append("periodo_numero", periodo_numero);
  dataForm.append("periodo", periodo);
  dataForm.append("fecha_contrato_ag", fecha_contrato_ag);
  dataForm.append("contrato_ag_observaciones", contrato_ag_observaciones);

  dataForm.append("tipo_moneda_renta_pactada", tipo_moneda_renta_pactada);
  dataForm.append("monto_renta", monto_renta);
  dataForm.append("impuesto_a_la_renta_id", impuesto_a_la_renta_id);
  dataForm.append("impuesto_a_la_renta_carta_de_instruccion_id", impuesto_a_la_renta_carta_de_instruccion_id);
  dataForm.append("numero_cuenta_detraccion", numero_cuenta_detraccion);
  dataForm.append("monto_garantia", monto_garantia);
  dataForm.append("tipo_adelanto_id", tipo_adelanto_id);
  dataForm.append("vigencia_del_contrato_en_meses", vigencia_del_contrato_en_meses);
  dataForm.append("contrato_inicio_fecha", contrato_inicio_fecha);
  dataForm.append("contrato_fin_fecha", contrato_fin_fecha);
  dataForm.append("periodo_gracia_id", periodo_gracia_id);
  dataForm.append("periodo_gracia_numero", periodo_gracia_numero);
  dataForm.append("tipo_incremento_id", tipo_incremento_id);
  dataForm.append("contrato_fecha_suscripcion", contrato_fecha_suscripcion);
  dataForm.append("id_propietarios", JSON.stringify(array_propietarios_contrato));
  dataForm.append("id_inmuebles", JSON.stringify(array_inmuebles_contrato));
  // dataForm.append("id_incrementos", JSON.stringify(array_incrementos_contrato));
  dataForm.append("id_beneficiarios", JSON.stringify(array_beneficiarios_contrato));
  // dataForm.append("id_adelantos", JSON.stringify(array_adelantos_contrato));
  dataForm.append("array_nuevos_files_anexos", JSON.stringify(array_nuevos_files_anexos));

  auditoria_send({ proceso: "guardar_contrato_agente", data: dataForm });

  $.ajax({
    url: "sys/set_contrato_nuevo_agente.php",
    type: "POST",
    data: dataForm,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function (xhr) {
      loading(true);
    },
    success: function (data) {
      var respuesta = JSON.parse(data);
      auditoria_send({ proceso: "guardar_contrato", data: respuesta });
      if (parseInt(respuesta.http_code) == 200) {
        swal(
          {
            title: "Registro exitoso",
            text: "La solicitud de contrato de agente fue registrada exitosamente",
            html: true,
            type: "success",
            timer: 6000,
            closeOnConfirm: false,
            showCancelButton: false,
          },
          function (isConfirm) {
            window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
          }
        );

        setTimeout(function () {
          window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
        }, 5000);

        return true;
      } else {
        if (typeof respuesta.error === "undefined") {
          texto_del_mensaje = respuesta.mensaje;
        } else {
          texto_del_mensaje = respuesta.error;
        }
        swal({
          title: "Error al guardar Solicitud de Contrato de Agente",
          text: texto_del_mensaje,
          html: true,
          type: "warning",
          closeOnConfirm: false,
          showCancelButton: false,
        });
        return false;
      }
    },
    complete: function () {
      loading(false);
    },
  });
}
 
 
function sec_contrato_nuevo_agente_buscar_propietario_modal(tipo_solicitud) {
  var tipo_solicitud = tipo_solicitud;

  if (tipo_solicitud == "adenda") {
    $("#modal_buscar_propietario_titulo").html("Adenda - Buscar Nuevo Propietario");
    $("#modal_buscar_propietario_tipo_solicitud").val("adenda");
  } else if (tipo_solicitud == "arrendamiento") {
    $("#modal_buscar_propietario_titulo").html("Buscar Propietario");
    $("#modal_buscar_propietario_tipo_solicitud").val("arrendamiento");
  }

  $("#modalBuscarPropietario").modal({ backdrop: "static", keyboard: false });
  $("#tlbPropietariosxBusqueda").html("");
  $("#divNoSeEncontroPropietario").hide();
  $("#divRegistrarNuevoPropietario").hide();
  $("#modal_propietario_nombre_o_numdocu").val("");
  $("#modal_propietario_nombre_o_numdocu").focus();
}

function sec_contrato_nuevo_agente_buscar_propietario_modal_ca(tipo_solicitud) {
  var tipo_solicitud = tipo_solicitud;

  if (tipo_solicitud == "adenda") {
    $("#modal_buscar_propietario_titulo").html("Adenda - Buscar Nuevo Propietario");
    $("#modal_buscar_propietario_tipo_solicitud").val("adenda");
  } else if (tipo_solicitud == "arrendamiento") {
    $("#modal_buscar_propietario_titulo").html("Buscar Propietario");
    $("#modal_buscar_propietario_tipo_solicitud").val("arrendamiento");
  } else if (tipo_solicitud == "agente") {
    $("#modal_buscar_propietario_titulo_ca").html("Buscar Propietario");
    $("#modal_buscar_propietario_tipo_solicitud_ca").val("agente");
  }

  $("#modalBuscarPropietario_ca").modal({ backdrop: "static", keyboard: false });
  $("#tlbPropietariosxBusqueda_ca").html("");
  $("#divNoSeEncontroPropietario_ca").hide();
  $("#divRegistrarNuevoPropietario_ca").hide();
  $("#modal_propietario_nombre_o_numdocu_ca").val("");
  $("#modal_propietario_nombre_o_numdocu_ca").focus();
}

function sec_contrato_nuevo_agente_nuevo_propietario_modal(tipo_solicitud) {
  var tipo_solicitud = tipo_solicitud;

  $("#modal_nuevo_propietario_tipo_solicitud").val(tipo_solicitud);

  if (tipo_solicitud == "arrendamiento") {
    $("#modal_nuevo_propietario_titulo").val("Nuevo Propietario");
    sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario("new");
  } else if (tipo_solicitud == "adenda") {
    $("#modal_nuevo_propietario_titulo").val("Adenda - Nuevo Propietario");
    sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario("adenda");
  }

  $("#div_modal_propietario_mensaje").hide();
  $("#modalBuscarPropietario").modal("hide");
  $("#modalNuevoPropietario").modal({ backdrop: "static", keyboard: false });

  var tipo_busqueda = $("#modal_propietario_tipo_busqueda").val();
  var nombre_o_numdocu = $("#modal_propietario_nombre_o_numdocu").val();
  if (tipo_busqueda == 1) {
    $("#modal_propietario_nombre").val(nombre_o_numdocu);
  } else if (tipo_busqueda == 2) {
    $("#modal_propietario_num_docu").val(nombre_o_numdocu);
  }

  setTimeout(function () {
    $("#modal_propietario_tipo_persona").select2("open");
  }, 500);
}

function sec_contrato_nuevo_agente_nuevo_propietario_modal_ca(tipo_solicitud) {
  var tipo_solicitud = tipo_solicitud;

  $("#modal_nuevo_propietario_tipo_solicitud_ca").val(tipo_solicitud);

  if (tipo_solicitud == "arrendamiento") {
    $("#modal_nuevo_propietario_titulo_ca").val("Nuevo Propietario");
    sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario_agente("new");
  } else if (tipo_solicitud == "adenda") {
    $("#modal_nuevo_propietario_titulo_ca").val("Adenda - Nuevo Propietario");
    sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario_agente("adenda");
  } else if (tipo_solicitud == "agente") {
    $("#modal_nuevo_propietario_titulo_ca").val("Agente - Nuevo Propietario");
    sec_contrato_nuevo_agente_resetear_formulario_nuevo_propietario_agente("agente");
  }

  $("#div_modal_propietario_mensaje_ca").hide();
  $("#modalBuscarPropietario_ca").modal("hide");
  $("#modalNuevoPropietario_ca").modal({ backdrop: "static", keyboard: false });

  var tipo_busqueda = $("#modal_propietario_tipo_busqueda_ca").val();
  var nombre_o_numdocu = $("#modal_propietario_nombre_o_numdocu_ca").val();
  if (tipo_busqueda == 1) {
    $("#modal_propietario_nombre_ca").val(nombre_o_numdocu);
  } else if (tipo_busqueda == 2) {
    $("#modal_propietario_num_docu_ca").val(nombre_o_numdocu);
  }

  setTimeout(function () {
    $("#modal_propietario_tipo_persona_ca").select2("open");
  }, 500);
}
 

function sec_contrato_nuevo_agente_cargar_tipos_anexos() {
  limpiar_select_tipos_anexos();
  array_tabla_subdiarios = [];
  var data = {
    accion: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos",
  };
  auditoria_send({ proceso: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos", data: data });
  $.ajax({
    url: "sys/get_contrato_nuevo_agente.php",
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
          array_tabla_subdiarios.push(item);
          $("#modal_nuevo_anexo_select_tipos").append(
            '<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
          );
        });
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_limpiar_select_tipos_anexos() {
  $("#modal_nuevo_anexo_select_tipos2").html("");
  $("#modal_nuevo_anexo_select_tipos2").append('<option value="0"> - Seleccione - </option>');

  $("#modal_nuevo_anexo_select_tipos2_ac").html("");
  $("#modal_nuevo_anexo_select_tipos2_ac").append('<option value="0"> - Seleccione - </option>');

  $("#modal_nuevo_anexo_select_tipos2_ca").html("");
  $("#modal_nuevo_anexo_select_tipos2_ca").append('<option value="0"> - Seleccione - </option>');
}

var nuevos_anexos_rr = [];
function sec_contrato_nuevo_agente_modal_guardar_nuevo_anexo() {
  var nombre_archivo = "";
  var tamano_archivo = 0;
  var extension_archivo = "";
  var tipo_documento_seleccionado_nombre = "";
  var id_tipo_documento_seleccionado = 0;
  var id_nuevo_objeto_nuevo_anexo = 0;
  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();
  //ARCHIVO SUBIDO TYPE FILE
  var fileInput = $("#sec_nuevo_file_nuevo_anexo")[0];
  nombre_archivo = $("#sec_nuevo_file_nuevo_anexo")
    .val()
    .replace(/.*(\/|\\)/, "");
  if (nombre_archivo == "") {
    swal("Aviso", "Seleccione el archivo que desea registrar", "warning");
    return;
  }
  tamano_archivo = fileInput.files[0].size;
  extension_archivo = nombre_archivo.split(".");
  extension_archivo = extension_archivo[extension_archivo.length - 1];

  //TIPO DE ARCHIVO SELECCIONADO
  tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos option:selected").text();
  id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos option:selected").val();

  if (id_tipo_documento_seleccionado == 0) {
    swal("Aviso", "Seleccione tipo de anexo", "warning");
    return;
  }

  var hoy = new Date();
  var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
  var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();

  var Tiempo = fecha + "" + hora;
  id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
  var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

  var html = "";
  html += '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px;">';
  html += '<div class="form-group">';
  html += '<div class="control-label">';
  html += tipo_documento_seleccionado_nombre + ": ";
  html += "</div>";
  html +=
    '<input id="' +
    id_nuevo_objeto_nuevo_anexo +
    '" name="' +
    id_nuevo_objeto_nuevo_anexo +
    '" type="text" readonly="true" value="' +
    nombre_archivo +
    '"/>';
  html += '<input hidden type="file" name = "file_new_anexo" id = "file_' + id_nuevo_objeto_nuevo_anexo + '" />';
  //html += '<input name="' + id_nuevo_objeto_nuevo_anexo + '" type="file" readonly="true" value="' + nombre_archivo + '"/>';
  html +=
    '<button style="margin-left:10px;" class="btn btn-sm btn-danger" onclick="' + onclick + '"><i class="fa fa-trash-o"></i></button>';
  html += "</div>";
  html += "</div>";

  $("#file_" + id_nuevo_objeto_nuevo_anexo).val($("#sec_nuevo_file_nuevo_anexo").val());

  var objeto = {
    id_objeto: id_nuevo_objeto_nuevo_anexo,
    nombre_archivo: nombre_archivo,
    tamano_archivo: tamano_archivo,
    extension_archivo: extension_archivo,
    tipo_documento_seleccionado_nombre: tipo_documento_seleccionado_nombre,
    id_tipo_documento_seleccionado: id_tipo_documento_seleccionado,
  };
  nuevos_anexos_rr.push(objeto);
 

  if (tipo_contrato_id == "1") {
    $("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item
  } else if (tipo_contrato_id == "2") {
    $("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // cargar el nuevo item
  }

  $("#modalNuevosAnexos").modal("hide");

  let inputFile = $("#sec_nuevo_file_nuevo_anexo");
  let filesContainer = $("#divListaAnexosCargados");
  var filesAnexos = [];
  divListaAnexos.style.display = "none";

  divListaAnexos.style.display = "";
  let newFiles = [];
  for (let index = 0; index < inputFile[0].files.length; index++) {
    let file = inputFile[0].files[index];
    newFiles.push(file);
    filesAnexos.push(file);
  }

  newFiles.forEach((file) => {
    let fileElement = $(`<p>${file.name}</p>`);
    fileElement.data("fileData", file);
    filesContainer.append(fileElement);

    fileElement.click(function (event) {
      let fileElement = $(event.target);
      let indexToRemove = filesAnexos.indexOf(fileElement.data("fileData"));
      fileElement.remove();
      filesAnexos.splice(indexToRemove, 1);
    });
  });
  
  $("#file_" + id_nuevo_objeto_nuevo_anexo).append(newFiles);
}

var nuevos_anexos_rr = [];
function sec_contrato_nuevo_agente_modal_guardar_nuevo_anexo() {
  var nombre_archivo = "";
  var tamano_archivo = 0;
  var extension_archivo = "";
  var tipo_documento_seleccionado_nombre = "";
  var id_tipo_documento_seleccionado = 0;
  var id_nuevo_objeto_nuevo_anexo = 0;
  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();
  //ARCHIVO SUBIDO TYPE FILE
  var fileInput = $("#sec_nuevo_file_nuevo_anexo")[0];
  nombre_archivo = $("#sec_nuevo_file_nuevo_anexo")
    .val()
    .replace(/.*(\/|\\)/, "");
  if (nombre_archivo == "") {
    swal("Aviso", "Seleccione el archivo que desea registrar", "warning");
    return;
  }
  tamano_archivo = fileInput.files[0].size;
  extension_archivo = nombre_archivo.split(".");
  extension_archivo = extension_archivo[extension_archivo.length - 1];

  //TIPO DE ARCHIVO SELECCIONADO
  tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos option:selected").text();
  id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos option:selected").val();

  if (id_tipo_documento_seleccionado == 0) {
    swal("Aviso", "Seleccione tipo de anexo", "warning");
    return;
  }

  var hoy = new Date();
  var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
  var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();

  var Tiempo = fecha + "" + hora;
  id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
  var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

  var html = "";
  html += '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px;">';
  html += '<div class="form-group">';
  html += '<div class="control-label">';
  html += tipo_documento_seleccionado_nombre + ": ";
  html += "</div>";
  html +=
    '<input id="' +
    id_nuevo_objeto_nuevo_anexo +
    '" name="' +
    id_nuevo_objeto_nuevo_anexo +
    '" type="text" readonly="true" value="' +
    nombre_archivo +
    '"/>';
  html += '<input hidden type="file" name = "file_new_anexo" id = "file_' + id_nuevo_objeto_nuevo_anexo + '" />';
  //html += '<input name="' + id_nuevo_objeto_nuevo_anexo + '" type="file" readonly="true" value="' + nombre_archivo + '"/>';
  html +=
    '<button style="margin-left:10px;" class="btn btn-sm btn-danger" onclick="' + onclick + '"><i class="fa fa-trash-o"></i></button>';
  html += "</div>";
  html += "</div>";

  $("#file_" + id_nuevo_objeto_nuevo_anexo).val($("#sec_nuevo_file_nuevo_anexo").val());

  var objeto = {
    id_objeto: id_nuevo_objeto_nuevo_anexo,
    nombre_archivo: nombre_archivo,
    tamano_archivo: tamano_archivo,
    extension_archivo: extension_archivo,
    tipo_documento_seleccionado_nombre: tipo_documento_seleccionado_nombre,
    id_tipo_documento_seleccionado: id_tipo_documento_seleccionado,
  };
  nuevos_anexos_rr.push(objeto);
 

  if (tipo_contrato_id == "1") {
    $("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item
  } else if (tipo_contrato_id == "2") {
    $("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // cargar el nuevo item
  }

  $("#modalNuevosAnexos").modal("hide");

  let inputFile = $("#sec_nuevo_file_nuevo_anexo");
  let filesContainer = $("#divListaAnexosCargados");
  var filesAnexos = [];
  divListaAnexos.style.display = "none";

  divListaAnexos.style.display = "";
  let newFiles = [];
  for (let index = 0; index < inputFile[0].files.length; index++) {
    let file = inputFile[0].files[index];
    newFiles.push(file);
    filesAnexos.push(file);
  }

  newFiles.forEach((file) => {
    let fileElement = $(`<p>${file.name}</p>`);
    fileElement.data("fileData", file);
    filesContainer.append(fileElement);

    fileElement.click(function (event) {
      let fileElement = $(event.target);
      let indexToRemove = filesAnexos.indexOf(fileElement.data("fileData"));
      fileElement.remove();
      filesAnexos.splice(indexToRemove, 1);
    });
  });
 

  $("#file_" + id_nuevo_objeto_nuevo_anexo).append(newFiles);
}

function sec_contrato_nuevo_agente_modal_eliminar_nuevo_anexo(id_anexo) {
  nuevos_anexos_rr = nuevos_anexos_rr.filter((item) => item.id_objeto !== id_anexo);
  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();

  var html = "";
  $.each(nuevos_anexos_rr, function (ind, elem) {
    var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + elem["id_objeto"] + "')";

    html += '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px;">';
    html += '<div class="form-group">';
    html += '<div class="control-label">';
    html += elem["tipo_documento_seleccionado_nombre"] + ": ";
    html += "</div>";
    html +=
      '<input id="' + elem["id_objeto"] + '" name="' + elem["id_objeto"] + '" type="text" readonly="true" value="' + nombre_archivo + '"/>';
    html +=
      '<button style="margin-left:10px;" class="btn btn-sm btn-danger" onclick="' + onclick + '"><i class="fa fa-trash-o"></i></button>';
    html += "</div>";
    html += "</div>";
  });
  if (tipo_contrato_id == "1") {
    $("#sec_nuevo_nuevos_anexos_listado").html(""); // limpiar el div
    $("#sec_nuevo_nuevos_anexos_listado").append(html); // y cargarlo nuevamente sin el item eliminado
  } else if (tipo_contrato_id == "2") {
    $("#sec_nuevo_nuevos_anexos_listado_proveedor").html(""); // limpiar el div
    $("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // y cargarlo nuevamente sin el item eliminado
  }
}

 

 

// INICIO OTROS ANEXOS ARRENDAMIENTO
var contArchivos = 1;

function sec_contrato_nuevo_agente_abrir_modal_tipos_anexos() {
  $("#modaltiposanexos").modal({ backdrop: "static", keyboard: false });
  $("#modal_nuevo_anexo_tipo_contrato_id").val("1");
  sec_nuevo_cargar_tipos_anexos2();
}
// FIN OTROS ANEXOS ARRENDAMIENTO

function sec_contrato_nuevo_agente_abrir_modal_tipos_anexos_ca() {
  $("#modaltiposanexos_ca").modal({ backdrop: "static", keyboard: false });
  $("#modal_nuevo_anexo_tipo_contrato_id_ca").val("6");
  sec_nuevo_cargar_tipos_anexos2_contrato_agente();
}
// FIN OTROS ANEXOS ARRENDAMIENTO

var array_nuevos_files_anexos = [];
function sec_contrato_nuevo_agente_anadirArchivo() {
  tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2 option:selected").text();
  id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2 option:selected").val();

  if (id_tipo_documento_seleccionado != "0") {
    var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();

    //Sumamos a la variable el número de archivos.
    contArchivos = contArchivos + 1;
    //Agregamos el componente de tipo input
    var div = document.createElement("div");
    var input = document.createElement("input");
    var a = document.createElement("a");

    //Añadimos los atributos de div
    div.id = "archivo" + contArchivos;

    //Añadimos los atributos de input
    input.type = "file";
    input.name = "newAnexoPrueba[]";

    //Añadimos los atributos del enlace a eliminar
    a.href = "#";
    a.id = "archivo" + contArchivos;
    a.onclick = function () {
      borrarArchivo(a.id);
    };
    a.text = "X Eliminar archivo";

    //TIPO DE ARCHIVO SELECCIONADO
    tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2 option:selected").text();
    id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2 option:selected").val();
    html2 = "";
    html2 += '<div class="control-label">';
    html2 += tipo_documento_seleccionado_nombre + ": ";
    html2 += "</div>";

    var hoy = new Date();
    var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
    var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
    var Tiempo = fecha + "" + hora;
    id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
    //var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

    var onclick = "borrarArchivo('" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "')";

    var html = "";
    html +=
      '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
      id_tipo_documento_seleccionado +
      "_" +
      id_nuevo_objeto_nuevo_anexo +
      '">';
    html += '<div class="form-group">';
    html += '<div class="control-label">';
    html += tipo_documento_seleccionado_nombre + ": ";
    html += "</div>";
    var onchange =
      "file(event,'" +
      id_tipo_documento_seleccionado +
      "_" +
      id_nuevo_objeto_nuevo_anexo +
      "', " +
      id_tipo_documento_seleccionado +
      ", '" +
      tipo_documento_seleccionado_nombre +
      "')";
    html += '<div style="margin-top:10px;">';
    html +=
      '<input name="miarchivo[]" type="file" id="' +
      id_tipo_documento_seleccionado +
      "_" +
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
    if (tipo_contrato_id == "1") {
      $("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item
    } else if (tipo_contrato_id == "2") {
      $("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // cargar el nuevo item
    }

    $("#modaltiposanexos").modal("hide");
  } else {
    alertify.error("Seleccione el tipo de anexo", 5);
  }
}

function sec_contrato_nuevo_agente_borrarArchivo(id_anexo) {
  //Restamos el número de archivos
  contArchivos = contArchivos - 1;

  array_nuevos_files_anexos = array_nuevos_files_anexos.filter((item) => item.id_objeto !== id_anexo);
  $("div[name=" + id_anexo + "]").remove();
}

///AÑADIR ARCHIVOS ACUERDO DE CONFIDENCILIDAD
// var array_nuevos_files_anexos_acuerdos_confidencilidad = [];
function sec_contrato_nuevo_agente_anadirArchivo_ac() {
  tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").text();
  id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").val();

  if (id_tipo_documento_seleccionado != "0") {
    var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ac").val();

    //Sumamos a la variable el número de archivos.
    contArchivos = contArchivos + 1;
    //Agregamos el componente de tipo input
    var div = document.createElement("div");
    var input = document.createElement("input");
    var a = document.createElement("a");

    //Añadimos los atributos de div
    div.id = "archivo" + contArchivos;

    //Añadimos los atributos de input
    input.type = "file";
    input.name = "newAnexoPrueba[]";

    //Añadimos los atributos del enlace a eliminar
    a.href = "#";
    a.id = "archivo" + contArchivos;
    a.onclick = function () {
      borrarArchivo(a.id);
    };
    a.text = "X Eliminar archivo";

    //TIPO DE ARCHIVO SELECCIONADO
    tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").text();
    id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ac option:selected").val();
    html2 = "";
    html2 += '<div class="control-label">';
    html2 += tipo_documento_seleccionado_nombre + ": ";
    html2 += "</div>";

    var hoy = new Date();
    var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
    var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
    var Tiempo = fecha + "" + hora;
    id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
    //var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

    var onclick = "borrarArchivo('" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "')";

    var html = "";
    html +=
      '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
      id_tipo_documento_seleccionado +
      "_" +
      id_nuevo_objeto_nuevo_anexo +
      '">';
    html += '<div class="form-group">';
    html += '<div class="control-label">';
    html += tipo_documento_seleccionado_nombre + ": ";
    html += "</div>";
    var onchange =
      "file(event,'" +
      id_tipo_documento_seleccionado +
      "_" +
      id_nuevo_objeto_nuevo_anexo +
      "', " +
      id_tipo_documento_seleccionado +
      ", '" +
      tipo_documento_seleccionado_nombre +
      "')";
    html += '<div style="margin-top:10px;">';
    html +=
      '<input name="miarchivo[]" type="file" id="' +
      id_tipo_documento_seleccionado +
      "_" +
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
    if (tipo_contrato_id == "1") {
      $("#sec_nuevo_nuevos_anexos_listado_ac").append(html); // cargar el nuevo item
    } else if (tipo_contrato_id == "5") {
      $("#sec_nuevo_nuevos_anexos_listado_proveedor_ac").append(html); // cargar el nuevo item
    }

    $("#modaltiposanexos_ac").modal("hide");
  } else {
    alertify.error("Seleccione el tipo de anexo", 5);
  }
}

//CONTRATO DE AGENTES
function sec_contrato_nuevo_agente_anadirArchivo_ca() {
  tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").text();
  id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").val();

  if (id_tipo_documento_seleccionado != "0") {
    var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ca").val();

    //Sumamos a la variable el número de archivos.
    contArchivos = contArchivos + 1;
    //Agregamos el componente de tipo input
    var div = document.createElement("div");
    var input = document.createElement("input");
    var a = document.createElement("a");

    //Añadimos los atributos de div
    div.id = "archivo" + contArchivos;

    //Añadimos los atributos de input
    input.type = "file";
    input.name = "newAnexoPrueba[]";

    //Añadimos los atributos del enlace a eliminar
    a.href = "#";
    a.id = "archivo" + contArchivos;
    a.onclick = function () {
      borrarArchivo(a.id);
    };
    a.text = "X Eliminar archivo";

    //TIPO DE ARCHIVO SELECCIONADO
    tipo_documento_seleccionado_nombre = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").text();
    id_tipo_documento_seleccionado = $("#modal_nuevo_anexo_select_tipos2_ca option:selected").val();
    html2 = "";
    html2 += '<div class="control-label">';
    html2 += tipo_documento_seleccionado_nombre + ": ";
    html2 += "</div>";

    var hoy = new Date();
    var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
    var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
    var Tiempo = fecha + "" + hora;
    id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" + id_tipo_documento_seleccionado + Tiempo;
    //var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

    var onclick = "borrarArchivo('" + id_tipo_documento_seleccionado + "_" + id_nuevo_objeto_nuevo_anexo + "')";

    var html = "";
    html +=
      '<div class="col-xs-12 col-md-4 col-lg-4" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
      id_tipo_documento_seleccionado +
      "_" +
      id_nuevo_objeto_nuevo_anexo +
      '">';
    html += '<div class="form-group">';
    html += '<div class="control-label">';
    html += tipo_documento_seleccionado_nombre + ": ";
    html += "</div>";
    var onchange =
      "file(event,'" +
      id_tipo_documento_seleccionado +
      "_" +
      id_nuevo_objeto_nuevo_anexo +
      "', " +
      id_tipo_documento_seleccionado +
      ", '" +
      tipo_documento_seleccionado_nombre +
      "')";
    html += '<div style="margin-top:10px;">';
    html +=
      '<input name="miarchivo[]" type="file" id="' +
      id_tipo_documento_seleccionado +
      "_" +
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
    if (tipo_contrato_id == "6") {
      $("#sec_nuevo_nuevos_anexos_listado_ca").append(html); // cargar el nuevo item
    }

    $("#modaltiposanexos_ca").modal("hide");
  } else {
    alertify.error("Seleccione el tipo de anexo", 5);
  }
}

function sec_contrato_nuevo_agente_borrarArchivoAcuerdoConfidencialidad(id_anexo) {
  //Restamos el número de archivos
  contArchivos = contArchivos - 1;

  array_nuevos_files_anexos = array_nuevos_files_anexos.filter((item) => item.id_objeto !== id_anexo);
  $("div[name=" + id_anexo + "]").remove();
}

function sec_contrato_nuevo_agente_cargar_tipos_anexos2() {
  limpiar_select_tipos_anexos();

  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();

  array_tabla_subdiarios = [];
  var data = {
    accion: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos",
    tipo_contrato_id: tipo_contrato_id,
  };
  auditoria_send({ proceso: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos", data: data });
  $.ajax({
    url: "sys/get_contrato_nuevo_agente.php",
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
          array_tabla_subdiarios.push(item);
          $("#modal_nuevo_anexo_select_tipos2").append(
            '<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
          );
        });
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_cargar_tipos_anexos2_acuerdo_confidencialidad() {
  limpiar_select_tipos_anexos();

  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ac").val();

  array_tabla_subdiarios = [];
  var data = {
    accion: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos",
    tipo_contrato_id: tipo_contrato_id,
  };
  auditoria_send({ proceso: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos", data: data });
  $.ajax({
    url: "sys/get_contrato_nuevo_agente.php",
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
          array_tabla_subdiarios.push(item);
          $("#modal_nuevo_anexo_select_tipos2_ac").append(
            '<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
          );
        });
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_cargar_tipos_anexos2_contrato_agente() {
  limpiar_select_tipos_anexos();

  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ca").val();

  array_tabla_subdiarios = [];
  var data = {
    accion: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos",
    tipo_contrato_id: tipo_contrato_id,
  };
  auditoria_send({ proceso: "sec_contrato_nuevo_agente_obtener_tipos_de_archivos", data: data });
  $.ajax({
    url: "sys/get_contrato_nuevo_agente.php",
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
          array_tabla_subdiarios.push(item);
          $("#modal_nuevo_anexo_select_tipos2_ca").append(
            '<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
          );
        });
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_con_agregar_nuevo_tipo_archivo() {
  $("#sec_nuevo_con_agregar_nuevo_tipo_archivo").modal({ backdrop: "static", keyboard: false });
  $("#sec_nuevo_tipo_anexo_nombre").val("");
  setTimeout(function () {
    $("#sec_nuevo_tipo_anexo_nombre").focus();
  }, 500);
}

function sec_contrato_nuevo_agente_con_agregar_nuevo_tipo_archivo_ac() {
  $("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ac").modal({ backdrop: "static", keyboard: false });
  $("#sec_nuevo_tipo_anexo_nombre_ac").val("");
  setTimeout(function () {
    $("#sec_nuevo_tipo_anexo_nombre_ac").focus();
  }, 500);
}

function sec_contrato_nuevo_agente_con_agregar_nuevo_tipo_archivo_ca() {
  $("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ca").modal({ backdrop: "static", keyboard: false });
  $("#sec_nuevo_tipo_anexo_nombre_ca").val("");
  setTimeout(function () {
    $("#sec_nuevo_tipo_anexo_nombre_ca").focus();
  }, 500);
}

function sec_contrato_nuevo_agente_guardarNuevoTipoAnexo() {
  if ($("#sec_nuevo_tipo_anexo_nombre").val() == "") {
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
  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id").val();
  var anexo = $("#sec_nuevo_tipo_anexo_nombre").val();
  var data = {
    accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
    anexo: anexo,
    tipo_contrato_id: tipo_contrato_id,
  };

  auditoria_send({ proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo", data: data });
  $.ajax({
    url: "sys/set_contrato_nuevo_agente.php",
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
        $("#sec_nuevo_con_agregar_nuevo_tipo_archivo").modal("hide");
        sec_nuevo_cargar_tipos_anexos2();
        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_guardarNuevoTipoAnexo_ac() {
  if ($("#sec_nuevo_tipo_anexo_nombre_ac").val() == "") {
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
  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ac").val();
  var anexo = $("#sec_nuevo_tipo_anexo_nombre_ac").val();
  var data = {
    accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
    anexo: anexo,
    tipo_contrato_id: tipo_contrato_id,
  };

  auditoria_send({ proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo", data: data });
  $.ajax({
    url: "sys/set_contrato_nuevo_agente.php",
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
        $("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ac").modal("hide");
        sec_nuevo_cargar_tipos_anexos2_acuerdo_confidencialidad();
        return false;
      }
    },
    error: function () {},
  });
}


function sec_nuevo_cargar_tipos_anexos2_contrato_agente() {
	limpiar_select_tipos_anexos();

	var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ca").val();

	array_tabla_subdiarios = [];
	var data = {
		accion: "sec_contrato_nuevo_obtener_tipos_de_archivos",
		tipo_contrato_id: tipo_contrato_id,
	};
	auditoria_send({ proceso: "sec_contrato_nuevo_obtener_tipos_de_archivos", data: data });
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
					array_tabla_subdiarios.push(item);
					$("#modal_nuevo_anexo_select_tipos2_ca").append(
						'<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + "</option>"
					);
				});
				return false;
			}
		},
		error: function () {},
	});
}

function sec_contrato_nuevo_agente_guardarNuevoTipoAnexo_ca() {
  if ($("#sec_nuevo_tipo_anexo_nombre_ca").val() == "") {
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
  var tipo_contrato_id = $("#modal_nuevo_anexo_tipo_contrato_id_ca").val();
  var anexo = $("#sec_nuevo_tipo_anexo_nombre_ca").val();
  var data = {
    accion: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo",
    anexo: anexo,
    tipo_contrato_id: tipo_contrato_id,
  };

  auditoria_send({ proceso: "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo", data: data });
  $.ajax({
    url: "sys/set_contrato_nuevo_agente.php",
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
        $("#sec_nuevo_con_agregar_nuevo_tipo_archivo_ca").modal("hide");
        sec_nuevo_cargar_tipos_anexos2_contrato_agente();

        setTimeout(function () {
          $("#modal_nuevo_anexo_select_tipos2_ca").val(respuesta.result).trigger('change');
     
        }, 1500); 

        return false;
      }
    },
    error: function () {},
  });
}

function sec_contrato_nuevo_agente_file(event, id, idtd, tdnombre) {
  var id_ = "#" + id;
  var id_tip_documento = idtd;
  let file = $(id_)[0].files[0];
  var nombre_archivo = file.name;
  var tamano_archivo = file.size;
  var extension = $(id_).val().replace(/^.*\./, "");

  var objeto = {
    id_objeto: id,
    nombre_archivo: nombre_archivo,
    tamano_archivo: tamano_archivo,
    extension: extension,
    id_tip_documento: id_tip_documento,
    tip_doc_nombre: tdnombre,
  };

  array_nuevos_files_anexos.push(objeto);
}
