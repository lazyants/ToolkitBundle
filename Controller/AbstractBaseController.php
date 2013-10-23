<?php

namespace Lazyants\ToolkitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractBaseController extends Controller
{
    abstract protected function getRepository();

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \FOS\UserBundle\Doctrine\UserManager
     */
    protected function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    /**
     * @param mixed $data
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function checkIfFound($data)
    {
        if (is_array($data) || $data instanceof \Countable) {
            if (count($data) == 0) {
                throw $this->createNotFoundException();
            }
        } elseif ($data == null) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param mixed $query
     * @param integer $page
     * @param integer $perPage
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    protected function paginate($query, $page, $perPage = 10)
    {
        return $this->get('knp_paginator')->paginate($query, $page, $perPage);
    }

    /**
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    protected function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return $this->get('translator')->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @param string $role
     * @return bool
     */
    protected function isGranted($role)
    {
        return $this->get('security.context')->isGranted($role);
    }

    /**
     * @param string $role
     * @throws AccessDeniedException
     */
    protected function throwExceptionIfNotGranted($role)
    {
        if (false === $this->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function isGrantedSuperadmin()
    {
        $this->throwExceptionIfNotGranted('ROLE_SUPER_ADMIN');
    }

    /**
     * @param string $type
     * @param string $message
     */
    protected function flash($type, $message)
    {
        $this->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * @param string $message
     */
    protected function flashSuccess($message)
    {
        $this->flash('success', $message);
    }

    /**
     * @param string $message
     */
    protected function flashError($message)
    {
        $this->flash('error', $message);
    }

    /**
     * @return void
     */
    protected function flashSuccessStored()
    {
        $this->flashSuccess($this->trans('flash.successfully_stored', array(), 'LazyantsToolkit'));
    }

    protected function flashNotStored()
    {
        $this->flashError($this->trans('flash.store_failed', array(), 'LazyantsToolkit'));
    }

    /**
     * @return void
     */
    protected function flashSuccessDeleted()
    {
        $this->flashSuccess($this->trans('flash.successfully_deleted', array(), 'LazyantsToolkit'));
    }

    /**
     * @return void
     */
    protected function flashNotDeleted()
    {
        $this->flashError($this->trans('flash.delete_failed', array(), 'LazyantsToolkit'));
    }
}