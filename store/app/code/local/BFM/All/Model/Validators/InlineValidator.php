<?php
/**
 * BFM_All_Model_Validators_InlineValidator represents a validator which is defined as a method in the object being validated.
 */
class BFM_All_Model_Validators_InlineValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var string the name of the validation method defined in the active record class
     */
    public $method;
    /**
     * @var array additional parameters that are passed to the validation method
     */
    public $params;

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param BFM_All_Model_Model $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        $method = $this->method;
        $object->$method($attribute, $this->params);
    }
}
