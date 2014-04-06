<?php
namespace Aura\Signal\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
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
    }
    
    public function modify(Container $di)
    {
    }
}
