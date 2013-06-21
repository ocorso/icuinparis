<?php
/**
 * BFM_All_Model_Validators_NumberValidator validates that the attribute value is a number.
 */
class BFM_All_Model_Validators_NumberValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var boolean whether the attribute value can only be an integer. Defaults to false.
     */
    public $integerOnly = false;
    /**
     * @var boolean whether the attribute value can be null or empty. Defaults to true,
     * meaning that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty = true;
    /**
     * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
     */
    public $max;
    /**
     * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
     */
    public $min;
    /**
     * @var string user-defined error message used when the value is too big.
     */
    public $tooBig;
    /**
     * @var string user-defined error message used when the value is too small.
     */
    public $tooSmall;

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
        if ($this->integerOnly) {
            if (!preg_match('/^\s*[+-]?\d+\s*$/', "$value")) {
                $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be an integer.', $attribute);
                $this->addError($object, $attribute, $message);
            }
        }
        else
        {
            if (!preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/', "$value")) {
                $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` must be a number.', $attribute);
                $this->addError($object, $attribute, $message);
            }
        }
        if ($this->min !== null && $value < $this->min) {
            $message = $this->tooSmall !== null ? $this->tooSmall : Mage::helper('bfmall')->__('`%s` is too small (minimum is {min}).', array($attribute, $this->min));
            $this->addError($object, $attribute, $message);
        }
        if ($this->max !== null && $value > $this->max) {
            $message = $this->tooBig !== null ? $this->tooBig : Mage::helper('bfmall')->__('`%s` is too big (maximum is {max}).', array($attribute, $this->min));
            $this->addError($object, $attribute, $message);
        }
    }
}
