<?php
namespace Sands\Presenter\Exceptions;

use Exception;

class PresenterNotFoundException extends Exception
{
    public function __construct($extension, $code = 0, Exception $previous = null)
    {
        parent::__construct("Presenter for {$extension} not found.", $code, $previous);
    }
}
