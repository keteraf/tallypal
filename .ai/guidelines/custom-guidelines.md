# Custom Guidelines
- At the end of every task run the command `composer test` and fix any errors that arise.
- When you need to check/fix lint or phpstan error or run tests you MUST use these scripts in the composer.josn file: 
  ```json
    {
        "lint": "pint",
        "refactor": "rector",
        "test:lint": "pint --test",
        "test:refactor": "rector --dry-run",
        "test:types": "phpstan analyse",
        "test:unit": "pest --colors=always --parallel @additional_args --coverage --min=90",
        "test:mutate": "pest --colors=always --parallel @additional_args --mutate --min=90",
        "test": [
          "@php artisan config:clear --ansi",
          "@test:lint",
          "@test:refactor",
          "@test:types",
          "@test:unit",
          "@test:mutate"
        ]
    }
    ```


