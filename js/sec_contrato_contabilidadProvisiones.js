var tabla_contrato_mantenimiento_tipo= '';
var mantenimiento_contrato_tipo = '';
var mantenimiento_nombre_tipo = '';
var tipo_general_mantenimiento = '';


function sec_contrato_contabilidadProvisiones() {
	if (sec_id == "contrato") {

    $(".tab-provisiones > div:not(:first-child)").hide();

    $(".tab-provisiones > div:first-child").show();

    $("a.tab_btn").click(function (event) {
      event.preventDefault();

      // obtenemos el tab seleccionado
      var tab = $(this).data("tab");

      // ocultamos todos los div 
      $(".tab-provisiones > div").hide();

      // Mostramos el div correspondiente al tab
      $(".tab-provisiones > ." + tab).show();
    });

		$('.sec_contrato_nueva_programacion_datepicker')
		.datepicker({
			dateFormat:'dd/mm/yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
	
		var fechaActual = new Date();

		// Obtener el año actual
		var anioActual = fechaActual.getFullYear();

		// Obtener el mes actual (agregar 1 ya que los meses en JavaScript van de 0 a 11)
		var mesActual = fechaActual.getMonth() + 1;

		// Obtener el día actual
		var diaActual = fechaActual.getDate();

		// Formatear la fecha en el formato "yyyy-mm-dd"
		var fecha_limite = anioActual + '-' + ('0' + mesActual).slice(-2) + '-' + ('0' + diaActual).slice(-2);

    // CON INDICE DE INFLACION
    //cargar_tabla_provisiones_contables((0:sin ipc,1 con ipc),fecha actual)
    cargar_tabla_provisiones_contables(0,fecha_limite) // POR DEFECTO SE CARGAN LAS PROVISIONES DEL MES ACTUAL Y SIN IPC
	
    $('#tab-provisiones  a').click(function (e) {
			e.preventDefault()
			cargar_tabla_provisiones_contables_pj();
		  })
		
	}
}


function cargar_tabla_provisiones_contables(tipo_provisiones,fecha_limite=null) {
	// fecha_limite  = "2023-06-20";
  var tipo_provisiones = $('#tipo_provisiones').val();
  var fecha_limite = $('#fecha_inicio_a_provisionar').val();
	tienda_id = '';
	let data = {
		contrato_id: '644',
		fecha_limite: fecha_limite,
		tipo_provisiones: tipo_provisiones,
    action : 'obtener_provisiones_por_periodo'
	};
  console.log(data)
	let url = 'sys/router/provisiones/index.php';
	axios({
			method: 'POST',
			url: url,
			data: data
		})
		.then(function(response) {
			console.log(response.data.result);
			data = response.data.result;
			provision_idIs = [];

			// Iterar sobre cada objeto en 'data'
			for (var i = 0; i < data.length; i++) {
			// Obtener el valor de 'cc_id' de cada objeto y agregarlo a 'ccIds'
			// console.log(data[i].provision_id);
					provision_idIs.push(data[i].provision_id);
			}
			tabla_provisiones_contables = $("#tbl_datos_provisiones").DataTable({
        lengthMenu: [
          [12, 10, 25, 50, -1],
          [12, 10, 25, 50, "Todos"],
        ],
        pageLength: 12, // mostrar 12 registros por página
        bDestroy: true,
        language: {
          search: "Buscar Tienda:",
          lengthMenu: "Mostrar _MENU_ registros por página",
          zeroRecords: "No se encontraron registros",
          info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
          infoEmpty: "No hay registros",
          infoFiltered: "(filtrado de _MAX_ total records)",
          paginate: {
            first: "Primero",
            last: "Último",
            next: "Siguiente",
            previous: "Anterior",
          },
          sProcessing: "Procesando...",
        },
        data: data,
        sDom: "<'row'<'col-sm-3'l><'col-sm-3 div_select_mes'><'col-sm-2 div_select_estado'><'col-sm-2'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
        initComplete: function (settings, json) {
          var sele = $(
            '<select name="estado_feriado" id="estado_feriado" class="form-control input-sm" style="width:80%"></select>'
          );
          sele.append($('<option value="">-- Todos --</option>'));
          sele.append($('<option value="0">No pagado</option>'));
          sele.append($('<option value="1">Sin aprobar</option>'));
          sele.append($('<option value="2">Aprobada</option>'));
          sele.append($('<option value="3">Procesado</option>'));

          $(".div_select_estado").append("Estado de pago ");
          $(".div_select_estado").append(sele);
          $("#estado_feriado")
            .off("change")
            .on("change", function () {
              var val = $(this).val();
              tabla_provisiones_contables.column(0).search(val).draw();
              tabla_provisiones_contables.columns.adjust();
            });

          // var sele = $('<select name="mes_select" id="mes_select" class="form-control input-sm" style="width:80%"></select>');
          // sele.append($('<option value="">-- Todos --</option>'))
          // // for (var i = 0; i < data_meses.length; i++) {
          // // 	sele.append($('<option value="' + data_meses[i].nombre + '">' + data_meses[i].nombre + '</option>'));
          // // }
          // $(".div_select_mes").append("Tienda  ");
          // $(".div_select_mes").append(sele);

          // $("#mes_select").off("change").on("change",function(){
          // 	var val=$(this).val();
          // 	tabla_provisiones_contables.column(1).search(val).draw();
          // 	tabla_provisiones_contables.columns.adjust();
          // })
        },
        order: [],
        columns: [
          {
            title: "<div class='text-center'>Número</div>",
            data: "etapa_id",
            visible: false,
          },

          {
            title: "Contrato",
            data: "contrato_id",
          },
          {
            title: "Nro Cuota",
            data: "num_cuota",
          },
          {
            title: "Mes periodo",
            render: function (data, type, row) {
              var periodo_inicio = row["fecha_actual"];
              var html_antisipo = "";
              if (row["tipo_anticipo_id"] == 2) {
                html_antisipo =
                  '<span class="badge bg-success text-white">Adelanto</span>';
              }

              var partesFecha = periodo_inicio.split("-");

              var mes = parseInt(partesFecha[1]); // Obtiene el mes como un número
              var año = parseInt(partesFecha[0]); // Obtiene el año como un número

              var fechaConvertida = mes + "/ " + año;

              return fechaConvertida + "  " + html_antisipo;
            },
          },
          {
            title: "C. de Costos",
            data: "cc_id",
          },
          {
            title: "Tienda",
            data: "nombre_tienda",
            class: "text-left",
          },

          {
            title: "Fecha inicial",
            render: function (data, type, row) {
              var periodo_inicio = row["periodo_inicio"];
              var fechaObjeto = new Date(periodo_inicio);

              var dia = fechaObjeto.getDate();
              var mes = fechaObjeto.getMonth() + 1;
              var anio = fechaObjeto.getFullYear();
              return dia + "/" + mes + "/" + anio;
            },
            // data: "periodo_fin",

            data: "periodo_inicio",
            class: "text-left",
          },
          {
            title: "Fecha final",
            render: function (data, type, row) {
              var periodo_fin = row["periodo_fin"];
              var fechaObjeto = new Date(periodo_fin);

              var dia = fechaObjeto.getDate();
              var mes = fechaObjeto.getMonth() + 1;
              var anio = fechaObjeto.getFullYear();
              return dia + "/" + mes + "/" + anio;
            },
            // data: "periodo_fin",

            class: "text-left",
          },
          {
            title: "Incremento",
            render: function (data, type, row) {
              var valor_incremento = row["incrementos"];
              console.log(valor_incremento);
              if(valor_incremento!=null){
                var valor_incremento = parseFloat(valor_incremento);
                var separadorDecimal = ".";
                var separadorMiles = ",";
                var numeroString = valor_incremento.toFixed(2).toString();
                var partes = numeroString.split(".");
                partes[0] = partes[0].replace(
                  /\B(?=(\d{3})+(?!\d))/g,
                  separadorMiles
                );
                return partes.join(separadorDecimal);
              }else{
                return '-';
              }
              
            },
            // data: "monto_renta",
            class: "text-left",
          },
          {
            title: "Valor de <br> Inflación",
            render: function (data, type, row) {
              var valor_ipc = row["valor_ipc"];
              // console.log(valor_ipc);
              if(valor_ipc!=null){
                if(valor_ipc==0){
                  return  '0';
                }
                var valor_ipc = parseFloat(valor_ipc);
                var separadorDecimal = ".";
                var separadorMiles = ",";
                var numeroString = valor_ipc.toFixed(2).toString();
                var partes = numeroString.split(".");
                partes[0] = partes[0].replace(
                  /\B(?=(\d{3})+(?!\d))/g,
                  separadorMiles
                );
                return partes.join(separadorDecimal)+' %';
              }else{
                return '-';
              }
              
            },
            // data: "monto_renta",
            class: "text-left",
          },
          {
            title: "Renta bruta",
            render: function (data, type, row) {
              var monto_renta = row["monto_renta"];
              var monto_renta = parseFloat(monto_renta);
              var separadorDecimal = ".";
              var separadorMiles = ",";
              var numeroString = monto_renta.toFixed(2).toString();
              var partes = numeroString.split(".");
              partes[0] = partes[0].replace(
                /\B(?=(\d{3})+(?!\d))/g,
                separadorMiles
              );
              return partes.join(separadorDecimal);
            },
            // data: "monto_renta",
            class: "text-left",
          },
          {
            title: "Importe a pagar",
            render: function (data, type, row) {
              var monto_renta = row["total_pagar"];
              var monto_renta = parseFloat(monto_renta);
              var separadorDecimal = ".";
              var separadorMiles = ",";
              var numeroString = monto_renta.toFixed(2).toString();
              var partes = numeroString.split(".");
              partes[0] = partes[0].replace(
                /\B(?=(\d{3})+(?!\d))/g,
                separadorMiles
              );
              return partes.join(separadorDecimal);
            },
            // data: "total_pagar",
            class: "text-left",
          },
          {
            title: "Estado de pago",
            render: function (data, type, row) {
              var status_valor = row["etapa_id"];
              var html_status = "";
              switch (status_valor) {
                case "0":
                  html_status =
                    '<span class="badge bg-warning text-white">No pagado</span>';
                  break;
                case "1":
                  html_status =
                    '<span class="badge bg-danger text-white">Sin aprobar</span>';
                  break;
                case "2":
                  html_status =
                    '<span class="badge bg-danger text-white">aprobada</span>';
                  break;
                case "3":
                  html_status =
                    '<span class="badge bg-success text-white">Procesado</span>';
                  break;
              }

              return html_status;
            },
            class: "text-center",
          },
          {
            title: "Día de pago",
            data: "dia_de_pago",
          },
          // {
          // 	title: "Opciones",
          // 	width: "150px",
          // 	class: "text-center",
          // 	"render": function (data, type, row) {
          // 		var id = row["provision_id"];
          // 		var estado_feriado = row["status"]=='Activo'?1:0;

          // 		var html = "<div style='text-align: center;'>";
          // 		var btn_class = "btn btn-sm btn-success indice_inflacion_historial";

          // 		html += ' <a class="btn btn-rounded btn-default btn-sm btn-edit" title="Editar" href=""#"">';
          // 		html += '<i class="glyphicon glyphicon-edit"></i>';
          // 		html += '</a>';
          // 		html += '</div>';
          // 		return html;
          // 	}
          // }
        ],
      });
		});


}

 
function exportar_plantilla_contable() {
  var tipo_provisiones = $('#tipo_provisiones').val();
  var fecha_fin_a_provisionar = $("#fecha_inicio_a_provisionar").val();
  var fechaActual = new Date();

  var anioActual = fechaActual.getFullYear();

  // Crear una nueva fecha para el 1 de enero del año actual
  var fechaEnero = new Date(anioActual, 0, 1);

  // Obtener el día del mes de enero
  var diaEnero = fechaEnero.getDate();

  // Formatear la fecha en el formato "dd/mm/yyyy"
  var fecha_inicio_a_provisionar =
    ("0" + diaEnero).slice(-2) + "/01/" + anioActual;
  // Mostrar la alerta de descarga en progreso

  var data = {
    fecha_inicio_a_provisionar: fecha_inicio_a_provisionar,
    fecha_fin_a_provisionar: fecha_fin_a_provisionar,
    action: "obtener_plantilla_asiento_contable",
    tipo_provisiones :tipo_provisiones
  };

  var requestOptions = {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  };
  swal({
    title: "Descargando plantilla contable",
    icon: "info",
    showConfirmButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false
  });

  fetch(
    "sys/router/provisiones/index.php",
    requestOptions
  )
    .then(function (response) {
      return response.blob(); // Convertir la respuesta a un objeto Blob
    })
    .then(function (blob) {
      // Crear un enlace temporal para descargar el archivo
      var url = window.URL.createObjectURL(blob);
      var a = document.createElement("a");
      a.href = url;
      a.download = "Plantilla_Contable_EXPORT.xls"; // Cambia 'nombre_del_archivo.xlsx' por el nombre que desees para el archivo descargado
      document.body.appendChild(a);
      a.click(); // Simular el clic en el enlace para iniciar la descarga
      window.URL.revokeObjectURL(url); // Liberar el objeto Blob
      swal.close();

      swal({
        title: "Descarga de plantilla terminada",
        icon: "success",
        timer: 3000,
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false,
      });
      
    })
    .catch(function (error) {
      // Manejar errores en caso de que la solicitud falle
      swal.close();

      swal({
        title: "Error",
        text: "Se produjo un error al descargar el archivo.",
        icon: "error",
        timer: 3000,
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false,
      });
      console.error("Error en la solicitud POST:", error);
    });
  // window.open('sys/router/provisiones/obtener_plantilla_asiento_contable?fecha_inicio_a_provisionar='+ fecha_inicio_a_provisionar + '&fecha_fin_a_provisionar=' + fecha_fin_a_provisionar,  '_blank');
}
function exportar_excel_calculo_provisiones(ipc=null) {
  var fecha_fin_a_provisionar = $("#fecha_inicio_a_provisionar").val();
  var tipo_provisiones = $('#tipo_provisiones').val();

  // Obtener la fecha actual
  var fechaActual = new Date();
  var anioActual = fechaActual.getFullYear();

  // Crear una nueva fecha para el 1 de enero del año actual
  var fechaEnero = new Date(anioActual, 0, 1);

  // Obtener el día del mes de enero
  var diaEnero = fechaEnero.getDate();

  // Formatear la fecha en el formato "dd/mm/yyyy"
  var fecha_inicio_a_provisionar =
    ("0" + diaEnero).slice(-2) + "/01/" + anioActual;

  // Datos a enviar en la solicitud POST
  var data = {
    fecha_inicio_a_provisionar: fecha_inicio_a_provisionar,
    fecha_fin_a_provisionar: fecha_fin_a_provisionar,
    action: "exportar_excel_calculo_provisiones",
    tipo_provisiones :tipo_provisiones
  };

  // Configurar las opciones de la solicitud POST
  var requestOptions = {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  };

  // Realizar la solicitud POST usando fetch
  fetch(
    "sys/router/provisiones/index.php",
    requestOptions
  )
    .then(function (response) {
      return response.blob(); // Convertir la respuesta a un objeto Blob
    })
    .then(function (blob) {
      // Crear un enlace temporal para descargar el archivo
      var url = window.URL.createObjectURL(blob);
      var a = document.createElement("a");
      a.href = url;
      a.download = "Cálculo_Provisiones.xls"; // Cambia 'nombre_del_archivo.xlsx' por el nombre que desees para el archivo descargado
      document.body.appendChild(a);
      a.click(); // Simular el clic en el enlace para iniciar la descarga
      window.URL.revokeObjectURL(url); // Liberar el objeto Blob
    })
    .catch(function (error) {
      // Manejar errores en caso de que la solicitud falle
      console.error("Error en la solicitud POST:", error);
    });
}
function obtener_provisiones_actuales(ipc=null){
	 fecha_limite  = $('#fecha_inicio_a_provisionar').val();
   swal(
		{
			title: "Obteniendo provisiones...",

			type: "info",
			timer: 1000,
      buttons: false,
      closeOnClickOutside: false,
      closeOnEsc: false
		});
	 cargar_tabla_provisiones_contables(fecha_limite);

}
function enviar_tesoreria_provisiones(ipc=null){
  // document.getElementById("enviar_tesoreria_button").disabled = true;
  var fecha_limite_envio_correo = $('#fecha_inicio_a_provisionar').val();
  var tipo_provisiones = $('#tipo_provisiones').val();

  var vali = validar_envio_tesoreria(provision_idIs);
	set_data = {
    fecha_limite_envio_correo : fecha_limite_envio_correo,
		provision_idIs:JSON.stringify(provision_idIs),
    action : 'enviar_tesoreria_provisiones',
    tipo_provisiones :tipo_provisiones
	};
	swal(
		{
			title: "¿Está seguro de enviar provisiones a tesoreria?",
			text: "Se enviarán las provisiones del periodo "+$('#fecha_inicio_a_provisionar').val(),

			type: "warning",
			timer: 3000,
			showCancelButton: true,
			closeOnConfirm: true,
			confirmButtonColor: "#3085d6",
			confirmButtonText: "Aceptar",
			cancelButtonText: "Cancelar"
		},
		function (result) {
			if (result) {

    
				axios({
					method: 'POST',
					url:  "/sys/router/provisiones/index.php",
					data: set_data
				}).then(function(response) { 
					console.log(response);
					swal({
						title: "Provisiones enviadas",
						text: "Se ha enviado provisiones a tesoreria",
            icon: "success",
						timer: 3000,
            buttons: false,
						closeOnClickOutside: false,
            closeOnEsc: false
					});
           sec_contrato_contabilidadProvisiones();
				
			
				});
			}
		}
	);
	

}

function validar_envio_tesoreria(provision_idIs){
  set_data = {
		provision_idIs:JSON.stringify(provision_idIs),
    action : 'validar_envio_tesoreria'
	};

	axios({
    method: 'POST',
    url:  "/sys/router/provisiones/index.php",
    data: set_data
  }).then(function(response) { 
			console.log(response.data);
      if(!response.data){
        swal({
          title: "No puede realizar el proceso ",
          text: "Las provisiones del periodo "+$('#fecha_inicio_a_provisionar').val()+" ya fueron enviadas a tesoreria ",
          html:true,
          type: "warning",
          timer: 3000,
          closeOnConfirm: false,
          showCancelButton: false
        })
        return ;
      }
  
  

  });
}


////////////////////////////////////////////////
/// *******    PERSONA JURIDICA  **********////
///////////////////////////////////////////////
function cargar_tabla_provisiones_contables_pj(tipo_provisiones,fecha_limite=null) {
	// fecha_limite  = "2023-06-20";
  var tipo_provisiones = $('#tipo_provisiones_pj').val();
  var fecha_limite = $('#fecha_inicio_a_provisionar_pj').val();
	tienda_id = '';
	let data = {
		contrato_id: '644',
		fecha_limite: fecha_limite,
		tipo_provisiones: tipo_provisiones,
    action : 'obtener_provisiones_PJ_por_periodo'
	};
  console.log(data)
	let url = 'sys/router/provisiones/index.php';
	axios({
			method: 'POST',
			url: url,
			data: data
		})
		.then(function(response) {
			console.log(response.data.result);
			data = response.data.result;
			provision_idIs = [];

			// Iterar sobre cada objeto en 'data'
			for (var i = 0; i < data.length; i++) {
			// Obtener el valor de 'cc_id' de cada objeto y agregarlo a 'ccIds'
			console.log(data[i].provision_id);
					provision_idIs.push(data[i].provision_id);
			}
			tabla_provisiones_contables = $("#tbl_datos_provisiones_PJ").DataTable({
        lengthMenu: [
          [12, 10, 25, 50, -1],
          [12, 10, 25, 50, "Todos"],
        ],
        pageLength: 12, // mostrar 12 registros por página
        bDestroy: true,
        language: {
          search: "Buscar Tienda:",
          lengthMenu: "Mostrar _MENU_ registros por página",
          zeroRecords: "No se encontraron registros",
          info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
          infoEmpty: "No hay registros",
          infoFiltered: "(filtrado de _MAX_ total records)",
          paginate: {
            first: "Primero",
            last: "Último",
            next: "Siguiente",
            previous: "Anterior",
          },
          sProcessing: "Procesando...",
        },
        data: data,
        sDom: "<'row'<'col-sm-3'l><'col-sm-3 div_select_mes_pj'><'col-sm-2 div_select_estado_pj'><'col-sm-2'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
        initComplete: function (settings, json) {
          var sele = $(
            '<select name="estado_feriado_pj" id="estado_feriado_pj" class="form-control input-sm" style="width:80%"></select>'
          );
          sele.append($('<option value="">-- Todos --</option>'));
          sele.append($('<option value="0">No pagado</option>'));
          sele.append($('<option value="1">Sin aprobar</option>'));
          sele.append($('<option value="2">Aprobada</option>'));
          sele.append($('<option value="3">Procesado</option>'));

          $(".div_select_estado_pj").append("Estado de pago ");
          $(".div_select_estado_pj").append(sele);
          $("#estado_feriado_pj")
            .off("change")
            .on("change", function () {
              var val = $(this).val();
              tabla_provisiones_contables.column(0).search(val).draw();
              tabla_provisiones_contables.columns.adjust();
            });

        
        },
        order: [],
        columns: [
          {
            title: "<div class='text-center'>Número</div>",
            data: "etapa_id",
            visible: false,
          },

          {
            title: "Contrato",
            data: "contrato_id",
          },
          {
            title: "Nro Cuota",
            data: "num_cuota",
          },
          {
            title: "Mes periodo",
            render: function (data, type, row) {
              var periodo_inicio = row["fecha_actual"];
              var html_antisipo = "";
              if (row["tipo_anticipo_id"] == 2) {
                html_antisipo =
                  '<span class="badge bg-success text-white">Adelanto</span>';
              }

              var partesFecha = periodo_inicio.split("-");

              var mes = parseInt(partesFecha[1]); // Obtiene el mes como un número
              var año = parseInt(partesFecha[0]); // Obtiene el año como un número

              var fechaConvertida = mes + "/ " + año;

              return fechaConvertida + "  " + html_antisipo;
            },
          },
          {
            title: "C. de Costos",
            data: "cc_id",
          },
          {
            title: "Tienda",
            data: "nombre_tienda",
            class: "text-left",
          },

          {
            title: "Fecha inicial",
            render: function (data, type, row) {
              var periodo_inicio = row["periodo_inicio"];
              var fechaObjeto = new Date(periodo_inicio);

              var dia = fechaObjeto.getDate();
              var mes = fechaObjeto.getMonth() + 1;
              var anio = fechaObjeto.getFullYear();
              return dia + "/" + mes + "/" + anio;
            },
            // data: "periodo_fin",

            data: "periodo_inicio",
            class: "text-left",
          },
          {
            title: "Fecha final",
            render: function (data, type, row) {
              var periodo_fin = row["periodo_fin"];
              var fechaObjeto = new Date(periodo_fin);

              var dia = fechaObjeto.getDate();
              var mes = fechaObjeto.getMonth() + 1;
              var anio = fechaObjeto.getFullYear();
              return dia + "/" + mes + "/" + anio;
            },
            // data: "periodo_fin",

            class: "text-left",
          },
          {
            title: "Incremento",
            render: function (data, type, row) {
              var valor_incremento = row["incrementos"];
              console.log(valor_incremento);
              if(valor_incremento!=null){
                var valor_incremento = parseFloat(valor_incremento);
                var separadorDecimal = ".";
                var separadorMiles = ",";
                var numeroString = valor_incremento.toFixed(2).toString();
                var partes = numeroString.split(".");
                partes[0] = partes[0].replace(
                  /\B(?=(\d{3})+(?!\d))/g,
                  separadorMiles
                );
                return partes.join(separadorDecimal);
              }else{
                return '-';
              }
              
            },
            // data: "monto_renta",
            class: "text-left",
          },
          {
            title: "Valor de  <br> Inflación",
            render: function (data, type, row) {
              var valor_ipc = row["valor_ipc"];
              console.log(valor_ipc);
              if(valor_ipc!=null){
                if(valor_ipc==0){
                  return  'calculado cada 12 meses';
                }
                var valor_ipc = parseFloat(valor_ipc);
                var separadorDecimal = ".";
                var separadorMiles = ",";
                var numeroString = valor_ipc.toFixed(2).toString();
                var partes = numeroString.split(".");
                partes[0] = partes[0].replace(
                  /\B(?=(\d{3})+(?!\d))/g,
                  separadorMiles
                );
                return partes.join(separadorDecimal)+' %';
              }else{
                return '-';
              }
              
            },
            // data: "monto_renta",
            class: "text-left",
          },
          {
            title: "Renta bruta",
            render: function (data, type, row) {
              var monto_renta = row["monto_renta"];
              var monto_renta = parseFloat(monto_renta);
              var separadorDecimal = ".";
              var separadorMiles = ",";
              var numeroString = monto_renta.toFixed(2).toString();
              var partes = numeroString.split(".");
              partes[0] = partes[0].replace(
                /\B(?=(\d{3})+(?!\d))/g,
                separadorMiles
              );
              return partes.join(separadorDecimal);
            },
            // data: "monto_renta",
            class: "text-left",
          },
          {
            title: "Importe a pagar",
            render: function (data, type, row) {
              var monto_renta = row["total_pagar"];
              var monto_renta = parseFloat(monto_renta);
              var separadorDecimal = ".";
              var separadorMiles = ",";
              var numeroString = monto_renta.toFixed(2).toString();
              var partes = numeroString.split(".");
              partes[0] = partes[0].replace(
                /\B(?=(\d{3})+(?!\d))/g,
                separadorMiles
              );
              return partes.join(separadorDecimal);
            },
            // data: "total_pagar",
            class: "text-left",
          },
          {
            title: "Estado de pago",
            render: function (data, type, row) {
              var status_valor = row["etapa_id"];
              var html_status = "";
              switch (status_valor) {
                case "0":
                  html_status =
                    '<span class="badge bg-warning text-white">No pagado</span>';
                  break;
                case "1":
                  html_status =
                    '<span class="badge bg-danger text-white">Sin aprobar</span>';
                  break;
                case "2":
                  html_status =
                    '<span class="badge bg-danger text-white">aprobada</span>';
                  break;
                case "3":
                  html_status =
                    '<span class="badge bg-success text-white">Procesado</span>';
                  break;
                case "5":
                    html_status =
                      '<span class="badge bg-success text-white">Pagado</span>';
                  break;
              }

              return html_status;
            },
            class: "text-center",
          },
          {
            title: "Día de pago",
            data: "dia_de_pago",
          },
       
        ],
      });
		});


}