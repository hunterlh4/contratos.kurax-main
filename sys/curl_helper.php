<?php

/**
 * @throws JsonException
 */
function curl_helper($url, $request = [], $headers = [], $json = true, $method = 'POST'): array
{
    $data_result = [
        'error' => true
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    if ($request && count($request)) {
        $data = $json ? json_encode($request, JSON_THROW_ON_ERROR) : $request;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $exec = curl_exec($ch);
    $data_result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result = null;

    if ($json && $exec) {
        $result = json_decode($exec, true, 512, JSON_THROW_ON_ERROR);
    }

    if (!$result) {
        $data_result['message'] = 'La API de Autoervicios está fuera de servicio.';
    } else if (curl_errno($ch)) {
        $data_result['message'] = curl_errno($ch);
    } else if (!$result['status']) {
        if (is_array(($result))) {
            foreach ($result as $arr) {
                if (is_array($arr)) {
                    foreach ($arr as $message) {
                        $data_result['message'] = $message;
                    }
                }
            }
        }
    } else if ($result['status'] === 'error') {
        $data_result['message'] = $result['message'];
    } else {
        $data_result['http_code'] = 200;
        $data_result['error'] = false;
        $data_result['message'] = 'Ok.';
        $data_result = array_merge($data_result, $result);
    }

    curl_close($ch);

    return $data_result;
}
