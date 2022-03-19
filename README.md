# Improf PHP JSON REST API for a personnal coaching platform.

Improf is a platform to offer or seek coaching in various fields.
Users can publish training offers to the community and earn credits or make appointment to available offers and spend their credits.
Credits is a mean to ensure community participation and exchange among members.
This project is a playground to build an ORM, an application using middleware architecture in PHP (PSR-7: HTTP message interfaces) from scratch and use various design patterns. (MVC, FACADE, DEPENDENCY INJECTION ... etc ...)

The work on this project in still ongoing...

## QUICK START

In order to start this project you will need to have php v8.00, mysql 8.00 and apache 2 server installed on your local machine.

-  Enable apache2 mode_rewrite

```bash
# inside /etc/apache2/apache2.conf
<Directory /var/www/html>
	Options Indexes FollowSymLinks
	AllowOverride All
	Require all granted
</Directory>
# enable apache mod_rewrite module
sudo a2enmod rewrite
```

- Setup the database

```bash
# import the .sql file found under /db/improf.sql from root project directory (this file contains a database create statement)
```

- Clone the project directory

```bash
# under /var/www/html
git clone https://github.com/learnWeb3/Improf.git
```

- Install the project dependencies using composer

```bash
cd Improf
composer install
```


## Project Settings

Please have a look at the project settings (available under /config/index.php) :
    - mysql database credentials
    - root project directory location
    - the upload folder path
    - facebook api callback url
    - facebook account client secret
    - facebook account client id 
    - zoom api callback url
    - zoom account client secret
    - zoom account client id
    - business features settings such as credits usage

## API DOCUMENTATION

- The api documentation is available under /doc/ from root project directory
- [online documentation](https://documenter.getpostman.com/view/13953520/UV5TGzhm)

## Project dependencies

- [php-8.0](https://www.php.net/releases/8.0/en.php)
- [apache2](https://httpd.apache.org/)
- [composer](https://getcomposer.org/doc/00-intro.md)
- [mysql-8.0](https://dev.mysql.com/)

## Project Resources
- [Visual Studio Code](https://code.visualstudio.com/)
- [PSR-7](https://www.php-fig.org/psr/psr-7/)