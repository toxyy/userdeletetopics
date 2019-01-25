<?php
/**
*
* phpBB Extension - toxyy User Delete Topics
* @copyright (c) 2019 toxyy <thrashtek@yahoo.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace toxyy\userdeletetopics\migrations;

class v_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['user_delete_topics']);
	}

	public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('user_delete_topics', '1')),

                        // Add permissions
			array('permission.add', array('f_deletetopic', false, 'f_softdelete')),
		);
	}
}
