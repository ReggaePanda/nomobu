<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cem
 * Date: 26.02.13
 * Time: 22:31
 * To change this template use File | Settings | File Templates.
 */

class TicketsController extends CD_Controller_Admin {
    protected $_itemClass = 'Application_Model_Ticket';

    public function listAction() {
        $modelClass = $this->_itemClass;
        $model = new $modelClass;
        $mapper = $model->getMapper();
        $items = $mapper->fetchAll();
        usort($items, array($this, 'sortTickets'));
        $this->view->items = $items;
    }

    public function sortTickets(Application_Model_Ticket $a, Application_Model_Ticket $b) {
        if($a->getState()->closes < $b->getState()->closes) return -1;
        if($a->getState()->closes > $b->getState()->closes) return 1;

        if($a->getUpdatedAsTimestamp() > $b->getUpdatedAsTimestamp()) return -1;
        if($a->getUpdatedAsTimestamp() < $b->getUpdatedAsTimestamp()) return 1;

        return 0;
    }

    public function viewAction() {
        $item = new Application_Model_Ticket($this->getRequest()->getParam('id'));

        $usersModel = new Application_Model_User();
        $users = $usersModel->getMapper()->fetchAll();
        $this->view->users = $users;

        $statesModel = new Application_Model_Status();
        $states = $statesModel->getMapper()->fetchAll();
        $this->view->states = $states;


        $this->view->headTitle('['.$item->getType()->__toString().' #'.$item->id .'] '.$item->__toString());
        $this->view->headTitle($item->getProject());
        $this->view->item = $item;
    }

    public function editAction() {
        if($this->getRequest()->getParam('id')) return $this->forward('view');
        parent::editAction();
    }

    public function setstateAction() {
        $ticket = new Application_Model_Ticket($this->getRequest()->getParam('id'));
        if(!$ticket->id) throw new Exception('Ticket not found');

        $state = new Application_Model_Status($this->getRequest()->getParam('stateId'));
        if(!$state->id) throw new Exception('State not found');

        $update = new Application_Model_Update();
        $update->tickets_id = $ticket->id;
        $update->states_id = $state->id;
        $update->save();

        CD_Message_Center::getInstance()->addMessage(new CD_Message_Success('<strong>'.$ticket->__toString().'</strong> updated'));

        if($state->closes) return $this->forward('list');
        return $this->forward('view', null, null, array('id' => $this->getParam('id')));
    }

    public function deactivateAction() {
        return $this->forward('list');
    }

    public function assignAction() {
        $ticket = new Application_Model_Ticket($this->getRequest()->getParam('id'));
        if(!$ticket->id) throw new Exception('Ticket not found');

        if($this->getRequest()->getParam('userId') == 'me') {
            throw new Exception('Cannot assign to self');
        } else {
            $user = new Application_Model_User($this->getRequest()->getParam('userId'));
            if(!$user->id) throw new Exception('User not found');
        }

        $update = new Application_Model_Update();
        $update->tickets_id = $ticket->id;
        $update->attached_to = $user->id;
        $update->save();

        CD_Message_Center::getInstance()->addMessage(new CD_Message_Success('<strong>'.$ticket->__toString().'</strong> updated'));
        $this->forward('list');
    }
}