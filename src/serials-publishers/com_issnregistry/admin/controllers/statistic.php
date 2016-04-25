<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_issnregistry
 * @author 	Petteri Kivim�ki
 * @copyright	Copyright (C) 2016 Petteri Kivim�ki. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Statistic Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_issnregistry
 * @since       1.0.0
 */
class IssnregistryControllerStatistic extends JControllerForm {

    public function getStatistics() {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get component parameters
        $params = JComponentHelper::getParams('com_issnregistry');
        // Get statistics file format
        $format = $params->get('statistics_format', 'XLS');
        // Get form data
        $data = $this->input->post->get('jform', array(), 'array');
        // Get begin
        $begin = $data['begin'];
        // Get end
        $end = $data['end'];
        // Get type
        $type = $data['type'];
        
        // Redirect
        if ($this->validateDate($begin) && $this->validateDate($end) && $this->validateType($type)) {
            $this->setRedirect('index.php?option=com_issnregistry&view=statistic&format=' . strtolower($format) . '&begin=' . $begin . '&end=' . $end . '&type=' . $type);
        } else {
            if (!$this->validateType($type)) {
                $this->setMessage(JText::_('COM_ISSNREGISTRY_STATISTIC_INVALID_TYPE'), 'error');
            } else {
                $this->setMessage(JText::_('COM_ISSNREGISTRY_STATISTIC_INVALID_DATE'), 'error');
            }
            $this->setRedirect('index.php?option=com_issnregistry&view=statistic&layout=popup&tmpl=component');
        }
        $this->redirect();
    }

    private function validateDate($dateStr) {
        if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dateStr)) {
            return false;
        }
        return true;
    }

    private function validateType($type) {
        if (!preg_match('/^(ISSN|PUBLISHERS|PUBLICATIONS|FORMS)$/', $type)) {
            return false;
        }
        return true;
    }

}
