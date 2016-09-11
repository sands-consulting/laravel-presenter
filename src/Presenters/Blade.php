<?php

namespace Sands\Presenter\Presenters;

use Sands\Presenter\Exceptions\UnableToResolveViewException;
use Sands\Presenter\Presenter;
use Sands\Presenter\PresenterContract;

class Blade implements PresenterContract
{

    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    protected function getViewPath()
    {
        if ($this->presenter->getOption('view')) {
            return $this->presenter->getOption('view');
        }
        $controller = $this->presenter->getOption('controller');
        $method = $this->presenter->getOption('method');
        if (!$controller || !$method) {
            throw new UnableToResolveViewException();
        }
        $prefix = $this->presenter->getOption('controllerPrefix');
        if (strstr($controller, $prefix)) {
            $controller = substr($controller, strlen($prefix));
        }
        $suffix = $this->presenter->getOption('controllerSuffix');
        if (strstr($controller, $suffix)) {
            $controller = substr($controller, 0, strlen($controller) - strlen($suffix));
        }
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $controller)) . '.' . $method;
    }

    public function render($data = [])
    {
        return view($this->getViewPath())->with($data);
    }
}
