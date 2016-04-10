<?php

namespace NextCellent\RSS\Writer\Media;

use NextCellent\RSS\Writer\Item;
use NextCellent\RSS\Writer\SimpleXMLElement;

/**
 * @author  Niko Strijbol
 * @version 10/04/2016
 */
class MediaContent extends SimpleXMLElement
{

    public function __construct()
    {
        parent::__construct('media:content');
    }

    /**
     * @param string $url should specify the direct URL to the media object.
     *
     * @return $this
     */
    public function url($url)
    {
        $this->attributes['url'] = $url;
        return $this;
    }

    /**
     * The number of bytes of the media object. It is an optional attribute.
     *
     * @param int|string $size
     *
     * @return $this
     */
    public function fileSize($size)
    {
        $this->attributes['fileSize'] = $size;
        return $this;
    }

    /**
     * The standard MIME type of the object. It is an optional attribute.
     *
     * @param string $type
     *
     * @return $this
     */
    public function type($type)
    {
        $this->attributes['type'] = $type;
        return $this;
    }

    /**
     * The type of object. While this attribute can at times seem redundant if
     * type is supplied, it is included because it simplifies decision making
     * on the reader side, as well as flushes out any ambiguities between
     * MIME type and object type. It is an optional attribute.
     *
     * @param string $medium (image | audio | video | document | executable)
     *
     * @return $this
     */
    public function medium($medium)
    {
        $this->attributes['medium'] = $medium;
        return $this;
    }

    /**
     * Determines if this is the default object that should be used for the <media:group>.
     * There should only be one default object per <media:group>. It is an optional attribute.
     * 
     * @param bool $default
     *
     * @return $this
     */
    public function isDefault($default)
    {
        $this->attributes['isDefault'] = $default;
        return $this;
    }

    /**
     * Append to a media group
     *
     * @param MediaGroup $group
     *
     * @return $this
     */
    public function appendToMediaGroup(MediaGroup $group)
    {
        $group->addChild($this);
        return $this;
    }

    public function appendToItem(Item $item)
    {
        $item->addChild($this);
        return $this;
    }
}