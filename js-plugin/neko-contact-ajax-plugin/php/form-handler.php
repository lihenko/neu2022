<?php

if ( !isset( $_SESSION ) ) session_start();

if ( !$_POST ) exit;

if ( !defined( "PHP_EOL" ) ) define( "PHP_EOL", "\r\n" );





$to = "wohlfuehlen@kosmetik-katharina.de";

$subject = "Website-Kontaktformular ";







foreach ($_POST as $key => $value) {

    if (ini_get('magic_quotes_gpc'))

        $_POST[$key] = stripslashes($_POST[$key]);

    $_POST[$key] = htmlspecialchars(strip_tags($_POST[$key]));

}



// Assign the input values to variables for easy reference

$name      = @$_POST["name"];

$email     = @$_POST["email"];

$phone     = @$_POST["phone"];

$message   = @$_POST["comment"];

$verify    = @$_POST["verify"];





// Test input values for errors

$errors = array();

 //php verif name

if(isset($_POST["name"])){

 

        if (!$name) {

            $errors[] = "Bitte geben Sie Ihren Namen ein.";

        } elseif(strlen($name) < 2)  {

            $errors[] = "Ihre Eingabe sollte aus mindestens 2 Zeichen bestehen.";

        }

 

}

    //php verif email

if(isset($_POST["email"])){

    if (!$email) {

        $errors[] = "Bitte geben Sie Ihre E-Mail-Adresse ein.";

    } else if (!validEmail($email)) {

        $errors[] = "Bitte geben Sie eine gültige E-Mail-Adresse ein.";

    }

}

    //php verif phone

if(isset($_POST["phone"])){

    if (!$phone) {

        $errors[] = "Bitte geben Sie Ihre Rückrufnummer ein.";

    }elseif ( !is_numeric( $phone ) ) {

        $errors[]= 'Ihre Telefonnummer soll nur aus Ziffern bestehen.';

    }

}







//php verif comment

if(isset($_POST["comment"])){

    if (strlen($message) < 10) {

        if (!$message) {

            $errors[] = "Bitte geben Sie eine kurze Nachricht ein.";

        } else {

            $errors[] = "Ihre Nachricht sollte aus mindestens 10 Zeichen bestehen.";

        }

    }

}


    //php verif captcha
if(isset($_POST["verify"])){
    if (!$verify) {
        $errors[] = "Bitte geben Sie den nebenstehenden Sicherheitscode ein.";
    } else if (md5($verify) != $_SESSION['nekoCheck']['verify']) {
        $errors[] = "Der eingegebene Sicherheitscode ist nicht korrekt. ";
    }
}


if ($errors) {

        // Output errors and die with a failure message

    $errortext = "";

    foreach ($errors as $error) {

        $errortext .= '<li>'. $error . "</li>";

    }



    echo '<div class="alert alert-danger">The following errors occured:<br><ul>'. $errortext .'</ul></div>';



}else{







    // Send the email

    $headers  = "From: $email" . PHP_EOL;

    $headers .= "Reply-To: $email" . PHP_EOL;

    $headers .= "MIME-Version: 1.0" . PHP_EOL;

    $headers .= "Content-type: text/plain; charset=utf-8" . PHP_EOL;

    $headers .= "Content-Transfer-Encoding: quoted-printable" . PHP_EOL;



    $mailBody  = "Du wurdest kontaktiert von $name" . PHP_EOL . PHP_EOL;

    $mailBody .= (!empty($company))?'Firma: '. PHP_EOL.$company. PHP_EOL . PHP_EOL:'';

    $mailBody .= (!empty($quoteType))?'Thema: '. PHP_EOL.$quoteType. PHP_EOL . PHP_EOL:''; 

    $mailBody .= "Nachricht :" . PHP_EOL;

    $mailBody .= $message . PHP_EOL . PHP_EOL;

    $mailBody .= "Du kannst $name kontaktieren via email, $email.";

    $mailBody .= (isset($phone) && !empty($phone))?" oder telefonisch $phone." . PHP_EOL . PHP_EOL:'';

    $mailBody .= "-------------------------------------------------------------------------------------------" . PHP_EOL;













    if(mail($to, $subject, $mailBody, $headers)){

        echo '<div class="alert alert-success">Vielen Dank! Ihre Nachricht wurde abgeschickt.</div>';

    }

}



// FUNCTIONS 

function validEmail($email) {

    $isValid = true;

    $atIndex = strrpos($email, "@");

    if (is_bool($atIndex) && !$atIndex) {

        $isValid = false;

    } else {

        $domain = substr($email, $atIndex + 1);

        $local = substr($email, 0, $atIndex);

        $localLen = strlen($local);

        $domainLen = strlen($domain);

        if ($localLen < 1 || $localLen > 64) {

            // local part length exceeded

            $isValid = false;

        } else if ($domainLen < 1 || $domainLen > 255) {

            // domain part length exceeded

            $isValid = false;

        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {

            // local part starts or ends with '.'

            $isValid = false;

        } else if (preg_match('/\\.\\./', $local)) {

            // local part has two consecutive dots

            $isValid = false;

        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {

            // character not valid in domain part

            $isValid = false;

        } else if (preg_match('/\\.\\./', $domain)) {

            // domain part has two consecutive dots

            $isValid = false;

        } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {

            // character not valid in local part unless

            // local part is quoted

            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {

                $isValid = false;

            }

        }

        

        if(function_exists('checkdnsrr')){

	        if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {

	            // domain not found in DNS

	            $isValid = false;

	        }

        }



    }

    return $isValid;

}



?>

