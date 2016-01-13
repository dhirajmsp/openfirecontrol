# About this project #
This project provides a PHP class to manage most features of Openfire chat server

# Motivation #
When you are about to integrate a Facebook or Gtalk style (XMPP based) chat system into your website you will need the followings:

  1. A chat server
  1. A js based chat client
  1. Find a way to connect website accounts with chatserver accounts

The first two are simple. The last one is a bit harder. In most case you need to change the users database structure to be compatible with chat server. It can interfere with other part of your site, so it is not a best solution. Fortunately it is not the only way. The other solution is to make an integratable chat server control interface. In this case you need only a slight modification in the website's code and hook the user creation/modification/deletion process. In that hook simply call the interface's function. It is so simple because no need to hack/reconfigure server and user's table.

# What is this and how it works? #
This is a class written in PHP. Just download it, put into a working directory, and include into the user management file. See the included example in the download package.

You have to define some contants to make the connection between the interface and the server as follows:
```
/*
* URL of the Openfire's control panel
*/
define("SERVER_URL","http://127.0.0.1:9090");

/*
 * When you install Openfire you need to set the chatserver domain
 * write that domain here. When you create a user on server, Openfire extends
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
define("ADMIN_PASS","admin");`
```

That's it.

# Licensing #
This class is under a BSD style license.
```
/*
 * Copyright (c) 2012-2013, Viktor Tassi  
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
 * [...]
```

I was spending some time to make this software to spare time for you. If you like this, donate the project! Thanks! :)

To place a little donation ($5) click here: [Paypal Donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UYCB9MR3G6CFW)

Thank you!