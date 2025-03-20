function sec_herramientas_ti()
{
	if(sec_id == "herramientas_ti")
	{
		if (sub_sec_id == "mantenimiento")
		{
			sec_herramientas_ti_mantenimiento();
		}
		if (sub_sec_id == "contratos" || sub_sec_id == "kasnet" || sub_sec_id == "mesa_de_partes")
			{
				sec_herramientas_ti_proceso();
			}
	}
}