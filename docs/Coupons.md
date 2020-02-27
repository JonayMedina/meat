# Coupons
These endpoints will allow you to easily manage coupons. Base URI is /api/v1/coupons.

## Model
|Field|Description|
|--- |--- |
|id|Id of the coupon|
|code|Unique coupon identifier|
|name|The name of the promotion|
|startsAt|Start date|
|endsAt|End date|
|usageLimit|Promotion’s usage limit|
|perCustomerUsageLimit|Promotion’s usage limit per customer|
|used|Number of times this promotion has been used|
|actions|Associated actions|
|createdAt|Date of creation|
|updatedAt|Date of last update|

## 1. Get Coupons
To retrieve a paginated list of coupons you will need to call the /api/v1/coupons/ endpoint with the GET method.
> **Stateless**  

### 1.1 Request

* URL: /api/v1/coupons/
* Method: GET

|Parameter|Parameter type|Description|
|--- |--- |--- |
|limit|query|(optional) Number of items to display per page, by default = 10|
|sorting[‘nameOfField’][‘direction’]|query|(optional) Field and direction of sorting, by default ‘desc’ and ‘priority’|
|criteria[‘nameOfCriterion’][‘searchOption’] criteria[‘nameOfCriterion’][‘searchingPhrase’]|query|(optional) Criterion, option and phrase of filtering,the criteria can be for example ‘couponBased’ and ‘search’, option can be ‘equal’, ‘contains’.|

### 1.2 Response

* HTTP Status: 200 (Ok), 204 (No content), 403 (Invalid credentials)
* Body:

```json
{
    "page": 1,
    "limit": 10,
    "pages": 1,
    "total": 2,
    "_links": {
        "self": {
            "href": "/api/v1/coupons/?page=1&limit=10"
        },
        "first": {
            "href": "/api/v1/coupons/?page=1&limit=10"
        },
        "last": {
            "href": "/api/v1/coupons/?page=1&limit=10"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 6,
                "code": "sd-promo",
                "name": "Sunday promotion"
            },
            {
                "id": 7,
                "code": "christmas-promotion",
                "name": "Christmas Promotion"
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
| _embedded.items[]             | Coupons collection                          | Object | (Required)   |
| _embedded.items[].id          | Coupon id                                   | Int    | (Required)   |
| _embedded.items[].code        | Coupon code                                 | String | (Required)   |
| _embedded.items[].name        | Coupon name                                 | String | (Required)   |


## 2. Get single coupon
To retrieve the details of a promotion you will need to call the /api/v1/coupons/{code} endpoint with the GET method.
> **Stateless**  

### 2.1 Request

* URL: /api/v1/coupons/{code}
* Method: GET


### 2.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)


```json
{
    "id": 6,
    "code": "sd-promo",
    "name": "Sunday promotion",
    "used": 0,
    "actions": [],
    "createdAt": "2017-02-28T12:05:12+0100",
    "updatedAt": "2017-02-28T12:05:13+0100"
}
```

|Field|Description|
|--- |--- |
|id|Id of the promotion|
|code|Unique promotion identifier|
|name|The name of the promotion|
|startsAt|Start date|
|endsAt|End date|
|used|Number of times this promotion has been used|
|actions|Associated actions|
|createdAt|Date of creation|
|updatedAt|Date of last update|

## 3. Create a coupon
To create a new promotion you will need to call the /api/v1/coupons/ endpoint with the POST method.
> **Stateless**  

### 3.1 Request

* URL: /api/v1/coupons/
* Method: POST
* Body:

Order fixed discount
```json
{
    "code": "christmas-promotion",
    "name": "Christmas Promotion",
    "startsAt": {
        "date": "2017-12-05",
        "time": "11:00"
    },
    "endsAt": {
        "date": "2017-12-31",
        "time": "11:00"
    },
    "actions": [
        {
            "type": "order_fixed_discount",
            "configuration": {
                "amount": 12
            }
        }
    ]
}
```

Order percentage discount
```json
{
    "code": "christmas-promotion",
    "name": "Christmas Promotion",
    "startsAt": {
        "date": "2017-12-05",
        "time": "11:00"
    },
    "endsAt": {
        "date": "2017-12-31",
        "time": "11:00"
    },
    "actions": [
        {
            "type": "order_percentage_discount",
            "configuration": {
                "percentage": 12
            }
        }
    ]
}
```

|Parameter|Parameter type|Description|
|--- |--- |--- |
|code|request|(Required) Promotion code|
|name|request|(Required) Promotion name|
|startsAt|request|(Optional) Object with date and time fields|
|endsAt|request|(Optional) Object with date and time fields|
|usageLimit|request|(Optional) Promotion’s usage limit|
|perCustomerUsageLimit|request|(Optional) Promotion’s usage limit per customer|
|couponBased|request|(Optional) Whether this promotion is triggered by a coupon|
|actions|request|(Optional) Collections of actions which will be done when the promotion will be|

### 3.2 Response

* HTTP Status: 201 (Created), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "id": 7,
    "code": "christmas-promotion",
    "name": "Christmas Promotion",
    "startsAt": "2017-12-05T11:00:00+0100",
    "endsAt": "2017-12-31T11:00:00+0100",
    "actions": [
        {
            "id": 3,
            "type": "order_fixed_discount",
            "configuration": {
                "amount": 1200
            }
        }
    ],
    "createdAt": "2017-03-06T11:40:38+0100",
    "updatedAt": "2017-03-06T11:40:39+0100"
}
```

## 4. Updating a coupon
To fully update a promotion you will need to call the /api/v1/coupons/{code} endpoint with the PUT method.
> **Stateless**  

### 4.1 Request

* URL: /api/v1/coupons/{code}
* Method: PUT
* Body: 

```json
{
    "code": "christmas-promotion",
    "name": "Christmas Promotion",
    "startsAt": {
        "date": "2017-12-05",
        "time": "11:00"
    },
    "endsAt": {
        "date": "2017-12-31",
        "time": "11:00"
    },
    "actions": [
        {
            "type": "order_fixed_discount",
            "configuration": {
                "amount": 12
            }
        }
    ]
}
```

## 5. Removing a coupon
To delete a promotion you will need to call the /api/v1/coupons/{code} endpoint with the DELETE method.
> **Stateless**  

### 5.1 Request

* URL: /api/v1/coupons/{code}
* Method: DELETE

### 5.2 Response

* HTTP Status: 204 (No content), 403 (Invalid credentials)
* Body: Empty


## 6. Valdations
When error on creation/update of coupons.

Body:
```json
{
    "code": 400,
    "message": "Validation Failed",
    "errors": {
        "children": {
            "name": {
                "errors": [
                    "Please enter promotion name."
                ]
            },
            "description": {},
            "startsAt": {
                "children": {
                    "date": {},
                    "time": {}
                }
            },
            "endsAt": {
                "children": {
                    "date": {},
                    "time": {}
                }
            },
            "actions": {},
            "code": {}
        }
    }
}
```
