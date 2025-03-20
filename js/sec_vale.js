function sec_vale(){
	if(sec_id == "vale"){
		if (sub_sec_id == "solicitud") {
			sec_vale_nuevo();
			sec_vale_solicitud();			
		}

		if (sub_sec_id == "control_interno") {
			sec_vale_control_interno();		
		}

		if (sub_sec_id == "mantenimiento") {
			sec_vale_mantenimiento();	
			sec_vale_motivo();	
			sec_vale_parametros_fraccionamiento();	
			sec_vale_parametro_general();		
		}

		if (sub_sec_id == "sincronizacion") {
			sec_vale_sincronizacion();		
		}

		if (sub_sec_id == "fraccionamiento_manual") {
			sec_vale_fraccionamiento_manual();		
		}
	}
}