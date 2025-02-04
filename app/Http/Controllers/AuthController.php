<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserConfirmRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    use HasApiTokens;
    private $smsApiKey = '5F0473CB-2FFD-5946-44E3-864B3A202FC0';

    public function register(UserRegistrationRequest $request): JsonResponse
    {
        $smsCode = rand(1000, 9999);
        $this->sendSms($request->phone, "Ваш код подтверждения: $smsCode");

        User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'phone' => $request->phone,
            'sms_code' => $smsCode,
        ]);
        return response()->json(['message' => 'SMS-код отправлен на ваш номер.'], 200);
    }

    private function sendSms($phone, $message): void
    {
        $ch = curl_init("https://sms.ru/sms/send");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            "api_id" => $this->smsApiKey,
            "to" => $phone,
            "msg" => $message,
            "json" => 1
        )));

        $body = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($body);
        if ($json) {
            if ($json->status == "OK") {
                foreach ($json->sms as $phone => $data) {
                    if ($data->status == "OK") {
                        echo "Сообщение на номер $phone успешно отправлено. ";
                        echo "ID сообщения: $data->sms_id. ";
                    } else {
                        echo "Сообщение на номер $phone не отправлено. ";
                        echo "Код ошибки: $data->status_code. ";
                        echo "Причина: $data->status_text. ";
                    }
                }
            } else {
                echo "Ошибка при отправке SMS: " . $json->message ;
            }
        } else {
            echo "Не удалось получить ответ от SMS API.";
        }
    }

    public function confirm(UserConfirmRequest $request): JsonResponse
    {

        $phone = $request->phone;
        $user = User::where('phone', $phone)->first();

        if (!$user || $user->sms_code !== $request->code) {
            return response()->json(['message' => 'Неверный код.'], 403);
        }

        $user->is_active = true;
        $user->sms_code = null;
        $user->tokens()->delete();
        $user->save();

        return response()->json([
            'message' => 'Номер телефона подтвержден.',
            'token' => $user->createToken('auth_token')->plainTextToken],
            200);
    }
    public function login(UserLoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->get('phone'))->first();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден.'], 404);
        }

        $smsCode = rand(1000, 9999);
        $this->sendSms($user->phone, "Ваш код подтверждения: $smsCode");

        $user->update([
            'sms_code' => $smsCode,
        ]);

        return response()->json(['message' => 'SMS-код отправлен на ваш номер.'], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()->tokens()->delete()) {
            return response()->json([
                'message' => 'Успешный выход'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Что-то не так, попробуйте еще раз'
            ], 500);
        }
    }
}
