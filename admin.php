<?php
	require 'pageStart.php';
	if($permissions < 2) echo '<meta http-equiv="refresh" content="0; url=./" />';
	$action_types = [
		'user'
	];
	$usr_actions = [
		'ban' => ['level' => 3, 'display' => 'Ban'],
		'chng_password' => ['level' => 2, 'display' => 'Change Password'],
		'chng_permissions' => ['level' => 4, 'display' => 'Change Permissions'],
		'chng_username' => ['level' => 2, 'display' => 'Change Username'],
		'deverify' => ['level' => 3, 'display' => 'Remove Verification'],
		'verify' => ['level' => 3, 'display' => 'Verify'] 
	];
	if(isset($_POST['execute'])) {
		if(in_array($_POST['action-type'], $action_types)) {
			if(array_key_exists($_POST['action'], $usr_actions)) {
				if($permissions >= $usr_actions[$_POST['action']]['level']) {
					$subjectPerm = dbq_get_perm($_POST['user']);
					if($subjectPerm !== false && $subjectPerm < $permissions) {
						switch ($_POST['action']) {
							case 'ban':
								dbq_chg_perm($_POST['user'], -1);
								addBanner('User Banned.');
								break;
							
							case 'chng_password':
								dbq_chg_pass($_POST['user'], $_POST['data']);
								addBanner('Password Updated.');
								break;
							
							case 'chng_permissions':
								dbq_chg_perm($_POST['user'], intval($_POST['data']));
								addBanner('Permissions Updated.');
								break;
							
							case 'chng_username':
								dbq_chg_usrnm($_POST['user'], $_POST['data']);
								addBanner('Username Updated.');
								break;
							
							case 'deverify':
								dbq_chg_perm($_POST['user'], 0);
								addBanner('Verified Status Removed.');
								break;
							
							case 'verify':
								if($subjectPerm == 0) {
									dbq_chg_perm($_POST['user'], 1);
									addBanner('User Verified.');
								}
								else
									addBanner('User Already Verified.');
								break;
							
							default:
								addBanner('Invalid Action.');
								break;
						}
					}
					else {
						addBanner('Invalid Permissions.');
					}
				}
				else {
					addBanner('Invalid Permissions.');
				}
			}
			else {
				addBanner('Invalid Action.');
			}
		}
		else {
			addBanner('Invalid Action.');
		}
	}
?>

		<title>Administrator Controls</title>
		<div class="body">
				<div class="card">
					<div class="postmeta">
						<div class="h">Administrator Controls</div>
					</div>
					<div class="stuffing"><b>User Controls</b><form action="./admin.php" method="post"><input type="hidden" name="action-type" value="user"><?php
						
						echo 'Action: <select name="action">';
						foreach ($usr_actions as $action => $action_data) {
							if($action_data['level'] <= $permissions) echo '<option value="'.$action.'">'.$action_data['display'].'</option>';
						}
						echo '</select>, User: <select name="user">';
						$query="SELECT `username` FROM `mcstuff`.`users` WHERE `permissions`<'".$permissions."';";
						$queryresult = [];
						dbq_get_usrs_perm($permissions, $queryresult);
						foreach ($queryresult as $usr) {
							echo '<option value="'.$usr.'">'.$usr.'</option>';
						}
						echo '</select>, Additional Data: <input type="text" name="data">, <input type="submit" name="execute" value="Execute">';
					?></form></div>
					<div class="footer">
						
					</div>
				</div>
				<div class="card">
					<div class="postmeta">
						<div class="h">User List</div>
					</div>
					<div class="stuffing"><table><tr><th>Username</th><th>Permission Level</th><th>Minecraft UUID</th><th>Last IP</th><th>Last On</th></tr><?php
						$userinfo = $pdo->prepare('SELECT `username`,`permissions`,`uuid`,`ip`,`laston` FROM `mcstuff`.`users`;');
						$userinfo->execute();
						$user_array = $userinfo->fetchAll(PDO::FETCH_BOTH);
						foreach ($user_array as $info) {
							echo '<tr><td>'.$info[0].'</td><td>'.$info[1].'</td><td>'.$info[2].'</td><td>'.$info[3].'</td><td>'.$info[4].'</td></tr>';
						}
					?></table></div>
					<div class="footer">
						
					</div>
				</div>
			</div>
		</div>

<?php
	require 'pageEnd.php';
?>