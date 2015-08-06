## Overview

The Session package provides an interface for managing sessions within an application. The `Session` class is the base object
within the package and serves as the primary API for managing a session.

### Creating your Session object
The `Session` class constructor takes 1 compulsory and 3 optional parameters:

```php
/**
 * @param   Input                $input       The input object
 * @param   StorageInterface     $store       A StorageInterface implementation
 * @param   DispatcherInterface  $dispatcher  DispatcherInterface for the session to use.
 * @param   array                $options     Optional parameters
 */
public function __construct(Input $input, StorageInterface $store = null, DispatcherInterface $dispatcher = null, array $options = array())
```

### Input Object
The Joomla Input object - required for accessing some variables in `$_SERVER` when validating a session. For more information on the Input package please read the [Joomla Input Package Documentation](https://github.com/joomla-framework/input)

#### Storage Object
The `StorageInterface` defines an object which represents a data store for session data. For more about this please read the [StorageInterface documentation](classes/StorageInterface.md).

#### Dispatcher Interface
The `Session` class triggers events on session start and session restart. You can inject a `Joomla\Event\DispatcherInterface` implementation to use these events in your application. For more information on the Event Dispatcher package please read the [Joomla Event Package Documentation](https://github.com/joomla-framework/event)

#### Array of Options
The session will take an array of options. The following keys are recognised:

* ```name```: Will set the name of the session into the Storage class
* ```id```: Will set the ID of the session into the Storage class
* ```expire``` (default 15 minutes) Will be used to set the expiry time for the session
* ```security``` Security contains an array of which the following values are recognised:
    * ```fix_browser``` (enabled by default) Will check if there are any browser agents located in the ```session.client.browser``` key of your storage engine. The variable in storage will be used to whitelist browsers. If the variable in storage is not set then this check will allow all browsers.
    * ```fix_address``` (disabled by default) Will check if there are any IP addresses located in the ```session.client.address``` key of your storage engine. The variable in storage will be used to whitelist IP's. If the variable in storage is not set then this check will allow all IP's.

### Starting a session

A session can be started by calling the `start()` method.

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();
```

This method is suitable for starting a new session or resuming a session if a session ID has already been assigned and stored
in a session cookie.

If you injected an event dispatcher into the `Session` class then a [SessionEvent](classes/SessionEvent.md] for the `onAfterSessionStart` event will be triggered.

### Closing a session
An existing session can be closed by triggering the `close()`. This will write all your session data through your storage handler.

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

// DO THINGS WITH SESSION

$session->close();
```

### The Session State
You can view the status of the session at any time by calling the `getState()` method. This will return one of the following strings:

* inactive
* active
* expired
* destroyed
* closed
* error

```php
use Joomla\Session\Session;

$session = new Session;
$session->getState();

// RETURNS: inactive

$session->start();
$session->getState()

// RETURNS: active
```

There is a further helper function `isStarted()` that tells you if the session has been started.

### Data in the session
The `SessionInterface` contains several methods to help you manage the data in your session.

#### Setting Data
You can set data into the session using the `set()` method. This method takes two parameters - the name of the variable you want to store and the value of that variable:

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

$session->set('foo', 'bar');

echo $_SESSION['foo']

// Assuming we are using the Native Storage Handler: RESULT: BAR
```

#### Getting Data
You can retrieve data set into the session using the `get()` function. This method also takes two parameters - the name of the variable you want to retrieve and the default value of that variable (null by default)

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

$session->set('foo', 'bar');
echo $session->get('foo');

// RESULT: bar

echo $session->get('unset_variable')

// RESULT: null;

echo $session->get('unset_variable2', 'default_var')

// RESULT: default_var;
```

#### Additional methods
To retrieve all the data from the session storage you can call `all()`

To clear all the data in the session storage you can call `clear()`

To remove a piece of data in the session storage you can call `remove()` with a parmeter of the name of the variable you wish to remove. If that variable is set then it's value will be returned. If the variable is not set then null will be returned.

To check if a piece of data is present in the session storage you can call `has()` with a parameter of the variable you wish to check. This returns a boolean depending on if the data is set.

You can iterate over the data in session storage by calling `getIterator()`. This will create a [Native PHP Array iterator](http://php.net/manual/en/class.arrayiterator.php) object containing all the data in the session storage object.
