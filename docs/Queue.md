# Cola de sincronización
Con este servicio podrá obtener que entidades han cambiado por eventos.

- **persist**: Se ejecuta al guardar un elemento nuevo en la base de datos.
- **update**: Se ejecuta después de actualizar un elemento en la base de datos.
- **order_completed**: Este evento se ejecuta después de completar el checkout de una orden.
- **order_rated**: Se ejecuta luego de que una orden ha sido calificada por parte del cliente.

El listado de modelos que pueden verse afectados por estos eventos son:

- **order**: Son las ordenes que los clientes han generado desde la tienda.
- **address**: Son las direcciones que los clientes han creado, generalmente estas necesitan ser aprobadas antes de ser usadas.
- **customer**: Cuando un nuevo cliente se registra se usa este modelo en la cola de sincronización.


## Listado de items


### Modelo
Campo | Descripción | Tipo de Dato| Longitud Máxima| INFO
------|-------------|------|----------|------------
id| Identificador del elemento en la cola | INT | 11 |
body.id | Identificador del recurso | INT | 11 |
body.model | Recurso | ENUM: order, category, product, coupon, customer | 150 |
body.type | El tipo de evento que desencadenó esta entrada en la cola | ENUM: persist, update, remove | 150 |
body.url | URL del API para ejecutar la actualización del modelo | VARCHAR | 255 | URL absoluta
body.metadata | Detalle de que ha cambiado en este evento. | VARCHAR | 255 | URL absoluta
created_at | Fecha/Hora en la que el evento fue creado | DATETIME | -- | Formato: Y-m-d H:i:s

### Request

- Método: GET
- URL: /api/v1/queue


### Parámetros

|Parameter|Parameter type|Description|Default|
|--- |--- |--- |--- |
|limit|url attribute|Número de items que devolverá el API| 10

#### Ejemplos
- /api/v1/queue?limit=5
- /api/v1/queue?limit=15


Ejemplo en PowerShell

```
$response = Invoke-RestMethod 'https://localhost:8000/api/v1/queue' -Method 'GET' -Headers $headers -Body $body
$response | ConvertTo-Json
```

Ejemplo C# (RestSharp)

```csharp
var client = new RestClient("https://localhost:8000/api/v1/queue");
client.Timeout = -1;
var request = new RestRequest(Method.GET);
IRestResponse response = client.Execute(request);
Console.WriteLine(response.Content);
```



### Response
La respuesta es una colección de items dentro de **recordset**, cada item representa una entrada en la cola de eventos a ejecutar del lado de los servidores de Procasa.

Si no hay extensión del servicio o si esta es .json, la respuesta será un JSON

```json
{
    "code": 200,
    "type": "info",
    "message": "Sync queue",
    "recordset": [
        {
            "id": 32,
            "body": {
                "id": 21,
                "model": "coupon",
                "type": "persist",
                "url": "http://localhost:8000/api/v1/coupon/21",
                "metadata": []
            },
            "created_at": "2020-03-31 17:08:53"
        },
        {
            "id": 33,
            "body": {
                "id": 26,
                "model": "coupon",
                "type": "update",
                "url": "http://localhost:8000/api/v1/coupon/26",
                "metadata": []
            },
            "created_at": "2020-03-31 21:22:14"
        }
    ]
}
```


## Marcar un elemento de la cola como ejecutado.
Con el fin de mantener la cola de eventos limpia, del lado de los servidores de Procasa debe marcarse cada item de la cola como ya ejecutado.

Esto se hará con una llamada POST al API de queues.


### Request

- Método: POST 
- URL: /api/v1/queue/:id

Ejemplo en PowerShell

```
$response = Invoke-RestMethod 'https://localhost:8000/api/v1/queue/32' -Method 'POST' -Headers $headers -Body $body
$response | ConvertTo-Json
```

Ejemplo C# (RestSharp)

```csharp
var client = new RestClient("https://localhost:8000/api/v1/queue/32");
client.Timeout = -1;
var request = new RestRequest(Method.POST);
request.AddHeader("", "");
IRestResponse response = client.Execute(request);
Console.WriteLine(response.Content);
```

### Response

El código de respuesta para este servicio puede ser:

- 200: Se marcó como terminado de forma correcta.
- 404: Puede que este evento ya haya sido marcado como terminado antes o no existe.

```json
{
    "code": 200,
    "type": "info",
    "message": "Updated",
    "recordset": []
}
```

```json
{
    "code": 404,
    "type": "error",
    "message": "Entry not found",
    "recordset": []
}
```
