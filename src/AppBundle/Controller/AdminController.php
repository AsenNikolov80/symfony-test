<?php
/**
 * Created by PhpStorm.
 * User: Asen
 * Date: 3.3.2017 г.
 * Time: 22:09
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction()
    {
        return $this->render('admin/home.html.twig', []);
    }

}