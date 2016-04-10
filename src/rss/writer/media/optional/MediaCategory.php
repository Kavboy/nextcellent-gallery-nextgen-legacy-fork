<?php

namespace NextCellent\RSS\Writer\Media\Optional;

/**
 * Allows a taxonomy to be set that gives an indication of the type of media content,
 * and its particular contents. It has two optional attributes.
 *
 * @author  Niko Strijbol
 */
class MediaCategory extends MediaElement
{
    /**
     * MediaCategory constructor.
     *
     * @param string $categories For example 'music/artist/album/song'.
     */
    public function __construct($categories)
    {
        parent::__construct('media:category', $categories);
    }

    /**
     * The URI that identifies the categorization scheme. It is an optional attribute.
     * If this attribute is not included,
     * the default scheme is "http://search.yahoo.com/mrss/category_schema".
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

    /**
     * The human readable label that can be displayed in end user applications.
     * It is an optional attribute.
     * 
     * @param string $label
     *
     * @return $this
     */
    public function label($label)
    {
        $this->attributes['label'] = $label;
        return $this;
    }
}