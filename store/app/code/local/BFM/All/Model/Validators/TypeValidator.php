<?php
/**
 * TypeValidator verifies if the attribute is of the type specified by {@link type}.
 *
 * The following data types are supported:
 * <ul>
 * <li><b>integer</b> A 32-bit signed integer data type.</li>
 * <li><b>float</b> A double-precision floating point number data type.</li>
 * <li><b>string</b> A string data type.</li>
 * <li><b>array</b> An array value. </li>
 * <li><b>date</b> A date data type.</li>
 * <li><b>time</b> A time data type.</li>
 * <li><b>datetime</b> A date and time data type.</li>
 * </ul>
 *
 * For <b>date</b> type, the property {@link dateFormat}
 * will be used to determine how to parse the date string. If the given date
 * value doesn't follow the format, the attribute is considered as invalid.
 */
class BFM_All_Model_Validators_TypeValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var string the data type that the attribute should be. Defaults to 'string'.
     * Valid values include 'string', 'integer', 'float', 'array', 'date', 'time' and 'datetime'.
     */
    public $type = 'string';
    /**
     * @var string the format pattern that the date value should follow. Defaults to 'MM/dd/yyyy'.
     * This property is effective only when {@link type} is 'date'.
     */
    public $dateFormat = 'MM/dd/yyyy';
    /**
     * @var string the format pattern that the time value should follow. Defaults to 'hh:mm'.
     * This property is effective only when {@link type} is 'time'.
     */
    public $timeFormat = 'hh:mm';
    /**
     * @var string the format pattern that the datetime value should follow. Defaults to 'MM/dd/yyyy hh:mm'.
     * This property is effective only when {@link type} is 'datetime'.
     */
    public $datetimeFormat = 'MM/dd/yyyy hh:mm';
    /**
     * @var boolean whether the attribute value can be null or empty. Defaults to true,
     * meaning that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty = true;

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param BFM_All_Model_Model $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->allowEmpty && $this->isEmpty($value))
            return;

        if ($this->type === 'integer')
            $valid = preg_match('/^[-+]?[0-9]+$/', trim($value));
        else if ($this->type === 'float')
            $valid = preg_match('/^[-+]?([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?$/', trim($value));
        else if ($this->type === 'date')
            $valid = false; // @todo сделать через хелпер для работы с датой. Хэлпера в Magento нет, есть модель Mage_Core_Model_Date
        else if ($this->type === 'time')
            $valid = false; // @todo сделать через хелпер для работы с датой
        else if ($this->type === 'datetime')
            $valid = false; // @todo сделать через хелпер для работы с датой
        else if ($this->type === 'array')
            $valid = is_array($value);
        else
            return;

        if (!$valid) {
            $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be `%s`.', array($attribute, $this->type));
            $this->addError($object, $attribute, $message);
        }
    }
}

