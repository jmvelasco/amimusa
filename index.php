<?php
require_once('bootstrap.php');

use \Models\Service;
$service = new \Models\Service();

// Default JS Scripts to include
$js[] = '/js/jquery-1.11.3.js';
$js[] = '/js/audiojs/audio.min.js';
$js[] = '/js/form-helper.js';

/**
 * Routing
 */
$target = 'home';
if (isset($_GET['target'])) {
	$target = $_GET['target'];
} elseif(isset($_POST)) {
	if (isset($_POST['target'])) {
		$target = $_POST['target'];
	}
}

switch ($target) {
    case 'send-feedback';
        $senderName = strip_tags(trim($_POST['feedbackSender']));
        $comments =  htmlentities(trim($_POST['comments']), ENT_NOQUOTES) . print_r($_POST, true);
        if ($service->sendFeedback($senderName, $comments)) {
            die('success');
        } else {
            die('error');
        }


        break;

	case 'login':
		if (isset($_SESSION['user'])) {
			header('Location: /?target=home');
		}
		// Set the title for the page
		$vars['title'] = 'Amimusa Welcome Page';
		if (isset($_GET['status']) && (1 == $_GET['status'])) {
			$message = '<div class="alert alert-success">Password updated successfully.<br /><small>Please, check your email to confirm the operation.</small></div>';
		} else {
			$message = '';
		}
		$data['alert-message'] = $message;
		$content = $service->render('login-form', $data);
		
		break;
	
	case 'register':
		// Set the title for the page
		$vars['title'] = 'Amimusa Regisration Page';
	
		$content = $service->render('register-form');
	
		break;
		
	case 'remember-password':
		// Set the title for the page
		$vars['title'] = 'Remember your password';
		$content = $service->render('rememberpassword-form');
	
		break;

    case 'active-account':
        $token = $_GET['token'];
        if ($service->activateContributor($token)) {
            header('Location: /?target=home');
        } else {
            $errorCode = 23440;
            header('Location: /?target=error-handler&error-code='.$errorCode);
        }
        break;
		
	case 'home':
		// Set the title for the page
		$vars['title'] = 'Amimusa\'s Home';

		if ($user = key_exists('user', $_SESSION)?$_SESSION['user']:false) {
			$profileData = $service->getContributorProfile($user['name']);
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$content .= $service->render('home');

			$musas = $service->getMusas();
			if (!empty($musas)) {
				$content .= '<div id="musas-wrapper">';
				foreach ($musas as $musaId =>$musa) {
					unset($musa['count']);
					$musa['id'] = $musaId;
					$content .= $service->render('musa-block', $musa);
				}
				$content .= '</div>';
			}
		} else {
			$content =  $service->render('publicheader', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$content .= $service->render('home');

			$musas = $service->getMusas();
			if (!empty($musas)) {
				$content .= '<div id="musas-wrapper">';
				foreach ($musas as $musaId =>$musa) {
					unset($musa['count']);
					$musa['id'] = $musaId;
					$content .= $service->render('musa-block', $musa);
				}
				$content .= '</div>';
			}
		}
		
		break;
		
	case 'profile':
		// Set the title for the page
		$vars['title'] = 'Amimusa\'s Contributor Profile';
		
		if ($user = $_SESSION['user']) {
			if (isset($_GET['userid'])) {
				$userId = $_GET['userid'];
			} else {
				$userId = $user['id'];
			}
			$profileData = $service->getContributorProfile($userId);
			$contributorMusas = $service->getContributorMusas($userId);
			$musas = '';
			foreach ($contributorMusas as $k => $v) {
				$musas .= $service->render('musaslist-block', $v);
			}
			$profileData['musas'] = $musas; 
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));

			$content .= $service->render('contributor', $profileData);
		} else {
			$errorCode = 23420;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		}
		
		break;
		
	case 'update-profile':
		// Set the title for the page
		$vars['title'] = 'Update Amimusa\'s Contributor Profile';
		
		if ($user = $_SESSION['user']) {
			$profileData = $service->getContributorProfile($user['id']);
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$content .= $service->render('contributor-form', $profileData);
		} else {
			$errorCode = 23420;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		}

		break;
		
	case 'user-contributions':
		$vars['title'] = 'My Contributions';
		
		if ($user = $_SESSION['user']) {
			if (isset($_GET['userid'])) {
				$userId = $_GET['userid'];
			} else {
				$userId = $user['id'];
			}
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$content .= $service->render('nav-block');
			
			$userContributions = $service->getUserContributions($userId);
			$data['rows'] = $service->renderContributionsRows($userContributions);
			if (isset($_GET['status']) && (1 == $_GET['status'])) {
				$message = '<div class="alert alert-success">Action performed successfully</div>';
			} else {
				$message = '';
			}
			$data['alert-message'] = $message;
			$content .= $service->render('usercontributions-table', $data);
		}  else {
			$errorCode = 23420;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		}
		
		break;
		
	case 'edit-contribution':
		// Set the title for the page
		$vars['title'] = 'Edit your contribution';
		
		if ($user = $_SESSION['user']) {
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$writting = $service->getWritting($_GET['id']);
			$content .= $service->render('updatewritting-form', $writting);
		} else {
			$errorCode = 23420;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		}
		
		break;
		
	case 'remove-contribution':
		// Set the title for the page
		$vars['title'] = 'Remove your contribution';
		
		if ($user = $_SESSION['user']) {
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$writting = $service->getWritting($_GET['id'], true);
			$content .= $service->render('removewritting-form', $writting);
		} else {
			$errorCode = 23420;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		}
		
		break;

	case 'get-publications';
		$vars['title'] = 'Amimusa: <small>' . strtoupper($_GET['name']) . '</small>';
		
		if ($user = in_array('user', $_SESSION)?$_SESSION['user']:false) {
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$content .= $service->render('nav-block');
			$results = $service->getMusaPublications($_GET['id']);
			foreach ($results as $result) {
				$content .= $service->render('write-block', $result);
			}
		} else {
			$content =  $service->render('publicheader', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));

			$content .= $service->render('nav-block');
			$results = $service->getMusaPublications($_GET['id']);
			foreach ($results as $result) {
				$content .= $service->render('write-block', $result);
			}
		}
		
		break;
		
	case 'writting':
		// Set the title for the page
		$vars['title'] = 'Share your inspiration';
		
		if ($user = $_SESSION['user']) {
			$content =  $service->render('header', array(
					'username' => $user['name'],
					'page-title' => $vars['title']
			));
			$content .= $service->render('writting-form');
		} else {
			$errorCode = 23420;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		}
		
		break;
		
	case 'writting-handler':
		$musasIdList = isset($_POST['musasIdList']) ? trim($_POST['musasIdList']) : '';
		$body = isset($_POST['body']) ? trim($_POST['body']) : '';
		if (empty($musasIdList) ||empty($body)) {
			$errorCode = 23500;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		} else {
			$errorCode = $service->registerWritting($_POST, $_SESSION['user']['id']);
			if (23000 == $errorCode) {
				header('Location: /?target=error-handler&error-code='.$errorCode);
			} else {
				header('Location: /?target=user-contributions&status=1');
			}

		}
	
		break;
		
	case 'updatewritting-handler':
		if (!isset($_POST['id'])) {
			$errorCode = 23500;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		} else {
			$errorCode = $service->updateWritting($_POST);
			if (23000 == $errorCode) {
				header('Location: /?target=error-handler&error-code='.$errorCode);
			} else {
				header('Location: /?target=user-contributions&status='.$errorCode);
			}

		}
		break;
		
	case 'removewritting-handler':
		if (!isset($_POST['id'])) {
			$errorCode = 23500;
			header('Location: /?target=error-handler&error-code='.$errorCode);
		} else {
			$errorCode = $service->removeWritting($_POST);
			if (23000 == $errorCode) {
				header('Location: /?target=error-handler&error-code='.$errorCode);
			} else {
				header('Location: /?target=user-contributions&status='.$errorCode);
			}

		}
		break;
		
	case 'updatecontributor-handler':
		$errorCode = $service->updateContributorProfile($_POST);
		if ((23000 == $errorCode) || (0 == $errorCode) ){
			header('Location: /?target=error-handler&error-code='.$errorCode);
		} else {
			header('Location: /?target=profile');
		}
	
		break;
		
	case 'login-handler':
		$returnCode = $service->loginSuccess($_POST);
		if (23410 != $returnCode) {
			$_SESSION['user'] = array('id' => $returnCode, 'name' => $_POST['username']);
			header('Location: /?target=home');
		} else {
			header('Location: /?target=error-handler&error-code='.$returnCode);
		}
		break;
		
	case 'register-handler':
		$returnCode = $service->register($_POST);
		if (23000 == $returnCode) {
			header('Location: /?target=error-handler&error-code='.$returnCode);
		} else {
			$_SESSION['user'] = array('id' => $returnCode, 'name' => $_POST['username']);
			header('Location: /?target=home');
		}
	
		break;
		
	case 'rememberform-handler':
		$returnCode = $service->updatePassword($_POST);
		$errorsCode = array(23000, 23430);
		if (in_array($returnCode, $errorsCode)) {
			header('Location: /?target=error-handler&error-code='.$returnCode);
		} else {
			header('Location: /?target=login&status='.$returnCode);
		}
	
		break;
				
	case 'error-handler':
		// Set the title for the page
		$vars['title'] = 'Amimusa Regisration Page';
		
		$page = file_get_contents('views/error.htpl');
		$errorMessage = $_GET['error-code'];
		
		switch ($_GET['error-code']) {
			case 23400:
				$errorMessage = "The password doesn't match.";
				break;
			case 23410:
				$errorMessage = "The user doesn't exist or the password is not correct.<br> <a href='/index.php?target=remember-password'>Remember Password</a>";
				break;
			case 23420:
				$errorMessage = "The user is not logged in.";
				break;
			case 23430:
				$errorMessage = "The e-mail doesn't much with the one the user was registered.";
				break;
            case '23440':
                $errorMessage = "The link is not associated with an activation token.<br> <a href='/index.php?target=remember-password'>Remember Password</a>";
                break;
			case 23000:
				$errorMessage = "There is another user with this username or email. Please choose another one to proceed.";
				break;
			default:
				$errorMessage = "Something is wrong: " . $_GET['error-code'];
		}
		$content = str_replace('###error-message###', $errorMessage, $page);
		
		break;
		
	case 'logout':
		unset($_SESSION['username']);
		session_destroy();
		header('Location: /');
		
		break;
		
	case 'search-musa':
		$result = $service->getMusasLike($_POST['str']);
		if (!empty($result)) {
			echo json_encode($result);
		} else {
			echo '';
		}
		
		exit;
		break;
		
	case 'insert-musa':
		$result = $service->registerMusa($_POST['musa']);
		echo $result;

		exit;
		break;

	default:
		throw new \Exception("Routing problem:" . $target . " doesn't match with any valid target.");
		
}

/*
 * Prepare render
 */
$vars['scripts'] = '';
if (isset($js)) {
	foreach ($js as $s) {
		$vars['scripts'] .= "<script src='$s'></script>\n";
	}	
} 
// Generate content from Default Layout
$step00 = file_get_contents('views/layout.htpl');
// Set title
$step01 = str_replace('###title###', strip_tags($vars['title']), $step00);
// Introduce JS Scripts
$step02 = str_replace('<!-- ###extra-js-scripts### -->', $vars['scripts'], $step01);
// Set the content
$step03 = str_replace('###content###', $content, $step02);
$render = $step03;

/*
 * Render the page
 */
die($render);
