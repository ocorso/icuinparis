<?php
class BFM_All_Objects_Component
{
    private $_e;
    private $_m;

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter();
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            $name = strtolower($name);
            if (!isset($this->_e[$name]))
                $this->_e[$name] = new BFM_All_Objects_List();
            return $this->_e[$name];
        }
        else if (isset($this->_m[$name]))
            return $this->_m[$name];
        else if (is_array($this->_m)) {
            foreach ($this->_m as $object)
            {
                if ($object->getEnabled() && (property_exists($object, $name) || $object->canGetProperty($name)))
                    return $object->$name;
            }
        }
        throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('Property "%s.%s" is not defined.', get_class($this), $name));
    }

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            return $this->$setter($value);
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            // duplicating getEventHandlers() here for performance
            $name = strtolower($name);
            if (!isset($this->_e[$name]))
                $this->_e[$name] = new BFM_All_Objects_List();
            return $this->_e[$name]->add($value);
        }
        else if (is_array($this->_m)) {
            foreach ($this->_m as $object)
            {
                if ($object->getEnabled() && (property_exists($object, $name) || $object->canSetProperty($name)))
                    return $object->$name = $value;
            }
        }
        if (method_exists($this, 'get' . $name))
            throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('Property "%s.%s" is read only.', get_class($this), $name));
        else
            throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('Property "%s.%s" is not defined.', get_class($this), $name));
    }

    /**
     * Checks if a property value is null.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using isset() to detect if a component property is set or not.
     * @param string $name the property name or the event name
     * @since 1.0.1
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter() !== null;
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            $name = strtolower($name);
            return isset($this->_e[$name]) && $this->_e[$name]->getCount();
        }
        else if (is_array($this->_m)) {
            if (isset($this->_m[$name]))
                return true;
            foreach ($this->_m as $object)
            {
                if ($object->getEnabled() && (property_exists($object, $name) || $object->canGetProperty($name)))
                    return true;
            }
        }
        return false;
    }

    /**
     * Sets a component property to be null.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using unset() to set a component property to be null.
     * @param string $name the property name or the event name
     * @throws BFM_All_Objects_Exception if the property is read only.
     * @since 1.0.1
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            $this->$setter(null);
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name))
            unset($this->_e[strtolower($name)]);
        else if (is_array($this->_m)) {
            if (isset($this->_m[$name]))
                $this->detachBehavior($name);
            else
            {
                foreach ($this->_m as $object)
                {
                    if ($object->getEnabled()) {
                        if (property_exists($object, $name))
                            return $object->$name = null;
                        else if ($object->canSetProperty($name))
                            return $object->$setter(null);
                    }
                }
            }
        }
        else if (method_exists($this, 'get' . $name))
            throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('Property "%s.%s" is read only.', get_class($this), $name));
    }

    /**
     * Calls the named method which is not a class method.
     * Do not call this method. This is a PHP magic method that we override
     * to implement the behavior feature.
     * @param string $name the method name
     * @param array $parameters method parameters
     * @return mixed the method return value
     * @since 1.0.2
     */
    public function __call($name, $parameters)
    {
        if ($this->_m !== null) {
            foreach ($this->_m as $object)
            {
                if ($object->getEnabled() && method_exists($object, $name))
                    return call_user_func_array(array($object, $name), $parameters);
            }
        }
        if (class_exists('Closure', false) && $this->canGetProperty($name) && $this->$name instanceof Closure)
            return call_user_func_array($this->$name, $parameters);
        throw new BFM_All_Objects_Exception(Mage::helper('bfmall')->__('%s does not have a method named "%s".', get_class($this), $name));
    }

    /**
     * Determines whether a property is defined.
     * A property is defined if there is a getter or setter method
     * defined in the class. Note, property names are case-insensitive.
     * @param string $name the property name
     * @return boolean whether the property is defined
     * @see canGetProperty
     * @see canSetProperty
     */
    public function hasProperty($name)
    {
        return method_exists($this, 'get' . $name) || method_exists($this, 'set' . $name);
    }

    /**
     * Determines whether a property can be read.
     * A property can be read if the class has a getter method
     * for the property name. Note, property name is case-insensitive.
     * @param string $name the property name
     * @return boolean whether the property can be read
     * @see canSetProperty
     */
    public function canGetProperty($name)
    {
        return method_exists($this, 'get' . $name);
    }

    /**
     * Determines whether a property can be set.
     * A property can be written if the class has a setter method
     * for the property name. Note, property name is case-insensitive.
     * @param string $name the property name
     * @return boolean whether the property can be written
     * @see canGetProperty
     */
    public function canSetProperty($name)
    {
        return method_exists($this, 'set' . $name);
    }

    /**
     * Evaluates a PHP expression or callback under the context of this component.
     *
     * Valid PHP callback can be class method name in the form of
     * array(ClassName/Object, MethodName), or anonymous function (only available in PHP 5.3.0 or above).
     *
     * If a PHP callback is used, the corresponding function/method signature should be
     * <pre>
     * function foo($param1, $param2, ..., $component) { ... }
     * </pre>
     * where the array elements in the second parameter to this method will be passed
     * to the callback as $param1, $param2, ...; and the last parameter will be the component itself.
     *
     * If a PHP expression is used, the second parameter will be "extracted" into PHP variables
     * that can be directly accessed in the expression. See {@link http://us.php.net/manual/en/function.extract.php PHP extract}
     * for more details. In the expression, the component object can be accessed using $this.
     *
     * @param mixed $_expression_ a PHP expression or PHP callback to be evaluated.
     * @param array $_data_ additional parameters to be passed to the above expression/callback.
     * @return mixed the expression result
     */
    public function evaluateExpression($_expression_, $_data_ = array())
    {
        if (is_string($_expression_)) {
            extract($_data_);
            return eval('return ' . $_expression_ . ';');
        }
        else
        {
            $_data_[] = $this;
            return call_user_func_array($_expression_, $_data_);
        }
    }
}