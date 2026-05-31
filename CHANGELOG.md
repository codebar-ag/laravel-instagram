# Changelog

All notable changes to `laravel-instagram` will be documented in this file.

## v13.0.0

### Added
- Support for Laravel 13 and PHP 8.5.
- A custom exception hierarchy under `CodebarAg\LaravelInstagram\Exceptions`
  (`InstagramException` base, plus `InstagramAuthenticationException`,
  `InstagramConfigurationException`, `InstagramResponseException`) replacing generic
  `\Exception` throws.
- `CodebarAg\LaravelInstagram\Contracts\InstagramHandlerContract`, bound in the
  container to `InstagramService`, so the handler can be injected and mocked.
  The static `InstagramHandler::connector()` / `InstagramHandler::user()` API is
  preserved as a convenience facade.
- Required-field validation in the `InstagramUser` and `InstagramImage` DTOs and a
  guard around the OAuth token response.

### Changed
- **Breaking:** Dropped support for Laravel 12 and PHP 8.2 (Laravel 13 requires PHP 8.3+).
- Tightened Saloon constraints to `saloonphp/saloon ^4.0` and `saloonphp/laravel-plugin ^4.3`.

### Removed
- The leftover `ray()` debug call in the user response transformer.
- The deprecated `InstagramAuthenticator::serialize()` / `::unserialize()` methods.
  Legacy PHP-serialized cache payloads are still read by `decodeFromCache()`.
