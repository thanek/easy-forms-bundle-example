<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Configuration\Form;

/**
 * @Route("/post")
 * @Form("new_form",method="createCreateForm",starter="newAction",acceptor="createAction",rejector="onFormFailed")
 * @Form("edit_form",method="createEditForm",starter="editAction",acceptor="updateAction",rejector="onFormFailed")
 * @Form("delete_form",method="createDeleteForm",starter="editAction",acceptor="deleteAction")
 */
class PostController extends Controller
{

    /**
     * Lists all Post entities.
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
     * Finds and displays a Post entity.
     * @Route("/{id}", name="post_show", requirements={"id": "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $deleteForm = $this->createDeleteForm($entity);

        return [
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to create a new Post entity.
     * @Route("/new", name="post_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Post();
        return [
            'entity' => $entity,
        ];
    }

    /**
     * Creates a new Post entity.
     * @Route("/", name="post_create")
     * @Method("POST")
     * @Template("AppBundle:Post:new.html.twig")
     */
    public function createAction(Post $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        $this->getSession()->getFlashBag()->add('notice', 'Post '.$entity->getTitle().' added successfully!');

        return $this->redirect($this->generateUrl('post_show', ['id' => $entity->getId()]));
    }

    /**
     * @param Post $entity
     */
    public function onFormFailed(Post $entity)
    {
        $this->getSession()->getFlashBag()->add('error', 'Form submission failed for ' . $entity->getTitle() . '!');
    }

    /**
     * Creates a form to create a Post entity.
     *
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
     * Displays a form to edit an existing Post entity.
     * @Route("/{id}/edit", name="post_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        return [
            'entity' => $entity,
        ];
    }

    /**
     * Creates a form to edit a Post entity.
     *
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
     * Edits an existing Post entity.
     * @Route("/{id}", name="post_update")
     * @Method("PUT")
     * @Template("AppBundle:Post:edit.html.twig")
     */
    public function updateAction(Post $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        $this->getSession()->getFlashBag()->add('notice', 'Udało się zmienić!');

        return $this->redirect($this->generateUrl('post_edit', ['id' => $entity->getId()]));
    }

    /**
     * Deletes a Post entity.
     * @Route("/{id}", name="post_delete")
     * @Method("DELETE")
     * @Template("AppBundle:Post:edit.html.twig")
     */
    public function deleteAction(Post $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->getSession()->getFlashBag()->add('notice', 'Udało się usunąć!');

        return $this->redirect($this->generateUrl('post'));
    }

    /**
     * Creates a form to delete a Post entity by id.
     *
     * @param mixed $id The entity id
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
     * @return Session
     */
    protected function getSession()
    {
        /** @var $session Session */
        $session = $this->get('session');
        return $session;
    }
}
