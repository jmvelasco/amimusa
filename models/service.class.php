<?php
namespace Models;


use PDO;
use PDOException;

class Service
{
    private $mdb;

    /**
     * Create the DB connection instance
     */
    public function __construct()
    {
        $this->getConnection();
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        if (!isset($this->mdb)) {
            require_once $_SERVER['DOCUMENT_ROOT'] .  DIRECTORY_SEPARATOR . 'config' .  DIRECTORY_SEPARATOR . 'settings.php';
            if (isset($settings)) {
                $host 	= $settings['host'];
                $dbName = $settings['dbName'];
                $user 	= $settings['user'];
                $pass 	= $settings['pass'];

                try {
                    $mdb = new \PDO("mysql:host=$host;dbname=$dbName", $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                    $mdb->setAttribute(\PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch(\PDOException  $e) {
                    $errorCode = (int)$e->getCode();
                    $error[$errorCode] = $e->getMessage();
                    throw new \PDOException($e);
                }

                $this->mdb = $mdb;
                $this->mdb->beginTransaction();
                return $this->mdb;
            } else {
                throw new \PDOException("No settings file found.");
            }
        } else {
            return $this->mdb;
        }



    }

    public function register($data)
    {
        if ($data['password'] === $data['password-match']) {
            $sentencia = $this->getConnection()->prepare("INSERT INTO `contributors` (`name`, `username`, `email`, `password`) VALUES (:name, :username, :email, :password)");
            $sentencia->bindParam(':name', $name);
            $sentencia->bindParam(':username', $username);
            $sentencia->bindParam(':email', $email);
            $sentencia->bindParam(':password', $password);

            $name       = isset($data['name'])?trim($data['name']):'';
            $username   = trim($data['username']);
            $email      = $data['email'];
            $password   = md5($data['password']);

            try {
                $sentencia->execute();
                $lastId = $this->getConnection()->lastInsertId();
                $this->getConnection()->commit();
                $errorCode = $lastId;
            } catch(\PDOException  $e) {
                $errorCode = (int)$e->getCode();
                $error[$errorCode] = $e->getMessage();
                $this->getConnection()->rollBack();
                //throw new \PDOException($e);
            }


        } else {
            $errorCode = 23400;
        }

        return $errorCode;
    }

    public function updatePassword($data)
    {
        $sentencia = $this->getConnection()->prepare("SELECT `id` FROM `contributors` where `username` = :username and `email` = :email");

        $username = trim($data['username']);
        $email = $data['email'];

        $sentencia->bindValue(':username', $username);
        $sentencia->bindValue(':email',  $email);

        try {
            $sentencia->execute();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($sentencia->rowCount() > 0) {
            $sentencia = $this->getConnection()->prepare("UPDATE `contributors` SET
                    `password` = ?, `security_token` = ?, `active` = 0
                     WHERE `username` = ?
                     AND `email` = ?");

            $username = isset($data['username'])?trim($data['username']):'';
            $email = $data['email'];
            $password = md5($data['password']);
            $token = md5(uniqid($username, true));

            $mapping = array($password, $token, $username, $email);

            try {
                $sentencia->execute($mapping);
                $this->getConnection()->commit();
                $errorCode = 1;
            } catch(\PDOException  $e) {
                $errorCode = (int)$e->getCode();
                $error[$errorCode] = $e->getMessage();
                $this->getConnection()->rollBack();
                throw new \PDOException($e);
            }

            $para      = $email;
            $titulo    = 'Password recovery';
            $activationLink = 'http://' . $_SERVER['SERVER_NAME'] . '/?target=active-account&token=' . $token;
            $mensaje   = 'Click on the following link to active your account:' . "\n" . $activationLink;
            $cabeceras = 'From: no-reply@amimusa.net' . "\r\n" .
                'Reply-To: no-reply@amimusa.net' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            mail($para, $titulo, $mensaje, $cabeceras);

            return $errorCode;
        } else {
            return 23430;
        }


    }

    public function activateContributor($token)
    {
        $sentencia = $this->getConnection()->prepare("SELECT `id`, `username` FROM `contributors` WHERE `security_token` = :token");
        $sentencia->bindValue(':token', $token);

        try {
            $sentencia->execute();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        $row = $sentencia->fetch();
        if (!empty($row)) {
            $sentencia = $this->getConnection()->prepare("UPDATE `contributors` SET
                    `active` = 1,
                    `security_token` = NULL
                     WHERE `security_token` = ?");

            $mapping = array($token);

            try {
                $sentencia->execute($mapping);
                $this->getConnection()->commit();
                $errorCode = 1;
            } catch(\PDOException  $e) {
                $errorCode = (int)$e->getCode();
                $error[$errorCode] = $e->getMessage();
                $this->getConnection()->rollBack();
                throw new \PDOException($e);
            }

            $_SESSION['user'] = array('id' => $row['id'], 'name' => $row['username']);
            return true;
        } else {
            return false;
        }

    }

    public function sendFeedback($senderName, $comments)
    {
        $para      = 'amimusamanou@gmail.com';
        $titulo    = 'Feedback received';

        $mensaje   = $comments . "\n" . $senderName;
        $cabeceras = 'From: no-reply@amimusa.net' . "\r\n" .
            'Reply-To: no-reply@amimusa.net' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($para, $titulo, $mensaje, $cabeceras);

        return true;
    }

    public function registerWritting($data, $idContributor)
    {
        $sentencia = $this->getConnection()->prepare("INSERT INTO `writtings` (`title`, `body`)
                VALUES (?, ?)");

        $title= isset($data['title'])?trim($data['title']):'';
        $body = trim(str_replace("\n", "<br />", $data['body']));

        $mapping = array($title, $body);

        try {
            $sentencia->execute($mapping);
            $lastId = $this->getConnection()->lastInsertId();
            $this->getConnection()->commit();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        $sentencia = $this->getConnection()->prepare("INSERT INTO `publications` (`id_contributor`, `id_writting`, `id_state`)
                VALUES (?, ?, 1)");

        $mapping = array($idContributor, $lastId);

        try {
            $sentencia->execute($mapping);
            $lastId = $this->getConnection()->lastInsertId();
            $errorCode = 1;
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        $sentencia = $this->getConnection()->prepare("INSERT INTO `publications_musas` (`id_publication`, `id_musa`)
                VALUES (?, ?)");

        $musasId = explode(",",$data['musasIdList']);
        foreach ($musasId as $musaId) {
            $mapping = array($lastId, $musaId);
            try {
                $sentencia->execute($mapping);
                $errorCode = 1;
            } catch(\PDOException  $e) {
                $errorCode = (int)$e->getCode();
                $error[$errorCode] = $e->getMessage();
                throw new \PDOException($e);
            }
        }

        return $errorCode;
    }

    public function loginSuccess($data)
    {
        $sentencia = $this->getConnection()->prepare("SELECT `id` FROM `contributors` where `username` = :username and `password` = :password and `active` = 1");

        $username = trim($data['username']);
        $password = md5($data['password']);

        $sentencia->bindValue(':username', $username);
        $sentencia->bindValue(':password',  $password);

        try {
            $sentencia->execute();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }
        if ($sentencia->rowCount() > 0) {
            $row = $sentencia->fetch();
            return $row['id'];
        } else {
            return 23410;
        }

    }

    public function getContributorProfile($id)
    {
        $sentencia = $this->getConnection()->prepare("SELECT `name`, `username`, `email`,`description`,`link_to_profile`
                FROM `contributors`
                WHERE `id` = ?");

        $mapping = array($id);

        try {
            $sentencia->execute($mapping);
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($result = $sentencia->fetch()) {
            foreach ($result as $column => $value) {
                if (!is_numeric($column)) {
                    $data[$column] = $value;
                }
            }
            return $data;
        } else {
            return null;

        }

    }


    public function updateContributorProfile($data)
    {
        $sentencia = $this->getConnection()->prepare("UPDATE `contributors` SET
                `name` = ?,
                `email` = ?,
                `description` = ?,
                `link_to_profile` = ?
                 WHERE `username` = ?");

        $name = isset($data['name'])?trim($data['name']):'';
        $email = $data['email'];
        $description = isset($data['description'])?$data['description']:'';
        $linkToProfile = isset($data['link-to-profile'])?$data['link-to-profile']:'';
        $currentUser = trim($data['current-user']);

        $mapping = array($name, $email, $description, $linkToProfile, $currentUser);

        try {
            $sentencia->execute($mapping);
            $this->getConnection()->commit();
            $errorCode = 1;
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            $this->getConnection()->rollBack();
            throw new \PDOException($e);
        }

        return $errorCode;
    }

    public function getPublications()
    {
        $sentencia = $this->getConnection()->prepare("SELECT `id_contributor`, `id_writting`
                FROM `publications`
                WHERE `id_state` = 1");

        try {
            $sentencia->execute();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($result = $sentencia->fetchAll()) {

            foreach ($result as $pN => $row) {
                foreach ($row as $column => $value) {
                    if (!is_numeric($column)) {
                        $data[$pN][$column] = $value;
                    }
                }
            }
            return $data;
        } else {
            return null;

        }
    }

    public function getWritting($idWritting, $withCR = false)
    {
        $sentencia = $this->getConnection()->prepare("SELECT `id`, `title`, `body`
                FROM `writtings`
                WHERE `id` = ?");

        $mapping = array($idWritting);

        try {
            $sentencia->execute($mapping);
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($result = $sentencia->fetch()) {
            foreach ($result as $column => $value) {
                if (!is_numeric($column)) {
                    if ($withCR) {
                        $data[$column] = $value;
                    } else {
                        $data[$column] = strip_tags($value);
                    }

                }
            }
            return $data;
        } else {
            return null;

        }

    }

    public function updateWritting($data)
    {
        $sentencia = $this->getConnection()->prepare("UPDATE `writtings`
                SET `body`=?, `title` = ?
                WHERE `id`=?");
        $body = $data['body'];
        $id = $data['id'];
        $title = $data['title'];

        $mapping = array($body, $title, $id);

        try {
            $sentencia->execute($mapping);
            $this->getConnection()->commit();
            $errorCode = 1;
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            $this->getConnection()->rollBack();
            throw new \PDOException($e);
        }

        return $errorCode;

    }

    public function removeWritting($data)
    {
        $sentencia = $this->getConnection()->prepare("DELETE FROM `writtings` WHERE `id`=?;");
        $mapping = array($data['id']);
        try {
            $sentencia->execute($mapping);
            $errorCode = 1;
            $this->getConnection()->commit();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            $this->getConnection()->rollBack();
            throw new \PDOException($e);
        }

        return $errorCode;
    }

    public function registerMusa($musa)
    {
        $musas = $this->getMusasLike($musa);
        if (!empty($musas)) {
            foreach ($musas as $musaId => $existingMusa) {
                if (0 == strcmp($musa, str_replace("\n", "", $existingMusa))) {
                    return $musaId;
                }
            }
        }

        $sentencia = $this->getConnection()->prepare("INSERT INTO `musas` (`name`) VALUES (?)");
        $mapping = array(strtolower(trim($musa,"\r")));
        try {
            $sentencia->execute($mapping);
            $lastId = $this->getConnection()->lastInsertId();
            $this->getConnection()->commit();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            $this->getConnection()->rollBack();
            throw new \PDOException($e);
        }

        return $lastId;


    }

    public function getMusasLike($str)
    {
        $sentencia = $this->getConnection()->prepare("SELECT `name`, `id`
                FROM `musas`
                WHERE `name` LIKE ?");

        $mapping = array(trim($str).'%');

        try {
            $sentencia->execute($mapping);
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($results = $sentencia->fetchAll()) {
            foreach ($results as $result) {
                $musas[$result[1]] = $result[0];
            }
        } else {
            return null;

        }

        return $musas;

    }

    public function getMusasPublicationsIds()
    {
        $sentencia = $this->getConnection()->prepare("SELECT `id_musa`, count(*) as `num`
                FROM `publications_musas`
                GROUP BY id_musa;");

        try {
            $sentencia->execute();
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($results = $sentencia->fetchAll()) {
            foreach ($results as $result) {
                $publications[$result['id_musa']] = $result['num'];
            }
        } else {
            $publications = null;

        }

        return $publications;
    }

    public function getMusaPublications($idMusa)
    {
        $sentencia = $this->getConnection()->prepare("
                SELECT c.`name` as contributor,
                       c.id as userid,
                       w.`title` as title,
                       w.`body` as body
                FROM amimusa.publications_musas pm
                JOIN publications p ON pm.id_publication = p.id
                JOIN writtings w ON p.id_writting = w.id
                JOIN contributors c ON p.id_contributor = c.id
                WHERE pm.id_musa = ?;
                ");

        $mapping = array($idMusa);

        try {
            $sentencia->execute($mapping);
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($results = $sentencia->fetchAll()) {
            foreach ($results as $k => $result) {
                foreach ($result as $column => $value) {
                    if (!is_numeric($column)) {
                        $publications[$k][$column] = $value;
                    }

                }

            }
        } else {
            $publications = null;

        }

        return $publications;
    }

    public function getMusas()
    {
        $musasPublicationsIds = $this->getMusasPublicationsIds();
        if (!empty($musasPublicationsIds)) {
            foreach($musasPublicationsIds as $musaId => $count) {
                $musas[$musaId] = array(
                        'name' => $this->getMusa($musaId),
                        'count' => $count
                );
            }
        } else {
            $musas = null;
        }

        return $musas;
    }

    public function getMusa($musaId)
    {
        $sentencia = $this->getConnection()->prepare("SELECT `id`, `name` FROM `musas` WHERE `id` = ?");
        $mapping = array($musaId);
        try {
            $sentencia->execute($mapping);
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        $result = $sentencia->fetch();

        $musa = trim($result['name']);

        return $musa;
    }
    public function getContributorMusas($userId)
    {
        $sentencia = $this->getConnection()->prepare("
                SELECT distinct(m.`name`), m.`id` FROM `musas` m
                JOIN `publications_musas` pm ON m.`id`= pm.`id_musa`
                JOIN `publications` p ON pm.`id_publication` = p.`id`
                WHERE p.`id_contributor` = ?;
                ");
        $mapping = array($userId);
        try {
            $sentencia->execute($mapping);
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        if ($sentencia->rowCount() > 0) {
            $result = $sentencia->fetchAll();
            foreach ($result as $r) {
                $res[] = array(
                        'id' => $r['id'],
                        'name' => $r['name']
                );
            }
        } else {
            $res = array();
        }

        return $res;

    }

    public function getUserContributions($userId)
    {
        $sentencia = $this->getConnection()->prepare("
                SELECT c.`id` as id_contributor, w.`id` as id_writting, w.`title`
                FROM `writtings` w
                JOIN `publications` p ON p.`id_writting` = w.`id`
                JOIN `contributors` c ON c.`id`= p.`id_contributor`
                WHERE p.`id_contributor` = ?;
                ");
        $mapping = array($userId);
        try {
            $sentencia->execute($mapping);
        } catch(\PDOException  $e) {
            $errorCode = (int)$e->getCode();
            $error[$errorCode] = $e->getMessage();
            throw new \PDOException($e);
        }

        $res = array();
        $result = $sentencia->fetchAll();
        foreach ($result as $r) {
            $res[] = array(
                    'idContributor' => $r['id_contributor'],
                    'idWritting' => $r['id_writting'],
                    'title' => $r['title'],
            );
        }
        return $res;
    }

    public function render($template, $data = array())
    {
        $tpl = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/views/'.$template.'.htpl');
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $tpl = str_replace('###'.$k.'###', $v, $tpl);
            }
        }
        return $tpl;
    }

    public function renderContributionsRows($userContributions)
    {
        $tpl = '';
        foreach ($userContributions as $contribution) {
            $tpl  .= '<tr>';
            $tpl  .= '<td>' . $contribution['title'] . '</td>';
            $tpl  .= '<td  class="text-center"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>&nbsp;<a href="/index.php?target=edit-contribution&id='.$contribution['idWritting'].'">Edit</a></td>';
            $tpl  .= '<td  class="text-center"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;<a href="/index.php?target=remove-contribution&id='.$contribution['idWritting'].'">Remove</a></td>';
            $tpl  .= '</tr>';
        }

        return $tpl;

    }



}