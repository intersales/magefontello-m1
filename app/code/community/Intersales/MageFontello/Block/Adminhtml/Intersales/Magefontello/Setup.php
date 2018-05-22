<?php
	/**
	 * Setup block
	 *
	 * @category   Intersales
	 * @package    Intersales_MageFontello
	 * @author     Daniel Rose <dr@intersales.de>
	 */
	class Intersales_MageFontello_Block_Adminhtml_Intersales_Magefontello_Setup extends Mage_Adminhtml_Block_Widget_Form_Container {
		public function __construct() {
			$this->_blockGroup = 'magefontello';
			$this->_controller = 'adminhtml_intersales_magefontello';
			$this->_mode = 'setup';

			parent::__construct();
			
			$this->_updateButton('save', 'label', Mage::helper('magefontello')->__('Run'));
			$this->_updateButton('save', 'onclick', 'setupForm.submit();');
			$this->_removeButton('delete');
			$this->_removeButton('back');
			$this->_removeButton('reset');
		}

		public function getHeaderText() {
			return Mage::helper('magefontello')->__('MageFontello Setup');
		}
	}
?>