<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * Tracking message
 */
class Tracking extends \ProtobufMessage
{
    /* Field index constants */
    const TRACKING_EVENT = 1;
    const TRACKING_URL = 2;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::TRACKING_EVENT => array(
            'name' => 'tracking_event',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::TRACKING_URL => array(
            'name' => 'tracking_url',
            'repeated' => true,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
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
        $this->values[self::TRACKING_EVENT] = null;
        $this->values[self::TRACKING_URL] = array();
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
     * Sets value of 'tracking_event' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setTrackingEvent($value)
    {
        return $this->set(self::TRACKING_EVENT, $value);
    }

    /**
     * Returns value of 'tracking_event' property
     *
     * @return integer
     */
    public function getTrackingEvent()
    {
        $value = $this->get(self::TRACKING_EVENT);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Appends value to 'tracking_url' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendTrackingUrl($value)
    {
        return $this->append(self::TRACKING_URL, $value);
    }

    /**
     * Clears 'tracking_url' list
     *
     * @return null
     */
    public function clearTrackingUrl()
    {
        return $this->clear(self::TRACKING_URL);
    }

    /**
     * Returns 'tracking_url' list
     *
     * @return string[]
     */
    public function getTrackingUrl()
    {
        return $this->get(self::TRACKING_URL);
    }

    /**
     * Returns 'tracking_url' iterator
     *
     * @return \ArrayIterator
     */
    public function getTrackingUrlIterator()
    {
        return new \ArrayIterator($this->get(self::TRACKING_URL));
    }

    /**
     * Returns element from 'tracking_url' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getTrackingUrlAt($offset)
    {
        return $this->get(self::TRACKING_URL, $offset);
    }

    /**
     * Returns count of 'tracking_url' list
     *
     * @return int
     */
    public function getTrackingUrlCount()
    {
        return $this->count(self::TRACKING_URL);
    }
}
}