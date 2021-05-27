# PHP Prefixer CLI

![PHP-Prefixer](https://php-prefixer.com/images/logo/php-prefixer-144x144.png)

[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/tterb/atomic-design-ui/blob/master/LICENSEs)

A command-line for the [PHP-Prefixer](https://php-prefixer.com) service. The command calls the **PHP-Prefixer** service using the [REST API](https://php-prefixer.com/docs/rest-api-reference/) to submit a project source code, apply the prefixes, wait and download the results.

**PHP-Prefixer** is a cloud service to apply PHP prefixes to namespaces, functions, helpers, traits, interfaces, etc. Start with a Composer project and a set of dependencies, and prefix all library files at once to generate a consistent prefixed codebase.

Given this sample class declaration:

```php
namespace Carbon;

use Carbon\Exceptions\InvalidDateException;
use DateInterval;
use Symfony\Component\Translation;

class Carbon extends DateTime
{
    const NO_ZERO_DIFF = 01;
...
```

The associated prefixed class declaration, with a new and distinct namespace `ACME`:

```php
namespace ACME\Carbon;

use ACME\Carbon\Exceptions\InvalidDateException;
use DateInterval;
use ACME\Symfony\Component\Translation;

class Carbon extends DateTime
{
    const NO_ZERO_DIFF = 01;
...
```

## Installation

### PHAR

The preferred installation method is with the PHP Prefixer CLI PHAR. You can download the latest PHAR from the most recent [Github Releases](https://github.com/PHP-Prefixer/php-prefixer-cli/releases).

### Composer

You can install PHP-Prefixer CLI globally with Composer:

```sh
composer global require php-prefixer/php-prefixer-cli
```

## Usage

To use the command line, you must create an account on [PHP-Prefixer](https://php-prefixer.com/), and prepare your projects with the prefix definition in the `composer.json` schema.

Before using the command line, we recommend checking the documentation and guides here: <https://php-prefixer.com/docs/>

The command line requires four parameters, and it can receive an additional parameter for GitHub integration:

Parameter | Description
---------|----------
source-directory * | The project source directory.
target-directory *| The target directory where the results are stored.
personal-access-token* | The personal access token, generated on [PHP-Prefixer](https://php-prefixer.com/)/ Settings.
project-id * | The identification of the configured project on [PHP-Prefixer](https://php-prefixer.com/)/ Projects.
--github-access-token | An optional GitHub token to access ´composer.json´ dependencies that are managed in private repositories.

```bash
# Sample command line
php-prefixer-cli prefix \
\
    # Source Directory
    /sample/acme_project \
\
    # Target Directory
    /sample/prefixed_project \
\
    # Personal Access Token
    789|Qkfuf79mLwXBCoEhpxLl12DJbeqUJKs03ZFAq2Nd \
\
    # Project ID
    123456 \
\
    # GitHub Access Token
    --github-access-token=95c889f375458a9b33988af375458a3387ba6
```

### Environment Variables

The command line supports the definition of the parameters as environment variables in a project `.env` file.

```yml
# Sample .env

# Source Directory
SOURCE_DIRECTORY="/sample/acme_project"

# Target Directory
TARGET_DIRECTORY="/sample/prefixed_project"

# Personal Access Token
PERSONAL_ACCESS_TOKEN="789|Qkfuf79mLwXBCoEhpxLl12DJbeqUJKs03ZFAq2Nd"

# Project ID
PROJECT_ID="123456"

# GitHub Access Token
GITHUB_ACCESS_TOKEN="95c889f375458a9b33988af375458a3387ba6"
```

## Documentation

- [PHP-Prefixer Documentation](https://php-prefixer.com/docs)
- [PHP Prefixer CLI](https://php-prefixer.com/docs/command-line)
- [REST API Referece](https://php-prefixer.com/docs/rest-api-reference/)

## Command Line Development

Clone the project:

```bash
  git clone https://github.com/php-prefixer/php-prefixer-cli
```

Go to the project directory:

```bash
  cd php-prefixer-cli
```

Install dependencies:

```bash
  composer update
```

Build the PHAR:

```bash
  php-prefixer-cli app:build
```

To run tests, run the following command:

```bash
phpunit
```

## Contributing

Contributions are always welcome!

See `CONTRIBUTING.md` for ways to get started.

## Security

If you discover a security vulnerability within this package, please send an email to Anibal Sanchez at team@php-prefixer.com. All security vulnerabilities will be promptly addressed.

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Authors

- [Aníbal Sánchez](https://www.twitter.com/anibal_sanchez)
- [PHP-Prefixer](https://php-prefixer.com/), Desarrollos Inteligentes Virtuales, SL.
