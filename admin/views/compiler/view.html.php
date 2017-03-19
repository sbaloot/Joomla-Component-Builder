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

	@version		@update number 25 of this MVC
	@build			16th February, 2017
	@created		1st February, 2017
	@package		Component Builder
	@subpackage		view.html.php
	@author			Llewellyn van der Merwe <http://vdm.bz/component-builder>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	Builds Complex Joomla Components 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
//defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Componentbuilder View class for the Compiler
 * edit by ehsan abbasi.
 * is expriment.
 */
class ComponentbuilderViewCompiler extends JViewLegacy
{
	// Overwriting JView display method
	/**
	 * @param null $tpl
	 *
	 * @return bool
	 *
	 * @since version
	 */
	function display($tpl = null)
	{
                // get component params
		$this->params	= JComponentHelper::getParams('com_componentbuilder');
		// get the application
		$this->app	= JFactory::getApplication();
		// get the user object
		$this->user	= JFactory::getUser();
                // get global action permissions
		$this->canDo	= ComponentbuilderHelper::getActions('compiler');
		// Initialise variables.
		$this->items	= $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode(PHP_EOL, $errors));
			return false;
		}
		if ($this->getLayout() !== 'modal')
		{
			// Include helper submenu
			ComponentbuilderHelper::addSubmenu('compiler');
			JHtmlSidebar::setAction('index.php?option=com_componentbuilder&view=compiler');
			$this->sidebar = JHtmlSidebar::render();
		}
		$this->Components 	= $this->get('Components');
		$this->form		= $this->setForm();

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			// add the tool bar
			$this->addToolBar();
		}

		// set the document
		$this->setDocument();

		parent::display($tpl);
	}

	public function setForm()
	{		
		if(ComponentbuilderHelper::checkArray($this->Components)){
			jimport('joomla.form.form');
			
			$radio1 = JFormHelper::loadFieldType('radio',true);
			// start building add to sales folder xml field
			$xml = '<field label="'.JText::_('COM_COMPONENTBUILDER_ADD_TO_BACKUP_FOLDER_AMP_SALES_SERVER_LTSMALLGTIF_SETLTSMALLGT').'" description="'.JText::_('COM_COMPONENTBUILDER_SHOULD_THE_ZIPPED_PACKAGE_OF_THE_COMPONENT_BE_MOVED_TO_THE_LOCAL_BACKUP_AND_REMOTE_SALES_SERVER_THIS_IS_ONLY_APPLICABLE_IF_THIS_COMPONENT_HAS_THOSE_VALUES_SET').'" name="backup" type="radio" class="btn-group btn-group-yesno" default="0">';
			$xml .= '<option value="1">'.JText::_('COM_COMPONENTBUILDER_YES').'</option> <option value="0">'.JText::_('COM_COMPONENTBUILDER_NO').'</option>';
			$xml .= "</field>";
			// prepare the xml
			$sales = new SimpleXMLElement($xml);
			// set components to form
			$radio1->setup($sales,0);
			
			$radio2 = JFormHelper::loadFieldType('radio',true);
			// start building add to git folder xml field
			$xml = '<field label="'.JText::_('COM_COMPONENTBUILDER_ADD_TO_GIT_FOLDER').'" description="'.JText::_('COM_COMPONENTBUILDER_SHOULD_THE_COMPONENT_BE_MOVED_TO_YOUR_LOCAL_GIT_FOLDER').'" name="git" type="radio" class="btn-group btn-group-yesno" default="1">';
			$xml .= '<option value="1">'.JText::_('COM_COMPONENTBUILDER_YES').'</option> <option value="0">'.JText::_('COM_COMPONENTBUILDER_NO').'</option>';
			$xml .= "</field>";
			// prepare the xml
			$git = new SimpleXMLElement($xml);
			// set components to form
			$radio2->setup($git,1);
			
			$radio3 = JFormHelper::loadFieldType('radio',true);
			// start building add to git folder xml field
			$xml = '<field label="'.JText::_('COM_COMPONENTBUILDER_ADD_CUSTOM_CODE_PLACEHOLDERS').'" description="'.JText::_('COM_COMPONENTBUILDER_SHOULD_JCB_INSERT_THE_CUSTOM_CODE_PLACEHOLDERS_THIS_IS_ONLY_APPLICABLE_IF_THIS_COMPONENT_HAS_CUSTOM_CODE').'" name="placeholders" type="radio" class="btn-group btn-group-yesno" default="2">';
			$xml .= '<option value="2">'.JText::_('COM_COMPONENTBUILDER_GLOBAL').'</option> <option value="1">'.JText::_('COM_COMPONENTBUILDER_YES').'</option> <option value="0">'.JText::_('COM_COMPONENTBUILDER_NO').'</option>';
			$xml .= "</field>";
			// prepare the xml
			$placeholder = new SimpleXMLElement($xml);
			// set components to form
			$radio3->setup($placeholder,2);
			
			$radio4 = JFormHelper::loadFieldType('radio',true);
			// start building add to git folder xml field
			$xml = '<field label="'.JText::_('COM_COMPONENTBUILDER_DEBUG_LINE_NUMBERS').'" description="'.JText::_('COM_COMPONENTBUILDER_ADD_CORRESPONDING_LINE_NUMBERS_TO_THE_DYNAMIC_COMMENTS_SO_TO_SEE_WHERE_IN_THE_COMPILER_THE_LINES_OF_CODE_WAS_BUILD_THIS_WILL_HELP_IF_YOU_NEED_TO_GET_MORE_TECHNICAL_WITH_AN_ISSUE_ON_GITHUB').'" name="debuglinenr" type="radio" class="btn-group btn-group-yesno" default="2">';
			$xml .= '<option value="2">'.JText::_('COM_COMPONENTBUILDER_GLOBAL').'</option> <option value="1">'.JText::_('COM_COMPONENTBUILDER_YES').'</option> <option value="0">'.JText::_('COM_COMPONENTBUILDER_NO').'</option>';
			$xml .= "</field>";
			// prepare the xml
			$debug_linenr = new SimpleXMLElement($xml);
			// set components to form
			$radio4->setup($debug_linenr,2);
			
			$list = JFormHelper::loadFieldType('list',true);
			// start building componet xml field
			$xml = '<field label="'.JText::_('COM_COMPONENTBUILDER_COMPONENTS').'" description="'.JText::_('COM_COMPONENTBUILDER_SELECT_THE_COMPONENT_TO_COMPILE').'" name="component" type="list" class="btn-group" required="true">';
			$xml .= '<option value="">'.JText::_('COM_COMPONENTBUILDER__SELECT_COMPONENT_').'</option>';
			foreach($this->Components as $componet){
				$xml .= '<option value="'.$componet->id.'">'.$this->escape($componet->name).'</option>';
			}
			$xml .= "</field>";
			// prepare the xml
			$componets = new SimpleXMLElement($xml);
			// set components to form
			$list->setup($componets,0);
						
			return array($radio1,$radio2,$radio3,$radio4,$list);
		}
		return false;
	}

        /**
	 * Prepares the document
	 */
	protected function setDocument()
	{

		// always make sure jquery is loaded.
		JHtml::_('jquery.framework');
		// Load the header checker class.
		require_once( JPATH_COMPONENT_ADMINISTRATOR.'/helpers/headercheck.php' );
		// Initialize the header checker.
		$HeaderCheck = new HeaderCheck;

		// Load uikit options.
		$uikit = $this->params->get('uikit_load');
		// Set script size.
		$size = $this->params->get('uikit_min');
		// Set css style.
		$style = $this->params->get('uikit_style');

		// The uikit css.
		if ((!$HeaderCheck->css_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
		{
			$this->document->addStyleSheet(JURI::root(true) .'/media/com_componentbuilder/uikit/css/uikit'.$style.$size.'.css');
		}
		// The uikit js.
		if ((!$HeaderCheck->js_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
		{
			$this->document->addScript(JURI::root(true) .'/media/com_componentbuilder/uikit/js/uikit'.$size.'.js');
		}

		// Load the needed uikit components in this view.
		$uikitComp = $this->get('UikitComp');
		if ($uikit != 2 && isset($uikitComp) && ComponentbuilderHelper::checkArray($uikitComp))
		{
			// load just in case.
			jimport('joomla.filesystem.file');
			// loading...
			foreach ($uikitComp as $class)
			{
				foreach (ComponentbuilderHelper::$uk_components[$class] as $name)
				{
					// check if the CSS file exists.
					if (JFile::exists(JPATH_ROOT.'/media/com_componentbuilder/uikit/css/components/'.$name.$style.$size.'.css'))
					{
						// load the css.
						$this->document->addStyleSheet(JURI::root(true) .'/media/com_componentbuilder/uikit/css/components/'.$name.$style.$size.'.css');
					}
					// check if the JavaScript file exists.
					if (JFile::exists(JPATH_ROOT.'/media/com_componentbuilder/uikit/js/components/'.$name.$size.'.js'))
					{
						// load the js.
						$this->document->addScript(JURI::root(true) .'/media/com_componentbuilder/uikit/js/components/'.$name.$size.'.js', 'text/javascript', true);
					}
				}
			}
		}   
                // add the document default css file
		$this->document->addStyleSheet(JURI::root(true) .'/administrator/components/com_componentbuilder/assets/css/compiler.css'); 
		// Set the Custom JS script to view
		$this->document->addScriptDeclaration("
			function getComponentDetails_server(id){
				var getUrl = JRouter(\"index.php?option=com_componentbuilder&task=ajax.getComponentDetails&format=json\");
				if(token.length > 0 && id > 0){
					var request = 'token='+token+'&id='+id;
				}
				return jQuery.ajax({
					type: 'GET',
					url: getUrl,
					dataType: 'jsonp',
					data: request,
					jsonp: 'callback'
				});
			}
			function getComponentDetails(id) {
				getComponentDetails_server(id).done(function(result) {
					if(result.html) {
						jQuery('#component-details').html(result.html);
					}
				});
			}
		");
        }

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// hide the main menu
                $this->app->input->set('hidemainmenu', true);
		// add title to the page
		JToolbarHelper::title(JText::_('COM_COMPONENTBUILDER_COMPILER'),'cogs');
                // add the back button
                // JToolBarHelper::custom('compiler.back', 'undo-2', '', 'COM_COMPONENTBUILDER_BACK', false);
                // add cpanel button
		JToolBarHelper::custom('compiler.dashboard', 'grid-2', '', 'COM_COMPONENTBUILDER_DASH', false);
		if ($this->canDo->get('compiler.clear_tmp'))
		{
			// add Clear tmp button.
			JToolBarHelper::custom('compiler.clearTmp', 'purge', '', 'COM_COMPONENTBUILDER_CLEAR_TMP', false);
		}

		// set help url for this view if found
                $help_url = ComponentbuilderHelper::getHelpUrl('compiler');
                if (ComponentbuilderHelper::checkString($help_url))
                {
                        JToolbarHelper::help('COM_COMPONENTBUILDER_HELP_MANAGER', false, $help_url);
                }

                // add the options comp button
                if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_componentbuilder');
		}
	}

        /**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
                // use the helper htmlEscape method instead.
		return ComponentbuilderHelper::htmlEscape($var, $this->_charset);
	}
}
