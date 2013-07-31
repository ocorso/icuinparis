<?php
abstract class BFM_All_Model_Abstract extends Mage_Core_Model_Abstract implements BFM_All_Model_Interface, IteratorAggregate, ArrayAccess
{
    private $_errors = array(); // attribute name => array of errors
    private $_validators; // validators
    private $_scenario = ''; // scenario


    public function rules()
    {
        return array();
    }

    /**
     * Returns the scenario that this model is used in.
     *
     * Scenario affects how validation is performed and which attributes can
     * be massively assigned.
     *
     * A validation rule will be performed when calling {@link validate()}
     * if its 'on' option is not set or contains the current scenario value.
     *
     * And an attribute can be massively assigned if it is associated with
     * a validation rule for the current scenario.
     *
     * @return string the scenario that this model is in.
     */
    public function getScenario()
    {
        return $this->_scenario;
    }

    /**
     * Sets the scenario for the model.
     * @param string $value the scenario that this model is in.
     * @see getScenario
     */
    public function setScenario($value)
    {
        $this->_scenario = $value;
    }

    /**
     * Performs the validation.
     *
     * This method executes the validation rules as declared in {@link rules}.
     * Only the rules applicable to the current {@link scenario} will be executed.
     * A rule is considered applicable to a scenario if its 'on' option is not set
     * or contains the scenario.
     *
     * Errors found during the validation can be retrieved via {@link getErrors}.
     *
     * @param array $attributes list of attributes that should be validated. Defaults to null,
     * meaning any attribute listed in the applicable validation rules should be
     * validated. If this parameter is given as a list of attributes, only
     * the listed attributes will be validated.
     * @return boolean whether the validation is successful without any error.
     * @see beforeValidate
     * @see afterValidate
     */
    public function validate($attributes = null)
    {
        $this->clearErrors();
        if ($this->beforeValidate()) {
            foreach ($this->getValidators() as $validator)
                $validator->validate($this, $attributes);
            $this->afterValidate();
            return !$this->hasErrors();
        }
        else
            return false;
    }

    /**
     * This method is invoked before validation starts.
     * The default implementation returns true.
     * You may override this method to do preliminary checks before validation.
     * @return boolean whether validation should be executed. Defaults to true.
     * If false is returned, the validation will stop and the model is considered invalid.
     */
    protected function beforeValidate()
    {
        return true;
    }

    /**
     * This method is invoked after validation ends.
     * The default implementation provide nothing.
     * You may override this method to do postprocessing after validation.
     */
    protected function afterValidate()
    {

    }

    /**
     * Returns the applicable validators.
     * @param string $attribute the name of the attribute whose validators should be returned.
     * If this is null, the validators for ALL attributes in the model will be returned.
     * @return array the applicable validators.
     */
    public function getValidators($attribute = null)
    {
        if ($this->_validators === null)
            $this->_validators = $this->createValidators();

        $scenario = $this->getScenario();
        $validators = array();
        foreach ($this->_validators as $validator)
        {
            if ($validator->applyTo($scenario)) {
                if ($attribute === null || in_array($attribute, $validator->attributes, true))
                    $validators[] = $validator;
            }
        }
        return $validators;
    }

    /**
     * Creates validator objects based on the specification in {@link rules}.
     * This method is mainly used internally.
     * @return BFM_All_Objects_List validators built based on {@link rules()}.
     */
    public function createValidators()
    {
        $validators = new BFM_All_Objects_List();
        foreach ($this->rules() as $rule)
        {
            if (isset($rule[0], $rule[1])) // attributes, validator name
                $validators->add(BFM_All_Model_Validators_Validator::createValidator($rule[1], $this, $rule[0], array_slice($rule, 2)));
            else
                throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('%s has an invalid validation rule. The rule must specify attributes to be validated and the validator name.', array(get_class($this))));
        }
        return $validators;
    }

    /**
     * Returns a value indicating whether there is any validation error.
     * @param string $attribute attribute name. Use null to check all attributes.
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute = null)
    {
        if ($attribute === null)
            return $this->_errors !== array();
        else
            return isset($this->_errors[$attribute]);
    }

    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null)
            return $this->_errors;
        else
            return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : array();
    }

    /**
     * Returns the errors for all attributes.
     * @return array errors for all attributes. Empty array is returned if no error.
     */
    public function getErrorsList()
    {
        $errors = array();
        if (sizeof($this->_errors)) {
            foreach ($this->_errors as $field => $fieldErrors)
            {
                if (!sizeof($fieldErrors))
                    continue;
                foreach ($fieldErrors as $error)
                {
                    $errors[] = $error;
                }
            }
        }
        return $errors;
    }

    /**
     * Returns the first error of the specified attribute.
     * @param string $attribute attribute name.
     * @return string the error message. Null is returned if no error.
     */
    public function getError($attribute)
    {
        return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
    }

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addError($attribute, $error)
    {
        $this->_errors[$attribute][] = $error;
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null)
            $this->_errors = array();
        else
            unset($this->_errors[$attribute]);
    }

    /**
     * Returns an iterator for traversing the attributes in the model.
     * This method is required by the interface IteratorAggregate.
     * @return BFM_All_Objects_MapIterator an iterator for traversing the items in the list.
     */
    public function getIterator()
    {
        $attributes = $this->getAttributes();
        return new BFM_All_Objects_MapIterator($attributes);
    }

    /**
     * Returns whether there is an element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * Returns the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to retrieve element.
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Sets the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to set element
     * @param mixed $item the element value
     */
    public function offsetSet($offset, $item)
    {
        $this->$offset = $item;
    }

    /**
     * Unsets the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}