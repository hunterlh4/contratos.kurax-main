function sec_billetera()
{
	if(sec_id == "billetera")
	{
		if (sub_sec_id == "validar")
		{
			sec_billetera_validar();
		}
		else if (sub_sec_id == "transaccion")
		{
			sec_billetera_transaccion();
		}
		else if (sub_sec_id == "reporte")
		{
			sec_billetera_reporte();
		}
		else if (sub_sec_id == "mantenimiento")
		{
			sec_billetera_mantenimiento();
		}
	}
}