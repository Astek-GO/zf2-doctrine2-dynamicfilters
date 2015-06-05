<?php

namespace Zf2Doctrine2DynamicFilters\Form;

use Zend\Form\Form;

class FilterForm extends Form
{
    const SUBMIT_RESET = 'reset';

    const SUBMIT = 'filter';

    const SUBMIT_DEFAULT = 'default';

    public function __construct($name = null)
    {
        parent::__construct($name, ['method' => 'post']);

        $this->setAttribute('action', $this->getActionUrl());

        $this->add([
            'name'       => self::SUBMIT_RESET,
            'attributes' => [
                'type'  => 'submit',
                'value' => 'BTN_RESET',
                'id'    => vsprintf('%s-%s', [$this->getName(), strtolower(self::SUBMIT_RESET)]),
                'class' => 'btn btn-primary'
            ]
        ]);

        $this->add([
            'name'       => self::SUBMIT,
            'attributes' => [
                'type'  => 'submit',
                'value' => 'BTN_FILTRER',
                'id'    => vsprintf('%s-%s', [$this->getName(), strtolower(self::SUBMIT)]),
                'class' => 'btn btn-success'
            ]
        ]);

        $this->add([
            'name'       => self::SUBMIT_DEFAULT,
            'attributes' => [
                'type'  => 'submit',
                'value' => 'BTN_DEFAULT',
                'id'    => vsprintf('%s-%s', [$this->getName(), strtolower(self::SUBMIT_DEFAULT)]),
                'class' => 'btn btn-primary'
            ]
        ]);
    }

    protected function getActionUrl()
    {
        return explode('?', "$_SERVER[REQUEST_URI]")[0];
    }


}