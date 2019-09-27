<?php
/**
 *
 * HTML5 Chat
 *
 * @copyright (c) 2019, toxyy, https://github.com/toxyy
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace toxyy\html5chat\controller;

use Symfony\Component\DependencyInjection\Container;

class main
{
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;
	/** @var \phpbb\group\helper */
	protected $group_helper;
	/** @var \phpbb\controller\helper */
	protected $helper;
	/** @var \phpbb\path_helper */
	protected $path_helper;
	/** @var string */
	protected $php_ext;

	/**
	 * Controller constructor.
	 *
	 * @param \phpbb\config\config				$config
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 * @param \phpbb\groupposition\teampage	$groupposition
	 * @param \phpbb\group\helper				$group_helper
	 * @param \phpbb\controller\helper			$helper
	 * @param \phpbb\path_helper				$path_helper
	 * @param string							$php_ext
	 *
	 * @return void
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\user	$user,
		\phpbb\groupposition\teampage $groupposition,
		\phpbb\group\helper	$group_helper,
		\phpbb\controller\helper $helper,
		\phpbb\path_helper $path_helper,
		$php_ext
	)
	{
		$this->config 			= $config;
		$this->template 		= $template;
		$this->user 			= $user;
		$this->groupposition	= $groupposition;
		$this->group_helper		= $group_helper;
		$this->helper 			= $helper;
		$this->path_helper 		= $path_helper;
		$this->root_path 		= $this->path_helper->get_web_root_path();
		$this->php_ext 			= $php_ext;
	}

	public function handle()
	{
		$user_vars = [
		    'U_USERNAME'=>'',
		    'U_AVATAR'=>'',
		    'U_GROUP_NAME'=>'guest',
        ];
		if($this->user->data['is_registered'])
		{
			$group_id = $this->user->data['group_id'];
			$group_row = $this->groupposition->get_group_values($group_id);
			$user_vars = [
				'U_USERNAME' 				=> $this->user->data['username'],
				'U_AVATAR'   				=> generate_board_url(true)."/{$this->root_path}download/file.$this->php_ext?avatar={$this->user->data['user_avatar']}",
                'U_HAS_AVATAR' 				=> $this->user->data['user_avatar']!='',
				'U_AVATAR_WIDTH'			=> $this->user->data['user_avatar_width'],
				'U_AVATAR_HEIGHT'			=> $this->user->data['user_avatar_height'],
				'U_GROUP_NAME'   			=> $this->group_helper->get_name($group_row['group_name'])
			];
		}

        $json = json_encode([
            'username'  =>  $user_vars['U_USERNAME'],
            'password'  =>  $this->config['html5chat_chat_password'],
            'gender'    =>  'male',
            'role'      =>  $user_vars['U_GROUP_NAME'],
            'image'     =>  base64_encode($user_vars['U_AVATAR']),
            'profile'   =>  ''
            ]);
        $jwt = file_get_contents("https://jwt.html5-chat.com/protect/".base64_encode($json));

		$this->template->assign_block_vars('htmlchatvars', [
			'HTML5CHAT_WEBMASTER_ID'	=> $this->config['html5chat_webmaster_id'],
			'HTML5CHAT_CHAT_PASSWORD' 	=> $this->config['html5chat_chat_password'],
			'HTML5CHAT_WIDTH' 			=> (int) $this->config['html5chat_width'],
			'HTML5CHAT_HEIGHT' 			=> (int) $this->config['html5chat_height'],
			'HTML5CHAT_FULLSCREEN' 		=> (int) $this->config['html5chat_fullscreen'],
			'HTML5CHAT_JWT' 		    => $jwt,
			'HTML5CHAT_JSON' 		    => $json,
			'HTML5CHAT_SCRIPT' 		    => "https://html5-chat.com/script/".$this->config['html5chat_webmaster_id']."/$jwt"


		] + $user_vars);

		return $this->helper->render("index.html");
	}
}
