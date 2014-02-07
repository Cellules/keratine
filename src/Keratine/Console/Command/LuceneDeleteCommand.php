<?php
namespace Keratine\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LuceneDeleteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('lucene:delete')
            ->setDescription('Delete Lucene document')
            ->addArgument(
                'id',
                InputArgument::OPTIONAL,
                'The id of the document to delete.'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'If set, the task will delete all indexed documents.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            // delete all indexed documents
            $numDocs = $this->getHelper('zendsearch')->getZendSearch()->numDocs();
            for ($id = 0; $id < $numDocs; $id++) {
                $this->getHelper('zendsearch')->getZendSearch()->delete($id);
            }
            $output->writeln(sprintf('<info>%d documents deleted</info>', $numDocs));
            return;
        }
        else if ($id = $input->getArgument('id')) {
            $hits = $this->getHelper('zendsearch')->getZendSearch()->find('id:' . $id);
            if (count($hits) > 0) {
                foreach ($hits as $hit) {
                    $this->getHelper('zendsearch')->getZendSearch()->delete($hit->id);
                }
                $output->writeln('<info>Document deleted</info>');
            }
            else {
                $output->writeln('<info>No document found</info>');
            }
        }
    }
}