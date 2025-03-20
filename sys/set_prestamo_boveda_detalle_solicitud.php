<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


$hora_actual = date("H:i:s");

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_detalle_guardar_recibe_dinero") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$query_update = "";

	$sec_prestamo_boveda_id = $_POST["sec_prestamo_boveda_id"];
	$txt_motivo = $_POST["txt_motivo"];

	if((int)$usuario_id > 0)
	{
		$query_update = "
					UPDATE tbl_caja_prestamo_boveda
						SET observacion_recibe_dinero = '".$txt_motivo."'
					WHERE id = '".$sec_prestamo_boveda_id."'
					";


		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos registrados.";
			$result["error"] = $error;
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_detalle_guardar_atencion") 
{
    $usuario_id = $login?$login['id']:null;
    $created_at = date("Y-m-d H:i:s");
    $error = '';
    $query_update = "";

    $prestamo_boveda_id = $_POST["prestamo_boveda_id"];
    $txt_situacion = $_POST["txt_situacion"];

    if((int)$usuario_id > 0)
    {
        $select_horarios_boveda = 
        "
            SELECT
                id, nombre, descripcion, valor
            FROM tbl_prestamo_parametro
            WHERE id IN (2)
        ";

        $query_horarios_boveda = $mysqli->query($select_horarios_boveda);

        $row_count = mysqli_num_rows($query_horarios_boveda);

        $valor_atencion = 0;

        if($row_count > 0)
        {
            while($li = $query_horarios_boveda->fetch_assoc())
            {
                $reg_id = $li["id"];
                $reg_valor = $li["valor"];

                $valor_atencion = $reg_valor;
            }
        }

        $atender_solicitud_horario = false;
        $hora_actual = date("H:i:s");

        if($hora_actual < $valor_atencion)
        {
            $atender_solicitud_horario = true;
        }

        if($atender_solicitud_horario === true)
        {
            if($txt_situacion == 2)
            {
                //APROBADO
                $query_update = "
                    UPDATE tbl_caja_prestamo_boveda 
                        SET situacion_jefe_etapa_id = '".$txt_situacion."',
                        usuario_jefe_atencion_id = '".$usuario_id."',
                        fecha_atencion_jefe = '".date('Y-m-d H:i:s')."'
                    WHERE id = '".$prestamo_boveda_id."'
                    ";
            }
            else
            {
                //RECHAZADO
                $query_update = "
                    UPDATE tbl_caja_prestamo_boveda 
                        SET situacion_jefe_etapa_id = '".$txt_situacion."',
                        usuario_jefe_atencion_id = '".$usuario_id."',
                        fecha_atencion_jefe = '".date('Y-m-d H:i:s')."',
                        situacion_tesoreria_etapa_id = 9
                    WHERE id = '".$prestamo_boveda_id."'
                    ";
            }
            
            
            $mysqli->query($query_update);

            if($mysqli->error)
            {
                $error = $mysqli->error;
            }

            if ($error == '') 
            {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestión.";
                $result["error"] = $error;
            } 
            else 
            {
                $result["http_code"] = 400;
                $result["status"] = "Error.";
                $result["error"] = $error;
            }

            echo json_encode($result);
            exit();
        }
        else
        {
            $result["http_code"] = 400;
            $result["status"] = "Error al registrar.";
            $result["error"] = "La hora permitida para realizar la atención del préstamo bóveda es hasta las " .date( "g:i a", strtotime($valor_solicitar_prestamo));

            echo json_encode($result);
            exit();
        }    
        
    }
    else
    {
        $result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }

    echo json_encode($result);
    exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_boveda_detalle_revertir_rechazado")
{
    $login_usuario_id = $login?$login['id']:null;
    $created_at = date("Y-m-d H:i:s");

    if((int)$login_usuario_id > 0)
    {
        $query_update = 
        "
            UPDATE tbl_caja_prestamo_boveda
            SET situacion_jefe_etapa_id = 1,
                situacion_tesoreria_etapa_id = 1,
                usuario_reversion = '".$login_usuario_id."',
                fecha_reversion = '".$created_at."',
                updated_at = '".$created_at."'
        ";
        
        $mysqli->query($query_update);
        
        if($mysqli->error)
        {
            $error = $mysqli->error;

            $result["http_code"] = 400;
            $result["titulo"] = "Error al revertir";
            $result["texto"] = $error;

            echo json_encode($result);
            exit();
        }

        $result["http_code"] = 200;
        $result["titulo"] = "Reversión exitoso.";
        $result["texto"] = "La reversión fue exitoso.";

        echo json_encode($result);
        exit();
    }
    else
    {
        $result["http_code"] = 400;
        $result["titulo"] = "Sesión perdida";
        $result["texto"] ="Por favor vuelva a iniciar sesión.";
    }

    echo json_encode($result);
    exit();
}
?>