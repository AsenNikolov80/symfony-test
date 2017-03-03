<?php

namespace ImportBundle\Entity;

/**
 * BankAccountTransaction
 */
class BankAccountTransaction
{
    /**
     * @var string
     */
    private $amount;

    /**
     * @var string
     */
    private $balance;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ImportBundle\Entity\BankAccountTransactions
     */
    private $parentTx;

    /**
     * @var \ImportBundle\Entity\BankAccounts
     */
    private $bankAccount;


    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return BankAccountTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set balance
     *
     * @param string $balance
     *
     * @return BankAccountTransaction
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get balance
     *
     * @return string
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return BankAccountTransaction
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parentTx
     *
     * @param \ImportBundle\Entity\BankAccountTransactions $parentTx
     *
     * @return BankAccountTransaction
     */
    public function setParentTx(\ImportBundle\Entity\BankAccountTransactions $parentTx = null)
    {
        $this->parentTx = $parentTx;

        return $this;
    }

    /**
     * Get parentTx
     *
     * @return \ImportBundle\Entity\BankAccountTransactions
     */
    public function getParentTx()
    {
        return $this->parentTx;
    }

    /**
     * Set bankAccount
     *
     * @param \ImportBundle\Entity\BankAccounts $bankAccount
     *
     * @return BankAccountTransaction
     */
    public function setBankAccount(\ImportBundle\Entity\BankAccounts $bankAccount = null)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return \ImportBundle\Entity\BankAccounts
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }
}

