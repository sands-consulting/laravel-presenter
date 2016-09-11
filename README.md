# Sands\Presenter

Automatic response presenter for Laravel 5+. Automatically sends your responses as Blade View, or JSON.

Looking for other types of responses? View the [plugins section](#available-plugins) below. You can also [create your own](#creating-your-own-presenter) presenter.


## Installation

```bash
$ composer require sands/laravel-presenter
```

In `config/app.php` add `Sands\Presenter\PresenterServiceProvider` inside the `providers` array:

```php
'providers' => [
     ...
     Sands\Presenter\PresenterServiceProvider::class,
     ...
]
```

In `app/Http/Controllers/Controller.php` add the `Sands\Presenter\PresentsResponses` trait so that it can be used by all your controllers:

```php
// app/Http/Controllers/Controller.php
...
use Sands\Presenter\PresentsResponses;
...
class Controller extends BaseController
{
    use PresentsResponses;
}
```

## Usage

Let's say you have a controller `UsersController` that is consumed by both the web and the mobile. For the web you'd want to return a HTML document generated by the Blade view and for the mobile you want to return the data in JSON.

In the `index` method of the controller you can present your data as such:

```php
public function index()
{
    return $this->present(['users' => User::paginate()])
        ->using('blade', 'json');
}
```

Laravel will automatically return the response in the preferred format. Format detection is done via the `Accept` header or via the `extension` request parameter.

Request `Accept` header with the `application/json` value will return the `json` format while `text/html` will return a rendered Blade view.

You can also define the response type via the `presentUsing` route parameter:

```php
// in your routes file
Route::get('/users/export.{presentUsing}', 'UsersController@index');
```

So when your users hit the `users/export.json` route, Laravel will return the response as JSON.

### Custom Data

There are times where you would want to return different data for different presentations. For instance you would want to return a Paginated set when rendering a Blade view but when accessing via JSON, you would want all the data to be available. For this, you can use the `setOption` method as below:

```php
public function index()
{
    return $this->present()
        ->setOption('data.blade', 'data')    // call this method to get data for blade
        ->setOption('data.json', 'jsonData') // call this method to get data for JSON
        ->setOption('data', 'data')          // default data method
        ->using('json', 'blade');
}

public function data()
{
    return ['users' => User::paginate()];
}

public function jsonData()
{
    return ['users' => User::all()];
}
```

By default the Presenter will look for `data.{presenterName}` option and call the method on the controller to get the data. If that does not exists then it will look for the `data` option and call the method on the controller. If that option is not set then it will return the data passed when `present` is called.

To avoid calling expensive DB operations for all the data methods, The method is called just before the presenter `render` method is called. This is done outside the context of the controller. As the data methods are called outside the controller, the visibility of the method should be `public`. 

> You should take into consideration of the naming convention for Laravel 5.0 - 5.2 controller methods so that controllers registered via `Route::controller` does not accidentally expose these data methods as routes.

You can also place these methods into a separate trait file so that your controller is not cluttered with data methods.

### Built-in Presenters

By default `Sands\Presenter` comes with three default presenters:

- blade (html)
- json

You can install additional [plugins](#available-plugins). Or even [create your own](#creating-your-own-presenter) as needed.

### Blade View Response

The Blade view path is auto calculated from the fully qualified Controller class name, with the `App\Http\Controllers\` prefix and `Controller` suffix removed and the current method that is invoked for the controller. For instance, calling the `App\Http\Controllers\Auth\FacebookAuthController@show` method will have the presenter load the Blade view `auth.facebook-auth.show`.

The controller prefix can be overridden by calling the `setOption` method when calling `present`:

```php
namespace App\Controllers;
...
public function index()
{
    return $this->present(['users' => User::paginate()])
        ->setOption('controllerPrefix', 'App\\Controllers')
        ->using('blade', 'json');
}
```

The controller suffix can be overridden by calling the `setOption` method when calling `present`:

```php
class UsersControllers {
...
public function index()
{
    return $this->present(['users' => User::paginate()])
        ->setOption('controllerSuffix', 'Controllers')
        ->using('blade', 'json');
}
```

The view path can be overridden by calling the `setOption` method when calling `present`:

```php
public function index()
{
    return $this->present(['users' => User::paginate()])
        ->setOption('view', 'some.blade.path')
        ->using('blade', 'json');
}
```

### JSON Response

The JSON response will return the data passed to it as JSON. This is particularly useful for mobile app to consume.

**Formatting JSON**

Optionally, you can transform the JSON using [spatie/laravel-fractal]() package *(not included with this package)* by telling the presenter to use a custom JSON data method:

```php
public function index()
{
    return $this->present()
        ->setOption('data', 'data')
        ->setOption('data.json', 'jsonData') // call this method to get data for JSON
        ->using('json', 'blade');
}

public function jsonData()
{
    return [
        'users' => fractal()
            ->collection(User::all()
            ->transformWith(new UserTransformer())
            ->toArray();
    ];
}
```


### Creating Your Own Presenter

Creating your own presenter is very simple. Your custom presenter class would need to implement the `Sands\Presenter\PresenterContract`. The presenter contract will expect your implementation to have the `__construct` and `render` method. The `render` method must return an instance of `Illuminate\Http\Response` or any data that can be consumed by it. 

The `__construct` method will have the `presenter` instance as the only argument. Typically you would attach the `presenter` as the class property.

```php
use Sands\Presenter\Presenter;
use Sands\Presenter\PresenterContract;
...
class PdfPresenter implements PresenterContract {
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }
...
```

All responses are lazy instantiated. This means that the presenter will only be loaded and instantiated when the presenter's `render` method needs to be called.

The `render` method will have a `$data` variable passed as the first argument. It must return an instance of `Illuminate\Http\Response` or a value that can be consumed by the class by it.

```php
public function render($data = [])
{
    $viewPath = $this->presenter->getOption('view.pdf');
    return PDF::loadView($viewPath, $data)->stream();
}
```

**Available Options**

Typically, options are set by the user by using the `setOption` method. These options are available for the use inside your custom presenter by calling the `$this->presenter->getOption('key')` method where `key` is the option you are looking for. If the option is not set, it will return `null`.

To get all options, use `$this->presenter->getOptions()` method.

By default, these options are available for you:

1. `controllerPrefix`: `App\Http\Controllers`
2. `controllersSuffix`: `Controllers`
3. `controller`: The current called controller e.g.: `App\Http\Controllers\UsersController`
4. `method`: The current called controller method e.g.: `index`
5. `routeParams`: The current route params.

**Registering Your Presenter**

To register your presenter, just call the `register` method:

```php
app('sands.presenter')->register('pdf', [
    'presenter' => \App\Presenters\Pdf::class,
    'mimes' => [ // optionally bind to these mimes
        'application/pdf',
    ],
    'extensions' => [ // optionally bind to these extensions
        'pdf'
    ],
    'options' => [] // options to be included in the $presenter instance
]);
```
Normally you should register your presenters in a Service Provider which is loaded after the `Sands\Presenter\PresenterServiceProvider`.


## Available Plugins

<table>
    <tr>
        <th>
[PDF Response](https://github.com/sands-consulting/laravel-presenter-pdf)
        </th>
        <td>
            Download your data as PDF from a custom blade view.
        </td>
    </tr>
    <tr>
        <th>
[XLSX, XLS and CSV Response](https://github.com/sands-consulting/laravel-presenter-excel)
        </th>
        <td>
            Download your data as XLSX, XLS or CSV
        </td>
    </tr>
<table>

## MIT License

Copyright (c) 2016 Sands Consulting Sdn Bhd


Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.