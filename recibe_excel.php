<?php 

include("sys/db_connect.php");
include("sys/sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

$tipo       = $_FILES['dataCliente']['type'];
$tamanio    = $_FILES['dataCliente']['size'];
$archivotmp = $_FILES['dataCliente']['tmp_name'];
$lineas     = file($archivotmp);

$i = 0;
$j = 0;
$error = '';


// MIGRAR DATA DE EXCEL DE CODIGO DE SUMINISTRO
foreach ($lineas as $linea)
{
	if($i != 0)
	{
		$datos = explode(";", $linea);
		
		if (count((array)$datos) < 3)
		{
			$datos = explode(",", $linea);
        }

        $tipo_servicio = 0;

        $inmueble_id = !empty($datos[1]) ? ($datos[1]) : '';
        $suministro_id = !empty($datos[4]) ? ($datos[4]) : '';
        $nro_suministro = !empty($datos[12]) ? ($datos[12]) : '0';
        
        $compromiso_pago = !empty($datos[13]) ? ($datos[13]) : '0';
        $compromiso_pago_todo = explode("|", $compromiso_pago);

        $monto_o_porcentaje = !empty($datos[14]) ? ($datos[14]) : '0';

        $servicio_empresa_agua = !empty($datos[15]) ? ($datos[15]) : '0';
        $servicio_empresa_agua_todo = explode("|", $servicio_empresa_agua);

        $servicio_empresa_luz = !empty($datos[16]) ? ($datos[16]) : '0';
        $servicio_empresa_luz_todo = explode("|", $servicio_empresa_luz);

        if($servicio_empresa_luz_todo[0] != 0)
        {
        	$tipo_servicio = 1;
        }
        else if($servicio_empresa_agua_todo[0] != 0)
        {
        	$tipo_servicio = 2;
        }

        echo '<p style="text-aling:center; color:#333;">Nº: '. $i .'</p>';
        
		echo '<p style="text-aling:center; color:#333;">suministro_id: '. $suministro_id .'</p>';
		echo '<p style="text-aling:center; color:#333;">nro_suministro: '. $nro_suministro .'</p>';
		echo '<p style="text-aling:center; color:#333;">compromiso_pago_todo: '. $compromiso_pago_todo[0] .'</p>';
		echo '<p style="text-aling:center; color:#333;">monto_o_porcentaje: '. $monto_o_porcentaje .'</p>';
		echo '<p style="text-aling:center; color:#333;">tipo_servicio: '. $tipo_servicio .'</p>';
		echo '<p style="text-aling:center; color:#333;">servicio_empresa_agua_todo: '. $servicio_empresa_agua_todo[0] .'</p>';
		echo '<p style="text-aling:center; color:#333;">servicio_empresa_luz_todo: '. $servicio_empresa_luz_todo[0] .'</p>';
		
		$query_update = 
		"
			UPDATE cont_inmueble_suministros 
				SET nro_suministro = '".$nro_suministro."',
					tipo_compromiso_pago_id = '".$compromiso_pago_todo[0]."',
			        monto_o_porcentaje = '".$monto_o_porcentaje."'
			WHERE id = '".$suministro_id."'
		";

		echo '<p style="text-aling:center; color:#333;">Query UPDATE cont_inmueble_suministros: '. $query_update .'</p>';

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			echo '<p style="text-aling:center; color:#333;">Error: '. $error .'</p>';

			exit();
		}

		if($tipo_servicio == 1)
		{
			//LUZ

			$query_update_luz = 
			"
				UPDATE cont_inmueble 
					SET id_empresa_servicio_luz = '".$servicio_empresa_luz_todo[0]."'
				WHERE id = '".$inmueble_id."'
			";

			echo '<p style="text-aling:center; color:#333;">Query UPDATE query_update_luz: '. $query_update_luz .'</p>';

			$mysqli->query($query_update_luz);

			if($mysqli->error)
			{
				$error .= $mysqli->error;

				echo '<p style="text-aling:center; color:#333;">Error: '. $error .'</p>';

				exit();
			}
		}
		else if($tipo_servicio == 2)
		{
			//AGUA

			$query_update_agua = 
			"
				UPDATE cont_inmueble 
					SET id_empresa_servicio_agua = '".$servicio_empresa_agua_todo[0]."'
				WHERE id = '".$inmueble_id."'
			";
			
			echo '<p style="text-aling:center; color:#333;">Query UPDATE query_update_agua: '. $query_update_agua .'</p>';

			$mysqli->query($query_update_agua);

			if($mysqli->error)
			{
				$error .= $mysqli->error;

				echo '<p style="text-aling:center; color:#333;">Error: '. $error .'</p>';

				exit();
			}
		}

		$j++;
	}

	$i++;
}

echo '<p style="text-aling:center; color:#333;">Cantidad de vueltas foreach: '. $i .'</p>';

echo '<p style="text-aling:center; color:#333;">Cantidad de updates: '. $j .'</p>';

exit();

/*
// ACTULIZAR CONTRATOS: ID DEL CONTRATO ACTUALIZAR EN LA COLUMNA DE contratos_id DE LA TABLA tbl_locales

$query_select_locales = 
"
	SELECT
		l.id, l.cc_id
	FROM tbl_locales l
	WHERE l.cc_id IS NOT NULL AND l.contrato_id IS NULL
	ORDER BY l.id
";

$data_query = $mysqli->query($query_select_locales);

while($row = $data_query->fetch_assoc())
{
	$id_local = $row["id"];
	$cc_id_local = $row["cc_id"];

	$query_select_contrato = 
	"
		SELECT
			c.contrato_id, c.cc_id
		FROM cont_contrato c
		WHERE c.cc_id = '".$cc_id_local."'
		LIMIT 1
	";

	$reg_query_select_contrato = $mysqli->query($query_select_contrato);

	$cant_reg_query_select_contrato = $reg_query_select_contrato->num_rows;

	if($cant_reg_query_select_contrato > 0)
	{
		$row = $reg_query_select_contrato->fetch_assoc();
		$contrato_id_contrato = $row["contrato_id"];
		$cc_id_contrato = $row["cc_id"];

		$query_update = 
		"
			UPDATE tbl_locales 
				SET contrato_id = '".$contrato_id_contrato."'
			WHERE id = '".$id_local."'
		";

		$mysqli->query($query_update);

		echo '<p style="text-aling:center; color:#333;">Query UPDATE: '. $query_update .'</p>';

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			echo '<p style="text-aling:center; color:#333;">Error: '. $error .'</p>';
			exit();
		}
		else
		{
			echo '<p style="text-aling:center; color:#333;">local ID ACTUALIZADO: '. $id_local .'</p>';
		}

		$j++;
	}

	$i++;
}

echo '<p style="text-aling:center; color:#333;">Cantidad de vueltas foreach: '. $i .'</p>';

echo '<p style="text-aling:center; color:#333;">Cantidad de updates: '. $j .'</p>';

exit();
*/

/*
foreach ($lineas as $linea)
{
	if($i != 0)
	{
		$datos = explode(";", $linea);
		
		if (count((array)$datos) < 3)
		{
			$datos = explode(",", $linea);
        }

        $id = !empty($datos[0]) ? ($datos[0]) : '';
		$id_tipo_servicio = !empty($datos[1]) ? ($datos[1]) : '';
		echo '<p style="text-aling:center; color:#333;">id_tipo_servicio: '. $id_tipo_servicio .'</p>';
		$id_tipo_servicio = explode("|", $id_tipo_servicio);
	    
	    $ruc = !empty($datos[2]) ? ($datos[2]) : '';
	    $razon_social = !empty($datos[3]) ? ($datos[3]) : '';
	    $nombre_comercial = !empty($datos[4]) ? ($datos[4]) : '';
		
		$query_update = 
	    "
	    	UPDATE cont_local_servicio_publico_empresas 
				SET id_tipo_servicio = '".$id_tipo_servicio[0]."', 
					ruc = '".$ruc."'
			WHERE id = '".$id."'
		";

		echo '<p style="text-aling:center; color:#333;">Query UPDATE: '. $query_update .'</p>';

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			echo '<p style="text-aling:center; color:#333;">Error: '. $error .'</p>';

			exit();
		}

		$j++;
	}

	$i++;
		
}

echo '<p style="text-aling:center; color:#333;">Cantidad de vueltas foreach: '. $i .'</p>';

echo '<p style="text-aling:center; color:#333;">Cantidad de updates: '. $j .'</p>';

*/
?>