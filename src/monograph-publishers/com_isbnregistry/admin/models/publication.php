<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_isbnregistry
 * @author 		Petteri Kivim�ki
 * @copyright	Copyright (C) 2015 Petteri Kivim�ki. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Publication Model
 *
 * @since  1.0.0
 */
class IsbnregistryModelPublication extends JModelAdmin {

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'Publication', $prefix = 'IsbnregistryTable', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm(
                'com_isbnregistry.publication', 'publication', array(
            'control' => 'jform', 'load_data' => $loadData
                )
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
                'com_isbnregistry.edit.publication.data', array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        // From comma separated string to array
        $data->role_1 = $this->fromStrToArray($data->role_1);
        $data->role_2 = $this->fromStrToArray($data->role_2);
        $data->role_3 = $this->fromStrToArray($data->role_3);
        $data->role_4 = $this->fromStrToArray($data->role_4);
        $data->type = $this->fromStrToArray($data->type);
        $data->fileformat = $this->fromStrToArray($data->fileformat);

        return $data;
    }

    /**
     * Converts the given comma separated string to array.
     */
    private function fromStrToArray($source) {
        if ($source && !is_array($source)) {
            $source = explode(',', $source);
        }
        return $source;
    }

    /**
     * Returns a list of publications without an identifier belonging to the
     * publisher specified by the publisher id.
     * @param integer $publisherId id of the publisher that owns the publications
     * @param string $type publication type, can be "ISBN" or "ISMN"
     * @return object list of publications
     */
    public function getPublicationsWithoutIdentifier($publisherId, $type) {
        // Get DAO for db access
        $dao = $this->getTable();
        // Return result
        return $dao->getPublicationsWithoutIdentifier($publisherId, $type);
    }

    public function updateIdentifier($publicationId, $publisherId, $identifier, $identifierType) {
        // Check that identifier type is valid
        if (!$this->isValidIdentifierType($identifierType)) {
            return false;
        }
        // Check that publication does not have an identifier yet
        if ($this->hasIdentifier($publicationId)) {
            return false;
        }

        // Get DAO for db access
        $dao = $this->getTable();
        // Return result
        return $dao->updateIdentifier($publicationId, $publisherId, $identifier, $identifierType);
    }

    /**
     * Validates the given identifier type. Valid values are "ISBN" and "ISMN".
     * @param string $type identifier type to be validated
     * @return boolean true if type is valid; otherwise false
     */
    private function isValidIdentifierType($type) {
        return preg_match('/^(ISBN|ISMN)$/', $type);
    }

    /**
     * Chacks if the publication identified by the given id has an identifier 
     * yet.
     * @param integer $publicationId id of the publication to be checked
     * @return boolean true if the publication doesn't have an identifier yet;
     * otherwise false
     */
    private function hasIdentifier($publicationId) {
        // Get DAO for db access
        $dao = $this->getTable();
        // Get object
        $publicationIdentifier = $dao->loadPublicationIdentifier($publicationId);

        // If publication_identifier column length is 0, 
        // publication does not have an identifier yet
        if ($publicationIdentifier == null || strlen($publicationIdentifier) == 0) {
            return false;
        }
        return true;
    }

}
