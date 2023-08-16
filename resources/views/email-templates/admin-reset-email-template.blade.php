<p>Dear {{ $admin->name }} </p>
<br>
<p>
  Your paasword on laravecom system was changed successfully.
  Here Your login credentials:
  <br>
  <b>Login ID: </b>{{ $admin->username }} or  {{ $admin->email }}
  <br>
  <b>Password: </b>{{ $new_password }}
</p>
<br>
Please, Keep Your Credentials Confidential. Your username and password are own credentials and you should never share them with anybody else.
<p>
  laravecom will not be liable for any misuse of your username and password. 
</p>
--------------------------------------------------------------------------
<p>
  This mail was autometically sent by laravecom system. Do not reply it.
</p>