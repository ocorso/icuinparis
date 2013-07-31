<?php
/**
 * CaptchaValidator compares the specified attribute value with reCaptcha value.
 */
class BFM_All_Model_Validators_CaptchaValidator extends BFM_All_Model_Validators_Validator
{
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
     * @var string error message
     */
    const ERROR_MSG = 'The CAPTCHA wasn\'t entered correctly.';

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

        $captchaIsValid = Mage::helper('usecaptcha/recaptcha')->captchaIsValid();
        if (!$captchaIsValid) {
            $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__(self::ERROR_MSG);
            $this->addError($object, $attribute, $message);
        }
    }
}
