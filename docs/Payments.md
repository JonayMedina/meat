# Payments
These endpoints will allow you to easily present payments. Base URI is /api/v1/payments.

## Model

|Field|Description|
|--- |--- |
|id|Unique id of the payment|
|method|The payment method object serialized for the cart|
|amount|The amount of payment|
|state|State of the payment process|
|_links[self]|Link to itself|
|_links[payment-method]|Link to the related payment method|
|_links[order]|Link to the related order|



## 1. Get payments
To retrieve a paginated list of payments you will need to call the /api/v1/payments/ endpoint with the GET method.
> **Stateless**  

### 1.1 Request

* URL: /api/v1/payments/
* Method: GET

|Parameter|Parameter type|Description|
|--- |--- |--- |
|page|query|(optional) Number of the page, by default = 1|
|limit|query|(optional) Number of items to display per page, by default = 10|
|sorting[amount]|query|(optional) Sorting direction on the amount field (DESC/ASC)|
|sorting[createdAt]|query|(optional) Sorting direction on the createdAt field (ASC by default)|
|sorting[updatedAt]|query|(optional) Sorting direction on the updatedAt field (DESC/ASC)|


### 1.2 Response

* HTTP Status: 200 (Ok), 204 (No content), 403 (Invalid credentials)
* Body:

```json
{
    "page":1,
    "limit":2,
    "pages":10,
    "total":20,
    "_links":{
        "self":{
            "href":"/api/v1/payments/?page=1&limit=2"
        },
        "first":{
            "href":"/api/v1/payments/?page=1&limit=2"
        },
        "last":{
            "href":"/api/v1/payments/?page=10&limit=2"
        },
        "next":{
            "href":"/api/v1/payments/?page=2&limit=2"
        }
    },
    "_embedded":{
        "items":[
            {
                "id":1,
                "method":{
                    "id":2,
                    "code":"bank_transfer",
                    "_links":{
                        "self":{
                            "href":"/api/v1/payment-methods/bank_transfer"
                        }
                    }
                },
                "amount":3812,
                "state":"new",
                "_links":{
                    "self":{
                        "href":"/api/v1/payments/1"
                    },
                    "payment-method":{
                        "href":"/api/v1/payment-methods/bank_transfer"
                    },
                    "order":{
                        "href":"/api/v1/orders/1"
                    }
                }
            },
            {
                "id":2,
                "method":{
                    "id":2,
                    "code":"bank_transfer",
                    "_links":{
                        "self":{
                            "href":"/api/v1/payment-methods/bank_transfer"
                        }
                    }
                },
                "amount":3915,
                "state":"new",
                "_links":{
                    "self":{
                        "href":"/api/v1/payments/2"
                    },
                    "payment-method":{
                        "href":"/api/v1/payment-methods/bank_transfer"
                    },
                    "order":{
                        "href":"/api/v1/orders/2"
                    }
                }
            }
        ]
    }
}
```

| Key                           | Description                                 | Type   | Rules        |
|-------------------------------|---------------------------------------------|--------|--------------|
| page                          | Page number                                 | Int    | (Required)   |
| limit                         | Max number of items                         | Int    | (Required)   |
| pages                         | Max number of pages                         | Int    | (Required)   |
| total                         | Available items count                       | Int    | (Required)   |
| _links                        | Pagination info                             | Object | (Required)   |
| _links.self.href              | Link to current page                        | URL    | (Optional)   |
| _links.first.href             | Link to first page                          | URL    | (Optional)   |
| _links.last.href              | Link to last page                           | URL    | (Optional)   |
| _links.next.href              | Link to next page                           | URL    | (Optional)   |
| _embedded                     | Embedded data                               | Object | (Required)   |
| _embedded.items[]             | Items wrapper                               | Object | (Required)   |
| _embedded.items[].id          | Payment ID                                 | Int    | (Required)   |
| _embedded.items[].method                      | Payment method                                 | Object    | (Required)   |
| _embedded.items[].method.id                   | Payment method id                              | Int    | (Required)   |
| _embedded.items[].method.code                 | Payment method code                            | String | (Required)   |
| _embedded.items[].method._links.self.href | Payment method link                                | URL    | (Required)   |
| _embedded.items[].amount                      | Payment amount                                 | Int    | (Required)   |
| _embedded.items[].state                       | Payment state                                  | String | (Required)   |
| _embedded.items[]._links.self.href            | Link to self payment                           | URL    | (Required)   |
| _embedded.items[]._links.payment-method.href  | Link to payment method                         | URL    | (Required)   |
| _embedded.items[]._links.order.href           | Link to order                                  | URL    | (Required)   |


## 2. Get single payment
To retrieve the details of a payment you will need to call the /api/v1/payments/{id} endpoint with the GET method.
> **Stateless**  

### 2.1 Request
* URL: /api/v1/payments/{id}
* Method: GET


### 2.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "id":20,
    "method":{
        "id":2,
        "code":"bank_transfer",
        "_links":{
            "self":{
                "href":"/api/v1/payment-methods/bank_transfer"
            }
        }
    },
    "amount":4507,
    "state":"new",
    "_links":{
        "self":{
            "href":"/api/v1/payments/20"
        },
        "payment-method":{
            "href":"/api/v1/payment-methods/bank_transfer"
        },
        "order":{
            "href":"/api/v1/orders/20"
        }
    }
}
```

| Key                           | Description                                 | Type   | Rules        |
|-------------------------------|---------------------------------------------|--------|--------------|
| id          | Payment ID                                 | Int    | (Required)   |
| method                      | Payment method                                 | Object    | (Required)   |
| method.id                   | Payment method id                              | Int    | (Required)   |
| method.code                 | Payment method code                            | String | (Required)   |
| method._links.self.href | Payment method link                                | URL    | (Required)   |
| amount                      | Payment amount                                 | Int    | (Required)   |
| state                       | Payment state                                  | String | (Required)   |
| _links.self.href            | Link to self payment                           | URL    | (Required)   |
| _links.payment-method.href  | Link to payment method                         | URL    | (Required)   |
| ._links.order.href           | Link to order                                  | URL    | (Required)   |
