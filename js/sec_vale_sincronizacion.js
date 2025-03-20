var select_items_vales_a_sincronizar = [];
var table_select = $("#tbl_vales_a_sincronizar").DataTable();
function sec_vale_sincronizacion() {
  $(".select2").select2({ width: "100%", placeholder: "- Todos -" });
  $(".sec_vale_sinc_datepicker")
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
    });


    
  table_select
  .on("select", function (e, dt, type, indexes) {
    var rowData = table_select.rows(indexes).data().toArray();
    rowData.forEach((element) => {
      if (!select_items_vales_a_sincronizar.includes(element.id)) {
        select_items_vales_a_sincronizar.push(element.id);
      }
    });
    // console.log(select_items_vales_a_sincronizar);
  })
  .on("deselect", function (e, dt, type, indexes) {    
    var rowData = table_select.rows(indexes).data().toArray();
    rowData.forEach((element) => {
      const index = select_items_vales_a_sincronizar
        .map((item) => item)
        .indexOf(element.id);
      select_items_vales_a_sincronizar.splice(index, 1);
    });
    // console.log(select_items_vales_a_sincronizar);
  });

  sec_vale_sinc_obtener_opciones("listar_empresas_por_usuario",$("#sec_vale_sinc_empresa"));

  setTimeout(() => {
    sec_vale_sinc_buscar_vale_a_sincronizar();
  }, 2000);

  $("#sec_vale_sinc_empresa").change(function () {
    sec_vale_sinc_obtener_zonas();
  });

  $("#frm_sincronizacion_vale").submit(function (evt) {
    evt.preventDefault();
    var empresa = $("#sec_vale_sinc_empresa").val();
    var zona = $("#sec_vale_sinc_zona").val();
    var fecha_desde_vale = $("#sec_vale_sinc_fecha_desde_vale").val();
    var fecha_hasta_vale = $("#sec_vale_sinc_fecha_hasta_vale").val();
    var empleado = $("#sec_vale_sinc_empleado").val();
    var dni = $("#sec_vale_sinc_dni").val();

    if (empresa == null) {
      alertify.error("Seleccione al menos una empresa", 5);
      $("#sec_vale_sinc_empresa").focus();
      $("#sec_vale_sinc_empresa").select2("open");
      return false;
    }
    if (zona == null) {
      alertify.error("Seleccione al menos una zona", 5);
      $("#sec_vale_sinc_zona").focus();
      $("#sec_vale_sinc_zona").select2("open");
      return false;
    }
    if (fecha_desde_vale.length == 0) {
      alertify.error("Seleccione una fecha", 5);
      $("#sec_vale_sinc_fecha_desde_vale").focus();
      $("#sec_vale_sinc_fecha_desde_vale").select2("open");
      return false;
    }
    if (fecha_hasta_vale.length == 0) {
      alertify.error("Seleccione una fecha", 5);
      $("#sec_vale_sinc_fecha_hasta_vale").focus();
      $("#sec_vale_sinc_fecha_hasta_vale").select2("open");
      return false;
    }

    sec_vale_sinc_buscar_vale_a_sincronizar();
  });
}

function sec_vale_sinc_obtener_opciones(accion, select) {
  $.ajax({
    url: "/vales/controllers/DataController.php",
    type: "POST",
    data: { accion: accion }, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      var respuesta = JSON.parse(datos);
      console.log(respuesta);
      $(select).find("option").remove().end();
      if (respuesta.status == 200) {
        var result = respuesta.result;
        var values = [];
        $(result).each(function (i, e) {
          opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
          $(select).append(opcion);
          values.push(e.id);
        });
        $(select).val(values).trigger('change.select2');
				$(select).trigger('change');
      }
    },
    error: function () {},
  });
}

function sec_vale_sinc_obtener_zonas() {
  var empresa_id = $("#sec_vale_sinc_empresa").val();
  if (empresa_id == null) {
    console.log("null");
    $("#sec_vale_sinc_zona").find("option").remove().end();
    return false;
  }
  var data = {
    empresa_id: empresa_id,
    accion: "listar_zonas_por_empresa_multiple",
  };

  $.ajax({
    url: "vales/controllers/DataController.php",
    type: "POST",
    data: data, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      var respuesta = JSON.parse(datos);
      if (respuesta.status == 200) {
        $("#sec_vale_sinc_zona").find("option").remove().end();
        $(respuesta.result).each(function (i, e) {
          opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
          $("#sec_vale_sinc_zona").append(opcion);
        });
        $("#sec_vale_sinc_zona").val(respuesta.value).trigger("change.select2");
      }
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function sec_vale_sinc_buscar_vale_a_sincronizar() {

  var empresa = $("#sec_vale_sinc_empresa").val();
  var zona = $("#sec_vale_sinc_zona").val();
  var fecha_desde_vale = $("#sec_vale_sinc_fecha_desde_vale").val();
  var fecha_hasta_vale = $("#sec_vale_sinc_fecha_hasta_vale").val();
  var empleado = $("#sec_vale_sinc_empleado").val();
  var dni = $("#sec_vale_sinc_dni").val();
  var cuota = $("#sec_vale_sinc_cuota").val();

  var data = {
    empresa: empresa,
    zona: zona,
    fecha_desde_vale: fecha_desde_vale,
    fecha_hasta_vale: fecha_hasta_vale,
    empleado: empleado,
    dni: dni,
    cuota: cuota,
    accion: "vales_a_sincronizar",
  };

  $.ajax({
    url: "vales/controllers/ValeController.php",
    type: "POST",
    data: data, //+data,
    beforeSend: function () {},
    complete: function () {},
    success: function (datos) {
      var respuesta = JSON.parse(datos);
      if (respuesta.status == 200) {
        fnc_render_table_vales_a_sincronizar(respuesta.result);
      } else {
        alertify.error(respuesta.message, 10);
      }
      return false;
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function fnc_render_table_vales_a_sincronizar(data = []) {

  select_items_vales_a_sincronizar = [];
  
  table_select.destroy();
  table_select = $("#tbl_vales_a_sincronizar").DataTable({
    bDestroy: true,
    dom: "Bfrtip",
    data: data,
    select: true,
    order: [[1, "asc"]],
    select: {
      style: "multi",
    },
    pageLength: 25,
    lengthMenu: [ [25, 50, 100, 500, -1], [25, 50, 100, 500, "Todos"] ],
    columns: [
      { data: "id", className: "text-center hidden" },
      { data: "empresa", className: "text-left" },
      { data: "zona", className: "text-left" },
      { data: "nombre_tipo_vale", className: "text-left" },
      { data: "nro_vale", className: "text-center" },
      { data: "nombre_empleado", className: "text-left" },
      { data: "dni_empleado", className: "text-left" },
      { data: "fecha_aprobacion_rechazo", className: "text-center" },
      { data: "fecha_incidencia", className: "text-center" },
      { data: "monto", className: "text-right" },
      { data: "nro_cuotas", className: "text-center" },
      { data: "observacion", className: "text-left hidden" },
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
      buttons: {
        pageLength: {
            _: "Mostrar %d Resultados",
            '-1': "Tout afficher"
        }
        
      }
      
    },
    buttons: [
      'pageLength',
      {
        text: "Selecionar Todos",
        action: function () {
          table_select.rows().select();
        },
      },
      {
        text: "Seleccionar Ninguno",
        action: function () {
          table_select.rows().deselect();
        },
      },
      {
        extend: 'excelHtml5',
        exportOptions: {
            columns: [1,2,3,4,5,6,7,8,9,10,11]
        },
        title: 'Vales de Descuento - Sincronización'
    },
    ],
    columnDefs: [
      {
          targets: [11],
          visible: false
      }
  ]
  });



}

function arraysSonIguales(arr1, arr2) {
  if (arr1.length !== arr2.length) {
      return false;
  }

  for (var i = 0; i < arr1.length; i++) {
      if (arr1[i] !== arr2[i]) {
          return false;
      }
  }

  return true;
}
function sec_vale_sinc_validar_sincronizacion() {
  var fecha_sincronizacion = $("#sec_vale_sinc_fecha_sincronizacion").val();

  if (select_items_vales_a_sincronizar.length == 0) {
    alertify.error("Seleccione al menos un vale para la sincronización", 5);
    return false;
  }

  if (fecha_sincronizacion.length == 0) {
    alertify.error("Seleccione una fecha", 5);
    $("#sec_vale_sinc_fecha_sincronizacion").focus();
    return false;
  }

  swal(
    {
      title: "Esta seguro de realizar la sincronización?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      confirmButtonText: "Si, estoy de acuerdo!",
      cancelButtonText: "No, cancelar",
      closeOnConfirm: true,
      closeOnCancel: true,
    },
    function (isConfirm) {
      if (isConfirm) {
        sec_vale_sinc_guardar_sincronizacion();
      }
    }
  );
}

function sec_vale_sinc_guardar_sincronizacion() {

	var fecha_sincronizacion = $("#sec_vale_sinc_fecha_sincronizacion").val();

	var data = {
		fecha_sincronizacion:fecha_sincronizacion,
		vales_a_sincronizar:select_items_vales_a_sincronizar,
		accion : 'sincronizacion_vale'
	};
  auditoria_send({ proceso: "sincronizacion_vale", data: data });
	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
			fracc_manual_cuotas = [];
			var respuesta = JSON.parse(datos);
      auditoria_send({ proceso: "sincronizacion_vale", data: respuesta });
      
      sec_vale_sinc_buscar_vale_a_sincronizar();
			if (respuesta.status == 200) {
        alertify.success(respuesta.message, 5);
			}else if(respuesta.status == 202){
        alertify.warning(respuesta.message, 10);
      }else{
        alertify.error(respuesta.message, 5);
      }
		},
		error: function (error) {
			
		},
	});
}
