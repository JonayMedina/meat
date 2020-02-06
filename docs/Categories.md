# Categories
These endpoints will allow you to easily manage taxons. Base URI is /api/v1/taxons.

## Model

|Field|Description|
|--- |--- |
|id|Id of the taxon|
|code|Unique taxon identifier|
|root|The main ancestor of the taxon|
|parent|Parent of the taxon|
|translations|Collection of translations (each contains slug, name and description in the respective language)|
|position|Position of the taxon among other taxons|
|images|Images assigned to the taxon|
|left|Location within the whole taxonomy|
|right|Location within the whole taxonomy|
|level|How deep the taxon is in the tree|
|children|Descendants of the taxon|



## 1. Get category collection
To retrieve a paginated list of taxons you will need to call the /api/v1/taxons/ endpoint with the GET method.
> **Stateless**  

### 1.1 Request

* URL: /api/v1/taxons/
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
    "limit": 10,
    "pages": 1,
    "total": 5,
    "_links": {
        "self": {
            "href": "\/api\/v1\/taxons\/?page=1&limit=10"
        },
        "first": {
            "href": "\/api\/v1\/taxons\/?page=1&limit=10"
        },
        "last": {
            "href": "\/api\/v1\/taxons\/?page=1&limit=10"
        }
    },
    "_embedded": {
        "items": [
            {
                "name": "Category",
                "id": 1,
                "code": "category",
                "position": 0,
                "translations": {
                    "es_GT": {
                        "locale": "es_GT",
                        "id": 1,
                        "name": "Category",
                        "slug": "category",
                        "description": "Consequatur illo amet aliquam."
                    }
                },
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/1"
                    }
                }
            },
            {
                "name": "T-Shirts",
                "id": 5,
                "code": "t_shirts",
                "root": {
                    "name": "Category",
                    "id": 1,
                    "code": "category",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/1"
                        }
                    }
                },
                "parent": {
                    "name": "Category",
                    "id": 1,
                    "code": "category",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/1"
                        }
                    }
                },
                "position": 0,
                "translations": {
                    "es_GT": {
                        "locale": "es_GT",
                        "id": 5,
                        "name": "T-Shirts",
                        "slug": "t-shirts",
                        "description": "Modi aut laborum aut sint aut ea itaque porro."
                    }
                },
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/5"
                    }
                }
            },
            {
                "name": "Men",
                "id": 6,
                "code": "mens_t_shirts",
                "root": {
                    "name": "Category",
                    "id": 1,
                    "code": "category",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/1"
                        }
                    }
                },
                "parent": {
                    "name": "T-Shirts",
                    "id": 5,
                    "code": "t_shirts",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/5"
                        }
                    }
                },
                "position": 0,
                "translations": {
                    "es_GT": {
                        "locale": "es_GT",
                        "id": 6,
                        "name": "Men",
                        "slug": "t-shirts\/men",
                        "description": "Reprehenderit vero atque eaque sunt perferendis est."
                    }
                },
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/6"
                    }
                }
            },
            {
                "name": "Women",
                "id": 7,
                "code": "womens_t_shirts",
                "root": {
                    "name": "Category",
                    "id": 1,
                    "code": "category",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/1"
                        }
                    }
                },
                "parent": {
                    "name": "T-Shirts",
                    "id": 5,
                    "code": "t_shirts",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/5"
                        }
                    }
                },
                "position": 1,
                "translations": {
                    "es_GT": {
                        "locale": "es_GT",
                        "id": 7,
                        "name": "Women",
                        "slug": "t-shirts\/women",
                        "description": "Illum quia beatae assumenda impedit."
                    }
                },
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/7"
                    }
                }
            },
            {
                "name": "toys",
                "id": 9,
                "code": "toys",
                "root": {
                    "name": "Category",
                    "id": 1,
                    "code": "category",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/1"
                        }
                    }
                },
                "parent": {
                    "name": "Category",
                    "id": 1,
                    "code": "category",
                    "position": 0,
                    "translations": [],
                    "images": [],
                    "_links": {
                        "self": {
                            "href": "\/api\/v1\/taxons\/1"
                        }
                    }
                },
                "position": 1,
                "translations": {
                    "en_US": {
                        "locale": "en_US",
                        "id": 9,
                        "name": "toys",
                        "slug": "toys",
                        "description": "Toys for boys"
                    }
                },
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/9"
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
| _embedded.items[].id          | Category ID                                 | Int    | (Required)   |
| _embedded.items[].name          | Category name                             | String    | (Required)   |
| _embedded.items[].code          | Category code                             | String    | (Required)   |
| _embedded.items[].root          | Root Category                             | Object    | (Required)   |
| _embedded.items[].parent                  | Parent Category                 | Object    | (Required)   |
| _embedded.items[].position                | Category position               | Int       | (Required)   |
| _embedded.items[].translations            | Translations                    | Object    | (Required)   |
| _embedded.items[].translations.es_GT.name | Category name                   | String    | (Required)   |
| _embedded.items[].translations.es_GT.slug | Category slug                   | String    | (Required)   |
| _embedded.items[].translations.es_GT.description | Category description     | String    | (Required)   |
| _embedded.items[].images                    | Category  images              | Array     | (Required)   |
| _embedded.items[]._links.self.href          | Category API URL              | URL       | (Required)   |

## 2. Create category
To create a new taxon you will need to call the /api/v1/taxons/ endpoint with the POST method.
> **Stateless**  

### 2.1 Request

* URL: /api/v1/taxons/
* Method: POST
* Body:

```json
{
    "code":"toys",
    "translations":{
        "es_GT": {
            "name": "Toys",
            "slug": "category/toys",
            "description": "Toys for boys"
        }
    },
    "parent": "category",
    "images": [
        {
            "type": "ford"
        }
    ]
}
```

|Parameter|Parameter type|Description|
|--- |--- |--- | 
|code|request|(unique) Taxon identifier|
|translations[‘localeCode’][‘name’]|request|Taxon name|
|translations[‘localeCode’][‘slug’]|request|(unique) Taxon slug|
|translations[‘localeCode’][‘description’]|request|(Optional) Description of the taxon|
|parent|request|(Optional) The parent taxon’s code|
|images|request|(Optional) Images codes assigned to the taxon|


### 2.2 Response

* HTTP Status: 201 (Created), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "name": "toys",
    "id": 9,
    "code": "toys",
    "root": {
        "name": "Category",
        "id": 1,
        "code": "category",
        "children": [
            {
                "name": "T-Shirts",
                "id": 5,
                "code": "t_shirts",
                "children": [],
                "left": 2,
                "right": 7,
                "level": 1,
                "position": 0,
                "translations": [],
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/5"
                    }
                }
            }
        ],
        "left": 1,
        "right": 10,
        "level": 0,
        "position": 0,
        "translations": {
            "es_GT": {
                "locale": "es_GT",
                "id": 1,
                "name": "Category",
                "slug": "category",
                "description": "Consequatur illo amet aliquam."
            }
        },
        "images": [],
        "_links": {
            "self": {
                "href": "\/api\/v1\/taxons\/1"
            }
        }
    },
    "parent": {
        "name": "Category",
        "id": 1,
        "code": "category",
        "children": [
            {
                "name": "T-Shirts",
                "id": 5,
                "code": "t_shirts",
                "children": [],
                "left": 2,
                "right": 7,
                "level": 1,
                "position": 0,
                "translations": [],
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/5"
                    }
                }
            }
        ],
        "left": 1,
        "right": 10,
        "level": 0,
        "position": 0,
        "translations": {
            "es_GT": {
                "locale": "es_GT",
                "id": 1,
                "name": "Category",
                "slug": "category",
                "description": "Consequatur illo amet aliquam."
            }
        },
        "images": [],
        "_links": {
            "self": {
                "href": "\/api\/v1\/taxons\/1"
            }
        }
    },
    "children": [],
    "left": 8,
    "right": 9,
    "level": 1,
    "position": 1,
    "translations": {
        "es_GT": {
            "locale": "es_GT",
            "id": 9,
            "name": "toys",
            "slug": "toys",
            "description": "Toys for boys"
        }
    },
    "images": [
        {
            "id": 1,
            "type": "ford",
            "path": "b9/65/01cec3d87aa2b819e195331843f6.jpeg"
        }
    ],
    "_links": {
        "self": {
            "href": "\/api\/v1\/taxons\/9"
        }
    }
}
```

|Field|Description|
|--- |--- |
|id|Id of the taxon|
|code|Unique taxon identifier|
|root|The main ancestor of the taxon|
|parent|Parent of the taxon|
|translations|Collection of translations (each contains slug, name and description in the respective language)|
|position|Position of the taxon among other taxons|
|images|Images assigned to the taxon|
|left|Location within the whole taxonomy|
|right|Location within the whole taxonomy|
|level|How deep the taxon is in the tree|
|children|Descendants of the taxon|


## 3. Get single category
To retrieve the details of a taxon you will need to call the /api/v1/taxons/{code} endpoint with the GET method.
> **Stateless**  

### 3.1 Request

* URL: /api/v1/taxons/{code}
* Method: GET

> To see the details of the taxon with code = toys use the below method:

### 3.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "name": "toys",
    "id": 9,
    "code": "toys",
    "root": {
        "name": "Category",
        "id": 1,
        "code": "category",
        "children": [
            {
                "name": "T-Shirts",
                "id": 5,
                "code": "t_shirts",
                "children": [],
                "left": 2,
                "right": 7,
                "level": 1,
                "position": 0,
                "translations": [],
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/5"
                    }
                }
            }
        ],
        "left": 1,
        "right": 10,
        "level": 0,
        "position": 0,
        "translations": {
            "en_US": {
                "locale": "en_US",
                "id": 1,
                "name": "Category",
                "slug": "category",
                "description": "Consequatur illo amet aliquam."
            }
        },
        "images": [],
        "_links": {
            "self": {
                "href": "\/api\/v1\/taxons\/1"
            }
        }
    },
    "parent": {
        "name": "Category",
        "id": 1,
        "code": "category",
        "children": [
            {
                "name": "T-Shirts",
                "id": 5,
                "code": "t_shirts",
                "children": [],
                "left": 2,
                "right": 7,
                "level": 1,
                "position": 0,
                "translations": [],
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/5"
                    }
                }
            }
        ],
        "left": 1,
        "right": 10,
        "level": 0,
        "position": 0,
        "translations": {
            "en_US": {
                "locale": "en_US",
                "id": 1,
                "name": "Category",
                "slug": "category",
                "description": "Consequatur illo amet aliquam."
            }
        },
        "images": [],
        "_links": {
            "self": {
                "href": "\/api\/v1\/taxons\/1"
            }
        }
    },
    "children": [],
    "left": 8,
    "right": 9,
    "level": 1,
    "position": 1,
    "translations": {
        "en_US": {
            "locale": "en_US",
            "id": 9,
            "name": "toys",
            "slug": "toys",
            "description": "Toys for boys"
        }
    },
    "images": [
        {
            "id": 1,
            "type": "ford",
            "path": "b9/65/01cec3d87aa2b819e195331843f6.jpeg"
        }
    ],
    "_links": {
        "self": {
            "href": "\/api\/v1\/taxons\/9"
        }
    }
}
```

|Field|Description|
|--- |--- |
|id|Id of the taxon|
|code|Unique taxon identifier|
|root|The main ancestor of the taxon|
|parent|Parent of the taxon|
|translations|Collection of translations (each contains slug, name and description in the respective language)|
|position|Position of the taxon among other taxons|
|images|Images assigned to the taxon|
|left|Location within the whole taxonomy|
|right|Location within the whole taxonomy|
|level|How deep the taxon is in the tree|
|children|Descendants of the taxon|



## 4. Updating a category
To fully update a taxon you will need to call the /api/v1/taxons/{code} endpoint with the PUT method.
> **Stateless**  

### 4.1 Request

* URL: /api/v1/taxons/{code}
* Method: PUT, PATCH
* Body: 

```json
{
    "translations": {
        "es_GT": {
            "name": "Dolls",
            "slug": "dolls"
        }
    }
}
```

|Parameter|Parameter type|Description|
|--- |--- |--- |
|code|url attribute|(unique) Identifier of the requested taxon|
|translations[‘localeCode’][‘name’]|request|(optional) Name of the taxon|
|translations[‘localeCode’][‘slug’]|request|(optional) (unique) Slug|
|translations[‘localeCode’][‘description’]|request|(optional) Description of the taxon|
|parent|request|(optional) The parent taxon’s code|
|images|request|(optional) Images codes assigned to the taxon|

> To update a taxon partially you will need to call the /api/v1/taxons/{code} endpoint with the PATCH method.

### 4.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "name": "toys",
    "id": 9,
    "code": "toys",
    "root": {
        "name": "Category",
        "id": 1,
        "code": "category",
        "children": [
            {
                "name": "T-Shirts",
                "id": 5,
                "code": "t_shirts",
                "children": [],
                "left": 2,
                "right": 7,
                "level": 1,
                "position": 0,
                "translations": [],
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/5"
                    }
                }
            }
        ],
        "left": 1,
        "right": 10,
        "level": 0,
        "position": 0,
        "translations": {
            "en_US": {
                "locale": "en_US",
                "id": 1,
                "name": "Category",
                "slug": "category",
                "description": "Consequatur illo amet aliquam."
            }
        },
        "images": [],
        "_links": {
            "self": {
                "href": "\/api\/v1\/taxons\/1"
            }
        }
    },
    "parent": {
        "name": "Category",
        "id": 1,
        "code": "category",
        "children": [
            {
                "name": "T-Shirts",
                "id": 5,
                "code": "t_shirts",
                "children": [],
                "left": 2,
                "right": 7,
                "level": 1,
                "position": 0,
                "translations": [],
                "images": [],
                "_links": {
                    "self": {
                        "href": "\/api\/v1\/taxons\/5"
                    }
                }
            }
        ],
        "left": 1,
        "right": 10,
        "level": 0,
        "position": 0,
        "translations": {
            "en_US": {
                "locale": "en_US",
                "id": 1,
                "name": "Category",
                "slug": "category",
                "description": "Consequatur illo amet aliquam."
            }
        },
        "images": [],
        "_links": {
            "self": {
                "href": "\/api\/v1\/taxons\/1"
            }
        }
    },
    "children": [],
    "left": 8,
    "right": 9,
    "level": 1,
    "position": 1,
    "translations": {
        "en_US": {
            "locale": "en_US",
            "id": 9,
            "name": "toys",
            "slug": "toys",
            "description": "Toys for boys"
        }
    },
    "images": [
        {
            "id": 1,
            "type": "ford",
            "path": "b9/65/01cec3d87aa2b819e195331843f6.jpeg"
        }
    ],
    "_links": {
        "self": {
            "href": "\/api\/v1\/taxons\/9"
        }
    }
}
```

|Field|Description|
|--- |--- |
|id|Id of the taxon|
|code|Unique taxon identifier|
|root|The main ancestor of the taxon|
|parent|Parent of the taxon|
|translations|Collection of translations (each contains slug, name and description in the respective language)|
|position|Position of the taxon among other taxons|
|images|Images assigned to the taxon|
|left|Location within the whole taxonomy|
|right|Location within the whole taxonomy|
|level|How deep the taxon is in the tree|
|children|Descendants of the taxon|


## 5. Removing a category
To delete a taxon you will need to call the /api/v1/taxons/{code} endpoint with the DELETE method.
> **Stateless**  

### 5.1 Request

* URL: /api/v1/taxons/{code}
* Method: DELETE

### 5.2 Response

* HTTP Status: 204 (No content), 403 (Invalid credentials)
* Body: Empty


## 6. Set position of product in a Taxon
Products can by grouped by taxon, therefore for every product there is a relation between the product and the assigned taxon. What is more, every product can have a specific position in the taxon to which it belongs. To put products in a specific order you will need to call the /api/v1/taxons/{code}/products endpoint wih the PUT method.

### 6.1 Request

* URL: /api/v1/taxons/{code}/products
* Method: PUT
* Body: 

```json
{
    "productsPositions": [
        {
            "productCode": "yellow_t_shirt",
            "position": 3
        },
        {
            "productCode": "princess_t_shirt",
            "position": 0
        }
    ]
}
```

> Remember the yellow_t_shirt and princess_t_shirt and womens_t_shirts are just exemplary codes and you can change them for the ones you need. Check in the list of all products if you are not sure which codes should be used.

### 7. Validations
When error on creation/update of category.

Body:
```json
{
   "code": 400,
    "message": "Validation Failed",
    "errors": {
        "children": {
            "translations": {},
            "images": {},
            "code": {
                "errors": [
                    "Please enter taxon code."
                ]
            },
            "parent": {}
        }
    }
}
```