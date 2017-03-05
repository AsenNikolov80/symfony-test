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
            /* @var $transaction BankAccountTransaction */
            if ($transaction) {
                if (!$this->checkForRights($this->getUser(), $transaction->getBankAccount()->getName())) {
                    return $this->redirectToRoute('home');
                }
                try {
                    $this->refundTransaction($transaction);
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = 'No such transaction found!';
            }
            return $this->render('transaction/refund.html.twig', []);
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

        $newTransaction = new BankAccountTransaction();
        $newTransaction->setBalance($transaction->getBankAccount()->getBalance() - $transaction->getAmount());
        $newTransaction->setDateCreated(new \DateTime());
        $newTransaction->setAmount($transaction->getAmount());
        $newTransaction->setParentTx($transaction);

        $sourceAccount = $transaction->getBankAccount();
        $newTransaction->setBankAccountSource($sourceAccount);
        $newAccountBalance = $sourceAccount->getBalance() - $transaction->getAmount();
        if ($newAccountBalance < 0) {
            throw new \Exception("Not enough money for refund");
        }
        $sourceAccount->setBalance($newAccountBalance);

        $refundedAccount = $transaction->getBankAccountSource();
        $newTransaction->setBankAccount($refundedAccount);
        $refundedAccount->setBalance($refundedAccount->getBalance() + $transaction->getAmount());

        $repo->persist($newTransaction);
        $repo->persist($sourceAccount);
        $repo->persist($refundedAccount);
        $repo->flush();
        $transaction->setParentTx($newTransaction);
        $repo->persist($transaction);
        $repo->flush();
    }
}