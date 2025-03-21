<?php
include("db_connect.php");
include("sys_login.php");

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'archivos' LIMIT 1");
while($r = $result->fetch_assoc()) $menu_id = $r["id"];

if(isset($_POST["get_archivos"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])){
		$data = $_POST["get_archivos"];
		$data['offset'] = $data['limit']*$data['page'];
		$list_where="WHERE ad.active = 1 AND ac.active = 1 AND ac.id IN (12, 13)";
		
		if($data["category"] != "all") $list_where.=" AND ad.category_id =".$data["category"];
		if ($data["filter_digital"] != 'all' && $data["category"] == 10){
			$list_where .= " AND ad.file_subdirectory = '".$data["filter_digital"]."'";
		}
		if($data['filter'] != ""){
			$list_where .= " AND (
					ad.id LIKE '%{$data['filter']}%' OR
					ad.filename LIKE '%{$data['filter']}%' OR
					ad.extension LIKE '%{$data['filter']}%' OR
					ad.size LIKE '%{$data['filter']}%' OR
					ac.name LIKE '%{$data['filter']}%' OR
					ad.file_subdirectory LIKE '%{$data['filter']}%'
				)
			";
		}

		$mysqli->query("START TRANSACTION");
		$result=$mysqli->query("
			SELECT
				ad.id,
				ad.filename,
				ac.name as category_name,
				ad.file_subdirectory,
				ad.download_path,
				ad.extension,
				ad.size
			FROM tbl_archivos_drive ad
			INNER JOIN tbl_archivos_categoria ac ON ac.id = ad.category_id
			$list_where
			ORDER BY category_name DESC,ad.created_at DESC
			LIMIT {$data['limit']} OFFSET {$data['offset']}
		");
		$num_rows = $mysqli->query("
			SELECT
				ad.id
			FROM tbl_archivos_drive ad
			INNER JOIN tbl_archivos_categoria ac ON ac.id = ad.category_id
			$list_where
		")->num_rows;
		$mysqli->query("COMMIT");

		$archivos=[];
		while ($r=$result->fetch_assoc()) $archivos[]=$r;

		$body = "";
		$nombre_cat = count($archivos)>0?$archivos[0]["category_name"]:'';
		$i=0;
		foreach ($archivos as $archivo) {
			if ($archivo["size"] > 1048576) $size = number_format(($archivo["size"]/1048576),2, ".", ",")." MB";
			else $size = number_format(($archivo["size"]/1024),2, ".", ",")." KB";

			if($archivo["category_name"]!=$nombre_cat || $i==0){
				$nombre_cat=$archivo["category_name"];
				$body .='<tr>';
				$body .='<td colspan="5" style="padding-bottom:30px;padding-top:14px;font-weight:bold;color:blue">';
				$body .=$nombre_cat;
				$body .='</td>';
				$body .='</tr>';
				$clase="active";
				$i = 0;
			}
			else{ 
				$clase = $i%2==0?"active":"";
			}
			$i++;

			$body .= '<tr class="'.$clase.'">';
			$body .= '<td>'.$archivo["filename"].'</td>';
			if ($archivo["category_name"] == "Digital") {
				$body .= '<td>'.$archivo["category_name"].' / '.$archivo["file_subdirectory"].'</td>';
			} else {
				$body .= '<td>'.$archivo["category_name"].'</td>';
			}
			$body .= '<td>'.strtoupper($archivo["extension"]).'</td>';
			$body .= '<td>'.$size.'</td>';
			$body .= '<td>';
			if(array_key_exists($menu_id,$usuario_permisos) && in_array("delete", $usuario_permisos[$menu_id])){
				$body .= '<button id="btnArchivosDelete" data-id="'.$archivo["id"].'" class="btn btn-danger btn-sm pull-right"><i class="fa fa-trash"></i></button> ';
			}
			$body .= '<a target="_blank" href="'.$archivo["download_path"].'" class="btn btn-primary btn-sm pull-right"><i class="fa fa-download"></i></a>';
			if ($archivo["category_name"] == "Digital") {
				$body .= '<abbr title="Presione aquí para copiar enlace">';
				$body .= '<button data-id="'.$archivo["id"].'" class="btn btn-info btn-sm pull-right btnCopyLink" data-url="'.$archivo["download_path"].'"><i class="fa fa-files-o"></i></button> ';
				$body .= '</abbr>';
			}
			$body .= '</td>';
			$body .= '</tr>';
		}

		echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
	}
	else echo json_decode(['body' => 'No tienes permisos.']);
}
elseif(isset($_POST["post_archivos"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("import", $usuario_permisos[$menu_id])){
		$data = $_POST;
		if(isset($_FILES['fileArchivosModal'])) {
			$message = "<div style='overflow-y:auto; height:500px;'>";
			$status = true;
			//$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'pdf', 'zip', 'rar');
			$valid_extensions = array('png');
			$arr_files = [];

			for ($i=0; $i < count($_FILES['fileArchivosModal']["name"]); $i++) { 
				$file = $_FILES['fileArchivosModal']['name'][$i];
				$tmp = $_FILES['fileArchivosModal']['tmp_name'][$i];
				$size = $_FILES['fileArchivosModal']['size'][$i];
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

				//$filename = strtolower(preg_replace('/.[\w]+$/', '', $file)."_".date('YmdHis').".".$ext);
				//$filename = strtolower(preg_replace('/.[\w]+$/', '', $file)."_".date('YmdHis').".".$ext);
				$file_subdirectory = strtolower(preg_replace('/.[\w]+$/', '', $file)."_".date('YmdHis').".".$ext);

				if($size <= 10485760){
					if(in_array($ext, $valid_extensions)) {
						if(count($_FILES['fileArchivosModal']['name']) === 1){

							if($data["cbArchivosModalCategoria"]==12){

								$mysqli->query("START TRANSACTION");
								$result = $mysqli->query("
									SELECT id, filename, filepath, file_subdirectory, download_path
									FROM tbl_archivos_drive
									WHERE category_id = ".$data["cbArchivosModalCategoria"]."
									AND filename <> file_subdirectory AND Active = 1
								");
								$mysqli->query("COMMIT");
	
								$archivos = [];
								while ($r = $result->fetch_assoc()) {
									$archivos[] = $r;
								}
	
								if(count($archivos) != 0){
									foreach ($archivos as $archivo) {
										$nuevo_nombre = $archivo['file_subdirectory'];
										$nueva_ruta = $_SERVER['DOCUMENT_ROOT'].'/lottingo/public/images/'.$nuevo_nombre;
										$nueva_ruta_descarga = '/lottingo/public/images/'.$nuevo_nombre;
									
										rename($_SERVER['DOCUMENT_ROOT'].'/lottingo/public/images/'.$archivo['filename'], $nueva_ruta);
									
										$mysqli->query("
											UPDATE tbl_archivos_drive
											SET filename = '".$nuevo_nombre."',
											filepath = '".$nueva_ruta."',
											download_path = '".$nueva_ruta_descarga."'
											WHERE id = '".$archivo['id']."'
										");
									}
								}
	
								$filename = "torito_bingo_imagen".".".$ext;
								$filepath = $_SERVER['DOCUMENT_ROOT'].'/lottingo/public/images/'.$filename;
								$download_dir = '/lottingo/public/images/';
								
							}elseif($data["cbArchivosModalCategoria"]==13){
	
								$mysqli->query("START TRANSACTION");
								$result = $mysqli->query("
									SELECT id, filename, filepath, file_subdirectory, download_path
									FROM tbl_archivos_drive
									WHERE category_id = ".$data["cbArchivosModalCategoria"]."
									AND filename <> file_subdirectory AND Active = 1
								");
								$mysqli->query("COMMIT");
	
								$archivos = [];
								while ($r = $result->fetch_assoc()) {
									$archivos[] = $r;
								}
	
								if(count($archivos) != 0){
									foreach ($archivos as $archivo) {
										$nuevo_nombre = $archivo['file_subdirectory'];
										$nueva_ruta = $_SERVER['DOCUMENT_ROOT'].'/bingototal/public/images/'.$nuevo_nombre;
										$nueva_ruta_descarga = '/bingototal/public/images/'.$nuevo_nombre;
									
										rename($_SERVER['DOCUMENT_ROOT'].'/bingototal/public/images/'.$archivo['filename'], $nueva_ruta);
									
										$mysqli->query("
											UPDATE tbl_archivos_drive
											SET filename = '".$nuevo_nombre."',
											filepath = '".$nueva_ruta."',
											download_path = '".$nueva_ruta_descarga."'
											WHERE id = '".$archivo['id']."'
										");
									}
								}
	
								$filename = "torito_total_imagen".".".$ext;
								$filepath = $_SERVER['DOCUMENT_ROOT'].'/bingototal/public/images/'.$filename;
								$download_dir = '/bingototal/public/images/';
	
							}else{
								$filepath = $base_path . '/files_bucket/drive/' . $filename;
								   $download_dir = '/files_bucket/drive/'; 
							}
	
							
							move_uploaded_file($tmp,$filepath);
							$app_url = env('APP_URL');
							$src = $app_url.$download_dir.$filename;
							//$src = $app_url.'/files_bucket/drive/'.$filename;
							$arr_files[$i]["src"] = $src;
							$arr_files[$i]["name"] = $filename;
							$mysqli->query("
								INSERT INTO tbl_archivos_drive(
									usuario_id,
									category_id,
									filename,
									filepath,
									file_subdirectory,
									download_path,
									extension,
									size,
									created_at,
									updated_at
								)
								VALUES(
									".$login["id"]." ,
									".$data["cbArchivosModalCategoria"]." ,
									'".$filename."',
									'".$filepath."',
									'".$file_subdirectory."',
									'".$download_dir.$filename."',
									'".$ext."',
									".$size." ,
									'".date('Y-m-d H:i:s')."',
									'".date('Y-m-d H:i:s')."'
								)
							");
							$message .= "<div class='small text-left alert alert-success'><b>".$file."</b> Importado Exitosamente.</div>";

						}else{							
							$status = false;
							$message .= "<div class='small text-left alert alert-danger'><b>".$file."</b> Solo se permite subir un archivo.</div>";
						}
					}
					else {
						$status = false;
						$message .= "<div class='small text-left alert alert-danger'><b>".$file."</b> extensión inválida. Solo es permitido las extensiones: 'png' .</div>";
					}
				}
				else {
					$status = false;
					$message .= "<div class='small text-left alert alert-danger'><b>".$file."</b> supera la cantidad máxima permitida (10 MB).</div>";
				}
			}
			$message .= "</div>";
			
			echo json_encode([
				'status' => $status,
				'message' => $message,
				'arr_src' => $arr_files
			]);
		}
		else{
			echo json_encode([
				'status' => false,
				'message' => 'Por favor seleccionar un archivo.'
			]);	
		}
	}
	else{
		echo json_encode([
			'status' => false,
			'message' => 'No tienes permisos para subir archivos.',
		]);
	}
}
elseif(isset($_POST["delete_archivos"])){
	if(array_key_exists($menu_id,$usuario_permisos) && in_array("delete", $usuario_permisos[$menu_id])){
		$data = $_POST["delete_archivos"];
		$mysqli->query("
			UPDATE tbl_archivos_drive 
			SET active = 0,
			updated_at='".date('Y-m-d H:i:s')."'
			WHERE id = ".$data["id"]."
		");
		echo json_encode(['response' => true]);
	}
	else echo json_encode([
		'status' => false,
		'message' => 'No tienes permisos borrar archivos',
	]);
}
else{
	echo json_encode([
		'status' => false,
		'message' => 'Archivo supera la cantidad máxima permitida (10MB)'
	]);	
}
?>
