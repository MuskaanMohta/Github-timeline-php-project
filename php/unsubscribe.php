<?php
require_once 'functions.php';
session_start();
// TODO: Implement the form and logic for email unsubscription.
//Initialize variables
$msg='';
$success='';
$email='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    //check if form is submitted only with email and no verification code
    if(isset($_POST['unsubscribe_email']) && !isset($_POST['unsubscribe_verification_code']))
    {
        $email=trim($_POST['unsubscribe_email']);//remove whitespace from the email

        //check if email is valid email address
        if(!filter_var($email,FILTER_VALIDATE_EMAIL))
        {
            $msg="Invalid Email Format";
        }
        else
        {
            $emailFile= __DIR__ . '/registered_emails.txt';
            $emails=file_exists($emailFile)?file($emailFile,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES):[];//if file exist read all email in the file else create empty array

            //check if email is registered or not i.e. present in file or not
            if(!in_array($email,$emails))
            {
                $msg="The email is not registered";
            }
            else
            {
                $code=generateVerificationCode(); //generate a random 6 digit verification code
                $_SESSION['verification_code'] = $code; //store the verification code in session for later use
                $_SESSION['email_for_verification'] = $email; //store the email in session for later use

                //call the function to send verification code to the email
                if(sendUnsubscribeEmail($email,$code))
                {
                    $msg="Verification code sent.Please enter below";
                    
                }
                else
                {
                    $msg="Failed to send verification code.";
                }
            }
        }
    }

    //check if form is submitted with both email and verification code
    if(isset($_POST['unsubscribe_verification_code'])&&isset($_POST['unsubscribe_email']))
    {
        $email=trim($_POST['unsubscribe_email']);//remove whitespace from mail
        $enteredCode=trim($_POST['unsubscribe_verification_code']);//remove whitespace from verification code
       
        //check if verification code and email are in session and if they match with stored values of email and code
        if (isset($_SESSION['verification_code']) && isset($_SESSION['email_for_verification']) && $email === $_SESSION['email_for_verification'] && $enteredCode === $_SESSION['verification_code'])
        {
            //unsubscribe the email
            if(unsubscribeEmail($email))
            {
                $success="You have been unsubscribed.";
                //clear the session data after successful unsubscription
                unset($_SESSION['verification_code']);
                unset($_SESSION['email_for_verification']);
            }
            else
            {
                $msg="Unsubscription failed";//failed to unsubscribe i.e. could not remove email from file
            }

        }
        else
        {
            $msg="Invalid verification code";//wrong verification code
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head><title>Unsubscribe</title></head>
    <body style="background-color: #bbc6fa;">
        <div style="top: 50%;left:50%;position:absolute;background:#ffffff;text-align:center;width: 500px;height:250px;transform:translate(-50%,-50%); border-radius:10px;box-shadow:0 10px 20px 0 rgba(0,0,0,0.15);">
            <h2>Unsubscribe from GitHub Timeline Updates</h2>
            <!-- if unsubscribe successful show the msg in green color -->
            <?php if($success): ?>
                <p style="color: green;"><?php echo htmlspecialchars($success); ?></p> 
            <!-- else show other msg in red color -->     
            <?php elseif($msg): ?>
                <p style="color: red;"><?php echo htmlspecialchars($msg); ?></p>
            <?php endif;?>
            <!-- create a form with an input field for email and an unsubscribe button -->
            <form method="POST">
                <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
                <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
            </form>
            <br>
            <!-- create a form with input fields for email,code and a verify button -->
            <form method="POST">
                <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
                <input type="text" name="unsubscribe_verification_code" maxlength="6" required placeholder="Enter 6-digit code">
                <button type="submit" id="verify-unsubscribe">Verify</button>
            </form>
        </div>
        
        
    </body>
</html>