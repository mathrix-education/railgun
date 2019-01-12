# Changelog

## Version 4.3.0
Introducing the paginated format:

```
GET /{route}/{page}/{perPage}

{
  "page": 12,
  "per_page": 100,
  "total": 1524,
  "data": [
    {...},
    {...}
    ...
  ]
}
```

The following routes are now using the paginated format :

- All __index__ routes
- All __by__ routes
