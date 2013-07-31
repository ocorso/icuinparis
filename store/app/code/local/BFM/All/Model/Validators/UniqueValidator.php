<?php
/**
 * BFM_All_Model_Validators_UniqueValidator validates that the attribute value is unique in the corresponding database table.
 */
class BFM_All_Model_Validators_UniqueValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var boolean whether the comparison is case sensitive. Defaults to true.
     * Note, by setting it to false, you are assuming the attribute type is string.
     */
    public $caseSensitive = true;
    /**
     * @var boolean whether the attribute value can be null or empty. Defaults to true,
     * meaning that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty = true;
    /**
     * @var string the Magento Model class name that should be used to
     * look for the attribute value being validated. Defaults to null, meaning using
     * the class of the object currently being validated.
     * You may use path alias to reference a class name here.
     * @see attributeName
     */
    public $className;
    /**
     * @var string the Magento Model class attribute name that should be
     * used to look for the attribute value being validated. Defaults to null,
     * meaning using the name of the attribute being validated.
     * @see className
     */
    public $attributeName;
    /**
     * @var string the user-defined error message. The placeholders "{attribute}" and "{value}"
     * are recognized, which will be replaced with the actual attribute name and value, respectively.
     */
    public $message;
    /**
     * @var boolean whether this validation rule should be skipped if when there is already a validation
     * error for the current attribute. Defaults to true.
     */
    public $skipOnError = true;

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param BFM_All_Model_Model $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        return;
    }
}

