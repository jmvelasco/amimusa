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
        $postData = array();
        parse_str($_POST['postData'], $postData);

        $senderName = strip_tags(trim($postData['feedback-sender']));
        $comments =  htmlentities(trim($postData['comments']), ENT_NOQUOTES);
        $captcha = $postData['g-recaptcha-response'];
        if (empty($captcha)) {
            die('no-captcha');
        } else {
            $secret = '6LeOIgsTAAAAANUz7PHsA9A3aMw1It8ifmpUT_sp';
            if ($service->checkCaptcha($captcha, $secret)) {
                if ($service->sendFeedback($senderName, $comments)) {
                    die('success');
                } else {
                    $errorCode = 23450;
                    header('Location: /?target=error-handler&error-code='.$errorCode);
                }
            } else {
                die('error');
            }
        }
        die('error');

        break;

    case 'login':
        if (isset($_SESSION['user'])) {
            header('Location: /?target=home');
        }
        // Set the title for the page
        $vars['title'] = 'Amimusa: El espacio para compartir tu musa';
        if (isset($_GET['status']) && (1 == $_GET['status'])) {
            $message = '<div class="alert alert-success">Por favor, comprueba tu correo para activar tu cuenta.</div>';
        } else {
            $message = '';
        }
        $data['alert-message'] = $message;
        $content = $service->render('login-form', $data);

        break;

    case 'register':
        // Set the title for the page
        $vars['title'] = 'Unete a Amimusa';

        $content = $service->render('register-form');

        break;

    case 'remember-password':
        // Set the title for the page
        $vars['title'] = 'Recuerda tu contraseña';
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
        $vars['title'] = 'Amimusa: El espacio para compartir tu musa';

        if ($user = key_exists('user', $_SESSION)?$_SESSION['user']:false) {
            $profileData = $service->getContributorProfile($user['name']);
            $content =  $service->render('header', array(
                    'username' => $user['name'],
                    'page-title' => $vars['title']
            ));

            $content .= $service->render('home');

            $musas = $service->getMusas();
            if (!empty($musas)) {
                $content .= '<div id="musas-wrapper" class="container">';
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
                $content .= '<div id="musas-wrapper" class="container">';
                foreach ($musas as $musaId =>$musa) {
                    unset($musa['count']);
                    $musa['id'] = $musaId;
                    $content .= $service->render('musa-block', $musa);
                }
                $content .= '</div>';
            }
        }
        $content .= $service->render('suggestion-block');

        break;

    case 'profile':
        // Set the title for the page
        $vars['title'] = 'Tu perfil';

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
        $vars['title'] = 'Modifica tu perfil';

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
        $vars['title'] = 'Mis escritos';

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
                $message = '<div class="alert alert-success">Todo correcto!</div>';
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
        $vars['title'] = 'Modifica tu escrito';

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
        $vars['title'] = 'Elimina tu escrito';

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

        if ($user = key_exists('user', $_SESSION)?$_SESSION['user']:false) {
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
        $vars['title'] = 'Comparte tu inspiración';

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
        $secret = '6LfoQQsTAAAAAKHyqpJ3-5HYLj56_TU1khY8vl9t';
        if ($service->checkCaptcha($_POST['g-recaptcha-response'], $secret)) {
            $returnCode = $service->register($_POST);
            if (23000 == $returnCode) {
                header('Location: /?target=error-handler&error-code='.$returnCode);
            } else {
                //$returnCode = 1;
                header('Location: /?target=login&status='.$returnCode);
            }
        } else {
            $returnCode = 23460;
            header('Location: /?target=error-handler&error-code='.$returnCode);
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
        $vars['title'] = 'Gestión de errores';

        $page = file_get_contents('views/error.htpl');
        $errorMessage = $_GET['error-code'];

        switch ($_GET['error-code']) {
            case 23400:
                $errorMessage = "Las contraseñas no coinciden.";
                break;
            case 23410:
                $errorMessage = "No existe el usuario o la contraseña es incorrecta.<br> <a href='/index.php?target=remember-password'>Recordar contraseña</a>.";
                break;
            case 23420:
                $errorMessage = "No estás dentro.<br> <a href='/index.php?target=login'>Entrar</a>&nbsp;or&nbsp;<a href='/index.php?target=register'>Registrarte</a>.";
                break;
            case 23430:
                $errorMessage = "La dirección de correo no coincide con el usuario.";
                break;
            case 23440:
                $errorMessage = "El enlace no se corresponde con ningún token.<br> <a href='/index.php?target=remember-password'>Recordar Contraseña</a>.";
                break;
            case 23460:
                $errorMessage = "Tienes que verificar que eres una persona.";
                break;
            case 23000:
                $errorMessage = "Ya existe un usuario con este nombre o dirección de correo. Deberías escoger otro.";
                break;
            default:
                $errorMessage = "Oops, algo va mal!: " . $_GET['error-code'];
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
