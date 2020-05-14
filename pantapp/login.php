<!DOCTYPE html>
    <html>
    <head>
    <script src="https://code.jquery.com/jquery-3.5.1.js"    ></script>
    <title>Facebook Login </title>
    <meta charset="UTF-8">
    </head>
    <body>
    <script>

    function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().
                 // The current login status of the person.
        if (response.status === 'connected') {   // Logged into your webpage and Facebook.
        testAPI(response.authResponse.userID, response.email);


        } else {                                 // Not logged into your webpage or we are unable to tell.
        document.getElementById('status').innerHTML = 'Please log ' +
            'into this webpage.';
        }
    }


    function checkLoginState() {               // Called when a person is finished with the Login Button.
        FB.getLoginStatus(function(response) {   // See the onlogin handler
        statusChangeCallback(response);
        });
    }


    window.fbAsyncInit = function() {
        FB.init({
        appId      : '3117852921599127',
        cookie     : true,                     // Enable cookies to allow the server to access the session.
        xfbml      : true,
        version    : 'v7.0'
        });


        FB.getLoginStatus(function(response) {   // Called after the JS SDK has been initialized.
        statusChangeCallback(response);        // Returns the login status.
        });
    };

    
    (function(d, s, id) {                      // Load the SDK asynchronously
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    
    function testAPI(id, email) {                      // Testing Graph API after login.  See statusChangeCallback() for when this call is made.
        console.log('Welcome!  Fetching your information.... ');

        var email;

        FB.api(
            "/"+id,
            'GET',
            {"fields":"birthday,name,email,hometown"},
            function(response) {
                window.location.href = "RegisterUser.php?name=" + response.name+" &id="+response.id+" &email="+response.email;

            }
        );
    }

    </script>



    <fb:login-button scope="public_profile,email" onlogin="checkLoginState();">
    </fb:login-button>

    <div id="status">
    </div>

    </body>
</html>