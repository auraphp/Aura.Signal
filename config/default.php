<?php
/**
 * Loader
 */
$loader->add('Aura\Signal\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Services
 */
$di->set('signal_manager', $di->lazyNew('Aura\Signal\Manager'));

/**
 * Aura\Signal\Manager
 */
$di->params['Aura\Signal\Manager'] = [
    'handler_factory'   => $di->newInstance('Aura\Signal\HandlerFactory'),
    'result_factory'    => $di->newInstance('Aura\Signal\ResultFactory'),
    'result_collection' => $di->newInstance('Aura\Signal\ResultCollection'),
];
