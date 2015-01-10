<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Configuration\Form;

/**
 * @Route("/post")
 * @Form("new_form",method="createCreateForm",starter="newAction",acceptor="createAction",rejector="onFormFailed")
 * @Form("edit_form",method="createEditForm",starter="editAction",acceptor="updateAction",rejector="onFormFailed")
 * @Form("delete_form",method="createDeleteForm",starter="editAction",acceptor="deleteAction")
 * @Form("delete_form",method="createDeleteForm",starter="showAction")
 */
class PostController extends Controller
{
    /**
     * @Route("/", name="post")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppBundle:Post')->findAll();

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Route("/{id}", name="post_show", requirements={"id": "\d+"})
     * @Method("GET")
     * @Template()
     *
     * @param Post $entity
     *
     * @return array
     */
    public function showAction(Post $entity)
    {
        return [
            'entity' => $entity,
        ];
    }

    /**
     * @Route("/new", name="post_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        return [
            'entity' => new Post(),
        ];
    }

    /**
     * @Route("/", name="post_create")
     * @Method("POST")
     * @Template("AppBundle:Post:new.html.twig")
     *
     * @param Post $entity
     *
     * @return RedirectResponse
     */
    public function createAction(Post $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        $this->addFlashMessage('notice', 'Post ' . $entity->getTitle() . ' added successfully!');

        return $this->redirect($this->generateUrl('post_show', ['id' => $entity->getId()]));
    }


    /**
     * @param Post $entity The entity
     *
     * @return FormInterface The form
     */
    public function createCreateForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, [
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit', ['label' => 'Create']);

        return $form;
    }

    /**
     * @Route("/{id}/edit", name="post_edit")
     * @Method("GET")
     * @Template()
     *
     * @param Post $entity
     *
     * @return array
     */
    public function editAction(Post $entity)
    {
        return [
            'entity' => $entity,
        ];
    }

    /**
     * @Route("/{id}", name="post_update")
     * @Method("PUT")
     * @Template("AppBundle:Post:edit.html.twig")
     *
     * @param Post $entity
     *
     * @return RedirectResponse
     */
    public function updateAction(Post $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        $this->addFlashMessage('notice', 'Post ' . $entity->getTitle() . ' updated successfully!');

        return $this->redirect($this->generateUrl('post_edit', ['id' => $entity->getId()]));
    }

    /**
     * @param Post $entity The entity
     *
     * @return FormInterface The form
     */
    public function createEditForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, [
            'action' => $this->generateUrl('post_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);
        $form->add('submit', 'submit', ['label' => 'Update']);

        return $form;
    }

    /**
     * @Route("/{id}", name="post_delete")
     * @Method("DELETE")
     * @Template("AppBundle:Post:edit.html.twig")
     *
     * @param Post $entity
     *
     * @return RedirectResponse
     */
    public function deleteAction(Post $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->addFlashMessage('notice', 'Post deleted successfully!');

        return $this->redirect($this->generateUrl('post'));
    }

    /**
     * @param Post $entity
     *
     * @return FormInterface The form
     */
    public function createDeleteForm(Post $entity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', ['id' => $entity->getId()]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * @param Post $entity
     */
    public function onFormFailed(Post $entity)
    {
        $this->addFlashMessage('error', 'Form submission failed for ' . $entity->getTitle() . '!');
    }

    /**
     * @param string $type
     * @param string $message
     */
    protected function addFlashMessage($type, $message)
    {
        $this->getSession()->getFlashBag()->add($type, $message);
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }
}
