<?php

namespace NextCellent\RSS\Writer\Media\Optional;

/**
 * The title of the particular media object. It has one optional attribute.
 * 
 * @author  Niko Strijbol
 */
class MediaTitle extends MediaElement
{
    /**
     * MediaTitle constructor.
     *
     * @param string $title The title.
     */
    public function __construct($title)
    {
        parent::__construct('media:title', $title, true);
    }

    /**
     * Specifies the type of text embedded. Possible values are either "plain" or "html".
     *
     * Default value is "plain". All HTML must be entity-encoded. It is an optional attribute.
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
}