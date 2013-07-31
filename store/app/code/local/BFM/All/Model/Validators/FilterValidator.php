<?php
/**
 * BFM_All_Model_Validators_FilterValidator transforms the data being validated based on a filter.
 *
 * BFM_All_Model_Validators_FilterValidator is actually not a validator but a data processor.
 * It invokes the specified filter method to process the attribute value
 * and save the processed value back to the attribute. The filter method
 * must follow the following signature:
 * <pre>
 * function foo($value) {...return $newValue; }
 * </pre>
 * Many PHP functions qualify this signature (e.g. trim).
 *
 * To specify the filter method, set {@link filter} property to be the function name.
 */
class BFM_All_Model_Validators_FilterValidator extends BFM_All_Model_Validators_Validator
{
    /**
     * @var callback the filter method
     */
    public $filter;

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param BFM_All_Model_Model $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        if ($this->filter === null || !is_callable($this->filter))
            throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('The "filter" property must be specified with a valid callback.'));

        $object->setData($attribute, call_user_func_array($this->filter, array($object->getData($attribute))));
    }
}
