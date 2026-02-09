# php-symfony-sample Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-02-08

## Active Technologies

- PHP 8.2+ with Symfony 7.2 LTS (001-social-profile)
- Doctrine ORM with SQLite
- PHPUnit with symfony/test-pack

## Project Structure

```text
src/
├── Controller/
├── Entity/
├── Form/
├── Repository/
└── EventSubscriber/

config/packages/
templates/
tests/
migrations/
public/uploads/
```

## Commands

```bash
# Install dependencies
composer install

# Database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console make:migration

# Dev server
symfony server:start

# Tests
php bin/phpunit

# Code quality
vendor/bin/php-cs-fixer fix --dry-run
vendor/bin/phpstan analyse src/
```

## Code Style

- PSR-12 via PHP-CS-Fixer
- PHPStan level 6
- Symfony attributes for routing, validation, ORM mapping
- Autowiring for dependency injection

## Recent Changes

- 001-social-profile: User signup, profile wizard, user directory, follow system

<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
