<style>
    .pkg-subscribe-wrapper {
        width: 100%;
        padding-right: 25px;
        border-radius: 3px;
        border: 1px solid rgba(0, 0, 0, .15);
        box-sizing: border-box;
        padding: 10px 0 10px 15px;
        margin-bottom: 15px;
    }
    .pkg-subscribe-wrapper .footer-title {
        color: #333;
        font-size: 19px;
        padding: 0;
        margin: 0 0 10px;
    }
    .pkg-subscribe-wrapper .pkg-form-subscriber {
        max-width: 640px;
        width: 100%;
        display: inline-block;
    }
    .pkg-subscribe-wrapper .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        border: 0;
    }
    .pkg-subscribe-wrapper .sr-only {
        display: none;
    }
    .pkg-subscribe-wrapper .input-group {
        position: relative;
        display: table;
        border-collapse: separate;
    }
    .pkg-subscribe-wrapper .input-group .form-control:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .pkg-subscribe-wrapper .input-group-btn {
        width: 1%;
        font-size: 0;
        position: relative;
        white-space: nowrap;
        vertical-align: middle;
    }
    .pkg-subscribe-wrapper .btn-subscribe {
        background: #bf4500;
        color: #fff;
        font-size: 13px;
        letter-spacing: 0px;
        font-weight: 500;
        padding-bottom: 14px;
        padding-top: 13px;
    }
    .pkg-subscribe-wrapper .btn {
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: normal;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid #e1e1e1;
        border-radius: 4px;
        outline: 0;
    }
    .pkg-subscribe-wrapper .btn-lg {
        padding: 0 16px;
        display: flex;
        justify-content: center;
        align-items: center;
        line-height: 1.3333333;
        border-radius: 6px;
    }
    @media (max-width: 992px) {
        .pkg-subscribe-wrapper {
            width: 100%;
            padding: 10px 7px;
        }
    }
</style>
<div class="pkg-subscribe-wrapper">
    <div class="footer-title">Join 100,000+ subscribers</div>
    <p>Join thousands of smart shoppers. Get handpicked deals delivered directly to your inbox.</p>
    <form id="pkg-form" class="pkg-form-subscriber" action="/activity/subscribe" method="get"><label class="sr-only" for="email-subscribe">Enter
            your email address</label>
        <div class="input-group">
            <input id="email-subscribe"
                   name="email" type="text"
                   placeholder="Enter your email address"
                   class="form-control input-lg email-subscribe">
            <span class="input-group-btn">
            <button class="btn btn-subscribe btn-lg" type="submit">Subscribe</button> </span></div>
    </form>
    <div class="newsletter-subscribe">
        <div class="subscribing-message text-center"></div>
    </div>
    <p>Get Coupon Codes and Online Deals delivered straight to your inbox</p>
</div>
<?php
$currentPage = isset($pageType) ? $pageType : "";
$currentUrl = isset($currentUrl) ? $currentUrl : "";
$storeId = -1;
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
?>
<script>
    document.getElementById('pkg-form').addEventListener('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(document.getElementById('pkg-form'));
        formData.append("prefered", window.location.href);
        var em = document.getElementById('email-subscribe').value;
        var messageEl = document.getElementsByClassName('subscribing-message')[0];
        var storeId = <?= $storeId ?>;
            var retVal = {
                email: em,
                prefered: window.location.href
            };
            if (storeId > 0) {
                formData.append("store", storeId);
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", '/activity/subscribe', true);
            xhr.onreadystatechange = function () {
                if (this.readyState != 4) return;
                if (this.status == 200) {
                    var data = JSON.parse(this.responseText);
                    document.getElementById('email-subscribe').value = "";
                    if (data.status == 'successful') {
                        messageEl.innerHTML = 'Thank for your subscribe!';
                    } else {
                        messageEl.innerHTML = 'Email has subscribed';
                    }
                    messageEl.style.display = "block";
                } else {
                    messageEl.innerHTML = 'Please enter your email!';
                    messageEl.style.display = "block";
                    em.focus();
                    return false;
                }
                setTimeout(() => {
                    messageEl.innerHTML = '';
                }, 2000);
            };
            xhr.send(formData);
    });
</script>
