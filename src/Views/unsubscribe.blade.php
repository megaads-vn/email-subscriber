@extends('frontend.layout.master', ['title' => ''])
@section('content')
<style type="text/css">
.unsubscribed-content {
    min-height: calc(100vh - 115px - 98px);
    margin-top: 50px;
}
.unsubscribed {
    line-height: 1.5;
    display: flex;
    justify-content: center;
    align-items:  center;
    flex-direction: column;
    text-align: center;
    font-size: 15px;
    color: #666;
}

.unsubscribed h1 {
    margin: 10px auto 0;
    text-align: center;
}

.unsubscribed ul {
    text-align: left;
    max-width: 80%;
    margin-top: 20px;
}

.unsubscribed ul li {
    margin-bottom: 10px;
}

.resubscribe-container {
    margin: 32px auto;
    width: 90%;
    max-width: 520px;
}
.resubscribe {
    display: flex;
    justify-content: center;
    align-items: stretch;
    margin: 10px auto;
    font-size: 16px;
    position: relative;
}

.resubscribe-email {
    line-height: 32px;
    border: 2px solid #e1e1e1;
    border-radius: 3px;
    background: #fff;
    padding: 10px;
    outline: none;
    width: 100%;
    font-size: 16px;
}

.resubscribe-button {
    background-color: #007aff;
    color: #fff;
    padding: 10px 40px;
    border: none;
    border-radius: 3px;
    margin-left: 10px;
    font-size: 16px;
    cursor: pointer;
    user-select: none;
}

.resubscribe-header {
    font-size: calc(21px + (31 - 21) * ((100vw - 300px) / (1920 - 300)));
    line-height: 1.1;
    font-weight: bold;
    color: #ff5c35;
}
.resubscribe-success {
    color: green
}
.resubscribe-error {
    color: red
}
.resubscribe-message {
    width: 100%;
    position: absolute;
    top: 100%;
    left: 0;
    padding: 6px 0;
    text-align: left
}
</style>
<div class="unsubscribed-content">
    <div class="unsubscribed">
        <div class="unsubscribed-container">
            <h1>Unsubscribed.</h1>
            <div style="font-size: 23px;">You have been unsubscribed from this publication.</div>
            <ul>
                <li>
                    We’re sad to see you go, but we’re here when you’re ready for more.
                </li>
            </ul>
        </div>
        <div class="resubscribe-container">
            <div class="resubscribe-header">
                If you unsubscribed accidently,<br> you can resubscribe again:
            </div>
            
            <form class="resubscribe" action="/activity/subscribe" method="post">
                <div class="resubscribe-message">
                    <div class="resubscribe-success" style="display: none;">Thank you for signing up for newsletter via email, from now you will receive the latest information from us.</div>   
                    <div class="resubscribe-error" style="display: none;">Invalid email address format.</div>   
                </div>
                <input class="resubscribe-email" name="homepage_deal_alert[email]" placeholder="Your Email" required="" type="email" >
                <button type="submit" class="resubscribe-button">Submit</button>
            </form>
        </div>
    </div>
</div>
<script>
    document.querySelector('.resubscribe').addEventListener('submit', function(event) {
        event.preventDefault();
        var em = document.getElementsByClassName('resubscribe-email')[0].value;
        var messageSuccess = document.getElementsByClassName('resubscribe-success')[0];
        var messageError = document.getElementsByClassName('resubscribe-error')[0];
        if(typeof(em) != 'undefined' && em != '' && em != null) {
            var retVal = {
                email: em,
                prefered: window.location.href
            };

            var xhr = new XMLHttpRequest();
            xhr.open("POST", '/activity/subscribe', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function () {
                if (this.readyState != 4) return;
                messageSuccess.style.display = "block";
                messageError.style.display = "block";
                if (this.status == 200) {
                    var data = JSON.parse(this.responseText);
                    document.getElementsByClassName('resubscribe-email')[0].value = "";
                    messageSuccess.innerHTML = 'Thank for your subscribe!';
                    messageError.innerHTML = '';
                } else {
                    messageError.innerHTML = 'Please enter your email!';
                    messageSuccess.innerHTML = '';
                    em.focus();
                    return false;
                }
                setTimeout(() => {
                    messageError.innerHTML = '';
                    messageSuccess.innerHTML = '';
                    elementABC.classList.remove('active');
                }, 2000);
            };
            xhr.send(JSON.stringify(retVal));
        } 
    });
</script>
@endsection