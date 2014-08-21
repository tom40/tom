<?php

class NavController extends App_Controller_Action
{

    /**
     * Initialize object
     *
     * @return void
     */
    public function init()
    {
//        // Allow access to all authenticated users
//        if (null !== $this->_getCurrentUser()) {
//            $this->_helper->acl->allow();
//        }
    }

    public function menuAction()
    {
//         $issueCount = Application_Model_Issues::getInstance()->getCount();
//         $this->view->issueCount = $issueCount;

//         $groupCount = Application_Model_ProjectGroups::getInstance()->getCount();
//         $this->view->groupCount = $groupCount;

        $this->_helper->viewRenderer->setResponseSegment('nav');
    }

    /**
     *
     */
    public function recentItemsAction()
    {
//         $config = Zend_Registry::get('config');

//         $table = new Zend_Db_Table('recent_items');
//         $db = $table->getAdapter();

//         $select = $db->select();
//         $select->from('recent_items');
//         $select->where('user_id = ?', App_AuthIdentity::getId());
//         $select->order('timestamp DESC');
//         $select->limit($config->app->recentItems);

//         $recentItems = $db->fetchAll($select);

//         if (count($recentItems) > 0) {
//             $this->view->recentItems = $recentItems;
//             $this->_helper->viewRenderer->setResponseSegment('nav');
//         }
    }


    public function rightcolAction()
    {
//        return 'HELLO IAN';
// use different layout script with this action:
//        $this->_helper->layout->setLayout('foobaz');

        $controller = $this->_request->getControllerName();
        $this->view->controller = $controller;
        $this->_helper->viewRenderer->setResponseSegment('rightcol');
    }

}