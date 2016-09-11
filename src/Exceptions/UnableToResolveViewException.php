<?php
namespace Sands\Presenter\Exceptions;

use Exception;

class UnableToResolveViewException extends Exception
{
    public function __construct($code = 0, Exception $previous = null)
    {
        parent::__construct("Presenter unable to resolve view. Use app('sands.presenter')->setOption('view', 'some.view') to resolve.", $code, $previous);
    }
}
