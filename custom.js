//var ls = localStorage;

var sec_id = $("#sec_id").val();
var sub_sec_id = $("#sub_sec_id").val();
var item_id = $("#item_id").val();
var url_object = {};

$(document).ready(function () {
  $(window).scroll(function (event) {
    var scroll = $(window).scrollTop();
    console.log(scroll);

    if (scroll >= 159) {
      $(".fixedHeader-floating").show();
    } else {
      $(".fixedHeader-floating").hide();
    }
  });

  url_to_object();

  //window.resizeTo(300,400);
  //console.log("custom.js:ready");
  btn_settings();
  btn_events();
  //loading(false);

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

  var table = $("#contratos_list").DataTable({
    fixedHeader: {
      header: true,
      footer: true,
    },
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
    aLengthMenu: arr_aLengthMenu,
    order: [[0, "desc"]],
  });

  init_sort();

  $(".datepicker")
    .datepicker({
      format: "dd-mm-yyyy",
      autoclose: true,
    })
    .on("show", function (ev) {
      console.log($(this));
    })
    .on("changeDate", function (ev) {
      $(this).datepicker("hide");
      var newDate = new Date(ev.date);
      $("input[data-real-date=" + $(this).attr("id") + "]").val(
        $.format.date(newDate, "yyyy-MM-dd")
      );
    });

  $("#trrrrrrreeeeeee").treegrid({
    initialState: "collapsed",
    saveState: true,
  });

  //var mmm = moment();
  //console.log(mmm);

  /*$('#html1')
	.on('select_node.jstree', function (e, data) {
		console.log(data.node.a_attr.href);
		window.location=data.node.a_attr.href;
	})
	.jstree();*/

  sec_afiliarse();
  sec_contratos();
  sec_clientes();
  sec_recaudacion();
  sec_locales();
  sec_reportes_agentes();

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
            console.log("sort_list:ready");
            console.log(r);
            //m_reload();
          }
        );
        console.log(list);
      },
    });
  });
}

function set_localstorage_data() {
  /*var $arr={};
	parse_str(parse_url(window.location.href,"query"),$arr);
	$.each($arr, function(index, val) {
		localStorage.setItem(index,val);
	});*/
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
  $("select.input_text ").each(function (index, el) {
    if ($(el).data("col") == "tbl_clientes_tipo_cliente_id") {
      $(el)
        //.off("change")
        .change(function (event) {
          console.log($(el).val());
          //$(".hidden_form").hide();
          if ($(el).val() == 1) {
            //Persona Natural
            //$(".hidden_form_"+$(el).val()).show();
            //$(".hidden_form_all").show();
            $(".form-group-dni").show();
            $(".form-group-nombre").show();
            $(".form-group-ruc").hide();
            $(".form-group-razon_social").hide();
            //$('input[type=text][name=nombre]').attr('required','required');
            //$('input[type=text][name=dni]').attr('required','required');
            //$('input[type=text][name=ruc]').removeAttr('required');
            //$('input[type=text][name=razon_social]').removeAttr('required');
          } else if ($(el).val() == 2) {
            //Persona Jurídica
            //$(".hidden_form_"+$(el).val()).show();
            //$(".hidden_form_all").show();
            $(".form-group-dni").hide();
            $(".form-group-nombre").hide();
            $(".form-group-ruc").show();
            $(".form-group-razon_social").show();
            //$('input[type=text][name=nombre]').removeAttr('required');
            //$('input[type=text][name=dni]').removeAttr('required');
            //$('input[type=text][name=ruc]').attr('required','required');
            //$('input[type=text][name=razon_social]').attr('required','required');
          } else {
          }
        });
    }
  });

  $("select[name=ubigeo_departamento]")
    .off()
    .change(function (event) {
      loading(true);
      //$("select[name=ubigeo_provincia]").append($("<option>").html("Seleccione una Provincia").val(0));
      $("select[name=ubigeo_distrito]").html("");
      $("select[name=ubigeo_distrito]").append(
        $("<option>").html("- Seleccione una Provincia -").val("")
      );
      $("select[name=ubigeo_distrito]").attr("disabled", "disabled");
      //Seleccione Departamento
      var data = Object();
      data.departamento_id = $(this).val();
      //console.log(data);
      $.get(
        "sys/build_html.php",
        {
          opt: "select_ubigeo_departamento",
          data: data,
        },
        function (r) {
          //console.log(r);
          var response = jQuery.parseJSON(r);
          //console.log(response);
          $("select[name=ubigeo_provincia]").html("");
          $("select[name=ubigeo_provincia]").append(
            $("<option>").html("Seleccione una Provincia").val("")
          );
          $.each(response, function (index, val) {
            $("select[name=ubigeo_provincia]").append(
              $("<option>").html(val.nombre).val(val.cod)
            );
          });
          $("select[name=ubigeo_provincia]").removeAttr("disabled");
          loading();
          $("select[name=ubigeo_provincia]")
            .off()
            .change(function (event) {
              loading(true);
              //var data = Object();
              data.provincia_id = $(this).val();
              //console.log(data);
              $.get(
                "sys/build_html.php",
                {
                  opt: "select_ubigeo_provincia",
                  data: data,
                },
                function (r) {
                  //console.log(r);
                  var response = jQuery.parseJSON(r);
                  //console.log(response);
                  $("select[name=ubigeo_distrito]").html("");
                  $("select[name=ubigeo_distrito]").append(
                    $("<option>").html("- Seleccione un Distrito -").val("")
                  );
                  $.each(response, function (index, val) {
                    $("select[name=ubigeo_distrito]").append(
                      $("<option>").html(val.nombre).val(val.cod)
                    );
                  });
                  $("select[name=ubigeo_distrito]").removeAttr("disabled");
                  loading();
                }
              );
            });
        }
      );
    });

  $("select[name=ubigeo_provincia]")
    .off()
    .change(function (event) {
      loading(true);
      var data = Object();
      data.departamento_id = $("select[name=ubigeo_departamento]").val();
      data.provincia_id = $(this).val();
      //console.log(data);
      $.get(
        "sys/build_html.php",
        {
          opt: "select_ubigeo_provincia",
          data: data,
        },
        function (r) {
          //console.log(r);
          var response = jQuery.parseJSON(r);
          //console.log(response);
          $("select[name=ubigeo_distrito]").html("");
          $("select[name=ubigeo_distrito]").append(
            $("<option>").html("- Seleccione un Distrito -").val("")
          );
          $.each(response, function (index, val) {
            $("select[name=ubigeo_distrito]").append(
              $("<option>").html(val.nombre).val(val.cod)
            );
          });
          $("select[name=ubigeo_distrito]").removeAttr("disabled");
          loading();
        }
      );
    });

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
    .off()
    .click(function (event) {
      save_item($(this));
    });
  $(".switch")
    .off()
    .change(function (event) {
      console.log(event);
      switch_data($(event.target));
    });
  $(".del_btn")
    .off()
    .click(function (event) {
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
          console.log("save_adm_inputs:ready");
          console.log(r);
          var response = jQuery.parseJSON(r);
          console.log(response);
          m_reload();
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
    .click(function (event) {
      //.modal("show");
      var target = $(this).data("target");
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
    });

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
    .click(function (event) {
      event.preventDefault();
      console.log("btn-preview:click");
      var data = Object();
      data.table = $(this).data("table");
      data.id = $(this).data("id");
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
      event.preventDefault();
      var tab = $(this).data("tab");
      if (item_id == "new") {
        swal("Por favor guarde antes de cambiar de pestaña.");
      } else {
        window.location.hash = "tab=" + tab;
        $(this).tab("show");
      }
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
  $.post(
    "sys/set_data.php",
    {
      opt: "save_item",
      data: save_data,
    },
    function (r, textStatus, xhr) {
      console.log("save_item:ready");
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
      //console.log(r);
      $("#select_add_dialog_modal").remove();
      $("body").append(r);
      $("#select_add_dialog_modal")
        .modal({})
        .on("show.bs.modal", function (e) {
          $("#select_add_dialog_modal .input_text[type=text]").first().focus();
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
    }
  );
}
function add_child_dialog(btn) {
  console.log("add_child_dialog");

  var sistema_id = btn.data("sistema-id");
  /*$("#select-sistema_id option:selected").removeAttr("selected");
	$("#select-sistema_id option[value='"+sistema_id+"']").attr("selected","selected");
	$("#select-sistema_id").change();*/
  $("#varchar_sistema_id").val(sistema_id);

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
  console.log("switch_data");
  console.log(btn);
  var data = Object();
  data.table = btn.attr("data-table");
  data.id = btn.attr("data-id");
  data.col = btn.attr("data-col");
  if (btn.prop("checked")) {
    data.val = btn.attr("data-on-value");
    btn.val(1);
  } else {
    data.val = btn.attr("data-off-value");
    btn.val(0);
  }
  console.log(data);
  auditoria_send({ proceso: "switch_data", data: data });
  $.post(
    "sys/set_data.php",
    {
      opt: "switch_data",
      data: data,
    },
    function (r, textStatus, xhr) {
      console.log("switch_data:ready");
      console.log(r);
    }
  );
}
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
function add_item(data) {
  console.log("add_item");
  loading(true);
  console.log(data);
  $.post(
    "sys/set_data.php",
    {
      opt: "add_item",
      data: data,
    },
    function (r, textStatus, xhr) {
      console.log("add_item:ready");
      console.log(r);
      m_reload();
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
  $(".switch").each(function (index, el) {
    if ($(el).prop("checked")) {
      save_data.values[$(el).attr("data-col")] = $(el).attr("data-on-value");
    } else {
      save_data.values[$(el).attr("data-col")] = $(el).attr("data-off-value");
    }
  });

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
  console.log(save_data.lp_id);

  $.post(
    "sys/set_data.php",
    {
      opt: "save_item",
      data: save_data,
    },
    function (r, textStatus, xhr) {
      console.log("save_item:ready");
      loading();
      console.log(r);
      var response = jQuery.parseJSON(r);
      console.log(response);

      swal(
        {
          title: "Guardado",
          text: "",
          type: "success",
          timer: 800,
          closeOnConfirm: false,
        },
        function () {
          console.log(btn.data("then"));
          if (btn.data("then") == "reload") {
            if (save_data["id"] == "new") {
              save_data.id = response.item_id;
              auditoria_send({ proceso: "add_item", data: save_data });
              window.location =
                "./?sec_id=" +
                sec_id +
                "&sub_sec_id=" +
                sub_sec_id +
                "&item_id=" +
                response.item_id;
            } else {
              auditoria_send({ proceso: "save_item", data: save_data });
              swal.close();
              m_reload();
            }
          } else if (btn.data("then") == "force_reload") {
            auditoria_send({ proceso: "save_item", data: save_data });
            m_reload();
          } else if (btn.data("then") == "exit") {
            auditoria_send({ proceso: "save_item", data: save_data });
            window.location =
              "./?sec_id=" + sec_id + "&sub_sec_id=" + sub_sec_id;
          } else {
          }
          swal.close();
        }
      ); /**/
    }
  ); /**/
}
//save_item();
function del_item_dialog(btn) {
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

function auditoria_send(data) {
  //console.log("auditoria_send");
  if (!data) {
    data = {};
  }
  if (!data.proceso) {
    data.proceso = "visita";
  }
  if (!data.sec_id) {
    data.sec_id = sec_id;
  }
  if (!data.sub_sec_id) {
    data.sub_sec_id = sub_sec_id;
  }
  if (!data.item_id) {
    data.item_id = item_id;
  }
  if (!data.url) {
    data.url = window.location.href;
  }

  //console.log(data);
  $.post(
    "sys/sys_auditoria.php",
    {
      opt: "auditoria_send",
      data: data,
    },
    function (r, textStatus, xhr) {
      //console.log("auditoria_send:ready");
      //console.log(r);
    }
  );
}

/* utils */
function loading(op) {
  if (op) {
    //console.log("loading:show");
    $(".loading_box").html("").show();
  } else {
    //console.log("loading:hide");
    $(".loading_box").html("").hide();
  }
}
function m_reload() {
  console.log("m_reload:reload");
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
