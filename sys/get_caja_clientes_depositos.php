<?php
$result=array();
include("db_connect.php");
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

function get_turno(){
	global $login;
	global $mysqli;
	$usuario_id=$login['id'];

	$command ="SELECT id FROM tbl_caja WHERE estado=0 AND usuario_id=".$usuario_id;
	$list_query=$mysqli->query($command);
	
	$list=array();
	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}
	if($mysqli->error){
		print_r($mysqli->error);
	}
	return $list;
}

if (isset($_POST["get_turno"])) {
	$usuario_id=$login['id'];


	$area_id=isset($login["area_id"]) ? $login["area_id"] : "";
	$cargo_id=isset($login["cargo_id"]) ? $login["cargo_id"] : "";
	$es_cajero=$area_id==21 && $cargo_id==5?true:false;

	if($es_cajero){
		$command ="SELECT id FROM tbl_caja WHERE estado=0 AND usuario_id=".$usuario_id;
		$list_query=$mysqli->query($command);
		
		$list=array();
		while ($li=$list_query->fetch_assoc()) {
			$list[]=$li;
		}
		if($mysqli->error){
			print_r($mysqli->error);
		}
		$tiene_turno=count($list)>0?true:false;
	}
	else{
		$tiene_turno=true;
	}
	$result["tiene_turno"]=$tiene_turno;
}

if (isset($_POST["get_cuenta"])) {
	$cuenta_tipo_id=$_POST['get_cuenta'];

	$command ="SELECT * FROM tbl_cuenta WHERE tipo_id=$cuenta_tipo_id";
	$list_query=$mysqli->query($command);
	
	$list=array();
	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}
	if($mysqli->error){
		print_r($mysqli->error);
	}
	$result["cuentas"]=$list;
}

if (isset($_POST["get_caja_cliente"])) {
	$valor=$_POST["get_caja_cliente"];
	$valor=$mysqli->real_escape_string($valor);
	$valor=trim($valor);
	$cant_digitos=strlen($valor);
	if($cant_digitos==9){
		$command ="SELECT * FROM tbl_caja_clientes WHERE telefono='".$valor."'";
		$list_query=$mysqli->query($command);
		
		$list=array();
		while ($li=$list_query->fetch_assoc()) {
			$list[]=$li;
		}
		if($mysqli->error){
			print_r($mysqli->error);
		}
		if(count($list)>0){
			$cliente=$list[0];
		}
		else{
			$cliente=null;
		}
		$command ="SELECT id,nombre FROM tbl_cuenta_tipo";
		$list_query=$mysqli->query($command);
		$list_tipo=array();
		while ($li=$list_query->fetch_assoc()) {
			$list_tipo[]=$li;
		}
		$result["cuenta_tipo"]=$list_tipo;
		$result["cliente"]=$cliente;
	}
	else{
		$result["cliente"]=false;
		$result["mensaje"]="Debe ingresar 9 dígitos";

	}
    $result["curr_login"]=$login;

}

if(isset($_POST["save_bet_id"])){
	$deposito_id = $_POST["deposito_id"];
	$bet_id = $_POST["bet_id"];

	if (!empty($bet_id)){
		$insert = "UPDATE tbl_caja_clientes_depositos SET bet_id = '$bet_id' WHERE id = $deposito_id";
		$mysqli->query($insert);
		if($mysqli->error){
			print_r($mysqli->error);
			$result["error"] = true;
			$result["mensaje"] = "Error al guardar el ID";
		} else {
			$result["mensaje"] = "Guardado correctamente";
		}
	} else {
		$result["error"] = true;
		$result["mensaje"] = "Ingrese un ID correcto";
	}

	$result["curr_login"] = $login;
}

if(isset($_POST["save_caja_cliente_deposito"])){
	$data=$_POST["save_caja_cliente_deposito"];
	$cliente_id=$_POST["id"];
	/*foreach ($data as $key => $value) {
		$data[$key]=$mysqli->real_escape_string($value);
	}*/
	$nombre=$mysqli->real_escape_string($_POST["nombre"]);
	$telefono=$mysqli->real_escape_string($_POST["telefono"]);
	$apellido_paterno=$mysqli->real_escape_string($_POST["apellido_paterno"]);
	$apellido_materno=$mysqli->real_escape_string($_POST["apellido_materno"]);
	//$cuenta_id=$mysqli->real_escape_string($_POST["cuenta_id"])||'null';
	//$monto=$mysqli->real_escape_string($_POST["monto"])||0;
	$telefono=trim($telefono);
	$cant_digitos=strlen($telefono);

	$tipo_doc = $mysqli->real_escape_string($_POST["tipo_doc"]);
	$num_doc = $mysqli->real_escape_string($_POST["num_doc"]);

	if($cant_digitos==9){
	
		if($cliente_id==0 || $cliente_id==""){
			$insert_command = "INSERT INTO tbl_caja_clientes (telefono,tipo_doc, num_doc, nombre,apellido_paterno,apellido_materno,created_at)
			VALUES('$telefono', '$tipo_doc', '$num_doc', '$nombre', '$apellido_paterno', '$apellido_materno', now())
			";
			$mysqli->query($insert_command);
			 $cliente_id=$mysqli->insert_id;
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
		}
		else{
			$command = "UPDATE tbl_caja_clientes SET 
			telefono='$telefono'
			,nombre='$nombre'
			,apellido_paterno='$apellido_paterno'
			,apellido_materno='$apellido_materno'
		    ,tipo_doc='$tipo_doc'
		    ,num_doc='$num_doc'
			,updated_at= now()
			WHERE id = $cliente_id
			";
			$mysqli->query($command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
		}

		$turno= get_turno();
		if(count($turno)>0){
			$turno=$turno[0];
			$turno_id=$turno["id"];
			$monto=$_POST["monto"]==""?0:$_POST["monto"];
			$cuenta_id=$_POST["cuenta_id"]==""?"null":$_POST["cuenta_id"];
			$insert_command = "INSERT INTO tbl_caja_clientes_depositos (turno_id,user_id,cliente_id,estado,created_at , cuenta_id,monto)
				VALUES(".$turno_id.",".$login["id"].",".$cliente_id.",0,now() , ".$cuenta_id.",'".$monto."')
				";
			$mysqli->query($insert_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
	        $insert_id = mysqli_insert_id($mysqli);

			$result["mensaje"]="Solicitud de Depósito $insert_id Registrada";


			//$archivo=$_POST["imagen_voucher"];
			$path = "/var/www/html/files_bucket/depositos/";
	        $file = [];
	        $imageLayer = [];
	        if (!is_dir($path)) mkdir($path, 0777, true);
	        $imageProcess = 0;

	        $filename = $_FILES['imagen_voucher']['tmp_name'];
	        $filenametem = $_FILES['imagen_voucher']['name'];
	        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
	        if($filename!=""){
	            $fileExt = pathinfo($_FILES['imagen_voucher']['name'], PATHINFO_EXTENSION);

	            $resizeFileName = $insert_id. "_" . date('YmdHis');
	            $nombre_archivo=$resizeFileName . "." . $fileExt;
	            if($fileExt=="pdf"){
	                move_uploaded_file($_FILES['imagen_voucher']['tmp_name'], $path. $nombre_archivo);
	            }
	            else{       
	                $sourceProperties = getimagesize($filename);
	                $size = $_FILES['imagen_voucher']['size'];
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
	                        $imageProcess = 0;
	                        break;
	                }
	                $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

	                $file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
	                $file[1] = imagegif($imageLayer[1], $path . "min_" . $nombre_archivo);
	                move_uploaded_file($file[0], $path . $nombre_archivo);
	                move_uploaded_file($file[1], $path . $nombre_archivo);
	                $imageProcess = 1;
	            }

	            $comando=" INSERT INTO tbl_caja_clientes_depositos_archivos
	                        (deposito_id,tipo,archivo,created_at,estado)
	                        VALUES(
	                            '" . $insert_id . "',
	                            1,
	                            '" . $nombre_archivo . "',
	                            '" . date('Y-m-d H:i:s') . "',
	                            1
	                            )";
	            $mysqli->query($comando);
	            $insert_id = mysqli_insert_id($mysqli);

	            $filepath = $path . $resizeFileName . "." . $fileExt;
	            $result[] = [
	                'id' => $insert_id,
	                'filename' => $nombre_archivo,
	                'filepath' => $filepath
	            ];
	        }
		}
		else{
			$result["mensaje"]="No hay turno activo";
		}
	}
	else{
		$result["error"]=true;

		$result["mensaje"]="Teléfono: debe Ingresar 9 dígitos";
	}
    $result["curr_login"]=$login;

}

if(isset($_POST["sec_caja_clientes_depositos_estado"])){
	$objeto=$_POST["sec_caja_clientes_depositos_estado"];
	$estado=$objeto["estado"];
	$id=$objeto["deposito_id"];
    $udpate_command = " UPDATE tbl_caja_clientes_depositos SET 
                         estado=$estado
                        ,update_user_at=now()
                        ,update_user_id=".$login["id"]." 
                        WHERE id= ".$id;
	$mysqli->query($udpate_command);
	$mensaje=$estado==2?"Rechazada":"Validada";
	$result["mensaje"]="Solicitud de Validación ".$id." ".$mensaje;
    $result["curr_login"]=$login;


}

if(isset($_POST["sec_depositos_list"])){
	$TABLA="tbl_caja_clientes_depositos";
	$ID_LOGIN=$login["id"];
	$data=$_POST["sec_depositos_list"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$column_index = $_POST['order'][0]['column']; // Column index
	$column_name = $_POST['columns'][$column_index]['data']; // Column name
	$column_sort_order = $_POST['order'][0]['dir']; // asc or desc
	$search_value = $_POST['search']['value']; // Search value
	$search_value = $mysqli->real_escape_string($search_value);

	$estado_sol=$_POST["estado_solicitud"];
	## Search 
	$search_query = " ";
	if($search_value != ''){
	   $search_query = " and ( (CONCAT(apt.nombre, ' ', IFNULL(apt.apellido_paterno, ''), ' ', IFNULL(apt.apellido_materno, '')))  like '%".$search_value."%' or 
	        val.usuario like '%".$search_value."%' or
	        cli.telefono like '%".$search_value."%' or
			(CASE 
				WHEN dep.estado=0 THEN 'Pendiente' 
				WHEN dep.estado=1 THEN 'Validado' 
	            WHEN dep.estado=2 THEN 'Rechazado'
	         END) like '%".$search_value."%' or
	        dep.created_at like '".$search_value."%' or
	        dep.update_user_at like '".$search_value."%'
	        ) ";
	        
	}

	$SELECT=" SELECT 
				dep.id
				,dep.created_at
				,usu.usuario AS usuario
				,CONCAT(cli.nombre, ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '')) AS cliente
				,cli.telefono
				,(CASE
					WHEN dep.estado=0 THEN 'Pendiente'
					WHEN dep.estado=1 THEN 'Validado'
		            WHEN dep.estado=2 THEN 'Rechazado'
		          END) AS estado
				,(SELECT count(*) AS fotos FROM tbl_caja_clientes_depositos_archivos WHERE tipo=1 AND deposito_id=dep.id) AS fotos
				,(SELECT count(*) AS fotos FROM tbl_caja_clientes_depositos_archivos WHERE tipo=2 AND deposito_id=dep.id) AS fotos2
				,(select count(*) as fotos FROM tbl_caja_clientes_depositos_archivos WHERE tipo=3 AND deposito_id=dep.id) AS archivos
				,val.usuario AS validador
				,(CONCAT(apt.nombre, ' ', IFNULL(apt.apellido_paterno, ''), ' ', IFNULL(apt.apellido_materno, ''))) AS validadornombre
				,dep.update_user_at
                ,ct.nombre as cuenta_tipo
                ,c.nombre as cuenta
                ,dep.monto as monto
 				,dep.bet_id as bet_id
			 FROM tbl_caja_clientes_depositos dep
			 LEFT JOIN tbl_usuarios usu ON usu.id = dep.user_id
			 LEFT JOIN tbl_caja_clientes cli ON cli.id=dep.cliente_id
			 LEFT JOIN tbl_usuarios val ON val.id = dep.update_user_id
			 LEFT JOIN tbl_personal_apt apt on apt.id= val.personal_id
             LEFT JOIN tbl_cuenta c on c.id = dep.cuenta_id
             LEFT JOIN tbl_cuenta_tipo ct on ct.id = c.tipo_id
			 JOIN tbl_caja turno on turno.id= dep.turno_id
 	";

	$area_id=isset($login["area_id"]) ? $login["area_id"] : "";
	$cargo_id=isset($login["cargo_id"]) ? $login["cargo_id"] : "";
	$es_cajero=$area_id==21 && $cargo_id==5?true:false;

// 	if(isset($_POST["cajero"])) {
 	if($es_cajero) {
	 	$where="turno.estado=0 and  dep.user_id =".$ID_LOGIN;
 	}else{
 		$where=" 1 ";
 	}

 	if($estado_sol!="Todo"){
		$where.=" and (CASE 
				WHEN dep.estado=0 THEN 'Pendiente' 
				WHEN dep.estado=1 THEN 'Validado' 
	            WHEN dep.estado=2 THEN 'Rechazado'
	         END) ='".$estado_sol."' ";
	    /*$curr_login=$login;
	    $estado=$estado_sol;*/

 	}
//echo ($where);
	$emp_query=   $SELECT."	WHERE $where ";
 	$sel = $mysqli->query("SELECT count(*) AS allcount FROM ($SELECT WHERE $where) as filas");
	$records = $sel->fetch_assoc();
	$total_records = $records['allcount'];

	$emp_query=   $SELECT."	WHERE $where ".$search_query;
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$emp_query.") AS subquery");
	$records = $sel->fetch_assoc();
	$total_recordwith_filter = $records['allcount'];

	$limit=" limit ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}
	$emp_query= $SELECT." WHERE $where ".$search_query." order by ".$column_name." ".$column_sort_order.$limit;	
	$registros = $mysqli->query($emp_query);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
	   $data[] = $row;
	}

	$response = array(
	  "draw" => $draw,
	  "iTotalRecords" => $total_records,
	  "iTotalDisplayRecords" => $total_recordwith_filter,
	  "aaData" => $data
	);
	/*if(isset($curr_login)){
		$response["curr_login"]=$curr_login;
		$response["estado"]=$estado;
	}*/

	echo json_encode($response);
	return;
}

echo json_encode($result);

?>


