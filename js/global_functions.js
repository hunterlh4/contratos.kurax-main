function crearDataTable(selector, url, data, columnDefs, buttons = ['pageLength']) {
    return $(selector).dataTable({
        language: {
            decimal: "",
            emptyTable: "No existen registros",
            info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
            infoEmpty: "Mostrando 0 a 0 de 0 entradas",
            infoFiltered: "",
            infoPostFix: "",
            thousands: ",",
            lengthMenu: "Mostrar _MENU_ entradas",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            zeroRecords: "Sin resultados",
            search: "Filtrar:",
            paginate: {
                first: "Primero",
                last: "Ultimo",
                next: "Siguiente",
                previous: "Anterior",
            },
            aria: {
                sortAscending: ": activar para ordenar columna ascendente",
                sortDescending: ": activar para ordenar columna descendente",
            },
            buttons: {
                pageLength: {
                    _: "Mostrar %d Resultados",
                    '-1': "Mostrar todo"
                }
            },
        },
        scrollY: true,
        scrollX: true,
        dom: 'Bfrtip',
        buttons: buttons,
        serverSide: false, // Modo cliente
        ajax: {
            url: url,
            data: data,
            type: "POST",
            dataType: "json",
            error: function (e) {
                console.error("Error al cargar los datos: ", e);
            },
        },
        columnDefs: columnDefs,
        createdRow: function (row, data, dataIndex) {
            if (data[0] === 'error') {
                var colspan = columnDefs.length;
                $('td:eq(0)', row).attr('colspan', colspan).css('text-align', 'center');
                for (let i = 1; i < colspan; i++) {
                    $('td:eq(' + i + ')', row).css('text-align', 'center');
                }
                this.api().cell($('td:eq(0)', row)).data(data[1]);
            }
        },
        destroy: true,
        lengthMenu: [10, 20, 30, 40, 50, 100],
        order: [[0, 'desc']]
    }).DataTable();
}