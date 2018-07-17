<?php

require_once 'ControllerAbstract.php';

class EventsController extends ControllerAbstract
{
    public function init()
    {
        parent::init();

        $this->config = Zend_Registry::get('config');

        $this->view->strPrimary = 'Events';

        /**
         * Set select option values
         */

        $this->arrCities = $this->view->arrCities = array(
            'gloucester'   => 'Gloucester',
            'hampton'      => 'Hampton',
            'newport-news' => 'Newport News',
            'toano'        => 'Toano',
            'matthews'		=> 'Mathews',
            //'virginia-beach' => 'Virginia Beach',
            'williamsburg' => 'Williamsburg',
            'yorktown'     => 'Yorktown',
            'poquoson'     => 'Poquoson',
            'lanexa'	=> 'Lanexa'
        );

        $this->view->arrCats = array(
            'administrative-work'   => 'Administrative Work',
            'blood-drive'           => 'Blood Drive',
            'caring-for-people'     => 'Caring for People',
            'caring-for-animals'    => 'Caring for Animals',
            'caring-for-kids'       => 'Caring for Kids & Students',
            'collecting-sorting'    => 'Collecting, Sorting & Organizing',
            'food-prep'             => 'Food Preparation & Meal Services',
            'landscaping-cleaning'  => 'Landscaping & Cleaning',
            'painting-construction' => 'Painting & Construction'        );

        $this->arrAges = $this->view->arrAges = array(
            'All Ages' => 'No age restrictions',
            '15+'      => '15 and over',
            '16+'      => '16 and over',
            '17+'      => '17 and over',
            '18+'      => '18 and over'
        );
    }

    public function indexAction()
    {
        $this->view->boolHome = true;

        /**
         * Column Headers
         */

        $this->view->arrFields = array(
            'events_title'          => 'Event',
            'event_organization'    => 'Organization',
            'event_start_date'      => 'Date',
            'event_start_time'      => 'Start Time',
            'event_spots_available' => 'Open<br>Spots',
            'event_join'            => 'Join',
        );

        /**
         * Filtering
         */

        $arrData['event_city']       = $this->_getParam('event_city', array());
        $arrData['event_category']   = $this->_getParam('event_category', array());
        $arrData['event_age']        = $this->_getParam('event_age', array());
        $arrData['event_start_date'] = $this->_getParam('event_start_date', '');
        $arrData['keywords']         = $this->_getParam('keywords', '');

        /**
         * Get Events
         */

        $objEvents = new Events();
        $objEvents->setKeywords($arrData['keywords']);
        $objEvents->setField(array('event_title', 'event_city', 'event_organization'));
        $objEvents->setSort($this->_getParam('sort', 'event_start_date'));
        $objEvents->setDir($this->_getParam('sdir', 'ASC'));

        $objSelect = $objEvents->getSelectInstance();

        if (!empty($arrData['event_city'])) {
            $objSelect->where('event_city IN (?)', $arrData['event_city']);
        }

        if (!empty($arrData['event_category'])) {
            $objSelect->where('event_category IN (?)', $arrData['event_category']);
        }

        if (!empty($arrData['event_age'])) {
            $objSelect->where('event_age IN (?)', $arrData['event_age']);
        }

        if (!empty($arrData['event_start_date'])) {
            $objSelect->where('event_start_date = ?', $arrData['event_start_date']);
        }

        /**
         * Zend Paginator
         */

        $objPaginator = Zend_Paginator::factory($objSelect);
        $objPaginator->setCurrentPageNumber($this->_getParam('page', 1));
        $objPaginator->setItemCountPerPage(30);
        $this->view->objPaginator = $objPaginator;

        /**
         * Assign Common View Variables
         */

        if (!empty($arrData['event_city']) || !empty($arrData['event_category']) || !empty($arrData['event_age']) || !empty($arrData['event_start_date']) || !empty($arrData['keywords'])) {
            $this->view->boolFilter = true;
        }

        if (count($objPaginator) <= 0) {
            $this->view->boolEmpty = true;
        }

        $this->view->arrData     = $arrData;
        $this->view->strKeywords = $objEvents->getKeywords();
        $this->view->strField    = $objEvents->getField();
        $this->view->strSort     = $objEvents->getSort();
        $this->view->strDir      = $objEvents->getDir();
    }

    public function viewAction()
    {
        $this->view->boolNoContainer = true;

        $intId = (int) $this->_getParam('id', 0);

        $objEvents = new Events();
        $objEvent = $objEvents->find($intId)->current();

        if ($objEvent) {
            $this->view->headTitle($objEvent->event_title);
            $this->view->objEvent = $objEvent;
        } else {
            $this->_helper->FlashMessenger(array('danger' => 'The event you requested could not be found.'));
            $this->_redirect('/events');
        }
    }

    public function registerAction()
    {
        if (!$this->boolIsUser) {
            $this->_helper->FlashMessenger(array('danger' => 'You must be logged in to join events.'));
            $this->_redirect('/login');
        }

        $intId = (int) $this->_getParam('id', 0);

        $objEvents = new Events();
        $objEvent = $objEvents->find($intId)->current();

        if ($objEvent) {

            if(!$objEvent->event_spots_available) {
                $this->_helper->FlashMessenger(array('danger' => 'This event has reached its max and is closed for registration.'));
                $this->_redirect('/events');
            }

            $objRecord = Participants::userIsRegistered($objEvent->id, $this->objAuth->getIdentity()->id);

            if (empty($objRecord)) {

                $this->view->headTitle($objEvent->event_title);
                $this->view->objEvent = $objEvent;
                $this->view->intSpotsTotal = $intSpotsTotal = $objEvent->event_spots_total;
                $this->view->intSpotsAvailable = $intSpotsAvailable = $objEvent->event_spots_available;

                /**
                 * Process
                 */

                if ($this->getRequest()->getPost()) {

                    $arrData = array();

                    $arrData['event_id']     = (int) $this->getRequest()->getPost('event_id');
                    $arrData['user_id']      = (int) $this->getRequest()->getPost('user_id');
                    $arrData['participants'] = (int) $this->getRequest()->getPost('participants');

                    $arrData = Myriad_Utils::stripSlashes($arrData);

                    $arrErrors = array();

                    if ($arrData['participants'] == '') {
                        $arrErrors[] = 'Missing Number of Participants';
                    }

                    if ($arrData['participants'] > $intSpotsAvailable) {
                        $arrErrors[] = 'There aren\'t enough spots for your party. (' . $intSpotsAvailable . ' spots available)';
                    }

                    /**
                     * Insert Record
                     */

                    if (count($arrErrors) == 0) {
                        $objParticipants = new Participants();
                        $intId = $objParticipants->insert($arrData);
                        $this->_helper->FlashMessenger(array('success' => 'You have been registered successfully.'));

                        /**
                         * Update Event Stats
                         */

                        $strWhere = $objEvents->getAdapter()->quoteInto('id = ?', $objEvent->id);
                        $intNewSpots = $intSpotsAvailable - $arrData['participants'];
                        $objEvents->update(array('event_spots_available' => $intNewSpots), $strWhere);

                        /**
                         * Send Confirmation Email
                         */

                        $objUsers = new Users();
                        $objUser = $objUsers->find($arrData['user_id'])->current();

                        // Introduction

                        $strBodyText = "Dear " . $objUser->user_first_name . ",\n\n";
                        $strBodyHTML = "Dear " . $objUser->user_first_name . ",<br /><br />";

                        // Message body
                        
                        $strBodyText .= "Thank you for registering for " . $objEvent->event_title . ".";
                        $strBodyHTML .= 'Thank you for registering for ' . $objEvent->event_title . '.';
						
						
						// Event specific message copy
                        if (!empty($objEvent->event_email)) {
                            $strBodyText .= $objEvent->event_email;
                            $strBodyHTML .= nl2br($objEvent->event_email);
                        } else {
                            
                        }

                        // When

                        if ($objEvent->event_start_date != $objEvent->event_end_date) {
                            $strBodyText .= "\n\nWhen: " . date('F jS', strtotime($objEvent->event_start_date)) . " - " . date('F jS', strtotime($objEvent->event_end_date)) . ", " . date('g:ia', strtotime($objEvent->event_start_time)) . " to " . date('g:ia', strtotime($objEvent->event_end_time));
                            $strBodyHTML .= "<br /><br />When: " . date('F jS', strtotime($objEvent->event_start_date)) . " - " . date('F jS', strtotime($objEvent->event_end_date)) . ", " . date('g:ia', strtotime($objEvent->event_start_time)) . " to " . date('g:ia', strtotime($objEvent->event_end_time));
                        } else {
                            $strBodyText .= "\n\nWhen: " . date('F jS', strtotime($objEvent->event_start_date)) . ", " . date('g:ia', strtotime($objEvent->event_start_time)) . " to " . date('g:ia', strtotime($objEvent->event_end_time));
                            $strBodyHTML .= "<br /><br />When: " . date('F jS', strtotime($objEvent->event_start_date)) . ", " . date('g:ia', strtotime($objEvent->event_start_time)) . " to " . date('g:ia', strtotime($objEvent->event_end_time));
                        }

                        // Where

                        $event_city = ($objEvent->event_city && isset($this->arrCities[$objEvent->event_city])) ? $this->arrCities[$objEvent->event_city]. ', VA' : '';

                        if (!empty($objEvent->event_address) && !empty($objEvent->event_zip)) {
                            $strBodyText .= "\nWhere: " . $objEvent->event_address . ' ' . $event_city . '' . $objEvent->event_zip;
                            $strBodyHTML .= "<br />Where: " . $objEvent->event_address . ' ' . $event_city . ' ' . $objEvent->event_zip;
                        } else if($event_city) {
                            $strBodyText .= "\nWhere: " . $event_city;
                            $strBodyHTML .= "<br />Where: " . $event_city;
                        }

                        // Organization

                        if (!empty($objEvent->event_organization)) {
                            $strBodyText .= "\nOrganization: " . $objEvent->event_organization;
                            $strBodyHTML .= "<br />Organization: " . $objEvent->event_organization;
                        }

                        // More Info
                        $config = $this->config->appSettings;
                        $strBodyText .= "\nEvent Info: ".$config->url->base."/events/view/id/" . $objEvent->id;
                        $strBodyHTML .= '<br /><a href="'.$config->url->base.'/events/view/id/' . $objEvent->id . '">View Event Info</a>. <br /><br />If you are no longer able to volunteer for this project, please remove your registration by logging into the Love Week database, selecting <strong><a href="http://wecloveweek.com/events/me">My Events</a></strong> at the bottom of the website, and deleting the project that you are no longer able to attend.';
                        
                        
                        

                        // Salutations

                        $strBodyText .= "\n\Waters Edge Church\n757.867.7378";
                        $strBodyHTML .= "<br /><br />Waters Edge Church<br />757.867.7378";

                        // Send Mail
                        $config = $this->config->resources->mail->eventRegister;
                        $objMail = new Zend_Mail();
                        $objMail->setBodyText(utf8_decode($strBodyText));
                        $objMail->setBodyHtml(utf8_decode($strBodyHTML));
                        $objMail->addTo($config->From->email,$config->From->name);
                        $objMail->addTo($objUser->username);
                        $objMail->setSubject('WEC Love Week: ' . $objEvent->event_title);

                        if ($objMail->send()) {
                            $this->_helper->FlashMessenger(array('info' => 'Confirmation email sent to ' . $objUser->username . '.'));
                        } else {
                            $this->_helper->FlashMessenger(array('danger' => 'Confirmation email failed to send.'));
                        }

                        $this->_redirect('/events/me');
                    } else {
                        $this->_helper->FlashMessenger(array('danger' => $arrErrors[0]));
                    }
                }

            } else {
                $this->_redirect('/events/me');
            }

        } else {
            $this->_helper->FlashMessenger(array('danger' => 'The event you requested could not be found.'));
            $this->_redirect('/events');
        }
    }

    public function meAction()
    {
        /**
         * Column Headers
         */

        $this->view->arrFields = array(
            'events_title'      => 'Title',
            'event_start_date'  => 'Start date',
            'event_end_date'    => 'End date',
            'event_city'        => 'City',
            'event_age'         => 'Age',
            'event_spots_total' => 'Spots',
            'event_spots_me'    => 'My Party',
        );

        /**
         * Events Lookup
         */

        if ($this->boolIsUser) {
            $this->view->objEvents = Participants::getUserEvents($this->objAuth->getIdentity()->id);
        }
    }

    public function changeAction()
    {
        $intUserId       = (int) $this->getRequest()->getParam('user_id');
        $intEventId      = (int) $this->getRequest()->getParam('event_id');
        $intParticipants = (int) $this->getRequest()->getParam('participants');

        if (isset($intUserId) && isset($intEventId)) {

            /**
             * Get Existing Record
             */

            $objParticipants = new Participants();
            $objRecord = $objParticipants->fetchRow($objParticipants->select()->where('event_id = ?', $intEventId)->where('user_id = ?', $intUserId));

            if (!empty($objRecord)) {

                $intOldParticipants = $objRecord->participants;
                $objRecord->participants = $intParticipants;

                $objRecord->save(); // save new value

                /**
                 * Update Event Stats
                 */

                $objEvents = new Events();
                $objEvent = $objEvents->find($intEventId)->current();
                $strWhere = $objEvents->getAdapter()->quoteInto('id = ?', $intEventId);

                $intSpotDiff = $intParticipants - $intOldParticipants;
                $intNewSpots = $objEvent->event_spots_available - $intSpotDiff;

                $objEvents->update(array('event_spots_available' => $intNewSpots), $strWhere);

                $this->_helper->FlashMessenger(array('success' => 'Your participant count has been updated in our records.'));
            }
        }

        $this->_redirect('/events/me');
    }

    public function unregisterAction()
    {
        $intUserId  = (int) $this->getRequest()->getParam('user_id');
        $intEventId = (int) $this->getRequest()->getParam('event_id');
        $boolAdmin  = (int) $this->getRequest()->getParam('admin');

        if (isset($intUserId) && isset($intEventId)) {

            /**
             * Get Existing Record
             */

            $objParticipants = new Participants();
            $objRecord = $objParticipants->fetchRow($objParticipants->select()->where('event_id = ?', $intEventId)->where('user_id = ?', $intUserId));

            if (!empty($objRecord)) {

                $intFreedParticipants = $objRecord->participants;
                $objRecord->delete(); // delete row

                /**
                 * Update Event Stats
                 */

                $objEvents = new Events();
                $objEvent = $objEvents->find($intEventId)->current();
                $strWhere = $objEvents->getAdapter()->quoteInto('id = ?', $intEventId);

                $intNewSpots = $objEvent->event_spots_available + $intFreedParticipants;

                $objEvents->update(array('event_spots_available' => $intNewSpots), $strWhere);

                if ($boolAdmin) {
                    $this->_helper->FlashMessenger(array('success' => 'The user has been successfully unregistered.'));
                    $this->_redirect('/admin/events/manage/id/' . $intEventId);
                }

                $this->_helper->FlashMessenger(array('success' => 'You have successfully unregistered.'));
            }
        }

        $this->_redirect('/events/me');
    }
}