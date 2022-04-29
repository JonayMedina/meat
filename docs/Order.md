# Ordenes


## Listado de ordenes
Listado de ordenes disponibles con parametros para paginación y búsqueda de elementos.

### Modelo
|Campo|Descripción|Tipo de dato|Longitud máxima|Comments|
|--- |--- |--- |--- |--- |
|id|Identificador único de la orden|INT|11||
|number|Número de la orden|VARCHAR|255||
|rating|Rating de esta orden|INT|11|Número entero del 1-5, null significa que no|
|ratingComment|Comentario del cliente|LONGTEXT|--||
|items|Colección de items incluidos en esta orden|ARRAY|--||
|itemsTotal|Cantidad total en items|INT|11|(total*100)|
|adjustments|Colección de ajustes hechos a la orden|ARRAY|--||
|adjustmentsTotal|Cantidad total en ajustes|INT|11|(adjustments*100)|
|total|Total de la orden|INT|11|(total*100)|
|customer|Objeto del cliente|ARRAY|--||
|currencyCode|Código de la moneda|VARCHAR|11|En ISO 4217, código de 3 dígitos, como USD, GTQ|
|checkoutState|Estado del proceso de pago|VARCHAR|25|cart, addressed, shipping_selected, shipping_skipped, payment_selected, payment_skipped, completed|
|state|Estado de la orden|VARCHAR|100|cart, new, fulfilled, cancelled|
|checkoutCompletedAt|Fecha en que se completó el pago|DATETIME||Nullable, Formato Y-m-d H:i:s|
|shippingAddress|Serialización detallada de direcciones|ARRAY|||
|billingAddress|Serialización detallada de direcciones|ARRAY|||
|shipments|Serialización detallada de todos los envíos relacionados.|ARRAY|||
|payments|Serialización detallada de todos los pagos relacionados.|ARRAY|||

### Request

- Método: GET
- URL: /api/v1/orders


### Parámetros

|Parameter|Parameter type|Description|Default|
|--- |--- |--- |--- |
|limit|url attribute|Número de items que devolverá el API| 10
|page|url attribute|Número de la página de resultados| 1
|search|url attribute|Texto de búsqueda| NULL

#### Ejemplos
- /api/v1/orders?limit=5
- /api/v1/orders?search=Rodmar+Zavala&page=2



### Response

```json
{
  "code": 200,
  "type": "info",
  "message": "Order list",
  "recordset": [
    {
      "id": 1,
      "number": "000000021",
      "rating": 4,
      "ratingComment": "El mejor servicio y la mejor carne.",
      "items": [
        {
          "id": 74,
          "quantity": 1,
          "unitPrice": 100000,
          "total": 7000,
          "units": [
            {
              "id": 228,
              "adjustments": [
              ],
              "adjustmentsTotal": 0
            }
          ],
          "unitsTotal": 100000,
          "adjustments": [
            {
              "id": 252,
              "type": "order_promotion",
              "label": "Cupón PM001",
              "amount": -1000
            }
          ],
          "adjustmentsTotal": -1000,
          "variant": {
            "id": 331,
            "code": "lomito",
            "optionValues": [
              {
                "name": "Lomito",
                "code": "lomito"
              }
            ],
            "position": 2,
            "name": "Medium Mug",
            "onHold": 0,
            "onHand": 10,
            "tracked": false,
            "price": 100000
          },
          "itemsTotal": 8000,
          "customer": {
            "id": 1,
            "email": "shop@example.com",
            "emailCanonical": "shop@example.com",
            "firstName": "John",
            "lastName": "Doe",
            "gender": "u",
            "user": {
              "id": 1,
              "username": "shop@example.com",
              "usernameCanonical": "shop@example.com",
              "enabled": true
            }
          },
          "currencyCode": "GTQ",
          "checkoutState": "completed",
          "state": "fulfilled",
          "checkoutCompletedAt": "2020-03-31 21:00:12",
          "shippingAddress": {
            "id": 71,
            "firstName": "Rodmar",
            "lastName": "Zavala",
            "countryCode": "GT",
            "street": "Principal, Cuchilla de el carmen",
            "city": "Santa Catarina Pinula",
            "postcode": "01069",
            "createdAt": "2020-02-14 11:55:40",
            "updatedAt": "2020-02-14 17:00:17"
          },
          "billingAddress": {
            "id": 72,
            "firstName": "Rodmar",
            "lastName": "Zavala",
            "countryCode": "GT",
            "street": "Principal, Cuchilla de el carmen",
            "city": "Santa Catarina Pinula",
            "postcode": "01069",
            "createdAt": "2020-02-14 11:55:40",
            "updatedAt": "2020-02-14 17:00:17"
          }
        }
      ]
    }
  ]
}
```


## Obtener una orden por su id

### Request

- URL: /api/v1/orders/:id
- Método: GET

### Response

Posibles códigos de respuesta:
- 200: No hubieron problemas
- 403: No hay suficientes permisos para ejecutar esta operación
- 404: Esta orden no existe.


```json
{
  "id": 1,
  "number": "000000021",
  "rating": 4,
  "ratingComment": "El mejor servicio y la mejor carne.",
  "items": [
    {
      "id": 74,
      "quantity": 1,
      "unitPrice": 100000,
      "total": 7000,
      "units": [
        {
          "id": 228,
          "adjustments": [
          ],
          "adjustmentsTotal": 0
        }
      ],
      "unitsTotal": 100000,
      "adjustments": [
        {
          "id": 252,
          "type": "order_promotion",
          "label": "Cupón PM001",
          "amount": -1000
        }
      ],
      "adjustmentsTotal": -1000,
      "variant": {
        "id": 331,
        "code": "lomito",
        "optionValues": [
          {
            "name": "Lomito",
            "code": "lomito"
          }
        ],
        "position": 2,
        "name": "Medium Mug",
        "onHold": 0,
        "onHand": 10,
        "tracked": false,
        "price": 100000
      },
      "itemsTotal": 8000,
      "customer": {
        "id": 1,
        "email": "shop@example.com",
        "emailCanonical": "shop@example.com",
        "firstName": "John",
        "lastName": "Doe",
        "gender": "u",
        "user": {
          "id": 1,
          "username": "shop@example.com",
          "usernameCanonical": "shop@example.com",
          "enabled": true
        }
      },
      "currencyCode": "GTQ",
      "checkoutState": "completed",
      "state": "fulfilled",
      "checkoutCompletedAt": "2020-03-31 21:00:12",
      "shippingAddress": {
        "id": 71,
        "firstName": "Rodmar",
        "lastName": "Zavala",
        "countryCode": "GT",
        "street": "Principal, Cuchilla de el carmen",
        "city": "Santa Catarina Pinula",
        "postcode": "01069",
        "createdAt": "2020-02-14 11:55:40",
        "updatedAt": "2020-02-14 17:00:17"
      },
      "billingAddress": {
        "id": 72,
        "firstName": "Rodmar",
        "lastName": "Zavala",
        "countryCode": "GT",
        "street": "Principal, Cuchilla de el carmen",
        "city": "Santa Catarina Pinula",
        "postcode": "01069",
        "createdAt": "2020-02-14 11:55:40",
        "updatedAt": "2020-02-14 17:00:17"
      }
    }
  ]
} 
```