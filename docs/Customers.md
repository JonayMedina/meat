# Customers

## Model

|Field|Description|
|--- |--- |
|id|Id of customer|
|user[id]|(optional) Id of related user|
|user[username]|(optional) Users username|
|user[usernameCanonical]|(optional) Canonicalized users username|
|user[roles]|(optional) Array of users roles|
|user[enabled]|(optional) Flag set if user is enabled|
|email|Customers email|
|emailCanonical|Canonicalized customers email|
|firstName|Customers first name|
|lastName|Customers last name|
|gender|Customers gender|
|birthday|Customers birthday|
|group|Customer group code|


## 1. Get customers
Customer list.

> **Stateless**  

### 1.1 Request

* URL: /api/v1/customers/
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
    "page":1,
    "limit":3,
    "pages":10,
    "total":300,
    "_links":{
        "self":{
             "href":"/api/v1/customers/?page=1&limit=3"
        },
        "first":{
             "href":"/api/v1/customers/?page=1&limit=3"
        },
        "last":{
             "href":"/api/v1/customers/?page=101&limit=3"
        },
        "next":{
             "href":"/api/v1/customers/?page=2&limit=3"
        }
    },
    "_embedded":{
        "items":[
             {
                    "id":407,
                    "email":"random@gmail.com",
                    "firstName":"Random",
                    "lastName":"Doe"
             },
             {
                    "id":406,
                    "email":"customer@email.com",
                    "firstName":"Alexanne",
                    "lastName":"Blick"
             },
             {
                    "id":405,
                    "user":{
                         "id":404,
                         "username":"gaylord.bins@example.com",
                         "enabled":true
                    },
                    "email":"gaylord.bins@example.com",
                    "firstName":"Dereck",
                    "lastName":"McDermott"
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
| _embedded.items               | Items wrapper                               | Object | (Required)   |
| _embedded.items.id            | Customer ID                                 | Int    | (Required)   |
| _embedded.items.user          | Related user associated with this customer  | Object | (Optional)   |
| _embedded.items.user.id       | User ID                                     | Int    | (Optional)   |
| _embedded.items.user.username | Username                                    | string | (Optional)   |
| _embedded.items.user.enabled  | Is this user enabled?                       | bool   | (Optional)   |
| _embedded.items.email         | Customer Email                              | String | (Required)   |
| _embedded.items.firstName     | First name                                  | String | (Required)   |
| _embedded.items.lastName      | Last name                                   | String | (Required)   |

## 2. Create customer

Creates a new customer.

> **Stateless**  

### 2.1 Request

* URL: /api/v1/customers/
* Method: POST
* Body:

```json
{
    "firstName": "John",
    "lastName": "Diggle",
    "email": "john.diggle@yahoo.com",
    "gender": "m",
    "user": {
        "plainPassword" : "testPassword"
    }
}
```

| Key                       | Description                                 | Type   | Rules        |
|---------------------------|---------------------------------------------|--------|--------------|
| firstName                 | Customer’s first name                       | string    | (Required)   |
| lastName                  | Customer’s last name                        | string    | (Required)   |
| email                     | (unique) Customer’s email                   | string    | (Required)   |
| gender                    | Customer’s gender                           | string    | (Required)   |
| birthday                  | Customer’s birthday                         | string    | (Optional)   |
| user                      | User model                                  | Object | (Optional)   |
| user.plainPassword        | Users plain password. Required if user account should be created together with customer                             | string | (Optional)   |
| user.enabled              | Flag set if user is enabled                 | bool   | (Optional)   |



### 2.2 Response

* HTTP Status: 201 (Created), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "id":409,
    "user":{
        "id":405,
        "username":"john.diggle@yahoo.com",
        "roles":[
            "ROLE_USER"
        ],
        "enabled":false
    },
    "email":"john.diggle@yahoo.com",
    "emailCanonical":"john.diggle@yahoo.com",
    "firstName":"John",
    "lastName":"Diggle",
    "gender":"m",
    "group":{}
}
```

|Field|Description|
|--- |--- |
|id|Id of customer|
|user[id]|(optional) Id of related user|
|user[username]|(optional) Users username|
|user[usernameCanonical]|(optional) Canonicalized users username|
|user[roles]|(optional) Array of users roles|
|user[enabled]|(optional) Flag set if user is enabled|
|email|Customers email|
|emailCanonical|Canonicalized customers email|
|firstName|Customers first name|
|lastName|Customers last name|
|gender|Customers gender|
|birthday|Customers birthday|
|group|Customer group code|


## 3. Get single customer

You can request detailed customer information by executing the following request:

> **Stateless**  

### 3.1 Request

* URL: /api/v1/customers/{id}
* Method: GET


### 3.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "id":399,
    "user":{
        "id":398,
        "username":"cgulgowski@example.com",
        "usernameCanonical":"cgulgowski@example.com",
        "roles":[
            "ROLE_USER"
        ],
        "enabled":false
    },
    "email":"cgulgowski@example.com",
    "emailCanonical":"cgulgowski@example.com",
    "firstName":"Levi",
    "lastName":"Friesen",
    "gender":"u",
    "group":{}
}
```

|Field|Description|
|--- |--- |
|id|Id of customer|
|user[id]|(optional) Id of related user|
|user[username]|(optional) Users username|
|user[usernameCanonical]|(optional) Canonicalized users username|
|user[roles]|(optional) Array of users roles|
|user[enabled]|(optional) Flag set if user is enabled|
|email|Customers email|
|emailCanonical|Canonicalized customers email|
|firstName|Customers first name|
|lastName|Customers last name|
|gender|Customers gender|
|birthday|Customers birthday|
|group|Customer group code|


## 4. Updating a customer
You can request full or partial update of resource. For full customer update, you should use PUT method.

> **Stateless**  

### 4.1 Request

* URL: /api/v1/customers/{id}
* Method: PUT, PATCH
* Body: 

```json
{
    "firstName": "John",
    "lastName": "Diggle",
    "email": "john.diggle@example.com",
    "gender": "m"
}
```


### 4.2 Response

* HTTP Status: 200 (Ok), 403 (Invalid credentials), 400 (Bad request)
* Body:

```json
{
    "id":399,
    "user":{
        "id":398,
        "username":"cgulgowski@example.com",
        "usernameCanonical":"cgulgowski@example.com",
        "roles":[
            "ROLE_USER"
        ],
        "enabled":false
    },
    "email":"cgulgowski@example.com",
    "emailCanonical":"cgulgowski@example.com",
    "firstName":"Levi",
    "lastName":"Friesen",
    "gender":"u",
    "group":{}
}
```

|Field|Description|
|--- |--- |
|id|Id of customer|
|user[id]|(optional) Id of related user|
|user[username]|(optional) Users username|
|user[usernameCanonical]|(optional) Canonicalized users username|
|user[roles]|(optional) Array of users roles|
|user[enabled]|(optional) Flag set if user is enabled|
|email|Customers email|
|emailCanonical|Canonicalized customers email|
|firstName|Customers first name|
|lastName|Customers last name|
|gender|Customers gender|
|birthday|Customers birthday|
|group|Customer group code|


## 5. Removing a customer

> **Stateless**  

### 5.1 Request

* URL: /api/v1/customers/{id}
* Method: DELETE

### 5.2 Response

* HTTP Status: 204 (No content), 403 (Invalid credentials)
* Body: Empty


## 6. Valdations
When error on creation/update of customer.

Body:
```json
{
    "code": 400,
    "message": "Validation Failed",
    "errors": {
        "children": {
            "firstName": {},
            "lastName": {},
            "email": {
                "errors": [
                    "Please enter your email."
                ]
            },
            "birthday": {},
            "gender": {
                "errors": [
                    "Please choose your gender."
                ]
            },
            "phoneNumber": {},
            "subscribedToNewsletter": {},
            "group": {}
        }
    }
}
```