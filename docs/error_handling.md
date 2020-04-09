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
      "message": "The error message",
      "extensions": {
        "category": "error-category",
        "foo": "bar",
        "bar": "baz"
      }
    }
  ]
}
```

## Category
Each error should have a `"category"` field in its `"extension"` map.
The category allows the client to determine the actions to take depending on the current context.

There are reserved categories names:

| Category     | Description                                                            |
|--------------|------------------------------------------------------------------------|
| `"internal"` | An unexpected internal error. This should not be reported to the user. |
| `"graphql"`  | GraphQL syntax error                                                   |

### Standard errors
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
            "category":"validation",
            "validation":{ 
               "password": [
                  "Le champ mot de passe doit être une chaîne de caractères."
               ]
            }
         }
      }
   ]
}
```
