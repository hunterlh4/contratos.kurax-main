function sec_consultas(){
	if(sec_id=="consultas"){
		if (sub_sec_id=="dni") {
			sec_consultas_dni();
		}
		else if(sub_sec_id=="kasnet_directorio_recaudos"){
			sec_consultas_kasnet_directorio_recaudos();
		}
	}
}
