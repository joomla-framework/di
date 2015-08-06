## Updating from v1 to v2

The following changes were made to the Session package between v1 and v2.

### PHP 5.3 support dropped

The Session package now requires PHP 5.4 or newer.

### Joomla\Session\Storage class chain removed

The `Storage` class chain has been removed and in v2 is replaced with a `StorageInterface` and `HandlerInterface`
(an extension of PHP's `SessionHandlerInterface`) set of classes to separate architectural concerns and improve use of the package.

### Session - Underscore prefixed methods removed

In v1, there are several protected methods named with a leading underscore. These are renamed without the underscore to
comply with the Joomla! Coding Standard.

### SessionInterface added

A new interface, `SessionInterface` has been added to represent the primary session API.

### HandlerInterface added

A new interface, [HandlerInterface](classes/HandlerInterface.md), has been added as an extension of PHP's native `SessionHandlerInterface`.
Classes implementing this interface are largely what the `Joomla\Session\Storage` class chain in v1 was handling.

### StorageInterface added

A new interface, [StorageInterface](classes/StorageInterface.md), has been added to represent a class acting as a session store.
A default implementation, `Storage\NativeStorage`, is included which stores data to PHP's `$_SESSION` superglobal. Abstracting this
logic to a new interface improves the internal architecture of the package and enables better testing of the API.

### Session::getInstance() removed

The base `Session` class no longer supports singleton object storage. The `Session` object should be stored in your application's DI
container or the `Joomla\Application\AbstractWebApplication` object.

### Session::initialise() removed

The `initialise` method has been removed. The base `Session` class now requires a `Joomla\Input\Input` object as part of the
constructor and the `Joomla\Event\DispatcherInterface` object should be injected via the constructor or the `setDispatcher` method.

### Namespaced session variables dropped

Support for namespaced session variables has been removed from the `Session` API. Previously, data was stored to the `$_SESSION` global in
a top level container, such as `$_SESSION[$namespace]`. In v2, data is stored directly to the global.

### Event dispatching modified

In v1, when the `onAfterSessionStart` method was dispatched, a generic `Joomla\Event\Event` object was passed with no parameters. In v2,
a `SessionEvent` object has been added and is dispatched with the `onAfterSessionStart` and new `onAfterSessionRestart` events. The `SessionEvent`
object is dispatched with the current `Session` instance attached so its API is accessible within the events.
