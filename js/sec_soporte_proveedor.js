$(document).ready(function () {
	if ($('#sec_soporte_proveedor').length == 0) {
	} else {

		$(".class_channel_sec").click(function (e) {
			e.preventDefault();
			$(".class_channel_sec").removeClass('active');
			$(this).addClass('active');
			fncRenderTableProviderPerService();
			fncRenderTableProviderPerServiceFileAnalize();
			$("#sec_soporte_proveedor_title_provider").html($('#id_sec_soporte_proveedor_ul_providers .active').data('servnombre'));
			
		});
		fncRenderTableProviderPerService();
		fncRenderTableProviderPerServiceFileAnalize();
		$("#sec_soporte_proveedor_title_provider").html($('#id_sec_soporte_proveedor_ul_providers .active').data('servnombre'));




	}
});
function secSoporteProveedorGetChannel() {
	var idChannel = $('#id_sec_soporte_proveedor_ul_providers .active').data('channel');
	//console.log(idChannel);
	return (idChannel);

}

function secSoporteProveedorGetCanalVentaId() {
	var idCanalVenta = $('#id_sec_soporte_proveedor_ul_providers .active').data('canal_venta_id');
	//console.log(idChannel);
	return (idCanalVenta);

}

function fncRenderTableProviderPerServiceFileAnalize(data = {}) {
	var table = $('#tbl_sec_soporte_proveedor_file_analyze').DataTable();
	table.clear();
	table.destroy();
	loading(true);
	var table = $('#tbl_sec_soporte_proveedor_file_analyze').DataTable({
		'destroy': true,
		"autoWidth": false,
		'processing': true,
		"lengthChange": false,
		"info": false,
		"dom": 'Bfrtip',
		"fnDrawCallback": function (oSettings) {
			$(function () {
				$('[data-toggle="popover"]').popover()
			})
		},
		buttons: {
			buttons: [				
				{
					text: '<i class="fa fa-save"></i> Guardar',
					className: function (e, dt, node, config) {
						
						var hidden = '';
						// if ($('#sec_mepa_movilidad_txt_estado').val() == "2") 
						// {
						// 	hidden = 'invisible';
						// }
						return 'btn btn-danger ' + hidden;
					},
					action: function (e, dt, node, config) {
						var data_insert = (dt.rows().data());
						//console.log(data_insert);
						let new_data = new Array();
					
						$.each(data_insert, function (indexInArray, valueOfElement) { 
							if (valueOfElement.type_data==2) {
								new_data.push(valueOfElement);
							}	
						});
						fnc_save_provider_per_service(new_data);

					}
				}
			]
		},
		"language": {
			processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> '
		},
		"data": data,
		"ordering": false,
		"language": {
			url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
		},
		"columns": [

			{
				"data": "cc_id",
				render: function (data, type, row) {
					var codigo = '[' + data + ']';
					return codigo;
				}
			},

			{
				"data": "type_data",
				render: function (data, type, row) {
					
					return '<button type="button" class="btn btn-primary btn-xs" data-container="body" data-toggle="popover"' +
						'data-placement="top" data-content="' + row.type_data_msj + '"><span class="glyphicon glyphicon-search"></span></button>';
				}
			},
			{
				"data": "id"
			}

		], 
		"createdRow": function (row, data, dataIndex, cells) {
			var code_color = '';
			if (data.type_data == 2) {
				code_color = '#93ebd0';
			}else if(data.type_data == 3){
				code_color = '#eaa1a7';
			}
			else if(data.type_data == 1){
				code_color = '#a8bdcf';
			}
			$(row).css("background-color", code_color);
		}
	});
	$('#tbl_sec_soporte_proveedor_file_analyze tbody').off('click');
	

}

function fncRenderTableProviderPerService() {
	var table = $('#tbl_sec_soporte_proveedor').DataTable();
	table.clear();
	table.destroy();
	loading(true);
	var table = $('#tbl_sec_soporte_proveedor').DataTable({
		'destroy': true,
		"autoWidth": false,
		'processing': true,
		"language": {
			processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> '
		},
		"ajax": {
			type: "POST",
			async: false,
			"url": "sys/get_soporte_porveedor.php",
			"data": { accion: 'list_provider_per_channel', channel: secSoporteProveedorGetChannel(), canal_venta_id: secSoporteProveedorGetCanalVentaId()},
			dataSrc: function (json) {
				loading();
				return json.data;
			}
		},
		"ordering": false,
		"language": {
			url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
		},
		"columns": [

			{
				"data": "cc_id",
				render: function (data, type, row) {
					var codigo = '[' + data + ']';
					return codigo;
				}
			},
			{
				"data": "nombre"
			},
			{
				"data": "proveedor_id"
			},
			{
				"data": "nombre_canal"

			},
			{
				"data": "id",
				render: function (data, type, row) {


					return '<a title="Ver local" target="_blank" href="./?sec_id=locales&amp;item_id='+row.id+'#tab=tab_config" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>';
				}
			}

		]
	});
	$('#tbl_sec_soporte_proveedor tbody').off('click');

}


function fncGetFileSecSoporteProveedor() {

	var idChannel = $('#id_sec_soporte_proveedor_ul_providers .active').data('channel');
	var formData = new FormData();
	formData.append('accion', 'analyze_file');
	formData.append('idChannel', idChannel);
	formData.append('canal_venta_id', secSoporteProveedorGetCanalVentaId());
	//var filesDisashop = document.querySelector('[data-disashopId="' + idDisashop + '"]').files.length;
	var file = document.getElementById('file-input').files.length;
	if (file > 0) {
		formData.append("file", document.getElementById('file-input').files[0]);
		document.getElementById('file-input').value = "";

		$.ajax({
			type: "POST",
			data: formData,
			url: 'sys/get_soporte_porveedor.php',
			contentType: false,
			processData: false,
			cache: false,
			success: function (response) {
				var jsonData = JSON.parse(response);
				if (!jsonData.error) {
					fncRenderTableProviderPerServiceFileAnalize(jsonData.data);
					loading();
				} else {
					swal("Algo paso durante la transacción", '', "error");
				}

			}, beforeSend: function () {
				//loading(true);
			}
		});
	}

}

const fnc_save_provider_per_service = (data_save) => {
	var name_service = $('#id_sec_soporte_proveedor_ul_providers .active').data('servnombre');
	var id_channel = $('#id_sec_soporte_proveedor_ul_providers .active').data('channel');
	
	swal({
		title: "¿Estás seguro?",
		text: 'Se insertaran datos para '+name_service,
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Si, proceder",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: false,
		closeOnCancel: false,

	},
		function (isConfirm) {
			var data = {
				'accion': 'save_data_provider_per_service',
				'data_save': JSON.stringify(data_save),
				'id_channel': id_channel,
				'canal_venta_id': secSoporteProveedorGetCanalVentaId()
			}
			if (isConfirm) {
				$.ajax({
					type: "POST",
					data: data,
					url: 'sys/get_soporte_porveedor.php',
					cache: false,
					success: function (response) {
						
						var jsonData = JSON.parse(response);
						if (jsonData.error == false) {
							swal("OK", jsonData.message, "success");
							fncRenderTableProviderPerServiceFileAnalize();
							fncRenderTableProviderPerService();
						} else {
							swal("Error", jsonData.message, "error");
						}
					}
				});
			} else {
				swal("Cancelado", "Los datos no se enviaron", "error");
			}
		});
}