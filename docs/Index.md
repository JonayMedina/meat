# Documentación del API de Administración

## Sincronización

Se creará un item en la cola de sincronización local por cada evento de creación/edición/eliminación de registros dentro de la base de datos.


El API está versionada, la versión actual de esta implementación es la **v1**.
También puede responder en dos formatos diferentes, json por default y XML.


Los servicios se dividen en servicios de lectura y escritura desde la perspectiva de Procasa.

|Entidad|Tipo|
|--- |--- |
| Queue | Lectura/Escritura |
| Coupon | Lectura/Escritura |
| Product & associations | Lectura/Escritura |
| Category | Lectura/Escritura |
| Customer | Lectura |
| Order | Lectura |
| Order Rating | Lectura |


## Documentación de servicios

- [Queue](./Queue.md)
- [Category](./Category.md)
- [Customer](./Customer.md)
- [Order](./Order.md)
- [Order rating](./Order-Rating.md)
- [Coupon](./Coupon.md)
- [Product & associations](./Product.md)
