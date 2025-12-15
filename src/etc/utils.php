<?php
function old($key, $default=null) {
    $result = $default;
    if (isset($_SESSION["form-data"])) {
        $data = $_SESSION["form-data"];
        if (is_array($data) && array_key_exists($key, $data)) {
            $result = $data[$key];
        }
    }
    return $result;
}
  
function error($key) {
    $result = null;
    if (isset($_SESSION["form-errors"])) {
        $errors = $_SESSION["form-errors"];
        if (is_array($errors) && array_key_exists($key, $errors)) {
            $result = $errors[$key];
        }
    }
    return $result;
}

function chosen($key, $search, $default=null) {
    $result = FALSE;
    if (isset($_SESSION["form-data"])) {
        $data = $_SESSION["form-data"];
        if (is_array($data) && array_key_exists($key, $data)) {
            $value = $data[$key];
            if (is_array($value)) {
                $result = in_array($search, $value);
            }
            else {
                $result = strcmp($value, $search) === 0;
            }
        }
    }
    else if ($default !== null) {
        if (is_array($default)) {
            $result = in_array($search, $default);
        }
        else {
            $result = strcmp($default, $search) === 0;
        }
    }
    return $result;
}

function clearFormData() {
    if (isset($_SESSION["form-data"])) {
        unset($_SESSION["form-data"]);
    }
    if (isset($_SESSION["form-errors"])) {
        unset($_SESSION["form-errors"]);
    }
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function dd($var) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
    die();
}

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function h($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
?>