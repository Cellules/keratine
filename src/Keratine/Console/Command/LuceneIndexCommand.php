<?php
namespace Keratine\Console\Command;

use ReflectionClass;

use Doctrine\Common\Annotations\AnnotationReader;

use Keratine\Lucene\IndexManager;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LuceneIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('lucene:index')
            ->setDescription('Regenerate Lucene index for the given entityClass')
            ->addArgument(
                'entityClass',
                InputArgument::REQUIRED,
                'The entityClass to index'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get repository
        $entityClass = $input->getArgument('entityClass');
        $repository = $this->getHelper('em')->getEntityManager()->getRepository($entityClass);

        $reflClass = new ReflectionClass($entityClass);
        $reader = new AnnotationReader();
        $annotation = $reader->getClassAnnotation($reflClass, '\Keratine\Lucene\Mapping\Annotation\Indexable');

        if (!$annotation) {
            $output->writeln(sprintf('<error>%s must define the "%s" annotation.</error>', $entityClass, '\Keratine\Lucene\Mapping\Annotation\Indexable'));
            return;
        }

        $indexManager = new IndexManager($this->getHelper('zendsearch')->getIndices()[$annotation->index]);

        // delete all indexed documents
        $numDocs = $indexManager->numDocs();
        for ($id = 0; $id < $numDocs; $id++) {
            $indexManager->delete($id);
        }

        // index each entity
        foreach ($repository->findAll() as $entity) {
            $indexManager->index($entity);
        }

        // optimize index
        $indexManager->optimize();

        // get number of indexed documents
        $numDocs = $indexManager->numDocs();

        $output->writeln(sprintf('<info>%d document(s) indexed</info>', $numDocs));
    }
}