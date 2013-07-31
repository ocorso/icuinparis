<?php
/**
 * BFM_All_Model_Validators_RequiredValidator validates that the specified attribute does not have null or empty value.
 */
class BFM_All_Model_Validators_RequiredValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var mixed the desired value that the attribute must have.
     * If this is null, the validator will validate that the specified attribute does not have null or empty value.
     * If this is set as a value that is not null, the validator will validate that
     * the attribute has a value that is the same as this property value.
     * Defaults to null.
     */
    public $requiredValue;
    /**
     * @var boolean whether the comparison to {@link requiredValue} is strict.
     * When this is true, the attribute value and type must both match those of {@link requiredValue}.
     * Defaults to false, meaning only the value needs to be matched.
     * This property is only used when {@link requiredValue} is not null.
     */
    public $strict = false;

    /**
     * @var string error messages
     */
    const ERROR_MSG = '`%s` must be `%s`.';
    const ERROR_MSG_BLANK = '`%s` cannot be blank.';

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param BFM_All_Model_Model $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->requiredValue !== null) {
            if (!$this->strict && $value != $this->requiredValue || $this->strict && $value !== $this->requiredValue) {
                $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__(self::ERROR_MSG, array($attribute, $this->requiredValue));
                $this->addError($object, $attribute, $message);
            }
        }
        else if ($this->isEmpty($value, true)) {
            $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__(self::ERROR_MSG_BLANK, $attribute);
            $this->addError($object, $attribute, $message);
        }
    }
}
