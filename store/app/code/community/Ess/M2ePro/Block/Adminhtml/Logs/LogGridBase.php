<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Logs_LogGridBase extends Mage_Adminhtml_Block_Widget_Grid
{
    //####################################

    protected function _getLogTypeList()
    {
        return array(
            Ess_M2ePro_Model_LogsBase::TYPE_NOTICE => Mage::helper('M2ePro')->__('Notice'),
            Ess_M2ePro_Model_LogsBase::TYPE_SUCCESS => Mage::helper('M2ePro')->__('Success'),
            Ess_M2ePro_Model_LogsBase::TYPE_WARNING => Mage::helper('M2ePro')->__('Warning'),
            Ess_M2ePro_Model_LogsBase::TYPE_ERROR => Mage::helper('M2ePro')->__('Error')
        );
    }

    protected function _getLogPriorityList()
    {
        return array(
            Ess_M2ePro_Model_LogsBase::PRIORITY_HIGH => Mage::helper('M2ePro')->__('High'),
            Ess_M2ePro_Model_LogsBase::PRIORITY_MEDIUM => Mage::helper('M2ePro')->__('Medium'),
            Ess_M2ePro_Model_LogsBase::PRIORITY_LOW => Mage::helper('M2ePro')->__('Low')
        );
    }

    //####################################

    public function callbackColumnType($value, $row, $column, $isExport)
    {
         switch ($row->getData('type')) {

            case Ess_M2ePro_Model_LogsBase::TYPE_NOTICE:
                break;

            case Ess_M2ePro_Model_LogsBase::TYPE_SUCCESS:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_LogsBase::TYPE_WARNING:
                $value = '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_LogsBase::TYPE_ERROR:
                 $value = '<span style="color: red; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnPriority($value, $row, $column, $isExport)
    {
         switch ($row->getData('priority')) {

            case Ess_M2ePro_Model_LogsBase::PRIORITY_HIGH:
                $value = '<span style="font-weight: bold;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_LogsBase::PRIORITY_MEDIUM:
                $value = '<span style="font-style: italic;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_LogsBase::PRIORITY_LOW:
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackDescription($value, $row, $column, $isExport)
    {
        $value = Mage::getModel('M2ePro/LogsBase')->decodeDescription($value);
        return htmlspecialchars($value);
    }

    //####################################
}