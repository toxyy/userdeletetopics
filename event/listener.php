<?php
/**
*
* phpBB Extension - toxyy User Delete Topics
* @copyright (c) 2019 toxyy <thrashtek@yahoo.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace toxyy\userdeletetopics\event;

/**
* Event listener
*/

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{       /** @var \phpbb\user */
        protected $user;
        /** @var \phpbb\auth\auth */
        protected $auth;

        /**
        * Constructor
        *
        * @param \phpbb\user                                            $user
        * @param \phpbb\auth\auth                                       $auth
        *
        */
        public function __construct(
                \phpbb\user $user,
                \phpbb\auth\auth $auth
        )
        {
                $this->user                                             = $user;
                $this->auth                                             = $auth;
        }

        static public function getSubscribedEvents()
	{
                return [
                        'core.user_setup'                               => 'core_user_setup',
                        'core.permissions'                              => 'core_permissions',
                        'core.viewtopic_add_quickmod_option_before'     => 'viewtopic_add_quickmod_option_before',
                        'core.mcp_modify_permissions'                   => 'mcp_modify_permissions',
                        'core.mcp_delete_topic_modify_permissions'      => 'mcp_delete_topic_modify_permissions',
                        'core.mcp_delete_topic_modify_hidden_fields'    => 'mcp_delete_topic_modify_hidden_fields',
		];
	}

        public function core_user_setup($event)
        {
                $lang_set_ext = $event['lang_set_ext'];
                $lang_set_ext[] = [
                        'ext_name' => 'toxyy/userdeletetopics',
                        'lang_set' => 'user_delete_topics_acp',
                ];
                $event['lang_set_ext'] = $lang_set_ext;
        }

        // add permissions to acp
        public function core_permissions($event)
        {
                $event['permissions'] = ['f_deletetopic' => ['lang' => 'UDT_PERMISSIONS', 'cat' => 'actions']] + $event['permissions'];
        }

        // add delete topic option to quickmod tools in viewtopic if the current user is the topic poster
        public function viewtopic_add_quickmod_option_before($event)
        {       // $event['quickmod_array']['delete_topic'][1] holds the delete_topic bool value
                $can_delete_own_topic = ($this->auth->acl_get('f_deletetopic', $event['forum_id']) && ($event['topic_data']['topic_poster'] == $this->user->data['user_id']));
                $event['quickmod_array'] = [
                        'delete_topic' => ['DELETE_TOPIC', $event['quickmod_array']['delete_topic'][1] || $can_delete_own_topic]
                ] + $event['quickmod_array'];
        }

        // if the user and topic poster are the same and we have permissions to delete the topic, then allow this user to use mcp functions
        public function mcp_modify_permissions($event)
        {       // only checks for one topic, since this is for normal users deleting their own topics. no real mcp to delete multile topics
                if($this->auth->acl_get('f_deletetopic', $event['forum_id']) && ($event['topic_info'][array_keys($event['topic_info'])[0]]['topic_poster'] == $this->user->data['user_id'])) $event['allow_user'] = true;
        }

        // if the user has permission to delete his own topics then dont check for permissions
        public function mcp_delete_topic_modify_permissions($event)
        {
                if($this->auth->acl_get('f_deletetopic', $event['forum_id'])) $event['check_permission'] = false;
        }

        // if this topic is going to be permanently deleted,
        public function mcp_delete_topic_modify_hidden_fields($event)
        {       // do we even have permission to permanently delete? (should run for only normal users, if not will replace with is_staff code)
                if(!$this->auth->acl_get('m_delete', $event['forum_id']))
                {       // change the confirm message to remove the _PERMANENT suffix
                        if($this->auth->acl_get('f_deletetopic', $event['forum_id']) && $event['only_softdeleted'])
                                $event['l_confirm'] = (count($event['topic_ids']) == 1) ? 'DELETE_TOPIC' : 'DELETE_TOPICS';

                        // don't delete permanently as we don't have permissions
                        $event['s_hidden_fields'] = ['delete_permanent' => '0'] + $event['s_hidden_fields'];
                }
        }
}
