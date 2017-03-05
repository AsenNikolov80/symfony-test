<?php
/**
 * Created by PhpStorm.
 * User: Asen
 * Date: 3.3.2017 г.
 * Time: 22:09
 */

namespace AppBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use ImportBundle\Entity\BankAccount;
use ImportBundle\Entity\BankAccountTransaction;
use ImportBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Adapter\ExtLdap\Query;

class UserController extends Controller
{
    /**
     * @Route("/account/list", name="user")
     */
    public function listAccountsAction()
    {
        /* @var $user User */
        $user = $this->getUser();
        $accounts = $this->getBankAccountsByUser($user->getId());
        $menuItem = 'user';
        return $this->render('user/home.html.twig', ['accounts' => $accounts, 'menuItem' => $menuItem]);
    }

    /**
     * @Route("/account/add", name="addAccount")
     */
    public function addBillAction(Request $request)
    {
        $newbankAccount = new BankAccount();
        $form = $this->createFormBuilder($newbankAccount)
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('save', SubmitType::class, ['label' => 'Add account', 'attr' => ['class' => 'btn btn-primary']])->getForm();

        $form->handleRequest($request);
        $error = null;
        $success = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $newbankAccount BankAccount */
            try {
                $newbankAccount = $form->getData();
                $newbankAccount->setBalance(0);
                $newbankAccount->setModified(new \DateTime());
                $newbankAccount->setCustomer($this->getUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($newbankAccount);
                $em->flush();
                $success = 'Bank account successfully added - "' . $newbankAccount->getName() . '"';
            } catch (\Exception $e) {
                $error = 'Something went wrong, please try again!';
            }
        }
        return $this->render('user/add-bill.html.twig',
            ['form' => $form->createView(), 'error' => $error, 'success' => $success]);
    }

    /**
     * @Route("/transaction/new/{name}", name="start-transaction")
     */
    public function startTransactionAction(Request $request, $name)
    {
        /* @var $user User */
        $user = $this->getUser();
        $this->checkForRights($user, $name);
        $availableAccounts = $this->getBankAccountsByUser($user->getId(), $name);
        $dropdownFormatAvailableAccounts = [];
        /* @var $availableAccount BankAccount */
        foreach ($availableAccounts as $availableAccount) {
            $dropdownFormatAvailableAccounts[$availableAccount->getName()] = $availableAccount->getId();
        }
        $newTransaction = new BankAccountTransaction();
        $form = $this->createFormBuilder($newTransaction)
            ->add('amount', IntegerType::class, ['attr' => ['min' => 0.01, 'class' => 'form-control']])
            ->add('bank_account', ChoiceType::class, ['choices' => $dropdownFormatAvailableAccounts, 'attr' => ['min' => 0.01, 'class' => 'form-control']])
            ->add('save', SubmitType::class, ['label' => 'Start transaction', 'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:15px']])->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        }
        /* @var $currentAccount BankAccount */
        $repo = $this->getDoctrine()->getRepository(BankAccount::class);
        $currentAccount = $repo->findOneBy(['name' => $name, 'customer' => $user->getId()]);
        return $this->render('user/transaction-new.html.twig',
            ['name' => $name, 'accounts' => $availableAccounts, 'form' => $form->createView(), 'amount' => $currentAccount->getBalance()]);
    }

    private function getBankAccountsByUser($userId, $excludedAccountName = null)
    {
        /* @var $repository EntityRepository */
        $repository = $this->getDoctrine()->getRepository(BankAccount::class);
        $q = $repository->createQueryBuilder('ba')->where('ba.customer=:id')->setParameter('id', $userId);
        if ($excludedAccountName) {
            $q->andWhere('ba.name!=:name')->setParameter('name', $excludedAccountName);
        }
        $q = $q->getQuery();
        $accounts = $q->getResult();
        return $accounts;
    }

    private function checkForRights(User $user, $bankAccountName)
    {
        $repo = $this->getDoctrine()->getRepository(BankAccount::class);
        $account = $repo->findOneBy(['name' => $bankAccountName, 'customer' => $user->getId()]);
        /* @var $account BankAccount */
        if ($account->getCustomer() != $user->getId()) {
            return $this->redirectToRoute('home');
        }
        return true;
    }
}