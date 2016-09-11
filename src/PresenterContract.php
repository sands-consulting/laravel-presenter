<?php

namespace Sands\Presenter;

interface PresenterContract
{
    public function __construct(Presenter $presenter);

    public function render($data);
}
