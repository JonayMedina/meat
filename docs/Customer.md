# Clientes


## Listado de clientes
Con este servicio usted podrá navegar por el listado de clientes realizando búsquedas paginadas.


### Modelo
|Campo|Descripción|Tipo de dato|Longitud máxima|Comments|
|--- |--- |--- |--- |--- |
|id|Identificador único del cliente|INT|11||
|user|User Object|ARRAY|--|El objeto usuario contiene user.id, user.username, user.enabled|
|email|Customer Email|VARCHAR|255||
|firstName|Nombre del cliente|VARCHAR|255||
|lastName|Apellido del cliente|VARCHAR|255||
|gender|Género del cliente|ENUM: m, f, u|1|m: Male, f: Female, u: Unknown|
|birthday|Fecha de cumpleaños del cliente|DATETIME|--|Formato: "Y-m-d H:i:s"|
|phoneNumber|Número de teléfono del usuario|VARCHAR|255||


### Request

- Método: GET
- URL: /api/v1/customers


### Parámetros

|Parameter|Parameter type|Description|Default|
|--- |--- |--- |--- |
|limit|url attribute|Número de items que devolverá el API| 10
|page|url attribute|Número de la página de resultados| 1
|search|url attribute|Texto de búsqueda| NULL

#### Ejemplos
- /api/v1/customers
- /api/v1/customers?page=3&search=Rodmar+Zavala


### Response


```json
{
    "code": 200,
    "type": "info",
    "message": "Customer list",
    "recordset": [
        {
            "id": 3,
            "user": {
                "id": 5,
                "username": "maluma",
                "enabled": true
            },
            "email": "jlondono@mnc.gt",
            "firstName": "Juan Luis",
            "lastName": "Londoño Arias",
            "gender": "m",
            "birthday": "1989-09-21",
            "phoneNumber": "5555-5555"
        },
        {
            "id": 4,
            "user": {
                "id": 7,
                "username": "badbunny",
                "enabled": true
            },
            "email": "bmartinez@mnc.gt",
            "firstName": "Benito Antonio",
            "lastName": "Martínez Ocasio",
            "gender": "m",
            "birthday": "1989-09-21",
            "phoneNumber": "5555-5555"
        }
    ]
}
```



## Obtener cliente por id

### Request

- URL: /api/v1/customers/:id
- Método: GET

### Response

Posibles códigos de respuesta:
- 200: No hubieron problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Esta cliente no existe.


```json
{
    "id": 4,
    "user": {
        "id": 7,
        "username": "badbunny",
        "enabled": true
    },
    "email": "bmartinez@mnc.gt",
    "firstName": "Benito Antonio",
    "lastName": "Martínez Ocasio",
    "gender": "m",
    "birthday": "1989-09-21",
    "phoneNumber": "5555-5555"
}
```
