En el API de productos/category obtenes algo como:

```
[...]
"images": [
    {
        "code": null,
        "path": "5a/2b/2eb047a9d1be7511c812b6c7d5e1.jpeg",
        "cached_path": "https://meathouse-assets.s3.amazonaws.com/media/cache/sylius_shop_api/5a/2b/2eb047a9d1be7511c812b6c7d5e1.jpeg"
    }
]
[...]
```


```
# vas a concatenar

http://meathouse.tribalworldwide.gt/media/cache/resolve/mobile_thumbnail/7c/8b/a63e4e2d2a3ff04f407f1e4b112e.jpeg
|------------- Host URL -----------|------------- Constant -------------|------------ PATH del API -------------|
```
