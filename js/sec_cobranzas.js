function sec_cobranzas() {
	if(sec_id=="cobranzas"){
		console.log("sec_cobranzas");
		sec_cobranzas_estados_de_cuenta();
		if (sub_sec_id=="detalle_estados_cuenta") {
			sec_cobranzas_detalle_estados_cuenta();
		}
	}
}