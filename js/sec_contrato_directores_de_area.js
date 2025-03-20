function sec_contrato_director_de_area() {
  sec_con_dir_ar_listar_director_area();

  // SELECT2 AJAX
  $("#sec_con_new_dir_ar_director_area").select2({
    ajax: {
      url: "sys/set_contrato_directores_de_area.php",
      type: "POST",
      data: function (params) {
        var query = {
          search: params.term,
          accion: "obtener_directores_de_area",
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
    placeholder: "Seleccion un responsable de Área",
    minimumInputLength: 2,
    language: {
      inputTooShort: function () {
        return 'Por favor ingrese 2 o mas caracteres';
      }
    }
  });

  $("#frm_director_area").submit(function (e) {
    e.preventDefault();
    var director_area = $("#sec_con_new_dir_ar_director_area").val();
    if (director_area == null) {
      alertify.warning("Seleccione un director de área.", 5);
      return;
    }
    sec_con_dir_ar_guardar_director_area();
  });
}

function sec_con_dir_ar_guardar_director_area() {
  var director_area = $("#sec_con_new_dir_ar_director_area").val();

  var data = {
    accion: "guardar_directores_de_area",
    director_area: director_area,
  };
  $.ajax({
    url: "/sys/set_contrato_directores_de_area.php",
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
        $("#sec_con_new_dir_ar_director_area").empty().trigger("change");
        alertify.success(response.message, 5);
        sec_con_dir_ar_listar_director_area();
      } else {
        alertify.error(response.message, 5);
      }
    },
    error: function (resp, status) {},
  });
}

function sec_con_dir_ar_listar_director_area() {
  var data = {
    accion: "listar_directores_de_area",
  };
  $.ajax({
    url: "/sys/set_contrato_directores_de_area.php",
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
        fnc_render_table_directores_area(response.result);
      }
    },
    error: function (resp, status) {},
  });
}

function fnc_render_table_directores_area(data = []) {
  $("#tbl-director-area")
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

  $(".switch-director-area").bootstrapToggle({
    on: "activo",
    off: "inactivo",
    onstyle: "success",
    offstyle: "danger",
    size: "mini",
  });

  $(".switch-director-area")
    .off()
    .change(function (event) {
      var id_director = $(this).attr("data-id");
      sec_con_dir_ar_modificar_estado_director_area(id_director);
    });
}

function sec_con_dir_ar_modificar_estado_director_area(id_director) {
  var data = {
    accion: "modificar_estado_directores_de_area",
    id_director: id_director,
  };
  $.ajax({
    url: "/sys/set_contrato_directores_de_area.php",
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
