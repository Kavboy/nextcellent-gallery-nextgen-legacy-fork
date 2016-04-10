<?php

namespace NextCellent\RSS\Writer\Media;

use NextCellent\RSS\Writer\Item;
use NextCellent\RSS\Writer\MediaContent;
use NextCellent\RSS\Writer\SimpleXMLElement;

/**
 * @author  Niko Strijbol
 * @version 10/04/2016
 */
class MediaGroup extends SimpleXMLElement
{
    public function __construct()
    {
        parent::__construct('media:group');
    }

    /**
     * Append to an item
     *
     * @param Item $item
     *
     * @return $this
     */
    public function appendTo(Item $item)
    {
        $item->addChild($this);
        return $this;
    }
}