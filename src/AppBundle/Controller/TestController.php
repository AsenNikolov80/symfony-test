<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TestController extends Controller
{
    /**
     * @Route("/test/index/{name}")
     */
    public function indexAction(Request $request, $name)
    {
        return $this->render('test/index.html.twig', ['name' => $name]);
    }

    public function testAction()
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist(new User());
        $em->flush();
    }

}
