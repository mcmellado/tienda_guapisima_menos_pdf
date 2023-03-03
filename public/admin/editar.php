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

    $id = obtener_get('id');

    if (!isset($id)) {
        return volver();
    }

    $set= [];
    $execute = [];

    $descripcion = obtener_post("descripcion");
    $precio = obtener_post("precio");
    $id_categoria = obtener_post("id_categoria");
    $stock = obtener_post("stock");
    $guardar = obtener_post("guardar");


    if (isset($descripcion) && $descripcion != '') {
        $set[] = 'descripcion = :descripcion';
        $execute[':descripcion'] = $descripcion;
    }

    if (isset($precio) && $precio != '') {
        $set[] = 'precio = :precio';
        $execute[':precio'] = $precio;
    }

    if (isset($id_categoria) && $id_categoria != '') {
        $set[] = 'id_categoria = :id_categoria';
        $execute[':id_categoria'] = $id_categoria;
    }

    if (isset($stock) && $stock != '') {
        $set[] = 'stock = :stock';
        $execute[':stock'] = $stock;
    }

    $set = !empty($set) ?  'SET ' . implode(', ', $set) : '';
    
    if (isset($guardar)) {
        
        $pdo = conectar();
        
        $sent = $pdo->prepare("UPDATE articulos 
                                $set
                                WHERE id = $id");

            $sent->execute($execute);
            
            $_SESSION['exito'] = 'El artículo se ha modificado correctamente.';
            volver_admin();
    }
    ?>

    <form method="POST" action="">
        <h2> Introduce nueva descripción: </h2>
            <label>
                <input type="text" name="descripcion" value="<?= $descripcion ?>">
            </label>
         <h2> Introduce nuevo precio: </h2>
            <label>
                <input type="text" name="precio" value="<?= $precio ?>">
            </label>
        <h2> Introduce el stock: </h2>
            <label>
                <input type="text" name="stock" value="<?= $stock ?>">
            </label>
        <h2> Introduce la categoría del articulo: </h2>
            <label>
            <select id="id_categoria" name="id_categoria">
                <?php
                $pdo = conectar(); 
                $categorias = $pdo->query("SELECT * FROM categorias"); ?>
                <?php foreach($categorias as $cat): ?>  
                    <option name="id_categoria" value="<?= $cat['id'] ?>"> <?= hh($cat['categoria']) ?>  </option>
                <?php endforeach ?>
            </select>
            <br>
            </label>
            <label>
                <input type="submit" value="guardar" name="guardar" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"></input>
            </label>
    </form>

