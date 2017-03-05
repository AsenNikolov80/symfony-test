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
     * @var \ImportBundle\Entity\BankAccountTransaction
     */
    private $parentTx;

    /**
     * @var \ImportBundle\Entity\BankAccount
     */
    private $bankAccount;

    /**
     * @var \ImportBundle\Entity\BankAccount
     */
    private $bankAccountSource;


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
     * @return float
     */
    public function getAmount()
    {
        return (float)$this->amount;
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
     * @return float
     */
    public function getBalance()
    {
        return (float)$this->balance;
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
     * @param \ImportBundle\Entity\BankAccountTransaction $parentTx
     *
     * @return BankAccountTransaction
     */
    public function setParentTx(\ImportBundle\Entity\BankAccountTransaction $parentTx = null)
    {
        $this->parentTx = $parentTx;

        return $this;
    }

    /**
     * Get parentTx
     *
     * @return \ImportBundle\Entity\BankAccountTransaction
     */
    public function getParentTx()
    {
        return $this->parentTx;
    }

    /**
     * Set bankAccount
     *
     * @param \ImportBundle\Entity\BankAccount $bankAccount
     *
     * @return BankAccountTransaction
     */
    public function setBankAccount(\ImportBundle\Entity\BankAccount $bankAccount = null)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return \ImportBundle\Entity\BankAccount
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @return BankAccount
     */
    public function getBankAccountSource()
    {
        return $this->bankAccountSource;
    }

    /**
     * @param BankAccount $bankAccountSource
     */
    public function setBankAccountSource($bankAccountSource)
    {
        $this->bankAccountSource = $bankAccountSource;
    }
}

