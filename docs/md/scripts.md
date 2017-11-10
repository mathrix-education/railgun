# Scripts

## import_from_v2.sh

Download tables from mathrix.fr as json, import them into the database throught the validator.

### Executing

As:

- mathrix_dev on web00@mathrix.fr
- mathrix on web01@mathrix.fr

Branch dev
```
cd /var/www/dev/api/current && scripts/import_from_v2.sh
```
Branch stage
```
cd /var/www/stage/api/current && scripts/import_from_v2.sh
```
Branch master
```
cd /var/www/master/api/current && scripts/import_from_v2.sh
```

### Tables imported (in this order)

__Content__
- countries
- classes
- disciplines
- chapters
- notions
- activities
- modules
- class_discipline
- activity_notion
- activity_module

__Blog__
- categories
- posts

__Users__
- users
- subscriptions