<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_isbnregistry
 * @author 	Petteri Kivim�ki
 * @copyright	Copyright (C) 2016 Petteri Kivim�ki. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/abstractpublisheridentifierrangecanceled.php';

/**
 * Publisher ISBN Range Canceled Table class
 *
 * @since  1.0.0
 */
class IsbnRegistryTablePublisherIsbnrangecanceled extends IsbnRegistryTableAbstractPublisherIdentifierRangeCanceled {

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    function __construct(&$db) {
        parent::__construct('#__isbn_registry_publisher_isbn_range_canceled', $db);
    }
}
