<?php
/**
 * Autoloader information.
 */
$loader->setPath('aura\signal\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Dependency services.
 */
$di->set('signal_manager', function() use ($di) {
    return $di->newInstance('aura\signal\Manager', array(
        'handler_factory'   => $di->newInstance('aura\signal\HandlerFactory'),
        'result_factory'    => $di->newInstance('aura\signal\ResultFactory'),
        'result_collection' => new aura\signal\ResultCollection, // newInstance() fails with ArrayObject ?
    ));
});

/*
// add signal handlers like so: when the vendor\package\Example class sends a 
// 'pre_method' signal, invoke the provided closure.
$di->config['aura\signal\Manager']['handlers'][] = array(
    'vendor\package\Example',                       // sender
    'signal_name',                                  // signal
    function($foo, $bar) use ($di) {                // callback
        $service = $di->get('some_service');
        return $service->doSomething($foo, $bar);
    },
);
*/
