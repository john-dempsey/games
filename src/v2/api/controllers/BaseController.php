<?php

class BaseController {
    protected function getJsonInput() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            JsonResponse::error('Invalid JSON', 'INVALID_JSON', 400);
        }

        return $data ?: [];
    }

    protected function validate($data, $rules) {
        $validator = new Validator($data, $rules);

        if ($validator->fails()) {
            JsonResponse::validationError($validator->errors());
        }

        return true;
    }

    protected function sendSuccess($data, $message = null, $code = 200) {
        http_response_code($code);
        JsonResponse::success($data, $message);
    }

    protected function sendError($message, $errorCode, $httpCode = 400) {
        http_response_code($httpCode);
        JsonResponse::error($message, $errorCode, $httpCode);
    }
}
