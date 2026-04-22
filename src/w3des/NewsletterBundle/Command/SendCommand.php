<?php
namespace w3des\NewsletterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use w3des\AdminBundle\Entity\User;
use w3des\NewsletterBundle\Util;
use Symfony\Component\Console\Input\InputOption;
use w3des\NewsletterBundle\Entity\NewsletterContent;

class SendCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('admin:newsletter:send')
            ->setDescription('...')
            ->addOption('content', null, InputOption::VALUE_OPTIONAL)
            ->addOption('test', null, InputOption::VALUE_OPTIONAL)
            ->addOption('all', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        if (! $input->getOption('test') && ! $input->getOption('all')) {
            $output->writeln('<error>Podaj e-mail testowy</error>');
            return;
        }
        if ($input->getOption('content')) {
            $content = $this->getContainer()
                ->get('doctrine.orm.default_entity_manager')
                ->find(NewsletterContent::class, $input->getOption('content'));
            if ($content) {
                $this->process($content, $input->getOption('test'), $output);
            } else {
                $output->writeln('<error>Nie istnieje</error>');
            }
        } else {
            foreach ($this->getContainer()
                ->get('doctrine.orm.default_entity_manager')
                ->createQuery('select c from w3desNewsletterBundle:NewsletterContent c where c.sendAt is not null and (c.sent != :send or c.sent is null)')
                ->execute([
                'send' => true
            ]) as $cnt) {
                $this->process($cnt, $input->getOption('test'), $output);
            }
        }
    }

    protected function process(NewsletterContent $content, $email = null, OutputInterface $output)
    {
        $output->writeln('<info>Wysyłka ' . $content->getTitle() . ':</info>');
        $this->getContainer()
            ->get('newsletter')
            ->send($content, $email, $output);
        if ($email == null) {
            $content->setSent(true);
            $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
            $em->persist($content);
            $em->flush();
        }
    }
}
