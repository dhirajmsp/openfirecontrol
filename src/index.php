<?php
// This page is for functionality testing.
?>
<html>
  <head>
    <title>Openfire control class test page</title>
    <style type="text/css">
      body{
        font-family:verdana,helvetica,sans;
        font-size:12px;
        color:black
      }
      .success {
        color: green;
      }
      .error{
        color:red
      }
    </style>
  </head>
  <body>
<h1>Testing openfire class control methods</h1>
<?php



/*
 * Server URL 
 * HTTPS is not tested.
 */
define("SERVER_URL","http://127.0.0.1:9090");

/*
 * When you install Openfire you need to set the chatserver domain
 * Write that domain here. When you create a user on server, Openfire extends
 * the username with this domain. That will be the "JID" of the user.
 */
define("SERVER_DOMAIN","deucalion");

/*
 * This is a default service name in Openfire. In most case you should leave it
 * as is.
 */
define("ROOMSERVICE","conference"); 

/*
 * The username and password what you use on Openfire control panel
 */
define("ADMIN_USER","admin");
define("ADMIN_PASS","admin");

/*
 * For the testing procedure we will use the admin user.
 */
define("EXISTINGUSER_USERNAME","admin");

/*
 * Loading class
 */
require_once "./ofcontrol.php";


/*
 * The testing procedure has two separatable phase. 
 * The first one is the "create" phase, the second is about to remove created
 * items. 
 */

$myOFControl = new ofctrl();

/*
 * Logging you in
 */
echo "Login: ". ($myOFControl->init(SERVER_URL,ADMIN_USER, ADMIN_PASS)? "<span class='success'>Success</span>": "<span class='error'>Error</span>")."<br /><br />";

/*
 * Creating the user "demo"
 */
echo "Ceating user: ".($myOFControl->usr_adduser("demo", "demo", "demo@".SERVER_DOMAIN, "demo", false) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Modifying user data
 */
echo "Modify user: ".($myOFControl->usr_modifyuser("demo","demo2", "demo@".SERVER_DOMAIN, "demo",false) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Changing user password
 */
echo "Change password: ".($myOFControl->usr_changepassword("demo", "demopass") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * To have user on an another user's contact list we need to do two steps.
 * 1st : add users on each other's contact list
 * 2nd : make subscription to each other's contact list
 * 
 * NOTE: Some XMPP clients need to relogin to refresh their lists.
 * 
 */
echo "Add roster item demo<-admin: ".($myOFControl->usr_addrosteritem("demo",  EXISTINGUSER_USERNAME."@".SERVER_DOMAIN, EXISTINGUSER_USERNAME, "") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";
echo "Add roster item admin<-demo: ".($myOFControl->usr_addrosteritem(EXISTINGUSER_USERNAME, "demo@".SERVER_DOMAIN, "demo", "") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

echo "Change subscription demo<-admin: ".($myOFControl->usr_editrosteritem("demo", EXISTINGUSER_USERNAME."@".SERVER_DOMAIN, EXISTINGUSER_USERNAME, "",rostersubscriptiontype::BOTH) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";
echo "Change subscription admin<-demo: ".($myOFControl->usr_editrosteritem(EXISTINGUSER_USERNAME,"demo@".SERVER_DOMAIN,  "demo",  "",rostersubscriptiontype::BOTH) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Creating user groups
 */
echo "Create group: ".($myOFControl->grp_addgroup("Demogroup","There is a demo group") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Modifying group data
 */
echo "Modify group: ".($myOFControl->grp_modifygroup("Demogroup","newdemogroup","There is a new demo group") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Share groups to subscribed user's.
 * They will see the group on their contact list
 */
echo "Share group: ".($myOFControl->grp_setcontactlistgrpshare("newdemogroup","demogroup",true) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";
        
/*
 * Adding user to a group
 */
echo "Add user to a group: ".($myOFControl->grp_adduser("newdemogroup","demo") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Adding user to a specified room
 * The member have to be one of:  "member", "owner", "admin" or "outcast";
 * The class named "roomusrafl" may helps you for this
 */
echo "Add user to room: ".($myOFControl->room_user('room@'.ROOMSERVICE.'.'.SERVER_DOMAIN,'demo@'.SERVER_DOMAIN,roomusrafl::MEMBER) ? "<span class='success'>Success</span>": "<span class='error'>Error</span>")."<br /><br />";

/*
 * Locing out the user from the server. 
 * NOTE: The values are in minutes
 */
echo "Lockout user: ".($myOFControl->usr_lockout("demo",110, 240) ? "<span class='success'>Success</span>": "<span class='error'>Error</span>")."<br /><br />";

/*
 * Sending message to all online user
 */
echo "Sending message: ".($myOFControl->tool_sendmsg("There is the time to destroy all test items !!!") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";


/*
 * Here comes the second phase.
 * if you uncomment the next line you should check the results of this test's 
 * first phase
 */

//die();

/*
 * Removing user's lockout
 */
echo "Unlock user: ".($myOFControl->usr_unlock("demo") ? "<span class='success'>Success</span>": "<span class='error'>Error</span>")."<br /><br />";

/*
 * Remove users room membership
 */
echo "Delete user from room: ".($myOFControl->room_deluser('room@'.ROOMSERVICE.'.'.SERVER_DOMAIN,'demo@'.SERVER_DOMAIN) ? "<span class='success'>Success</span>": "<span class='error'>Error</span>");
echo "<br /><br />";

/*
 * Remove users group membership
 */
echo "Remove user from group: ".($myOFControl->grp_removeuser("newdemogroup","demo@".SERVER_DOMAIN) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Disable contact list sharing
 */
echo "Disable contact list sharing: ".($myOFControl->grp_setcontactlistgrpshare("newdemogroup","demogroup",false) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Delete group
 */
echo "Delete group: ".($myOFControl->grp_deletegroup("newdemogroup") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Delete users from each others contact list
 */
echo "Delete roster item demo<-admin: ".($myOFControl->usr_deleterosteritem("demo",  "admin@".SERVER_DOMAIN) ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";
echo "Delete roster item admin<-demo: ".($myOFControl->usr_deleterosteritem("admin", "demo@".SERVER_DOMAIN)  ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

/*
 * Delete user
 */
echo "Delete user ".($myOFControl->usr_deleteuser("demo") ? "<span class='success'>Success</span>":"<span class='error'>Error</span>")."<br /><br />";

?>
  </body>
</html>