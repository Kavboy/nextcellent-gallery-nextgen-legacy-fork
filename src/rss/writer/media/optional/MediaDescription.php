<?php

namespace NextCellent\RSS\Writer\Media\Optional;

/**
 * Short description describing the media object typically a sentence in length.
 * It has one optional attribute.
 *
 * @author  Niko Strijbol
 */
class MediaDescription extends MediaElement
{
    /**
     * MediaDescription constructor.
     *
     * @param string $description The description.
     */
    public function __construct($description)
    {
        parent::__construct('media:description', $description, true);
    }

    /**
     * Specifies the type of text embedded. Possible values are either "plain" or "html".
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