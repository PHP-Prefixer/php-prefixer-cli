# PHP Prefixer REST API CLI

![PHP-Prefixer](https://php-prefixer.com/images/logo/php-prefixer-144x144.png)

A REST client for the PHP-Prefixer service.

[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/tterb/atomic-design-ui/blob/master/LICENSEs)

## Run Locally

Clone the project

```bash
  git clone https://github.com/php-prefixer/php-prefixer-rest-api-cli
```

Go to the project directory

```bash
  cd php-prefixer-rest-api-cli
```

Install dependencies

```bash
  composer update
```

Run the prefixer service

```bash
  ./php-prefixer-rest-api-cli prefix ./tests/Mock/Source ./tests/Mock/Target \
      $PHP_PREFIXER_PERSONAL_ACCESS_TOKEN \
      aaa \
      --github-access-token=$PHP_PREFIXER_GH_TOKEN
```

## Environment Variables

To run this project, you will need to add the following environment variables to your .env file

`API_KEY`

`ANOTHER_API_KEY`


## Documentation

- [PHP-Prefixer Documentation](https://php-prefixer.com/docs)
- [REST API Referece](https://php-prefixer.com/docs/api-documentation/)

## Running Tests

To run tests, run the following command

```bash
phpunit
```

## Contributing

Contributions are always welcome!

See `contributing.md` for ways to get started.

## Security

If you discover a security vulnerability within this package, please send an email to Graham Campbell at team@php-prefixer.com. All security vulnerabilities will be promptly addressed.

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Authors

- [Aníbal Sánchez](https://www.twitter.com/anibal_sanchez)
- [PHP-Prefixer](https://php-prefixer.com/), Desarrollos Inteligentes Virtuales, SL.
