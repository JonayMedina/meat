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
|shortName|Nombre corto del producto|VARCHAR|150|Sí|
|description|Descripción del producto|LONGTEXT|--|Sí|
|price|Precio base del producto en GTQ (price * 100)|INT|11|Sí|
|offerPrice|Precio de oferta del producto en GTQ (offerPrice * 100)|INT|11|Sí|
|measurementUnit|Tipo de medida|ENUM: (pound, package, piece, liter)|100|Sí|
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
        "code": "carnes-rojas",
        "photo": "https://URL_DE_LA_FOTO.jpg"
      },
      "categories": [
        {
          "id": 1,
          "name": "Carnes Rojas",
          "code": "carnes-rojas",
          "photo": "https://URL_DE_LA_FOTO.jpg"
        },
        {
          "id": 2,
          "name": "Carnes de Res",
          "code": "res",
          "photo": "https://URL_DE_LA_FOTO.jpg"
        }
      ],
      "code": "lomito",
      "name": "Lomito",
      "description": "Pieza de lomito",
      "price": 2450,
      "offerPrice": 2200,
      "measurementUnit": "piece",
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
  "category":  "carnes-rojas",
  "categories": ["carnes-rojas", "res"],
  "code": "lomito",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": 2200,
  "measurementUnit": "piece",
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
    "code": "carnes-rojas",
    "photo": "https://URL_DE_LA_FOTO.jpg"
  },
  "categories": [
    {
      "id": 1,
      "name": "Carnes Rojas",
      "code": "carnes-rojas",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    },
    {
      "id": 2,
      "name": "Carnes de Res",
      "code": "res",
      "photo": "https://URL_DE_LA_FOTO.jpg"
    }
  ],
  "code": "lomito",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": 2200,
  "measurementUnit": "piece",
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
- 200: No hubieron problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este producto no existe.


```json
{
  "category":  "carnes-rojas",
  "categories": ["carnes-rojas", "res"],
  "code": "lomito",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": 2200,
  "measurementUnit": "piece",
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
  "category":  "carnes-rojas",
  "offerPrice": null,
  "photo": "BASE64 image"
}
```

Posibles códigos de respuesta:
- 200: No hubieron problemas
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este producto no existe.


```json
{
  "category":  "carnes-rojas",
  "categories": ["carnes-rojas", "res"],
  "code": "lomito",
  "name": "Lomito",
  "description": "Pieza de lomito",
  "price": 2450,
  "offerPrice": null,
  "measurementUnit": "piece",
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
    "CODE1",
    "CODE2",
    "CODE3"
  ],
  "complimentary_products": [
    "CODE4",
    "CODE5",
    "CODE6"
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