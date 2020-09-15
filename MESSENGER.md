# Messenger

Recomendaciones para usar en producción.

1) Usar forever o supervisor para mantener la tarea activa siempre.
2) Reiniciar la tarea tras cada deploy (investigar como...)

```
php bin/console messenger:consume push_notification
# use -vv to see details about what's happening
php bin/console messenger:consume push_notification -vv
```

## Configure transport
php bin/console messenger:setup-transports
