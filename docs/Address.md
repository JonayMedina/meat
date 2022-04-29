# Address


## Obtener detalle de dirección por id.

### Request

- Método: GET
- URL: /api/v1/addresses/:id

### Response

Posibles códigos de respuesta:
- 200: No hubo problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Esta dirección no existe.

```json
{
    "code": 200,
    "type": "info",
    "message": "Ok",
    "recordset": {
        "id": 73,
        "ask_for": "Ricardo Mart\u00ednez",
        "full_address": "Avenida Siempreviva 742",
        "phone_number": "3517-5196",
        "status": "pending",
        "type": "shipping",
        "is_default": false,
        "parent": [],
        "validated_at": null,
        "customer": {
            "email": "rmartinez@mnc.gt",
            "first_name": "Ricardo",
            "last_name": "Martinez",
            "phone_number": null,
            "gender": "m",
            "age": null
        }
    }
}
```


## Validar dirección

### Request

- Método: POST
- URL: /api/v1/addresses/:id

### Response

Posibles códigos de respuesta:
- 200: No hubo problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Esta dirección no existe.

```json
{
    "code": 200,
    "type": "info",
    "message": "Ok",
    "recordset": {
        "id": 73,
        "ask_for": "Ricardo Martínez",
        "full_address": "Avenida Siempreviva 742",
        "phone_number": "3517-5196",
        "status": "validated",
        "type": "shipping",
        "is_default": false,
        "parent": [],
        "validated_at": "2020-07-03T18:41:42-06:00",
        "customer": {
            "email": "rmartinez@mnc.gt",
            "first_name": "Ricardo",
            "last_name": "Martinez",
            "phone_number": null,
            "gender": "m",
            "age": null
        }
    }
}
```


## Rechazar dirección

### Request

- Método: DELETE
- URL: /api/v1/addresses/:id

### Response

Posibles códigos de respuesta:
- 200: No hubo problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Esta dirección no existe.

```json
{
    "code": 200,
    "type": "info",
    "message": "Ok",
    "recordset": {
        "id": 73,
        "ask_for": "Ricardo Mart\u00ednez",
        "full_address": "Avenida Siempreviva 742",
        "phone_number": "3517-5196",
        "status": "pending",
        "type": "shipping",
        "is_default": false,
        "parent": [],
        "validated_at": null,
        "customer": {
            "email": "rmartinez@mnc.gt",
            "first_name": "Ricardo",
            "last_name": "Martinez",
            "phone_number": null,
            "gender": "m",
            "age": null
        }
    }
}
```
