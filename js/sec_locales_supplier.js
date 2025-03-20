if ($("#local_supplier").length == 0) {
} else {
  // $(".soloNumeros").on("input", function () {
  //   var valorInput = $(this).val();
  //   var valorNumerico = valorInput.replace(/\D/g, "");
  //   $(this).val(valorNumerico);
  // });
  fncGetDataToTableSupplier();
  document.getElementById("id_form_local_supplier").addEventListener("submit", function (e) {
    e.preventDefault();
    fncDataSaveSupplier();
  });
  function fncDataSaveSupplier() {
    var formData = new FormData();

    if ($("#id_input_id_local_supplier_name").val().trim().length < 3 || $("#id_input_id_local_supplier_name").val().trim() == 0) {
      swal(
        "Error",
        "El Nombre debe tener mínimo 3 caracteres. Por favor, ingrese un nombre válido.",
        "error"
      );
    } else if ($("#id_input_id_local_supplier").val() == "") {
      swal(
        "Error",
        "El Proveedor ID está vacío. Por favor, ingrese un valor válido.",
        "error"
      );
    } else {
      var selectedValue = $("#id_select_cnv_local_supplier").val();
      formData.append(
        "local_caja_id",
        $("#id_select_caja_local_supplier").val()
      ); 
      formData.append(
        "id_caja_detalle_tipo",
        $('#id_select_cnv_local_supplier').find(':selected').attr('data-caja-detalle'));
      formData.append(
        "canal_de_venta_id",
        $("#id_select_cnv_local_supplier").val()
      );
      formData.append("local_id", $("#item_id_temporal").val());
      formData.append("proveedor_id", $("#id_input_id_local_supplier").val());
      formData.append("nombre", $("#id_input_id_local_supplier_name").val().trim());
      formData.append("action", "save_gr_supplier_id");

      var formDataObjectAudit = {};
      formData.forEach(function (value, key) {
        formDataObjectAudit[key] = value;
      });

      Swal.fire(
        {
          title: "¿Estas seguro?",
          text: "¡Proveedor este local será configurado!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Si, continuar",
          cancelButtonText: "No, cancelar",
          width: 500,
        }).then((result) => {
          if(result.isConfirmed){
            $.ajax({
              type: "POST",
              data: formData,
              url: "app/routes/SupplierGr/index.php",
              contentType: false,
              processData: false,
              cache: false,
              success: function (response) {
                Swal.fire({
                  title: "Registrado!",
                  text: "El ID Proveedor fue registrado correctamente.",
                  icon: "success",
                  showConfirmButton: true,
                  width: 500,
                  //timer: 2000,
                  });
                fncGetDataToTableSupplier();
                auditoria_send({
                  proceso: "actualizar_agregar_Proveedor_id",
                  data: formDataObjectAudit,
                });
              },
              error: function (jqXHR, textStatus, responseText) {
                var jsonData = JSON.parse(jqXHR.responseText);
                if (jqXHR.status === 400) {
                  Swal.fire({
                    title: "Registrado en otro Local!",
                    text:  jsonData.data,
                    icon: "error",
                    showConfirmButton: true,
                    width: 500,
                    });  
                }
              },
              beforeSend: function () {},
            });
          }
        }
      );
    }
  }

  function fncGetDataToTableSupplier() {
    var formData = new FormData();
    formData.append("local_id", $("#item_id_temporal").val());
    formData.append("action", "list_supplier");

    $.ajax({
      type: "POST",
      data: formData,
      url: "app/routes/SupplierGr/index.php",
      contentType: false,
      processData: false,
      cache: false,
      success: function (response) {
        var jsonData = JSON.parse(response);
        //debugger;
        if (jsonData.error == true) {
        } else {
          fncRenderTableLocalSupplier(jsonData.data);
          //  loading();
        }
      },
      beforeSend: function () {
        // loading(true);
      },
    });
  }
  function fncRenderTableLocalSupplier(data = {}) {
    var table = $("#tabla_form_local_supplier").DataTable();
    table.clear();
    table.destroy();
    let table_id = "#tabla_form_local_supplier";
    let $table = $(table_id);
    let datatable;
    if (!$.fn.DataTable.isDataTable(table_id)) {
      datatable = $table.DataTable({
        destroy: true,
        data: data,
        dom: "Bfrtip",
        scrollX: true,
        "bAutoWidth": true,
        
        lengthMenu: [
          [10, 25, 50, -1],
          ["10 registros", "25 registros", "50 registros", "Mostrar Todos"],
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
            // {
            //   className: "btn-success",
            //   text: '<i class="fa fa-file-pdf-o"></i>',
            //   action: function (e, dt, node, config) {
            //     //fnc_get_data_en_torito_excel();
            //   },
            // },
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
            targets: [1, 3],
          },
          { targets: [0,4], visible: false },
          { className: "text-right", targets: [5] },
          { className: 'dt-left', className: 'dt-head-left', targets: [2] },
          { targets: 5, orderable: false },
        ],
        columns: [
          {
            data: "canal_venta_codigo",
          },
          {
            data: "local_caja_nombre",
          },
          {
            data: "proveedor_nombre",
            render: function(data,type,row) {
              return "["+row.caja_detalle_tipo_nombre+"] : "+row.proveedor_nombre;
            }
          },
          {
            data: "proveedor_supplier_id",
          },
          {
            data: "proveedor_habilitado",
          },
          {
            data: "proveedor_estado",
            render: function (data, type, row) {
              //console.log(row);
              let btn_principal =
                ' <button data-principal="0" class="btn btn-xs btn-warning btn_principal_supplier" type="button"  title="Marcado como Principal"><i class="glyphicon glyphicon-star"></i></button>';
              let btn_secondary =
                ' <button data-principal="1" class="btn btn-xs btn-info    btn_principal_supplier" type="button"  title="Marcado como Secundario"><i class="glyphicon glyphicon-star-empty"></i></button>';
              let btn_status =
                ' <button class="btn btn-xs btn-danger btn_eliminar_supplier" type="button" title="Eliminar Proveedor"><i class="glyphicon glyphicon-trash"></i></button>';
              let btn_habilitado =
                ' <button class="btn btn-xs btn-success btn_habilitado_btn_deshabilitado" type="button" title="Proveedor Habilitado"><i class="glyphicon glyphicon-ok"></i></button>';
              let btn_inhabilitado =
                ' <button class="btn btn-xs btn-danger btn_habilitado_btn_deshabilitado" type="button" title="Proveedor Deshabilitado"><i class="glyphicon glyphicon-remove"></i></button>';
              var btn = "";
              if (row.canal_venta_id == 21) {
                if (row.proveedor_principal == 1) {
                  btn = btn_principal + " " + btn_status;
                } else {
                  btn = btn_secondary + " " + btn_status;
                }
              } else {
                btn = btn_status;
              }

              //console.log(btn);
              if (row.proveedor_habilitado == 1) {
                btn = btn + btn_habilitado;
              } else {
                btn = btn + btn_inhabilitado;
              }
              return btn;
            },
          },
        ],

        order: [[0, "asc"]],
        // rowGroup: {
        //   dataSrc: "caja_detalle_tipo_nombre"
        // },
        drawCallback: function (settings) {
          var api = this.api();
          var rows = api.rows({ page: "all" }).nodes();
          var last = null;

          // Remove the formatting to get integer data for summation
          var intVal = function (i) {
            return typeof i === "string"
              ? i.replace(/[\$,]/g, "") * 1
              : typeof i === "number"
              ? i
              : 0;
          };
          total = [];
          totalActive = [];
          var active = 0;
          var inactive = 1;
          api
            .column(0, { page: "all" })
            .data()
            .each(function (group, i) {
              group_assoc = group.replace(/ /g, "_"); //clases de los rows
              //console.log(group_assoc);

              //total[group_assoc]=0;

              if (typeof total[group_assoc] !== "undefined") {
                total[group_assoc]++;
              } else {
                total[group_assoc] = 1;
              }
              var rowData = api.row(i).data();

              if (typeof totalActive[group_assoc] === "undefined") {
                // Si no está definido, inicialízalo como un objeto vacío
                totalActive[group_assoc] = {};
                if (rowData.proveedor_estado) {
                  totalActive[group_assoc]["activos"] = 1;
                  totalActive[group_assoc]["inactivos"] = 0;
                } else {
                  totalActive[group_assoc]["inactivos"] = 0;
                  totalActive[group_assoc]["activos"] = 1;
                }
              } else {
                if (rowData.proveedor_estado) {
                  totalActive[group_assoc]["activos"]++;
                } else {
                  totalActive[group_assoc]["inactivos"]++;
                }
              }

              if (last !== group) {
                $(rows)
                  .eq(i)
                  .before(
                    '<tr style="background-color: #ddd !important;"><td class="text-center">' +
                      '<h4><span class="badge badge-dark">' +
                      group +
                      '</span></h4></td><td></td><td></td><td class=" text-center ' +
                      group_assoc +
                      '"></td></tr>'
                  );

                last = group;
              }
            });
          //debugger;
          var sumTotalMonto = 0;
          for (var key in total) {
            //$("." + key).html('<h4><span class="badge badge-primary">' + "" + total[key] + '</span></h4>');
            //console.log(total);
            //console.log(total[key]);
            //sumTotalMonto += total[key];
          }
          for (var key in totalActive) {
            $("." + key).append(
              '<h4><span class="badge badge-primary">' +
                "" +
                totalActive[key]["activos"] +
                "</span></h4>"
            );
            //$("." + key).append('<h4><span class="badge badge-danger">' + "" + totalActive[key]['inactivos'] + '</span></h4>');
            //console.log(total);
            //console.log(total[key]);
            //sumTotalMonto += total[key];
          }

          //$("#idTotalMontoTorito").html(sumTotalMonto.toFixed(2));
        },
        // createdRow: function (row, data, type) {
        //   if (row.proveedor_estado == 0) {
        //     $(row).hide();
        //   }
        // }
      });
    } else {
      datatable = new $.fn.dataTable.Api(table_id);
      datatable.clear();
      datatable.rows.add(data);
      datatable.draw();
    }
    $("#tabla_form_local_supplier tbody").off("click");
    $("#tabla_form_local_supplier tbody").on(
      "click",
      ".btn_principal_supplier",
      function () {
        var data = table.row(this).data();
        var rowData = null;
        if (data == undefined) {
          var selected_row = $(this).parents("tr");
          if (selected_row.hasClass("child")) {
            selected_row = selected_row.prev();
          }
          rowData = $("#tabla_form_local_supplier")
            .DataTable()
            .row(selected_row)
            .data();
        } else {
          rowData = data;
        }
        //debugger;
        fncSetPrincipaLocalSupplier(
          rowData.proveedor_id,
          rowData.proveedor_principal
        );
      }
    );
    $("#tabla_form_local_supplier tbody").on(
      "click",
      ".btn_eliminar_supplier",
      function () {
        // debugger;

        var data = table.row(this).data();

        var rowData = null;
        if (data == undefined) {
          var selected_row = $(this).parents("tr");
          if (selected_row.hasClass("child")) {
            selected_row = selected_row.prev();
          }
          rowData = $("#tabla_form_local_supplier")
            .DataTable()
            .row(selected_row)
            .data();
        } else {
          rowData = data;
        }
        fncDeleteLocalSupplier(rowData.proveedor_id);
      }
    );
    $("#tabla_form_local_supplier tbody").on(
      "click",
      ".btn_habilitado_btn_deshabilitado",
      function () {
        // debugger;

        var data = table.row(this).data();

        var rowData = null;
        if (data == undefined) {
          var selected_row = $(this).parents("tr");
          if (selected_row.hasClass("child")) {
            selected_row = selected_row.prev();
          }
          rowData = $("#tabla_form_local_supplier")
            .DataTable()
            .row(selected_row)
            .data();
        } else {
          rowData = data;
        }
        fncUpdatedEnabledLocalSupplier(rowData.proveedor_id,rowData.proveedor_habilitado);
      }
    );
    
    return datatable;
  }

  function fncSetPrincipaLocalSupplier(id, id_proveedor_principal) {
    var formData = new FormData();
    if (id_proveedor_principal == null || id_proveedor_principal == 0) {
      formData.append("proveedor_principal", 1);
    } else {
      formData.append("proveedor_principal", 0);
    }

    formData.append("id", id);
    formData.append("action", "updated_principal_supplier");

    $.ajax({
      type: "POST",
      data: formData,
      url: "app/routes/SupplierGr/index.php",
      contentType: false,
      processData: false,
      cache: false,
      success: function (response) {
        var jsonData = JSON.parse(response);
        //debugger;
        if (jsonData.error == true) {
        } else {
          fncGetDataToTableSupplier();
          //  loading();
        }
      },
      beforeSend: function () {
        // loading(true);
      },
    });
  }
  function fncDeleteLocalSupplier(id) {
    var formData = new FormData();
    formData.append("id", id);
    formData.append("action", "delete_principal_supplier");
    swal(
      {
        title: "¿Estas seguro?",
        text: "¡Proveedor este local será Eliminado!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si, continuar",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: true,
        closeOnCancel: true,
      },
      function (isConfirm) {
        if (isConfirm) {
          $.ajax({
            type: "POST",
            data: formData,
            url: "app/routes/SupplierGr/index.php",
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
              var jsonData = JSON.parse(response);
              //debugger;
              if (jsonData.error == true) {
              } else {
                fncGetDataToTableSupplier();
                //  loading();
              }
            },
            beforeSend: function () {
              // loading(true);
            },
          });
        }
      }
    );
  }

  function fncUpdatedEnabledLocalSupplier(id,status) {
    console.log(status);
    var formData = new FormData();
    if (status == null || status == 0) {
      formData.append("status", 1);
    } else {
      formData.append("status", 0);
    }
    formData.append("id", id);
    formData.append("action", "enable_disabled_supplier");
    swal(
      {
        title: "¿Estas seguro?",
        text: "¡ El Estado Del Proveedor sera Modificado!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si, continuar",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: true,
        closeOnCancel: true,
      },
      function (isConfirm) {
        if (isConfirm) {
          $.ajax({
            type: "POST",
            data: formData,
            url: "app/routes/SupplierGr/index.php",
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
              var jsonData = JSON.parse(response);
              //debugger;
              if (jsonData.error == true) {
              } else {
                fncGetDataToTableSupplier();
                //  loading();
              }
            },
            beforeSend: function () {
              // loading(true);
            },
          });
        }
      }
    );
  }
}
