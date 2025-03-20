function sec_soporte(){
	if(sec_id=="soporte"){
		if (sub_sec_id=="retiros") {
			sec_soporte_retiros();	
		}
		if (sub_sec_id=="alerta_goldenrace") {
			sec_soporte_alerta_goldenrace();
		}
		if (sub_sec_id=="alerta_terminal") {
			sec_soporte_alerta_terminal();
		}
	}
}