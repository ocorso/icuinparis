<?php
/**
 * BFM_All_Model_Validators_NumberValidator validates that the attribute value is a number.
 */
class BFM_All_Model_Validators_PhoneValidator extends BFM_All_Model_Validators_Validator
{
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
        if (!preg_match('/^[\d-()\s+,]+$/', "$value")) {
            $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('Invalid phone number. The example of valid phone number is (123) 456-7890', $attribute);
            $this->addError($object, $attribute, $message);
        }

    }
}
