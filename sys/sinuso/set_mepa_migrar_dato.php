<?php

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");
require_once '/var/www/html/sys/helpers.php';


include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_migrar_dato_file_select_opcion_9") 
{
	$error = '';

	$user_id = $login?$login['id']:null;
	
	if((int) $user_id > 0)
	{
		if(isset($_FILES['mepa_reporte_migrar_dato_select_opcion_9_file']))
		{
			
			$tipo       = $_FILES['mepa_reporte_migrar_dato_select_opcion_9_file']['type'];
			$tamanio    = $_FILES['mepa_reporte_migrar_dato_select_opcion_9_file']['size'];
			$archivotmp = $_FILES['mepa_reporte_migrar_dato_select_opcion_9_file']['tmp_name'];
			$lineas     = file($archivotmp);

			$i = 0;
			$j = 0;
			$error = '';

			foreach ($lineas as $linea)
			{
				if($i != 0)
				{
					$datos = explode(";", $linea);

					if (count((array)$datos) < 3)
					{
			            $datos = explode(",", $linea);
			        }

					$dni = !empty($datos[0]) ? ($datos[0]) : '';
					$nombres = !empty($datos[1]) ? ($datos[1]) : '';
				    $apaterno = !empty($datos[2]) ? ($datos[2]) : '';
				    $amaterno = !empty($datos[3]) ? ($datos[3]) : '';
				    $parametro_razon_social_id = !empty($datos[4]) ? ($datos[4]) : '';

				    //area operaciones id = 21;
					//area comercial id = 15;

				    $query_validacion = "
								SELECT
									p.id AS personal_id,
								    p.dni
								FROM tbl_personal_apt p
								WHERE p.dni = $dni AND (p.area_id = 21 OR p.area_id = 15)
								";

					$reg_verificacion = $mysqli->query($query_validacion);

					$cant_reg_verificacion = $reg_verificacion->num_rows;

					if($cant_reg_verificacion > 0)
					{
						$row = $reg_verificacion->fetch_assoc();
						$personal_id = $row["personal_id"];


						$query_update = "
										UPDATE tbl_personal_apt 
											SET razon_social_id = '".$parametro_razon_social_id."'
										WHERE id = '".$personal_id."'
										";

						$mysqli->query($query_update);

						if($mysqli->error)
						{
							$error .= $mysqli->error;

							$result["http_code"] = 400;
							$result["status"] = "Ocurrio un error.";
							$result["error"] = $error;

							echo json_encode($result);
							exit();
						}

						$j++;
					}
				}

				$i++;
			}

			$result["http_code"] = 200;
			$result["status"] = "Información migrada.";
			$result["cant_registros"] = $i - 1;
			$result["cant_registros_migrados"] = $j;

			echo json_encode($result);
			exit();
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = "Por favor seleccionar un archivo.";

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

echo json_encode($result);

?>