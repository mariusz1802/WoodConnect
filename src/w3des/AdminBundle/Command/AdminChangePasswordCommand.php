<?php
namespace w3des\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use w3des\AdminBundle\Entity\User;

class AdminChangePasswordCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('admin:change-password')
            ->setDescription('...')
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail')
            ->addArgument('password', InputArgument::REQUIRED, 'Hasło');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $user = $em->getRepository(User::class)->findOneBy([
            'email' => strtolower($input->getArgument('email'))
        ]);
        if (!$user) {
            $output->writeln('<error>User not exists!</error>');
            return;
        }


        $pass = $this->getContainer()
            ->get('security.password_encoder')
            ->encodePassword($user, $input->getArgument('password'));
        $user->setPassword($pass);
        $user->setEnabled(true);
        $em->persist($user);
        $em->flush();
    }
}
