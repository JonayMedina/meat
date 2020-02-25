# Products
These endpoints will allow you to easily manage products. Base URI is /api/v1/products.
> Product API response structure

If you request a product via API, you will receive an object with the following fields:

|Field|Description|
|--- |--- |
|id|Id of the product|
|code|Unique product identifier (for example SKU)|
|averageRating|Average from accepted ratings given by customer|
|translations|Collection of translations (each contains slug and name in given language)|
|images|Images assigned to the product|

If you request for more detailed data, you will receive an object with the following fields:

|Field|Description|
|--- |--- |
|id|Id of the product|
|code|Unique product identifier|
|averageRating|Average from ratings given by customer|
|translations|Collection of translations (each contains slug and name in given language)|
|associations|Collection of products associated with the created product (for example accessories to this product)|
|reviews|Collection of reviews passed by customers|
|productTaxons|Collection of relations between product and taxons|
|mainTaxon|The main taxon to whose the product is assigned|

## 1. Get products
To retrieve a paginated list of products you will need to call the /api/v1/products/ endpoint with the GET method.
> **Stateless**  

### 1.1 Request

* URL: /api/v1/products/
* Method: GET

|Parameter|Parameter type|Description|
|--- |--- |--- |
|limit|query|(optional) Number of items to display per page, by default = 10|
|sorting[‘nameOfField’][‘direction’]|query|(optional) Field and direction of sorting, by default ‘desc’ and ‘createdAt’|

### 1.2 Response

* HTTP Status: 200 (Ok), 204 (No content), 403 (Invalid credentials)
* Body:

```json
{
    "page": 1,
    "limit": 4,
    "pages": 16,
    "total": 63,
    "_links": {
        "self": {
            "href": "/api/v1/products/?sorting%5Bcode%5D=desc&page=1&limit=4"
        },
        "first": {
            "href": "/api/v1/products/?sorting%5Bcode%5D=desc&page=1&limit=4"
        },
        "last": {
            "href": "/api/v1/products/?sorting%5Bcode%5D=desc&page=16&limit=4"
        },
        "next": {
            "href": "/api/v1/products/?sorting%5Bcode%5D=desc&page=2&limit=4"
        }
    },
    "_embedded": {
        "items": [
            {
                "name": "Spiderman Mug",
                "id": 61,
                "code": "SMM",
                "options": [],
                "averageRating": 0,
                "images": [],
                "_links": {
                    "self": {
                        "href": "/api/v1/products/SMM"
                    }
                }
            },
            {
                "name": "Theme Mug",
                "id": 63,
                "code": "MUG_TH",
                "averageRating": 0,
                "images": [],
                "_links": {
                    "self": {
                        "href": "/api/v1/products/MUG_TH"
                    }
                }
            },
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
| _embedded.items[].id          | Product ID                                 | Int    | (Required)   |
| _embedded.items[].name        | Product name                               | String | (Required)   |
| _embedded.items[].code        | Product code                               | String | (Required)   |
| _embedded.items[].averageRating | Average rating                               | Int | (Optional)   |
| _embedded.items[].images[]        | Product images                               | Object | (Optional)   |
| _embedded.items[].images[].id        | Image id                               | Int | (Required)   |
| _embedded.items[].images[].type        | Image type, it can be main or thumbnail  | String | (Required)   |
| _embedded.items[]._links.self.href        | Product API URL                               | URL | (Required)   |


## 2. Create Product
To create a new product you will need to call the /api/v1/products/ endpoint with the POST method.
> **Stateless**  

### 2.1 Request

* URL: /api/v1/products/
* Method: POST
* Body:

```json
{
    "code": "TS3"
}
```

|Field|Description|
|--- |--- |
|id|Id of the product|
|code|Unique product identifier|
|averageRating|Average from ratings given by customer|
|translations|Collection of translations (each contains slug and name in given language)|
|attributes|Collection of attributes connected with the product (for example material)|
|associations|Collection of products associated with the created product (for example accessories to this product)|
|reviews|Collection of reviews passed by customers|
|productTaxons|Collection of relations between product and categories|
|mainTaxon|The main category to whose the product is assigned|




### 2.2 Response

* HTTP Status: 201 (Created), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "id": 61,
    "code": "TS3",
    "associations": [],
    "productTaxons": [],
    "reviews": [],
    "averageRating": 0,
    "images": []
}
```

You can also create a product with additional (not required) fields:

|Parameter|Parameter type|Description|
|--- |--- |--- |
|channels|request|Collection of channels codes, which we want to associate with created product|
|translations[‘localeCode’][‘name’]|request|Name of the product|
|translations[‘localeCode’][‘slug’]|request|(unique) Slug for the product|
|options|request|Collection of options codes, which we want to associate with created product|
|images|request|Collection of images types, which we want to associate with created product|
|attributes|request|Array of attributes (each object has information about selected attribute’s code, its value and locale in which it was defined)|
|associations|request|Object with code of productAssociationType and string in which the codes of associated products was written down.|
|productTaxons|request|String in which the codes of taxons was written down (separated by comma)|
|mainTaxon|request|The main taxon’s code to whose product is assigned|

* URL: /api/v1/products/
* Method: POST
* Body:

```json
{
    "code": "MUG_TH",
    "mainTaxon": "mugs",
    "productTaxons": "mugs",
    "channels": [
        "WHAREHOUSE"
    ],
     "associations": {
         "similar_products": "SMM,BMM",
         "complimentary_products": "SMM,BMM" 
     },
    "translations": {
        "es_GT": {
            "name": "Taza temática",
            "slug": "taza-tematica"
        }
    },
    "images": [
        {
            "type": "ford"
        }
    ]
}
```

* HTTP Status: 201 (Created), 403 (Invalid credentials), 400 (Bad request)
* Body:
```json
{
    "name": "Theme Mug",
    "id": 69,
    "code": "MUG_TH",
    "associations": [
        {
            "id": 13,
            "type": {
                "name": "Similar products",
                "id": 1,
                "code": "similar_products",
                "translations": {
                    "en_US": {
                        "locale": "en_US",
                        "id": 1,
                        "name": "Similar products"
                    }
                }
            },
            "associatedProducts": [
                {
                    "name": "Batman mug",
                    "id": 63,
                    "code": "BMM",
                    "associations": [],
                    "translations": {
                        "es_GT": {
                            "locale": "es_GT",
                            "id": 63,
                            "name": "Batman mug",
                            "slug": "batman-mug"
                        }
                    },
                    "productTaxons": [],
                    "averageRating": 0,
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "/api/v1/products/BMM"
                        },
                        "variants": {
                            "href": "/api/v1/products/BMM/variants/"
                        }
                    }
                },
                {
                    "name": "Spider-Man Mug",
                    "id": 68,
                    "code": "SMM",
                    "associations": [],
                    "translations": {
                        "es_GT": {
                            "locale": "es_GT",
                            "id": 70,
                            "name": "Spider-Man Mug",
                            "slug": "spider-man-mug"
                        }
                    },
                    "productTaxons": [],
                    "reviews": [],
                    "averageRating": 0,
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "/api/v1/products/SMM"
                        },
                        "variants": {
                            "href": "/api/v1/products/SMM/variants/"
                        }
                    }
                }
            ]
        }
    ],
    "translations": {
        "es_GT": {
            "locale": "es_GT",
            "id": 71,
            "name": "Taza temática",
            "slug": "taza-tematica"
        }
    },
    "productTaxons": [
        {
            "id": 78,
            "taxon": {
                "name": "Mugs",
                "id": 2,
                "code": "mugs",
                "translations": {
                    "es_GT": {
                        "locale": "es_GT",
                        "id": 2,
                        "name": "Tazas",
                        "slug": "tazas",
                        "description": "Non omnis vel impedit eaque necessitatibus et eveniet. Fugiat distinctio quos aut commodi ea minima. Et natus ratione sit aperiam a molestiae. Eligendi sed cumque deleniti unde magnam."
                    }
                },
                "images": [],
                "_links": {
                    "self": {
                        "href": "/api/v1/taxons/mugs"
                    }
                }
            },
            "position": 0
        }
    ],
    "mainTaxon": {
        "name": "Mugs",
        "id": 2,
        "code": "mugs",
        "translations": {
            "en_US": {
                "locale": "en_US",
                "id": 2,
                "name": "Mugs",
                "slug": "mugs",
                "description": "Non omnis vel impedit eaque necessitatibus et eveniet. Fugiat distinctio quos aut commodi ea minima. Et natus ratione sit aperiam a molestiae. Eligendi sed cumque deleniti unde magnam."
            }
        },
        "images": [],
        "_links": {
            "self": {
                "href": "/api/v1/taxons/mugs"
            }
        }
    },
    "reviews": [],
    "averageRating": 0,
    "images": [
        {
            "id": 121,
            "type": "ford",
            "path": "65/f6/1e3b25f3721768b535e5c37ac005.jpeg"
        }
    ],
    "_links": {
        "self": {
            "href": "/api/v1/products/MUG_TH"
        },
        "variants": {
            "href": "/api/v1/products/MUG_TH/variants/"
        }
    }
}
```

|Field|Description|
|--- |--- |
|id|Id of the product|
|code|Unique product identifier|
|averageRating|Average from ratings given by customer|
|translations|Collection of translations (each contains slug and name in given language)|
|associations|Collection of products associated with the created product (for example accessories to this product)|
|reviews|Collection of reviews passed by customers|
|productTaxons|Collection of relations between product and taxons|
|mainTaxon|The main taxon to whose the product is assigned|



## 3. Get single product
To retrieve the details of a product you will need to call the /api/v1/products/code endpoint with the GET method.
> **Stateless**  

### 3.1 Request

* URL: /api/v1/products/{code}
* Method: GET


### 3.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "name": "Batman mug",
    "id": 63,
    "code": "BMM",
    "associations": [],
    "translations": {
        "es_GT": {
            "locale": "es_GT",
            "id": 63,
            "name": "Batman mug",
            "slug": "batman-mug"
        }
    },
    "productTaxons": [],
    "averageRating": 0,
    "images": []
}
```

## 4. Updating a product
To fully update a product you will need to call the /api/v1/products/code endpoint with the PUT method.
> **Stateless**  

### 4.1 Request

* URL: /api/v1/products/{code}
* Method: PUT, PATCH
* Body: 

```json
{
    "translations": {
        "es_GT": {
            "name": "Taza de Batman",
            "slug": "taza-de-batman"
        }
    }
}
```

### 4.2 Response

* HTTP Status: 204 (Ok), 403 (Invalid credentials), 400 (Bad request)
* Body: Empty


## 5. Removing a customer
To delete a product you will need to call the /api/v1/products/code endpoint with the DELETE method.
> **Stateless**  

### 5.1 Request

* URL: /api/v1/products/{code}
* Method: DELETE

### 5.2 Response

* HTTP Status: 204 (No content), 403 (Invalid credentials)
* Body: Empty

## 6. Valdations
When error on creation/update of a product.

Body:
```json
{
    "code": 400,
    "message": "Validation Failed",
    "errors": {
        "children": {
            "enabled": {},
            "translations": {},
            "associations": {},
            "mainTaxon": {},
            "productTaxons": {},
            "images": {},
            "code": {
                "errors": [
                    "Please enter product code."
                ]
            }
        }
    }
}

```
