<?php

namespace App\Security\Model;

use Symfony\Component\Ldap\Entry;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /** @var string */
    protected $dn;

    /** @var string */
    protected $username;

    /** @var string */
    protected $email;

    /** @var string */
    protected $roles = [];

    /** @var string */
    protected $password;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $firstName;

    /** @var string */
    protected $salt;

    public function createUserFromLdap(Entry $entry, array $roles = ['ROLE_USER'])
    {
        $this->dn = $entry->getDn();
        $this->lastName = $entry->getAttribute('givenName')[0];
        $this->firstName = $entry->getAttribute('sn')[0];
        $this->email = $entry->getAttribute('mail')[0];
        $this->username = $entry->getAttribute('uid')[0];
        $this->roles = $roles;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getDn()
    {
        return $this->dn;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getFullName() {
        return $this->firstName.' '.$this->lastName;
    }

    public function eraseCredentials()
    {
    }
}
