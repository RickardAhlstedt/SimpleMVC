# SimpleMVC Framework Documentation

## Table of Contents
1. [Bootstrap Flow](#bootstrap-flow)
2. [Application Flow](#application-flow)
3. [Compilation Process](#compilation-process)
4. [Container Building](#container-building)
5. [Routing & Request Handling](#routing--request-handling)
6. [Event System](#event-system)
7. [Architecture Overview](#architecture-overview)

## Bootstrap Flow

The application starts from `bootstrap.php`, which serves as the entry point web requests.

### 1. Path Definition
```php
define('PATH_ROOT', __DIR__);
define('PATH_CORE', PATH_ROOT . '/src');
define('PATH_APP', PATH_ROOT . '/app');
define('PATH_CONFIG', PATH_ROOT . '/config');
define('PATH_CACHE', PATH_ROOT . '/cache');
define('PATH_TEMPLATE', PATH_ROOT . '/templates');
define('PATH_PUBLIC', PATH_ROOT . '/public');
define('PATH_VENDOR', PATH_ROOT . '/vendor');
define('PATH_LOG', PATH_ROOT . '/logs/app.log');
```

### 2. Environment Loading
```php
\SimpleMVC\Core\Env::load(); // Loads .env file
```

### 3. Application Instantiation & Execution
```php
$app = new Application(PATH_CONFIG, PATH_CACHE);
$app->run();
```

## Application Flow

The `Application::run()` method orchestrates the entire request lifecycle through several phases:

### Phase 1: Compilation
```php
$this->compileIfNeeded();
```
- Checks if compiled cache files exist
- Runs compilation passes if needed:
  - `ConfigCompilerPass` - Compiles YAML configs to PHP arrays
  - `ContainerCompilerPass` - Compiles service definitions
  - `RouteCompilerPass` - Compiles routes from YAML and annotations

### Phase 2: Container Building
```php
$this->buildContainer();
```
- Creates service container instance
- Loads compiled service definitions
- Registers services with dependency injection
- Automatically registers event listeners

### Phase 3: Application Lifecycle Events
```php
$this->dispatch('application.start');
$this->dispatch('application.before_route');
$this->routeApplication();
$this->dispatch('application.after_route');
$this->dispatch('application.end');
```

## Compilation Process

The framework uses a compilation system to optimize performance by pre-processing configuration files, routes, and service definitions.

### Compiler Passes

#### 1. ConfigCompilerPass
- **Purpose**: Compiles YAML configuration files into optimized PHP arrays
- **Input**: `config/*.yaml` files (except routes.yaml)
- **Output**: `cache/config.php`
- **Features**:
  - Environment variable replacement (`%PATH_ROOT%`, etc.)
  - Recursive placeholder processing

#### 2. ContainerCompilerPass
- **Purpose**: Processes service definitions for dependency injection
- **Input**: `config/services.yaml`
- **Output**: `cache/container.php`
- **Features**:
  - Service argument resolution
  - Environment variable substitution

#### 3. RouteCompilerPass
- **Purpose**: Combines YAML routes and annotation-based routes
- **Input**: 
  - `config/routes.yaml`
  - Controllers with `#[Route]` attributes in `app/Controller`
- **Output**: `cache/routes.php`
- **Features**:
  - Route validation (controller/action existence)
  - Automatic route discovery via annotations

### Compilation Triggers
Compilation occurs when cache files don't exist. Each compiler pass is run independently, allowing selective cache rebuilding.

## Container Building

The service container provides dependency injection and service management.

### Container Features
- **Lazy Loading**: Services are instantiated only when requested
- **Singleton Pattern**: Services are cached after first instantiation
- **Dependency Resolution**: Automatic constructor injection
- **Service References**: Support for `@ServiceName` references
- **Parameter Substitution**: Environment and path variables

### Service Definition Example
```yaml
services:
  SimpleMVC\Service\LoggerService:
    arguments:
      - '%PATH_LOG%'
  
  App\Controller\HomeController:
    arguments:
      - '@SimpleMVC\Core\HTTP\RequestStack'
      - '@SimpleMVC\Templating\Templating'
```

### Event Listener Registration
The container automatically registers eventlisteners by:
1. Checking if service classes implement `getSubscribedEvents()`
2. Registering listeners with the EventDispatcher
3. Creating callable references to listener methods

## Routing & Request Handling

### Route Resolution Process

#### 1. Route Matching
```php
$router = new Router($compiledRoutes, $dbRoutes, $discoveredRoutes);
$route = $router->match($uri, $_SERVER['REQUEST_METHOD']);
```

#### 2. Middleware Processing
- Extract middleware from route definition
- Execute middleware chain in order
- Stop execution if any middleware returns false

#### 3. Controller Resolution & Execution
```php
$controller = \SimpleMVC\Core\Container::getInstance()->get($route['controller']);
$action = $route['action'];

// Dependency injection for controller method
$reflection = new \ReflectionMethod($controller, $action);
$parameters = [];
foreach ($reflection->getParameters() as $param) {
    $type = $param->getType();
    if ($type && !$type->isBuiltin()) {
        $service = \SimpleMVC\Core\Container::getInstance()->get($type->getName());
        $parameters[] = $service;
    }
}

$response = $reflection->invokeArgs($controller, $parameters);
```

### Route Definition Methods

#### 1. YAML Routes (config/routes.yaml)
```yaml
routes:
  - name: "about_show"
    path: /about
    method: GET
    controller: App\Controller\AboutController
    action: show
```

#### 2. Annotation Routes
```php
#[Controller]
class HomeController extends AbstractController
{
    #[Route(
        name: 'home_index',
        path: '/',
        method: 'GET'
    )]
    public function index(RequestStack $request): Response
    {
        return new Response($this->render('home.html.twig', ['name' => 'World']), 200);
    }
}
```

## Event System

The framework includes a comprehensive event system for extensibility.

### Event Dispatcher
- **Priority Support**: Listeners can be registered with priorities
- **Event Propagation**: Events can be stopped from propagating
- **Automatic Registration**: Event listeners are auto-registered from service container

### Application Events
- `application.start` - Application initialization
- `application.before_route` - Before route matching
- `application.route_matched` - After successful route match
- `application.middleware_start` - Before middleware execution
- `application.middleware_end` - After middleware execution
- `application.middleware_failed` - When middleware fails
- `application.controller_invoke` - Before controller method call
- `application.controller_invoked` - After controller method call
- `application.after_route` - After route processing
- `application.end` - Application shutdown

### Event Listener Example
```php
class LogRequestEventListener
{
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            'application.route_matched' => 'onRequest',
        ];
    }

    public function onRequest(Event $event): void
    {
        $data = $event->getData();
        $route = $data['route'] ?? 'unknown';
        $this->logger->info("Incoming request to route: {$route}");
    }
}
```

## Architecture Overview

### Directory Structure
```
├── app/                    # Application-specific code
│   ├── Command/           # CLI commands
│   ├── Controller/        # Controllers
│   ├── Entity/            # Data entities
│   ├── EventListener/     # Event listeners
│   └── Middleware/        # Request middleware
├── cache/                 # Compiled cache files
├── config/                # Configuration files
│   ├── app.yaml
│   ├── routes.yaml
│   └── services.yaml
├── src/                   # Framework core
│   ├── Attribute/         # PHP attributes
│   ├── CLI/              # CLI infrastructure
│   ├── Compiler/         # Compilation system
│   ├── Core/             # Core components
│   ├── Discovery/        # Auto-discovery services
│   ├── Event/            # Event system
│   ├── Middleware/       # Middleware interfaces
│   ├── Routing/          # Routing system
│   ├── Service/          # Core services
│   ├── Support/          # Utility classes
│   └── Templating/       # Template engine
├── templates/            # Twig templates
└── public/              # Web-accessible files
```

### Key Design Principles

1. **Performance First**: Compilation-based optimization
2. **Convention over Configuration**: Sensible defaults with override capability
3. **Dependency Injection**: Clean, testable architecture
4. **Event-Driven**: Extensible through event system
5. **PSR Compliance**: Following PHP standards where applicable

### Request Lifecycle Summary

1. **Bootstrap** → Load environment, create application
2. **Compile** → Generate optimized cache files if needed
3. **Container** → Build service container with dependencies
4. **Route** → Match incoming request to route
5. **Middleware** → Execute middleware chain
6. **Controller** → Resolve and execute controller action
7. **Response** → Return response to client
8. **Events** → Dispatch events throughout lifecycle

This architecture provides a balance between simplicity and extensibility, making it suitable for both small applications and larger projects that need room to grow.