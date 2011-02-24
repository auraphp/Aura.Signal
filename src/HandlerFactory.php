<?php
namespace aura\signal;
class HandlerFactory
{
    protected $base = array(
        'sender'   => null,
        'signal'    => null,
        'callback' => null,
    );
    
    public function newInstance(array $params)
    {
        $params = array_merge($this->base, $params);
        return new Handler(
            $params['sender'],
            $params['signal'],
            $params['callback']
        );
    }
}
