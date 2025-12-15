<?php

class JsonResponse {
    public static function success($data, $message = null) {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public static function error($message, $code, $httpCode = 400, $validationErrors = null) {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code
            ]
        ];

        if ($validationErrors !== null) {
            $response['error']['validation_errors'] = $validationErrors;
        }

        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public static function validationError($errors) {
        self::error('Validation failed', 'VALIDATION_ERROR', 422, $errors);
    }
}
