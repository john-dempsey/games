<?php

class JsonResponse {
    public static function success($data, $message = null, $httpCode = 200) {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public static function error($message, $httpCode = 400, $validationErrors = null) {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($validationErrors !== null) {
            $response['validation_errors'] = $validationErrors;
        }

        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
