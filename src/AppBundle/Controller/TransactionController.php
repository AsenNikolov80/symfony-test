<?php
/**
 * Created by PhpStorm.
 * User: Asen
 * Date: 5.3.2017 г.
 * Time: 16:32
 */

namespace AppBundle\Controller;


use ImportBundle\Entity\BankAccount;
use ImportBundle\Entity\BankAccountTransaction;
use ImportBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class TransactionController extends Controller
{
    /**
     * @Route("/refund/{id}", name="refund")
     */
    public function refundAction(Request $request, $id)
    {
        if ($request->request->get('confirm') == 'yes') {
            $repoTransactions = $this->getDoctrine()->getRepository(BankAccountTransaction::class);
            $transaction = $repoTransactions->find($id);
            $error = null;
            $success = null;
            /* @var $transaction BankAccountTransaction */
            if ($transaction) {
                if (!$this->checkForRights($this->getUser(), $transaction->getBankAccount()->getName())) {
                    return $this->redirectToRoute('home');
                }
                try {
                    $this->refundTransaction($transaction);
                    $success = 'Transaction refunded!';
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = 'No such transaction found!';
            }
            return $this->render('transaction/refund.html.twig', ['error' => $error, 'success' => $success]);
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @param User $user
     * @param string $bankAccountName
     * @return bool
     */
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

    /**
     * @param BankAccountTransaction $transaction
     */
    private function refundTransaction(BankAccountTransaction $transaction)
    {
        $repo = $this->getDoctrine()->getManager();

        $sourceAccount = $transaction->getBankAccount();
        $refundAmount = $transaction->getAmount();
        $newAccountBalance = $sourceAccount->getBalance() - $refundAmount;
        if ($newAccountBalance < 0) {
            throw new \Exception("Not enough money for refund!");
        }

        // create positive transaction
        $newTransactionPositive = new BankAccountTransaction();

        // account, who will receive the refund
        $refundedAccount = $transaction->getParentTx()->getBankAccount();
        $newTransactionPositive->setBalance($refundedAccount->getBalance() + $refundAmount);
        $newTransactionPositive->setDateCreated(new \DateTime());
        $newTransactionPositive->setAmount($refundAmount);

        $sourceAccount->setBalance($newAccountBalance);

        $newTransactionPositive->setBankAccount($refundedAccount);
        $refundedAccount->setBalance($refundedAccount->getBalance() + $refundAmount);

        // create negative transaction
        $newTransactionNegative = new BankAccountTransaction();
        $newTransactionNegative->setBankAccount($sourceAccount);
        $newTransactionNegative->setAmount(-$refundAmount);
        $newTransactionNegative->setBalance($newAccountBalance);
        $newTransactionNegative->setDateCreated(new \DateTime());
        $newTransactionNegative->setParentTx($newTransactionPositive);
        $newTransactionPositive->setParentTx($newTransactionNegative);

        // mark transaction as reverted
        $transaction->setRevertTransaction($newTransactionNegative);

        $repo->persist($newTransactionPositive);
        $repo->persist($newTransactionNegative);
        $repo->persist($sourceAccount);
        $repo->persist($refundedAccount);
        $repo->persist($transaction);

        $repo->flush();
    }
}