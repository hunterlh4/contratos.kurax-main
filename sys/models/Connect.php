<?php
date_default_timezone_set('America/Lima');
class Database
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        $dsn = 'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_DATABASE');

        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        try {
            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->exec("set names utf8mb4");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8mb4");
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}

class Model
{
    protected $db;

    protected $id_usuario;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get_usuario_id()
    {
        include './../../db_connect.php';
        include './../../sys_login.php';
        $usuario_id = $login ? $login['id'] : null;

        return $usuario_id;
    }


    public function get_usuario_locales()
    {
        include './../../db_connect.php';
        include './../../sys_login.php';
        $usuario_locales = $login ? $login['usuario_locales'] : false;

        return $usuario_locales;
    }

    public function get_usuario_permisos()
    {
        include './../../db_connect.php';
        include './../../sys_login.php';

        $usuario_permisos = $usuario_permisos ? $usuario_permisos : [];
        return $usuario_permisos;
    }


    public function get_menu_id($data)
    {
        $query = '';
        try {

            $where_sub_sec_id = isset($data['sub_sec_id']) && !empty($data['sub_sec_id']) ? " AND sub_sec_id = '" . $data['sub_sec_id'] . "'" : "";
            $query = "SELECT id FROM tbl_menu_sistemas 
            WHERE sec_id = '" . $data['sec_id'] . "' 
            " . $where_sub_sec_id . " LIMIT 1";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetch(PDO::FETCH_ASSOC);
            $resultado = isset($resultado['id']) ? $resultado['id'] : 0;
            $result['status'] = 200;
            $result['result'] = (int) $resultado;
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

    public function replace_invalid_caracters($cadena)
    {
        $cadena = trim($cadena);
        $cadena = str_replace("'", "", $cadena);
        $cadena = str_replace("\\", "", $cadena);
        $cadena = str_replace("^", "", $cadena);
        $cadena = str_replace("`", "", $cadena);
        $cadena = str_replace("|", "", $cadena);
        $cadena = str_replace("~", "", $cadena);
        $cadena = str_replace("¢", "", $cadena);
        $cadena = str_replace("£", "", $cadena);
        $cadena = str_replace("¤", "", $cadena);
        $cadena = str_replace("¥", "", $cadena);
        $cadena = str_replace("¦", "", $cadena);
        $cadena = str_replace("§", "", $cadena);
        $cadena = str_replace("¨", "", $cadena);
        $cadena = str_replace("ª", "", $cadena);
        $cadena = str_replace("«", "", $cadena);
        $cadena = str_replace("¬", "", $cadena);
        $cadena = str_replace("®", "", $cadena);
        $cadena = str_replace("°", "", $cadena);
        $cadena = str_replace("±", "", $cadena);
        $cadena = str_replace("²", "", $cadena);
        $cadena = str_replace("³", "", $cadena);
        $cadena = str_replace("´", "", $cadena);
        $cadena = str_replace("µ", "", $cadena);
        $cadena = str_replace("¶", "", $cadena);
        $cadena = str_replace("'", "", $cadena);

        return $cadena;
    }
}
