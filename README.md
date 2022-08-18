# PHP Prefixer CLI

![PHP-Prefixer](https://php-prefixer.com/images/logo/php-prefixer-144x144.png)

[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/tterb/atomic-design-ui/blob/master/LICENSEs)

A command-line for the [PHP-Prefixer](https://php-prefixer.com) service.

**Blog post announcement**: [New PHP-Prefixer CLI: Prefix from the Terminal](https://blog.php-prefixer.com/2021/06/12/new-php-prefixer-cli-prefix-from-the-terminal/)

The [PHP-Prefixer](https://php-prefixer.com) service has a command-line (CLI) to use the service locally and process the project source code from your workstation.

The command calls the **PHP-Prefixer** service using the [REST API](https://php-prefixer.com/docs/rest-api-reference/) to submit a project source code, apply the prefixes, wait and download the results.

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

## How to install the CLI?

You can install the CLI using two ways:

### 1. How to install the PHAR CLI

The preferred installation method is with the **PHP Prefixer CLI PHAR**. You can download the latest PHAR from the most recent [Github Releases](https://github.com/PHP-Prefixer/php-prefixer-cli/releases). After downloading it, copy the file into a directory on your local path and assign the execution permissions to run the following commands.

### 2. Install via Composer

Alternatively, you can install **PHP-Prefixer CLI** globally with Composer:

```sh
composer global require php-prefixer/php-prefixer-cli
```

The source code is available here: <https://github.com/PHP-Prefixer/php-prefixer-cli>

## Usage

To use the CLI, you must create an account on [PHP-Prefixer](https://php-prefixer.com/) and prepare your projects with the prefix definition in the `composer.json` schema.

Before using the command-line, we recommend checking the documentation and guides here: <https://php-prefixer.com/docs/>

The CLI requires four parameters to function, and it can receive an additional parameter for GitHub integration:

Parameter | Description
---------|----------
source-directory * | The project source directory
target-directory *| The target directory where the results are stored
personal-access-token* | The personal access token, generated on [PHP-Prefixer](https://php-prefixer.com/) Settings
project-id * | The identification of the configured project on [PHP-Prefixer](https://php-prefixer.com/) Projects
--github-access-token | An optional GitHub token to access ´composer.json´ dependencies that are managed in private repositories
--include-vendor | include the local preinstalled vendor direcory in the build
--include-all | include all files from the source direcory instead of only composer relevant and php files

```bash
# Sample command-line
./php-prefixer-cli prefix \
\
    /sample/acme_project \
\
    /output/prefixed_project \
\
    "789|1234567890123456789012345678901234567890" \
\
    123456 \
\
    --github-access-token=1234567890123456789012345678901234567890 \
\
    --include-vendor
```

### Environment Variables

The CLI supports the definition of the parameters as environment variables in a project `.env` file.

```yml
# PHP Prefixer CLI - Sample .env

# Note: the .env file must be located in the php-prefixer-cli.phar directory

# Source Directory: The project source directory
SOURCE_DIRECTORY="/sample/acme_project"

# Target Directory: The target directory where the results are stored
TARGET_DIRECTORY="/output/prefixed_project"

# Personal Access Token: The personal access token, generated on PHP-Prefixer Settings
PERSONAL_ACCESS_TOKEN="789|1234567890123456789012345678901234567890"

# Project ID: The identification of the configured project on PHP-Prefixer Projects
PROJECT_ID="123456"

# GitHub Access Token:  An optional GitHub token to access composer.json dependencies that are managed in private repositories.
GITHUB_ACCESS_TOKEN="1234567890123456789012345678901234567890"
```

## Documentation

- [PHP-Prefixer Documentation](https://php-prefixer.com/docs)
- [PHP Prefixer CLI](https://php-prefixer.com/docs/command-line)
- [REST API Reference](https://php-prefixer.com/docs/rest-api-reference/)

## Command-Line Development

If you want to customize the command-line or help us in the development, please, check the following steps:

Step 1: Clone the project:

```bash
git clone https://github.com/php-prefixer/php-prefixer-cli
```

Step 2: Go to the project directory:

```bash
cd php-prefixer-cli
```

Step 3: Install dependencies:

```bash
composer update
```

Step 4: Build the PHAR:

```bash
./php-prefixer-cli app:build
```

Step 5: To run tests, execute the following command:

```bash
phpunit
```

## Roadmap / Ideas

This roadmap is subject to change and should only be used as a general guideline regarding future releases. As long as a version, feature or application is not yet released, the dates are estimated and could be altered.

- Parameter to exclude directories from ZIP, `--exclude`

## Contributing

The current CLI is a starting point to prefix PHP code. If you want to improve the current commmand-line, contributions are always welcome!

See [CONTRIBUTING.md](https://github.com/PHP-Prefixer/php-prefixer-cli/blob/main/CONTRIBUTING.md) for ways to get started.

## Security

If you discover a security vulnerability within this package, please email to Anibal Sanchez at team@php-prefixer.com. We address all security vulnerabilities promptly.

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Authors

- [Aníbal Sánchez](https://www.twitter.com/anibal_sanchez)
- [PHP-Prefixer](https://php-prefixer.com/), Desarrollos Inteligentes Virtuales, SL.
