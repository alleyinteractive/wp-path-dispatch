# WP Path Dispatch

[![Coding Standards](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/coding-standards.yml)
[![Testing Suite](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/unit-test.yml/badge.svg)](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/unit-test.yml)

Simply and easily add a URL which fires an action, triggers a callback, and/or
loads a template.

## Installation

You can install the package via composer:

```bash
composer require alleyinteractive/wp-path-dispatch
```

## Usage

At any point before init,

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path'     => 'some-path',
		'callback' => 'some_function'
	]
);
```

This will cause http://domain.com/some-path/ to call `some_function()`.

IMPORTANT! You must flush your rewrites after adding a path.

You can add multiple paths at once with `add_paths()`:

```php
\WP_Path_Dispatch\Path_Dispatch()->add_paths(
	[
		[
			'path'     => 'some-path',
			'callback' => 'some_function',
		],
		[
			'path'     => 'custom-feed.json',
			'callback' => 'custom_feed',
		],
		[
			'path'     => 'custom-feed.xml',
			'callback' => 'custom_feed',
		],
	]
);
```

The dispatch happens on parse_query, so you can then modify the query via pre_get_posts or do whatever
you have to do. You can even just load a static file and exit if you simply need to render static content.

When the path is loaded, the action dispatch_path_{$path} is fired. You can hook onto this instead of or
in addition to passing a callback to add_path(s). The callback is optional.

Lastly, you can set custom rewrites if your paths are more complex. In these cases, the 'path' argument
essentially becomes a slug. See [add_rewrite_rule()](http://codex.wordpress.org/Rewrite_API/add_rewrite_rule)
for details about 'rule', 'redirect' (rewrite), and 'position'.

## Full breakdown of all the path options

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path'     => 'some-path',     // required
		'callback' => 'some_function', // optional
		'action'   => '',              // fire this action instead of dispatch_path_{$path}
		'template' => '',              // optional
		'rewrite'  => [                // optional
			'query_vars' => [],                               // optional
			'rule'       => '',                               // required (assuming 'rewrite' is set)
			'redirect'   => 'index.php?dispatch=$matches[1]', // optional
			'position'   => 'top'                             // optional
		],
	]
);
```

## Examples

Simplest possible usages: fires the action 'dispatch_path_my-path' at http://domain.com/my-path/

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path' => 'my-path',
	]
);
```

This can even be simplified further as:

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path( 'my-path' );
```

Call the function 'my_function' at http://domain.com/my-path/

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path'     => 'my-path',
		'callback' => 'my_function',
	]
);
```

Load the template file 'dispatch-custom-page.php' at http://domain.com/my-path/

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path'     => 'my-path',
		'template' => 'custom-page'
	]
);
```

Add a custom rewrite rule. Fires the action 'dispatch_path_my-path' at e.g.
http://domain.com/my-path/foo/ and sets the query var 'my_path' to 'foo'. This
assumes you already registered that query var.

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path'    => 'my-rewrite',
		'rewrite' => [
			'rule'     => 'my-path/(.*)/?',
			'redirect' => 'index.php?dispatch=my-rewrite&my_path=$matches[1]'
		],
	]
);
```

Same as above, but registers the query var automatically, and loads the template 'dispatch-my-page.php'.

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path'     => 'my-rewrite',
		'rewrite'  => [
			'rule'       => 'my-path/(.+)/?',
			'redirect'   => 'index.php?dispatch=my-rewrite&my_path=$matches[1]',
			'query_vars' => 'my_path',
		],
		'template' => 'my-page',
	]
);
```

Same as above, but with multiple query vars, and with a callback instead of a template.

```php
\WP_Path_Dispatch\Path_Dispatch()->add_path(
	[
		'path'     => 'my-rewrite',
		'rewrite'  => [
			'rule'       => 'my-path/([^/]+)/(.+)/?',
			'redirect'   => 'index.php?dispatch=my-rewrite&my_path=$matches[1]&my_section=$matches[2]',
			'query_vars' => [ 'my_path', 'my_section' ],
		],
		'callback' => [ My_Singleton(), 'my_method' ],
	]
);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.co/careers/).

- [Matt Boynes](https://github.com/mboynes)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
