<div class="content container-fluid vista_anuncios_anuncios">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Adminístrador Publicitario</h1>
			</div>
		</div>
	</div>

	
    <div class="panel-body no-pad">
        <form id="idformPromcionesMarketing" method="post" class="mt-5">
            <div class="row">
            <div class="col-xs-12 col-sm-4 col-lg-2 mt-3">
                <div class="form-group">
                    <label for="idInputFechaPromocion">Fecha Promoción:</label>
                    <input type="hidden" value="0" name="idPromocion" id="idPromocion">
                    <input class="form-control" type="date" name="idInputFechaPromocion" id="idInputFechaPromocion" value="<?php echo (new DateTime())->format('Y-m-d'); ?>" min="<?php //echo (new DateTime())->format('Y-m-d'); 
                                                                                                                                                                            ?>">
                </div>
            </div>
            <div class="col-xs-12 col-sm-8 col-lg-4 mt-3">
                <div class="form-group">
                    <label for="" >Nombre Promoción:</label>
                    <input class="form-control" type="text" name="idInputNombrePromocion" id="idInputNombrePromocion" placeholder="Ingrese nombre de promocion">
                </div>
            </div>
            </div>
            <div class="row">
                
                <div class="col-xs-12 col-sm-4 col-lg-2">
                    <br>
                    <div class="form-group">
                        <input class="btn btn-block btn-rounded btn-success" type="submit" value="Agregar Nueva Promoción" id="idBtnGuardarPromocionMarketin">
                    </div>
                </div>
                <div class="col-xs-12 col-sm-4 col-lg-2 ">
                    <br>
                    <div class="form-group">
                        <input class="btn btn-o btn-block btn-rounded btn-danger" type="button" value="Cancelar Edición" id="idBtnEditarCancelar" style="display:none;">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <!-- element with class added tenpory: table-responsive -->
        <!-- <div class="col-xs-12"> -->
            <table id="idTablePromociones" class="table table-striped table-bordered dt-responsive table-bordered table-hover dataTables_wrapper" style="width:100%">
                <thead>
                    <tr>
                        <!-- <th>Nro</th> -->
                        <th style="width: 5%">Código</th>
                        <th>Nombre Promoción</th>
                        <th>Fecha Promoción</th>
                        <th>Mes Promoción</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>


                </tbody>
            </table>
        <!-- </div> -->
        <div id="idLoaderReportePromocionMarketing"></div>
    </div>

    <div class="col-md-12 offset-md-1 hidden" id="idDivListDetalle" style="border: 1px solid black">
        <hr>
        <div class="col-12 col-sm-6 col-md-8">
            <table id="idTablePromocionesDetalle" class="dataTables_wrapper compact cell-border" style="width:100%">
                <thead>
                    <tr>
                        <!-- <th>Nro</th> -->
                        <th>local</th>
                        <th>cc_id</th>
                        <th>zona</th>
                        <th>jefe_comercial</th>
                        <th>url</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
        <div class="col-6 col-sm-3 col-md-4">
            <h6>Archivos de: <code id="idTitlePromotion"></code></h6>
            <hr>
            <div class="row text-center" id="idImgSetMarketing">
                <!-- <img src="http://someimage.jpg" style="width:100%; height:100%"> -->
            </div>
        </div>
    </div>
</div>

<script>
    
	function fncGetDataInsertUpdate() {
        var formData = new FormData();
        formData.append('idPromocion', $("#idPromocion").val());
        formData.append('fechaPromocion', $("#idInputFechaPromocion").val());
        formData.append('nombrePromocion', $("#idInputNombrePromocion").val());
        return formData;
    }

    function fncGuardarNuevoPagoCliente(data) {
        $.ajax({
            type: "POST",
            data: data,
            url: '/fastreport/promocion_marketing/PromocionGuardar.php',
            contentType: false,
            processData: false,
            cache: false,
            success: function(response) {
                var jsonData = JSON.parse(response);
                // if (jsonData.error == false) {
                //     swal("Registrado", jsonData.mensaje, "success");
                //     $('.btn-cerrar-modal').trigger('click');
                // } else {
                //     swal("Error", jsonData.mensaje, "error");
                // }

                $("#idBtnGuardarPromocionMarketin").val("Agregar Nueva Promoción");
                $('#idBtnEditarCancelar').hide();
                $("#idInputNombrePromocion").val("");
                $("#idPromocion").val("0");
                $("#idformPromcionesMarketing").validate().resetForm();

                // $("#idInputFechaPromocion").val('');
                fncRenderizarDataTable();
            }
        });
    }

    function fncRenderizarDataTable() {

        var table = $('#idTablePromociones').DataTable();
        table.clear();
        table.destroy();
        var table = $('#idTablePromociones').DataTable({
            'destroy': true,
            "ajax": {
                type: "GET",
                "url": "/fastreport/promocion_marketing/PromocionListar.php",
                cache: true

            },
            "dataSrc": function(json) {
                console.log(json);
                var result = JSON.parse(json);
                return result.data;
            },
            "columnDefs": [{
                "searchable": false,
                "orderable": false,
                "targets": 0
            }],
            "order": [
                [2, 'desc']
            ],
            "language": {
                url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
            },
            columnDefs: [{
                className: 'text-center',
                targets: [0, 1, 2, 3, 4]
            }, ],
            "columns": [

                // {
                //     "defaultContent": ''
                // },
                {
                    "data": "id",
                    render: function(data, type, row) {
                        var codigo = '[' + data + ']';
                        return codigo;
                    }
                },
                {
                    "data": "nombrePromocion"
                },
                {
                    "data": "fechaPromocion"
                },
                {
                    "data": "fechaPromocion",
                    render: function(data, type, row) {
                        var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                        var fecha = data;
                        var objDate = new Date(Date.parse(fecha));
                        //console.log(objDate.getMonth());
                        return meses[objDate.getMonth()];
                    }
                },

                {
                    "defaultContent": '',
                    render: function(data, type, row) {
                        var btn = '<button id="idBtnVerPromocion" class="btn btn-light"><i class="fa fa-list" aria-hidden="true"></i></button> ' +
                            '<button id="idBtnEditarPromocion" title="Editar" class="btn btn-warning"><i class="fa fa-edit" aria-hidden="true"></i></button> ' +
                            '<button id="idBtnEliminarPromocion" title="Eliminar" class="btn btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></button> ' +
                            '<button id="idBtnGenerarReportePromocion" class="btn btn-sm btn-info">Reporte</button> ';
                        return btn;
                    },
                    width: "280px"
                }
            ]


        });
        $('#idTablePromociones tbody').off('click');
        // table.on('order.dt search.dt', function() { //numeracion para la tabla
        //     table.column(0, {
        //         search: 'applied',
        //         order: 'applied'
        //     }).nodes().each(function(cell, i) {
        //         cell.innerHTML = i + 1;
        //     });
        // }).draw();

        $('#idTablePromociones tbody').on('click', '#idBtnEditarPromocion', function() {

            var data = table.row($(this).parents('tr')).data();
            //console.log(data);
            var rowData = null;
            if (data == undefined) {
                var selected_row = $(this).parents('tr');
                if (selected_row.hasClass('child')) {
                    selected_row = selected_row.prev();
                }
                rowData = $('#idTablePromociones').DataTable().row(selected_row).data();
            } else {
                rowData = data;

            }
            $("#idPromocion").val(rowData.id);
            $("#idInputFechaPromocion").val(rowData.fechaPromocion);
            $("#idInputNombrePromocion").val(rowData.nombrePromocion);
            $("#idBtnGuardarPromocionMarketin").val("Guardar Edición");
            $('#idBtnEditarCancelar').show()


        });
        $('#idTablePromociones tbody').on('click', '#idBtnEliminarPromocion', function() {
            var data = table.row($(this).parents('tr')).data();
            var rowData = null;
            if (data == undefined) {
                var selected_row = $(this).parents('tr');
                if (selected_row.hasClass('child')) {
                    selected_row = selected_row.prev();
                }
                rowData = $('#idTablePromociones').DataTable().row(selected_row).data();
            } else {
                rowData = data;

            }
            swal(
                {
                    title: 'Eliminar Registro',
                    text: '¿Está seguro de eliminar Promoción [' + rowData.id + '] ? ',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Si',
                    cancelButtonText: 'No',
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm)
                {
                    if(isConfirm)
                    {
                        var data = {
                            'id': rowData.id
                        }
                        $.ajax({
                            type: "POST",
                            data: data,
                            url: '/fastreport/promocion_marketing/PromocionEliminar.php',
                            beforeSend: function() {
                                $('#idLoaderReportePromocionMarketing').html('<div class="loading"><div class="alert alert-primary" role="alert"><img src="https://upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif"/><br/>Procesando, por favor espere...</div></div>');
                            },
                            success: function(response) {
                                resp = JSON.parse(response);
                                swal(
                                {
                                    title: '',
                                    text : resp.mensaje,
                                    type: resp.swaltipo,
                                    closeOnConfirm: false,
                                    closeOnCancel: true
                                },function(){
                                    swal.close;
                                    window.location.reload();
                                })
                            },
                            complete: function() {
                                $('#idLoaderReportePromocionMarketing').html('');
                            }
                        });
                    }
                })



            });
        $('#idTablePromociones tbody').on('click', '#idBtnGenerarReportePromocion', function() {
            var data = table.row($(this).parents('tr')).data();
            //console.log(data);
            var rowData = null;
            if (data == undefined) {
                var selected_row = $(this).parents('tr');
                if (selected_row.hasClass('child')) {
                    selected_row = selected_row.prev();
                }
                rowData = $('#idTablePromociones').DataTable().row(selected_row).data();
            } else {
                rowData = data;
            }
            var data = {
                'id': rowData.id,
                'nombrePromocion': rowData.nombrePromocion,
                'fechaPromocion': rowData.fechaPromocion
            }
            console.log(rowData);


            $.ajax({
                type: "POST",
                data: data,
                url: '/fastreport/promocion_marketing/PromocionReporte.php',
                beforeSend: function() {
                    $('#idLoaderReportePromocionMarketing').html('<div class="loading"><div class="alert alert-primary" role="alert"><img src="https://upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif"/><br/>Procesando, por favor espere...</div></div>');
                },
                success: function(resp) {
                	debugger;
                    let obj = JSON.parse(resp);
                    window.open(obj.data.path);
                },
                complete: function() {
                    $('#idLoaderReportePromocionMarketing').html('');
                }

            });

        });

        $('#idTablePromociones tbody').on('click', '#idBtnVerPromocion', function() {
            var data = table.row($(this).parents('tr')).data();
            //console.log(data);
            var rowData = null;
            if (data == undefined) {
                var selected_row = $(this).parents('tr');
                if (selected_row.hasClass('child')) {
                    selected_row = selected_row.prev();
                }
                rowData = $('#idTablePromociones').DataTable().row(selected_row).data();
            } else {
                rowData = data;
            }
            var data = {
                'id': rowData.id,
                'nombrePromocion': rowData.nombrePromocion,
                'fechaPromocion': rowData.fechaPromocion
            }
            $("#idDivListDetalle").removeClass('hidden');
            $("#idImgSetMarketing").html('');
            $("#idTitlePromotion").html(rowData.nombrePromocion);

            fncRenderizarDetalleDataTable(data);

        });

    }

    function fncRenderizarDetalleDataTable(data) {

        var table = $('#idTablePromocionesDetalle').DataTable();
        table.clear();
        table.destroy();
        var table = $('#idTablePromocionesDetalle').DataTable({
            'destroy': true,
            "autoWidth": false,
            'processing': true,
            "pageLength": 25,
            "language": {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> '
            },
            "ajax": {
                type: "POST",
                async: true,
                "url": "/fastreport/promocion_marketing/PromocionVerDetalle.php",
                "data": {
                    'id': data.id,
                    'nombrePromocion': data.nombrePromocion,
                    'fechaPromocion': data.fechaPromocion
                },
                dataSrc: function(json) {
                    return json.data;
                },
                beforeSend: function() {
                    $('#idLoaderReportePromocionMarketing').html('<div class="loading"><div class="alert alert-primary" role="alert"><img src="https://upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif"/><br/>Procesando, por favor espere...</div></div>');
                },
                complete: function() {
                    $('#idLoaderReportePromocionMarketing').html('');
                }
            },
            "columnDefs": [{
                "searchable": false,
                "orderable": false,
                "targets": 0
            }],
            "order": [
                [2, 'desc']
            ],
            "language": {
                url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
            },
            columnDefs: [{
                className: 'text-center',
                targets: []
            }, {
                "width": "5%",
                "targets": []
            }],
            createdRow: function(row, data, index) {
                if (data.url.length>0) {
                    $(row).css("background-color", "#4e9cf729");    
                  
                }else{
                    $(row).css("background-color", "#63636329");
                }
                $(row).css("border-bottom", "1px solid #f6f8f9"); 
                
                
            },
            "columns": [{
                    "data": "nombre"
                },
                {
                    "data": "cc_id"
                },
                {
                    "data": "zona"
                },
                {
                    "data": "jefe_comercial"
                },
                {
                    "data": 'url',
                    render: function(data, type, row) {
                        var btn = '';
                        //console.log(data);
                        $.map(data, function(url, key) {


                            btn += '<button title="Ver Imagen" data-url="' + url + '" onclick="fastSetImgMarketing(this);" href="#" class="btn btn-xs btn-primary">img' + (key + 1) + '<i class="fa fa-eye"></i></button>';

                        });


                        return btn;
                    }
                }
            ]


        });
        $('#idTablePromocionesDetalle tbody').off('click');

    }

    function fastSetImgMarketing(identifier) {
        // alert("data-id:" + $(identifier).data('url') + ", data-option:" + $(identifier).data('option'));
        var img = '<a href="' + $(identifier).data('url') + '" target="_blank">';
        img += '<img class="center-block" src="' + $(identifier).data('url') + '"  style="width:100%">';
        img += '</a>';
        $("#idImgSetMarketing").html(img);
    }
</script>