<?php
header('Content-Type: text/html; charset=UTF-8');

$user = 'u52807';
$pass = '8865176';
$db = new PDO('mysql:host=localhost;dbname=u52807', $user, $pass, [PDO::ATTR_PERSISTENT => true]);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();
  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages[] = 'Спасибо, результаты сохранены.';
    if (!empty($_COOKIE['password'])) {
      $messages[] = sprintf(' Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
        и паролем <strong>%s</strong> для изменения данных.',
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['password']));
    }
    setcookie('login', '', 100000);
    setcookie('password', '', 100000);
  }

  $errors = array();
  $errors['name'] = !empty($_COOKIE['name_error']);
  $errors['year'] = !empty($_COOKIE['year_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['limbs'] = !empty($_COOKIE['limbs_error']);
  $errors['gender'] = !empty($_COOKIE['gender_error']);
  $errors['abilities'] = !empty($_COOKIE['abilities_error']);
  $errors['biography'] = !empty($_COOKIE['biography_error']);
  $errors['checkbox'] = !empty($_COOKIE['checkbox_error']);

  if ($errors['name']) {
    setcookie('name_error', '', 100000);
    $messages[] = '<div class="error">Заполните имя.</div>';
  }

  if ($errors['year']) {
    setcookie('year_error', '', 100000);
    $messages[] = '<div class="error">укажите год.</div>';
  }

  if ($errors['email']) {
    setcookie('email_error', '', 100000);
    $messages[] = '<div class="error">Заполните email конкретно.</div>';
  }

  if ($errors['limbs']) {
    setcookie('limbs_error', '', 100000);
    $messages[] = '<div class="error">укажите конечности.</div>';
  }

  if ($errors['gender']) {
    setcookie('gender_error', '', 100000);
    $messages[] = '<div class="error">Заполните пол.</div>';
  }

  if ($errors['abilities']) {
    setcookie('abilities_error', '', 100000);
    $messages[] = '<div class="error">Заполните сверхспособности.</div>';
  }

  if ($errors['biography']) {
    setcookie('biography_error', '', 100000);
    $messages[] = '<div class="error">Биография пустая/слишком длинная.</div>';
  }

  if ($errors['checkbox']) {
    setcookie('checkbox_error', '', 100000);
    $messages[] = '<div class="error">Check the box.</div>';
  }

  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['name_value']));
  $values['year'] = empty($_COOKIE['year_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['year_value']));
  $values['email'] = empty($_COOKIE['email_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['email_value']));
  $values['limbs'] = empty($_COOKIE['limbs_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['limbs_value']));
  $values['gender'] = empty($_COOKIE['gender_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['gender_value']));
  $values['abilities'] = empty($_COOKIE['abilities_value']) ? [] : json_decode($_COOKIE['abilities_value']);
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['biography_value']));
  $values['checkbox'] = empty($_COOKIE['checkbox_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['checkbox_value']));

  if (count(array_filter($errors)) === 0 && !empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
    $login = $_SESSION['login'];
    try {
      $stmt = $db->prepare("SELECT person_id FROM users WHERE login = ?");
      $stmt->execute([$login]);
      $person_id = $stmt->fetchColumn();

      $stmt = $db->prepare("SELECT name, year, email, limbs, gender, checkbox, biography FROM application WHERE id = ?");
      $stmt->execute([$person_id]);
      $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $stmt = $db->prepare("SELECT sup_id FROM connects WHERE person_id = ?");
      $stmt->execute([$person_id]);
      $abilities = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

      if (!empty($dates[0]['name'])) {
        $values['name'] = htmlspecialchars($dates[0]['name']);
      }
      if (!empty($dates[0]['year'])) {
        $values['year'] = htmlspecialchars($dates[0]['year']);
      }
      if (!empty($dates[0]['email'])) {
        $values['email'] = htmlspecialchars($dates[0]['email']);
      }
      if (!empty($dates[0]['limbs'])) {
        $values['limbs'] = htmlspecialchars($dates[0]['limbs']);
      }
      if (!empty($dates[0]['gender'])) {
        $values['gender'] = htmlspecialchars($dates[0]['gender']);
      }
      if (!empty($dates[0]['checkbox'])) {
        $values['checkbox'] = htmlspecialchars($dates[0]['checkbox']);
      } 
      if (!empty($dates[0]['biography'])) {
        $values['biography'] = htmlspecialchars($dates[0]['biography']);
      } 
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
    printf('<header><p>Вход с логином %s; uid: %d</p><a href=logout.php>Выйти</a></header>', $_SESSION['login'], $_SESSION['uid']);
  }
  include('form.php');
} else {
  $errors = FALSE;
  if (empty($_POST['name'])) {
    $errors = TRUE;
    setcookie('name_error', '1', time() + 24 * 60 * 60);
  } else {
    setcookie('name_value', $_POST['name'], time() + 30 * 24 * 60 * 60);
  }
  if (empty($_POST['year']) || !is_numeric($_POST['year']) || (int)$_POST['year']<=1923 || (int)$_POST['year']>=2024) {
    $errors = TRUE;
    setcookie('year_error', '1', time() + 24 * 60 * 60);
  } else {
      setcookie('year_value', $_POST['year'], time() + 30 * 24 * 60 * 60);
  }
  if (empty($_POST['email']) || !preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $_POST['email'])){
    $errors = TRUE;
    setcookie('email_error', '1', time() + 24 * 60 * 60);
  } else {
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
  }
  if ($_POST['gender'] !== 'male' && $_POST['gender'] !== 'female'){
    $errors = TRUE;
    setcookie('gender_error', '1', time() + 24 * 60 * 60);
  } else {
    setcookie('gender_value', $_POST['gender'], time() + 30 * 24 * 60 * 60);
  }
  if ($_POST['limbs'] !== '1' && $_POST['limbs'] !== '2' && $_POST['limbs'] !== '3' && $_POST['limbs'] !== '4'){  
    $errors = TRUE;
    setcookie('limbs_error', '1', time() + 24 * 60 * 60);
  } else {
    setcookie('limbs_value', $_POST['limbs'], time() + 30 * 24 * 60 * 60);
  }
  if (empty($_POST['abilities']) || !is_array($_POST['abilities'])) {
    $errors = TRUE;
    setcookie('abilities_error', '1', time() + 24 * 60 * 60);
  } else {
    setcookie('abilities_value', json_encode($_POST['abilities']), time() + 30 * 24 * 60 * 60);
  }
  if (empty($_POST['biography']) || strlen($_POST['biography']) >128){
    $errors = TRUE;
    setcookie('biography_error', '1', time() + 24 * 60 * 60);
  } else{
    setcookie('biography_value', $_POST['biography'], time() + 30 * 24 * 60 * 60);
  }
  if ($_POST['checkbox']==''){
    $errors = TRUE;
    setcookie('checkbox_error', '1', time() + 24 * 60 * 60);
  } else {
    setcookie('checkbox_value', $_POST['checkbox'], time() + 30 * 24 * 60 * 60);
  }

  if ($errors) {
    header('Location: index.php');
    exit();
  } else {
    setcookie('name_error', '', 100000);
    setcookie('year_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('gender_error', '', 100000);
    setcookie('limbs_error', '', 100000);
    setcookie('abilities_error', '', 100000);
    setcookie('checkbox_error', '', 100000); 
    setcookie('biography_error', '', 100000); 
  }

  if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
    if (!empty($_POST['token']) && hash_equals($_POST['token'], $_SESSION['token'])) {
      $login = $_SESSION['login'];
      try {
        $stmt = $db->prepare("SELECT person_id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $person_id = $stmt->fetchColumn();

        $stmt = $db->prepare("UPDATE application SET name = ?, year = ?, email = ?, limbs = ?, gender = ?, biography = ?, checkbox = ?
          WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['year'], $_POST['email'], $_POST['limbs'], $_POST['gender'], $_POST['biography'], $_POST['checkbox'], $person_id]);

        $stmt = $db->prepare("SELECT sup_id FROM connects WHERE person_id = ?");
        $stmt->execute([$person_id]);
        $abil = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        if (array_diff($abil, $_POST['abilities'])) {
          $stmt = $db->prepare("DELETE FROM connects WHERE person_id = ?");
          $stmt->execute([$person_id]);

          $stmt = $db->prepare("INSERT INTO connects (person_id, sup_id) VALUES (?, ?)");
          foreach ($_POST['abilities'] as $superpower_id) {
            $stmt->execute([$person_id, $superpower_id]);
          }
        }
      } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
      }
    } else {
      die('Ошибка CSRF: недопустимый токен');
    }
  } else {
    $login = 'user' . rand(1, 1000);
    $password = rand(1, 100);
    setcookie('login', $login);
    setcookie('password', $password);
    try {
      $stmt = $db->prepare("INSERT INTO application (name, year, email, limbs, gender, biography, checkbox) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$_POST['name'], $_POST['year'], $_POST['email'], $_POST['limbs'], $_POST['gender'], $_POST['biography'], $_POST['checkbox']]);
      $person_id = $db->lastInsertId();
      $stmt = $db->prepare("INSERT INTO connects (person_id, sup_id) VALUES (?, ?)");
      foreach ($_POST['abilities'] as $superpower_id) {
        $stmt->execute([$person_id, $superpower_id]);
      }
      $stmt = $db->prepare("INSERT INTO users (person_id, login, password) VALUES (?, ?, ?)");
      $stmt->execute([$person_id, $login, md5($password)]);
    } catch (PDOException $e) {
      print('Error : ' . $e->getMessage());
      exit();
    }
  }

  setcookie('save', '1');
  header('Location: ./');
}
