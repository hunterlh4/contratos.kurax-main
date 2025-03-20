$(document).ready(function () {
    if ($("#sec_id_logs").length) {
      fncRenderDataTableViewLogs();
      var secViewCountLog = 0;
    }
    function fncRenderDataTableViewLogs() {
      var table = $("#idTableLogsView").DataTable();
      table.clear();
      table.destroy();
  
      var table = $("#idTableLogsView").DataTable({
        destroy: true,
        bLengthChange: false,
        pageLength: 20,
        fnInitComplete: function () {
          // Cambiar el tamaño de la letra de la tabla
          $(".ui-widget").css("font-size", "12px");
        },
        dom: "ftip",
        ajax: {
          type: "POST",
          async: false,
          url: "app/routes/ViewLog/index.php",
          data: { action: "list_logs" },
        },
        dataSrc: function (json) {
          console.log(json);
          var result = JSON.parse(json);
          return result.data;
        },
        columnDefs: [
          {
            searchable: false,
            orderable: false,
            targets: 0,
          },
        ],
        order: [[3, "desc"]],
        language: {
          url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
        },
        columnDefs: [
          {
            className: "text-center",
            targets: [0, 1],
          },
        ],
        columns: [
          {
            data: "file",
            render: function (data, type, row) {
              return data + "_(" + row.lineCount + ")";
            },
          },
          {
            data: "size",
          },
          {
            data: "lastModification",
          },
          {
            data: "fullPath",
            render: function (data, type, row) {
              return '<button title="Ver" type="button" class="btn-ver "><i class="fa fa-eye"></i></button>';
            },
          },
        ],
  
        // $logFilesInfo[] = [
        //     'file' => $file,
        //     'fullPath' => $fullPath,
        //     'size' => $sizeMB,
        //     'lineCount' => $lineCount,
        //     'lastModification' => $lastModification,
        // ];
      });
      $("#idTableLogsView tbody").off("click");
  
      $("#idTableLogsView tbody").on("click", ".btn-ver", function () {
        var data = table.row($(this).parents("tr")).data();
        var rowData = null;
        if (data == undefined) {
          var selected_row = $(this).parents("tr");
          if (selected_row.hasClass("child")) {
            selected_row = selected_row.prev();
          }
          rowData = $("#idTableLogsView").DataTable().row(selected_row).data();
        } else {
          rowData = data;
        }
        //console.log(rowData.file);
        fncLoadLogs(rowData.file);
      });
    }
    function fncLoadLogs(file) {
      //$("#idSecLogTextNameFile").text(file);
      $("#idSecLogHiddenFrom").val(0);
      $("#idSecLogHiddenTo").val(500);
      $("#idSecLogHiddenName").val(file);
      fncSecLogsView();
    }
  
    function fncSecLogsView(from, to, file) {
      try {
        var file = $("#idSecLogHiddenName").val();
        var from = $("#idSecLogHiddenFrom").val();
        var to = $("#idSecLogHiddenTo").val();
        //debugger;
        var data = {
          action: "get_data_log",
          file: file,
          from: from,
          to: to,
        };
        //debugger;
        //auditoria_send({ "proceso": "guardar_address_mac_device_local", "data": data });
        $.ajax({
          url: "app/routes/ViewLog/index.php",
          type: "POST",
          data: data,
          beforeSend: function () {
            //loading("true");
          },
          complete: function () {
            loading();
          },
          success: function (resp) {
            var response = JSON.parse(resp);
            var logPanel = document.getElementById("logPanel");
  
            // Asigna una cadena vacía al contenido
            logPanel.innerHTML = "";
            $("#idSecLogDownloadButton").show();
            fncSecLogsPrintView(response);
          },
          error: function () {},
        });
      } catch (e) {
        console.log("Error de TRY-CATCH -- Error: " + e);
      }
    }
  
    function fncSecLogsPrintView(data) {
      var logPanel = document.getElementById("logPanel");
  
      //secViewCountLog = data.data.length;
      data.data.forEach(function (logText, index) {
        //var logEntry = document.createElement("div");
  
        $("#logPanel").append("<p class='texto-code'>" + logText + "</p>");
        var logPanel = document.getElementById("logPanel");
  
        // Desplázate al final del contenido editable
        logPanel.scrollTop = logPanel.scrollHeight;
      });
    }
  
    $("#idSecLogDownloadButton").click(function (e) { 
      e.preventDefault();
      var nowCountLog=parseInt($("#idSecLogHiddenTo").val());
      $("#idSecLogHiddenTo").val(nowCountLog+500);
      fncSecLogsView();
    });
  });