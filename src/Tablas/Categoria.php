<?php

namespace App\Tablas;

use PDO;

class Categoria extends Modelo
{
    protected static string $tabla = 'categorias';

    public $id;

    public $categoria;

    public function __construct(array $campos)
    {
        $this->id = $campos['id'];
        $this->categoria = $campos['categoria'];
    }

    public function getTodosarticulos(?PDO $pdo = null)
    {
        $where[] = 'id_categoria = :id:';
        $execute[':id:'] = $this->id;
        return Articulo::todos($where, $execute);

    }

    
}
