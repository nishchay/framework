# Nishchay PHP Framework

Nishchay is open structure php framework which allows us to create web application, REST web services and more.

##### Learn

Learn everything about nishchay [here](https://nishchay.io/learningCenter).

##### Install

This framework is installed using composer only.  Use below command

```
composer create-project nishchay/nishchay MyNewNishchayApp
```

###### Next thing

This installation comes with login and register services and few static pages like about us, terms and help. To make login and register work, do as follows:


1. Update database settings in `settings/configuration/database.php`
2. Execute `db.sql` on your database.
3. Register user `service/account/register`
4. Check `service/account/login` using new registered user
5. Check `service/account/{userId}`, use token returned from login or register service.
