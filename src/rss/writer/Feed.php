<?php

namespace NextCellent\RSS\Writer;

use DOMDocument;
use NextCellent\RSS\Writer\Channel\Channel;

/**
 * This class is the start point and is basically the RSS feed.
 *
 * This library implements version 2.0.11 of the RSS 2.0 specification,
 * published by the RSS Advisory Board on March 30, 2009.
 *
 * @see http://www.rssboard.org/rss-specification
 */
class Feed
{
    const NS_MEDIA = 'xmlns:media=http://search.yahoo.com/mrss/';

    /** @var Channel */
    protected $channel;

    /** @var string[] */
    protected $namespace;

    /**
     * Add the channel.
     * @param Channel $channel
     * @return $this
     */
    public function channel(Channel $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Add a namespace.
     *
     * @param string $namespace
     */
    public function addNamespace($namespace)
    {
        $elements = explode('=', $namespace);
        $this->namespace[$elements[0]] = $elements[1];
    }

    /**
     * Render XML
     * @return string
     */
    public function render()
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');

        $rss  = $xml->createElement('rss');
        $rss->setAttribute('version', '2.0');

        foreach ($this->namespace as $space => $url) {
            $rss->setAttribute($space, $url);
        }
        
        if($this->channel !== null) {
            $rss->appendChild($this->channel->asXML($xml));
        }

        $xml->appendChild($rss);
        
        $xml->formatOutput = true;
        
        return $xml->saveXML();
    }

    /**
     * Render XML
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
