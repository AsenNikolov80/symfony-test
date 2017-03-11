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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ['form' => $form->createView(), 'error' => $error, 'success' => $success, 'menuItem' => 'transactions']);
    }

    /**
     * @Route("/transaction/new/{name}/{userName}", name="start-transaction")
     */
    public function startTransactionAction(Request $request, $name, $userName)
    {
        /* @var $user User */
        $user = $this->getUser();
        if (!$this->checkForRights($user, $name)) {
            return $this->redirectToRoute('home');
        }
        /* @var $currentAccount BankAccount */
        $repoAccounts = $this->getDoctrine()->getRepository(BankAccount::class);
        $repoUsers = $this->getDoctrine()->getRepository(User::class);
        $targetUser = $repoUsers->findOneBy(['username' => $userName]);
        $currentAccount = $repoAccounts->findOneBy(['name' => $name, 'customer' => $user->getId()]);

        $availableAccounts = $this->getBankAccountsByUser($targetUser->getId(), $name);
        $dropdownFormatAvailableAccounts = [];
        /* @var $availableAccount BankAccount */
        foreach ($availableAccounts as $availableAccount) {
            $dropdownFormatAvailableAccounts[$availableAccount->getName()] = $availableAccount->getId();
        }
        $sourceTransaction = new BankAccountTransaction();
        $form = $this->createFormBuilder($sourceTransaction)
            ->add('amount', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('revertTransaction', HiddenType::class)
            ->add('bankAccount', ChoiceType::class, ['choices' => $availableAccounts, 'choice_label' => function ($account) {
                /* @var $account BankAccount */
                return $account->getName();
            }, 'attr' => ['class' => 'form-control']])
            ->add('save', SubmitType::class, ['label' => 'Start transaction', 'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:15px']])->getForm();
        $form->handleRequest($request);
        $error = null;
        $success = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $oldAmount = $currentAccount->getBalance();
            /* @var $sourceTransaction BankAccountTransaction */
            $sourceTransaction = $form->getData();
            $amountOfTransaction = $sourceTransaction->getAmount();
            if ($oldAmount >= $amountOfTransaction && $amountOfTransaction > 0) {

                $targetAccount = $sourceTransaction->getBankAccount();
                $targetAccount->setBalance($targetAccount->getBalance() + $amountOfTransaction);
                $targetAccount->setModified(new \DateTime());

                $sourceTransaction->setBalance($targetAccount->getBalance());
                $sourceTransaction->setDateCreated(new \DateTime());

                $currentAccount->setBalance($currentAccount->getBalance() - $amountOfTransaction);
                $currentAccount->setModified(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($sourceTransaction);
                $em->persist($targetAccount);
                $answerTransaction = new BankAccountTransaction();
                $answerTransaction->setAmount(-$amountOfTransaction);
                $answerTransaction->setBalance($oldAmount - $amountOfTransaction);
                $answerTransaction->setDateCreated(new \DateTime());
                $answerTransaction->setBankAccount($currentAccount);
                $answerTransaction->setParentTx($sourceTransaction);

                $em->persist($answerTransaction);
                $sourceTransaction->setParentTx($answerTransaction);
                $em->flush();

                $success = "Successful transaction!";
            } else {
                $error = 'Not enough money!';
            }
        }

        return $this->render('user/transaction-new.html.twig',
            ['name' => $name,
                'accounts' => $availableAccounts,
                'form' => $form->createView(),
                'amount' => $currentAccount->getBalance(),
                'error' => $error,
                'success' => $success,
                'menuItem' => 'transactions'
            ]);
    }

    /**
     * @Route("/transaction/{name}", name="choose-account")
     */
    public function chooseTargetUser(Request $request, $name)
    {
        $repoUsers = $this->getDoctrine()->getRepository(User::class);
        $users = $repoUsers->findAll();
        $targetUsername = $request->request->get('username');
        $error = null;
        if ($targetUsername) {
            $targetUser = $repoUsers->findOneBy(['username' => $targetUsername]);
            if ($targetUser) {
                return $this->redirectToRoute('start-transaction', ['name' => $name, 'userName' => $targetUser->getUsername()]);
            } else {
                $error = 'No such user found!';
            }
        }
        return $this->render('user/choose-user.html.twig',
            ['name' => $name, 'error' => $error]);
    }

    /**
     * @Route("/transactions/{id}", name="transactions")
     */
    public function listTransactions($id)
    {
        $repoTransactions = $this->getDoctrine()->getRepository(BankAccountTransaction::class);
        $repoAccounts = $this->getDoctrine()->getRepository(BankAccount::class);
        /* @var $account BankAccount */
        $account = $repoAccounts->find($id);
        if (!$this->checkForRights($this->getUser(), $account->getName())) {
            return $this->redirectToRoute('home');
        }

        /* @var $repoTransactions EntityRepository */
        $revertedTransactionIdsArray = $repoTransactions->createQueryBuilder('t')
            ->where('t.revertTransaction IS NOT NULL')->getQuery()->getResult();
        $revertedTransactionIds = [];
        foreach ($revertedTransactionIdsArray as $item) {
            /* @var $item BankAccountTransaction */
            $revertedTransactionIds[] = $item->getRevertTransaction()->getId();
        }
        // get transactions for current account
        $listTransactions = $repoTransactions->findBy(['bankAccount' => $account->getId()], ['dateCreated' => 'DESC']);
        return $this->render('user/transaction-list.html.twig',
            [
                'listTransactions' => $listTransactions,
                'revertedTransactionIds' => $revertedTransactionIds,
                'menuItem' => 'transactions'
            ]);
    }

    /**
     * @Route("/transactions", name="transactions-all")
     */
    public function listTransactionsAll(Request $request)
    {
        $targetAccountId = $request->request->get('accountName');
        if ($targetAccountId) {
            $repoAccounts = $this->getDoctrine()->getRepository(BankAccount::class);
            $targetAccount = $repoAccounts->find($targetAccountId);
            if (!$this->checkForRights($this->getUser(), $targetAccount->getName())) {
                return $this->redirectToRoute('home');
            }
            return $this->redirectToRoute('transactions', ['id' => $targetAccount->getId()]);
        }
        $accountsRaw = $this->getBankAccountsByUser($this->getUser()->getId());
        $accounts = [];
        foreach ($accountsRaw as $item) {
            /* @var $item BankAccount */
            $accounts[] = ['id' => $item->getId(), 'value' => $item->getName()];
        }
        $menuItem = 'transactions';
        return $this->render('user/transaction-list-all.html.twig',
            ['accounts' => $accounts, 'menuItem' => $menuItem]);
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
        if (!$account || ($account->getCustomer()->getId() != $user->getId())) {
            return false;
        }
        return true;
    }
}