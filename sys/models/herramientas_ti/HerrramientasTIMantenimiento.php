<?php
class HerrramientasTIMantenimiento extends Model {

    private $data;
    private $id;
  
    public function obtener_modulos($data) {
        $query = '';
        try {
            
            $query = "SELECT hm.id as value, hm.nombre as text
            FROM tbl_herramientas_ti_modulo AS hm
            WHERE hm.status = 1";
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

    public function obtener_tablas($data) {
        $query = '';
        try {
            
            $query = "SHOW TABLES";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $data_Table = [];
            foreach ($resultado as $table) {
                array_push($data_Table, array(
                    'value' => $table["Tables_in_".env('DB_DATABASE')],
                    'text' => $table["Tables_in_".env('DB_DATABASE')],
                ));
            }

            $result['status'] = 200;
            $result['result'] = $data_Table;
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

    public function obtener_columnas_de_tabla($data) {
        $query = '';
        try {
            
            $query = "DESCRIBE ".$data['tabla'];
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $data_columns = [];
            foreach ($resultado as $field) {
                array_push($data_columns, array(
                    'value' => $field['Field'],
                    'text' => $field['Field'],
                ));
            }
            $result['status'] = 200;
            $result['result'] = $data_columns;
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

    public function registrar_proceso($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_herramientas_ti_proceso (
                    modulo_id,
                    nombre,
                    tabla,
                    filtro_fecha,
                    status,
                    user_created_id,
                    created_at
            )VALUES(
                '".$data['modulo_id']."',
                '".$data['nombre']."',
                '".$data['tabla']."',
                '".$data['filtro_fecha']."',
                '".$data['status']."',
                '".$data['user_created_id']."',
                '".$data['created_at']."'
            )";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $result['status'] = 200;
            $result['result'] = 0;
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

    public function listar_procesos($data) {
        $query = '';
        try {
            
            $query = "SELECT hp.id, hp.nombre, hp.tabla, hp.filtro_fecha, hp.status, hm.nombre AS modulo
            FROM tbl_herramientas_ti_proceso AS hp 
            INNER JOIN tbl_herramientas_ti_modulo AS hm ON hm.id = hp.modulo_id
            WHERE hp.status <> 9 ORDER BY hp.id DESC";
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

    public function obtener_proceso_por_id($data) {
        $query = '';
        try {
            
            $query = "SELECT hp.id, hp.nombre, hp.tabla, hp.filtro_fecha, hp.status, hp.modulo_id, hm.nombre AS modulo
            FROM tbl_herramientas_ti_proceso AS hp 
            INNER JOIN tbl_herramientas_ti_modulo AS hm ON hm.id = hp.modulo_id
            WHERE hp.id = ".$data['id'];
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $resultado = $resultado[0];
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

    public function editar_proceso($data) {
        $query = '';
        try {
            
            $query = "UPDATE tbl_herramientas_ti_proceso SET 
                    nombre = '".$data['nombre']."',
                    modulo_id = '".$data['modulo_id']."',
                    filtro_fecha = '".$data['filtro_fecha']."',
                    status = '".$data['status']."',
                    user_updated_id = '".$data['user_updated_id']."',
                    updated_at = '".$data['updated_at']."'
                WHERE id = '".$data['id']."'
            ";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $result['status'] = 200;
            $result['result'] = $data['status'];
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

    public function eliminar_proceso($data) {
        $query = '';
        try {
            
            $query = "UPDATE tbl_herramientas_ti_proceso SET 
                    status = '".$data['status']."',
                    user_updated_id = '".$data['user_updated_id']."',
                    updated_at = '".$data['updated_at']."'
                WHERE id = '".$data['id']."'
            ";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $result['status'] = 200;
            $result['result'] = $data['status'];
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



    public function registrar_proceso_detalle($data) {
        $query = '';
        try {
            
            $query = "SELECT * FROM tbl_herramientas_ti_proceso_detalle WHERE proceso_id = '".$data['proceso_id']."' AND columna = '".$data['columna']."' AND status = 1";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            if (count($resultado) > 0) {
                $result['status'] = 400;
                $result['result'] = 0;
                $result['message'] = 'La columna ya fue agregada anteriormente';
                return $result;
            }

            $query = "INSERT INTO tbl_herramientas_ti_proceso_detalle (
                    proceso_id,
                    columna,
                    status,
                    user_created_id,
                    created_at
            )VALUES(
                '".$data['proceso_id']."',
                '".$data['columna']."',
                '".$data['status']."',
                '".$data['user_created_id']."',
                '".$data['created_at']."'
            )";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $result['status'] = 200;
            $result['result'] = 0;
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

    public function listar_procesos_detalle($data) {
        $query = '';
        try {
            //status = 9 elimineado
            $query = "SELECT * FROM tbl_herramientas_ti_proceso_detalle WHERE proceso_id = '".$data['proceso_id']."' AND status <> 9 ORDER BY id DESC";
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


    public function actualizar_estado_proceso_detalle($data) {
        $query = '';
        try {
            
            $query = "UPDATE tbl_herramientas_ti_proceso_detalle SET 
                    status = '".$data['status']."',
                    user_updated_id = '".$data['user_updated_id']."',
                    updated_at = '".$data['updated_at']."'
                WHERE id = '".$data['id']."'
            ";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $result['status'] = 200;
            $result['result'] = $data['status'];
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

}