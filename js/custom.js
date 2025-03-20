//var ls = localStorage;

var sec_id = $("#sec_id").val();
var sub_sec_id = $("#sub_sec_id").val();
var item_id = $("#item_id").val();
var url_object = {};
// console.log("solo");
var site_title = $(document).prop("title");
loading(true);
$(window).load(function () {
  // console.log("document.load");
});

function check_login() {
  // console.log("check_login");
  $.get(
    "/sys/get_login.php",
    {
      check_login: true,
    },
    function (r) {
      if (r == "no_login") {
        m_reload();
      }
    }
  );
}

(function ($) {
  $.fn.fixMe = function (data = "") {
    // console.log(data);
    if (data.columns == undefined) data.columns = 0;
    if (data.marginTop == undefined) data.marginTop = 0;
    if (data.footer == undefined) data.footer = false;
    if (data.bgColor == undefined) data.bgColor = "white";
    if (data.bgHeaderColor == undefined) data.bgHeaderColor = "white";
    if (data.zIndex == undefined) data.zIndex = 0;
    if (data.recursive == undefined) data.recursive = false;

    if (!$(this.selector + "_fixed").length) {
      $(this.selector + "_fixed").remove();
    }

    return this.each(function () {
      var $this = $(this),
        $t_fixed,
        $col_fixed,
        $initialXTable = $(this).offset().left;
      function init() {
        if (!$("." + $this.prop("id") + "_wrap").length) {
          $this.wrap('<div class="' + $this.prop("id") + '_wrap">');
        }

        $t_fixed = $this.clone();
        $t_fixed.prop("id", $this.prop("id") + "_fixed");

        if (!data.footer) {
          $t_fixed.find("tfoot").remove(); //remove footer
        }

        $t_fixed
          .find("tbody")
          .remove()
          .end()
          .addClass("fixed")
          .insertBefore($this);

        $t_fixed.css("margin-top", data.marginTop + "px");
        $t_fixed.css("background-color", "white");
        $t_fixed.css("z-index", data.zIndex);

        if (data.columns > 0) {
          $col_fixed = $this.clone();

          //height de tabla

          //colores de la tabla fixed
          $this.find("tbody tr").each(function (index) {
            var tr = $(this);
            var colorTr = tr.css("background-color");
            var heig = tr.height();
            var pad = tr.css("padding");
            if (colorTr == "rgba(0, 0, 0, 0)") {
              colorTr = "#ffffff";
            }
            $col_fixed
              .find("tbody tr")
              .eq(index)
              .css({ "background-color": colorTr, height: heig, padding: pad });
          });

          $col_fixed
            .find("thead tr:gt(0)")
            .remove()
            .end()
            .addClass("fixed")
            .insertBefore($this); //delete all tr but first

          var count_column = 0;
          $col_fixed.find("tbody tr:nth-child(1) td").each(function () {
            //get column count
            count_column++;
          });

          for (count_column; data.columns <= count_column; count_column--) {
            //remove all columns but the fixed ones
            $col_fixed
              .find("thead tr")
              .find("th:eq(" + count_column + ")")
              .remove();
            $col_fixed
              .find("tbody tr")
              .find("td:eq(" + count_column + ")")
              .remove();
            $col_fixed
              .find("tbody tr")
              .find("th:eq(" + count_column + ")")
              .remove();
          }

          $col_fixed.css("margin-top", data.marginTop + "px");
          $t_fixed.css("background-color", "white");
          $col_fixed.css("z-index", data.zIndex);
        }

        resizeFixed();
      }
      function resizeFixed() {
        $t_fixed.width($this.outerWidth());
        $t_fixed.find("th").each(function (index) {
          $(this).css("width", $this.find("th").eq(index).outerWidth() + "px");
        });

        if (data.columns > 0) {
          var widthCol = 0;
          for (i = 0; i < data.columns; i++) {
            widthCol += $this
              .find("tbody tr")
              .find("td:eq(" + i + ")")
              .width();
          }

          $col_fixed.width(widthCol);

          $col_fixed.find("th").each(function (index) {
            $(this).css(
              "width",
              $this.find("th").eq(index).outerWidth() + "px"
            );
            //	$(this).css("height",$this.find("th").eq(index).outerHeight()+"px");
            $(this).css("background-color", data.bgHeaderColor);
          });

          $col_fixed.find("td").each(function (index) {
            $(this).css(
              "width",
              $this.find("td").eq(index).outerWidth() + "px"
            );
            //$(this).css("height",$this.find("td").eq(index).outerHeight()+"px");
            //$(this).css("background-color",data.bgColor);
          });

          $col_fixed.fixMe({
            recursive: $col_fixed,
            marginTop: data.marginTop,
            bgColor: data.bgColor,
            bgHeaderColor: data.bgHeaderColor,
            zIndex: data.zIndex + 1,
          });
        }
      }
      function scrollFixed() {
        var offsetY = $(this).scrollTop(),
          offsetX = $(this).scrollLeft(),
          tableOffsetTop = $this.offset().top - data.marginTop,
          tableOffsetBottom =
            tableOffsetTop + $this.height() - $this.find("thead").height(),
          tableOffsetLeft = $this.offset().left;

        if (offsetY < tableOffsetTop || offsetY > tableOffsetBottom)
          $t_fixed.hide();
        else if (
          offsetY >= tableOffsetTop &&
          offsetY <= tableOffsetBottom &&
          $t_fixed.is(":hidden")
        )
          $t_fixed.show();

        if (data.recursive && data.recursive.is(":hidden")) {
          $t_fixed.hide();
        }

        $t_fixed.css("left", tableOffsetLeft - offsetX + "px");

        if (data.columns > 0) {
          if ($initialXTable <= tableOffsetLeft) $col_fixed.hide();
          else $col_fixed.show();
          $col_fixed.css("top", tableOffsetTop - offsetY + "px");
        }
      }
      //$(window).resize(resizeFixed);
      $(window).scroll(scrollFixed);
      $(".table-responsive").on("scroll", function () {
        scrollFixed();
      });

      $(".tablaHeight").on("scroll", function () {
        scrollFixed();
      });

      $(".main-container").on("scroll", function () {
        scrollFixed();
      });

      $(".nose").on("scroll", function (event) {
        scrollFixed();
      });
      init();
    });
  };
})(jQuery);

$(document).ready(function () {
  $(document).on("keypress", function (e) {
    if (e.which === 10) $(".trigger-ctrlenter").click(); //ctrl+enter
    if (e.which === 13) $(".trigger-enter").click(); //enter
    if (e.which === 9) $(".trigger-ctrli").click(); //ctrl+i
  });

  $(".auto-focus").focus();

  url_to_object();

  //window.resizeTo(300,400);
  //console.log("custom.js:ready");
  btn_settings();
  btn_events();
  loading(false);

  $("#adm_mantenimiento_list").on("init.dt", function () {
    $("#adm_mantenimiento_list").show();
  });
  var arr_aLengthMenu = [10, 25, 50, 100];
  if (screen.height > 800) {
    arr_aLengthMenu = [15, 30, 60, 120];
  }
  $("#adm_mantenimiento_list").DataTable({
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
    },
    order: [[0, "desc"]],
  });

  init_sort();

  $(".datepicker")
    .datepicker({
      format: "dd-mm-yyyy",
      autoclose: true,
    })
    .on("show", function (ev) {
      // console.log($(this));
    })
    .on("changeDate", function (ev) {
      $(this).datepicker("hide");
      var newDate = new Date(ev.date);
      $("input[data-real-date=" + $(this).attr("id") + "]").val(
        $.format.date(newDate, "yyyy-MM-dd")
      );
    });

  sec_afiliarse();
  sec_contratos();
  sec_clientes();
  sec_recaudacion();
  sec_reportes();
  sec_dev();
  sec_soporte();
  sec_tickets_por_pagar();
  sec_locales();
  sec_horarios();
  sec_consultas();
  sec_mantenimientos();
  sec_usuarios();
  sec_destinatarios();
  sec_incidencias();
  sec_adm_login_ip_whitelist();
  sec_caja_hermeticase();
  sec_aprobacion_registros();
  sec_historial_monto();
  sec_personal();
  sec_bancos();
  sec_cobranzas();
  // sec_cobranzas_estados_de_cuenta_diferencia();
  sec_comercial();
  sec_marketing();
  sec_home();
  sec_caja();

  sec_archivos();
  sec_bingotorito();

  fixHeadTicket();
  registro_premios();
  sec_caja_clientes_depositos();
  sec_televentas();
  sec_televentas_historial_cliente();
  sec_televentas_depositos();
  //	sec_caja_clientes_depositos_val();
  sec_sorteos();
  registro_foto_premios();
  sec_reporte_premios();
  sec_reportes_televentas();
  sec_reportes_televentas_clientes();
  sec_reportes_torito();
  sec_reportes_agentes();
  sec_torito();
  sec_mantenimientos_etiquetas_tlv();
  sec_adm_periodo_liquidacion();
  sec_comercial_zona_meta();
  sec_cron_report_tls();
  sec_contrato();
  sec_tesoreria();
  sec_solicitud_mantenimiento();
  sec_reportes_solicitud_mantenimiento();
  sec_adm_tipo_cambio();
  sec_adm_indice_inflacion();
  sec_adm_feriados();
  sec_versiones();
  sec_mantenimientos_billetera_tls();
  sec_reportes_contables();
  sec_televentas_pagador();
  sec_reportes_televentas_pagos();
  sec_mantenimientos_programacion_pagador();
  sec_reportes_prevencion_fraude();
  sec_reportes_balance();
  sec_mepa();
  sec_reportes_tickets_pagados();
  sec_reportes_cuadre_balance();
  sec_reportes_recargas_web();
  sec_recaudacion_liquidaciones_agente();
  sec_recaudacion_liquidaciones_cont_agentes();
  sec_consolidado_agentes();
  sec_reportes_correcciones();
  sec_prestamo();
  sec_garantia_locales();
  sec_dni_2_factores();

  sec_caja_resumen_agentes();
  sec_derivacion_tecnico();

  sec_servicio_tecnico_atencion();
  sec_servicio_tecnico();
  sec_servicio_tecnico_derivacion();
  sec_servicio_tecnico_observado();
  sec_servicio_tecnico_reporte();
  sec_autollenado_tambo();
  sec_reportes_clientes_online_jv();
  sec_vale();
  sec_reportes_televentas_billetera_juegos_virtuales();
  sec_reportes_televentas_billetera_det_juegos_virtuales();
  sec_conciliacion();
  sec_extorno();
  sec_mantenimiento();
  sec_comprobante();
  sec_kasnet();
  sec_herramientas_ti();

  sec_solicitud_estimacion();
  sec_adm_parametros_generales();
  sec_adm_modificaciones();
  sec_reportes_ventas_retail();
  sec_reportes_etiquetas();
  sec_reportes_sorteo_nuevos_registros();
  sec_resumen_apuestas_aterax();

  sec_kurax();
  if (sec_id == "billetera") {
    sec_billetera();
  }
  if (sec_id == "reportes" && sub_sec_id == "precierre") {
    sec_reportes_precierre();
  }
  var $contratos_meses = $("#contratos-meses");
  if ($contratos_meses.length > 0) {
    new Chart($contratos_meses, {
      type: "line",
      data: {
        labels: [
          "Jul 16",
          "Ago 16",
          "Sep 16",
          "Oct 16",
          "Nov 16",
          "Dic 16",
          "Ene 17",
          "Feb 17",
          "Mar 17",
          "Abr 17",
        ],
        datasets: [
          {
            label: " Contratos Iniciados",
            data: [1, 1, 5, 12, 10, 4, 24, 11, 6, 2],
            backgroundColor: "rgba(101, 156, 224, .3)",
            borderColor: "rgba(101, 156, 224, .4)",
            borderWidth: 1,
            pointRadius: 3,
            pointHitRadius: 5,
          },
          {
            label: " Contratos Vencidos",
            data: [0, 0, 0, 0, 0, 0, 0, 0, 12, 2],
            backgroundColor: "rgba(237, 107, 118, .3)",
            borderColor: "rgba(237, 107, 118, .4)",
            borderWidth: 1,
            pointRadius: 3,
            pointHitRadius: 5,
          },
        ],
      },
      options: {
        scales: {
          yAxes: [
            {
              ticks: {
                beginAtZero: true,
              },
            },
          ],
        },
      },
    });
  }

  var $contrato_modelos = $("#contrato-modelos");
  if ($contrato_modelos.length > 0) {
    new Chart($contrato_modelos, {
      type: "doughnut",
      data: {
        labels: ["Consorcio", "Participacion", "Comision"],
        datasets: [
          {
            label: " Current Week Visits",
            data: [5, 80, 4],
            backgroundColor: [
              "rgba( 55, 198, 211, .6)",
              "rgba(241, 196,  15, .6)",
              "rgba(237, 107, 118, .6)",
            ],
            hoverBackgroundColor: [
              "rgba( 55, 198, 211, .8)",
              "rgba(241, 196,  15, .8)",
              "rgba(237, 107, 118, .8)",
            ],
            borderColor: "rgba(255, 255, 255, 1)",
            hoverBorderColor: "rgba(255, 255, 255, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {},
    });
  }

  auditoria_send();
});

function url_to_object() {
  //console.log("url_to_object");
  //console.log(parse_url(window.location.href));

  url_object.fragment = {};
  parse_str(parse_url(window.location.href, "fragment"), url_object.fragment);
  url_object.query = {};
  parse_str(parse_url(window.location.href, "query"), url_object.query);

  //console.log(url_object);
}
function init_sort() {
  $(".sort_list").each(function (index, el) {
    $(el).sortable({
      helper: "clone",
      cursor: "move",
      stop: function (event, ui) {
        //console.log(ui);
        var list = {};
        $(el)
          .children(".sort_item")
          .each(function (i, e) {
            list[i] = $(e).attr("data-sort-id");
          });
        var sort_data = Object();
        sort_data.tabla = $(el).attr("data-sort-tabla");
        sort_data.list = list;
        $.post(
          "sys/set_data.php",
          {
            opt: "sort_list",
            data: sort_data,
          },
          function (r, textStatus, xhr) {
            try {
              // console.log("sort_list:ready");
              // console.log(r);
              //m_reload();
            } catch (err) {
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
        // console.log(list);
      },
    });
  });
}

function btn_settings() {
  //console.log("btn_settings");
  $(".switch").bootstrapToggle({
    on: "activo",
    off: "inactivo",
    onstyle: "success",
    offstyle: "danger",
    size: "mini",
  });
  $(".toggle")
    //.off()
    .on("click", function (event) {
      if (typeof $(this).find(".switch").data().ignore === "undefined") {
        //$(this).find(".switch").bootstrapToggle("toggle");
      }
    });
  // $("select[name=ubigeo_departamento]").off()
  // 	.change(function(event) {
  // 		loading(true);
  // 		//$("select[name=ubigeo_provincia]").append($("<option>").html("Seleccione una Provincia").val(0));
  // 		$("select[name=ubigeo_distrito]").html("");
  // 		$("select[name=ubigeo_distrito]").append($("<option>").html("- Seleccione una Provincia -").val(""));
  // 		$("select[name=ubigeo_distrito]").attr('disabled',"disabled");
  // 		//Seleccione Departamento
  // 		var data = Object();
  // 			data.departamento_id = $(this).val();
  // 		//console.log(data);
  // 		$.get('sys/build_html.php', {
  // 			"opt":"select_ubigeo_departamento",
  // 			"data":data
  // 			},
  // 			function(r) {
  // 				//console.log(r);
  // 				var response = jQuery.parseJSON(r);
  // 				//console.log(response);
  // 				$("select[name=ubigeo_provincia]").html("");
  // 				$("select[name=ubigeo_provincia]").append($("<option>").html("Seleccione una Provincia").val(""));
  // 				$.each(response, function(index, val) {
  // 					 $("select[name=ubigeo_provincia]").append($("<option>").html(val.nombre).val(val.cod));
  // 				});
  // 				$("select[name=ubigeo_provincia]").removeAttr('disabled');
  // 				loading();
  // 				$("select[name=ubigeo_provincia]").off().change(function(event) {
  // 					loading(true);
  // 					//var data = Object();
  // 						data.provincia_id = $(this).val();
  // 					//console.log(data);
  // 					$.get('sys/build_html.php', {
  // 						"opt":"select_ubigeo_provincia",
  // 						"data":data
  // 						},
  // 						function(r) {
  // 							try{
  // 								//console.log(r);
  // 								var response = jQuery.parseJSON(r);
  // 								//console.log(response);
  // 								$("select[name=ubigeo_distrito]").html("");
  // 								$("select[name=ubigeo_distrito]").append($("<option>").html("- Seleccione un Distrito -").val(""));
  // 								$.each(response, function(index, val) {
  // 									 $("select[name=ubigeo_distrito]").append($("<option>").html(val.nombre).val(val.cod));
  // 								});
  // 								$("select[name=ubigeo_distrito]").removeAttr('disabled');
  // 								loading();

  // 							}catch(err){
  // 								swal({
  // 									title: 'Error en la base de datos',
  // 									type: "warning",
  // 									timer: 2000,
  // 								}, function(){
  // 									swal.close();
  // 								});
  // 							}
  // 					});
  // 				});

  // 		});
  // 	});

  // $("select[name=ubigeo_provincia]").off().change(function(event) {
  // 		loading(true);
  // 		var data = Object();
  // 			data.departamento_id = $("select[name=ubigeo_departamento]").val();
  // 			data.provincia_id = $(this).val();
  // 		//console.log(data);
  // 		$.get('sys/build_html.php', {
  // 			"opt":"select_ubigeo_provincia",
  // 			"data":data
  // 			},
  // 			function(r) {
  // 				//console.log(r);
  // 				var response = jQuery.parseJSON(r);
  // 				//console.log(response);
  // 				$("select[name=ubigeo_distrito]").html("");
  // 				$("select[name=ubigeo_distrito]").append($("<option>").html("- Seleccione un Distrito -").val(""));
  // 				$.each(response, function(index, val) {
  // 					 $("select[name=ubigeo_distrito]").append($("<option>").html(val.nombre).val(val.cod));
  // 				});
  // 				$("select[name=ubigeo_distrito]").removeAttr('disabled');
  // 				loading();

  // 		});
  // 	});

  $("input[type=radio][name=tbl_locales_otra_casa_apuestas]").change(
    function () {
      if (this.value == "0") {
        $(".hide_form_otra_casa_apuestas_des").hide();
        //$('.hide_form_otra_casa_apuestas_des textarea').removeAttr('required');
      } else if (this.value == "1") {
        $(".hide_form_otra_casa_apuestas_des").show();
        //$('.hide_form_otra_casa_apuestas_des textarea').attr('required','required');
        $(".hide_form_otra_casa_apuestas_des textarea").focus();
      }
    }
  );
  $("input[type=radio][name=tbl_locales_experiencia_casa_apuestas]").change(
    function () {
      if (this.value == "0") {
        //$('.hidden_form_experiencia_casa_apuestas_des textarea').removeAttr('required');
        $(".hidden_form_experiencia_casa_apuestas_des").hide();
      } else if (this.value == "1") {
        $(".hidden_form_experiencia_casa_apuestas_des").show();
        //$('.hidden_form_experiencia_casa_apuestas_des textarea').attr('required','required');
        $(".hidden_form_experiencia_casa_apuestas_des textarea").focus();
      }
    }
  );
}
function btn_events() {
  //console.log("btn_events:ready");
  $(".add_btn")
    .off()
    .click(function (event) {
      add_item_dialog(event);
    });
  $(".save_btn")
    //.off()
    .on("click", function () {
      loading(true);
      save_item($(this));
      console.log($(this));
    });
  $(".check_local_paid_btn")
    .off()
    .on("click", function () {
      loading(true);
      check_local_paid($(this));
    });

  $(".switch")
    .off()
    .on("change", function (event) {
      switch_data($(event.target));
    });

  $(".del_btn")
    .off()
    .on("click", function (event) {
      event.preventDefault();
      del_item_dialog($(this));
    });
  $(".btn-add-child")
    .off()
    .click(function (event) {
      event.preventDefault();
      add_child_dialog($(this));
    });
  $(".select_add_dialog_btn")
    .off()
    .click(function (event) {
      event.preventDefault();
      select_add_dialog($(this));
    });
  $(".select_add_btn")
    .off()
    .click(function (event) {
      event.preventDefault();
      select_add($(this));
    });

  $(".save_adm_btn")
    .off()
    .click(function (event) {
      console.log("save_adm_btn");
      loading(true);
      var save_data = Object();
      save_data.table = "adm_form_items";
      save_data.values = Object();
      $(".adm_box_inputs").each(function (index, el) {
        var itm = Object();
        itm.id = $(el).data("id");
        itm.values = Object();
        $("#" + $(el).attr("id") + " input").each(function (index, el) {
          var input = Object();
          input.col = $(el).attr("name");
          input.val = $(el).val();
          itm.values[input.col] = input.val;
        });
        $("#" + $(el).attr("id") + " select").each(function (index, el) {
          var input = Object();
          input.col = $(el).attr("name");
          input.val = $(el).val();
          itm.values[input.col] = input.val;
        });
        save_data.values[itm.id] = itm;
      });
      console.log(save_data);
      $.post(
        "sys/set_data.php",
        {
          opt: "save_adm_inputs",
          data: save_data,
        },
        function (r, textStatus, xhr) {
          try {
            console.log("save_adm_inputs:ready");
            console.log(r);
            var response = jQuery.parseJSON(r);
            console.log(response);
            m_reload();
          } catch (err) {
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
    });

  $(".list-filter-input")
    .off()
    .keyup(function (event) {
      var input, filter, ul, li, a, i;
      input = $(this);
      filter = input.val().toUpperCase();
      console.log(filter);
      ul = $("#" + $(this).data("list"));

      if (input.data("list-item")) {
        //li = $('.'+$(this).data("list-item"));
        $("." + input.data("list-item")).each(function (index, el) {
          //console.log(el);
          var key = $(el)
            .find("." + input.data("list-key"))
            .html();

          //console.log(input.data("list-key"));
          if (key.toUpperCase().indexOf(filter) > -1) {
            //console.log(key);
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      } else {
        li = ul.children("li");
        for (i = 0; i < li.length; i++) {
          a = li[i].getElementsByTagName("label")[0];
          if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
            $(li[i]).show();
          } else {
            $(li[i]).hide();
          }
        }
      }
      //console.log(li);
    });

  $(".modal_open_btn")
    .off()
    .on("click", function (event) {
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
              //.modal("show");
              var target = buton.data("target");
              $("" + target)
                .on("shown.bs.modal", function (e) {
                  $(target + " input[type=text]")
                    .first()
                    .focus();
                  btn_events();
                  //$("#filtro").focus();
                  //console.log($("#select_add_dialog_modal .input_text[type=text]").first());
                })
                .on("hidden.bs.modal", function (e) {
                  //$("#select_add_dialog_modal").remove();
                })
                .modal("show");
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
              }
            );
          }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          if (console && console.log) {
            console.log(
              "La solicitud validar permisos pagos manuales ver a fallado: " +
                textStatus
            );
          }
        });
    });

  // $(".export_list_btn")
  // 	.off()
  // 	.on("click",function(event) {
  // 			event.preventDefault();
  // var buton = $(this);
  // var data = Object();
  // data.filtro = Object();
  // data.where="validar_usuario_permiso_botones";
  // $(".input_text_validacion").each(function(index, el) {
  // 	data.filtro[$(el).attr("data-col")]=$(el).val();
  // });
  // data.filtro.text_btn = buton.data("button");
  // console.log(data);
  // auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
  // $.ajax({
  // 	data: data,
  // 	type: "POST",
  // 	dataType: "json",
  // 	url: "/api/?json"
  // })
  // .done(function( dataresponse) {
  // 	try{
  // 		console.log(dataresponse);
  // 		if (dataresponse.permisos==true) {
  // window.location.replace(event.target.getAttribute('href'));

  // 		}else{
  // 			swal({
  // 				title: 'No tienes permisos',
  // 				type: "info",
  // 				timer: 2000,
  // 			}, function(){
  // 				swal.close();
  // 			});
  // 		}
  // 	}catch(err){
  // 		swal({
  // 			title: 'Error en la base de datos',
  // 			type: "warning",
  // 			timer: 2000,
  // 		}, function(){
  // 			swal.close();
  // 		});
  // 	}
  // })
  // .fail(function( jqXHR, textStatus, errorThrown ) {
  // 	if ( console && console.log ) {
  // 		console.log( "La solicitud validar permisos pagos manuales ver a fallado: " +  textStatus);
  // 	}
  // })
  // });

  /*$(".export_list_btn")
		.off()
		.click(function(event) {
			var table = $(this).data("table");
			var save_data = Object();
				save_data.type = $(this).data("type");
			$.post('export.php', {
				"export": table
				,"data":save_data
			}, function(r, textStatus, xhr) {
				console.log("save_item:ready");
				console.log(r);
				var response = jQuery.parseJSON(r);
				console.log(response);


			});
		});*/

  //$("#prev_info").modal('show');
  $(".btn-preview")
    .off()
    .on("click", function (event) {
      event.preventDefault();
      var buton = $(this);
      var data = Object();
      data.filtro = Object();
      data.where = "validar_usuario_permiso_botones";
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
              event.preventDefault();
              console.log("btn-preview:click");
              var data = Object();
              data.table = buton.data("table");
              data.id = buton.data("id");
              $.get(
                "sys/build_html.php",
                {
                  opt: "preview_item",
                  data: data,
                },
                function (r) {
                  //console.log(r);
                  //$("#select_add_dialog_modal").remove();
                  $("body").append(r);
                  //$('#prev_info .modal-body').html(r);
                  $("#prev_info")
                    .modal({})
                    .on("show.bs.modal", function (e) {
                      //$("#select_add_dialog_modal .input_text[type=text]").first().focus();
                      //console.log($("#select_add_dialog_modal .input_text[type=text]").first());
                    })
                    .on("hidden.bs.modal", function (e) {
                      $("#prev_info").remove();
                    })
                    .modal("show");
                  //btn_settings();
                  //btn_events();
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
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          if (console && console.log) {
            console.log(
              "La solicitud validar permisos mostrar información a fallado: " +
                textStatus
            );
          }
        });
    });

  $(".btn-print")
    .off()
    .click(function (event) {
      event.preventDefault();
      console.log();
      window.open(
        $(this).attr("href"),
        "Imprimir",
        "width=550, height=600, menubar=no, resizable=no, status=no, titlebar=no, toolbar=no, top=10",
        true
      );
    });

  //		ACTIVE TAB
  //if(active_tab = localStorage.getItem("active_tab")){
  //console.log(contrato_tab);
  //$(".contrato_tabs a[data-tab="+contrato_tab+"]").tab("show");
  //}
  //console.log(url_object.fragment.tab);
  if (url_object.fragment.tab) {
    $("a.tab_btn[data-tab=" + url_object.fragment.tab + "]").tab("show");
  }
  $("a.tab_btn")
    .off()
    .click(function (event) {
      var boton = $(this);
      // var data = Object();
      // data.filtro = Object();
      // data.where="validar_usuario_permiso_botones";
      // $(".input_text_validacion").each(function(index, el) {
      // 	data.filtro[$(el).attr("data-col")]=$(el).val();
      // });
      // //data.filtro.cod_btn = 9;
      // data.filtro.text_btn = $(this).data("button");
      // console.log(data);
      // auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
      // $.ajax({
      // 	data: data,
      // 	type: "POST",
      // 	dataType: "json",
      // 	url: "/api/?json"
      // })
      // .done(function( dataresponse) {
      // 	try{
      // 		console.log(dataresponse);
      // 		if (dataresponse.permisos==true) {
      event.preventDefault();
      var tab = boton.data("tab");
      if (item_id == "new") {
        swal("Por favor guarde antes de cambiar de pestaña.");
      } else {
        window.location.hash = "tab=" + tab;
        boton.tab("show");
      }
      // 		}else{
      // 			swal({
      // 				title: 'No tienes permisos',
      // 				type: "info",
      // 				timer: 2000,
      // 			}, function(){
      // 				swal.close();
      // 			});
      // 		}
      // 	}catch(err){
      // 		swal({
      // 			title: 'Error en la base de datos',
      // 			type: "warning",
      // 			timer: 2000,
      // 		}, function(){
      // 			swal.close();
      // 		});
      // 	}
      // })
      // .fail(function( jqXHR, textStatus, errorThrown ) {
      // 	if ( console && console.log ) {
      // 		console.log( "La solicitud validar permisos tab contratos a fallado: " +  textStatus);
      // 	}
      // })
    });

  //		/ACTIVE TAB

  $(".no_permiso")
    .off()
    .click(function (event) {
      swal({
        title: "Accion no realizada",
        text: "No tiene permiso para realizar esta acción",
        type: "warning",
        closeOnConfirm: true,
      });
    });

  $(".table_to_xls_btn")
    .off()
    .click(function (event) {
      table_to_xls($(this));
    });

  $(".swal_alert")
    .off()
    .click(function (event) {
      var text = $(this).data("text");
      if (text) {
        swal(text);
      }
    });
}
function select_add(btn) {
  console.log("select_add");
  //loading(true);
  var save_data = Object();
  $(".select_new_save_data").each(function (index, el) {
    save_data[$(el).data("col")] = $(el).val();
  });
  save_data.values = Object();
  $(".input_text").each(function (index, i_el) {
    //console.log(i_el);
    //console.log($(i_el).data("col").search(save_data["table"]+"_"));
    if ($(i_el).data("col")) {
      if (
        $(i_el)
          .data("col")
          .search(save_data["table"] + "_") >= 0
      ) {
        save_data.values[
          $(i_el)
            .data("col")
            .replace(save_data["table"] + "_", "")
        ] = $(i_el).val();
      }
    }
  });
  console.log(save_data);

  $("#select_add_dialog_modal").modal("hide");
  var temp_select_data = Object();
  $("#" + btn.data("select") + " option").each(function (index, el) {
    var tmp_option = Object();
    tmp_option.value = $(el).val();
    tmp_option.html = $(el).html();
    temp_select_data[index] = tmp_option;
  });
  $("#" + btn.data("select")).html("");
  $.each(temp_select_data, function (index, val) {
    $("#" + btn.data("select")).append(
      $("<option>", {
        value: val.value,
        text: val.html,
      })
    );
  });
  /*
	$(".input_text_validacion").each(function(index, el) {
		save_data.filtro[$(el).attr("data-col")]=$(el).val();
	});
	*/
  save_data.usr = $("#hd_txt_usuario").val();
  $.post(
    "sys/set_data.php",
    {
      opt: "save_item",
      data: save_data,
    },
    function (r, textStatus, xhr) {
      try {
        console.log("save_item:ready");
        // console.log("personal abc");
        console.log(r);
        var response = jQuery.parseJSON(r);
        console.log(response);

        save_data.id = response.item_id;
        auditoria_send({ proceso: "select_add", data: save_data });

        var new_select_option_text = response.data.values.nombre;
        if (response.data.values.razon_social) {
          new_select_option_text = response.data.values.razon_social;
        }

        //new_select_option_text = "Formula "

        $("#" + btn.data("select")).append(
          $("<option>", {
            value: response.item_id,
            text: new_select_option_text,
            selected: "selected",
          })
        );
        loading();

        if ((save_data.table = "tbl_facturacion_formulas")) {
          //var producto_id =
          //$("#select-formula_id-"+producto_id).change();
          $("#" + btn.data("select")).change();
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
          }
        );
      }
    }
  );
}
function select_add_dialog(btn) {
  console.log("select_add_dialog");
  var data = btn.data();
  $.get(
    "sys/build_html.php",
    {
      opt: "select_add_dialog",
      data: data,
    },
    function (r) {
      try {
        //console.log(r);
        $("#select_add_dialog_modal").remove();
        $("body").append(r);
        $("#select_add_dialog_modal")
          .modal({})
          .on("show.bs.modal", function (e) {
            $("#select_add_dialog_modal .input_text[type=text]")
              .first()
              .focus();
            console.log(
              $("#select_add_dialog_modal .input_text[type=text]").first()
            );
          })
          .on("hidden.bs.modal", function (e) {
            $("#select_add_dialog_modal").remove();
          })
          .modal("show");
        btn_settings();
        btn_events();
      } catch (err) {
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
function add_child_dialog(btn) {
  console.log("add_child_dialog");
  //alert(btn.data("sistema-id"));
  var sistema_id = btn.data("sistema-id");
  /*$("#select-sistema_id option:selected").removeAttr("selected");
	$("#select-sistema_id option[value='"+sistema_id+"']").attr("selected","selected");
	$("#select-sistema_id").change();*/
  $("#varchar_sistema_id").val(sistema_id);
  get_nombre_sistema(sistema_id);
  get_nombre_seccion_sub_seccion(btn);

  var grupo_id = btn.data("grupo-id");
  $("#varchar_relacion_grupo_id").val(grupo_id);
  $("#varchar_grupo_id").val(grupo_id + 1);
  $("#varchar-sec_id").val(btn.data("sec_id"));

  /*$("#select-grupo_id option:selected").removeAttr("selected");
	$("#select-grupo_id option[value='"+(grupo_id+1)+"']").attr("selected","selected");*/

  var id = btn.data("id");
  $("#varchar_relacion_id").val(id);

  console.log(btn);
  $("#" + btn.data("target")).modal({
    show: true,
    keyboard: true,
  });
}

function switch_data(btn) {
  var data = Object();
  data.table = btn.attr("data-table");
  data.id = btn.attr("data-id");
  data.col = btn.attr("data-col");
  data.view = btn.attr("data-view");
  if (btn.prop("checked")) {
    data.val = btn.attr("data-on-value");
    btn.val(1);
  } else {
    data.val = btn.attr("data-off-value");
    btn.val(0);
  }
  if (
    document.getElementById("idTableLocaleUserOperations") !== null &&
    !btn.prop("checked")
  ) {
    //if(!btn.prop("checked")){
    let set_data = data;
    set_data.id_switch = data.id;
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
              //auditoria_send({ proceso: "switch_data", data: data });
              if (
                document.getElementById("idTableLocaleUserOperations") !== null
              ) {
                //se trata de una llamada desde locales-usuarios
                let id_usuario = set_data.id_switch;
                let valor_check = btn[0].checked; //true, false
                locales_activate_usuarioCustom(id_usuario, valor_check);
              }
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
          $("#checkbox_" + data.id + "").bootstrapToggle("on");
          swal("No desactivado", "El usuario continuará activo", "info");
        }
      }
    );
    //}
  } else {
    auditoria_send({ proceso: "switch_data", data: data });
    $.post(
      "sys/set_data.php",
      {
        opt: "switch_data",
        data: data,
      },
      function (r, textStatus, xhr) {
        var res = JSON.parse(r);
        console.log(res);
        if (res.switch_data.mysqli_error) {
          alert(res.switch_data.mysqli_error);
        }
        if (
          data.table == "tbl_usuarios" &&
          data.col == "estado" &&
          data.view == "listar"
        ) {
          if (data.val == 1) {
            alertify.success("El personal se ha dado de ALTA.", 5);
          } else if (data.val == 0) {
            alertify.warning("El personal se ha dado de BAJA.", 5);
          }
        }

        if (document.getElementById("idTableLocaleUserOperations") !== null) {
          //se trata de una llamada desde locales-usuarios
          let id_usuario = res.switch_data.data.id;
          let valor_check = btn[0].checked; //true, false
          locales_activate_usuarioCustom(id_usuario, valor_check);
        }
      }
    )
      .done(function () {
        //alert("second success");
      })
      .fail(function (r, textStatus, xhr) {
        console.log(r["switch_data"]);
        alert("error, ver consola");
      })
      .always(function () {
        //alert("finished");
      });
  }
}
//--------------------------------------------------------------------------------------------------------------------------------------//
// Funcionalidades traidas de LOCALES
//--------------------------------------------------------------------------------------------------------------------------------------//
function locales_activate_usuarioCustom(usuario_id, valor_check) {
  loading(true);
  var get_data = {};
  $(".save_data").each(function (index, el) {
    get_data[$(el).attr("data-col")] = $(el).val();
  });
  get_data.usuario_id = usuario_id;
  get_data.check = valor_check;
  // $(".save_data[name=id]").each(function(index, el) {
  // 	get_data[$(el).attr("name")]=$(el).val();
  // });
  console.log(get_data);
  $.post(
    "/sys/set_locales.php",
    {
      locales_activate_usuario: get_data,
    },
    function (r) {
      loading();
      try {
        // var obj = jQuery.parseJSON(r);
        if (valor_check)
          auditoria_send({
            proceso: "locales_activate_usuario",
            data: get_data,
          });
        else auditoria_send({ proceso: "switch_data", data: get_data });
        //auditoria_send({"proceso":"locales_activate_usuario","data":get_data});
        // console.log(r);
        loading(false);
        swal(
          {
            title: "¡Usuario actualizado de este local!",
            text: "",
            type: "success",
            closeOnConfirm: true,
          },
          function () {
            swal.close();
          }
        );
        fncDataToRenderTableLocaleUserOperationsCustom();
      } catch (err) {
        // console.log(err);
      }
      // console.log(r);
    }
  );
}

function fncDataToRenderTableLocaleUserOperationsCustom() {
  var formData = new FormData();
  formData.append("fechaInicio", $("#idFromUserOperatinsLog").val());
  formData.append("fechaFin", $("#idToUserOperatinsLog").val());
  formData.append("local_id", $("#item_id_temporal").val());
  formData.append("action", "list_logs");

  $.ajax({
    type: "POST",
    data: formData,
    url: "app/routes/UsuarioLog/",
    contentType: false,
    processData: false,
    cache: false,
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.error == true) {
      } else {
        fncRenderTableLocaleUserOperationsCustom(jsonData.data);
      }
    },
    beforeSend: function () {
      //loading(true);
    },
  });
}
function fncRenderTableLocaleUserOperationsCustom(data = {}) {
  var table = $("#idTableLocaleUserOperations").DataTable();
  table.clear();
  table.destroy();
  //loading(true);
  var table = $("#idTableLocaleUserOperations").DataTable({
    destroy: true,
    autoWidth: false,
    scrollX: true,
    lengthChange: false,
    dom: "Bfrtip",
    fnDrawCallback: function (oSettings) {
      $(function () {
        //$('[data-toggle="popover"]').popover()
      });
    },
    buttons: {
      buttons: [],
    },
    language: {
      processing:
        '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> ',
    },
    data: data,
    ordering: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
    },
    columns: [
      {
        data: "id",
        // render: function (data, type, row) {
        // 	var codigo = '[' + data + ']';
        // 	return codigo;
        // }
      },

      {
        data: "usuario",
      }, //--------------------------------------------------------------------------------------------------------------------------------------//
      {
        data: "to_user",
      },
      {
        data: "action",
      },
      {
        data: "created_at",
      },
    ],
    createdRow: function (row, data, dataIndex, cells) {
      var code_color = "";
      if (data.action == "Agregar") {
        code_color = "#93ebd0";
      } else if (data.action == "Eliminar") {
        code_color = "#eaa1a7";
      }
      $(row).css("background-color", code_color);
    },
  });
  //$('#idTableLocaleUserOperations tbody').off('click');
}
//--------------------------------------------------------------------------------------------------------------------------------------//

function add_item_dialog(e) {
  console.log(e.target);
  swal(
    {
      title: "Agregar Item",
      text: "Tabla: " + $(e.target).attr("data-table"),
      type: "input",
      showCancelButton: true,
      closeOnConfirm: false,
      animation: "slide-from-top",
      inputPlaceholder: "Nombre",
      showLoaderOnConfirm: false,
    },
    function (inputValue) {
      if (inputValue === false) return false;
      if (inputValue === "") {
        swal.showInputError("You need to write something!");
        return false;
      }
      //swal('Nice!', 'You wrote: ' + inputValue, 'success');
      var data = Object();
      data.table = $(e.target).attr("data-table");
      data.values = Object();
      data.values.nombre = inputValue;
      swal.close();
      add_item(data);
    }
  );
}

function check_local_paid(btn) {
  let save_data = {};
  save_data.opt = "check_local_paid";

  $.post(
    "sys/set_data.php",
    {
      opt: "check_local_paid",
      data: save_data,
    },
    function (r, textStatus, xhr) {
      loading();
      try {
        var response = jQuery.parseJSON(r);
        console.log(response);
        swal(
          {
            title: "Registros Actualizados",
            text: "",
            type: "success",
            timer: 300,
            closeOnConfirm: true,
          },
          function () {
            console.log(btn.data("then"));
          }
        );
      } catch (err) {
        ajax_error(true, r, err);
      }
    }
  );
}

function add_item(data) {
  console.log("add_item");
  loading(true);
  $.post(
    "sys/set_data.php",
    {
      opt: "add_item",
      data: data,
    },
    function (r, textStatus, xhr) {
      try {
        console.log("add_item:ready");
        console.log(r);
        m_reload();
      } catch (err) {
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
function save_item(btn) {
  console.log("save_item");
  loading(true);

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
  // $(".switch").each(function(index, el) {
  // 	if($(el).prop('checked')){
  // 		save_data.values[$(el).attr("data-col")]=$(el).attr("data-on-value");
  // 	}else{
  // 		save_data.values[$(el).attr("data-col")]=$(el).attr("data-off-value");
  // 	}
  // });
  //console.log(save_data);
  save_data.extra = {};
  $(".save_extra").each(function (index, el) {
    var extra = $(el).data();
    extra.val = $(el).val();
    if ($(el).attr("type") == "checkbox") {
      if ($(el).prop("checked")) {
        extra.checked = 1;
      } else {
        extra.checked = 0;
      }
    }
    save_data.extra[index] = extra;
  });
  //console.log(save_data.extra);

  save_data.lp_id = {};
  $(".lp_id").each(function (index, el) {
    var lp_id = $(el).data();
    lp_id.nombre = $(el).find("input[name=nombre]").val();
    lp_id.proveedor_id = $(el).find("input[name=proveedor_id]").val();

    save_data.lp_id[index] = lp_id;
  });
  // console.log(save_data.lp_id);

  save_data.local_qty = {};
  $(".local_qty").each(function (index, el) {
    var local_qty = $(el).data();
    local_qty.val = $(el).val();

    save_data.local_qty[index] = local_qty;
  });

  save_data.configv2 = {};
  let configv2;
  $("#tbProductos tbody tr").each(function () {
    configv2 = {};
    configv2.proveedor_id = $(this).find("#txtCajaId").val();
    configv2.nombre = $(this).find("#txtCajaDesc").val();

    save_data.configv2[$(this).find("#config_id").val()] = configv2;
  });

  // save_data.cctv_id = {};
  // var cctv_id = {};
  // 	cctv_id.id = $("input[name=id_cctv]").val();
  // 	cctv_id.username = $("input[name=username_cctv]").val();
  // 	cctv_id.password = $("input[name=password_cctv]").val();
  // 	save_data.cctv_id[0]=cctv_id;

  //console.log(save_data.configv2);
  save_data.local_credenciales = {};
  $(".credencial").each(function (index, el) {
    var cred_id = {};
    cred_id.id = el.getAttribute("data-id");
    cred_id.local_id = el.getAttribute("data-id_local");
    cred_id.campo_tipo_credencial_id = el.getAttribute("data-id_campo");
    cred_id.valor = el.value;
    save_data.local_credenciales[index] = cred_id;
  });
  //Datos de servicios del local
  save_data.servicio_id = $("input[name=servicio_id]").val();
  //save_data.internet_proveedor_id = $("select[name=internet_proveedor_id]").val() !== null && $("select[name=internet_proveedor_id]").val() !== undefined ? $("select[name=internet_proveedor_id]").val() : 0;
  save_data.internet_proveedor_id = $(
    "select[name=internet_proveedor_id]"
  ).val();
  save_data.internet_tipo_id = $("select[name=internet_tipo_id]").val();
  save_data.num_decos_directv = $("input[name=num_decos_directv]").val();
  save_data.num_decos_internet = $("input[name=num_decos_internet]").val();

  //Datos de equipos del local
  save_data.equipos_id = $("input[name=equipos_id]").val();
  // save_data.num_terminales_kasnet = $("input[name=num_terminales_kasnet]").val();
  save_data.num_tv_apuestas_virtuales = $(
    "input[name=num_tv_apuestas_virtuales]"
  ).val();
  save_data.num_tv_apuestas_deportivas = $(
    "input[name=num_tv_apuestas_deportivas]"
  ).val();

  //Datos de equipos de computo del local
  save_data.equipos_computo_id = $("input[name=equipos_computo_id]").val();
  save_data.num_cpu = $("input[name=num_cpu]").val();
  save_data.num_monitores = $("input[name=num_monitores]").val();
  save_data.num_autoservicios = $("input[name=num_autoservicios]").val();
  save_data.num_allinone = $("input[name=num_allinone]").val();
  save_data.num_terminales_hibrido = $(
    "input[name=num_terminales_hibrido]"
  ).val();
  save_data.num_terminales_antiguo = $(
    "input[name=num_terminales_antiguo]"
  ).val();

  save_data.usr = $("#hd_txt_usuario").val();
  $.post(
    "sys/set_data.php",
    {
      opt: "save_item",
      data: save_data,
    },
    function (r, textStatus, xhr) {
      console.log("save_item:ready");
      console.log(save_data);
      loading();
      try {
        var response = jQuery.parseJSON(r);
        console.log(response);

        if (response.error_msg != undefined) {
          swal(
            {
              title: "Error!",
              text: response.error_msg,
              type: "warning",
              timer: 3000,
              closeOnConfirm: true,
            },
            function () {
              swal.close();
              setTimeout(function () {
                $(
                  "div[data-id='" + response.focus + "'] [name='proveedor_id']"
                ).focus();
              }, 500);
            }
          );
          return false;
        }

        // console.log(response.permisos);
        // if (response.permisos==true) {
        swal(
          {
            title: "Guardado",
            text: "",
            type: "success",
            timer: 300,
            closeOnConfirm: false,
          }
          // function () {
          //   console.log(btn.data("then"));
          //   if (btn.data("then") == "reload") {
          //     if (save_data["id"] == "new") {
          //       save_data.id = response.item_id;
          //       auditoria_send({ proceso: "add_item", data: save_data });
          //       window.location =
          //         "./?sec_id=" +
          //         sec_id +
          //         "&sub_sec_id=" +
          //         sub_sec_id +
          //         "&item_id=" +
          //         response.item_id;
          //     } else {
          //       auditoria_send({ proceso: "save_item", data: save_data });
          //       swal.close();
          //       m_reload();
          //     }
          //   } else if (btn.data("then") == "force_reload") {
          //     auditoria_send({ proceso: "save_item", data: save_data });
          //     m_reload();
          //   } else if (btn.data("then") == "exit") {
          //     auditoria_send({ proceso: "save_item", data: save_data });
          //     window.location =
          //       "./?sec_id=" + sec_id + "&sub_sec_id=" + sub_sec_id;
          //   } else {
          //   }
          //   swal.close();
          // }
        );
        // }else{
        // 	swal({
        // 		title: 'No tienes permisos',
        // 		type: "warning",
        // 		timer: 2000,
        // 	}, function(){
        // 		swal.close();

        // 	});
        // }
      } catch (err) {
        ajax_error(true, r, err);
        // swal({
        // 	title: 'Error en la base de datos',
        // 	type: "info",
        // 	timer: 2000,
        // }, function(){
        // 	swal.close();
        // });
        // console.log(r);
      }
    }
  );
}
function del_item_dialog(btn) {
  var data = Object();
  data.filtro = Object();
  data.where = "validar_usuario_permiso_botones";
  $(".input_text_validacion").each(function (index, el) {
    data.filtro[$(el).attr("data-col")] = $(el).val();
  });
  data.filtro.text_btn = btn.data("button");
  console.log(data);
  auditoria_send({ proceso: "validar_usuario_permiso_botones", data: data });
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
          swal(
            {
              title: "Seguro?",
              text: "Una vez eliminado no se podrá recuperar!",
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Si, borrar!",
              cancelButtonText: "No",
              closeOnConfirm: false,
            },
            function () {
              var data = Object();
              data.table = btn.data("table");
              data.id = btn.data("id");
              del_item(data, btn);
              //console.log(data);
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
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      if (console && console.log) {
        console.log(
          "La solicitud validacion eliminar usuario a fallado: " + textStatus
        );
      }
    });
}
function del_item(data, btn) {
  //console.log("del_item");
  //loading(true);
  //console.log(data);
  $.post(
    "sys/set_data.php",
    {
      opt: "del_item",
      data: data,
    },
    function (r, textStatus, xhr) {
      //console.log("del_item:ready");
      //console.log(r);
      swal(
        {
          title: "Eliminado",
          text: "El archivo ha sido eliminado.",
          type: "success",
          timer: 2000,
          closeOnConfirm: false,
        },
        function () {
          if (btn.data("then") == "reload") {
            auditoria_send({ proceso: "del_item", data: data });
            swal.close();
            m_reload();
          } else if (btn.data("then") == "exit") {
            auditoria_send({ proceso: "del_item", data: data });
            window.location =
              "./?sec_id=" + sec_id + "&sub_sec_id=" + sub_sec_id;
          } else {
          }
          swal.close();
        }
      );
    }
  );
}

function auditoria_send_post(d) {
  // console.log("auditoria_send_post");
  // console.log(d);
  $.post(
    "sys/sys_auditoria.php",
    {
      opt: "auditoria_send",
      data: d,
    },
    function (r, textStatus, xhr) {
      // console.log("auditoria_send:ready");
      // console.log(r);
    }
  );
}

function auditoria_send(data) {
  //   // console.log("auditoria_send");
  //   // console.log(data);
  //   if (!data) {
  //     data = {};
  //   }
  //   if (!data.proceso) {
  //     data.proceso = "visita";
  //   }
  //   if (!data.sec_id) {
  //     data.sec_id = sec_id;
  //   }
  //   if (!data.sub_sec_id) {
  //     data.sub_sec_id = sub_sec_id;
  //   }
  //   if (!data.item_id) {
  //     data.item_id = item_id;
  //   }
  //   if (!data.url) {
  //     data.url = window.location.href;
  //   }
  //   if (navigator.geolocation) {
  //     // console.log("geo OK");
  //     var gps = navigator.geolocation.getCurrentPosition(
  //       function (position) {
  //         var gl = {};
  //         gl.latitude = position.coords.latitude;
  //         gl.longitude = position.coords.longitude;
  //         gl.altitude = position.coords.altitude;
  //         gl.accuracy = position.coords.accuracy;
  //         gl.altitudeAccuracy = position.coords.altitudeAccuracy;
  //         gl.heading = position.coords.heading;
  //         gl.speed = position.coords.speed;
  //         var gl_json = JSON.stringify(gl);
  //         data.geolocation = gl_json;
  //         auditoria_send_post(data);
  //       },
  //       function (err) {
  //         // console.log(err);
  //         switch (err.code) {
  //           case err.PERMISSION_DENIED:
  //             // console.log("User denied the request for Geolocation.");
  //             // $(".anuncio_holder").removeClass("hidden");
  //             // var txt = "Por favor habilitar la ubicación.";
  //             var txt = "";
  //             txt +=
  //               '<video width="400" controls><source src="files/howlocation2.mp4" type="video/mp4">Para continuar debes habilitar la ubicación.<br> Por favor haz click en el botón de abajo<br> para aprender cómo hacerlo.</video>';
  //             // txt+= "Para continuar debes habilitar la ubicación.<br> Por favor haz click en el botón de abajo<br> para aprender cómo hacerlo.";
  //             // txt+= "<br>";
  //             // txt+= "Aprende cómo hacerlo en el siguiente enlace:";
  //             // txt+= "\n";
  //             // txt+= "<a style='color:#fff;' href='https://support.google.com/chrome/answer/114662' target='_blank'>";
  //             // txt+= "-> Cómo cambiar la configuración de un sitio específico <-";
  //             // txt+= "</a>";
  //             // txt+= "<br>";
  //             // txt+= "A partir del 27 de Enero del 2020<br>se bloqueará el acceso a usuarios que no tengan la ubicación habilitada.";
  //             // txt+= "<br>";
  //             // txt+= "<br>";
  //             // $(".anuncio_holder div").html(txt);
  //             // $("#loading_box").show();
  //             swal(
  //               {
  //                 title: "¡Ubicación no habilitada!",
  //                 text: txt,
  //                 type: "warning",
  //                 showCancelButton: false,
  //                 closeOnConfirm: false,
  //                 html: 1,
  //                 confirmButtonText: "Aprende cómo habilitar aquí",
  //                 allowEscapeKey: 0,
  //                 // animation: 'slide-from-top',
  //                 // inputPlaceholder: 'Nombre',
  //                 // showLoaderOnConfirm: false
  //               },
  //               function (inputValue) {
  //                 console.log(inputValue);
  //                 // window.location.href = "https://support.google.com/chrome/answer/114662";
  //                 window.open(
  //                   "https://support.google.com/chrome/answer/114662",
  //                   "_blank" // <- This is what makes it open in a new window.
  //                 );
  //                 // if (inputValue === false) return false;
  //                 // if (inputValue === '') {
  //                 // 	swal.showInputError('You need to write something!');
  //                 // 	return false;
  //                 // }
  //                 //swal('Nice!', 'You wrote: ' + inputValue, 'success');
  //                 // var data = Object();
  //                 // data.table = $(e.target).attr("data-table");
  //                 // data.values = Object();
  //                 // data.values.nombre = inputValue;
  //                 // swal.close();
  //                 // add_item(data);
  //               }
  //             );
  //             // swal({
  //             // 	title: "Por favor habilitar la ubicación",
  //             // 	text: txt,
  //             // 	icon: "error",
  //             // 	dangerMode: false,
  //             // });
  //             // $(".swal_alert").show();
  //             // .off()
  //             // .click(function(event) {
  //             // 	var text = $(this).data("text");
  //             // 	if(text){
  //             // 		swal(text);
  //             // 	}
  //             // });
  //             // 						swal("Are you sure you want to do this?", {
  //             //   buttons: ["Oh noez!", "Aww yiss!"],
  //             // });
  //             // 						swal({
  //             //   title: "Here's a title!",
  //             // });
  //             break;
  //           case err.POSITION_UNAVAILABLE:
  //             // console.log("Location information is unavailable.");
  //             break;
  //           case err.TIMEOUT:
  //             // console.log("The request to get user location timed out.");
  //             break;
  //           case err.UNKNOWN_ERROR:
  //             // console.log("An unknown error occurred.");
  //             break;
  //         }
  //         var gl = {};
  //         gl.error = err.message;
  //         var gl_json = JSON.stringify(gl);
  //         data.geolocation = gl_json;
  //         auditoria_send_post(data);
  //       }
  //     );
  //   } else {
  //     // console.log("geo NO");
  //     auditoria_send_post(data);
  //   }
  //   // console.log(data);
  //   // $.post('sys/sys_auditoria.php', {
  //   // 	"opt": 'auditoria_send'
  //   // 	,"data":data
  //   // }, function(r, textStatus, xhr) {
  //   // 	//console.log("auditoria_send:ready");
  //   // 	// console.log(r);
  //   // });
}

/* utils */
var loading_state = false;
var loading_dots = 0;
function loading(op, debug) {
  if (op) {
    //console.log("loading:show");
    $(".loading_box").html("").show();
    if (debug) {
      console.log("loading...");
    }
    loading_dots = 0;
    loading_state = true;
  } else {
    loading_state = false;
    //console.log("loading:hide");
    $(".loading_box").html("").hide();
    //if(debug){ console.log("loading:DONE"); }
  }
  // tmp_title = $(document).prop('title')
  loading_title();
}
function loading_title() {
  // console.log("loading_title");

  if (loading_state) {
    if (loading_dots < 3) {
      loading_dots = loading_dots + 1;
    } else {
      loading_dots = 1;
    }
    var new_title = "Cargando";
    for (i = 1; i <= loading_dots; i++) {
      new_title += ".";
    }
    // new_title=new_title+" "+site_title;

    // console.log(new_title);
    setTimeout(loading_title, 1000);
    // if(new_title.indexOf('>>>>>')== -1){
    // 	$(document).prop('title', '>'+new_title);
    // }else{
    // 	$(document).prop('title', '>'+tmp_title);
    // }
    $(document).prop("title", new_title + " " + site_title);
  } else {
    loading_dots = 0;
    $(document).prop("title", site_title);
  }
}
function ajax_error(opt, r, err) {
  console.log("ajax_error");
  if (opt) {
    var send_data = {};
    if (r) {
      console.log(r);
      send_data.response = r;
    }
    if (err) {
      send_data.error = err;
      console.log(err);
    }
    console.log(send_data);
    auditoria_send({ proceso: "ajax_error", data: send_data });
    swal(
      {
        title: "¡Atención!",
        text: r,
        type: "info",
        /*timer: 2000,*/
      },
      function () {
        swal.close();
        loading();
      }
    );
    // loading();
  }
}
function m_reload() {
  console.log("m_reload:reload");
  loading(true);
  window.location.reload();
}
function go_to(u) {
  console.log("go_to:" + u);
  window.location = u;
}
function table_to_xls(btn) {
  /*console.log("table_to_xls");
	console.log(btn);
	var data = Object();
	data.table = $("."+$(btn).data("table"));*/

  //e.preventDefault();

  //getting data from our table
  var data_type = "data:application/vnd.ms-excel";
  var table_div = document.getElementById("export_table_holder");
  var table_html = table_div.outerHTML.replace(/ /g, "%20");

  var a = document.createElement("a");
  a.href = data_type + ", " + table_html;
  a.download =
    "exported_table_" + Math.floor(Math.random() * 9999999 + 1000000) + ".xls";
  a.click();

  /*$.post('export.php', {
		"opt": 'table_to_xls'
		,"data":data
	}, function(r, textStatus, xhr) {
		//console.log("auditoria_send:ready");
		console.log(r);
	});
	console.log(table.html());*/
}
function bytesToSize(bytes) {
  var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
  if (bytes == 0) return "0 Byte";
  var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
  return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
}
/* /utils*/
function validar_permiso_usuario(cod_btn, sec_id, sub_sec_id) {
  var data = {};
  data.where = "validar_usuario_permiso_botones";
  data.filtro = {};
  data.filtro.text_btn = cod_btn;
  data.filtro.sec_id = sec_id;
  data.filtro.sub_sec_id = sub_sec_id;
  auditoria_send({ proceso: "validar_usuario_permiso_botones", data: data });
  $.ajax({
    data: data,
    type: "POST",
    dataType: "json",
    url: "/api/?json",
    async: "false",
  })
    .done(function (dataresponse) {
      $(document).trigger({
        type: "evento_validar_permiso_usuario",
        event_data: dataresponse.permisos,
      });
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      if (console && console.log) {
        console.log(jqXHR);
      }
    });
}
function get_nombre_seccion_sub_seccion(btn) {
  var sec_sub_sec_id = btn.data("id");
  var data = {};
  data.what = {};
  data.what[0] = "titulo";
  data.where = "menu_sistema";
  data.filtro = {};
  data.filtro.sec_sub_sec_id = sec_sub_sec_id;
  auditoria_send({ proceso: "sec_mantenimientos_get_sec_sub_sec", data: data });
  $.ajax({
    url: "/api/?json",
    type: "POST",
    dataType: "json",
    data: data,
  })
    .done(function (dataresponse, textStatus, jqXHR) {
      //console.log(dataresponse);
      $("#sec_sub_sec_descipcion").text(dataresponse.data.titulo);
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      console.log("error");
    })
    .always(function () {
      console.log("complete");
    });
}
function get_nombre_sistema(sistema_id) {
  var data = {};
  data.filtro = {};
  data.filtro.sistema_id = sistema_id;
  data.opt = "get_nombre_sistema";
  $.ajax({
    url: "sys/get_data.php",
    type: "POST",
    data: data,
  })
    .done(function (dataresponse, textStatus, jqXHR) {
      //console.log(dataresponse);
      var obj = JSON.parse(dataresponse);
      $("#varchar_sistema_nombre").val(obj.data.nombre);
    })
    .fail(function () {
      console.log("error");
    })
    .always(function () {
      console.log("complete");
    });
}

function validate_null_number(value) {
  if (value == null) {
    return 0;
  } else {
    return value;
  }
}
function validate_null_string(value) {
  if (value == null) {
    return "";
  } else {
    return value;
  }
}

function custom_highlight(o, c) {
  // console.log("hello");
  $(o).addClass("bg-danger", 500);
  setTimeout(function () {
    // console.log("bye");
    $(o).finish().removeClass("bg-danger", 1500);
  }, 1500);
}

function debounce(callback, wait) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      callback.apply(this, args);
    }, wait);
  };
}
