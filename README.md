# bolt-core

## Whut?

Bolt core is an effort to make the core components of the bolt cms as extendable and modular as possible.

The main functionality is that certain config files are loaded into actual PHP objects.
For these objects, a Factory and a Facade class exists.
The Factory objects allow you to construct these objects with ease.
The Facade objects allow you to swap out the implementation with your custom implementation.

This package contains the following components


### Config

The config component loads config files and provides a simple interface for retrieving (nested) values easily.
This new implementation adds support for loading config files in other formats and from different locations.


### ContentType

This implementation contains a `ContentTypeCollection` and a `ContentType` object that contain information about the contenttypes in your system (loaded from the config by default).
You can easily add contenttypes on the fly by constructing them via the Factory class and adding them to `$app['contenttypes']`, which is a `ContentTypeCollection`.

__EXAMPLE__
```php
$entriesConfig = array(
    'name' => 'Entries'
    'singular_name' => 'Entry'
    'fields' => array(
        'title' => array(
            'type' => 'string'
            'class' => 'large'
        )
    )
);
$core['contenttypes']->add('entries', $entriesConfig);

// Or

use Bolt\Core\Support\Facades\ContentType;

$entries = ContentType::fromConfig($entriesConfig);
$core['contenttypes']->addContentType('entries', $entries);

// Or

use Bolt\Core\Support\Facades\ContentType;
use Bolt\Core\Support\Facades\FieldTypeCollection;

$fields = FieldCollection::fromConfig($entriesConfig['fields']);
$entries = ContentType::create('Entries', 'Entry', $fields);
$core['contenttypes']->addContentType('entries', $entries);
```

You have now met the Collection's `add` method, which runs ContentType::fromConfig in the background for you, and adds the ContentType object to the collection.

The `addContentType` method is an alias for the Collection's `put` method and simply adds the object to the collection.

Factories also have the `create` method which instantiates the object via the constructor.

#### NOTE: The `FieldType`, `Field`, `Relation`, `Taxonomy` and `Extension` objects ALL have the same factory methods (`create`, `fromConfig`) and their Collection's also have the `add` and `'add'.$NameOfObject` methods.

It's very easy to remember where to find the core objects, since they are all accessible via their Facade, which are located in the following namespace:
`Bolt\Core\Support\Facades\NameOfTheObject`

You can also take a look in the facade class to see with what name the service is registered, or browse through the serviceproviders (in `src/Bolt\Core\Providers\Silex`)

Presented below is a table of the available facades and their `key` with which they are registered on the `$app` container.

Facade | Container key | Purpose
--- | --- | ---
**Bolt\Core\Support\Facades\FieldType**|fieldtype.factory|Instantiate new FieldType objects
**Bolt\Core\Support\Facades\FieldTypeCollection**|fieldtypes.factory|Instantiate new FieldTypeCollection objects
**Bolt\Core\Support\Facades\View**|view.factory|Instantiate new View objects

### FieldTypes

Same as contenttypes, the new implementation moves related code into this object, and provides a single place to register new fieldtypes.

### Field

Information about fields will be put into an object too, so we can move related code into this object.
This new implementation
