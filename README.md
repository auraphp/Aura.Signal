Introduction
============

The Aura Signal package is a SignalSlots / EventHandler implementation for PHP 5.3+.  It allows you to invoke handlers ("slots" or "hooks") whenever an object sends a signal ("notification" or "event") to the signal manager.


Basic Usage
===========

Instantiating the Signal Manager
--------------------------------

First, instantiate the signal `Manager` class. The easiest way to do this is to call the `aura.signal/scripts/instance.php` script.

    <?php
    $signal = require '/path/to/aura.signal/scripts/instance.php';

Alternatively, you can register the `aura.signal/src` directory with your autoloader and instantiate it yourself:

    <?php
    use aura\signal\Manager;
    use aura\signal\HandlerFactory;
    use aura\signal\ResultFactory;
    use aura\signal\ResultCollection;
    return new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);


Adding Signal Handlers
----------------------

Before we can send a signal to the `Manager`, we will need to add a handler for it.  To add a handler, specify:

1. The class you expect to be sending the signal.  This can be `'*'` for "any class", or a fully-qualified class name.

2. The name of the signal.

3. A closure or callback to handle the signal.

For example, to add a closure that will be executed every time a object of the class `vendor\package\Example` sends a signal called `'example_signal'`:

    <?php
    $signal->handler(
        'vendor\package\Example',
        'example_signal',
        function ($arg) { echo $arg; }
    );


Signals By Class
----------------  

To send a signal, the sending class must have an instance of the `Manager`.  The class should call the `send()` method with the originating object (itself), the signal being sent, and arguments to pass to the signal handler.

For example, we will define the `vendor\package\Example` class, and have it send a signal to the `Manager`. 

    <?php
    namespace vendor\package;
    use aura\signal\Manager as SignalManager;
    
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

Now whenever we call the `doSomething()` method, it will send the `'example_signal'` to the `Manager`, and the `Manager` will invoke the handler for that signal.


Signal Inheritance
------------------

If a class sends a signal, and no handler has been set for it, then the `Manager` will do nothing.  However, if a handler has been set for a parent class, and one of its child classes sends a signal handled for the parent, the `Manager` will handle that signal for the child as well.

For example, if we have these two classes, and call `doSomethingElse()` on each of them ...

    <?php
    namespace vendor\package;
    use aura\signal\Manager as SignalManager;
    
    class ExampleChild extends Example
    {
        public function doSomethingElse($text)
        {
            echo $text . $text . $text;
            $this->signal->send($this, 'example_signal', $text)
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

... then the `Manager` *will* handle the signal from `ExampleChild` because its parent has a handler for it. The `Manager` *will not* handle the signal for `ExampleOther` because no handlers for it or its parents have been added to the `Manager`.


Signals By Object
-----------------

You can tie a handler to an object instance, so that only signals sent from that specific object will be handled.  To do so, pass the object instance as the `$sender` for the handler.

    <?php
    /**
     * @var aura\signal\Manager $signal
     */
    $object = new vendor\package\ExampleChild($signal);
    
    $signal->handler(
        $object,
        'example_signal',
        function ($arg) { echo "$arg!!!";}
    );

If that specific object instance sends the `example_signal` then the handler will be triggered, but no other instance of `ExampleChild` will trigger the handler when it sends the same signal.  This is useful for setting signal handlers from within an object that also contains the callback; for example:

    <?php
    namespace vendor\package;
    use aura\signal\Manager as SignalManager;
    
    class ExampleAnotherChild extends Example
    {
        public function __construct(SignalManager $signal)
        {
            parent::__construct();
            $this->signal->handler($this, 'preAction', array($this, 'preAction'));
            $this->signal->handler($this, 'postAction', array($this, 'postAction'));
        }
        
        public function action()
        {
            $this->signal->send($this, 'preAction');
            $this->doSomething();
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

When you instantiate `ExampleAnotherChild` and call `action()`, the code:

1. Sends a `'preAction'` signal to the `Manager`, which in turn calls the `preAction()` method on the object

2. Calls the `doSomething()` method on the object (n.b., remember that the `doSomething()` method sends an `'example_signal'` of its own to the `Manager`)

3. Sends a `'postAction'` signal to the `Manager`, which in turn calls the `postAction()` method on the object.

If there are class-based handlers for `ExampleAnotherChild` class or its parents, those will also be executed.  This means you can set up combinations of handlers to be applied to classes overall along with handlers that are tied to specific objects.


Advanced Usage
==============


Handler Position Groups
----------------------

By default, all `Handler` objects will be appended to the `Manager` stack, and will be processed the order they were added.  Sometimes you will need a `Handler` to be processed in a different order; for example, before or after all others. If so, you can pass a `$position` value when adding a `Handler` to the `Manager`.  (The default `$position` for `Handler` objects is 5000.)

    <?php
    // add a closure at position 1000, which means it will be processed
    // before all handlers at the default position 5000.
    $closure = function() { echo "Before all others."; };
    $signal->handler('ExampleChild', 'example_signal', $closure, 1000);

    // add a closure at position 9000, which means it will be processed
    // after all handlers at the default position 5000.
    $closure = function() { echo "After all others."; };
    $signal->handler('ExampleChild', 'example_signal', $closure, 1000);

`Handler` objects added at a position will still be appended within that position group.


Result Inspection
-----------------

(implemented; documentation coming soon)


Stopping Signal Processing
--------------------------

(implemented; documentation coming soon)


Setting Handlers at Construction
--------------------------------

(implemented; documentation coming soon)

