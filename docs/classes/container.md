## HandlerInterface

The `HandlerInterface` is an extension of the native `SessionHandlerInterface`. These objects are used within the `Storage\NativeStorage`
object for managing session data. Note that the Session API does not require session handlers to implement the `HandlerInterface`,
all uses typehint against the `SessionHandlerInterface` and check whether the extended interface is used.

### Check if the Handler is Supported

The `isSupported` method is used to validate whether a handler can be used in the current environment. Handlers implementing this interface
should add internal checks to ensure its dependencies are available. For example, the `Handler\MemcachedHandler` object ensures that Memcached
is available on the server.

```php
/*
 * @return  boolean
 */
public static function isSupported();
```

