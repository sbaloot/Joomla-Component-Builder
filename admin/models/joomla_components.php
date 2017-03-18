<?php
/*--------------------------------------------------------------------------------------------------------|  www.vdm.io  |------/
    __      __       _     _____                 _                                  _     __  __      _   _               _
    \ \    / /      | |   |  __ \               | |                                | |   |  \/  |    | | | |             | |
     \ \  / /_ _ ___| |_  | |  | | _____   _____| | ___  _ __  _ __ ___   ___ _ __ | |_  | \  / | ___| |_| |__   ___   __| |
      \ \/ / _` / __| __| | |  | |/ _ \ \ / / _ \ |/ _ \| '_ \| '_ ` _ \ / _ \ '_ \| __| | |\/| |/ _ \ __| '_ \ / _ \ / _` |
       \  / (_| \__ \ |_  | |__| |  __/\ V /  __/ | (_) | |_) | | | | | |  __/ | | | |_  | |  | |  __/ |_| | | | (_) | (_| |
        \/ \__,_|___/\__| |_____/ \___| \_/ \___|_|\___/| .__/|_| |_| |_|\___|_| |_|\__| |_|  |_|\___|\__|_| |_|\___/ \__,_|
                                                        | |                                                                 
                                                        |_| 				
/-------------------------------------------------------------------------------------------------------------------------------/

	@version		@update number 96 of this MVC
	@build			17th February, 2017
	@created		6th May, 2015
	@package		Component Builder
	@subpackage		joomla_components.php
	@author			Llewellyn van der Merwe <http://vdm.bz/component-builder>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	Builds Complex Joomla Components 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * Joomla_components Model
 */
class ComponentbuilderModelJoomla_components extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
        {
			$config['filter_fields'] = array(
				'a.id','id',
				'a.published','published',
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'a.system_name','system_name',
				'a.name_code','name_code',
				'a.component_version','component_version',
				'a.short_description','short_description',
				'a.companyname','companyname',
				'a.author','author'
			);
		}

		parent::__construct($config);
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}
		$system_name = $this->getUserStateFromRequest($this->context . '.filter.system_name', 'filter_system_name');
		$this->setState('filter.system_name', $system_name);

		$name_code = $this->getUserStateFromRequest($this->context . '.filter.name_code', 'filter_name_code');
		$this->setState('filter.name_code', $name_code);

		$component_version = $this->getUserStateFromRequest($this->context . '.filter.component_version', 'filter_component_version');
		$this->setState('filter.component_version', $component_version);

		$short_description = $this->getUserStateFromRequest($this->context . '.filter.short_description', 'filter_short_description');
		$this->setState('filter.short_description', $short_description);

		$companyname = $this->getUserStateFromRequest($this->context . '.filter.companyname', 'filter_companyname');
		$this->setState('filter.companyname', $companyname);

		$author = $this->getUserStateFromRequest($this->context . '.filter.author', 'filter_author');
		$this->setState('filter.author', $author);
        
		$sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
		$this->setState('filter.sorting', $sorting);
        
		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);
        
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
		$this->setState('filter.created_by', $created_by);

		$created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
		$this->setState('filter.created', $created);

		// List state information.
		parent::populateState($ordering, $direction);
	}
	
	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{ 
		// check in items
		$this->checkInNow();

		// load parent items
		$items = parent::getItems();  
        
		// return items
		return $items;
	}
	
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Get the user object.
		$user = JFactory::getUser();
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('a.*');

		// From the componentbuilder_item table
		$query->from($db->quoteName('#__componentbuilder_joomla_component', 'a'));

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}
		// Implement View Level Access
		if (!$user->authorise('core.options', 'com_componentbuilder'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where('(a.system_name LIKE '.$search.' OR a.name_code LIKE '.$search.' OR a.short_description LIKE '.$search.' OR a.companyname LIKE '.$search.' OR a.author LIKE '.$search.' OR a.name LIKE '.$search.')');
			}
		}

		// Filter by Companyname.
		if ($companyname = $this->getState('filter.companyname'))
		{
			$query->where('a.companyname = ' . $db->quote($db->escape($companyname)));
		}
		// Filter by Author.
		if ($author = $this->getState('filter.author'))
		{
			$query->where('a.author = ' . $db->quote($db->escape($author)));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'asc');	
		if ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	* Method to get list export data.
	*
	* @return mixed  An array of data items on success, false on failure.
	*/
	public function getExportData($pks)
	{
		// setup the query
		if (ComponentbuilderHelper::checkArray($pks))
		{
			// Set a value to know this is exporting method.
			$_export = true;
			// Get the user object.
			$user = JFactory::getUser();
			// Create a new query object.
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			// Select some fields
			$query->select('a.*');

			// From the componentbuilder_joomla_component table
			$query->from($db->quoteName('#__componentbuilder_joomla_component', 'a'));
			$query->where('a.id IN (' . implode(',',$pks) . ')');
			// Implement View Level Access
			if (!$user->authorise('core.options', 'com_componentbuilder'))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}

			// Order the results by ordering
			$query->order('a.ordering  ASC');

			// Load the items
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				$items = $db->loadObjectList();

				// Get the basic encription key.
				$basickey = ComponentbuilderHelper::getCryptKey('basic');
				// Get the encription object.
				$basic = new FOFEncryptAes($basickey, 128);

				// set values to display correctly.
				if (ComponentbuilderHelper::checkArray($items))
				{
					foreach ($items as $nr => &$item)
					{
						// decode css
						$item->css = base64_decode($item->css);
						// decode php_postflight_update
						$item->php_postflight_update = base64_decode($item->php_postflight_update);
						if ($basickey && !is_numeric($item->update_server_ftp) && $item->update_server_ftp === base64_encode(base64_decode($item->update_server_ftp, true)))
						{
							// decrypt update_server_ftp
							$item->update_server_ftp = $basic->decryptString($item->update_server_ftp);
						}
						// decode php_preflight_update
						$item->php_preflight_update = base64_decode($item->php_preflight_update);
						// decode readme
						$item->readme = base64_decode($item->readme);
						if ($basickey && !is_numeric($item->sales_server_ftp) && $item->sales_server_ftp === base64_encode(base64_decode($item->sales_server_ftp, true)))
						{
							// decrypt sales_server_ftp
							$item->sales_server_ftp = $basic->decryptString($item->sales_server_ftp);
						}
						// decode php_preflight_install
						$item->php_preflight_install = base64_decode($item->php_preflight_install);
						// decode php_postflight_install
						$item->php_postflight_install = base64_decode($item->php_postflight_install);
						// decode php_method_uninstall
						$item->php_method_uninstall = base64_decode($item->php_method_uninstall);
						if ($basickey && !is_numeric($item->whmcs_key) && $item->whmcs_key === base64_encode(base64_decode($item->whmcs_key, true)))
						{
							// decrypt whmcs_key
							$item->whmcs_key = $basic->decryptString($item->whmcs_key);
						}
						// decode php_helper_both
						$item->php_helper_both = base64_decode($item->php_helper_both);
						// decode php_helper_admin
						$item->php_helper_admin = base64_decode($item->php_helper_admin);
						// decode php_admin_event
						$item->php_admin_event = base64_decode($item->php_admin_event);
						// decode php_helper_site
						$item->php_helper_site = base64_decode($item->php_helper_site);
						// decode php_site_event
						$item->php_site_event = base64_decode($item->php_site_event);
						// decode sql
						$item->sql = base64_decode($item->sql);
						// decode php_dashboard_methods
						$item->php_dashboard_methods = base64_decode($item->php_dashboard_methods);
						// decode buildcompsql
						$item->buildcompsql = base64_decode($item->buildcompsql);
						// unset the values we don't want exported.
						unset($item->asset_id);
						unset($item->checked_out);
						unset($item->checked_out_time);
					}
				}
				// Add headers to items array.
				$headers = $this->getExImPortHeaders();
				if (ComponentbuilderHelper::checkObject($headers))
				{
					array_unshift($items,$headers);
				}
				return $items;
			}
		}
		return false;
	}

	/**
	* Method to get header.
	*
	* @return mixed  An array of data items on success, false on failure.
	*/
	public function getExImPortHeaders()
	{
		// Get a db connection.
		$db = JFactory::getDbo();
		// get the columns
		$columns = $db->getTableColumns("#__componentbuilder_joomla_component");
		if (ComponentbuilderHelper::checkArray($columns))
		{
			// remove the headers you don't import/export.
			unset($columns['asset_id']);
			unset($columns['checked_out']);
			unset($columns['checked_out_time']);
			$headers = new stdClass();
			foreach ($columns as $column => $type)
			{
				$headers->{$column} = $column;
			}
			return $headers;
		}
		return false;
	} 
	
	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.system_name');
		$id .= ':' . $this->getState('filter.name_code');
		$id .= ':' . $this->getState('filter.component_version');
		$id .= ':' . $this->getState('filter.short_description');
		$id .= ':' . $this->getState('filter.companyname');
		$id .= ':' . $this->getState('filter.author');

		return parent::getStoreId($id);
	}

	/**
	* Build an SQL query to checkin all items left checked out longer then a set time.
	*
	* @return  a bool
	*
	*/
	protected function checkInNow()
	{
		// Get set check in time
		$time = JComponentHelper::getParams('com_componentbuilder')->get('check_in');
		
		if ($time)
		{

			// Get a db connection.
			$db = JFactory::getDbo();
			// reset query
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__componentbuilder_joomla_component'));
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				// Get Yesterdays date
				$date = JFactory::getDate()->modify($time)->toSql();
				// reset query
				$query = $db->getQuery(true);

				// Fields to update.
				$fields = array(
					$db->quoteName('checked_out_time') . '=\'0000-00-00 00:00:00\'',
					$db->quoteName('checked_out') . '=0'
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('checked_out') . '!=0', 
					$db->quoteName('checked_out_time') . '<\''.$date.'\''
				);

				// Check table
				$query->update($db->quoteName('#__componentbuilder_joomla_component'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}

		return false;
	}
}
