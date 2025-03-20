$(document).ready(function() {
	$(".loading_box").html("").hide();
	$("#login_form").submit(function(event) {
		$(".loading_box").html("").show();
	});
});

function validarTextoUsuarioLogin(input) {
	var textoInput = input.value;
	var regex = /^[a-zA-Z.ñÑ\d]*$/;
	var regex_puntos = /\.{2,}/g;

	if (!regex.test(textoInput)) {
		input.value = textoInput.replace(/[^a-zA-ZñÑ\d.]/g, '').replace(regex_puntos, '.');
	}else{
		input.value = textoInput.replace(regex_puntos, '.');
	}
}