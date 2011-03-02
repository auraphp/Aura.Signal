<?php
namespace aura\signal;
require dirname(__DIR__) . '/src.php';
return new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
