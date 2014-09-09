<?php
/*************************************************************************
 * minitweet.php - Simple console based social networking application.   *
 * Written by Richard Richardson on 4th September 2014			 *
 * I was going to write class methods for the messages, but I considered *
 * it simpler in this case to use global arrays and I prefer simplicity. *
 *************************************************************************/

// Message array + index.
$messages=array();			// Each message system wide.
$user_for_message=array();		// The user who created each message.
$time_for_message=array();		// The time each message was created.
$no_messages=0;				// The number of messages in the array.

$users=new Users();			// Create an instance for the user info.
$stdin=new Stdin();			// Class for basic console input.

echo "\n";				// Ensure we start on a new line.
while (($cmd=$stdin->GetLine())!="") {	// Get each console line (Command).
					// Exit when a blank line is typed.
    $user=get_next_command_element($cmd);
					// The first part of the command is
					// always the user.
    $action=get_next_command_element($cmd);
					// The second part is always the action
					// to take - default = 'read' messages
					// for the user.
    $third_el=$cmd;	 		// get it all rather than the next
					// element as a message may have
					// spaces. The third element may be
					// who to follow, or an actual message.

    switch ($action)			// Which type of command was it?
	{
	case "->":			// Add a message.
    	    $users->CheckUser($user);	// Ensure the user is set up.
 	    add_message($user,$third_el);
					// Add to the message array.
	    break;

	case "":
	    read($user);		// Read a users messages.
	    break;

	case "follows":
	    $users->Follow($user,$third_el);
					// Set up tables to show I now follow
					// the user in $third_el.
	    break;

	case "wall":
	    wall($user);		// Display the users wall.
	    break;
	}
    }					// End of while() back for next command.


// Function to add a message.
function add_message($user,$message)
{
//  global $users; // tempy rem.
  global $messages;
  global $user_for_message;
  global $time_for_message;
  global $no_messages;

  $messages[$no_messages]=$message;
  $user_for_message[$no_messages]=$user;
  $time_for_message[$no_messages]=time();
  $no_messages++;			// update counter for next message.
}					// End of add_message()


// Function to read and display a user messages.
function read($user)
{
//  global $users; // tempy rem.
  global $messages;
  global $user_for_message;
  global $no_messages;

  for ($this_message=$no_messages;$this_message>0;)
    {					// Loop through array of messages
					// starting with the most recent.
    $this_message--;			// $this_message is now the right
					// index for the arrays.
    if ($user_for_message[$this_message]!=$user)
	continue;			// The message is not for the wanted
					// user.
    echo $messages[$this_message]." (".how_old_is_message($this_message).")\n";
					// Echo the message to the screen.
    }
}					// End of function read()


// Function to Show the Wall for the user passed as an argument.
function wall($user)
{
  global $users;
  global $messages;
  global $user_for_message;
  global $no_messages;

  for ($this_message=$no_messages;$this_message>0;)
    {					// Loop through array of messages
					// starting with the most recent.
    $this_message--;			// $this_message is now the right
					// index for the arrays.
    if ($user_for_message[$this_message]!=$user &&
	$users->Does_User_follow($user,
				$user_for_message[$this_message])==FALSE)
	    continue;			// Don't want messages for users other
					// than myself unless I follow them.
    echo $user_for_message[$this_message]." - ".$messages[$this_message]." (".
	how_old_is_message($this_message).")\n";
					// Display the message along with the
					// users name.
    }					// for ($this_message= ...
}					// End of function wall()


// Function to get next element from the command.
// (command elements are terminated by a space character)
// The value returned is the ununsed portion of the command.
function get_next_command_element(&$cmd)
{
  $pos=strpos($cmd," ");		// Find 1st space in command.
  if (!$pos) {				// no space found.
    $command_tail=$cmd;
    $cmd="";				// make rest of command null.
    }
  else {				// space found.
    $command_tail=substr($cmd,0,$pos);	// return everything up to space.
    $cmd=substr($cmd,$pos+1);		// update $cmd to have all thats left.
    }
  return $command_tail;
}					// End of get_next_command()


// Function to return a string stating how old a message is.
// Less comments here as the code is self-explanitory.
function how_old_is_message($this_message)
{
  global $time_for_message;

  $how_long_ago=time()-$time_for_message[$this_message];
					// Age of message in seconds.
  if ($how_long_ago<60) {
    if (how_old==1)
	$how_old="1 second ago";
    else
	$how_old=$how_long_ago." seconds ago";
    }
  else if ($how_long_ago<3600) {
    $how_old=intval($how_long_ago/60);
    if ($how_old==1)
	$how_old.=" minute ago";
    else
	$how_old.=" minutes ago";
    }
  else {
    $how_old=intval($how_long_ago/3600);
    if ($how_old==1)
	$how_old.=" hour ago";
    else
	$how_old.=" hours ago";
    }
  return $how_old;
}					// End of how_old_is_message()



// Program Classes.

// Users class. 
class Users {
  var $usernames=array();		// Array of usernames.
  var $user_follows=array();		// Array per user of who the user
					// follows in the form:-
					// '|<user-i-follow>|<next-one>|' ...
					// The php function strpos() returns 0
					// if the first match is at the start
					// of the string which is similar to
					// what it returns when there is no
					// match. Therefore I prefixed the
					// array with an additional '|' making
					// the 1st match occur at position 1.
						
  var $no_users=0;

  function Users()				// Constructor
  {
    ;
  }						// End of Users() - Constructor.


  // Function to check if user exists and add them if they don't.
  function CheckUser($username)
  {
    for ($i=0; $i<$this->no_users;$i++)
      if ($this->usernames[$i]==$username)	// User is already set up.
	return;

    // New user - set up in array.
    $this->usernames[$this->no_users]=$username;
    $this->userfollows[$this->no_users]="||";	// Initialise as 'following no
						// one' so far.
    $this->no_users++;
  }						// End of CheckUser()


  // Function to find if user follows the person in the $follow argument.
  function Does_User_Follow($username,$follow)
  {
    for ($i=0;$i<$this->no_users;$i++) {	// Scan all users.
      if ($this->usernames[$i]==$username) {	// Found user in array.
	$this_user_follows=$this->userfollows[$i];
	if (strpos($this_user_follows,"|".$follow."|"))
	  return TRUE;				// They are following the person
						// in the argument $follow.
	else
	  return FALSE;				// Not following the person
        }
      }						// End of for loop.
    return FALSE;
  }						// End of Does_User_Follow()


  // Function to flag a user as following someone.
  function Follow($username,$follow)
  {
    // Could 1st check that user does not already follow them, but we are told
    // the user will always type the correct command.
    for ($i=0;$i<$this->no_users;$i++) {	// Scan all users.
      if ($this->usernames[$i]==$username) {	// Found the user.
	$this->userfollows[$i].=$follow."|";	// '|'+$person_following+'|' is
						// the way it is presented in
						// the array. (There will
						// already be the leading '|'.
	break;
	}
      }						// End of for ($i=0 ...
  }						// End of follow()
}						// End of Users() class.


class Stdin {
  var $stdin_fd=null;

  function Stdin()				// Constructor
  {
    $this->stdin_fd=fopen("php://stdin","r");
  }						// End of Stdin() - Constructor

  function GetLine()
  {
    echo "> ";					// Show the prompt.
    $msg=fgets($this->stdin_fd);
    while (substr($msg,strlen($msg)-1,1)=="\n" || substr($msg,strlen($msg)-1,1)
		=="\r")
      $msg=substr($msg,0,strlen($msg)-1);	// Remove trailing newlines and
						// carriage returns from string.
    return $msg;
  }						// End of GetLine()
}						// End of Stdin class.
?>
