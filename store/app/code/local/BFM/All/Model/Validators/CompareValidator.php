<?php
/**
 * CompareValidator compares the specified attribute value with another value and validates if they are equal.
 *
 * The value being compared with can be another attribute value
 * (specified via {@link compareAttribute}) or a constant (specified via
 * {@link compareValue}. When both are specified, the latter takes
 * precedence. If neither is specified, the attribute will be compared
 * with another attribute whose name is by appending "_repeat" to the source
 * attribute name.
 *
 * The comparison can be either {@link strict} or not.
 */
class BFM_All_Model_Validators_CompareValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var string the name of the attribute to be compared with
     */
    public $compareAttribute;
    /**
     * @var string the constant value to be compared with
     */
    public $compareValue;
    /**
     * @var boolean whether the comparison is strict (both value and type must be the same.)
     * Defaults to false.
     */
    public $strict = false;
    /**
     * @var boolean whether the attribute value can be null or empty. Defaults to false.
     * If this is true, it means the attribute is considered valid when it is empty.
     */
    public $allowEmpty = false;
    /**
     * @var string the operator for comparison. Defaults to '='.
     * The followings are valid operators:
     * <ul>
     * <li>'=' or '==': validates to see if the two values are equal. If {@link strict} is true, the comparison
     * will be done in strict mode (i.e. checking value type as well).</li>
     * <li>'!=': validates to see if the two values are NOT equal. If {@link strict} is true, the comparison
     * will be done in strict mode (i.e. checking value type as well).</li>
     * <li>'>': validates to see if the value being validated is greater than the value being compared with.</li>
     * <li>'>=': validates to see if the value being validated is greater than or equal to the value being compared with.</li>
     * <li>'<': validates to see if the value being validated is less than the value being compared with.</li>
     * <li>'<=': validates to see if the value being validated is less than or equal to the value being compared with.</li>
     * </ul>
     */
    public $operator = '=';

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
        if ($this->compareValue !== null)
            $compareTo = $compareValue = $this->compareValue;
        else
        {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue = $object->$compareAttribute;
            $compareTo = $object->getAttributeLabel($compareAttribute);
        }

        switch ($this->operator)
        {
            case '=' :
            case '==' :
                if (($this->strict && $value !== $compareValue) || (!$this->strict && $value != $compareValue)) {
                    $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be repeated exactly.', $compareTo);
                    $this->addError($object, $attribute, $message);
                }
                break;
            case '!=' :
                if (($this->strict && $value === $compareValue) || (!$this->strict && $value == $compareValue)) {
                    $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must not be equal to "%s".', array($compareTo, $compareValue));
                    $this->addError($object, $attribute, $message);
                }
                break;
            case '>' :
                if ($value <= $compareValue) {
                    $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be greater than "%s".', array($compareTo, $compareValue));
                    $this->addError($object, $attribute, $message);
                }
                break;
            case '>=' :
                if ($value < $compareValue) {
                    $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be greater than or equal to "%s".', array($compareTo, $compareValue));
                    $this->addError($object, $attribute, $message);
                }
                break;
            case '<' :
                if ($value >= $compareValue) {
                    $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be less than "%s".', array($compareTo, $compareValue));
                    $this->addError($object, $attribute, $message);
                }
                break;
            case '<=' :
                if ($value > $compareValue) {
                    $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be less than or equal to "%s".', array($compareTo, $compareValue));
                    $this->addError($object, $attribute, $message);
                }
                break;
            default :
                throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('Invalid operator "%s".', $this->operator));
        }
    }
}
