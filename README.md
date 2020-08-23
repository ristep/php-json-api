# php-json-api

## Simple JSON single endpoint API writhen with PHP and PDO

I started this project some time ago just for testing purposes. It was meant to be used in my ReactJS experiments. But I think is quite useful for small real life projects.

Do it simple bro, is the main motto in this project. All other API for remote db access are to complex for my taste.

Actually this is only a JSON wrapper for SQL, or transformer, transforms JSON to SQL statement then execute that statement and return result in JSON encoded object.

All requests are expected as POST requests, for sake of simplicity.

## Getting Started

Just download or clone this repo on your LAMP server and compose :)

Run composer in the project directory. 

>
>composer install
>

composer will download "firebase/php-jwt" in /vendor folder. 

If you don't need authentication and tokens just comment the corresponding line in index.php.

```php

$tokenData = false;

//$tokenData = require_once('tokening.php'); // for user validation uncomment

```

## Prerequisites

MySQL, PHP with PDO enabled on an apache2 webserver. Probably will work on eny http sever with PHP but is not tested, yet.

Full source code for testing app can be found here ong github [ristep/json-api-react-test](https://github.com/ristep/json-api-react-test)

## Using

Just send post request whit JSON encoded Data

```js
// Example request JSON
{
    "get":{
        "type":"users",
        "filter":{
            "email like": "%gmail.com"
        },
        "page":{
            "limit": 2,
            "offset": 2
        },
        "attributes":[
            "name","first_name","second_name","email","place"
        ]
    }    
}
```

this will be transformed in to SQL statement:

```SQL
SELECT id,name,first_name,second_name  FROM users WHERE email like '%gmail.com' limit 2 offset 0;
```

and if everything is OK service will return something like this

```json
{
    "meta": {
        "OK": true,
        "count": 2
    },
    "data": [
        {
            "type": "users",
            "id": 3,
            "attributes": {
                "name": "admin",
                "first_name": "Root Admin",
                "second_name": "Adminov",
                "email": "addm@localhost.com",
                "place": "Кавадарци"
            }
        },
        {
            "type": "users",
            "id": 5,
            "attributes": {
                "name": "mavro",
                "first_name": "Mavricie",
                "second_name": "Mavrovskic",
                "email": "nema@gmail.com",
                "place": "Маврово"
            }
        }
    ]
}
```

If is 'id' in request:

```json
{
    "get":{
        "type":"users",
        "id":5, 
        "attributes":[
            "name","first_name","second_name","email","place"
        ]
    }    
}
```

server should return single record:

```json
{
    "meta": {
        "OK": true,
        "count": 1
    },
    "data": {
        "type": "users",
        "id": 5,
        "attributes": {
            "name": "mavro",
            "first_name": "Mavricie",
            "second_name": "Mavrovskic",
            "email": "nema@gmail.com",
            "place": "Маврово"
        }
    }
}
```

Every data table in the database should have 'id' field.

## Examples

For live example and the complete documentation  go to this link:

(yet to be done)

>
> <a href="https://jsonApi-test.sman.cloud/" target="_blank">jsonApi-test.sman.cloud</a>
>

## Built With

* [PHP](https://www.php.net/) - PHP is a popular general-purpose scripting language that is especially suited to web development.
* [PDO](https://www.php.net/manual/en/book.pdo.php) - PHP Data Objects
* [php-jwt](https://github.com/firebase/php-jwt) - A simple library to encode and decode JSON Web Tokens (JWT) in PHP, conforming to RFC 7519.

## Contributing

Please read [CONTRIBUTING.md](./CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/ristep/SimpJ2J/tags).

## Author

[Riste Panovski](https://github.com/ristep)
