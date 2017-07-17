<?php

/**
 * Flash Messenger View Helper
 */

class Zend_View_Helper_FlashMessenger
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    private $_flashMessenger = null;

    public function flashMessenger($strMessageType = 'info')
    {
        $objFlashMessenger = $this->_getFlashMessenger();

        $strHtml = '';

        $arrMessages = $objFlashMessenger->getMessages();

        if ($arrMessages || $objFlashMessenger->hasCurrentMessages()) {

            $arrMessages = array_merge(
                $arrMessages,
                $objFlashMessenger->getCurrentMessages()
            );

            $objFlashMessenger->clearMessages();
            $objFlashMessenger->clearCurrentMessages();

            foreach ($arrMessages as $strMessage)
            {
                if (is_array($strMessage)) {
                    list($strMessageType, $strMessage) = each($strMessage);
                }

                switch ($strMessageType)
                {
                  case 'success':
                    $strHtml .= '<div class="alert alert-success alert-dismissable">';
                    $strHtml .= '  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                    $strHtml .= '  <strong>Well done!</strong> ' . $strMessage;
                    $strHtml .= '</div>';
                    break;
                  case 'warning':
                    $strHtml .= '<div class="alert alert-warning alert-dismissable">';
                    $strHtml .= '  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                    $strHtml .= '  <strong>Warning!</strong> ' . $strMessage;
                    $strHtml .= '</div>';
                    break;
                  case 'danger':
                    $strHtml .= '<div class="alert alert-danger alert-dismissable">';
                    $strHtml .= '  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                    $strHtml .= '  <strong></strong> ' . $strMessage;
                    $strHtml .= '</div>';
                    break;
                  default:
                    $strHtml .= '<div class="alert alert-info alert-dismissable">';
                    $strHtml .= '  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                    $strHtml .= '  <strong>Heads up!</strong> ' . $strMessage;
                    $strHtml .= '</div>';
                    break;
                }
            }
        }

        return $strHtml;
    }

    public function _getFlashMessenger()
    {
        if (null === $this->_flashMessenger) {
            $this->_flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        }

        return $this->_flashMessenger;
    }
}

