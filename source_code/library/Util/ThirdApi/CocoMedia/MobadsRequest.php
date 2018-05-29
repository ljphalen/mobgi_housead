<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * MobadsRequest message
 */
class MobadsRequest extends \ProtobufMessage
{
    /* Field index constants */
    const REQUEST_ID = 1;
    const API_VERSION = 2;
    const ADSLOT = 7;
    const APP = 3;
    const DEVICE = 4;
    const NETWORK = 5;
    const GPS = 6;
    const IS_DEBUG = 8;
    const REQUEST_NUM = 15;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::REQUEST_ID => array(
            'name' => 'request_id',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::API_VERSION => array(
            'name' => 'api_version',
            'required' => false,
            'type' => '\CocoMedia\Version'
        ),
        self::ADSLOT => array(
            'name' => 'adslot',
            'required' => false,
            'type' => '\CocoMedia\AdSlot'
        ),
        self::APP => array(
            'name' => 'app',
            'required' => false,
            'type' => '\CocoMedia\App'
        ),
        self::DEVICE => array(
            'name' => 'device',
            'required' => false,
            'type' => '\CocoMedia\Device'
        ),
        self::NETWORK => array(
            'name' => 'network',
            'required' => false,
            'type' => '\CocoMedia\Network'
        ),
        self::GPS => array(
            'name' => 'gps',
            'required' => false,
            'type' => '\CocoMedia\Gps'
        ),
        self::IS_DEBUG => array(
            'default' => false,
            'name' => 'is_debug',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_BOOL,
        ),
        self::REQUEST_NUM => array(
            'name' => 'request_num',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
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
        $this->values[self::REQUEST_ID] = null;
        $this->values[self::API_VERSION] = null;
        $this->values[self::ADSLOT] = null;
        $this->values[self::APP] = null;
        $this->values[self::DEVICE] = null;
        $this->values[self::NETWORK] = null;
        $this->values[self::GPS] = null;
        $this->values[self::IS_DEBUG] = self::$fields[self::IS_DEBUG]['default'];
        $this->values[self::REQUEST_NUM] = null;
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
     * Sets value of 'request_id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setRequestId($value)
    {
        return $this->set(self::REQUEST_ID, $value);
    }

    /**
     * Returns value of 'request_id' property
     *
     * @return string
     */
    public function getRequestId()
    {
        $value = $this->get(self::REQUEST_ID);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'api_version' property
     *
     * @param \CocoMedia\Version $value Property value
     *
     * @return null
     */
    public function setApiVersion(\CocoMedia\Version $value=null)
    {
        return $this->set(self::API_VERSION, $value);
    }

    /**
     * Returns value of 'api_version' property
     *
     * @return \CocoMedia\Version
     */
    public function getApiVersion()
    {
        return $this->get(self::API_VERSION);
    }

    /**
     * Sets value of 'adslot' property
     *
     * @param \CocoMedia\AdSlot $value Property value
     *
     * @return null
     */
    public function setAdslot(\CocoMedia\AdSlot $value=null)
    {
        return $this->set(self::ADSLOT, $value);
    }

    /**
     * Returns value of 'adslot' property
     *
     * @return \CocoMedia\AdSlot
     */
    public function getAdslot()
    {
        return $this->get(self::ADSLOT);
    }

    /**
     * Sets value of 'app' property
     *
     * @param \CocoMedia\App $value Property value
     *
     * @return null
     */
    public function setApp(\CocoMedia\App $value=null)
    {
        return $this->set(self::APP, $value);
    }

    /**
     * Returns value of 'app' property
     *
     * @return \CocoMedia\App
     */
    public function getApp()
    {
        return $this->get(self::APP);
    }

    /**
     * Sets value of 'device' property
     *
     * @param \CocoMedia\Device $value Property value
     *
     * @return null
     */
    public function setDevice(\CocoMedia\Device $value=null)
    {
        return $this->set(self::DEVICE, $value);
    }

    /**
     * Returns value of 'device' property
     *
     * @return \CocoMedia\Device
     */
    public function getDevice()
    {
        return $this->get(self::DEVICE);
    }

    /**
     * Sets value of 'network' property
     *
     * @param \CocoMedia\Network $value Property value
     *
     * @return null
     */
    public function setNetwork(\CocoMedia\Network $value=null)
    {
        return $this->set(self::NETWORK, $value);
    }

    /**
     * Returns value of 'network' property
     *
     * @return \CocoMedia\Network
     */
    public function getNetwork()
    {
        return $this->get(self::NETWORK);
    }

    /**
     * Sets value of 'gps' property
     *
     * @param \CocoMedia\Gps $value Property value
     *
     * @return null
     */
    public function setGps(\CocoMedia\Gps $value=null)
    {
        return $this->set(self::GPS, $value);
    }

    /**
     * Returns value of 'gps' property
     *
     * @return \CocoMedia\Gps
     */
    public function getGps()
    {
        return $this->get(self::GPS);
    }

    /**
     * Sets value of 'is_debug' property
     *
     * @param boolean $value Property value
     *
     * @return null
     */
    public function setIsDebug($value)
    {
        return $this->set(self::IS_DEBUG, $value);
    }

    /**
     * Returns value of 'is_debug' property
     *
     * @return boolean
     */
    public function getIsDebug()
    {
        $value = $this->get(self::IS_DEBUG);
        return $value === null ? (boolean)$value : $value;
    }

    /**
     * Sets value of 'request_num' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setRequestNum($value)
    {
        return $this->set(self::REQUEST_NUM, $value);
    }

    /**
     * Returns value of 'request_num' property
     *
     * @return integer
     */
    public function getRequestNum()
    {
        $value = $this->get(self::REQUEST_NUM);
        return $value === null ? (integer)$value : $value;
    }
}
}