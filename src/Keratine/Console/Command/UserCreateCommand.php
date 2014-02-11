<?php
namespace Keratine\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class UserCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'The username'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'The password'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'The email'
            )
            ->addOption(
                'roles',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'The user roles'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $email = $input->getArgument('email');

        $user = new \Entity\User();
        $user->setUsername($username);
        $user->setEmail($email);

        if ($roles = $input->getOption('roles')) {
            $user->setRoles($roles);
        }

        // encode the password
        $this->getHelper('users')->getUserProvider()->setUserPassword($user, $password);

        $this->getHelper('em')->getEntityManager()->persist($user);
        $this->getHelper('em')->getEntityManager()->flush();

        $output->writeln(sprintf('<info>Added %s user with password %s</info>', $username, $password));
    }
}