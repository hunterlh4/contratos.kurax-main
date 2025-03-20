function sec_contrato_responsable_de_area() {
  sec_con_res_ar_listar_responsable_area();

  // SELECT2 AJAX
  $("#sec_con_new_res_ar_responsable_area").select2({
    ajax: {
      url: "sys/set_contrato_responsables_de_area.php",
      type: "POST",
      data: function (params) {
        var query = {
          search: params.term,
          accion: "obtener_responsables_de_area",
        };
        return query;
      },
      processResults: function (info) {
        var data = JSON.parse(info);
        return {
          results: $.map(data.result, function (item) {
            return {
              text: item.text,
              id: item.id,
            };
          }),
        };
      },
    },
    width: "100%",
    placeholder: "Seleccionar un responsable de Área",
    minimumInputLength: 2,
    language: {
      inputTooShort: function () {
        return 'Por favor ingrese 2 o más caracteres';
      }
    }
  });

  $("#frm_responsable_area").submit(function (e) {
    e.preventDefault();
    var responsable_area = $("#sec_con_new_res_ar_responsable_area").val();
    if (responsable_area == null) {
      alertify.warning("Seleccione un responsable de área.", 5);
      return;
    }
    sec_con_res_ar_guardar_responsable_area();
  });
}

function sec_con_res_ar_guardar_responsable_area() {
  var responsable_area = $("#sec_con_new_res_ar_responsable_area").val();

  var data = {
    accion: "guardar_responsables_de_area",
    responsable_area: responsable_area,
  };
  $.ajax({
    url: "/sys/set_contrato_responsables_de_area.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      let response = JSON.parse(resp);
      if (response.status == 200) {
        $("#sec_con_new_res_ar_responsable_area").empty().trigger("change");
        alertify.success(response.message, 5);
        sec_con_res_ar_listar_responsable_area();
      } else {
        alertify.error(response.message, 5);
      }
    },
    error: function (resp, status) {},
  });
}

function sec_con_res_ar_listar_responsable_area() {
  var data = {
    accion: "listar_responsables_de_area",
  };
  $.ajax({
    url: "/sys/set_contrato_responsables_de_area.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      let response = JSON.parse(resp);
      if (response.status == 200) {
        fnc_render_table_responsables_area(response.result);
      }
    },
    error: function (resp, status) {},
  });
}

function fnc_render_table_responsables_area(data = []) {
  $("#tbl-responsable-area")
    .dataTable({
      bDestroy: true,
      data: data,
      columns: [
        { data: "index", className: "text-center" },
        { data: "nombre", className: "text-left" },
        { data: "estado", className: "text-center" },
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
      },
      scrollY: false,
    })
    .DataTable();

  $(".switch-responsable-area").bootstrapToggle({
    on: "activo",
    off: "inactivo",
    onstyle: "success",
    offstyle: "danger",
    size: "mini",
  });

  $(".switch-responsable-area")
    .off()
    .change(function (event) {
      var id_responsable = $(this).attr("data-id");
      sec_con_res_ar_modificar_estado_responsable_area(id_responsable);
    });
}

function sec_con_res_ar_modificar_estado_responsable_area(id_responsable) {
  var data = {
    accion: "modificar_estado_responsables_de_area",
    id_responsable: id_responsable,
  };
  $.ajax({
    url: "/sys/set_contrato_responsables_de_area.php",
    type: "POST",
    data: data,
    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      let response = JSON.parse(resp);
      /* if (response.status == 200) {
        alertify.success(response.message, 5);
      } else {
        alertify.error(response.message, 5);
      } */
    },
    error: function (resp, status) {},
  });
}
