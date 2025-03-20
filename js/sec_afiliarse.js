function sec_afiliarse() {
	if(sec_id=="afiliarse"){

		var input_select = Object();
			input_select["tipo_cliente"]=$("form-group_tipo_cliente select");
		
		$("select.input_text ").each(function(index, el) {
			if($(el).data("col")=="tipo_cliente")		{
				$(el).change(function(event) {
					console.log($(el).val());
					$(".hidden_form").hide();
					if($(el).val()==1){ //Persona Natural
						$(".hidden_form_"+$(el).val()).show();
						$(".hidden_form_all").show();
						$('input[type=text][name=nombre]').attr('required','required');
						$('input[type=text][name=dni]').attr('required','required');
						$('input[type=text][name=ruc]').removeAttr('required');
						$('input[type=text][name=razon_social]').removeAttr('required');
					}else if($(el).val()==2){ //Persona Jurídica
						$(".hidden_form_"+$(el).val()).show();
						$(".hidden_form_all").show();
						$('input[type=text][name=nombre]').removeAttr('required');
						$('input[type=text][name=dni]').removeAttr('required');
						$('input[type=text][name=ruc]').attr('required','required');
						$('input[type=text][name=razon_social]').attr('required','required');
					}else{
					}
				});
			}
		});
		$(".form-group").each(function(index, el) {
			if($(el).hasClass('form-group_tipo_cliente')){

			}else{
			}
		});
		$("#select-ubigeo_departamento").off().change(function(event) {
			loading(true);
			$("#select-ubigeo_distrito").html("");
			$("#select-ubigeo_distrito").append($("<option>").html("- Seleccione una Provincia -").val(""));
			$("#select-ubigeo_distrito").attr('disabled',"disabled");
			//Seleccione Departamento
			var data = Object();
				data.departamento_id = $(this).val();

			auditoria_send({"proceso":"select-ubigeo_departamento","data":data});
			$.get('sys/build_html.php', {
				"opt":"select_ubigeo_departamento",
				"data":data
				},
				function(r) {
					var response = jQuery.parseJSON(r);
					$("#select-ubigeo_provincia").html("");
					$("#select-ubigeo_provincia").append($("<option>").html("Seleccione una Provincia").val(""));
					$.each(response, function(index, val) {
						 $("#select-ubigeo_provincia").append($("<option>").html(val).val(index));
					});
					$("#select-ubigeo_provincia").removeAttr('disabled');
					loading();
					$("#select-ubigeo_provincia").off().change(function(event) {
						loading(true);
						var data = Object();
							data.provincia_id = $(this).val();
							auditoria_send({"proceso":"select-ubigeo_provincia","data":data});
						$.get('sys/build_html.php', {
							"opt":"select_ubigeo_provincia",
							"data":data
							},
							function(r) {
								var response = jQuery.parseJSON(r);
								$("#select-ubigeo_distrito").html("");
								$("#select-ubigeo_distrito").append($("<option>").html("- Seleccione un Distrito -").val(""));
								$.each(response, function(index, val) {
									 $("#select-ubigeo_distrito").append($("<option>").html(val).val(index));
								});
								$("#select-ubigeo_distrito").removeAttr('disabled');
								loading();
								
						});
					});

			});
		});


		
		$('input[type=radio][name=como_se_entero]').change(function() {
			if (this.value == 'otros') {
				$(".hide_form_como_se_entero_des").show();
				$('.hide_form_como_se_entero_des textarea').attr('required','required');
				$(".hide_form_como_se_entero_des textarea").focus();
			}else{
				$(".hide_form_como_se_entero_des").hide();
				$('.hide_form_como_se_entero_des textarea').removeAttr('required');
			}
		});

		$("#add_cliente_form")
			.off()
			.submit(function(event) {
				event.preventDefault();
				console.log("add_cliente_form:SUBMIT");

				var save_data = Object();
					save_data.values=Object();
				$(".input_text").each(function(index, el) {
					save_data.values[$(el).attr("name")]=$(el).val();
				});
				$("input[type='radio']:checked").each(function(index, el) {
					save_data.values[$(el).attr("name")]=$(el).val();
				});
				console.log(save_data);
				auditoria_send({"proceso":"add_cliente_form","data":data});
				$.post('sys/set_data.php', {
					"opt": 'add_cliente_form'
					,"data":save_data
				}, function(r, textStatus, xhr) {
					console.log("add_cliente_form:ready");
					console.log(r);
					var response = jQuery.parseJSON(r);
					console.log(response);
				});
			});
		$(".add_cliente_btn")
			.off()
			.click(function(event) {
			});
	}
}