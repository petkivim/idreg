<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_isbnregistry
 * @author 		Petteri Kivim�ki
 * @copyright	Copyright (C) 2016 Petteri Kivim�ki. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Message Table class
 *
 * @since  1.0.0
 */
class IsbnRegistryTableMessage extends JTable {

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    function __construct(&$db) {
        parent::__construct('#__isbn_registry_message', 'id', $db);
    }

    /**
     * Stores a message.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function store($updateNulls = false) {
        // Transform the params field
        if (is_array($this->params)) {
            $registry = new Registry;
            $registry->loadArray($this->params);
            $this->params = (string) $registry;
        }

        // Get date and user
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        // New item
        if (!$this->id) {
            // New item
            $this->sent_by = $user->get('username');
            $this->sent = $date->toSql();
        }

        return parent::store($updateNulls);
    }

    /**
     * Deletes a message.
     *
     * @param   integer  $pk  Primary key of the message template to be deleted.
     *
     * @return  boolean  True on success, false on failure.
     *
     */
    public function delete($pk = null) {
        // Check if message has attachments
        if($this->has_attachment) {
            if(!unlink(JPATH_COMPONENT . '/email/' . $this->attachment_name)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_ISBNREGISTRY_ERROR_MESSAGE_DELETE_ATTACHMENT_FAILED'), 'warning');
            }
        }
        // No ISBNs have been used, delete the item
        return parent::delete($pk);
    }

}
