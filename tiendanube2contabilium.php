<?php

define('TIENDANUBE_MAX_ROWS', 1000);
define('TIENDANUBE_MAX_CHARS_DESCRIPTION', 499);

function getTiendaNubeHeaders()
{
    if (($handle = @fopen(__DIR__ . "/data/contabilium_template.csv", "r")) === false) {
        return false;
    }

    $tiendanubeHeaders = fgetcsv($handle, 1000, ";");
    fclose($handle);

    if ($tiendanubeHeaders === false) {
        return false;
    }

    return $tiendanubeHeaders;
}

function getProductosTiendaNube()
{
    $productos = array();
    if (($handle = @fopen(__DIR__ . "/data/tiendanube_productos.csv", "r")) === false) {
        return false;
    }

    $lastName = '';
    $lastDescripcion = '';
    $firstRow = true;
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if ($firstRow) {
            $firstRow = false;
            continue;
        }

        // Limito los caracteres de la descripción
        $data[20] = substr(strip_tags($data[20]), 0, TIENDANUBE_MAX_CHARS_DESCRIPTION);

        if (empty($data[1])) {
            $data[1] = $lastName;
            $data[20] = $lastDescripcion;
        } else {
            $lastName = $data[1];
            $lastDescripcion = $data[20];
        }

        $productos[] = convertHeaders($data);
    }

    fclose($handle);

    return $productos;
}

function convertHeaders($tiendaNube)
{
    $output = [];

    $output[0] = $tiendaNube[1]; // Nombre
    $output[1] = $tiendaNube[16]; // codigo sku
    $output[2] = $tiendaNube[20]; // Descripcion
    $output[3] = 0; // stock
    $output[4] = 3; // stock minimo
    $output[5] = str_replace('.', ',', str_replace(',', '', $tiendaNube[9])); // precio unitario
    $output[6] = ''; // observaciones
    $output[7] = 0; // rentabilidad
    $output[8] = 21; // iva
    $output[9] = 0; // costo interno s/iva
    $output[10] = ''; // CodigoProveedor
    $output[11] = 'Principal'; // deposito
    $output[12] = ''; // codigo de barras
    $output[13] = 'General'; // Rubro
    $output[14] = ''; // SubRubro
    $output[15] = ''; // Tipo
    $output[16] = 'NO'; // PrecioAutomatico

    return $output;
}

function generateContabiliumCSV($filename, $headers, $productos)
{
    if (file_exists($filename) && !@unlink($filename)) {
        return false;
    }

    $fp = @fopen($filename, 'w');
    if ($fp === false) {
        return false;
    }

    fputcsv($fp, $headers, ';');
    foreach ($productos as $fields) {
        fputcsv($fp, $fields, ';');
    }

    fclose($fp);

    return true;
}

$tiendanubeHeaders = getTiendaNubeHeaders();
if (!$tiendanubeHeaders) {
    echo 'No existe, no se pudo leer o es inválido contabilium_template.csv' . PHP_EOL;
    exit;
}

$tiendanubeProductos = getProductosTiendaNube();
if (!$tiendanubeProductos) {
    echo 'No existe o no se pudo leer tiendanube_productos.csv' . PHP_EOL;
    exit;
}

$chunksProductos = array_chunk($tiendanubeProductos, TIENDANUBE_MAX_ROWS);

foreach ($chunksProductos as $i => $productos) {
    $file = 'contabilium_productos_' . ($i + 1) . '.csv';
    $output = __DIR__ . '/data/' . $file;
    if (generateContabiliumCSV($output, $tiendanubeHeaders, $productos)) {
        echo 'Archivo generado: ' . $file . PHP_EOL;
    } else {
        echo '(!) Error al generar el CSV: ' . $file . PHP_EOL;
    }
}
