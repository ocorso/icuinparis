<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Controller_Adminhtml_CmdController extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    protected function getCommandsData()
    {
        $reflectionClass = new ReflectionClass ('Ess_M2ePro_Adminhtml_CmdController');
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        // Get actions methods
        //----------------------------------
        $actions = array();
        foreach ($reflectionMethods as $reflectionMethod) {

            $className = $reflectionClass->getMethod($reflectionMethod->name)
                                         ->getDeclaringClass()->name;
            $methodName = $reflectionMethod->name;

            if ($className != 'Ess_M2ePro_Adminhtml_CmdController') {
                continue;
            }
            if ($methodName == 'indexAction') {
                continue;
            }
            if (substr($methodName,strlen($methodName)-6) != 'Action') {
                continue;
            }

            $methodName = substr($methodName,0,strlen($methodName)-6);

            $actions[] = $methodName;
        }
        //----------------------------------

        // Print method actions
        //----------------------------------
        $methods = array();
        foreach ($actions as $action) {

            $reflectionMethod = new ReflectionMethod ('Ess_M2ePro_Adminhtml_CmdController',$action.'Action');
            $commentsString = $this->getMethodComments($reflectionMethod);

            preg_match('/@hidden/', $commentsString, $matchesHidden);

            if (isset($matchesHidden[0])) {
                continue;
            }

            preg_match('/@title[\s]*\"(.*)\"/', $commentsString, $matchesTitle);

            $methodTitle = '';
            if (isset($matchesTitle[1])) {
                $methodTitle = $matchesTitle[1];
            } else {
                $methodTitle = $action;
            }

            preg_match('/@description[\s]*\"(.*)\"/', $commentsString, $matchesDescription);

            $methodDescription = '';
            if (isset($matchesDescription[1])) {
                $methodDescription = $matchesDescription[1];
            }

            $methodNumber = count($methods)+1;
            $methodUrl = $this->getUrl('*/*/'.$action);

            $methodContent = '';
            $fileContent = file($reflectionMethod->getFileName());
            for($i=$reflectionMethod->getStartLine()+2;$i<$reflectionMethod->getEndLine();$i++) {
                $methodContent .= $fileContent[$i-1];
            }

            preg_match('/@new_line/', $commentsString, $matchesNewLine);
            $methodNewLine = isset($matchesNewLine[0]) ? true : false;

            preg_match('/@confirm[\s]*\"(.*)\"/', $commentsString, $matchesConfirm);
            $methodConfirm = '';
            if (isset($matchesConfirm[1])) {
                $methodConfirm = $matchesConfirm[1];
            }

            $methods[] = array(
                'number' => $methodNumber,
                'title' => $methodTitle,
                'description' => $methodDescription,
                'url' => $methodUrl,
                'content' => $methodContent,
                'new_line' => $methodNewLine,
                'confirm' => $methodConfirm
            );
        }
        //----------------------------------

        return $methods;
    }

    private function getMethodComments(ReflectionMethod $reflectionMethod)
    {
        $contentPhpFile = file_get_contents($reflectionMethod->getFileName());
        $contentPhpFile = explode(chr(10),$contentPhpFile);

        $commentsArray = array();
        for ($i=$reflectionMethod->getStartLine()-2;$i>0;$i--) {
            $contentPhpFile[$i] = trim($contentPhpFile[$i]);
            $commentsArray[] = $contentPhpFile[$i];
            if ($contentPhpFile[$i] == '/**') {
                break;
            }
        }
        
        $commentsArray = array_reverse($commentsArray);
        $commentsString = implode(chr(10),$commentsArray);

        return $commentsString;
    }

    //#############################################

    protected function printBack()
    {
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_cmd_backButton');
        echo $block->toHtml();
    }

    protected function printCommandsList()
    {
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_cmd_commandsList');
        $block->setData(array('commands'=>$this->getCommandsData()));
        echo $block->toHtml();
    }

    //#############################################
}