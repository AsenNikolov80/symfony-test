<?php

namespace ImportBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 */
class User implements UserInterface, \Serializable
{
    const ROLE_ADMIN = 1;
    const ROLE_USER = 0;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @var integer
     */
    private $role;

    /**
     * Set username
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set dateCreated
     * @param \DateTime $dateCreated
     * @return User
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Get id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
    }

    public function unserialize($serialized)
    {
        return list(
            $this->id,
            $this->username,
            $this->password) = unserialize($serialized);
    }

    public function getRoles()
    {
        $result = ['ROLE_USER'];
        if ($this->role == self::ROLE_ADMIN) {
            $result[] = 'ROLE_ADMIN';
        }
        return $result;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return integer
     */
    public function getRole()
    {
        return (int)$this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
}

