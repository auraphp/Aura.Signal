<?php
/**
 * Package prefix for autoloader.
 */
$loader->add('Aura\Signal\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Dependency services.
 */
$di->set('signal_manager', function() use ($di) {
    return $di->newInstance('Aura\Signal\Manager', [
        'handler_factory'   => $di->newInstance('Aura\Signal\HandlerFactory'),
        'result_factory'    => $di->newInstance('Aura\Signal\ResultFactory'),
        'result_collection' => new Aura\Signal\ResultCollection, // newInstance() fails with ArrayObject ?
    ]);
});
