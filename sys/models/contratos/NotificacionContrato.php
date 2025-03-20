<?php
class NotificacionContrato extends Model {

    private $data;
    private $id;

    public function registrar($data) {
        $query = '';
        try {
    
            $usuario_id = $data['usuario_id'];
            $area_id = $data['area_id'];
            $id_user_created = $data['id_user_created'];

            $query = "SELECT id FROM cont_notificacion_contrato_correos WHERE user_id = :user_id AND status <> 9";
            $db_query = $this->db->prepare($query);
            $db_query->execute(['user_id' => $usuario_id]);
            $existingRecord = $db_query->fetch(PDO::FETCH_ASSOC);
    
            if ($existingRecord) {
                $result['status'] = 400;
                $result['message'] = 'El usuario ya está registrado';
                return $result;
            } else {
                $query = "INSERT INTO cont_notificacion_contrato_correos (user_id, area_id, status, user_created_id) 
                          VALUES (:user_id, :area_id, 1, :user_created_id)";
                $db_query = $this->db->prepare($query);
                $db_query->execute(['user_id' => $usuario_id, 'area_id' => $area_id,'user_created_id' => $id_user_created]);
    
                $result['status'] = 200;
                $result['message'] = 'Registro guardado correctamente';
                return $result;
            }

        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'Ha ocurrido un error con la consulta SQL: ' . $th->getMessage();
            return $result;
        }
    }
  
    public function listar_notificacion_contrato() {
        $query = '';
        try {
            $query = "SELECT
            t1.id,
            CONCAT(t3.nombre,' ',t3.apellido_paterno) personal,
            CONCAT(t5.nombre,' ',t5.apellido_paterno) creador,
            CONCAT(t8.nombre,' ',t8.apellido_paterno) modificador,
            DATE_FORMAT(t1.created_at, '%d-%m-%Y %H:%i:%s') AS created_at,
            DATE_FORMAT(t1.modified_at, '%d-%m-%Y %H:%i:%s') AS modified_at,
            t6.nombre area,
            t1.status
            FROM cont_notificacion_contrato_correos t1
            INNER JOIN tbl_usuarios t2 ON t2.id = t1.user_id
            INNER JOIN tbl_personal_apt t3 ON t3.id = t2.personal_id
            INNER JOIN tbl_usuarios t4 ON t4.id = t1.user_created_id
            INNER JOIN tbl_personal_apt t5 ON t5.id = t4.personal_id
            LEFT JOIN tbl_areas t6 ON t6.id = t1.area_id
            LEFT JOIN tbl_usuarios t7 ON t7.id = t1.user_updated_id
            LEFT JOIN tbl_personal_apt t8 ON t8.id = t7.personal_id
            WHERE t1.status <> 9;";
    
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
    
            $result['sEcho'] = 1;
            $result['iTotalRecords'] = count($resultado);
            $result['iTotalDisplayRecords'] = count($resultado);
            $result['aaData'] = $resultado;
            return $result;
        } catch (\Throwable $th) {
            $result['sEcho'] = 1;
            $result['iTotalRecords'] = 0;
            $result['iTotalDisplayRecords'] = 0;
            $result['aaData'] = [];
            $result['error'] = $th->getMessage();
            $result['query'] = $query;
            return $result;
        }

    }

    public function cambiar_estado($data) {
        $query = '';
        try {
            // Obtener el valor anterior del estado
            $query = "SELECT status FROM cont_notificacion_contrato_correos WHERE id = :id";
            $db_query = $this->db->prepare($query);
            $db_query->execute(['id' => $data['id']]);
            $estado_anterior = $db_query->fetchColumn();
    
            // Actualizar el estado en la tabla principal
            $query = "UPDATE cont_notificacion_contrato_correos 
                      SET status = :estado, 
                          user_updated_id = :user_updated_id 
                      WHERE id = :id";        
    
            $db_query = $this->db->prepare($query);
            $db_query->execute([
                'estado' => $data['estado'],
                'user_updated_id' => $data['user_updated_id'],
                'id' => $data['id']
            ]);
    
            // Insertar el cambio en la tabla de historial
            $query = "INSERT INTO cont_notificacion_contrato_correos_historial_cambios 
                      (cont_notificacion_contrato_correos_id, valor_nuevo_estado, valor_anterior_estado, status, user_created_id, created_at) 
                      VALUES (:cont_notificacion_contrato_correos_id, :valor_nuevo_estado, :valor_anterior_estado, 1, :user_created_id, NOW())";
            
            $db_query = $this->db->prepare($query);
            $db_query->execute([
                'cont_notificacion_contrato_correos_id' => $data['id'],
                'valor_nuevo_estado' => $data['estado'],
                'valor_anterior_estado' => $estado_anterior,
                'user_created_id' => $data['user_updated_id']
            ]);
    
            // Mensaje de éxito
            if ($data['estado'] == 1) {
                $result['message'] = 'Se ha activado correctamente el registro';
            } else {
                $result['message'] = 'Se ha inactivado correctamente el registro';
            }
    
            $result['status'] = 200;
            $result['result'] = $data;
            return $result;
    
        } catch (\Throwable $th) {
            $result['status'] = 400;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'Ha ocurrido un error con la consulta, comuníquese con soporte.';
            return $result;
        }
    }

    public function obtener_usuarios($data) {
        $query = '';
        
        try {
            $query = "SELECT u.id, CONCAT( p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,''), ' - ',IFNULL(p.correo,''),' - Área: ',IFNULL(ta.nombre,'')) AS text  
            FROM tbl_personal_apt AS p 
            INNER JOIN tbl_usuarios AS u ON u.personal_id = p.id
            LEFT JOIN
			tbl_cargos ON p.cargo_id=tbl_cargos.id	 
			LEFT JOIN
			tbl_zonas ON p.zona_id=tbl_zonas.id
            LEFT JOIN tbl_areas ta ON ta.id = p.area_id 
            WHERE p.estado = 1 AND u.estado = 1 
            AND p.correo IS NOT NULL
            AND p.area_id IN (2,3,33)
            AND CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,'')) LIKE '%".$data['search']."%'
            GROUP BY u.id
            ORDER BY CONCAT(p.nombre, IFNULL(p.apellido_paterno, ' '), IFNULL(p.apellido_materno,' '), ' - ',IFNULL(p.correo,'')) ASC LIMIT 100";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function obtener_areas($data) {
        $query = '';
        
        try {
            $query = "SELECT id, nombre text
            FROM tbl_areas
            WHERE estado = 1
            AND id IN (2,3,33)
            AND nombre LIKE '%".$data['search']."%'
            ORDER BY nombre ASC;";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }
    }
    
    public function obtener_por_id($id) {
        try {
            $query = "SELECT * FROM cont_notificacion_contrato_correos WHERE id = :id";
            $db_query = $this->db->prepare($query);
            $db_query->execute(['id' => $id]);
    
            return $db_query->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    public function modificar($data) {
        try {
            $query = "UPDATE cont_notificacion_contrato_correos 
                      SET area_id = :area_id, user_updated_id = :id_user_updated
                      WHERE id = :id";
            $db_query = $this->db->prepare($query);
            $db_query->execute([
                'area_id' => $data['area_id'],
                'id_user_updated' => $data['id_user_updated'],
                'id' => $data['id']
            ]);

    
            return ['status' => 200, 'message' => 'Registro actualizado correctamente.'];
        } catch (\Throwable $th) {
            return ['status' => 400, 'message' => 'No se pudo actualizar el registro.'];
        }
    }

    public function obtener_area_por_usuario($data) {
        $usuario_id = $data['usuario_id'];
        try {
            $query = "SELECT p.area_id AS id, a.nombre AS nombre
                      FROM tbl_personal_apt p
                      INNER JOIN tbl_areas a ON p.area_id = a.id
                      WHERE p.id = (
                          SELECT personal_id
                          FROM tbl_usuarios
                          WHERE id = :usuario_id
                      )";
            $db_query = $this->db->prepare($query);
            $db_query->execute(['usuario_id' => $usuario_id]);
            $resultado = $db_query->fetch(PDO::FETCH_ASSOC);
    
            if ($resultado) {
                return ['status' => 200, 'result' => $resultado];
            } else {
                return ['status' => 404, 'message' => 'Área no encontrada para el usuario.'];
            }
        } catch (\Throwable $th) {
            return ['status' => 500, 'message' => 'Error en la consulta.', 'error' => $th->getMessage()];
        }
    }

    public function listar_historial($data) {
        $query = '';
        try {

            $query = "SELECT
            t1.id,
            t1.cont_notificacion_contrato_correos_id,
            CASE 
                WHEN t1.valor_nuevo_estado = '1' THEN 'Activo'
                WHEN t1.valor_nuevo_estado = '0' THEN 'Inactivo'
                ELSE 'Desconocido'
            END AS nuevo_estado,
            CASE 
                WHEN t1.valor_anterior_estado = '1' THEN 'Activo'
                WHEN t1.valor_anterior_estado = '0' THEN 'Inactivo'
                ELSE 'Desconocido'
            END AS anterior_estado,
            CONCAT(t3.nombre,' ',t3.apellido_paterno) usuario,
            DATE_FORMAT(t1.created_at, '%m-%d-%Y %H:%i:%s') AS created_at
            FROM cont_notificacion_contrato_correos_historial_cambios t1
            INNER JOIN tbl_usuarios t2 ON t2.id = t1.user_created_id
            INNER JOIN tbl_personal_apt t3 ON t3.id = t2.personal_id
            WHERE t1.cont_notificacion_contrato_correos_id =".$data[0]['id']."
            ORDER BY t1.created_at DESC;";
    
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
    
            $result['sEcho'] = 1;
            $result['iTotalRecords'] = count($resultado);
            $result['iTotalDisplayRecords'] = count($resultado);
            $result['aaData'] = $resultado;
            return $result;
        } catch (\Throwable $th) {
            $result['sEcho'] = 1;
            $result['iTotalRecords'] = 0;
            $result['iTotalDisplayRecords'] = 0;
            $result['aaData'] = [];
            $result['error'] = $th->getMessage();
            $result['query'] = $query;
            return $result;
        }
    }

    public function eliminar_notificacion_por_area_id($data) {
        $query = '';
        try {
            
            $query = "UPDATE cont_notificacion_contrato_correos 
                SET  status = '9',
                user_updated_id = ".$data['user_updated_id'].",
                modified_at = '".$data['updated_at']."'
            WHERE id = ".$data['id'];
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
        
            $result['status'] = 200;
            $result['result'] = $data;
            $result['message'] = 'Se ha eliminado correctamente el registro';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }
}