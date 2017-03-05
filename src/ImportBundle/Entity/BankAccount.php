<?php

namespace ImportBundle\Entity;

/**
 * BankAccount
 */
class BankAccount
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $balance;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ImportBundle\Entity\User
     */
    private $customer;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return BankAccount
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set balance
     *
     * @param string $balance
     *
     * @return BankAccount
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
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return BankAccount
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
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
     * Set customer
     *
     * @param \ImportBundle\Entity\User $customer
     *
     * @return BankAccount
     */
    public function setCustomer(\ImportBundle\Entity\User $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \ImportBundle\Entity\User
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}

