<?php

namespace Sands\Presenter\Presenters;

use Sands\Presenter\Presenter;
use Sands\Presenter\PresenterContract;

class Json implements PresenterContract
{
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    public function render($data = [])
    {
        return response()->json($data);
    }
}
