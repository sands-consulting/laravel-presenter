<?php
namespace Sands\Presenter\Exceptions;

use Exception;

class PresenterNotLoadedException extends Exception
{
    public function __construct($extension, $code = 0, Exception $previous = null)
    {
        parent::__construct("Presenter {$extension} not loaded for this method.", $code, $previous);
    }
}
