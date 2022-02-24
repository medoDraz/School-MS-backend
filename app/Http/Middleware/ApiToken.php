<?php


namespace App\Http\Middleware;
use App\User;
use Closure;


class ApiToken
{
    public $attributes;
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if (is_null($request->bearerToken())) {
            return $this->sendError('No Such Author..' . $request->bearerToken(), '', $code = 401);
        }
        $author = User::where('api_token', $request->bearerToken())->where('status', 1)->first();
        if (is_null($author)) {
            return $this->sendError('No Such Author..' . $request->bearerToken(), '', $code = 401);
        }
        $request->merge(['user' => $author]);
        return $next($request);
    }

    public function sendResponse($result, $message)
    {
        $response = [
            'error' => false,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 200)
    {
        $response = [
            'error' => true,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $respone['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
