<?php

namespace App\Security;

use App\Security\Model\User;
use App\Security\Provider\LdapUserProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Ldap\Security\LdapUser;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /** @var RouterInterface */
    private $router;

    /** @var LdapInterface */
    private $ldap;

    public function __construct(LdapInterface $ldap, RouterInterface $router)
    {
        $this->router = $router;
        $this->ldap = $ldap;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    /**
     * @return User|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$userProvider instanceof LdapUserProvider) {
            return null;
        }

        /*
         * @var LdapUserProvider $userProvider
         */

        //Par username
        try {
            $userLdap = $userProvider->loadUserByUsername($credentials['username']);
        } catch (UsernameNotFoundException $e) {
            //Par email
            $userLdap = $userProvider->loadUserByEmail($credentials['username']);
        }

        if (!$userLdap instanceof LdapUser) {
            return null;
        }

        return new User($userLdap->getEntry(), $userLdap->getRoles());
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        /* @var User $user */

        try {
            $this->ldap->bind($user->getDn(), $credentials['password']);

            return true;
        } catch (ConnectionException $e) {
            return false;
        }
    }

    /**
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->getHomepageUrl());
    }

    protected function getHomepageUrl(): string
    {
        return $this->router->generate('app_homepage');
    }
}
