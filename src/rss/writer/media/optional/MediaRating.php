<?php

namespace NextCellent\RSS\Writer\Media\Optional;

/**
 * This allows the permissible audience to be declared. If this element is not included,
 * it assumes that no restrictions are necessary. It has one optional attribute.
 */
class MediaRating extends MediaElement
{
    /**
     * MediaRating constructor.
     *
     * @param string $rating The rating of the content.
     */
    public function __construct($rating)
    {
        parent::__construct('media:rating', $rating);
    }

    /**
     * The URI that identifies the rating scheme. It is an optional attribute.
     * If this attribute is not included, the default scheme is urn:simple (adult | nonadult).
     * 
     * @param string $scheme
     *
     * @return $this
     */
    public function scheme($scheme)
    {
        $this->attributes['scheme'] = $scheme;
        return $this;
    }
}