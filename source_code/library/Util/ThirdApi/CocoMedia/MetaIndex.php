<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * MetaIndex message
 */
class MetaIndex extends \ProtobufMessage
{
    /* Field index constants */
    const TOTAL_NUM = 1;
    const CURRENT_INDEX = 2;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::TOTAL_NUM => array(
            'name' => 'total_num',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::CURRENT_INDEX => array(
            'name' => 'current_index',
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
        $this->values[self::TOTAL_NUM] = null;
        $this->values[self::CURRENT_INDEX] = null;
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
     * Sets value of 'total_num' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setTotalNum($value)
    {
        return $this->set(self::TOTAL_NUM, $value);
    }

    /**
     * Returns value of 'total_num' property
     *
     * @return integer
     */
    public function getTotalNum()
    {
        $value = $this->get(self::TOTAL_NUM);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'current_index' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setCurrentIndex($value)
    {
        return $this->set(self::CURRENT_INDEX, $value);
    }

    /**
     * Returns value of 'current_index' property
     *
     * @return integer
     */
    public function getCurrentIndex()
    {
        $value = $this->get(self::CURRENT_INDEX);
        return $value === null ? (integer)$value : $value;
    }
}
}