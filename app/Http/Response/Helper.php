<?php

namespace App\Http\Response;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait Helper
{
    /**
     * @return Response
     */
    protected function null()
    {
        return new Response([
            'code' => 200,
            'message' => config('error.#200.message'),
        ]);
    }

    /**
     * @param array $array
     * @return Response
     */
    protected function array(array $array = [])
    {
        return new Response([
            'code' => 200,
            'message' => config('error.#200.message'),
            'data' => $array,
        ]);
    }


    protected function success_data($msg, $data = '',$status = 1)
    {
        return array('status' => $status, 'msg' => $msg, 'data' => $data);
    }

    protected function error_data($msg, $data = '',$status = 0)
    {
        return array('status' => $status, 'msg' => $msg, 'data' => $data);
    }

    /**
     * @param int $code
     * @param string $message
     * @param int $statusCode
     */
    protected function error(int $code = 500, string $message = null, int $statusCode = 500)
    {
        if (!$message) {
            $message = config("error.#{$code}.message");
            $statusCode = config("error.#{$code}.status_code");
        }

        throw new HttpException($statusCode, $message, null, [], $code);
    }
}
