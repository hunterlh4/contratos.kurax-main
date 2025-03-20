function sec_marketing() {
	if(sec_id=="marketing"){
		console.log("sec:marketing");
		if (sub_sec_id=="pizarra") {
			sec_marketing_pizarra();			
		}
		if (sub_sec_id=="solicitud") {
			sec_marketing_solicitud();			
		}
		if (sub_sec_id=="nuevo") {
			sec_marketing_nuevo();			
		}
		if (sub_sec_id=="detalle_solicitud") {
			sec_marketing_detalle_solicitud();			
		}
		if (sub_sec_id=="promocion_marketing")
		{
			sec_marketing_promocion_marketing();
		}
	}
}