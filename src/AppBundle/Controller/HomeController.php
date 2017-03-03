<?php
/**
 * Created by PhpStorm.
 * User: Asen
 * Date: 3.3.2017 г.
 * Time: 17:12
 */

namespace AppBundle\Controller;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use ImportBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class HomeController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('home/index.html.twig', ['menuItem' => 'home']);
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        $newUser = new User();
        $form = $this->createFormBuilder($newUser)
            ->add('username', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('password', PasswordType::class, ['attr' => ['class' => 'form-control']])
            ->add('save', SubmitType::class, ['label' => 'Register now!', 'attr' => ['class' => 'btn btn-primary']])->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newUser = $form->getData();
            /* @var $newUser User */
            $newUser->setDateCreated(new \DateTime());
            $encoder = new BCryptPasswordEncoder(12);
            $passwordHash = $encoder->encodePassword($newUser->getPassword(), '');
            $newUser->setPassword($passwordHash);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
            $em->flush();
            return $this->redirectToRoute('login', ['success' => 'You have registered!']);
        }

        return $this->render('home/register.html.twig', ['menuItem' => 'register', 'form' => $form->createView()]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $flagForNewlyRegisteredUser = $request->query->get('success');
        $newUser = new User();
        $form = $this->createFormBuilder($newUser)
            ->add('username', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('password', PasswordType::class, ['attr' => ['class' => 'form-control']])
            ->add('save', SubmitType::class, ['label' => 'Login', 'attr' => ['class' => 'btn btn-primary']])->getForm();

        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $newUser = $form->getData();
            /* @var $newUser User */
            $repo = $this->getDoctrine()->getRepository('ImportBundle:User');
            $searchedUser = $repo->findBy(['username' => $newUser->getUsername()]);

            if (!$searchedUser) {
                $error = 'Invalid data, please try again!';
            } else {
                $encoder = new BCryptPasswordEncoder(12);
                /* @var $searchedUser User */
                $searchedUser = $searchedUser[0];
                $passwordHash = $searchedUser->getPassword();
                if ($encoder->isPasswordValid($passwordHash, $newUser->getPassword(), '')) {
                    // login OK
//                    $auth = new DaoAuthenticationProvider()
                    $newUser->serialize();
                } else {
                    $error = 'Invalid data, please try again!';
                }
            }
        }

        return $this->render('home/login.html.twig',
            [
                'menuItem' => 'login',
                'flag' => $flagForNewlyRegisteredUser,
                'form' => $form->createView(),
                'error' => $error
            ]);
    }
}