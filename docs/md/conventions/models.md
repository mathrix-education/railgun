# Model conventions

## Class header

Generated with the following command:

```bash
php artisan ide-helper:models -W
```

Do not forget ton remove the `@mixin \Eloquent` mixin at the end of the class header comment.

Sort the model properties (`@property`) in the table order, same for the methods (`@method`).


## Method groups

The methods should be ordered in the following order:

1. Relation methods, alphabetically ordered.
2. Attributes methods, alphabetically ordered.
3. Other methods.
