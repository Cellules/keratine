<?php
namespace Keratine\Console\Helper;

use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Component\Console\Helper\Helper;

class UserProviderHelper extends Helper
{
    /**
     * UserProvider.
     *
     * @var UserProviderInterface
     */
    protected $_userProvider;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     */
    public function __construct(UserProviderInterface $userProvider)
    {
        $this->_userProvider = $userProvider;
    }

    /**
     * Retrieves UserProvider.
     *
     * @return UserProviderInterface
     */
    public function getUserProvider()
    {
        return $this->_userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user.provider';
    }
}