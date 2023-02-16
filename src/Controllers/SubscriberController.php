<?php

namespace Megaads\EmailSubscriber\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class SubscriberController extends Controller
{
    /**
     * @param Request $request
     * @return void
     */
    public function subscribeEmail(Request $request)
    {
        $resonse = [
            "status" => "fail"
        ];
        try {
            $params = [
              "email" => $request->get("email"),
              "site" => config('subscriber.app_name'),
              "refer" => $request->get('prefered'),
              "token" => config('subscriber.token'),  
            ];

            if ($request->has('store')) {
                $params['storeId'] = $request->get('store');
            }

            $resonse = $this->triggerSyncRequest('https://coupon.megaads.vn/api/email-marketing/create', 'POST', $params);
        } catch (\Exception $ex) {
            $resonse["message"] = "Has some error! " . $ex->getMessage();
            Log::error("SUBSCRIBE_EMAIL: " . $ex->getMessage() . " File " . $ex->getFile() . ". Line " . $ex->getLine());
        }
        return Response::json($resonse);
    }

    public function unsubscribeEmail(Request $request) {
        if (!$request->has('email')) {
            abort(404);
        }
        $params = array(
            "email" => $request->get('email'),
            'site' => config('subscriber.app_name'),
            'token' => 'ajsdf435kjdsjf43t343'
        );
        $curlResponse = $this->triggerSyncRequest('https://coupon.megaads.vn/api/email-marketing/unsubcribe', 'POST', $params);

        return View::make('email-subscriber::unsubscribe');
    }

    public function readPackageConfig(Request $request) {
        $packageConfig = config_path('subscriber.php');
        if (!file_exists($packageConfig)) {
            $source = dirname(__FILE__) . '/../../config/subscriber.php';
            copy($source, $packageConfig);
        }
        $content = include $packageConfig;
        if (isset($content['token'])) {
            unset($content['token']);
        }
        if (isset($content['basicAuthentication'])) {
            unset($content['basicAuthentication']);
        }
        return Response::json($content);
    }

    public function savePackageConfig(Request $request) {
        $packageConfig = config_path('subscriber.php');
        if (!file_exists($packageConfig)) {
            throw new \Exception("Package config file does not exists. Please create first");
        } else {
            $content = include $packageConfig;
            $input = $request->all();
            foreach ($input as $key => $val) {
                if (isset($content[$key]) && strpos($key, '.') <= 0 ) {
                    $content[$key] = $val;
                } else if (!isset($content[$key]) &&  strpos('.', $key) >= 0) {
                    $arrayConfig = explode(".", $key);
                    if (isset($content[$arrayConfig[0]]) && isset($content[$arrayConfig[0]][$arrayConfig[1]])) {
                        $content[$arrayConfig[0]][$arrayConfig[1]] = $val;
                    }
                }
            }
            $this->saveToFileConfig($packageConfig, $content);
        }
        return Response::json(['status' => 'successful']);
    }

    private function triggerSyncRequest($url, $method = 'GET', $params = [], $headers = [])
    {
        $ch = curl_init();
        $timeout = 2;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data, true);
    }

    private function saveToFileConfig($filePath, $content) {
        file_put_contents($filePath,
            "<?php\n return "
            .var_export($content, true)
            .";\n?>"
        );

    }
}