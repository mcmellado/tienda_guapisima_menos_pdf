<?php session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Listado de usuarios</title>
</head>

<body>
    <?php
    require '../../vendor/autoload.php';

    if ($usuario = \App\Tablas\Usuario::logueado()) {
        if (!$usuario->es_admin()) {
            $_SESSION['error'] = 'Acceso no autorizado.';
            return volver();
        }
    } else {
        return redirigir_login();
    }

    $pdo = conectar();

    $id = obtener_post('id');

    if (isset($id)) {
        $sent = $pdo->prepare('UPDATE articulos
                                  SET visible = NOT visible
                                WHERE id = :id');
        $sent->execute([':id' => $id]);
    }

    $_SESSION['exito'] = 'El artículo se ha modificado correctamente.';
    volver_admin();

    ?>
