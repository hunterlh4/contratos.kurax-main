function sec_prestamo()
{
	if(sec_id == "prestamo")
	{
		if (sub_sec_id == "slot")
		{
			sec_prestamo_slot();
		}
		else if (sub_sec_id == "slot_detalle_solicitud")
		{
			sec_prestamo_slot_detalle_solicitud();
		}
		else if (sub_sec_id == "boveda")
		{
			sec_prestamo_boveda();
		}
		else if (sub_sec_id == "tesoreria")
		{
			sec_prestamo_tesoreria();
		}
		else if (sub_sec_id == "tesoreria_atencion")
		{
			sec_prestamo_tesoreria_atencion();
		}
		else if (sub_sec_id == "detalle_tesoreria_programacion")
		{
			sec_prestamo_detalle_tesoreria_programacion();
		}
		else if (sub_sec_id == "boveda_detalle_solicitud")
		{
			sec_prestamo_boveda_detalle_solicitud();
		}
		else if (sub_sec_id == "configuracion")
		{
			sec_prestamo_configuracion();
		}
	}
}