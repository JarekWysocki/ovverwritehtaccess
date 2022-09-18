<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * TODO:
 *  - Wcięcia w kodzie
 *  - Sensowne nazwy zmiennych
 *  - Popraw błędy (phpstorm podkresla na czerwono)
 *  - HTML wyciagnac do templatki
 *  - Poprawić sprawdzanie czy formularz został wysłany bo $_POST['name'] itp. może być gdzieś użyte i odpali sie ten moduł
 *  - Jakas informacja dla użytkownika jesli plik .htpasswd istnieje
 *  - Dodac komentarze w kodzie
 *  - Wyrzucic .htpasswd z repo
 *  - Przeczytać: https://en.wikipedia.org/wiki/Single-responsibility_principle
 */
class WebixaOverrideHtaccess extends Module
{

    public function __construct()
    {
        $this->name = 'webixaoverridehtaccess';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Webixa';
        $this->need_instance = true;

        parent::__construct();

        $this->displayName = $this->l('Nadpisywanie pliku htaccess');
        $this->description = $this->l('');
        $this->ps_versions_compliancy = array('min' => '1.7.3', 'max' => _PS_VERSION_);
    }

    public function uninstall()
    {
        $this->deleteOverride();
        return parent::uninstall();
    }

    public function getContent()
    {
        $myserver = $_SERVER['DOCUMENT_ROOT'];
        $linkToHtpasswd = $myserver.'/.htpasswd';
        if (!empty($_POST['htaccessName']) && !empty($_POST['htaccessPassword'])) {
            $name = $_POST['htaccessName'];
            $password = $_POST['htaccessPassword'];
            $hashedPassword = base64_encode(sha1($password, true));
            // ovverride .htpasswd file
            $HtpasswdFile = fopen($linkToHtpasswd, 'w');
            fwrite($HtpasswdFile, $name.':{SHA}'.$hashedPassword);
            fclose($HtpasswdFile);
            // ovverride .htaccess file
            $htaccessFile = fopen($myserver."/.htaccess", 'r');
            $valid = false;
            while (($buffer = fgets($htaccessFile)) !== false) {
                if (strstr($buffer, 'Require valid-user')) {
                    $valid = true;
                }
            }
            if($valid == false) {
                $changeHtaccessFile = fopen($myserver."/.htaccess", "a+");
                fwrite($changeHtaccessFile, '
AuthName "Forbidden"
AuthType Basic
AuthUserFile '.$linkToHtpasswd.'
Require valid-user');
                fclose($changeHtaccessFile);
            }
            fclose($htaccessFile);
        }
        
        $this->context->smarty->assign(['htpasswdExist' => file_exists($linkToHtpasswd)]);
        return  $this->display(__FILE__, 'views/templates/admin/form.tpl');
    }
    public function deleteOverride()
    {
        $myserver = $_SERVER['DOCUMENT_ROOT'];
        $linkToHtaccess = $myserver.'/.htaccess';
        //clear .htaccess statements for use password
        $currentHtaccess = file_get_contents($linkToHtaccess);
        $newHtaccess = str_replace('AuthName "Forbidden"
AuthType Basic
AuthUserFile '.$myserver.'/.htpasswd
Require valid-user',"",$currentHtaccess);
        $htaccessFile = fopen($myserver . "/.htaccess", "w+");
        fwrite($htaccessFile, $newHtaccess);
        fclose($htaccessFile);
        unlink($myserver.'/.htpasswd'); //remove .htpasswd file
    }

}



