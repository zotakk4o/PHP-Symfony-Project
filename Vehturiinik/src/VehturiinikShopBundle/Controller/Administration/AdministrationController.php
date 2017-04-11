<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdministrationController
 * @package VehturiinikShopBundle\Controller\Administration
 * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_EDITOR')")
 */
class AdministrationController extends Controller
{
    /**
     * @Route("/administration",name="view_admin_panel")
     */
    public function indexAction()
    {
        return $this->render('administration/panel.html.twig');
    }

}
