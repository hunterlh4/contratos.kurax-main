function sec_comprobante()
{
	if(sec_id == "comprobante")
	{
		if (sub_sec_id == "pago")
		{
			sec_comprobante_pago();
		}
		else if (sub_sec_id == "reporte")
		{
			sec_comprobante_reporte();
		}
		else if (sub_sec_id == "mantenimiento")
		{
			sec_comprobante_mantenimiento();
		}
		
	}
}

