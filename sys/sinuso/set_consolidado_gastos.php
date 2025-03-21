<?php  
date_default_timezone_set("America/Lima");

include("db_connect.php");
include("sys_login.php");
include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);
require_once '../phpexcel/classes/PHPExcel.php';

if (isset($_POST["accion"]) && $_POST["accion"] === "cargar_consolidado_gastos") {
    $usuario_id = $login ? $login['id'] : null;
    $response = [];

    if ((int)$usuario_id > 0) {
        $fecha = date("Y-m-d H:i:s");

        // Obtener el mapeo de cc_id a id de la tabla tbl_locales
        $locales_query = "SELECT id, cc_id FROM tbl_locales WHERE cc_id !='' AND cc_id IS NOT NULL";
        $result = $mysqli->query($locales_query);
        $locales = [];
        while ($row = $result->fetch_assoc()) {
            $locales[$row["cc_id"]] = $row["id"];
        }

        $conceptos_query = "SELECT id, REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(codigo_importar, 'Á', 'A'), 'É', 'E'), 'Í', 'I'), 'Ó', 'O'), 'Ú', 'U'), 'Ü', 'U') AS codigo_importar FROM tbl_gastos_conceptos WHERE estado =1";
        $result = $mysqli->query($conceptos_query);
        $conceptos = [];
        while ($row = $result->fetch_assoc()) {
            $conceptos[$row["codigo_importar"]] = $row["id"];
        }

        // Verificar si se ha cargado un archivo
        if (isset($_FILES['excelFile'])) {
            $excelFile = $_FILES['excelFile']['tmp_name'];

            // Cargar y procesar el archivo Excel
            try {
                // Crear una instancia de PHPExcel
                $objPHPExcel = PHPExcel_IOFactory::load($excelFile);
                $sheet = $objPHPExcel->getActiveSheet();

                // Obtener los títulos de las columnas de la primera fila
                $firstRow = $sheet->getRowIterator(1)->current();
                $columnNames = [];
                foreach ($firstRow->getCellIterator() as $cellIndex => $cell) {
                    $columnNames[$cellIndex] = $cell->getValue();
                }

                // Verificar la presencia de las columnas requeridas
                $requiredColumns = ['CECO', 'YEAR', 'NUM_MES', 'AGRUPADOR_RETAIL_1', 'TOTAL'];
                foreach ($requiredColumns as $requiredColumn) {
                    if (!in_array($requiredColumn, $columnNames)) {
                        $response["http_code"] = 400;
                        $response["error"] = "La columna '$requiredColumn' es requerida en el archivo Excel.";
                        echo json_encode($response);
                        exit();
                    }
                }

                // Inicializar transacción
                $mysqli->autocommit(false);

                $registrosEncontrados = false;

                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $rowData = [];

                    foreach ($row->getCellIterator() as $cellIndex => $cell) {
                        if ($rowIndex === 1) {
                            // Esta es la primera fila, que contiene los títulos de las columnas
                            $columnNames[$cellIndex] = $cell->getValue();
                        } else {
                            // Estas son las filas de datos
                            $rowData[$columnNames[$cellIndex]] = $cell->getValue();
                        }
                    }

                    if ($rowIndex > 1) {
                        $ceco = mysqli_real_escape_string($mysqli, $rowData['CECO']);
                        $anio = mysqli_real_escape_string($mysqli, $rowData['YEAR']);
                        $mes = mysqli_real_escape_string($mysqli, $rowData['NUM_MES']);
                        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
                        $fecha = $anio."-".$mes."-01";
                        $conceptoNombreBase = mysqli_real_escape_string($mysqli, $rowData['AGRUPADOR_RETAIL_1']);
                        $conceptoNombreBase1 = quitarTildes($conceptoNombreBase);
                        $conceptoNombre = strtoupper($conceptoNombreBase1);
                        $monto = mysqli_real_escape_string($mysqli, $rowData['TOTAL']);

                        // Verificar si los valores no son vacíos antes de realizar la inserción
                        if ($ceco != '' && $conceptoNombre != '' && $monto != '' && $monto != 0) {
                            // Obtener el local_id correspondiente al cc_id del archivo Excel y verificar si los valores no son vacíos antes de realizar la inserción
                            if ($ceco != '' && $conceptoNombre != '' && isset($locales[$ceco]) && isset($conceptos[$conceptoNombre]) && $anio != '' && $mes != '' && $mes != '00') {
                                $localId = $locales[$ceco];
                                $conceptoId = $conceptos[$conceptoNombre];

                                $sql = "INSERT INTO tbl_gastos (mes, anio, fecha, local_id, gastos_conceptos_id, monto, estado, user_created_id, created_at)
                                        VALUES ('$mes', '$anio', '$fecha', $localId, $conceptoId, $monto, 1, $usuario_id, NOW())";
                                $result = mysqli_query($mysqli, $sql);

                                if (!$result) {
                                    throw new Exception("Error en la consulta SQL: " . mysqli_error($mysqli));
                                }
                            } else {
                                if (!isset($conceptos[$conceptoNombre])) {
                                    $variable = 'concepto';
                                    $columna = 'AGRUPADOR_RETAIL_1';
                                } elseif (!isset($locales[$ceco])) {
                                    $variable = 'local';
                                    $columna = 'CECO';
                                } elseif (!isset($anio) || $anio == '' || $anio == '0000') {
                                    $variable = 'año';
                                    $columna = 'YEAR';
                                } elseif (!isset($mes) || $mes == '' || $mes == '00') {
                                    $variable = 'mes';
                                    $columna = 'NUM_MES';
                                }
                                $response["http_code"] = 400;
                                $response["error"] = "No se encontró un '$variable' correspondiente para la columna '$columna' en la fila $rowIndex del archivo Excel.";
                                echo json_encode($response);
                                exit();
                            }
                        }
                    }
                }

                // Commit de la transacción si todo ha ido bien
                $mysqli->commit();
                $response["http_code"] = 200;
                $response["status"] = "Datos obtenidos de gestión";
            } catch (Exception $e) {
                // Rollback en caso de error
                $mysqli->rollback();
                $response["http_code"] = 400;
                $response["error"] = $e->getMessage();
            } finally {
                // Finalizar la transacción
                $mysqli->autocommit(true);
            }
        } else {
            $response["http_code"] = 400;
            $response["error"] = "No se proporcionó un archivo Excel";
        }
    } else {
        $response["http_code"] = 400;
        $response["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($response);
    exit();
}


function quitarTildes($texto) {
    $buscar = array('Á', 'É', 'Í', 'Ó', 'Ú', 'á', 'é', 'í', 'ó', 'ú', 'Ü', 'ü');
    $reemplazar = array('A', 'E', 'I', 'O', 'U', 'a', 'e', 'i', 'o', 'u', 'U', 'u');
    return str_replace($buscar, $reemplazar, $texto);
}
?>

