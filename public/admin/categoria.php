<?php
session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <script>
        function cambiar(el, id) {
            el.preventDefault();
            const oculto = document.getElementById('oculto');
            oculto.setAttribute('value', id);
        }
    </script>
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

    $categoria = obtener_post('categoria');
    $guardar = obtener_post("guardar");
    $errores = ['categoria' => []];

    if(isset($guardar)) {

    
        $pdo = conectar();
    
        $sent = $pdo->query("SELECT * from categorias");
        
        foreach($sent as $array) {
           if($array['categoria'] == $categoria) {
            $errores['categoria'][] = 'Ya existe esa categoría.';
           } else {
            $vacio = true;
           }
        }
        
        foreach ($errores as $err) {
            if (!empty($err)) {
                $vacio = false;
                break;
            }
        }
        
    
        if($vacio) {
            $pdo = conectar();
    
            $insertar = $pdo->prepare("INSERT INTO categorias (categoria) VALUES (:categoria)");
            $insertar->execute([':categoria' => $categoria]);

            var_dump($insertar);
    
            $_SESSION['exito'] = 'Has introducido una nueva categoría';
            volver_admin();

        }
    }

    ?>

<form method="POST" action="">
    <h2> Introduce el nombre de la nueva categoría: </h2>
            <label>
                <input type="text" name="categoria" value="<?= $categoria ?>">
            <?php foreach ($errores['categoria'] as $err): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-bold">¡Error!</span> <?= $err ?></p>
            <?php endforeach ?>
            <label>
                <input type="submit" value="guardar" name="guardar" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"></input>
            </label>
            </label>