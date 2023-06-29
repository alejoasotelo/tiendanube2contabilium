<?php

function getTiendaNubeHeaders()
{
    if (($handle = fopen(__DIR__ . "/data/contabilium_template.csv", "r")) === false) {
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
    if (($handle = fopen(__DIR__ . "/data/tiendanube_productos.csv", "r")) === false) {
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

$output = __DIR__ . '/data/contabilium_productos.csv';

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

if (file_exists($output)) {
    unlink($output);
}

$fp = fopen($output, 'w');
if ($fp === false) {
    exit;
}

fputcsv($fp, $tiendanubeHeaders, ';');
foreach ($tiendanubeProductos as $fields) {
    fputcsv($fp, $fields, ';');
}
