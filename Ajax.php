<?php
namespace Undefined\Core;

/**
 * Set Ajax Function
 *
 * @name Ajax
 * @since 1.0.0
 * @package Undefined\Core
 */
class Ajax
{
    protected $_ajaxFunctions = [];

    public function __construct()
    {
        if ((!defined('DOING_AJAX') || !DOING_AJAX || $GLOBALS['pagenow'] != 'admin-ajax.php') || (empty($_GET['is_ajax']) && empty($_POST['is_ajax']))) {
            return;
        }

        if (in_array($_REQUEST['action'], $this->_ajaxFunctions)) {
            foreach ($this->_ajaxFunctions as $function) {
                add_action('wp_ajax_' . $function, array(&$this, $function));
                add_action('wp_ajax_nopriv_' . $function, array(&$this, $function));
            }
        } else {
            $this->_getErrorMessage();
        }
    }

    /**
     * Retrieve Ajax Functions
     * @return array
     */
    public function getAjaxFunctions()
    {
        return $this->_ajaxFunctions;
    }

    /**
     * Get Error Message
     */
    protected function _getErrorMessage(){
        wp_send_json_error(
            \Error::_getError('Alors on triche ?')
        );
    }

    /**
     * Upload file from ajax
     */
    protected function _uploadFiles(){
        $files = [];

        foreach($_FILES as $name => $file){
            $fileO = \ProjectFunctions::uploadFile($name);
            $fileO['filename'] = basename($fileO['file']);
            $files[] = $fileO;
        }

        wp_send_json_success($files);
    }

}

