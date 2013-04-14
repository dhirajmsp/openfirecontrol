<?php
/*
 * Copyright (c) 2012, Viktor Tassi  
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions, the following disclaimer and the 
 *       copyright holder's contact information.
 * 
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions, the following disclaimer and the 
 *       copyright holder's contact information in the documentation and/or 
 *       other materials provided with the distribution.
 *
 *     * Neither the name of the copyright holder nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF 
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * For more information you can contact me at:
 *   e-mail: captainsheldon@gmail.com
 *   skype : tassi.viktor (text only)
 */

/*
 * NOTE: This software is incomplete! The donations helps me to complete this 
 * class and support versions relased in the future. So if you find this stuff 
 * useful (or saves some time for you):
 * 
 * Please send me $5 via Paypal to: captainsheldon@gmail.com
 * 
 * Thank you!
 * 
 */

/*
 * 
 * Openfire Control Class V1.01 Copyright (c) 2012, Viktor Tassi
 * 
 * This class is made for user/group/room membership management from PHP
 * 
 * Tested with Openfire 3.7.1
 * 
 * Dependecies: 
 * 
 *   - PHP_CURL extension
 * 
 * TODO: 
 *   - Complete documentation
 *   - Service management
 *   - Query lists (user, groups, services, etc)
 *   - Room management
 *   - fsockopen operations when curl is not available
 *   - "Shortcut" methods for complex operations ie. connect users to each other
 *     on their contact list with one call
 * 
 */

class roomusrafl {
    const MEMBER  = "member";
    const OWNER   = "owner";
    const ADMIN   = "admin";
    const OUTCAST = "outcast";
}

class rostersubscriptiontype {
    const REMOVE = -1;
    const NONE   =  0;
    const TO     =  1;
    const FROM   =  2;
    const BOTH   =  3;
}

class muc_room{
  public $JID ="";
  public $name="";
  public $description ="";
  public $topic="";
  public $occupantsnum = 0;
  public $brdcst_presence_moderator = true;
  public $brdcst_presence_participants = true;
  public $brdcst_presence_visitor = true;
  public $password = "";
  public $show_realJIDs_to = "Moderator"; //Anyone
  public $list_room_in_directory = true;
  public $moderatedroom = false;
  public $membersonly = false;
  public $allowoccupantsinvite = false;
  public $occupantschangesubject = false;
  public $joinwithregisterednickonly = false;
  public $allownickchange = true;
  public $usersmayregisterwithroom = true;
  public $logroomchat = false;
}

class ofctrl {
  private $_jsessid;
  private $_baseurl;
  private $_ch;
  
  public $_data;
  public $_lasterror;
  
private function _loggedout($headerdata){
  return (strpos($headerdata, '/login.jsp') > 0) ? true : false;
}

private function _curlgetrawdata($url,$postdata=null){
  $this->_data = "";
  $this->_lasterror = "";
  
  $this->_ch = curl_init($url);
  
  if (($postdata !== null) && (!empty($postdata)) ) {
    curl_setopt($this->_ch, CURLOPT_POST      ,1);
    curl_setopt($this->_ch, CURLOPT_POSTFIELDS    ,$postdata); 
  }
  
  curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($this->_ch, CURLOPT_HEADER, true);
  curl_setopt($this->_ch, CURLOPT_NOBODY, false);
  curl_setopt($this->_ch, CURLOPT_COOKIESESSION, false);
  curl_setopt($this->_ch, CURLOPT_COOKIE, 'JSESSIONID='.$this->_jsessid);
  $this->_data = curl_exec($this->_ch);
  
  if (curl_errno($this->_ch)) {
    $this->_lasterror = curl_error($this->_ch);
  }

  curl_close($this->_ch);
  return true;
}

/*
 * Initialize & login
 */

public function init($baseurl,$user,$pass){
  $jsessid = NULL;
  $this->_baseurl = $baseurl;
  $this->_ch = curl_init($this->_baseurl."/login.jsp");
  curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($this->_ch, CURLOPT_HEADER, true);
  curl_setopt($this->_ch, CURLOPT_NOBODY, true);
  curl_setopt($this->_ch, CURLOPT_COOKIESESSION, true);
  $this->_data = curl_exec($this->_ch);
  curl_close($this->_ch);
  preg_match('/^Set-Cookie: JSESSIONID=(.*?);/m', $this->_data, $jsessid);
  $this->_jsessid = $jsessid[1];
  
  $this->_curlgetrawdata($this->_baseurl."/login.jsp?url=/_login_ok&login=true&username=".urlencode($user)."&password=".urlencode($pass)."&JSESSIONID=".urlencode($this->_jsessid));
  
  if ( strpos($this->_data, "Login failed:") > 0 ) {return false;}
          
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, '_login_ok') > 0) ? true : false));
}

/*
 * Add or modify user permission in room
 */

public function room_user ($roomJID,$userJID,$affiliation = roomusrafl::MEMBER) {
  $this->_curlgetrawdata($this->_baseurl."/muc-room-affiliations.jsp?add&roomJID=".urlencode($roomJID)."&userJID=".urlencode($userJID)."&affiliation=".urlencode($affiliation));
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'addsuccess=true') > 0) ? true : false));
}

/*
 * Delete room permission of user
 */

public function room_deluser ($roomJID,$userJID) {
  $this->_curlgetrawdata($this->_baseurl."/muc-room-affiliations.jsp?roomJID=".urlencode($roomJID)."&userJID=".urlencode($userJID)."&delete=true");
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'deletesuccess=true') > 0) ? true : false));
}

/*
 * Add user
 */

public function usr_adduser($username, $password, $email='', $realname='',$isadmin = false){
  $this->_curlgetrawdata($this->_baseurl."/user-create.jsp?&create=Create+User".
                                         "&username=".urlencode($username).
                                         "&name=".urlencode($realname).
                                         "&email=".urlencode($email).
                                         "&password=".urlencode($password).
                                         "&passwordConfirm=".urlencode($password).
                                         "&isadmin=".($isadmin ? 'on':'off'));
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'success=true') > 0) ? true : false));
}

/*
 * Modify user properties
 */

public function usr_modifyuser($username, $email, $realname, $isadmin = false){
  $this->_curlgetrawdata($this->_baseurl."/user-edit-form.jsp?save=true&username=".urlencode($username).
                                         "&email=".urlencode($email).
                                         "&name=".urlencode($realname).
                                         "&isadmin=".($isadmin ? 'on':'off')
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'editsuccess=true') > 0) ? true : false));        
}

/*
 * Change password
 */

public function usr_changepassword($username, $password){
    //lehet, hogy POST kell...
  $this->_curlgetrawdata($this->_baseurl."/user-password.jsp","&update=Update+Password".
                                         "&username=".urlencode($username).
                                         "&password=".urlencode($password).
                                         "&passwordConfirm=".urlencode($password)
  );
 return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'success=true') > 0) ? true : false));
}

/*
 * Delete user
 */

public function usr_deleteuser($username){
  $this->_curlgetrawdata($this->_baseurl."/user-delete.jsp?delete=Delete+User"
                                        ."&username=".urlencode($username));
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'deletesuccess=true') > 0) ? true : false));
}

/*
 * Add roster item to user
 */

public function usr_addrosteritem($username,$rosteruserJID,$nickname,$groups){
  $this->_curlgetrawdata($this->_baseurl."/user-roster-add.jsp?&add=Add+Item".
                                         "&username=".urlencode($username).
                                         "&jid=".urlencode($rosteruserJID).
                                         "&nickname=".urlencode($nickname).
                                         "&email=".urlencode($groups)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'addsuccess=true') > 0) ? true : false));
}

/*
 * Delete roster item
 */

public function usr_deleterosteritem($username,$rosteruserJID){
  $this->_curlgetrawdata($this->_baseurl."/user-roster-delete.jsp?delete=Delete+Roster+Item".
                                         "&username=".urlencode($username).
                                         "&jid=".urlencode($rosteruserJID)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'deletesuccess=true') > 0) ? true : false));
}

/*
 * Edit roster item, set subscription
 */

public function usr_editrosteritem($username,$rosteruserJID,$nickname,$groups,$sub=rostersubscriptiontype::BOTH){
  $this->_curlgetrawdata($this->_baseurl."/user-roster-edit.jsp?save=true".
                                         "&username=".urlencode($username).
                                         "&jid=".urlencode($rosteruserJID).
                                         "&nickname=".urlencode($nickname).
                                         "&groups=".urlencode($groups).
                                         "&sub=".urlencode($sub)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'editsuccess=true') > 0) ? true : false));
}

/*
 * Lockout user 
 * Delay, duration are in minutes.
 * Startdelay = -1 start immediately
 * Duration = -1 Forever
 */

public function usr_lockout($username,$startdelay=-1,$duration=-1){
  $this->_curlgetrawdata($this->_baseurl."/user-lockout.jsp?lock=Lock+Out+User"
                                        ."&username=".urlencode($username)
                                        ."&startdelay="
                                        ."&duration="
                                        ."stardelay_custom=".urlencode($startdelay)."&duration_custom=".urlencode($duration)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'locksuccess=1') > 0) ? true : false));
}

/*
 * Unlock a locked user
 */

public function usr_unlock($username){
  $this->_curlgetrawdata($this->_baseurl."/user-lockout.jsp?unlock=Unlock+User"
                                        ."&username=".urlencode($username)
  );
return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'unlocksuccess=1') > 0) ? true : false));       
}

/*
 * Create user group
 */

public function grp_addgroup($name,$description){
  $this->_curlgetrawdata($this->_baseurl."/group-create.jsp","create=Create+Group".
                                         "&name=".urlencode($name).
                                         "&description=".urlencode($description)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'creategroupsuccess=true') > 0) ? true : false));
}  

/*
 * Modify group
 */

public function grp_modifygroup($group,$newname,$newdescription){
  $this->_curlgetrawdata($this->_baseurl."/group-create.jsp","edit=Edit+Group".
                                         "&group=".urlencode($group).
                                         "&name=".urlencode($newname).
                                         "&description=".urlencode($newdescription)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'groupChanged=true') > 0) ? true : false));
}  

/*
 * Delete group
 */

public function grp_deletegroup($name){
  $this->_curlgetrawdata($this->_baseurl."/group-delete.jsp?delete=Delete+Group&group=".urlencode($name));
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'deletesuccess=true') > 0) ? true : false));
}

/*
 * Add user to a group
 */

public function grp_adduser($groupname,$username){
  $this->_curlgetrawdata($this->_baseurl."/group-edit.jsp","add=Add&addbutton=Add".
                                         "&group=".urlencode($groupname).
                                         "&username=".urlencode($username)
  );
 return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'success=true') > 0) ? true : false));
}

/*
 * Remove user from a group
 */

public function grp_removeuser($groupname,$userJID){
  $this->_curlgetrawdata($this->_baseurl."/group-edit.jsp","remove=Remove".
                                         "&group=".urlencode($groupname).
                                         "&delete=".urlencode($userJID)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'deletesuccess=true') > 0) ? true : false));
}

/*
 * Enable / disable contact list group sharing
 */

public function grp_setcontactlistgrpshare($groupname,$displayname,$enable=false){
  $this->_curlgetrawdata($this->_baseurl."/group-edit.jsp?save=Save+Contact+List+Settings"
                                        ."&group=".urlencode($groupname)
                                        ."&groupDisplayName=".urlencode($displayname)
                                        ."&enableRosterGroups=".($enable ? "true":"false")
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'groupChanged=true') > 0) ? true : false));
}

/*
 * Send message to all online user
 */

public function tool_sendmsg($message){
  $this->_curlgetrawdata($this->_baseurl."/user-message.jsp","tabs=true&send=true"
                                        ."&message=".urlencode($message)
  );
 return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'success=true') > 0) ? true : false));
}

/*
 * Kill user session - UNTESTED!
 */

public function sess_kill($userJID,$resourceid){
  $this->_curlgetrawdata($this->_baseurl."/session-summary.jsp?close=true"
                                        ."&jid=".urlencode($userJID)."/".urlencode($resourceid)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'close=success') > 0) ? true : false));
}

/*
 * Delete specified room  - UNTESTED!
 */

public function room_deleteroom($roomJID,$reason,$alternateroom){
  $this->_curlgetrawdata($this->_baseurl."/muc-room-delete.jsp?&delete=Destroy+Room"
                                        ."&roomJID=".urlencode($roomJID)
                                        ."&reason=".urlencode($reason)
                                        ."&alternateJID=".urlencode($alternateroom)
  );
  return ($this->_loggedout($this->_data) ? false : ((strpos($this->_data, 'deletesuccess=true') > 0) ? true : false));
}

//TODO 

public function room_addeditroom($roomclass){
  return false;
}
public function grp_adminuser($groupname, $userJID,$isadmin=false){
  //a bit complicated but not impossible ;-) 
}
public function sess_listuser(){
  return false;
}
public function grp_listgroups(){
  return false;
}
public function grp_getgroupdata($groupname){
  return false;
}
public function srv_listproperties(){
  return false;
}
public function srv_getproperty($name,$defaultvalue=NULL){
  //stuff
  return $defaultvalue;
}
public function srv_setproperty($name,$value){
  //stuff
  return true;
}
public function srv_delproperty($name){
  //stuff
  return true;
}
public function srv_listmucservices() {
  return array();
}
public function srv_addmucservice(){
    return false;
}
public function usr_listusers(){
  return false;
}
public function usr_getuserdata($username){
  return false;
}
public function room_listrooms(){
  return false;
}
public function room_getproperties($roomJID) {
  return false;
}

}
?>