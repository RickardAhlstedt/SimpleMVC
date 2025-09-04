# SimpleMVC

A lightweight MVC framework with a focus on simplicity, performance, and extensibility.

## Installation

1. You will need [composer](https://getcomposer.org/)
2. With a terminal, navigate to SimpleMVC, and perform `composer install`
3. Rename `env.sample` to `.env`, if the file is missing, please create the file and past the content listed below
3. To run this with the built-in web-server in PHP, execute `php -S 127.0.0.1:8080 -t ./public`
4. Look around, test and have fun, the documentation is coming

## .env-contents
```bash
APP_ENV=dev
APP_DEBUG=true
APP_URL=http://localhost
APP_NAME=SimpleMVC
APP_VERSION=1.0.0
APP_LOCALE=en
APP_TIMEZONE=UTC
```

---

## Roadmap & Feature List

### 1. Core Architecture
- **MVC Structure:** Model, View, Controller separation.
- **Autoloader:** PSR-4 compliant autoloader for classes.

### 2. Dependency Injection & Container
- **Simple Container:** Basic service container for managing dependencies.
- **DI Support:** Constructor injection and service definitions via YAML.

### 3. Configuration
- **YAML Parsing:** YAML parser for config files (e.g., routes, services).
- **Config Loader:** Centralized config loader with caching.

### 4. Routing
- **Route Definitions:** Support routes defined in YAML and via annotations.
- **Route Compilation:** Compile route table for fast lookup (cached PHP class).

### 5. Controllers & Entities
- **Controllers:** Base controller class, auto-discovery via config or annotation.
- **Entities & DTOs:** Simple entity classes, optional DTOs for data transfer.

### 6. Templating Engine
- **PHP Templating:** Simple PHP-based template engine.
- **Template Caching:** Compile templates to cached PHP files for performance.

### 7. Caching System
- **Cache Interface:** Define cache interface.
- **Drivers:** Implement file, MySQL, and Redis drivers.
- **Configurable:** Select cache driver via config.

### 8. CLI Tooling
- **Basic CLI:** Command-line interface for running tasks (e.g., cache clear, compile routes).
- **Command Discovery:** Commands defined in YAML or via annotation, compiled to a command list.

### 9. Compilation & Optimization
- **Class Compilation:** Compile route table, command list, and config to PHP classes for performance.
- **Cache Invalidation:** CLI commands to clear and rebuild caches.

### 10. Extensibility & Documentation
- **Extensible:** Add custom cache drivers, commands, etc.
- **Documentation:** Usage examples and API docs.

---

## Feature Checklist

- [X] PSR-4 Autoloader
- [X] Service Container & DI
- [X] YAML Config Loader
- [X] Routing (YAML & Annotation)
- [X] Route Table Compilation
- [X] Controller Base Class
- [ ] Entity & DTO Support
- [X] PHP Templating Engine
- [X] Template Caching
- [X] Cache Interface & Drivers (File, MySQL, Redis)
- [X] Database abstraction layer and entity-creation
- [X] CLI Tool
- [X] Command Discovery & Compilation
- [X] Cache Invalidation Commands
- [X] EventListener & dispatching
