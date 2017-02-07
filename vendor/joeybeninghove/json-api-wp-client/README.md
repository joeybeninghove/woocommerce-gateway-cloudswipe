# JSON API WordPress Client

This library is an opinionated, resource-based JSON API client for WordPress that strives to adhere to the offical [JSON API 1.0 spec](http://jsonapi.org).

## Requirements

* WordPress

## Usage

### Define a resource
* `base_url` is specified as the root URL used when interacting with the API.
* `type` is the [JSON API type](http://jsonapi.org/format/#document-resource-objects) for the current resource

```php
class Invoice extends Json_Api_Wp_Resource
{
    public function __construct()
    {
        parent::__construct(
            "https://api.site.com/v1/", // base URL
            "invoices" // type
        )
    }
}
```

### Set up HTTP Authentication
The `username` is required, but the `password` is optional and defaults to blank.
```php
Json_Api_Wp_Resource::auth( "jdoe", "secret" );
```

#### API Key example
If you're using a typical API key over HTTP Authentication, here is an example
of using a base class to abstract that away.
```php
class Base extends Json_Api_Wp_Resource
{
    public function __construct( $type )
    {
        parent::__construct( "http://api.site.come/v1/", $type );
    }

    public static function set_api_key( $api_key )
    {
        parent::auth( $apiKey );
    }
}

class Invoice extends Base
{
    public function __construct()
    {
        parent::__construct( "invoices" );
    }
}

Base::set_api_key( "some secret key" );
$invoices = Invoice::get_all();
```

### Create a resource
```php
$invoice = Invoice::create([
    "description" => "T-Shirt",
    "total" => 10.95
]);
```

### Update a resource
This library does not yet support updating of resources because of the lack of
`PATCH` support in the WordPress HTTP library.

### Get a single resource
```php
$invoice = Invoice::get_one( "invoice_123" );
```

### Get all resources
```php
$invoices = Invoice::get_all();
```

### Delete a resource
This library does not yet support updating of resources because of the lack of
`DELETE` support in the WordPress HTTP library.
