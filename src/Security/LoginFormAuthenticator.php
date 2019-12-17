<?php


namespace App\Security;


use App\Security\Model\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Ldap\Security\LdapUser;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var LdapInterface */
    private $ldap;

    public function __construct(LdapInterface $ldap,
                                RouterInterface $router,
                                UserPasswordEncoderInterface $passwordEncoder
                                )
    {
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
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

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        //Par username
        try {
            $userLdap = $userProvider->loadUserByUsername($credentials['username']);
        } catch (UsernameNotFoundException $e) {
            //Par email
            $userLdap = $userProvider->loadUserByEmail($credentials['username']);
        }

        if(!$userLdap instanceof LdapUser) {
            return null;
        }

        /** @var LdapUser $userLdap */
        $user = new User();

        return $user->createUserFromLdap($userLdap->getEntry(), $userLdap->getRoles());
    }

    public function checkCredentials($credentials, UserInterface $user)
    {

        if(!$user instanceof User) {
            return false;
        }

        /** @var User $user */

        try {
            $this->ldap->bind($user->getDn(), $credentials['password']);
            return true;
        } catch(ConnectionException $e) {
            return false;
        }

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->getHomepageUrl());
    }

    protected function getHomepageUrl()
    {
        return $this->router->generate('app_homepage');
    }
}