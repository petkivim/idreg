<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_isbnregistry
 * @author 		Petteri Kivim�ki
 * @copyright	Copyright (C) 2015 Petteri Kivim�ki. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Publisher ISBN Range Table class
 *
 * @since  1.0.0
 */
abstract class IsbnRegistryTableAbstractPublisherIdentifierRange extends JTable {

    abstract public function getRangeLength();

    /**
     * Constructor
     *
     * @param   String Name of the table
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    function __construct($tableName, &$db) {
        parent::__construct($tableName, 'id', $db);
    }

    /**
     * Stores an Identifier Range.
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

        if ($this->id) {
            // Existing item
            $this->modified_by = $user->get('username');
            $this->modified = $date->toSql();
        } else {
            // New item
            $this->created_by = $user->get('username');
            $this->created = $date->toSql();
            $this->category = strlen($this->range_begin);
            $this->free = $this->range_end - $this->range_begin + 1;
            $this->next = $this->range_begin;
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to store a new publisher identifier range into database.
     * 
     * @param range $range identifier range object which subset the 
     * publisher identifier range is
     * @param int $publisherId id of the publisher that owns the range
     * @param int $publisherIdentifier publisher identifier of the publisher 
     * that owns the range to be created
     * @return boolean returns true if and only if the object was 
     * successfully saved to the database; otherwise false
     */
    public function saveToDb($range, $publisherId, $publisherIdentifier) {
        // Get category
        $category = $this->getRangeLength() - $range->category;

        // Set values
        $this->publisher_identifier = $publisherIdentifier;
        $this->publisher_id = $publisherId;
        $this->category = $category;
        $this->is_active = true;
        $this->is_closed = false;
        $this->range_begin = str_pad('', $category, '0', STR_PAD_LEFT);
        $this->range_end = str_pad('', $category, '9', STR_PAD_LEFT);
        $this->free = $this->range_end - $this->range_begin + 1;
        $this->next = $this->range_begin;

        // Add object to DB
        if (!$this->store()) {
            return false;
        }
        return true;
    }

    /**
     * Activates the given publisher identifier range that belong to the given
     * publisher.
     * @param integer $publisherId id of the publisher
     * @param integer $publisherRangeId id of the range
     * @return boolean true on success
     */
    public function activateRange($publisherId, $publisherRangeId) {
        // Conditions for which records should be updated.
        $conditions = array(
            'publisher_id' => $publisherId,
            'id' => $publisherRangeId
        );
        // Load object
        if (!$this->load($conditions)) {
            return false;
        }

        // Update is_active field
        $this->is_active = true;

        // Update object to DB
        if (!$this->store()) {
            return false;
        }
        return true;
    }

    /**
     * Disactivates all the identifier ranges related to the publisher matching 
     * the given publisher id.
     * 
     * @param int $publisherId id of the publisher which identifier ranges 
     * are disactived
     * @return int number of affected database rows
     */
    public function disactivateAll($publisherId) {
        // Get date and user
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        // Database connection
        $query = $this->_db->getQuery(true);

        // Fields to update.
        $fields = array(
            $this->_db->quoteName('is_active') . ' = ' . $this->_db->quote(false),
            $this->_db->quoteName('modified') . ' = ' . $this->_db->quote($date->toSql()),
            $this->_db->quoteName('modified_by') . ' = ' . $this->_db->quote($user->get('username'))
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $this->_db->quoteName('publisher_id') . ' = ' . $this->_db->quote($publisherId)
        );
        // Create query
        $query->update($this->_db->quoteName($this->_tbl))->set($fields)->where($conditions);
        $this->_db->setQuery($query);
        // Execute query
        $result = $this->_db->execute();
        // Return the number of rows that was updated
        return $this->_db->getAffectedRows();
    }

    /**
     * Deletes the given publisher range.
     * @param integer $publisherRangeId id of the range to be deleted
     * @return boolean true on success
     */
    public function deleteRange($publisherRangeId) {
        // Database connection
        $query = $this->_db->getQuery(true);

        // Conditions for delete operation - delete by id
        $conditions = array(
            $this->_db->quoteName('id') . ' = ' . $this->_db->quote($publisherRangeId)
        );
        // Create query		
        $query->delete($this->_db->quoteName($this->_tbl));
        $query->where($conditions);
        $this->_db->setQuery($query);
        // Execute query  
        $result = $this->_db->execute();

        // Return true or false
        if ($this->_db->getAffectedRows() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Returns the publisher identifier range identified by the given id. 
     * @param integer $publisherIsbnRangeId id of the range to be fetched
     * @param boolean $mustBeActive must range be active
     * @return object identifier range object on success; null on failure
     */
    public function getPublisherRange($publisherIsbnRangeId, $mustBeActive) {
        $conditions = array(
            $this->_db->quoteName('id') . " = " . $this->_db->quote($publisherIsbnRangeId)
        );
        if ($mustBeActive) {
            array_push($conditions, $this->_db->quoteName('is_active') . " = " . $this->_db->quote(true));
            array_push($conditions, $this->_db->quoteName('is_closed') . " = " . $this->_db->quote(false));
        }
        // Database query
        $query = $this->_db->getQuery(true);
        $query->select('*');
        $query->from($this->_db->quoteName($this->_tbl));
        $query->where($conditions);
        $this->_db->setQuery((string) $query);

        return $this->_db->loadObject();
    }

    /**
     * Returns the publisher identifier range belonging to the publisher
     * identified by the given publisher id. The range must be active and
     * not closed.
     * @param integer $publisherId id of the publisher that owns the range
     * @return object identifier range object on success; null on failure
     */
    public function getPublisherRangeByPublisherId($publisherId) {
        $conditions = array(
            $this->_db->quoteName('publisher_id') . " = " . $this->_db->quote($publisherId),
            $this->_db->quoteName('is_active') . " = " . $this->_db->quote(true),
            $this->_db->quoteName('is_closed') . " = " . $this->_db->quote(false)
        );

        // Database query
        $query = $this->_db->getQuery(true);
        $query->select('*');
        $query->from($this->_db->quoteName($this->_tbl));
        $query->where($conditions);
        $this->_db->setQuery((string) $query);

        return $this->_db->loadObject();
    }

    /**
     * Updates the given publisher identifier range to the database.
     * @param publisherRange $publisherRange object to be updated
     * @return boolean true on success; false on failure
     */
    public function updateToDb($publisherRange) {
        // Load object
        if (!$this->load($publisherRange->id)) {
            return false;
        }

        // Update fields
        $this->free = $publisherRange->free;
        $this->taken = $publisherRange->taken;
        $this->next = $publisherRange->next;
        $this->is_active = $publisherRange->is_active;
        $this->is_closed = $publisherRange->is_closed;

        // Update object to DB
        return $this->store();
    }

    /**
     * Returns a list of identifier ranges belonging to the publisher
     * identified by the given id.
     * @param integer $publisherId id of the publisher who owns the identifiers
     * @return array list of identifiers
     */
    public function getPublisherIdentifiers($publisherId) {
        // Initialize variables.
        $query = $this->_db->getQuery(true);

        // Conditions for which records should be fetched
        $conditions = array(
            $this->_db->quoteName('publisher_id') . ' = ' . $this->_db->quote($publisherId)
        );

        // Create the base select statement.
        $query->select('*')
                ->from($this->_db->quoteName($this->_tbl))
                ->where($conditions)
                ->order('is_active DESC, publisher_identifier ASC, range_begin ASC');
        $this->_db->setQuery($query);
        // Return results
        return $this->_db->loadObjectList();
    }

}

?>