<?php
namespace Megaads\EmailSubscriber\Middlewares;

use Closure;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class HtmlInjectionMiddleware
{
    protected $viewPath;

    public function __construct() {
        $this->viewPath = dirname(__FILE__) . '/../Views';
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $this->currentRouteName();
        $response = $next($request);
        if (config('subscriber.enable')) {
            $this->modifyResponse($request, $response);
        }
        return $response;
    }

    protected function modifyResponse($request, &$response) {
        $acceptHeaders = [
            'text/html; charset=UTF-8',
            'text/html'
        ];
        $contentType = $response->headers->get('Content-Type');

        if (!empty($contentType) && !in_array($contentType, $acceptHeaders)) {
            return $response;
        } else if (!$request->ajax()) {
            $content = $response->getContent();
            if (config('subscriber.show_popup')) {
                $bodyPos = strripos($content, '</body>');
                if (false !== $bodyPos) {
                    $popupView = View::make('email-subscriber::popup-subscribe')->render();
                    $content = substr($content, 0, $bodyPos) . $popupView . substr($content, $bodyPos);
                }
            }
            $appendDefault = config('subscriber.append_form_before');
            $showPos = $this->showFormInStore();
            if (!empty($appendDefault) && $showPos == 'default' && config('subscriber.store_subscribe_form'))  {
                $appendPos = strripos($content, $appendDefault);
                if (false !== $appendPos) {
                    $form = View::make('email-subscriber::subscribe-form')->render();
                    $content = substr($content, 0, $appendPos) . $form . substr($content, $appendPos);
                }
            } else if ($showPos == 'inItem' && config('subscriber.store_coupon_item_subscribe_form')) {
                $index = 0;
                $content = preg_replace_callback(config('subscriber.append_store_item_before.element'), function($matches) use(&$index) {
                    $index++;
                    return '<div class="' . trim($matches[1]) . ' custom-index-' . $index . ' ">';
                }, $content);
                preg_match_all(config('subscriber.append_store_item_before.element'), $content, $matches);
                if (isset($matches[0]) && isset($matches[0][config('subscriber.append_store_item_before.position')])) {
                    $appendPos = strripos($content, $matches[0][config('subscriber.append_store_item_before.position')]);
                    if (false !== $appendPos) {
                        $form = View::make('email-subscriber::subscribe-form')->render();
                        $content = substr($content, 0, $appendPos) . $form . substr($content, $appendPos);
                    }
                }
            }

            // Update the new content and reset the content length
            $response->setContent($content);
            $response->headers->remove('Content-Length');
        }
    }

    private function currentRouteName() {
        $routeName = "";
        $currentUrl = Request::url();
        if (preg_match('/store\//i', $currentUrl, $matched)) {
            $routeName = "store";
        }
//        $appVersion = app()->version();
//        preg_match('/\d+\.\d+/i', $appVersion, $matched);
//        $matchVersion = isset($matched[0]) ? $matched[0] : 0;
//        switch ($matchVersion) {
//            case "5.2":
//                    $routeName = Route::currentRouteName();
//                break;
//            default:
//
//
//        }
        View::share('pageType', $routeName);
        View::share('currentUrl', $currentUrl);
    }

    private function showFormInStore() {
        $retVal = "default";
        try {
            $currentUrl = Request::url();
            if (preg_match('/\/store\/([\w+\-\.\d+]+)/i', $currentUrl, $matched)) {
                $table = config('subscriber.store_table');
                if (!empty($table) && isset($matched[1])) {
                    $findStore = \DB::table($table)->where('slug', $matched[1])->first(['id']);
                    if (!empty($findStore)) {
                        $configTable = config('subscriber.config_table');
                        $config = \DB::table($configTable)->where('key', 'site.storeSubscribe')->first(['value']);
                        if (!empty($config)) {
                            $listStoreId = explode(',', $config->value);
                            if (in_array($findStore->id, $listStoreId)) {
                                $retVal = "inItem";
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            \Log::error('EMAIL_SUBSCRIBE: ' . $ex->getMessage() . ' File ' . $ex->getFile() . ' Line ' . $ex->getLine());
        }
        return $retVal;
    }

}