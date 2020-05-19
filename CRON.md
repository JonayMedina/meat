# HOW TO CRONTAB

## Start using the bundle:

```shell
bin/console schedule:list
```

## To run your cron jobs automatically, add the following line to your (or whomever's) crontab:
```
* * * * * /path/to/symfony/install/bin/console schedule:run >> /dev/null 2>&1
```

## Available commands

### list
```shell
bin/console schedule:list
```

## Recommended Cron jobs
```shell
# Send push notifications to FCM servers, 
# In production use supervisor and restart every deploy. 
# https://symfony.com/doc/current/messenger.html#deploying-to-production
messenger:consume push_notification
```
