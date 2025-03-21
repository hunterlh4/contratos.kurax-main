<?php
include("db_connect.php");
include("sys_login.php");

function resizeImage($resourceType, $image_width, $image_height)
{
    $imagelayer = [];

    if ($image_width < 1920 && $image_height < 1080) {

        //mini
        $resizewidth_mini = 100;
        $resizeheight_mini = 100;

        $imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);

        //mini
        $imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
        imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
    } else {

        $ratio = $image_width / $image_height;
        $escalaW = 1920 / $image_width;
        $escalaH = 1080 / $image_height;

        if ($ratio > 1) {
            //es mas ancha $resizeheight
            $resizewidth = $image_width * $escalaW;
            $resizeheight = $image_height * $escalaW;

        } else {
            //es mas larga
            $resizeheight = $image_height * $escalaH;
            $resizewidth = $image_width * $escalaH;
        }

        //mini
        $resizewidth_mini = 100;
        $resizeheight_mini = 100;

        $imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);

        //mini
        $imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
        imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);

    }

    return $imagelayer;
}

if (isset($_POST['get_img'])) {
    $photo_type = "tipo = 'foto'";
    if (isset($_POST['type'])) {
        if ($_POST['type'] === "markt") {
            $photo_type = "(tipo = 'foto' OR tipo = 'foto_markt')";
        } else {
            $photo_type = "tipo = 'foto_" . $_POST['type'] . "'";
        }
    }

    $idJackpot = $_POST['get_img']['id'];
    $consulta = [];
    $retorna = [];
    $query = "SELECT archivo from tbl_archivos WHERE tabla ='tbl_registro_premios' AND item_id = " . $idJackpot . " AND " . $photo_type . " ORDER BY fecha DESC";
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta[] = $r;
    foreach ($consulta as $key => $value) {
        $retorna[$key] = $value;
    }
    print_r(json_encode($retorna));
};

if (isset($_POST['upload_status'])) {
    $photo_type = "tipo = 'foto'";
    if (isset($_POST['type'])) {
        if ($_POST['type'] === "markt") {
            $photo_type = "(tipo = 'foto' OR tipo = 'foto_markt')";
        } else {
            $photo_type = "tipo = 'foto_" . $_POST['type'] . "'";
        }
    }

    global $mysqli;
    $idTicket = $_POST['upload_status']['id'];
    $query = "SELECT count(id) AS cant from tbl_archivos WHERE item_id =" . $idTicket . " AND tabla ='tbl_registro_premios' AND " . $photo_type;
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta = $r;
    print_r(json_encode($consulta));
}

if (isset($_POST['upload_status_firma'])) {
    global $mysqli;
    $id = $_POST['upload_status_firma']['id'];
    $query = "SELECT count(id) AS cant from tbl_archivos WHERE item_id =" . $id . "  AND tabla ='tbl_registro_premios' AND tipo='signature' ";
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta = $r;
    print_r(json_encode($consulta));
}

if (isset($_POST["sec_registro_fotos_jackpot"])) {

    $id_jackpot = $_POST['id-Jackpot'];
    $result = [];

    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
        $path = "/var/www/html/files_bucket/registros/premios/";
        $file = [];
        $imageLayer = [];
        if (!is_dir($path)) mkdir($path, 0777, true);

        /* resize de la imagen */
        $imageProcess = 0;

        $filename = $_FILES['files']['tmp_name'][$i];
        print_r($filename);
        $sourceProperties = getimagesize($filename);
        $resizeFileName = $id_jackpot . "_" . date('YmdHis');
        $size = $_FILES['files']['size'][$i];

        $fileExt = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);
        $uploadImageType = $sourceProperties[2];
        $sourceImageWith = $sourceProperties[0];
        $sourceImageHeight = $sourceProperties[1];

        switch ($uploadImageType) {
            case IMAGETYPE_JPEG:
                $resourceType = imagecreatefromjpeg($filename);

                $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                $file[0] = imagejpeg($imageLayer[0], $path . $resizeFileName . $i . '.' . $fileExt);
                $file[1] = imagejpeg($imageLayer[1], $path . "min_" . $resizeFileName . $i . '.' . $fileExt);
                break;
            case IMAGETYPE_PNG:
                $resourceType = imagecreatefrompng($filename);

                $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                $file[0] = imagepng($imageLayer[0], $path . $resizeFileName . $i . '.' . $fileExt);
                $file[1] = imagepng($imageLayer[1], $path . "min_" . $resizeFileName . $i . '.' . $fileExt);
                break;
            case IMAGETYPE_GIF:
                $resourceType = imagecreatefromgif($filename);

                $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                $file[0] = imagegif($imageLayer[0], $path . $resizeFileName . $i . '.' . $fileExt);
                $file[1] = imagegif($imageLayer[1], $path . "min_" . $resizeFileName . $i . '.' . $fileExt);
                break;

            default:
                $imageProcess = 0;
                break;
        }

        move_uploaded_file($file[0], $path . $resizeFileName . $i . "." . $fileExt);
        move_uploaded_file($file[1], $path . $resizeFileName . $i . "." . $fileExt);
        $imageProcess = 1;

        /* resize de la imagen esto es una prueba */
        $photo_type = '';
        if (isset($_POST['photoType'])) {
            $photo_type = '_' . $_POST['photoType'];
        }

        $mysqli->query("
        INSERT INTO tbl_archivos(
            tabla,
            item_id,
            tipo,
            ext,
            size,
            nombre,
            descripcion,
            archivo,
            fecha,
            orden,
            estado
            )
            VALUES(
                'tbl_registro_premios',
                '" . $id_jackpot . "',
                'foto" . $photo_type . "',
                '" . $fileExt . "',
                '" . $size . "',
                '" . $resizeFileName . "',
                'foto',
                '" . $resizeFileName . $i . "." . $fileExt . "',
                '" . date('Y-m-d H:i:s') . "',
                '0',
                '1'
                )
                ");

        $insert_id = mysqli_insert_id($mysqli);

        $filepath = $path . $resizeFileName . "." . $fileExt;
        $result[] = [
            'id' => $insert_id,
            'filename' => $resizeFileName . $i . "." . $fileExt,
            'filepath' => $filepath
        ];
    }
    echo json_encode($result);

}


?>
