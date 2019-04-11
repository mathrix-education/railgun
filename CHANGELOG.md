# Changelog

## Version 5.0.0


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

- All __index__ routes (ie. `/{models}/[page/perPage]`)
- All __by__ routes  (ie. `/{models}/by-{model}/[page/perPage]`)

Example: `GET /modules/by-activity/{page}/{perPage}`
```
[
  {module1},
  {module2},
  {module3},
  {module4},
  {module5}
]
```
become:
```
{
  "page": 1,
  "per_page": 100,
  "total": 5,
  "data": [
    {module1},
    {module2},
    {module3},
    {module4},
    {module5}
  ]
}
```
