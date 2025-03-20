<?php
require_once("db_connect.php");
require_once("sys_login.php");
if ( /*$sec_id !='login' && */ in_array(strtoupper($_SERVER['REQUEST_URI']), ['/sys/get_mepa_movilidad.php']) && in_array(strtoupper($_SERVER['REQUEST_METHOD']), ['POST']) && isset($_POST["accion"])) {
	$input = json_decode(json_encode($_POST));
	$mepaDocumentoCorrelativo = new MepaDocumentoCorrelativo;
	try {
		switch ($_POST["accion"]) {
			case 'sec_mepa_obtener_correlativo':
				$mepa_obtener_correlativo = $mepaDocumentoCorrelativo->fnObtenerCorrelativo($input);
				if ($mepa_obtener_correlativo) {
					http_response_code(200);
					$result["mensaje"] = "Registro listado con éxito";
					$result["error"]	= false;
					$result["data"]	= $mepa_obtener_correlativo;
				} else {
					http_response_code(422);
					$result["mensaje"] = "No existe el registro";
					$result["error"]	= true;
				}
				break;
			case 'sec_mepa_obtener_correlativo_por_id_usuario':
				$mepa_obtener_correlativo = $mepaDocumentoCorrelativo->fnObtenerCorrelativoPorIdUsuario($input);
				if ($mepa_obtener_correlativo) {
					http_response_code(200);
					$result["mensaje"] = "Registro listado con éxito";
					$result["error"]	= false;
					$result["data"]	= $mepa_obtener_correlativo;
				} else {
					http_response_code(422);
					$result["mensaje"] = "No existe el registro";
					$result["error"]	= true;
				}
				break;
			case 'sec_mepa_generar_correlativo_por_id_usuario':
				$mepa_obtener_correlativo = $mepaDocumentoCorrelativo->fnGenerarCorrelativo($input);
				if ($mepa_obtener_correlativo) {
					http_response_code(200);
					$result["mensaje"] = "Registro listado con éxito";
					$result["error"]	= false;
					$result["data"]	= $mepa_obtener_correlativo;
				} else {
					http_response_code(422);
					$result["mensaje"] = "No existe el registro";
					$result["error"]	= true;
				}
				break;
			default:
				# code...
				break;
		}
	} catch (\Throwable $e) {
		http_response_code(200);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}

	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
class MepaDocumentoCorrelativo
{
	public function fnObtenerCorrelativo($input)
	{
		$id = (int)$input->id;
		$dtReturn = array();
		if (!$data = $this->fncBdObtenerCorrelativo($id)) {
			return false;
		}
		$dtReturn = $data;
		return $dtReturn;
	}
	public function fnObtenerCorrelativoPorIdUsuario($input)
	{
		$mepa_documento_correlativo = new stdClass;
		$mepa_documento_correlativo->id_usuario = (int)$input->id_usuario;
		$mepa_documento_correlativo->tipo_solicitud = (int)$input->tipo_solicitud;
		$dtReturn = array();
		if (!$data = $this->fncBdObtenerCorrelativoPorIdUsuario($mepa_documento_correlativo)) {
			return false;
		}
		$dtReturn = $data;
		return $dtReturn;
	}
	public function fnGuardarCorrelativo($input)
	{
		$mepa_documento_correlativo = new stdClass;
		$mepa_documento_correlativo->id_usuario = (int)$input->id_usuario;
		$mepa_documento_correlativo->tipo_solicitud = (int)$input->tipo_solicitud;
		$dtReturn = array();
		if (!$data = $this->fncBdGuardarCorrelativo($mepa_documento_correlativo)) {
			return false;
		}
		$dtReturn = $this->fnObtenerCorrelativo($data);
		return $dtReturn;
	}
	public function fnGenerarCorrelativo($input)
	{
		$mepa_documento_correlativo = new stdClass;
		$mepa_documento_correlativo->id_usuario = (int)$input->id_usuario;
		$mepa_documento_correlativo->tipo_solicitud = (int)$input->tipo_solicitud;

		$dtReturn = array();
		if (!$data = $this->fnObtenerCorrelativoPorIdUsuario($mepa_documento_correlativo)) {
			$data = $this->fnGuardarCorrelativo($mepa_documento_correlativo);
		}else{
			$this->fncActualizarCorrelativo($data);
		}
		
		$dtReturn = $this->fnObtenerCorrelativo($data);
		return $dtReturn;
	}
	public function fncActualizarCorrelativo($input)
	{
		$mepa_documento_correlativo = new stdClass;
		$mepa_documento_correlativo->id = (int)$input->id;
		$mepa_documento_correlativo->num_correlativo = (int)$input->num_correlativo+1;
		$dtReturn = array();
		if (!$data = $this->fncBdActualizarCorrelativo($mepa_documento_correlativo)) {
			return false;
		}
		$dtReturn = $this->fnObtenerCorrelativo($mepa_documento_correlativo);
		return $dtReturn;
	}


	/*---------------------------------- FUNCIONES DE BD ---------------------------------*/


	private function fncBdObtenerCorrelativo($id)
	{
		global $mysqli;
		$querySelect = "
		SELECT
			mdc.id,
			mdc.num_correlativo,
			mdc.id_usuario,
			mdc.tipo_solicitud,
			mdc.created_at,
			mdc.updated_at
		FROM
			mepa_documento_correlativo AS mdc
		WHERE mdc.id = {$id}
		";
		$resultQuery = $mysqli->query($querySelect);
		if ($mysqli->error) {
			return ('Error: ' . $mysqli->error);
		}
		while (!$li = $resultQuery->fetch_assoc()) {
			return false;
		}
		$mepa_documento_correlativo = new stdClass;
		$mepa_documento_correlativo->id = $li["id"];
		$mepa_documento_correlativo->num_correlativo = $li["num_correlativo"];
		$mepa_documento_correlativo->id_usuario = $li["id_usuario"];
		$mepa_documento_correlativo->tipo_solicitud = $li["tipo_solicitud"];
		$mepa_documento_correlativo->created_at = $li["created_at"];
		$mepa_documento_correlativo->updated_at = $li["updated_at"];

		return $mepa_documento_correlativo;
	}
	private function fncBdObtenerCorrelativoPorIdUsuario($mepa_documento_correlativo)
	{
		global $mysqli;
		$querySelect = "
		SELECT
			mdc.id,
			mdc.num_correlativo,
			mdc.id_usuario,
			mdc.tipo_solicitud,
			mdc.created_at,
			mdc.updated_at
		FROM
			mepa_documento_correlativo AS mdc
		WHERE mdc.id_usuario = {$mepa_documento_correlativo->id_usuario}
		and mdc.tipo_solicitud = '{$mepa_documento_correlativo->tipo_solicitud}'
		";
		$resultQuery = $mysqli->query($querySelect);
		if ($mysqli->error) {
			return ('Error: ' . $mysqli->error);
		}
		while (!$li = $resultQuery->fetch_assoc()) {
			return false;
		}
		$mepa_documento_correlativo = new stdClass;
		$mepa_documento_correlativo->id = $li["id"];
		$mepa_documento_correlativo->num_correlativo = $li["num_correlativo"];
		$mepa_documento_correlativo->id_usuario = $li["id_usuario"];
		$mepa_documento_correlativo->tipo_solicitud = $li["tipo_solicitud"];
		$mepa_documento_correlativo->created_at = $li["created_at"];
		$mepa_documento_correlativo->updated_at = $li["updated_at"];

		return $mepa_documento_correlativo;
	}
	private function fncBdGuardarCorrelativo(stdClass $mepa_documento_correlativo)
	{
		global $mysqli;
		$querySelect = "
		INSERT INTO mepa_documento_correlativo
		(
			num_correlativo,
			id_usuario,
			tipo_solicitud,
			created_at
		)
		VALUES
		(
			1,
			{$mepa_documento_correlativo->id_usuario},
			'{$mepa_documento_correlativo->tipo_solicitud}',
			now()
		)
		";
		$mysqli->query($querySelect);
		if ($mysqli->insert_id) {
			$mepa_documento_correlativo->id = $mysqli->insert_id;
		return $mepa_documento_correlativo;
		}else{
			return false;
		}
		
	}
	private function fncBdActualizarCorrelativo(stdClass $mepa_documento_correlativo)
	{
		global $mysqli;
		$querySelect = "
		UPDATE mepa_documento_correlativo
		SET			
			num_correlativo = {$mepa_documento_correlativo->num_correlativo},
			updated_at = now()				
		WHERE id = {$mepa_documento_correlativo->id}
		";
		$dtReturn = $mysqli->query($querySelect);
		if ($dtReturn) {
			return $mepa_documento_correlativo;
		}else {
			return false;
		}
		
	}
}

// if (isset($_POST["accion"])) {
// 	$getOption = $_POST["accion"];
// 	$input = json_decode(json_encode($_POST));
// 	$mepaDocumentoCorrelativo = new MepaDocumentoCorrelativo;
// 	switch ($getOption) {
// 		case 'value':
// 			$arrayReturn = $mepaDocumentoCorrelativo->fnBuscarCorrelativo($input);
// 			break;

// 		default:
// 			# code...
// 			break;
// 	}
// 	if (!$arrayReturn) {
// 		$error .= 'Error: ';
// 	}
// 	if ($error == '') {
// 		$result["http_code"] = 200;
// 		$result["status"] = "Dato obtenido.";
// 		$result["error"] = false;
// 		$result["data"] = $arrayReturn;
// 	} else {
// 		$result["http_code"] = 400;
// 		$result["status"] = "Ocurrio un error";
// 		$result["error"] = true;
// 	}
// }

