## Router
```
  _____  _    _ _____             _____             _            
 |  __ \| |  | |  __ \           |  __ \           | |           
 | |__) | |__| | |__) |  ______  | |__) |___  _   _| |_ ___ _ __
 |  ___/|  __  |  ___/  |______| |  _  // _ \| | | | __/ _ \ '__|
 | |    | |  | | |               | | \ \ (_) | |_| | ||  __/ |   
 |_|    |_|  |_|_|               |_|  \_\___/ \__,_|\__\___|_|   

```
simple Router class for PHP. with the support of Controllers and Middlewares.

### Features
- Supports GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD, AJAX and ANY request methods
- Controllers support (Example: HomeController@about)
- Before and after Route Middlewares support
- Static Route Patterns
- Dynamic Route Patterns
- Easy-to-use patterns
- Adding a new pattern supports. (with RegExp)
- Namespaces supports.
- Group Routing
- Custom 404 handling
- Debug mode (Error message open/close)

## Install

composer.json file:
```json
{
    "require": {
        "inf/router": "^1"
    }
}
```
after run the install command.
```
$ composer install
```

OR run the following command directly.

```
$ composer require inf/router
```

## Example Usage
```php
require 'vendor/autoload.php';

$router = new Inf\Router();

$router->get('/', function() {
    return 'Hello World!';
});
$router->get('/controller', 'TestController@main');

$router->run();
```

## Docs
Documentation page: [Inf\Router Docs][doc-url]

## ToDo
- Write Test
- Write Documentation

## Support
[gen2wind's homepage][author-url]

[gen2wind's twitter][twitter-url]

## Licence
[MIT Licence][mit-url]

## Contributing

1. Fork it ( https://github.com/gen2wind/php-router/fork )
2. Create your feature branch (git checkout -b my-new-feature)
3. Commit your changes (git commit -am 'Add some feature')
4. Push to the branch (git push origin my-new-feature)
5. Create a new Pull Request

## Contributors

- [gen2wind](https://github.com/gen2wind) Ogunyemi Oludayo - creator, maintainer

[mit-url]: http://opensource.org/licenses/MIT
[doc-url]: https://github.com/gen2wind/php-router/docs
[author-url]: https://github.com/gen2wind
[twitter-url]: https://twitter.com/gen2wind
"# test-findworka" 
