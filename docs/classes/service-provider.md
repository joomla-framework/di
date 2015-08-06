## StorageInterface

The `StorageInterface` defines an object which represents a data store for session data.

### Get the Session Name

The `getName` method is used to read the session name.

```php
/*
 * @return  string  The session name
 */
public function getName();
```

### Set the Session Name

The `setName` method is used to set the session name. In regular use, the `Session` object will inject the calculated session
name into the storage object.

```php
/*
 * @param   string  $name  The session name
 *
 * @return  $this
 */
public function setName($name);
```
