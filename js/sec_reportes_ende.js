$(document).ready(function () {
  if ($("#id_contenedor_reporte_pagados_de_otras_tiendas_all").length == 0) {
  } else {
    fncGetDataTableEnDeAll();
    var dataExcelEnDe = [];
    $("#cbLocalesToritoEnde").select2({
      width: "resolve",
      placeholder: "Todos",
      tags: true,
    });
    $("#btn_consultar_en_de_all")
      .off("click")
      .on("click", function () {
        fncGetDataTableEnDeAll();
      });
    function fncGetDataTableEnDeAll() {
      var formData = new FormData();
      formData.append("fromDate", $("#from_date").val());
      formData.append("locals", $("#cbLocalesToritoEnde").val());
      formData.append("toDate", $("#to_date").val());

      formData.append("action", "ende_cabecera");
      loading(true);
      $.ajax({
        type: "POST",
        data: formData,
        url: "app/routes/ToritoEnDe/index.php",
        contentType: false,
        processData: false,
        cache: false,
        success: function (response) {
          var jsonData = JSON.parse(response);
          if (jsonData.error == true) {
          } else {
            dataExcelEnDe = jsonData.data;
            tableEndeAll(jsonData.data);
            // loading();
          }
        },
        beforeSend: function () {
          //loading(true);
        },
      });
    }
    function tableEndeAll(data = {}) {
      var table = $("#table_en_de_all").DataTable();
      table.clear();
      table.destroy();
      0;
      let table_id = "#table_en_de_all";
      let $table = $(table_id);
      let datatable;
      if (!$.fn.DataTable.isDataTable(table_id)) {
        datatable = $table.DataTable({
          destroy: true,
          data: data,
          dom: "Bfrtip",
          pageLength: 50,
          lengthMenu: [
            [10, 25, 50, 100, 200, -1],
            [
              "10 registros",
              "25 registros",
              "50 registros",
              "100 registros",
              "200 registros",
              "Mostrar Todos",
            ],
          ],
          buttons: {
            buttons: [
              {
                extend: "pageLength",
                className: "btn-dark",
                exportOptions: {
                  orthogonal: "exportcsv",
                },
              },
              {
                className: "btn-success",
                text: '<i class="fa fa-file-excel-o"></i>',
                action: function (e, dt, node, config) {
                  loading(true);
                  generateExcelReportEnDe();
                  loading();
                },
              },
              // {
              // 	text: 'Generar EXCEL',
              // 	className: function (e, dt, node, config) {

              // 		var hidden = '';
              // 		if ($('#sec_mepa_movilidad_txt_estado').val() == "2")
              // 		{
              // 			hidden = 'invisible';
              // 		}
              // 		return 'btn btn-danger ' + hidden;
              // 	},
              // 	action: function (e, dt, node, config) {
              // 		close_mobility_expenses($('#idCajaChicaMovilidad').val());
              // 	}
              // }
            ],
            dom: {
              button: {
                className: "btn",
              },
              buttonLiner: {
                tag: null,
              },
            },
          },
          language: {
            url: "/locales/Datatable/es.json",
          },
          columnDefs: [
            {
              className: "text-center",
              targets: [0, 1, 2, 3, 4, 6, 5],
            },
            { targets: 0, visible: true },
            { targets: 1, orderable: false },
            { targets: 2, orderable: false },
            { targets: 3, orderable: false },
            { targets: 4, orderable: false },
            { targets: 7, orderable: false },
          ],
          columns: [
            {
              data: "loca_id",
            },
            {
              data: "registro_red",
            },
            {
              data: "nombre_local",
            },
            {
              data: "canal_venta",
            },
            {
              data: "red",
            },
            {
              data: "pagado_en_otra_tienda",
            },

            {
              data: "pagado_de_otra_tienda",
            },
            {
              data: "fecha",
            },
          ],
          order: [[0, "asc"]],
          // "drawCallback": function (settings) {

          // 	var api = this.api();
          // 	var rows = api.rows({ page: 'all' }).nodes();
          // 	var last = null;

          // 	// Remove the formatting to get integer data for summation
          // 	var intVal = function (i) {
          // 		return typeof i === 'string' ?
          // 			i.replace(/[\$,]/g, '') * 1 :
          // 			typeof i === 'number' ?
          // 				i : 0;
          // 	};
          // 	total = [];
          // 	totalEn = [];
          // 	api.column(0, { page: 'all' }).data().each(function (group, i) {
          // 		group_assoc = group;//clases de los rows
          // 		//console.log(group_assoc);
          // 		if (typeof total[group_assoc] != 'undefined') {
          // 			total[group_assoc] = total[group_assoc] + intVal(api.column(6).data()[i]); //columna a sumar
          // 		} else {
          // 			total[group_assoc] = intVal(api.column(6).data()[i]);
          // 		}
          // 		if (typeof totalEn[group_assoc] != 'undefined') {
          // 			totalEn[group_assoc] = totalEn[group_assoc] + intVal(api.column(5).data()[i]); //columna a sumar
          // 		} else {
          // 			totalEn[group_assoc] = intVal(api.column(5).data()[i]);
          // 		}
          // 		if (last !== group) {
          // 			$(rows).eq(i).before(
          // 				'<tr style="background-color: #ddd !important;"><td class="text-center">' + '<h4><span class="badge badge-dark">' + group + '</span></h4></td><td></td><td></td><td></td><td></td><td class=" text-center ' + group_assoc + 'En"></td><td class=" text-center ' + group_assoc + '"></td><td></td></tr>'
          // 			);

          // 			last = group;
          // 		}
          // 	});
          // 	var sumTotalMonto = 0;
          // 	for (var key in total) {
          // 		$("." + key).html('<h4><span class="badge badge-primary">' + "S/." + total[key].toFixed(2) + '</span></h4>');
          // 		//console.log(total);
          // 		//console.log(total[key]);
          // 		sumTotalMonto += total[key];
          // 	}
          // 	for (var key in totalEn) {
          // 		$("." + key+'En').html('<h4><span class="badge badge-primary">' + "S/." + totalEn[key].toFixed(2) + '</span></h4>');
          // 		//console.log(total);
          // 		//console.log(total[key]);
          // 		sumTotalMonto += total[key];
          // 	}

          //$("#idTotalMontoTorito").html(sumTotalMonto.toFixed(2));
          //},
          rowGroup: {
            startRender: null,
            endRender: function (rows, group) {
              let sumSalary = rows.data();
              let pagado_en_otra_tienda = 0;
              let pagado_de_otra_tienda = 0;
              let nombre_local = "";
              // Iterar sobre las filas y sumar las columnas correspondientes
              sumSalary.each(function (value, index) {
                // Sumar la columna "pagado_de_otra_tienda_1" (ajusta el índice según tu estructura de datos)
                pagado_en_otra_tienda +=
                  parseFloat(value.pagado_en_otra_tienda) || 0;
                pagado_de_otra_tienda +=
                  parseFloat(value.pagado_de_otra_tienda) || 0;
                nombre_local = value.nombre_local;
              });
              let tr = document.createElement("tr");

              addCell(tr, "[" + group + "]" + nombre_local, 5);
              addCell(tr, pagado_en_otra_tienda.toFixed(2));
              addCell(tr, pagado_de_otra_tienda.toFixed(2));
              addCell(tr, "");

              return tr;
            },
            dataSrc: "loca_id",
          },
          initComplete: function () {
            this.api()
              .columns()
              .every(function () {
                var column = this;
                var select = $(
                  '<select><option value="">--Seleccione--</option></select>'
                )
                  .appendTo($(column.footer()).empty())
                  .on("change", function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());

                    column
                      .search(val ? "^" + val + "$" : "", true, false)
                      .draw();
                  });

                column
                  .data()
                  .unique()
                  .sort()
                  .each(function (d, j) {
                    select.append(
                      '<option value="' + d + '">' + d + "</option>"
                    );
                  });
              });
            loading();
          },
          createdRow: function (row, data, index) {
            $("td:eq(0)", row).css("background-color", "rgb(94 97 113 / 12%)");
            $("td:eq(1)", row).css("background-color", "rgb(94 97 113 / 12%)");
            $("td:eq(2)", row).css("background-color", "rgb(94 97 113 / 12%)");
          },
          footerCallback: function (row, data, start, end, display) {
            let api = this.api();
            let intVal = function (i) {
              return typeof i === "string"
                ? i.replace(/[\$,]/g, "") * 1
                : typeof i === "number"
                ? i
                : 0;
            };

            // Total over all pages
            totalEn = api
              .column(5)
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);

            // Total over this page
            pageTotalEn = api
              .column(5, { page: "current" })
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);

            // Update footer

            // Total over all pages
            totalDe = api
              .column(6)
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);

            // Total over this page
            pageTotalDe = api
              .column(6, { page: "current" })
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);

            // Update footer

            $("#idTotaleN").html(
              "Pagina Total: S/." +
                pageTotalEn.toFixed(2) +
                "<hr> Total: S/." +
                totalEn.toFixed(2)
            );
            $("#idTotalDe").html(
              "Pagina Total: S/." +
                pageTotalDe.toFixed(2) +
                "<hr> Total: S/." +
                totalDe.toFixed(2)
            );
          },
        });

        $("#tabla_torito_en_de tbody").off("click");
      } else {
        datatable = new $.fn.dataTable.Api(table_id);
        datatable.clear();
        datatable.rows.add(data);
        datatable.draw();
      }
      return datatable;
    }
  }
  function addCell(tr, content, colSpan = 1) {
    
    let td = document.createElement("th");

    td.colSpan = colSpan;
    td.classList.add("text-center");
    td.textContent = content;
    tr.style.cssText = "background-color: #ddd !important;";
    tr.appendChild(td);
  }

  function generateExcelReportEnDe() {
    if (dataExcelEnDe.length <= 0) {
      swal('Aviso', 'No hay Datos en la Tabla', 'warning');	
      return;
    }
    var dataExcel = {};
    dataExcelEnDe.forEach(function (objeto) {
      if (!dataExcel.hasOwnProperty(objeto.loca_id)) {
        dataExcel[objeto.loca_id] = [];
      }
      dataExcel[objeto.loca_id].push(objeto);
    });

    let wb = new ExcelJS.Workbook();
    let workbookName = "en_de_reporte_"+Math.floor(Date.now() / 1000)+".xlsx";
    let worksheetName = "Reporte";
    let ws = wb.addWorksheet(worksheetName, {
      properties: {
        tabColor: { argb: "FFFF0000" },
      },
    });

    ws.columns = [
      {
        key: "LOCAL_ID",
        header: "LOCAL_ID",
        width: 15,
        style: {
          alignment: { horizontal: "center" },
        },
        hidden: false,
      },
      {
        key: "LOCAL_RED",
        header: "LOCAL_RED",
        width: 15,
        style: {
          alignment: { horizontal: "center" },
        },
        hidden: false,
      },
      {
        key: "LOCAL_NOMBRE",
        header: "LOCAL_NOMBRE",
        width: 22,
        style: {
          alignment: { horizontal: "center" },
        },
        hidden: false,
      },
      {
        key: "CNV",
        header: "CNV",
        width: 15,
        style: {
          alignment: { horizontal: "center" },
        },
        hidden: false,
      },
      {
        key: "RED",
        header: "RED",
        width: 15,
        style: {
          alignment: { horizontal: "center" },
        },
        hidden: false,
      },
      {
        key: "EN_OTRA_TIENDA",
        header: "EN OTRA TIENDA",
        width: 15,
        style: {
          alignment: { horizontal: "center" },
          numFmt: "#,##0.00",
        },
        hidden: false,
      },
      {
        key: "DE_OTRA_TIENDA",
        header: "DE OTRA TIENDA",
        width: 15,
        style: {
          alignment: { horizontal: "center" },
          numFmt: "#,##0.00",
        },
        hidden: false,
      },
      {
        key: "FECHA",
        header: "FECHA",
        width: 19,
        style: {
          alignment: { horizontal: "center" },
        },
        hidden: false,
      },
    ];

    let iniRow = 2;
    let totalEn = 0.0;
    let totalDe = 0.0;
    $.map(dataExcel, function (local, indexLocal) {
      let subTotalEn = 0.0;
      let subTotalDe = 0.0;
      let title = "";
      $.map(local, function (reg, indexReg) {
        ws.getCell("A" + iniRow).value = reg.loca_id;
        ws.getCell("B" + iniRow).value = reg.registro_red;
        ws.getCell("C" + iniRow).value = reg.nombre_local;
        ws.getCell("D" + iniRow).value = reg.canal_venta;
        ws.getCell("E" + iniRow).value = reg.red;
        ws.getCell("F" + iniRow).value = parseFloat(reg.pagado_en_otra_tienda);
        ws.getCell("G" + iniRow).value = parseFloat(reg.pagado_de_otra_tienda);
        ws.getCell("H" + iniRow).value = reg.fecha;

        subTotalEn += parseFloat(reg.pagado_en_otra_tienda);
        subTotalDe += parseFloat(reg.pagado_de_otra_tienda);
        title = "[" + reg.loca_id + "]" + reg.nombre_local;
        iniRow++;
      });
      ws.mergeCells("A" + iniRow + ":E" + iniRow);
      ws.getCell("A" + iniRow).value = title;
      ws.getCell("F" + iniRow).value = subTotalEn;
      ws.getCell("G" + iniRow).value = subTotalDe;
      totalEn += subTotalEn;
      totalDe += subTotalDe;
      iniRow++;
    });
    ws.getCell("E" + iniRow).value = "TOTAL";
    ws.getCell("F" + iniRow).value = totalEn;
    ws.getCell("G" + iniRow).value = totalDe;

    // ESTILOS PARA TOTALIZADOS
    const columnas = ['A', 'B', 'C', 'D','E','F','G','H']; 
    // Recorre cada columna
    columnas.forEach(columna => {
      // Aplica las configuraciones a cada celda en la fila inicial (iniRow)
      ws.getCell(columna + iniRow).fill = {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: "415b75" } // Color de fondo #415b75
      };

      ws.getCell(columna + iniRow).font = {
        color: { argb: "FFFFFF" }, // Color de letra blanco
      };

      ws.getRow(iniRow).height = 30; // Altura de la celda ajustada a 30 unidades (cambia este valor según lo necesites)

      ws.getCell(columna + iniRow).alignment = {
        vertical: "middle", // Centrar verticalmente el contenido
        horizontal: "center" // Centrar horizontalmente el contenido
      };
    });

    ws.getRow(iniRow).height = 30;
    var headerStyle = {
      alignment: { horizontal: "center" },
      font: { color: { argb: "FFFFFF" }, bold: true },
      fill: {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: "3474B7" }, 
      },
    };

    ws.getRow(1).eachCell(function (cell) {
      cell.style = headerStyle;
    });

    wb.xlsx.writeBuffer().then(function (buffer) {
      saveAs(
        new Blob([buffer], { type: "application/octet-stream" }),
        workbookName
      );
    });
  }
});