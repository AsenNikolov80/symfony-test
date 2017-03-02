<?php
/**
 * Created by PhpStorm.
 * User: Asen
 * Date: 2.3.2017 г.
 * Time: 21:05
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    private $id;
}