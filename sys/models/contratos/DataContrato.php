<?php
class DataContrato extends Model
{
    public function ObtenerTiposDeContratos()
    {
        try {
            $db_query = $this->db->prepare('SELECT id, nombre FROM cont_tipo_contrato WHERE status = 1');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function ObtenerTipoCorreoMetodo()
    {
        try {
            $db_query = $this->db->prepare('SELECT id, nombre FROM cont_mantenimiento_correo_tipo WHERE status = 1');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtereAreaId()
    {
        try {
            $db_query = $this->db->prepare('SELECT *FROM tbl_areas ta WHERE ta.estado = 1');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function ObtenerTodosContratos()
    {
        try {
            $db_query = $this->db->prepare('SELECT contrato_id FROM cont_contrato');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    function obtener_empresas()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_query = $this->db->prepare(
                'SELECT  id, nombre
                        FROM
                            tbl_razon_social
                        WHERE status = 1 
                        ORDER BY nombre ASC'
            );
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_personal_responsable()
    {
        try {
            $db_query = $this->db->prepare("
            SELECT u.id, concat( IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre
            FROM tbl_personal_apt p
            INNER JOIN tbl_usuarios u ON p.id = u.personal_id
            WHERE p.area_id = 21 AND p.cargo_id = 4 AND p.estado = 1 AND u.estado = 1
	        ORDER BY nombre ASC
            ");
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_propietarios($tipo_busqueda, $ids, $nombre_o_numdocu)
    {
        try {
            $query = "";
            $query = "SELECT 
                        id, 
                        tipo_persona_id, 
                        nombre, 
                        tipo_docu_identidad_id, 
                        num_docu, 
                        num_ruc,
                        direccion, 
                        representante_legal, 
                        num_partida_registral, 
                        contacto_nombre, 
                        contacto_telefono, 
                        contacto_email 
                    FROM cont_persona 
                    WHERE status = 1
                    ";

            if ($tipo_busqueda == '1') {
                $query .= " AND nombre like '%" . $nombre_o_numdocu . "%' ";
            } else if ($tipo_busqueda == '2') {
                $query .= " AND (num_docu like '%" . $nombre_o_numdocu . "%' OR num_ruc like '%" . $nombre_o_numdocu . "%')";
            }

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion ' . $query;
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_ocupantes($tipo_busqueda, $ids, $nombre_o_numdocu)
    {
        try {
            $query = "";
            $query = "SELECT 
                        id,  
                        nombre, 
                        tipo_docu_identidad_id, 
                        num_docu, 
                        num_ruc,
                        direccion, 
                        representante_legal, 
                        num_partida_registral
                    FROM tbl_razon_social 
                    WHERE status = 1
                    ";

            if ($tipo_busqueda == '1') {
                $query .= " AND nombre like '%" . $nombre_o_numdocu . "%' ";
            } else if ($tipo_busqueda == '2') {
                $query .= " AND (num_docu like '%" . $nombre_o_numdocu . "%' OR num_ruc like '%" . $nombre_o_numdocu . "%')";
            }

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion ' . $query;
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_persona()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_query = $this->db->prepare(
                'SELECT id, nombre
                 FROM cont_tipo_persona
                WHERE estado = 1
            ORDER BY id ASC'
            );

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_doc_identidad()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_query = $this->db->prepare(
                'SELECT 
                    id, 
                    nombre
                FROM 
                    cont_tipo_docu_identidad
                WHERE 
                    estado = 1
                ORDER BY id ASC'
            );

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_departartamentos()
    {

        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_query = $this->db->prepare(
                "SELECT nombre, cod_depa AS id 
                 FROM tbl_ubigeo
                 WHERE cod_prov = '00' AND cod_dist = '00' AND estado = 1 
                 ORDER BY nombre ASC"
            );

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_provincias_segun_departamento($departamento_id)
    {

        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_query = $this->db->prepare(
                "SELECT nombre,cod_prov AS id
                 FROM tbl_ubigeo
                 WHERE cod_depa = " . $departamento_id . " AND cod_dist = '00' AND cod_prov != '00' AND estado = '1'
                 ORDER BY nombre ASC"
            );

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_distritos_segun_provincia($provincia_id, $departamento_id)
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = "SELECT nombre,cod_dist AS id";
            $query .= " FROM tbl_ubigeo";
            $query .= " WHERE cod_prov = '" . $provincia_id . "' ";
            $query .= " AND cod_depa = '" . $departamento_id . "' ";
            $query .= " AND cod_dist != '00' ";
            $query .= " AND estado = '1'";
            $query .= " ORDER BY nombre ASC";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_compromiso_pago()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
            FROM cont_tipo_pago_servicio
            WHERE estado = 1
            ORDER BY nombre ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_compromiso_pago_arbitrio()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
            FROM cont_tipo_pago_arbitrios
            WHERE estado = 1
            ORDER BY nombre ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_moneda_de_contrato()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, CONCAT(nombre,' (',simbolo,')') AS nombre
            FROM tbl_moneda
            WHERE estado = 1 AND id IN(1,2)
            ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_pago_renta()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
            FROM cont_tipo_pago_renta
            WHERE status = 1
            ORDER BY id ASC";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_tipo_afectacion_igv()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
            FROM cont_tipo_afectacion_igv
            WHERE status = 1
            ORDER BY nombre ASC";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_tipo_venta()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
            FROM cont_tipo_venta
            WHERE status = 1
            ORDER BY nombre ASC";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_tipo_adelantos()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
            FROM cont_tipo_adelanto
            WHERE status = 1
            ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_impuesto_a_la_renta()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT 
                id, nombre
            FROM
                cont_tipo_impuesto_a_la_renta
            WHERE
                status = 1
            ORDER BY id ASC";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_periodo_de_gracia()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
            FROM cont_tipo_periodo_de_gracia
            WHERE status = 1
            ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_tipo_periodo()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
                        FROM cont_tipo_plazo
                        WHERE status = 1";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_tipo_incrementos()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
                    FROM cont_tipo_incremento
                    WHERE status = 1
                    ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_pago_incrementos()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
                        FROM cont_tipo_pago_incrementos
                        WHERE estado = 1
                        ORDER BY nombre ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_continuidad_pago()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
                    FROM cont_tipo_continuidad_pago
                    WHERE estado = 1
                    ORDER BY nombre ASC;";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_tipo_anios_incrementos()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query =  "SELECT id, nombre
                        FROM cont_tipo_anios_incrementos
                        WHERE status = 1
                        ORDER BY id ASC";

            $db_query = $this->db->prepare($query);

            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_inflacion()
    {
        try {

            $query =  "SELECT id, nombre
                        FROM cont_tipo_inflacion
                        WHERE status = 1
                        ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_periodicidad()
    {
        try {

            $query =  "SELECT id, nombre 
                        FROM cont_tipo_periodicidad
                        WHERE status = 1";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function obtener_tipo_anio_mes()
    {
        try {

            $query =  "SELECT id, nombre 
                        FROM cont_periodo
                        ORDER BY id ASC";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_aplicacion()
    {
        try {

            $query =  "SELECT id, nombre 
                        FROM cont_tipo_aplicacion
                        ORDER BY id ASC";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_meses()
    {
        try {

            $query =  "SELECT id, nombre 
                        FROM tbl_meses
                        ORDER BY id ASC";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function obtener_mondeda_por_id($moneda_id)
    {
        try {

            $query =  "SELECT 
                            nombre,
                            simbolo
                        FROM 
                            tbl_moneda
                        WHERE 
                            id = " . $moneda_id;

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_forma_pago()
    {
        try {

            $query =  "SELECT id, nombre
                        FROM cont_forma_pago
                        WHERE estado = 1
                        ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_bancos()
    {
        try {

            $query =  "SELECT id, nombre
                        FROM tbl_bancos
                        WHERE estado = 1
                        ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_tipo_monto_a_depositar()
    {
        try {

            $query =  "SELECT id, nombre
                        FROM cont_tipo_monto_a_depositar
                        WHERE status = 1
                        ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function obtener_tipo_cuota_extraordinaria()
    {
        try {

            $query =  "SELECT id, nombre
                        FROM cont_tipo_cuota_extraordinaria
                        WHERE status = 1
                        ORDER BY id ASC;";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function obtener_tipo_anexos($data)
    {
        try {

            $query =  "SELECT 
                tipo_archivo_id as id, nombre_tipo_archivo as nombre
            FROM
                cont_tipo_archivos
            WHERE
                status = 1
                AND tipo_contrato_id = " . $data['tipo_contrato_id'] . "
                AND tipo_archivo_id NOT IN (16 , 17, 19)
            ORDER BY nombre_tipo_archivo ASC";

            $db_query = $this->db->prepare($query);
            $this->db->exec("SET NAMES 'utf8'");
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['query'] = $query;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }




    function sec_contrato_nuevo_de_meses_a_anios_y_meses($meses)
    {
        if ($meses < 12) {
            $anio_y_meses = $meses . ' meses';
        } else {
            $anio = intval($meses / 12);
            $meses_restantes = $meses % 12;

            if ($anio == 0) {
                $anio = '';
            } else if ($anio == 1) {
                $anio = $anio . ' año';
            } else if ($anio > 1) {
                $anio = $anio . ' años';
            }

            if ($meses_restantes == 0) {
                $meses_restantes = '';
            } else if ($meses_restantes == 1) {
                $meses_restantes = ' y ' . $meses_restantes . ' mes';
            } else if ($meses_restantes > 1) {
                $meses_restantes = ' y ' . $meses_restantes . ' meses';
            }

            return $anio . $meses_restantes;
        }
    }




    public function obtener_contratos_internos($data)
    {


        try {
            $db_query = $this->db->prepare('SELECT contrato_id FROM cont_contrato');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $data;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function actualizar_suministros($data)
    {


        try {
            $db_query = $this->db->prepare('SELECT contrato_id FROM cont_contrato');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $data;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function obtener_meses_adelantos($data)
    {


        try {
            $db_query = $this->db->prepare('SELECT id, nombre FROM cont_tipo_mes_adelanto WHERE status = 1 ORDER BY orden ASC');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function obtener_abogados($data)
    {


        try {
            $db_query = $this->db->prepare("SELECT  u.id ,
            CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
        FROM 
            tbl_usuarios u
            INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
            INNER JOIN tbl_cargos AS c ON c.id = p.cargo_id
        WHERE 
            u.estado = 1
            AND p.estado = 1
            AND p.area_id IN (33)
        ORDER BY 
            p.nombre ASC,
            p.apellido_paterno ASC");
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    function obtener_aprobador()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_query = $this->db->prepare(
                "SELECT 
                u.id, 
                CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
            FROM 
                tbl_usuarios u
                INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
                INNER JOIN cont_usuarios_directores ud ON u.id = ud.user_id
            WHERE 
                u.estado = 1
                AND ud.status = 1
            ORDER BY 
                p.nombre ASC,
                p.apellido_paterno ASC"
            );
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    function obtener_cargo_aprobador()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_query = $this->db->prepare(
                "SELECT c.id, c.nombre FROM tbl_cargos AS c WHERE c.estado = 1 ORDER BY c.nombre ASC"
            );
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
}
