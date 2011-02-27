<?php
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
