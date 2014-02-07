<?php

namespace Keratine\Lucene;

use ReflectionClass;

use Doctrine\Common\Annotations\AnnotationReader;

use Symfony\Component\PropertyAccess\PropertyAccess;

use ZendSearch\Lucene\SearchIndexInterface;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;

/**
 * Lucene Index Manager
 *
 * @author Quentin Aupetit <quentin.aupetit@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class IndexManager
{
    private $index;

    private $reader;

    public function __construct(SearchIndexInterface $index)
    {
        $this->index = $index;
        $this->reader = new AnnotationReader();
    }

    /**
     * Returns the total number of non-deleted documents in this index.
     *
     * @return integer
     */
    public function numDocs()
    {
        return $this->index->numDocs();
    }

    /**
     * Create or update an indexed document
     *
     * @param object $object
     */
    public function index($object)
    {
        // create property accessor
        $accessor = PropertyAccess::createPropertyAccessor();

        // delete existing documents with same id
        foreach ($this->index->find('id:' . $accessor->getValue($object, 'id')) as $hit) {
            $this->index->delete($hit->id);
        }

        // create new Lucene document
        $doc = new Document();

        // add primary key to identify it in the search results
        $doc->addField(Field::keyword('id', $accessor->getValue($object, 'id')));

        // add entity class reference to identify it in the search results
        $doc->addField(Field::unIndexed('entityClass', get_class($object)));

        // analyze each property's annotations to see which ones must be add to the document
        $reflClass = new ReflectionClass($object);
        foreach ($reflClass->getProperties() as $property) {
            $reflProperty = new \ReflectionProperty($object, $property->name);
            $annotation = $this->reader->getPropertyAnnotation($reflProperty, '\Keratine\Lucene\Mapping\Annotation\DocumentField');
            if ($annotation) {
                $value = $accessor->getValue($object, $property->name);
                $value = $this->ensureString($value);
                // use the appropriate indexing strategy for the field
                switch ($annotation->type) {
                    case 'keyword':
                        $doc->addField(Field::keyword($property->name, $value, 'UTF-8'));
                        break;
                    case 'unIndexed':
                        $doc->addField(Field::unIndexed($property->name, $value, 'UTF-8'));
                        break;
                    case 'binary':
                        $doc->addField(Field::binary($property->name, $value));
                        break;
                    case 'text':
                        $doc->addField(Field::text($property->name, $value, 'UTF-8'));
                        break;
                    case 'unStored':
                    default:
                        $doc->addField(Field::unStored($property->name, $value, 'UTF-8'));
                        break;
                }
            }
        }

        // add the document to the index and commit it
        $this->index->addDocument($doc);
        $this->index->commit();
    }

    /**
     * Ensures that the value is a string
     *
     * @param mixed $value
     *
     * @return string
     **/
    private function ensureString($value)
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value) || $value instanceOf \Traversable) {
            $string = array();
            foreach ($value as $val) {
                if (is_object($val) && method_exists($val, '__toString')) {
                    $string[] = $val->__toString();
                }
                else {
                    $string[] = (string) $val;
                }
            }
            return implode(',', $string);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return $value->__toString();
        }
    }

    /**
     * Delete document on objects after being remove
     *
     * @param object $object
     */
    public function remove($object)
    {
        // create property accessor
        $accessor = PropertyAccess::createPropertyAccessor();

        // delete existing documents with same id
        foreach ($this->index->find('id:' . $accessor->getValue($object, 'id')) as $hit) {
            $this->index->delete($hit->id);
        }
    }

    /**
     * Deletes a document from the index.
     * $id is an internal document id
     *
     * @param integer|\ZendSearch\Lucene\Search\QueryHit $id
     * @throws \ZendSearch\Lucene\Exception\OutOfRangeException
     */
    public function delete($id)
    {
        $this->index->delete($id);
    }

    /**
     * Optimize index.
     *
     * Merges all segments into one
     */
    public function optimize()
    {
        $this->index->optimize();
    }
}