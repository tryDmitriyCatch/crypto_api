# CryptoAPI

This is simple API to manage User and his Assets

## Getting Started

A few instruction notes on how to setup project locally

### Installing

Download project locally

Fill in your DB details in .env file. Make sure to create crypto_api Database beforehand

```
composer install

bin/console d:s:u -f

bin/console app:create:users

bin/console app:create:assets
```

You should be good to go and have a few Users and Assets in DB

## Swagger

You can checkout swagger documentation at http://127.0.0.1:8000/api/doc

## Built With

* [Symfony](https://symfony.com/) - The php framework used

## Author

* **Dmitriy Yanov-Yanovskiy**

## License

This project is licensed under the MIT License
