<?php

namespace Sands\Presenter;

use Sands\Presenter\Exceptions\PresenterNotFoundException;
use Sands\Presenter\Exceptions\PresenterNotLoadedException;

class Presenter
{

    protected $presenter = null;
    protected $controller = null;
    protected $presenters = [];
    protected $mimes = [];
    protected $extensions = [];
    protected $options = [
        'controllerPrefix' => 'App\\Http\\Controllers\\',
        'controllerSuffix' => 'Controller',
    ];

    public function register($name, $options)
    {
        $this->presenters[$name] = $options['presenter'];
        if (isset($options['extensions'])) {
            foreach ($options['extensions'] as $extension) {
                $this->extensions[$extension] = $name;
            }
        }
        if (isset($options['mimes'])) {
            foreach ($options['mimes'] as $mime) {
                $this->mimes[$mime] = $name;
            }
        }
        if (isset($options['options'])) {
            foreach ($options['options'] as $key => $value) {
                $this->setOption($key, $value);
            }
        }
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getOption($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setData($data = [])
    {
        $this->data = $data;
        return $this;
    }

    public function getData($presenterName)
    {
        if ($presenterName) {
            $presenterDataMethod = $this->getOption("data.{$presenterName}");
            if ($presenterDataMethod) {
                return $this->controller->$presenterDataMethod();
            }
        }
        $dataMethod = $this->getOption("data");
        if ($dataMethod) {
            return $this->controller->$dataMethod();
        }
        return $this->data;
    }

    public function using($types)
    {
        if (is_string($types)) {
            $types = func_get_args();
        }

        $available = array_keys($this->presenters);

        foreach ($types as $type) {
            if (!in_array($type, $available)) {
                throw new PresenterNotFoundException($type);
            }
        }

        $params = $this->getOption('routeParams');
        if (isset($params['presentUsing'])) {
            $extension = $params['presentUsing'];
            if (!in_array($extension, array_keys($this->extensions))) {
                abort(404);
            }
            if (!in_array($extension, $types)) {
                throw new PresenterNotLoadedException($extension);
            }
            $presenterName = $this->extensions[$extension];
        } else {
            $contentTypes = app('request')->getAcceptableContentTypes();
            foreach ($contentTypes as $contentType) {
                if (isset($this->mimes[$contentType])) {
                    $name = $this->mimes[$contentType];
                    if (in_array($name, $types)) {
                        $presenterName = $this->mimes[$contentType];
                        break;
                    }
                }
            }
        }

        if (!isset($presenterName)) {
            abort(404);
        }

        $this->presenter = new $this->presenters[$presenterName]($this);
        return $this->presenter->render($this->getData($presenterName));
    }
}
