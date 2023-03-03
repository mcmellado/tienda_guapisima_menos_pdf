DROP TABLE IF EXISTS categorias CASCADE;

CREATE TABLE categorias(
    id          bigserial PRIMARY KEY,
    categoria   varchar   NOT NULL  
);

DROP TABLE IF EXISTS articulos CASCADE;

CREATE TABLE articulos (
    id              bigserial     PRIMARY KEY,
    codigo          varchar(13)   NOT NULL UNIQUE,
    descripcion     varchar(255)  NOT NULL,
    precio          numeric(7, 2) NOT NULL,
    stock           int           NOT NULL,
    descuento       int,
    visible         boolean       NOT NULL,
    fecha           date          NOT NULL,
    id_categoria    bigint    NOT NULL REFERENCES categorias(id)
);

DROP TABLE IF EXISTS cupones CASCADE;

CREATE TABLE cupones (
    id          bigserial       PRIMARY KEY,
    cupon       varchar(255)    NOT NULL UNIQUE,
    descuento   int             NOT NULL,
    fecha       date            NOT NULL
);


DROP TABLE IF EXISTS usuarios CASCADE;

CREATE TABLE usuarios (
    id       bigserial    PRIMARY KEY,
    usuario  varchar(255) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    validado bool         NOT NULL
);

DROP TABLE IF EXISTS facturas CASCADE;

CREATE TABLE facturas (
    id         bigserial  PRIMARY KEY,
    cupon      int        REFERENCES cupones(id),
    created_at timestamp  NOT NULL DEFAULT localtimestamp(0),
    usuario_id bigint NOT NULL REFERENCES usuarios (id)
);

DROP TABLE IF EXISTS articulos_facturas CASCADE;

CREATE TABLE articulos_facturas (
    articulo_id bigint NOT NULL REFERENCES articulos (id),
    factura_id  bigint NOT NULL REFERENCES facturas (id),
    cantidad    int    NOT NULL,
    PRIMARY KEY (articulo_id, factura_id)
);

-- Carga inicial de datos de prueba:


INSERT INTO categorias(categoria)
    VALUES  ('tecnologia'),
            ('alimentacion');

INSERT INTO cupones(cupon, descuento, fecha)
        VALUES ('morenitaxulita', 30, '04/03/2023'),
                ('prueba', 50, '02/03/2023');

INSERT INTO articulos (codigo, descripcion, precio, stock, id_categoria, visible, fecha)
    VALUES ('18273892389', 'Yogur piña', 200.50, 4, 2, true, '1/1/2023'),
           ('83745828273', 'Tigretón', 50.10, 2, 2, true, '1/2/2023'),
           ('51736128495', 'Disco duro SSD 500 GB', 150.30, 0, 1, true, '1/3/2023'),
           ('83746828273', 'Tigretón', 50.10, 3, 2, true, '1/4/2023'),
           ('51786128435', 'Disco duro SSD 500 GB', 150.30, 5, 1, true, '1/5/2023'),
           ('83745228673', 'Tigretón', 50.10, 8, 2, true, '1/6/2023'),
           ('51786198495', 'Disco duro SSD 500 GB', 150.30, 1, 1, true, '1/7/2023');


INSERT INTO usuarios (usuario, password, validado)
    VALUES ('admin', crypt('admin', gen_salt('bf', 10)), true),
           ('pepe', crypt('pepe', gen_salt('bf', 10)), false);
