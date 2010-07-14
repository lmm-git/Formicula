<?php
/**
 * Formicula - the contact mailer for Zikula
 * -----------------------------------------
 *
 * @copyright  (c) Formicula Development Team
 * @link       http://code.zikula.org/formicula
 * @version    $Id$
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Frank Schummertz <frank@zikula.org>
 * @package    Third_Party_Components
 * @subpackage formicula
 */

class Formicula_Api_Admin extends Zikula_Api
{
    /**
     * getContact
     * reads a single contact by id
     *
     *@param cid int contact id
     *@returns array with contact information
     */
    public function getContact($args)
    {
        if (!isset($args['cid']) || empty($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Formicula::', ":$cid:", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $contact = DBUtil::selectObjectByID('formcontacts', $args['cid'], 'cid');
        return $contact;
    }

    /**
     * readContacts
     * reads the contact list and returns it as array
     *
     *@param none
     *@returns array with contact information
     */
    public function readContacts()
    {
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission("Formicula::", "::", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        $contacts = array();
        $pntable =&DBUtil::getTables();
        $contactscolumn = &$pntable['formcontacts_column'];
        $orderby = "ORDER BY $contactscolumn[cid]";

        $contacts = DBUtil::selectObjectArray('formcontacts', '', $orderby);

        // Return the contacts
        return $contacts;
    }

    /**
     * createContact
     * creates a new contact
     *
     *@param name  string name of the contact
     *@param email string email address
     *@param public int 0/1 to indicate if address is for public use
     *@param sname string use this as senders name in confirmation mails
     *@param semail string use this as senders email address in confirmation mails
     *@param ssubject string use this as subject in confirmation mails
     *@returns boolean
     */
    public function createContact($args)
    {
        if (!System::isInstalling() && !SecurityUtil::checkPermission('Formicula::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if ((!isset($args['name'])) || (!isset($args['email']))) {
            return LogUtil::registerArgsError();
        }
        if ((!isset($args['public'])) || empty($args['public'])) {
            $args['public'] = 0;
        }

        $obj = DBUtil::insertObject($args, 'formcontacts', 'cid');
        if($obj == false) {
            return LogUtil::registerError(__('Error! Creation attempt failed.', $dom));
        }
        $this->callHooks('item', 'create', $obj['cid']);
        return $obj['cid'];
    }

    /**
     * deleteContact
     * deletes a contact.
     *
     *@param cid int contact id
     *@returns boolean
     */
    public function deleteContact($args)
    {
        if ((!isset($args['cid'])) || empty($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!SecurityUtil::checkPermission('formicula::', ':' . (int)$args['cid'] . ':', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        $res = DBUtil::deleteObjectByID ('formcontacts', (int)$args['cid'], 'cid');
        if($res==false) {
            return LogUtil::registerError($this->__('Error! Sorry! Deletion attempt failed.'));
        }

        // Let any hooks know that we have deleted a contact
        $this->callHooks('item', 'delete', $args['cid']);

        // Let the calling process know that we have finished successfully
        return true;
    }


    /**
     * updateContact
     * updates a contact
     *
     *@param cid int contact id
     *@param name string name of the contact
     *@param email string email address
     *@returns boolean
     */
    public function updateContact($args)
    {
        if ((!isset($args['cid'])) ||
                (!isset($args['name'])) ||
                (!isset($args['email']) ||
                        (empty($args['name'])) ||
                        (empty($args['email'])) )) {
            return LogUtil::registerArgsError();
        }
        if ((!isset($args['public'])) || empty($args['public'])) {
            $args['public'] = 0;
        }

        // Security check
        if (!SecurityUtil::checkPermission('formicula::', ':' . $args['cid'] . ':', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $res = DBUtil::updateObject($args, 'formcontacts', '', 'cid');
        if($res == false) {
            return LogUtil::registerError($this->__('Error! Update attempt failed.'));
        }
        $this->callHooks('item', 'update', $args['cid']);
        return $args['cid'];
    }

    /**
     * get available admin panel links
     *
     * @author Mark West
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();
        if (SecurityUtil::checkPermission('formicula::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('formicula', 'admin', 'view'), 'text' => $this->__('View contacts'));
            $links[] = array('url' => ModUtil::url('formicula', 'admin', 'edit', array('cid' => -1)), 'text' => $this->__('Add contact'));
            $links[] = array('url' => ModUtil::url('formicula', 'admin', 'clearcache'), 'text' => $this->__('Clear captcha image cache'));
            $links[] = array('url' => ModUtil::url('formicula', 'admin', 'modifyconfig'), 'text' => $this->__('Modify configuration'));
        }
        return $links;
    }
}