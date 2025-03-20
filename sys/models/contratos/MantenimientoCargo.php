<?php
class MantenimientoCargo extends Model {

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
    public function obtener_cargos($data) {
        $query = '';
        try {
            
            $query = "	SELECT  
                CONCAT(tpa.nombre, ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno,'')) AS usuario,
            
                tbl_areas.nombre as area, tbl_areas.id as area_id,
                tc.nombre as text ,tc.id  
                -- Eliminé tc.id de la selección
                FROM tbl_personal_apt tpa 
                INNER JOIN tbl_usuarios tu ON tu.personal_id = tpa.id 
                LEFT JOIN tbl_cargos tc ON tc.id = tpa.cargo_id
                LEFT JOIN tbl_areas ON tpa.area_id = tbl_areas.id 
                LEFT JOIN tbl_razon_social trs ON trs.id = tpa.razon_social_id 
                WHERE tbl_areas.estado = 1 and tc.estado = 1 and tu.estado = 1 and tpa.estado = 1
                -- and tbl_areas.id = 1
                and tc.id   NOT IN (5,8,9,10,11,12,20,21,23,24,28,30,33)
                and tbl_areas.id = ".$data["area_id"]."
                GROUP BY tc.nombre, tbl_areas.id
                ORDER BY tbl_areas.nombre;";
     
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
            
            $query = "SELECT tbl_areas.id , tbl_areas.nombre AS text
            FROM  
			tbl_areas 
            WHERE  tbl_areas.estado = 1
            AND tbl_areas.nombre LIKE '%".$data['search']."%'
            GROUP BY tbl_areas.id";
     
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
    public function obtener_usuarios_cargo($data) {
        $query = '';
        try {
            
            $query = "SELECT tu.id as usuario_id,
            CONCAT( tpa.nombre,' ',tu.id, ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno,''), ' - ',IFNULL(tpa.correo,''),'  cargo: ',IFNULL(tc.nombre,'')) AS text
                ,tpa.correo ,tc.nombre as cargo, tbl_areas.nombre as area ,tbl_areas.id  
                FROM tbl_personal_apt tpa 
                INNER JOIN tbl_usuarios tu ON tu.personal_id = tpa.id 
                LEFT JOIN tbl_cargos tc ON tc.id = tpa.cargo_id
                LEFT JOIN tbl_areas ON tpa.area_id = tbl_areas.id 
                WHERE 
                tpa.estado = 1 AND tpa.estado = 1 AND tu.estado =1 
                 AND tbl_areas.id = ".$data['area_id']."
                 AND tc.id  = ".$data['cargo_id'];
     
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
            
            $query = "SELECT  tca.* , tc2.nombre  as cargo_nombre , tu.usuario,
            CONCAT(tpa.nombre, ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno,'')) AS usuario_nombres_completos  from cont_cargos_areas tca 
                                LEFT JOIN tbl_cargos tc2 ON tc2.id = tca.cargo_id 
                                LEFT JOIN tbl_usuarios tu ON tu.id =  tca.user_created_id
                                LEFT JOIN tbl_personal_apt tpa ON  tpa.id= tu.personal_id
                                WHERE tca.estado = 1 
                                AND tca.area_id  =".$data['metodo_id'];
     
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
    public function listar_historial($data) {
        $query = '';
        try {
            
            $query = "SELECT  tca.* , tc2.nombre  as cargo_nombre , cah.created_at AS fecha_actualizacion,
            
            IF(cah.estado = 1, 'Activo', 'Inactivo') AS estado_historial,tu.usuario AS usuario_name,
                        CONCAT(tpa.nombre, ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno,'')) AS usuario  
                        FROM cont_cargos_areas_historial cah
                        LEFT JOIN cont_cargos_areas tca ON cah.cargo_area_id= tca.id
                                            LEFT JOIN tbl_cargos tc2 ON tc2.id = tca.cargo_id 
                                            LEFT JOIN tbl_usuarios tu ON tu.id =  cah.user_created_id
                                            LEFT JOIN tbl_personal_apt tpa ON  tpa.id= tu.personal_id
                                WHERE 
                                 tca.area_id  =".$data['id_area']." ORDER BY cah.created_at DESC";
            // var_dump($query);exit;
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

    public function registrar($data) {
        $query = '';
        try {

            // se valida que esyte en INACTIVO (0) activo para registrar 
            $query = "SELECT  *
                    FROM cont_cargos_areas tca 
                    WHERE tca.estado = 1 
                                AND tca.area_id  = ".$data["area_id"]." 
                                AND tca.cargo_id =".$data["cargo_id"];
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (count($resultado) > 0) {
                $result['status'] = 404;
                $result['result'] = $resultado[0];
                $result['query'] = $query;
                $result['message'] = 'El Cargo ya esta registrado como esta ACTIVO.';
                return $result;
            }

            
            $query = "INSERT INTO cont_cargos_areas (
                area_id,
                cargo_id,
                estado,
                user_created_id,
                created_at
            )VALUES(
                " . $data['area_id'] . ",
                '" . $data['cargo_id'] . "',
                " . $data['status']. ",
                " . $data["user_created_id"]. ",
                '" . $data["created_at"]. "'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

        // insertamos en la tabla para el historial
            $query_historial = "INSERT INTO cont_cargos_areas_historial (
                cargo_area_id,
                estado,
                user_created_id,
                created_at
            )VALUES(
            
                '" . $id . "',
                1,
                '" . $data["user_created_id"]. "',
                '".$data["created_at"]."')";
     
            $db_query_historial = $this->db->prepare($query_historial);
            $db_query_historial->execute();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado correctamente el registro';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (cont_cargos_areas) '.$th->getMessage();
            return $result;
        }

    }

    public function cambiar_estado($data) {
        $query = '';
        try {
            
            $query = " UPDATE cont_cargos_areas  
                        SET estado = ".$data["estado"]." ,
                        user_updated_id = ".$data["user_updated_id"]." ,
                        created_at  = NOW()
                        WHERE id = ".$data['id'];
                
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $query_historial = "INSERT INTO cont_cargos_areas_historial (
                cargo_area_id,
                estado,
                user_created_id,
                created_at
            )VALUES(
             
                '" . $data['id'] . "',
                0,
               
                '" . $data["user_updated_id"]. "',
                '".$data['updated_at']."')";
            // var_dump($query_historial);exit();
            $db_query2 = $this->db->prepare($query_historial);
            $db_query2->execute();
        
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

    public function listar_personal_area_cargo($data) {
        $query = '';
        try {
            
            $query = "SELECT u.id, 
            u.usuario, 
            CONCAT(IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,''),' ', IFNULL(p.apellido_materno,'') ) AS personal,
            p.correo,
            a.nombre AS nombre_area,
            c.nombre AS nombre_cargo,
            p.fecha_ingreso_laboral
            FROM tbl_usuarios AS u 
            INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id
            INNER JOIN cont_cargos_areas AS ca ON ca.area_id = p.area_id AND ca.cargo_id = p.cargo_id
            INNER JOIN tbl_areas AS a ON a.id = p.area_id
            INNER JOIN tbl_cargos AS c ON c.id = p.cargo_id
            WHERE u.estado = 1 AND p.estado = 1 AND ca.estado = 1
            AND ca.area_id = ".$data['area_id']."
            ORDER BY ca.cargo_id DESC";
            
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



    public function registrar_correo_area($data) {
        $query = '';
        try {

            $query = "SELECT cca.id
            FROM cont_correos_area cca 
            WHERE cca.status <> 9 AND cca.area_id = ".$data['area_id']." AND cca.usuario_id = ".$data['usuario_id'];
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            if (count($resultado) > 0) {
                $result['status'] = 404;
                $result['result'] = 0;
                $result['message'] = 'Ya se encuentra registrado el personal ingresado.';
                return $result; 
            }

            $query = "INSERT INTO cont_correos_area (
                area_id,
                usuario_id,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['area_id'] . ",
                '" . $data['usuario_id'] . "',
                " . $data['status']. ",
                " . $data["user_created_id"]. ",
                '" . $data["created_at"]. "'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado correctamente el personal';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error. Intentalo mas tarde';
            return $result;
        }
    }

    public function listar_correo_area($data) {
        $query = '';
        try {
            
            $query = "SELECT cca.id,
            tu.usuario, 
            CONCAT(IFNULL(tpa.nombre,''),' ',IFNULL(tpa.apellido_paterno,''),' ', IFNULL(tpa.apellido_materno,'') ) AS personal,
            tpa.correo,
            cca.status,
            tc.nombre  as cargo
            FROM cont_correos_area cca 
            INNER JOIN tbl_usuarios tu ON tu.id  = cca.usuario_id 
            INNER JOIN tbl_personal_apt tpa ON tpa.id  = tu.personal_id 
            INNER JOIN tbl_cargos tc ON tc.id = tpa.cargo_id 
            WHERE tu.estado = 1 AND tpa.estado = 1 AND cca.status <> 9 AND cca.area_id = ".$data['area_id']."
            ORDER BY tu.id  DESC";
            
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
            $result['message'] = 'A ocurrido un error. Intentalo mas tarde';
            return $result;
        }

    }

    public function modificar_correo_area($data) {
        $query = '';
        try {

            $query = "UPDATE cont_correos_area SET
                    status = '".$data['status']."',
                    user_updated_id = '".$data['user_updated_id']."',
                    updated_at = '".$data['updated_at']."'
                WHERE id = ".$data['correo_id'];
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
         

            $result['status'] = 200;
            $result['result'] = $data['status'];
            $result['message'] = 'Se ha modificado el estado correctamente';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error. Intentalo mas tarde';
            return $result;
        }
    }
}