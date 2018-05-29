<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * App message
 */
class App extends \ProtobufMessage
{
    /* Field index constants */
    const APP_ID = 1;
    const CHANNEL_ID = 2;
    const APP_VERSION = 3;
    const APP_PACKAGE = 4;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::APP_ID => array(
            'default' => '',
            'name' => 'app_id',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::CHANNEL_ID => array(
            'name' => 'channel_id',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::APP_VERSION => array(
            'name' => 'app_version',
            'required' => false,
            'type' => '\CocoMedia\Version'
        ),
        self::APP_PACKAGE => array(
            'name' => 'app_package',
            'required' => false,
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
        $this->values[self::APP_ID] = self::$fields[self::APP_ID]['default'];
        $this->values[self::CHANNEL_ID] = null;
        $this->values[self::APP_VERSION] = null;
        $this->values[self::APP_PACKAGE] = null;
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
     * Sets value of 'app_id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setAppId($value)
    {
        return $this->set(self::APP_ID, $value);
    }

    /**
     * Returns value of 'app_id' property
     *
     * @return string
     */
    public function getAppId()
    {
        $value = $this->get(self::APP_ID);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'channel_id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setChannelId($value)
    {
        return $this->set(self::CHANNEL_ID, $value);
    }

    /**
     * Returns value of 'channel_id' property
     *
     * @return string
     */
    public function getChannelId()
    {
        $value = $this->get(self::CHANNEL_ID);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'app_version' property
     *
     * @param \CocoMedia\Version $value Property value
     *
     * @return null
     */
    public function setAppVersion(\CocoMedia\Version $value=null)
    {
        return $this->set(self::APP_VERSION, $value);
    }

    /**
     * Returns value of 'app_version' property
     *
     * @return \CocoMedia\Version
     */
    public function getAppVersion()
    {
        return $this->get(self::APP_VERSION);
    }

    /**
     * Sets value of 'app_package' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setAppPackage($value)
    {
        return $this->set(self::APP_PACKAGE, $value);
    }

    /**
     * Returns value of 'app_package' property
     *
     * @return string
     */
    public function getAppPackage()
    {
        $value = $this->get(self::APP_PACKAGE);
        return $value === null ? (string)$value : $value;
    }
}
}