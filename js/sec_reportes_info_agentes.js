$(function () {
 
  $("#SecRepTor_ag_fecha_inicio").datetimepicker({
    format: "YYYY-MM-DD",
  });
  $("#SecRepTor_ag_fecha_fin").datetimepicker({
    format: "YYYY-MM-DD",
  });

  $("#SecRepTor_ag_fecha_inicio").val($("#g_fecha_actual").val());
  $("#SecRepTor_ag_fecha_fin").val($("#g_fecha_actual").val());

  $("#SecRepTor_ag_fecha_inicio").change(function () {
    var var_fecha_change = $("#SecRepTor_ag_fecha_inicio").val();
    if (!(parseInt(var_fecha_change.length) > 0)) {
      $("#SecRepTor_ag_fecha_inicio").val($("#g_fecha_actual").val());
    }
  });
  $("#SecRepTor_ag_fecha_fin").change(function () {
    var var_fecha_change = $("#SecRepTor_ag_fecha_fin").val();
    if (!(parseInt(var_fecha_change.length) > 0)) {
      $("#SecRepTor_ag_fecha_fin").val($("#g_fecha_actual").val());
    }
  });

  $("#SecRepTor_ag_btn_buscar").click(function () {
    listar_SecRepTor_tabla_transacciones_ag();
  });

  $("#SecRepTor_btn_exportar_ag").on("click", function () {
    var SecRepTor_ag_fecha_inicio = $.trim($("#SecRepTor_ag_fecha_inicio").val());
    var SecRepTor_ag_fecha_fin = $.trim($("#SecRepTor_ag_fecha_fin").val());

    var data = {
      "accion": "listar_transacciones_ag_export_xls",
      "fecha_inicio": SecRepTor_ag_fecha_inicio,
      "fecha_fin": SecRepTor_ag_fecha_fin
      
  }


    $.ajax({
      url: "/sys/get_reportes_info_agentes.php",
      type: "POST",
      data: data,
      beforeSend: function () {
        loading("true");
      },
      complete: function () {
        loading();
      },
      success: function(resp) {
            
        let obj = JSON.parse(resp);
        if(parseInt(obj.estado_archivo) == 1)
        {
            window.open(obj.ruta_archivo);
            loading(false);    
        }
        else if(parseInt(obj.estado_archivo) == 0)
        {
            swal({
                title: "Error al Generar el Concar",
                text: obj.ruta_archivo,
                html:true,
                type: "warning",
                closeOnConfirm: false,
                showCancelButton: false
            });
            return false;
        }
        else
        {
            swal({
                title: "Error",
                text: "Ponerse en contacto con Soporte",
                html:true,
                type: "warning",
                closeOnConfirm: false,
                showCancelButton: false
            });
            return false;
        }
    },
    error: function(resp, status) {

    }
    });
  });
});

function sec_reportes_info_agentes() {
  if (sec_id == "reportes" && sub_sec_id == "info_agentes") {
 
    $("#SecRepTor_ag_fecha_inicio").val($("#g_fecha_actual").val());
    $("#SecRepTor_ag_fecha_fin").val($("#g_fecha_actual").val());
    //listar_SecRepTor_tabla_transacciones();
  }
}

function limpiar_SecRepTor_tabla_transacciones_ag() {
  $("#SecRepTor_tabla_transacciones_ag").html(
    "<thead>" +
      "   <tr>" +
      '       <th class="text-center" width="5%">#</th>' +
      '       <th class="text-center" width="5%">CC</th>' +
      '       <th class="text-center" width="10%">NOMBRE AGENTE</th>' +
      '       <th class="text-center" width="10%">RAZON SOCIAL</th>' +
      '       <th class="text-center" width="10%">RUC</th>' +
      '       <th class="text-center" width="10%">CORREO</th>' +
      '       <th class="text-center" width="5%">CELULAR</th>' +
      '       <th class="text-center" width="5%">F.APERTURA</th>' +
      '       <th class="text-center" width="8%">TIPO BETSHOP</th>' +
      '       <th class="text-center" width="8%">TIPO JUEGOS VIRTUALES</th>' +
      '       <th class="text-center" width="8%">TIPO TERMINALES</th>' +
      '       <th class="text-center" width="8%">TIPO BINGO</th>' +
      '       <th class="text-center" width="8%">TIPO DEPOSITO WEB</th>' +
      "   </tr>" +
      "</thead>" +
      "<tbody>"
  );
  $("#SecRepTor_cant_depositos").val("0");
  $("#SecRepTor_cant_pagos").val("0");
  $("#SecRepTor_cant_recargas").val("0");
  $("#SecRepTor_total_ventas").val("0.00");
  $("#SecRepTor_total_pagos").val("0.00");
  $("#SecRepTor_total_recargas").val("0.00");
}

function listar_SecRepTor_tabla_transacciones_ag() {
  limpiar_SecRepTor_tabla_transacciones_ag();

  var SecRepTor_ag_fecha_inicio = $.trim($("#SecRepTor_ag_fecha_inicio").val());
  var SecRepTor_ag_fecha_fin = $.trim($("#SecRepTor_ag_fecha_fin").val());

  if (SecRepTor_ag_fecha_inicio.length !== 10) {
    $("#SecRepTor_ag_fecha_inicio").focus();
    return false;
  }
  if (SecRepTor_ag_fecha_fin.length !== 10) {
    $("#SecRepTor_ag_fecha_fin").focus();
    return false;
  }

  var data = {
        "accion": "listar_transacciones_ag",
        "fecha_inicio": SecRepTor_ag_fecha_inicio,
        "fecha_fin": SecRepTor_ag_fecha_fin 
    }

  auditoria_send({ proceso: "listar_transacciones_ag", data: data });
  $.ajax({
    url: "/sys/get_reportes_info_agentes.php",
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
      //console.log(respuesta);
      if (parseInt(respuesta.http_code) == 400) {
        return false;
      }
      if (parseInt(respuesta.http_code) == 200) {
        
        if (respuesta.result.length > 0) {
          $.each(respuesta.result, function (index, item) {
            
            if(!item.c_costos){
              item.c_costos="";
             }

             if(!item.nombre_agente){
              item.nombre_agente="";
             }
           
           
            $("#SecRepTor_tabla_transacciones_ag").append(
              "<tr>" +
                '<td class="text-center">' +
                (index + 1) +
                "</td>" +
                '<td class="text-center">' +
                item.c_costos +
                "</td>" +
                '<td class="text-center">' +
                item.nombre_agente +
                "</td>" +
                '<td class="text-center">' +
                item.nombre +
                "</td>" +
                '<td class="text-center">' +
                item.num_ruc +
                "</td>" +
                '<td class="text-center">' +
                item.contacto_email +
                "</td>" +
                '<td class="text-center">' +
                item.contacto_telefono +
                "</td>" +
                '<td class="text-center">' +
                item.fecha_suscripcion_contrato +
                "</td>" +
                '<td class="text-center">' +
                item.betshop +
                "</td>" +
                '<td class="text-center">' +
                item.juegos_virtuales +
                "</td>" +
                '<td class="text-center">' +
                item.terminales +
                "</td>" +
                '<td class="text-center">' +
                item.bingo +
                "</td>" +
                '<td class="text-center">' +
                item.deposito_web +
                "</td>" +
                "</tr>"
            );
          });
          DATATABLE_FORMATO_SecRepTor_tabla_transacciones_ag("#SecRepTor_tabla_transacciones_ag");
        } else {
          $("#SecRepTor_tabla_transacciones_ag").append("<tr>" + '<td class="text-center" colspan="8">No hay transacciones.</td>' + "</tr>");
        }
        
        //console.log(array_clientes);
        return false;
      }
    },
    error: function () {},
  });
}

function DATATABLE_FORMATO_SecRepTor_tabla_transacciones_ag(id) {
  if ($.fn.dataTable.isDataTable(id)) {
    $(id).DataTable().destroy();
  }
  $(id).DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    order: [[0, "asc"]],
    info: true,
    autoWidth: false,
    language: {
      processing: "Procesando...",
      lengthMenu: "Mostrar _MENU_ registros",
      zeroRecords: "No se encontraron resultados",
      emptyTable: "Ningún dato disponible en esta tabla",
      info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
      infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
      infoFiltered: "(filtrado de un total de _MAX_ registros)",
      infoPostFix: "",
      search: "Buscar: ",
      url: "",
      infoThousands: ",",
      loadingRecords: "Cargando...",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior",
      },
      aria: {
        sortAscending: ": Activar para ordenar la columna de manera ascendente",
        sortDescending: ": Activar para ordenar la columna de manera descendente",
      },
    },
  });
}
