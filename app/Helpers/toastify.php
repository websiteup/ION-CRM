<?php

if (!function_exists('notify')) {
    function notify()
    {
        return new \App\Helpers\ToastifyHelper();
    }
}

