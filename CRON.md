# HOW TO CRONTAB

## Start using the bundle:

```shell
bin/console schedule:list
```

## To run your cron jobs automatically, add the following line to your (or whomever's) crontab:
```
* * * * * /path/to/symfony/install/bin/console schedule:run >> /dev/null 2>&1
```
