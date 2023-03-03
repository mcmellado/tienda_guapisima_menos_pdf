<?php

use App\Tablas\Articulo;
use App\Tablas\Categoria;
use App\Tablas\Usuario;

 session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Portal</title>
</head>

<body>
    <?php
    require '../vendor/autoload.php';


    $carrito = unserialize(carrito());

    $categoria_id = obtener_get('cat');
    $desde = obtener_get("desde");
    $hasta = obtener_get("hasta");
    $ordenar = obtener_get("ordenar");


    
    $pdo = conectar();
    $where = [];
    $execute = [];
    
    if (isset($categoria_id) && $categoria_id != '') {
        $where[] = 'c.id = :id';
        $execute[':id'] = $categoria_id;
    }

    if (isset($desde) && $desde != '') {
        $where[] = 'precio >= :desde';
        $execute[':desde'] = $desde;
    }

    if (isset($hasta) && $hasta != '') {
        $where[] = 'precio <= :hasta';
        $execute[':hasta'] = $hasta;
    }

    if (isset($ordenar) && $ordenar != '') {
        if($ordenar == 'fecha') {
            $orderby[] = 'fecha';
        } 
        if ($ordenar == 'nombre') {
            $orderby[] = 'descripcion';
        }
    }



 
    $where = !empty($where) ?  'WHERE ' . implode(' AND ', $where) .  " AND visible = true" : "WHERE visible = true";
    $orderby = !empty($orderby) ? 'ORDER BY ' . implode($orderby) : " ";
    $sent = $pdo->query("SELECT * FROM categorias ORDER BY id");



    // $sent->execute($execute);

    $cats = $sent->fetchAll();

    foreach($cats as $cat){
        $categ[$cat['id']] = new Categoria($cat);
    }


    ?>
    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <?php require '../src/_alerts.php' ?>

        <?php if ($usuario = \App\Tablas\Usuario::logueado()):
              if ($usuario->es_admin()): ?> 
            <a href="/admin/index.php"><button class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 mr-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900">Administrar admin</button></a>
        <?php endif ?>
        <?php endif ?>
        <br>
        <h2> Listado de categorías: </h2>
        <br>
        <form action="" method="GET">
            <select id="cat" name="cat">
                <?php $categorias = $pdo->query("SELECT * FROM categorias"); ?>
                <option name="valor" value=<?= "" ?>> Todas las categorias </option>
                <?php foreach($categ as $categoria): ?>  
                    <option name="valor" value=<?= hh($categoria->id) ?> <?= ($categoria->id == $categoria_id) ? 'selected' : ''?>> <?= hh($categoria->categoria) ?>  </option>
                <?php endforeach ?>
            </select>
        <h2> Ordenar por: </h2>
            <select id="ordenar" name="ordenar">
                    <option name="fecha" value=<?= "fecha" ?>> Fecha </option>
                    <option name="nombre" value=<?= "nombre" ?>> Nombre </option>
            </select>
        <h2> Precio mínimo: </h2>
            <label>
                <input type="text" name="desde" value="<?= $desde ?>">
            <label>
        <h2> Precio máximo: </h2>
            <label>
                <input type="text" name="hasta" value="<?= $hasta ?>">
            <label>
        <button type="submit" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Buscar</button>
        </form>

        <?php
            $sent = $pdo->prepare("SELECT p.*, c.categoria, c.id as id_categoria
                            FROM articulos p
                            JOIN categorias c ON c.id = p.id_categoria
                            $where
                            $orderby");
            $sent->execute($execute);
        ?>

       

                    

        <?php
            $sent = $pdo->prepare("SELECT * FROM articulos a JOIN categorias c ON a.id_categoria = c.id $where $orderby");
            $sent->execute($execute);
            $sent->fetch(PDO::FETCH_NAMED);

        ?>


        <div class="flex">
            <main class="flex-1 grid grid-cols-3 gap-4 justify-center justify-items-center">
                <?php foreach ($sent as $fila) : ?>
                    
                    <div class="p-6 max-w-xs min-w-full bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?= hh($fila['descripcion']) ?></h5>
                        </a>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><?= hh($fila['descripcion']) ?></p>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><?= hh($fila['categoria']) ?></p>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Existencias: <?= hh($fila['stock']) ?></p>
                        <?php if(hh($fila['descuento'])): ?>
                            <span class="text-lg font-medium text-red-900 line-through dark:text-white"> Antes: <del> <?= (hh($fila['precio']) * 100) / (100 - hh($fila['descuento'])) ?> </del></span> <span class="ml-3 text-lg font-medium">Ahora: <?= (hh($fila['precio'])) ?></span>
                        <?php else: ?>
                            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Precio: <?= hh($fila['precio']) ?></p>
                        <?php endif ?>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Fecha: <?= hh($fila['fecha']) ?></p>
                        <?php if ($fila['stock'] > 0): ?>
                            <a href="/insertar_en_carrito.php?id=<?= $fila[0] ?>&cat=<?= hh($categoria_id) ?>&ordenar=<?=hh($ordenar)?>&desde=<?=hh($desde)?>&hasta=<?= hh($hasta)?>" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Añadir al carrito
                                <svg aria-hidden="true" class="ml-3 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        <?php else: ?>
                            <a class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-gray-700 rounded-lg hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                Sin existencias
                            </a>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            </main>

            <?php if (!$carrito->vacio()) : ?>
                <aside class="flex flex-col items-center w-1/4" aria-label="Sidebar">
                    <div class="overflow-y-auto py-4 px-3 bg-gray-50 rounded dark:bg-gray-800">
                        <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <th scope="col" class="py-3 px-6">Descripción</th>
                                <th scope="col" class="py-3 px-6">Cantidad</th>
                                <th scope="col" class="py-3 px-6">Categoria</th>
                            </thead>
                            <tbody>
                                <?php foreach ($carrito->getLineas() as $id => $linea): ?>
                                    <?php
                                    $articulo = $linea->getArticulo();
                                    $cantidad = $linea->getCantidad();
                                    ?>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="py-4 px-6"><?= $articulo->getDescripcion() ?></td>
                                        <td class="py-4 px-6 text-center"><?= $cantidad ?></td>
                                        <td class="py-4 px-6 text-center"> <?= $articulo->getCategoria()->categoria ?></td>


                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <a href="/vaciar_carrito.php" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Vaciar carrito</a>
                        <a href="/comprar.php" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">Comprar</a>
                    </div>
                </aside>
            <?php endif ?>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>
