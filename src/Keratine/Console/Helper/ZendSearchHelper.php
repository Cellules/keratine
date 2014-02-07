<?php
namespace Keratine\Console\Helper;

use ZendSearch\Lucene\SearchIndexInterface;

use Symfony\Component\Console\Helper\Helper;

class ZendSearchHelper extends Helper
{
    /**
     * SearchIndexInterface.
     *
     * @var SearchIndexInterface
     */
    protected $_zendSearch;

    /**
     * Indices.
     *
     * @var array
     */
    protected $_indices;

    /**
     * Constructor.
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $zendSearch
     */
    public function __construct(SearchIndexInterface $zendSearch, $indices = array())
    {
        $this->_zendSearch = $zendSearch;
        $this->_indices = $indices;
    }

    /**
     * Retrieves SearchIndexInterface.
     *
     * @return SearchIndexInterface
     */
    public function getZendSearch()
    {
        return $this->_zendSearch;
    }

    /**
     * Retrieves indices.
     *
     * @return array
     */
    public function getIndices()
    {
        return $this->_indices;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zendsearch';
    }
}