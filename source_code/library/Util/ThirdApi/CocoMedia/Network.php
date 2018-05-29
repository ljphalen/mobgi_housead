<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * Network message
 */
class Network extends \ProtobufMessage
{
    /* Field index constants */
    const IPV4 = 1;
    const CONNECTION_TYPE = 2;
    const OPERATOR_TYPE = 3;
    const CELLULAR_ID = 4;
    const WIFI_APS = 5;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::IPV4 => array(
            'name' => 'ipv4',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::CONNECTION_TYPE => array(
            'name' => 'connection_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::OPERATOR_TYPE => array(
            'name' => 'operator_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::CELLULAR_ID => array(
            'name' => 'cellular_id',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::WIFI_APS => array(
            'name' => 'wifi_aps',
            'repeated' => true,
            'type' => '\CocoMedia\WiFiAp'
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
        $this->values[self::IPV4] = null;
        $this->values[self::CONNECTION_TYPE] = null;
        $this->values[self::OPERATOR_TYPE] = null;
        $this->values[self::CELLULAR_ID] = null;
        $this->values[self::WIFI_APS] = array();
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
     * Sets value of 'ipv4' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setIpv4($value)
    {
        return $this->set(self::IPV4, $value);
    }

    /**
     * Returns value of 'ipv4' property
     *
     * @return string
     */
    public function getIpv4()
    {
        $value = $this->get(self::IPV4);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'connection_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setConnectionType($value)
    {
        return $this->set(self::CONNECTION_TYPE, $value);
    }

    /**
     * Returns value of 'connection_type' property
     *
     * @return integer
     */
    public function getConnectionType()
    {
        $value = $this->get(self::CONNECTION_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'operator_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setOperatorType($value)
    {
        return $this->set(self::OPERATOR_TYPE, $value);
    }

    /**
     * Returns value of 'operator_type' property
     *
     * @return integer
     */
    public function getOperatorType()
    {
        $value = $this->get(self::OPERATOR_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'cellular_id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setCellularId($value)
    {
        return $this->set(self::CELLULAR_ID, $value);
    }

    /**
     * Returns value of 'cellular_id' property
     *
     * @return string
     */
    public function getCellularId()
    {
        $value = $this->get(self::CELLULAR_ID);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Appends value to 'wifi_aps' list
     *
     * @param \CocoMedia\WiFiAp $value Value to append
     *
     * @return null
     */
    public function appendWifiAps(\CocoMedia\WiFiAp $value)
    {
        return $this->append(self::WIFI_APS, $value);
    }

    /**
     * Clears 'wifi_aps' list
     *
     * @return null
     */
    public function clearWifiAps()
    {
        return $this->clear(self::WIFI_APS);
    }

    /**
     * Returns 'wifi_aps' list
     *
     * @return \CocoMedia\WiFiAp[]
     */
    public function getWifiAps()
    {
        return $this->get(self::WIFI_APS);
    }

    /**
     * Returns 'wifi_aps' iterator
     *
     * @return \ArrayIterator
     */
    public function getWifiApsIterator()
    {
        return new \ArrayIterator($this->get(self::WIFI_APS));
    }

    /**
     * Returns element from 'wifi_aps' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return \CocoMedia\WiFiAp
     */
    public function getWifiApsAt($offset)
    {
        return $this->get(self::WIFI_APS, $offset);
    }

    /**
     * Returns count of 'wifi_aps' list
     *
     * @return int
     */
    public function getWifiApsCount()
    {
        return $this->count(self::WIFI_APS);
    }
}
}