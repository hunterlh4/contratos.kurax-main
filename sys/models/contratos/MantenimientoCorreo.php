<?php
class MantenimientoCorreo extends Model {

    private $data;
    private $id;
  
    public function obtener_usuarios($data) {
        $query = '';
        try {
            
            $query = "SELECT u.id, CONCAT( p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,''), ' - ',IFNULL(p.correo,''),'  cargo: ',IFNULL(tbl_cargos.nombre,''),'  zona: ',IFNULL(tbl_zonas.nombre,'')) AS text  
            FROM tbl_personal_apt AS p 
            INNER JOIN tbl_usuarios AS u ON u.personal_id = p.id
            LEFT JOIN
			tbl_cargos ON p.cargo_id=tbl_cargos.id	 
			LEFT JOIN
			tbl_zonas ON p.zona_id=tbl_zonas.id
            WHERE p.estado = 1 AND u.estado = 1 
            -- AND (p.cargo_id = 3 OR p.cargo_id = 16 OR p.cargo_id = 26) -- gerente | jefe | director
            AND p.correo IS NOT NULL
            AND CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,'')) LIKE '%".$data['search']."%'
            GROUP BY u.id
            ORDER BY CONCAT(p.nombre, IFNULL(p.apellido_paterno, ' '), IFNULL(p.apellido_materno,' '), ' - ',IFNULL(p.correo,'')) ASC LIMIT 10";
     
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

    public function obtener_area_grupo_id($data) {
        $query = '';
        try {
            
            $query = "SELECT
            t2.id,
            t2.nombre text
            FROM cont_notificacion_contrato_correos t1
            INNER JOIN tbl_areas t2 ON t2.id = t1.area_id
            AND t2.nombre LIKE '%".$data['search']."%'
            GROUP BY t1.area_id
            ORDER BY t2.nombre ASC LIMIT 10";
     
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


    public function listar($data) {
        $query = '';
        try {
            
            $query = "SELECT mc.id, tc.nombre, 
                    CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,'')) AS personal,
                    u.usuario,
                    p.correo,
                    mc.status
                    FROM cont_mantenimiento_correos AS mc
                    INNER JOIN cont_mantenimiento_correo_metodo AS mt ON mt.id = mc.metodo_id
                    INNER JOIN cont_mantenimiento_correo_tipo AS tc ON tc.id = mt.tipo_metodo_id
                    INNER JOIN tbl_usuarios AS u ON u.id = mc.usuario_id 
                    INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id AND p.estado = 1
                    WHERE mc.status <> 9
                    AND mc.metodo_id = ".$data['metodo_id']."
                    ORDER BY mc.id DESC";
     
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

    public function listar_por_area($data) {
        $query = '';

        try {
            $query = "SELECT
            t1.id,
            t2.nombre,
            t1.status,
            t1.area_id
            FROM cont_mantenimiento_correos_por_area t1
            INNER JOIN tbl_areas t2 ON t2.id = t1.area_id
            WHERE t1.status <> 9
            AND t1.metodo_id = ".$data['metodo_id']."
            ORDER BY t1.id DESC;";
    
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

    public function listar_por_usuarios_por_area($data) {
        $query = '';

        try {
            $query = "SELECT 
            t1.id,
            CONCAT(t3.nombre,' ',t3.apellido_paterno) personal,
            DATE_FORMAT(t1.created_at, '%d-%m-%Y %H:%i:%s') AS created_at,
            t1.status,
            t4.nombre area
            FROM cont_notificacion_contrato_correos t1
            INNER JOIN tbl_usuarios t2 ON t2.id = t1.user_id 
            INNER JOIN tbl_personal_apt t3 ON t3.id = t2.personal_id
            LEFT JOIN tbl_areas t4 ON t4.id = t1.area_id
            WHERE t1.area_id = ".$data['area_id']."
            ORDER BY t1.id DESC;";
    
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


    public function registrar($data) {
        $query = '';
        try {


            $query = "SELECT *
                    FROM cont_mantenimiento_correos AS mc
                    WHERE mc.status <> 9
                    AND mc.metodo_id = ".$data['metodo_id']."
                    AND mc.usuario_id = ".$data['usuario_id']."
                    ORDER BY mc.id DESC";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (count($resultado) > 0) {
                $result['status'] = 404;
                $result['result'] = $resultado[0];
                $result['query'] = $query;
                $result['message'] = 'El usuario ingresado ya se encuentra registrado.';
                return $result;
            }

            
            $query = "INSERT INTO cont_mantenimiento_correos (
                usuario_id,
                metodo_id,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['usuario_id'] . ",
                '" . $data['metodo_id'] . "',
                " . $data['status']. ",
                " . $data["user_created_id"]. ",
                '" . $data["created_at"]. "'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado correctamente el registro';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_area($data) {
        $query = '';
        try {


            $query = "SELECT *
                    FROM cont_mantenimiento_correos_por_area AS mc
                    WHERE mc.status <> 9
                    AND mc.metodo_id = ".$data['metodo_id']."
                    AND mc.area_id = ".$data['area_id']."
                    ORDER BY mc.id DESC";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (count($resultado) > 0) {
                $result['status'] = 404;
                $result['result'] = $resultado[0];
                $result['query'] = $query;
                $result['message'] = 'El área ingresada ya se encuentra registrada.';
                return $result;
            }

            
            $query = "INSERT INTO cont_mantenimiento_correos_por_area (
                area_id,
                metodo_id,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['area_id'] . ",
                '" . $data['metodo_id'] . "',
                " . $data['status']. ",
                " . $data["user_created_id"]. ",
                '" . $data["created_at"]. "'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado correctamente el registro';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function cambiar_estado($data) {
        $query = '';
        try {
            
            $query = "UPDATE cont_mantenimiento_correos 
                SET  status = ".$data['estado'].",
                user_updated_id = ".$data['user_updated_id'].",
                updated_at = '".$data['updated_at']."'
            WHERE id = ".$data['id'];

            $message = "";
            if($data['estado'] == 1){
                $message = "Se ha activado correctamente el registro";
            }else{
                $message = "Se ha inactivado correctamente el registro";
            }
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
        
            $result['status'] = 200;
            $result['result'] = $data;
            $result['message'] = $message;
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function cambiar_estado_por_area($data) {
        $query = '';
        try {
            
            $query = "UPDATE cont_mantenimiento_correos_por_area 
                SET  status = ".$data['estado'].",
                user_updated_id = ".$data['user_updated_id'].",
                updated_at = '".$data['updated_at']."'
            WHERE id = ".$data['id'];

            $message = "";
            if($data['estado'] == 1){
                $message = "Se ha activado correctamente el registro";
            }else{
                $message = "Se ha inactivado correctamente el registro";
            }
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
        
            $result['status'] = 200;
            $result['result'] = $data;
            $result['message'] = $message;
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function eliminar_por_id($data) {
        $query = '';
        try {
            
            $query = "UPDATE cont_mantenimiento_correos 
                SET  status = '9',
                user_updated_id = ".$data['user_updated_id'].",
                updated_at = '".$data['updated_at']."'
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

    public function eliminar_por_area_id($data) {
        $query = '';
        try {
            
            $query = "UPDATE cont_mantenimiento_correos_por_area 
                SET  status = '9',
                user_updated_id = ".$data['user_updated_id'].",
                updated_at = '".$data['updated_at']."'
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