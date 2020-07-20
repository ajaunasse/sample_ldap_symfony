<?php

namespace App\Security\Model;

use Symfony\Component\Ldap\Entry;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /** @var string|null */
    protected $dn;

    protected $ou;

    /** @var string */
    protected $username;

    /** @var string|null */
    protected $email;

    /** @var string[] */
    protected $roles = ['ROLE_USER'];

    /** @var string|null */
    protected $password;

    /** @var string|null */
    protected $lastName;

    /** @var string|null */
    protected $firstName;

    /** @var string|null */
    protected $salt;

    public function __construct(Entry $entry, array $roles = ['ROLE_USER'])
    {

        if(strpos( $entry->getDn(), 'ou') > 0 ) {
            $dnAsarray = explode(',', $entry->getDn());
            foreach ($dnAsarray as $val ) {
                $tmp = explode('=', $val);
                if('ou' == $tmp[0]) {
                    $this->ou = $tmp[1];
                }
            }
        }
        $this->dn = $entry->getDn();

        if (!empty($lastname = $entry->getAttribute('givenName'))) {
            $this->lastName = $lastname[0];
        }

        if (!empty($firstName = $entry->getAttribute('sn'))) {
            $this->firstName = $firstName[0];
        }

        if (!empty($mail = $entry->getAttribute('mail'))) {
            $this->email = $mail[0];
        }

        if (!empty($username = $entry->getAttribute('uid'))) {
            $this->username = $username[0];
        }

        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getDn(): ?string
    {
        return $this->dn;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }


    /**
     * @return void
     */
    public function eraseCredentials()
    {
    }
}
