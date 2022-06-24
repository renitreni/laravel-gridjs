# Laravel Gridjs (Supports Vanilla Js)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require throwexceptions/laravel-gridjs
```

## Usage

1. Be sure to add the assets first in your views.
- Add this in your ``` <head></head> ```
```html
    <x-throwexceptions::styles/>
```
- Add this before inside the <body> before end tag.
```html
    <x-throwexceptions::scripts/>
```

2. Run this in your terminal to create class builder for the data grid.
    `*Create your sample users`
```bash
    php artisan gridjs:make-builder <TableName> <Model>
    
    e.g.
    php artisan migrate
    php artisan gridjs:make-builder UserTable User
```

3. Create the make() and fetch() for server-side calls

```php
Route::get('/', function () {
    return view('welcome', ['tableUser' => app(UserTableGridjs::class)->make(route('user.fetch'))]);
});

Route::get('/user/fetch', function () {
    return app(UserTableGridjs::class)->fetch(request());
})->name('user.fetch');
```

4. Use component generated and pass variable in view
```html
<x-throwexceptions::gridjs :table="$tableUser" name="tableUser"/>
```

## Full Script

UserTableGridjs
```php
<?php

namespace Gridjs;

use App\Models\User;
use Throwexceptions\LaravelGridjs\LaravelGridjs;

class UserTableGridjs extends LaravelGridjs
{
    public function config()
    {
        $this->setQuery(model: User::query())
             ->enableFixedHeader()
             ->editColumn('action', function ($row) {
                 return '<button class="btn btn-info">button</button>';
             });
    }

    public function columns(): array
    {
        return [
            'id'     => 'ID',
            'name'   => 'Name',
            'email'  => 'E-mails',
            'action' => [
                'name' => "Action",
                'sort' => ['enabled' => false],
                'formatter' => true
            ],
        ];
    }
}
```
routes/web.php
```php
Route::get('/', function () {
    return view('welcome', ['tableUser' => app(UserTableGridjs::class)->make(route('user.fetch'))]);
});

Route::get('/user/fetch', function () {
    return app(UserTableGridjs::class)->fetch(request());
})->name('user.fetch');

```
welcome.blade.php
```html
<x-throwexceptions::styles/>
</head>
<body class="antialiased">
<x-throwexceptions::gridjs :table="$tableUser" name="tableUser"/>

<x-throwexceptions::scripts/>
</body>
```
## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author@email.com instead of using the issue tracker.

## Credits

- [Renier Trenuela][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/throwexceptions/laravel-gridjs.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/throwexceptions/laravel-gridjs.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/throwexceptions/laravel-gridjs/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/throwexceptions/laravel-gridjs
[link-downloads]: https://packagist.org/packages/throwexceptions/laravel-gridjs
[link-travis]: https://travis-ci.org/throwexceptions/laravel-gridjs
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/throwexceptions
[link-contributors]: ../../contributors
