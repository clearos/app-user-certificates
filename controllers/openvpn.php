<?php

/**
 * User OpenVPN configuration controller.
 *
 * @category   Apps
 * @package    User_Certificates
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/user_certificates/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\openvpn\OpenVPN as OpenVPN_Server;
use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * User OpenVPN configuration controller.
 *
 * @category   Apps
 * @package    User_Certificates
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/user_certificates/
 */

class OpenVPN extends ClearOS_Controller
{
    /**
     * User OpenVPN configuration default controller.
     *
     * @return view
     */

    function index()
    {
        // Bail if accounts not configured
        //--------------------------------

        $this->load->module('accounts/status');

        if ($this->status->unhappy())
            return;

        // Bail if root
        //-------------

        $username = $this->session->userdata('username');

        if ($username === 'root') {
            $this->page->view_form('root_warning', $data, lang('user_certificates_app_name'));
            return;
        }

        // Bail if OpenVPN is not installed
        //---------------------------------

        if (!clearos_app_installed('openvpn'))
            return;

        // Load libraries
        //---------------

        $this->lang->load('openvpn');
        $this->load->library('openvpn/OpenVPN');
        $this->load->library('user_agent');

        // Handle form submit
        //-------------------

        if ($this->input->post('submit')) {
            $host = $this->openvpn->get_server_hostname();
            $config = $this->openvpn->get_client_configuration(
                $this->input->post('configuration'),
                $this->session->userdata('username') 
            );

            $tempfile = CLEAROS_TEMP_DIR . '/' . $host . '.ovpn';
            file_put_contents($tempfile, $config);

            clearstatcache();

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($tempfile));
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . preg_replace('/.*\//', '', $tempfile . ';'));

            echo file_get_contents($tempfile);

            // redirect('/user_certificates/openvpn');
        }

        // Load the view data 
        //------------------- 

        try {
            $data['configurations'] = $this->openvpn->get_client_types();

            $platform = $this->agent->platform();

            if (preg_match('/Linux/i', $platform))
                $data['configuration'] = OpenVPN_Server::TYPE_OS_LINUX;
            elseif (preg_match('/Mac.*OS/i', $platform))
                $data['configuration'] = OpenVPN_Server::TYPE_OS_MACOS;
            else
                $data['configuration'] = OpenVPN_Server::TYPE_OS_WINDOWS;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load the views
        //---------------

        $this->page->view_form('openvpn', $data, lang('openvpn_app_name'));
    }
}
