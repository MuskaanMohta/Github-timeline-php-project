<?php
require_once 'functions.php';
session_start();
// TODO: Implement the form and logic for email registration and verification
//initialize variables
$msg='';
$code='';
$email='';

if($_SERVER['REQUEST_METHOD']==='POST')
{
    //check if form is submitted only with email and no verification code
    if(isset($_POST['email']) && !isset($_POST['verification_code']))
    {
        $email=trim($_POST['email']); //remove whitespaces from email
        $code=generateVerificationCode();//generate 6-digit verification code

        $_SESSION['verification_code'] = $code; //store verification code in session 
        $_SESSION['email_for_verification'] = $email; //store email in session

        //send verification mail
        if(sendVerificationEmail($email,$code))
        {
            $msg="Verification code sent to $email";
        }
        else
        {
            $msg="Failed to send verification email";
        }
    }

    //check if form is submitted with both email and verification code
    if(isset($_POST['email']) && isset($_POST['verification_code']))
    {
        $email=trim($_POST['email']); //remove whitespaces from email
        $enteredCode=trim($_POST['verification_code']); //remove whitespace from 6-digit verification code

        //verify if verifiaction code and email values are same as stored session values
        if(isset($_SESSION['verification_code']) && isset($_SESSION['email_for_verification']) && $email === $_SESSION['email_for_verification'] && $enteredCode === $_SESSION['verification_code'])
        {
            registerEmail($email);//register the verified email by storing it in file
            $msg="Email $email successfully verified and registered";
        }
        else
        {
            $msg="Invalid verification code";
        }
    }

}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Email Verification</title>
    </head>
    <body style="background: #bbc6fa;">
        <div style="top: 50%;left:50%;position:absolute;background:#ffffff;text-align:center;width: 500px;height:250px;transform:translate(-50%,-50%); border-radius:10px;box-shadow:0 10px 20px 0 rgba(0,0,0,0.15);">
            <h2>Register and Verify Email</h2>
            <!-- create a form with an input field for email and a submit button -->
            <form method="POST">
                <input type="email" name="email" required placeholder="Enter your email">
                <button type="submit" id="submit-email">Submit</button>
            </form>
            <h2>Verify Email</h2>
            <!-- create a form with input fields for email,6-digit verification code and a verify button -->
            <form method="POST">
                <input type="email" name="email" required placeholder="Enter your email">
                <input type="text" name="verification_code" maxlength="6" required placeholder="Enter 6-digit code">
                <button type="submit" id="submit-verification">Verify</button>
            </form>
            <!-- display success or error message  -->
            <p><?php echo htmlspecialchars($msg); ?></p>
        </div>
        
    </body>
</html>
