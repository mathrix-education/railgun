# Mathrix.fr REST API
[![pipeline status](https://gitlab.com/mathrix/mathrix-api/badges/master/pipeline.svg)](https://gitlab.com/mathrix/mathrix-api/commits/master)

Written by Mathieu Bour for Mathrix.

- [Documentation](https://docs.institut.mathrix.fr)
- [Lumen Utils](https://gitlab.com/mathrix/lumen-utils)


### Installation
1. Install PHP 7.1 with mysql, mbstring, intl and dom extensions.
2. Clone this repository on your local workspace.
3. Start your ssh-agent and add our SSH key located in `storage/ssh/gitlab_mathrix_id_rsa` to your identities.
4. Run `composer install` to setup the project files.
5. Run `php artisan migrate:refresh --seed` to setup a working database.
6. Run `php artisan ide-helper:generate` to generate PHPDoc for Laravel Facades.


### Gulp tasks
#### apidoc
Build apidoc.

#### mjml
Build all .html and .php files from .mjml files.

#### mjml-watch
Watch and build .html and .php file matching with .mjml on modification.


### Testing
#### Locally with PHPUnit only
1. Ensure that PHPUnit is installed by running `vendor/phpunit/phpunit/phpunit --version`
2. Run `vendor/phpunit/phpunit/phpunit --configuration ./phpunit.xml`

#### Locally with GitLab Runner 
1. Install [GitLab Runner](https://docs.gitlab.com/runner/install/) with Docker
2. Run `gitlab-runner exec docker test:app`

