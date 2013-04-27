typeprotect
===========

Typeprotect allows you to password-protect any of your pages. 

=
Example page
http://www.yejoopark.com/

Benefits
=
1. Minimal yet extremely usuable user interface. 
2. Lightweight - 3.817kb
3. Simple - one single file, no external CSS


Instruction
=
1. Copy & paste typeprotect.php to your directory
2. Open up typeprotect.php and change the value for $password to your own SHA-1 hashed password (http://hash.online-convert.com/sha1-generator).
3. Include the following code on the first line of the document you'd like to protect.

<?php require('typeprotect.php'); ?>

4. To create a sign-out link, add the following line of code. 
 
<a href="typeprotect.php?signout=1">Sign Out</a>
