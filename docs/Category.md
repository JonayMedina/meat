# Category


## Listado de categorías
Listado de categorías disponibles con parámetros para paginación y búsqueda de elementos.

### Modelo
|Campo|Descripción|Tipo de dato|Longitud máxima|Requerido|
|--- |--- |--- |--- |--- |
|id|Identificador único de categoría|INT|11|No|
|code|Identificador único de categoría|VARCHAR|255|Sí|
|name|Nombre de la categoría|VARCHAR|255|Sí|
|parent|Id de la categoría padre|INT|11|No|
|position|Posición de la categoría entre otras categorías.|INT|11|No|
|photo|Fotografía principal de la categoría|LONGTEXT (BASE64) |--|Sí|

### Request

- Método: GET
- URL: /api/v1/categories


### Parámetros

|Parameter|Parameter type|Description|Default|
|--- |--- |--- |--- |
|limit|url attribute|Número de items que devolverá el API| 10
|page|url attribute|Número de la página de resultados| 1
|search|url attribute|Texto de búsqueda| NULL

#### Ejemplos
- /api/v1/categories?limit=5
- /api/v1/categories?search=Carnes+importadas&page=2



### Response

```json
{
    "code": 200,
    "type": "info",
    "message": "Category list",
    "recordset": [
        {
            "id": 1,
            "code": "carnes-rojas",
            "name": "Carnes Rojas",
            "parent": 1,
            "tree_root": null,
            "left": 1,
            "right": 11,
            "level": 0,
            "position": 0,
            "photo": "https://URL_DE_LA_FOTO.jpg",
            "created_at": "2020-03-31 17:08:53"
        },
		{
            "id": 2,
            "code": "Res",
            "name": "Res",
            "parent": 1,
            "left": 2,
            "right": 10,
            "level": 1,
            "position": 0,
            "photo": "https://URL_DE_LA_FOTO.jpg",
            "created_at": "2020-03-31 17:08:53"
        }
    ]
}
```


## Crear una categoría

### Request

* URL: /api/v1/categories
* Método: POST
* Body:

```json
{
    "code":"cordero",
    "name": "Cordero",
    "parent": 1,
    "photo": "BASE64 image",
    "position": 5
}
```

### Response

Posibles códigos de respuesta:
- 201: Se creó la categoría
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación

```json
{
    "id": 3,
    "code": "cordero",
    "name": "Cordero",
    "parent": {
        "id": 7,
        "code": "promo",
        "name": "Promociones",
        "left": 1,
        "right": 10,
        "position": 0,
        "photo": "https://URL_DE_LA_FOTO.jpg",
        "created_at": "2020-03-31 17:08:53"
    },
    "left": 1,
    "right": 10,
    "position": 9,
    "photo": "https://URL_DE_LA_FOTO.jpg",
    "created_at": "2020-03-31T17:08:53"
}
```

## Obtener una categoría por su código

### Request

- URL: /api/v1/categories/:code
- Método: GET

### Response

Posibles códigos de respuesta:
- 200: No hubieron problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Esta categoría no existe.


```json
{
    "id": 3,
    "code": "cordero",
    "name": "Cordero",
    "parent": {
        "id": 7,
        "code": "promo",
        "name": "Promociones",
        "left": 1,
        "right": 10,
        "position": 0,
        "photo": "https://URL_DE_LA_FOTO.jpg",
        "created_at": "2020-03-31 17:08:53"
    },
    "left": 1,
    "right": 10,
    "position": 1,
    "photo": "https://URL_DE_LA_FOTO.jpg",
    "created_at": "2020-03-31 17:08:53"
}
```


## Actualizar una categoría existente


### Request

- URL: /api/v1/categories/:code
- Método: PUT
- Body:

```json
{
    "photo": "BASE64 image"
}
```

Posibles códigos de respuesta:
- 200: No hubieron problemas
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Esta categoría no existe.


```json
{
    "id": 3,
    "code": "cordero",
    "name": "Cordero",
    "parent": {
        "id": 7,
        "code": "promo",
        "name": "Promociones",
        "left": 1,
        "right": 10,
        "position": 0,
        "photo": "https://URL_DE_LA_FOTO.jpg",
        "created_at": "2020-03-31 17:08:53"
    },
    "position": 1,
    "photo": "https://URL_DE_LA_NUEVA_FOTO.jpg",
    "created_at": "2020-03-31 17:08:53"
}
```


## Eliminar una categoría existente

### Request

* URL: /api/v1/categories/:code
* Method: DELETE


### Response

* HTTP Status: 204 (No content), 403 (Invalid credentials)
* Body: N/A
