<?php

use App\Tablas\Articulo;

session_start();

require '../vendor/autoload.php';

try {
    $id = obtener_get('id');
    $cat = obtener_get('cat');
    $ordenar = obtener_get('ordenar');
    $desde = obtener_get('desde');
    $hasta = obtener_get('hasta');

    if ($id === null) {
        return volver();
    }

    $articulo = Articulo::obtener($id);

    if ($articulo === null) {
        return volver();
    }

    if ($articulo->getStock() <= 0) {
        $_SESSION['error'] = 'No hay existencias suficientes.';
        return volver();
    }

    $carrito = unserialize(carrito());
    $carrito->insertar($id);
    $_SESSION['carrito'] = serialize($carrito);
    $url = "";
    
    if($cat !== null) {
    
        $url .= '&cat=' . hh($cat);
    }

    if($ordenar !== null) {
    
        $url .= '&ordenar=' . hh($ordenar);
    }

    if($desde !== null) {
    
        $url .= '&desde=' . hh($desde);
    
    }

    if($hasta !== null) {
    
        $url .= '&hasta=' . hh($hasta);
    }

    header("Location: /index.php?$url");

} catch (ValueError $e) {
    // TODO: mostrar mensaje de error en un Alert
}







