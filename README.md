Aura Signal
===========

The Aura Signal package is a SignalSlots/EventHandler implementation for PHP.
With it, we can invoke handlers ("slots" or "hooks") whenever an object sends
a signal ("notification" or "event") to the signal manager.

This package is compliant with [PSR-0][], [PSR-1][], and [PSR-2][]. If you
notice compliance oversights, please send a patch via pull request.

[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

Basic Usage
===========

Instantiating the Signal Manager
--------------------------------

First, instantiate the signal `Manager` class. The easiest way to do this is
to call the `Aura.Signal/scripts/instance.php` script.

```php
<?php
$signal = require '/path/to/Aura.Signal/scripts/instance.php';
```

Adding Signal Handlers
----------------------

Before we can send a signal to the `Manager`, we will need to add a handler
for it. To add a handler, specify:

1. The class expected to be sending the signal.  This can be `'*'` for "any class", or a fully-qualified class name.

2. The name of the signal.

3. A closure or callback to handle the signal.

For example, to add a closure that will be executed every time an object of
the class `Vendor\Package\Example` sends a signal called `'example_signal'`:

```php
<?php
$signal->handler(
    'Vendor\Package\Example',
    'example_signal',
    function ($arg) { echo $arg; }
);
```


Signals By Class
----------------  

To send a signal, the sending class must have an instance of the `Manager`.
The class should call the `send()` method with the originating object
(itself), the signal being sent, and arguments to pass to the signal handler.

For example, we will define the `Vendor\Package\Example` class, and have it
send a signal to the `Manager`.

```php
<?php
namespace Vendor\Package;
use Aura\Signal\Manager as SignalManager;

class Example
{
    protected $signal;
    
    public function __construct(SignalManager $signal)
    {
        $this->signal = $signal;
    }
    
    public function doSomething($text)
    {
        echo $text;
        $this->signal->send($this, 'example_signal', $text);
    }
}
```

Now whenever we call the `doSomething()` method, it will send the
`'example_signal'` to the `Manager`, and the `Manager` will invoke the handler
for that signal.


Signal Inheritance
------------------

If a class sends a signal, and no handler has been set for it, then the
`Manager` will do nothing. However, if a handler has been set for a parent
class, and one of its child classes sends a signal handled for the parent, the
`Manager` will handle that signal for the child as well.

For example, if we have these two classes, and call `doSomethingElse()` on
each of them ...

```php
<?php
namespace Vendor\Package;
use Aura\Signal\Manager as SignalManager;

class ExampleChild extends Example
{
    public function doSomethingElse($text)
    {
        echo $text . $text . $text;
        $this->signal->send($this, 'example_signal', $text);
    }
}

class ExampleOther
{
    protected $signal;
    
    public function __construct(SignalManager $signal)
    {
        $this->signal = $signal;
    }
    
    public function doSomethingElse($text)
    {
        echo $text . $text . $text;
        $this->signal->send($this, 'example_signal', $text)
    }
}
```

... then the `Manager` *will* handle the signal from `ExampleChild` because
its parent has a handler for it. The `Manager` *will not* handle the signal
for `ExampleOther` because no handlers for it or its parents have been added
to the `Manager`.


Signals By Object
-----------------

It is possible to tie a handler to an object instance, so that only signals
sent from that specific object will be handled. To do so, pass the object
instance as the `$sender` for the handler.

```php
<?php
/**
 * @var Aura\Signal\Manager $signal
 */
$object = new Vendor\Package\ExampleChild($signal);

$signal->handler(
    $object,
    'example_signal',
    function ($arg) { echo "$arg!!!";}
);
```

If that specific object instance sends the `example_signal` then the handler
will be triggered, but no other instance of `ExampleChild` will trigger the
handler when it sends the same signal. This is useful for setting signal
handlers from within an object that contains its own callback; for example:

```php
<?php
namespace Vendor\Package;
use Aura\Signal\Manager as SignalManager;

class ExampleAnotherChild extends Example
{
    public function __construct(SignalManager $signal)
    {
        parent::__construct();
        $this->signal = $signal;
        $this->signal->handler($this, 'preAction', [$this, 'preAction']);
        $this->signal->handler($this, 'postAction', [$this, 'postAction']);
    }
    
    public function action()
    {
        $this->signal->send($this, 'preAction');
        $this->doSomething( __METHOD__ );
        $this->signal->send($this, 'postAction');
    }
    
    public function preAction()
    {
        // happens before the main action() logic
    }
    
    public function postAction()
    {
        // happens after the main action() logic
    }
}
```

When `ExampleAnotherChild::action()` is called, the code:

1. Sends a `'preAction'` signal to the `Manager`, which in turn calls the `preAction()` method on the object

2. Calls the `doSomething()` method on the object (n.b., remember that the `doSomething()` method sends an `'example_signal'` of its own to the `Manager`)

3. Sends a `'postAction'` signal to the `Manager`, which in turn calls the `postAction()` method on the object.

If there are class-based handlers for `ExampleAnotherChild` class or its
parents, those will also be executed. This means we can set up combinations of
handlers to be applied to classes overall, along with handlers that are tied
to specific objects.


Advanced Usage
==============

Handler Position Groups
-----------------------

By default, all `Handler` objects will be appended to the `Manager` stack, and
will be processed the order they were added. Sometimes you will need a
`Handler` to be processed in a different order; for example, before or after
all others. If so, you can pass a `$position` value when adding a `Handler` to
the `Manager`. (The default `$position` for `Handler` objects is 5000.)

```php
<?php
// add a closure at position 1000, which means it will be processed
// before all handlers at the default position 5000.
$closure = function() { 
    echo "Before all others."; 
    return "First closure";
};
$signal->handler('Vendor\Package\ExampleChild', 'example_signal', $closure, 1000);

// add a closure at position 9000, which means it will be processed
// after all handlers at the default position 5000.
$closure = function() { 
    echo "After all others."; 
    return "Second closure";
};
$signal->handler('Vendor\Package\ExampleChild', 'example_signal', $closure, 1000);
```

`Handler` objects added at a position will still be appended within that
position group.


Result Inspection
-----------------

After a signal has been sent, we can review the results returned by every
handler for that signal.

```php
<?php
// send a signal
$this->signal->send($this, 'example_signal');

// get the result collection
$results =  $this->signal->getResults();

// go through each result ...
foreach ($results as $result) {
    
    // ... and echo the value returned by the Handler callback
    echo $result->value;
}
```

The `getResults()` method returns a `ResultCollection` of `Result` objects,
each of which has these properties:

- `$origin`: The object that sent the signal.

- `$sender`: The sender expected by the `Handler`.

- `$signal`: The signal that was sent by the origin.

- `$value`: The value returned by the `Handler` callback.

If you need only the last result, you can call `getLast()` on the
`ResultCollection` object.

```php
<?php
// send a signal and retain the results from each Handler
$results = $this->signal->send($this, 'example_signal');

// get the last result
$result = $results->getLast();

// and echo the value returned by the last Handler callback
echo $result->value;
```


Stopping Signal Processing
--------------------------

Sometimes it will be necessary to stop processing signal handlers. If a
handler callback returns the `Aura\Signal\Manager::STOP` constant, then no
more handlers for that signal will be processed.

First we define the handlers; note that the second one returns the `STOP`
constant:

```php
<?php
// add signal handlers
$signal->handler(
    'Vendor\Package\Example',
    'mock_signal',
    function() { return 'first'; }
);

$signal->handler(
    'Vendor\Package\Example',
    'mock_signal',
    function() { return \Aura\Signal\Manager::STOP; }
);

$signal->handler(
    'Vendor\Package\Example',
    'mock_signal',
    function() { return 'third'; }
);
```

Then, from inside an object, we send a signal:

```php
<?php
$results = $this->signal->send($this, 'mock_signal');
// Or you can get via 
// $results = $this->signal->getResults();
```
    
Normally, `$results` would have three entries. In this case it has only two,
because the second handler returned `\aura\signal\Manager::STOP`. As such, the
third handler was never executed. You can call `ResultCollection::isStopped()`
to see if the `Manager` stopped processing handlers in this way.

```php
<?php
if ($results->isStopped()) {
    $result = $results->getLast();
    echo "Processing for signal 'mock_signal' stopped "
       . "by handler for " . $result->sender;
}
```


Setting Handlers at Construction
--------------------------------

It is possible to set the `Handler` definitions for a `Manager` at
construction time. This allows us to use one or more config files to define
the `Handler` stack for a `Manager`.

Given this file at `/path/to/signal_handlers.php` ...

```php
<?php
return [
    // first handler, with a closure
    [
        'Vendor\Package\Example',
        'mock_signal',
        function() { return 'foo'; },
    ],
    // second handler, with a static callback
    [
        'Vendor\Package\Example',
        'mock_signal',
        ['Vendor\Package\SomeClass', 'someMethod'],
    ],
    // third handler, with a closure and position
    [
        'Vendor\Package\Example',
        'mock_signal',
        function() { return 'baz'; },
        1000,
    ],
];
```

... we can configure a `Manager` like so:

```php
<?php
namespace Aura\Signal;
$handlers = require '/path/to/signal_handlers.php';
$signal = new Manager(
    new HandlerFactory,
    new ResultFactory,
    new ResultCollection,
    $handlers
);
```

That is the equivalent of calling `$signal->handler()` three times to add each
handler.


Thanks
------

Thanks to Richard "Cyberlot" Thomas for the original suggestion, Galactic Void
for bringing it back up, and [Matthew Weier
O'Phinney](http://weierophinney.net/matthew/archives/251-Aspects,-Filters,-and-Signals,-Oh,-My!.html).
