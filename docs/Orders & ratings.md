# Orders & ratings
Orders API endpoint is /api/v1/orders.

### Model
|Field|Description|
|--- |--- |
|id|Id of the order|
|items|List of items related to the order|
|itemsTotal|Sum of all items prices|
|adjustments|List of adjustments related to the order|
|adjustmentsTotal|Sum of all order adjustments|
|total|Sum of items total and adjustments total|
|customer|Customer detailed serialization for order|
|checkoutState|State of the checkout process|
|state|State of the order|
|checkoutCompletedAt|Date when the checkout has been completed|
|number|Serial number of the order|
|rating|Order rating|
|shippingAddress|Detailed address serialization|
|billingAddress|Detailed address serialization|
|shipments|Detailed serialization of all related shipments|
|payments|Detailed serialization of all related payments|


## 1. Get Orders
Order list.
> **Stateless**  

### 1.1 Request

* URL: /api/v1/orders/
* Method: GET

| Parameter      | Parameter type   | Description                                                       |
| -------------- | ---------------- | ----------------------------------------------------------------- |
| page           | query            | (optional) Number of the page, by default = 1                     |
| limit          | query            | (optional) Number of items to display per page, by default = 10   |

### 1.2 Response

* HTTP Status: 200 (Ok), 204 (No content), 403 (Invalid credentials)
* Body:

```json
{
    "page": 1,
    "limit": 10,
    "pages": 1,
    "total": 1,
    "_links": {
        "self": {
            "href": "/api/v1/orders/?page=1&limit=10"
        },
        "first": {
            "href": "/api/v1/orders/?page=1&limit=10"
        },
        "last": {
            "href": "/api/v1/orders/?page=1&limit=10"
        },
        "next": {
            "href": "/api/v1/orders/?page=1&limit=10"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 21,
                "items": [
                    {
                        "id": 65,
                        "quantity": 1,
                        "unitPrice": 8082,
                        "total": 8082,
                        "units": [
                            {
                                "id": 189,
                                "adjustments": [],
                                "adjustmentsTotal": 0
                            }
                        ],
                        "unitsTotal": 8082,
                        "rating": null,
                        "adjustments": [],
                        "adjustmentsTotal": 0,
                        "variant": {
                            "id": 100,
                            "code": "727F_patched_cropped_jeans-variant-0",
                            "optionValues": [
                                {
                                    "code": "jeans_size_s",
                                    "translations": {
                                        "es_GT": {
                                            "locale": "es_GT",
                                            "id": 14,
                                            "value": "S"
                                        }
                                    }
                                }
                            ],
                            "position": 0,
                            "translations": {
                                "es_GT": {
                                    "locale": "es_GT",
                                    "id": 100,
                                    "name": "S"
                                }
                            },
                            "version": 1,
                            "tracked": false,
                            "_links": {
                                "self": {
                                    "href": "/api/v1/products/727F_patched_cropped_jeans/variants/727F_patched_cropped_jeans-variant-0"
                                }
                            }
                        },
                        "_links": {
                            "order": {
                                "href": "/api/v1/orders/21"
                            },
                            "product": {
                                "href": "/api/v1/products/727F_patched_cropped_jeans"
                            },
                            "variant": {
                                "href": "/api/v1/products/727F_patched_cropped_jeans/variants/727F_patched_cropped_jeans-variant-0"
                            }
                        }
                    },
                    {
                        "id": 67,
                        "quantity": 1,
                        "unitPrice": 4090,
                        "total": 4090,
                        "rating": 4,
                        "units": [
                            {
                                "id": 188,
                                "adjustments": [],
                                "adjustmentsTotal": 0
                            }
                        ],
                        "unitsTotal": 4090,
                        "adjustments": [],
                        "adjustmentsTotal": 0,
                        "_links": {
                            "order": {
                                "href": "/api/v1/orders/21"
                            },
                            "product": {
                                "href": "/api/v1/products/Everyday_white_basic_T_Shirt"
                            },
                            "variant": {
                                "href": "/api/v1/products/Everyday_white_basic_T_Shirt/variants/Everyday_white_basic_T_Shirt-variant-0"
                            }
                        }
                    }
                ],
                "itemsTotal": 12172,
                "total": 12172,
                "customer": {
                    "id": 22,
                    "email": "rodmar_zavala@hotmail.com",
                    "firstName": "Rodmar",
                    "lastName": "Zavala",
                    "user": {
                        "id": 22,
                        "username": "rodmar_zavala@hotmail.com",
                        "enabled": true
                    },
                    "_links": {
                        "self": {
                            "href": "/api/v1/customers/22"
                        }
                    }
                },
                "checkoutState": "completed"
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
| _embedded.id                  | Order ID                                    | Int    | (Required)   |
| _embedded.items[]               | Items wrapper                               | Object | (Required)   |
| _embedded.items[].id            | Item ID                                     | Int    | (Required)   |
| _embedded.items[].quantity      | Quantity of this item                                    | Int    | (Required)   |
| _embedded.items[].unitPriuce    | Unit price of item                                    | Int    | (Required)   |
| _embedded.items[].total                      | Total of item                                   | Int    | (Required)   |
| _embedded.items[].adjustments               | Price adjustments (Promotions)                   | Object    | (Required)   |
| _embedded.items[].adjustmentsTotal          | Adjustments total                                | Int    | (Required)   |
| _embedded.items[].variant                   | Variant Data                                  | Object    | (Required)   |
| _embedded.items[].variant.id                | Variant ID                                    | Int    | (Required)   |
| _embedded.items[].variant.code              | Variant code                                    | String    | (Required)   |
| _embedded.items[].variant.optionValues      | Variant Options                             | Object    | (Required)   |
| _embedded.items[].variant.optionValues.code | Option code                                    | String    | (Required)   |
| _embedded.items[].variant.optionValues.translations | Option translations                                    | Int    | (Required)   |
| _embedded.items[].variant.optionValues.translations.es_GT.value | Option label                                    | Int    | (Required)   |
| _embedded.items[].links                     | Links to relted entities                                    | Object    | (Required)   |
| _embedded.items[].links.order.href          | Link to order                                    | URL    | (Required)   |
| _embedded.items[].links.product.href        | Link to product                                    | URL    | (Required)   |
| _embedded.items[].links.variant.href        | Link to variant                                  | URL    | (Required)   |
| _embedded.itemsTotal        | Sum of all items prices    | Int    | (Required)   |
| _embedded.adjustments        | List of adjustments related to the order       | Object    | (Required)   |
| _embedded.total        | Sum of items total and adjustments total             | Int    | (Required)   |
| _embedded.customer        | Cusrtomer Object                                    | Object    | (Required)   |
| _embedded.checkoutState        | State of the checkout process                                   | Object    | (Required)   |
| _embedded.number        | Serial number of the order                                   | Int    | (Optional)   |
| _embedded.rating        | Order rating                                   | Int    | (Optional)   |
| _embedded.shippingAddress     | Detailed address serialization                                   | Object    | (Optional)   |
| _embedded.billingAddress     | Detailed address serialization                                   | Object    | (Optional)   |
| _embedded.shipments     | Detailed serialization of all related shipments                       | Object    | (Optional)   |
| _embedded.payments     | Detailed serialization of all related payments                         | Object    | (Optional)   |

## 2. Get single order
You can request detailed order information by executing the following request:
> **Stateless**  

### 2.1 Request

* URL: /api/v1/orders/{id}
* Method: GET


### 2.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)

|Field|Description|
|--- |--- |
|id|Id of the order|
|items|List of items related to the order|
|itemsTotal|Sum of all items prices|
|adjustments|List of adjustments related to the order|
|adjustmentsTotal|Sum of all order adjustments|
|total|Sum of items total and adjustments total|
|customer|Customer detailed serialization for order|
|channel|Default channel serialization|
|currencyCode|Currency of the order|
|checkoutState|State of the checkout process|
|state|State of the order|
|checkoutCompletedAt|Date when the checkout has been completed|
|number|Serial number of the order|
|rating|Order rating|
|shippingAddress|Detailed address serialization|
|billingAddress|Detailed address serialization|
|shipments|Detailed serialization of all related shipments|
|payments|Detailed serialization of all related payments|



## 3. Cancel an order
You can cancel an already placed order by executing the following request:
> **Stateless**  

### 3.1 Request

* URL: /api/v1/orders/{id}/cancel
* Method: PUT

### 3.2 Response

* HTTP Status: 204 (No content), 403 (Invalid credentials), 400 (Bad request)
* Body: Empty
