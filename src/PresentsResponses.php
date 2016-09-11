<?php
namespace Sands\Presenter;

trait PresentsResponses
{
    protected function present($data = [])
    {
        $route = app('router')->current();
        list($controller, $method) = explode('@', $route->getAction()['uses']);
        $presenter = app('sands.presenter');
        $presenter->setController($this);
        $presenter->setOption('controller', $controller);
        $presenter->setOption('method', $method);
        $presenter->setOption('routeParams', $route->parameters());
        $presenter->setData($data);
        return $presenter;
    }
}
