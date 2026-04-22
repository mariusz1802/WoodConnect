<?php
namespace w3des\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use w3des\AdminBundle\Entity\Node;

class IndexAllCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('admin:index-all')
            ->setDescription('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $luceneSearch = $this->getContainer()->get('ivory_lucene_search');
        $luceneSearch->eraseIndex('nodes');

        $index = $luceneSearch->getIndex('nodes');
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        foreach ($this->getContainer()->get('nodes')->getCfg() as $type => $cf) {
            if (!$cf['index']) {
                continue;
            }
            foreach ($em->createQuery('select n from w3desAdminBundle:Node n where n.type = :type')->execute(['type' => $type]) as $node) {
                $index->addDocument($this->getContainer()->get('nodes')->getDocument($node));
            }
            $index->commit();
        }

        $index->optimize();

    }
}
