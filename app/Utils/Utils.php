<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\SiswaTest;
use App\Models\SiswaTestQuestion;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserPackage;
use Carbon\Carbon;
use DOMXPath;
use Error;
use ErrorException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Utils
{

    static public function isAdmin()
    {
        return Auth::user()->role_id == Role::ADMIN;
    }

    static public function isUser()
    {
        return Auth::user()->role_id == Role::USER;
    }

    static public function dateReadable($date, $format = 'dddd, DD MMMM YYYY - HH:mm')
    {
        return Carbon::parse($date, config('app.timezone'))->isoFormat($format);
    }

    static public function dateReadableShort($date, $format = 'ddd, DD MMM YYYY - HH:mm:ss')
    {
        return Carbon::parse($date, config('app.timezone'))->isoFormat($format);
    }

    static public function minutesSpent($start, $end)
    {
        $startAt = Carbon::make($start);
        $endAt = Carbon::make($end);

        $minutes = $startAt->diffInMinutes($endAt);
        return (int) $minutes;
    }

    static function randomStr(
        int $length = 64,
        // string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
        string $keyspace = 'abcdefghijklmnopqrstuvwxyz'
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
