<?php
	/**
	 * MageFontello observer
	 *
	 * @category   Intersales
	 * @package    Intersales_ProductFeeds
	 * @author     Daniel Rose <dr@intersales.de>
	 */
	class Intersales_MageFontello_Model_Observer {
		/**
		 * Add css files before layout prepare
		 *
		 * @param   Varien_Event_Observer $observer
		 * @return  Intersales_MageFontello_Model_Observer
		 */
		public function addCssToHead($observer) {
			if($observer && ($event = $observer->getEvent())) {
				$block = $event->getBlock();

				if($block instanceof Mage_Page_Block_Html_Head) {
					$subDirectory = Mage::app()->getStore();
					$fontelloCssPath = 'media' . DS . 'magefontello' . DS . $subDirectory->getId() . DS . 'fontello' . DS . 'css' . DS;
					
					$animationCssFile = $fontelloCssPath  . 'animation.css';
					$fontelloCssFile = $fontelloCssPath  . 'fontello.css';


					if(file_exists(Mage::getBaseDir('base') .DS . $animationCssFile) && file_exists(Mage::getBaseDir('base') .DS . $fontelloCssFile)) {
						$block->addItem('js_css', '..' . DS . $animationCssFile);
						$block->addItem('js_css', '..' . DS . $fontelloCssFile);
					}
				}
			}
		}
	}
?>