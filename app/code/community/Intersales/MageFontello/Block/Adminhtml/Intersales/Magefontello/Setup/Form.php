<?php
	/**
	 * Adminhtml setup form
	 *
	 * @category   Intersales
	 * @package    Intersales_MageFontello
	 * @author     Daniel Rose <dr@intersales.de>
	 */
	class Intersales_MageFontello_Block_Adminhtml_Intersales_Magefontello_Setup_Form extends Mage_Adminhtml_Block_Widget_Form {

		protected $currentStoreId;

		/**
		 * Class constructor
		 *
		 */
		protected function _construct() {
			parent::_construct();

			if(Mage::registry('current_store_id')) {
				$this->currentStoreId = Mage::registry('current_store_id');
			} else {
				$this->currentStoreId = Mage::app()->getDefaultStoreView()->getId();
			}

		}

		/**
		 * Prepare form for render
		 */
		protected function _prepareLayout() {
			parent::_prepareLayout();

			$form = new Varien_Data_Form(array(
				'id'		=> 'setupForm',
    			'action'	=> $this->getUrl('*/*/run'),
    			'method'	=> 'post',
				'enctype'	=> 'multipart/form-data'
			));

			$fieldset = $form->addFieldset('info_fieldset', array(
				'legend'	=> Mage::helper('magefontello')->__('General Information'),
				'comment'	=> 'This tool lets you combine icon webfonts for your own project. With fontello you can: shrink glyph collections, minimizing font size merge symbols from several fonts into a single file access large sets of professional-grade open source icons Now it\'s trivial to make a custom icon webfont, exactly for your needs. First, select the icons you like. Then update glyph codes (optional), and download your webfont bundle. We generate everything you need, ready for publishing on your website!'
			));

			$fieldset = $form->addFieldset('base_fieldset', array(
				'legend'	=> Mage::helper('magefontello')->__('Fontello Configuration')
			));

			$isRequired = true;
			$note = Mage::helper('magefontello')->__('Please upload a valid config file!');

			if(file_exists(Mage::getBaseDir('media') . DS . 'magefontello' . DS . $this->currentStoreId .  DS .'config.json')) {
				$isRequired = false;
				$note = Mage::helper('magefontello')->__('Current config file: %s', 'media' . DS . 'magefontello' . DS . $this->currentStoreId . DS . 'config.json');
			}

			$fieldset->addField('store_view', 'select', array(
				'label'		=> Mage::helper('magefontello')->__('Store View'),
				'name'		=> 'store_view',
				'required'	=> true,
				'class'		=> 'required-entry',
				'values'	=> $this->_getOptionsForStoreViewSwitcher(),
				'value'		=> $this->getUrl('*/*/*', array('current_store_id' => $this->currentStoreId)),
				'onchange'	=> 'location.href=this.options[this.selectedIndex].value'
			));

			$fieldset->addField('store_id', 'hidden', array(
				'name'		=> 'store_id',
				'value'		=> $this->currentStoreId
			));

			$fieldset->addField('config', 'file', array(
				'label'		=> Mage::helper('magefontello')->__('Config File'),
				'name'		=> 'config',
				'note'		=> $note,
				'required'	=> $isRequired,
				'class'		=> $isRequired ? 'required-entry' : ''
			));

			$form->setUseContainer(true);
			$form->setId('setupForm');
			$form->setAction($this->getUrl('*/*/run'));
			$this->setForm($form);
		}

		protected function _getOptionsForStoreViewSwitcher() {
			$options = array();

			foreach (Mage::app()->getStores() as $storeId => $store) {
				$options[] = array(
					'label'	=> $store->getName(),
					'value'	=> $this->getUrl('*/*/*', array('current_store_id' => $storeId))
				);
			}

			return $options;
		}
	}
?>