function sec_cobranzas_estados_de_cuenta_diferencia(){
    console.log('sec_cobranzas_estados_de_cuenta_diferencia');
    $(document).ready(function(){
        $('#ajuste').on('input', function() {
            let ajuste = isNaN(parseFloat($(this).val())) ? 0 : parseFloat($(this).val());
            let total_deuda = parseFloat($('#total_deuda').val());
            $('#deuda_final').val((total_deuda + ajuste).toFixed(2));
            // console.log(ajuste);
        });
    });
    
    // OPEN MODAL
    $("#tbl_cobranzas_diferencias tbody .btn_ver_detalle_diferencia").off("click").on("click",function(){
		$('#modal_detalle_solve').modal({
			backdrop: 'static',
			keyboard: false
		});
        let periodo_id = this.getAttribute('data-periodo-id');
        let local_id = this.getAttribute('data-local-id');
        sec_cobranzas_diferencia_detalle(periodo_id, local_id);
    })


    $("#modal_detalle_solve #sec_estados_de_cuenta_guardar_btn").off("click").on("click",function(){
        var form = document.getElementById('form_cobranzas_diferencias');
        sec_diferencias_solve(form);
    })
}

function sec_diferencias_solve(form){
    loading(true);
    let dataForm = new FormData(form);

    let periodo_year = $('#periodo_year').val();
    let periodo_mes = $('#periodo_mes').val();
    let periodo_rango = $('#periodo_rango').val();
    let periodo_inicio = $('#periodo_inicio').val();
    let periodo_fin = $('#periodo_fin').val();
    let periodo_rango_int = $('#periodo_rango_int').val();
    let local_id = $('#local_id').val();
    let descripcion = $('#descripcion').val();
    let periodo_id = $('#periodo_id').val();

    dataForm.append("periodo_year", periodo_year);
    dataForm.append("periodo_mes", periodo_mes);
    dataForm.append("periodo_rango", periodo_rango);
    dataForm.append("periodo_inicio", periodo_inicio);
    dataForm.append("periodo_fin", periodo_fin);
    dataForm.append("periodo_rango_int", periodo_rango_int);
    dataForm.append("local_id", local_id);
    dataForm.append("descripcion", descripcion);
    dataForm.append("periodo_id", periodo_id);

	dataForm.append("set_cobranzas_estados_de_cuenta_diferencia","set_cobranzas_estados_de_cuenta_diferencia");
	result = {};
	for (let entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	let set_data = {};
	set_data = result;

	$.ajax({
		url: 'sys/set_cobranzas_estados_de_cuenta_diferencia.php',
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_cobranzas_estados_de_cuenta_diferencia_save_error","data":set_data});
				loading(false);
				swal({
					title: "Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
			}else{
				loading(false);
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_cobranzas_estados_de_cuenta_diferencia_save_done","data":set_data});	
				swal({
					title: "Ajuste Exitoso",
					text: obj.msg,
					type: "success",
					timer: 5000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				location.reload();
			}
		}
	});
}

function sec_cobranzas_diferencia_detalle(periodo_id, local_id){
	loading(true);
    var set_data = {};
    set_data.periodo_id = periodo_id;
    set_data.local_id = local_id;
	set_data.sec_cobranzas_diferencia_detalle = "sec_cobranzas_diferencia_detalle";

	$.ajax({
		url: 'sys/set_cobranzas_estados_de_cuenta_diferencia.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
            console.log(obj);
			loading();
			$("#modal_detalle_solve").modal("show");
			$("#liquidacion_fg").val(obj.liquidaciones_fg);
            $("#total_deuda").val(obj.total_deuda);
            $("#periodo_year").val(obj.periodo_year);
            $("#periodo_mes").val(obj.periodo_mes);
            $("#periodo_rango").val(obj.periodo_rango);
            $("#periodo_inicio").val(obj.periodo_inicio);
            $("#periodo_fin").val(obj.periodo_fin);
            $("#periodo_rango_int").val(obj.periodo_rango_int);
            $("#local_id").val(obj.local_id);
            $("#periodo_id").val(obj.periodo_id);
		},
		complete : function (){
			loading();
		},
        error: function () {
			loading();
       	}
	});
}