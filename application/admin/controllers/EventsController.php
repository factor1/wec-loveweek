<?php

require_once 'ControllerAbstract.php';

class Admin_EventsController extends Admin_ControllerAbstract
{
    public function init()
    {
        parent::init();

        $this->view->strAdminPrimary = 'Events';
        $this->view->headTitle('Events');

        /**
         * Set select option values
         */

        $this->view->arrCities = array(
            'gloucester'   => 'Gloucester',
            'hampton'      => 'Hampton',
            'newport-news' => 'Newport News',
            'matthews'     => 'Mathews',
            //'virginia-beach' => 'Virginia Beach',
            'poquoson'     => 'Poquoson',
            'seaford'     => 'Seaford',
            'toano'        => 'Toano',
            'williamsburg' => 'Williamsburg',
            'yorktown'     => 'Yorktown',
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

        $this->view->arrAges = array(
            'All Ages' => 'No age restrictions',
            '15+'      => '15 and over',
            '16+'      => '16 and over',
            '17+'      => '17 and over',
            '18+'      => '18 and over'
        );
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        /**
         * Column Headers
         */

        $this->view->arrFields = array(
            'events_title'          => 'Event',
            'event_organization'    => 'Organization',
            'event_start_date'      => 'Date',
            'event_start_time'      => 'Start Time',
            'event_spots_available' => 'Spots'
        );

        /**
         * Get Events
         */

        $objEvents = new Events();
        $objEvents->setKeywords($this->_getParam('keywords', ''));
        $objEvents->setField(array('event_title', 'event_city', 'event_organization'));
        $objEvents->setSort($this->_getParam('sort', 'event_start_date'));
        $objEvents->setDir($this->_getParam('sdir', 'ASC'));

        /**
         * Zend Paginator
         */

        $objPaginator = Zend_Paginator::factory($objEvents->getSelectInstance());
        $objPaginator->setCurrentPageNumber($this->_getParam('page', 1));
        $objPaginator->setItemCountPerPage(30);
        $this->view->objPaginator = $objPaginator;

        /**
         * Assign Common View Variables
         */

        $this->view->strKeywords = $objEvents->getKeywords();
        $this->view->strField    = $objEvents->getField();
        $this->view->strSort     = $objEvents->getSort();
        $this->view->strDir      = $objEvents->getDir();
    }

    public function viewAction()
    {
        $intId = (int) $this->_getParam('id', 0);

        $objEvents = new Events();
        $objEvent = $objEvents->find($intId)->current();

        if ($objEvent) {
            $this->view->headTitle($objEvent->event_title);
            $this->view->objEvent = $objEvent;
        } else {
            $this->_helper->FlashMessenger(array('danger' => 'The event you requested could not be found.'));
            $this->_redirect('/admin/events');
        }
    }

    public function manageAction()
    {
        $intId = (int) $this->_getParam('id', 0);
        $strReferer = $this->_getParam('referer', getenv('HTTP_REFERER'));

        /**
         * User Lookup
         */

        $this->view->objUsers = Participants::getEventUsers($intId);

        /**
         * Process
         */

        if ($this->getRequest()->getPost()) {

            $arrData = array();
 
            $arrData['event_title']           = trim($this->getRequest()->getPost('event_title'));
            $arrData['event_organization']    = trim($this->getRequest()->getPost('event_organization'));
            $arrData['event_category']        = trim($this->getRequest()->getPost('event_category'));
            $arrData['event_start_date']      = $this->getRequest()->getPost('event_start_date'); // YYYY-MM-DD
            $arrData['event_end_date']        = $this->getRequest()->getPost('event_end_date'); // YYYY-MM-DD
            $arrData['event_start_time']      = date ('H:i:s', strtotime($this->getRequest()->getPost('event_start_time'))); // H:i:s
            $arrData['event_end_time']        = date ('H:i:s', strtotime($this->getRequest()->getPost('event_end_time'))); // H:i:s
            $arrData['event_city']            = trim($this->getRequest()->getPost('event_city'));
            $arrData['event_address']         = trim($this->getRequest()->getPost('event_address'));
            $arrData['event_zip']             = trim($this->getRequest()->getPost('event_zip'));
            $arrData['event_age']             = trim($this->getRequest()->getPost('event_age'));
            $arrData['event_spots_total']     = (int) $this->getRequest()->getPost('event_spots_total');
            $arrData['event_spots_available'] = ($intId != 0) ? (int) $this->getRequest()->getPost('event_spots_available') : (int) $this->getRequest()->getPost('event_spots_total');
            $arrData['event_register_link']   = trim($this->getRequest()->getPost('event_register_link'));
            $arrData['event_no_register']     = (int) $this->getRequest()->getPost('event_no_register');
            $arrData['event_notes']           = trim($this->getRequest()->getPost('event_notes'));
            $arrData['event_email']           = trim($this->getRequest()->getPost('event_email'));
            $arrData['event_caution']         = trim($this->getRequest()->getPost('event_caution'));

            $arrData = Myriad_Utils::stripSlashes($arrData);

            /**
             * Update Available Spots if Total Spots Changes
             */

            if ($intId != 0) {

                $objEvents = new Events();
                $objEvent = $objEvents->find($intId)->current(); // grab row

                if ($arrData['event_spots_total'] != $objEvent->event_spots_total) { // if the admin updated total spots
                    $intTotalDiff = $arrData['event_spots_total'] - $objEvent->event_spots_total;
                    $intNewAvailable = $objEvent->event_spots_available + $intTotalDiff;
                    if ($intNewAvailable >= 0) {
                        $arrData['event_spots_available'] = $intNewAvailable;
                    } else {
                        $arrData['event_spots_available'] = 0;
                    }
                }
            }


            /**
             * Error Checking
             */

            $arrErrors = array();

            if ($arrData['event_title'] == '') {
                $arrErrors[] = 'Missing Title';
            }

            if ($arrData['event_category'] == '') {
                $arrErrors[] = 'Missing Category';
            }

            if ($arrData['event_start_date'] == '') {
                $arrErrors[] = 'Missing Start Date';
            }

            if ($arrData['event_end_date'] == '') {
                $arrErrors[] = 'Missing End Date';
            }

            if ($arrData['event_start_time'] == '') {
                $arrErrors[] = 'Missing Start Time';
            }

            if ($arrData['event_end_time'] == '') {
                $arrErrors[] = 'Missing End Time';
            }

            if ($arrData['event_city'] == '') {
                $arrErrors[] = 'Missing City';
            }

            if ($arrData['event_spots_total'] == '') {
                $arrErrors[] = 'Missing Total Spots';
            }


            /**
             * Insert / Update Record
             */

            if (count($this->view->arrErrors) == 0) {

                $objEvents = new Events();

                if ($intId == 0) {
                    $intId = $objEvents->insert($arrData);
                    $this->_helper->FlashMessenger(array('success' => 'The event has been added successfully.'));
                } else {
                    $strWhere = $objEvents->getAdapter()->quoteInto('id = ?', $intId);
                    $objEvents->update($arrData, $strWhere);
                    $this->_helper->FlashMessenger(array('success' => 'The event has been updated successfully.'));
                }
            } else {
                $this->_helper->FlashMessenger(array('danger' => $arrErrors[0]));
            }
        }


        /**
         * Create or Find Row
         */

        if (count($this->view->arrErrors) > 0) {

            $objEvent = (object) $arrData;

        } else {

            $objEvents = new Events();

            if ($intId > 0) {
                $objEvent = $objEvents->find($intId)->current();

                if (!is_object($objEvent)) {
                    $this->_helper->FlashMessenger(array('danger' => 'The event you requested could not be found.'));
                    $this->_redirect('/admin/events');
                }
            } else {
                $objEvent = $objEvents->createRow();
            }
        }


        /**
         * Assign View Variables
         */

        $this->view->intId      = $intId;
        $this->view->strReferer = $strReferer;
        $this->view->objEvent   = $objEvent;
        $this->view->arrStates  = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District Of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin');
    }

    public function deleteAction()
    {
        if (!$this->boolIsAdmin) {
            $this->_helper->FlashMessenger(array('warning' => 'Only administrators are allowed to delete events.'));
            $this->_redirect(getenv('HTTP_REFERER'));
        }

        $intId = (int) $this->_getParam('id', 0);
        $arrChecked = $this->_getParam('checked', array());

        if ($intId > 0) {
            $arrChecked[] = $intId;
        }

        $objEvents = new Events();

        $this->_helper->FlashMessenger(array('success' => $objEvents->deleteChecked($arrChecked, 'event')));

        $this->_redirect(getenv('HTTP_REFERER'));
    }
}
