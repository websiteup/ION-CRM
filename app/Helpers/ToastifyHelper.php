<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class ToastifyHelper
{
    public function success($message)
    {
        Session::flash('toastify', [
            'type' => 'success',
            'message' => $message,
        ]);
        return $this;
    }

    public function error($message)
    {
        Session::flash('toastify', [
            'type' => 'error',
            'message' => $message,
        ]);
        return $this;
    }

    public function info($message)
    {
        Session::flash('toastify', [
            'type' => 'info',
            'message' => $message,
        ]);
        return $this;
    }

    public function warning($message)
    {
        Session::flash('toastify', [
            'type' => 'warning',
            'message' => $message,
        ]);
        return $this;
    }
}

