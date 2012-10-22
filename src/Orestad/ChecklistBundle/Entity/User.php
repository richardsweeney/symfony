<?php

// src/Orestad/ChecklistBundle/Entity/User.php
namespace Orestad\ChecklistBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Orestad\ChecklistBundle\Entity\User
 */
class User implements UserInterface
{

    private $id;

    private $username;

    private $store;

    private $salt;

    private $password;

    private $email;

    private $isActive;

    public function __construct()
    {
        $this->isActive = true;
        $this->salt = md5(uniqid(null, true));
    }


    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }


    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }


    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    public function getStore()
    {
        return $this->store;
    }


    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }


    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    public function getSalt()
    {
        return $this->salt;
    }


    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function getRoles()
    {
        return array('ROLE_USER');
    }


    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        return $this->username === $user->getUsername();
    }
}
