<?php

namespace src\rss\writer\media\optional;

/**
 * Highly relevant keywords describing the media object with typically a maximum of 10 words.
 * The keywords and phrases should be comma-delimited.
 * 
 * @author  Niko Strijbol
 */
class MediaKeywords extends MediaElement
{
    /**
     * MediaKeywords constructor.
     *
     * @param string $keywords A comma separated list of comments.
     */
    public function __construct($keywords)
    {
        parent::__construct('media:keywords', $keywords);
    }

    /**
     * Add a single keyword.
     * 
     * @param string $keyword
     *
     * @return $this
     */
    public function addKeyword($keyword)
    {
        $this->value .= ', ' . $keyword;
        return $this;
    }
}