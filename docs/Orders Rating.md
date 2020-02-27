# Orders ratings
Orders API endpoint is /api/v1/orders.

## 1. Give rating to an order
You can update an order with a rating by executing the following request (accepted values null, 1-5):
> **Stateless**  

### 1.1 Request

* URL: /api/v1/orders/{id}
* Method: PUT
* Body: 

```json
{
    "rating": 5
}
```

### 1.2 Response

* HTTP Status: 204 (No content), 403 (Invalid credentials), 400 (Bad request)
* Body: Empty
