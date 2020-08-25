# Tracking Task

## About Tracking Task

Tracking Task is a web application build with laravel(back-end) and angular(frontend).
Upload date : 

## Domain requirements

todo.vietguys.biz

## Hardware requirements

Just for demo

### Setup Pm2 to run Queue

Install PM2

```bash
sudo npm i -g pm2
```
Configure PM2 file for Laravel queue
Create laravel-queue-worker.yml file on the root of the Laravel project and put the following code.

```bash
apps:
  - name: laravel-queue-worker
    script: artisan
    exec_mode: fork
    interpreter: php
    instances: 1
    args:
      - queue:work
      - --tries=5
      - --sleep=1
```

Run and Monitor queue from PM2

```bash
pm2 start laravel-queue-worker.yml
```
View log and monitor the queue process.

```bash
pm2 logs laravel-queue-worker
```

### PHP Extensions

Please change version according to your development server or use __IaC__ to manage all your configuration.

```bash

sudo apt install php7.3-common php7.3-mysql php7.3-xml php7.3-xmlrpc php7.3-curl php7.3-gd php7.3-imagick php7.3-cli php7.3-dev php7.3-imap php7.3-mbstring php7.3-opcache php7.3-soap php7.3-zip php7.3-intl -y

```

What will you install with the command above.

* php-common
* php-mysql
* php-xml
* php-xmlrpc
* php-curl
* php-gd
* php-imagick
* php-cli
* php-dev
* php-imap
* php-mbstring
* php-opcache
* php-soap
* php-zip
* php-intl

If you don't have Composer, install it by running (this will install composer __globally__):

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

Install dependencies via composer:

```bash
composer install
composer dump-autoload
```

Import blank database:

```bash
php artisan migrate
php artisan db:seed
```

Config environment at `.env` file, example at `.env.example`

```bash
cp .env.example .env
```

After copy the `.env`, Edit it to match your `database`, `mail server` 

### Database

```bash

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={your_database_name}
DB_USERNAME={your_mysql_username}
DB_PASSWORD={your_mysql_password}
```

### Mail server

```bash
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME={your_mailservice_username}
MAIL_PASSWORD={your_mailservice_password}
MAIL_ENCRYPTION=tls
MAIL_FROM_NAME={your_name ex: VietGuys}

```

### Update Domail Frontend
Defaul local: http://localhost:4200/#/

```bash
REAL_DOMAIN={your_frontend_domain}
```
### Update Cors URL in App\Http\Middleware\cors.php
default_origin_frontend_local: http://localhost:4200

```bash
header('Access-Control-Allow-Origin', {origin_url_frontend})
```

Generate laravel Application key

```bash
php artisan key:generate
```

## Development

Running development mode with artisan:

```bash
php artisan serve
```

## Directory Permissions

Nginx use www-data as one of the default user.

```bash
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
sudo chmod -R 755 your_project_directory
chmod -R o+w your_project_directory/storage/
```
