<?php
/**
 * Created by PhpStorm.
 * User: Asen
 * Date: 3.3.2017 г.
 * Time: 22:09
 */

namespace AppBundle\Controller;


use Doctrine\ORM\EntityRepository;
use ImportBundle\Entity\BankAccount;
use ImportBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @Route("/admin/funds/list", name="edit-funds")
     */
    public function listFundsAction()
    {
        // get all users
        $repoUsers = $this->getDoctrine()->getRepository(User::class);
        $users = $repoUsers->findAll();
        return $this->render('admin/home.html.twig', ['users' => $users, 'menuItem' => 'edit-funds']);
    }

    /**
     * @Route("/admin/user/{id}/accounts", name="edit-user")
     */
    public function editUserAction(Request $request, $id)
    {
        $repoUsers = $this->getDoctrine()->getRepository(User::class);
        /* @var $user User */
        $user = $repoUsers->findOneBy(['id' => $id]);
        $error = null;
        $success = null;
        $shouldRenderInfo = true;
        if (!$user) {
            $error = 'No user found!';
            $shouldRenderInfo = false;
            return $this->render('admin/edit-user-account.html.twig',
                [
                    'user' => $user,
                    'error' => $error,
                    'shouldRenderInfo' => $shouldRenderInfo
                ]);
        }
        $accounts = $this->getBankAccountsByUser($user->getId());
        $accountsIds = [];
        foreach ($accounts as $account) {
            /* @var $account BankAccount */
            $accountsIds[] = $account->getId();
        }
        $amount = $request->request->get('amount');
        $action = $request->request->get('action');
        $accountId = $request->request->get('accountId');
        if ($amount && $action && $accountId) {
            // form is submitted
            if (in_array($accountId, $accountsIds)) {
                $repoAccounts = $this->getDoctrine()->getRepository(BankAccount::class);
                $targetAccount = $repoAccounts->findOneBy(['id' => $accountId, 'customer' => $user]);
                /* @var $targetAccount BankAccount */
                $newBalance = $targetAccount->getBalance();
                if ($action == 'Add') {
                    $newBalance = $targetAccount->getBalance() + $amount;
                } elseif ($action == 'Remove') {
                    if ($targetAccount->getBalance() - $amount >= 0)
                        $newBalance = $targetAccount->getBalance() - $amount;
                    else
                        $error = 'Not enough money! Account have only: ' . $targetAccount->getBalance();
                }
                if ($targetAccount->getBalance() != $newBalance && !$error) {
                    $targetAccount->setBalance($newBalance);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($targetAccount);
                    $em->flush();
                    $success = 'Account updated!';
                }
            } else {
                // desired account not belongs to the currently selected user
                $error = 'Wrong account selected!';
            }
        }
        return $this->render('admin/edit-user-account.html.twig',
            [
                'user' => $user,
                'accounts' => $accounts,
                'error' => $error,
                'success' => $success,
                'shouldRenderInfo' => $shouldRenderInfo,
                'menuItem' => 'edit-funds'
            ]);
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
}