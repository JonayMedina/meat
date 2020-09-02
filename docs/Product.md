# Products


## Listado de productos
Listado de productos disponibles con parametros para paginación y búsqueda de elementos.

### Modelo

|Campo|Descripción|Tipo de dato|Longitud máxima|Requerido|
|--- |--- |--- |--- |--- |
|id|Identificador único del producto|INT|11|No|
|category|id de la categoría principal|INT|11|Sí|
|categories|colección de categorías asociadas|INT|11|Sí|
|code|SKU del producto|VARCHAR|255|Sí|
|name|Nombre del producto|VARCHAR|255|Sí|
|description|Descripción del producto|LONGTEXT|--|Sí|
|price|Precio base del producto en GTQ (price * 100)|INT|11|Sí|
|offerPrice|Precio de oferta del producto en GTQ (offerPrice * 100)|INT|11|Sí|
|measurementUnit|Tipo de medida|JSON con dos valores, plural y singular|255|Sí|
|keywords|Nombres clave para búsqueda de producto separados por coma|VARCHAR|255|No|
|photo|Fotografía principal del producto|LONGTEXT (BASE64) |--|Sí|

### Request

- Método: GET
- URL: /api/v1/products


### Parámetros

|Parameter|Parameter type|Description|Default|
|--- |--- |--- |--- |
|limit|url attribute|Número de items que devolverá el API| 10
|page|url attribute|Número de la página de resultados| 1
|search|url attribute|Texto de búsqueda| NULL

#### Ejemplos
- /api/v1/products?limit=5
- /api/v1/products?search=Pollo&page=2



### Response

```json
{
  "code": 200,
  "type": "info",
  "message": "Product list",
  "recordset": [
    {
      "id": 1,
      "category": {
        "id": 1,
        "name": "Carnes Rojas",
        "code": "SKU002",
        "slug": "carnes-rojas",
        "photo": "https://URL_DE_LA_FOTO.jpg"
      },
      "categories": [
        {
          "id": 1,
          "name": "Carnes Rojas",
          "code": "SKU002",
          "slug": "carnes-rojas",
          "photo": "https://URL_DE_LA_FOTO.jpg"
        },
        {
          "id": 2,
          "name": "Carnes de Res",
          "code": "SKU003",
          "slug": "res",
          "photo": "https://URL_DE_LA_FOTO.jpg"
        }
      ],
      "code": "SKU001",
      "slug": "lomito",
      "name": "Lomito",
      "description": "Pieza de lomito",
      "price": 2450,
      "offerPrice": 2200,
      "inStock": true,
      "measurementUnit": {
          "singular": "Libra",
          "plural": "Libras"
      },
      "keywords": "lomito, res, vaca",
      "photo": "BASE64 image"
    }
  ]
}
```


## Crear un producto

### Request

* URL: /api/v1/products
* Método: POST
* Body:


```json
{
  "category":  "SKU001",
  "categories": ["SKU002", "SKU003"],
  "code": "SKU001",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": 2200,
  "measurementUnit": {
     "singular": "Libra",
     "plural": "Libras"
  },
  "keywords": "lomito, res, vaca",
  "photo": "BASE64 image"
}
```

    
### Response

Posibles códigos de respuesta:
- 201: Se creó el producto
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación

```json
{
  "id": 2,
  "category": {
    "id": 1,
    "name": "Carnes Rojas",
    "code": "SKU005",
    "slug": "carnes-rojas",
    "photo": "https://URL_DE_LA_FOTO.jpg"
  },
  "categories": [
    {
      "id": 1,
      "name": "Carnes Rojas",
      "code": "SKU005",
      "slug": "carnes-rojas",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    },
    {
      "id": 2,
      "name": "Carnes de Res",
      "code": "SKU002",
      "slug": "res",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    }
  ],
  "code": "lomito",
  "slug": "SKU001",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": 2200,
  "inStock": true,
  "measurementUnit": {
      "singular": "Libra",
      "plural": "Libras"
  },
  "keywords": "lomito, res, vaca",
  "photo": "BASE64 image"
}
```

## Obtener un producto por su código

### Request

- URL: /api/v1/products/:code
- Método: GET

### Response

Posibles códigos de respuesta:
- 200: No hubo problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este producto no existe.


```json
{
  "id": 2,
  "category": {
    "id": 1,
    "name": "Carnes Rojas",
    "code": "SKU002",
    "slug": "carnes-rojas",
    "photo": "https://URL_DE_LA_FOTO.jpg"
  },
  "categories": [
    {
      "id": 1,
      "name": "Carnes Rojas",
      "code": "SKU002",
      "slug": "carnes-rojas",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    },
    {
      "id": 2,
      "name": "Carnes de Res",
      "code": "SKU002",
      "slug": "res",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    }
  ],
  "code": "SKU001",
  "slug": "lomito",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": 2200,
  "inStock": true,
  "measurementUnit": {
      "singular": "Libra",
      "plural": "Libras"
  },
  "keywords": "lomito, res, vaca",
  "photo": "BASE64 image"
}
```


## Actualizar un producto existente


### Request

- URL: /api/v1/products/:code
- Método: PUT
- Body:

```json
{
  "category":  "SKU001",
  "offerPrice": null,
  "photo": "BASE64 image"
}
```

Posibles códigos de respuesta:
- 200: No hubo problemas
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este producto no existe.


```json
{
  "id": 2,
  "category": {
    "id": 1,
    "name": "Carnes Rojas",
    "code": "SKU002",
    "slug": "carnes-rojas",
    "photo": "https://URL_DE_LA_FOTO.jpg"
  },
  "categories": [
    {
      "id": 1,
      "name": "Carnes Rojas",
      "code": "SKU002",
      "slug": "carnes-rojas",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    },
    {
      "id": 2,
      "name": "Carnes de Res",
      "code": "SKU001",
      "slug": "res",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    }
  ],
  "code": "SKU001",
  "slug": "lomito",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": 2200,
  "inStock": true,
  "measurementUnit": {
      "singular": "Libra",
      "plural": "Libras"
  },
  "keywords": "lomito, res, vaca",
  "photo": "BASE64 image"
}
```

## Configurar asociaciones de productos

### Request

- URL: /api/v1/products/:code/associations
- Método: POST
- Body: 

```json
{
  "similar_products": [
    "SKU001",
    "SKU002",
    "SKU003"
  ],
  "complimentary_products": [
    "SKU004",
    "SKU005",
    "SKU006"
  ]
}
```


## Eliminar un producto existente

### Request

* URL: /api/v1/products/:code
* Method: DELETE


### Response

* HTTP Status: 204 (No content), 403 (Invalid credentials)
* Body: N/A

## Marcar un producto como fuera de stock

### Request

* URL: /api/v1/products/:code/out-of-stock
* Method: PUT

## Marcar un producto como disponible

### Request

* URL: /api/v1/products/:code/in-stock
* Method: PUT


## Deshabilitar un producto

### Request

* URL: /api/v1/products/:code/disable
* Method: PUT


## Habilitar un producto

### Request

* URL: /api/v1/products/:code/enable
* Method: PUT
