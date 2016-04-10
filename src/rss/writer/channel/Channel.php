<?php

namespace NextCellent\RSS\Writer\Channel;

use NextCellent\RSS\Writer\Feed;
use NextCellent\RSS\Writer\Item;
use NextCellent\RSS\Writer\Media\MediaElement;
use NextCellent\RSS\Writer\SimpleXMLElement;

/**
 * Class Channel
 * @package Suin\RSSWriter
 */
class Channel extends SimpleXMLElement
{
    protected $image;
    
    public function __construct()
    {
        parent::__construct('channel');
    }

    /**
     * Set channel title
     * @param string $title
     * @return $this
     */
    public function title($title)
    {
        $this->simpleChild('title', $title, true);
        return $this;
    }

    /**
     * Set channel link
     * @param string $link
     * @return $this
     */
    public function link($link)
    {
        $this->simpleChild('link', $link);
        return $this;
    }

    /**
     * Set channel description
     * @param string $description
     * @return $this
     */
    public function description($description)
    {
        $this->simpleChild('description', $description, true);
        return $this;
    }

    /**
     * Set ISO639 language code
     *
     * The language the channel is written in. This allows aggregators to group all
     * Italian language sites, for example, on a single page. A list of allowable
     * values for this element, as provided by Netscape, is here. You may also use
     * values defined by the W3C.
     *
     * @param string $language
     * @return $this
     */
    public function language($language)
    {
        $this->simpleChild('language', $language);
        return $this;
    }

    /**
     * Set channel copyright
     * @param string $copyright
     * @return $this
     */
    public function copyright($copyright)
    {
        $this->simpleChild('copyright', $copyright);
        return $this;
    }

    /**
     * Set channel published date.
     *
     * @param string $pubDate Date in any format accepted by DateTime.
     *
     * @see \DateTime
     *
     * @return $this
     */
    public function pubDate($pubDate)
    {
        $this->simpleChild('pubDate', (new \DateTime($pubDate))->format(\DateTime::RFC822));
        return $this;
    }

    /**
     * Set channel last build date
     * @param string $lastBuildDate Date in any format accepted by DateTime.
     * @return $this
     */
    public function lastBuildDate($lastBuildDate)
    {
        $this->simpleChild('lastBuildDate', (new \DateTime($lastBuildDate))->format(\DateTime::RFC822));
        return $this;
    }

    /**
     * Set channel generator
     * 
     * @param string $generator
     *
     * @return $this
     */
    public function generator($generator) {
        $this->simpleChild('generator', $generator, true);
        return $this;
    }

    /**
     * Append to feed
     * @param Feed $feed
     * @return $this
     */
    public function appendTo(Feed $feed)
    {
        $feed->channel($this);
        return $this;
    }

    /**
     * Append a channel image.
     * 
     * @param Image $image
     *
     * @return $this
     */
    public function image(Image $image) {
        $this->children[] = $image;
        return $this;
    }
}
