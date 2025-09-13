# Jobs and queues
SimpleMVC features a job-system to offload long-running tasks to the background.

To dispatch a worker you need to execute the following command:
```bash
bin/console queue:work
```
and the worker will begin to reserve jobs from the queue.

## Lifecycle of a dispatch

### Dispatching a job
First we need to grab the instance of the queue-driver
```php
$queue = Container::getInstance()?->get(\SimpleMVC\Queue\DatabaseQueueDriver::class);
$queue->dispatch('Jobname', [payload], delay = 0);
# or
Queue::dispatch('Jobname', [payload], delay = 0);
```

#### Parameters
> - `string $jobName` – the job (handler) to take care of the payload
> - `array $payload = []` – the payload that gets passed to the handler
> - `int delay = 0` – when the job is supposed to be executed

### Worker-processing
The worker **runs at 1 second interval**, and looks at the table named jobs for available work, and once it finds it the worker reserves it (i.e consumes the job).  
Next thing, the worker looks at what job is set to handle the payload, and tries to resolve it against the [JobRegistry](../src/Queue/JobRegistry.php).

If the worker finds a handler, it executes the handler-function defined by the job, derived from the [JobInterface](../src/Queue/JobInterface.php).  
If the job fails, the payload and handle is removed from the database and the worker will log an exception to the CLI.  
If the worker can't resolve a handler, the worker will log the missing handler-name to the console.

## Creating a job
To create a job, create a class in the `App\Jobs`-namespace, and implement the [JobInterface](../src/Queue/JobInterface.php).

Example:
```php
<?php

namespace App\Jobs;

use SimpleMVC\Queue\JobInterface;

class TestJob implements JobInterface
{

    public function handle(array|string $payload): void
    {
        var_dump($payload);
        return;
    }
}
```

## Registering a job
[Bootstrap.php](../src/Core/Bootstrap.php) will create a new jobregistry and look for classes in `SimpleMVC\Jobs – src/Jobs` and `App\Jobs – app/Jobs`, if the found class implements the JobInterface, it will be added to the JobRegistry automatically.