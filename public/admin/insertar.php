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

    $codigo = obtener_post("codigo");
    $descripcion = obtener_post("descripcion");
    $precio = obtener_post("precio");
    $id_categoria = obtener_post("id_categoria");
    $stock = obtener_post("stock");
    $guardar = obtener_post("guardar");
    $fecha = obtener_post("fecha");
    $errores = ['codigo' => [], 'descripcion' => [], 'precio' => [], 'stock' => [], 'fecha' => []];


    if (isset($guardar)) {

    if(isset($codigo, $descripcion, $precio, $stock)) {
       

        if ($codigo == '') {
            $errores['codigo'][] = 'El código es obligatorio.';
        }

        if (!preg_match('/^[0-9]*$/', $codigo)) {
            $errores['codigo'][] = 'Sólo puede ingresar datos de tipo numérico';
        }
        
        if (!preg_match('/[0-9]{13}/', $codigo)) {
            $errores['codigo'][] = 'Sólo puede ingresar un código de 13 numeros';
        }

        if ($precio == '') {
            $errores['precio'][] = 'El precio es obligatorio.';
        }
        
        if (!preg_match('/^[0-9]*$/', $precio)) {
            $errores['precio'][] = 'Sólo puede ingresar datos de tipo numérico';
        }
        
        if ($stock == '') {
            $errores['stock'][] = 'El stock es obligatorio.';
        }

        if (!preg_match('/^[0-9]*$/', $stock)) {
            $errores['stock'][] = 'Sólo puede ingresar datos de tipo numérico';
        }
        
        if ($descripcion == '') {
            $errores['descripcion'][] = 'La descripción es obligatoria.';
        }

        if (!preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $fecha)) {
            $errores['fecha'][] = 'Sólo puedes ingresar fechas tipo dd/mm/yyyy';
            $fecha = date($fecha);
        }


        $vacio = true;

        foreach ($errores as $err) {
            var_dump($errores);
            if (!empty($err)) {
                $vacio = false;
                break;
            }
        }
    }

        if($vacio) {

            $pdo = conectar();

            $sent = $pdo->prepare("INSERT INTO articulos (codigo, descripcion, precio, stock, id_categoria, visible, fecha)
            VALUES (:codigo, :descripcion, :precio, :stock, :id_categoria, false, :fecha)");

            var_dump($sent);

                $sent->execute([':codigo' => $codigo,
                                ':descripcion' => $descripcion,
                                ':precio' => $precio,
                                ':stock' => $stock,
                                ':id_categoria' => $id_categoria,
                                ':fecha' => $fecha]);



                $_SESSION['exito'] = 'El artículo se ha creado correctamente.';
                volver_admin();

        }
    }

    ?>

    <form method="POST" action="">
    <h2> Introduce el código del articulo nuevo: </h2>
            <label>
                <input type="text" name="codigo" value="<?= $codigo ?>">
                <?php foreach ($errores['codigo'] as $err): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-bold">¡Error!</span> <?= $err ?></p>
                <?php endforeach ?>
            </label>
        <h2> Introduce nueva descripción: </h2>
            <label>
                <input type="text" name="descripcion" value="<?= $descripcion ?>">
                <?php foreach ($errores['descripcion'] as $err): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-bold">¡Error!</span> <?= $err ?></p>
                <?php endforeach ?>
            </label>
         <h2> Introduce nuevo precio: </h2>
            <label>
                <input type="text" name="precio" value="<?= $precio ?>">
                <?php foreach ($errores['precio'] as $err): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-bold">¡Error!</span> <?= $err ?></p>
                <?php endforeach ?>
            </label>
        <h2> Introduce el stock: </h2>
            <label>
                <input type="text" name="stock" value="<?= $stock ?>">
                <?php foreach ($errores['stock'] as $err): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-bold">¡Error!</span> <?= $err ?></p>
                <?php endforeach ?>
            </label>
        <h2> Introduce la fecha del articulo: </h2>
            <label>
                <input type="text" name="fecha" value="<?= $fecha ?>">
                <?php foreach ($errores['fecha'] as $err): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-bold">¡Error!</span> <?= $err ?></p>
                <?php endforeach ?>
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


