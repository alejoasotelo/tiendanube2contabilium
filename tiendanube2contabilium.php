<?php

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

function getProductosTiendaNube()
{
    $productos = array();
    if (($handle = fopen(__DIR__ . "/data/tiendanube_productos.csv", "r")) !== FALSE) {
        $lastName = '';
        $lastDescripcion = '';
        $i = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if ($i === 0) {
                $i++;
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
    }

    return $productos;
}

function getTiendaNubeHeaders()
{
    if (($handle = fopen(__DIR__ . "/data/contabilium_template.csv", "r")) === false) {
        echo 'No existe contabilium_template.csv' . PHP_EOL;
        exit;
    }

    $tiendanubeHeaders = fgetcsv($handle, 1000, ";");
    if ($tiendanubeHeaders === false) {
        exit;
    }

    fclose($handle);

    return $tiendanubeHeaders;
}

$output = __DIR__ . '/data/contabilium_productos.csv';
$tiendanubeHeaders = getTiendaNubeHeaders();
$tiendanubeProductos = getProductosTiendaNube();

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