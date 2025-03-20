<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("set_clientes.php");
include("sys_login.php");

function resizeImage($resourceType, $image_width, $image_height)
{
    $imagelayer = [];

    if ($image_width < 1920 && $image_height < 1080) {

        //mini
        $resizewidth_mini = 100;
        $resizeheight_mini = 100;

        $imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);

        //mini
        $imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
        imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
    } else {

        $ratio = $image_width / $image_height;
        $escalaW = 1920 / $image_width;
        $escalaH = 1080 / $image_height;

        if ($ratio > 1) {
            //es mas ancha $resizeheight
            $resizewidth = $image_width * $escalaW;
            $resizeheight = $image_height * $escalaW;

        } else {
            //es mas larga
            $resizeheight = $image_height * $escalaH;
            $resizewidth = $image_width * $escalaH;
        }

        //mini
        $resizewidth_mini = 100;
        $resizeheight_mini = 100;

        $imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);

        //mini
        $imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
        imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);

    }

    return $imagelayer;
}

function data_to_db($d){
	global $mysqli;
	$tmp=array();
	$nulls=array("null","",false);
	foreach ($d as $k => $v) {
		if($v===0){
			$tmp[$k]=$v;
		}elseif(in_array($v, $nulls)){
			$tmp[$k]="NULL";
		}else{
			if(is_float($v)){
				$tmp[$k]="'".$v."'";
			}elseif(is_int($v)){
				$tmp[$k]=$v;
			}else{
				$v=str_replace(",", ".", $v);
				$tmp[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		}
	}
	return $tmp;
}

$return["num_update"]=0;
$return["num_insert"]=0;
$return["num_nothing"]=0;
if(isset($_POST["opt"])){
	$opt=$_POST["opt"];
	// print_r($_POST);
	if($opt=="cobranzas_add_deuda"){
		extract($_POST);
		// print_r($data);
		if(!is_numeric($data["monto"])){
			$return["error"]="El monto debe ser número";
			print_r(json_encode($return));
			die();
		}
		$tmp = array();
			$tmp["fecha_ingreso"]=date("Y-m-d H:i:s");
			$tmp["periodo_year"]=$data["periodo_year"];
			$tmp["periodo_mes"]=$data["periodo_mes"];
			$tmp["periodo_rango"]=$data["periodo_rango"];
			$tmp["periodo_inicio"]=$data["periodo_year"]."-".$data["periodo_mes"]."-".strstr($data["periodo_rango"],"-",true);
			$tmp["periodo_fin"]=$data["periodo_year"]."-".$data["periodo_mes"]."-".substr(strstr($data["periodo_rango"],"-"),1);
			$tmp["periodo_rango_int"]=intval(str_replace("-", "", $data["periodo_rango"]));
			$tmp["canal_de_venta_id"]=0;
			$tmp["local_id"]=$data["local_id"];
			$tmp["saldo"]=0;
			$tmp["estado"]=1;
			$tmp["monto"]=$data["monto"];
			// $tmp["tipo"]=$data["tipo"];
			$tmp["tipo_id"]=$data["tipo_id"];
			$tmp["descripcion"]=$data["descripcion"];
			$tmp["at_unique_id"]=md5($tmp["fecha_ingreso"].$tmp["local_id"].$tmp["canal_de_venta_id"].$tmp["tipo_id"]);
			$tmp["periodo_liquidacion_id"]=$data["periodo_liquidacion_id"];
			$tmp["estado_liquidacion"]=1;
		// print_r(data_to_db($tmp));
		$data_to_db=data_to_db($tmp);
		$insert_command = "INSERT INTO tbl_deudas";
			$insert_command.="(";
			$insert_command.=implode(",", array_keys($data_to_db));
			$insert_command.=")";
			$insert_command.=" VALUES ";
			$insert_command.="(";
			$insert_command.=implode(",", $data_to_db);
			$insert_command.=")";
			
		$mysqli->query($insert_command);
		if($mysqli_error = $mysqli->error){
			print_r($mysqli_error);
			echo "\n";
			echo $insert_command;
			exit();
		}else{
			$affected_rows = $mysqli->affected_rows;
			if($affected_rows==2){
				$return["num_update"]++;
			}elseif($affected_rows==1){
				$return["num_insert"]++;
			}else{
				$return["num_nothing"]++;
			}
		}

		
		$sql_insert_eecc = "
			UPDATE tbl_estados_cuenta
			SET deuda = deuda + {$data["monto"]}, update_fecha_deuda = NOW()
			WHERE id_local = {$data["local_id"]}
		";
		$mysqli->query($sql_insert_eecc);
		
	}
	if($opt=="cobranzas_add_pago"){
		$data = $_POST["data"];
		
		if($data=="false"){
			$return["error"]="nodata";
		}else{
			$data = json_decode($_POST["data"],true);
			// echo "\n\n";
			// print_r($_POST);
			// exit();
			if($data["pago_tipo_id"]=="" || $data["pago_tipo_id"]=="Seleccione"){
				$return["error"]="Seleccione Tipo de Pago";
				print_r(json_encode($return));
				die();
			}
			if( trim($data["nro_operacion"]) == "" ){
				$return["error"]="Ingrese Nro Operación";
				print_r(json_encode($return));
				die();
			}
			if( trim($data["nro_operacion"]) != "" ){
				$command = "SELECT id FROM tbl_pagos_detalle WHERE estado = 1 AND nro_operacion = '" . trim($data["nro_operacion"]) . "' ";

				$result_query = $mysqli->query($command)->fetch_assoc();
				if($result_query)
				{
					$return["error"] = "Nro de Operacion ".trim($data["nro_operacion"] . "<b> Ya registrado.</b>");
					print_r(json_encode($return));
					die();	
				}
			}
			if( trim($data["fecha_voucher"]) == "" ){
				$return["error"] = "Ingrese Fecha Voucher";
				die(json_encode($return));
			}
			if(!is_numeric($data["abono"])){
				$return["error"]="Abono debe ser número";
				print_r(json_encode($return));
				die();
			}

			$pago_detalle_id=null;
			$nombre_archivo=null;
			//if($data["pago_tipo_id"]==1){///trans bancari
			$archivo=$_FILES['voucher'];

			$path = "/var/www/html/files_bucket/pagos_voucher/";
			$file = [];
			$imageLayer = [];
			if (!is_dir($path)) mkdir($path, 0777, true);

			$filename = $archivo['tmp_name'];
			$filenametem = $archivo['name'];

			$size = $archivo['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

			if($filename==""){
				$return["error"]="Debe Ingresar un archivo de imagen";
				print_r(json_encode($return));
				die();
			}
			if(!in_array($ext, $valid_extensions)) {
				$return["error"]="Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
				print_r(json_encode($return));
				die();
			}
			if($size > 10485760){//10 mb
				$return["error"]="Archivo supera la cantidad máxima permitida (10 MB)";
				print_r(json_encode($return));
				die();
			}
			if($filename!=""){
			    $fileExt = pathinfo($archivo['name'], PATHINFO_EXTENSION);

			    $resizeFileName =   date('YmdHis');
			    $nombre_archivo=$resizeFileName . "." . $fileExt;
			          
		        $sourceProperties = getimagesize($filename);
		        $uploadImageType = $sourceProperties[2];
		        $sourceImageWith = $sourceProperties[0];
		        $sourceImageHeight = $sourceProperties[1];

		        switch ($uploadImageType) {
		            case IMAGETYPE_JPEG:
		                $resourceType = imagecreatefromjpeg($filename);
		                break;
		            case IMAGETYPE_PNG:
		                $resourceType = imagecreatefrompng($filename);
		                break;
		            case IMAGETYPE_GIF:
		                $resourceType = imagecreatefromgif($filename);
		                break;
		            default:
		                break;
		        }
		        $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		        $file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
		        move_uploaded_file($file[0], $path . $nombre_archivo);
			}
			$insert_command = "INSERT INTO tbl_pagos_detalle";
			if( $data["pago_tipo_id"] == 1 ){
				$insert_command.="(nro_operacion , descripcion,monto,voucher,banco_id , created_at , fecha_voucher, estado_liquidacion)";
				$insert_command.=" VALUES ";
				$insert_command.="('".$data["nro_operacion"]."','".$data["descripcion"]."',".$data["abono"].",'".$nombre_archivo."',".$data["banco_id"].",'".date("Y-m-d H:i:s"). "', '" . $data["fecha_voucher"] . "', 1 )";
			}
			else{
				$insert_command.="(nro_operacion , descripcion,monto,voucher , created_at , fecha_voucher, estado_liquidacion)";
				$insert_command.=" VALUES ";
				$insert_command.="('".$data["nro_operacion"]."','".$data["descripcion"]."',".$data["abono"].",'".$nombre_archivo."','".date("Y-m-d H:i:s"). "' , '" . $data["fecha_voucher"] . "', 1 )";
			}
			$mysqli->query($insert_command);
			$pago_detalle_id=$mysqli->insert_id;

			if($mysqli_error = $mysqli->error){
				print_r($mysqli_error);
				echo "\n";
				echo $insert_command;
				exit();
			}
			$data["total_abono"]=0;
			$pagos_arr = array();
			if(array_key_exists("pagos", $data)){
				foreach ($data["pagos"] as $key => $p) {
					$pago = array();
						$pago["fecha_ingreso"]=date("Y-m-d H:i:s");
						$pago["periodo_year"]=$data["periodo_year"];
						$pago["periodo_mes"]=$data["periodo_mes"];
						$pago["periodo_rango"]=$data["periodo_rango"];
						$pago["periodo_inicio"]=$data["periodo_year"]."-".$data["periodo_mes"]."-".strstr($data["periodo_rango"],"-",true);
						$pago["periodo_fin"]=$data["periodo_year"]."-".$data["periodo_mes"]."-".substr(strstr($data["periodo_rango"],"-"),1);
						$pago["periodo_rango_int"]=intval(str_replace("-", "", $data["periodo_rango"]));

						$pago["local_id"]=$data["local_id"];
						$pago["estado"]=1;
						$pago["abono"]=$p["deuda_abonar"];

						$pago_tipo = $data["pago_tipo_id"];
						if($p["deuda_tipo_id"] == null){
							$pago_tipo = 5; //pago tipo saldo a favor;
						}
						$pago["deuda_tipo_id"]=$p["deuda_tipo_id"];
						$pago["pago_tipo_id"]=$pago_tipo;
						if(array_key_exists("data", $data)){
							$pago["descripcion"]=$data["descripcion"];
						}
						$pago["at_unique_id"]=md5($pago["fecha_ingreso"].$pago["local_id"].$pago["deuda_tipo_id"].$pago["pago_tipo_id"]);
						$pago["periodo_liquidacion_id"]=$data["periodo_liquidacion_id"];
						if($pago_detalle_id!=null){
							$pago["pago_detalle_id"]=$pago_detalle_id;
						}
					$pagos_arr[]=$pago;
					$data["total_abono"]+=$pago["abono"];
				}			
			}

			//echo "<pre>";print_r($pagos_arr);echo "</pre>";
			foreach ($pagos_arr as $p_key => $p_val) {
				$data_to_db=data_to_db($p_val);

				$insert_command = "INSERT INTO tbl_pagos";
					$insert_command.="(";
					$insert_command.=implode(",", array_keys($data_to_db));
					$insert_command.=")";
					$insert_command.=" VALUES ";
					$insert_command.="(";
					$insert_command.=implode(",", $data_to_db);
					$insert_command.=")";			
				$mysqli->query($insert_command);
				if($mysqli_error = $mysqli->error){
					print_r($mysqli_error);
					echo "\n";
					echo $insert_command;
					exit();
				}else{
					$affected_rows = $mysqli->affected_rows;
					if($affected_rows==2){
						$return["num_update"]++;
					}elseif($affected_rows==1){
						$return["num_insert"]++;
					}else{
						$return["num_nothing"]++;
					}
				}
			}
			
			$sql_insert_eecc = "
				UPDATE tbl_estados_cuenta
				SET pago = pago + {$data["abono"]}, update_fecha_pago = NOW()
				WHERE id_local = {$data["local_id"]}
			";
			$mysqli->query($sql_insert_eecc);
			$return["sql_insert_eecc"] = $sql_insert_eecc;
			
		}
	}
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>