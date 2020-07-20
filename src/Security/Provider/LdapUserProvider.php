<?php

namespace App\Security\Provider;

use App\Security\Model\User;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Ldap\Security\LdapUserProvider as BaseLdapUserProvider;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class LdapUserProvider extends BaseLdapUserProvider
{
    /** @var LdapInterface */
    protected $ldap;

    /** @var string */
    protected $baseDn;

    /** @var string|null */
    protected $searchDn;

    /** @var string|null */
    protected $searchPassword;

    /** @var string */
    protected $uidKey;


    public function __construct(LdapInterface $ldap, string $baseDn, string $searchDn = null, string $searchPassword = null, array $defaultRoles = [], string $uidKey = null, string $filter = null, string $passwordAttribute = null, array $extraFields = [])
    {
        parent::__construct($ldap, $baseDn, $searchDn, $searchPassword, $defaultRoles, $uidKey, $filter, $passwordAttribute, $extraFields);

        $this->ldap = $ldap;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->uidKey = $uidKey;
    }

    public function loadUserByEmail(string $email): UserInterface
    {
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $query = 'mail='.$email;
            $search = $this->ldap->query($this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $email), 0, $e);
        }

        $entries = $search->execute();

        $count = \count($entries);

        if (!$count) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $email));
        }

        if ($count > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }

        $entry = $entries[0];

        $username = $this->getAttributeValue($entry, $this->uidKey);

        return $this->loadUser($username, $entry);
    }

    /**
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $user;
    }

    private function getAttributeValue(Entry $entry, string $attribute): string
    {
        $values = $entry->getAttribute($attribute);

        if (!$values) {
            throw new InvalidArgumentException(sprintf('Missing attribute "%s" for user "%s".', $attribute, $entry->getDn()));
        }

        if (1 !== \count($values)) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" has multiple values.', $attribute));
        }

        return $values[0];
    }
}
