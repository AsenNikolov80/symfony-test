<?php

namespace AppBundle\Controller;

use ImportBundle\Entity\User;
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

    /**
     * @Route("/user/add/{username}")
     */
    public function testAction($username)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $newUser = new User();
            $newUser->setUsername($username);
            $newUser->setDateCreated(new \DateTime());
            $em->persist($newUser);
            $em->flush();
            return $this->render('test/add.html.twig', ['name' => $newUser->getUsername()]);
        } catch (\Exception $e) {
            return $this->render('test/add.html.twig', ['name' => 'Something is broken, try with different username']);
        }
    }
}
