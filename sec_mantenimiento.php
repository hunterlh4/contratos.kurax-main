<?php
if($sec_id == 'mantenimiento')
{
	if($sub_sec_id == 'num_cuenta')
	{
		include("sec_mantenimiento_".$sub_sec_id.".php");
	}
	else if($sub_sec_id == 'razon_social')
	{
		include("sec_mantenimiento_".$sub_sec_id.".php");
	}
	else if($sub_sec_id == 'producto')
	{
		include("sec_mantenimiento_".$sub_sec_id.".php");
	}
	else if($sub_sec_id == 'canal_venta')
	{
		include("sec_mantenimiento_".$sub_sec_id.".php");
	}
	else if($sub_sec_id == 'canal_caja')
	{
		include("sec_mantenimiento_".$sub_sec_id.".php");
	}
}
?>
