# Laravel Queue `1.0.0`

## Installation

1. Use composer: 
```shell
composer require m-alsafadi/laravel-queue
```
2. Add to `.env` file:
```dotenv
LARAVEL_QUEUE_ENABLED=true
LARAVEL_QUEUE_DISK=local
LARAVEL_QUEUE_SINGLE_PROC=true
LARAVEL_QUEUE_CACHE=false
```
3. Add trait to your model (_optional_):
```php
use \MAlsafadi\LaravelQueue\Traits\TLaravelQueueModel;
```
4. Create job:
```shell
php artisan laravel:queue:job UserJob
```
5. Modify `handle` method in your job at `\App\Jobs\UserJob`
6. Start the worker:
   1. Via artisan command: 
    ```shell
    php artisan larave:queue:start
    ```
   2. Via file: 
    ```shell
    php laravel-queue
    ```

---

#### Publish config:
```shell
php artisan vendor:publish --provider=MAlsafadi\\LaravelQueue\\Providers\\LaravelQueueProvider
```

---

#### How to  add job to queue:
1. Via your job:
```php
\App\Jobs\UserJob::addJob($model, $valid_at, $arguments);
```
2. Via your model:
```php
\App\Models\User::find(1)->addJob(\App\Jobs\UserJob::class, $valid_at, $arguments);
```
3. Via Laravel Queue:
```php
LaravelQueue::addJob($model, \App\Jobs\UserJob::class, $valid_at, $arguments);
```

---

#### Commands:
1. Laravel Queue Helper:
```shell
php artisan laravel:queue
```
2. Create New Laravel Queue Job:
```shell
php artisan laravel:queue:job
```
3. Start Laravel Queue Worker:
```shell
php artisan laravel:queue:start
```

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

The Laravel Queue is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
