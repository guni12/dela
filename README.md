(__"dELa"__) guni12 /Schoolproject at BTH; "Ramverk1"
==================================

[![Build Status](https://travis-ci.org/guni12/dela.svg?branch=master)](https://travis-ci.org/guni12/dela)
[![Build Status](https://scrutinizer-ci.com/g/guni12/dela/badges/build.png?b=master)](https://scrutinizer-ci.com/g/guni12/dela/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/guni12/dela/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/guni12/dela/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/guni12/dela/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/guni12/dela/?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/2faf2369720e7502efd6/maintainability)](https://codeclimate.com/github/guni12/dela/maintainability)



### Clone the dELa Frontend Code


```
git clone https://github.com/guni12/dela.git
```
or you can download the zip


### Fix the database

Look in the `sql`-folder. There you find the structure of the database tables in `sqlfile.sql`. Initiate the two tables `comm`and `user`. Then you can insert the supplied values if you prefer, but that is not necessary.

You also find an example of how to set up the mysql database.php file. Keep the name database.php and place it in the `config`-folder.

### If you prefer sqlite:

```
mkdir data
chmod 777 data
```
Modify `database.php`:
```
"driver_options"  => null,
"dsn" => "sqlite:" . ANAX_INSTALL_PATH . "/data/db.sqlite",
```


### Install with composer

```
composer install
```

Now you should be ready to go!


License
------------------

This software carries a MIT license.



```
 .  
..:  Copyright (c) 2017 Gunvor Nilsson (gunvor@behovsbo.se)
```
