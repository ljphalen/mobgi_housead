<?php
/**
 * Auto generated from foo.proto at 2018-03-23 11:38:38
 */

namespace {
/**
 * Foo message
 */
class Foo extends \ProtobufMessage
{
    /* Field index constants */
    const BAR = 1;
    const BAZ = 2;
    const SPAM = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::BAR => array(
            'name' => 'bar',
            'required' => true,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::BAZ => array(
            'name' => 'baz',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::SPAM => array(
            'name' => 'spam',
            'repeated' => true,
            'type' => \ProtobufMessage::PB_TYPE_FLOAT,
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
        $this->values[self::BAR] = null;
        $this->values[self::BAZ] = null;
        $this->values[self::SPAM] = array();
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
     * Sets value of 'bar' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setBar($value)
    {
        return $this->set(self::BAR, $value);
    }

    /**
     * Returns value of 'bar' property
     *
     * @return integer
     */
    public function getBar()
    {
        $value = $this->get(self::BAR);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'baz' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setBaz($value)
    {
        return $this->set(self::BAZ, $value);
    }

    /**
     * Returns value of 'baz' property
     *
     * @return string
     */
    public function getBaz()
    {
        $value = $this->get(self::BAZ);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Appends value to 'spam' list
     *
     * @param double $value Value to append
     *
     * @return null
     */
    public function appendSpam($value)
    {
        return $this->append(self::SPAM, $value);
    }

    /**
     * Clears 'spam' list
     *
     * @return null
     */
    public function clearSpam()
    {
        return $this->clear(self::SPAM);
    }

    /**
     * Returns 'spam' list
     *
     * @return double[]
     */
    public function getSpam()
    {
        return $this->get(self::SPAM);
    }

    /**
     * Returns 'spam' iterator
     *
     * @return \ArrayIterator
     */
    public function getSpamIterator()
    {
        return new \ArrayIterator($this->get(self::SPAM));
    }




	/**
     * Returns element from 'spam' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return double
     */
    public function getSpamAt($offset)
    {
        return $this->get(self::SPAM, $offset);
    }

    /**
     * Returns count of 'spam' list
     *
     * @return int
     */
    public function getSpamCount()
    {
        return $this->count(self::SPAM);
    }
}
}