<?php

namespace Keratine\Lucence;

interface Indexable
{
    // use now annotations instead of predefined methods, this interface is not necessary

    /**
     * @keratine:Indexable
     * to mark the field as indexable use property annotation @keratine:Indexable
     * this field value will be included in built document to index
     */
}
