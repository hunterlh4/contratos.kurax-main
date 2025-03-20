function sec_destinatarios(){
	if(sub_sec_id=="destinatarios"){
		console.log("sec:destinatarios");
		sec_destinatarios_events();
	}
}

function sec_destinatarios_events(){
	$(".save_btn")
		.off()
		.click(function(event) {
			var btn = $(this);
			sec_destinatarios_save(btn);
		});
	
	$(".select2").select2({
		closeOnSelect: true,
		width:"100%"
	});

	$("#tbl_destinatarios")
		.DataTable();
}

function sec_destinatarios_save(btn){
	var set_data = {};
	$(".save_data").each(function(index, el) {
		set_data[$(el).attr("name")]=$(el).val();
	});
	// console.log(set_data);
	$.post('/sys/set_destinatarios.php', {
		"sec_destinatarios_save": set_data
	}, function(r) {
		loading();
		try{
			var obj = jQuery.parseJSON(r);
			// console.log(obj);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_destinatarios_save_error","data":set_data});
				swal({
					title: "Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
					custom_highlight($(".save_data[name='"+obj.error_focus+"']"));
					setTimeout(function(){
						$(".save_data[name='"+obj.error_focus+"']").val("").focus();	
					}, 10);
				});
			}else{
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_destinatarios_save_done","data":set_data});
				swal({
					title: "Guardado!",
					text: "",
					type: "success",
					timer: 5000,
					closeOnConfirm: true
				},
				function(){
					m_reload();
					if(btn.data("then")=="reload"){
						if(set_data.id=="new"){
							set_data.id=obj.id;
							auditoria_send({"proceso":"add_item","data":set_data});
							window.location="./?sec_id="+sec_id+"&sub_sec_id="+sub_sec_id+"&item_id="+obj.id;
						}else{
							auditoria_send({"proceso":"save_item","data":set_data});
							swal.close();
							m_reload();
						}
					}else if(btn.data("then")=="exit"){
						auditoria_send({"proceso":"save_item","data":set_data});
						window.location="./?sec_id="+sec_id+"&sub_sec_id="+sub_sec_id;
					}else{
					}
				});
			}
		}catch(err){
			// console.log(r);
		}
		// console.log(r);
	});
}



