function sec_mantenimiento()
{
	if(sec_id == "mantenimiento")
	{
		if (sub_sec_id == "num_cuenta")
		{
			sec_mantenimiento_num_cuenta();
		}
		else if (sub_sec_id == "razon_social")
		{
			sec_mantenimiento_razon_social();
		}
		else if (sub_sec_id == "producto")
		{
			sec_mantenimiento_producto();
		}
		else if (sub_sec_id == "canal_venta")
		{
			sec_mantenimiento_canal_venta();
		}
		else if (sub_sec_id == "canal_caja")
		{
			sec_mantenimiento_canal_caja();
		}
	}
}