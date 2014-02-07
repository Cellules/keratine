<?php
namespace Keratine\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LuceneCountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('lucene:count')
            ->setDescription('Count the number of documents indexed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numDocs = $this->getHelper('zendsearch')->getZendSearch()->numDocs();
        $output->writeln(sprintf('<info>%d documents indexed</info>', $numDocs));
    }
}