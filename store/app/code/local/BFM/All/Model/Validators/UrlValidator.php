<?php
/**
 * UrlValidator validates that the attribute value is a valid http or https URL.
 */
class BFM_All_Model_Validators_UrlValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var string the regular expression used to validates the attribute value.
     */
    public $pattern = '/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
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
        if (!$this->validateValue($value)) {
            $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__('`%s` is not a valid URL.', $attribute);
            $this->addError($object, $attribute, $message);
        }
    }

    /**
     * Validates a static value to see if it is a valid URL.
     * Note that this method does not respect {@link allowEmpty} property.
     * This method is provided so that you can call it directly without going through the model validation rule mechanism.
     * @param mixed $value the value to be validated
     * @return boolean whether the value is a valid URL
     */
    public function validateValue($value)
    {
        return is_string($value) && preg_match($this->pattern, $value);
    }
}

