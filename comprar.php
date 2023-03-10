<?php 
use App\Tablas\Factura;
use App\Tablas\Usuario;

session_start() 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Comprar</title>
</head>

<body>
    <?php require '../vendor/autoload.php';

    if (!\App\Tablas\Usuario::esta_logueado()) {
        return redirigir_login();
    }

    $carrito = unserialize(carrito());

    if (obtener_post('_testigo') !== null) {
        $pdo = conectar();
        $sent = $pdo->prepare('SELECT *
                                 FROM articulos
                                WHERE id IN (:ids)');
        foreach ($sent->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            if ($fila['stock'] < $carrito->getLinea($fila['id'])->getCantidad()) {
                $_SESSION['error'] = 'No hay existencias suficientes para crear la factura.';
                return volver();
            }
        }
        // Crear factura
        $usuario = \App\Tablas\Usuario::logueado();
        $usuario_id = $usuario->id;

        $cupon = obtener_get("cupon");

        if(isset($cupon)) {
                $pdo->beginTransaction();
            $sent = $pdo->prepare('INSERT INTO facturas (usuario_id, cupon)
                                VALUES (:usuario_id, :cupon)
                                RETURNING id');
            $sent->execute([':usuario_id' => $usuario_id,
                            ':cupon' => $cupon]);
            $factura_id = $sent->fetchColumn();
            $lineas = $carrito->getLineas();
            $values = [];
            $execute = [':f' => $factura_id];
            $i = 1;
        } else {
            $pdo->beginTransaction();
            $sent = $pdo->prepare('INSERT INTO facturas (usuario_id)
                                   VALUES (:usuario_id)
                                   RETURNING id');
            $sent->execute([':usuario_id' => $usuario_id]);
            $factura_id = $sent->fetchColumn();
            $lineas = $carrito->getLineas();
            $values = [];
            $execute = [':f' => $factura_id];
            $i = 1;
        }

        foreach ($lineas as $id => $linea) {
            $values[] = "(:a$i, :f, :c$i)";
            $execute[":a$i"] = $id;
            $execute[":c$i"] = $linea->getCantidad();
            $i++;
        }

        $values = implode(', ', $values);
        $sent = $pdo->prepare("INSERT INTO articulos_facturas (articulo_id, factura_id, cantidad)
                               VALUES $values");
        $sent->execute($execute);
        foreach ($lineas as $id => $linea) {
            $cantidad = $linea->getCantidad();
            $sent = $pdo->prepare('UPDATE articulos
                                      SET stock = stock - :cantidad
                                    WHERE id = :id');
            $sent->execute([':id' => $id, ':cantidad' => $cantidad]);
        }
        $pdo->commit();
        $_SESSION['exito'] = 'La factura se ha creado correctamente.';
        unset($_SESSION['carrito']);
        return volver();
    }

    $cupon = obtener_get("cupon");
    $aplicar = obtener_get("aplicar");
    $errores = ['cupon' => []]; 
    
    if(isset($aplicar)) {
        
        $pdo = conectar();
        
        $sent = $pdo->query("SELECT * from cupones");
            
            foreach($sent as $array) {
            if($array['cupon'] !== $cupon) {
                $errores['cupon'][] = 'No existe ese cup??n.';
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
        }


    ?>

    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <div class="overflow-y-auto py-4 px-3 bg-gray-50 rounded dark:bg-gray-800">
            <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <th scope="col" class="py-3 px-6">C??digo</th>
                    <th scope="col" class="py-3 px-6">Descripci??n</th>
                    <th scope="col" class="py-3 px-6">Cantidad</th>
                    <th scope="col" class="py-3 px-6">Precio</th>
                    <?php if(isset($aplicar)): ?>
                        <?php if($vacio): ?>
                        <th scope="col" class="py-3 px-6">Nuevo precio</th>
                        <?php endif  ?>
                        <?php endif  ?>
                    <th scope="col" class="py-3 px-6">Importe</th>
                    <th scope="col" class="py-3 px-6">Acciones</th>
                    
                </thead>
                <tbody>
                    <?php $total = 0 ?>
                    <?php foreach ($carrito->getLineas() as $id => $linea) : ?>
                        <?php
                        $articulo = $linea->getArticulo();
                        $codigo = $articulo->getCodigo();
                        $cantidad_producto = $linea->getCantidad();
                        $precio = $articulo->getPrecio();
                        
                        if(isset($aplicar) && $vacio) {
                                $pdo = conectar();
                                $cupones_ = $pdo->query("SELECT * FROM cupones");
                                foreach($cupones_ as $cupo):
                                    $descuento = hh($cupo['descuento']);
                                    $precio_nuevo = $precio - ($precio * ($descuento/100));
                                    $importe_nuevo = $precio_nuevo * $cantidad_producto;
                                    $total += $importe_nuevo;
                                   
                                    var_dump($importe_nuevo);
                                endforeach;
                            } else { 
                                    $importe = $precio * $cantidad_producto;
                                    $total += $importe;
                            }
        
                        ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-4 px-6"><?= $articulo->getCodigo() ?></td>
                            <td class="py-4 px-6"><?= $articulo->getDescripcion() ?></td>
                            <td class="py-4 px-6 text-center"><?= $cantidad_producto ?></td>
                            <?php if(isset($aplicar)): ?>
                            <?php if($vacio): ?>
                                <td class="py-4 px-6 text-center text-red">
                                    <del> <?= dinero($precio) ?> </del>
                                </td>
                            <?php endif ?>
                            <?php else: ?>
                                <td class="py-4 px-6 text-center">
                                     <?= dinero($total) ?> </del>
                                </td>
                                <?php endif ?>
                                
                            <?php if(!(isset($aplicar) && $vacio)): ?>
                                <td class="py-4 px-6 text-center">
                                    <?= dinero($precio) ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?= dinero($precio) ?>
                                </td>
                            <?php else: ?>
                                <td class="py-4 px-6 text-center">
                                    <?= dinero($precio_nuevo) ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?= dinero($importe_nuevo) ?>
                                </td>
                            <?php endif ?>
                            <td class="py-4 px-6 text-center">
                                <a href="/restar.php?id=<?= $articulo->id ?>&cupon=<?= hh($cupon) ?>&aplicar=<?= hh($aplicar) ?>" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"><?= $articulo->id ?></a>
                                <a href="/sumar.php?id=<?= $articulo->id ?>&cupon=<?= hh($cupon) ?>&aplicar=<?= hh($aplicar) ?>" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"><?= $articulo->id ?></a>
                            </td>

                        </tr>
                    <?php endforeach ?>
            
            <h2> ??Tienes alg??n cup??n de descuento?: </h2>
            <form action="" method="GET" class="mx-auto flex mt-4">
            <label>
                <input type="text" name="cupon" value="<?= $cupon ?>">
                <button type="submit" name="aplicar" class="mx-auto focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">Aplicar cupon</button>
                <?php foreach ($errores['cupon'] as $err): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-bold">??Error!</span> <?= $err ?></p>
                <?php endforeach ?>
            </label>
            </form>


                </tbody>
                <tfoot>
                    <td colspan="3"></td>
                    <td class="text-center font-semibold">TOTAL:</td>
                    <td class="text-center font-semibold"><?= dinero($total) ?></td>
                    <?php if(isset($aplicar)): ?>
                        <?php if($vacio): ?>
                        <td scope="col" class="py-3 px-6">descuento: <?= $cupon ?> <?= $descuento ?> % </td>
                        <?php endif  ?>
                        <?php endif  ?>
                </tfoot>
            </table>
            <form action="" method="POST" class="mx-auto flex mt-4">
                <input type="hidden" name="_testigo" value="1">
                <button type="submit" href="" class="mx-auto focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">Realizar pedido</button>
            </form>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
                </body>
</html>
