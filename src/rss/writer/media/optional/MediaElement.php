<?php

namespace NextCellent\RSS\Writer\Media\Optional;

use NextCellent\RSS\Writer\Channel\Channel;
use NextCellent\RSS\Writer\Item;
use NextCellent\RSS\Writer\Media\MediaContent;
use NextCellent\RSS\Writer\Media\MediaGroup;
use NextCellent\RSS\Writer\SimpleXMLElement;

/**
 * If PHP ever gets union types, this can be reduced to one function.
 * 
 * @author  Niko Strijbol
 */
abstract class MediaElement extends SimpleXMLElement
{
    /**
     * Append to an item
     * 
     * @param Item $item
     *
     * @return $this
     */
    public function appendToItem(Item $item)
    {
        $item->addChild($this);
        return $this;
    }

    /**
     * Append to a channel
     * 
     * @param Channel $channel
     *
     * @return $this
     */
    public function appendToChannel(Channel $channel)
    {
        $channel->addChild($this);
        return $this;
    }

    /**
     * Append to media content
     * 
     * @param MediaContent $content
     *
     * @return $this
     */
    public function appendToMediaContent(MediaContent $content)
    {
        $content->addChild($this);
        return $this;
    }

    /**
     * Append to media group
     * @param MediaGroup $group
     *
     * @return $this
     */
    public function appendToMediaGroup(MediaGroup $group)
    {
        $group->addChild($this);
        return $this;
    }
}