# Error handling
## General nomenclature
All errors handled by Railgun follow the GraphQL Error handling standard.
Additionally, an error category is available in the `"extensions"` key.
All errors should be reported to the user, except those which belong to the `"internal"` category.

The general shape of an error payload is the following:

```json
{
  "errors": [
    {
      
    }
  ]
}
```

## Validation
Category: `"validation"`

When one or more fields fail to pass the validation rules, the servers throws a validation error.
The validation errors per-fields are available in the `"validation"` field of the `"extensions"` payload.

Extensions content:
```json
{
  "field1":  [
    "message1",
    "message2"
  ],
  "field2":  [
    "message3",
    "message4"
  ]
}
```

Example:
```json
{
   "errors": [
      {
         "message":"Les données soumises pour le champ [updateMe] sont invalides",
         "extensions": {
            "validation":{ 
               "password": [
                  "Le champ mot de passe doit être une chaîne de caractères."
               ]
            },
            "category":"validation"
         }
      }
   ]
}
```
