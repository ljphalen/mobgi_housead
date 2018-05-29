<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * Ad message
 */
class Ad extends \ProtobufMessage
{
    /* Field index constants */
    const ADSLOT_ID = 1;
    const AD_KEY = 4;
    const META_GROUP = 6;
    const HTML_SNIPPET = 2;
    const AD_TRACKING = 5;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::ADSLOT_ID => array(
            'name' => 'adslot_id',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::AD_KEY => array(
            'name' => 'ad_key',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::META_GROUP => array(
            'name' => 'meta_group',
            'repeated' => true,
            'type' => '\CocoMedia\MaterialMeta'
        ),
        self::HTML_SNIPPET => array(
            'name' => 'html_snippet',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::AD_TRACKING => array(
            'name' => 'ad_tracking',
            'repeated' => true,
            'type' => '\CocoMedia\Tracking'
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::ADSLOT_ID] = null;
        $this->values[self::AD_KEY] = null;
        $this->values[self::META_GROUP] = array();
        $this->values[self::HTML_SNIPPET] = null;
        $this->values[self::AD_TRACKING] = array();
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'adslot_id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setAdslotId($value)
    {
        return $this->set(self::ADSLOT_ID, $value);
    }

    /**
     * Returns value of 'adslot_id' property
     *
     * @return string
     */
    public function getAdslotId()
    {
        $value = $this->get(self::ADSLOT_ID);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'ad_key' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setAdKey($value)
    {
        return $this->set(self::AD_KEY, $value);
    }

    /**
     * Returns value of 'ad_key' property
     *
     * @return string
     */
    public function getAdKey()
    {
        $value = $this->get(self::AD_KEY);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Appends value to 'meta_group' list
     *
     * @param \CocoMedia\MaterialMeta $value Value to append
     *
     * @return null
     */
    public function appendMetaGroup(\CocoMedia\MaterialMeta $value)
    {
        return $this->append(self::META_GROUP, $value);
    }

    /**
     * Clears 'meta_group' list
     *
     * @return null
     */
    public function clearMetaGroup()
    {
        return $this->clear(self::META_GROUP);
    }

    /**
     * Returns 'meta_group' list
     *
     * @return \CocoMedia\MaterialMeta[]
     */
    public function getMetaGroup()
    {
        return $this->get(self::META_GROUP);
    }

    /**
     * Returns 'meta_group' iterator
     *
     * @return \ArrayIterator
     */
    public function getMetaGroupIterator()
    {
        return new \ArrayIterator($this->get(self::META_GROUP));
    }

    /**
     * Returns element from 'meta_group' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return \CocoMedia\MaterialMeta
     */
    public function getMetaGroupAt($offset)
    {
        return $this->get(self::META_GROUP, $offset);
    }

    /**
     * Returns count of 'meta_group' list
     *
     * @return int
     */
    public function getMetaGroupCount()
    {
        return $this->count(self::META_GROUP);
    }

    /**
     * Sets value of 'html_snippet' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setHtmlSnippet($value)
    {
        return $this->set(self::HTML_SNIPPET, $value);
    }

    /**
     * Returns value of 'html_snippet' property
     *
     * @return string
     */
    public function getHtmlSnippet()
    {
        $value = $this->get(self::HTML_SNIPPET);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Appends value to 'ad_tracking' list
     *
     * @param \CocoMedia\Tracking $value Value to append
     *
     * @return null
     */
    public function appendAdTracking(\CocoMedia\Tracking $value)
    {
        return $this->append(self::AD_TRACKING, $value);
    }

    /**
     * Clears 'ad_tracking' list
     *
     * @return null
     */
    public function clearAdTracking()
    {
        return $this->clear(self::AD_TRACKING);
    }

    /**
     * Returns 'ad_tracking' list
     *
     * @return \CocoMedia\Tracking[]
     */
    public function getAdTracking()
    {
        return $this->get(self::AD_TRACKING);
    }

    /**
     * Returns 'ad_tracking' iterator
     *
     * @return \ArrayIterator
     */
    public function getAdTrackingIterator()
    {
        return new \ArrayIterator($this->get(self::AD_TRACKING));
    }

    /**
     * Returns element from 'ad_tracking' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return \CocoMedia\Tracking
     */
    public function getAdTrackingAt($offset)
    {
        return $this->get(self::AD_TRACKING, $offset);
    }

    /**
     * Returns count of 'ad_tracking' list
     *
     * @return int
     */
    public function getAdTrackingCount()
    {
        return $this->count(self::AD_TRACKING);
    }
}
}