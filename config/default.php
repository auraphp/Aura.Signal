<?php
/**
 * Package prefix for autoloader.
 */
$loader->add('Aura\Signal\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

$di->params['Aura\Signal\Manager'] = [
    'handler_factory'   => $di->newInstance('Aura\Signal\HandlerFactory'),
    'result_factory'    => $di->newInstance('Aura\Signal\ResultFactory'),
    'result_collection' => $di->newInstance('Aura\Signal\ResultCollection'),
];

/**
 * Dependency services.
 */
$di->set('signal_manager', function() use ($di) {
    return $di->newInstance('Aura\Signal\Manager');
});
