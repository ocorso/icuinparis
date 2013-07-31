<?php
/**
 * BFM_All_Model_Validators_EmailValidator validates that the attribute value is a valid email address.
 */
class BFM_All_Model_Validators_EmailValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var string the regular expression used to validate the attribute value.
     * @see http://www.regular-expressions.info/email.html
     */
    public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
    /**
     * @var string the regular expression used to validate email addresses with the name part.
     * This property is used only when {@link allowName} is true.
     * @see allowName
     */
    public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
    /**
     * @var boolean whether to allow name in the email address (e.g. "Qiang Xue <qiang.xue@gmail.com>"). Defaults to false.
     * @see fullPattern
     */
    public $allowName = false;
    /**
     * @var boolean whether to check the MX record for the email address.
     * Defaults to false. To enable it, you need to make sure the PHP function 'checkdnsrr'
     * exists in your PHP installation.
     */
    public $checkMX = false;
    /**
     * @var boolean whether to check port 25 for the email address.
     * Defaults to false.
     */
    public $checkPort = false;
    /**
     * @var boolean whether the attribute value can be null or empty. Defaults to true,
     * meaning that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty = true;

    /**
     * @var string error message
     */
    const ERROR_MSG = '`%s` is not a valid email address.';

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
            $message = $this->message !== null ? $this->message : Mage::helper('bfmall')->__(self::ERROR_MSG, $attribute);
            $this->addError($object, $attribute, $message);
        }
    }

    /**
     * Validates a static value to see if it is a valid email.
     * Note that this method does not respect {@link allowEmpty} property.
     * This method is provided so that you can call it directly without going through the model validation rule mechanism.
     * @param mixed $value the value to be validated
     * @return boolean whether the value is a valid email
     */
    public function validateValue($value)
    {
        $valid = is_string($value) && (preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value));
        if ($valid)
            $domain = rtrim(substr($value, strpos($value, '@') + 1), '>');
        if ($valid && $this->checkMX && function_exists('checkdnsrr'))
            $valid = checkdnsrr($domain, 'MX');
        if ($valid && $this->checkPort && function_exists('fsockopen'))
            $valid = fsockopen($domain, 25) !== false;
        return $valid;
    }
}
