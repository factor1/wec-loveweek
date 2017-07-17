<?php

class Zend_View_Helper_ParseMessages
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

	public function parseMessages()
	{
        $strHtml = '';

        if ($this->view->strMessage) {
            $strHtml .= '<p class="message">' . htmlspecialchars($this->view->strMessage) . '</p>';
        }

        if ($this->view->strError) {
            $strHtml .= '<p class="error">' . htmlspecialchars($this->view->strError) . '</p>';
        }

        if ($this->view->arrErrors && count($this->view->arrErrors) > 0) {
            $strHtml .= '<ul class="errors">';
            foreach ($this->view->arrErrors as $strError) {
                $strHtml .= '<li>' . htmlspecialchars($strError) . '</li>';
            }
            $strHtml .= '</ul>';
        }

        return $strHtml;
    }
}
