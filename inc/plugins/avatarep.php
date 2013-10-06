<?php
/**
*@ Autor: Dark Neo
*@ Fecha: 2012-02-07
*@ Version: 2.5
*@ Contacto: dark.neo@hotmail.es
*/

// Inhabilitar acceso directo a este archivo
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Añadir hooks
$plugins->add_hook('build_forumbits_forum', 'forumlist_avatar');
$plugins->add_hook("forumdisplay_thread", "avatarep_thread");
$plugins->add_hook('forumdisplay_announcement', 'avatarep_announcement');
$plugins->add_hook('usercp_do_avatar_end', 'avatarep_avatar_update');

if(THIS_SCRIPT == 'modcp.php' && in_array($mybb->input['action'], array('do_new_announcement', 'do_edit_announcement'))){
$plugins->add_hook('redirect', 'avatarep_announcement_update');}

// Informacion del plugin
function avatarep_info()
{
	global $mybb, $cache, $db, $lang;

    $lang->load("avatarep", false, true);
	$avatarep_config_link = '';

	$query = $db->simple_select('settinggroups', '*', "name='avatarep'");

	if (count($db->fetch_array($query)))
	{
		$avatarep_config_link = '(<a href="index.php?module=config&action=change&search=avatarep" style="color:#035488;">'.$db->escape_string($lang->avatarep_config).'</a>)';
	}

	return array(
        "name"			=> $db->escape_string($lang->avatarep_name),
    	"description"	=> $db->escape_string($lang->avatarep_descrip) . "  " . $avatarep_config_link,
		"website"		=> "http://www.soportemybb.com",
		"author"		=> "<a href='http://www.soportemybb.com/miembro_dark-neo'>Dark Neo</a>",
		"authorsite"	=> "Dark Neo",
		"version"		=> "2.5",
		"guid" 			=> "c4f9c28c311a919b6bcf8914f61e6133",
		"compatibility" => "16*"
	);
}

//Se ejecuta al activar el plugin
function avatarep_activate() {
    //Variables que vamos a utilizar
   	global $mybb, $cache, $db, $lang, $templates;

    $lang->load("avatarep", false, true);

    // Crear el grupo de opciones
    $query = $db->simple_select("settinggroups", "COUNT(*) as rows");
    $rows = $db->fetch_field($query, "rows");

    $avatarep_groupconfig = array(
        'name' => 'avatarep',
        'title' => $db->escape_string($lang->avatarep_title),
        'description' => $db->escape_string($lang->avatarep_title_descrip),
        'disporder' => $rows+1,
        'isdefault' => 0
    );

    $group['gid'] = $db->insert_query("settinggroups", $avatarep_groupconfig);

    // Crear las opciones del plugin a utilizar
    $avatarep_config = array();

    $avatarep_config[] = array(
        'name' => 'avatarep_active',
        'title' => $db->escape_string($lang->avatarep_power),
        'description' => $db->escape_string($lang->avatarep_power_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 10,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_foros',
        'title' => $db->escape_string($lang->avatarep_forum),
        'description' => $db->escape_string($lang->avatarep_forum_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 20,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_temas',
        'title' => $db->escape_string($lang->avatarep_thread_owner),
        'description' => $db->escape_string($lang->avatarep_thread_owner_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 30,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_temas2',
        'title' =>  $db->escape_string($lang->avatarep_thread_lastposter),
        'description' => $db->escape_string($lang->avatarep_thread_lastposter_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 40,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_anuncios',
        'title' =>  $db->escape_string($lang->avatarep_thread_announcements),
        'description' => $db->escape_string($lang->avatarep_thread_announcements_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 50,
        'gid' => $group['gid']
    );
    
    $avatarep_config[] = array(
        'name' => 'avatarep_fondo',
        'title' => $db->escape_string($lang->avatarep_background),
        'description' => $db->escape_string($lang->avatarep_background_descrip),
        'optionscode' => 'textarea',
        'value' => '#FCFDFD',
        'disporder' => 60,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_marco',
        'title' => $db->escape_string($lang->avatarep_border_color),
        'description' => $db->escape_string($lang->avatarep_border_color_descrip),
        'optionscode' => 'textarea',
        'value' => '#D8DFEA',
        'disporder' => 70,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_ancho',
        'title' => $db->escape_string($lang->avatarep_width),
        'description' => $db->escape_string($lang->avatarep_width_descrip),
        'optionscode' => 'textarea',
        'value' => '30px',
        'disporder' => 80,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_alto',
        'title' => $db->escape_string($lang->avatarep_height),
        'description' => $db->escape_string($lang->avatarep_height_descrip),
        'optionscode' => 'textarea',
        'value' => '30px',
        'disporder' => 90,
        'gid' => $group['gid']
    );

    foreach($avatarep_config as $array => $content)
    {
        $db->insert_query("settings", $content);
    }

	// Creamos la cache de datos para nuestros avatares
	$query = $db->simple_select('announcements', 'uid');
	$query = $db->query("
		SELECT DISTINCT(a.uid) as uid, u.username, u.username AS userusername, u.avatar, u.usergroup, ug.namestyle
		FROM ".TABLE_PREFIX."announcements a
		LEFT JOIN ".TABLE_PREFIX."users u ON u.uid = a.uid
        LEFT JOIN ".TABLE_PREFIX."usergroups ug ON u.usergroup = ug.gid		
	");

	if($db->num_rows($query))
	{
		$inline_avatars = array();
		while($user = $db->fetch_array($query))
		{
			$inline_avatars[$user['uid']] = format_avatar($user);
		}

		$cache->update('anno_cache', $inline_avatars);
	}
	
	//Reconstruimos las opciones del archivo settings
	rebuild_settings();
	
//Creamos la hoja de estilo para el popup de los avatares...
	$query_tid = $db->write_query("SELECT tid FROM ".TABLE_PREFIX."themes WHERE def='1'");
	$themetid = $db->fetch_array($query_tid);
	$style = array(
			'name'         => 'avatarep.css',
			'tid'          => $themetid['tid'],
			'stylesheet'   => '/* CAJA Y ESTILO*/
.avatarep.popupbody {
    width: 260px;
    display: block;
    box-shadow: 0px 4px 7px #777777;
    -moz-box-shadow: 0px 4px 7px #777777;
    -webkit-box-shadow: 0px 4px 7px #777777;
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
}

.popupbody {
    background: #E9E9E9 url(images/top-highlight.png) repeat-x;
    border: 1px solid #355B4C;
    padding: 2px;
    position: absolute;
    z-index: 1000;
    margin-top: 2px;
    left: 20px;
    border-image: initial;
    opacity: 0.90;
    filter:alpha(opacity=90);
}

/*OBJETOS DEL MENU*/
.popupbody li a,
.popupbody li label {
    display: block;
    color: #3E3E3E;
    background: transparent;
    text-decoration: none;
    text-align: left;
    white-space: nowrap;
    font: normal 11px Tahoma, Calibri, Verdana, Geneva, sans-serif;
}

.popupbody li a:hover,
.popupbody li label:hover {
    background: #FFEB90;
    color: #000;
    text-decoration:none;
    cursor: pointer;
    opacity: 0.80;
    filter:alpha(opacity=80);
}

.popupbody li a:active,
.popupbody li label:active {
    background: #ABC;
    color: #000;
    text-decoration:none;
    cursor: pointer;
}

.popupbody li.left {
    width: 110px;
    margin-top: 2px;
    margin-bottom: 2px;
    margin-left: 10px;
    float: left;
    clear: left;
    list-style: none;
}

.popupbody li.right {
    width: 110px;
    margin-top: 2px;
    margin-bottom: 2px;
    margin-right: 10px;
    float: right;
    clear: right;
    list-style: none;
}',
			'lastmodified' => TIME_NOW
		);
		$sid = $db->insert_query('themestylesheets', $style);
		$db->update_query('themestylesheets', array('cachefile' => "css.php?stylesheet={$sid}"), "sid='{$sid}'", 1);
		$query = $db->simple_select('themes', 'tid');
		while($theme = $db->fetch_array($query))
		{
			require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
			update_theme_stylesheet_list($theme['tid']);
		}

    //Archivo requerido para reemplazo de templates
   	require "../inc/adminfunctions_templates.php";
    // Reemplazos que vamos a hacer en las plantillas 1.- Platilla 2.- Contenido a Reemplazar 3.- Contenido que reemplaza lo anterior
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$thread[\'profilelink\']}').'#', '{$avatarep_avatar[\'avatar\']}{$thread[\'profilelink\']}');
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$lastposterlink}').'#', '{$avatarep_lastpost[\'avatar\']}{$lastposterlink}');
	find_replace_templatesets("forumbit_depth2_forum_lastpost", '#'.preg_quote('{$lastpost_profilelink}').'#', '{$forum[\'avatarep_lastpost\'][\'avatar\']}{$lastpost_profilelink}');
	find_replace_templatesets("forumdisplay_announcements_announcement", '#'.preg_quote('{$announcement[\'profilelink\']}').'#', '{$anno_avatar[\'avatar\']}{$announcement[\'profilelink\']}');	
    //Se actualiza la info de las plantillas
   	$cache->update_forums();

    return True;

}

function avatarep_deactivate() {
    //Variables que vamos a utilizar
	global $mybb, $cache, $db;
    // Borrar el grupo de opciones
    $query = $db->simple_select("settinggroups", "gid", "name = 'avatarep'");
    $rows = $db->fetch_field($query, "gid");

    //Eliminamos el grupo de opciones
    $db->delete_query("settinggroups", "gid = {$rows}");

    // Borrar las opciones
    $db->delete_query("settings", "gid = {$rows}");
	$db->delete_query('datacache', "title = 'anno_cache'");
	
    rebuild_settings();
	
    //Eliminamos la hoja de estilo creada...
    	$db->delete_query('themestylesheets', "name='avatarep.css'");
		$query = $db->simple_select('themes', 'tid');
		while($theme = $db->fetch_array($query))
		{
			require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
			update_theme_stylesheet_list($theme['tid']);
		}

    //Archivo requerido para reemplazo de templates
 	require "../inc/adminfunctions_templates.php";
    //Reemplazos que vamos a hacer en las plantillas 1.- Platilla 2.- Contenido a Reemplazar 3.- Contenido que reemplaza lo anterior
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$avatarep_avatar[\'avatar\']}').'#', '',0);
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$avatarep_lastpost[\'avatar\']}').'#', '',0);
	find_replace_templatesets("forumbit_depth2_forum_lastpost", '#'.preg_quote('{$forum[\'avatarep_lastpost\'][\'avatar\']}').'#', '',0);
	find_replace_templatesets("forumdisplay_announcements_announcement", '#'.preg_quote('{$anno_avatar[\'avatar\']}').'#', '',0);
	
    //Se actualiza la info de las plantillas
  	$cache->update_forums();

    return True;

}

// Creamos el formato que llevara el avatar al ser llamado...
function avatarep_format_avatar($user)
{
	global $mybb;
		
    $avatarep_ancho = $mybb->settings['avatarep_ancho'];
    $avatarep_alto = $mybb->settings['avatarep_alto'];		
		
	if($mybb->version_code >= 1700)
	{
	    // 1.8 utiliza una sintaxis diferente
        $avatarep_ancho = $mybb->settings['avatarep_ancho'];
        $avatarep_alto = $mybb->settings['avatarep_alto'];		
		if($avatarep_ancho == ''){$avatarep_ancho ='30px';}
		if($avatarep_alto == ''){$avatarep_alto ='30px';}		
	
		$size = (defined('MAX_FP_SIZE')) ? MAX_FP_SIZE : $mybb->settings['postmaxavatarsize'];
		$dimensions = explode('|', ($user['avatar']) ? $user['avatardimensions'] : (defined('DEF_FP_SIZE')) ? DEF_FP_SIZE : '{$avatarep_ancho}|{$avatarep_alto}');
		$avatar = format_avatar($user['avatar'], $dimensions, $size);

		return array(
			'avatar' => $avatar['image'],
			'username' => htmlspecialchars_uni($user['userusername']),
			'profile' => get_profile_link($user['uid']),
			'uid' => (int)$user['uid'],			
			'usergroup' => (int)$user['userusername'],
			'namestyle' => htmlspecialchars_uni($user['namestyle'])
		);
	}

	return format_avatar($user);
}

/* format_avatar es una funcion de la version 1.8, la 
   creamos sino existe Versiones menores a 1.8 de MyBB.*/
if(!function_exists('format_avatar'))
{
	function format_avatar($user)
	{
		global $mybb;
		static $users;

		if(!isset($users))
		{
			$users = array();
		}

		if(isset($users[$user['uid']]))
		{
			return $users[$user['uid']];
		}
		

        $avatarep_fondo = $mybb->settings['avatarep_fondo'];
        $avatarep_marco = $mybb->settings['avatarep_marco'];
        $avatarep_ancho = $mybb->settings['avatarep_ancho'];
        $avatarep_alto = $mybb->settings['avatarep_alto'];		
        if($avatarep_fondo == ''){$avatarep_fondo ='#FCFDFD';}
		if($avatarep_marco == ''){$avatarep_marco ='#D8DFEA';}
		if($avatarep_ancho == ''){$avatarep_ancho ='30px';}
		if($avatarep_alto == ''){$avatarep_alto ='30px';}		
		$size = (defined('MAX_FP_SIZE')) ? MAX_FP_SIZE : $mybb->settings['postmaxavatarsize'];
		$dimensions = explode('|', ($user['avatar']) ? $user['avatardimensions'] : (defined('DEF_FP_SIZE')) ? DEF_FP_SIZE : '{$avatarep_ancho}|{$avatarep_alto}');
		$avatar = ($user['avatar']) ? htmlspecialchars_uni($user['avatar']) : $mybb->settings['bburl'].'/images/default_avatar.gif';
    		
		$users[$user['uid']] = array(
			'avatar' => "<img src='" . $avatar . "' style='width: {$avatarep_ancho}; height: {$avatarep_alto}; border-style: double; color: {$avatarep_marco}; padding: 2px; background-color: {$avatarep_fondo}; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;'/>",
			'username' => htmlspecialchars_uni($user['userusername']),
			'profilelink' => get_profile_link($user['uid']),
			'uid' => (int)$user['uid'],
			'usergroup' => (int)$user['usergroup'],
			'namestyle' => htmlspecialchars_uni($user['namestyle'])
		);

		return $users[$user['uid']];
	}
}

function forumlist_avatar(&$_f)
{
	global $cache, $db, $fcache, $mybb, $lang, $forum, $lastpost_profilelink;

    // Cargamos idioma
    $lang->load("avatarep", false, true);
    
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == '0' || $mybb->settings['avatarep_active'] == '1' && !$mybb->settings['avatarep_foros'] == '1')
    {
     return false;	
	}
			
	if(!isset($cache->cache['avatarep_cache']))
	{
		$cache->cache['avatarep_cache'] = array();
		$avatarep_cache = $cache->read('avatarep_cache');

		$forums = new RecursiveIteratorIterator(new RecursiveArrayIterator($fcache));

		// Sentencia que busca el creador de los temas, cuando existen subforos...
		foreach($forums as $_forum)
		{
			$forum = $forums->getSubIterator();

			if($forum['fid'])
			{
				$forum = iterator_to_array($forum);
				$avatarep_cache[$forum['fid']] = $forum;

				if($forum['parentlist'])
				{
					$avatarep_cache[$forum['fid']] = $forum;
					$avatarep_cache[$forum['fid']]['avataruid'] = $forum['lastposteruid'];
					
					$exp = array_reverse(explode(',', $forum['parentlist']));

					foreach($exp as $parent)
					{
						if($parent == $forum['fid']) continue;
						if(isset($avatarep_cache[$parent]) && $forum['lastpost'] > $avatarep_cache[$parent]['lastpost'])
						{
							$avatarep_cache[$parent]['lastpost'] = $forum['lastpost'];
							$avatarep_cache[$parent]['avataruid'] = $forum['lastposteruid']; // Se reemplaza la info de un subforo, por la original...
					}
					}
				}
			}
		}

		// Esta sentencia ordena los usuarios por usuario/foro
		$users = array();
		foreach($avatarep_cache as $forum)
		{
			if(isset($forum['avataruid']))
			{
				$users[$forum['avataruid']][] = $forum['fid'];
			}
		}

		// Esta sentecia trae la información de los avatares de usuario
		if(!empty($users))
		{
			$sql = implode(',', array_keys($users));
			//$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar', "uid IN ({$sql})");
            $query = $db->query("SELECT u.uid, u.username, u.username AS userusername, u.avatar, u.avatardimensions, u.usergroup, ug.namestyle 
			FROM " . TABLE_PREFIX . "users u 
			INNER JOIN " . TABLE_PREFIX . "usergroups ug
			ON u.usergroup = ug.gid 
			WHERE uid IN ({$sql})");

			while($user = $db->fetch_array($query))
			{
				// Finalmente, se le asigna el avatar a cada uno de ellos, los traidos en la sentencia.
				$avatar = avatarep_format_avatar($user); 				
				foreach($users[$user['uid']] as $fid)
				{
				$avatarep_cache[$fid]['avatarep_avatar'] = $avatar;
				}	
			}
		}

		// Aplicamos los cambios! Reemplazando las lineas de código para guardarlas en cache...
		$cache->cache['avatarep_cache'] = $avatarep_cache;	

	}

	$_f['avatarep_lastpost'] = $cache->cache['avatarep_cache'][$_f['fid']]['avatarep_avatar'];
	$_f['avatarep_lastpost']['avatar'] = "<a href=\"". $_f['avatarep_lastpost']['profilelink'] . "\" id =\"forum_member{$_f['fid']}\">".$_f['avatarep_lastpost']['avatar']."</a>
<ul class=\"avatarep popupbody\" id=\"forum_member{$_f['fid']}_popup\">

<li style=\"float: right; clear: right; list-style: none; margin-top: -18px; margin-right: 5px;\">
<a href=\"". $_f['avatarep_lastpost']['profilelink']."\">
".$_f['avatarep_lastpost']['avatar']."
</a>
</li>

<li class=\"left\">
<a href=\"member.php?action=profile&amp;uid=". $_f['avatarep_lastpost']['uid'] ."\">
<img src=\"images/site_icons/profile.png\" alt=\"Pefil de Usuario\" />
".$lang->avatarep_user_profile."
</a>
</li>

<li class=\"right\">
<a href=\"search.php?action=finduser&amp;uid=". $_f['avatarep_lastpost']['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/blog.png\" alt=\"Mensajes de Usuario\" />
".$lang->avatarep_user_messages."
</a>
</li>

<li class=\"left\">
<a href=\"private.php?action=send&amp;uid=". $_f['avatarep_lastpost']['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/article.png\" alt=\"Enviar Mensaje Privado\" />
".$lang->avatarep_user_sendpm."
</a>
</li>

<li class=\"right\">
<a href=\"member.php?action=emailuser&amp;uid=". $_f['avatarep_lastpost']['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/message.png\" alt=\"Enviar Correo\" />
".$lang->avatarep_user_sendemail."
</a>
</li>

<li class=\"left\">
<a href=\"search.php?action=finduserthreads&amp;uid=". $_f['avatarep_lastpost']['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/forum.png\" alt=\"Temas del Usuario\" />
".$lang->avatarep_user_threads."
</a>
</li>

</ul>
<script type=\"text/javascript\">if(use_xmlhttprequest == \"1\"){new PopupMenu(\"forum_member{$_f['fid']}\");}</script>";
	$_f['lastposter'] = format_name($_f['avatarep_lastpost']['username'], $_f['avatarep_lastpost']['namestyle'], $_f['avatarep_lastpost']['usergroup']);

}


// Avatar en temas
function avatarep_thread() {

	// Puedes definir las variables deseadas para usar en las plantillas
	global $db, $lang, $avatarep_avatar, $avatarep_firstpost, $avatarep_lastpost, $mybb, $post, $search, $thread, $threadcache, $thread_cache;
	static $avatarep_cache, $avatarep_type;

    $lang->load("avatarep", false, true);        
        
    //Revisar si la opcion esta activa
    if($mybb->settings['avatarep_active'] == '0')
    {
        return False;
    }
	
	if(!isset($avatarep_cache))
	{
		$users = $avatarep_cache = array();
		$cache = ($thread_cache) ? $thread_cache : $threadcache;

		if(isset($cache))
		{
			// Obtenemos los resultados en lista de temas y la busqueda
			foreach($cache as $t)
			{
				if(!in_array($t['uid'], $users))
				{
					$users[] = "'".intval($t['uid'])."'"; // El autor del tema
				}
				if(!in_array($t['lastposteruid'], $users))
				{
					$users[] = "'".intval($t['lastposteruid'])."'"; // El ultimo envio (Si no es el autor del tema)
				}		
			}

			if(!empty($users))
			{
				$sql = implode(',', $users);
				//$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup', "uid IN ({$sql})");
                $query = $db->query("SELECT u.uid, u.username, u.username AS userusername, u.avatar, u.usergroup, ug.namestyle
					FROM " . TABLE_PREFIX . "users u 
					INNER JOIN " . TABLE_PREFIX . "usergroups ug
					ON u.usergroup = ug.gid 
					WHERE uid IN ({$sql})");
					
				while($user = $db->fetch_array($query))
				{
					$avatarep_cache[$user['uid']] = avatarep_format_avatar($user);					
				}

			}
		}
	}

	if(empty($avatarep_cache))
	{
		return; // Si no hay avatares...
	}

	$uid = ($post['uid']) ? $post['uid'] : $thread['uid']; // Siempre debe haber un autor

	if(isset($avatarep_cache[$uid]))
	{
		$avatarep_avatar = $avatarep_cache[$uid];
	}

	if(isset($avatarep_cache[$thread['lastposteruid']]))
	{
		$avatarep_lastpost = $avatarep_cache[$thread['lastposteruid']]; // Unicamente para los últimos envios
	}
     if($mybb->settings['avatarep_temas'] == '1'){
		$thread['username'] = format_name($avatarep_avatar['username'], $avatarep_avatar['namestyle'], $avatarep_avatar['usergroup']);
	    $avatarep_avatar['avatar'] = "<a href=\"". $avatarep_avatar['profilelink'] . "\" id =\"tal_member{$thread['tid']}\">".$avatarep_avatar['avatar']."</a>
<ul class=\"avatarep popupbody\" id=\"tal_member{$thread['tid']}_popup\">

<li style=\"float: right; clear: right; list-style: none; margin-top: -18px; margin-right: 5px;\">
<a href=\"". $avatarep_avatar['profilelink']."\">
".$avatarep_avatar['avatar']."
</a>
</li>

<li class=\"left\">
<a href=\"member.php?action=profile&amp;uid=". $avatarep_avatar['uid'] ."\">
<img src=\"images/site_icons/profile.png\" alt=\"Perfil de Usuario\" />
".$lang->avatarep_user_profile."
</a>
</li>

<li class=\"right\">
<a href=\"search.php?action=finduser&amp;uid=". $avatarep_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/blog.png\" alt=\"Mensajes de Usuario\" />
".$lang->avatarep_user_messages."
</a>
</li>

<li class=\"left\">
<a href=\"private.php?action=send&amp;uid=". $avatarep_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/article.png\" alt=\"Enviar Mensaje Privado\" />
".$lang->avatarep_user_sendpm."
</a>
</li>

<li class=\"right\">
<a href=\"member.php?action=emailuser&amp;uid=". $avatarep_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/message.png\" alt=\"Enviar Correo\" />
".$lang->avatarep_user_sendemail."
</a>
</li>

<li class=\"left\">
<a href=\"search.php?action=finduserthreads&amp;uid=". $avatarep_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/forum.png\" alt=\"Temas del Usuario\" />
".$lang->avatarep_user_threads."
</a>
</li>

</ul>
<script type=\"text/javascript\">if(use_xmlhttprequest == \"1\"){new PopupMenu(\"tal_member{$thread['tid']}\");}</script>";  
}     
        if($mybb->settings['avatarep_temas2'] == '1'){
		$thread['lastposter'] = format_name($avatarep_lastpost['username'],$avatarep_lastpost['namestyle'], $avatarep_lastpost['usergroup']);
		$avatarep_lastpost['avatar'] = 	"<a href=\"". $avatarep_lastpost['profilelink'] . "\" id =\"tao_member{$thread['tid']}\">".$avatarep_lastpost['avatar']."</a>
<ul class=\"avatarep popupbody\" id=\"tao_member{$thread['tid']}_popup\">

<li style=\"float: right; clear: right; list-style: none; margin-top: -18px; margin-right: 5px;\">
<a href=\"". $avatarep_lastpost['profilelink']."\">
".$avatarep_lastpost['avatar']."
</a>
</li>

<li class=\"left\">
<a href=\"member.php?action=profile&amp;uid=". $avatarep_lastpost['uid'] ."\">
<img src=\"images/site_icons/profile.png\" alt=\"Perfil de Usuario\" />
".$lang->avatarep_user_profile."
</a>
</li>

<li class=\"right\">
<a href=\"search.php?action=finduser&amp;uid=". $avatarep_lastpost['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/blog.png\" alt=\"Mensajes de Usuario\" />
".$lang->avatarep_user_messages."
</a>
</li>

<li class=\"left\">
<a href=\"private.php?action=send&amp;uid=". $avatarep_lastpost['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/article.png\" alt=\"Enviar Mensaje Privado\" />
".$lang->avatarep_user_sendpm."
</a>
</li>

<li class=\"right\">
<a href=\"member.php?action=emailuser&amp;uid=". $avatarep_lastpost['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/message.png\" alt=\"Enviar Correo\" />
".$lang->avatarep_user_sendemail."
</a>
</li>

<li class=\"left\">
<a href=\"search.php?action=finduserthreads&amp;uid=". $avatarep_lastpost['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/forum.png\" alt=\"Temas del Usuario\" />
".$lang->avatarep_user_threads."
</a>
</li>

</ul>
<script type=\"text/javascript\">if(use_xmlhttprequest == \"1\"){new PopupMenu(\"tao_member{$thread['tid']}\");}</script>";
		}
     if($mybb->settings['avatarep_temas'] == '0'){
			//$thread['username'] = "";
			$avatarep_avatar['avatar'] = "";
		}	
     if($mybb->settings['avatarep_temas2'] == '0'){
			//$thread['lastposter']= "";
			$avatarep_lastpost['avatar']= "";
	 }
}


// Actualizar si hay un nuevo avatar
function avatarep_avatar_update()
{
    global $cache, $db, $extra_user_updates, $mybb, $updated_avatar, $user;

    $user = ($user) ? $user : $mybb->user;
    $inline_avatars = $cache->read('anno_cache');

    if(!$inline_avatars[$user['uid']])
    {
        return;
    }

    $update = ($extra_user_updates) ? $extra_user_updates : $updated_avatar;

    if(is_array($update))
    {
        $user = array_merge($user, $update);    

        $inline_avatars[$user['uid']] = avatarep_format_avatar($user);
        $cache->update('anno_cache', $inline_avatars);
    }
} 

// Avatar en anuncions
function avatarep_announcement()
{
	global $announcement, $cache, $anno_avatar, $mybb, $lang;

	if($mybb->settings['avatarep_active'] == '0' || $mybb->settings['avatarep_active'] == '1' && $mybb->settings['avatarep_anuncios'] == '0')
    {
        return False;
    }
	
    $lang->load("avatarep", false, true); 
	$inline_avatars = $cache->read('anno_cache');
    $avatarep_fondo = $mybb->settings['avatarep_fondo'];
    $avatarep_marco = $mybb->settings['avatarep_marco'];
    $avatarep_ancho = $mybb->settings['avatarep_ancho'];
    $avatarep_alto = $mybb->settings['avatarep_alto'];	
	
	if($inline_avatars[$announcement['uid']])
	{
		$anno_avatar = array(
			'avatar' => $inline_avatars[$announcement['uid']]['avatar'],
			'username' => $inline_avatars[$announcement['uid']]['username'], 
			'uid' => $inline_avatars[$announcement['uid']]['uid'],			
			'usergroup' => $inline_avatars[$announcement['uid']]['usergroup'],
			'namestyle' => $inline_avatars[$announcement['uid']]['namestyle'], 			
			'profilelink' => $inline_avatars[$announcement['uid']]['profilelink']
		);
		
	}
	$announcement['profilelink'] = format_name($anno_avatar['username'], $anno_avatar['namestyle'], $anno_avatar['usergroup']);
	$anno_avatar['avatar'] = "<a href=\"". $anno_avatar['profilelink'] . "\" id =\"aa_member{$thread['tid']}\">".$anno_avatar['avatar']."</a>
<ul class=\"avatarep popupbody\" id=\"aa_member{$thread['tid']}_popup\">

<li style=\"float: right; clear: right; list-style: none; margin-top: -18px; margin-right: 5px;\">
<a href=\"". $anno_avatar['profilelink']."\">
".$anno_avatar['avatar']."
</a>
</li>

<li class=\"left\">
<a href=\"member.php?action=profile&amp;uid=". $anno_avatar['uid'] ."\">
<img src=\"images/site_icons/profile.png\" alt=\"Perfil de Usuario\" />
".$lang->avatarep_user_profile."
</a>
</li>

<li class=\"right\">
<a href=\"search.php?action=finduser&amp;uid=". $anno_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/blog.png\" alt=\"Mensajes de Usuario\" />
".$lang->avatarep_user_messages."
</a>
</li>

<li class=\"left\">
<a href=\"private.php?action=send&amp;uid=". $anno_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/article.png\" alt=\"Enviar Mensaje Privado\" />
".$lang->avatarep_user_sendpm."
</a>
</li>

<li class=\"right\">
<a href=\"member.php?action=emailuser&amp;uid=". $anno_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/message.png\" alt=\"Enviar Correo\" />
".$lang->avatarep_user_sendemail."
</a>
</li>

<li class=\"left\">
<a href=\"search.php?action=finduserthreads&amp;uid=". $anno_avatar['uid'] ."\" rel=\"nofollow\">
<img src=\"images/site_icons/forum.png\" alt=\"Temas del Usuario\" />
".$lang->avatarep_user_threads."
</a>
</li>

</ul>
<script type=\"text/javascript\">if(use_xmlhttprequest == \"1\"){new PopupMenu(\"aa_member{$thread['tid']}\");}</script>";
}

function avatarep_announcement_update($args)
{
	global $cache, $db, $insert_announcement, $mybb, $update_announcement;

	$inline_avatars = $cache->read('anno_cache');
	$anno = ($update_announcement) ? $update_announcement : $insert_announcement;

	if(is_array($inline_avatars) && $inline_avatars[$anno['uid']])
	{
		return; //  No hay necesidad de recrear la cache...
	}

	if($anno['uid'] == $mybb->user['uid'])
	{
		$inline_avatars[$anno['uid']] = avatarep_format_avatar($mybb->user);
	}
	else
	{
		//$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup', "uid = '{$anno['uid']}'");
        $query = $db->query("SELECT u.uid, u.username, u.username AS userusername, u.avatar, u.usergroup, ug.namestyle 
			FROM " . TABLE_PREFIX . "users u 
			INNER JOIN " . TABLE_PREFIX . "usergroups ug
			ON u.usergroup = ug.gid 
			WHERE uid = '{$anno['uid']}'");

		$user = $db->fetch_array($query);

		$inline_avatars[$user['uid']] = avatarep_format_avatar($user);
	}

	$cache->update('anno_cache', $inline_avatars);
}

?>
