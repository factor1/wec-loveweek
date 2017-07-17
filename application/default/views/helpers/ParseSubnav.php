<?php

class Zend_View_Helper_ParseSubnav
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

	public function parseSubnav()
	{
        $strHtml  = '';

        if ($this->view->strPrimary && array_key_exists($this->view->strPrimary, $this->view->arrSecondary) && (count($this->view->arrSecondary[$this->view->strPrimary]) > 0)) {
            $strHtml .= '    <ul>' . "\n";

            foreach ($this->view->arrSecondary[$this->view->strPrimary] as $key => $value) {
                $strActive = ($this->view->strSecondary == $key) ? 'active' : 'inactive';
                $strHtml  .= '      <li class="' . $strActive . '"><a href="' . $value . '">' . htmlspecialchars($key) . '</a></li>' . "\n";
            }

            $strHtml .= '    </ul>' . "\n\n";
        }

        return $strHtml;
    }
}
