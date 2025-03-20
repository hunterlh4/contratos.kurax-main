function sec_conciliacion()
{
	if(sec_id == "conciliacion")
	{
		if (sub_sec_id == "venta")
		{
			sec_conciliacion_venta();
		}
		else if (sub_sec_id == "liquidacion")
		{
			sec_conciliacion_liquidacion();
		}
		else if (sub_sec_id == "reporte")
		{
			sec_conciliacion_reporte();
		}
		else if (sub_sec_id == "mantenimiento")
		{
			sec_conciliacion_mantenimiento();
		}
		else if (sub_sec_id == "anulacion")
		{
			sec_conciliacion_anulacion();
		}
		else if (sub_sec_id == "recaudacion")
		{
			sec_conciliacion_recaudacion();
		}
		else if (sub_sec_id == "devolucion")
		{
			sec_conciliacion_devolucion();
		}
		
	}
}

