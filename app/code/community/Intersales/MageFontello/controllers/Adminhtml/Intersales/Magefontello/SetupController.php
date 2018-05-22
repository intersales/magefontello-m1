<?php
	/**
	 * Setup controller
	 *
	 * @category   Intersales
	 * @package    Intersales_MageFontello
	 * @author     Daniel Rose <dr@intersales.de>
	 */
	class Intersales_MageFontello_Adminhtml_Intersales_Magefontello_SetupController extends Mage_Adminhtml_Controller_Action {
		
		protected function _initGroup() {
			$this->_title($this->__('InterSales Modules'))->_title($this->__('ProductFeeds'))->_title($this->__('Manage Feeds'));
		}

		/**
		 * Init setup form
		 */
		public function indexAction() {
			if (($currentStoreId = $this->getRequest()->getParam('current_store_id'))) {
				Mage::register('current_store_id', $currentStoreId);
			}

			$this->_title($this->__('InterSales Modules'))->_title($this->__('MageFontello'))->_title($this->__('Setup'));
			$this->loadLayout();
			$this->_setActiveMenu('intersales_modules/mage_fontello/setup');
			$this->_addBreadcrumb(Mage::helper('magefontello')->__('InterSales Modules'), Mage::helper('magefontello')->__('InterSales Modules'));
			$this->_addBreadcrumb(Mage::helper('magefontello')->__('MageFontello'), Mage::helper('magefontello')->__('MageFontello'));
			$this->_addBreadcrumb(Mage::helper('magefontello')->__('Setup'), Mage::helper('magefontello')->__('Setup'));	
			$this->renderLayout();
		}

		/**
		 * Callback action
		 */
		public function callbackAction() {
			try {
				$this->_downloadFontelloArchive();
				$this->_extractFontelloArchive();
				$this->_removeFontelloFiles();
				$this->_moveFontelloFiles();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magefontello')->__('Setup is completed!'));
			} catch(Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

			$this->loadLayout();
			$this->renderLayout();
		}

		protected function _moveFontelloFiles() {
			$subDirectory = Mage::getSingleton('adminhtml/session')->getData('mage_fontello_sub_directory_name');

			foreach (glob(Mage::getBaseDir('media') . DS . 'magefontello' . DS . 'fontello-*' . DS)  as $index => $path) {
				rename($path, Mage::getBaseDir('media') . DS . 'magefontello' . DS . $subDirectory . DS . 'fontello' . DS);
			}

			unlink(Mage::getBaseDir('media') . DS . 'magefontello' . DS . $subDirectory . DS . 'config.json');
			unlink(Mage::getBaseDir('media') . DS . 'magefontello' . DS . $subDirectory . DS . 'fontello' . DS . 'demo.html');
			unlink(Mage::getBaseDir('media') . DS . 'magefontello' . DS . 'fontello.zip');

			rename(
				Mage::getBaseDir('media') . DS . 'magefontello' . DS . $subDirectory . DS . 'fontello' . DS . 'config.json',
				Mage::getBaseDir('media') . DS . 'magefontello' . DS . $subDirectory . DS . 'config.json'
			);
		}

		protected function _downloadFontelloArchive() {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'http://fontello.com/' . Mage::getSingleton('adminhtml/session')->getData('fontello_sid') . '/get');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, false);

			$data = curl_exec($ch);
			
			if($data) {
				file_put_contents(Mage::getBaseDir('media') . DS . 'magefontello' . DS . 'fontello.zip', $data);
			} else {
				throw new Exception('Could not download fontello archive! Session id is not valid!');
			}

			curl_close($ch);
		}

		protected function _extractFontelloArchive() {
			$zip = new ZipArchive;
			
			$res = $zip->open(Mage::getBaseDir('media') . DS . 'magefontello' . DS . 'fontello.zip');
			
			if ($res === TRUE) {
				$zip->extractTo(Mage::getBaseDir('media') . DS . 'magefontello' . DS);
				$zip->close();
			} else {
				throw new Exception('Could not extract archive "fontello.zip"!');
			}
		}

		protected function _removeFontelloFiles() {
			$subDirectory = Mage::getSingleton('adminhtml/session')->getData('mage_fontello_sub_directory_name');

			$path = Mage::getBaseDir('media') . DS . 'magefontello' . DS . $subDirectory . DS .'fontello' . DS;
			if(file_exists($path)) {
				$iterator = new RecursiveDirectoryIterator($path);
					
				foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
					if ($file->isDir()) {  
						rmdir($file->getPathname());  
					} else {  
						unlink($file->getPathname());  
					}
				}
			
				rmdir($path);
			}
		}

		/**
		 * Run
		 */
		public function runAction() {
			if (($data = $this->getRequest()->getParams())) {
				try {
					if (isset($_FILES['config']['name']) && $_FILES['config']['name'] != '') {
						$this->_uploadFile('config', $data['store_id']);
					}

					$contentOfConfigFile = '@' . Mage::getBaseDir('media') . DS . 'magefontello' . DS . $data['store_id'] . DS . 'config.json';

					$ch = curl_init();
					
					curl_setopt($ch, CURLOPT_URL, 'http://fontello.com/');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, true);

					$postData = array(
						'config'	=> $contentOfConfigFile,
						'url'		=> $this->getUrl('*/*/callback')
					);

					curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
					$output = curl_exec($ch);
					curl_close($ch);

					Mage::getSingleton('adminhtml/session')->setData('fontello_sid', $output);
					Mage::getSingleton('adminhtml/session')->setData('mage_fontello_sub_directory_name', $data['store_id']);
					

					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magefontello')->__('Starting setup!'));
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magefontello')->__('Click <a href="http://fontello.com/%s" target="_blank">here</a> to choose your icons.', $output));
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}

			$this->getResponse()->setRedirect($this->getUrl('*/*/'));
		}

		/**
		 * Upload file by field name
		 */
		protected function _uploadFile($fieldName, $subDirectoryName) {
			$path = Mage::getBaseDir('media') . DS . 'magefontello' . DS . $subDirectoryName;

			$uploader = new Varien_File_Uploader($fieldName);
			$uploader->setAllowedExtensions(array('json'));
			$uploader->setAllowRenameFiles(false);
			$uploader->setFilesDispersion(false);

			$uploader->save($path, 'config.json');
		}

		protected function _isAllowed() {
			return Mage::getSingleton('admin/session')->isAllowed('intersales_modules/mage_fontello/setup');
		}
	}
?>