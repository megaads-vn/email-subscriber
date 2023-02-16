<?php
    $currentPage = isset($pageType) ? $pageType : "";
    $currentUrl = isset($currentUrl) ? $currentUrl : "";
    $storeId = -1;
    try {
        if ($currentPage === 'store' && !empty($currentUrl)) {
            preg_match('/\/store\/([\w+\-\.\d+]+)/i', $currentUrl, $matched);
            $slug = isset($matched[1]) ? $matched[1] : "";
            $table = config('subscriber.store_table');
            if (!empty($slug) && !empty($table)) {
                $findStore = DB::table($table)->where('slug', $slug)->first(['id']);
                if (!empty($findStore)) {
                    $storeId = $findStore->id;
                }
            }
        }
    } catch (Exception $ex) {
        \Illuminate\Support\Facades\Log::error('EMAIL_SUBSCRIBE_ERROR: ' . $ex->getMessage() . '. File ' . $ex->getFile() . ' Line ' . $ex->getLine());
    }
?>
<div class="subscribe-form-wrapper">
    <style type="text/css">
        .subscribe-form-wrapper * {
            box-sizing: border-box;
        }
        .subscribe-form-wrapper {
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10000;
            width: 100%;
            height: 100%;
            opacity: 0;
            pointer-events: none;
            transition: opacity 250ms ease-in-out
        }

        .subscribe-form-wrapper.active {
            opacity: 1;
            pointer-events: all;
        }

        .subscribe-form-content {
            width: 92%;
            max-width: 500px;
            background-color: #fff;
            padding: 24px 16px;
            border-radius: 5px;
            box-shadow: 0 5px 25px rgba(11 11 11 / 11%);
            z-index: 2;
            position: absolute;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            line-height: 1.1;
            text-align: center;
            user-select: none;
        }

        .subscribe-form-close {
            position: absolute;
            width: 24px;
            height: 24px;
            display: flex;
            justify-items: center;
            align-items: center;
            position: absolute;
            top: 5px;
            right: 5px;
            color: #999;
            cursor: pointer;
        }

        .subscribe-form-close:hover {
            color: #222;
        }

        .subscribe-form-background {
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10000;
            width: 100%;
            height: 100%;
            background-color: rgba(11 11 11 / 44%);
            z-index: 1;
        }

        .sign-up {
            font-size: 31px;
        }

        .subscribe-text {
            color: #d10000;
            text-transform: uppercase;
            font-size: 31px;
            display: flex;
            justify-items: center;
            align-items: center;
            flex-direction: column;
            line-height: 1;
            padding: 10px;
        }

        .subscribe-text strong {
            font-size: 51px;
        }

        .form-site {
            color: #333;
            letter-spacing: 1px;
            font-size: 25px;
            text-transform: uppercase;
        }

        .subscribe-form-wrapper .form {
            padding: 20px;
            text-align: left;
        }

        .subscribe-form-wrapper .form label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        .subscribe-form-wrapper .form input {
            width: 100%;
            padding: 12px;
            border: 2px solid rgb(25, 117, 142);
            font-size: 17px;
            border-radius: 5px;
        }

        .subscribe-form-wrapper .form button {
            width: 100%;
            padding: 20px 10px;
            font-size: 18px;
            border-radius: 5px;
            border: none;
            outline: none;
            cursor: pointer;
            background: #0e5f77;
            color: #fff;
            box-shadow: 0px 2px 2px #ddd;
            margin-top: 12px;
            transition: background-color 250ms ease-in-out;
        }
        .subscribe-form-wrapper .form button:hover {
            background-color: #096fa9;
        }
        .subscribe-message {
            width: 100%;
            top: 100%;
            left: 0;
            padding: 6px 0;
            text-align: left
        }
        .subscribe-success {
            color: green
        }
        .subscribe-error {
            color: red
        }
    </style>
    <div id="subscribeForm" class="subscribe-form-content">
        <span class="subscribe-form-close"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"> <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/> </svg></span>
        <a href="/" target="_blank" style="border: none; text-decoration: none; width: 370px; height: 60px; margin: 0 auto 10px; display: block;">
            <img src="{{ config('subscriber.app_logo') }}" width="245" height="40" alt="{{ config('app.siteName')  }}" style="display: block; margin: 0 auto; color: #111; font-size: 12px;">
        </a>
        <div>
            <div class="sign-up"> Subscribe to receive </div>
            <div class="subscribe-text">
                <strong>Latest</strong>
                <span>coupon codes</span>
            </div>
            <div class="form-site"> From {{ config('app.siteName', '') }} </div>
            <form class="form" action="/activity/subscribe" method="post">
                <div class="input">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" placeholder="john.doe@gmail.com: {{ $currentPage }}">
                </div>
                <div class="button-popup">
                    <button type="submit">Subscribe</button>
                </div>
                <div class="subscribe-message">
                    <div class="subscribe-success" style="display: none;">Thank you for signing up for newsletter via email, from now you will receive the latest information from us.</div>
                    <div class="subscribe-error" style="display: none;">Invalid email address format.</div>
                </div>
            </form>
        </div>
    </div>
    <span class="subscribe-form-background"></span>
    <script type="text/javascript">
        function checkShowSubscribePopup () {
            var currentDate = new Date();
            var localObject = JSON.parse(window.localStorage.getItem("{{ config('subscriber.popup_key')  }}"));
            var expirationDate = localObject?.expiresAt;
            var checkSubscribe = window.localStorage.getItem("{{ config('subscriber.popup_subscribe_key')  }}");
            return (expirationDate == null || Date.parse(currentDate) >= Date.parse(expirationDate)) && checkSubscribe == null
        }

        function updateLocalStorage() {
            window.localStorage.removeItem("{{ config('subscriber.popup_key')  }}");
            var expires = new Date();
            expires.setDate(expires.getDate() + 1);
            var localObject = {
                expiresAt: expires,
                hasPopup: true
            }
            window.localStorage.setItem("{{ config('subscriber.popup_key')  }}", JSON.stringify(localObject));
        }

        let elementABC = document.querySelector('.subscribe-form-wrapper');
        document.addEventListener('scroll', function() {
            if (!elementABC.classList.contains('active')) {
                if(checkShowSubscribePopup()) {
                    elementABC.classList.add('active');
                    updateLocalStorage();
                }
            }
        });

        document.querySelector('.subscribe-form-close').addEventListener('click', function() {
            if (elementABC.classList.contains('active')) {
                elementABC.classList.remove('active');
            }
        });

        document.querySelector('.form').addEventListener('submit', function(event) {
            event.preventDefault();
            var em = document.getElementById('email').value;
            var messageSuccess = document.getElementsByClassName('subscribe-success')[0];
            var messageError = document.getElementsByClassName('subscribe-error')[0];
            var storeId = <?= $storeId ?>;
            if(typeof(em) != 'undefined' && em != '' && em != null) {
                var retVal = {
                    email: em,
                    prefered: window.location.href
                };
                if (storeId > 0) {
                    retVal.store = storeId;
                }

                var xhr = new XMLHttpRequest();
                xhr.open("POST", '/activity/subscribe', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onreadystatechange = function () {
                    if (this.readyState != 4) return;
                    messageSuccess.style.display = "block";
                    messageError.style.display = "block";
                    if (this.status == 200) {
                        var data = JSON.parse(this.responseText);
                        document.getElementById('email').value = "";
                        messageSuccess.innerHTML = 'Thank for your subscribe!';
                        messageError.innerHTML = '';
                    }
                    window.localStorage.setItem("{{ config('subscriber.popup_subscribe_key')  }}", 'true');
                    setTimeout(() => {
                        messageError.innerHTML = '';
                        messageSuccess.innerHTML = '';
                        messageSuccess.style.display = "none";
                        messageError.style.display = "none";
                        elementABC.classList.remove('active');
                    }, 2000);
                };
                xhr.send(JSON.stringify(retVal));
            } else {
                messageError.innerHTML = 'Please enter your email!';
                messageError.style.display = "block";
                messageSuccess.innerHTML = '';
                setTimeout(() => {
                    messageError.innerHTML = '';
                    messageSuccess.innerHTML = '';
                }, 2000);
            }
        });
    </script>
</div>