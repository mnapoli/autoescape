# Auto-escaping untrusted strings in SQL queries

This package is a proof of concept for automatically escaping untrusted strings in SQL queries using PHP.

This provides a friendly API for developers, allowing to craft SQL queries naturally like this:

```php
$db->fetchAll("SELECT * FROM users WHERE name = $name")
```

The common pitfall with the code above is that it creates a SQL injection vulnerability if `$name` contains untrusted user input. The proper approach in PHP is to use prepared statements, leading to more verbose code like this:

```php
$stmt = $db->prepare("SELECT * FROM users WHERE name = :name");
$stmt->execute(['name' => $name]);
$results = $stmt->fetchAll();
```

## Example

Here's how this POC package can be used:

```php
$name = new UntrustedString("O'Reilly");

$results = $db->fetchAll("SELECT * FROM users WHERE name = $name");
```

## Vision

This is a POC, but this idea could be implemented natively into Laravel for example. Data coming from requests could automatically be wrapped with `UntrustedString`:

```php
$name = $request->input('name');
$results = $db->fetchAll("SELECT * FROM users WHERE name = $name");
```

## How it works

The package leverages PHP's `__toString()` magic method to automatically escape untrusted strings when they are interpolated into SQL queries. The `UntrustedString` class wraps the untrusted input and ensures it is properly escaped before being used in the query.

In short, the `"SELECT * FROM users WHERE name = $name"` string becomes:

```php
SELECT * FROM users WHERE name = {escaped:TydSZWlsbHk=}
```

Note that base64 encoding is used to safely represent the untrusted string within the query.

When the query is executed, the `Db` class detects `{escaped:...}` patterns and replaces them with parameters:

```php
SELECT * FROM users WHERE name = :param1
```

The package then prepares the statement and binds the original value to `:param1`, ensuring that the query is safe from SQL injection.

## Drawbacks

The obvious drawback of this approach is that developers have to remember to wrap untrusted strings with the `UntrustedString` class.

There is room for human error, which could lead to SQL injection vulnerabilities.

I wonder if this could be 100% covered by static analysis tools like PHPStan or Psalm. There's a whole domain called "**taint analysis**" that relates to this problem. The good thing with taint analysis is that it could help with more than just this specific use case, but with SQL injections in general.

Also I want to re-iterate that this repository is a POC, it doesn't cover all edge cases and is not production-ready. Don't use it, and don't judge the idea based on this implementation specifically.

## Why this?

My goal here is to plant some ideas in the community. Maybe there are some good ideas that could come out of this, maybe not.
