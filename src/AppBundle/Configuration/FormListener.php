<?php
namespace AppBundle\Configuration;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FormListener
{
    /** @var ContainerInterface */
    private $container;

    /** @var array */
    private $templateParameters;
    /** @var FormInterface */
    private $currentForm;
    /** @var object */
    private $controller;
    /** @var Form */
    private $currentFormAnnotation;
    /** @var array */
    private $tree = [];

    /**
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FilterControllerEvent $event
     * @throws \Exception
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        list($controllerInstance, $method) = $controller;

        $className = ClassUtils::getClass($controllerInstance);

        $formAnnotations = $this->getFormAnnotations($className);
        $this->buildFormsTree($formAnnotations, $method, $className);

        $this->controller = $controllerInstance;

        $request = $event->getRequest();
        foreach ($formAnnotations as $formAnnotation) {

            if ($method == $formAnnotation->getAcceptor()) {
                $this->currentFormAnnotation = $formAnnotation;
                $starterActionName = $className . '::' . $formAnnotation->getStarter();
                $response = $this->executeStarter($event, $request, $starterActionName);
                if ($response->getStatusCode() == '200') {
                    $templateParams = $this->templateParameters;

                    /** @var FormInterface $form */
                    $form = $this->currentForm;
                    $form->handleRequest($request);
                    if (!$form->isValid()) {
                        $rejector = $formAnnotation->getRejector();
                        if (!empty($rejector)) {
                            call_user_func_array([$controllerInstance, $rejector], $templateParams);
                        }

                        $formName = $this->currentFormAnnotation->getValue();
                        $templateParams[$formName] = $form->createView();
                        $event->setController(function () use ($templateParams) {
                            return $templateParams;
                        });
                    } else {
                        $request->attributes->set('entity', $templateParams['entity']);
                    }
                }
                $this->currentFormAnnotation = null;
            }
        }
    }

    /**
     * @param $className
     *
     * @return Form[]
     */
    protected function getFormAnnotations($className)
    {
        /** @var Reader */
        $annotationReader = $this->container->get('annotation_reader');
        $reflectionClass = new \ReflectionClass($className);
        $allAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
        $formAnnotations = array_filter($allAnnotations, function ($annotation) {
            return $annotation instanceof Form;
        });
        return $formAnnotations;
    }

    /**
     * @param $formAnnotations
     * @param $method
     * @param $className
     * @return mixed
     */
    protected function buildFormsTree($formAnnotations, $method, $className)
    {
        foreach ($formAnnotations as $formAnnotation) {
            if ($method != $formAnnotation->getStarter()
                && $method != $formAnnotation->getAcceptor()
            ) {
                continue;
            }

            $starter = $formAnnotation->getStarter();
            $starterActionName = $className . '::' . $starter;
            if (empty($this->tree[$starterActionName])) {
                $this->tree[$starterActionName] = [];
            }
            $this->tree[$starterActionName][] = $formAnnotation;
        }
    }

    /**
     * @param FilterControllerEvent $event
     * @param $request
     * @param $starterActionName
     * @throws \Exception
     *
     * @return Response
     */
    protected function executeStarter(FilterControllerEvent $event, $request, $starterActionName)
    {
        $request->attributes->set('_controller', $starterActionName);

        /** @var ContainerAwareHttpKernel $kernel */
        $kernel = $event->getKernel();
        $response = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
        return $response;
    }


    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $controllerName = $request->get('_controller');
        if (!empty($this->tree[$controllerName])) {
            foreach ($this->tree[$controllerName] as $formAnnotation) {
                $this->injectFormIntoTemplateParameters($event, $formAnnotation);
            }
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     * @param Form $formAnnotation
     */
    protected function injectFormIntoTemplateParameters(GetResponseForControllerResultEvent $event, Form $formAnnotation)
    {
        $params = $event->getControllerResult();
        $this->templateParameters = $params;
        $entity = $params['entity'];
        $formName = $formAnnotation->getValue();
        if (empty($params[$formName])) {
            $formCreateMethod = $formAnnotation->getMethod();
            $form = call_user_func_array([$this->controller, $formCreateMethod], [$entity]);
            $params[$formName] = $form->createView();
            if ($this->currentFormAnnotation && $this->currentFormAnnotation->getMethod() == $formCreateMethod) {
                $this->currentForm = $form;
            }
        }
        $event->setControllerResult($params);
    }
}