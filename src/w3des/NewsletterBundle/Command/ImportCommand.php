<?php
namespace w3des\NewsletterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use w3des\AdminBundle\Entity\User;
use w3des\NewsletterBundle\Util;

class ImportCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('admin:newsletter:import')
            ->setDescription('...')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV')
            ->addArgument('locale', InputArgument::REQUIRED);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $util = new Util();
        $output->writeln($util->import($em, $input->getArgument('locale'), $input->getArgument('file')));
    }
}
