<?php
/**
 * Package prefix for autoloader.
 */
$loader->add('Aura\Signal\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Instance params and setter values.
 */
$di->params['Aura\Signal\Manager']['handler_factory']   = $di->newInstance('Aura\Signal\HandlerFactory');
$di->params['Aura\Signal\Manager']['result_factory']    = $di->newInstance('Aura\Signal\ResultFactory');
$di->params['Aura\Signal\Manager']['result_collection'] = $di->newInstance('Aura\Signal\ResultCollection');

/**
 * Dependency services.
 */
$di->set('signal_manager', $di->lazyNew('Aura\Signal\Manager'));
