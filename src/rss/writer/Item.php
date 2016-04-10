<?php

namespace NextCellent\RSS\Writer;

use NextCellent\RSS\Writer\Channel\Channel;
use NextCellent\RSS\Writer\Media\MediaElement;
use NextCellent\RSS\Writer\Media\MediaGroup;

/**
 * Class Item
 * @package Suin\RSSWriter
 */
class Item extends SimpleXMLElement
{
    /** @var MediaGroup[]|MediaContent[] */
    protected $media = [];
    
    public function __construct()
    {
        parent::__construct('item');
    }

    /**
     * Set item title
     * @param string $title
     * @return $this
     */
    public function title($title)
    {
        $this->simpleChild('title', $title, true);
        return $this;
    }

	/**
	 * Set item URL
	 * @param string $link
	 * @return $this
	 */
    public function link($link)
    {
        $this->simpleChild('link', $link);
        return $this;
    }

	/**
	 * Set item description
	 * @param string $description
	 * @return $this
	 */
    public function description($description)
    {
        $this->simpleChild('description', $description, true);
        return $this;
    }

    /**
     * The author of the story's e-mail address.
     * 
     * @param string $author
     *
     * @return $this
     */
    public function author($author)
    {
        $this->simpleChild('author', $author);
        return $this;
    }

    /**
     * Set GUID
     *
     * @param string $guid
     *
     * @return $this
     */
    public function guid($guid)
    {
        $this->simpleChild('guid', $guid);
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
     * It has three required attributes.
     * 
     * @param string $url Where the enclosure is located. Must be an HTTP url.
     * @param string $length How big it is in bytes.
     * @param string $type What its type is, a standard MIME type.
     *
     * @return $this
     */
    public function enclosure($url, $length = null, $type = null)
    {
        $e = $this->simpleChild('enclosure');

        $e->attributes['url'] = $url;

        if($length !== null) {
            $e->attributes['length'] = $length;
        }

        if($type !== null) {
            $e->attributes['type'] = $type;
        }

        return $this;
    }

	/**
	 * Append item to the channel
	 * @param Channel $channel
	 * @return $this
	 */
    public function appendTo(Channel $channel)
    {
        $channel->addChild($this);
        return $this;
    }
}
