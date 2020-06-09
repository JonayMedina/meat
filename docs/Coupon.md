# Coupons


## Listado de cupones
Listado de cupones disponibles con parametros para paginación y búsqueda de elementos.

### Modelo
|Campo|Descripción|Tipo de dato|Longitud máxima|Requerido|
|--- |--- |--- |--- |--- |
|id|Identificador único del cupón|INT|11|No|
|code|Código del cupón|VARCHAR|255|Sí|
|description|Descripción del cupón|VARCHAR|255|No|
|startsAt|Fecha de inicio de la promoción|DATETIME|--|No|
|endsAt|Fecha de fin de la promoción|DATETIME|--|No|
|enabled|Define si la promoción está o no habilitada|TINYINT|1|Sí|
|type|Tipo de promoción|ENUM: order_fixed_discount, order_percentage_discount|1|Sí|
|amount|Valor de la promoción|order_fixed_discount: INT(11), order_percentage_discount: DECIMAL (0-99)|--|Sí|
|oneUsagePerUser|Si está habilitado, solo se permitirá un uso por cliente|TINYINT|1|Sí|
|limitUsageToXQuantityOfUsers|Si está habilitado, se limita el uso del cupón a una cantidad de usuarios|TINYINT|1|Sí|
|usageLimit|Limite de uso del cupón por cliente|INT|11|No|
|used|Número de veces que el cupón ha sido usado (Solo lectura)|INT|11|No|


### Request

- Método: GET
- URL: /api/v1/coupons


### Parámetros

|Parameter|Parameter type|Description|Default|
|--- |--- |--- |--- |
|limit|url attribute|Número de items que devolverá el API| 10
|page|url attribute|Número de la página de resultados| 1
|search|url attribute|Texto de búsqueda| NULL

#### Ejemplos
- /api/v1/coupons?limit=5
- /api/v1/coupons?search=PROM001&page=2



### Response

```json
{
    "code": 200,
    "type": "info",
    "message": "Coupon list",
    "recordset": [
        {
            "id": 1,
            "code": "PROM001",
            "description": null,
            "enabled": true,
            "type": "order_fixed_discount",
            "amount": 10,
            "oneUsagePerUser": true,
            "limitUsageToXQuantityOfUsers": true,
            "usageLimit": 10,
            "used": 3,
            "startsAt": "2020-01-31 17:08:53",
            "endsAt": "2020-03-31 17:08:53"
        },
		{
            "id": 2,
            "code": "PROM002",
            "description": "Promoción de verano en Meat House",
            "enabled": true,
            "type": "order_percentage_discount",
            "amount": 15,
            "oneUsagePerUser": true,
            "limitUsageToXQuantityOfUsers": false,
            "usageLimit": null,
            "used": 13,
            "startsAt": "2020-02-31 17:08:53",
            "endsAt": "2020-03-31 17:08:53"
        }
    ]
}
```


## Crear un cupón

### Request

* URL: /api/v1/coupons
* Método: POST
* Body:

**Porcentual**
(En este caso el cupón es del 10% de descuento)

```json
{
  "code": "PROM003",
  "description": "Promoción de invierno",
  "enabled": true,
  "type": "order_percentage_discount",
  "amount": 10,
  "oneUsagePerUser": true,
  "limitUsageToXQuantityOfUsers": true,
  "usageLimit": 10,
  "startsAt": "2020-01-31 17:08:53",
  "endsAt": "2020-03-31 17:08:53"
}
```

**Monto fijo**
(En este caso, el monto son Q10, 1000/100, para evitar confusiones con puntos y comas)

```json
{
  "code": "PROM003",
  "description": "Promoción de invierno",
  "enabled": true,
  "type": "order_fixed_discount",
  "amount": 1000,
  "oneUsagePerUser": true,
  "limitUsageToXQuantityOfUsers": true,
  "usageLimit": 10,
  "used": 3,
  "startsAt": "2020-01-31 17:08:53",
  "endsAt": "2020-03-31 17:08:53"
}
```

### Response

Posibles códigos de respuesta:
- 201: Se creó el cupón
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación

```json
{
  "id": 3,
  "code": "PROM003",
  "description": "Promoción de invierno",
  "enabled": true,
  "type": "order_fixed_discount",
  "amount": 10,
  "oneUsagePerUser": true,
  "limitUsageToXQuantityOfUsers": true,
  "usageLimit": 10,
  "used": 3,
  "startsAt": "2020-01-31 17:08:53",
  "endsAt": "2020-03-31 17:08:53"
}
```

## Obtener un cupón por su código

### Request

- URL: /api/v1/coupons/:code
- Método: GET

### Response

Posibles códigos de respuesta:
- 200: No hubo problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este cupón no existe.


```json
{
  "id": 3,
  "code": "PROM003",
  "description": "Promoción de invierno",
  "enabled": true,
  "type": "order_fixed_discount",
  "amount": 10,
  "oneUsagePerUser": true,
  "limitUsageToXQuantityOfUsers": true,
  "usageLimit": 10,
  "used": 3,
  "startsAt": "2020-01-31 17:08:53",
  "endsAt": "2020-03-31 17:08:53"
}
```


## Actualizar un cupón existente


### Request

- URL: /api/v1/coupons/:code
- Método: PUT
- Body:

```json
{
  "description": "Venta de invierno"
}
```

Posibles códigos de respuesta:
- 200: No hubo problemas
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este cupón no existe.


```json
{
  "id": 3,
  "code": "PROM003",
  "description": "Venta de invierno",
  "enabled": false,
  "type": "order_fixed_discount",
  "amount": 10,
  "oneUsagePerUser": true,
  "limitUsageToXQuantityOfUsers": true,
  "usageLimit": 10,
  "used": 3,
  "startsAt": "2020-01-31 17:08:53",
  "endsAt": "2020-03-31 17:08:53"
}
```


## Eliminar un cupón existente

### Request

* URL: /api/v1/coupons/:code
* Method: DELETE


### Response

* HTTP Status: 204 (No content), 403 (Invalid credentials)
* Body: N/A


## Activar un cupón

### Request

- URL: /api/v1/coupons/:code/enable
- Método: PUT

### Response
```json
{
  "id": 3,
  "code": "PROM003",
  "description": "Venta de invierno",
  "enabled": true,
  "type": "order_fixed_discount",
  "amount": 10,
  "oneUsagePerUser": true,
  "limitUsageToXQuantityOfUsers": true,
  "usageLimit": 10,
  "used": 3,
  "startsAt": "2020-01-31 17:08:53",
  "endsAt": "2020-03-31 17:08:53"
}
```

Posibles códigos de respuesta:
- 200: No hubo problemas
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este cupón no existe.

## Desactivar un cupón

### Request

- URL: /api/v1/coupons/:code/disable
- Método: PUT

### Response
```json
{
  "id": 3,
  "code": "PROM003",
  "description": "Venta de invierno",
  "enabled": false,
  "type": "order_fixed_discount",
  "amount": 10,
  "oneUsagePerUser": true,
  "limitUsageToXQuantityOfUsers": true,
  "usageLimit": 10,
  "used": 3,
  "startsAt": "2020-01-31 17:08:53",
  "endsAt": "2020-03-31 17:08:53"
}
```

Posibles códigos de respuesta:
- 200: No hubo problemas
- 400: Por favor revise el contenido que está enviando, parece que hay errores
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Este cupón no existe.
