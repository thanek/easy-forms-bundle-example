<?php
namespace AppBundle\Configuration;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Form extends Annotation
{
    protected $method;
    protected $starter;
    protected $acceptor;
    protected $rejector;

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getStarter()
    {
        return $this->starter;
    }

    /**
     * @return mixed
     */
    public function getAcceptor()
    {
        return $this->acceptor;
    }

    /**
     * @return mixed
     */
    public function getRejector()
    {
        return $this->rejector;
    }
}