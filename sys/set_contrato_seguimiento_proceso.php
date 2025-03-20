<?php 
class SeguimientoProceso {

	public function registrar_proceso ($data){
		try {
			include("db_connect.php");
			if (isset($data['usuario_id'])) {
				$data['usuario_id'] = "'".$data['usuario_id']."'";
				$data['updated_at'] = "'".$data['updated_at']."'";
				$data['user_updated_id'] = "'".$data['user_updated_id']."'";
			}else{
				$data['usuario_id'] = "NULL";
				$data['updated_at'] = "NULL";
				$data['user_updated_id'] = "NULL";
			}
			$query_insert = "INSERT INTO cont_seguimiento_proceso_legal (
						tipo_documento_id,
						proceso_id,
						proceso_detalle_id,
						usuario_id,
						area_id,
						etapa_id,
						status,
						created_at,
						user_created_id,
						updated_at,
						user_updated_id
					) VALUES ( 
						'".$data['tipo_documento_id']."',
						'".$data['proceso_id']."',
						'".$data['proceso_detalle_id']."',
						".$data['usuario_id'].",
						'".$data['area_id']."',
						'".$data['etapa_id']."',
						'".$data['status']."',
						'".$data['created_at']."',
						'".$data['user_created_id']."',
						".$data['updated_at'].",
						".$data['user_updated_id']."
					)";

			$mysqli->query($query_insert);
			if($mysqli->error){
				return [ 
						'status' => 404,
						'result' => 0,
						'message' => $mysqli->error . $query_insert
				];
			}
			$insert_id = mysqli_insert_id($mysqli);
			return [ 
				'status' => 200,
				'result' => $insert_id,
				'message' => 'Se registro el proceso exitosamente'
			];
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
		
	}

	public function modificar_proceso ($data){
		try {

			include("db_connect.php");

			if (isset($data['seguimiento_id'])) {
				$query_update = "UPDATE cont_seguimiento_proceso_legal SET 
							usuario_id = '".$data['usuario_id']."',
							status = '".$data['status']."',
							updated_at = '".$data['updated_at']."',
							user_updated_id = '".$data['user_updated_id']."'
						WHERE id = ".$data['seguimiento_id'];

				$mysqli->query($query_update);
				if($mysqli->error){
					return [ 
							'status' => 404,
							'result' => 0,
							'message' => $mysqli->error . $query_update
					];
				}
				return [ 
					'status' => 200,
					'result' => $data['seguimiento_id'],
					'message' => 'Se modifico el proceso exitosamente'
				];
			}else{
				$query_seguimiento = "SELECT id AS seguimiento_id FROM cont_seguimiento_proceso_legal 
					WHERE tipo_documento_id = ".$data['tipo_documento_id']." 
					AND proceso_id = ".$data['proceso_id']." 
					AND proceso_detalle_id = ".$data['proceso_detalle_id']." 
					and etapa_id = ".$data['etapa_id']." LIMIT 1";
				$list_query = $mysqli->query($query_seguimiento);
				$cant_seguimientos = $list_query->num_rows;
				if ($cant_seguimientos > 0) {
					$data_seguimiento = $list_query->fetch_assoc();

					$query_update = "UPDATE cont_seguimiento_proceso_legal SET 
								usuario_id = '".$data['usuario_id']."',
								status = '".$data['status']."',
								updated_at = '".$data['updated_at']."',
								user_updated_id = '".$data['user_updated_id']."'
							WHERE id = ".$data_seguimiento['seguimiento_id'];

					$mysqli->query($query_update);
					if($mysqli->error){
						return [ 
								'status' => 404,
								'result' => 0,
								'message' => $mysqli->error . $query_update
						];
					}
					return [ 
						'status' => 200,
						'result' => $data_seguimiento['seguimiento_id'],
						'message' => 'Se modifico el proceso exitosamente'
					];
				}
				return [ 
					'status' => 404,
					'result' => 0,
					'message' => 'No hay registros que modificar.'
				];
				
			}
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
		
	}

	public function aprobar_rechazar_seguimiento_proceso($data){

		try {
			include("db_connect.php");
		
			$INICIO_DE_PROCESO_LEGAL = 2;
			$NO_HAY_SEGUIMIENTO = 8;
			$CONTRATO = 1;
			$ADENDA_DE_CONTRATO = 2;
			$RESOLUCION_DE_CONTRATO = 3;
			$TIPO_DOCUMENTO_ID = 0;
			$CONTRATO_PROVEEDOR = 2;

			$data_proceso['area_id'] = 0;
			if ($data['tipo_documento_id'] == $CONTRATO) {
				$query_contrato = "SELECT area_responsable_id, tipo_contrato_id FROM cont_contrato WHERE contrato_id = ".$data['proceso_id'];
				$list_query = $mysqli->query($query_contrato);
				$data_contrato = $list_query->fetch_assoc();
				$data_proceso['area_id'] = $data_contrato['area_responsable_id'];
				$TIPO_DOCUMENTO_ID = $CONTRATO;
				$data_proceso['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
			}

			if ($data['tipo_documento_id'] == $ADENDA_DE_CONTRATO) {
				$query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_adendas ca INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id WHERE ca.id  = ".$data['proceso_id']." LIMIT 1";
				$list_query = $mysqli->query($query_adenda);
				$data_contrato = $list_query->fetch_assoc();
				$data_proceso['area_id'] = $data_contrato['area_responsable_id'];
				$TIPO_DOCUMENTO_ID = $ADENDA_DE_CONTRATO;
				$data_proceso['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
			}

			if ($data['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO) {
				$query_resolucion_contrato = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_resolucion_contrato crc  INNER JOIN cont_contrato cc ON cc.contrato_id = crc.contrato_id WHERE crc.id  = ".$data['proceso_id']." LIMIT 1";
				$list_query = $mysqli->query($query_resolucion_contrato);
				$data_contrato = $list_query->fetch_assoc();
				$data_proceso['area_id'] = $data_contrato['area_responsable_id'];
				$TIPO_DOCUMENTO_ID = $RESOLUCION_DE_CONTRATO;
				$data_proceso['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
			}

			$APROBACION_DEL_DOCUMENTO = 1;
			$INICIO_DE_PROCESO_LEGAL = 2;
			$NO_HAY_SEGUIMIENTO = 8;
			$AREA_LEGAL_ID = 33;

			if ($data_proceso['tipo_contrato_id'] == $CONTRATO_PROVEEDOR) {
				$seg_proceso = new SeguimientoProceso();
				if ($data['estado_aprobacion'] == 1) { // Aprobado
					//actualizar seguimiento proceso
					$data_proceso_mod['usuario_id'] = $data['usuario_id'];
					$data_proceso_mod['status'] = 2; //finalizado
					$data_proceso_mod['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_mod['user_updated_id'] = $data['usuario_id'];
					$data_proceso_mod['tipo_documento_id'] = $TIPO_DOCUMENTO_ID;
					$data_proceso_mod['proceso_id'] = $data['proceso_id'];
					$data_proceso_mod['proceso_detalle_id'] = $data['proceso_detalle_id'];
					$data_proceso_mod['etapa_id'] = $APROBACION_DEL_DOCUMENTO;
					$res_proceso = $seg_proceso->modificar_proceso($data_proceso_mod);

					//registrar seguimiento proceso
					$data_proceso_reg['tipo_documento_id'] = $TIPO_DOCUMENTO_ID;
					$data_proceso_reg['proceso_id'] = $data['proceso_id'];
					$data_proceso_reg['proceso_detalle_id'] = $data['proceso_detalle_id'];
					$data_proceso_reg['area_id'] = $AREA_LEGAL_ID;
					$data_proceso_reg['etapa_id'] = $INICIO_DE_PROCESO_LEGAL;
					$data_proceso_reg['status'] = 1;
					$data_proceso_reg['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg['user_created_id'] = $data['usuario_id'];
					$res_proceso = $seg_proceso->registrar_proceso($data_proceso_reg);
				}else if ($data['estado_aprobacion'] == 0){ // Rechazada
					//actualizar seguimiento proceso
					$data_proceso_mod['usuario_id'] = $data['usuario_id'];
					$data_proceso_mod['status'] = 2; //finalizado
					$data_proceso_mod['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_mod['user_updated_id'] = $data['usuario_id'];
					$data_proceso_mod['tipo_documento_id'] = $TIPO_DOCUMENTO_ID;
					$data_proceso_mod['proceso_id'] = $data['proceso_id'];
					$data_proceso_mod['proceso_detalle_id'] = $data['proceso_detalle_id'];
					$data_proceso_mod['etapa_id'] = $APROBACION_DEL_DOCUMENTO;
					$res_proceso = $seg_proceso->modificar_proceso($data_proceso_mod);

					//registrar seguimiento proceso
					$data_proceso_reg['tipo_documento_id'] = $TIPO_DOCUMENTO_ID;
					$data_proceso_reg['proceso_id'] = $data['proceso_id'];
					$data_proceso_reg['proceso_detalle_id'] = $data['proceso_detalle_id'];
					$data_proceso_reg['usuario_id'] = $data['usuario_id'];
					$data_proceso_reg['area_id'] = $data_proceso['area_id'];
					$data_proceso_reg['etapa_id'] = $NO_HAY_SEGUIMIENTO;
					$data_proceso_reg['status'] = 2;
					$data_proceso_reg['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg['user_created_id'] = $data['usuario_id'];
					$data_proceso_reg['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg['user_updated_id'] = $data['usuario_id'];
					$res_proceso = $seg_proceso->registrar_proceso($data_proceso_reg);
				}
			}

			
			
			
			
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
		

	}

	public function atender_inicio_proceso_legal($data){

		try {
			include("db_connect.php");
		
			$INICIO_DE_PROCESO_LEGAL = 2;
			$NO_HAY_SEGUIMIENTO = 8;
			$OBSERVADO = 8;

			$CONTRATO = 1;
			$ADENDA_DE_CONTRATO = 2;
			$RESOLUCION_DE_CONTRATO = 3;
			
			$AREA_LEGAL_ID = 33;
			$TIPO_DOCUMENTO_ID = 0;
			$CONTRATO_PROVEEDOR = 2;
			$data_proceso['area_id'] = 0;
			$data_proceso['tipo_contrato_id'] = 0;
			$data_proceso['usuario_area_creacion'] = 0;
			if ($data['tipo_documento_id'] == $CONTRATO) {
				$query_contrato = "SELECT cc.area_responsable_id, cc.tipo_contrato_id, tpa.area_id FROM cont_contrato cc LEFT JOIN tbl_usuarios tu ON tu.id = cc.user_created_id LEFT JOIN tbl_personal_apt tpa ON tpa.id = tu.personal_id
				WHERE cc.contrato_id = ".$data['proceso_id'];
				$list_query = $mysqli->query($query_contrato);
				$data_contrato = $list_query->fetch_assoc();
				$data_proceso['area_id'] = $data_contrato['area_responsable_id'];
				$data_proceso['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
				$data_proceso['usuario_area_creacion'] = $data_contrato['area_id'];
			} 
			if ($data['tipo_documento_id'] == $ADENDA_DE_CONTRATO) {
				$query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id, tpa.area_id 
				FROM cont_adendas ca INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id 
				LEFT JOIN tbl_usuarios tu ON tu.id = ca.user_created_id LEFT JOIN tbl_personal_apt tpa ON tpa.id = tu.personal_id
				WHERE ca.id  = ".$data['proceso_id']." LIMIT 1";
				$list_query = $mysqli->query($query_adenda);
				$data_adenda = $list_query->fetch_assoc();
				$data_proceso['area_id'] = $data_adenda['area_responsable_id'];
				$data_proceso['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
				$data_proceso['usuario_area_creacion'] = $data_adenda['area_id'];
			}
			if ($data['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO) {
				$query_resolucion_contrato = "SELECT cc.area_responsable_id, cc.tipo_contrato_id, tpa.area_id 
				FROM cont_resolucion_contrato crc  
				INNER JOIN cont_contrato cc ON cc.contrato_id = crc.contrato_id 
				LEFT JOIN tbl_usuarios tu ON tu.id = crc.user_created_id LEFT JOIN tbl_personal_apt tpa ON tpa.id = tu.personal_id
				WHERE crc.id  = ".$data['proceso_id']." LIMIT 1";
				$list_query = $mysqli->query($query_resolucion_contrato);
				$data_adenda = $list_query->fetch_assoc();
				$data_proceso['area_id'] = $data_adenda['area_responsable_id'];
				$data_proceso['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
				$data_proceso['usuario_area_creacion'] = $data_adenda['area_id'];
			}

			if ($data_proceso['tipo_contrato_id'] == $CONTRATO_PROVEEDOR) {
				//actualizar inicio proceso legal
				$data_proceso_mod['usuario_id'] = $data['usuario_id'];
				$data_proceso_mod['status'] = 2; //finalizado
				$data_proceso_mod['updated_at'] = date('Y-m-d H:i:s');
				$data_proceso_mod['user_updated_id'] = $data['usuario_id'];
				$data_proceso_mod['tipo_documento_id'] = $data['tipo_documento_id'];
				$data_proceso_mod['proceso_id'] = $data['proceso_id'];
				$data_proceso_mod['proceso_detalle_id'] = $data['proceso_detalle_id'];
				$data_proceso_mod['etapa_id'] = $INICIO_DE_PROCESO_LEGAL;
				$result_mod = $this->modificar_proceso($data_proceso_mod);

				if ($data['nueva_etapa_id'] > 0 && $data_proceso['usuario_area_creacion'] != $AREA_LEGAL_ID) {
					$AREA_LEGAL_ID = 33;
					//registrar seguimiento proceso
					$data_proceso_reg['tipo_documento_id'] = $data['tipo_documento_id'];;
					$data_proceso_reg['proceso_id'] = $data['proceso_id'];
					$data_proceso_reg['proceso_detalle_id'] = $data['proceso_detalle_id'];
					$data_proceso_reg['area_id'] = $data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO ? $AREA_LEGAL_ID : $data_proceso['area_id']; // En caso que la etapa sea 
					$data_proceso_reg['etapa_id'] = $data['nueva_etapa_id'];
					$data_proceso_reg['status'] = $data['status_nueva_etapa_id'];
					$data_proceso_reg['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg['user_created_id'] = $data['usuario_id'];
					if ($data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO || $data['nueva_etapa_id'] == $OBSERVADO) {
						$data_proceso_reg['usuario_id'] = $data['usuario_id'];
						$data_proceso_reg['updated_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg['user_updated_id'] = $data['usuario_id'];
					}
					$result_reg = $this->registrar_proceso($data_proceso_reg);

						//Notificar por Correo 
					if ($data['tipo_documento_id'] == $CONTRATO) {
						$data_correo['contrato_id'] = $data['proceso_id'];
						$data_correo['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
						$data_correo['seguimiento_id'] = $result_reg['result'];
						$this->notificar_seguimiento_proceso_contrato($data_correo);
					}else if ($data['tipo_documento_id'] == $ADENDA_DE_CONTRATO){
						$data_correo['adenda_id'] = $data['proceso_id'];
						$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
						$data_correo['seguimiento_id'] = $result_reg['result'];
						$this->notificar_seguimiento_proceso_adenda($data_correo);
					}else if ($data['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO){
						$data_correo['resolucion_id'] = $data['proceso_id'];
						$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
						$data_correo['seguimiento_id'] = $result_reg['result'];
						$this->notificar_seguimiento_proceso_resolucion($data_correo);
					}
					
				} else{
					if ($data_proceso['usuario_area_creacion'] != $AREA_LEGAL_ID) {
						//Notificar por Correo
						if ($data['tipo_documento_id'] == $CONTRATO) {
							$data_correo['contrato_id'] = $data['proceso_id'];
							$data_correo['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
							$data_correo['seguimiento_id'] = $result_mod['result'];
							$this->notificar_seguimiento_proceso_contrato($data_correo);
						}else if ($data['tipo_documento_id'] == $ADENDA_DE_CONTRATO){
							$data_correo['adenda_id'] = $data['proceso_id'];
							$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
							$data_correo['seguimiento_id'] = $result_mod['result'];
							$this->notificar_seguimiento_proceso_adenda($data_correo);
						}else if ($data['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO){
							$data_correo['resolucion_id'] = $data['proceso_id'];
							$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
							$data_correo['seguimiento_id'] = $result_mod['result'];
							$this->notificar_seguimiento_proceso_resolucion($data_correo);
						}
					}
				}
			}
			
			
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
		

	}

	public function reiniciar_seguimiento_proceso($data){

		try {
			include("db_connect.php");
			$REVISION_AREA_LEGAL = 2;
			$REVISION_AREA_USUARIA = 3;
		
			$CONTRATO = 1;
			$ADENDA_DE_CONTRATO = 2;
			$RESOLUCION_DE_CONTRATO = 3;

			$AREA_LEGAL_ID = 33;

			if ($data['nueva_etapa_id'] == $REVISION_AREA_USUARIA){
				
				if ($data['tipo_documento_id'] == $CONTRATO) {
						$query_contrato = "SELECT contrato_id, area_responsable_id, tipo_contrato_id FROM cont_contrato WHERE contrato_id = ".$data['proceso_id'];
						$list_query = $mysqli->query($query_contrato);
						$data_contrato = $list_query->fetch_assoc();
						
						//registrar seguimiento proceso revision area legal
						$data_proceso_reg_ral['tipo_documento_id'] = $CONTRATO;
						$data_proceso_reg_ral['proceso_id'] = $data['proceso_id'];
						$data_proceso_reg_ral['proceso_detalle_id'] = $data['proceso_detalle_id'];
						$data_proceso_reg_ral['area_id'] = $AREA_LEGAL_ID;
						$data_proceso_reg_ral['etapa_id'] = $REVISION_AREA_LEGAL;
						$data_proceso_reg_ral['status'] = 2; //Finalizado
						$data_proceso_reg_ral['created_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg_ral['user_created_id'] = $data['usuario_id'];
						$data_proceso_reg_ral['usuario_id'] = $data['usuario_id'];
						$data_proceso_reg_ral['updated_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg_ral['user_updated_id'] = $data['usuario_id'];
						$result_reg_paso_firmas = $this->registrar_proceso($data_proceso_reg_ral);

						//registrar seguimiento proceso revision area usuaria
						$data_proceso_reg_rau['tipo_documento_id'] = $CONTRATO;
						$data_proceso_reg_rau['proceso_id'] = $data['proceso_id'];
						$data_proceso_reg_rau['proceso_detalle_id'] = $data['proceso_detalle_id'];
						$data_proceso_reg_rau['area_id'] = $data_contrato['area_responsable_id'];
						$data_proceso_reg_rau['etapa_id'] = $REVISION_AREA_USUARIA;
						$data_proceso_reg_rau['status'] = 1; //Pendiente
						$data_proceso_reg_rau['created_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg_rau['user_created_id'] = $data['usuario_id'];
						$result_reg_revision_usuario = $this->registrar_proceso($data_proceso_reg_rau);

						//Notificar por Correo 
						$data_correo['contrato_id'] = $data['proceso_id'];
						$data_correo['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
						$data_correo['seguimiento_id'] = $result_reg_revision_usuario['result'];
						$this->notificar_seguimiento_proceso_contrato($data_correo);
						return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
				}
			}

			
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
		

	}

	public function atender_seguimiento_proceso($data){

		try {
			include("db_connect.php");
		
			$REVISION_AREA_USUARIA = 3;
			$REVISION_DEL_PROVEEDOR = 4;
			$REVISION_AREA_LEGAL = 5;
			$PASO_A_FIRMAS = 6;
			$CONFORMIDAD_AREA_USUARIA = 7;
			$NO_HAY_SEGUIMIENTO = 8;
			$OBSERVADO = 9;

			$CONTRATO = 1;
			$ADENDA_DE_CONTRATO = 2;
			$RESOLUCION_DE_CONTRATO = 3;

			$AREA_LEGAL_ID = 33;

			$query_seguimiento = "SELECT id  AS seguimiento_id, tipo_documento_id, proceso_id, proceso_detalle_id, area_id FROM cont_seguimiento_proceso_legal WHERE id = ".$data['seguimiento_id'];
			$list_query = $mysqli->query($query_seguimiento);
			$data_seguimiento = $list_query->fetch_assoc();

			//inicio actualizar proceso
			$data_proceso_mod['seguimiento_id'] = $data['seguimiento_id'];
			$data_proceso_mod['usuario_id'] = $data['usuario_id'];
			$data_proceso_mod['status'] = 2; //finalizado
			$data_proceso_mod['updated_at'] = date('Y-m-d H:i:s');
			$data_proceso_mod['user_updated_id'] = $data['usuario_id'];
			$result_mod = $this->modificar_proceso($data_proceso_mod);
			//fin actualizar proceso

			if ($data['nueva_etapa_id'] == $CONFORMIDAD_AREA_USUARIA) { //Finaliza el proceso con la conformidad del usuario
				if ($data_seguimiento['tipo_documento_id'] == $CONTRATO) {
					$query_contrato = "SELECT contrato_id, area_responsable_id, tipo_contrato_id FROM cont_contrato WHERE contrato_id = ".$data_seguimiento['proceso_id'];
					$list_query = $mysqli->query($query_contrato);
					$data_contrato = $list_query->fetch_assoc();

					$data_correo['contrato_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_mod['result'];
					$this->notificar_seguimiento_proceso_contrato($data_correo);

					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
				}else if ($data_seguimiento['tipo_documento_id'] == $ADENDA_DE_CONTRATO) {
					$query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_adendas ca INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id WHERE ca.id  = ".$data_seguimiento['proceso_id']." LIMIT 1";
					$list_query = $mysqli->query($query_adenda);
					$data_adenda = $list_query->fetch_assoc();

					$data_correo['adenda_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_mod['result'];
					$this->notificar_seguimiento_proceso_adenda($data_correo);

					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
				}else if ($data_seguimiento['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO) {
					$query_resolucion = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_resolucion_contrato crc INNER JOIN cont_contrato cc ON cc.contrato_id = crc.contrato_id WHERE crc.id  = ".$data_seguimiento['proceso_id']." LIMIT 1";
					$list_query = $mysqli->query($query_resolucion);
					$data_resolucion = $list_query->fetch_assoc();

					$data_correo['resolucion_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_resolucion['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_mod['result'];
					$this->notificar_seguimiento_proceso_resolucion($data_correo);

					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
				}
			
			}else if ($data['nueva_etapa_id'] == $PASO_A_FIRMAS){
				
				if ($data_seguimiento['tipo_documento_id'] == $CONTRATO) {
						$query_contrato = "SELECT contrato_id, area_responsable_id, tipo_contrato_id FROM cont_contrato WHERE contrato_id = ".$data_seguimiento['proceso_id'];
						$list_query = $mysqli->query($query_contrato);
						$data_contrato = $list_query->fetch_assoc();
						
						//registrar seguimiento proceso pase a firma
						$data_proceso_reg_paf['tipo_documento_id'] = $CONTRATO;
						$data_proceso_reg_paf['proceso_id'] = $data_seguimiento['proceso_id'];
						$data_proceso_reg_paf['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
						$data_proceso_reg_paf['area_id'] = $AREA_LEGAL_ID;
						$data_proceso_reg_paf['etapa_id'] = $data['nueva_etapa_id'];
						$data_proceso_reg_paf['status'] = 2; //Finalizado
						$data_proceso_reg_paf['created_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg_paf['user_created_id'] = $data['usuario_id'];
						$data_proceso_reg_paf['usuario_id'] = $data['usuario_id'];
						$data_proceso_reg_paf['updated_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg_paf['user_updated_id'] = $data['usuario_id'];
						$result_reg_paso_firmas = $this->registrar_proceso($data_proceso_reg_paf);

						//registrar seguimiento proceso conformidad area usuaria
						$data_proceso_reg_cau['tipo_documento_id'] = $CONTRATO;
						$data_proceso_reg_cau['proceso_id'] = $data_seguimiento['proceso_id'];
						$data_proceso_reg_cau['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
						$data_proceso_reg_cau['area_id'] = $data_contrato['area_responsable_id'];
						$data_proceso_reg_cau['etapa_id'] = $CONFORMIDAD_AREA_USUARIA;
						$data_proceso_reg_cau['status'] = 1; //Pendiente
						$data_proceso_reg_cau['created_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg_cau['user_created_id'] = $data['usuario_id'];
						$result_reg_conformidad_usuaria = $this->registrar_proceso($data_proceso_reg_cau);

						//Notificar por Correo 
						$data_correo['contrato_id'] = $data_seguimiento['proceso_id'];
						$data_correo['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
						$data_correo['seguimiento_id'] = $result_reg_paso_firmas['result'];
						$this->notificar_seguimiento_proceso_contrato($data_correo);
						return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
				}else if ($data_seguimiento['tipo_documento_id'] == $ADENDA_DE_CONTRATO) {
					$query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_adendas ca INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id WHERE ca.id  = ".$data_seguimiento['proceso_id']." LIMIT 1";
					$list_query = $mysqli->query($query_adenda);
					$data_adenda = $list_query->fetch_assoc();
					
					//registrar seguimiento proceso pase a firma
					$data_proceso_reg_paf['tipo_documento_id'] = $ADENDA_DE_CONTRATO;
					$data_proceso_reg_paf['proceso_id'] = $data_seguimiento['proceso_id'];
					$data_proceso_reg_paf['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
					$data_proceso_reg_paf['area_id'] = $AREA_LEGAL_ID;
					$data_proceso_reg_paf['etapa_id'] = $data['nueva_etapa_id'];
					$data_proceso_reg_paf['status'] = 2; //Finalizado
					$data_proceso_reg_paf['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_paf['user_created_id'] = $data['usuario_id'];
					$data_proceso_reg_paf['usuario_id'] = $data['usuario_id'];
					$data_proceso_reg_paf['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_paf['user_updated_id'] = $data['usuario_id'];
					$result_reg_paso_firmas = $this->registrar_proceso($data_proceso_reg_paf);

					//registrar seguimiento proceso conformidad area usuaria
					$data_proceso_reg_cau['tipo_documento_id'] = $ADENDA_DE_CONTRATO;
					$data_proceso_reg_cau['proceso_id'] = $data_seguimiento['proceso_id'];
					$data_proceso_reg_cau['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
					$data_proceso_reg_cau['area_id'] = $data_adenda['area_responsable_id'];
					$data_proceso_reg_cau['etapa_id'] = $CONFORMIDAD_AREA_USUARIA;
					$data_proceso_reg_cau['status'] = 1; //Pendiente
					$data_proceso_reg_cau['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_cau['user_created_id'] = $data['usuario_id'];
					$result_reg_conformidad_usuaria = $this->registrar_proceso($data_proceso_reg_cau);

					//Notificar por Correo 
					$data_correo['adenda_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_reg_paso_firmas['result'];
					$this->notificar_seguimiento_proceso_adenda($data_correo);
					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
				}else if ($data_seguimiento['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO) {
					$query_resolucion = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_resolucion_contrato crc INNER JOIN cont_contrato cc ON cc.contrato_id = crc.contrato_id WHERE crc.id  = ".$data_seguimiento['proceso_id']." LIMIT 1";
					$list_query = $mysqli->query($query_resolucion);
					$data_resolucion = $list_query->fetch_assoc();
					
					//registrar seguimiento proceso pase a firma
					$data_proceso_reg_paf['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO;
					$data_proceso_reg_paf['proceso_id'] = $data_seguimiento['proceso_id'];
					$data_proceso_reg_paf['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
					$data_proceso_reg_paf['area_id'] = $AREA_LEGAL_ID;
					$data_proceso_reg_paf['etapa_id'] = $data['nueva_etapa_id'];
					$data_proceso_reg_paf['status'] = 2; //Finalizado
					$data_proceso_reg_paf['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_paf['user_created_id'] = $data['usuario_id'];
					$data_proceso_reg_paf['usuario_id'] = $data['usuario_id'];
					$data_proceso_reg_paf['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_paf['user_updated_id'] = $data['usuario_id'];
					$result_reg_paso_firmas = $this->registrar_proceso($data_proceso_reg_paf);

					//registrar seguimiento proceso conformidad area usuaria
					$data_proceso_reg_cau['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO;
					$data_proceso_reg_cau['proceso_id'] = $data_seguimiento['proceso_id'];
					$data_proceso_reg_cau['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
					$data_proceso_reg_cau['area_id'] = $data_resolucion['area_responsable_id'];
					$data_proceso_reg_cau['etapa_id'] = $CONFORMIDAD_AREA_USUARIA;
					$data_proceso_reg_cau['status'] = 1; //Pendiente
					$data_proceso_reg_cau['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_cau['user_created_id'] = $data['usuario_id'];
					$result_reg_conformidad_usuaria = $this->registrar_proceso($data_proceso_reg_cau);

					//Notificar por Correo 
					$data_correo['resolucion_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_resolucion['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_reg_paso_firmas['result'];
					$this->notificar_seguimiento_proceso_resolucion($data_correo);
					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
				}
			}else{  // Caso contrario continua con los demas procesos
				if ($data_seguimiento['tipo_documento_id'] == $CONTRATO) {
					$data_proceso['area_id'] = 0;
					$data_proceso['tipo_contrato_id'] = 0;
	
					$query_contrato = "SELECT contrato_id, area_responsable_id, tipo_contrato_id FROM cont_contrato WHERE contrato_id = ".$data_seguimiento['proceso_id'];
					$list_query = $mysqli->query($query_contrato);
					$data_contrato = $list_query->fetch_assoc();
					$data_proceso['area_id'] = $data_contrato['area_responsable_id'];
					$data_proceso['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
	
					$area_id = 0;
					$status_nueva_etapa = 1; // Pendiente
					if ($data['nueva_etapa_id'] == $REVISION_AREA_LEGAL || $data['nueva_etapa_id'] == $PASO_A_FIRMAS) {
						$area_id = $AREA_LEGAL_ID;
					}else if ($data['nueva_etapa_id'] == $REVISION_AREA_USUARIA || $data['nueva_etapa_id'] == $REVISION_DEL_PROVEEDOR || $data['nueva_etapa_id'] == $CONFORMIDAD_AREA_USUARIA){
						$area_id = $data_contrato['area_responsable_id'];
					}else if ($data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO || $data['nueva_etapa_id'] == $OBSERVADO){
						$area_id = $data_contrato['area_responsable_id'];
						$status_nueva_etapa = 2; // Finalizado
					}
					//registrar seguimiento proceso
					$data_proceso_reg['tipo_documento_id'] = $CONTRATO;
					$data_proceso_reg['proceso_id'] = $data_seguimiento['proceso_id'];
					$data_proceso_reg['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
					$data_proceso_reg['area_id'] = $area_id;
					$data_proceso_reg['etapa_id'] = $data['nueva_etapa_id'];
					$data_proceso_reg['status'] = $status_nueva_etapa;
					$data_proceso_reg['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg['user_created_id'] = $data['usuario_id'];
					if ($data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO || $data['nueva_etapa_id'] == $OBSERVADO){
						$data_proceso_reg['usuario_id'] = $data['usuario_id'];
						$data_proceso_reg['updated_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg['user_updated_id'] = $data['usuario_id'];
					}
					$result_reg = $this->registrar_proceso($data_proceso_reg);
					//Notificar por Correo 
					$data_correo['contrato_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_contrato['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_reg['result'];
					$this->notificar_seguimiento_proceso_contrato($data_correo);
	
					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
	
				}else if ($data_seguimiento['tipo_documento_id'] == $ADENDA_DE_CONTRATO) {
					$data_proceso['area_id'] = 0;
					$data_proceso['tipo_contrato_id'] = 0;
	
					$query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_adendas ca INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id WHERE ca.id  = ".$data_seguimiento['proceso_id']." LIMIT 1";
					$list_query = $mysqli->query($query_adenda);
					$data_adenda = $list_query->fetch_assoc();
					$data_proceso['area_id'] = $data_adenda['area_responsable_id'];
					$data_proceso['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
	
					$area_id = 0;
					$status_nueva_etapa = 1; // Pendiente
					if ($data['nueva_etapa_id'] == $REVISION_AREA_LEGAL || $data['nueva_etapa_id'] == $PASO_A_FIRMAS) {
						$area_id = $AREA_LEGAL_ID;
					}else if ($data['nueva_etapa_id'] == $REVISION_AREA_USUARIA || $data['nueva_etapa_id'] == $REVISION_DEL_PROVEEDOR || $data['nueva_etapa_id'] == $CONFORMIDAD_AREA_USUARIA){
						$area_id = $data_adenda['area_responsable_id'];
					}else if ($data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO || $data['nueva_etapa_id'] == $OBSERVADO){
						$area_id = $data_adenda['area_responsable_id'];
						$status_nueva_etapa = 2; // Finalizado
					}
					//registrar seguimiento proceso
					$data_proceso_reg['tipo_documento_id'] = $ADENDA_DE_CONTRATO;
					$data_proceso_reg['proceso_id'] = $data_seguimiento['proceso_id'];
					$data_proceso_reg['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
					$data_proceso_reg['area_id'] = $area_id;
					$data_proceso_reg['etapa_id'] = $data['nueva_etapa_id'];
					$data_proceso_reg['status'] = $status_nueva_etapa;
					$data_proceso_reg['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg['user_created_id'] = $data['usuario_id'];
					if ($data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO || $data['nueva_etapa_id'] == $OBSERVADO){
						$data_proceso_reg['usuario_id'] = $data['usuario_id'];
						$data_proceso_reg['updated_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg['user_updated_id'] = $data['usuario_id'];
					}
					$result_reg = $this->registrar_proceso($data_proceso_reg);
					//Notificar por Correo 
					$data_correo['adenda_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_reg['result'];
					$this->notificar_seguimiento_proceso_adenda($data_correo);
	
					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
	
				}else if ($data_seguimiento['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO) {
					$data_proceso['area_id'] = 0;
					$data_proceso['tipo_contrato_id'] = 0;
	
					$query_resolucion = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_resolucion_contrato crc INNER JOIN cont_contrato cc ON cc.contrato_id = crc.contrato_id WHERE crc.id  = ".$data_seguimiento['proceso_id']." LIMIT 1";
					$list_query = $mysqli->query($query_resolucion);
					$data_resolucion = $list_query->fetch_assoc();
					$data_proceso['area_id'] = $data_resolucion['area_responsable_id'];
					$data_proceso['tipo_contrato_id'] = $data_resolucion['tipo_contrato_id'];
	
					$area_id = 0;
					$status_nueva_etapa = 1; // Pendiente
					if ($data['nueva_etapa_id'] == $REVISION_AREA_LEGAL || $data['nueva_etapa_id'] == $PASO_A_FIRMAS) {
						$area_id = $AREA_LEGAL_ID;
					}else if ($data['nueva_etapa_id'] == $REVISION_AREA_USUARIA || $data['nueva_etapa_id'] == $REVISION_DEL_PROVEEDOR || $data['nueva_etapa_id'] == $CONFORMIDAD_AREA_USUARIA){
						$area_id = $data_resolucion['area_responsable_id'];
					}else if ($data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO || $data['nueva_etapa_id'] == $OBSERVADO){
						$area_id = $data_resolucion['area_responsable_id'];
						$status_nueva_etapa = 2; // Finalizado
					}
					//registrar seguimiento proceso
					$data_proceso_reg['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO;
					$data_proceso_reg['proceso_id'] = $data_seguimiento['proceso_id'];
					$data_proceso_reg['proceso_detalle_id'] = $data_seguimiento['proceso_detalle_id'];
					$data_proceso_reg['area_id'] = $area_id;
					$data_proceso_reg['etapa_id'] = $data['nueva_etapa_id'];
					$data_proceso_reg['status'] = $status_nueva_etapa;
					$data_proceso_reg['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg['user_created_id'] = $data['usuario_id'];
					if ($data['nueva_etapa_id'] == $NO_HAY_SEGUIMIENTO || $data['nueva_etapa_id'] == $OBSERVADO) {
						$data_proceso_reg['usuario_id'] = $data['usuario_id'];
						$data_proceso_reg['updated_at'] = date('Y-m-d H:i:s');
						$data_proceso_reg['user_updated_id'] = $data['usuario_id'];
					}
					$result_reg = $this->registrar_proceso($data_proceso_reg);
					//Notificar por Correo 
					$data_correo['resolucion_id'] = $data_seguimiento['proceso_id'];
					$data_correo['tipo_contrato_id'] = $data_resolucion['tipo_contrato_id'];
					$data_correo['seguimiento_id'] = $result_reg['result'];
					$this->notificar_seguimiento_proceso_resolucion($data_correo);
	
					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
	
				}
			}
		
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
		

	}

	public function fin_seguimiento_proceso_alterno($data){
		try {
			include("db_connect.php");
			$INICIO_DE_PROCESO_LEGAL = 2;
			$CONFORMIDAD_AREA_USUARIA = 7;
			$FIN_DE_PROCESO_ALTERNO = 3;
		
			$CONTRATO = 1;
			$ADENDA_DE_CONTRATO = 2;
			$RESOLUCION_DE_CONTRATO = 3;

			$AREA_LEGAL_ID = 33;
			
			if ($data['tipo_documento_id'] == $CONTRATO) {
					$query_contrato = "SELECT contrato_id, area_responsable_id, tipo_contrato_id FROM cont_contrato WHERE contrato_id = ".$data['proceso_id'];
					$list_query = $mysqli->query($query_contrato);
					$data_contrato = $list_query->fetch_assoc();
					
					$query_contrato_pendiente = "SELECT cspl.id FROM cont_seguimiento_proceso_legal cspl 
					where cspl.status = 1 AND cspl.tipo_documento_id = ".$CONTRATO."  AND cspl.proceso_id  = ".$data['proceso_id']." LIMIT 1";
					$list_query_pend = $mysqli->query($query_contrato_pendiente);
					$data_contrato_pend = $list_query_pend->fetch_assoc();
					$cant_seguimientos_pend = $list_query_pend->num_rows;

					if ($cant_seguimientos_pend > 0) {
						//actualizar proceso legal
						$data_proceso_mod['seguimiento_id'] = $data_contrato_pend['id'];
						$data_proceso_mod['usuario_id'] = $data['usuario_id'];
						$data_proceso_mod['status'] = 2; //finalizado
						$data_proceso_mod['updated_at'] = date('Y-m-d H:i:s');
						$data_proceso_mod['user_updated_id'] = $data['usuario_id'];
						$result_mod = $this->modificar_proceso($data_proceso_mod);
					}

					//registrar seguimiento conformidad usuario
					$data_proceso_reg_rau['tipo_documento_id'] = $CONTRATO;
					$data_proceso_reg_rau['proceso_id'] = $data['proceso_id'];
					$data_proceso_reg_rau['proceso_detalle_id'] = $data['proceso_detalle_id'];
					$data_proceso_reg_rau['area_id'] = $data_contrato['area_responsable_id'];
					$data_proceso_reg_rau['etapa_id'] = $CONFORMIDAD_AREA_USUARIA;
					$data_proceso_reg_rau['status'] = 2; //Finalizado
					$data_proceso_reg_rau['created_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_rau['user_created_id'] = $data['usuario_id'];
					$data_proceso_reg_rau['usuario_id'] = $data['usuario_id'];
					$data_proceso_reg_rau['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_reg_rau['user_updated_id'] = $data['usuario_id'];
					$result_reg_conf_user = $this->registrar_proceso($data_proceso_reg_rau);

					return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
			}else if ($data['tipo_documento_id'] == $ADENDA_DE_CONTRATO) {
				$query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_adendas ca INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id WHERE ca.id  = ".$data['proceso_id']." LIMIT 1";
				$list_query = $mysqli->query($query_adenda);
				$data_adenda = $list_query->fetch_assoc();


				$query_adenda_pendiente = "SELECT cspl.id FROM cont_seguimiento_proceso_legal cspl 
				where cspl.status = 1 AND cspl.tipo_documento_id = ".$ADENDA_DE_CONTRATO."  AND cspl.proceso_id  = ".$data['proceso_id']." LIMIT 1";
				$list_query_pend = $mysqli->query($query_adenda_pendiente);
				$data_adenda_pend = $list_query_pend->fetch_assoc();
				$cant_seguimientos_pend = $list_query_pend->num_rows;

				if ($cant_seguimientos_pend > 0) {
					//actualizar proceso legal
					$data_proceso_mod['seguimiento_id'] = $data_adenda_pend['id'];
					$data_proceso_mod['usuario_id'] = $data['usuario_id'];
					$data_proceso_mod['status'] = 2; //finalizado
					$data_proceso_mod['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_mod['user_updated_id'] = $data['usuario_id'];
					$result_mod = $this->modificar_proceso($data_proceso_mod);
				}

				//registrar seguimiento conformidad usuario
				$data_proceso_reg_rau['tipo_documento_id'] = $ADENDA_DE_CONTRATO;
				$data_proceso_reg_rau['proceso_id'] = $data['proceso_id'];
				$data_proceso_reg_rau['proceso_detalle_id'] = $data['proceso_detalle_id'];
				$data_proceso_reg_rau['area_id'] = $data_adenda['area_responsable_id'];
				$data_proceso_reg_rau['etapa_id'] = $CONFORMIDAD_AREA_USUARIA;
				$data_proceso_reg_rau['status'] = 2; //Finalizado
				$data_proceso_reg_rau['created_at'] = date('Y-m-d H:i:s');
				$data_proceso_reg_rau['user_created_id'] = $data['usuario_id'];
				$data_proceso_reg_rau['usuario_id'] = $data['usuario_id'];
				$data_proceso_reg_rau['updated_at'] = date('Y-m-d H:i:s');
				$data_proceso_reg_rau['user_updated_id'] = $data['usuario_id'];
				$result_reg_conf_user = $this->registrar_proceso($data_proceso_reg_rau);

				//Notificar por Correo 
				$data_correo['adenda_id'] = $data['proceso_id'];
				$data_correo['tipo_contrato_id'] = $data_adenda['tipo_contrato_id'];
				$data_correo['seguimiento_id'] = $result_reg_conf_user['result'];
				$this->notificar_seguimiento_proceso_adenda($data_correo);
				return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
			}else if ($data['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO) {
				$query_resolucion = "SELECT cc.area_responsable_id, cc.tipo_contrato_id FROM cont_resolucion_contrato crc INNER JOIN cont_contrato cc ON cc.contrato_id = crc.contrato_id WHERE crc.id  = ".$data['proceso_id']." LIMIT 1";
				$list_query = $mysqli->query($query_resolucion);
				$data_resolucion = $list_query->fetch_assoc();

				$query_resolucion_pendiente = "SELECT cspl.id FROM cont_seguimiento_proceso_legal cspl 
				where cspl.status = 1 AND cspl.tipo_documento_id = ".$RESOLUCION_DE_CONTRATO."  AND cspl.proceso_id  = ".$data['proceso_id']." LIMIT 1";
				$list_query_pend = $mysqli->query($query_resolucion_pendiente);
				$data_resolucion_pend = $list_query_pend->fetch_assoc();
				$cant_seguimientos_pend = $list_query_pend->num_rows;

				if ($cant_seguimientos_pend > 0) {
					//actualizar proceso legal
					$data_proceso_mod['seguimiento_id'] = $data_resolucion_pend['id'];
					$data_proceso_mod['usuario_id'] = $data['usuario_id'];
					$data_proceso_mod['status'] = 2; //finalizado
					$data_proceso_mod['updated_at'] = date('Y-m-d H:i:s');
					$data_proceso_mod['user_updated_id'] = $data['usuario_id'];
					$result_mod = $this->modificar_proceso($data_proceso_mod);
				}

				//registrar seguimiento conformidad usuario
				$data_proceso_reg_rau['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO;
				$data_proceso_reg_rau['proceso_id'] = $data['proceso_id'];
				$data_proceso_reg_rau['proceso_detalle_id'] = $data['proceso_detalle_id'];
				$data_proceso_reg_rau['area_id'] = $data_resolucion['area_responsable_id'];
				$data_proceso_reg_rau['etapa_id'] = $CONFORMIDAD_AREA_USUARIA;
				$data_proceso_reg_rau['status'] = 2; //Finalizado
				$data_proceso_reg_rau['created_at'] = date('Y-m-d H:i:s');
				$data_proceso_reg_rau['user_created_id'] = $data['usuario_id'];
				$data_proceso_reg_rau['usuario_id'] = $data['usuario_id'];
				$data_proceso_reg_rau['updated_at'] = date('Y-m-d H:i:s');
				$data_proceso_reg_rau['user_updated_id'] = $data['usuario_id'];
				$result_reg_conf_user = $this->registrar_proceso($data_proceso_reg_rau);

				//Notificar por Correo 
				$data_correo['resolucion_id'] = $data['proceso_id'];
				$data_correo['tipo_contrato_id'] = $data_resolucion['tipo_contrato_id'];
				$data_correo['seguimiento_id'] = $result_reg_conf_user['result'];
				$this->notificar_seguimiento_proceso_resolucion($data_correo);
				return ['status' => 200, 'result' => 0, 'message' => 'Se ha notificado el seguimiento del proceso correctamente.'];
		}
			

			
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
	}

	public function notificar_seguimiento_proceso_contrato ($data){
		try {

			include("db_connect.php");
			$reenvio = isset($data['reenvio']) ? $data['reenvio'] : false;
			
			$query_seguimiento = "SELECT
					spl.id ,
					spl.proceso_id,
					spl.proceso_detalle_id ,
					spl.status,
					DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
					DATE_FORMAT(spl.updated_at, '%Y-%m-%d %H:%i') as updated_at,
					CONCAT(IFNULL(tpa.nombre, ''), ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS responsable,
					cesl.nombre as etapa,
					ta.nombre as area
				FROM
					cont_seguimiento_proceso_legal as spl
				INNER JOIN cont_etapa_seguimiento_legal cesl ON
					cesl.id = spl.etapa_id
				LEFT JOIN tbl_usuarios tu ON
					tu.id = spl.usuario_id
				LEFT JOIN tbl_personal_apt tpa ON
					tpa.id = tu.personal_id
				LEFT JOIN tbl_areas ta ON
					ta.id = spl.area_id
				WHERE spl.id = ".$data['seguimiento_id'];
			$sel_seg_query = $mysqli->query($query_seguimiento);
			$data_seguimiento = $sel_seg_query->fetch_assoc();

			$request = [];

			if ($data['tipo_contrato_id'] == 2) {// Contrato de Proveedores
				
				$host= $_SERVER["HTTP_HOST"];
				$sel_query = $mysqli->query("SELECT
						c.empresa_suscribe_id,
						rs.nombre AS empresa_suscribe,
						c.ruc AS proveedor_ruc,
						c.razon_social AS proveedor_razon_social,
						c.nombre_comercial AS proveedor_nombre_comercial,
						c.detalle_servicio,
						c.plazo_id,
						tp.nombre AS plazo,
						CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
						concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
						per.correo AS usuario_creacion_correo,
						c.fecha_inicio,
						ar.nombre AS area_creacion,
						c.check_gerencia_proveedor,
						c.fecha_atencion_gerencia_proveedor,
						c.aprobacion_gerencia_proveedor,
						co.sigla AS sigla_correlativo,
						c.codigo_correlativo,
						c.gerente_area_id,
						c.gerente_area_nombre,
						c.gerente_area_email,
						CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
						peg.correo AS email_del_gerente_area,
						CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por,
						puap.correo AS email_del_aprobante,
						CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado,
						pab.correo AS correo_abogado
					FROM 
						cont_contrato c
						LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
						LEFT JOIN cont_periodo p ON c.periodo = p.id
						INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
						INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
						INNER JOIN tbl_areas ar ON per.area_id = ar.id
						INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
						LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

						LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
						LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
						
						LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
						LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

						LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
						LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

					WHERE c.contrato_id = '".$data['contrato_id']."'
					");

				$body = "";
				$body .= '<html>';
				$correos_adicionales = [];

				while($sel = $sel_query->fetch_assoc())
				{
					$empresa_suscribe_id = $sel['empresa_suscribe_id'];
					$empresa_suscribe = $sel['empresa_suscribe'];

					$sigla_correlativo = $sel['sigla_correlativo'];
					$codigo_correlativo = $sel['codigo_correlativo'];

					$plazo_id = $sel["plazo_id"];
					$plazo = $sel["plazo"];

					$date = date_create($sel["fecha_inicio"]);
					$fecha_inicio_contrato = date_format($date, "Y/m/d");

					if (!Empty($sel['correo_abogado'])) {
						array_push($correos_adicionales, $sel['correo_abogado']); //Correo abogado
					}
					
					$gerente_area_id = trim($sel["gerente_area_id"]);
					if (empty($gerente_area_id)) {
						$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
						$gerente_area_email = trim($sel["gerente_area_email"]);
						array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
					} else {
						$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
						$gerente_area_email = trim($sel["email_del_gerente_area"]);
						array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
					}

					array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); //Correo Persona de Contacto

					if(!Empty($sel['email_del_aprobante'])){
						array_push($correos_adicionales, trim($sel['email_del_aprobante'])); //Correo del Aprobante
					}

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';
						$body .= '<tr>';
							$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
								$body .= '<b>Solicitud de Proveedor - '.$data_seguimiento['etapa'].'</b>';
							$body .= '</th>';
						$body .= '</tr>';
					$body .= '</thead>';
					$body .= '<tbody>';
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Contratante:</b></td>';
							$body .= '<td>'.$sel["empresa_suscribe"].'</td>';
						$body .= '</tr>';
				
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>RUC Proveedor:</b></td>';
							$body .= '<td>'.$sel["proveedor_ruc"].'</td>';
						$body .= '</tr>';
						
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Razon social Proveedor:</b></td>';
							$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
						$body .= '</tr>';
				
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Nombre Comercial Proveedor:</b></td>';
							$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
						$body .= '</tr>';
				
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Plazo</b></td>';
							$body .= '<td>' . $plazo . '</td>';
						$body .= '</tr>';
				
						if($plazo_id == 1) {
							$body .= '<tr>';
								$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
								$body .= '<td>'.$sel["periodo"].'</td>';
							$body .= '</tr>';
						}
				
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
							$body .= '<td>'.$sel["usuario_creacion"].'</td>';
						$body .= '</tr>';
				
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Responsable de Área:</b></td>';
							$body .= '<td>' . $gerente_area_nombre . '</td>';
						$body .= '</tr>';
				
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
							$body .= '<td>'.$fecha_inicio_contrato.'</td>';
						$body .= '</tr>';
				
						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
							$body .= '<td>'.$sel["detalle_servicio"].'</td>';
						$body .= '</tr>';
				
					$body .= '</tbody>';
					$body .= '</table>';
					$body .= '</div>';

				}

				$body .= '<div>';
					$body .= '<br>';
				$body .= '</div>';
	
				$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
					$body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$data['contrato_id'].'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">'; 
						$body .= '<b>Ver Solicitud</b>';
					$body .= '</a>';
				$body .= '</div>';

				if ($empresa_suscribe_id != 0) {
					$sql_contador_tesorero = "SELECT 
							p.correo
						FROM
							cont_usuarios_razones_sociales urs
							INNER JOIN tbl_usuarios u ON urs.user_id = u.id
							INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
						WHERE
							urs.razon_social_id = ".$empresa_suscribe_id."
							AND p.estado = 1  AND u.estado = 1";
					$sel_query = $mysqli->query($sql_contador_tesorero);
					$row_count = $sel_query->num_rows;
					if ($row_count > 0) {
						while($sel = $sel_query->fetch_assoc()){
							array_push($correos_adicionales, $sel['correo']);
						}
					}
				}

				$text_reenvio = $reenvio ? "Reenvío - ":"";
				$titulo_email = "Gestion - Sistema Contratos - ".$text_reenvio.$data_seguimiento['etapa']." - Solicitud de Proveedor : CÓD - ".$sigla_correlativo.$codigo_correlativo;
				$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
				$lista_correos = $correos->send_email_seguimiento_proceso_proveedores($correos_adicionales);
			
				if (env('SEND_EMAIL') == 'TEST') { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
					$correos_produccion = implode(", ", $lista_correos['cc_dev']);

					$body .= '<div>';
						$body .= '<br>';
					$body .= '</div>';
		
					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';
					$body .= '<tr>';
						$body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Lista de Correos</b>';
						$body .= '</th>';
					$body .= '</tr>';
					$body .= '</thead>';
					$body .= '<tbody>';
					$body .= '<tr>';
						$body .= '<td>'.$correos_produccion.'</td>';
					$body .= '</tr>';
					$body .= '</tfoot>';
					$body .= '<tr>';
						$body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
					$body .= '</tr>';
					$body .= '</tfoot>';
					$body .= '</table>';
					$body .= '</div>';
				}

				$request = [
					"subject" => $titulo_email,
					"body"    => $body,
					"cc"      => $lista_correos['cc'],
					"bcc"     => $lista_correos['bcc'],
					"attach"  => [
						// $filepath . $file,
					],
				];
			}


			if (isset($request['cc'])) { // validar que tenga seteado la info de correo
				$mail = new PHPMailer(true);
				$mail->isSMTP();
				$mail->Host = "smtp.gmail.com";
				$mail->SMTPAuth = true;
		
				$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
				$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
				$mail->Port = 465;
				$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
				$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');
		
				if(isset($request["cc"]))
				{
					foreach ($request["cc"] as $cc) 
					{
						$mail->addAddress($cc);
					}
				}
		
				if(isset($request["bcc"]))
				{
					foreach ($request["bcc"] as $bcc) 
					{
						$mail->addBCC($bcc);
					}
				}
		
				$mail->isHTML(true);
				$mail->Subject  = $request["subject"];
				$mail->Body     = $request["body"];
				$mail->CharSet = 'UTF-8';
				$mail->Encoding = 'base64';
				$mail->send();
			}

			return [ 
				'status' => 200,
				'result' => 0,
				'message' => 'Se ha enviado el correo correctamente.'
			];
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
	}

	public function notificar_seguimiento_proceso_adenda ($data){
		try {

			include("db_connect.php");
			$reenvio = isset($data['reenvio']) ? $data['reenvio'] : false;

			$query_seguimiento = "SELECT
					spl.id ,
					spl.proceso_id,
					spl.proceso_detalle_id ,
					spl.status,
					DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
					DATE_FORMAT(spl.updated_at, '%Y-%m-%d %H:%i') as updated_at,
					CONCAT(IFNULL(tpa.nombre, ''), ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS responsable,
					cesl.nombre as etapa,
					ta.nombre as area
				FROM
					cont_seguimiento_proceso_legal as spl
				INNER JOIN cont_etapa_seguimiento_legal cesl ON
					cesl.id = spl.etapa_id
				LEFT JOIN tbl_usuarios tu ON
					tu.id = spl.usuario_id
				LEFT JOIN tbl_personal_apt tpa ON
					tpa.id = tu.personal_id
				LEFT JOIN tbl_areas ta ON
					ta.id = spl.area_id
				WHERE spl.id = ".$data['seguimiento_id'];
			$sel_seg_query = $mysqli->query($query_seguimiento);
			$data_seguimiento = $sel_seg_query->fetch_assoc();

			$request = [];

			if ($data['tipo_contrato_id'] == 2) {// Adenda de Proveedores
				
				$host= $_SERVER["HTTP_HOST"];

				$query_adenda = "SELECT
					a.id,
					a.created_at,
					co.sigla,
					c.codigo_correlativo,
					c.contrato_id,
					tc.nombre,
					concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
					r.nombre AS empresa_suscribe,
					ar.nombre AS nombre_area,
					c.gerente_area_email as email_gerente_area_2,
					tp.correo as email_creacion_adenda,
					per.correo AS email_creacion_contrato,
					peg.correo AS email_del_gerente_area,
					puap.correo AS email_del_aprobante,
					pab.correo AS email_del_abogado_adenda,
					pera.correo AS email_del_aprobante_adenda
				FROM
					cont_adendas AS a
					INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
					INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
					INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
					INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
					INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id
					INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
					LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

					LEFT JOIN tbl_usuarios uaa ON a.aprobado_por_id = uaa.id
					LEFT JOIN tbl_personal_apt pera ON uaa.personal_id = pera.id

					LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
					LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

					LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
					LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
					
					LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
					LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

					LEFT JOIN tbl_usuarios uab ON a.abogado_id = uab.id
					LEFT JOIN tbl_personal_apt pab ON uap.personal_id = pab.id
				WHERE
					a.id = ".$data['adenda_id']."
				";

				$sel_query = $mysqli->query($query_adenda);

				$body = "";
				$body .= '<html>';
				$correos_adicionales = [];
				$contrato_id = 0;
				while($sel = $sel_query->fetch_assoc())
				{
					$contrato_id = $sel['contrato_id'];
					$sigla_correlativo = $sel['sigla'];
					$codigo_correlativo = $sel['codigo_correlativo'];
					$empresa_suscribe = $sel['empresa_suscribe'];
					$fecha_solicitud = $sel['created_at'];
					$usuario_solicitante = $sel['usuario_solicitante'];
					if(!Empty($sel['email_creacion_adenda'])){
						array_push($correos_adicionales, $sel['email_creacion_adenda']);
					}
					if(!Empty($sel['email_creacion_contrato'])){
						array_push($correos_adicionales, $sel['email_creacion_contrato']);
					}
					if(!Empty($sel['email_del_gerente_area'])){
						array_push($correos_adicionales, $sel['email_del_gerente_area']);
					}
					if(!Empty($sel['email_gerente_area_2'])){
						array_push($correos_adicionales, $sel['email_gerente_area_2']);
					}
					if(!Empty($sel['email_del_aprobante'])){
						array_push($correos_adicionales, $sel['email_del_aprobante']);
					}
					if(!Empty($sel['email_del_aprobante_adenda'])){
						array_push($correos_adicionales, $sel['email_del_aprobante_adenda']);
					}
					if(!Empty($sel['email_del_abogado_adenda'])){
						array_push($correos_adicionales, $sel['email_del_abogado_adenda']);
					}

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Solicitud Adenda de Proveedor - '.$data_seguimiento['etapa'].'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
						$body .= '<td>'.$sel["nombre_area"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
						$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
						$body .= '<td>'.$sel["created_at"].'</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';



					$query = $mysqli->query("SELECT id,
						adenda_id,
						nombre_tabla,
						valor_original,
						nombre_menu_usuario,
						nombre_campo_usuario,
						nombre_campo,
						tipo_valor,
						valor_varchar,
						valor_int,
						valor_date,
						valor_decimal,
						valor_select_option,
						status
					FROM cont_adendas_detalle
					WHERE
						adenda_id = " . $data['adenda_id'] . "
						AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
						AND status = 1
					");

					$row_count = $query->num_rows;
					$numero_adenda_detalle = 0;

					if ($row_count > 0) {
						$body .= '<div>';
						$body .= '<br>';
						$body .= '</div>';

						$body .= '<div>';
						$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

						$body .= '<thead>';

						$body .= '<tr>';
							$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
								$body .= '<b>Detalle</b>';
							$body .= '</th>';
						$body .= '</tr>';


						$body .= '</thead>';

						$body .= '<tr>';
							$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
						$body .= '</tr>';

						while($row = $query->fetch_assoc()){
							$nombre_menu_usuario = $row["nombre_menu_usuario"];
							$nombre_campo_usuario = $row["nombre_campo_usuario"];
							$valor_original = $row["valor_original"];
							$tipo_valor = $row["tipo_valor"];
							if ($tipo_valor == 'varchar') {
								$nuevo_valor = $row['valor_varchar'];
							} else if ($tipo_valor == 'int') {
								$nuevo_valor = $row['valor_int'];
							} else if ($tipo_valor == 'date') {
								$nuevo_valor = $row['valor_date'];
							} else if ($tipo_valor == 'decimal') {
								$nuevo_valor = $row['valor_decimal'];
							} else if ($tipo_valor == 'select_option') {
								$nuevo_valor = $row['valor_select_option'];
							}
							$numero_adenda_detalle++;

							$body .= '<tr>';
								$body .= '<td>'.$numero_adenda_detalle.'</td>';
								$body .= '<td>'.$nombre_menu_usuario.'</td>';
								$body .= '<td>'.$nombre_campo_usuario.'</td>';
								$body .= '<td>'.$valor_original.'</td>';
								$body .= '<td>'.$nuevo_valor.'</td>';
							$body .= '</tr>';

						}
						$body .= '</table>';
						$body .= '</div>';
					}


					$query = $mysqli->query("SELECT id,
							adenda_id,
							nombre_tabla,
							valor_original,
							nombre_menu_usuario,
							nombre_campo_usuario,
							nombre_campo,
							tipo_valor,
							valor_varchar,
							valor_int,
							valor_date,
							valor_decimal,
							valor_select_option,
							status
						FROM cont_adendas_detalle
						WHERE adenda_id = " . $data['adenda_id'] . "
						AND tipo_valor = 'registro'
						AND status = 1");
					$row_count = $query->num_rows;
					$numero_adenda_detalle = 0;
					if ($row_count > 0) {
						while($row = $query->fetch_assoc()){
							if ($row["nombre_menu_usuario"] == 'Representate Legal') {
								$query_pro = "
								SELECT
									rl.id,
									rl.dni_representante,
									rl.nombre_representante,
									rl.nro_cuenta_detraccion,
									rl.id_banco,
									b.nombre as banco_representante,
									rl.nro_cuenta,
									rl.nro_cci,
									rl.vigencia_archivo_id,
									rl.dni_archivo_id
								FROM
									cont_representantes_legales rl
									LEFT JOIN tbl_bancos b on b.id = rl.id_banco
								WHERE
									rl.id IN ('" . $row["valor_int"] . "')
								";

								$valores_originales = [];
								$valores_nuevos = [];
								$list_query = $mysqli->query($query_pro);
								while ($li = $list_query->fetch_assoc()) {
									if ($li["id"] == $row["valor_int"]) {
										$valores_nuevos[] = $li;
									}
								}
								$body .= '<div>';
								$body .= '<br>';
								$body .= '</div>';

								$body .= '<div>';
								$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

								$body .= '<thead>';

								$body .= '<tr>';
									$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
										$body .= '<b>Nuevo Representante Legal</b>';
									$body .= '</th>';
								$body .= '</tr>';

								$body .= '<tr>';
									$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
									$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
								$body .= '</tr>';

								$body .= '</thead>';

								$body .= '<tr>';
									$body .= '<td >DNI del representante legal</td>';
									$body .= '<td >'.$valores_nuevos[0]["dni_representante"].'</td>';
								$body .= '</tr>';

								$body .= '<tr>';
									$body .= '<td >Nombre completo del representante legal</td>';
									$body .= '<td >'.$valores_nuevos[0]["nombre_representante"].'</td>';
								$body .= '</tr>';

								$body .= '<tr>';
									$body .= '<td >Nro cuenta de detraccion (Banco de la nación)</td>';
									$body .= '<td >'.$valores_nuevos[0]["nro_cuenta_detraccion"].'</td>';
								$body .= '</tr>';

								$body .= '<tr>';
									$body .= '<td >Banco</td>';
									$body .= '<td >'.$valores_nuevos[0]["banco_representante"].'</td>';
								$body .= '</tr>';

								$body .= '<tr>';
									$body .= '<td >Nro Cuenta</td>';
									$body .= '<td >'.$valores_nuevos[0]["nro_cuenta"].'</td>';
								$body .= '</tr>';

								$body .= '<tr>';
									$body .= '<td >Nro CCI</td>';
									$body .= '<td >'.$valores_nuevos[0]["nro_cci"].'</td>';
								$body .= '</tr>';



								$body .= '</table>';
								$body .= '</div>';
							}

							if ($row["nombre_menu_usuario"] == 'Contraprestación') {
								$query_cont = "
								SELECT
									c.id,
									c.moneda_id,
									m.nombre AS tipo_moneda,
									m.simbolo AS tipo_moneda_simbolo,
									c.subtotal,
									c.igv,
									c.monto,
									c.forma_pago_detallado,
									c.tipo_comprobante_id,
									t.nombre AS tipo_comprobante,
									c.plazo_pago
								FROM
									cont_contraprestacion c
									INNER JOIN tbl_moneda m ON c.moneda_id = m.id
									INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
								WHERE
									c.id IN ('" . $row["valor_int"] . "')
								";

								$valores_originales = [];
								$valores_nuevos = [];
								$list_query = $mysqli->query($query_cont);
								while ($li = $list_query->fetch_assoc()) {
									if ($li["id"] == $row["valor_int"]) {
										$valores_nuevos[] = $li;
									}
								}
								$body .= '<div>';
								$body .= '<br>';
								$body .= '</div>';

								$body .= '<div>';
								$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

								$body .= '<thead>';

								$body .= '<tr>';
									$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
										$body .= '<b>Nueva Contraprestación</b>';
									$body .= '</th>';
								$body .= '</tr>';

								$body .= '<tr>';
									$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
									$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
								$body .= '</tr>';

								$body .= '</thead>';

								$body .= '<tr>';
								$body .= '<td>Tipo de moneda</td>';
								$body .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
								$body .= '</tr>';

								$body .= '<tr>';
								$body .= '<td>Subtotal</td>';
								$body .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
								$body .= '</tr>';

								$body .= '<tr>';
								$body .= '<td>IGV</td>';
								$body .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
								$body .= '</tr>';

								$body .= '<tr>';
								$body .= '<td>Monto Bruto</td>';
								$body .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
								$body .= '</tr>';

								$body .= '<tr>';
								$body .= '<td>Tipo de comprobante a emitir</td>';
								$body .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
								$body .= '</tr>';

								$body .= '<tr>';
								$body .= '<td>Plazo de Pago</td>';
								$body .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
								$body .= '</tr>';

								$body .= '<tr>';
								$body .= '<td>Forma de pago</td>';
								$body .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
								$body .= '</tr>';


								$body .= '</table>';
								$body .= '</div>';
							}
						}
					}
				}


				$body .= '<div>';
					$body .= '<br>';
				$body .= '</div>';
	
				$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
					$body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id.'&adenda_id='.$data['adenda_id'].'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">'; 
						$body .= '<b>Ver Solicitud</b>';
					$body .= '</a>';
				$body .= '</div>';

				
				$text_reenvio = $reenvio ? "Reenvío - ":"";
				$titulo_email = "Gestion - Sistema Contratos - ".$text_reenvio.$data_seguimiento['etapa']." - Solicitud de Adenda Proveedor : CÓD - ".$sigla_correlativo.$codigo_correlativo;
				$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
				$lista_correos = $correos->send_email_seguimiento_proceso_proveedores($correos_adicionales);
			
				if (env('SEND_EMAIL') == 'TEST' && isset($lista_correos['cc_dev'])) { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
					$correos_produccion = implode(", ", $lista_correos['cc_dev']);

					$body .= '<div>';
						$body .= '<br>';
					$body .= '</div>';
		
					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';
					$body .= '<tr>';
						$body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Lista de Correos</b>';
						$body .= '</th>';
					$body .= '</tr>';
					$body .= '</thead>';
					$body .= '<tbody>';
					$body .= '<tr>';
						$body .= '<td>'.$correos_produccion.'</td>';
					$body .= '</tr>';
					$body .= '</tfoot>';
					$body .= '<tr>';
						$body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
					$body .= '</tr>';
					$body .= '</tfoot>';
					$body .= '</table>';
					$body .= '</div>';
				}

				$request = [
					"subject" => $titulo_email,
					"body"    => $body,
					"cc"      => $lista_correos['cc'],
					"bcc"     => $lista_correos['bcc'],
					"attach"  => [
						// $filepath . $file,
					],
				];
			}


			if (isset($request['cc'])) { // validar que tenga seteado la info de correo
				$mail = new PHPMailer(true);
				$mail->isSMTP();
				$mail->Host = "smtp.gmail.com";
				$mail->SMTPAuth = true;
		
				$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
				$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
				$mail->Port = 465;
				$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
				$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');
		
				if(isset($request["cc"]))
				{
					foreach ($request["cc"] as $cc) 
					{
						$mail->addAddress($cc);
					}
				}
		
				if(isset($request["bcc"]))
				{
					foreach ($request["bcc"] as $bcc) 
					{
						$mail->addBCC($bcc);
					}
				}
		
				$mail->isHTML(true);
				$mail->Subject  = $request["subject"];
				$mail->Body     = $request["body"];
				$mail->CharSet = 'UTF-8';
				$mail->Encoding = 'base64';
				$mail->send();
			}

			return [ 
				'status' => 200,
				'result' => 0,
				'message' => 'Se ha enviado el correo correctamente.'
			];
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
	}

	public function notificar_seguimiento_proceso_resolucion ($data){
		try {

			include("db_connect.php");
			$reenvio = isset($data['reenvio']) ? $data['reenvio'] : false;

			$query_seguimiento = "SELECT
					spl.id ,
					spl.proceso_id,
					spl.proceso_detalle_id ,
					spl.status,
					DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
					DATE_FORMAT(spl.updated_at, '%Y-%m-%d %H:%i') as updated_at,
					CONCAT(IFNULL(tpa.nombre, ''), ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS responsable,
					cesl.nombre as etapa,
					ta.nombre as area
				FROM
					cont_seguimiento_proceso_legal as spl
				INNER JOIN cont_etapa_seguimiento_legal cesl ON
					cesl.id = spl.etapa_id
				LEFT JOIN tbl_usuarios tu ON
					tu.id = spl.usuario_id
				LEFT JOIN tbl_personal_apt tpa ON
					tpa.id = tu.personal_id
				LEFT JOIN tbl_areas ta ON
					ta.id = spl.area_id
				WHERE spl.id = ".$data['seguimiento_id'];
			$sel_seg_query = $mysqli->query($query_seguimiento);
			$data_seguimiento = $sel_seg_query->fetch_assoc();

			$request = [];

			if ($data['tipo_contrato_id'] == 2) {// Adenda de Proveedores
				
				$host= $_SERVER["HTTP_HOST"];

				$query_resolucion = "SELECT r.id, r.contrato_id, c.tipo_contrato_id, r.motivo, r.fecha_solicitud, 
				DATE_FORMAT(r.fecha_resolucion,'%d-%m-%Y') AS fecha_resolucion,
				DATE_FORMAT(r.fecha_carta,'%d-%m-%Y') AS fecha_carta,
				CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
				r.anexo_archivo_id,
				r.archivo_id,
				CONCAT(IFNULL(tpa2.nombre, ''),' ',IFNULL(tpa2.apellido_paterno, ''),	' ',	IFNULL(tpa2.apellido_materno, '')) AS usuario_aprobado,
				r.fecha_resolucion_contrato_aprobado,
				r.status,
				DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
				c.nombre_tienda,
				co.sigla, c.codigo_correlativo, tpa.correo, ar.nombre AS nombre_area,
				
				per.correo AS email_usuario_creacion_contrato,
				c.gerente_area_email,
				peg.correo AS email_del_gerente_area,
				puap.correo AS email_del_aprobante
					
				FROM cont_resolucion_contrato AS r
				INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
				INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
				INNER JOIN tbl_areas AS ar ON tpa.area_id = ar.id
				
				LEFT JOIN tbl_usuarios tu2 ON r.usuario_resolucion_contrato_aprobado_id = tu2.id
				LEFT JOIN tbl_personal_apt tpa2 ON tu2.personal_id = tpa2.id

				INNER JOIN cont_contrato AS c ON c.contrato_id = r.contrato_id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

				LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
				LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

				LEFT JOIN tbl_usuarios uab ON r.abogado_id = uab.id
				LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
				
				LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
				LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

				WHERE r.id = ".$data['resolucion_id'];

				$sel_query = $mysqli->query($query_resolucion);

				$body = "";
				$body .= '<html>';
				$correos_adicionales = [];
				$contrato_id = 0;
				$tipo_contrato_id = 0;
				while($sel = $sel_query->fetch_assoc())
				{
					$contrato_id = $sel['contrato_id']; 
					$tipo_contrato_id = $sel['tipo_contrato_id']; 
					$sigla_correlativo = $sel['sigla'];
					$codigo_correlativo = $sel['codigo_correlativo'];
					$motivo = $sel['motivo'];
					$usuario_solicitud = $sel['usuario_solicitud'];
					$fecha_solicitud = $sel['created_at'];
					$usuario_aprobado = $sel['usuario_aprobado'];
					$fecha_resolucion = $sel['fecha_resolucion'];
					$fecha_carta = $sel['fecha_carta'];
					$nombre_tienda = $sel['nombre_tienda'];

					if(!Empty($sel['correo'])){
						array_push($correos_adicionales, $sel['correo']);
					}
					
			
					if ($tipo_contrato_id == 2) { // contrato de proveedor
						if(!Empty($sel['email_usuario_creacion_contrato'])){
							array_push($correos_adicionales, $sel['email_usuario_creacion_contrato']);
						}
						if(!Empty($sel['gerente_area_email'])){
							array_push($correos_adicionales, $sel['gerente_area_email']);
						}
						if(!Empty($sel['email_del_gerente_area'])){
							array_push($correos_adicionales, $sel['email_del_gerente_area']);
						}
						if(!Empty($sel['email_del_aprobante'])){
							array_push($correos_adicionales, $sel['email_del_aprobante']);
						}
					}

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';
					
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Solicitud de Resolución Contrato de Proveedor - '.$data_seguimiento['etapa'].'</b>';
						$body .= '</th>';
					$body .= '</tr>';
					
					$body .= '</thead>';

					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
						$body .= '<td>'.$sel["nombre_area"].'</td>';
					$body .= '</tr>';
					
					
					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
						$body .= '<td>'.$sel["motivo"].'</td>';
					$body .= '</tr>';
					if($tipo_contrato_id == 1){
						$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Nombre de tienda:</b></td>';
						$body .= '<td>'.$nombre_tienda.'</td>';
						$body .= '</tr>';
					}

					if (!Empty($fecha_carta)) {
						$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Fecha de Carta:</b></td>';
						$body .= '<td>'.$fecha_carta.'</td>';
						$body .= '</tr>';
					}
					
					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Fecha de Resolución:</b></td>';
						$body .= '<td>'.$sel["fecha_resolucion"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
					$body .= '<td>'.$sel["usuario_solicitud"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
						$body .= '<td>'.$sel["created_at"].'</td>';
					$body .= '</tr>';

					
					$body .= '</table>';
					$body .= '</div>';



				}


				$body .= '<div>';
					$body .= '<br>';
				$body .= '</div>';
	
				$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
					$body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">'; 
						$body .= '<b>Ver Solicitud</b>';
					$body .= '</a>';
				$body .= '</div>';

				$nombre_tipo_contrato = "";
				switch ($tipo_contrato_id) {
					case '1': $nombre_tipo_contrato = "Contrato de Arrendamiento"; break;
					case '2': $nombre_tipo_contrato = "Contrato de Proveedor"; break;
					case '5': $nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad"; break;
					case '6': $nombre_tipo_contrato = "Contrato de Agente"; break;
					case '7': $nombre_tipo_contrato = "Contrato Interno"; break;
				}

				$text_reenvio = $reenvio ? "Reenvío - ":"";
				$titulo_email = "Gestion - Sistema Contratos - ".$text_reenvio.$data_seguimiento['etapa']." - Nueva Solicitud de Resolución de ".$nombre_tipo_contrato.": ".$nombre_tienda." Código - ".$sigla_correlativo.$codigo_correlativo;
				$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
				$lista_correos = $correos->send_email_seguimiento_proceso_proveedores($correos_adicionales);
			
				if (env('SEND_EMAIL') == 'TEST' && isset($lista_correos['cc_dev'])) { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
					$correos_produccion = implode(", ", $lista_correos['cc_dev']);

					$body .= '<div>';
						$body .= '<br>';
					$body .= '</div>';
		
					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';
					$body .= '<tr>';
						$body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Lista de Correos</b>';
						$body .= '</th>';
					$body .= '</tr>';
					$body .= '</thead>';
					$body .= '<tbody>';
					$body .= '<tr>';
						$body .= '<td>'.$correos_produccion.'</td>';
					$body .= '</tr>';
					$body .= '</tfoot>';
					$body .= '<tr>';
						$body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
					$body .= '</tr>';
					$body .= '</tfoot>';
					$body .= '</table>';
					$body .= '</div>';
				}

				$request = [
					"subject" => $titulo_email,
					"body"    => $body,
					"cc"      => $lista_correos['cc'],
					"bcc"     => $lista_correos['bcc'],
					"attach"  => [
						// $filepath . $file,
					],
				];
			}


			if (isset($request['cc'])) { // validar que tenga seteado la info de correo
				$mail = new PHPMailer(true);
				$mail->isSMTP();
				$mail->Host = "smtp.gmail.com";
				$mail->SMTPAuth = true;
		
				$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
				$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
				$mail->Port = 465;
				$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
				$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');
		
				if(isset($request["cc"]))
				{
					foreach ($request["cc"] as $cc) 
					{
						$mail->addAddress($cc);
					}
				}
		
				if(isset($request["bcc"]))
				{
					foreach ($request["bcc"] as $bcc) 
					{
						$mail->addBCC($bcc);
					}
				}
		
				$mail->isHTML(true);
				$mail->Subject  = $request["subject"];
				$mail->Body     = $request["body"];
				$mail->CharSet = 'UTF-8';
				$mail->Encoding = 'base64';
				$mail->send();
			}
			
			return [ 
				'status' => 200,
				'result' => 0,
				'message' => 'Se ha enviado el correo correctamente.'
			];
		} catch (\Exception $e) {
			return [ 
				'status' => 404,
				'result' => 0,
				'message' => $e->getMessage()
			];
		}
	}

	public function obtener_ultimo_seguimiento($data){

		include("db_connect.php");
		$query_seguimiento = "SELECT cesl.nombre as etapa 
		FROM cont_seguimiento_proceso_legal cspl 
		INNER JOIN cont_etapa_seguimiento_legal cesl ON cesl.id  = cspl.etapa_id 
		WHERE cspl.status IN (1,2) AND cspl.tipo_documento_id = ".$data['tipo_documento_id']." AND cspl.proceso_id = ".$data['proceso_id']."  AND cspl.proceso_detalle_id = ".$data['proceso_detalle_id']." 
		ORDER BY cspl.id DESC LIMIT 1";
		$list_query = $mysqli->query($query_seguimiento);
		$data_contrato = $list_query->fetch_assoc();
		if (isset($data_contrato['etapa'])) {
			return $data_contrato['etapa'];
		}
		return '';
	}

	public function reenviar_notificacion_seguimiento_proceso($data){

		try {
			$CONTRATO = 1;
			$ADENDA_DE_CONTRATO = 2;
			$RESOLUCION_DE_CONTRATO = 3;

			if ($data['tipo_documento_id'] == $CONTRATO) {
				$data_correo['contrato_id'] = $data['proceso_id'];
				$data_correo['tipo_contrato_id'] = $data['tipo_contrato_id'];
				$data_correo['seguimiento_id'] = $data['seguimiento_id'];
				$data_correo['reenvio'] = true;
				return $this->notificar_seguimiento_proceso_contrato($data_correo);
			}else if ($data['tipo_documento_id'] == $ADENDA_DE_CONTRATO){
				$data_correo['adenda_id'] = $data['proceso_id'];
				$data_correo['tipo_contrato_id'] = $data['tipo_contrato_id'];
				$data_correo['seguimiento_id'] = $data['seguimiento_id'];
				$data_correo['reenvio'] = true;
				return $this->notificar_seguimiento_proceso_adenda($data_correo);
			}else if ($data['tipo_documento_id'] == $RESOLUCION_DE_CONTRATO){
				$data_correo['resolucion_id'] = $data['proceso_id'];
				$data_correo['tipo_contrato_id'] = $data['tipo_contrato_id'];
				$data_correo['seguimiento_id'] = $data['seguimiento_id'];
				$data_correo['reenvio'] = true;
				return $this->notificar_seguimiento_proceso_resolucion($data_correo);
			}
		} catch (\Exception $e) {
			//throw $th;
		}
		
	}

}


?>