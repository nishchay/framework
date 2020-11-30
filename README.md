# Nishchay PHP Framework
<p align="center">
  <a href="https://nishchay.io">
      <img src="https://static.nishchay.io/resources/images/nishchay.png"/>
  </a>
</p>

![PHP Version Support](https://img.shields.io/packagist/php-v/nishchay/nishchay)
![Latest version](https://img.shields.io/packagist/v/nishchay/nishchay.svg)
![Downloads](https://img.shields.io/packagist/dt/nishchay/nishchay.svg)

Nishchay is open structure php framework which allows us to create web application, REST web services and more.

## Install

This framework is installed using composer only.  Use below command

```
composer create-project nishchay/nishchay MyNewNishchayApp
```

## Installaion guidelines

* [Linux Ubuntu](https://nishchay.io/learningCenter/installation/ubuntu/composer)
* [Linux CentOS](https://nishchay.io/learningCenter/installation/centos/composer)
* [Windows](https://nishchay.io/learningCenter/installation/windows/composer)
* [MacOS](https://nishchay.io/learningCenter/installation/macos/composer)


## Learn

Learn everything about nishchay [here](https://nishchay.io/learningCenter).

## Next thing

This installation comes with login and register services and few static pages like about us, terms and help. To make login and register work, do as follows:


1. Update database settings in `settings/configuration/database.php`
2. Execute `db.sql` on your database.
3. Register user `service/account/register`
4. Check `service/account/login` using new registered user
5. Check `service/account/{userId}`, use token returned from login or register service.
