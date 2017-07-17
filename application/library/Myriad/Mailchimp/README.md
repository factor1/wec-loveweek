MailChimp API
=============

Super-simple, minimum abstraction MailChimp API v2 wrapper, in PHP.

I hate complex wrappers. This lets you get from the MailChimp API docs to the code as directly as possible.

Requires curl and a pulse. Abstraction is for chimps.

Examples
--------

List lists (lists/list method)

	<?php
	$MailChimp = new MailChimp('abc123abc123abc123abc123abc123-us1');
	print_r($MailChimp->call('lists/list'));

Subscribe someone to a list

	<?php
	$MailChimp = new MailChimp('abc123abc123abc123abc123abc123-us1');
	$result = $MailChimp->call('lists/subscribe', array(
					'id'                => 'b1234346',
					'email'             => array('email'=>'davy@example.com'),
					'merge_vars'        => array('FNAME'=>'Davy', 'LNAME'=>'Jones'),
					'double_optin'      => false,
					'update_existing'   => true,
					'replace_interests' => false,
					'send_welcome'      => false,
				));
	print_r($result);



Myriad Implementation:
---------------------

public function subscribeAction()
{
    $arrData = $this->_request->getPost();

    if (count($arrData) > 0) {

        $strEmail     = $this->getRequest()->getPost('email');
        $strFirstName = $this->getRequest()->getPost('first_name');
        $strLastName  = $this->getRequest()->getPost('last_name');

        $objMailchimp = new Myriad_Mailchimp('75d97b68f5d111a925afb1b769189e84-us7');

        $result = $objMailchimp->call('lists/subscribe', array(
            'id'                => '446229c07f',
            'email'             => array('email' => $strEmail),
            'merge_vars'        => array('FNAME' => $strFirstName, 'LNAME' => $strLastName),
            'double_optin'      => false,
            'update_existing'   => true,
            'replace_interests' => false,
            'send_welcome'      => false,
        ));

        print_r($result);

    }
}