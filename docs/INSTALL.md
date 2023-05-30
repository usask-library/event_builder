# Event Builder

- [Intended Audience](#intended-audience)
- [Technical Requirements](#technical-requirements)
- [Installation Instructions](#installation-instructions)
- [Troubleshooting](#troubleshooting)


## Intended Audience

This manual is intended for individuals interested in installing, configuring and maintaining the Event Builder
application.

Experience installing and configuring database and web server software, especially MySQL and Apache, will be beneficial.
These steps will require SSH access to the server on which the software will be installed, and some tasks may require
`root` user access to complete.


## Technical Requirements

Event Builder makes use of the [Laravel framework for PHP](https://laravel.com/).
Its server requirements therefore mirror those of [Laravel](https://laravel.com/docs/6.x#server-requirements).

- PHP 7.2.5 (minimum)
  - Event Builder was developed with PHP 7.3
  - some preliminary testing has been done using PHP 8.0
- PHP extensions BCMath, Ctype, Fileinfo, JSON, mbstring, OpenSSL, PDO, Tokenizer, XML, ZipArchive
- Composer
- Apache web server (other web servers may work, but none have been tested)
- MySQL or PostgreSQL
- Git

## Installation Instructions

These instructions assume Apache and MySQL are being used. nginx and PostgreSQl are supported by the Laravel framework
but have not been tested with the Event Builder software.

### Create the Database

Event Builder requires a MySQL or PostgreSQL database to store the person, place and object data, as well as the events
created.  Creating an application specific user is also good practice.

Assuming MySQL is running on the same server as the Event Builder software, the following example shows how you might
create a database, a user with a password, and then grant that user the necessary access to the database. 

```bash
sudo mysql -h localhost -u root -p -e "CREATE DATABASE event_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -h localhost -u root -p -e "CREATE USER 'event_builder'@'localhost' IDENTIFIED BY 'INSERT_PASSWORD_HERE';"
sudo mysql -h localhost -u root -p -e "GRANT ALL PRIVILEGES ON event_builder.* TO 'event_builder'@'localhost';"
```

You may need to adjust the above to suit your environment. For example, if your MYSQL server is running on another
server, or you do not require `root` level access to create databases (in which case `sudo` can be removed from the
above statements).  You may also choose a database or username other than `event_builder` if you prefer.

This process will of course need to be changed if your institution uses PostgreSQL instead of MySQL.

### Install the Software

Create a directory to store the project.  The directory should **not** be directly accessible via the web.  On a typical
Apache web server, this means installing the application in `/var/www` rather than the default web accessible directory
`/var/www/html`.

```bash
sudo mkdir /var/www/event_builder
sudo chgrp apache /var/www/event_builder
sudo chmod 2775 /var/www/event_builder
```

Check out the code from the Git repository into the directory created in the previous step

```bash
sudo git clone -b master git@gitlab.usask.ca:library/dh/event_builder.git /var/www/event_builder
sudo git clone -b stable/1.0.x http://github.com/usask-library/event_builder.git /var/www/event_builder
```

Use Composer to install the necessary packages

```bash
cd /var/www/event_builder
composer install
```

If you are using PHP 8 and Composer warns that your version does not satisfy the requirements or that PHP 7.x is
required, you can have Composer ignore the PHP version check by running the following command

```bash
composer install --ignore-platform-req=php
```

### Configuration the Application

The application stores its settings in a file named `.env` in the `/var/www/event_builder` directory. The example
configuration file provided can be used as a basis for your own config by making a copy of it.  A unique application key
value also needs to be created.

```bash
cp  .env.example  .env
php artisan key:generate
```

You will need to edit the `.env` file and update several values.

`APP_URL` for example should be updated to be the full URL at which your instance of Event Builder will be available.

The `DB_` settings will need to be updated with the details
of the database created above, particularly the `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` values.

The `MAIL_` settings may also need to be updated to match your environment.

The final step in the application configuration is to initialize the database.  The following command will create the
required tables.

```bash
php artisan migrate
```

### Configure the Web Server

The Event Builder directory should not be accessible via the web -- doing so would be a security risk as it would expose
your configuration files, which include the database user and password details.  The only directory that should be
exposed via the web is the `public` directory.  The easiest method to accomplish this is to create a URL Alias,
then define a matching Directory section in your Apache config, like this

```
Alias /event_builder "/var/www/event_builder/public"

<Directory "/var/www/event_builder/public">
    Options +FollowSymLinks -Indexes
    AllowOverride All
    Require all granted
</Directory>
```

Event Builder would then be accessible at the URL `https://your.institution.edu/event_builder/`


## Troubleshooting

### Enable Debugging

The `APP_DEBUG` directive in the `.env` configuration file can be used to enable and disable debugging by the underlying
Laravel framework.  The default setting for this is `false`.  If yoo experience an issue with an apparent blank screen,
or HTTP `500` errors, setting this to `true` may help reveal the cause.

### Permissions

Several directories need to be writable by the web server -- cache and log directory for example.  If, after enabling
debugging mode as described above, you receive an error message that log file could not be opened or is not writable,
it is possible file permissions are to blame.  To ensure the web server user has write access to the necessary files,
try running the following commands

```bash
sudo chown -R apache /var/www/event_builder/storage
sudo chown -R apache /var/www/event_builder/bootstrap/cache/
```

Adjust `apache` in the above commands if the web server runs as a different user in your environment (`httpd` or `nginx`
for example). 

### SElinux

Servers utilizing SElinux may encounter permission issues in addition to those covered by the previous section.
SElinux enfo rces policies in addition to user and group when determining whether a file can be written to by the web
server user.

To view the existing SElinix context, try the following command.

```bash
ls -lZ /var/www/event_builder/storage/logs/
```

If the above command displays `httpd_sys_content_t` instead of `httpd_sys_rw_content_t`, then it is likely that SElinux
is preventing the web server from writing files.  You can attempt to resolve these issues by running the following:

```bash
sudo chcon -R -t httpd_sys_rw_content_t /var/www/event_builder/storage
sudo chcon -R -t httpd_sys_rw_content_t /var/www/event_builder/bootstrap/cache/
```
