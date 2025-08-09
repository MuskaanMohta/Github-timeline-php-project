<?php

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
  // TODO: Implement this function
  return str_pad(rand(0,999999),6,'0',STR_PAD_LEFT); //If no is < 6 digit then 0 is added at left of no to make it 6 digit Ex-123 no is generated then code will be 000123
}


/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
  // TODO: Implement this function
  //mail(to,subject,messge,headers) allows to send emails to receiver
  //$email->receiver of the mail
  $subject="Your Verification Code"; //subject of email as given in readme cannot contain newline 
  $message="<p>Your verification code is: <strong>$code</strong></p>"; //Email body i.e. msg in HTML format  as given in readme 
  //headers of the email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
  $headers .="From: no-reply@example.com" . "\r\n";
  return mail($email,$subject,$message,$headers); //send the mail

}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
  $file = __DIR__ . '/registered_emails.txt';
  // TODO: Implement this function
  $email=strtolower($email); //converting email to lower case
  $emails=file_exists($file)?file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES):[]; //if file exists open it and read existing emails else use empty array
  $normalemail=array_map(fn($e)=>strtolower(trim($e)),$emails); //emails are converted to lower case and trimmed i.e. remove whitespaces 
  
  //check if $email is present in $normalemail i.e if it is registered return false
  if(in_array($email,$normalemail)){
    return false;
  }
    
  return file_put_contents($file,$email.PHP_EOL,FILE_APPEND|LOCK_EX)!==false; //append new email with end of line to the file

}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
  $file = __DIR__ . '/registered_emails.txt';
  
  // TODO: Implement this function
  $email = strtolower(trim($email)); //remove whitespaces of email and convert it to lower case
   
  //check whether file exists or not if it does not exist=>nothing to unsubscribe
  if(!file_exists($file))
    return false;
    
  $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);//read all emails excluding empty lines and new lines
    
  $updated = array_filter($emails, fn($e) => strtolower(trim($e)) !==$email);//filter out  emails which are to be unsubscribed by comparing it to lowercase,trimmed (normalized emails)
   
  $content=empty($updated)?'':implode(PHP_EOL, $updated) . PHP_EOL; //if no email left=>return empty string else join the remaining emails (after unsubscribing) with newline characters and add new line at end
    
  return file_put_contents($file,$content) !== false; 
    
}

/**
 * Fetch GitHub timeline.
 */
function fetchGitHubTimeline() {
  // TODO: Implement this function
  $url="https://www.github.com/timeline"; //fetch data from url mentioned in readme

  //associative array options with http,method,header as keys and values as associative array,GET,User-Agent: PHP
  $options=["http"=>[
    "method"=>"GET",
    "header"=>"User-Agent: PHP\r\n"
  ]];
  $context=stream_context_create($options); //need an associative array to create stream context
  $xmlContent=file_get_contents($url,false,$context);//file_get_contents->reads a file into string;$url->url to fetch data, false->ignores include_path, $context->stream context 
  
  //if xmlContent is false i.e. cannot fetch data return empty array
  if($xmlContent===false)
    return [];
  $xml=simplexml_load_string($xmlContent);//converts xml string into an object where $xmlContent->well formed xml string

  //if xml string is not converted to object return empty array
  if ($xml===false)
    return [];
  $data=[];//initialize an empty data array
  foreach($xml->entry as $entry) {
    $data[]=[
      'user' => (string) $entry->author->name, //GitHub username
      'repo' => (string) $entry->link['href']   //repository link
    ];
  }
  return $data;  
}

/**
 * Format GitHub timeline data. Returns a valid HTML sting.
 */
function formatGitHubData(array $data): string {
  // TODO: Implement this function
  $html="<h2>GitHub Timeline Updates</h2>\n";  //Add heading
  $html .="<table border=\"1\">\n"; //Create table with border 
  $html .="<tr><th>Event</th><th>User</th></tr>\n"; //Table headers are Event and User
  foreach($data as $item){
    $user=htmlspecialchars($item['user']??'Unknown'); //get user login if not present return Unknown
    $event = htmlspecialchars($item['repo'] ?? '#'); //get event repo link if not present return #
    $html .="<tr><td><a href=\"$event\" target=\"_blank\">Link</a></td><td>$user</td></tr>\n"; //Add data in row in table
  }
  $html .="</table>\n"; //End table
  $html .="<p><a href=\"{{UNSUBSCRIBE_URL}}\" id=\"unsubscribe-button\">Unsubscribe</a></p>"; //Add unsubscribe link

  return $html;

}

/**
 * Send the formatted GitHub updates to registered emails.
 */
function sendGitHubUpdatesToSubscribers(): void {
  $file = __DIR__ . '/registered_emails.txt';
  // TODO: Implement this function
  //if file does not exist return nothing and exit the function
  if(!file_exists($file))
    return;

  $emails=file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES); //read all emails of file ignoring empty lines and new lines

  //if no email is present i.e. file is empty exit the function
  if(empty($emails))
    return;

  //fetch and format GitHub Timeline data
  $githubData = fetchGitHubTimeline(); 
  $formatted = formatGitHubData($githubData);
   
  foreach($emails as $email)
  {
    $trimEmail=trim($email); //remove whitespace from email

    //if trimmed email is empty then skip that iteration 
    if(empty($trimEmail))
      continue;
       
    $unsubscribeUrl = "http://localhost:3000/src/unsubscribe.php"; //generate unsubscribe link (when we are using localhost 3000 to run index.php)
    $body=str_replace("{{UNSUBSCRIBE_URL}}",$unsubscribeUrl,$formatted);//{{UNSUBSCRIBE_URL}} is replaced by $unsubscribeUrl & find $formatted
    $subject="Latest GitHub Updates";  //subject of mail with no new line
    $headers = "MIME-Version: 1.0" . "\r\n"; //header of mail
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@example.com" . "\r\n";
    mail($trimEmail,$subject,$body,$headers);
  }
}
function sendUnsubscribeEmail(string $email, string $code): bool {
  $subject = "Confirm Unsubscription"; //subject of unsubscription mail
  $message = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>"; //message of mail
  //headers of the mail  
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
  $headers .= "From: no-reply@example.com" . "\r\n";
    
  return mail($email, $subject, $message, $headers);
}
