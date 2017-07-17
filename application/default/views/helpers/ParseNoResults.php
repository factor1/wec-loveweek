<?php

class Zend_View_Helper_ParseNoResults
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function parseNoResults($strManager = 'manage')
	{
        $strHtml = '';

        if ($this->view->strKeywords) {
            $strHtml .= '<p>There are no records that match your search.</p>';
        } else {
            $strHtml .= '<p>There are no records in the database at this time.<br />';
            $strHtml .= 'To add a new record, <a href="' . htmlspecialchars($this->view->strSelf) . '/' . $strManager .'">click here</a></p>';
        }

        return $strHtml;
    }
}
