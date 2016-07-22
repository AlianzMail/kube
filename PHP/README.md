AlianzMailerPHP

include the AlianzMailer folder in your project and load the classes as follows


```
use \AlianzMailer\Gen;
use \AlianzMailer\Messenger;
```
The aim is to create a message of the following format using the classes above

```
{
	"messenger":[
	{
		"subject":"string",
		"to":[
		{
			"email":"required",
			"name":"optional"
		}],
		"cc":[
		],
		"bcc":[
		],
		"dispatch_time":"timestamp"
	}],
	"message":{
		"text":"string",
		"html":"string"
	},
	"from":{
		"name":"site name",
		"email":"site email"
	},
	"reply_to":{
		"name":"someone",
		"email":"one@where"
	},
	"subject":"general subject",
	"dispatch_time":"timestamp"
}
```

First create a new messenger. More than one messenger can be used 
```
$messenger = new Messenger;
$messenger->addReceipients(array(
	array(
		'Email'=>'some email',
		'Name' => 'name of receipient,
		'Type' => Messenger::RCPT_DIRECT,
		),
		/// more receipients
	));
```
Then add the messenger into the generator along with other options
```
$mailer = new Gen;
$mailer->addMessenger($messenger);        // add the messenger to the gen
$mailer->htmlBody($this->mail_file);      // add html message to send
$mailer->altBody($this->mail_file_alt);   // add alternate text message  //requried
$mailer->setSubject($this->subject);
$mailer->setFrom('senderemail','sendername');
$mailer->setCredentials('api_token');

$results = $mailer->dispatch();
```
Other options like setting reply to can be used
```
$mailer->setReplyTo('email','name');
$mailer->setDispatchTime('2016-12-12 10:1:00');
```
it is possible to omit the Messenger class and use the Gen only by calling the <code>mailer->createMessenger()</code> with the array of options to use
```
$mailer->createMessenger(array(
  'subject'=>'some subject',
  'dispatch_time' =>'YYY-MM-DD',
  'name'		=> 'unique_name',
  'receipients' => array(array(
    'email'=>'someone@somedomain',
    'name' => 'someoptional name',
    'type' => $Messenger::RCPT_DIRECT
  )),
));
```
the type represents the type of receipientand the options are
```
Messenger::RCPT_DIRECT  	// for direct receipients
Messenger::RCPT_CC   		// for carbon copy receipients
Messenger::RCPT_BCC		// for blind carbon copy recepients
```
If errors are found in your json, the Mailer will return an error object with HTTP 40* response
or and HTTP 50* response if the request fails entirely.
The error response may be of this format so as to be specific on the messenger or actual location of the error
```
{
  "errors": [
    {
      "field": null,
      "message": {
        "messenger1": {
          "to": [
            {
              "email": "email cannot be empty"
            }
          ]
        }
      }
    }
  ]
}
// or
{
  "errors": [
    {
      "field": null,
      "message": {
        "subject": "subject is invalid"
      }
    }
  ]
}
```
the request will return true if all messages are sent successfully
