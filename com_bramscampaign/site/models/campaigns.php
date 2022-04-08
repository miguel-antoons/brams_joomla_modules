<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Log\Log;

/**
 * Campaigns Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * campaigns.
 *
 * @since  0.0.1
 */
class BramsCampaignModelCampaigns extends ItemModel {
	public $campaign_messages = array(
		// default message (0) is empty
		(0) => array(
			('message') => '',
			('css_class') => ''
		),
		(1) => array(
			('message') => 'Campaign was successfully updated',
			('css_class') => 'success'
		),
		(2) => array(
			('message') => 'Campaign was successfully created',
			('css_class') => 'success'
		)
	);

	// function connects to the database and returns the database object
	private function connectToDatabase() {
		try {
			/* Below lines are for connecting to production database later on */
			// $database_options = array();

			// $database_options['driver'] = $_ENV['DB_DRIVER'];
			// $database_options['host'] = $_ENV['DB_HOST'];
			// $database_options['user'] = $_ENV['DB_USER'];
			// $database_options['password'] = $_ENV['DB_PASSWORD'];
			// $database_options['database'] = $_ENV['DB_NAME'];
			// $database_options['prefix'] = $_ENV['DB_PREFIX'];

			// return JDatabaseDriver::getInstance($database_options);

			/*
			below line is for connecting to default joomla database
			WARNING : this line should be commented/removed for production
			*/
			return Factory::getDbo();
		} catch (Exception $e) {
			// if an error occurs, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return false;
		}
	}

	/**
	 * Function gets all the campaigns and their information from the database. If no
	 * error occurs, it returns all the received information from the database.
	 *
	 * @return int|array -1 if an error occurs, the array with all the campaigns on success
	 * @since 0.0.1
	 */
	public function getCampaigns() {
		// if the connection to the database failed, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$campaigns_query = $db->getQuery(true);
		$sub_campaign_query = $db->getQuery(true);

		// query to check if there are any countings for a given campaign
		$sub_campaign_query->select($db->quoteName('campaign_id'));
		$sub_campaign_query->from($db->quoteName('manual_counting'));
		$sub_campaign_query->where(
			$db->quoteName('campaign_id') . ' = ' . $db->quoteName('m_camp.id') . ' limit 1'
		);

		// SQL query to get all the campaigns and their information
		$campaigns_query->select(
			$db->quoteName('m_camp.id') . ' as id, '
			. $db->quoteName('m_camp.name') . ' as name, '
			. $db->quoteName('m_type.name') . ' as type, '
			. $db->quoteName('m_camp.start') . ' as start, '
			. $db->quoteName('m_camp.end') . ' as end, '
			. $db->quoteName('system_id') . ' as sysId, '
			. $db->quoteName('system.name') . ' as station, '
			. 'exists(' . $sub_campaign_query . ')'   . ' as notDeletable'
		);
		$campaigns_query->from($db->quoteName('manual_counting_campaign_type') . ' as m_type');
		$campaigns_query->join(
			'INNER',
			$db->quoteName('manual_counting_campaign') . ' as m_camp'
			. ' ON '
			. $db->quoteName('m_type.id')
			. ' = '
			. $db->quoteName('m_camp.type_id')
		);
		$campaigns_query->join(
			'INNER',
			$db->quoteName('system')
			. ' ON '
			. $db->quoteName('system.id')
			. ' = '
			. $db->quoteName('system_id')
		);

		$db->setQuery($campaigns_query);

		// try to execute the query and return the system info
		try {
			return $db->loadObjectList();
		} catch (RuntimeException $e) {
			// if an error occurs, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	/**
	 * Function deletes a campaign with campaign.id equal to $id (arg)
	 *
	 * @param $id   int                 id of the campaign that has to be deleted
	 * @return      int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
	 *
	 * @since 0.0.1
	 */
	public function deleteCampaign($id) {
		// if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$campaign_query = $db->getQuery(true);

		// delete condition
		$condition = array(
			$db->quoteName('id') . ' = ' . $db->quote($id)
		);

		// delete query
		$campaign_query->delete($db->quoteName('manual_counting_campaign'));
		$campaign_query->where($condition);

		$db->setQuery($campaign_query);

		// trying to execute the query and return the results
		try {
			return $db->execute();
		} catch (RuntimeException $e) {
			// on fail, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	/**
	 * Function gets all the available campaign names from the database except
	 * the campaign name whose id is equal to $id (arg
	 *
	 * @param $id   int         id of the campaign not to take the campaign name from
	 * @return      int|array   -1 if the function fails, the array with campaign names on success
	 *
	 * @since 0.0.1
	 */
	public function getLocationCodes($id = -1) {
		// if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$campaign_query = $db->getQuery(true);

		// query to get all the campaign names and ids
		$campaign_query->select(
			$db->quoteName('id') . ', '
			. $db->quoteName('name')
		);
		$campaign_query->from($db->quoteName('manual_counting_campaign'));

		$db->setQuery($campaign_query);

		// try to execute the query and return the results
		try {
			return $this->structureCampaigns($db->loadObjectList(), $id);
		} catch (RuntimeException $e) {
			// on fail, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	/**
	 * Function filters the campaign whose id is equal to $id from the database
	 * results and also transforms an array of stdClasses into a simple array.
	 *
	 * @param $database_data    array   campaign names from the database
	 * @param $id               int     id to filter
	 * @return                  array   array of strings with all campaign names except the one with id = $id
	 *
	 * @since 0.0.1
	 */
	private function structureCampaigns($database_data, $id) {
		$final_campaign_array = array();
		foreach ($database_data as $campaign) {
			// if the campaign id is not equal to $id (arg)
			if ($campaign->id !== $id) {
				// add the campaign name to the campaign names array
				$final_campaign_array[] = $campaign->name;
			}
		}

		return $final_campaign_array;
	}

	/**
	 * Function gets all the information from the database related to the campaign
	 * with its id equal to $campaign_id (arg)
	 *
	 * @param $campaign_id  int         id of the campaign to get information about
	 * @return              array|int   -1 on fail, array with location info on success
	 *
	 * @since 0.0.1
	 */
	public function getCampaign($campaign_id) {
		// if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$campaign_query = $db->getQuery(true);

		// query to get the campaign information
		$campaign_query->select(
			$db->quoteName('name') . ', '
			. $db->quoteName('system_id') . ' as system, '
			. $db->quoteName('type_id') . ' as type, '
			. $db->quoteName('start') . ', '
			. $db->quoteName('end') . ', '
			. $db->quoteName('fft') . ', '
			. $db->quoteName('overlap') . ', '
			. $db->quoteName('color_min') . ' as colorMin, '
			. $db->quoteName('color_max') . ' as colorMax, '
			. $db->quoteName('comments')
		);
		$campaign_query->from($db->quoteName('manual_counting_campaign'));
		$campaign_query->where(
			$db->quoteName('id') . ' = ' . $db->quote($campaign_id)
		);

		$db->setQuery($campaign_query);

		// try to execute the query and return the result
		try {
			return $db->loadObjectList();
		} catch (RuntimeException $e) {
			// if it fails, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	/**
	 * Function inserts a new campaign into the database. The attributes of the new
	 * value are given as argument ($campaign_info)
	 *
	 * @param $campaign_info    array               array with the attributes of the new campaign
	 * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
	 *
	 * @since 0.0.1
	 */
	public function newCampaign($campaign_info) {
		// if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$campaign_query = $db->getQuery(true);

		// query to insert a new campaign with data being the $campaign_info arg
		$campaign_query
			->insert($db->quoteName('manual_counting_campaign'))
			->columns(
				$db->quoteName(
					array(
						'name',
						'system_id',
						'type_id',
						'start',
						'end',
						'fft',
						'overlap',
						'color_min',
						'color_max',
						'comments'
					)
				)
			)
			->values(
				$db->quote($campaign_info['name']) . ', '
				. $db->quote($campaign_info['system']) . ', '
				. $db->quote($campaign_info['type']) . ', '
				. $db->quote($campaign_info['start']) . ', '
				. $db->quote($campaign_info['end']) . ', '
				. $db->quote($campaign_info['fft']) . ', '
				. $db->quote($campaign_info['overlap']) . ', '
				. $db->quote($campaign_info['colorMin']) . ', '
				. $db->quote($campaign_info['colorMax']) . ', '
				. $db->quote($campaign_info['comments'])
			);

		$db->setQuery($campaign_query);

		// try to execute the query and return the result
		try {
			return $db->execute();
		} catch (RuntimeException $e) {
			// on fail, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	/**
	 * Function updates a campaign from the database with values from the
	 * $campaign_info argument.
	 *
	 * @param $campaign_info    array               array with the attributes of the modified campaign
	 * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
	 *
	 * @since 0.0.1
	 */
	public function updateCampaign($campaign_info) {
		// if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$campaign_query = $db->getQuery(true);
		// attributes to update with their new values
		$fields = array(
			$db->quoteName('name')      . ' = ' . $db->quote($campaign_info['name']),
			$db->quoteName('system_id') . ' = ' . $db->quote($campaign_info['system']),
			$db->quoteName('type_id')   . ' = ' . $db->quote($campaign_info['type']),
			$db->quoteName('start')     . ' = ' . $db->quote($campaign_info['start']),
			$db->quoteName('end')       . ' = ' . $db->quote($campaign_info['end']),
			$db->quoteName('fft')       . ' = ' . $db->quote($campaign_info['fft']),
			$db->quoteName('overlap')   . ' = ' . $db->quote($campaign_info['overlap']),
			$db->quoteName('color_min') . ' = ' . $db->quote($campaign_info['colorMin']),
			$db->quoteName('color_max') . ' = ' . $db->quote($campaign_info['colorMax']),
			$db->quoteName('comments')  . ' = ' . $db->quote($campaign_info['comments'])
		);

		// location to be updated
		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($campaign_info['id'])
		);

		// update query
		$campaign_query
			->update($db->quoteName('location'))
			->set($fields)
			->where($conditions);

		$db->setQuery($campaign_query);

		// trying to execute the query and return the result
		try {
			return $db->execute();
		} catch (RuntimeException $e) {
			// on fail, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}
}
