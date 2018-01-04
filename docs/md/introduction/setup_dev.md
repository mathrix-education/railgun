# Setup for development
This project is designed for UNIX-based systems development.

Make sure you got the following technologies installed on your workstation:

- PHP >= 7.1
- MySQL >= 5.5
- Node.js >= v8 with
    - gulp
    - yarn
    
Then, run then `setup_dev.sh` script:

```bash
./scripts/setup_dev.sh
```

That will setup composer and yarn packages and gulp will generate the mails blade views and the documentation.

You finally need to create a MySQL database and set it credentials in the `.env` file.

Finally, run:

```bash
php artisan migrate --seed
```

Make sure you are working on the dev branch and you are now ready to work with Mathrix API!
