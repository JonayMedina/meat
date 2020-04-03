# Order rating
Este servicio devuelve cual es el rating promedio de las ordenes, este servicio puede devolver el resultado de las ordenes en un rango especifico de fechas.


## Modelo
Campo | Descripción | Tipo de Dato| Longitud Máxima| INFO
------|-------------|------|----------|------------
rating| Promedio de rating de las ordenes | FLOAT |  |

## Request

- Método: GET
- URL: /api/v1/order-rating


## Parámetros

|Parameter|Parameter type|Description|Default|
|--- |--- |--- |--- |
|startsAt|url attribute|Fecha de inicio de conteo, formato Y-m-d| NULL
|endsAt|url attribute|Fecha fin de conteo, formato Y-m-d| NULL

### Ejemplos
- /api/v1/order-rating
- /api/v1/order-rating?startsAt=2020-01-01&endsAt=2020-03-31


Ejemplo en PowerShell

```
$response = Invoke-RestMethod 'https://localhost:8000/api/v1/order-rating' -Method 'GET' -Headers $headers -Body $body
$response | ConvertTo-Json
```


## Response


```json
{
    "code": 200,
    "type": "info",
    "message": "Order rating result",
    "recordset": [
        {
            "rating": 3.5
        }
    ]
}
```
