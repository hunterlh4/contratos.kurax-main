<?php
class Correos {
  private $tipo;
  private $cc;
  private $cc_dev;
  private $bcc;

	public function __construct($tipo,$email_test)
	{
		$this->tipo = $tipo;
		$this->cc =  !Empty($email_test) ? [$email_test] : [];
		$this->cc_dev = [];
		$this->bcc =  [];
	}
///////////////////////////////////////////////////////////////////////////////
	public function obtener_correos_por_cargo($cargo_id) {
		$correos = [];
	
		include("db_connect.php");
	
		$query = "
			SELECT correo 
			FROM tbl_personal_apt 
			WHERE cargo_id = ?
			AND estado = 1"; 
	
		$stmt = $mysqli->prepare($query);
		if (!$stmt) {
			throw new Exception("Error al preparar la consulta: " . $mysqli->error);
		}
	
		$stmt->bind_param("i", $cargo_id);
	
		if (!$stmt->execute()) {
			throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
		}
	
		$result = $stmt->get_result();
		while ($row = $result->fetch_assoc()) {
			$email = filter_var(trim($row['correo']), FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				array_push($correos, $email); 
			}
		}
	
		$stmt->close();
	
		return $correos; 
	}

	public function obtener_correo_abogado_por_contrato($contrato_id) {
		include("db_connect.php"); 
	
		$query = "
			SELECT p.correo 
			FROM tbl_personal_apt p
			INNER JOIN tbl_usuarios u ON p.id = u.personal_id
			INNER JOIN cont_contrato c ON u.id = c.abogado_id
			WHERE c.contrato_id = ?
		";
	
		$stmt = $mysqli->prepare($query);
		if (!$stmt) {
			throw new Exception("Error al preparar la consulta: " . $mysqli->error);
		}
	
		$stmt->bind_param("i", $contrato_id);
		if (!$stmt->execute()) {
			throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
		}
	
		$result = $stmt->get_result();
		if ($row = $result->fetch_assoc()) {
			$email = filter_var(trim($row['correo']), FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$stmt->close();
				return $email; // Retorna el correo del abogado
			}
		}
	
		$stmt->close();
		throw new Exception("No se encontró un correo válido para el abogado asignado al contrato.");
	}

	////////////////////////////////////////////////////////////////////////////////
	
	///sys/set_contrato_detalle_solicitud
	public function send_email_confirmacion_giro_local($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_confirmacion_giro_local');
			/*
			$this->cc =  [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correos del área Municipal
				"roberto.cunya@testtest.apuestatotal.com",
				"brenda.puicon@testtest.apuestatotal.com",
				"arturo.huasasquiche@testtest.apuestatotal.com",
				"jackeline.velasquez@testtest.apuestatotal.com",
				// Correo de Jefe de Arrendamiento
				"walter.cortes@testtest.apuestatotal.com",
				"ricardo.bendezu@testtest.apuestatotal.com"
				// Correo del usuario que registro la solicitud
				// "$email_user_created"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	//Solicitud de contratos firmadas
	public function send_email_solicitud_contrato_arrendamiento_firmada($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_arrendamiento_firmada');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				// Correos del área Municipal
				"roberto.cunya@testtest.apuestatotal.com",
				"brenda.puicon@testtest.apuestatotal.com",
				"arturo.huasasquiche@testtest.apuestatotal.com",
				"jackeline.velasquez@testtest.apuestatotal.com",
				// Correo de Jefe de Arrendamiento
				"walter.cortes@testtest.apuestatotal.com",
				// Correos del Área Contable
				"richard.tomasto@testtest.apuestatotal.com",
				"wendy.aguilar@testtest.apuestatotal.com",
				"prishlina.ramirez@testtest.apuestatotal.com",
				// Correos del Área de Tesorería
				"jefferson.vicharra@testtest.apuestatotal.com",
				// Correos del Área Comercial
				"jonathan.gutierrez@testtest.apuestatotal.com",
				"ernesto.osma@testtest.apuestatotal.com",
				"jaime.neyra@testtest.apuestatotal.com",
				"ricardo.bendezu@testtest.apuestatotal.com"
				// Correo del usuario que registro la solicitud
				// "$email_user_created"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_contrato_proveedor_firmada($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_proveedor_firmada');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}else { // Desarrollo
			$this->cc_dev = $this->obtener_correos('send_email_solicitud_contrato_proveedor_firmada');
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc_dev, $email);
					}
				}
			}
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'cc_dev' => array_unique($this->cc_dev),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_rechazada($emails)
	{
		if ($this->tipo == 'produccion') {
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}else { // Desarrollo
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc_dev, $email);
					}
				}
			}
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'cc_dev' => array_unique($this->cc_dev),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_contrato_interno_firmada($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_interno_firmada');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_contrato_agente_firmada($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_agente_firmada');
			/*
			$this->cc = [
				// area legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				//area comercial
				"jonathan.gutierrez@testtest.apuestatotal.com",
				"maria.meza@testtest.apuestatotal.com",
				// area de contabilidad
				"richard.tomasto@testtest.apuestatotal.com",
				//area de tesoreria
				"jefferson.vicharra@testtest.apuestatotal.com",
				// area Municipal			 
				"arturo.huasasquiche@testtest.apuestatotal.com",
				//supervisores
				"lucia.navarro@testtest.apuestatotal.net" // falta
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}
	
	public function send_email_solicitud_acuerdo_confidencialidad_firmada($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_acuerdo_confidencialidad_firmada');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}


	//observacion de contratos
	public function send_email_observacion_contrato_agente($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_observacion_contrato_agente');
			/*
			$this->cc = [
				// area legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				//area comercial
				"jonathan.gutierrez@testtest.apuestatotal.com",
				"maria.meza@testtest.apuestatotal.com",  /// en gestion esta con .net
				// area de contabilidad
				"richard.tomasto@testtest.apuestatotal.com",
				//area de tesoreria
				"jefferson.vicharra@testtest.apuestatotal.com",
				// area Municipal			 
				"arturo.huasasquiche@testtest.apuestatotal.com",
				//supervisores
				"lucia.navarro@testtest.apuestatotal.net", // usuario inactivo
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_observacion_contrato_arrendamiento($emails)
	{
		if ($this->tipo == 'produccion') {
			
			$this->cc = $this->obtener_correos('send_email_observacion_contrato_arrendamiento');
			/*
			$this->cc = [
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// "$correo_del_jefe_comercial"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_observacion_contrato_proveedor($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_observacion_contrato_proveedor');
			/*
			$this->cc = [
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_observacion_contrato_interno($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_observacion_contrato_interno');
			/*
			$this->cc = [
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_observacion_acuerdo_confidencialidad($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_observacion_acuerdo_confidencialidad');
			/*
			$this->cc = [
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}


	public function send_email_formato_de_pago($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_formato_de_pago');
			/*
			$this->cc = [
				// Correos del Área Contable
				"richard.tomasto@testtest.apuestatotal.com",
				"prishlina.ramirez@testtest.apuestatotal.com",
				"wendy.aguilar@testtest.apuestatotal.com",
				"veronica.huanachin@testtest.apuestatotal.com",
				// Correos del Área de Tesorería
				"jefferson.vicharra@testtest.apuestatotal.com",
				"leandro.diaz@testtest.apuestatotal.com",
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

  //adendas firmadas
	public function send_email_adenda_contrato_arrendamiento_firmada($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_adenda_contrato_arrendamiento_firmada');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"ricardo.bendezu@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_adenda_contrato_proveedor_firmada($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_adenda_contrato_proveedor_firmada');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
			];
			*/
			// Correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}

		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);

		return $resultado;
	}

	public function send_email_adenda_contrato_interno_firmada($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_adenda_contrato_interno_firmada');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
			];
			*/
			// Correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}

		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);

		return $resultado;
	}

	public function send_email_adenda_contrato_agente_firmada($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_adenda_contrato_agente_firmada');
			/*
			$this->cc = [
				// area legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				//area comercial
				
				"jonathan.gutierrez@testtest.apuestatotal.com",
				"maria.meza@testtest.apuestatotal.com",  /// inactivo
				// area de contabilidad
				"richard.tomasto@testtest.apuestatotal.com",
				//area de tesoreria
				"jefferson.vicharra@testtest.apuestatotal.com",
				// area Municipal			 
				"arturo.huasasquiche@testtest.apuestatotal.com",
				//supervisores
				"lucia.navarro@testtest.apuestatotal.net" // inactivo
			];
			*/
			// Correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}

		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);

		return $resultado;
	}
	
	public function send_email_adenda_acuerdo_confidencialidad_firmada($emails)
	{
	  if ($this->tipo == 'produccion') {

		$this->cc = $this->obtener_correos('send_email_adenda_acuerdo_confidencialidad_firmada');
		/*
		$this->cc = [
			// Correos del área Legal
			"mayra.duffoo@testtest.apuestatotal.com",
			"sandra.murrugarra@testtest.apuestatotal.com",
			"carolina.cano@testtest.apuestatotal.com",
			"ingrid.escobar@testtest.apuestatotal.com",
			"camila.silva@testtest.apuestatotal.com",
			
		];
		*/
		// Correos adionales
		for ($i=0; $i < count($emails) ; $i++) { 
			if(!Empty($emails[$i])){
				$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
			}
		}

		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    ,
			  
		  ];
	  }

	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );

	  return $resultado;
	}
 
	
	//solicitud de contratos
	public function send_email_solicitud_contrato_arrendamiento($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_arrendamiento');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correos del área Municipal
				"roberto.cunya@testtest.apuestatotal.com",
				"brenda.puicon@testtest.apuestatotal.com",
				"arturo.huasasquiche@testtest.apuestatotal.com",
				"jackeline.velasquez@testtest.apuestatotal.com",
				// Correo del usuario que registro la solicitud
				// "$email_user_created"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_contrato_agente($emails)
	{

		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_agente');
			/*
			$this->cc = [
				// area legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				//area comercial
				
				"jonathan.gutierrez@testtest.apuestatotal.com",
				"maria.meza@testtest.apuestatotal.com",
				// area de contabilidad
				"richard.tomasto@testtest.apuestatotal.com",
				//area de tesoreria
				"jefferson.vicharra@testtest.apuestatotal.com",
				// area Municipal			 
				"arturo.huasasquiche@testtest.apuestatotal.com",
				//supervisores
				"lucia.navarro@testtest.apuestatotal.net" // inactivo
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				"jeremi.nunez@testtest.apuestatotal.com",
				"erika.polo@testtest.apuestatotal.com"
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		
		
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_contrato_proveedor($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_proveedor');
	
			// correos adionales        
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}else { // Desarroollo
			$this->cc_dev = $this->obtener_correos('send_email_solicitud_contrato_proveedor');
			// correos adionales        
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc_dev, $email);
					}
				}
			}
		}

		$resultado = array(
			'cc' => array_unique($this->cc),
			'cc_dev' => array_unique($this->cc_dev),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_contrato_interno($emails)
	{
		if ($this->tipo == 'produccion') {
			
			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_interno');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales        
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_acuerdo_confidencialidad($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_acuerdo_confidencialidad');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}
//Confirmacion de contratos
	public function send_email_confirmacion_solicitud_contrato_arrendamiento($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];

			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				} else {
					$this->cc = $this->obtener_correos('send_email_confirmacion_solicitud_contrato_proveedor');
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}else { // Desarroollo
			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc_dev, $email);
					}
				} else {
					$this->cc_dev = $this->obtener_correos('send_email_confirmacion_solicitud_contrato_proveedor');
				}
			}
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'cc_dev' => array_unique($this->cc_dev),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function enviar_correo_contrato_arrendamiento_cargo_34($emails_adicionales = []) {
		$correos_cargo_34 = $this->obtener_correos_por_cargo( 34);
	
		$correos_a_enviar = array_merge($correos_cargo_34, $emails_adicionales);
	
		$this->cc = array_unique($correos_a_enviar); 
		$this->bcc = [
			// "yonathan.mamani@testtest.kurax.dev",
			// "rossmery.garrido@testtest.kurax.dev",
			// "ronald.visitacion@testtest.apuestatotal.com",
			// "eduardo.chacaliaza@testtest.apuestatotal.com",
		];
	
		return [
			'cc' => $this->cc,
			'bcc' => $this->bcc,
		];
	}

	public function enviar_correo_notificacion_abogado($contrato_id, $emails_adicionales = []) {
		try {
			$correo_abogado = $this->obtener_correo_abogado_por_contrato($contrato_id);
	
			$emails_adicionales[] = $correo_abogado;
	
			$this->cc = array_unique($emails_adicionales); 
			$this->bcc = [
			];
	
			return [
				'cc' => $this->cc,
				'bcc' => $this->bcc,
			];
		} catch (Exception $e) {
			throw new Exception("Error al obtener los correos: " . $e->getMessage());
		}
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	//Confirmacion de contratos
	public function send_email_confirmacion_solicitud_contrato_proveedor($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];

			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				} else {
					$this->cc = $this->obtener_correos('send_email_confirmacion_solicitud_contrato_proveedor');
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}else { // Desarroollo
			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc_dev, $email);
					}
				} else {
					$this->cc_dev = $this->obtener_correos('send_email_confirmacion_solicitud_contrato_proveedor');
				}
			}
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'cc_dev' => array_unique($this->cc_dev),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_confirmacion_solicitud_contrato_interno($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_confirmacion_solicitud_contrato_interno');
			/*
			$this->cc = [
				// Correos de Gerencia quein verificara la solicitud		
				"lourdes.britto@testtest.apuestatotal.com"
			];
			*/
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_confirmacion_acuerdo_confidencialidad($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];

			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				} else {
					$this->cc = $this->obtener_correos('send_email_confirmacion_acuerdo_confidencialidad');
					//array_push($this->cc, "lourdes.britto@testtest.apuestatotal.com");
				}
			}
			
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}


	
	//detalle de contrato
	public function send_email_solicitud_contrato_arrendamiento_detallado($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_arrendamiento_detallado');
			/*
			$this->cc = [
				// Correo de Jefe de Arrendamiento
				"walter.cortes@testtest.apuestatotal.com",
				"ricardo.bendezu@testtest.apuestatotal.com"
			];
			*/
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				"jeremi.nunez@testtest.apuestatotal.com",
				"erika.polo@testtest.apuestatotal.com"
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_contrato_agente_detallado($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_contrato_agente_detallado');
			/*
			$this->cc = [
				// area legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				//area comercial
				
				"jonathan.gutierrez@testtest.apuestatotal.com",
				"maria.meza@testtest.apuestatotal.com",
				// area de contabilidad
				"richard.tomasto@testtest.apuestatotal.com",
				//area de tesoreria
				"jefferson.vicharra@testtest.apuestatotal.com",
				// area Municipal			 
				"arturo.huasasquiche@testtest.apuestatotal.com",
				//supervisores
				"lucia.navarro@testtest.apuestatotal.net", // inactivo
			
			];
			*/
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}
 

	//Solicitud de Adenda
	public function send_email_solicitud_adenda_contrato_arrendamiento($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_solicitud_adenda_contrato_arrendamiento');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"ricardo.bendezu@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_adenda_contrato_proveedor($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_solicitud_adenda_contrato_proveedor');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_adenda_contrato_interno($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_adenda_contrato_interno');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_adenda_contrato_agente($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_solicitud_adenda_contrato_agente');
			/*
			$this->cc = [
				// area legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				//area comercial
				
				"jonathan.gutierrez@testtest.apuestatotal.com",
				"maria.meza@testtest.apuestatotal.com", // falta
				// area de contabilidad
				"richard.tomasto@testtest.apuestatotal.com",
				//area de tesoreria
				"jefferson.vicharra@testtest.apuestatotal.com",
				// area Municipal			 
				"arturo.huasasquiche@testtest.apuestatotal.com",
				//supervisores
				"lucia.navarro@testtest.apuestatotal.net" /// inactivo
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}
	
	public function send_email_solicitud_adenda_acuerdo_confidencialidad($emails)
	{
		if ($this->tipo == 'produccion') {
			
			$this->cc = $this->obtener_correos('send_email_solicitud_adenda_acuerdo_confidencialidad');
			/*
			$this->cc = [
				// Correos del área Legal
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
				// Correo del usuario que registro la solicitud
				// "$usuario_creacion_correo"
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

  	
	//modificaicon de contrato
	public function send_email_modificacion_contrato_arrendamiento($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_modificacion_contrato_arrendamiento');
		  /*
		  $this->cc = [
			  // Correos del área Legal
			  "mayra.duffoo@testtest.apuestatotal.com",
			  "sandra.murrugarra@testtest.apuestatotal.com",
			  "carolina.cano@testtest.apuestatotal.com",
			  "ingrid.escobar@testtest.apuestatotal.com",
			  "camila.silva@testtest.apuestatotal.com",
			  
		  ];
		  */
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }
  
		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}
  
	public function send_email_modificacion_contrato_proveedor($emails)
	{
	  if ($this->tipo == 'produccion') {

		   $this->cc = $this->obtener_correos('send_email_modificacion_contrato_proveedor');
			/*
		  $this->cc = [
			  "mayra.duffoo@testtest.apuestatotal.com",
			  "sandra.murrugarra@testtest.apuestatotal.com",
			  "carolina.cano@testtest.apuestatotal.com",
			  "ingrid.escobar@testtest.apuestatotal.com",
			  "camila.silva@testtest.apuestatotal.com",
			  
		  ];
		  */
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }
		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}

	public function send_email_cambio_contrato_confirmacion($emails)
	{
	  if ($this->tipo == 'produccion') {
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }
		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com"
		  ];
	  }else{ // En desarrollo
		for ($i=0; $i < count($emails) ; $i++) { 
			if(!Empty($emails[$i])){
				$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc_dev, $email);
				}
			}
		}
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'cc_dev' => array_unique($this->cc_dev),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}
  
	public function send_email_modificacion_acuerdo_confidencialidad($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_modificacion_acuerdo_confidencialidad');
		  /*
		  $this->cc = [
			  "mayra.duffoo@testtest.apuestatotal.com",
			  "sandra.murrugarra@testtest.apuestatotal.com",
			  "carolina.cano@testtest.apuestatotal.com",
			  "ingrid.escobar@testtest.apuestatotal.com",
			  "camila.silva@testtest.apuestatotal.com",
			  
		  ];
		  */
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }
		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}
  
	public function send_email_modificacion_contrato_agente($emails)
	{
	  if ($this->tipo == 'produccion') {

		   $this->cc = $this->obtener_correos('send_email_modificacion_contrato_agente');
		/*
		  $this->cc = [
			  "mayra.duffoo@testtest.apuestatotal.com",
			  "sandra.murrugarra@testtest.apuestatotal.com",
			  "carolina.cano@testtest.apuestatotal.com",
			  "ingrid.escobar@testtest.apuestatotal.com",
			  "camila.silva@testtest.apuestatotal.com",
			  
		  ];
		*/
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }
		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}
  
	public function send_email_modificacion_contrato_interno($emails)
	{
	  if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_modificacion_contrato_interno');
			/*
		  $this->cc = [
			  "mayra.duffoo@testtest.apuestatotal.com",
			  "sandra.murrugarra@testtest.apuestatotal.com",
			  "carolina.cano@testtest.apuestatotal.com",
			  "ingrid.escobar@testtest.apuestatotal.com",
			  "camila.silva@testtest.apuestatotal.com",
			  
		  ];
  			*/
		  // correos adionales        
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }
  
		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}

	// resolucion de contrato
	public function send_email_solicitud_resolucion_contrato($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_solicitud_resolucion_contrato');
		/*
		  $this->cc = [
			  "mayra.duffoo@testtest.apuestatotal.com",
			  "sandra.murrugarra@testtest.apuestatotal.com",
			  "carolina.cano@testtest.apuestatotal.com",
			  "ingrid.escobar@testtest.apuestatotal.com",
			  "camila.silva@testtest.apuestatotal.com",
			  
		  ];
		  */
		  // correos adionales        
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }

		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}


	// resolucion de contrato
	public function send_email_seguimiento_proceso_proveedores($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_seguimiento_proceso_proveedores');
		  // correos adionales        
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }

		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }else { // Desarroollo
		$this->cc_dev = $this->obtener_correos('send_email_seguimiento_proceso_proveedores');
		// correos adionales        
		for ($i=0; $i < count($emails) ; $i++) { 
			if(!Empty($emails[$i])){
				$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc_dev, $email);
				}
			}
		}
	}
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'cc_dev' => array_unique($this->cc_dev),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}

	// resolucion de contrato
	public function send_email_confirmacion_solicitud_resolucion_contrato($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = [];

		  // correos adionales        
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }

		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}

	public function send_email_solicitud_resolucion_contrato_firmado($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_solicitud_resolucion_contrato_firmado');
			/*
			$this->cc = [
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
			];
			*/
			// correos adionales        
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}


	public function send_email_solicitud_no_aplica($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_solicitud_no_aplica');
			/*
			$this->cc = [
				"mayra.duffoo@testtest.apuestatotal.com",
				"sandra.murrugarra@testtest.apuestatotal.com",
				"carolina.cano@testtest.apuestatotal.com",
				"ingrid.escobar@testtest.apuestatotal.com",
				"camila.silva@testtest.apuestatotal.com",
				
			
			];
			*/
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
			"yonathan.mamani@testtest.kurax.dev",
			"rossmery.garrido@testtest.kurax.dev",
			"ronald.visitacion@testtest.apuestatotal.com",
			"eduardo.chacaliaza@testtest.apuestatotal.com",
			//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}


	

	public function send_email_observacion_gerencia($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];
			// correos adionales        
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}


  	 //servicios publicos
	public function send_email_contrato_servicio_publico_observaciones($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];

			if (count($emails) == 0) {
				$this->cc = $this->obtener_correos('send_email_contrato_servicio_publico_observaciones');
				// array_push($this->cc, "prishlina.ramirez@testtest.apuestatotal.com");
				// array_push($this->cc, "wendy.aguilar@testtest.apuestatotal.com");
			}
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i]) && ($emails[$i] != "prishlina.ramirez@testtest.apuestatotal.com" && $emails[$i] != "wendy.aguilar@testtest.apuestatotal.com")){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				// "eduardo.chacaliaza@testtest.apuestatotal.com",
				// //  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_adenda_arrendamiento_escision($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_adenda_arrendamiento_escision');
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }
  
		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",
			  "eduardo.chacaliaza@testtest.apuestatotal.com",
			  //  "bladimir.quispe@testtest.apuestatotal.com"    
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}


	public function send_email_eliminacion_adenda_de_escision($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_eliminacion_adenda_de_escision');
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }

		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",  
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}

	public function send_email_objeto_adenda_proveedor($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_objeto_adenda_proveedor');
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }

		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",  
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}
	
	public function send_email_cron_contratos_por_vencer($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_cron_contratos_por_vencer');

			// $this->cc = [
			// 	"mayra.duffoo@testtest.apuestatotal.com",
			// 	"sandra.murrugarra@testtest.apuestatotal.com",
			// 	"carolina.cano@testtest.apuestatotal.com",
			// 	"ingrid.escobar@testtest.apuestatotal.com",
			// 	"camila.silva@testtest.apuestatotal.com",
			// ];

			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
				}
			}

			$this->excluir_correos_a_enviar('send_email_cron_contratos_por_vencer');// excluir algunos correos

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}


	public function send_email_cron_contratos_por_vencer_solicitante($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_cron_contratos_por_vencer_solicitante');

			// $this->cc = [
			// 	"mayra.duffoo@testtest.apuestatotal.com",
			// 	"sandra.murrugarra@testtest.apuestatotal.com",
			// 	"carolina.cano@testtest.apuestatotal.com",
			// 	"ingrid.escobar@testtest.apuestatotal.com",
			// 	"camila.silva@testtest.apuestatotal.com",
				
			// ];

			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty(trim($emails[$i]))) {
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->excluir_correos_a_enviar('excluir_gerentes');// excluir correos de los gerentes
			
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_cron_contratos_por_vencer_proveedores_solicitante($emails,$contrato_id,$empresa_suscribe_id,$emails_aprobante)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_cron_contratos_por_vencer_proveedores_solicitante');
			$usuarios_cargos = $this->obtener_correo_de_cargo_por_area($contrato_id,$empresa_suscribe_id);

			// $this->cc = [
			// 	"mayra.duffoo@testtest.apuestatotal.com",
			// 	"sandra.murrugarra@testtest.apuestatotal.com",
			// 	"carolina.cano@testtest.apuestatotal.com",
			// 	"ingrid.escobar@testtest.apuestatotal.com",
			// 	"camila.silva@testtest.apuestatotal.com",
				
			// ];

			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty(trim($emails[$i]))) {
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			// correos adionales
			for ($i=0; $i < count($usuarios_cargos) ; $i++) { 
				if(!Empty(trim($usuarios_cargos[$i]))) {
					$email = filter_var($usuarios_cargos[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			
			$this->excluir_correos_a_enviar('excluir_gerentes');// excluir correos de los gerentes

			for ($i=0; $i < count($emails_aprobante) ; $i++) { 
				if(!Empty(trim($emails_aprobante[$i]))) {
					$email = filter_var($emails_aprobante[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}
	
	public function send_email_cron_contratos_por_vencer_agente_solicitante($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_cron_contratos_por_vencer_agente_solicitante');
			// $this->cc = [
			// 	"mayra.duffoo@testtest.apuestatotal.com",
			// 	"sandra.murrugarra@testtest.apuestatotal.com",
			// 	"carolina.cano@testtest.apuestatotal.com",
			// 	"ingrid.escobar@testtest.apuestatotal.com",
			// 	"camila.silva@testtest.apuestatotal.com",
				
			// ];

			

			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty(trim($emails[$i]))) {
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->excluir_correos_a_enviar('excluir_gerentes');// excluir correos de los gerentes
			
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_cron_contratos_por_vencer_solicitante_arrendamiento($emails,$cc_ids)
	{
		if ($this->tipo == 'produccion') {


			$this->cc = $this->obtener_correos('send_email_cron_contratos_por_vencer_solicitante_arrendamiento');
			// $gerente_de_desarrollo_de_negocios = $this->obtener_correos_area_gerentes('ID_area_negocios');
			$gerente_de_proyectos = $this->obtener_correos_area_gerentes('ID_area_proyectos');
			$jefe_operaciones = $this->obtener_correo_desarrollo_inmobiliario_arrendamiento('ID_usuario_inmobiliario');
			// $jefes_comerciales = $this->obtener_correos_jefes_comerciales($cc_ids);
			// $this->cc = [
			// 	"walter.cortes@testtest.apuestatotal.com",
			// 	"mayra.duffoo@testtest.apuestatotal.com",
			// 	"sandra.murrugarra@testtest.apuestatotal.com",
			// 	"carolina.cano@testtest.apuestatotal.com",
			// 	"ingrid.escobar@testtest.apuestatotal.com",
			// 	"camila.silva@testtest.apuestatotal.com",
			// 	"ricardo.bendezu@testtest.apuestatotal.com"
			// ];

			
			// correos Gerente de Proyectos

			for ($i=0; $i < count($gerente_de_proyectos) ; $i++) { 
				if(!Empty(trim($gerente_de_proyectos[$i]))) {
					$gerente_de_proyecto = filter_var($gerente_de_proyectos[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($gerente_de_proyecto, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $gerente_de_proyecto);
					}
				}
			}
			// correos Jefe Operaciones  -- Desarrollo inmobiliario

			for ($i=0; $i < count($jefe_operaciones) ; $i++) { 
				if(!Empty(trim($jefe_operaciones[$i]))) {
					$jefe_peracione = filter_var($jefe_operaciones[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($jefe_peracione, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $jefe_peracione);
					}
				}
			}

			// for ($i=0; $i < count($jefes_comerciales) ; $i++) { 
			// 	if(!Empty(trim($jefes_comerciales[$i]))) {
			// 		$jefes_comerciale = filter_var($jefes_comerciales[$i], FILTER_SANITIZE_EMAIL);
			// 		if (filter_var($jefes_comerciale, FILTER_VALIDATE_EMAIL)) {
			// 			array_push($this->cc, $jefes_comerciale);
			// 		}
			// 	}
			// }
			// // correos adionales

			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty(trim($emails[$i]))) {
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
						
			// foreach ($emails as $email) {
			
			// 	if (!empty(trim($email))) {
			// 		$email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
			
			// 		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// 			array_push($this->cc, $email);
			// 		}
			// 	}
			// }
			
			

			$this->excluir_correos_a_enviar('excluir_gerentes');// excluir correos de los gerentes

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		// var_dump("correos 3");

		// var_dump($resultado);

		return $resultado;
	}

	public function send_email_cron_contratos_vencidos_por_mes()
	{

		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_cron_contratos_vencidos_por_mes');
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev"
			];
		}
		
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		
		return $resultado;
	}

	public function send_email_cron_licencias_por_vencer($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_cron_licencias_por_vencer');

			// $this->cc = [
			// 	"roberto.cunya@testtest.apuestatotal.com",
			// 	"brenda.puicon@testtest.apuestatotal.com",
			// 	"arturo.huasasquiche@testtest.apuestatotal.com"
			// ];
			
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	

	public function send_email_is_valid_email($str) {
		return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
	}


	public function send_email_requerimiento_marketing($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [
				"alonso.osores@testtest.apuestatotal.com",
			];
			// correos adionales        
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_cambio_marketing($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [
				"alonso.osores@testtest.apuestatotal.com",
			];
			// correos adionales        
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_subir_documento_resolucion_contrato($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];

			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",   
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_cancelar_solicitud($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];

			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_cancelar_solicitud_adenda($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_cancelar_solicitud_adenda');

			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_solicitud_aprobacion_adenda($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [];

			for ($i=0; $i < count($emails) ; $i++) { 
				if( ( !Empty($emails[$i]) ) && $this->send_email_is_valid_email($emails[$i]) ){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					array_push($this->cc, $email);
				}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				"eduardo.chacaliaza@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function enviar_correo_vales_rechazados($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [
				"gdt.sap@testtest.apuestatotal.com",
			];
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"kevin.montes@testtest.kurax.dev"
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function enviar_correo_contabilidad_provisiones($emails)
	{
		if ($this->tipo == 'produccion') {
			$this->cc = [
				 
				// Correos del Área Contable
				"prishlina.ramirez@testtest.apuestatotal.com",
				// Correos del Área de Tesorería
				"leandro.diaz@testtest.apuestatotal.com"
			   
				// Correo del usuario que registro la solicitud
				// "$email_user_created"
			];
			// correos adionales
			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"ronald.visitacion@testtest.apuestatotal.com",
				//  "bladimir.quispe@testtest.apuestatotal.com"    ,
				"ronald.visitacion@testtest.apuestatotal.com"
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_cambiar_estado_contrato($emails)
	{
	  if ($this->tipo == 'produccion') {

		  $this->cc = $this->obtener_correos('send_email_cambiar_estado_contrato');
		  // correos adionales
		  for ($i=0; $i < count($emails) ; $i++) { 
			  if(!Empty($emails[$i])){
				  $email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
				  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					  array_push($this->cc, $email);
				  }
			  }
		  }

		  $this->bcc = [
			  "yonathan.mamani@testtest.kurax.dev",
			  "rossmery.garrido@testtest.kurax.dev",
			  "ronald.visitacion@testtest.apuestatotal.com",  
		  ];
	  }
	  $resultado = array(
		  'cc' => array_unique($this->cc),
		  'bcc' => $this->bcc,
	  );
	  return $resultado;
	}

	public function obtener_correos($metodo){
		$correos = [];
		include("db_connect.php");

		$sel_query = $mysqli->query("SELECT p.correo
								FROM cont_mantenimiento_correos AS mc
								INNER JOIN cont_mantenimiento_correo_metodo AS mt ON mt.id = mc.metodo_id
								INNER JOIN tbl_usuarios AS u ON u.id = mc.usuario_id 
								INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id AND p.estado = 1
								WHERE mc.status = 1 AND u.estado = 1  AND mt.status = 1 AND mt.metodo = '".$metodo."'");
		while($sel = $sel_query->fetch_assoc())
		{
			$email = filter_var(trim($sel['correo']), FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				array_push($correos, $email);
			}
		}
	
		return $correos;
	}

	public function excluir_correos_a_enviar($metodo){
		$correos = [];
		include("db_connect.php");
		$sel_query = $mysqli->query("SELECT p.correo
								FROM tbl_mantenimiento_correos_excluidos AS ce
								INNER JOIN tbl_usuarios AS u ON u.id = ce.usuario_id 
								INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id AND p.estado = 1
								WHERE ce.status = 1 AND u.estado = 1 AND ce.metodo = '".$metodo."'");
		while($sel = $sel_query->fetch_assoc())
		{
			$email = filter_var(trim($sel['correo']), FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				array_push($correos, $email);
			}
		}
		$correos_final = array_diff($this->cc, $correos);
		$this->cc = array_values($correos_final);
		return true;
	}

	public function obtener_correos_area_gerentes($codigo){
		$correos = [];
		include("db_connect.php");
		$sel_query = $mysqli->query("SELECT codigo,descripcion,valor FROM tbl_parametros_generales WHERE codigo =  '".$codigo."' LIMIT 1");
		while($sel = $sel_query->fetch_assoc())
		{
			$area_id=$sel['valor'];
		}
		$query = "SELECT tpa.id as personal_id,tpa.nombre ,tpa.apellido_paterno ,
		tpa.correo ,tc.nombre as cargo, tc.id ,tbl_areas.nombre as area ,tbl_areas.id  
		FROM tbl_personal_apt tpa 
		INNER JOIN tbl_usuarios tu ON tu.personal_id = tpa.id 
		LEFT JOIN tbl_cargos tc ON tc.id = tpa.cargo_id
		LEFT JOIN tbl_zonas tz ON tz.id = tpa.zona_id
		LEFT JOIN tbl_areas ON tpa.area_id = tbl_areas.id 
		LEFT JOIN tbl_usuarios_grupos tug ON tug.id = tu.grupo_id 
		WHERE 
		tpa.estado = 1 AND tpa.estado = 1 AND tu.estado =1 
		AND tc.id = 3
		AND tbl_areas.id =".$area_id."
		AND (
			(LOWER(tpa.nombre) NOT LIKE '%test%' OR tpa.nombre IS NULL OR tpa.nombre = '')
			AND (LOWER(tpa.apellido_paterno) NOT LIKE '%test%' OR tpa.apellido_paterno IS NULL OR tpa.apellido_paterno = '')
			AND (LOWER(tpa.apellido_materno) NOT LIKE '%test%' OR tpa.apellido_materno IS NULL OR tpa.apellido_materno = '')
		)";
		
		$sel_query = $mysqli->query($query);
		while($sel = $sel_query->fetch_assoc())
		{
			$email = filter_var(trim($sel['correo']), FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				array_push($correos, $email);
			}
		}
	
		return $correos;
	}
	public function obtener_correos_jefes_comerciales($cc_ids){
		$correos = [];
		include("db_connect.php");
		
		 
		$cc_ids = implode(', ', $cc_ids);

		$query = "	SELECT 
		tl.id ,tl.nombre 
		,tpa.id as personal_id,concat( IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''),' ', IFNULL(tpa.apellido_materno, '')) AS nombre
		,tz.nombre as 'nombre de zona', tpa.correo 
		FROM tbl_locales tl 
		 LEFT  JOIN tbl_zonas tz ON tz.id = tl.zona_id 
		LEFT JOIN tbl_personal_apt tpa ON tpa.id = tz.jop_id  
		WHERE tl.estado=1
		AND tl.cc_id  in (".$cc_ids .")
		GROUP BY tz.nombre";
		// var_dump($query);exit();
	 
		$sel_query = $mysqli->query($query);
		while($sel = $sel_query->fetch_assoc())
		{
			$email = filter_var(trim($sel['correo']), FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				array_push($correos, $email);
			}
		}
	
		return $correos;
	}
	public function obtener_correo_desarrollo_inmobiliario_arrendamiento($codigo){
		$correos = [];
		include("db_connect.php");
		$sel_query = $mysqli->query("SELECT codigo,descripcion,valor FROM tbl_parametros_generales WHERE codigo = '".$codigo."' LIMIT 1");
		while($sel = $sel_query->fetch_assoc())
		{
			$usuario_id=$sel['valor'];
		}
		$query = "SELECT tpa.id as personal_id,tpa.nombre ,tpa.apellido_paterno ,
		tpa.correo ,tc.nombre as cargo, tc.id ,tbl_areas.nombre as area ,tbl_areas.id  
		FROM tbl_personal_apt tpa 
		INNER JOIN tbl_usuarios tu ON tu.personal_id = tpa.id 
		LEFT JOIN tbl_cargos tc ON tc.id = tpa.cargo_id
		LEFT JOIN tbl_zonas tz ON tz.id = tpa.zona_id
		LEFT JOIN tbl_areas ON tpa.area_id = tbl_areas.id 
		LEFT JOIN tbl_usuarios_grupos tug ON tug.id = tu.grupo_id 
		where 
		tpa.estado = 1 AND tpa.estado = 1 AND tu.estado =1 
		AND tu.id = ".$usuario_id."
		AND (
			(LOWER(tpa.nombre) NOT LIKE '%test%' OR tpa.nombre IS NULL OR tpa.nombre = '')
			AND (LOWER(tpa.apellido_paterno) NOT LIKE '%test%' OR tpa.apellido_paterno IS NULL OR tpa.apellido_paterno = '')
			AND (LOWER(tpa.apellido_materno) NOT LIKE '%test%' OR tpa.apellido_materno IS NULL OR tpa.apellido_materno = '')
		)";
		
		$sel_query = $mysqli->query($query);
	while($sel = $sel_query->fetch_assoc())
		{
			$email = filter_var(trim($sel['correo']), FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				array_push($correos, $email);
			}
		}
	
		return $correos;
	}

	public function obtener_correo_de_cargo_por_area($contrato_id,$empresa_suscribe_id){
		$correos = [];
		include("db_connect.php");
		$query_r_social	=	$mysqli->query("SELECT trs.id , trs.nombre ,trs.red_id  
                                FROM tbl_razon_social trs 
                                WHERE trs.id =".$empresa_suscribe_id)->fetch_assoc();

            $r_social_red = $query_r_social["red_id"]; // id de la red de la empresa

            	$query = "	SELECT  tu.id ,
							tpa.id ,tpa.nombre  ,tpa.correo ,
							cca.area_id , cca.cargo_id ,
							cc.area_responsable_id  
							,trs.red_id ,trs.nombre,trs.id  ,trs.red_id
							FROM cont_contrato cc 
							INNER JOIN cont_cargos_areas cca ON cc.area_responsable_id = cca.area_id 
							LEFT JOIN tbl_personal_apt tpa ON tpa.area_id  =  cca.area_id AND tpa.cargo_id = cca.cargo_id  
							INNER  JOIN tbl_usuarios tu ON tu.personal_id = tpa.id 
							LEFT JOIN tbl_razon_social trs ON trs.id = tpa.razon_social_id 
							WHERE cc.contrato_id =  ".$contrato_id."
							AND tpa.estado=1
							AND cca.estado= 1
							AND tu.estado = 1 " ;

                if($r_social_red	==	16){ // SOLO SI ES RED 16 TOMANMOS EL FILTRO 
                    $query.=    " AND trs.red_id = ".$r_social_red;
                }else{
                    $query.=    " AND (trs.red_id <> 16 OR trs.red_id IS NULL);";

				}
                // $query.=  " ORDER BY tu.id";


            $sel_query = $mysqli->query($query);
            while($sel = $sel_query->fetch_assoc())
            {
                $email = filter_var(trim($sel['correo']), FILTER_SANITIZE_EMAIL);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    array_push($correos, $email);
                }
                // array_push($correos, $sel['area_responsable_id']);

            }       
		return $correos;
	}


	public function send_email_autorizacion_mincetur($emails)
	{
		if ($this->tipo == 'produccion') {

			$this->cc = $this->obtener_correos('send_email_autorizacion_mincetur');

			for ($i=0; $i < count($emails) ; $i++) { 
				if(!Empty($emails[$i])){
					$email = filter_var($emails[$i], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($this->cc, $email);
					}
				}
			}

			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"jeremi.nunez@testtest.apuestatotal.com"
			];
		}
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		return $resultado;
	}

	public function send_email_cron_contratos_categoria_por_semana()
	{

		if ($this->tipo == 'produccion') {
			$this->cc = $this->obtener_correos('send_email_cron_contratos_categoria_por_semana');
			$this->bcc = [
				"yonathan.mamani@testtest.kurax.dev",
				"rossmery.garrido@testtest.kurax.dev",
				"jeremi.nunez@testtest.apuestatotal.com",
				"bladimir.quispe@testtest.kurax.dev"
			];
		}
		
		$resultado = array(
			'cc' => array_unique($this->cc),
			'bcc' => $this->bcc,
		);
		
		return $resultado;
	}

}
